<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Branch;
use App\Models\StockMovement;

class CleanDuplicateOpeningStockMovements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:clean-opening-edits {--fix : Actually execute the cleanup in the database} {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preview and safely clean up duplicate opening stock movements and historical OPENING_ADJ entries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Scanning stock_movements for duplicate opening records...");

        $groups = DB::table('stock_movements')
            ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ'])
            ->select('product_id', 'branch_id', DB::raw('COUNT(*) as total_count'))
            ->groupBy('product_id', 'branch_id')
            ->get();

        $duplicates = [];

        foreach ($groups as $g) {
            $hasAdj = DB::table('stock_movements')
                ->where('product_id', $g->product_id)
                ->where('branch_id', $g->branch_id)
                ->where('ref_type', 'OPENING_ADJ')
                ->exists();

            if ($g->total_count > 1 || $hasAdj) {
                $duplicates[] = $g;
            }
        }

        if (empty($duplicates)) {
            $this->info("✓ No duplicate opening stock records found in the database. Everything is already 100% clean!");
            return 0;
        }

        $this->warn("\nFound " . count($duplicates) . " product-branch record(s) with duplicate opening entries:");

        $tableData = [];
        $cleanupPlans = [];

        foreach ($duplicates as $d) {
            $product = Product::find($d->product_id);
            $productName = $product ? $product->item_name : "Product #{$d->product_id}";
            $branch = Branch::find($d->branch_id);
            $branchName = $branch ? $branch->name : "Branch #{$d->branch_id}";
            $targetQty = (float) ($product->initial_stock ?? 0);

            $movements = DB::table('stock_movements')
                ->where('product_id', $d->product_id)
                ->where('branch_id', $d->branch_id)
                ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ'])
                ->orderBy('id', 'asc')
                ->get();

            if ($targetQty <= 0) {
                $netQty = 0;
                foreach ($movements as $m) {
                    $netQty += ($m->type === 'out' ? -1 : 1) * (float) $m->qty;
                }
                $targetQty = max(0, $netQty);
            }

            // Build human-readable breakdown of existing duplicates
            $breakdown = [];
            foreach ($movements as $m) {
                $breakdown[] = "#{$m->id} [{$m->ref_type}]: " . floatval($m->qty) . " (at " . date('d-M-y H:i', strtotime($m->created_at)) . ")";
            }

            $primaryId = $movements->first()->id;
            $deleteIds = $movements->skip(1)->pluck('id')->toArray();
            
            foreach ($movements as $m) {
                if ($m->ref_type === 'OPENING_ADJ' && !in_array($m->id, $deleteIds)) {
                    $deleteIds[] = $m->id;
                }
            }
            $deleteIds = array_unique($deleteIds);

            $tableData[] = [
                'Product'          => "ID: {$d->product_id} - {$productName}",
                'Branch'           => $branchName,
                'Current Entries'  => implode("\n", $breakdown),
                'Clean Result'     => "Single OPENING entry of " . $targetQty,
                'Rows Removed'     => count($deleteIds),
            ];

            $cleanupPlans[] = [
                'product_id' => $d->product_id,
                'branch_id'  => $d->branch_id,
                'primary_id' => $primaryId,
                'target_qty' => $targetQty,
                'delete_ids' => $deleteIds,
            ];
        }

        // Render formatted preview table
        $this->table(
            ['Product', 'Branch', 'Current Entries (Before)', 'Clean Result (After)', 'Rows Removed'],
            $tableData
        );

        if (!$this->option('fix')) {
            $this->comment("\n[PREVIEW MODE] This was a dry-run preview. No changes were made to the database.");
            $this->info("To apply the cleanup, run: php artisan stock:clean-opening-edits --fix");
            return 0;
        }

        // Confirmation prompt before applying
        if (!$this->option('force')) {
            if (!$this->confirm("\nReview the preview table above. Do you want to proceed and clean up these records in the database?", false)) {
                $this->warn("Operation cancelled by user. No database changes were made.");
                return 0;
            }
        }

        $this->info("\nApplying cleanup to database...");

        DB::transaction(function () use ($cleanupPlans) {
            foreach ($cleanupPlans as $plan) {
                if (!empty($plan['delete_ids'])) {
                    DB::table('stock_movements')->whereIn('id', $plan['delete_ids'])->delete();
                }

                if ($plan['target_qty'] > 0) {
                    DB::table('stock_movements')
                        ->where('id', $plan['primary_id'])
                        ->update([
                            'ref_type'   => 'OPENING',
                            'type'       => 'in',
                            'qty'        => $plan['target_qty'],
                            'note'       => 'Opening stock entry',
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('stock_movements')->where('id', $plan['primary_id'])->delete();
                }
            }
        });

        $this->info("✓ Successfully cleaned up all duplicate opening stock records! All affected products now have clean, single opening stock entries.");
        return 0;
    }
}

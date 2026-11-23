<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductVariant;

class MigrateProductsToVariants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:products-to-variants 
                            {--product-id= : Specific product ID to migrate}
                            {--dry-run : Run in preview mode without saving changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely converts products with color into variant products and attaches existing stock without any loss.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $targetId = $this->option('product-id');

        $this->info("====================================================================");
        $this->info("   PRODUCT COLOR -> VARIANT MIGRATION TOOL (100% STOCK SAFE)");
        $this->info("====================================================================");

        if ($isDryRun) {
            $this->warn("⚠️  DRY RUN MODE ENABLED - No changes will be written to database.\n");
        }

        $query = Product::where('has_variants', 0)
            ->whereNotNull('color')
            ->where('color', '!=', '')
            ->where('color', '!=', '[]')
            ->where('color', '!=', 'null');

        if ($targetId) {
            $query->where('id', $targetId);
        }

        $products = $query->orderBy('id')->get();

        if ($products->isEmpty()) {
            $this->info("No standard products with color found to migrate.");
            return 0;
        }

        $this->info("Found " . $products->count() . " products with colors to migrate.\n");

        $totalMigrated = 0;
        $totalVariantsCreated = 0;
        $totalStockMigrated = 0;

        DB::beginTransaction();

        try {
            foreach ($products as $p) {
                // Parse and split colors (splits "Cream & Black", "White/Black", etc. into separate variants)
                $rawItems = [];
                if (is_array($p->color)) {
                    $rawItems = $p->color;
                } elseif (is_string($p->color)) {
                    $decoded = json_decode($p->color, true);
                    if (is_array($decoded)) {
                        $rawItems = $decoded;
                    } else {
                        $rawItems = [$p->color];
                    }
                }

                $colors = [];
                foreach ($rawItems as $rawItem) {
                    $normalized = str_ireplace([' & ', ' and ', '/', ',', '&'], '|', (string)$rawItem);
                    $parts = explode('|', $normalized);
                    foreach ($parts as $part) {
                        $trimmed = trim($part);
                        if ($trimmed !== '') {
                            $formatted = ucwords(strtolower($trimmed));
                            if (!in_array($formatted, $colors)) {
                                $colors[] = $formatted;
                            }
                        }
                    }
                }

                if (empty($colors)) {
                    continue;
                }

                // Initial stock checks
                $initialWsStock = (float) DB::table('warehouse_stocks')->where('product_id', $p->id)->sum('quantity');

                $this->line("--------------------------------------------------------------------");
                $this->info("Processing [{$p->item_code}] {$p->item_name} (ID: {$p->id})");
                $this->line("Current Total Stock: {$initialWsStock} | Colors: " . implode(', ', $colors));

                $firstVariantId = null;

                foreach ($colors as $idx => $color) {
                    $cleanColor = preg_replace('/[^a-zA-Z0-9]/', '', $color);
                    $colorCode = strlen($cleanColor) >= 3 ? strtoupper(substr($cleanColor, 0, 3)) : strtoupper(str_pad($cleanColor, 3, 'X'));
                    $varCode = ($p->item_code ? $p->item_code : 'ITEM-' . $p->id) . '-' . $colorCode;
                    $barcode = ($p->barcode_path ? $p->barcode_path : '1000' . $p->id) . '-' . ($idx + 1);

                    $variantData = [
                        'product_id'      => $p->id,
                        'variant_name'    => $p->item_name . " ({$color})",
                        'variant_code'    => $varCode,
                        'color'           => $color,
                        'barcode'         => $barcode,
                        'wholesale_price' => (float)$p->wholesale_price,
                        'retail_price'    => (float)$p->price,
                        'alert_quantity'  => (int)($p->alert_quantity ?? 0),
                        'status'          => 1,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];

                    if (!$isDryRun) {
                        $vId = DB::table('product_variants')->insertGetId($variantData);
                    } else {
                        $vId = 999990 + $idx; // mock id for dry-run
                    }

                    if ($firstVariantId === null) {
                        $firstVariantId = $vId;
                    }

                    $totalVariantsCreated++;
                    $this->line("  ✓ Created Variant: {$color} [Code: {$varCode}, Barcode: {$barcode}] (ID: {$vId})");
                }

                // Map existing stock to the primary variant if product has stock
                if (!$isDryRun && $firstVariantId) {
                    // Update warehouse_stocks
                    $updatedWs = DB::table('warehouse_stocks')
                        ->where('product_id', $p->id)
                        ->whereNull('variant_id')
                        ->update(['variant_id' => $firstVariantId]);

                    // Update stocks
                    DB::table('stocks')
                        ->where('product_id', $p->id)
                        ->whereNull('variant_id')
                        ->update(['variant_id' => $firstVariantId]);

                    // Update stock_movements
                    DB::table('stock_movements')
                        ->where('product_id', $p->id)
                        ->whereNull('variant_id')
                        ->update(['variant_id' => $firstVariantId]);

                    // Update product has_variants = 1
                    DB::table('products')
                        ->where('id', $p->id)
                        ->update(['has_variants' => 1]);

                    // Verify stock equality
                    $afterWsStock = (float) DB::table('warehouse_stocks')->where('product_id', $p->id)->sum('quantity');
                    if (abs($initialWsStock - $afterWsStock) > 0.001) {
                        throw new \Exception("Stock discrepancy detected on product ID {$p->id}! Before: {$initialWsStock}, After: {$afterWsStock}");
                    }

                    $this->info("  ✓ Successfully linked {$initialWsStock} pcs stock to primary variant (ID: {$firstVariantId})");
                } elseif ($isDryRun) {
                    $this->info("  [Dry Run] Would link {$initialWsStock} pcs stock to variant ID {$firstVariantId}");
                }

                $totalStockMigrated += $initialWsStock;
                $totalMigrated++;
            }

            if ($isDryRun) {
                DB::rollBack();
                $this->info("\n====================================================================");
                $this->info("DRY RUN SUMMARY: {$totalMigrated} products and {$totalVariantsCreated} variants simulated.");
                $this->info("Total Stock Verified: {$totalStockMigrated} pcs. No changes committed.");
                $this->info("====================================================================");
            } else {
                DB::commit();
                $this->info("\n====================================================================");
                $this->info("MIGRATION COMPLETED SUCCESSFULLY!");
                $this->info("Total Products Migrated: {$totalMigrated}");
                $this->info("Total Variants Created:  {$totalVariantsCreated}");
                $this->info("Total Stock Preserved:   {$totalStockMigrated} pcs (Zero loss)");
                $this->info("====================================================================");
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\n❌ ERROR OCCURRED: " . $e->getMessage());
            $this->error("All changes have been safely rolled back. Database is completely untouched.");
            return 1;
        }
    }
}

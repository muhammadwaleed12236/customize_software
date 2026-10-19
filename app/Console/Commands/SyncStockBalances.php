<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Stock;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;

class SyncStockBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:sync-live-balances 
                            {--product_id= : Specific product ID to inspect/fix}
                            {--branch_id= : Specific branch ID to inspect/fix}
                            {--fix : Actually apply the corrections to stocks and warehouse_stocks tables}
                            {--force : Bypass confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes snapshot stock tables (stocks, warehouse_stocks) with true transactional ledger history';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productId = $this->option('product_id');
        $branchId  = $this->option('branch_id');
        $isFix     = $this->option('fix');
        $isForce   = $this->option('force');

        $this->info("=== Inventory Ledger & Snapshot Reconciliation ===");
        if ($isFix) {
            $this->warn("MODE: FIX (Modifications will be written to database)");
        } else {
            $this->line("MODE: DRY RUN / AUDIT (Run with --fix to apply corrections)");
        }

        // Query Products
        $pQuery = Product::query();
        if ($productId) {
            $pQuery->where('id', $productId);
        }
        $products = $pQuery->get();

        if ($products->isEmpty()) {
            $this->error("No products found matching criteria.");
            return 1;
        }

        // Query Branches
        $bQuery = Branch::query();
        if ($branchId) {
            $bQuery->where('id', $branchId);
        }
        $branches = $bQuery->get();

        if ($branches->isEmpty()) {
            $this->error("No branches found matching criteria.");
            return 1;
        }

        $report = [];
        $discrepanciesCount = 0;

        foreach ($products as $product) {
            foreach ($branches as $branch) {
                $bId = $branch->id;
                $pId = $product->id;

                // 1. Opening Stock Movements
                $openQuery = DB::table('stock_movements')
                    ->where('product_id', $pId)
                    ->where('branch_id', $bId)
                    ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ'])
                    ->where('type', 'in');
                $opening = (float) $openQuery->sum('qty');

                // Fallback to initial_stock ONLY if no stock movements exist anywhere in system for this product
                if ($opening == 0) {
                    $hasAnyGlobalMov = DB::table('stock_movements')
                        ->where('product_id', $pId)
                        ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ'])
                        ->exists();
                    if (!$hasAnyGlobalMov && (float)($product->initial_stock ?? 0) > 0) {
                        $primaryBranch = $product->branch_id ?: 1;
                        if ($bId == $primaryBranch) {
                            $opening = (float) $product->initial_stock;
                        }
                    }
                }

                // 2. Purchases (all non-deleted purchases for this branch)
                $purchases = (float) DB::table('purchase_items')
                    ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                    ->where('purchase_items.product_id', $pId)
                    ->where('purchases.branch_id', $bId)
                    ->whereNull('purchases.deleted_at')
                    ->sum('purchase_items.qty');

                // 3. Sales Returns
                $saleReturns = (float) DB::table('sales_returns')
                    ->where('product_code', $product->item_code)
                    ->whereExists(function ($sub) use ($bId) {
                        $sub->select(DB::raw(1))
                            ->from('sales')
                            ->whereColumn('sales.id', 'sales_returns.sale_id')
                            ->where('sales.branch_id', $bId);
                    })
                    ->sum('qty');

                // 4. Stock Transfers In
                $transfersIn = (float) DB::table('stock_transfers')
                    ->where('product_id', $pId)
                    ->where('to_branch_id', $bId)
                    ->where('status', 'approved')
                    ->sum('quantity');

                // 5. Sales Out (non-cancelled)
                $sales = (float) DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->where('sale_items.product_id', $pId)
                    ->where('sales.branch_id', $bId)
                    ->where('sales.status', '!=', 'cancelled')
                    ->sum('sale_items.sales_qty');

                // 6. Purchase Returns
                $purchaseReturns = 0.0;
                if (\Illuminate\Support\Facades\Schema::hasTable('purchase_returns') && \Illuminate\Support\Facades\Schema::hasTable('purchase_return_items')) {
                    $prQuery = DB::table('purchase_return_items')
                        ->join('purchase_returns', 'purchase_return_items.purchase_return_id', '=', 'purchase_returns.id')
                        ->where('purchase_return_items.product_id', $pId);
                    if (\Illuminate\Support\Facades\Schema::hasColumn('purchase_returns', 'branch_id')) {
                        $prQuery->where('purchase_returns.branch_id', $bId);
                    }
                    $purchaseReturns = (float) $prQuery->sum('purchase_return_items.qty');
                }

                // 7. Stock Transfers Out
                $transfersOut = (float) DB::table('stock_transfers')
                    ->where('product_id', $pId)
                    ->where('from_branch_id', $bId)
                    ->where('status', 'approved')
                    ->sum('quantity');

                // 8. Damaged Stocks
                $damaged = 0.0;
                if (\Illuminate\Support\Facades\Schema::hasTable('damaged_stocks')) {
                    $damaged = (float) DB::table('damaged_stocks')
                        ->where('product_id', $pId)
                        ->where('branch_id', $bId)
                        ->sum('quantity');
                }

                // Calculated True Ledger Balance
                $totalIn = $opening + $purchases + $saleReturns + $transfersIn;
                $totalOut = $sales + $purchaseReturns + $transfersOut + $damaged;
                $trueBalance = $totalIn - $totalOut;

                // Current Recorded Snapshot Balances
                $currentStockRow = DB::table('stocks')
                    ->where('product_id', $pId)
                    ->where('branch_id', $bId)
                    ->first();
                $currentStockQty = $currentStockRow ? (float) $currentStockRow->qty : 0.0;

                $currentWhQty = (float) DB::table('warehouse_stocks')
                    ->where('product_id', $pId)
                    ->where('branch_id', $bId)
                    ->sum('quantity');

                $hasActivity = ($totalIn > 0 || $totalOut > 0 || $currentStockQty != 0 || $currentWhQty != 0);
                if (!$hasActivity) {
                    continue; // Skip products with zero activity in this branch
                }

                $stockDiff = abs($currentStockQty - $trueBalance);
                $whDiff    = abs($currentWhQty - $trueBalance);
                $isMismatch = ($stockDiff > 0.0001 || $whDiff > 0.0001);

                if ($isMismatch) {
                    $discrepanciesCount++;
                }

                $report[] = [
                    'p_id'         => $pId,
                    'name'         => mb_strimwidth($product->item_name, 0, 24, '...'),
                    'b_name'       => $branch->name,
                    'opening'      => number_format($opening, 2),
                    'inward'       => number_format($purchases + $saleReturns + $transfersIn, 2),
                    'outward'      => number_format($sales + $purchaseReturns + $transfersOut + $damaged, 2),
                    'true_balance' => number_format($trueBalance, 2),
                    'stocks_qty'   => number_format($currentStockQty, 2),
                    'wh_sum'       => number_format($currentWhQty, 2),
                    'status'       => $isMismatch ? '<fg=red>MISMATCH</>' : '<fg=green>MATCH</>',
                    'raw_true'     => $trueBalance,
                    'p_id_raw'     => $pId,
                    'b_id_raw'     => $bId,
                    'is_mismatch'  => $isMismatch,
                ];
            }
        }

        if (empty($report)) {
            $this->info("No active inventory records found.");
            return 0;
        }

        $mismatchedRows = array_filter($report, fn($r) => $r['is_mismatch']);

        $displayList = !empty($mismatchedRows) ? $mismatchedRows : $report;

        $displayRows = array_map(function ($r) {
            return [
                $r['p_id'],
                $r['name'],
                $r['b_name'],
                $r['opening'],
                $r['inward'],
                $r['outward'],
                $r['true_balance'],
                $r['stocks_qty'],
                $r['wh_sum'],
                $r['status'],
            ];
        }, $displayList);

        $this->table(
            ['ID', 'Product', 'Branch', 'Opening', 'Inward', 'Outward', 'True Bal', 'Stock Qty', 'WH Sum', 'Status'],
            $displayRows
        );

        $this->info("Total Items Evaluated: " . count($report) . " | Discrepancies Found: {$discrepanciesCount}");

        if ($discrepanciesCount === 0) {
            $this->info("✅ All stock snapshot records are 100% in sync with transaction history.");
            return 0;
        }

        if (!$isFix) {
            $this->warn("Found {$discrepanciesCount} discrepancies. Run with --fix to synchronize database snapshot tables.");
            return 0;
        }

        if (!$isForce && !$this->confirm("Apply synchronization to align snapshot tables with the true ledger balance?", true)) {
            $this->line("Operation cancelled by user.");
            return 0;
        }

        $this->info("Applying stock balance synchronization for {$discrepanciesCount} mismatched items...");

        DB::transaction(function () use ($report) {
            $now = now();
            foreach ($report as $r) {
                if (!$r['is_mismatch']) {
                    continue;
                }
                $pId = $r['p_id_raw'];
                $bId = $r['b_id_raw'];
                $trueBal = $r['raw_true'];

                // 1. Update stocks table
                DB::table('stocks')->updateOrInsert(
                    ['product_id' => $pId, 'branch_id' => $bId],
                    ['qty' => $trueBal, 'updated_at' => $now]
                );

                // 2. Update warehouse_stocks table
                // Check if branch has warehouses with allocated stock
                $whEntries = DB::table('warehouse_stocks')
                    ->where('product_id', $pId)
                    ->where('branch_id', $bId)
                    ->get();

                $warehouseOnlyEntries = $whEntries->filter(fn($w) => !is_null($w->warehouse_id));
                $warehouseOnlySum = (float) $warehouseOnlyEntries->sum('quantity');

                if ($warehouseOnlyEntries->isEmpty()) {
                    // Only shop stock or no record exists
                    DB::table('warehouse_stocks')->updateOrInsert(
                        ['product_id' => $pId, 'branch_id' => $bId, 'warehouse_id' => null],
                        ['quantity' => $trueBal, 'updated_at' => $now]
                    );
                } else {
                    // Specific warehouses hold some stock. Shop receives the remainder.
                    $shopRemainder = $trueBal - $warehouseOnlySum;
                    DB::table('warehouse_stocks')->updateOrInsert(
                        ['product_id' => $pId, 'branch_id' => $bId, 'warehouse_id' => null],
                        ['quantity' => $shopRemainder, 'updated_at' => $now]
                    );
                }
            }
        });

        $this->info("✅ Synchronization completed successfully! All stocks snapshot tables now match the transaction ledger.");
        return 0;
    }
}

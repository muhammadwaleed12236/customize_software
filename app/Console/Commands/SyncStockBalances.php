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
            $ids = array_filter(array_map('trim', explode(',', (string) $productId)));
            $pQuery->whereIn('id', $ids);
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

                // 2. Update warehouse_stocks table with exact per-warehouse ledger calculations
                $prodModel = Product::find($pId);

                // Find all warehouses belonging to this branch, plus any warehouses involved in purchases/sales/transfers/existing records
                $branchWhIds = DB::table('branch_warehouse')->where('branch_id', $bId)->pluck('warehouse_id')->toArray();
                $txPurWhIds = DB::table('purchase_items')
                    ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                    ->where('purchase_items.product_id', $pId)
                    ->where('purchases.branch_id', $bId)
                    ->whereNull('purchases.deleted_at')
                    ->pluck('purchase_items.warehouse_id')
                    ->toArray();
                $txSaleWhIds = DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->where('sale_items.product_id', $pId)
                    ->where('sales.branch_id', $bId)
                    ->where('sales.status', '!=', 'cancelled')
                    ->pluck('sale_items.warehouse_id')
                    ->toArray();
                $existingWhIds = DB::table('warehouse_stocks')
                    ->where('product_id', $pId)
                    ->where('branch_id', $bId)
                    ->pluck('warehouse_id')
                    ->toArray();

                $allWhCandidates = array_unique(array_filter(array_merge($branchWhIds, $txPurWhIds, $txSaleWhIds, $existingWhIds)));

                $totalWhStock = 0.0;
                foreach ($allWhCandidates as $wId) {
                    $whBalance = $this->calculateWarehouseLedgerBalance($pId, $bId, (int)$wId, $prodModel);
                    $totalWhStock += $whBalance;

                    DB::table('warehouse_stocks')->updateOrInsert(
                        ['product_id' => $pId, 'branch_id' => $bId, 'warehouse_id' => $wId],
                        ['quantity' => $whBalance, 'updated_at' => $now]
                    );
                }

                // Calculate shop balance (warehouse_id = null)
                $shopBalance = $this->calculateWarehouseLedgerBalance($pId, $bId, null, $prodModel);
                $allocatedTotal = $totalWhStock + $shopBalance;
                $remainder = $trueBal - $allocatedTotal;
                $finalShopBalance = $shopBalance + $remainder;

                DB::table('warehouse_stocks')->updateOrInsert(
                    ['product_id' => $pId, 'branch_id' => $bId, 'warehouse_id' => null],
                    ['quantity' => $finalShopBalance, 'updated_at' => $now]
                );
            }
        });

        $this->info("✅ Synchronization completed successfully! All stocks snapshot tables now match the transaction ledger.");
        return 0;
    }

    /**
     * Compute exact ledger balance for a specific warehouse (or shop if null)
     */
    private function calculateWarehouseLedgerBalance(int $productId, int $branchId, ?int $warehouseId, Product $product): float
    {
        $ppp = (float) ($product->piece_per_pack > 1 ? $product->piece_per_pack : 1);
        $isCartonProd = ($ppp > 1);

        // 1. Opening Stock from stock_movements
        $openQ = DB::table('stock_movements')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ'])
            ->where('type', 'in');

        if (\Illuminate\Support\Facades\Schema::hasColumn('stock_movements', 'warehouse_id')) {
            if ($warehouseId !== null) {
                $openQ->where('warehouse_id', $warehouseId);
            } else {
                $openQ->whereNull('warehouse_id');
            }
        }
        $opening = (float) $openQ->sum('qty');

        if ($opening == 0 && $warehouseId === null) {
            $hasAnyMov = DB::table('stock_movements')
                ->where('product_id', $productId)
                ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ'])
                ->exists();
            if (!$hasAnyMov && (float)($product->initial_stock ?? 0) > 0) {
                $primaryBranch = $product->branch_id ?: 1;
                if ($branchId == $primaryBranch) {
                    $opening = (float) $product->initial_stock;
                }
            }
        }

        // 2. Purchases
        $purQ = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('purchase_items.product_id', $productId)
            ->where('purchases.branch_id', $branchId)
            ->whereNull('purchases.deleted_at');

        if ($warehouseId !== null) {
            $purQ->where(function ($q) use ($warehouseId) {
                $q->where('purchase_items.warehouse_id', $warehouseId)
                  ->orWhere(function ($sq) use ($warehouseId) {
                      $sq->whereNull('purchase_items.warehouse_id')->where('purchases.warehouse_id', $warehouseId);
                  });
            });
        } else {
            $purQ->whereNull('purchase_items.warehouse_id')->whereNull('purchases.warehouse_id');
        }

        $purchases = 0.0;
        foreach ($purQ->select('purchase_items.qty', 'purchase_items.packing_qty', 'purchase_items.item_per_piece')->get() as $pb) {
            $m = (float)($pb->item_per_piece > 1 ? $pb->item_per_piece : ($isCartonProd ? $ppp : 1));
            $effectivePbQty = (!empty($pb->packing_qty) && (float)$pb->packing_qty > 0) ? $pb->packing_qty : $pb->qty;
            $purchases += $isCartonProd ? Product::parseCartonQtyToPieces($effectivePbQty, $m, 'ctn') : ((float)$effectivePbQty * $m);
        }

        // 3. Sales Out
        $saleQ = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sale_items.product_id', $productId)
            ->where('sales.branch_id', $branchId)
            ->where('sales.status', '!=', 'cancelled');

        if ($warehouseId !== null) {
            $saleQ->where('sale_items.warehouse_id', $warehouseId);
        } else {
            $saleQ->whereNull('sale_items.warehouse_id');
        }

        $sales = 0.0;
        foreach ($saleQ->select('sale_items.sales_qty', 'sale_items.sale_unit')->get() as $sb) {
            $isPcsSale = strtolower($sb->sale_unit ?? '') === 'pcs';
            $sales += Product::parseCartonQtyToPieces($sb->sales_qty, $ppp, $isPcsSale ? 'pcs' : 'ctn');
        }

        // 4. Sales Returns
        $saleReturns = 0.0;
        $srQ = DB::table('sales_returns')
            ->where('product_code', $product->item_code)
            ->whereExists(function ($sub) use ($branchId) {
                $sub->select(DB::raw(1))
                    ->from('sales')
                    ->whereColumn('sales.id', 'sales_returns.sale_id')
                    ->where('sales.branch_id', $branchId);
            });
        foreach ($srQ->select('sales_returns.qty', 'sales_returns.sale_id')->get() as $srb) {
            $origSaleItem = DB::table('sale_items')->where('sale_id', $srb->sale_id)->where('product_id', $productId)->first();
            $origWh = $origSaleItem ? $origSaleItem->warehouse_id : null;
            if ($origWh == $warehouseId) {
                $isPcsReturn = $origSaleItem && strtolower($origSaleItem->sale_unit ?? '') === 'pcs';
                $saleReturns += Product::parseCartonQtyToPieces($srb->qty, $ppp, $isPcsReturn ? 'pcs' : 'ctn');
            }
        }

        // 5. Stock Transfers
        $transfersIn = 0.0;
        $transfersOut = 0.0;
        if ($warehouseId !== null) {
            $transfersIn = (float) DB::table('stock_transfers')
                ->where('product_id', $productId)
                ->where('to_warehouse_id', $warehouseId)
                ->where('status', 'approved')
                ->sum('quantity');

            $transfersOut = (float) DB::table('stock_transfers')
                ->where('product_id', $productId)
                ->where('from_warehouse_id', $warehouseId)
                ->where('status', 'approved')
                ->sum('quantity');
        }

        // 6. Damaged Stocks
        $damaged = 0.0;
        if (\Illuminate\Support\Facades\Schema::hasTable('damaged_stocks')) {
            $dmgQ = DB::table('damaged_stocks')
                ->where('product_id', $productId)
                ->where('branch_id', $branchId);
            if ($warehouseId !== null) {
                $dmgQ->where('warehouse_id', $warehouseId);
            } else {
                $dmgQ->whereNull('warehouse_id');
            }
            $damaged = (float) $dmgQ->sum('quantity');
        }

        return ($opening + $purchases + $saleReturns + $transfersIn) - ($sales + $transfersOut + $damaged);
    }
}

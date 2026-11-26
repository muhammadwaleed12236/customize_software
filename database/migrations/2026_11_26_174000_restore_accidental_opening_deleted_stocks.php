<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $now = now();

            // 1. Find unrestored OPENING_DELETE movements
            $restoredIds = DB::table('stock_movements')
                ->where('ref_type', 'STOCK_RESTORE')
                ->whereNotNull('ref_id')
                ->pluck('ref_id')
                ->toArray();

            $unrestoredMoves = DB::table('stock_movements')
                ->where('ref_type', 'OPENING_DELETE')
                ->whereNotIn('id', $restoredIds)
                ->get();

            foreach ($unrestoredMoves as $m) {
                $pid = (int) $m->product_id;
                $qty = (float) $m->qty;
                $variantId = $m->variant_id ? (int) $m->variant_id : null;
                $branchId = (int) ($m->branch_id ?: 4);

                if ($qty <= 0) continue;

                // Determine target warehouse:
                // Check transfer
                $transfer = DB::table('stock_transfers')
                    ->where('product_id', $pid)
                    ->whereDate('transfer_date', '2026-11-19')
                    ->first();

                // Check purchase 1
                $p1 = DB::table('purchase_items')
                    ->where('purchase_id', 1)
                    ->where('product_id', $pid)
                    ->first();

                $targetWh = null;
                if ($transfer) {
                    $targetWh = (int) $transfer->to_warehouse_id; // 2 (ALK-G#1)
                } elseif ($p1) {
                    $targetWh = (int) ($p1->warehouse_id ?: 3); // 3 (ALK-G#2)
                } else {
                    $targetWh = 2; // Default G#1
                }

                // 1. Update warehouse_stocks
                $wsQuery = DB::table('warehouse_stocks')
                    ->where('product_id', $pid)
                    ->where('branch_id', $branchId);
                if ($variantId) {
                    $wsQuery->where('variant_id', $variantId);
                } else {
                    $wsQuery->whereNull('variant_id');
                }

                if ($targetWh) {
                    $wsQuery->where('warehouse_id', $targetWh);
                } else {
                    $wsQuery->whereNull('warehouse_id');
                }

                $existingWs = $wsQuery->first();

                if ($existingWs) {
                    DB::table('warehouse_stocks')->where('id', $existingWs->id)->update([
                        'quantity'   => (float) $existingWs->quantity + $qty,
                        'updated_at' => $now,
                    ]);
                } else {
                    DB::table('warehouse_stocks')->insert([
                        'product_id'   => $pid,
                        'variant_id'   => $variantId,
                        'branch_id'    => $branchId,
                        'warehouse_id' => $targetWh,
                        'quantity'     => $qty,
                        'price'        => null,
                        'remarks'      => 'Restored after accidental opening delete',
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);
                }

                // 2. Update stocks
                $sQuery = DB::table('stocks')
                    ->where('product_id', $pid)
                    ->where('branch_id', $branchId);
                if ($variantId) {
                    $sQuery->where('variant_id', $variantId);
                } else {
                    $sQuery->whereNull('variant_id');
                }

                $existingS = $sQuery->first();

                if ($existingS) {
                    DB::table('stocks')->where('id', $existingS->id)->update([
                        'qty'        => (float) $existingS->qty + $qty,
                        'updated_at' => $now,
                    ]);
                } else {
                    DB::table('stocks')->insert([
                        'product_id'   => $pid,
                        'variant_id'   => $variantId,
                        'branch_id'    => $branchId,
                        'qty'          => $qty,
                        'reserved_qty' => 0,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);
                }

                // 3. Insert audit movement
                DB::table('stock_movements')->insert([
                    'product_id' => $pid,
                    'variant_id' => $variantId,
                    'branch_id'  => $branchId,
                    'type'       => 'in',
                    'qty'        => $qty,
                    'ref_type'   => 'STOCK_RESTORE',
                    'ref_id'     => $m->id,
                    'note'       => 'Stock restored: Reversal of accidental opening delete',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 2. Reconcile any purchase items shortages in ALK-G#2 (Warehouse #3)
            $p1Items = DB::table('purchase_items')->where('purchase_id', 1)->get();
            foreach ($p1Items as $item) {
                $pid = $item->product_id;
                $purchased = (float) $item->qty;

                $sales = (float) DB::table('stock_movements')
                    ->where('product_id', $pid)
                    ->where('ref_type', 'SALE')
                    ->where('type', 'out')
                    ->sum('qty');
                $returns = (float) DB::table('stock_movements')
                    ->where('product_id', $pid)
                    ->where('ref_type', 'SALE_RETURN')
                    ->where('type', 'in')
                    ->sum('qty');
                $netSales = $sales - $returns;

                $otherPurchases = (float) DB::table('purchase_items')
                    ->where('purchase_id', '!=', 1)
                    ->where('product_id', $pid)
                    ->sum('qty');

                $expectedMinStock = $purchased + $otherPurchases - $netSales;

                $currWh = DB::table('warehouse_stocks')->where('product_id', $pid)->get();
                $currTot = (float) $currWh->sum('quantity');

                if ($currTot < $expectedMinStock) {
                    $shortage = $expectedMinStock - $currTot;

                    $ws3 = DB::table('warehouse_stocks')
                        ->where('product_id', $pid)
                        ->where('warehouse_id', 3)
                        ->first();

                    if ($ws3) {
                        DB::table('warehouse_stocks')->where('id', $ws3->id)->update([
                            'quantity'   => (float) $ws3->quantity + $shortage,
                            'updated_at' => $now,
                        ]);
                    } else {
                        DB::table('warehouse_stocks')->insert([
                            'product_id'   => $pid,
                            'warehouse_id' => 3,
                            'branch_id'    => 4,
                            'quantity'     => $shortage,
                            'remarks'      => 'Restored purchase stock in ALK-G#2',
                            'created_at'   => $now,
                            'updated_at'   => $now,
                        ]);
                    }

                    $s = DB::table('stocks')->where('product_id', $pid)->where('branch_id', 4)->first();
                    if ($s) {
                        DB::table('stocks')->where('id', $s->id)->update([
                            'qty'        => (float) $s->qty + $shortage,
                            'updated_at' => $now,
                        ]);
                    } else {
                        DB::table('stocks')->insert([
                            'product_id'   => $pid,
                            'branch_id'    => 4,
                            'qty'          => $shortage,
                            'reserved_qty' => 0,
                            'created_at'   => $now,
                            'updated_at'   => $now,
                        ]);
                    }

                    DB::table('stock_movements')->insert([
                        'product_id' => $pid,
                        'branch_id'  => 4,
                        'type'       => 'in',
                        'qty'        => $shortage,
                        'ref_type'   => 'STOCK_RESTORE',
                        'ref_id'     => 1,
                        'note'       => 'Purchase stock restored in ALK-G#2',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // 3. Ensure Purchase #1 header points to ALK-G#2 (Warehouse #3)
            DB::table('purchases')->where('id', 1)->update([
                'warehouse_id' => 3,
                'updated_at'   => $now,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep resilient
    }
};

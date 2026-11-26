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
        // 🔒 STRICT SAFETY GUARD FOR MULTI-SOFTWARE / MULTI-CLIENT REPOSITORIES
        // Ensure this migration ONLY runs on the specific "AL KARAM AUTO" instance
        // If this codebase is pulled by another client/software, it will silently SKIP and do NOTHING.
        $isAlKaramInstance = DB::table('branches')->where('id', 4)->where('name', 'like', '%AL KARAM%')->exists()
            && DB::table('warehouses')->where('id', 3)->where('warehouse_name', 'like', '%ALK-G#2%')->exists();

        if (!$isAlKaramInstance) {
            return;
        }

        DB::transaction(function () {
            $now = now();
            $branchId = 4;

            // Get all items in Purchase 1
            $p1Items = DB::table('purchase_items')->where('purchase_id', 1)->get();

            foreach ($p1Items as $item) {
                $pid = (int) $item->product_id;
                $purchasedQty = (float) $item->qty;
                $purchaseWhId = (int) ($item->warehouse_id ?: 3); // 3 = ALK-G#2

                // Check other purchases if any
                $otherPurchases = DB::table('purchase_items')
                    ->where('purchase_id', '!=', 1)
                    ->where('product_id', $pid)
                    ->get();
                $otherPurchasesQtyByWh = [];
                foreach ($otherPurchases as $op) {
                    $wh = (int) ($op->warehouse_id ?: 3);
                    $otherPurchasesQtyByWh[$wh] = ($otherPurchasesQtyByWh[$wh] ?? 0) + (float) $op->qty;
                }

                // Transfers OUT of purchaseWhId into other warehouses
                $transfersOut = DB::table('stock_transfers')
                    ->where('product_id', $pid)
                    ->where('from_warehouse_id', $purchaseWhId)
                    ->whereIn('status', ['approved', 'completed'])
                    ->get();
                $transferredToWh = [];
                $totalTransferredOut = 0;
                foreach ($transfersOut as $trf) {
                    $toWh = (int) $trf->to_warehouse_id;
                    $tQty = (float) $trf->quantity;
                    $transferredToWh[$toWh] = ($transferredToWh[$toWh] ?? 0) + $tQty;
                    $totalTransferredOut += $tQty;
                }

                // Sales by warehouse
                $sales = DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->where('sale_items.product_id', $pid)
                    ->where('sales.status', '!=', 'cancelled')
                    ->select('sale_items.warehouse_id', DB::raw('sum(sale_items.sales_qty) as total_sold'))
                    ->groupBy('sale_items.warehouse_id')
                    ->get();
                $salesByWh = [];
                foreach ($sales as $s) {
                    $wh = $s->warehouse_id !== null ? (int) $s->warehouse_id : 'shop';
                    $salesByWh[$wh] = (float) $s->total_sold;
                }

                // Calculate exact expected stock per warehouse
                // 1. Warehouse #3 (ALK-G#2):
                $expectedG2 = $purchasedQty + ($otherPurchasesQtyByWh[$purchaseWhId] ?? 0) - $totalTransferredOut - ($salesByWh[$purchaseWhId] ?? 0);

                // 2. Warehouse #2 (ALK-G#1):
                $expectedG1 = ($transferredToWh[2] ?? 0) + ($otherPurchasesQtyByWh[2] ?? 0) - ($salesByWh[2] ?? 0);

                // 3. Shop (branch counter):
                $expectedShop = -($salesByWh['shop'] ?? 0);

                // Total company stock
                $expectedTotal = $expectedG2 + $expectedG1 + $expectedShop;

                // Update or Insert Warehouse #3 (ALK-G#2)
                $wsG2 = DB::table('warehouse_stocks')
                    ->where('product_id', $pid)
                    ->where('warehouse_id', 3)
                    ->where('branch_id', $branchId)
                    ->first();
                if ($wsG2) {
                    DB::table('warehouse_stocks')->where('id', $wsG2->id)->update([
                        'quantity'   => $expectedG2,
                        'updated_at' => $now,
                    ]);
                } else {
                    DB::table('warehouse_stocks')->insert([
                        'product_id'   => $pid,
                        'variant_id'   => null,
                        'branch_id'    => $branchId,
                        'warehouse_id' => 3,
                        'quantity'     => $expectedG2,
                        'price'        => null,
                        'remarks'      => 'Reconciled to ALK-G#2',
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);
                }

                // Update or Insert Warehouse #2 (ALK-G#1)
                $wsG1 = DB::table('warehouse_stocks')
                    ->where('product_id', $pid)
                    ->where('warehouse_id', 2)
                    ->where('branch_id', $branchId)
                    ->first();
                if ($wsG1) {
                    DB::table('warehouse_stocks')->where('id', $wsG1->id)->update([
                        'quantity'   => $expectedG1,
                        'updated_at' => $now,
                    ]);
                } else {
                    DB::table('warehouse_stocks')->insert([
                        'product_id'   => $pid,
                        'variant_id'   => null,
                        'branch_id'    => $branchId,
                        'warehouse_id' => 2,
                        'quantity'     => $expectedG1,
                        'price'        => null,
                        'remarks'      => 'Reconciled to ALK-G#1',
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);
                }

                // Update or Insert Shop Stock (warehouse_id = null) if there were shop sales
                if (abs($expectedShop) > 0.001) {
                    $wsShop = DB::table('warehouse_stocks')
                        ->where('product_id', $pid)
                        ->whereNull('warehouse_id')
                        ->where('branch_id', $branchId)
                        ->first();
                    if ($wsShop) {
                        DB::table('warehouse_stocks')->where('id', $wsShop->id)->update([
                            'quantity'   => $expectedShop,
                            'updated_at' => $now,
                        ]);
                    } else {
                        DB::table('warehouse_stocks')->insert([
                            'product_id'   => $pid,
                            'variant_id'   => null,
                            'branch_id'    => $branchId,
                            'warehouse_id' => null,
                            'quantity'     => $expectedShop,
                            'price'        => null,
                            'remarks'      => 'Shop Stock',
                            'created_at'   => $now,
                            'updated_at'   => $now,
                        ]);
                    }
                }

                // Update stocks table
                $sRow = DB::table('stocks')
                    ->where('product_id', $pid)
                    ->where('branch_id', $branchId)
                    ->first();
                if ($sRow) {
                    DB::table('stocks')->where('id', $sRow->id)->update([
                        'qty'        => $expectedTotal,
                        'updated_at' => $now,
                    ]);
                } else {
                    DB::table('stocks')->insert([
                        'product_id'   => $pid,
                        'variant_id'   => null,
                        'branch_id'    => $branchId,
                        'qty'          => $expectedTotal,
                        'reserved_qty' => 0,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);
                }

                // Record audit stock movement
                DB::table('stock_movements')->insert([
                    'product_id' => $pid,
                    'variant_id' => null,
                    'branch_id'  => $branchId,
                    'type'       => 'in',
                    'qty'        => 0,
                    'ref_type'   => 'STOCK_RECONCILE',
                    'ref_id'     => 1,
                    'note'       => "Reconciled stock to correct warehouse: G#2: {$expectedG2}, G#1: {$expectedG1}, Shop: {$expectedShop}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down operation needed
    }
};

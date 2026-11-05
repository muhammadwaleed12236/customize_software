<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\CustomerLedger;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // 1. Expand sales.total_net from DECIMAL(8,2) to DECIMAL(15,2)
            // DECIMAL(8,2) caps amounts at 999,999.99 which breaks any sale >= 1,000,000
            DB::statement("ALTER TABLE sales MODIFY total_net DECIMAL(15, 2) NULL");

            // 2. Fix sales where total_net was truncated to 999,999.99
            DB::statement("UPDATE sales SET total_net = sub_total2 WHERE total_net = 999999.99 AND sub_total2 > 999999.99");
            DB::statement("UPDATE sales SET total_net = sub_total1 WHERE (total_net = 999999.99 OR total_net IS NULL OR total_net = 0) AND sub_total1 > 999999.99");

            // 3. Chronologically reconcile previous_balance for all sales and ledgers
            // Fixes old sales where previous_balance was wrongly set equal to the sale amount itself
            $customers = Customer::all();

            foreach ($customers as $customer) {
                $cid = $customer->id;
                $runningBalance = (float)($customer->opening_balance ?? 0);

                // Get all sales for this customer in chronological order
                $sales = Sale::where('customer_id', $cid)
                    ->where(function($q) {
                        $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
                    })
                    ->orderBy('id', 'asc')
                    ->get();

                if ($sales->isEmpty()) {
                    continue;
                }

                // If customer only has 1 sale and opening balance is 0,
                // previous_balance MUST be 0
                foreach ($sales as $index => $sale) {
                    $orderLevelDiscount = (float)($sale->discount_amount ?? 0);
                    $additionalDiscount = (float)($sale->additional_discount ?? 0);
                    $extraCharges = (float)($sale->extra_charges ?? 0);

                    $effectiveDiscount = ($orderLevelDiscount > 0 && $additionalDiscount > 0 && abs($orderLevelDiscount - $additionalDiscount) < 0.01)
                        ? $additionalDiscount
                        : ($orderLevelDiscount + $additionalDiscount);

                    $netSale = (float)($sale->total_net > 0 ? $sale->total_net : ($sale->sub_total2 > 0 ? $sale->sub_total2 : max(0, (float)$sale->sub_total1 - $effectiveDiscount + $extraCharges)));

                    // Receipts linked to this sale
                    $paidOnSale = (float) DB::table('receipts_vouchers')
                        ->where(function($q) use ($sale) {
                            $q->where('sale_id', $sale->id)
                              ->orWhere(function($q2) use ($sale) {
                                  $q2->whereNull('sale_id')
                                     ->where('reference_no', $sale->invoice_no)
                                     ->where('party_id', (string)$sale->customer_id);
                              });
                        })
                        ->where(function($q) {
                            $q->whereNull('status')->orWhere('status', '!=', 'voided');
                        })
                        ->sum('amount');

                    if ($paidOnSale <= 0) {
                        $paidOnSale = (float)(($sale->receipt1 ?? 0) + ($sale->receipt2 ?? 0));
                    }

                    // True previous balance for this specific sale
                    $truePrevBalance = $runningBalance;
                    $trueTotalBalance = ($truePrevBalance + $netSale) - $paidOnSale;

                    // If sale's previous_balance was erroneously saved equal to the sale itself or corrupted
                    $needsUpdate = false;
                    if (abs((float)$sale->previous_balance - $truePrevBalance) > 0.01) {
                        $sale->previous_balance = $truePrevBalance;
                        $needsUpdate = true;
                    }
                    if (abs((float)$sale->total_balance - $trueTotalBalance) > 0.01) {
                        $sale->total_balance = $trueTotalBalance;
                        $needsUpdate = true;
                    }
                    if ($sale->total_net <= 0 || abs((float)$sale->total_net - 999999.99) < 0.01) {
                        $sale->total_net = $netSale;
                        $needsUpdate = true;
                    }
                    if ($needsUpdate) {
                        $sale->save();
                    }

                    // Also sync the corresponding customer ledger entry
                    $ledger = CustomerLedger::where('customer_id', $cid)
                        ->where('reference_id', (string)$sale->id)
                        ->where('transaction_type', 'Sale')
                        ->first();

                    if ($ledger) {
                        if (abs((float)$ledger->previous_balance - $truePrevBalance) > 0.01 ||
                            abs((float)$ledger->closing_balance - $trueTotalBalance) > 0.01) {
                            $ledger->previous_balance = $truePrevBalance;
                            $ledger->total_debit = $netSale;
                            $ledger->closing_balance = $trueTotalBalance;
                            $ledger->save();
                        }
                    }

                    // Advance running balance for the next transaction
                    $runningBalance = $trueTotalBalance;
                }
            }

            Log::info("Migration fix_large_sales_decimal_and_previous_balance completed successfully.");
        } catch (\Throwable $e) {
            Log::error("Migration fix_large_sales_decimal_and_previous_balance failed: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Models\Sale;
use App\Models\CustomerLedger;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // 1. Correct existing credit sales where total_balance had double discount subtracted
            $creditSales = Sale::where('party_type', 'credit')
                ->where('additional_discount', '>', 0)
                ->get();

            foreach ($creditSales as $sale) {
                $netSale = (float)($sale->total_net > 0 ? $sale->total_net : ($sale->sub_total2 > 0 ? $sale->sub_total2 : ($sale->sub_total1 - $sale->additional_discount)));
                $prev = (float)($sale->previous_balance ?? 0);
                $receipts = (float)(($sale->receipt1 ?? 0) + ($sale->receipt2 ?? 0));
                
                // Receipts from vouchers
                $rcptSum = (float) DB::table('receipts_vouchers')
                    ->where(function($q) use ($sale) {
                        $q->where('sale_id', $sale->id)
                          ->orWhere('reference_no', $sale->invoice_no);
                    })
                    ->where(function($q) {
                        $q->whereNull('status')->orWhere('status', '!=', 'voided');
                    })
                    ->sum('amount');
                $paid = max($receipts, $rcptSum);

                $correctTotalBalance = ($prev + $netSale) - $paid;

                // If sale total_balance was corrupted (differs by approximately additional_discount)
                if (abs($sale->total_balance - $correctTotalBalance) > 0.01) {
                    $sale->total_balance = $correctTotalBalance;
                    $sale->save();

                    // Also sync the matching customer ledger row
                    $ledger = CustomerLedger::where('customer_id', $sale->customer_id)
                        ->where('reference_id', (string)$sale->id)
                        ->where('transaction_type', 'Sale')
                        ->first();

                    if ($ledger) {
                        $ledger->total_debit = $netSale;
                        $ledger->closing_balance = $correctTotalBalance;
                        $ledger->save();
                    }
                }
            }

            // 2. Automatically reconcile all customer ledgers across the database
            Artisan::call('customer:reconcile-ledger', ['--fix' => true]);

            Log::info("Migration fix_sale_invoice_double_discount_and_ledger executed successfully.");
        } catch (\Throwable $e) {
            Log::error("Migration fix_sale_invoice_double_discount_and_ledger failed: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};

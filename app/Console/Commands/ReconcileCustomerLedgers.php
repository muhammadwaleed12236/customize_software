<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Sale;
use App\Models\ReceiptsVoucher;
use Illuminate\Support\Facades\DB;

class ReconcileCustomerLedgers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:reconcile-ledger {customer_id? : Optional specific customer ID} {--fix : Automatically sync closing balance if mismatch found}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify and reconcile customer ledger closing balances against actual sales and receipts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetId = $this->argument('customer_id');
        $autoFix  = $this->option('fix');

        $this->info("=== CUSTOMER LEDGER RECONCILIATION ===");
        if ($autoFix) {
            $this->warn("Mode: AUTO-FIX ENABLED");
        } else {
            $this->line("Mode: DRY-RUN (Use --fix to automatically correct discrepancies)");
        }

        $query = Customer::query();
        if ($targetId) {
            $query->where('id', $targetId);
        }
        $customers = $query->orderBy('id')->get();

        $discrepancies = 0;

        foreach ($customers as $c) {
            $cid = $c->id;
            $opening = (float) ($c->opening_balance ?? 0);

            // Sum of sales (debits)
            $salesSum = (float) DB::table('sales')
                ->where('customer_id', $cid)
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
                })
                ->sum('total_net');

            // Sum of receipts (credits)
            $receiptsSum = (float) DB::table('receipts_vouchers')
                ->where('party_id', (string) $cid)
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'voided');
                })
                ->sum('amount');

            // Sum of sales returns (credits)
            $returnsSum = (float) DB::table('sales_returns')
                ->join('sales', 'sales.id', '=', 'sales_returns.sale_id')
                ->where('sales.customer_id', $cid)
                ->sum('sales_returns.total_net');

            $trueBalance = $opening + $salesSum - $receiptsSum - $returnsSum;

            // Latest entry in customer_ledgers
            $latestLedger = CustomerLedger::where('customer_id', $cid)->latest('id')->first();
            $ledgerClosing = $latestLedger ? (float) $latestLedger->closing_balance : $opening;

            $diff = round($trueBalance - $ledgerClosing, 2);

            if (abs($diff) > 0.01) {
                $discrepancies++;
                $this->error("\n[MISMATCH] Customer #{$cid} ({$c->customer_name}):");
                $this->line("   Opening:        Rs " . number_format($opening, 2));
                $this->line(" + Sales:          Rs " . number_format($salesSum, 2));
                $this->line(" - Receipts:       Rs " . number_format($receiptsSum, 2));
                $this->line(" - Returns:        Rs " . number_format($returnsSum, 2));
                $this->line(" = Calculated Bal: Rs " . number_format($trueBalance, 2));
                $this->line("   Ledger Balance: Rs " . number_format($ledgerClosing, 2));
                $this->line("   Difference:     Rs " . number_format($diff, 2));

                if ($autoFix) {
                    // 1. Sync all credit sales in CustomerLedger with actual sales & receipts
                    $custSales = Sale::where('customer_id', $cid)->orderBy('id')->get();
                    foreach ($custSales as $s) {
                        $actualReceipts = (float) DB::table('receipts_vouchers')
                            ->where(function ($q) use ($s) {
                                $q->where('sale_id', $s->id)
                                  ->orWhere('reference_no', $s->invoice_no);
                            })
                            ->where(function ($q) {
                                $q->whereNull('status')->orWhere('status', '!=', 'voided');
                            })
                            ->sum('amount');

                        $sLedger = CustomerLedger::where('customer_id', $cid)
                            ->where('reference_id', (string)$s->id)
                            ->where('transaction_type', 'Sale')
                            ->first();

                        if ($sLedger) {
                            $sLedger->total_debit  = (float)($s->total_net ?? 0);
                            $sLedger->total_credit = $actualReceipts;
                            $sLedger->save();
                        }

                        // Remove redundant delta update entries
                        CustomerLedger::where('customer_id', $cid)
                            ->where('reference_id', (string)$s->id)
                            ->where('transaction_type', 'Sale Update')
                            ->delete();
                    }

                    // 2. Recalculate customer running balances end-to-end
                    \App\Services\VoucherService::recalculateCustomerLedger($cid);

                    // 3. Sync sales previous_balance and total_balance with the recalculated ledger
                    foreach ($custSales as $s) {
                        $sLedger = CustomerLedger::where('customer_id', $cid)
                            ->where('reference_id', (string)$s->id)
                            ->where('transaction_type', 'Sale')
                            ->first();
                        if ($sLedger) {
                            $s->previous_balance = (float)$sLedger->previous_balance;
                            $s->total_balance    = (float)$sLedger->closing_balance;
                            $s->save();
                        }
                    }

                    $this->info("   -> Fully reconciled all sales, receipts, and ledger rows for Customer #{$cid}");
                }
            }
        }

        if ($discrepancies === 0) {
            $this->info("\nAll " . $customers->count() . " customer ledgers are 100% MATCHED with zero discrepancies!");
        } else {
            $this->warn("\nTotal discrepancies found: {$discrepancies}");
        }

        return Command::SUCCESS;
    }
}

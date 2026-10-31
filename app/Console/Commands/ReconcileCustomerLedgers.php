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
                    if ($latestLedger) {
                        $latestLedger->closing_balance = $trueBalance;
                        $latestLedger->save();
                        $this->info("   -> Updated latest ledger row #{$latestLedger->id} closing_balance to " . number_format($trueBalance, 2));
                    } else {
                        CustomerLedger::create([
                            'customer_id'      => $cid,
                            'admin_or_user_id' => 1,
                            'transaction_type' => 'Opening Balance',
                            'description'      => 'Reconciled Initial Balance',
                            'opening_balance'  => $opening,
                            'previous_balance' => 0,
                            'total_debit'      => $salesSum,
                            'total_credit'     => $receiptsSum + $returnsSum,
                            'closing_balance'  => $trueBalance,
                        ]);
                        $this->info("   -> Created initial ledger row with closing_balance " . number_format($trueBalance, 2));
                    }
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

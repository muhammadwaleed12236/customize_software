<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\SalesReturn;
use App\Models\AccountLedgerEntry;
use App\Services\VoucherService;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Post Cash Refund of 5,100 PKR for Sale Return INV-0032 (RET-20261020130908)
     * into Cash in Hand account (B11-CASH-001) on date 2026-10-20.
     */
    public function up(): void
    {
        DB::beginTransaction();
        try {
            // 1. Locate the EXACT Cash in Hand Account for Branch 11
            $account = Account::where('account_code', 'B11-CASH-001')
                ->orWhere(function ($q) {
                    $q->where('branch_id', 11)->where('title', 'like', '%Cash%');
                })
                ->first();

            // Fallback: search any cash in hand if branch 11 not matched
            if (!$account) {
                $account = Account::where('title', 'like', '%Cash in Hand%')->first();
            }

            if (!$account) {
                DB::rollBack();
                return;
            }

            // Cleanup any entry accidentally posted to account id 3 if id 3 is different
            if ($account->id !== 3) {
                $wrongEntries = AccountLedgerEntry::where('account_id', 3)
                    ->where('voucher_no', 'RET-20261020130908')
                    ->get();
                foreach ($wrongEntries as $we) {
                    $we->delete();
                }
                AccountLedgerEntry::recalculateRunningBalance(3);
            }

            // 2. Locate the Sales Return record
            $saleReturn = SalesReturn::where('reference', 'RET-20261020130908')
                ->orWhere('id', 3)
                ->first();

            $returnId   = $saleReturn ? $saleReturn->id : 3;
            $returnRef  = $saleReturn ? ($saleReturn->reference ?? 'RET-20261020130908') : 'RET-20261020130908';
            $returnDate = '2026-10-20';
            $amount     = 5100.00;

            // 3. Post ledger entry to Cash in Hand (Credit = Cash outflow / refund)
            VoucherService::postLedgerEntry(
                $account->id,
                'payment',
                $returnRef,
                $returnId,
                $returnDate,
                'Sale Return Cash Refund — ' . $returnRef,
                0,
                $amount,
                1
            );

            // 4. Recalculate running balance in strict chronological order
            AccountLedgerEntry::recalculateRunningBalance($account->id);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();
        try {
            $account = Account::where('account_code', 'B11-CASH-001')
                ->orWhere(function ($q) {
                    $q->where('branch_id', 11)->where('title', 'like', '%Cash%');
                })
                ->first();

            if ($account) {
                AccountLedgerEntry::where('account_id', $account->id)
                    ->where('voucher_type', 'payment')
                    ->where('voucher_no', 'RET-20261020130908')
                    ->delete();

                AccountLedgerEntry::recalculateRunningBalance($account->id);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
};

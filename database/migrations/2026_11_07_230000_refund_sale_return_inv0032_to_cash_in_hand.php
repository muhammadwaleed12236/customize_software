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
     * into Cash in Hand account on date 2026-10-20.
     */
    public function up(): void
    {
        DB::beginTransaction();
        try {
            // 1. Locate Cash in Hand Account (ID 3 or code B11-CASH-001)
            $account = Account::where('id', 3)
                ->orWhere('code', 'B11-CASH-001')
                ->first();

            if (!$account) {
                DB::rollBack();
                return;
            }

            // 2. Locate the Sales Return record
            $saleReturn = SalesReturn::where('id', 3)
                ->orWhere('reference', 'RET-20261020130908')
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
     * Safely rolls back the refund entry and restores the original account balance.
     */
    public function down(): void
    {
        DB::beginTransaction();
        try {
            $account = Account::where('id', 3)
                ->orWhere('code', 'B11-CASH-001')
                ->first();

            if ($account) {
                AccountLedgerEntry::where('account_id', $account->id)
                    ->where('voucher_type', 'payment')
                    ->where(function ($q) {
                        $q->where('voucher_no', 'RET-20261020130908')
                          ->orWhere('voucher_id', 3);
                    })
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

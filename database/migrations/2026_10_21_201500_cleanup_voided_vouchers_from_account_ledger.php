<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\AccountLedgerEntry;
use App\Models\ExpenseVoucher;
use App\Models\PaymentVoucher;
use App\Models\ReceiptsVoucher;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::beginTransaction();

            // 1. Gather all voided voucher numbers
            $voidedEvids = ExpenseVoucher::where('status', 'voided')->pluck('evid')->filter()->toArray();
            $voidedPvids = PaymentVoucher::where('status', 'voided')->pluck('pvid')->filter()->toArray();
            $voidedRvids = ReceiptsVoucher::where('status', 'voided')->pluck('rvid')->filter()->toArray();
            $allVoidedNos = array_unique(array_filter(array_merge($voidedEvids, $voidedPvids, $voidedRvids)));

            $affectedAccountIds = [];

            if (!empty($allVoidedNos)) {
                $affectedAccountIds = DB::table('account_ledger_entries')
                    ->whereIn('voucher_no', $allVoidedNos)
                    ->pluck('account_id')
                    ->unique()
                    ->toArray();

                DB::table('account_ledger_entries')
                    ->whereIn('voucher_no', $allVoidedNos)
                    ->delete();
            }

            // 2. Also ensure Account 12 (Cash in Hand) is refreshed
            $affectedAccountIds[] = 12;

            // 3. Recalculate running balance strictly in date order for all affected accounts
            foreach (array_unique(array_filter($affectedAccountIds)) as $accId) {
                AccountLedgerEntry::recalculateRunningBalance((int)$accId);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Migration cleanup_voided_vouchers_from_account_ledger failed: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data fix: no rollback necessary
    }
};

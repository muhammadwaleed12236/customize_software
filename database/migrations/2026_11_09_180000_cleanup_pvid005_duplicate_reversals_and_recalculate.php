<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\AccountLedgerEntry;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('account_ledger_entries')) {
            return;
        }

        try {
            DB::beginTransaction();

            // 1. Identify any accounts affected by PVID-005
            $affectedAccountIds = DB::table('account_ledger_entries')
                ->where('voucher_no', 'PVID-005')
                ->pluck('account_id')
                ->filter()
                ->unique()
                ->toArray();

            // 2. Delete all synthetic reversal entries specifically for PVID-005
            DB::table('account_ledger_entries')
                ->where('voucher_no', 'PVID-005')
                ->where('description', 'like', 'Reversal of Payment%')
                ->delete();

            // 3. Deduplicate payment voucher entries for PVID-005 (keep only the original MIN(id) per account)
            $duplicates = DB::table('account_ledger_entries')
                ->where('voucher_no', 'PVID-005')
                ->where('voucher_type', 'payment')
                ->select('account_id', DB::raw('MIN(id) as keep_id'), DB::raw('count(*) as count'))
                ->groupBy('account_id')
                ->having('count', '>', 1)
                ->get();

            foreach ($duplicates as $dup) {
                DB::table('account_ledger_entries')
                    ->where('voucher_no', 'PVID-005')
                    ->where('voucher_type', 'payment')
                    ->where('account_id', $dup->account_id)
                    ->where('id', '>', $dup->keep_id)
                    ->delete();
            }

            // 4. Safely recalculate running balance only for the affected accounts
            foreach ($affectedAccountIds as $accId) {
                AccountLedgerEntry::recalculateRunningBalance((int)$accId);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Migration cleanup_pvid005_duplicate_reversals_and_recalculate failed: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cleanup operations are not reversible
    }
};

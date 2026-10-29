<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\AccountLedgerEntry;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::beginTransaction();

            // 1. Delete all synthetic reversal entries created by old voucher edit logic
            DB::table('account_ledger_entries')
                ->where('description', 'like', 'Reversal of Payment%')
                ->delete();

            // 2. Delete duplicate payment voucher entries generated during multi-edits (e.g. PVID-005 2nd edit)
            $duplicateEntries = DB::table('account_ledger_entries')
                ->whereIn('voucher_type', ['payment', 'receipt', 'expense'])
                ->whereNotNull('voucher_id')
                ->where('voucher_id', '>', 0)
                ->select('account_id', 'voucher_type', 'voucher_id', 'voucher_no', 'debit', 'credit', DB::raw('MIN(id) as keep_id'), DB::raw('count(*) as count'))
                ->groupBy('account_id', 'voucher_type', 'voucher_id', 'voucher_no', 'debit', 'credit')
                ->having('count', '>', 1)
                ->get();

            foreach ($duplicateEntries as $dup) {
                DB::table('account_ledger_entries')
                    ->where('account_id', $dup->account_id)
                    ->where('voucher_type', $dup->voucher_type)
                    ->where('voucher_id', $dup->voucher_id)
                    ->where('voucher_no', $dup->voucher_no)
                    ->where('debit', $dup->debit)
                    ->where('credit', $dup->credit)
                    ->where('id', '>', $dup->keep_id)
                    ->delete();
            }

            // 3. Recalculate running balance for all affected accounts
            $affectedAccounts = [12, 10, 24, 1, 33];
            foreach ($affectedAccounts as $accId) {
                AccountLedgerEntry::recalculateRunningBalance((int)$accId);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Migration cleanup_duplicate_payment_reversals_and_entries failed: " . $e->getMessage());
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

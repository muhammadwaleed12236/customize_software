<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentVoucher;
use App\Models\Account;
use App\Models\AccountLedgerEntry;
use App\Services\VoucherService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('payment_vouchers') || !Schema::hasTable('account_ledger_entries')) {
            return;
        }

        try {
            DB::beginTransaction();

            // 1. Sync branch_id of all payment vouchers with their paying account (row_account_id)
            $allPV = PaymentVoucher::all();
            foreach ($allPV as $pv) {
                $accIds = VoucherService::safeDecodeArray($pv->row_account_id);
                if (!empty($accIds)) {
                    $acc = Account::find($accIds[0]);
                    if ($acc && $acc->branch_id && (int)$acc->branch_id !== (int)$pv->branch_id) {
                        DB::table('payment_vouchers')
                            ->where('id', $pv->id)
                            ->update(['branch_id' => (int)$acc->branch_id]);
                    }
                }
            }

            // Ensure PVID-005 explicitly has branch_id = 11 (ibrahim)
            DB::table('payment_vouchers')
                ->where('pvid', 'PVID-005')
                ->orWhere('id', 5)
                ->update(['branch_id' => 11]);

            // 2. Clean up any synthetic reversal entries created by old edit logic
            DB::table('account_ledger_entries')
                ->where('description', 'like', 'Reversal of Payment%')
                ->delete();

            // 3. For PVID-005 specifically:
            // Ensure only genuine payment entries exist with transaction_date = '2026-10-04'
            // Delete duplicate entries for PVID-005, keeping the correct one per account
            $pvid005Entries = DB::table('account_ledger_entries')
                ->where('voucher_no', 'PVID-005')
                ->where('voucher_type', 'payment')
                ->get();

            // Group by account_id and remove duplicates
            $grouped = $pvid005Entries->groupBy('account_id');
            foreach ($grouped as $accId => $entries) {
                if ($entries->count() > 1) {
                    // Prefer entry with date 2026-10-04, otherwise take first
                    $keep = $entries->firstWhere('transaction_date', '2026-10-04') ?? $entries->first();
                    foreach ($entries as $e) {
                        if ($e->id !== $keep->id) {
                            DB::table('account_ledger_entries')->where('id', $e->id)->delete();
                        }
                    }
                    // Ensure date is 2026-10-04
                    DB::table('account_ledger_entries')->where('id', $keep->id)->update([
                        'transaction_date' => '2026-10-04',
                        'branch_id'        => (int)($accId == 12 ? 11 : ($accId == 10 ? 12 : $keep->branch_id)),
                    ]);
                } else {
                    $single = $entries->first();
                    if ($single) {
                        DB::table('account_ledger_entries')->where('id', $single->id)->update([
                            'transaction_date' => '2026-10-04',
                            'branch_id'        => (int)($accId == 12 ? 11 : ($accId == 10 ? 12 : $single->branch_id)),
                        ]);
                    }
                }
            }

            // 4. Recalculate running balance for affected accounts
            $affectedAccounts = [12, 10, 1, 33, 24];
            foreach ($affectedAccounts as $accId) {
                if (Account::where('id', $accId)->exists()) {
                    AccountLedgerEntry::recalculateRunningBalance((int)$accId);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Migration fix_payment_vouchers_branch_mismatch_and_cleanup failed: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cleanup and sync operations are not reversible
    }
};

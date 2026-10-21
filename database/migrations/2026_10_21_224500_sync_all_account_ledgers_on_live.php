<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Account;
use App\Models\AccountLedgerEntry;
use App\Http\Controllers\AccountsHeadController;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            $accounts = Account::all();
            foreach ($accounts as $account) {
                AccountsHeadController::syncAccountLedger($account);
                AccountLedgerEntry::recalculateRunningBalance((int)$account->id);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Migration sync_all_account_ledgers_on_live failed: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback necessary
    }
};

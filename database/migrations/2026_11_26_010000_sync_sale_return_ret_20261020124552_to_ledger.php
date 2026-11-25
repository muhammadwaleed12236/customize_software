<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Http\Controllers\AccountsHeadController;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update date of sales_returns record RET-20261020124552 to 19-10-2026
        DB::table('sales_returns')
            ->where('reference', 'RET-20261020124552')
            ->update([
                'created_at' => '2026-10-19 12:46:28',
                'updated_at' => '2026-10-19 12:46:28',
            ]);

        // 2. Trigger auto-sync on Cash in Hand account for Smart Electronics (Branch 12)
        $cashAccounts = Account::where(function ($q) {
            $q->where('head_id', 1)
              ->orWhere('title', 'like', '%cash in hand%')
              ->orWhere('account_code', 'like', '%CASH%');
        })->get();

        foreach ($cashAccounts as $account) {
            try {
                AccountsHeadController::syncAccountLedger($account);
            } catch (\Exception $e) {
                // Keep resilient
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep intact
    }
};

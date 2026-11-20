<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentVoucher;
use App\Models\Account;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Specifically fix PVID-183 to Ibrahim Branch (11)
        DB::table('payment_vouchers')
            ->where('pvid', 'PVID-183')
            ->update(['branch_id' => 11]);

        // 2. Scan all payment vouchers and sync branch_id with paying account branch if mismatched
        $vouchers = PaymentVoucher::all();
        foreach ($vouchers as $pv) {
            $rowAccs = \App\Services\VoucherService::safeDecodeArray($pv->row_account_id);
            if (!empty($rowAccs)) {
                $acc = Account::find($rowAccs[0]);
                if ($acc && $acc->branch_id && (int)$acc->branch_id !== (int)$pv->branch_id) {
                    DB::table('payment_vouchers')
                        ->where('id', $pv->id)
                        ->update(['branch_id' => (int)$acc->branch_id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed
    }
};

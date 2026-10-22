<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Account;
use App\Models\AccountLedgerEntry;
use App\Models\PaymentVoucher;
use App\Services\VoucherService;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fixes accounts whose opening_balance was artificially inflated by old VoucherService bug.
     */
    public function up(): void
    {
        $accounts = Account::all();

        foreach ($accounts as $acc) {
            $pvs = PaymentVoucher::where('status', '!=', 'voided')->get();
            $pvPartySum = 0.0;

            foreach ($pvs as $pv) {
                $pIds = VoucherService::safeDecodeArray($pv->party_id);
                $pAmts = VoucherService::safeDecodeArray($pv->party_amount);
                $pTypes = VoucherService::safeDecodeArray($pv->type);
                if (empty($pIds) && !empty($pv->party_id)) {
                    $pIds = [$pv->party_id];
                    $pAmts = [(float)$pv->total_amount];
                    $pTypes = [$pv->type];
                }

                foreach ($pIds as $idx => $pId) {
                    if ((int)$pId === (int)$acc->id) {
                        $pType = strtolower(trim((string)($pTypes[$idx] ?? ($pTypes[0] ?? ''))));
                        if (!in_array($pType, ['vendor', 'customer', 'walkin', 'sale_receipt'])) {
                            $amt = isset($pAmts[$idx]) && is_numeric($pAmts[$idx])
                                ? (float)$pAmts[$idx]
                                : (count($pIds) === 1 ? (float)$pv->total_amount : 0.0);
                            $pvPartySum += $amt;
                        }
                    }
                }
            }

            if ($pvPartySum > 0 && abs((float)$acc->opening_balance - $pvPartySum) < 0.01) {
                $acc->opening_balance = 0.00;
                $acc->save();

                $ob = AccountLedgerEntry::where('account_id', $acc->id)
                    ->where('voucher_type', 'opening_balance')
                    ->first();
                if ($ob) {
                    $ob->debit = 0.00;
                    $ob->credit = 0.00;
                    $ob->running_balance = 0.00;
                    $ob->save();
                }

                AccountLedgerEntry::recalculateRunningBalance((int)$acc->id);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible data correction
    }
};

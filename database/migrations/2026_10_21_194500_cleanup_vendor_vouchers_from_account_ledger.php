<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\AccountLedgerEntry;
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

            // 1. Specifically delete known corrupt vendor voucher entries from account_ledger_entries
            DB::table('account_ledger_entries')
                ->where('account_id', 10)
                ->whereIn('voucher_no', ['PVID-067', 'PVID-068'])
                ->delete();

            // 2. Scan and clean any other account ledger entries where payment/receipt voucher belongs to a vendor or customer
            $partyEntries = AccountLedgerEntry::whereIn('voucher_type', ['payment', 'receipt'])
                ->where('description', 'like', '%Party Side%')
                ->get();

            $affectedAccountIds = [10];

            foreach ($partyEntries as $entry) {
                if ($entry->voucher_type === 'payment') {
                    $pv = PaymentVoucher::where('pvid', $entry->voucher_no)
                        ->orWhere('id', $entry->voucher_id)
                        ->first();
                    if ($pv) {
                        $types = is_array(json_decode($pv->type, true)) ? json_decode($pv->type, true) : [$pv->type];
                        $hasNumeric = false;
                        foreach ($types as $t) {
                            if (is_numeric($t)) {
                                $hasNumeric = true;
                                break;
                            }
                        }
                        if (!$hasNumeric) {
                            $affectedAccountIds[] = $entry->account_id;
                            $entry->delete();
                        }
                    }
                } elseif ($entry->voucher_type === 'receipt') {
                    $rv = ReceiptsVoucher::where('rvid', $entry->voucher_no)
                        ->orWhere('id', $entry->voucher_id)
                        ->first();
                    if ($rv) {
                        $types = is_array(json_decode($rv->type, true)) ? json_decode($rv->type, true) : [$rv->type];
                        $hasNumeric = false;
                        foreach ($types as $t) {
                            if (is_numeric($t)) {
                                $hasNumeric = true;
                                break;
                            }
                        }
                        if (!$hasNumeric) {
                            $affectedAccountIds[] = $entry->account_id;
                            $entry->delete();
                        }
                    }
                }
            }

            // 3. Recalculate running balance strictly in date order for affected accounts
            foreach (array_unique(array_filter($affectedAccountIds)) as $accId) {
                AccountLedgerEntry::recalculateRunningBalance((int)$accId);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Migration cleanup_vendor_vouchers_from_account_ledger failed: " . $e->getMessage());
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\AccountLedgerEntry;
use App\Models\PaymentVoucher;
use App\Models\ReceiptsVoucher;
use App\Services\VoucherService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::beginTransaction();

            // 1. Locate H.S Accounts
            $hsIbrahim = Account::where(function($q) {
                $q->where('id', 24)
                  ->orWhere(function($sub) {
                      $sub->where('branch_id', 11)->where('title', 'like', 'H.S%');
                  });
            })->first();

            $hsSmart = Account::where(function($q) {
                $q->where('id', 10)
                  ->orWhere(function($sub) {
                      $sub->where('branch_id', 12)->where('title', 'like', 'H.S%');
                  });
            })->first();

            // Cash in Hand for Ibrahim
            $cashIbrahim = Account::where('branch_id', 11)
                ->where(function($q) {
                    $q->where('head_id', 1)->orWhere('title', 'like', '%Cash%');
                })->first();
            $cashIbrahimId = $cashIbrahim?->id ?? 12;

            // Reset H.S opening balances to clean base values
            if ($hsIbrahim) {
                $hsIbrahim->update([
                    'opening_balance' => 0.00,
                    'type' => 'Debit'
                ]);
                DB::table('account_ledger_entries')
                    ->where('account_id', $hsIbrahim->id)
                    ->where('voucher_type', 'opening_balance')
                    ->update([
                        'debit' => 0.00,
                        'credit' => 0.00,
                        'running_balance' => 0.00
                    ]);
            }

            if ($hsSmart) {
                $hsSmart->update([
                    'opening_balance' => 28500.00,
                    'type' => 'Debit'
                ]);
                DB::table('account_ledger_entries')
                    ->where('account_id', $hsSmart->id)
                    ->where('voucher_type', 'opening_balance')
                    ->update([
                        'debit' => 28500.00,
                        'credit' => 0.00,
                        'running_balance' => 28500.00
                    ]);
            }

            // 2. Re-route walk-in cash sales mistakenly posted to H.S Ibrahim
            $walkInInvoices = ['INV-0055', 'INV-0094', 'INV-0095', 'INV-0097', 'INV-0098'];
            $mistargetedRvs = ReceiptsVoucher::where('type', 'SALE_RECEIPT')
                ->whereIn('reference_no', $walkInInvoices)
                ->get();

            foreach ($mistargetedRvs as $rv) {
                $rv->update(['row_account_id' => $cashIbrahimId]);
                DB::table('account_ledger_entries')
                    ->where('voucher_id', $rv->id)
                    ->where('voucher_type', 'receipt')
                    ->update(['account_id' => $cashIbrahimId]);
            }

            // 3. Remove synthetic reversal entries for Payment Vouchers
            DB::table('account_ledger_entries')
                ->where('description', 'like', 'Reversal of Payment%')
                ->delete();

            // 4. Clean old payment voucher entries for PVID-001 through PVID-018
            $pvIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 13, 14, 15, 16, 17, 18];
            DB::table('account_ledger_entries')
                ->where(function($q) use ($pvIds) {
                    $q->whereIn('voucher_id', $pvIds)
                      ->where('voucher_type', 'payment');
                })
                ->orWhere(function($q) {
                    $q->where('voucher_no', 'like', 'PVID-%')
                      ->where('voucher_type', 'payment')
                      ->whereNotIn('voucher_no', ['PVID-010', 'PVID-019']);
                })
                ->delete();

            // 5. Re-apply clean payment vouchers
            $pvs = PaymentVoucher::whereIn('id', $pvIds)
                ->where('status', 'active')
                ->orderBy('id')
                ->get();

            foreach ($pvs as $pv) {
                $data = [
                    'total_amount'     => (float)$pv->total_amount,
                    'branch_id'        => (int)$pv->branch_id,
                    'receipt_date'     => $pv->receipt_date,
                    'remarks'          => $pv->remarks,
                    'row_account_id'   => VoucherService::safeDecodeArray($pv->row_account_id),
                    'amount'           => VoucherService::safeDecodeArray($pv->amount),
                    'party_id'         => VoucherService::safeDecodeArray($pv->party_id),
                    'party_type'       => VoucherService::safeDecodeArray($pv->type),
                    'party_amount'     => VoucherService::safeDecodeArray($pv->party_amount),
                ];

                VoucherService::applyPaymentVoucher($pv, $data, 1);
            }

            // 6. Recalculate running balance strictly in date order for affected accounts
            $affected = array_filter(array_unique([
                $hsIbrahim?->id ?? 24,
                $hsSmart?->id ?? 10,
                $cashIbrahimId,
                1, 11, 33
            ]));

            foreach ($affected as $accId) {
                AccountLedgerEntry::recalculateRunningBalance((int)$accId);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Migration repair_hs_receivable_ledger_data failed: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data fix migration: no rollback necessary
    }
};

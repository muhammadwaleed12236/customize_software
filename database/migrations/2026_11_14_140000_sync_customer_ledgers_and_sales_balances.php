<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Services\VoucherService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sync created_at in customer_ledgers for Receipt Vouchers to match voucher receipt_date
        $receipts = DB::table('receipts_vouchers')
            ->whereNotNull('receipt_date')
            ->whereNotNull('rvid')
            ->select('rvid', 'receipt_date')
            ->get();

        foreach ($receipts as $rv) {
            DB::table('customer_ledgers')
                ->where('reference_id', $rv->rvid)
                ->update([
                    'created_at' => $rv->receipt_date . ' 12:00:00'
                ]);
        }

        // 2. Sync created_at in customer_ledgers for Payment Vouchers
        $payments = DB::table('payment_vouchers')
            ->whereNotNull('receipt_date')
            ->whereNotNull('pvid')
            ->select('pvid', 'receipt_date')
            ->get();

        foreach ($payments as $pv) {
            DB::table('customer_ledgers')
                ->where('reference_id', $pv->pvid)
                ->update([
                    'created_at' => $pv->receipt_date . ' 12:00:00'
                ]);
        }

        // 3. Recalculate all customer ledgers and sync sales table
        $customerIds = DB::table('customer_ledgers')
            ->distinct()
            ->pluck('customer_id')
            ->toArray();

        foreach ($customerIds as $cid) {
            VoucherService::recalculateCustomerLedger((int)$cid);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive data sync migration
    }
};

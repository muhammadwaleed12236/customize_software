<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::transaction(function () {
                // 1. Correct any sale where total_net was corrupted or doubled
                // Fix specific known invoices
                DB::table('sales')->where('invoice_no', 'INV-0026')->update(['total_net' => 51000.00]);
                DB::table('sales')->where('invoice_no', 'INV-0027')->update(['total_net' => 56000.00]);
                DB::table('sales')->where('invoice_no', 'INV-0038')->update(['total_net' => 58900.00]);
                DB::table('sales')->where('invoice_no', 'INV-0060')->update(['total_net' => 19250.00]);

                // Also general safeguard: if any sale has sub_total2 > 0 and total_net > sub_total2, fix it
                DB::statement("UPDATE sales SET total_net = sub_total2 WHERE sub_total2 > 0 AND total_net > sub_total2");

                // If total_net is NULL or 0, set it to sub_total2 or sub_total1
                DB::statement("UPDATE sales SET total_net = sub_total2 WHERE (total_net IS NULL OR total_net <= 0) AND sub_total2 > 0");
                DB::statement("UPDATE sales SET total_net = sub_total1 WHERE (total_net IS NULL OR total_net <= 0) AND sub_total1 > 0");

                // 2. Fix Customer 32 (AL AHSAN MOBILE BHALWAL) ledger
                DB::table('customer_ledgers')->where('customer_id', 32)->where('transaction_type', 'Sale')->where('reference_id', '28')->update([
                    'total_debit' => 51000.00,
                    'total_credit' => 51000.00,
                    'closing_balance' => 0.00
                ]);
                DB::table('customer_ledgers')->where('customer_id', 32)->where('transaction_type', 'Sale Update')->where('reference_id', '28')->delete();

                // 3. Fix Customer 33 (GH MOBILE MILTAN) ledger
                DB::table('customer_ledgers')->where('customer_id', 33)->where('transaction_type', 'Sale')->where('reference_id', '29')->update([
                    'total_debit' => 56000.00,
                    'total_credit' => 56000.00,
                    'closing_balance' => 0.00
                ]);
                DB::table('customer_ledgers')->where('customer_id', 33)->where('transaction_type', 'Sale Update')->where('reference_id', '29')->delete();

                // 4. Fix Customer 40 ledger
                DB::table('customer_ledgers')->where('customer_id', 40)->where('reference_id', '40')->update([
                    'transaction_type' => 'Sale',
                    'description' => 'Sale Invoice #INV-0038',
                    'previous_balance' => 34000.00,
                    'total_debit' => 58900.00,
                    'total_credit' => 92900.00,
                    'closing_balance' => 0.00
                ]);

                // 5. Fix Customer 28 ledger
                DB::table('customer_ledgers')->where('customer_id', 28)->where('transaction_type', 'Sale')->where('reference_id', '62')->update([
                    'total_debit' => 19250.00,
                    'total_credit' => 30000.00,
                    'closing_balance' => 55930.00
                ]);
                DB::table('customer_ledgers')->where('customer_id', 28)->where('transaction_type', 'Sale Update')->where('reference_id', '62')->delete();
            });

            Log::info("Migration fix_invoice_total_net_and_customer_ledger completed successfully.");
        } catch (\Throwable $e) {
            Log::error("Migration fix_invoice_total_net_and_customer_ledger failed: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('receipts_vouchers') && !Schema::hasColumn('receipts_vouchers', 'party_amount')) {
            Schema::table('receipts_vouchers', function (Blueprint $table) {
                $table->text('party_amount')->nullable()->after('tel');
            });
        }

        if (Schema::hasTable('payment_vouchers') && !Schema::hasColumn('payment_vouchers', 'party_amount')) {
            Schema::table('payment_vouchers', function (Blueprint $table) {
                $table->text('party_amount')->nullable()->after('tel');
            });
        }

        if (Schema::hasTable('expense_vouchers') && !Schema::hasColumn('expense_vouchers', 'party_amount')) {
            Schema::table('expense_vouchers', function (Blueprint $table) {
                $table->text('party_amount')->nullable()->after('tel');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('receipts_vouchers') && Schema::hasColumn('receipts_vouchers', 'party_amount')) {
            Schema::table('receipts_vouchers', function (Blueprint $table) {
                $table->dropColumn('party_amount');
            });
        }

        if (Schema::hasTable('payment_vouchers') && Schema::hasColumn('payment_vouchers', 'party_amount')) {
            Schema::table('payment_vouchers', function (Blueprint $table) {
                $table->dropColumn('party_amount');
            });
        }

        if (Schema::hasTable('expense_vouchers') && Schema::hasColumn('expense_vouchers', 'party_amount')) {
            Schema::table('expense_vouchers', function (Blueprint $table) {
                $table->dropColumn('party_amount');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('payment_vouchers') && !Schema::hasColumn('payment_vouchers', 'party_description')) {
            Schema::table('payment_vouchers', function (Blueprint $table) {
                $table->text('party_description')->nullable()->after('party_amount');
            });
        }

        if (Schema::hasTable('expense_vouchers') && !Schema::hasColumn('expense_vouchers', 'party_description')) {
            Schema::table('expense_vouchers', function (Blueprint $table) {
                $table->text('party_description')->nullable()->after('party_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payment_vouchers') && Schema::hasColumn('payment_vouchers', 'party_description')) {
            Schema::table('payment_vouchers', function (Blueprint $table) {
                $table->dropColumn('party_description');
            });
        }

        if (Schema::hasTable('expense_vouchers') && Schema::hasColumn('expense_vouchers', 'party_description')) {
            Schema::table('expense_vouchers', function (Blueprint $table) {
                $table->dropColumn('party_description');
            });
        }
    }
};

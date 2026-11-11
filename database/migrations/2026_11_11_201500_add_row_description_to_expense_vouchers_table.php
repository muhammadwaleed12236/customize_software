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
        if (Schema::hasTable('expense_vouchers') && !Schema::hasColumn('expense_vouchers', 'row_description')) {
            Schema::table('expense_vouchers', function (Blueprint $table) {
                $table->text('row_description')->nullable()->after('row_account_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('expense_vouchers') && Schema::hasColumn('expense_vouchers', 'row_description')) {
            Schema::table('expense_vouchers', function (Blueprint $table) {
                $table->dropColumn('row_description');
            });
        }
    }
};

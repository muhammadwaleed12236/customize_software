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
        Schema::table('expense_vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('expense_vouchers', 'processed')) {
                $table->boolean('processed')->default(false)->after('total_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('expense_vouchers', 'processed')) {
                $table->dropColumn('processed');
            }
        });
    }
};

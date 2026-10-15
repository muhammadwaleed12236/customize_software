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
        Schema::table('payment_vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_vouchers', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->default(1)->after('id')->index();
            }
            if (!Schema::hasColumn('payment_vouchers', 'processed')) {
                $table->boolean('processed')->default(false)->after('total_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('payment_vouchers', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
            if (Schema::hasColumn('payment_vouchers', 'processed')) {
                $table->dropColumn('processed');
            }
        });
    }
};

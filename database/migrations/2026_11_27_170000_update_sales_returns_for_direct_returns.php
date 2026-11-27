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
        Schema::table('sales_returns', function (Blueprint $table) {
            // Drop existing foreign key so sale_id can be nullable
            try {
                $table->dropForeign(['sale_id']);
            } catch (\Exception $e) {
                // Already dropped or doesn't exist
            }
        });

        Schema::table('sales_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_id')->nullable()->change();

            if (!Schema::hasColumn('sales_returns', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('sale_id');
            }
            if (!Schema::hasColumn('sales_returns', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('branch_id');
            }
            if (!Schema::hasColumn('sales_returns', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('warehouse_id');
            }
            if (!Schema::hasColumn('sales_returns', 'is_direct')) {
                $table->boolean('is_direct')->default(0)->after('customer_id');
            }
            if (!Schema::hasColumn('sales_returns', 'refund_type')) {
                $table->string('refund_type')->nullable()->after('is_direct');
            }
        });

        // Re-add foreign key on sale_id with set null on delete
        Schema::table('sales_returns', function (Blueprint $table) {
            try {
                $table->foreign('sale_id')->references('id')->on('sales')->nullOnDelete();
            } catch (\Exception $e) {
                // Ignore if not supported
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            if (Schema::hasColumn('sales_returns', 'refund_type')) {
                $table->dropColumn('refund_type');
            }
            if (Schema::hasColumn('sales_returns', 'is_direct')) {
                $table->dropColumn('is_direct');
            }
            if (Schema::hasColumn('sales_returns', 'customer_id')) {
                $table->dropColumn('customer_id');
            }
            if (Schema::hasColumn('sales_returns', 'warehouse_id')) {
                $table->dropColumn('warehouse_id');
            }
            if (Schema::hasColumn('sales_returns', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
        });
    }
};

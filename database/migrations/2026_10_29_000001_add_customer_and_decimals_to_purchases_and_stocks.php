<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add customer_id & party_type to purchases table
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('vendor_id')->index();
            }
            if (!Schema::hasColumn('purchases', 'party_type')) {
                $table->string('party_type', 50)->default('vendor')->after('customer_id')->index();
            }
        });

        // 2. Add purchase_party_source to sale_settings table
        Schema::table('sale_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_settings', 'purchase_party_source')) {
                $table->string('purchase_party_source', 50)->default('both')->after('warehouse_mode');
            }
        });

        // 3. Alter integer stock and qty columns to DECIMAL(15,2) to support decimal quantities and points (e.g. 1200.14)
        DB::statement("ALTER TABLE purchase_items MODIFY qty DECIMAL(15,2) NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE purchase_items MODIFY price DECIMAL(15,2) NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE purchase_items MODIFY line_total DECIMAL(15,2) NOT NULL DEFAULT 0");

        DB::statement("ALTER TABLE stocks MODIFY qty DECIMAL(15,2) NOT NULL DEFAULT 0");
        if (Schema::hasColumn('stocks', 'reserved_qty')) {
            DB::statement("ALTER TABLE stocks MODIFY reserved_qty DECIMAL(15,2) NOT NULL DEFAULT 0");
        }

        DB::statement("ALTER TABLE warehouse_stocks MODIFY quantity DECIMAL(15,2) NOT NULL DEFAULT 0");
        if (Schema::hasColumn('warehouse_stocks', 'reserved_qty')) {
            DB::statement("ALTER TABLE warehouse_stocks MODIFY reserved_qty DECIMAL(15,2) NOT NULL DEFAULT 0");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'customer_id')) {
                $table->dropColumn('customer_id');
            }
            if (Schema::hasColumn('purchases', 'party_type')) {
                $table->dropColumn('party_type');
            }
        });

        Schema::table('sale_settings', function (Blueprint $table) {
            if (Schema::hasColumn('sale_settings', 'purchase_party_source')) {
                $table->dropColumn('purchase_party_source');
            }
        });
    }
};

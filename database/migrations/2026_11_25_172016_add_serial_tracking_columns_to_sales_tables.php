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
        if (Schema::hasTable('sale_items')) {
            Schema::table('sale_items', function (Blueprint $table) {
                if (!Schema::hasColumn('sale_items', 'serial_numbers')) {
                    $table->text('serial_numbers')->nullable()->after('amount');
                }
            });
        }

        if (Schema::hasTable('product_booking_items')) {
            Schema::table('product_booking_items', function (Blueprint $table) {
                if (!Schema::hasColumn('product_booking_items', 'serial_numbers')) {
                    $table->text('serial_numbers')->nullable()->after('amount');
                }
            });
        }

        if (Schema::hasTable('product_serials')) {
            Schema::table('product_serials', function (Blueprint $table) {
                if (!Schema::hasColumn('product_serials', 'sale_id')) {
                    $table->unsignedBigInteger('sale_id')->nullable()->index()->after('source_id');
                }
                if (!Schema::hasColumn('product_serials', 'sale_item_id')) {
                    $table->unsignedBigInteger('sale_item_id')->nullable()->index()->after('sale_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sale_items')) {
            Schema::table('sale_items', function (Blueprint $table) {
                if (Schema::hasColumn('sale_items', 'serial_numbers')) {
                    $table->dropColumn('serial_numbers');
                }
            });
        }

        if (Schema::hasTable('product_booking_items')) {
            Schema::table('product_booking_items', function (Blueprint $table) {
                if (Schema::hasColumn('product_booking_items', 'serial_numbers')) {
                    $table->dropColumn('serial_numbers');
                }
            });
        }

        if (Schema::hasTable('product_serials')) {
            Schema::table('product_serials', function (Blueprint $table) {
                if (Schema::hasColumn('product_serials', 'sale_item_id')) {
                    $table->dropColumn('sale_item_id');
                }
                if (Schema::hasColumn('product_serials', 'sale_id')) {
                    $table->dropColumn('sale_id');
                }
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add delivered_qty to product_booking_items
        Schema::table('product_booking_items', function (Blueprint $table) {
            $table->decimal('delivered_qty', 12, 2)->default(0)->after('sales_qty');
        });

        // Add delivery_status to productbookings
        Schema::table('productbookings', function (Blueprint $table) {
            $table->string('delivery_status')->default('pending')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('product_booking_items', function (Blueprint $table) {
            $table->dropColumn('delivered_qty');
        });

        Schema::table('productbookings', function (Blueprint $table) {
            $table->dropColumn('delivery_status');
        });
    }
};

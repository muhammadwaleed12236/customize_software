<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_booking_items') && !Schema::hasColumn('product_booking_items', 'watt')) {
            Schema::table('product_booking_items', function (Blueprint $table) {
                $table->decimal('watt', 10, 2)->default(0)->after('product_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_booking_items') && Schema::hasColumn('product_booking_items', 'watt')) {
            Schema::table('product_booking_items', function (Blueprint $table) {
                $table->dropColumn('watt');
            });
        }
    }
};

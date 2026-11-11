<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_settings', 'show_received_account')) {
                $table->boolean('show_received_account')->default(false)->after('invoice_pcs_format');
            }
            if (!Schema::hasColumn('sale_settings', 'show_delivery_remaining_qty')) {
                $table->boolean('show_delivery_remaining_qty')->default(true)->after('show_received_account');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_settings', function (Blueprint $table) {
            if (Schema::hasColumn('sale_settings', 'show_received_account')) {
                $table->dropColumn('show_received_account');
            }
            if (Schema::hasColumn('sale_settings', 'show_delivery_remaining_qty')) {
                $table->dropColumn('show_delivery_remaining_qty');
            }
        });
    }
};

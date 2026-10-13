<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_settings', 'show_watt')) {
                $table->boolean('show_watt')->default(false)->after('show_overall_discount');
            }
            if (!Schema::hasColumn('sale_settings', 'warehouse_mode')) {
                $table->string('warehouse_mode', 20)->default('global')->after('show_watt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_settings', function (Blueprint $table) {
            if (Schema::hasColumn('sale_settings', 'show_watt')) {
                $table->dropColumn('show_watt');
            }
            if (Schema::hasColumn('sale_settings', 'warehouse_mode')) {
                $table->dropColumn('warehouse_mode');
            }
        });
    }
};

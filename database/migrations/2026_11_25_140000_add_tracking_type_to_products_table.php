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
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'tracking_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('tracking_type', 50)->default('standard')->after('unit_id')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'tracking_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('tracking_type');
            });
        }
    }
};

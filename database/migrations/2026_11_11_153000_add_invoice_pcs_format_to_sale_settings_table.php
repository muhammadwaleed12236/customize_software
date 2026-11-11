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
        if (Schema::hasTable('sale_settings') && !Schema::hasColumn('sale_settings', 'invoice_pcs_format')) {
            Schema::table('sale_settings', function (Blueprint $table) {
                $table->string('invoice_pcs_format')->default('pcs')->after('invoice_customer_name_format');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sale_settings') && Schema::hasColumn('sale_settings', 'invoice_pcs_format')) {
            Schema::table('sale_settings', function (Blueprint $table) {
                $table->dropColumn('invoice_pcs_format');
            });
        }
    }
};

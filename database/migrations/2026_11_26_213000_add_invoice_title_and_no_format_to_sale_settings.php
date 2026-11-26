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
        if (Schema::hasTable('sale_settings')) {
            Schema::table('sale_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('sale_settings', 'invoice_title_type')) {
                    $table->string('invoice_title_type')->default('invoice')->after('invoice_pcs_format');
                }
                if (!Schema::hasColumn('sale_settings', 'invoice_no_format')) {
                    $table->string('invoice_no_format')->default('full')->after('invoice_pcs_format');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sale_settings')) {
            Schema::table('sale_settings', function (Blueprint $table) {
                if (Schema::hasColumn('sale_settings', 'invoice_no_format')) {
                    $table->dropColumn('invoice_no_format');
                }
                if (Schema::hasColumn('sale_settings', 'invoice_title_type')) {
                    $table->dropColumn('invoice_title_type');
                }
            });
        }
    }
};

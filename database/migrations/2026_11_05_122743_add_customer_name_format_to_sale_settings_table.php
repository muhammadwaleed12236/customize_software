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
        Schema::table('sale_settings', function (Blueprint $table) {
            $table->string('invoice_customer_name_format')->default('both')->after('invoice_show_watermark');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_settings', function (Blueprint $table) {
            $table->dropColumn('invoice_customer_name_format');
        });
    }
};

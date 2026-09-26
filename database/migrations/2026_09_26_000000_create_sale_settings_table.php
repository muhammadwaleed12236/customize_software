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
        Schema::create('sale_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('show_gst')->default(true);
            $table->boolean('show_line_discount')->default(true);
            $table->boolean('show_overall_discount')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_settings');
    }
};

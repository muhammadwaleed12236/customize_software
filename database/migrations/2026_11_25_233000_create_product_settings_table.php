<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_settings')) {
            Schema::create('product_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('show_tracking_type')->default(true);
                $table->boolean('show_packaging_type')->default(true);
                $table->boolean('show_item_name_urdu')->default(true);
                $table->boolean('show_model')->default(true);
                $table->boolean('show_hs_code')->default(true);
                $table->boolean('show_color')->default(true);
                $table->boolean('show_type')->default(true);
                $table->boolean('show_brand')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_settings');
    }
};

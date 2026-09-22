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
        if (!Schema::hasColumn('products', 'item_name_urdu')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('item_name_urdu')->nullable()->after('item_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'item_name_urdu')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('item_name_urdu');
            });
        }
    }
};

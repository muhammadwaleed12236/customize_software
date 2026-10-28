<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'branch_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('name')->index();
            });
        }

        if (Schema::hasTable('subcategories') && !Schema::hasColumn('subcategories', 'branch_id')) {
            Schema::table('subcategories', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('category_id')->index();
            });
        }

        // Backfill existing categories and subcategories to Branch 1 (or first associated product's branch)
        DB::table('categories')->whereNull('branch_id')->update(['branch_id' => 1]);
        DB::table('subcategories')->whereNull('branch_id')->update(['branch_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'branch_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasTable('subcategories') && Schema::hasColumn('subcategories', 'branch_id')) {
            Schema::table('subcategories', function (Blueprint $table) {
                $table->dropColumn('branch_id');
            });
        }
    }
};

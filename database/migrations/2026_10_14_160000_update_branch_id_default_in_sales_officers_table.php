<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('sales_officers', 'branch_id')) {
            // Drop foreign key if exists, update column default to NULL, and re-add foreign key
            try {
                Schema::table('sales_officers', function (Blueprint $table) {
                    $table->dropForeign(['branch_id']);
                });
            } catch (\Throwable $e) {
                // Foreign key might have a different name or not exist
            }

            try {
                DB::statement("ALTER TABLE sales_officers MODIFY branch_id BIGINT UNSIGNED NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                // Fallback via schema builder if DB statement fails
                Schema::table('sales_officers', function (Blueprint $table) {
                    $table->unsignedBigInteger('branch_id')->nullable()->default(null)->change();
                });
            }

            try {
                Schema::table('sales_officers', function (Blueprint $table) {
                    $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // If re-adding fails (e.g. already exists)
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op rollback
    }
};

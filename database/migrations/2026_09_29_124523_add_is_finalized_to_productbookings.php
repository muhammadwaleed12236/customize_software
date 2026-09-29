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
        Schema::table('productbookings', function (Blueprint $table) {
            if (!Schema::hasColumn('productbookings', 'is_finalized')) {
                $table->boolean('is_finalized')->default(0)->after('is_posted');
            }
            if (!Schema::hasColumn('productbookings', 'is_posted')) {
                $table->boolean('is_posted')->default(0)->after('total_balance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productbookings', function (Blueprint $table) {
            $table->dropColumnIfExists('is_finalized');
        });
    }
};

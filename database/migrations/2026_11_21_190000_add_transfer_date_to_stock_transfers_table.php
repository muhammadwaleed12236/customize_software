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
        Schema::table('stock_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_transfers', 'transfer_date')) {
                $table->dateTime('transfer_date')->nullable()->after('quantity');
            }
        });

        // Set transfer_date for existing records from created_at
        DB::table('stock_transfers')
            ->whereNull('transfer_date')
            ->update([
                'transfer_date' => DB::raw('created_at')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            if (Schema::hasColumn('stock_transfers', 'transfer_date')) {
                $table->dropColumn('transfer_date');
            }
        });
    }
};

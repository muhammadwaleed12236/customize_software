<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('customer_opening_balance_histories', 'resulting_closing_balance')) {
            Schema::table('customer_opening_balance_histories', function (Blueprint $table) {
                $table->decimal('resulting_closing_balance', 15, 2)->default(0)->after('new_opening_balance');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customer_opening_balance_histories', 'resulting_closing_balance')) {
            Schema::table('customer_opening_balance_histories', function (Blueprint $table) {
                $table->dropColumn('resulting_closing_balance');
            });
        }
    }
};

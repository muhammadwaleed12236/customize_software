<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            if (!Schema::hasColumn('outward_gatepasses', 'expense_account_id')) {
                $table->unsignedBigInteger('expense_account_id')->nullable()->after('transport_rent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            if (Schema::hasColumn('outward_gatepasses', 'expense_account_id')) {
                $table->dropColumn('expense_account_id');
            }
        });
    }
};

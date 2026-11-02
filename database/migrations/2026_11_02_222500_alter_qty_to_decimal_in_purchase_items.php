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
        // 1. Modify purchase_items.qty to DECIMAL(12,2)
        DB::statement("ALTER TABLE purchase_items MODIFY COLUMN qty DECIMAL(12, 2) NOT NULL DEFAULT 0.00");

        // 2. Modify purchase_return_items.qty to DECIMAL(12,2) if table exists
        if (Schema::hasTable('purchase_return_items')) {
            DB::statement("ALTER TABLE purchase_return_items MODIFY COLUMN qty DECIMAL(12, 2) NOT NULL DEFAULT 0.00");
        }

        // 3. Sync existing purchase_items where packing_qty was stored as decimal (e.g. 2.50) but qty got rounded to 3
        DB::statement("UPDATE purchase_items SET qty = packing_qty WHERE packing_qty > 0 AND (qty != packing_qty OR qty = ROUND(packing_qty))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE purchase_items MODIFY COLUMN qty INT(11) NOT NULL DEFAULT 0");
        if (Schema::hasTable('purchase_return_items')) {
            DB::statement("ALTER TABLE purchase_return_items MODIFY COLUMN qty INT(11) NOT NULL DEFAULT 0");
        }
    }
};

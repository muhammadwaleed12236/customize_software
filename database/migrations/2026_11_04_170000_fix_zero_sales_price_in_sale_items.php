<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // 1. Where sales_price is 0 or null, but retail_price is > 0: copy retail_price to sales_price
            $updated1 = DB::table('sale_items')
                ->where(function ($q) {
                    $q->whereNull('sales_price')->orWhere('sales_price', '<=', 0);
                })
                ->where('retail_price', '>', 0)
                ->update(['sales_price' => DB::raw('retail_price')]);

            // 2. Where retail_price is 0 or null, but sales_price is > 0: copy sales_price to retail_price
            $updated2 = DB::table('sale_items')
                ->where(function ($q) {
                    $q->whereNull('retail_price')->orWhere('retail_price', '<=', 0);
                })
                ->where('sales_price', '>', 0)
                ->update(['retail_price' => DB::raw('sales_price')]);

            // 3. Fallback: Where both sales_price and retail_price are 0 or null, but amount > 0 and sales_qty > 0
            $updated3 = DB::table('sale_items')
                ->where(function ($q) {
                    $q->whereNull('sales_price')->orWhere('sales_price', '<=', 0);
                })
                ->where('amount', '>', 0)
                ->where('sales_qty', '>', 0)
                ->update([
                    'sales_price' => DB::raw('amount / sales_qty'),
                    'retail_price' => DB::raw('amount / sales_qty'),
                ]);

            Log::info("Migration fix_zero_sales_price_in_sale_items executed: $updated1 copied from retail_price, $updated2 copied to retail_price, $updated3 calculated from amount/sales_qty.");
        } catch (\Throwable $e) {
            Log::error("Migration fix_zero_sales_price_in_sale_items failed: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};

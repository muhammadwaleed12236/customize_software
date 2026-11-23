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
        // 1. Create product_variants table
        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->string('variant_code')->nullable()->index();
                $table->string('color')->nullable()->index();
                $table->string('size')->nullable();
                $table->string('barcode')->nullable()->index();
                $table->decimal('wholesale_price', 15, 2)->nullable();
                $table->decimal('retail_price', 15, 2)->nullable();
                $table->integer('alert_quantity')->default(0);
                $table->string('image')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Add has_variants flag to products table
        if (!Schema::hasColumn('products', 'has_variants')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('has_variants')->default(false)->after('color')->index();
            });
        }

        // 3. Add variant_id to warehouse_stocks
        if (!Schema::hasColumn('warehouse_stocks', 'variant_id')) {
            Schema::table('warehouse_stocks', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id')->index();
            });
        }

        // 4. Add variant_id to stocks
        if (!Schema::hasColumn('stocks', 'variant_id')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id');
            });

            // Adjust unique index on stocks if exists
            try {
                Schema::table('stocks', function (Blueprint $table) {
                    $table->dropUnique('stocks_unique_pair');
                });
            } catch (\Exception $e) {
                // Ignore if index did not exist
            }

            try {
                Schema::table('stocks', function (Blueprint $table) {
                    $table->index(['branch_id', 'product_id', 'variant_id'], 'stocks_branch_product_variant_idx');
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }

        // 5. Add variant_id to stock_movements
        if (Schema::hasTable('stock_movements') && !Schema::hasColumn('stock_movements', 'variant_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id')->index();
            });
        }

        // 6. Add variant_id to sale_items
        if (Schema::hasTable('sale_items') && !Schema::hasColumn('sale_items', 'variant_id')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id')->index();
            });
        }

        // 7. Add variant_id to purchase_items
        if (Schema::hasTable('purchase_items') && !Schema::hasColumn('purchase_items', 'variant_id')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id')->index();
            });
        }

        // 8. Add variant_id to purchase_order_items
        if (Schema::hasTable('purchase_order_items') && !Schema::hasColumn('purchase_order_items', 'variant_id')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id')->index();
            });
        }

        // 9. Add variant_id to inward_gatepass_items
        if (Schema::hasTable('inward_gatepass_items') && !Schema::hasColumn('inward_gatepass_items', 'variant_id')) {
            Schema::table('inward_gatepass_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id')->index();
            });
        }

        // 10. Add variant_id to stock_request_items
        if (Schema::hasTable('stock_request_items') && !Schema::hasColumn('stock_request_items', 'variant_id')) {
            Schema::table('stock_request_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id')->index();
            });
        }

        // 11. Add variant_id to product_booking_items
        if (Schema::hasTable('product_booking_items') && !Schema::hasColumn('product_booking_items', 'variant_id')) {
            Schema::table('product_booking_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id')->index();
            });
        }

        // 12. Add variant_id to damaged_stocks
        if (Schema::hasTable('damaged_stocks') && !Schema::hasColumn('damaged_stocks', 'variant_id')) {
            Schema::table('damaged_stocks', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'damaged_stocks',
            'product_booking_items',
            'stock_request_items',
            'inward_gatepass_items',
            'purchase_order_items',
            'purchase_items',
            'sale_items',
            'stock_movements',
            'stocks',
            'warehouse_stocks'
        ];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'variant_id')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropColumn('variant_id');
                });
            }
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'has_variants')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('has_variants');
            });
        }

        Schema::dropIfExists('product_variants');
    }
};

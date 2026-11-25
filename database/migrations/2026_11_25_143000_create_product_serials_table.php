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
        if (!Schema::hasTable('product_serials')) {
            Schema::create('product_serials', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('variant_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->index();
                $table->unsignedBigInteger('warehouse_id')->nullable()->index();
                $table->string('serial_number', 191)->index();
                $table->string('status', 50)->default('available')->index(); // available, sold, damaged, transferred
                $table->string('source_type', 50)->default('OPENING')->index(); // OPENING, PURCHASE
                $table->unsignedBigInteger('source_id')->nullable()->index();
                $table->decimal('purchase_price', 15, 2)->nullable();
                $table->decimal('sale_price', 15, 2)->nullable();
                $table->timestamp('sold_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['product_id', 'serial_number']);
                $table->index(['branch_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_serials');
    }
};

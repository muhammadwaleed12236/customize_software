<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**
     * Parent product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Warehouse stocks for this specific variant
     */
    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class, 'variant_id');
    }

    /**
     * Stocks for this specific variant
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class, 'variant_id');
    }

    /**
     * Get stock for a specific branch
     */
    public function getStockForBranch(?int $branchId): float
    {
        if (!$branchId) return 0;
        return (float) $this->warehouseStocks()
            ->where('branch_id', $branchId)
            ->sum('quantity');
    }

    /**
     * Get stock across all branches
     */
    public function getTotalStockAttribute(): float
    {
        return (float) $this->warehouseStocks()->sum('quantity');
    }
}

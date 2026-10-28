<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Subcategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category_id', 'branch_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'sub_category_id');
    }

    /**
     * Scope: Accessible by Branch
     * A subcategory is accessible to a branch if:
     * 1. It belongs to this branch (`branch_id = $branchId`), OR
     * 2. The branch has access to at least one product in this subcategory (owned or shared).
     */
    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where(function (Builder $q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereExists(function ($subQuery) use ($branchId) {
                  $subQuery->select(DB::raw(1))
                      ->from('products')
                      ->whereColumn('products.sub_category_id', 'subcategories.id')
                      ->where(function ($pq) use ($branchId) {
                          $pq->where('products.branch_id', $branchId)
                             ->orWhereExists(function ($sq) use ($branchId) {
                                 $sq->select(DB::raw(1))
                                    ->from('product_branch_shares')
                                    ->whereColumn('product_branch_shares.product_id', 'products.id')
                                    ->where('product_branch_shares.branch_id', $branchId);
                             });
                      });
              });
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Stock;
use App\Models\Branch;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    // protected $fillable = [
    //     'creater_id', 'category_id', 'sub_category_id', 'item_code', 'item_name', 'size',
    //     'opening_carton_quantity', 'carton_quantity', 'loose_pieces', 'pcs_in_carton',
    //     'wholesale_price', 'retail_price', 'initial_stock', 'alert_quantity'
    // ];
    // app/Models/Product.php
 public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_stocks', 'product_id', 'warehouse_id')
                    ->withPivot('quantity', 'price', 'remarks');
    }

    /**
     * Direct relationship to WarehouseStock (for branch-aware queries)
     */
    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class, 'product_id', 'id');
    }

    // app/Models/Product.php

    public function activeDiscount()
    {
        return $this->hasOne(ProductDiscount::class, 'product_id')
            ->where('status', 1); // only active discount
    }




    public function category_relation()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function sub_category_relation()
    {
        return $this->belongsTo(Subcategory::class, 'sub_category_id');
    }


    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Get Packing Multiplier (Pcs per Carton/Pack)
     */
    public function getPackingMultiplier(): float
    {
        $ppp = (float) ($this->piece_per_pack ?? 0);
        return $ppp > 1 ? $ppp : 1.0;
    }

    /**
     * Check if product has custom carton packing
     */
    public function hasPacking(): bool
    {
        return (float) ($this->piece_per_pack ?? 0) > 1;
    }

    /**
     * Format stock into Pieces and Cartons
     */
    public function formatStockWithPacking(float $qty): string
    {
        $multiplier = $this->getPackingMultiplier();
        if ($multiplier > 1) {
            $cartons = $qty / $multiplier;
            return number_format($qty, 2) . " Pcs (" . number_format($cartons, 2) . " Crtn)";
        }
        return number_format($qty, 2);
    }

    /**
     * Format piece quantity into Cartons + Loose Pieces string
     * e.g. 55 pcs with 10 ppp -> "5 Ctn + 5 Pcs"
     * e.g. 50 pcs with 10 ppp -> "5 Ctn"
     * e.g. 5 pcs with 10 ppp  -> "5 Pcs"
     */
    public static function formatCartonPieces(float $qty, float $ppp = 1, string $unitName = 'Ctn'): string
    {
        if ($ppp <= 1) {
            return number_format($qty, 2);
        }

        $isNegative = $qty < 0;
        $absQty     = abs($qty);
        $cartons    = (int) floor($absQty / $ppp);
        $loose      = round($absQty - ($cartons * $ppp), 2);

        $unitClean = trim($unitName ?: 'Ctn');
        if (preg_match('/carton/i', $unitClean)) {
            $unitClean = 'Ctn';
        }

        $parts = [];
        if ($cartons > 0) {
            $parts[] = $cartons . ' ' . $unitClean;
        }

        if ($loose > 0 || empty($parts)) {
            $looseFormatted = ($loose == (int)$loose) ? (int)$loose : number_format($loose, 2);
            $parts[] = $looseFormatted . ' Pcs';
        }

        $res = implode(' + ', $parts);
        return $isNegative ? ('-' . $res) : $res;
    }

    /**
     * Parse Carton.Pieces wholesale shorthand notation into total base pieces.
     * e.g. With piece_per_pack = 5:
     *   "1"   => 1 Carton (5 Pcs)
     *   "1.1" => 1 Carton + 1 Loose Pc = 6 Pcs
     *   "1.2" => 1 Carton + 2 Loose Pcs = 7 Pcs
     *   "0.1" => 0 Cartons + 1 Loose Pc = 1 Pc
     */
    public static function parseCartonQtyToPieces($qty, float $ppp = 1, ?string $unit = 'ctn'): float
    {
        $isPiece = $unit && strtolower($unit) === 'pcs';
        if ($isPiece || $ppp <= 1) {
            return (float) $qty;
        }

        $qtyStr = trim((string) $qty);
        if (strpos($qtyStr, '.') !== false) {
            $parts = explode('.', $qtyStr, 2);
            $cartons = (int) ($parts[0] ?? 0);
            $looseStr = $parts[1] ?? '0';
            $loose = (int) $looseStr;

            // If loose pieces is >= ppp, check if stripping trailing zeros (from DB DECIMAL e.g. "1.20") brings it < ppp
            if ($loose >= $ppp && substr($looseStr, -1) === '0') {
                $trimmedLoose = (int) rtrim($looseStr, '0');
                if ($trimmedLoose < $ppp) {
                    $loose = $trimmedLoose;
                }
            }

            // If still >= ppp, it's either an invalid shorthand or a standard decimal fraction (e.g. 7.6 ctn = 38 pcs)
            if ($loose >= $ppp) {
                return ((float) $qty) * $ppp;
            }

            return (float) (($cartons * $ppp) + $loose);
        }

        return ((float) $qty) * $ppp;
    }

    /**
     * Calculate line total for carton products supporting Carton.Pieces shorthand.
     * e.g. With ppp = 5, Carton Price = 100 (Piece Price = 20):
     *   "1"   => 5 Pcs * 20 = 100.00
     *   "1.1" => 6 Pcs * 20 = 120.00
     */
    public static function calculateCartonLineTotal($qty, float $price, float $ppp = 1, ?string $unit = 'ctn'): float
    {
        $isPiece = $unit && strtolower($unit) === 'pcs';
        if ($isPiece || $ppp <= 1) {
            return ((float) $qty) * $price;
        }

        $totalPieces = self::parseCartonQtyToPieces($qty, $ppp, $unit);
        $piecePrice = $ppp > 0 ? ($price / $ppp) : $price;
        return $totalPieces * $piecePrice;
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function type()
    {
        return $this->belongsTo(ProductType::class, 'type_id');
    }
    // public function stock()
    // {
    //     return $this->hasOne(Stock::class);
    // }
   // ✅ SINGLE stock relation
    public function stockproduct()
    {
        return $this->hasOne(Stock::class, 'product_id', 'id');
    }

    public function boms(){ return $this->hasMany(ProductBom::class,'product_id'); }
public function components(){ return $this->belongsToMany(Product::class,'product_boms','product_id','part_id')->withPivot('qty_per_unit'); }
public function movements(){ return $this->hasMany(StockMovement::class); }
public function scopeWithAvailable($q){
    return $q->withSum('movements as available_qty','qty'); // sum of ledger
}

public function stock()
{
    // Stock model me foreign key product_id hai
    return $this->hasOne(Stock::class, 'product_id', 'id');
}

public function stocks()
{
    return $this->hasMany(Stock::class, 'product_id', 'id');
}

  public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'product_id', 'id');
    }

    /**
     * Product has many purchase items
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'product_id', 'id');
    }

    /**
     * Product belongs to a Branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    /**
     * ✅ MULTI-BRANCH: Product branch codes (per branch item code)
     */
    public function branchProductCodes()
    {
        return $this->hasMany(BranchProductCode::class, 'product_id', 'id');
    }

    /**
     * ✅ Get branch-specific item code
     * Returns formatted code like "001", "002" for the given branch
     */
    public function getBranchItemCode($branchId)
    {
        $code = BranchProductCode::where('product_id', $this->id)
            ->where('branch_id', $branchId)
            ->first();
        
        return $code ? $code->item_code : null;
    }

    /**
     * ✅ Check if product is PRIMARY for a branch
     * PRIMARY: Has warehouse_stocks with quantity > 0
     */
    public function isPrimaryForBranch($branchId)
    {
        return $this->warehouseStocks()
            ->where('branch_id', $branchId)
            ->where('quantity', '>', 0)
            ->exists();
    }

    /**
     * ✅ Check if product is SECONDARY for a branch
     * SECONDARY: No warehouse_stocks entry for this branch
     */
    public function isSecondaryForBranch($branchId)
    {
        return !$this->warehouseStocks()
            ->where('branch_id', $branchId)
            ->exists();
    }

    /**
     * ✅ Get warehouse stocks for a specific branch
     */
    public function getStockForBranch($branchId)
    {
        return $this->warehouseStocks()
            ->where('branch_id', $branchId)
            ->sum('quantity');
    }

    /**
     * Branches that have been granted access to this product
     */
    public function sharedBranches()
    {
        return $this->belongsToMany(Branch::class, 'product_branch_shares', 'product_id', 'branch_id')
            ->withTimestamps();
    }

    /**
     * Scope: Products accessible by a specific branch (safeguard)
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\ProductBom;
use App\Models\StockMovement;
use App\Models\Stock;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Milon\Barcode\DNS1D;
use App\Http\Controllers\Concerns\BranchScope;


class ProductController extends Controller
{
    use BranchScope;
    public function getProductDetails(Request $request, $id){
        $branchId = $request->get('branch_id');
        if (!$branchId && Auth::check()) {
            $branchId = Auth::user()->branch_id;
        }

        $variantId = $request->get('variant_id');
        $productId = (int)$id;
        if (is_string($id) && str_contains($id, '_var_')) {
            [$pPart, $vPart] = explode('_var_', $id);
            $productId = (int)$pPart;
            if (!$variantId && !empty($vPart)) {
                $variantId = (int)$vPart;
            }
        }

        $product = Product::with(['brand', 'unit'])->find($productId);
        if ($product) {
            if ($variantId) {
                $variant = \App\Models\ProductVariant::find($variantId);
                if ($variant) {
                    $vRetail = (float)($variant->retail_price ?: ($product->retail_price ?? $product->price ?? 0));
                    $product->retail_price = $vRetail;
                    $product->price = $vRetail;
                    $product->variant_code = $variant->variant_code;
                    $product->variant_id = $variant->id;
                    $product->variant_name = $variant->variant_name ?: $variant->color;
                    $product->color = $variant->color;
                }
            }

            // Branch-specific warehouse stock
            $whStockQuery = WarehouseStock::with('warehouse')->where('product_id', $productId);
            if ($variantId) {
                $whStockQuery->where('variant_id', $variantId);
            }
            if ($branchId) {
                $whStockQuery->where('branch_id', $branchId);
            }
            $whStocks = $whStockQuery->get();

            $totalAvailableStock = (float) $whStocks->sum('quantity');

            $breakdown = $whStocks->map(function ($ws) {
                return [
                    'warehouse_id'   => $ws->warehouse_id,
                    'warehouse_name' => $ws->warehouse ? $ws->warehouse->warehouse_name : 'Direct Stock',
                    'quantity'       => (float) $ws->quantity
                ];
            })->values();

            return response()->json([
                'status'              => 'success',
                'product'             => $product,
                'available_stock'     => $totalAvailableStock,
                'warehouse_breakdown' => $breakdown
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function getPrice(Request $request)
    {
        $product = Product::find($request->product_id);

        return response()->json([
            'retail_price' => $product?->price ?? 0
        ]);
        
    }

    public function productget()
    {
        $products=Product::all();
        return response()->json($products);
    }

    private function upsertStocks(int $productId, float $qtyDelta, int $branchId = 1): void
    {
        // try update
        $updated = Stock::where([
            'branch_id'    => $branchId,
            
            'product_id'   => $productId,
        ])->update([
            'qty'        => DB::raw('qty + ('.($qtyDelta+0).')'),
            'updated_at' => now(),
        ]);

        if (!$updated) {
            // insert if row doesn't exist
            Stock::create([
                'branch_id'    => $branchId,
               
                'product_id'   => $productId,
                'qty'          => $qtyDelta,
                'reserved_qty' => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }



    public function assemblyReport(Request $request, $id)
    {
        // optional: user ne target units pass kiye (e.g. kitne banana chahte ho)
        $targetUnits = (float) $request->get('target', 0);

        // product + BOM rows
        $product = Product::findOrFail($id);
        $bomRows = ProductBom::with(['part:id,item_name,unit_id'])
            ->where('product_id', $id)
            ->get(['id', 'product_id', 'part_id', 'qty_per_unit']);

        // sab involved product_ids (finished + parts)
        $allIds = collect([$id])->merge($bomRows->pluck('part_id'))->unique()->values();

        // available map: product_id => available_qty
        $avail = StockMovement::whereIn('product_id', $allIds)
            ->selectRaw('product_id, COALESCE(SUM(qty),0) as qty_sum')
            ->groupBy('product_id')
            ->pluck('qty_sum', 'product_id');

        $readyStock = (float)($avail[$id] ?? 0);

        // assemblePossible = min(floor(available_part / qty_per_unit))
        $assemblePossible = $bomRows->count() ? $bomRows->map(function ($r) use ($avail) {
            $a = (float)($avail[$r->part_id] ?? 0);
            $rpu = (float)$r->qty_per_unit;
            return $rpu > 0 ? floor($a / $rpu) : INF;
        })->min() : 0;

        // agar user ne target diya hai to usko use karo; warna assemblePossible ko target maan lo
        $target = $targetUnits > 0 ? $targetUnits : $assemblePossible;

        // per-part breakdown
        $parts = $bomRows->map(function ($r) use ($avail, $target) {
            $available = (float)($avail[$r->part_id] ?? 0);
            $needed    = (float)$r->qty_per_unit * (float)$target;
            $shortage  = max(0, $needed - $available);
            return [
                'part_id'        => $r->part_id,
                'part_name'      => $r->part->item_name ?? 'N/A',
                'qty_per_unit'   => (float)$r->qty_per_unit,
                'available'      => $available,
                'needed'         => $needed,
                'shortage'       => $shortage,
            ];
        });

        // response
        return response()->json([
            'product_id'      => $product->id,
            'product_name'    => $product->item_name,
            'ready_stock'     => $readyStock,
            'assemble_possible' => (float)$assemblePossible,
            'total_sellable'  => (float)($readyStock + $assemblePossible),
            'target_used'     => (float)$target,
            'parts'           => $parts,
            'short_parts'     => $parts->filter(fn($p) => $p['shortage'] > 0)->values(),
        ]);
    }
    public function assemblySummary()
    {
        // sirf wo products jinke BOM rows hain (assembled)
        $assembledIds = ProductBom::select('product_id')->distinct()->pluck('product_id');

        // sab related product_ids (finished + unke parts)
        $partIds = ProductBom::whereIn('product_id', $assembledIds)->pluck('part_id');
        $allIds  = $assembledIds->merge($partIds)->unique()->values();

        // available map
        $avail = StockMovement::whereIn('product_id', $allIds)
            ->selectRaw('product_id, COALESCE(SUM(qty),0) as qty_sum')
            ->groupBy('product_id')
            ->pluck('qty_sum', 'product_id');

        // build rows
        $rows = $assembledIds->map(function ($pid) use ($avail) {
            $p = Product::find($pid);
            $bom = ProductBom::where('product_id', $pid)->get();
            if (!$p || $bom->isEmpty()) {
                return null;
            }
            $assemblePossible = $bom->map(function ($r) use ($avail) {
                $a   = (float)($avail[$r->part_id] ?? 0);
                $rpu = (float)$r->qty_per_unit;
                return $rpu > 0 ? floor($a / $rpu) : INF;
            })->min();
            $ready = (float)($avail[$pid] ?? 0);

            return [
                'product_id'        => $pid,
                'product_name'      => $p->item_name,
                'ready_stock'       => $ready,
                'assemble_possible' => (float)$assemblePossible,
                'total_sellable'    => (float)($ready + $assemblePossible),
            ];
        })->filter()->values();

        return view('admin_panel.product.assembly_summary', compact('rows'));
    }





    // ===== Product search (general) =====


 public function searchProducts(Request $request)
 {
     $q = trim((string)$request->get('q', ''));
     $vendorId = $request->get('vendor_id');

     // ✅ GLOBAL PRODUCTS - Show all products from all branches
     // But mark Primary/Secondary based on login branch stock
     $query = Product::with(['brand','unit','stockproduct','variants']);

     // Filter by vendor brands if vendor_id is provided
     if ($vendorId) {
         $vendor = \App\Models\Vendor::find($vendorId);
         if ($vendor && !empty($vendor->brand_ids)) {
             $query->whereIn('brand_id', $vendor->brand_ids);
         } else if ($vendor) {
             // If vendor has NO brands assigned, they see NO products
             // This enforces the "vendor must have brands" logic
             $query->whereRaw('1=0');
         }
     }

     if ($q !== '') {
         $qLower = strtolower($q);
         $qStarts = $qLower . '%';
         $qWord = '% ' . $qLower . '%';
         $qContains = '%' . $qLower . '%';
         $keywords = array_filter(explode(' ', $qLower));

         $query->where(function ($builder) use ($q, $qContains, $keywords) {
             $builder->where('item_name', 'like', $qContains)
                   ->orWhere('item_name_urdu', 'like', $qContains)
                   ->orWhere('item_code', 'like', $qContains)
                   ->orWhere('barcode_path', 'like', $qContains)
                   ->orWhere('model', 'like', $qContains)
                   ->orWhereHas('brand', function ($bQuery) use ($q) {
                       $bQuery->where('name', 'like', "%{$q}%");
                   })
                   ->orWhereHas('category_relation', function ($cQuery) use ($q) {
                       $cQuery->where('name', 'like', "%{$q}%");
                   })
                   ->orWhereHas('sub_category_relation', function ($scQuery) use ($q) {
                       $scQuery->where('name', 'like', "%{$q}%");
                   })
                   ->orWhereHas('variants', function ($vQuery) use ($qContains) {
                       $vQuery->where('color', 'like', $qContains)
                              ->orWhere('variant_name', 'like', $qContains)
                              ->orWhere('variant_code', 'like', $qContains)
                              ->orWhere('barcode', 'like', $qContains);
                   });

             // Support multi-word searches matching across fields
             if (count($keywords) > 1) {
                 $builder->orWhere(function ($multiQ) use ($keywords) {
                     foreach ($keywords as $kw) {
                         $multiQ->where(function ($wQ) use ($kw) {
                             $wQ->where('item_name', 'like', "%{$kw}%")
                                ->orWhere('item_code', 'like', "%{$kw}%")
                                ->orWhere('model', 'like', "%{$kw}%")
                                ->orWhereHas('variants', function ($vQuery) use ($kw) {
                                    $vQuery->where('color', 'like', "%{$kw}%")
                                           ->orWhere('variant_name', 'like', "%{$kw}%")
                                           ->orWhere('variant_code', 'like', "%{$kw}%")
                                           ->orWhere('barcode', 'like', "%{$kw}%");
                                });
                         });
                     }
                 });
             }
         });

         // Intelligent relevance ranking: Exact match > Starts With > Word Boundary > Substring
         $query->orderByRaw('
             CASE 
                 WHEN LOWER(item_code) = ? THEN 1
                 WHEN LOWER(item_name) = ? THEN 2
                 WHEN LOWER(item_code) LIKE ? THEN 3
                 WHEN LOWER(item_name) LIKE ? THEN 4
                 WHEN LOWER(CONCAT(" ", item_code, " ")) LIKE ? THEN 5
                 WHEN LOWER(CONCAT(" ", item_name, " ")) LIKE ? THEN 6
                 WHEN LOWER(item_code) LIKE ? THEN 7
                 WHEN LOWER(item_name) LIKE ? THEN 8
                 ELSE 9
             END ASC,
             LENGTH(item_code) ASC,
             LENGTH(item_name) ASC
         ', [$qLower, $qLower, $qStarts, $qStarts, $qWord, $qWord, $qContains, $qContains]);
     }

     $branchId = $request->get('branch_id') ?: (Auth::check() ? Auth::user()->branch_id : null);
     $products = $query->limit(35)->get();
     $results = [];

     foreach ($products as $product) {
         if ($product->has_variants && $product->variants->isNotEmpty()) {
             foreach ($product->variants as $v) {
                 if ($q !== '') {
                     $qLower = strtolower($q);
                     $matchesProduct = str_contains(strtolower($product->item_name), $qLower)
                         || str_contains(strtolower($product->item_code), $qLower)
                         || str_contains(strtolower($product->barcode_path ?? ''), $qLower)
                         || str_contains(strtolower($product->model ?? ''), $qLower);
                     $matchesVariant = str_contains(strtolower($v->color ?? ''), $qLower)
                         || str_contains(strtolower($v->variant_name ?? ''), $qLower)
                         || str_contains(strtolower($v->barcode ?? ''), $qLower)
                         || str_contains(strtolower($v->variant_code ?? ''), $qLower);

                     if (!$matchesProduct && !$matchesVariant) {
                         continue;
                     }
                 }

                 $wsQuery = DB::table('warehouse_stocks')
                     ->where('product_id', $product->id)
                     ->where('variant_id', $v->id);
                 if ($branchId) {
                     $wsQuery->where('branch_id', $branchId);
                 }
                 $stockQty = (float) $wsQuery->sum('quantity');

                 if ($stockQty == 0) {
                     $sQuery = DB::table('stocks')
                         ->where('product_id', $product->id)
                         ->where('variant_id', $v->id);
                     if ($branchId) {
                         $sQuery->where('branch_id', $branchId);
                     }
                     $stockQty = (float) $sQuery->sum('qty');
                 }

                 if ($stockQty == 0) {
                     $genWsQuery = DB::table('warehouse_stocks')
                         ->where('product_id', $product->id)
                         ->whereNull('variant_id');
                     if ($branchId) {
                         $genWsQuery->where('branch_id', $branchId);
                     }
                     $genStock = (float) $genWsQuery->sum('quantity');
                     if ($genStock == 0) {
                         $genSQuery = DB::table('stocks')
                             ->where('product_id', $product->id)
                             ->whereNull('variant_id');
                         if ($branchId) {
                             $genSQuery->where('branch_id', $branchId);
                         }
                         $genStock = (float) $genSQuery->sum('qty');
                     }
                     if ($genStock > 0) {
                         $stockQty = $genStock;
                     }
                 }

                 $vColor = $v->color ? " ({$v->color})" : '';
                 if ($v->variant_name && strtolower($v->variant_name) !== strtolower($v->color ?? '')) {
                     $displayTitle = $v->variant_name . $vColor;
                 } else {
                     $displayTitle = $product->item_name . $vColor;
                 }

                 $vRetail = (float)($v->retail_price ?: ($product->retail_price ?? $product->price ?? 0));
                 $vWholesale = (float)($v->wholesale_price ?: ($product->wholesale_price ?? 0));
                 $normalCode = $product->item_code;
                 $isPrimary = ($stockQty > 0);

                 $results[] = [
                     'id'             => $product->id . '_var_' . $v->id,
                     'select2_id'     => $product->id . '_var_' . $v->id,
                     'product_id'     => $product->id,
                     'variant_id'     => $v->id,
                     'text'           => ($normalCode ? $normalCode . ' — ' : '') . $displayTitle . ' [Stock: ' . $stockQty . ']',
                     'item_name'      => $displayTitle,
                     'display_title'  => $displayTitle,
                     'base_item_name' => $product->item_name,
                     'item_code'      => $normalCode,
                     'base_item_code' => $normalCode,
                     'variant_code'   => $v->variant_code,
                     'variant_name'   => $v->variant_name ?: $v->color,
                     'color'          => $v->color,
                     'barcode'        => $v->barcode ?: $product->barcode_path ?: $v->variant_code,
                     'model'          => $product->model,
                     'brand_name'     => $product->brand->name ?? '',
                     'unit_name'      => $product->unit->name ?? '',
                     'unit_id'        => $product->unit_id,
                     'stock'          => $stockQty,
                     'overall_stock'  => $stockQty,
                     'price'          => $vRetail,
                     'retail_price'   => $vRetail,
                     'wholesale_price'=> $vWholesale,
                     'is_primary'     => $isPrimary,
                     'pack_type'      => $product->pack_type ?? 'Standard',
                     'piece_per_pack' => (float)($product->piece_per_pack ?? 1),
                     'tracking_type'  => $product->tracking_type ?? 'standard',
                     'is_serial'      => method_exists($product, 'isSerial') ? $product->isSerial() : (($product->tracking_type ?? '') === 'serial'),
                     'has_variants'   => true,
                 ];
             }
         } else {
             $wsQuery = DB::table('warehouse_stocks')->where('product_id', $product->id);
             if ($branchId) {
                 $wsQuery->where('branch_id', $branchId);
             }
             $stockQty = (float) $wsQuery->sum('quantity');

             if ($stockQty == 0) {
                 $sQuery = DB::table('stocks')->where('product_id', $product->id);
                 if ($branchId) {
                     $sQuery->where('branch_id', $branchId);
                 }
                 $stockQty = (float) $sQuery->sum('qty');
             }

             $isPrimary = ($stockQty > 0);
             $retailPrice = (float)($product->retail_price ?? $product->price ?? 0);

             $results[] = [
                 'id'             => $product->id,
                 'select2_id'     => (string)$product->id,
                 'product_id'     => $product->id,
                 'variant_id'     => null,
                 'text'           => ($product->item_code ? $product->item_code . ' — ' : '') . $product->item_name . ' [Stock: ' . $stockQty . ']',
                 'item_name'      => $product->item_name,
                 'display_title'  => $product->item_name,
                 'base_item_name' => $product->item_name,
                 'item_code'      => $product->item_code,
                 'base_item_code' => $product->item_code,
                 'variant_code'   => null,
                 'variant_name'   => null,
                 'color'          => null,
                 'barcode'        => $product->barcode_path ?: $product->item_code,
                 'model'          => $product->model,
                 'brand_name'     => $product->brand->name ?? '',
                 'unit_name'      => $product->unit->name ?? '',
                 'unit_id'        => $product->unit_id,
                 'stock'          => $stockQty,
                 'overall_stock'  => $stockQty,
                 'price'          => $product->price ?? 0,
                 'retail_price'   => $retailPrice,
                 'wholesale_price'=> (float)($product->wholesale_price ?? 0),
                 'is_primary'     => $isPrimary,
                 'pack_type'      => $product->pack_type ?? 'Standard',
                 'piece_per_pack' => (float)($product->piece_per_pack ?? 1),
                 'tracking_type'  => $product->tracking_type ?? 'standard',
                 'is_serial'      => method_exists($product, 'isSerial') ? $product->isSerial() : (($product->tracking_type ?? '') === 'serial'),
                 'has_variants'   => false,
             ];
         }
     }

     return response()->json($results);
 }

public function searchProductsForSalebypagination(Request $request)
{
    // Use page-based pagination which integrates cleanly with Select2's
    // `params.page` value. This avoids fragile last_id state handling.
    $page = max(1, (int) $request->get('page', 1));
    $perPage = 10;

    // Allow searching by product name, code, brand, category, etc.
    $q = trim((string) $request->get('q', ''));
    $branchId = $request->get('branch_id') ?: (Auth::check() ? Auth::user()->branch_id : null);

    // ✅ GLOBAL PRODUCTS - Show all products from all branches
    // But mark Primary/Secondary based on login branch stock
    $query = Product::with(['brand', 'unit', 'category_relation', 'sub_category_relation', 'variants']);

    // NO branch restriction - products are GLOBAL

    if ($q !== '') {
        $qLower = strtolower($q);
        $qStarts = $qLower . '%';
        $qWord = '% ' . $qLower . '%';
        $qContains = '%' . $qLower . '%';
        $keywords = array_filter(explode(' ', $qLower));

        $query->where(function ($builder) use ($q, $qContains, $keywords) {
            $builder->where('item_name', 'like', $qContains)
                    ->orWhere('item_name_urdu', 'like', $qContains)
                    ->orWhere('item_code', 'like', $qContains)
                    ->orWhere('barcode_path', 'like', $qContains)
                    ->orWhere('model', 'like', $qContains)
                    ->orWhereHas('brand', function ($bQuery) use ($q) {
                        $bQuery->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('category_relation', function ($cQuery) use ($q) {
                        $cQuery->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('sub_category_relation', function ($scQuery) use ($q) {
                        $scQuery->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('variants', function ($vQuery) use ($qContains) {
                        $vQuery->where('color', 'like', $qContains)
                               ->orWhere('variant_name', 'like', $qContains)
                               ->orWhere('variant_code', 'like', $qContains)
                               ->orWhere('barcode', 'like', $qContains);
                    });

            if (count($keywords) > 1) {
                $builder->orWhere(function ($multiQ) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $multiQ->where(function ($wQ) use ($kw) {
                            $wQ->where('item_name', 'like', "%{$kw}%")
                               ->orWhere('item_code', 'like', "%{$kw}%")
                               ->orWhere('model', 'like', "%{$kw}%")
                               ->orWhereHas('variants', function ($vQuery) use ($kw) {
                                   $vQuery->where('color', 'like', "%{$kw}%")
                                          ->orWhere('variant_name', 'like', "%{$kw}%")
                                          ->orWhere('variant_code', 'like', "%{$kw}%")
                                          ->orWhere('barcode', 'like', "%{$kw}%");
                               });
                        });
                    }
                });
            }
        });

        $query->orderByRaw('
            CASE 
                WHEN LOWER(item_code) = ? THEN 1
                WHEN LOWER(item_name) = ? THEN 2
                WHEN LOWER(item_code) LIKE ? THEN 3
                WHEN LOWER(item_name) LIKE ? THEN 4
                WHEN LOWER(CONCAT(" ", item_code, " ")) LIKE ? THEN 5
                WHEN LOWER(CONCAT(" ", item_name, " ")) LIKE ? THEN 6
                WHEN LOWER(item_code) LIKE ? THEN 7
                WHEN LOWER(item_name) LIKE ? THEN 8
                ELSE 9
            END ASC,
            LENGTH(item_code) ASC,
            LENGTH(item_name) ASC
        ', [$qLower, $qLower, $qStarts, $qStarts, $qWord, $qWord, $qContains, $qContains]);
    } else {
        $query->orderBy('id', 'asc');
    }

    $products = $query
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

    // Add ownership information and Primary/Secondary status for each product
    $productsWithOwnership = [];
    foreach ($products as $p) {
        if ($p->has_variants && $p->variants->isNotEmpty()) {
            foreach ($p->variants as $v) {
                if ($q !== '') {
                    $qLower = strtolower($q);
                    $matchesProduct = str_contains(strtolower($p->item_name), $qLower)
                        || str_contains(strtolower($p->item_code), $qLower)
                        || str_contains(strtolower($p->barcode_path ?? ''), $qLower)
                        || str_contains(strtolower($p->model ?? ''), $qLower);
                    $matchesVariant = str_contains(strtolower($v->color ?? ''), $qLower)
                        || str_contains(strtolower($v->variant_name ?? ''), $qLower)
                        || str_contains(strtolower($v->barcode ?? ''), $qLower)
                        || str_contains(strtolower($v->variant_code ?? ''), $qLower);

                    if (!$matchesProduct && !$matchesVariant) {
                        continue;
                    }
                }

                $whStockQuery = DB::table('warehouse_stocks')
                    ->where('product_id', $p->id)
                    ->where('variant_id', $v->id);
                if ($branchId) {
                    $whStockQuery->where('branch_id', $branchId);
                }
                $overallStock = (float) $whStockQuery->sum('quantity');

                if ($overallStock == 0) {
                    $stocksQuery = DB::table('stocks')
                        ->where('product_id', $p->id)
                        ->where('variant_id', $v->id);
                    if ($branchId) {
                        $stocksQuery->where('branch_id', $branchId);
                    }
                    $overallStock = (float) $stocksQuery->sum('qty');
                }

                if ($overallStock == 0) {
                    $genWsQuery = DB::table('warehouse_stocks')
                        ->where('product_id', $p->id)
                        ->whereNull('variant_id');
                    if ($branchId) {
                        $genWsQuery->where('branch_id', $branchId);
                    }
                    $genStock = (float) $genWsQuery->sum('quantity');
                    if ($genStock == 0) {
                        $genSQuery = DB::table('stocks')
                            ->where('product_id', $p->id)
                            ->whereNull('variant_id');
                        if ($branchId) {
                            $genSQuery->where('branch_id', $branchId);
                        }
                        $genStock = (float) $genSQuery->sum('qty');
                    }
                    if ($genStock > 0) {
                        $overallStock = $genStock;
                    }
                }
                $stockQty = $overallStock;
                $isPrimary = ($overallStock > 0);

                $vColor = $v->color ? " ({$v->color})" : '';
                if ($v->variant_name && strtolower($v->variant_name) !== strtolower($v->color ?? '')) {
                    $displayTitle = $v->variant_name . $vColor;
                } else {
                    $displayTitle = $p->item_name . $vColor;
                }

                $vRetail = (float)($v->retail_price ?: ($p->retail_price ?? $p->price ?? 0));
                $vWholesale = (float)($v->wholesale_price ?: ($p->wholesale_price ?? 0));
                $normalCode = $p->item_code;

                $productsWithOwnership[] = [
                    'id'             => $p->id . '_var_' . $v->id,
                    'select2_id'     => $p->id . '_var_' . $v->id,
                    'product_id'     => $p->id,
                    'variant_id'     => $v->id,
                    'text'           => ($normalCode ? $normalCode . ' — ' : '') . $displayTitle . ' [Stock: ' . $stockQty . ']',
                    'item_name'      => $displayTitle,
                    'display_title'  => $displayTitle,
                    'base_item_name' => $p->item_name,
                    'item_code'      => $normalCode,
                    'base_item_code' => $normalCode,
                    'variant_code'   => $v->variant_code,
                    'variant_name'   => $v->variant_name ?: $v->color,
                    'color'          => $v->color,
                    'barcode'        => $v->barcode ?: $p->barcode_path ?: $v->variant_code,
                    'model'          => $p->model,
                    'stock'          => $stockQty,
                    'overall_stock'  => $stockQty,
                    'price'          => $vRetail,
                    'retail_price'   => $vRetail,
                    'wholesale_price'=> $vWholesale,
                    'branch_id'      => $p->branch_id,
                    'is_owner'       => ($branchId && $p->branch_id == $branchId),
                    'brand_name'     => $p->brand->name ?? '',
                    'unit_name'      => $p->unit->name ?? '',
                    'unit_id'        => $p->unit_id,
                    'is_primary'     => $isPrimary,
                    'pack_type'      => $p->pack_type ?? 'Standard',
                    'piece_per_pack' => (float)($p->piece_per_pack ?? 1),
                    'tracking_type'  => $p->tracking_type ?? 'standard',
                    'is_serial'      => method_exists($p, 'isSerial') ? $p->isSerial() : (($p->tracking_type ?? '') === 'serial'),
                    'has_variants'   => true,
                ];
            }
        } else {
            $whStockQuery = DB::table('warehouse_stocks')->where('product_id', $p->id);
            if ($branchId) {
                $whStockQuery->where('branch_id', $branchId);
            }
            $overallStock = (float) $whStockQuery->sum('quantity');

            if ($overallStock == 0) {
                $stocksQuery = DB::table('stocks')->where('product_id', $p->id);
                if ($branchId) {
                    $stocksQuery->where('branch_id', $branchId);
                }
                $overallStock = (float) $stocksQuery->sum('qty');
            }
            $stockQty = $overallStock;
            $isPrimary = ($overallStock > 0);
            $retailPrice = (float)($p->retail_price ?? $p->price ?? 0);

            $productsWithOwnership[] = [
                'id'             => $p->id,
                'select2_id'     => (string)$p->id,
                'product_id'     => $p->id,
                'variant_id'     => null,
                'text'           => ($p->item_code ? $p->item_code . ' — ' : '') . $p->item_name . ' [Stock: ' . $stockQty . ']',
                'item_name'      => $p->item_name,
                'display_title'  => $p->item_name,
                'base_item_name' => $p->item_name,
                'item_code'      => $p->item_code,
                'base_item_code' => $p->item_code,
                'variant_code'   => null,
                'variant_name'   => null,
                'color'          => null,
                'barcode'        => $p->barcode_path ?: $p->item_code,
                'model'          => $p->model,
                'stock'          => $stockQty,
                'overall_stock'  => $stockQty,
                'price'          => $p->price ?? 0,
                'retail_price'   => $retailPrice,
                'wholesale_price'=> (float)($p->wholesale_price ?? 0),
                'branch_id'      => $p->branch_id,
                'is_owner'       => ($branchId && $p->branch_id == $branchId),
                'brand_name'     => $p->brand->name ?? '',
                'unit_name'      => $p->unit->name ?? '',
                'unit_id'        => $p->unit_id,
                'is_primary'     => $isPrimary,
                'pack_type'      => $p->pack_type ?? 'Standard',
                'piece_per_pack' => (float)($p->piece_per_pack ?? 1),
                'tracking_type'  => $p->tracking_type ?? 'standard',
                'is_serial'      => method_exists($p, 'isSerial') ? $p->isSerial() : (($p->tracking_type ?? '') === 'serial'),
                'has_variants'   => false,
            ];
        }
    }

    return response()->json([
        'products' => $productsWithOwnership,
        'last_id'  => $products->last()->id ?? null,
        'has_more' => $products->count() == $perPage,
        'page' => $page,
        'user_branch_id' => $branchId,
    ]);
}





    // ===== List page =====
    

    public function product(Request $request)
    {
        // Get current user's branch
        $userBranch = Auth::check() ? Auth::user()->branch_id : null;
        $isSuperAdmin = Auth::check() ? Auth::user()->hasRole('super admin') : false;

        // Parse multiple selected branch IDs (handles array, comma-delimited string, or single ID)
        $rawBranchIds = $request->get('branch_ids', $request->get('branch_id', null));
        $selectedBranchIds = [];

        if (!empty($rawBranchIds)) {
            if (is_array($rawBranchIds)) {
                $selectedBranchIds = array_values(array_filter(array_map('intval', $rawBranchIds)));
            } elseif (is_string($rawBranchIds) && $rawBranchIds !== 'all') {
                $selectedBranchIds = array_values(array_filter(array_map('intval', explode(',', $rawBranchIds))));
            }
        }

        // Determine which branches the current user is allowed to see
        $allowedBranchIds = [];

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->hasRole('super admin')) {
                // If specific branches are selected by super admin, filter by them
                if (!empty($selectedBranchIds)) {
                    $allowedBranchIds = $selectedBranchIds;
                } else {
                    // super admin sees all branches by default
                    $allowedBranchIds = Branch::pluck('id')->toArray();
                }
            } else {
                // ✅ IMPORTANT: Only include user's own branch + explicitly granted permissions
                // always include own branch
                $allowedBranchIds[] = $user->branch_id;

                // ✅ Check for explicit cross-branch permissions
                // Permission format: branch:{id}:product.view
                // These are only granted explicitly to specific users who need cross-branch access
                $branchIds = Branch::pluck('id');
                foreach ($branchIds as $bid) {
                    if ($bid !== $user->branch_id && $user->can("branch:{$bid}:product.view")) {
                        $allowedBranchIds[] = $bid;
                    }
                }

                // make unique
                $allowedBranchIds = array_unique($allowedBranchIds);
            }
        }

        // Build query with relationships - INCLUDE branch product codes
        $query = Product::with([
            'category_relation', 
            'sub_category_relation', 
            'unit', 
            'brand', 
            'branch',
            'branchProductCodes', // ✅ Load branch-specific codes
            'warehouseStocks.branch' // ✅ Load all warehouse stocks with branch for super admin
        ]);

        if (!empty($allowedBranchIds)) {
            $query->whereIn('branch_id', $allowedBranchIds);
        } else {
            // no branch allowed, return empty
            $query->whereRaw('1=0');
        }

        $products = $query->get()
            ->map(function ($product) use ($userBranch, $isSuperAdmin) {
                if ($isSuperAdmin) {
                    $product->branch_item_code = $product->getBranchItemCode($product->branch_id);

                    // Group warehouse stocks by branch
                    $stocksByBranch = [];
                    if ($product->warehouseStocks && $product->warehouseStocks->count() > 0) {
                        foreach ($product->warehouseStocks as $ws) {
                            if (!isset($stocksByBranch[$ws->branch_id])) {
                                $stocksByBranch[$ws->branch_id] = [
                                    'branch_id' => $ws->branch_id,
                                    'branch_name' => $ws->branch->name ?? 'Unknown',
                                    'quantity' => 0
                                ];
                            }
                            $stocksByBranch[$ws->branch_id]['quantity'] += (float)$ws->quantity;
                        }
                    }

                    // Fallback: Check stocks table for any branch missing in warehouse_stocks or with 0 quantity
                    $allStocks = Stock::with('branch')->where('product_id', $product->id)->where('qty', '>', 0)->get();
                    foreach ($allStocks as $st) {
                        if (!isset($stocksByBranch[$st->branch_id]) || $stocksByBranch[$st->branch_id]['quantity'] == 0) {
                            $stocksByBranch[$st->branch_id] = [
                                'branch_id' => $st->branch_id,
                                'branch_name' => $st->branch->name ?? 'Unknown',
                                'quantity' => (float)$st->qty
                            ];
                        }
                    }

                    $stocksByBranch = array_filter($stocksByBranch, function ($item) {
                        return $item['quantity'] > 0;
                    });

                    $product->all_warehouse_stocks = array_values($stocksByBranch);
                    $product->branch_stock_qty = array_sum(array_column($stocksByBranch, 'quantity'));
                    $product->is_primary = ($product->branch_stock_qty > 0);
                    $product->is_secondary = ($product->branch_stock_qty == 0);
                    $product->user_branch_stock_qty = $product->branch_stock_qty;
                    $product->has_stock_in_user_branch = ($product->branch_stock_qty > 0);
                } elseif ($userBranch) {
                    $product->branch_item_code = $product->getBranchItemCode($userBranch);

                    $stockQty = (float) $product->getStockForBranch($userBranch);
                    if ($stockQty == 0) {
                        $stockQty = (float) (Stock::where('product_id', $product->id)->where('branch_id', $userBranch)->sum('qty') ?? 0);
                    }

                    $product->branch_stock_qty = $stockQty;
                    $product->is_primary = ($stockQty > 0);
                    $product->is_secondary = ($stockQty == 0);
                    $product->user_branch_stock_qty = $stockQty;
                    $product->has_stock_in_user_branch = ($stockQty > 0);
                    $product->all_warehouse_stocks = null;
                } else {
                    $product->branch_item_code = null;
                    $product->is_primary = false;
                    $product->is_secondary = true;
                    $product->branch_stock_qty = 0;
                    $product->user_branch_stock_qty = 0;
                    $product->has_stock_in_user_branch = false;
                    $product->all_warehouse_stocks = null;
                }
                return $product;
            });

        $categories = Category::get();
        // supply branch list for add modal & filter (super admin can choose branch)
        $branchesList = Branch::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        
        // pass userBranch, isSuperAdmin, selectedBranchIds, and allowedBranchIds to view
        return view('admin_panel.product.index', compact('products', 'categories', 'allowedBranchIds', 'branchesList', 'userBranch', 'isSuperAdmin', 'selectedBranchIds', 'brands'));
    }

 public function productview($id)
{
    // ✅ ERP PROPER: Include branch relationship for super admin display
    $product = Product::with([
        'category_relation',
        'sub_category_relation',
        'brand',
        'unit',
        'stock',
        'branch',
        'variants'  // Include variants
    ])->find($id);

    if ($product) {
        $user = auth()->user();
        $isSuperAdmin = $user && $user->hasRole('super admin');
        $branchId = $user ? $user->branch_id : null;

        // Live physical stock across warehouses / shop floor
        $whStockQuery = \App\Models\WarehouseStock::where('product_id', $product->id);
        if (!$isSuperAdmin && $branchId) {
            $whStockQuery->where('branch_id', $branchId);
        }
        $whTotal = (float) $whStockQuery->sum('quantity');

        // Fallback: check stocks table
        $stkQuery = \App\Models\Stock::where('product_id', $product->id);
        if (!$isSuperAdmin && $branchId) {
            $stkQuery->where('branch_id', $branchId);
        }
        $stkTotal = (float) $stkQuery->sum('qty');

        if ($product->has_variants) {
            $totalStock = $whTotal;
        } else {
            $totalStock = ($whTotal > 0 || \App\Models\WarehouseStock::where('product_id', $product->id)->exists()) ? $whTotal : $stkTotal;
        }

        if (!$product->stock) {
            $product->setRelation('stock', new \App\Models\Stock(['qty' => $totalStock]));
        } else {
            $product->stock->qty = $totalStock;
        }

        // Calculate variant-level stock if product has variants
        if ($product->has_variants && $product->variants) {
            $variantStockSum = 0;
            foreach ($product->variants as $variant) {
                $vQuery = \App\Models\WarehouseStock::where('product_id', $product->id)
                    ->where('variant_id', $variant->id);
                if (!$isSuperAdmin && $branchId) {
                    $vQuery->where('branch_id', $branchId);
                }
                $vStock = (float) $vQuery->sum('quantity');
                if ($vStock == 0) {
                    $vsQuery = \App\Models\Stock::where('product_id', $product->id)
                        ->where('variant_id', $variant->id);
                    if (!$isSuperAdmin && $branchId) {
                        $vsQuery->where('branch_id', $branchId);
                    }
                    $vStock = (float) $vsQuery->sum('qty');
                }
                $variant->stock_qty = $vStock;
                $variantStockSum += $vStock;
            }

            if ($variantStockSum > $totalStock) {
                $totalStock = $variantStockSum;
                if ($product->stock) {
                    $product->stock->qty = $totalStock;
                }
            }
        }
    }

    return response()->json($product);
}

    /**
     * Get real-time stock breakdown across all shops and warehouses for a given product
     */
    public function getProductLocationStock($id)
    {
        $product = Product::with(['unit', 'brand', 'category_relation'])->findOrFail($id);

        $user = auth()->user();
        $isSuperAdmin = $user && $user->hasRole('super admin');
        $userBranchId = $user ? $user->branch_id : null;

        $branches = Branch::with('warehouses')->get();
        if (!$isSuperAdmin && $userBranchId) {
            $branches = $branches->where('id', $userBranchId);
        }

        $isCarton = $product->isCartonProduct();
        $ppp = $isCarton ? (float)($product->piece_per_pack ?? 1) : 1;
        $unitName = $product->unit?->name ?? 'Pcs';

        $locations = [];
        $totalStock = 0;

        foreach ($branches as $branch) {
            // 1. Warehouses linked to this branch (only where stock is available)
            $totalWhStockForBranch = 0;
            $whLocations = [];

            foreach ($branch->warehouses as $warehouse) {
                $whQty = (float) WarehouseStock::where('branch_id', $branch->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->where('product_id', $product->id)
                    ->sum('quantity');

                if ($whQty > 0) {
                    $totalWhStockForBranch += $whQty;
                    $whLocations[] = [
                        'type'           => 'Warehouse',
                        'branch_id'      => $branch->id,
                        'branch_name'    => $branch->name,
                        'warehouse_id'   => $warehouse->id,
                        'location_name'  => $warehouse->warehouse_name ?? ('Warehouse #' . $warehouse->id),
                        'quantity'       => $whQty,
                        'formatted'      => $ppp > 1 ? Product::formatCartonPieces($whQty, $ppp, $unitName) : number_format($whQty, 0) . ' ' . $unitName,
                        'pieces'         => number_format($whQty, 0) . ' Pcs',
                        'in_stock'       => true,
                    ];
                }
            }

            // 2. Shop Floor Stock (Counter) - warehouse_id IS NULL (or 0)
            $explicitShopStock = (float) WarehouseStock::where('branch_id', $branch->id)
                ->where(function ($q) {
                    $q->whereNull('warehouse_id')->orWhere('warehouse_id', 0);
                })
                ->where('product_id', $product->id)
                ->sum('quantity');

            $shopQty = 0;
            if ($explicitShopStock > 0) {
                $shopQty = $explicitShopStock;
            } elseif ($totalWhStockForBranch == 0 && !$product->has_variants) {
                // Only if NO warehouse stock exists at all for this branch, check Stock table fallback
                $stk = Stock::where('branch_id', $branch->id)
                    ->where('product_id', $product->id)
                    ->sum('qty');
                if ($stk && (float)$stk > 0) {
                    $shopQty = (float)$stk;
                }
            }

            // Include shop counter only if stock is available
            if ($shopQty > 0) {
                $totalStock += $shopQty;
                $locations[] = [
                    'type'           => 'Shop',
                    'branch_id'      => $branch->id,
                    'branch_name'    => $branch->name,
                    'location_name'  => $branch->name . ' (Counter / Shop)',
                    'quantity'       => $shopQty,
                    'formatted'      => $ppp > 1 ? Product::formatCartonPieces($shopQty, $ppp, $unitName) : number_format($shopQty, 0) . ' ' . $unitName,
                    'pieces'         => number_format($shopQty, 0) . ' Pcs',
                    'in_stock'       => true,
                ];
            }

            foreach ($whLocations as $whLoc) {
                $totalStock += $whLoc['quantity'];
                $locations[] = $whLoc;
            }
        }

        // Also check any standalone warehouse_stocks rows not covered in branch_warehouse
        $coveredWhIds = array_filter(array_column($locations, 'warehouse_id'));
        $otherWhStocks = WarehouseStock::with(['branch', 'warehouse'])
            ->where('product_id', $product->id)
            ->whereNotNull('warehouse_id')
            ->where('quantity', '>', 0)
            ->when(!empty($coveredWhIds), function ($q) use ($coveredWhIds) {
                $q->whereNotIn('warehouse_id', $coveredWhIds);
            })
            ->get();

        foreach ($otherWhStocks as $ows) {
            $whQty = (float)$ows->quantity;
            $totalStock += $whQty;
            $locations[] = [
                'type'          => 'Warehouse',
                'branch_id'     => $ows->branch_id,
                'branch_name'   => $ows->branch?->name ?? 'Branch #' . $ows->branch_id,
                'warehouse_id'  => $ows->warehouse_id,
                'location_name' => $ows->warehouse?->warehouse_name ?? ('Warehouse #' . $ows->warehouse_id),
                'quantity'      => $whQty,
                'formatted'     => $ppp > 1 ? Product::formatCartonPieces($whQty, $ppp, $unitName) : number_format($whQty, 0) . ' ' . $unitName,
                'pieces'        => number_format($whQty, 0) . ' Pcs',
                'in_stock'      => true,
            ];
        }

        return response()->json([
            'status'          => 'success',
            'product_id'      => $product->id,
            'item_code'       => $product->item_code,
            'product_name'    => $product->item_name,
            'product_name_ur' => $product->item_name_urdu,
            'brand'           => $product->brand?->name ?? 'N/A',
            'category'        => $product->category_relation?->name ?? 'N/A',
            'unit'            => $unitName,
            'piece_per_pack'  => $ppp,
            'total_stock'     => $totalStock,
            'total_formatted' => $ppp > 1 ? Product::formatCartonPieces($totalStock, $ppp, $unitName) : number_format($totalStock, 0) . ' ' . $unitName,
            'locations'       => $locations,
        ]);
    }

////////////////////////



///////////////////////////


    public function view_store()
    {
        // ✅ ERP STANDARD: Role-based branch visibility
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        // Build branch options based on role
        if ($isSuperAdmin) {
            // Super admin sees all branches
            $branches = Branch::all();
        } else {
            // Regular user only sees their own branch
            $userBranch = $user->branch_id ? Branch::find($user->branch_id) : null;
            $branches = $userBranch ? collect([$userBranch]) : collect();
        }
        
        $categories = Category::select('id', 'name')->get();
        $units      = Unit::select('id', 'name')->get();
        $brands     = Brand::select('id', 'name')->get();
        $types      = \App\Models\ProductType::all();
        $warehouses = Warehouse::with('branches')->get();
        
        // ✅ ERP STANDARD: Sequential Barcode & Item Code Generation
        $maxId = \App\Models\Product::max('id') ?? 0;
        // Prefix with '100' and pad to 5 digits (e.g., 10000001, 10000002)
        $nextBarcode = '100' . str_pad($maxId + 1, 5, '0', STR_PAD_LEFT);
        $nextItemCode = 'ITEM-' . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
        $productSettings = \App\Models\ProductSetting::getSettings();
        
        return view('admin_panel.product.create', compact(
            'categories', 'units', 'brands', 'types', 'branches', 'warehouses',
            'isSuperAdmin', 'user', 'nextBarcode', 'nextItemCode', 'productSettings'
        ));
    }

    // ===== Dependent subcategories =====
    public function getSubcategories($category_id)
    {
        $subcategories = Subcategory::where('category_id', $category_id)->get();
        return response()->json($subcategories);
    }

    // ===== Barcode =====
    public function generateBarcode(Request $request)
    {
        $barcodeNumber = $request->filled('code') ? $request->code : rand(100000000000, 999999999999);
        $barcodePNG    = (new DNS1D)->getBarcodePNG($barcodeNumber, 'C39', 3, 50);
        $barcodeImage  = "data:image/png;base64," . $barcodePNG;

        return response()->json([
            'barcode_number' => $barcodeNumber,
            'barcode_image'  => $barcodeImage
        ]);
    }

    // ===== Store product (2-Phase Workflow) =====
    // ✅ PHASE 1: Basic product profile (name, code, brand, unit, category, type)
    // ✅ PHASE 2: Opening stock configuration (opening_stock, alert_qty, prices, warehouse assignments)
    public function store_product(Request $request)
    {       
        if (!Auth::id()) return redirect()->back();

        $userId = Auth::id();
        $phase = $request->input('phase', 'phase1'); // Default to Phase 1

        // ============ PHASE 1: CREATE BASIC PRODUCT PROFILE ============
        if ($phase === 'phase1') {
            return $this->storeProductPhase1($request, $userId);
        }

        // ============ PHASE 2: CONFIGURE OPENING STOCK & WAREHOUSE ASSIGNMENTS ============
        if ($phase === 'phase2') {
            return $this->storeProductPhase2($request);
        }

        return redirect()->back()->with('error', 'Invalid phase specified');
    }

    /**
     * PHASE 1: Store Basic Product Profile Only
     * Creates product with: name, code, category, brand, unit, image, is_part, is_assembled
     * Sets completion_status = 'profile_only'
     * Redirects to opening stock form
     */
    private function storeProductPhase1(Request $request, int $userId)
    {
        if (!$request->filled('model')) {
            return redirect()->back()->withInput()->with('swal_error', 'Product cannot be stored without Model.');
        }

        // Image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $imagePath = $filename;
        }

        $productId = DB::transaction(function () use ($request, $userId, $imagePath) {

            // ============ BRANCH ASSIGNMENT LOGIC (ERP BUSINESS LOGIC) ============
            $branchId = 1; // Default fallback
            
            if (Auth::check()) {
                $user = Auth::user();
                
                // Super admin can select branch via form
                if ($user->hasRole('super admin')) {
                    $branchId = (int) ($request->input('branch_id') ?? $user->branch_id ?? 1);
                    $branch = Branch::find($branchId);
                    if (!$branch) {
                        $branchId = 1;
                    }
                } else {
                    // Regular users: use their own branch
                    $branchId = (int) ($user->branch_id ?? 1);
                }
            }

            // Generate per-branch item_code
            $lastForBranch = Product::where('branch_id', $branchId)
                ->whereNotNull('item_code')
                ->where('item_code', 'like', 'ITEM-%')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastForBranch && preg_match('/ITEM-0*([0-9]+)$/', $lastForBranch->item_code, $matches)) {
                $seq = intval($matches[1]) + 1;
            } else {
                $seq = 1;
            }
            $nextCode = 'ITEM-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // Handle packing type logic
            $rawPackingType = trim((string)$request->input('packing_type', 'Standard'));
            $isCustomize = (strcasecmp($rawPackingType, 'Customize') === 0) || (empty($rawPackingType) && ((float)($request->piece_per_pack ?? 0) > 0 || (float)($request->pack_qty ?? 0) > 0));
            if ($isCustomize) {
                $packingType = 'Customize';
                $packQty = (float) ($request->packing_qty ?? $request->pack_qty ?? 0);
                $piecePerPack = (float) ($request->piece_per_pack ?? 0);
                $loosePiece = (float) ($request->loose_piece ?? 0);
            } else {
                $packingType = 'Standard';
                $packQty = 0;
                $piecePerPack = 0;
                $loosePiece = 0;
            }

            $colors = $request->input('color');
            $hasVariants = $request->boolean('has_variants');

            // If variants are supplied, sync product color list from variant table
            if ($hasVariants && $request->has('variants') && is_array($request->variants)) {
                $variantColors = [];
                foreach ($request->variants as $v) {
                    $vc = trim($v['color'] ?? '');
                    if ($vc !== '' && !in_array($vc, $variantColors)) {
                        $variantColors[] = $vc;
                    }
                }
                if (!empty($variantColors)) {
                    $colors = $variantColors;
                }
            }

            // ✅ PHASE 1: Create product form basic profile only
            // SKIP: opening_stock, prices, warehouse_assignments
            $product = Product::create([
                'branch_id'       => $branchId,
                'creater_id'      => $userId,
                'category_id'     => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'type_id'         => $request->type_id,
                'item_code'       => $request->filled('item_code') ? $request->item_code : $nextCode,
                'item_name'       => $request->product_name,
                'item_name_urdu'  => $request->item_name_urdu ?? $request->product_name_urdu,
                'barcode_path'    => $request->barcode_path ?? rand(100000000000, 999999999999),
                'unit_id'         => $request->unit,
                'tracking_type'   => $request->input('tracking_type', 'standard'),
                'brand_id'        => $request->brand_id,
                'wholesale_price' => 0,              // ← PHASE 2
                'price'           => 0,              // ← PHASE 2
                'alert_quantity'  => 0,              // ← PHASE 2
                'model'           => $request->model,
                'hs_code'         => $request->filled('hs_code') ? $request->hs_code : null,
                'pack_type'       => $packingType,
                'pack_qty'        => $packQty,
                'piece_per_pack'  => $piecePerPack,
                'loose_piece'     => $loosePiece,
                'image'           => $imagePath,
                'color'           => $colors ? (is_array($colors) ? json_encode(array_values(array_filter($colors))) : $colors) : null,
                'has_variants'    => $hasVariants ? 1 : 0,
                'is_part'         => $request->has('is_part') ? 1 : 0,
                'is_assembled'    => $request->has('is_assembled') ? 1 : 0,
                'completion_status' => 'profile_only', // ← Mark as incomplete
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Save Product Variants if has_variants is selected
            if ($hasVariants && $request->has('variants') && is_array($request->variants)) {
                $firstVarBarcode = null;
                foreach ($request->variants as $idx => $v) {
                    $vColor = trim($v['color'] ?? '');
                    $vName  = !empty($v['variant_name']) ? trim($v['variant_name']) : $vColor;
                    if ($vColor !== '' || $vName !== '') {
                        $vBarcode = !empty($v['barcode']) ? trim($v['barcode']) : ($product->barcode_path . '-' . ($idx + 1));
                        if (!$firstVarBarcode) {
                            $firstVarBarcode = $vBarcode;
                        }
                        \App\Models\ProductVariant::create([
                            'product_id'      => $product->id,
                            'variant_name'    => $vName ?: $vColor,
                            'variant_code'    => !empty($v['variant_code']) ? trim($v['variant_code']) : ($product->item_code . '-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $vColor ?: 'VAR'), 0, 3))),
                            'color'           => $vColor ?: $vName,
                            'size'            => !empty($v['size']) ? trim($v['size']) : null,
                            'barcode'         => $vBarcode,
                            'wholesale_price' => isset($v['wholesale_price']) && is_numeric($v['wholesale_price']) ? (float)$v['wholesale_price'] : 0,
                            'retail_price'    => isset($v['retail_price']) && is_numeric($v['retail_price']) ? (float)$v['retail_price'] : 0,
                            'alert_quantity'  => isset($v['alert_quantity']) && is_numeric($v['alert_quantity']) ? (int)$v['alert_quantity'] : 0,
                            'status'          => 1,
                        ]);
                    }
                }
                if ($firstVarBarcode && empty($request->barcode_path)) {
                    $product->update(['barcode_path' => $firstVarBarcode]);
                }
            }

            // BOM save (Phase 1: can configure BOM with profile)
            if ($request->boolean('is_assembled') && $request->filled('bom_json')) {
                $rows = collect(json_decode($request->bom_json, true))
                    ->filter(fn($r) => !empty($r['part_id']) && (float)($r['required_per_unit'] ?? 0) > 0)
                    ->map(fn($r) => [
                        'product_id'   => $product->id,
                        'part_id'      => $r['part_id'],
                        'qty_per_unit' => (float)$r['required_per_unit'],
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ])->values();

                if ($rows->count()) {
                    DB::table('product_boms')->insert($rows->all());
                }
            }

            return $product->id;
        });

        // ✅ Redirect to product list page (product route) after successful save
        return redirect()->route('product')
                         ->with('success', 'Product profile created successfully! You can now configure opening stock from "Opening Stocks" in the navbar.');
    }

    /**
     * PHASE 2: Configure Opening Stock, Alert Qty, Prices, Warehouse Assignments
     * Updates existing product with completion details
     * Sets completion_status = 'complete'
     */
    private function storeProductPhase2(Request $request)
    {
        $productId = $request->input('product_id');
        $product = Product::findOrFail($productId);

        // ✅ OWNERSHIP CHECK: Prevent cross-branch updates
        $userBranchId = Auth::check() ? Auth::user()->branch_id : null;
        $isSuperAdmin = Auth::check() && Auth::user()->hasRole('super admin');
        
        if (!$isSuperAdmin && $userBranchId !== $product->branch_id) {
            return redirect()->back()->with('error', 'You can only complete products from your own branch');
        }

        DB::transaction(function () use ($request, $product) {
            // Update product with Phase 2 data
            $product->update([
                'alert_quantity'  => (int) ($request->alert_quantity ?? 0),
                'wholesale_price' => (float) ($request->wholesale_price ?? 0),
                'price'           => (float) ($request->retail_price ?? 0),
                'initial_stock'   => (float) ($request->opening_stock ?? 0),  // ✅ ERP STANDARD: Store opening stock
                'completion_status' => 'complete', // ← Mark as complete
                'updated_at'      => now(),
            ]);

            $opening = (float) ($request->opening_stock ?? 0);

            // ✅ Parse allocation data (multi-row allocation system)
            $allocationDataJson = $request->input('allocation_data', '[]');
            $allocationData = json_decode($allocationDataJson, true) ?? [];

            // Opening stock → single movement + stocks upsert
            if ($opening > 0) {
                // Create stock movement with branch tracking
                StockMovement::create([
                    'product_id' => $product->id,
                    'branch_id'  => $product->branch_id,  // ✅ ERP STANDARD: Track which branch
                    'type'       => 'in',
                    'qty'        => $opening,
                    'ref_type'   => 'OPENING',
                    'ref_id'     => null,
                    'note'       => 'Opening stock',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // ✅ Update stocks table for branch (ERP Standard: dual-table sync)
                // For multi-allocation, we use branch-level only (no warehouse_id in stocks table)
                $this->upsertStocks(
                    productId: $product->id,
                    qtyDelta: $opening,
                    branchId: $product->branch_id,
                );
            }

            // ✅ ERP STANDARD: Create warehouse_stocks entries
            $branch = Branch::find($product->branch_id);
            $allWarehouses = $branch ? $branch->warehouses : collect();

            // ✅ STEP 1: Create branch-level entry (warehouse_id = NULL)
            $branchLevelQty = 0;
            
            // Calculate branch-level quantity from allocations
            foreach ($allocationData as $allocation) {
                if ($allocation['location_type'] === 'shop') {
                    $branchLevelQty += (float) ($allocation['quantity'] ?? 0);
                }
            }

            WarehouseStock::firstOrCreate([
                'branch_id'    => $product->branch_id,
                'warehouse_id' => null,
                'product_id'   => $product->id,
            ], [
                'quantity' => $branchLevelQty,
                'price'    => $request->retail_price ?? 0,
            ]);

            // ✅ STEP 2: Create entries for ALL warehouses in the branch
            foreach ($allWarehouses as $warehouse) {
                // Calculate warehouse quantity from allocations
                $warehouseQuantity = 0;
                
                foreach ($allocationData as $allocation) {
                    if ($allocation['location_type'] === 'warehouse' && 
                        (int)($allocation['warehouse_id'] ?? 0) === $warehouse->id) {
                        $warehouseQuantity += (float) ($allocation['quantity'] ?? 0);
                    }
                }

                WarehouseStock::firstOrCreate([
                    'branch_id'    => $product->branch_id,
                    'warehouse_id' => $warehouse->id,
                    'product_id'   => $product->id,
                ], [
                    'quantity' => $warehouseQuantity,
                    'price'    => $request->retail_price ?? 0,
                ]);
            }
        });

        return redirect()->route('product')
                         ->with('success', 'Product opening stock configured successfully!');
    }


    // ===== Parts search (for BOM modal) with real available qty =====
    //     public function searchPartName(Request $request)
    // {
    //     $q = $request->get('q', '');

    //     $parts = Product::where('is_part', 1)
    //         ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
    //         ->where(function ($x) use ($q) {
    //             $x->where('products.item_name', 'like', "%{$q}%")
    //               ->orWhere('products.item_code', 'like', "%{$q}%");
    //         })
    //         ->groupBy('products.id', 'products.item_name', 'products.item_code', 'products.unit_id')
    //         ->selectRaw('products.id, products.item_name, products.item_code, products.unit_id, COALESCE(SUM(stocks.qty),0) as available_qty')
    //         ->limit(20)
    //         ->get();

    //     return response()->json($parts->map(function ($p) {
    //         return [
    //             'id'            => $p->id,
    //             'item_name'     => $p->item_name,
    //             'item_code'     => $p->item_code,
    //             'unit'          => optional(Unit::find($p->unit_id))->name ?? '',
    //             'available_qty' => (float)$p->available_qty,
    //         ];
    //     }));
    // }

    /**
     * PHASE 2 (FORM): Show Opening Stock Configuration Form
     * For products with completion_status = 'profile_only'
     */
    public function createOpeningStock($product_id)
    {
        $product = Product::with(['unit', 'brand', 'category_relation'])
                          ->where('completion_status', 'profile_only')
                          ->findOrFail($product_id);

        // ✅ OWNERSHIP CHECK
        $userBranchId = Auth::check() ? Auth::user()->branch_id : null;
        $isSuperAdmin = Auth::check() && Auth::user()->hasRole('super admin');
        
        if (!$isSuperAdmin && $userBranchId !== $product->branch_id) {
            abort(403, 'You can only complete products from your own branch');
        }

        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        // Get warehouses for the product's branch
        $branch = $product->branch;
        $warehouses = $branch ? $branch->warehouses : collect();
        
        return view('admin_panel.product.opening-stock', compact(
            'product', 'warehouses', 'isSuperAdmin', 'user'
        ));
    }

    /**
     * Index: Show all incomplete products (completion_status = 'profile_only')
     * For navbar "Opening Stocks" link
     */
    public function incompleteProducts()
    {
        $userBranch = Auth::check() ? Auth::user()->branch_id : null;

        $query = Product::where('completion_status', 'profile_only')
                        ->with(['unit', 'brand', 'category_relation', 'branch']);

        if (Auth::check() && !Auth::user()->hasRole('super admin')) {
            $query->where('branch_id', $userBranch);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin_panel.product.incomplete-products', compact('products', 'userBranch'));
    }

    public function searchPartName(Request $request)
    {
        $q = $request->get('q', '');

        $parts = Product::query()
            ->where('is_part', 1)
            ->leftJoin('v_stock_onhand as v', 'v.product_id', '=', 'products.id')
            ->where(function ($x) use ($q) {
                $x->where('products.item_name', 'like', "%{$q}%")
                    ->orWhere('products.item_code', 'like', "%{$q}%");
            })
            ->select([
                'products.id',
                'products.item_name',
                'products.item_code',
                'products.unit_id',
                DB::raw('COALESCE(v.onhand_qty,0) as available_qty'),
            ])
            ->limit(20)
            ->get();

        return response()->json($parts->map(function ($p) {
            return [
                'id'            => $p->id,
                'item_name'     => $p->item_name,
                'item_code'     => $p->item_code,
                'unit'          => optional(Unit::find($p->unit_id))->name ?? '',
                'available_qty' => (float)$p->available_qty,
            ];
        }));
    }



    // ===== Update product =====
    public function update(Request $request, $id)
    {
        $product      = Product::findOrFail($id);
        $user         = Auth::user();
        $userBranchId = $user ? $user->branch_id : null;
        $isSuperAdmin = $user && $user->hasRole('super admin');
        
        $targetBranchId = $isSuperAdmin 
            ? (int) ($request->branch_id ?? $product->branch_id ?? 1) 
            : (int) ($userBranchId ?: $product->branch_id ?: 1);

        $userId = auth()->id();
        if (!$request->filled('model')) {
            return redirect()->back()->withInput()->with('swal_error', 'Product cannot be updated without Model.');
        }

        // Image handle
        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/products'), $imageName);
            $imagePath = $imageName;
        }

        DB::transaction(function () use ($request, $product, $id, $userId, $imagePath, $targetBranchId) {
            $wholesalePrice = $request->has('wholesale_price') ? (float) $request->wholesale_price : (float) ($product->wholesale_price ?? 0);
            $retailPrice    = ($request->has('price') || $request->has('retail_price')) ? (float) ($request->price ?? $request->retail_price) : (float) ($product->price ?? 0);
            $alertQty       = $request->has('alert_quantity') ? (float) $request->alert_quantity : (float) ($product->alert_quantity ?? 0);

            $updateData = [
                'creater_id'      => $userId,
                'branch_id'       => $targetBranchId,
                'category_id'     => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'type_id'         => $request->type_id,
                'item_code'       => $request->item_code ?? $product->item_code,
                'item_name'       => $request->product_name ?? $product->item_name,
                'item_name_urdu'  => $request->item_name_urdu ?? $request->product_name_urdu ?? $product->item_name_urdu,
                'barcode_path'    => $request->barcode_path ?? $product->barcode_path,
                'unit_id'         => $request->unit ?? $product->unit_id,
                'tracking_type'   => $request->input('tracking_type', $product->tracking_type ?? 'standard'),
                'brand_id'        => $request->brand_id ?? $product->brand_id,
                'model'           => $request->model ?? $product->model,
                'hs_code'         => $request->has('hs_code') ? ($request->filled('hs_code') ? $request->hs_code : null) : $product->hs_code,
                'pack_type'       => (strcasecmp(trim($request->input('packing_type', '')), 'Customize') === 0 || (empty($request->input('packing_type')) && ((float)($request->piece_per_pack ?? 0) > 0 || (float)($request->pack_qty ?? 0) > 0))) ? 'Customize' : 'Standard',
                'pack_qty'        => (strcasecmp(trim($request->input('packing_type', '')), 'Customize') === 0 || (empty($request->input('packing_type')) && ((float)($request->piece_per_pack ?? 0) > 0 || (float)($request->pack_qty ?? 0) > 0))) ? (float) ($request->packing_qty ?? $request->pack_qty ?? 0) : 0,
                'piece_per_pack'  => (strcasecmp(trim($request->input('packing_type', '')), 'Customize') === 0 || (empty($request->input('packing_type')) && ((float)($request->piece_per_pack ?? 0) > 0 || (float)($request->pack_qty ?? 0) > 0))) ? (float) ($request->piece_per_pack ?? 0) : 0,
                'loose_piece'     => (strcasecmp(trim($request->input('packing_type', '')), 'Customize') === 0 || (empty($request->input('packing_type')) && ((float)($request->piece_per_pack ?? 0) > 0 || (float)($request->pack_qty ?? 0) > 0))) ? (float) ($request->loose_piece ?? 0) : 0,
                'image'           => $imagePath,
                'is_part'         => $request->has('is_part') ? 1 : 0,
                'is_assembled'    => $request->has('is_assembled') ? 1 : 0,
                'wholesale_price' => $wholesalePrice,
                'price'           => $retailPrice,
                'alert_quantity'  => $alertQty,
                'updated_at'      => now(),
            ];

            if ($request->filled('color') || $request->has('color')) {
                $colors = $request->input('color');
                $updateData['color'] = !empty($colors) ? (is_array($colors) ? json_encode(array_values(array_filter($colors))) : $colors) : null;
            } else {
                $updateData['color'] = null;
            }

            $hasVariants = $request->boolean('has_variants');
            $updateData['has_variants'] = $hasVariants ? 1 : 0;

            if ($hasVariants && $request->has('variants') && is_array($request->variants)) {
                $variantColors = [];
                foreach ($request->variants as $v) {
                    $vc = trim($v['color'] ?? '');
                    if ($vc !== '' && !in_array($vc, $variantColors)) {
                        $variantColors[] = $vc;
                    }
                }
                if (!empty($variantColors)) {
                    $updateData['color'] = json_encode($variantColors);
                }
            }

            $hasStockInput = $request->has('initial_stock') || $request->has('stock_quantity');
            if ($hasStockInput) {
                $newStockQty = (float) ($request->initial_stock ?? $request->stock_quantity ?? 0);
                $updateData['initial_stock'] = $newStockQty;
            } else {
                $newStockQty = null;
            }

            Product::where('id', $id)->update($updateData);

            // Sync Product Variants if has_variants is true
            if ($hasVariants && $request->has('variants') && is_array($request->variants)) {
                $keptIds = [];
                foreach ($request->variants as $idx => $v) {
                    $vColor = trim($v['color'] ?? '');
                    $vName  = !empty($v['variant_name']) ? trim($v['variant_name']) : $vColor;
                    if ($vColor === '' && $vName === '') continue;

                    $vId = !empty($v['id']) ? (int)$v['id'] : null;
                    $vData = [
                        'product_id'      => $id,
                        'variant_name'    => $vName ?: $vColor,
                        'variant_code'    => !empty($v['variant_code']) ? trim($v['variant_code']) : ($product->item_code . '-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $vColor ?: 'VAR'), 0, 3))),
                        'color'           => $vColor ?: $vName,
                        'size'            => !empty($v['size']) ? trim($v['size']) : null,
                        'barcode'         => !empty($v['barcode']) ? trim($v['barcode']) : ($product->barcode_path . '-' . ($idx + 1)),
                        'wholesale_price' => isset($v['wholesale_price']) && is_numeric($v['wholesale_price']) ? (float)$v['wholesale_price'] : 0,
                        'retail_price'    => isset($v['retail_price']) && is_numeric($v['retail_price']) ? (float)$v['retail_price'] : 0,
                        'alert_quantity'  => isset($v['alert_quantity']) && is_numeric($v['alert_quantity']) ? (int)$v['alert_quantity'] : 0,
                        'status'          => 1,
                    ];

                    if ($vId && $existing = \App\Models\ProductVariant::where('id', $vId)->where('product_id', $id)->first()) {
                        $existing->update($vData);
                        $keptIds[] = $existing->id;
                    } else {
                        $newVar = \App\Models\ProductVariant::create($vData);
                        $keptIds[] = $newVar->id;
                    }
                }
                \App\Models\ProductVariant::where('product_id', $id)->whereNotIn('id', $keptIds)->delete();
            } elseif (!$hasVariants) {
                \App\Models\ProductVariant::where('product_id', $id)->delete();
            }

            // BOM re-save
            DB::table('product_boms')->where('product_id', $id)->delete();

            if ($request->has('is_assembled') && $request->is_assembled && $request->filled('bom_json')) {
                $rows = collect(json_decode($request->bom_json, true))
                    ->filter(fn($r) => !empty($r['part_id']) && (float)($r['required_per_unit'] ?? 0) > 0)
                    ->map(fn($r) => [
                        'product_id'   => $id,
                        'part_id'      => $r['part_id'],
                        'qty_per_unit' => (float)$r['required_per_unit'],
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ])->values();

                if ($rows->count()) {
                    DB::table('product_boms')->insert($rows->all());
                }
            }

            // Stock update & movement tracking
            if ($newStockQty !== null) {
                $currentQty = (float) DB::table('warehouse_stocks')
                    ->where('product_id', $id)
                    ->where('branch_id', $targetBranchId)
                    ->sum('quantity');

                if ($currentQty == 0) {
                    $currentQty = (float) (DB::table('stocks')
                        ->where('product_id', $id)
                        ->where('branch_id', $targetBranchId)
                        ->value('qty') ?? 0);
                }

                $delta = $newStockQty - $currentQty;

                if ($delta != 0) {
                    StockMovement::create([
                        'product_id' => $id,
                        'branch_id'  => $targetBranchId,
                        'type'       => $delta > 0 ? 'in' : 'out',
                        'qty'        => abs($delta),
                        'ref_type'   => 'PRODUCT_EDIT',
                        'ref_id'     => null,
                        'note'       => 'Stock updated from product edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                Stock::updateOrCreate(
                    ['product_id' => $id, 'branch_id' => $targetBranchId],
                    ['qty' => $newStockQty, 'updated_at' => now()]
                );

                WarehouseStock::where('product_id', $id)
                    ->where('branch_id', $targetBranchId)
                    ->delete();

                DB::table('warehouse_stocks')->insert([
                    'branch_id'    => $targetBranchId,
                    'warehouse_id' => null,
                    'product_id'   => $id,
                    'quantity'     => $newStockQty,
                    'price'        => $retailPrice,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            } elseif ($retailPrice > 0) {
                WarehouseStock::where('product_id', $id)
                    ->where('branch_id', $targetBranchId)
                    ->update(['price' => $retailPrice, 'updated_at' => now()]);
            }
        });

        return redirect()->route('product')->with('success', 'Product updated successfully');
    }

    // ===== Edit view =====
    public function edit($id)
    {
        $product = Product::with('category_relation', 'sub_category_relation', 'unit', 'brand', 'stock', 'variants')->findOrFail($id);
        
        $user         = Auth::user();
        $userBranchId = $user ? $user->branch_id : null;
        $isSuperAdmin = $user && $user->hasRole('super admin');
        
        if ($isSuperAdmin) {
            $branches = Branch::all();
        } else {
            $userBranch = $userBranchId ? Branch::find($userBranchId) : null;
            $branches   = $userBranch ? collect([$userBranch]) : Branch::all();
        }

        $targetBranchId = $isSuperAdmin ? ($product->branch_id ?: 1) : ($userBranchId ?: $product->branch_id ?: 1);

        $currentStock = (float) DB::table('warehouse_stocks')
            ->where('product_id', $id)
            ->where('branch_id', $targetBranchId)
            ->sum('quantity');

        if ($currentStock == 0) {
            $currentStock = (float) (DB::table('stocks')
                ->where('product_id', $id)
                ->where('branch_id', $targetBranchId)
                ->value('qty') ?? 0);
        }

        $product->current_branch_stock = $currentStock;

        $categories    = Category::all();
        $subcategories = SubCategory::all();
        $brands        = Brand::all();
        $units         = Unit::select('id', 'name')->get();
        $types         = \App\Models\ProductType::all();

        $productSettings = \App\Models\ProductSetting::getSettings();

        return view('admin_panel.product.edit', compact('product', 'categories', 'subcategories', 'brands', 'units', 'types', 'branches', 'isSuperAdmin', 'user', 'productSettings'));
    }

    // ===== Barcode view =====
    public function barcode($id)
    {
        $product = Product::findOrFail($id);
        return view('admin_panel.product.barcode', compact('product'));
    }

    // ===== Get warehouses with product stock =====
    public function warehouses(Request $request)
    {
        try {
            $productId = $request->query('product_id');
            
            if (!$productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product ID required'
                ], 400);
            }

            // Get warehouses with available stock for this product
            $warehouses = DB::table('warehouse_stocks as ws')
                ->join('warehouses as w', 'ws.warehouse_id', '=', 'w.id')
                ->where('ws.product_id', $productId)
                ->where('ws.quantity', '>', 0)
                ->select(
                    'ws.warehouse_id',
                    'w.warehouse_name',
                    'ws.quantity'
                )
                ->get();

            return response()->json([
                'success' => true,
                'warehouses' => $warehouses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading warehouses: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===== Stock locations report view =====
    public function stockLocations()
    {
        $allowedBranchIds = $this->getAllowedBranchIds();
        $branches = Branch::whereIn('id', $allowedBranchIds)->get();

        // Show branch selector to super admin or when user has access to multiple branches.
        $showBranchSelect = false;
        if (Auth::check() && Auth::user()->hasRole('super admin')) {
            $showBranchSelect = true;
        } elseif ($branches->count() > 1) {
            $showBranchSelect = true;
        }

        $selectedBranchId = null;
        if (!$showBranchSelect) {
            $selectedBranchId = $branches->first()->id ?? null;
        }

        return view('admin_panel.stock.locations', compact('branches', 'showBranchSelect', 'selectedBranchId'));
    }

    // ===== Stock locations data (for selected branch + product) =====
    public function stockLocationsData(Request $request)
    {
        $branchId = (int) $request->query('branch_id');
        $productId = (int) $request->query('product_id');

        if (!$branchId || !$productId) {
            return response()->json([
                'success' => false,
                'message' => 'Branch and product are required'
            ], 400);
        }

        // Ensure the user is allowed to view this branch
        $allowedBranchIds = $this->getAllowedBranchIds();
        if (!in_array($branchId, $allowedBranchIds, true)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view stock for this branch.'
            ], 403);
        }

        try {
            // Branch stock (warehouse_id = NULL)
            $branchStock = DB::table('warehouse_stocks')
                ->where('branch_id', $branchId)
                ->whereNull('warehouse_id')
                ->where('product_id', $productId)
                ->select(DB::raw("'Branch Stock' as location"), 'quantity')
                ->first();

            // Warehouse stocks
            $warehouseStocks = DB::table('warehouse_stocks as ws')
                ->leftJoin('warehouses as w', 'ws.warehouse_id', '=', 'w.id')
                ->where('ws.branch_id', $branchId)
                ->where('ws.product_id', $productId)
                ->whereNotNull('ws.warehouse_id')
                ->select(DB::raw("COALESCE(w.warehouse_name, 'Unknown Warehouse') as location"), 'ws.quantity')
                ->get();

            $locations = [];
            if ($branchStock) {
                $locations[] = ['location' => $branchStock->location, 'quantity' => (float)$branchStock->quantity];
            }

            foreach ($warehouseStocks as $row) {
                $locations[] = ['location' => $row->location, 'quantity' => (float)$row->quantity];
            }

            // If no records exist, show an empty row (handled in JS)
            return response()->json([
                'success' => true,
                'locations' => $locations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading stock locations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine which branches the current user is allowed to access.
     *
     * Super admin: all branches.
     * Other users: own branch + any branches granted via 'branch:{id}:product.view' permissions.
     */
    private function getAllowedBranchIds(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();

        if ($user->hasRole('super admin')) {
            return Branch::pluck('id')->toArray();
        }

        $allowed = [(int) $user->branch_id];
        Branch::pluck('id')->each(function ($bid) use ($user, &$allowed) {
            $bid = (int) $bid;
            if ($bid !== (int) $user->branch_id && $user->can("branch:{$bid}:product.view")) {
                $allowed[] = $bid;
            }
        });

        return array_unique($allowed);
    }
        // ===== Check for duplicate product name and model =====
    public function checkProductName(Request $request)
    {
        $productName = $request->query('name');
        $productModel = $request->query('model');

        if (!$productName && !$productModel) {
            return response()->json(['exists' => false]);
        }

        $exists = false;

        // Check for duplicate name
        if ($productName) {
            $exists = Product::where('item_name', $productName)->exists();
        }

        // Check for duplicate model
        if (!$exists && $productModel) {
            $exists = Product::where('model', $productModel)->exists();
        }

        return response()->json(['exists' => $exists]);
    }

    // ==========================================
    // ✅ NEW UNIFIED OPENING STOCK MANAGER
    // ==========================================

    /**
     * Index: Opening Stocks List Register
     * Shows all products with opening stock, warehouse allocations, prices, and actions (edit, delete)
     */
    public function openingStockIndex(Request $request)
    {
        $user         = Auth::user();
        $isSuperAdmin = $user ? $user->hasRole('super admin') : false;
        $userBranchId = $user ? (int) $user->branch_id : 1;
        $allBranches  = Branch::orderBy('name')->get();
        $categories   = Category::orderBy('name')->get();

        // Branch filter
        $branchId = $request->get('branch_id');
        if (!$isSuperAdmin) {
            $branchId = $userBranchId;
        } elseif ($branchId === null || $branchId === '') {
            $branchId = 'all';
        }

        $categoryId = $request->get('category_id');
        $status     = $request->get('status', 'opening_set'); // 'opening_set', 'pending', 'all'

        $query = Product::with([
            'unit',
            'category_relation',
            'brand',
            'branch',
            'warehouseStocks.warehouse',
            'warehouseStocks.branch',
            'warehouseStocks.variant',
            'variants',
            'stocks'
        ]);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($branchId && $branchId !== 'all') {
            $bid = (int) $branchId;
            $query->where(function ($q) use ($bid) {
                $q->where('branch_id', $bid)
                  ->orWhereHas('warehouseStocks', function ($ws) use ($bid) {
                      $ws->where('branch_id', $bid);
                  })
                  ->orWhereHas('stocks', function ($st) use ($bid) {
                      $st->where('branch_id', $bid);
                  });
            });
        }

        // Status filter
        if ($status === 'opening_set') {
            $query->where(function ($q) use ($branchId) {
                $q->where('initial_stock', '>', 0)
                  ->orWhereExists(function ($sub) use ($branchId) {
                      $sub->select(DB::raw(1))
                          ->from('stock_movements')
                          ->whereColumn('stock_movements.product_id', 'products.id')
                          ->whereIn('stock_movements.ref_type', ['OPENING', 'OPENING_ADJ', 'INITIAL_STOCK'])
                          ->when($branchId && $branchId !== 'all', fn($sm) => $sm->where('branch_id', (int) $branchId));
                  })
                  ->orWhereExists(function ($sub2) use ($branchId) {
                      $sub2->select(DB::raw(1))
                          ->from('warehouse_stocks')
                          ->whereColumn('warehouse_stocks.product_id', 'products.id')
                          ->where('warehouse_stocks.quantity', '>', 0)
                          ->when($branchId && $branchId !== 'all', fn($ws) => $ws->where('branch_id', (int) $branchId));
                  });
            });
        } elseif ($status === 'pending') {
            $query->where(function ($q) {
                $q->whereNull('initial_stock')->orWhere('initial_stock', '<=', 0);
            })->whereDoesntHave('warehouseStocks', function ($ws) {
                $ws->where('quantity', '>', 0);
            });
        }

        $products = $query->orderBy('item_name')->get();

        // Fetch all opening stock movements for these products in a single bulk query
        $productIds = $products->pluck('id')->toArray();
        $openingMovements = collect();
        if (!empty($productIds)) {
            $openingMovements = DB::table('stock_movements')
                ->whereIn('product_id', $productIds)
                ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ', 'INITIAL_STOCK'])
                ->when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', (int) $branchId))
                ->orderBy('id', 'desc')
                ->get()
                ->groupBy('product_id');
        }

        // Transform records — expand variant products into separate rows per variant
        $items = $products->flatMap(function ($product) use ($branchId, $openingMovements, $userBranchId, $status) {
            $pId = $product->id;

            // Target branch for this item
            $targetBranchId = ($branchId && $branchId !== 'all')
                ? (int) $branchId
                : ($product->branch_id ?: ($userBranchId ?: 1));

            // Warehouse allocations list for this branch
            $whList = $product->warehouseStocks;
            if ($branchId && $branchId !== 'all') {
                $whList = $whList->where('branch_id', (int) $branchId);
            }

            // Variant product: emit a separate row for each variant ONLY IF variant-specific stocks exist
            $hasVariantSpecificStock = $whList->whereNotNull('variant_id')->isNotEmpty();

            if ($product->has_variants && $product->variants->isNotEmpty() && $hasVariantSpecificStock) {
                $variantRows = [];
                $whByVariant = $whList->groupBy('variant_id');

                foreach ($product->variants as $pv) {
                    $vWhList = $whByVariant->get($pv->id, collect());
                    $vOpeningQty = (float) $vWhList->sum('quantity');
                    $vCurrentStock = $vOpeningQty;

                    // If filtered by 'opening_set', only include variants that have stock
                    if ($status === 'opening_set' && $vOpeningQty <= 0) {
                        continue;
                    }

                    $vAllocations = $vWhList->map(function ($ws) use ($pv) {
                        return [
                            'warehouse_name' => $ws->warehouse ? $ws->warehouse->warehouse_name : 'Shop / Counter',
                            'branch_name'    => $ws->branch ? $ws->branch->name : 'Branch',
                            'quantity'       => (float) $ws->quantity,
                            'is_shop'        => is_null($ws->warehouse_id),
                            'variant_id'     => $pv->id,
                            'variant_name'   => $pv->variant_name ?: $pv->color,
                            'color'          => $pv->color,
                        ];
                    })->values();

                    $vWholesalePrice = (float) ($pv->wholesale_price ?: ($product->wholesale_price ?? 0));
                    $vRetailPrice    = (float) ($pv->retail_price ?: ($product->price ?? 0));
                    $vValuation      = $vOpeningQty * $vWholesalePrice;

                    $colorLabel = $pv->color ? " ({$pv->color})" : '';
                    $rowTitle = $product->item_name . $colorLabel;

                    $variantRows[] = (object) [
                        'id'                 => $product->id,
                        'variant_id'         => $pv->id,
                        'item_code'          => $product->item_code,
                        'item_name'          => $rowTitle,
                        'base_item_name'     => $product->item_name,
                        'color'              => $pv->color,
                        'item_name_urdu'     => $product->item_name_urdu,
                        'has_variants'       => true,
                        'category_name'      => optional($product->category_relation)->name ?? 'General',
                        'brand_name'         => optional($product->brand)->name ?? '-',
                        'unit_name'          => optional($product->unit)->name ?? 'PCS',
                        'piece_per_pack'     => (float) ($product->piece_per_pack ?? 1),
                        'pack_type'          => $product->pack_type ?? 'Standard',
                        'branch_id'          => $targetBranchId,
                        'branch_name'        => optional($product->branch)->name ?? ('Branch ' . $targetBranchId),
                        'opening_qty'        => $vOpeningQty,
                        'current_stock'      => $vCurrentStock,
                        'wholesale_price'    => $vWholesalePrice,
                        'retail_price'       => $vRetailPrice,
                        'valuation'          => $vValuation,
                        'allocations'        => $vAllocations,
                        'completion_status'  => $product->completion_status,
                        'updated_at'         => $product->updated_at ? $product->updated_at->format('d M Y, h:i A') : '-',
                    ];
                }

                if (!empty($variantRows)) {
                    return $variantRows;
                }
            }

            // Standard product (no variants)
            $movements = $openingMovements->get($pId, collect());
            $movOpening = $movements->where('type', 'in')->sum('qty');
            if ($movements->isNotEmpty() && $movOpening > 0) {
                $openingQty = (float) $movOpening;
            } elseif ($product->initial_stock !== null && $product->initial_stock > 0) {
                $openingQty = (float) $product->initial_stock;
            } else {
                $openingQty = (float) $whList->sum('quantity');
            }

            if ($branchId && $branchId !== 'all') {
                $currentStock = (float) ($product->stocks->where('branch_id', (int) $branchId)->first()?->qty ?? 0);
            } else {
                $currentStock = (float) $product->stocks->sum('qty');
            }

            $allocations = $whList->map(function ($ws) {
                return [
                    'warehouse_name' => $ws->warehouse ? $ws->warehouse->warehouse_name : 'Shop / Counter',
                    'branch_name'    => $ws->branch ? $ws->branch->name : 'Branch',
                    'quantity'       => (float) $ws->quantity,
                    'is_shop'        => is_null($ws->warehouse_id),
                ];
            })->values();

            $wholesalePrice = (float) ($product->wholesale_price ?? 0);
            $retailPrice    = (float) ($product->price ?? 0);
            $valuation      = $openingQty * $wholesalePrice;

            return [(object) [
                'id'                 => $product->id,
                'variant_id'         => null,
                'item_code'          => $product->item_code,
                'item_name'          => $product->item_name,
                'base_item_name'     => $product->item_name,
                'color'              => null,
                'item_name_urdu'     => $product->item_name_urdu,
                'has_variants'       => false,
                'category_name'      => optional($product->category_relation)->name ?? 'General',
                'brand_name'         => optional($product->brand)->name ?? '-',
                'unit_name'          => optional($product->unit)->name ?? 'PCS',
                'piece_per_pack'     => (float) ($product->piece_per_pack ?? 1),
                'pack_type'          => $product->pack_type ?? 'Standard',
                'branch_id'          => $targetBranchId,
                'branch_name'        => optional($product->branch)->name ?? ('Branch ' . $targetBranchId),
                'opening_qty'        => $openingQty,
                'current_stock'      => $currentStock,
                'wholesale_price'    => $wholesalePrice,
                'retail_price'       => $retailPrice,
                'valuation'          => $valuation,
                'allocations'        => $allocations,
                'completion_status'  => $product->completion_status,
                'updated_at'         => $product->updated_at ? $product->updated_at->format('d M Y, h:i A') : '-',
            ]];
        });

        // Summary KPI Totals
        $kpi = [
            'total_items'      => $items->count(),
            'total_with_stock' => $items->where('opening_qty', '>', 0)->count(),
            'total_opening_qty'=> $items->sum('opening_qty'),
            'total_valuation'  => $items->sum('valuation'),
            'total_current_qty'=> $items->sum('current_stock'),
        ];

        return view('admin_panel.product.opening-stocks-index', compact(
            'items', 'kpi', 'allBranches', 'categories', 'branchId', 'categoryId', 'status', 'isSuperAdmin', 'userBranchId'
        ));
    }

    /**
     * Show unified Opening Stock Manager page
     * Super admin: sees branch selector + all products
     * Regular user: auto-branch, no selector
     */
    public function openingStockManager()
    {
        $user         = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        $branches     = $isSuperAdmin ? \App\Models\Branch::orderBy('name')->get() : collect();
        $userBranchId = $user->branch_id;

        // Super Admin may have no branch_id — default to first branch
        if ($isSuperAdmin && !$userBranchId) {
            $userBranchId = $branches->first()?->id;
        }

        // Fetch warehouses assigned to the currently selected branch
        $warehouses = collect();
        if ($userBranchId) {
            $warehouses = \App\Models\Warehouse::whereHas('branches', function ($q) use ($userBranchId) {
                $q->where('branch_id', $userBranchId);
            })->orderBy('warehouse_name')->get();
        }

        return view('admin_panel.product.opening-stocks-manager', compact(
            'isSuperAdmin', 'branches', 'userBranchId', 'warehouses'
        ));
    }


    /**
     * AJAX: Search products for opening stock manager
     * Returns products filtered by branch_id
     */
    public function searchProductsForStock(Request $request)
    {
        $q = trim($request->get('q', ''));

        // Products are GLOBAL — any branch can add opening stock for any product
        // Branch is only used to store the STOCK, not to filter products
        $query = Product::with(['unit', 'variants'])
            ->when($q !== '', function ($q2) use ($q) {
                $q2->where(function ($q3) use ($q) {
                    $q3->where('item_name', 'like', "%{$q}%")
                       ->orWhere('item_code', 'like', "%{$q}%")
                       ->orWhereHas('variants', function ($vq) use ($q) {
                           $vq->where('color', 'like', "%{$q}%")
                              ->orWhere('variant_name', 'like', "%{$q}%")
                              ->orWhere('barcode', 'like', "%{$q}%")
                              ->orWhere('variant_code', 'like', "%{$q}%");
                       });
                });
            })
            ->orderBy('item_name')
            ->limit(50)
            ->get(['id', 'item_name', 'item_code', 'unit_id', 'branch_id', 'has_variants',
                   'wholesale_price', 'price', 'alert_quantity', 'initial_stock', 'piece_per_pack', 'pack_type', 'tracking_type']);

        $branchId = (int) $request->get('branch_id', Auth::user()->branch_id ?? 0);
        $results = [];

        foreach ($query as $p) {
            $curStock = $branchId
                ? (Stock::where('product_id', $p->id)->where('branch_id', $branchId)->value('qty') ?? 0)
                : (Stock::where('product_id', $p->id)->where('branch_id', $p->branch_id)->value('qty') ?? 0);

            if ($p->has_variants && $p->variants->isNotEmpty()) {
                foreach ($p->variants as $v) {
                    if ($q !== '') {
                        $qLower = strtolower($q);
                        $matchesProduct = str_contains(strtolower($p->item_name), $qLower) || str_contains(strtolower($p->item_code), $qLower);
                        $matchesVariant = str_contains(strtolower($v->color ?? ''), $qLower)
                            || str_contains(strtolower($v->variant_name ?? ''), $qLower)
                            || str_contains(strtolower($v->barcode ?? ''), $qLower)
                            || str_contains(strtolower($v->variant_code ?? ''), $qLower);

                        if (!$matchesProduct && !$matchesVariant) {
                            continue;
                        }
                    }

                    $vColor = $v->color ? " ({$v->color})" : '';
                    if ($v->variant_name && strtolower($v->variant_name) !== strtolower($v->color ?? '')) {
                        $displayTitle = $v->variant_name . $vColor;
                    } else {
                        $displayTitle = $p->item_name . $vColor;
                    }

                    $results[] = [
                        'id'              => $p->id . '_var_' . $v->id,
                        'product_id'      => $p->id,
                        'variant_id'      => $v->id,
                        'text'            => $displayTitle . ' — ' . $p->item_code,
                        'display_title'   => $displayTitle,
                        'item_name'       => $p->item_name,
                        'item_code'       => $p->item_code,
                        'variant_name'    => $v->variant_name ?: $v->color,
                        'color'           => $v->color,
                        'barcode'         => $v->barcode ?: $p->item_code,
                        'unit'            => optional($p->unit)->name ?? 'PCS',
                        'piece_per_pack'  => (float)($p->piece_per_pack ?? 1),
                        'pack_type'       => $p->pack_type ?? 'Standard',
                        'has_variants'    => true,
                        'tracking_type'   => $p->tracking_type ?? 'standard',
                        'is_serial'       => strtolower($p->tracking_type ?? '') === 'serial',
                        'wholesale_price' => (float)($v->wholesale_price ?: ($p->wholesale_price ?? 0)),
                        'retail_price'    => (float)($v->retail_price ?: ($p->price ?? 0)),
                        'alert_quantity'  => (float)($p->alert_quantity ?? 0),
                        'current_stock'   => $curStock,
                    ];
                }
            } else {
                $results[] = [
                    'id'              => (string)$p->id,
                    'product_id'      => $p->id,
                    'variant_id'      => null,
                    'text'            => $p->item_name . ' — ' . $p->item_code,
                    'display_title'   => $p->item_name,
                    'item_name'       => $p->item_name,
                    'item_code'       => $p->item_code,
                    'variant_name'    => null,
                    'color'           => null,
                    'barcode'         => $p->item_code,
                    'unit'            => optional($p->unit)->name ?? 'PCS',
                    'piece_per_pack'  => (float)($p->piece_per_pack ?? 1),
                    'pack_type'       => $p->pack_type ?? 'Standard',
                    'has_variants'    => false,
                    'tracking_type'   => $p->tracking_type ?? 'standard',
                    'is_serial'       => strtolower($p->tracking_type ?? '') === 'serial',
                    'wholesale_price' => (float)($p->wholesale_price ?? 0),
                    'retail_price'    => (float)($p->price ?? 0),
                    'alert_quantity'  => (float)($p->alert_quantity ?? 0),
                    'current_stock'   => $curStock,
                ];
            }
        }

        return response()->json($results);
    }

    /**
     * AJAX: Get warehouses for a branch (called when super admin changes branch)
     */
    public function getWarehousesForBranch(Request $request)
    {
        $branchId = $request->get('branch_id');

        $warehouses = collect();
        if ($branchId) {
            $warehouses = \App\Models\Warehouse::whereHas('branches', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })->orderBy('warehouse_name')->get(['id', 'warehouse_name']);
        }

        return response()->json($warehouses->map(fn($w) => [
            'id'            => $w->id,
            'warehouse_name'=> $w->warehouse_name,
            'name'          => $w->warehouse_name,
        ]));
    }

    /**
     * AJAX: Get stock breakdown for a product in a branch
     * Returns branch total + per-warehouse rows
     */
    public function getProductStockBreakdown(Request $request)
    {
        $productId = (int) $request->get('product_id');
        $branchId  = (int) $request->get('branch_id');

        if (!$productId || !$branchId) {
            return response()->json(['total' => 0, 'locations' => []]);
        }

        // Branch total from stocks table
        $total = DB::table('stocks')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->value('qty') ?? 0;

        // Per-location breakdown from warehouse_stocks (qty > 0)
        $rows = DB::table('warehouse_stocks as ws')
            ->leftJoin('warehouses as w', 'ws.warehouse_id', '=', 'w.id')
            ->where('ws.product_id', $productId)
            ->where('ws.branch_id', $branchId)
            ->where('ws.quantity', '>', 0)
            ->select('ws.warehouse_id', 'ws.quantity', 'w.warehouse_name')
            ->get();

        $locations = $rows->map(fn($r) => [
            'label' => is_null($r->warehouse_id) ? '🏪 Branch / Shop' : ('📦 ' . ($r->warehouse_name ?? 'Warehouse')),
            'qty'   => (float) $r->quantity,
        ]);

        return response()->json([
            'total'     => (float) $total,
            'locations' => $locations,
        ]);
    }

    /**
     * Store batch opening stocks
     * rows[] = [{product_id, opening_qty, alert_qty, wholesale_price, retail_price, allocation_data}]
     * Logic: ADDITIVE — adds on top of existing stock
     */
    public function storeOpeningStocks(Request $request)
    {
        $user         = Auth::user();
        $isSuperAdmin = $user ? $user->hasRole('super admin') : false;
        $branchId     = $isSuperAdmin
            ? (int) $request->input('branch_id', $user?->branch_id)
            : (int) ($user?->branch_id ?: $request->input('branch_id'));

        // Guard: branch_id must be valid
        if (!$branchId) {
            return response()->json(['error' => 'Branch not selected. Please select a branch first.'], 422);
        }

        // Verify branch exists in DB
        $branchExists = DB::table('branches')->where('id', $branchId)->exists();
        if (!$branchExists) {
            return response()->json(['error' => "Branch ID {$branchId} does not exist."], 422);
        }

        $rows = $request->input('rows', []);
        if (empty($rows)) {
            return response()->json(['error' => 'No rows submitted.'], 422);
        }

        try {
            DB::transaction(function () use ($rows, $branchId) {
                // Group submitted rows by product_id to support multiple variant rows per product
                $grouped = [];
                foreach ($rows as $row) {
                    $productId = (int) ($row['product_id'] ?? 0);
                    $opening   = (float) ($row['opening_qty'] ?? 0);
                    if (!$productId || $opening <= 0) continue;

                    if (!isset($grouped[$productId])) {
                        $grouped[$productId] = [
                            'product_id'      => $productId,
                            'total_opening'   => 0,
                            'alert_qty'       => (float) ($row['alert_qty'] ?? 0),
                            'wholesale_price' => (float) ($row['wholesale_price'] ?? 0),
                            'retail_price'    => (float) ($row['retail_price'] ?? 0),
                            'alloc_items'     => [],
                        ];
                    }
                    $grouped[$productId]['total_opening'] += $opening;
                    if ((float) ($row['wholesale_price'] ?? 0) > 0) {
                        $grouped[$productId]['wholesale_price'] = (float) $row['wholesale_price'];
                    }
                    if ((float) ($row['retail_price'] ?? 0) > 0) {
                        $grouped[$productId]['retail_price'] = (float) $row['retail_price'];
                    }
                    if ((float) ($row['alert_qty'] ?? 0) > 0) {
                        $grouped[$productId]['alert_qty'] = (float) $row['alert_qty'];
                    }

                    $rowVariantId = !empty($row['variant_id']) ? (int) $row['variant_id'] : null;
                    $allocData = json_decode($row['allocation_data'] ?? '[]', true) ?? [];
                    foreach ($allocData as $a) {
                        if (empty($a['variant_id']) && $rowVariantId) {
                            $a['variant_id'] = $rowVariantId;
                        }
                        $grouped[$productId]['alloc_items'][] = $a;
                    }

                    // Update variant-specific pricing if variant selected
                    if ($rowVariantId) {
                        $vWholesale = (float) ($row['wholesale_price'] ?? 0);
                        $vRetail    = (float) ($row['retail_price'] ?? 0);
                        if ($vWholesale > 0 || $vRetail > 0) {
                            \App\Models\ProductVariant::where('id', $rowVariantId)->where('product_id', $productId)->update([
                                'wholesale_price' => $vWholesale,
                                'retail_price'    => $vRetail,
                            ]);
                        }
                    }
                }

                foreach ($grouped as $productId => $group) {
                    $product = Product::find($productId);
                    if (!$product) continue;

                    $opening   = $group['total_opening'];
                    $alertQty  = $group['alert_qty'];
                    $wholesale = $group['wholesale_price'];
                    $retail    = $group['retail_price'];

                    // Convert carton to base pieces if carton product
                    $isCarton = $product->isCartonProduct();
                    $ppp = $isCarton ? (float)($product->piece_per_pack ?? 1) : 1;
                    $multiplier = $ppp > 1 ? $ppp : 1;
                    $openingPieces = $opening * $multiplier;

                    $allocPiecesData = [];
                    foreach ($group['alloc_items'] as $a) {
                        $allocPiecesData[] = [
                            'location_type' => $a['location_type'] ?? 'shop',
                            'warehouse_id'  => $a['warehouse_id'] ?? null,
                            'variant_id'    => !empty($a['variant_id']) ? (int)$a['variant_id'] : null,
                            'quantity'      => ((float)($a['quantity'] ?? 0)) * $multiplier,
                        ];
                    }

                    // Compute delta against existing opening stock movement
                    $existingOpening = StockMovement::where('product_id', $productId)
                        ->where('branch_id', $branchId)
                        ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ', 'INITIAL_STOCK'])
                        ->where('type', 'in')
                        ->first();

                    $oldOpeningQty = $existingOpening ? (float)$existingOpening->qty : 0;
                    if ($oldOpeningQty <= 0 && (float)($product->initial_stock ?? 0) > 0 && ($product->branch_id == $branchId || !$product->branch_id)) {
                        $oldOpeningQty = (float)$product->initial_stock;
                    }
                    $delta = $openingPieces - $oldOpeningQty;

                    // Update prices & alert (initial_stock stored in base pieces)
                    $product->update([
                        'wholesale_price'   => $wholesale > 0 ? $wholesale : $product->wholesale_price,
                        'price'             => $retail > 0 ? $retail : $product->price,
                        'alert_quantity'    => $alertQty,
                        'initial_stock'     => $openingPieces,
                        'completion_status' => 'complete',
                    ]);

                    // Single clean OPENING stock movement in base pieces
                    if ($existingOpening) {
                        $existingOpening->update([
                            'qty'        => $openingPieces,
                            'type'       => 'in',
                            'ref_type'   => 'OPENING',
                            'note'       => 'Opening stock entry',
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('stock_movements')->insert([
                            'product_id'  => $productId,
                            'branch_id'   => $branchId,
                            'type'        => 'in',
                            'qty'         => $openingPieces,
                            'ref_type'    => 'OPENING',
                            'ref_id'      => null,
                            'note'        => 'Opening stock entry',
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                    }

                    // Variant-level OPENING stock movements & allocations
                    $varAllocs = [];
                    foreach ($allocPiecesData as $al) {
                        $vId = !empty($al['variant_id']) ? (int)$al['variant_id'] : null;
                        if ($vId) {
                            $varAllocs[$vId] = ($varAllocs[$vId] ?? 0) + (float)($al['quantity'] ?? 0);
                        }
                    }

                    if ($product->has_variants && !empty($varAllocs)) {
                        foreach ($varAllocs as $vId => $vQty) {
                            $existingVarOpening = StockMovement::where('product_id', $productId)
                                ->where('branch_id', $branchId)
                                ->where('variant_id', $vId)
                                ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ', 'INITIAL_STOCK'])
                                ->where('type', 'in')
                                ->first();
                            if ($existingVarOpening) {
                                $existingVarOpening->update([
                                    'qty'        => $vQty,
                                    'updated_at' => now(),
                                ]);
                            } else {
                                DB::table('stock_movements')->insert([
                                    'product_id'  => $productId,
                                    'branch_id'   => $branchId,
                                    'variant_id'  => $vId,
                                    'type'        => 'in',
                                    'qty'         => $vQty,
                                    'ref_type'    => 'OPENING',
                                    'ref_id'      => null,
                                    'note'        => 'Opening stock entry',
                                    'created_at'  => now(),
                                    'updated_at'  => now(),
                                ]);
                            }

                            // Net non-opening movements (sales, purchases, etc.)
                            $netNonOpening = (float) (DB::table('stock_movements')
                                ->where('product_id', $productId)
                                ->where('branch_id', $branchId)
                                ->where('variant_id', $vId)
                                ->whereNotIn('ref_type', ['OPENING', 'OPENING_ADJ', 'INITIAL_STOCK'])
                                ->selectRaw("SUM(CASE WHEN type = 'in' THEN qty ELSE -qty END) as net")
                                ->value('net') ?? 0);

                            $actualVarStock = max(0, $vQty + $netNonOpening);

                            $stk = Stock::where('product_id', $productId)
                                ->where('branch_id', $branchId)
                                ->where('variant_id', $vId)
                                ->first();
                            if ($stk) {
                                $stk->qty = $actualVarStock;
                                $stk->save();
                            } else {
                                Stock::create([
                                    'product_id'   => $productId,
                                    'branch_id'    => $branchId,
                                    'variant_id'   => $vId,
                                    'qty'          => $actualVarStock,
                                    'reserved_qty' => 0,
                                ]);
                            }
                        }

                        Stock::where('product_id', $productId)
                            ->where('branch_id', $branchId)
                            ->whereNull('variant_id')
                            ->delete();
                    } else {
                        // Net non-opening movements (sales, purchases, etc.)
                        $netNonOpening = (float) (DB::table('stock_movements')
                            ->where('product_id', $productId)
                            ->where('branch_id', $branchId)
                            ->whereNotIn('ref_type', ['OPENING', 'OPENING_ADJ', 'INITIAL_STOCK'])
                            ->selectRaw("SUM(CASE WHEN type = 'in' THEN qty ELSE -qty END) as net")
                            ->value('net') ?? 0);

                        $actualStock = max(0, $openingPieces + $netNonOpening);

                        $stockRecord = Stock::where('product_id', $productId)->where('branch_id', $branchId)->first();
                        if ($stockRecord) {
                            $stockRecord->qty = $actualStock;
                            $stockRecord->save();
                        } else {
                            Stock::create([
                                'product_id'   => $productId,
                                'branch_id'    => $branchId,
                                'qty'          => $actualStock,
                                'reserved_qty' => 0,
                            ]);
                        }
                    }

                    // Upsert warehouse_stocks in base pieces
                    $this->applyWarehouseAllocation($productId, $branchId, $retail, $allocPiecesData, $openingPieces);

                    // Save serial numbers if product is serial tracked
                    if ($product->isSerial() && !empty($group['alloc_items'])) {
                        \App\Models\ProductSerial::where('product_id', $productId)
                            ->where('branch_id', $branchId)
                            ->where('source_type', 'OPENING')
                            ->where('status', 'available')
                            ->delete();

                        foreach ($group['alloc_items'] as $itemAlloc) {
                            $locType = $itemAlloc['location_type'] ?? 'shop';
                            $wId     = ($locType === 'warehouse' && !empty($itemAlloc['warehouse_id'])) ? (int)$itemAlloc['warehouse_id'] : null;
                            $vId     = !empty($itemAlloc['variant_id']) ? (int)$itemAlloc['variant_id'] : null;
                            $serials = $itemAlloc['serials'] ?? [];

                            if (is_array($serials)) {
                                foreach ($serials as $sn) {
                                    $snClean = trim((string)$sn);
                                    if ($snClean !== '') {
                                        \App\Models\ProductSerial::create([
                                            'product_id'     => $productId,
                                            'variant_id'     => $vId,
                                            'branch_id'      => $branchId,
                                            'warehouse_id'   => $wId,
                                            'serial_number'  => $snClean,
                                            'status'         => 'available',
                                            'source_type'    => 'OPENING',
                                            'purchase_price' => $wholesale > 0 ? $wholesale : null,
                                            'sale_price'     => $retail > 0 ? $retail : null,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Save failed: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Opening stocks saved successfully!',
        ]);
    }

    /**
     * Show edit form for a single product's opening stock
     * Super admin: can choose which branch's stock to edit via ?branch_id query param
     * Regular user: always their own branch
     */
    public function editOpeningStock(Request $request, $id)
    {
        $product      = Product::with(['unit', 'category_relation', 'branch', 'variants'])->findOrFail($id);
        $user         = Auth::user();
        $isSuperAdmin = $user ? $user->hasRole('super admin') : false;
        $userBranchId = $user ? (int) $user->branch_id : 1;

        // Fetch ALL warehouses
        $warehouses = DB::table('warehouses')->orderBy('warehouse_name')->get();

        if ($isSuperAdmin) {
            $allBranches = Branch::orderBy('name')->get();

            // Which branch is selected? Query param > user's branch > product's branch > first branch
            $requestedBranch = $request->get('branch_id');
            if ($requestedBranch && $allBranches->contains('id', (int) $requestedBranch)) {
                $selectedBranchId = (int) $requestedBranch;
            } else {
                $selectedBranchId = (int) ($userBranchId ?: ($product->branch_id ?: ($allBranches->first()?->id ?? 1)));
            }

            // Find branches where this product has stock (for badges)
            $branchIdsWithStock = DB::table('warehouse_stocks')
                ->where('product_id', $id)
                ->pluck('branch_id')
                ->push($userBranchId ?: $product->branch_id)
                ->unique()
                ->values();

            $availableBranches = Branch::whereIn('id', $branchIdsWithStock)
                ->orderBy('name')
                ->get();

        } else {
            $allBranches       = collect();
            $availableBranches = collect();
            $selectedBranchId  = (int) ($userBranchId ?: ($product->branch_id ?: 1));
        }

        $selectedBranch = Branch::find($selectedBranchId);

        // Detect variant if product has variants
        $variantId = $request->get('variant_id');
        $selectedVariant = null;
        if ($product->has_variants && $product->variants->isNotEmpty()) {
            if ($variantId) {
                $selectedVariant = $product->variants->firstWhere('id', (int) $variantId);
            }
            if (!$selectedVariant) {
                $allocatedVarId = DB::table('warehouse_stocks')
                    ->where('product_id', $id)
                    ->where('branch_id', $selectedBranchId)
                    ->whereNotNull('variant_id')
                    ->value('variant_id');
                if ($allocatedVarId) {
                    $selectedVariant = $product->variants->firstWhere('id', (int) $allocatedVarId);
                } elseif ($product->variants->count() === 1) {
                    $selectedVariant = $product->variants->first();
                }
            }
        }

        if ($selectedVariant) {
            if ((float)($selectedVariant->wholesale_price ?? 0) > 0) {
                $product->wholesale_price = $selectedVariant->wholesale_price;
            }
            if ((float)($selectedVariant->retail_price ?? 0) > 0) {
                $product->price = $selectedVariant->retail_price;
            }
        }

        // Current warehouse allocations for the SELECTED branch (and variant if applicable)
        $rawAllocsQuery = DB::table('warehouse_stocks')
            ->where('product_id', $id)
            ->where('branch_id', $selectedBranchId);
        if ($selectedVariant) {
            $rawAllocsQuery->where('variant_id', $selectedVariant->id);
        } else {
            $rawAllocsQuery->whereNull('variant_id');
        }
        $rawAllocs = $rawAllocsQuery->get();

        // Group by warehouse_id (or null for shop) so each location has exactly 1 row
        $currentAllocs = $rawAllocs->groupBy(function($item) {
            return $item->warehouse_id ?: 'shop';
        })->map(function($group) {
            $first = clone $group->first();
            $first->quantity = $group->sum('quantity');
            return $first;
        })->values();

        // 1. Fetch warehouses belonging to the selected branch + any allocated warehouses
        $branchWarehouses = \App\Models\Warehouse::whereHas('branches', function($q) use ($selectedBranchId) {
            $q->where('branch_id', $selectedBranchId);
        })->orderBy('warehouse_name')->get();

        $allocatedWhIds = $currentAllocs->pluck('warehouse_id')->filter()->unique();
        if ($allocatedWhIds->isNotEmpty()) {
            $extraWarehouses = \App\Models\Warehouse::whereIn('id', $allocatedWhIds)->get();
            $warehouses = $branchWarehouses->merge($extraWarehouses)->unique('id')->sortBy('warehouse_name')->values();
        } else {
            $warehouses = $branchWarehouses;
        }

        // 2. Global Stock Summary (for Super Admin to see where stock is)
        $globalStockSummary = [];
        if ($isSuperAdmin) {
            $globalStockSummaryQuery = DB::table('stocks')
                ->join('branches', 'stocks.branch_id', '=', 'branches.id')
                ->where('stocks.product_id', $id)
                ->where('stocks.qty', '>', 0);
            if ($selectedVariant) {
                $globalStockSummaryQuery->where('stocks.variant_id', $selectedVariant->id);
            }
            $globalStockSummary = $globalStockSummaryQuery
                ->select('branches.name as branch_name', 'stocks.qty', 'stocks.branch_id')
                ->get();
        }

        // Current stock for the SELECTED branch
        $currentStockQuery = DB::table('warehouse_stocks')
            ->where('product_id', $id)
            ->where('branch_id', $selectedBranchId);
        if ($selectedVariant) {
            $currentStockQuery->where('variant_id', $selectedVariant->id);
        } else {
            $currentStockQuery->whereNull('variant_id');
        }
        $currentStock = (float) $currentStockQuery->sum('quantity');

        // Fallback to stocks table if warehouse_stocks has no records for this branch
        if ($currentStock == 0) {
            $fallbackQuery = DB::table('stocks')
                ->where('product_id', $id)
                ->where('branch_id', $selectedBranchId);
            if ($selectedVariant) {
                $fallbackQuery->where('variant_id', $selectedVariant->id);
            } else {
                $fallbackQuery->whereNull('variant_id');
            }
            $currentStock = (float) ($fallbackQuery->value('qty') ?? 0);
        }

        // Fetch existing serial numbers if product is serial-tracked
        $existingSerials = [];
        if ($product->isSerial()) {
            $serialsQuery = \App\Models\ProductSerial::where('product_id', $id)
                ->where('branch_id', $selectedBranchId)
                ->where('source_type', 'OPENING')
                ->where('status', 'available');
            if ($selectedVariant) {
                $serialsQuery->where('variant_id', $selectedVariant->id);
            }
            $serialsCollection = $serialsQuery->get(['warehouse_id', 'variant_id', 'serial_number']);

            foreach ($serialsCollection as $es) {
                $locKey = $es->warehouse_id ? ('wh_' . $es->warehouse_id) : 'shop';
                $existingSerials[$locKey][] = $es->serial_number;
            }
        }

        return view('admin_panel.product.opening-stock-edit', compact(
            'product', 'selectedVariant', 'isSuperAdmin', 'allBranches', 'availableBranches',
            'warehouses', 'currentAllocs', 'currentStock', 'selectedBranchId',
            'selectedBranch', 'globalStockSummary', 'existingSerials'
        ));
    }

    /**
     * Update a single product's opening stock
     * - Updates prices
     * - Adds delta stock movement
     * - Replaces warehouse allocations
     */
    public function updateOpeningStock(Request $request, $id)
    {
        $product      = Product::findOrFail($id);
        $user         = Auth::user();
        $isSuperAdmin = $user ? $user->hasRole('super admin') : false;
        $userBranchId = $user ? (int) $user->branch_id : 1;

        $branchId = $isSuperAdmin
            ? (int) $request->input('branch_id', $userBranchId ?: $product->branch_id)
            : (int) ($userBranchId ?: $product->branch_id);

        $variantId = $request->input('variant_id') ? (int) $request->input('variant_id') : null;
        $newQty    = (float) $request->input('opening_qty', 0);
        $alertQty  = (float) $request->input('alert_qty', 0);
        $wholesale = (float) $request->input('wholesale_price', 0);
        $retail    = (float) $request->input('retail_price', 0);
        $allocData = json_decode($request->input('allocation_data', '[]'), true) ?? [];

        if ($variantId) {
            foreach ($allocData as &$al) {
                if (empty($al['variant_id'])) {
                    $al['variant_id'] = $variantId;
                }
            }
            unset($al);
        }

        // Convert input qty to base pieces if carton product
        $isCarton = $product->isCartonProduct();
        $ppp = $isCarton ? (float)($product->piece_per_pack ?? 1) : 1;
        $multiplier = $ppp > 1 ? $ppp : 1;
        $newPieces = $newQty * $multiplier;

        // Compute delta between new opening stock (in pieces) and existing opening stock movements (in pieces)
        $oldOpeningQuery = DB::table('stock_movements')
            ->where('product_id', $id)
            ->where('branch_id', $branchId)
            ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ']);
        if ($variantId) {
            $oldOpeningQuery->where('variant_id', $variantId);
        }
        $oldOpeningQty = (float) $oldOpeningQuery->sum('qty');

        if ($oldOpeningQty <= 0 && !$variantId) {
            $oldOpeningQty = (float) ($product->initial_stock ?? 0);
        }

        $delta = $newPieces - $oldOpeningQty;

        DB::transaction(function () use ($product, $id, $branchId, $variantId, $delta, $newPieces, $alertQty, $wholesale, $retail, $allocData, $multiplier) {
            // Update product master record
            $productUpdates = [
                'alert_quantity'    => $alertQty,
                'completion_status' => 'complete',
            ];
            if (!$variantId) {
                $productUpdates['wholesale_price'] = $wholesale;
                $productUpdates['price']           = $retail;
                $productUpdates['initial_stock']   = $newPieces;
            }
            $product->update($productUpdates);

            // Update variant-specific pricing if variant selected
            if ($variantId) {
                \App\Models\ProductVariant::where('id', $variantId)->where('product_id', $id)->update([
                    'wholesale_price' => $wholesale,
                    'retail_price'    => $retail,
                ]);
            }

            // Adjust branch stock by delta (preserves previous purchases and sales)
            $varTotals = [];
            foreach ($allocData as $al) {
                $vId = !empty($al['variant_id']) ? (int)$al['variant_id'] : ($variantId ?: null);
                if ($vId) {
                    $varTotals[$vId] = ($varTotals[$vId] ?? 0) + (((float)($al['quantity'] ?? 0)) * $multiplier);
                }
            }

            if (!empty($varTotals)) {
                foreach ($varTotals as $vId => $vQty) {
                    $netNonOpening = (float) (DB::table('stock_movements')
                        ->where('product_id', $id)
                        ->where('branch_id', $branchId)
                        ->where('variant_id', $vId)
                        ->whereNotIn('ref_type', ['OPENING', 'OPENING_ADJ', 'INITIAL_STOCK'])
                        ->selectRaw("SUM(CASE WHEN type = 'in' THEN qty ELSE -qty END) as net")
                        ->value('net') ?? 0);

                    $actualVarStock = max(0, $vQty + $netNonOpening);

                    $stk = Stock::where('product_id', $id)
                        ->where('branch_id', $branchId)
                        ->where('variant_id', $vId)
                        ->first();
                    if ($stk) {
                        $stk->qty = $actualVarStock;
                        $stk->save();
                    } else {
                        Stock::create([
                            'product_id'   => $id,
                            'branch_id'    => $branchId,
                            'variant_id'   => $vId,
                            'qty'          => $actualVarStock,
                            'reserved_qty' => 0,
                        ]);
                    }
                }
            } else {
                // Setting opening stock at product level (without variant breakdown):
                // Clean up any stale variant-specific stock rows to avoid duplicates
                Stock::where('product_id', $id)->where('branch_id', $branchId)->whereNotNull('variant_id')->delete();
                DB::table('warehouse_stocks')->where('product_id', $id)->where('branch_id', $branchId)->whereNotNull('variant_id')->delete();
                StockMovement::where('product_id', $id)->where('branch_id', $branchId)->whereNotNull('variant_id')->whereIn('ref_type', ['OPENING', 'OPENING_ADJ', 'INITIAL_STOCK'])->delete();

                $netNonOpening = (float) (DB::table('stock_movements')
                    ->where('product_id', $id)
                    ->where('branch_id', $branchId)
                    ->whereNotIn('ref_type', ['OPENING', 'OPENING_ADJ', 'INITIAL_STOCK'])
                    ->selectRaw("SUM(CASE WHEN type = 'in' THEN qty ELSE -qty END) as net")
                    ->value('net') ?? 0);

                $actualStock = max(0, $newPieces + $netNonOpening);

                $stockRecord = Stock::where('product_id', $id)->where('branch_id', $branchId)->whereNull('variant_id')->first();
                if ($stockRecord) {
                    $stockRecord->qty = $actualStock;
                    $stockRecord->save();
                } else {
                    Stock::create([
                        'product_id'   => $id,
                        'branch_id'    => $branchId,
                        'variant_id'   => null,
                        'qty'          => $actualStock,
                        'reserved_qty' => 0,
                    ]);
                }
            }

            // Update or create the primary OPENING stock movement (scoped to variant if variantId exists)
            $existingOpeningQuery = StockMovement::where('product_id', $id)
                ->where('branch_id', $branchId)
                ->where('ref_type', 'OPENING');
            if ($variantId) {
                $existingOpeningQuery->where('variant_id', $variantId);
            } elseif (!empty($varTotals)) {
                $existingOpeningQuery->where('variant_id', array_key_first($varTotals));
            } else {
                $existingOpeningQuery->whereNull('variant_id');
            }
            $existingOpening = $existingOpeningQuery->first();

            if ($existingOpening) {
                if ($newPieces > 0) {
                    $existingOpening->update([
                        'qty'        => $newPieces,
                        'type'       => 'in',
                        'note'       => 'Opening stock entry',
                        'updated_at' => now(),
                    ]);
                } else {
                    $existingOpening->delete();
                }
            } elseif ($newPieces > 0) {
                StockMovement::create([
                    'product_id' => $id,
                    'variant_id' => $variantId ?: (!empty($varTotals) ? array_key_first($varTotals) : null),
                    'branch_id'  => $branchId,
                    'type'       => 'in',
                    'qty'        => $newPieces,
                    'ref_type'   => 'OPENING',
                    'ref_id'     => null,
                    'note'       => 'Opening stock entry',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Remove any old OPENING_ADJ movements for this product and branch
            $adjQuery = StockMovement::where('product_id', $id)
                ->where('branch_id', $branchId)
                ->where('ref_type', 'OPENING_ADJ');
            if ($variantId) {
                $adjQuery->where('variant_id', $variantId);
            }
            $adjQuery->delete();

            // Adjust warehouse_stocks
            $allocPieces = [];
            foreach ($allocData as $alloc) {
                $allocPieces[] = [
                    'location_type' => $alloc['location_type'] ?? 'shop',
                    'warehouse_id'  => $alloc['warehouse_id'] ?? null,
                    'variant_id'    => !empty($alloc['variant_id']) ? (int)$alloc['variant_id'] : ($variantId ?: null),
                    'quantity'      => ((float) ($alloc['quantity'] ?? 0)) * $multiplier,
                ];
            }
            $this->applyWarehouseAllocation($id, $branchId, $retail, $allocPieces, $newPieces);

            // Save serial numbers if product is serial-tracked
            if ($product->isSerial() && !empty($allocData)) {
                $delSerialQuery = \App\Models\ProductSerial::where('product_id', $id)
                    ->where('branch_id', $branchId)
                    ->where('source_type', 'OPENING')
                    ->where('status', 'available');
                if ($variantId) {
                    $delSerialQuery->where('variant_id', $variantId);
                }
                $delSerialQuery->delete();

                foreach ($allocData as $alloc) {
                    $locType = $alloc['location_type'] ?? 'shop';
                    $wId     = ($locType === 'warehouse' && !empty($alloc['warehouse_id'])) ? (int)$alloc['warehouse_id'] : null;
                    $vId     = !empty($alloc['variant_id']) ? (int)$alloc['variant_id'] : ($variantId ?: null);
                    $serials = $alloc['serials'] ?? [];

                    if (is_array($serials)) {
                        foreach ($serials as $sn) {
                            $snClean = trim((string)$sn);
                            if ($snClean !== '') {
                                \App\Models\ProductSerial::create([
                                    'product_id'     => $id,
                                    'variant_id'     => $vId,
                                    'branch_id'      => $branchId,
                                    'warehouse_id'   => $wId,
                                    'serial_number'  => $snClean,
                                    'status'         => 'available',
                                    'source_type'    => 'OPENING',
                                    'purchase_price' => $wholesale > 0 ? $wholesale : null,
                                    'sale_price'     => $retail > 0 ? $retail : null,
                                ]);
                            }
                        }
                    }
                }
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Opening stock updated successfully!',
                'redirect_url' => route('opening.stocks.index')
            ]);
        }

        return redirect()->route('opening.stocks.edit', ['id' => $id, 'branch_id' => $branchId])
                         ->with('success', 'Opening stock updated successfully!');
    }

    /**
     * Calculate net non-opening movements (purchases, sales, returns, transfers, damaged)
     * specifically for a warehouse (or shop if null) and optional variant.
     */
    private function getWarehouseNonOpeningDelta(int $productId, int $branchId, ?int $warehouseId, ?int $variantId = null): float
    {
        $product = Product::find($productId);
        $isCartonProd = ($product && $product->isCartonProduct());
        $ppp = $isCartonProd ? (float)$product->piece_per_pack : 1;

        // 1. Purchases
        $purQ = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('purchase_items.product_id', $productId)
            ->where('purchases.branch_id', $branchId)
            ->whereNull('purchases.deleted_at')
            ->when($variantId !== null, fn($q) => $q->where('purchase_items.variant_id', $variantId));

        if ($warehouseId !== null) {
            $purQ->where(function ($q) use ($warehouseId) {
                $q->where('purchase_items.warehouse_id', $warehouseId)
                  ->orWhere(function ($sq) use ($warehouseId) {
                      $sq->whereNull('purchase_items.warehouse_id')->where('purchases.warehouse_id', $warehouseId);
                  });
            });
        } else {
            $purQ->whereNull('purchase_items.warehouse_id')->whereNull('purchases.warehouse_id');
        }

        $purchases = 0.0;
        foreach ($purQ->select('purchase_items.qty', 'purchase_items.packing_qty', 'purchase_items.item_per_piece')->get() as $pb) {
            $m = (float)($pb->item_per_piece > 1 ? $pb->item_per_piece : ($isCartonProd ? $ppp : 1));
            $effectivePbQty = (!empty($pb->packing_qty) && (float)$pb->packing_qty > 0) ? $pb->packing_qty : $pb->qty;
            $purchases += $isCartonProd ? Product::parseCartonQtyToPieces($effectivePbQty, $m, 'ctn') : ((float)$effectivePbQty * $m);
        }

        // 2. Sales Out
        $saleQ = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sale_items.product_id', $productId)
            ->where('sales.branch_id', $branchId)
            ->where('sales.status', '!=', 'cancelled')
            ->when($variantId !== null, fn($q) => $q->where('sale_items.variant_id', $variantId));

        if ($warehouseId !== null) {
            $saleQ->where('sale_items.warehouse_id', $warehouseId);
        } else {
            $saleQ->whereNull('sale_items.warehouse_id');
        }

        $sales = 0.0;
        foreach ($saleQ->select('sale_items.sales_qty', 'sale_items.sale_unit')->get() as $sb) {
            $isPcsSale = strtolower($sb->sale_unit ?? '') === 'pcs';
            $sales += Product::parseCartonQtyToPieces($sb->sales_qty, $ppp, $isPcsSale ? 'pcs' : 'ctn');
        }

        // 3. Sales Returns
        $saleReturns = 0.0;
        if (\Illuminate\Support\Facades\Schema::hasTable('sales_returns') && $product) {
            $srQ = DB::table('sales_returns')
                ->where('product_code', $product->item_code)
                ->whereExists(function ($sub) use ($branchId) {
                    $sub->select(DB::raw(1))
                        ->from('sales')
                        ->whereColumn('sales.id', 'sales_returns.sale_id')
                        ->where('sales.branch_id', $branchId);
                });
            foreach ($srQ->select('sales_returns.qty', 'sales_returns.sale_id')->get() as $srb) {
                $origSaleItem = DB::table('sale_items')
                    ->where('sale_id', $srb->sale_id)
                    ->where('product_id', $productId)
                    ->when($variantId !== null, fn($q) => $q->where('variant_id', $variantId))
                    ->first();
                $origWh = $origSaleItem ? $origSaleItem->warehouse_id : null;
                if ($origWh == $warehouseId) {
                    $isPcsReturn = $origSaleItem && strtolower($origSaleItem->sale_unit ?? '') === 'pcs';
                    $saleReturns += Product::parseCartonQtyToPieces($srb->qty, $ppp, $isPcsReturn ? 'pcs' : 'ctn');
                }
            }
        }

        // 4. Stock Transfers
        $transfersIn = 0.0;
        $transfersOut = 0.0;
        if ($warehouseId !== null && \Illuminate\Support\Facades\Schema::hasTable('stock_transfers')) {
            $transfersIn = (float) DB::table('stock_transfers')
                ->where('product_id', $productId)
                ->where('to_warehouse_id', $warehouseId)
                ->where('status', 'approved')
                ->sum('quantity');

            $transfersOut = (float) DB::table('stock_transfers')
                ->where('product_id', $productId)
                ->where('from_warehouse_id', $warehouseId)
                ->where('status', 'approved')
                ->sum('quantity');
        }

        // 5. Damaged Stocks
        $damaged = 0.0;
        if (\Illuminate\Support\Facades\Schema::hasTable('damaged_stocks')) {
            $dmgQ = DB::table('damaged_stocks')
                ->where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->when($variantId !== null, fn($q) => $q->where('variant_id', $variantId));
            if ($warehouseId !== null) {
                $dmgQ->where('warehouse_id', $warehouseId);
            } else {
                $dmgQ->whereNull('warehouse_id');
            }
            $damaged = (float) $dmgQ->sum('quantity');
        }

        return ($purchases + $saleReturns + $transfersIn) - ($sales + $transfersOut + $damaged);
    }

    /**
     * Helper: Apply warehouse allocation data
     */
    private function applyWarehouseAllocation(int $productId, int $branchId, float $price, array $allocData, float $totalQty): void
    {
        $now = now();

        // Group allocations by location and variant to aggregate duplicate entries cleanly
        $grouped = [];
        if (!empty($allocData)) {
            foreach ($allocData as $alloc) {
                $qty          = (float) ($alloc['quantity'] ?? 0);
                $locationType = $alloc['location_type'] ?? 'shop';
                $whId         = ($locationType === 'warehouse' && !empty($alloc['warehouse_id'])) ? (int) $alloc['warehouse_id'] : null;
                $vId          = !empty($alloc['variant_id']) ? (int) $alloc['variant_id'] : null;
                $key          = ($whId === null ? 'shop' : 'wh_' . $whId) . ($vId !== null ? '_var_' . $vId : '');

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'warehouse_id' => $whId,
                        'variant_id'   => $vId,
                        'quantity'     => 0,
                    ];
                }
                $grouped[$key]['quantity'] += $qty;
            }
        } else {
            $grouped['shop'] = [
                'warehouse_id' => null,
                'variant_id'   => null,
                'quantity'     => $totalQty,
            ];
        }

        $savedIds = [];

        foreach ($grouped as $group) {
            $whId = $group['warehouse_id'];
            $vId  = $group['variant_id'] ?? null;
            $targetQty = (float) $group['quantity'];

            // Calculate location-specific non-opening delta (sales, purchases, returns, transfers)
            $locationDelta = $this->getWarehouseNonOpeningDelta($productId, $branchId, $whId, $vId);

            // Live warehouse quantity = Target Allocated Opening + Non-Opening transactions for this specific location
            $liveQty = max(0, $targetQty + $locationDelta);

            $q = DB::table('warehouse_stocks')
                ->where('branch_id', $branchId)
                ->where('product_id', $productId);
            if ($whId === null) {
                $q->whereNull('warehouse_id');
            } else {
                $q->where('warehouse_id', $whId);
            }
            if ($vId === null) {
                $q->whereNull('variant_id');
            } else {
                $q->where('variant_id', $vId);
            }
            $existing = $q->first();
            if ($existing) {
                DB::table('warehouse_stocks')
                    ->where('id', $existing->id)
                    ->update([
                        'quantity'   => $liveQty,
                        'price'      => $price,
                        'updated_at' => $now,
                    ]);
                $savedIds[] = $existing->id;
            } else {
                $newId = DB::table('warehouse_stocks')->insertGetId([
                    'branch_id'    => $branchId,
                    'warehouse_id' => $whId,
                    'product_id'   => $productId,
                    'variant_id'   => $vId,
                    'quantity'     => $liveQty,
                    'price'        => $price,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
                $savedIds[] = $newId;
            }
        }

        // ══════════════════════════════════════════════════════════════
        // Ensure Total Synchronization between stocks and warehouse_stocks
        // ══════════════════════════════════════════════════════════════
        // warehouse_stocks is the detail table calculated from specific transactions.
        // Update stocks (summary table) so branch live stock equals the sum of warehouse live stocks.
        $hasVariantAllocations = DB::table('warehouse_stocks')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->whereIn('id', $savedIds)
            ->whereNotNull('variant_id')
            ->exists();

        if ($hasVariantAllocations) {
            $variantSums = DB::table('warehouse_stocks')
                ->where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->whereIn('id', $savedIds)
                ->groupBy('variant_id')
                ->select('variant_id', DB::raw('SUM(quantity) as total_qty'))
                ->get();

            foreach ($variantSums as $vs) {
                Stock::updateOrCreate(
                    ['product_id' => $productId, 'branch_id' => $branchId, 'variant_id' => $vs->variant_id],
                    ['qty' => max(0, (float)$vs->total_qty), 'reserved_qty' => 0]
                );
            }
        } else {
            $totalWhLive = (float) DB::table('warehouse_stocks')
                ->where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->whereIn('id', $savedIds)
                ->sum('quantity');

            Stock::updateOrCreate(
                ['product_id' => $productId, 'branch_id' => $branchId, 'variant_id' => null],
                ['qty' => max(0, $totalWhLive), 'reserved_qty' => 0]
            );
        }

        // Delete any warehouse_stocks for this product and branch that were removed by the user
        $delQuery = DB::table('warehouse_stocks')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->whereNotIn('id', $savedIds);
        if ($hasVariantAllocations) {
            $updatedVarIds = array_filter(array_column($grouped, 'variant_id'));
            if (!empty($updatedVarIds)) {
                $delQuery->whereIn('variant_id', $updatedVarIds);
            }
        }
        $delQuery->delete();
    }

    /**
     * Delete / Reset opening stock for a product in a branch
     * Reverses stock movements and warehouse allocations safely
     */
    public function destroyOpeningStock(Request $request, $id)
    {
        $product      = Product::findOrFail($id);
        $user         = Auth::user();
        $isSuperAdmin = $user ? $user->hasRole('super admin') : false;
        $userBranchId = $user ? (int) $user->branch_id : 1;

        $branchId = (int) $request->input('branch_id', $product->branch_id ?: $userBranchId);
        $variantId = $request->input('variant_id') ? (int) $request->input('variant_id') : null;

        // Ownership / permission check
        if (!$isSuperAdmin && $userBranchId !== $branchId) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'You do not have permission to delete stock for this branch.'], 403);
            }
            return back()->with('error', 'You do not have permission to delete stock for this branch.');
        }

        try {
            DB::transaction(function () use ($product, $id, $branchId, $variantId, $user) {
                // 1. Calculate opening quantity to reverse
                $openingMoveQuery = DB::table('stock_movements')
                    ->where('product_id', $id)
                    ->where('branch_id', $branchId)
                    ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ', 'INITIAL_STOCK'])
                    ->where('type', 'in');
                if ($variantId) {
                    $openingMoveQuery->where('variant_id', $variantId);
                }
                $openingMovementQty = (float) $openingMoveQuery->sum('qty');

                if ($openingMovementQty <= 0) {
                    $whSumQuery = DB::table('warehouse_stocks')
                        ->where('product_id', $id)
                        ->where('branch_id', $branchId);
                    if ($variantId) {
                        $whSumQuery->where('variant_id', $variantId);
                    }
                    $whSum = (float) $whSumQuery->sum('quantity');
                    $openingMovementQty = $whSum > 0 ? $whSum : (float) ($product->initial_stock ?? 0);
                }

                // 2. Decrement branch stock in stocks table
                $branchStockQuery = DB::table('stocks')
                    ->where('product_id', $id)
                    ->where('branch_id', $branchId);
                if ($variantId) {
                    $branchStockQuery->where('variant_id', $variantId);
                } else {
                    $branchStockQuery->whereNull('variant_id');
                }
                $branchStock = $branchStockQuery->first();

                if ($branchStock) {
                    $newStockQty = max(0, (float) $branchStock->qty - $openingMovementQty);
                    DB::table('stocks')
                        ->where('id', $branchStock->id)
                        ->update([
                            'qty'        => $newStockQty,
                            'updated_at' => now(),
                        ]);
                }

                // 3. Delete warehouse_stocks allocations for this branch (and variant if specified)
                $delWhQuery = DB::table('warehouse_stocks')
                    ->where('product_id', $id)
                    ->where('branch_id', $branchId);
                if ($variantId) {
                    $delWhQuery->where('variant_id', $variantId);
                }
                $delWhQuery->delete();

                // Delete product_serials if serial-tracked
                $delSerQuery = DB::table('product_serials')
                    ->where('product_id', $id)
                    ->where('branch_id', $branchId)
                    ->where('source_type', 'OPENING');
                if ($variantId) {
                    $delSerQuery->where('variant_id', $variantId);
                }
                $delSerQuery->delete();

                // 4. Record audit rollback movement
                if ($openingMovementQty > 0) {
                    DB::table('stock_movements')->insert([
                        'product_id'  => $id,
                        'variant_id'  => $variantId,
                        'branch_id'   => $branchId,
                        'type'        => 'out',
                        'qty'         => $openingMovementQty,
                        'ref_type'    => 'OPENING_DELETE',
                        'ref_id'      => null,
                        'note'        => 'Opening stock deleted by ' . ($user ? $user->name : 'System'),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                // Delete old OPENING records
                $delMoveQuery = DB::table('stock_movements')
                    ->where('product_id', $id)
                    ->where('branch_id', $branchId)
                    ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ']);
                if ($variantId) {
                    $delMoveQuery->where('variant_id', $variantId);
                }
                $delMoveQuery->delete();

                // 5. Update initial_stock on Product
                $newInitial = max(0, (float) ($product->initial_stock ?? 0) - $openingMovementQty);
                $product->update([
                    'initial_stock' => $newInitial,
                ]);
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Opening stock for "' . $product->item_name . '" has been deleted successfully.',
                ]);
            }

            return redirect()->route('opening.stocks.index')->with('success', 'Opening stock for "' . $product->item_name . '" deleted successfully.');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Delete failed: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete product only if it has NO sales, NO purchases, and NO opening stock/movement.
     */
    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $user         = Auth::user();
        $isSuperAdmin = $user ? $user->hasRole('super admin') : false;
        $userBranchId = $user ? (int) $user->branch_id : 1;

        // Permission / Branch ownership check
        if (!$isSuperAdmin && $product->branch_id && $userBranchId !== (int) $product->branch_id) {
            $msg = 'You do not have permission to delete a product belonging to another branch.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        // Helper to check table existence before querying
        $hasRecord = function ($table, $column = 'product_id', $extraCondition = null) use ($id) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                return false;
            }
            $query = DB::table($table)->where($column, $id);
            if ($extraCondition) {
                $extraCondition($query);
            }
            return $query->exists();
        };

        // 1. Check Sales
        $hasSales = $hasRecord('sale_items')
            || $hasRecord('product_booking_items')
            || $hasRecord('productbookings')
            || $hasRecord('sale_postings')
            || $hasRecord('customer_remaining');

        if ($hasSales) {
            $msg = 'Yeh product delete nahi ho sakta kyunke iski Sale ya Booking transaction history maujood hai.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // 2. Check Purchases
        $hasPurchases = $hasRecord('purchase_items')
            || $hasRecord('purchase_order_items')
            || $hasRecord('purchase_return_items')
            || $hasRecord('inward_gatepass_items')
            || $hasRecord('vendor_remaining');

        if ($hasPurchases) {
            $msg = 'Yeh product delete nahi ho sakta kyunke iski Purchase ya Inward Gate Pass transaction history maujood hai.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // 3. Check Opening Stock & Movements
        $hasInitialStock = (float) ($product->initial_stock ?? 0) > 0;
        $hasMovements    = $hasRecord('stock_movements');
        $hasBranchStock  = $hasRecord('stocks', 'product_id', fn($q) => $q->where('qty', '!=', 0));
        $hasWhStock      = $hasRecord('warehouse_stocks', 'product_id', fn($q) => $q->where('quantity', '!=', 0));

        if ($hasInitialStock || $hasMovements || $hasBranchStock || $hasWhStock) {
            $msg = 'Yeh product delete nahi ho sakta kyunke iski Opening Stock ya Stock Movements record maujood hain.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // 4. Check Stock Transfers & Complaints
        $hasOtherTrans = $hasRecord('stock_transfers')
            || $hasRecord('stock_request_items')
            || $hasRecord('stock_holds')
            || $hasRecord('damaged_stocks')
            || $hasRecord('damaged_stock_transfers')
            || $hasRecord('complaints');

        if ($hasOtherTrans) {
            $msg = 'Yeh product delete nahi ho sakta kyunke iski Stock Transfer ya Warranty/Complaint history maujood hai.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // 5. Check if used as a BOM part for an assembled product
        $isBomPart = $hasRecord('product_boms', 'part_id');
        if ($isBomPart) {
            $msg = 'Yeh product delete nahi ho sakta kyunke yeh kisi Assembled Product ke BOM me component/part ke tor par use ho raha hai.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // All checks passed! Proceed with safe cleanup in DB transaction
        try {
            DB::transaction(function () use ($product, $id) {
                // Delete auxiliary configs / zero-stock rows
                if (\Illuminate\Support\Facades\Schema::hasTable('branch_product_codes')) {
                    DB::table('branch_product_codes')->where('product_id', $id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('product_branch_shares')) {
                    DB::table('product_branch_shares')->where('product_id', $id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('product_discounts')) {
                    DB::table('product_discounts')->where('product_id', $id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('product_boms')) {
                    DB::table('product_boms')->where('product_id', $id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                    DB::table('notifications')->where('product_id', $id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('stocks')) {
                    DB::table('stocks')->where('product_id', $id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('warehouse_stocks')) {
                    DB::table('warehouse_stocks')->where('product_id', $id)->delete();
                }

                // Delete image file if exists
                if (!empty($product->image)) {
                    $imgPath = public_path('uploads/products/' . $product->image);
                    if (file_exists($imgPath) && is_file($imgPath)) {
                        @unlink($imgPath);
                    }
                }

                $product->forceDelete();
            });

            $successMsg = 'Product "' . $product->item_name . '" has been deleted successfully.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $successMsg]);
            }
            return redirect()->route('product')->with('success', $successMsg);

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}

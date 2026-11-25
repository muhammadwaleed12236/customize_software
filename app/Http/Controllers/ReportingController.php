<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountHead;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Product;
use App\Models\ReceiptsVoucher;
use App\Models\PaymentVoucher;
use App\Models\JournalVoucher;
use App\Models\ExpenseVoucher;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\SalesOfficer;
use App\Models\Vendor;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportingController extends Controller
{

    public function onhand(Request $request)
    {
        $user = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        $branchId = null;
        if ($isSuper) {
            $branches = Branch::orderBy('name')->get();
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $branchId = (int)$request->branch_id;
            }
        } else {
            $branches = $user->branch_id ? Branch::where('id', $user->branch_id)->get() : collect();
            $branchId = $user->branch_id;
        }

        // Fetch Warehouses based on branch
        if ($branchId) {
            $warehouses = Warehouse::whereHas('branches', function($q) use ($branchId) {
                $q->where('branches.id', $branchId);
            })->orderBy('warehouse_name')->get();
        } else {
            $warehouses = Warehouse::orderBy('warehouse_name')->get();
        }

        $warehouseId = null;
        if ($request->filled('warehouse_id') && $request->warehouse_id !== 'all') {
            $warehouseId = (int)$request->warehouse_id;
        }

        $categoryId = null;
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $categoryId = (int)$request->category_id;
        }

        $brandId = null;
        if ($request->filled('brand_id') && $request->brand_id !== 'all') {
            $brandId = (int)$request->brand_id;
        }

        $modelName = null;
        if ($request->filled('model') && $request->model !== 'all') {
            $modelName = $request->model;
        }

        $productIdRaw = $request->get('product_id');
        $productId = null;
        $variantId = null;
        if (!empty($productIdRaw) && $productIdRaw !== 'all') {
            if (strpos($productIdRaw, ':') !== false) {
                [$productId, $variantId] = explode(':', $productIdRaw);
                $productId = (int)$productId;
                $variantId = (int)$variantId;
            } else {
                $productId = (int)$productIdRaw;
            }
        }

        // Default to showing available stock only (> 0) unless specified otherwise
        $stockStatus = $request->get('stock_status', 'in_stock');

        // Master Lists for Filters
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);
        $models = Product::whereNotNull('model')->where('model', '!=', '')->distinct()->orderBy('model')->pluck('model');
        $allProducts = Product::with('variants')->orderBy('item_name')->get(['id', 'item_name', 'item_code', 'has_variants']);

        // Main Query - Supports Variant Products with Individual Variant Breakdown
        $query = Product::query()
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('product_variants as pv', function($join) {
                $join->on('pv.product_id', '=', 'products.id')
                     ->where('products.has_variants', '=', 1);
            })
            ->leftJoin('warehouse_stocks as ws', function($join) use ($branchId, $warehouseId) {
                $join->on('ws.product_id', '=', 'products.id')
                     ->where(function($w) {
                         $w->where('products.has_variants', '=', 0)
                           ->orWhereColumn('ws.variant_id', 'pv.id');
                     });
                if ($branchId) {
                    $join->where('ws.branch_id', '=', $branchId);
                }
                if ($warehouseId) {
                    $join->where('ws.warehouse_id', '=', $warehouseId);
                }
            });

        if (!$branchId && !$warehouseId) {
            $query->leftJoin('v_stock_onhand as soh', 'soh.product_id', '=', 'products.id');
            $onhandExpr = "
                CASE 
                    WHEN products.has_variants = 1 THEN COALESCE(SUM(ws.quantity), 0)
                    ELSE COALESCE(SUM(ws.quantity), soh.onhand_qty, 0)
                END
            ";
        } else {
            $onhandExpr = "COALESCE(SUM(ws.quantity), 0)";
        }

        $query->selectRaw("
            products.id,
            pv.id as variant_id,
            products.item_code,
            CASE 
                WHEN products.has_variants = 1 AND pv.color IS NOT NULL AND pv.color != '' 
                    THEN CONCAT(products.item_name, ' (', pv.color, ')')
                WHEN products.has_variants = 1 AND pv.variant_name IS NOT NULL AND pv.variant_name != '' 
                    THEN CONCAT(products.item_name, ' (', pv.variant_name, ')')
                ELSE products.item_name 
            END as item_name,
            products.model,
            COALESCE(brands.name, '') as brand_name,
            COALESCE(categories.name, '') as category_name,
            COALESCE(units.name, '') as unit_name,
            {$onhandExpr} as onhand_qty,
            products.is_part,
            products.is_assembled
        ")
        ->groupBy(
            'products.id',
            'pv.id',
            'products.item_code',
            'products.item_name',
            'pv.color',
            'pv.variant_name',
            'products.has_variants',
            'products.model',
            'brands.name',
            'categories.name',
            'units.name',
            'products.is_part',
            'products.is_assembled'
        );

        if (!$branchId && !$warehouseId) {
            $query->groupBy('soh.onhand_qty');
        }

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        if ($brandId) {
            $query->where('products.brand_id', $brandId);
        }

        if ($modelName) {
            $query->where('products.model', $modelName);
        }

        if ($productId) {
            $query->where('products.id', $productId);
            if ($variantId) {
                $query->where('pv.id', $variantId);
            }
        }

        // Apply Stock Status Filter
        if ($stockStatus === 'in_stock') {
            $query->havingRaw("{$onhandExpr} > 0");
        } elseif ($stockStatus === 'out_of_stock') {
            $query->havingRaw("{$onhandExpr} <= 0");
        }

        $rows = $query->orderBy('products.item_name')->get();

        if ($request->ajax()) {
            return response()->json([
                'rows' => $rows,
                'totalItems' => $rows->count(),
                'totalQty' => $rows->sum('onhand_qty'),
                'partsCount' => $rows->where('is_part', 1)->count(),
                'assembledCount' => $rows->where('is_assembled', 1)->count(),
            ]);
        }

        return view('admin_panel.reporting.onhand', compact(
            'rows', 'branches', 'warehouses', 'categories', 'brands', 'models', 'allProducts',
            'isSuper', 'branchId', 'warehouseId', 'categoryId', 'brandId', 'modelName', 'productId', 'stockStatus', 'productIdRaw', 'variantId'
        ));
    }
    public function customer_ledger_new(){
        $user = Auth::user();

        // Determine which branches user can view
        if ($user->hasRole('super admin')) {
            $branches = Branch::orderBy('name')->get();
        } else {
            $branches = Branch::where('id', $user->branch_id)->get();
        }

        // Get customers for non-admin users (super admin will load via AJAX)
        $customers = [];
        if (!$user->hasRole('super admin') && $user->branch_id) {
            $customers = Customer::where('branch_id', $user->branch_id)
                ->where('status', 'active')
                ->select('id', 'customer_name', 'customer_type', 'credit_limit', 'address', 'mobile', 'email_address', 'opening_balance')
                ->orderBy('customer_name')
                ->get();
        }

        $startDate = date('Y-m-01');
        $endDate   = date('Y-m-d');

        return view('admin_panel.reporting.customer_leger_new', compact('branches', 'customers', 'startDate', 'endDate'));
    }

    /* ═══════════════════════════════════════════════════════════════════════
     *  FETCH CUSTOMER LEDGER (NEW – International ERP Standard)
     *
     *  Builds a chronological ledger by DIRECTLY joining:
     *    1. Sales            → Debit  (SIN-xxxxx / invoice_no)
     *    2. Sale Returns     → Credit (return_note)
     *    3. Receipt Vouchers → Credit (BRV / cash payment)
     *    4. Payment Vouchers → Credit (discount / expense against customer)
     *  Each sale row shows ONE row per sale-item (Qty + Rate).
     *  Gate Pass & DC numbers are looked up from outward_gatepasses.
     *  Running balance is calculated in PHP to stay accurate.
     * ═══════════════════════════════════════════════════════════════════════ */
    /**
     * Calculate mathematically exact Opening Balance for a Customer before $startDate.
     * Opening = Initial + Prior Sales + Prior JV Debits - Prior Returns - Prior Receipts - Prior Payments - Prior JV Credits
     */
    public function getCustomerOpeningBalance(int $customerId, string $startDate): float
    {
        $customer = Customer::find($customerId);
        $initialOpening = (float)($customer->opening_balance ?? 0);

        // 1. Prior Sales (Debit)
        $priorSalesRaw = DB::table('sales')
            ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.customer_id', $customerId)
            ->where(DB::raw('DATE(sales.created_at)'), '<', $startDate)
            ->where(function ($q) {
                $q->whereNull('sales.status')->orWhere('sales.status', '!=', 'cancelled');
            })
            ->select(
                'sales.id',
                'sales.total_net',
                'sales.additional_discount',
                'sales.extra_charges',
                DB::raw('COALESCE(SUM(sale_items.amount), 0) as items_sum'),
                DB::raw('COUNT(sale_items.id) as items_count')
            )
            ->groupBy('sales.id', 'sales.total_net', 'sales.additional_discount', 'sales.extra_charges')
            ->get();

        $priorSales = 0;
        foreach ($priorSalesRaw as $ps) {
            $itemsSum = (float)$ps->items_sum;
            $addDisc  = (float)($ps->additional_discount ?? 0);
            $extraChg = (float)($ps->extra_charges ?? 0);
            if ($ps->items_count > 0 && $itemsSum > 0) {
                $priorSales += max(0, $itemsSum - $addDisc + $extraChg);
            } else {
                $priorSales += (float)($ps->total_net ?? 0);
            }
        }

        // 2. Prior Sale Returns (Credit)
        $priorReturns = DB::table('sales_returns')
            ->join('sales', 'sales.id', '=', 'sales_returns.sale_id')
            ->where('sales.customer_id', $customerId)
            ->where(DB::raw('DATE(sales_returns.created_at)'), '<', $startDate)
            ->sum('sales_returns.total_net');

        // 2b. Prior Sale Return Cash Refunds (Debit)
        $priorRefunds = (float) DB::table('account_ledger_entries')
            ->join('sales_returns', 'sales_returns.id', '=', 'account_ledger_entries.voucher_id')
            ->join('sales', 'sales.id', '=', 'sales_returns.sale_id')
            ->where('sales.customer_id', $customerId)
            ->where('account_ledger_entries.voucher_type', 'payment')
            ->where('account_ledger_entries.voucher_no', 'like', 'RET-%')
            ->where(DB::raw('DATE(account_ledger_entries.transaction_date)'), '<', $startDate)
            ->sum('account_ledger_entries.credit');

        // 3. Prior Receipts (Credit)
        $priorReceiptsQ = DB::table('receipts_vouchers')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->where(DB::raw('DATE(receipt_date)'), '<', $startDate);
        \App\Services\VoucherService::scopePartyQuery($priorReceiptsQ, $customerId, 'party_id');
        $priorReceiptsRaw = $priorReceiptsQ->get();

        $priorReceipts = 0;
        foreach ($priorReceiptsRaw as $rec) {
            $priorReceipts += \App\Services\VoucherService::getPartyAmount($rec, $customerId, 'customer');
        }

        // 4. Prior Payments (Credit - Customer discounts/rebates)
        $priorPaymentsQ = DB::table('payment_vouchers')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->where(DB::raw('DATE(receipt_date)'), '<', $startDate);
        \App\Services\VoucherService::scopePartyQuery($priorPaymentsQ, $customerId, 'party_id');
        $priorPaymentsRaw = $priorPaymentsQ->get();

        $priorPayments = 0;
        foreach ($priorPaymentsRaw as $pv) {
            $priorPayments += \App\Services\VoucherService::getPartyAmount($pv, $customerId, 'customer');
        }

        // 5. Prior Journal Vouchers (Debits & Credits)
        $priorJvDebits = DB::table('journal_vouchers')
            ->where('debit_party_type', 'customer')
            ->where('debit_party_id', $customerId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->where(DB::raw('DATE(voucher_date)'), '<', $startDate)
            ->sum('amount');

        $priorJvCredits = DB::table('journal_vouchers')
            ->where('credit_party_type', 'customer')
            ->where('credit_party_id', $customerId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->where(DB::raw('DATE(voucher_date)'), '<', $startDate)
            ->sum('amount');

        // 6. Prior Customer Purchases (Credit - contra purchases from customer reduce receivable)
        $priorCustPurchases = 0;
        if (\Illuminate\Support\Facades\Schema::hasColumn('purchases', 'customer_id')) {
            $priorCustPurchases = (float) DB::table('purchases')
                ->where('customer_id', $customerId)
                ->whereNull('deleted_at')
                ->where(DB::raw('DATE(created_at)'), '<', $startDate)
                ->sum(DB::raw('COALESCE(net_amount, 0)'));
        }

        return (float)$initialOpening 
            + (float)$priorSales 
            + (float)$priorRefunds
            + (float)$priorJvDebits 
            - (float)$priorReturns 
            - (float)$priorReceipts 
            - (float)$priorPayments 
            - (float)$priorJvCredits
            - (float)$priorCustPurchases;
    }

    /**
     * Calculate mathematically exact Opening Balance for a Vendor before $startDate.
     * Opening = Initial + Prior Purchases + Prior Receipts + Prior JV Credits - Prior Returns - Prior Payments - Prior JV Debits
     */
    public function getVendorOpeningBalance(int $vendorId, ?int $branchId, string $startDate): float
    {
        $vendor = \App\Models\Vendor::find($vendorId);
        $initialOpening = (float)($vendor->opening_balance ?? 0);

        // 1. Prior Purchases (Credit)
        $purchasesQ = DB::table('purchases')
            ->where('vendor_id', $vendorId)
            ->where(DB::raw('DATE(created_at)'), '<', $startDate)
            ->whereNull('deleted_at');

        if ($branchId) {
            $purchasesQ->where('branch_id', $branchId);
        }

        $priorPurchases = $purchasesQ->sum(DB::raw('COALESCE(net_amount, 0)'));

        // 2. Prior Purchase Returns (Debit)
        $priorReturns = DB::table('purchase_returns')
            ->where('vendor_id', $vendorId)
            ->where(DB::raw('DATE(created_at)'), '<', $startDate)
            ->sum('net_amount');

        // 3. Prior Payments (Debit)
        $priorPaymentsQ = DB::table('payment_vouchers')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->where(DB::raw('DATE(receipt_date)'), '<', $startDate);
        \App\Services\VoucherService::scopePartyQuery($priorPaymentsQ, $vendorId, 'party_id');
        $priorPaymentsRaw = $priorPaymentsQ->get();

        $priorPayments = 0;
        foreach ($priorPaymentsRaw as $pv) {
            $priorPayments += \App\Services\VoucherService::getPartyAmount($pv, $vendorId, 'vendor');
        }

        // 4. Prior Receipts (Credit - refunds from vendor)
        $priorReceiptsQ = DB::table('receipts_vouchers')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->where(DB::raw('DATE(receipt_date)'), '<', $startDate);
        \App\Services\VoucherService::scopePartyQuery($priorReceiptsQ, $vendorId, 'party_id');
        $priorReceiptsRaw = $priorReceiptsQ->get();

        $priorReceipts = 0;
        foreach ($priorReceiptsRaw as $rec) {
            $priorReceipts += \App\Services\VoucherService::getPartyAmount($rec, $vendorId, 'vendor');
        }

        // 5. Prior Journal Vouchers (Debits & Credits)
        $priorJvCredits = DB::table('journal_vouchers')
            ->where('credit_party_type', 'vendor')
            ->where('credit_party_id', $vendorId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->where(DB::raw('DATE(voucher_date)'), '<', $startDate)
            ->sum('amount');

        $priorJvDebits = DB::table('journal_vouchers')
            ->where('debit_party_type', 'vendor')
            ->where('debit_party_id', $vendorId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->where(DB::raw('DATE(voucher_date)'), '<', $startDate)
            ->sum('amount');

        return (float)$initialOpening 
            + (float)$priorPurchases 
            + (float)$priorReceipts 
            + (float)$priorJvCredits 
            - (float)$priorReturns 
            - (float)$priorPayments 
            - (float)$priorJvDebits;
    }

    public function fetch_customer_ledger_new(Request $request)
    {
        $user       = auth()->user();
        $customerId = (int) $request->customer_id;
        $start      = $request->start_date;          // Y-m-d
        $end        = $request->end_date;             // Y-m-d

        if (!$customerId || !$start || !$end) {
            return response()->json(['error' => 'Missing required parameters'], 422);
        }

        // ── Authorization ──────────────────────────────────────────────────
        $customer     = Customer::findOrFail($customerId);
        $custBranchId = $customer->branch_id ?? null;
        $allowed      = false;

        if ($user && $user->hasRole('super admin')) {
            $allowed = true;
        } elseif ($user && ($user->branch_id ?? null) && $custBranchId && $user->branch_id == $custBranchId) {
            $allowed = true;
        } elseif ($user && $user->can('report.customer.ledger.branch.view')) {
            $allowed = true;
        }

        if (!$allowed) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // ── Mathematically Exact Opening Balance ─────────────────────────
        $openingBalance = $this->getCustomerOpeningBalance($customerId, $start);

        // ── Gate Pass lookup map: invoice_no → {dc_no, gatepass_number} ───
        $gpMap = [];
        $gpRows = DB::table('outward_gatepasses')
            ->whereNotNull('invoice_no')
            ->select('invoice_no', 'dc_no', 'gatepass_number', 'created_at')
            ->get();
        foreach ($gpRows as $gp) {
            // Store the LATEST gatepass per invoice (there can be multiple DCs)
            $gpMap[$gp->invoice_no][] = [
                'dc_no'            => $gp->dc_no ?? '-',
                'gatepass_number'  => $gp->gatepass_number ?? '-',
            ];
        }

        // ── Customer Remaining Stock items lookup ────────────────────────
        $remainingItems = DB::table('customer_remaining')
            ->where('customer_id', $customerId)
            ->where('remaining_qty', '>', 0)
            ->select('id', 'sale_id', 'product_id', 'product_name', 'item_code', 'unit', 'remaining_qty', 'status')
            ->get();

        $totalRemainingQty = floatval($remainingItems->sum('remaining_qty'));

        $saleRemainingMap = [];
        foreach ($remainingItems as $rem) {
            $unitStr = $rem->unit;
            if ($unitStr && (str_starts_with($unitStr, '{') || str_starts_with($unitStr, '['))) {
                $uObj = json_decode($unitStr, true);
                $unitStr = $uObj['name'] ?? 'pcs';
            }
            $saleRemainingMap[$rem->sale_id][$rem->product_id] = [
                'remaining_qty' => floatval($rem->remaining_qty),
                'unit'          => $unitStr ?: 'pcs',
            ];
        }

        // ══════════════════════════════════════════════════════════════════
        // PART 1 – SALES  (one entry per sale-item for Qty/Rate breakdown)
        // ══════════════════════════════════════════════════════════════════
        $salesRaw = DB::table('sales')
            ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products',   'products.id',        '=', 'sale_items.product_id')
            ->where('sales.customer_id', $customerId)
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$start, $end])
            ->select(
                'sales.id          as sale_id',
                'sales.invoice_no  as invoice_no',
                'sales.manual_invoice as manual_invoice',
                DB::raw('COALESCE(sales.manual_invoice, "-") as bill_no'),
                DB::raw('COALESCE(sales.total_net, sales.sub_total1 - sales.discount_amount) as total_net'),
                'sales.additional_discount as additional_discount',
                'sales.extra_charges       as extra_charges',
                'sales.created_at  as txn_date',
                'products.item_name as item_name',
                'products.item_name_urdu as item_name_urdu',
                'products.price    as retail_price',
                'products.wholesale_price as n_price',
                'sale_items.product_id as product_id',
                'sale_items.sales_qty  as qty',
                'sale_items.discount_amount as item_discount',
                DB::raw('COALESCE(sale_items.watt, 0) as watt'),
                DB::raw('CASE 
                    WHEN sale_items.retail_price > 0 THEN sale_items.retail_price 
                    WHEN sale_items.sales_price > 0 THEN sale_items.sales_price 
                    WHEN sale_items.sales_qty > 0 AND (sale_items.amount + sale_items.discount_amount) > 0 THEN ROUND((sale_items.amount + sale_items.discount_amount) / sale_items.sales_qty, 2)
                    WHEN products.price > 0 THEN products.price
                    ELSE 0 END as rate'),
                'sale_items.amount  as line_amount'
            )
            ->orderBy('sales.created_at', 'asc')
            ->orderBy('sales.id', 'asc')
            ->orderBy('sale_items.id', 'asc')
            ->get();

        // Build avg purchase price map per product (total_cost / total_qty)
        $avgPriceMap = [];
        $avgPriceRaw = DB::table('purchase_items')
            ->select(
                'product_id',
                DB::raw('COALESCE(SUM(qty),0) as total_qty'),
                DB::raw('COALESCE(SUM(line_total),0) as total_cost')
            )
            ->groupBy('product_id')
            ->get();
        foreach ($avgPriceRaw as $ap) {
            $avgPriceMap[$ap->product_id] = $ap->total_qty > 0
                ? floatval($ap->total_cost) / floatval($ap->total_qty)
                : 0;
        }

        // Group sale items under their parent sale
        // For running balance: debit hits ONCE per sale, accurately calculated from items sum
        $salesGrouped = [];
        foreach ($salesRaw as $row) {
            if (!isset($salesGrouped[$row->sale_id])) {
                $salesGrouped[$row->sale_id] = [
                    'header' => [
                        'invoice_no'          => $row->invoice_no,
                        'manual_invoice'      => $row->manual_invoice ?? '-',
                        'total_net'           => floatval($row->total_net ?? 0),
                        'additional_discount' => floatval($row->additional_discount ?? 0),
                        'extra_charges'       => floatval($row->extra_charges ?? 0),
                        'txn_date'            => $row->txn_date,
                    ],
                    'items'     => [],
                    'items_sum' => 0,
                ];
            }
            $qty       = floatval($row->qty ?? 0);
            $rate      = floatval($row->rate ?? 0);
            if ($rate == 0 && floatval($row->retail_price ?? 0) > 0) {
                $rate = floatval($row->retail_price);
            }
            $lineAmt   = floatval($row->line_amount ?? 0);
            if ($lineAmt == 0 && $qty > 0 && $rate > 0) {
                $lineAmt = $qty * $rate;
            }
            if ($rate == 0 && $qty > 0 && $lineAmt > 0) {
                $rate = round($lineAmt / $qty, 2);
            }

            $salesGrouped[$row->sale_id]['items_sum'] += $lineAmt;

            $avgPrice  = $avgPriceMap[$row->product_id] ?? 0;
            $nPrice    = floatval($row->n_price ?? 0);
            $remQty    = $saleRemainingMap[$row->sale_id][$row->product_id]['remaining_qty'] ?? 0;

            $salesGrouped[$row->sale_id]['items'][] = [
                'item_name'     => $row->item_name,
                'item_name_urdu'=> $row->item_name_urdu ?? null,
                'qty'           => $qty,
                'remaining_qty' => floatval($remQty),
                'rate'          => $rate,
                'watt'          => floatval($row->watt ?? 0),
                'item_discount' => floatval($row->item_discount ?? 0),
                'line_amount'   => $lineAmt,
                'retail_price'  => floatval($row->retail_price ?? 0),
                'policy_price'  => $rate,
                'avg_price'     => $avgPrice,
                'avg_s_value'   => $avgPrice * $qty,
                'n_price'       => $nPrice,
                'stock_value'   => floatval($row->retail_price ?? 0) * $qty,
            ];
        }

        // ══════════════════════════════════════════════════════════════════
        // PART 2 – SALE RETURNS  (Credit entries)
        // ══════════════════════════════════════════════════════════════════
        $returnsRaw = DB::table('sales_returns')
            ->join('sales', 'sales.id', '=', 'sales_returns.sale_id')
            ->where('sales.customer_id', $customerId)
            ->whereBetween(DB::raw('DATE(sales_returns.created_at)'), [$start, $end])
            ->select(
                'sales_returns.id         as id',
                'sales.id                 as sale_id',
                'sales.invoice_no         as invoice_no',
                'sales_returns.return_note as return_note',
                'sales_returns.product    as item_name',
                'sales_returns.qty        as qty',
                'sales_returns.per_price  as rate',
                'sales_returns.total_net  as credit_amount',
                'sales_returns.created_at as txn_date'
            )
            ->orderBy('sales_returns.created_at', 'asc')
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // PART 2b – SALE RETURN CASH REFUNDS (Debit entries)
        // ══════════════════════════════════════════════════════════════════
        $refundsRaw = DB::table('account_ledger_entries')
            ->join('sales_returns', 'sales_returns.id', '=', 'account_ledger_entries.voucher_id')
            ->join('sales', 'sales.id', '=', 'sales_returns.sale_id')
            ->leftJoin('accounts', 'accounts.id', '=', 'account_ledger_entries.account_id')
            ->where('sales.customer_id', $customerId)
            ->where('account_ledger_entries.voucher_type', 'payment')
            ->where('account_ledger_entries.voucher_no', 'like', 'RET-%')
            ->whereBetween(DB::raw('DATE(account_ledger_entries.transaction_date)'), [$start, $end])
            ->select(
                'account_ledger_entries.id as entry_id',
                'account_ledger_entries.entry_no as entry_no',
                'account_ledger_entries.voucher_no as voucher_no',
                'account_ledger_entries.credit as debit_amount',
                'account_ledger_entries.transaction_date as txn_date',
                'accounts.title as account_title',
                'sales_returns.id as return_id',
                'sales.invoice_no as sale_invoice_no'
            )
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // PART 3 – RECEIPT VOUCHERS  (Cash + Bank payments from customer)
        // ══════════════════════════════════════════════════════════════════
        $receiptsQ = DB::table('receipts_vouchers')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->whereBetween(DB::raw('DATE(receipts_vouchers.receipt_date)'), [$start, $end])
            ->orderBy('receipts_vouchers.receipt_date', 'asc');
        \App\Services\VoucherService::scopePartyQuery($receiptsQ, $customerId, 'party_id');
        $receiptsRaw = $receiptsQ->get();

        // ══════════════════════════════════════════════════════════════════
        // PART 4 – PAYMENT VOUCHERS  (Discounts / Expenses against customer)
        // ══════════════════════════════════════════════════════════════════
        $paymentVouchersQ = DB::table('payment_vouchers')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->whereBetween(DB::raw('DATE(payment_vouchers.receipt_date)'), [$start, $end])
            ->orderBy('payment_vouchers.receipt_date', 'asc');
        \App\Services\VoucherService::scopePartyQuery($paymentVouchersQ, $customerId, 'party_id');
        $paymentVouchersRaw = $paymentVouchersQ->get();

        // ══════════════════════════════════════════════════════════════════
        // PART 5 – JOURNAL VOUCHERS  (Debits & Credits for Customer)
        // ══════════════════════════════════════════════════════════════════
        $jvsDebitRaw = DB::table('journal_vouchers')
            ->where('debit_party_type', 'customer')
            ->where('debit_party_id', $customerId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->whereBetween(DB::raw('DATE(voucher_date)'), [$start, $end])
            ->get();

        $jvsCreditRaw = DB::table('journal_vouchers')
            ->where('credit_party_type', 'customer')
            ->where('credit_party_id', $customerId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->whereBetween(DB::raw('DATE(voucher_date)'), [$start, $end])
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // PART 6 – PURCHASES FROM CUSTOMER (Contra / Buyback - Credit)
        // ══════════════════════════════════════════════════════════════════
        $custPurchasesRaw = collect();
        if (\Illuminate\Support\Facades\Schema::hasColumn('purchases', 'customer_id')) {
            $custPurchasesRaw = DB::table('purchases')
                ->where('customer_id', $customerId)
                ->whereNull('deleted_at')
                ->whereBetween(DB::raw('DATE(created_at)'), [$start, $end])
                ->select('id', 'invoice_no', 'net_amount', 'created_at', 'note')
                ->get();
        }

        // ══════════════════════════════════════════════════════════════════
        // BUILD UNIFIED EVENT LIST  (sorted by date → then type priority)
        // ══════════════════════════════════════════════════════════════════
        $events = [];

        // Add Sales
        foreach ($salesGrouped as $saleId => $saleData) {
            $header   = $saleData['header'];
            $items    = $saleData['items'];
            $itemsSum = floatval($saleData['items_sum'] ?? 0);
            $addDisc  = floatval($header['additional_discount'] ?? 0);
            $extraChg = floatval($header['extra_charges'] ?? 0);

            $effectiveDebit = (count($items) > 0 && $itemsSum > 0)
                ? max(0, $itemsSum - $addDisc + $extraChg)
                : floatval($header['total_net'] ?? 0);

            $gp      = $gpMap[$header['invoice_no']] ?? [];
            $dcNo    = !empty($gp) ? implode(' / ', array_unique(array_column($gp, 'dc_no'))) : '-';
            $gpNo    = !empty($gp) ? implode(' / ', array_unique(array_column($gp, 'gatepass_number'))) : '-';

            $events[] = [
                'sort_date'   => $header['txn_date'],
                'type'        => 'sale',
                'priority'    => 1,
                'data'        => [
                    'sale_id'     => $saleId,
                    'invoice_no'  => $header['invoice_no'],
                    'bill_no'     => $header['manual_invoice'] ?? '-',
                    'dc_no'       => $dcNo,
                    'gp_no'       => $gpNo,
                    'txn_date'    => $header['txn_date'],
                    'debit'       => $effectiveDebit,
                    'add_disc'    => $addDisc,
                    'extra_chg'   => $extraChg,
                    'items'       => $items,
                ],
            ];
        }

        // Add Sale Returns
        foreach ($returnsRaw as $ret) {
            $events[] = [
                'sort_date' => $ret->txn_date,
                'type'      => 'return',
                'priority'  => 2,
                'data'      => [
                    'return_id'   => $ret->id,
                    'sale_id'     => $ret->sale_id ?? null,
                    'invoice_no'  => $ret->invoice_no ?? '-',
                    'return_note' => $ret->return_note ?? 'RETURN',
                    'txn_date'    => $ret->txn_date,
                    'credit'      => floatval($ret->credit_amount ?? 0),
                    'item_name'   => $ret->item_name ?? '-',
                    'qty'         => floatval($ret->qty ?? 0),
                    'rate'        => floatval($ret->rate ?? 0),
                ],
            ];
        }

        // Add Sale Return Cash Refunds (Debit)
        foreach ($refundsRaw as $rf) {
            $isBank = false;
            if ($rf->account_title && (
                str_contains(strtolower($rf->account_title), 'bank') ||
                in_array(strtoupper($rf->account_title), ['HBL', 'MCB', 'UBL', 'MEEZAN', 'ALLIED', 'ASKARI'])
            )) {
                $isBank = true;
            }

            $events[] = [
                'sort_date' => $rf->txn_date,
                'type'      => 'return_refund',
                'priority'  => 2.5,
                'data'      => [
                    'entry_id'     => $rf->entry_id,
                    'vno'          => $rf->entry_no ?: ('BP-' . $rf->entry_id),
                    'reference_no' => $rf->voucher_no,
                    'account'      => $rf->account_title ?? 'Cash/Bank',
                    'description'  => 'RETURN CASH REFUND (' . ($rf->account_title ?? 'Cash/Bank') . ')',
                    'txn_date'     => $rf->txn_date,
                    'debit'        => floatval($rf->debit_amount ?? 0),
                    'is_bank'      => $isBank,
                ],
            ];
        }

        // Add Receipt Vouchers
        foreach ($receiptsRaw as $rec) {
            $creditAmt = \App\Services\VoucherService::getPartyAmount($rec, $customerId, 'customer');
            if ($creditAmt <= 0) {
                continue;
            }

            $rowAccountIds = \App\Services\VoucherService::safeDecodeArray($rec->row_account_id);
            $narrationIds  = \App\Services\VoucherService::safeDecodeArray($rec->narration_id);

            $accountTitles = [];
            $isBank = false;
            foreach ($rowAccountIds as $accId) {
                if ($accId && is_numeric($accId)) {
                    $acc = DB::table('accounts')->leftJoin('account_heads', 'account_heads.id', '=', 'accounts.head_id')->where('accounts.id', $accId)->select('accounts.title', 'account_heads.name as head_name')->first();
                    if ($acc) {
                        $accountTitles[] = $acc->title;
                        if ((isset($acc->head_name) && str_contains(strtolower($acc->head_name), 'bank')) 
                            || (isset($acc->title) && str_contains(strtolower($acc->title), 'bank'))
                            || (isset($acc->title) && in_array(strtoupper($acc->title), ['HBL', 'MCB', 'UBL', 'MEEZAN', 'ALLIED', 'ASKARI']))) {
                            $isBank = true;
                        }
                    }
                }
            }
            $accountStr = !empty($accountTitles) ? implode(', ', $accountTitles) : ($isBank ? 'Bank A/c' : 'Cash');

            $narrTexts = [];
            foreach ($narrationIds as $narrId) {
                if ($narrId && is_numeric($narrId)) {
                    $narr = DB::table('narrations')->where('id', $narrId)->value('narration');
                    if ($narr) $narrTexts[] = $narr;
                }
            }
            $narrText = !empty($narrTexts) ? implode(', ', $narrTexts) : null;

            $vno    = $rec->rvid ?? ('BRV-' . $rec->id);
            $desc   = $narrText ?? ($rec->remarks ?: ($isBank ? 'ONLINE / BANK RECEIVED' : 'CASH RECEIVED'));
            $events[] = [
                'sort_date' => $rec->receipt_date,
                'type'      => 'receipt',
                'priority'  => 3,
                'data'      => [
                    'voucher_id'   => $rec->id,
                    'voucher_type' => 'receipt',
                    'vno'          => $vno,
                    'reference_no' => $rec->reference_no ?? '-',
                    'account'      => $accountStr,
                    'description'  => strtoupper($desc),
                    'txn_date'     => $rec->receipt_date,
                    'credit'       => $creditAmt,
                    'is_bank'      => $isBank,
                ],
            ];
        }

        // Add Payment Vouchers (discounts, tour expense, etc.)
        foreach ($paymentVouchersRaw as $pv) {
            $creditAmt = \App\Services\VoucherService::getPartyAmount($pv, $customerId, 'customer');
            if ($creditAmt <= 0) {
                continue;
            }

            $rowAccountIds = \App\Services\VoucherService::safeDecodeArray($pv->row_account_id);
            $narrationIds  = \App\Services\VoucherService::safeDecodeArray($pv->narration_id);

            $accountTitles = [];
            $isBank = false;
            foreach ($rowAccountIds as $accId) {
                if ($accId && is_numeric($accId)) {
                    $acc = DB::table('accounts')->leftJoin('account_heads', 'account_heads.id', '=', 'accounts.head_id')->where('accounts.id', $accId)->select('accounts.title', 'account_heads.name as head_name')->first();
                    if ($acc) {
                        $accountTitles[] = $acc->title;
                        if ((isset($acc->head_name) && str_contains(strtolower($acc->head_name), 'bank')) 
                            || (isset($acc->title) && str_contains(strtolower($acc->title), 'bank'))
                            || (isset($acc->title) && in_array(strtoupper($acc->title), ['HBL', 'MCB', 'UBL', 'MEEZAN', 'ALLIED', 'ASKARI']))) {
                            $isBank = true;
                        }
                    }
                }
            }
            $accountStr = !empty($accountTitles) ? implode(', ', $accountTitles) : ($isBank ? 'Bank A/c' : 'Cash');

            $narrTexts = [];
            foreach ($narrationIds as $narrId) {
                if ($narrId && is_numeric($narrId)) {
                    $narr = DB::table('narrations')->where('id', $narrId)->value('narration');
                    if ($narr) $narrTexts[] = $narr;
                }
            }
            $narrText = !empty($narrTexts) ? implode(', ', $narrTexts) : null;

            $vno  = $pv->pvid ?? ('PV-' . $pv->id);
            $desc = $narrText ?? ($pv->remarks ?: 'PAYMENT VOUCHER');
            $events[] = [
                'sort_date' => $pv->receipt_date,
                'type'      => 'payment_voucher',
                'priority'  => 4,
                'data'      => [
                    'voucher_id'   => $pv->id,
                    'voucher_type' => 'payment_voucher',
                    'vno'          => $vno,
                    'reference_no' => $pv->reference_no ?? '-',
                    'account'      => $accountStr,
                    'description'  => strtoupper($desc),
                    'txn_date'     => $pv->receipt_date,
                    'credit'       => $creditAmt,
                    'is_bank'      => $isBank,
                ],
            ];
        }

        // Add Journal Vouchers (Debits & Credits)
        foreach ($jvsDebitRaw as $jv) {
            $events[] = [
                'sort_date' => $jv->voucher_date,
                'type'      => 'journal_debit',
                'priority'  => 5,
                'data'      => [
                    'voucher_id'   => $jv->id,
                    'voucher_type' => 'journal_voucher',
                    'vno'          => $jv->jvid,
                    'reference_no' => $jv->reference_no ?? '-',
                    'account'      => 'JV Adjustment',
                    'description'  => 'JOURNAL VOUCHER: ' . ($jv->remarks ?: 'ADJUSTMENT DEBIT'),
                    'txn_date'     => $jv->voucher_date,
                    'debit'        => floatval($jv->amount ?? 0),
                    'credit'       => 0,
                ],
            ];
        }

        foreach ($jvsCreditRaw as $jv) {
            $events[] = [
                'sort_date' => $jv->voucher_date,
                'type'      => 'journal_credit',
                'priority'  => 6,
                'data'      => [
                    'voucher_id'   => $jv->id,
                    'voucher_type' => 'journal_voucher',
                    'vno'          => $jv->jvid,
                    'reference_no' => $jv->reference_no ?? '-',
                    'account'      => 'JV Adjustment',
                    'description'  => 'JOURNAL VOUCHER: ' . ($jv->remarks ?: 'ADJUSTMENT CREDIT'),
                    'txn_date'     => $jv->voucher_date,
                    'debit'        => 0,
                    'credit'       => floatval($jv->amount ?? 0),
                ],
            ];
        }

        // Add Purchases from Customer (Contra Credit)
        foreach ($custPurchasesRaw as $cp) {
            $events[] = [
                'sort_date' => $cp->created_at,
                'type'      => 'contra_purchase',
                'priority'  => 3,
                'data'      => [
                    'voucher_id'   => $cp->id,
                    'voucher_type' => 'contra_purchase',
                    'vno'          => $cp->invoice_no,
                    'reference_no' => '-',
                    'description'  => $cp->note ?: 'PURCHASE FROM CUSTOMER (CONTRA)',
                    'txn_date'     => $cp->created_at,
                    'credit'       => floatval($cp->net_amount ?? 0),
                ],
            ];
        }

        // Sort all events in strict chronological order
        usort($events, function ($a, $b) {
            $dateA = substr((string)$a['sort_date'], 0, 10);
            $dateB = substr((string)$b['sort_date'], 0, 10);
            $dateCompare = strcmp($dateA, $dateB);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            // Same date: sort by logical business priority (Sale -> Return -> Refund -> Receipt -> Payment -> JV)
            $pA = (float)($a['priority'] ?? 99);
            $pB = (float)($b['priority'] ?? 99);
            if ($pA != $pB) {
                return $pA < $pB ? -1 : 1;
            }

            // Same priority: compare full timestamp if available, else tiebreak
            $timeCompare = strcmp((string)$a['sort_date'], (string)$b['sort_date']);
            if ($timeCompare !== 0) {
                return $timeCompare;
            }

            return ($a['data']['sale_id'] ?? ($a['data']['voucher_id'] ?? 0)) - ($b['data']['sale_id'] ?? ($b['data']['voucher_id'] ?? 0));
        });

        // ══════════════════════════════════════════════════════════════════
        // BUILD FINAL TRANSACTION ROWS WITH RUNNING BALANCE
        // ══════════════════════════════════════════════════════════════════
        $transactions   = [];
        $runningBalance = $openingBalance;
        $totalDebit     = 0;
        $totalCredit    = 0;
        $brCounter      = 0;
        $crCounter      = 0;
        $bpCounter      = 0;
        $cpCounter      = 0;

        foreach ($events as $event) {
            $type = $event['type'];
            $d    = $event['data'];

            if ($type === 'sale') {
                // ── SALE: one parent row (no qty/rate), then item sub-rows ──
                $debit          = $d['debit'];
                $runningBalance += $debit;
                $totalDebit     += $debit;

                // Parent summary row
                $transactions[] = [
                    'row_type'    => 'sale_header',
                    'sale_id'     => $d['sale_id'] ?? null,
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $d['invoice_no'],
                    'bill'        => $d['bill_no'],
                    'dc_no'       => $d['dc_no'],
                    'gp_no'       => $d['gp_no'],
                    'description' => 'SALE',
                    'item_name'   => null,
                    'qty'         => null,
                    'rate'        => null,
                    'debit'       => $debit,
                    'credit'      => 0,
                    'balance'     => $runningBalance,
                ];

                // Item detail sub-rows (qty + rate + stock pricing, no balance change)
                $invoiceTotalQty = 0;
                $invoiceTotalStk = 0;
                foreach ($d['items'] as $item) {
                    $invoiceTotalQty += $item['qty'];
                    $invoiceTotalStk += $item['stock_value'] ?? 0;
                    $transactions[] = [
                        'row_type'     => 'sale_item',
                        'sale_id'      => $d['sale_id'] ?? null,
                        'date'         => null,
                        'vno'          => null,
                        'bill'         => null,
                        'dc_no'        => null,
                        'gp_no'        => null,
                        'description'  => null,
                        'item_name'    => $item['item_name'],
                        'item_name_urdu'=> $item['item_name_urdu'] ?? null,
                        'qty'          => $item['qty'],
                        'remaining_qty'=> $item['remaining_qty'] ?? 0,
                        'rate'         => $item['rate'],
                        'watt'         => floatval($item['watt'] ?? 0),
                        'item_discount'=> $item['item_discount'] ?? 0,
                        'line_amount'  => $item['line_amount']   ?? 0,
                        'policy_price' => $item['policy_price'] ?? 0,
                        'avg_price'    => $item['avg_price']    ?? 0,
                        'avg_s_value'  => $item['avg_s_value']  ?? 0,
                        'n_price'      => $item['n_price']      ?? 0,
                        'stock_value'  => $item['stock_value']  ?? 0,
                        'debit'        => null,
                        'credit'       => null,
                        'balance'      => null,
                    ];
                }

                // Total Qty + Stock Value + Additional Discount + Extra Charges
                $transactions[] = [
                    'row_type'  => 'sale_total',
                    'total_qty' => $invoiceTotalQty,
                    'total_stk' => $invoiceTotalStk,
                    'add_disc'  => $d['add_disc'] ?? 0,
                    'extra_chg' => $d['extra_chg'] ?? 0,
                    'total_net' => $d['debit'],
                ];

            } elseif ($type === 'return') {
                $credit          = $d['credit'];
                $runningBalance -= $credit;
                $totalCredit    += $credit;

                $transactions[] = [
                    'row_type'    => 'return',
                    'return_id'   => $d['return_id'] ?? null,
                    'sale_id'     => $d['sale_id'] ?? null,
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $d['invoice_no'],
                    'bill'        => $d['return_note'],
                    'dc_no'       => '-',
                    'gp_no'       => '-',
                    'description' => 'SALE RETURN',
                    'item_name'   => $d['item_name'],
                    'qty'         => $d['qty'] > 0 ? $d['qty'] : null,
                    'rate'        => $d['rate'] > 0 ? $d['rate'] : null,
                    'debit'       => 0,
                    'credit'      => $credit,
                    'balance'     => $runningBalance,
                ];

            } elseif ($type === 'return_refund') {
                $debit           = $d['debit'];
                $runningBalance += $debit;
                $totalDebit     += $debit;

                $transactions[] = [
                    'row_type'     => 'payment_voucher',
                    'voucher_id'   => $d['entry_id'],
                    'voucher_type' => 'payment_voucher',
                    'date'         => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'          => $d['vno'],
                    'bill'         => $d['reference_no'] ?? '-',
                    'dc_no'        => '-',
                    'gp_no'        => '-',
                    'description'  => $d['description'],
                    'item_name'    => $d['account'],
                    'qty'          => null,
                    'rate'         => null,
                    'debit'        => $debit,
                    'credit'       => 0,
                    'balance'      => $runningBalance,
                ];

            } elseif ($type === 'receipt') {
                $credit          = $d['credit'];
                $runningBalance -= $credit;
                $totalCredit    += $credit;

                $isBank = $d['is_bank'] ?? false;
                $prefix = $isBank ? 'BR' : 'CR';
                $count  = $isBank ? ++$brCounter : ++$crCounter;
                $displayVno = $d['vno'] . " ($prefix-$count)";

                $transactions[] = [
                    'row_type'     => 'receipt',
                    'voucher_id'   => $d['voucher_id'] ?? null,
                    'voucher_type' => 'receipt',
                    'date'         => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'          => $displayVno,
                    'bill'         => $d['reference_no'],
                    'dc_no'        => '-',
                    'gp_no'        => '-',
                    'description'  => $d['description'],
                    'item_name'    => $d['account'],
                    'qty'          => null,
                    'rate'         => null,
                    'debit'        => 0,
                    'credit'       => $credit,
                    'balance'      => $runningBalance,
                ];

            } elseif ($type === 'contra_purchase') {
                $credit          = $d['credit'];
                $runningBalance -= $credit;
                $totalCredit    += $credit;

                $transactions[] = [
                    'row_type'     => 'receipt',
                    'voucher_id'   => $d['voucher_id'] ?? null,
                    'voucher_type' => 'contra_purchase',
                    'date'         => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'          => $d['vno'],
                    'bill'         => $d['reference_no'] ?? '-',
                    'dc_no'        => '-',
                    'gp_no'        => '-',
                    'description'  => $d['description'],
                    'item_name'    => 'CONTRA PURCHASE',
                    'qty'          => null,
                    'rate'         => null,
                    'debit'        => 0,
                    'credit'       => $credit,
                    'balance'      => $runningBalance,
                ];

            } elseif ($type === 'payment_voucher') {
                $credit          = $d['credit'];
                $runningBalance -= $credit;
                $totalCredit    += $credit;

                $isBank = $d['is_bank'] ?? false;
                $prefix = $isBank ? 'BP' : 'CP';
                $count  = $isBank ? ++$bpCounter : ++$cpCounter;
                $displayVno = $d['vno'] . " ($prefix-$count)";

                $transactions[] = [
                    'row_type'     => 'payment_voucher',
                    'voucher_id'   => $d['voucher_id'] ?? null,
                    'voucher_type' => 'payment_voucher',
                    'date'         => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'          => $displayVno,
                    'bill'         => $d['reference_no'],
                    'dc_no'        => '-',
                    'gp_no'        => '-',
                    'description'  => $d['description'],
                    'item_name'    => $d['account'],
                    'qty'          => null,
                    'rate'         => null,
                    'debit'        => 0,
                    'credit'       => $credit,
                    'balance'      => $runningBalance,
                ];

            } elseif ($type === 'journal_debit') {
                $debit          = $d['debit'];
                $runningBalance += $debit;
                $totalDebit     += $debit;

                $transactions[] = [
                    'row_type'     => 'journal_debit',
                    'voucher_id'   => $d['voucher_id'] ?? null,
                    'voucher_type' => 'journal_voucher',
                    'date'         => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'          => $d['vno'],
                    'bill'         => $d['reference_no'],
                    'dc_no'        => '-',
                    'gp_no'        => '-',
                    'description'  => $d['description'],
                    'item_name'    => $d['account'] ?? 'JV Adjustment',
                    'qty'          => null,
                    'rate'         => null,
                    'debit'        => $debit,
                    'credit'       => 0,
                    'balance'      => $runningBalance,
                ];

            } elseif ($type === 'journal_credit') {
                $credit          = $d['credit'];
                $runningBalance -= $credit;
                $totalCredit    += $credit;

                $transactions[] = [
                    'row_type'     => 'journal_credit',
                    'voucher_id'   => $d['voucher_id'] ?? null,
                    'voucher_type' => 'journal_voucher',
                    'date'         => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'          => $d['vno'],
                    'bill'         => $d['reference_no'],
                    'dc_no'        => '-',
                    'gp_no'        => '-',
                    'description'  => $d['description'],
                    'item_name'    => $d['account'] ?? 'JV Adjustment',
                    'qty'          => null,
                    'rate'         => null,
                    'debit'        => 0,
                    'credit'       => $credit,
                    'balance'      => $runningBalance,
                ];

            } elseif ($type === 'discount') {
                // ERP Standard: Invoice-level discount credited to customer
                $credit          = $d['credit'];
                $runningBalance -= $credit;
                $totalCredit    += $credit;

                $transactions[] = [
                    'row_type'    => 'discount',
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $d['invoice_no'],
                    'bill'        => null,
                    'dc_no'       => null,
                    'gp_no'       => null,
                    'description' => $d['description'],
                    'item_name'   => null,
                    'qty'         => null,
                    'rate'        => null,
                    'debit'       => 0,
                    'credit'      => $credit,
                    'balance'     => $runningBalance,
                ];
            }
        }

        return response()->json([
            'customer' => [
                'id'             => $customer->id,
                'name'           => $customer->customer_name,
                'type'           => $customer->customer_type,
                'address'        => $customer->address ?? '-',
                'mobile'         => $customer->mobile ?? '-',
                'email'          => $customer->email_address ?? '-',
                'credit_limit'   => floatval($customer->credit_limit ?? 0),
                'opening_balance'=> $openingBalance,
            ],
            'period' => [
                'start' => $start,
                'end'   => $end,
            ],
            'opening_balance' => $openingBalance,
            'total_debit'         => $totalDebit,
            'total_credit'        => $totalCredit,
            'total_remaining_qty' => $totalRemainingQty,
            'remaining_items'     => $remainingItems,
            'closing_balance'     => $runningBalance,
            'transactions'        => $transactions,
        ]);
    }

    /**
     * Preview a ledger transaction (Sale Invoice, Receipt Voucher, Payment Voucher, JV)
     * Loaded inside modal iframe or in a new browser tab.
     */
    public function transaction_preview(Request $request)
    {
        $type = strtolower($request->query('type', ''));
        $id   = $request->query('id');
        $vno  = $request->query('vno');

        $notFoundView = function ($title, $msg) {
            return response('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . e($title) . '</title>' .
                '<style>body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f8fafc;display:flex;align-items:center;justify-content:center;height:100vh;color:#334155;text-align:center;padding:20px;box-sizing:border-box;}' .
                '.card{background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:32px 24px;max-width:440px;box-shadow:0 4px 15px rgba(0,0,0,0.05);}' .
                'h4{margin:0 0 10px;color:#0f172a;font-size:18px;}p{margin:0;color:#64748b;font-size:13.5px;line-height:1.5;}</style></head>' .
                '<body><div class="card"><div style="font-size:38px;margin-bottom:12px;">⚠️</div><h4>' . e($title) . '</h4><p>' . e($msg) . '</p></div></body></html>', 200)
                ->header('Content-Type', 'text/html');
        };

        if ($type === 'sale') {
            $sale = null;
            if ($id && is_numeric($id)) {
                $sale = Sale::find($id);
            }
            if (!$sale && $vno) {
                $cleanVno = trim(explode('(', $vno)[0]);
                $sale = Sale::where('invoice_no', $cleanVno)
                    ->orWhere('manual_invoice', $cleanVno)
                    ->first();
            }
            if (!$sale) {
                return $notFoundView('Sale Invoice Not Found', 'The requested sale invoice (' . ($vno ?: ('#' . $id)) . ') could not be found.');
            }
            request()->merge(['embed' => 1]);
            return app(SaleController::class)->invoicesale($sale);
        }

        if ($type === 'receipt') {
            $receipt = null;
            if ($id && is_numeric($id)) {
                $receipt = ReceiptsVoucher::find($id);
            }
            if (!$receipt && $vno) {
                $cleanVno = trim(explode('(', $vno)[0]);
                $cleanNum = preg_replace('/[^0-9]/', '', $cleanVno);
                $receipt = ReceiptsVoucher::where('rvid', $cleanVno)
                    ->orWhere('receipt_no', $cleanVno)
                    ->when($cleanNum, function ($q) use ($cleanNum) {
                        return $q->orWhere('id', (int)$cleanNum);
                    })
                    ->first();
            }
            if (!$receipt) {
                return $notFoundView('Receipt Voucher Not Found', 'The requested receipt voucher (' . ($vno ?: ('#' . $id)) . ') could not be found.');
            }
            return app(VoucherController::class)->print($receipt->id);
        }

        if ($type === 'payment' || $type === 'payment_voucher') {
            $pv = null;
            if ($id && is_numeric($id)) {
                $pv = PaymentVoucher::find($id);
            }
            if (!$pv && $vno) {
                $cleanVno = trim(explode('(', $vno)[0]);
                $cleanNum = preg_replace('/[^0-9]/', '', $cleanVno);
                $pv = PaymentVoucher::where('pvid', $cleanVno)
                    ->when($cleanNum, function ($q) use ($cleanNum) {
                        return $q->orWhere('id', (int)$cleanNum);
                    })
                    ->first();
            }
            if (!$pv) {
                return $notFoundView('Payment Voucher Not Found', 'The requested payment voucher (' . ($vno ?: ('#' . $id)) . ') could not be found.');
            }
            return app(VoucherController::class)->Paymentprint($pv->id);
        }

        if ($type === 'return') {
            if ($id && is_numeric($id)) {
                $ret = DB::table('sales_returns')->where('id', $id)->first();
                if ($ret && $ret->sale_id) {
                    $sale = Sale::find($ret->sale_id);
                    if ($sale) {
                        request()->merge(['embed' => 1]);
                        return app(SaleController::class)->invoicesale($sale);
                    }
                }
            }
            if ($vno) {
                $cleanVno = trim(explode('(', $vno)[0]);
                $sale = Sale::where('invoice_no', $cleanVno)->orWhere('manual_invoice', $cleanVno)->first();
                if ($sale) {
                    request()->merge(['embed' => 1]);
                    return app(SaleController::class)->invoicesale($sale);
                }
            }
            return $notFoundView('Sale Return Not Found', 'The requested sale return (' . ($vno ?: ('#' . $id)) . ') could not be found.');
        }

        if ($type === 'journal' || $type === 'journal_voucher' || $type === 'journal_debit' || $type === 'journal_credit') {
            $jv = null;
            if ($id && is_numeric($id)) {
                $jv = JournalVoucher::find($id);
            }
            if (!$jv && $vno) {
                $cleanVno = trim(explode('(', $vno)[0]);
                $jv = JournalVoucher::where('jvid', $cleanVno)->orWhere('id', $cleanVno)->first();
            }
            if (!$jv) {
                return $notFoundView('Journal Voucher Not Found', 'The requested journal voucher (' . ($vno ?: ('#' . $id)) . ') could not be found.');
            }
            return app(VoucherController::class)->journal_voucher_print($jv->id);
        }

        return $notFoundView('Invalid Preview Request', 'The transaction preview type "' . e($type) . '" is not recognized.');
    }

    public function vendor_ledger_new()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        // Determine which branches user can view
        if ($user->hasRole('super admin')) {
            $branches = \App\Models\Branch::orderBy('name')->get();
        } else {
            $branches = \App\Models\Branch::where('id', $user->branch_id)->get();
        }

        // Vendors (support branch-specific as well as global vendors)
        $vendorQuery = \App\Models\Vendor::select('id', 'name', 'phone', 'email', 'opening_balance');
        
        if (!$user->hasRole('super admin') && ($user->branch_id ?? 0) > 0) {
            $vendorQuery->where(function($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhere('branch_id', 0)
                  ->orWhereNull('branch_id');
            });
        }
        
        $vendors = $vendorQuery->orderBy('name')->get();

        $startDate = date('Y-m-01');
        $endDate   = date('Y-m-d');

        return view('admin_panel.reporting.vendor_ledger_new', compact('branches', 'vendors', 'startDate', 'endDate'));
    }

    public function vendorsByBranch(Request $request)
    {
        $branchId = $request->branch_id ?? null;
        $query = \App\Models\Vendor::query();

        if ($branchId && $branchId !== 'all') {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhere('branch_id', 0)
                  ->orWhereNull('branch_id');
            });
        }

        $vendors = $query->select('id', 'name', 'phone')
            ->orderBy('name')
            ->get();
            
        // Map to match customer format & standard format
        $result = $vendors->map(function($v) {
            return [
                'id' => $v->id,
                'name' => $v->name,
                'customer_name' => $v->name, // Using customer_name key so JS doesn't break
                'customer_type' => '-'
            ];
        });
            
        return response()->json($result);
    }

    public function fetch_vendor_ledger_new(Request $request)
    {
        $user       = auth()->user();
        $vendorId   = (int) $request->vendor_id;
        $branchId   = ($request->filled('branch_id') && $request->branch_id !== 'all' && $request->branch_id !== '') ? (int) $request->branch_id : null;
        $start      = $request->start_date;
        $end        = $request->end_date;

        if (!$vendorId || !$start || !$end) {
            return response()->json(['error' => 'Missing required parameters (vendor and dates)'], 422);
        }

        $vendor       = \App\Models\Vendor::findOrFail($vendorId);
        $vBranchId   = (int) ($vendor->branch_id ?? 0);
        
        // Ensure branch isolation
        $allowed      = false;
        if ($user && $user->hasRole('super admin')) {
            $allowed = true;
        } elseif ($vBranchId === 0 || ($user && ($user->branch_id ?? 0) === $vBranchId)) {
            $allowed = true;
        } elseif ($user && $user->can('report.vendor.ledger.branch.view')) {
            $allowed = true;
        }

        if (!$allowed) {
            return response()->json(['error' => 'Unauthorized vendor access'], 403);
        }

        // ── Mathematically Exact Opening Balance ──────────
        $openingBalance = $this->getVendorOpeningBalance($vendorId, $branchId, $start);

        // ── Gate Pass lookup map for Purchases: purchase_id → {dc_no, gp_no} ───
        $gpMap = [];
        $gpRows = DB::table('inward_gatepasses')
            ->whereNotNull('purchase_id')
            ->select('purchase_id', 'gatepass_no', 'bilty_no', 'delivery_challan_no')
            ->get();
        foreach ($gpRows as $gp) {
            $gpMap[$gp->purchase_id] = [
                'dc_no'  => $gp->delivery_challan_no ?: ($gp->bilty_no ?: '-'),
                'gp_no'  => $gp->gatepass_no ?? '-',
            ];
        }

        // ══════════════════════════════════════════════════════════════════
        // PART 1 – PURCHASES (Credit)
        // ══════════════════════════════════════════════════════════════════
        $purchasesQuery = DB::table('purchases')
            ->leftJoin('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->leftJoin('products', 'products.id', '=', 'purchase_items.product_id')
            ->where('purchases.vendor_id', $vendorId)
            ->whereNull('purchases.deleted_at')
            ->whereBetween(DB::raw('DATE(purchases.created_at)'), [$start, $end]);

        if ($branchId) {
            $purchasesQuery->where('purchases.branch_id', $branchId);
        } elseif (!$user->hasRole('super admin') && ($user->branch_id ?? 0) > 0) {
            $purchasesQuery->where('purchases.branch_id', $user->branch_id);
        }

        $purchasesRaw = $purchasesQuery->select(
                'purchases.id          as purchase_id',
                'purchases.invoice_no  as invoice_no',
                DB::raw('COALESCE(purchases.net_amount, 0) as total_net'),
                'purchases.discount    as additional_discount',
                'purchases.extra_cost  as extra_charges',
                'purchases.created_at  as txn_date',
                'products.item_name    as item_name',
                'products.item_name_urdu as item_name_urdu',
                'purchase_items.qty    as qty',
                'purchase_items.item_discount as item_discount',
                'purchase_items.price   as rate',
                'purchase_items.line_total as line_amount'
            )
            ->orderBy('purchases.created_at', 'asc')
            ->orderBy('purchases.id', 'asc')
            ->get();

        $purchasesGrouped = [];
        foreach ($purchasesRaw as $row) {
            if (!isset($purchasesGrouped[$row->purchase_id])) {
                $purchasesGrouped[$row->purchase_id]['header'] = [
                    'invoice_no'          => $row->invoice_no,
                    'total_net'           => floatval($row->total_net ?? 0),
                    'additional_discount' => floatval($row->additional_discount ?? 0),
                    'extra_charges'       => floatval($row->extra_charges ?? 0),
                    'txn_date'            => $row->txn_date,
                ];
            }
            if ($row->item_name) {
                $pQty     = floatval($row->qty ?? 0);
                $pRate    = floatval($row->rate ?? 0);
                $pLineAmt = floatval($row->line_amount ?? 0);
                if ($pRate == 0 && $pQty > 0 && $pLineAmt > 0) {
                    $pRate = round($pLineAmt / $pQty, 2);
                }
                if ($pLineAmt == 0 && $pQty > 0 && $pRate > 0) {
                    $pLineAmt = round($pQty * $pRate, 2);
                }
                $purchasesGrouped[$row->purchase_id]['items'][] = [
                    'item_name'     => $row->item_name,
                    'item_name_urdu'=> $row->item_name_urdu ?? null,
                    'qty'           => $pQty,
                    'rate'          => $pRate,
                    'item_discount' => floatval($row->item_discount ?? 0),
                    'line_amount'   => $pLineAmt,
                ];
            }
        }

        // ══════════════════════════════════════════════════════════════════
        // PART 2 – PURCHASE RETURNS (Debit)
        // ══════════════════════════════════════════════════════════════════
        $returnsRaw = DB::table('purchase_returns')
            ->where('vendor_id', $vendorId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$start, $end])
            ->select(
                'id',
                'return_invoice as invoice_no',
                'return_reason as return_note',
                'net_amount as debit_amount',
                'created_at as txn_date'
            )
            ->orderBy('created_at', 'asc')
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // PART 3 – PAYMENT VOUCHERS (Payments TO vendor - Debit)
        // ══════════════════════════════════════════════════════════════════
        $paymentVouchersQ = DB::table('payment_vouchers')
            ->leftJoin('narrations', 'narrations.id', '=', 'payment_vouchers.narration_id')
            ->leftJoin('accounts', 'accounts.id', '=', 'payment_vouchers.row_account_id')
            ->leftJoin('account_heads', 'account_heads.id', '=', 'accounts.head_id')
            ->where(function ($q) {
                $q->whereNull('payment_vouchers.status')->orWhere('payment_vouchers.status', '!=', 'voided');
            })
            ->whereBetween(DB::raw('DATE(payment_vouchers.receipt_date)'), [$start, $end]);
        \App\Services\VoucherService::scopePartyQuery($paymentVouchersQ, $vendorId, 'payment_vouchers.party_id');
        $paymentVouchersRaw = $paymentVouchersQ->select(
                'payment_vouchers.id           as id',
                'payment_vouchers.pvid         as pvid',
                'payment_vouchers.type         as type',
                'payment_vouchers.party_id     as party_id',
                'payment_vouchers.party_amount as party_amount',
                'payment_vouchers.receipt_date as txn_date',
                'payment_vouchers.amount       as amount',
                'payment_vouchers.total_amount as total_amount',
                'payment_vouchers.total_amount as debit_amount',
                'payment_vouchers.reference_no as reference_no',
                'payment_vouchers.remarks      as remarks',
                'payment_vouchers.row_account_id as row_account_id',
                'narrations.narration          as narration_text',
                'account_heads.name            as head_name',
                'accounts.title                as account_title'
            )
            ->orderBy('payment_vouchers.receipt_date', 'asc')
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // PART 4 – RECEIPT VOUCHERS (Refunds FROM vendor - Credit)
        // ══════════════════════════════════════════════════════════════════
        $receiptsQ = DB::table('receipts_vouchers')
            ->leftJoin('narrations', 'narrations.id', '=', 'receipts_vouchers.narration_id')
            ->leftJoin('accounts', 'accounts.id', '=', 'receipts_vouchers.row_account_id')
            ->leftJoin('account_heads', 'account_heads.id', '=', 'accounts.head_id')
            ->where(function ($q) {
                $q->whereNull('receipts_vouchers.status')->orWhere('receipts_vouchers.status', '!=', 'voided');
            })
            ->whereBetween(DB::raw('DATE(receipts_vouchers.receipt_date)'), [$start, $end]);
        \App\Services\VoucherService::scopePartyQuery($receiptsQ, $vendorId, 'receipts_vouchers.party_id');
        $receiptsRaw = $receiptsQ->select(
                'receipts_vouchers.id           as id',
                'receipts_vouchers.rvid         as rvid',
                'receipts_vouchers.type         as type',
                'receipts_vouchers.party_id     as party_id',
                'receipts_vouchers.party_amount as party_amount',
                'receipts_vouchers.receipt_date as txn_date',
                'receipts_vouchers.amount       as amount',
                'receipts_vouchers.total_amount as total_amount',
                'receipts_vouchers.total_amount as credit_amount',
                'receipts_vouchers.reference_no as reference_no',
                'receipts_vouchers.remarks      as remarks',
                'receipts_vouchers.row_account_id as row_account_id',
                'narrations.narration           as narration_text',
                'account_heads.name             as head_name',
                'accounts.title                 as account_title'
            )
            ->orderBy('receipts_vouchers.receipt_date', 'asc')
            ->get();
            
        // ══════════════════════════════════════════════════════════════════
        // PART 5 – VENDOR PAYMENTS (Legacy alternative - Debit)
        // ══════════════════════════════════════════════════════════════════
        $vendorPaymentsRaw = DB::table('vendor_payments')
            ->where('vendor_id', $vendorId)
            ->whereBetween(DB::raw('DATE(payment_date)'), [$start, $end])
            ->select(
                'id',
                'payment_date as txn_date',
                'amount as debit_amount',
                'payment_method',
                'note as remarks'
            )
            ->orderBy('payment_date', 'asc')
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // PART 6 – JOURNAL VOUCHERS (Debits & Credits for Vendor)
        // ══════════════════════════════════════════════════════════════════
        $jvsDebitVendor = DB::table('journal_vouchers')
            ->where('debit_party_type', 'vendor')
            ->where('debit_party_id', $vendorId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->whereBetween(DB::raw('DATE(voucher_date)'), [$start, $end])
            ->get();

        $jvsCreditVendor = DB::table('journal_vouchers')
            ->where('credit_party_type', 'vendor')
            ->where('credit_party_id', $vendorId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            })
            ->whereBetween(DB::raw('DATE(voucher_date)'), [$start, $end])
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // BUILD UNIFIED EVENT LIST
        // ══════════════════════════════════════════════════════════════════
        $events = [];

        // Add Purchases
        foreach ($purchasesGrouped as $purchaseId => $purchaseData) {
            $header  = $purchaseData['header'];
            $items   = $purchaseData['items'] ?? [];
            $gp      = $gpMap[$purchaseId] ?? ['dc_no' => '-', 'gp_no' => '-'];

            $events[] = [
                'sort_date'   => $header['txn_date'],
                'type'        => 'purchase',
                'priority'    => 1,
                'data'        => [
                    'invoice_no'  => $header['invoice_no'],
                    'bill_no'     => '-',
                    'dc_no'       => $gp['dc_no'],
                    'gp_no'       => $gp['gp_no'],
                    'txn_date'    => $header['txn_date'],
                    'credit'      => $header['total_net'], // Purchases are Credit for Vendor
                    'add_disc'    => $header['additional_discount'] ?? 0,
                    'extra_chg'   => $header['extra_charges'] ?? 0,
                    'items'       => $items,
                ],
            ];
        }

        // Add Purchase Returns
        foreach ($returnsRaw as $ret) {
            $events[] = [
                'sort_date' => $ret->txn_date,
                'type'      => 'return',
                'priority'  => 2,
                'data'      => [
                    'invoice_no'  => $ret->invoice_no ?? '-',
                    'return_note' => $ret->return_note ?? 'RETURN',
                    'txn_date'    => $ret->txn_date,
                    'debit'       => floatval($ret->debit_amount ?? 0), // Returns are Debit
                    'item_name'   => 'PURCHASE RETURN',
                    'qty'         => null,
                    'rate'        => null,
                ],
            ];
        }

        // Add Receipt Vouchers (Refunds FROM vendor - Credit)
        foreach ($receiptsRaw as $rec) {
            $creditAmt = \App\Services\VoucherService::getPartyAmount($rec, $vendorId, 'vendor');
            if ($creditAmt <= 0) {
                continue;
            }

            $isBank = (isset($rec->head_name) && str_contains(strtolower($rec->head_name), 'bank'))
                || (isset($rec->account_title) && str_contains(strtolower($rec->account_title), 'bank')) // Using account_title join if available
                || (isset($rec->remarks) && str_contains(strtolower($rec->remarks), 'bank'));
            $vno  = $rec->rvid ?? ('RV-' . $rec->id);
            $desc = $rec->narration_text ?? ($rec->remarks ?: 'REFUND RECEIVED');
            
            $accIds = \App\Services\VoucherService::safeDecodeArray($rec->row_account_id);
            $amounts = \App\Services\VoucherService::safeDecodeArray($rec->amount);
            $accountInfo = null;
            if (!empty($accIds)) {
                $accounts = DB::table('accounts')
                    ->whereIn('id', $accIds)
                    ->select('id', 'title', 'account_code')
                    ->get()
                    ->keyBy('id');
                
                $accDetails = [];
                // If amounts is a flat array corresponding to accIds indices
                foreach ($accIds as $idx => $id) {
                    if (isset($accounts[$id])) {
                        $acc = $accounts[$id];
                        $amt = isset($amounts[$idx]) ? number_format((float)$amounts[$idx], 2) : '0.00';
                        $accDetails[] = $acc->title . ($acc->account_code ? " ({$acc->account_code})" : "") . ": " . $amt;
                    }
                }
                $accountInfo = implode(", ", $accDetails);
            }

            $events[] = [
                'sort_date' => $rec->txn_date,
                'type'      => 'receipt',
                'priority'  => 3,
                'data'      => [
                    'vno'          => $vno,
                    'reference_no' => $rec->reference_no ?? '-',
                    'description'  => strtoupper($desc),
                    'account_info' => $accountInfo,
                    'txn_date'     => $rec->txn_date,
                    'credit'       => $creditAmt,
                    'is_bank'      => $isBank,
                ],
            ];
        }

        // Add Payment Vouchers (Payments TO vendor - Debit)
        foreach ($paymentVouchersRaw as $pv) {
            $debitAmt = \App\Services\VoucherService::getPartyAmount($pv, $vendorId, 'vendor');
            if ($debitAmt <= 0) {
                continue;
            }

            $isBank = (isset($pv->head_name) && str_contains(strtolower($pv->head_name), 'bank')) 
                || (isset($pv->account_title) && str_contains(strtolower($pv->account_title), 'bank'))
                || (isset($pv->account_title) && in_array(strtoupper($pv->account_title), ['HBL', 'MCB', 'UBL', 'MEEZAN', 'ALLIED', 'ASKARI']));
            $vno  = $pv->pvid ?? ('PV-' . $pv->id);
            $desc = $pv->narration_text ?? ($pv->remarks ?: 'PAYMENT TO VENDOR');
            
            $accIds = \App\Services\VoucherService::safeDecodeArray($pv->row_account_id);
            $amounts = \App\Services\VoucherService::safeDecodeArray($pv->amount);
            $accountInfo = null;
            if (!empty($accIds)) {
                $accounts = DB::table('accounts')
                    ->whereIn('id', $accIds)
                    ->select('id', 'title', 'account_code')
                    ->get()
                    ->keyBy('id');
                
                $accDetails = [];
                foreach ($accIds as $idx => $id) {
                    if (isset($accounts[$id])) {
                        $acc = $accounts[$id];
                        $amt = isset($amounts[$idx]) ? number_format((float)$amounts[$idx], 2) : '0.00';
                        $accDetails[] = $acc->title . ($acc->account_code ? " ({$acc->account_code})" : "") . ": " . $amt;
                    }
                }
                $accountInfo = implode(", ", $accDetails);
            }

            $events[] = [
                'sort_date' => $pv->txn_date,
                'type'      => 'payment_voucher',
                'priority'  => 4,
                'data'      => [
                    'vno'          => $vno,
                    'reference_no' => $pv->reference_no ?? '-',
                    'description'  => strtoupper($desc),
                    'account_info' => $accountInfo,
                    'txn_date'     => $pv->txn_date,
                    'debit'        => $debitAmt,
                    'is_bank'      => $isBank,
                ],
            ];
        }

        // Add Legacy Vendor Payments
        foreach ($vendorPaymentsRaw as $vp) {
            $events[] = [
                'sort_date' => $vp->txn_date,
                'type'      => 'legacy_payment',
                'priority'  => 5,
                'data'      => [
                    'vno'          => 'PAY-' . $vp->id,
                    'description'  => strtoupper($vp->remarks ?: 'LEGACY PAYMENT'),
                    'account_info' => strtoupper($vp->payment_method ?? 'CASH/BANK'),
                    'txn_date'     => $vp->txn_date,
                    'debit'        => floatval($vp->debit_amount ?? 0),
                ],
            ];
        }

        // Add Journal Vouchers for Vendor
        foreach ($jvsDebitVendor as $jv) {
            $events[] = [
                'sort_date' => $jv->voucher_date,
                'type'      => 'journal_debit',
                'priority'  => 6,
                'data'      => [
                    'vno'          => $jv->jvid,
                    'reference_no' => $jv->reference_no ?? '-',
                    'description'  => 'JOURNAL VOUCHER: ' . ($jv->remarks ?: 'ADJUSTMENT DEBIT'),
                    'account_info' => 'JV Adjustment',
                    'txn_date'     => $jv->voucher_date,
                    'debit'        => floatval($jv->amount ?? 0),
                    'credit'       => 0,
                ],
            ];
        }

        foreach ($jvsCreditVendor as $jv) {
            $events[] = [
                'sort_date' => $jv->voucher_date,
                'type'      => 'journal_credit',
                'priority'  => 7,
                'data'      => [
                    'vno'          => $jv->jvid,
                    'reference_no' => $jv->reference_no ?? '-',
                    'description'  => 'JOURNAL VOUCHER: ' . ($jv->remarks ?: 'ADJUSTMENT CREDIT'),
                    'account_info' => 'JV Adjustment',
                    'txn_date'     => $jv->voucher_date,
                    'debit'        => 0,
                    'credit'       => floatval($jv->amount ?? 0),
                ],
            ];
        }

        // Sort all events in strict chronological order
        usort($events, function ($a, $b) {
            $dateA = substr((string)$a['sort_date'], 0, 10);
            $dateB = substr((string)$b['sort_date'], 0, 10);
            $dateCompare = strcmp($dateA, $dateB);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            // Same date: sort by logical business priority (Purchase -> Return -> Receipt -> Payment -> JV)
            $pA = (int)($a['priority'] ?? 99);
            $pB = (int)($b['priority'] ?? 99);
            if ($pA !== $pB) {
                return $pA - $pB;
            }

            // Same priority: compare full timestamp if available, else tiebreak
            $timeCompare = strcmp((string)$a['sort_date'], (string)$b['sort_date']);
            if ($timeCompare !== 0) {
                return $timeCompare;
            }

            return strcmp((string)($a['data']['vno'] ?? ($a['data']['invoice_no'] ?? '')), (string)($b['data']['vno'] ?? ($b['data']['invoice_no'] ?? '')));
        });

        // ══════════════════════════════════════════════════════════════════
        // BUILD FINAL TRANSACTION ROWS WITH RUNNING BALANCE
        // ══════════════════════════════════════════════════════════════════
        $transactions   = [];
        $runningBalance = $openingBalance;
        $totalDebit     = 0;
        $totalCredit    = 0;
        $brCounter      = 0;
        $crCounter      = 0;
        $bpCounter      = 0;
        $cpCounter      = 0;

        foreach ($events as $event) {
            $type = $event['type'];
            $d    = $event['data'];

            if ($type === 'purchase') {
                $credit         = $d['credit']; // Vendor liability increases
                $runningBalance += $credit;
                $totalCredit    += $credit;

                // Parent summary row
                $transactions[] = [
                    'row_type'    => 'sale_header', // Kept as 'sale_header' so JS coloring works same
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $d['invoice_no'],
                    'bill'        => $d['bill_no'],
                    'dc_no'       => $d['dc_no'],
                    'gp_no'       => $d['gp_no'],
                    'description' => 'PURCHASE',
                    'item_name'   => null,
                    'qty'         => null,
                    'rate'        => null,
                    'debit'       => 0,
                    'credit'      => $credit,
                    'balance'     => $runningBalance,
                ];

                $invoiceTotalQty = 0;
                foreach ($d['items'] as $item) {
                    $invoiceTotalQty += $item['qty'];
                    $transactions[] = [
                        'row_type'     => 'sale_item',
                        'date'         => null,
                        'vno'          => null,
                        'bill'         => null,
                        'dc_no'        => null,
                        'gp_no'        => null,
                        'description'  => null,
                        'item_name'    => $item['item_name'],
                        'item_name_urdu'=> $item['item_name_urdu'] ?? null,
                        'qty'          => $item['qty'],
                        'rate'         => $item['rate'],
                        'item_discount'=> $item['item_discount'] ?? 0,
                        'line_amount'  => $item['line_amount']   ?? 0,
                        'debit'        => null,
                        'credit'       => null,
                        'balance'      => null,
                    ];
                }

                $transactions[] = [
                    'row_type'  => 'sale_total',
                    'total_qty' => $invoiceTotalQty,
                    'total_stk' => 0,
                    'add_disc'  => $d['add_disc'] ?? 0,
                    'extra_chg' => $d['extra_chg'] ?? 0,
                    'total_net' => $d['credit'],
                ];

            } elseif ($type === 'return') {
                $debit           = $d['debit'];
                $runningBalance -= $debit; // Vendor liability decreases
                $totalDebit     += $debit;

                $transactions[] = [
                    'row_type'    => 'return',
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $d['invoice_no'],
                    'bill'        => $d['return_note'],
                    'dc_no'       => '-',
                    'gp_no'       => '-',
                    'description' => 'PURCHASE RETURN',
                    'item_name'   => $d['item_name'],
                    'qty'         => $d['qty'] > 0 ? $d['qty'] : null,
                    'rate'        => $d['rate'] > 0 ? $d['rate'] : null,
                    'debit'       => $debit,
                    'credit'      => 0,
                    'balance'     => $runningBalance,
                ];

            } elseif ($type === 'payment_voucher' || $type === 'legacy_payment' || $type === 'journal_debit') {
                $debit           = $d['debit'];
                $runningBalance -= $debit; // We pay them / JV adjustment, liability decreases
                $totalDebit     += $debit;

                $isBank = $d['is_bank'] ?? false;
                $prefix = $isBank ? 'BP' : 'CP';
                $count  = $isBank ? ++$bpCounter : ++$cpCounter;
                $displayVno = $type === 'journal_debit' ? $d['vno'] : ($d['vno'] . " ($prefix-$count)");

                $transactions[] = [
                    'row_type'    => 'payment_voucher',
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $displayVno,
                    'bill'        => $d['reference_no'] ?? '-',
                    'dc_no'       => '-',
                    'gp_no'       => '-',
                    'description' => $d['description'],
                    'item_name'   => $d['account_info'] ?? null,
                    'qty'         => null,
                    'rate'        => null,
                    'debit'       => $debit,
                    'credit'      => 0,
                    'balance'     => $runningBalance,
                ];

            } elseif ($type === 'receipt' || $type === 'journal_credit') {
                $credit          = $d['credit'];
                $runningBalance += $credit; // Refund to us / JV adjustment increases liability
                $totalCredit    += $credit;

                $isBank = $d['is_bank'] ?? false;
                $prefix = $isBank ? 'BR' : 'CR';
                $count  = $isBank ? ++$brCounter : ++$crCounter;
                $displayVno = $type === 'journal_credit' ? $d['vno'] : ($d['vno'] . " ($prefix-$count)");

                $transactions[] = [
                    'row_type'    => 'receipt',
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $displayVno,
                    'bill'        => $d['reference_no'] ?? '-',
                    'dc_no'       => '-',
                    'gp_no'       => '-',
                    'description' => $d['description'],
                    'item_name'   => $d['account_info'] ?? null,
                    'qty'         => null,
                    'rate'        => null,
                    'debit'       => 0,
                    'credit'      => $credit,
                    'balance'     => $runningBalance,
                ];
            }
        }

        return response()->json([
            'vendor' => [
                'id'             => $vendor->id,
                'name'           => $vendor->name,
                'company'        => $vendor->company_name ?? '-',
                'mobile'         => $vendor->phone ?? '-',
                'email'          => $vendor->email ?? '-',
                'opening_balance'=> $openingBalance,
            ],
            'period' => [
                'start' => $start,
                'end'   => $end,
            ],
            'opening_balance' => $openingBalance,
            'total_debit'     => $totalDebit,
            'total_credit'    => $totalCredit,
            'closing_balance' => $runningBalance,
            'transactions'    => $transactions,
        ]);
    }

    public function item_stock_report()
    {
        // Determine which branches user can view
        $user = Auth::user();
        $userBranches = [];
        $selectedBranchId = null;

        if ($user->hasRole('super admin')) {
            // Super admin can view all branches
            $userBranches = \App\Models\Branch::orderBy('name')->get();
            $selectedBranchId = request('branch_id') ?? $userBranches->first()?->id ?? 1;
        } elseif ($user->hasRole('branch admin') || $user->hasRole('warehouse manager')) {
            // Branch admin/manager can only view their own branch
            $userBranches = [\App\Models\Branch::find($user->branch_id)];
            $selectedBranchId = $user->branch_id;
        } else {
            // Regular users see only their branch
            $userBranches = [\App\Models\Branch::find($user->branch_id)];
            $selectedBranchId = $user->branch_id;
        }

        // Fetch all products so user can filter by any product or view all
        $products = Product::with('variants')->orderBy('item_name')->get();

        return view('admin_panel.reporting.item_stock_report', [
            'products' => $products,
            'userBranches' => $userBranches,
            'selectedBranchId' => $selectedBranchId,
            'isSuperAdmin' => $user->hasRole('super admin')
        ]);
    }

    /**
     * ✅ FETCH ITEM STOCK REPORT - Branch-Aware & Variant-Aware, ERP Standard
     * 
     * Features:
     * 1. Super admin: Can view any branch
     * 2. Branch admin: Views only their branch
     * 3. Variant products: Cleanly broken down into individual variant rows
     */
    public function fetchItemStock(Request $request)
    {
        $productIdRaw = $request->product_id;
        $requestedBranchId = $request->branch_id;

        $productId = null;
        $variantId = null;
        if (!empty($productIdRaw) && $productIdRaw !== 'all') {
            if (strpos($productIdRaw, ':') !== false) {
                [$productId, $variantId] = explode(':', $productIdRaw);
                $productId = (int)$productId;
                $variantId = (int)$variantId;
            } else {
                $productId = (int)$productIdRaw;
            }
        }

        // ================= BRANCH ACCESS CONTROL =================
        $user = Auth::user();
        $allowedBranchId = null;

        if ($user->hasRole('super admin')) {
            // Super admin can view any branch (or specific requested branch)
            $allowedBranchId = ($requestedBranchId && $requestedBranchId !== 'all') ? (int)$requestedBranchId : ($requestedBranchId === 'all' ? 'all' : $user->branch_id);
        } else {
            // Non-admin can only see their own branch
            $allowedBranchId = $user->branch_id;
        }

        // ================= FETCH PRODUCTS =================
        $productsQuery = Product::with(['unit', 'variants']);
        if ($productId) {
            $productsQuery->where('id', $productId);
        }
        $products = $productsQuery->orderBy('item_name')->get();

        // ================= PRE-AGGREGATE DATA FOR ACCURACY & PERFORMANCE =================
        
        // 1. Pre-calculate OPENING quantities from stock_movements (Branch & Variant aware)
        $openingQuery = DB::table('stock_movements')
            ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ']);
        if ($allowedBranchId && $allowedBranchId !== 'all') {
            $openingQuery->where('branch_id', $allowedBranchId);
        }
        $openingQtyMap = [];
        $variantOpeningQtyMap = [];
        foreach ($openingQuery->select('product_id', 'variant_id', DB::raw('SUM(qty) as total_qty'))->groupBy('product_id', 'variant_id')->get() as $om) {
            $pid = $om->product_id;
            $vid = $om->variant_id;
            $openingQtyMap[$pid] = ($openingQtyMap[$pid] ?? 0) + (float)$om->total_qty;
            if ($vid) {
                $variantOpeningQtyMap[$pid . '_' . $vid] = (float)$om->total_qty;
            }
        }

        // 2. Pre-calculate PURCHASED quantities & amounts (Branch & Variant aware)
        $purchaseQuery = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->whereNull('purchases.deleted_at');
        if ($allowedBranchId && $allowedBranchId !== 'all') {
            $purchaseQuery->where('purchases.branch_id', $allowedBranchId);
        }
        $productsPpp = $products->pluck('piece_per_pack', 'id')->toArray();
        $purchasePiecesMap = [];
        $purchaseAmountMap = [];
        $variantPurchasePiecesMap = [];
        $variantPurchaseAmountMap = [];
        foreach ($purchaseQuery->select('purchase_items.product_id', 'purchase_items.variant_id', 'purchase_items.qty', 'purchase_items.packing_qty', 'purchase_items.item_per_piece', 'purchase_items.line_total')->get() as $pItem) {
            $pid = $pItem->product_id;
            $vid = $pItem->variant_id;
            $pMult = (float)(($pItem->item_per_piece ?? 1) > 1 ? $pItem->item_per_piece : ($productsPpp[$pid] ?? 1));
            $effectiveItemQty = (!empty($pItem->packing_qty) && (float)$pItem->packing_qty > 0) ? $pItem->packing_qty : $pItem->qty;
            $pPieces = Product::parseCartonQtyToPieces($effectiveItemQty, $pMult, 'ctn');
            $purchasePiecesMap[$pid] = ($purchasePiecesMap[$pid] ?? 0) + $pPieces;
            $purchaseAmountMap[$pid] = ($purchaseAmountMap[$pid] ?? 0) + (float)$pItem->line_total;
            if ($vid) {
                $vKey = $pid . '_' . $vid;
                $variantPurchasePiecesMap[$vKey] = ($variantPurchasePiecesMap[$vKey] ?? 0) + $pPieces;
                $variantPurchaseAmountMap[$vKey] = ($variantPurchaseAmountMap[$vKey] ?? 0) + (float)$pItem->line_total;
            }
        }

        // 3. Pre-calculate DELIVERED / SOLD quantities & amounts (Branch & Variant aware)
        $salesQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where(function ($q) {
                $q->whereNull('sales.status')->orWhere('sales.status', '!=', 'cancelled');
            });
        if ($allowedBranchId && $allowedBranchId !== 'all') {
            $salesQuery->where('sales.branch_id', $allowedBranchId);
        }
        $salesPiecesMap = [];
        $salesAmountMap = [];
        $variantSalesPiecesMap = [];
        $variantSalesAmountMap = [];
        foreach ($salesQuery->select('sale_items.product_id', 'sale_items.variant_id', 'sale_items.sales_qty', 'sale_items.sale_unit', 'sale_items.amount')->get() as $sItem) {
            $pid = $sItem->product_id;
            $vid = $sItem->variant_id;
            $sPpp = (float)($productsPpp[$pid] ?? 1);
            $sPieces = Product::parseCartonQtyToPieces($sItem->sales_qty, $sPpp, $sItem->sale_unit);
            $salesPiecesMap[$pid] = ($salesPiecesMap[$pid] ?? 0) + $sPieces;
            $salesAmountMap[$pid] = ($salesAmountMap[$pid] ?? 0) + (float)$sItem->amount;
            if ($vid) {
                $vKey = $pid . '_' . $vid;
                $variantSalesPiecesMap[$vKey] = ($variantSalesPiecesMap[$vKey] ?? 0) + $sPieces;
                $variantSalesAmountMap[$vKey] = ($variantSalesAmountMap[$vKey] ?? 0) + (float)$sItem->amount;
            }
        }

        // Pre-calculate SALE RETURNS from stock_movements
        $returnQuery = DB::table('stock_movements')
            ->where('ref_type', 'SALE_RETURN')
            ->where('type', 'in');
        if ($allowedBranchId && $allowedBranchId !== 'all') {
            $returnQuery->where('branch_id', $allowedBranchId);
        }
        $returnQtyMap = [];
        $variantReturnQtyMap = [];
        foreach ($returnQuery->select('product_id', 'variant_id', DB::raw('COALESCE(SUM(qty), 0) as total_qty'))->groupBy('product_id', 'variant_id')->get() as $rItem) {
            $pid = $rItem->product_id;
            $vid = $rItem->variant_id;
            $returnQtyMap[$pid] = ($returnQtyMap[$pid] ?? 0) + (float)$rItem->total_qty;
            if ($vid) {
                $variantReturnQtyMap[$pid . '_' . $vid] = (float)$rItem->total_qty;
            }
        }

        // Check if there are any gatepasses with items
        $gpQuery = DB::table('outward_gatepasses')->whereNotNull('items');
        if ($allowedBranchId && $allowedBranchId !== 'all') {
            $gpQuery->where('branch_id', $allowedBranchId);
        }
        $gpDeliveredQty = [];
        $gpDeliveredAmount = [];
        foreach ($gpQuery->select('items')->cursor() as $gp) {
            $gpItems = json_decode($gp->items, true);
            if (is_array($gpItems)) {
                foreach ($gpItems as $gItem) {
                    $pid = $gItem['product_id'] ?? null;
                    if ($pid) {
                        $gpDeliveredQty[$pid] = ($gpDeliveredQty[$pid] ?? 0) + floatval($gItem['qty'] ?? 0);
                        $gpDeliveredAmount[$pid] = ($gpDeliveredAmount[$pid] ?? 0) + floatval($gItem['amount'] ?? 0);
                    }
                }
            }
        }

        // 4. Pre-calculate RESERVED quantities (Active bookings pending delivery / dispatch)
        $pendingBookingsQuery = DB::table('product_booking_items')
            ->join('productbookings', 'product_booking_items.booking_id', '=', 'productbookings.id')
            ->where(function ($q) {
                $q->whereNull('productbookings.status')
                  ->orWhereNotIn('productbookings.status', ['cancelled', 'sale', 'completed']);
            })
            ->where(function ($q) {
                $q->whereNull('productbookings.is_posted')
                  ->orWhere('productbookings.is_posted', 0);
            });
        if ($allowedBranchId && $allowedBranchId !== 'all') {
            $pendingBookingsQuery->where(function ($q) use ($allowedBranchId) {
                $q->where('product_booking_items.branch_id', $allowedBranchId)
                  ->orWhere('productbookings.branch_id', $allowedBranchId);
            });
        }
        $reservedQtyMap = [];
        $variantReservedQtyMap = [];
        foreach ($pendingBookingsQuery->select('product_booking_items.product_id', 'product_booking_items.variant_id', DB::raw('COALESCE(SUM(product_booking_items.sales_qty), 0) as total_qty'))->groupBy('product_booking_items.product_id', 'product_booking_items.variant_id')->get() as $bItem) {
            $pid = $bItem->product_id;
            $vid = $bItem->variant_id;
            $reservedQtyMap[$pid] = ($reservedQtyMap[$pid] ?? 0) + (float)$bItem->total_qty;
            if ($vid) {
                $variantReservedQtyMap[$pid . '_' . $vid] = (float)$bItem->total_qty;
            }
        }

        $rows = [];
        $grandTotalValue = 0;

        foreach ($products as $product) {
            $isVariantProduct = $product->has_variants && $product->variants && $product->variants->isNotEmpty();

            // Variant product: emit individual row per variant
            if ($isVariantProduct) {
                $variantsList = $product->variants;
                if ($variantId) {
                    $variantsList = $variantsList->where('id', $variantId);
                }

                foreach ($variantsList as $pv) {
                    $vWsQuery = WarehouseStock::where('product_id', $product->id)
                        ->where('variant_id', $pv->id);
                    if ($allowedBranchId && $allowedBranchId !== 'all') {
                        $vWsQuery->where('branch_id', $allowedBranchId);
                    }
                    $vWarehouseStocks = $vWsQuery->get();
                    $vTotalBalance = floatval($vWarehouseStocks->sum('quantity') ?? 0);

                    $vKey = $product->id . '_' . $pv->id;
                    $vOpeningStock = floatval($variantOpeningQtyMap[$vKey] ?? 0);
                    if ($vOpeningStock == 0 && empty($variantPurchasePiecesMap[$vKey])) {
                        $vOpeningStock = $vTotalBalance;
                    }

                    $vPurchased = floatval($variantPurchasePiecesMap[$vKey] ?? 0);
                    $vPurchaseAmount = floatval($variantPurchaseAmountMap[$vKey] ?? 0);

                    $vRawSold = floatval($variantSalesPiecesMap[$vKey] ?? 0);
                    $vRetQty = floatval($variantReturnQtyMap[$vKey] ?? 0);
                    $vSold = max(0, $vRawSold - $vRetQty);
                    $vSaleAmount = floatval($variantSalesAmountMap[$vKey] ?? 0);

                    $vReservedQty = floatval($variantReservedQtyMap[$vKey] ?? 0);

                    $vWarehouseBreakdown = $vWsQuery->with('warehouse')
                        ->select('warehouse_id', 'quantity')
                        ->get()
                        ->filter(fn($s) => floatval($s->quantity) > 0)
                        ->map(function ($stock) {
                            $warehouseName = $stock->warehouse_id === null ? 'Shop/Branch' : ($stock->warehouse?->warehouse_name ?? "Warehouse #{$stock->warehouse_id}");
                            $location = $stock->warehouse_id === null ? 'Main' : ($stock->warehouse?->location ?? '');
                            return [
                                'warehouse_id'   => $stock->warehouse_id,
                                'warehouse_name' => $warehouseName,
                                'location'       => $location,
                                'qty'            => floatval($stock->quantity)
                            ];
                        })
                        ->values()
                        ->toArray();

                    if (empty($vWarehouseBreakdown) && $vTotalBalance > 0) {
                        $vWarehouseBreakdown[] = [
                            'warehouse_id'   => null,
                            'warehouse_name' => 'Shop/Branch',
                            'location'       => 'Main',
                            'qty'            => $vTotalBalance
                        ];
                    }

                    $vWholesalePrice = floatval($pv->wholesale_price ?? 0);
                    if ($vWholesalePrice <= 0) {
                        $vWholesalePrice = floatval($pv->retail_price ?? 0);
                    }
                    if ($vWholesalePrice <= 0) {
                        $vWholesalePrice = floatval($product->wholesale_price ?: ($product->price ?? 0));
                    }
                    $vStockValue = $vTotalBalance * $vWholesalePrice;
                    $grandTotalValue += $vStockValue;

                    $colorLabel = $pv->color ? " ({$pv->color})" : ($pv->variant_name ? " ({$pv->variant_name})" : '');
                    $rowTitle = $product->item_name . $colorLabel;

                    $rows[] = [
                        'id'                 => $product->id,
                        'variant_id'         => $pv->id,
                        'item_code'          => $product->item_code,
                        'item_name'          => $rowTitle,
                        'unit'               => $product->unit?->name ?? 'Pcs',
                        'piece_per_pack'     => (float)($product->piece_per_pack ?? 1),
                        'pack_type'          => $product->pack_type ?? 'Standard',
                        'initial_stock'      => $vOpeningStock,
                        'purchased'          => $vPurchased,
                        'purchase_amount'    => $vPurchaseAmount,
                        'sold'               => $vSold,
                        'sale_amount'        => $vSaleAmount,
                        'reserved_qty'       => $vReservedQty,
                        'balance'            => $vTotalBalance,
                        'price'              => $vWholesalePrice,
                        'stock_value'        => $vStockValue,
                        'warehouse_breakdown'=> $vWarehouseBreakdown,
                        'branch_id'          => $allowedBranchId
                    ];
                }
                continue;
            }

            // Standard product (no variants)
            $wsQuery = WarehouseStock::where('product_id', $product->id);
            if ($allowedBranchId && $allowedBranchId !== 'all') {
                $wsQuery->where('branch_id', $allowedBranchId);
            }
            $warehouseStocks = $wsQuery->get();
            $totalBalance = floatval($warehouseStocks->sum('quantity') ?? 0);

            if ($totalBalance == 0) {
                $stQuery = \App\Models\Stock::where('product_id', $product->id);
                if ($allowedBranchId && $allowedBranchId !== 'all') {
                    $stQuery->where('branch_id', $allowedBranchId);
                }
                $totalBalance = floatval($stQuery->sum('qty') ?? 0);
            }

            $openingStock = floatval($openingQtyMap[$product->id] ?? (($allowedBranchId === 'all' || $product->branch_id == $allowedBranchId) ? ($product->initial_stock ?? 0) : 0));

            $multiplier = (float)($product->piece_per_pack > 1 ? $product->piece_per_pack : 1);

            $purchased = floatval($purchasePiecesMap[$product->id] ?? 0);
            $purchaseAmount = floatval($purchaseAmountMap[$product->id] ?? 0);

            $rawSoldPieces = floatval($salesPiecesMap[$product->id] ?? ($gpDeliveredQty[$product->id] ?? 0));
            $retQtyPieces = floatval($returnQtyMap[$product->id] ?? 0);
            $sold = max(0, $rawSoldPieces - $retQtyPieces);
            $saleAmount = floatval($salesAmountMap[$product->id] ?? ($gpDeliveredAmount[$product->id] ?? 0));

            $reservedQty = floatval($reservedQtyMap[$product->id] ?? 0);

            $wbQuery = WarehouseStock::where('product_id', $product->id);
            if ($allowedBranchId && $allowedBranchId !== 'all') {
                $wbQuery->where('branch_id', $allowedBranchId);
            }
            $warehouseBreakdown = $wbQuery->with('warehouse')
                ->select('warehouse_id', 'quantity')
                ->get()
                ->filter(fn($s) => floatval($s->quantity) > 0)
                ->map(function ($stock) {
                    $warehouseName = $stock->warehouse_id === null ? 'Shop/Branch' : ($stock->warehouse?->warehouse_name ?? "Warehouse #{$stock->warehouse_id}");
                    $location = $stock->warehouse_id === null ? 'Main' : ($stock->warehouse?->location ?? '');
                    return [
                        'warehouse_id'   => $stock->warehouse_id,
                        'warehouse_name' => $warehouseName,
                        'location'       => $location,
                        'qty'            => floatval($stock->quantity)
                    ];
                })
                ->values()
                ->toArray();

            if (empty($warehouseBreakdown) && $totalBalance > 0) {
                $warehouseBreakdown[] = [
                    'warehouse_id'   => null,
                    'warehouse_name' => 'Shop/Branch',
                    'location'       => 'Main',
                    'qty'            => $totalBalance
                ];
            }

            $wholesalePrice = floatval($product->wholesale_price ?? 0);
            if ($wholesalePrice <= 0) {
                $wholesalePrice = floatval($product->price ?? 0);
            }
            if ($wholesalePrice <= 0) {
                $latestPurchasePrice = DB::table('purchase_items')
                    ->where('product_id', $product->id)
                    ->latest()
                    ->value('price');
                $wholesalePrice = floatval($latestPurchasePrice ?? 0);
            }
            $stockValue = $totalBalance * $wholesalePrice;
            $grandTotalValue += $stockValue;

            $rows[] = [
                'id'                 => $product->id,
                'item_code'          => $product->item_code,
                'item_name'          => $product->item_name,
                'unit'               => $product->unit?->name ?? 'Pcs',
                'piece_per_pack'     => (float)($product->piece_per_pack ?? 1),
                'pack_type'          => $product->pack_type ?? 'Standard',
                'initial_stock'      => $openingStock,
                'purchased'          => $purchased,
                'purchase_amount'    => $purchaseAmount,
                'sold'               => $sold,
                'sale_amount'        => $saleAmount,
                'reserved_qty'       => $reservedQty,
                'balance'            => $totalBalance,
                'price'              => $wholesalePrice,
                'stock_value'        => $stockValue,
                'warehouse_breakdown'=> $warehouseBreakdown,
                'branch_id'          => $allowedBranchId
            ];
        }

        return response()->json([
            'data' => $rows,
            'grand_total' => $grandTotalValue,
            'branch_id' => $allowedBranchId
        ]);
    }

    public function purchase_report()
    {
        $user = Auth::user();
        
        // Determine which branches user can view
        if ($user->hasRole('super admin')) {
            $branches = \App\Models\Branch::orderBy('name')->get();
        } else {
            $branches = \App\Models\Branch::where('id', $user->branch_id)->get();
        }

        // Vendors are now BRANCH-SPECIFIC
        $vendorQuery = \App\Models\Vendor::select('id', 'name', 'phone');
        if (!$user->hasRole('super admin')) {
            $vendorQuery->where('branch_id', $user->branch_id ?? 0);
        }
        $vendors = $vendorQuery->orderBy('name')->get();

        // ✅ ERP STANDARD: Auto-select current month date range
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        
        return view('admin_panel.reporting.purchase_report', compact('startDate', 'endDate', 'vendors', 'branches', 'user'));
    }


    public function fetchPurchaseReport(Request $request)
    {
        $user      = Auth::user();
        $startDate = $request->start_date;
        $endDate   = $request->end_date;
        $vendorId  = $request->vendor_id;
        $branchId  = $request->branch_id;

        // Security: Non-admin can only see their own branch
        if (!$user->hasRole('super admin')) {
            $branchId = $user->branch_id;
        }

        $query = DB::table('purchases')
            ->leftJoin('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->leftJoin('products', 'purchase_items.product_id', '=', 'products.id')
            ->leftJoin('vendors', 'purchases.vendor_id', '=', 'vendors.id')
            ->when($branchId && $branchId !== 'all', function($q) use ($branchId) {
                return $q->where('purchases.branch_id', $branchId);
            })
            ->when($vendorId && $vendorId !== 'all', function($q) use ($vendorId) {
                return $q->where('purchases.vendor_id', $vendorId);
            })
            ->select(
                'purchases.id as purchase_id',
                'purchases.branch_id',
                'purchases.purchase_date',
                'purchases.invoice_no',
                'purchases.manual_invoice',
                'purchases.vendor_invoice_no',
                DB::raw('COALESCE(vendors.name, purchases.vendor_name, "Local Market") as vendor_name'),
                'products.item_code',
                'products.item_name',
                'purchase_items.unit',
                'purchase_items.price',
                'purchases.subtotal',
                'purchases.discount',
                'purchases.extra_cost',
                'purchases.net_amount',
                'purchases.paid_amount',
                'purchases.due_amount',
                DB::raw('SUM(purchase_items.qty) as qty'),
                DB::raw('SUM(purchase_items.item_discount) as item_discount'),
                DB::raw('SUM(purchase_items.line_total) as line_total'),
                DB::raw('GROUP_CONCAT(purchase_items.color SEPARATOR ", ") as colors')
            )
            ->groupBy(
                'purchases.id',
                'purchases.branch_id',
                'purchases.purchase_date',
                'purchases.invoice_no',
                'purchases.manual_invoice',
                'purchases.vendor_invoice_no',
                'vendors.name',
                'purchases.vendor_name',
                'products.item_code',
                'products.item_name',
                'purchase_items.product_id',
                'purchase_items.price',
                'purchase_items.unit',
                'purchases.subtotal',
                'purchases.discount',
                'purchases.extra_cost',
                'purchases.net_amount',
                'purchases.paid_amount',
                'purchases.due_amount'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('purchases.purchase_date', [$startDate, $endDate]);
        }

        if ($vendorId && $vendorId !== 'all') {
            $query->where('purchases.vendor_id', $vendorId);
        }

        $data = $query->orderBy('purchases.purchase_date', 'asc')->get();

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * ✅ PO vs Gatepass Report (Procurement Tracking)
     * Shows what was ordered (PO) vs what was actually received (Gatepass)
     */
    public function po_vs_gatepass_report()
    {
        $user = auth()->user();
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        
        // Determine which branches user can view
        if ($user->hasRole('super admin')) {
            $branches = \App\Models\Branch::orderBy('name')->get();
            $defaultBranchId = $user->branch_id ?? ($branches->first()?->id ?? 0);
        } else {
            $branches = \App\Models\Branch::where('id', $user->branch_id)->get();
            $defaultBranchId = $user->branch_id;
        }

        // Vendors are now BRANCH-SPECIFIC
        // Default to the vendors of the initially selected branch
        $vendors = \App\Models\Vendor::where('branch_id', $defaultBranchId)
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);
        
        return view('admin_panel.reporting.po_vs_gatepass', compact('startDate', 'endDate', 'vendors', 'branches', 'user'));
    }

    public function fetch_po_vs_gatepass_report(Request $request)
    {
        $user      = auth()->user();
        $startDate = $request->start_date;
        $endDate   = $request->end_date;
        $vendorId  = $request->vendor_id;
        $branchId  = $request->branch_id;

        // Security: Non-admin can only see their own branch
        if (!$user->hasRole('super admin')) {
            $branchId = $user->branch_id;
        }

        // ✅ Query: PO Items compared with their receipt status
        // Only includes POs that have been used to create a Gatepass
        $query = DB::table('purchase_orders')
            ->join('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->join('vendors', 'purchase_orders.vendor_id', '=', 'vendors.id')
            ->leftJoin('branches', 'purchase_orders.branch_id', '=', 'branches.id')
            // Filter to only those POs that have at least one inward gatepass
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('inward_gatepasses')
                    ->whereRaw('inward_gatepasses.purchase_order_id = purchase_orders.id');
            })
            ->when($branchId && $branchId !== 'all', function($q) use ($branchId) {
                return $q->where('purchase_orders.branch_id', $branchId);
            })
            ->when($vendorId && $vendorId !== 'all', function($q) use ($vendorId) {
                return $q->where('purchase_orders.vendor_id', $vendorId);
            })
            ->select(
                'purchase_orders.id as po_id',
                'purchase_orders.po_number',
                'purchase_orders.order_date',
                'vendors.name as vendor_name',
                'branches.name as branch_name',
                'products.item_code',
                'products.item_name',
                DB::raw('GROUP_CONCAT(CONCAT(IFNULL(purchase_order_items.color, "Default"), ": ", purchase_order_items.qty, " / ", purchase_order_items.received_qty) SEPARATOR "||") as color_breakdown'),
                DB::raw('SUM(purchase_order_items.qty) as ordered_qty'),
                DB::raw('SUM(purchase_order_items.received_qty) as received_qty'),
                'purchase_orders.status as po_status'
            )
            ->groupBy(
                'purchase_orders.id',
                'purchase_orders.po_number',
                'purchase_orders.order_date',
                'vendors.name',
                'branches.name',
                'products.item_code',
                'products.item_name',
                'purchase_orders.status'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('purchase_orders.order_date', [$startDate, $endDate]);
        }

        $data = $query->orderBy('purchase_orders.order_date', 'desc')->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function getVendorsByBranch(Request $request)
    {
        $branchId = $request->branch_id;
        $vendors = \App\Models\Vendor::where('branch_id', $branchId)
            ->orderBy('name')
            ->get(['id', 'name']);
            
        return response()->json($vendors);
    }

    public function sale_report()
    {
        // ✅ ERP STANDARD: Auto-select current month date range
        $startDate = now()->startOfMonth()->format('Y-m-d');  // 1st of current month
        $endDate = now()->format('Y-m-d');                     // Today
        
        $user = auth()->user();
        $branches = \App\Models\Branch::orderBy('name')->get();
        
        $customersQuery = \App\Models\Customer::where('status', 'active');
        
        // If not super admin, filter customers by branch
        if ($user && !$user->hasRole('super admin')) {
            $customersQuery->where('branch_id', $user->branch_id);
        }
        
        $customers = $customersQuery->orderBy('customer_name')->get();

        return view('admin_panel.reporting.sale_report', compact('branches', 'customers', 'startDate', 'endDate'));
    }

    /**
     * ✅ IMPROVED SALE REPORT - With Proper Relations & Business Logic
     * 
     * Workflow:
     * 1. Fetch Sales with customer, items, products, and returns relationships
     * 2. Filter by date range (start_date to end_date)
     * 3. For each sale, aggregate:
     *    - Sale header info (invoice, customer, address, etc.)
     *    - Sale items (product wise breakdown with prices, qty, amounts)
     *    - Sale returns (if any)
     * 4. Return structured JSON with all necessary data
     */
    public function fetchsaleReport(Request $request)
    {
        if ($request->ajax()) {
            $start = $request->start_date;
            $end = $request->end_date;

            // ================= BUILD QUERY WITH RELATIONSHIPS =================
            $query = Sale::with([
                'customer',              // Customer details
                'saleItems.product',     // Sale items with product details
                'saleItems.warehouse',   // Warehouse information
                'branch',                // Branch for reporting
            ]);

            // ================= BRANCH-LEVEL ACCESS CONTROL =================
            $user = auth()->user();
            // Default: non-super users only see their branch
            if ($user && ! $user->hasRole('super admin')) {
                $userBranch = $user->branch_id ?? null;
                if ($userBranch) {
                    $query->where('branch_id', $userBranch);
                }
            } else {
                // Super admin: may view all branches. Additionally allow a special
                // permission to grant other users the ability to view other branches.
                // If request includes a `branch_id` filter, apply it (optional).
                if ($request->filled('branch_id')) {
                    $query->where('branch_id', (int) $request->branch_id);
                }
            }

            // ================= APPLY DATE & CUSTOMER FILTER =================
            if ($start && $end) {
                $query->whereBetween(DB::raw('DATE(created_at)'), [$start, $end]);
            }

            if ($request->filled('customer_id') && $request->customer_id !== 'all') {
                $query->where('customer_id', (int) $request->customer_id);
            }

            // ================= GET SALES & FETCH RETURNS =================
            $sales = $query->orderBy('created_at', 'asc')->get();

            // ================= TRANSFORM DATA FOR FRONTEND =================
            $formattedSales = $sales->map(function ($sale) {
                
                // ============= SALE ITEMS AGGREGATION =============
                $saleItems = [];
                $totalQty = 0;
                $totalAmount = 0;

                if ($sale->saleItems && $sale->saleItems->count() > 0) {
                    foreach ($sale->saleItems as $item) {
                        $product = $item->product;
                        $warehouseName = $item->warehouse ? $item->warehouse->warehouse_name : 'Unknown';

                        $saleItems[] = [
                            'product_id' => $item->product_id,
                            'product_name' => $product ? $product->item_name : 'N/A',
                            'product_code' => $product ? $product->item_code : 'N/A',
                            'warehouse' => $warehouseName,
                            'qty' => floatval($item->sales_qty ?? 0),
                            'price' => floatval($item->retail_price ?? 0),
                            'discount_percent' => floatval($item->discount_percent ?? 0),
                            'discount_amount' => floatval($item->discount_amount ?? 0),
                            'amount' => floatval($item->amount ?? 0),
                        ];

                        $totalQty += floatval($item->sales_qty ?? 0);
                        $totalAmount += floatval($item->amount ?? 0);
                    }
                }

                // ============= SALES RETURNS =============
                $returns = SalesReturn::where('sale_id', $sale->id)->get();
                $returnItems = [];
                $totalReturnAmount = 0;

                foreach ($returns as $return) {
                    $returnItems[] = [
                        'reference' => $return->reference ?? 'RET-' . $return->id,
                        'product' => $return->product,
                        'qty' => $return->qty,
                        'total_net' => floatval($return->total_net ?? 0),
                    ];
                    $totalReturnAmount += floatval($return->total_net ?? 0);
                }

                    // ============= FINAL SALE OBJECT =============
                    $customerName = 'Walk-in Customer';
                    if ($sale->customer && !empty($sale->customer->customer_name)) {
                        $customerName = $sale->customer->customer_name;
                        if (!empty($sale->sub_customer)) {
                            $customerName .= ' (' . $sale->sub_customer . ')';
                        }
                    } elseif (!empty($sale->sub_customer)) {
                        $customerName = $sale->sub_customer . ' (Walk-in)';
                    } elseif (!empty($sale->party_type) && ($sale->party_type === 'walking' || $sale->party_type === 'walk_in')) {
                        $customerName = 'Walk-in Customer';
                    }

                    $branchName = $sale->branch ? ($sale->branch->name ?? $sale->branch->branch_name ?? '') : '';

                return [
                    'id' => $sale->id,
                    'invoice_no' => $sale->invoice_no,
                    'manual_invoice' => $sale->manual_invoice,
                    'created_at' => $sale->created_at,
                    'customer_id' => $sale->customer_id,
                    'customer_name' => $customerName,
                    'party_type' => $sale->party_type,
                    'address' => $sale->address,
                    'tel' => $sale->tel,
                    'remarks' => $sale->remarks,
                    // Branch info (for super-admin or when available)
                    'branch_id' => $sale->branch_id ?? null,
                    'branch_name' => $branchName,
                    
                    // ============= SALE HEADER AMOUNTS =============
                    'sub_total1' => floatval($sale->sub_total1 ?? 0),
                    'sub_total2' => floatval($sale->sub_total2 ?? 0),
                    'discount_percent' => floatval($sale->discount_percent ?? 0),
                    'discount_amount' => floatval($sale->discount_amount ?? 0),
                    'total_balance' => floatval($sale->total_balance ?? 0),
                    'total_net' => floatval($sale->total_net ?? 0),
                    
                    // ============= RECEIPT INFO =============
                    'receipt1' => floatval($sale->receipt1 ?? 0),
                    'receipt2' => floatval($sale->receipt2 ?? 0),
                    'final_balance1' => floatval($sale->final_balance1 ?? 0),
                    'final_balance2' => floatval($sale->final_balance2 ?? 0),
                    
                    // ============= SALE ITEMS BREAKDOWN =============
                    'items' => $saleItems,
                    'items_count' => count($saleItems),
                    'total_qty' => $totalQty,
                    'total_items_amount' => $totalAmount,
                    
                    // ============= RETURNS INFO =============
                    'returns' => $returnItems,
                    'returns_count' => count($returnItems),
                    'total_returns_amount' => $totalReturnAmount,
                ];
            });

            return response()->json($formattedSales);
        }

        return view('admin_panel.reporting.sale_report');
    }

    /**
     * ✅ CUSTOMER LEDGER REPORT VIEW - Fresh Load
     * Returns customers with full details for dropdown selection
     */
    public function customer_ledger_report()
    {
        // ✅ ERP STANDARD: Auto-select current month date range
        $startDate = now()->startOfMonth()->format('Y-m-d');  // 1st of current month
        $endDate = now()->format('Y-m-d');                     // Today
        
        // Branch list and customers depend on user role/permissions
        $user = auth()->user();
        $branches = \App\Models\Branch::orderBy('name')->get();

        if ($user && $user->hasRole('super admin')) {
            // Super admin: show branches; customers will be loaded per-branch in UI
            $customers = collect();
        } else {
            // Non-super: only customers of user's branch
            $userBranchId = $user->branch_id ?? null;
            $customers = Customer::select('id', 'customer_name', 'customer_type', 'opening_balance', 'credit_limit', 'address', 'mobile')
                ->where('status', 'active')
                ->when($userBranchId, function ($q) use ($userBranchId) {
                    $q->where('branch_id', $userBranchId);
                })
                ->where('customer_type', 'credit')
                ->get();
        }

        return view('admin_panel.reporting.customer_ledger_report', compact('branches', 'customers', 'startDate', 'endDate'));
    }

    /**
     * ✅ FETCH CUSTOMER LEDGER - Proper Business Logic with Sales & Receipts
     * 
     * Workflow:
     * 1. Get customer with full details
     * 2. Determine opening balance (from latest ledger entry before start_date)
     * 3. Fetch all sales in date range with invoice numbers
     * 4. Fetch all receipts_voucher (payments) with account details to determine payment mode
     * 5. Merge ledger entries with sales/receipts data
     * 6. Calculate running balance
     * 7. Return formatted ledger with proper transaction descriptions
     */
    public function fetch_customer_ledger(Request $request)
    {
        $user = auth()->user();

        $customerId = $request->customer_id;
        $start = $request->start_date;
        $end = $request->end_date . " 23:59:59";
        $endDate = substr($end, 0, 10);

        // ================= FETCH CUSTOMER DETAILS =================
        $customer = Customer::findOrFail($customerId);

        // ================= BRANCH-LEVEL AUTHORIZATION =================
        $custBranchId = $customer->branch_id ?? null;

        $allowed = false;
        if ($user && $user->hasRole('super admin')) {
            $allowed = true;
        } else {
            // Owner of branch can view
            if ($user && ($user->branch_id ?? null) && $custBranchId && $user->branch_id == $custBranchId) {
                $allowed = true;
            }

            // Users granted base permission can view other branches
            if (! $allowed && $user && $user->can('report.customer.ledger.branch.view')) {
                $allowed = true;
            }

            // Per-branch grant
            if (! $allowed && $user && $custBranchId && $user->can('report.customer.ledger.branch.view.' . $custBranchId)) {
                $allowed = true;
            }
        }

        if (! $allowed) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // ================= DETERMINE OPENING BALANCE =================
        $openingBalance = $this->getCustomerOpeningBalance($customerId, $start);

        // ================= FETCH SALES IN DATE RANGE =================
        $salesMap = [];
        $sales = Sale::where('customer_id', $customerId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$start, $endDate])
            ->select('id', 'invoice_no', 'total_net', 'created_at')
            ->get();

        foreach ($sales as $sale) {
            $key = $sale->created_at->format('Y-m-d H:i:s');
            $salesMap[$key] = [
                'invoice_no' => $sale->invoice_no,
                'total_net' => floatval($sale->total_net),
            ];
        }

        // ================= FETCH RECEIPTS (PAYMENTS) WITH ACCOUNT DETAILS =================
        $paymentsMap = [];
        $receiptsQ = ReceiptsVoucher::whereBetween(DB::raw('DATE(receipt_date)'), [$start, $endDate])
            ->orderBy('receipt_date', 'asc');
        \App\Services\VoucherService::scopePartyQuery($receiptsQ, $customerId, 'party_id');
        $receipts = $receiptsQ->get();

        foreach ($receipts as $receipt) {
            $amt = \App\Services\VoucherService::getPartyAmount($receipt, $customerId, 'customer');
            if ($amt <= 0) {
                continue;
            }

            $dateKey = $receipt->receipt_date instanceof \Carbon\Carbon 
                ? $receipt->receipt_date->format('Y-m-d H:i:s')
                : \Carbon\Carbon::parse($receipt->receipt_date)->format('Y-m-d H:i:s');

            // ============= DETERMINE PAYMENT MODE =============
            $paymentMode = "Cash"; // Default
            $accountName = "-";

            // Get account details to determine payment mode
            if ($receipt->row_account_id) {
                $accIds = \App\Services\VoucherService::safeDecodeArray($receipt->row_account_id);
                $firstAccId = $accIds[0] ?? $receipt->row_account_id;
                $account = Account::find($firstAccId);
                if ($account) {
                    $accountHead = $account->head;
                    // Check if account head is a bank account
                    if ($accountHead && strtolower($accountHead->name) === 'bank') {
                        $paymentMode = "Bank";
                        $accountName = $account->title ?? "Bank A/c";
                    } else {
                        $paymentMode = "Cash";
                    }
                }
            }

            $paymentsMap[$dateKey] = [
                'amount' => $amt,
                'reference' => $receipt->reference_no ?? "-",
                'payment_mode' => $paymentMode,
                'account_name' => $accountName,
            ];
        }

        // ================= FETCH LEDGER ENTRIES IN DATE RANGE =================
        $ledgerEntries = CustomerLedger::where('customer_id', $customerId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$start, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        // ================= TRANSFORM LEDGER ENTRIES FOR FRONTEND =================
        $transactions = $ledgerEntries->map(function ($entry) use ($salesMap, $paymentsMap) {
            // Calculate debit and credit from the balance changes
            $difference = floatval($entry->closing_balance) - floatval($entry->previous_balance);

            // If difference is positive = DEBIT (customer owes more)
            // If difference is negative = CREDIT (customer paid/balance reduced)
            $debit = $difference > 0 ? $difference : 0;
            $credit = $difference < 0 ? abs($difference) : 0;

            // ============= DETERMINE TRANSACTION TYPE & INVOICE =============
            $description = "Ledger Entry";
            $invoice = "-";

            // Check if it matches a sale
            $dateKey = $entry->created_at->format('Y-m-d H:i:s');
            if (isset($salesMap[$dateKey])) {
                $saleData = $salesMap[$dateKey];
                $description = "Sale";
                $invoice = $saleData['invoice_no'] ?? "-";
            }
            // Check if it matches a payment/receipt
            elseif (isset($paymentsMap[$dateKey])) {
                $paymentData = $paymentsMap[$dateKey];
                // Build description with payment mode and account name
                if ($paymentData['payment_mode'] === 'Bank') {
                    $description = "Payment Received - Bank ({$paymentData['account_name']})";
                } else {
                    $description = "Payment Received - Cash";
                }
                $invoice = $paymentData['reference'];
            }

            return [
                'date' => $entry->created_at->format('Y-m-d'),
                'invoice' => $invoice,
                'description' => $description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => floatval($entry->closing_balance),
            ];
        });

        // ================= RETURN FORMATTED RESPONSE =================
        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'customer_name' => $customer->customer_name,
                'customer_type' => $customer->customer_type,
                'address' => $customer->address,
                'mobile' => $customer->mobile,
                'email' => $customer->email_address,
                'credit_limit' => floatval($customer->credit_limit ?? 0),
            ],
            'opening_balance' => $openingBalance,
            'transactions' => $transactions->toArray(),
        ]);
    }

    /**
     * Return customers for a given branch (used by AJAX in customer ledger view)
     */
    public function customersByBranch(Request $request)
    {
        try {
            $branchId = $request->branch_id ?? null;
            $query = Customer::query();

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            // Exclude inactive only if status is explicitly inactive
            $query->where(function($q) {
                $q->whereNull('status')
                  ->orWhere('status', '!=', 'inactive')
                  ->orWhere('status', 'active')
                  ->orWhere('status', '1');
            });

            $customers = $query->select('id', 'customer_name', 'customer_type', 'credit_limit', 'address', 'mobile')
                ->orderBy('customer_name')
                ->get();

            return response()->json($customers);
        } catch (\Exception $e) {
            \Log::error('customersByBranch error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ ENHANCED CUSTOMER LEDGER WITH PRODUCT DETAILS
     * Returns ledger with: Date, V NO, Bill, Description, Qty, Rate, Debit, Credit, Total
     * This is for the customer_leger_new.blade.php view - International ERP Standard format
     */
    public function fetch_customer_ledger_detailed(Request $request)
    {
        $user = auth()->user();
        $customerId = $request->customer_id;
        $start = $request->start_date;
        $end = $request->end_date . " 23:59:59";
        $endDate = substr($end, 0, 10);

        // ✅ Authorization
        $customer = Customer::findOrFail($customerId);
        $custBranchId = $customer->branch_id ?? null;
        $allowed = false;

        if ($user && $user->hasRole('super admin')) {
            $allowed = true;
        } else {
            if ($user && ($user->branch_id ?? null) && $custBranchId && $user->branch_id == $custBranchId) {
                $allowed = true;
            }
            if (! $allowed && $user && $user->can('report.customer.ledger.branch.view')) {
                $allowed = true;
            }
            if (! $allowed && $user && $custBranchId && $user->can('report.customer.ledger.branch.view.' . $custBranchId)) {
                $allowed = true;
            }
        }

        if (! $allowed) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // ✅ Get opening balance
        $openingBalance = $this->getCustomerOpeningBalance($customerId, $start);

        // ✅ Fetch all sales with their items (for Qty, Rate, Bill)
        $sales = Sale::where('customer_id', $customerId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$start, $endDate])
            ->with(['saleItems.product'])
            ->select('id', 'invoice_no', 'bill_number', 'total_net', 'created_at')
            ->get();

        $salesMap = [];
        foreach ($sales as $sale) {
            $key = $sale->created_at->format('Y-m-d H:i:s');
            $salesMap[$key] = [
                'invoice_no' => $sale->invoice_no,
                'bill_number' => $sale->bill_number ?? '-',
                'total_net' => floatval($sale->total_net),
                'items' => $sale->saleItems ? $sale->saleItems->map(function($item) {
                    return [
                        'product_name' => $item->product->item_name ?? '-',
                        'quantity' => (float) ($item->sales_qty ?? 0),
                        'rate' => (float) ($item->sales_price ?? 0),
                    ];
                })->toArray() : [],
            ];
        }

        // ✅ Fetch receipts (payments) with account details
        $paymentsMap = [];
        $receiptsQ = ReceiptsVoucher::whereBetween(DB::raw('DATE(receipt_date)'), [$start, $endDate])
            ->orderBy('receipt_date', 'asc');
        \App\Services\VoucherService::scopePartyQuery($receiptsQ, $customerId, 'party_id');
        $receipts = $receiptsQ->get();

        foreach ($receipts as $receipt) {
            $amt = \App\Services\VoucherService::getPartyAmount($receipt, $customerId, 'customer');
            if ($amt <= 0) {
                continue;
            }

            $dateKey = $receipt->receipt_date instanceof \Carbon\Carbon 
                ? $receipt->receipt_date->format('Y-m-d H:i:s')
                : \Carbon\Carbon::parse($receipt->receipt_date)->format('Y-m-d H:i:s');

            $paymentMode = "Cash";
            $accountName = "-";

            if ($receipt->row_account_id) {
                $accIds = \App\Services\VoucherService::safeDecodeArray($receipt->row_account_id);
                $firstAccId = $accIds[0] ?? $receipt->row_account_id;
                $account = Account::find($firstAccId);
                if ($account) {
                    $accountHead = $account->head;
                    if ($accountHead && strtolower($accountHead->name) === 'bank') {
                        $paymentMode = "Bank";
                        $accountName = $account->title ?? "Bank A/c";
                    }
                }
            }

            $paymentsMap[$dateKey] = [
                'amount' => floatval($amt),
                'reference' => $receipt->reference_no ?? "-",
                'voucher_no' => $receipt->receipt_no ?? $receipt->rvid ?? "REC-" . $receipt->id,
                'payment_mode' => $paymentMode,
                'account_name' => $accountName,
            ];
        }

        // ✅ Fetch ledger entries
        $ledgerEntries = CustomerLedger::where('customer_id', $customerId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$start, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        // ✅ Transform ledger entries with enhanced details
        $transactions = $ledgerEntries->map(function ($entry) use ($salesMap, $paymentsMap) {
            $difference = floatval($entry->closing_balance) - floatval($entry->previous_balance);
            $debit = $difference > 0 ? $difference : 0;
            $credit = $difference < 0 ? abs($difference) : 0;

            $dateKey = $entry->created_at->format('Y-m-d H:i:s');
            
            $voucherNo = "-";
            $billNumber = "-";
            $description = "Ledger Entry";
            $qty = null;
            $rate = null;
            $itemName = "-";

            // Check if it matches a sale
            if (isset($salesMap[$dateKey])) {
                $saleData = $salesMap[$dateKey];
                $voucherNo = $saleData['invoice_no'] ?? "-";
                $billNumber = $saleData['bill_number'] ?? "-";
                $description = "SALE";
                
                // Get first item's details (or combine if multiple items)
                if (!empty($saleData['items'])) {
                    $firstItem = $saleData['items'][0];
                    $itemName = $firstItem['product_name'];
                    $qty = $firstItem['quantity'];
                    $rate = $firstItem['rate'];
                }
            }
            // Check if it matches a payment
            elseif (isset($paymentsMap[$dateKey])) {
                $paymentData = $paymentsMap[$dateKey];
                $voucherNo = $paymentData['voucher_no'];
                $billNumber = $paymentData['reference'];
                if ($paymentData['payment_mode'] === 'Bank') {
                    $description = "PAYMENT - BANK (" . $paymentData['account_name'] . ")";
                } else {
                    $description = "PAYMENT - CASH";
                }
            }

            return [
                'date' => $entry->created_at->format('d-m-y'),
                'vno' => $voucherNo,
                'bill' => $billNumber,
                'description' => $description,
                'item_name' => $itemName,
                'qty' => $qty,
                'rate' => $rate,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => floatval($entry->closing_balance),
            ];
        });

        // ✅ Return comprehensive response
        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->customer_name,
                'type' => $customer->customer_type,
                'address' => $customer->address ?? '-',
                'mobile' => $customer->mobile ?? '-',
                'email' => $customer->email_address ?? '-',
                'credit_limit' => floatval($customer->credit_limit ?? 0),
                'opening_balance' => $openingBalance,
            ],
            'period' => [
                'start' => $start,
                'end' => $endDate,
            ],
            'opening_balance' => $openingBalance,
            'transactions' => $transactions->toArray(),
        ]);
    }

    /**
     * ═══════════════════════════════════════════════════════════════════════════
     * 📊 STOCK HOLD AUDIT REPORT
     * ═══════════════════════════════════════════════════════════════════════════
     * 
     * Shows complete audit trail for draft_posted sales:
     * - How much stock was available when DC was created
     * - How much was delivered
     * - How much remained in inventory
     * 
     * Perfect for ERP audit and compliance tracking
     */
    public function stockHoldAudit(Request $request)
    {
        try {
            // ✅ Fetch all stock holds (with filters)
            // ✅ INTERNATIONAL ERP STANDARD: Eager load product relationships for data integrity
            // ✅ LEFT JOIN with outward_gatepasses to show if Gate Pass created
            $query = \App\Models\StockHold::with([
                'sale.customer',   // ✅ Load sale's customer for regular customer names
                'sale.booking',    // Load booking to get walking customer names (sub_customer)
                'sale.saleItems',  // ✅ Load sale items to calculate total qty for invoice
                'product.brand',   // Fetch brand relationship to avoid N+1
                'product.unit',    // Fetch unit relationship to avoid N+1
                'warehouse',
                'customer',
                'creator'
            ])
            ->leftJoin('outward_gatepasses', 'stock_holds.warehouse_order_id', '=', 'outward_gatepasses.order_id')
            ->select(
                'stock_holds.*',
                \DB::raw('COALESCE(outward_gatepasses.id, 0) as has_gatepass')  // ✅ 1 if GP exists, 0 if not
            )
            ->orderBy('stock_holds.created_at', 'desc');

            // Filter by invoice if provided
            if ($request->has('invoice_no') && !empty($request->invoice_no)) {
                $query->where('invoice_no', 'like', '%' . $request->invoice_no . '%');
            }

            // Filter by DC if provided
            if ($request->has('dc_no') && !empty($request->dc_no)) {
                $query->where('dc_no', $request->dc_no);
            }

            // Filter by customer
            if ($request->has('customer_id') && !empty($request->customer_id)) {
                $query->where('customer_id', $request->customer_id);
            }

            // Filter by warehouse
            if ($request->has('warehouse_id') && !empty($request->warehouse_id)) {
                $query->where('warehouse_id', $request->warehouse_id);
            }

            // Filter by date range
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $stockHolds = $query->paginate(50);

            // ✅ INTERNATIONAL ERP STANDARD: Eager loaded relationships
            // Formatting will be applied in blade template to preserve pagination

            // ✅ Summary statistics
            $totalAvailableQty = \App\Models\StockHold::sum('available_qty');
            $totalDeliverQty = \App\Models\StockHold::sum('deliver_qty');
            $totalRemainingQty = \App\Models\StockHold::sum('remaining_qty');
            $totalValue = \App\Models\StockHold::selectRaw('SUM(deliver_qty * unit_price) as total')
                ->first()->total ?? 0;

            // ✅ Get filters for dropdowns
            $customers = Customer::orderBy('customer_name')->get();
            $warehouses = Warehouse::orderBy('warehouse_name')->get();

            return view('admin_panel.reporting.stock_hold_audit', [
                'stockHolds' => $stockHolds,
                'customers' => $customers,
                'warehouses' => $warehouses,
                'totalAvailableQty' => $totalAvailableQty,
                'totalDeliverQty' => $totalDeliverQty,
                'totalRemainingQty' => $totalRemainingQty,
                'totalValue' => $totalValue,
                'filters' => $request->all(),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading stock hold audit: ' . $e->getMessage());
        }
    }

    /**
     * Export stock hold audit as Excel
     */
    public function stockHoldAuditExport(Request $request)
    {
        try {
            $query = \App\Models\StockHold::with(['product', 'warehouse', 'customer', 'sale.booking']);

            // Apply same filters
            if ($request->has('invoice_no') && !empty($request->invoice_no)) {
                $query->where('invoice_no', 'like', '%' . $request->invoice_no . '%');
            }
            if ($request->has('dc_no') && !empty($request->dc_no)) {
                $query->where('dc_no', $request->dc_no);
            }
            if ($request->has('customer_id') && !empty($request->customer_id)) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->has('warehouse_id') && !empty($request->warehouse_id)) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $stockHolds = $query->get();

            // Create CSV
            $csv = "Invoice,DC No,Product,Warehouse,Customer,Available Qty,Deliver Qty,Remaining Qty,Unit Price,Total Value,Created Date\n";
            foreach ($stockHolds as $hold) {
                $totalValue = ($hold->deliver_qty ?? 0) * ($hold->unit_price ?? 0);
                $csv .= implode(',', [
                    $hold->invoice_no,
                    $hold->dc_no,
                    $hold->product_name,
                    $hold->warehouse?->warehouse_name ?? 'N/A',
                    $hold->customer?->customer_name ?? 'N/A',
                    $hold->available_qty,
                    $hold->deliver_qty,
                    $hold->remaining_qty,
                    $hold->unit_price,
                    number_format($totalValue, 2),
                    $hold->created_at->format('Y-m-d H:i'),
                ]) . "\n";
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="stock_hold_audit_' . now()->format('Y-m-d_H-i') . '.csv"',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting stock hold audit: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Salesman Performance Report View
     */
    public function salesman_performance_report()
    {
        $user = Auth::user();
        $isSuper = $user->hasRole('super admin');
        $userBranchId = $user->branch_id ?? null;

        // ✅ ERP Standard: Branch List for Super Admin
        $branches = $isSuper ? Branch::all() : Branch::where('id', $userBranchId)->get();

        // ✅ If NOT super-admin, fetch only salesmen for their branch
        if (!$isSuper && $userBranchId) {
            $salesmanIds = Sale::where('branch_id', $userBranchId)
                ->whereNotNull('salesman_id')
                ->distinct()
                ->pluck('salesman_id');
                
            $salesmen = \App\Models\SalesOfficer::whereIn('id', $salesmanIds)
                ->orderBy('name')
                ->get();
        } else {
            $salesmen = \App\Models\SalesOfficer::orderBy('name')->get();
        }

        return view('admin_panel.reporting.salesman_performance', compact('branches', 'isSuper', 'salesmen', 'userBranchId'));
    }

    /**
     * ✅ Fetch Salesmen by Branch (AJAX)
     */
    public function salesmenByBranch(Request $request)
    {
        $branchId = $request->branch_id;
        
        if (!$branchId) {
            $salesmen = \App\Models\SalesOfficer::orderBy('name')->get();
        } else {
            // ✅ ERP Standard: Show salesmen who have actually made sales in this branch
            $salesmanIds = Sale::where('branch_id', $branchId)
                ->whereNotNull('salesman_id')
                ->distinct()
                ->pluck('salesman_id');
                
            $salesmen = \App\Models\SalesOfficer::whereIn('id', $salesmanIds)
                ->orderBy('name')
                ->get();
        }

        return response()->json($salesmen);
    }

    /**
     * ✅ Fetch Salesman Performance Data
     */
    public function fetch_salesman_performance(Request $request)
    {
        $user = Auth::user();
        $isSuper = $user->hasRole('super admin');
        $branchId = $isSuper ? $request->branch_id : $user->branch_id;

        $startDate = $request->start_date ? $request->start_date . ' 00:00:00' : now()->startOfMonth();
        $endDate = $request->end_date ? $request->end_date . ' 23:59:59' : now()->endOfMonth();

        try {
            $query = DB::table('sales')
                ->leftJoin('sales_officers', 'sales.salesman_id', '=', 'sales_officers.id')
                ->leftJoin('branches', 'sales.branch_id', '=', 'branches.id')
                ->select(
                    DB::raw('IFNULL(sales_officers.name, "Direct Sale (No Salesman)") as salesman_name'),
                    'branches.name as branch_name',
                    DB::raw('COUNT(sales.id) as total_invoices'),
                    DB::raw('SUM(sales.total_net) as total_amount'),
                    DB::raw('DATE_FORMAT(sales.created_at, "%M %Y") as month_year')
                )
                ->whereBetween('sales.created_at', [$startDate, $endDate]);

            if ($branchId) {
                $query->where('sales.branch_id', $branchId);
            }

            $results = $query->groupBy('salesman_name', 'branches.name', 'sales.branch_id', 'month_year')
                ->orderBy('total_amount', 'DESC')
                ->get();

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * ✅ Fetch Detailed Salesman Ledger (Transaction List)
     */
    public function fetch_salesman_ledger(Request $request)
    {
        $user = Auth::user();
        $isSuper = $user->hasRole('super admin');
        $branchId = $isSuper ? $request->branch_id : $user->branch_id;

        $salesmanId = $request->salesman_id;
        $start = $request->start_date ? $request->start_date . ' 00:00:00' : now()->startOfMonth();
        $end = $request->end_date ? $request->end_date . ' 23:59:59' : now()->endOfMonth();

        try {
            $query = Sale::with(['customer', 'branch'])
                ->whereBetween('created_at', [$start, $end]);

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            if ($salesmanId === 'direct') {
                $query->whereNull('salesman_id');
            } elseif ($salesmanId) {
                $query->where('salesman_id', $salesmanId);
            }

            $sales = $query->orderBy('created_at', 'ASC')->get();

            $formatted = $sales->map(function ($s) {
                return [
                    'date' => $s->created_at->format('d-M-Y'),
                    'invoice_no' => $s->invoice_no,
                    'customer' => $s->customer ? $s->customer->customer_name : ($s->sub_customer ?? 'Walking Customer'),
                    'total_amount' => (float)$s->total_net,
                    'branch' => $s->branch ? $s->branch->name : '-',
                ];
            });

            return response()->json($formatted);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ Fetch Item-wise Salesman Performance / Sales & Profit Breakdown
     */
    public function fetch_salesman_items(Request $request)
    {
        $user = Auth::user();
        $isSuper = $user->hasRole('super admin');
        $branchId = $isSuper ? $request->branch_id : $user->branch_id;

        $salesmanId = $request->salesman_id;
        $start = $request->start_date ? $request->start_date . ' 00:00:00' : now()->startOfMonth();
        $end = $request->end_date ? $request->end_date . ' 23:59:59' : now()->endOfMonth();

        try {
            $query = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
                ->leftJoin('product_variants as pv', 'sale_items.variant_id', '=', 'pv.id')
                ->whereBetween('sales.created_at', [$start, $end])
                ->where(function ($q) {
                    $q->whereNull('sales.status')->orWhere('sales.status', '!=', 'cancelled');
                });

            if ($branchId) {
                $query->where('sales.branch_id', $branchId);
            }

            if ($salesmanId === 'direct') {
                $query->whereNull('sales.salesman_id');
            } elseif ($salesmanId) {
                $query->where('sales.salesman_id', $salesmanId);
            }

            $items = $query->select(
                'products.id as product_id',
                'products.item_code',
                'products.item_name',
                'products.wholesale_price',
                'products.price as product_price',
                'sale_items.variant_id',
                'pv.variant_name',
                'pv.color as variant_color',
                'pv.wholesale_price as variant_wholesale_price',
                DB::raw('SUM(sale_items.sales_qty) as total_qty'),
                DB::raw('SUM(sale_items.amount) as total_sale_amount')
            )
            ->groupBy(
                'products.id',
                'products.item_code',
                'products.item_name',
                'products.wholesale_price',
                'products.price',
                'sale_items.variant_id',
                'pv.variant_name',
                'pv.color',
                'pv.wholesale_price'
            )
            ->orderBy('total_sale_amount', 'DESC')
            ->get();

            $productIds = $items->pluck('product_id')->filter()->unique()->toArray();

            // Fetch latest purchase prices
            $purchasePrices = [];
            if (!empty($productIds)) {
                $purchasePrices = DB::table('purchase_items')
                    ->whereIn('product_id', $productIds)
                    ->where('price', '>', 0)
                    ->orderBy('id', 'desc')
                    ->get()
                    ->unique('product_id')
                    ->pluck('price', 'product_id')
                    ->toArray();
            }

            $formatted = $items->map(function ($it) use ($purchasePrices) {
                $qty = (float) $it->total_qty;
                $saleAmount = (float) $it->total_sale_amount;

                // Purchasing Rate determination
                $purchasingRate = 0.0;
                if (!empty($it->variant_id) && !empty($it->variant_wholesale_price) && (float)$it->variant_wholesale_price > 0) {
                    $purchasingRate = (float) $it->variant_wholesale_price;
                } elseif (!empty($it->product_id) && isset($purchasePrices[$it->product_id])) {
                    $purchasingRate = (float) $purchasePrices[$it->product_id];
                } elseif (!empty($it->wholesale_price) && (float)$it->wholesale_price > 0) {
                    $purchasingRate = (float) $it->wholesale_price;
                } elseif (!empty($it->product_price) && (float)$it->product_price > 0) {
                    $purchasingRate = (float) $it->product_price;
                }

                $totalCost = round($qty * $purchasingRate, 2);
                $netProfit = round($saleAmount - $totalCost, 2);

                $itemName = $it->item_name ?? 'N/A';
                if (!empty($it->variant_color)) {
                    $itemName .= " ({$it->variant_color})";
                } elseif (!empty($it->variant_name)) {
                    $itemName .= " ({$it->variant_name})";
                }

                return [
                    'item_code'       => $it->item_code ?? '-',
                    'item_name'       => $itemName,
                    'quantity'        => $qty,
                    'sale_price'      => $saleAmount,
                    'purchasing_rate' => $purchasingRate,
                    'total_cost'      => $totalCost,
                    'net_profit'      => $netProfit,
                ];
            });

            return response()->json($formatted);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ NEW: Local Purchase Report View
     */
    public function local_purchase_report()
    {
        $user = auth()->user();
        if ($user->hasRole('super admin')) {
            $branches = Branch::orderBy('name')->get();
            $shops = DB::table('purchases')
                ->where(function($q) {
                    $q->where('purchase_type', 'local')
                      ->orWhereNull('vendor_id');
                })
                ->whereNotNull('vendor_name')
                ->where('vendor_name', '!=', '')
                ->distinct()
                ->orderBy('vendor_name')
                ->pluck('vendor_name');
        } else {
            $branches = Branch::where('id', $user->branch_id)->get();
            $shops = DB::table('purchases')
                ->where(function($q) {
                    $q->where('purchase_type', 'local')
                      ->orWhereNull('vendor_id');
                })
                ->where('branch_id', $user->branch_id)
                ->whereNotNull('vendor_name')
                ->where('vendor_name', '!=', '')
                ->distinct()
                ->orderBy('vendor_name')
                ->pluck('vendor_name');
        }

        $startDate = date('Y-m-01');
        $endDate   = date('Y-m-d');

        // ✅ Get account heads and accounts for payment modal
        $accountHeads = \App\Models\AccountHead::whereIn('status', [1, '1', 'active'])->orderBy('name')->get();
        $accountQuery = \App\Models\Account::with('head')->whereIn('status', [1, '1', 'active']);
        if (!$user->hasRole('super admin')) {
            $accountQuery->where('branch_id', $user->branch_id);
        }
        $bankAccounts = $accountQuery->orderBy('title')->get();

        return view('admin_panel.reporting.local_purchase_report', compact('branches', 'shops', 'startDate', 'endDate', 'bankAccounts', 'accountHeads'));
    }

    /**
     * ✅ NEW: Fetch Local Shops by Branch (AJAX)
     */
    public function shopsByBranch(Request $request)
    {
        $user = auth()->user();
        $branchId = $request->branch_id;

        $query = DB::table('purchases')
            ->where(function($q) {
                $q->where('purchase_type', 'local')
                  ->orWhereNull('vendor_id');
            })
            ->whereNotNull('vendor_name')
            ->where('vendor_name', '!=', '');

        if (!$user->hasRole('super admin')) {
            $query->where('branch_id', $user->branch_id);
        } elseif (!empty($branchId)) {
            $query->where('branch_id', $branchId);
        }

        $shops = $query->distinct()->orderBy('vendor_name')->pluck('vendor_name');

        return response()->json($shops);
    }

    /**
     * ✅ NEW: Fetch Local Purchase Report Data (AJAX)
     */
    public function fetch_local_purchase_report(Request $request)
    {
        $user      = auth()->user();
        $start     = $request->start_date;
        $end       = $request->end_date;
        $branchId  = $request->branch_id;
        $shopName  = $request->shop_name;

        $query = DB::table('purchases')
            ->where(function($q) {
                $q->where('purchase_type', 'local')
                  ->orWhereNull('vendor_id');
            });

        if (!empty($shopName)) {
            $query->where('vendor_name', $shopName);
        }

        if ($start && $end) {
            $query->whereBetween(DB::raw('DATE(purchase_date)'), [$start, $end]);
        }

        if (!$user->hasRole('super admin')) {
            $query->where('branch_id', $user->branch_id);
        } elseif (!empty($branchId)) {
            $query->where('branch_id', $branchId);
        }

        $purchases = $query->select(
                'id',
                'invoice_no',
                'vendor_name',
                'purchase_date',
                'net_amount',
                'paid_amount',
                'due_amount',
                'branch_id'
            )
            ->orderBy('purchase_date', 'asc')
            ->get();

        $branches = Branch::pluck('name', 'id');
        
        $results = $purchases->map(function($p) use ($branches) {
            return [
                'id'            => $p->id,
                'invoice_no'    => $p->invoice_no,
                'shop_name'     => $p->vendor_name ?? 'Local Market',
                'date'          => date('d-m-Y', strtotime($p->purchase_date)),
                'branch'        => $branches[$p->branch_id] ?? '-',
                'net_amount'    => (float)$p->net_amount,
                'paid_amount'   => (float)$p->paid_amount,
                'due_amount'    => (float)$p->due_amount,
                'status'        => $p->due_amount <= 0 ? 'Paid' : ($p->paid_amount > 0 ? 'Partial' : 'Due'),
            ];
        });

        return response()->json([
            'data' => $results,
            'summary' => [
                'total_net'  => (float)$results->sum('net_amount'),
                'total_paid' => (float)$results->sum('paid_amount'),
                'total_due'  => (float)$results->sum('due_amount'),
            ]
        ]);
    }

    /**
     * ✅ Render Product Stock Movement Ledger (Cardex) View
     */
    public function product_ledger(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super admin');

        $selectedBranchId = null;
        if ($isSuperAdmin) {
            $branches = Branch::orderBy('name')->get();
            $selectedBranchId = $request->filled('branch_id') ? $request->branch_id : 'all';
        } else {
            $branches = $user->branch_id ? Branch::where('id', $user->branch_id)->get() : collect();
            $selectedBranchId = $user->branch_id;
        }

        // Warehouses list
        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $warehouses = Warehouse::whereHas('branches', function ($q) use ($selectedBranchId) {
                $q->where('branches.id', $selectedBranchId);
            })->orderBy('warehouse_name')->get();
        } else {
            $warehouses = Warehouse::orderBy('warehouse_name')->get();
        }

        // Products for searchable dropdown with variants
        $products = Product::with(['unit', 'brand', 'category_relation', 'variants'])
            ->orderBy('item_name')
            ->get(['id', 'item_code', 'item_name', 'unit_id', 'brand_id', 'category_id', 'wholesale_price', 'price', 'initial_stock', 'has_variants']);

        return view('admin_panel.reporting.product_ledger', compact(
            'branches',
            'warehouses',
            'products',
            'selectedBranchId',
            'isSuperAdmin'
        ));
    }

    /**
     * ✅ Fetch Product Stock Movement Ledger Data (AJAX)
     * Full ERP Cardex: Opening Stock, Chronological Transactions, Running Balance & Valuation
     */
    public function fetch_product_ledger(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super admin');

        $productIdRaw = $request->input('product_id');
        $productId    = null;
        $variantId    = null;
        if (!empty($productIdRaw)) {
            if (strpos($productIdRaw, ':') !== false) {
                [$productId, $variantId] = explode(':', $productIdRaw);
                $productId = (int) $productId;
                $variantId = (int) $variantId;
            } else {
                $productId = (int) $productIdRaw;
            }
        }
        $branchId    = $request->input('branch_id', 'all');
        $warehouseId = $request->input('warehouse_id', 'all');
        $startDate   = $request->input('start_date');
        $endDate     = $request->input('end_date');

        if (!$productId) {
            return response()->json(['error' => 'Please select a valid product.'], 422);
        }

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Start Date and End Date are required.'], 422);
        }

        // Security / Branch isolation
        if (!$isSuperAdmin && $user) {
            $branchId = (int) $user->branch_id;
        }

        $product = Product::with(['unit', 'brand', 'category_relation', 'sub_category_relation', 'variants'])->find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        $variant = null;
        if ($variantId && $product->has_variants && $product->variants) {
            $variant = $product->variants->firstWhere('id', $variantId);
        }

        $costRate = (float) (($variant && (float)$variant->wholesale_price > 0) ? $variant->wholesale_price : ($product->wholesale_price ?? 0));
        $retailRate = (float) (($variant && (float)$variant->retail_price > 0) ? $variant->retail_price : ($product->price ?? 0));
        $ppp = (float) ($product->piece_per_pack > 1 ? $product->piece_per_pack : 1);
        $isCartonProd = ($ppp > 1);
        $costPerPiece = $isCartonProd ? ($costRate / $ppp) : $costRate;
        $retailPerPiece = $isCartonProd ? ($retailRate / $ppp) : $retailRate;
        $startDt = $startDate . ' 00:00:00';
        $endDt = $endDate . ' 23:59:59';

        // ══════════════════════════════════════════════════════════════════
        // 1. EXACT OPENING STOCK CALCULATION (Transactions before start_date)
        // ══════════════════════════════════════════════════════════════════

        // A. Initial Stock entries recorded before start date
        $openQuery = DB::table('stock_movements')
            ->where('product_id', $productId)
            ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ'])
            ->where('created_at', '<', $startDt);
        if ($variantId) {
            $openQuery->where('variant_id', $variantId);
        }
        if ($branchId !== 'all' && !empty($branchId)) {
            $openQuery->where('branch_id', $branchId);
        }
        $openingInitial = (float) $openQuery->sum('qty');

        // Fallback: If no stock_movements exist at all, check warehouse_stocks or initial_stock
        if ($openingInitial == 0) {
            $hasAnyMovement = DB::table('stock_movements')->where('product_id', $productId)
                ->when($variantId, fn($q) => $q->where('variant_id', $variantId))
                ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ'])->exists();
            if (!$hasAnyMovement) {
                if ($variantId) {
                    $wsAlloc = DB::table('warehouse_stocks')
                        ->where('product_id', $productId)
                        ->where('variant_id', $variantId)
                        ->when($branchId !== 'all' && !empty($branchId), fn($q) => $q->where('branch_id', $branchId))
                        ->when($warehouseId !== 'all' && !empty($warehouseId), fn($q) => $q->where('warehouse_id', $warehouseId));
                    if ($product->created_at < $startDt) {
                        $openingInitial = (float) $wsAlloc->sum('quantity');
                    }
                } elseif ((float)$product->initial_stock > 0 && $product->created_at < $startDt) {
                    if ($branchId === 'all' || empty($branchId) || $product->branch_id == $branchId) {
                        $openingInitial = (float) $product->initial_stock;
                    }
                }
            }
        }

        // B. Purchases before start_date
        $purBeforeQuery = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('purchase_items.product_id', $productId)
            ->whereNull('purchases.deleted_at')
            ->where('purchases.created_at', '<', $startDt);
        if ($variantId) {
            $purBeforeQuery->where('purchase_items.variant_id', $variantId);
        }
        if ($branchId !== 'all' && !empty($branchId)) {
            $purBeforeQuery->where('purchases.branch_id', $branchId);
        }
        if ($warehouseId !== 'all' && !empty($warehouseId)) {
            $purBeforeQuery->where(function ($q) use ($warehouseId) {
                $q->where('purchase_items.warehouse_id', $warehouseId)
                  ->orWhere('purchases.warehouse_id', $warehouseId);
            });
        }
        $purchasesBefore = 0;
        foreach ($purBeforeQuery->select('purchase_items.qty', 'purchase_items.packing_qty', 'purchase_items.item_per_piece')->get() as $pb) {
            $m = (float)($pb->item_per_piece > 1 ? $pb->item_per_piece : ($isCartonProd ? $ppp : 1));
            $effectivePbQty = (!empty($pb->packing_qty) && (float)$pb->packing_qty > 0) ? $pb->packing_qty : $pb->qty;
            $purchasesBefore += $isCartonProd ? Product::parseCartonQtyToPieces($effectivePbQty, $m, 'ctn') : ((float)$effectivePbQty * $m);
        }

        // C. Sales Returns before start_date
        $saleReturnBeforeQuery = DB::table('sales_returns')
            ->where('product_code', $product->item_code)
            ->where('created_at', '<', $startDt);
        if ($branchId !== 'all' && !empty($branchId)) {
            $saleReturnBeforeQuery->whereExists(function ($sub) use ($branchId) {
                $sub->select(DB::raw(1))
                    ->from('sales')
                    ->whereColumn('sales.id', 'sales_returns.sale_id')
                    ->where('sales.branch_id', $branchId);
            });
        }
        $saleReturnsBefore = 0;
        foreach ($saleReturnBeforeQuery->select('sales_returns.qty', 'sales_returns.sale_id')->get() as $srb) {
            $origSaleItem = DB::table('sale_items')->where('sale_id', $srb->sale_id)->where('product_id', $productId)->first();
            $isPcsReturn = $origSaleItem && strtolower($origSaleItem->sale_unit ?? '') === 'pcs';
            $saleReturnsBefore += Product::parseCartonQtyToPieces($srb->qty, $ppp, $isPcsReturn ? 'pcs' : 'ctn');
        }

        // D. Stock Transfers In before start_date
        $transInBefore = 0;
        if ($branchId !== 'all' && !empty($branchId)) {
            $transInBefore = (float) DB::table('stock_transfers')
                ->where('product_id', $productId)
                ->where('to_branch_id', $branchId)
                ->where('status', 'approved')
                ->where('created_at', '<', $startDt)
                ->sum('quantity');
        } elseif ($warehouseId !== 'all' && !empty($warehouseId)) {
            $transInBefore = (float) DB::table('stock_transfers')
                ->where('product_id', $productId)
                ->where('to_warehouse_id', $warehouseId)
                ->where('status', 'approved')
                ->where('created_at', '<', $startDt)
                ->sum('quantity');
        }

        // E. Sales & Gatepasses before start_date
        // Bulk identify sales that have delivery challans (warehouse_orders)
        $dcSaleIds = DB::table('warehouse_orders')
            ->whereNotNull('sale_id')
            ->pluck('sale_id')
            ->unique()
            ->toArray();

        $saleBeforeQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sale_items.product_id', $productId)
            ->where('sales.status', '!=', 'cancelled')
            ->where('sales.created_at', '<', $startDt);

        if ($variantId) {
            $saleBeforeQuery->where('sale_items.variant_id', $variantId);
        }

        if (!empty($dcSaleIds)) {
            $saleBeforeQuery->whereNotIn('sales.id', $dcSaleIds);
        }

        if ($branchId !== 'all' && !empty($branchId)) {
            $saleBeforeQuery->where('sales.branch_id', $branchId);
        }
        if ($warehouseId !== 'all' && !empty($warehouseId)) {
            $saleBeforeQuery->where('sale_items.warehouse_id', $warehouseId);
        }
        $salesBefore = 0;
        foreach ($saleBeforeQuery->select('sale_items.sales_qty', 'sale_items.sale_unit')->get() as $sb) {
            $isPcsSale = strtolower($sb->sale_unit ?? '') === 'pcs';
            $salesBefore += Product::parseCartonQtyToPieces($sb->sales_qty, $ppp, $isPcsSale ? 'pcs' : 'ctn');
        }

        // Add Outward Gatepasses issued BEFORE start_date
        $gpBeforeQuery = DB::table('outward_gatepasses')
            ->leftJoin('warehouse_orders', 'outward_gatepasses.order_id', '=', 'warehouse_orders.id')
            ->whereNotNull('outward_gatepasses.items')
            ->where('outward_gatepasses.created_at', '<', $startDt);

        if ($branchId !== 'all' && !empty($branchId)) {
            $gpBeforeQuery->where(function ($q) use ($branchId) {
                $q->where('outward_gatepasses.branch_id', $branchId)
                  ->orWhere('warehouse_orders.branch_id', $branchId);
            });
        }
        if ($warehouseId !== 'all' && !empty($warehouseId)) {
            $gpBeforeQuery->where(function ($q) use ($warehouseId) {
                $q->where('outward_gatepasses.warehouse_id', $warehouseId)
                  ->orWhere('warehouse_orders.warehouse_id', $warehouseId);
            });
        }

        $gpsBefore = $gpBeforeQuery->select('outward_gatepasses.items')->get();
        $gpOutBefore = 0;
        foreach ($gpsBefore as $gp) {
            $items = is_string($gp->items) ? json_decode($gp->items, true) : $gp->items;
            if (is_array($items)) {
                foreach ($items as $it) {
                    if ((int)($it['product_id'] ?? 0) === $productId) {
                        if ($variantId && !empty($it['variant_id']) && (int)$it['variant_id'] !== $variantId) {
                            continue;
                        }
                        $rawGQty = (float) ($it['qty'] ?? 0);
                        $isPcsGp = strtolower($it['sale_unit'] ?? ($it['unit'] ?? '')) === 'pcs';
                        $m = (!$isPcsGp && $isCartonProd) ? $ppp : 1;
                        $gpOutBefore += $isCartonProd ? Product::parseCartonQtyToPieces($rawGQty, $m, $isPcsGp ? 'pcs' : 'ctn') : ($rawGQty * $m);
                    }
                }
            }
        }
        $salesBefore += $gpOutBefore;

        // F. Purchase Returns before start_date
        $purReturnBeforeQuery = DB::table('purchase_return_items')
            ->join('purchase_returns', 'purchase_return_items.purchase_return_id', '=', 'purchase_returns.id')
            ->where('purchase_return_items.product_id', $productId)
            ->where('purchase_returns.created_at', '<', $startDt);
        if ($warehouseId !== 'all' && !empty($warehouseId)) {
            $purReturnBeforeQuery->where('purchase_returns.warehouse_id', $warehouseId);
        }
        $purReturnsBefore = (float) $purReturnBeforeQuery->sum('purchase_return_items.qty');

        // G. Stock Transfers Out before start_date
        $transOutBefore = 0;
        if ($branchId !== 'all' && !empty($branchId)) {
            $transOutBefore = (float) DB::table('stock_transfers')
                ->where('product_id', $productId)
                ->where('from_branch_id', $branchId)
                ->where('status', 'approved')
                ->where('created_at', '<', $startDt)
                ->sum('quantity');
        } elseif ($warehouseId !== 'all' && !empty($warehouseId)) {
            $transOutBefore = (float) DB::table('stock_transfers')
                ->where('product_id', $productId)
                ->where('from_warehouse_id', $warehouseId)
                ->where('status', 'approved')
                ->where('created_at', '<', $startDt)
                ->sum('quantity');
        }

        // H. Damaged Stocks before start_date
        $damagedBeforeQuery = DB::table('damaged_stocks')
            ->where('product_id', $productId)
            ->where('created_at', '<', $startDt);
        if ($branchId !== 'all' && !empty($branchId)) {
            $damagedBeforeQuery->where('branch_id', $branchId);
        }
        if ($warehouseId !== 'all' && !empty($warehouseId)) {
            $damagedBeforeQuery->where('warehouse_id', $warehouseId);
        }
        $damagedBefore = (float) $damagedBeforeQuery->sum('quantity');

        // Total Net Opening Stock Qty (In base pieces)
        $openingQty = ($openingInitial + $purchasesBefore + $saleReturnsBefore + $transInBefore)
                    - ($salesBefore + $purReturnsBefore + $transOutBefore + $damagedBefore);
        $openingValue = $openingQty * $costPerPiece;

        // ══════════════════════════════════════════════════════════════════
        // 2. TRANSACTIONS WITHIN DATE RANGE [startDate, endDate]
        // ══════════════════════════════════════════════════════════════════
        $events = [];

        // A. Opening entries in date range
        $inRangeOpenQuery = DB::table('stock_movements')
            ->leftJoin('product_variants as pv', 'stock_movements.variant_id', '=', 'pv.id')
            ->where('stock_movements.product_id', $productId)
            ->whereIn('stock_movements.ref_type', ['OPENING', 'OPENING_ADJ'])
            ->whereBetween('stock_movements.created_at', [$startDt, $endDt]);
        if ($variantId) {
            $inRangeOpenQuery->where('stock_movements.variant_id', $variantId);
        }
        if ($branchId !== 'all' && !empty($branchId)) {
            $inRangeOpenQuery->where('stock_movements.branch_id', $branchId);
        }
        $inRangeOpenQuery->select('stock_movements.*', 'pv.color as variant_color', 'pv.variant_name');
        foreach ($inRangeOpenQuery->get() as $op) {
            $isOut    = ($op->type === 'out');
            $inQty    = $isOut ? 0 : (float) $op->qty;
            $outQty   = $isOut ? (float) $op->qty : 0;
            $vColor   = $op->variant_color ?: $op->variant_name;
            $events[] = [
                'datetime'      => $op->created_at,
                'date'          => date('d-M-Y H:i', strtotime($op->created_at)),
                'type'          => 'Opening Stock Entry',
                'badge'         => 'badge-primary',
                'icon'          => 'fa-hourglass-start',
                'ref_no'        => 'OPENING',
                'party'         => 'Opening Stock Allotment' . ($vColor ? " ({$vColor})" : ''),
                'warehouse'     => 'Main Store',
                'in_qty'        => $inQty,
                'in_price'      => $costPerPiece,
                'in_total'      => $inQty * $costPerPiece,
                'out_qty'       => $outQty,
                'out_price'     => $costPerPiece,
                'out_total'     => $outQty * $costPerPiece,
                'remarks'       => ($vColor ? "[Color: {$vColor}] " : '') . ($op->note ?? 'Initial stock recorded'),
            ];
        }

        // B. Purchases in date range
        $purRangeQuery = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->leftJoin('vendors', 'purchases.vendor_id', '=', 'vendors.id')
            ->leftJoin('warehouses', 'purchase_items.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('product_variants as pv', 'purchase_items.variant_id', '=', 'pv.id')
            ->where('purchase_items.product_id', $productId)
            ->whereNull('purchases.deleted_at')
            ->whereBetween('purchases.created_at', [$startDt, $endDt]);
        if ($variantId) {
            $purRangeQuery->where('purchase_items.variant_id', $variantId);
        }
        if ($branchId !== 'all' && !empty($branchId)) {
            $purRangeQuery->where('purchases.branch_id', $branchId);
        }
        if ($warehouseId !== 'all' && !empty($warehouseId)) {
            $purRangeQuery->where(function ($q) use ($warehouseId) {
                $q->where('purchase_items.warehouse_id', $warehouseId)
                  ->orWhere('purchases.warehouse_id', $warehouseId);
            });
        }
        $purRangeQuery->select(
            'purchases.created_at',
            'purchases.invoice_no',
            'purchases.vendor_name',
            'vendors.name as vendor_db_name',
            'warehouses.warehouse_name',
            'purchase_items.qty',
            'purchase_items.packing_qty',
            'purchase_items.item_per_piece',
            'purchase_items.price',
            'purchase_items.line_total',
            'purchases.note',
            'pv.color as variant_color',
            'pv.variant_name'
        );
        foreach ($purRangeQuery->get() as $pur) {
            $vName = !empty($pur->vendor_name) ? $pur->vendor_name : (!empty($pur->vendor_db_name) ? $pur->vendor_db_name : 'Supplier');
            $vColor = $pur->variant_color ?: $pur->variant_name;
            $whName = !empty($pur->warehouse_name) ? $pur->warehouse_name : 'Main Store';
            $pMult = (float)(($pur->item_per_piece ?? 1) > 1 ? $pur->item_per_piece : ($isCartonProd ? $ppp : 1));
            $effectivePurQty = (!empty($pur->packing_qty) && (float)$pur->packing_qty > 0) ? $pur->packing_qty : $pur->qty;
            $pQty = $isCartonProd ? Product::parseCartonQtyToPieces($effectivePurQty, $pMult, 'ctn') : ((float) $effectivePurQty * $pMult);
            $pTotal = (float) ($pur->line_total > 0 ? $pur->line_total : ($pQty * $costPerPiece));
            $pPrice = $pQty > 0 ? ($pTotal / $pQty) : $costPerPiece;
            $events[] = [
                'datetime'      => $pur->created_at,
                'date'          => date('d-M-Y H:i', strtotime($pur->created_at)),
                'type'          => 'Purchase (Inward)',
                'badge'         => 'badge-success',
                'icon'          => 'fa-cart-plus',
                'ref_no'        => $pur->invoice_no ?? 'PUR',
                'party'         => $vName . ($vColor ? " ({$vColor})" : ''),
                'warehouse'     => $whName,
                'in_qty'        => $pQty,
                'in_price'      => $pPrice,
                'in_total'      => $pTotal,
                'out_qty'       => 0,
                'out_price'     => 0,
                'out_total'     => 0,
                'remarks'       => ($vColor ? "[{$vColor}] " : '') . ($pur->note ?? 'Received from Supplier'),
            ];
        }

        // C. Sales in date range
        $saleRangeQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->leftJoin('warehouses', 'sale_items.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('product_variants as pv', 'sale_items.variant_id', '=', 'pv.id')
            ->where('sale_items.product_id', $productId)
            ->where('sales.status', '!=', 'cancelled')
            ->whereBetween('sales.created_at', [$startDt, $endDt]);
        if ($variantId) {
            $saleRangeQuery->where('sale_items.variant_id', $variantId);
        }
        if ($branchId !== 'all' && !empty($branchId)) {
            $saleRangeQuery->where('sales.branch_id', $branchId);
        }
        if ($warehouseId !== 'all' && !empty($warehouseId)) {
            $saleRangeQuery->where('sale_items.warehouse_id', $warehouseId);
        }
        $saleRangeQuery->select(
            'sales.id as sale_id',
            'sales.created_at',
            'sales.invoice_no',
            'customers.customer_name',
            'warehouses.warehouse_name',
            'sale_items.sales_qty',
            'sale_items.sale_unit',
            'sale_items.sales_price',
            'sale_items.retail_price',
            'sale_items.amount',
            'sales.remarks',
            'pv.color as variant_color',
            'pv.variant_name'
        );
        foreach ($saleRangeQuery->get() as $sale) {
            $isDcSale = in_array($sale->sale_id, $dcSaleIds);
            $cName = !empty($sale->customer_name) ? $sale->customer_name : 'Customer';
            $vColor = $sale->variant_color ?: $sale->variant_name;
            $whName = !empty($sale->warehouse_name) ? $sale->warehouse_name : 'Main Store';
            $rawQty = (float) $sale->sales_qty;
            $isPcsSale = strtolower($sale->sale_unit ?? '') === 'pcs';
            $sPieces = Product::parseCartonQtyToPieces($sale->sales_qty, $ppp, $isPcsSale ? 'pcs' : 'ctn');
            $sTotal = (float) $sale->amount > 0 ? (float) $sale->amount : ($rawQty * (float)$sale->retail_price);
            $sPrice = $sPieces > 0 ? ($sTotal / $sPieces) : ($ppp > 0 ? ((float)$sale->retail_price / $ppp) : (float)$sale->retail_price);

            if ($isDcSale) {
                // Booking / Delivery Challan sale: Goods are physically dispatched via Outward Gatepasses
                $events[] = [
                    'datetime'      => $sale->created_at,
                    'date'          => date('d-M-Y H:i', strtotime($sale->created_at)),
                    'type'          => 'Sale Booking (DC)',
                    'badge'         => 'badge-info',
                    'icon'          => 'fa-calendar-check',
                    'ref_no'        => $sale->invoice_no ?? 'SALE',
                    'party'         => $cName . ($vColor ? " ({$vColor})" : ''),
                    'warehouse'     => $whName,
                    'in_qty'        => 0,
                    'in_price'      => 0,
                    'in_total'      => 0,
                    'out_qty'       => 0, // No immediate stock reduction
                    'out_price'     => $sPrice,
                    'out_total'     => 0,
                    'remarks'       => ($vColor ? "[Color: {$vColor}] " : '') . "Booking Order ({$sPieces} pcs booked - Dispatched via Gatepass)",
                ];
            } else {
                // Counter / Direct sale: Immediate stock reduction
                $events[] = [
                    'datetime'      => $sale->created_at,
                    'date'          => date('d-M-Y H:i', strtotime($sale->created_at)),
                    'type'          => 'Sale Invoice',
                    'badge'         => 'badge-danger',
                    'icon'          => 'fa-file-invoice-dollar',
                    'ref_no'        => $sale->invoice_no ?? 'SALE',
                    'party'         => $cName . ($vColor ? " ({$vColor})" : ''),
                    'warehouse'     => $whName,
                    'in_qty'        => 0,
                    'in_price'      => 0,
                    'in_total'      => 0,
                    'out_qty'       => $sPieces,
                    'out_price'     => $sPrice,
                    'out_total'     => $sTotal,
                    'remarks'       => ($vColor ? "[Color: {$vColor}] " : '') . ($sale->remarks ?? 'Sold to Customer'),
                ];
            }
        }

        // C2. Outward Gatepasses in date range (Physical stock dispatch)
        $gpRangeQuery = DB::table('outward_gatepasses')
            ->leftJoin('warehouse_orders', 'outward_gatepasses.order_id', '=', 'warehouse_orders.id')
            ->leftJoin('warehouses', 'outward_gatepasses.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('branches', 'outward_gatepasses.branch_id', '=', 'branches.id')
            ->whereNotNull('outward_gatepasses.items')
            ->whereBetween('outward_gatepasses.created_at', [$startDt, $endDt]);

        if ($branchId !== 'all' && !empty($branchId)) {
            $gpRangeQuery->where(function ($q) use ($branchId) {
                $q->where('outward_gatepasses.branch_id', $branchId)
                  ->orWhere('warehouse_orders.branch_id', $branchId);
            });
        }
        if ($warehouseId !== 'all' && !empty($warehouseId)) {
            $gpRangeQuery->where(function ($q) use ($warehouseId) {
                $q->where('outward_gatepasses.warehouse_id', $warehouseId)
                  ->orWhere('warehouse_orders.warehouse_id', $warehouseId);
            });
        }

        $gpRangeQuery->select(
            'outward_gatepasses.*',
            'warehouses.warehouse_name',
            'branches.name as branch_name',
            'warehouse_orders.dc_no as order_dc_no'
        );

        foreach ($gpRangeQuery->get() as $gp) {
            $items = is_string($gp->items) ? json_decode($gp->items, true) : $gp->items;
            if (!is_array($items)) continue;

            foreach ($items as $it) {
                if ((int)($it['product_id'] ?? 0) !== $productId) continue;
                if ($variantId && !empty($it['variant_id']) && (int)$it['variant_id'] !== $variantId) continue;

                $rawGQty = (float)($it['qty'] ?? 0);
                if ($rawGQty <= 0) continue;

                $isPcsGp = strtolower($it['sale_unit'] ?? ($it['unit'] ?? '')) === 'pcs';
                $gPieces = $isCartonProd ? Product::parseCartonQtyToPieces($rawGQty, $ppp, $isPcsGp ? 'pcs' : 'ctn') : ($rawGQty * 1);
                $gTotal = (float)($it['amount'] ?? ($rawGQty * (float)($it['retail_price'] ?? $costRate)));
                $gPrice = $gPieces > 0 ? ($gTotal / $gPieces) : $costPerPiece;

                $locName = !empty($gp->warehouse_name) ? $gp->warehouse_name : (!empty($gp->branch_name) ? "{$gp->branch_name} (Branch)" : 'Main Store');
                $dcNum = !empty($gp->dc_no) ? $gp->dc_no : ($gp->order_dc_no ?? '');
                $invNum = !empty($gp->invoice_no) ? $gp->invoice_no : '';
                $refDetails = [];
                if ($dcNum) $refDetails[] = "DC: {$dcNum}";
                if ($invNum) $refDetails[] = "Inv: {$invNum}";
                $refText = !empty($refDetails) ? ' (' . implode(', ', $refDetails) . ')' : '';

                $events[] = [
                    'datetime'      => $gp->created_at,
                    'date'          => date('d-M-Y H:i', strtotime($gp->created_at)),
                    'type'          => 'Outward Gate Pass',
                    'badge'         => 'badge-primary',
                    'icon'          => 'fa-truck-arrow-right',
                    'ref_no'        => $gp->gatepass_number ?? ('GP-' . $gp->id),
                    'party'         => $gp->customer_name ?? 'Customer Dispatch',
                    'warehouse'     => $locName,
                    'in_qty'        => 0,
                    'in_price'      => 0,
                    'in_total'      => 0,
                    'out_qty'       => $gPieces,
                    'out_price'     => $gPrice,
                    'out_total'     => $gTotal,
                    'remarks'       => "Goods Dispatched{$refText}" . ($gp->remarks ? " - {$gp->remarks}" : ''),
                ];
            }
        }

        // D. Sales Returns in date range
        $saleReturnQuery = DB::table('sales_returns')
            ->where('product_code', $product->item_code)
            ->whereBetween('created_at', [$startDt, $endDt]);
        if ($branchId !== 'all' && !empty($branchId)) {
            $saleReturnQuery->whereExists(function ($sub) use ($branchId) {
                $sub->select(DB::raw(1))
                    ->from('sales')
                    ->whereColumn('sales.id', 'sales_returns.sale_id')
                    ->where('sales.branch_id', $branchId);
            });
        }
        foreach ($saleReturnQuery->get() as $sr) {
            $rawSrQty = (float) $sr->qty;
            $origSaleItem = DB::table('sale_items')->where('sale_id', $sr->sale_id)->where('product_id', $productId)->first();
            $isPcsReturn = $origSaleItem && strtolower($origSaleItem->sale_unit ?? '') === 'pcs';
            $srPieces = Product::parseCartonQtyToPieces($rawSrQty, $ppp, $isPcsReturn ? 'pcs' : 'ctn');
            $srTotal = (float) ($sr->per_total > 0 ? $sr->per_total : ($rawSrQty * (float)$sr->per_price));
            $srPrice = $srPieces > 0 ? ($srTotal / $srPieces) : $costPerPiece;

            // Resolve actual warehouse or shop name
            $srWhName = 'Shop';
            if (!empty($sr->sale_id)) {
                $srItem = DB::table('sale_items')
                    ->leftJoin('warehouses', 'sale_items.warehouse_id', '=', 'warehouses.id')
                    ->leftJoin('branches', 'sale_items.branch_id', '=', 'branches.id')
                    ->where('sale_items.sale_id', $sr->sale_id)
                    ->where('sale_items.product_id', $productId)
                    ->select('warehouses.warehouse_name', 'branches.name as branch_name')
                    ->first();
                if ($srItem) {
                    $srWhName = !empty($srItem->warehouse_name) ? $srItem->warehouse_name : ($srItem->branch_name ? "{$srItem->branch_name} (Shop)" : 'Shop');
                }
            }

            $events[] = [
                'datetime'      => $sr->created_at,
                'date'          => date('d-M-Y H:i', strtotime($sr->created_at)),
                'type'          => 'Sale Return',
                'badge'         => 'badge-info',
                'icon'          => 'fa-undo',
                'ref_no'        => $sr->reference ?? 'SR',
                'party'         => $sr->customer ?? 'Customer Return',
                'warehouse'     => $srWhName,
                'in_qty'        => $srPieces,
                'in_price'      => $srPrice,
                'in_total'      => $srTotal,
                'out_qty'       => 0,
                'out_price'     => 0,
                'out_total'     => 0,
                'remarks'       => $sr->return_note ?? 'Goods returned by Customer',
            ];
        }

        // E. Purchase Returns in date range
        $purReturnQuery = DB::table('purchase_return_items')
            ->join('purchase_returns', 'purchase_return_items.purchase_return_id', '=', 'purchase_returns.id')
            ->leftJoin('vendors', 'purchase_returns.vendor_id', '=', 'vendors.id')
            ->leftJoin('warehouses', 'purchase_returns.warehouse_id', '=', 'warehouses.id')
            ->where('purchase_return_items.product_id', $productId)
            ->whereBetween('purchase_returns.created_at', [$startDt, $endDt]);
        if ($warehouseId !== 'all' && !empty($warehouseId)) {
            $purReturnQuery->where('purchase_returns.warehouse_id', $warehouseId);
        }
        $purReturnQuery->select(
            'purchase_returns.created_at',
            'purchase_returns.return_invoice',
            'vendors.name as vendor_name',
            'warehouses.warehouse_name',
            'purchase_return_items.qty',
            'purchase_return_items.price',
            'purchase_return_items.line_total',
            'purchase_returns.remarks'
        );
        foreach ($purReturnQuery->get() as $pr) {
            $prQty = (float) $pr->qty;
            $prTotal = (float) ($pr->line_total > 0 ? $pr->line_total : ($prQty * $costPerPiece));
            $prPrice = $prQty > 0 ? ($prTotal / $prQty) : $costPerPiece;
            $events[] = [
                'datetime'      => $pr->created_at,
                'date'          => date('d-M-Y H:i', strtotime($pr->created_at)),
                'type'          => 'Purchase Return',
                'badge'         => 'badge-warning',
                'icon'          => 'fa-reply',
                'ref_no'        => $pr->return_invoice ?? 'PR',
                'party'         => $pr->vendor_name ?? 'Vendor Return',
                'warehouse'     => $pr->warehouse_name ?? 'Main Store',
                'in_qty'        => 0,
                'in_price'      => 0,
                'in_total'      => 0,
                'out_qty'       => $prQty,
                'out_price'     => $prPrice,
                'out_total'     => $prTotal,
                'remarks'       => $pr->remarks ?? 'Returned to Vendor',
            ];
        }

        // F. Stock Transfers in date range
        $transQuery = DB::table('stock_transfers')
            ->leftJoin('branches as fb', 'stock_transfers.from_branch_id', '=', 'fb.id')
            ->leftJoin('branches as tb', 'stock_transfers.to_branch_id', '=', 'tb.id')
            ->leftJoin('warehouses as fw', 'stock_transfers.from_warehouse_id', '=', 'fw.id')
            ->leftJoin('warehouses as tw', 'stock_transfers.to_warehouse_id', '=', 'tw.id')
            ->where('stock_transfers.product_id', $productId)
            ->where('stock_transfers.status', 'approved')
            ->whereBetween('stock_transfers.created_at', [$startDt, $endDt])
            ->select(
                'stock_transfers.*',
                'fb.name as from_branch_name',
                'tb.name as to_branch_name',
                'fw.warehouse_name as from_wh_name',
                'tw.warehouse_name as to_wh_name'
            );
        foreach ($transQuery->get() as $st) {
            $qty = (float) $st->quantity;
            $fromName = ($st->from_branch_name ? $st->from_branch_name . ' - ' : '') . ($st->from_wh_name ?? 'Shop/Branch');
            $toName = ($st->to_branch_name ? $st->to_branch_name . ' - ' : '') . ($st->to_wh_name ?? 'Shop/Branch');

            if ($branchId !== 'all' && !empty($branchId)) {
                if ($st->from_branch_id == $branchId) {
                    $events[] = [
                        'datetime'      => $st->created_at,
                        'date'          => date('d-M-Y H:i', strtotime($st->created_at)),
                        'type'          => 'Transfer Out',
                        'badge'         => 'badge-secondary',
                        'icon'          => 'fa-arrow-right',
                        'ref_no'        => 'TRF-' . $st->id,
                        'party'         => 'To: ' . $toName,
                        'warehouse'     => $fromName,
                        'in_qty'        => 0,
                        'in_price'      => 0,
                        'in_total'      => 0,
                        'out_qty'       => $qty,
                        'out_price'     => $costPerPiece,
                        'out_total'     => $qty * $costPerPiece,
                        'remarks'       => $st->remarks ?? 'Transferred Out',
                    ];
                } elseif ($st->to_branch_id == $branchId) {
                    $events[] = [
                        'datetime'      => $st->created_at,
                        'date'          => date('d-M-Y H:i', strtotime($st->created_at)),
                        'type'          => 'Transfer In',
                        'badge'         => 'badge-primary',
                        'icon'          => 'fa-arrow-left',
                        'ref_no'        => 'TRF-' . $st->id,
                        'party'         => 'From: ' . $fromName,
                        'warehouse'     => $toName,
                        'in_qty'        => $qty,
                        'in_price'      => $costPerPiece,
                        'in_total'      => $qty * $costPerPiece,
                        'out_qty'       => 0,
                        'out_price'     => 0,
                        'out_total'     => 0,
                        'remarks'       => $st->remarks ?? 'Transferred In',
                    ];
                }
            } else {
                $events[] = [
                    'datetime'      => $st->created_at,
                    'date'          => date('d-M-Y H:i', strtotime($st->created_at)),
                    'type'          => 'Internal Transfer',
                    'badge'         => 'badge-secondary',
                    'icon'          => 'fa-exchange-alt',
                    'ref_no'        => 'TRF-' . $st->id,
                    'party'         => "From: {$fromName} → To: {$toName}",
                    'warehouse'     => $fromName,
                    'in_qty'        => 0,
                    'in_price'      => 0,
                    'in_total'      => 0,
                    'out_qty'       => 0,
                    'out_price'     => 0,
                    'out_total'     => 0,
                    'remarks'       => ($st->remarks ?? '') . " [Transfer Qty: {$qty}]",
                ];
            }
        }

        // G. Damaged Stocks in date range
        $damagedRangeQuery = DB::table('damaged_stocks')
            ->leftJoin('warehouses', 'damaged_stocks.warehouse_id', '=', 'warehouses.id')
            ->where('damaged_stocks.product_id', $productId)
            ->whereBetween('damaged_stocks.created_at', [$startDt, $endDt]);
        if ($branchId !== 'all' && !empty($branchId)) {
            $damagedRangeQuery->where('damaged_stocks.branch_id', $branchId);
        }
        if ($warehouseId !== 'all' && !empty($warehouseId)) {
            $damagedRangeQuery->where('damaged_stocks.warehouse_id', $warehouseId);
        }
        foreach ($damagedRangeQuery->get() as $dmg) {
            $dQty = (float) $dmg->quantity;
            $events[] = [
                'datetime'      => $dmg->created_at,
                'date'          => date('d-M-Y H:i', strtotime($dmg->created_at)),
                'type'          => 'Damaged Stock',
                'badge'         => 'badge-dark',
                'icon'          => 'fa-dumpster',
                'ref_no'        => 'DMG-' . $dmg->id,
                'party'         => 'Damaged Stock Report',
                'warehouse'     => $dmg->warehouse_name ?? 'Main Store',
                'in_qty'        => 0,
                'in_price'      => 0,
                'in_total'      => 0,
                'out_qty'       => $dQty,
                'out_price'     => $costPerPiece,
                'out_total'     => $dQty * $costPerPiece,
                'remarks'       => 'Damaged / Scrapped items',
            ];
        }

        // ══════════════════════════════════════════════════════════════════
        // 3. CHRONOLOGICAL SORTING & RUNNING BALANCE
        // ══════════════════════════════════════════════════════════════════
        usort($events, function ($a, $b) {
            return strcmp($a['datetime'], $b['datetime']);
        });

        $runningBalance = $openingQty;
        $totalInQty = 0;
        $totalInValue = 0;
        $totalOutQty = 0;
        $totalOutValue = 0;

        foreach ($events as &$ev) {
            $inQ = (float) $ev['in_qty'];
            $outQ = (float) $ev['out_qty'];

            $runningBalance += ($inQ - $outQ);
            $ev['balance_qty'] = $runningBalance;
            $ev['valuation_rate'] = $costPerPiece;
            $ev['balance_value'] = $runningBalance * $costPerPiece;

            $ev['in_qty_display'] = $inQ > 0 ? Product::formatCartonPieces($inQ, $ppp, $product->unit?->name) : '-';
            $ev['out_qty_display'] = $outQ > 0 ? Product::formatCartonPieces($outQ, $ppp, $product->unit?->name) : '-';
            $ev['balance_qty_display'] = Product::formatCartonPieces($runningBalance, $ppp, $product->unit?->name);

            $totalInQty += $inQ;
            $totalInValue += (float) $ev['in_total'];
            $totalOutQty += $outQ;
            $totalOutValue += (float) $ev['out_total'];
        }

        $closingQty = $runningBalance;
        $closingValue = $closingQty * $costPerPiece;

        // Current Branch & Warehouse info for display
        $branchName = 'All Branches (Consolidated)';
        if ($branchId !== 'all' && !empty($branchId)) {
            $branchObj = Branch::find($branchId);
            $branchName = $branchObj ? $branchObj->name : "Branch #{$branchId}";
        }

        $warehouseName = 'All Warehouses';
        if ($warehouseId !== 'all' && !empty($warehouseId)) {
            $whObj = Warehouse::find($warehouseId);
            $warehouseName = $whObj ? $whObj->warehouse_name : "Warehouse #{$warehouseId}";
        }

        return response()->json([
            'success'   => true,
            'product'   => [
                'id'                => $product->id,
                'variant_id'        => $variantId,
                'item_code'         => $product->item_code,
                'item_name'         => $variant ? ($product->item_name . ' (' . ($variant->color ?: $variant->variant_name) . ')') : ($product->has_variants ? ($product->item_name . ' (All Variants)') : $product->item_name),
                'category'          => $product->category_relation->name ?? '-',
                'subcategory'       => $product->sub_category_relation->name ?? '-',
                'brand'             => $product->brand->name ?? '-',
                'unit'              => $product->unit->name ?? 'Pcs',
                'piece_per_pack'    => $ppp,
                'is_carton'         => $isCartonProd,
                'cost_per_piece'    => $costPerPiece,
                'retail_per_piece'  => $retailPerPiece,
                'wholesale_price'   => $costRate,
                'retail_price'      => $retailRate,
                'alert_quantity'    => $product->alert_quantity ?? 0,
            ],
            'filters'   => [
                'branch_id'         => $branchId,
                'branch_name'       => $branchName,
                'warehouse_id'      => $warehouseId,
                'warehouse_name'    => $warehouseName,
                'start_date'        => date('d-M-Y', strtotime($startDate)),
                'end_date'          => date('d-M-Y', strtotime($endDate)),
            ],
            'summary'   => [
                'opening_qty'          => $openingQty,
                'opening_qty_display'  => Product::formatCartonPieces($openingQty, $ppp, $product->unit?->name),
                'opening_value'        => $openingValue,
                'total_in_qty'         => $totalInQty,
                'total_in_qty_display' => Product::formatCartonPieces($totalInQty, $ppp, $product->unit?->name),
                'total_in_value'       => $totalInValue,
                'total_out_qty'        => $totalOutQty,
                'total_out_qty_display'=> Product::formatCartonPieces($totalOutQty, $ppp, $product->unit?->name),
                'total_out_value'      => $totalOutValue,
                'closing_qty'          => $closingQty,
                'closing_qty_display'  => Product::formatCartonPieces($closingQty, $ppp, $product->unit?->name),
                'closing_value'        => $closingValue,
            ],
            'events'    => $events,
        ]);
    }

    // =========================================================
    // ✅ TRIAL BALANCE REPORT
    // READ-ONLY: Sirf SELECT queries - koi bhi existing logic,
    // voucher, ledger ya data affect nahi hota.
    // =========================================================

    public function trialBalance(Request $request)
    {
        $user    = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        $branches = $isSuper
            ? Branch::orderBy('name')->get()
            : ($user->branch_id ? Branch::where('id', $user->branch_id)->get() : collect());

        $defaultBranchId = $isSuper ? null : $user->branch_id;
        $accountsQuery = DB::table('accounts as a')
            ->join('account_heads as ah', 'ah.id', '=', 'a.head_id')
            ->select('a.id', 'a.account_code', 'a.title', 'ah.name as head_name')
            ->where(function ($q) {
                $q->whereNull('a.status')
                  ->orWhereNotIn('a.status', [0, '0', 'inactive']);
            })
            ->orderBy('ah.name')
            ->orderBy('a.title');

        if ($defaultBranchId) {
            $accountsQuery->where('a.branch_id', $defaultBranchId);
        }
        $accounts = $accountsQuery->get();

        return view('admin_panel.reporting.trial_balance', compact('branches', 'isSuper', 'accounts'));
    }

    public function getAccountsByBranch(Request $request)
    {
        $user    = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        $branchId = null;
        if ($isSuper) {
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $branchId = (int) $request->branch_id;
            }
        } else {
            $branchId = $user->branch_id;
        }

        $query = DB::table('accounts as a')
            ->join('account_heads as ah', 'ah.id', '=', 'a.head_id')
            ->select('a.id', 'a.account_code', 'a.title', 'ah.name as head_name')
            ->where(function ($q) {
                $q->whereNull('a.status')
                  ->orWhereNotIn('a.status', [0, '0', 'inactive']);
            })
            ->orderBy('ah.name')
            ->orderBy('a.title');

        if ($branchId) {
            $query->where('a.branch_id', $branchId);
        }

        return response()->json($query->get());
    }

    public function fetchTrialBalance(Request $request)
    {
        $user    = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        // ── Filters ────────────────────────────────────────────
        $branchId  = null;
        if ($isSuper) {
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $branchId = (int) $request->branch_id;
            }
        } else {
            $branchId = $user->branch_id;
        }

        $accountId = null;
        if ($request->filled('account_id') && $request->account_id !== 'all') {
            $accountId = (int) $request->account_id;
        }

        $dateFrom = $request->input('date_from');   // e.g. 2024-01-01
        $dateTo   = $request->input('date_to');     // e.g. 2024-12-31
        $hideZero = $request->input('hide_zero') == '1';

        // ── Accounts Query (READ-ONLY SELECT) ─────────────────
        $accountsQuery = DB::table('accounts as a')
            ->join('account_heads as ah', 'ah.id', '=', 'a.head_id')
            ->leftJoin('branches as br', 'br.id', '=', 'a.branch_id')
            ->select(
                'a.id',
                'a.account_code',
                'a.title',
                'a.type',
                'a.opening_balance',
                'a.opening_date',
                'ah.name as head_name',
                'br.name as branch_name'
            )
            ->where(function ($q) {
                $q->whereNull('a.status')
                  ->orWhereNotIn('a.status', [0, '0', 'inactive']);
            })
            ->orderBy('ah.name')
            ->orderBy('a.account_code');

        if ($branchId) {
            $accountsQuery->where('a.branch_id', $branchId);
        }

        if ($accountId) {
            $accountsQuery->where('a.id', $accountId);
        }

        $accounts = $accountsQuery->get();

        // ── Build Result ───────────────────────────────────────
        $rows = [];
        $grandOb     = 0;
        $grandTxDr   = 0;
        $grandTxCr   = 0;
        $grandDebit  = 0;
        $grandCredit = 0;

        foreach ($accounts as $acc) {
            $isCredit = trim(strtolower($acc->type ?? 'debit')) === 'credit';
            $ob = (float) ($acc->opening_balance ?? 0);

            // Prior transactions before date_from
            $priorDebit  = 0;
            $priorCredit = 0;
            if ($dateFrom) {
                $prior = DB::table('account_ledger_entries')
                    ->where('account_id', $acc->id)
                    ->where('voucher_type', '!=', 'opening_balance')
                    ->where('transaction_date', '<', $dateFrom)
                    ->selectRaw('COALESCE(SUM(debit), 0) as dr, COALESCE(SUM(credit), 0) as cr')
                    ->first();
                $priorDebit  = (float) ($prior->dr ?? 0);
                $priorCredit = (float) ($prior->cr ?? 0);
            }

            $openingAtDate = $isCredit 
                ? ($ob + $priorCredit - $priorDebit) 
                : ($ob + $priorDebit - $priorCredit);

            // ── Transactions (ledger entries) in date range ───
            $txQuery = DB::table('account_ledger_entries')
                ->where('account_id', $acc->id)
                ->where('voucher_type', '!=', 'opening_balance');

            if ($dateFrom) {
                $txQuery->where('transaction_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $txQuery->where('transaction_date', '<=', $dateTo);
            }

            $tx = $txQuery->selectRaw('COALESCE(SUM(debit),0) as total_debit, COALESCE(SUM(credit),0) as total_credit')->first();
            $txDebit  = (float) ($tx->total_debit  ?? 0);
            $txCredit = (float) ($tx->total_credit ?? 0);

            // ── Net Closing Balance ───────────────────────────
            $closingBal = $isCredit
                ? ($openingAtDate + $txCredit - $txDebit)
                : ($openingAtDate + $txDebit - $txCredit);

            // ── Debit / Credit column split ───────────────────
            $debitBal  = 0;
            $creditBal = 0;

            if ($isCredit) {
                if ($closingBal >= 0) {
                    $creditBal = $closingBal;
                } else {
                    $debitBal  = abs($closingBal);
                }
            } else {
                if ($closingBal >= 0) {
                    $debitBal  = $closingBal;
                } else {
                    $creditBal = abs($closingBal);
                }
            }

            // Hide zero balance accounts if requested
            if ($hideZero && $debitBal == 0 && $creditBal == 0 && $txDebit == 0 && $txCredit == 0 && $openingAtDate == 0) {
                continue;
            }

            $grandOb     += $openingAtDate;
            $grandTxDr   += $txDebit;
            $grandTxCr   += $txCredit;
            $grandDebit  += $debitBal;
            $grandCredit += $creditBal;

            $rows[] = [
                'id'           => $acc->id,
                'account_code' => $acc->account_code,
                'title'        => $acc->title,
                'head_name'    => $acc->head_name,
                'branch_name'  => $acc->branch_name ?? '—',
                'type'         => $acc->type,
                'ob'           => $openingAtDate,
                'tx_debit'     => $txDebit,
                'tx_credit'    => $txCredit,
                'debit_bal'    => $debitBal,
                'credit_bal'   => $creditBal,
            ];
        }

        // ── Operational Control Accounts (Missing Commercial Items) ──────
        // Inhe sirf tab include karte hain jab specific account filter na ho
        if (!$accountId) {
            $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Branch') : 'All Branches';

            // 1. Sales (Credit - Gross Sales matching Income Statement)
            $salesTotal = (float) DB::table('sale_items as si')
                ->join('sales as s', 's.id', '=', 'si.sale_id')
                ->where(function ($q) { $q->whereNull('s.status')->orWhere('s.status', '!=', 'cancelled'); })
                ->when($branchId, fn($q) => $q->where('s.branch_id', $branchId))
                ->when($dateFrom, fn($q) => $q->whereDate('s.created_at', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('s.created_at', '<=', $dateTo))
                ->sum('si.amount');
            if ($salesTotal <= 0) {
                $salesTotal = (float) DB::table('sales')
                    ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'cancelled'); })
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                    ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
                    ->sum('total_net');
            }

            // 2. Purchases (Debit - matching Purchases Report & Income Statement)
            $purchasesTotal = (float) DB::table('purchases')
                ->whereNull('deleted_at')
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when($dateFrom, fn($q) => $q->whereDate('purchase_date', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('purchase_date', '<=', $dateTo))
                ->sum('net_amount');

            // 3. Sales Returns (Debit - matching Sales Return Report & Income Statement)
            $saleReturnTotal = (float) DB::table('sales_returns')
                ->when($branchId, function ($q) use ($branchId) {
                    $q->whereExists(function ($sq) use ($branchId) {
                        $sq->select(DB::raw(1))->from('sales')
                           ->whereColumn('sales.id', 'sales_returns.sale_id')
                           ->where('sales.branch_id', $branchId);
                    });
                })
                ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->sum('total_net');

            // 4. Purchase Returns (Credit - matching Income Statement)
            $purchaseReturnTotal = (float) DB::table('purchase_returns')
                ->when($branchId, function ($q) use ($branchId) {
                    $q->whereExists(function ($sq) use ($branchId) {
                        $sq->select(DB::raw(1))->from('purchases')
                           ->whereColumn('purchases.id', 'purchase_returns.purchase_id')
                           ->where('purchases.branch_id', $branchId);
                    });
                })
                ->when($dateFrom, fn($q) => $q->whereDate('return_date', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('return_date', '<=', $dateTo))
                ->sum('net_amount');

            // 5. Accounts Receivable - Customers (Debit - Live Transactional exact match with /report/receivable and Balance Sheet)
            $recReq = new Request([
                'branch_id' => $branchId,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ]);
            $recData = $this->fetch_receivable_report($recReq)->getData(true);
            $arTotal = (float) ($recData['kpi']['grand_closing'] ?? 0);

            // 6. Accounts Payable - Vendors (Credit - Live Transactional exact match with /report/payable and Balance Sheet)
            $payReq = new Request([
                'branch_id' => $branchId,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ]);
            $payData = $this->fetch_payable_report($payReq)->getData(true);
            $apTotal = (float) ($payData['kpi']['grand_closing'] ?? 0);

            // 7. Discount Allowed (Debit - matching Income Statement Sales Discount)
            $discountTotal = (float) DB::table('sales')
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'cancelled'); })
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->sum('discount_amount');

            // 8. Cartage Inward & Outward (Debit)
            $inwardCartage = (float) DB::table('inward_gatepasses')
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->sum('freight_charges');
            $purchExtra = (float) DB::table('purchases')
                ->whereNull('deleted_at')
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when($dateFrom, fn($q) => $q->whereDate('purchase_date', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('purchase_date', '<=', $dateTo))
                ->sum('extra_cost');
            $outwardCartage = (float) DB::table('sales')
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'cancelled'); })
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->sum('extra_charges');
            $cartageTotal = $inwardCartage + $purchExtra + $outwardCartage;

            // 9. Opening Stock / Inventory Valuation (Debit - Market Value matching Opening Stocks / Income Statement)
            $isReq = new Request([
                'branch_id' => $branchId,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ]);
            $isData = $this->fetchIncomeStatement($isReq)->getData(true);
            $stockValuation = (float) ($isData['opening_stock'] ?? 0);

            // Operational Items List
            $operationalItems = [
                // Credit items
                [
                    'id'           => null,
                    'account_code' => 'AP-CTRL',
                    'title'        => 'Accounts Payable (Vendors / Creditors)',
                    'head_name'    => 'Current Liabilities',
                    'branch_name'  => $branchName,
                    'type'         => 'Credit',
                    'ob'           => 0,
                    'tx_debit'     => 0,
                    'tx_credit'    => $apTotal,
                    'debit_bal'    => 0,
                    'credit_bal'   => $apTotal,
                    'link_url'     => route('report.vendor.ledger.new'),
                ],
                [
                    'id'           => null,
                    'account_code' => 'REV-SALE',
                    'title'        => 'Sales Revenue',
                    'head_name'    => 'Revenue / Sales',
                    'branch_name'  => $branchName,
                    'type'         => 'Credit',
                    'ob'           => 0,
                    'tx_debit'     => 0,
                    'tx_credit'    => $salesTotal,
                    'debit_bal'    => 0,
                    'credit_bal'   => $salesTotal,
                    'link_url'     => route('report.sale'),
                ],
                [
                    'id'           => null,
                    'account_code' => 'PUR-RET',
                    'title'        => 'Purchase Returns (Outward)',
                    'head_name'    => 'Purchases Contra',
                    'branch_name'  => $branchName,
                    'type'         => 'Credit',
                    'ob'           => 0,
                    'tx_debit'     => 0,
                    'tx_credit'    => $purchaseReturnTotal,
                    'debit_bal'    => 0,
                    'credit_bal'   => $purchaseReturnTotal,
                    'link_url'     => null,
                ],

                // Debit items
                [
                    'id'           => null,
                    'account_code' => 'PUR-CTRL',
                    'title'        => 'Purchases Account',
                    'head_name'    => 'Cost of Goods / Purchases',
                    'branch_name'  => $branchName,
                    'type'         => 'Debit',
                    'ob'           => 0,
                    'tx_debit'     => $purchasesTotal,
                    'tx_credit'    => 0,
                    'debit_bal'    => $purchasesTotal,
                    'credit_bal'   => 0,
                    'link_url'     => route('report.purchase'),
                ],
                [
                    'id'           => null,
                    'account_code' => 'SAL-RET',
                    'title'        => 'Sales Returns (Inward)',
                    'head_name'    => 'Revenue Contra',
                    'branch_name'  => $branchName,
                    'type'         => 'Debit',
                    'ob'           => 0,
                    'tx_debit'     => $saleReturnTotal,
                    'tx_credit'    => 0,
                    'debit_bal'    => $saleReturnTotal,
                    'credit_bal'   => 0,
                    'link_url'     => null,
                ],
                [
                    'id'           => null,
                    'account_code' => 'AR-CTRL',
                    'title'        => 'Accounts Receivable (Customers / Debtors)',
                    'head_name'    => 'Current Assets',
                    'branch_name'  => $branchName,
                    'type'         => 'Debit',
                    'ob'           => 0,
                    'tx_debit'     => $arTotal,
                    'tx_credit'    => 0,
                    'debit_bal'    => $arTotal,
                    'credit_bal'   => 0,
                    'link_url'     => route('report.customer.ledger.new'),
                ],
                [
                    'id'           => null,
                    'account_code' => 'EXP-FRT',
                    'title'        => 'Cartage Inward & Outward (Transport/Freight)',
                    'head_name'    => 'Direct & Indirect Expense',
                    'branch_name'  => $branchName,
                    'type'         => 'Debit',
                    'ob'           => 0,
                    'tx_debit'     => $cartageTotal,
                    'tx_credit'    => 0,
                    'debit_bal'    => $cartageTotal,
                    'credit_bal'   => 0,
                    'link_url'     => null,
                ],
                [
                    'id'           => null,
                    'account_code' => 'STK-VAL',
                    'title'        => 'Opening Stock / Inventory Valuation',
                    'head_name'    => 'Current Assets',
                    'branch_name'  => $branchName,
                    'type'         => 'Debit',
                    'ob'           => 0,
                    'tx_debit'     => $stockValuation,
                    'tx_credit'    => 0,
                    'debit_bal'    => $stockValuation,
                    'credit_bal'   => 0,
                    'link_url'     => route('report.item_stock'),
                ],
                [
                    'id'           => null,
                    'account_code' => 'EXP-DISC',
                    'title'        => 'Discount Allowed',
                    'head_name'    => 'Operating Expenses',
                    'branch_name'  => $branchName,
                    'type'         => 'Debit',
                    'ob'           => 0,
                    'tx_debit'     => $discountTotal,
                    'tx_credit'    => 0,
                    'debit_bal'    => $discountTotal,
                    'credit_bal'   => 0,
                    'link_url'     => null,
                ],
            ];

            foreach ($operationalItems as $item) {
                if ($hideZero && $item['debit_bal'] == 0 && $item['credit_bal'] == 0) {
                    continue;
                }
                $grandTxDr   += $item['tx_debit'];
                $grandTxCr   += $item['tx_credit'];
                $grandDebit  += $item['debit_bal'];
                $grandCredit += $item['credit_bal'];
                $rows[] = $item;
            }

            // 10. Owner's Capital / General Equity (Balancing Equation)
            $equityDiff = $grandDebit - $grandCredit;
            $capitalDr  = 0;
            $capitalCr  = 0;
            if ($equityDiff >= 0) {
                $capitalCr = $equityDiff;
            } else {
                $capitalDr = abs($equityDiff);
            }

            if (!$hideZero || $capitalDr != 0 || $capitalCr != 0) {
                $capitalRow = [
                    'id'           => null,
                    'account_code' => 'EQ-CAP',
                    'title'        => "Owner's Capital / General Equity",
                    'head_name'    => 'Equity & Reserves',
                    'branch_name'  => $branchName,
                    'type'         => $equityDiff >= 0 ? 'Credit' : 'Debit',
                    'ob'           => 0,
                    'tx_debit'     => $capitalDr,
                    'tx_credit'    => $capitalCr,
                    'debit_bal'    => $capitalDr,
                    'credit_bal'   => $capitalCr,
                    'link_url'     => route('report.balance_sheet'),
                ];
                $grandDebit  += $capitalDr;
                $grandCredit += $capitalCr;
                $grandTxDr   += $capitalDr;
                $grandTxCr   += $capitalCr;
                $rows[] = $capitalRow;
            }
        }

        $isBalanced = abs($grandDebit - $grandCredit) < 0.01;

        return response()->json([
            'rows'          => $rows,
            'grand_ob'      => $grandOb,
            'grand_tx_dr'   => $grandTxDr,
            'grand_tx_cr'   => $grandTxCr,
            'grand_debit'   => $grandDebit,
            'grand_credit'  => $grandCredit,
            'is_balanced'   => $isBalanced,
            'date_from'     => $dateFrom,
            'date_to'       => $dateTo,
            'branch_label'  => $branchId
                ? (Branch::find($branchId)?->name ?? 'Branch')
                : 'All Branches',
        ]);
    }

    // =========================================================
    // ✅ INCOME STATEMENT (PROFIT & LOSS) REPORT
    // READ-ONLY: Pure SELECT queries - no business logic altered.
    // =========================================================

    public function incomeStatement(Request $request)
    {
        $user    = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        $branches = $isSuper
            ? Branch::orderBy('name')->get()
            : ($user->branch_id ? Branch::where('id', $user->branch_id)->get() : collect());

        return view('admin_panel.reporting.income_statement', compact('branches', 'isSuper'));
    }

    public function fetchIncomeStatement(Request $request)
    {
        $user    = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        $branchId = null;
        if ($isSuper) {
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $branchId = (int) $request->branch_id;
            }
        } else {
            $branchId = $user->branch_id;
        }

        $dateFrom   = $request->input('date_from');
        $dateTo     = $request->input('date_to');
        $cogsMethod = $request->input('cogs_method', 'item_cost'); // 'item_cost' or 'purchases'

        // ── 1. REVENUE (Sales) ──────────────────────────────────
        $salesQ = DB::table('sales')->where(function ($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
        });
        if ($branchId) {
            $salesQ->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $salesQ->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesQ->whereDate('created_at', '<=', $dateTo);
        }

        $itemsGrossQ = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->where(function ($q) {
                $q->whereNull('s.status')->orWhere('s.status', '!=', 'cancelled');
            });
        if ($branchId) {
            $itemsGrossQ->where('s.branch_id', $branchId);
        }
        if ($dateFrom) {
            $itemsGrossQ->whereDate('s.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $itemsGrossQ->whereDate('s.created_at', '<=', $dateTo);
        }

        $grossSales = (float) ($itemsGrossQ->sum('si.amount') ?? 0);

        $salesRow = $salesQ->selectRaw('
            COALESCE(SUM(discount_amount), 0) as discount_amount,
            COALESCE(SUM(total_net), 0) as total_net
        ')->first();

        $totalDiscounts        = (float) ($salesRow->discount_amount ?? 0);
        $netSalesBeforeReturns = (float) ($salesRow->total_net ?? 0);

        // Sales Returns
        $returnsQ = DB::table('sales_returns');
        if ($branchId) {
            $returnsQ->whereExists(function ($q) use ($branchId) {
                $q->select(DB::raw(1))->from('sales')
                  ->whereColumn('sales.id', 'sales_returns.sale_id')
                  ->where('sales.branch_id', $branchId);
            });
        }
        if ($dateFrom) {
            $returnsQ->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $returnsQ->whereDate('created_at', '<=', $dateTo);
        }
        $salesReturns = (float) ($returnsQ->sum('total_net') ?? 0);

        // Effective Net Revenue
        $netRevenue = $netSalesBeforeReturns - $salesReturns;
        if ($netRevenue < 0 && $grossSales == 0) {
            $netRevenue = 0;
        }

        // ── 2. COST OF GOODS SOLD (COGS) ───────────────────────
        $cogsMethod = $request->input('cogs_method', 'periodic'); // 'periodic' | 'item_cost' | 'purchases'

        // 2.1 Direct Cost of Sold Items (Qty * Product Purchase Price)
        $cogsItemsQ = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->leftJoin('products as p', 'p.id', '=', 'si.product_id')
            ->where(function ($q) {
                $q->whereNull('s.status')->orWhere('s.status', '!=', 'cancelled');
            });
        if ($branchId) {
            $cogsItemsQ->where('s.branch_id', $branchId);
        }
        if ($dateFrom) {
            $cogsItemsQ->whereDate('s.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $cogsItemsQ->whereDate('s.created_at', '<=', $dateTo);
        }
        $itemCogsTotal = (float) ($cogsItemsQ->sum(DB::raw('si.sales_qty * COALESCE(p.price, 0)')) ?? 0);

        // 2.2 Purchases in Period
        $purchQ = DB::table('purchases')->whereNull('deleted_at');
        if ($branchId) {
            $purchQ->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $purchQ->whereDate('purchase_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $purchQ->whereDate('purchase_date', '<=', $dateTo);
        }
        $purchTotals = $purchQ->selectRaw('
            COALESCE(SUM(subtotal), 0) as gross_purchases,
            COALESCE(SUM(discount), 0) as purch_discount,
            COALESCE(SUM(net_amount), 0) as net_purchases
        ')->first();
        $grossPurchases = (float) ($purchTotals->gross_purchases ?? 0);
        $netPurchases   = (float) ($purchTotals->net_purchases ?? 0);

        // Purchase Returns
        $purchRetQ = DB::table('purchase_returns');
        if ($branchId) {
            $purchRetQ->whereExists(function ($q) use ($branchId) {
                $q->select(DB::raw(1))->from('purchases')
                  ->whereColumn('purchases.id', 'purchase_returns.purchase_id')
                  ->where('purchases.branch_id', $branchId);
            });
        }
        if ($dateFrom) {
            $purchRetQ->whereDate('return_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $purchRetQ->whereDate('return_date', '<=', $dateTo);
        }
        $purchaseReturns    = (float) ($purchRetQ->sum('net_amount') ?? 0);
        $effectivePurchases = $netPurchases - $purchaseReturns;

        // 2.3 Standard Accounting Periodic Inventory: Opening + Purchases - Returns - Closing Stock
        // Closing Stock Valuation — exactly matching Item Stock Report
        $allProdsForCS = \App\Models\Product::select('id', 'wholesale_price', 'price')->get()->keyBy('id');
        $csQtyMap = DB::table('warehouse_stocks')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id')
            ->pluck('total_qty', 'product_id');

        $closingStock = 0;
        $csNeedPrice = [];
        foreach ($csQtyMap as $pid => $qty) {
            $p = $allProdsForCS[$pid] ?? null;
            $wPrice = (float)(($p->wholesale_price ?? 0));
            if ($wPrice <= 0) $wPrice = (float)(($p->price ?? 0));
            if ($wPrice <= 0) {
                $csNeedPrice[] = $pid;
            }
            $closingStock += (float)$qty * $wPrice;
        }
        if (!empty($csNeedPrice)) {
            $csLatest = DB::table('purchase_items')
                ->whereIn('product_id', $csNeedPrice)
                ->select('product_id', 'price')
                ->orderBy('id', 'desc')
                ->get()
                ->unique('product_id')
                ->keyBy('product_id');
            foreach ($csNeedPrice as $pid) {
                $qty = (float)($csQtyMap[$pid] ?? 0);
                $fallback = (float)($csLatest[$pid]->price ?? 0);
                $closingStock += $qty * $fallback;
            }
        }

        // Opening Stock Valuation (Wholesale / Market Value - matching Opening Stocks Manager /opening-stocks)
        $queryOpen = \App\Models\Product::with(['warehouseStocks']);
        if ($branchId) {
            $bid = (int) $branchId;
            $queryOpen->where(function ($q) use ($bid) {
                $q->where('branch_id', $bid)
                  ->orWhereHas('warehouseStocks', fn($ws) => $ws->where('branch_id', $bid))
                  ->orWhereHas('stocks', fn($st) => $st->where('branch_id', $bid));
            });
        }
        $queryOpen->where(function ($q) use ($branchId) {
            $q->where('initial_stock', '>', 0)
              ->orWhereExists(function ($sub) use ($branchId) {
                  $sub->select(DB::raw(1))->from('stock_movements')
                      ->whereColumn('stock_movements.product_id', 'products.id')
                      ->whereIn('stock_movements.ref_type', ['OPENING', 'OPENING_ADJ', 'INITIAL_STOCK'])
                      ->when($branchId, fn($sm) => $sm->where('branch_id', (int) $branchId));
              })
              ->orWhereExists(function ($sub2) use ($branchId) {
                  $sub2->select(DB::raw(1))->from('warehouse_stocks')
                      ->whereColumn('warehouse_stocks.product_id', 'products.id')
                      ->where('warehouse_stocks.quantity', '>', 0)
                      ->when($branchId, fn($ws) => $ws->where('branch_id', (int) $branchId));
              });
        });
        $openProds = $queryOpen->get();
        $pIds = $openProds->pluck('id')->toArray();

        $openMovements = DB::table('stock_movements')
            ->whereIn('product_id', $pIds)
            ->whereIn('ref_type', ['OPENING', 'OPENING_ADJ', 'INITIAL_STOCK'])
            ->when($branchId, fn($q) => $q->where('branch_id', (int) $branchId))
            ->get()
            ->groupBy('product_id');

        $openingStock = 0;
        foreach ($openProds as $p) {
            $moves = $openMovements->get($p->id, collect());
            $movOpening = $moves->where('type', 'in')->sum('qty');
            if ($moves->isNotEmpty() && $movOpening > 0) {
                $openingQty = (float) $movOpening;
            } elseif ($p->initial_stock !== null && $p->initial_stock > 0) {
                $openingQty = (float) $p->initial_stock;
            } else {
                $whSum = $p->warehouseStocks
                    ->when($branchId, fn($c) => $c->where('branch_id', (int) $branchId))
                    ->sum('quantity');
                $openingQty = max(0, (float) $whSum);
            }

            $wPrice = (float) ($p->wholesale_price ?? 0);
            if ($wPrice <= 0) $wPrice = (float) ($p->price ?? 0);
            $openingStock += ($openingQty * $wPrice);
        }

        $goodsAvailable = $openingStock + $effectivePurchases;
        $periodicCogs   = $goodsAvailable - $closingStock;
        if ($periodicCogs < 0 && $itemCogsTotal > 0) {
            $periodicCogs = $itemCogsTotal;
        }

        // Active COGS based on method selected:
        if ($cogsMethod === 'purchases') {
            $activeCogs = $effectivePurchases;
        } elseif ($cogsMethod === 'item_cost') {
            $activeCogs = $itemCogsTotal;
        } else {
            // Default: 'periodic' (Standard Accounting: Opening + Purchases - Returns - Closing)
            $activeCogs = $periodicCogs;
        }

        $grossProfit        = $netRevenue - $activeCogs;
        $grossMarginPercent = $netRevenue > 0 ? round(($grossProfit / $netRevenue) * 100, 2) : 0;

        // ── 3. OPERATING EXPENSES (Head-wise from account_ledger_entries) ──
        $expQuery = DB::table('account_ledger_entries as ale')
            ->join('accounts as a', 'a.id', '=', 'ale.account_id')
            ->join('account_heads as ah', 'ah.id', '=', 'a.head_id')
            ->where(function ($q) {
                $q->where('ah.name', 'like', '%exp%')
                  ->orWhere('ah.name', 'like', '%expense%');
            })
            ->where('ale.voucher_type', '!=', 'opening_balance');

        if ($branchId) {
            $expQuery->where('ale.branch_id', $branchId);
        }
        if ($dateFrom) {
            $expQuery->whereDate('ale.transaction_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $expQuery->whereDate('ale.transaction_date', '<=', $dateTo);
        }

        $expensesBreakdown = (clone $expQuery)
            ->groupBy('a.id', 'a.title', 'a.account_code', 'ah.name')
            ->selectRaw('a.id, a.title, a.account_code, ah.name as head_name, COALESCE(SUM(ale.debit), 0) - COALESCE(SUM(ale.credit), 0) as amount')
            ->havingRaw('amount != 0')
            ->orderByDesc('amount')
            ->get();

        $totalOperatingExpenses = (float) ($expQuery->selectRaw('COALESCE(SUM(ale.debit), 0) - COALESCE(SUM(ale.credit), 0) as total')->value('total') ?? 0);

        // ── 4. NET PROFIT / (LOSS) ──────────────────────────────
        $netProfit        = $grossProfit - $totalOperatingExpenses;
        $netMarginPercent = $netRevenue > 0 ? round(($netProfit / $netRevenue) * 100, 2) : 0;

        return response()->json([
            'date_from'            => $dateFrom,
            'date_to'              => $dateTo,
            'cogs_method'          => $cogsMethod,
            'branch_label'         => $branchId ? (Branch::find($branchId)?->name ?? 'Branch') : 'All Branches',
            // Revenue
            'gross_sales'          => $grossSales,
            'sales_discounts'      => $totalDiscounts,
            'sales_returns'        => $salesReturns,
            'net_revenue'          => $netRevenue,
            // COGS
            'opening_stock'        => $openingStock,
            'gross_purchases'      => $grossPurchases,
            'purchase_returns'     => $purchaseReturns,
            'net_purchases'        => $effectivePurchases,
            'goods_available'      => $goodsAvailable,
            'closing_stock'        => $closingStock,
            'periodic_cogs'        => $periodicCogs,
            'item_cogs'            => $itemCogsTotal,
            'active_cogs'          => $activeCogs,
            // Gross Profit
            'gross_profit'         => $grossProfit,
            'gross_margin_percent' => $grossMarginPercent,
            // Expenses
            'expenses'             => $expensesBreakdown,
            'total_expenses'       => $totalOperatingExpenses,
            // Net Profit
            'net_profit'           => $netProfit,
            'net_margin_percent'   => $netMarginPercent,
        ]);
    }

    // =========================================================
    // ✅ BALANCE SHEET REPORT
    // READ-ONLY: Pure SELECT queries - no business logic altered.
    // =========================================================

    public function balanceSheet(Request $request)
    {
        $user    = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        $branches = $isSuper
            ? Branch::orderBy('name')->get()
            : ($user->branch_id ? Branch::where('id', $user->branch_id)->get() : collect());

        return view('admin_panel.reporting.balance_sheet', compact('branches', 'isSuper'));
    }

    public function fetchBalanceSheet(Request $request)
    {
        $user    = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        $branchId = null;
        if ($isSuper) {
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $branchId = (int) $request->branch_id;
            }
        } else {
            $branchId = $user->branch_id;
        }

        $asOfDate = $request->input('as_of_date', date('Y-m-d'));

        // ── 1. ASSETS ──────────────────────────────────────────

        // 1.2 Bank Balances (Head is Bank or code is BANK, excluding Expense)
        $bankAccounts = DB::table('accounts as a')
            ->join('account_heads as ah', 'ah.id', '=', 'a.head_id')
            ->where(function ($q) {
                $q->where('ah.name', 'Bank')
                  ->orWhere('a.account_code', 'like', '%-BANK-%');
            })
            ->where('ah.name', '!=', 'Expence')
            ->where('a.account_code', 'not like', '%-EXPE-%')
            ->where(function ($q) {
                $q->whereNull('a.status')->orWhereNotIn('a.status', [0, '0', 'inactive']);
            })
            ->when($branchId, fn($q) => $q->where('a.branch_id', $branchId))
            ->select('a.id', 'a.title', 'a.account_code', 'a.opening_balance', 'a.type')
            ->get();

        $bankAccountIds = $bankAccounts->pluck('id')->toArray();

        $bankList = [];
        $totalBank = 0;
        foreach ($bankAccounts as $ba) {
            $tx = DB::table('account_ledger_entries')
                ->where('account_id', $ba->id)
                ->where('voucher_type', '!=', 'opening_balance')
                ->when($asOfDate, fn($q) => $q->whereDate('transaction_date', '<=', $asOfDate))
                ->selectRaw('COALESCE(SUM(debit),0) as dr, COALESCE(SUM(credit),0) as cr')
                ->first();
            $bal = (float) $ba->opening_balance + (float) $tx->dr - (float) $tx->cr;
            if ($bal != 0) {
                $bankList[] = [
                    'title'   => $ba->title,
                    'code'    => $ba->account_code,
                    'balance' => $bal,
                ];
                $totalBank += $bal;
            }
        }

        // 1.1 Cash in Hand accounts (Head is Cash or code is CASH, strictly excluding Bank and Expense)
        $cashAccounts = DB::table('accounts as a')
            ->join('account_heads as ah', 'ah.id', '=', 'a.head_id')
            ->where(function ($q) {
                $q->where('ah.name', 'Cash')
                  ->orWhere('a.account_code', 'like', '%-CASH-%');
            })
            ->when(!empty($bankAccountIds), fn($q) => $q->whereNotIn('a.id', $bankAccountIds))
            ->where('ah.name', '!=', 'Expence')
            ->where('a.account_code', 'not like', '%-EXPE-%')
            ->where(function ($q) {
                $q->whereNull('a.status')->orWhereNotIn('a.status', [0, '0', 'inactive']);
            })
            ->when($branchId, fn($q) => $q->where('a.branch_id', $branchId))
            ->select('a.id', 'a.title', 'a.account_code', 'a.opening_balance', 'a.type')
            ->get();

        $cashList = [];
        $totalCash = 0;
        foreach ($cashAccounts as $ca) {
            $tx = DB::table('account_ledger_entries')
                ->where('account_id', $ca->id)
                ->where('voucher_type', '!=', 'opening_balance')
                ->when($asOfDate, fn($q) => $q->whereDate('transaction_date', '<=', $asOfDate))
                ->selectRaw('COALESCE(SUM(debit),0) as dr, COALESCE(SUM(credit),0) as cr')
                ->first();
            $bal = (float) $ca->opening_balance + (float) $tx->dr - (float) $tx->cr;
            if ($bal != 0) {
                $cashList[] = [
                    'title'   => $ca->title,
                    'code'    => $ca->account_code,
                    'balance' => $bal,
                ];
                $totalCash += $bal;
            }
        }

        // 1.3 Trade Receivables (Customers - Live Transactional exact match with /report/receivable)
        $recReq = new Request([
            'branch_id' => $branchId,
            'date_to'   => $asOfDate,
        ]);
        $recData = $this->fetch_receivable_report($recReq)->getData(true);
        $totalReceivables = (float) ($recData['kpi']['grand_closing'] ?? 0);

        // 1.4 Stock on Hand Valuation — exactly matching Item Stock Report
        // Per-product loop: warehouse_stocks qty * wholesale_price (with purchase_items fallback)
        $allProductsForStock = \App\Models\Product::select('id', 'wholesale_price', 'price')->get()->keyBy('id');
        $wsQtyMap = DB::table('warehouse_stocks')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id')
            ->pluck('total_qty', 'product_id');

        $productIdsNeedingPurchasePrice = [];
        $totalStock = 0;
        foreach ($wsQtyMap as $pid => $qty) {
            $p = $allProductsForStock[$pid] ?? null;
            $wPrice = (float)(($p->wholesale_price ?? 0));
            if ($wPrice <= 0) $wPrice = (float)(($p->price ?? 0));
            if ($wPrice <= 0) {
                $productIdsNeedingPurchasePrice[] = $pid;
            }
            $totalStock += (float)$qty * $wPrice;
        }
        // Fallback: for products with no price, use latest purchase_items price
        if (!empty($productIdsNeedingPurchasePrice)) {
            $latestPrices = DB::table('purchase_items')
                ->whereIn('product_id', $productIdsNeedingPurchasePrice)
                ->select('product_id', 'price')
                ->orderBy('id', 'desc')
                ->get()
                ->unique('product_id')
                ->keyBy('product_id');
            foreach ($productIdsNeedingPurchasePrice as $pid) {
                $qty = (float)($wsQtyMap[$pid] ?? 0);
                $fallback = (float)($latestPrices[$pid]->price ?? 0);
                $totalStock += $qty * $fallback;
            }
        }

        // 1.5 Loans & Advances
        $loanAccounts = DB::table('accounts as a')
            ->join('account_heads as ah', 'ah.id', '=', 'a.head_id')
            ->where('ah.name', 'like', '%loan%')
            ->where(function ($q) {
                $q->whereNull('a.status')->orWhereNotIn('a.status', [0, '0', 'inactive']);
            })
            ->when($branchId, fn($q) => $q->where('a.branch_id', $branchId))
            ->select('a.id', 'a.title', 'a.account_code', 'a.opening_balance', 'a.type')
            ->get();

        $loanList = [];
        $totalLoans = 0;
        foreach ($loanAccounts as $la) {
            $tx = DB::table('account_ledger_entries')
                ->where('account_id', $la->id)
                ->where('voucher_type', '!=', 'opening_balance')
                ->when($asOfDate, fn($q) => $q->whereDate('transaction_date', '<=', $asOfDate))
                ->selectRaw('COALESCE(SUM(debit),0) as dr, COALESCE(SUM(credit),0) as cr')
                ->first();
            $bal = (float) $la->opening_balance + (float) $tx->dr - (float) $tx->cr;
            if ($bal != 0) {
                $loanList[] = [
                    'title'   => $la->title,
                    'code'    => $la->account_code,
                    'balance' => $bal,
                ];
                $totalLoans += $bal;
            }
        }

        // 1.6 Other Assets
        $otherAssetAccounts = DB::table('accounts as a')
            ->join('account_heads as ah', 'ah.id', '=', 'a.head_id')
            ->whereNotIn('ah.name', ['Cash', 'Bank', 'Expence'])
            ->where('ah.name', 'not like', '%loan%')
            ->where(function ($q) {
                $q->whereNull('a.status')->orWhereNotIn('a.status', [0, '0', 'inactive']);
            })
            ->when($branchId, fn($q) => $q->where('a.branch_id', $branchId))
            ->select('a.id', 'a.title', 'a.account_code', 'a.opening_balance', 'a.type', 'ah.name as head_name')
            ->get();

        $otherAssetList = [];
        $totalOtherAssets = 0;
        foreach ($otherAssetAccounts as $oa) {
            $tx = DB::table('account_ledger_entries')
                ->where('account_id', $oa->id)
                ->where('voucher_type', '!=', 'opening_balance')
                ->when($asOfDate, fn($q) => $q->whereDate('transaction_date', '<=', $asOfDate))
                ->selectRaw('COALESCE(SUM(debit),0) as dr, COALESCE(SUM(credit),0) as cr')
                ->first();
            $isCredit = trim(strtolower($oa->type ?? 'debit')) === 'credit';
            $bal = $isCredit
                ? ((float) $oa->opening_balance + (float) $tx->cr - (float) $tx->dr)
                : ((float) $oa->opening_balance + (float) $tx->dr - (float) $tx->cr);
            if ($bal != 0) {
                $otherAssetList[] = [
                    'title'   => $oa->title,
                    'head'    => $oa->head_name,
                    'code'    => $oa->account_code,
                    'balance' => $bal,
                ];
                $totalOtherAssets += $bal;
            }
        }

        $totalAssets = $totalCash + $totalBank + $totalReceivables + $totalStock + $totalLoans + $totalOtherAssets;

        // ── 2. LIABILITIES ─────────────────────────────────────

        // 2.1 Trade Payables (Vendors / Suppliers - Live Transactional, exact match with /report/payable)
        $vendQ = DB::table('vendors as v');
        if ($branchId) {
            $vendQ->where('v.branch_id', $branchId);
        }
        $vendorsList = $vendQ->select('v.id', 'v.branch_id', 'v.opening_balance as initial_ob')->get();

        $totalPayables = 0;
        foreach ($vendorsList as $v) {
            $vId = (int)$v->id;
            $vBranch = $branchId ?: null;
            $openingBalance = (float)($v->initial_ob ?? 0);

            // Purchases (Credit)
            $pQ = DB::table('purchases')->where('vendor_id', $vId)->whereNull('deleted_at');
            if ($vBranch) $pQ->where('branch_id', $vBranch);
            if ($asOfDate) $pQ->whereDate('created_at', '<=', $asOfDate);
            $purchases = (float)$pQ->sum(DB::raw('COALESCE(net_amount, 0)'));

            // Purchase Returns (Debit)
            $rQ = DB::table('purchase_returns')->where('vendor_id', $vId);
            if ($asOfDate) $rQ->whereDate('created_at', '<=', $asOfDate);
            $returns = (float)$rQ->sum('net_amount');

            // Payments (Debit)
            $pvQ = DB::table('payment_vouchers')->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            });
            if ($asOfDate) $pvQ->whereDate('receipt_date', '<=', $asOfDate);
            \App\Services\VoucherService::scopePartyQuery($pvQ, $vId, 'party_id');
            $payments = 0;
            foreach ($pvQ->get() as $pv) {
                $payments += \App\Services\VoucherService::getPartyAmount($pv, $vId, 'vendor');
            }

            // Receipts / Refunds (Credit)
            $rvQ = DB::table('receipts_vouchers')->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'voided');
            });
            if ($asOfDate) $rvQ->whereDate('receipt_date', '<=', $asOfDate);
            \App\Services\VoucherService::scopePartyQuery($rvQ, $vId, 'party_id');
            $refunds = 0;
            foreach ($rvQ->get() as $rv) {
                $refunds += \App\Services\VoucherService::getPartyAmount($rv, $vId, 'vendor');
            }

            // Journal Vouchers
            $jvDebQ = DB::table('journal_vouchers')->where('debit_party_type', 'vendor')->where('debit_party_id', $vId)
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'voided'); });
            if ($asOfDate) $jvDebQ->whereDate('voucher_date', '<=', $asOfDate);
            $jvDeb = (float)$jvDebQ->sum('amount');

            $jvCredQ = DB::table('journal_vouchers')->where('credit_party_type', 'vendor')->where('credit_party_id', $vId)
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'voided'); });
            if ($asOfDate) $jvCredQ->whereDate('voucher_date', '<=', $asOfDate);
            $jvCred = (float)$jvCredQ->sum('amount');

            $closingBalance = $openingBalance + ($purchases + $refunds + $jvCred) - ($returns + $payments + $jvDeb);
            $totalPayables += $closingBalance;
        }

        $totalLiabilities = $totalPayables;

        // ── 3. EQUITY & RESERVES ───────────────────────────────

        // 3.1 Current Period Net Profit (Synchronized 100% with Income Statement authoritative Net Profit)
        $isReq = new Request([
            'branch_id' => $branchId,
            'date_to'   => $asOfDate,
        ]);
        $isData = $this->fetchIncomeStatement($isReq)->getData(true);
        $retainedEarnings = (float) ($isData['net_profit'] ?? 0);

        // Total Equity = Total Assets - Total Liabilities
        $totalEquity = $totalAssets - $totalLiabilities;
        $ownersCapital = $totalEquity - $retainedEarnings;

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        $isBalanced = abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01;
        $workingCapital = ($totalCash + $totalBank + $totalReceivables + $totalStock) - $totalLiabilities;

        return response()->json([
            'as_of_date'                => $asOfDate,
            'branch_label'              => $branchId ? (Branch::find($branchId)?->name ?? 'Branch') : 'All Branches',
            // Assets
            'cash_list'                 => $cashList,
            'total_cash'                => $totalCash,
            'bank_list'                 => $bankList,
            'total_bank'                => $totalBank,
            'total_receivables'         => $totalReceivables,
            'total_stock'               => $totalStock,
            'loan_list'                 => $loanList,
            'total_loans'               => $totalLoans,
            'other_assets_list'         => $otherAssetList,
            'total_other_assets'        => $totalOtherAssets,
            'total_assets'              => $totalAssets,
            // Liabilities
            'total_payables'            => $totalPayables,
            'total_liabilities'         => $totalLiabilities,
            // Equity
            'owners_capital'            => $ownersCapital,
            'retained_earnings'         => $retainedEarnings,
            'total_equity'              => $totalEquity,
            'total_liabilities_equity'  => $totalLiabilitiesAndEquity,
            // Status
            'is_balanced'               => $isBalanced,
            'working_capital'           => $workingCapital,
        ]);
    }

    // =========================================================================
    //  ACCOUNTS RECEIVABLE REPORT (CUSTOMER RECEIVABLES / AGING) - READ-ONLY
    // =========================================================================

    /**
     * Display the Accounts Receivable Report view.
     */
    public function receivable_report(Request $request)
    {
        $user = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        if ($isSuper) {
            $branches = Branch::orderBy('name')->get();
        } else {
            $branches = $user->branch_id ? Branch::where('id', $user->branch_id)->get() : collect();
        }

        $defaultBranchId = $isSuper ? null : $user->branch_id;

        // Distinct non-empty zones from customers
        $zones = Customer::query()
            ->when($defaultBranchId, fn($q) => $q->where('branch_id', $defaultBranchId))
            ->whereNotNull('zone')
            ->where('zone', '!=', '')
            ->distinct()
            ->orderBy('zone')
            ->pluck('zone');

        // Customers list for dropdown filter
        $customers = Customer::query()
            ->when($defaultBranchId, fn($q) => $q->where('branch_id', $defaultBranchId))
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['0', 'inactive']);
            })
            ->select('id', 'customer_id', 'customer_name', 'customer_name_ur', 'branch_id')
            ->orderBy('customer_name')
            ->get();

        return view('admin_panel.reporting.receivable_report', compact(
            'branches',
            'isSuper',
            'zones',
            'customers',
            'defaultBranchId'
        ));
    }

    /**
     * AJAX endpoint: Fetch Accounts Receivable and Aging data (READ-ONLY).
     */
    public function fetch_receivable_report(Request $request)
    {
        $user    = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        // Branch restriction
        $branchId = null;
        if ($isSuper) {
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $branchId = (int)$request->branch_id;
            }
        } else {
            $branchId = $user->branch_id ? (int)$user->branch_id : null;
        }

        $zone        = ($request->filled('zone') && $request->zone !== 'all') ? $request->zone : null;
        $customerId  = ($request->filled('customer_id') && $request->customer_id !== 'all') ? (int)$request->customer_id : null;
        $balanceType = $request->input('balance_type', 'all');
        $dateFrom    = $request->input('date_from');
        $dateTo      = $request->input('date_to') ?: date('Y-m-d');

        $today = \Carbon\Carbon::today();
        $asOf  = \Carbon\Carbon::parse($dateTo)->endOfDay();

        // ── Build Customer List ──────────────────────────────────────────
        $customerQuery = DB::table('customers as c')
            ->leftJoin('branches as b', 'b.id', '=', 'c.branch_id')
            ->select(
                'c.id', 'c.branch_id', 'c.customer_id as code',
                'c.customer_name', 'c.customer_name_ur', 'c.contact_person',
                'c.mobile', 'c.zone', 'c.credit_limit', 'c.no_credit_limit',
                'c.opening_balance as initial_ob', 'c.status',
                'b.name as branch_name'
            );

        if ($branchId) {
            $customerQuery->where('c.branch_id', $branchId);
        }
        if ($zone) {
            $customerQuery->where('c.zone', $zone);
        }
        if ($customerId) {
            $customerQuery->where('c.id', $customerId);
        }

        $rawRows = $customerQuery->orderBy('c.customer_name')->get();

        // ── Check if purchases.customer_id column exists (for contra) ───
        $hasCustIdOnPurchases = \Illuminate\Support\Facades\Schema::hasColumn('purchases', 'customer_id');

        // ── Per-customer live calculation ────────────────────────────────
        $processedRows = [];
        foreach ($rawRows as $r) {
            $cId = $r->id;

            // Opening Balance
            if ($dateFrom) {
                $openingBalance = $this->getCustomerOpeningBalance($cId, $dateFrom);
            } else {
                $openingBalance = (float)($r->initial_ob ?? 0);
            }

            // ── Period Sales (Debit) ─────────────────────────────────────
            $periodSalesRaw = DB::table('sales')
                ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.customer_id', $cId)
                ->where(function ($q) {
                    $q->whereNull('sales.status')->orWhere('sales.status', '!=', 'cancelled');
                })
                ->select(
                    'sales.id',
                    'sales.total_net',
                    'sales.additional_discount',
                    'sales.extra_charges',
                    DB::raw('COALESCE(SUM(sale_items.amount), 0) as items_sum'),
                    DB::raw('COUNT(sale_items.id) as items_count')
                )
                ->groupBy('sales.id', 'sales.total_net', 'sales.additional_discount', 'sales.extra_charges');

            if ($dateFrom) {
                $periodSalesRaw->whereBetween(DB::raw('DATE(sales.created_at)'), [$dateFrom, $dateTo]);
            } else {
                $periodSalesRaw->whereDate('sales.created_at', '<=', $dateTo);
            }
            $periodSalesRaw = $periodSalesRaw->get();

            $periodSales = 0;
            foreach ($periodSalesRaw as $ps) {
                $itemsSum = (float)$ps->items_sum;
                $addDisc  = (float)($ps->additional_discount ?? 0);
                $extraChg = (float)($ps->extra_charges ?? 0);
                if ($ps->items_count > 0 && $itemsSum > 0) {
                    $periodSales += max(0, $itemsSum - $addDisc + $extraChg);
                } else {
                    $periodSales += (float)($ps->total_net ?? 0);
                }
            }

            // ── Period Returns (Credit) ──────────────────────────────────
            $returnsQ = DB::table('sales_returns')
                ->join('sales', 'sales.id', '=', 'sales_returns.sale_id')
                ->where('sales.customer_id', $cId);
            if ($dateFrom) {
                $returnsQ->whereBetween(DB::raw('DATE(sales_returns.created_at)'), [$dateFrom, $dateTo]);
            } else {
                $returnsQ->whereDate('sales_returns.created_at', '<=', $dateTo);
            }
            $periodReturns = (float)$returnsQ->sum('sales_returns.total_net');

            // ── Period Refunds (Debit – cash refunds on returns) ─────────
            $refundsQ = DB::table('account_ledger_entries')
                ->join('sales_returns', 'sales_returns.id', '=', 'account_ledger_entries.voucher_id')
                ->join('sales', 'sales.id', '=', 'sales_returns.sale_id')
                ->where('sales.customer_id', $cId)
                ->where('account_ledger_entries.voucher_type', 'payment')
                ->where('account_ledger_entries.voucher_no', 'like', 'RET-%');
            if ($dateFrom) {
                $refundsQ->whereBetween(DB::raw('DATE(account_ledger_entries.transaction_date)'), [$dateFrom, $dateTo]);
            } else {
                $refundsQ->whereDate('account_ledger_entries.transaction_date', '<=', $dateTo);
            }
            $periodRefunds = (float)$refundsQ->sum('account_ledger_entries.credit');

            // ── Period Receipts (Credit) ─────────────────────────────────
            $receiptsQ = DB::table('receipts_vouchers')
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'voided');
                });
            if ($dateFrom) {
                $receiptsQ->whereBetween(DB::raw('DATE(receipt_date)'), [$dateFrom, $dateTo]);
            } else {
                $receiptsQ->whereDate('receipt_date', '<=', $dateTo);
            }
            \App\Services\VoucherService::scopePartyQuery($receiptsQ, $cId, 'party_id');
            $receiptsRaw   = $receiptsQ->get();
            $periodReceipts = 0;
            foreach ($receiptsRaw as $rec) {
                $periodReceipts += \App\Services\VoucherService::getPartyAmount($rec, $cId, 'customer');
            }

            // ── Period Payments (Credit – customer rebates/discounts) ────
            $paymentsQ = DB::table('payment_vouchers')
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'voided');
                });
            if ($dateFrom) {
                $paymentsQ->whereBetween(DB::raw('DATE(receipt_date)'), [$dateFrom, $dateTo]);
            } else {
                $paymentsQ->whereDate('receipt_date', '<=', $dateTo);
            }
            \App\Services\VoucherService::scopePartyQuery($paymentsQ, $cId, 'party_id');
            $paymentsRaw   = $paymentsQ->get();
            $periodPayments = 0;
            foreach ($paymentsRaw as $pv) {
                $periodPayments += \App\Services\VoucherService::getPartyAmount($pv, $cId, 'customer');
            }

            // ── Period JV Debits & Credits ───────────────────────────────
            $jvDebQ = DB::table('journal_vouchers')
                ->where('debit_party_type', 'customer')
                ->where('debit_party_id', $cId)
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'voided'); });
            $jvCredQ = DB::table('journal_vouchers')
                ->where('credit_party_type', 'customer')
                ->where('credit_party_id', $cId)
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'voided'); });
            if ($dateFrom) {
                $jvDebQ->whereBetween(DB::raw('DATE(voucher_date)'), [$dateFrom, $dateTo]);
                $jvCredQ->whereBetween(DB::raw('DATE(voucher_date)'), [$dateFrom, $dateTo]);
            } else {
                $jvDebQ->whereDate('voucher_date', '<=', $dateTo);
                $jvCredQ->whereDate('voucher_date', '<=', $dateTo);
            }
            $periodJvDeb  = (float)$jvDebQ->sum('amount');
            $periodJvCred = (float)$jvCredQ->sum('amount');

            // ── Period Contra Purchases (Credit) ─────────────────────────
            $periodCustPur = 0;
            if ($hasCustIdOnPurchases) {
                $cpQ = DB::table('purchases')
                    ->where('customer_id', $cId)
                    ->whereNull('deleted_at');
                if ($dateFrom) {
                    $cpQ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
                } else {
                    $cpQ->whereDate('created_at', '<=', $dateTo);
                }
                $periodCustPur = (float)$cpQ->sum(DB::raw('COALESCE(net_amount, 0)'));
            }

            // ── Totals ───────────────────────────────────────────────────
            $periodDebit  = $periodSales + $periodRefunds + $periodJvDeb;
            $periodCredit = $periodReturns + $periodReceipts + $periodPayments + $periodJvCred + $periodCustPur;
            $closingBalance = $openingBalance + $periodDebit - $periodCredit;

            // ── Last Receipt Date (from live receipts_vouchers) ──────────
            $lastReceiptDate   = null;
            $lastReceiptAmount = 0;
            $lastRecQ = DB::table('receipts_vouchers')
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'voided'); })
                ->whereDate('receipt_date', '<=', $dateTo);
            \App\Services\VoucherService::scopePartyQuery($lastRecQ, $cId, 'party_id');
            $lastRecRow = $lastRecQ->orderBy('receipt_date', 'desc')->orderBy('id', 'desc')->first();
            if ($lastRecRow) {
                $lastReceiptDate   = $lastRecRow->receipt_date;
                $lastReceiptAmount = \App\Services\VoucherService::getPartyAmount($lastRecRow, $cId, 'customer');
            }

            // ── Last Sale Date (from live sales table) ───────────────────
            $lastSaleDate   = null;
            $lastSaleAmount = 0;
            $lastSaleRow = DB::table('sales')
                ->where('customer_id', $cId)
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'cancelled'); })
                ->whereDate('created_at', '<=', $dateTo)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->select('created_at', 'total_net')
                ->first();
            if ($lastSaleRow) {
                $lastSaleDate   = $lastSaleRow->created_at;
                $lastSaleAmount = (float)($lastSaleRow->total_net ?? 0);
            }

            $r->_opening  = $openingBalance;
            $r->_debit    = $periodDebit;
            $r->_credit   = $periodCredit;
            $r->_closing  = $closingBalance;
            $r->_lr_date  = $lastReceiptDate;
            $r->_lr_amt   = $lastReceiptAmount;
            $r->_ls_date  = $lastSaleDate;
            $r->_ls_amt   = $lastSaleAmount;
            $processedRows[] = $r;
        }

        // ── FIFO Aging from live sales table ─────────────────────────────
        $customerIdsWithDebit = [];
        $balancesMap = [];
        foreach ($processedRows as $r) {
            if ($r->_closing > 0) {
                $customerIdsWithDebit[] = $r->id;
                $balancesMap[$r->id]    = $r->_closing;
            }
        }

        $agingMap = [];
        if (!empty($customerIdsWithDebit)) {
            $agingInvoicesQ = DB::table('sales')
                ->whereIn('customer_id', $customerIdsWithDebit)
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'cancelled'); })
                ->whereDate('created_at', '<=', $dateTo)
                ->select('customer_id', 'total_net', 'created_at')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc');

            if ($dateFrom) {
                // For aging we still want all invoices up to dateTo (not just period)
                // because closing balance may include older invoices
            }

            $agingInvoices = $agingInvoicesQ->get()->groupBy('customer_id');

            foreach ($customerIdsWithDebit as $cid) {
                $bal = $balancesMap[$cid] ?? 0;
                $rem = $bal;
                $c0 = 0.0; $c30 = 0.0; $c60 = 0.0; $c90 = 0.0;

                if (isset($agingInvoices[$cid])) {
                    foreach ($agingInvoices[$cid] as $inv) {
                        if ($rem <= 0) break;
                        $days = \Carbon\Carbon::parse($inv->created_at)->diffInDays($asOf);
                        $amt  = min((float)($inv->total_net ?? 0), $rem);
                        if ($amt <= 0) continue;

                        if ($days <= 30)       { $c0  += $amt; }
                        elseif ($days <= 60)   { $c30 += $amt; }
                        elseif ($days <= 90)   { $c60 += $amt; }
                        else                   { $c90 += $amt; }
                        $rem -= $amt;
                    }
                }

                if ($rem > 0) {
                    $c90 += $rem;
                }

                $agingMap[$cid] = [
                    'c_0_30'    => $c0,
                    'c_31_60'   => $c30,
                    'c_61_90'   => $c60,
                    'c_90_plus' => $c90,
                ];
            }
        }

        // Process rows and KPI totals
        $rows = [];
        $totalReceivable = 0;
        $totalAdvance = 0;
        $grandOpening = 0;
        $grandDebit = 0;
        $grandCredit = 0;
        $overLimitCount = 0;
        $overLimitAmount = 0;
        $agingTotals = [
            'c_0_30'    => 0,
            'c_31_60'   => 0,
            'c_61_90'   => 0,
            'c_90_plus' => 0,
        ];

        foreach ($processedRows as $r) {
            $openingBalance = $r->_opening;
            $periodDebit    = $r->_debit;
            $periodCredit   = $r->_credit;
            $closingBalance = $r->_closing;

            $creditLimit = (float)($r->credit_limit ?? 0);
            $hasLimit    = empty($r->no_credit_limit) && $creditLimit > 0;
            $isOverLimit = $hasLimit && ($closingBalance > $creditLimit);
            $overLimitDiff = $isOverLimit ? ($closingBalance - $creditLimit) : 0;

            if ($isOverLimit) {
                $overLimitCount++;
                $overLimitAmount += $overLimitDiff;
            }

            // Filter by Balance Type
            if ($balanceType === 'receivable' && $closingBalance <= 0) continue;
            if ($balanceType === 'advance' && $closingBalance >= 0) continue;
            if ($balanceType === 'with_balance' && $closingBalance == 0) continue;
            if ($balanceType === 'over_limit' && !$isOverLimit) continue;
            if ($balanceType === 'zero' && $closingBalance != 0) continue;

            if ($closingBalance > 0) {
                $totalReceivable += $closingBalance;
            } else {
                $totalAdvance += abs($closingBalance);
            }

            $grandOpening += $openingBalance;
            $grandDebit   += $periodDebit;
            $grandCredit  += $periodCredit;

            // Days since last receipt
            $daysSinceReceipt = null;
            if ($r->_lr_date) {
                $daysSinceReceipt = $today->diffInDays(\Carbon\Carbon::parse($r->_lr_date)->startOfDay());
            }

            // Days since last sale
            $daysSinceSale = null;
            if ($r->_ls_date) {
                $daysSinceSale = $today->diffInDays(\Carbon\Carbon::parse($r->_ls_date)->startOfDay());
            }

            // Status label & badge
            $statusBadge = 'success';
            $statusText = 'Cleared / Nil';
            if ($isOverLimit) {
                $statusBadge = 'danger';
                $statusText = 'Over Limit (+' . number_format($overLimitDiff, 0) . ')';
            } elseif ($closingBalance > 0) {
                $statusBadge = 'primary';
                $statusText = 'Receivable';
            } elseif ($closingBalance < 0) {
                $statusBadge = 'info';
                $statusText = 'Advance';
            }

            $agingData = $agingMap[$r->id] ?? ['c_0_30' => 0, 'c_31_60' => 0, 'c_61_90' => 0, 'c_90_plus' => 0];
            $agingTotals['c_0_30']    += $agingData['c_0_30'];
            $agingTotals['c_31_60']   += $agingData['c_31_60'];
            $agingTotals['c_61_90']   += $agingData['c_61_90'];
            $agingTotals['c_90_plus'] += $agingData['c_90_plus'];

            $cleanPhone = preg_replace('/[^0-9]/', '', $r->mobile ?? '');
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '92' . substr($cleanPhone, 1);
            }

            $rows[] = [
                'id'                  => $r->id,
                'code'                => $r->code,
                'customer_name'       => $r->customer_name,
                'customer_name_ur'    => $r->customer_name_ur ?? null,
                'contact_person'      => $r->contact_person ?? null,
                'mobile'              => $r->mobile,
                'whatsapp_phone'      => $cleanPhone,
                'zone'                => $r->zone ?: '—',
                'branch_name'         => $r->branch_name ?: '—',
                'credit_limit'        => $creditLimit,
                'no_credit_limit'     => !empty($r->no_credit_limit),
                'opening_balance'     => $openingBalance,
                'period_debit'        => $periodDebit,
                'period_credit'       => $periodCredit,
                'closing_balance'     => $closingBalance,
                'is_over_limit'       => $isOverLimit,
                'over_limit_diff'     => $overLimitDiff,
                'status_badge'        => $statusBadge,
                'status_text'         => $statusText,
                'aging'               => $agingData,
                'last_receipt_date'   => $r->_lr_date ? \Carbon\Carbon::parse($r->_lr_date)->format('d M Y') : '—',
                'last_receipt_amount' => (float)($r->_lr_amt ?? 0),
                'days_since_receipt'  => $daysSinceReceipt,
                'last_sale_date'      => $r->_ls_date ? \Carbon\Carbon::parse($r->_ls_date)->format('d M Y') : '—',
                'last_sale_amount'    => (float)($r->_ls_amt ?? 0),
                'days_since_sale'     => $daysSinceSale,
                'ledger_url'          => route('report.customer.ledger.new', ['customer_id' => $r->id]),
            ];
        }

        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Branch') : 'All Branches';

        return response()->json([
            'rows' => $rows,
            'kpi'  => [
                'total_receivable'  => $totalReceivable,
                'total_advance'     => $totalAdvance,
                'net_outstanding'   => $totalReceivable - $totalAdvance,
                'grand_opening'     => $grandOpening,
                'grand_debit'       => $grandDebit,
                'grand_credit'      => $grandCredit,
                'grand_closing'     => $totalReceivable - $totalAdvance,
                'customers_count'   => count($rows),
                'over_limit_count'  => $overLimitCount,
                'over_limit_amount' => $overLimitAmount,
                'aging_totals'      => $agingTotals,
            ],
            'meta' => [
                'branch_label' => $branchName,
                'date_from'    => $dateFrom,
                'date_to'      => $dateTo,
                'balance_type' => $balanceType,
            ]
        ]);
    }

    // =========================================================================
    //  ACCOUNTS PAYABLE REPORT (VENDOR PAYABLES) - READ-ONLY
    // =========================================================================

    /**
     * Display the Accounts Payable Report view.
     */
    public function payable_report(Request $request)
    {
        $user = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        if ($isSuper) {
            $branches = Branch::orderBy('name')->get();
        } else {
            $branches = $user->branch_id ? Branch::where('id', $user->branch_id)->get() : collect();
        }

        $defaultBranchId = $isSuper ? null : $user->branch_id;

        // Vendors list for dropdown filter
        $vendors = Vendor::query()
            ->when($defaultBranchId, fn($q) => $q->where('branch_id', $defaultBranchId))
            ->select('id', 'name', 'branch_id', 'phone')
            ->orderBy('name')
            ->get();

        return view('admin_panel.reporting.payable_report', compact(
            'branches',
            'isSuper',
            'vendors',
            'defaultBranchId'
        ));
    }

    /**
     * AJAX endpoint: Fetch Accounts Payable data (READ-ONLY).
     */
    public function fetch_payable_report(Request $request)
    {
        $user    = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        // Branch restriction
        $branchId = null;
        if ($isSuper) {
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $branchId = (int)$request->branch_id;
            }
        } else {
            $branchId = $user->branch_id ? (int)$user->branch_id : null;
        }

        $vendorId = ($request->filled('vendor_id') && $request->vendor_id !== 'all') ? (int)$request->vendor_id : null;
        $dateFrom = $request->input('date_from'); // optional Y-m-d
        $dateTo   = $request->input('date_to') ?: date('Y-m-d'); // defaults to today

        // Build main vendor query (aligned exactly with /vendors-ledger authoritative logic)
        $vendorQuery = DB::table('vendors as v')
            ->leftJoin('branches as b', 'b.id', '=', 'v.branch_id')
            ->select(
                'v.id',
                'v.branch_id',
                'v.name as vendor_name',
                'v.phone',
                'v.opening_balance as initial_ob',
                'b.name as branch_name'
            );

        if ($branchId) {
            $vendorQuery->where('v.branch_id', $branchId);
        }

        if ($vendorId) {
            $vendorQuery->where('v.id', $vendorId);
        }

        $vendors = $vendorQuery->orderBy('v.name')->get();

        $rows = [];
        $grandOpening = 0;
        $grandDebit   = 0;
        $grandCredit  = 0;
        $grandClosing = 0;

        foreach ($vendors as $v) {
            $vId = (int)$v->id;
            $vBranch = $branchId ?: null;

            // 1. Mathematically exact opening balance matching /vendors-ledger
            $openingBalance = $dateFrom 
                ? $this->getVendorOpeningBalance($vId, $vBranch, $dateFrom) 
                : (float)($v->initial_ob ?? 0);

            // 2. Period Purchases (Credit)
            $pQ = DB::table('purchases')
                ->where('vendor_id', $vId)
                ->whereNull('deleted_at');
            if ($vBranch) $pQ->where('branch_id', $vBranch);
            if ($dateFrom) $pQ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            else $pQ->whereDate('created_at', '<=', $dateTo);
            $purchases = (float)$pQ->sum(DB::raw('COALESCE(net_amount, 0)'));

            // 3. Period Purchase Returns (Debit)
            $rQ = DB::table('purchase_returns')->where('vendor_id', $vId);
            if ($dateFrom) $rQ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            else $rQ->whereDate('created_at', '<=', $dateTo);
            $returns = (float)$rQ->sum('net_amount');

            // 4. Period Payments (Debit)
            $pvQ = DB::table('payment_vouchers')
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'voided'); });
            if ($dateFrom) $pvQ->whereBetween(DB::raw('DATE(receipt_date)'), [$dateFrom, $dateTo]);
            else $pvQ->whereDate('receipt_date', '<=', $dateTo);
            \App\Services\VoucherService::scopePartyQuery($pvQ, $vId, 'party_id');
            $payments = 0;
            foreach ($pvQ->get() as $pv) {
                $payments += \App\Services\VoucherService::getPartyAmount($pv, $vId, 'vendor');
            }

            // 5. Period Receipts / Refunds (Credit)
            $rvQ = DB::table('receipts_vouchers')
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'voided'); });
            if ($dateFrom) $rvQ->whereBetween(DB::raw('DATE(receipt_date)'), [$dateFrom, $dateTo]);
            else $rvQ->whereDate('receipt_date', '<=', $dateTo);
            \App\Services\VoucherService::scopePartyQuery($rvQ, $vId, 'party_id');
            $refunds = 0;
            foreach ($rvQ->get() as $rv) {
                $refunds += \App\Services\VoucherService::getPartyAmount($rv, $vId, 'vendor');
            }

            // 6. Period Journal Vouchers
            $jvDebQ = DB::table('journal_vouchers')->where('debit_party_type', 'vendor')->where('debit_party_id', $vId)
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'voided'); });
            if ($dateFrom) $jvDebQ->whereBetween(DB::raw('DATE(voucher_date)'), [$dateFrom, $dateTo]);
            else $jvDebQ->whereDate('voucher_date', '<=', $dateTo);
            $jvDeb = (float)$jvDebQ->sum('amount');

            $jvCredQ = DB::table('journal_vouchers')->where('credit_party_type', 'vendor')->where('credit_party_id', $vId)
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'voided'); });
            if ($dateFrom) $jvCredQ->whereBetween(DB::raw('DATE(voucher_date)'), [$dateFrom, $dateTo]);
            else $jvCredQ->whereDate('voucher_date', '<=', $dateTo);
            $jvCred = (float)$jvCredQ->sum('amount');

            $periodCredit   = $purchases + $refunds + $jvCred;
            $periodDebit    = $returns + $payments + $jvDeb;
            $closingBalance = $openingBalance + $periodCredit - $periodDebit;

            $grandOpening += $openingBalance;
            $grandDebit   += $periodDebit;
            $grandCredit  += $periodCredit;
            $grandClosing += $closingBalance;

            $rows[] = [
                'id'              => $v->id,
                'code'            => 'VEND-' . str_pad($v->id, 4, '0', STR_PAD_LEFT),
                'vendor_name'     => $v->vendor_name,
                'phone'           => $v->phone,
                'branch_name'     => $v->branch_name ?: '—',
                'opening_balance' => $openingBalance,
                'period_credit'   => $periodCredit, // Purchases
                'period_debit'    => $periodDebit,  // Paid
                'closing_balance' => $closingBalance, // Payable
                'ledger_url'      => route('report.vendor.ledger.new', ['vendor_id' => $v->id]),
            ];
        }

        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Branch') : 'All Branches';

        return response()->json([
            'rows' => $rows,
            'kpi'  => [
                'grand_opening' => $grandOpening,
                'grand_credit'  => $grandCredit, // Purchases
                'grand_debit'   => $grandDebit,  // Paid
                'grand_closing' => $grandClosing, // Payable
                'vendors_count' => count($rows),
            ],
            'meta' => [
                'branch_label' => $branchName,
                'date_from'    => $dateFrom,
                'date_to'      => $dateTo,
            ]
        ]);
    }
}

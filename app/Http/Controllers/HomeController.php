<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\PurchaseReturn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function testingform(Request $request)
    {
        return view('admin_panel.sale.testing_form');
    }

    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $usertype = $user->usertype;

        if ($usertype == 'user') {
            return view('user_panel.dashboard', ['userId' => $user->id]);
        } elseif ($usertype == 'admin' || $user->hasRole('admin') || $user->hasRole('super admin')) {
            $isSuperAdmin = $user->hasRole('super admin');
            $branches = Branch::select('id', 'name')->get();

            // Branch filter logic
            $selectedBranchId = null;
            if ($isSuperAdmin) {
                $selectedBranchId = $request->get('branch_id');
            } else {
                $selectedBranchId = $user->branch_id ?? 1;
            }

            $currentBranchName = 'All Branches';
            if ($selectedBranchId) {
                $branchObj = $branches->firstWhere('id', $selectedBranchId);
                $currentBranchName = $branchObj ? $branchObj->name : 'Selected Branch';
            }

            // Stats Counters
            $categoryCount = DB::table('categories')->count();
            $subcategoryCount = DB::table('subcategories')->count();
            $productCount = DB::table('products')->count();

            $customersQuery = DB::table('customers');
            $purchasesQuery = DB::table('purchases');
            $purchaseReturnsQuery = DB::table('purchase_returns');
            $salesQuery = DB::table('sales');
            $salesReturnsQuery = DB::table('sales_returns');
            $expensesQuery = DB::table('expense_vouchers');

            if ($selectedBranchId) {
                $customersQuery->where('branch_id', $selectedBranchId);
                $purchasesQuery->where('branch_id', $selectedBranchId);
                $salesQuery->where('branch_id', $selectedBranchId);

                $purchaseReturnsQuery->whereExists(function ($query) use ($selectedBranchId) {
                    $query->select(DB::raw(1))
                          ->from('purchases')
                          ->whereColumn('purchases.id', 'purchase_returns.purchase_id')
                          ->where('purchases.branch_id', $selectedBranchId);
                });

                $salesReturnsQuery->whereExists(function ($query) use ($selectedBranchId) {
                    $query->select(DB::raw(1))
                          ->from('sales')
                          ->whereColumn('sales.id', 'sales_returns.sale_id')
                          ->where('sales.branch_id', $selectedBranchId);
                });
            }

            $customerscount = $customersQuery->count();
            $totalPurchases = (float) $purchasesQuery->sum('net_amount');
            $totalPurchaseReturns = (float) $purchaseReturnsQuery->sum('net_amount');
            $totalSales = (float) $salesQuery->sum('total_net');
            $totalSalesReturns = (float) $salesReturnsQuery->sum('total_net');
            $totalExpenses = (float) ($expensesQuery->sum('total_amount') ?: $expensesQuery->sum('amount') ?: 0);

            // Today's, This Month's & Last Month's KPIs
            $today = Carbon::today()->format('Y-m-d');
            $thisMonth = Carbon::now()->month;
            $thisYear = Carbon::now()->year;

            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

            // Today
            $todaySalesQuery = DB::table('sales')->whereDate('created_at', $today);
            $todayPurchasesQuery = DB::table('purchases')->whereDate('created_at', $today);
            $todayExpensesQuery = DB::table('expense_vouchers')->whereDate('created_at', $today);

            // This Month
            $monthSalesQuery = DB::table('sales')->whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear);
            $monthPurchasesQuery = DB::table('purchases')->whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear);
            $monthExpensesQuery = DB::table('expense_vouchers')->whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear);

            // Last Month
            $lastMonthSalesQuery = DB::table('sales')->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd]);
            $lastMonthPurchasesQuery = DB::table('purchases')->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd]);
            $lastMonthExpensesQuery = DB::table('expense_vouchers')->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd]);

            if ($selectedBranchId) {
                $todaySalesQuery->where('branch_id', $selectedBranchId);
                $todayPurchasesQuery->where('branch_id', $selectedBranchId);
                $monthSalesQuery->where('branch_id', $selectedBranchId);
                $monthPurchasesQuery->where('branch_id', $selectedBranchId);
                $lastMonthSalesQuery->where('branch_id', $selectedBranchId);
                $lastMonthPurchasesQuery->where('branch_id', $selectedBranchId);
            }

            $todaySales = (float) $todaySalesQuery->sum('total_net');
            $todayPurchases = (float) $todayPurchasesQuery->sum('net_amount');
            $todayExpenses = (float) ($todayExpensesQuery->sum('total_amount') ?: $todayExpensesQuery->sum('amount') ?: 0);

            $monthSales = (float) $monthSalesQuery->sum('total_net');
            $monthPurchases = (float) $monthPurchasesQuery->sum('net_amount');
            $monthExpenses = (float) ($monthExpensesQuery->sum('total_amount') ?: $monthExpensesQuery->sum('amount') ?: 0);

            $lastMonthSales = (float) $lastMonthSalesQuery->sum('total_net');
            $lastMonthPurchases = (float) $lastMonthPurchasesQuery->sum('net_amount');
            $lastMonthExpenses = (float) ($lastMonthExpensesQuery->sum('total_amount') ?: $lastMonthExpensesQuery->sum('amount') ?: 0);

            // Sales & Purchases Growth % vs Last Month
            $salesGrowth = $lastMonthSales > 0 ? (($monthSales - $lastMonthSales) / $lastMonthSales) * 100 : ($monthSales > 0 ? 100 : 0);
            $purchasesGrowth = $lastMonthPurchases > 0 ? (($monthPurchases - $lastMonthPurchases) / $lastMonthPurchases) * 100 : ($monthPurchases > 0 ? 100 : 0);

            // Daily Labels & Dual Chart Data (Last 7 Days)
            $dailyLabels = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i)->format('Y-m-d'));
            $dailyFormattedLabels = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i)->format('d M'));
            
            $dailySalesData = $dailyLabels->map(function ($date) use ($selectedBranchId) {
                $q = DB::table('sales')->whereDate('created_at', $date);
                if ($selectedBranchId) $q->where('branch_id', $selectedBranchId);
                return (float) $q->sum('total_net');
            });

            $dailyPurchaseData = $dailyLabels->map(function ($date) use ($selectedBranchId) {
                $q = DB::table('purchases')->whereDate('created_at', $date);
                if ($selectedBranchId) $q->where('branch_id', $selectedBranchId);
                return (float) $q->sum('net_amount');
            });

            $dailyExpenseData = $dailyLabels->map(function ($date) {
                $q = DB::table('expense_vouchers')->whereDate('created_at', $date);
                return (float) ($q->sum('total_amount') ?: $q->sum('amount') ?: 0);
            });

            // Weekly & Monthly Sales/Purchase Data
            $weeklyLabels = ['2 Weeks Ago', 'Last Week', 'This Week'];
            $weeklySalesData = collect([2, 1, 0])->map(function ($i) use ($selectedBranchId) {
                $start = Carbon::now()->startOfWeek()->subWeeks($i);
                $end = $start->copy()->endOfWeek();
                $q = DB::table('sales')->whereBetween('created_at', [$start, $end]);
                if ($selectedBranchId) $q->where('branch_id', $selectedBranchId);
                return (float) $q->sum('total_net');
            });

            $weeklyPurchaseData = collect([2, 1, 0])->map(function ($i) use ($selectedBranchId) {
                $start = Carbon::now()->startOfWeek()->subWeeks($i);
                $end = $start->copy()->endOfWeek();
                $q = DB::table('purchases')->whereBetween('created_at', [$start, $end]);
                if ($selectedBranchId) $q->where('branch_id', $selectedBranchId);
                return (float) $q->sum('net_amount');
            });

            $months = range(1, Carbon::now()->month);
            $monthLabels = collect($months)->map(fn($m) => Carbon::create()->month($m)->format('M'));
            $monthlySalesData = collect($months)->map(function ($m) use ($selectedBranchId) {
                $q = DB::table('sales')->whereMonth('created_at', $m)->whereYear('created_at', Carbon::now()->year);
                if ($selectedBranchId) $q->where('branch_id', $selectedBranchId);
                return (float) $q->sum('total_net');
            });

            $monthlyPurchaseData = collect($months)->map(function ($m) use ($selectedBranchId) {
                $q = DB::table('purchases')->whereMonth('created_at', $m)->whereYear('created_at', Carbon::now()->year);
                if ($selectedBranchId) $q->where('branch_id', $selectedBranchId);
                return (float) $q->sum('net_amount');
            });

            $salesChartStats = [
                'daily' => ['categories' => $dailyFormattedLabels, 'series' => [['name' => 'Sales', 'data' => $dailySalesData]]],
                'weekly' => ['categories' => $weeklyLabels, 'series' => [['name' => 'Sales', 'data' => $weeklySalesData]]],
                'monthly' => ['categories' => $monthLabels, 'series' => [['name' => 'Sales', 'data' => $monthlySalesData]]]
            ];

            $purchaseChartStats = [
                'daily' => ['categories' => $dailyFormattedLabels, 'series' => [['name' => 'Purchases', 'data' => $dailyPurchaseData]]],
                'weekly' => ['categories' => $weeklyLabels, 'series' => [['name' => 'Purchases', 'data' => $weeklyPurchaseData]]],
                'monthly' => ['categories' => $monthLabels, 'series' => [['name' => 'Purchases', 'data' => $monthlyPurchaseData]]]
            ];

            // Party Type Distribution (Cash vs Credit)
            $cashSalesQuery = DB::table('sales')->where('party_type', 'walking');
            $creditSalesQuery = DB::table('sales')->where('party_type', 'credit');
            if ($selectedBranchId) {
                $cashSalesQuery->where('branch_id', $selectedBranchId);
                $creditSalesQuery->where('branch_id', $selectedBranchId);
            }
            $cashSalesTotal = (float) $cashSalesQuery->sum('total_net');
            $creditSalesTotal = (float) $creditSalesQuery->sum('total_net');

            // Low Stock Products
            $lowStockProducts = Product::with(['category_relation'])
                ->select('id', 'item_code', 'item_name', 'item_name_urdu', 'alert_quantity', 'category_id')
                ->latest()
                ->take(6)
                ->get();

            // Recent Transactions / Invoices (Last 6)
            $recentSales = Sale::with('customer')
                ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
                ->latest()
                ->take(6)
                ->get();

            // Recent Procurement / Purchases (Last 5)
            $recentPurchases = Purchase::when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
                ->latest()
                ->take(5)
                ->get();

            return view('admin_panel.dashboard', compact(
                'isSuperAdmin', 'branches', 'selectedBranchId', 'currentBranchName',
                'categoryCount', 'subcategoryCount', 'productCount', 'customerscount',
                'todaySales', 'todayPurchases', 'todayExpenses',
                'monthSales', 'monthPurchases', 'monthExpenses',
                'lastMonthSales', 'lastMonthPurchases', 'lastMonthExpenses',
                'salesGrowth', 'purchasesGrowth',
                'totalPurchases', 'totalPurchaseReturns', 'totalSales', 'totalSalesReturns', 'totalExpenses',
                'salesChartStats', 'purchaseChartStats', 'dailyExpenseData',
                'cashSalesTotal', 'creditSalesTotal', 'lowStockProducts', 'recentSales', 'recentPurchases'
            ));
        } else {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
    }
}


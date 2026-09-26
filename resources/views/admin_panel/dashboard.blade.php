@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Dashboard Custom Modern Design System */
    .dashboard-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #1e3a5f 100%);
        border-radius: 16px;
        padding: 24px 28px;
        color: #fff;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
        position: relative;
        overflow: hidden;
    }
    .dashboard-hero::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, rgba(255,255,255,0) 70%);
        pointer-events: none;
    }
    
    /* Modern KPI Cards */
    .kpi-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px 22px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
        border-color: #cbd5e1;
    }
    
    .kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .kpi-label {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #fff;
        flex-shrink: 0;
    }

    /* KPI Icon Themes */
    .icon-emerald { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
    .icon-blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
    .icon-amber { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3); }
    .icon-indigo { background: linear-gradient(135deg, #6366f1, #4338ca); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
    .icon-purple { background: linear-gradient(135deg, #a855f7, #7e22ce); box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3); }
    .icon-teal { background: linear-gradient(135deg, #14b8a6, #0f766e); box-shadow: 0 4px 12px rgba(20, 184, 166, 0.3); }
    .icon-rose { background: linear-gradient(135deg, #f43f5e, #be123c); box-shadow: 0 4px 12px rgba(244, 63, 94, 0.3); }
    .icon-gold { background: linear-gradient(135deg, #d97706, #b45309); box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3); }

    .kpi-value {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        margin-bottom: 6px;
    }
    .kpi-subtext {
        font-size: 11.5px;
        color: #94a3b8;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    /* Card Container Panels */
    .dashboard-panel {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .panel-header {
        padding: 18px 22px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
    }
    .panel-title {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .panel-body {
        padding: 20px 22px;
    }
    
    /* Branch Filter Dropdown */
    .branch-select-box {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #fff;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 13px;
        font-weight: 600;
        outline: none;
        cursor: pointer;
        backdrop-filter: blur(4px);
    }
    .branch-select-box option {
        background: #1e293b;
        color: #fff;
    }
    
    /* Quick Action Buttons */
    .btn-quick-act {
        background: #2563eb;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: 0.2s;
        text-decoration: none;
    }
    .btn-quick-act:hover {
        background: #1d4ed8;
        color: #fff;
        transform: translateY(-1px);
    }
    .btn-quick-act-light {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .btn-quick-act-light:hover {
        background: rgba(255, 255, 255, 0.3);
        color: #fff;
    }

    /* Table Custom Styling */
    .table-dash {
        width: 100%;
        margin: 0;
    }
    .table-dash th {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 14px;
    }
    .table-dash td {
        font-size: 13px;
        color: #334155;
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .table-dash tr:last-child td {
        border-bottom: none;
    }
    
    /* Mobile Responsiveness & Margin Fixes */
    @media (max-width: 991px) {
        .dashboard-hero { padding: 20px; }
        .kpi-value { font-size: 19px; }
    }
    @media (max-width: 768px) {
        .dashboard-hero { padding: 16px; border-radius: 12px; margin-bottom: 16px; }
        .dashboard-hero-content { flex-direction: column; align-items: stretch !important; gap: 12px; }
        .branch-select-box { width: 100%; }
        .btn-quick-act { width: 100%; justify-content: center; }
        .kpi-card { padding: 14px 16px; border-radius: 12px; margin-bottom: 10px; }
        .panel-header { padding: 12px 16px; flex-direction: column; align-items: flex-start; gap: 8px; }
        .panel-body { padding: 14px 12px; }
    }
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid px-0">

            {{-- ══════════════════════════════════════════════════
                 HERO HEADER BANNER WITH SINGLE BRANCH FOCUS
            ══════════════════════════════════════════════════ --}}
            <div class="dashboard-hero">
                <div class="d-flex align-items-center justify-content-between dashboard-hero-content">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h3 class="m-0 font-weight-bold text-white" style="font-size: 22px; letter-spacing: -0.3px;">
                                Dashboard Overview
                            </h3>
                            <span class="badge bg-primary px-2 py-1 text-white" style="font-size: 11px; border-radius: 6px; background: rgba(37, 99, 235, 0.5) !important;">
                                {{ $currentBranchName }}
                            </span>
                        </div>
                        <p class="m-0 text-white-50 small">
                            <i class="far fa-calendar-alt text-warning me-1"></i> {{ \Carbon\Carbon::now()->format('l, d F Y') }}
                            &nbsp;&bull;&nbsp; Welcome back, <strong>{{ Auth::user()->name }}</strong>
                        </p>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if($isSuperAdmin)
                            <form method="GET" action="{{ route('home') }}" class="m-0 d-flex align-items-center gap-2">
                                <i class="fas fa-filter text-white-50 small"></i>
                                <select name="branch_id" class="branch-select-box" onchange="this.form.submit()">
                                    <option value="">🏢 All Branches</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>
                                            🏢 {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @endif

                        <a href="{{ route('sale.add') }}" class="btn-quick-act btn-quick-act-light">
                            <i class="fas fa-plus"></i> New Sale / POS
                        </a>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════
                 ROW 1: CURRENT & LAST MONTH REVENUE METRICS
            ══════════════════════════════════════════════════ --}}
            <div class="row g-3 mb-3">
                {{-- Today's Sales --}}
                <div class="col-xl-3 col-md-6">
                    <div class="kpi-card">
                        <div>
                            <div class="kpi-header">
                                <span class="kpi-label">Today's Sales</span>
                                <div class="kpi-icon icon-emerald"><i class="fas fa-coins"></i></div>
                            </div>
                            <div class="kpi-value">Rs {{ number_format($todaySales, 2) }}</div>
                        </div>
                        <div class="kpi-subtext mt-2">
                            <i class="fas fa-arrow-up text-success"></i> Sales generated today
                        </div>
                    </div>
                </div>

                {{-- This Month's Sales --}}
                <div class="col-xl-3 col-md-6">
                    <div class="kpi-card">
                        <div>
                            <div class="kpi-header">
                                <span class="kpi-label">This Month Sales</span>
                                <div class="kpi-icon icon-blue"><i class="fas fa-chart-line"></i></div>
                            </div>
                            <div class="kpi-value">Rs {{ number_format($monthSales, 2) }}</div>
                        </div>
                        <div class="kpi-subtext mt-2">
                            @if($salesGrowth >= 0)
                                <i class="fas fa-arrow-up text-success"></i> <span class="text-success fw-bold">+{{ number_format($salesGrowth, 1) }}%</span> vs Last Month
                            @else
                                <i class="fas fa-arrow-down text-danger"></i> <span class="text-danger fw-bold">{{ number_format($salesGrowth, 1) }}%</span> vs Last Month
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Last Month's Sales --}}
                <div class="col-xl-3 col-md-6">
                    <div class="kpi-card">
                        <div>
                            <div class="kpi-header">
                                <span class="kpi-label">Last Month Sales</span>
                                <div class="kpi-icon icon-indigo"><i class="fas fa-history"></i></div>
                            </div>
                            <div class="kpi-value">Rs {{ number_format($lastMonthSales, 2) }}</div>
                        </div>
                        <div class="kpi-subtext mt-2">
                            <i class="fas fa-calendar-alt text-muted"></i> {{ \Carbon\Carbon::now()->subMonth()->format('F Y') }} total
                        </div>
                    </div>
                </div>

                {{-- This Month Expenses --}}
                <div class="col-xl-3 col-md-6">
                    <div class="kpi-card">
                        <div>
                            <div class="kpi-header">
                                <span class="kpi-label">This Month Expenses</span>
                                <div class="kpi-icon icon-rose"><i class="fas fa-receipt"></i></div>
                            </div>
                            <div class="kpi-value">Rs {{ number_format($monthExpenses, 2) }}</div>
                        </div>
                        <div class="kpi-subtext mt-2">
                            <i class="fas fa-credit-card text-danger"></i> Today: Rs {{ number_format($todayExpenses, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════
                 ROW 2: OPERATIONAL & INVENTORY KPIs
            ══════════════════════════════════════════════════ --}}
            <div class="row g-3 mb-4">
                {{-- Active Products --}}
                <div class="col-xl-3 col-md-6">
                    <div class="kpi-card">
                        <div>
                            <div class="kpi-header">
                                <span class="kpi-label">Active Products</span>
                                <div class="kpi-icon icon-purple"><i class="fas fa-boxes"></i></div>
                            </div>
                            <div class="kpi-value">{{ number_format($productCount) }}</div>
                        </div>
                        <div class="kpi-subtext mt-2">
                            <i class="fas fa-tags text-purple"></i> Cataloged items
                        </div>
                    </div>
                </div>

                {{-- Customers --}}
                <div class="col-xl-3 col-md-6">
                    <div class="kpi-card">
                        <div>
                            <div class="kpi-header">
                                <span class="kpi-label">Customers</span>
                                <div class="kpi-icon icon-teal"><i class="fas fa-users"></i></div>
                            </div>
                            <div class="kpi-value">{{ number_format($customerscount) }}</div>
                        </div>
                        <div class="kpi-subtext mt-2">
                            <i class="fas fa-user-check text-teal"></i> Registered parties
                        </div>
                    </div>
                </div>

                {{-- Total Procurement (Purchases) --}}
                <div class="col-xl-3 col-md-6">
                    <div class="kpi-card">
                        <div>
                            <div class="kpi-header">
                                <span class="kpi-label">Total Purchases</span>
                                <div class="kpi-icon icon-amber"><i class="fas fa-truck-loading"></i></div>
                            </div>
                            <div class="kpi-value">Rs {{ number_format($totalPurchases, 2) }}</div>
                        </div>
                        <div class="kpi-subtext mt-2">
                            <i class="fas fa-box text-warning"></i> All-time procurement cost
                        </div>
                    </div>
                </div>

                {{-- Categories & Subcategories --}}
                <div class="col-xl-3 col-md-6">
                    <div class="kpi-card">
                        <div>
                            <div class="kpi-header">
                                <span class="kpi-label">Categories</span>
                                <div class="kpi-icon icon-gold"><i class="fas fa-layer-group"></i></div>
                            </div>
                            <div class="kpi-value">{{ $categoryCount }} <span style="font-size:14px; font-weight:600; color:#64748b;">/ {{ $subcategoryCount }} Sub</span></div>
                        </div>
                        <div class="kpi-subtext mt-2">
                            <i class="fas fa-sitemap text-warning"></i> Inventory categories
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════
                 ANALYTICS SECTION: CHARTS
            ══════════════════════════════════════════════════ --}}
            <div class="row g-3 mb-4">
                {{-- Sales vs Purchases Comparison Area Chart (8 Columns) --}}
                <div class="col-lg-8">
                    <div class="dashboard-panel h-100">
                        <div class="panel-header">
                            <div class="panel-title">
                                <i class="fas fa-chart-area text-primary"></i> Financial Performance (Sales vs Purchases)
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label for="salesFilter" class="small text-muted m-0">View:</label>
                                <select id="salesFilter" class="form-select form-select-sm" style="width: 120px; border-radius: 8px; font-weight:600;">
                                    <option value="daily" selected>Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div id="financialComparisonChart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>

                {{-- Financial Distribution Donut Chart & Quick Actions (4 Columns) --}}
                <div class="col-lg-4">
                    <div class="dashboard-panel h-100 d-flex flex-column justify-content-between">
                        <div class="panel-header">
                            <div class="panel-title">
                                <i class="fas fa-pie-chart text-success"></i> Sales Party Split
                            </div>
                        </div>
                        <div class="panel-body flex-grow-1 d-flex flex-column justify-content-center">
                            <div id="partyTypeDonutChart" style="height: 240px;"></div>
                            
                            {{-- Quick Shortcuts --}}
                            <div class="mt-3 pt-3 border-top">
                                <div class="small font-weight-bold text-muted mb-2 text-uppercase" style="letter-spacing: 0.5px;">Quick Shortcuts</div>
                                <div class="d-grid gap-2" style="grid-template-columns: repeat(2, 1fr);">
                                    <a href="{{ route('store') }}" class="btn btn-sm btn-outline-primary fw-bold text-start">
                                        <i class="fas fa-box-open me-1"></i> Add Product
                                    </a>
                                    <a href="{{ route('add_purchase') }}" class="btn btn-sm btn-outline-success fw-bold text-start">
                                        <i class="fas fa-cart-plus me-1"></i> New Purchase
                                    </a>
                                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-info fw-bold text-start">
                                        <i class="fas fa-user-friends me-1"></i> Customers
                                    </a>
                                    <a href="{{ route('product') }}" class="btn btn-sm btn-outline-secondary fw-bold text-start">
                                        <i class="fas fa-warehouse me-1"></i> Stock List
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════
                 RECENT TRANSACTIONS & LOW STOCK PANELS
            ══════════════════════════════════════════════════ --}}
            <div class="row g-3 mb-4">
                {{-- Recent Sales Invoices (7 Columns) --}}
                <div class="col-lg-7">
                    <div class="dashboard-panel mb-0">
                        <div class="panel-header">
                            <div class="panel-title">
                                <i class="fas fa-file-invoice-dollar text-primary"></i> Recent Sales Invoices
                            </div>
                            <a href="{{ route('sale.index') }}" class="btn btn-sm btn-link text-decoration-none fw-bold p-0">View All &rarr;</a>
                        </div>
                        <div class="panel-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dash align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Customer</th>
                                            <th>Party Type</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentSales as $sale)
                                            <tr>
                                                <td class="font-weight-bold text-dark">
                                                    <i class="fas fa-receipt text-muted me-1"></i>{{ $sale->invoice_no }}
                                                </td>
                                                <td>
                                                    {{ $sale->customer->customer_name ?? ($sale->sub_customer ?? 'Walking Customer') }}
                                                </td>
                                                <td>
                                                    @if(($sale->party_type ?? '') === 'credit')
                                                        <span class="badge bg-warning text-dark px-2 py-1">Credit</span>
                                                    @else
                                                        <span class="badge bg-success text-white px-2 py-1">Cash</span>
                                                    @endif
                                                </td>
                                                <td class="text-end font-weight-bold text-primary">
                                                    Rs {{ number_format($sale->total_net ?? 0, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('sale.invoice', $sale->id) }}" class="btn btn-xs btn-outline-primary px-2 py-1" style="font-size: 11px;">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No recent sales records found for this branch.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Low Stock Items Warning Panel (5 Columns) --}}
                <div class="col-lg-5">
                    <div class="dashboard-panel mb-0">
                        <div class="panel-header">
                            <div class="panel-title">
                                <i class="fas fa-exclamation-triangle text-danger"></i> Products Catalog & Alerts
                            </div>
                            <a href="{{ route('product') }}" class="btn btn-sm btn-link text-decoration-none fw-bold p-0">All Products &rarr;</a>
                        </div>
                        <div class="panel-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dash align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Product Name</th>
                                            <th class="text-center">Alert Limit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($lowStockProducts as $prod)
                                            <tr>
                                                <td class="font-weight-bold text-muted small">
                                                    {{ $prod->item_code }}
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $prod->item_name }}</div>
                                                    @if($prod->item_name_urdu)
                                                        <small class="text-muted" style="direction:rtl; font-family:'Noto Nastaliq Urdu', sans-serif;">{{ $prod->item_name_urdu }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger text-white px-2 py-1" style="font-weight:600;">
                                                        Limit: {{ $prod->alert_quantity ?? 0 }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No low stock items detected.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════
                 RECENT PROCUREMENT PANEL
            ══════════════════════════════════════════════════ --}}
            <div class="row g-3 mt-1">
                <div class="col-12">
                    <div class="dashboard-panel mb-0">
                        <div class="panel-header">
                            <div class="panel-title">
                                <i class="fas fa-truck text-warning"></i> Recent Procurement & Purchases
                            </div>
                            <a href="{{ route('Purchase.home') }}" class="btn btn-sm btn-link text-decoration-none fw-bold p-0">View All Procurement &rarr;</a>
                        </div>
                        <div class="panel-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dash align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Ref / Invoice #</th>
                                            <th>Date</th>
                                            <th class="text-end">Net Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentPurchases as $pur)
                                            <tr>
                                                <td class="font-weight-bold text-dark">
                                                    <i class="fas fa-box text-muted me-1"></i>{{ $pur->invoice_no ?? $pur->reference_no ?? ('PUR-'.$pur->id) }}
                                                </td>
                                                <td class="small text-muted">
                                                    {{ $pur->created_at ? $pur->created_at->format('d M Y') : '-' }}
                                                </td>
                                                <td class="text-end font-weight-bold text-dark">
                                                    Rs {{ number_format($pur->net_amount ?? 0, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No recent procurement records.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const salesStats = @json($salesChartStats);
        const purchaseStats = @json($purchaseChartStats);

        // 1. Dual Comparison Area Chart
        const comparisonOptions = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                fontFamily: 'Inter, Segoe UI, sans-serif'
            },
            stroke: { curve: 'smooth', width: 2.5 },
            colors: ['#10b981', '#3b82f6'],
            series: [
                { name: 'Sales Revenue', data: salesStats.daily.series[0].data },
                { name: 'Purchases Cost', data: purchaseStats.daily.series[0].data }
            ],
            xaxis: {
                categories: salesStats.daily.categories,
                labels: { style: { colors: '#64748b', fontSize: '11px', fontFamily: 'Inter, sans-serif' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: '#64748b', fontSize: '11px', fontFamily: 'Inter, sans-serif' }, formatter: val => "Rs " + val.toLocaleString() }
            },
            dataLabels: { enabled: false },
            markers: { size: 4, colors: ['#fff'], strokeWidth: 2 },
            fill: {
                type: "gradient",
                gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.02, stops: [0, 95, 100] }
            },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: {
                theme: "light",
                y: { formatter: val => "Rs " + val.toLocaleString() }
            },
            legend: { position: 'top', horizontalAlign: 'right', labels: { colors: '#334155', fontWeight: 600 } }
        };

        const comparisonChart = new ApexCharts(document.querySelector("#financialComparisonChart"), comparisonOptions);
        comparisonChart.render();

        // Switcher logic
        document.getElementById('salesFilter').addEventListener('change', function() {
            const selected = this.value;
            comparisonChart.updateOptions({
                series: [
                    { name: 'Sales Revenue', data: salesStats[selected].series[0].data },
                    { name: 'Purchases Cost', data: purchaseStats[selected].series[0].data }
                ],
                xaxis: { categories: salesStats[selected].categories }
            });
        });

        // 2. Party Type Donut Chart
        const cashTotal = {{ $cashSalesTotal ?? 0 }};
        const creditTotal = {{ $creditSalesTotal ?? 0 }};
        
        const donutOptions = {
            chart: {
                type: 'donut',
                height: 240,
                fontFamily: 'Inter, Segoe UI, sans-serif'
            },
            series: [cashTotal > 0 || creditTotal > 0 ? cashTotal : 1, creditTotal],
            labels: ['Cash / Walking Sales', 'Credit Sales'],
            colors: ['#10b981', '#f59e0b'],
            legend: { position: 'bottom', fontSize: '12px', fontWeight: 600 },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Sales',
                                fontSize: '12px',
                                fontWeight: 600,
                                color: '#64748b',
                                formatter: () => "Rs " + (cashTotal + creditTotal).toLocaleString()
                            }
                        }
                    }
                }
            },
            tooltip: {
                y: { formatter: val => "Rs " + val.toLocaleString() }
            }
        };

        const donutChart = new ApexCharts(document.querySelector("#partyTypeDonutChart"), donutOptions);
        donutChart.render();
    });
</script>
@endsection
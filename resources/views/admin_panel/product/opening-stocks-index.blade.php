@extends('admin_panel.layout.app')

@section('content')
@can('product.view')

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
    /* ─── Global Font & Wrap ─── */
    .osi-wrap {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #f8fafc;
        min-height: 100vh;
        padding: 1.5rem;
    }

    /* ─── Hero Header (Ameen & Sons Navy/Gold Theme) ─── */
    .osi-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        border-radius: 16px;
        padding: 1.5rem 2rem;
        color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(30, 58, 95, 0.25);
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.25rem;
    }
    .osi-hero-content {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }
    .osi-hero-icon {
        width: 52px;
        height: 52px;
        background: rgba(200, 151, 58, 0.2);
        border: 1.5px solid rgba(200, 151, 58, 0.45);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: #fef08a;
        flex-shrink: 0;
    }
    .osi-title {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin: 0;
        color: #ffffff !important;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .osi-badge-erp {
        background: rgba(200, 151, 58, 0.25);
        border: 1px solid rgba(200, 151, 58, 0.5);
        color: #fef08a !important;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .osi-subtitle {
        font-size: 13.5px;
        color: #cbd5e1;
        margin-top: 4px;
        font-weight: 500;
    }
    .osi-hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-hero-add {
        background: linear-gradient(135deg, #c8973a 0%, #b3832d 100%);
        color: #ffffff !important;
        font-size: 13.5px;
        font-weight: 700;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(200, 151, 58, 0.35);
        transition: all 0.2s ease;
        border: none;
    }
    .btn-hero-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(200, 151, 58, 0.5);
        color: #ffffff;
        text-decoration: none;
    }
    .btn-hero-outline {
        background: rgba(255, 255, 255, 0.12);
        border: 1.5px solid rgba(255, 255, 255, 0.25);
        color: #ffffff !important;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 16px;
        border-radius: 10px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }
    .btn-hero-outline:hover {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
        text-decoration: none;
        transform: translateY(-1px);
    }

    /* ─── KPI Cards ─── */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .kpi-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    }
    .kpi-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .kpi-icon-blue { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    .kpi-icon-green { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .kpi-icon-gold { background: #fefce8; color: #ca8a04; border: 1px solid #fef08a; }
    .kpi-icon-purple { background: #faf5ff; color: #9333ea; border: 1px solid #e9d5ff; }
    .kpi-label {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 4px;
    }
    .kpi-val {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }
    .kpi-sub {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 3px;
        font-weight: 500;
    }

    /* ─── Filter Bar ─── */
    .filter-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        padding: 1.1rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
    }
    .filter-form {
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 180px;
        flex: 1;
    }
    .filter-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #475569;
        margin: 0;
    }
    .filter-select {
        border: 1.5px solid #cbd5e1;
        border-radius: 9px;
        padding: 8px 12px;
        font-size: 13.5px;
        font-weight: 600;
        color: #0f172a;
        background: #f8fafc;
        outline: none;
        transition: all 0.2s;
        width: 100%;
        height: 40px;
    }
    .filter-select:focus {
        border-color: #1e3a5f;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.12);
    }
    .filter-btn-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-filter-apply {
        background: #1e3a5f;
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        padding: 9px 18px;
        border-radius: 9px;
        border: none;
        height: 40px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-filter-apply:hover {
        background: #2c5282;
        color: #ffffff;
    }
    .btn-filter-reset {
        background: #f1f5f9;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 14px;
        border-radius: 9px;
        border: 1px solid #cbd5e1;
        height: 40px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-filter-reset:hover {
        background: #e2e8f0;
        color: #0f172a;
        text-decoration: none;
    }

    /* ─── Table Card & DataTables ─── */
    .table-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1.5px solid #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        padding: 1.25rem;
    }
    .table-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1.5px solid #f1f5f9;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .table-card-title {
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .table-count-badge {
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        border: 1px solid #bfdbfe;
    }

    /* ─── Horizontal Scroll Wrapper & Scrollbar ─── */
    .osi-table-responsive {
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        margin-top: 10px;
        padding-bottom: 8px;
    }
    .osi-table-responsive::-webkit-scrollbar,
    .dataTables_scrollBody::-webkit-scrollbar {
        height: 10px !important;
    }
    .osi-table-responsive::-webkit-scrollbar-track,
    .dataTables_scrollBody::-webkit-scrollbar-track {
        background: #f1f5f9 !important;
        border-radius: 6px;
    }
    .osi-table-responsive::-webkit-scrollbar-thumb,
    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #94a3b8 !important;
        border-radius: 6px;
        border: 2px solid #f1f5f9;
    }
    .osi-table-responsive::-webkit-scrollbar-thumb:hover,
    .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
        background: #64748b !important;
    }

    /* ─── Table Styling ─── */
    #openingStocksTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
        min-width: 1350px !important; /* Guarantees all 12 columns fit comfortably with horizontal scroll */
        margin-top: 0 !important;
        margin-bottom: 15px !important;
    }
    #openingStocksTable thead th {
        background: #f8fafc !important;
        color: #475569 !important;
        font-size: 11.5px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.6px !important;
        padding: 12px 14px !important;
        border-top: 1px solid #e2e8f0 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        white-space: nowrap !important;
    }
    #openingStocksTable tbody td {
        padding: 12px 14px !important;
        font-size: 13px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #1e293b;
        white-space: nowrap !important;
    }
    #openingStocksTable tbody tr:hover td {
        background-color: #f8faff !important;
    }

    /* Badges & Elements */
    .sku-badge {
        background: #f1f5f9;
        color: #0f172a;
        font-weight: 800;
        font-size: 11.5px;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        font-family: monospace;
        letter-spacing: 0.3px;
        display: inline-block;
    }
    .prod-title {
        font-weight: 700;
        color: #0f172a;
        font-size: 13.5px;
        line-height: 1.3;
    }
    .prod-sub {
        font-size: 11.5px;
        color: #64748b;
        margin-top: 2px;
    }
    .urdu-title {
        font-family: 'Noto Nastaliq Urdu', serif;
        font-size: 11px;
        color: #2563eb;
        display: block;
        direction: rtl;
        text-align: left;
    }
    .branch-badge {
        background: #f0fdf4;
        color: #166534;
        font-weight: 700;
        font-size: 11.5px;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #bbf7d0;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }
    .qty-opening-badge {
        background: #e0f2fe;
        color: #0369a1;
        font-size: 13.5px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid #bae6fd;
        display: inline-block;
        min-width: 60px;
        text-align: right;
    }
    .qty-current-badge {
        font-size: 13.5px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 8px;
        display: inline-block;
        min-width: 60px;
        text-align: right;
    }
    .qty-positive {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .qty-negative {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    /* ─── Compact Location View Button on Front Table ─── */
    .btn-location-view {
        background: #f0f9ff;
        border: 1.5px solid #bae6fd;
        color: #0369a1;
        font-size: 12px;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        cursor: pointer;
        outline: none;
        white-space: nowrap;
        box-shadow: 0 1px 3px rgba(3, 105, 161, 0.08);
    }
    .btn-location-view:hover {
        background: #0284c7;
        color: #ffffff !important;
        border-color: #0284c7;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);
    }
    .btn-location-view:hover i {
        color: #ffffff !important;
    }

    .val-cell {
        font-weight: 800;
        color: #0f172a;
        text-align: right;
    }

    /* Action Buttons */
    .btn-action-group {
        display: flex;
        align-items: center;
        gap: 6px;
        justify-content: flex-end;
    }
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        transition: all 0.15s ease;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .btn-action-view {
        background: #eff6ff;
        color: #0284c7;
        border-color: #bae6fd;
    }
    .btn-action-view:hover {
        background: #0284c7;
        color: #ffffff;
    }
    .btn-action-edit {
        background: #f0fdf4;
        color: #16a34a;
        border-color: #bbf7d0;
        text-decoration: none;
    }
    .btn-action-edit:hover {
        background: #16a34a;
        color: #ffffff;
        text-decoration: none;
    }
    .btn-action-delete {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }
    .btn-action-delete:hover {
        background: #dc2626;
        color: #ffffff;
    }

    /* Modal styling */
    .modal-stat-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        text-align: center;
    }
    .modal-stat-label {
        font-size: 11px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .modal-stat-val {
        font-size: 17px;
        font-weight: 800;
        margin-top: 2px;
        color: #0f172a;
    }
</style>
@endsection

<div class="osi-wrap">

    {{-- Hero Header --}}
    <div class="osi-hero">
        <div class="osi-hero-content">
            <div class="osi-hero-icon">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <div>
                <h4 class="osi-title">
                    Opening Stocks Register
                    <span class="osi-badge-erp">ERP Inventory</span>
                </h4>
                <div class="osi-subtitle">
                    Manage and view initial opening stock entries configured across branches &amp; warehouses
                </div>
            </div>
        </div>
        <div class="osi-hero-actions">
            <a href="{{ route('opening.stocks.create') }}" class="btn-hero-add">
                <i class="fas fa-plus-circle"></i> + Add Opening Stock (Bulk Entry)
            </a>
            @if(\App\Models\Product::where('completion_status', 'profile_only')->count() > 0)
            <a href="{{ route('product.incomplete') }}" class="btn-hero-outline" title="Products with incomplete opening stock">
                <i class="fas fa-hourglass-half" style="color: #fbbf24;"></i> Incomplete Products ({{ \App\Models\Product::where('completion_status', 'profile_only')->count() }})
            </a>
            @endif
            <a href="{{ route('product') }}" class="btn-hero-outline">
                <i class="fas fa-arrow-left"></i> Products Catalog
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon-box kpi-icon-blue">
                <i class="fas fa-cubes"></i>
            </div>
            <div>
                <div class="kpi-label">Configured Products</div>
                <div class="kpi-val">{{ number_format($kpi['total_with_stock']) }}</div>
                <div class="kpi-sub">Total products with opening stock</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box kpi-icon-green">
                <i class="fas fa-dolly-flatbed"></i>
            </div>
            <div>
                <div class="kpi-label">Total Opening Units</div>
                <div class="kpi-val">{{ number_format($kpi['total_opening_qty'], 2) }}</div>
                <div class="kpi-sub">Aggregated opening stock volume</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box kpi-icon-gold">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div>
                <div class="kpi-label">Opening Valuation (PKR)</div>
                <div class="kpi-val">₨ {{ number_format($kpi['total_valuation'], 2) }}</div>
                <div class="kpi-sub">Based on wholesale acquisition cost</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box kpi-icon-purple">
                <i class="fas fa-warehouse"></i>
            </div>
            <div>
                <div class="kpi-label">Current Available Units</div>
                <div class="kpi-val">{{ number_format($kpi['total_current_qty'], 2) }}</div>
                <div class="kpi-sub">Live on-hand balance across locations</div>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('opening.stocks.index') }}" class="filter-form">
            @if($isSuperAdmin)
            <div class="filter-group">
                <label class="filter-label"><i class="fas fa-code-branch"></i> Branch Filter</label>
                <select name="branch_id" class="filter-select">
                    <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>🏢 All Branches (Global)</option>
                    @foreach($allBranches as $b)
                        <option value="{{ $b->id }}" {{ (string)$b->id === (string)$branchId ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="filter-group">
                <label class="filter-label"><i class="fas fa-tags"></i> Category</label>
                <select name="category_id" class="filter-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$cat->id === (string)$categoryId ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label"><i class="fas fa-filter"></i> Stock Status</label>
                <select name="status" class="filter-select">
                    <option value="opening_set" {{ $status === 'opening_set' ? 'selected' : '' }}>✅ With Opening Stock Configured</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>⏳ Zero / Pending Opening Stock</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>📋 All Products (Complete + Pending)</option>
                </select>
            </div>

            <div class="filter-btn-group">
                <button type="submit" class="btn-filter-apply">
                    <i class="fas fa-search"></i> Apply Filter
                </button>
                <a href="{{ route('opening.stocks.index') }}" class="btn-filter-reset">
                    <i class="fas fa-sync-alt"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Main DataTable Card --}}
    <div class="table-card">
        <div class="table-card-header">
            <h5 class="table-card-title">
                <i class="fas fa-list-check" style="color:#1e3a5f;"></i>
                Opening Stock Records
                <span class="table-count-badge">{{ $items->count() }} Items</span>
            </h5>
            <div>
                <a href="{{ route('opening.stocks.create') }}" class="btn btn-sm btn-primary" style="background:#1e3a5f;border-color:#1e3a5f;font-weight:700;">
                    <i class="fas fa-plus"></i> New Opening Entry
                </a>
            </div>
        </div>

        <div class="table-responsive osi-table-responsive">
            <table id="openingStocksTable" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">#</th>
                        <th style="width: 110px;">SKU / Code</th>
                        <th style="min-width: 220px;">Product Name</th>
                        <th style="width: 130px;">Branch</th>
                        <th style="width: 140px; text-align: center;">Locations / WH</th>
                        <th style="width: 110px; text-align: right;">Opening Qty</th>
                        <th style="width: 110px; text-align: right;">Live Stock</th>
                        <th style="width: 110px; text-align: right;">Wholesale ₨</th>
                        <th style="width: 110px; text-align: right;">Retail ₨</th>
                        <th style="width: 120px; text-align: right;">Total Value ₨</th>
                        <th style="width: 120px;">Updated</th>
                        <th style="width: 130px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $idx => $item)
                    <tr id="row-{{ $item->id }}-{{ $item->branch_id }}">
                        <td style="text-align: center; font-weight: 700; color: #64748b;">
                            {{ $idx + 1 }}
                        </td>
                        <td>
                            <span class="sku-badge">{{ $item->item_code ?? 'ITEM-' . $item->id }}</span>
                        </td>
                        <td>
                            <div class="prod-title">{{ $item->item_name }}</div>
                            @if(!empty($item->item_name_urdu))
                                <div class="urdu-title">{{ $item->item_name_urdu }}</div>
                            @endif
                            <div class="prod-sub">
                                <span class="badge badge-light" style="border:1px solid #e2e8f0; font-weight:600;">{{ $item->category_name }}</span>
                                @if($item->brand_name !== '-')
                                    <span class="badge badge-light" style="border:1px solid #e2e8f0; font-weight:600;">{{ $item->brand_name }}</span>
                                @endif
                                <span style="font-weight:700; color:#475569;">• {{ $item->unit_name }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="branch-badge">
                                <i class="fas fa-building" style="font-size:10px;"></i> {{ $item->branch_name }}
                            </span>
                        </td>
                        {{-- Clean Locations View Button (No Clutter on Front) --}}
                        <td style="text-align: center;">
                            <button type="button" class="btn-location-view btn-view-allocations"
                                    data-id="{{ $item->id }}"
                                    data-name="{{ $item->item_name }}"
                                    data-code="{{ $item->item_code ?? 'ITEM-' . $item->id }}"
                                    data-unit="{{ $item->unit_name }}"
                                    data-category="{{ $item->category_name }}"
                                    data-brand="{{ $item->brand_name }}"
                                    data-branch="{{ $item->branch_name }}"
                                    data-branch-id="{{ $item->branch_id }}"
                                    data-opening="{{ $item->opening_qty }}"
                                    data-current="{{ $item->current_stock }}"
                                    data-wholesale="{{ $item->wholesale_price }}"
                                    data-retail="{{ $item->retail_price }}"
                                    data-valuation="{{ $item->valuation }}"
                                    data-allocations='@json($item->allocations)'
                                    title="Click to view full warehouse &amp; shop breakdown">
                                <i class="fas fa-eye text-primary"></i>
                                <span>
                                    @if(count($item->allocations) > 0)
                                        {{ count($item->allocations) }} {{ count($item->allocations) == 1 ? 'Location' : 'Locations' }}
                                    @else
                                        Direct Stock
                                    @endif
                                </span>
                            </button>
                        </td>
                        <td style="text-align: right;">
                            <span class="qty-opening-badge">
                                {{ number_format($item->opening_qty, 2) }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <span class="qty-current-badge {{ $item->current_stock > 0 ? 'qty-positive' : ($item->current_stock < 0 ? 'qty-negative' : 'text-muted') }}">
                                {{ number_format($item->current_stock, 2) }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #334155;">
                            ₨ {{ number_format($item->wholesale_price, 2) }}
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #334155;">
                            ₨ {{ number_format($item->retail_price, 2) }}
                        </td>
                        <td class="val-cell">
                            ₨ {{ number_format($item->valuation, 2) }}
                        </td>
                        <td style="font-size: 11.5px; color: #64748b; white-space: nowrap;">
                            {{ $item->updated_at }}
                        </td>
                        <td style="text-align: center; width: 130px; min-width: 130px;">
                            <div class="btn-action-group" style="justify-content: center;">
                                {{-- View Locations Breakdown Modal --}}
                                <button type="button" class="btn-action btn-action-view btn-view-allocations"
                                        data-id="{{ $item->id }}"
                                        data-name="{{ $item->item_name }}"
                                        data-code="{{ $item->item_code ?? 'ITEM-' . $item->id }}"
                                        data-unit="{{ $item->unit_name }}"
                                        data-category="{{ $item->category_name }}"
                                        data-brand="{{ $item->brand_name }}"
                                        data-branch="{{ $item->branch_name }}"
                                        data-branch-id="{{ $item->branch_id }}"
                                        data-opening="{{ $item->opening_qty }}"
                                        data-current="{{ $item->current_stock }}"
                                        data-wholesale="{{ $item->wholesale_price }}"
                                        data-retail="{{ $item->retail_price }}"
                                        data-valuation="{{ $item->valuation }}"
                                        data-allocations='@json($item->allocations)'
                                        title="View Stock Breakdown">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit Opening Stock & Pricing --}}
                                <a href="{{ route('opening.stocks.edit', $item->id) }}?branch_id={{ $item->branch_id }}"
                                   class="btn-action btn-action-edit"
                                   title="Edit Stock &amp; Pricing">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- Delete / Reset Opening Stock --}}
                                <button type="button" class="btn-action btn-action-delete btn-delete-stock"
                                        data-id="{{ $item->id }}"
                                        data-branch-id="{{ $item->branch_id }}"
                                        data-name="{{ $item->item_name }}"
                                        data-qty="{{ $item->opening_qty }}"
                                        data-unit="{{ $item->unit_name }}"
                                        title="Delete / Reset Opening Stock">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-5">
                            <div style="color: #94a3b8; font-size: 40px; margin-bottom: 12px;">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h5 style="color: #334155; font-weight: 700;">No Opening Stock Records Found</h5>
                            <p style="color: #64748b; font-size: 13px; max-width: 400px; margin: 0 auto 16px;">
                                No products match the current filters. Click below to add initial opening stock for your inventory.
                            </p>
                            <a href="{{ route('opening.stocks.create') }}" class="btn btn-primary" style="background:#1e3a5f;border-color:#1e3a5f;font-weight:700;">
                                <i class="fas fa-plus"></i> + Add Opening Stock Now
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Stock Breakdown & Location Modal --}}
<div class="modal fade" id="locationsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.18);">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: #fff; padding: 1.25rem 1.75rem;">
                <div class="d-flex align-items-center">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(200, 151, 58, 0.25); border: 1.5px solid rgba(200, 151, 58, 0.5); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fef08a; margin-right: 14px; flex-shrink: 0;">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="locModalTitle" style="font-weight: 800; font-size: 17px; margin: 0; color: #fff;">
                            Stock Location Breakdown
                        </h5>
                        <div id="locModalSubtitle" style="color: #cbd5e1; font-size: 12.5px; margin-top: 3px;"></div>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.85; text-shadow: none; font-size: 26px; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 1.5rem 1.75rem;">
                {{-- Quick Metrics Grid in Modal --}}
                <div class="row g-2 mb-4">
                    <div class="col-sm-3 col-6 mb-2">
                        <div class="modal-stat-box" style="background: #eff6ff; border-color: #bfdbfe;">
                            <div class="modal-stat-label" style="color: #1e40af;">Total Opening</div>
                            <div id="locModalOpening" class="modal-stat-val" style="color: #1d4ed8;">0.00</div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6 mb-2">
                        <div class="modal-stat-box">
                            <div class="modal-stat-label">Live Stock</div>
                            <div id="locModalCurrent" class="modal-stat-val">0.00</div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6 mb-2">
                        <div class="modal-stat-box">
                            <div class="modal-stat-label">Wholesale Rate</div>
                            <div id="locModalWholesale" class="modal-stat-val">₨ 0.00</div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6 mb-2">
                        <div class="modal-stat-box">
                            <div class="modal-stat-label">Retail Rate</div>
                            <div id="locModalRetail" class="modal-stat-val">₨ 0.00</div>
                        </div>
                    </div>
                </div>

                {{-- Location Breakdown Table --}}
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 style="font-size: 12.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #1e3a5f; margin: 0;">
                        <i class="fas fa-map-marker-alt text-primary mr-1"></i> Allocated Locations &amp; Warehouses
                    </h6>
                    <span id="locModalCountBadge" class="badge badge-primary" style="font-size: 11px; font-weight: 700; border-radius: 12px; padding: 4px 10px;"></span>
                </div>

                <div class="table-responsive" style="border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                    <table class="table table-sm table-striped mb-0" style="font-size: 13px;">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th style="padding: 10px 14px; font-weight: 800; color: #475569; width: 40px;">#</th>
                                <th style="padding: 10px 14px; font-weight: 800; color: #475569;">Location Name</th>
                                <th style="padding: 10px 14px; font-weight: 800; color: #475569; width: 130px;">Type</th>
                                <th style="padding: 10px 14px; font-weight: 800; color: #475569;">Branch</th>
                                <th style="padding: 10px 14px; font-weight: 800; color: #475569; text-align: right; width: 140px;">Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="locModalTableBody">
                            <!-- Dynamic Table Rows -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 1rem 1.75rem; display: flex; justify-content: space-between; align-items: center;">
                <a id="locModalEditBtn" href="#" class="btn btn-sm btn-primary" style="background: #1e3a5f; border-color: #1e3a5f; font-weight: 700; border-radius: 8px; padding: 8px 16px;">
                    <i class="fas fa-edit mr-1"></i> Edit Stock &amp; Pricing
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="font-weight: 600; border-radius: 8px; padding: 8px 16px;">Close</button>
            </div>
        </div>
    </div>
</div>

@endcan
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // 1. Initialize DataTable with Native Horizontal Scroll (No Column Collapsing)
    var table = $('#openingStocksTable').DataTable({
        responsive: false, // Disabled so columns are NEVER collapsed or hidden
        scrollX: true,     // Enable native horizontal scrolling
        autoWidth: false,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[0, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search opening stock...",
            lengthMenu: "Show _MENU_ records",
            info: "Showing _START_ to _END_ of _TOTAL_ opening stocks",
            infoEmpty: "No opening stock records available",
            zeroRecords: "No matching records found"
        },
        columnDefs: [
            { orderable: false, targets: [4, 11] }
        ]
    });

    // 2. View Locations Modal (Clean & Detailed)
    $(document).on('click', '.btn-view-allocations', function() {
        var btn = $(this);
        var id = btn.data('id');
        var name = btn.data('name');
        var code = btn.data('code');
        var unit = btn.data('unit');
        var category = btn.data('category');
        var brand = btn.data('brand');
        var branch = btn.data('branch');
        var branchId = btn.data('branch-id');
        var opening = parseFloat(btn.data('opening') || 0);
        var current = parseFloat(btn.data('current') || 0);
        var wholesale = parseFloat(btn.data('wholesale') || 0);
        var retail = parseFloat(btn.data('retail') || 0);
        var allocations = btn.data('allocations') || [];

        $('#locModalTitle').text(name);
        $('#locModalSubtitle').html(`
            <span class="badge badge-light" style="background:rgba(255,255,255,0.18);color:#fff;font-weight:700;margin-right:6px;">${code}</span>
            <span>${category}</span> • <span>${branch}</span> • <strong>${unit}</strong>
        `);
        $('#locModalOpening').text(opening.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ' + unit);
        
        var currentFormatted = current.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ' + unit;
        var currentClass = current > 0 ? '#15803d' : (current < 0 ? '#b91c1c' : '#64748b');
        $('#locModalCurrent').text(currentFormatted).css('color', currentClass);

        $('#locModalWholesale').text('₨ ' + wholesale.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#locModalRetail').text('₨ ' + retail.toLocaleString('en-US', {minimumFractionDigits: 2}));

        $('#locModalEditBtn').attr('href', '{{ url("opening-stocks") }}/' + id + '/edit?branch_id=' + (branchId || ''));

        var rowsHtml = '';
        if (allocations && allocations.length > 0) {
            $('#locModalCountBadge').text(allocations.length + (allocations.length === 1 ? ' Location' : ' Locations'));
            allocations.forEach(function(item, idx) {
                var icon = item.is_shop ? '🏪' : '📦';
                var typeLabel = item.is_shop 
                    ? '<span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-weight:700;">Shop / Counter</span>' 
                    : '<span class="badge" style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;font-weight:700;">Warehouse</span>';
                
                var qty = parseFloat(item.quantity || 0);
                var qtyBadge = qty >= 0 
                    ? `<span style="font-weight:800;color:#15803d;">${qty.toLocaleString('en-US', {minimumFractionDigits: 2})} <small style="color:#64748b;">${unit}</small></span>`
                    : `<span style="font-weight:800;color:#b91c1c;">${qty.toLocaleString('en-US', {minimumFractionDigits: 2})} <small style="color:#64748b;">${unit}</small></span>`;

                rowsHtml += `
                    <tr>
                        <td style="padding:10px 14px;font-weight:700;color:#64748b;">${idx + 1}</td>
                        <td style="padding:10px 14px;font-weight:700;color:#0f172a;">
                            <span class="mr-1">${icon}</span> ${item.warehouse_name}
                        </td>
                        <td style="padding:10px 14px;">${typeLabel}</td>
                        <td style="padding:10px 14px;color:#475569;font-weight:600;">${item.branch_name || branch}</td>
                        <td style="padding:10px 14px;text-align:right;">${qtyBadge}</td>
                    </tr>
                `;
            });
        } else {
            $('#locModalCountBadge').text('Direct Stock');
            rowsHtml = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted" style="font-size:13px;">
                        <i class="fas fa-store-alt mr-1"></i> No separate warehouse allocations recorded. All stock is managed as direct branch counter stock.
                    </td>
                </tr>
            `;
        }

        $('#locModalTableBody').html(rowsHtml);
        $('#locationsModal').modal('show');
    });

    // 3. Delete / Reset Opening Stock with SweetAlert2 Confirmation
    $(document).on('click', '.btn-delete-stock', function() {
        var btn = $(this);
        var productId = btn.data('id');
        var branchId  = btn.data('branch-id');
        var prodName  = btn.data('name');
        var qty       = btn.data('qty');
        var unit      = btn.data('unit');

        Swal.fire({
            title: 'Delete Opening Stock?',
            html: `
                <div style="font-size:14px; color:#475569; text-align:left; line-height:1.6;">
                    Are you sure you want to delete the opening stock for:<br>
                    <strong style="color:#0f172a; font-size:15px;">${prodName}</strong>?
                    <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:10px; margin-top:12px; color:#b91c1c;">
                        ⚠️ <strong>Inventory Reversal:</strong><br>
                        This will remove <strong>${qty} ${unit}</strong> from warehouse &amp; branch stock, delete the opening movement, and reset initial stock.
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Delete Stock',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Reversing inventory and removing opening stock',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ url("opening-stocks") }}/' + productId,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}',
                        branch_id: branchId
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message || 'Opening stock deleted successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        var errMsg = 'Failed to delete opening stock.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errMsg = xhr.responseJSON.error;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errMsg
                        });
                    }
                });
            }
        });
    });
});
</script>
@endsection

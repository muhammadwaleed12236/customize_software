@extends('admin_panel.layout.app')

@section('content')
@can('product.edit')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endsection

<style>
    /* ── Main Container & Layout (Identical to Opening Stocks Manager) ── */
    .osm-wrap {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #f8fafc;
        min-height: 100vh;
        padding: 1.5rem;
    }

    /* ── Hero Header (Ameen & Sons Navy Theme) ── */
    .osm-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        border-radius: 16px;
        padding: 1.5rem;
        color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(30, 58, 95, 0.25);
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .osm-hero-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .osm-hero-icon {
        width: 48px;
        height: 48px;
        background: rgba(200, 151, 58, 0.2);
        border: 1.5px solid rgba(200, 151, 58, 0.4);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #c8973a;
        flex-shrink: 0;
    }
    .osm-title {
        font-size: 20px;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        color: #ffffff !important;
    }
    .osm-hero-content h4,
    .osm-hero-content h4 * {
        color: #ffffff !important;
    }
    .osm-badge-erp {
        background: rgba(200, 151, 58, 0.25);
        border: 1px solid rgba(200, 151, 58, 0.4);
        color: #fef08a !important;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .osm-subtitle {
        font-size: 13px;
        color: #cbd5e1 !important;
        margin-top: 4px;
    }
    .btn-back-link {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #ffffff;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-back-link:hover {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
        transform: translateY(-1px);
        text-decoration: none;
    }

    /* ── Branch Selector Card (Super Admin) ── */
    .branch-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        flex-wrap: wrap;
    }
    .branch-card label {
        font-weight: 700;
        font-size: 13.5px;
        color: #334155;
        white-space: nowrap;
        margin: 0;
    }
    .branch-card select {
        border: 1.5px solid #1e3a5f;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        background: #f8fafc;
        outline: none;
        min-width: 250px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .branch-card select:focus {
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(30, 58, 95, 0.12);
    }

    /* ── Main Container & Table Card ── */
    .table-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1.5px solid #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    /* Table horizontal scroll wrapper for desktop */
    .osm-table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .osm-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .osm-thead {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        color: #ffffff;
    }
    .osm-thead th {
        padding: 14px 12px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        border: none;
        white-space: nowrap;
    }
    .osm-thead th:first-child { border-top-left-radius: 0px; }
    .osm-thead th:last-child { border-top-right-radius: 0px; }

    .osm-row td {
        vertical-align: top;
        padding: 16px 12px;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
        transition: background 0.15s;
    }
    .osm-row:hover td {
        background: #f8faff;
    }
    .osm-row-num {
        width: 44px;
        text-align: center;
        font-weight: 800;
        color: #64748b;
        font-size: 13px;
        padding-top: 22px !important;
    }

    /* ── Form Inputs ── */
    .fi {
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        padding: 9px 12px;
        font-size: 13px;
        font-weight: 500;
        width: 100%;
        transition: all 0.2s ease;
        background: #f8fafc;
        color: #0f172a;
    }
    .fi:focus {
        border-color: #1e3a5f;
        background: #ffffff;
        outline: none;
        box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.12);
    }
    .fi-num {
        text-align: right;
        font-weight: 700;
    }
    .fi-label {
        font-size: 10px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        display: block;
    }

    /* Input prefix (Currency PKR) */
    .input-with-prefix {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }
    .input-with-prefix .prefix {
        position: absolute;
        left: 10px;
        font-size: 10.5px;
        font-weight: 800;
        color: #64748b;
        pointer-events: none;
    }
    .input-with-prefix input {
        padding-left: 36px !important;
    }

    /* ── Product Card Display inside Table Cell ── */
    .product-info-box {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 12px;
        min-width: 250px;
    }
    .prod-code-tags {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 5px;
    }
    .badge-sku {
        background: #1e3a5f;
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 6px;
        letter-spacing: 0.4px;
    }
    .badge-cat {
        background: #e0e7ff;
        color: #3730a3;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 6px;
    }
    .badge-unit {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 6px;
    }
    .prod-name-main {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.35;
        margin-bottom: 2px;
    }
    .prod-name-urdu {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        direction: rtl;
        text-align: left;
    }

    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #dcfce7;
        color: #166534;
        font-size: 10.5px;
        font-weight: 800;
        padding: 3px 10px;
        border-radius: 20px;
        margin-top: 6px;
    }
    .stock-badge.zero {
        background: #fee2e2;
        color: #991b1b;
    }

    /* ── Location Allocation Sub-rows ── */
    .alloc-container {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .alloc-inline-row {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        padding: 5px 8px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .alloc-inline-row:focus-within {
        border-color: #1e3a5f;
        box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.12);
    }
    .alloc-inline-row select {
        flex: 1.4;
        font-size: 12.5px;
        font-weight: 600;
        border: none;
        background: transparent;
        color: #0f172a;
        outline: none;
        cursor: pointer;
        min-width: 0;
        padding: 4px;
    }
    .alloc-inline-row input {
        flex: 1;
        font-size: 13px;
        font-weight: 700;
        border: none;
        background: transparent;
        color: #1e3a5f;
        text-align: right;
        outline: none;
        min-width: 0;
        padding: 4px;
    }
    .btn-del-alloc {
        background: none;
        color: #94a3b8;
        border: none;
        font-size: 14px;
        cursor: pointer;
        padding: 3px 6px;
        flex-shrink: 0;
        line-height: 1;
        border-radius: 6px;
        transition: all 0.15s;
    }
    .btn-del-alloc:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-add-alloc {
        background: #f1f5f9;
        color: #1e3a5f;
        border: 1.5px dashed #94a3b8;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 6px;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .btn-add-alloc:hover {
        background: #1e3a5f;
        color: #ffffff;
        border-style: solid;
        border-color: #1e3a5f;
    }

    /* Delta info pill */
    .delta-info {
        font-size: 10.5px;
        font-weight: 700;
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        margin-top: 4px;
    }
    .delta-pos { color: #166534; background: #dcfce7; }
    .delta-neg { color: #991b1b; background: #fee2e2; }
    .delta-zero { color: #92400e; background: #fef3c7; }

    /* ── Action Footer ── */
    .osm-footer {
        background: #f8fafc;
        border-top: 1.5px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 0 0 16px 16px;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .btn-save-all {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 11px 32px;
        font-size: 14.5px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(30, 58, 95, 0.35);
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-save-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(30, 58, 95, 0.45);
    }

    .flash-success {
        background: #dcfce7;
        border: 1.5px solid #86efac;
        color: #166534;
        padding: 12px 18px;
        border-radius: 12px;
        margin-bottom: 1.25rem;
        font-weight: 700;
        font-size: 13.5px;
    }
    .flash-error {
        background: #fee2e2;
        border: 1.5px solid #fca5a5;
        color: #991b1b;
        padding: 12px 18px;
        border-radius: 12px;
        margin-bottom: 1.25rem;
        font-weight: 700;
        font-size: 13.5px;
    }

    /* ── Mobile Card Responsiveness (< 768px) ── */
    @media (max-width: 767.98px) {
        .osm-wrap {
            padding: 0.75rem;
            padding-bottom: 6rem;
        }
        .osm-hero {
            padding: 1.25rem;
            border-radius: 14px;
        }
        .osm-hero-icon {
            width: 42px;
            height: 42px;
            font-size: 20px;
        }
        .osm-title {
            font-size: 17px;
        }
        .osm-table thead {
            display: none;
        }
        .osm-table, .osm-table tbody, .osm-row, .osm-row td {
            display: block;
            width: 100%;
        }
        .osm-row {
            background: #ffffff;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            margin-bottom: 1.25rem;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        }
        .osm-row td {
            padding: 0.5rem 0 !important;
            border: none;
            background: transparent !important;
        }
        .osm-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1.5px solid #cbd5e1;
            padding: 0.75rem 1rem;
            z-index: 1000;
            box-shadow: 0 -4px 20px rgba(15, 23, 42, 0.12);
            display: flex;
            gap: 0.5rem;
            justify-content: space-between;
            border-radius: 0;
        }
        .btn-save-all {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="osm-wrap">
    @if(session('success'))
        <div class="flash-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash-error">❌ {{ session('error') }}</div>
    @endif

    {{-- Hero Header (Ameen & Sons Navy/Gold) --}}
    <div class="osm-hero">
        <div class="osm-hero-content">
            <div class="osm-hero-icon">
                <i class="las la-edit"></i>
            </div>
            <div>
                <h4 class="osm-title">
                    Edit Opening Stock
                    <span class="osm-badge-erp">ERP Standard</span>
                </h4>
                <div class="osm-subtitle">
                    Editing stock &amp; allocations for: <strong>{{ $product->item_name }}</strong> (SKU: {{ $product->item_code }})
                </div>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('opening.stocks.index') }}" class="btn-back-link">
                <i class="las la-arrow-left"></i> Back to Opening Stocks List
            </a>
            <a href="{{ route('opening.stocks.create') }}" class="btn-back-link" style="opacity:0.9;">
                <i class="las la-plus-circle"></i> Add New Stock
            </a>
            <a href="{{ route('product') }}" class="btn-back-link" style="opacity:0.8;">
                <i class="las la-boxes"></i> Products Catalog
            </a>
        </div>
    </div>

    {{-- Branch Selector Card (Super Admin Only) --}}
    @if($isSuperAdmin)
    <div class="branch-card">
        <i class="las la-code-branch" style="font-size:24px;color:#1e3a5f;"></i>
        <label for="branch_switcher">Target Branch:</label>
        <select id="branch_switcher" onchange="switchBranch(this.value)">
            @foreach($allBranches as $b)
                <option value="{{ $b->id }}" {{ $b->id == $selectedBranchId ? 'selected' : '' }}>
                    {{ $b->name }}
                </option>
            @endforeach
        </select>
        <span style="font-size:12px;color:#64748b;font-weight:600;">
            ℹ️ Allocations and stock are loaded for <strong>{{ $selectedBranch?->name ?? 'selected branch' }}</strong>
        </span>
    </div>

    {{-- System-Wide Stock Summary Chips (Super Admin) --}}
    @if(count($globalStockSummary) > 0)
    <div style="background:#ffffff; border:1.5px solid #e2e8f0; border-radius:14px; padding:0.85rem 1.25rem; margin-bottom:1.25rem; box-shadow:0 2px 8px rgba(15,23,42,0.02);">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:0.5rem;">
            <i class="las la-globe" style="color:#1e3a5f; font-size:18px;"></i>
            <span style="color:#475569; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">System-Wide Live Stock Across Branches:</span>
        </div>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            @foreach($globalStockSummary as $row)
                <div style="display:flex; align-items:center; gap:6px; background:#f8fafc; border:1.5px solid {{ $row->branch_id == $selectedBranchId ? '#1e3a5f' : '#cbd5e1' }}; padding:4px 12px; border-radius:8px; font-size:12px;">
                    <span style="color:#64748b; font-weight:600;">{{ $row->branch_name }}:</span>
                    <span style="color:#0f172a; font-weight:800;">{{ number_format($row->qty, 2) }}</span>
                    @if($row->branch_id != $selectedBranchId)
                        <a href="{{ route('opening.stocks.edit', $product->id) }}?branch_id={{ $row->branch_id }}" style="margin-left:4px; font-size:11px; color:#1e3a5f; font-weight:700; text-decoration:underline;">Switch</a>
                    @else
                        <span class="badge" style="background:#166534; color:#fff; font-size:9.5px; padding:2px 7px; border-radius:10px;">Active</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    {{-- Main Edit Form --}}
    <form id="osmEditForm" method="POST" action="{{ route('opening.stocks.update', $product->id) }}">
        @csrf
        <input type="hidden" name="branch_id" id="form_branch_id" value="{{ $selectedBranchId }}">
        <input type="hidden" name="allocation_data" id="allocation_data" value="[]">

        <div class="table-card">
            <div class="osm-table-responsive">
                <table class="osm-table">
                    <thead class="osm-thead">
                        <tr>
                            <th style="width:44px;text-align:center;">#</th>
                            <th style="min-width:270px;">Product Name / SKU</th>
                            <th style="min-width:340px;">Location &amp; Allocated Qty</th>
                            <th style="width:130px;">Wholesale ₨</th>
                            <th style="width:130px;">Retail ₨</th>
                            <th style="width:110px;">Alert Qty</th>
                            <th style="width:140px;">Total Opening Qty</th>
                            <th style="width:80px;text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="osm_rows">
                        <tr class="osm-row" id="row_1" data-row="1">
                            {{-- Mobile Header --}}
                            <td class="osm-cell-mobile-header d-md-none">
                                <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom" style="border-color:#f1f5f9 !important;">
                                    <span class="badge" style="background:#1e3a5f; color:#ffffff; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:20px;">#1</span>
                                    <span class="badge bg-primary" style="font-size:11px; font-weight:700;">Editing Mode</span>
                                </div>
                            </td>

                            {{-- Row # --}}
                            <td class="osm-row-num d-none d-md-table-cell">#1</td>

                            {{-- Product Name / SKU Details --}}
                            <td class="osm-cell-product">
                                <span class="fi-label">Product Details</span>
                                <div class="product-info-box">
                                    <div class="prod-code-tags">
                                        <span class="badge-sku">{{ $product->item_code }}</span>
                                        @if($product->category_relation)
                                            <span class="badge-cat">{{ $product->category_relation->name }}</span>
                                        @endif
                                        <span class="badge-unit">{{ $product->unit?->name ?? 'pcs' }}</span>
                                    </div>
                                    <div class="prod-name-main">{{ $product->item_name }}</div>
                                    @if($product->item_name_urdu)
                                        <div class="prod-name-urdu">{{ $product->item_name_urdu }}</div>
                                    @endif
                                    <div>
                                        <span class="stock-badge {{ $currentStock <= 0 ? 'zero' : '' }}" id="stk_badge">
                                            <i class="las la-warehouse"></i> Live Stock: {{ number_format($currentStock, 2) }} {{ $product->unit?->name ?? 'pcs' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- Location & Allocated Qty (Dynamic Sub-rows) --}}
                            <td class="osm-cell-location">
                                <span class="fi-label">Warehouse / Location &amp; Quantities</span>
                                <div class="alloc-container" id="alloc_rows">
                                    @php
                                        $nonZeroAllocs = $currentAllocs->filter(fn($a) => (float)$a->quantity != 0);
                                    @endphp
                                    @if($nonZeroAllocs->isNotEmpty())
                                        @foreach($nonZeroAllocs as $alloc)
                                            <div class="alloc-inline-row">
                                                <select class="alloc-type" onchange="calcTotal()">
                                                    <option value="">-- Select Location --</option>
                                                    <option value="shop" {{ is_null($alloc->warehouse_id) ? 'selected' : '' }}>🏪 Branch / Shop Stock</option>
                                                    @if(count($warehouses) > 0)
                                                        <optgroup label="── Warehouses ──">
                                                            @foreach($warehouses as $w)
                                                                <option value="wh_{{ $w->id }}" {{ (string)$alloc->warehouse_id === (string)$w->id ? 'selected' : '' }}>
                                                                    📦 {{ $w->warehouse_name ?? $w->name }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endif
                                                </select>
                                                <input type="number" class="alloc-qty" placeholder="Qty" step="0.01" min="0"
                                                       value="{{ (float)$alloc->quantity }}"
                                                       oninput="calcTotal()" onkeydown="handleAllocEnter(event)">
                                                <button type="button" class="btn-del-alloc" onclick="delAllocLine(this)" title="Remove location">&#10005;</button>
                                            </div>
                                        @endforeach
                                    @else
                                        {{-- Default 1 row if no allocations exist in DB --}}
                                        <div class="alloc-inline-row">
                                            <select class="alloc-type" onchange="calcTotal()">
                                                <option value="">-- Select Location --</option>
                                                <option value="shop" selected>🏪 Branch / Shop Stock</option>
                                                @if(count($warehouses) > 0)
                                                    <optgroup label="── Warehouses ──">
                                                        @foreach($warehouses as $w)
                                                            <option value="wh_{{ $w->id }}">
                                                                📦 {{ $w->warehouse_name ?? $w->name }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            </select>
                                            <input type="number" class="alloc-qty" placeholder="Qty" step="0.01" min="0"
                                                   value="{{ $currentStock > 0 ? (float)$currentStock : '' }}"
                                                   oninput="calcTotal()" onkeydown="handleAllocEnter(event)">
                                            <button type="button" class="btn-del-alloc" onclick="delAllocLine(this)" title="Remove location">&#10005;</button>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn-add-alloc" onclick="addAllocLine()">
                                    <i class="las la-plus"></i> Add Location
                                </button>
                                <div id="oqty_label" style="font-size:11.5px;color:#1e3a5f;font-weight:700;margin-top:6px;display:flex;align-items:center;gap:6px;">
                                    <span>ℹ️ Total Allocated:</span>
                                    <span id="oqty_display" style="background:#1e3a5f;color:#fff;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:800;">
                                        {{ number_format($currentStock, 2) }}
                                    </span>
                                </div>
                            </td>

                            {{-- Wholesale Price --}}
                            <td class="osm-cell-price-w">
                                <span class="fi-label">Wholesale Price</span>
                                <div class="input-with-prefix">
                                    <span class="prefix">PKR</span>
                                    <input type="number" class="fi fi-num price-wholesale" id="wholesale_price" name="wholesale_price"
                                           value="{{ (float)($product->wholesale_price ?? 0) > 0 ? (float)$product->wholesale_price : '' }}"
                                           placeholder="0.00" step="0.01" min="0">
                                </div>
                            </td>

                            {{-- Retail Price --}}
                            <td class="osm-cell-price-r">
                                <span class="fi-label">Retail Price</span>
                                <div class="input-with-prefix">
                                    <span class="prefix">PKR</span>
                                    <input type="number" class="fi fi-num price-retail" id="retail_price" name="retail_price"
                                           value="{{ (float)($product->price ?? 0) > 0 ? (float)$product->price : '' }}"
                                           placeholder="0.00" step="0.01" min="0">
                                </div>
                                <div id="price_warning_msg" style="display:none; color:#dc2626; font-size:10px; font-weight:700; margin-top:4px;">
                                    ⚠️ Retail &lt; Wholesale
                                </div>
                            </td>

                            {{-- Alert Qty --}}
                            <td class="osm-cell-alert">
                                <span class="fi-label">Alert Qty</span>
                                <input type="number" class="fi fi-num" id="alert_qty" name="alert_qty"
                                       value="{{ (float)($product->alert_quantity ?? 0) > 0 ? (float)$product->alert_quantity : '' }}"
                                       placeholder="0" step="0.01" min="0">
                            </td>

                            {{-- Total Opening Qty (Auto-calculated) --}}
                            <td class="osm-cell-total">
                                <span class="fi-label">Opening Qty</span>
                                <input type="number" class="fi fi-num" id="opening_qty" name="opening_qty"
                                       value="{{ (float)$currentStock > 0 ? (float)$currentStock : '' }}"
                                       placeholder="0.00" step="0.01" min="0" readonly
                                       style="background:#f1f5f9;border-color:#cbd5e1;color:#1e3a5f;font-weight:800;cursor:not-allowed;"
                                       title="Auto-calculated from location allocations">
                                <div class="delta-info delta-zero" id="delta_info">No change</div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="osm-cell-actions d-none d-md-table-cell" style="text-align:center; vertical-align:middle;">
                                <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:800; font-size:10.5px; padding:6px 10px; border-radius:12px;">
                                    <i class="las la-pen"></i> Edit
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Footer Action Bar --}}
            <div class="osm-footer">
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <a href="{{ route('opening.stocks.index') }}" class="btn-back-link" style="background:#ffffff;color:#334155;border:1.5px solid #cbd5e1;">
                        <i class="las la-arrow-left"></i> Cancel &amp; Back
                    </a>
                    <div style="font-size:12.5px;color:#64748b;font-weight:600;">
                        Target Branch: <strong style="color:#1e3a5f;">{{ $selectedBranch?->name ?? 'Main Branch' }}</strong>
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn-save-all" id="btn_submit">
                        <i class="las la-save" style="font-size:17px;"></i> Update Opening Stock
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {

    var liveStock      = parseFloat("{{ $currentStock }}") || 0;
    var warehousesData = @json(collect($warehouses)->map(fn($w) => ['id' => $w->id, 'warehouse_name' => $w->warehouse_name ?? $w->name]));

    /* ── Switch branch via redirect (Super Admin) ── */
    window.switchBranch = function(branchId) {
        if (!branchId) return;
        window.location.href = "{{ route('opening.stocks.edit', $product->id) }}?branch_id=" + branchId;
    };

    /* ── Build location options ── */
    function buildLocOpts() {
        var opts = '<option value="">-- Select Location --</option>';
        opts += '<option value="shop">&#127978; Branch / Shop Stock</option>';
        if (warehousesData.length > 0) {
            opts += '<optgroup label="── Warehouses ──">';
            warehousesData.forEach(function(w) {
                opts += '<option value="wh_' + w.id + '">&#128230; ' + (w.warehouse_name || 'Warehouse') + '</option>';
            });
            opts += '</optgroup>';
        }
        return opts;
    }

    /* ── Build one allocation sub-row ── */
    function buildAllocLine() {
        return '<div class="alloc-inline-row">' +
            '<select class="alloc-type" onchange="calcTotal()">' +
            buildLocOpts() +
            '</select>' +
            '<input type="number" class="alloc-qty" placeholder="Qty" step="0.01" min="0" ' +
            'oninput="calcTotal()" ' +
            'onkeydown="handleAllocEnter(event)">' +
            '<button type="button" class="btn-del-alloc" onclick="delAllocLine(this)" title="Remove location">&#10005;</button>' +
            '</div>';
    }

    /* ── Add / delete inline allocation rows ── */
    window.addAllocLine = function() {
        var line = $(buildAllocLine());
        $('#alloc_rows').append(line);
        line.find('select').focus();
        calcTotal();
    };

    window.delAllocLine = function(btn) {
        if ($('#alloc_rows .alloc-inline-row').length <= 1) {
            // If only 1 line left, reset its value instead of leaving 0 lines
            $(btn).closest('.alloc-inline-row').find('.alloc-qty').val('');
        } else {
            $(btn).closest('.alloc-inline-row').remove();
        }
        calcTotal();
    };

    /* Enter key in qty → add next alloc row */
    window.handleAllocEnter = function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addAllocLine();
        }
    };

    /* ── Recalculate Opening Qty total & Delta ── */
    window.calcTotal = function() {
        var total = 0;
        var data = [];

        $('#alloc_rows .alloc-inline-row').each(function() {
            var type = $(this).find('.alloc-type').val();
            var qty  = parseFloat($(this).find('.alloc-qty').val()) || 0;
            total += qty;

            if (type === 'shop') {
                data.push({ location_type: 'shop', quantity: qty });
            } else if (type && type.indexOf('wh_') === 0) {
                data.push({
                    location_type: 'warehouse',
                    warehouse_id: parseInt(type.replace('wh_','')),
                    quantity: qty
                });
            }
        });

        $('#opening_qty').val(total > 0 ? total.toFixed(2) : '');
        $('#oqty_display').text(total.toFixed(2));
        $('#allocation_data').val(JSON.stringify(data));

        /* Delta info calculation */
        var delta = total - liveStock;
        var deltaEl = $('#delta_info');
        if (Math.abs(delta) < 0.001) {
            deltaEl.text('No change from live stock (' + liveStock.toFixed(2) + ')').attr('class', 'delta-info delta-zero');
        } else if (delta > 0) {
            deltaEl.text('▲ +' + delta.toFixed(2) + ' units will be added').attr('class', 'delta-info delta-pos');
        } else {
            deltaEl.text('▼ ' + delta.toFixed(2) + ' units will be removed').attr('class', 'delta-info delta-neg');
        }
    };

    /* ── Wholesale vs Retail Price Check ── */
    function checkPriceWarning() {
        var wholesale = parseFloat($('#wholesale_price').val()) || 0;
        var retail    = parseFloat($('#retail_price').val()) || 0;
        var warningEl = $('#price_warning_msg');

        if (wholesale > 0 && retail > 0 && retail < wholesale) {
            $('#wholesale_price, #retail_price').css({'border-color': '#dc2626', 'background': '#fff1f2'});
            warningEl.show();
        } else {
            $('#wholesale_price, #retail_price').css({'border-color': '', 'background': ''});
            warningEl.hide();
        }
    }

    $(document).on('input change', '#wholesale_price, #retail_price', checkPriceWarning);

    /* ── Flash message helper ── */
    function showGlobalMsg(msg, type) {
        var cls  = type === 'success' ? 'flash-success' : 'flash-error';
        var icon = type === 'success' ? '✅' : '❌';
        var el = $('<div class="' + cls + '">' + icon + ' ' + msg + '</div>');
        $('.osm-wrap').prepend(el);
        setTimeout(function() { el.fadeOut(400, function(){ $(this).remove(); }); }, 4500);
    }

    /* ── AJAX Form Submission ── */
    $('#osmEditForm').on('submit', function(e) {
        e.preventDefault();
        calcTotal();

        var totalQty  = parseFloat($('#opening_qty').val()) || 0;
        var wholesale = parseFloat($('#wholesale_price').val()) || 0;
        var retail    = parseFloat($('#retail_price').val()) || 0;

        if (wholesale > 0 && retail > 0 && retail < wholesale) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Pricing',
                text: 'Retail price cannot be less than Wholesale price.',
                confirmButtonColor: '#1e3a5f'
            });
            return;
        }

        var $btn = $('#btn_submit');
        $btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Updating Stock…');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="las la-save"></i> Update Opening Stock');
                if (res && res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated Successfully!',
                        text: res.message || 'Opening stock updated successfully.',
                        confirmButtonColor: '#1e3a5f',
                        timer: 1600,
                        showConfirmButton: false
                    });
                    setTimeout(function() {
                        window.location.href = "{{ route('opening.stocks.index') }}";
                    }, 1400);
                } else if (res && res.error) {
                    showGlobalMsg(res.error, 'error');
                } else {
                    window.location.href = "{{ route('opening.stocks.index') }}";
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="las la-save"></i> Update Opening Stock');
                var msg = 'Failed to update opening stock. Please check inputs and try again.';
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (r.error) msg = r.error;
                    if (r.message) msg = r.message;
                } catch(e) {}
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: msg,
                    confirmButtonColor: '#1e3a5f'
                });
            }
        });
    });

    // Run initial calculations on load
    calcTotal();
    checkPriceWarning();
});
</script>
@else
    <div class="alert alert-danger m-4">You do not have permission to manage opening stocks.</div>
@endcan
@endsection

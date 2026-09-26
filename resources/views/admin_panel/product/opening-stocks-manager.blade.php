@extends('admin_panel.layout.app')

@section('content')
@can('product.edit')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endsection

<style>
    /* ── Main Container & Layout ── */
    .osm-wrap {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #f8fafc;
        min-height: 100vh;
        padding: 1.5rem;
    }

    /* ── Hero Header ── */
    .osm-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        border-radius: 16px;
        padding: 1.5rem;
        color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
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
        background: rgba(99, 102, 241, 0.2);
        border: 1.5px solid rgba(129, 140, 248, 0.4);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #818cf8;
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
    }
    .osm-badge-erp {
        background: rgba(99, 102, 241, 0.25);
        border: 1px solid rgba(165, 180, 252, 0.3);
        color: #c7d2fe;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .osm-subtitle {
        font-size: 13px;
        color: #94a3b8;
        margin-top: 4px;
    }
    .btn-back-link {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #f1f5f9;
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
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        transform: translateY(-1px);
    }

    /* ── Branch Selector Card (Super Admin) ── */
    .branch-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
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
        border: 1.5px solid #6366f1;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        background: #f8fafc;
        outline: none;
        min-width: 240px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .branch-card select:focus {
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
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
        background: linear-gradient(135deg, #1e293b, #334155);
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
        padding: 14px 10px;
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
        color: #94a3b8;
        font-size: 13px;
        padding-top: 18px !important;
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
        border-color: #6366f1;
        background: #ffffff;
        outline: none;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
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
        margin-bottom: 5px;
        display: block;
    }

    /* Input prefix (Currency ₨) */
    .input-with-prefix {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }
    .input-with-prefix .prefix {
        position: absolute;
        left: 10px;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        pointer-events: none;
    }
    .input-with-prefix input {
        padding-left: 26px !important;
    }

    /* ── Select2 Modern Custom Styling ── */
    .select2-container--default .select2-selection--single {
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 10px !important;
        height: 42px !important;
        background: #f8fafc !important;
        transition: all 0.2s ease !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #0f172a !important;
        padding-left: 12px !important;
        padding-right: 28px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #6366f1 !important;
        background: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12) !important;
    }
    .select2-dropdown {
        border: 1.5px solid #6366f1 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15) !important;
        overflow: hidden !important;
        z-index: 9999 !important;
    }
    .select2-results__option {
        padding: 9px 12px !important;
        font-size: 13px !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: #6366f1 !important;
    }

    .stock-badge {
        display: inline-block;
        background: #dcfce7;
        color: #166534;
        font-size: 10px;
        font-weight: 800;
        padding: 3px 10px;
        border-radius: 20px;
        margin-top: 5px;
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
        border: 1.5px solid #c7d2fe;
        border-radius: 10px;
        padding: 5px 8px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .alloc-inline-row:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
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
        color: #4f46e5;
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
        background: #eef2ff;
        color: #4f46e5;
        border: 1.5px dashed #a5b4fc;
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
        background: #6366f1;
        color: #ffffff;
        border-style: solid;
        border-color: #6366f1;
    }

    .btn-del-row {
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 8px;
        width: 32px;
        height: 32px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 800;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-del-row:hover {
        background: #dc2626;
        color: #ffffff;
        transform: scale(1.05);
    }

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
    .btn-add-row {
        background: #ffffff;
        border: 2px dashed #6366f1;
        color: #6366f1;
        border-radius: 10px;
        padding: 10px 20px;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-add-row:hover {
        background: #eef2ff;
        border-color: #4f46e5;
        color: #4f46e5;
        transform: translateY(-1px);
    }
    .btn-save-all {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 11px 32px;
        font-size: 14.5px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-save-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
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
            padding-bottom: 5rem; /* space for mobile sticky footer */
        }
        .osm-hero {
            padding: 1.2rem;
            border-radius: 14px;
        }
        .osm-hero-content {
            gap: 0.75rem;
        }
        .osm-hero-icon {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }
        .osm-title {
            font-size: 17px;
        }

        /* Convert Table to Mobile Cards */
        .osm-table,
        .osm-table tbody,
        .osm-table tr,
        .osm-table td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .osm-thead {
            display: none !important;
        }
        .osm-row {
            background: #ffffff !important;
            border-radius: 16px !important;
            border: 1.5px solid #e2e8f0 !important;
            margin-bottom: 1.25rem !important;
            padding: 1rem 1rem 1.25rem 1rem !important;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04) !important;
            position: relative !important;
        }
        .osm-row td {
            padding: 6px 0 !important;
            border: none !important;
        }

        /* Row header element inside card */
        .osm-row-num {
            display: inline-block !important;
            position: absolute !important;
            top: 14px !important;
            left: 14px !important;
            background: #eef2ff !important;
            color: #4f46e5 !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            padding: 3px 10px !important;
            border-radius: 20px !important;
            width: auto !important;
            text-align: left !important;
            padding-top: 3px !important;
        }
        .osm-cell-actions {
            position: absolute !important;
            top: 10px !important;
            right: 14px !important;
            width: auto !important;
            padding-top: 0 !important;
        }

        /* Product search container on mobile */
        .osm-cell-product {
            margin-top: 24px;
        }

        /* Grid layout for prices on mobile */
        .mobile-price-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 4px;
        }

        /* Sticky bottom footer bar on mobile */
        .osm-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-top: 1.5px solid #e2e8f0;
            padding: 0.75rem 1rem;
            z-index: 1000;
            box-shadow: 0 -4px 20px rgba(15, 23, 42, 0.08);
            display: flex;
            gap: 0.5rem;
            justify-content: space-between;
            border-radius: 0;
        }
        .btn-add-row {
            flex: 1;
            justify-content: center;
            padding: 10px 12px;
            font-size: 12.5px;
        }
        .btn-save-all {
            flex: 1.4;
            justify-content: center;
            padding: 10px 16px;
            font-size: 13px;
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

    {{-- Hero Header --}}
    <div class="osm-hero">
        <div class="osm-hero-content">
            <div class="osm-hero-icon">
                <i class="las la-boxes"></i>
            </div>
            <div>
                <h4 class="osm-title">
                    Opening Stock Manager
                    <span class="osm-badge-erp">ERP Standard</span>
                </h4>
                <div class="osm-subtitle">Add opening stock — products are global, stock is allocated per branch/warehouse</div>
            </div>
        </div>
        <a href="{{ route('product') }}" class="btn-back-link">
            <i class="las la-arrow-left"></i> Back to Products
        </a>
    </div>

    {{-- Branch Selector (Super Admin Only) --}}
    @if($isSuperAdmin)
    <div class="branch-card">
        <i class="las la-code-branch" style="font-size:22px;color:#6366f1;"></i>
        <label for="branch_selector">Target Branch:</label>
        <select id="branch_selector">
            <option value="">-- Select Branch --</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ $b->id == $userBranchId ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
        </select>
        <span style="font-size:12px;color:#64748b;font-weight:500;">
            ℹ️ Stock allocations will be recorded for the selected branch
        </span>
    </div>
    @endif

    <form id="osmForm" method="POST" action="{{ route('opening.stocks.store') }}">
        @csrf
        <input type="hidden" name="branch_id" id="form_branch_id" value="{{ $userBranchId }}">

        <div class="table-card">
            <div class="osm-table-responsive">
                <table class="osm-table">
                    <thead class="osm-thead">
                        <tr>
                            <th style="width:44px;text-align:center;">#</th>
                            <th style="min-width:240px;">Product Name / SKU</th>
                            <th style="min-width:320px;">Location &amp; Allocated Qty</th>
                            <th style="width:130px;">Wholesale ₨</th>
                            <th style="width:130px;">Retail ₨</th>
                            <th style="width:100px;">Alert Qty</th>
                            <th style="width:120px;">Total Opening Qty</th>
                            <th style="width:44px;text-align:center;"></th>
                        </tr>
                    </thead>
                    <tbody id="osm_rows"></tbody>
                </table>
            </div>

            <div class="osm-footer">
                <button type="button" class="btn-add-row" id="btn_add_row">
                    <i class="las la-plus-circle" style="font-size:16px;"></i> Add Product Row
                </button>
                <button type="submit" class="btn-save-all">
                    <i class="las la-save" style="font-size:17px;"></i> Save All Opening Stocks
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {

    var searchUrl    = "{{ route('opening.stocks.search') }}";
    var warehouseUrl = "{{ route('opening.stocks.warehouses') }}";
    var warehousesData = @json(collect($warehouses)->map(fn($w) => ['id' => $w->id, 'warehouse_name' => $w->warehouse_name, 'name' => $w->warehouse_name]));
    var rowCounter   = 0;

    /* ── Build location options ── */
    function buildLocOpts() {
        var opts = '<option value="">-- Select Location --</option>';
        opts += '<option value="shop">&#127978; Branch / Shop Stock</option>';
        if (warehousesData.length > 0) {
            opts += '<optgroup label="── Warehouses ──">';
            warehousesData.forEach(function(w) {
                opts += '<option value="wh_' + w.id + '">&#128230; ' + (w.warehouse_name || w.name) + '</option>';
            });
            opts += '</optgroup>';
        }
        return opts;
    }

    /* ── Build one allocation sub-row ── */
    function buildAllocLine(idx, rid) {
        rid = rid || ('al' + Date.now() + Math.random().toString(36).slice(2,5));
        return '<div class="alloc-inline-row" id="' + rid + '" data-parent="' + idx + '">' +
            '<select class="alloc-type" onchange="calcTotal(' + idx + ')">' +
            buildLocOpts() +
            '</select>' +
            '<input type="number" class="alloc-qty" placeholder="Qty" step="0.01" min="0" ' +
            'oninput="calcTotal(' + idx + ')" ' +
            'onkeydown="handleAllocEnter(event,' + idx + ')">' +
            '<button type="button" class="btn-del-alloc" onclick="delAllocLine(this,' + idx + ')" title="Remove location">&#10005;</button>' +
            '</div>';
    }

    /* ── Build HTML for one product row ── */
    function buildRow(idx) {
        return `
        <tr class="osm-row" id="row_${idx}" data-row="${idx}">
            <td class="osm-row-num">#${idx}</td>
            <td class="osm-cell-product">
                <span class="fi-label">Product Selection</span>
                <select class="product-sel" id="prod_${idx}" name="rows[${idx}][product_id]" style="width:100%;"></select>
                <span class="stock-badge zero" id="stk_${idx}" style="display:none;">Stock: 0</span>
                <input type="hidden" name="rows[${idx}][allocation_data]" id="alloc_data_${idx}" value="[]">
            </td>
            <td>
                <span class="fi-label">Warehouse / Location &amp; Quantities</span>
                <div class="alloc-container" id="alloc_rows_${idx}"></div>
                <button type="button" class="btn-add-alloc" onclick="addAllocLine(${idx})">
                    <i class="las la-plus"></i> Add Location
                </button>
                <div id="oqty_label_${idx}" style="font-size:11px;color:#4f46e5;font-weight:700;margin-top:6px;display:none;">
                    ℹ️ Total Allocated: <span id="oqty_display_${idx}">0</span>
                </div>
            </td>
            <td>
                <div class="mobile-price-grid">
                    <div>
                        <span class="fi-label">Wholesale Price</span>
                        <div class="input-with-prefix">
                            <span class="prefix">₨</span>
                            <input type="number" class="fi fi-num price-wholesale" name="rows[${idx}][wholesale_price]" placeholder="0.00" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="d-md-none">
                        <span class="fi-label">Retail Price</span>
                        <div class="input-with-prefix">
                            <span class="prefix">₨</span>
                            <input type="number" class="fi fi-num price-retail" name="rows[${idx}][retail_price]" placeholder="0.00" step="0.01" min="0">
                        </div>
                    </div>
                </div>
            </td>
            <td class="d-none d-md-table-cell">
                <span class="fi-label">Retail Price</span>
                <div class="input-with-prefix">
                    <span class="prefix">₨</span>
                    <input type="number" class="fi fi-num price-retail" name="rows[${idx}][retail_price]" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="price-warning-msg" style="display:none; color:#dc2626; font-size:10px; font-weight:700; margin-top:4px;">⚠️ Retail < Wholesale</div>
            </td>
            <td>
                <span class="fi-label">Alert Qty</span>
                <input type="number" class="fi fi-num" name="rows[${idx}][alert_qty]" placeholder="0" step="0.01" min="0">
            </td>
            <td>
                <span class="fi-label">Opening Qty</span>
                <input type="number" class="fi fi-num" id="oqty_${idx}"
                       name="rows[${idx}][opening_qty]" placeholder="0.00" step="0.01" min="0"
                       readonly
                       style="background:#eef2ff;border-color:#a5b4fc;color:#4f46e5;font-weight:800;cursor:not-allowed;"
                       title="Auto-calculated from warehouse locations">
            </td>
            <td class="osm-cell-actions">
                <button type="button" class="btn-del-row" onclick="delRow(${idx})" title="Remove Product Row">✕</button>
            </td>
        </tr>`;
    }

    /* ── Add / delete product rows ── */
    function addRow() {
        rowCounter++;
        $('#osm_rows').append(buildRow(rowCounter));
        initSelect2(rowCounter);
        renumber();
        // Auto-add first location row
        addAllocLine(rowCounter);
    }

    window.delRow = function(idx) {
        if ($('#osm_rows tr').length <= 1) {
            Swal.fire({
                icon: 'info',
                title: 'Minimum Row Required',
                text: 'At least one product row must remain.',
                confirmButtonColor: '#6366f1'
            });
            return;
        }
        $('#row_' + idx).remove();
        renumber();
    };

    function renumber() {
        $('#osm_rows tr').each(function(i) {
            $(this).find('.osm-row-num').text('#' + (i + 1));
        });
    }

    /* ── Select2 init (global products) ── */
    function initSelect2(idx) {
        $('#prod_' + idx).select2({
            placeholder: '🔍 Search product name or SKU...',
            allowClear: true,
            minimumInputLength: 0,
            width: '100%',
            ajax: {
                url: searchUrl,
                dataType: 'json',
                delay: 200,
                cache: false,
                data: function(params) {
                    return {
                        q: params.term || '',
                        branch_id: $('#form_branch_id').val() || ''
                    };
                },
                processResults: function(data) {
                    return { results: data };
                }
            }
        }).on('select2:select', function(e) {
            var d   = e.params.data;
            var row = $(this).closest('tr');
            var idx = row.data('row');

            // Sync wholesale, retail, alert_qty for both mobile and desktop input elements
            row.find('.price-wholesale').val(d.wholesale_price || '');
            row.find('.price-retail').val(d.retail_price || '');
            row.find('[name$="[alert_qty]"]').val(d.alert_quantity || '');

            var stk = parseFloat(d.current_stock || 0);
            $('#stk_' + idx).text('Current Stock: ' + stk).css('display','inline-block')
                            .toggleClass('zero', stk <= 0);
        });
    }

    /* ── Add / delete inline allocation rows ── */
    window.addAllocLine = function(idx) {
        var line = buildAllocLine(idx);
        $('#alloc_rows_' + idx).append(line);
        $('#alloc_rows_' + idx + ' .alloc-inline-row:last select').focus();
        calcTotal(idx);
    };

    window.delAllocLine = function(btn, idx) {
        $(btn).closest('.alloc-inline-row').remove();
        calcTotal(idx);
    };

    /* Enter key in qty → add next alloc row */
    window.handleAllocEnter = function(e, idx) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addAllocLine(idx);
        }
    };

    /* Recalculate Opening Qty total */
    window.calcTotal = function(idx) {
        var total = 0;
        $('#alloc_rows_' + idx + ' .alloc-inline-row').each(function() {
            total += parseFloat($(this).find('.alloc-qty').val()) || 0;
        });
        $('#oqty_' + idx).val(total > 0 ? total.toFixed(2) : '');
        var lbl = $('#oqty_label_' + idx);
        if (total > 0) {
            $('#oqty_display_' + idx).text(total.toFixed(2));
            lbl.show();
        } else {
            lbl.hide();
        }
        buildAllocJson(idx);
    };

    function buildAllocJson(idx) {
        var data = [];
        var container = document.getElementById('alloc_rows_' + idx);
        if (!container) { $('#alloc_data_' + idx).val('[]'); return; }

        $(container).find('.alloc-inline-row').each(function() {
            var type = $(this).find('.alloc-type').val();
            var qty  = parseFloat($(this).find('.alloc-qty').val()) || 0;
            if (type === 'shop') {
                data.push({ location_type: 'shop', quantity: qty });
            } else if (type && type.indexOf('wh_') === 0) {
                data.push({ location_type: 'warehouse', warehouse_id: type.replace('wh_',''), quantity: qty });
            }
        });

        $('#alloc_data_' + idx).val(JSON.stringify(data));
    }

    /* ── Branch change (super admin) ── */
    $('#branch_selector').on('change', function() {
        var bid = $(this).val();
        $('#form_branch_id').val(bid);

        if (bid) {
            $.getJSON(warehouseUrl, {branch_id: bid}, function(data) {
                warehousesData = data;
                rebuildAllocDropdowns();
            });
        } else {
            warehousesData = [];
            rebuildAllocDropdowns();
        }
        $('#osm_rows .product-sel').val(null).trigger('change');
    });

    /* Rebuild every alloc-type select to reflect current warehousesData */
    function rebuildAllocDropdowns() {
        var newOpts = buildLocOpts();
        $('#osm_rows .alloc-type').each(function() {
            var currentVal = $(this).val();
            $(this).html(newOpts);
            if (currentVal) { $(this).val(currentVal); }
        });
    }

    /* ── Inline error/success display ── */
    function showRowError(idx, msg) {
        $('#row_err_' + idx).remove();
        var errHtml = '<div id="row_err_' + idx + '" style="color:#dc2626;font-size:11px;font-weight:700;margin-top:6px;padding:6px 10px;background:#fee2e2;border-radius:8px;border:1px solid #fca5a5;">⚠️ ' + msg + '</div>';
        $('#row_' + idx + ' .osm-cell-product').append(errHtml);
        $('#row_' + idx).css('outline','2px solid #fca5a5');
    }
    function clearRowErrors() {
        $('#osm_rows tr').css('outline','');
        $('[id^="row_err_"]').remove();
    }
    function showGlobalMsg(msg, type) {
        var cls  = type === 'success' ? 'flash-success' : 'flash-error';
        var icon = type === 'success' ? '✅' : '❌';
        var el = $('<div class="' + cls + '">' + icon + ' ' + msg + '</div>');
        $('.osm-wrap').prepend(el);
        setTimeout(function() { el.fadeOut(400, function(){ $(this).remove(); }); }, 4500);
    }

    /* ── AJAX Form Submit ── */
    $('#osmForm').on('submit', function(e) {
        e.preventDefault();
        clearRowErrors();

        /* Client-side validate */
        var ok = true;
        $('#osm_rows tr').each(function() {
            var idx = $(this).data('row');
            buildAllocJson(idx);
            var pid = $('#prod_' + idx).val();
            var qty = parseFloat($('#oqty_' + idx).val()) || 0;
            if (!pid) {
                showRowError(idx, 'Please select a product.');
                ok = false; return false;
            }
            if (qty <= 0) {
                showRowError(idx, 'Add at least one location with quantity > 0.');
                ok = false; return false;
            }

            // Price validation check: Retail >= Wholesale
            var wholesale = parseFloat($(this).find('.price-wholesale').val()) || 0;
            var retail = parseFloat($(this).find('.price-retail').val()) || 0;
            if (wholesale > 0 && retail > 0 && retail < wholesale) {
                showRowError(idx, 'Retail price cannot be less than Wholesale price.');
                $(this).find('.price-wholesale, .price-retail').css('border-color', '#dc2626');
                ok = false; return false;
            }
        });
        if (!ok) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please check the product rows for missing inputs or invalid pricing.',
                confirmButtonColor: '#6366f1'
            });
            return;
        }

        var $btn = $('.btn-save-all');
        $btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Saving Opening Stocks…');

        $.ajax({
            url   : $(this).attr('action'),
            method: 'POST',
            data  : $(this).serialize(),
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="las la-save"></i> Save All Opening Stocks');
                if (res && res.success) {
                    showGlobalMsg(res.message || 'Opening stocks saved! Redirecting...', 'success');
                    setTimeout(function() {
                        window.location.href = "{{ route('product') }}";
                    }, 1200);
                } else if (res && res.error) {
                    showGlobalMsg(res.error, 'error');
                    if (res.row_idx) showRowError(res.row_idx, res.error);
                } else {
                    showGlobalMsg('Saved successfully! Redirecting...', 'success');
                    setTimeout(function() {
                        window.location.href = "{{ route('product') }}";
                    }, 1200);
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="las la-save"></i> Save All Opening Stocks');
                var msg = 'Server error occurred. Please try again.';
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (r.error)   msg = r.error;
                    if (r.message) msg = r.message;
                    if (r.row_idx) showRowError(r.row_idx, msg);
                } catch(ex) {}
                showGlobalMsg(msg, 'error');
            }
        });
    });

    /* ── Price Sync between Mobile/Desktop & Validation ── */
    $(document).on('input change', '.price-wholesale, .price-retail', function() {
        var row = $(this).closest('tr');
        var val = $(this).val();

        // Keep desktop and mobile duplicate input values synchronized
        if ($(this).hasClass('price-wholesale')) {
            row.find('.price-wholesale').val(val);
        } else if ($(this).hasClass('price-retail')) {
            row.find('.price-retail').val(val);
        }

        var wholesale = parseFloat(row.find('.price-wholesale').first().val()) || 0;
        var retail = parseFloat(row.find('.price-retail').first().val()) || 0;
        var warningMsg = row.find('.price-warning-msg');

        if (wholesale > 0 && retail > 0 && retail < wholesale) {
            row.find('.price-wholesale, .price-retail').css({'border-color': '#dc2626', 'background': '#fff1f2'});
            warningMsg.show();
        } else {
            row.find('.price-wholesale, .price-retail').css({'border-color': '', 'background': ''});
            warningMsg.hide();
        }
    });

    /* ── Init ── */
    $('#btn_add_row').on('click', addRow);
    addRow(); // load initial product row
});
</script>
@else
    <div class="alert alert-danger m-4">You do not have permission to manage opening stocks.</div>
@endcan
@endsection


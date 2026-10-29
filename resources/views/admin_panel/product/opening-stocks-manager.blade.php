@extends('admin_panel.layout.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')
@can('product.edit')

<style>
    :root {
        --osm-blue: #0284c7;
        --osm-blue-dark: #0369a1;
        --osm-navy-header: #1e3a5f;
        --osm-bg: #f8fafc;
        --osm-border: #e2e8f0;
        --osm-dark: #0f172a;
        --osm-muted: #64748b;
    }

    body {
        background-color: var(--osm-bg);
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .osm-container {
        padding: 1.25rem;
        max-width: 1440px;
        margin: 0 auto;
    }

    /* ── 1. Top Header ── */
    .osm-top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .osm-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .osm-header-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #e0f2fe;
        color: #0284c7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        border: 1.5px solid #bae6fd;
        flex-shrink: 0;
    }
    .osm-title-main {
        font-size: 22px;
        font-weight: 800;
        color: var(--osm-dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        letter-spacing: -0.02em;
    }
    .badge-erp-tag {
        background: #fef3c7;
        color: #b45309;
        font-size: 10.5px;
        font-weight: 800;
        padding: 3px 9px;
        border-radius: 6px;
        text-transform: uppercase;
        border: 1px solid #fde68a;
    }
    .osm-subtitle-main {
        font-size: 13px;
        color: var(--osm-muted);
        margin-top: 2px;
    }
    .osm-top-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-osm-nav {
        background: #ffffff;
        border: 1.5px solid var(--osm-border);
        color: var(--osm-muted);
        font-size: 12.5px;
        font-weight: 700;
        padding: 7px 14px;
        border-radius: 8px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-osm-nav:hover {
        background: #f1f5f9;
        color: var(--osm-dark);
    }

    /* ── 2. Target Branch Card (Card Header Style) ── */
    .osm-branch-bar {
        background: #ffffff;
        border: 1.5px solid var(--osm-border);
        border-radius: 12px;
        padding: 10px 18px;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .osm-branch-label {
        font-size: 12.5px;
        font-weight: 800;
        color: var(--osm-dark);
        white-space: nowrap;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .osm-branch-select-box {
        position: relative;
        min-width: 260px;
    }
    .osm-branch-select-box select {
        width: 100%;
        border: 1.5px solid var(--osm-border);
        border-radius: 8px;
        padding: 7px 28px 7px 34px;
        font-size: 13px;
        font-weight: 700;
        color: var(--osm-dark);
        background: #ffffff;
        appearance: none;
        -webkit-appearance: none;
        outline: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .osm-branch-select-box select:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    .osm-branch-select-box .b-icon {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #0284c7;
        font-size: 12.5px;
        pointer-events: none;
    }
    .osm-branch-select-box .b-arrow {
        position: absolute;
        right: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 10px;
        pointer-events: none;
    }
    .osm-branch-info-pill {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        font-size: 11.5px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* ── 3. Table Card ── */
    .osm-card-wrapper {
        background: #ffffff;
        border: 1.5px solid var(--osm-border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .osm-table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .osm-main-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    .osm-main-table thead th {
        background: var(--osm-navy-header);
        color: #ffffff;
        font-size: 11.5px;
        font-weight: 700;
        padding: 12px 14px;
        border: none;
        white-space: nowrap;
        letter-spacing: 0.02em;
    }
    .osm-main-table tbody td {
        padding: 14px 12px;
        border-bottom: 1px solid var(--osm-border);
        vertical-align: top;
        background: #ffffff;
    }
    .osm-main-table tbody tr:hover td {
        background: #fafcff;
    }
    .row-index-num {
        font-size: 13px;
        font-weight: 800;
        color: #64748b;
        text-align: center;
        padding-top: 18px !important;
    }

    /* Product card inside table cell */
    .product-cell-container {
        min-width: 250px;
        max-width: 310px;
    }
    .prod-selected-preview {
        display: flex;
        gap: 10px;
        align-items: center;
        background: #ffffff;
        border: 1.5px solid var(--osm-border);
        border-radius: 8px;
        padding: 8px 10px;
        margin-top: 6px;
    }
    .prod-preview-thumb {
        width: 44px;
        height: 44px;
        background: #f1f5f9;
        border-radius: 6px;
        border: 1px solid var(--osm-border);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    .prod-preview-thumb img { width: 100%; height: 100%; object-fit: contain; }
    .prod-preview-thumb i { font-size: 18px; color: #94a3b8; }
    .prod-preview-info { flex: 1; min-width: 0; }
    .prod-preview-title {
        font-size: 12.5px;
        font-weight: 800;
        color: var(--osm-dark);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .prod-preview-code {
        font-size: 10.5px;
        color: #0284c7;
        font-weight: 800;
    }
    .prod-preview-meta {
        font-size: 10.5px;
        color: var(--osm-muted);
        margin-top: 2px;
        line-height: 1.3;
    }

    /* Location & Allocation Rows */
    .alloc-cell-container {
        min-width: 280px;
    }
    .alloc-single-row {
        background: #ffffff;
        border: 1.5px solid var(--osm-border);
        border-radius: 8px;
        padding: 6px 8px;
        margin-bottom: 6px;
        transition: all 0.2s;
    }
    .alloc-single-row:focus-within {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    .alloc-loc-select-wrap {
        position: relative;
        margin-bottom: 6px;
    }
    .alloc-loc-select-wrap select {
        width: 100%;
        border: 1px solid var(--osm-border);
        border-radius: 6px;
        padding: 5px 24px 5px 28px;
        font-size: 12px;
        font-weight: 700;
        color: var(--osm-dark);
        background: #f8fafc;
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
    }
    .alloc-loc-select-wrap .loc-icon {
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        color: #0284c7;
        font-size: 11px;
    }
    .alloc-loc-select-wrap .loc-arrow {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 9px;
    }
    .alloc-qty-sub-row {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .alloc-qty-tag {
        font-size: 11px;
        font-weight: 800;
        color: var(--osm-muted);
        text-transform: uppercase;
    }
    .alloc-qty-box {
        display: flex;
        align-items: center;
        border: 1px solid var(--osm-border);
        border-radius: 6px;
        background: #ffffff;
        flex: 1;
        overflow: hidden;
    }
    .alloc-qty-box input {
        border: none;
        outline: none;
        padding: 4px 8px;
        font-size: 12.5px;
        font-weight: 800;
        color: var(--osm-dark);
        width: 100%;
        text-align: right;
    }
    .alloc-qty-box .unit-tag {
        padding: 4px 8px;
        background: #f8fafc;
        color: var(--osm-muted);
        font-size: 11px;
        font-weight: 700;
        border-left: 1px solid var(--osm-border);
    }
    .btn-rm-loc {
        background: none;
        border: none;
        color: #dc2626;
        cursor: pointer;
        padding: 2px 6px;
        font-size: 12px;
        border-radius: 4px;
        transition: 0.15s;
    }
    .btn-rm-loc:hover { background: #fee2e2; }
    .btn-add-loc-link {
        font-size: 11px;
        font-weight: 800;
        color: #0284c7;
        background: none;
        border: none;
        cursor: pointer;
        padding: 2px 0;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .btn-add-loc-link:hover { text-decoration: underline; }

    /* Number inputs */
    .tbl-clean-group {
        display: flex;
        align-items: center;
        border: 1.5px solid var(--osm-border);
        border-radius: 7px;
        background: #ffffff;
        overflow: hidden;
        min-width: 110px;
    }
    .tbl-clean-group:focus-within {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    .tbl-clean-group input {
        border: none;
        outline: none;
        padding: 6px 8px;
        font-size: 13px;
        font-weight: 700;
        color: var(--osm-dark);
        width: 100%;
        text-align: right;
    }
    .tbl-clean-group .addon-label {
        padding: 6px 8px;
        background: #f8fafc;
        color: var(--osm-muted);
        font-size: 11px;
        font-weight: 700;
        border-left: 1px solid var(--osm-border);
    }
    .tbl-input-single {
        border: 1.5px solid var(--osm-border);
        border-radius: 7px;
        padding: 6px 8px;
        font-size: 13px;
        font-weight: 700;
        color: var(--osm-dark);
        width: 100%;
        min-width: 75px;
        outline: none;
        text-align: center;
    }
    .tbl-input-single:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    .tbl-readonly-total {
        border: 1.5px solid var(--osm-border);
        border-radius: 7px;
        padding: 6px 8px;
        font-size: 13px;
        font-weight: 800;
        color: var(--osm-dark);
        background: #f8fafc;
        width: 100%;
        min-width: 90px;
        text-align: right;
        font-family: monospace;
    }

    .btn-trash-row {
        width: 32px;
        height: 32px;
        border-radius: 7px;
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 13px;
    }
    .btn-trash-row:hover {
        background: #dc2626;
        color: #ffffff;
    }

    /* ── 4. Bottom Footer Bar ── */
    .osm-card-footer {
        padding: 14px 20px;
        background: #ffffff;
        border-top: 1.5px solid var(--osm-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .btn-add-product-row {
        background: #ffffff;
        border: 1.5px solid #0284c7;
        color: #0284c7;
        border-radius: 8px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-add-product-row:hover {
        background: #f0f9ff;
        border-color: #0369a1;
        color: #0369a1;
    }
    .osm-total-counter-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 10px;
        padding: 6px 16px;
    }
    .total-counter-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--osm-dark);
    }
    .total-counter-val {
        font-size: 18px;
        font-weight: 900;
        color: #0284c7;
        font-family: monospace;
    }
    .unit-mixed-badge {
        background: #e0f2fe;
        color: #0369a1;
        font-size: 10.5px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 6px;
        border: 1px solid #bae6fd;
    }

    .osm-bottom-submit-bar {
        display: flex;
        justify-content: flex-end;
        padding-top: 16px;
    }
    .btn-save-all-stocks {
        background: #0284c7;
        border: none;
        color: #ffffff;
        border-radius: 8px;
        padding: 10px 28px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
    }
    .btn-save-all-stocks:hover {
        background: #0369a1;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(2, 132, 199, 0.4);
    }

    /* Select2 Tweaks */
    .select2-container--default .select2-selection--single {
        border: 1.5px solid var(--osm-border) !important;
        border-radius: 7px !important;
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 10px !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        color: var(--osm-dark) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>

<div class="osm-container">

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm py-2 px-3 mb-3 small font-weight-bold" style="border-left: 4px solid #10b981 !important; border-radius: 8px;">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm py-2 px-3 mb-3 small font-weight-bold" style="border-left: 4px solid #ef4444 !important; border-radius: 8px;">
            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ── 1. Top Bar ── --}}
    <div class="osm-top-bar">
        <div class="osm-header-left">
            <div class="osm-header-icon-box">
                <i class="fas fa-database"></i>
            </div>
            <div>
                <h4 class="osm-title-main">
                    Opening Stock Manager
                    <span class="badge-erp-tag">ERP Standard</span>
                </h4>
                <div class="osm-subtitle-main">
                    Add opening stock — products are global, stock is allocated per branch/warehouse.
                </div>
            </div>
        </div>
        <div class="osm-top-actions">
            <a href="{{ route('opening.stocks.index') }}" class="btn-osm-nav">
                <i class="fas fa-arrow-left"></i> Back to Opening Stocks List
            </a>
            <a href="{{ route('product') }}" class="btn-osm-nav">
                <i class="fas fa-cube"></i> Products Catalog
            </a>
        </div>
    </div>

    {{-- ── 2. Target Branch Bar ── --}}
    <div class="osm-branch-bar">
        <label class="osm-branch-label" for="branch_selector">
            Target Branch <span class="text-danger">*</span>
        </label>
        <div class="osm-branch-select-box">
            <i class="fas fa-store b-icon"></i>
            <select id="branch_selector">
                @if($isSuperAdmin)
                    <option value="">-- Select Branch --</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $b->id == $userBranchId ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                @else
                    <option value="{{ $userBranchId }}" selected>{{ Auth::user()->branch?->name ?? 'Current Branch' }}</option>
                @endif
            </select>
            <i class="fas fa-chevron-down b-arrow"></i>
        </div>
        <div class="osm-branch-info-pill">
            <i class="fas fa-info-circle"></i>
            Stock allocations will be recorded for the selected branch.
        </div>
    </div>

    {{-- ── 3. Table Card Form ── --}}
    <form id="osmForm" method="POST" action="{{ route('opening.stocks.store') }}">
        @csrf
        <input type="hidden" name="branch_id" id="form_branch_id" value="{{ $userBranchId }}">

        <div class="osm-card-wrapper">
            <div class="osm-table-responsive">
                <table class="osm-main-table">
                    <thead>
                        <tr>
                            <th style="width: 36px; text-align: center;">#</th>
                            <th style="width: 320px;">Product Name / SKU</th>
                            <th style="width: 300px;">Location &amp; Allocated Qty</th>
                            <th style="width: 140px;">Wholesale Rs</th>
                            <th style="width: 140px;">Retail Rs</th>
                            <th style="width: 100px; text-align: center;">Alert Qty</th>
                            <th style="width: 130px; text-align: right;">Total Opening Qty</th>
                            <th style="width: 50px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="osm_rows">
                        {{-- Populated dynamically via JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Card Footer --}}
            <div class="osm-card-footer">
                <button type="button" class="btn-add-product-row" id="btn_add_row">
                    <i class="fas fa-plus"></i> Add Product Row
                </button>
                <div class="osm-total-counter-pill">
                    <span class="total-counter-label">Total Opening Quantity:</span>
                    <span class="total-counter-val" id="grand_total_qty">0.00</span>
                    <span class="unit-mixed-badge">Mixed Units</span>
                </div>
            </div>
        </div>

        {{-- ── 4. Bottom Action Bar ── --}}
        <div class="osm-bottom-submit-bar">
            <button type="submit" class="btn-save-all-stocks" id="btn_submit_all">
                <i class="fas fa-save"></i> Save All Opening Stocks
            </button>
        </div>
    </form>

</div>

@else
    <div class="alert alert-danger m-4">You do not have permission to manage opening stocks.</div>
@endcan
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {

    var searchUrl      = "{{ route('opening.stocks.search') }}";
    var warehouseUrl   = "{{ route('opening.stocks.warehouses') }}";
    var warehousesData = @json(collect($warehouses)->map(fn($w) => ['id' => $w->id, 'warehouse_name' => $w->warehouse_name, 'name' => $w->warehouse_name]));
    var rowCounter     = 0;

    /* Build Location options HTML */
    function buildLocOpts() {
        var opts = '<option value="shop">Branch / Shop Stock</option>';
        if (warehousesData.length > 0) {
            opts += '<optgroup label="── Warehouses ──">';
            warehousesData.forEach(function(w) {
                opts += '<option value="wh_' + w.id + '">' + (w.warehouse_name || w.name) + '</option>';
            });
            opts += '</optgroup>';
        }
        return opts;
    }

    /* Build one allocation sub-row */
    function buildAllocLine(idx) {
        var unitName = $('#unit_name_' + idx).text() || 'Pcs';
        return `
            <div class="alloc-single-row">
                <div class="alloc-loc-select-wrap">
                    <i class="fas fa-store loc-icon"></i>
                    <select class="alloc-type" onchange="calcTotal(${idx})">
                        ${buildLocOpts()}
                    </select>
                    <i class="fas fa-chevron-down loc-arrow"></i>
                </div>
                <div class="alloc-qty-sub-row">
                    <span class="alloc-qty-tag">Qty</span>
                    <div class="alloc-qty-box">
                        <input type="number" class="alloc-qty" placeholder="0" step="0.01" min="0" value="" oninput="calcTotal(${idx})">
                        <span class="unit-tag alloc-unit-lbl">${unitName}</span>
                    </div>
                    <button type="button" class="btn-rm-loc" onclick="delAllocLine(this, ${idx})" title="Remove location">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
    }

    /* Build HTML for one product row */
    function buildRow(idx) {
        return `
        <tr class="osm-row" id="row_${idx}" data-row="${idx}">
            <td class="row-index-num">${idx}</td>

            {{-- Product Selection --}}
            <td>
                <div class="product-cell-container">
                    <select class="product-sel" id="prod_${idx}" style="width:100%;"></select>
                    <input type="hidden" name="rows[${idx}][product_id]" id="prod_id_${idx}" value="">
                    <input type="hidden" name="rows[${idx}][allocation_data]" id="alloc_data_${idx}" value="[]">
                    
                    {{-- Selected product preview card (hidden until product picked) --}}
                    <div class="prod-selected-preview" id="preview_${idx}" style="display: none;">
                        <div class="prod-preview-thumb" id="thumb_${idx}">
                            <i class="fas fa-cube"></i>
                        </div>
                        <div class="prod-preview-info">
                            <div class="prod-preview-title" id="title_${idx}">—</div>
                            <div class="prod-preview-code" id="code_${idx}">—</div>
                            <div class="prod-preview-meta" id="meta_${idx}">—</div>
                            <span id="unit_name_${idx}" style="display:none;">Pcs</span>
                        </div>
                    </div>
                </div>
            </td>

            {{-- Location & Allocated Qty --}}
            <td>
                <div class="alloc-cell-container">
                    <div class="alloc-list-container" id="alloc_rows_${idx}"></div>
                    <button type="button" class="btn-add-loc-link" onclick="addAllocLine(${idx})">
                        <i class="fas fa-plus-circle"></i> Add Location
                    </button>
                </div>
            </td>

            {{-- Wholesale Price --}}
            <td>
                <div class="tbl-clean-group">
                    <input type="number" step="0.01" min="0" class="price-wholesale" name="rows[${idx}][wholesale_price]" placeholder="0" oninput="checkRowPriceWarning(${idx})">
                    <span class="addon-label">Rs</span>
                </div>
            </td>

            {{-- Retail Price --}}
            <td>
                <div class="tbl-clean-group">
                    <input type="number" step="0.01" min="0" class="price-retail" name="rows[${idx}][retail_price]" placeholder="0" oninput="checkRowPriceWarning(${idx})">
                    <span class="addon-label">Rs</span>
                </div>
                <div class="price-warning-msg text-danger small font-weight-bold mt-1" id="warn_${idx}" style="display:none; font-size:10px;">
                    ⚠️ Retail < Wholesale
                </div>
            </td>

            {{-- Alert Qty --}}
            <td style="text-align: center;">
                <input type="number" step="0.01" min="0" class="tbl-input-single" name="rows[${idx}][alert_qty]" placeholder="0">
            </td>

            {{-- Total Opening Qty --}}
            <td>
                <input type="number" step="0.01" class="tbl-readonly-total" id="oqty_${idx}" name="rows[${idx}][opening_qty]" placeholder="0.00" readonly>
                <input type="hidden" id="carton_qty_${idx}" name="rows[${idx}][carton_qty]" value="0">
                <input type="hidden" id="ppp_${idx}" name="rows[${idx}][piece_per_pack]" value="1">
                <div id="packing_badge_${idx}" class="small font-weight-bold text-success mt-1" style="display:none; font-size:11px;"></div>
            </td>

            {{-- Action Trash --}}
            <td style="text-align: center;">
                <button type="button" class="btn-trash-row" onclick="delRow(${idx})" title="Remove Product Row">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>`;
    }

    /* Add product row */
    window.addRow = function() {
        rowCounter++;
        $('#osm_rows').append(buildRow(rowCounter));
        initSelect2(rowCounter);
        addAllocLine(rowCounter);
        renumberRows();
        calcGrandTotal();
        // Focus the newly added row's product selector
        setTimeout(function() {
            $('#prod_' + rowCounter).select2('open');
        }, 100);
    };

    /* Explicit button click binding */
    $(document).on('click', '#btn_add_row', function(e) {
        e.preventDefault();
        addRow();
    });

    /* Enter key on inputs to spawn a new row */
    $(document).on('keydown', '.price-wholesale, .price-retail, .tbl-input-single, .alloc-qty', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            // Check if this input is in the last row, then add a new row
            var isLastRow = $(this).closest('tr').is(':last-child');
            if (isLastRow) {
                addRow();
            } else {
                // Focus next row's input or addRow
                addRow();
            }
        }
    });

    /* Remove product row */
    window.delRow = function(idx) {
        if ($('#osm_rows tr').length <= 1) {
            Swal.fire({
                icon: 'info',
                title: 'Minimum Row Required',
                text: 'At least one product row must remain.',
                confirmButtonColor: '#0284c7'
            });
            return;
        }
        $('#row_' + idx).remove();
        renumberRows();
        calcGrandTotal();
    };

    function renumberRows() {
        $('#osm_rows tr').each(function(i) {
            $(this).find('.row-index-num').text(i + 1);
        });
    }

    /* Initialize Select2 for product picker */
    function initSelect2(idx) {
        $('#prod_' + idx).select2({
            placeholder: '🔍 Search product or SKU...',
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
            var row = $('#row_' + idx);
            var selectedId = d.id;

            // Set hidden product_id input value
            $('#prod_id_' + idx).val(selectedId);

            row.find('.price-wholesale').val(d.wholesale_price || '');
            row.find('.price-retail').val(d.retail_price || '');
            row.find('[name$="[alert_qty]"]').val(d.alert_quantity || '');

            // Update Unit
            var u = d.unit || d.unit_name || 'Pcs';
            var ppp = parseFloat(d.piece_per_pack) || 1;
            $('#ppp_' + idx).val(ppp);
            $('#unit_name_' + idx).text(u);
            row.find('.alloc-unit-lbl').text(u);

            // Populate Card Preview
            $('#title_' + idx).text(d.text || d.item_name || 'Product');
            $('#code_' + idx).text(d.item_code || '');
            
            var metaArr = [];
            if (d.category_name) metaArr.push(d.category_name);
            if (u) metaArr.push(u);
            if (ppp > 1) metaArr.push('📦 ' + ppp + ' Pcs/Crtn');
            if (d.brand_name) metaArr.push(d.brand_name);
            $('#meta_' + idx).text(metaArr.join(' | ') + (d.item_name_urdu ? ('\n' + d.item_name_urdu) : ''));

            if (d.image) {
                $('#thumb_' + idx).html('<img src="' + d.image + '" alt="">');
            } else {
                $('#thumb_' + idx).html('<i class="fas fa-cube"></i>');
            }
            $('#preview_' + idx).slideDown(150);

            checkRowPriceWarning(idx);

            // Automatically move focus to allocated qty input for fast data entry
            setTimeout(function() {
                row.find('.alloc-qty').first().focus();
            }, 100);
        }).on('select2:clear', function() {
            $('#prod_id_' + idx).val('');
            $('#preview_' + idx).slideUp(150);
        });
    }

    /* Add location sub-row */
    window.addAllocLine = function(idx) {
        var line = $(buildAllocLine(idx));
        $('#alloc_rows_' + idx).append(line);
        calcTotal(idx);
    };

    /* Remove location sub-row */
    window.delAllocLine = function(btn, idx) {
        if ($('#alloc_rows_' + idx + ' .alloc-single-row').length <= 1) {
            $(btn).closest('.alloc-single-row').find('.alloc-qty').val('');
        } else {
            $(btn).closest('.alloc-single-row').remove();
        }
        calcTotal(idx);
    };

    /* Calculate row total & build JSON */
    window.calcTotal = function(idx) {
        var total = 0;
        var data = [];
        var ppp = parseFloat($('#ppp_' + idx).val()) || 1;

        $('#alloc_rows_' + idx + ' .alloc-single-row').each(function() {
            var type = $(this).find('.alloc-type').val();
            var qty  = parseFloat($(this).find('.alloc-qty').val()) || 0;
            total += qty;

            if (type === 'shop') {
                data.push({ location_type: 'shop', quantity: qty });
            } else if (type && type.indexOf('wh_') === 0) {
                data.push({
                    location_type: 'warehouse',
                    warehouse_id: parseInt(type.replace('wh_', '')),
                    quantity: qty
                });
            }
        });

        var pieceQty = ppp > 1 ? (total * ppp) : total;
        $('#carton_qty_' + idx).val(total > 0 ? total : 0);
        $('#oqty_' + idx).val(pieceQty > 0 ? pieceQty.toFixed(2) : '');

        var badge = $('#packing_badge_' + idx);
        if (ppp > 1 && total > 0) {
            badge.html('<i class="fas fa-boxes"></i> ' + total + ' Crtn × ' + ppp + ' = ' + pieceQty + ' Pcs').show();
        } else {
            badge.hide().empty();
        }

        $('#alloc_data_' + idx).val(JSON.stringify(data));
        calcGrandTotal();
    };

    /* Grand Total Calculation */
    function calcGrandTotal() {
        var grand = 0;
        $('.tbl-readonly-total').each(function() {
            grand += parseFloat($(this).val()) || 0;
        });
        $('#grand_total_qty').text(grand.toFixed(2));
    }

    /* Row Price Warning */
    window.checkRowPriceWarning = function(idx) {
        var row = $('#row_' + idx);
        var wholesale = parseFloat(row.find('.price-wholesale').val()) || 0;
        var retail    = parseFloat(row.find('.price-retail').val()) || 0;
        var warn      = $('#warn_' + idx);

        if (wholesale > 0 && retail > 0 && retail <= wholesale) {
            warn.show();
        } else {
            warn.hide();
        }
    };

    /* Branch selector change */
    $('#branch_selector').on('change', function() {
        var bid = $(this).val();
        $('#form_branch_id').val(bid);

        if (bid) {
            $.getJSON(warehouseUrl, { branch_id: bid }, function(data) {
                warehousesData = data;
                rebuildAllocDropdowns();
            });
        } else {
            warehousesData = [];
            rebuildAllocDropdowns();
        }
        $('#osm_rows .product-sel').val(null).trigger('change');
    });

    function rebuildAllocDropdowns() {
        var newOpts = buildLocOpts();
        $('#osm_rows .alloc-type').each(function() {
            var currentVal = $(this).val();
            $(this).html(newOpts);
            if (currentVal) { $(this).val(currentVal); }
        });
    }

    /* Form Submit via AJAX - Filters out unselected/empty rows cleanly */
    $('#osmForm').on('submit', function(e) {
        e.preventDefault();

        var validRowsCount = 0;
        var hasInvalidRow = false;

        $('#osm_rows tr').each(function() {
            var idx = $(this).data('row');
            calcTotal(idx);
            var pid = $('#prod_id_' + idx).val() || $('#prod_' + idx).val();
            var qty = parseFloat($('#oqty_' + idx).val()) || 0;

            // If completely empty (no product selected), we just disable its inputs so it is NOT submitted
            if (!pid && qty === 0) {
                $(this).find('input, select').prop('disabled', true);
                return; // skip this empty row
            }

            // If product selected but qty is 0 or negative
            if (pid && qty <= 0) {
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Quantity Missing', 
                    text: 'Please enter quantity greater than 0 for row #' + $(this).find('.row-index-num').text(), 
                    confirmButtonColor: '#0284c7' 
                });
                hasInvalidRow = true;
                return false;
            }

            // If qty entered but product not selected
            if (!pid && qty > 0) {
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Product Missing', 
                    text: 'Please select a product for row #' + $(this).find('.row-index-num').text(), 
                    confirmButtonColor: '#0284c7' 
                });
                hasInvalidRow = true;
                return false;
            }

            validRowsCount++;
        });

        if (hasInvalidRow) {
            // Re-enable any disabled inputs if validation stopped
            $('#osm_rows tr').find('input, select').prop('disabled', false);
            return;
        }

        if (validRowsCount === 0) {
            $('#osm_rows tr').find('input, select').prop('disabled', false);
            Swal.fire({
                icon: 'warning',
                title: 'No Products to Save',
                text: 'Please select at least one product with quantity to save opening stock.',
                confirmButtonColor: '#0284c7'
            });
            return;
        }

        var $btn = $('#btn_submit_all');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving Opening Stocks…');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save All Opening Stocks');
                if (res && res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved Successfully!',
                        text: res.message || 'Opening stocks have been saved.',
                        confirmButtonColor: '#0284c7',
                        timer: 1600,
                        showConfirmButton: false
                    });
                    setTimeout(function() {
                        window.location.href = "{{ route('opening.stocks.index') }}";
                    }, 1400);
                } else {
                    window.location.href = "{{ route('opening.stocks.index') }}";
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save All Opening Stocks');
                $('#osm_rows tr').find('input, select').prop('disabled', false);
                var msg = 'Failed to save opening stocks. Please check your inputs.';
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (r.error) msg = r.error;
                    if (r.message) msg = r.message;
                } catch(e) {}
                Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#0284c7' });
            }
        });
    });

    // Start with 1 row
    addRow();
});
</script>
@endsection

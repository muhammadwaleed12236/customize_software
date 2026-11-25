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
    .btn-open-serials {
        display: none;
        align-items: center;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        border: 1px solid #f59e0b;
        background: #fef3c7;
        color: #b45309;
        white-space: nowrap;
        height: 28px;
        margin-left: 4px;
        outline: none;
    }
    .btn-open-serials:hover {
        background: #fde68a;
        color: #92400e;
    }
    .btn-open-serials.serials-completed {
        border-color: #86efac;
        background: #dcfce7;
        color: #15803d;
    }
    .btn-open-serials.serials-completed:hover {
        background: #bbf7d0;
        color: #166534;
    }
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

    /* Select2 Dropdown Item Highlights */
    .osm-select2-item {
        padding: 3px 2px;
    }
    .osm-select2-title {
        font-weight: 700;
        color: #0f172a;
        font-size: 13px;
        line-height: 1.35;
    }
    .osm-select2-sub {
        color: #64748b;
        font-size: 11px;
        margin-top: 2px;
        font-weight: 500;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] .osm-select2-title,
    .select2-container--default .select2-results__option--highlighted .osm-select2-title,
    .select2-container--default .select2-results__option[aria-selected="true"] .osm-select2-title {
        color: #ffffff !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] .osm-select2-sub,
    .select2-container--default .select2-results__option--highlighted .osm-select2-sub,
    .select2-container--default .select2-results__option[aria-selected="true"] .osm-select2-sub {
        color: rgba(255, 255, 255, 0.85) !important;
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

    {{-- ── 5. Serial / IMEI Numbers Entry Modal ── --}}
    <div class="modal fade" id="serialModal" tabindex="-1" role="dialog" aria-labelledby="serialModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.2); overflow: hidden;">
                <div class="modal-header py-2 px-3" style="background: #1e3a5f; color: #fff; border-bottom: 2px solid #0284c7;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-barcode mr-2" style="font-size: 18px; color: #38bdf8;"></i>
                        <div>
                            <h6 class="modal-title m-0 font-weight-bold" id="serialModalLabel" style="font-size: 14px; letter-spacing: 0.3px;">
                                Add Serial / IMEI Numbers
                            </h6>
                            <small style="color: #94a3b8; font-size: 11px;">Enter or scan serial numbers for each allocated unit</small>
                        </div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; outline: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-3" style="background: #f8fafc;">
                    <!-- Product & Location Summary Banner -->
                    <div class="d-flex justify-content-between align-items-center p-2 mb-3 rounded" style="background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div>
                            <div class="font-weight-bold text-dark" id="sm_product_name" style="font-size: 13.5px;">Product Name</div>
                            <div class="text-muted small" style="font-size: 11px;">
                                <span id="sm_product_code" class="font-weight-bold text-secondary">SKU: ITEM-0000</span> &bull; 
                                Location: <span class="font-weight-bold text-primary" id="sm_location_name">Branch / Shop Stock</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-primary px-2 py-1" style="font-size: 12px; font-weight: 700;" id="sm_qty_badge">
                                Target: 0 Serials
                            </span>
                        </div>
                    </div>

                    <!-- Toolbar & Modes -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small font-weight-bold">
                            <span id="sm_count_label" class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11.5px;">
                                0 / 0 Entered
                            </span>
                        </span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-secondary active font-weight-bold" id="btn_mode_slots" style="font-size: 11px;">
                                <i class="fas fa-list-ol mr-1"></i> Slots
                            </button>
                            <button type="button" class="btn btn-outline-secondary font-weight-bold" id="btn_mode_bulk" style="font-size: 11px;">
                                <i class="fas fa-paste mr-1"></i> Quick Paste / Scan
                            </button>
                        </div>
                    </div>

                    <!-- View 1: Slots (1 to N) -->
                    <div id="sm_slots_container" style="max-height: 320px; overflow-y: auto; padding-right: 4px;">
                        <!-- Dynamically generated input fields -->
                    </div>

                    <!-- View 2: Quick Bulk Paste / Scan Textarea (Hidden by default) -->
                    <div id="sm_bulk_container" style="display: none;">
                        <p class="small text-muted mb-1">
                            <i class="fas fa-info-circle mr-1 text-primary"></i> Paste serial numbers separated by newlines, commas, or scan continuously with a barcode gun:
                        </p>
                        <textarea id="sm_bulk_textarea" class="form-control font-monospace" rows="7" placeholder="Scan or paste serial numbers here...
SN-1001
SN-1002
SN-1003" style="font-size: 12px; line-height: 1.5;"></textarea>
                        <div class="d-flex justify-content-end mt-2">
                            <button type="button" class="btn btn-sm btn-primary" id="btn_apply_bulk_serials" style="font-size: 11.5px; font-weight: 700;">
                                <i class="fas fa-check mr-1"></i> Apply to Slots
                            </button>
                        </div>
                    </div>

                    <!-- Duplicate or Error Alert -->
                    <div id="sm_alert" class="alert alert-danger py-2 px-3 mt-2 mb-0 small" style="display: none; font-size: 11.5px; font-weight: 600;"></div>
                </div>

                <div class="modal-footer py-2 px-3" style="background: #ffffff; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-light border text-secondary font-weight-bold px-3" data-dismiss="modal" style="font-size: 12px;">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-sm btn-primary px-3 font-weight-bold" id="btn_save_serials" style="font-size: 12px; background: #0284c7; border-color: #0284c7;">
                        <i class="fas fa-check-circle mr-1"></i> Save Serials
                    </button>
                </div>
            </div>
        </div>
    </div>

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
    var rowProducts    = {}; // Store raw product data per row
    var rowVariants    = {}; // Store variants per row: { idx: [variants] }

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

    /* Build one clean allocation sub-row (Location + Qty) */
    function buildAllocLine(idx) {
        var unitName = $('#unit_name_' + idx).text() || 'Pcs';
        var isSerial = (rowProducts[idx] && rowProducts[idx].is_serial);
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
                        <input type="number" class="alloc-qty" placeholder="0" step="0.01" min="0" value="" oninput="handleQtyInput(this, ${idx})">
                        <span class="unit-tag alloc-unit-lbl">${unitName}</span>
                    </div>
                    <button type="button" class="btn-open-serials" onclick="triggerSerialModalForLine(this, ${idx})" style="${isSerial ? 'display:inline-flex;' : 'display:none;'}" title="Add or View Serial Numbers">
                        <i class="fas fa-barcode mr-1"></i>Serials (<span class="serials-entered-count">0</span>/<span class="serials-required-count">0</span>)
                    </button>
                    <input type="hidden" class="alloc-serials-json" value="[]">
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

            {{-- Product Selection & Variant (Exact Match to User Reference) --}}
            <td>
                <div class="product-cell-container">
                    <select class="product-sel" id="prod_${idx}" style="width:100%;"></select>
                    <input type="hidden" name="rows[${idx}][product_id]" id="prod_id_${idx}" value="">
                    <input type="hidden" name="rows[${idx}][variant_id]" id="row_variant_id_${idx}" value="">
                    <input type="hidden" name="rows[${idx}][allocation_data]" id="alloc_data_${idx}" value="[]">

                    {{-- Unit / Pack Badge Line (Shown after product picked) --}}
                    <div class="prod-unit-line" id="unit_line_${idx}" style="display: none; align-items: center; gap: 6px; margin-top: 6px;">
                        <span class="badge" style="background:#e0f2fe; color:#0284c7; border:1px solid #bae6fd; font-size:11px; font-weight:700; border-radius:4px; padding:2px 7px;">
                            <i class="fas fa-check-circle mr-1"></i><span id="badge_unit_${idx}">Pieces</span>
                        </span>
                        <span class="font-weight-bold text-dark" style="font-size:11.5px;" id="badge_pack_desc_${idx}">Single Pcs</span>
                    </div>
                    
                    {{-- Selected product preview card (hidden until product picked) --}}
                    <div class="prod-selected-preview" id="preview_${idx}" style="display: none; margin-top: 8px;">
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
        delete rowVariants[idx];
        delete rowProducts[idx];
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
            templateResult: function(item) {
                if (!item.id) return item.text;
                var title = item.display_title || item.text || '';
                var code = item.item_code || '';
                return $(`
                    <div class="osm-select2-item">
                        <div class="osm-select2-title">${title}</div>
                        <div class="osm-select2-sub">SKU: ${code}</div>
                    </div>
                `);
            },
            templateSelection: function(item) {
                if (!item.id) return item.text;
                if (item.display_title && item.item_code) {
                    return item.item_code + ' — ' + item.display_title;
                }
                return item.text;
            },
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
            var d = e.params.data;
            var row = $('#row_' + idx);
            var selectedProductId = d.product_id || d.id;
            var selectedVariantId = d.variant_id || '';

            rowProducts[idx] = d;

            // Set hidden product_id & variant_id
            $('#prod_id_' + idx).val(selectedProductId);
            $('#row_variant_id_' + idx).val(selectedVariantId);

            row.find('.price-wholesale').val(d.wholesale_price || '');
            row.find('.price-retail').val(d.retail_price || '');
            row.find('[name$="[alert_qty]"]').val(d.alert_quantity || '');

            // Update Unit & Packaging badge
            var u = d.unit || 'Pieces';
            $('#unit_name_' + idx).text(u);
            row.find('.alloc-unit-lbl').text(u);
            $('#badge_unit_' + idx).text(u);

            var ppp = parseFloat(d.piece_per_pack || 0);
            var packDesc = (d.pack_type === 'Customize' || ppp > 1) ? (ppp + ' Pcs / Carton') : 'Single Pcs';
            $('#badge_pack_desc_' + idx).text(packDesc);
            $('#unit_line_' + idx).css('display', 'flex');

            // Populate Card Preview
            $('#title_' + idx).text(d.display_title || d.item_name || 'Product');
            $('#code_' + idx).text(d.item_code || '');

            var metaArr = [];
            if (d.color) metaArr.push('🎨 Color: ' + d.color);
            if (u) metaArr.push(u);
            $('#meta_' + idx).text(metaArr.join(' | '));

            if (d.image) {
                $('#thumb_' + idx).html('<img src="' + d.image + '" alt="">');
            } else {
                $('#thumb_' + idx).html('<i class="fas fa-cube"></i>');
            }

            // If product is serial, show badge in preview
            $('#preview_' + idx + ' .serial-badge-pill').remove();
            if (d.is_serial) {
                $('#preview_' + idx + ' .prod-preview-info').append(`
                    <div class="serial-badge-pill mt-1">
                        <span class="badge" style="font-size:10px; font-weight:700; background:#fef3c7; color:#b45309; border:1px solid #fde68a; border-radius:3px; padding:2px 6px;">
                            <i class="fas fa-barcode mr-1"></i>Serial Tracked
                        </span>
                    </div>
                `);
            }

            $('#preview_' + idx).slideDown(150);

            // Ensure single clean allocation row
            if ($('#alloc_rows_' + idx + ' .alloc-single-row').length === 0) {
                $('#alloc_rows_' + idx).append(buildAllocLine(idx));
            }

            updateAllocSerialButtons(idx);
            calcTotal(idx);
            checkRowPriceWarning(idx);

            setTimeout(function() {
                row.find('.alloc-qty').first().focus();
            }, 100);
        }).on('select2:clear', function() {
            $('#prod_id_' + idx).val('');
            $('#row_variant_id_' + idx).val('');
            $('#preview_' + idx).slideUp(150);
            $('#preview_' + idx + ' .serial-badge-pill').remove();
            $('#unit_line_' + idx).hide();
            rowProducts[idx] = null;
            updateAllocSerialButtons(idx);
            calcTotal(idx);
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
        var vId = $('#row_variant_id_' + idx).val() || null;

        $('#alloc_rows_' + idx + ' .alloc-single-row').each(function() {
            var type = $(this).find('.alloc-type').val();
            var qty  = parseFloat($(this).find('.alloc-qty').val()) || 0;
            total += qty;

            var serials = [];
            try {
                serials = JSON.parse($(this).find('.alloc-serials-json').val() || '[]');
            } catch(e) {}

            if (type === 'shop') {
                data.push({
                    location_type: 'shop',
                    quantity: qty,
                    variant_id: vId ? parseInt(vId) : null,
                    serials: serials
                });
            } else if (type && type.indexOf('wh_') === 0) {
                data.push({
                    location_type: 'warehouse',
                    warehouse_id: parseInt(type.replace('wh_', '')),
                    variant_id: vId ? parseInt(vId) : null,
                    quantity: qty,
                    serials: serials
                });
            }
        });

        $('#oqty_' + idx).val(total > 0 ? total.toFixed(2) : '');
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

    // =========================================================================
    // ── SERIAL / IMEI NUMBERS MANAGEMENT ENGINE ──
    // =========================================================================
    var currentActiveAllocLine = null;
    var currentActiveRowIdx    = null;
    var currentTargetQty       = 0;
    var qtySerialDebounceTimer = null;

    /* Update Serials button appearance & counts on allocation lines */
    window.updateAllocSerialButtons = function(idx) {
        var isSerial = (rowProducts[idx] && rowProducts[idx].is_serial);
        $('#alloc_rows_' + idx + ' .alloc-single-row').each(function() {
            var $line = $(this);
            var $btn  = $line.find('.btn-open-serials');
            if (!isSerial) {
                $btn.hide();
                return;
            }
            var qty = parseInt($line.find('.alloc-qty').val()) || 0;
            if (qty <= 0) {
                $btn.hide();
                return;
            }
            var serials = [];
            try {
                serials = JSON.parse($line.find('.alloc-serials-json').val() || '[]');
            } catch(e) {}
            var enteredCount = serials.filter(function(s) { return String(s).trim().length > 0; }).length;

            $btn.find('.serials-entered-count').text(enteredCount);
            $btn.find('.serials-required-count').text(qty);

            if (enteredCount === qty && qty > 0) {
                $btn.addClass('serials-completed').html('<i class="fas fa-check-circle mr-1"></i>Serials (' + enteredCount + '/' + qty + ')');
            } else {
                $btn.removeClass('serials-completed').html('<i class="fas fa-barcode mr-1"></i>Serials (' + enteredCount + '/' + qty + ')');
            }
            $btn.css('display', 'inline-flex');
        });
    };

    /* Handle Qty typing / change on allocation row */
    window.handleQtyInput = function(input, idx) {
        calcTotal(idx);
        updateAllocSerialButtons(idx);

        var isSerial = (rowProducts[idx] && rowProducts[idx].is_serial);
        if (isSerial) {
            var $allocLine = $(input).closest('.alloc-single-row');
            var qty = parseInt($(input).val()) || 0;
            if (qty > 0) {
                clearTimeout(qtySerialDebounceTimer);
                qtySerialDebounceTimer = setTimeout(function() {
                    if ($('#serialModal').hasClass('show')) return;

                    var currentSerials = [];
                    try {
                        currentSerials = JSON.parse($allocLine.find('.alloc-serials-json').val() || '[]');
                    } catch(e) {}
                    var validCount = currentSerials.filter(function(s) { return String(s).trim().length > 0; }).length;
                    if (validCount !== qty) {
                        openSerialModal(idx, $allocLine, qty);
                    }
                }, 600);
            }
        }
    };

    /* Also trigger on change / blur to ensure modal opens without delay */
    $(document).on('change', '.alloc-qty', function() {
        var $row = $(this).closest('.osm-row');
        var idx = $row.data('row');
        var isSerial = (rowProducts[idx] && rowProducts[idx].is_serial);
        if (isSerial) {
            var $allocLine = $(this).closest('.alloc-single-row');
            var qty = parseInt($(this).val()) || 0;
            if (qty > 0) {
                clearTimeout(qtySerialDebounceTimer);
                if (!$('#serialModal').hasClass('show')) {
                    var currentSerials = [];
                    try {
                        currentSerials = JSON.parse($allocLine.find('.alloc-serials-json').val() || '[]');
                    } catch(e) {}
                    var validCount = currentSerials.filter(function(s) { return String(s).trim().length > 0; }).length;
                    if (validCount !== qty) {
                        openSerialModal(idx, $allocLine, qty);
                    }
                }
            }
        }
    });

    /* Click on Serials button opens modal */
    window.triggerSerialModalForLine = function(btn, idx) {
        var $allocLine = $(btn).closest('.alloc-single-row');
        var qty = parseInt($allocLine.find('.alloc-qty').val()) || 0;
        if (qty <= 0) {
            Swal.fire({
                icon: 'info',
                title: 'Quantity Needed',
                text: 'Please enter a quantity first before adding serial numbers.',
                confirmButtonColor: '#0284c7'
            });
            $allocLine.find('.alloc-qty').focus();
            return;
        }
        openSerialModal(idx, $allocLine, qty);
    };

    /* Open Serial Modal */
    window.openSerialModal = function(rowIdx, $allocLine, qty) {
        currentActiveRowIdx    = rowIdx;
        currentActiveAllocLine = $allocLine;
        currentTargetQty       = parseInt(qty) || 0;

        var prod = rowProducts[rowIdx] || {};
        var prodTitle = prod.display_title || prod.item_name || 'Product';
        var prodCode  = prod.item_code || '';
        var locSelect = $allocLine.find('.alloc-type');
        var locName   = locSelect.find('option:selected').text() || 'Branch / Shop Stock';

        $('#sm_product_name').text(prodTitle);
        $('#sm_product_code').text('SKU: ' + prodCode);
        $('#sm_location_name').text(locName);
        $('#sm_qty_badge').text('Target: ' + currentTargetQty + ' Serials');

        // Existing serials
        var existing = [];
        try {
            existing = JSON.parse($allocLine.find('.alloc-serials-json').val() || '[]');
        } catch(e) {}

        renderSerialSlots(currentTargetQty, existing);

        // Switch to Slots tab by default
        $('#sm_slots_container').show();
        $('#sm_bulk_container').hide();
        $('#btn_mode_slots').addClass('active btn-secondary').removeClass('btn-outline-secondary');
        $('#btn_mode_bulk').removeClass('active btn-secondary').addClass('btn-outline-secondary');
        $('#sm_alert').hide().text('');

        $('#serialModal').modal('show');
        setTimeout(function() {
            var firstEmpty = $('#sm_slots_container input.sm-slot-input').filter(function() { return !$(this).val(); }).first();
            if (firstEmpty.length) {
                firstEmpty.focus();
            } else {
                $('#sm_slots_container input.sm-slot-input').first().focus();
            }
        }, 400);
    };

    /* Render Slot inputs for N serials */
    function renderSerialSlots(qty, existingValues) {
        var html = '<div class="row g-2">';
        existingValues = existingValues || [];
        for (var i = 0; i < qty; i++) {
            var val = (existingValues[i] !== undefined && existingValues[i] !== null) ? existingValues[i] : '';
            html += `
                <div class="col-md-6 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text font-weight-bold" style="background:#f1f5f9; color:#475569; font-size:11.5px; width:40px; justify-content:center;">#${i + 1}</span>
                        </div>
                        <input type="text" class="form-control sm-slot-input font-monospace" data-slot="${i}" value="${val}" placeholder="Serial / IMEI #${i + 1}" style="font-size:12px; height:32px;">
                    </div>
                </div>
            `;
        }
        html += '</div>';
        $('#sm_slots_container').html(html);
        updateModalCounter();
    }

    /* Modal counter & duplicate validation */
    function updateModalCounter() {
        var entered = 0;
        var seen = {};
        var hasDup = false;
        $('.sm-slot-input').each(function() {
            var v = $(this).val().trim();
            if (v) {
                entered++;
                if (seen[v.toLowerCase()]) {
                    hasDup = true;
                    $(this).css('border-color', '#ef4444');
                } else {
                    seen[v.toLowerCase()] = true;
                    $(this).css('border-color', '');
                }
            } else {
                $(this).css('border-color', '');
            }
        });

        var $badge = $('#sm_count_label');
        $badge.text(entered + ' / ' + currentTargetQty + ' Entered');

        if (hasDup) {
            $('#sm_alert').show().text('Duplicate serial numbers detected! Each serial number must be unique.');
            $badge.removeClass('badge-success badge-warning').addClass('badge-danger');
        } else {
            $('#sm_alert').hide().text('');
            if (entered >= currentTargetQty && currentTargetQty > 0) {
                $badge.removeClass('badge-danger badge-warning').addClass('badge-success');
            } else {
                $badge.removeClass('badge-danger badge-success').addClass('badge-warning');
            }
        }
    }

    $(document).on('input', '.sm-slot-input', function() {
        updateModalCounter();
    });

    /* Enter key in slot jumps to next slot */
    $(document).on('keydown', '.sm-slot-input', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var nextSlot = $(this).closest('.col-md-6').next().find('.sm-slot-input');
            if (nextSlot.length) {
                nextSlot.focus();
            } else {
                $('#btn_save_serials').focus();
            }
        }
    });

    /* Paste multi-line text into a slot auto-distributes */
    $(document).on('paste', '.sm-slot-input', function(e) {
        var clipboardData = e.originalEvent.clipboardData || window.clipboardData;
        var pastedData = clipboardData ? clipboardData.getData('text') : '';
        if (pastedData && (pastedData.indexOf('\n') !== -1 || pastedData.indexOf('\r') !== -1 || pastedData.indexOf('\t') !== -1)) {
            e.preventDefault();
            var lines = pastedData.split(/[\r\n\t]+/).map(function(s) { return s.trim(); }).filter(function(s) { return s.length > 0; });
            var startSlot = parseInt($(this).data('slot')) || 0;
            var $slots = $('.sm-slot-input');
            for (var i = 0; i < lines.length && (startSlot + i) < $slots.length; i++) {
                $slots.eq(startSlot + i).val(lines[i]);
            }
            updateModalCounter();
        }
    });

    /* Tab switching: Slots vs Bulk */
    $('#btn_mode_slots').on('click', function() {
        $('#sm_slots_container').show();
        $('#sm_bulk_container').hide();
        $(this).addClass('active btn-secondary').removeClass('btn-outline-secondary');
        $('#btn_mode_bulk').removeClass('active btn-secondary').addClass('btn-outline-secondary');
    });

    $('#btn_mode_bulk').on('click', function() {
        var lines = [];
        $('.sm-slot-input').each(function() {
            var v = $(this).val().trim();
            if (v) lines.push(v);
        });
        $('#sm_bulk_textarea').val(lines.join('\n'));

        $('#sm_slots_container').hide();
        $('#sm_bulk_container').show();
        $(this).addClass('active btn-secondary').removeClass('btn-outline-secondary');
        $('#btn_mode_slots').removeClass('active btn-secondary').addClass('btn-outline-secondary');
        $('#sm_bulk_textarea').focus();
    });

    $('#btn_apply_bulk_serials').on('click', function() {
        var text = $('#sm_bulk_textarea').val() || '';
        var lines = text.split(/[\r\n,]+/).map(function(s) { return s.trim(); }).filter(function(s) { return s.length > 0; });
        var $slots = $('.sm-slot-input');
        $slots.val('');
        for (var i = 0; i < lines.length && i < $slots.length; i++) {
            $slots.eq(i).val(lines[i]);
        }
        $('#btn_mode_slots').trigger('click');
        updateModalCounter();
    });

    /* Save serials from modal */
    $('#btn_save_serials').on('click', function() {
        var serials = [];
        var duplicates = [];
        var seen = {};

        $('.sm-slot-input').each(function() {
            var v = $(this).val().trim();
            if (v) {
                if (seen[v.toLowerCase()]) {
                    duplicates.push(v);
                } else {
                    seen[v.toLowerCase()] = true;
                }
                serials.push(v);
            }
        });

        if (duplicates.length > 0) {
            $('#sm_alert').show().text('Duplicate serial numbers detected: ' + duplicates.join(', ') + '. Each serial number must be unique.');
            return;
        }

        if (serials.length < currentTargetQty) {
            Swal.fire({
                title: 'Incomplete Serials',
                text: 'You have entered ' + serials.length + ' of ' + currentTargetQty + ' serial numbers. Save anyway?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0284c7',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Save',
                cancelButtonText: 'Back to Edit'
            }).then(function(res) {
                if (res.isConfirmed) {
                    commitSerials(serials);
                }
            });
        } else {
            commitSerials(serials);
        }
    });

    function commitSerials(serials) {
        if (currentActiveAllocLine) {
            currentActiveAllocLine.find('.alloc-serials-json').val(JSON.stringify(serials));
            updateAllocSerialButtons(currentActiveRowIdx);
            calcTotal(currentActiveRowIdx);
        }
        $('#serialModal').modal('hide');
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

            // Serial numbers validation for serial-tracked products
            var prodObj = rowProducts[idx];
            if (pid && qty > 0 && prodObj && prodObj.is_serial) {
                var missingInLine = false;
                var $rowElem = $(this);
                $rowElem.find('.alloc-single-row').each(function() {
                    var lineQty = parseInt($(this).find('.alloc-qty').val()) || 0;
                    if (lineQty > 0) {
                        var sArr = [];
                        try {
                            sArr = JSON.parse($(this).find('.alloc-serials-json').val() || '[]');
                        } catch(e) {}
                        var validCount = sArr.filter(function(s) { return String(s).trim().length > 0; }).length;
                        if (validCount < lineQty) {
                            var locName = $(this).find('.alloc-type option:selected').text();
                            Swal.fire({
                                icon: 'warning',
                                title: 'Serial Numbers Required',
                                text: 'Product "' + (prodObj.display_title || prodObj.item_name) + '" at location "' + locName + '" requires ' + lineQty + ' serial numbers, but only ' + validCount + ' entered. Please enter all serial numbers.',
                                confirmButtonColor: '#0284c7'
                            });
                            missingInLine = true;
                            return false;
                        }
                    }
                });
                if (missingInLine) {
                    hasInvalidRow = true;
                    return false;
                }
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

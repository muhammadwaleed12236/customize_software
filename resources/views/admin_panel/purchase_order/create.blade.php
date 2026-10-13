@extends('admin_panel.layout.app')

@section('content')
@can('purchase.order.create')

<style>
    :root {
        --po-navy:       #0f172a;
        --po-navy-mid:   #1e293b;
        --po-blue:       #2563eb;
        --po-blue-light: #3b82f6;
        --po-blue-pale:  #eff6ff;
        --po-green:      #059669;
        --po-border:     #e2e8f0;
        --po-bg:         #f1f5f9;
        --po-muted:      #64748b;
    }

    .po-page-wrapper { background: var(--po-bg); min-height: 100vh; padding: 16px 0 50px; }

    /* ── Top Banner ── */
    .po-banner {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #2c5282 100%);
        border-radius: 12px; padding: 14px 22px;
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        margin-bottom: 14px;
        box-shadow: 0 4px 18px rgba(15,23,42,.18);
    }
    .po-banner-left { display: flex; align-items: center; gap: 12px; }
    .po-banner-icon {
        width: 40px; height: 40px; border-radius: 10px;
        background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; color: #fcd34d; flex-shrink: 0;
    }
    .po-banner-title { font-size: 18px; font-weight: 800; color: #fff; margin: 0; }
    .po-draft-pill {
        display: inline-flex; align-items: center; gap: 4px;
        background: rgba(252,211,77,.15); border: 1px solid rgba(252,211,77,.4);
        color: #fcd34d; font-size: 10px; font-weight: 800;
        padding: 2px 9px; border-radius: 20px; text-transform: uppercase;
    }
    .po-banner-sub { font-size: 11.5px; color: rgba(255,255,255,.65); margin-top: 2px; display: flex; align-items: center; gap: 8px; }
    .po-banner-actions { display: flex; align-items: center; gap: 7px; }
    .btn-po-cancel {
        background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
        color: #fff !important; padding: 7px 14px; border-radius: 7px;
        font-size: 12px; font-weight: 700; text-decoration: none !important;
        display: inline-flex; align-items: center; gap: 5px; transition: .2s;
    }
    .btn-po-cancel:hover { background: rgba(255,255,255,.2); }
    .btn-po-draft {
        background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.25);
        color: #fcd34d !important; padding: 7px 16px; border-radius: 7px;
        font-size: 12px; font-weight: 800; cursor: pointer;
        display: inline-flex; align-items: center; gap: 5px; transition: .2s;
    }
    .btn-po-draft:hover { background: rgba(255,255,255,.2); }
    .btn-po-submit {
        background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;
        color: #fff !important; padding: 8px 20px; border-radius: 7px;
        font-size: 12.5px; font-weight: 800; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        box-shadow: 0 3px 12px rgba(37,99,235,.35); transition: .2s;
    }
    .btn-po-submit:hover { transform: translateY(-1px); box-shadow: 0 5px 16px rgba(37,99,235,.45); }

    /* ── Compact Fields Card ── */
    .po-fields-card {
        background: #fff; border-radius: 10px;
        border: 1px solid var(--po-border);
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        padding: 16px 20px; margin-bottom: 14px;
    }

    .po-label {
        font-size: 10.5px; font-weight: 700; color: var(--po-muted);
        text-transform: uppercase; letter-spacing: .04em;
        margin-bottom: 4px; display: block;
    }
    .po-input, .po-select {
        width: 100%; border: 1.5px solid var(--po-border); border-radius: 7px;
        padding: 7px 10px; font-size: 12.5px; font-weight: 600;
        color: #0f172a; background: #fff; outline: none;
        appearance: none; -webkit-appearance: none; transition: .2s;
    }
    .po-input:focus, .po-select:focus {
        border-color: var(--po-blue-light);
        box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }
    .po-input.po-auto {
        background: #eff6ff; color: var(--po-blue);
        font-weight: 800; border-color: #bfdbfe; font-size: 13px;
    }
    .po-select-wrap { position: relative; }
    .po-select-wrap::after {
        content: "\f078"; font-family: "Font Awesome 5 Free"; font-weight: 900;
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        color: var(--po-muted); font-size: 10px; pointer-events: none;
    }

    /* Vendor info inline strip */
    .vendor-strip {
        display: none; margin-top: 6px;
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: 7px; padding: 7px 12px;
        display: none; flex-wrap: wrap; gap: 16px;
    }
    .vs-item { display: flex; align-items: center; gap: 6px; }
    .vs-icon { color: #2563eb; font-size: 11px; }
    .vs-val  { font-size: 12px; font-weight: 700; color: #0f172a; }

    /* ── Product Table Card ── */
    .po-table-card {
        background: #fff; border-radius: 10px;
        border: 1px solid var(--po-border);
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        overflow: hidden;
    }
    .po-table-header {
        background: var(--po-navy-mid); padding: 12px 18px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .po-table-header-title {
        color: #fff; font-size: 13px; font-weight: 800;
        display: flex; align-items: center; gap: 7px;
    }
    .po-tbl-actions { display: flex; gap: 7px; }
    .btn-add-row {
        background: linear-gradient(135deg, #059669, #047857); border: none;
        color: #fff !important; padding: 6px 13px; border-radius: 6px;
        font-size: 12px; font-weight: 800; cursor: pointer;
        display: inline-flex; align-items: center; gap: 5px;
        box-shadow: 0 2px 8px rgba(5,150,105,.3); transition: .2s;
    }
    .btn-add-row:hover { transform: translateY(-1px); }

    /* Search bar */
    .po-search-bar {
        padding: 10px 18px; background: #fafbfc;
        border-bottom: 1px solid var(--po-border);
    }
    .po-search-wrap { position: relative; }
    .po-search-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--po-muted); font-size: 12px; }
    .po-search-wrap input {
        width: 100%; border: 1.5px solid var(--po-border); border-radius: 7px;
        padding: 7px 10px 7px 32px; font-size: 12.5px; outline: none;
        background: #fff; transition: .2s;
    }
    .po-search-wrap input:focus { border-color: var(--po-blue-light); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }

    /* Table */
    .po-table { width: 100%; border-collapse: collapse; }
    .po-table thead th {
        background: #f1f5f9; color: #475569; font-size: 10.5px;
        font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        padding: 9px 12px; border-bottom: 2px solid var(--po-border); white-space: nowrap;
    }
    .po-table tbody td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .po-table tbody tr:hover td { background: #fafcff; }

    .td-input {
        border: 1.5px solid var(--po-border); border-radius: 6px;
        padding: 6px 9px; font-size: 12.5px; font-weight: 600;
        width: 100%; outline: none; background: #fff; transition: .2s;
    }
    .td-input:focus { border-color: var(--po-blue-light); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
    .td-readonly { background: #f8fafc !important; color: var(--po-muted); cursor: default; }
    .td-total { background: #f0fdf4 !important; color: #065f46; font-weight: 800; font-family: monospace; }

    .row-num {
        width: 24px; height: 24px; border-radius: 50%;
        background: #e2e8f0; color: #475569; font-size: 10px; font-weight: 800;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .btn-remove-row {
        width: 28px; height: 28px; border-radius: 6px;
        background: #fee2e2; color: #dc2626; border: 1px solid #fecaca;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 12px; transition: .2s;
    }
    .btn-remove-row:hover { background: #dc2626; color: #fff; }
    .btn-breakdown {
        background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;
        border-radius: 5px; font-size: 10px; font-weight: 800;
        padding: 2px 7px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 3px; transition: .2s; margin-top: 3px;
    }
    .btn-breakdown:hover { background: #2563eb; color: #fff; }

    /* Empty */
    .po-empty { text-align: center; padding: 40px 20px; color: var(--po-muted); }
    .po-empty-icon {
        width: 52px; height: 52px; background: #f1f5f9; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 22px; color: #94a3b8; margin-bottom: 10px;
    }
    .po-empty-title { font-size: 13px; font-weight: 700; color: #334155; }
    .po-empty-desc  { font-size: 12px; }

    /* Total bar */
    .po-total-bar {
        background: var(--po-navy-mid); border-radius: 0 0 10px 10px;
        padding: 12px 22px; display: flex; align-items: center; justify-content: flex-end; gap: 14px;
    }
    .po-total-label { font-size: 12px; font-weight: 700; color: rgba(255,255,255,.65); text-transform: uppercase; letter-spacing: .05em; }
    .po-total-value { font-size: 24px; font-weight: 900; color: #fff; font-family: monospace; }
    .po-total-currency { font-size: 13px; font-weight: 800; color: #93c5fd; }

    /* Breakdown */
    .breakdown-row td { padding: 0 !important; border-bottom: 2px solid #e2e8f0 !important; background: #fff !important; }
    .breakdown-inner { padding: 12px 20px 12px 26px; background: #fafbff; border-left: 4px solid #2563eb; }
    .breakdown-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .breakdown-header-title { font-size: 11px; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: .05em; }
    .btn-add-color { background: #2563eb; color: #fff; border: none; border-radius: 5px; font-size: 10px; font-weight: 800; padding: 4px 9px; cursor: pointer; display: inline-flex; align-items: center; gap: 3px; }
    .breakdown-item { display: flex; gap: 7px; align-items: center; margin-bottom: 7px; }
    .breakdown-item .color-sel { flex: 1; }
    .breakdown-item .qty-w { width: 100px; }
    .btn-rm-color { color: #dc2626; background: none; border: none; cursor: pointer; font-size: 15px; padding: 0 3px; }
    .qty-readonly-style { background: #f0fdf4 !important; font-weight: 800; color: #065f46; }

    /* Select2 overrides */
    .select2-container--default .select2-selection--single {
        border: 1.5px solid var(--po-border) !important; border-radius: 7px !important;
        height: 36px !important; display: flex !important; align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 34px !important; padding-left: 10px !important; font-size: 12.5px !important; font-weight: 600 !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 34px !important; right: 6px !important; }
    .select2-dropdown { border-radius: 8px !important; border: 1.5px solid #bfdbfe !important; box-shadow: 0 8px 24px rgba(0,0,0,.1) !important; }
    /* Vendor Input Group with '+' button */
    .vendor-input-group {
        display: flex;
        align-items: center;
        gap: 6px;
        width: 100%;
    }
    .btn-vendor-quick-add {
        width: 36px;
        height: 36px;
        background: #eff6ff;
        border: 1.5px solid #bfdbfe;
        color: #2563eb;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.2s;
    }
    .btn-vendor-quick-add:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(37,99,235,0.25);
    }
</style>

<div class="po-page-wrapper">
<div class="container-fluid px-4">
<form action="{{ route('purchase_orders.store') }}" method="POST" id="poForm">
@csrf

{{-- Alerts --}}
@if ($errors->any())
    <div class="alert alert-danger mb-2 py-2 px-3 small fw-bold border-0" style="border-left:4px solid #ef4444!important;border-radius:8px;">
        <i class="fa fa-exclamation-triangle me-1"></i> {{ $errors->first() }}
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger mb-2 py-2 px-3 small fw-bold border-0" style="border-left:4px solid #ef4444!important;border-radius:8px;">
        <i class="fa fa-times-circle me-1"></i> {{ session('error') }}
    </div>
@endif

{{-- ── BANNER ── --}}
<div class="po-banner">
    <div class="po-banner-left">
        <div class="po-banner-icon"><i class="fas fa-file-invoice"></i></div>
        <div>
            <div class="po-banner-title">New Purchase Order</div>
            <div class="po-banner-sub">
                <span class="po-draft-pill"><i class="fas fa-circle" style="font-size:6px;"></i> Draft</span>
                <span>Official procurement request for your vendor</span>
            </div>
        </div>
    </div>
    <div class="po-banner-actions">
        <a href="{{ route('purchase_orders.index') }}" class="btn-po-cancel">
            <i class="fas fa-arrow-left"></i> Cancel
        </a>
        <button type="submit" name="save_draft" value="1" class="btn-po-draft">
            <i class="fas fa-save"></i> Save Draft
        </button>
        <button type="submit" class="btn-po-submit">
            <i class="fas fa-paper-plane"></i> Submit Purchase Order
        </button>
    </div>
</div>

{{-- ── ONE COMPACT FIELDS CARD ── --}}
<div class="po-fields-card">
    <div class="row g-2 align-items-end">

        {{-- Order Date --}}
        <div class="col-6 col-md-2">
            <label class="po-label">Order Date <span class="text-danger">*</span></label>
            <input type="date" name="order_date" value="{{ date('Y-m-d') }}" class="po-input" required>
        </div>

        {{-- Expected Date --}}
        <div class="col-6 col-md-2">
            <label class="po-label">Expected Date <span class="text-danger">*</span></label>
            <input type="date" name="expected_date" value="{{ date('Y-m-d') }}" class="po-input" required>
        </div>

        {{-- PO Number --}}
        <div class="col-6 col-md-2">
            <label class="po-label">PO Number <span style="color:#2563eb;font-weight:800;font-size:9px;"> AUTO</span></label>
            <input type="text" id="po_number" value="{{ $nextPO }}" class="po-input po-auto" readonly>
        </div>

        {{-- Branch (super admin only) --}}
        @if($isSuperAdmin)
        <div class="col-6 col-md-2">
            <label class="po-label">Branch <span class="text-danger">*</span></label>
            <div class="po-select-wrap">
                <select name="branch_id" id="branch_select" class="po-select" required>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected($b->id == $currentBranch)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @else
            <input type="hidden" name="branch_id" id="branch_select" value="{{ $currentBranch }}">
        @endif

        {{-- Warehouse --}}
        <div class="col-6 col-md-2">
            <label class="po-label">Warehouse <span class="text-danger">*</span></label>
            <div class="po-select-wrap">
                <select name="warehouse_id" id="warehouse_select" class="po-select">
                    <option value="">— Select —</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->warehouse_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Vendor --}}
        <div class="col-6 col-md-{{ $isSuperAdmin ? '2' : '3' }}">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="po-label mb-0">Vendor <span class="text-danger">*</span></label>
            </div>
            <div class="vendor-input-group">
                <div style="flex: 1; min-width: 0;">
                    <select name="vendor_id" id="vendor_select" class="select2-vendor" style="width:100%;" required>
                        <option value="">— Search Vendor —</option>
                        @foreach($vendors as $v)
                            @php $co = is_array($v->company_names) ? implode(', ', $v->company_names) : ($v->company_names ?? ''); @endphp
                            <option value="{{ $v->id }}" data-phone="{{ $v->phone }}" data-address="{{ $v->address }}" data-company="{{ $co }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="btn-vendor-quick-add" id="btnQuickAddVendor" title="Quick Add Vendor">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>

        {{-- Note --}}
        <div class="col-12 col-md-{{ $isSuperAdmin ? '12' : '3' }}">
            <label class="po-label">Note</label>
            <input type="text" name="note" class="po-input" placeholder="Delivery / quality instructions…">
        </div>

    </div>

    {{-- Vendor Info Strip (shown after vendor selected) --}}
    <div id="vendor_strip" class="vendor-strip" style="display:none;">
        <div class="vs-item"><i class="fas fa-phone vs-icon"></i><span class="vs-val" id="v_phone">—</span></div>
        <div class="vs-item"><i class="fas fa-building vs-icon"></i><span class="vs-val" id="v_company">—</span></div>
        <div class="vs-item"><i class="fas fa-map-marker-alt vs-icon"></i><span class="vs-val" id="v_address">—</span></div>
    </div>
</div>

{{-- ── LINE ITEMS ── --}}
<div class="po-table-card">
    <div class="po-table-header">
        <div class="po-table-header-title">
            <i class="fas fa-boxes"></i> Line Items
            <span style="font-size:11px;font-weight:600;color:rgba(255,255,255,.45);margin-left:4px;">— Add products to your order</span>
        </div>
        <div class="po-tbl-actions">
            <button type="button" class="btn-add-row" id="btnAddRow">
                <i class="fas fa-plus-circle"></i> Add Product
            </button>
        </div>
    </div>

    {{-- Search --}}
    <div class="po-search-bar">
        <div class="po-search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="globalProductSearch" placeholder="Search product by name, code or brand…">
        </div>
    </div>

    <div class="table-responsive">
        <table class="po-table">
            <thead>
                <tr>
                    <th style="width:36px;text-align:center;">#</th>
                    <th style="width:270px;">Product</th>
                    <th style="width:120px;">Item Code</th>
                    <th style="width:120px;">Brand</th>
                    <th style="width:140px;text-align:right;">Unit Price (Rs)</th>
                    <th style="width:120px;text-align:center;">Qty</th>
                    <th style="width:150px;text-align:right;">Line Total (Rs)</th>
                    <th style="width:44px;text-align:center;">Act</th>
                </tr>
            </thead>
            <tbody id="poItems"></tbody>
        </table>
    </div>

    <div id="emptyState" class="po-empty">
        <div class="po-empty-icon"><i class="fas fa-box-open"></i></div>
        <div class="po-empty-title">No products added yet</div>
        <div class="po-empty-desc">Click <strong>Add Product</strong> to start building your purchase order</div>
    </div>

    <div class="po-total-bar">
        <div class="po-total-label">Total Order Valuation:</div>
        <div><span class="po-total-currency">Rs </span><span class="po-total-value" id="grandTotal">0.00</span></div>
    </div>
</div>

</form>
</div>
</div>

<!-- Modal: Quick Add Vendor -->
<div class="modal fade" id="quickAddVendorModal" tabindex="-1" role="dialog" aria-labelledby="quickAddVendorTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15); overflow: hidden;">
            <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); color: #fff;">
                <h6 class="modal-title font-weight-bold mb-0" id="quickAddVendorTitle" style="font-size: 14px;">
                    <i class="fas fa-user-plus mr-2" style="color: #60a5fa;"></i> Quick Add Vendor
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="background: transparent; border: none; font-size: 20px; opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickAddVendorForm">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <input type="hidden" name="branch_id" id="modal_vendor_branch_id" value="{{ $currentBranch }}">

                    <div class="mb-3">
                        <label class="po-label">Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="modal_vendor_name" class="po-input" placeholder="e.g. Al-Madina Traders" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="po-label">Phone / Contact</label>
                            <input type="text" name="phone" id="modal_vendor_phone" class="po-input" placeholder="03001234567">
                        </div>
                        <div class="col-6">
                            <label class="po-label">Opening Balance (PKR)</label>
                            <input type="number" step="0.01" min="0" name="opening_balance" id="modal_vendor_opening" class="po-input" placeholder="0.00" value="0.00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="po-label">Company Name(s)</label>
                        <input type="text" name="company_names_raw" id="modal_vendor_company" class="po-input" placeholder="e.g. ProWave Tech (comma separated if multiple)">
                    </div>

                    <div class="mb-1">
                        <label class="po-label">Warehouse / Shop Address</label>
                        <input type="text" name="address" id="modal_vendor_address" class="po-input" placeholder="e.g. Shop #12, Market, City">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-dismiss="modal" style="border-radius: 6px; font-weight: 700;">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4" id="btnSubmitQuickVendor" style="background: #2563eb; border: none; border-radius: 6px; font-weight: 700;">
                        <i class="fas fa-check-circle mr-1"></i> Save Vendor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endcan
@endsection

@section('js')
<script>
$(document).ready(function() {

    // Vendor Select2
    $('.select2-vendor').select2({ width: '100%', placeholder: '— Search Vendor —' });

    // Product Select2
    function initProductSelect2(el) {
        $(el).select2({
            minimumInputLength: 0,
            allowClear: true,
            ajax: {
                url: '{{ route("search-products") }}', dataType: 'json', delay: 200,
                data: function(p) { return { q: p.term || '', vendor_id: $('#vendor_select').val() || '' }; },
                processResults: function(data) {
                    return { results: (data || []).map(function(p) { return {
                        id: p.id,
                        text: (p.item_code ? '[' + p.item_code + '] ' : '') + p.item_name,
                        item_code: p.item_code, brand_name: p.brand_name, price: p.price
                    }; }) };
                }, cache: true
            },
            placeholder: 'Search product…', width: '100%'
        }).on('select2:opening', function(e) {
            if (!$('#vendor_select').val()) {
                e.preventDefault();
                Swal.fire({ icon:'info', title:'Vendor Required', text:'Please select a vendor first to filter authorized products.', confirmButtonColor:'#2563eb' });
            }
        });
    }

    var rowIndex = 0;

    function updateEmptyState() {
        $('#emptyState').toggle($('.osm-row').length === 0);
        $('.osm-row').each(function(i) { $(this).find('.row-num').text(i + 1); });
    }

    function addNewRow() {
        var idx = rowIndex++;
        var rowHtml = '<tr class="osm-row" data-index="' + idx + '">' +
            '<td style="text-align:center;"><span class="row-num">' + ($('.osm-row').length + 1) + '</span></td>' +
            '<td>' +
                '<select name="items[' + idx + '][product_id]" class="product-select" style="width:100%;" required><option value="">Search product…</option></select>' +
                '<button type="button" class="btn-breakdown btn-add-breakdown">&#xf53f; Color Breakdown</button>' +
            '</td>' +
            '<td><input type="text" class="td-input td-readonly item_code" readonly placeholder="—"></td>' +
            '<td><input type="text" class="td-input td-readonly brand_name" readonly placeholder="—"></td>' +
            '<td><input type="number" step="0.01" name="items[' + idx + '][price]" class="td-input price" value="0.00" min="0" style="text-align:right;" required></td>' +
            '<td><input type="number" step="0.01" name="items[' + idx + '][total_qty]" class="td-input total-qty" value="1" min="0.01" style="text-align:center;" required></td>' +
            '<td><input type="text" class="td-input td-total line_total" readonly value="0.00" style="text-align:right;"></td>' +
            '<td style="text-align:center;"><button type="button" class="btn-remove-row remove-row"><i class="fas fa-trash-alt"></i></button></td>' +
        '</tr>' +
        '<tr class="breakdown-row d-none" id="breakdown_row_' + idx + '">' +
            '<td colspan="8">' +
                '<div class="breakdown-inner">' +
                    '<div class="breakdown-header">' +
                        '<div class="breakdown-header-title"><i class="fas fa-layer-group" style="margin-right:5px;"></i> Color / Variant Breakdown</div>' +
                        '<button type="button" class="btn-add-color btn-add-color-item" data-row-index="' + idx + '"><i class="fas fa-plus"></i> Add Color</button>' +
                    '</div>' +
                    '<div class="breakdown-list" id="breakdown_list_' + idx + '"></div>' +
                '</div>' +
            '</td>' +
        '</tr>';

        var $row = $(rowHtml);
        $('#poItems').append($row);
        initProductSelect2($row.find('.product-select'));
        updateEmptyState();
        calculateTotals();
    }

    addNewRow();
    $('#btnAddRow').on('click', addNewRow);

    $(document).on('click', '.remove-row', function() {
        var idx = $(this).closest('tr').data('index');
        $(this).closest('tr').remove();
        $('#breakdown_row_' + idx).remove();
        updateEmptyState(); calculateTotals();
    });

    $(document).on('click', '.btn-add-breakdown', function() {
        var idx = $(this).closest('tr').data('index');
        $('#breakdown_row_' + idx).toggleClass('d-none');
        if (!$('#breakdown_row_' + idx).hasClass('d-none') && $('#breakdown_list_' + idx).children().length === 0) {
            addColorItem(idx);
        }
        updateQtyLock(idx);
    });

    function addColorItem(rowIdx, qtyVal) {
        qtyVal = qtyVal || '';
        var colorHtml = '<div class="breakdown-item">' +
            '<div class="color-sel">' +
                '<select name="items[' + rowIdx + '][colors][]" class="form-select color-select-dynamic" style="font-size:12.5px;">' +
                    '<option value=""></option>' +
                    @foreach($existingColors as $color) '<option value="{{ $color }}">{{ $color }}</option>' + @endforeach
                    '' +
                '</select>' +
            '</div>' +
            '<div class="qty-w"><input type="number" step="0.01" name="items[' + rowIdx + '][color_qtys][]" class="td-input color_qty" placeholder="Qty" value="' + qtyVal + '" required></div>' +
            '<button type="button" class="btn-rm-color"><i class="fas fa-times-circle"></i></button>' +
        '</div>';
        var $list = $('#breakdown_list_' + rowIdx);
        $list.append(colorHtml);
        $list.find('.color-select-dynamic').last().select2({ tags:true, multiple:false, placeholder:'Select or type color…', allowClear:true, width:'100%' });
        updateQtyLock(rowIdx);
    }

    $(document).on('click', '.btn-add-color-item', function() { addColorItem($(this).data('row-index')); });
    $(document).on('click', '.btn-rm-color', function() {
        var rowIdx = $(this).closest('.breakdown-list').attr('id').split('_').pop();
        $(this).closest('.breakdown-item').remove(); updateQtyLock(rowIdx);
    });
    $(document).on('input', '.color_qty', function() {
        var rowIdx = $(this).closest('.breakdown-list').attr('id').split('_').pop();
        updateQtyLock(rowIdx);
    });

    function updateQtyLock(rowIdx) {
        var $qi  = $('tr[data-index="' + rowIdx + '"]').find('.total-qty');
        var $lst = $('#breakdown_list_' + rowIdx);
        var hasB = $lst.children().length > 0 && !$('#breakdown_row_' + rowIdx).hasClass('d-none');
        if (hasB) {
            var s = 0; $lst.find('.color_qty').each(function() { s += parseFloat($(this).val()) || 0; });
            $qi.val(s).addClass('qty-readonly-style').prop('readonly', true);
        } else { $qi.removeClass('qty-readonly-style').prop('readonly', false); }
        calculateTotals();
    }

    // Branch change
    $('#branch_select').on('change', function() {
        var bid = $(this).val(); if (!bid) return;
        $.get('{{ url("purchase-orders/branch") }}/' + bid + '/next-po', function(r) { $('#po_number').val(r.next_po); });
        $.get('{{ route("warehouses-by-branch") }}', { branch_id: bid }, function(res) {
            var s = $('#warehouse_select').empty().append('<option value="">— Select —</option>');
            res.forEach(function(w) { s.append('<option value="' + w.id + '">' + w.warehouse_name + '</option>'); });
        });
        $.get('{{ url("purchase-orders/branch") }}/' + bid + '/vendors', function(vendors) {
            var vs = $('#vendor_select');
            if (vs.hasClass('select2-hidden-accessible')) vs.select2('destroy');
            vs.empty().append('<option value="">— Search Vendor —</option>');
            vendors.forEach(function(v) {
                var co = v.company_names ? (Array.isArray(v.company_names) ? v.company_names.join(', ') : v.company_names) : '';
                vs.append($('<option>').val(v.id).text(v.name).attr('data-phone', v.phone||'').attr('data-address', v.address||'').attr('data-company', co));
            });
            vs.select2({ width:'100%', placeholder:'— Search Vendor —' });
            $('#vendor_strip').hide();
        });
    });

    // Vendor change → show inline strip
    $('#vendor_select').on('change', function() {
        var opt = $(this).find('option:selected');
        if (opt.val()) {
            $('#v_phone').text(opt.data('phone') || 'N/A');
            $('#v_company').text(opt.data('company') || 'N/A');
            $('#v_address').text(opt.data('address') || 'N/A');
            $('#vendor_strip').fadeIn(150);
        } else { $('#vendor_strip').hide(); }
    });

    // ── Quick Add Vendor Modal Handler (No Page Reload) ──
    $('#btnQuickAddVendor').on('click', function() {
        var currentBranchId = $('#branch_select').val() || '{{ $currentBranch }}';
        $('#modal_vendor_branch_id').val(currentBranchId);
        $('#quickAddVendorForm')[0].reset();
        $('#modal_vendor_opening').val('0.00');
        $('#quickAddVendorModal').modal('show');
    });

    $('#quickAddVendorForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $submitBtn = $('#btnSubmitQuickVendor');

        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: "{{ route('vendors.store.ajax') }}",
            type: "POST",
            data: $form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                $submitBtn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Save Vendor');
                
                if (response && response.success && response.vendor) {
                    var v = response.vendor;
                    
                    // Create new option in vendor select
                    var newOption = new Option(v.name, v.id, true, true);
                    $(newOption)
                        .attr('data-phone', v.phone || '')
                        .attr('data-address', v.address || '')
                        .attr('data-company', v.company_str || '');
                    
                    $('#vendor_select').append(newOption).trigger('change');

                    $('#quickAddVendorModal').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Vendor Created!',
                        text: v.name + ' has been added and selected.',
                        timer: 1600,
                        showConfirmButton: false
                    });
                } else {
                    $('#quickAddVendorModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Vendor Saved!',
                        text: 'Vendor saved successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                $submitBtn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Save Vendor');
                var errorMsg = 'Failed to create vendor. Please check inputs.';
                try {
                    var err = JSON.parse(xhr.responseText);
                    if (err.errors) {
                        errorMsg = Object.values(err.errors).flat().join('<br>');
                    } else if (err.message) {
                        errorMsg = err.message;
                    }
                } catch(e) {}
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMsg,
                    confirmButtonColor: '#2563eb'
                });
            }
        });
    });

    // Product selected
    $(document).on('select2:select', '.product-select', function(e) {
        var d = e.params.data, $tr = $(this).closest('tr');
        $tr.find('.item_code').val(d.item_code || '');
        $tr.find('.brand_name').val(d.brand_name || '');
        $tr.find('.price').val(parseFloat(d.price || 0).toFixed(2));
        calculateTotals();
    });

    $(document).on('input', '.total-qty, .price', calculateTotals);

    // ── Enter key on Qty / Price to add new row ──
    $(document).on('keydown', '.total-qty, .price', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addNewRow();
            // Focus on newly created row's product selector
            setTimeout(function() {
                $('.osm-row:last .product-select').select2('open');
            }, 100);
        }
    });

    // Automatically focus Total Qty when a product is selected
    $(document).on('select2:select', '.product-select', function(e) {
        var $tr = $(this).closest('tr');
        setTimeout(function() {
            $tr.find('.total-qty').focus().select();
        }, 100);
    });

    function calculateTotals() {
        var grand = 0;
        $('.osm-row').each(function() {
            var qty = parseFloat($(this).find('.total-qty').val()) || 0;
            var prc = parseFloat($(this).find('.price').val()) || 0;
            var tot = qty * prc;
            $(this).find('.line_total').val(tot.toFixed(2));
            grand += tot;
        });
        $('#grandTotal').text(grand.toLocaleString('en-PK', { minimumFractionDigits:2, maximumFractionDigits:2 }));
    }

    $('#poForm').on('submit', function(e) {
        if ($('.osm-row').length === 0) {
            e.preventDefault();
            Swal.fire({ icon:'warning', title:'Order Empty', text:'Please add at least one product line item.', confirmButtonColor:'#2563eb' });
            return false;
        }
        $(this).find('button[type="submit"]').prop('disabled', true);
    });
});
</script>
@endsection

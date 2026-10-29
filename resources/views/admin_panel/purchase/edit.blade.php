@extends('admin_panel.layout.app')

@section('content')
<link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* ================= ULTRA-COMPACT EXCEL-LIKE ERP UI (MATCHING SALE/CREATE) ================= */
    body {
        background-color: #f8fafc !important;
        font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
    }

    .main-container {
        border: 1px solid #94a3b8 !important;
        border-radius: 4px !important;
        box-shadow: none !important;
        background-color: #ffffff !important;
        padding: 6px !important;
        font-size: .78rem;
        max-width: 100%;
    }

    .card-panel {
        background-color: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 3px !important;
        padding: 6px !important;
        height: 100%;
    }

    .totals-card {
        background-color: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 3px !important;
        padding: 6px !important;
    }

    .section-title {
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.72rem !important;
        letter-spacing: 0.5px !important;
        color: #1e293b !important;
        margin-bottom: 4px !important;
        border-left: 3px solid #2563eb !important;
        padding-left: 6px !important;
    }

    .form-control,
    .form-select,
    .select2-container--default .select2-selection--single {
        border: 1px solid #cbd5e1 !important;
        border-radius: 3px !important;
        padding: 2px 6px !important;
        font-weight: 500 !important;
        color: #1e293b !important;
        background-color: #ffffff !important;
        transition: all 0.15s ease-in-out !important;
        height: 26px !important;
        font-size: 0.78rem !important;
    }

    .form-control:focus,
    .form-select:focus,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1) !important;
        outline: none !important;
    }

    .input-readonly {
        background-color: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        color: #475569 !important;
        font-weight: 600 !important;
        cursor: not-allowed !important;
    }

    /* Transaction Grid / Table */
    .table-responsive {
        border: 1px solid #cbd5e1 !important;
        border-radius: 2px !important;
        overflow-x: auto !important;
        overflow-y: visible !important;
        box-shadow: none !important;
        min-height: 100px;
        background-color: #ffffff;
    }

    .sales-table {
        border-collapse: collapse !important;
        margin-bottom: 0 !important;
        width: 100% !important;
        min-width: 935px !important;
        table-layout: fixed !important;
    }

    .col-index     { width: 28px !important; min-width: 28px !important; max-width: 28px !important; text-align: center !important; }
    .col-product   { width: 280px !important; min-width: 280px !important; max-width: 280px !important; text-align: left !important; }
    .col-warehouse { width: 130px !important; min-width: 130px !important; max-width: 130px !important; }
    .col-stock     { width: 55px !important; min-width: 55px !important; max-width: 55px !important; }
    .col-qty       { width: 75px !important; min-width: 75px !important; max-width: 75px !important; }
    .col-pieces    { width: 70px !important; min-width: 70px !important; max-width: 70px !important; }
    .col-price-p   { width: 85px !important; min-width: 85px !important; max-width: 85px !important; }
    .col-disc      { width: 85px !important; min-width: 85px !important; max-width: 85px !important; }
    .col-amount    { width: 95px !important; min-width: 95px !important; max-width: 95px !important; }
    .col-action    { width: 32px !important; min-width: 32px !important; max-width: 32px !important; text-align: center !important; }

    .sales-table thead th {
        background-color: #e2e8f0 !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 10px !important;
        letter-spacing: 0.3px;
        padding: 3px 4px !important;
        border: 1px solid #94a3b8 !important;
        border-bottom: 2px solid #64748b !important;
        vertical-align: middle !important;
        text-align: center;
        white-space: nowrap;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .sales-table thead th.col-product {
        text-align: left !important;
        padding-left: 6px !important;
    }

    .sales-table tbody td {
        border: 1px solid #cbd5e1 !important;
        padding: 0 !important;
        background-color: #ffffff;
        vertical-align: middle !important;
    }

    /* Flat borderless grid inputs */
    .sales-table tbody .form-control,
    .sales-table tbody .form-select {
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        height: 26px !important;
        margin: 0 !important;
        padding: 1px 4px !important;
        width: 100% !important;
        background-color: transparent !important;
        text-align: center;
        color: #1e293b !important;
        font-weight: 500 !important;
        font-size: 0.76rem !important;
    }

    /* Fixed Product cell & Select2 with ellipsis */
    .sales-table td.col-product {
        width: 280px !important;
        max-width: 280px !important;
        overflow: hidden !important;
        white-space: nowrap !important;
        text-overflow: ellipsis !important;
        text-align: left !important;
    }

    .sales-table td.col-product .select2-container {
        width: 100% !important;
        max-width: 100% !important;
        display: block !important;
    }

    .sales-table tbody .select2-container--default .select2-selection--single {
        height: 26px !important;
        padding: 0 !important;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background-color: transparent !important;
        width: 100% !important;
        max-width: 100% !important;
        display: flex !important;
        align-items: center !important;
    }

    .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
        padding-left: 6px !important;
        padding-right: 18px !important;
        font-size: 0.76rem !important;
        color: #1e293b !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        display: block !important;
        width: 100% !important;
        max-width: 255px !important;
        text-align: left !important;
    }

    .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 26px !important;
        right: 4px !important;
    }

    .select2-container--open .select2-dropdown {
        min-width: 280px !important;
        border: 1px solid #94a3b8 !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }

    /* Payment Methods row */
    .rv-row {
        display: flex !important;
        align-items: center !important;
        gap: 4px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .rv-row .rv-account {
        flex: 1 1 0 !important;
        min-width: 0 !important;
        width: 0 !important;
        font-size: 0.75rem !important;
        height: 28px !important;
        padding: 2px 4px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 4px !important;
        color: #1e293b !important;
    }

    .rv-row .rv-amount {
        flex: 0 0 95px !important;
        width: 95px !important;
        min-width: 95px !important;
        max-width: 95px !important;
        font-size: 0.75rem !important;
        height: 28px !important;
        padding: 2px 6px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 4px !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
    }

    .rv-row .btn-remove-rv {
        flex: 0 0 22px !important;
        width: 22px !important;
        min-width: 22px !important;
        height: 28px !important;
        padding: 0 !important;
        line-height: 24px !important;
        font-size: 0.95rem !important;
        border-radius: 4px !important;
    }

    .top-field-purchaser .select2-container {
        width: 100% !important;
        height: 28px !important;
    }
    .top-field-purchaser .select2-container .select2-selection--single {
        height: 28px !important;
        min-height: 28px !important;
        max-height: 28px !important;
        line-height: 26px !important;
        font-size: 0.78rem !important;
        padding: 0 4px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 4px !important;
        display: flex !important;
        align-items: center !important;
    }
    .top-field-purchaser .select2-selection__rendered {
        line-height: 26px !important;
        height: 26px !important;
        font-size: 0.78rem !important;
        padding-left: 4px !important;
        color: #1e293b !important;
        font-weight: 600 !important;
    }
    .top-field-purchaser .select2-selection__arrow {
        height: 26px !important;
        top: 0 !important;
        right: 4px !important;
    }

    /* Discount wrapper with toggle button */
    .sales-table tbody .discount-wrapper {
        display: flex !important;
        align-items: stretch !important;
        width: 100% !important;
        height: 26px !important;
        gap: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .sales-table tbody .discount-wrapper .discount-value {
        flex-grow: 1 !important;
        border: none !important;
        border-radius: 0 !important;
        height: 100% !important;
        text-align: center;
        background-color: transparent !important;
        padding: 1px 3px !important;
    }

    .sales-table tbody .discount-wrapper .discount-toggle {
        border: none !important;
        border-radius: 0 !important;
        background-color: #e2e8f0 !important;
        color: #475569 !important;
        font-weight: 700 !important;
        font-size: 0.7rem !important;
        width: 24px !important;
        min-width: 24px !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 !important;
        cursor: pointer !important;
    }

    .sales-table tfoot td {
        background-color: #e2e8f0 !important;
        border: 1px solid #94a3b8 !important;
        border-top: 2px solid #64748b !important;
        padding: 2px 4px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        font-size: 0.78rem !important;
    }

    .sales-table tbody tr:hover td {
        background-color: #f8fafc !important;
    }

    /* Column Widths */
    .col-product { width: auto !important; }
    .col-warehouse { width: 140px !important; min-width: 140px !important; }
    .col-stock { width: 55px !important; min-width: 55px !important; }
    .col-qty { width: 85px !important; min-width: 85px !important; }
    .col-pieces { width: 75px !important; min-width: 75px !important; }
    .col-price-p { width: 85px !important; min-width: 85px !important; }
    .col-disc { width: 85px !important; min-width: 85px !important; }
    .col-amount { width: 95px !important; min-width: 95px !important; }
    .col-action { width: 28px !important; min-width: 28px !important; text-align: center; }

    .pos-main-row {
        --bs-gutter-x: 2px !important;
        --bs-gutter-y: 2px !important;
        margin-left: -1px !important;
        margin-right: -1px !important;
    }
    .pos-main-row > [class*="col-"],
    .pos-main-row > .pos-left-main,
    .pos-main-row > .pos-right-summary {
        padding-left: 1px !important;
        padding-right: 1px !important;
    }
    @media (min-width: 992px) {
        .pos-left-main {
            flex: 1 1 0% !important;
            max-width: none !important;
            width: auto !important;
            min-width: 0 !important;
        }
        .pos-right-summary {
            flex: 0 0 330px !important;
            max-width: 330px !important;
            width: 330px !important;
        }
    }
    @media (max-width: 991.98px) {
        .pos-left-main,
        .pos-right-summary {
            flex: 0 0 100% !important;
            max-width: 100% !important;
            width: 100% !important;
        }
    }

    /* Top info fields - tight zero-gap flow */
    .top-info-fields-row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 4px;
        margin-bottom: 2px;
        width: 100%;
    }
    .top-info-fields-row .form-control,
    .top-info-fields-row .form-select {
        width: 100% !important;
    }
    .top-field-branch { flex: 0 0 155px; max-width: 155px; }
    .top-field-loc { flex: 0 0 185px; max-width: 185px; }
    .top-field-date { flex: 0 0 130px; max-width: 130px; }
    .top-field-purchaser { flex: 0 0 145px; max-width: 145px; }
    .top-field-crdays { flex: 0 0 65px; max-width: 65px; }
    .top-field-mbill { flex: 0 0 120px; max-width: 120px; }
    .top-field-remarks { flex: 1 1 160px; min-width: 120px; }
    @media (max-width: 767.98px) {
        .top-field-branch,
        .top-field-loc,
        .top-field-date,
        .top-field-purchaser,
        .top-field-crdays,
        .top-field-mbill,
        .top-field-remarks {
            flex: 1 1 calc(50% - 4px);
            max-width: 100%;
        }
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 2.5px 0 !important;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 0.77rem;
    }
    .summary-val-net {
        font-weight: 800;
        color: #2563eb;
        font-size: 0.92rem;
    }
    .summary-val-change {
        background: #ffe4e6;
        color: #e11d48;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.85rem;
    }

    .bottom-summary-strip {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 5px 12px;
        margin-top: 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }
    .btn-save-complete {
        background: #10b981 !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        border-radius: 6px !important;
        padding: 7px 20px !important;
        font-size: 0.88rem !important;
        border: none !important;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25) !important;
        transition: all 0.2s ease !important;
        cursor: pointer;
    }
    .btn-save-complete:hover {
        background: #059669 !important;
        transform: translateY(-1px);
    }

    /* Unified Vendor Input Group */
    #vendorInputWrapper {
        display: flex !important;
        align-items: stretch !important;
        height: 31px !important;
        min-height: 31px !important;
        max-height: 31px !important;
        width: 100% !important;
    }

    #vendorInputWrapper #vendorSelectBoxContainer {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        height: 31px !important;
    }

    #vendorInputWrapper #vendorSelectBoxContainer .select2-container {
        width: 100% !important;
        height: 31px !important;
        display: block !important;
    }

    #vendorInputWrapper #vendorSelectBoxContainer .select2-container .select2-selection--single {
        height: 31px !important;
        min-height: 31px !important;
        max-height: 31px !important;
        line-height: 29px !important;
        border-top-left-radius: 4px !important;
        border-bottom-left-radius: 4px !important;
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        box-sizing: border-box !important;
    }

    #vendorInputWrapper #vendorSelectBoxContainer .select2-selection__rendered {
        line-height: 29px !important;
        height: 29px !important;
        font-size: 0.78rem !important;
        padding-left: 8px !important;
        padding-right: 20px !important;
        color: #1e293b !important;
        font-weight: 600 !important;
        margin: 0 !important;
        display: block !important;
    }

    #vendorInputWrapper #vendorSelectBoxContainer .select2-selection__arrow {
        height: 29px !important;
        top: 0 !important;
        right: 4px !important;
    }

    #vendorInputWrapper #walkinNameInput {
        height: 31px !important;
        min-height: 31px !important;
        max-height: 31px !important;
        border-top-left-radius: 4px !important;
        border-bottom-left-radius: 4px !important;
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        padding: 2px 8px !important;
        box-sizing: border-box !important;
    }

    #vendorInputWrapper #btnWalkinToggle {
        height: 31px !important;
        min-height: 31px !important;
        max-height: 31px !important;
        border-radius: 0 !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 0.74rem !important;
        font-weight: 700 !important;
        line-height: 1 !important;
        white-space: nowrap !important;
        margin-left: -1px !important;
        padding: 0 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        z-index: 2 !important;
        box-sizing: border-box !important;
    }

    #vendorInputWrapper #btnWalkinToggle.btn-primary.active {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }

    #vendorInputWrapper #btnAddNewVendorModal {
        height: 31px !important;
        min-height: 31px !important;
        max-height: 31px !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        border-top-right-radius: 4px !important;
        border-bottom-right-radius: 4px !important;
        border: 1px solid #10b981 !important;
        font-size: 0.74rem !important;
        font-weight: 700 !important;
        line-height: 1 !important;
        white-space: nowrap !important;
        margin-left: -1px !important;
        padding: 0 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        z-index: 2 !important;
        box-sizing: border-box !important;
    }

    .payment-badge {
        padding: 4px 10px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 700;
        font-size: 11px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .payment-badge.active {
        background: #ecfdf5;
        color: #065f46;
        border-color: #059669;
    }
</style>

<div class="container-fluid py-0 px-1">
    <div class="main-container bg-white border mx-auto p-2 rounded-3">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-1 px-2 mb-1" role="alert" style="font-size:0.78rem;">
                <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close py-1" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show py-1 px-2 mb-1" role="alert" style="font-size:0.78rem;">
                <i class="fas fa-exclamation-triangle me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close py-1" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('purchase.update', $purchase->id) }}" method="POST" id="purchaseForm" autocomplete="off">
            @csrf
            @method('PUT')
            <input type="hidden" name="purchase_type" value="{{ $purchase->purchase_type ?? 'local' }}">
            <input type="hidden" name="_vendor_mode" id="vendorModeInput" value="{{ ($purchase->vendor_id || $purchase->customer_id) ? 'vendor' : 'local' }}">
            <input type="hidden" name="party_type" id="partyTypeInput" value="{{ $purchase->party_type ?? ($purchase->customer_id ? 'customer' : 'vendor') }}">
            <input type="hidden" name="vendor_id" id="vendorIdInput" value="{{ $purchase->vendor_id }}">
            <input type="hidden" name="customer_id" id="customerIdInput" value="{{ $purchase->customer_id }}">
            <input type="hidden" name="vendor_name" id="vendorNameInput" value="{{ $purchase->vendor_name ?? ($purchase->vendor->name ?? ($purchase->customer->customer_name ?? '')) }}">
            <input type="hidden" name="invoice_no" id="inputInvoiceNo" value="{{ $purchase->invoice_no }}">

            {{-- Hidden calculation trackers --}}
            <input type="hidden" name="subtotal" id="subtotal_val" value="{{ $purchase->subtotal }}">
            <input type="hidden" name="net_amount" id="netAmount" value="{{ $purchase->net_amount }}">
            <input type="hidden" name="extra_cost" id="extraCost" value="{{ $purchase->extra_cost }}">

            {{-- TOP HEADER BAR WITH INVOICE NO BADGE --}}
            <div class="d-flex justify-content-between align-items-center mb-1 px-1">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('Purchase.home') }}" class="btn btn-sm btn-light border rounded-circle" title="Back"><i class="fas fa-arrow-left text-secondary"></i></a>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                            <i class="fas fa-edit text-warning"></i> Edit Purchase Invoice
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1 rounded-pill" style="font-size: 0.78rem; background: #e6fcf5;">
                                <i class="fas fa-file-invoice me-1"></i><span id="headerInvoiceNoDisplay">{{ $purchase->invoice_no }}</span>
                            </span>
                        </h5>
                        <small class="text-muted" style="font-size: 0.7rem;">Modify purchase invoice and procurement records</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('purchase.invoice', $purchase->id) }}" class="btn btn-sm btn-light border rounded-3 text-secondary fw-bold" style="font-size:0.75rem;">
                        <i class="fas fa-file-invoice text-primary me-1"></i>View Invoice
                    </a>
                    <a href="{{ route('Purchase.home') }}" class="btn btn-sm btn-light border rounded-3 text-secondary fw-bold" style="font-size:0.75rem;">
                        <i class="fas fa-list text-primary me-1"></i>All Purchases
                    </a>
                    <button type="button" class="btn btn-sm btn-light border rounded-3 text-secondary" title="Fullscreen" onclick="document.documentElement.requestFullscreen()"><i class="fas fa-expand"></i></button>
                </div>
            </div>

            <!-- TOP INFORMATION PANEL -->
            <div class="card-panel mb-1 bg-white border rounded-3 shadow-xs" style="padding: 4px 6px !important;">
                <div class="top-info-fields-row">
                    <!-- Branch Selection -->
                    <div class="top-field-branch">
                        <label class="form-label fw-bold text-uppercase text-primary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                            <i class="fas fa-building me-1"></i>Branch
                        </label>
                        @if ($isSuperAdmin)
                            <select class="form-select fw-bold border-primary shadow-xs" name="branch_id" id="branch_id" style="font-size:0.78rem; height: 28px;">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $b->id == $purchase->branch_id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="form-control form-control-sm bg-light fw-bold text-primary text-truncate border-secondary-subtle" style="font-size:0.78rem; height: 28px; line-height: 20px;">
                                <i class="fas fa-store me-1"></i>{{ $purchase->branch->name ?? 'Default Branch' }}
                            </div>
                            <input type="hidden" name="branch_id" id="branch_id" value="{{ $purchase->branch_id }}">
                        @endif
                    </div>

                    <!-- Location Selection (Shop / Godown) -->
                    <div class="top-field-loc">
                        <label class="form-label fw-bold text-uppercase text-secondary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                            <i class="fas fa-map-marker-alt text-info me-1"></i>Location (Default)
                        </label>
                        <select class="form-select fw-bold shadow-xs" name="warehouse_id" id="globalWarehouseSelect" style="font-size:0.78rem; height: 28px;">
                            <optgroup label="🏬 Shops / Branches (Direct Stock)">
                                <option value="" {{ empty($purchase->warehouse_id) ? 'selected' : '' }}>🏢 Direct Shop Stock</option>
                            </optgroup>
                            @if(isset($Warehouse) && count($Warehouse) > 0)
                                <optgroup label="🏭 Godowns / Warehouses">
                                    @foreach($Warehouse as $w)
                                        <option value="{{ $w->id }}" {{ $purchase->warehouse_id == $w->id ? 'selected' : '' }}>🏭 {{ $w->warehouse_name ?? $w->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>

                    <!-- Date with Calendar -->
                    <div class="top-field-date">
                        <label class="form-label fw-bold text-uppercase text-secondary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                            <i class="far fa-calendar-alt text-success me-1"></i>Date
                        </label>
                        <input type="date" name="purchase_date" class="form-control text-center fw-bold shadow-xs" id="displayDateInput" value="{{ $purchase->purchase_date ? substr($purchase->purchase_date, 0, 10) : date('Y-m-d') }}" style="font-size:0.78rem; height: 28px;" required>
                    </div>

                    <!-- Purchaser / Staff -->
                    <div class="top-field-purchaser">
                        <label class="form-label fw-bold text-uppercase text-secondary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                            <i class="fas fa-user-tie text-warning me-1"></i>Purchaser
                        </label>
                        <select class="form-select fw-bold shadow-xs select2" name="purchaser" id="purchaserSelect" style="font-size:0.78rem; height: 28px;">
                            <option value="">Select Purchaser</option>
                            @if(isset($users) && count($users) > 0)
                                @foreach($users as $u)
                                    <option value="{{ $u->name }}" {{ (Auth::check() && Auth::user()->name == $u->name) ? 'selected' : '' }}>
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="{{ Auth::check() ? Auth::user()->name : 'admin' }}" selected>{{ Auth::check() ? Auth::user()->name : 'admin' }}</option>
                            @endif
                        </select>
                    </div>

                    <!-- Cr. Days -->
                    <div class="top-field-crdays">
                        <label class="form-label fw-bold text-uppercase text-secondary mb-0 text-truncate d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                            <i class="fas fa-clock text-danger me-1"></i>Cr. Days
                        </label>
                        <input type="number" class="form-control text-center fw-bold shadow-xs" name="credit_days" placeholder="0" min="0" value="0" style="font-size:0.78rem; height: 28px;">
                    </div>

                    <!-- M. Bill # (Supplier Bill / DC) -->
                    <div class="top-field-mbill">
                        <label class="form-label fw-bold text-uppercase text-secondary mb-0 d-flex align-items-center text-truncate" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                            <i class="fas fa-file-invoice text-secondary me-1"></i>M. Bill#
                        </label>
                        <input type="text" class="form-control shadow-xs" name="manual_invoice" id="manualInvoice" value="{{ $purchase->transport_name ?? '' }}" placeholder="Manual bill#..." style="font-size:0.78rem; height: 28px;">
                    </div>

                    <!-- Remarks -->
                    <div class="top-field-remarks">
                        <label class="form-label fw-bold text-uppercase text-secondary mb-0 d-flex align-items-center text-truncate" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                            <i class="fas fa-sticky-note text-secondary me-1"></i>Remarks
                        </label>
                        <input type="text" class="form-control shadow-xs" name="note" id="note" value="{{ $purchase->note }}" placeholder="Enter remarks..." style="font-size:0.78rem; height: 28px;">
                    </div>
                </div>

                <!-- Vendor / Customer Selection Group -->
                <div style="margin-top: 3px;">
                    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 2px !important;">
                        <label class="form-label fw-bold text-uppercase text-primary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px;">
                            <i class="fas fa-handshake text-primary me-1"></i>Party / Supplier Selection
                            @if(($partySource ?? 'both') === 'both')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.6rem;">Vendors + Customers</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border ms-1" style="font-size: 0.6rem;">Vendors Only</span>
                            @endif
                        </label>
                        <div id="vendorPrevBalWrapper" class="d-flex align-items-center gap-1" style="font-size:0.75rem;">
                            <span class="text-muted fw-semibold" id="lblPrevBal">Prev. Balance:</span>
                            <span id="badgeVendorPrevBal" class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold font-monospace px-2 py-0" style="font-size:0.78rem;">Rs 0.00</span>
                        </div>
                    </div>
                    <div id="vendorInputWrapper" class="input-group input-group-sm shadow-xs rounded">
                        <div id="vendorSelectBoxContainer" class="flex-grow-1 {{ empty($purchase->vendor_id) && empty($purchase->customer_id) && !empty($purchase->vendor_name) ? 'd-none' : '' }}" style="min-width: 0;">
                            <select class="form-select select2" id="vendorSelect" name="vendor_id_select" style="width:100%">
                                <option value="">Select or search party (Vendor / Customer)...</option>
                                @if(isset($Vendor) && count($Vendor) > 0)
                                    <optgroup label="🏢 Registered Vendors (وینڈرز)">
                                        @foreach($Vendor as $v)
                                            <option value="vendor_{{ $v->id }}"
                                                data-type="vendor"
                                                data-id="{{ $v->id }}"
                                                data-name="{{ $v->name }}"
                                                data-balance="{{ $v->closing_balance ?? 0 }}"
                                                data-phone="{{ $v->phone_number ?? '' }}"
                                                {{ ($purchase->party_type === 'vendor' || !$purchase->party_type) && $v->id == $purchase->vendor_id ? 'selected' : '' }}>
                                                🏢 {{ $v->name }} (Payable: Rs. {{ number_format($v->closing_balance ?? 0, 2) }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if(($partySource ?? 'both') !== 'vendors_only' && isset($Customer) && count($Customer) > 0)
                                    <optgroup label="👤 Customers / Contra Parties (کسٹمرز)">
                                        @foreach($Customer as $c)
                                            <option value="customer_{{ $c->id }}"
                                                data-type="customer"
                                                data-id="{{ $c->id }}"
                                                data-name="{{ $c->customer_name }}"
                                                data-balance="{{ $c->closing_balance ?? 0 }}"
                                                data-phone="{{ $c->mobile ?? '' }}"
                                                {{ ($purchase->party_type === 'customer' || $purchase->customer_id) && $c->id == $purchase->customer_id ? 'selected' : '' }}>
                                                👤 {{ $c->customer_name }} (Receivable: Rs. {{ number_format($c->closing_balance ?? 0, 2) }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                        <input type="text" class="form-control fw-bold {{ empty($purchase->vendor_id) && empty($purchase->customer_id) && !empty($purchase->vendor_name) ? '' : 'd-none' }}" name="local_vendor_name" id="walkinNameInput" value="{{ $purchase->vendor_name }}" placeholder="Enter Local Supplier / Market Shop Name...">
                        <button type="button" class="btn {{ empty($purchase->vendor_id) && empty($purchase->customer_id) && !empty($purchase->vendor_name) ? 'btn-primary active' : 'btn-outline-primary' }} fw-bold px-3 d-flex align-items-center gap-1" id="btnWalkinToggle">
                            <i class="fas fa-store"></i> Local Market
                        </button>
                        <button type="button" class="btn btn-outline-success fw-bold px-3 d-flex align-items-center gap-1" id="btnAddNewVendorModal" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                            <i class="fas fa-user-plus"></i> New Vendor
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2-COLUMN RESPONSIVE POS LAYOUT -->
            <div class="row pos-main-row align-items-stretch">
                <!-- LEFT MAIN AREA: Items Grid Table -->
                <div class="col-12 col-lg-8 col-xl-9 pos-left-main">
                    <div class="card-panel d-flex flex-column h-100 bg-white" style="border-radius:4px; padding: 6px 8px !important;">
                        <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                            <div class="d-flex align-items-center gap-2">
                                <div class="section-title mb-0" style="font-size:0.8rem;">Purchase Items (<span id="itemsRowCount">{{ count($purchase->items) }}</span>)</div>
                            </div>

                            <div class="d-flex gap-1 ms-auto">
                                <button type="button" class="btn btn-outline-success btn-sm py-1 px-3 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#quickAddProductModal" style="font-size:0.75rem;">
                                    <i class="fas fa-bolt text-warning me-1"></i>Create Product
                                </button>
                                <button type="button" class="btn btn-primary btn-sm py-1 px-3 rounded-pill fw-bold" id="btnAdd" style="font-size:0.75rem;">
                                    <i class="fas fa-plus me-1"></i>Add Row
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive flex-grow-1" style="overflow-x: auto; overflow-y: visible;">
                            <table class="table table-bordered sales-table mb-0" id="itemsTable" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th class="col-index text-center" style="width:28px;">#</th>
                                        <th class="col-product" style="width: 280px;">PRODUCT / MODEL</th>
                                        <th class="col-warehouse" style="width: 130px;">WAREHOUSE</th>
                                        <th class="col-stock" style="width: 55px;">STOCK</th>
                                        <th class="col-qty" style="width: 75px;">QTY</th>
                                        <th class="col-pieces" style="width: 70px;">PACKING / UNIT</th>
                                        <th class="col-price-p" style="width: 85px;">COST PRICE</th>
                                        <th class="col-disc" style="width: 85px;">DISCOUNT</th>
                                        <th class="col-amount" style="width: 95px;">AMOUNT</th>
                                        <th class="col-action" style="width: 32px;">×</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsList">
                                    @foreach($purchase->items as $index => $item)
                                    @php
                                        $unitName = $item->unit ?? ($item->product->unit->name ?? 'Piece');
                                    @endphp
                                    <tr class="item-row">
                                        <td class="text-center fw-bold text-muted row-index" style="vertical-align:middle; font-size:0.75rem;">{{ $index + 1 }}</td>
                                        <td class="col-product">
                                            <select name="product_id[]" class="form-select product-select select2" required style="width: 100%;">
                                                <option value="">Select Product...</option>
                                                @foreach($Products as $p)
                                                    <option value="{{ $p->id }}" 
                                                        data-price="{{ $p->last_purchase_price }}" 
                                                        data-unit="{{ $p->unit->name ?? 'unit' }}" 
                                                        data-code="{{ $p->item_code }}"
                                                        data-piece-per-pack="{{ (float)($p->piece_per_pack ?? 1) }}"
                                                        data-pack-type="{{ $p->pack_type ?? 'Standard' }}"
                                                        {{ $p->id == $item->product_id ? 'selected' : '' }}>
                                                        {{ $p->item_name }} {{ $p->item_code ? '('.$p->item_code.')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="unit[]" class="unit-input" value="{{ $unitName }}">
                                            <input type="hidden" name="color[]" class="color-input" value="{{ $item->color ?? '' }}">
                                            <input type="hidden" name="packing_type[]" class="packing-type-input" value="{{ $item->packing_type ?? 'Standard' }}">
                                            <input type="hidden" name="packing_qty[]" class="pack-qty-input" value="{{ $item->packing_qty ?? 0 }}">
                                            <input type="hidden" name="item_per_piece[]" class="ipp-input" value="{{ $item->item_per_piece ?? 0 }}">
                                            <input type="hidden" name="loose_piece[]" class="loose-pcs-input" value="{{ $item->loose_piece ?? 0 }}">
                                            <input type="hidden" name="total_pieces[]" class="total-pieces-input" value="{{ $item->qty }}">
                                        </td>

                                        <td class="col-warehouse">
                                            <select name="line_warehouse_id[]" class="form-select line-warehouse-select" style="width: 100%; height: 26px; font-size: 0.78rem; padding: 2px 4px; font-weight: 600;">
                                                <option value="" {{ empty($item->warehouse_id) ? 'selected' : '' }}>🏢 Shop Stock</option>
                                                @if(isset($Warehouse) && count($Warehouse) > 0)
                                                    @foreach($Warehouse as $wh)
                                                        <option value="{{ $wh->id }}" {{ $item->warehouse_id == $wh->id ? 'selected' : '' }}>🏭 {{ $wh->warehouse_name ?? $wh->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>

                                        <td class="col-stock">
                                            <input type="text" class="form-control stock text-center input-readonly" readonly tabindex="-1" value="0">
                                        </td>

                                        <td class="col-qty">
                                            <input type="number" step="any" name="qty[]" class="form-control qty-input text-center fw-bold" value="{{ ($item->packing_qty > 0) ? (float)$item->packing_qty : (float)$item->qty }}" min="0.001" required style="font-size: 0.85rem;">
                                        </td>

                                        <td class="col-pieces text-center">
                                            <input type="text" class="form-control unit-display text-center input-readonly" readonly tabindex="-1" value="{{ $unitName }}" style="font-size: 0.75rem;">
                                            <div class="packing-preview-badge small text-success fw-bold" style="{{ ($item->packing_qty > 0 && $item->item_per_piece > 1) ? '' : 'display:none;' }} font-size:10.5px; line-height:1.2; margin-top:3px;">
                                                @if($item->packing_qty > 0 && $item->item_per_piece > 1)
                                                    <i class="fas fa-boxes"></i> {{ (float)$item->packing_qty }} Crtn = {{ (float)$item->qty }} Pcs
                                                @endif
                                            </div>
                                        </td>

                                        <td class="col-price-p">
                                            <input type="number" step="0.01" min="0" name="price[]" class="form-control price-input text-end fw-bold" value="{{ $item->price }}" required placeholder="0.00" style="font-size: 0.85rem;">
                                        </td>

                                        <td class="col-disc">
                                            <div class="discount-wrapper">
                                                <input type="number" step="0.01" min="0" class="discount-value disc-input-visual" value="{{ $item->item_discount }}" placeholder="0">
                                                <button type="button" class="discount-toggle disc-type-toggle" data-type="amount">Rs</button>
                                                <input type="hidden" class="disc-type-input" value="amount">
                                                <input type="hidden" name="item_discount[]" class="disc-input-hidden" value="{{ $item->item_discount }}">
                                            </div>
                                        </td>

                                        <td class="col-amount text-end">
                                            <input type="text" class="form-control line-total text-end fw-bold text-success input-readonly" readonly value="{{ number_format($item->line_total, 2, '.', '') }}" tabindex="-1" style="font-size: 0.85rem;">
                                        </td>

                                        <td class="col-action text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger p-0 border-0 remove-row" title="Delete" style="width: 22px; height: 22px; line-height: 20px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-end fw-bold pe-2" style="font-size: 0.8rem;">GRID TOTAL:</td>
                                        <td class="text-end fw-bold text-primary font-monospace px-2" id="gridTotalDisplay" style="font-size: 0.88rem;">{{ number_format($purchase->subtotal, 2, '.', '') }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT PANEL: Summary & Payment Methods -->
                <div class="col-12 col-lg-4 col-xl-3 pos-right-summary">
                    <div class="d-flex flex-column h-100" style="gap: 5px !important;">
                        <!-- Executive Summary Card -->
                        <div class="card-panel bg-white" style="border-radius:4px; padding: 6px 8px !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                                <span class="fw-bold text-dark" style="font-size:0.82rem;"><i class="fas fa-calculator text-primary me-1"></i>Summary</span>
                                <span class="badge bg-primary rounded-pill px-2 py-0" style="font-size:0.68rem; line-height: 18px;">Live</span>
                            </div>
                            
                            <div class="summary-row">
                                <span class="text-muted">Total Amount</span>
                                <span class="fw-bold text-dark" id="tGross">{{ number_format($purchase->subtotal, 2, '.', '') }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-muted">Line Discount</span>
                                <span class="fw-bold text-danger" id="tLineDisc">0.00</span>
                            </div>
                            <div class="summary-row">
                                <span class="fw-bold text-dark">Current Net</span>
                                <span class="summary-val-net" id="tSub">{{ number_format($purchase->net_amount, 2, '.', '') }}</span>
                            </div>
                            <div class="summary-row" id="rowPrevBalance">
                                <span class="text-muted">Prev. Balance</span>
                                <span class="fw-bold text-danger" id="displayPrevBalance">0.00</span>
                            </div>
                            <div class="summary-row" id="rowTotalPayable">
                                <span class="fw-bold text-primary">Total Payable</span>
                                <span class="fw-bold text-primary" id="displayTotalPayable">0.00</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-muted">Total Paid</span>
                                <span class="fw-bold text-success" id="receiptsTotalBadge">{{ number_format($purchase->paid_amount, 2, '.', '') }}</span>
                            </div>
                            <div class="summary-row pt-1">
                                <span class="fw-bold text-danger" id="lblChangeOrDue">Balance Due</span>
                                <span class="summary-val-change" id="walkinChange">{{ number_format($purchase->due_amount, 2, '.', '') }}</span>
                            </div>
                        </div>

                        <!-- Payment Methods Card -->
                        <div class="card-panel bg-white flex-grow-1 d-flex flex-column" style="border-radius:4px; padding: 6px 8px !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                <span class="fw-bold text-dark" style="font-size:0.8rem;"><i class="fas fa-wallet text-success me-1"></i>Payment Methods</span>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" id="btnAddRV" style="font-size:0.7rem;"><i class="fas fa-plus me-1"></i>Add Account</button>
                            </div>

                            <div class="d-flex gap-2 mb-2">
                                <label class="payment-badge {{ $purchase->paid_amount > 0 ? '' : 'active' }}" id="badgeLater">
                                    <input type="radio" name="payment_type" value="pay_later" class="d-none" {{ $purchase->paid_amount > 0 ? '' : 'checked' }}> 💳 Pay Later
                                </label>
                                <label class="payment-badge {{ $purchase->paid_amount > 0 ? 'active' : '' }}" id="badgeNow">
                                    <input type="radio" name="payment_type" value="pay_now" class="d-none" {{ $purchase->paid_amount > 0 ? 'checked' : '' }}> 💵 Pay Now
                                </label>
                            </div>

                            <div id="paymentFields" style="{{ $purchase->paid_amount > 0 ? '' : 'display: none;' }}" class="mb-2">
                                <div id="rvWrapper">
                                    @if(!empty($prefilledPayments))
                                        @foreach($prefilledPayments as $pIndex => $payment)
                                            <div class="rv-row mb-1">
                                                <select class="form-select form-select-sm rv-account bg-light fw-bold" name="payment_account_id[]" title="Select Account">
                                                    <option value="">Select Account...</option>
                                                    @foreach($bankAccounts as $acc)
                                                        <option value="{{ $acc->id }}" {{ $acc->id == $payment['account_id'] ? 'selected' : '' }}>
                                                            {{ $acc->title }} (Bal: Rs. {{ number_format($acc->closing_balance ?? $acc->opening_balance ?? 0, 2) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="number" step="0.01" class="form-control form-control-sm text-end rv-amount fw-bold" name="payment_amount[]" placeholder="0.00" value="{{ $payment['amount'] }}" title="Payment Amount">
                                                @if($pIndex > 0)
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-rv" title="Remove">&times;</button>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="rv-row mb-1">
                                            <select class="form-select form-select-sm rv-account bg-light fw-bold" name="payment_account_id[]" title="Select Account">
                                                <option value="">Select Account...</option>
                                                @foreach($bankAccounts as $acc)
                                                    <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title ?? ''), 'cash in hand') || $loop->first ? 'selected' : '' }}>
                                                        {{ $acc->title }} (Bal: Rs. {{ number_format($acc->closing_balance ?? $acc->opening_balance ?? 0, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="number" step="0.01" class="form-control form-control-sm text-end rv-amount fw-bold" name="payment_amount[]" placeholder="0.00" value="0.00" title="Payment Amount">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <button type="submit" class="btn btn-save-complete w-100 mt-auto py-2" id="btnSavePurchase">
                                <i class="fas fa-save me-2"></i>UPDATE PURCHASE INVOICE (F9)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTTOM SUMMARY STRIP -->
            <div class="bottom-summary-strip">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted fw-semibold" style="font-size:0.8rem;">Net Total</span>
                    <span class="fs-5 fw-bold text-primary font-monospace" id="walkinNetTotal">{{ number_format($purchase->net_amount, 2, '.', '') }}</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted fw-semibold" style="font-size:0.8rem;">Discount (Rs.)</span>
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <input type="number" step="0.01" min="0" class="form-control text-end fw-bold text-danger" id="overallDiscount" name="discount" value="{{ $purchase->discount }}" placeholder="0">
                        <span class="input-group-text bg-light text-muted">Rs</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted fw-semibold" style="font-size:0.8rem;">Payments</span>
                    <span class="fs-6 fw-bold text-success font-monospace" id="bottomPaymentsTotal">{{ number_format($purchase->paid_amount, 2, '.', '') }}</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted fw-semibold" style="font-size:0.8rem;">Balance Due</span>
                    <span class="fs-6 fw-bold text-danger font-monospace" id="bottomChangeVal">{{ number_format($purchase->due_amount, 2, '.', '') }}</span>
                </div>

                <button type="submit" class="btn btn-save-complete" id="btnSavePurchaseBottom">
                    <i class="fas fa-save me-2"></i>UPDATE PURCHASE INVOICE (F9)
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Product Modal -->
<div class="modal fade" id="quickAddProductModal" tabindex="-1" aria-labelledby="quickAddProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 pb-2">
                <h5 class="modal-title fw-bold" id="quickAddProductModalLabel">
                    <i class="fa fa-plus-circle text-primary me-2"></i>Quick Add Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddProductForm">
                @csrf
                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="item_name" required placeholder="Enter product name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="qap_category" required>
                                <option value="">Select Category</option>
                                @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Brand <span class="text-danger">*</span></label>
                            <select class="form-select" name="brand_id" id="qap_brand" required>
                                <option value="">Select Brand</option>
                                @foreach($brands ?? [] as $br)
                                    <option value="{{ $br->id }}">{{ $br->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Item Code / SKU</label>
                            <input type="text" class="form-control" name="item_code" placeholder="Optional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Unit</label>
                            <input type="text" class="form-control" name="unit_name" value="Piece" placeholder="Piece, Box, etc.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Cost / Purchase Price</label>
                            <input type="number" step="0.01" class="form-control" name="wholesale_price" value="0.00" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Retail Price</label>
                            <input type="number" step="0.01" class="form-control" name="retail_price" value="0.00" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold" id="btnQuickSaveProduct">
                        <i class="fa fa-save me-1"></i>Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Vendor Modal -->
<div class="modal fade" id="addVendorModal" tabindex="-1" aria-labelledby="addVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 pb-2">
                <h5 class="modal-title fw-bold" id="addVendorModalLabel">
                    <i class="fas fa-truck text-primary me-2"></i>New Vendor / Supplier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ajaxAddVendorForm">
                @csrf
                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Vendor / Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="Vendor name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Phone Number</label>
                            <input type="text" class="form-control" name="phone_number" placeholder="0300-1234567">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Opening Balance</label>
                            <input type="number" step="0.01" class="form-control" name="opening_balance" value="0.00">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Address</label>
                            <input type="text" class="form-control" name="address" placeholder="Address">
                        </div>
                        <input type="hidden" name="branch_id" value="{{ $purchase->branch_id }}">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold" id="btnSaveAjaxVendor">
                        <i class="fa fa-save me-1"></i>Save Vendor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    window.WAREHOUSE_STOCKS = @json($warehouseStocks ?? []);
    window.BANK_ACCOUNTS = @json($bankAccounts ?? []);
    const originalPaidAmount = parseFloat("{{ (float)($purchase->paid_amount ?? 0) }}") || 0;

    function initProductSelect2($target) {
        var $elements = $target ? $($target) : $('.product-select');
        $elements.each(function() {
            var $el = $(this);
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                placeholder: "Select Product / Model...",
                width: '100%',
                allowClear: true,
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') return data;
                    if (typeof data.text === 'undefined') return null;
                    var term = params.term.toLowerCase();
                    var text = data.text.toLowerCase();
                    var code = $(data.element).data('code') ? $(data.element).data('code').toString().toLowerCase() : '';
                    if (text.indexOf(term) > -1 || code.indexOf(term) > -1) return data;
                    return null;
                }
            });
        });
    }

    function initHeaderSelects() {
        if ($('#vendorSelect').hasClass('select2-hidden-accessible')) {
            $('#vendorSelect').select2('destroy');
        }
        $('#vendorSelect').select2({
            placeholder: "Select or search vendor...",
            width: '100%',
            allowClear: true
        });

        if ($('#purchaserSelect').hasClass('select2-hidden-accessible')) {
            $('#purchaserSelect').select2('destroy');
        }
        $('#purchaserSelect').select2({
            placeholder: "Select Purchaser",
            width: '100%',
            allowClear: true
        });
    }

    initProductSelect2();
    initHeaderSelects();

    // Select2 Autofocus & Global Keyboard Navigation
    $(document).on('select2:open', function() {
        setTimeout(function() {
            const searchInput = document.querySelector('.select2-container--open .select2-search__field');
            if (searchInput) {
                searchInput.focus();
            }
            const $open = $('.select2-container--open');
            const $options = $open.find('.select2-results__option[aria-selected]');
            if ($options.length && !$options.filter('.select2-results__option--highlighted').length) {
                $options.first().addClass('select2-results__option--highlighted');
            }
        }, 20);
    });

    $(document).on('keydown', function(e) {
        const $openContainer = $('.select2-container--open');
        if (!$openContainer.length) return;

        if (e.key === 'Enter' || e.keyCode === 13) {
            const $highlighted = $openContainer.find('.select2-results__option--highlighted');
            if ($highlighted.length && !$highlighted.hasClass('select2-results__message')) {
                e.preventDefault();
                e.stopImmediatePropagation();

                let data = $highlighted.data('data');
                let $select = $openContainer.prev('select');
                if (data && $select.length) {
                    $select.val(data.id).trigger('change');
                    $select.trigger({
                        type: 'select2:select',
                        params: { data: data }
                    });
                    $select.select2('close');
                } else {
                    $highlighted.trigger('mouseup');
                }
            }
        }
    });

    $(document).on('select2:select', '.product-select', function() {
        var $row = $(this).closest('tr.item-row');
        setTimeout(function() {
            $row.find('.qty-input').focus().select();
        }, 80);
    });

    // Party Selection Logic (Vendor vs Customer)
    $('#vendorSelect').on('change', function() {
        var opt = $(this).find(':selected');
        var val = $(this).val();
        var partyType = opt.data('type') || 'vendor';
        var partyId = opt.data('id') || '';
        var name = opt.data('name') || '';
        var bal = parseFloat(opt.data('balance')) || 0;

        if (partyType === 'customer') {
            $('#partyTypeInput').val('customer');
            $('#customerIdInput').val(partyId);
            $('#vendorIdInput').val('');
            $('#vendorNameInput').val(name);
            $('#lblPrevBal').text('Receivable:');
            $('#badgeVendorPrevBal').removeClass('bg-danger-subtle text-danger border-danger-subtle bg-success-subtle text-success border-success-subtle')
                .addClass('bg-info-subtle text-info border-info-subtle')
                .text('Rs ' + bal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        } else {
            $('#partyTypeInput').val('vendor');
            $('#customerIdInput').val('');
            $('#vendorIdInput').val(partyId);
            $('#vendorNameInput').val(name);
            $('#lblPrevBal').text('Payable:');
            $('#badgeVendorPrevBal').removeClass('bg-info-subtle text-info border-info-subtle bg-success-subtle text-success border-success-subtle')
                .addClass('bg-danger-subtle text-danger border-danger-subtle')
                .text('Rs ' + bal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }

        if (!val) {
            $('#vendorIdInput').val('');
            $('#customerIdInput').val('');
            $('#partyTypeInput').val('vendor');
            $('#vendorNameInput').val('');
            $('#badgeVendorPrevBal').text('Rs 0.00');
        }

        $('#displayPrevBalance').text(bal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        recalc();
    });

    // Local Market / Cash Supplier toggle
    $('#btnWalkinToggle').on('click', function() {
        var isLocal = $(this).hasClass('active');
        if (isLocal) {
            // Switch back to Registered Vendor
            $(this).removeClass('active btn-primary').addClass('btn-outline-primary');
            $('#walkinNameInput').addClass('d-none');
            $('#vendorSelectBoxContainer').removeClass('d-none');
            $('#vendorModeInput').val('vendor');
            $('#vendorSelect').trigger('change');
        } else {
            // Switch to Local Market Free Text
            $(this).addClass('active btn-primary').removeClass('btn-outline-primary');
            $('#vendorSelectBoxContainer').addClass('d-none');
            $('#walkinNameInput').removeClass('d-none').focus();
            $('#vendorModeInput').val('local');
            $('#partyTypeInput').val('vendor');
            $('#vendorIdInput').val('');
            $('#customerIdInput').val('');
            $('#vendorNameInput').val($('#walkinNameInput').val() || 'Local Market');
            $('#badgeVendorPrevBal').text('Rs 0.00');
            $('#displayPrevBalance').text('0.00');
            recalc();
        }
    });

    $('#walkinNameInput').on('input', function() {
        $('#vendorNameInput').val($(this).val() || 'Local Market');
    });

    // Global Location sync with line warehouse
    $('#globalWarehouseSelect').on('change', function() {
        var selectedWh = $(this).val();
        $('.line-warehouse-select').each(function() {
            if (!$(this).data('customized')) {
                $(this).val(selectedWh);
                updateRowStock($(this).closest('tr'));
            }
        });
    });

    $(document).on('change', '.line-warehouse-select', function() {
        $(this).data('customized', true);
        updateRowStock($(this).closest('tr'));
    });

    // Update row stock
    function updateRowStock($row) {
        var pid = $row.find('.product-select').val();
        var wid = $row.find('.line-warehouse-select').val();
        var stock = 0;

        if (pid && window.WAREHOUSE_STOCKS && window.WAREHOUSE_STOCKS.length > 0) {
            var item = window.WAREHOUSE_STOCKS.find(function(s) {
                if (wid) {
                    return String(s.product_id) === String(pid) && String(s.warehouse_id) === String(wid);
                } else {
                    return String(s.product_id) === String(pid);
                }
            });
            if (item) stock = parseFloat(item.quantity) || 0;
        }
        $row.find('.stock').val(stock);
    }

    // Initialize stocks on load
    $('.item-row').each(function() {
        updateRowStock($(this));
    });

    // Packing info calculation helper
    function updatePackingInfo($row) {
        var opt = $row.find('.product-select option:selected');
        var ppp = parseFloat(opt.data('piece-per-pack')) || 1;
        var packType = opt.data('pack-type') || 'Standard';
        var unit = opt.data('unit') || 'Piece';
        var qty = parseFloat($row.find('.qty-input').val()) || 0;

        $row.find('.ipp-input').val(ppp);
        $row.find('.packing-type-input').val(packType);
        $row.find('.pack-qty-input').val(qty);

        var totalPieces = ppp > 1 ? (qty * ppp) : qty;
        $row.find('.total-pieces-input').val(totalPieces);

        var badge = $row.find('.packing-preview-badge');
        if (ppp > 1 && qty > 0) {
            $row.find('.unit-display').val(unit + ' (' + ppp + ' Pcs)');
            badge.html('<i class="fas fa-boxes"></i> ' + qty + ' Crtn = ' + totalPieces + ' Pcs').show();
        } else {
            $row.find('.unit-display').val(unit);
            badge.hide().empty();
        }
    }

    // Product Selection -> Auto Cost Price & Unit
    $(document).on('change', '.product-select', function() {
        var opt = $(this).find(':selected');
        var price = opt.data('price') || 0;
        var unit = opt.data('unit') || 'Piece';
        var $row = $(this).closest('tr');

        $row.find('.price-input').val(parseFloat(price).toFixed(2));
        $row.find('.unit-input').val(unit);
        $row.find('.unit-display').val(unit);
        updateRowStock($row);
        updatePackingInfo($row);
        recalc();
    });

    // Update packing on qty input change
    $(document).on('input', '.qty-input', function() {
        updatePackingInfo($(this).closest('tr'));
    });

    // Add New Row
    function addNewRow() {
        var newRow = $('.item-row:first').clone();
        newRow.find('input').val('');
        newRow.find('.qty-input').val('1');
        newRow.find('.price-input').val('0.00');
        newRow.find('.disc-input-visual').val('0');
        newRow.find('.disc-input-hidden').val('0');
        newRow.find('.line-total').val('0.00');
        newRow.find('.stock').val('0');
        newRow.find('.unit-input').val('Piece');
        newRow.find('.unit-display').val('Piece');
        newRow.find('.packing-type-input').val('Standard');
        newRow.find('.pack-qty-input, .ipp-input, .loose-pcs-input').val('0');
        newRow.find('.total-pieces-input').val('1');
        newRow.find('.packing-preview-badge').hide().empty();

        var toggleBtn = newRow.find('.disc-type-toggle');
        toggleBtn.attr('data-type', 'amount').text('Rs');
        newRow.find('.disc-type-input').val('amount');

        newRow.find('.line-warehouse-select').val($('#globalWarehouseSelect').val());
        newRow.find('.select2-container').remove();
        var $newSelect = newRow.find('.product-select');
        $newSelect.removeClass('select2-hidden-accessible').removeAttr('data-select2-id').removeAttr('tabindex').removeAttr('aria-hidden');
        $newSelect.find('option').removeAttr('data-select2-id');
        $newSelect.val('');

        $('#itemsList').append(newRow);
        updateRowIndices();
        initProductSelect2($newSelect);
        recalc();

        setTimeout(function() {
            $newSelect.select2('open');
        }, 80);
        return newRow;
    }

    $('#btnAdd').on('click', addNewRow);

    function updateRowIndices() {
        var count = 0;
        $('.item-row').each(function(i) {
            count++;
            $(this).find('.row-index').text(count);
        });
        $('#itemsRowCount').text(count);
    }

    // Remove row
    $(document).on('click', '.remove-row', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            updateRowIndices();
            recalc();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Cannot remove!',
                text: 'Invoice mein kam se kam 1 item hona zarori hai.'
            });
        }
    });

    // Enter key navigation & Auto Row Add
    $(document).on('keydown', '#itemsTable input, #itemsTable select', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            var $currentRow = $(this).closest('tr.item-row');
            var isLastRow = $currentRow.is(':last-child');

            if ($(this).hasClass('qty-input')) {
                $currentRow.find('.price-input').focus().select();
            } else if ($(this).hasClass('price-input')) {
                $currentRow.find('.disc-input-visual').focus().select();
            } else if ($(this).hasClass('disc-input-visual')) {
                if (isLastRow) {
                    addNewRow();
                } else {
                    $currentRow.next('tr.item-row').find('.product-select').select2('open');
                }
            } else {
                if (isLastRow) {
                    addNewRow();
                } else {
                    $currentRow.next('tr.item-row').find('.qty-input').focus().select();
                }
            }
        }
    });

    // Auto focus Qty when Product selected
    $(document).on('select2:select', '.product-select', function() {
        var $row = $(this).closest('tr.item-row');
        setTimeout(function() {
            $row.find('.qty-input').focus().select();
        }, 80);
    });

    // Discount Toggle (Rs / %)
    $(document).on('click', '.disc-type-toggle', function() {
        var type = $(this).attr('data-type');
        if (type === 'amount') {
            $(this).attr('data-type', 'percent').text('%');
            $(this).siblings('.disc-type-input').val('percent');
        } else {
            $(this).attr('data-type', 'amount').text('Rs');
            $(this).siblings('.disc-type-input').val('amount');
        }
        $(this).siblings('.disc-input-visual').focus();
        recalc();
    });

    // Recalculate Totals
    function recalc() {
        var subtotal = 0;
        var totalLineDisc = 0;

        $('.item-row').each(function() {
            var qty = parseFloat($(this).find('.qty-input').val()) || 0;
            var price = parseFloat($(this).find('.price-input').val()) || 0;
            var discVal = parseFloat($(this).find('.disc-input-visual').val()) || 0;
            var discType = $(this).find('.disc-type-input').val() || 'amount';

            var discAmt = 0;
            if (discType === 'percent') {
                discAmt = (qty * price) * (discVal / 100);
            } else {
                discAmt = discVal;
            }

            $(this).find('.disc-input-hidden').val(discAmt.toFixed(2));
            totalLineDisc += discAmt;

            var lineTotal = (qty * price) - discAmt;
            $(this).find('.line-total').val(lineTotal.toFixed(2));
            subtotal += lineTotal;
        });

        $('#gridTotalDisplay').text(subtotal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#tGross').text(subtotal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#tLineDisc').text(totalLineDisc.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#subtotal_val').val(subtotal.toFixed(2));

        var overallDisc = parseFloat($('#overallDiscount').val()) || 0;
        var extra = parseFloat($('#extraCost').val()) || 0;
        var net = Math.max(0, (subtotal - overallDisc) + extra);

        $('#tSub').text(net.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#walkinNetTotal').text(net.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#netAmount').val(net.toFixed(2));

        var prevBal = parseFloat($('#displayPrevBalance').text().replace(/,/g, '')) || 0;
        var totalPayable = net + prevBal;
        $('#displayTotalPayable').text(totalPayable.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        calcPayments();
    }

    $(document).on('input', '.qty-input, .price-input, .disc-input-visual, #overallDiscount', recalc);

    // Payment Toggles
    $('#badgeLater').click(function() {
        $('input[name="payment_type"][value="pay_later"]').prop('checked', true);
        $(this).addClass('active');
        $('#badgeNow').removeClass('active');
        $('#paymentFields').slideUp();
        calcPayments();
    });

    $('#badgeNow').click(function() {
        $('input[name="payment_type"][value="pay_now"]').prop('checked', true);
        $(this).addClass('active');
        $('#badgeLater').removeClass('active');
        $('#paymentFields').slideDown();

        var firstAmount = $('.rv-amount').first();
        if (firstAmount.val() === '' || typeof firstAmount.val() === 'undefined') {
            if (originalPaidAmount > 0) {
                firstAmount.val(originalPaidAmount.toFixed(2));
            } else {
                firstAmount.val('0.00');
            }
        }
        calcPayments();
        firstAmount.focus();
    });

    function calcPayments() {
        var isPayNow = $('input[name="payment_type"][value="pay_now"]').is(':checked');
        var totalPaid = 0;
        if (isPayNow) {
            $('.rv-amount').each(function() {
                var v = parseFloat($(this).val());
                if (!isNaN(v) && v > 0) totalPaid += v;
            });
        }
        $('#receiptsTotalBadge').text(totalPaid.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#bottomPaymentsTotal').text(totalPaid.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        var netAmount = parseFloat($('#netAmount').val()) || 0;
        var balanceDue = isPayNow ? Math.max(0, netAmount - totalPaid) : netAmount;

        $('#walkinChange').text(balanceDue.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#bottomChangeVal').text(balanceDue.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    $(document).on('input', '.rv-amount', calcPayments);

    // Add Account Row
    $('#btnAddRV').click(function() {
        var newRow = $('.rv-row:first').clone();
        newRow.find('.rv-amount').val('0.00');
        if (newRow.find('.btn-remove-rv').length === 0) {
            newRow.append('<button type="button" class="btn btn-sm btn-outline-danger btn-remove-rv" title="Remove">&times;</button>');
        }
        $('#rvWrapper').append(newRow);
        calcPayments();
    });

    $(document).on('click', '.btn-remove-rv', function() {
        $(this).closest('.rv-row').remove();
        calcPayments();
    });

    // Quick Add Product Form Submit
    $('#btnQuickSaveProduct').on('click', function() {
        var form = $('#quickAddProductForm');
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: "{{ route('store-product') }}",
            type: "POST",
            data: form.serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i>Save Product');
                $('#quickAddProductModal').modal('hide');
                form[0].reset();

                // Append new product to all product selects
                if (res && res.id) {
                    var newOption = new Option(res.item_name + (res.item_code ? ' ('+res.item_code+')' : ''), res.id, true, true);
                    $(newOption).data('price', res.wholesale_price || 0);
                    $(newOption).data('unit', res.unit ? res.unit.name : 'Piece');
                    $(newOption).data('code', res.item_code || '');
                    $(newOption).data('piece-per-pack', res.piece_per_pack || 1);
                    $(newOption).data('pack-type', res.pack_type || 'Standard');
                    $('.product-select:last').append(newOption).trigger('change');
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Created!',
                    text: 'Product successfully added.',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function(err) {
                btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i>Save Product');
                Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: 'Product create karne mein masla hua. Please check fields.'
                });
            }
        });
    });

    // Quick Add Vendor Form Submit
    $('#btnSaveAjaxVendor').on('click', function() {
        var form = $('#ajaxAddVendorForm');
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: "{{ route('vendors.store.ajax') }}",
            type: "POST",
            data: form.serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i>Save Vendor');
                $('#addVendorModal').modal('hide');
                form[0].reset();

                if (res && res.id) {
                    var newOption = new Option('🏢 ' + res.name + ' (Payable: Rs. ' + (parseFloat(res.opening_balance)||0).toFixed(2) + ')', 'vendor_' + res.id, true, true);
                    $(newOption).data('type', 'vendor');
                    $(newOption).data('id', res.id);
                    $(newOption).data('name', res.name);
                    $(newOption).data('balance', res.opening_balance || 0);
                    $('#vendorSelect').append(newOption).trigger('change');
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Vendor Created!',
                    text: 'New vendor successfully registered.',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function(err) {
                btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i>Save Vendor');
                Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: 'Vendor create nahi ho saka. Please check input.'
                });
            }
        });
    });

    // F9 Shortcut to submit form
    $(document).on('keydown', function(e) {
        if (e.key === 'F9' || e.keyCode === 120) {
            e.preventDefault();
            $('#purchaseForm').submit();
        }
    });

    // Form submit validation
    $('#purchaseForm').on('submit', function(e) {
        var hasProduct = false;
        $('.product-select').each(function() {
            if ($(this).val()) hasProduct = true;
        });

        if (!hasProduct) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'No Product Selected!',
                text: 'Kam se kam aik product select karna lazmi hai.'
            });
            return false;
        }

        var btn = $('#btnSavePurchase');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>UPDATING PURCHASE...');
        $('#btnSavePurchaseBottom').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>UPDATING...');
    });

    // Trigger vendor change to populate previous balance on load
    if ($('#vendorSelect').val()) {
        var opt = $('#vendorSelect').find(':selected');
        var bal = parseFloat(opt.data('balance')) || 0;
        $('#badgeVendorPrevBal').text('Rs ' + bal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#displayPrevBalance').text(bal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    // Initial calculation
    recalc();
});
</script>
@endsection

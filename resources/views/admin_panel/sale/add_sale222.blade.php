@extends('admin_panel.layout.app')

@section('content')
    <!-- Loader Overlay -->
    <div id="pageLoader"
        class="position-fixed top-0 start-0 w-100 h-100 flex-column gap-3 justify-content-center align-items-center d-none"
        style="background: rgba(255,255,255,0.9); z-index: 1055; display: none !important;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="fw-bold text-primary fs-5">Loading Sale Data...</div>
    </div>
    <link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendors/select2/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        /* ================= ULTRA-COMPACT EXCEL-LIKE ERP UI ================= */
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

        /* Section Titles */
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

        /* Read-only fields */
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
            min-width: 100% !important;
            table-layout: auto !important;
        }

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
        }

        .sales-table thead th.col-product {
            text-align: left !important;
            padding-left: 4px !important;
        }

        .sales-table tbody td {
            border: 1px solid #cbd5e1 !important;
            padding: 0 !important;
            background-color: #ffffff;
            vertical-align: middle !important;
        }

        /* ⚡ FLAT BORDERLESS GRID INPUTS - COMPACT ⚡ */
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

        .sales-table tbody td.col-product .form-select {
            text-align: left !important;
            padding-left: 12px !important;
        }

        .sales-table tbody .input-readonly,
        .sales-table tbody input[readonly] {
            background-color: #f1f5f9 !important;
            cursor: not-allowed !important;
            color: #475569 !important;
            font-weight: 600 !important;
        }

        .sales-table tbody .form-control:focus,
        .sales-table tbody .form-select:focus {
            outline: none !important;
            background-color: #eff6ff !important;
            box-shadow: inset 0 0 0 1px #2563eb !important;
        }

        /* Select2 Specific flat borderless styling */
        .sales-table tbody .select2-container--default .select2-selection--single {
            height: 26px !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background-color: transparent !important;
            display: flex;
            align-items: center;
        }

        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            padding-left: 4px !important;
            padding-right: 16px !important;
            font-size: 0.76rem !important;
            color: #1e293b !important;
            font-weight: 500 !important;
            text-align: left !important;
        }

        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 26px !important;
            right: 4px !important;
        }

        /* Elegant flat block layout for discount input + toggle */
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

        /* Column Widths - Product column stretches to 100% fill */
        .col-product { width: auto !important; }
        .col-warehouse { width: 140px !important; min-width: 140px !important; }
        .col-stock { width: 55px !important; min-width: 55px !important; }
        .col-qty { width: 85px !important; min-width: 85px !important; }
        .col-pieces { width: 50px !important; min-width: 50px !important; }
        .col-watt { width: 75px !important; min-width: 75px !important; }
        .col-price-p { width: 75px !important; min-width: 75px !important; }
        .col-disc { width: 75px !important; min-width: 75px !important; }
        .col-amount { width: 85px !important; min-width: 85px !important; }
        .col-action { width: 28px !important; min-width: 28px !important; text-align: center; }

        /* Quick Products Card */
        .pos-product-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .pos-product-name {
            font-size: 0.78rem;
            font-weight: 700;
            color: #1e293b;
        }
        .pos-product-add-btn {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            background: #2563eb;
            color: #fff;
            border: none;
            cursor: pointer;
        }

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
        .top-field-salesman { flex: 0 0 145px; max-width: 145px; }
        .top-field-crdays { flex: 0 0 65px; max-width: 65px; }
        .top-field-mbill { flex: 0 0 115px; max-width: 115px; }
        .top-field-remarks { flex: 1 1 160px; min-width: 120px; }
        @media (max-width: 767.98px) {
            .top-field-branch,
            .top-field-loc,
            .top-field-date,
            .top-field-salesman,
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
            border-radius: 8px !important;
            padding: 8px 24px !important;
            font-size: 0.9rem !important;
            border: none !important;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25) !important;
            transition: all 0.2s ease !important;
            cursor: pointer;
        }

        /* ================= UNIFIED CUSTOMER INPUT GROUP ================= */
        #customerInputWrapper {
            display: flex !important;
            align-items: stretch !important;
            height: 31px !important;
            min-height: 31px !important;
            max-height: 31px !important;
            width: 100% !important;
        }

        #customerInputWrapper #customerSelectBoxContainer {
            flex: 1 1 auto !important;
            min-width: 0 !important;
            height: 31px !important;
        }

        #customerInputWrapper #customerSelectBoxContainer .select2-container {
            width: 100% !important;
            height: 31px !important;
            display: block !important;
        }

        #customerInputWrapper #customerSelectBoxContainer .select2-container .select2-selection--single {
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

        #customerInputWrapper #customerSelectBoxContainer .select2-selection__rendered {
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

        #customerInputWrapper #customerSelectBoxContainer .select2-selection__arrow {
            height: 29px !important;
            top: 0 !important;
            right: 4px !important;
        }

        #customerInputWrapper #walkinNameInput {
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

        #customerInputWrapper #btnWalkinToggle {
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

        #customerInputWrapper #btnWalkinToggle.btn-primary.active {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
        }

        #customerInputWrapper #btnAddNewCustomerModal {
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
    </style>

    <div class="container-fluid py-0 px-1">
        <div class="main-container bg-white border mx-auto p-2 rounded-3">

            <div id="alertBox" class="alert d-none mb-1" role="alert" style="padding:4px 8px; font-size:0.78rem;"></div>

            <form id="saleForm" autocomplete="off">
                @csrf
                @if(isset($isEditMode) && $isEditMode && isset($sale))
                    <input type="hidden" id="sale_id" name="sale_id" value="{{ $sale->id }}">
                @endif
                <input type="hidden" id="booking_id" name="booking_id" value="{{ $sale->booking_id ?? '' }}">
                <input type="hidden" id="action" name="action" value="sale">
                <input type="hidden" id="is_posted" name="is_posted" value="{{ $sale->is_posted ?? 0 }}">
                <input type="hidden" id="is_finalized" name="is_finalized" value="0">
                <input type="hidden" name="Invoice_date" id="Invoice_date" value="{{ isset($sale) ? date('Y-m-d', strtotime($sale->created_at ?? date('Y-m-d'))) : date('Y-m-d') }}">
                <input type="hidden" name="estimated_delivery_date" id="estimated_delivery_date" value="{{ date('Y-m-d', strtotime('+15 days')) }}">
                <input type="hidden" id="customer_id" name="customer_id" value="{{ $sale->customer_id ?? '' }}">
                <input type="hidden" id="customer" name="customer" value="{{ $sale->customer_id ?? '' }}">
                <input type="hidden" id="address" name="address" value="{{ $sale->address ?? '' }}">
                <input type="hidden" id="tel" name="tel" value="{{ $sale->tel ?? '' }}">
                <input type="hidden" id="previousBalance" name="previousBalance" value="{{ $sale->previous_balance ?? 0 }}">
                <input type="hidden" id="rangeBalance" value="0">
                <input type="hidden" id="creditLimit" value="0">
                <input type="hidden" id="noCreditLimit" value="0">
                <input type="hidden" name="Invoice_no" id="inputInvoiceNo" value="{{ $sale->invoice_no ?? $nextInvoiceNumber }}">
                
                {{-- Hidden Party Type Radio Group for JS compatibility --}}
                <div class="d-none" id="partyTypeGroup">
                    <input type="radio" name="partyType" value="credit" id="typeCustomers" {{ ($partyType ?? $sale->partyType ?? $sale->party_type ?? 'credit') === 'credit' ? 'checked' : '' }}>
                    <input type="radio" name="partyType" value="cash" id="typeWalkin" {{ ($partyType ?? $sale->partyType ?? $sale->party_type ?? 'credit') === 'cash' ? 'checked' : '' }}>
                    <input type="radio" name="partyType" value="walking" id="typewalking" {{ ($partyType ?? $sale->partyType ?? $sale->party_type ?? 'credit') === 'walking' ? 'checked' : '' }}>
                </div>

                {{-- TOP HEADER BAR WITH INVOICE NO BADGE & SALE SETTINGS BUTTON --}}
                <div class="d-flex justify-content-between align-items-center mb-1 px-1">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('sale.index') }}" class="btn btn-sm btn-light border rounded-circle" title="Back"><i class="fas fa-arrow-left text-secondary"></i></a>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                                <i class="fas fa-{{ isset($isEditMode) && $isEditMode ? 'edit text-warning' : 'shopping-cart text-primary' }}"></i> {{ isset($isEditMode) && $isEditMode ? 'Edit Sale #' . ($sale->invoice_no ?? '') : 'New Sale' }}
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1 rounded-pill" style="font-size: 0.78rem; background: #e6fcf5;">
                                    <i class="fas fa-file-invoice me-1"></i><span id="activePrefixLabelHeader">{{ $activePrefix ?? 'INV' }}</span>-<span id="headerInvoiceNoDisplay">{{ $sale->invoice_no ?? $nextInvoiceNumber }}</span>
                                </span>
                            </h5>
                            <small class="text-muted" style="font-size: 0.7rem;">{{ isset($isEditMode) && $isEditMode ? 'Modify existing sale record' : 'Create a new invoice' }}</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('sale.settings.index') }}" target="_blank" class="btn btn-sm btn-light border rounded-3 text-secondary fw-bold" title="Sale Settings" style="font-size:0.75rem;">
                            <i class="fas fa-cog text-warning me-1"></i>Settings
                        </a>
                        <button type="button" class="btn btn-sm btn-light border rounded-3 text-secondary" title="Calculator"><i class="fas fa-calculator"></i></button>
                        <button type="button" class="btn btn-sm btn-light border rounded-3 text-secondary" title="Toggle Theme"><i class="fas fa-moon"></i></button>
                        <button type="button" class="btn btn-sm btn-light border rounded-3 text-secondary" title="Fullscreen" onclick="document.documentElement.requestFullscreen()"><i class="fas fa-expand"></i></button>
                    </div>
                </div>

                <!-- TOP INFORMATION PANEL -->
                <div class="card-panel mb-1 bg-white border rounded-3 shadow-xs" style="padding: 4px 6px !important;">
                    <div class="top-info-fields-row">
                        <!-- Branch Selection (Super Admin dropdown, Regular User readonly/badge) -->
                        <div class="top-field-branch">
                            <label class="form-label fw-bold text-uppercase text-primary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                                <i class="fas fa-building me-1"></i>Branch
                            </label>
                            @if (Auth::user() && Auth::user()->hasRole('super admin'))
                                <select class="form-select fw-bold border-primary shadow-xs" name="branch_id" id="branch_id" style="font-size:0.78rem; height: 28px;">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ (isset($sale) && $sale->branch_id == $b->id) || (!isset($sale) && ((isset($defaultBranchId) && $defaultBranchId == $b->id) || (Auth::user()->branch_id == $b->id))) ? 'selected' : '' }}>
                                            {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <div class="form-control form-control-sm bg-light fw-bold text-primary text-truncate border-secondary-subtle" style="font-size:0.78rem; height: 28px; line-height: 20px;">
                                    <i class="fas fa-store me-1"></i>{{ Auth::user()->branch->name ?? 'Default Branch' }}
                                </div>
                                <input type="hidden" name="branch_id" id="branch_id" value="{{ $sale->branch_id ?? Auth::user()->branch_id ?? 1 }}">
                            @endif
                        </div>

                        <!-- Location Selection (Shop / Warehouse) -->
                        <div class="top-field-loc">
                            <label class="form-label fw-bold text-uppercase text-secondary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                                <i class="fas fa-map-marker-alt text-info me-1"></i>Location {{ (isset($saleSettings) && $saleSettings->warehouse_mode === 'product_wise') ? '(Default)' : '' }}
                            </label>
                            <select class="form-select fw-bold shadow-xs" name="global_warehouse_id" id="globalWarehouseSelect" style="font-size:0.78rem; height: 28px;">
                                @if(isset($branches) && count($branches) > 0)
                                    <optgroup label="🏬 Shops / Branches (Shop Stock)">
                                        @foreach($branches as $b)
                                            <option value="branch_{{ $b->id }}" {{ (isset($defaultBranchId) && $defaultBranchId == $b->id) || (!isset($sale) && $loop->first) || (isset($sale) && is_null($sale->warehouse_id) && $sale->branch_id == $b->id) ? 'selected' : '' }}>
                                                {{ $b->name }} (Shop)
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @php
                                    $whItems = (isset($allWarehouses) && count($allWarehouses) > 0) ? $allWarehouses : ((isset($warehouse) && count($warehouse) > 0) ? $warehouse : \App\Models\Warehouse::all());
                                @endphp
                                @if(isset($whItems) && count($whItems) > 0)
                                    <optgroup label="🏭 Warehouses (Godown Stock)">
                                        @foreach($whItems as $w)
                                            <option value="warehouse_{{ $w->id }}" {{ (isset($sale) && $sale->warehouse_id == $w->id) ? 'selected' : '' }}>
                                                {{ $w->warehouse_name ?? $w->name }}
                                            </option>
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
                            <input type="date" name="sale_date" class="form-control text-center fw-bold shadow-xs" id="displayDateInput" value="{{ isset($sale) && $sale->created_at ? (is_string($sale->created_at) ? substr($sale->created_at, 0, 10) : $sale->created_at->format('Y-m-d')) : date('Y-m-d') }}" style="font-size:0.78rem; height: 28px;">
                        </div>

                        <!-- Salesman Selection Dropdown -->
                        <div class="top-field-salesman">
                            <label class="form-label fw-bold text-uppercase text-secondary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                                <i class="fas fa-user-tie text-warning me-1"></i>Salesman
                            </label>
                            <select class="form-select fw-bold shadow-xs" name="salesman_id" id="salesmanSelect" style="font-size:0.78rem; height: 28px;">
                                <option value="">Select Salesman</option>
                                @if(isset($salesmen) && count($salesmen) > 0)
                                    @foreach($salesmen as $sm)
                                        <option value="{{ $sm->id }}" {{ (isset($booking) && $booking->salesman_id == $sm->id) ? 'selected' : (isset($sale) && $sale->salesman_id == $sm->id ? 'selected' : '') }}>
                                            {{ $sm->name ?? $sm->salesman_name ?? $sm->full_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Cr. Days -->
                        <div class="top-field-crdays">
                            <label class="form-label fw-bold text-uppercase text-secondary mb-0 text-truncate d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                                <i class="fas fa-clock text-danger me-1"></i>Cr. Days
                            </label>
                            <input type="number" class="form-control text-center fw-bold shadow-xs" name="credit_days" placeholder="0" min="0" value="{{ $sale->credit_days ?? '0' }}" style="font-size:0.78rem; height: 28px;">
                        </div>

                        <!-- M. Bill # (Manual Invoice) -->
                        <div class="top-field-mbill">
                            <label class="form-label fw-bold text-uppercase text-secondary mb-0 d-flex align-items-center text-truncate" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                                <i class="fas fa-file-invoice text-secondary me-1"></i>M. Bill#
                            </label>
                            <input type="text" class="form-control shadow-xs" name="manual_invoice" id="manualInvoice" value="{{ $sale->manual_invoice ?? $booking->manual_invoice ?? '' }}" placeholder="Manual bill#..." style="font-size:0.78rem; height: 28px;">
                            <input type="hidden" name="Invoice_main" id="Invoice_main" value="{{ $sale->manual_invoice ?? $booking->manual_invoice ?? '' }}">
                        </div>

                        <!-- Remarks -->
                        <div class="top-field-remarks">
                            <label class="form-label fw-bold text-uppercase text-secondary mb-0 d-flex align-items-center text-truncate" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                                <i class="fas fa-sticky-note text-secondary me-1"></i>Remarks
                            </label>
                            <input type="text" class="form-control shadow-xs" name="remarks" id="remarks" value="{{ $sale->remarks ?? $booking->remarks ?? '' }}" placeholder="Enter remarks..." style="font-size:0.78rem; height: 28px;">
                            <input type="hidden" name="reference" id="reference" value="{{ $sale->remarks ?? $booking->remarks ?? '' }}">
                        </div>
                    </div>

                    <!-- Customer Input Group -->
                    <div style="margin-top: 3px;">
                        <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 2px !important;">
                            <label class="form-label fw-bold text-uppercase text-primary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px;">
                                <i class="fas fa-user-circle text-primary me-1"></i>Customer Selection
                            </label>
                            <div id="customerPrevBalWrapper" class="d-flex align-items-center gap-1" style="font-size:0.75rem;">
                                <span class="text-muted fw-semibold">Prev. Balance:</span>
                                <span id="badgeCustomerPrevBal" class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold font-monospace px-2 py-0" style="font-size:0.78rem;">Rs 0.00</span>
                            </div>
                        </div>
                        <div id="customerInputWrapper" class="input-group input-group-sm shadow-xs rounded">
                            <div id="customerSelectBoxContainer" class="flex-grow-1" style="min-width: 0;">
                                <select class="form-select" id="customerSelect" name="customer_select" style="width:100%">
                                    <option value="">Select or search customer...</option>
                                    @if(isset($customer) && $customer->count() > 0)
                                        @foreach($customer as $c)
                                            <option value="{{ $c->id }}"
                                                data-mobile="{{ $c->mobile ?? '' }}"
                                                data-address="{{ $c->address ?? '' }}"
                                                data-prev-balance="{{ $c->closing_balance ?? $c->opening_balance ?? 0 }}"
                                                {{ (isset($sale) && $sale->customer_id == $c->id) ? 'selected' : '' }}>
                                                {{ $c->customer_id }} — {{ $c->customer_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <input type="text" class="form-control fw-bold d-none" name="walkin_name" id="walkinNameInput" value="Walk-in Customer" placeholder="Enter Walk-in Name...">
                            <button type="button" class="btn btn-outline-primary fw-bold px-3 d-flex align-items-center gap-1" id="btnWalkinToggle">
                                <i class="fas fa-walking"></i> Walk-in
                            </button>
                            <button type="button" class="btn btn-outline-success fw-bold px-3 d-flex align-items-center gap-1" id="btnAddNewCustomerModal" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                <i class="fas fa-user-plus"></i> New
                            </button>
                            <input type="checkbox" id="walkinToggle" name="is_walkin" value="1" class="d-none">
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
                                    <div class="section-title mb-0" style="font-size:0.8rem;">Order Items (<span id="itemsRowCount">0</span>)</div>
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
                                <table class="table table-bordered sales-table mb-0" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="width:25px;" class="text-center">#</th>
                                            <th class="col-product" style="min-width: 140px;">PRODUCT / MODEL</th>
                                            @if(isset($saleSettings) && $saleSettings->warehouse_mode === 'product_wise')
                                                <th class="col-warehouse" style="width: 140px;">WAREHOUSE</th>
                                            @endif
                                            <th class="col-stock" style="width: 55px;">STOCK</th>
                                            <th class="col-qty" style="width: 85px;">QTY</th>
                                            <th class="col-pieces" style="width: 50px;">PCS</th>
                                            @if(isset($saleSettings) && $saleSettings->show_watt)
                                                <th class="col-watt" style="width: 75px;">WATT</th>
                                            @endif
                                            <th class="col-price-p" style="width: 80px;">PRICE</th>
                                            @if(!isset($saleSettings) || $saleSettings->show_line_discount)
                                                <th class="col-disc" style="width: 80px;">DISCOUNT</th>
                                            @endif
                                            <th class="col-amount" style="width: 90px;">AMOUNT</th>
                                            <th class="col-action" style="width: 32px;">×</th>
                                        </tr>
                                    </thead>
                                    <tbody id="salesTableBody">
                                        @if(isset($isEditMode) && $isEditMode && !empty($saleItems))
                                            @foreach($saleItems as $index => $item)
                                                <tr>
                                                    <td class="text-center fw-bold text-muted row-index" style="vertical-align:middle; font-size:0.75rem;">{{ $index + 1 }}</td>
                                                    <td class="col-product">
                                                        <select class="form-select product product-select" name="product_id[]" style="width:100%">
                                                            <option value="{{ $item['product_id'] }}" selected>{{ $item['item_name'] }}</option>
                                                        </select>
                                                        <input type="hidden" class="product-id-hidden" name="product_id_hidden[]" value="{{ $item['product_id'] }}">
                                                        <input type="hidden" class="variant-data-hidden" name="color[]" value="">
                                                        <input type="hidden" class="item-code-display" value="{{ $item['item_code'] ?? '' }}">
                                                    </td>

                                                    @if(isset($saleSettings) && $saleSettings->warehouse_mode === 'product_wise')
                                                        <td class="col-warehouse">
                                                            <select class="form-select warehouse warehouse-select shadow-none" name="warehouse_id[]" style="width: 100%; height: 26px; font-size: 0.78rem; padding: 2px 4px; font-weight: 600;">
                                                                @php
                                                                    $itemWh = $item['warehouse_id'] ?? null;
                                                                    if ($itemWh && !str_starts_with((string)$itemWh, 'warehouse_') && !str_starts_with((string)$itemWh, 'branch_')) {
                                                                        $itemWh = 'warehouse_' . $itemWh;
                                                                    }
                                                                    if (!$itemWh) {
                                                                        $itemWh = isset($sale) && $sale->branch_id ? ('branch_' . $sale->branch_id) : 'branch_1';
                                                                    }
                                                                @endphp
                                                                @if(isset($branches) && count($branches) > 0)
                                                                    <optgroup label="🏬 Shops / Branches">
                                                                        @foreach($branches as $b)
                                                                            <option value="branch_{{ $b->id }}" {{ $itemWh === 'branch_'.$b->id ? 'selected' : '' }}>
                                                                                {{ $b->name }} (Shop)
                                                                            </option>
                                                                        @endforeach
                                                                    </optgroup>
                                                                @endif
                                                                @php
                                                                    $whList = (isset($allWarehouses) && count($allWarehouses) > 0) ? $allWarehouses : ((isset($warehouse) && count($warehouse) > 0) ? $warehouse : []);
                                                                @endphp
                                                                @if(count($whList) > 0)
                                                                    <optgroup label="🏭 Warehouses">
                                                                        @foreach($whList as $w)
                                                                            <option value="warehouse_{{ $w->id }}" {{ $itemWh === 'warehouse_'.$w->id ? 'selected' : '' }}>
                                                                                {{ $w->warehouse_name ?? $w->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </optgroup>
                                                                @endif
                                                            </select>
                                                        </td>
                                                        <td class="col-stock">
                                                            <input type="text" class="form-control stock text-center input-readonly" readonly tabindex="-1" value="{{ $item['onhand_qty'] ?? 0 }}">
                                                        </td>
                                                    @else
                                                        <td class="col-stock">
                                                            <input type="text" class="form-control stock text-center input-readonly" readonly tabindex="-1" value="{{ $item['onhand_qty'] ?? 0 }}">
                                                            <input type="hidden" class="warehouse" name="warehouse_id[]" value="{{ $item['warehouse_id'] ?? '' }}">
                                                        </td>
                                                    @endif

                                                    <td style="width:95px;min-width:95px;" class="col-qty-wrapper">
                                                        <input type="number" step="any" class="form-control carton-qty sales-qty text-start" name="carton_qty[]" placeholder="0" min="0" value="{{ $item['sales_qty'] ?? 1 }}" style="width: 100%; height: 26px; font-size: 0.85rem; padding-left: 6px;">
                                                        <input type="hidden" name="sales_qty[]" class="sales-qty-mirror" value="{{ $item['sales_qty'] ?? 1 }}">
                                                    </td>
                                                    <td class="col-pieces">
                                                        <input type="text" class="form-control total-pieces text-end input-readonly" name="total_pieces[]" readonly placeholder="0" tabindex="-1" value="{{ $item['sales_qty'] ?? 1 }}">
                                                        <input type="hidden" class="sales-qty-hidden" name="qty[]" value="{{ $item['sales_qty'] ?? 1 }}">
                                                    </td>

                                                    @if(isset($saleSettings) && $saleSettings->show_watt)
                                                        <td class="col-watt">
                                                            <input type="number" step="any" class="form-control item-watt text-center" name="watt[]" placeholder="0" min="0" value="{{ $item['watt'] ?? 0 }}" style="width: 100%; height: 26px; font-size: 0.85rem; padding: 2px 4px; font-weight: 700;">
                                                        </td>
                                                    @else
                                                        <td class="d-none">
                                                            <input type="hidden" class="item-watt" name="watt[]" value="{{ $item['watt'] ?? 0 }}">
                                                        </td>
                                                    @endif

                                                    <td class="col-price-p">
                                                        <input type="text" class="form-control visible-price retail-price text-end" name="retail_price[]" value="{{ number_format($item['retail_price'] ?? 0, 2, '.', '') }}" placeholder="0" style="width: 100%;">
                                                        <input type="hidden" class="price-per-piece" name="price_per_piece[]" value="{{ number_format($item['retail_price'] ?? 0, 2, '.', '') }}">
                                                    </td>

                                                    @if(!isset($saleSettings) || $saleSettings->show_line_discount)
                                                        <td class="col-disc">
                                                            <div class="discount-wrapper">
                                                                <input type="number" class="form-control discount-value text-end" name="discount_percentage[]" value="{{ ($item['discount_percent'] ?? 0) > 0 ? $item['discount_percent'] : (($item['discount_amount'] ?? 0) > 0 ? $item['discount_amount'] : '') }}" placeholder="0">
                                                                <input type="hidden" class="discount-type-field" name="discount_type[]" value="{{ $item['discount_type'] ?? 'percent' }}">
                                                                <button type="button" class="btn btn-outline-secondary discount-toggle" data-type="{{ $item['discount_type'] ?? 'percent' }}" tabindex="-1">{{ ($item['discount_type'] ?? 'percent') === 'percent' ? '%' : 'Rs' }}</button>
                                                            </div>
                                                            <input type="hidden" class="discount-amount" name="discount_amount[]" value="{{ number_format($item['discount_amount'] ?? 0, 2, '.', '') }}">
                                                        </td>
                                                    @else
                                                        <td class="d-none">
                                                            <input type="hidden" class="discount-value" name="discount_percentage[]" value="{{ ($item['discount_percent'] ?? 0) > 0 ? $item['discount_percent'] : (($item['discount_amount'] ?? 0) > 0 ? $item['discount_amount'] : '0') }}">
                                                            <input type="hidden" class="discount-type-field" name="discount_type[]" value="{{ $item['discount_type'] ?? 'percent' }}">
                                                            <input type="hidden" class="discount-amount" name="discount_amount[]" value="{{ number_format($item['discount_amount'] ?? 0, 2, '.', '') }}">
                                                        </td>
                                                    @endif

                                                    <td class="col-amount">
                                                        <input type="text" class="form-control sales-amount text-end input-readonly" name="sales_amount[]" value="{{ number_format($item['amount'] ?? 0, 2, '.', '') }}" readonly tabindex="-1">
                                                        <input type="hidden" class="gross-amount" value="{{ number_format($item['amount'] ?? 0, 2, '.', '') }}">
                                                    </td>
                                                    <td class="col-action text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger del-row" tabindex="-1">&times;</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            @php
                                                $colsBeforeAmount = 5
                                                    + ((isset($saleSettings) && $saleSettings->warehouse_mode === 'product_wise') ? 1 : 0)
                                                    + ((isset($saleSettings) && $saleSettings->show_watt) ? 1 : 0)
                                                    + ((!isset($saleSettings) || $saleSettings->show_line_discount) ? 1 : 0);
                                            @endphp
                                            <td colspan="{{ $colsBeforeAmount }}" class="text-end fw-bold text-uppercase text-secondary" style="font-size:0.78rem;">Grid Total:</td>
                                            <td class="text-end fw-bold text-success fs-6"><span id="totalAmount">{{ isset($sale) ? number_format($sale->total_net ?? $sale->sub_total2 ?? 0, 2, '.', '') : '0.00' }}</span></td>
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
                                    <span class="fw-bold text-dark" id="tGross">{{ isset($sale) ? number_format($sale->sub_total1 ?? $sale->total_net ?? 0, 2, '.', '') : '0.00' }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="text-muted">Line Discount</span>
                                    <span class="fw-bold text-danger" id="tLineDisc">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="fw-bold text-dark">Current Net</span>
                                    <span class="summary-val-net" id="tSub">{{ isset($sale) ? number_format($sale->total_net ?? $sale->sub_total2 ?? 0, 2, '.', '') : '0.00' }}</span>
                                </div>
                                <div class="summary-row" id="rowPrevBalance">
                                    <span class="text-muted">Prev. Balance</span>
                                    <span class="fw-bold text-danger" id="displayPrevBalance">{{ isset($sale) && $sale->party_type !== 'walking' ? number_format($sale->previous_balance ?? 0, 2, '.', '') : '0.00' }}</span>
                                </div>
                                <div class="summary-row" id="rowTotalPayable">
                                    <span class="fw-bold text-primary">Total Payable</span>
                                    <span class="fw-bold text-primary" id="displayTotalPayable">{{ isset($sale) ? number_format($sale->total_balance ?? $sale->total_net ?? 0, 2, '.', '') : '0.00' }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="text-muted">Total Paid</span>
                                    @php
                                        $initialPaid = 0;
                                        if (isset($receipts) && count($receipts) > 0) {
                                            $initialPaid = $receipts->sum(fn($r) => floatval($r->total_amount ?? $r->amount ?? 0));
                                        } elseif (isset($sale)) {
                                            $initialPaid = floatval($sale->receipt1 ?? 0) + floatval($sale->receipt2 ?? 0);
                                        }
                                    @endphp
                                    <span class="fw-bold text-success" id="receiptsTotalBadge">{{ number_format($initialPaid, 2, '.', '') }}</span>
                                </div>
                                <div class="summary-row pt-1">
                                    <span class="fw-bold text-danger" id="lblChangeOrDue">Balance Due</span>
                                    <span class="summary-val-change" id="walkinChange">{{ isset($sale) ? number_format(max(0, ($sale->total_balance ?? $sale->total_net ?? 0) - $initialPaid), 2, '.', '') : '0.00' }}</span>
                                </div>

                                {{-- Hidden elements for backward compatibility --}}
                                <span class="d-none" id="receiptsTotal">0</span>
                                <span class="d-none" id="tOrderDisc">0</span>
                                <span class="d-none" id="tPrev">0</span>
                                <span class="d-none" id="tPayable">0</span>
                            </div>

                            <!-- Payment Methods Card -->
                            <div class="card-panel bg-white flex-grow-1 d-flex flex-column" style="border-radius:4px; padding: 6px 8px !important;">
                                <div class="d-flex align-items-center justify-content-between mb-1 pb-1 border-bottom">
                                    <span class="fw-bold text-dark" style="font-size:0.8rem;"><i class="fas fa-wallet text-success me-1"></i>Payment Methods</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" id="btnAddRV" style="font-size:0.7rem;"><i class="fas fa-plus me-1"></i>Add Account</button>
                                </div>

                                <div id="rvWrapper" class="mb-1">
                                    <div class="d-flex gap-1 align-items-center mb-1 rv-row">
                                        <select class="form-select form-select-sm rv-account bg-light fw-bold" name="receipt_account_id[]" style="font-size:0.75rem;">
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title), 'cash') || str_contains(strtolower($acc->title), 'easypaisa') ? 'selected' : '' }}>{{ $acc->title }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" step="0.01" class="form-control form-control-sm text-end rv-amount fw-bold" name="receipt_amount[]" placeholder="0.00" style="width: 100px; font-size:0.75rem;">
                                    </div>
                                </div>

                                <button type="button" class="btn btn-save-complete w-100 mt-auto py-1.5" id="btnSaveAndComplete" style="padding: 6px 12px !important; font-size: 0.85rem !important;">
                                    <i class="fas fa-save me-2"></i>{{ isset($isEditMode) && $isEditMode ? 'Update Sale' : 'Save & Complete (F9)' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM SUMMARY STRIP -->
                <div class="bottom-summary-strip">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-semibold" style="font-size:0.8rem;">Net Total</span>
                        <span class="fs-5 fw-bold text-primary" id="walkinNetTotal">0.00</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-semibold" style="font-size:0.8rem;">Discount (Rs.)</span>
                        <div class="input-group input-group-sm" style="width: 130px;">
                            <input type="number" class="form-control text-end fw-bold text-danger" id="walkinDiscountRs" name="additional_discount" value="{{ isset($sale) ? ($sale->additional_discount ?? 0) : 0 }}" placeholder="0">
                            <input type="hidden" id="discountPercent" value="0">
                            <span class="input-group-text bg-light text-muted">%</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-semibold" style="font-size:0.8rem;">Payments</span>
                        <span class="fs-6 fw-bold text-success" id="bottomPaymentsTotal">0.00</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-semibold" style="font-size:0.8rem;">Change</span>
                        <span class="fs-6 fw-bold text-danger" id="bottomChangeVal">0.00</span>
                    </div>

                    <button type="button" class="btn btn-save-complete" id="btnSaveAndComplete2">
                        <i class="fas fa-save me-2"></i>{{ isset($isEditMode) && $isEditMode ? 'Update Sale' : 'Save & Complete (F9)' }}
                    </button>
                </div>

                {{-- Additional hidden fields for calculations --}}
                <input type="hidden" name="extra_charges" id="echarges" value="0">
                <input type="hidden" name="notify_me" id="notify_me" value="">
                <input type="hidden" name="subTotal1" id="subTotal1" value="0">
                <input type="hidden" name="subTotal2" id="subTotal2" value="0">
                <input type="hidden" name="discountAmount" id="discountAmount" value="0">
                <input type="hidden" name="totalBalance" id="totalBalance" value="0">

                {{-- ACTION BUTTONS ROW WITH 3 MAIN ACTION BUTTONS & PRINTING --}}
                <div class="d-flex flex-wrap gap-2 justify-content-center py-2 px-3 mt-2 border-top bg-white rounded-3 shadow-sm">
                    @if(isset($isEditMode) && $isEditMode)
                        <button type="button" class="btn btn-success btn-sm px-5 py-2 fw-bold fs-6 shadow-sm" id="btnPosted" style="background:#16a34a !important; border-color:#16a34a !important;">
                            <i class="fas fa-save me-1"></i>Update Sale
                        </button>
                    @else
                        <button type="button" class="btn btn-primary btn-sm px-4 py-2 fw-bold shadow-sm" id="btnSave" style="background:#2563eb !important; border-color:#2563eb !important;">
                            <i class="fas fa-file-invoice me-1"></i>Save Booking (Draft)
                        </button>
                        <button type="button" class="btn btn-success btn-sm px-5 py-2 fw-bold fs-6 shadow-sm" id="btnPosted" style="background:#16a34a !important; border-color:#16a34a !important;">
                            <i class="fas fa-shopping-cart me-1"></i>Process Sale / POS
                        </button>
                        <button type="button" class="btn btn-warning btn-sm px-4 py-2 fw-bold text-dark shadow-sm" id="btnPartialSale" style="background:#f59e0b !important; border-color:#d97706 !important;">
                            <i class="fas fa-truck-loading me-1"></i>Partially Sale
                        </button>
                    @endif
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold" id="btnPrint"><i class="fas fa-print me-1"></i>A4 Print</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold" id="btnEstimate"><i class="fas fa-file-invoice me-1"></i>Estimate</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold" id="btnPrint2"><i class="fas fa-receipt me-1"></i>Thermal Print</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold" id="btnDcThermal"><i class="fas fa-truck me-1"></i>DC</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">
                        <i class="fas fa-user-plus text-primary me-2"></i>New Customer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ajaxAddCustomerForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Customer Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="customer_type" required>
                                    <option value="Main Customer">Main Customer</option>
                                    <option value="Walking Customer">Walking Customer</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" required placeholder="Customer Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mobile</label>
                                <input type="text" class="form-control" name="mobile" placeholder="0300-1234567">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Opening Balance</label>
                                <input type="number" step="0.01" class="form-control" name="opening_balance" value="0">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Address</label>
                                <input type="text" class="form-control" name="address" placeholder="Address">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSaveAjaxCustomer">Save Customer</button>
                </div>
            </div>
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
                                <input type="text" class="form-control" name="product_name" required placeholder="Enter product name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Category <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" id="qap_category" required>
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Sub Category</label>
                                <select class="form-select" name="sub_category_id" id="qap_subcategory">
                                    <option value="">Select Sub Category</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Brand <span class="text-danger">*</span></label>
                                <select class="form-select" name="brand_id" id="qap_brand" required>
                                    <option value="">Select Brand</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Model / Series</label>
                                <input type="text" class="form-control" name="model" placeholder="Optional">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Size Mode <span class="text-danger">*</span></label>
                                <select class="form-select" name="size_mode" id="qap_size_mode" required>
                                    <option value="by_cartons" selected>By Cartons</option>
                                    <option value="by_pieces">By Pieces</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="qap_ppb_wrap">
                                <label class="form-label fw-bold small text-muted">Pieces Per Box</label>
                                <input type="number" class="form-control" name="pieces_per_box" id="qap_ppb" value="1" min="1" placeholder="e.g. 12">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Low Stock (Cartons)</label>
                                <input type="number" class="form-control" name="alert_carton_quantity" min="0" placeholder="e.g. 5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Purchase Price /pc</label>
                                <input type="number" step="0.01" class="form-control" name="purchase_price_per_piece" value="0" placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Sale Price /pc</label>
                                <input type="number" step="0.01" class="form-control" name="sale_price_per_box" value="0" placeholder="0.00">
                            </div>
                        </div>
                        <input type="hidden" name="boxes_quantity" value="0">
                        <input type="hidden" name="loose_pieces" value="0">
                        <input type="hidden" name="piece_quantity" value="0">
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnQuickSaveProduct">
                            <i class="fa fa-save me-1"></i>Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- PARTIAL SALE / DELIVERY DISPATCH MODAL --}}
    <div class="modal fade" id="partialSaleModal" tabindex="-1" aria-labelledby="partialSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark py-2 px-3">
                    <h5 class="modal-title fw-bold fs-6" id="partialSaleModalLabel">
                        <i class="fas fa-truck-loading me-2"></i> Partial Delivery / Sale Dispatch
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="alert alert-warning py-2 px-3 mb-3 small d-flex align-items-center gap-2 border-warning">
                        <i class="fas fa-info-circle fs-5 text-dark"></i>
                        <div class="text-dark">
                            Select the location (Shop / Warehouse) and specify the quantity to dispatch out <strong>NOW</strong>. 
                            A Delivery Challan (DC) will be generated for dispatched items, and the remaining stock will be saved in 
                            <strong>Pending Deliveries</strong> until fully issued.
                        </div>
                    </div>

                    <div class="row g-2 mb-3 align-items-center bg-light p-2 rounded border">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small mb-0"><i class="fas fa-map-marker-alt me-1 text-primary"></i> Dispatch Location:</label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select form-select-sm border-primary fw-semibold" id="partial_warehouse_id">
                                <option value="">-- Select Dispatch Location (Shop / Warehouse) --</option>
                                @if(isset($branches) && count($branches) > 0)
                                    <optgroup label="🏬 Shops / Main Store">
                                        @foreach($branches as $b)
                                            <option value="branch_{{ $b->id }}" {{ (isset($defaultBranchId) && $defaultBranchId == $b->id) || ($loop->first) ? 'selected' : '' }}>
                                                {{ $b->name }} (Shop / Main Store)
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @php
                                    $whList = (isset($allWarehouses) && count($allWarehouses) > 0) ? $allWarehouses : ((isset($warehouse) && count($warehouse) > 0) ? $warehouse : \App\Models\Warehouse::all());
                                @endphp
                                @if(isset($whList) && count($whList) > 0)
                                    <optgroup label="🏭 Warehouses">
                                        @foreach($whList as $wh)
                                            <option value="warehouse_{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-center mb-0" id="partialSaleTable">
                            <thead class="table-dark small">
                                <tr>
                                    <th style="width: 35%; text-align: left;" class="ps-2">Product</th>
                                    <th style="width: 15%;">Sale Qty</th>
                                    <th style="width: 15%;">Wh Stock</th>
                                    <th style="width: 20%;">Dispatch Qty Now</th>
                                    <th style="width: 15%;">Remaining Qty</th>
                                </tr>
                            </thead>
                            <tbody id="partialSaleTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-secondary btn-sm rounded-2 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning btn-sm rounded-2 fw-bold text-dark px-4 shadow-sm" id="btnConfirmPartialSale" style="background:#f59e0b !important; border-color:#d97706 !important;">
                        <i class="fas fa-check-circle me-1"></i> Confirm & Issue Partial DC
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.RECEIPT_ACCOUNTS = @json($accounts);
        window.AVAILABLE_PRODUCTS = @json($products ?? []);
        window.BRANCHES = @json($branches ?? []);
        window.BRANCH_COUNTERS = @json($branchCounters ?? []);
        window.ALL_WAREHOUSES = @json($allWarehouses ?? $warehouse ?? \App\Models\Warehouse::all());
        window.WAREHOUSE_STOCKS = @json($warehouseStocks ?? []);
        window.IS_ADMIN = {{ Auth::user() && Auth::user()->hasRole('super admin') ? 'true' : 'false' }};
        window.saleSettings = {
            show_gst: {{ (isset($saleSettings) && $saleSettings->show_gst) ? 'true' : 'false' }},
            show_line_discount: {{ (!isset($saleSettings) || $saleSettings->show_line_discount) ? 'true' : 'false' }},
            show_overall_discount: {{ (isset($saleSettings) && $saleSettings->show_overall_discount) ? 'true' : 'false' }},
            show_watt: {{ (isset($saleSettings) && $saleSettings->show_watt) ? 'true' : 'false' }},
            warehouse_mode: '{{ $saleSettings->warehouse_mode ?? 'global' }}'
        };
        window.USER_BRANCH_ID = {{ Auth::user()->branch_id ?? 1 }};
        window.IS_EDIT_MODE = {{ isset($isEditMode) && $isEditMode ? 'true' : 'false' }};
        window.EDIT_SALE = @json($sale ?? null);
        window.EDIT_SALE_ITEMS = @json($saleItems ?? []);
        window.EDIT_RECEIPTS = @json($receipts ?? []);
        window.UPDATE_URL = "{{ isset($sale) ? route('sales.update', $sale->id) : '' }}";
        window.BOOKING_DATA = @json($booking ?? null);
        window.BOOKING_CUSTOMER = @json($booking_customer ?? null);
        window.BOOKING_ITEMS = @json($bookingItems ?? []);

        function showAlert(type, msg) {
            Swal.fire({
                icon: type === 'error' ? 'error' : (type === 'success' ? 'success' : 'info'),
                title: type.toUpperCase(),
                text: msg,
                timer: type === 'success' ? 2000 : undefined,
                showConfirmButton: type !== 'success'
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            // --- Global Helpers ---
            function toNum(v) {
                return parseFloat(v || 0) || 0;
            }

            function updateRowIndexNumbers() {
                $('#salesTableBody tr').each(function(index) {
                    $(this).find('.row-index').text(index + 1);
                });
                $('#itemsRowCount').text($('#salesTableBody tr').length);
            }

            window.PRODUCT_LOCATION_STOCKS = {};

            function getProductLocationStock(prodId, locKey) {
                if (!prodId) return null;
                const pIdStr = String(prodId);
                if (window.PRODUCT_LOCATION_STOCKS[pIdStr] && window.PRODUCT_LOCATION_STOCKS[pIdStr][locKey] !== undefined) {
                    return window.PRODUCT_LOCATION_STOCKS[pIdStr][locKey];
                }
                if (window.WAREHOUSE_STOCKS && window.WAREHOUSE_STOCKS.length > 0) {
                    let total = 0;
                    let pIdNum = parseInt(prodId);
                    if (locKey.startsWith('branch_')) {
                        let bIdNum = parseInt(locKey.replace('branch_', ''));
                        window.WAREHOUSE_STOCKS.forEach(function(ws) {
                            if (parseInt(ws.product_id) === pIdNum && parseInt(ws.branch_id) === bIdNum && (!ws.warehouse_id || parseInt(ws.warehouse_id) === 0)) {
                                total += parseFloat(ws.quantity || 0);
                            }
                        });
                    } else if (locKey.startsWith('warehouse_')) {
                        let wIdNum = parseInt(locKey.replace('warehouse_', ''));
                        window.WAREHOUSE_STOCKS.forEach(function(ws) {
                            if (parseInt(ws.product_id) === pIdNum && parseInt(ws.warehouse_id) === wIdNum) {
                                total += parseFloat(ws.quantity || 0);
                            }
                        });
                    }
                    return total;
                }
                return 0;
            }

            function buildWarehouseOptionsHtml(selectedVal, prodId) {
                let html = '';
                let sVal = String(selectedVal || '');
                if (window.BRANCHES && window.BRANCHES.length > 0) {
                    html += '<optgroup label="🏬 Shops / Branches">';
                    window.BRANCHES.forEach(function(b) {
                        const val = 'branch_' + b.id;
                        const isSel = (sVal === val || sVal === String(b.id)) ? 'selected' : '';
                        let stockBadge = '';
                        if (prodId) {
                            let st = getProductLocationStock(prodId, val);
                            stockBadge = ` — [Stock: ${st !== null ? st : 0}]`;
                        }
                        html += `<option value="${val}" ${isSel}>${b.name} (Shop)${stockBadge}</option>`;
                    });
                    html += '</optgroup>';
                }
                if (window.ALL_WAREHOUSES && window.ALL_WAREHOUSES.length > 0) {
                    html += '<optgroup label="🏭 Warehouses">';
                    window.ALL_WAREHOUSES.forEach(function(w) {
                        const val = 'warehouse_' + w.id;
                        const isSel = (sVal === val || sVal === String(w.id)) ? 'selected' : '';
                        let stockBadge = '';
                        if (prodId) {
                            let st = getProductLocationStock(prodId, val);
                            stockBadge = ` — [Stock: ${st !== null ? st : 0}]`;
                        }
                        html += `<option value="${val}" ${isSel}>${w.warehouse_name || w.name}${stockBadge}</option>`;
                    });
                    html += '</optgroup>';
                }
                return html;
            }

            // --- Add Row Logic (Model & Product Only - Size & Color Removed) ---
            function addNewRow() {
                const rowCount = $('#salesTableBody tr').length + 1;
                const currentLoc = $('#globalWarehouseSelect').val() || '';
                const isProductWise = (window.saleSettings.warehouse_mode === 'product_wise');
                const isShowWatt = !!window.saleSettings.show_watt;
                const isShowLineDisc = !!window.saleSettings.show_line_discount;

                let whCellHtml = '';
                let stockCellHtml = '';
                if (isProductWise) {
                    whCellHtml = `
                        <td class="col-warehouse">
                            <select class="form-select warehouse warehouse-select shadow-none" name="warehouse_id[]" style="width: 100%; height: 26px; font-size: 0.78rem; padding: 2px 4px; font-weight: 600;">
                                ${buildWarehouseOptionsHtml(currentLoc)}
                            </select>
                        </td>
                    `;
                    stockCellHtml = `
                        <td class="col-stock">
                            <input type="text" class="form-control stock text-center input-readonly" readonly tabindex="-1" value="0" title="Warehouse Stock">
                        </td>
                    `;
                } else {
                    stockCellHtml = `
                        <td class="col-stock">
                            <input type="text" class="form-control stock text-center input-readonly" readonly tabindex="-1" value="0" title="Overall Stock">
                            <input type="hidden" class="warehouse" name="warehouse_id[]" value="${currentLoc}">
                        </td>
                    `;
                }

                let wattCellHtml = '';
                if (isShowWatt) {
                    wattCellHtml = `
                        <td class="col-watt">
                            <input type="number" step="any" class="form-control item-watt text-center" name="watt[]" placeholder="0" min="0" value="0" style="width: 100%; height: 26px; font-size: 0.85rem; padding: 2px 4px; font-weight: 700;">
                        </td>
                    `;
                } else {
                    wattCellHtml = `<td class="d-none"><input type="hidden" class="item-watt" name="watt[]" value="0"></td>`;
                }

                let discCellHtml = '';
                if (isShowLineDisc) {
                    discCellHtml = `
                        <td class="col-disc">
                            <div class="discount-wrapper">
                                <input type="number" class="form-control discount-value text-end" name="discount_percentage[]" placeholder="0">
                                <input type="hidden" class="discount-type-field" name="discount_type[]" value="percent">
                                <button type="button" class="btn btn-outline-secondary discount-toggle" data-type="percent" tabindex="-1">%</button>
                            </div>
                            <input type="hidden" class="discount-amount" name="discount_amount[]" value="0">
                        </td>
                    `;
                } else {
                    discCellHtml = `
                        <td class="d-none">
                            <input type="hidden" class="discount-value" name="discount_percentage[]" value="0">
                            <input type="hidden" class="discount-type-field" name="discount_type[]" value="percent">
                            <input type="hidden" class="discount-amount" name="discount_amount[]" value="0">
                        </td>
                    `;
                }

                const rowHtml = `
                    <tr>
                        <td class="text-center fw-bold text-muted row-index" style="vertical-align:middle; font-size:0.75rem;">${rowCount}</td>
                        <td class="col-product">
                            <select class="form-select product product-select" name="product_id[]" style="width:100%">
                                <option value=""></option>
                            </select>
                            <input type="hidden" class="product-id-hidden" name="product_id_hidden[]">
                            <input type="hidden" class="variant-data-hidden" name="color[]">
                            <input type="hidden" class="item-code-display">
                        </td>
                        ${whCellHtml}
                        ${stockCellHtml}
                        <td style="width:95px;min-width:95px;" class="col-qty-wrapper">
                            <input type="number" step="any" class="form-control carton-qty sales-qty text-start" name="carton_qty[]" placeholder="0" min="0" value="1" style="width: 100%; height: 26px; font-size: 0.85rem; padding-left: 6px;">
                            <input type="hidden" name="sales_qty[]" class="sales-qty-mirror" value="1">
                        </td>
                        <td class="col-pieces">
                            <input type="text" class="form-control total-pieces text-end input-readonly" name="total_pieces[]" readonly placeholder="0" tabindex="-1" value="1">
                            <input type="hidden" class="sales-qty-hidden" name="qty[]" value="1">
                        </td>
                        ${wattCellHtml}
                        <td class="col-price-p">
                            <input type="text" class="form-control visible-price retail-price text-end" name="retail_price[]" placeholder="0" style="width: 100%;">
                            <input type="hidden" class="price-per-piece" name="price_per_piece[]">
                        </td>
                        ${discCellHtml}
                        <td class="col-amount">
                            <input type="text" class="form-control sales-amount text-end input-readonly" name="sales_amount[]" value="0.00" readonly tabindex="-1">
                            <input type="hidden" class="gross-amount">
                        </td>
                        <td class="col-action text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger del-row" tabindex="-1">&times;</button>
                        </td>
                    </tr>
                `;
                $('#salesTableBody').append(rowHtml);
                const $addedRow = $('#salesTableBody tr').last();
                initProductSelect2($addedRow.find('.product-select'));
                updateRowIndexNumbers();
            }

            $('#btnAdd').on('click', function() {
                addNewRow();
                updateGrandTotals();
            });

            // Initial row if empty (only for new sale mode)
            if (!window.IS_EDIT_MODE && $('#salesTableBody tr').length === 0) {
                addNewRow();
            }

            // --- Helper: Fetch Live Stock for a Row ---
            function fetchRowStock($row, prodId) {
                if (!prodId) return;
                let loc = $row.find('.warehouse-select, .warehouse').val() || $('#globalWarehouseSelect').val() || '';
                let branchId = $('#branch_id').val() || '';
                $.get("{{ route('sale.check.stock') }}", { product_id: prodId, location_id: loc, branch_id: branchId }, function(res) {
                    if (res) {
                        if (res.location_breakdown) {
                            const pIdStr = String(prodId);
                            if (!window.PRODUCT_LOCATION_STOCKS[pIdStr]) {
                                window.PRODUCT_LOCATION_STOCKS[pIdStr] = {};
                            }
                            Object.assign(window.PRODUCT_LOCATION_STOCKS[pIdStr], res.location_breakdown);
                        }

                        // Update warehouse options with fresh live stock labels for this row
                        const $whSelect = $row.find('.warehouse-select');
                        if ($whSelect.length) {
                            const currentWh = $whSelect.val() || loc;
                            $whSelect.html(buildWarehouseOptionsHtml(currentWh, prodId));
                        }

                        let isProductWise = (window.saleSettings.warehouse_mode === 'product_wise');
                        let displayStock = isProductWise ? (res.location_stock !== undefined ? res.location_stock : (res.stock || res.overall_stock || 0)) : ((res.overall_stock !== undefined) ? res.overall_stock : res.available);
                        $row.find('.stock').val(displayStock);
                        $row.find('.stock').attr('title', `Overall Stock: ${res.overall_stock} | Location Stock: ${res.location_stock}`);
                    }
                });
            }

            // --- Location Dropdown Change Event ---
            $('#globalWarehouseSelect').on('change', function() {
                let loc = $(this).val();
                if (window.saleSettings.warehouse_mode !== 'product_wise') {
                    $('.warehouse').val(loc);
                    $('#salesTableBody tr').each(function() {
                        let pId = $(this).find('.product-select').val();
                        if (pId) {
                            fetchRowStock($(this), pId);
                        }
                    });
                }
            });

            // --- Product-wise Warehouse Change Event ---
            $(document).on('change', '.warehouse-select', function() {
                const $row = $(this).closest('tr');
                const pId = $row.find('.product-select').val();
                if (pId) {
                    fetchRowStock($row, pId);
                }
            });

            // --- Product Select2 Init ---
            function initProductSelect2($select) {
                $select.select2({
                    placeholder: 'Select Product / Model...',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        transport: function(params, success, failure) {
                            let term = (params.data && (params.data.term || params.data.q)) || '';
                            let page = (params.data && (params.data.page || 1)) || 1;
                            let branchId = $('#branch_id').val() || $('[name="branch_id"]').val() || '';
                            let ajaxUrl = term && term.length > 0 ? '/search_products' : '/search-products-sale';
                            $.ajax({
                                url: ajaxUrl,
                                data: { q: term, page: page, branch_id: branchId },
                                dataType: 'json',
                                success: function(data) { success(data); },
                                error: failure
                            });
                        },
                        delay: 250,
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            let results = [];
                            const mapProduct = function(p) {
                                let stockVal = (p.stock !== undefined && p.stock !== null) ? p.stock : (p.overall_stock || 0);
                                return {
                                    id: p.id,
                                    text: (p.item_code ? p.item_code + ' — ' : '') + (p.item_name || p.name || '') + ' [Stock: ' + stockVal + ']',
                                    product: p
                                };
                            };
                            if (Array.isArray(data)) {
                                results = data.map(mapProduct);
                                return { results: results, pagination: { more: false } };
                            }
                            results = (data.products || []).map(mapProduct);
                            return { results: results, pagination: { more: !!data.has_more } };
                        },
                        cache: true
                    }
                });
            }

            // On Product Select
            $(document).on('change', '.product-select, .product', function() {
                const $row = $(this).closest('tr');
                const prodId = $(this).val();
                if (!prodId) return;

                const data = $(this).select2('data')[0];
                const prod = data ? data.product : null;

                if (prod) {
                    let initialStock = (prod.stock !== undefined && prod.stock !== null) ? prod.stock : (prod.overall_stock || 0);
                    $row.find('.stock').val(initialStock);
                    $row.find('.retail-price').val(prod.retail_price || prod.price || prod.sale_price_per_box || prod.purchase_price_per_piece || 0);
                    $row.find('.total-pieces').val($row.find('.sales-qty').val() || 1);

                    // Auto-detect Watt if watt column is enabled
                    if (window.saleSettings && window.saleSettings.show_watt) {
                        let pName = prod.item_name || prod.name || '';
                        let detectedWatt = 0;
                        let match = pName.match(/\b(\d{3,4})\s*(?:watts?|w)\b/i);
                        if (match) {
                            detectedWatt = parseFloat(match[1]);
                        } else {
                            let match2 = pName.match(/\b(645|585|590|575|580|550|540|530|700|650)\b/i);
                            if (match2) {
                                detectedWatt = parseFloat(match2[1]);
                            }
                        }
                        if (detectedWatt > 0) {
                            $row.find('.item-watt').val(detectedWatt);
                        }
                    }

                    const $whSel = $row.find('.warehouse-select');
                    if ($whSel.length) {
                        $whSel.html(buildWarehouseOptionsHtml($whSel.val(), prodId));
                    }

                    fetchRowStock($row, prodId);
                    computeRow($row);
                    updateGrandTotals();
                }
            });

            // --- Row Calculation Logic ---
            function computeRow($row) {
                const price = toNum($row.find('.retail-price').val());
                const qty = toNum($row.find('.sales-qty').val());
                const watt = (window.saleSettings && window.saleSettings.show_watt) ? toNum($row.find('.item-watt').val()) : 0;
                let discVal = toNum($row.find('.discount-value').val());
                const discType = $row.find('.discount-toggle').data('type') || $row.find('.discount-type-field').val() || 'percent';

                let gross = (watt > 0) ? (price * watt * qty) : (price * qty);
                let discAmt = 0;

                if (discVal > 0) {
                    if (discType === 'percent') {
                        discVal = Math.min(discVal, 100);
                        discAmt = (gross * discVal) / 100;
                    } else {
                        discAmt = discVal * qty;
                    }
                }

                let net = Math.max(0, gross - discAmt);

                $row.find('.total-pieces').val(qty);
                $row.find('.sales-qty-mirror').val(qty);
                $row.find('.discount-amount').val(discAmt.toFixed(2));
                $row.find('.sales-amount').val(net.toFixed(2));
            }

            // --- Update Grand Totals & Summary Strip ---
            function updateGrandTotals() {
                let tGross = 0;
                let tLineDisc = 0;
                let tNet = 0;

                $('#salesTableBody tr').each(function() {
                    const $r = $(this);
                    const price = toNum($r.find('.retail-price').val());
                    const qty = toNum($r.find('.sales-qty').val());
                    const watt = (window.saleSettings && window.saleSettings.show_watt) ? toNum($r.find('.item-watt').val()) : 0;
                    const discAmt = toNum($r.find('.discount-amount').val());
                    const gross = (watt > 0) ? (price * watt * qty) : (price * qty);
                    const net = Math.max(0, gross - discAmt);

                    tGross += gross;
                    tLineDisc += discAmt;
                    tNet += net;
                });

                let overallDisc = toNum($('#walkinDiscountRs').val());
                let finalNet = Math.max(0, tNet - overallDisc);

                let receipts = 0;
                $('.rv-amount').each(function() {
                    receipts += toNum($(this).val());
                });

                let isWalkin = $('#walkinToggle').is(':checked');
                let prevBal = isWalkin ? 0 : toNum($('#previousBalance').val());
                let totalPayable = isWalkin ? finalNet : (finalNet + prevBal);
                let remainingDue = Math.max(0, totalPayable - receipts);
                let changeVal = receipts - finalNet;

                // Executive Summary Card Update
                $('#tGross').text(tGross.toFixed(2));
                $('#tLineDisc').text(tLineDisc.toFixed(2));
                $('#tSub').text(finalNet.toFixed(2));
                $('#displayPrevBalance').text(prevBal.toFixed(2));
                $('#displayTotalPayable').text(totalPayable.toFixed(2));
                $('#receiptsTotalBadge').text(receipts.toFixed(2));
                $('#receiptsTotal').text(receipts.toFixed(2));
                $('#tPrev').text(prevBal.toFixed(2));
                $('#tPayable').text(totalPayable.toFixed(2));
                $('#badgeCustomerPrevBal').text('Rs ' + prevBal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                if (isWalkin) {
                    $('#rowPrevBalance').hide();
                    $('#rowTotalPayable').show();
                    if (changeVal >= 0) {
                        $('#lblChangeOrDue').text('Change');
                        $('#walkinChange').text(changeVal.toFixed(2)).removeClass('text-danger').addClass('text-success');
                        $('#bottomChangeVal').text(changeVal.toFixed(2));
                    } else {
                        $('#lblChangeOrDue').text('Balance Due');
                        $('#walkinChange').text(Math.abs(changeVal).toFixed(2)).removeClass('text-success').addClass('text-danger');
                        $('#bottomChangeVal').text(Math.abs(changeVal).toFixed(2));
                    }
                } else {
                    $('#rowPrevBalance').show();
                    $('#rowTotalPayable').show();
                    $('#lblChangeOrDue').text('Balance Due');
                    $('#walkinChange').text(remainingDue.toFixed(2)).removeClass('text-success').addClass('text-danger');
                    $('#bottomChangeVal').text(remainingDue.toFixed(2));
                }

                // Table Foot
                $('#totalAmount').text(finalNet.toFixed(2));

                // Bottom Summary Strip Update
                $('#walkinNetTotal').text(finalNet.toFixed(2));
                $('#bottomPaymentsTotal').text(receipts.toFixed(2));

                // Form hidden mirrors
                $('#subTotal1').val(tGross.toFixed(2));
                $('#subTotal2').val(finalNet.toFixed(2));
                $('#discountAmount').val(overallDisc.toFixed(2));
                $('#totalBalance').val(totalPayable.toFixed(2));
            }

            // Events on grid inputs
            $(document).on('input', '.sales-qty, .carton-qty, .retail-price, .discount-value, .item-watt', function() {
                computeRow($(this).closest('tr'));
                updateGrandTotals();
            });

            $(document).on('input', '#walkinDiscountRs', function() {
                updateGrandTotals();
            });

            // Discount toggle button handler (% vs PKR)
            $(document).on('click', '.discount-toggle', function() {
                const $btn = $(this);
                const currentType = $btn.data('type');
                if (currentType === 'percent') {
                    $btn.data('type', 'pkr').text('Rs');
                    $btn.closest('.discount-wrapper').find('.discount-type-field').val('pkr');
                } else {
                    $btn.data('type', 'percent').text('%');
                    $btn.closest('.discount-wrapper').find('.discount-type-field').val('percent');
                }
                computeRow($btn.closest('tr'));
                updateGrandTotals();
            });

            // Delete row handler
            $(document).on('click', '.del-row', function() {
                if ($('#salesTableBody tr').length > 1) {
                    $(this).closest('tr').remove();
                    updateRowIndexNumbers();
                    updateGrandTotals();
                }
            });

            // --- Payment Accounts ---
            $('#btnAddRV').on('click', function() {
                let optionsHtml = '';
                window.RECEIPT_ACCOUNTS.forEach(function(acc) {
                    optionsHtml += `<option value="${acc.id}">${acc.title}</option>`;
                });
                const $row = $(`
                    <div class="d-flex gap-1 align-items-center mb-2 rv-row">
                        <select class="form-select form-select-sm rv-account bg-light fw-bold" name="receipt_account_id[]" style="font-size:0.75rem;">
                            ${optionsHtml}
                        </select>
                        <input type="number" step="0.01" class="form-control form-control-sm text-end rv-amount fw-bold" name="receipt_amount[]" placeholder="0.00" style="width: 110px; font-size:0.75rem;">
                        <button type="button" class="btn btn-sm btn-outline-danger btnRemRV py-0 px-1">&times;</button>
                    </div>
                `);
                $('#rvWrapper').append($row);
            });

            $(document).on('click', '.btnRemRV', function() {
                $(this).closest('.rv-row').remove();
                updateGrandTotals();
            });

            $(document).on('input', '.rv-amount', function() {
                updateGrandTotals();
            });

            // --- Walk-in Customer Toggle ---
            $('#btnWalkinToggle').on('click', function(e) {
                e.preventDefault();
                let isCurrentlyWalkin = $('#walkinToggle').is(':checked');
                $('#walkinToggle').prop('checked', !isCurrentlyWalkin).trigger('change');
            });

            window.applyCustomerData = function(d) {
                if (!d) return;
                let prev = d.previous_balance !== undefined ? d.previous_balance : (d.closing_balance !== undefined ? d.closing_balance : (d.opening_balance || 0));
                let prevVal = parseFloat(prev || 0);

                $('#previousBalance').val(prevVal.toFixed(2));
                $('#badgeCustomerPrevBal').text('Rs ' + prevVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#displayPrevBalance').text(prevVal.toFixed(2));
                $('#address').val(d.address || '');
                $('#tel').val(d.mobile || '');
                $('#customer_id').val(d.id || '');
                $('#customer').val(d.id || '');
                updateGrandTotals();
            };

            $('#walkinToggle').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#walkinNameInput').removeClass('d-none').show().focus();
                    $('#customerSelectBoxContainer').addClass('d-none').hide();
                    $('#customerPrevBalWrapper').addClass('d-none').hide();
                    $('#btnWalkinToggle').removeClass('btn-outline-primary').addClass('btn-primary active');
                    $('#typewalking').prop('checked', true).trigger('change');
                    $('#customer_id').val('');
                    $('#customer').val('');
                    $('#address').val('');
                    $('#tel').val('');
                    $('#previousBalance').val('0.00');
                    $('#badgeCustomerPrevBal').text('Rs 0.00');
                    $('#displayPrevBalance').text('0.00');
                    updateGrandTotals();
                } else {
                    $('#walkinNameInput').addClass('d-none').hide();
                    $('#customerSelectBoxContainer').removeClass('d-none').show();
                    $('#customerPrevBalWrapper').removeClass('d-none').show();
                    $('#btnWalkinToggle').removeClass('btn-primary active').addClass('btn-outline-primary');
                    $('#typeCustomers').prop('checked', true).trigger('change');
                    let currentCid = $('#customerSelect').val();
                    if (currentCid) {
                        $('#customerSelect').trigger('change');
                    } else {
                        updateGrandTotals();
                    }
                }
            });

            // Default to Customer Select mode on page load (new sale only)
            if (!window.IS_EDIT_MODE) {
                $('#walkinToggle').prop('checked', false).trigger('change');
            }

            // --- Dynamic Branch Selection Change Handler ---
            $(document).on('change', '#branch_id', function() {
                let selectedBranchId = $(this).val();
                if (!selectedBranchId) return;

                // 1. Update Invoice Counter
                if (!window.IS_EDIT_MODE && window.BRANCH_COUNTERS && window.BRANCH_COUNTERS[selectedBranchId] !== undefined) {
                    let nextCounter = parseInt(window.BRANCH_COUNTERS[selectedBranchId]) + 1;
                    let formattedNo = 'INV-' + String(nextCounter).padStart(4, '0');
                    $('#inputInvoiceNo').val(formattedNo);
                    $('#headerInvoiceNoDisplay').text(formattedNo);
                }

                // 2. Fetch Salesmen for Selected Branch
                $.ajax({
                    url: "/sale/get-branch-salesmen/" + selectedBranchId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(salesmen) {
                        let $smSelect = $('#salesmanSelect');
                        let currentSmId = $smSelect.val();
                        $smSelect.empty().append('<option value="">Select Salesman</option>');
                        if (Array.isArray(salesmen) && salesmen.length > 0) {
                            salesmen.forEach(function(sm) {
                                let name = sm.name || sm.salesman_name || sm.full_name || ('Salesman #' + sm.id);
                                let isSel = (currentSmId && currentSmId == sm.id) ? 'selected' : '';
                                $smSelect.append(`<option value="${sm.id}" ${isSel}>${name}</option>`);
                            });
                        }
                    },
                    error: function(err) {
                        console.error('Failed to fetch branch salesmen:', err);
                    }
                });

                // 3. Reset customer selection upon branch change & reload branch customers
                if (!window.IS_EDIT_MODE) {
                    $('#customerSelect').val(null).trigger('change');
                    $('#customer_id').val('');
                    $('#customer').val('');
                    $('#address').val('');
                    $('#tel').val('');
                    $('#previousBalance').val('0.00');
                    $('#badgeCustomerPrevBal').text('Rs 0.00');
                    $('#displayPrevBalance').text('0.00');
                    updateGrandTotals();

                    $.get('/sale/customers', { branch_id: selectedBranchId }, function(data) {
                        let $cs = $('#customerSelect');
                        $cs.empty().append('<option value="">Select or search customer...</option>');
                        if (Array.isArray(data)) {
                            data.forEach(function(c) {
                                let prev = c.previous_balance !== undefined ? c.previous_balance : (c.closing_balance !== undefined ? c.closing_balance : (c.opening_balance || 0));
                                let opt = $('<option></option>')
                                    .attr('value', c.id)
                                    .attr('data-mobile', c.mobile || '')
                                    .attr('data-address', c.address || '')
                                    .attr('data-prev-balance', prev)
                                    .text((c.customer_id ? c.customer_id + ' — ' : '') + c.customer_name);
                                $cs.append(opt);
                            });
                        }
                    });
                }
            });

            // --- Customer Select2 Search & Change ---
            $('#customerSelect').select2({
                placeholder: 'Search Customer...',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '/sale/customers',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        let activeBranch = $('#branch_id').val() || $('[name="branch_id"]').val() || '';
                        return { search: params.term || '', branch_id: activeBranch };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(c) {
                                return {
                                    id: c.id,
                                    text: (c.customer_id || '') + ' — ' + c.customer_name,
                                    customer: c
                                };
                            })
                        };
                    }
                }
            });

            $('#customerSelect').on('select2:select change', function(e) {
                const id = (e.params && e.params.data && e.params.data.id) ? e.params.data.id : $(this).val();
                if (!id) {
                    $('#previousBalance').val('0.00');
                    $('#badgeCustomerPrevBal').text('Rs 0.00');
                    $('#displayPrevBalance').text('0.00');
                    $('#customer_id').val('');
                    $('#customer').val('');
                    $('#address').val('');
                    $('#tel').val('');
                    updateGrandTotals();
                    return;
                }

                // 1. If customer data is directly attached in e.params.data, use it right away!
                if (e.params && e.params.data && e.params.data.customer) {
                    window.applyCustomerData(e.params.data.customer);
                } else {
                    // 2. Check if option in DOM has data-prev-balance
                    let $opt = $(this).find(`option[value="${id}"]`);
                    let optPrev = $opt.attr('data-prev-balance') || $opt.data('prev-balance') || $opt.data('prevBalance');
                    if (optPrev !== undefined && optPrev !== null && optPrev !== '') {
                        window.applyCustomerData({
                            id: id,
                            mobile: $opt.attr('data-mobile') || '',
                            address: $opt.attr('data-address') || '',
                            previous_balance: optPrev
                        });
                    }
                }

                // 3. Fetch latest live balance from server via relative URL (100% immune to CORS)
                $.ajax({
                    url: '/sale/customers/' + id,
                    type: 'GET',
                    dataType: 'json',
                    success: function(d) {
                        if (d) {
                            d.id = d.id || id;
                            window.applyCustomerData(d);
                        }
                    },
                    error: function(err) {
                        console.error('Error fetching customer balance:', err);
                    }
                });
            });

            // Smooth focus transition to table on close (if customer selected)
            $('#customerSelect').on('select2:close', function() {
                let cid = $(this).val();
                if (cid) {
                    setTimeout(() => {
                        let $firstProd = $('#salesTableBody tr:first .product-select');
                        if ($firstProd.length) {
                            if ($firstProd.val()) {
                                $('#salesTableBody tr:first .sales-qty').focus().select();
                            } else {
                                $firstProd.select2('open');
                            }
                        }
                    }, 120);
                }
            });

            // --- Form Save & Post Logic ---
            function ensureSaved() {
                return new Promise((resolve, reject) => {
                    if ($('#displayDateInput').val()) {
                        $('#Invoice_date').val($('#displayDateInput').val());
                    }
                    let formData = $('#saleForm').serialize();
                    let isEdit = window.IS_EDIT_MODE && window.UPDATE_URL;
                    let targetUrl = isEdit ? window.UPDATE_URL : "{{ route('sale.ajax.save') }}";
                    let targetMethod = isEdit ? 'POST' : 'POST';

                    if (isEdit) {
                        formData += '&_method=PUT';
                    }

                    $.ajax({
                        url: targetUrl,
                        type: targetMethod,
                        data: formData,
                        success: function(res) {
                            let bId = res.booking_id || res.id || res.sale_id;
                            if (bId) {
                                if (!isEdit) {
                                    $('#booking_id').val(bId);
                                }
                                resolve(bId);
                            } else {
                                showAlert('error', 'Response ID not returned from server.');
                                reject(new Error('Response ID not returned'));
                            }
                        },
                        error: function(err) {
                            let msg = isEdit ? 'Failed to update sale.' : 'Failed to save sale form.';
                            if (err.responseJSON && err.responseJSON.message) {
                                msg = err.responseJSON.message;
                            } else if (err.responseJSON && err.responseJSON.error) {
                                msg = err.responseJSON.error;
                            }
                            showAlert('error', msg);
                            reject(err);
                        }
                    });
                });
            }

            // Process Sale / Save & Complete Button (Posts transaction, deducts stock, creates INV invoice)
            $('#btnPosted, #btnHeaderSaveSale, #btnSaveAndComplete, #btnSaveAndComplete2').on('click', function() {
                // Validation
                let hasProducts = false;
                $('#salesTableBody tr').each(function() {
                    let pId = $(this).find('.product-select').val();
                    let qty = toNum($(this).find('.sales-qty').val());
                    if (pId && qty > 0) hasProducts = true;
                });
                if (!hasProducts) {
                    showAlert('error', 'Please add at least one product with quantity > 0.');
                    return;
                }

                const $activeBtns = $('#btnPosted, #btnHeaderSaveSale, #btnSaveAndComplete, #btnSaveAndComplete2');
                $activeBtns.prop('disabled', true);

                if (window.IS_EDIT_MODE) {
                    $('#action').val('sale');
                    ensureSaved().then(id => {
                        showAlert('success', 'Sale updated successfully!');
                        setTimeout(() => {
                            window.location.href = "{{ route('sale.index') }}";
                        }, 1200);
                    }).catch(err => {
                        $activeBtns.prop('disabled', false);
                    });
                    return;
                }

                // If New Sale / POS: Direct sale without creating a booking!
                $('#action').val('sale');
                
                // Show loading state
                const origCompleteText1 = $('#btnSaveAndComplete').html();
                const origCompleteText2 = $('#btnSaveAndComplete2').html();
                $('#btnSaveAndComplete, #btnSaveAndComplete2').html('<i class="fas fa-spinner fa-spin me-2"></i>Posting Sale...');

                if ($('#displayDateInput').val()) {
                    $('#Invoice_date').val($('#displayDateInput').val());
                }

                let postData = $('#saleForm').serialize();

                $.ajax({
                    url: "{{ route('sale.ajax.post') }}",
                    type: 'POST',
                    data: postData,
                    success: function(res) {
                        if (res && res.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'SALE POSTED!',
                                text: 'Sale completed & posted successfully. Stock deducted!',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(() => {
                                if (res.invoice_url) {
                                    window.open(res.invoice_url, '_blank');
                                }
                                window.location.href = "{{ route('sale.index') }}";
                            });
                        } else {
                            $activeBtns.prop('disabled', false);
                            $('#btnSaveAndComplete').html(origCompleteText1);
                            $('#btnSaveAndComplete2').html(origCompleteText2);
                            showAlert('error', (res && res.msg) ? res.msg : 'Failed to post sale.');
                        }
                    },
                    error: function(xhr) {
                        $activeBtns.prop('disabled', false);
                        $('#btnSaveAndComplete').html(origCompleteText1);
                        $('#btnSaveAndComplete2').html(origCompleteText2);
                        let msg = 'Server error while posting sale.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            msg = xhr.responseJSON.error;
                        }
                        showAlert('error', msg);
                    }
                });
            });

            // ==============================================================
            // 🚀 COMPREHENSIVE ENTER KEY NAVIGATION & AUTO ROW CREATION
            // ==============================================================

            // 1. Prevent default form submit on Enter key
            $('#saleForm').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    if (!$(e.target).is('textarea, button, [type="submit"], [type="button"]')) {
                        e.preventDefault();
                    }
                }
            });

            // 2. Top Header Fields Enter Navigation Chain
            $('#branch_id').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    $('#globalWarehouseSelect').focus();
                }
            });

            $('#globalWarehouseSelect').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    $('#displayDateInput').focus();
                }
            });

            $('#displayDateInput').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    $('#salesmanSelect').focus();
                }
            });

            $('#salesmanSelect').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    const $crDays = $('[name="credit_days"]');
                    if ($crDays.length && $crDays.is(':visible')) {
                        $crDays.focus().select();
                    } else {
                        $('#manualInvoice').focus().select();
                    }
                }
            });

            $('[name="credit_days"]').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    $('#manualInvoice').focus().select();
                }
            });

            $('#manualInvoice').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    $('#remarks').focus().select();
                }
            });

            $('#remarks').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    if ($('#walkinToggle').is(':checked')) {
                        $('#walkinNameInput').focus().select();
                    } else {
                        $('#customerSelect').select2('open');
                    }
                }
            });

            $(document).on('input', '#manualInvoice', function() {
                $('#Invoice_main').val($(this).val());
            });

            $(document).on('input', '#remarks', function() {
                $('#reference').val($(this).val());
            });

            $('#walkinNameInput').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    let $firstProd = $('#salesTableBody tr:first .product-select');
                    if ($firstProd.length) {
                        if ($firstProd.val()) {
                            $('#salesTableBody tr:first .sales-qty').focus().select();
                        } else {
                            $firstProd.select2('open');
                        }
                    }
                }
            });

            // 3. Order Items Grid: Navigation & Auto New Row on Enter
            function handleRowAdvance($row) {
                let pId = $row.find('.product-select').val();
                if (!pId) {
                    // Empty row, move to summary / discount / payments
                    $('#walkinDiscountRs').focus().select();
                    return;
                }

                let $nextRow = $row.next('tr');
                if ($nextRow.length) {
                    let nextPId = $nextRow.find('.product-select').val();
                    if (nextPId) {
                        $nextRow.find('.sales-qty').focus().select();
                    } else {
                        $nextRow.find('.product-select').select2('open');
                    }
                } else {
                    // Automatically add a new row on Enter and open product search
                    addNewRow();
                    updateGrandTotals();
                    let $newRow = $('#salesTableBody tr').last();
                    setTimeout(() => {
                        $newRow.find('.product-select').select2('open');
                    }, 80);
                }
            }

            // --- Select2 Autofocus & Global Keyboard Navigation (Up / Down Arrow Buttons & Enter) ---
            $(document).on('select2:open', function() {
                setTimeout(() => {
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

            // Global Enter key navigation for open Select2 dropdown
            $(document).on('keydown', function(e) {
                const $openContainer = $('.select2-container--open');
                if (!$openContainer.length) return;

                const key = e.key;
                const keyCode = e.keyCode;

                // Enter Key in open dropdown -> select highlighted option
                if (key === 'Enter' || keyCode === 13) {
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

            // On Product Selection (Select2) -> jump to QTY
            $(document).on('select2:select', '.product-select', function(e) {
                const $row = $(this).closest('tr');
                setTimeout(() => {
                    $row.find('.sales-qty').focus().select();
                }, 80);
            });

            // When user is on Select2 selection container and hits Enter or ArrowDown/Up
            $(document).on('keydown', '.select2-selection', function(e) {
                const $select = $(this).closest('.select2').prev('select');
                if (e.key === 'Enter' || e.keyCode === 13) {
                    if ($select.hasClass('product-select')) {
                        const $row = $select.closest('tr');
                        if ($select.val()) {
                            e.preventDefault();
                            $row.find('.sales-qty').focus().select();
                        } else {
                            e.preventDefault();
                            $select.select2('open');
                        }
                    }
                } else if (e.key === 'ArrowDown' || e.keyCode === 40) {
                    if (!$('.select2-container--open').length) {
                        e.preventDefault();
                        $select.select2('open');
                    }
                } else if (e.key === 'ArrowUp' || e.keyCode === 38) {
                    if (!$('.select2-container--open').length) {
                        const $row = $(this).closest('tr');
                        const $prevRow = $row.prev('tr');
                        if ($prevRow.length) {
                            e.preventDefault();
                            $prevRow.find('.product-select').select2('open');
                        }
                    }
                }
            });

            // Table Grid Row Navigation with Up / Down Arrow buttons (when dropdown is closed)
            $(document).on('keydown', '.sales-qty, .retail-price, .discount-value, .item-watt, .warehouse-select', function(e) {
                if ($('.select2-container--open').length) return;

                if (e.key === 'ArrowDown' || e.keyCode === 40) {
                    const $row = $(this).closest('tr');
                    const $nextRow = $row.next('tr');
                    if ($nextRow.length) {
                        e.preventDefault();
                        let targetClass = '';
                        if ($(this).hasClass('sales-qty')) targetClass = '.sales-qty';
                        else if ($(this).hasClass('retail-price')) targetClass = '.retail-price';
                        else if ($(this).hasClass('discount-value')) targetClass = '.discount-value';
                        else if ($(this).hasClass('item-watt')) targetClass = '.item-watt';
                        else if ($(this).hasClass('warehouse-select')) targetClass = '.warehouse-select';

                        if (targetClass) {
                            $nextRow.find(targetClass).focus().select();
                        }
                    }
                } else if (e.key === 'ArrowUp' || e.keyCode === 38) {
                    const $row = $(this).closest('tr');
                    const $prevRow = $row.prev('tr');
                    if ($prevRow.length) {
                        e.preventDefault();
                        let targetClass = '';
                        if ($(this).hasClass('sales-qty')) targetClass = '.sales-qty';
                        else if ($(this).hasClass('retail-price')) targetClass = '.retail-price';
                        else if ($(this).hasClass('discount-value')) targetClass = '.discount-value';
                        else if ($(this).hasClass('item-watt')) targetClass = '.item-watt';
                        else if ($(this).hasClass('warehouse-select')) targetClass = '.warehouse-select';

                        if (targetClass) {
                            $prevRow.find(targetClass).focus().select();
                        }
                    }
                }
            });

            // Warehouse select in product-wise mode -> Enter -> Qty
            $(document).on('keydown', '.warehouse-select', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    $(this).closest('tr').find('.sales-qty').focus().select();
                }
            });

            // Qty -> Enter -> Watt (if visible) or Price
            $(document).on('keydown', '.sales-qty', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    const $row = $(this).closest('tr');
                    const $watt = $row.find('.item-watt');
                    if (window.saleSettings && window.saleSettings.show_watt && $watt.length && $watt.is(':visible')) {
                        $watt.focus().select();
                    } else {
                        $row.find('.retail-price').focus().select();
                    }
                }
            });

            // Watt -> Enter -> Price
            $(document).on('keydown', '.item-watt', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    const $row = $(this).closest('tr');
                    $row.find('.retail-price').focus().select();
                }
            });

            // Price -> Enter -> Line Discount (if visible) or Advance Row
            $(document).on('keydown', '.retail-price', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    const $row = $(this).closest('tr');
                    const $disc = $row.find('.discount-value');
                    if ((!window.saleSettings || window.saleSettings.show_line_discount) && $disc.length && $disc.is(':visible')) {
                        $disc.focus().select();
                    } else {
                        handleRowAdvance($row);
                    }
                }
            });

            // Line Discount -> Enter -> Advance Row (Creates new row automatically!)
            $(document).on('keydown', '.discount-value', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    const $row = $(this).closest('tr');
                    handleRowAdvance($row);
                }
            });

            // 4. Summary & Payment Fields Enter Navigation
            $('#walkinDiscountRs').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    let $firstRv = $('#rvWrapper .rv-amount').first();
                    if ($firstRv.length) {
                        $firstRv.focus().select();
                    } else {
                        $('#btnSaveAndComplete').focus();
                    }
                }
            });

            $(document).on('keydown', '.rv-account', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    $(this).closest('.rv-row').find('.rv-amount').focus().select();
                }
            });

            $(document).on('keydown', '.rv-amount', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    let $nextRv = $(this).closest('.rv-row').next('.rv-row');
                    if ($nextRv.length) {
                        $nextRv.find('.rv-amount').focus().select();
                    } else {
                        $('#btnSaveAndComplete').focus();
                    }
                }
            });

            // F9 Keyboard Shortcut for Save & Complete
            $(document).on('keydown', function(e) {
                if (e.key === 'F9' || e.keyCode === 120) {
                    e.preventDefault();
                    $('#btnSaveAndComplete').trigger('click');
                }
            });

            // Booking / Draft Button
            $('#btnSave').on('click', function() {
                $('#action').val('booking');
                ensureSaved().then(id => {
                    showAlert('success', 'Booking draft saved successfully!');
                });
            });

            // Partial Sale Button Logic
            $('#btnPartialSale').on('click', function() {
                let hasProducts = false;
                $('#salesTableBody tr').each(function() {
                    let pId = $(this).find('.product-select').val();
                    let qty = toNum($(this).find('.sales-qty').val());
                    if (pId && qty > 0) hasProducts = true;
                });
                if (!hasProducts) {
                    showAlert('error', 'Please add at least one product with quantity > 0 before proceeding with Partial Sale.');
                    return;
                }

                $('#action').val('booking');
                ensureSaved().then(bookingId => {
                    $('#booking_id').val(bookingId);
                    let html = '';
                    $('#salesTableBody tr').each(function() {
                        let $r = $(this);
                        let pId = $r.find('.product-select').val();
                        let pName = $r.find('.product-select option:selected').text() || 'Product';
                        let qty = toNum($r.find('.sales-qty').val());
                        let stock = toNum($r.find('.stock').val());
                        if (pId && qty > 0) {
                            html += `
                                <tr data-product-id="${pId}">
                                    <td class="text-start ps-2 fw-bold">${pName}</td>
                                    <td class="fw-bold text-primary">${qty}</td>
                                    <td>${stock}</td>
                                    <td style="width: 120px;">
                                        <input type="number" step="any" class="form-control form-control-sm text-center fw-bold dispatch-qty-input" name="dispatch_qty[${pId}]" value="${qty}" max="${qty}" min="0">
                                    </td>
                                    <td class="fw-bold text-secondary remaining-qty-cell">0</td>
                                </tr>
                            `;
                        }
                    });
                    $('#partialSaleTableBody').html(html);
                    $('#partialSaleModal').modal('show');
                }).catch(err => {
                    console.error('ensureSaved failed:', err);
                });
            });

            $(document).on('input', '.dispatch-qty-input', function() {
                let $tr = $(this).closest('tr');
                let saleQty = toNum($tr.find('td:eq(1)').text());
                let dispatchQty = toNum($(this).val());
                let rem = Math.max(0, saleQty - dispatchQty);
                $tr.find('.remaining-qty-cell').text(rem);
            });

            $('#btnConfirmPartialSale').on('click', function() {
                let bookingId = $('#booking_id').val();
                let locationVal = $('#partial_warehouse_id').val();
                if (!locationVal) {
                    showAlert('error', 'Please select a dispatch location/warehouse');
                    return;
                }

                let dispatchData = {
                    booking_id: bookingId,
                    warehouse_id: locationVal,
                    _token: '{{ csrf_token() }}',
                    dispatch_qty: {}
                };

                $('.dispatch-qty-input').each(function() {
                    let pId = $(this).closest('tr').data('product-id');
                    let qty = toNum($(this).val());
                    if (pId) {
                        dispatchData.dispatch_qty[pId] = qty;
                    }
                });

                let btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Issuing DC…');

                $.ajax({
                    url: "{{ Route::has('sale.ajax.post-partial') ? route('sale.ajax.post-partial') : url('/sale/ajax/post-partial') }}",
                    type: 'POST',
                    data: dispatchData,
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Confirm & Issue Partial DC');
                        if (res.success || res.dc_id || res.ok || res.sale_id) {
                            $('#partialSaleModal').modal('hide');
                            showAlert('success', res.msg || 'Partial DC created successfully!');
                            let dcId = res.dc_id || res.sale_id;
                            if (dcId) {
                                window.open('{{ url('/sale/dc') }}/' + dcId + '/thermal', '_blank');
                            }
                            setTimeout(() => {
                                window.location.href = "{{ route('sale.index') }}";
                            }, 1200);
                        } else if (res.error) {
                            showAlert('error', res.error);
                        }
                    },
                    error: function(err) {
                        btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Confirm & Issue Partial DC');
                        let msg = 'Error creating partial delivery.';
                        if (err.responseJSON && err.responseJSON.message) {
                            msg = err.responseJSON.message;
                        }
                        if (err.responseJSON && err.responseJSON.error) {
                            msg = err.responseJSON.error;
                        }
                        showAlert('error', msg);
                    }
                });
            });

            // Print Handlers
            $('#btnPrint').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/invoice', '_blank'));
            });
            $('#btnEstimate').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/invoice?type=estimate', '_blank'));
            });
            $('#btnPrint2').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/recepit', '_blank'));
            });
            $('#btnDcThermal').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/dc-thermal', '_blank'));
            });

            // Save Customer AJAX Modal
            $('#btnSaveAjaxCustomer').on('click', function() {
                let form = $('#ajaxAddCustomerForm');
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }
                let btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                $.ajax({
                    url: '{{ route('customers.store') }}',
                    type: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).text('Save Customer');
                        if (res.success || res.customer) {
                            $('#addCustomerModal').modal('hide');
                            form[0].reset();

                            if ($('#walkinToggle').is(':checked')) {
                                $('#walkinToggle').prop('checked', false).trigger('change');
                            }

                            let c = res.customer;
                            let prev = c.previous_balance !== undefined ? c.previous_balance : (c.closing_balance !== undefined ? c.closing_balance : (c.opening_balance || 0));
                            let newOption = new Option((c.customer_id || '') + ' — ' + c.customer_name, c.id, true, true);
                            $(newOption).attr('data-prev-balance', prev);
                            $(newOption).attr('data-mobile', c.mobile || '');
                            $(newOption).attr('data-address', c.address || '');
                            $('#customerSelect').append(newOption).trigger('change');

                            window.applyCustomerData(c);

                            showAlert('success', 'Customer added successfully!');
                        }
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Save Customer');
                        let msg = 'Error adding customer.';
                        if (err.responseJSON && err.responseJSON.errors) {
                            msg = Object.values(err.responseJSON.errors).flat().join('\n');
                        } else if (err.responseJSON && err.responseJSON.message) {
                            msg = err.responseJSON.message;
                        }
                        showAlert('error', msg);
                    }
                });
            });

            // --- Bulk Add Products to Grid Logic ---
            function addMultipleProductsToGrid(itemsList) {
                if (!itemsList || itemsList.length === 0) return;

                // Remove initial unselected empty row if present
                const $rows = $('#salesTableBody tr');
                if ($rows.length === 1) {
                    const firstVal = $rows.first().find('.product-select').val();
                    if (!firstVal) {
                        $rows.first().remove();
                    }
                }

                let rowsHtml = '';
                const defaultWarehouse = $('#globalWarehouseSelect').val() || $('#branch_id').val() || '1';

                const isProductWise = (window.saleSettings.warehouse_mode === 'product_wise');
                const isShowWatt = !!window.saleSettings.show_watt;
                const isShowLineDisc = !!window.saleSettings.show_line_discount;

                itemsList.forEach(item => {
                    const currentWarehouse = item.warehouse_id || defaultWarehouse;
                    const prodId = item.id;
                    const prodName = item.item_name || item.name || '';
                    const price = parseFloat(item.price || item.retail_price || 0) || 0;
                    const stock = item.stock || 0;
                    const qty = parseFloat(item.qty || 1) || 1;
                    const discVal = parseFloat(item.disc || item.discount || 0) || 0;
                    const discType = item.discType || 'percent';

                    let wattVal = parseFloat(item.watt || 0) || 0;
                    if (!wattVal && isShowWatt) {
                        let match = prodName.match(/\b(\d{3,4})\s*(?:watts?|w)\b/i);
                        if (match) wattVal = parseFloat(match[1]);
                        else {
                            let match2 = prodName.match(/\b(645|585|590|575|580|550|540|530|700|650)\b/i);
                            if (match2) wattVal = parseFloat(match2[1]);
                        }
                    }

                    let gross = (wattVal > 0) ? (price * wattVal * qty) : (price * qty);
                    let discAmt = 0;
                    if (discVal > 0) {
                        if (discType === 'percent') {
                            discAmt = (gross * Math.min(discVal, 100)) / 100;
                        } else {
                            discAmt = discVal * qty;
                        }
                    }
                    let net = Math.max(0, gross - discAmt);

                    let whCellHtml = '';
                    let stockCellHtml = '';
                    if (isProductWise) {
                        whCellHtml = `
                            <td class="col-warehouse">
                                <select class="form-select warehouse warehouse-select shadow-none" name="warehouse_id[]" style="width: 100%; height: 26px; font-size: 0.78rem; padding: 2px 4px; font-weight: 600;">
                                    ${buildWarehouseOptionsHtml(currentWarehouse, prodId)}
                                </select>
                            </td>
                        `;
                        stockCellHtml = `
                            <td class="col-stock">
                                <input type="text" class="form-control stock text-center input-readonly" readonly tabindex="-1" value="${stock}">
                            </td>
                        `;
                    } else {
                        stockCellHtml = `
                            <td class="col-stock">
                                <input type="text" class="form-control stock text-center input-readonly" readonly tabindex="-1" value="${stock}">
                                <input type="hidden" class="warehouse" name="warehouse_id[]" value="${currentWarehouse}">
                            </td>
                        `;
                    }

                    let wattCellHtml = '';
                    if (isShowWatt) {
                        wattCellHtml = `
                            <td class="col-watt">
                                <input type="number" step="any" class="form-control item-watt text-center" name="watt[]" placeholder="0" min="0" value="${wattVal}" style="width: 100%; height: 26px; font-size: 0.85rem; padding: 2px 4px; font-weight: 700;">
                            </td>
                        `;
                    } else {
                        wattCellHtml = `<td class="d-none"><input type="hidden" class="item-watt" name="watt[]" value="${wattVal}"></td>`;
                    }

                    let discCellHtml = '';
                    if (isShowLineDisc) {
                        discCellHtml = `
                            <td class="col-disc">
                                <div class="discount-wrapper">
                                    <input type="number" class="form-control discount-value text-end" name="discount_percentage[]" value="${discVal > 0 ? discVal : ''}" placeholder="0">
                                    <input type="hidden" class="discount-type-field" name="discount_type[]" value="${discType}">
                                    <button type="button" class="btn btn-outline-secondary discount-toggle" data-type="${discType}" tabindex="-1">${discType === 'percent' ? '%' : 'Rs'}</button>
                                </div>
                                <input type="hidden" class="discount-amount" name="discount_amount[]" value="${discAmt.toFixed(2)}">
                            </td>
                        `;
                    } else {
                        discCellHtml = `
                            <td class="d-none">
                                <input type="hidden" class="discount-value" name="discount_percentage[]" value="${discVal > 0 ? discVal : '0'}">
                                <input type="hidden" class="discount-type-field" name="discount_type[]" value="${discType}">
                                <input type="hidden" class="discount-amount" name="discount_amount[]" value="${discAmt.toFixed(2)}">
                            </td>
                        `;
                    }

                    rowsHtml += `
                        <tr>
                            <td class="text-center fw-bold text-muted row-index" style="vertical-align:middle; font-size:0.75rem;"></td>
                            <td class="col-product">
                                <select class="form-select product product-select" name="product_id[]" style="width:100%">
                                    <option value="${prodId}" selected>${prodName}</option>
                                </select>
                                <input type="hidden" class="product-id-hidden" name="product_id_hidden[]" value="${prodId}">
                                <input type="hidden" class="variant-data-hidden" name="color[]" value="">
                                <input type="hidden" class="item-code-display" value="${item.item_code || ''}">
                            </td>
                            ${whCellHtml}
                            ${stockCellHtml}
                            <td style="width:95px;min-width:95px;" class="col-qty-wrapper">
                                <input type="number" step="any" class="form-control carton-qty sales-qty text-start" name="carton_qty[]" placeholder="0" min="0" value="${qty}" style="width: 100%; height: 26px; font-size: 0.85rem; padding-left: 6px;">
                                <input type="hidden" name="sales_qty[]" class="sales-qty-mirror" value="${qty}">
                            </td>
                            <td class="col-pieces">
                                <input type="text" class="form-control total-pieces text-end input-readonly" name="total_pieces[]" readonly placeholder="0" tabindex="-1" value="${qty}">
                                <input type="hidden" class="sales-qty-hidden" name="qty[]" value="${qty}">
                            </td>
                            ${wattCellHtml}
                            <td class="col-price-p">
                                <input type="text" class="form-control visible-price retail-price text-end" name="retail_price[]" value="${price.toFixed(2)}" placeholder="0" style="width: 100%;">
                                <input type="hidden" class="price-per-piece" name="price_per_piece[]" value="${price.toFixed(2)}">
                            </td>
                            ${discCellHtml}
                            <td class="col-amount">
                                <input type="text" class="form-control sales-amount text-end input-readonly" name="sales_amount[]" value="${net.toFixed(2)}" readonly tabindex="-1">
                                <input type="hidden" class="gross-amount" value="${gross.toFixed(2)}">
                            </td>
                            <td class="col-action text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger del-row" tabindex="-1">&times;</button>
                            </td>
                        </tr>
                    `;
                });

                $('#salesTableBody').append(rowsHtml);

                // Initialize Select2 on any uninitialized row and fetch stock
                $('#salesTableBody tr').each(function() {
                    const $select = $(this).find('.product-select');
                    if (!$select.data('select2')) {
                        initProductSelect2($select);
                    }
                    const pId = $select.val();
                    if (pId) {
                        fetchRowStock($(this), pId);
                    }
                });

                updateRowIndexNumbers();
                updateGrandTotals();
            }

            // Update bulk count badge
            function updateBulkSelectedCount() {
                const totalVisible = $('.bulk-product-row:visible').length;
                const selectedCount = $('.bulk-product-row:visible .bulk-product-checkbox:checked').length;

                $('#totalVisibleProductCount').text(totalVisible);
                $('#selectedProductCountBadge').text(selectedCount);
                $('#btnSelectedCount').text(selectedCount);

                $('#masterSelectAllProducts').prop('checked', totalVisible > 0 && selectedCount === totalVisible);
            }

            $(document).on('change', '#masterSelectAllProducts', function() {
                const isChecked = $(this).is(':checked');
                $('.bulk-product-row:visible .bulk-product-checkbox').prop('checked', isChecked);
                updateBulkSelectedCount();
            });

            $(document).on('change', '.bulk-product-checkbox', function() {
                updateBulkSelectedCount();
            });

            $(document).on('input', '#sidebarProductSearch', function() {
                const term = $(this).val().toLowerCase().trim();
                $('.bulk-product-row').each(function() {
                    const name = $(this).data('name') || '';
                    if (name.includes(term)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                updateBulkSelectedCount();
            });

            $(document).on('click', '#btnClearSearch', function() {
                $('#sidebarProductSearch').val('').trigger('input');
            });

            $(document).on('click', '#btnApplyGlobalQtyDisc', function() {
                const globalQty = parseFloat($('#globalBulkQty').val()) || 1;
                const globalDisc = parseFloat($('#globalBulkDisc').val()) || 0;

                $('.bulk-product-row:visible').each(function() {
                    $(this).find('.bulk-item-qty').val(globalQty);
                    $(this).find('.bulk-item-disc').val(globalDisc);
                });

                showAlert('success', `Applied Qty: ${globalQty} & Disc: ${globalDisc}% to all visible products.`);
            });

            $(document).on('click', '#btnAddSelectedBulkProducts', function() {
                const selectedItems = [];

                $('.bulk-product-row:visible').each(function() {
                    const $row = $(this);
                    const $chk = $row.find('.bulk-product-checkbox');
                    if ($chk.is(':checked')) {
                        const id = $row.data('id');
                        const name = $row.find('.product-name-text').text().trim();
                        const price = parseFloat($row.find('.bulk-item-price').val()) || 0;
                        const qty = parseFloat($row.find('.bulk-item-qty').val()) || 1;
                        const disc = parseFloat($row.find('.bulk-item-disc').val()) || 0;

                        selectedItems.push({
                            id: id,
                            item_name: name,
                            price: price,
                            qty: qty,
                            disc: disc
                        });
                    }
                });

                if (selectedItems.length === 0) {
                    showAlert('error', 'Please select at least one product to add!');
                    return;
                }

                addMultipleProductsToGrid(selectedItems);

                const offcanvasEl = document.getElementById('quickProductsOffcanvas');
                if (offcanvasEl) {
                    const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if (bsOffcanvas) bsOffcanvas.hide();
                }

                showAlert('success', `${selectedItems.length} Products added to order grid successfully!`);
            });

            $(document).on('click', '#btnAddAllProductsDirect', function() {
                $('.bulk-product-checkbox').prop('checked', true);
                updateBulkSelectedCount();
                $('#btnAddSelectedBulkProducts').trigger('click');
            });

            $(document).on('click', '.btn-add-single-bulk-product', function() {
                const $row = $(this).closest('.bulk-product-row');
                const id = $row.data('id');
                const name = $row.find('.product-name-text').text().trim();
                const price = parseFloat($row.find('.bulk-item-price').val()) || 0;
                const qty = parseFloat($row.find('.bulk-item-qty').val()) || 1;
                const disc = parseFloat($row.find('.bulk-item-disc').val()) || 0;

                addMultipleProductsToGrid([{
                    id: id,
                    item_name: name,
                    price: price,
                    qty: qty,
                    disc: disc
                }]);

                showAlert('success', `${name} added to order grid!`);
            });

            const quickOffcanvasEl = document.getElementById('quickProductsOffcanvas');
            if (quickOffcanvasEl) {
                quickOffcanvasEl.addEventListener('shown.bs.offcanvas', function () {
                    updateBulkSelectedCount();
                });
            }

            // ================= EDIT MODE INITIALIZATION =================
            try {
                if (window.IS_EDIT_MODE && window.EDIT_SALE) {
                    // 1. Customer initialization
                    let pType = window.EDIT_SALE.party_type || window.EDIT_SALE.partyType || 'credit';
                    if (pType === 'walking' || !window.EDIT_SALE.customer_id) {
                        $('#walkinToggle').prop('checked', true).trigger('change');
                        $('#walkinNameInput').val(window.EDIT_SALE.customer_name || window.EDIT_SALE.sub_customer || 'Walk-in Customer');
                    } else {
                        $('#walkinToggle').prop('checked', false).trigger('change');
                        if (window.EDIT_SALE.customer_id) {
                            let cust = window.EDIT_SALE.customer || {};
                            let custText = (cust.customer_id ? cust.customer_id + ' — ' : '') + (cust.customer_name || 'Customer #' + window.EDIT_SALE.customer_id);
                            let opt = new Option(custText, window.EDIT_SALE.customer_id, true, true);
                            let prev = window.EDIT_SALE.previous_balance || cust.previous_balance || cust.closing_balance || 0;
                            $(opt).attr('data-prev-balance', prev);
                            $(opt).attr('data-address', window.EDIT_SALE.address || cust.address || '');
                            $(opt).attr('data-mobile', window.EDIT_SALE.tel || cust.mobile || '');
                            $('#customerSelect').append(opt).trigger('change.select2');
                            window.applyCustomerData({
                                id: window.EDIT_SALE.customer_id,
                                address: window.EDIT_SALE.address || cust.address || '',
                                mobile: window.EDIT_SALE.tel || cust.mobile || '',
                                previous_balance: prev
                            });
                        }
                    }

                    if (window.EDIT_SALE.salesman_id) {
                        $('#salesmanSelect').val(window.EDIT_SALE.salesman_id);
                    }

                    if (window.EDIT_SALE.warehouse_id) {
                        let whVal = String(window.EDIT_SALE.warehouse_id);
                        if (!whVal.startsWith('warehouse_') && !whVal.startsWith('branch_')) {
                            whVal = 'warehouse_' + whVal;
                        }
                        $('#globalWarehouseSelect').val(whVal);
                    } else if (window.EDIT_SALE.branch_id) {
                        $('#globalWarehouseSelect').val('branch_' + window.EDIT_SALE.branch_id);
                    }

                    if (window.EDIT_SALE.manual_invoice) {
                        $('#manualInvoice').val(window.EDIT_SALE.manual_invoice);
                        $('#Invoice_main').val(window.EDIT_SALE.manual_invoice);
                    }
                    if (window.EDIT_SALE.remarks) {
                        $('#remarks').val(window.EDIT_SALE.remarks);
                        $('#reference').val(window.EDIT_SALE.remarks);
                    }

                    if (window.EDIT_SALE.additional_discount) {
                        $('#walkinDiscountRs').val(parseFloat(window.EDIT_SALE.additional_discount) || 0);
                    }

                    // 2. Populate Order Items
                    if ($('#salesTableBody tr').length > 0) {
                        $('#salesTableBody tr').each(function() {
                            const $r = $(this);
                            const $select = $r.find('.product-select');
                            if (!$select.data('select2')) {
                                initProductSelect2($select);
                            }
                            const pId = $select.val() || $r.find('.product-id-hidden').val();
                            if (pId) {
                                fetchRowStock($r, pId);
                            }
                        });
                        updateRowIndexNumbers();
                    } else if (window.EDIT_SALE_ITEMS && window.EDIT_SALE_ITEMS.length > 0) {
                        $('#salesTableBody').empty();
                        let formattedItems = window.EDIT_SALE_ITEMS.map(it => {
                            let discType = it.discount_type || (parseFloat(it.discount_percent) > 0 ? 'percent' : 'pkr');
                            let discVal = (discType === 'percent') ? (parseFloat(it.discount_percent) || 0) : (parseFloat(it.discount_amount) || parseFloat(it.discount) || 0);
                            let itWh = it.warehouse_id;
                            if (itWh) {
                                let sWh = String(itWh);
                                if (!sWh.startsWith('warehouse_') && !sWh.startsWith('branch_')) {
                                    itWh = 'warehouse_' + sWh;
                                }
                            }
                            return {
                                id: it.product_id,
                                item_name: it.item_name,
                                item_code: it.item_code,
                                price: parseFloat(it.retail_price || it.sales_price || it.price || 0),
                                qty: parseFloat(it.sales_qty || it.qty || 1),
                                stock: parseFloat(it.onhand_qty || 0),
                                disc: discVal,
                                discType: discType,
                                warehouse_id: itWh,
                                watt: parseFloat(it.watt || 0) || 0,
                                amount: parseFloat(it.amount || it.total || 0) || 0
                            };
                        });
                        addMultipleProductsToGrid(formattedItems);
                    }

                    // 3. Populate Receipts
                    if (window.EDIT_RECEIPTS && window.EDIT_RECEIPTS.length > 0) {
                        $('#rvWrapper').empty();
                        window.EDIT_RECEIPTS.forEach(rv => {
                            let optionsHtml = '';
                            if (window.RECEIPT_ACCOUNTS && Array.isArray(window.RECEIPT_ACCOUNTS)) {
                                window.RECEIPT_ACCOUNTS.forEach(function(acc) {
                                    let isSel = (acc.id == rv.row_account_id) ? 'selected' : '';
                                    optionsHtml += `<option value="${acc.id}" ${isSel}>${acc.title}</option>`;
                                });
                            }
                            let rvAmt = parseFloat(rv.total_amount || rv.amount || 0).toFixed(2);
                            const $row = $(`
                                <div class="d-flex gap-1 align-items-center mb-2 rv-row">
                                    <select class="form-select form-select-sm rv-account bg-light fw-bold" name="receipt_account_id[]" style="font-size:0.75rem;">
                                        ${optionsHtml}
                                    </select>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end rv-amount fw-bold" name="receipt_amount[]" value="${rvAmt}" placeholder="0.00" style="width: 110px; font-size:0.75rem;">
                                    <button type="button" class="btn btn-sm btn-outline-danger btnRemRV py-0 px-1">&times;</button>
                                </div>
                            `);
                            $('#rvWrapper').append($row);
                        });
                    } else {
                        // Fallback: If no receipt voucher records found, check sale's payment amounts
                        let fallbackPaid = 0;
                        if (window.EDIT_SALE) {
                            let r1 = parseFloat(window.EDIT_SALE.receipt1) || 0;
                            let r2 = parseFloat(window.EDIT_SALE.receipt2) || 0;
                            if (r1 + r2 > 0) {
                                fallbackPaid = r1 + r2;
                            } else if (window.EDIT_SALE.party_type === 'cash' || window.EDIT_SALE.party_type === 'walking' || window.EDIT_SALE.status === 'posted' || window.EDIT_SALE.is_posted) {
                                fallbackPaid = parseFloat(window.EDIT_SALE.total_balance || window.EDIT_SALE.sub_total2 || window.EDIT_SALE.total_net || 0);
                            }
                        }
                        if (fallbackPaid > 0) {
                            $('#rvWrapper .rv-amount').first().val(fallbackPaid.toFixed(2));
                        }
                    }
                } else if (window.BOOKING_ITEMS && window.BOOKING_ITEMS.length > 0) {
                    $('#salesTableBody').empty();
                    let formattedItems = window.BOOKING_ITEMS.map(it => {
                        let discType = it.discount_type || (parseFloat(it.discount_percent) > 0 ? 'percent' : 'pkr');
                        let discVal = (discType === 'percent') ? (parseFloat(it.discount_percent) || 0) : (parseFloat(it.discount_amount) || parseFloat(it.discount) || 0);
                        let itWh = it.warehouse_id;
                        if (itWh) {
                            let sWh = String(itWh);
                            if (!sWh.startsWith('warehouse_') && !sWh.startsWith('branch_')) {
                                itWh = 'warehouse_' + sWh;
                            }
                        }
                        return {
                            id: it.product_id,
                            item_name: it.product ? it.product.item_name : (it.item_name || ''),
                            item_code: it.product ? it.product.item_code : (it.item_code || ''),
                            price: parseFloat(it.retail_price || it.sales_price || it.price || 0),
                            qty: parseFloat(it.sales_qty || it.qty || 1),
                            stock: parseFloat(it.stock || 0),
                            disc: discVal,
                            discType: discType,
                            warehouse_id: itWh,
                            watt: parseFloat(it.watt || 0) || 0
                        };
                    });
                    addMultipleProductsToGrid(formattedItems);

                    if (window.BOOKING_DATA) {
                        if (window.BOOKING_DATA.manual_invoice) {
                            $('#manualInvoice').val(window.BOOKING_DATA.manual_invoice);
                            $('#Invoice_main').val(window.BOOKING_DATA.manual_invoice);
                        }
                        if (window.BOOKING_DATA.remarks) {
                            $('#remarks').val(window.BOOKING_DATA.remarks);
                            $('#reference').val(window.BOOKING_DATA.remarks);
                        }
                    }
                } else if ($('#salesTableBody tr').length === 0) {
                    addNewRow();
                }
            } catch (err) {
                console.error('Error during edit mode initialization:', err);
            } finally {
                // Initial calculations & hide page loader
                updateGrandTotals();
                $('#pageLoader').addClass('d-none').css('display', 'none');
            }
        });
    </script>
@endsection

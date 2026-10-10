@extends('admin_panel.layout.app')

@section('content')
    <!-- Loader Overlay -->
    <div id="pageLoader"
        class="position-fixed top-0 start-0 w-100 h-100 flex-column gap-3 justify-content-center align-items-center {{ isset($sale) ? 'd-flex' : 'd-none' }}"
        style="background: rgba(255,255,255,0.9); z-index: 1055; {{ isset($sale) ? 'display: flex;' : 'display: none !important;' }}">
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
        .col-stock { width: 55px !important; min-width: 55px !important; }
        .col-qty { width: 85px !important; min-width: 85px !important; }
        .col-pieces { width: 50px !important; min-width: 50px !important; }
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

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 0.8rem;
        }
        .summary-val-net {
            font-weight: 800;
            color: #2563eb;
            font-size: 1rem;
        }
        .summary-val-change {
            background: #ffe4e6;
            color: #e11d48;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .bottom-summary-strip {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 16px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
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
                <input type="hidden" id="booking_id" name="booking_id" value="">
                <input type="hidden" id="action" name="action" value="sale">
                <input type="hidden" id="is_posted" name="is_posted" value="0">
                <input type="hidden" id="is_finalized" name="is_finalized" value="0">
                <input type="hidden" name="Invoice_date" id="Invoice_date" value="{{ date('Y-m-d') }}">
                <input type="hidden" name="estimated_delivery_date" id="estimated_delivery_date" value="{{ date('Y-m-d', strtotime('+15 days')) }}">
                <input type="hidden" id="customer_id" name="customer_id" value="">
                <input type="hidden" id="customer" name="customer" value="">
                <input type="hidden" id="address" name="address" value="">
                <input type="hidden" id="tel" name="tel" value="">
                <input type="hidden" id="previousBalance" value="0">
                <input type="hidden" id="rangeBalance" value="0">
                <input type="hidden" id="creditLimit" value="0">
                <input type="hidden" id="noCreditLimit" value="0">
                <input type="hidden" name="Invoice_no" id="inputInvoiceNo" value="{{ $nextInvoiceNumber }}">
                
                {{-- Hidden Party Type Radio Group for JS compatibility --}}
                <div class="d-none" id="partyTypeGroup">
                    <input type="radio" name="partyType" value="credit" id="typeCustomers" checked>
                    <input type="radio" name="partyType" value="cash" id="typeWalkin">
                    <input type="radio" name="partyType" value="walking" id="typewalking">
                </div>

                {{-- TOP HEADER BAR WITH INVOICE NO BADGE & SALE SETTINGS BUTTON --}}
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('sale.index') }}" class="btn btn-sm btn-light border rounded-circle" title="Back"><i class="fas fa-arrow-left text-secondary"></i></a>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                                <i class="fas fa-shopping-cart text-primary"></i> New Sale
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1 rounded-pill" style="font-size: 0.78rem; background: #e6fcf5;">
                                    <i class="fas fa-file-invoice me-1"></i><span id="activePrefixLabelHeader">{{ $activePrefix ?? 'INV' }}</span>-<span id="headerInvoiceNoDisplay">{{ $nextInvoiceNumber }}</span>
                                </span>
                            </h5>
                            <small class="text-muted" style="font-size: 0.7rem;">Create a new invoice</small>
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
                <div class="card-panel mb-2 p-2 bg-white border rounded-3 shadow-xs">
                    <div class="row g-2 align-items-end">
                        <!-- Branch Selection (Super Admin dropdown, Regular User readonly/badge) -->
                        <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                            <label class="form-label fw-bold text-uppercase text-primary mb-1 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px;">
                                <i class="fas fa-building me-1"></i>Branch
                            </label>
                            @if (Auth::user() && Auth::user()->hasRole('super admin'))
                                <select class="form-select fw-bold border-primary shadow-xs" name="branch_id" id="branch_id" style="font-size:0.78rem; height: 30px;">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ (isset($defaultBranchId) && $defaultBranchId == $b->id) || (Auth::user()->branch_id == $b->id) ? 'selected' : '' }}>
                                            {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <div class="form-control form-control-sm bg-light fw-bold text-primary text-truncate border-secondary-subtle" style="font-size:0.78rem; height: 30px; line-height: 22px;">
                                    <i class="fas fa-store me-1"></i>{{ Auth::user()->branch->name ?? 'Default Branch' }}
                                </div>
                                <input type="hidden" name="branch_id" id="branch_id" value="{{ Auth::user()->branch_id ?? 1 }}">
                            @endif
                        </div>

                        <!-- Warehouse Selection -->
                        <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                            <label class="form-label fw-bold text-uppercase text-secondary mb-1 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px;">
                                <i class="fas fa-warehouse text-info me-1"></i>Warehouse
                            </label>
                            <select class="form-select fw-bold shadow-xs" name="warehouse_id" id="globalWarehouseSelect" style="font-size:0.78rem; height: 30px;">
                                @foreach($allWarehouses as $w)
                                    <option value="{{ $w->id }}" {{ (Auth::user()->warehouse_id == $w->id) ? 'selected' : '' }}>
                                        {{ $w->warehouse_name ?? $w->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date with Calendar -->
                        <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                            <label class="form-label fw-bold text-uppercase text-secondary mb-1 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px;">
                                <i class="far fa-calendar-alt text-success me-1"></i>Date
                            </label>
                            <input type="date" name="sale_date" class="form-control text-center fw-bold shadow-xs" id="displayDateInput" value="{{ date('Y-m-d') }}" style="font-size:0.78rem; height: 30px;">
                        </div>

                        <!-- Salesman Selection Dropdown -->
                        <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                            <label class="form-label fw-bold text-uppercase text-secondary mb-1 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px;">
                                <i class="fas fa-user-tie text-warning me-1"></i>Salesman
                            </label>
                            <select class="form-select fw-bold shadow-xs" name="salesman_id" id="salesmanSelect" style="font-size:0.78rem; height: 30px;">
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
                        <div class="col-xl-1 col-lg-1 col-md-2 col-sm-6">
                            <label class="form-label fw-bold text-uppercase text-secondary mb-1 text-truncate d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px;">
                                <i class="fas fa-clock text-danger me-1"></i>Cr. Days
                            </label>
                            <input type="number" class="form-control text-center fw-bold shadow-xs" name="credit_days" placeholder="0" min="0" value="{{ $sale->credit_days ?? '0' }}" style="font-size:0.78rem; height: 30px;">
                        </div>

                        <!-- M.Bill / Remarks -->
                        <div class="col-xl-3 col-lg-3 col-md-10 col-sm-6">
                            <label class="form-label fw-bold text-uppercase text-secondary mb-1 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px;">
                                <i class="fas fa-sticky-note text-secondary me-1"></i>M.Bill / Remarks
                            </label>
                            <input type="text" class="form-control shadow-xs" name="reference" id="remarks" placeholder="Enter remarks or ref no..." style="font-size:0.78rem; height: 30px;">
                        </div>

                        <!-- Customer Input Group -->
                        <div class="col-12 mt-2">
                            <label class="form-label fw-bold text-uppercase text-primary mb-1 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px;">
                                <i class="fas fa-user-circle text-primary me-1"></i>Customer Selection
                            </label>
                            <div id="customerInputWrapper" class="input-group input-group-sm shadow-xs rounded">
                                <div id="customerSelectBoxContainer" class="flex-grow-1" style="min-width: 0;">
                                    <select class="form-select" id="customerSelect" name="customer" style="width:100%">
                                        <option value=""></option>
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
                </div>

                <!-- 2-COLUMN RESPONSIVE POS LAYOUT -->
                <div class="row g-1 align-items-stretch">
                    <!-- LEFT MAIN AREA: Items Grid Table -->
                    <div class="col-lg-8 col-xl-9">
                        <div class="card-panel d-flex flex-column h-100 p-2 bg-white" style="border-radius:6px;">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="section-title mb-0" style="font-size:0.8rem;">Order Items (<span id="itemsRowCount">0</span>)</div>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" data-bs-toggle="offcanvas" data-bs-target="#quickProductsOffcanvas" style="font-size:0.72rem;">
                                        <i class="fas fa-th me-1"></i>Quick Products Panel
                                    </button>
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
                                            <th class="col-stock" style="width: 55px;">STOCK</th>
                                            <th class="col-qty" style="width: 85px;">QTY</th>
                                            <th class="col-pieces" style="width: 50px;">PCS</th>
                                            <th class="col-price-p" style="width: 80px;">PRICE</th>
                                            <th class="col-disc" style="width: 80px;">DISCOUNT</th>
                                            <th class="col-amount" style="width: 90px;">AMOUNT</th>
                                            <th class="col-action" style="width: 32px;">×</th>
                                        </tr>
                                    </thead>
                                    <tbody id="salesTableBody">
                                        <!-- Table rows are dynamically rendered by JS -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="7" class="text-end fw-bold text-uppercase text-secondary" style="font-size:0.78rem;">Grid Total:</td>
                                            <td class="text-end fw-bold text-success fs-6"><span id="totalAmount">0.00</span></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT PANEL: Summary & Payment Methods -->
                    <div class="col-lg-4 col-xl-3">
                        <div class="d-flex flex-column h-100 gap-2">
                            <!-- Executive Summary Card -->
                            <div class="card-panel p-3 bg-white" style="border-radius:6px;">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                    <span class="fw-bold text-dark" style="font-size:0.85rem;"><i class="fas fa-calculator text-primary me-1"></i>Summary</span>
                                    <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size:0.7rem;">Live</span>
                                </div>
                                
                                <div class="summary-row">
                                    <span class="text-muted">Total Amount</span>
                                    <span class="fw-bold text-dark" id="tGross">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="text-muted">Line Discount</span>
                                    <span class="fw-bold text-danger" id="tLineDisc">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="fw-bold text-dark">Net Total</span>
                                    <span class="summary-val-net" id="tSub">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="text-muted">Total Paid</span>
                                    <span class="fw-bold text-success" id="receiptsTotalBadge">0.00</span>
                                </div>
                                <div class="summary-row pt-1">
                                    <span class="fw-bold text-danger">Change</span>
                                    <span class="summary-val-change" id="walkinChange">0.00</span>
                                </div>

                                {{-- Hidden elements for backward compatibility --}}
                                <span class="d-none" id="receiptsTotal">0</span>
                                <span class="d-none" id="tOrderDisc">0</span>
                                <span class="d-none" id="tPrev">0</span>
                                <span class="d-none" id="tPayable">0</span>
                            </div>

                            <!-- Payment Methods Card -->
                            <div class="card-panel p-3 bg-white flex-grow-1 d-flex flex-column" style="border-radius:6px;">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                    <span class="fw-bold text-dark" style="font-size:0.82rem;"><i class="fas fa-wallet text-success me-1"></i>Payment Methods</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" id="btnAddRV" style="font-size:0.7rem;"><i class="fas fa-plus me-1"></i>Add Account</button>
                                </div>

                                <div id="rvWrapper" class="mb-2">
                                    <div class="d-flex gap-1 align-items-center mb-2 rv-row">
                                        <select class="form-select form-select-sm rv-account bg-light fw-bold" name="receipt_account_id[]" style="font-size:0.75rem;">
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title), 'cash') || str_contains(strtolower($acc->title), 'easypaisa') ? 'selected' : '' }}>{{ $acc->title }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" step="0.01" class="form-control form-control-sm text-end rv-amount fw-bold" name="receipt_amount[]" placeholder="0.00" style="width: 110px; font-size:0.75rem;">
                                    </div>
                                </div>

                                <button type="button" class="btn btn-save-complete w-100 mt-auto py-2" id="btnSaveAndComplete">
                                    <i class="fas fa-save me-2"></i>Save & Complete (F9)
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
                            <input type="number" class="form-control text-end fw-bold text-danger" id="walkinDiscountRs" name="additional_discount" value="0" placeholder="0">
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
                        <i class="fas fa-save me-2"></i>Save & Complete (F9)
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
                    <button type="button" class="btn btn-primary btn-sm px-4 py-2 fw-bold shadow-sm" id="btnSave" style="background:#2563eb !important; border-color:#2563eb !important;">
                        <i class="fas fa-file-invoice me-1"></i>Save Booking (Draft)
                    </button>
                    <button type="button" class="btn btn-success btn-sm px-5 py-2 fw-bold fs-6 shadow-sm" id="btnPosted" style="background:#16a34a !important; border-color:#16a34a !important;">
                        <i class="fas fa-shopping-cart me-1"></i>Process Sale / POS
                    </button>
                    <button type="button" class="btn btn-warning btn-sm px-4 py-2 fw-bold text-dark shadow-sm" id="btnPartialSale" style="background:#f59e0b !important; border-color:#d97706 !important;">
                        <i class="fas fa-truck-loading me-1"></i>Partially Sale
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold" id="btnPrint"><i class="fas fa-print me-1"></i>A4 Print</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold" id="btnEstimate"><i class="fas fa-file-invoice me-1"></i>Estimate</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold" id="btnPrint2"><i class="fas fa-receipt me-1"></i>Thermal Print</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold" id="btnDcThermal"><i class="fas fa-truck me-1"></i>DC</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Products Offcanvas Drawer (Bulk Product Add) -->
    <div class="offcanvas offcanvas-start shadow-lg" tabindex="-1" id="quickProductsOffcanvas" style="width: 550px;">
        <div class="offcanvas-header bg-dark text-white py-2 border-bottom">
            <h6 class="offcanvas-title fw-bold text-white mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-layer-group text-primary fs-5"></i> Quick Products Panel (Bulk Add)
            </h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-2 d-flex flex-column" style="background-color: #f8fafc;">
            <!-- Top Controls Toolbar -->
            <div class="card p-2 mb-2 border-0 shadow-sm" style="background:#ffffff; border-radius: 8px;">
                <!-- Search Box -->
                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 fw-semibold" id="sidebarProductSearch" placeholder="Search products by name, code or SKU...">
                    <button class="btn btn-outline-secondary" type="button" id="btnClearSearch"><i class="fas fa-times"></i></button>
                </div>

                <!-- Global Default Qty & Disc Controls -->
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light fw-bold" style="font-size:0.7rem;">Qty</span>
                            <input type="number" step="any" class="form-control text-center fw-bold" id="globalBulkQty" value="1" min="0.01">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light fw-bold" style="font-size:0.7rem;">Disc %</span>
                            <input type="number" step="any" class="form-control text-center fw-bold" id="globalBulkDisc" value="0" min="0" max="100">
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="button" class="btn btn-sm btn-outline-primary w-100 fw-bold py-1" id="btnApplyGlobalQtyDisc" style="font-size:0.72rem;">
                            <i class="fas fa-sync-alt me-1"></i> Apply All
                        </button>
                    </div>
                </div>

                <!-- Master Checkbox & Selection Bar -->
                <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded-2 border mb-2">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="masterSelectAllProducts" checked>
                        <label class="form-check-label fw-bold text-dark" for="masterSelectAllProducts" style="font-size:0.78rem;">
                            Select All (<span id="totalVisibleProductCount">0</span>)
                        </label>
                    </div>
                    <small class="text-muted fw-bold" style="font-size:0.75rem;">
                        <span id="selectedProductCountBadge" class="badge bg-primary px-2 py-1">0</span> Selected
                    </small>
                </div>

                <!-- Main Action Buttons -->
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm flex-fill fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnAddSelectedBulkProducts">
                        <i class="fas fa-cart-plus fs-6"></i> Add Selected Products (<span id="btnSelectedCount">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-3 py-2" id="btnAddAllProductsDirect" title="Add ALL products to grid immediately">
                        <i class="fas fa-layer-group me-1"></i> Add ALL
                    </button>
                </div>
            </div>

            <!-- Product Items List Table Container -->
            <div class="overflow-auto flex-grow-1 border rounded-2 bg-white" id="sidebarProductContainer" style="max-height: calc(100vh - 270px);">
                @php
                    $productList = (isset($recentProducts) && count($recentProducts) > 0) ? $recentProducts : ($products ?? []);
                @endphp
                <table class="table table-sm table-hover mb-0 align-middle" style="font-size: 0.78rem;" id="bulkProductsTable">
                    <thead class="table-light sticky-top" style="z-index: 1;">
                        <tr>
                            <th class="text-center" style="width: 32px;">#</th>
                            <th>Product Name</th>
                            <th class="text-end" style="width: 80px;">Price</th>
                            <th class="text-center" style="width: 65px;">Qty</th>
                            <th class="text-center" style="width: 60px;">Disc%</th>
                            <th class="text-center" style="width: 40px;">+</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productList as $prod)
                            @php
                                $price = $prod->retail_price ?? $prod->sale_price_per_box ?? $prod->purchase_price_per_piece ?? 0;
                                $stock = $prod->total_pieces ?? $prod->piece_quantity ?? 0;
                            @endphp
                            <tr class="bulk-product-row" data-id="{{ $prod->id }}" data-name="{{ strtolower($prod->item_name) }}">
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input bulk-product-checkbox" data-id="{{ $prod->id }}" checked>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark product-name-text">{{ $prod->item_name }}</div>
                                    <small class="text-muted" style="font-size:0.68rem;">Stock: <span class="badge bg-success-subtle text-success border px-1">{{ $stock }} Pcs</span></small>
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    {{ number_format($price, 2) }}
                                    <input type="hidden" class="bulk-item-price" value="{{ $price }}">
                                </td>
                                <td>
                                    <input type="number" step="any" class="form-control form-control-sm text-center fw-bold bulk-item-qty px-1" value="1" min="0.01" style="height:26px;">
                                </td>
                                <td>
                                    <input type="number" step="any" class="form-control form-control-sm text-center bulk-item-disc px-1" value="0" min="0" max="100" placeholder="0" style="height:26px;">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 btn-add-single-bulk-product" data-id="{{ $prod->id }}" title="Add single product to grid" style="height:26px; line-height:1;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
                            Select the warehouse and specify the quantity to dispatch out <strong>NOW</strong>. 
                            A Delivery Challan (DC) will be generated for dispatched items, and the remaining stock will be saved in 
                            <strong>Pending Deliveries</strong> until fully issued.
                        </div>
                    </div>

                    <div class="row g-2 mb-3 align-items-center bg-light p-2 rounded border">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small mb-0"><i class="fas fa-warehouse me-1 text-primary"></i> Dispatch Warehouse:</label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select form-select-sm border-primary fw-semibold" id="partial_warehouse_id">
                                <option value="">-- Select Dispatch Location / Warehouse --</option>
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
            show_line_discount: {{ (isset($saleSettings) && $saleSettings->show_line_discount) ? 'true' : 'false' }},
            show_overall_discount: {{ (isset($saleSettings) && $saleSettings->show_overall_discount) ? 'true' : 'false' }}
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

            // --- Add Row Logic (Model & Product Only - Size & Color Removed) ---
            function addNewRow() {
                const rowCount = $('#salesTableBody tr').length + 1;
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
                        <td class="col-stock">
                            <input type="text" class="form-control stock text-center input-readonly" readonly tabindex="-1" value="0">
                            <input type="hidden" class="warehouse" name="warehouse_id[]" value="{{ auth()->user()->warehouse_id ?? 1 }}">
                        </td>
                        <td style="width:95px;min-width:95px;" class="col-qty-wrapper">
                            <input type="number" step="any" class="form-control carton-qty sales-qty text-start" name="carton_qty[]" placeholder="0" min="0" value="1" style="width: 100%; height: 26px; font-size: 0.85rem; padding-left: 6px;">
                            <input type="hidden" name="sales_qty[]" class="sales-qty-mirror" value="1">
                        </td>
                        <td class="col-pieces">
                            <input type="text" class="form-control total-pieces text-end input-readonly" name="total_pieces[]" readonly placeholder="0" tabindex="-1" value="1">
                            <input type="hidden" class="sales-qty-hidden" name="qty[]" value="1">
                        </td>
                        <td class="col-price-p">
                            <input type="text" class="form-control visible-price retail-price text-end" name="retail_price[]" placeholder="0" style="width: 100%;">
                            <input type="hidden" class="price-per-piece" name="price_per_piece[]">
                        </td>
                        <td class="col-disc">
                            <div class="discount-wrapper">
                                <input type="number" class="form-control discount-value text-end" name="discount_percentage[]" placeholder="0">
                                <input type="hidden" class="discount-type-field" name="discount_type[]" value="percent">
                                <button type="button" class="btn btn-outline-secondary discount-toggle" data-type="percent" tabindex="-1">%</button>
                            </div>
                            <input type="hidden" class="discount-amount" name="discount_amount[]" value="0">
                        </td>
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

            // Initial row if empty
            if ($('#salesTableBody tr').length === 0) {
                addNewRow();
            }

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
                                return {
                                    id: p.id,
                                    text: (p.item_code ? p.item_code + ' — ' : '') + (p.item_name || p.name || ''),
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
                    $row.find('.stock').val(prod.piece_quantity || prod.total_pieces || 0);
                    $row.find('.retail-price').val(prod.sale_price_per_box || prod.purchase_price_per_piece || 0);
                    $row.find('.total-pieces').val($row.find('.sales-qty').val() || 1);
                    computeRow($row);
                    updateGrandTotals();
                }
            });

            // --- Row Calculation Logic ---
            function computeRow($row) {
                const price = toNum($row.find('.retail-price').val());
                const qty = toNum($row.find('.sales-qty').val());
                let discVal = toNum($row.find('.discount-value').val());
                const discType = $row.find('.discount-toggle').data('type') || 'percent';

                let gross = price * qty;
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
                    const discAmt = toNum($r.find('.discount-amount').val());
                    const gross = price * qty;
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

                let changeVal = receipts - finalNet;

                // Executive Summary Card Update
                $('#tGross').text(tGross.toFixed(2));
                $('#tLineDisc').text(tLineDisc.toFixed(2));
                $('#tSub').text(finalNet.toFixed(2));
                $('#receiptsTotalBadge').text(receipts.toFixed(2));
                $('#receiptsTotal').text(receipts.toFixed(2));
                $('#walkinChange').text(changeVal.toFixed(2));

                // Table Foot
                $('#totalAmount').text(finalNet.toFixed(2));

                // Bottom Summary Strip Update
                $('#walkinNetTotal').text(finalNet.toFixed(2));
                $('#bottomPaymentsTotal').text(receipts.toFixed(2));
                $('#bottomChangeVal').text(changeVal.toFixed(2));

                // Form hidden mirrors
                $('#subTotal1').val(tGross.toFixed(2));
                $('#subTotal2').val(finalNet.toFixed(2));
                $('#discountAmount').val(overallDisc.toFixed(2));
                $('#totalBalance').val(finalNet.toFixed(2));
            }

            // Events on grid inputs
            $(document).on('input', '.sales-qty, .retail-price, .discount-value', function() {
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

            $('#walkinToggle').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#walkinNameInput').removeClass('d-none').show().focus();
                    $('#customerSelectBoxContainer').addClass('d-none').hide();
                    $('#btnWalkinToggle').removeClass('btn-outline-primary').addClass('btn-primary active');
                    $('#typewalking').prop('checked', true).trigger('change');
                    $('#customer_id').val('');
                    $('#customer').val('');
                    $('#address').val('');
                    $('#tel').val('');
                    $('#previousBalance').val('0.00');
                } else {
                    $('#walkinNameInput').addClass('d-none').hide();
                    $('#customerSelectBoxContainer').removeClass('d-none').show();
                    $('#btnWalkinToggle').removeClass('btn-primary active').addClass('btn-outline-primary');
                    $('#typeCustomers').prop('checked', true).trigger('change');
                }
            });

            // Default to Customer Select mode on page load
            $('#walkinToggle').prop('checked', false).trigger('change');

            // --- Dynamic Branch Selection Change Handler ---
            $(document).on('change', '#branch_id', function() {
                let selectedBranchId = $(this).val();
                if (!selectedBranchId) return;

                // 1. Update Invoice Counter
                if (window.BRANCH_COUNTERS && window.BRANCH_COUNTERS[selectedBranchId] !== undefined) {
                    let nextCounter = parseInt(window.BRANCH_COUNTERS[selectedBranchId]) + 1;
                    let formattedNo = 'INV-' + String(nextCounter).padStart(4, '0');
                    $('#inputInvoiceNo').val(formattedNo);
                    $('#headerInvoiceNoDisplay').text(formattedNo);
                }

                // 2. Fetch Salesmen for Selected Branch
                $.ajax({
                    url: "{{ url('/sale/get-branch-salesmen') }}/" + selectedBranchId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(salesmen) {
                        let $smSelect = $('#salesmanSelect');
                        $smSelect.empty().append('<option value="">Select Salesman</option>');
                        if (Array.isArray(salesmen) && salesmen.length > 0) {
                            salesmen.forEach(function(sm) {
                                let name = sm.name || sm.salesman_name || sm.full_name || ('Salesman #' + sm.id);
                                $smSelect.append(`<option value="${sm.id}">${name}</option>`);
                            });
                        }
                    },
                    error: function(err) {
                        console.error('Failed to fetch branch salesmen:', err);
                    }
                });

                // 3. Reset customer selection upon branch change
                $('#customerSelect').val(null).trigger('change');
                $('#customer_id').val('');
                $('#customer').val('');
                $('#address').val('');
                $('#tel').val('');
                $('#previousBalance').val('0.00');
            });

            // --- Customer Select2 AJAX Search ---
            $('#customerSelect').select2({
                placeholder: 'Search Customer...',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '{{ route('salecustomers.index') }}',
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

            $('#customerSelect').on('select2:select', function(e) {
                const id = e.params.data.id;
                if (!id) return;
                $.get("{{ url('sale/customers') }}/" + id, function(d) {
                    $('#address').val(d.address || '');
                    $('#tel').val(d.mobile || '');
                    $('#previousBalance').val(parseFloat(d.previous_balance || 0).toFixed(2));
                    $('#customer_id').val(d.id);
                    $('#customer').val(d.id);
                    updateGrandTotals();
                });
            });

            // --- Form Save & Post Logic ---
            function ensureSaved() {
                return new Promise((resolve, reject) => {
                    let formData = $('#saleForm').serialize();
                    $.ajax({
                        url: "{{ route('sales.store') }}",
                        type: 'POST',
                        data: formData,
                        success: function(res) {
                            if (res.success || res.id) {
                                resolve(res.id || res.sale_id || res.booking_id);
                            } else {
                                showAlert('success', 'Saved successfully');
                                resolve(res.id || 1);
                            }
                        },
                        error: function(err) {
                            showAlert('error', 'Failed to save sale form.');
                            reject(err);
                        }
                    });
                });
            }

            // Process Sale Button
            $('#btnPosted, #btnHeaderSaveSale, #btnSaveAndComplete, #btnSaveAndComplete2').on('click', function() {
                $('#action').val('sale');
                ensureSaved().then(id => {
                    showAlert('success', 'Sale completed successfully!');
                    setTimeout(() => {
                        window.location.href = "{{ route('sale.index') }}";
                    }, 1200);
                });
            });

            // Booking / Draft Button
            $('#btnSave').on('click', function() {
                $('#action').val('booking');
                ensureSaved().then(id => {
                    showAlert('success', 'Booking saved successfully!');
                });
            });

            // Partial Sale Button Logic
            $('#btnPartialSale').on('click', function() {
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
                        if (res.success || res.dc_id) {
                            $('#partialSaleModal').modal('hide');
                            showAlert('success', 'Partial DC created successfully!');
                            if (res.dc_id) {
                                window.open('{{ url('sales') }}/' + res.dc_id + '/dc-thermal', '_blank');
                            }
                            setTimeout(() => {
                                window.location.href = "{{ route('sale.index') }}";
                            }, 1200);
                        }
                    },
                    error: function(err) {
                        btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Confirm & Issue Partial DC');
                        let msg = 'Error creating partial delivery.';
                        if (err.responseJSON && err.responseJSON.message) {
                            msg = err.responseJSON.message;
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
                            let newOption = new Option((c.customer_id || '') + ' — ' + c.customer_name, c.id, true, true);
                            $('#customerSelect').append(newOption).trigger('change');

                            $('#customer_id').val(c.id);
                            $('#customer').val(c.id);
                            $('#address').val(c.address || '');
                            $('#tel').val(c.mobile || '');
                            $('#previousBalance').val(parseFloat(c.opening_balance || 0).toFixed(2));
                            updateGrandTotals();

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
                const currentWarehouse = $('#branch_id').val() || '1';

                itemsList.forEach(item => {
                    const prodId = item.id;
                    const prodName = item.item_name || item.name || '';
                    const price = parseFloat(item.price || item.retail_price || 0) || 0;
                    const stock = item.stock || 0;
                    const qty = parseFloat(item.qty || 1) || 1;
                    const discVal = parseFloat(item.disc || item.discount || 0) || 0;
                    const discType = item.discType || 'percent';

                    let gross = price * qty;
                    let discAmt = 0;
                    if (discVal > 0) {
                        if (discType === 'percent') {
                            discAmt = (gross * Math.min(discVal, 100)) / 100;
                        } else {
                            discAmt = discVal * qty;
                        }
                    }
                    let net = Math.max(0, gross - discAmt);

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
                            <td class="col-stock">
                                <input type="text" class="form-control stock text-center input-readonly" readonly tabindex="-1" value="${stock}">
                                <input type="hidden" class="warehouse" name="warehouse_id[]" value="${currentWarehouse}">
                            </td>
                            <td style="width:95px;min-width:95px;" class="col-qty-wrapper">
                                <input type="number" step="any" class="form-control carton-qty sales-qty text-start" name="carton_qty[]" placeholder="0" min="0" value="${qty}" style="width: 100%; height: 26px; font-size: 0.85rem; padding-left: 6px;">
                                <input type="hidden" name="sales_qty[]" class="sales-qty-mirror" value="${qty}">
                            </td>
                            <td class="col-pieces">
                                <input type="text" class="form-control total-pieces text-end input-readonly" name="total_pieces[]" readonly placeholder="0" tabindex="-1" value="${qty}">
                                <input type="hidden" class="sales-qty-hidden" name="qty[]" value="${qty}">
                            </td>
                            <td class="col-price-p">
                                <input type="text" class="form-control visible-price retail-price text-end" name="retail_price[]" value="${price.toFixed(2)}" placeholder="0" style="width: 100%;">
                                <input type="hidden" class="price-per-piece" name="price_per_piece[]" value="${price.toFixed(2)}">
                            </td>
                            <td class="col-disc">
                                <div class="discount-wrapper">
                                    <input type="number" class="form-control discount-value text-end" name="discount_percentage[]" value="${discVal > 0 ? discVal : ''}" placeholder="0">
                                    <input type="hidden" class="discount-type-field" name="discount_type[]" value="${discType}">
                                    <button type="button" class="btn btn-outline-secondary discount-toggle" data-type="${discType}" tabindex="-1">${discType === 'percent' ? '%' : 'Rs'}</button>
                                </div>
                                <input type="hidden" class="discount-amount" name="discount_amount[]" value="${discAmt.toFixed(2)}">
                            </td>
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

                // Initialize Select2 on any uninitialized row
                $('#salesTableBody tr').each(function() {
                    const $select = $(this).find('.product-select');
                    if (!$select.data('select2')) {
                        initProductSelect2($select);
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

            // Initial calculations & hide page loader
            updateGrandTotals();
            $('#pageLoader').addClass('d-none').css('display', 'none');
        });
    </script>
@endsection

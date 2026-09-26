@extends('admin_panel.layout.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* ================= RESPONSIVE SALES UI ================= */

        * {
            box-sizing: border-box;
        }

        /* table container - no scroll on mobile */
        .table-responsive {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 360px;
            -webkit-overflow-scrolling: touch;
        }

        /* base table width - responsive */
        .sales-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* 🔹 DISCOUNT COLUMN – THORI SI BARI */
        .sales-table td.large-col {
            min-width: 95px;
            padding: 4px;
        }

        /* 🔹 DISCOUNT LAYOUT */
        .discount-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: nowrap;
        }

        /* helper text for discount input — absolutely positioned to avoid layout shift */
        .discount-help {
            position: absolute;
            left: 0;
            bottom: -36px;
            font-size: 0.85rem;
            line-height: 1;
            color: #dc3545;
            /* Bootstrap danger */
            background: rgba(255, 255, 255, 0.9);
            padding: 0 4px;
            border-radius: 3px;
        }

        .sales-table td {
            padding: 3px 4px !important;
            vertical-align: middle !important;
        }

        .sales-table tbody tr {
            min-height: 32px !important;
            height: 32px !important;
        }

        /* 🔹 INPUT – NOT TOO SMALL */
        .discount-wrapper .discount-value {
            width: 60px;
            min-width: 60px;
            font-size: 0.8rem;
            padding: 4px 6px;
        }

        /* 🔹 PLUS ICON – NEAT & SMALL */
        .discount-wrapper .discount-plus {
            width: 22px;
            height: 22px;
            padding: 0;
            font-size: 13px;
            line-height: 1;
        }

        /* 🔹 DROPDOWN */
        .discount-wrapper .discount-type {
            position: absolute;
            right: 0;
            top: 115%;
            width: 65px;
            font-size: 0.75rem;
            z-index: 30;
        }

        /* ---------- DESKTOP (>= 1200px) ---------- */
        @media (min-width: 1200px) {
            .sales-table {
                width: 100%;
            }
        }

        /* ---------- TABLET (992px - 1199px) ---------- */
        @media (max-width: 1199px) and (min-width: 992px) {
            .main-container {
                max-width: 100%;
                margin: 0 auto;
                padding: 1rem;
            }

            .sales-table {
                width: 100%;
            }

            .sales-table td.product-col {
                min-width: 130px;
            }

            .sales-table td.small-col {
                width: 80px;
            }

            .sales-table td.medium-col {
                width: 90px;
            }

            .minw-350 {
                min-width: 100%;
            }

            .d-flex.gap-3 {
                flex-direction: column;
                gap: 1rem !important;
            }

            .items-panel {
                width: 100%;
                min-width: 0;
            }
        }

        /* ---------- MOBILE (768px - 991px) ---------- */
        @media (max-width: 991px) {
            .main-container {
                max-width: 100%;
                margin: 0 auto;
                padding: 1rem;
            }

            .header-text {
                font-size: 1rem;
            }

            .btn {
                padding: .35rem .5rem;
            }

            /* stack header buttons */
            .d-flex.justify-content-between.align-items-center {
                flex-wrap: wrap;
                gap: 8px;
            }

            /* customer + invoice panel full width */
            .minw-350 {
                width: 100%;
                min-width: 0;
            }

            /* reduce input font */
            .form-control,
            .form-select {
                font-size: .8rem;
            }

            /* CRITICAL: Make table responsive */
            .sales-table {
                width: 100%;
                font-size: 0.75rem;
            }

            .sales-table td.product-col {
                min-width: 100px;
            }

            .sales-table td.small-col {
                width: 60px;
            }

            .sales-table td.medium-col {
                width: 70px;
            }

            .sales-table td.action-col {
                width: 50px;
            }

            .d-flex.gap-3 {
                flex-direction: column;
                gap: 1rem !important;
            }

            .items-panel {
                width: 100%;
                min-width: 0;
                flex-grow: 1;
            }
        }

        /* ---------- SMALL PHONES (<= 576px) ---------- */
        @media (max-width: 576px) {
            .main-container {
                max-width: 100%;
                margin: 0 auto;
                padding: 0.75rem;
            }

            .sales-table {
                font-size: 0.65rem;
                width: 100%;
            }

            .table {
                --bs-table-padding-y: 0.2rem;
                --bs-table-padding-x: 0.3rem;
            }

            .sales-table td.product-col {
                min-width: 90px;
            }

            .sales-table td.small-col {
                width: 50px;
            }

            .sales-table td.medium-col {
                width: 60px;
            }

            .discount-wrapper .discount-value {
                width: 50px;
                min-width: 50px;
            }

            .minw-350 {
                min-width: 0;
                width: 100%;
            }

            .items-panel {
                width: 100%;
                min-width: 0;
            }

            .p-3 {
                padding: 0.75rem !important;
            }
        }
    </style>
    <style>
        /* ====== REMOVE SCROLL BAR STYLING ====== */

        /* Disable ALL scrolling for table */
        .table-responsive {
            overflow: auto;
            max-height: 360px;
        }

        .items-panel {
            overflow: visible;
            width: 100%;
            min-width: 0;
        }

        .main-container {
            font-size: .85rem;
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
            padding: 1rem;
        }

        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .container-fluid {
            padding-left: 0;
            padding-right: 0;
            width: 100%;
            max-width: 100%;
        }

        .header-text {
            font-size: 1.1rem;
        }

        .form-control,
        .form-select,
        .btn {
            font-size: .85rem;
            padding: .4rem .6rem;
            height: auto;
        }

        .invalid-cell {
            background-color: #fff5f5 !important;
            /* soft red */
            border: 1px solid #e3342f !important;
            /* red border */
        }

        .invalid-select,
        .invalid-input {
            border-color: #e3342f !important;
            box-shadow: none !important;
        }

        .input-readonly {
            background: #f9fbff;
        }

        .section-title {
            font-weight: 700;
            color: #6c757d;
            letter-spacing: .3px;
        }

        .table {
            --bs-table-padding-y: .35rem;
            --bs-table-padding-x: .5rem;
            font-size: .85rem;
            width: 100%;
        }

        .table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8f9fa;
            text-align: center;
        }

        .minw-350 {
            min-width: 360px;
        }

        .w-70 {
            width: 70px
        }

        .w-90 {
            width: 90px
        }

        .w-110 {
            width: 110px
        }

        .w-120 {
            width: 120px
        }

        .w-150 {
            width: 150px
        }

        .totals-card {
            background: #fcfcfe;
            border: 1px solid #eee;
            border-radius: .5rem;
        }

        .totals-card .row+.row {
            border-top: 1px dashed #e5e7eb;
        }

        .badge-soft {
            background: #eef2ff;
            color: #3730a3;
        }

        /* ===== Excel-Like Compact Grid UI Overrides ===== */
        .sales-table {
            table-layout: fixed !important;
            width: 100% !important;
            min-width: 860px !important;
            border-collapse: collapse !important;
        }

        .sales-table th {
            background: #f1f5f9 !important;
            color: #334155 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.4px !important;
            padding: 5px 6px !important;
            border: 1px solid #cbd5e1 !important;
            white-space: nowrap !important;
        }

        .sales-table td {
            padding: 2px 4px !important;
            vertical-align: middle !important;
            border: 1px solid #e2e8f0 !important;
            height: 32px !important;
        }

        .sales-table tbody tr {
            height: 32px !important;
        }

        .sales-table .index-col     { width: 3%  !important; min-width: 35px  !important; text-align: center !important; }
        .sales-table .product-col   { width: 25% !important; min-width: 180px !important; }
        .sales-table .warehouse-col { width: 17% !important; min-width: 140px !important; }
        .sales-table .qty-col       { width: 7%  !important; min-width: 60px  !important; }
        .sales-table .unit-col      { width: 6%  !important; min-width: 50px  !important; }
        .sales-table .price-col     { width: 11% !important; min-width: 80px  !important; }
        .sales-table .disc-col      { width: 10% !important; min-width: 80px  !important; }
        .sales-table .discamt-col   { width: 7%  !important; min-width: 60px  !important; }
        .sales-table .amount-col    { width: 11% !important; min-width: 85px  !important; }
        .sales-table .action-col    { width: 3%  !important; min-width: 35px  !important; text-align: center !important; }

        /* Select2 Truncation & Height Fix */
        .sales-table td {
            position: relative;
        }

        .sales-table .select2-container {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
        }

        .sales-table .select2-container .select2-selection--single {
            height: 28px !important;
            min-height: 28px !important;
            display: flex !important;
            align-items: center !important;
            border-radius: 4px !important;
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
        }

        .sales-table .select2-container .select2-selection__rendered {
            line-height: 26px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            display: block !important;
            padding-right: 18px !important;
            padding-left: 4px !important;
            font-size: 12px !important;
            font-weight: 500 !important;
            color: #0f172a !important;
        }

        .sales-table .select2-container .select2-selection__arrow {
            height: 26px !important;
            top: 1px !important;
        }

        /* Inputs in Sales Table */
        .sales-table .form-control,
        .sales-table .form-select {
            height: 28px !important;
            min-height: 28px !important;
            font-size: 12px !important;
            padding: 2px 6px !important;
            border-radius: 4px !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
        }

        .sales-table .input-group-sm > .form-control,
        .sales-table .input-group-sm > .input-group-text,
        .sales-table .input-group-sm > .btn {
            height: 28px !important;
            padding: 2px 6px !important;
            font-size: 11px !important;
            border-radius: 4px !important;
        }

        .sales-table .discount-wrapper {
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
        }

        .sales-table .discount-wrapper .discount-value {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            flex: 1 1 auto !important;
            min-width: 0 !important;
        }

        .sales-table .discount-wrapper .discount-toggle {
            height: 28px !important;
            padding: 0 6px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            border: 1px solid #cbd5e1 !important;
            border-left: none !important;
            background: #f1f5f9 !important;
            color: #475569 !important;
        }

        .items-panel>.d-flex {
            flex-wrap: wrap;
            gap: 8px;
        }

        @media (max-width: 768px) {
            #btnAdd {
                width: 100%;
            }
        }

        /* Select2 dropdown height + scroll & soft modern hover styling */
        .select2-results__options {
            max-height: 250px;
            overflow-y: auto;
        }

        .select2-container--default .select2-results__option {
            padding: 6px 10px !important;
            border-bottom: 1px solid #f1f5f9;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #eff6ff !important;
            color: #0f172a !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] .product-title {
            color: #2563eb !important;
        }

        .select2-search--dropdown {
            padding: 6px !important;
            background-color: #f8fafc;
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 5px 10px !important;
            font-size: 12.5px !important;
        }

        /* ✅ MODAL STYLING - Ensure Modal is Fully Interactive */
        #branchSelectionModal .modal-content {
            pointer-events: auto !important;
            opacity: 1 !important;
            background-color: white;
        }

        #branchSelectionModal .modal-body,
        #branchSelectionModal .modal-footer {
            pointer-events: auto !important;
        }

        #branchSelectionDropdown {
            pointer-events: auto !important;
            cursor: pointer !important;
            background-color: #f8f9fa;
            border: 2px solid #dee2e6;
            padding: 0.75rem;
            font-size: 1rem;
        }

        #branchSelectionDropdown:focus {
            background-color: white;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        #branchConfirmBtn {
            pointer-events: auto !important;
            cursor: pointer !important;
            transition: all 0.2s ease;
        }

        #branchConfirmBtn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        #branchConfirmBtn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Ensure modal backdrop doesn't block interaction */
        .modal-backdrop {
            z-index: 1040;
        }

        #branchSelectionModal {
            z-index: 1050;
        }

        /* ===== CUSTOMER TOOLBAR UI FIXES & POLISH ===== */
        .customer-card-toolbar {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important;
        }

        /* Fix Select2 inside .customer-card-toolbar */
        .customer-card-toolbar .select2-container {
            width: 100% !important;
            max-width: 100% !important;
        }

        .customer-card-toolbar .select2-container .select2-selection--single {
            height: 31px !important;
            min-height: 31px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
        }

        .customer-card-toolbar .select2-container .select2-selection__rendered {
            line-height: 29px !important;
            font-size: 12px !important;
            padding-left: 8px !important;
            padding-right: 20px !important;
            color: #1e293b !important;
            font-weight: 500 !important;
            display: block !important;
        }

        .customer-card-toolbar .select2-container .select2-selection__arrow {
            height: 29px !important;
            top: 1px !important;
        }

        .customer-card-toolbar .form-control,
        .customer-card-toolbar .form-select {
            height: 31px !important;
            min-height: 31px !important;
            font-size: 12px !important;
            border-color: #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 3px 8px !important;
        }

        .customer-card-toolbar .input-group-sm > .form-control {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        .customer-card-toolbar .input-group-text {
            height: 31px !important;
            font-size: 11px !important;
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: #64748b !important;
            border-top-left-radius: 6px !important;
            border-bottom-left-radius: 6px !important;
        }

        /* Modern Radio Buttons */
        #partyTypeGroup .btn-outline-primary {
            color: #475569 !important;
            border-color: #cbd5e1 !important;
            background-color: #f8fafc !important;
            font-weight: 600 !important;
            font-size: 11px !important;
            padding: 2px 10px !important;
            transition: all 0.15s ease-in-out !important;
        }

        #partyTypeGroup .btn-check:checked + .btn-outline-primary {
            color: #ffffff !important;
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
            border-color: #2563eb !important;
            box-shadow: 0 2px 5px rgba(37, 99, 235, 0.3) !important;
        }
    </style>



    <div class="container-fluid px-2 py-2 font-sans">
        <div class="main-container bg-white border border-0 shadow-sm mx-auto p-3 rounded-3" style="background: #f8fafc;">

            <div id="alertBox" class="alert d-none mb-2" role="alert"></div>

            <form id="saleForm" autocomplete="off">
                @csrf
                <input type="hidden" id="booking_id" name="booking_id" value="">
                <input type="hidden" id="action" name="action" value="save">
                <input type="hidden" id="is_posted" name="is_posted" value="0">
                <input type="hidden" id="is_finalized" name="is_finalized" value="0">

                {{-- 1. TOP HEADER BAR --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-2 px-3 rounded-3 shadow-sm mb-3 border">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('sale.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm fw-semibold d-inline-flex align-items-center gap-2 text-dark border-secondary-subtle">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;">
                            <i class="fas fa-shopping-cart fs-6"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.3px;">New Sale / Booking</h5>
                            <p class="text-muted small mb-0 fw-medium" style="font-size: 11px;">Executive order generation, instant booking ledger, and multi-format dispatch invoicing</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                        <small class="text-muted fw-semibold me-2" id="entryDateTime"><i class="far fa-clock me-1 text-primary"></i> Entry: --</small>
                        <a href="{{ route('sale.settings.index') }}" target="_blank" class="btn btn-sm btn-outline-secondary border rounded-pill px-3 py-1 text-dark fw-semibold">
                            <i class="fas fa-cog me-1 text-warning"></i> Settings
                        </a>
                        <a href="{{ route('sale.index') }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-secondary fw-semibold">
                            <i class="fas fa-list me-1"></i> Sales List
                        </a>
                    </div>
                </div>

                {{-- 2. CUSTOMER TOOLBAR (SINGLE LINE LAYOUT) --}}
                <div class="customer-card-toolbar p-3 mb-3">
                    <input type="hidden" name="Invoice_date" id="Invoice_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="estimated_delivery_date" id="estimated_delivery_date" value="{{ date('Y-m-d', strtotime('+15 days')) }}">
                    <input type="hidden" name="Invoice_no" id="Invoice_no" value="{{ $nextInvoiceNumber }}">

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <label class="form-label fw-bold text-uppercase small text-primary mb-0 me-1" style="letter-spacing: 0.5px;">
                                <i class="fas fa-user-circle me-1"></i> Customer
                            </label>
                            <div class="btn-group btn-group-sm" role="group" id="partyTypeGroup">
                                <input type="radio" class="btn-check" name="partyType" id="typeCustomers" value="credit" checked>
                                <label class="btn btn-outline-primary btn-sm py-0 px-2" for="typeCustomers">Credit</label>
                                <input type="radio" class="btn-check" name="partyType" id="typeWalkin" value="cash">
                                <label class="btn btn-outline-primary btn-sm py-0 px-2" for="typeWalkin">Cash</label>
                                <input type="radio" class="btn-check" name="partyType" id="typewalking" value="walking">
                                <label class="btn btn-outline-primary btn-sm py-0 px-2" for="typewalking">Walking</label>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark border py-1 px-2">Inv#: <strong class="text-primary" id="invoiceNoText">{{ $nextInvoiceNumber }}</strong></span>
                            <button id="clearCustomerData" type="button" class="btn btn-sm btn-light border text-danger py-0 px-2 fw-semibold">
                                <i class="fas fa-eraser"></i> Clear
                            </button>
                        </div>
                    </div>

                    {{-- CUSTOMER SELECT / WALKING INPUT & SINGLE-LINE SUMMARY --}}
                    <div class="row g-2 align-items-center">
                        {{-- CUSTOMER SEARCH DROPDOWN --}}
                        <div class="col-lg-3 col-md-3 col-12 position-relative" id="customerSelectWrapper">
                            <div class="d-flex align-items-center gap-1">
                                <div class="flex-grow-1 position-relative" style="min-width: 0;">
                                    <select class="form-select js-customer border-primary-subtle rounded-3" id="customerSelect" style="width: 100%;">
                                        <option selected disabled>Search Customer...</option>
                                    </select>
                                </div>
                                <button type="button" id="btnOpenAddCustomerModal" class="btn btn-primary btn-sm px-2.5 fw-semibold rounded-3 text-white d-inline-flex align-items-center justify-content-center shadow-sm" style="background:#2563eb !important; border-color:#2563eb !important; text-decoration: none; height: 31px; white-space: nowrap;">
                                    <i class="fas fa-plus me-1"></i> Add
                                </button>
                            </div>
                            <small class="text-muted position-absolute" id="customerCountHint" style="font-size: 10px; top: 100%; left: 0; line-height: 1; margin-top: 2px; z-index: 5;"></small>
                        </div>

                        {{-- VISIBLE / EDITABLE CUSTOMER NAME INPUT --}}
                        <div class="col-lg-3 col-md-3 col-12" id="customerDisplayWrapper" style="display: none;">
                            <input type="text" class="form-control form-control-sm border-primary-subtle fw-bold text-dark rounded-3" id="customerDisplay" name="customer_display" placeholder="Walking Customer Name" value="Walking Customer">
                        </div>

                        <input type="hidden" id="customer_id" name="customer_id" value="">
                        <input type="hidden" id="customer" name="customer" value="">

                        {{-- PHONE # --}}
                        <div class="col-lg-2 col-md-2 col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-phone small"></i></span>
                                <input type="text" class="form-control form-control-sm" id="tel" name="tel" placeholder="Phone #">
                            </div>
                        </div>

                        {{-- ADDRESS --}}
                        <div class="col-lg-4 col-md-4 col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-map-marker-alt small"></i></span>
                                <input type="text" class="form-control form-control-sm" id="address" name="address" placeholder="Address">
                            </div>
                        </div>

                        {{-- PREV BALANCE & CREDIT LIMIT (SINGLE LINE) --}}
                        <div class="col-lg-3 col-md-3 col-12 ms-auto" id="balanceFieldsContainer">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <span class="small text-muted fw-bold">Prev Bal:</span>
                                <input type="text" class="form-control form-control-sm text-end fw-bold text-danger rounded-3" id="previousBalance" value="0" style="width: 80px;" readonly>

                                <span class="small text-muted fw-bold ms-1">Limit:</span>
                                <input type="text" class="form-control form-control-sm text-end fw-bold text-primary rounded-3" id="creditLimit" value="0" style="width: 75px;" readonly>
                                <small id="noCreditLimitMsg" class="text-success fw-bold ms-1" style="display:none;">Infinite</small>
                                <input type="hidden" id="noCreditLimit" value="0">
                            </div>
                        </div>
                    </div>

                    {{-- SECONDARY ROW (Manual Inv#, Branch, Salesman, Remarks) --}}
                    <div class="mt-2 pt-2 border-top">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-2 col-6">
                                <input type="text" class="form-control form-control-sm" name="Invoice_main" placeholder="Manual Inv#">
                            </div>
                            @if (Auth::user() && Auth::user()->hasRole('super admin'))
                            <div class="col-md-2 col-6">
                                <select class="form-select form-select-sm" name="branch_id" id="branch_id">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->branch_name ?? $b->name ?? 'Branch ' . $b->id }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                                <input type="hidden" name="branch_id" value="{{ Auth::user()->branch_id ?? 1 }}">
                            @endif
                            <div class="col-md-3 col-6">
                                <select class="form-select form-select-sm select2" name="salesman_id" id="salesman_id">
                                    <option value="">Select Salesman</option>
                                    @foreach($salesmen as $sm)
                                        <option value="{{ $sm->id }}" {{ (isset($booking) && $booking->salesman_id == $sm->id) ? 'selected' : '' }}>
                                            {{ $sm->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5 col-12">
                                <input type="text" class="form-control form-control-sm" id="remarks" name="remarks" placeholder="Order Remarks / Notes">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. MAIN SECTION: ORDER ITEMS (FULL WIDTH 100%) --}}
                <div class="bg-white p-3 rounded-4 shadow-sm border mb-4">
                    {{-- PANEL HEADER --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                        <div class="d-flex align-items-center">
                            <span class="bg-primary rounded-pill me-2" style="width: 4px; height: 20px; display: inline-block;"></span>
                            <h5 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.3px;">
                                ORDER ITEMS <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1 fs-6" id="itemsCountBadge">(1)</span>
                            </h5>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1" id="btnQuickProductsModal" data-bs-toggle="modal" data-bs-target="#productSearchModal">
                                <i class="fas fa-th"></i> Quick Products Panel
                            </button>
                            <button type="button" class="btn btn-outline-warning text-dark btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1" id="btnCreateProduct">
                                <i class="fas fa-bolt text-warning"></i> Create Product
                            </button>
                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1" id="btnAdd" style="background:#2563eb !important; border-color:#2563eb !important;">
                                <i class="fas fa-plus"></i> Add Row
                            </button>
                        </div>
                    </div>

                    {{-- ITEMS TABLE --}}
                    <div class="table-responsive rounded-3 border" style="max-height: 480px; overflow-y: auto;">
                        <table class="table table-hover align-middle sales-table mb-0">
                            <thead style="background: #f1f5f9; position: sticky; top: 0; z-index: 10;">
                                <tr class="text-uppercase small fw-bold text-secondary">
                                    <th class="index-col text-center" style="width: 35px;">#</th>
                                    <th class="product-col">PRODUCT</th>
                                    <th class="warehouse-col">WAREHOUSE</th>
                                    <th class="qty-col text-center">QTY</th>
                                    <th class="unit-col text-center">UNIT</th>
                                    <th class="price-col text-end">PRICE</th>
                                    <th class="disc-col text-center" @if(isset($saleSettings) && !$saleSettings->show_line_discount) style="display: none;" @endif>DISCOUNT</th>
                                    <th class="discamt-col text-end" @if(isset($saleSettings) && !$saleSettings->show_gst) style="display: none;" @endif>GST %</th>
                                    <th class="amount-col text-end">AMOUNT</th>
                                    <th class="action-col text-center" style="width: 35px;">×</th>
                                </tr>
                            </thead>
                            <tbody id="salesTableBody">
                                {{-- Dynamically populated rows --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- TABLE FOOTER SUMMARY BAR --}}
                    <div class="d-flex justify-content-end align-items-center pt-3 mt-2 border-top">
                        <div class="bg-light px-4 py-2 rounded-3 border d-flex align-items-center gap-3">
                            <span class="fw-bold text-uppercase text-secondary small">INVOICE TOTAL:</span>
                            <span class="fs-5 fw-bold text-primary" id="totalAmount">0.00</span>
                        </div>
                    </div>
                </div>

                {{-- 4. BOTTOM SECTION: ADVANCE PAYMENT & ORDER SUMMARY (BELOW TABLE) --}}
                <div class="row g-4 mb-4">
                    {{-- ADVANCE / PAYMENT CARD --}}
                    <div class="col-lg-6 col-md-12">
                        <div class="bg-white p-4 rounded-4 shadow-sm border h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                    <i class="fas fa-wallet text-success"></i> Advance / Payment
                                </h5>
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill py-0 px-2 fw-semibold" id="btnAddRV">
                                        <i class="fas fa-plus"></i> Add Account
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2 fw-semibold" id="btnSplitPayment">
                                        <i class="fas fa-layer-group"></i> Split
                                    </button>
                                </div>
                            </div>

                            <div id="rvWrapper" class="d-flex flex-column gap-2 mb-3">
                                <div class="d-flex gap-2 align-items-center rv-row">
                                    <select class="form-select form-select-sm rv-account rounded-3 border-secondary-subtle" name="receipt_account_id[]">
                                        @foreach ($accounts as $acc)
                                            <option value="" disabled selected>Select Account</option>
                                            <option value="{{ $acc->id }}">{{ $acc->title }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" class="form-control form-control-sm text-end rv-amount rounded-3 border-secondary-subtle fw-semibold" name="receipt_amount[]" placeholder="0.00" style="max-width:140px">
                                </div>
                            </div>

                            {{-- ADDITIONAL CHARGES & EXPECTED DAYS --}}
                            <div class="pt-3 border-top d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between align-items-center" id="overall_discount_wrapper" @if(isset($saleSettings) && !$saleSettings->show_overall_discount) style="display: none !important;" @endif>
                                    <span class="small text-muted fw-bold">Additional Disc</span>
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="text" class="form-control form-control-sm text-end rounded-3" name="additional_discount" id="discountPercent" value="0" style="width: 100px;">
                                        <button type="button" id="orderDiscountToggle" class="btn btn-outline-secondary btn-sm py-0 px-2" data-type="pkr">PKR</button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted fw-bold">Extra Charges</span>
                                    <input type="text" class="form-control form-control-sm text-end rounded-3" name="extra_charges" id="echarges" value="0" style="width: 100px;">
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted fw-bold">Expected Days</span>
                                    <input type="number" name="notify_me" id="notify_me" class="form-control form-control-sm rounded-3 text-end" placeholder="Days" min="0" max="365" style="width: 100px;">
                                </div>

                                {{-- HIDDEN MIRRORS --}}
                                <input type="hidden" name="subTotal1" id="subTotal1" value="0">
                                <input type="hidden" name="subTotal2" id="subTotal2" value="0">
                                <input type="hidden" name="discountAmount" id="discountAmount" value="0">
                                <input type="hidden" name="totalBalance" id="totalBalance" value="0">
                            </div>
                        </div>
                    </div>

                    {{-- ORDER SUMMARY CARD --}}
                    <div class="col-lg-6 col-md-12">
                        <div class="bg-white p-4 rounded-4 shadow-sm border h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                    <i class="fas fa-calculator text-primary"></i> Order Summary
                                </h5>
                                <span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold px-3 py-1 border border-primary-subtle">
                                    <i class="fas fa-circle me-1 small" style="font-size: 8px;"></i> Live
                                </span>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="text-secondary fw-medium">Invoice Total</span>
                                    <span class="fw-bold text-dark" id="tGross">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-1" id="summary_line_disc_row" @if(isset($saleSettings) && !$saleSettings->show_line_discount) style="display: none !important;" @endif>
                                    <span class="text-secondary fw-medium">Discount</span>
                                    <span class="fw-bold text-danger" id="tLineDisc">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-1" id="summary_gst_row" @if(isset($saleSettings) && !$saleSettings->show_gst) style="display: none !important;" @endif>
                                    <span class="text-secondary fw-medium">GST / Tax</span>
                                    <span class="fw-bold text-success" id="tOrderDisc">0.00</span>
                                </div>

                                {{-- NET TOTAL BLUE HIGHLIGHT BOX --}}
                                <div class="bg-primary-subtle p-3 rounded-3 border border-primary-subtle my-2 d-flex justify-content-between align-items-center" style="background-color: #eff6ff !important; border-color: #bfdbfe !important;">
                                    <span class="fw-bold text-primary fs-6">Net Total</span>
                                    <span class="fw-bold text-primary fs-4" id="tSub">0.00</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="text-secondary fw-medium">Advance Payment</span>
                                    <span class="fw-bold text-success" id="receiptsTotal">0.00</span>
                                </div>

                                {{-- BALANCE AMOUNT SOFT PINK HIGHLIGHT BOX --}}
                                <div class="bg-danger-subtle p-3 rounded-3 border border-danger-subtle my-1 d-flex justify-content-between align-items-center" style="background-color: #fff1f2 !important; border-color: #fecdd3 !important;">
                                    <span class="fw-bold text-danger fs-6">Balance Amount</span>
                                    <span class="fw-bold text-danger fs-4" id="tPayable">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. BOTTOM ACTION BUTTONS BAR --}}
                <div class="bg-white p-3 rounded-4 shadow-sm border mt-4 d-flex flex-wrap align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm" id="btnSave" style="background:#2563eb !important; border-color:#2563eb !important;">
                        <i class="fas fa-file-invoice"></i> Save Booking (Draft)
                    </button>

                    <button type="button" class="btn btn-success px-5 py-2.5 rounded-3 fw-bold fs-6 d-inline-flex align-items-center gap-2 shadow-sm" id="btnPosted2" style="background:#16a34a !important; border-color:#16a34a !important;" disabled>
                        <i class="fas fa-shopping-cart"></i> Process Sale / POS
                    </button>

                    {{-- 
                    <button type="button" class="btn btn-indigo px-4 py-2 rounded-3 fw-semibold text-white d-inline-flex align-items-center gap-2 shadow-sm" id="btnPosted3" style="background:#4f46e5 !important; border-color:#4f46e5 !important;">
                        <i class="fas fa-check-double"></i> Post & Print
                    </button>
                    <button type="button" class="btn btn-secondary px-3 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2" id="btnPrint">
                        <i class="fas fa-print"></i> Booking Invoice
                    </button>
                    <button type="button" class="btn btn-outline-danger px-3 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2" id="btnDelete">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                    <button type="button" class="btn btn-light border px-3 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2 text-dark" id="btnExit">
                        <i class="fas fa-times-circle"></i> Exit
                    </button>
                    --}}
                </div>
            </form>
        </div>
    </div>

    {{-- BRANCH SELECTION MODAL (Admin Users) --}}
    <div class="modal fade" id="branchSelectionModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">📍 Select Branch for Sale</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Branch</label>
                        <select class="form-select form-select-lg" id="branchSelectionDropdown" style="pointer-events: auto; cursor: pointer;">
                            <option selected disabled value="">-- Choose a branch --</option>
                        </select>
                        <small class="text-muted d-block mt-2">Admin users must select a branch for this sale transaction</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info btn-lg btn-confirm" id="branchConfirmBtn" style="pointer-events: auto; cursor: pointer;">✓ Confirm & Proceed</button>
                </div>
            </div>
        </div>
    </div>

    {{-- product search model --}}

    <div class="modal fade" id="productSearchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Search Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- Search input -->
                    <input type="text" id="productSearchInput" class="form-control mb-3"
                        placeholder="Search product by name...">

                    <!-- Product list -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="productSearchResults">
                                <tr>
                                    <td colspan="2" class="text-center">Type to search...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Warehouse Selection Modal -->
    <div class="modal fade" id="warehouseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="warehouseModalBody">
                    <!-- Warehouses will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const alertBox = document.getElementById('alertBox');
            if (!alertBox) return;

            const message = alertBox.innerText.trim();
            if (message === '') return;

            let icon = 'info';
            if (alertBox.classList.contains('alert-success')) icon = 'success';
            if (alertBox.classList.contains('alert-danger')) icon = 'error';
            if (alertBox.classList.contains('alert-warning')) icon = 'warning';

            Swal.fire({
                icon: icon,
                title: 'Message',
                text: message
            });

            alertBox.classList.add('d-none');
        });
    </script>
    <script>
        function faraz() {
            const productIds = [];
            $('#salesTableBody tr').each(function() {
                const pid = $(this).find('.product-select').val();
                if (pid) productIds.push(pid);
            });
            if (productIds.length === 0) {
                alert('Please select at least one product!');
                return;
            }
            $.ajax({
                url: '/get-warehouses/',
                type: 'GET',
                data: {
                    product_ids: productIds
                },
                success: function(res) {
                    // Build per-product rows with warehouse dropdowns
                    if (!Array.isArray(res) || res.length === 0) {
                        $('#warehouseModalBody').html(
                            '<div class="text-center">No warehouses available for selected products</div>');
                        $('#warehouseModal').modal('show');
                        return;
                    }

                    let html = '<form id="warehouseSelectForm">';
                    html +=
                        '<table class="table table-sm"><thead><tr><th>Product</th><th>Warehouse</th></tr></thead><tbody>';
                    res.forEach(function(row) {
                        const pid = row.product_id;
                        const pname = row.product_name;
                        const whs = row.warehouses || [];

                        html += `<tr data-product-id="${pid}">`;
                        html += `<td>${pname}</td>`;
                        html += `<td>`;
                        html +=
                            `<select class="form-control form-select warehouse-for-product" data-product-id="${pid}">`;
                        if (whs.length === 0) {
                            html += `<option value="">No stock in any warehouse</option>`;
                        } else {
                            html += `<option value="">Select warehouse</option>`;
                            whs.forEach(function(w) {
                                html +=
                                    `<option value="${w.warehouse_id}" data-qty="${w.quantity}">${w.warehouse_name} (qty: ${w.quantity})</option>`;
                            });
                        }
                        html += `</select>`;
                        html += `</td>`;
                        html += `</tr>`;
                    });
                    html += '</tbody></table>';
                    html +=
                        '<div class="text-end"><button type="button" id="warehouseApplyBtn" class="btn btn-primary">Apply</button></div>';
                    html += '</form>';

                    $('#warehouseModalBody').html(html);
                    $('#warehouseModal').modal('show');
                },
                error: function(err) {
                    console.error(err);
                    alert('Something went wrong fetching warehouses!');
                }
            });
        }

        // Apply per-product warehouse selections
        $(document).on('click', '#warehouseApplyBtn', function() {
            const mapping = {};
            $('.warehouse-for-product').each(function() {
                const pid = $(this).data('product-id');
                const wid = $(this).val();
                if (pid && wid) mapping[pid] = wid;
            });

            // write mapping into hidden inputs in table rows
            $('#salesTableBody tr').each(function() {
                const pid = $(this).find('.product-select').val();
                if (!pid) return;
                const wid = mapping[pid] || '';
                const $warehouseInput = $(this).find('.warehouse-id');
                $warehouseInput.attr('name', `warehouse_id[${pid}]`).val(wid);
            });

            $('#warehouseModal').modal('hide');
            
            // 🔹 Check if we're in print mode - DISABLED (btnPrint2 is commented out)
            // if (PRINT_AFTER_POST) {
            //     PRINT_AFTER_POST = false; // Reset flag
            //     ensureSaved().then(directPostAndPrint); // Post + Print flow
            // } else {
                ensureSaved().then(postNow); // Normal Post flow
            // }
        });
    </script>








    {{-- faarz memon --}}
    <script>
        let CURRENT_PRODUCT_ROW = null;
        // let PRINT_AFTER_POST = false; // 🔹 Flag to track if we should print after posting - DISABLED (btnPrint2 commented out)

        // faraz memon



        $(document).on('click', '.select-product', function() {

            if (!CURRENT_PRODUCT_ROW) return;

            const id = $(this).data('id');
            const name = $(this).data('name');
            const stock = $(this).data('stock');
            const price = $(this).data('price');

            // product dropdown me add + select
            const $productSelect = CURRENT_PRODUCT_ROW.find('.product-select');

            if ($productSelect.find(`option[value="${id}"]`).length === 0) {
                $productSelect.append(`<option value="${id}">${name}</option>`);
            }

            $productSelect.val(id).trigger('change');

            // stock & price set
            CURRENT_PRODUCT_ROW.find('.stock').val(stock).data('available-stock', stock);
            CURRENT_PRODUCT_ROW.find('.sales-qty').data('available-stock', stock);
            CURRENT_PRODUCT_ROW.find('.retail-price').val(price);

            // modal close
            $('#productSearchModal').modal('hide');

            // qty par focus
            setTimeout(() => {
                CURRENT_PRODUCT_ROW.find('.sales-qty').focus();
            }, 200);
        });
    </script>










    {{-- faarz memon --}}


    <script>
        window.RECEIPT_ACCOUNTS = @json($accounts);
        // products list sent from controller (branch‑filtered for non‑super admins)
        window.AVAILABLE_PRODUCTS = @json($products ?? []);
        // Branches for admin users
        window.BRANCHES = @json($branches ?? []);
        // Warehouse stocks for client-side validation
        window.WAREHOUSE_STOCKS = @json($warehouseStocks ?? []);
        // Check if current user is admin
        window.IS_ADMIN = {{ Auth::user() && Auth::user()->hasRole('super admin') ? 'true' : 'false' }};
        // Sale Screen display settings
        window.saleSettings = {
            show_gst: {{ (isset($saleSettings) && $saleSettings->show_gst) ? 'true' : 'false' }},
            show_line_discount: {{ (isset($saleSettings) && $saleSettings->show_line_discount) ? 'true' : 'false' }},
            show_overall_discount: {{ (isset($saleSettings) && $saleSettings->show_overall_discount) ? 'true' : 'false' }}
        };
        // Current user's branch ID
        window.USER_BRANCH_ID = {{ Auth::user()->branch_id ?? 1 }};
        
        // ✅ Booking prefill data (passed from SaleController::convertFromBooking)
        window.BOOKING_DATA = @json($booking ?? null);
        window.BOOKING_CUSTOMER = @json($booking_customer ?? null);
        window.BOOKING_ITEMS = @json($bookingItems ?? []);
    </script>
    <script>
        function loadAccountsInto($select) {

            const currentVal = $select.val(); // 🔒 preserve selection
            let usedAccounts = [];

            $('.rv-account').each(function() {
                const val = $(this).val();
                if (val && this !== $select[0]) {
                    usedAccounts.push(String(val));
                }
            });

            let html = '<option value="">Select account</option>';

            window.RECEIPT_ACCOUNTS.forEach(function(acc) {
                const accId = String(acc.id);

                if (!usedAccounts.includes(accId) || accId === String(currentVal)) {
                    html += `<option value="${accId}">${acc.title}</option>`;
                }
            });

            $select.html(html);

            // 🔥 restore selected value
            if (currentVal) {
                $select.val(currentVal);
            }
        }
    </script>


    <script>
        $(document).ready(function() {
            loadAccountsInto($('.rv-account').first());
        });
    </script>

    <!--fgdffhjkjkhgkhkh  -->

    <script>
        $(document).ready(function() {
            
            // ✅ NEW: Function to prefill form with booking data
            function prefillFormWithBooking() {
                if (!window.BOOKING_DATA || !window.BOOKING_CUSTOMER) {
                    console.log('📭 No booking data to prefill');
                    return;
                }

                console.log('🔍 Prefilling form with booking data:', {
                    booking: window.BOOKING_DATA,
                    customer: window.BOOKING_CUSTOMER,
                    items: window.BOOKING_ITEMS
                });

                // === 1. SET BOOKING ID ===
                $('#booking_id').val(window.BOOKING_DATA.id || '');

                // === 1b. SET BRANCH (for Admin users) ===
                if (window.BOOKING_DATA.branch_id) {
                    $('#branch_id').val(window.BOOKING_DATA.branch_id);
                    console.log('🏢 Branch set:', window.BOOKING_DATA.branch_id);
                }

                // === 2. SET CUSTOMER TYPE ===
                const customerType = window.BOOKING_CUSTOMER.customer_type || 'credit';
                $(`input[name="partyType"][value="${customerType}"]`).prop('checked', true);

                // === 3. LOAD CUSTOMERS WITH AUTO-SELECT ===
                // This replaces the old timeout-based selection
                loadCustomersByType(customerType, window.BOOKING_CUSTOMER.id);

                // === 4. POPULATE CUSTOMER FIELDS ===
                if (window.BOOKING_CUSTOMER.id) {
                    const customerId = window.BOOKING_CUSTOMER.id;
                    $('#customer_id').val(customerId);
                        
                        const customerName = window.BOOKING_CUSTOMER.customer_name || 
                                           window.BOOKING_CUSTOMER.name || '';
                        $('#customerDisplay').val(customerName);
                        $('#customer').val(customerId);

                        // Load customer details (address, phone, balance, credit limit)
                        $.ajax({
                            url: `/customers/${customerId}`,
                            type: 'GET',
                            success: function(customer) {
                                $('#address').val(customer.address || '');
                                $('#tel').val(customer.phone_no || '');
                                $('#previousBalance').val(customer.previous_balance || '0');
                                $('#creditLimit').val(customer.credit_limit || '0');
                                console.log('✅ Customer details loaded:', customer);
                            },
                            error: function(err) {
                                console.log('⚠️ Could not load customer details:', err);
                                // Use data from booking_customer as fallback
                                $('#address').val(window.BOOKING_CUSTOMER.address || '');
                                $('#tel').val(window.BOOKING_CUSTOMER.mobile || '');
                            }
                        });
                    } else if (customerType === 'walking') {
                        // Walking customer
                        const customerName = window.BOOKING_CUSTOMER.customer_name || 
                                           window.BOOKING_CUSTOMER.name || 
                                           window.BOOKING_CUSTOMER.walking_customer_name || '';
                        $('#customerDisplay').val(customerName).prop('readonly', false);
                        $('#customer').val(customerName);
                        
                        $('#address').val(window.BOOKING_CUSTOMER.address || '');
                        $('#tel').val(window.BOOKING_CUSTOMER.mobile || '');
                    }
                    
                    $('#remarks').val(window.BOOKING_DATA.remarks || '');

                    // === 5. PREFILL ITEMS TABLE ===
                    if (Array.isArray(window.BOOKING_ITEMS) && window.BOOKING_ITEMS.length > 0) {
                        console.log(`📦 Adding ${window.BOOKING_ITEMS.length} items to table...`);
                        
                        // ✅ CLEAR EXISTING EMPTY ROWS FROM INIT
                        // Remove all existing rows (from init) so we have a clean slate
                        $('#salesTableBody').empty();
                        console.log('🧹 Cleared existing rows');
                        
                        window.BOOKING_ITEMS.forEach((item, index) => {
                            // Add new row for each booking item
                            addNewRow();

                            // Get the newly added row (should be at current length - 1)
                            const $newRow = $('#salesTableBody tr').last();

                            // Set product
                            const productId = item.product_id;
                            const $productSelect = $newRow.find('.product-select');

                            // Add option if not exists
                            if ($productSelect.find(`option[value="${productId}"]`).length === 0) {
                                $productSelect.append(
                                    `<option value="${productId}">${item.item_name || ''}</option>`
                                );
                            }

                            $productSelect.val(productId).trigger('change');

                            // Set stock
                            $newRow.find('.stock').val(item.onhand_qty || 0);

                            // Set quantity
                            $newRow.find('.sales-qty').val(item.qty || 0);

                            // Set price
                            $newRow.find('.retail-price').val(parseFloat(item.price || 0).toFixed(2));

                            // Set discount (handle both percent and amount)
                            const $discountInput = $newRow.find('.discount-value');
                            const $discountBtn = $newRow.find('.discount-toggle');
                            const $discountAmount = $newRow.find('.discount-amount');
                            const $discountTypeField = $newRow.find('.discount-type-field');

                            console.log(`📦 Item ${index}: discount_type=${item.discount_type}, discount=${item.discount}, discount_percent=${item.discount_percent}`);
                            
                            if (item.discount_type === 'percent' || item.discount_percent > 0) {
                                $discountBtn.data('type', 'percent').attr('data-type', 'percent').text('%');
                                $discountTypeField.val('percent');
                                $discountInput.val(parseFloat(item.discount_percent || 0).toFixed(2));
                                $discountAmount.val('0');
                            } else {
                                $discountBtn.data('type', 'pkr').attr('data-type', 'pkr').text('PKR');
                                $discountTypeField.val('pkr');
                                // Set the input value to the per-unit discount
                                $discountInput.val(parseFloat(item.discount || 0).toFixed(2));
                                $discountAmount.val(parseFloat(item.discount_amount || 0).toFixed(2));
                            }

                            // Set warehouse if available
                            if (item.warehouse_id) {
                                $newRow.find('.warehouse-id')
                                    .attr('name', `warehouse_id[${productId}]`)
                                    .val(item.warehouse_id);
                            }

                            // ✅ DIRECTLY COMPUTE THE ROW
                            // Call computeRow directly to ensure calculations happen
                            // This will:
                            // 1. Calculate discount amount based on discount % or PKR
                            // 2. Calculate final amount (gross - discount)
                            // 3. Format all display values properly
                            computeRow($newRow, false, true);
                        });

                        console.log(`✅ ${window.BOOKING_ITEMS.length} items prefilled successfully`);
                    }

                    // === 6. RECALCULATE TOTALS ===
                    updateGrandTotals();
                    
                    // === 7. UPDATE BUTTON STATES ===
                    // Refresh button enable/disable states based on new item count
                    refreshPostedState();

                    console.log('✅ Form prefilled with booking data');
            }
            
            function init() {
                addNewRow();
                // If super-admin (branch selector exists), check if branch is already selected
                const branchEl = document.getElementById('branch_id');
                if (branchEl && branchEl.value) {
                    // Branch already selected on page load → load customers
                    loadCustomersByType('credit');
                } else if (branchEl) {
                    // No branch selected yet → show prompt
                    $('#customerSelect').html('<option selected disabled>Select branch first</option>').prop('disabled', true);
                } else {
                    // Non super-admin → load normally
                    loadCustomersByType('credit');
                }
                // loadAccountsInto($('.rv-account').first());
                updateGrandTotals();
                refreshPostedState();
            }

            init();
            
            // ✅ PREFILL WITH BOOKING DATA AFTER INIT
            if (window.BOOKING_DATA) {
                console.log('📥 Preparing to prefill with booking data...');
                setTimeout(() => {
                    prefillFormWithBooking();
                }, 800);
            }
            
            // ✅ NEW: Fetch and restore booking state on page load
            // Try multiple ways to get booking_id:
            // 1. URL parameter: ?booking_id=123
            // 2. localStorage: stored after previous save
            
            const urlParams = new URLSearchParams(window.location.search);
            let bookingIdParam = urlParams.get('booking_id');
            
            // Fallback to localStorage if no URL param
            if (!bookingIdParam) {
                bookingIdParam = localStorage.getItem('current_booking_id');
            }
            
            if (bookingIdParam) {
                console.log('📥 Loading booking state:', bookingIdParam);
                $.get('/booking/check-posted-state/' + bookingIdParam)
                    .done(function(res) {
                        if (res && res.booking) {
                            $('#booking_id').val(res.booking.id);
                            $('#is_posted').val(res.booking.is_posted ? '1' : '0');
                            $('#is_finalized').val(res.booking.is_finalized ? '1' : '0');
                            localStorage.setItem('current_booking_id', res.booking.id);
                            console.log('✅ Booking state restored:', res.booking);
                            refreshPostedState();
                        }
                    })
                    .fail(function(err) {
                        console.log('⏭️ Booking state endpoint error or booking not found');
                        localStorage.removeItem('current_booking_id');
                    });
            }
            // 🔹 Load customers on page load
            // loadCustomersByType('customer');

            // 🔹 Change customer type (radio)
            $(document).on('change', 'input[name="partyType"]', function() {
                $('#customerSelect').val('');
                if ($('#customerSelect').hasClass('select2-hidden-accessible')) {
                    $('#customerSelect').val(null).trigger('change');
                }
                $('#address,#tel,#remarks').val('');
                $('#previousBalance').val('0');
                $('#creditLimit').val('0');
                $('#customer_id').val('');
                $('#customer').val('');

                // 🔹 Toggle fields based on type
                if (this.value === 'cash') {
                    // CASH CUSTOMER: Same as credit but NO balance fields
                    $('#balanceFieldsContainer').hide();
                    $('#customerSelectWrapper').show();
                    $('#customerDisplayWrapper').hide();
                    $('#customerDisplay').val('').prop('readonly', true);
                    loadCustomersByType(this.value);
                    refreshPostedState();
                } else if (this.value === 'walking') {
                    // WALKING CUSTOMER: Manual entry, no balance fields
                    $('#balanceFieldsContainer').hide();
                    $('#customerSelectWrapper').hide();
                    $('#customerDisplayWrapper').show();
                    $('#customerDisplay').val('Walking Customer').prop('readonly', false);
                    $('#customer').val('Walking Customer');
                    loadCustomersByType('walking'); // Clear any previous selections
                    refreshPostedState();
                } else {
                    // CREDIT CUSTOMER: Full details with balance fields
                    $('#balanceFieldsContainer').show();
                    $('#customerSelectWrapper').show();
                    $('#customerDisplayWrapper').hide();
                    $('#customerDisplay').val('').prop('readonly', true);
                    loadCustomersByType(this.value);
                    refreshPostedState();
                }
            });

            // 🔹 When walking customer types their name, copy to hidden field for ajaxsave
            $(document).on('input', '#customerDisplay', function() {
                if ($('input[name="partyType"]:checked').val() === 'walking') {
                    $('#customer').val($(this).val());
                }
            });

            // 🔹 Initialize on page load
            const initialPartyType = $('input[name="partyType"]:checked').val() || 'credit';
            if (initialPartyType === 'walking') {
                $('#balanceFieldsContainer').hide();
                $('#customerSelectWrapper').hide();
                $('#customerDisplayWrapper').show();
                if (!$('#customerDisplay').val()) {
                    $('#customerDisplay').val('Walking Customer');
                }
                $('#customerDisplay').prop('readonly', false);
                $('#customer').val($('#customerDisplay').val() || 'Walking Customer');
            } else if (initialPartyType === 'cash') {
                $('#balanceFieldsContainer').hide();
                $('#customerSelectWrapper').show();
                $('#customerDisplayWrapper').hide();
            } else {
                $('#balanceFieldsContainer').show();
                $('#customerSelectWrapper').show();
                $('#customerDisplayWrapper').hide();
            }

            // 🔹 Load customers list
            function loadCustomersByType(type, idToSelect = null) {
                // For walking customers, don't load from API (no pre-defined customers)
                if (type === 'walking') {
                    $('#customerSelect').html('<option selected disabled>N/A</option>')
                        .prop('disabled', true);
                    return;
                }

                // If branch selector present, include branch_id parameter; if not selected, prompt user
                const branchEl = document.getElementById('branch_id');
                let params = { type: type };
                if (branchEl) {
                    const bid = branchEl.value;
                    if (!bid) {
                        $('#customerSelect').html('<option selected disabled>Select branch first</option>').prop('disabled', true);
                        return;
                    }
                    params.branch_id = bid;
                }

                // alert('loadCustomersByType CALLED → ' + type);
                $('#customerSelect')
                    .prop('disabled', true)
                    .html('<option selected disabled>Loading…</option>');

                $.get('{{ route('salecustomers.index') }}', params, function(data) {

                    let html = '<option value="">-- Select --</option>';

                    if (data.length > 0) {
                        data.forEach(row => {
                            // show customer name first for clarity
                            const label = (row.customer_name || '(No name)') + ' — ' + (row
                                .customer_id || '');
                            html += `<option value="${row.id}">` + label + `</option>`;
                        });
                        $('#customerCountHint').text(data.length + ' record(s) found');
                    } else {
                        html += '<option disabled>No record found</option>';
                        $('#customerCountHint').text('No record found');
                    }

                    // Destroy Select2 before replacing HTML (so it can reinitialize on new options)
                    if ($('#customerSelect').hasClass('select2-hidden-accessible')) {
                        $('#customerSelect').select2('destroy');
                    }
                    $('#customerSelect').html(html).prop('disabled', false);
                    // Reinitialize Select2 so it picks up the new options
                    $('#customerSelect').select2({ placeholder: '-- Select Customer --', allowClear: true, width: '100%' });

                    // ✅ AUTO-SELECT IF ID PROVIDED
                    if (idToSelect) {
                        console.log('🎯 Auto-selecting customer:', idToSelect);
                        $('#customerSelect').val(idToSelect).trigger('change');
                    }
                });

                // If branch selector present, bind change to reload customers, salesmen, accounts, invoice number, and refresh product rows
                if (document.getElementById('branch_id')) {
                    $('#branch_id').off('change.branchCustomer').on('change.branchCustomer', function() {
                        const newBranchId = $(this).val();
                        if (!newBranchId) return;

                        // 1. Clear previous customer data
                        if ($('#customerSelect').hasClass('select2-hidden-accessible')) {
                            $('#customerSelect').val(null).trigger('change');
                        } else {
                            $('#customerSelect').val('');
                        }
                        $('#customer_id, #customer, #customerDisplay').val('');
                        $('#address, #tel, #remarks').val('');
                        $('#previousBalance').val('0.00');
                        $('#creditLimit').val('0.00');
                        $('#noCreditLimit').val(0);
                        $('#noCreditLimitMsg').hide();
                        $('#customerCountHint').text('');

                        // 2. Reload Customers for newly selected branch
                        const currentType = $('input[name="partyType"]:checked').val() || 'credit';
                        loadCustomersByType(currentType);

                        // 3. Dynamic Next Invoice Number for branch
                        $.get('/get-branch-invoice-no/' + newBranchId, function(res) {
                            if (res && res.invoice_no) {
                                $('#Invoice_no').val(res.invoice_no);
                                $('input[name="Invoice_no"]').val(res.invoice_no);
                                console.log('✅ Updated Invoice No for branch', newBranchId, res.invoice_no);
                            }
                        });

                        // 4. Dynamic Salesmen for branch
                        $.get('/get-branch-salesmen/' + newBranchId, function(salesmen) {
                            const $smSelect = $('#salesman_id');
                            let html = '<option value="">Select Salesman</option>';
                            if (Array.isArray(salesmen) && salesmen.length > 0) {
                                salesmen.forEach(function(sm) {
                                    html += `<option value="${sm.id}">${sm.name}</option>`;
                                });
                            }
                            $smSelect.html(html);
                            if ($smSelect.hasClass('select2-hidden-accessible')) {
                                $smSelect.select2('destroy');
                            }
                            $smSelect.select2({ placeholder: 'Select Salesman', allowClear: true, width: '100%' });
                            console.log('✅ Updated Salesmen for branch', newBranchId);
                        });

                        // 5. Dynamic Accounts for branch
                        $.get('/get-branch-accounts/' + newBranchId, function(accounts) {
                            if (Array.isArray(accounts)) {
                                window.RECEIPT_ACCOUNTS = accounts;
                                $('.rv-account').each(function() {
                                    loadAccountsInto($(this));
                                });
                                console.log('✅ Updated Accounts for branch', newBranchId, accounts.length);
                            }
                        });

                        // 6. 🔄 Refresh stock & prices for all existing product rows
                        $('#salesTableBody tr').each(function() {
                            const $row = $(this);
                            const productId = $row.find('.product-select').val();
                            if (productId) {
                                $.get('/get-product-details/' + productId, { branch_id: newBranchId }, function(data) {
                                    if (data && data.product) {
                                        const price = parseFloat(data.product.retail_price ?? data.product.price ?? 0).toFixed(2);
                                        const stockQty = data.available_stock !== undefined ? data.available_stock : 
                                                        ((data.product.stock && (data.product.stock.qty ?? data.product.stock)) || 0);

                                        let breakdownText = 'Warehouse Stock Breakdown:\n-------------------------\n';
                                        if (data.warehouse_breakdown && data.warehouse_breakdown.length > 0) {
                                            data.warehouse_breakdown.forEach(function(wb) {
                                                breakdownText += `• ${wb.warehouse_name}: ${wb.quantity}\n`;
                                            });
                                            breakdownText += `-------------------------\nTotal Branch Stock: ${stockQty}`;
                                        } else {
                                            breakdownText += `Total Branch Stock: ${stockQty}`;
                                        }

                                        $row.find('.retail-price').val(price);
                                        $row.find('.stock').val(stockQty).data('available-stock', stockQty).attr('title', breakdownText);
                                        $row.find('.sales-qty').data('available-stock', stockQty);
                                        computeRow($row);
                                        updateGrandTotals();
                                        refreshPostedState();
                                    }
                                });
                            }
                        });
                    });
                }
            }

            // 🔹 Central function to load customer details by DB id
            function loadCustomerDetails(id) {
                if (!id) return;
                $('#customer_id').val(id);
                $('#customer').val(id);

                $.ajax({
                    url: '{{ route('salecustomers.show', '__ID__') }}'.replace('__ID__', id),
                    type: 'GET',
                    dataType: 'json',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(d) {
                        console.log('✅ Customer details loaded:', d);

                        // Populate customer fields
                        $('#address').val(d.address || '');
                        $('#tel').val(d.mobile || '');
                        $('#remarks').val(d.remarks || '');
                        $('#creditLimit').val(d.credit_limit || '0');

                        // Display customer name — code in the visible readonly field
                        $('#customerDisplay').val((d.customer_name || '') + ' — ' + (d.customer_id || ''));

                        // Previous balance from ledger (closing_balance) or opening_balance fallback
                        let previousBalance = parseFloat(d.closing_balance || d.opening_balance || 0) || 0;
                        $('#previousBalance').val(previousBalance.toFixed(2));

                        // Credit limit toggle
                        if (d.no_credit_limit == 1) {
                            $('#creditLimit').prop('disabled', true);
                            $('#noCreditLimitMsg').show();
                            $('#noCreditLimit').val(1);
                        } else {
                            $('#creditLimit').prop('disabled', false);
                            $('#noCreditLimitMsg').hide();
                            $('#noCreditLimit').val(0);
                        }
                    },
                    error: function(xhr) {
                        console.error('❌ Failed to load customer details. Status:', xhr.status, xhr.responseText);
                    }
                });
            }

            // 🔹 When customer selected via native change OR Select2
            $(document).on('change', '#customerSelect', function() {
                loadCustomerDetails($(this).val());
            });

            // 🔹 Also listen for Select2 select event (backup for when Select2 intercepts the change)
            $(document).on('select2:select', '#customerSelect', function(e) {
                const id = e?.params?.data?.id || $(this).val();
                loadCustomerDetails(id);
            });

            // 🔹 Clear button
            $('#clearCustomerData').on('click', function() {
                $('#customerSelect').val('');
                $('#customer_id, #customer, #customerDisplay').val('');
                $('#address,#tel,#remarks').val('');
                $('#previousBalance').val('0');
            });

        });
    </script>










    <script>
        /* ---------- helpers ---------- */
        function pad(n) {
            return n < 10 ? '0' + n : n
        }

        function setNowStamp() {
            const d = new Date();
            const dt =
                `${pad(d.getDate())}-${pad(d.getMonth() + 1)}-${String(d.getFullYear()).slice(-2)} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
            const dOnly = `${pad(d.getDate())}-${pad(d.getMonth() + 1)}-${String(d.getFullYear()).slice(-2)}`;
            $('#entryDateTime').text('Entry Date_Time: ' + dt);
            $('#entryDate').text('Date: ' + dOnly);
        }
        setNowStamp();
        setInterval(setNowStamp, 60 * 1000);
        // Note: Select2 on #customerSelect is initialized inside loadCustomersByType() after options are populated.

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });

        function showAlert(type, msg) {
            const el = $('#alertBox');
            el.removeClass('d-none alert-success alert-danger alert-warning alert-info').addClass('alert-' + type).text(
            msg);
            console.log('Showing alert:', msg);

            // Map our types to SweetAlert icons
            let icon = 'info';
            if (type === 'success') icon = 'success';
            else if (type === 'danger' || type === 'error') icon = 'error';
            else if (type === 'warning') icon = 'warning';

            // Show SweetAlert2 popup with the message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: icon,
                    title: icon === 'success' ? 'Success' : (icon === 'error' ? 'Error' : 'Notice'),
                    text: msg,
                    timer: 3000,
                    showConfirmButton: false
                });
            }

            // keep the inline alert as a short-lived mirror
            setTimeout(() => el.addClass('d-none'), 2500);
        }

        // ================= BUTTON LOCKING LOGIC =================
        let IS_BUSY = false;

        function setBusy($btn = null) {
            IS_BUSY = true;
            // Disable all action buttons
            $('.btn-action, #btnAdd, #btnAddRV, .btn-confirm').prop('disabled', true);
            
            // If a specific button was clicked, show loading state
            if ($btn && $btn.length) {
                $btn.data('original-text', $btn.html());
                $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
            }
        }

        function setReady($btn = null) {
            IS_BUSY = false;
            // Restore buttons state (this will be further refined by refreshPostedState)
            $('.btn-action, #btnAdd, #btnAddRV, .btn-confirm').prop('disabled', false);
            
            // Restore button text if applicable
            if ($btn && $btn.length && $btn.data('original-text')) {
                $btn.html($btn.data('original-text'));
            }
            
            // Re-run the standard state refresher to ensure buttons that should be disabled stay disabled
            if (typeof refreshPostedState === 'function') {
                refreshPostedState();
            }
        }

        // Global intercept for action buttons to prevent double-clicks
        $(document).on('click', '.btn-action', function(e) {
            if (IS_BUSY) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        function updateRowIndexNumbers() {
            $('#salesTableBody tr').each(function(index) {
                $(this).find('.row-index').text(index + 1);
            });
            const totalItems = $('#salesTableBody tr').length;
            $('#itemsCountBadge').text(`(${totalItems})`);
        }

        function addNewRow() {
            const rowCount = $('#salesTableBody tr').length + 1;
            $('#salesTableBody').append(`
      <tr>
        <!-- hidden warehouse id -->
        <input type="hidden" class="warehouse-id" name="warehouse_id[]" value="">

        <!-- INDEX -->
        <td class="text-center text-muted fw-bold row-index small align-middle">${rowCount}</td>

        <!-- PRODUCT -->
        <td class="product-col">
          <select class="form-select product-select rounded-3" name="product_id[]" style="width:100%">
            <option value="">Search product (Item Code / Name / Brand / Model)...</option>
          </select>
        </td>

        <!-- WAREHOUSE -->
        <td class="warehouse-col">
          <select class="form-select warehouse-select rounded-3" name="warehouse_select_display[]" style="width:100%; font-size: 12px;">
            <option value="">Select product first</option>
          </select>
        </td>

        <!-- QTY -->
        <td class="qty-col">
          <input type="text" class="form-control sales-qty text-center fw-bold rounded-3" name="sales_qty[]" data-available-stock="0" placeholder="1" value="1">
        </td>

        <!-- UNIT -->
        <td class="unit-col text-center align-middle">
          <span class="badge bg-light text-secondary border rounded-2 px-2 py-1 unit-label">Pcs</span>
        </td>

        <!-- RETAIL PRICE -->
        <td class="price-col">
          <div class="input-group input-group-sm">
            <input type="text" class="form-control retail-price text-end rounded-start-3" value="0.00" name="retail_price[]">
            <span class="input-group-text bg-light text-muted px-1" style="font-size: 11px;">Rs</span>
          </div>
        </td>

        <!-- DISCOUNT -->
        <td class="disc-col" ${(!window.saleSettings || !window.saleSettings.show_line_discount) ? 'style="display:none;"' : ''}>
          <div class="discount-wrapper input-group input-group-sm">
            <input type="text" class="form-control discount-value text-end" placeholder="0" name="discount_percentage[]">
            <button type="button" class="btn btn-outline-secondary discount-toggle" data-type="percent">%</button>
            <input type="hidden" class="discount-type-field" name="discount_type[]" value="percent">
          </div>
        </td>

        <!-- DISCOUNT AMOUNT / GST % -->
        <td class="discamt-col" ${(!window.saleSettings || !window.saleSettings.show_gst) ? 'style="display:none;"' : ''}>
          <div class="input-group input-group-sm">
            <input type="text" class="form-control discount-amount text-end rounded-start-3" name="discount_amount[]" placeholder="0">
            <span class="input-group-text bg-light text-success px-1" style="font-size: 11px;">%</span>
          </div>
        </td>

        <!-- NET AMOUNT -->
        <td class="amount-col">
          <input type="text" class="form-control sales-amount text-end input-readonly rounded-3" name="sales_amount[]" value="0.00" readonly style="font-weight:700; color:#2563eb; background-color:#f8fafc;">
        </td>

        <!-- ACTION -->
        <td class="action-col text-center align-middle">
          <button type="button" class="btn btn-sm btn-outline-danger del-row rounded-2 px-2 py-1">&times;</button>
        </td>
      </tr>
      `);

            // initialize select2 on the newly appended product-select
            initProductSelect2('#salesTableBody tr:last-child .product-select', '/search-products-sale',
            '/search_products');

            updateRowIndexNumbers();
        }


        // discunt % field
        $(document).on('click', '.discount-toggle', function() {

            const $btn = $(this);
            const currentType = $btn.data('type');

            if (currentType === 'percent') {
                $btn.data('type', 'pkr').text('PKR');
            } else {
                $btn.data('type', 'percent').text('%');
            }

            // ✅ NEW: Sync the hidden discount_type field with button state
            const newType = $btn.data('type');
            $btn.closest('tr').find('.discount-type-field').val(newType);

            // re-calc row
            const $row = $btn.closest('tr');
            computeRow($row);
            updateGrandTotals();
        });








        function canPost() {
            let ok = false;
            $('#salesTableBody tr').each(function() {
                const pid = $(this).find('.product-select').val();
                const qty = parseFloat($(this).find('.sales-qty').val() || '0');
                if (pid && qty > 0) {
                    ok = true;
                    return false;
                }
            });
            return ok;
        }

        function refreshPostedState() {
            const state = canPost();
            const partyType = $('input[name="partyType"]:checked').val();
            const isPosted = $('#is_posted').val() === '1';
            const isFinalized = $('#is_finalized').val() === '1';
            
            // btnPosted & btnHeaderPosted: disabled if no items OR already posted OR already finalized
            $('#btnPosted, #btnHeaderPosted').prop('disabled', !state || isPosted || isFinalized);
            
            // btnPosted2: enabled if items AND valid party type AND NOT posted AND NOT finalized
            const quickAllowed = (partyType === 'cash' || partyType === 'walking' || partyType === 'credit') && state && !isPosted && !isFinalized;
            $('#btnPosted2').prop('disabled', !quickAllowed);
            
            // btnPosted3: disabled if no items OR already posted OR already finalized
            const canDraftPost = state && !isPosted && !isFinalized;
            $('#btnPosted3').prop('disabled', !canDraftPost);
            
            console.log('🔧 refreshPostedState:', {
                hasItems: state,
                partyType: partyType,
                isPosted: isPosted,
                isFinalized: isFinalized,
                btnPosted_disabled: !state || isPosted || isFinalized,
                btnPosted2_disabled: !quickAllowed,
                btnPosted3_disabled: !canDraftPost
            });
        }

        /* ---------- SAVE/POST ---------- */
        function serializeForm() {
            // ✅ FILTER OUT EMPTY ROWS BEFORE SERIALIZING
            // Temporarily remove empty rows, then serialize, then restore them
            
            const emptyRows = [];
            let emptyRowCount = 0;
            
            // Find and remove empty rows
            $('#salesTableBody tr').each(function(index) {
                const $row = $(this);
                const productId = $row.find('.product-select').val();
                const qty = parseFloat($row.find('.sales-qty').val() || 0);
                
                // If row is empty (no product or qty = 0), remove it temporarily
                if (!productId || qty === 0) {
                    emptyRows.push($row.clone()); // Save a copy for restoration
                    $row.detach(); // Remove from DOM temporarily
                    emptyRowCount++;
                }
            });

            console.log(`✅ Removed ${emptyRowCount} empty rows before serialization`);

            // Serialize the form with only valid rows
            const serialized = $('#saleForm').serialize();

            // Restore empty rows back to the DOM
            if (emptyRows.length > 0) {
                emptyRows.forEach(row => {
                    $('#salesTableBody').append(row);
                });
                console.log(`✅ Restored ${emptyRows.length} empty rows`);
            }

            return serialized;
        }

        function ensureSaved() {
            console.log('ensureSaved called');
            return new Promise(function(resolve, reject) {

                // 🔴 VALIDATION: Check if form has items before saving
                if (!canPost()) {
                    showAlert('danger', 'Please add at least one product with quantity before saving');
                    setReady(); // Ensure buttons are restored if validation fails
                    reject({ msg: 'No items in form' });
                    return;
                }

                // Recompute every row so discount % and discount amount fields are populated
                $('#salesTableBody tr').each(function() {
                    try {
                        computeRow($(this));
                    } catch (e) {
                        console.warn('computeRow error', e);
                    }
                });
                updateGrandTotals();

                // Ensure discount fields have values (not empty strings)
                $('#salesTableBody tr').each(function() {
                    const $discValue = $(this).find('.discount-value');
                    const $discAmount = $(this).find('.discount-amount');
                    if (!$discValue.val()) $discValue.val('0.00');
                    if (!$discAmount.val()) $discAmount.val('0.00');
                });

                const formData = serializeForm();
                console.log('🚀 DATA GOING TO sale.ajax.save:', formData);

                // 🔹 Add CSRF token to form data
                const dataToSend = formData + '&_token=' + '{{ csrf_token() }}';

                $.post('{{ route('sale.ajax.save') }}', dataToSend)
                    .done(function(res) {
                        console.log('✅ RESPONSE FROM SERVER:', res);
                        
                        if (res?.ok) {
                            $('#booking_id').val(res.booking_id);
                            if (res.invoice_no) {
                                $('input[name="Invoice_no"]').val(res.invoice_no);
                            }
                            const displayNumber = res.invoice_no || res.booking_id;
                            const displayLabel = res.invoice_no ? 'Invoice #' : 'Booking #';
                            showAlert('success', 'Saved (' + displayLabel + displayNumber + ')');
                            
                            // 💡 We DON'T call setReady() here if it's part of a chain (like Post)
                            // The caller will handle setReady() when the entire process is done.
                            resolve(res.booking_id);
                        } else {
                            showAlert('danger', res.msg || 'Save failed');
                            setReady(); // Restore on error
                            reject(res);
                        }
                    })
                    .fail(function(xhr) {
                        console.error('❌ AJAX ERROR RESPONSE:', xhr.responseText);
                        setReady(); // Restore on network error
                        
                        let msg = 'Save error';
                        try {
                            const json = xhr.responseJSON || JSON.parse(xhr.responseText || '{}');
                            msg = json.message || json.msg || msg;
                        } catch (err) {
                            msg = xhr.responseText.split('\n').find(l => l.trim()) || msg;
                        }
                        showAlert('danger', msg);
                        reject(xhr);
                    });
            });
        }


        function postNow() {

            let bookingId = $('#booking_id').val();

            // 🔹 Ensure all discount fields have values before posting
            $('#salesTableBody tr').each(function() {
                const $discValue = $(this).find('.discount-value');
                const $discAmount = $(this).find('.discount-amount');
                if (!$discValue.val()) $discValue.val('0.00');
                if (!$discAmount.val()) $discAmount.val('0.00');
            });

            let data = $('#saleForm').serializeArray();

            // Validate receipts before posting — if invalid, show message and abort
            const receiptValidation = validateReceipts();
            // If there are any receipt amounts > 0 but receipts are invalid, prevent post
            let hasReceiptAmount = false;
            $('.rv-amount').each(function() {
                if (toNum($(this).val()) > 0) hasReceiptAmount = true;
            });
            if (hasReceiptAmount && !receiptValidation.ok) {
                showAlert('danger', receiptValidation.firstMessage || 'Please fix receipt rows before posting');
                if (receiptValidation.firstEl) receiptValidation.firstEl.focus();
                return;
            }

            // warehouse_id[product_id] build karo
            $('#salesTableBody tr').each(function() {
                let productId = $(this).find('.product-select').val();
                let warehouseId = $(this).find('.warehouse-id').val();

                if (productId && warehouseId) {
                    data.push({
                        name: `warehouse_id[${productId}]`,
                        value: warehouseId
                    });
                }
            });

            // booking id ensure
            data.push({
                name: 'booking_id',
                value: bookingId
            });

            // Include receipt rows (if any) so ajaxPost can create/process them
            $('.rv-account').each(function(i) {
                const acc = $(this).val();
                const amt = $('.rv-amount').eq(i).val() || '';
                data.push({
                    name: 'receipt_account_id[]',
                    value: acc
                });
                data.push({
                    name: 'receipt_amount[]',
                    value: amt
                });
            });

            // 🔹 GET request ke liye data query string me convert karo
            let queryString = $.param(data);

            console.log('GET Request URL:', '{{ route('sale.ajax.post') }}?' + queryString);

            // 🔹 AJAX GET request
            $.get('{{ route('sale.ajax.post') }}', queryString)

                .done(function(res) {
                    console.log('Response:', res);

                    if (res && res.ok) {
                        showAlert('success', 'Posted successfully');
                        $('#is_posted').val('1'); // Mark sale as posted
                        refreshPostedState();

                        if (res.invoice_url) {
                            window.open(res.invoice_url, '_blank');
                        }
                    } else {
                        $('#btnPosted, #btnHeaderPosted').prop('disabled', false);
                        showAlert('danger', res.msg || 'Post failed');
                    }
                })

                .fail(function(xhr) {
                    console.error('Server Error:', xhr.responseText);
                    $('#btnPosted, #btnHeaderPosted').prop('disabled', false);

                    // Try to extract a useful message from server JSON or responseText
                    let msg = 'Server error while posting';
                    try {
                        const json = xhr.responseJSON || JSON.parse(xhr.responseText || '{}');
                        if (json && (json.message || json.msg)) {
                            msg = json.message || json.msg;
                        } else if (typeof xhr.responseText === 'string' && xhr.responseText.trim()) {
                            msg = xhr.responseText.split('\n').find(l => l.trim()) || msg;
                        }
                    } catch (err) {
                        if (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.msg)) {
                            msg = xhr.responseJSON.message || xhr.responseJSON.msg;
                        } else if (typeof xhr.responseText === 'string' && xhr.responseText.trim()) {
                            msg = xhr.responseText.split('\n').find(l => l.trim()) || msg;
                        }
                    }

                    showAlert('danger', msg);
                });
        }


        /* ---------- Events top buttons ---------- */
        $('#btnAdd').on('click', addNewRow);
        $('#btnEdit').on('click', () => alert('Edit mode activated'));
        $('#btnRevert').on('click', () => location.reload());
        $('#btnDelete').on('click', function() {
            const $btn = $(this);
            if (!confirm('Reset all fields?')) return;
            setBusy($btn);
            
            $('#saleForm')[0].reset();
            $('#booking_id').val('');
            $('#salesTableBody').html('');
            addNewRow();
            $('#totalAmount').text('0.00');
            updateGrandTotals();
            refreshPostedState();
            showAlert('success', 'Form cleared');
            setReady($btn);
        });
        $('#btnSave').on('click', function() {
            const $btn = $(this);
            // 🔴 Validate form has items
            if (!canPost()) {
                showAlert('danger', 'Please add at least one product with quantity');
                return;
            }
            setBusy($btn);
            ensureSaved().then(() => {
                setReady($btn);
            }).catch(() => {
                setReady($btn);
            });
        });
        
        // Quick post to Main Store for cash / walking customers (bypass warehouse modal)
        $('#btnPosted2').on('click', function() {
            const $btn = $(this);
            const partyType = $('input[name="partyType"]:checked').val();

            console.log('Sale button clicked, IS_ADMIN:', window.IS_ADMIN, 'partyType:', partyType);

            if (!['cash','walking','credit'].includes(partyType?.toLowerCase())) {
                showAlert('danger', 'Main Store quick post is allowed only for Cash, Walking or Credit customers');
                return;
            }

            if (!canPost()) {
                showAlert('danger', 'Please add at least one product with quantity before posting');
                return;
            }

            // 🔴 VALIDATION: Cash/Walking customers MUST have at least 1 receipt amount
            if (['cash', 'walking'].includes(partyType?.toLowerCase())) {
                let hasValidReceipt = false;
                $('.rv-amount').each(function(i) {
                    const amt = toNum($(this).val());
                    if (amt > 0) {
                        hasValidReceipt = true;
                        return false; // break
                    }
                });
                
                if (!hasValidReceipt) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Payment Required',
                        text: 'Amount required in at least 1 bank account for ' + partyType + ' customer',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
            }

            setBusy($btn);

            // ONLY VALIDATE FOR NON-ADMIN (they use their own fixed branch)
            // ADMIN validation happens AFTER branch selection in confirmation button
            if (!(window.IS_ADMIN === 'true' || window.IS_ADMIN === true)) {
                // Non-admin user: validate stock for their branch before proceeding
                const stockValidation = validateBranchStock(window.USER_BRANCH_ID);
                if (!stockValidation.ok) {
                    const stockMsg = stockValidation.message;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock Limit Exceeded',
                        html: stockMsg.replace(/\n/g, '<br>') + '<br><br>Do you want to proceed with <strong style="color:red">negative stock</strong>?',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, Proceed',
                        cancelButtonText: 'No, Cancel',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // User confirmed — proceed directly, skip stock check
                            proceedWithSale(partyType).finally(() => setReady($btn));
                        } else {
                            setReady($btn);
                        }
                    });
                    return; // Wait for Swal response
                }
            }

            // If user is admin, show branch selection modal first
            if (window.IS_ADMIN === 'true' || window.IS_ADMIN === true) {
                console.log('Admin user detected, showing branch modal');
                
                // Populate branch dropdown
                const $branchSelect = $('#branchSelectionDropdown');
                $branchSelect.empty();
                $branchSelect.append('<option selected disabled value="">Choose a branch...</option>');
                
                if (window.BRANCHES && window.BRANCHES.length > 0) {
                    window.BRANCHES.forEach(branch => {
                        $branchSelect.append(`<option value="${branch.id}">${branch.branch_name || branch.name}</option>`);
                    });
                }
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('branchSelectionModal'));
                modal.show();
                setReady($btn); // Ready because user now needs to interact with modal
                return;
            }

            console.log('Non-admin user, proceeding directly');
            // Non-admin users proceed directly with their branch (already validated above)
            proceedWithSale(partyType).finally(() => setReady($btn));
        });

        // STOCK VALIDATION FUNCTION
        function validateBranchStock(branchId) {
            // Get all products from sales table rows
            const products = [];
            $('#salesTableBody tr').each(function() {
                const productId = $(this).find('.product-select').val();
                const qty = toNum($(this).find('.sales-qty').val());
                const warehouseId = $(this).find('.warehouse-id').val();
                
                if (productId && qty > 0) {
                    products.push({ 
                        product_id: productId, 
                        qty: qty,
                        warehouse_id: warehouseId || null  // Include warehouse if selected
                    });
                }
            });

            if (products.length === 0) {
                return { ok: false, message: 'No products added to sale' };
            }

            // Check each product against warehouse_stocks
            const insufficientProducts = [];
            
            products.forEach(product => {
                let availableQty = 0;
                
                // ✅ ERP STANDARD: Check total available stock (shop + warehouses)
                if (product.warehouse_id) {
                    // If specific warehouse is selected, verify that warehouse has stock
                    const warehouseStock = window.WAREHOUSE_STOCKS.find(stock => 
                        stock.product_id == product.product_id && 
                        stock.branch_id == branchId && 
                        stock.warehouse_id == product.warehouse_id
                    );
                    availableQty = warehouseStock ? (warehouseStock.quantity || 0) : 0;
                } else {
                    // NO warehouse specified - check TOTAL available stock
                    // Sum: Shop stock (warehouse_id = null) + All warehouse stock (warehouse_id > 0)
                    const allStocks = window.WAREHOUSE_STOCKS.filter(stock => 
                        stock.product_id == product.product_id && 
                        stock.branch_id == branchId
                        // Include BOTH shop stock (NULL) and warehouse stock (> 0)
                    );
                    availableQty = allStocks.reduce((sum, stock) => sum + (stock.quantity || 0), 0);
                }
                
                if (availableQty < product.qty) {
                    const productName = $('#salesTableBody tr').find(`.product-select[value="${product.product_id}"]`)
                        .closest('tr').find('.product-select').text() || `Product #${product.product_id}`;
                    
                    insufficientProducts.push({
                        name: productName,
                        required: product.qty,
                        available: availableQty
                    });
                }
            });

            if (insufficientProducts.length > 0) {
                let msg = 'Branch does not have sufficient stock for sale:\n\n';
                insufficientProducts.forEach(p => {
                    msg += `• ${p.name}: Need ${p.required}, Available ${p.available}\n`;
                });
                return { ok: false, message: msg };
            }

            return { ok: true };
        }

        // Handle branch confirmation
        $(document).on('click', '#branchConfirmBtn', function(e) {
            const $btn = $(this);
            e.preventDefault();
            e.stopPropagation();
            
            const selectedBranch = $('#branchSelectionDropdown').val();
            const partyType = $('input[name="partyType"]:checked').val();
            
            if (!selectedBranch || selectedBranch === '') {
                showAlert('danger', 'Please select a branch');
                return;
            }

            setBusy($btn);

            // VALIDATE STOCK FOR SELECTED BRANCH
            const stockValidation = validateBranchStock(parseInt(selectedBranch));
            if (!stockValidation.ok) {
                const stockMsg = stockValidation.message;
                // Close the branch modal first
                try {
                    const modalEl = document.getElementById('branchSelectionModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                } catch (err) { console.error(err); }

                Swal.fire({
                    icon: 'warning',
                    title: 'Stock Limit Exceeded',
                    html: stockMsg.replace(/\n/g, '<br>') + '<br><br>Do you want to proceed with <strong style="color:red">negative stock</strong>?',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, Proceed',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('input[name="branch_id"]').val(selectedBranch);
                        proceedWithSale(partyType).finally(() => setReady($btn));
                    } else {
                        setReady($btn);
                    }
                });
                return;
            }
            
            $('input[name="branch_id"]').val(selectedBranch);
            
            // Close modal
            try {
                const modalEl = document.getElementById('branchSelectionModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            } catch (err) { console.error(err); }
            
            proceedWithSale(partyType).finally(() => setReady($btn));
        });

        // Helper function to proceed with sale after branch selection
        function proceedWithSale(partyType) {
            return ensureSaved().then(function(bookingId) {
                console.log('Booking saved, proceeding with post:', bookingId);
                
                // Collect receipt data from form (needed for customer ledger + receipt vouchers)
                let receiptAccountIds = [];
                let receiptAmounts = [];
                $('.rv-account').each(function(i) {
                    let acc = toNum($(this).val());
                    let amt = toNum($('.rv-amount').eq(i).val());
                    if (acc && amt > 0) {
                        receiptAccountIds.push(acc);
                        receiptAmounts.push(amt);
                    }
                });

                // Build form data with all required fields for ajaxPost
                // Include CSRF token for POST request
                let formData = {
                    _token: '{{ csrf_token() }}',
                    booking_id: bookingId,
                    partyType: partyType,
                    branch_id: $('input[name="branch_id"]').val(),  // Include selected branch for admin
                    receipt_account_id: receiptAccountIds,
                    receipt_amount: receiptAmounts
                };
                
                console.log('Form data:', formData);

                // Perform quick post via dedicated endpoint
                const url = '{{ route('sale.ajax.post-mainstore') }}';
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    dataType: 'json',
                    success: function(res) {
                        console.log('Sale post response:', res);
                        if (res && res.ok) {
                            showAlert('success', 'Sale completed using Branch Stock');
                            $('#is_posted').val('1'); // Mark sale as posted
                            // Open invoice in new tab
                            if (res.invoice_url) window.open(res.invoice_url, '_blank');
                            // mark posted state & refresh UI
                            $('#booking_id').val(bookingId);
                            refreshPostedState();
                        } else {
                            showAlert('danger', (res && res.error) ? res.error : 'Unknown response');
                        }
                    },
                    error: function(xhr, status, error) {
                        // ✅ IMPROVED ERROR HANDLING
                        console.error('❌ Ajax error details:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            responseJSON: xhr.responseJSON,
                            error: error
                        });

                        let errorMsg = 'Post failed';

                        // Try multiple ways to extract error message
                        if (xhr.responseJSON) {
                            // JSON response - try multiple field names
                            if (xhr.responseJSON.error) {
                                errorMsg = xhr.responseJSON.error;
                            } else if (xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.msg) {
                                errorMsg = xhr.responseJSON.msg;
                            } else if (xhr.responseJSON.errors) {
                                // Multiple field errors
                                let errStr = '';
                                for (const [field, msgs] of Object.entries(xhr.responseJSON.errors)) {
                                    if (Array.isArray(msgs)) {
                                        errStr += `${field}: ${msgs.join(', ')}\n`;
                                    } else {
                                        errStr += `${field}: ${msgs}\n`;
                                    }
                                }
                                if (errStr) errorMsg = errStr.trim();
                            }
                        } else if (xhr.responseText) {
                            // Text response - extract meaningful part
                            try {
                                const text = xhr.responseText.trim();
                                // Try to parse as JSON if it looks like JSON
                                if (text.startsWith('{')) {
                                    const json = JSON.parse(text);
                                    errorMsg = json.error || json.message || json.msg || text;
                                } else {
                                    // Plain text error - take first non-empty line
                                    const lines = text.split('\n').map(l => l.trim()).filter(l => l);
                                    if (lines.length > 0) {
                                        errorMsg = lines[0];
                                    }
                                }
                            } catch (e) {
                                // If parsing fails, use raw text
                                errorMsg = xhr.responseText.substring(0, 200);
                            }
                        }

                        // Fallback to status text if no message found
                        if (!errorMsg || errorMsg === 'Post failed') {
                            if (xhr.status === 0) {
                                errorMsg = 'Network error - Cannot connect to server';
                            } else if (xhr.status === 404) {
                                errorMsg = 'Route not found (404)';
                            } else if (xhr.status === 403) {
                                errorMsg = 'Access denied (403)';
                            } else if (xhr.status === 500) {
                                errorMsg = 'Server error (500) - Check server logs';
                            } else if (xhr.status === 422) {
                                errorMsg = 'Validation error - Check your form data';
                            } else if (status === 'timeout') {
                                errorMsg = 'Request timeout - Server took too long to respond';
                            } else if (status === 'error') {
                                errorMsg = error || 'Unknown error occurred';
                            }
                        }

                        console.error('🔴 Final error message:', errorMsg);
                        showAlert('danger', errorMsg);
                    }
                });
            }).catch(function(err) {
                console.error('ensureSaved failed', err);
                showAlert('danger', 'Failed to save booking: ' + (err?.msg || err?.message || err));
            });
        }
        $('#btnPrint').on('click', function() {
            const $btn = $(this);
            // 🔴 Validate form has items
            if (!canPost()) {
                showAlert('danger', 'Please add at least one product with quantity');
                return;
            }
            setBusy($btn);
            ensureSaved().then(id => {
                window.open('{{ url('booking/invoice/') }}/' + id, '_blank');
                setReady($btn);
            }).catch(() => setReady($btn));
        });
        // $('#btnPrint2').off('click').on('click', function() {
        //     // Post + Print flow: post the sale and then show print2
        //     cleanupEmptyRows();
        //     updateGrandTotals();
        //     refreshPostedState();

        //     const v = validateFormAll();
        //     if (!v.ok) {
        //         showAlert('danger', v.message);
        //         if (v.el && v.el.length) {
        //             v.el.focus();
        //             if (v.el.hasClass('js-customer')) v.el.select2?.('open');
        //         }
        //         return;
        //     }

        //     if (!canPost()) {
        //         showAlert('danger', 'No valid item lines to post');
        //         return;
        //     }

        //     // 🔹 CHECK IF ALREADY SAVED - if yes, skip save and go directly to warehouse modal
        //     const bookingId = $('#booking_id').val();
            
        //     if (bookingId) {
        //         // ✅ Already saved - directly open warehouse modal (like Posted button)
        //         PRINT_AFTER_POST = true;
        //         faraz();
        //         return;
        //     }

        //     // 🔹 NOT SAVED YET - Check credit limit and save first
        //     const partyType = $('input[name="partyType"]:checked').val();
        //     let cust = null;

        //     if (partyType === 'walking') {
        //         cust = $('#customerDisplay').val();
        //     } else {
        //         cust = $('#customerSelect').val();
        //     }

        //     const payable = parseFloat($('#totalBalance').val() || $('#tPayable').text() || 0) || 0;

        //     // For walking customers, skip credit check
        //     if (partyType === 'walking') {
        //         // Direct post+print without credit check
        //         ensureSaved().then(() => {
        //             PRINT_AFTER_POST = true; // 🔹 Set flag for print mode
        //             faraz(); // Open warehouse modal
        //         }).catch(() => {
        //             showAlert('danger', 'Failed to save booking. Please try again.');
        //         });
        //     } else if (cust) {
        //         $.get('/get-customer/' + cust)
        //             .done(function(res) {
        //                 const credit = parseFloat(res.credit_limit || 0) || 0;
        //                 if (credit > 0 && payable > credit) {
        //                     Swal.fire({
        //                         icon: 'warning',
        //                         title: 'Credit limit exceeded',
        //                         html: `Customer credit limit is <b>Rs. ${credit.toFixed(2)}</b>.<br>Payable amount is <b>Rs. ${payable.toFixed(2)}</b>.`,
        //                     });
        //                     return;
        //                 }
        //                 // Proceed to save, then open warehouse modal for print+post
        //                 ensureSaved().then(() => {
        //                     PRINT_AFTER_POST = true; // 🔹 Set flag for print mode
        //                     faraz(); // Open warehouse modal
        //                 }).catch(() => {
        //                     showAlert('danger', 'Failed to save booking. Please try again.');
        //                 });
        //             })
        //             .fail(function() {
        //                 showAlert('danger', 'Failed to verify customer credit limit. Please try again.');
        //             });
        //     } else {
        //         showAlert('danger', 'Please select a customer before posting');
        //         return;
        //     }
        // });

        // ❌ DISABLED - btnPrint2 button is commented out, directPostAndPrint function removed
        // This function is no longer needed
        $('#btnDCPrint').off('click').on('click', function() {
            const $btn = $(this);
            // 🔴 Validate form has items
            if (!canPost()) {
                showAlert('danger', 'Please add at least one product with quantity');
                return;
            }

            setBusy($btn);
            ensureSaved().then(function(Invoice_no) {
                window.open('{{ url('sale/dc/') }}/' + Invoice_no, '_blank');
                setReady($btn);
            }).catch(function() {
                alert('Save failed');
                setReady($btn);
            });
        });

        $('#btnThermalPrint').off('click').on('click', function() {
            const $btn = $(this);
            // 🔴 Validate form has items
            if (!canPost()) {
                showAlert('danger', 'Please add at least one product with quantity');
                return;
            }

            setBusy($btn);
            ensureSaved().then(function(id) {
                window.open('{{ url('booking/print2') }}/' + id, '_blank');
                setReady($btn);
            }).catch(function() {
                alert('Save failed');
                setReady($btn);
            });
        });

        $('#btnExit').on('click', function() {
            const $btn = $(this);
            setBusy($btn);
            ensureSaved().then(() => {
                window.location.href = "{{ route('sale.index') }}";
            }).catch(() => setReady($btn));
        });

        //     $('#btnPosted, #btnHeaderPosted').on('click', function () {
        //     ensureSaved().faraz().then(postNow);
        // });



        /* ---------- Row compute ---------- */
        function toNum(v) {
            return parseFloat(v || 0) || 0;
        }

        function computeRow($row, manualAmount = false, formatDiscount = true) {

            const rp = toNum($row.find('.retail-price').val());
            // console.log("retail price",rp);
            const qty = toNum($row.find('.sales-qty').val());
            // console.log("qty:",qty);

            // 🔹 Safe discount value (never negative)
            const $discInput = $row.find('.discount-value');
            let rawDisc = Math.max(0, $discInput.val());
            if (window.saleSettings && window.saleSettings.show_line_discount === false) {
                rawDisc = 0;
            }
            let discValue = rawDisc;

            const discType = $row.find('.discount-toggle').data('type'); // percent | pkr
            console.log("percent discount:", discType);
            let dam = toNum($row.find('.discount-amount').val());
            if (window.saleSettings && window.saleSettings.show_gst === false) {
                dam = 0;
            }

            // 🔹 GROSS
            const gross = rp * qty;

            /* ===== AUTO DISCOUNT ===== */
            if (discValue > 0) {

                if (discType === 'percent') {

                    // If user entered >100%, mark invalid and show helper text
                    const $help = $row.find('.discount-help');
                    if (rawDisc > 101) {
                        markInvalid($discInput);
                        const $wrapper = $row.find('.discount-wrapper');
                        if ($wrapper.find('.discount-help').length === 0) {
                            $wrapper.append('<div class="discount-help">Discount never be <= 100%</div>');
                        } else {
                            $wrapper.find('.discount-help').text('Discount must be <= 100%');
                        }
                        // use 100 for calculation but keep visual warning
                        discValue = 100;
                    } else {

                        clearInvalid($discInput);
                        $row.find('.discount-help').remove();
                        discValue = Math.min(discValue, 100);
                    }

                    dam = (gross * discValue) / 100; // % from retail

                } else {
                    // PKR discount should not exceed gross per row
                    const totalPKR = discValue * qty;
                    if (totalPKR > gross) {
                        markInvalid($discInput);
                        const $wrapper = $row.find('.discount-wrapper');
                        if ($wrapper.find('.discount-help').length === 0) {
                            $wrapper.append('<div class="discount-help">Discount cannot exceed row gross</div>');
                        } else {
                            $wrapper.find('.discount-help').text('Discount cannot exceed row gross');
                        }
                        // cap discount amount to gross but keep user's per-unit input unchanged
                        dam = gross;
                    } else {
                        clearInvalid($discInput);
                        $row.find('.discount-help').remove();
                        dam = totalPKR;
                    }
                }

                if (formatDiscount) {
                    $discInput.val(discValue.toFixed(2));
                }
                $row.find('.discount-amount').val(dam.toFixed(2));

            } else {
                // 🔹 Discount empty or 0
                dam = 0;
                $row.find('.discount-amount').val('0.00');
                // clear any helper/invalid state when input is empty
                clearInvalid($discInput);
                $row.find('.discount-help').remove();
                // Don't clear discount-value, keep it empty or with user's last input
                if (!$discInput.val()) {
                    $discInput.val('');
                }
            }

            /* ===== NET ===== */
            const net = Math.max(0, gross - dam);
            $row.find('.sales-amount').val(net.toFixed(2));

            if (formatDiscount) {
                $row.find('.retail-price').val(rp.toFixed(2));
            }
        }







        $(document).on('input', '.sales-qty, .discount-value, .retail-price', function(e) {
            const $row = $(this).closest('tr');
            // If typing in discount or retail-price input, do not reformat it while typing
            if ($(this).hasClass('discount-value') || $(this).hasClass('retail-price')) {
                computeRow($row, false, false); // manualAmount=false, formatDiscount=false
            } else {
                computeRow($row);
            }
            updateGrandTotals();
            refreshPostedState();
        });

        // On blur of discount input, format and validate
        $(document).on('blur', '.discount-value, .retail-price', function() {
            const $row = $(this).closest('tr');
            computeRow($row, false, true); // now format the input
            updateGrandTotals();
            refreshPostedState();
        });

        $(document).on('input', '.discount-amount', function() {
            const $row = $(this).closest('tr');
            computeRow($row, true); // manual amount respected
            updateGrandTotals();
            refreshPostedState();
        });

        /* ---------- Delete row ---------- */
        $(document).on('click', '.del-row', function() {
            const $tr = $(this).closest('tr');
            const $tbody = $('#salesTableBody');
            if ($tbody.find('tr').length > 1) {
                $tr.remove();
                updateRowIndexNumbers();
                updateGrandTotals();
                refreshPostedState();
            }
        });

        /* ---------- Totals ---------- */
        function updateGrandTotals() {

            let tQty = 0;
            let tGross = 0;
            let tLineDisc = 0;
            let tNet = 0;

            $('#salesTableBody tr').each(function() {

                const $r = $(this);

                const rp = toNum($r.find('.retail-price').val());
                const qty = toNum($r.find('.sales-qty').val());
                const dam = toNum($r.find('.discount-amount').val());

                const gross = rp * qty;
                const net = Math.max(0, gross - dam);

                tQty += qty;
                tGross += gross;
                tLineDisc += dam;
                tNet += net; // ✅ NET TOTAL
            });

            // ===== ORDER LEVEL =====
            const orderRaw = (window.saleSettings && window.saleSettings.show_overall_discount === false) ? 0 : toNum($('#discountPercent').val());
            const orderType = $('#orderDiscountToggle').data('type') || 'percent'; // percent | pkr
            let orderDisc = 0;
            if (window.saleSettings && window.saleSettings.show_overall_discount === false) {
                orderDisc = 0;
            } else if (orderType === 'percent') {
                orderDisc = (tNet * orderRaw) / 100;
            } else {
                // treat input as absolute PKR amount
                orderDisc = orderRaw;
            }

            // 🔹 For cash & walking customers: NO previous balance (they don't have ledger)
            const partyType = $('input[name="partyType"]:checked').val();
            const isCashOrWalking = (partyType === 'cash' || partyType === 'walking');
            const prev = isCashOrWalking ? 0 : toNum($('#previousBalance').val());
            const receipts = toNum($('#receiptsTotal').text());
            const echarges = toNum($('#echarges').val());

            const payable = Math.max(0, tNet - orderDisc + prev - receipts + echarges);

            // ===== UI UPDATE =====
            $('#tQty').text(tQty.toFixed(0));
            $('#tGross').text(tGross.toFixed(2));
            $('#tLineDisc').text(tLineDisc.toFixed(2));
            $('#tSub').text(tNet.toFixed(2));
            
            $('#tOrderDisc').text(orderDisc.toFixed(2));
            $('#tPrev').text(prev.toFixed(2));
            $('#tPayable').text(payable.toFixed(2));

            // 🔥 TABLE FOOTER TOTAL
            $('#totalAmount').text(tNet.toFixed(2));

            // ===== BACKEND MIRRORS =====
            // subTotal2 = subTotal1 + additional_discount + extra_charges
            const additionalDiscount = toNum($('#discountPercent').val());
            const extraCharges = toNum($('#echarges').val());
            const subTotal2Calculated = (tNet - additionalDiscount) + extraCharges;
            
            $('#subTotal1').val(tGross.toFixed(2));
            $('#subTotal2').val(subTotal2Calculated.toFixed(2));
            $('#discountAmount').val(orderDisc.toFixed(2));
            $('#totalBalance').val(payable.toFixed(2));
        }

        // recalc when discount/toggle/previous balance/echarges change
        $(document).on('input', '#previousBalance, #discountPercent, #echarges', updateGrandTotals);
        $(document).on('click', '#orderDiscountToggle', function() {
            const $btn = $(this);
            const current = $btn.data('type');
            if (current === 'percent') {
                $btn.data('type', 'pkr').text('PKR');
            } else {
                $btn.data('type', 'percent').text('%');
            }
            updateGrandTotals();
        });

        /* ---------- Row auto-add ---------- */
        $('#salesTableBody').on('input', '.sales-qty', function() {
            const $row = $(this).closest('tr');
            computeRow($row);
            updateGrandTotals();
            refreshPostedState();
        });

        // ✅ ERP STOCK VALIDATION: On blur of qty field, check if qty > available stock
        $('#salesTableBody').on('blur', '.sales-qty', function() {
            const $row = $(this).closest('tr');
            const $qtyInput = $(this);
            const enteredQty = parseFloat($qtyInput.val() || '0') || 0;
            const availableStock = parseFloat($qtyInput.data('available-stock') || '0') || 0;
            const productName = $row.find('.product-select option:selected').text() || 'Product';

            // If qty > available stock, show confirmation popup
            if (enteredQty > 0 && availableStock > 0 && enteredQty > availableStock) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock Limit Exceeded',
                    html: `<div style="text-align:left;">
                        <p><strong>${productName}</strong></p>
                        <p>Available Stock: <strong>${availableStock}</strong> pieces</p>
                        <p>You want to sell: <strong>${enteredQty}</strong> pieces</p>
                        <p style="margin-top:15px;"><strong>Do you want to sell ${enteredQty} pieces?</strong></p>
                    </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Proceed',
                    cancelButtonText: 'No, Use Available Stock',
                    confirmButtonColor: '#d32f2f',
                    cancelButtonColor: '#999'
                }).then(result => {
                    if (result.isConfirmed) {
                        // User confirmed - keep the qty as entered
                        console.log('✅ User confirmed sale of', enteredQty, 'pieces (Available:', availableStock + ')');
                        computeRow($row);
                        updateGrandTotals();
                        refreshPostedState();
                    } else {
                        // User cancelled - reset to available stock
                        console.log('❌ User cancelled - resetting qty to available stock:', availableStock);
                        $qtyInput.val(availableStock);
                        computeRow($row);
                        updateGrandTotals();
                        refreshPostedState();
                    }
                });
            }
        });

        /* ---------- Add new row when user presses Enter in Disc % (only on last row) ---------- */
        $('#salesTableBody').on('keydown', '.discount-value, .retail-price', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault(); // prevent accidental form submit
                const $current = $(this).closest('tr');

                // compute current row first (in case user typed value and pressed Enter)
                computeRow($current);
                updateGrandTotals();
                refreshPostedState();

                // only add new row when this is the last row AND discount has some value OR qty > 0 or product selected
                const isLast = $current.is(':last-child');
                const discVal = parseFloat($(this).val() || '0') || 0;
                const qtyVal = parseFloat($current.find('.sales-qty').val() || '0') || 0;
                const prodSelected = !!$current.find('.product').val();

                // require at least one 'meaningful' value so blank Enter doesn't create rows
                if (isLast && (discVal !== 0 || qtyVal > 0 || prodSelected)) {
                    addNewRow();
                    // focus on new row product for quick entry
                    const $newRow = $('#salesTableBody tr:last-child');
                    // setTimeout(() => $newRow.find('.warehouse').focus(), 0);
                }
            }
        });



        function recomputeReceipts() {
            let sum = 0;
            // Calculate the total receipt amount
            $('.rv-amount').each(function() {
                sum += toNum($(this).val()); // Sum up all the receipt amounts
            });
            $('#receiptsTotal').text(sum.toFixed(2)); // Display total in the respective element
            updateGrandTotals(); // Update other totals if needed
            updatePostButtonState();
            // Live-validate receipt rows and highlight invalid fields
            validateReceipts();
        }

        $('#btnAddRV').on('click', function() {

            const $row = $(`
            <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                <select class="form-select rv-account" name="receipt_account_id[]" style="max-width:320px"></select>
                <input type="text" class="form-control text-end rv-amount" name="receipt_amount[]" placeholder="0.00" style="max-width:160px">
                <button type="button" class="btn btn-outline-danger btn-sm btnRemRV">&times;</button>
            </div>
        `);

            $('#rvWrapper').append($row);

            loadAccountsInto($row.find('.rv-account'));
            // keep post button state updated when a new row is added
            updatePostButtonState();
        });



        $(document).on('click', '.btnRemRV', function() {
            $(this).closest('.rv-row').remove();
            recomputeReceipts();

            $('.rv-account').each(function() {
                loadAccountsInto($(this));
            });
            updatePostButtonState();
        });

        // Recompute total receipt amounts when input changes
        $(document).on('input', '.rv-amount', recomputeReceipts);

        // Update post button and validate when account selection changes
        // Also refresh other account dropdowns so the selected account is
        // removed from the other rows (prevents selecting same account twice).
        $(document).on('change', '.rv-account', function() {
            updatePostButtonState();
            validateReceipts();
            $('.rv-account').each(function() {
                loadAccountsInto($(this));
            });
        });



        function markInvalid($el) {
            // add visuals; $el can be input/select/td
            $el.addClass('invalid-input invalid-select');
            // also add class to closest td for table cells
            $el.closest('td').addClass('invalid-cell');
        }

        function clearInvalid($el) {
            $el.removeClass('invalid-input invalid-select');
            $el.closest('td').removeClass('invalid-cell');
        }

        function clearAllInvalids() {
            $('.invalid-input, .invalid-select').removeClass('invalid-input invalid-select');
            $('.invalid-cell').removeClass('invalid-cell');
        }

        $(document).on('input change', 'select, input, textarea', function() {
            clearInvalid($(this));
        });

        function validateRows() {
            let ok = true;
            let firstMessage = null;
            let firstEl = null;

            $('#salesTableBody tr').each(function(rowIndex) {
                const $row = $(this);
                // const $wh = $row.find('.warehouse');
                const $prod = $row.find('.product-select');
                const $qty = $row.find('.sales-qty');

                // Product / Item
                if (!$prod.val()) {
                    ok = false;
                    if (!firstMessage) {
                        firstMessage = 'Please select Item for row ' + (rowIndex + 1);
                        firstEl = $prod;
                    }
                    markInvalid($prod);
                }

                // Qty > 0
                const qtyVal = parseFloat($qty.val() || '0') || 0;
                if (qtyVal <= 0) {
                    ok = false;
                    if (!firstMessage) {
                        firstMessage = 'Please enter Item qty (> 0) for row ' + (rowIndex + 1);
                        firstEl = $qty;
                    }
                    markInvalid($qty);
                }
            });

            return {
                ok,
                firstMessage,
                firstEl
            };
        }


        function validateReceipts() {
            let ok = true,
                firstMessage = null,
                firstEl = null;
            $('#rvWrapper .rv-row').each(function(i) {
                const $row = $(this);
                const $acc = $row.find('.rv-account');
                const $amt = $row.find('.rv-amount');
                const amtVal = parseFloat($amt.val() || '0') || 0;

                if (amtVal > 0 && (!$acc.val() || $acc.val() === "")) {
                    ok = false;
                    if (!firstMessage) {
                        firstMessage = 'Please select Account for receipt row ' + (i + 1);
                        firstEl = $acc;
                    }
                    markInvalid($acc);
                }
            });
            return {
                ok,
                firstMessage,
                firstEl
            };
        }

        // Enable/disable Post button depending on receipts validity and row validation
        function updatePostButtonState() {
            const postBtn = $('#btnPosted');
            const headerPostBtn = $('#btnHeaderPosted');

            const rowsValid = validateRows().ok;
            const receiptsValid = validateReceipts().ok;

            // If there are any receipt amount inputs with value > 0, require receiptsValid
            let hasReceiptAmount = false;
            $('.rv-amount').each(function() {
                if (toNum($(this).val()) > 0) hasReceiptAmount = true;
            });

            const enable = rowsValid && (!hasReceiptAmount || receiptsValid);
            postBtn.prop('disabled', !enable);
            headerPostBtn.prop('disabled', !enable);
        }

        /**
         * validateHeader() -> Type & Party mandatory
         */
        function validateHeader() {
            let ok = true,
                firstMessage = null,
                firstEl = null;
            // Type (partyType) - we expect a radio selected
            const partyType = $('input[name="partyType"]:checked').val();
            if (!partyType) {
                ok = false;
                firstMessage = 'Please select Type';
                firstEl = $('input[name="partyType"]').first();
                // mark buttons visually
                $('#partyTypeGroup').addClass('invalid-cell');
            } else {
                $('#partyTypeGroup').removeClass('invalid-cell');
            }

            // Party / Customer
            const cust = $('#customerSelect').val();
            if (!cust && partyType !== 'walking') {
                ok = false;
                if (!firstMessage) {
                    firstMessage = 'Please select Party (Customer / Vendor)';
                    firstEl = $('#customerSelect');
                }
                markInvalid($('#customerSelect'));
            }

            return {
                ok,
                firstMessage,
                firstEl
            };
        }

        /**
         * validateFormAll() -> run header, rows, receipts
         * returns { ok, message, el }
         */
        function validateFormAll() {
            clearAllInvalids();

            // header
            const h = validateHeader();
            if (!h.ok) {
                return {
                    ok: false,
                    message: h.firstMessage,
                    el: h.firstEl
                };
            }

            // rows
            const r = validateRows();
            if (!r.ok) {
                return {
                    ok: false,
                    message: r.firstMessage,
                    el: r.firstEl
                };
            }

            // receipts
            const rec = validateReceipts();
            if (!rec.ok) {
                return {
                    ok: false,
                    message: rec.firstMessage,
                    el: rec.firstEl
                };
            }

            // if all ok
            return {
                ok: true
            };
        }

        /* ---------- Hook validation into Save / Post ---------- */

        // override Save button to validate first and check credit limit
        $('#btnSave').off('click').on('click', function() {
            cleanupEmptyRows(); // remove empty rows
            updateGrandTotals(); // recompute totals after cleanup
            refreshPostedState();

            // run the existing validation pipeline
            const v = validateFormAll();
            if (!v.ok) {
                showAlert('danger', v.message);
                if (v.el && v.el.length) {
                    v.el.focus();
                    if (v.el.hasClass('js-customer')) v.el.select2?.('open');
                }
                return;
            }

            // CHECK CREDIT LIMIT BEFORE SAVING
            const partyType = $('input[name="partyType"]:checked').val();
            let cust = null;

            // Walking customers use manual name input, others use dropdown
            if (partyType === 'walking') {
                cust = $('#customerDisplay').val();
            } else {
                cust = $('#customerSelect').val();
            }

            const payable = parseFloat($('#totalBalance').val() || $('#tPayable').text() || 0) || 0;

            // For walking customers, skip credit check and save directly
            if (partyType === 'walking') {
                ensureSaved();
            } else if (cust) {
                $.get('/get-customer/' + cust)
                    .done(function(res) {
                        const credit = parseFloat(res.credit_limit || 0) || 0;
                        if (credit > 0 && payable > credit) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Credit limit exceeded',
                                html: `Customer credit limit is <b>${credit.toFixed(2)}</b>.<br>Payable amount is <b>${payable.toFixed(2)}</b>.`,
                            });
                            return;
                        }
                        // proceed to save
                        ensureSaved();
                    })
                    .fail(function() {
                        // If customer lookup fails, proceed with save but log
                        ensureSaved();
                    });
            } else {
                ensureSaved();
            }
        });


        // override Post buttons to validate first AND CHECK CREDIT LIMIT
        $('#btnHeaderPosted, #btnPosted').off('click').on('click', function() {
            const $btn = $(this);
            cleanupEmptyRows();
            updateGrandTotals();
            refreshPostedState();
            
            const v = validateFormAll();
            if (!v.ok) {
                showAlert('danger', v.message);
                if (v.el && v.el.length) {
                    v.el.focus();
                    if (v.el.hasClass('js-customer')) v.el.select2?.('open');
                }
                return;
            }

            if (!canPost()) {
                showAlert('danger', 'No valid item lines to post');
                return;
            }

            // 🔴 VALIDATION: Cash/Walking customers MUST have at least 1 receipt amount
            const partyType = $('input[name="partyType"]:checked').val();
            if (['cash', 'walking'].includes(partyType?.toLowerCase())) {
                let hasValidReceipt = false;
                $('.rv-amount').each(function(i) {
                    if (toNum($(this).val()) > 0) {
                        hasValidReceipt = true;
                        return false;
                    }
                });
                
                if (!hasValidReceipt) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Payment Required',
                        text: 'Amount required in at least 1 bank account for ' + partyType + ' customer',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
            }

            setBusy($btn);

            // ✅ CHECK CREDIT LIMIT BEFORE POSTING
            let cust = null;
            if (partyType === 'walking') {
                cust = $('#customerDisplay').val();
            } else {
                cust = $('#customerSelect').val();
            }

            const payable = parseFloat($('#totalBalance').val() || $('#tPayable').text() || 0) || 0;

            if (partyType === 'walking') {
                ensureSaved().then(() => {
                    faraz();
                    setReady($btn);
                }).catch(() => setReady($btn));
            } else if (cust) {
                $.get('/get-customer/' + cust)
                    .done(function(res) {
                        const credit = parseFloat(res.credit_limit || 0) || 0;
                        if (credit > 0 && payable > credit) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Credit limit exceeded',
                                html: `Customer credit limit is <b>Rs. ${credit.toFixed(2)}</b>.<br>Payable amount is <b>Rs. ${payable.toFixed(2)}</b>.`,
                            });
                            setReady($btn);
                            return;
                        }
                        ensureSaved().then(() => {
                            faraz();
                            setReady($btn);
                        }).catch(() => setReady($btn));
                    })
                    .fail(function() {
                        showAlert('danger', 'Failed to verify customer credit limit. Please try again.');
                        setReady($btn);
                    });
            } else {
                showAlert('danger', 'Please select a customer before posting');
                setReady($btn);
            }
        });


        function isRowMeaningful($row) {
            const prod = $row.find('.product-select').val();
            const wh = $row.find('.warehouse-id').val();
            const qty = parseFloat($row.find('.sales-qty').val() || '0') || 0;
            const discPct = parseFloat($row.find('.discount-value').val() || '0') || 0;
            const discAmt = parseFloat($row.find('.discount-amount').val() || '0') || 0;

            // consider row meaningful if product selected OR qty > 0 OR discount entered OR warehouse selected
            return !!prod || !!wh || qty > 0 || discPct !== 0 || discAmt !== 0;
        }

        function cleanupEmptyRows() {
            $('#salesTableBody tr').each(function() {
                const $r = $(this);
                const prod = $r.find('.product-select').val();
                // const wh = $r.find('.warehouse').val();
                const qty = parseFloat($r.find('.sales-qty').val() || '0') || 0;

                // Remove row when qty is zero or product is empty
                if ((qty <= 0) || (!prod || prod === '')) {
                    // ensure we keep at least one row in UI
                    if ($('#salesTableBody tr').length > 1) {
                        $r.remove();
                    } else {
                        // if only one row left, clear its fields instead of removing (keeps UI stable)
                        $r.find('select').val('');
                        $r.find('input').val('');
                        $r.find('.stock').val('');
                        $r.find('.sales-amount').val('0');
                        // Set discount fields to 0
                        $r.find('.discount-value').val('0.00');
                        $r.find('.discount-amount').val('0.00');
                    }
                }
            });

            // Ensure all remaining rows have discount fields populated
            $('#salesTableBody tr').each(function() {
                const $discValue = $(this).find('.discount-value');
                const $discAmount = $(this).find('.discount-amount');
                if (!$discValue.val()) $discValue.val('0.00');
                if (!$discAmount.val()) $discAmount.val('0.00');
            });

            // ensure at least one blank row exists
            if ($('#salesTableBody tr').length === 0) addNewRow();
        }
    </script>

    <script>
        // Product dropdown infinite scroll with Select2
        function initProductSelect2(
            selector = '.product-select',
            url = '/search-products-sale',
            searchUrl = '/search_products'
        ) {
            $(selector).select2({
                ajax: {
                    transport: function(params, success, failure) {
                        // prefer params.data.term which Select2 populates
                        let term = (params.data && (params.data.term || params.data.q)) || '';
                        let page = (params.data && (params.data.page || 1)) || 1;
                        let branchId = $('#branch_id').val() || $('[name="branch_id"]').val() || window.USER_BRANCH_ID || '';
                        let ajaxUrl = term && term.length > 0 ? searchUrl : url;
                        $.ajax({
                            url: ajaxUrl,
                            data: {
                                q: term,
                                page: page,
                                branch_id: branchId
                            },
                            dataType: 'json',
                            success: function(data) {
                                success(data);
                            },
                            error: failure
                        });
                    },
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1,
                            branch_id: $('#branch_id').val() || $('[name="branch_id"]').val() || window.USER_BRANCH_ID || ''
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        let results = [];
                        const mapProduct = function(p) {
                            const ownershipBadge = p.is_owner ? '' : ' [SECONDARY]';
                            return {
                                id: p.id,
                                text: (p.item_name || '') + ownershipBadge,
                                item_code: p.item_code,
                                item_name: p.item_name,
                                brand_name: p.brand_name,
                                stock: p.stock,
                                price: p.retail_price || p.price,
                                is_owner: p.is_owner,
                                branch_id: p.branch_id
                            };
                        };

                        if (Array.isArray(data)) {
                            results = data.map(mapProduct);
                            return {
                                results: results,
                                pagination: {
                                    more: false
                                }
                            };
                        }

                        results = (data.products || []).map(mapProduct);

                        return {
                            results: results,
                            pagination: {
                                more: !!data.has_more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(p) {
                    if (!p.id) return p.text;
                    const stockBadge = '<span class="badge bg-light text-primary border" style="font-size: 10.5px; padding: 2px 7px;">Stock: ' + (p.stock || 0) + '</span>';
                    return $(
                        '<div class="d-flex justify-content-between align-items-center w-100 py-0.5">' +
                            '<span class="fw-bold product-title" style="font-size: 13px;">' + (p.item_name || p.text) + '</span>' +
                            '<div class="ms-2 flex-shrink-0">' +
                                stockBadge +
                            '</div>' +
                        '</div>'
                    );
                },
                templateSelection: function(p) {
                    if (!p.id) return p.text;
                    return p.item_name || p.text;
                },
                minimumInputLength: 0,
                placeholder: 'Search Product (Item Code / Name / Brand / Model)...',
                allowClear: true,
                width: '100%'
            });
        }



        $(document).ready(function() {
            initProductSelect2('.product-select', '/search-products-sale', '/search_products');

            // 1. Warehouse Selection Change Handler
            $(document).on('change', '.warehouse-select', function() {
                const $row = $(this).closest('tr');
                const $selectedOpt = $(this).find('option:selected');
                const whId = $(this).val();
                const stockQty = parseFloat($selectedOpt.data('stock') || 0);
                const productId = $row.find('.product-select').val();

                // Update hidden warehouse-id input & form field name
                $row.find('.warehouse-id').val(whId);
                if (productId) {
                    $row.find('.warehouse-id').attr('name', `warehouse_id[${productId}]`);
                }

                // Update stock input & available-stock data attribute
                $row.find('.stock').val(stockQty).data('available-stock', stockQty);
                $row.find('.sales-qty').data('available-stock', stockQty);

                // Recompute row & totals
                computeRow($row);
                updateGrandTotals();
                refreshPostedState();
            });

            // 2. Product Select Handler
            $(document).on('select2:select', '.product-select', function(e) {
                if (e && e.params && e.params.data && e.params.data.id) {
                    const branchId = $('#branch_id').val() || $('[name="branch_id"]').val() || window.USER_BRANCH_ID || '';
                    $.get('/get-product-details/' + e.params.data.id, { branch_id: branchId }, function(data) {
                        const $row = $(e.target).closest('tr');
                        if (data && data.product) {
                            const price = parseFloat(data.product.retail_price ?? data.product.price ?? 0).toFixed(2);
                            const totalBranchStock = data.available_stock !== undefined ? data.available_stock : 
                                            ((data.product.stock && (data.product.stock.qty ?? data.product.stock)) || 0);

                            // Build Warehouse breakdown dropdown options for this row
                            const $whSelect = $row.find('.warehouse-select');
                            $whSelect.empty();

                            let selectedStock = totalBranchStock;
                            let selectedWhId = '';

                            if (data.warehouse_breakdown && data.warehouse_breakdown.length > 0) {
                                data.warehouse_breakdown.forEach(function(wb) {
                                    const wid = wb.warehouse_id || '';
                                    const wname = wb.warehouse_name || 'Warehouse';
                                    const wqty = parseFloat(wb.quantity || 0);
                                    
                                    $whSelect.append(`<option value="${wid}" data-stock="${wqty}">🏬 ${wname} (${wqty})</option>`);
                                });

                                // Add All / Shop Stock option
                                $whSelect.prepend(`<option value="" data-stock="${totalBranchStock}">🏢 All / Shop Stock (${totalBranchStock})</option>`);

                                // Auto-select first warehouse with stock if available
                                const $firstWhWithStock = $whSelect.find('option[value!=""]').filter(function() {
                                    return parseFloat($(this).data('stock')) > 0;
                                }).first();

                                if ($firstWhWithStock.length) {
                                    $firstWhWithStock.prop('selected', true);
                                    selectedWhId = $firstWhWithStock.val();
                                    selectedStock = parseFloat($firstWhWithStock.data('stock'));
                                } else {
                                    $whSelect.find('option:first').prop('selected', true);
                                    selectedWhId = $whSelect.val();
                                    selectedStock = parseFloat($whSelect.find('option:selected').data('stock') || totalBranchStock);
                                }
                            } else {
                                $whSelect.append(`<option value="" data-stock="${totalBranchStock}">🏢 General Branch Stock (${totalBranchStock})</option>`);
                            }

                            // Set hidden warehouse-id input
                            $row.find('.warehouse-id')
                                .attr('name', `warehouse_id[${data.product.id}]`)
                                .val(selectedWhId);

                            $row.find('.retail-price').val(price);
                            $row.find('.stock').val(selectedStock).data('available-stock', selectedStock);
                            $row.find('.sales-qty').data('available-stock', selectedStock);
                            $row.find('.product-select').val(data.product.id).trigger('change');

                            // Recompute row and totals
                            computeRow($row);
                            updateGrandTotals();
                            refreshPostedState();
                        }
                    });
                }
            });
        });
    </script>

    <!-- ============================================ -->
    <!-- btnPosted3 - DRAFT POSTING (Save → AjaxPostDraft) -->
    <!-- ============================================ -->
    <script>
        /**
         * 🎯 btnPosted3 Handler - DRAFT MODE POSTING
         * 
         * Professional ERP Flow:
         * 1. Validate form (items, party type, receipts)
         * 2. Check credit limit
         * 3. SAVE booking via ensureSaved()
         * 4. POST to ajaxPostDraft endpoint
         * 5. Proper error handling & button state management
         */
        $('#btnPosted3').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            console.log('🎯 btnPosted3 clicked - Starting draft posting flow');

            // Step 1: Cleanup & Validate
            cleanupEmptyRows();
            updateGrandTotals();
            refreshPostedState();

            const v = validateFormAll();
            if (!v.ok) {
                showAlert('danger', v.message);
                if (v.el && v.el.length) {
                    v.el.focus();
                    if (v.el.hasClass('js-customer')) v.el.select2?.('open');
                }
                return;
            }

            if (!canPost()) {
                showAlert('danger', 'No valid item lines to post');
                return;
            }

            // Step 2: Receipt validation for cash/walking customers
            const partyType = $('input[name="partyType"]:checked').val();
            if (['cash', 'walking'].includes(partyType?.toLowerCase())) {
                let hasValidReceipt = false;
                $('.rv-amount').each(function(i) {
                    const amt = toNum($(this).val());
                    if (amt > 0) {
                        hasValidReceipt = true;
                        return false;
                    }
                });
                
                if (!hasValidReceipt) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Payment Required',
                        text: 'Amount required in at least 1 bank account for ' + partyType + ' customer',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
            }

            let cust = null;
            if (partyType === 'walking') {
                cust = $('#customerDisplay').val();
            } else {
                cust = $('#customerSelect').val();
            }

            const payable = parseFloat($('#totalBalance').val() || $('#tPayable').text() || 0) || 0;

            const $btn = $(this);
            setBusy($btn);

            if (partyType === 'walking') {
                proceedWithDraftPost($btn);
            } else if (cust) {
                $.get('/get-customer/' + cust)
                    .done(function(res) {
                        const credit = parseFloat(res.credit_limit || 0) || 0;
                        if (credit > 0 && payable > credit) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Credit limit exceeded',
                                html: `Customer credit limit is <b>Rs. ${credit.toFixed(2)}</b>.<br>Payable amount is <b>Rs. ${payable.toFixed(2)}</b>.`,
                            });
                            setReady($btn);
                            return;
                        }
                        proceedWithDraftPost($btn);
                    })
                    .fail(function() {
                        showAlert('danger', 'Failed to verify customer credit limit. Please try again.');
                        setReady($btn);
                    });
            } else {
                showAlert('danger', 'Please select a customer before posting');
                setReady($btn);
            }
        });

        /**
         * 🔄 STEP 1: SAVE BOOKING
         * 🔄 STEP 2: POST TO DRAFT (ajaxPostDraft)
         */
        function proceedWithDraftPost($btn = null) {
            console.log('📝 proceedWithDraftPost - Starting save → post flow');
            if ($btn) setBusy($btn);
            else setBusy();

            // STEP 1: Save booking first
            ensureSaved()
                .then(function(bookingId) {
                    console.log('✅ Booking saved:', bookingId);
                    console.log('📤 Now posting to ajaxPostDraft...');

                    // Collect form data for ajaxPostDraft
                    let formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('booking_id', bookingId);

                    // Collect warehouse selections per product
                    $('#salesTableBody tr').each(function() {
                        const productId = $(this).find('.product-select').val();
                        const warehouseId = $(this).find('.warehouse-id').val();
                        if (productId) {
                            formData.append(`warehouse_id[${productId}]`, warehouseId || '');
                        }
                    });

                    // Collect receipt rows
                    $('.rv-account').each(function(i) {
                        const acc = $(this).val();
                        const amt = $('.rv-amount').eq(i).val() || '0';
                        if (acc) {
                            formData.append(`receipt_account_id[]`, acc);
                            formData.append(`receipt_amount[]`, amt);
                        }
                    });

                    // STEP 2: Call ajaxPostDraft
                    $.ajax({
                        url: '{{ route('sale.ajax.post-draft') }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(res) {
                            console.log('✅ Draft post successful:', res);

                            if (res && res.ok) {
                                showAlert('success', 'Draft posted! Items ready for delivery. Stock will be deducted on gate pass.');
                                $('#is_posted').val('1');
                                $('#btnPosted3').prop('disabled', true);
                                
                                // Disable other post buttons
                                $('#btnPosted, #btnPosted2, #btnHeaderPosted, #btnSave').prop('disabled', true);
                                refreshPostedState();

                                // Open invoice in new tab
                                if (res.invoice_url) {
                                    window.open(res.invoice_url, '_blank');
                                }
                            } else {
                                $('#btnPosted3').prop('disabled', false).css('opacity', '1');
                                showAlert('danger', res.msg || 'Draft post failed');
                            }
                        },
                        error: function(xhr) {
                            console.error('❌ Draft post error:', xhr);
                            $('#btnPosted3').prop('disabled', false).css('opacity', '1');

                            let msg = 'Server error while posting';
                            try {
                                const json = xhr.responseJSON || JSON.parse(xhr.responseText || '{}');
                                msg = json.message || json.msg || json.error || msg;
                            } catch (e) {
                                if (xhr.responseText) {
                                    msg = xhr.responseText.split('\n')[0];
                                }
                            }

                            showAlert('danger', msg);
                        }
                    });
                })
                .catch(function(err) {
                    console.error('❌ Save failed:', err);
                    showAlert('danger', 'Failed to save booking before posting');
                })
                .finally(() => {
                    setReady($btn);
                });
        }

        /**
         * 🔧 Enable btnPosted3 after successful warehouse/main posting
         */
        function enableBtnPosted3() {
            $('#btnPosted3').prop('disabled', false).css('opacity', '1');
            $('#is_posted').val('1');
            console.log('✅ btnPosted3 enabled');
        }

        /**
         * 🔧 Restore btnPosted3 state on page load (for editing existing sales)
         */
        function restorePosted3StateIfExists() {
            const bookingId = $('#booking_id').val();
            const isFinalized = $('#is_finalized').val() === '1';

            if (!bookingId) {
                console.log('⏸️ New sale - btnPosted3 will be controlled by refreshPostedState()');
                return;
            }

            if (isFinalized) {
                $('#btnPosted3')
                    .prop('disabled', true)
                    .html('✓ Finalized')
                    .addClass('btn-success')
                    .removeClass('btn-outline-success');
                console.log('✅ btnPosted3 shown as finalized');
            }
        }

        // Restore on document ready
        $(document).ready(function() {
            setTimeout(function() {
                restorePosted3StateIfExists();
            }, 100);

            // 🔹 Open Quick Add Customer Modal when clicking + Add button
            $(document).on('click', '#btnOpenAddCustomerModal', function(e) {
                e.preventDefault();
                $('#quickCustomerForm')[0].reset();
                $('#quickCustomerAlert').addClass('d-none').removeClass('alert-danger alert-success').text('');

                const branchId = $('#branch_id').val() || $('[name="branch_id"]').val() || '1';
                const partyType = $('input[name="partyType"]:checked').val() || 'credit';

                $('#qc_branch_id').val(branchId);
                $('#qc_customer_type').val(partyType === 'cash' ? 'cash' : 'credit');

                // Fetch Next Customer ID
                $.ajax({
                    url: '{{ route('customers.nextId') }}',
                    type: 'GET',
                    data: { branch_id: branchId },
                    success: function(resp) {
                        if (resp && resp.customer_id) {
                            $('#qc_customer_id').val(resp.customer_id);
                        }
                    },
                    error: function() {
                        $('#qc_customer_id').val('CUST-' + Date.now().toString().slice(-4));
                    }
                });

                const modalEl = document.getElementById('quickAddCustomerModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                modalEl.addEventListener('shown.bs.modal', function () {
                    $('#qc_customer_name').focus();
                }, { once: true });
            });

            // 🔹 Save Quick Add Customer Form via AJAX
            $(document).on('submit', '#quickCustomerForm', function(e) {
                e.preventDefault();
                const $btn = $('#btnSaveQuickCustomer');
                const origText = $btn.html();

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
                $('#quickCustomerAlert').addClass('d-none');

                $.ajax({
                    url: '{{ route('customers.store') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(res) {
                        $btn.prop('disabled', false).html(origText);
                        if (res.success && res.customer) {
                            const newCust = res.customer;
                            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('quickAddCustomerModal'));
                            if (modalInstance) modalInstance.hide();

                            showAlert('success', 'Customer "' + (newCust.customer_name || newCust.customer_id) + '" created successfully!');

                            // Reload customer dropdown and auto select
                            const cType = $('input[name="partyType"]:checked').val() || 'credit';
                            loadCustomersByType(cType, newCust.id);
                        } else {
                            $('#quickCustomerAlert').removeClass('d-none').addClass('alert-danger').text(res.message || 'Error saving customer.');
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html(origText);
                        let errStr = 'Failed to save customer.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errs = Object.values(xhr.responseJSON.errors).flat();
                            errStr = errs.join('<br>');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errStr = xhr.responseJSON.message;
                        }
                        $('#quickCustomerAlert').removeClass('d-none').addClass('alert-danger').html(errStr);
                    }
                });
            });
        });
    </script>

    <!-- QUICK ADD CUSTOMER MODAL -->
    <div class="modal fade" id="quickAddCustomerModal" tabindex="-1" aria-labelledby="quickAddCustomerModalLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header text-white py-2.5 px-3 rounded-top-4" style="background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;">
                    <h6 class="modal-title fw-bold text-white mb-0" id="quickAddCustomerModalLabel">
                        <i class="fas fa-user-plus me-1.5"></i> Add New Customer
                    </h6>
                    <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickCustomerForm" autocomplete="off">
                    @csrf
                    <div class="modal-body p-3">
                        <div id="quickCustomerAlert" class="alert d-none py-1.5 px-2.5 small mb-2"></div>

                        <input type="hidden" name="branch_id" id="qc_branch_id" value="{{ Auth::user()->branch_id ?? 1 }}">

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">Customer Code / ID</label>
                                <input type="text" class="form-control form-control-sm bg-light" name="customer_id" id="qc_customer_id" readonly required placeholder="Auto-generated">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">Customer Type</label>
                                <select class="form-select form-select-sm" name="customer_type" id="qc_customer_type">
                                    <option value="credit" selected>Credit</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold mb-1">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm fw-semibold" name="customer_name" id="qc_customer_name" required placeholder="Enter customer full name">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">Mobile / Phone #</label>
                                <input type="text" class="form-control form-control-sm" name="mobile" id="qc_mobile" placeholder="03xx-xxxxxxx">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">Credit Limit (Rs.)</label>
                                <input type="number" step="any" min="0" class="form-control form-control-sm" name="credit_limit" id="qc_credit_limit" value="0">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold mb-1">Address</label>
                                <input type="text" class="form-control form-control-sm" name="address" id="qc_address" placeholder="Customer address / location">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">Opening Balance (Dr)</label>
                                <input type="number" step="any" min="0" class="form-control form-control-sm" name="opening_balance" id="qc_opening_balance" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-3 rounded-bottom-4 justify-content-between">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold" id="btnSaveQuickCustomer" style="background:#2563eb !important; border-color:#2563eb !important;">
                            <i class="fas fa-check me-1"></i> Save & Select
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

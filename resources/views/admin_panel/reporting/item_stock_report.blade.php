@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #0d9f6e;
        --coa-border: #e2e8f0;
    }

    .rpt-wrapper {
        padding: 12px 0 30px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .rpt-header-bar {
        background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 60%, var(--coa-navy-light) 100%);
        border-radius: 10px;
        padding: 16px 22px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        box-shadow: 0 4px 15px rgba(15, 31, 56, 0.15);
        margin-bottom: 18px;
    }

    .rpt-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--coa-gold);
        border: 1px solid rgba(200, 151, 58, 0.3);
        flex-shrink: 0;
    }

    .rpt-header-title {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        line-height: 1.2;
    }

    .rpt-header-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.85);
        margin-top: 3px;
    }

    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.03em;
        margin-bottom: 4px;
        display: block;
    }

    #stockTable th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 8px;
        border: 1px solid #1e3a5f;
    }

    /* Select2 Responsive Fix */
    .select2-container {
        width: 100% !important;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
    }

    /* Swipe hint bar for mobile */
    .swipe-hint-bar {
        display: none;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 11.5px;
        color: #475569;
        margin-bottom: 10px;
    }

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 768px) {
        .rpt-wrapper {
            padding: 6px 0 15px 0;
        }

        .rpt-header-bar {
            flex-direction: column;
            align-items: stretch;
            padding: 12px 14px;
            gap: 10px;
        }

        .rpt-header-title {
            font-size: 16px;
        }

        .rpt-header-sub {
            font-size: 11px;
        }

        .rpt-header-bar .d-flex.gap-2 {
            justify-content: stretch;
            width: 100%;
        }

        .rpt-header-bar .d-flex.gap-2 button {
            flex: 1;
            padding: 8px 10px;
            font-size: 12px;
            justify-content: center;
        }

        /* KPI Cards on Mobile: 2 Columns */
        #kpiSummaryCards {
            margin-bottom: 10px !important;
        }

        .kpi-col {
            padding-left: 4px !important;
            padding-right: 4px !important;
            margin-bottom: 8px !important;
        }

        .kpi-card-box {
            padding: 10px 12px !important;
        }

        .kpi-card-box h4 {
            font-size: 14.5px !important;
        }

        .kpi-card-box .text-muted {
            font-size: 9.5px !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .kpi-icon-box {
            width: 32px !important;
            height: 32px !important;
            font-size: 14px !important;
        }

        /* Swipe hint visible on mobile */
        .swipe-hint-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* DataTables Mobile Layout */
        .dataTables_wrapper .dataTables_filter {
            float: none !important;
            text-align: left !important;
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            margin-left: 0 !important;
            height: 36px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }

        .dataTables_wrapper .dataTables_length {
            float: none !important;
            text-align: left !important;
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_paginate {
            float: none !important;
            text-align: center !important;
            margin-top: 10px;
        }

        /* Table Touch Scroll Optimization */
        .table-responsive {
            border: 1px solid var(--coa-border);
            border-radius: 6px;
            -webkit-overflow-scrolling: touch;
        }

        #stockTable {
            font-size: 11px !important;
            white-space: nowrap;
        }

        #stockTable th {
            padding: 8px 6px !important;
            font-size: 10.5px !important;
        }

        #stockTable td {
            padding: 8px 6px !important;
            vertical-align: middle;
        }

        /* Sticky Item Code Column for Mobile Horizontal Scroll */
        #stockTable th:nth-child(2),
        #stockTable td:nth-child(2) {
            position: sticky;
            left: 0;
            z-index: 2;
            box-shadow: 2px 0 5px rgba(0,0,0,0.06);
        }

        #stockTable td:nth-child(2) {
            background-color: #f8fafc !important;
        }

        #stockTable th:nth-child(2) {
            background-color: #0f1f38 !important;
            z-index: 3;
        }

        .warehouse-btn {
            padding: 2px 6px !important;
            font-size: 10px !important;
        }
    }

    /* ── Formal Print Document Styles ──── */
    #formalPrintDoc {
        display: none;
        background: #fff;
        color: #000;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .print-doc-header { margin-bottom: 10px; }
    .print-company-title { font-size: 15pt; font-weight: 800; color: #000; margin-bottom: 2px; text-transform: uppercase; letter-spacing: .02em; }
    .print-doc-title { font-size: 21pt; font-weight: 900; color: #000; margin-bottom: 2px; }
    .print-doc-period { font-size: 11pt; font-weight: 600; color: #1e293b; }
    .print-top-divider { border-bottom: 2px solid #000; margin-top: 6px; margin-bottom: 12px; }

    .print-tb-table { width: 100% !important; max-width: 100% !important; table-layout: fixed !important; border-collapse: collapse !important; font-size: 10pt !important; margin-bottom: 14px !important; color: #000 !important; }
    .print-tb-table thead { display: table-header-group !important; }
    .print-tb-table th { border-top: 1.5px solid #000 !important; border-bottom: 1.5px solid #000 !important; padding: 8px 4px !important; background: #f8fafc !important; font-weight: 800 !important; color: #000 !important; font-size: 9pt !important; text-transform: uppercase !important; vertical-align: middle !important; line-height: 1.25 !important; }
    .print-tb-table td { border-bottom: 1px solid #cbd5e1 !important; padding: 7px 4px !important; vertical-align: middle !important; color: #000 !important; font-size: 10pt !important; line-height: 1.3 !important; overflow: hidden; word-wrap: break-word; }
    .print-tb-table tr { page-break-inside: avoid !important; }
    .print-tb-table .num-cell { text-align: right !important; font-variant-numeric: tabular-nums !important; font-feature-settings: "tnum" !important; white-space: nowrap !important; font-size: 10pt !important; letter-spacing: -0.01em !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }
    .print-tb-table tr.p-tfoot td { border-top: 1.5px solid #000 !important; border-bottom: 3px double #000 !important; font-weight: 800 !important; padding: 9px 4px !important; background: #fff !important; font-size: 10.5pt !important; color: #000 !important; }

    .print-doc-footer { margin-top: 24px; font-size: 9.5pt; color: #000; page-break-inside: avoid; }
    .print-footer-info { display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 6px; margin-bottom: 38px; font-weight: 600; color: #000; font-size: 9pt; }
    .print-sign-row { display: flex; justify-content: space-between; padding: 0 15px; }
    .print-sign-box { border-top: 1.5px dashed #000; width: 140px; text-align: center; padding-top: 6px; font-size: 9.5pt; font-weight: 700; color: #000; }

    /* ── Full-Page Clean Report Preview Overlay ──── */
    .report-preview-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        width: 100vw; height: 100vh;
        background: #f1f5f9;
        z-index: 99999;
        display: flex; flex-direction: column;
        overflow: hidden;
    }
    .report-preview-topbar {
        height: 52px; background: #0f1f38; color: #fff;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.15); flex-shrink: 0;
    }
    .report-preview-body {
        flex: 1; overflow-y: auto; overflow-x: auto;
        padding: 24px 20px 50px 20px;
        display: flex; justify-content: center;
    }
    .report-preview-paper {
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 32px 34px;
        width: 100%; max-width: 1100px;
        height: max-content; box-sizing: border-box;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 6mm 6mm 6mm 6mm !important;
        }
        body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
        .main-content { padding: 0 !important; margin: 0 !important; }
        .rpt-wrapper, .navbar, .sidebar, .rpt-header-bar, #stockFilterForm, #kpiSummaryCards, #reportContent, .no-print, .btn, .card {
            display: none !important;
        }
        #formalPrintDoc {
            display: block !important;
            padding: 0 !important; margin: 0 !important;
            width: 100% !important; max-width: 100% !important;
        }
        .print-tb-table { width: 100% !important; max-width: 100% !important; table-layout: fixed !important; }
        .print-tb-table th { font-size: 8.5pt !important; padding: 6px 3px !important; }
        .print-tb-table td { font-size: 9pt !important; padding: 5px 3px !important; }
        .print-tb-table .num-cell { font-size: 9pt !important; }
        .print-tb-table tr.p-tfoot td { font-size: 9.5pt !important; padding: 7px 3px !important; }
        .print-tb-table th, .print-tb-table td { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Corporate Header Bar --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Item Stock Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-warehouse mr-1" style="color: var(--coa-gold);"></i> Track opening, purchased, sold, reserved & balance per product &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" onclick="openPrintPreview()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.4);" title="Clean Full-Page Preview">
                        <i class="fas fa-eye mr-1"></i> Preview
                    </button>
                    <button type="button" id="waShareBtn" onclick="shareWhatsApp()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(37, 211, 102, 0.2); border-color: #25D366; color: #25D366;">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button type="button" onclick="showExportOptions()" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-download mr-1 text-primary"></i> Export
                    </button>
                </div>
            </div>

            {{-- 2. Filter Card --}}
            <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form id="stockFilterForm" class="row g-2 align-items-end mb-0">
                        {{-- Branch Selector (Super Admin Only) --}}
                        @if($isSuperAdmin)
                        <div class="col-md-3">
                            <label class="f-label">Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                <option value="all">-- All Branches --</option>
                                @foreach($userBranches as $branch)
                                    <option value="{{ $branch->id }}" @selected($branch->id == $selectedBranchId)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="f-label">Product</label>
                            <select name="product_id" id="product_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Products --</option>
                                @foreach($products as $prod)
                                    @if($prod->has_variants && $prod->variants && $prod->variants->isNotEmpty())
                                        <option value="{{ $prod->id }}">{{ $prod->item_code }} - {{ $prod->item_name }} (All Variants)</option>
                                        @foreach($prod->variants as $v)
                                            <option value="{{ $prod->id }}:{{ $v->id }}">&nbsp;&nbsp;&nbsp;&nbsp;↳ {{ $prod->item_code }} - {{ $prod->item_name }} ({{ $v->color ?: $v->variant_name }})</option>
                                        @endforeach
                                    @else
                                        <option value="{{ $prod->id }}">{{ $prod->item_code }} - {{ $prod->item_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Search Stock
                            </button>
                        </div>
                        @else
                        {{-- Non-Admin User: Branch Display Only --}}
                        <div class="col-md-3">
                            <label class="f-label">Branch</label>
                            <div class="form-control form-control-sm bg-light font-weight-bold" style="height: 38px; display: flex; align-items: center; border: 1.5px solid #cbd5e1;">
                                {{ $userBranches[0]?->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-5">
                            <label class="f-label">Product</label>
                            <select name="product_id" id="product_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Products --</option>
                                @foreach($products as $prod)
                                    @if($prod->has_variants && $prod->variants && $prod->variants->isNotEmpty())
                                        <option value="{{ $prod->id }}">{{ $prod->item_code }} - {{ $prod->item_name }} (All Variants)</option>
                                        @foreach($prod->variants as $v)
                                            <option value="{{ $prod->id }}:{{ $v->id }}">&nbsp;&nbsp;&nbsp;&nbsp;↳ {{ $prod->item_code }} - {{ $prod->item_name }} ({{ $v->color ?: $v->variant_name }})</option>
                                        @endforeach
                                    @else
                                        <option value="{{ $prod->id }}">{{ $prod->item_code }} - {{ $prod->item_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Search Stock
                            </button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- 2.5 Summary KPI Cards --}}
            <div class="row g-2 mb-3" id="kpiSummaryCards" style="display: none;">
                <div class="col-6 col-md-3 kpi-col">
                    <div class="card border-0 shadow-sm p-3 kpi-card-box" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #1e3a5f !important; border: 1px solid var(--coa-border);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.05em;">Total Balance Stock</div>
                                <h4 class="font-weight-bold mb-0 text-dark mt-1" id="kpiTotalBalance">0.00</h4>
                            </div>
                            <div class="rounded-circle p-2 kpi-icon-box" style="background: rgba(30, 58, 95, 0.1); color: #1e3a5f; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-boxes fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 kpi-col">
                    <div class="card border-0 shadow-sm p-3 kpi-card-box" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #0d9f6e !important; border: 1px solid var(--coa-border);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.05em;">Delivered Qty (دیے)</div>
                                <h4 class="font-weight-bold mb-0 text-success mt-1" id="kpiTotalDelivered">0.00</h4>
                            </div>
                            <div class="rounded-circle p-2 kpi-icon-box" style="background: rgba(13, 159, 110, 0.1); color: #0d9f6e; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-truck-loading fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 kpi-col">
                    <div class="card border-0 shadow-sm p-3 kpi-card-box" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #d97706 !important; border: 1px solid var(--coa-border);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.05em;">Reserved Qty (ریزرورڈ)</div>
                                <h4 class="font-weight-bold mb-0 mt-1" style="color: #d97706 !important;" id="kpiTotalReserved">0.00</h4>
                            </div>
                            <div class="rounded-circle p-2 kpi-icon-box" style="background: rgba(217, 119, 6, 0.1); color: #d97706; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-clock fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 kpi-col">
                    <div class="card border-0 shadow-sm p-3 kpi-card-box" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #c8973a !important; border: 1px solid var(--coa-border);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.05em;">Total Stock Value</div>
                                <h4 class="font-weight-bold mb-0 mt-1" style="color: #b45309;" id="kpiTotalStockValue">Rs. 0.00</h4>
                            </div>
                            <div class="rounded-circle p-2 kpi-icon-box" style="background: rgba(200, 151, 58, 0.15); color: #c8973a; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-coins fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Report Table Card --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="reportContent">
                <div class="card-body p-3">
                    <div id="loader" style="display:none;text-align:center;padding:30px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Fetching stock metrics...</p>
                    </div>

                    {{-- Negative Stock Alert Banner --}}
                    <div id="negativeStockAlert" class="alert alert-danger font-weight-bold mb-3 d-none" style="border-radius: 8px;">
                        <i class="fas fa-exclamation-triangle me-1"></i> One or more products have <strong>Negative Stock</strong> due to force sales. Please review and restock.
                    </div>

                    {{-- Swipe hint indicator for mobile touch users --}}
                    <div class="swipe-hint-bar">
                        <span><i class="fas fa-arrows-alt-h text-primary me-1"></i> <strong>Swipe left/right</strong> to view full 13 columns</span>
                        <span class="badge bg-secondary text-white">پوری تفصیل</span>
                    </div>

                    <div class="table-responsive">
                        <table id="stockTable" class="table table-bordered mb-0" style="font-size: 12.5px; width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 3%;">#</th>
                                    <th style="width: 8%;">Item Code</th>
                                    <th>Item Name</th>
                                    <th class="text-end" style="width: 6%;">Opening</th>
                                    <th class="text-end" style="width: 6%;">Purchased</th>
                                    <th class="text-end" style="width: 8%;">Purch. Value</th>
                                    <th class="text-end" style="width: 8%; background: #064e3b !important;" title="Delivered / Dispatched Quantity (دیے کتنے)">Delivered (دیے)</th>
                                    <th class="text-end" style="width: 8%;">Delivered Value</th>
                                    <th class="text-end" style="width: 8%; background: #78350f !important;" title="Reserved Quantity Pending Delivery (ریزرورڈ)">Reserved (ریزرورڈ)</th>
                                    <th class="text-end" style="width: 7%;">Balance</th>
                                    <th class="text-end" style="width: 7%; background: #1e3a5f !important;" title="Unit Price / Wholesale Rate (قیمت / ریٹ)">Price (قیمت)</th>
                                    <th class="text-end" style="width: 9%;">Stock Value</th>
                                    <th class="text-center" style="width: 7%;">Warehouses</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <!-- Filled by AJAX -->
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 12.5px;">
                                    <th colspan="3" class="text-end font-weight-bold" style="font-family: sans-serif;">Total Stock Summary:</th>
                                    <th class="text-end" id="footOpening">0.00</th>
                                    <th class="text-end" id="footPurchased">0.00</th>
                                    <th class="text-end" id="footPurchAmount">Rs. 0.00</th>
                                    <th class="text-end text-success" id="footDelivered">0.00</th>
                                    <th class="text-end" id="footDeliveredAmount">Rs. 0.00</th>
                                    <th class="text-end text-warning" id="footReserved">0.00</th>
                                    <th class="text-end" id="footBalance">0.00</th>
                                    <th class="text-end">--</th>
                                    <th class="text-end text-success font-weight-bold" id="grandStockValue">0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>

            <!-- Warehouse Breakdown Modal -->
            <div class="modal fade" id="warehouseModal" tabindex="-1" role="dialog" aria-labelledby="warehouseModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow" style="border-radius: 10px; overflow: hidden;">
                        <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 100%);">
                            <h6 class="modal-title font-weight-bold mb-0" id="warehouseModalLabel">
                                <i class="fas fa-warehouse mr-1" style="color: var(--coa-gold);"></i> Warehouse Stock Breakdown
                            </h6>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <h6 id="modalProductName" class="mb-3"></h6>
                            <table class="table table-bordered table-hover" id="warehouseTable">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>Warehouse Name</th>
                                        <th>Location</th>
                                        <th class="text-end">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody id="warehouseTableBody">
                                    <!-- Filled dynamically -->
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Formal Clean Print Document (Used for Preview & Print) ── --}}
            <div id="formalPrintDoc" style="display:none;">
                <div class="print-doc-header">
                    <div class="print-company-title" id="p_branch_title">ALL BRANCHES</div>
                    <div class="print-doc-title">Item Stock Report</div>
                    <div class="print-doc-period" id="p_doc_period">Track opening, purchased, sold, reserved &amp; balance</div>
                    <div class="print-top-divider"></div>
                </div>

                <table class="print-tb-table">
                    <colgroup>
                        <col style="width: 3.5%;">
                        <col style="width: 9.5%;">
                        <col style="width: 20%;">
                        <col style="width: 6.5%;">
                        <col style="width: 6.5%;">
                        <col style="width: 8%;">
                        <col style="width: 7.5%;">
                        <col style="width: 8.5%;">
                        <col style="width: 7%;">
                        <col style="width: 7%;">
                        <col style="width: 7.5%;">
                        <col style="width: 8.5%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th style="text-align:center;">#</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th style="text-align:right;">Opening</th>
                            <th style="text-align:right;">Purchased</th>
                            <th style="text-align:right;">Purch. Value</th>
                            <th style="text-align:right;">Delivered</th>
                            <th style="text-align:right;">Delivered Value</th>
                            <th style="text-align:right;">Reserved</th>
                            <th style="text-align:right;">Balance</th>
                            <th style="text-align:right;">Price</th>
                            <th style="text-align:right;">Stock Value</th>
                        </tr>
                    </thead>
                    <tbody id="p_stock_tbody">
                        <!-- Populated by JS -->
                    </tbody>
                    <tfoot>
                        <tr class="p-tfoot">
                            <td colspan="3" style="text-align:right; font-weight:800; padding-right:10px;">TOTAL SUMMARY:</td>
                            <td class="num-cell" style="font-weight:800;" id="p_foot_opening">0.00</td>
                            <td class="num-cell" style="font-weight:800;" id="p_foot_purchased">0.00</td>
                            <td class="num-cell" style="font-weight:800;" id="p_foot_purch_value">0.00</td>
                            <td class="num-cell" style="font-weight:800; color:#047857;" id="p_foot_delivered">0.00</td>
                            <td class="num-cell" style="font-weight:800;" id="p_foot_delivered_value">0.00</td>
                            <td class="num-cell" style="font-weight:800; color:#b45309;" id="p_foot_reserved">0.00</td>
                            <td class="num-cell" style="font-weight:800;" id="p_foot_balance">0.00</td>
                            <td class="num-cell" style="text-align:center;">--</td>
                            <td class="num-cell" style="font-weight:800; color:#047857;" id="p_foot_stock_value">0.00</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="print-doc-footer">
                    <div class="print-footer-info">
                        <span>Printed on: <span id="p_print_time"></span></span>
                        <span>Item Stock &amp; Valuation Summary</span>
                        <span>ERP System &mdash; Stock Management Report</span>
                    </div>
                    <div class="print-sign-row">
                        <div class="print-sign-box">Prepared By</div>
                        <div class="print-sign-box">Store In-Charge</div>
                        <div class="print-sign-box">Approved By</div>
                    </div>
                </div>
            </div>

            {{-- ── Full-Page Clean Print Preview Modal ──── --}}
            <div id="reportPreviewModal" class="report-preview-overlay" style="display:none;" onclick="if(event.target===this) closePrintPreview()">
                <div class="report-preview-topbar">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-light text-dark font-weight-bold" style="font-size:11px; padding:4px 8px;">
                            <i class="fas fa-eye mr-1 text-primary"></i> PREVIEW
                        </span>
                        <span style="font-weight:700; font-size:14px; letter-spacing:.02em;">Item Stock Report &mdash; Print Document Preview</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-light font-weight-bold text-dark" onclick="printFromPreview()">
                            <i class="fas fa-print mr-1 text-primary"></i> Print
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light font-weight-bold" onclick="closePrintPreview()">
                            <i class="fas fa-times mr-1"></i> Close
                        </button>
                    </div>
                </div>
                <div class="report-preview-body">
                    <div class="report-preview-paper" id="previewPaperContent">
                        <!-- Populated dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('js')

<script>
$(document).ready(function() {
    var warehouseDataStore = {}; // ✅ Global storage for warehouse data
    var stockTable = null;

    function renderRows(rows, grandTotal) {
        // Destroy existing DataTable instance cleanly
        if ($.fn.DataTable.isDataTable('#stockTable')) {
            $('#stockTable').DataTable().destroy();
        }
        
        // Clear the table body
        $('#reportBody').empty();
        warehouseDataStore = {}; // Reset warehouse data

        if (!rows || rows.length === 0) {
            $('#negativeStockAlert').addClass('d-none');
            $('#footOpening').text('0.00');
            $('#footPurchased').text('0.00');
            $('#footPurchAmount').text('Rs. 0.00');
            $('#footDelivered').text('0.00');
            $('#footDeliveredAmount').text('Rs. 0.00');
            $('#footReserved').text('0.00');
            $('#footBalance').text('0.00');
            $('#grandStockValue').text('0.00').removeClass('text-danger');

            $('#kpiTotalBalance').text('0.00');
            $('#kpiTotalDelivered').text('0.00');
            $('#kpiTotalReserved').text('0.00');
            $('#kpiTotalStockValue').text('Rs. 0.00');

            stockTable = $('#stockTable').DataTable({
                paging: true,
                searching: true,
                info: true,
                ordering: true,
                language: {
                    emptyTable: "No stock data found for selected criteria in this branch."
                },
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });
            renderFormalPrintDoc([], 0);
            return;
        }

        let hasNegativeStock = false;
        let sumOpening = 0, sumPurchased = 0, sumPurchAmount = 0;
        let sumDelivered = 0, sumDeliveredAmount = 0, sumReserved = 0;
        let sumBalance = 0;

        rows.forEach(function(r, idx) {
            let ppp = parseFloat(r.piece_per_pack) || 1;

            // ============= STORE WAREHOUSE DATA =============
            let dataKey = 'product_' + r.id + (r.variant_id ? ('_' + r.variant_id) : '');
            warehouseDataStore[dataKey] = {
                itemCode: r.item_code,
                itemName: r.item_name,
                piece_per_pack: ppp,
                warehouses: r.warehouse_breakdown || []
            };

            // ============= ACCUMULATE TOTALS =============
            let opening     = parseFloat(r.initial_stock || 0);
            let purchased   = parseFloat(r.purchased || 0);
            let purchAmt    = parseFloat(r.purchase_amount || 0);
            let sold        = parseFloat(r.sold || 0);
            let saleAmt     = parseFloat(r.sale_amount || 0);
            let reserved    = parseFloat(r.reserved_qty || 0);
            let balance     = parseFloat(r.balance || 0);
            let price       = parseFloat(r.price || 0);
            let stockValue  = parseFloat(r.stock_value || 0);

            sumOpening += opening;
            sumPurchased += purchased;
            sumPurchAmount += purchAmt;
            sumDelivered += sold;
            sumDeliveredAmount += saleAmt;
            sumReserved += reserved;
            sumBalance += balance;

            // ============= BUILD WAREHOUSE BREAKDOWN HTML =============
            let warehouseHtml = '';
            if (r.warehouse_breakdown && r.warehouse_breakdown.length > 0) {
                warehouseHtml = `<button type="button" class="btn btn-sm btn-info warehouse-btn" data-product-id="${r.id}" data-variant-id="${r.variant_id || ''}">
                    View <span class="badge badge-light">${r.warehouse_breakdown.length}</span>
                </button>`;
            } else {
                warehouseHtml = '<span class="text-muted">No breakdown</span>';
            }

            // ============= PACKING DISPLAY LOGIC =============
            let packBadge = (ppp > 1) ? ` <span class="badge bg-light text-primary border" style="font-size:10px;"><i class="fas fa-boxes"></i> ${ppp} Pcs/Crtn</span>` : '';

            let formatQtyWithCarton = function(qtyVal) {
                if (ppp > 1 && qtyVal != 0) {
                    let isNeg = qtyVal < 0;
                    let absQty = Math.abs(qtyVal);
                    let ctns = Math.floor(absQty / ppp);
                    let rem = Math.round((absQty % ppp) * 100) / 100;
                    if (rem >= ppp) { ctns++; rem = 0; }

                    let parts = [];
                    if (ctns > 0) parts.push(`${ctns} Ctn`);
                    if (rem > 0 || parts.length === 0) {
                        let remStr = (rem % 1 === 0) ? rem : rem.toFixed(2);
                        parts.push(`${remStr} Pcs`);
                    }
                    let ctnStr = (isNeg ? '-' : '') + parts.join(' + ');
                    return `${qtyVal.toFixed(2)} <span class="badge bg-light text-dark border" style="font-size:9.5px; font-weight:600;">(${ctnStr})</span>`;
                }
                return qtyVal.toFixed(2);
            };

            // ============= NEGATIVE STOCK VISUAL LOGIC =============
            let isNegative  = balance < 0;
            if (isNegative) hasNegativeStock = true;

            let balanceHtml = isNegative
                ? `<strong class="text-danger">${formatQtyWithCarton(balance)} <span class="badge badge-danger" title="Negative Stock — sold more than available">⚠ Negative</span></strong>`
                : `<strong>${formatQtyWithCarton(balance)}</strong>`;

            let stockValueHtml = isNegative
                ? `<strong class="text-danger">Rs. ${stockValue.toFixed(2)}</strong>`
                : `<strong>Rs. ${stockValue.toFixed(2)}</strong>`;

            let rowStyle = isNegative ? 'background-color: #fff5f5;' : '';

            // ============= BUILD TABLE ROW =============
            let row = `
                <tr style="${rowStyle}">
                    <td>${idx + 1}${isNegative ? ' <span class="text-danger" title="Negative Stock">⚠</span>' : ''}</td>
                    <td><strong>${r.item_code}</strong></td>
                    <td>${r.item_name}${packBadge}</td>
                    <td class="text-end">${formatQtyWithCarton(opening)}</td>
                    <td class="text-end">${formatQtyWithCarton(purchased)}</td>
                    <td class="text-end">Rs. ${purchAmt.toFixed(2)}</td>
                    <td class="text-end fw-bold text-success" title="Physically Delivered Qty (دیے گئے)">${formatQtyWithCarton(sold)}</td>
                    <td class="text-end">Rs. ${saleAmt.toFixed(2)}</td>
                    <td class="text-end" title="Reserved Stock Pending Delivery (ریزرورڈ)"><span class="badge bg-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px;">${reserved.toFixed(2)} pending</span></td>
                    <td class="text-end">${balanceHtml}</td>
                    <td class="text-end fw-bold" style="color: #1e3a5f !important;" title="Wholesale / Unit Price">Rs. ${price.toFixed(2)}</td>
                    <td class="text-end">${stockValueHtml}</td>
                    <td class="text-center">${warehouseHtml}</td>
                </tr>
            `;
            
            $('#reportBody').append(row);
        });

        // Negative stock alert banner outside the table (avoids DataTables colspan crash)
        if (hasNegativeStock) {
            $('#negativeStockAlert').removeClass('d-none');
        } else {
            $('#negativeStockAlert').addClass('d-none');
        }

        // ============= UPDATE FOOTER TOTALS =============
        $('#footOpening').text(sumOpening.toFixed(2));
        $('#footPurchased').text(sumPurchased.toFixed(2));
        $('#footPurchAmount').text('Rs. ' + sumPurchAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#footDelivered').text(sumDelivered.toFixed(2));
        $('#footDeliveredAmount').text('Rs. ' + sumDeliveredAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#footReserved').text(sumReserved.toFixed(2));
        $('#footBalance').text(sumBalance.toFixed(2));

        let grandTotalVal = parseFloat(grandTotal);
        let grandTotalEl  = $('#grandStockValue');
        grandTotalEl.text('Rs. ' + grandTotalVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        if (hasNegativeStock) {
            grandTotalEl.addClass('text-danger');
        } else {
            grandTotalEl.removeClass('text-danger');
        }

        // ============= UPDATE KPI SUMMARY CARDS =============
        $('#kpiTotalBalance').text(sumBalance.toFixed(2));
        $('#kpiTotalDelivered').text(sumDelivered.toFixed(2));
        $('#kpiTotalReserved').text(sumReserved.toFixed(2));
        $('#kpiTotalStockValue').text('Rs. ' + grandTotalVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#kpiSummaryCards').slideDown();
        
        // Reinitialize DataTable with new data
        stockTable = $('#stockTable').DataTable({
            paging: true,
            searching: true,
            info: true,
            ordering: true,
            columnDefs: [
                { orderable: false, targets: -1 } // Disable sorting on action column
            ]
        });

        // ============= ATTACH CLICK HANDLERS TO WAREHOUSE BUTTONS =============
        $(document).on('click', '.warehouse-btn', function() {
            let productId = $(this).data('product-id');
            let variantId = $(this).data('variant-id');
            let dataKey = 'product_' + productId + (variantId ? ('_' + variantId) : '');
            
            if (warehouseDataStore[dataKey]) {
                let data = warehouseDataStore[dataKey];
                showWarehouseBreakdown(data.itemCode, data.itemName, data.warehouses, data.piece_per_pack || 1);
            }
        });

        // Synchronize formal print document
        renderFormalPrintDoc(rows, grandTotal);
    }

    // ============= SHOW WAREHOUSE BREAKDOWN MODAL =============
    function showWarehouseBreakdown(itemCode, itemName, warehouses, ppp = 1) {
        $('#modalProductName').text(`${itemCode} - ${itemName}`);
        $('#warehouseTableBody').html('');
        
        let totalQty = 0;
        
        if (!warehouses || warehouses.length === 0) {
            $('#warehouseTableBody').html('<tr><td colspan="3" class="text-center text-muted">No warehouse data available</td></tr>');
            $('#warehouseModal').modal('show');
            return;
        }
        
        warehouses.forEach(function(w) {
            let qty = parseFloat(w.qty || 0);
            totalQty += qty;
            let qtyClass = qty < 0 ? 'text-danger fw-bold' : '';
            let qtyLabel = qty < 0 ? ` <span class="badge badge-danger">⚠ Negative</span>` : '';
            let ctnInfo = '';
            if (ppp > 1 && qty !== 0) {
                let isNeg = qty < 0;
                let absQty = Math.abs(qty);
                let ctns = Math.floor(absQty / ppp);
                let rem = Math.round((absQty % ppp) * 100) / 100;
                if (rem >= ppp) { ctns++; rem = 0; }
                let parts = [];
                if (ctns > 0) parts.push(`${ctns} Ctn`);
                if (rem > 0 || parts.length === 0) {
                    let remStr = (rem % 1 === 0) ? rem : rem.toFixed(2);
                    parts.push(`${remStr} Pcs`);
                }
                let ctnStr = (isNeg ? '-' : '') + parts.join(' + ');
                ctnInfo = ` <span class="badge bg-light text-dark border" style="font-size:9.5px; font-weight:600;">(${ctnStr})</span>`;
            }

            let row = `
                <tr>
                    <td><strong>${w.warehouse_name || 'Unknown'}</strong></td>
                    <td>${w.location || '-'}</td>
                    <td class="text-end ${qtyClass}">${qty.toFixed(2)}${ctnInfo}${qtyLabel}</td>
                </tr>
            `;
            $('#warehouseTableBody').append(row);
        });
        
        // Add total row
        $('#warehouseTableBody').append(`
            <tr class="fw-bold bg-light">
                <td colspan="2" class="text-end">Total Quantity:</td>
                <td class="text-end"><strong>${totalQty.toFixed(2)}</strong></td>
            </tr>
        `);
        
        $('#warehouseModal').modal('show');
    }

    // ============= MANUAL CLOSE BUTTON HANDLERS =============
    $(document).on('click', '[data-dismiss="modal"]', function(){
        $('#warehouseModal').modal('hide');
    });

    $('#btnSearch').on('click', function() { fetchReport(); });
    $('#product_id').on('keypress', function(e){ if(e.key==='Enter'){ e.preventDefault(); fetchReport(); } });
    
    // ✅ ERP STANDARD: When super admin changes branch, reload products for that branch
    $('#branch_id').on('change', function() {
        var selectedBranchId = $(this).val();
        
        // Fetch products for the selected branch
        $.ajax({
            url: "{{ route('report.item_stock') }}",
            type: "GET",
            data: { branch_id: selectedBranchId },
            success: function(html) {
                // Extract product options from the new HTML
                var newProductOptions = $(html).find('#product_id').html();
                $('#product_id').html(newProductOptions);
                if ($.fn.select2 && $('#product_id').hasClass('select2-hidden-accessible')) {
                    $('#product_id').select2('destroy');
                }
                if ($.fn.select2) {
                    $('#product_id').select2();
                }
                
                // Auto-fetch report with new branch
                fetchReport();
            },
            error: function() {
                console.error('Error loading products for branch');
                // Still fetch report with current branch
                fetchReport();
            }
        });
    });

    function fetchReport() {
        var productId = $('#product_id').val();
        var branchId = $('#branch_id').val() || '{{ $selectedBranchId }}'; // Use default branch if not in dropdown
        
        $('#loader').show();
        $.ajax({
            url: "{{ route('report.item_stock.fetch') }}",
            type: "POST",
            data: { 
                _token: "{{ csrf_token() }}", 
                product_id: productId,
                branch_id: branchId
            },
            success: function(response) {
                $('#loader').hide();
                renderRows(response.data || [], response.grand_total || 0);
            },
            error: function(xhr, status, err) {
                $('#loader').hide();
                alert('Error fetching report. See console.');
                console.error(xhr.responseText || err);
            }
        });
    }

    // Initial load
    fetchReport();

    /* ---------- WhatsApp Share ---------- */
    window.shareWhatsApp = function() {
        Swal.fire({
            title: 'Preparing WhatsApp Share...',
            text: 'Generating PDF document to share.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        var element = document.getElementById('reportContent');
        var opt = {
          margin:       0.2,
          filename:     'Item_Stock_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
            var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Item Stock Report',
                    text: 'Please find the attached Item Stock Report.',
                    files: [file]
                }).then(() => {
                    Swal.close();
                }).catch((error) => {
                    console.log('Error sharing', error);
                    fallbackWaShare(pdfBlob, opt.filename);
                });
            } else {
                fallbackWaShare(pdfBlob, opt.filename);
            }
        });
    };

    function fallbackWaShare(pdfBlob, filename) {
        Swal.fire({
            icon: 'info',
            title: 'Share PDF via WhatsApp',
            text: 'The PDF will be downloaded now. WhatsApp will open allowing you to choose any chat. Please attach the downloaded PDF manually.',
            confirmButtonText: 'Download & Open WhatsApp'
        }).then(() => {
            var url = URL.createObjectURL(pdfBlob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            
            var msg = "*Item Stock Report*\nPlease find the attached PDF document.";
            var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
            window.open(waUrl, '_blank');
        });
    }

    /* ---------- Export Options & PDF ---------- */
    window.showExportOptions = function() {
        Swal.fire({
            title: 'Export Stock Report',
            text: 'Choose your preferred export format:',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-file-excel me-1"></i> Excel (CSV)',
            cancelButtonText: '<i class="fas fa-file-pdf me-1"></i> PDF',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                exportCSV();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                exportPDF();
            }
        });
    };

    window.exportPDF = function() {
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Please wait while your PDF is being prepared.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        var element = document.getElementById('reportContent');
        var opt = {
          margin:       0.2,
          filename:     'Item_Stock_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).save().then(function() {
            Swal.close();
        });
    };

    window.exportCSV = function () {
        var rows = [['Item Code','Item Name','Opening Stock','Purchased Qty','Purchased Amount','Delivered Qty (دیے)','Delivered Value','Reserved Qty (ریزرورڈ)','Balance','Price (قیمت)','Stock Value']];
        
        $('#reportBody tr').each(function () {
            var cells = [];
            $(this).find('td').each(function (idx) {
                if(idx === 0 || idx === 12) return; // Skip # and Action columns
                var text = $(this).text().replace(/Rs\.\s?/g, '').trim().replace(/"/g, '""');
                cells.push('"' + text + '"');
            });
            if (cells.length) rows.push(cells);
        });
        
        // Add grand total
        rows.push(['','','','','','','','','','Grand Total','"' + $('#grandStockValue').text().replace(/Rs\.\s?/g, '') + '"']);

        var csv  = rows.map(function(r){return r.join(',');}).join('\n');
        var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8;'});
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href   = url;
        a.download = 'item_stock_report_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
    };
});

function renderFormalPrintDoc(rows, grandTotal) {
    // Branch title
    let branchName = 'ALL BRANCHES';
    const branchSelect = document.getElementById('branch_id');
    if (branchSelect && branchSelect.tagName === 'SELECT' && branchSelect.value !== 'all' && branchSelect.selectedIndex >= 0) {
        branchName = branchSelect.options[branchSelect.selectedIndex].text.trim();
    } else {
        const branchText = document.querySelector('#stockFilterForm .bg-light');
        if (branchText && branchText.textContent.trim()) {
            branchName = branchText.textContent.trim();
        }
    }
    const branchEl = document.getElementById('p_branch_title');
    if (branchEl) branchEl.textContent = branchName.toUpperCase();

    // Product subtitle
    const prodSelect = document.getElementById('product_id');
    let prodName = '';
    if (prodSelect && prodSelect.value !== 'all' && prodSelect.selectedIndex >= 0) {
        prodName = 'Product: ' + prodSelect.options[prodSelect.selectedIndex].text.trim();
    }
    const periodEl = document.getElementById('p_doc_period');
    if (periodEl) {
        periodEl.textContent = 'Track opening, purchased, sold, reserved & balance' + (prodName ? ' — ' + prodName : '');
    }

    // Print timestamp
    const now = new Date();
    const printTimeEl = document.getElementById('p_print_time');
    if (printTimeEl) {
        printTimeEl.textContent = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    const tbody = document.getElementById('p_stock_tbody');
    if (!tbody) return;

    if (!rows || rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="12" style="text-align:center; padding:18px; color:#64748b;">No stock items found for selected filter criteria.</td></tr>';
        $('#p_foot_opening').text('0.00');
        $('#p_foot_purchased').text('0.00');
        $('#p_foot_purch_value').text('0.00');
        $('#p_foot_delivered').text('0.00');
        $('#p_foot_delivered_value').text('0.00');
        $('#p_foot_reserved').text('0.00');
        $('#p_foot_balance').text('0.00');
        $('#p_foot_stock_value').text('0.00');
        return;
    }

    let sumOpening = 0, sumPurchased = 0, sumPurchAmount = 0;
    let sumDelivered = 0, sumDeliveredAmount = 0, sumReserved = 0;
    let sumBalance = 0;
    let html = '';

    rows.forEach((r, idx) => {
        let opening     = parseFloat(r.initial_stock || 0);
        let purchased   = parseFloat(r.purchased || 0);
        let purchAmt    = parseFloat(r.purchase_amount || 0);
        let sold        = parseFloat(r.sold || 0);
        let saleAmt     = parseFloat(r.sale_amount || 0);
        let reserved    = parseFloat(r.reserved_qty || 0);
        let balance     = parseFloat(r.balance || 0);
        let price       = parseFloat(r.price || 0);
        let stockValue  = parseFloat(r.stock_value || 0);

        sumOpening += opening;
        sumPurchased += purchased;
        sumPurchAmount += purchAmt;
        sumDelivered += sold;
        sumDeliveredAmount += saleAmt;
        sumReserved += reserved;
        sumBalance += balance;

        let isNeg = balance < 0;
        let balColor = isNeg ? '#dc2626' : '#000';
        let valColor = stockValue < 0 ? '#dc2626' : (stockValue > 0 ? '#047857' : '#000');

        html += `<tr>
            <td style="text-align:center; color:#64748b;">${idx + 1}</td>
            <td style="font-weight:700; font-family:monospace; color:#1e3a5f;">${r.item_code || ''}</td>
            <td style="font-weight:700; color:#0f172a;">${r.item_name || ''}</td>
            <td class="num-cell">${opening.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="num-cell">${purchased.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="num-cell">${purchAmt.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="num-cell" style="font-weight:700; color:#047857;">${sold.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="num-cell">${saleAmt.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="num-cell" style="color:#b45309;">${reserved.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="num-cell" style="font-weight:800; color:${balColor};">${balance.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="num-cell" style="color:#1e3a5f; font-weight:600;">${price.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="num-cell" style="font-weight:800; color:${valColor};">${stockValue.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
        </tr>`;
    });

    tbody.innerHTML = html;

    // Footer Totals
    let grandTotalVal = parseFloat(grandTotal || 0);
    $('#p_foot_opening').text(sumOpening.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#p_foot_purchased').text(sumPurchased.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#p_foot_purch_value').text(sumPurchAmount.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#p_foot_delivered').text(sumDelivered.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#p_foot_delivered_value').text(sumDeliveredAmount.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#p_foot_reserved').text(sumReserved.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#p_foot_balance').text(sumBalance.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#p_foot_stock_value').text(grandTotalVal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

    // Live sync preview if modal open
    const previewModal = document.getElementById('reportPreviewModal');
    if (previewModal && previewModal.style.display === 'flex') {
        const formalDoc = document.getElementById('formalPrintDoc');
        const paper = document.getElementById('previewPaperContent');
        if (formalDoc && paper) {
            paper.innerHTML = formalDoc.innerHTML;
        }
    }
}

function openPrintPreview() {
    const formalDoc = document.getElementById('formalPrintDoc');
    if (!formalDoc) return;

    const reportHtml = formalDoc.innerHTML;
    const branchTitle = document.getElementById('p_branch_title')?.textContent || 'Company';
    const docTitle = 'Item Stock Report';
    const fullTitle = `${docTitle} - ${branchTitle}`;

    const win = window.open('', '_blank');
    if (win) {
        win.document.write(`<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${fullTitle}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px 20px 60px 20px;
            background: #f8fafc;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .action-toolbar {
            max-width: 1100px;
            margin: 0 auto 14px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .report-badge {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e3a5f;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 15px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #1e293b;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-action:hover { background: #f1f5f9; }
        .btn-primary-action { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
        .btn-primary-action:hover { background: #0f1f38; }

        .normal-page-card {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.06);
            border: 1px solid #cbd5e1;
            padding: 32px 34px;
        }

        .print-doc-header { margin-bottom: 10px; }
        .print-company-title { font-size: 15pt; font-weight: 800; color: #000; margin-bottom: 2px; text-transform: uppercase; letter-spacing: .02em; }
        .print-doc-title { font-size: 21pt; font-weight: 900; color: #000; margin-bottom: 2px; }
        .print-doc-period { font-size: 11pt; font-weight: 600; color: #1e293b; }
        .print-top-divider { border-bottom: 2px solid #000; margin-top: 6px; margin-bottom: 12px; }

        .print-tb-table { width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 14px; color: #000; table-layout: fixed; }
        .print-tb-table thead { display: table-header-group; }
        .print-tb-table th { border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 8px 4px; background: #f8fafc; font-weight: 800; color: #000; font-size: 9pt; text-transform: uppercase; vertical-align: middle; line-height: 1.25; }
        .print-tb-table td { border-bottom: 1px solid #cbd5e1; padding: 7px 4px; vertical-align: middle; color: #000; font-size: 10pt; line-height: 1.3; overflow: hidden; word-wrap: break-word; }
        .print-tb-table .num-cell { text-align: right; font-variant-numeric: tabular-nums; font-feature-settings: "tnum"; white-space: nowrap; font-size: 10pt; letter-spacing: -0.01em; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .print-tb-table tr.p-tfoot td { border-top: 1.5px solid #000; border-bottom: 3px double #000; font-weight: 800; padding: 9px 4px; background: #fff; font-size: 10.5pt; color: #000; }

        .print-doc-footer { margin-top: 24px; font-size: 9.5pt; color: #000; }
        .print-footer-info { display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 6px; margin-bottom: 38px; font-weight: 600; color: #000; font-size: 9pt; }
        .print-sign-row { display: flex; justify-content: space-between; padding: 0 15px; }
        .print-sign-box { border-top: 1.5px dashed #000; width: 140px; text-align: center; padding-top: 6px; font-size: 9.5pt; font-weight: 700; color: #000; }

        @media print {
            @page {
                size: A4 landscape;
                margin: 6mm 6mm 6mm 6mm !important;
            }
            body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
            .action-toolbar, .no-print { display: none !important; }
            .normal-page-card { border: none !important; box-shadow: none !important; padding: 0 !important; max-width: 100% !important; width: 100% !important; margin: 0 !important; }
            .print-tb-table { width: 100% !important; max-width: 100% !important; table-layout: fixed !important; }
            .print-tb-table th { font-size: 8.5pt !important; padding: 6px 3px !important; }
            .print-tb-table td { font-size: 9pt !important; padding: 5px 3px !important; }
            .print-tb-table .num-cell { font-size: 9pt !important; }
            .print-tb-table tr.p-tfoot td { font-size: 9.5pt !important; padding: 7px 3px !important; }
            .print-tb-table th, .print-tb-table td { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>
    <div class="action-toolbar no-print">
        <div class="report-badge">
            <i class="fas fa-boxes"></i>
            <span>${fullTitle}</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn-action btn-primary-action" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn-action" onclick="window.close()">
                <i class="fas fa-times"></i> Close Tab
            </button>
        </div>
    </div>
    <div class="normal-page-card">
        ${reportHtml}
    </div>
</body>
</html>`);
        win.document.close();
        win.focus();
        return;
    }

    const paper = document.getElementById('previewPaperContent');
    const modal = document.getElementById('reportPreviewModal');
    if (paper && modal) {
        paper.innerHTML = reportHtml;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closePrintPreview() {
    const modal = document.getElementById('reportPreviewModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function printFromPreview() {
    window.print();
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePrintPreview();
});
</script>
@endsection

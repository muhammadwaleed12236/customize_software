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

    .rpt-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    @media (max-width: 992px) {
        .rpt-kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .rpt-kpi-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 13px 16px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .rpt-kpi-card.highlight {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #a7f3d0;
    }

    .rpt-kpi-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .rpt-kpi-val {
        font-size: 18px;
        font-weight: 800;
        color: var(--coa-navy);
        font-family: monospace;
    }

    .rpt-kpi-val.emerald { color: #047857; }
    .rpt-kpi-val.crimson { color: #dc2626; }
    .rpt-kpi-val.amber { color: #d97706; }

    .rpt-kpi-icon {
        width: 38px;
        height: 38px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .kpi-icon-blue { background: #e0f2fe; color: #0284c7; }
    .kpi-icon-emerald { background: #d1fae5; color: #059669; }
    .kpi-icon-red { background: #fee2e2; color: #dc2626; }
    .kpi-icon-amber { background: #fef3c7; color: #d97706; }

    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.03em;
        margin-bottom: 4px;
        display: block;
    }

    #purchaseTable th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 8px;
        border: 1px solid #1e3a5f;
    }

    /* Print Document Styles */
    #formalPrintDoc {
        display: none;
    }

    @media print {
        @page {
            size: auto;
            margin: 0mm !important;
        }
        html, body, .container-scroller, .main-content, .rpt-wrapper, .container-fluid {
            background: #fff !important;
            color: #000 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 100% !important;
            border: none !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Hide all interactive/screen UI elements */
        .rt_nav_header, .nav-bottom, .navbar, .footer, .no-print,
        .rpt-header-bar, #filterCard, .rpt-kpi-grid, #summaryCards,
        #placeholder, #loader, .btn, #tableWrapper, .card,
        .main-footer, .header, .sidebar, .sidebar-menu, .header-area,
        #reportPreviewModal, #pdfHeader, #reportContent, .modal-backdrop {
            display: none !important;
        }

        #formalPrintDoc {
            display: block !important;
            box-sizing: border-box !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 100% !important;
            padding: 8mm 5mm 8mm 5mm !important;
            margin: 0 !important;
            background: #fff !important;
            color: #000 !important;
        }

        .print-doc-header {
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        .print-company-title {
            font-size: 14pt;
            font-weight: 800;
            color: #000;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .print-doc-title {
            font-size: 19pt;
            font-weight: 900;
            color: #000;
            margin-bottom: 2px;
        }
        .print-doc-period {
            font-size: 10.5pt;
            font-weight: 600;
            color: #334155;
        }
        .print-top-divider {
            border-bottom: 2px solid #000;
            margin-top: 6px;
            margin-bottom: 10px;
        }

        .print-tb-table {
            width: 100% !important;
            max-width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
            font-size: 6.8pt !important;
            margin-bottom: 12px !important;
            color: #000 !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
        }
        .print-tb-table thead {
            display: table-header-group !important;
        }
        .print-tb-table tfoot {
            display: table-row-group !important;
        }
        .print-tb-table th {
            border-top: 1.5px solid #000 !important;
            border-bottom: 1.5px solid #000 !important;
            padding: 3px 1.5px !important;
            background: #f8fafc !important;
            font-weight: 800 !important;
            color: #000 !important;
            font-size: 6.2pt !important;
            text-transform: uppercase !important;
            vertical-align: middle !important;
            line-height: 1.1 !important;
            overflow-wrap: break-word !important;
        }
        .print-tb-table td {
            border-bottom: 1px solid #cbd5e1 !important;
            padding: 2.5px 1.5px !important;
            vertical-align: middle !important;
            color: #000 !important;
            font-size: 6.8pt !important;
            line-height: 1.15 !important;
            overflow-wrap: break-word !important;
        }
        .print-tb-table tr {
            page-break-inside: avoid !important;
        }
        .print-tb-table .num-cell {
            text-align: right !important;
            font-variant-numeric: tabular-nums !important;
            font-feature-settings: "tnum" !important;
            white-space: nowrap !important;
            font-size: 6.5pt !important;
            padding-left: 1px !important;
            padding-right: 2px !important;
        }
        .print-tb-table tr.p-tfoot td {
            border-top: 1.5px solid #000 !important;
            border-bottom: 3px double #000 !important;
            font-weight: 800 !important;
            padding: 5px 2px !important;
            background: #fff !important;
            font-size: 7.2pt !important;
            color: #000 !important;
        }

        .print-doc-footer {
            margin-top: 24px;
            font-size: 8.5pt;
            color: #000;
            page-break-inside: avoid !important;
        }
        .print-footer-info {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #000;
            padding-top: 6px;
            margin-bottom: 35px;
            font-weight: 600;
            color: #000;
            font-size: 8pt;
        }
        .print-sign-row {
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }
        .print-sign-box {
            border-top: 1.5px dashed #000;
            width: 150px;
            text-align: center;
            padding-top: 5px;
            font-size: 8.5pt;
            font-weight: 700;
            color: #000;
        }
    }

    /* ── Full-Page Clean Report Preview Styles ──── */
    .report-preview-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: #f1f5f9;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .report-preview-topbar {
        height: 52px;
        background: #0f1f38;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        flex-shrink: 0;
    }
    .report-preview-body {
        flex: 1;
        overflow-y: auto;
        overflow-x: auto;
        padding: 24px 20px 50px 20px;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }
    .report-preview-paper {
        background: #ffffff;
        width: 100%;
        max-width: 1350px;
        min-height: 80vh;
        padding: 35px 45px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #000;
        box-sizing: border-box;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .report-preview-paper .print-doc-header { margin-bottom: 12px; }
    .report-preview-paper .print-company-title { font-size: 15pt; font-weight: 800; color: #000; margin-bottom: 2px; text-transform: uppercase; letter-spacing: .02em; }
    .report-preview-paper .print-doc-title { font-size: 20pt; font-weight: 900; color: #000; margin-bottom: 2px; }
    .report-preview-paper .print-doc-period { font-size: 11pt; font-weight: 600; color: #334155; }
    .report-preview-paper .print-top-divider { border-bottom: 2px solid #000; margin-top: 8px; margin-bottom: 14px; }
    .report-preview-paper .print-tb-table { width: 100%; table-layout: auto; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 16px; color: #000; }
    .report-preview-paper .print-tb-table th { border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 7px 4px; background: #f8fafc; font-weight: 800; color: #000; font-size: 8pt; text-transform: uppercase; vertical-align: middle; line-height: 1.25; }
    .report-preview-paper .print-tb-table td { border-bottom: 1px solid #cbd5e1; padding: 5px 4px; vertical-align: middle; color: #000; font-size: 8.5pt; line-height: 1.3; }
    .report-preview-paper .print-tb-table .num-cell { text-align: right; font-variant-numeric: tabular-nums; font-feature-settings: "tnum"; white-space: nowrap; font-size: 8.5pt; }
    .report-preview-paper .print-tb-table tr.p-tfoot td { border-top: 2px solid #000; border-bottom: 3.5px double #000; font-weight: 800; padding: 8px 4px; background: #fff; font-size: 9pt; color: #000; }
    .report-preview-paper .print-doc-footer { margin-top: 30px; font-size: 9pt; color: #000; }
    .report-preview-paper .print-footer-info { display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 6px; margin-bottom: 45px; font-weight: 600; color: #000; font-size: 8.5pt; }
    .report-preview-paper .print-sign-row { display: flex; justify-content: space-between; padding: 0 25px; }
    .report-preview-paper .print-sign-box { border-top: 1.5px dashed #000; width: 160px; text-align: center; padding-top: 6px; font-size: 9pt; font-weight: 700; color: #000; }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">
            
            {{-- 1. Corporate Header Bar --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Purchase Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-truck-loading mr-1" style="color: var(--coa-gold);"></i> Detailed Purchase History & Analytical View &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" onclick="openPrintPreview()" class="btn btn-sm btn-outline-light font-weight-bold no-print" title="Clean Full-Page Preview">
                        <i class="fas fa-eye mr-1"></i> Preview
                    </button>
                    <button type="button" id="waShareBtn" onclick="shareWhatsApp()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(37, 211, 102, 0.2); border-color: #25D366; color: #25D366;">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button type="button" onclick="showExportOptions()" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-download mr-1 text-primary"></i> Export
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-light font-weight-bold">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                </div>
            </div>

            {{-- 2. Filter Card --}}
            <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form id="purchaseFilterForm" class="row g-2 align-items-end mb-0">
                        <div class="col-md-3" id="branch_container" @if(!$user->hasRole('super admin')) style="display:none;" @endif>
                            <label class="f-label">Select Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control form-control-sm select2">
                                @if($user->hasRole('super admin'))
                                    <option value="all">-- All Branches --</option>
                                @endif
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $user->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-{{ $user->hasRole('super admin') ? '3' : '4' }}">
                            <label class="f-label">Select Vendor</label>
                            <select name="vendor_id" id="vendor_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Vendors --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. Summary Cards --}}
            <div id="summaryCards" class="rpt-kpi-grid" style="display:none;">
                <div class="rpt-kpi-card highlight">
                    <div>
                        <div class="rpt-kpi-label" style="color: #047857;">Net Purchases</div>
                        <div id="stat_net" class="rpt-kpi-val emerald">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Paid</div>
                        <div id="stat_paid" class="rpt-kpi-val">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-blue">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Balance</div>
                        <div id="stat_due" class="rpt-kpi-val crimson">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-red">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Discount</div>
                        <div id="stat_disc" class="rpt-kpi-val amber">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-amber">
                        <i class="fas fa-tag"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Report Table --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="reportContent">
                <div class="card-body p-3">
                    
                    {{-- PDF HEADER (HIDDEN ON SCREEN) --}}
                    <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #0f1f38; padding-bottom:10px;">
                        <h2 style="margin:0; color:#0f1f38; text-transform:uppercase; letter-spacing:1px;">Purchase Report</h2>
                        <p style="margin:5px 0; font-size:14px; color:#333;">
                            <strong>Period:</strong> <span id="pdfPeriod"></span> | 
                            <strong>Vendor:</strong> <span id="pdfVendorName">All Vendors</span>
                        </p>
                        <p style="margin:0; font-size:12px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:40px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Aggregating purchase data...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="purchaseTable" class="table table-bordered mb-0" style="font-size:12px; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 35px;">#</th>
                                    <th style="width: 90px; white-space: nowrap;">Date</th>
                                    <th style="width: 105px; white-space: nowrap;">Invoice</th>
                                    <th style="width: 95px; white-space: nowrap;">M. Bill#</th>
                                    <th style="width: 95px; white-space: nowrap;">Vendor Inv#</th>
                                    <th style="min-width: 130px; width: 13%;">Vendor</th>
                                    <th style="min-width: 260px; width: 25%;">Item / Product Details</th>
                                    <th class="text-end" style="width: 75px; white-space: nowrap;">Qty</th>
                                    <th class="text-end" style="width: 85px; white-space: nowrap;">Price</th>
                                    <th class="text-end" style="width: 80px; white-space: nowrap;">Item Disc</th>
                                    <th class="text-end" style="width: 95px; white-space: nowrap;">Net Item</th>
                                    <th class="text-end" style="width: 80px; white-space: nowrap;">Bill Disc</th>
                                    <th class="text-end" style="width: 105px; white-space: nowrap;">Total Net</th>
                                    <th class="text-end" style="width: 85px; white-space: nowrap;">Paid</th>
                                    <th class="text-end" style="width: 95px; white-space: nowrap;">Due</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>{{-- /#reportContent --}}

            {{-- ── FORMAL A4 PRINT DOCUMENT (Shown ONLY when printing or in clean preview) ──── --}}
            <div id="formalPrintDoc" class="formal-print-sheet">
                <div class="print-doc-header">
                    <div class="print-company-title" id="p_branch_title">ALL BRANCHES</div>
                    <div class="print-doc-title">Purchase Report</div>
                    <div class="print-doc-period" id="p_period_text">For The Period: 01 Nov 2026 to 21 Nov 2026</div>
                    <div class="print-top-divider"></div>
                </div>

                <table class="print-tb-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 2.5%;">#</th>
                            <th style="width: 7%; white-space: nowrap;">Date</th>
                            <th style="width: 7.5%; white-space: nowrap;">Invoice</th>
                            <th style="width: 4.5%; white-space: nowrap;">M.Bill#</th>
                            <th style="width: 4.5%; white-space: nowrap;">V.Inv#</th>
                            <th style="width: 8%;">Vendor</th>
                            <th style="width: 19%;">Item / Product Details</th>
                            <th class="text-end" style="width: 5.5%; white-space: nowrap;">Qty</th>
                            <th class="text-end" style="width: 6.5%; white-space: nowrap;">Price</th>
                            <th class="text-end" style="width: 5%; white-space: nowrap;">Disc</th>
                            <th class="text-end" style="width: 7%; white-space: nowrap;">Net Item</th>
                            <th class="text-end" style="width: 5%; white-space: nowrap;">B.Disc</th>
                            <th class="text-end" style="width: 7.5%; white-space: nowrap;">Total Net</th>
                            <th class="text-end" style="width: 5%; white-space: nowrap;">Paid</th>
                            <th class="text-end" style="width: 6%; white-space: nowrap;">Due</th>
                        </tr>
                    </thead>
                    <tbody id="p_purchase_body">
                        <!-- Populated dynamically -->
                    </tbody>
                    <tfoot>
                        <tr class="p-tfoot">
                            <td colspan="12" class="text-end" style="font-size:7.5pt; font-weight:800; letter-spacing:.02em; text-transform:uppercase;">GRAND TOTAL (UNIQUE INVOICES):</td>
                            <td class="text-end num-cell" style="font-weight:800;" id="p_foot_net">0.00</td>
                            <td class="text-end num-cell" style="font-weight:800;" id="p_foot_paid">0.00</td>
                            <td class="text-end num-cell" style="font-weight:800;" id="p_foot_due">0.00</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="print-doc-footer">
                    <div class="print-footer-info">
                        <span>Printed on: <span id="p_print_time"></span></span>
                        <span>Detailed Purchase History & Summary</span>
                        <span>ERP System &mdash; Purchase Report</span>
                    </div>
                    <div class="print-sign-row">
                        <div class="print-sign-box">Prepared By</div>
                        <div class="print-sign-box">Checked By</div>
                        <div class="print-sign-box">Approved By</div>
                    </div>
                </div>
            </div>{{-- /formalPrintDoc --}}

            {{-- ── Full-Page Clean Print Preview Modal ──── --}}
            <div id="reportPreviewModal" class="report-preview-overlay" style="display:none;" onclick="if(event.target===this) closePrintPreview()">
                <div class="report-preview-topbar">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-light text-dark font-weight-bold" style="font-size:11px; padding:4px 8px;">
                            <i class="fas fa-eye mr-1 text-primary"></i> PREVIEW
                        </span>
                        <span style="font-weight:700; font-size:14px; letter-spacing:.02em;">Purchase Report &mdash; Print Document Preview</span>
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
                        <!-- Populated dynamically by openPrintPreview() -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
    #purchaseTable { border: 1px solid #cbd5e1; }
    #purchaseTable th { 
        vertical-align: middle; 
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase; 
        letter-spacing: 0.04em;
        padding: 10px 8px;
        border: 1px solid #1e3a5f;
    }
    #purchaseTable td { 
        vertical-align: middle; 
        border: 1px solid #e2e8f0; 
        padding: 8px 8px; 
        font-weight: 500;
        color: #1e293b;
    }
    #purchaseTable tbody tr:nth-child(even) { background-color: #f8fafc; }
    #purchaseTable tbody tr:hover { background-color: #f1f5f9 !important; transition: 0.15s; }
    
    .invoice-no { color: #1e3a5f; font-weight: 700; font-family: monospace; font-size: 12px; }
    .item-name { font-weight: 700; color: #0f172a; font-size: 12.5px; line-height: 1.35; }
    
    @media print {
        #purchaseFilterForm, #btnSearch, .btn { display: none !important; }
        .main-content { padding: 0 !important; margin-top:0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        #reportContent { overflow: visible !important; }
    }
</style>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        
        function n(v) { return parseFloat(v) || 0; }
        function fmt(v) { return n(v).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

        function renderRows(rows) {
            let tableContent = '';
            let pPurchaseContent = '';
            let grandDiscount = 0;
            let grandNet = 0;
            let grandPaid = 0;
            let grandDue = 0;

            let processedInvoices = new Set(); // To avoid double-counting invoice-level totals

            if (rows.length === 0) {
                $('#reportBody').html('<tr><td colspan="15" class="text-center py-4 text-muted">No data found</td></tr>');
                $('#p_purchase_body').html('<tr><td colspan="15" class="text-center py-4" style="color:#64748b; font-size:9pt;">No purchase records found for this criteria</td></tr>');
                $('#summaryCards').hide();
                return;
            }

            rows.forEach(function(r, idx) {
                let colorBadges = '';
                let pColorText = '';
                if (r.colors) {
                    let colorList = r.colors.split(', ');
                    colorList.forEach(color => {
                        if (color && color.trim() !== '') {
                            colorBadges += `<span class="badge" style="background:#e2e8f0; color:#334155; font-size:9.5px; font-weight:600; padding:2px 5px; border-radius:3px;">${color.toUpperCase()}</span>`;
                        }
                    });
                    pColorText = ` &bull; <span style="color:#64748b;">${r.colors}</span>`;
                }

                tableContent += `<tr>
                    <td class="text-center text-muted" style="font-size:11px;">${idx + 1}</td>
                    <td style="white-space:nowrap; font-size:11.5px;">${r.purchase_date}</td>
                    <td><span class="invoice-no">${r.invoice_no}</span></td>
                    <td style="white-space:nowrap;"><span class="font-monospace text-secondary" style="font-size:11.5px; font-weight:600;">${r.manual_invoice || '-'}</span></td>
                    <td style="white-space:nowrap;"><span class="font-monospace text-secondary" style="font-size:11.5px; font-weight:600;">${r.vendor_invoice_no || '-'}</span></td>
                    <td><strong class="text-dark" style="font-size:12px;">${r.vendor_name}</strong></td>
                    <td>
                        <div class="item-name mb-1">${r.item_name}</div>
                        <div class="d-flex align-items-center flex-wrap gap-1">
                            ${colorBadges}
                            <span class="badge badge-light border text-muted" style="font-size:9.5px; font-family:monospace; padding:1px 4px;">${r.item_code}</span>
                        </div>
                    </td>
                    <td class="text-end font-monospace" style="font-weight:700; font-size:12px; white-space:nowrap;">${n(r.qty).toFixed(2)} <small class="text-muted font-weight-normal">${r.unit || ''}</small></td>
                    <td class="text-end font-monospace" style="font-size:12px;">${fmt(r.price)}</td>
                    <td class="text-end font-monospace ${n(r.item_discount) > 0 ? 'text-danger font-weight-bold' : 'text-muted'}" style="font-size:12px;">${fmt(r.item_discount)}</td>
                    <td class="text-end font-monospace font-weight-bold" style="background:rgba(30, 58, 95, 0.04); color:#1e3a5f; font-size:12px;">${fmt(r.line_total)}</td>
                    <td class="text-end font-monospace ${n(r.discount) > 0 ? 'text-danger font-weight-bold' : 'text-muted'}" style="font-size:12px;">${fmt(r.discount)}</td>
                    <td class="text-end font-monospace font-weight-bold" style="background:rgba(13, 159, 110, 0.08); color:#047857; font-size:12.5px;">${fmt(r.net_amount)}</td>
                    <td class="text-end font-monospace text-dark" style="font-size:12px;">${fmt(r.paid_amount)}</td>
                    <td class="text-end font-monospace font-weight-bold ${n(r.due_amount) > 0 ? 'text-danger' : 'text-success'}" style="font-size:12.5px;">${fmt(r.due_amount)}</td>
                </tr>`;

                pPurchaseContent += `<tr>
                    <td class="text-center" style="width:28px;">${idx + 1}</td>
                    <td style="white-space:nowrap;">${r.purchase_date}</td>
                    <td style="white-space:nowrap; font-weight:700; color:#1e3a5f;">${r.invoice_no}</td>
                    <td style="white-space:nowrap; color:#64748b;">${r.manual_invoice || '-'}</td>
                    <td style="white-space:nowrap; color:#64748b;">${r.vendor_invoice_no || '-'}</td>
                    <td style="font-weight:700; color:#0f172a;">${r.vendor_name}</td>
                    <td>
                        <div style="font-weight:700; color:#0f172a;">${r.item_name}</div>
                        <div style="color:#64748b; font-size:0.9em;">
                            ${r.item_code ? `<span style="font-family:monospace;">${r.item_code}</span>` : ''}
                            ${pColorText}
                        </div>
                    </td>
                    <td class="num-cell" style="font-weight:700;">${n(r.qty).toFixed(2)} <small style="color:#64748b;">${r.unit || ''}</small></td>
                    <td class="num-cell">${fmt(r.price)}</td>
                    <td class="num-cell" style="${n(r.item_discount) > 0 ? 'color:#dc2626; font-weight:700;' : ''}">${fmt(r.item_discount)}</td>
                    <td class="num-cell" style="font-weight:700;">${fmt(r.line_total)}</td>
                    <td class="num-cell" style="${n(r.discount) > 0 ? 'color:#dc2626; font-weight:700;' : ''}">${fmt(r.discount)}</td>
                    <td class="num-cell" style="font-weight:800; color:#047857;">${fmt(r.net_amount)}</td>
                    <td class="num-cell">${fmt(r.paid_amount)}</td>
                    <td class="num-cell" style="font-weight:700; ${n(r.due_amount) > 0 ? 'color:#dc2626;' : 'color:#047857;'}">${fmt(r.due_amount)}</td>
                </tr>`;

                // Add item-level discount to grand total
                grandDiscount += n(r.item_discount);

                // Invoice-level totals (count only once per unique purchase)
                let invKey = (r.purchase_id !== undefined && r.purchase_id !== null)
                    ? r.purchase_id
                    : (r.branch_id ? r.branch_id + '_' + r.invoice_no : r.invoice_no);

                if (!processedInvoices.has(invKey)) {
                    grandNet += n(r.net_amount);
                    grandPaid += n(r.paid_amount);
                    grandDue += n(r.due_amount);
                    grandDiscount += n(r.discount); // Add invoice-level discount
                    processedInvoices.add(invKey);
                }
            });

            // Summary Stats
            $('#stat_net').text('Rs. ' + fmt(grandNet));
            $('#stat_paid').text('Rs. ' + fmt(grandPaid));
            $('#stat_due').text('Rs. ' + fmt(grandDue));
            $('#stat_disc').text('Rs. ' + fmt(grandDiscount));
            $('#summaryCards').fadeIn();

            // Grand total row
            tableContent += `<tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                <td colspan="12" class="text-end font-weight-bold" style="font-family: sans-serif;">Total Summary (Unique Invoices):</td>
                <td class="text-end text-success font-weight-bold" style="background:rgba(13, 159, 110, 0.12);">${fmt(grandNet)}</td>
                <td class="text-end text-dark font-weight-bold">${fmt(grandPaid)}</td>
                <td class="text-end text-danger font-weight-bold">${fmt(grandDue)}</td>
            </tr>`;

            $('#reportBody').html(tableContent);

            // Populate Clean Formal Print/Preview Table
            $('#p_purchase_body').html(pPurchaseContent);
            $('#p_foot_net').text(fmt(grandNet));
            $('#p_foot_paid').text(fmt(grandPaid));
            $('#p_foot_due').text(fmt(grandDue));

            // Sync Header in Formal Print Doc
            let branchText = 'All Branches';
            if ($('#branch_id').length && $('#branch_id').val() && $('#branch_id').val() !== 'all' && $('#branch_id').val() !== '') {
                branchText = $('#branch_id option:selected').text();
            }
            const pBranchElem = document.getElementById('p_branch_title');
            if (pBranchElem) pBranchElem.textContent = branchText.toUpperCase();

            let sDate = $('#start_date').val();
            let eDate = $('#end_date').val();
            let vendText = $('#vendor_id option:selected').text();
            let pStr = '';
            if (sDate && eDate) {
                pStr = `For The Period: ${sDate} to ${eDate}`;
            } else {
                pStr = `Date: All Dates`;
            }
            if (vendText && vendText !== '-- All Vendors --') {
                pStr += ` | Vendor: ${vendText}`;
            }
            const pPeriodElem = document.getElementById('p_period_text');
            if (pPeriodElem) pPeriodElem.textContent = pStr;

            const pTimeElem = document.getElementById('p_print_time');
            if (pTimeElem) {
                pTimeElem.textContent = new Date().toLocaleString('en-GB', {
                    day: '2-digit', month: 'short', year: 'numeric',
                    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
                });
            }

            // Live sync if modal is currently open
            const previewModal = document.getElementById('reportPreviewModal');
            if (previewModal && previewModal.style.display === 'flex') {
                const formalDoc = document.getElementById('formalPrintDoc');
                const paper = document.getElementById('previewPaperContent');
                if (formalDoc && paper) paper.innerHTML = formalDoc.innerHTML;
            }
        }

        $('#btnSearch').on('click', function() {
            fetchReport();
        });

        // ✅ NEW: Branch change listener to fetch vendors dynamically
        $('#branch_id').on('change', function() {
            let branchId = $(this).val();
            if (branchId === 'all') {
                // If all branches, maybe show all vendors or clear
                // For now, let's clear and show "Select Branch"
                $('#vendor_id').html('<option value="all">-- All Vendors --</option>').trigger('change');
                return;
            }

            $.ajax({
                url: "{{ route('vendors-by-branch') }}", // Existing route from ReportingController
                type: "GET",
                data: { branch_id: branchId },
                success: function(response) {
                    let options = '<option value="all">-- All Vendors --</option>';
                    response.forEach(v => {
                        // The controller returns {id, customer_name, customer_type} for compatibility
                        options += `<option value="${v.id}">${v.customer_name}</option>`;
                    });
                    $('#vendor_id').html(options).trigger('change');
                }
            });
        });

        // ✅ ERP STANDARD: Auto-fetch report on page load
        fetchReport();

        function fetchReport() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var vendor_id = $('#vendor_id').val();

            // Update PDF Header Info for Export
            $('#pdfPeriod').text(start_date + ' to ' + end_date);
            $('#pdfVendorName').text($('#vendor_id option:selected').text());

            $('#loader').show();

            $.ajax({
                url: "{{ route('report.purchase.fetch') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    start_date: start_date,
                    end_date: end_date,
                    vendor_id: vendor_id,
                    branch_id: $('#branch_id').val()
                },
                success: function(response) {
                    $('#loader').hide();
                    renderRows(response.data);
                },
                error: function() {
                    $('#loader').hide();
                    alert('Error fetching purchase report');
                }
            });
        }

        /* ---------- WhatsApp Share ---------- */
        window.shareWhatsApp = function() {
            Swal.fire({
                title: 'Preparing WhatsApp Share...',
                text: 'Generating PDF document to share.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            var element = document.getElementById('reportContent');
            $('#pdfHeader').show(); // Show header for capture

            var opt = {
                margin:       0.2,
                filename:     'Purchase_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a3', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
                $('#pdfHeader').hide(); // Hide after capture
                var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
                
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    navigator.share({
                        title: 'Purchase Report',
                        text: 'Please find the attached Purchase Report.',
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
                
                var msg = "*Purchase Report*\nPlease find the attached PDF document.";
                var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
                window.open(waUrl, '_blank');
            });
        }

        /* ---------- Export Options & PDF ---------- */
        window.showExportOptions = function() {
            Swal.fire({
                title: 'Export Purchase Report',
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
            $('#pdfHeader').show(); // Show header for capture

            var opt = {
                margin:       0.2,
                filename:     'Purchase_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a3', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                $('#pdfHeader').hide(); // Hide after capture
                Swal.close();
            });
        };

        window.exportCSV = function () {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            $('#loader').show();

            $.ajax({
                url: "{{ route('report.purchase.fetch') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    start_date: start_date,
                    end_date: end_date,
                    vendor_id: $('#vendor_id').val(),
                    branch_id: $('#branch_id').val()
                },
                success: function(response) {
                    $('#loader').hide();
                    if (!response.data || !response.data.length) {
                        alert('No data to export');
                        return;
                    }

                    var csv = 'Purchase Date,Invoice No,M. Bill No,Vendor Invoice No,Vendor,Item Code,Item Name,Qty,Unit,Price,Item Discount,Line Total,Subtotal,Discount,Extra Cost,Net Amount,Paid Amount,Due Amount\n';
                    response.data.forEach(function(r) {
                        csv += `"${r.purchase_date}","${r.invoice_no}","${r.manual_invoice || ''}","${r.vendor_invoice_no || ''}","${r.vendor_name}","${r.item_code}","${r.item_name}",${r.qty},${r.unit},${r.price},${r.item_discount},${r.line_total},${r.subtotal},${r.discount},${r.extra_cost},${r.net_amount},${r.paid_amount},${r.due_amount}\n`;
                    });

                    var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8;'});
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'purchase_report_' + new Date().toISOString().slice(0,10) + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                },
                error: function() {
                    $('#loader').hide();
                    alert('Export failed');
                }
            });
        };

        /* ---------- Clean Full-Page Print Preview ---------- */
        window.openPrintPreview = function() {
            const formalDoc = document.getElementById('formalPrintDoc');
            if (!formalDoc) return;

            const tbody = formalDoc.querySelector('tbody');
            if (tbody && tbody.children.length === 0) {
                alert('Report data is still loading or empty. Please wait a moment or click Search.');
                return;
            }

            const reportHtml = formalDoc.innerHTML;
            const branchTitle = document.getElementById('p_branch_title')?.textContent || 'All Branches';
            const docTitle = 'Purchase Report';
            const fullTitle = `${docTitle} - ${branchTitle}`;

            // 1. Try opening as a clean, full normal page in a new tab
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
            max-width: 1350px;
            margin: 0 auto 14px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .report-badge {
            font-size: 13px;
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
            padding: 7px 14px;
            font-size: 12.5px;
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
            max-width: 1350px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            padding: 28px 32px;
            box-sizing: border-box;
        }

        .print-doc-header { margin-bottom: 8px; page-break-inside: avoid; }
        .print-company-title { font-size: 13pt; font-weight: 800; color: #000; margin-bottom: 2px; text-transform: uppercase; letter-spacing: .02em; }
        .print-doc-title { font-size: 18pt; font-weight: 900; color: #000; margin-bottom: 2px; }
        .print-doc-period { font-size: 10pt; font-weight: 600; color: #334155; }
        .print-top-divider { border-bottom: 2px solid #000; margin-top: 6px; margin-bottom: 10px; }

        .print-tb-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 14px; color: #000; table-layout: fixed; }
        .print-tb-table thead { display: table-header-group !important; }
        .print-tb-table tfoot { display: table-row-group !important; }
        .print-tb-table tr { page-break-inside: avoid !important; }
        .print-tb-table th { border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 6px 3px; background: #f8fafc; font-weight: 800; color: #000; font-size: 8pt; text-transform: uppercase; vertical-align: middle; line-height: 1.2; }
        .print-tb-table td { border-bottom: 1px solid #cbd5e1; padding: 5px 3px; vertical-align: middle; color: #000; font-size: 8.5pt; line-height: 1.25; }
        .print-tb-table .num-cell { text-align: right; font-variant-numeric: tabular-nums; font-feature-settings: "tnum"; white-space: nowrap; font-size: 8.5pt; }
        .print-tb-table tr.p-tfoot td { border-top: 1.5px solid #000; border-bottom: 3px double #000; font-weight: 800; padding: 7px 3px; background: #fff; font-size: 9pt; color: #000; }

        .print-doc-footer { margin-top: 24px; font-size: 8.5pt; color: #000; page-break-inside: avoid !important; }
        .print-footer-info { display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 6px; margin-bottom: 35px; font-weight: 600; color: #000; font-size: 8pt; }
        .print-sign-row { display: flex; justify-content: space-between; padding: 0 20px; }
        .print-sign-box { border-top: 1.5px dashed #000; width: 150px; text-align: center; padding-top: 6px; font-size: 8.5pt; font-weight: 700; color: #000; }

        @media print {
            @page {
                size: auto;
                margin: 0mm !important;
            }
            html, body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            .action-toolbar, .no-print {
                display: none !important;
            }
            .normal-page-card {
                border: none !important;
                box-shadow: none !important;
                padding: 8mm 4mm 8mm 4mm !important;
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                box-sizing: border-box !important;
            }
            .print-tb-table {
                width: 100% !important;
                max-width: 100% !important;
                table-layout: fixed !important;
                font-size: 7pt !important;
            }
            .print-tb-table th {
                padding: 3.5px 1.5px !important;
                font-size: 6.5pt !important;
                line-height: 1.1 !important;
            }
            .print-tb-table td {
                padding: 3px 1.5px !important;
                font-size: 7pt !important;
                line-height: 1.15 !important;
            }
            .print-tb-table .num-cell {
                font-size: 7pt !important;
                padding-right: 1.5px !important;
            }
            .print-tb-table tr.p-tfoot td {
                padding: 5px 2px !important;
                font-size: 7.5pt !important;
            }
        }
    </style>
</head>
<body>
    <div class="action-toolbar no-print">
        <div class="report-badge">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>${fullTitle}</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn-action btn-primary-action" onclick="printDoc()">
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
    <script>
        function printDoc() {
            document.title = '';
            window.print();
        }
        window.addEventListener('beforeprint', function() {
            document.title = '';
        });
        window.addEventListener('afterprint', function() {
            document.title = '';
        });
    <\/script>
</body>
</html>`);
                win.document.close();
                win.focus();
                return;
            }

            // 2. Fallback if popups are blocked by browser: show in-page modal
            const paper = document.getElementById('previewPaperContent');
            const modal = document.getElementById('reportPreviewModal');
            if (paper && modal) {
                paper.innerHTML = reportHtml;
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        };

        window.closePrintPreview = function() {
            const modal = document.getElementById('reportPreviewModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        };

        window.printFromPreview = function() {
            window.print();
        };

        // Suppress browser header/footer title during direct print
        window.addEventListener('beforeprint', function () {
            window._savedDocTitle = document.title;
            document.title = '';
        });
        window.addEventListener('afterprint', function () {
            document.title = window._savedDocTitle || 'ERP System';
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                window.closePrintPreview();
            }
        });
    });
</script>
@endsection
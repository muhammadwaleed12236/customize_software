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

    #saleTable th {
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
            padding: 10mm 12mm 10mm 12mm !important;
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
            font-size: 8pt !important;
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
            padding: 5px 4px !important;
            background: #f8fafc !important;
            font-weight: 800 !important;
            color: #000 !important;
            font-size: 8pt !important;
            text-transform: uppercase !important;
            vertical-align: middle !important;
        }
        .print-tb-table td {
            border-bottom: 1px solid #cbd5e1 !important;
            padding: 4px 4px !important;
            vertical-align: middle !important;
            color: #000 !important;
            font-size: 8.5pt !important;
        }
        .print-tb-table tr {
            page-break-inside: avoid !important;
        }
        .print-tb-table .num-cell {
            text-align: right !important;
            font-variant-numeric: tabular-nums !important;
            font-feature-settings: "tnum" !important;
            white-space: nowrap !important;
            font-size: 8.5pt !important;
        }
        .print-tb-table tr.p-tfoot td {
            border-top: 1.5px solid #000 !important;
            border-bottom: 3px double #000 !important;
            font-weight: 800 !important;
            padding: 6px 4px !important;
            background: #fff !important;
            font-size: 9pt !important;
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
        max-width: 1250px;
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
    .report-preview-paper .print-tb-table { width: 100%; table-layout: auto; border-collapse: collapse; font-size: 9pt; margin-bottom: 16px; color: #000; }
    .report-preview-paper .print-tb-table th { border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 7px 6px; background: #f8fafc; font-weight: 800; color: #000; font-size: 8.5pt; text-transform: uppercase; vertical-align: middle; line-height: 1.25; }
    .report-preview-paper .print-tb-table td { border-bottom: 1px solid #cbd5e1; padding: 6px 6px; vertical-align: middle; color: #000; font-size: 9pt; line-height: 1.3; }
    .report-preview-paper .print-tb-table .num-cell { text-align: right; font-variant-numeric: tabular-nums; font-feature-settings: "tnum"; white-space: nowrap; font-size: 9pt; }
    .report-preview-paper .print-tb-table tr.p-tfoot td { border-top: 2px solid #000; border-bottom: 3.5px double #000; font-weight: 800; padding: 8px 6px; background: #fff; font-size: 9.5pt; color: #000; }
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
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Sale Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-chart-line mr-1" style="color: var(--coa-gold);"></i> Detailed Sales Analysis & Business Intelligence &mdash; Ameen & Sons Corporate ERP</span>
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
                    <form id="saleFilterForm" class="row g-2 align-items-end mb-0">
                        @if(auth()->user()->hasRole('super admin'))
                        <div class="col-md-3">
                            <label class="f-label">Select Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control form-control-sm select2">
                                <option value="">-- All Branches --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-{{ auth()->user()->hasRole('super admin') ? '3' : '4' }}">
                            <label class="f-label">Select Customer</label>
                            <select name="customer_id" id="customer_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Customers --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
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

            {{-- 3. KPI Summary Cards --}}
            <div id="summaryCards" class="rpt-kpi-grid" style="display:none;">
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Gross Sales</div>
                        <div id="stat_gross" class="rpt-kpi-val">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-blue">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card highlight">
                    <div>
                        <div class="rpt-kpi-label" style="color: #047857;">Net Revenue</div>
                        <div id="stat_net" class="rpt-kpi-val emerald">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Sales Returns</div>
                        <div id="stat_return" class="rpt-kpi-val crimson">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-red">
                        <i class="fas fa-undo"></i>
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
                        <h2 style="margin:0; color:#0f1f38; text-transform:uppercase; letter-spacing:1px;">SALE REPORT</h2>
                        <p style="margin:5px 0; font-size:14px; color:#333;">
                            <strong>Period:</strong> <span id="pdfPeriod"></span> | 
                            <strong>Customer:</strong> <span id="pdfCustomerName">All Customers</span>
                        </p>
                        <p style="margin:0; font-size:12px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:40px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Processing sales data...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="saleTable" class="table table-bordered mb-0" style="font-size:12px; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 35px;">#</th>
                                    <th style="width: 90px; white-space: nowrap;">Date</th>
                                    <th style="width: 105px; white-space: nowrap;">Invoice</th>
                                    <th style="min-width: 150px; width: 16%;">Customer</th>
                                    <th style="min-width: 250px; width: 26%;">Products / Items</th>
                                    <th class="text-end" style="width: 75px; white-space: nowrap;">Qty</th>
                                    <th class="text-end" style="width: 80px; white-space: nowrap;">Disc</th>
                                    <th class="text-end" style="width: 100px; white-space: nowrap;">Gross Total</th>
                                    <th class="text-end" style="width: 105px; white-space: nowrap;">Total Net</th>
                                    <th class="text-end" style="width: 95px; white-space: nowrap;">Returns</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody"></tbody>
                            <tfoot id="tableFooter" style="display:none;">
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                                    <td colspan="5" class="text-end font-weight-bold" style="font-family: sans-serif;">Grand Totals:</td>
                                    <td class="text-end font-weight-bold text-dark" id="footerQty">0.00</td>
                                    <td class="text-end text-danger font-weight-bold" id="footerDisc">0.00</td>
                                    <td class="text-end text-primary font-weight-bold" id="footerGross">0.00</td>
                                    <td class="text-end text-success font-weight-bold" style="background:rgba(13, 159, 110, 0.12);" id="footerNet">0.00</td>
                                    <td class="text-end text-danger font-weight-bold" id="footerReturn">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>{{-- /#reportContent --}}

            {{-- ── FORMAL A4 PRINT DOCUMENT (Shown ONLY when printing or in clean preview) ──── --}}
            <div id="formalPrintDoc" class="formal-print-sheet">
                <div class="print-doc-header">
                    <div class="print-company-title" id="p_branch_title">ALL BRANCHES</div>
                    <div class="print-doc-title">Sale Report</div>
                    <div class="print-doc-period" id="p_period_text">For The Period: 01 Nov 2026 to 21 Nov 2026</div>
                    <div class="print-top-divider"></div>
                </div>

                <table class="print-tb-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 3%;">#</th>
                            <th style="width: 8%; white-space: nowrap;">Date</th>
                            <th style="width: 9%; white-space: nowrap;">Invoice</th>
                            <th style="width: 16%;">Customer</th>
                            <th style="width: 26%;">Products / Items</th>
                            <th class="text-end" style="width: 6%; white-space: nowrap;">Qty</th>
                            <th class="text-end" style="width: 6%; white-space: nowrap;">Disc</th>
                            <th class="text-end" style="width: 8.5%; white-space: nowrap;">Gross Total</th>
                            <th class="text-end" style="width: 9%; white-space: nowrap;">Total Net</th>
                            <th class="text-end" style="width: 8.5%; white-space: nowrap;">Returns</th>
                        </tr>
                    </thead>
                    <tbody id="p_sale_body">
                        <!-- Populated dynamically -->
                    </tbody>
                    <tfoot>
                        <tr class="p-tfoot">
                            <td colspan="5" class="text-end" style="font-size:8.5pt; font-weight:800; letter-spacing:.02em; text-transform:uppercase;">GRAND TOTAL:</td>
                            <td class="text-end num-cell" style="font-weight:800;" id="p_foot_qty">0.00</td>
                            <td class="text-end num-cell" style="font-weight:800;" id="p_foot_disc">0.00</td>
                            <td class="text-end num-cell" style="font-weight:800;" id="p_foot_gross">0.00</td>
                            <td class="text-end num-cell" style="font-weight:800;" id="p_foot_net">0.00</td>
                            <td class="text-end num-cell" style="font-weight:800;" id="p_foot_return">0.00</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="print-doc-footer">
                    <div class="print-footer-info">
                        <span>Printed on: <span id="p_print_time"></span></span>
                        <span>Detailed Sales Summary</span>
                        <span>ERP System &mdash; Sales Report</span>
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
                        <span style="font-weight:700; font-size:14px; letter-spacing:.02em;">Sale Report &mdash; Print Document Preview</span>
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
    #saleTable { border: 1px solid #cbd5e1; }
    #saleTable th { 
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
    #saleTable td { 
        vertical-align: middle; 
        border: 1px solid #e2e8f0; 
        padding: 8px 8px; 
        font-weight: 500;
        color: #1e293b;
    }
    #saleTable tbody tr:nth-child(even) { background-color: #f8fafc; }
    #saleTable tbody tr:hover { background-color: #f1f5f9 !important; transition: 0.15s; }
    
    .invoice-no { color: #1e3a5f; font-weight: 700; font-family: monospace; font-size: 12px; }
    .customer-name { font-weight: 700; color: #0f172a; font-size: 12.5px; }
    .product-item { padding: 3px 0; border-bottom: 1px dashed #e2e8f0; }
    .product-item:last-child { border-bottom: none; }
    
    @media print {
        #saleFilterForm, #btnSearch, .btn { display: none !important; }
        .main-content { padding: 0 !important; margin-top:0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        #reportContent { overflow: visible !important; }
    }
</style>
@endsection

@section('js')
<script>
    const isSuper = {{ (auth()->user() && auth()->user()->hasRole('super admin')) ? 'true' : 'false' }};

    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: '-- Select Option --'
        });

        // ✅ DYNAMIC: Fetch customers when branch changes (for Super Admin)
        $('#branch_id').on('change', function() {
            let branchId = $(this).val();
            let customerDropdown = $('#customer_id');

            // Show loading state
            customerDropdown.html('<option value="all">Loading...</option>');

            $.ajax({
                url: "{{ route('report.customers.byBranch') }}",
                type: "GET",
                data: { branch_id: branchId },
                success: function(res) {
                    let html = '<option value="all">-- All Customers --</option>';
                    res.forEach(function(c) {
                        html += `<option value="${c.id}">${c.customer_name}</option>`;
                    });
                    customerDropdown.html(html).trigger('change');
                },
                error: function() {
                    customerDropdown.html('<option value="all">-- Error loading --</option>');
                }
            });
        });
        
        function n(v) { return parseFloat(v) || 0; }
        function fmt(v) { return n(v).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

        function renderRows(rows) {
            let tableContent = '';
            let pSaleContent = '';
            let grandQty = 0;
            let grandGross = 0;
            let grandDisc = 0;
            let grandNet = 0;
            let grandReturn = 0;

            if (rows.length === 0) {
                $('#reportBody').html('<tr><td colspan="10" class="text-center py-4 text-muted">No sales found for this criteria</td></tr>');
                $('#p_sale_body').html('<tr><td colspan="10" class="text-center py-4" style="color:#64748b; font-size:9pt;">No sales found for this criteria</td></tr>');
                $('#summaryCards').hide();
                $('#tableFooter').hide();
                return;
            }

            rows.forEach(function(s, idx) {
                let itemsHtml = "";
                let pItemsHtml = "";
                s.items.forEach((item, iIdx) => {
                    itemsHtml += `<div class="product-item">
                        <span class="font-weight-bold text-dark" style="font-size:12.5px;">${item.product_name}</span> 
                        <code class="text-muted" style="font-size:10.5px;">(${item.product_code})</code><br>
                        <span class="text-muted small" style="font-size:11px;">Qty: <strong>${item.qty}</strong> x ${fmt(item.price)} = <strong class="text-dark">${fmt(item.amount)}</strong></span>
                        ${item.discount_amount > 0 ? `<span class="text-danger ms-2 font-weight-bold" style="font-size:11px;">Disc: ${fmt(item.discount_amount)}</span>` : ''}
                    </div>`;

                    let discBadge = item.discount_amount > 0 ? ` <span style="color:#dc2626; font-size:7.5pt; font-weight:700;">(Disc: ${fmt(item.discount_amount)})</span>` : '';
                    pItemsHtml += `<div style="padding: 2px 0; font-size: 8pt; line-height: 1.35; ${iIdx > 0 ? 'border-top: 1px dotted #e2e8f0;' : ''}">
                        <strong style="color:#0f172a;">${item.product_name}</strong> <span style="color:#64748b; font-size:7.5pt;">(${item.product_code})</span><br>
                        <span style="color:#334155; font-size:7.5pt;">Qty: <strong>${item.qty}</strong> &times; ${fmt(item.price)} = <strong>${fmt(item.amount)}</strong></span>${discBadge}
                    </div>`;
                });

                let returnsHtml = '<span class="text-muted font-monospace">&#8212;</span>';
                let pReturnsHtml = '<span style="color:#94a3b8;">&mdash;</span>';
                if (s.returns && s.returns.length > 0) {
                    returnsHtml = "";
                    pReturnsHtml = "";
                    s.returns.forEach(r => {
                        returnsHtml += `<div class="text-danger font-weight-bold small border-bottom border-danger-subtle pb-1 mb-1 font-monospace" style="font-size:11.5px;">
                            ${r.product} (${r.qty}) = ${fmt(r.total_net)}
                        </div>`;
                        pReturnsHtml += `<div style="font-size:7.5pt; color:#dc2626; font-weight:700; line-height:1.3;">
                            ${r.product} (${r.qty}) = ${fmt(r.total_net)}
                        </div>`;
                    });
                }

                let branchBadge = '';
                let pBranchBadge = '';
                if (isSuper && s.branch_name) {
                    branchBadge = `<div class="text-muted mt-1" style="font-size: 10.5px; opacity: 0.85;"><i class="fas fa-building mr-1" style="font-size: 9.5px; color: #64748b;"></i><span style="color: #475569; font-weight: 600;">${s.branch_name}</span></div>`;
                    pBranchBadge = `<div style="font-size:7.5pt; color:#475569; font-weight:600; margin-top:2px;">🏛️ ${s.branch_name}</div>`;
                }

                let addressHtml = s.address ? `<small class="text-muted d-block" style="font-size: 11px;">${s.address}</small>` : '';

                tableContent += `<tr>
                    <td class="text-center text-muted" style="font-size:11px;">${idx + 1}</td>
                    <td style="white-space:nowrap; font-size:11.5px;">${new Date(s.created_at).toLocaleDateString('en-GB')}</td>
                    <td><span class="invoice-no">${s.invoice_no}</span></td>
                    <td>
                        <div class="customer-name">${s.customer_name}</div>
                        ${addressHtml}
                        ${branchBadge}
                    </td>
                    <td>${itemsHtml}</td>
                    <td class="text-end font-monospace font-weight-bold" style="font-size:12px; white-space:nowrap;">${n(s.total_qty).toFixed(2)}</td>
                    <td class="text-end font-monospace ${n(s.discount_amount) > 0 ? 'text-danger font-weight-bold' : 'text-muted'}" style="font-size:12px;">${fmt(s.discount_amount)}</td>
                    <td class="text-end font-monospace font-weight-bold" style="background:rgba(30, 58, 95, 0.04); color:#1e3a5f; font-size:12px;">${fmt(s.total_items_amount)}</td>
                    <td class="text-end font-monospace font-weight-bold" style="background:rgba(13, 159, 110, 0.08); color:#047857; font-size:12.5px;">${fmt(s.total_net)}</td>
                    <td class="text-end" style="background:#fff5f5;">${returnsHtml}</td>
                </tr>`;

                pSaleContent += `<tr>
                    <td class="text-center" style="font-size:8pt; width:32px;">${idx + 1}</td>
                    <td style="white-space:nowrap; font-size:8pt;">${new Date(s.created_at).toLocaleDateString('en-GB')}</td>
                    <td style="white-space:nowrap; font-weight:700; font-size:8pt; color:#1e3a5f;">${s.invoice_no}</td>
                    <td>
                        <div style="font-weight:700; font-size:8.5pt; color:#0f172a;">${s.customer_name}</div>
                        ${s.address ? `<div style="font-size:7.5pt; color:#64748b;">${s.address}</div>` : ''}
                        ${pBranchBadge}
                    </td>
                    <td>${pItemsHtml}</td>
                    <td class="num-cell" style="font-weight:700;">${n(s.total_qty).toFixed(2)}</td>
                    <td class="num-cell" style="${n(s.discount_amount) > 0 ? 'color:#dc2626; font-weight:700;' : ''}">${fmt(s.discount_amount)}</td>
                    <td class="num-cell" style="font-weight:700;">${fmt(s.total_items_amount)}</td>
                    <td class="num-cell" style="font-weight:800; color:#047857;">${fmt(s.total_net)}</td>
                    <td class="num-cell">${pReturnsHtml}</td>
                </tr>`;

                grandQty += n(s.total_qty);
                grandGross += n(s.total_items_amount);
                grandDisc += n(s.discount_amount);
                grandNet += n(s.total_net);
                grandReturn += n(s.total_returns_amount);
            });

            // Summary Stats
            $('#stat_gross').text('Rs. ' + fmt(grandGross));
            $('#stat_net').text('Rs. ' + fmt(grandNet));
            $('#stat_return').text('Rs. ' + fmt(grandReturn));
            $('#stat_disc').text('Rs. ' + fmt(grandDisc));
            $('#summaryCards').fadeIn();

            // Footer Totals
            $('#footerQty').text(grandQty.toFixed(2));
            $('#footerDisc').text(fmt(grandDisc));
            $('#footerGross').text(fmt(grandGross));
            $('#footerNet').text(fmt(grandNet));
            $('#footerReturn').text(fmt(grandReturn));
            $('#tableFooter').show();

            $('#reportBody').html(tableContent);

            // Populate Clean Formal Print/Preview Table
            $('#p_sale_body').html(pSaleContent);
            $('#p_foot_qty').text(grandQty.toFixed(2));
            $('#p_foot_disc').text(fmt(grandDisc));
            $('#p_foot_gross').text(fmt(grandGross));
            $('#p_foot_net').text(fmt(grandNet));
            $('#p_foot_return').text(fmt(grandReturn));

            // Sync Header in Formal Print Doc
            let branchText = 'All Branches';
            if ($('#branch_id').length && $('#branch_id').val() && $('#branch_id').val() !== 'all' && $('#branch_id').val() !== '') {
                branchText = $('#branch_id option:selected').text();
            }
            const pBranchElem = document.getElementById('p_branch_title');
            if (pBranchElem) pBranchElem.textContent = branchText.toUpperCase();

            let sDate = $('#start_date').val();
            let eDate = $('#end_date').val();
            let custText = $('#customer_id option:selected').text();
            let pStr = '';
            if (sDate && eDate) {
                pStr = `For The Period: ${sDate} to ${eDate}`;
            } else {
                pStr = `Date: All Dates`;
            }
            if (custText && custText !== '-- All Customers --' && custText !== 'Loading...') {
                pStr += ` | Customer: ${custText}`;
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

        // ✅ ERP STANDARD: Auto-fetch report on page load
        fetchReport();

        function fetchReport() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var customer_id = $('#customer_id').val();
            var branch_id = $('#branch_id').val() || '';

            // Update PDF Header Info for Export
            let periodText = start_date + ' to ' + end_date;
            if (branch_id) {
                periodText += ' | Branch: ' + $('#branch_id option:selected').text();
            }
            $('#pdfPeriod').text(periodText);
            $('#pdfCustomerName').text($('#customer_id option:selected').text());

            $('#loader').show();

            $.ajax({
                url: "{{ route('report.sale.fetch') }}",
                type: "GET",
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    customer_id: customer_id,
                    branch_id: branch_id
                },
                success: function(response) {
                    $('#loader').hide();
                    renderRows(response);
                },
                error: function() {
                    $('#loader').hide();
                    alert('Error fetching sale report');
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
            $('#pdfHeader').show(); 

            var opt = {
                margin:       0.2,
                filename:     'Sale_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a3', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
                $('#pdfHeader').hide(); 
                var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
                
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    navigator.share({
                        title: 'Sale Report',
                        text: 'Please find the attached Sale Report.',
                        files: [file]
                    }).then(() => {
                        Swal.close();
                    }).catch((error) => {
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
                text: 'The PDF will be downloaded now. Please attach it manually to your WhatsApp chat.',
                confirmButtonText: 'Download & Open WhatsApp'
            }).then(() => {
                var url = URL.createObjectURL(pdfBlob);
                var a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                var msg = "*Sale Report*\nPlease find the attached PDF document.";
                var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
                window.open(waUrl, '_blank');
            });
        }

        /* ---------- Export Options ---------- */
        window.showExportOptions = function() {
            Swal.fire({
                title: 'Export Sale Report',
                text: 'Choose format:',
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
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            var element = document.getElementById('reportContent');
            $('#pdfHeader').show(); 

            var opt = {
                margin:       0.2,
                filename:     'Sale_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a3', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                $('#pdfHeader').hide(); 
                Swal.close();
            });
        };

        window.exportCSV = function () {
            let csv = [];
            let headers = [];
            $("#saleTable thead th").each(function() {
                headers.push('"' + $(this).text().trim() + '"');
            });
            csv.push(headers.join(","));

            $("#saleTable tbody tr").each(function() {
                let row = [];
                $(this).find('td').each(function() {
                    let cellText = $(this).text().replace(/<[^>]*>/g, "").replace(/\s+/g, " ").trim();
                    row.push('"' + cellText.replace(/"/g, '""') + '"');
                });
                csv.push(row.join(","));
            });

            let csvString = "\uFEFF" + csv.join("\n");
            let blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            let url = URL.createObjectURL(blob);
            let a = document.createElement("a");
            a.href = url;
            a.download = "sale_report_" + new Date().toISOString().slice(0,10) + ".csv";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
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
            const docTitle = 'Sale Report';
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
            max-width: 1250px;
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
            max-width: 1250px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            padding: 28px 34px;
        }

        .print-doc-header { margin-bottom: 8px; page-break-inside: avoid; }
        .print-company-title { font-size: 13pt; font-weight: 800; color: #000; margin-bottom: 2px; text-transform: uppercase; letter-spacing: .02em; }
        .print-doc-title { font-size: 18pt; font-weight: 900; color: #000; margin-bottom: 2px; }
        .print-doc-period { font-size: 10pt; font-weight: 600; color: #334155; }
        .print-top-divider { border-bottom: 2px solid #000; margin-top: 6px; margin-bottom: 10px; }

        .print-tb-table { width: 100% !important; max-width: 100% !important; border-collapse: collapse !important; font-size: 8pt !important; margin-bottom: 14px !important; color: #000 !important; table-layout: fixed !important; word-break: break-word !important; overflow-wrap: break-word !important; }
        .print-tb-table thead { display: table-header-group !important; }
        .print-tb-table tfoot { display: table-row-group !important; }
        .print-tb-table tr { page-break-inside: avoid !important; }
        .print-tb-table th { border-top: 1.5px solid #000 !important; border-bottom: 1.5px solid #000 !important; padding: 5px 3px !important; background: #f8fafc !important; font-weight: 800 !important; color: #000 !important; font-size: 7.5pt !important; text-transform: uppercase !important; vertical-align: middle !important; }
        .print-tb-table td { border-bottom: 1px solid #cbd5e1 !important; padding: 4px 3px !important; vertical-align: middle !important; color: #000 !important; font-size: 8pt !important; }
        .print-tb-table .num-cell { text-align: right !important; font-variant-numeric: tabular-nums !important; font-feature-settings: "tnum" !important; white-space: nowrap !important; font-size: 8pt !important; }
        .print-tb-table tr.p-tfoot td { border-top: 1.5px solid #000 !important; border-bottom: 3px double #000 !important; font-weight: 800 !important; padding: 6px 3px !important; background: #fff !important; font-size: 8.5pt !important; color: #000 !important; }

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
                padding: 8mm 6mm 8mm 6mm !important;
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                box-sizing: border-box !important;
            }
        }
    </style>
</head>
<body>
    <div class="action-toolbar no-print">
        <div class="report-badge">
            <i class="fas fa-shopping-cart"></i>
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
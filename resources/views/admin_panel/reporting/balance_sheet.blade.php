@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --bs-navy:       #1e3a5f;
        --bs-navy-dark:  #0f1f38;
        --bs-navy-light: #2c5282;
        --bs-gold:       #c8973a;
        --bs-emerald:    #0d9f6e;
        --bs-red:        #dc2626;
        --bs-border:     #e2e8f0;
    }

    .rpt-wrapper { padding: 8px 0 20px 0; font-family: 'Inter', system-ui, sans-serif; }

    .rpt-header-bar {
        background: linear-gradient(135deg, var(--bs-navy-dark) 0%, var(--bs-navy) 60%, var(--bs-navy-light) 100%);
        border-radius: 8px; padding: 12px 18px; color: #fff;
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        box-shadow: 0 3px 12px rgba(15,31,56,.12); margin-bottom: 12px;
    }
    .rpt-header-icon {
        width: 38px; height: 38px; border-radius: 8px;
        background: rgba(255,255,255,.12); display: flex; align-items: center;
        justify-content: center; font-size: 18px; color: var(--bs-gold);
        border: 1px solid rgba(200,151,58,.3); flex-shrink: 0;
    }
    .rpt-header-title { font-size: 16px; font-weight: 800; color: #fff !important; margin: 0; }
    .rpt-header-sub   { font-size: 11.5px; color: rgba(255,255,255,.85); margin-top: 2px; }

    .f-label {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        color: #475569; letter-spacing: .03em; margin-bottom: 3px; display: block;
    }

    /* KPI Grid */
    .bs-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 12px; }
    @media(max-width: 992px){ .bs-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media(max-width: 576px){ .bs-kpi-grid { grid-template-columns: 1fr; } }

    .bs-kpi-card {
        background: #fff; border-radius: 7px; padding: 10px 14px;
        border: 1px solid var(--bs-border); box-shadow: 0 1px 4px rgba(0,0,0,.03);
        display: flex; align-items: center; justify-content: space-between;
    }
    .bs-kpi-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing:.04em; margin-bottom: 1px; }
    .bs-kpi-val   { font-size: 17px; font-weight: 800; color: var(--bs-navy); font-family: monospace; }
    .bs-kpi-val.emerald { color: #047857; }
    .bs-kpi-val.crimson { color: var(--bs-red); }
    .bs-kpi-icon  { width: 34px; height: 34px; border-radius: 6px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink: 0; }

    /* Statement 2-Column Balanced Cards */
    .bs-panel {
        background: #fff; border-radius: 8px; border: 1px solid var(--bs-border);
        box-shadow: 0 1px 4px rgba(0,0,0,.03); margin-bottom: 8px; overflow: hidden;
    }
    .bs-panel-grow {
        background: #fff; border-radius: 8px; border: 1px solid var(--bs-border);
        box-shadow: 0 1px 4px rgba(0,0,0,.03); margin-bottom: 8px; overflow: hidden;
        display: flex; flex-direction: column; flex: 1;
    }
    .bs-section-header {
        background: #0f1f38; color: #fff; padding: 7px 12px;
        font-size: 12px; font-weight: 800; text-transform: uppercase;
        letter-spacing: .04em; display: flex; justify-content: space-between; align-items: center;
    }
    .bs-section-subhead {
        background: #f1f5f9; color: #334155; padding: 5px 12px;
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
        border-top: 1px solid var(--bs-border); border-bottom: 1px solid var(--bs-border);
    }
    .bs-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 0; }
    .bs-table td { padding: 6px 12px; border-bottom: 1px solid #f8fafc; }
    .bs-table tr:hover td { background: #f8fafc; }
    .bs-table .item-name { color: #334155; font-weight: 500; }
    .bs-table .item-sub  { color: #64748b; font-size: 11px; padding-left: 14px; }
    .bs-table .amount-col { text-align: right; font-family: monospace; font-weight: 700; width: 160px; }
    .bs-table .total-row td {
        background: #f8fafc; font-weight: 800; font-size: 12.5px;
        border-top: 1.5px solid #cbd5e1; border-bottom: 1.5px solid #cbd5e1;
        color: var(--bs-navy);
    }

    /* Scroll box for accounts breakdown */
    .bs-scroll-box {
        max-height: 140px; overflow-y: auto; overflow-x: hidden;
    }
    .bs-scroll-box::-webkit-scrollbar { width: 4px; }
    .bs-scroll-box::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    /* Grand Total Callout */
    .bs-grand-total-card {
        border-radius: 8px; padding: 10px 14px; display: flex; min-height: 52px;
        align-items: center; justify-content: space-between; border: 1.5px solid;
    }
    .bs-grand-total-card.assets {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #059669; color: #047857;
    }
    .bs-grand-total-card.liabilities {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #2563eb; color: #1d4ed8;
    }

    /* Print */
    #formalPrintDoc {
        display: none;
    }

    @media print {
        @page {
            size: A4 portrait;
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
        .rpt-header-bar, #filterCard, .bs-kpi-grid, #summaryCards,
        #placeholder, #loader, .btn, #reportContainer,
        .main-footer, .header, .sidebar, .sidebar-menu, .header-area,
        #reportPreviewModal {
            display: none !important;
        }

        #formalPrintDoc {
            display: block !important;
            box-sizing: border-box !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 100% !important;
            padding: 8mm 12mm 6mm 12mm !important;
            margin: 0 !important;
            background: #fff !important;
            color: #000 !important;
            page-break-inside: avoid !important;
            page-break-after: avoid !important;
        }

        .print-doc-header {
            margin-bottom: 6px;
        }
        .print-company-title {
            font-size: 15pt;
            font-weight: 800;
            color: #000;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .print-doc-title {
            font-size: 20pt;
            font-weight: 900;
            color: #000;
            margin-bottom: 2px;
        }
        .print-doc-period {
            font-size: 11.5pt;
            font-weight: 700;
            color: #000;
        }
        .print-top-divider {
            border-bottom: 2.5px solid #000;
            margin-top: 6px;
            margin-bottom: 8px;
        }

        .print-currency-header {
            text-align: right;
            margin-bottom: 4px;
            padding-right: 2px;
        }
        .print-currency-header span {
            font-size: 11.5pt;
            font-style: italic;
            text-decoration: underline;
            font-weight: 700;
            color: #000;
        }

        .print-statement-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
            margin-bottom: 12px;
            color: #000;
        }
        .print-statement-table td {
            padding: 4px 4px;
            vertical-align: middle;
            color: #000;
        }
        .print-statement-table tr.p-sec-head td {
            font-weight: 900;
            font-size: 13pt;
            padding-top: 10px;
            padding-bottom: 4px;
            color: #000;
            letter-spacing: .02em;
        }
        .print-statement-table tr.p-sub-item td.p-desc {
            padding-left: 20px;
            color: #000;
            font-weight: 600;
            font-size: 11pt;
        }
        .print-statement-table tr.p-sub-item td.p-val {
            text-align: right;
            font-family: "Courier New", Courier, monospace, sans-serif;
            font-size: 11.5pt;
            font-weight: 700;
            color: #000;
            width: 170px;
        }
        .print-statement-table tr.p-subtotal td {
            font-weight: 800;
            padding-top: 6px;
            padding-bottom: 6px;
            font-size: 11.5pt;
            color: #000;
        }
        .print-statement-table tr.p-subtotal td.p-desc {
            text-align: right;
            padding-right: 20px;
            font-weight: 800;
        }
        .print-statement-table tr.p-subtotal td.p-val {
            text-align: right;
            font-family: "Courier New", Courier, monospace, sans-serif;
            font-size: 11.5pt;
            font-weight: 800;
            border-top: 1.5px solid #000;
            color: #000;
            width: 170px;
        }
        .print-statement-table tr.p-grandtotal td {
            font-weight: 900;
            font-size: 13pt;
            padding-top: 7px;
            padding-bottom: 7px;
            color: #000;
        }
        .print-statement-table tr.p-grandtotal td.p-desc {
            text-align: right;
            padding-right: 20px;
            font-weight: 900;
            letter-spacing: .01em;
        }
        .print-statement-table tr.p-grandtotal td.p-val {
            text-align: right;
            font-family: "Courier New", Courier, monospace, sans-serif;
            font-size: 13pt;
            font-weight: 900;
            border-top: 1.5px solid #000;
            border-bottom: 3.5px double #000;
            color: #000;
            width: 170px;
        }

        .print-doc-footer {
            margin-top: 16px;
            font-size: 9.5pt;
            color: #000;
            page-break-inside: avoid;
        }
        .print-footer-info {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-bottom: 30px;
            font-weight: 600;
            color: #000;
        }
        .print-sign-row {
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }
        .print-sign-box {
            border-top: 1.5px dashed #000;
            width: 160px;
            text-align: center;
            padding-top: 6px;
            font-size: 10pt;
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
        max-width: 820px;
        min-height: 80vh;
        padding: 30px 40px;
        box-shadow: 0 1px 10px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #000;
        box-sizing: border-box;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .report-preview-paper .print-doc-header { margin-bottom: 6px; }
    .report-preview-paper .print-company-title { font-size: 15pt; font-weight: 800; color: #000; margin-bottom: 2px; text-transform: uppercase; letter-spacing: .02em; }
    .report-preview-paper .print-doc-title { font-size: 20pt; font-weight: 900; color: #000; margin-bottom: 2px; }
    .report-preview-paper .print-doc-period { font-size: 11.5pt; font-weight: 700; color: #000; }
    .report-preview-paper .print-top-divider { border-bottom: 2.5px solid #000; margin-top: 6px; margin-bottom: 8px; }
    .report-preview-paper .print-currency-header { text-align: right; margin-bottom: 4px; padding-right: 2px; }
    .report-preview-paper .print-currency-header span { font-size: 11.5pt; font-style: italic; text-decoration: underline; font-weight: 700; color: #000; }
    .report-preview-paper .print-statement-table { width: 100%; border-collapse: collapse; font-size: 11pt; margin-bottom: 12px; color: #000; }
    .report-preview-paper .print-statement-table td { padding: 4px 4px; vertical-align: middle; color: #000; }
    .report-preview-paper .print-statement-table tr.p-sec-head td { font-weight: 900; font-size: 13pt; padding-top: 10px; padding-bottom: 4px; color: #000; letter-spacing: .02em; }
    .report-preview-paper .print-statement-table tr.p-sub-item td.p-desc { padding-left: 20px; color: #000; font-weight: 600; font-size: 11pt; }
    .report-preview-paper .print-statement-table tr.p-sub-item td.p-val { text-align: right; font-family: "Courier New", Courier, monospace, sans-serif; font-size: 11.5pt; font-weight: 700; color: #000; width: 170px; }
    .report-preview-paper .print-statement-table tr.p-subtotal td { font-weight: 800; padding-top: 6px; padding-bottom: 6px; font-size: 11.5pt; color: #000; }
    .report-preview-paper .print-statement-table tr.p-subtotal td.p-desc { text-align: right; padding-right: 20px; font-weight: 800; }
    .report-preview-paper .print-statement-table tr.p-subtotal td.p-val { text-align: right; font-family: "Courier New", Courier, monospace, sans-serif; font-size: 11.5pt; font-weight: 800; border-top: 1.5px solid #000; color: #000; width: 170px; }
    .report-preview-paper .print-statement-table tr.p-grandtotal td { font-weight: 900; font-size: 13pt; padding-top: 10px; padding-bottom: 10px; color: #000; }
    .report-preview-paper .print-statement-table tr.p-grandtotal td.p-desc { text-align: right; padding-right: 20px; font-weight: 900; }
    .report-preview-paper .print-statement-table tr.p-grandtotal td.p-val { text-align: right; font-family: "Courier New", Courier, monospace, sans-serif; font-size: 13pt; font-weight: 900; border-top: 1.5px solid #000; border-bottom: 3.5px double #000; color: #000; width: 170px; }
    .report-preview-paper .print-doc-footer { margin-top: 16px; font-size: 9.5pt; color: #000; }
    .report-preview-paper .print-footer-info { display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 5px; margin-bottom: 30px; font-weight: 600; color: #000; }
    .report-preview-paper .print-sign-row { display: flex; justify-content: space-between; padding: 0 20px; }
    .report-preview-paper .print-sign-box { border-top: 1.5px dashed #000; width: 160px; text-align: center; padding-top: 6px; font-size: 10pt; font-weight: 700; color: #000; }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">

            {{-- ── 1. Header Bar ──────────────────────────────── --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon"><i class="fas fa-landmark"></i></div>
                    <div>
                        <h4 class="rpt-header-title">Balance Sheet</h4>
                        <div class="rpt-header-sub">
                            <i class="fas fa-balance-scale mr-1" style="color:var(--bs-gold);"></i>
                            Statement of Financial Position &mdash; Assets, Liabilities &amp; Equity &mdash; ERP System
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="openPrintPreview()" class="btn btn-sm btn-outline-light font-weight-bold no-print" title="Clean Full-Page Preview">
                        <i class="fas fa-eye mr-1"></i> Preview
                    </button>
                    <button onclick="exportCSV()" class="btn btn-sm btn-light font-weight-bold text-dark border no-print">
                        <i class="fas fa-file-csv mr-1 text-success"></i> CSV
                    </button>
                </div>
            </div>

            {{-- ── 2. Filter Card ──────────────────────────────── --}}
            <div class="card shadow-sm mb-2 border-0 no-print" id="filterCard" style="border-radius:8px; border:1px solid var(--bs-border) !important;">
                <div class="card-body p-2 px-3">
                    <div class="row g-2 align-items-end">

                        @if($isSuper)
                        <div class="col-md-4">
                            <label class="f-label">Branch</label>
                            <select id="branch_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Branches --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-{{ $isSuper ? '5' : '8' }}">
                            <label class="f-label">As of Date</label>
                            <input type="date" id="as_of_date" class="form-control form-control-sm"
                                   style="height:36px; border-radius:6px; border:1.5px solid #cbd5e1;"
                                   value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-{{ $isSuper ? '3' : '4' }}">
                            <button type="button" id="btnSearch" onclick="loadReport()" class="btn btn-sm btn-primary w-100 font-weight-bold"
                                    style="height:36px; border-radius:6px; background:var(--bs-navy); border-color:var(--bs-navy);">
                                <i class="fas fa-search mr-1"></i> Generate
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 3. KPI Summary Cards ─────────────────────────── --}}
            <div id="summaryCards" class="bs-kpi-grid" style="display:none;">
                <div class="bs-kpi-card">
                    <div>
                        <div class="bs-kpi-label">Total Assets</div>
                        <div id="kpiTotalAssets" class="bs-kpi-val emerald">Rs. 0.00</div>
                    </div>
                    <div class="bs-kpi-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-coins"></i></div>
                </div>

                <div class="bs-kpi-card">
                    <div>
                        <div class="bs-kpi-label">Total Liabilities</div>
                        <div id="kpiTotalLiabilities" class="bs-kpi-val crimson">Rs. 0.00</div>
                    </div>
                    <div class="bs-kpi-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-hand-holding-usd"></i></div>
                </div>

                <div class="bs-kpi-card">
                    <div>
                        <div class="bs-kpi-label">Working Capital</div>
                        <div id="kpiWorkingCapital" class="bs-kpi-val">Rs. 0.00</div>
                    </div>
                    <div class="bs-kpi-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-chart-line"></i></div>
                </div>

                <div class="bs-kpi-card">
                    <div>
                        <div class="bs-kpi-label">Owner's Equity</div>
                        <div id="kpiTotalEquity" class="bs-kpi-val" style="color:#7c3aed;">Rs. 0.00</div>
                    </div>
                    <div class="bs-kpi-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-balance-scale"></i></div>
                </div>
            </div>

            {{-- ── 4. Main Statement (2-Column Balanced Layout) ──── --}}
            <div id="loader" style="display:none;text-align:center;padding:30px;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2 small font-weight-bold">Calculating balance sheet...</p>
            </div>

            <div id="placeholder" class="card shadow-sm border-0 text-center py-5 text-muted" style="border-radius:8px; border:1px solid var(--bs-border) !important;">
                <i class="fas fa-landmark fa-3x mb-3" style="opacity:.3;"></i>
                <p>Select filters and click <strong>Generate</strong> to view the balance sheet.</p>
            </div>

            {{-- Report Container --}}
            <div id="reportContainer" style="display:none;">

                {{-- Metadata Bar --}}
                <div class="d-flex justify-content-between align-items-center mb-2 px-1 no-print">
                    <span class="font-weight-bold text-dark" style="font-size:12px;">
                        <i class="fas fa-building text-primary mr-1"></i> <span id="lblBranch">All Branches</span>
                    </span>
                    <span class="badge badge-light border text-muted" style="font-size:11px;" id="lblAsOfDate"></span>
                </div>

                <div class="row g-2 align-items-stretch">

                    {{-- ── LEFT COLUMN: ASSETS ───────────────────────── --}}
                    <div class="col-lg-6 d-flex flex-column">

                        <div class="bs-panel-grow mb-2">
                            <div class="bs-section-header">
                                <span><i class="fas fa-coins mr-1 text-warning"></i> ASSETS</span>
                                <span id="hdrTotalAssets">Rs. 0.00</span>
                            </div>

                            {{-- Current Assets Subhead --}}
                            <div class="bs-section-subhead">1. CURRENT ASSETS</div>

                            {{-- Cash in Hand --}}
                            <table class="bs-table">
                                <tbody>
                                    <tr style="background:#f8fafc;">
                                        <td class="font-weight-bold text-dark"><i class="fas fa-money-bill-wave text-success mr-1"></i> Cash in Hand</td>
                                        <td class="amount-col text-success" id="valTotalCash">Rs. 0.00</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="bs-scroll-box" id="cashBox">
                                <table class="bs-table"><tbody id="cashRows"></tbody></table>
                            </div>

                            {{-- Bank Balances --}}
                            <table class="bs-table">
                                <tbody>
                                    <tr style="background:#f8fafc; border-top:1px solid var(--bs-border);">
                                        <td class="font-weight-bold text-dark"><i class="fas fa-university text-primary mr-1"></i> Bank Accounts</td>
                                        <td class="amount-col text-primary" id="valTotalBank">Rs. 0.00</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="bs-scroll-box" id="bankBox">
                                <table class="bs-table"><tbody id="bankRows"></tbody></table>
                            </div>

                            {{-- Trade Receivables & Stock --}}
                            <table class="bs-table">
                                <tbody>
                                    <tr style="border-top:1px solid var(--bs-border);">
                                        <td class="item-name"><i class="fas fa-users mr-1 text-info"></i> Trade Receivables (Customer Balances)</td>
                                        <td class="amount-col text-dark" id="valTradeReceivables">Rs. 0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="item-name"><i class="fas fa-boxes mr-1 text-warning"></i> Inventory / Stock on Hand (Valuation)</td>
                                        <td class="amount-col text-dark" id="valInventoryStock">Rs. 0.00</td>
                                    </tr>
                                </tbody>
                            </table>

                            {{-- Loans & Advances --}}
                            <table class="bs-table">
                                <tbody>
                                    <tr style="background:#f8fafc; border-top:1px solid var(--bs-border);">
                                        <td class="font-weight-bold text-dark"><i class="fas fa-hand-holding-usd text-secondary mr-1"></i> Loans &amp; Advances</td>
                                        <td class="amount-col text-dark" id="valTotalLoans">Rs. 0.00</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="bs-scroll-box" id="loanBox">
                                <table class="bs-table"><tbody id="loanRows"></tbody></table>
                            </div>

                            {{-- Other Assets if any --}}
                            <div id="otherAssetsSection" style="display:none;">
                                <table class="bs-table">
                                    <tbody>
                                        <tr style="background:#f8fafc; border-top:1px solid var(--bs-border);">
                                            <td class="font-weight-bold text-dark"><i class="fas fa-layer-group text-muted mr-1"></i> Other Current Assets</td>
                                            <td class="amount-col text-dark" id="valTotalOtherAssets">Rs. 0.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="bs-scroll-box">
                                    <table class="bs-table"><tbody id="otherAssetRows"></tbody></table>
                                </div>
                            </div>

                        </div>{{-- /bs-panel-grow --}}

                        {{-- TOTAL ASSETS CALLOUT --}}
                        <div class="bs-grand-total-card assets mb-2 mt-auto">
                            <div>
                                <div class="font-weight-bold" style="font-size:13.5px;">TOTAL ASSETS</div>
                                <div class="small" style="opacity:.85; font-size:11px;">Current Assets + Liquid Funds + Inventory</div>
                            </div>
                            <div class="font-weight-bold text-success" style="font-size:18px; font-family:monospace;" id="valGrandAssets">Rs. 0.00</div>
                        </div>

                    </div>{{-- /col-lg-6 --}}

                    {{-- ── RIGHT COLUMN: LIABILITIES & EQUITY ────────── --}}
                    <div class="col-lg-6 d-flex flex-column">

                        <div class="bs-panel-grow mb-2">
                            <div class="bs-section-header" style="background:#1e3a5f;">
                                <span><i class="fas fa-balance-scale mr-1 text-info"></i> LIABILITIES &amp; EQUITY</span>
                                <span id="hdrTotalLiabilitiesEquity">Rs. 0.00</span>
                            </div>

                            {{-- 1. LIABILITIES --}}
                            <div class="bs-section-subhead">1. LIABILITIES</div>
                            <table class="bs-table">
                                <tbody>
                                    <tr>
                                        <td class="item-name"><i class="fas fa-truck mr-1 text-danger"></i> Trade Payables (Vendor / Supplier Balances)</td>
                                        <td class="amount-col text-danger" id="valTradePayables">Rs. 0.00</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td class="font-weight-bold">TOTAL LIABILITIES</td>
                                        <td class="amount-col text-danger" id="valTotalLiabilities">Rs. 0.00</td>
                                    </tr>
                                </tbody>
                            </table>

                            {{-- 2. EQUITY & CAPITAL --}}
                            <div class="bs-section-subhead mt-2">2. OWNER'S EQUITY &amp; RESERVES</div>
                            <table class="bs-table">
                                <tbody>
                                    <tr>
                                        <td class="item-name">
                                            <i class="fas fa-user-tie mr-1 text-primary"></i> Owner's Capital / General Reserves
                                            <div class="item-sub text-muted">Capital introduced &amp; prior reserves</div>
                                        </td>
                                        <td class="amount-col text-primary" id="valOwnersCapital">Rs. 0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="item-name">
                                            <i class="fas fa-chart-line mr-1 text-success" id="iconRetained"></i> Retained Earnings / Current Period Net Profit
                                            <div class="item-sub text-muted">Cumulative Profit / (Loss) up to date</div>
                                        </td>
                                        <td class="amount-col font-weight-bold" id="valRetainedEarnings">Rs. 0.00</td>
                                    </tr>
                                    <tr class="total-row" style="background:#f1f5f9;">
                                        <td class="font-weight-bold text-dark">TOTAL EQUITY</td>
                                        <td class="amount-col" style="color:#7c3aed;" id="valTotalEquity">Rs. 0.00</td>
                                    </tr>
                                </tbody>
                            </table>

                            {{-- Accounting Equality Proof --}}
                            <div class="p-2 px-3 mt-auto bg-light border-top text-center" style="font-size:11.5px;">
                                <span class="badge badge-success font-weight-normal px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Balance Sheet Tallied</span>
                                <span class="text-muted ml-2">Assets = Liabilities + Owner's Equity</span>
                            </div>

                        </div>{{-- /bs-panel-grow --}}

                        {{-- TOTAL LIABILITIES & EQUITY CALLOUT --}}
                        <div class="bs-grand-total-card liabilities mb-2 mt-auto">
                            <div>
                                <div class="font-weight-bold" style="font-size:13.5px;">TOTAL LIABILITIES &amp; EQUITY</div>
                                <div class="small" style="opacity:.85; font-size:11px;">Total Liabilities + Total Owner's Equity</div>
                            </div>
                            <div class="font-weight-bold text-primary" style="font-size:18px; font-family:monospace;" id="valGrandLiabilitiesEquity">Rs. 0.00</div>
                        </div>

                    </div>{{-- /col-lg-6 --}}

                </div>{{-- /row --}}

            </div>{{-- /reportContainer --}}

            {{-- ── 5. FORMAL A4 PRINT DOCUMENT (Shown ONLY when printing) ──── --}}
            <div id="formalPrintDoc" class="formal-print-sheet">
                <div class="print-doc-header">
                    <div class="print-company-title" id="p_branch_title">Company / Branch Name</div>
                    <div class="print-doc-title">Balance Sheet</div>
                    <div class="print-doc-period" id="p_as_of_text">As of November 18, 2026</div>
                    <div class="print-top-divider"></div>
                </div>

                <div class="print-currency-header">
                    <span>Amount Rs.</span>
                </div>

                <table class="print-statement-table">
                    <tbody id="p_statement_body">
                        <!-- Populated dynamically by renderFormalPrintDoc -->
                    </tbody>
                </table>

                <div class="print-doc-footer">
                    <div class="print-footer-info">
                        <span>Printed on: <span id="p_print_time"></span></span>
                        <span>Statement of Financial Position</span>
                        <span>ERP System &mdash; Financial Statement</span>
                    </div>
                    <div class="print-sign-row">
                        <div class="print-sign-box">Prepared By</div>
                        <div class="print-sign-box">Accountant</div>
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
                        <span style="font-weight:700; font-size:14px; letter-spacing:.02em;">Balance Sheet &mdash; Print Document Preview</span>
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

        </div>{{-- /container --}}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.select2) {
        $('.select2').select2({ width: '100%' });
        $('.select2').on('change', function() {
            loadReport();
        });
    }

    document.getElementById('btnSearch').addEventListener('click', loadReport);
    document.getElementById('as_of_date').addEventListener('change', loadReport);

    // Auto load on initial view
    loadReport();

    // Remove document title during printing so browsers do not print default header text
    window.addEventListener('beforeprint', function () {
        window._savedDocTitle = document.title;
        document.title = '';
    });
    window.addEventListener('afterprint', function () {
        document.title = window._savedDocTitle || 'ERP System';
    });
});

function fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function loadReport() {
    const params = new URLSearchParams();
    const branchEl = document.getElementById('branch_id');
    if (branchEl && branchEl.value) {
        params.append('branch_id', branchEl.value);
    }
    params.append('as_of_date', document.getElementById('as_of_date').value);

    document.getElementById('loader').style.display          = 'block';
    document.getElementById('placeholder').style.display     = 'none';
    document.getElementById('reportContainer').style.display = 'none';
    document.getElementById('summaryCards').style.display    = 'none';

    fetch('{{ route("report.balance_sheet.fetch") }}?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loader').style.display = 'none';
        renderBalanceSheet(data);
    })
    .catch(err => {
        document.getElementById('loader').style.display = 'none';
        alert('Error: ' + err.message);
    });
}

function renderBalanceSheet(d) {
    // ── Metadata ───────────────────────────────────────────
    document.getElementById('lblBranch').textContent = d.branch_label || 'All Branches';
    document.getElementById('lblAsOfDate').textContent = 'As of Date: ' + (d.as_of_date || 'Today');

    // ── 1. ASSETS ──────────────────────────────────────────

    // Cash List
    document.getElementById('valTotalCash').textContent = 'Rs. ' + fmt(d.total_cash);
    const cashTbody = document.getElementById('cashRows');
    if ((d.cash_list || []).length > 0) {
        let html = '';
        d.cash_list.forEach(c => {
            html += `<tr>
                <td class="item-sub">${c.title} <span class="text-muted small">(${c.code || '—'})</span></td>
                <td class="amount-col text-success">Rs. ${fmt(c.balance)}</td>
            </tr>`;
        });
        cashTbody.innerHTML = html;
        document.getElementById('cashBox').style.display = 'block';
    } else {
        cashTbody.innerHTML = '';
        document.getElementById('cashBox').style.display = 'none';
    }

    // Bank List
    document.getElementById('valTotalBank').textContent = 'Rs. ' + fmt(d.total_bank);
    const bankTbody = document.getElementById('bankRows');
    if ((d.bank_list || []).length > 0) {
        let html = '';
        d.bank_list.forEach(b => {
            html += `<tr>
                <td class="item-sub">${b.title} <span class="text-muted small">(${b.code || '—'})</span></td>
                <td class="amount-col text-primary">Rs. ${fmt(b.balance)}</td>
            </tr>`;
        });
        bankTbody.innerHTML = html;
        document.getElementById('bankBox').style.display = 'block';
    } else {
        bankTbody.innerHTML = '';
        document.getElementById('bankBox').style.display = 'none';
    }

    // Trade Receivables & Stock
    document.getElementById('valTradeReceivables').textContent = 'Rs. ' + fmt(d.total_receivables);
    document.getElementById('valInventoryStock').textContent   = 'Rs. ' + fmt(d.total_stock);

    // Loans List
    document.getElementById('valTotalLoans').textContent = 'Rs. ' + fmt(d.total_loans);
    const loanTbody = document.getElementById('loanRows');
    if ((d.loan_list || []).length > 0) {
        let html = '';
        d.loan_list.forEach(l => {
            html += `<tr>
                <td class="item-sub">${l.title} <span class="text-muted small">(${l.code || '—'})</span></td>
                <td class="amount-col text-dark">Rs. ${fmt(l.balance)}</td>
            </tr>`;
        });
        loanTbody.innerHTML = html;
        document.getElementById('loanBox').style.display = 'block';
    } else {
        loanTbody.innerHTML = '';
        document.getElementById('loanBox').style.display = 'none';
    }

    // Other Assets List
    const otherAssets = d.other_assets_list || [];
    if (otherAssets.length > 0) {
        document.getElementById('valTotalOtherAssets').textContent = 'Rs. ' + fmt(d.total_other_assets);
        let html = '';
        otherAssets.forEach(o => {
            html += `<tr>
                <td class="item-sub">${o.title} <span class="text-muted small">(${o.head || '—'})</span></td>
                <td class="amount-col text-dark">Rs. ${fmt(o.balance)}</td>
            </tr>`;
        });
        document.getElementById('otherAssetRows').innerHTML = html;
        document.getElementById('otherAssetsSection').style.display = 'block';
    } else {
        document.getElementById('otherAssetsSection').style.display = 'none';
    }

    // Total Assets
    document.getElementById('hdrTotalAssets').textContent = 'Rs. ' + fmt(d.total_assets);
    document.getElementById('valGrandAssets').textContent = 'Rs. ' + fmt(d.total_assets);

    // ── 2. LIABILITIES ─────────────────────────────────────
    document.getElementById('valTradePayables').textContent    = 'Rs. ' + fmt(d.total_payables);
    document.getElementById('valTotalLiabilities').textContent = 'Rs. ' + fmt(d.total_liabilities);

    // ── 3. EQUITY & RESERVES ───────────────────────────────
    document.getElementById('valOwnersCapital').textContent    = 'Rs. ' + fmt(d.owners_capital);

    const retVal = d.retained_earnings || 0;
    const retEl  = document.getElementById('valRetainedEarnings');
    const retIco = document.getElementById('iconRetained');
    if (retVal >= 0) {
        retEl.className   = 'amount-col font-weight-bold text-success';
        retEl.textContent = 'Rs. ' + fmt(retVal);
        if (retIco) retIco.className = 'fas fa-chart-line mr-1 text-success';
    } else {
        retEl.className   = 'amount-col font-weight-bold text-danger';
        retEl.textContent = '- Rs. ' + fmt(Math.abs(retVal));
        if (retIco) retIco.className = 'fas fa-arrow-down mr-1 text-danger';
    }

    document.getElementById('valTotalEquity').textContent               = 'Rs. ' + fmt(d.total_equity);
    document.getElementById('hdrTotalLiabilitiesEquity').textContent    = 'Rs. ' + fmt(d.total_liabilities_equity);
    document.getElementById('valGrandLiabilitiesEquity').textContent   = 'Rs. ' + fmt(d.total_liabilities_equity);

    // ── KPI Cards ──────────────────────────────────────────
    document.getElementById('kpiTotalAssets').textContent      = 'Rs. ' + fmt(d.total_assets);
    document.getElementById('kpiTotalLiabilities').textContent = 'Rs. ' + fmt(d.total_liabilities);
    document.getElementById('kpiWorkingCapital').textContent   = 'Rs. ' + fmt(d.working_capital);
    document.getElementById('kpiTotalEquity').textContent      = 'Rs. ' + fmt(d.total_equity);

    document.getElementById('summaryCards').style.display    = 'grid';
    document.getElementById('reportContainer').style.display = 'block';

    // ── Build Formal A4 Print Sheet ────────────────────────
    renderFormalPrintDoc(d);
}

function fmtPrint(n) {
    const val = Math.round(parseFloat(n || 0));
    const absFmt = Math.abs(val).toLocaleString('en-PK');
    return val < 0 ? `(${absFmt})` : absFmt;
}
function fmtPrintNeg(n) {
    const val = Math.abs(Math.round(parseFloat(n || 0)));
    return val === 0 ? '0' : `(${val.toLocaleString('en-PK')})`;
}

function renderFormalPrintDoc(d) {
    const branchName = d.branch_label || 'All Branches';
    document.getElementById('p_branch_title').textContent = branchName;

    // As of Date
    const asOfInput = document.getElementById('as_of_date')?.value || d.as_of_date;
    const dt = asOfInput ? new Date(asOfInput + 'T00:00:00') : new Date();
    const dateFormatted = dt.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('p_as_of_text').textContent = `As of ${dateFormatted}`;

    // Print timestamp
    const now = new Date();
    document.getElementById('p_print_time').textContent = now.toLocaleDateString('en-US', { day:'2-digit', month:'short', year:'numeric' }) + ' ' + now.toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });

    let html = '';

    // ══════════════════════════════════════════════════════════
    //  1. ASSETS
    // ══════════════════════════════════════════════════════════
    html += `<tr class="p-sec-head"><td colspan="2">1. ASSETS</td></tr>`;
    
    // Cash in Hand
    html += `<tr class="p-sub-item"><td class="p-desc">Cash in Hand</td><td class="p-val">${fmtPrint(d.total_cash)}</td></tr>`;

    // Bank Accounts (Single summary total line)
    html += `<tr class="p-sub-item"><td class="p-desc">Bank Accounts</td><td class="p-val">${fmtPrint(d.total_bank)}</td></tr>`;

    // Trade Receivables
    html += `<tr class="p-sub-item"><td class="p-desc">Trade Receivables (Customer Balances)</td><td class="p-val">${fmtPrint(d.total_receivables)}</td></tr>`;

    // Inventory
    html += `<tr class="p-sub-item"><td class="p-desc">Inventory / Stock on Hand (Valuation)</td><td class="p-val">${fmtPrint(d.total_stock)}</td></tr>`;

    // Loans & Advances
    if (parseFloat(d.total_loans || 0) !== 0) {
        html += `<tr class="p-sub-item"><td class="p-desc">Loans &amp; Advances</td><td class="p-val">${fmtPrint(d.total_loans)}</td></tr>`;
    }

    // Other Assets
    if (parseFloat(d.total_other_assets || 0) !== 0) {
        html += `<tr class="p-sub-item"><td class="p-desc">Other Current Assets</td><td class="p-val">${fmtPrint(d.total_other_assets)}</td></tr>`;
    }

    // TOTAL ASSETS
    html += `<tr class="p-grandtotal">
        <td class="p-desc">TOTAL ASSETS</td>
        <td class="p-val">${fmtPrint(d.total_assets)}</td>
    </tr>`;

    // ══════════════════════════════════════════════════════════
    //  2. LIABILITIES & EQUITY
    // ══════════════════════════════════════════════════════════
    html += `<tr class="p-sec-head" style="padding-top:10px;"><td colspan="2">2. LIABILITIES &amp; OWNER'S EQUITY</td></tr>`;

    // Liabilities
    html += `<tr class="p-sub-item"><td class="p-desc" style="font-weight:700;">Liabilities:</td><td class="p-val"></td></tr>`;
    html += `<tr class="p-sub-item"><td class="p-desc" style="padding-left:30px;">Trade Payables (Vendor / Supplier Balances)</td><td class="p-val">${fmtPrint(d.total_payables)}</td></tr>`;
    html += `<tr class="p-subtotal">
        <td class="p-desc">Total Liabilities</td>
        <td class="p-val">${fmtPrint(d.total_liabilities)}</td>
    </tr>`;

    // Owner's Equity & Reserves
    html += `<tr class="p-sub-item" style="padding-top:4px;"><td class="p-desc" style="font-weight:700;">Owner's Equity &amp; Reserves:</td><td class="p-val"></td></tr>`;
    html += `<tr class="p-sub-item"><td class="p-desc" style="padding-left:30px;">Owner's Capital / General Reserves</td><td class="p-val">${fmtPrint(d.owners_capital)}</td></tr>`;
    html += `<tr class="p-sub-item"><td class="p-desc" style="padding-left:30px;">Current Period Net Profit / Retained Earnings</td><td class="p-val">${fmtPrint(d.retained_earnings)}</td></tr>`;
    html += `<tr class="p-subtotal">
        <td class="p-desc">Total Owner's Equity</td>
        <td class="p-val">${fmtPrint(d.total_equity)}</td>
    </tr>`;

    // TOTAL LIABILITIES & EQUITY
    html += `<tr class="p-grandtotal">
        <td class="p-desc">TOTAL LIABILITIES &amp; OWNER'S EQUITY</td>
        <td class="p-val">${fmtPrint(d.total_liabilities_equity)}</td>
    </tr>`;

    document.getElementById('p_statement_body').innerHTML = html;

    // If preview modal is currently open, sync it live
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

    const tbody = formalDoc.querySelector('tbody');
    if (tbody && tbody.children.length === 0) {
        alert('Report data is still loading or empty. Please wait a moment or click Generate.');
        return;
    }

    const reportHtml = formalDoc.innerHTML;
    const branchTitle = document.getElementById('p_branch_title')?.textContent || 'Company';
    const docTitle = 'Balance Sheet';
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
            max-width: 820px;
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
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            padding: 30px 40px;
        }

        .print-doc-header { margin-bottom: 6px; }
        .print-company-title { font-size: 13pt; font-weight: 800; color: #000; margin-bottom: 2px; text-transform: uppercase; letter-spacing: .02em; }
        .print-doc-title { font-size: 18pt; font-weight: 900; color: #000; margin-bottom: 2px; }
        .print-doc-period { font-size: 10pt; font-weight: 600; color: #334155; }
        .print-top-divider { border-bottom: 2px solid #000; margin-top: 6px; margin-bottom: 10px; }

        .print-currency-header { text-align: right; margin-bottom: 6px; }
        .print-currency-header span { font-size: 9.5pt; font-style: italic; text-decoration: underline; font-weight: 700; color: #000; }

        .print-statement-table { width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 16px; color: #000; }
        .print-statement-table td { padding: 4px 4px; vertical-align: middle; color: #000; }
        .print-statement-table tr.p-sec-head td { font-weight: 900; font-size: 11pt; padding-top: 12px; padding-bottom: 4px; color: #000; letter-spacing: .02em; }
        .print-statement-table tr.p-sub-item td.p-desc { padding-left: 16px; color: #000; font-weight: 600; font-size: 10pt; }
        .print-statement-table tr.p-sub-item td.p-val { text-align: right; font-variant-numeric: tabular-nums; font-size: 10pt; font-weight: 700; color: #000; width: 170px; }
        .print-statement-table tr.p-subtotal td { font-weight: 800; padding-top: 6px; padding-bottom: 6px; font-size: 10pt; color: #000; }
        .print-statement-table tr.p-subtotal td.p-desc { text-align: right; padding-right: 16px; font-weight: 800; }
        .print-statement-table tr.p-subtotal td.p-val { text-align: right; font-variant-numeric: tabular-nums; font-size: 10pt; font-weight: 800; border-top: 1.5px solid #000; color: #000; width: 170px; }
        .print-statement-table tr.p-grandtotal td { font-weight: 900; font-size: 11pt; padding-top: 10px; padding-bottom: 10px; color: #000; }
        .print-statement-table tr.p-grandtotal td.p-desc { text-align: right; padding-right: 16px; font-weight: 900; }
        .print-statement-table tr.p-grandtotal td.p-val { text-align: right; font-variant-numeric: tabular-nums; font-size: 11pt; font-weight: 900; border-top: 1.5px solid #000; border-bottom: 3.5px double #000; color: #000; width: 170px; }

        .print-doc-footer { margin-top: 25px; font-size: 9pt; color: #000; }
        .print-footer-info { display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 6px; margin-bottom: 35px; font-weight: 600; color: #000; font-size: 8.5pt; }
        .print-sign-row { display: flex; justify-content: space-between; padding: 0 20px; }
        .print-sign-box { border-top: 1.5px dashed #000; width: 150px; text-align: center; padding-top: 6px; font-size: 9pt; font-weight: 700; color: #000; }

        @media print {
            body { background: #fff !important; padding: 0 !important; }
            .action-toolbar, .no-print { display: none !important; }
            .normal-page-card { border: none !important; box-shadow: none !important; padding: 0 !important; max-width: 100% !important; margin: 0 !important; }
        }
    </style>
</head>
<body>
    <div class="action-toolbar no-print">
        <div class="report-badge">
            <i class="fas fa-landmark"></i>
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

    // 2. Fallback if popups are blocked by browser: show in-page full normal card
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

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closePrintPreview();
    }
});

function exportCSV() {
    let csv = 'BALANCE SHEET (STATEMENT OF FINANCIAL POSITION)\n';
    csv += 'Branch: ' + document.getElementById('lblBranch').textContent + '\n';
    csv += document.getElementById('lblAsOfDate').textContent + '\n\n';

    csv += 'Category,Particulars,Amount (PKR)\n';
    csv += 'Assets,Cash in Hand,' + document.getElementById('valTotalCash').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Assets,Bank Accounts,' + document.getElementById('valTotalBank').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Assets,Trade Receivables (Customers),' + document.getElementById('valTradeReceivables').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Assets,Inventory / Stock on Hand,' + document.getElementById('valInventoryStock').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Assets,Loans & Advances,' + document.getElementById('valTotalLoans').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Assets,TOTAL ASSETS,' + document.getElementById('valGrandAssets').textContent.replace(/[Rs.\s,]/g, '') + '\n\n';

    csv += 'Liabilities,Trade Payables (Vendors),' + document.getElementById('valTotalLiabilities').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Equity,Owner\'s Capital,' + document.getElementById('valOwnersCapital').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Equity,Retained Earnings,' + document.getElementById('valRetainedEarnings').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Equity,TOTAL EQUITY,' + document.getElementById('valTotalEquity').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Summary,TOTAL LIABILITIES & EQUITY,' + document.getElementById('valGrandLiabilitiesEquity').textContent.replace(/[Rs.\s,]/g, '') + '\n';

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'balance_sheet_' + (document.getElementById('as_of_date').value || 'report') + '.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection

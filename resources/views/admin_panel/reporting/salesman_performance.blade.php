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

    #performanceTable th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 8px;
        border: 1px solid #1e3a5f;
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">
            
            {{-- 1. Corporate Header Bar --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Salesman Performance Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-chart-line mr-1" style="color: var(--coa-gold);"></i> Monthly productivity, commission & sales target analysis &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" onclick="openPrintPreview()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.4);" title="Clean Full-Page Preview">
                        <i class="fas fa-eye mr-1"></i> Preview
                    </button>
                    <button type="button" onclick="shareOnWhatsApp()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(37, 211, 102, 0.2); border-color: #25D366; color: #25D366;">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button type="button" onclick="showExportOptions()" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-file-export mr-1 text-primary"></i> Export
                    </button>
                </div>
            </div>

            {{-- 2. Filter Card --}}
            <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form id="performanceFilterForm" class="row g-2 align-items-end mb-0">
                        @if($isSuper)
                        <div class="col-md-2">
                            <label class="f-label">Select Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control form-control-sm select2 branch-trigger">
                                <option value="">-- All Branches --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->branch_name ?? $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="branch_id" id="branch_id" value="{{ $userBranchId }}">
                        @endif

                        <div class="col-md-{{ $isSuper ? '3' : '3' }}">
                            <label class="f-label">Select Salesman</label>
                            <select name="salesman_id" id="salesman_id" class="form-control form-control-sm select2">
                                <option value="">-- All (Summary) --</option>
                                <option value="direct">Direct Sale (No Salesman)</option>
                                @foreach($salesmen as $sm)
                                    <option value="{{ $sm->id }}">{{ $sm->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="f-label">Report View / Mode</label>
                            <select name="report_type" id="report_type" class="form-control form-control-sm select2">
                                <option value="invoice_wise" selected>Invoice Wise</option>
                                <option value="item_wise">Item Wise (Product Detail)</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="f-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ date('Y-m-01') }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>

                        <div class="col-md-{{ $isSuper ? '1' : '1' }}">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Analyze
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. REPORT TABLE --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="reportContent">
                <div class="card-body p-3">
                    
                    {{-- PDF HEADER (HIDDEN ON SCREEN) --}}
                    <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #0f1f38; padding-bottom:10px;">
                        <h2 style="margin:0; color:#0f1f38; text-transform:uppercase; letter-spacing:1px;">SALESMAN PERFORMANCE REPORT</h2>
                        <p style="margin:5px 0; font-size:14px; color:#333;">
                            <strong>Period:</strong> <span id="pdfPeriod"></span>
                        </p>
                        <p style="margin:0; font-size:12px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:40px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Aggregating performance data...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="performanceTable" class="table table-bordered mb-0" style="font-size:12.5px; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%;">#</th>
                                    <th>Salesman Name</th>
                                    <th style="width: 18%;">Branch</th>
                                    <th style="width: 12%;">Month</th>
                                    <th class="text-end" style="width: 14%;">Total Invoices</th>
                                    <th class="text-end" style="width: 18%;">Total Sales (PKR)</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody"></tbody>
                            <tfoot id="tableFooter" style="display:none;">
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                                    <td colspan="4" class="text-end font-weight-bold" style="font-family: sans-serif;">Totals:</td>
                                    <td class="text-end font-weight-bold text-dark" id="footerInvoices">0</td>
                                    <td class="text-end font-weight-bold text-success" id="footerTotalAmount">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 4. DETAILED LEDGER SECTION (Show only when salesman selected) --}}
            <div class="card shadow-sm border-0 mt-3 d-none" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="ledgerContent">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 font-weight-bold" style="color: var(--coa-navy);">
                        <i class="fas fa-list-alt mr-1" style="color: var(--coa-gold);"></i>
                        Detailed Sales Ledger: <span id="selectedSalesmanName" class="text-dark"></span>
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div id="ledgerLoader" style="display:none;text-align:center;padding:20px;">
                        <div class="spinner-border text-info" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Loading transaction history...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="ledgerTable" class="table table-hover table-bordered mb-0" style="font-size:12px;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 12%;">Date</th>
                                    <th style="width: 16%;">Invoice #</th>
                                    <th>Customer</th>
                                    <th style="width: 18%;">Branch</th>
                                    <th class="text-end" style="width: 16%;">Amount (PKR)</th>
                                </tr>
                            </thead>
                            <tbody id="ledgerBody"></tbody>
                            <tfoot>
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                                    <td colspan="4" class="text-end font-weight-bold" style="font-family: sans-serif;">Total Amount:</td>
                                    <td class="text-end text-success font-weight-bold" id="ledgerTotal">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 5. ITEM-WISE SALES & PROFIT SECTION (Image 2 style) --}}
            <div class="card shadow-sm border-0 mt-3 d-none" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="itemContent">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 font-weight-bold" style="color: var(--coa-navy);">
                        <i class="fas fa-boxes mr-1" style="color: var(--coa-gold);"></i>
                        Item-wise Sales &amp; Profit Report: <span id="itemSalesmanName" class="text-dark">All Salesmen</span>
                    </h6>
                    <span class="badge badge-light border text-dark font-weight-bold" id="itemCountBadge">0 Products</span>
                </div>
                <div class="card-body p-3">
                    <div id="itemLoader" style="display:none;text-align:center;padding:25px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Loading item-wise product sales &amp; profit...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="itemTable" class="table table-hover table-bordered mb-0" style="font-size:12px; border-collapse:collapse;">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center" style="width: 4%;">#</th>
                                    <th style="width: 14%;">Item Code</th>
                                    <th>Item Name</th>
                                    <th class="text-end" style="width: 10%;">Quantity</th>
                                    <th class="text-end" style="width: 14%;">Sale Price</th>
                                    <th class="text-end" style="width: 14%;">Purchasing Rate</th>
                                    <th class="text-end" style="width: 14%;">Total</th>
                                    <th class="text-end" style="width: 14%;">Net Profit</th>
                                </tr>
                            </thead>
                            <tbody id="itemBody"></tbody>
                            <tfoot id="itemFooter" style="display:none;">
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                                    <td colspan="3" class="text-end font-weight-bold" style="font-family: sans-serif;">Totals:</td>
                                    <td class="text-end font-weight-bold text-dark" id="itemTotalQty">0</td>
                                    <td class="text-end font-weight-bold text-primary" id="itemTotalSalePrice">0.00</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end font-weight-bold text-secondary" id="itemTotalCost">0.00</td>
                                    <td class="text-end font-weight-bold" id="itemTotalProfit">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── Formal Clean Print Document (Used for Preview & Print) ── --}}
            <div id="formalPrintDoc" style="display:none;">
                <div class="print-doc-header">
                    <div class="print-company-title" id="p_branch_title">ALL BRANCHES</div>
                    <div class="print-doc-title" id="p_doc_title">Salesman Performance Report</div>
                    <div class="print-doc-period" id="p_doc_period">Monthly productivity, commission &amp; sales target analysis</div>
                    <div class="print-top-divider"></div>
                </div>

                <!-- Table Container populated dynamically -->
                <div id="p_table_container"></div>

                <div class="print-doc-footer">
                    <div class="print-footer-info">
                        <span>Printed on: <span id="p_print_time"></span></span>
                        <span id="p_footer_doc_desc">Salesman Performance &amp; Productivity Summary</span>
                        <span>ERP System &mdash; Sales Management</span>
                    </div>
                    <div class="print-sign-row">
                        <div class="print-sign-box">Prepared By</div>
                        <div class="print-sign-box">Sales Manager</div>
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
                        <span style="font-weight:700; font-size:14px; letter-spacing:.02em;">Salesman Performance Report &mdash; Print Document Preview</span>
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
@endsection

@section('css')
<style>
    #performanceTable { border: 2px solid #1a1a2e; }
    #performanceTable th { 
        vertical-align: middle; 
        border: 1px solid #444; 
        text-transform: uppercase; 
        letter-spacing: 0.5px;
        font-weight: 700;
    }
    #performanceTable td { 
        vertical-align: middle; 
        border: 1px solid #ccc; 
        padding: 12px 10px; 
        font-weight: 500;
        color: #1a1a2e;
    }
    #performanceTable tbody tr:nth-child(even) { background-color: #f9f9f9; }
    #performanceTable tbody tr:hover { background-color: #f0f4ff !important; transition: 0.2s; }
    
    .salesman-name { font-weight: 700; color: #0066cc; font-size: 14px; }

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
    .print-tb-table th { border-top: 1.5px solid #000 !important; border-bottom: 1.5px solid #000 !important; padding: 8px 6px !important; background: #f8fafc !important; font-weight: 800 !important; color: #000 !important; font-size: 9.5pt !important; text-transform: uppercase !important; vertical-align: middle !important; line-height: 1.25 !important; }
    .print-tb-table td { border-bottom: 1px solid #cbd5e1 !important; padding: 7px 6px !important; vertical-align: middle !important; color: #000 !important; font-size: 10pt !important; line-height: 1.3 !important; overflow: hidden; word-wrap: break-word; }
    .print-tb-table tr { page-break-inside: avoid !important; }
    .print-tb-table .num-cell { text-align: right !important; font-variant-numeric: tabular-nums !important; font-feature-settings: "tnum" !important; white-space: nowrap !important; font-size: 10.5pt !important; letter-spacing: -0.01em !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }
    .print-tb-table tr.p-tfoot td { border-top: 1.5px solid #000 !important; border-bottom: 3px double #000 !important; font-weight: 800 !important; padding: 9px 6px !important; background: #fff !important; font-size: 11pt !important; color: #000 !important; }

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
        width: 100%; max-width: 950px;
        height: max-content; box-sizing: border-box;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 6mm 6mm 6mm 6mm !important;
        }
        body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
        .main-content { padding: 0 !important; margin: 0 !important; }
        .rpt-wrapper, .navbar, .sidebar, .rpt-header-bar, #performanceFilterForm, #reportContent, #ledgerContent, #itemContent, .no-print, .btn, .card {
            display: none !important;
        }
        #formalPrintDoc {
            display: block !important;
            padding: 0 !important; margin: 0 !important;
            width: 100% !important; max-width: 100% !important;
        }
        .print-tb-table { width: 100% !important; max-width: 100% !important; table-layout: fixed !important; }
        .print-tb-table th, .print-tb-table td { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: '-- Select Option --'
        });

        function n(v) { return parseFloat(v) || 0; }
        function fmt(v) { return n(v).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

        window.currentSummaryData = [];
        window.currentLedgerData = [];
        window.currentItemData = [];

        $('#report_type').on('change', function() {
            fetchReport();
        });

        function renderRows(rows) {
            window.currentSummaryData = rows || [];
            let tableContent = '';
            let grandInvoices = 0;
            let grandAmount = 0;

            if (rows.length === 0) {
                $('#reportBody').html('<tr><td colspan="6" class="text-center py-4 text-muted">No performance data found for this criteria</td></tr>');
                $('#tableFooter').hide();
                if (typeof renderFormalPrintDoc === 'function') renderFormalPrintDoc();
                return;
            }

            rows.forEach(function(r, idx) {
                tableContent += `<tr>
                    <td class="text-center">${idx + 1}</td>
                    <td><div class="salesman-name">${r.salesman_name || 'N/A'}</div></td>
                    <td>${r.branch_name || '-'}</td>
                    <td>${r.month_year}</td>
                    <td class="text-end fw-bold">${r.total_invoices}</td>
                    <td class="text-end fw-bold text-success" style="background:#f0fff0; font-size:15px;">${fmt(r.total_amount)}</td>
                </tr>`;

                grandInvoices += n(r.total_invoices);
                grandAmount += n(r.total_amount);
            });

            // Footer Totals
            $('#footerInvoices').text(grandInvoices);
            $('#footerTotalAmount').text(fmt(grandAmount));
            $('#tableFooter').show();

            $('#reportBody').html(tableContent);
            if (typeof renderFormalPrintDoc === 'function') renderFormalPrintDoc();
        }

        $('#btnSearch').on('click', function() {
            fetchReport();
        });

        // Auto-fetch on page load
        fetchReport();

        function fetchReport() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var branch_id = $('#branch_id').val() || '';
            var salesman_id = $('#salesman_id').val() || '';
            var report_type = $('#report_type').val() || 'invoice_wise';

            // Update PDF Header Info for Export
            let periodText = start_date + ' to ' + end_date;
            if (branch_id) {
                periodText += ' | Branch: ' + $('#branch_id option:selected').text();
            }
            $('#pdfPeriod').text(periodText);

            // ── MODE 1: ITEM WISE (Product Breakdown with Sale Price, Purchase Rate & Net Profit) ──
            if (report_type === 'item_wise') {
                $('#reportContent').hide();
                $('#ledgerContent').hide();
                $('#itemContent').removeClass('d-none').show();
                fetchSalesmanItems(salesman_id, start_date, end_date, branch_id);
                return;
            }

            // ── MODE 2: INVOICE WISE (Ledger or Summary) ──
            $('#itemContent').hide();

            // If a specific salesman is selected, show their ledger instead of the summary
            if (salesman_id) {
                $('#reportContent').hide();
                $('#ledgerContent').removeClass('d-none').show();
                fetchSalesmanLedger(salesman_id, start_date, end_date);
                return;
            } else {
                $('#reportContent').show();
                $('#ledgerContent').hide();
            }

            $('#loader').show();

            $.ajax({
                url: "{{ route('report.salesman.performance.fetch') }}",
                type: "GET",
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    branch_id: branch_id
                },
                success: function(response) {
                    $('#loader').hide();
                    renderRows(response);
                },
                error: function(xhr) {
                    $('#loader').hide();
                    let msg = 'Error fetching performance report';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        msg += ': ' + xhr.responseJSON.error;
                    }
                    alert(msg);
                }
            });
        }

        function fetchSalesmanLedger(salesman_id, start_date, end_date) {
            $('#selectedSalesmanName').text($('#salesman_id option:selected').text());
            $('#ledgerLoader').show();
            $('#ledgerBody').empty();

            $.ajax({
                url: "{{ route('report.salesman.ledger.fetch') }}",
                type: "GET",
                data: {
                    salesman_id: salesman_id,
                    start_date: start_date,
                    end_date: end_date
                },
                success: function(response) {
                    $('#ledgerLoader').hide();
                    window.currentLedgerData = response || [];
                    let html = '';
                    let total = 0;

                    if (response.length === 0) {
                        html = '<tr><td colspan="5" class="text-center py-3 text-muted">No transactions found for this salesman</td></tr>';
                    } else {
                        response.forEach(function(item) {
                            html += `<tr>
                                <td>${item.date}</td>
                                <td class="fw-bold text-primary">${item.invoice_no}</td>
                                <td>${item.customer}</td>
                                <td>${item.branch}</td>
                                <td class="text-end fw-bold">${fmt(item.total_amount)}</td>
                            </tr>`;
                            total += n(item.total_amount);
                        });
                    }

                    $('#ledgerBody').html(html);
                    $('#ledgerTotal').text(fmt(total));
                    if (typeof renderFormalPrintDoc === 'function') renderFormalPrintDoc();
                },
                error: function(xhr) {
                    $('#ledgerLoader').hide();
                    alert('Error fetching salesman ledger');
                }
            });
        }

        function fetchSalesmanItems(salesman_id, start_date, end_date, branch_id) {
            let smName = $('#salesman_id').val() ? $('#salesman_id option:selected').text() : 'All Salesmen';
            $('#itemSalesmanName').text(smName);
            $('#itemLoader').show();
            $('#itemBody').empty();
            $('#itemFooter').hide();

            $.ajax({
                url: "{{ route('report.salesman.items.fetch') }}",
                type: "GET",
                data: {
                    salesman_id: salesman_id,
                    start_date: start_date,
                    end_date: end_date,
                    branch_id: branch_id
                },
                success: function(response) {
                    $('#itemLoader').hide();
                    window.currentItemData = response || [];
                    let html = '';
                    let grandQty = 0;
                    let grandSalePrice = 0;
                    let grandCost = 0;
                    let grandProfit = 0;

                    if (!response || response.length === 0) {
                        html = '<tr><td colspan="8" class="text-center py-4 text-muted">No item sales found for this criteria</td></tr>';
                        $('#itemCountBadge').text('0 Products');
                        $('#itemFooter').hide();
                    } else {
                        $('#itemCountBadge').text(response.length + ' Products');
                        response.forEach(function(item, idx) {
                            let qty = n(item.quantity);
                            let salePrice = n(item.sale_price);
                            let purchRate = n(item.purchasing_rate);
                            let totalCost = n(item.total_cost);
                            let netProfit = n(item.net_profit);

                            grandQty += qty;
                            grandSalePrice += salePrice;
                            grandCost += totalCost;
                            grandProfit += netProfit;

                            let profitClass = netProfit >= 0 ? 'text-success' : 'text-danger';
                            let profitBg = netProfit >= 0 ? '#f0fff4' : '#fff5f5';
                            let profitFormatted = (netProfit < 0 ? '-' : '') + fmt(Math.abs(netProfit));

                            let unitSubtext = qty > 1 ? `<div class="small text-muted" style="font-size:10px; font-weight:normal;">@ ${fmt(salePrice / qty)} / unit</div>` : '';

                            html += `<tr>
                                <td class="text-center text-muted">${idx + 1}</td>
                                <td class="fw-bold">${item.item_code || '-'}</td>
                                <td class="fw-bold text-dark">${item.item_name || 'N/A'}</td>
                                <td class="text-end fw-bold">${fmt(qty).replace('.00', '')}</td>
                                <td class="text-end fw-bold text-primary">${fmt(salePrice)}${unitSubtext}</td>
                                <td class="text-end">${fmt(purchRate)}</td>
                                <td class="text-end fw-bold text-secondary">${fmt(totalCost)}</td>
                                <td class="text-end fw-bold ${profitClass}" style="background:${profitBg}; font-size:13px;">${profitFormatted}</td>
                            </tr>`;
                        });

                        $('#itemTotalQty').text(fmt(grandQty).replace('.00', ''));
                        $('#itemTotalSalePrice').text(fmt(grandSalePrice));
                        $('#itemTotalCost').text(fmt(grandCost));

                        let totalProfitFormatted = (grandProfit < 0 ? '-' : '') + fmt(Math.abs(grandProfit));
                        let totalProfitClass = grandProfit >= 0 ? 'text-success' : 'text-danger';
                        $('#itemTotalProfit').text(totalProfitFormatted).removeClass('text-success text-danger').addClass(totalProfitClass);

                        $('#itemFooter').show();
                    }

                    $('#itemBody').html(html);
                    if (typeof renderFormalPrintDoc === 'function') renderFormalPrintDoc();
                },
                error: function(xhr) {
                    $('#itemLoader').hide();
                    alert('Error fetching item-wise salesman performance');
                }
            });
        }

        // ✅ NEW: Branch Change Trigger for Salesman Filtering
        $(document).on('change', '.branch-trigger', function() {
            const branchId = $(this).val();
            loadSalesmenByBranch(branchId);
        });

        function loadSalesmenByBranch(branchId) {
            const $smSelect = $('#salesman_id');
            $smSelect.html('<option selected disabled>Loading salesmen...</option>');

            $.get("{{ route('report.salesmen.byBranch') }}", { branch_id: branchId }, function(data) {
                let html = '<option value="">-- All (Summary) --</option>';
                html += '<option value="direct">Direct Sale (No Salesman)</option>';
                
                if (data.length > 0) {
                    data.forEach(sm => {
                        html += `<option value="${sm.id}">${sm.name}</option>`;
                    });
                }
                
                $smSelect.html(html).trigger('change');
            });
        }

        /* ---------- Export Options ---------- */
        window.showExportOptions = function() {
            Swal.fire({
                title: 'Export Performance Report',
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

            const reportType = $('#report_type').val() || 'invoice_wise';
            const isLedger = !$('#ledgerContent').hasClass('d-none');
            let element;
            let filePrefix = 'Salesman_Performance_';

            if (reportType === 'item_wise') {
                element = document.getElementById('itemContent');
                filePrefix = 'Salesman_Item_Profit_';
            } else if (isLedger) {
                element = document.getElementById('ledgerContent');
                filePrefix = 'Salesman_Ledger_';
            } else {
                element = document.getElementById('reportContent');
            }
            
            // Temporarily show PDF header if needed
            $('#pdfHeader').show(); 

            var opt = {
                margin:       0.2,
                filename:     filePrefix + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: reportType === 'item_wise' ? 'landscape' : 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                $('#pdfHeader').hide(); 
                Swal.close();
            });
        };

        window.exportCSV = function () {
            let csv = [];
            let headers = [];
            
            const reportType = $('#report_type').val() || 'invoice_wise';
            const isLedger = !$('#ledgerContent').hasClass('d-none');
            let tableSelector = "#performanceTable";
            let filePrefix = "salesman_performance_";

            if (reportType === 'item_wise') {
                tableSelector = "#itemTable";
                filePrefix = "salesman_item_profit_";
            } else if (isLedger) {
                tableSelector = "#ledgerTable";
                filePrefix = "salesman_ledger_";
            }
            
            $(tableSelector + " thead th").each(function() {
                headers.push('"' + $(this).text().trim() + '"');
            });
            csv.push(headers.join(","));

            $(tableSelector + " tbody tr").each(function() {
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
            a.download = filePrefix + new Date().toISOString().slice(0,10) + ".csv";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        };

        window.shareOnWhatsApp = function() {
            const reportType = $('#report_type').val() || 'invoice_wise';
            const isLedger = !$('#ledgerContent').hasClass('d-none');
            const salesman = $('#salesman_id').val() ? $('#salesman_id option:selected').text() : 'All Salesmen';
            const period = $('#start_date').val() + ' to ' + $('#end_date').val();
            
            let text = `📊 *Salesman Performance Report*\n`;
            text += `👤 *Salesman:* ${salesman}\n`;
            text += `📅 *Period:* ${period}\n`;
            text += `━━━━━━━━━━━━━━━━━━━━\n`;

            if (reportType === 'item_wise') {
                text += `📦 *Total Qty Sold:* ${$('#itemTotalQty').text()}\n`;
                text += `💰 *Total Sales:* ${$('#itemTotalSalePrice').text()}\n`;
                text += `🏷️ *Total Cost:* ${$('#itemTotalCost').text()}\n`;
                text += `📈 *Net Profit:* ${$('#itemTotalProfit').text()}\n`;
                text += `━━━━━━━━━━━━━━━━━━━━\n`;
                text += `*Top Sold Products:*\n`;
                $('#itemBody tr').slice(0, 5).each(function() {
                    const code = $(this).find('td:eq(1)').text();
                    const name = $(this).find('td:eq(2)').text();
                    const qty = $(this).find('td:eq(3)').text();
                    const sale = $(this).find('td:eq(4)').text();
                    const profit = $(this).find('td:eq(7)').text();
                    text += `• ${name} (${code}): Qty ${qty} | Sale: ${sale} | Profit: ${profit}\n`;
                });
            } else if (isLedger) {
                text += `💰 *Total Sales:* ${$('#ledgerTotal').text()}\n`;
                text += `📄 _Details shared below_\n`;
                // Add a few recent invoices if any
                $('#ledgerBody tr').slice(0, 5).each(function() {
                    const date = $(this).find('td:eq(0)').text();
                    const inv = $(this).find('td:eq(1)').text();
                    const amt = $(this).find('td:eq(4)').text();
                    text += `• ${date} | ${inv} | ${amt}\n`;
                });
            } else {
                text += `📊 *Total Invoices:* ${$('#footerInvoices').text()}\n`;
                text += `💰 *Total Amount:* ${$('#footerTotalAmount').text()}\n`;
                text += `━━━━━━━━━━━━━━━━━━━━\n`;
                // Add top 5 salesmen
                $('#reportBody tr').slice(0, 5).each(function() {
                    const name = $(this).find('.salesman-name').text();
                    const amt = $(this).find('td:eq(5)').text();
                    text += `👤 ${name}: ${amt}\n`;
                });
            }

            text += `\n_Generated via ERP System_`;
            
            const waUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
            window.open(waUrl, '_blank');
        };
    });

function renderFormalPrintDoc() {
    const reportType = $('#report_type').val() || 'invoice_wise';
    const isLedger = !!$('#salesman_id').val();
    const branchName = $('#branch_id option:selected').val() ? $('#branch_id option:selected').text().toUpperCase() : 'ALL BRANCHES';
    const startDate = $('#start_date').val() || '';
    const endDate = $('#end_date').val() || '';
    const periodText = (startDate && endDate) ? `For The Period: ${startDate} to ${endDate}` : '';

    const pBranchTitle = document.getElementById('p_branch_title');
    const pDocTitle = document.getElementById('p_doc_title');
    const pDocPeriod = document.getElementById('p_doc_period');
    const pTableContainer = document.getElementById('p_table_container');
    const pFooterDesc = document.getElementById('p_footer_doc_desc');
    const pPrintTime = document.getElementById('p_print_time');

    if (pBranchTitle) pBranchTitle.textContent = branchName;
    if (pPrintTime) {
        const now = new Date();
        pPrintTime.textContent = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + 
                                 now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    function n(v) { return parseFloat(v) || 0; }
    function fmt(v) { return n(v).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

    if (reportType === 'item_wise') {
        const salesmanName = $('#salesman_id').val() ? $('#salesman_id option:selected').text() : 'All Salesmen';
        if (pDocTitle) pDocTitle.textContent = 'Salesman Item-Wise Sales & Profit Report';
        if (pDocPeriod) pDocPeriod.textContent = `Salesman: ${salesmanName} | ${periodText}`;
        if (pFooterDesc) pFooterDesc.textContent = `Salesman Item-Wise Performance & Profitability Analysis — ${salesmanName}`;

        const data = window.currentItemData || [];
        let grandQty = 0;
        let grandSale = 0;
        let grandCost = 0;
        let grandProfit = 0;
        let tbodyHtml = '';

        if (data.length === 0) {
            tbodyHtml = '<tr><td colspan="8" style="text-align:center; padding:18px; color:#64748b; font-style:italic;">No item sales found for this criteria</td></tr>';
        } else {
            data.forEach(function(item, idx) {
                const qty = n(item.quantity);
                const sale = n(item.sale_price);
                const rate = n(item.purchasing_rate);
                const cost = n(item.total_cost);
                const profit = n(item.net_profit);

                grandQty += qty;
                grandSale += sale;
                grandCost += cost;
                grandProfit += profit;

                tbodyHtml += `
                <tr>
                    <td style="text-align:center;">${idx + 1}</td>
                    <td style="font-weight:700;">${item.item_code || '-'}</td>
                    <td>${item.item_name || '-'}</td>
                    <td class="num-cell">${fmt(qty).replace('.00', '')}</td>
                    <td class="num-cell" style="font-weight:700;">${fmt(sale)}</td>
                    <td class="num-cell">${fmt(rate)}</td>
                    <td class="num-cell">${fmt(cost)}</td>
                    <td class="num-cell" style="font-weight:700; color:${profit >= 0 ? '#047857' : '#b91c1c'};">${profit < 0 ? '-' : ''}${fmt(Math.abs(profit))}</td>
                </tr>`;
            });
        }

        const tableHtml = `
        <table class="print-tb-table">
            <thead>
                <tr>
                    <th style="width: 4%; text-align: center;">#</th>
                    <th style="width: 14%;">ITEM CODE</th>
                    <th style="width: 28%;">ITEM NAME</th>
                    <th style="width: 8%; text-align: right;">QUANTITY</th>
                    <th style="width: 12%; text-align: right;">SALE PRICE</th>
                    <th style="width: 11%; text-align: right;">PURCHASING RATE</th>
                    <th style="width: 11%; text-align: right;">TOTAL</th>
                    <th style="width: 12%; text-align: right;">NET PROFIT</th>
                </tr>
            </thead>
            <tbody>
                ${tbodyHtml}
            </tbody>
            <tfoot>
                <tr class="p-tfoot">
                    <td colspan="3" style="text-align: right; font-weight: 800;">GRAND TOTAL:</td>
                    <td class="num-cell" style="font-weight: 800; font-size: 10.5pt;">${fmt(grandQty).replace('.00', '')}</td>
                    <td class="num-cell" style="font-weight: 800; font-size: 10.5pt;">${fmt(grandSale)}</td>
                    <td class="num-cell" style="font-weight: 800; font-size: 10.5pt;">-</td>
                    <td class="num-cell" style="font-weight: 800; font-size: 10.5pt;">${fmt(grandCost)}</td>
                    <td class="num-cell" style="font-weight: 800; font-size: 10.5pt; color:${grandProfit >= 0 ? '#047857' : '#b91c1c'};">${grandProfit < 0 ? '-' : ''}${fmt(Math.abs(grandProfit))}</td>
                </tr>
            </tfoot>
        </table>`;

        if (pTableContainer) pTableContainer.innerHTML = tableHtml;

    } else if (isLedger) {
        const salesmanName = $('#salesman_id option:selected').text();
        if (pDocTitle) pDocTitle.textContent = 'Salesman Detailed Sales Ledger';
        if (pDocPeriod) pDocPeriod.textContent = `Salesman: ${salesmanName} | ${periodText}`;
        if (pFooterDesc) pFooterDesc.textContent = `Salesman Detailed Ledger & Transactions — ${salesmanName}`;

        const data = window.currentLedgerData || [];
        let grandTotal = 0;
        let tbodyHtml = '';

        if (data.length === 0) {
            tbodyHtml = '<tr><td colspan="6" style="text-align:center; padding:18px; color:#64748b; font-style:italic;">No transactions found for this salesman</td></tr>';
        } else {
            data.forEach(function(item, idx) {
                const amt = n(item.total_amount);
                grandTotal += amt;
                tbodyHtml += `
                <tr>
                    <td style="text-align:center;">${idx + 1}</td>
                    <td>${item.date || '-'}</td>
                    <td style="font-weight:700;">${item.invoice_no || '-'}</td>
                    <td>${item.customer || '-'}</td>
                    <td>${item.branch || '-'}</td>
                    <td class="num-cell" style="font-weight:700;">${fmt(amt)}</td>
                </tr>`;
            });
        }

        const tableHtml = `
        <table class="print-tb-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 14%;">DATE</th>
                    <th style="width: 18%;">INVOICE #</th>
                    <th style="width: 28%;">CUSTOMER</th>
                    <th style="width: 17%;">BRANCH</th>
                    <th style="width: 18%; text-align: right;">AMOUNT (PKR)</th>
                </tr>
            </thead>
            <tbody>
                ${tbodyHtml}
            </tbody>
            <tfoot>
                <tr class="p-tfoot">
                    <td colspan="5" style="text-align: right; font-weight: 800;">GRAND TOTAL:</td>
                    <td class="num-cell" style="font-weight: 800; font-size: 11pt;">${fmt(grandTotal)}</td>
                </tr>
            </tfoot>
        </table>`;

        if (pTableContainer) pTableContainer.innerHTML = tableHtml;

    } else {
        // Summary Mode
        if (pDocTitle) pDocTitle.textContent = 'Salesman Performance Report';
        if (pDocPeriod) pDocPeriod.textContent = `${periodText} | Monthly productivity & sales target analysis`;
        if (pFooterDesc) pFooterDesc.textContent = 'Salesman Performance & Productivity Summary';

        const data = window.currentSummaryData || [];
        let grandInvoices = 0;
        let grandAmount = 0;
        let tbodyHtml = '';

        if (data.length === 0) {
            tbodyHtml = '<tr><td colspan="6" style="text-align:center; padding:18px; color:#64748b; font-style:italic;">No performance data found for this criteria</td></tr>';
        } else {
            data.forEach(function(r, idx) {
                const invCount = n(r.total_invoices);
                const salesAmt = n(r.total_amount);
                grandInvoices += invCount;
                grandAmount += salesAmt;
                tbodyHtml += `
                <tr>
                    <td style="text-align:center;">${idx + 1}</td>
                    <td style="font-weight:700;">${r.salesman_name || 'N/A'}</td>
                    <td>${r.branch_name || '-'}</td>
                    <td style="text-align:center;">${r.month_year || '-'}</td>
                    <td class="num-cell" style="font-weight:600;">${invCount}</td>
                    <td class="num-cell" style="font-weight:700;">${fmt(salesAmt)}</td>
                </tr>`;
            });
        }

        const tableHtml = `
        <table class="print-tb-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 31%;">SALESMAN NAME</th>
                    <th style="width: 20%;">BRANCH</th>
                    <th style="width: 15%; text-align: center;">MONTH</th>
                    <th style="width: 13%; text-align: right;">TOTAL INVOICES</th>
                    <th style="width: 16%; text-align: right;">TOTAL SALES (PKR)</th>
                </tr>
            </thead>
            <tbody>
                ${tbodyHtml}
            </tbody>
            <tfoot>
                <tr class="p-tfoot">
                    <td colspan="4" style="text-align: right; font-weight: 800;">GRAND TOTAL:</td>
                    <td class="num-cell" style="font-weight: 800; font-size: 11pt;">${grandInvoices}</td>
                    <td class="num-cell" style="font-weight: 800; font-size: 11pt;">${fmt(grandAmount)}</td>
                </tr>
            </tfoot>
        </table>`;

        if (pTableContainer) pTableContainer.innerHTML = tableHtml;
    }
}

function openPrintPreview() {
    renderFormalPrintDoc();

    const docElem = document.getElementById('formalPrintDoc');
    if (!docElem) return;

    const reportHtml = docElem.innerHTML;
    const reportType = $('#report_type').val() || 'invoice_wise';
    const isLedger = !!$('#salesman_id').val();
    let fullTitle = 'Salesman Performance Report';
    if (reportType === 'item_wise') {
        fullTitle = 'Salesman Item-Wise Sales & Profit Report';
    } else if (isLedger) {
        fullTitle = 'Salesman Detailed Sales Ledger';
    }

    const win = window.open('', '_blank');
    if (win) {
        win.document.open();
        win.document.write(`<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>${fullTitle} - Print Preview</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body {
            margin: 0;
            padding: 0;
            background: #e2e8f0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #000;
        }
        .action-toolbar {
            position: sticky;
            top: 0;
            background: #0f1f38;
            color: #fff;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .report-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .02em;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 5px;
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.15);
            color: #fff;
            cursor: pointer;
            transition: 0.15s;
        }
        .btn-action:hover { background: rgba(255,255,255,0.25); }
        .btn-primary-action { background: #2563eb; border-color: #2563eb; }
        .btn-primary-action:hover { background: #1d4ed8; }

        .normal-page-card {
            max-width: 950px;
            margin: 24px auto 40px auto;
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
        .print-tb-table th { border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 8px 6px; background: #f8fafc; font-weight: 800; color: #000; font-size: 9.5pt; text-transform: uppercase; vertical-align: middle; line-height: 1.25; }
        .print-tb-table td { border-bottom: 1px solid #cbd5e1; padding: 7px 6px; vertical-align: middle; color: #000; font-size: 10pt; line-height: 1.3; overflow: hidden; word-wrap: break-word; }
        .print-tb-table .num-cell { text-align: right; font-variant-numeric: tabular-nums; font-feature-settings: "tnum"; white-space: nowrap; font-size: 10.5pt; letter-spacing: -0.01em; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .print-tb-table tr.p-tfoot td { border-top: 1.5px solid #000; border-bottom: 3px double #000; font-weight: 800; padding: 9px 6px; background: #fff; font-size: 11pt; color: #000; }

        .print-doc-footer { margin-top: 24px; font-size: 9.5pt; color: #000; }
        .print-footer-info { display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 6px; margin-bottom: 38px; font-weight: 600; color: #000; font-size: 9pt; }
        .print-sign-row { display: flex; justify-content: space-between; padding: 0 15px; }
        .print-sign-box { border-top: 1.5px dashed #000; width: 140px; text-align: center; padding-top: 6px; font-size: 9.5pt; font-weight: 700; color: #000; }

        @media print {
            @page {
                size: A4 portrait;
                margin: 6mm 6mm 6mm 6mm !important;
            }
            body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
            .action-toolbar, .no-print { display: none !important; }
            .normal-page-card { border: none !important; box-shadow: none !important; padding: 0 !important; max-width: 100% !important; width: 100% !important; margin: 0 !important; }
            .print-tb-table { width: 100% !important; max-width: 100% !important; table-layout: fixed !important; }
            .print-tb-table th { font-size: 9pt !important; padding: 6px 4px !important; }
            .print-tb-table td { font-size: 9.5pt !important; padding: 5px 4px !important; }
            .print-tb-table .num-cell { font-size: 9.5pt !important; }
            .print-tb-table tr.p-tfoot td { font-size: 10.5pt !important; padding: 7px 4px !important; }
            .print-tb-table th, .print-tb-table td { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>
    <div class="action-toolbar no-print">
        <div class="report-badge">
            <i class="fas fa-user-tie"></i>
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

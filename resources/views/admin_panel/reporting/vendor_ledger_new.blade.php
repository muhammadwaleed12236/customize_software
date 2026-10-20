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

    #ledgerTable th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 6px;
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
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Vendor Account Ledger</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-file-invoice-dollar mr-1" style="color: var(--coa-gold);"></i> Complete Supplier Transaction History & Running Balance &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2" id="printBtnWrap" style="display:none;">
                    <button id="waShareBtn" onclick="shareWhatsApp()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(37, 211, 102, 0.2); border-color: #25D366; color: #25D366;">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button onclick="printVendorReferenceLedger()" class="btn btn-sm btn-outline-light font-weight-bold">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                    <button onclick="showExportOptions()" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-download mr-1 text-primary"></i> Export
                    </button>
                </div>
            </div>

            {{-- 2. Filter Card --}}
            <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form id="ledgerForm" class="row g-2 align-items-end mb-0">
                        @php $user = Auth::user(); @endphp
                        
                        @if($user && $user->hasRole('super admin'))
                            <div class="col-md-3">
                                <label class="f-label">Select Branch</label>
                                <select id="branch_id" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                    <option value="all">-- All Branches --</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ (request('branch_id') == $b->id) ? 'selected' : '' }}>{{ $b->name ?? $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" id="branch_id" value="{{ $user->branch_id ?? '' }}">
                        @endif

                        <div class="col-md-{{ ($user && $user->hasRole('super admin')) ? '3' : '4' }}">
                            <label class="f-label">Select Vendor</label>
                            <select id="vendor_id" class="form-control form-control-sm select2">
                                <option value="">-- Choose Vendor --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}" {{ (request('vendor_id') == $v->id) ? 'selected' : '' }}>{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="f-label">From Date</label>
                            <input type="date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">To Date</label>
                            <input type="date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-sync-alt mr-1"></i> GENERATE
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- LOADER --}}
            <div id="loader" class="text-center py-4" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2 small font-weight-bold">Loading ledger data&hellip;</p>
            </div>

            {{-- LEDGER OUTPUT --}}
            <div id="ledgerBox" style="display:none;">

                {{-- 3. Vendor Summary Card --}}
                <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1.5px solid #cbd5e1 !important; background: #ffffff;">
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-3">
                                <div class="f-label" style="margin-bottom: 2px;">Vendor</div>
                                <div id="vendor_name" style="color: var(--coa-navy); font-size: 15px; font-weight: 800;">-</div>
                            </div>
                            <div class="col-md-3">
                                <div class="f-label" style="margin-bottom: 2px;">Company</div>
                                <span id="vendor_company" class="badge badge-info text-dark" style="font-size: 11px; padding: 4px 8px;">-</span>
                            </div>
                            <div class="col-md-3">
                                <div class="f-label" style="margin-bottom: 2px;">Mobile</div>
                                <div id="vendor_mobile" class="font-weight-bold font-monospace text-dark">-</div>
                            </div>
                            <div class="col-md-3">
                                <div class="f-label" style="margin-bottom: 2px;">Email</div>
                                <div id="vendor_email" class="text-muted small text-truncate">-</div>
                            </div>
                        </div>
                        <hr class="my-2" style="border-top: 1px dashed #cbd5e1;">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <span class="f-label d-inline mr-1">Period: </span>
                                <span id="vendor_period" class="font-weight-bold" style="color: var(--coa-navy);">-</span>
                            </div>
                            <div class="col-md-8 text-right font-monospace" style="font-size: 13px;">
                                <span class="mr-3"><span class="text-muted">Opening: </span><span id="s_open" class="font-weight-bold text-dark">-</span></span>
                                <span class="mr-3"><span class="text-muted">Total Debit (Payments): </span><span id="s_debit" class="font-weight-bold text-danger">-</span></span>
                                <span class="mr-3"><span class="text-muted">Total Credit (Purchases): </span><span id="s_credit" class="font-weight-bold text-success">-</span></span>
                                <span><span class="text-muted">Closing: </span><span id="s_close" class="font-weight-bold text-primary" style="font-size: 15px;">-</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. LEDGER TABLE --}}
                <div id="printArea">
                    <div class="table-responsive" style="border: 1px solid var(--coa-border); border-radius: 9px; overflow: hidden;">
                        <table id="ledgerTable" class="table table-bordered mb-0" style="font-size: 12.5px; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #0f1f38; color: #ffffff;">
                                    <th class="text-center" style="width: 45px;">No</th>
                                    <th class="text-center" style="width: 70px;">Inv No.</th>
                                    <th class="text-center" style="width: 45px;">Type</th>
                                    <th class="text-center" style="width: 70px;">Date</th>
                                    <th class="text-center" style="width: 70px;">Ref</th>
                                    <th>Details (تفصیل)</th>
                                    <th class="text-right" style="width: 70px; background: #1e3a5f !important;" title="Rate per watt (ریٹ)">Rate (ریٹ)</th>
                                    <th class="text-right" style="width: 85px; background: #0f2b48 !important;" title="Unit Price per panel (قیمت)">Price (قیمت)</th>
                                    <th class="text-right" style="width: 65px; background: #064e3b !important;" title="Purchased Quantity (لیے کتنے)">Qty (لیے)</th>
                                    <th class="text-right" style="width: 90px;">Debit (نام)</th>
                                    <th class="text-right" style="width: 90px;">Credit (قیمت/جمع)</th>
                                    <th class="text-right" style="width: 100px;">Balance (بقیہ)</th>
                                </tr>
                            </thead>
                            <tbody id="ledgerBody"></tbody>
                            <tfoot id="ledgerFooter"></tfoot>
                        </table>
                    </div>
                </div>

            </div>{{-- /ledgerBox --}}

            {{-- 4.5 Dedicated Reference Print Container (Matches Reference Ledger Exactly) --}}
            <div id="referencePrintContainer"></div>

        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.lbl  { font-size:11px;color:#666;font-weight:600;text-transform:uppercase;letter-spacing:.4px; }
.val  { font-size:13px;font-weight:600;color:#1a1a2e; }

/* Modern Filter Styles */
.filter-card {
    background: #ffffff;
    border-radius: 15px;
    border: 1px solid #edf2f7;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}
.modern-input {
    border: 1px solid #e2e8f0 !important;
    border-radius: 8px !important;
    padding: 0.6rem 0.75rem !important;
    font-size: 0.875rem !important;
    transition: all 0.2s ease;
    background-color: #f8fafc !important;
}
.modern-input:focus {
    background-color: #fff !important;
    border-color: #0066cc !important;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1) !important;
    outline: none !important;
}
.filter-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 800;
    color: #64748b;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    line-height: 1;
}
.btn-generate {
    border-radius: 8px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 0.6rem 1.5rem !important;
    box-shadow: 0 4px 6px -1px rgba(0, 102, 204, 0.2);
}

/* Row colors */
tr.r-open    td { background:#f0f9ff !important; color:#0369a1 !important; font-weight:700; border-color:#bae6fd !important; }
tr.r-sale    td { background:#ffffff !important; color:#1e293b !important; font-weight:700; border-color:#e2e8f0 !important; }
tr.r-item    td { background:#ffffff !important; color:#334155 !important; font-size:12px; border-color:#f1f5f9 !important; }
tr.r-receipt td { background:#f0fdf4 !important; color:#166534 !important; font-weight:600; border-color:#bbf7d0 !important; }
tr.r-pv      td { background:#faf5ff !important; color:#6b21a8 !important; border-color:#e9d5ff !important; }
tr.r-return  td { background:#fff1f2 !important; color:#9f1239 !important; border-color:#fecdd3 !important; }
tr.r-discount td { background:#fff7ed !important; color:#c2410c !important; font-style:italic; border-color:#fed7aa !important; }
tr.r-total   td { background:#f8fafc !important; color:#0f172a !important; font-weight:800; border-top:2px solid #334155 !important; font-size:13px; }
tr.r-close   td { background: linear-gradient(135deg, #0f1f38 0%, #1e3a5f 100%) !important; color:#ffffff !important; font-weight:800; font-size:14px; letter-spacing:0.04em; padding:10px 14px !important; }
tr.r-grand   td { background:#0f1f38 !important; color:#ffffff !important; font-weight:800; font-size:13px; }

/* Balance colors */
.b-dr   { color:#dc2626; font-weight:800; }
.b-cr   { color:#16a34a; font-weight:800; }
.b-zero { color:#2563eb; font-weight:800; }
tr.r-close .b-dr { color:#f87171 !important; font-weight:800; text-shadow:0 1px 2px rgba(0,0,0,0.3); }
tr.r-close .b-cr { color:#4ade80 !important; font-weight:800; text-shadow:0 1px 2px rgba(0,0,0,0.3); }
tr.r-grand .b-dr { color:#f87171 !important; font-weight:800; }
tr.r-grand .b-cr { color:#4ade80 !important; font-weight:800; }

.inv-total-pill {
    background: #0f1f38;
    color: #4ade80 !important;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 800;
    display: inline-block;
    box-shadow: 0 2px 4px rgba(15,31,56,0.15);
}

#ledgerTable td,
#ledgerTable th { vertical-align:middle; padding:6px 7px; }
#ledgerTable tbody tr:hover td { filter:brightness(.98); }

/* Toggle Details CSS */
#ledgerTable.hide-details .detail-row { display: none !important; }

/* Reference Print Container Styles */
#referencePrintContainer {
    display: none;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 6mm 8mm 8mm 8mm;
    }

    /* Force background colors and borders to print properly */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Completely hide everything else on the page */
    .header-navbar, .sidebar, .sidebar-wrapper, .app-header, .main-header,
    .footer, .card, form, button, .btn, .btn-group, .modal, .modal-backdrop,
    .rpt-header-bar, #kpiSummaryCards, .nav, .navbar, .topbar, #ledgerBox, #loader {
        display: none !important;
        visibility: hidden !important;
    }

    body * {
        visibility: hidden !important;
    }

    #referencePrintContainer,
    #referencePrintContainer * {
        visibility: visible !important;
    }

    #referencePrintContainer {
        display: block !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        color: #000000 !important;
        box-sizing: border-box !important;
    }

    #referencePrintContainer table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    #referencePrintContainer thead {
        display: table-header-group !important;
    }

    #referencePrintContainer tfoot {
        display: table-footer-group !important;
    }

    #referencePrintContainer tr {
        page-break-inside: avoid !important;
    }
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function () {
    var lastVendorResponse = null;
    var lastStartDate = '';
    var lastEndDate = '';

    /* ---------- helpers ---------- */
    function n(v) { return parseFloat(v) || 0; }

    function fmt(v) {
        v = n(v);
        return v.toLocaleString('en-PK', {minimumFractionDigits:0, maximumFractionDigits:0});
    }

    function balHtml(b) {
        b = n(b);
        var cls   = b < 0 ? 'b-dr' : (b > 0 ? 'b-cr' : 'b-zero');
        var label = b < 0 ? ' Dr'  : (b > 0 ? ' Cr'  : '');
        return '<span class="' + cls + '">' + fmt(Math.abs(b)) + label + '</span>';
    }

    function dash() { return '<span style="color:#ccc;">&#8212;</span>'; }

    function getWattVal(str) {
        if (!str) return 0;
        var m = String(str).match(/(\d+)\s*(?:W|WATT|WATTS)\b/i);
        return m ? parseFloat(m[1]) : 0;
    }

    /* 12 visible columns */
    function td(txt, align, attrs) {
        align = align || 'center';
        attrs = attrs || '';
        var val = (txt !== null && txt !== undefined && txt !== '') ? txt : dash();
        return '<td style="text-align:' + align + ';border:1px solid #ddd;" ' + attrs + '>' + val + '</td>';
    }

    /* ---------- Initialize Select2 ---------- */
    if ($.fn.select2) {
        $('#vendor_id').select2({ width: '100%' });
    }

    /* ---------- branch -> vendor loader (super admin) ---------- */
    $('#branch_id').on('change', function() {
        var bid = $(this).val();
        var branchParam = (bid === 'all' || !bid) ? '' : bid;

        // Fetch vendors for this branch
        $.get("{{ route('vendors-by-branch') }}", { branch_id: branchParam }, function(res) {
            var currentVid = $('#vendor_id').val();
            var html = '<option value="">-- Choose Vendor --</option>';
            $.each(res, function(i, v) {
                var sel = (v.id == currentVid) ? ' selected' : '';
                html += '<option value="' + v.id + '"' + sel + '>' + (v.customer_name || v.name) + '</option>';
            });
            $('#vendor_id').html(html);
            if ($.fn.select2) {
                $('#vendor_id').select2({ width: '100%' });
            }
        });
    });

    /* ---------- search ---------- */
    $('#btnSearch').on('click', function () {
        var bid   = $('#branch_id').val();
        var vid   = $('#vendor_id').val();
        var start = $('#start_date').val();
        var end   = $('#end_date').val();

        if (!vid || !start || !end) {
            alert('Please select a vendor and date range.');
            return;
        }

        $('#loader').show();
        $('#ledgerBox').hide();
        $('#printBtnWrap').hide();

        var branchParam = (bid === 'all' || !bid) ? '' : bid;

        $.get("{{ route('report.vendor.ledger.fetch.new') }}", {
            branch_id  : branchParam,
            vendor_id  : vid,
            start_date : start,
            end_date   : end
        })
        .done(function (res) {
            $('#loader').hide();
            if (res.error) { alert(res.error); return; }
            render(res, start, end);
        })
        .fail(function (xhr) {
            $('#loader').hide();
            var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Server error. Check console.';
            alert(msg);
        });
    });

    // Auto-search if vendor is pre-selected (e.g. from vendor list link)
    if ($('#vendor_id').val()) {
        $('#btnSearch').trigger('click');
    }

    /* ---------- render ---------- */
    function render(res, start, end) {
        var v = res.vendor;

        /* vendor header */
        $('#vendor_name').text(v.name);
        $('#vendor_company').text(v.company && v.company !== '-' ? v.company.toUpperCase() : '-');
        $('#vendor_mobile').text(v.mobile || '-');
        $('#vendor_email').text(v.email || '-');
        $('#vendor_period').text(start + '  to  ' + end);
        $('#s_open').html(balHtml(res.opening_balance));
        $('#s_debit').text('Rs. ' + fmt(res.total_debit));
        $('#s_credit').text('Rs. ' + fmt(res.total_credit));
        $('#s_close').html(balHtml(res.closing_balance));

        var ob          = n(res.opening_balance);
        var txns        = res.transactions || [];
        var grandDr     = 0;
        var grandCr     = 0;
        var grandQty    = 0;   /* total qty across all items */
        var rowNum      = 2;

        var bodyHtml = '';

        /* Opening Balance Row — 12 cols (Row #1) */
        bodyHtml += '<tr class="r-open" style="background:#f8fafc; font-weight:700; border-bottom:2px solid #cbd5e1;">';
        bodyHtml += td('<strong>1</strong>', 'center'); // 1. No
        bodyHtml += td('', 'center'); // 2. Inv No.
        bodyHtml += td('<span class="badge badge-secondary" style="font-size:10px;padding:3px 6px;">OB</span>', 'center'); // 3. Type
        bodyHtml += td(start || '', 'center'); // 4. Date
        bodyHtml += td('<span class="badge bg-secondary text-white" style="font-size:10px;">OPENING</span>', 'center'); // 5. Ref
        bodyHtml += td('<strong style="color:var(--coa-navy);">Opening Balance / Initial Balance</strong>', 'left'); // 6. Details
        bodyHtml += td('', 'right');  // 7. Rate
        bodyHtml += td('', 'right');  // 8. Price
        bodyHtml += td('', 'right');  // 9. Qty
        bodyHtml += td(ob < 0 ? '<strong style="color:#c62828;">' + fmt(Math.abs(ob)) + '</strong>' : '', 'right');  // 10. Debit
        bodyHtml += td(ob > 0 ? '<strong style="color:#2e7d32;">' + fmt(ob) + '</strong>' : '', 'right');  // 11. Credit
        bodyHtml += td(balHtml(ob), 'right'); // 12. Balance
        bodyHtml += '</tr>';

        /* Process transactions */
        var i = 0;
        while (i < txns.length) {
            var t = txns[i];

            /* ── PURCHASE INVOICE BLOCK ── */
            if (t.row_type === 'sale_header') {
                var header = t;
                var items  = [];
                var saleTotal = null;

                i++; // move past header
                while (i < txns.length && txns[i].row_type === 'sale_item') {
                    items.push(txns[i]);
                    i++;
                }
                if (i < txns.length && txns[i].row_type === 'sale_total') {
                    saleTotal = txns[i];
                    i++;
                }

                var debit  = n(header.debit);
                var credit = n(header.credit);
                if (debit  > 0) grandDr += debit;
                if (credit > 0) grandCr += credit;

                var refVal = (header.bill && header.bill !== '-') ? header.bill : '';

                if (items.length === 0) {
                    /* Fallback: Purchase with no items */
                    var q = saleTotal ? n(saleTotal.total_qty) : 0;
                    if (q > 0) grandQty += q;
                    bodyHtml += '<tr class="r-sale" style="border-top:1.5px solid #cbd5e1;">';
                    bodyHtml += td(rowNum++, 'center');
                    bodyHtml += td(header.vno ? '<strong>' + header.vno + '</strong>' : '', 'center');
                    bodyHtml += td('<span class="badge badge-primary" style="font-size:10px;padding:3px 6px;">PI</span>', 'center');
                    bodyHtml += td(header.date || '', 'center');
                    bodyHtml += td(refVal, 'center');
                    bodyHtml += td('<strong>' + (header.description || 'PURCHASE') + '</strong>', 'left');
                    bodyHtml += td('', 'right'); // Rate
                    bodyHtml += td('', 'right'); // Price
                    bodyHtml += td(q > 0 ? fmt(q) : '', 'right'); // Qty
                    bodyHtml += td(debit > 0 ? '<strong style="color:#c62828;">' + fmt(debit) + '</strong>' : '', 'right');
                    bodyHtml += td(credit > 0 ? '<strong style="color:#2e7d32;">' + fmt(credit) + '</strong>' : '', 'right');
                    bodyHtml += td(header.balance !== null && header.balance !== undefined ? balHtml(header.balance) : '', 'right');
                    bodyHtml += '</tr>';
                } else {
                    /* Render each item in the invoice wrapped together */
                    $.each(items, function(idx, item) {
                        var itemQty  = n(item.qty);
                        var itemRate = n(item.rate);
                        var itemAmt  = n(item.line_amount);
                        if (itemAmt === 0 && items.length === 1 && credit > 0) {
                            itemAmt = credit;
                        }
                        if (itemAmt === 0 && itemQty > 0 && itemRate > 0) {
                            itemAmt = itemQty * itemRate;
                        }
                        grandQty += itemQty;

                        var displayName = (item.item_name_urdu && item.item_name_urdu.trim() !== '') ? item.item_name_urdu : (item.item_name || 'Item');
                        var itemDetailsHtml = '<strong>' + displayName + '</strong>';
                        if (item.item_name_urdu && item.item_name && item.item_name.trim() !== '' && item.item_name !== displayName) {
                            itemDetailsHtml += ' <small class="text-muted">(' + item.item_name + ')</small>';
                        }
                        if (n(item.item_discount) > 0) {
                            itemDetailsHtml += '<br><small style="color:#ea580c;">↳ Disc: &minus;' + fmt(item.item_discount) + '</small>';
                        }

                        var watt = getWattVal(item.item_name) || getWattVal(item.item_name_urdu) || n(item.watt);
                        var rateStr  = '';
                        var priceStr = '';

                        if (watt > 0) {
                            if (itemRate > 0 && itemRate < 500) {
                                rateStr  = '<strong style="color:#1e3a5f;">' + fmt(itemRate) + '</strong>';
                                priceStr = '<strong style="color:#0f2b48;">' + fmt(itemRate * watt) + '</strong>';
                            } else {
                                var unitP = itemRate > 0 ? itemRate : (itemQty > 0 ? itemAmt / itemQty : 0);
                                priceStr = unitP > 0 ? '<strong style="color:#0f2b48;">' + fmt(unitP) + '</strong>' : '';
                                var perW = unitP > 0 ? Math.round((unitP / watt) * 100) / 100 : 0;
                                rateStr  = perW > 0 ? '<strong style="color:#1e3a5f;">' + fmt(perW) + '</strong>' : '';
                            }
                        } else {
                            var rVal = itemRate > 0 ? itemRate : (itemQty > 0 ? itemAmt / itemQty : 0);
                            var pVal = (itemQty > 0 && itemAmt > 0) ? (itemAmt / itemQty) : rVal;
                            rateStr  = rVal > 0 ? '<strong style="color:#1e3a5f;">' + fmt(rVal) + '</strong>' : dash();
                            priceStr = pVal > 0 ? '<strong style="color:#0f2b48;">' + fmt(pVal) + '</strong>' : dash();
                        }

                        // Final safety fallback: If rateStr is empty or dash but unit price exists, populate rateStr
                        if ((!rateStr || rateStr === dash()) && (itemRate > 0 || (itemQty > 0 && itemAmt > 0))) {
                            var fallbackR = itemRate > 0 ? itemRate : (itemAmt / itemQty);
                            rateStr = '<strong style="color:#1e3a5f;">' + fmt(fallbackR) + '</strong>';
                        }
                        if ((!priceStr || priceStr === dash()) && (itemRate > 0 || (itemQty > 0 && itemAmt > 0))) {
                            var fallbackP = (itemQty > 0 && itemAmt > 0) ? (itemAmt / itemQty) : itemRate;
                            priceStr = '<strong style="color:#0f2b48;">' + fmt(fallbackP) + '</strong>';
                        }

                        var borderStyle = idx === 0 ? 'border-top: 1.5px solid #cbd5e1; background: #f0fdf4;' : 'border-top: 1px dashed #e2e8f0; background: #f6fdf8;';

                        bodyHtml += '<tr class="r-sale" style="' + borderStyle + '">';
                        if (idx === 0) {
                            /* Main invoice parameters on 1st item row */
                            bodyHtml += td(rowNum++, 'center');
                            bodyHtml += td(header.vno ? '<strong>' + header.vno + '</strong>' : '', 'center');
                            bodyHtml += td('<span class="badge badge-primary" style="font-size:10px;padding:3px 6px;">PI</span>', 'center');
                            bodyHtml += td(header.date || '', 'center');
                            bodyHtml += td(refVal, 'center');
                            bodyHtml += td(itemDetailsHtml, 'left');
                            bodyHtml += td(rateStr, 'right');
                            bodyHtml += td(priceStr, 'right');
                            bodyHtml += td(itemQty > 0 ? fmt(itemQty) : '', 'right');
                            bodyHtml += td('', 'right');
                            bodyHtml += td(itemAmt > 0 ? '<strong style="color:#2e7d32;">' + fmt(itemAmt) + '</strong>' : (credit > 0 ? '<strong style="color:#2e7d32;">' + fmt(credit) + '</strong>' : ''), 'right');
                            bodyHtml += td(header.balance !== null && header.balance !== undefined ? balHtml(header.balance) : '', 'right');
                        } else {
                            /* Wrapped sub-rows for item 2, item 3, etc. */
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td(itemDetailsHtml, 'left');
                            bodyHtml += td(rateStr, 'right');
                            bodyHtml += td(priceStr, 'right');
                            bodyHtml += td(itemQty > 0 ? fmt(itemQty) : '', 'right');
                            bodyHtml += td('', 'right');
                            bodyHtml += td(itemAmt > 0 ? '<strong style="color:#2e7d32;">' + fmt(itemAmt) + '</strong>' : '', 'right');
                            bodyHtml += td('', 'right');
                        }
                        bodyHtml += '</tr>';
                    });

                    /* Optional Additional Discount / Freight rows if any */
                    if (saleTotal) {
                        var addDisc  = n(saleTotal.add_disc);
                        var extraChg = n(saleTotal.extra_chg);
                        if (addDisc > 0) {
                            bodyHtml += '<tr class="r-sale" style="border-top:1px dashed #e2e8f0; background: #f6fdf8;">';
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('<small style="color:#e65100;font-weight:700;">↳ Additional Discount</small>', 'left');
                            bodyHtml += td('', 'right');
                            bodyHtml += td('', 'right');
                            bodyHtml += td('', 'right');
                            bodyHtml += td('', 'right');
                            bodyHtml += td('<small style="color:#bf360c;font-weight:800;">&minus; ' + fmt(addDisc) + '</small>', 'right');
                            bodyHtml += td('', 'right');
                            bodyHtml += '</tr>';
                        }
                        if (extraChg > 0) {
                            bodyHtml += '<tr class="r-sale" style="border-top:1px dashed #e2e8f0; background: #f6fdf8;">';
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('<small style="color:#1b5e20;font-weight:700;">↳ Extra Charges (Freight)</small>', 'left');
                            bodyHtml += td('', 'right'); // Rate
                            bodyHtml += td('', 'right'); // Price
                            bodyHtml += td('', 'right'); // Qty
                            bodyHtml += td('', 'right'); // Debit
                            bodyHtml += td('<small style="color:#1b5e20;font-weight:800;">+ ' + fmt(extraChg) + '</small>', 'right'); // Credit
                            bodyHtml += td('', 'right'); // Balance
                            bodyHtml += '</tr>';
                        }
                    }
                }

                continue;
            }

            /* ── REGULAR NON-SALE TRANSACTIONS (Receipt, PV, PR, JV) ── */
            var rc = 'r-txn';
            if      (t.row_type === 'receipt')         rc = 'r-receipt';
            else if (t.row_type === 'payment_voucher') rc = 'r-pv';
            else if (t.row_type === 'return')          rc = 'r-return';
            else if (t.row_type === 'discount')        rc = 'r-discount';

            var debit  = n(t.debit);
            var credit = n(t.credit);
            if (debit  > 0) grandDr += debit;
            if (credit > 0) grandCr += credit;

            var typeBadge = '<span class="badge badge-secondary" style="font-size:10px;padding:3px 6px;">TXN</span>';
            if      (t.row_type === 'receipt')         typeBadge = '<span class="badge badge-warning text-dark" style="font-size:10px;padding:3px 6px;">PV</span>';
            else if (t.row_type === 'payment_voucher') typeBadge = '<span class="badge badge-success" style="font-size:10px;padding:3px 6px;">PA</span>';
            else if (t.row_type === 'return')          typeBadge = '<span class="badge badge-danger" style="font-size:10px;padding:3px 6px;">PR</span>';
            else if (t.row_type === 'journal_debit' || t.row_type === 'journal_credit') typeBadge = '<span class="badge badge-info text-dark" style="font-size:10px;padding:3px 6px;">JV</span>';

            var descHtml = t.description ? '<strong>' + t.description + '</strong>' : '';
            if (t.item_name && t.item_name !== '-') {
                descHtml += '<br><small style="color:#555;">' + t.item_name + '</small>';
            }
            var refVal = (t.bill && t.bill !== '-') ? t.bill : ((t.reference_no && t.reference_no !== '-') ? t.reference_no : '');

            bodyHtml += '<tr class="' + rc + '" style="border-top:1.5px solid #cbd5e1;">';
            bodyHtml += td(rowNum++, 'center');                                                   // 1. No
            bodyHtml += td(t.vno ? '<strong>' + t.vno + '</strong>' : '', 'center');              // 2. Inv No.
            bodyHtml += td(typeBadge, 'center');                                                 // 3. Type
            bodyHtml += td(t.date  || '', 'center');                                              // 4. Date
            bodyHtml += td(refVal, 'center');                                                     // 5. Ref
            bodyHtml += td(descHtml, 'left');                                                     // 6. Details
            var txRateVal = (t.rate && n(t.rate) > 0) ? ('<strong style="color:#1e3a5f;">' + fmt(t.rate) + '</strong>') : '';
            var txPriceVal = (t.rate && n(t.rate) > 0) ? ('<strong style="color:#0f2b48;">' + fmt(t.rate) + '</strong>') : '';
            bodyHtml += td(txRateVal, 'right');                                                   // 7. Rate
            bodyHtml += td(txPriceVal, 'right');                                                  // 8. Price
            bodyHtml += td(t.qty  && n(t.qty)  > 0 ? fmt(t.qty)  : '', 'right');                  // 9. Qty
            bodyHtml += td(debit  > 0 ? '<strong style="color:#c62828;">' + fmt(debit)  + '</strong>' : '', 'right'); // 10. Debit
            bodyHtml += td(credit > 0 ? '<strong style="color:#2e7d32;">' + fmt(credit) + '</strong>' : '', 'right');// 11. Credit
            bodyHtml += td(t.balance !== null && t.balance !== undefined ? balHtml(t.balance) : '', 'right');          // 12. Balance
            bodyHtml += '</tr>';

            i++;
        }

        /* ── footer HTML ── */
        var finalBal = n(res.closing_balance);

        /* Overall Sum row */
        var footHtml = '<tr class="r-total">';
        footHtml += '<td colspan="6" style="text-align:right;border:1px solid #ccc;padding:6px 10px;font-size:13px;"><strong>Total Sum (All Transactions)</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;">&#8212;</td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;">&#8212;</td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;font-size:13px;font-weight:800;color:#1a1a2e;background:#eaf4ff;">' + fmt(grandQty) + '</td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;"><strong style="color:#c62828;">' + fmt(grandDr) + '</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;"><strong style="color:#2e7d32;">' + fmt(grandCr) + '</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;">' + balHtml(finalBal) + '</td>';
        footHtml += '</tr>';

        /* Closing Balance row */
        footHtml += '<tr class="r-close">';
        footHtml += '<td colspan="11" style="text-align:right;border:1px solid #333;padding:8px 12px;font-size:13px;letter-spacing:.4px;"><strong>CLOSING BALANCE</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #333;padding:8px 10px;font-size:15px;">' + balHtml(finalBal) + '</td>';
        footHtml += '</tr>';

        /* ── inject into DOM ── */
        $('#ledgerBody').html(bodyHtml);
        $('#ledgerFooter').html(footHtml);

        $('#ledgerBox').show();
        $('#printBtnWrap').show();

        lastVendorResponse = res;
        lastStartDate = start;
        lastEndDate = end;
        buildVendorReferencePrint(res, start, end);
    }

    /* ---------- Reference Print Builder (Exact Accounting Format Matching Reference) ---------- */
    function buildVendorReferencePrint(res, start, end) {
        if (!res || !res.vendor) return;
        var v = res.vendor;
        var ob = n(res.opening_balance);
        var txns = res.transactions || [];

        // Branch detection
        var branchName = '';
        var bSel = document.getElementById('branch_id');
        if (bSel && bSel.selectedIndex > 0) {
            var txt = bSel.options[bSel.selectedIndex].text;
            if (txt && txt.indexOf('--') === -1) branchName = txt.trim();
        }

        // Date formatting helper: 2026-10-28 -> 28-Oct-2026
        function refDate(dStr) {
            if (!dStr) return '';
            var parts = String(dStr).split(/[-/]/);
            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            if (parts.length === 3) {
                if (parts[0].length === 4) {
                    var mo = parseInt(parts[1], 10) - 1;
                    return parts[2] + '-' + (months[mo] || parts[1]) + '-' + parts[0];
                } else {
                    var mo = parseInt(parts[1], 10) - 1;
                    var yr = parts[2].length === 2 ? ('20' + parts[2]) : parts[2];
                    return parts[0] + '-' + (months[mo] || parts[1]) + '-' + yr;
                }
            }
            return dStr;
        }

        // Standard 2-decimal money format (e.g. 10,006,344.00)
        function fmt2(val) {
            var num = parseFloat(val) || 0;
            return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Current Date & Time for header
        var now = new Date();
        var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var pDate = String(now.getDate()).padStart(2, '0') + '-' + monthNames[now.getMonth()] + '-' + now.getFullYear();
        var hrs = now.getHours();
        var mins = String(now.getMinutes()).padStart(2, '0');
        var ampm = hrs >= 12 ? 'pm' : 'am';
        hrs = hrs % 12; hrs = hrs ? hrs : 12;
        var pDateTime = pDate + ' &nbsp;&nbsp; ' + hrs + ':' + mins + ' ' + ampm;

        // Financial Year
        var finYear = (start && start.length >= 4) ? start.substring(0, 4) : now.getFullYear();

        // Account Details for Vendor
        var acCode = '21-01-' + String(v.id || 1).padStart(6, '0');
        var acDesc = (v.name || 'VENDOR').toUpperCase();
        var acSub = '';
        if (v.company && v.company !== '-') acSub += v.company;
        if (v.mobile && v.mobile !== '-') acSub += (acSub ? ' &nbsp;|&nbsp; ' : '') + 'Ph: ' + v.mobile;

        var html = '';
        html += '<div style="font-family: \'Segoe UI\', Tahoma, Arial, sans-serif; color: #000; padding: 2px;">';

        // 1. TOP HEADER (Center company name, report title, right date/time/page)
        html += '<div style="position: relative; text-align: center; margin-bottom: 14px;">';
        html += '  <div style="font-family: \'Times New Roman\', Times, Georgia, serif; font-size: 24px; font-weight: bold; color: #000; letter-spacing: 0.5px;">Ameen &amp; Sons</div>';
        if (branchName) {
            html += '  <div style="font-size: 12px; font-weight: 600; color: #333; margin-top: 1px;">Branch: ' + branchName + '</div>';
        }
        html += '  <div style="font-size: 15px; font-weight: 700; color: #111; margin-top: 3px;">Vendor Ledger Report</div>';
        html += '  <div style="position: absolute; right: 0; top: 0; text-align: right; font-size: 11px; line-height: 1.4; color: #222;">';
        html += '    <div>' + pDateTime + '</div>';
        html += '    <div style="margin-top: 2px;">Page 1 of 1</div>';
        html += '  </div>';
        html += '</div>';

        // 2. ACCOUNT HEADER ROW (Left rounded box + Right dates)
        html += '<div style="display: flex; justify-content: space-between; align-items: stretch; margin-bottom: 8px;">';
        // Left Box
        html += '  <div style="width: 63%; border: 1.5px solid #000; border-radius: 14px; padding: 8px 14px; font-size: 12px; line-height: 1.5; box-sizing: border-box;">';
        html += '    <div><strong>A/C Code :</strong> &nbsp; <span style="font-family: monospace; font-size: 12.5px;">' + acCode + '</span></div>';
        html += '    <div style="margin-top: 2px;"><strong>A/C Description :</strong> &nbsp; <strong style="font-size: 12.5px;">' + acDesc + '</strong></div>';
        if (acSub) {
            html += '    <div style="font-size: 10.5px; color: #333; margin-top: 1px; padding-left: 110px;">' + acSub + '</div>';
        }
        html += '  </div>';
        // Right Dates
        html += '  <div style="width: 33%; display: flex; flex-direction: column; justify-content: center; font-size: 12px; box-sizing: border-box;">';
        html += '    <div style="display: flex; align-items: baseline; margin-bottom: 8px;">';
        html += '      <strong style="width: 80px;">From Date :</strong>';
        html += '      <span style="flex: 1; border-bottom: 1px solid #000; text-align: center; font-weight: 600; padding-bottom: 2px;">' + refDate(start) + '</span>';
        html += '    </div>';
        html += '    <div style="display: flex; align-items: baseline;">';
        html += '      <strong style="width: 80px;">To Date :</strong>';
        html += '      <span style="flex: 1; border-bottom: 1px solid #000; text-align: center; font-weight: 600; padding-bottom: 2px;">' + refDate(end) + '</span>';
        html += '    </div>';
        html += '  </div>';
        html += '</div>';

        // 3. SUB-BOXES ROW (Left: Financial Year & Op Bal, Right: Balance Before)
        html += '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
        // Left Sub-box
        html += '  <div style="width: 48%; border: 1.5px solid #000; border-radius: 12px; padding: 6px 14px; font-size: 11.5px; line-height: 1.5; box-sizing: border-box;">';
        html += '    <div><strong>Financial Year :</strong> &nbsp;&nbsp;&nbsp;&nbsp; <span>' + finYear + '</span></div>';
        html += '    <div style="display: flex; justify-content: space-between; margin-top: 2px;">';
        html += '      <span><strong>Year Op. Bal :</strong> &nbsp; ' + fmt2(Math.abs(ob)) + '</span>';
        html += '      <strong>' + (ob < 0 ? 'Debit' : 'Credit') + '</strong>';
        html += '    </div>';
        html += '  </div>';
        // Right Sub-box
        html += '  <div style="width: 38%; border: 1.5px solid #000; border-radius: 12px; padding: 10px 16px; font-size: 12px; display: flex; justify-content: space-between; align-items: center; box-sizing: border-box;">';
        html += '    <strong>Balance Before :</strong>';
        html += '    <strong style="font-size: 13px; font-family: monospace;">' + fmt2(Math.abs(ob)) + '</strong>';
        html += '  </div>';
        html += '</div>';

        // 4. THE 9-COLUMN ACCOUNTING TABLE
        html += '<table style="width: 100%; border-collapse: collapse; font-size: 10.5px; margin-top: 4px; font-family: inherit;">';
        html += '  <thead>';
        html += '    <tr style="background: #f1f3f5; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000;">';
        html += '      <th style="border: 1px solid #555; padding: 4px 3px; text-align: center; width: 75px;">Date</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 3px; text-align: center; width: 95px;">Voucher #</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 3px; text-align: center; width: 55px;">Ref #</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 3px; text-align: center; width: 55px;">Doc. #</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 6px; text-align: left;">Remarks</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 3px; text-align: center; width: 70px;">Cheque #</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 6px; text-align: right; width: 95px;">Debit</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 6px; text-align: right; width: 95px;">Credit</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 6px; text-align: right; width: 105px;">Balance</th>';
        html += '    </tr>';
        html += '  </thead>';
        html += '  <tbody>';

        // ROW 1: OPENING BALANCE
        html += '    <tr style="border-bottom: 1px solid #777;">';
        html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + refDate(start) + '</td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">-</td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;"></td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;"></td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: left;"><strong>OPENING BALANCE</strong></td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;"></td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right;">' + (ob < 0 ? fmt2(Math.abs(ob)) : '0.00') + '</td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right;">' + (ob > 0 ? fmt2(ob) : '0.00') + '</td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right; font-weight: bold;">' + fmt2(Math.abs(ob)) + '</td>';
        html += '    </tr>';

        // TRANSACTION ROWS
        var i = 0;
        while (i < txns.length) {
            var t = txns[i];

            if (t.row_type === 'sale_header') {
                var header = t;
                var items = [];
                i++;
                while (i < txns.length && txns[i].row_type === 'sale_item') {
                    items.push(txns[i]);
                    i++;
                }
                if (i < txns.length && txns[i].row_type === 'sale_total') {
                    i++;
                }

                var credit = n(header.credit);
                var refVal = (header.bill && header.bill !== '-') ? header.bill : '';
                var docVal = header.vno ? header.vno.replace(/^[A-Za-z]+-/, '') : '';

                var remarks = '';
                if (items.length > 0) {
                    var itemParts = [];
                    for (var k = 0; k < items.length; k++) {
                        var it = items[k];
                        var iName = (it.item_name_urdu && it.item_name_urdu.trim()) ? it.item_name_urdu : (it.item_name || 'Item');
                        var itQty = n(it.qty);
                        itemParts.push(iName + (itQty > 0 ? ' (' + itQty + ')' : ''));
                    }
                    remarks = itemParts.join(', ');
                }
                if (!remarks) remarks = header.description || 'PURCHASE INVOICE';

                html += '    <tr style="border-bottom: 1px solid #777;">';
                html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + refDate(header.date) + '</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center; font-weight: 600;">' + (header.vno || '') + '</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + refVal + '</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + docVal + '</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: left;">' + remarks + '</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;"></td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right;">0.00</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right;">' + (credit > 0 ? fmt2(credit) : '0.00') + '</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right; font-weight: 600;">' + (header.balance !== null ? fmt2(Math.abs(header.balance)) : '') + '</td>';
                html += '    </tr>';
                continue;
            }

            var dVal = refDate(t.date);
            var vVal = t.vno || '';
            var rVal = (t.bill && t.bill !== '-') ? t.bill : ((t.reference_no && t.reference_no !== '-') ? t.reference_no : '');
            var docNo = vVal ? vVal.replace(/^[A-Za-z]+-/, '') : '';
            var remVal = t.description || '';
            if (t.item_name && t.item_name !== '-') {
                remVal += (remVal ? ' - ' : '') + t.item_name;
            }
            var chqVal = (t.cheque_no && t.cheque_no !== '-') ? t.cheque_no : '';
            var dr = n(t.debit);
            var cr = n(t.credit);
            var bal = t.balance !== null && t.balance !== undefined ? fmt2(Math.abs(t.balance)) : '';

            html += '    <tr style="border-bottom: 1px solid #777;">';
            html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + dVal + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center; font-weight: 600;">' + vVal + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + rVal + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + docNo + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: left;">' + remVal + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + chqVal + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right;">' + (dr > 0 ? fmt2(dr) : '0.00') + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right;">' + (cr > 0 ? fmt2(cr) : '0.00') + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right; font-weight: 600;">' + bal + '</td>';
            html += '    </tr>';

            i++;
        }

        html += '  </tbody>';

        // 5. TOTALS ROW (Footer)
        var finalClose = n(res.closing_balance);
        html += '  <tfoot>';
        html += '    <tr style="background: #f8fafc; font-weight: bold; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000;">';
        html += '      <td colspan="6" style="border: 1px solid #555; padding: 6px 8px; text-align: right; font-size: 11px;">Total Sum :</td>';
        html += '      <td style="border: 1px solid #555; padding: 6px 6px; text-align: right; font-size: 11px;">' + fmt2(res.total_debit) + '</td>';
        html += '      <td style="border: 1px solid #555; padding: 6px 6px; text-align: right; font-size: 11px;">' + fmt2(res.total_credit) + '</td>';
        html += '      <td style="border: 1px solid #555; padding: 6px 6px; text-align: right; font-size: 11px;">' + fmt2(Math.abs(finalClose)) + '</td>';
        html += '    </tr>';
        html += '  </tfoot>';
        html += '</table>';

        // 6. SIGNATURE LINES
        html += '<div style="margin-top: 45px; display: flex; justify-content: space-between; padding: 0 40px; page-break-inside: avoid;">';
        html += '  <div style="text-align: center; width: 160px;">';
        html += '    <div style="border-top: 1.5px solid #000; padding-top: 5px; font-weight: bold; font-size: 11px;">Prepared By</div>';
        html += '  </div>';
        html += '  <div style="text-align: center; width: 160px;">';
        html += '    <div style="border-top: 1.5px solid #000; padding-top: 5px; font-weight: bold; font-size: 11px;">Checked By</div>';
        html += '  </div>';
        html += '  <div style="text-align: center; width: 160px;">';
        html += '    <div style="border-top: 1.5px solid #000; padding-top: 5px; font-weight: bold; font-size: 11px;">Authorized By</div>';
        html += '  </div>';
        html += '</div>';

        html += '</div>';

        var container = document.getElementById('referencePrintContainer');
        if (container) {
            container.innerHTML = html;
        }
    }

    /* Print Trigger */
    window.printVendorReferenceLedger = function() {
        if (!lastVendorResponse || !lastVendorResponse.vendor) {
            alert('Please choose a vendor and click GENERATE first.');
            return;
        }
        buildVendorReferencePrint(lastVendorResponse, lastStartDate, lastEndDate);
        setTimeout(function() {
            window.print();
        }, 100);
    };

    /* ---------- WhatsApp Share ---------- */
    window.shareWhatsApp = function() {
        Swal.fire({
            title: 'Preparing WhatsApp Share...',
            text: 'Generating PDF document to share.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        var element = document.getElementById('ledgerBox');
        var opt = {
          margin:       0.3,
          filename:     'vendor_ledger_' + new Date().toISOString().slice(0,10) + '.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
            var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            // Try Web Share API first for direct file sharing
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Vendor Ledger',
                    text: 'Please find the attached vendor ledger.',
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
            // Download the file
            var url = URL.createObjectURL(pdfBlob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            
            // Open WhatsApp without a specific phone number to allow chat selection
            var msg = "*Vendor Ledger*\nPlease find the attached PDF document.";
            var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
            window.open(waUrl, '_blank');
        });
    }
    /* ---------- Export Options & PDF ---------- */
    window.showExportOptions = function() {
        Swal.fire({
            title: 'Export Vendor Ledger',
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
            didOpen: () => {
                Swal.showLoading();
            }
        });

        var element = document.getElementById('ledgerBox');
        var opt = {
          margin:       0.3,
          filename:     'vendor_ledger_' + new Date().toISOString().slice(0,10) + '.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).save().then(function() {
            Swal.close();
        });
    };

    /* ---------- CSV Export ---------- */
    window.exportCSV = function () {
        var rows = [['No','Inv No','Type','Date','Ref','Details','Rate','Price','Qty','Debit','Credit','Balance']];
        // Only export visible rows (respects toggle details state)
        $('#ledgerTable tbody tr:visible, #ledgerTable tfoot tr:visible').each(function () {
            var cells = [];
            $(this).find('td').each(function () {
                var $td = $(this);
                // Sanitize text: replace special arrows/symbols with plain ascii
                var text = $td.text()
                            .replace(/►/g, '>')
                            .replace(/↳/g, '->')
                            .replace(/−/g, '-')
                            .replace(/↓/g, 'v')
                            .replace(/↑/g, '^')
                            .trim()
                            .replace(/"/g, '""');
                cells.push('"' + text + '"');

                // Handle colspan to keep columns aligned
                var colspan = parseInt($td.attr('colspan')) || 1;
                for (var i = 1; i < colspan; i++) {
                    cells.push('""');
                }
            });
            if (cells.length) rows.push(cells);
        });
        var csv  = rows.map(function(r){return r.join(',');}).join('\n');
        // Prepend UTF-8 BOM (\uFEFF) so Excel opens it with proper character encoding
        var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8;'});
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href   = url;
        a.download = 'vendor_ledger_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
    };
});
</script>
@endsection

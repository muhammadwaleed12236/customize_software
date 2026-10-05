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
                        <h4 class="rpt-header-title">Customer Account Ledger</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-file-invoice-dollar mr-1" style="color: var(--coa-gold);"></i> Complete Transaction History with Running Balance &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2" id="printBtnWrap" style="display:none;">
                    <button id="waShareBtn" onclick="shareWhatsApp()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(37, 211, 102, 0.2); border-color: #25D366; color: #25D366;">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-light font-weight-bold">
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
                                <select id="branch_id" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;" onchange="loadCustomersForBranch(this.value)">
                                    <option value="">-- Select Branch --</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name ?? $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="f-label">
                                    Customer <small id="customerCountHint" class="text-primary ml-1"></small>
                                </label>
                                <select id="customer_id" class="form-control form-control-sm select2" style="width: 100%;">
                                    <option value="">-- Select Branch First --</option>
                                </select>
                            </div>
                        @else
                            <div class="col-md-4">
                                <label class="f-label">
                                    Customer
                                    <small class="text-muted ml-1">({{ count($customers) }} records)</small>
                                </label>
                                <select id="customer_id" class="form-control form-control-sm select2" style="width: 100%;">
                                    <option value="">-- Select Customer --</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">
                                            {{ $c->customer_name }}
                                            @if($c->customer_type) ({{ ucfirst($c->customer_type) }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-2">
                            <label class="f-label">From Date</label>
                            <input type="date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">To Date</label>
                            <input type="date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch"
                                onclick="generateLedger()"
                                class="btn btn-sm btn-primary w-100 font-weight-bold"
                                style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-file-invoice mr-1"></i> Generate Ledger
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

                {{-- 3. Customer Summary Card --}}
                <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1.5px solid #cbd5e1 !important; background: #ffffff;">
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-3">
                                <div class="f-label" style="margin-bottom: 2px;">Customer</div>
                                <div id="cust_name" style="color: var(--coa-navy); font-size: 15px; font-weight: 800;">-</div>
                            </div>
                            <div class="col-md-2">
                                <div class="f-label" style="margin-bottom: 2px;">Type</div>
                                <span id="cust_type" class="badge badge-info text-dark" style="font-size: 11px; padding: 4px 8px;">-</span>
                            </div>
                            <div class="col-md-2">
                                <div class="f-label" style="margin-bottom: 2px;">Mobile</div>
                                <div id="cust_mobile" class="font-weight-bold font-monospace text-dark">-</div>
                            </div>
                            <div class="col-md-3">
                                <div class="f-label" style="margin-bottom: 2px;">Address</div>
                                <div id="cust_addr" class="text-muted small text-truncate">-</div>
                            </div>
                            <div class="col-md-2">
                                <div class="f-label" style="margin-bottom: 2px;">Credit Limit</div>
                                <div id="cust_limit" class="font-weight-bold font-monospace" style="color: #ea580c;">-</div>
                            </div>
                        </div>
                        <hr class="my-2" style="border-top: 1px dashed #cbd5e1;">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <span class="f-label d-inline mr-1">Period: </span>
                                <span id="cust_period" class="font-weight-bold" style="color: var(--coa-navy);">-</span>
                            </div>
                            <div class="col-md-8 text-right font-monospace" style="font-size: 13px;">
                                <span class="mr-3"><span class="text-muted">Opening: </span><span id="s_open" class="font-weight-bold text-dark">-</span></span>
                                <span class="mr-3"><span class="text-muted">Total Debit: </span><span id="s_debit" class="font-weight-bold text-danger">-</span></span>
                                <span class="mr-3"><span class="text-muted">Total Credit: </span><span id="s_credit" class="font-weight-bold text-success">-</span></span>
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
                                <tr>
                                    <th class="text-center" style="width: 65px;">No</th>
                                    <th class="text-center" style="width: 80px;">Inv No.</th>
                                    <th class="text-center" style="width: 55px;">Type</th>
                                    <th class="text-center" style="width: 80px;">Date</th>
                                    <th class="text-center" style="width: 85px;">Ref</th>
                                    <th>Details</th>
                                    <th class="text-right" style="width: 65px;">Qty</th>
                                    <th class="text-right" style="width: 95px;">Debit</th>
                                    <th class="text-right" style="width: 95px;">Credit</th>
                                    <th class="text-right" style="width: 110px;">Balance</th>
                                </tr>
                            </thead>
                            <tbody id="ledgerBody"></tbody>
                            <tfoot id="ledgerFooter"></tfoot>
                        </table>
                    </div>
                </div>

            </div>{{-- /ledgerBox --}}

        </div>
    </div>
</div>

<!-- Scripts for Export options -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
.lbl  { font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.4px; }
.val  { font-size:13px;font-weight:700;color:#0f172a; }

/* Row colors */
tr.r-open    td { background:#f0f9ff !important; color:#0369a1 !important; font-weight:700; border-color:#bae6fd !important; }
tr.r-sale    td { background:#fefce8 !important; color:#1e293b !important; font-weight:700; border-color:#fef08a !important; }
tr.r-item    td { background:#ffffff !important; color:#334155 !important; font-size:12px; border-color:#f1f5f9 !important; }
tr.r-receipt td { background:#f0fdf4 !important; color:#166534 !important; font-weight:600; border-color:#bbf7d0 !important; }
tr.r-pv      td { background:#faf5ff !important; color:#6b21a8 !important; border-color:#e9d5ff !important; }
tr.r-return  td { background:#fff1f2 !important; color:#9f1239 !important; border-color:#fecdd3 !important; }
tr.r-discount td { background:#fff7ed !important; color:#c2410c !important; font-style:italic; border-color:#fed7aa !important; }
tr.r-total   td { background:#f8fafc !important; color:#0f172a !important; font-weight:800; border-top:2px solid #334155 !important; font-size:13px; }
tr.r-close   td { background: linear-gradient(135deg, #0f1f38 0%, #1e3a5f 100%) !important; color:#ffffff !important; font-weight:800; font-size:14px; letter-spacing:0.04em; padding:10px 14px !important; }
tr.r-grand   td { background:#0f1f38 !important; color:#ffffff !important; font-weight:800; font-size:13px; }

/* Premium Input Styling */
.fi-premium {
    border-radius: 8px !important;
    border: 1.5px solid #e3e6f0 !important;
    background-color: #ffffff !important;
    padding: 0.6rem 0.75rem !important;
    font-size: 0.9rem !important;
    transition: all 0.2s ease-in-out;
}
.fi-premium:focus {
    border-color: #0066cc !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.1) !important;
    background-color: #fff !important;
}
.select2-container--default .select2-selection--single {
    border: 1.5px solid #e3e6f0 !important;
    border-radius: 8px !important;
    height: 45px !important;
    padding-top: 8px !important;
}

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

@media print {
    .card, button, form, #printBtnWrap { display:none !important; }
    #ledgerBox, #printArea { display:block !important; }
}
</style>

<script>

    /* ═══════════════════════════════════════════════════
     * INITIALIZE SELECT2 (Safely waiting for jQuery to load)
     * ═══════════════════════════════════════════════════ */
    window.addEventListener('load', function() {
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            $('#customer_id').select2({
                width: '100%',
                placeholder: '-- Select Customer --',
                allowClear: true
            });
        }
    });

    /* ═══════════════════════════════════════════════════
     * GLOBAL: Load customers by branch (called from onchange)
     * ═══════════════════════════════════════════════════ */
    function loadCustomersForBranch(bid) {
        var $c = $('#customer_id');
        var $hint = $('#customerCountHint');

        // Safely destroy Select2 if it exists, then reset options
        if ($c.hasClass('select2-hidden-accessible')) {
            $c.select2('destroy');
        }
        $c.html('<option value="">Loading customers\u2026</option>');
        $c.prop('disabled', true);
        if ($hint.length) $hint.text('');

        if (!bid) {
            $c.html('<option value="">-- Select Branch First --</option>');
            $c.prop('disabled', false);
            $c.select2({ width: '100%' });
            return;
        }

        // Use jQuery AJAX since it is guaranteed to be loaded when this is triggered
        if (typeof jQuery === 'undefined') {
            console.error("jQuery is not loaded yet.");
            return;
        }

        $.ajax({
            url: "{{ route('report.customers.byBranch') }}",
            type: 'GET',
            data: { branch_id: bid },
            dataType: 'json',
            success: function(list) {
                $c.html('<option value="">-- Select Customer --</option>');
                if (list.length === 0) {
                    $c.append('<option disabled>No customers found for this branch</option>');
                } else {
                    $.each(list, function(i, c) {
                        var label = c.customer_name + (c.customer_type ? ' (' + c.customer_type + ')' : '');
                        $c.append($('<option>', { value: c.id, text: label }));
                    });
                    if ($hint.length) $hint.text(list.length + ' customer(s)');
                }
                $c.prop('disabled', false);
                $c.select2({ width: '100%' });
            },
            error: function(xhr) {
                $c.html('<option value="">-- Error loading customers --</option>');
                $c.prop('disabled', false);
                $c.select2({ width: '100%' });
                console.error('Failed to load customers. Status:', xhr.status, xhr.responseText);
            }
        });
    }

    /* ═══════════════════════════════════════════════════
     * GLOBAL: Generate customer ledger report
     * ═══════════════════════════════════════════════════ */
    function generateLedger() {
        var cid   = document.getElementById('customer_id').value;
        var start = document.getElementById('start_date').value;
        var end   = document.getElementById('end_date').value;

        if (!cid || !start || !end) {
            alert('Please select a customer and choose the date range.');
            return;
        }

        document.getElementById('loader').style.display = 'block';
        document.getElementById('ledgerBox').style.display = 'none';
        var pwrap = document.getElementById('printBtnWrap');
        if (pwrap) pwrap.style.display = 'none';

        var url = "{{ route('report.customer.ledger.fetch.new') }}?customer_id=" + encodeURIComponent(cid)
                + '&start_date=' + encodeURIComponent(start)
                + '&end_date='   + encodeURIComponent(end);

        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function() {
            document.getElementById('loader').style.display = 'none';
            if (xhr.status === 200) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.error) { alert(res.error); return; }
                    renderLedger(res, start, end);
                } catch(e) {
                    alert('Parse error. Check console.');
                    console.error('JSON parse error:', e, xhr.responseText);
                }
            } else {
                try {
                    var err = JSON.parse(xhr.responseText);
                    alert(err.error || 'Server error ' + xhr.status);
                } catch(e) {
                    alert('Server error ' + xhr.status + '. Check console.');
                }
                console.error('Ledger fetch failed:', xhr.status, xhr.responseText);
            }
        };

        xhr.onerror = function() {
            document.getElementById('loader').style.display = 'none';
            alert('Network error. Please try again.');
        };

        xhr.send();
    }

    /* ---------- helpers ---------- */
    function n(v) { return parseFloat(v) || 0; }

    function fmt(v) {
        v = n(v);
        return v.toLocaleString('en-PK', {minimumFractionDigits:0, maximumFractionDigits:0});
    }

    function balHtml(b) {
        b = n(b);
        var cls   = b > 0 ? 'b-dr' : (b < 0 ? 'b-cr' : 'b-zero');
        var label = b > 0 ? ' Dr'  : (b < 0 ? ' Cr'  : '');
        return '<span class="' + cls + '">' + fmt(Math.abs(b)) + label + '</span>';
    }

    function dash() { return '<span style="color:#ccc;">&#8212;</span>'; }

    /* 10 visible columns: No, Inv No., Type, Date, Ref, Details, Qty, Debit, Credit, Balance */
    function td(txt, align, attrs) {
        align = align || 'center';
        attrs = attrs || '';
        var val = (txt !== null && txt !== undefined && txt !== '') ? txt : dash();
        return '<td style="text-align:' + align + ';border:1px solid #ddd;" ' + attrs + '>' + val + '</td>';
    }

    /* ---------- render ---------- */
    function renderLedger(res, start, end) {
        var c = res.customer;

        /* customer header */
        $('#cust_name').text(c.name);
        $('#cust_type').text(c.type ? c.type.toUpperCase() : '-');
        $('#cust_mobile').text(c.mobile || '-');
        $('#cust_addr').text(c.address || '-');
        $('#cust_limit').text(c.credit_limit ? 'Rs. ' + fmt(c.credit_limit) : 'Unlimited');
        $('#cust_period').text(start + '  to  ' + end);
        $('#s_open').html(balHtml(res.opening_balance));
        $('#s_debit').text('Rs. ' + fmt(res.total_debit));
        $('#s_credit').text('Rs. ' + fmt(res.total_credit));
        $('#s_close').html(balHtml(res.closing_balance));

        var ob          = n(res.opening_balance);
        var txns        = res.transactions || [];
        var grandDr     = 0;
        var grandCr     = 0;
        var grandQty    = 0;   /* total qty across all items */
        var rowNum      = 1;

        var bodyHtml = '';

        /* Opening Balance Row — 10 cols */
        bodyHtml += '<tr class="r-open">';
        bodyHtml += td('', 'center'); // 1. No
        bodyHtml += td('', 'center'); // 2. Inv No.
        bodyHtml += td('', 'center'); // 3. Type
        bodyHtml += td('', 'center'); // 4. Date
        bodyHtml += td('', 'center'); // 5. Ref
        bodyHtml += td('<strong>Opening Balance</strong>', 'left'); // 6. Details
        bodyHtml += td('', 'right');  // 7. Qty
        bodyHtml += td('', 'right');  // 8. Debit
        bodyHtml += td('', 'right');  // 9. Credit
        bodyHtml += td(balHtml(ob), 'right'); // 10. Balance
        bodyHtml += '</tr>';

        /* Process transactions */
        var i = 0;
        while (i < txns.length) {
            var t = txns[i];

            /* ── SALE INVOICE BLOCK ── */
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
                    /* Fallback: Sale with no items */
                    var q = saleTotal ? n(saleTotal.total_qty) : 0;
                    if (q > 0) grandQty += q;
                    bodyHtml += '<tr class="r-sale" style="border-top:1.5px solid #cbd5e1;">';
                    bodyHtml += td(rowNum++, 'center');
                    bodyHtml += td(header.vno ? '<strong>' + header.vno + '</strong>' : '', 'center');
                    bodyHtml += td('<span class="badge badge-primary" style="font-size:10px;padding:3px 6px;">SI</span>', 'center');
                    bodyHtml += td(header.date || '', 'center');
                    bodyHtml += td(refVal, 'center');
                    bodyHtml += td('<strong>' + (header.description || 'SALE') + '</strong>', 'left');
                    bodyHtml += td(q > 0 ? fmt(q) : '', 'right');
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
                        if (itemAmt === 0 && items.length === 1 && debit > 0) {
                            itemAmt = debit;
                        }
                        if (itemAmt === 0 && itemQty > 0 && itemRate > 0) {
                            itemAmt = itemQty * itemRate;
                        }
                        grandQty += itemQty;

                        var itemDetailsHtml = '<strong>' + (item.item_name || 'Item') + '</strong>';
                        if (itemRate > 0) {
                            itemDetailsHtml += ' <span style="color:#475569;font-size:11px;">(Price: ' + fmt(itemRate) + ', Qty: ' + fmt(itemQty) + ', Total: ' + fmt(itemAmt) + ')</span>';
                        } else if (itemQty > 0) {
                            itemDetailsHtml += ' <span style="color:#475569;font-size:11px;">(Qty: ' + fmt(itemQty) + ')</span>';
                        }
                        if (n(item.item_discount) > 0) {
                            itemDetailsHtml += '<br><small style="color:#ea580c;">↳ Disc: &minus;' + fmt(item.item_discount) + '</small>';
                        }

                        var borderStyle = idx === 0 ? 'border-top: 1.5px solid #cbd5e1; background: #fffde7;' : 'border-top: 1px dashed #e2e8f0; background: #fffffa;';

                        bodyHtml += '<tr class="r-sale" style="' + borderStyle + '">';
                        if (idx === 0) {
                            /* Main invoice parameters on 1st item row */
                            bodyHtml += td(rowNum++, 'center');
                            bodyHtml += td(header.vno ? '<strong>' + header.vno + '</strong>' : '', 'center');
                            bodyHtml += td('<span class="badge badge-primary" style="font-size:10px;padding:3px 6px;">SI</span>', 'center');
                            bodyHtml += td(header.date || '', 'center');
                            bodyHtml += td(refVal, 'center');
                            bodyHtml += td(itemDetailsHtml, 'left');
                            bodyHtml += td(itemQty > 0 ? fmt(itemQty) : '', 'right');
                            bodyHtml += td(itemAmt > 0 ? '<strong style="color:#c62828;">' + fmt(itemAmt) + '</strong>' : (debit > 0 ? '<strong style="color:#c62828;">' + fmt(debit) + '</strong>' : ''), 'right');
                            bodyHtml += td('', 'right');
                            bodyHtml += td(header.balance !== null && header.balance !== undefined ? balHtml(header.balance) : '', 'right');
                        } else {
                            /* Wrapped sub-rows for item 2, item 3, etc. */
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td(itemDetailsHtml, 'left');
                            bodyHtml += td(itemQty > 0 ? fmt(itemQty) : '', 'right');
                            bodyHtml += td(itemAmt > 0 ? '<strong style="color:#c62828;">' + fmt(itemAmt) + '</strong>' : '', 'right');
                            bodyHtml += td('', 'right');
                            bodyHtml += td('', 'right');
                        }
                        bodyHtml += '</tr>';
                    });

                    /* Optional Additional Discount / Freight rows if any */
                    if (saleTotal) {
                        var addDisc  = n(saleTotal.add_disc);
                        var extraChg = n(saleTotal.extra_chg);
                        if (addDisc > 0) {
                            bodyHtml += '<tr class="r-sale" style="border-top:1px dashed #e2e8f0; background: #fffffa;">';
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('<small style="color:#e65100;font-weight:700;">↳ Additional Discount</small>', 'left');
                            bodyHtml += td('', 'right');
                            bodyHtml += td('<small style="color:#bf360c;font-weight:800;">&minus; ' + fmt(addDisc) + '</small>', 'right');
                            bodyHtml += td('', 'right');
                            bodyHtml += td('', 'right');
                            bodyHtml += '</tr>';
                        }
                        if (extraChg > 0) {
                            bodyHtml += '<tr class="r-sale" style="border-top:1px dashed #e2e8f0; background: #fffffa;">';
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('', 'center');
                            bodyHtml += td('<small style="color:#1b5e20;font-weight:700;">↳ Extra Charges (Freight)</small>', 'left');
                            bodyHtml += td('', 'right');
                            bodyHtml += td('<small style="color:#1b5e20;font-weight:800;">+ ' + fmt(extraChg) + '</small>', 'right');
                            bodyHtml += td('', 'right');
                            bodyHtml += td('', 'right');
                            bodyHtml += '</tr>';
                        }
                    }
                }

                continue;
            }

            /* ── REGULAR NON-SALE TRANSACTIONS (Receipt, PV, SR, JV) ── */
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
            if      (t.row_type === 'receipt')         typeBadge = '<span class="badge badge-success" style="font-size:10px;padding:3px 6px;">PA</span>';
            else if (t.row_type === 'payment_voucher') typeBadge = '<span class="badge badge-warning text-dark" style="font-size:10px;padding:3px 6px;">PV</span>';
            else if (t.row_type === 'return')          typeBadge = '<span class="badge badge-danger" style="font-size:10px;padding:3px 6px;">SR</span>';
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
            bodyHtml += td(t.qty  && n(t.qty)  > 0 ? fmt(t.qty)  : '', 'right');                  // 7. Qty
            bodyHtml += td(debit  > 0 ? '<strong style="color:#c62828;">' + fmt(debit)  + '</strong>' : '', 'right'); // 8. Debit
            bodyHtml += td(credit > 0 ? '<strong style="color:#2e7d32;">' + fmt(credit) + '</strong>' : '', 'right');// 9. Credit
            bodyHtml += td(t.balance !== null && t.balance !== undefined ? balHtml(t.balance) : '', 'right');          // 10. Balance
            bodyHtml += '</tr>';

            i++;
        }

        /* ── footer HTML ── */
        var finalBal = n(res.closing_balance);

        /* Overall Sum row */
        var footHtml = '<tr class="r-total">';
        footHtml += '<td colspan="6" style="text-align:right;border:1px solid #ccc;padding:6px 10px;font-size:13px;"><strong>Total Sum (All Transactions)</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;font-size:13px;font-weight:800;color:#1a1a2e;background:#eaf4ff;">' + fmt(grandQty) + '</td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;"><strong style="color:#c62828;">' + fmt(grandDr) + '</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;"><strong style="color:#2e7d32;">' + fmt(grandCr) + '</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;">' + balHtml(finalBal) + '</td>';
        footHtml += '</tr>';

        /* Closing Balance row */
        footHtml += '<tr class="r-close">';
        footHtml += '<td colspan="9" style="text-align:right;border:1px solid #333;padding:8px 12px;font-size:13px;letter-spacing:.4px;"><strong>CLOSING BALANCE</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #333;padding:8px 10px;font-size:15px;">' + balHtml(finalBal) + '</td>';
        footHtml += '</tr>';

        /* ── inject into DOM ── */
        $('#ledgerBody').html(bodyHtml);
        $('#ledgerFooter').html(footHtml);

        $('#ledgerBox').show();
        $('#printBtnWrap').show();
    }

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
          filename:     'customer_ledger_' + new Date().toISOString().slice(0,10) + '.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
            var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            // Try Web Share API first for direct file sharing
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Customer Ledger',
                    text: 'Please find the attached customer ledger.',
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
            var msg = "*Customer Ledger*\nPlease find the attached PDF document.";
            var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
            window.open(waUrl, '_blank');
        });
    }
    /* ---------- Export Options & PDF ---------- */
    window.showExportOptions = function() {
        Swal.fire({
            title: 'Export Customer Ledger',
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
          filename:     'customer_ledger_' + new Date().toISOString().slice(0,10) + '.pdf',
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
        var rows = [['Date','V NO','Bill','DC No','Gate Pass','Description / Item','Qty','Rate','Debit','Credit','Balance']];
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
        a.download = 'customer_ledger_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
    };
</script>
@endsection
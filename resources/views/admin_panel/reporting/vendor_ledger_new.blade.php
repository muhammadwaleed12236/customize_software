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
                                <select id="branch_id" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                    <option value="">-- Choose Branch --</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name ?? $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" id="branch_id" value="{{ $user->branch_id }}">
                        @endif

                        <div class="col-md-{{ ($user && $user->hasRole('super admin')) ? '3' : '4' }}">
                            <label class="f-label">Select Vendor</label>
                            <select id="vendor_id" class="form-control form-control-sm select2">
                                <option value="">-- Choose Vendor --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
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
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th class="text-center" style="width: 75px;">Inv No.</th>
                                    <th class="text-center" style="width: 50px;">Type</th>
                                    <th class="text-center" style="width: 75px;">Date</th>
                                    <th class="text-center" style="width: 75px;">Ref</th>
                                    <th>Details (تفصیل)</th>
                                    <th class="text-right" style="width: 85px; background: #0f2b48 !important;">Price (قیمت)</th>
                                    <th class="text-right" style="width: 70px; background: #064e3b !important;" title="Purchased Quantity">Qty (لیے)</th>
                                    <th class="text-right" style="width: 95px;">Debit (نام)</th>
                                    <th class="text-right" style="width: 95px;">Credit (قیمت/جمع)</th>
                                    <th class="text-right" style="width: 105px;">Balance (بقیہ)</th>
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
tr.r-sale    td { background:#fefce8 !important; color:#1e293b !important; font-weight:700; border-color:#fef08a !important; }
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

@media print {
    .card, button, form, #printBtnWrap { display:none !important; }
    #ledgerBox, #printArea { display:block !important; }
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function () {

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

    /* 11 visible columns only */
    function td(txt, align, attrs) {
        align = align || 'center';
        attrs = attrs || '';
        var val = (txt !== null && txt !== undefined && txt !== '') ? txt : dash();
        return '<td style="text-align:' + align + ';border:1px solid #ddd;" ' + attrs + '>' + val + '</td>';
    }

    /* ---------- branch -> vendor loader (super admin) ---------- */
    $('#branch_id').on('change', function() {
        var bid = $(this).val();
        if (!bid) {
            $('#vendor_id').html('<option value="">-- Choose Vendor --</option>');
            return;
        }

        // Fetch vendors for this branch
        $.get("{{ route('vendors-by-branch') }}", { branch_id: bid }, function(res) {
            var html = '<option value="">-- Choose Vendor --</option>';
            $.each(res, function(i, v) {
                html += '<option value="' + v.id + '">' + v.customer_name + '</option>';
            });
            $('#vendor_id').html(html).trigger('change');
        });
    });

    /* ---------- search ---------- */
    $('#btnSearch').on('click', function () {
        var bid   = $('#branch_id').val();
        var vid   = $('#vendor_id').val();
        var start = $('#start_date').val();
        var end   = $('#end_date').val();

        if (!vid || !start || !end) { alert('Please fill all fields.'); return; }
        if ($('#branch_id').length && !bid) { alert('Please select a branch.'); return; }

        $('#loader').show();
        $('#ledgerBox').hide();
        $('#printBtnWrap').hide();

        $.get("{{ route('report.vendor.ledger.fetch.new') }}", {
            branch_id  : bid,
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
                            bodyHtml += td(itemRate > 0 ? '<strong style="color:#1e3a5f;">' + fmt(itemRate) + '</strong>' : '', 'right');
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
                            bodyHtml += td(itemRate > 0 ? '<strong style="color:#1e3a5f;">' + fmt(itemRate) + '</strong>' : '', 'right');
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
                            bodyHtml += td('', 'right');
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
            bodyHtml += td(t.rate && n(t.rate) > 0 ? fmt(t.rate) : '', 'right');                 // 7. Price
            bodyHtml += td(t.qty  && n(t.qty)  > 0 ? fmt(t.qty)  : '', 'right');                  // 8. Qty
            bodyHtml += td(debit  > 0 ? '<strong style="color:#c62828;">' + fmt(debit)  + '</strong>' : '', 'right'); // 9. Debit
            bodyHtml += td(credit > 0 ? '<strong style="color:#2e7d32;">' + fmt(credit) + '</strong>' : '', 'right');// 10. Credit
            bodyHtml += td(t.balance !== null && t.balance !== undefined ? balHtml(t.balance) : '', 'right');          // 11. Balance
            bodyHtml += '</tr>';

            i++;
        }

        /* ── footer HTML ── */
        var finalBal = n(res.closing_balance);

        /* Overall Sum row */
        var footHtml = '<tr class="r-total">';
        footHtml += '<td colspan="6" style="text-align:right;border:1px solid #ccc;padding:6px 10px;font-size:13px;"><strong>Total Sum (All Transactions)</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;">&#8212;</td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;font-size:13px;font-weight:800;color:#1a1a2e;background:#eaf4ff;">' + fmt(grandQty) + '</td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;"><strong style="color:#c62828;">' + fmt(grandDr) + '</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;"><strong style="color:#2e7d32;">' + fmt(grandCr) + '</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;">' + balHtml(finalBal) + '</td>';
        footHtml += '</tr>';

        /* Closing Balance row */
        footHtml += '<tr class="r-close">';
        footHtml += '<td colspan="10" style="text-align:right;border:1px solid #333;padding:8px 12px;font-size:13px;letter-spacing:.4px;"><strong>CLOSING BALANCE</strong></td>';
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
        var rows = [['No','Inv No','Type','Date','Ref','Details','Price','Qty','Debit','Credit','Balance']];
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

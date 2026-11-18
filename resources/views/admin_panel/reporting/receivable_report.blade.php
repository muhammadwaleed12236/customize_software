@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --ar-navy:       #1e3a5f;
        --ar-navy-dark:  #0f1f38;
        --ar-navy-light: #2c5282;
        --ar-gold:       #c8973a;
        --ar-emerald:    #0d9f6e;
        --ar-border:     #e2e8f0;
    }

    .rpt-wrapper { padding: 12px 0 30px 0; font-family: 'Inter', system-ui, -apple-system, sans-serif; }

    .rpt-header-bar {
        background: linear-gradient(135deg, var(--ar-navy-dark) 0%, var(--ar-navy) 60%, var(--ar-navy-light) 100%);
        border-radius: 10px; padding: 16px 22px; color: #fff;
        display: flex; align-items: center; justify-content: space-between; gap: 15px;
        box-shadow: 0 4px 15px rgba(15,31,56,.15); margin-bottom: 18px;
    }
    .rpt-header-icon {
        width: 44px; height: 44px; border-radius: 9px;
        background: rgba(255,255,255,.12); display: flex; align-items: center;
        justify-content: center; font-size: 20px; color: var(--ar-gold);
        border: 1px solid rgba(200,151,58,.3); flex-shrink: 0;
    }
    .rpt-header-title { font-size: 18px; font-weight: 800; color: #fff !important; margin: 0; }
    .rpt-header-sub   { font-size: 12px; color: rgba(255,255,255,.85); margin-top: 3px; }

    .f-label {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        color: #475569; letter-spacing: .03em; margin-bottom: 4px; display: block;
    }

    /* 4 KPI Cards: Opening, Sale, Received, Receivable */
    .ar-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; }
    @media(max-width: 992px){ .ar-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media(max-width: 576px){ .ar-kpi-grid { grid-template-columns: 1fr; } }

    .ar-kpi-card {
        background: #fff; border-radius: 8px; padding: 14px 18px;
        border: 1px solid var(--ar-border); box-shadow: 0 2px 6px rgba(0,0,0,.03);
        display: flex; align-items: center; justify-content: space-between;
    }
    .ar-kpi-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing:.04em; margin-bottom: 3px; }
    .ar-kpi-val   { font-size: 20px; font-weight: 800; color: var(--ar-navy); font-family: monospace; }
    .ar-kpi-val.opening    { color: #475569; }
    .ar-kpi-val.sale       { color: #0284c7; }
    .ar-kpi-val.received   { color: #059669; }
    .ar-kpi-val.receivable { color: #1e3a5f; }
    .ar-kpi-icon  { width: 42px; height: 42px; border-radius: 8px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink: 0; }

    /* Table */
    #arTable th {
        background: #0f1f38 !important; color: #fff !important;
        font-size: 11.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .03em; padding: 10px 8px; border: 1px solid #1e3a5f;
        white-space: nowrap; vertical-align: middle;
    }
    #arTable td { font-size: 12.5px; vertical-align: middle; padding: 8px 10px; }
    #arTable tbody tr:hover { background: #f8fafc; }

    .cust-name-link { color: #0f52ba; text-decoration: none; font-weight: 700; }
    .cust-name-link:hover { color: #1e3a5f; text-decoration: underline; }

    .tfoot-row td { background: #1e3a5f !important; color: #fff !important; font-weight: 800; font-family: monospace; font-size: 13px; }

    .search-filter-input {
        border-radius: 6px; border: 1.5px solid #cbd5e1; height: 38px; font-size: 12px; padding: 6px 12px;
    }
    .search-filter-input:focus { border-color: var(--ar-navy); box-shadow: 0 0 0 2px rgba(30,58,95,.15); }

    /* Print Styles */
    @media print {
        .rpt-header-bar .btn, #filterCard, .no-print, .main-header, .sidebar, .footer { display: none !important; }
        .rpt-header-bar { background: #0f1f38 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        #arTable th    { background: #0f1f38 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .tfoot-row td  { background: #1e3a5f !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .main-content  { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        body { font-size: 10px !important; }
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">

            {{-- ── Header Bar ──────────────────────────────── --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon"><i class="fas fa-hand-holding-usd"></i></div>
                    <div>
                        <h4 class="rpt-header-title">Customer Receivable Report</h4>
                        <div class="rpt-header-sub">
                            <i class="fas fa-file-invoice-dollar mr-1" style="color:var(--ar-gold);"></i>
                            Opening, Sale, Received &amp; Receivable Summary
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="window.print()" class="btn btn-sm btn-outline-light font-weight-bold no-print">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                    <button onclick="exportCSV()" class="btn btn-sm btn-light font-weight-bold text-dark border no-print">
                        <i class="fas fa-file-csv mr-1 text-success"></i> Export CSV
                    </button>
                </div>
            </div>

            {{-- ── Filter Card ──────────────────────────────── --}}
            <div class="card shadow-sm mb-3 border-0 no-print" id="filterCard" style="border-radius:9px; border:1px solid var(--ar-border) !important;">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-end">

                        @if($isSuper)
                        <div class="col-md-2">
                            <label class="f-label">Branch</label>
                            <select id="branch_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Branches --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" id="branch_id" value="{{ $defaultBranchId }}">
                        @endif

                        <div class="col-md-{{ $isSuper ? '2' : '3' }}">
                            <label class="f-label">Zone / Area</label>
                            <select id="zone" class="form-control form-control-sm select2">
                                <option value="all">-- All Zones --</option>
                                @foreach($zones as $z)
                                    <option value="{{ $z }}">{{ $z }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-{{ $isSuper ? '3' : '3' }}">
                            <label class="f-label">Customer</label>
                            <select id="customer_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Customers --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->customer_name }} @if($c->customer_id)({{ $c->customer_id }})@endif</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="f-label">Date From</label>
                            <input type="date" id="date_from" class="form-control form-control-sm search-filter-input"
                                   value="{{ date('Y-m-01') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="f-label">Date To</label>
                            <input type="date" id="date_to" class="form-control form-control-sm search-filter-input"
                                   value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-{{ $isSuper ? '1' : '2' }}">
                            <button type="button" id="btnGenerate" onclick="loadReport()" class="btn btn-sm btn-primary font-weight-bold w-100"
                                    style="height:38px; border-radius:6px; background:var(--ar-navy); border-color:var(--ar-navy);">
                                <i class="fas fa-search mr-1" id="refreshIcon"></i> Search
                            </button>
                        </div>

                        <div class="col-md-12 d-flex align-items-center gap-2 mt-2 pt-2 border-top">
                            <span class="badge badge-primary px-2 py-1" id="branchBadge" style="font-size:11px;">All Branches</span>
                            <span class="badge badge-light border text-muted px-2 py-1" id="periodBadge" style="font-size:11px;">Period</span>
                            <span class="badge badge-secondary px-2 py-1" id="totalRowsBadge" style="font-size:11px;">0 records</span>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── 4 KPI Cards: Opening, Sale, Received, Receivable ─────── --}}
            <div id="summarySection" style="display:none;">
                <div class="ar-kpi-grid">
                    {{-- 1. Opening --}}
                    <div class="ar-kpi-card">
                        <div>
                            <div class="ar-kpi-label">Opening</div>
                            <div class="ar-kpi-val opening" id="kpiOpening">Rs. 0.00</div>
                            <small class="text-muted" style="font-size:10px;">Balance before Date From</small>
                        </div>
                        <div class="ar-kpi-icon" style="background:#f1f5f9;color:#475569;"><i class="fas fa-wallet"></i></div>
                    </div>

                    {{-- 2. Sale --}}
                    <div class="ar-kpi-card">
                        <div>
                            <div class="ar-kpi-label">Sale / Debit</div>
                            <div class="ar-kpi-val sale" id="kpiSale">Rs. 0.00</div>
                            <small class="text-muted" style="font-size:10px;">Invoiced in period</small>
                        </div>
                        <div class="ar-kpi-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-file-invoice"></i></div>
                    </div>

                    {{-- 3. Received --}}
                    <div class="ar-kpi-card">
                        <div>
                            <div class="ar-kpi-label">Received / Credit</div>
                            <div class="ar-kpi-val received" id="kpiReceived">Rs. 0.00</div>
                            <small class="text-muted" style="font-size:10px;">Payments received in period</small>
                        </div>
                        <div class="ar-kpi-icon" style="background:#dcfce7;color:#059669;"><i class="fas fa-receipt"></i></div>
                    </div>

                    {{-- 4. Receivable --}}
                    <div class="ar-kpi-card">
                        <div>
                            <div class="ar-kpi-label">Receivable</div>
                            <div class="ar-kpi-val receivable" id="kpiReceivable">Rs. 0.00</div>
                            <small class="text-muted" style="font-size:10px;">Net Closing Balance</small>
                        </div>
                        <div class="ar-kpi-icon" style="background:#fef3c7;color:#b45309;"><i class="fas fa-hand-holding-usd"></i></div>
                    </div>
                </div>
            </div>

            {{-- ── Loader ────────────────────────────────────────── --}}
            <div id="loader" style="display:none; text-align:center; padding:50px 0;">
                <div class="spinner-border text-primary" role="status" style="width:3rem; height:3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <div class="mt-2 font-weight-bold text-muted">Calculating customer receivables...</div>
            </div>

            {{-- ── Placeholder ───────────────────────────────────── --}}
            <div id="placeholder" class="text-center py-5 text-muted" style="background:#fff; border-radius:8px; border:1px dashed #cbd5e1;">
                <i class="fas fa-chart-line fa-3x mb-3 text-secondary" style="opacity:0.4;"></i>
                <h5 class="font-weight-bold">Customer Receivable Report</h5>
                <p class="mb-3 small">Click <strong>"Search"</strong> to generate the report.</p>
                <button type="button" onclick="loadReport()" class="btn btn-sm btn-primary font-weight-bold px-3">
                    <i class="fas fa-search mr-1"></i> Generate Report
                </button>
            </div>

            {{-- ── Table Wrapper ─────────────────────────────────── --}}
            <div id="tableWrapper" style="display:none;">
                <div class="card shadow-sm border-0" style="border-radius:8px; overflow:hidden;">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="arTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:40px;">#</th>
                                    <th style="width:110px;">Code</th>
                                    <th>Customer Name</th>
                                    <th style="width:120px;">Branch</th>
                                    <th style="width:110px;">Zone</th>
                                    <th class="text-end" style="width:130px;">Opening Bal</th>
                                    <th class="text-end" style="width:130px;">Sales / Debit</th>
                                    <th class="text-end" style="width:130px;">RECEIVED</th>
                                    <th class="text-end" style="width:140px; background:#1e3a5f !important;">Receivable (Rs.)</th>
                                </tr>
                            </thead>
                            <tbody id="arBody">
                            </tbody>
                            <tfoot>
                                <tr class="tfoot-row">
                                    <td colspan="5" class="text-end font-weight-bold">Grand Total:</td>
                                    <td class="text-end" id="footOpening">Rs. 0.00</td>
                                    <td class="text-end" id="footDebit">Rs. 0.00</td>
                                    <td class="text-end" id="footCredit">Rs. 0.00</td>
                                    <td class="text-end" id="footClosing" style="font-size:14px; background:#0f1f38 !important;">Rs. 0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
let rawReportData = [];

document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }
    // Auto load on page load
    loadReport();
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
    const zoneEl = document.getElementById('zone');
    if (zoneEl && zoneEl.value && zoneEl.value !== 'all') {
        params.append('zone', zoneEl.value);
    }
    const custEl = document.getElementById('customer_id');
    if (custEl && custEl.value && custEl.value !== 'all') {
        params.append('customer_id', custEl.value);
    }
    params.append('date_from', document.getElementById('date_from').value);
    params.append('date_to',   document.getElementById('date_to').value);

    document.getElementById('loader').style.display       = 'block';
    document.getElementById('placeholder').style.display  = 'none';
    document.getElementById('tableWrapper').style.display = 'none';
    document.getElementById('summarySection').style.display = 'none';

    fetch('{{ route("report.receivable.fetch") }}?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loader').style.display = 'none';
        rawReportData = data.rows || [];
        renderTable(data);
    })
    .catch(err => {
        document.getElementById('loader').style.display = 'none';
        alert('Error loading receivable report: ' + err.message);
    });
}

function renderTable(data) {
    const rows = data.rows || [];
    const kpi  = data.kpi || {};
    const meta = data.meta || {};
    const tbody = document.getElementById('arBody');
    tbody.innerHTML = '';

    // Update 4 KPI Cards: Opening, Sale, Received, Receivable
    document.getElementById('kpiOpening').textContent    = 'Rs. ' + fmt(kpi.grand_opening || 0);
    document.getElementById('kpiSale').textContent       = 'Rs. ' + fmt(kpi.grand_debit || 0);
    document.getElementById('kpiReceived').textContent   = 'Rs. ' + fmt(kpi.grand_credit || 0);
    document.getElementById('kpiReceivable').textContent = 'Rs. ' + fmt(kpi.grand_closing !== undefined ? kpi.grand_closing : (kpi.net_outstanding || 0));

    // Update Badges
    document.getElementById('branchBadge').textContent = meta.branch_label || 'All Branches';
    document.getElementById('periodBadge').textContent = (meta.date_from ? meta.date_from + ' to ' : 'As of ') + (meta.date_to || 'Today');
    document.getElementById('totalRowsBadge').textContent = rows.length + ' customers';

    if (rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-5 font-weight-bold">No records found matching the selected criteria.</td></tr>';
        document.getElementById('tableWrapper').style.display   = 'block';
        document.getElementById('summarySection').style.display = 'block';
        return;
    }

    let html = '';

    rows.forEach((r, i) => {
        // WhatsApp / Phone
        let phoneHtml = '';
        if (r.mobile) {
            phoneHtml = `<div class="small text-muted d-flex align-items-center gap-1 mt-1">
                <i class="fas fa-phone-alt" style="font-size:10px;"></i> ${r.mobile}`;
            if (r.whatsapp_phone) {
                const msg = encodeURIComponent(`Assalam o Alaikum ${r.customer_name}, your outstanding receivable balance is Rs. ${fmt(r.closing_balance)}.`);
                phoneHtml += ` <a href="https://wa.me/${r.whatsapp_phone}?text=${msg}" target="_blank" class="text-success ml-1 no-print" title="Send WhatsApp Reminder"><i class="fab fa-whatsapp"></i></a>`;
            }
            phoneHtml += `</div>`;
        }

        // Formatted amounts
        const obFmt  = r.opening_balance !== 0 ? 'Rs. ' + fmt(r.opening_balance) : '<span class="text-muted">0.00</span>';
        const drFmt  = r.period_debit > 0 ? '<span style="color:#0284c7;font-weight:700;">Rs. ' + fmt(r.period_debit) + '</span>' : '<span class="text-muted">—</span>';
        const crFmt  = r.period_credit > 0 ? '<span style="color:#059669;font-weight:700;">Rs. ' + fmt(r.period_credit) + '</span>' : '<span class="text-muted">—</span>';

        let balClass = 'text-muted';
        let balStyle = 'font-weight:700;';
        if (r.closing_balance > 0) {
            balClass = 'text-dark';
            balStyle = 'font-weight:800; font-size:13px; color:#1e3a5f !important;';
        } else if (r.closing_balance < 0) {
            balClass = 'text-warning text-dark';
            balStyle = 'font-weight:800; font-size:13px; color:#b45309 !important;';
        }

        const balFmt = '<span class="' + balClass + '" style="' + balStyle + '">Rs. ' + fmt(r.closing_balance) + '</span>';

        html += `<tr data-customer="${(r.customer_name + ' ' + (r.code||'') + ' ' + (r.mobile||'') + ' ' + (r.zone||'')).toLowerCase()}">
            <td class="text-center text-muted" style="font-size:11px;">${i+1}</td>
            <td style="font-family:monospace;font-size:11px;font-weight:700;color:#1e3a5f;">${r.code || '—'}</td>
            <td>
                <div>
                    <a href="${r.ledger_url}" target="_blank" class="cust-name-link" title="Open Customer Ledger">
                        ${r.customer_name}
                    </a>
                </div>
                ${phoneHtml}
            </td>
            <td style="font-size:11.5px;color:#475569;">${r.branch_name}</td>
            <td style="font-size:11.5px;color:#475569;">${r.zone}</td>
            <td class="text-end" style="font-family:monospace;">${obFmt}</td>
            <td class="text-end" style="font-family:monospace;">${drFmt}</td>
            <td class="text-end" style="font-family:monospace;">${crFmt}</td>
            <td class="text-end" style="font-family:monospace; background:rgba(30,58,95,0.03);">${balFmt}</td>
        </tr>`;
    });

    tbody.innerHTML = html;

    // Footers
    document.getElementById('footOpening').textContent = 'Rs. ' + fmt(kpi.grand_opening || 0);
    document.getElementById('footDebit').textContent   = 'Rs. ' + fmt(kpi.grand_debit || 0);
    document.getElementById('footCredit').textContent  = 'Rs. ' + fmt(kpi.grand_credit || 0);
    document.getElementById('footClosing').textContent = 'Rs. ' + fmt(kpi.grand_closing !== undefined ? kpi.grand_closing : (kpi.net_outstanding || 0));

    document.getElementById('summarySection').style.display = 'block';
    document.getElementById('tableWrapper').style.display   = 'block';
}

function exportCSV() {
    if (!rawReportData || rawReportData.length === 0) {
        alert('No data to export.');
        return;
    }

    let csv = '#,Customer Code,Customer Name,Mobile,Branch,Zone,Opening Bal,Sales / Debit,RECEIVED,Receivable (Rs.)\n';

    rawReportData.forEach((r, idx) => {
        const line = [
            idx + 1,
            `"${(r.code || '').replace(/"/g, '""')}"`,
            `"${(r.customer_name || '').replace(/"/g, '""')}"`,
            `"${(r.mobile || '').replace(/"/g, '""')}"`,
            `"${(r.branch_name || '').replace(/"/g, '""')}"`,
            `"${(r.zone || '').replace(/"/g, '""')}"`,
            (r.opening_balance || 0).toFixed(2),
            (r.period_debit || 0).toFixed(2),
            (r.period_credit || 0).toFixed(2),
            (r.closing_balance || 0).toFixed(2)
        ].join(',');
        csv += line + '\n';
    });

    const gob = document.getElementById('footOpening').textContent;
    const gdr = document.getElementById('footDebit').textContent;
    const gcr = document.getElementById('footCredit').textContent;
    const gcl = document.getElementById('footClosing').textContent;

    csv += `"","","Grand Total","","","","${gob.replace('Rs. ','').trim()}","${gdr.replace('Rs. ','').trim()}","${gcr.replace('Rs. ','').trim()}","${gcl.replace('Rs. ','').trim()}"\n`;

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'customer_receivable_' + new Date().toISOString().slice(0, 10) + '.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection

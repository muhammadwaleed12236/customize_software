@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --tb-navy:       #1e3a5f;
        --tb-navy-dark:  #0f1f38;
        --tb-navy-light: #2c5282;
        --tb-gold:       #c8973a;
        --tb-emerald:    #0d9f6e;
        --tb-red:        #dc2626;
        --tb-border:     #e2e8f0;
    }

    .rpt-wrapper { padding: 12px 0 30px 0; font-family: 'Inter', system-ui, sans-serif; }

    .rpt-header-bar {
        background: linear-gradient(135deg, var(--tb-navy-dark) 0%, var(--tb-navy) 60%, var(--tb-navy-light) 100%);
        border-radius: 10px; padding: 16px 22px; color: #fff;
        display: flex; align-items: center; justify-content: space-between; gap: 15px;
        box-shadow: 0 4px 15px rgba(15,31,56,.15); margin-bottom: 18px;
    }
    .rpt-header-icon {
        width: 44px; height: 44px; border-radius: 9px;
        background: rgba(255,255,255,.12); display: flex; align-items: center;
        justify-content: center; font-size: 20px; color: var(--tb-gold);
        border: 1px solid rgba(200,151,58,.3); flex-shrink: 0;
    }
    .rpt-header-title { font-size: 18px; font-weight: 800; color: #fff !important; margin: 0; }
    .rpt-header-sub   { font-size: 12px; color: rgba(255,255,255,.85); margin-top: 3px; }

    .f-label {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        color: #475569; letter-spacing: .03em; margin-bottom: 4px; display: block;
    }

    /* KPI Cards */
    .tb-kpi-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; margin-bottom: 18px; }
    @media(max-width:768px){ .tb-kpi-grid { grid-template-columns: 1fr; } }
    .tb-kpi-card {
        background: #fff; border-radius: 8px; padding: 14px 18px;
        border: 1px solid var(--tb-border); box-shadow: 0 2px 6px rgba(0,0,0,.03);
        display: flex; align-items: center; justify-content: space-between;
    }
    .tb-kpi-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing:.04em; margin-bottom:2px; }
    .tb-kpi-val   { font-size: 20px; font-weight: 800; color: var(--tb-navy); font-family: monospace; }
    .tb-kpi-val.emerald { color: #047857; }
    .tb-kpi-val.crimson { color: var(--tb-red); }
    .tb-kpi-icon  { width: 38px; height: 38px; border-radius: 7px; display:flex; align-items:center; justify-content:center; font-size:16px; }

    /* Table */
    #tbTable th {
        background: #0f1f38 !important; color: #fff !important;
        font-size: 11.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; padding: 10px 8px; border: 1px solid #1e3a5f;
        white-space: nowrap;
    }
    #tbTable td { font-size: 12px; vertical-align: middle; padding: 7px 8px; }
    #tbTable tbody tr:hover { background: #f8fafc; }

    .balanced-badge   { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
    .unbalanced-badge { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }

    .tfoot-row td { background: #1e3a5f !important; color: #fff !important; font-weight: 800; font-family: monospace; font-size: 13px; }

    /* Print */
    @media print {
        .rpt-header-bar .btn, #filterCard, .no-print { display: none !important; }
        .rpt-header-bar { background: #0f1f38 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        #tbTable th    { background: #0f1f38 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .tfoot-row td  { background: #1e3a5f !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .main-content  { margin: 0 !important; padding: 0 !important; }
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">

            {{-- ── Header Bar ──────────────────────────────── --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon"><i class="fas fa-balance-scale"></i></div>
                    <div>
                        <h4 class="rpt-header-title">Trial Balance</h4>
                        <div class="rpt-header-sub">
                            <i class="fas fa-chart-bar mr-1" style="color:var(--tb-gold);"></i>
                            Chart of Accounts — Debit &amp; Credit Summary &mdash; ERP System
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="window.print()" class="btn btn-sm btn-outline-light font-weight-bold no-print">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                    <button onclick="exportCSV()" class="btn btn-sm btn-light font-weight-bold text-dark border no-print">
                        <i class="fas fa-file-csv mr-1 text-success"></i> CSV
                    </button>
                </div>
            </div>

            {{-- ── Filter Card ──────────────────────────────── --}}
            <div class="card shadow-sm mb-3 border-0 no-print" id="filterCard" style="border-radius:9px; border:1px solid var(--tb-border) !important;">
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
                        @endif

                        <div class="col-md-{{ $isSuper ? '3' : '3' }}">
                            <label class="f-label">Select Account</label>
                            <select id="account_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Accounts --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->head_name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="f-label">Date From</label>
                            <input type="date" id="date_from" class="form-control form-control-sm"
                                   style="height:38px; border-radius:6px; border:1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">Date To</label>
                            <input type="date" id="date_to" class="form-control form-control-sm"
                                   style="height:38px; border-radius:6px; border:1.5px solid #cbd5e1;"
                                   value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-{{ $isSuper ? '2' : '2' }}">
                            <label class="f-label">Zero Balance</label>
                            <select id="hide_zero" class="form-control form-control-sm">
                                <option value="1">Hide Zero</option>
                                <option value="0">Show All</option>
                            </select>
                        </div>

                        <div class="col-md-{{ $isSuper ? '1' : '2' }}">
                            <button type="button" id="btnSearch" onclick="loadReport()" class="btn btn-sm btn-primary w-100 font-weight-bold"
                                    style="height:38px; border-radius:6px; background:var(--tb-navy); border-color:var(--tb-navy); padding-left:4px; padding-right:4px;">
                                <i class="fas fa-search mr-1"></i> Generate
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── KPI Summary ──────────────────────────────── --}}
            <div id="summarySection" style="display:none;">
                <div class="tb-kpi-grid">
                    <div class="tb-kpi-card">
                        <div>
                            <div class="tb-kpi-label">Total Debit Balance</div>
                            <div class="tb-kpi-val emerald" id="kpiDebit">0.00</div>
                        </div>
                        <div class="tb-kpi-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-arrow-down"></i></div>
                    </div>
                    <div class="tb-kpi-card">
                        <div>
                            <div class="tb-kpi-label">Total Credit Balance</div>
                            <div class="tb-kpi-val crimson" id="kpiCredit">0.00</div>
                        </div>
                        <div class="tb-kpi-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-arrow-up"></i></div>
                    </div>
                    <div class="tb-kpi-card">
                        <div>
                            <div class="tb-kpi-label">Tally Status</div>
                            <div id="kpiStatus"><span class="balanced-badge"><i class="fas fa-check-circle mr-1"></i> Balanced</span></div>
                        </div>
                        <div class="tb-kpi-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-balance-scale"></i></div>
                    </div>
                </div>
            </div>

            {{-- ── Table ────────────────────────────────────── --}}
            <div class="card shadow-sm border-0" style="border-radius:9px; border:1px solid var(--tb-border) !important;">
                <div class="card-body p-3">

                    {{-- Loader --}}
                    <div id="loader" style="display:none;text-align:center;padding:40px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Calculating balances...</p>
                    </div>

                    {{-- Placeholder --}}
                    <div id="placeholder" class="text-center py-5 text-muted">
                        <i class="fas fa-balance-scale fa-3x mb-3" style="opacity:.3;"></i>
                        <p>Filters set kar ke <strong>Generate</strong> press karein.</p>
                    </div>

                    {{-- Table --}}
                    <div id="tableWrapper" style="display:none;" class="table-responsive">
                        <div class="d-flex justify-content-between align-items-center mb-2 no-print" id="tableTopBar">
                            <span id="recordCount" class="text-muted small font-weight-bold"></span>
                            <span id="periodLabel" class="badge badge-secondary" style="font-size:11px;"></span>
                        </div>
                        <table id="tbTable" class="table table-bordered mb-0" style="border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th style="width:35px;" class="text-center">#</th>
                                    <th style="width:90px;">Code</th>
                                    <th style="min-width:200px;">Account Title</th>
                                    <th style="min-width:140px;">Head / Group</th>
                                    <th style="min-width:120px;">Branch</th>
                                    <th class="text-end" style="width:140px;">Debit Balance</th>
                                    <th class="text-end" style="width:140px;">Credit Balance</th>
                                </tr>
                            </thead>
                            <tbody id="tbBody"></tbody>
                            <tfoot>
                                <tr class="tfoot-row">
                                    <td colspan="5" class="text-end" style="font-family:sans-serif; font-size:13px; letter-spacing:.05em;">
                                        GRAND TOTAL
                                    </td>
                                    <td class="text-end" id="footDebit">0.00</td>
                                    <td class="text-end" id="footCredit">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
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
    }

    // Branch change: dynamically update Accounts dropdown
    if (window.jQuery) {
        $('#branch_id').on('change', function() {
            updateAccountsByBranch(this.value);
        });
        $('#account_id').on('change', function() {
            loadReport();
        });
    } else {
        const branchEl = document.getElementById('branch_id');
        if (branchEl) {
            branchEl.addEventListener('change', function() {
                updateAccountsByBranch(this.value);
            });
        }
        document.getElementById('account_id')?.addEventListener('change', loadReport);
    }

    // Auto-set date_from to start of current year
    const now = new Date();
    document.getElementById('date_from').value = now.getFullYear() + '-01-01';

    document.getElementById('btnSearch').addEventListener('click', loadReport);
    document.getElementById('hide_zero').addEventListener('change', loadReport);

    // Auto load on initial page view
    loadReport();
});

function updateAccountsByBranch(branchId) {
    const accSelect = $('#account_id');
    accSelect.empty().append('<option value="all">-- All Accounts --</option>');

    const url = '{{ route("report.trial_balance.accounts") }}?branch_id=' + encodeURIComponent(branchId || 'all');
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(accounts => {
            accounts.forEach(acc => {
                accSelect.append(new Option(`${acc.title} (${acc.head_name})`, acc.id));
            });
            if (window.jQuery && $.fn.select2) {
                accSelect.trigger('change.select2');
            }
            loadReport();
        })
        .catch(err => {
            console.error('Error fetching accounts:', err);
            loadReport();
        });
}

function fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function loadReport() {
    const params = new URLSearchParams();
    const branchEl = document.getElementById('branch_id');
    if (branchEl && branchEl.value) {
        params.append('branch_id', branchEl.value);
    }
    const accEl = document.getElementById('account_id');
    if (accEl && accEl.value && accEl.value !== 'all') {
        params.append('account_id', accEl.value);
    }
    params.append('date_from',  document.getElementById('date_from').value);
    params.append('date_to',    document.getElementById('date_to').value);
    params.append('hide_zero',  document.getElementById('hide_zero').value);

    document.getElementById('loader').style.display      = 'block';
    document.getElementById('placeholder').style.display = 'none';
    document.getElementById('tableWrapper').style.display = 'none';
    document.getElementById('summarySection').style.display = 'none';

    fetch('{{ route("report.trial_balance.fetch") }}?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loader').style.display = 'none';
        renderTable(data);
    })
    .catch(err => {
        document.getElementById('loader').style.display = 'none';
        alert('Error: ' + err.message);
    });
}

function renderTable(data) {
    const rows  = data.rows || [];
    const tbody = document.getElementById('tbBody');
    tbody.innerHTML = '';

    if (rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4 font-weight-bold">Koi record nahi mila.</td></tr>';
        document.getElementById('tableWrapper').style.display = 'block';
        document.getElementById('summarySection').style.display = 'none';
        return;
    }

    let html = '';
    rows.forEach((r, i) => {
        const debitFmt  = r.debit_bal  > 0 ? '<span style="color:#047857;font-weight:700;font-family:monospace;">Rs. ' + fmt(r.debit_bal)  + '</span>' : '<span class="text-muted">—</span>';
        const creditFmt = r.credit_bal > 0 ? '<span style="color:#dc2626;font-weight:700;font-family:monospace;">Rs. ' + fmt(r.credit_bal) + '</span>' : '<span class="text-muted">—</span>';

        html += `<tr>
            <td class="text-center text-muted" style="font-size:11px;">${i+1}</td>
            <td style="font-family:monospace;font-size:11px;color:#64748b;">${r.account_code || '—'}</td>
            <td class="font-weight-bold" style="color:#1e3a5f;">${r.title}</td>
            <td><span class="badge badge-secondary" style="font-size:10px;padding:3px 8px;">${r.head_name}</span></td>
            <td style="font-size:11px;color:#64748b;">${r.branch_name}</td>
            <td class="text-end">${debitFmt}</td>
            <td class="text-end">${creditFmt}</td>
        </tr>`;
    });
    tbody.innerHTML = html;

    // Footer
    document.getElementById('footDebit').innerHTML  = '<span style="font-family:monospace;">Rs. ' + fmt(data.grand_debit)  + '</span>';
    document.getElementById('footCredit').innerHTML = '<span style="font-family:monospace;">Rs. ' + fmt(data.grand_credit) + '</span>';

    // KPI
    document.getElementById('kpiDebit').textContent  = 'Rs. ' + fmt(data.grand_debit);
    document.getElementById('kpiCredit').textContent = 'Rs. ' + fmt(data.grand_credit);

    const statusEl = document.getElementById('kpiStatus');
    if (data.is_balanced) {
        statusEl.innerHTML = '<span class="balanced-badge"><i class="fas fa-check-circle mr-1"></i> Balanced</span>';
    } else {
        const diff = Math.abs(data.grand_debit - data.grand_credit);
        statusEl.innerHTML = `<span class="unbalanced-badge"><i class="fas fa-exclamation-triangle mr-1"></i> Diff: Rs. ${fmt(diff)}</span>`;
    }

    // Labels
    const df = document.getElementById('date_from').value;
    const dt = document.getElementById('date_to').value;
    document.getElementById('periodLabel').textContent = (df || 'Start') + '  →  ' + (dt || 'Today');
    document.getElementById('recordCount').textContent  = rows.length + ' accounts';

    document.getElementById('summarySection').style.display = 'block';
    document.getElementById('tableWrapper').style.display   = 'block';
}

function exportCSV() {
    const rows  = document.querySelectorAll('#tbTable tbody tr');
    const gd    = document.getElementById('footDebit').textContent;
    const gc    = document.getElementById('footCredit').textContent;

    let csv = '#,Code,Account Title,Head / Group,Branch,Debit Balance,Credit Balance\n';
    rows.forEach(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length < 7) return;
        const line = Array.from(cells).map(c => '"' + c.textContent.trim().replace(/"/g, '""') + '"').join(',');
        csv += line + '\n';
    });
    csv += `"","","","","Grand Total","${gd.trim()}","${gc.trim()}"\n`;

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'trial_balance_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection

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
    @media print {
        .rpt-header-bar .btn, #filterCard, .no-print { display: none !important; }
        .rpt-header-bar { background: #0f1f38 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .bs-section-header { background: #0f1f38 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .bs-scroll-box { max-height: none !important; overflow: visible !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
    }
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
                    <button onclick="window.print()" class="btn btn-sm btn-outline-light font-weight-bold no-print">
                        <i class="fas fa-print mr-1"></i> Print
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
                <p>Filters set kar ke <strong>Generate</strong> press karein.</p>
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
}

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

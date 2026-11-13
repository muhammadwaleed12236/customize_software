@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --is-navy:       #1e3a5f;
        --is-navy-dark:  #0f1f38;
        --is-navy-light: #2c5282;
        --is-gold:       #c8973a;
        --is-emerald:    #0d9f6e;
        --is-red:        #dc2626;
        --is-border:     #e2e8f0;
    }

    .rpt-wrapper { padding: 8px 0 20px 0; font-family: 'Inter', system-ui, sans-serif; }

    .rpt-header-bar {
        background: linear-gradient(135deg, var(--is-navy-dark) 0%, var(--is-navy) 60%, var(--is-navy-light) 100%);
        border-radius: 8px; padding: 12px 18px; color: #fff;
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        box-shadow: 0 3px 12px rgba(15,31,56,.12); margin-bottom: 12px;
    }
    .rpt-header-icon {
        width: 38px; height: 38px; border-radius: 8px;
        background: rgba(255,255,255,.12); display: flex; align-items: center;
        justify-content: center; font-size: 18px; color: var(--is-gold);
        border: 1px solid rgba(200,151,58,.3); flex-shrink: 0;
    }
    .rpt-header-title { font-size: 16px; font-weight: 800; color: #fff !important; margin: 0; }
    .rpt-header-sub   { font-size: 11.5px; color: rgba(255,255,255,.85); margin-top: 2px; }

    .f-label {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        color: #475569; letter-spacing: .03em; margin-bottom: 3px; display: block;
    }

    /* KPI Cards - Compact */
    .is-kpi-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 12px; }
    @media(max-width: 992px){ .is-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media(max-width: 576px){ .is-kpi-grid { grid-template-columns: 1fr; } }

    .is-kpi-card {
        background: #fff; border-radius: 7px; padding: 9px 12px;
        border: 1px solid var(--is-border); box-shadow: 0 1px 4px rgba(0,0,0,.03);
        display: flex; align-items: center; justify-content: space-between;
    }
    .is-kpi-card.highlight-profit {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #a7f3d0;
    }
    .is-kpi-card.highlight-loss {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border-color: #fca5a5;
    }
    .is-kpi-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing:.04em; margin-bottom: 1px; }
    .is-kpi-val   { font-size: 16px; font-weight: 800; color: var(--is-navy); font-family: monospace; }
    .is-kpi-val.emerald { color: #047857; }
    .is-kpi-val.crimson { color: var(--is-red); }
    .is-kpi-icon  { width: 32px; height: 32px; border-radius: 6px; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink: 0; }

    /* Statement 2-Column Cards */
    .stmt-panel {
        background: #fff; border-radius: 8px; border: 1px solid var(--is-border);
        box-shadow: 0 1px 4px rgba(0,0,0,.03); margin-bottom: 8px; overflow: hidden;
    }
    .stmt-panel-grow {
        background: #fff; border-radius: 8px; border: 1px solid var(--is-border);
        box-shadow: 0 1px 4px rgba(0,0,0,.03); margin-bottom: 8px; overflow: hidden;
        display: flex; flex-direction: column; flex: 1;
    }
    .stmt-section-header {
        background: #0f1f38; color: #fff; padding: 6px 12px;
        font-size: 11.5px; font-weight: 800; text-transform: uppercase;
        letter-spacing: .04em; display: flex; justify-content: space-between; align-items: center;
    }
    .stmt-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 0; }
    .stmt-table td { padding: 6px 12px; border-bottom: 1px solid #f1f5f9; }
    .stmt-table tr:hover td { background: #f8fafc; }
    .stmt-table .item-name { color: #334155; font-weight: 500; }
    .stmt-table .item-sub  { color: #64748b; font-size: 11px; padding-left: 14px; }
    .stmt-table .amount-col { text-align: right; font-family: monospace; font-weight: 700; width: 170px; }
    .stmt-table .total-row td {
        background: #f8fafc; font-weight: 800; font-size: 12.5px;
        border-top: 1.5px solid #cbd5e1; border-bottom: 1.5px solid #cbd5e1;
        color: var(--is-navy);
    }

    /* Scrollable Expenses List - Flex to fill equal height to Left Column */
    .expenses-scroll-box {
        flex: 1; min-height: 220px; max-height: 360px; overflow-y: auto; overflow-x: hidden;
    }
    .expenses-scroll-box::-webkit-scrollbar { width: 5px; }
    .expenses-scroll-box::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    /* Net Profit Summary Banner */
    .net-profit-banner {
        border-radius: 8px; padding: 8px 14px; display: flex; min-height: 52px;
        align-items: center; justify-content: space-between; border: 1.5px solid;
    }
    .net-profit-banner.profit {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-color: #059669; color: #047857;
    }
    .net-profit-banner.loss {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border-color: #dc2626; color: #dc2626;
    }

    /* Print */
    @media print {
        .rpt-header-bar .btn, #filterCard, .no-print { display: none !important; }
        .rpt-header-bar { background: #0f1f38 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .stmt-section-header { background: #0f1f38 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .expenses-scroll-box { max-height: none !important; overflow: visible !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">

            {{-- ── 1. Header Bar ──────────────────────────────── --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div>
                        <h4 class="rpt-header-title">Income Statement (Profit &amp; Loss)</h4>
                        <div class="rpt-header-sub">
                            <i class="fas fa-chart-line mr-1" style="color:var(--is-gold);"></i>
                            Financial Performance &amp; Net Profitability &mdash; ERP System
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
            <div class="card shadow-sm mb-2 border-0 no-print" id="filterCard" style="border-radius:8px; border:1px solid var(--is-border) !important;">
                <div class="card-body p-2 px-3">
                    <div class="row g-2 align-items-end">

                        @if($isSuper)
                        <div class="col-md-3">
                            <label class="f-label">Branch</label>
                            <select id="branch_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Branches --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-{{ $isSuper ? '2' : '3' }}">
                            <label class="f-label">Date From</label>
                            <input type="date" id="date_from" class="form-control form-control-sm"
                                   style="height:36px; border-radius:6px; border:1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-{{ $isSuper ? '2' : '3' }}">
                            <label class="f-label">Date To</label>
                            <input type="date" id="date_to" class="form-control form-control-sm"
                                   style="height:36px; border-radius:6px; border:1.5px solid #cbd5e1;"
                                   value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-{{ $isSuper ? '3' : '4' }}">
                            <label class="f-label">Cost of Sales (COGS) Basis</label>
                            <select id="cogs_method" class="form-control form-control-sm" style="height:36px;">
                                <option value="item_cost">Product Cost (Qty &times; Cost Price)</option>
                                <option value="purchases">Periodic Net Purchases</option>
                            </select>
                        </div>

                        <div class="col-md-{{ $isSuper ? '2' : '2' }}">
                            <button type="button" id="btnSearch" onclick="loadReport()" class="btn btn-sm btn-primary w-100 font-weight-bold"
                                    style="height:36px; border-radius:6px; background:var(--is-navy); border-color:var(--is-navy);">
                                <i class="fas fa-search mr-1"></i> Generate
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 3. KPI Summary Cards ─────────────────────────── --}}
            <div id="summaryCards" class="is-kpi-grid" style="display:none;">
                <div class="is-kpi-card">
                    <div>
                        <div class="is-kpi-label">Net Revenue</div>
                        <div id="kpiNetSales" class="is-kpi-val emerald">Rs. 0.00</div>
                    </div>
                    <div class="is-kpi-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-cash-register"></i></div>
                </div>

                <div class="is-kpi-card">
                    <div>
                        <div class="is-kpi-label">Cost of Sales (COGS)</div>
                        <div id="kpiCogs" class="is-kpi-val crimson">Rs. 0.00</div>
                    </div>
                    <div class="is-kpi-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-boxes"></i></div>
                </div>

                <div class="is-kpi-card">
                    <div>
                        <div class="is-kpi-label">Gross Profit</div>
                        <div id="kpiGrossProfit" class="is-kpi-val">Rs. 0.00</div>
                    </div>
                    <div class="is-kpi-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-chart-pie"></i></div>
                </div>

                <div class="is-kpi-card">
                    <div>
                        <div class="is-kpi-label">Operating Expenses</div>
                        <div id="kpiExpenses" class="is-kpi-val" style="color:#d97706;">Rs. 0.00</div>
                    </div>
                    <div class="is-kpi-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-receipt"></i></div>
                </div>

                <div class="is-kpi-card highlight-profit" id="kpiNetCard">
                    <div>
                        <div class="is-kpi-label" id="kpiNetLabel">Net Profit</div>
                        <div id="kpiNetProfit" class="is-kpi-val emerald">Rs. 0.00</div>
                    </div>
                    <div class="is-kpi-icon" id="kpiNetIcon" style="background:#d1fae5;color:#059669;"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>

            {{-- ── 4. Main Statement (Compact 2-Column Layout) ──── --}}
            <div id="loader" style="display:none;text-align:center;padding:30px;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2 small font-weight-bold">Calculating income statement...</p>
            </div>

            <div id="placeholder" class="card shadow-sm border-0 text-center py-5 text-muted" style="border-radius:8px; border:1px solid var(--is-border) !important;">
                <i class="fas fa-file-invoice-dollar fa-3x mb-3" style="opacity:.3;"></i>
                <p>Filters set kar ke <strong>Generate</strong> press karein.</p>
            </div>

            {{-- Report Container (Side-by-Side 2 Columns) --}}
            <div id="reportContainer" style="display:none;">

                {{-- Metadata Bar --}}
                <div class="d-flex justify-content-between align-items-center mb-2 px-1 no-print">
                    <span class="font-weight-bold text-dark" style="font-size:12px;">
                        <i class="fas fa-building text-primary mr-1"></i> <span id="lblBranch">All Branches</span>
                    </span>
                    <span class="badge badge-light border text-muted" style="font-size:11px;" id="lblPeriod"></span>
                </div>

                <div class="row g-2 align-items-stretch">

                    {{-- ── LEFT COLUMN: Revenue + COGS + Gross Profit ── --}}
                    <div class="col-lg-6 d-flex flex-column">
                        
                        {{-- SECTION 1: REVENUE --}}
                        <div class="stmt-panel mb-2">
                            <div class="stmt-section-header">
                                <span>1. OPERATING REVENUE</span>
                                <span id="hdrNetSales">Rs. 0.00</span>
                            </div>
                            <table class="stmt-table">
                                <tbody>
                                    <tr>
                                        <td class="item-name">Gross Sales</td>
                                        <td class="amount-col" id="valGrossSales">Rs. 0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="item-sub text-danger">&minus; Trade &amp; Invoice Discounts</td>
                                        <td class="amount-col text-danger" id="valDiscounts">Rs. 0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="item-sub text-danger">&minus; Sales Returns</td>
                                        <td class="amount-col text-danger" id="valSalesReturns">Rs. 0.00</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td class="font-weight-bold">TOTAL NET REVENUE</td>
                                        <td class="amount-col text-success" id="valNetRevenue">Rs. 0.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- SECTION 2: COST OF SALES (COGS) --}}
                        <div class="stmt-panel mb-2">
                            <div class="stmt-section-header" style="background:#1e3a5f;">
                                <span>2. COST OF GOODS SOLD (COGS)</span>
                                <span id="hdrCogs">Rs. 0.00</span>
                            </div>
                            <table class="stmt-table">
                                <tbody>
                                    <tr id="rowItemCogs">
                                        <td class="item-name">Product Cost of Items Sold (Qty &times; Cost)</td>
                                        <td class="amount-col text-danger" id="valItemCogs">Rs. 0.00</td>
                                    </tr>
                                    <tr id="rowPurchases">
                                        <td class="item-name">Periodic Net Purchases</td>
                                        <td class="amount-col text-danger" id="valPurchases">Rs. 0.00</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td class="font-weight-bold">TOTAL COST OF GOODS SOLD</td>
                                        <td class="amount-col text-danger" id="valTotalCogs">Rs. 0.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- GROSS PROFIT CALLOUT (Bottom Left - matched height) --}}
                        <div class="card p-2 px-3 mb-2 border-0 shadow-sm mt-auto" style="min-height: 52px; display:flex; justify-content:center; background:linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius:8px; border-left:4px solid var(--is-navy) !important;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="font-weight-bold text-dark" style="font-size:13px;">GROSS PROFIT</span>
                                    <span class="badge badge-primary font-weight-normal ml-2" id="badgeGrossMargin">0% Margin</span>
                                </div>
                                <div class="font-weight-bold text-primary" style="font-size:16px; font-family:monospace;" id="valGrossProfit">Rs. 0.00</div>
                            </div>
                        </div>

                    </div>{{-- /col-lg-6 --}}

                    {{-- ── RIGHT COLUMN: Operating Expenses + Net Profit ── --}}
                    <div class="col-lg-6 d-flex flex-column">

                        {{-- SECTION 3: OPERATING EXPENSES (Stretches to match left column height!) --}}
                        <div class="stmt-panel-grow mb-2">
                            <div class="stmt-section-header" style="background:#2c5282;">
                                <span>3. OPERATING EXPENSES</span>
                                <span id="hdrExpenses">Rs. 0.00</span>
                            </div>
                            
                            {{-- Scrollable internal table for expenses --}}
                            <div class="expenses-scroll-box">
                                <table class="stmt-table">
                                    <tbody id="expenseRows">
                                        <tr><td colspan="2" class="text-muted text-center py-2">Koi expense record nahi mila.</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <table class="stmt-table">
                                <tfoot>
                                    <tr class="total-row">
                                        <td class="font-weight-bold">TOTAL OPERATING EXPENSES</td>
                                        <td class="amount-col text-danger" id="valTotalExpenses">Rs. 0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- SECTION 4: NET PROFIT / LOSS BANNER (Bottom Right - perfectly level with Gross Profit) --}}
                        <div class="net-profit-banner profit mb-2 mt-auto" id="bannerNet">
                            <div>
                                <div class="font-weight-bold" style="font-size:13px;">
                                    <i class="fas fa-award mr-1" id="iconNetBanner"></i>
                                    <span id="lblNetResult">NET PROFIT FOR THE PERIOD</span>
                                </div>
                                <div class="small" style="opacity:.85; font-size:11px;">
                                    Net Margin: <strong id="badgeNetMargin">0%</strong> &nbsp;|&nbsp; Gross Profit &minus; Operating Expenses
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="font-weight-bold" style="font-size:18px; font-family:monospace;" id="valNetProfit">Rs. 0.00</div>
                            </div>
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

    // Default Date From to 1st of current year/month
    const now = new Date();
    document.getElementById('date_from').value = now.getFullYear() + '-01-01';

    document.getElementById('btnSearch').addEventListener('click', loadReport);
    document.getElementById('cogs_method').addEventListener('change', loadReport);

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
    params.append('date_from',   document.getElementById('date_from').value);
    params.append('date_to',     document.getElementById('date_to').value);
    params.append('cogs_method', document.getElementById('cogs_method').value);

    document.getElementById('loader').style.display          = 'block';
    document.getElementById('placeholder').style.display     = 'none';
    document.getElementById('reportContainer').style.display = 'none';
    document.getElementById('summaryCards').style.display    = 'none';

    fetch('{{ route("report.income_statement.fetch") }}?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loader').style.display = 'none';
        renderStatement(data);
    })
    .catch(err => {
        document.getElementById('loader').style.display = 'none';
        alert('Error: ' + err.message);
    });
}

function renderStatement(d) {
    // ── Metadata ───────────────────────────────────────────
    document.getElementById('lblBranch').textContent = d.branch_label || 'All Branches';
    const df = d.date_from || 'Beginning';
    const dt = d.date_to || 'Today';
    document.getElementById('lblPeriod').textContent = 'Period: ' + df + '  to  ' + dt;

    // ── Revenue Section ────────────────────────────────────
    document.getElementById('valGrossSales').textContent    = 'Rs. ' + fmt(d.gross_sales);
    document.getElementById('valDiscounts').textContent     = 'Rs. ' + fmt(d.sales_discounts);
    document.getElementById('valSalesReturns').textContent  = 'Rs. ' + fmt(d.sales_returns);
    document.getElementById('valNetRevenue').textContent    = 'Rs. ' + fmt(d.net_revenue);
    document.getElementById('hdrNetSales').textContent      = 'Rs. ' + fmt(d.net_revenue);

    // ── Cost of Goods Sold ─────────────────────────────────
    document.getElementById('valItemCogs').textContent      = 'Rs. ' + fmt(d.item_cogs);
    document.getElementById('valPurchases').textContent     = 'Rs. ' + fmt(d.net_purchases);
    document.getElementById('valTotalCogs').textContent     = 'Rs. ' + fmt(d.active_cogs);
    document.getElementById('hdrCogs').textContent          = 'Rs. ' + fmt(d.active_cogs);

    // Method row visibility
    if (d.cogs_method === 'purchases') {
        document.getElementById('rowItemCogs').style.opacity = '0.4';
        document.getElementById('rowPurchases').style.opacity = '1.0';
        document.getElementById('rowPurchases').style.fontWeight = 'bold';
    } else {
        document.getElementById('rowItemCogs').style.opacity = '1.0';
        document.getElementById('rowItemCogs').style.fontWeight = 'bold';
        document.getElementById('rowPurchases').style.opacity = '0.4';
    }

    // Gross Profit
    document.getElementById('valGrossProfit').textContent    = 'Rs. ' + fmt(d.gross_profit);
    document.getElementById('badgeGrossMargin').textContent  = d.gross_margin_percent + '% Margin';

    // ── Operating Expenses ─────────────────────────────────
    const expTbody = document.getElementById('expenseRows');
    const expenses = d.expenses || [];
    if (expenses.length === 0) {
        expTbody.innerHTML = '<tr><td colspan="2" class="text-muted text-center py-2">Koi expense record nahi mila.</td></tr>';
    } else {
        let expHtml = '';
        expenses.forEach(e => {
            expHtml += `<tr>
                <td class="item-name">
                    ${e.title}
                    <span class="text-muted small ml-1">(${e.account_code || '—'})</span>
                </td>
                <td class="amount-col text-danger">Rs. ${fmt(e.amount)}</td>
            </tr>`;
        });
        expTbody.innerHTML = expHtml;
    }
    document.getElementById('valTotalExpenses').textContent = 'Rs. ' + fmt(d.total_expenses);
    document.getElementById('hdrExpenses').textContent      = 'Rs. ' + fmt(d.total_expenses);

    // ── Net Profit / Loss ──────────────────────────────────
    const isProfit  = (d.net_profit >= 0);
    const bannerNet = document.getElementById('bannerNet');
    const netCard   = document.getElementById('kpiNetCard');
    const netIcon   = document.getElementById('kpiNetIcon');
    const netLabel  = document.getElementById('kpiNetLabel');
    const netVal    = document.getElementById('valNetProfit');

    if (isProfit) {
        if (bannerNet) bannerNet.className = 'net-profit-banner profit';
        document.getElementById('lblNetResult').textContent = 'NET PROFIT FOR THE PERIOD';
        const iconBanner = document.getElementById('iconNetBanner');
        if (iconBanner) iconBanner.className = 'fas fa-award mr-1';
        netLabel.textContent = 'Net Profit';
        netCard.className = 'is-kpi-card highlight-profit';
        netIcon.style.background = '#d1fae5';
        netIcon.style.color = '#059669';
        netIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
        document.getElementById('kpiNetProfit').className = 'is-kpi-val emerald';
    } else {
        if (bannerNet) bannerNet.className = 'net-profit-banner loss';
        document.getElementById('lblNetResult').textContent = 'NET LOSS FOR THE PERIOD';
        const iconBanner = document.getElementById('iconNetBanner');
        if (iconBanner) iconBanner.className = 'fas fa-exclamation-triangle mr-1';
        netLabel.textContent = 'Net Loss';
        netCard.className = 'is-kpi-card highlight-loss';
        netIcon.style.background = '#fee2e2';
        netIcon.style.color = '#dc2626';
        netIcon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        document.getElementById('kpiNetProfit').className = 'is-kpi-val crimson';
    }

    netVal.textContent = 'Rs. ' + fmt(Math.abs(d.net_profit));
    document.getElementById('badgeNetMargin').textContent = d.net_margin_percent + '% Margin';

    // ── KPI Cards ──────────────────────────────────────────
    document.getElementById('kpiNetSales').textContent    = 'Rs. ' + fmt(d.net_revenue);
    document.getElementById('kpiCogs').textContent        = 'Rs. ' + fmt(d.active_cogs);
    document.getElementById('kpiGrossProfit').textContent = 'Rs. ' + fmt(d.gross_profit);
    document.getElementById('kpiExpenses').textContent    = 'Rs. ' + fmt(d.total_expenses);
    document.getElementById('kpiNetProfit').textContent   = (isProfit ? '' : '- ') + 'Rs. ' + fmt(Math.abs(d.net_profit));

    document.getElementById('summaryCards').style.display    = 'grid';
    document.getElementById('reportContainer').style.display = 'block';
}

function exportCSV() {
    let csv = 'INCOME STATEMENT (PROFIT & LOSS)\n';
    csv += 'Branch: ' + document.getElementById('lblBranch').textContent + '\n';
    csv += document.getElementById('lblPeriod').textContent + '\n\n';

    csv += 'Section,Description,Amount (PKR)\n';
    csv += 'Revenue,Gross Sales,' + document.getElementById('valGrossSales').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Revenue,Discounts,' + document.getElementById('valDiscounts').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Revenue,Sales Returns,' + document.getElementById('valSalesReturns').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Revenue,NET REVENUE,' + document.getElementById('valNetRevenue').textContent.replace(/[Rs.\s,]/g, '') + '\n';

    csv += 'Cost of Sales,Cost of Goods Sold,' + document.getElementById('valTotalCogs').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Gross Profit,GROSS PROFIT,' + document.getElementById('valGrossProfit').textContent.replace(/[Rs.\s,]/g, '') + '\n';

    const expRows = document.querySelectorAll('#expenseRows tr');
    expRows.forEach(tr => {
        const name = tr.querySelector('.item-name')?.innerText.trim() || '';
        const amt = tr.querySelector('.amount-col')?.innerText.replace(/[Rs.\s,]/g, '').trim() || '';
        if (name) csv += 'Operating Expense,"' + name + '",' + amt + '\n';
    });
    csv += 'Operating Expense,TOTAL EXPENSES,' + document.getElementById('valTotalExpenses').textContent.replace(/[Rs.\s,]/g, '') + '\n';
    csv += 'Final,NET PROFIT / (LOSS),' + document.getElementById('valNetProfit').textContent.replace(/[Rs.\s,]/g, '') + '\n';

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'income_statement_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection

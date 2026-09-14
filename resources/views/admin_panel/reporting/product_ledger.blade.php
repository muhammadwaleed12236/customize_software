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
        --coa-bg: #f8fafc;
    }

    .rpt-wrapper {
        padding: 12px 0 35px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* ── Header Bar ── */
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
        font-size: 19px;
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

    /* ── Filter Card ── */
    .filter-card {
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        padding: 18px 20px;
        margin-bottom: 18px;
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

    .btn-quick-date {
        padding: 3px 9px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 5px;
        border: 1px solid #cbd5e1;
        background: #f1f5f9;
        color: #334155;
        transition: all 0.15s ease;
    }

    .btn-quick-date:hover, .btn-quick-date.active {
        background: var(--coa-navy);
        color: #ffffff;
        border-color: var(--coa-navy);
    }

    /* ── Product Meta Card ── */
    .product-meta-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #cbd5e1;
        border-left: 5px solid var(--coa-gold);
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        display: none;
    }

    .meta-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--coa-navy-dark);
        margin-bottom: 8px;
    }

    .meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        color: #334155;
        font-weight: 600;
    }

    .meta-pill span {
        color: #64748b;
        font-weight: 500;
    }

    /* ── KPI Summary Cards ── */
    .kpi-card {
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        padding: 16px 18px;
        position: relative;
        overflow: hidden;
        margin-bottom: 18px;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }

    .kpi-card.opening::before { background: #3b82f6; }
    .kpi-card.inward::before  { background: #10b981; }
    .kpi-card.outward::before { background: #ef4444; }
    .kpi-card.closing::before { background: var(--coa-gold); }

    .kpi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 4px;
    }

    .kpi-val {
        font-size: 21px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .kpi-sub {
        font-size: 11.5px;
        color: #64748b;
        margin-top: 4px;
        font-weight: 500;
    }

    /* ── Table Styling ── */
    .ledger-table-wrap {
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .ledger-table-head {
        background: #0f1f38;
        padding: 12px 18px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    #ledgerTable {
        margin-bottom: 0;
        font-size: 12px;
        width: 100% !important;
    }

    #ledgerTable th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 10px 8px;
        border: 1px solid #1e3a5f;
        vertical-align: middle;
        text-align: center;
    }

    #ledgerTable td {
        padding: 9px 8px;
        vertical-align: middle;
        border-color: #f1f5f9;
        font-size: 12px;
    }

    #ledgerTable tbody tr:hover {
        background-color: #f8fafc;
    }

    .row-opening {
        background: #f0f9ff !important;
        font-weight: 700;
        border-top: 2px solid #bae6fd;
        border-bottom: 2px solid #bae6fd;
    }

    .row-opening td {
        color: #0369a1 !important;
    }

    .table-footer-total {
        background: #f8fafc !important;
        font-weight: 800;
        border-top: 2px solid #0f1f38;
    }

    .table-footer-total td {
        font-size: 12.5px;
        color: #0f172a;
    }

    /* ── Badges ── */
    .l-badge {
        display: inline-block;
        padding: 3px 8px;
        font-size: 10.5px;
        font-weight: 700;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .l-badge-success   { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .l-badge-danger    { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .l-badge-primary   { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .l-badge-info      { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .l-badge-warning   { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .l-badge-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
    .l-badge-dark      { background: #e2e8f0; color: #1e293b; border: 1px solid #cbd5e1; }

    .qty-in {
        font-weight: 700;
        color: #15803d;
    }

    .qty-out {
        font-weight: 700;
        color: #b91c1c;
    }

    .qty-bal {
        font-weight: 800;
        color: #0f172a;
    }

    /* ── Print Specific Styling ── */
    @media print {
        body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 10pt;
        }
        nav.rt_nav_header,
        footer,
        .filter-card,
        .btn,
        .rpt-header-bar button,
        .no-print {
            display: none !important;
        }
        .container-scroller, .main-content, .rpt-wrapper, .container-fluid {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .rpt-header-bar {
            background: none !important;
            color: #000000 !important;
            box-shadow: none !important;
            border-bottom: 2px solid #000000;
            padding: 0 0 10px 0 !important;
            margin-bottom: 15px !important;
        }
        .rpt-header-title {
            color: #000000 !important;
            font-size: 18pt !important;
        }
        .rpt-header-sub {
            color: #555555 !important;
            font-size: 9pt !important;
        }
        .rpt-header-icon {
            display: none !important;
        }
        .product-meta-card {
            display: block !important;
            border: 1px solid #000000 !important;
            box-shadow: none !important;
            background: none !important;
        }
        .kpi-card {
            border: 1px solid #999999 !important;
            box-shadow: none !important;
            margin-bottom: 10px !important;
        }
        #ledgerTable {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        #ledgerTable th {
            background: #e2e8f0 !important;
            color: #000000 !important;
            border: 1px solid #000000 !important;
            font-size: 8pt !important;
            padding: 4px !important;
        }
        #ledgerTable td {
            border: 1px solid #cccccc !important;
            font-size: 8pt !important;
            padding: 4px !important;
            color: #000000 !important;
        }
        .print-signatures {
            display: flex !important;
            justify-content: space-between;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .print-signature-box {
            width: 22%;
            text-align: center;
            border-top: 1px solid #000000;
            padding-top: 5px;
            font-size: 9pt;
            font-weight: 600;
        }
    }

    .print-signatures {
        display: none;
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Corporate Header Bar --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Product Stock Movement Ledger (Cardex)</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-file-waveform mr-1" style="color: var(--coa-gold);"></i> Complete Stock Timeline, Inward Purchases, Outward Sales, Rates & Running Inventory &mdash; Ameen & Sons ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2" id="headerActions" style="display:none !important;">
                    <button type="button" class="btn btn-sm btn-outline-light font-weight-bold" onclick="exportToCsv()">
                        <i class="fas fa-file-excel mr-1" style="color:#22c55e;"></i> Export Excel
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light font-weight-bold" onclick="window.print()">
                        <i class="fas fa-print mr-1" style="color:#38bdf8;"></i> Print Ledger
                    </button>
                </div>
            </div>

            {{-- 2. Filter Card --}}
            <div class="filter-card">
                <form id="ledgerFilterForm" onsubmit="return false;">
                    <div class="row align-items-end">

                        {{-- Product Dropdown --}}
                        <div class="col-lg-4 col-md-6 mb-3">
                            <label class="f-label"><i class="fas fa-box mr-1 text-primary"></i> Select Product <span class="text-danger">*</span></label>
                            <select id="product_id" name="product_id" class="form-control select2" required style="width: 100%;">
                                <option value="">-- Choose Product --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" data-code="{{ $prod->item_code }}" data-cost="{{ $prod->wholesale_price }}" data-retail="{{ $prod->price }}">
                                        {{ $prod->item_code ? '[' . $prod->item_code . '] ' : '' }}{{ $prod->item_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Branch Dropdown --}}
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="f-label"><i class="fas fa-building mr-1 text-primary"></i> Branch</label>
                            <select id="branch_id" name="branch_id" class="form-control" {{ !$isSuperAdmin ? 'disabled' : '' }}>
                                @if($isSuperAdmin)
                                    <option value="all">All Branches (Consolidated)</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                    @endforeach
                                @else
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" selected>{{ $b->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        {{-- Warehouse Dropdown --}}
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="f-label"><i class="fas fa-warehouse mr-1 text-primary"></i> Warehouse</label>
                            <select id="warehouse_id" name="warehouse_id" class="form-control">
                                <option value="all">All Warehouses / Shops</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Action Button --}}
                        <div class="col-lg-2 col-md-6 mb-3">
                            <button type="button" id="btnGenerate" class="btn btn-primary btn-block font-weight-bold" style="background: var(--coa-navy); border-color: var(--coa-navy); height: 38px;">
                                <i class="fas fa-magnifying-glass mr-1"></i> View Ledger
                            </button>
                        </div>

                        {{-- Date Filters & Quick Presets --}}
                        <div class="col-lg-4 col-md-6 mb-2">
                            <label class="f-label"><i class="fas fa-calendar-alt mr-1 text-primary"></i> Start Date <span class="text-danger">*</span></label>
                            <input type="date" id="start_date" name="start_date" class="form-control form-control-sm" value="{{ date('Y-m-01') }}" required>
                        </div>

                        <div class="col-lg-4 col-md-6 mb-2">
                            <label class="f-label"><i class="fas fa-calendar-alt mr-1 text-primary"></i> End Date <span class="text-danger">*</span></label>
                            <input type="date" id="end_date" name="end_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-lg-4 col-md-12 mb-2">
                            <label class="f-label"><i class="fas fa-bolt mr-1 text-warning"></i> Quick Ranges</label>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn-quick-date" onclick="setQuickDate('today')">Today</button>
                                <button type="button" class="btn-quick-date active" onclick="setQuickDate('this_month')">This Month</button>
                                <button type="button" class="btn-quick-date" onclick="setQuickDate('last_month')">Last Month</button>
                                <button type="button" class="btn-quick-date" onclick="setQuickDate('this_year')">This Year</button>
                                <button type="button" class="btn-quick-date" onclick="setQuickDate('all_time')">All Time</button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            {{-- 3. Product Meta Info Card --}}
            <div class="product-meta-card" id="productMetaCard">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="meta-title">
                            <i class="fas fa-cube text-primary mr-1"></i>
                            <span id="meta_product_name">-</span>
                            <small class="badge badge-secondary ml-2 font-weight-bold" id="meta_product_code">-</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <div class="meta-pill"><span>Category:</span> <b id="meta_category">-</b></div>
                            <div class="meta-pill"><span>Subcategory:</span> <b id="meta_subcategory">-</b></div>
                            <div class="meta-pill"><span>Brand:</span> <b id="meta_brand">-</b></div>
                            <div class="meta-pill"><span>Unit (UOM):</span> <b id="meta_unit">-</b></div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 text-right">
                        <div class="meta-pill bg-light"><span>Wholesale / Cost:</span> <b class="text-success" id="meta_cost_price">Rs. 0</b></div>
                        <div class="meta-pill bg-light"><span>Retail Price:</span> <b class="text-primary" id="meta_retail_price">Rs. 0</b></div>
                        <div class="meta-pill bg-light"><span>Location Filter:</span> <b class="text-dark" id="meta_branch_warehouse">-</b></div>
                    </div>
                </div>
            </div>

            {{-- 4. KPI Summary Cards --}}
            <div class="row" id="kpiCardsWrap" style="display:none;">
                <div class="col-xl-3 col-sm-6">
                    <div class="kpi-card opening">
                        <div class="kpi-label"><i class="fas fa-hourglass-start mr-1 text-primary"></i> Opening Stock</div>
                        <div class="kpi-val text-primary" id="kpi_opening_qty">0</div>
                        <div class="kpi-sub">Value: <span class="font-weight-bold" id="kpi_opening_val">Rs. 0</span></div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="kpi-card inward">
                        <div class="kpi-label"><i class="fas fa-arrow-down mr-1 text-success"></i> Total Inward (Receipts)</div>
                        <div class="kpi-val text-success" id="kpi_inward_qty">0</div>
                        <div class="kpi-sub">Cost Value: <span class="font-weight-bold" id="kpi_inward_val">Rs. 0</span></div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="kpi-card outward">
                        <div class="kpi-label"><i class="fas fa-arrow-up mr-1 text-danger"></i> Total Outward (Issues)</div>
                        <div class="kpi-val text-danger" id="kpi_outward_qty">0</div>
                        <div class="kpi-sub">Sales Value: <span class="font-weight-bold" id="kpi_outward_val">Rs. 0</span></div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="kpi-card closing">
                        <div class="kpi-label"><i class="fas fa-layer-group mr-1" style="color:var(--coa-gold);"></i> Net Closing Stock</div>
                        <div class="kpi-val" style="color:var(--coa-navy-dark);" id="kpi_closing_qty">0</div>
                        <div class="kpi-sub">Valuation: <span class="font-weight-bold" id="kpi_closing_val">Rs. 0</span></div>
                    </div>
                </div>
            </div>

            {{-- 5. Ledger Data Table Card --}}
            <div class="ledger-table-wrap" id="ledgerTableWrap" style="display:none;">
                <div class="ledger-table-head">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-table-list text-warning"></i>
                        <span class="font-weight-bold" style="font-size: 13.5px;">Chronological Stock Movement Statement</span>
                    </div>
                    <div>
                        <span class="badge badge-light font-weight-bold text-dark px-2 py-1" id="ledgerDateRangeBadge">-</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center" id="ledgerTable">
                        <thead>
                            <tr>
                                <th style="width: 35px;">#</th>
                                <th style="width: 120px;">Date & Time</th>
                                <th style="width: 140px;">Type</th>
                                <th style="width: 110px;">Doc / Ref #</th>
                                <th style="width: 180px;">Party / Destination</th>
                                <th style="width: 130px;">Location</th>
                                <th style="width: 75px; background: #064e3b !important; color: #34d399 !important;">In Qty</th>
                                <th style="width: 90px; background: #064e3b !important; color: #34d399 !important;">Unit Cost</th>
                                <th style="width: 105px; background: #064e3b !important; color: #34d399 !important;">Total In (Rs.)</th>
                                <th style="width: 75px; background: #7f1d1d !important; color: #f87171 !important;">Out Qty</th>
                                <th style="width: 90px; background: #7f1d1d !important; color: #f87171 !important;">Sale Price</th>
                                <th style="width: 105px; background: #7f1d1d !important; color: #f87171 !important;">Total Out (Rs.)</th>
                                <th style="width: 85px; background: #1e3a5f !important; color: #fcd34d !important;">Balance Qty</th>
                                <th style="width: 90px; background: #1e3a5f !important; color: #fcd34d !important;">Val. Rate</th>
                                <th style="width: 110px; background: #1e3a5f !important; color: #fcd34d !important;">Stock Value</th>
                                <th style="width: 150px;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="ledgerTableBody">
                            {{-- Rows generated dynamically via JavaScript --}}
                        </tbody>
                        <tfoot id="ledgerTableFoot">
                            {{-- Totals calculated dynamically --}}
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- 6. Initial Prompt / Empty State --}}
            <div id="emptyState" class="text-center py-5" style="background:#ffffff; border-radius:10px; border:1px dashed #cbd5e1;">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <i class="fas fa-boxes-stacked fa-2x text-muted"></i>
                </div>
                <h5 class="font-weight-bold text-dark">Select a Product to Generate Stock Ledger</h5>
                <p class="text-muted font-size-13 max-w-500 mx-auto">
                    Choose an item from the searchable dropdown above, specify the branch and date range, then click <b>View Ledger</b> to inspect complete opening stock, purchases, sales, and running balance.
                </p>
            </div>

            {{-- 7. Loading Spinner --}}
            <div id="loadingState" class="text-center py-5" style="display:none; background:#ffffff; border-radius:10px; border:1px solid #e2e8f0;">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <h5 class="font-weight-bold text-dark">Calculating Product Movement Ledger...</h5>
                <p class="text-muted font-size-13">Aggregating initial stocks, purchases, deliveries, invoices, returns, and inventory valuations.</p>
            </div>

            {{-- Print Signatures Footer (Visible only when printed) --}}
            <div class="print-signatures">
                <div class="print-signature-box">Prepared By</div>
                <div class="print-signature-box">Store In-Charge</div>
                <div class="print-signature-box">Inventory Auditor</div>
                <div class="print-signature-box">Authorized Signatory</div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    var currentLedgerData = null;

    $(document).ready(function() {
        // Initialize Select2 for product search
        if ($.fn.select2) {
            $('#product_id').select2({
                placeholder: "-- Search by Product Code or Item Name --",
                allowClear: true,
                width: '100%'
            });
        }

        // Branch change handler to update warehouses
        $('#branch_id').on('change', function() {
            var branchId = $(this).val();
            var $whSelect = $('#warehouse_id');
            $whSelect.html('<option value="all">All Warehouses / Shops</option>');

            if (branchId !== 'all') {
                $.ajax({
                    url: '{{ route("warehouses-by-branch") }}',
                    type: 'GET',
                    data: { branch_id: branchId },
                    success: function(warehouses) {
                        if (Array.isArray(warehouses)) {
                            warehouses.forEach(function(wh) {
                                $whSelect.append('<option value="' + wh.id + '">' + wh.warehouse_name + '</option>');
                            });
                        }
                    }
                });
            }
        });

        // Trigger on "View Ledger" button click
        $('#btnGenerate').on('click', function() {
            generateLedger();
        });
    });

    // Quick Date Preset Handler
    function setQuickDate(preset) {
        $('.btn-quick-date').removeClass('active');
        $(event.target).addClass('active');

        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var dd = String(today.getDate()).padStart(2, '0');
        var todayStr = yyyy + '-' + mm + '-' + dd;

        if (preset === 'today') {
            $('#start_date').val(todayStr);
            $('#end_date').val(todayStr);
        } else if (preset === 'this_month') {
            $('#start_date').val(yyyy + '-' + mm + '-01');
            $('#end_date').val(todayStr);
        } else if (preset === 'last_month') {
            var lastMonthDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            var lYear = lastMonthDate.getFullYear();
            var lMonth = String(lastMonthDate.getMonth() + 1).padStart(2, '0');
            var lastDayOfLastMonth = new Date(today.getFullYear(), today.getMonth(), 0).getDate();
            $('#start_date').val(lYear + '-' + lMonth + '-01');
            $('#end_date').val(lYear + '-' + lMonth + '-' + String(lastDayOfLastMonth).padStart(2, '0'));
        } else if (preset === 'this_year') {
            $('#start_date').val(yyyy + '-01-01');
            $('#end_date').val(todayStr);
        } else if (preset === 'all_time') {
            $('#start_date').val('2024-01-01');
            $('#end_date').val(todayStr);
        }
    }

    // Number formatting helper
    function numFmt(val, decimals) {
        if (decimals === undefined) decimals = 2;
        var num = parseFloat(val);
        if (isNaN(num)) num = 0;
        return num.toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    // Currency formatting helper
    function currFmt(val) {
        var num = parseFloat(val);
        if (isNaN(num)) num = 0;
        return 'Rs. ' + num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Main Ajax Fetcher
    function generateLedger() {
        var productId = $('#product_id').val();
        var branchId = $('#branch_id').val();
        var warehouseId = $('#warehouse_id').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        if (!productId) {
            Swal.fire({
                icon: 'warning',
                title: 'Select Product',
                text: 'Please select a product from the list to view its ledger.',
                confirmButtonColor: '#1e3a5f'
            });
            return;
        }

        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Date Range Required',
                text: 'Please select both start date and end date.',
                confirmButtonColor: '#1e3a5f'
            });
            return;
        }

        // UI states
        $('#emptyState').hide();
        $('#productMetaCard').hide();
        $('#kpiCardsWrap').hide();
        $('#ledgerTableWrap').hide();
        $('#headerActions').attr('style', 'display:none !important;');
        $('#loadingState').show();

        $.ajax({
            url: '{{ route("report.product_ledger.fetch") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_id: productId,
                branch_id: branchId,
                warehouse_id: warehouseId,
                start_date: startDate,
                end_date: endDate
            },
            success: function(res) {
                $('#loadingState').hide();

                if (!res.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.error || 'Failed to fetch ledger.',
                    });
                    $('#emptyState').show();
                    return;
                }

                currentLedgerData = res;
                renderProductLedger(res);
            },
            error: function(xhr) {
                $('#loadingState').hide();
                $('#emptyState').show();
                var msg = 'An unexpected server error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Fetch Error',
                    text: msg,
                });
            }
        });
    }

    // Render Data to Table and Cards
    function renderProductLedger(data) {
        var p = data.product;
        var s = data.summary;
        var f = data.filters;
        var events = data.events;

        // 1. Populate Product Meta Banner
        $('#meta_product_name').text(p.item_name);
        $('#meta_product_code').text(p.item_code || 'NO-CODE');
        $('#meta_category').text(p.category || '-');
        $('#meta_subcategory').text(p.subcategory || '-');
        $('#meta_brand').text(p.brand || '-');
        $('#meta_unit').text(p.unit || 'Pcs');
        $('#meta_cost_price').text(currFmt(p.wholesale_price));
        $('#meta_retail_price').text(currFmt(p.retail_price));
        $('#meta_branch_warehouse').text(f.branch_name + ' | ' + f.warehouse_name);
        $('#productMetaCard').fadeIn(200);

        // 2. Populate KPI Cards
        $('#kpi_opening_qty').text(numFmt(s.opening_qty, 2) + ' ' + p.unit);
        $('#kpi_opening_val').text(currFmt(s.opening_value));

        $('#kpi_inward_qty').text(numFmt(s.total_in_qty, 2) + ' ' + p.unit);
        $('#kpi_inward_val').text(currFmt(s.total_in_value));

        $('#kpi_outward_qty').text(numFmt(s.total_out_qty, 2) + ' ' + p.unit);
        $('#kpi_outward_val').text(currFmt(s.total_out_value));

        $('#kpi_closing_qty').text(numFmt(s.closing_qty, 2) + ' ' + p.unit);
        $('#kpi_closing_val').text(currFmt(s.closing_value));
        $('#kpiCardsWrap').fadeIn(200);

        // 3. Populate Header Date Badge & Actions
        $('#ledgerDateRangeBadge').html('<i class="fas fa-clock mr-1"></i> ' + f.start_date + ' &nbsp;&rarr;&nbsp; ' + f.end_date);
        $('#headerActions').attr('style', 'display:flex !important;');

        // 4. Build Table Rows
        var tbodyHtml = '';

        // Initial Opening Balance Row
        tbodyHtml += `
            <tr class="row-opening">
                <td>0</td>
                <td><b>${f.start_date}</b></td>
                <td><span class="l-badge l-badge-primary"><i class="fas fa-hourglass-start mr-1"></i> Opening Balance</span></td>
                <td><span class="text-muted">INITIAL</span></td>
                <td><b>Stock Brought Forward</b></td>
                <td>${f.warehouse_name}</td>
                <td class="text-muted">-</td>
                <td class="text-muted">-</td>
                <td class="text-muted">-</td>
                <td class="text-muted">-</td>
                <td class="text-muted">-</td>
                <td class="text-muted">-</td>
                <td class="qty-bal" style="font-size:13px; color:#0369a1;">${numFmt(s.opening_qty, 2)}</td>
                <td>${numFmt(p.wholesale_price, 2)}</td>
                <td style="font-weight:700;">${numFmt(s.opening_value, 2)}</td>
                <td><small class="text-muted">Balance prior to ${f.start_date}</small></td>
            </tr>
        `;

        if (events.length === 0) {
            tbodyHtml += `
                <tr>
                    <td colspan="16" class="text-center py-4 text-muted">
                        <i class="fas fa-info-circle mr-1 text-info"></i> No transactions (purchases, sales, or transfers) recorded for this product during the selected period.
                    </td>
                </tr>
            `;
        } else {
            events.forEach(function(ev, idx) {
                var badgeClass = 'l-badge-secondary';
                if (ev.type.indexOf('Purchase') !== -1) badgeClass = 'l-badge-success';
                else if (ev.type.indexOf('Sale') !== -1) badgeClass = 'l-badge-danger';
                else if (ev.type.indexOf('Return') !== -1) badgeClass = 'l-badge-info';
                else if (ev.type.indexOf('Opening') !== -1) badgeClass = 'l-badge-primary';
                else if (ev.type.indexOf('Damaged') !== -1) badgeClass = 'l-badge-dark';

                var inQtyHtml = ev.in_qty > 0 ? `<span class="qty-in">+${numFmt(ev.in_qty, 2)}</span>` : '<span class="text-muted">-</span>';
                var inPriceHtml = ev.in_qty > 0 ? numFmt(ev.in_price, 2) : '<span class="text-muted">-</span>';
                var inTotalHtml = ev.in_qty > 0 ? numFmt(ev.in_total, 2) : '<span class="text-muted">-</span>';

                var outQtyHtml = ev.out_qty > 0 ? `<span class="qty-out">-${numFmt(ev.out_qty, 2)}</span>` : '<span class="text-muted">-</span>';
                var outPriceHtml = ev.out_qty > 0 ? numFmt(ev.out_price, 2) : '<span class="text-muted">-</span>';
                var outTotalHtml = ev.out_qty > 0 ? numFmt(ev.out_total, 2) : '<span class="text-muted">-</span>';

                var balColor = ev.balance_qty < 0 ? 'color:#dc2626;' : 'color:#0f172a;';

                tbodyHtml += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td style="white-space:nowrap; font-size:11.5px;">${ev.date}</td>
                        <td><span class="l-badge ${badgeClass}"><i class="fas ${ev.icon || 'fa-tag'} mr-1"></i> ${ev.type}</span></td>
                        <td><span class="font-weight-bold" style="color:var(--coa-navy-light);">${ev.ref_no || '-'}</span></td>
                        <td style="text-align:left; font-weight:600;">${ev.party || '-'}</td>
                        <td style="font-size:11.5px;">${ev.warehouse || '-'}</td>
                        <td>${inQtyHtml}</td>
                        <td>${inPriceHtml}</td>
                        <td style="font-weight:600;">${inTotalHtml}</td>
                        <td>${outQtyHtml}</td>
                        <td>${outPriceHtml}</td>
                        <td style="font-weight:600;">${outTotalHtml}</td>
                        <td class="qty-bal" style="${balColor} font-size:12.5px;">${numFmt(ev.balance_qty, 2)}</td>
                        <td style="font-size:11.5px;">${numFmt(ev.valuation_rate, 2)}</td>
                        <td style="font-weight:700;">${numFmt(ev.balance_value, 2)}</td>
                        <td style="text-align:left; font-size:11px;" class="text-muted">${ev.remarks || ''}</td>
                    </tr>
                `;
            });
        }

        $('#ledgerTableBody').html(tbodyHtml);

        // 5. Build Footer Totals Row
        var tfootHtml = `
            <tr class="table-footer-total">
                <td colspan="6" class="text-right pr-3 font-weight-bold">TOTAL PERIOD MOVEMENTS & CLOSING BALANCE:</td>
                <td class="qty-in">+${numFmt(s.total_in_qty, 2)}</td>
                <td class="text-muted">-</td>
                <td class="font-weight-bold" style="color:#15803d;">${numFmt(s.total_in_value, 2)}</td>
                <td class="qty-out">-${numFmt(s.total_out_qty, 2)}</td>
                <td class="text-muted">-</td>
                <td class="font-weight-bold" style="color:#b91c1c;">${numFmt(s.total_out_value, 2)}</td>
                <td class="qty-bal" style="font-size:13.5px; background:#fef3c7; color:#92400e;">${numFmt(s.closing_qty, 2)}</td>
                <td class="text-muted">-</td>
                <td class="font-weight-bold" style="font-size:13.5px; background:#fef3c7; color:#92400e;">${numFmt(s.closing_value, 2)}</td>
                <td></td>
            </tr>
        `;
        $('#ledgerTableFoot').html(tfootHtml);

        $('#ledgerTableWrap').fadeIn(200);

        // Smooth scroll down to results
        $('html, body').animate({
            scrollTop: $('#productMetaCard').offset().top - 20
        }, 300);
    }

    // Export to CSV Function
    function exportToCsv() {
        if (!currentLedgerData || !currentLedgerData.product) {
            Swal.fire({
                icon: 'info',
                title: 'No Data',
                text: 'Please generate a ledger before exporting.',
            });
            return;
        }

        var p = currentLedgerData.product;
        var s = currentLedgerData.summary;
        var f = currentLedgerData.filters;
        var events = currentLedgerData.events;

        var csv = [];
        csv.push(['"AMEEN & SONS - PRODUCT STOCK MOVEMENT LEDGER"']);
        csv.push(['"Product Code:"', '"' + (p.item_code || '') + '"', '"Product Name:"', '"' + p.item_name + '"']);
        csv.push(['"Category:"', '"' + p.category + '"', '"Brand:"', '"' + p.brand + '"', '"Unit:"', '"' + p.unit + '"']);
        csv.push(['"Branch:"', '"' + f.branch_name + '"', '"Warehouse:"', '"' + f.warehouse_name + '"']);
        csv.push(['"Period:"', '"' + f.start_date + ' to ' + f.end_date + '"']);
        csv.push(['"Cost Price:"', '"' + p.wholesale_price + '"', '"Retail Price:"', '"' + p.retail_price + '"']);
        csv.push([]);

        // Table Header
        csv.push([
            '"#"',
            '"Date & Time"',
            '"Transaction Type"',
            '"Ref / Doc No"',
            '"Party / Location"',
            '"Warehouse"',
            '"In Qty"',
            '"Unit Cost"',
            '"Total In (Rs.)"',
            '"Out Qty"',
            '"Sale Price"',
            '"Total Out (Rs.)"',
            '"Balance Qty"',
            '"Valuation Rate"',
            '"Stock Value (Rs.)"',
            '"Remarks"'
        ]);

        // Opening Row
        csv.push([
            '0',
            '"' + f.start_date + '"',
            '"Opening Balance"',
            '"INITIAL"',
            '"Stock Brought Forward"',
            '"' + f.warehouse_name + '"',
            '0',
            '0',
            '0',
            '0',
            '0',
            '0',
            s.opening_qty,
            p.wholesale_price,
            s.opening_value,
            '"Opening balance prior to ' + f.start_date + '"'
        ]);

        // Transactions
        events.forEach(function(e, i) {
            csv.push([
                i + 1,
                '"' + e.date + '"',
                '"' + e.type + '"',
                '"' + (e.ref_no || '') + '"',
                '"' + (e.party || '').replace(/"/g, '""') + '"',
                '"' + (e.warehouse || '').replace(/"/g, '""') + '"',
                e.in_qty,
                e.in_price,
                e.in_total,
                e.out_qty,
                e.out_price,
                e.out_total,
                e.balance_qty,
                e.valuation_rate,
                e.balance_value,
                '"' + (e.remarks || '').replace(/"/g, '""') + '"'
            ]);
        });

        // Totals Row
        csv.push([
            '"TOTAL"',
            '""',
            '""',
            '""',
            '""',
            '""',
            s.total_in_qty,
            '""',
            s.total_in_value,
            s.total_out_qty,
            '""',
            s.total_out_value,
            s.closing_qty,
            '""',
            s.closing_value,
            '""'
        ]);

        var csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + csv.map(e => e.join(',')).join('\n');
        var encodedUri = encodeURI(csvContent);
        var link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        var fileName = 'Product_Ledger_' + (p.item_code || 'ITEM') + '_' + f.start_date + '_to_' + f.end_date + '.csv';
        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endsection

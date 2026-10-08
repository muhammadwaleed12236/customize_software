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

    #stockTable th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 8px;
        border: 1px solid #1e3a5f;
    }

    /* Select2 Responsive Fix */
    .select2-container {
        width: 100% !important;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
    }

    /* Swipe hint bar for mobile */
    .swipe-hint-bar {
        display: none;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 11.5px;
        color: #475569;
        margin-bottom: 10px;
    }

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 768px) {
        .rpt-wrapper {
            padding: 6px 0 15px 0;
        }

        .rpt-header-bar {
            flex-direction: column;
            align-items: stretch;
            padding: 12px 14px;
            gap: 10px;
        }

        .rpt-header-title {
            font-size: 16px;
        }

        .rpt-header-sub {
            font-size: 11px;
        }

        .rpt-header-bar .d-flex.gap-2 {
            justify-content: stretch;
            width: 100%;
        }

        .rpt-header-bar .d-flex.gap-2 button {
            flex: 1;
            padding: 8px 10px;
            font-size: 12px;
            justify-content: center;
        }

        /* KPI Cards on Mobile: 2 Columns */
        #kpiSummaryCards {
            margin-bottom: 10px !important;
        }

        .kpi-col {
            padding-left: 4px !important;
            padding-right: 4px !important;
            margin-bottom: 8px !important;
        }

        .kpi-card-box {
            padding: 10px 12px !important;
        }

        .kpi-card-box h4 {
            font-size: 14.5px !important;
        }

        .kpi-card-box .text-muted {
            font-size: 9.5px !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .kpi-icon-box {
            width: 32px !important;
            height: 32px !important;
            font-size: 14px !important;
        }

        /* Swipe hint visible on mobile */
        .swipe-hint-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* DataTables Mobile Layout */
        .dataTables_wrapper .dataTables_filter {
            float: none !important;
            text-align: left !important;
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            margin-left: 0 !important;
            height: 36px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }

        .dataTables_wrapper .dataTables_length {
            float: none !important;
            text-align: left !important;
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_paginate {
            float: none !important;
            text-align: center !important;
            margin-top: 10px;
        }

        /* Table Touch Scroll Optimization */
        .table-responsive {
            border: 1px solid var(--coa-border);
            border-radius: 6px;
            -webkit-overflow-scrolling: touch;
        }

        #stockTable {
            font-size: 11px !important;
            white-space: nowrap;
        }

        #stockTable th {
            padding: 8px 6px !important;
            font-size: 10.5px !important;
        }

        #stockTable td {
            padding: 8px 6px !important;
            vertical-align: middle;
        }

        /* Sticky Item Code Column for Mobile Horizontal Scroll */
        #stockTable th:nth-child(2),
        #stockTable td:nth-child(2) {
            position: sticky;
            left: 0;
            z-index: 2;
            box-shadow: 2px 0 5px rgba(0,0,0,0.06);
        }

        #stockTable td:nth-child(2) {
            background-color: #f8fafc !important;
        }

        #stockTable th:nth-child(2) {
            background-color: #0f1f38 !important;
            z-index: 3;
        }

        .warehouse-btn {
            padding: 2px 6px !important;
            font-size: 10px !important;
        }
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Corporate Header Bar --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Item Stock Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-warehouse mr-1" style="color: var(--coa-gold);"></i> Track opening, purchased, sold, reserved & balance per product &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="waShareBtn" onclick="shareWhatsApp()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(37, 211, 102, 0.2); border-color: #25D366; color: #25D366;">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button type="button" onclick="showExportOptions()" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-download mr-1 text-primary"></i> Export
                    </button>
                </div>
            </div>

            {{-- 2. Filter Card --}}
            <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form id="stockFilterForm" class="row g-2 align-items-end mb-0">
                        {{-- Branch Selector (Super Admin Only) --}}
                        @if($isSuperAdmin)
                        <div class="col-md-3">
                            <label class="f-label">Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                <option value="all">-- All Branches --</option>
                                @foreach($userBranches as $branch)
                                    <option value="{{ $branch->id }}" @selected($branch->id == $selectedBranchId)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="f-label">Product</label>
                            <select name="product_id" id="product_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Products --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->item_code }} - {{ $prod->item_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Search Stock
                            </button>
                        </div>
                        @else
                        {{-- Non-Admin User: Branch Display Only --}}
                        <div class="col-md-3">
                            <label class="f-label">Branch</label>
                            <div class="form-control form-control-sm bg-light font-weight-bold" style="height: 38px; display: flex; align-items: center; border: 1.5px solid #cbd5e1;">
                                {{ $userBranches[0]?->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-5">
                            <label class="f-label">Product</label>
                            <select name="product_id" id="product_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Products --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->item_code }} - {{ $prod->item_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Search Stock
                            </button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- 2.5 Summary KPI Cards --}}
            <div class="row g-2 mb-3" id="kpiSummaryCards" style="display: none;">
                <div class="col-6 col-md-3 kpi-col">
                    <div class="card border-0 shadow-sm p-3 kpi-card-box" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #1e3a5f !important; border: 1px solid var(--coa-border);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.05em;">Total Balance Stock</div>
                                <h4 class="font-weight-bold mb-0 text-dark mt-1" id="kpiTotalBalance">0.00</h4>
                            </div>
                            <div class="rounded-circle p-2 kpi-icon-box" style="background: rgba(30, 58, 95, 0.1); color: #1e3a5f; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-boxes fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 kpi-col">
                    <div class="card border-0 shadow-sm p-3 kpi-card-box" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #0d9f6e !important; border: 1px solid var(--coa-border);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.05em;">Delivered Qty (دیے)</div>
                                <h4 class="font-weight-bold mb-0 text-success mt-1" id="kpiTotalDelivered">0.00</h4>
                            </div>
                            <div class="rounded-circle p-2 kpi-icon-box" style="background: rgba(13, 159, 110, 0.1); color: #0d9f6e; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-truck-loading fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 kpi-col">
                    <div class="card border-0 shadow-sm p-3 kpi-card-box" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #d97706 !important; border: 1px solid var(--coa-border);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.05em;">Reserved Qty (ریزرورڈ)</div>
                                <h4 class="font-weight-bold mb-0 mt-1" style="color: #d97706 !important;" id="kpiTotalReserved">0.00</h4>
                            </div>
                            <div class="rounded-circle p-2 kpi-icon-box" style="background: rgba(217, 119, 6, 0.1); color: #d97706; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-clock fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 kpi-col">
                    <div class="card border-0 shadow-sm p-3 kpi-card-box" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #c8973a !important; border: 1px solid var(--coa-border);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.05em;">Total Stock Value</div>
                                <h4 class="font-weight-bold mb-0 mt-1" style="color: #b45309;" id="kpiTotalStockValue">Rs. 0.00</h4>
                            </div>
                            <div class="rounded-circle p-2 kpi-icon-box" style="background: rgba(200, 151, 58, 0.15); color: #c8973a; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-coins fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Report Table Card --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="reportContent">
                <div class="card-body p-3">
                    <div id="loader" style="display:none;text-align:center;padding:30px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Fetching stock metrics...</p>
                    </div>

                    {{-- Swipe hint indicator for mobile touch users --}}
                    <div class="swipe-hint-bar">
                        <span><i class="fas fa-arrows-alt-h text-primary me-1"></i> <strong>Swipe left/right</strong> to view full 13 columns</span>
                        <span class="badge bg-secondary text-white">پوری تفصیل</span>
                    </div>

                    <div class="table-responsive">
                        <table id="stockTable" class="table table-bordered mb-0" style="font-size: 12.5px; width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 3%;">#</th>
                                    <th style="width: 8%;">Item Code</th>
                                    <th>Item Name</th>
                                    <th class="text-end" style="width: 6%;">Opening</th>
                                    <th class="text-end" style="width: 6%;">Purchased</th>
                                    <th class="text-end" style="width: 8%;">Purch. Value</th>
                                    <th class="text-end" style="width: 8%; background: #064e3b !important;" title="Delivered / Dispatched Quantity (دیے کتنے)">Delivered (دیے)</th>
                                    <th class="text-end" style="width: 8%;">Delivered Value</th>
                                    <th class="text-end" style="width: 8%; background: #78350f !important;" title="Reserved Quantity Pending Delivery (ریزرورڈ)">Reserved (ریزرورڈ)</th>
                                    <th class="text-end" style="width: 7%;">Balance</th>
                                    <th class="text-end" style="width: 7%; background: #1e3a5f !important;" title="Unit Price / Wholesale Rate (قیمت / ریٹ)">Price (قیمت)</th>
                                    <th class="text-end" style="width: 9%;">Stock Value</th>
                                    <th class="text-center" style="width: 7%;">Warehouses</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <!-- Filled by AJAX -->
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 12.5px;">
                                    <th colspan="3" class="text-end font-weight-bold" style="font-family: sans-serif;">Total Stock Summary:</th>
                                    <th class="text-end" id="footOpening">0.00</th>
                                    <th class="text-end" id="footPurchased">0.00</th>
                                    <th class="text-end" id="footPurchAmount">Rs. 0.00</th>
                                    <th class="text-end text-success" id="footDelivered">0.00</th>
                                    <th class="text-end" id="footDeliveredAmount">Rs. 0.00</th>
                                    <th class="text-end text-warning" id="footReserved">0.00</th>
                                    <th class="text-end" id="footBalance">0.00</th>
                                    <th class="text-end">--</th>
                                    <th class="text-end text-success font-weight-bold" id="grandStockValue">0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>

            <!-- Warehouse Breakdown Modal -->
            <div class="modal fade" id="warehouseModal" tabindex="-1" role="dialog" aria-labelledby="warehouseModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow" style="border-radius: 10px; overflow: hidden;">
                        <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 100%);">
                            <h6 class="modal-title font-weight-bold mb-0" id="warehouseModalLabel">
                                <i class="fas fa-warehouse mr-1" style="color: var(--coa-gold);"></i> Warehouse Stock Breakdown
                            </h6>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <h6 id="modalProductName" class="mb-3"></h6>
                            <table class="table table-bordered table-hover" id="warehouseTable">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>Warehouse Name</th>
                                        <th>Location</th>
                                        <th class="text-end">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody id="warehouseTableBody">
                                    <!-- Filled dynamically -->
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('js')

<script>
$(document).ready(function() {
    var warehouseDataStore = {}; // ✅ Global storage for warehouse data
    
    var stockTable = $('#stockTable').DataTable({
        paging: true,
        searching: true,
        info: true,
        ordering: true,
        columnDefs: [
            { orderable: false, targets: -1 } // Disable sorting on action column
        ]
    });

    function renderRows(rows, grandTotal) {
        // Destroy and recreate DataTable for clean rendering
        if ($.fn.DataTable.isDataTable('#stockTable')) {
            stockTable.destroy();
        }
        
        // Clear the table body
        $('#reportBody').html('');
        warehouseDataStore = {}; // Reset warehouse data

        if (!rows || rows.length === 0) {
            $('#reportBody').html('<tr><td colspan="13" class="text-center py-4 text-muted font-weight-bold">No stock data found for selected criteria in this branch.</td></tr>');
            stockTable = $('#stockTable').DataTable({
                paging: true,
                searching: true,
                info: true,
                ordering: true,
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });
            $('#grandStockValue').text('0.00').removeClass('text-danger');
            return;
        }

        let hasNegativeStock = false;
        let sumOpening = 0, sumPurchased = 0, sumPurchAmount = 0;
        let sumDelivered = 0, sumDeliveredAmount = 0, sumReserved = 0;
        let sumBalance = 0;

        rows.forEach(function(r, idx) {
            // ============= STORE WAREHOUSE DATA =============
            let dataKey = 'product_' + r.id;
            warehouseDataStore[dataKey] = {
                itemCode: r.item_code,
                itemName: r.item_name,
                warehouses: r.warehouse_breakdown || []
            };

            // ============= ACCUMULATE TOTALS =============
            let opening     = parseFloat(r.initial_stock || 0);
            let purchased   = parseFloat(r.purchased || 0);
            let purchAmt    = parseFloat(r.purchase_amount || 0);
            let sold        = parseFloat(r.sold || 0);
            let saleAmt     = parseFloat(r.sale_amount || 0);
            let reserved    = parseFloat(r.reserved_qty || 0);
            let balance     = parseFloat(r.balance || 0);
            let price       = parseFloat(r.price || 0);
            let stockValue  = parseFloat(r.stock_value || 0);

            sumOpening += opening;
            sumPurchased += purchased;
            sumPurchAmount += purchAmt;
            sumDelivered += sold;
            sumDeliveredAmount += saleAmt;
            sumReserved += reserved;
            sumBalance += balance;

            // ============= BUILD WAREHOUSE BREAKDOWN HTML =============
            let warehouseHtml = '';
            if (r.warehouse_breakdown && r.warehouse_breakdown.length > 0) {
                warehouseHtml = `<button type="button" class="btn btn-sm btn-info warehouse-btn" data-product-id="${r.id}">
                    View <span class="badge badge-light">${r.warehouse_breakdown.length}</span>
                </button>`;
            } else {
                warehouseHtml = '<span class="text-muted">No breakdown</span>';
            }

            // ============= NEGATIVE STOCK VISUAL LOGIC =============
            let isNegative  = balance < 0;
            if (isNegative) hasNegativeStock = true;

            let balanceHtml = isNegative
                ? `<strong class="text-danger">${balance.toFixed(2)} <span class="badge badge-danger" title="Negative Stock — sold more than available">⚠ Negative</span></strong>`
                : `<strong>${balance.toFixed(2)}</strong>`;

            let stockValueHtml = isNegative
                ? `<strong class="text-danger">Rs. ${stockValue.toFixed(2)}</strong>`
                : `<strong>Rs. ${stockValue.toFixed(2)}</strong>`;

            let rowStyle = isNegative ? 'background-color: #fff5f5;' : '';

            // ============= BUILD TABLE ROW =============
            let row = `
                <tr style="${rowStyle}">
                    <td>${idx + 1}${isNegative ? ' <span class="text-danger" title="Negative Stock">⚠</span>' : ''}</td>
                    <td><strong>${r.item_code}</strong></td>
                    <td>${r.item_name}</td>
                    <td class="text-end">${opening.toFixed(2)}</td>
                    <td class="text-end">${purchased.toFixed(2)}</td>
                    <td class="text-end">Rs. ${purchAmt.toFixed(2)}</td>
                    <td class="text-end fw-bold text-success" title="Physically Delivered Qty (دیے گئے)">${sold.toFixed(2)}</td>
                    <td class="text-end">Rs. ${saleAmt.toFixed(2)}</td>
                    <td class="text-end" title="Reserved Stock Pending Delivery (ریزرورڈ)"><span class="badge bg-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px;">${reserved.toFixed(2)} pending</span></td>
                    <td class="text-end">${balanceHtml}</td>
                    <td class="text-end fw-bold" style="color: #1e3a5f !important;" title="Wholesale / Unit Price">Rs. ${price.toFixed(2)}</td>
                    <td class="text-end">${stockValueHtml}</td>
                    <td class="text-center">${warehouseHtml}</td>
                </tr>
            `;
            
            $('#reportBody').append(row);
        });

        // Negative stock summary row
        if (hasNegativeStock) {
            $('#reportBody').append(`
                <tr class="bg-danger text-white">
                    <td colspan="13" class="text-center fw-bold py-2">
                        ⚠ One or more products have <strong>Negative Stock</strong> due to force sales. Please review and restock.
                    </td>
                </tr>
            `);
        }

        // ============= UPDATE FOOTER TOTALS =============
        $('#footOpening').text(sumOpening.toFixed(2));
        $('#footPurchased').text(sumPurchased.toFixed(2));
        $('#footPurchAmount').text('Rs. ' + sumPurchAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#footDelivered').text(sumDelivered.toFixed(2));
        $('#footDeliveredAmount').text('Rs. ' + sumDeliveredAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#footReserved').text(sumReserved.toFixed(2));
        $('#footBalance').text(sumBalance.toFixed(2));

        let grandTotalVal = parseFloat(grandTotal);
        let grandTotalEl  = $('#grandStockValue');
        grandTotalEl.text('Rs. ' + grandTotalVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        if (hasNegativeStock) {
            grandTotalEl.addClass('text-danger');
        } else {
            grandTotalEl.removeClass('text-danger');
        }

        // ============= UPDATE KPI SUMMARY CARDS =============
        $('#kpiTotalBalance').text(sumBalance.toFixed(2));
        $('#kpiTotalDelivered').text(sumDelivered.toFixed(2));
        $('#kpiTotalReserved').text(sumReserved.toFixed(2));
        $('#kpiTotalStockValue').text('Rs. ' + grandTotalVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#kpiSummaryCards').slideDown();
        
        // Reinitialize DataTable with new data
        stockTable = $('#stockTable').DataTable({
            paging: true,
            searching: true,
            info: true,
            ordering: true,
            columnDefs: [
                { orderable: false, targets: -1 } // Disable sorting on action column
            ]
        });

        // ============= ATTACH CLICK HANDLERS TO WAREHOUSE BUTTONS =============
        $(document).on('click', '.warehouse-btn', function() {
            let productId = $(this).data('product-id');
            let dataKey = 'product_' + productId;
            
            if (warehouseDataStore[dataKey]) {
                let data = warehouseDataStore[dataKey];
                showWarehouseBreakdown(data.itemCode, data.itemName, data.warehouses);
            }
        });
    }

    // ============= SHOW WAREHOUSE BREAKDOWN MODAL =============
    function showWarehouseBreakdown(itemCode, itemName, warehouses) {
        $('#modalProductName').text(`${itemCode} - ${itemName}`);
        $('#warehouseTableBody').html('');
        
        let totalQty = 0;
        
        if (!warehouses || warehouses.length === 0) {
            $('#warehouseTableBody').html('<tr><td colspan="3" class="text-center text-muted">No warehouse data available</td></tr>');
            $('#warehouseModal').modal('show');
            return;
        }
        
        warehouses.forEach(function(w) {
            let qty = parseFloat(w.qty || 0);
            totalQty += qty;
            let qtyClass = qty < 0 ? 'text-danger fw-bold' : '';
            let qtyLabel = qty < 0 ? ` <span class="badge badge-danger">⚠ Negative</span>` : '';

            let row = `
                <tr>
                    <td><strong>${w.warehouse_name || 'Unknown'}</strong></td>
                    <td>${w.location || '-'}</td>
                    <td class="text-end ${qtyClass}">${qty.toFixed(2)}${qtyLabel}</td>
                </tr>
            `;
            $('#warehouseTableBody').append(row);
        });
        
        // Add total row
        $('#warehouseTableBody').append(`
            <tr class="fw-bold bg-light">
                <td colspan="2" class="text-end">Total Quantity:</td>
                <td class="text-end"><strong>${totalQty.toFixed(2)}</strong></td>
            </tr>
        `);
        
        $('#warehouseModal').modal('show');
    }

    // ============= MANUAL CLOSE BUTTON HANDLERS =============
    $(document).on('click', '[data-dismiss="modal"]', function(){
        $('#warehouseModal').modal('hide');
    });

    $('#btnSearch').on('click', function() { fetchReport(); });
    $('#product_id').on('keypress', function(e){ if(e.key==='Enter'){ e.preventDefault(); fetchReport(); } });
    
    // ✅ ERP STANDARD: When super admin changes branch, reload products for that branch
    $('#branch_id').on('change', function() {
        var selectedBranchId = $(this).val();
        
        // Fetch products for the selected branch
        $.ajax({
            url: "{{ route('report.item_stock') }}",
            type: "GET",
            data: { branch_id: selectedBranchId },
            success: function(html) {
                // Extract product options from the new HTML
                var newProductOptions = $(html).find('#product_id').html();
                $('#product_id').html(newProductOptions);
                
                // Auto-fetch report with new branch
                fetchReport();
            },
            error: function() {
                console.error('Error loading products for branch');
                // Still fetch report with current branch
                fetchReport();
            }
        });
    });

    function fetchReport() {
        var productId = $('#product_id').val();
        var branchId = $('#branch_id').val() || '{{ $selectedBranchId }}'; // Use default branch if not in dropdown
        
        $('#loader').show();
        $.ajax({
            url: "{{ route('report.item_stock.fetch') }}",
            type: "POST",
            data: { 
                _token: "{{ csrf_token() }}", 
                product_id: productId,
                branch_id: branchId
            },
            success: function(response) {
                $('#loader').hide();
                renderRows(response.data || [], response.grand_total || 0);
            },
            error: function(xhr, status, err) {
                $('#loader').hide();
                alert('Error fetching report. See console.');
                console.error(xhr.responseText || err);
            }
        });
    }

    // Initial load
    fetchReport();

    /* ---------- WhatsApp Share ---------- */
    window.shareWhatsApp = function() {
        Swal.fire({
            title: 'Preparing WhatsApp Share...',
            text: 'Generating PDF document to share.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        var element = document.getElementById('reportContent');
        var opt = {
          margin:       0.2,
          filename:     'Item_Stock_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
            var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Item Stock Report',
                    text: 'Please find the attached Item Stock Report.',
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
            var url = URL.createObjectURL(pdfBlob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            
            var msg = "*Item Stock Report*\nPlease find the attached PDF document.";
            var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
            window.open(waUrl, '_blank');
        });
    }

    /* ---------- Export Options & PDF ---------- */
    window.showExportOptions = function() {
        Swal.fire({
            title: 'Export Stock Report',
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
            didOpen: () => { Swal.showLoading(); }
        });

        var element = document.getElementById('reportContent');
        var opt = {
          margin:       0.2,
          filename:     'Item_Stock_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).save().then(function() {
            Swal.close();
        });
    };

    window.exportCSV = function () {
        var rows = [['Item Code','Item Name','Opening Stock','Purchased Qty','Purchased Amount','Delivered Qty (دیے)','Delivered Value','Reserved Qty (ریزرورڈ)','Balance','Price (قیمت)','Stock Value']];
        
        $('#reportBody tr').each(function () {
            var cells = [];
            $(this).find('td').each(function (idx) {
                if(idx === 0 || idx === 12) return; // Skip # and Action columns
                var text = $(this).text().replace(/Rs\.\s?/g, '').trim().replace(/"/g, '""');
                cells.push('"' + text + '"');
            });
            if (cells.length) rows.push(cells);
        });
        
        // Add grand total
        rows.push(['','','','','','','','','','Grand Total','"' + $('#grandStockValue').text().replace(/Rs\.\s?/g, '') + '"']);

        var csv  = rows.map(function(r){return r.join(',');}).join('\n');
        var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8;'});
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href   = url;
        a.download = 'item_stock_report_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
    };
});
</script>
@endsection

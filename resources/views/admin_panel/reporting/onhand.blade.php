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
        letter-spacing: 0.04em;
        margin-bottom: 5px;
        display: block;
    }

    .rpt-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    @media (max-width: 992px) {
        .rpt-kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .rpt-kpi-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 13px 16px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .rpt-kpi-card.highlight {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #a7f3d0;
    }

    .rpt-kpi-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .rpt-kpi-val {
        font-size: 18px;
        font-weight: 800;
        color: var(--coa-navy);
        font-family: monospace;
    }

    .rpt-kpi-val.emerald { color: #047857; }
    .rpt-kpi-val.purple { color: #7c3aed; }
    .rpt-kpi-val.amber { color: #d97706; }

    .rpt-kpi-icon {
        width: 38px;
        height: 38px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .kpi-icon-blue { background: #e0f2fe; color: #0284c7; }
    .kpi-icon-emerald { background: #d1fae5; color: #059669; }
    .kpi-icon-purple { background: #f3e8ff; color: #7c3aed; }
    .kpi-icon-amber { background: #fef3c7; color: #d97706; }

    #onhandTable {
        border-collapse: collapse;
        width: 100%;
    }

    #onhandTable thead th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 10px;
        border: 1px solid #1e3a5f;
    }

    #onhandTable tbody td {
        padding: 8px 10px;
        vertical-align: middle;
        border: 1px solid #e2e8f0;
        font-size: 12px;
    }

    #onhandTable tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    #onhandTable tbody tr:hover {
        background-color: #f1f5f9 !important;
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
                        <h4 class="rpt-header-title">Inventory On-Hand Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-warehouse mr-1" style="color: var(--coa-gold);"></i> Real-time Stock Valuation & Availability Status &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" onclick="openPrintPreview()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.4);" title="Clean Full-Page Preview">
                        <i class="fas fa-eye mr-1"></i> Preview
                    </button>
                    <button type="button" onclick="shareWhatsApp()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(37, 211, 102, 0.2); border-color: #25D366; color: #25D366;">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button type="button" onclick="showExportOptions()" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-download mr-1 text-primary"></i> Export
                    </button>
                </div>
            </div>

            {{-- 2. ERP Standard Filter Card --}}
            <div class="card shadow-sm border-0 mb-3" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form id="onhandFilterForm" class="row g-2 align-items-end mb-0">
                        @if($isSuper)
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <label class="f-label"><i class="fas fa-building mr-1 text-primary"></i> Select Branch</label>
                                <select id="filter_branch" name="branch_id" class="form-control form-control-sm select2">
                                    <option value="all">-- All Branches --</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ (isset($branchId) && $branchId == $b->id) ? 'selected' : '' }}>
                                            {{ $b->name ?? $b->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" id="filter_branch" name="branch_id" value="{{ $branchId }}">
                        @endif

                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <label class="f-label"><i class="fas fa-warehouse mr-1 text-info"></i> Select Warehouse</label>
                            <select id="filter_warehouse" name="warehouse_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Warehouses --</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" {{ (isset($warehouseId) && $warehouseId == $w->id) ? 'selected' : '' }}>
                                        {{ $w->warehouse_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <label class="f-label"><i class="fas fa-th-large mr-1 text-warning"></i> Select Category</label>
                            <select id="filter_category" name="category_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Categories --</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ (isset($categoryId) && $categoryId == $c->id) ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <label class="f-label"><i class="fas fa-copyright mr-1 text-danger"></i> Select Brand</label>
                            <select id="filter_brand" name="brand_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Brands --</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}" {{ (isset($brandId) && $brandId == $b->id) ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <label class="f-label"><i class="fas fa-car mr-1" style="color: #7c3aed;"></i> Select Model</label>
                            <select id="filter_model" name="model" class="form-control form-control-sm select2">
                                <option value="all">-- All Models --</option>
                                @foreach($models as $m)
                                    <option value="{{ $m }}" {{ (isset($modelName) && $modelName == $m) ? 'selected' : '' }}>
                                        {{ $m }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <label class="f-label"><i class="fas fa-box-open mr-1 text-secondary"></i> Select Product</label>
                            <select id="filter_product" name="product_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Products --</option>
                                @foreach($allProducts as $p)
                                    <option value="{{ $p->id }}" {{ (isset($productId) && $productId == $p->id) ? 'selected' : '' }}>
                                        {{ $p->item_name }} ({{ $p->item_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <label class="f-label"><i class="fas fa-filter mr-1" style="color: #047857;"></i> Stock Status</label>
                            <select id="filter_stock_status" name="stock_status" class="form-control form-control-sm select2">
                                <option value="in_stock" {{ (!isset($stockStatus) || $stockStatus == 'in_stock') ? 'selected' : '' }}>Available Only (> 0)</option>
                                <option value="all" {{ (isset($stockStatus) && $stockStatus == 'all') ? 'selected' : '' }}>-- All Stock (Inc. 0) --</option>
                                <option value="out_of_stock" {{ (isset($stockStatus) && $stockStatus == 'out_of_stock') ? 'selected' : '' }}>Out of Stock (= 0)</option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 d-flex gap-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary flex-grow-1 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <button type="button" id="btnReset" class="btn btn-sm btn-light border font-weight-bold text-muted d-inline-flex align-items-center justify-content-center" style="height: 38px; border-radius: 6px; width: 38px;" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. Summary KPI Cards --}}
            @php
                $totalItems = $rows->count();
                $totalQty = $rows->sum('onhand_qty');
                $partsCount = $rows->where('is_part', 1)->count();
                $assembledCount = $rows->where('is_assembled', 1)->count();
            @endphp
            <div class="rpt-kpi-grid">
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Products</div>
                        <div class="rpt-kpi-val" id="stat_total_items">{{ number_format($totalItems) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-blue">
                        <i class="fas fa-cube"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card highlight">
                    <div>
                        <div class="rpt-kpi-label" style="color: #047857;">Total On-Hand Qty</div>
                        <div class="rpt-kpi-val emerald" id="stat_total_qty">{{ number_format($totalQty, 2) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-cubes"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Parts Items</div>
                        <div class="rpt-kpi-val purple" id="stat_parts_count">{{ number_format($partsCount) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-purple">
                        <i class="fas fa-cogs"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Assembled Items</div>
                        <div class="rpt-kpi-val amber" id="stat_assembled_count">{{ number_format($assembledCount) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-amber">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Report Table --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="reportContent">
                <div class="card-body p-3">
                    
                    {{-- PDF HEADER (HIDDEN ON SCREEN) --}}
                    <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #0f1f38; padding-bottom:10px;">
                        <h2 style="margin:0; color:#0f1f38; text-transform:uppercase; letter-spacing:1px;">Inventory On-Hand Report</h2>
                        <p style="margin:5px 0; font-size:13px; color:#333;" id="pdfFilterInfo">Real-time Stock Availability Status</p>
                        <p style="margin:0; font-size:11.5px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:30px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Updating inventory balance...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="onhandTable" class="table table-bordered mb-0" style="font-size:12.5px; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 40px;">#</th>
                                    <th style="width: 110px; white-space: nowrap;">Item Code</th>
                                    <th>Product Name</th>
                                    <th style="width: 130px;">Category</th>
                                    <th style="width: 130px;">Brand</th>
                                    <th style="width: 120px;">Model</th>
                                    <th style="width: 90px;">UOM</th>
                                    <th class="text-center" style="width: 100px;">Status</th>
                                    <th class="text-end" style="width: 130px; white-space: nowrap;">On-Hand Qty</th>
                                </tr>
                            </thead>
                            <tbody id="onhandBody">
                                @forelse($rows as $i => $r)
                                <tr>
                                    <td class="text-center text-muted" style="font-size: 11px;">{{ $i + 1 }}</td>
                                    <td style="font-family: monospace; font-weight: 700; color: #1e3a5f; font-size: 12px;">{{ $r->item_code }}</td>
                                    <td class="font-weight-bold text-dark" style="font-size: 12.5px;">{{ $r->item_name }}</td>
                                    <td>{{ $r->category_name ?: '—' }}</td>
                                    <td>{{ $r->brand_name ?: '—' }}</td>
                                    <td>{{ $r->model ?: '—' }}</td>
                                    <td>{{ $r->unit_name ?: 'Piece' }}</td>
                                    <td class="text-center">
                                        @if($r->is_part)
                                            <span class="badge" style="background:#ede9fe; color:#6d28d9; border:1px solid #ddd6fe; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">Part</span>
                                        @elseif($r->is_assembled)
                                            <span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">Assembled</span>
                                        @else
                                            <span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">Simple</span>
                                        @endif
                                    </td>
                                    <td class="text-end font-monospace font-weight-bold" style="font-size: 13px; color: {{ $r->onhand_qty > 0 ? '#047857' : '#dc2626' }}; background: {{ $r->onhand_qty > 0 ? 'rgba(13, 159, 110, 0.06)' : 'rgba(220, 38, 38, 0.03)' }};">
                                        {{ number_format($r->onhand_qty, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">No stock items found for selected filter criteria.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                                    <td colspan="8" class="text-end font-weight-bold" style="font-family: sans-serif;">Total On-Hand:</td>
                                    <td class="text-end text-success font-weight-bold" id="footerTotalQty" style="font-size: 13.5px; background: rgba(13, 159, 110, 0.12);">
                                        {{ number_format($totalQty, 2) }}
                                    </td>
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
                    <div class="print-doc-title">Inventory On-Hand Report</div>
                    <div class="print-doc-period" id="p_doc_period">Real-time Stock Availability Status</div>
                    <div class="print-top-divider"></div>
                </div>

                <table class="print-tb-table">
                    <colgroup>
                        <col style="width: 3%;">
                        <col style="width: 12%;">
                        <col style="width: 25%;">
                        <col style="width: 12%;">
                        <col style="width: 11%;">
                        <col style="width: 11%;">
                        <col style="width: 7%;">
                        <col style="width: 7%;">
                        <col style="width: 12%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th style="text-align:center;">#</th>
                            <th>Item Code</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th style="text-align:center;">UOM</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:right;">On-Hand Qty</th>
                        </tr>
                    </thead>
                    <tbody id="p_onhand_tbody">
                        @forelse($rows as $i => $r)
                        @php
                            $qty = (float)($r->onhand_qty ?? 0);
                            $status = $r->is_part ? 'Part' : ($r->is_assembled ? 'Assembled' : 'Simple');
                        @endphp
                        <tr>
                            <td style="text-align:center; color:#64748b;">{{ $i + 1 }}</td>
                            <td style="font-weight:700; font-family:monospace; color:#1e3a5f;">{{ $r->item_code }}</td>
                            <td style="font-weight:700; color:#0f172a;">{{ $r->item_name }}</td>
                            <td>{{ $r->category_name ?: '—' }}</td>
                            <td>{{ $r->brand_name ?: '—' }}</td>
                            <td>{{ $r->model ?: '—' }}</td>
                            <td style="text-align:center;">{{ $r->unit_name ?: 'Piece' }}</td>
                            <td style="text-align:center;"><span style="font-size:8pt; font-weight:700; padding:1px 6px; border:1px solid #cbd5e1; border-radius:3px;">{{ $status }}</span></td>
                            <td class="num-cell" style="font-weight:700; color: {{ $qty > 0 ? '#047857' : '#dc2626' }};">{{ number_format($qty, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:18px; color:#64748b;">No stock items found for selected filter criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="p-tfoot">
                            <td colspan="8" style="text-align:right; font-weight:800; padding-right:10px;">TOTAL ON-HAND:</td>
                            <td class="num-cell" style="font-weight:800; color:#047857;" id="p_foot_total_qty">{{ number_format($totalQty, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="print-doc-footer">
                    <div class="print-footer-info">
                        <span>Printed on: <span id="p_print_time">{{ date('d M Y h:i A') }}</span></span>
                        <span>Inventory Valuation &amp; Stock Availability</span>
                        <span>ERP System &mdash; Financial &amp; Stock Report</span>
                    </div>
                    <div class="print-sign-row">
                        <div class="print-sign-box">Prepared By</div>
                        <div class="print-sign-box">Store In-Charge</div>
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
                        <span style="font-weight:700; font-size:14px; letter-spacing:.02em;">Inventory On-Hand Report &mdash; Print Document Preview</span>
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
    .lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }

    /* ── Formal Print Document Styles ──── */
    #formalPrintDoc {
        display: none;
        background: #fff;
        color: #000;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .print-doc-header { margin-bottom: 8px; }
    .print-company-title { font-size: 14pt; font-weight: 800; color: #000; margin-bottom: 2px; text-transform: uppercase; letter-spacing: .02em; }
    .print-doc-title { font-size: 19pt; font-weight: 900; color: #000; margin-bottom: 2px; }
    .print-doc-period { font-size: 10pt; font-weight: 600; color: #1e293b; }
    .print-top-divider { border-bottom: 2px solid #000; margin-top: 6px; margin-bottom: 10px; }

    .print-tb-table { width: 100% !important; max-width: 100% !important; table-layout: fixed !important; border-collapse: collapse !important; font-size: 9pt !important; margin-bottom: 12px !important; color: #000 !important; }
    .print-tb-table thead { display: table-header-group !important; }
    .print-tb-table th { border-top: 1.5px solid #000 !important; border-bottom: 1.5px solid #000 !important; padding: 6px 4px !important; background: #f8fafc !important; font-weight: 800 !important; color: #000 !important; font-size: 8pt !important; text-transform: uppercase !important; vertical-align: middle !important; }
    .print-tb-table td { border-bottom: 1px solid #cbd5e1 !important; padding: 5px 4px !important; vertical-align: middle !important; color: #000 !important; font-size: 8.5pt !important; line-height: 1.25 !important; overflow: hidden; word-wrap: break-word; }
    .print-tb-table tr { page-break-inside: avoid !important; }
    .print-tb-table .num-cell { text-align: right !important; font-variant-numeric: tabular-nums !important; font-feature-settings: "tnum" !important; white-space: nowrap !important; font-size: 8.5pt !important; letter-spacing: -0.01em !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }
    .print-tb-table tr.p-tfoot td { border-top: 1.5px solid #000 !important; border-bottom: 3px double #000 !important; font-weight: 800 !important; padding: 7px 4px !important; background: #fff !important; font-size: 9pt !important; color: #000 !important; }

    .print-doc-footer { margin-top: 22px; font-size: 8.5pt; color: #000; page-break-inside: avoid; }
    .print-footer-info { display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 5px; margin-bottom: 36px; font-weight: 600; color: #000; font-size: 8pt; }
    .print-sign-row { display: flex; justify-content: space-between; padding: 0 15px; }
    .print-sign-box { border-top: 1.5px dashed #000; width: 140px; text-align: center; padding-top: 5px; font-size: 8.5pt; font-weight: 700; color: #000; }

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
        padding: 30px 32px;
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
        .rpt-wrapper, .navbar, .sidebar, .rpt-header-bar, #onhandFilterForm, .rpt-kpi-grid, #reportContent, .no-print, .btn, .card {
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
    const isSuperAdmin = {{ $isSuper ? 'true' : 'false' }};

    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            dropdownParent: $('body')
        });

        // Dynamic Warehouse Loading when Branch changes (for Super Admin)
        $('#filter_branch').on('change', function() {
            let branchId = $(this).val();
            let warehouseSelect = $('#filter_warehouse');

            warehouseSelect.html('<option value="all">Loading...</option>');

            if (branchId === 'all' || !branchId) {
                // Fetch all warehouses
                $.ajax({
                    url: "{{ route('warehouses-by-branch') }}",
                    type: "GET",
                    data: { branch_id: '' },
                    success: function(res) {
                        let html = '<option value="all">-- All Warehouses --</option>';
                        res.forEach(function(w) {
                            html += `<option value="${w.id}">${w.warehouse_name}</option>`;
                        });
                        warehouseSelect.html(html).trigger('change');
                    },
                    error: function() {
                        warehouseSelect.html('<option value="all">-- All Warehouses --</option>').trigger('change');
                    }
                });
            } else {
                $.ajax({
                    url: "{{ route('warehouses-by-branch') }}",
                    type: "GET",
                    data: { branch_id: branchId },
                    success: function(res) {
                        let html = '<option value="all">-- All Warehouses --</option>';
                        res.forEach(function(w) {
                            html += `<option value="${w.id}">${w.warehouse_name}</option>`;
                        });
                        warehouseSelect.html(html).trigger('change');
                    },
                    error: function() {
                        warehouseSelect.html('<option value="all">-- All Warehouses --</option>').trigger('change');
                    }
                });
            }
        });

        // Filter Click
        $('#btnSearch').on('click', function() {
            fetchOnhandReport();
        });

        // Reset Click
        $('#btnReset').on('click', function() {
            if (isSuperAdmin) {
                $('#filter_branch').val('all').trigger('change');
            }
            $('#filter_warehouse').val('all').trigger('change');
            $('#filter_category').val('all').trigger('change');
            $('#filter_brand').val('all').trigger('change');
            $('#filter_model').val('all').trigger('change');
            $('#filter_product').val('all').trigger('change');
            $('#filter_stock_status').val('in_stock').trigger('change');
            setTimeout(fetchOnhandReport, 200);
        });

        function fetchOnhandReport() {
            let branchId = $('#filter_branch').val() || '';
            let warehouseId = $('#filter_warehouse').val() || '';
            let categoryId = $('#filter_category').val() || '';
            let brandId = $('#filter_brand').val() || '';
            let modelName = $('#filter_model').val() || '';
            let productId = $('#filter_product').val() || '';
            let stockStatus = $('#filter_stock_status').val() || 'in_stock';

            $('#loader').show();

            $.ajax({
                url: "{{ route('reports.onhand') }}",
                type: "GET",
                data: {
                    branch_id: branchId,
                    warehouse_id: warehouseId,
                    category_id: categoryId,
                    brand_id: brandId,
                    model: modelName,
                    product_id: productId,
                    stock_status: stockStatus
                },
                success: function(res) {
                    $('#loader').hide();
                    renderOnhandTable(res);
                },
                error: function() {
                    $('#loader').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch inventory report data.'
                    });
                }
            });
        }

        function renderOnhandTable(data) {
            // Update KPIs
            $('#stat_total_items').text(Number(data.totalItems).toLocaleString());
            $('#stat_total_qty').text(Number(data.totalQty).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#stat_parts_count').text(Number(data.partsCount).toLocaleString());
            $('#stat_assembled_count').text(Number(data.assembledCount).toLocaleString());
            $('#footerTotalQty').text(Number(data.totalQty).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            let rows = data.rows || [];
            let tbodyHtml = '';

            if (rows.length === 0) {
                tbodyHtml = '<tr><td colspan="9" class="text-center py-4 text-muted">No stock items found for selected filter criteria.</td></tr>';
            } else {
                rows.forEach((r, idx) => {
                    let qty = parseFloat(r.onhand_qty) || 0;
                    let qtyColor = qty > 0 ? '#047857' : '#dc2626';
                    let qtyBg = qty > 0 ? 'rgba(13, 159, 110, 0.06)' : 'rgba(220, 38, 38, 0.03)';

                    let statusBadge = '';
                    if (r.is_part == 1) {
                        statusBadge = '<span class="badge" style="background:#ede9fe; color:#6d28d9; border:1px solid #ddd6fe; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">Part</span>';
                    } else if (r.is_assembled == 1) {
                        statusBadge = '<span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">Assembled</span>';
                    } else {
                        statusBadge = '<span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">Simple</span>';
                    }

                    tbodyHtml += `<tr>
                        <td class="text-center text-muted" style="font-size: 11px;">${idx + 1}</td>
                        <td style="font-family: monospace; font-weight: 700; color: #1e3a5f; font-size: 12px;">${r.item_code}</td>
                        <td class="font-weight-bold text-dark" style="font-size: 12.5px;">${r.item_name}</td>
                        <td>${r.category_name || '—'}</td>
                        <td>${r.brand_name || '—'}</td>
                        <td>${r.model || '—'}</td>
                        <td>${r.unit_name || 'Piece'}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-end font-monospace font-weight-bold" style="font-size: 13px; color: ${qtyColor}; background: ${qtyBg};">
                            ${qty.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                        </td>
                    </tr>`;
                });
            }

            $('#onhandBody').html(tbodyHtml);

            // Update PDF filter subtitle
            let filterDesc = [];
            if (isSuperAdmin && $('#filter_branch').val() !== 'all') {
                filterDesc.push('Branch: ' + $('#filter_branch option:selected').text().trim());
            }
            if ($('#filter_warehouse').val() !== 'all') {
                filterDesc.push('Warehouse: ' + $('#filter_warehouse option:selected').text().trim());
            }
            if ($('#filter_category').val() !== 'all') {
                filterDesc.push('Category: ' + $('#filter_category option:selected').text().trim());
            }
            if ($('#filter_brand').val() !== 'all') {
                filterDesc.push('Brand: ' + $('#filter_brand option:selected').text().trim());
            }
            if ($('#filter_model').val() !== 'all') {
                filterDesc.push('Model: ' + $('#filter_model option:selected').text().trim());
            }
            if ($('#filter_product').val() !== 'all') {
                filterDesc.push('Product: ' + $('#filter_product option:selected').text().trim());
            }
            if ($('#filter_stock_status').val() !== 'in_stock') {
                filterDesc.push('Status: ' + $('#filter_stock_status option:selected').text().trim());
            }
            $('#pdfFilterInfo').text(filterDesc.length > 0 ? filterDesc.join(' | ') : 'All Branches & Warehouses');

            // Synchronize formal print document
            renderFormalPrintDoc(data);
        }
        
        /* ---------- WhatsApp Share ---------- */
        window.shareWhatsApp = function() {
            Swal.fire({
                title: 'Preparing WhatsApp Share...',
                text: 'Generating PDF document to share.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            var element = document.getElementById('reportContent');
            $('#pdfHeader').show(); 

            var opt = {
                margin:       0.2,
                filename:     'Onhand_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
                $('#pdfHeader').hide(); 
                var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
                
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    navigator.share({
                        title: 'Inventory Report',
                        text: 'Please find the attached Inventory On-Hand Report.',
                        files: [file]
                    }).then(() => {
                        Swal.close();
                    }).catch((error) => {
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
                text: 'The PDF will be downloaded. Please attach it manually to your WhatsApp chat.',
                confirmButtonText: 'Download & Open WhatsApp'
            }).then(() => {
                var url = URL.createObjectURL(pdfBlob);
                var a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                var msg = "*Inventory On-Hand Report*\nPlease find the attached PDF document.";
                var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
                window.open(waUrl, '_blank');
            });
        }

        /* ---------- Export Options ---------- */
        window.showExportOptions = function() {
            Swal.fire({
                title: 'Export Inventory Report',
                text: 'Choose your format:',
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

            var element = document.getElementById('reportContent');
            $('#pdfHeader').show(); 

            var opt = {
                margin:       0.2,
                filename:     'Onhand_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                $('#pdfHeader').hide(); 
                Swal.close();
            });
        };

        window.exportCSV = function () {
            let csv = [];
            let headers = [];
            $("#onhandTable thead th").each(function() {
                headers.push('"' + $(this).text().trim() + '"');
            });
            csv.push(headers.join(","));

            $("#onhandTable tbody tr").each(function() {
                let row = [];
                $(this).find('td').each(function() {
                    let cellText = $(this).text().trim();
                    row.push('"' + cellText.replace(/"/g, '""') + '"');
                });
                csv.push(row.join(","));
            });

            // Add footer
            let footer = [];
            $("#onhandTable tfoot td").each(function() {
                footer.push('"' + $(this).text().trim() + '"');
            });
            csv.push(footer.join(","));

            let csvString = "\uFEFF" + csv.join("\n");
            let blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            let url = URL.createObjectURL(blob);
            let a = document.createElement("a");
            a.href = url;
            a.download = "onhand_report_" + new Date().toISOString().slice(0,10) + ".csv";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        };
    });

    function renderFormalPrintDoc(data) {
        const rows = data.rows || [];
        const totalQty = parseFloat(data.totalQty || 0);

        // Branch title
        let branchName = 'ALL BRANCHES';
        const branchSelect = document.getElementById('filter_branch');
        if (branchSelect && branchSelect.tagName === 'SELECT' && branchSelect.value !== 'all' && branchSelect.selectedIndex >= 0) {
            branchName = branchSelect.options[branchSelect.selectedIndex].text.trim();
        }
        const branchEl = document.getElementById('p_branch_title');
        if (branchEl) branchEl.textContent = branchName.toUpperCase();

        // Filters subtitle
        let filterParts = [];
        const whSelect = document.getElementById('filter_warehouse');
        if (whSelect && whSelect.value !== 'all' && whSelect.selectedIndex >= 0) {
            filterParts.push('Warehouse: ' + whSelect.options[whSelect.selectedIndex].text.trim());
        }
        const catSelect = document.getElementById('filter_category');
        if (catSelect && catSelect.value !== 'all' && catSelect.selectedIndex >= 0) {
            filterParts.push('Category: ' + catSelect.options[catSelect.selectedIndex].text.trim());
        }
        const brSelect = document.getElementById('filter_brand');
        if (brSelect && brSelect.value !== 'all' && brSelect.selectedIndex >= 0) {
            filterParts.push('Brand: ' + brSelect.options[brSelect.selectedIndex].text.trim());
        }
        const modSelect = document.getElementById('filter_model');
        if (modSelect && modSelect.value !== 'all' && modSelect.selectedIndex >= 0) {
            filterParts.push('Model: ' + modSelect.options[modSelect.selectedIndex].text.trim());
        }
        const prodSelect = document.getElementById('filter_product');
        if (prodSelect && prodSelect.value !== 'all' && prodSelect.selectedIndex >= 0) {
            filterParts.push('Product: ' + prodSelect.options[prodSelect.selectedIndex].text.trim());
        }
        const statusSelect = document.getElementById('filter_stock_status');
        if (statusSelect && statusSelect.value !== 'in_stock' && statusSelect.selectedIndex >= 0) {
            filterParts.push('Status: ' + statusSelect.options[statusSelect.selectedIndex].text.trim());
        }

        const periodEl = document.getElementById('p_doc_period');
        if (periodEl) {
            periodEl.textContent = 'Real-time Stock Availability' + (filterParts.length > 0 ? ' — ' + filterParts.join(' | ') : '');
        }

        // Print timestamp
        const now = new Date();
        const printTimeEl = document.getElementById('p_print_time');
        if (printTimeEl) {
            printTimeEl.textContent = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        }

        // Render Rows
        const tbody = document.getElementById('p_onhand_tbody');
        if (!tbody) return;

        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:18px; color:#64748b;">No stock items found for selected filter criteria.</td></tr>';
        } else {
            let html = '';
            rows.forEach((r, idx) => {
                const qty = parseFloat(r.onhand_qty) || 0;
                const status = (r.is_part == 1) ? 'Part' : ((r.is_assembled == 1) ? 'Assembled' : 'Simple');
                html += `<tr>
                    <td style="text-align:center; color:#64748b;">${idx + 1}</td>
                    <td style="font-weight:700; font-family:monospace; color:#1e3a5f;">${r.item_code || ''}</td>
                    <td style="font-weight:700; color:#0f172a;">${r.item_name || ''}</td>
                    <td>${r.category_name || '—'}</td>
                    <td>${r.brand_name || '—'}</td>
                    <td>${r.model || '—'}</td>
                    <td style="text-align:center;">${r.unit_name || 'Piece'}</td>
                    <td style="text-align:center;"><span style="font-size:8pt; font-weight:700; padding:1px 6px; border:1px solid #cbd5e1; border-radius:3px;">${status}</span></td>
                    <td class="num-cell" style="font-weight:700; color:${qty > 0 ? '#047857' : '#dc2626'};">${qty.toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        // Update Footer
        const footQty = document.getElementById('p_foot_total_qty');
        if (footQty) {
            footQty.textContent = totalQty.toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Live sync preview if modal open
        const previewModal = document.getElementById('reportPreviewModal');
        if (previewModal && previewModal.style.display === 'flex') {
            const formalDoc = document.getElementById('formalPrintDoc');
            const paper = document.getElementById('previewPaperContent');
            if (formalDoc && paper) {
                paper.innerHTML = formalDoc.innerHTML;
            }
        }
    }

    function openPrintPreview() {
        const formalDoc = document.getElementById('formalPrintDoc');
        if (!formalDoc) return;

        const reportHtml = formalDoc.innerHTML;
        const branchTitle = document.getElementById('p_branch_title')?.textContent || 'Company';
        const docTitle = 'Inventory On-Hand Report';
        const fullTitle = `${docTitle} - ${branchTitle}`;

        // 1. Try opening as a clean, full normal page in a new tab
        const win = window.open('', '_blank');
        if (win) {
            win.document.write(`<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${fullTitle}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px 20px 60px 20px;
            background: #f8fafc;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .action-toolbar {
            max-width: 950px;
            margin: 0 auto 14px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .report-badge {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e3a5f;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 15px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #1e293b;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-action:hover { background: #f1f5f9; }
        .btn-primary-action { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
        .btn-primary-action:hover { background: #0f1f38; }

        .normal-page-card {
            max-width: 950px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.06);
            border: 1px solid #cbd5e1;
            padding: 30px 32px;
        }

        .print-doc-header { margin-bottom: 8px; }
        .print-company-title { font-size: 14pt; font-weight: 800; color: #000; margin-bottom: 2px; text-transform: uppercase; letter-spacing: .02em; }
        .print-doc-title { font-size: 19pt; font-weight: 900; color: #000; margin-bottom: 2px; }
        .print-doc-period { font-size: 10pt; font-weight: 600; color: #1e293b; }
        .print-top-divider { border-bottom: 2px solid #000; margin-top: 6px; margin-bottom: 10px; }

        .print-tb-table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 12px; color: #000; table-layout: fixed; }
        .print-tb-table thead { display: table-header-group; }
        .print-tb-table th { border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 6px 4px; background: #f8fafc; font-weight: 800; color: #000; font-size: 8pt; text-transform: uppercase; vertical-align: middle; }
        .print-tb-table td { border-bottom: 1px solid #cbd5e1; padding: 5px 4px; vertical-align: middle; color: #000; font-size: 8.5pt; line-height: 1.25; overflow: hidden; word-wrap: break-word; }
        .print-tb-table .num-cell { text-align: right; font-variant-numeric: tabular-nums; font-feature-settings: "tnum"; white-space: nowrap; font-size: 8.5pt; letter-spacing: -0.01em; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .print-tb-table tr.p-tfoot td { border-top: 1.5px solid #000; border-bottom: 3px double #000; font-weight: 800; padding: 7px 4px; background: #fff; font-size: 9pt; color: #000; }

        .print-doc-footer { margin-top: 22px; font-size: 8.5pt; color: #000; }
        .print-footer-info { display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 5px; margin-bottom: 36px; font-weight: 600; color: #000; font-size: 8pt; }
        .print-sign-row { display: flex; justify-content: space-between; padding: 0 15px; }
        .print-sign-box { border-top: 1.5px dashed #000; width: 140px; text-align: center; padding-top: 5px; font-size: 8.5pt; font-weight: 700; color: #000; }

        @media print {
            @page {
                size: A4 portrait;
                margin: 6mm 6mm 6mm 6mm !important;
            }
            body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
            .action-toolbar, .no-print { display: none !important; }
            .normal-page-card { border: none !important; box-shadow: none !important; padding: 0 !important; max-width: 100% !important; width: 100% !important; margin: 0 !important; }
            .print-tb-table { width: 100% !important; max-width: 100% !important; table-layout: fixed !important; }
            .print-tb-table th, .print-tb-table td { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>
    <div class="action-toolbar no-print">
        <div class="report-badge">
            <i class="fas fa-boxes"></i>
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

        // 2. Fallback if popups blocked
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

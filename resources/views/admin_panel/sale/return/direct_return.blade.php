@extends('admin_panel.layout.app')

@section('content')
    <!-- Loader Overlay -->
    <div id="pageLoader"
        class="position-fixed top-0 start-0 w-100 h-100 flex-column gap-3 justify-content-center align-items-center d-none"
        style="background: rgba(255,255,255,0.9); z-index: 1055; display: none !important;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="fw-bold text-primary fs-5">Processing Direct Return...</div>
    </div>

    <style>
        /* ================= ULTRA-COMPACT EXCEL-LIKE ERP UI ================= */
        body {
            background-color: #f8fafc !important;
            font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
        }

        .main-container {
            border: 1px solid #94a3b8 !important;
            border-radius: 4px !important;
            box-shadow: none !important;
            background-color: #ffffff !important;
            padding: 6px !important;
            font-size: .78rem;
            max-width: 100%;
        }

        .card-panel {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 3px !important;
            padding: 6px !important;
            height: 100%;
        }

        .section-title {
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 0.72rem !important;
            letter-spacing: 0.5px !important;
            color: #1e293b !important;
            margin-bottom: 4px !important;
            border-left: 3px solid #2563eb !important;
            padding-left: 6px !important;
        }

        .form-control,
        .form-select,
        .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 3px !important;
            padding: 2px 6px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            background-color: #ffffff !important;
            transition: all 0.15s ease-in-out !important;
            height: 26px !important;
            font-size: 0.78rem !important;
        }

        .form-control:focus,
        .form-select:focus,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1) !important;
            outline: none !important;
        }

        .input-readonly {
            background-color: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #475569 !important;
            font-weight: 600 !important;
            cursor: not-allowed !important;
        }

        /* Unified Customer Input Group (Taller and Longer) */
        #customerInputWrapper {
            display: flex !important;
            align-items: stretch !important;
            height: 34px !important;
            min-height: 34px !important;
            max-height: 34px !important;
            width: 100% !important;
        }

        #customerInputWrapper #customerSelectBoxContainer {
            flex: 1 1 auto !important;
            min-width: 0 !important;
            height: 34px !important;
        }

        #customerInputWrapper #customerSelectBoxContainer .select2-container {
            width: 100% !important;
            height: 34px !important;
            display: block !important;
        }

        #customerInputWrapper #customerSelectBoxContainer .select2-container .select2-selection--single {
            height: 34px !important;
            min-height: 34px !important;
            max-height: 34px !important;
            line-height: 32px !important;
            border-top-left-radius: 4px !important;
            border-bottom-left-radius: 4px !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            box-sizing: border-box !important;
        }

        #customerInputWrapper #customerSelectBoxContainer .select2-selection__rendered {
            line-height: 32px !important;
            height: 32px !important;
            font-size: 0.82rem !important;
            padding-left: 10px !important;
            padding-right: 24px !important;
            color: #1e293b !important;
            font-weight: 600 !important;
            margin: 0 !important;
            display: block !important;
        }

        #customerInputWrapper #customerSelectBoxContainer .select2-selection__arrow {
            height: 32px !important;
            right: 8px !important;
        }

        #customerInputWrapper .btn {
            height: 34px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0.8rem !important;
        }

        #walkinNameInput {
            height: 34px !important;
            font-size: 0.82rem !important;
        }

        /* Transaction Grid / Table */
        .table-responsive {
            border: 1px solid #cbd5e1 !important;
            border-radius: 2px !important;
            overflow-x: auto !important;
            overflow-y: visible !important;
            box-shadow: none !important;
            min-height: 120px;
            background-color: #ffffff;
        }

        .sales-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100% !important;
            font-size: 0.76rem !important;
            table-layout: fixed;
        }

        .sales-table thead th {
            background-color: #f1f5f9 !important;
            border: 1px solid #94a3b8 !important;
            padding: 3px 4px !important;
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }

        .sales-table thead th.col-product {
            text-align: left !important;
            padding-left: 6px !important;
        }

        .sales-table tbody td {
            border: 1px solid #cbd5e1 !important;
            padding: 0 !important;
            vertical-align: middle !important;
            background-color: #ffffff;
            height: 26px !important;
        }

        .sales-table tbody .form-control,
        .sales-table tbody .form-select {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            height: 26px !important;
            margin: 0 !important;
            padding: 1px 4px !important;
            width: 100% !important;
            background-color: transparent !important;
            text-align: center;
            color: #1e293b !important;
            font-weight: 500 !important;
            font-size: 0.76rem !important;
        }

        .sales-table tbody td.col-product .form-select {
            text-align: left !important;
            padding-left: 6px !important;
        }

        .sales-table tbody .form-control:focus,
        .sales-table tbody .form-select:focus {
            outline: none !important;
            background-color: #eff6ff !important;
            box-shadow: inset 0 0 0 1px #2563eb !important;
        }

        .sales-table tbody .select2-container--default .select2-selection--single {
            height: 26px !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background-color: transparent !important;
            display: flex;
            align-items: center;
        }

        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            padding-left: 4px !important;
            padding-right: 16px !important;
            font-size: 0.76rem !important;
            color: #1e293b !important;
            font-weight: 500 !important;
            text-align: left !important;
        }

        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 26px !important;
            right: 4px !important;
        }

        .sales-table tfoot td {
            background-color: #e2e8f0 !important;
            border: 1px solid #94a3b8 !important;
            border-top: 2px solid #64748b !important;
            padding: 2px 4px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            font-size: 0.78rem !important;
        }

        .sales-table tbody tr:hover td {
            background-color: #f8fafc !important;
        }

        /* Column Widths (Exact Match with Sale Order Grid) */
        .col-idx { width: 25px !important; }
        .col-product { width: auto !important; min-width: 140px !important; }
        .col-warehouse { width: 140px !important; }
        .col-stock { width: 55px !important; }
        .col-qty { width: 110px !important; }
        .col-pieces { width: 50px !important; }
        .col-price-p { width: 80px !important; }
        .col-disc { width: 80px !important; }
        .col-amount { width: 90px !important; }
        .col-action { width: 32px !important; text-align: center; }

        /* Summary Sidebar Layout */
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 0.78rem;
        }

        .summary-val-net {
            font-size: 1.1rem !important;
            font-weight: 800 !important;
            color: #2563eb !important;
        }

        .btn-save-complete {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            border: none !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3) !important;
            transition: all 0.2s ease !important;
        }

        .btn-save-complete:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            transform: translateY(-1px);
        }

        .top-info-fields-row {
            display: grid;
            grid-template-columns: 180px 200px 130px 180px 1fr;
            gap: 6px;
            align-items: flex-end;
            margin-bottom: 4px;
        }

        @media (max-width: 992px) {
            .top-info-fields-row {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>

    <div class="container-fluid px-2 py-1">
        <div class="main-container">
            <form id="directReturnForm" autocomplete="off">
                @csrf
                <input type="hidden" id="customer_id" name="customer_id" value="">
                <input type="hidden" id="customer" name="customer" value="Walk-in Customer">
                <input type="hidden" id="previousBalance" name="previousBalance" value="0">

                {{-- TOP HEADER BAR --}}
                <div class="d-flex justify-content-between align-items-center mb-1 px-1">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('sale.returns.index') }}" class="btn btn-sm btn-light border rounded-circle" title="Back"><i class="fas fa-arrow-left text-secondary"></i></a>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                                <i class="fas fa-undo text-primary"></i> Direct Sale Return
                                <span class="badge font-monospace px-2 py-1 rounded-pill" style="font-size: 0.78rem; background: #e6fcf5; color: #059669; border: 1px solid #a7f3d0;">
                                    <i class="fas fa-file-invoice me-1"></i><span id="headerReturnRef">{{ $nextReturnNumber }}</span>
                                </span>
                            </h5>
                            <small class="text-muted" style="font-size: 0.7rem;">Return items to inventory directly without invoice reference</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('sale.returns.index') }}" class="btn btn-sm btn-light border rounded-3 text-secondary fw-bold" style="font-size:0.75rem;">
                            <i class="fas fa-list text-primary me-1"></i>Sale Returns
                        </a>
                        <button type="button" class="btn btn-sm btn-light border rounded-3 text-secondary" title="Calculator"><i class="fas fa-calculator"></i></button>
                        <button type="button" class="btn btn-sm btn-light border rounded-3 text-secondary" title="Toggle Theme"><i class="fas fa-moon"></i></button>
                        <button type="button" class="btn btn-sm btn-light border rounded-3 text-secondary" title="Fullscreen" onclick="document.documentElement.requestFullscreen()"><i class="fas fa-expand"></i></button>
                    </div>
                </div>

                {{-- TOP INFORMATION PANEL --}}
                <div class="card-panel mb-1 bg-white border rounded-3 shadow-xs" style="padding: 4px 6px !important;">
                    <div class="top-info-fields-row">
                        <!-- Branch Selection -->
                        <div>
                            <label class="form-label fw-bold text-uppercase text-primary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                                <i class="fas fa-building me-1"></i>Branch
                            </label>
                            @if (Auth::user() && Auth::user()->hasRole('super admin'))
                                <select class="form-select fw-bold border-primary shadow-xs" name="branch_id" id="branch_id" style="font-size:0.78rem; height: 28px;">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ (isset($defaultBranchId) && $defaultBranchId == $b->id) || (Auth::user()->branch_id == $b->id) ? 'selected' : '' }}>
                                            {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <div class="form-control form-control-sm bg-light fw-bold text-primary text-truncate border-secondary-subtle" style="font-size:0.78rem; height: 28px; line-height: 20px;">
                                    <i class="fas fa-store me-1"></i>{{ Auth::user()->branch->name ?? 'Default Branch' }}
                                </div>
                                <input type="hidden" name="branch_id" id="branch_id" value="{{ Auth::user()->branch_id ?? 1 }}">
                            @endif
                        </div>

                        <!-- Location (Default) -->
                        <div>
                            <label class="form-label fw-bold text-uppercase text-secondary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                                <i class="fas fa-map-marker-alt text-info me-1"></i>Location (Default)
                            </label>
                            @php
                                $activeBranch = (isset($branches) && count($branches) > 0) ? ($branches->firstWhere('id', $defaultBranchId ?? null) ?? $branches->first()) : null;
                                $assignedWhs = ($activeBranch && $activeBranch->warehouses) ? $activeBranch->warehouses : collect();
                            @endphp
                            <select class="form-select fw-bold shadow-xs" name="warehouse_id" id="globalWarehouseSelect" style="font-size:0.78rem; height: 28px;">
                                @if($activeBranch)
                                    <optgroup label="Shops / Branches (Shop Stock)">
                                        <option value="branch_{{ $activeBranch->id }}" selected>
                                            {{ $activeBranch->name }} (Shop)
                                        </option>
                                    </optgroup>
                                @endif
                                @if(isset($assignedWhs) && count($assignedWhs) > 0)
                                    <optgroup label="Warehouses (Godown Stock)">
                                        @foreach($assignedWhs as $w)
                                            <option value="warehouse_{{ $w->id }}">
                                                {{ $w->warehouse_name ?? $w->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>

                        <!-- Date -->
                        <div>
                            <label class="form-label fw-bold text-uppercase text-secondary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                                <i class="far fa-calendar-alt text-success me-1"></i>Date
                            </label>
                            <input type="date" name="return_date" class="form-control text-center fw-bold shadow-xs" id="returnDateInput" value="{{ date('Y-m-d') }}" style="font-size:0.78rem; height: 28px;">
                        </div>

                        <!-- Return Reference (Matching Image 4) -->
                        <div>
                            <label class="form-label fw-bold text-uppercase text-primary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                                <i class="fas fa-barcode text-primary me-1"></i>Return Reference:
                            </label>
                            <input type="text" class="form-control fw-bold shadow-xs text-dark" name="reference" id="referenceInput" value="{{ $nextReturnNumber }}" style="font-size:0.78rem; height: 28px;">
                        </div>

                        <!-- Return Note (Matching Image 4) -->
                        <div>
                            <label class="form-label fw-bold text-uppercase text-secondary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px; margin-bottom: 2px !important;">
                                <i class="fas fa-sticky-note text-warning me-1"></i>Return Note:
                            </label>
                            <input type="text" class="form-control shadow-xs" name="return_note" id="returnNote" placeholder="Reason for return..." style="font-size:0.78rem; height: 28px;">
                        </div>
                    </div>

                    <!-- Customer Selection Row (Taller & Longer) -->
                    <div style="margin-top: 3px;">
                        <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 2px !important;">
                            <label class="form-label fw-bold text-uppercase text-primary mb-0 d-flex align-items-center" style="font-size:0.67rem; letter-spacing:0.3px;">
                                <i class="fas fa-user-circle text-primary me-1"></i>Customer Selection
                            </label>
                            <div id="customerPrevBalWrapper" class="d-flex align-items-center gap-1" style="font-size:0.75rem;">
                                <span class="text-muted fw-semibold">Prev. Balance:</span>
                                <span id="badgeCustomerPrevBal" class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold font-monospace px-2 py-0" style="font-size:0.78rem;">Rs 0.00</span>
                            </div>
                        </div>
                        <div id="customerInputWrapper" class="shadow-xs rounded">
                            <div id="customerSelectBoxContainer">
                                <select class="form-select" id="customerSelect" name="customer_select" style="width:100%">
                                    <option value="">Select or search customer...</option>
                                    @if(isset($customer) && $customer->count() > 0)
                                        @foreach($customer as $c)
                                            <option value="{{ $c->id }}"
                                                data-name="{{ $c->customer_name }}"
                                                data-mobile="{{ $c->mobile ?? '' }}"
                                                data-prev-balance="{{ $c->closing_balance ?? $c->opening_balance ?? 0 }}">
                                                {{ $c->customer_id }} — {{ $c->customer_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <input type="text" class="form-control fw-bold d-none" name="walkin_name" id="walkinNameInput" value="Walk-in Customer" placeholder="Enter Walk-in Name...">
                            <button type="button" class="btn btn-outline-primary fw-bold px-3 d-flex align-items-center gap-1" id="btnWalkinToggle">
                                <i class="fas fa-walking"></i> Walk-in
                            </button>
                            <button type="button" class="btn btn-outline-success fw-bold px-3 d-flex align-items-center gap-1" id="btnAddNewCustomerModal" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                <i class="fas fa-user-plus"></i> New
                            </button>
                            <input type="checkbox" id="walkinToggle" name="is_walkin" value="1" class="d-none">
                        </div>
                    </div>
                </div>

                <!-- 2-COLUMN RESPONSIVE LAYOUT -->
                <div class="row align-items-stretch">
                    <!-- LEFT MAIN AREA: Items Grid Table -->
                    <div class="col-12 col-lg-8 col-xl-9">
                        <div class="card-panel d-flex flex-column h-100 bg-white" style="border-radius:4px; padding: 6px 8px !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="section-title mb-0" style="font-size:0.8rem;">Order Items (<span id="itemsRowCount">0</span>)</div>
                                </div>
                                <div class="d-flex gap-1 ms-auto">
                                    <button type="button" class="btn btn-primary btn-sm py-1 px-3 rounded-pill fw-bold" id="btnAddRow" style="font-size:0.75rem;">
                                        <i class="fas fa-plus me-1"></i>Add Row
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive flex-grow-1" style="overflow-x: auto; overflow-y: visible;">
                                <table class="table table-bordered sales-table mb-0" id="returnTable" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th class="col-idx text-center">#</th>
                                            <th class="col-product text-start">PRODUCT / MODEL</th>
                                            <th class="col-warehouse">WAREHOUSE</th>
                                            <th class="col-stock">STOCK</th>
                                            <th class="col-qty">QTY</th>
                                            <th class="col-pieces">PCS</th>
                                            <th class="col-price-p">PRICE</th>
                                            <th class="col-disc">DISCOUNT</th>
                                            <th class="col-amount">AMOUNT</th>
                                            <th class="col-action">×</th>
                                        </tr>
                                    </thead>
                                    <tbody id="returnTableBody">
                                        <!-- Rows added by JS -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">GRID TOTAL:</td>
                                            <td class="text-center fw-bold" id="footerTotalQty">0</td>
                                            <td class="text-center fw-bold" id="footerTotalPieces">0</td>
                                            <td></td>
                                            <td class="text-end fw-bold" id="footerTotalDisc">0.00</td>
                                            <td class="text-end fw-bold text-dark" id="footerTotalAmount">0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT SIDEBAR: Summary & Refund Settlement -->
                    <div class="col-12 col-lg-4 col-xl-3 mt-2 mt-lg-0">
                        <div class="d-flex flex-column gap-1 h-100">
                            <!-- Summary Card (Live) -->
                            <div class="card-panel bg-white" style="border-radius:4px; padding: 6px 8px !important;">
                                <div class="d-flex align-items-center justify-content-between mb-1 pb-1 border-bottom">
                                    <span class="fw-bold text-dark" style="font-size:0.8rem;"><i class="fas fa-calculator text-primary me-1"></i>Summary</span>
                                    <span class="badge bg-primary text-white px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">Live</span>
                                </div>
                                <div class="summary-row">
                                    <span class="text-muted">Total Amount</span>
                                    <span class="fw-bold" id="sumGross">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="text-muted">Line Discount</span>
                                    <span class="fw-bold text-danger" id="sumDisc">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="fw-bold text-dark">Current Net</span>
                                    <span class="summary-val-net" id="sumNet">0.00</span>
                                </div>
                                <div class="summary-row" id="rowPrevBalance">
                                    <span class="text-muted">Prev. Balance</span>
                                    <span class="fw-bold text-danger" id="sumPrevBalance">0.00</span>
                                </div>
                                <div class="summary-row" id="rowRefundPaid" style="display: none;">
                                    <span class="text-muted">Total Refund</span>
                                    <span class="fw-bold text-success" id="sumRefundPaid">0.00</span>
                                </div>
                                <div class="summary-row" id="rowAfterBalance">
                                    <span class="fw-bold text-danger" id="lblBalanceDue">Balance Due</span>
                                    <span class="fw-bold text-danger" id="sumAfterBalance">0.00</span>
                                </div>

                                <!-- REFUND METHOD RADIO (Matching Image 5) -->
                                <div class="mt-2 pt-2 border-top">
                                    <label class="form-label fw-bold text-primary mb-1 d-block" style="font-size: 0.72rem; letter-spacing:0.3px;">
                                        REFUND METHOD
                                    </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check form-check-inline mb-0 d-flex align-items-center gap-1">
                                            <input class="form-check-input" type="radio" name="refund_type" id="refundCredit" value="credit" checked style="cursor: pointer;">
                                            <label class="form-check-label badge border border-danger text-danger px-2 py-1 fw-bold" for="refundCredit" style="cursor: pointer; font-size: 0.75rem; background:#fff;">
                                                <i class="fas fa-file-invoice text-danger me-1"></i>Credit Note (Khata)
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mb-0 d-flex align-items-center gap-1">
                                            <input class="form-check-input" type="radio" name="refund_type" id="refundCash" value="cash" style="cursor: pointer;">
                                            <label class="form-check-label badge bg-success text-white px-2 py-1 fw-bold" for="refundCash" style="cursor: pointer; font-size: 0.75rem;">
                                                <i class="fas fa-money-bill-wave text-white me-1"></i>Cash Refund
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Methods Card (Matching Image 6 - visible ONLY when Cash Refund is selected) -->
                            <div class="card-panel bg-white flex-grow-1 flex-column" id="paymentMethodsSection" style="display: none !important; border-radius:4px; padding: 6px 8px !important;">
                                <div class="d-flex align-items-center justify-content-between mb-1 pb-1 border-bottom">
                                    <span class="fw-bold text-dark" style="font-size:0.8rem;">
                                        <i class="fas fa-wallet text-success me-1"></i>Payment Methods
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" id="btnAddRefundAccount" style="font-size:0.7rem;">
                                        <i class="fas fa-plus me-1"></i>Add Account
                                    </button>
                                </div>

                                <div id="refundAccountsWrapper" class="mb-1">
                                    <div class="d-flex gap-1 align-items-center mb-1 refund-acc-row">
                                        <select class="form-select form-select-sm refund-account bg-light fw-bold" name="refund_account_id[]" style="font-size:0.75rem;">
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title ?? ''), 'cash in hand') ? 'selected' : '' }}>
                                                    {{ $acc->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="number" step="0.01" class="form-control form-control-sm text-end refund-amount fw-bold" name="refund_amount[]" placeholder="0.00" style="width: 100px; font-size:0.75rem;">
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-save-complete w-100 mt-auto py-2" id="btnSaveReturn" style="font-size: 0.85rem !important;">
                                <i class="fas fa-save me-2"></i>Save & Complete (F9)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hidden Grand Totals Inputs -->
                <input type="hidden" name="total_subtotal" id="inputTotalSubtotal" value="0">
                <input type="hidden" name="total_extra_discount" id="inputTotalExtraDiscount" value="0">
                <input type="hidden" name="total_net" id="inputTotalNet" value="0">
                <input type="hidden" name="cash_refund_amount" id="inputCashRefundAmount" value="0">
            </form>
        </div>
    </div>

    <!-- Quick Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">
                        <i class="fas fa-user-plus text-primary me-2"></i>New Customer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ajaxAddCustomerForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Customer Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="customer_type" required>
                                    <option value="Main Customer">Main Customer</option>
                                    <option value="Walking Customer">Walking Customer</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" required placeholder="Customer Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mobile</label>
                                <input type="text" class="form-control" name="mobile" placeholder="0300-1234567">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Opening Balance</label>
                                <input type="number" step="0.01" class="form-control" name="opening_balance" value="0">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Address</label>
                                <input type="text" class="form-control" name="address" placeholder="Address">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSaveAjaxCustomer">Save Customer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            const branchWarehouseMap = @json($branchWarehouseMap ?? []);
            const allBranches = @json($branches ?? []);
            const allAccounts = @json($accounts ?? []);

            function toNum(val) {
                const n = parseFloat(val);
                return isNaN(n) ? 0 : n;
            }

            // Customer Select2
            $('#customerSelect').select2({
                placeholder: 'Select or search customer...',
                allowClear: true,
                width: '100%'
            }).on('change', function() {
                const $opt = $(this).find(':selected');
                const prevBal = toNum($opt.data('prev-balance'));
                const cId = $(this).val();
                const cName = $opt.data('name') || $opt.text().trim();

                if (cId) {
                    $('#customer_id').val(cId);
                    $('#customer').val(cName);
                    $('#previousBalance').val(prevBal);
                    $('#badgeCustomerPrevBal').text('Rs ' + prevBal.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                    $('#sumPrevBalance').text(prevBal.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                } else {
                    $('#customer_id').val('');
                    $('#customer').val('Walk-in Customer');
                    $('#previousBalance').val(0);
                    $('#badgeCustomerPrevBal').text('Rs 0.00');
                    $('#sumPrevBalance').text('0.00');
                }
                updateTotals();
            });

            // Walk-in Toggle
            $('#btnWalkinToggle').on('click', function() {
                const isChecked = $('#walkinToggle').is(':checked');
                if (!isChecked) {
                    $('#walkinToggle').prop('checked', true);
                    $(this).removeClass('btn-outline-primary').addClass('btn-primary text-white');
                    $('#customerSelectBoxContainer').addClass('d-none');
                    $('#walkinNameInput').removeClass('d-none').focus();
                    $('#customer_id').val('');
                    $('#customer').val($('#walkinNameInput').val() || 'Walk-in Customer');
                    $('#previousBalance').val(0);
                    $('#badgeCustomerPrevBal').text('Rs 0.00');
                    $('#sumPrevBalance').text('0.00');
                } else {
                    $('#walkinToggle').prop('checked', false);
                    $(this).removeClass('btn-primary text-white').addClass('btn-outline-primary');
                    $('#customerSelectBoxContainer').removeClass('d-none');
                    $('#walkinNameInput').addClass('d-none');
                    $('#customerSelect').trigger('change');
                }
                updateTotals();
            });

            $('#walkinNameInput').on('input', function() {
                $('#customer').val($(this).val() || 'Walk-in Customer');
            });

            // Build Warehouse Options HTML
            function buildWarehouseOptionsHtml(selectedVal) {
                const branchId = $('#branch_id').val();
                let html = '';
                const branchObj = allBranches.find(b => b.id == branchId);
                if (branchObj) {
                    html += '<optgroup label="Shops / Branches">';
                    const isShopSel = (selectedVal === 'branch_' + branchObj.id) ? 'selected' : '';
                    html += `<option value="branch_${branchObj.id}" ${isShopSel}>${branchObj.name} (Shop)</option>`;
                    html += '</optgroup>';
                }

                const whList = branchWarehouseMap[branchId] || [];
                if (whList.length > 0) {
                    html += '<optgroup label="Warehouses">';
                    whList.forEach(w => {
                        const val = 'warehouse_' + w.id;
                        const isSel = (selectedVal === val) ? 'selected' : '';
                        html += `<option value="${val}" ${isSel}>${w.warehouse_name || w.name}</option>`;
                    });
                    html += '</optgroup>';
                }
                return html;
            }

            // Init Product Select2 on Row (Exact same mapping as New Sale screen)
            function initProductSelect2($select) {
                $select.select2({
                    placeholder: 'Select Product / Model...',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        transport: function(params, success, failure) {
                            let term = (params.data && (params.data.term || params.data.q)) || '';
                            let page = (params.data && (params.data.page || 1)) || 1;
                            let branchId = $('#branch_id').val() || $('[name="branch_id"]').val() || '';
                            let ajaxUrl = term && term.length > 0 ? '/search_products' : '/search-products-sale';
                            $.ajax({
                                url: ajaxUrl,
                                data: { q: term, page: page, branch_id: branchId },
                                dataType: 'json',
                                success: function(data) { success(data); },
                                error: failure
                            });
                        },
                        delay: 250,
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            let results = [];
                            const mapProduct = function(p) {
                                let stockVal = (p.stock !== undefined && p.stock !== null) ? p.stock : (p.overall_stock || 0);
                                let selectId = p.select2_id || (p.variant_id ? (p.product_id + '_var_' + p.variant_id) : p.id);
                                let codeStr = p.base_item_code || p.item_code;
                                let nameStr = p.display_title || p.item_name || p.name || '';
                                let displayText = p.text || ((codeStr ? codeStr + ' — ' : '') + nameStr + ' [Stock: ' + stockVal + ']');
                                return {
                                    id: selectId,
                                    text: displayText,
                                    product: p
                                };
                            };
                            if (Array.isArray(data)) {
                                results = data.map(mapProduct);
                                return { results: results, pagination: { more: false } };
                            }
                            results = (data.products || []).map(mapProduct);
                            return { results: results, pagination: { more: !!data.has_more } };
                        },
                        cache: false
                    }
                });
            }

            // Product Selection Change
            $(document).on('change', '.product-select', function() {
                const $row = $(this).closest('tr');
                const rawVal = $(this).val();
                if (!rawVal) return;

                const data = $(this).select2('data')[0];
                const prod = data ? data.product : null;

                if (prod) {
                    const actualProductId = prod.product_id || (typeof rawVal === 'string' && rawVal.includes('_var_') ? rawVal.split('_var_')[0] : rawVal);
                    $row.find('.product-id-hidden').val(actualProductId);
                    $row.find('.product-name-hidden').val(prod.display_title || prod.item_name || prod.name || '');
                    $row.find('.item-code-hidden').val(prod.base_item_code || prod.item_code || '');
                    $row.find('.brand-hidden').val(prod.brand_name || '');
                    $row.find('.unit-hidden').val(prod.unit_name || prod.unit || 'pcs');

                    let ppp = parseFloat(prod.piece_per_pack) || 1;
                    $row.data('piece-per-pack', ppp);

                    let rate = parseFloat(prod.retail_price || prod.price || 0);
                    $row.find('.return-price').val(rate.toFixed(2));

                    fetchRowStock($row, actualProductId);
                    recalcRow($row);
                }
            });

            // Fetch live row stock
            function fetchRowStock($row, prodId) {
                const branchId = $('#branch_id').val();
                const locVal = $row.find('.row-warehouse').val() || $('#globalWarehouseSelect').val();
                let whId = null;
                if (locVal && locVal.startsWith('warehouse_')) {
                    whId = locVal.replace('warehouse_', '');
                }

                $.ajax({
                    url: '{{ route("sale.check.stock") }}',
                    data: { product_id: prodId, branch_id: branchId, warehouse_id: whId },
                    success: function(res) {
                        const stock = (res && typeof res.stock !== 'undefined') ? res.stock : 0;
                        $row.find('.row-stock').val(stock);
                    },
                    error: function() {
                        $row.find('.row-stock').val(0);
                    }
                });
            }

            // Add New Row
            function addNewRow() {
                const rowCount = $('#returnTableBody tr').length + 1;
                const currentLoc = $('#globalWarehouseSelect').val() || '';

                const rowHtml = `
                    <tr>
                        <td class="text-center fw-bold text-muted row-index col-idx" style="vertical-align:middle; font-size:0.75rem;">${rowCount}</td>
                        <td class="col-product">
                            <select class="form-select product-select" name="product_id[]" style="width:100%">
                                <option value=""></option>
                            </select>
                            <input type="hidden" class="product-id-hidden" name="product_id_hidden[]">
                            <input type="hidden" class="product-name-hidden" name="product[]">
                            <input type="hidden" class="item-code-hidden" name="item_code[]">
                            <input type="hidden" class="brand-hidden" name="brand[]">
                            <input type="hidden" class="unit-hidden" name="unit[]" value="pcs">
                        </td>
                        <td class="col-warehouse">
                            <select class="form-select row-warehouse" name="row_warehouse[]" style="width: 100%; height: 26px; font-size: 0.78rem; padding: 2px 4px; font-weight: 600;">
                                ${buildWarehouseOptionsHtml(currentLoc)}
                            </select>
                        </td>
                        <td class="col-stock">
                            <input type="text" class="form-control row-stock text-center input-readonly" readonly tabindex="-1" value="0" title="Stock">
                        </td>
                        <td class="col-qty">
                            <input type="number" step="any" class="form-control return-qty text-center fw-bold" name="qty[]" placeholder="0" min="0" value="1" style="height: 26px; font-size: 0.85rem;">
                        </td>
                        <td class="col-pieces">
                            <input type="text" class="form-control total-pieces text-center input-readonly" readonly placeholder="0" tabindex="-1" value="1">
                        </td>
                        <td class="col-price-p">
                            <input type="number" step="any" class="form-control return-price text-end fw-bold" name="price[]" placeholder="0.00" min="0" value="0.00" style="height: 26px;">
                        </td>
                        <td class="col-disc">
                            <input type="number" step="any" class="form-control item-disc text-end" name="item_disc[]" placeholder="0.00" min="0" value="0.00" style="height: 26px;">
                        </td>
                        <td class="col-amount">
                            <input type="text" class="form-control row-total text-end input-readonly text-dark fw-bold" name="total[]" value="0.00" readonly tabindex="-1">
                        </td>
                        <td class="col-action text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger del-row py-0 px-1" tabindex="-1">&times;</button>
                        </td>
                    </tr>
                `;

                $('#returnTableBody').append(rowHtml);
                const $addedRow = $('#returnTableBody tr').last();
                initProductSelect2($addedRow.find('.product-select'));
                updateRowIndexNumbers();
                recalcRow($addedRow);
            }

            function updateRowIndexNumbers() {
                $('#returnTableBody tr').each(function(idx) {
                    $(this).find('.row-index').text(idx + 1);
                });
                $('#itemsRowCount').text($('#returnTableBody tr').length);
            }

            function recalcRow($row) {
                const qty = toNum($row.find('.return-qty').val());
                const price = toNum($row.find('.return-price').val());
                const disc = toNum($row.find('.item-disc').val());
                const ppp = toNum($row.data('piece-per-pack')) || 1;

                const pcs = qty * ppp;
                $row.find('.total-pieces').val(pcs);

                const total = Math.max(0, (qty * price) - disc);
                $row.find('.row-total').val(total.toFixed(2));

                updateTotals();
            }

            function updateTotals() {
                let grandQty = 0;
                let grandPieces = 0;
                let grandDisc = 0;
                let grandGross = 0;

                $('#returnTableBody tr').each(function() {
                    const q = toNum($(this).find('.return-qty').val());
                    const pr = toNum($(this).find('.return-price').val());
                    const d = toNum($(this).find('.item-disc').val());
                    const pcs = toNum($(this).find('.total-pieces').val());

                    grandQty += q;
                    grandPieces += pcs;
                    grandDisc += d;
                    grandGross += (q * pr);
                });

                const grandNet = Math.max(0, grandGross - grandDisc);

                $('#footerTotalQty').text(grandQty);
                $('#footerTotalPieces').text(grandPieces);
                $('#footerTotalDisc').text(grandDisc.toFixed(2));
                $('#footerTotalAmount').text(grandNet.toFixed(2));

                $('#sumGross').text(grandGross.toFixed(2));
                $('#sumDisc').text(grandDisc.toFixed(2));
                $('#sumNet').text(grandNet.toFixed(2));

                $('#inputTotalSubtotal').val(grandGross.toFixed(2));
                $('#inputTotalExtraDiscount').val(grandDisc.toFixed(2));
                $('#inputTotalNet').val(grandNet.toFixed(2));

                // If cash refund is active, calculate cash refund total
                let totalRefundPaid = 0;
                if ($('#refundCash').is(':checked')) {
                    const $rows = $('.refund-acc-row');
                    // If first row amount was empty or 0, default to grandNet
                    const $firstRow = $rows.first().find('.refund-amount');
                    if ($firstRow.length && toNum($firstRow.val()) === 0 && grandNet > 0) {
                        $firstRow.val(grandNet.toFixed(2));
                    }
                    totalRefundPaid = calcCashRefund();
                } else {
                    $('#inputCashRefundAmount').val('0.00');
                    $('#sumRefundPaid').text('0.00');
                }

                // Balance Due Logic:
                // Customer's old balance = prevBal
                // Goods returned to us = grandNet (reduces what customer owes)
                // Cash refunded to customer = totalRefundPaid (increases what customer owes / cancels return reduction)
                // Net Remaining Balance = prevBal - (grandNet - totalRefundPaid)
                const prevBal = toNum($('#previousBalance').val());
                const netBalance = prevBal - (grandNet - totalRefundPaid);

                if (netBalance >= 0) {
                    $('#lblBalanceDue').text('Balance Due').removeClass('text-success').addClass('text-danger');
                    $('#sumAfterBalance').text(netBalance.toFixed(2)).removeClass('text-success').addClass('text-danger');
                } else {
                    // When netBalance < 0, customer has a credit advance with shop
                    $('#lblBalanceDue').text('Customer Credit').removeClass('text-danger').addClass('text-success');
                    $('#sumAfterBalance').text(Math.abs(netBalance).toFixed(2)).removeClass('text-danger').addClass('text-success');
                }
            }

            function calcCashRefund() {
                let totalCash = 0;
                if ($('#refundCash').is(':checked')) {
                    $('.refund-acc-row').each(function() {
                        totalCash += toNum($(this).find('.refund-amount').val());
                    });
                }
                $('#inputCashRefundAmount').val(totalCash.toFixed(2));
                $('#sumRefundPaid').text(totalCash.toFixed(2));
                return totalCash;
            }

            // Table Events
            $('#btnAddRow').on('click', function() {
                addNewRow();
            });

            $(document).on('input', '.return-qty, .return-price, .item-disc', function() {
                recalcRow($(this).closest('tr'));
            });

            $(document).on('change', '.row-warehouse', function() {
                const $row = $(this).closest('tr');
                const pid = $row.find('.product-id-hidden').val();
                if (pid) fetchRowStock($row, pid);
            });

            $(document).on('click', '.del-row', function() {
                $(this).closest('tr').remove();
                updateRowIndexNumbers();
                updateTotals();
            });

            // Refund Method Toggle (ONLY show Payment Methods when Cash Refund is checked!)
            function updateRefundMethodUI() {
                if ($('#refundCash').is(':checked')) {
                    $('#paymentMethodsSection').attr('style', 'display: flex !important; border-radius:4px; padding: 6px 8px !important;').removeClass('d-none');
                    $('#rowRefundPaid').show();
                    const net = toNum($('#inputTotalNet').val());
                    const $firstRow = $('.refund-acc-row').first();
                    if ($firstRow.length && toNum($firstRow.val()) === 0) {
                        $firstRow.val(net.toFixed(2));
                    }
                } else {
                    $('#paymentMethodsSection').attr('style', 'display: none !important; border-radius:4px; padding: 6px 8px !important;').addClass('d-none');
                    $('#rowRefundPaid').hide();
                    $('.refund-acc-row .refund-amount').val('0.00');
                }
                updateTotals();
            }

            $('input[name="refund_type"]').on('change', updateRefundMethodUI);
            // Run initially so Payment Methods is completely hidden on load
            updateRefundMethodUI();

            // Add Refund Account row
            $('#btnAddRefundAccount').on('click', function() {
                let optionsHtml = '';
                allAccounts.forEach(acc => {
                    optionsHtml += `<option value="${acc.id}">${acc.title}</option>`;
                });
                const accRow = `
                    <div class="d-flex gap-1 align-items-center mb-1 refund-acc-row">
                        <select class="form-select form-select-sm refund-account bg-light fw-bold" name="refund_account_id[]" style="font-size:0.75rem;">
                            ${optionsHtml}
                        </select>
                        <input type="number" step="0.01" class="form-control form-control-sm text-end refund-amount fw-bold" name="refund_amount[]" placeholder="0.00" style="width: 100px; font-size:0.75rem;">
                        <button type="button" class="btn btn-xs btn-outline-danger del-refund-row py-0 px-1">&times;</button>
                    </div>
                `;
                $('#refundAccountsWrapper').append(accRow);
                updateTotals();
            });

            $(document).on('input', '.refund-amount', function() {
                updateTotals();
            });

            $(document).on('click', '.del-refund-row', function() {
                $(this).closest('.refund-acc-row').remove();
                updateTotals();
            });

            // Branch change
            $('#branch_id').on('change', function() {
                const newWhOptions = buildWarehouseOptionsHtml('');
                $('#globalWarehouseSelect').html(newWhOptions);
                $('.row-warehouse').each(function() {
                    $(this).html(newWhOptions);
                });
            });

            // Global Warehouse Change
            $('#globalWarehouseSelect').on('change', function() {
                const loc = $(this).val();
                $('.row-warehouse').val(loc).trigger('change');
            });

            // Save Customer AJAX
            $('#btnSaveAjaxCustomer').on('click', function() {
                const formData = $('#ajaxAddCustomerForm').serialize();
                $.ajax({
                    url: '{{ route("customers.store") }}',
                    method: 'POST',
                    data: formData,
                    success: function(res) {
                        $('#addCustomerModal').modal('hide');
                        $('#ajaxAddCustomerForm')[0].reset();
                        if (res && res.customer) {
                            const newOption = new Option(res.customer.customer_id + ' — ' + res.customer.customer_name, res.customer.id, true, true);
                            $(newOption).data('prev-balance', res.customer.opening_balance || 0);
                            $(newOption).data('name', res.customer.customer_name);
                            $('#customerSelect').append(newOption).trigger('change');
                        }
                        Swal.fire({ icon: 'success', title: 'Customer Created!', timer: 1500, showConfirmButton: false });
                    },
                    error: function(xhr) {
                        let msg = 'Failed to create customer.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    }
                });
            });

            // Submit Direct Return
            function submitDirectReturn() {
                const totalItems = toNum($('#footerTotalQty').text());
                if (totalItems <= 0) {
                    Swal.fire({ icon: 'warning', title: 'No Items', text: 'Please add at least one product with quantity to return!' });
                    return;
                }

                if ($('#refundCash').is(':checked')) {
                    const cashPaid = toNum($('#inputCashRefundAmount').val());
                    if (cashPaid <= 0) {
                        Swal.fire({ icon: 'warning', title: 'Refund Amount Missing', text: 'Please specify the refund amount in Payment Methods!' });
                        return;
                    }
                }

                Swal.fire({
                    title: 'Confirm Return?',
                    text: 'Are you sure you want to process this direct sale return?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Save Return'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#pageLoader').removeClass('d-none').css('display', 'flex !important');
                        $('#btnSaveReturn').prop('disabled', true);

                        $.ajax({
                            url: '{{ route("sales.direct_return.store") }}',
                            method: 'POST',
                            data: $('#directReturnForm').serialize(),
                            success: function(res) {
                                $('#pageLoader').addClass('d-none');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Return Saved!',
                                    text: res.message || 'Direct Sale Return successfully recorded!',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = res.redirect || '{{ route("sale.returns.index") }}';
                                });
                            },
                            error: function(xhr) {
                                $('#pageLoader').addClass('d-none');
                                $('#btnSaveReturn').prop('disabled', false);
                                let errorMsg = 'Failed to save return.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
                            }
                        });
                    }
                });
            }

            $('#btnSaveReturn').on('click', function() {
                submitDirectReturn();
            });

            // Keyboard shortcut F9
            $(document).on('keydown', function(e) {
                if (e.key === 'F9') {
                    e.preventDefault();
                    submitDirectReturn();
                }
            });

            // Add first empty row on page load
            addNewRow();
        });
    </script>
@endsection

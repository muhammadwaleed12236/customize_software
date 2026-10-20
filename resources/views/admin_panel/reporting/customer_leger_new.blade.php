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
                    <button onclick="printCustomerReferenceLedger()" class="btn btn-sm btn-outline-light font-weight-bold">
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
                            <div class="col-md-2 col-lg-2">
                                <label class="f-label">Select Branch</label>
                                <select id="branch_id" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;" onchange="loadCustomersForBranch(this.value)">
                                    <option value="">-- Select Branch --</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name ?? $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-3">
                                <label class="f-label">
                                    Customer <small id="customerCountHint" class="text-primary ml-1"></small>
                                </label>
                                <select id="customer_id" class="form-control form-control-sm select2" style="width: 100%;">
                                    <option value="">-- Select Branch First --</option>
                                </select>
                            </div>
                        @else
                            <div class="col-md-4 col-lg-4">
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

                        <div class="col-md-2 col-lg-2">
                            <label class="f-label">From Date</label>
                            <input type="date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2 col-lg-2">
                            <label class="f-label">To Date</label>
                            <input type="date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="{{ ($user && $user->hasRole('super admin')) ? 'col-md-3 col-lg-3' : 'col-md-4 col-lg-4' }}">
                            <div class="d-flex gap-2">
                                <button type="button" id="btnGeneralLedger"
                                    onclick="loadLedger('general')"
                                    class="btn btn-sm btn-primary font-weight-bold flex-fill"
                                    style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy); font-size: 11.5px; white-space: nowrap;"
                                    title="General Ledger: Full Detailed View with item breakdown">
                                    <i class="fas fa-list-alt mr-1"></i> General Ledger
                                </button>
                                <button type="button" id="btnSimpleLedger"
                                    onclick="loadLedger('simple')"
                                    class="btn btn-sm font-weight-bold flex-fill"
                                    style="height: 38px; border-radius: 6px; background: #ffffff; border: 1.5px solid #16a34a; color: #16a34a; font-size: 11.5px; white-space: nowrap;"
                                    title="Simple Ledger: Compact View (Date, Inv/Voucher, Account, Debit, Credit, Balance)">
                                    <i class="fas fa-table mr-1"></i> Simple Ledger
                                </button>
                            </div>
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

                {{-- 2.5 Summary KPI Cards --}}
                <div class="row g-2 mb-3" id="kpiSummaryCards">
                    <div class="col-md-2 col-sm-6">
                        <div class="card border-0 shadow-sm p-3" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #1e3a5f !important; border: 1px solid var(--coa-border);">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.04em;">Closing Balance (بقیہ)</div>
                                    <h5 class="font-weight-bold mb-0 text-dark mt-1" id="kpiClosingBalance">0.00</h5>
                                </div>
                                <div class="rounded-circle p-2" style="background: rgba(30, 58, 95, 0.1); color: #1e3a5f; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-wallet fs-6"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="card border-0 shadow-sm p-3" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #dc2626 !important; border: 1px solid var(--coa-border);">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.04em;">Total Debit (فروخت)</div>
                                    <h5 class="font-weight-bold mb-0 text-danger mt-1" id="kpiTotalDebit">Rs. 0</h5>
                                </div>
                                <div class="rounded-circle p-2" style="background: rgba(220, 38, 38, 0.1); color: #dc2626; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-invoice-dollar fs-6"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card border-0 shadow-sm p-3" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #16a34a !important; border: 1px solid var(--coa-border);">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.04em;">Total Credit (وصولی)</div>
                                    <h5 class="font-weight-bold mb-0 text-success mt-1" id="kpiTotalCredit">Rs. 0</h5>
                                </div>
                                <div class="rounded-circle p-2" style="background: rgba(22, 163, 74, 0.1); color: #16a34a; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-hand-holding-usd fs-6"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="card border-0 shadow-sm p-3" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #0d9f6e !important; border: 1px solid var(--coa-border);">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.04em;">Delivered Qty (دیے گئے)</div>
                                    <h5 class="font-weight-bold mb-0 mt-1" style="color: #0d9f6e;" id="kpiTotalDeliveredQty">0.00</h5>
                                </div>
                                <div class="rounded-circle p-2" style="background: rgba(13, 159, 110, 0.15); color: #0d9f6e; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-truck-loading fs-6"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card border-0 shadow-sm p-3" style="border-radius: 9px; background: #ffffff; border-left: 4px solid #d97706 !important; border: 1px solid var(--coa-border);">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.04em;">Remaining Stock Qty (بقیہ مال)</div>
                                    <h5 class="font-weight-bold mb-0 mt-1" style="color: #d97706;" id="kpiTotalRemainingQty">0.00</h5>
                                </div>
                                <div class="rounded-circle p-2" style="background: rgba(217, 119, 6, 0.15); color: #d97706; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-boxes fs-6"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                            <div class="col-md-3">
                                <span class="f-label d-inline mr-1">Period: </span>
                                <span id="cust_period" class="font-weight-bold" style="color: var(--coa-navy);">-</span>
                            </div>
                            <div class="col-md-9 text-right font-monospace" style="font-size: 12.5px;">
                                <span class="mr-2"><span class="text-muted">Opening: </span><span id="s_open" class="font-weight-bold text-dark">-</span></span>
                                <span class="mr-2"><span class="text-muted">Debit: </span><span id="s_debit" class="font-weight-bold text-danger">-</span></span>
                                <span class="mr-2"><span class="text-muted">Credit: </span><span id="s_credit" class="font-weight-bold text-success">-</span></span>
                                <span class="mr-2"><span class="text-muted">Delivered: </span><span id="s_delivered" class="font-weight-bold text-info">0</span></span>
                                <span class="mr-2"><span class="text-muted">Remaining Stock: </span><span id="s_remaining" class="font-weight-bold text-dark" style="font-size: 14px;">0</span></span>
                                <span><span class="text-muted">Closing: </span><span id="s_close" class="font-weight-bold text-primary" style="font-size: 15px;">-</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3.5 Active View Switcher Bar --}}
                <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                    <div class="btn-group shadow-sm" role="group" aria-label="Ledger Mode">
                        <button type="button" class="btn btn-sm font-weight-bold" id="modeBtnGeneral" onclick="switchLedgerView('general')" style="background: var(--coa-navy); color: #fff; border: 1.5px solid var(--coa-navy); padding: 5px 14px;">
                            <i class="fas fa-list-alt mr-1"></i> General Ledger (Detailed)
                        </button>
                        <button type="button" class="btn btn-sm font-weight-bold" id="modeBtnSimple" onclick="switchLedgerView('simple')" style="background: #ffffff; color: #16a34a; border: 1.5px solid #16a34a; padding: 5px 14px;">
                            <i class="fas fa-table mr-1"></i> Simple Ledger (Compact)
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge px-3 py-2 text-white shadow-sm font-weight-bold" id="currentViewBadge" style="background: var(--coa-navy); font-size: 11.5px; border-radius: 6px;">
                            <i class="fas fa-eye mr-1"></i> Viewing: General Ledger (12 Columns)
                        </span>
                    </div>
                </div>

                {{-- 4. LEDGER TABLE --}}
                <div id="printArea">
                    <div class="table-responsive" style="border: 1px solid var(--coa-border); border-radius: 9px; overflow: hidden;">
                        <table id="ledgerTable" class="table table-bordered mb-0" style="font-size: 12.5px; border-collapse: collapse;">
                            <thead id="ledgerHead">
                                <tr style="background: #0f1f38; color: #ffffff;">
                                    <th class="text-center" style="width: 45px;">No</th>
                                    <th class="text-center" style="width: 85px;">Inv No.</th>
                                    <th class="text-center" style="width: 45px;">Type</th>
                                    <th class="text-center" style="width: 70px;">Date</th>
                                    <th class="text-center" style="width: 70px;">Ref</th>
                                    <th>Details (تفصیل)</th>
                                    <th class="text-right" style="width: 70px; background: #1e3a5f !important;" title="Rate per watt (ریٹ)">Rate (ریٹ)</th>
                                    <th class="text-right" style="width: 85px; background: #0f2b48 !important;" title="Unit Price per panel (قیمت)">Price (قیمت)</th>
                                    <th class="text-right" style="width: 65px; background: #064e3b !important;" title="Delivered Quantity (دیے کتنے)">Qty (دیے)</th>
                                    <th class="text-right" style="width: 90px;">Debit (ڈیبٹ)</th>
                                    <th class="text-right" style="width: 90px;">Credit (وصولی)</th>
                                    <th class="text-right" style="width: 100px;">Balance (بقیہ)</th>
                                </tr>
                            </thead>
                            <tbody id="ledgerBody"></tbody>
                            <tfoot id="ledgerFooter"></tfoot>
                        </table>
                    </div>
                </div>

            </div>{{-- /ledgerBox --}}

            {{-- 4.5 Dedicated Reference Print Container (Matches Reference Ledger Exactly) --}}
            <div id="referencePrintContainer"></div>

        </div>
    </div>
</div>

{{-- 5. TRANSACTION DETAIL MODAL --}}
<div class="modal fade" id="transactionModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 92%; margin: 1.5rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header py-2 px-3 text-white" style="background: #0f1f38; flex-shrink: 0; border-bottom: 2px solid #1e3a5f;">
                <div class="d-flex align-items-center">
                    <i id="txModalIcon" class="fas fa-file-invoice mr-2 text-warning fs-5"></i>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0 text-white" id="txModalTitle" style="font-size: 15px;">
                            Transaction Details
                        </h5>
                        <small class="text-white-50" id="txModalSubtitle">Customer Ledger Document Viewer</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light font-weight-bold" onclick="printTxModal()" title="Print this document">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                    <a id="txModalNewTab" href="#" target="_blank" class="btn btn-sm btn-light font-weight-bold text-dark" title="Open in new browser tab">
                        <i class="fas fa-external-link-alt mr-1 text-primary"></i> Full Page
                    </a>
                    <button type="button" class="close text-white opacity-100 ml-2" data-dismiss="modal" aria-label="Close" style="font-size: 26px; line-height: 1; outline: none; background: transparent; border: none; cursor: pointer;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body p-0" style="background: #ffffff; flex: 1 1 auto; position: relative; overflow: hidden; min-height: 520px; height: calc(90vh - 65px);">
                <div id="txModalSpinner" style="position: absolute; top:0; left:0; width:100%; height:100%; background: #ffffff; z-index: 20; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <div class="font-weight-bold text-dark" id="txModalSpinnerText" style="font-size: 14px;">Loading document details&hellip;</div>
                    <small class="text-muted mt-1">Please wait while the official voucher / invoice loads</small>
                    <div id="txModalTimeoutMsg" class="mt-3 text-center" style="display: none;">
                        <p class="text-muted small mb-2">If loading takes longer than usual, click below:</p>
                        <a id="txModalDirectLink" href="#" target="_blank" class="btn btn-sm btn-primary">
                            <i class="fas fa-external-link-alt mr-1"></i> Open Full Page
                        </a>
                    </div>
                </div>
                <iframe id="txModalIframe" src="" style="width: 100%; height: 100%; border: none; display: block;" onload="onTxIframeLoaded()"></iframe>
            </div>
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
tr.r-sale    td { background:#ffffff !important; color:#1e293b !important; font-weight:700; border-color:#e2e8f0 !important; }
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

/* Reference Print Container Styles */
#referencePrintContainer {
    display: none;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 6mm 8mm 8mm 8mm;
    }

    /* Force background colors and borders to print properly */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Completely hide everything else on the page */
    .header-navbar, .sidebar, .sidebar-wrapper, .app-header, .main-header,
    .footer, .card, form, button, .btn, .btn-group, .modal, .modal-backdrop,
    .rpt-header-bar, #kpiSummaryCards, .nav, .navbar, .topbar, #ledgerBox, #loader {
        display: none !important;
        visibility: hidden !important;
    }

    body * {
        visibility: hidden !important;
    }

    #referencePrintContainer,
    #referencePrintContainer * {
        visibility: visible !important;
    }

    #referencePrintContainer {
        display: block !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        color: #000000 !important;
        box-sizing: border-box !important;
    }

    #referencePrintContainer table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    #referencePrintContainer thead {
        display: table-header-group !important;
    }

    #referencePrintContainer tfoot {
        display: table-footer-group !important;
    }

    #referencePrintContainer tr {
        page-break-inside: avoid !important;
    }
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
     * STATE MANAGEMENT: Active Ledger Mode & Cache
     * ═══════════════════════════════════════════════════ */
    var currentLedgerMode = 'general'; // 'general' or 'simple'
    var lastLedgerResponse = null;
    var lastStartDate = '';
    var lastEndDate = '';

    function loadLedger(mode) {
        if (mode) {
            currentLedgerMode = mode;
            updateModeUI();
        }
        var cid   = document.getElementById('customer_id').value;
        var start = document.getElementById('start_date').value;
        var end   = document.getElementById('end_date').value;

        if (lastLedgerResponse && lastLedgerResponse.customer && lastLedgerResponse.customer.id == cid && lastStartDate === start && lastEndDate === end) {
            renderLedger(lastLedgerResponse, start, end);
            return;
        }
        generateLedger();
    }

    function switchLedgerView(mode) {
        if (currentLedgerMode === mode) return;
        currentLedgerMode = mode;
        updateModeUI();
        if (lastLedgerResponse) {
            renderLedger(lastLedgerResponse, lastStartDate, lastEndDate);
        }
    }

    function updateModeUI() {
        if (currentLedgerMode === 'general') {
            $('#btnGeneralLedger').css({ 'background': 'var(--coa-navy)', 'color': '#ffffff', 'border-color': 'var(--coa-navy)' });
            $('#btnSimpleLedger').css({ 'background': '#ffffff', 'color': '#16a34a', 'border': '1.5px solid #16a34a' });
            $('#modeBtnGeneral').css({ 'background': 'var(--coa-navy)', 'color': '#ffffff', 'border-color': 'var(--coa-navy)' });
            $('#modeBtnSimple').css({ 'background': '#ffffff', 'color': '#16a34a', 'border': '1.5px solid #16a34a' });
            $('#currentViewBadge').html('<i class="fas fa-eye mr-1"></i> Viewing: General Ledger (Detailed &mdash; 12 Columns)').css('background', 'var(--coa-navy)');
        } else {
            $('#btnSimpleLedger').css({ 'background': '#16a34a', 'color': '#ffffff', 'border-color': '#16a34a' });
            $('#btnGeneralLedger').css({ 'background': '#ffffff', 'color': 'var(--coa-navy)', 'border': '1.5px solid var(--coa-navy)' });
            $('#modeBtnSimple').css({ 'background': '#16a34a', 'color': '#ffffff', 'border-color': '#16a34a' });
            $('#modeBtnGeneral').css({ 'background': '#ffffff', 'color': 'var(--coa-navy)', 'border': '1.5px solid var(--coa-navy)' });
            $('#currentViewBadge').html('<i class="fas fa-table mr-1"></i> Viewing: Simple Ledger (Compact &mdash; 9 Columns)').css('background', '#16a34a');
        }
    }

    /* ═══════════════════════════════════════════════════
     * TRANSACTION DETAIL MODAL
     * ═══════════════════════════════════════════════════ */
    var txLoadTimer = null;

    function openTransactionModal(type, id, vno) {
        var title = 'Document Details';
        var iconClass = 'fas fa-file-invoice';
        var subtitle = 'Document Reference: ' + (vno || ('#' + id));

        // Use root-relative URL so it always uses the exact same protocol, domain, and port as current browser window
        var previewBase = "{{ route('report.customer.ledger.preview', [], false) }}";
        var previewUrl = previewBase + "?type=" + encodeURIComponent(type);
        if (id) previewUrl += '&id=' + encodeURIComponent(id);
        if (vno) previewUrl += '&vno=' + encodeURIComponent(vno);

        if (type === 'sale') {
            title = 'Sale Invoice: ' + (vno || ('#' + id));
            iconClass = 'fas fa-file-invoice-dollar';
            subtitle = 'Official Customer Sale Invoice & Item Breakdown';
        } else if (type === 'receipt') {
            title = 'Receipt Voucher: ' + (vno || ('#' + id));
            iconClass = 'fas fa-hand-holding-usd';
            subtitle = 'Bank / Cash Receipt Voucher Details';
        } else if (type === 'payment' || type === 'payment_voucher') {
            title = 'Payment Voucher: ' + (vno || ('#' + id));
            iconClass = 'fas fa-money-check-alt';
            subtitle = 'Payment Voucher / Discount Details';
        } else if (type === 'return') {
            title = 'Sale Return: ' + (vno || ('#' + id));
            iconClass = 'fas fa-undo-alt';
            subtitle = 'Sale Return & Adjustment Details';
        } else if (type === 'journal' || type === 'journal_voucher' || type === 'journal_debit' || type === 'journal_credit') {
            title = 'Journal Voucher: ' + (vno || ('#' + id));
            iconClass = 'fas fa-book';
            subtitle = 'Journal Adjustment Voucher Details';
        }

        var fullPageUrl = previewUrl;
        if (type === 'sale' && id) {
            fullPageUrl = "{{ url('sale/invoice') }}/" + encodeURIComponent(id);
        }

        $('#txModalTitle').text(title);
        $('#txModalSubtitle').text(subtitle);
        $('#txModalIcon').attr('class', iconClass + ' mr-2 text-warning fs-5');
        $('#txModalNewTab').attr('href', fullPageUrl);
        $('#txModalDirectLink').attr('href', fullPageUrl);

        $('#txModalTimeoutMsg').hide();
        $('#txModalSpinner').show();

        // Load into iframe
        var iframe = document.getElementById('txModalIframe');
        if (iframe) {
            iframe.src = previewUrl;
        }

        $('#transactionModal').modal('show');

        // Safety fallback timer: auto-reveal if browser delays onload event
        if (txLoadTimer) clearTimeout(txLoadTimer);
        txLoadTimer = setTimeout(function() {
            $('#txModalTimeoutMsg').show();
            // Automatically hide spinner after 1.5s more in case onload was consumed
            setTimeout(function() {
                $('#txModalSpinner').fadeOut(200);
            }, 1200);
        }, 1800);
    }

    function onTxIframeLoaded() {
        var iframe = document.getElementById('txModalIframe');
        if (iframe && iframe.getAttribute('src') && iframe.getAttribute('src') !== '') {
            if (txLoadTimer) clearTimeout(txLoadTimer);
            $('#txModalSpinner').fadeOut(150);
        }
    }

    function printTxModal() {
        var iframe = document.getElementById('txModalIframe');
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }
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
                    lastLedgerResponse = res;
                    lastStartDate = start;
                    lastEndDate = end;
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

    function getWattVal(str) {
        if (!str) return 0;
        var m = String(str).match(/(\d+)\s*(?:W|WATT|WATTS)\b/i);
        return m ? parseFloat(m[1]) : 0;
    }

    function td(txt, align, attrs) {
        align = align || 'center';
        attrs = attrs || '';
        var val = (txt !== null && txt !== undefined && txt !== '') ? txt : dash();
        return '<td style="text-align:' + align + ';border:1px solid #ddd;" ' + attrs + '>' + val + '</td>';
    }

    function makeClickableLink(type, id, vno, displayLabel, color) {
        if (!vno && !id) return dash();
        var label = displayLabel || vno || ('#' + id);
        var safeType = "'" + (type || '') + "'";
        var safeId = id ? id : 'null';
        var safeVno = vno ? "'" + String(vno).replace(/'/g, "\\'") + "'" : "''";
        var safeColor = color || '#0f172a';

        return '<a href="javascript:void(0)" onclick="openTransactionModal(' + safeType + ', ' + safeId + ', ' + safeVno + ')" '
            + 'class="font-weight-bold" style="color:' + safeColor + '; text-decoration:underline; cursor:pointer;" '
            + 'title="Click to view details in modal">'
            + label + ' <i class="fas fa-external-link-alt ml-1" style="font-size:9px; opacity:0.65;"></i></a>';
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

        /* top KPI cards */
        $('#kpiClosingBalance').html(balHtml(res.closing_balance));
        $('#kpiTotalDebit').text('Rs. ' + fmt(res.total_debit));
        $('#kpiTotalCredit').text('Rs. ' + fmt(res.total_credit));

        var ob          = n(res.opening_balance);
        var txns        = res.transactions || [];
        var grandDr     = 0;
        var grandCr     = 0;
        var grandQty    = 0;   /* total qty across all items */
        var rowNum      = 1;

        var isSimple = (currentLedgerMode === 'simple');
        updateModeUI();

        /* Render dynamic Table Header */
        var headHtml = '';
        if (isSimple) {
            headHtml = '<tr style="background: #0f1f38; color: #ffffff;">' +
                '<th class="text-center" style="width: 45px;">No</th>' +
                '<th class="text-center" style="width: 85px;">Date (تاریخ)</th>' +
                '<th class="text-center" style="width: 130px;">Inv / Vouch No.</th>' +
                '<th class="text-center" style="width: 55px;">Type</th>' +
                '<th>Account / Description (اکاؤنٹ / تفصیل)</th>' +
                '<th class="text-center" style="width: 95px;">Ref / Chq (حوالہ)</th>' +
                '<th class="text-right" style="width: 105px;">Debit (ڈیبٹ)</th>' +
                '<th class="text-right" style="width: 105px;">Credit (وصولی)</th>' +
                '<th class="text-right" style="width: 115px;">Balance (بقیہ)</th>' +
            '</tr>';
        } else {
            headHtml = '<tr style="background: #0f1f38; color: #ffffff;">' +
                '<th class="text-center" style="width: 45px;">No</th>' +
                '<th class="text-center" style="width: 85px;">Inv No.</th>' +
                '<th class="text-center" style="width: 45px;">Type</th>' +
                '<th class="text-center" style="width: 70px;">Date</th>' +
                '<th class="text-center" style="width: 70px;">Ref</th>' +
                '<th>Details (تفصیل)</th>' +
                '<th class="text-right" style="width: 70px; background: #1e3a5f !important;" title="Rate per watt (ریٹ)">Rate (ریٹ)</th>' +
                '<th class="text-right" style="width: 85px; background: #0f2b48 !important;" title="Unit Price per panel (قیمت)">Price (قیمت)</th>' +
                '<th class="text-right" style="width: 65px; background: #064e3b !important;" title="Delivered Quantity (دیے کتنے)">Qty (دیے)</th>' +
                '<th class="text-right" style="width: 90px;">Debit (ڈیبٹ)</th>' +
                '<th class="text-right" style="width: 90px;">Credit (وصولی)</th>' +
                '<th class="text-right" style="width: 100px;">Balance (بقیہ)</th>' +
            '</tr>';
        }
        $('#ledgerHead').html(headHtml);

        var bodyHtml = '';

        /* Opening Balance Row */
        if (isSimple) {
            bodyHtml += '<tr class="r-open">';
            bodyHtml += td('', 'center');
            bodyHtml += td(start || '', 'center');
            bodyHtml += td('-', 'center');
            bodyHtml += td('<span class="badge badge-secondary" style="font-size:9.5px;padding:2px 5px;">OB</span>', 'center');
            bodyHtml += td('<strong>Opening Balance (ابتدائی بقایا)</strong>', 'left');
            bodyHtml += td('-', 'center');
            bodyHtml += td(ob > 0 ? fmt(ob) : '', 'right');
            bodyHtml += td(ob < 0 ? fmt(Math.abs(ob)) : '', 'right');
            bodyHtml += td(balHtml(ob), 'right');
            bodyHtml += '</tr>';
        } else {
            bodyHtml += '<tr class="r-open">';
            bodyHtml += td('', 'center'); // 1. No
            bodyHtml += td('', 'center'); // 2. Inv No.
            bodyHtml += td('', 'center'); // 3. Type
            bodyHtml += td('', 'center'); // 4. Date
            bodyHtml += td('', 'center'); // 5. Ref
            bodyHtml += td('<strong>Opening Balance</strong>', 'left'); // 6. Details
            bodyHtml += td('', 'right');  // 7. Rate
            bodyHtml += td('', 'right');  // 8. Price
            bodyHtml += td('', 'right');  // 9. Qty
            bodyHtml += td('', 'right');  // 10. Debit
            bodyHtml += td('', 'right');  // 11. Credit
            bodyHtml += td(balHtml(ob), 'right'); // 12. Balance
            bodyHtml += '</tr>';
        }

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
                var invLink = makeClickableLink('sale', header.sale_id, header.vno, header.vno, '#1e3a5f');

                if (isSimple) {
                    /* SIMPLE LEDGER: ONE clean row per sale */
                    var saleQty = saleTotal ? n(saleTotal.total_qty) : 0;
                    if (saleQty === 0 && items.length > 0) {
                        $.each(items, function(idx, it) { saleQty += n(it.qty); });
                    }
                    if (saleQty > 0) grandQty += saleQty;

                    var saleDesc = '<strong>SALE INVOICE</strong>';
                    if (items.length === 1) {
                        var singleName = (items[0].item_name_urdu && items[0].item_name_urdu.trim()) ? items[0].item_name_urdu : (items[0].item_name || 'Item');
                        saleDesc += ' &mdash; <span class="text-muted small">' + singleName + (items[0].qty > 0 ? ' (' + fmt(items[0].qty) + ' pcs)' : '') + '</span>';
                    } else if (items.length > 1) {
                        saleDesc += ' &mdash; <span class="text-muted small">(' + items.length + ' items' + (saleQty > 0 ? ', ' + fmt(saleQty) + ' pcs' : '') + ')</span>';
                    }

                    bodyHtml += '<tr class="r-sale" style="border-top:1.5px solid #cbd5e1;">';
                    bodyHtml += td(rowNum++, 'center');                                                   // 1. No
                    bodyHtml += td(header.date || '', 'center');                                           // 2. Date
                    bodyHtml += td(invLink, 'center');                                                    // 3. Inv / Vouch No
                    bodyHtml += td('<span class="badge badge-primary" style="font-size:10px;padding:3px 6px;">SI</span>', 'center'); // 4. Type
                    bodyHtml += td(saleDesc, 'left');                                                     // 5. Account / Description
                    bodyHtml += td(refVal, 'center');                                                     // 6. Ref
                    bodyHtml += td(debit > 0 ? '<strong style="color:#c62828;">' + fmt(debit) + '</strong>' : '', 'right'); // 7. Debit
                    bodyHtml += td('', 'right');                                                          // 8. Credit
                    bodyHtml += td(header.balance !== null && header.balance !== undefined ? balHtml(header.balance) : '', 'right'); // 9. Balance
                    bodyHtml += '</tr>';

                } else {
                    /* GENERAL LEDGER: Full item-by-item detailed rows */
                    if (items.length === 0) {
                        var q = saleTotal ? n(saleTotal.total_qty) : 0;
                        if (q > 0) grandQty += q;
                        bodyHtml += '<tr class="r-sale" style="border-top:1.5px solid #cbd5e1;">';
                        bodyHtml += td(rowNum++, 'center');
                        bodyHtml += td(invLink, 'center');
                        bodyHtml += td('<span class="badge badge-primary" style="font-size:10px;padding:3px 6px;">SI</span>', 'center');
                        bodyHtml += td(header.date || '', 'center');
                        bodyHtml += td(refVal, 'center');
                        bodyHtml += td('<strong>' + (header.description || 'SALE') + '</strong>', 'left');
                        bodyHtml += td('', 'right'); // Rate
                        bodyHtml += td('', 'right'); // Price
                        bodyHtml += td(q > 0 ? fmt(q) : '', 'right');
                        bodyHtml += td(debit > 0 ? '<strong style="color:#c62828;">' + fmt(debit) + '</strong>' : '', 'right');
                        bodyHtml += td(credit > 0 ? '<strong style="color:#2e7d32;">' + fmt(credit) + '</strong>' : '', 'right');
                        bodyHtml += td(header.balance !== null && header.balance !== undefined ? balHtml(header.balance) : '', 'right');
                        bodyHtml += '</tr>';
                    } else {
                        $.each(items, function(idx, item) {
                            var itemQty       = n(item.qty);
                            var itemRemaining = n(item.remaining_qty);
                            var itemRate      = n(item.rate);
                            var itemAmt       = n(item.line_amount);
                            if (itemAmt === 0 && items.length === 1 && debit > 0) {
                                itemAmt = debit;
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

                            var watt = getWattVal(item.item_name) || getWattVal(item.item_name_urdu) || n(item.watt);
                            var rateStr  = '';
                            var priceStr = '';

                            if (watt > 0) {
                                if (itemRate > 0 && itemRate < 500) {
                                    rateStr  = '<strong style="color:#1e3a5f;">' + fmt(itemRate) + '</strong>';
                                    priceStr = '<strong style="color:#0f2b48;">' + fmt(itemRate * watt) + '</strong>';
                                } else {
                                    var unitP = itemRate > 0 ? itemRate : (itemQty > 0 ? itemAmt / itemQty : 0);
                                    priceStr = unitP > 0 ? '<strong style="color:#0f2b48;">' + fmt(unitP) + '</strong>' : '';
                                    var perW = unitP > 0 ? Math.round((unitP / watt) * 100) / 100 : 0;
                                    rateStr  = perW > 0 ? '<strong style="color:#1e3a5f;">' + fmt(perW) + '</strong>' : '';
                                }
                            } else {
                                var rVal = itemRate > 0 ? itemRate : (itemQty > 0 ? itemAmt / itemQty : 0);
                                var pVal = (itemQty > 0 && itemAmt > 0) ? (itemAmt / itemQty) : rVal;
                                rateStr  = rVal > 0 ? '<strong style="color:#1e3a5f;">' + fmt(rVal) + '</strong>' : dash();
                                priceStr = pVal > 0 ? '<strong style="color:#0f2b48;">' + fmt(pVal) + '</strong>' : dash();
                            }

                            // Final safety fallback: If rateStr is empty or dash but unit price exists, populate rateStr
                            if ((!rateStr || rateStr === dash()) && (itemRate > 0 || (itemQty > 0 && itemAmt > 0))) {
                                var fallbackR = itemRate > 0 ? itemRate : (itemAmt / itemQty);
                                rateStr = '<strong style="color:#1e3a5f;">' + fmt(fallbackR) + '</strong>';
                            }
                            if ((!priceStr || priceStr === dash()) && (itemRate > 0 || (itemQty > 0 && itemAmt > 0))) {
                                var fallbackP = (itemQty > 0 && itemAmt > 0) ? (itemAmt / itemQty) : itemRate;
                                priceStr = '<strong style="color:#0f2b48;">' + fmt(fallbackP) + '</strong>';
                            }

                            var qtyCellHtml = itemQty > 0 ? fmt(itemQty) : '-';
                            if (itemRemaining > 0) {
                                qtyCellHtml += '<br><small class="font-weight-bold text-dark" style="font-size:10.5px;" title="Remaining undelivered stock">بقیہ: ' + fmt(itemRemaining) + '</small>';
                            } else if (itemQty > 0) {
                                qtyCellHtml += '<br><small class="text-success font-weight-bold" style="font-size:9.5px;">(دیے گئے)</small>';
                            }

                            var borderStyle = idx === 0 ? 'border-top: 1.5px solid #cbd5e1; background: #ffffff;' : 'border-top: 1px dashed #e2e8f0; background: #ffffff;';

                            bodyHtml += '<tr class="r-sale" style="' + borderStyle + '">';
                            if (idx === 0) {
                                bodyHtml += td(rowNum++, 'center');
                                bodyHtml += td(invLink, 'center');
                                bodyHtml += td('<span class="badge badge-primary" style="font-size:10px;padding:3px 6px;">SI</span>', 'center');
                                bodyHtml += td(header.date || '', 'center');
                                bodyHtml += td(refVal, 'center');
                                bodyHtml += td(itemDetailsHtml, 'left');
                                bodyHtml += td(rateStr, 'right');
                                bodyHtml += td(priceStr, 'right');
                                bodyHtml += td(qtyCellHtml, 'right');
                                bodyHtml += td(itemAmt > 0 ? '<strong style="color:#c62828;">' + fmt(itemAmt) + '</strong>' : (debit > 0 ? '<strong style="color:#c62828;">' + fmt(debit) + '</strong>' : ''), 'right');
                                bodyHtml += td('', 'right');
                                bodyHtml += td(header.balance !== null && header.balance !== undefined ? balHtml(header.balance) : '', 'right');
                            } else {
                                bodyHtml += td('', 'center');
                                bodyHtml += td('', 'center');
                                bodyHtml += td('', 'center');
                                bodyHtml += td('', 'center');
                                bodyHtml += td('', 'center');
                                bodyHtml += td(itemDetailsHtml, 'left');
                                bodyHtml += td(rateStr, 'right');
                                bodyHtml += td(priceStr, 'right');
                                bodyHtml += td(qtyCellHtml, 'right');
                                bodyHtml += td(itemAmt > 0 ? '<strong style="color:#c62828;">' + fmt(itemAmt) + '</strong>' : '', 'right');
                                bodyHtml += td('', 'right');
                                bodyHtml += td('', 'right');
                            }
                            bodyHtml += '</tr>';
                        });

                        if (saleTotal) {
                            var addDisc  = n(saleTotal.add_disc);
                            var extraChg = n(saleTotal.extra_chg);
                            if (addDisc > 0) {
                                bodyHtml += '<tr class="r-sale" style="border-top:1px dashed #e2e8f0; background: #ffffff;">';
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
                                bodyHtml += td('', 'right');
                                bodyHtml += '</tr>';
                            }
                            if (extraChg > 0) {
                                bodyHtml += '<tr class="r-sale" style="border-top:1px dashed #e2e8f0; background: #ffffff;">';
                                bodyHtml += td('', 'center');
                                bodyHtml += td('', 'center');
                                bodyHtml += td('', 'center');
                                bodyHtml += td('', 'center');
                                bodyHtml += td('', 'center');
                                bodyHtml += td('<small style="color:#1b5e20;font-weight:700;">↳ Extra Charges (Freight)</small>', 'left');
                                bodyHtml += td('', 'right');
                                bodyHtml += td('', 'right');
                                bodyHtml += td('', 'right');
                                bodyHtml += td('<small style="color:#1b5e20;font-weight:800;">+ ' + fmt(extraChg) + '</small>', 'right');
                                bodyHtml += td('', 'right');
                                bodyHtml += td('', 'right');
                                bodyHtml += '</tr>';
                            }
                        }
                    }
                }

                continue;
            }

            /* ── REGULAR NON-SALE TRANSACTIONS (Receipt, PV, SR, JV) ── */
            var rc = 'r-txn';
            if      (t.row_type === 'receipt' || t.voucher_type === 'receipt')                 rc = 'r-receipt';
            else if (t.row_type === 'payment_voucher' || t.voucher_type === 'payment_voucher') rc = 'r-pv';
            else if (t.row_type === 'return')                                                  rc = 'r-return';
            else if (t.row_type === 'discount')                                                rc = 'r-discount';

            var debit  = n(t.debit);
            var credit = n(t.credit);
            if (debit  > 0) grandDr += debit;
            if (credit > 0) grandCr += credit;

            var typeBadge = '<span class="badge badge-secondary" style="font-size:10px;padding:3px 6px;">TXN</span>';
            var txType = 'receipt';
            var linkColor = '#166534';

            if (t.row_type === 'receipt' || t.voucher_type === 'receipt') {
                typeBadge = '<span class="badge badge-success" style="font-size:10px;padding:3px 6px;">PA</span>';
                txType = 'receipt';
                linkColor = '#166534';
            } else if (t.row_type === 'payment_voucher' || t.voucher_type === 'payment_voucher') {
                typeBadge = '<span class="badge badge-warning text-dark" style="font-size:10px;padding:3px 6px;">PV</span>';
                txType = 'payment';
                linkColor = '#6b21a8';
            } else if (t.row_type === 'return') {
                typeBadge = '<span class="badge badge-danger" style="font-size:10px;padding:3px 6px;">SR</span>';
                txType = 'return';
                linkColor = '#9f1239';
            } else if (t.row_type === 'journal_debit' || t.row_type === 'journal_credit' || t.voucher_type === 'journal_voucher') {
                typeBadge = '<span class="badge badge-info text-dark" style="font-size:10px;padding:3px 6px;">JV</span>';
                txType = 'journal';
                linkColor = '#0284c7';
            }

            var refVal = (t.bill && t.bill !== '-') ? t.bill : ((t.reference_no && t.reference_no !== '-') ? t.reference_no : '');
            var vnoLink = makeClickableLink(txType, t.voucher_id || t.sale_id || t.return_id, t.vno, t.vno, linkColor);

            if (isSimple) {
                /* SIMPLE LEDGER: Clean Account + Description */
                var accDescHtml = '';
                if (t.row_type === 'receipt' || t.voucher_type === 'receipt') {
                    accDescHtml = '<div class="font-weight-bold" style="color:#166534;"><i class="fas fa-university mr-1 text-muted"></i> ' + (t.item_name || 'Cash Account') + '</div>';
                    if (t.description) accDescHtml += '<small class="text-muted">' + t.description + '</small>';
                } else if (t.row_type === 'payment_voucher' || t.voucher_type === 'payment_voucher') {
                    accDescHtml = '<div class="font-weight-bold" style="color:#6b21a8;"><i class="fas fa-money-bill-wave mr-1 text-muted"></i> ' + (t.item_name || 'Cash Account') + '</div>';
                    if (t.description) accDescHtml += '<small class="text-muted">' + t.description + '</small>';
                } else if (t.row_type === 'return') {
                    accDescHtml = '<strong style="color:#9f1239;">SALE RETURN</strong>';
                    if (t.description || t.item_name) accDescHtml += ' &mdash; <small class="text-muted">' + (t.description || t.item_name) + '</small>';
                } else {
                    accDescHtml = '<strong>' + (t.description || 'Adjustment') + '</strong>';
                    if (t.item_name && t.item_name !== '-') accDescHtml += '<br><small class="text-muted">' + t.item_name + '</small>';
                }

                bodyHtml += '<tr class="' + rc + '" style="border-top:1.5px solid #cbd5e1;">';
                bodyHtml += td(rowNum++, 'center');                                                   // 1. No
                bodyHtml += td(t.date || '', 'center');                                               // 2. Date
                bodyHtml += td(vnoLink, 'center');                                                    // 3. Inv / Vouch No
                bodyHtml += td(typeBadge, 'center');                                                 // 4. Type
                bodyHtml += td(accDescHtml, 'left');                                                  // 5. Account / Description
                bodyHtml += td(refVal, 'center');                                                     // 6. Ref
                bodyHtml += td(debit > 0 ? '<strong style="color:#c62828;">' + fmt(debit) + '</strong>' : '', 'right'); // 7. Debit
                bodyHtml += td(credit > 0 ? '<strong style="color:#2e7d32;">' + fmt(credit) + '</strong>' : '', 'right'); // 8. Credit
                bodyHtml += td(t.balance !== null && t.balance !== undefined ? balHtml(t.balance) : '', 'right'); // 9. Balance
                bodyHtml += '</tr>';

            } else {
                /* GENERAL LEDGER: 12 columns */
                var descHtml = t.description ? '<strong>' + t.description + '</strong>' : '';
                if (t.item_name && t.item_name !== '-') {
                    descHtml += '<br><small style="color:#555;">' + t.item_name + '</small>';
                }

                bodyHtml += '<tr class="' + rc + '" style="border-top:1.5px solid #cbd5e1;">';
                bodyHtml += td(rowNum++, 'center');                                                   // 1. No
                bodyHtml += td(vnoLink, 'center');                                                    // 2. Inv No.
                bodyHtml += td(typeBadge, 'center');                                                 // 3. Type
                bodyHtml += td(t.date  || '', 'center');                                              // 4. Date
                bodyHtml += td(refVal, 'center');                                                     // 5. Ref
                bodyHtml += td(descHtml, 'left');                                                     // 6. Details
                var txRateVal = (t.rate && n(t.rate) > 0) ? ('<strong style="color:#1e3a5f;">' + fmt(t.rate) + '</strong>') : '';
                var txPriceVal = (t.rate && n(t.rate) > 0) ? ('<strong style="color:#0f2b48;">' + fmt(t.rate) + '</strong>') : '';
                bodyHtml += td(txRateVal, 'right');                                                   // 7. Rate
                bodyHtml += td(txPriceVal, 'right');                                                  // 8. Price
                bodyHtml += td(t.qty  && n(t.qty)  > 0 ? fmt(t.qty)  : '', 'right');                  // 9. Qty
                bodyHtml += td(debit  > 0 ? '<strong style="color:#c62828;">' + fmt(debit)  + '</strong>' : '', 'right'); // 10. Debit
                bodyHtml += td(credit > 0 ? '<strong style="color:#2e7d32;">' + fmt(credit) + '</strong>' : '', 'right');// 11. Credit
                bodyHtml += td(t.balance !== null && t.balance !== undefined ? balHtml(t.balance) : '', 'right');          // 12. Balance
                bodyHtml += '</tr>';
            }

            i++;
        }

        /* ── footer HTML & KPI update ── */
        $('#kpiTotalDeliveredQty').text(fmt(grandQty));
        $('#kpiTotalRemainingQty').text(fmt(res.total_remaining_qty || 0));

        $('#s_delivered').text(fmt(grandQty));
        $('#s_remaining').text(fmt(res.total_remaining_qty || 0));

        var finalBal = n(res.closing_balance);

        /* Footer construction */
        var footHtml = '';
        if (isSimple) {
            footHtml += '<tr class="r-total">';
            footHtml += '<td colspan="6" style="text-align:right;border:1px solid #ccc;padding:6px 10px;font-size:13px;"><strong>Total Sum (کل میزان)</strong></td>';
            footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;"><strong style="color:#c62828;">' + fmt(grandDr) + '</strong></td>';
            footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;"><strong style="color:#2e7d32;">' + fmt(grandCr) + '</strong></td>';
            footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;">' + balHtml(finalBal) + '</td>';
            footHtml += '</tr>';

            footHtml += '<tr class="r-close">';
            footHtml += '<td colspan="8" style="text-align:right;border:1px solid #333;padding:8px 12px;font-size:13px;letter-spacing:.4px;"><strong>CLOSING BALANCE (بقیہ بیلنس)</strong></td>';
            footHtml += '<td style="text-align:right;border:1px solid #333;padding:8px 10px;font-size:15px;">' + balHtml(finalBal) + '</td>';
            footHtml += '</tr>';
        } else {
            var footerQtyHtml = fmt(grandQty);
            if (n(res.total_remaining_qty) > 0) {
                footerQtyHtml += '<br><small style="color:#0f172a;font-weight:800;font-size:11px;">(بقیہ: ' + fmt(res.total_remaining_qty) + ')</small>';
            }

            footHtml += '<tr class="r-total">';
            footHtml += '<td colspan="6" style="text-align:right;border:1px solid #ccc;padding:6px 10px;font-size:13px;"><strong>Total Sum (All Transactions)</strong></td>';
            footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;">&#8212;</td>';
            footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;">&#8212;</td>';
            footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;font-size:13px;font-weight:800;color:#1a1a2e;background:#eaf4ff;">' + footerQtyHtml + '</td>';
            footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;"><strong style="color:#c62828;">' + fmt(grandDr) + '</strong></td>';
            footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;"><strong style="color:#2e7d32;">' + fmt(grandCr) + '</strong></td>';
            footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;">' + balHtml(finalBal) + '</td>';
            footHtml += '</tr>';

            footHtml += '<tr class="r-close">';
            footHtml += '<td colspan="11" style="text-align:right;border:1px solid #333;padding:8px 12px;font-size:13px;letter-spacing:.4px;"><strong>CLOSING BALANCE</strong></td>';
            footHtml += '<td style="text-align:right;border:1px solid #333;padding:8px 10px;font-size:15px;">' + balHtml(finalBal) + '</td>';
            footHtml += '</tr>';
        }

        /* ── inject into DOM ── */
        $('#ledgerBody').html(bodyHtml);
        $('#ledgerFooter').html(footHtml);

        $('#ledgerBox').show();
        $('#printBtnWrap').show();
        buildCustomerReferencePrint(res, start, end);
    }

    /* ---------- Reference Print Builder (Exact Accounting Format Matching Reference) ---------- */
    function buildCustomerReferencePrint(res, start, end) {
        if (!res || !res.customer) return;
        var c = res.customer;
        var ob = n(res.opening_balance);
        var txns = res.transactions || [];

        // Branch detection
        var branchName = '';
        var bSel = document.getElementById('branch_id');
        if (bSel && bSel.selectedIndex > 0) {
            var txt = bSel.options[bSel.selectedIndex].text;
            if (txt && txt.indexOf('--') === -1) branchName = txt.trim();
        }

        // Date formatting helper: 2026-10-28 -> 28-Oct-2026
        function refDate(dStr) {
            if (!dStr) return '';
            var parts = String(dStr).split(/[-/]/);
            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            if (parts.length === 3) {
                if (parts[0].length === 4) {
                    var mo = parseInt(parts[1], 10) - 1;
                    return parts[2] + '-' + (months[mo] || parts[1]) + '-' + parts[0];
                } else {
                    var mo = parseInt(parts[1], 10) - 1;
                    var yr = parts[2].length === 2 ? ('20' + parts[2]) : parts[2];
                    return parts[0] + '-' + (months[mo] || parts[1]) + '-' + yr;
                }
            }
            return dStr;
        }

        // Standard 2-decimal money format (e.g. 10,006,344.00)
        function fmt2(v) {
            var num = parseFloat(v) || 0;
            return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Current Date & Time for header
        var now = new Date();
        var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var pDate = String(now.getDate()).padStart(2, '0') + '-' + monthNames[now.getMonth()] + '-' + now.getFullYear();
        var hrs = now.getHours();
        var mins = String(now.getMinutes()).padStart(2, '0');
        var ampm = hrs >= 12 ? 'pm' : 'am';
        hrs = hrs % 12; hrs = hrs ? hrs : 12;
        var pDateTime = pDate + ' &nbsp;&nbsp; ' + hrs + ':' + mins + ' ' + ampm;

        // Financial Year
        var finYear = (start && start.length >= 4) ? start.substring(0, 4) : now.getFullYear();

        // Account Details
        var acCode = c.customer_id ? c.customer_id : ('23-01-' + String(c.id).padStart(6, '0'));
        var acDesc = (c.name || 'CUSTOMER').toUpperCase();
        var acSub = '';
        if (c.address && c.address !== '-') acSub += c.address;
        if (c.mobile && c.mobile !== '-') acSub += (acSub ? ' &nbsp;|&nbsp; ' : '') + 'Ph: ' + c.mobile;

        var html = '';
        html += '<div style="font-family: \'Segoe UI\', Tahoma, Arial, sans-serif; color: #000; padding: 2px;">';

        // 1. TOP HEADER (Center company name, report title, right date/time/page)
        html += '<div style="position: relative; text-align: center; margin-bottom: 14px;">';
        html += '  <div style="font-family: \'Times New Roman\', Times, Georgia, serif; font-size: 24px; font-weight: bold; color: #000; letter-spacing: 0.5px;">Ameen &amp; Sons</div>';
        if (branchName) {
            html += '  <div style="font-size: 12px; font-weight: 600; color: #333; margin-top: 1px;">Branch: ' + branchName + '</div>';
        }
        html += '  <div style="font-size: 15px; font-weight: 700; color: #111; margin-top: 3px;">General Ledger Report</div>';
        html += '  <div style="position: absolute; right: 0; top: 0; text-align: right; font-size: 11px; line-height: 1.4; color: #222;">';
        html += '    <div>' + pDateTime + '</div>';
        html += '    <div style="margin-top: 2px;">Page 1 of 1</div>';
        html += '  </div>';
        html += '</div>';

        // 2. ACCOUNT HEADER ROW (Left rounded box + Right dates)
        html += '<div style="display: flex; justify-content: space-between; align-items: stretch; margin-bottom: 8px;">';
        // Left Box
        html += '  <div style="width: 63%; border: 1.5px solid #000; border-radius: 14px; padding: 8px 14px; font-size: 12px; line-height: 1.5; box-sizing: border-box;">';
        html += '    <div><strong>A/C Code :</strong> &nbsp; <span style="font-family: monospace; font-size: 12.5px;">' + acCode + '</span></div>';
        html += '    <div style="margin-top: 2px;"><strong>A/C Description :</strong> &nbsp; <strong style="font-size: 12.5px;">' + acDesc + '</strong></div>';
        if (acSub) {
            html += '    <div style="font-size: 10.5px; color: #333; margin-top: 1px; padding-left: 110px;">' + acSub + '</div>';
        }
        html += '  </div>';
        // Right Dates
        html += '  <div style="width: 33%; display: flex; flex-direction: column; justify-content: center; font-size: 12px; box-sizing: border-box;">';
        html += '    <div style="display: flex; align-items: baseline; margin-bottom: 8px;">';
        html += '      <strong style="width: 80px;">From Date :</strong>';
        html += '      <span style="flex: 1; border-bottom: 1px solid #000; text-align: center; font-weight: 600; padding-bottom: 2px;">' + refDate(start) + '</span>';
        html += '    </div>';
        html += '    <div style="display: flex; align-items: baseline;">';
        html += '      <strong style="width: 80px;">To Date :</strong>';
        html += '      <span style="flex: 1; border-bottom: 1px solid #000; text-align: center; font-weight: 600; padding-bottom: 2px;">' + refDate(end) + '</span>';
        html += '    </div>';
        html += '  </div>';
        html += '</div>';

        // 3. SUB-BOXES ROW (Left: Financial Year & Op Bal, Right: Balance Before)
        html += '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
        // Left Sub-box
        html += '  <div style="width: 48%; border: 1.5px solid #000; border-radius: 12px; padding: 6px 14px; font-size: 11.5px; line-height: 1.5; box-sizing: border-box;">';
        html += '    <div><strong>Financial Year :</strong> &nbsp;&nbsp;&nbsp;&nbsp; <span>' + finYear + '</span></div>';
        html += '    <div style="display: flex; justify-content: space-between; margin-top: 2px;">';
        html += '      <span><strong>Year Op. Bal :</strong> &nbsp; ' + fmt2(Math.abs(ob)) + '</span>';
        html += '      <strong>' + (ob >= 0 ? 'Debit' : 'Credit') + '</strong>';
        html += '    </div>';
        html += '  </div>';
        // Right Sub-box
        html += '  <div style="width: 38%; border: 1.5px solid #000; border-radius: 12px; padding: 10px 16px; font-size: 12px; display: flex; justify-content: space-between; align-items: center; box-sizing: border-box;">';
        html += '    <strong>Balance Before :</strong>';
        html += '    <strong style="font-size: 13px; font-family: monospace;">' + fmt2(Math.abs(ob)) + '</strong>';
        html += '  </div>';
        html += '</div>';

        // 4. THE 9-COLUMN ACCOUNTING TABLE
        html += '<table style="width: 100%; border-collapse: collapse; font-size: 10.5px; margin-top: 4px; font-family: inherit;">';
        html += '  <thead>';
        html += '    <tr style="background: #f1f3f5; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000;">';
        html += '      <th style="border: 1px solid #555; padding: 4px 3px; text-align: center; width: 75px;">Date</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 3px; text-align: center; width: 95px;">Voucher #</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 3px; text-align: center; width: 55px;">Ref #</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 3px; text-align: center; width: 55px;">Doc. #</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 6px; text-align: left;">Remarks</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 3px; text-align: center; width: 70px;">Cheque #</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 6px; text-align: right; width: 95px;">Debit</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 6px; text-align: right; width: 95px;">Credit</th>';
        html += '      <th style="border: 1px solid #555; padding: 4px 6px; text-align: right; width: 105px;">Balance</th>';
        html += '    </tr>';
        html += '  </thead>';
        html += '  <tbody>';

        // ROW 1: OPENING BALANCE
        html += '    <tr style="border-bottom: 1px solid #777;">';
        html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + refDate(start) + '</td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">-</td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;"></td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;"></td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: left;"><strong>OPENING BALANCE</strong></td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;"></td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right;">' + (ob > 0 ? fmt2(ob) : '0.00') + '</td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right;">' + (ob < 0 ? fmt2(Math.abs(ob)) : '0.00') + '</td>';
        html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right; font-weight: bold;">' + fmt2(Math.abs(ob)) + '</td>';
        html += '    </tr>';

        // TRANSACTION ROWS
        var i = 0;
        while (i < txns.length) {
            var t = txns[i];

            if (t.row_type === 'sale_header') {
                var header = t;
                var items = [];
                i++;
                while (i < txns.length && txns[i].row_type === 'sale_item') {
                    items.push(txns[i]);
                    i++;
                }
                if (i < txns.length && txns[i].row_type === 'sale_total') {
                    i++;
                }

                var debit = n(header.debit);
                var refVal = (header.bill && header.bill !== '-') ? header.bill : '';
                var docVal = header.sale_id || '';

                var remarks = '';
                if (items.length > 0) {
                    var itemParts = [];
                    for (var k = 0; k < items.length; k++) {
                        var it = items[k];
                        var iName = (it.item_name_urdu && it.item_name_urdu.trim()) ? it.item_name_urdu : (it.item_name || 'Item');
                        var itQty = n(it.qty);
                        itemParts.push(iName + (itQty > 0 ? ' (' + itQty + ')' : ''));
                    }
                    remarks = itemParts.join(', ');
                }
                if (!remarks) remarks = header.description || 'SALE INVOICE';

                html += '    <tr style="border-bottom: 1px solid #777;">';
                html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + refDate(header.date) + '</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center; font-weight: 600;">' + (header.vno || '') + '</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + refVal + '</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + docVal + '</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: left;">' + remarks + '</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;"></td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right;">' + (debit > 0 ? fmt2(debit) : '0.00') + '</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right;">0.00</td>';
                html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right; font-weight: 600;">' + (header.balance !== null ? fmt2(Math.abs(header.balance)) : '') + '</td>';
                html += '    </tr>';
                continue;
            }

            var dVal = refDate(t.date);
            var vVal = t.vno || '';
            var rVal = (t.bill && t.bill !== '-') ? t.bill : ((t.reference_no && t.reference_no !== '-') ? t.reference_no : '');
            var docNo = t.voucher_id || t.sale_id || '';
            var remVal = t.description || '';
            if (t.item_name && t.item_name !== '-') {
                remVal += (remVal ? ' - ' : '') + t.item_name;
            }
            var chqVal = (t.cheque_no && t.cheque_no !== '-') ? t.cheque_no : '';
            var dr = n(t.debit);
            var cr = n(t.credit);
            var bal = t.balance !== null && t.balance !== undefined ? fmt2(Math.abs(t.balance)) : '';

            html += '    <tr style="border-bottom: 1px solid #777;">';
            html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + dVal + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center; font-weight: 600;">' + vVal + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + rVal + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + docNo + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: left;">' + remVal + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 3px; text-align: center;">' + chqVal + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right;">' + (dr > 0 ? fmt2(dr) : '0.00') + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right;">' + (cr > 0 ? fmt2(cr) : '0.00') + '</td>';
            html += '      <td style="border: 1px solid #777; padding: 4px 6px; text-align: right; font-weight: 600;">' + bal + '</td>';
            html += '    </tr>';

            i++;
        }

        html += '  </tbody>';

        // 5. TOTALS ROW (Footer)
        var finalClose = n(res.closing_balance);
        html += '  <tfoot>';
        html += '    <tr style="background: #f8fafc; font-weight: bold; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000;">';
        html += '      <td colspan="6" style="border: 1px solid #555; padding: 6px 8px; text-align: right; font-size: 11px;">Total Sum :</td>';
        html += '      <td style="border: 1px solid #555; padding: 6px 6px; text-align: right; font-size: 11px;">' + fmt2(res.total_debit) + '</td>';
        html += '      <td style="border: 1px solid #555; padding: 6px 6px; text-align: right; font-size: 11px;">' + fmt2(res.total_credit) + '</td>';
        html += '      <td style="border: 1px solid #555; padding: 6px 6px; text-align: right; font-size: 11px;">' + fmt2(Math.abs(finalClose)) + '</td>';
        html += '    </tr>';
        html += '  </tfoot>';
        html += '</table>';

        // 6. SIGNATURE LINES
        html += '<div style="margin-top: 45px; display: flex; justify-content: space-between; padding: 0 40px; page-break-inside: avoid;">';
        html += '  <div style="text-align: center; width: 160px;">';
        html += '    <div style="border-top: 1.5px solid #000; padding-top: 5px; font-weight: bold; font-size: 11px;">Prepared By</div>';
        html += '  </div>';
        html += '  <div style="text-align: center; width: 160px;">';
        html += '    <div style="border-top: 1.5px solid #000; padding-top: 5px; font-weight: bold; font-size: 11px;">Checked By</div>';
        html += '  </div>';
        html += '  <div style="text-align: center; width: 160px;">';
        html += '    <div style="border-top: 1.5px solid #000; padding-top: 5px; font-weight: bold; font-size: 11px;">Authorized By</div>';
        html += '  </div>';
        html += '</div>';

        html += '</div>';

        var container = document.getElementById('referencePrintContainer');
        if (container) {
            container.innerHTML = html;
        }
    }

    /* Print Trigger */
    window.printCustomerReferenceLedger = function() {
        if (!lastLedgerResponse || !lastLedgerResponse.customer) {
            alert('Please select a customer and generate ledger first.');
            return;
        }
        buildCustomerReferencePrint(lastLedgerResponse, lastStartDate, lastEndDate);
        setTimeout(function() {
            window.print();
        }, 100);
    };

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
        var headerRow = (currentLedgerMode === 'simple')
            ? ['No', 'Date', 'Inv / Vouch No', 'Type', 'Account / Description', 'Ref / Chq', 'Debit', 'Credit', 'Balance']
            : ['No', 'Inv No', 'Type', 'Date', 'Ref', 'Details', 'Rate', 'Price', 'Qty', 'Debit', 'Credit', 'Balance'];
        var rows = [headerRow];

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
        a.download = (currentLedgerMode === 'simple' ? 'customer_simple_ledger_' : 'customer_general_ledger_') + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
    };
</script>
@endsection
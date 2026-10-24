@extends('admin_panel.layout.app')

@section('content')
@can('purchase.view')

<style>
    :root {
        --pur-navy: #0f1f38;
        --pur-navy-light: #1e3a5f;
        --pur-gold: #c8973a;
        --pur-emerald: #059669;
        --pur-crimson: #dc2626;
        --pur-slate: #475569;
        --pur-border: #e2e8f0;
        --pur-bg: #f8fafc;
    }

    .pur-wrapper {
        background-color: #f1f5f9;
        min-height: 100vh;
        padding: 1.5rem 0 3rem 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Top Page Header */
    .pur-header {
        background: #ffffff;
        border-radius: 12px;
        padding: 1.25rem 1.75rem;
        border: 1px solid var(--pur-border);
        box-shadow: 0 4px 15px -3px rgba(15, 31, 56, 0.05);
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .pur-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--pur-navy);
        letter-spacing: -0.5px;
        margin-bottom: 3px;
    }

    .pur-subtitle {
        font-size: 0.8rem;
        color: var(--pur-slate);
        font-weight: 500;
    }

    /* KPI Cards Grid */
    .pur-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 992px) {
        .pur-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 576px) {
        .pur-kpi-grid { grid-template-columns: 1fr; }
    }

    .pur-kpi-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        border: 1px solid var(--pur-border);
        box-shadow: 0 4px 15px -3px rgba(15, 31, 56, 0.05);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .pur-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -5px rgba(15, 31, 56, 0.1);
    }

    .pur-kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }

    .kpi-blue::before { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }
    .kpi-navy::before { background: linear-gradient(90deg, var(--pur-navy), var(--pur-navy-light)); }
    .kpi-green::before { background: linear-gradient(90deg, #10b981, #059669); }
    .kpi-red::before { background: linear-gradient(90deg, #ef4444, #dc2626); }

    .pur-kpi-label {
        font-size: 0.725rem;
        font-weight: 700;
        color: var(--pur-slate);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
    }

    .pur-kpi-value {
        font-size: 1.4rem;
        font-weight: 900;
        color: var(--pur-navy);
        line-height: 1.2;
        font-family: monospace;
    }

    .pur-kpi-sub {
        font-size: 0.725rem;
        color: #64748b;
        margin-top: 4px;
    }

    .pur-kpi-icon {
        position: absolute;
        right: 1.25rem;
        bottom: 1.25rem;
        font-size: 2.2rem;
        opacity: 0.12;
        color: var(--pur-navy);
    }

    /* Filter Tabs Bar */
    .pur-filter-bar {
        background: #ffffff;
        border-radius: 10px;
        padding: 0.6rem 1rem;
        border: 1px solid var(--pur-border);
        box-shadow: 0 2px 8px -2px rgba(15, 31, 56, 0.04);
        margin-bottom: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .pur-pill-group {
        display: flex;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .pur-pill {
        border: 1px solid var(--pur-border);
        background: #f8fafc;
        color: var(--pur-slate);
        font-size: 0.75rem;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .pur-pill:hover, .pur-pill.active {
        background: var(--pur-navy);
        color: #ffffff;
        border-color: var(--pur-navy);
    }

    /* Table Card */
    .pur-table-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid var(--pur-border);
        box-shadow: 0 6px 20px -4px rgba(15, 31, 56, 0.07);
        overflow: visible !important;
    }

    .table thead th {
        background: var(--pur-navy) !important;
        color: #ffffff !important;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 12px 14px;
        border: none;
        vertical-align: middle;
    }

    .table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #edf2f7;
        font-size: 0.825rem;
        vertical-align: middle;
        color: #1e293b;
    }

    .table tbody tr:nth-child(even) { background-color: #fafcff; }
    .table tbody tr:hover { background-color: #f1f5f9; }

    .table tfoot th {
        background: #f8fafc;
        border-top: 2px solid var(--pur-navy);
        padding: 12px 14px;
        font-size: 0.825rem;
        font-weight: 800;
        color: var(--pur-navy);
    }

    /* Custom Badges */
    .bg-soft-success { background-color: #ecfdf5 !important; color: #065f46 !important; border: 1px solid #a7f3d0; }
    .bg-soft-warning { background-color: #fefce8 !important; color: #854d0e !important; border: 1px solid #fef08a; }
    .bg-soft-danger  { background-color: #fef2f2 !important; color: #991b1b !important; border: 1px solid #fecaca; }
    .bg-soft-info    { background-color: #f0f9ff !important; color: #075985 !important; border: 1px solid #bae6fd; }

    /* Dropdown Styling - Opens inwards to Left to prevent overflow & scroll */
    .manage-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .btn-manage {
        transition: all 0.2s ease;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: var(--pur-slate);
        border-radius: 6px;
        font-weight: 700;
        font-size: 11.5px;
        padding: 4px 10px;
        white-space: nowrap;
    }

    .manage-dropdown:hover .btn-manage, .btn-manage:focus {
        background: var(--pur-navy);
        color: #ffffff;
        border-color: var(--pur-navy);
        box-shadow: 0 4px 10px rgba(15, 31, 56, 0.15);
    }

    .manage-dropdown .dropdown-menu {
        position: absolute !important;
        right: 0 !important;
        left: auto !important;
        top: 100% !important;
        min-width: 195px !important;
        border: 1px solid #e2e8f0;
        box-shadow: 0 12px 30px rgba(15, 31, 56, 0.18);
        border-radius: 10px;
        padding: 6px;
        z-index: 1060 !important;
        background: #ffffff !important;
        margin-top: 3px !important;
        transform: none !important;
    }

    .manage-dropdown .dropdown-item {
        padding: 8px 12px !important;
        font-size: 12.5px !important;
        font-weight: 600;
        color: #334155;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap !important;
    }

    .manage-dropdown .dropdown-item:hover {
        background-color: #f1f5f9;
        color: var(--pur-navy);
    }

    /* DataTables Inputs */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 6px 12px;
        font-size: 13px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--pur-navy);
        box-shadow: 0 0 0 3px rgba(15, 31, 56, 0.1);
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        padding: 4px 8px;
        font-size: 13px;
    }

    /* Fix responsive table dropdown overflow */
    .table-responsive {
        overflow-x: auto;
        overflow-y: visible !important;
        padding-bottom: 85px;
        margin-bottom: -85px;
    }
</style>

<div class="pur-wrapper">
    <div class="container-fluid px-4">

        {{-- Top Header Banner --}}
        <div class="pur-header">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge" style="background: var(--pur-gold); color: #000; font-size: 10px; font-weight: 800; letter-spacing: 0.5px;">PROCUREMENT ERP</span>
                    <span class="text-muted small">|</span>
                    <span class="badge bg-soft-info text-primary font-weight-bold" style="font-size: 10px;"><i class="fas fa-sort-amount-down me-1"></i> Newest First (DESC)</span>
                </div>
                <h4 class="pur-title mb-0">
                    <i class="fas fa-boxes text-primary me-2"></i> Purchase Orders & Invoices
                </h4>
                <div class="pur-subtitle">
                    Comprehensive procurement tracking, vendor settlement balances, and receiving inventory.
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('purchase.addLocal') }}" class="btn btn-sm btn-success font-weight-bold px-3 py-2 shadow-sm" style="border-radius: 8px;">
                    <i class="fas fa-cart-plus me-1"></i> Add Local Purchase
                </a>
                <a href="{{ route('add_purchase') }}" class="btn btn-sm text-white font-weight-bold px-3 py-2 shadow-sm" style="background: var(--pur-navy); border-radius: 8px;">
                    <i class="fas fa-plus me-1"></i> Add PO Purchase
                </a>
                <button onclick="location.reload()" class="btn btn-sm btn-outline-secondary font-weight-bold px-3 py-2" style="border-radius: 8px;" title="Refresh list">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        {{-- Session Flash Alerts --}}
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                <strong><i class="fas fa-check-circle me-2"></i>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                <strong><i class="fas fa-exclamation-circle me-2"></i>Notice!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- KPI Summary Cards --}}
        <div class="pur-kpi-grid">
            {{-- Card 1: Total Purchases --}}
            <div class="pur-kpi-card kpi-blue">
                <div class="pur-kpi-label">Total Purchases</div>
                <div class="pur-kpi-value text-primary">{{ number_format($totalPurchases) }}</div>
                <div class="pur-kpi-sub">Procurement invoices on record</div>
                <i class="fas fa-file-invoice pur-kpi-icon"></i>
            </div>

            {{-- Card 2: Total Net Amount --}}
            <div class="pur-kpi-card kpi-navy">
                <div class="pur-kpi-label">Total Bill Amount</div>
                <div class="pur-kpi-value" style="color: var(--pur-navy);">Rs. {{ number_format($totalNet, 2) }}</div>
                <div class="pur-kpi-sub">Total gross payable volume</div>
                <i class="fas fa-receipt pur-kpi-icon"></i>
            </div>

            {{-- Card 3: Total Paid Amount --}}
            <div class="pur-kpi-card kpi-green">
                <div class="pur-kpi-label">Total Settled (Paid)</div>
                <div class="pur-kpi-value text-success">Rs. {{ number_format($totalPaid, 2) }}</div>
                <div class="pur-kpi-sub">Paid via Cash & Bank accounts</div>
                <i class="fas fa-check-circle pur-kpi-icon"></i>
            </div>

            {{-- Card 4: Total Outstanding Due --}}
            <div class="pur-kpi-card kpi-red">
                <div class="pur-kpi-label">Outstanding Due Balance</div>
                <div class="pur-kpi-value text-danger">Rs. {{ number_format($totalDue, 2) }}</div>
                <div class="pur-kpi-sub">Payable vendor credit balance</div>
                <i class="fas fa-exclamation-triangle pur-kpi-icon"></i>
            </div>
        </div>

        {{-- Quick Filter Pills --}}
        <div class="pur-filter-bar">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted font-weight-bold small text-uppercase" style="font-size: 11px;">
                    <i class="fas fa-filter text-primary me-1"></i> Quick Filter:
                </span>
                <div class="pur-pill-group">
                    <button type="button" class="pur-pill active" data-filter="all">All ({{ $totalPurchases }})</button>
                    <button type="button" class="pur-pill" data-filter="paid">Fully Paid</button>
                    <button type="button" class="pur-pill" data-filter="due">Has Due Balance</button>
                    <button type="button" class="pur-pill" data-filter="pending_del">Pending Receiving</button>
                    <button type="button" class="pur-pill" data-filter="received">Received Complete</button>
                </div>
            </div>

            <div class="text-muted small">
                Showing all procurement entries &mdash; <strong>Newest Invoices on Top (DESC)</strong>
            </div>
        </div>

        {{-- Main Table Card --}}
        <div class="pur-table-card">
            <div class="table-responsive">
                <table id="purchase-table" class="table table-hover mb-0 w-100">
                    <thead>
                        <tr class="text-center">
                            <th style="width: 55px;" class="text-center">ID</th>
                            <th style="width: 95px;" class="text-center">Date</th>
                            @if($showBranchColumn) <th style="width: 110px;">Branch</th> @endif
                            <th class="text-start">Vendor / Destination</th>
                            <th style="width: 140px;">Invoice #</th>
                            <th class="text-end" style="width: 125px;">Net Amount</th>
                            <th class="text-end" style="width: 115px;">Paid</th>
                            <th class="text-end" style="width: 125px;">Due Balance</th>
                            <th class="text-center" style="width: 95px;">Payment</th>
                            <th class="text-center" style="width: 95px;">Delivery</th>
                            <th class="text-center" style="width: 95px; padding-right: 18px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($Purchase as $purchase)
                            @php
                                $isPaid = $purchase->due_amount <= 0;
                                $isPartial = ($purchase->paid_amount > 0 && $purchase->due_amount > 0);
                                $payStatus = $isPaid ? 'paid' : ($isPartial ? 'partial' : 'unpaid');
                                $recStatus = $purchase->receipt_status ?? 'received';
                            @endphp
                            <tr data-pay-status="{{ $payStatus }}" data-rec-status="{{ $recStatus }}">
                                {{-- Numeric ID for strict ascending sorting --}}
                                <td class="text-center font-monospace font-weight-bold" data-sort="{{ $purchase->id }}" style="font-size: 12px; color: var(--pur-navy);">
                                    <span class="badge bg-light text-dark border">#{{ $purchase->id }}</span>
                                </td>

                                {{-- Date --}}
                                <td class="text-center text-muted font-monospace" style="font-size: 11.5px;">
                                    {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }}
                                </td>

                                {{-- Branch (conditional) --}}
                                @if($showBranchColumn)
                                    <td class="text-center">
                                        <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 10px; font-weight: 700;">
                                            <i class="fas fa-building text-muted me-1"></i>{{ $purchase->branch->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                @endif

                                {{-- Vendor / Warehouse --}}
                                <td class="text-start">
                                    <div class="font-weight-bold text-dark text-uppercase" style="font-size: 13px;">
                                        {{ $purchase->vendor->name ?? $purchase->vendor_name ?? 'Local Market Supplier' }}
                                    </div>
                                    <div class="small text-muted" style="font-size: 11px;">
                                        <i class="fas fa-warehouse me-1 text-secondary"></i>
                                        @php
                                            if ($purchase->warehouse_id && $purchase->warehouse) {
                                                echo $purchase->warehouse->warehouse_name;
                                            } elseif ($purchase->items && $purchase->items->count() > 0) {
                                                echo $purchase->items->pluck('warehouse.warehouse_name')->unique()->filter()->implode(', ') ?: 'Branch Inventory';
                                            } else {
                                                echo 'Branch Direct Display';
                                            }
                                        @endphp
                                    </div>
                                </td>

                                {{-- Invoice No & Type --}}
                                <td class="text-center">
                                    <div class="font-monospace font-weight-bold text-dark" style="font-size: 12.5px;">
                                        {{ $purchase->formatted_invoice }}
                                    </div>
                                    <span class="badge {{ $purchase->purchase_type === 'local' ? 'bg-soft-info' : 'bg-light text-muted border' }}" style="font-size: 9px; font-weight: 700;">
                                        {{ strtoupper($purchase->purchase_type ?? 'Standard') }}
                                    </span>
                                </td>

                                {{-- Net Amount --}}
                                <td class="text-end font-monospace font-weight-bold" style="color: var(--pur-navy); font-size: 13px;">
                                    Rs. {{ number_format($purchase->net_amount, 2) }}
                                </td>

                                {{-- Paid Amount --}}
                                <td class="text-end font-monospace font-weight-bold text-success" style="font-size: 12.5px;">
                                    Rs. {{ number_format($purchase->paid_amount, 2) }}
                                </td>

                                {{-- Due Balance --}}
                                <td class="text-end font-monospace font-weight-bold {{ $purchase->due_amount > 0 ? 'text-danger' : 'text-muted' }}" style="font-size: 13px;">
                                    Rs. {{ number_format($purchase->due_amount, 2) }}
                                </td>

                                {{-- Payment Status --}}
                                <td class="text-center">
                                    @if($isPaid)
                                        <span class="badge bg-soft-success px-2 py-1" style="font-size: 10px; font-weight: 800;">
                                            <i class="fas fa-check me-1"></i> PAID
                                        </span>
                                    @elseif($isPartial)
                                        <span class="badge bg-soft-warning px-2 py-1" style="font-size: 10px; font-weight: 800;">
                                            <i class="fas fa-adjust me-1"></i> PARTIAL
                                        </span>
                                    @else
                                        <span class="badge bg-soft-danger px-2 py-1" style="font-size: 10px; font-weight: 800;">
                                            <i class="fas fa-clock me-1"></i> DUE
                                        </span>
                                    @endif
                                </td>

                                {{-- Delivery / Inward Status --}}
                                <td class="text-center">
                                    @if($purchase->receipt_status === 'pending')
                                        <span class="badge bg-soft-warning px-2 py-1 text-uppercase" style="font-size: 10px; font-weight: 800;">Pending</span>
                                    @elseif($purchase->receipt_status === 'partial')
                                        <span class="badge bg-soft-info px-2 py-1 text-uppercase" style="font-size: 10px; font-weight: 800;">Partial</span>
                                    @else
                                        <span class="badge bg-soft-success px-2 py-1 text-uppercase" style="font-size: 10px; font-weight: 800;">Received</span>
                                    @endif
                                </td>

                                {{-- Action Dropdown --}}
                                <td class="text-center" style="padding-right: 18px;">
                                    <div class="dropdown manage-dropdown">
                                        <button class="btn btn-manage dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Manage
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end shadow-lg">
                                            @can('purchase.invoice')
                                                <a class="dropdown-item" href="{{ route('purchase.invoice', $purchase->id) }}">
                                                    <i class="fas fa-print text-warning"></i> View / Print Invoice
                                                </a>
                                            @endcan

                                            @if($purchase->due_amount > 0)
                                                <a class="dropdown-item btn-quick-pay" href="javascript:void(0)"
                                                   data-id="{{ $purchase->id }}"
                                                   data-invoice="{{ $purchase->formatted_invoice }}"
                                                   data-supplier="{{ $purchase->vendor->name ?? $purchase->vendor_name ?? 'Local Supplier' }}"
                                                   data-due="{{ (float)$purchase->due_amount }}">
                                                    <i class="fas fa-hand-holding-usd text-success"></i> Record Payment
                                                </a>
                                            @endif

                                            @can('purchase.edit')
                                                <a class="dropdown-item" href="{{ route('purchase.edit', $purchase->id) }}">
                                                    <i class="fas fa-edit text-primary"></i> Edit Purchase
                                                </a>
                                            @endcan

                                            @can('purchase.return')
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="{{ route('purchase.return.show', $purchase->id) }}">
                                                    <i class="fas fa-undo text-danger"></i> Purchase Return
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="{{ $showBranchColumn ? '5' : '4' }}" class="text-end text-uppercase" style="letter-spacing: 0.5px;">
                                Total Procurement Volume:
                            </th>
                            <th class="text-end font-monospace font-weight-bold" style="color: var(--pur-navy); font-size: 13.5px;">
                                Rs. {{ number_format($totalNet, 2) }}
                            </th>
                            <th class="text-end font-monospace font-weight-bold text-success" style="font-size: 13px;">
                                Rs. {{ number_format($totalPaid, 2) }}
                            </th>
                            <th class="text-end font-monospace font-weight-bold text-danger" style="font-size: 13.5px;">
                                Rs. {{ number_format($totalDue, 2) }}
                            </th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- Quick Payment Modal --}}
<div class="modal fade" id="quickPayModal" tabindex="-1" aria-labelledby="quickPayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--pur-navy) 0%, var(--pur-navy-light) 100%); border-bottom: 3px solid var(--pur-gold);">
                <h5 class="modal-title font-weight-bold" id="quickPayModalLabel" style="font-size: 15px;">
                    <i class="fas fa-hand-holding-usd me-2 text-warning"></i> Record Purchase Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickPayForm" method="POST">
                @csrf
                <div class="modal-body p-4" style="background: #f8fafc;">
                    <input type="hidden" id="qp_purchase_id" name="purchase_id">

                    {{-- Summary strip --}}
                    <div class="p-3 mb-3 rounded border bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted text-uppercase" style="font-size: 10px; font-weight: 700;">Invoice & Party</div>
                            <div class="font-weight-bold text-dark" id="qp_supplier_display">---</div>
                            <div class="small text-primary font-monospace" id="qp_invoice_display">---</div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted text-uppercase" style="font-size: 10px; font-weight: 700;">Outstanding Due</div>
                            <div class="font-weight-bold text-danger font-monospace" style="font-size: 15px;" id="qp_due_display">
                                Rs. 0.00
                            </div>
                        </div>
                    </div>

                    {{-- Account Select --}}
                    <div class="mb-3">
                        <label class="form-label font-weight-bold small text-dark">
                            <i class="fas fa-wallet text-primary me-1"></i> Payment Account (Cash / Bank) <span class="text-danger">*</span>
                        </label>
                        <select name="account_id" id="qp_account_id" class="form-select form-select-sm" required style="border-radius: 6px;">
                            <option value="" disabled selected>-- Select Payment Account --</option>
                            @foreach($bankAccounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->title }} ({{ $acc->head->name ?? $acc->head->title ?? 'Account' }}) — Bal: Rs. {{ number_format($acc->closing_balance ?? $acc->balance ?? 0, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Amount --}}
                    <div class="mb-3">
                        <label class="form-label font-weight-bold small text-dark">
                            <i class="fas fa-money-bill-wave text-success me-1"></i> Payment Amount (Rs.) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text font-weight-bold bg-white">Rs.</span>
                            <input type="number" step="0.01" min="0.01" name="amount" id="qp_amount" class="form-control form-control-sm font-monospace font-weight-bold" required style="border-radius: 0 6px 6px 0;">
                        </div>
                        <div class="form-text small text-muted" style="font-size: 11px;">
                            Supports partial payment. Amount cannot exceed outstanding balance.
                        </div>
                    </div>

                    {{-- Date --}}
                    <div class="mb-3">
                        <label class="form-label font-weight-bold small text-dark">
                            <i class="fas fa-calendar-alt text-info me-1"></i> Payment Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date" id="qp_date" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required style="border-radius: 6px;">
                    </div>

                    {{-- Note --}}
                    <div class="mb-2">
                        <label class="form-label font-weight-bold small text-dark">
                            <i class="fas fa-sticky-note text-secondary me-1"></i> Remarks / Note
                        </label>
                        <input type="text" name="note" id="qp_note" placeholder="e.g. Paid via Cash / Cheque #..." class="form-control form-control-sm" style="border-radius: 6px;">
                    </div>
                </div>
                <div class="modal-footer bg-white border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold" data-bs-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                    <button type="submit" id="btnSubmitQuickPay" class="btn btn-sm btn-success font-weight-bold px-3" style="border-radius: 6px;">
                        <i class="fas fa-check-circle me-1"></i> Confirm & Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable sorted DESC on Column 0 (ID) - Newest on Top
        var table = $('#purchase-table').DataTable({
            "pageLength": 15,
            "order": [[0, 'desc']], // ✅ Newest purchases on top (DESC)
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search by invoice, vendor, branch...",
                "lengthMenu": "Show _MENU_ records",
                "paginate": {
                    "previous": "<i class='fas fa-chevron-left'></i>",
                    "next": "<i class='fas fa-chevron-right'></i>"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": [{{ $showBranchColumn ? 10 : 9 }}] } // Disable ordering on Action column
            ]
        });

        // Quick Filter Buttons
        $('.pur-pill').on('click', function() {
            $('.pur-pill').removeClass('active');
            $(this).addClass('active');

            var filter = $(this).data('filter');

            // Reset custom search filters
            $.fn.dataTable.ext.search.pop();

            if (filter === 'all') {
                table.draw();
            } else if (filter === 'paid') {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var row = table.row(dataIndex).node();
                    return $(row).attr('data-pay-status') === 'paid';
                });
                table.draw();
            } else if (filter === 'due') {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var row = table.row(dataIndex).node();
                    var pay = $(row).attr('data-pay-status');
                    return pay === 'unpaid' || pay === 'partial';
                });
                table.draw();
            } else if (filter === 'pending_del') {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var row = table.row(dataIndex).node();
                    return $(row).attr('data-rec-status') === 'pending';
                });
                table.draw();
            } else if (filter === 'received') {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var row = table.row(dataIndex).node();
                    return $(row).attr('data-rec-status') === 'received';
                });
                table.draw();
            }
        });

        // Quick Pay Modal Trigger
        $(document).on('click', '.btn-quick-pay', function() {
            var btn = $(this);
            var id = btn.data('id');
            var invoice = btn.data('invoice');
            var supplier = btn.data('supplier');
            var due = parseFloat(btn.data('due')) || 0;

            $('#qp_purchase_id').val(id);
            $('#qp_invoice_display').text('Invoice: ' + invoice);
            $('#qp_supplier_display').text(supplier);
            $('#qp_due_display').text('Rs. ' + due.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#qp_amount').val(due).attr('max', due);
            $('#quickPayForm').attr('action', '/purchase/' + id + '/pay');

            var modal = new bootstrap.Modal(document.getElementById('quickPayModal'));
            modal.show();
        });

        // Quick Pay Submit Form via AJAX
        $('#quickPayForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#btnSubmitQuickPay');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    var modalEl = document.getElementById('quickPayModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Recorded!',
                        text: res.success || 'Payment recorded successfully.',
                        timer: 1600,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Confirm & Save Payment');
                    var err = 'Failed to record payment.';
                    if (xhr.responseJSON && xhr.responseJSON.error) err = xhr.responseJSON.error;
                    else if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                    Swal.fire('Error', err, 'error');
                }
            });
        });

        // Hover & Click interaction for dropdown
        $(document).on('mouseenter', '.manage-dropdown', function() {
            $('.dropdown-menu').removeClass('show');
            $(this).find('.dropdown-menu').addClass('show');
        }).on('mouseleave', '.manage-dropdown', function() {
            $(this).find('.dropdown-menu').removeClass('show');
        });
    });
</script>

@else
    <div class="container py-5 text-center">
        <div class="alert alert-danger shadow">Access Denied: You do not have 'purchase.view' permission.</div>
    </div>
@endcan
@endsection

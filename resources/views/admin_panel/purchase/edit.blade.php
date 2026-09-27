@extends('admin_panel.layout.app')

@section('content')
@can('purchase.edit')
<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #059669;
        --coa-border: #cbd5e1;
    }

    .main-content { background-color: #f8fafc; min-height: 100vh; padding: 10px 0 40px 0; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    
    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.04em;
        margin-bottom: 5px;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }

    .fi {
        width: 100%;
        height: 38px;
        padding: 6px 12px;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13px;
        transition: all 0.2s;
        background-color: #ffffff;
    }

    .fi:focus {
        outline: none;
        border-color: var(--coa-navy);
        box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
    }

    .fi[readonly] {
        background-color: #f1f5f9;
        color: #475569;
    }

    /* Excel Grid Theme */
    .excel-grid-wrapper {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        background: #ffffff;
    }

    #itemsTable {
        border-collapse: collapse !important;
        width: 100%;
        margin-bottom: 0 !important;
    }

    #itemsTable thead th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 8px 6px !important;
        border: 1px solid #1e3a5f !important;
        vertical-align: middle;
        white-space: nowrap;
    }

    #itemsTable tbody td {
        padding: 4px 5px !important;
        vertical-align: middle;
        border: 1px solid #cbd5e1 !important;
        background: #ffffff;
    }

    #itemsTable tbody tr:hover td {
        background-color: #f8fafc;
    }

    /* Excel Cell Inputs */
    #itemsTable .fi {
        height: 32px !important;
        padding: 3px 6px !important;
        font-size: 12.5px !important;
        border-radius: 4px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff;
        box-shadow: none;
    }

    #itemsTable .fi:focus {
        outline: none !important;
        border: 2px solid #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2) !important;
        background-color: #ffffff !important;
    }

    #itemsTable .fi[readonly] {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border-color: #e2e8f0 !important;
        font-weight: 600;
    }

    .summary-card {
        background: #ffffff;
        border-radius: 9px;
        padding: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .summary-label {
        font-size: 12.5px;
        font-weight: 700;
        color: #64748b;
    }

    .summary-value {
        font-weight: 800;
        color: var(--coa-navy-dark);
        font-size: 14px;
        font-family: monospace;
    }

    .summary-total {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 2px dashed #cbd5e1;
    }

    .summary-total .summary-value {
        font-size: 18px;
        color: #047857;
    }

    .btn-submit-pur {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white;
        border: none;
        border-radius: 7px;
        padding: 11px 20px;
        font-weight: 800;
        font-size: 13.5px;
        width: 100%;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        transition: all 0.2s;
    }

    .btn-submit-pur:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35);
        color: #ffffff;
    }

    .payment-badge {
        padding: 6px 14px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 700;
        font-size: 12px;
        border: 1.5px solid #cbd5e1;
        background: #f8fafc;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .payment-badge.active {
        background: #e0f2fe;
        color: #0369a1;
        border-color: #0284c7;
    }

    /* Select2 Tweaks */
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 10px !important;
        font-size: 12.5px !important;
        background-color: #ffffff !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important;
        line-height: 36px !important;
        padding-left: 0 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }

    #itemsTable .select2-container .select2-selection--single {
        height: 32px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 4px !important;
        background-color: #ffffff !important;
    }

    #itemsTable .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 30px !important;
        padding-left: 6px !important;
        font-size: 12.5px !important;
        font-weight: 600;
        color: #1e293b !important;
    }

    #itemsTable .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 30px !important;
        right: 4px !important;
    }

    #itemsTable .select2-container--focus .select2-selection--single,
    #itemsTable .select2-container--open .select2-selection--single {
        border: 2px solid #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2) !important;
    }

    #itemsTable .input-group-text {
        height: 32px !important;
        padding: 0 6px !important;
        font-size: 11px !important;
        font-weight: 700;
        border-color: #cbd5e1 !important;
        background-color: #f8fafc !important;
    }

    #itemsTable .disc-type-toggle {
        height: 32px !important;
        padding: 0 6px !important;
        font-size: 11px !important;
        font-weight: 700;
        border-color: #cbd5e1 !important;
        background-color: #f8fafc !important;
    }

    .remove-row {
        width: 28px;
        height: 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: all 0.15s ease;
    }

    .remove-row:hover {
        background-color: #fee2e2;
        color: #dc2626;
        border-color: #fca5a5;
    }
</style>

<div class="main-content">
    <div class="container-fluid px-2">

        {{-- Compact Top Action Bar --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-edit text-primary mr-2"></i>Edit Purchase Invoice #{{ $purchase->invoice_no }}</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('Purchase.home') }}" class="btn btn-sm btn-outline-secondary fw-bold">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Purchases
                </a>
            </div>
        </div>

        <form action="{{ route('purchase.update', $purchase->id) }}" method="POST" id="purchaseForm">
            @csrf
            @method('PUT')
            
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-3 border-0 shadow-sm" role="alert" style="border-radius: 8px;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <div>
                            <strong class="mb-1">Please fix the following errors:</strong>
                            <ul class="mb-0 small pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-warning alert-dismissible fade show mb-3 border-0 shadow-sm" role="alert" style="border-radius: 8px;">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card shadow-sm border-0 mb-3" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3 p-lg-4">

                    <!-- Single Compact Header Row: Vendor, Branch, Warehouse & Date -->
                    <div class="row align-items-end g-2 mb-3">
                        <div class="col-md-3">
                            <label class="f-label mb-1"><i class="fas fa-building mr-1 text-muted"></i> Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" id="vendor_id" class="fi select2" required>
                                <option value="">Select Vendor</option>
                                @foreach($Vendor as $v)
                                    <option value="{{ $v->id }}" {{ $v->id == $purchase->vendor_id ? 'selected' : '' }}>{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- BRANCH (Shown ONLY to Super Admin) --}}
                        @if($isSuperAdmin)
                            <div class="col-md-3">
                                <label class="f-label mb-1"><i class="fas fa-code-branch mr-1 text-muted"></i> Branch <span class="text-danger">*</span></label>
                                <select name="branch_id" id="branch_id" class="fi select2" required>
                                    @foreach($Branch as $b)
                                        <option value="{{ $b->id }}" {{ $b->id == $purchase->branch_id ? 'selected' : '' }}>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="branch_id" value="{{ $purchase->branch_id }}">
                        @endif

                        <div class="{{ $isSuperAdmin ? 'col-md-3' : 'col-md-4' }}">
                            <label class="f-label mb-1" title="Warehouse / Destination"><i class="fas fa-warehouse mr-1 text-muted"></i> Warehouse <span class="text-danger">*</span></label>
                            <select name="warehouse_id" id="warehouse_id" class="fi select2">
                                <option value="">🏢 Direct to Shop</option>
                                @foreach($Warehouse as $w)
                                    <option value="{{ $w->id }}" {{ $w->id == $purchase->warehouse_id ? 'selected' : '' }}>
                                        [{{ $w->branches->first()->name ?? 'Global' }}] - {{ $w->warehouse_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="{{ $isSuperAdmin ? 'col-md-3' : 'col-md-5' }}">
                            <label class="f-label mb-1"><i class="fas fa-calendar-alt mr-1 text-muted"></i> Date <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date" class="fi" value="{{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d') : '' }}" required>
                        </div>
                    </div>

                    <!-- Items Table (Excel Grid) -->
                    <div class="table-responsive mb-1 excel-grid-wrapper">
                        <table class="table table-bordered align-middle mb-0" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 24%;">Product Details <span class="text-danger">*</span></th>
                                    <th style="width: 10%;">Packing Type</th>
                                    <th style="width: 20%; text-align: center;">Packing Details</th>
                                    <th style="width: 9%; text-align: center;">Total Qty <span class="text-danger">*</span></th>
                                    <th style="width: 12%; text-align: right;">Cost Price <span class="text-danger">*</span></th>
                                    <th style="width: 10%;">Disc</th>
                                    <th style="width: 8%; text-align: right;">Disc Amt</th>
                                    <th style="width: 12%; text-align: right;">Line Total</th>
                                    <th style="width: 3%; text-align: center;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsList">
                                @foreach($purchase->items as $item)
                                @php
                                    $product = $item->product;
                                    $unitName = $item->unit ?? ($product->unit->name ?? 'Piece');
                                @endphp
                                <tr class="item-row">
                                    <td>
                                        <select name="product_id[]" class="fi select2 product-select" required>
                                            <option value="">Select Product</option>
                                            @foreach($Products as $p)
                                                <option value="{{ $p->id }}" 
                                                    data-price="{{ $p->last_purchase_price }}" 
                                                    data-unit="{{ $p->unit->name ?? 'unit' }}"
                                                    data-code="{{ $p->item_code }}"
                                                    {{ $p->id == $item->product_id ? 'selected' : '' }}>
                                                    {{ $p->item_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="unit[]" class="unit-input" value="{{ $unitName }}">
                                    </td>
                                    
                                    <!-- PACKING TYPE -->
                                    <td>
                                        <select name="packing_type[]" class="fi packing-type-select">
                                            <option value="Standard" {{ strtolower($item->packing_type ?? 'standard') === 'standard' ? 'selected' : '' }}>Standard</option>
                                            <option value="Customize" {{ strtolower($item->packing_type ?? 'standard') === 'customize' ? 'selected' : '' }}>Customize</option>
                                        </select>
                                    </td>
                                    
                                    <!-- PACKING DETAILS -->
                                    <td>
                                        <!-- Standard View -->
                                        <div class="standard-packing-view text-center" style="{{ strtolower($item->packing_type ?? 'standard') === 'standard' ? '' : 'display: none;' }}">
                                            <input type="text" class="fi text-center" value="{{ $unitName }}" readonly style="background-color: #f1f5f9; font-size: 11.5px;">
                                        </div>
                                        <!-- Customize View -->
                                        <div class="customize-packing-view gap-1 {{ strtolower($item->packing_type ?? 'standard') === 'customize' ? 'd-flex' : '' }}" style="{{ strtolower($item->packing_type ?? 'standard') === 'customize' ? '' : 'display: none;' }}">
                                            <div class="flex-grow-1 text-center" style="width: 33%;">
                                                <div style="font-size: 9.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Packs</div>
                                                <input type="number" name="packing_qty[]" class="fi text-center pack-qty-input" step="1" min="0" value="{{ $item->packing_qty ?? 0 }}" placeholder="Packs">
                                            </div>
                                            <div class="flex-grow-1 text-center" style="width: 33%;">
                                                <div style="font-size: 9.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Pcs/Pk</div>
                                                <input type="number" name="item_per_piece[]" class="fi text-center ipp-input" step="1" min="0" value="{{ $item->item_per_piece ?? 0 }}" placeholder="Pcs/Pack">
                                            </div>
                                            <div class="flex-grow-1 text-center" style="width: 33%;">
                                                <div style="font-size: 9.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Loose</div>
                                                <input type="number" name="loose_piece[]" class="fi text-center loose-pcs-input" step="1" min="0" value="{{ $item->loose_piece ?? 0 }}" placeholder="Loose">
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <input type="number" name="qty[]" class="fi text-center qty-input font-weight-bold" style="font-family: monospace;" value="{{ $item->qty }}" min="1" step="0.01" required {{ strtolower($item->packing_type ?? 'standard') === 'customize' ? 'readonly' : '' }}>
                                    </td>
                                    <td>
                                        <input type="number" name="price[]" class="fi price-input text-end font-weight-bold" style="font-family: monospace;" step="0.01" min="0" value="{{ $item->price }}" required placeholder="0.00">
                                    </td>
                                    <td>
                                        <div class="input-group" style="flex-wrap: nowrap;">
                                            <input type="number" class="fi form-control disc-input-visual text-end" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: 0;" step="0.01" min="0" value="{{ $item->item_discount }}">
                                            <button class="btn btn-outline-secondary disc-type-toggle" type="button" data-type="amount" style="border-top-right-radius: 5px; border-bottom-right-radius: 5px; border: 1.5px solid #cbd5e1; border-left: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold; font-size: 11px; width: 36px; padding: 0;">Rs</button>
                                            <input type="hidden" class="disc-type-input" value="amount">
                                            <input type="hidden" name="item_discount[]" class="disc-input-hidden" value="{{ $item->item_discount }}">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="fi text-end font-weight-bold disc-amt-display" value="{{ number_format($item->item_discount, 2, '.', '') }}" readonly style="background:#f8fafc; color:#64748b; font-family: monospace;">
                                    </td>
                                    <td class="text-end">
                                        <input type="text" class="fi text-end font-weight-bold line-total text-success" value="{{ number_format($item->line_total, 2, '.', '') }}" readonly style="background:#f0fdf4; font-family: monospace;">
                                    </td>
                                    
                                    <input type="hidden" name="line_warehouse_id[]" class="line-warehouse-input" value="{{ $item->warehouse_id }}">
                                    
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row" style="padding: 2px 6px; border-radius: 5px;"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted d-block mb-3" style="font-size: 11.5px;">
                        <i class="fas fa-keyboard text-primary mr-1"></i> <strong>Excel Auto-Row:</strong> Press <strong>Enter</strong> key anywhere in the row to automatically add a new line.
                    </small>on>

                    <!-- Footer Section -->
                    <div class="row g-4">
                        <!-- Left: Notes & Payment -->
                        <div class="col-lg-7">
                            <label class="section-label">Additional Notes</label>
                            <textarea name="note" class="fi mb-4" rows="3" placeholder="Enter any internal remarks or terms...">{{ $purchase->note }}</textarea>

                            <label class="section-label">Payment Information</label>
                            <div class="d-flex gap-3 mb-4">
                                <label class="payment-badge {{ $purchase->paid_amount > 0 ? '' : 'active' }}" id="badgeLater">
                                    <input type="radio" name="payment_type" value="pay_later" class="d-none" {{ $purchase->paid_amount > 0 ? '' : 'checked' }}> 💳 Pay Later (Credit)
                                </label>
                                <label class="payment-badge {{ $purchase->paid_amount > 0 ? 'active' : '' }}" id="badgeNow">
                                    <input type="radio" name="payment_type" value="pay_now" class="d-none" {{ $purchase->paid_amount > 0 ? 'checked' : '' }}> 💵 Pay Now (Cash/Bank)
                                </label>
                            </div>

                            <div id="paymentFields" style="{{ $purchase->paid_amount > 0 ? '' : 'display: none;' }}" class="bg-light p-4 rounded-4 border border-info border-opacity-25">
                                <label class="section-label mb-3">Payment Accounts & Amounts <span class="text-danger">*</span></label>
                                <div id="rvWrapper">
                                    @if(!empty($prefilledPayments))
                                        @foreach($prefilledPayments as $index => $payment)
                                            <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                                                <div class="flex-grow-1">
                                                    <select class="form-select fi rv-account" name="payment_account_id[]" required>
                                                        <option value="" disabled>Select Source...</option>
                                                        @foreach($bankAccounts as $acc)
                                                            <option value="{{ $acc->id }}" {{ $acc->id == $payment['account_id'] ? 'selected' : '' }}>{{ $acc->title }} ({{ $acc->head->title ?? '' }})</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="account-balance-wrapper mt-1 ms-2" style="display:none; font-size: 0.8rem;">
                                                        <span class="text-muted">Available Balance:</span> 
                                                        <span class="fw-bold text-info balance-amt">0.00</span>
                                                    </div>
                                                </div>
                                                <div style="width: 160px;">
                                                    <input type="number" name="payment_amount[]" class="fi border-info rv-amount text-end" step="0.01" placeholder="0.00" value="{{ $payment['amount'] }}" required>
                                                </div>
                                                <div style="width: 80px;">
                                                    @if($index === 0)
                                                        <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnAddRV" style="height: 48px; border-radius: 0.75rem;">Add</button>
                                                    @else
                                                        <button type="button" class="btn btn-outline-danger btn-sm w-100 btn-remove-rv" style="height: 48px; border-radius: 0.75rem;">Remove</button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                                            <div class="flex-grow-1">
                                                <select class="form-select fi rv-account" name="payment_account_id[]">
                                                    <option value="" disabled selected>Select Source...</option>
                                                    @foreach($bankAccounts as $acc)
                                                        <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->head->title ?? '' }})</option>
                                                    @endforeach
                                                </select>
                                                <div class="account-balance-wrapper mt-1 ms-2" style="display:none; font-size: 0.8rem;">
                                                    <span class="text-muted">Available Balance:</span> 
                                                    <span class="fw-bold text-info balance-amt">0.00</span>
                                                </div>
                                            </div>
                                            <div style="width: 160px;">
                                                <input type="number" name="payment_amount[]" class="fi border-info rv-amount text-end" step="0.01" placeholder="0.00">
                                            </div>
                                            <div style="width: 80px;">
                                                <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnAddRV" style="height: 48px; border-radius: 0.75rem;">Add</button>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="text-end mt-3 pt-2 border-top">
                                        <span class="me-2 text-muted fw-bold">Total Paid:</span>
                                        <span class="fw-bold text-success" id="totalPaidDisplay" style="font-size: 1.2rem;">{{ number_format($purchase->paid_amount, 2, '.', '') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Totals Summary -->
                        <div class="col-lg-5">
                            <div class="summary-card shadow-sm">
                                <div class="summary-row">
                                    <span class="summary-label">Items Subtotal</span>
                                    <span class="summary-value" id="dispSubtotal">0.00</span>
                                    <input type="hidden" name="subtotal" id="subtotal_val" value="{{ $purchase->subtotal }}">
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Additional Discount</span>
                                    <div style="width: 120px;">
                                        <input type="number" name="discount" id="overallDiscount" class="fi text-end py-1" step="0.01" value="{{ $purchase->discount }}">
                                    </div>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Extra Charges (Freight/Misc)</span>
                                    <div style="width: 120px;">
                                        <input type="number" name="extra_cost" id="extraCost" class="fi text-end py-1" step="0.01" value="{{ $purchase->extra_cost }}">
                                    </div>
                                </div>
                                <div class="summary-row summary-total">
                                    <span class="summary-label text-primary">Invoice Net Total</span>
                                    <span class="summary-value" id="dispNet">0.00</span>
                                    <input type="hidden" name="net_amount" id="netAmount" value="{{ $purchase->net_amount }}">
                                </div>
                                <div class="summary-row outstanding-row" style="{{ $purchase->paid_amount > 0 ? '' : 'display: none;' }}">
                                    <span class="summary-label text-danger">Outstanding Balance</span>
                                    <span class="summary-value text-danger" id="dispOutstanding">0.00</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-submit-pur mt-3">
                                <i class="fas fa-check-double mr-2"></i> UPDATE PURCHASE INVOICE
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {

    // --- Select2 Initialization ---
    function initSelect2() {
        if($.fn.select2) {
            $('#vendor_id, #warehouse_id, #branch_id').select2({
                width: '100%'
            });

            $('.product-select').select2({
                placeholder: "Select Product",
                width: '100%',
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') {
                        return data;
                    }
                    if (typeof data.text === 'undefined') {
                        return null;
                    }
                    var term = params.term.toLowerCase();
                    var text = data.text.toLowerCase();
                    var code = $(data.element).data('code') ? $(data.element).data('code').toString().toLowerCase() : '';
                    
                    if (text.indexOf(term) > -1 || code.indexOf(term) > -1) {
                        return data;
                    }
                    return null;
                }
            });
        }
    }
    
    // Initial load
    initSelect2();

    // ===== EXCEL GRID AUTO ROW ADDITION & ENTER KEY NAVIGATION =====
    function addNewRow() {
        var newRow = $('.item-row:first').clone();
        
        // Reset inputs
        newRow.find('input').not('.disc-type-input, .disc-input-hidden, .unit-input, .standard-packing-view input').val(0);
        newRow.find('.qty-input').val(1);
        newRow.find('.price-input').val(0);
        newRow.find('.line-total').val('0.00');
        newRow.find('.disc-amt-display').val('0.00');
        newRow.find('.unit-input').val('Piece');
        newRow.find('.line-warehouse-input').val('');
        
        // Reset product dropdown
        newRow.find('.product-select').val('');

        // Reset packing type to Standard and clear customize inputs
        newRow.find('.packing-type-select').val('Standard');
        newRow.find('.standard-packing-view').show();
        newRow.find('.standard-packing-view input').val('Piece');
        newRow.find('.customize-packing-view').removeClass('d-flex').hide();
        newRow.find('.qty-input').prop('readonly', false).css('background-color', '#fff');
        
        // Reset discount toggle
        const toggleBtn = newRow.find('.disc-type-toggle');
        toggleBtn.attr('data-type', 'amount').text('Rs');
        newRow.find('.disc-type-input').val('amount');
        newRow.find('.disc-input-hidden').val(0);

        newRow.find('.select2-container').remove();
        $('#itemsList').append(newRow);
        initSelect2();
        recalc();

        // Focus & open product select of newly added row
        setTimeout(function() {
            var $newSelect = newRow.find('.product-select');
            if ($.fn.select2) {
                $newSelect.select2('open');
            } else {
                $newSelect.focus();
            }
        }, 100);

        return newRow;
    }

    // Auto focus Qty when Product is selected via Select2
    $(document).on('select2:select', '.product-select', function() {
        var $row = $(this).closest('tr.item-row');
        setTimeout(function() {
            if ($row.find('.packing-type-select').val().toLowerCase() === 'customize') {
                $row.find('.pack-qty-input').focus().select();
            } else {
                $row.find('.qty-input').focus().select();
            }
        }, 100);
    });

    // Handle Enter Key Navigation across cells & auto-add row on last field/row
    $(document).on('keydown', '#itemsTable input, #itemsTable select', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault(); // Prevent default form submit
            var $currentRow = $(this).closest('tr.item-row');
            var isLastRow = $currentRow.is(':last-child');

            if ($(this).hasClass('qty-input')) {
                $currentRow.find('.price-input').focus().select();
            } else if ($(this).hasClass('price-input')) {
                $currentRow.find('.disc-input-visual').focus().select();
            } else if ($(this).hasClass('disc-input-visual') || $(this).hasClass('packing-type-select') || $(this).hasClass('pack-qty-input') || $(this).hasClass('ipp-input') || $(this).hasClass('loose-pcs-input')) {
                if (isLastRow) {
                    addNewRow();
                } else {
                    var $nextRow = $currentRow.next('tr.item-row');
                    var $nextSelect = $nextRow.find('.product-select');
                    if ($nextSelect.length && $.fn.select2) {
                        $nextSelect.select2('open');
                    } else {
                        $nextRow.find('.qty-input').focus().select();
                    }
                }
            } else {
                if (isLastRow) {
                    addNewRow();
                } else {
                    var $nextRow = $currentRow.next('tr.item-row');
                    $nextRow.find('.qty-input').focus().select();
                }
            }
        }
    });

    // Remove Row
    $(document).on('click', '.remove-row', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            recalc();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Cannot delete!',
                text: 'Invoice mein kam se kam ek product hona zarori hai.'
            });
        }
    });

    // Product Selection -> Auto Price
    $(document).on('change', '.product-select', function() {
        var price = $(this).find(':selected').data('price') || 0;
        var unit = $(this).find(':selected').data('unit') || 'Piece';
        $(this).closest('tr').find('.price-input').val(price);
        $(this).closest('tr').find('.unit-input').val(unit);
        
        // Also update standard packing view text to the actual unit
        $(this).closest('tr').find('.standard-packing-view input').val(unit);
        recalc();
    });

    // --- Packing Logic ---
    $(document).on('change', '.packing-type-select', function() {
        const row = $(this).closest('tr');
        const type = $(this).val().toLowerCase();
        
        if (type === 'standard') {
            row.find('.standard-packing-view').show();
            row.find('.customize-packing-view').removeClass('d-flex').hide();
            
            // Standard allows direct Qty edit
            row.find('.qty-input').prop('readonly', false).css('background-color', '#fff');
            
            // Clear packing inputs
            row.find('.pack-qty-input, .ipp-input, .loose-pcs-input').val(0);
        } else {
            row.find('.standard-packing-view').hide();
            row.find('.customize-packing-view').addClass('d-flex').show();
            
            // Customize makes Qty readonly (auto-calculated)
            row.find('.qty-input').prop('readonly', true).css('background-color', '#eef2ff');
        }
        recalc();
    });

    $(document).on('input', '.pack-qty-input, .ipp-input, .loose-pcs-input', function() {
        const row = $(this).closest('tr');
        const packingType = row.find('.packing-type-select').val().toLowerCase();
        
        if (packingType === 'customize') {
            const packQty = parseFloat(row.find('.pack-qty-input').val()) || 0;
            const ipp = parseFloat(row.find('.ipp-input').val()) || 0;
            const loose = parseFloat(row.find('.loose-pcs-input').val()) || 0;
            const totalQty = (packQty * ipp) + loose;
            row.find('.qty-input').val(totalQty);
        }
        recalc();
    });

    function recalc() {
        let subtotal = 0;

        $('.item-row').each(function() {
            const qty   = parseFloat($(this).find('.qty-input').val())   || 0;
            const price = parseFloat($(this).find('.price-input').val()) || 0;
            const discVal = parseFloat($(this).find('.disc-input-visual').val()) || 0;
            const discType = $(this).find('.disc-type-input').val() || 'amount';

            let discAmt = 0;
            if (discType === 'percent') {
                discAmt = (qty * price) * (discVal / 100);
            } else {
                discAmt = discVal;
            }
            
            // Set the calculated Rs discount to hidden field so backend gets correct value
            $(this).find('.disc-input-hidden').val(discAmt.toFixed(2));
            $(this).find('.disc-amt-display').val(discAmt.toFixed(2));

            const lineTotal = (qty * price) - discAmt;
            $(this).find('.line-total').val(lineTotal.toFixed(2));
            subtotal += lineTotal;
        });

        $('#dispSubtotal').text(subtotal.toLocaleString('en-PK', {minimumFractionDigits: 2}));
        $('#subtotal_val').val(subtotal.toFixed(2));

        const overDisc = parseFloat($('#overallDiscount').val()) || 0;
        const extra    = parseFloat($('#extraCost').val())        || 0;
        const net      = (subtotal - overDisc) + extra;

        $('#dispNet').text(net.toLocaleString('en-PK', {minimumFractionDigits: 2}));
        $('#netAmount').val(net.toFixed(2));
        
        // Update the payment amount if paying now
        if ($('input[name="payment_type"][value="pay_now"]').is(':checked')) {
            let firstAmount = $('.rv-amount').first();
            if(!firstAmount.val() || parseFloat(firstAmount.val()) > 0) {
                firstAmount.val(net.toFixed(2));
            }
        }
        
        calcPayments();
    }

    // Run on any price / disc / overhead change
    $(document).on('input', '.qty-input, .price-input, .disc-input-visual, #overallDiscount, #extraCost', recalc);

    // Discount Type Toggle (Rs / %)
    $(document).on('click', '.disc-type-toggle', function() {
        let type = $(this).attr('data-type');
        if(type === 'amount') {
            $(this).attr('data-type', 'percent');
            $(this).text('%');
            $(this).siblings('.disc-type-input').val('percent');
        } else {
            $(this).attr('data-type', 'amount');
            $(this).text('Rs');
            $(this).siblings('.disc-type-input').val('amount');
        }
        // focus back on input for fast typing
        $(this).siblings('.disc-input-visual').focus();
        recalc();
    });

    // Payment Toggles
    $('#badgeLater').click(function() {
        $('input[name="payment_type"][value="pay_later"]').prop('checked', true);
        $(this).addClass('active');
        $('#badgeNow').removeClass('active');
        $('#paymentFields').slideUp();
        $('.outstanding-row').slideUp();
        $('.rv-amount').val('');
        calcPayments();
    });

    $('#badgeNow').click(function() {
        $('input[name="payment_type"][value="pay_now"]').prop('checked', true);
        $(this).addClass('active');
        $('#badgeLater').removeClass('active');
        $('#paymentFields').slideDown();
        $('.outstanding-row').slideDown();
        
        const net = parseFloat($('#netAmount').val()) || 0;
        let firstAmount = $('.rv-amount').first();
        if(!firstAmount.val()) {
            firstAmount.val(net.toFixed(2));
            calcPayments();
        }
        firstAmount.focus();
    });

    // Accounts logic for multiple payments
    window.PAYMENT_ACCOUNTS = @json($bankAccounts);

    function loadAccountsInto($select) {
        const currentVal = $select.val();
        let usedAccounts = [];
        $('.rv-account').each(function() {
            const val = $(this).val();
            if (val && this !== $select[0]) usedAccounts.push(String(val));
        });

        let html = '<option value="" disabled selected>-- Select Account --</option>';
        window.PAYMENT_ACCOUNTS.forEach(function(acc) {
            const accId = String(acc.id);
            if (!usedAccounts.includes(accId) || accId === String(currentVal)) {
                html += `<option value="${accId}">${acc.title} (${acc.account_code})</option>`;
            }
        });
        $select.html(html);
        if (currentVal) $select.val(currentVal);
    }

    $(document).on('change', '.rv-account', function() {
        $('.rv-account').each(function() { loadAccountsInto($(this)); });
        updateBalances();
    });

    function updateBalances() {
        $('.rv-account').each(function() {
            const val = $(this).val();
            const wrapper = $(this).siblings('.account-balance-wrapper');
            if (val) {
                const acc = window.PAYMENT_ACCOUNTS.find(a => String(a.id) === String(val));
                if (acc) {
                    const bal = parseFloat(acc.opening_balance) || 0;
                    wrapper.find('.balance-amt').text(bal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    wrapper.slideDown(150);
                } else { wrapper.hide(); }
            } else { wrapper.hide(); }
        });
    }

    function calcPayments() {
        let totalPaid = 0;
        $('.rv-amount').each(function() { totalPaid += parseFloat($(this).val()) || 0; });
        $('#totalPaidDisplay').text(totalPaid.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        const netAmount = parseFloat($('#netAmount').val()) || 0;
        const outstanding = netAmount - totalPaid;
        $('#dispOutstanding').text(outstanding.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    $(document).on('input', '.rv-amount', calcPayments);

    $('#btnAddRV').click(function() {
        const $row = $('.rv-row').first().clone();
        $row.find('.rv-account').val('');
        $row.find('.rv-amount').val('');
        const $btn = $row.find('button');
        $btn.attr('id', '').removeClass('btn-outline-primary').addClass('btn-outline-danger btn-remove-rv').text('Remove');
        const netAmount = parseFloat($('#netAmount').val()) || 0;
        let currentPaid = 0;
        $('.rv-amount').each(function() { currentPaid += parseFloat($(this).val()) || 0; });
        const remaining = Math.max(0, netAmount - currentPaid);
        if (remaining > 0) $row.find('.rv-amount').val(remaining.toFixed(2));
        $row.insertBefore($('#totalPaidDisplay').closest('.text-end'));
        $('.rv-account').each(function() { loadAccountsInto($(this)); });
        calcPayments();
        updateBalances();
    });

    $(document).on('click', '.btn-remove-rv', function() {
        $(this).closest('.rv-row').remove();
        $('.rv-account').each(function() { loadAccountsInto($(this)); });
        calcPayments();
        updateBalances();
    });

    // Dynamic Warehouse Loading by Branch
    $('#branch_id').on('change', function() {
        const branchId = $(this).val();
        if (!branchId) return;

        const $warehouseSelect = $('#warehouse_id');
        $warehouseSelect.prop('disabled', true);
        
        $.ajax({
            url: "{{ route('warehouses-by-branch') }}",
            type: "GET",
            data: { branch_id: branchId },
            success: function(res) {
                let html = '<option value="">🏢 Direct to Shop (Branch Display)</option>';
                if (res && res.length > 0) {
                    res.forEach(function(w) {
                        html += `<option value="${w.id}">🏢 ${w.warehouse_name}</option>`;
                    });
                }
                $warehouseSelect.html(html).prop('disabled', false).trigger('change');
            },
            error: function() {
                $warehouseSelect.prop('disabled', false);
            }
        });
    });

    // Initialize accounts dropdown and balances
    $('.rv-account').each(function() { loadAccountsInto($(this)); });
    updateBalances();
    calcPayments();

    // Trigger initial calculation
    recalc();

    // Prevent enter key submission in form inputs globally outside row auto addition
    $('#purchaseForm').on('keydown', 'input', function(e) {
        if(e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
        }
    });

    // Validation on submit
    $('#purchaseForm').on('submit', function(e) {
        let emptyPrice = false;
        $('.price-input').each(function() {
            if(parseFloat($(this).val()) < 0) emptyPrice = true;
        });
        if(emptyPrice) {
            e.preventDefault();
            Swal.fire({ 
                icon: 'warning', 
                title: 'Check Prices!', 
                text: 'Kuch items ka cost price invalid hai. Please check karein.' 
            });
        }
    });
});
</script>
@endsection
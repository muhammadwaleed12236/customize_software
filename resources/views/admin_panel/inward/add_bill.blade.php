@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --inw-navy:       #0f172a;
        --inw-navy-mid:   #1e293b;
        --inw-blue:       #2563eb;
        --inw-blue-light: #3b82f6;
        --inw-border:     #e2e8f0;
        --inw-bg:         #f1f5f9;
        --inw-muted:      #64748b;
    }

    .osm-wrap { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background: var(--inw-bg); 
        min-height: 100vh; 
        padding: 16px 20px 50px; 
    }

    /* Top Navy Banner */
    .inw-banner {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #2c5282 100%);
        border-radius: 12px; 
        padding: 14px 22px;
        display: flex; 
        align-items: center; 
        justify-content: space-between; 
        gap: 12px;
        margin-bottom: 14px; 
        box-shadow: 0 4px 18px rgba(15,23,42,.18);
    }
    .inw-banner-left { display: flex; align-items: center; gap: 12px; }
    .inw-banner-icon {
        width: 40px; height: 40px; border-radius: 10px;
        background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; color: #fcd34d; flex-shrink: 0;
    }
    .inw-banner-title { font-size: 18px; font-weight: 800; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px; }
    .inw-banner-badge {
        background: rgba(252,211,77,.15); border: 1px solid rgba(252,211,77,.4);
        color: #fcd34d; font-size: 10px; font-weight: 800;
        padding: 2px 9px; border-radius: 20px; text-transform: uppercase;
    }
    .inw-banner-sub { font-size: 11.5px; color: rgba(255,255,255,.65); margin-top: 2px; }
    .inw-banner-actions { display: flex; align-items: center; gap: 8px; }
    .btn-inw-outline {
        background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
        color: #fff !important; padding: 7px 14px; border-radius: 7px;
        font-size: 12px; font-weight: 700; text-decoration: none !important;
        display: inline-flex; align-items: center; gap: 5px; transition: .2s;
    }
    .btn-inw-outline:hover { background: rgba(255,255,255,.2); }
    .btn-inw-submit {
        background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;
        color: #fff !important; padding: 8px 22px; border-radius: 7px;
        font-size: 12.5px; font-weight: 800; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        box-shadow: 0 3px 12px rgba(37,99,235,.35); transition: .2s;
    }
    .btn-inw-submit:hover { transform: translateY(-1px); box-shadow: 0 5px 16px rgba(37,99,235,.45); }

    /* Compact Fields Card */
    .inw-fields-card {
        background: #fff; border-radius: 10px;
        border: 1px solid var(--inw-border);
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        padding: 16px 20px; margin-bottom: 14px;
    }
    .inw-label {
        font-size: 10px; font-weight: 700; color: var(--inw-muted);
        text-transform: uppercase; letter-spacing: .04em;
        margin-bottom: 5px; display: block;
    }
    
    /* Clean white inputs */
    .fi {
        border: 1.5px solid var(--inw-border); border-radius: 7px;
        padding: 7px 11px; font-size: 12.5px; font-weight: 600;
        width: 100%; transition: .2s; background: #ffffff !important;
        color: #0f172a; outline: none;
    }
    .fi:focus {
        border-color: var(--inw-blue-light); background: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }
    .fi[readonly] {
        background: #f8fafc !important; color: #475569; cursor: not-allowed;
        border-color: #e2e8f0;
    }
    .fi-table {
        padding: 6px 9px !important; font-size: 12px !important;
    }

    /* Table Card */
    .table-card {
        background: #fff; border-radius: 10px;
        border: 1px solid var(--inw-border);
        box-shadow: 0 2px 8px rgba(0,0,0,.04); overflow: hidden;
        margin-bottom: 14px;
    }
    .osm-thead { background: var(--inw-navy-mid); color: #fff; }
    .osm-thead th {
        padding: 10px 12px; font-size: 10.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .04em; border: none; white-space: nowrap;
    }
    .osm-row td { 
        vertical-align: middle; padding: 10px 12px; 
        border-bottom: 1px solid #f1f5f9; background: #fff; 
    }
    .osm-row:hover td { background: #fafcff; }

    /* Summary Card */
    .summary-card { 
        background: #fff; border-radius: 10px; 
        padding: 18px 20px; border: 1px solid var(--inw-border);
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
    }
    .summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; }
    .summary-label { font-weight: 700; color: #64748b; font-size: 12.5px; }
    .summary-value { font-weight: 800; color: #1e293b; font-size: 14px; }
    .summary-total { margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #e2e8f0; }
    .summary-total .summary-value { font-size: 1.4rem; color: #2563eb; }

    /* Payment Badges */
    .payment-badge { 
        padding: 8px 16px; border-radius: 8px; cursor: pointer; 
        transition: all 0.2s; font-weight: 700; font-size: 12.5px;
        border: 1.5px solid var(--inw-border); background: #ffffff; color: #64748b;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .payment-badge:hover { border-color: #cbd5e1; background: #f8fafc; }
    .payment-badge.active { background: #eff6ff; color: #2563eb; border-color: #3b82f6; }
</style>

<div class="osm-wrap">
    <form action="{{ route('store.bill', $gatepass->id) }}" method="POST" id="billForm">
        @csrf
        <input type="hidden" name="branch_id" value="{{ $gatepass->branch_id }}">
        <input type="hidden" name="warehouse_id" value="{{ $gatepass->warehouse_id }}">

        {{-- Top Navy Banner --}}
        <div class="inw-banner">
            <div class="inw-banner-left">
                <div class="inw-banner-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <h4 class="inw-banner-title">
                        Create Purchase Invoice
                        <span class="inw-banner-badge">GRN Ref: #{{ $gatepass->id }}</span>
                    </h4>
                    <div class="inw-banner-sub">
                        Record vendor invoice &amp; generate accounting voucher for Inward Gatepass #{{ $gatepass->id }}
                    </div>
                </div>
            </div>
            <div class="inw-banner-actions">
                <a href="{{ route('InwardGatepass.home') }}" class="btn-inw-outline">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn-inw-submit">
                    <i class="fas fa-check-circle"></i> Post Invoice
                </button>
            </div>
        </div>

        {{-- Header Info Card --}}
        <div class="inw-fields-card">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="inw-label"><i class="fas fa-truck-loading me-1"></i> Vendor / Supplier</label>
                    <input type="text" class="fi" value="{{ $gatepass->vendor->name ?? 'N/A' }}" readonly>
                    <input type="hidden" name="vendor_id" value="{{ $gatepass->vendor_id }}">
                </div>
                <div class="col-md-3">
                    <label class="inw-label"><i class="fas fa-warehouse me-1"></i> Warehouse (Header)</label>
                    <input type="text" class="fi" value="{{ $gatepass->warehouse->warehouse_name ?? 'N/A' }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="inw-label"><i class="fas fa-code-branch me-1"></i> Branch</label>
                    <input type="text" class="fi" value="{{ $gatepass->branch->name ?? 'N/A' }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="inw-label"><i class="fas fa-calendar-alt me-1 text-primary"></i> Invoice Date <span class="text-danger">*</span></label>
                    <input type="date" name="purchase_date" class="fi" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
        </div>

        {{-- Items Table Card --}}
        <div class="table-card">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="osm-thead">
                        <tr>
                            <th style="width: 22%;">Product Details</th>
                            <th style="width: 11%;">Packing Type</th>
                            <th style="width: 17%; text-align: center;">Packing Details</th>
                            <th style="width: 9%; text-align: center;">Total Qty</th>
                            <th style="width: 13%;">Cost Price (Rs)</th>
                            <th style="width: 11%;">Disc</th>
                            <th style="width: 7%; text-align: right;">Disc Amt</th>
                            <th style="width: 10%; text-align: right;">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Group items by Product, Packing Type, and Unit to prevent repetition in UI
                            $groupedItems = $gatepass->items->groupBy(function($item) {
                                return $item->product_id . '_' . ($item->packing_type ?? 'Standard') . '_' . ($item->unit ?? 'Piece');
                            });
                        @endphp

                        @foreach($groupedItems as $groupKey => $items)
                        @php
                            $first = $items->first();
                            $totalQty = $items->sum('qty');
                            $product = $first->product;
                            $groupId = "group_" . $loop->index;
                        @endphp
                        <tr class="item-group-row osm-row" data-group-id="{{ $groupId }}">
                            <td>
                                <div class="fw-bold text-dark mb-1" style="font-size: 13.5px; line-height: 1.3;">{{ $product->item_name }}</div>
                                <div class="d-flex align-items-center flex-wrap gap-1 mb-1 mt-1">
                                    <span class="badge bg-white text-muted border px-2 py-1" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">
                                        <i class="fas fa-barcode me-1"></i>{{ $product->item_code }}
                                    </span>
                                    @if($product->brand)
                                        <span class="badge text-primary px-2 py-1" style="background: #eef2ff; font-size: 10px; font-weight: 800; border: 1px solid #c7d2fe; text-transform: uppercase;">
                                            <i class="fas fa-tag me-1"></i>{{ $product->brand->name }}
                                        </span>
                                    @endif
                                    @if($product->model)
                                        <span class="badge text-secondary px-2 py-1" style="background: #f8fafc; font-size: 10px; font-weight: 700; border: 1px solid #e2e8f0;">
                                            <i class="fas fa-cube me-1"></i>{{ $product->model }}
                                        </span>
                                    @endif
                                </div>
                                
                                <!-- Color Breakdown Badges -->
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($items as $subItem)
                                        @if($subItem->color)
                                            <span class="badge rounded-pill" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-size: 10.5px; padding: 3px 8px;">
                                                <i class="fas fa-circle me-1" style="font-size: 8px; color: {{ strtolower($subItem->color) == 'white' ? '#cbd5e1' : strtolower($subItem->color) }}"></i>
                                                {{ $subItem->color }}: <strong>{{ $subItem->qty }}</strong>
                                            </span>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Hidden Inputs for Backend (One set per original item) -->
                                @foreach($items as $subItem)
                                    <div class="hidden-item-inputs" data-item-id="{{ $subItem->id }}">
                                        <input type="hidden" name="product_id[]" value="{{ $subItem->product_id }}">
                                        <input type="hidden" name="color[]" value="{{ $subItem->color }}">
                                        <input type="hidden" name="unit[]" value="{{ $subItem->unit ?? ($product->unit->name ?? 'Piece') }}">
                                        <input type="hidden" name="qty[]" class="sub-item-qty" value="{{ $subItem->qty }}">
                                        <input type="hidden" name="packing_type[]" value="{{ $subItem->packing_type }}">
                                        <input type="hidden" name="packing_qty[]" value="{{ $subItem->packing_qty }}">
                                        <input type="hidden" name="item_per_piece[]" value="{{ $subItem->item_per_piece }}">
                                        <input type="hidden" name="loose_piece[]" value="{{ $subItem->loose_piece }}">
                                        <input type="hidden" name="price[]" class="sub-item-price-hidden" value="{{ $first->last_purchase_price }}">
                                        <input type="hidden" name="item_discount[]" class="sub-item-disc-hidden" value="0">
                                    </div>
                                @endforeach
                            </td>
                            
                            <td>
                                <span class="badge {{ strtolower($first->packing_type ?? 'standard') === 'standard' ? 'bg-light text-primary border' : 'bg-warning bg-opacity-10 text-dark border border-warning' }} px-2 py-1" style="font-size: 11px;">
                                    {{ $first->packing_type ?? 'Standard' }}
                                </span>
                            </td>
                            
                            <td class="text-center">
                                @if(strtolower($first->packing_type ?? 'standard') === 'customize')
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <div class="text-center">
                                            <div class="text-muted" style="font-size: 9px; font-weight: 700;">PACKS</div>
                                            <div class="fw-bold" style="font-size: 12px;">{{ $first->packing_qty ?? 0 }}</div>
                                        </div>
                                        <div class="vr mx-1" style="height: 20px;"></div>
                                        <div class="text-center">
                                            <div class="text-muted" style="font-size: 9px; font-weight: 700;">PCS/PK</div>
                                            <div class="fw-bold" style="font-size: 12px;">{{ $first->item_per_piece ?? 0 }}</div>
                                        </div>
                                        <div class="vr mx-1" style="height: 20px;"></div>
                                        <div class="text-center">
                                            <div class="text-muted" style="font-size: 9px; font-weight: 700;">LOOSE</div>
                                            <div class="fw-bold" style="font-size: 12px;">{{ $first->loose_piece ?? 0 }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size: 11.5px;">Standard ({{ $first->unit ?? 'Piece' }})</span>
                                @endif
                            </td>
                            
                            <td class="text-center">
                                <div class="fw-bold text-primary" style="font-size: 14px;">{{ $totalQty }}</div>
                                <div class="text-muted text-uppercase" style="font-size: 9.5px; font-weight: 700;">{{ $first->unit ?? 'Piece' }}s</div>
                                <input type="hidden" class="group-total-qty" value="{{ $totalQty }}">
                            </td>
                            
                            <td>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 px-2 text-muted" style="border-color: var(--inw-border);"><i class="fas fa-tag text-primary"></i></span>
                                    <input type="number" class="fi fi-table master-price-input border-start-0" style="border-left: none; background:#ffffff !important; font-weight: 700;" step="0.01" min="0" value="{{ $first->last_purchase_price }}" required>
                                </div>
                            </td>

                            <td>
                                <div class="input-group input-group-sm" style="flex-wrap: nowrap;">
                                    <input type="number" class="fi fi-table master-disc-input-visual" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: 0;" step="0.01" min="0" value="0">
                                    <button class="btn btn-outline-secondary disc-type-toggle px-2" type="button" data-type="amount" style="border-top-right-radius: 7px; border-bottom-right-radius: 7px; border: 1.5px solid var(--inw-border); border-left: 1px solid #cbd5e1; background: #ffffff; font-weight: bold; width: 42px; color: var(--inw-blue); font-size: 11px;">Rs</button>
                                    <input type="hidden" class="master-disc-type-input" value="amount">
                                </div>
                            </td>

                            <td class="text-end">
                                <input type="text" class="fi fi-table text-end fw-bold group-disc-amt-display" value="0.00" readonly style="background:#f8fafc !important; color:#64748b; font-size:12px;">
                            </td>

                            <td class="text-end">
                                <input type="text" class="fi fi-table text-end fw-bold group-line-total" value="0.00" readonly style="background:#f0fdf4 !important; color:#15803d; font-size:13px; font-weight: 800;">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer Section: Notes & Payments / Totals --}}
        <div class="row g-3">
            {{-- Left: Notes & Payment Setup --}}
            <div class="col-lg-7">
                <div class="inw-fields-card h-100">
                    <div class="mb-3">
                        <label class="inw-label"><i class="fas fa-sticky-note me-1"></i> Additional Notes / Remarks</label>
                        <textarea name="note" class="fi" rows="2" placeholder="Enter any internal remarks or terms..."></textarea>
                    </div>

                    <div class="pt-2 border-top">
                        <label class="inw-label mb-2"><i class="fas fa-wallet me-1"></i> Payment Mode</label>
                        <div class="d-flex gap-2 mb-3">
                            <label class="payment-badge active" id="badgeLater">
                                <input type="radio" name="payment_type" value="pay_later" class="d-none" checked> 
                                <i class="fas fa-clock text-warning"></i> Pay Later (Credit)
                            </label>
                            <label class="payment-badge" id="badgeNow">
                                <input type="radio" name="payment_type" value="pay_now" class="d-none"> 
                                <i class="fas fa-money-bill-wave text-success"></i> Pay Now (Cash/Bank)
                            </label>
                        </div>

                        <div id="paymentFields" style="display: none;" class="p-3 rounded-3 border bg-light">
                            <label class="inw-label mb-2 text-primary"><i class="fas fa-university me-1"></i> Payment Accounts &amp; Amounts <span class="text-danger">*</span></label>
                            <div id="rvWrapper">
                                <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                                    <div class="flex-grow-1">
                                        <select class="form-select fi rv-account" name="payment_account_id[]" style="height: 38px;">
                                            <option value="" disabled selected>-- Select Payment Account --</option>
                                            @foreach($bankAccounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->head->name ?? $acc->head->title ?? '' }})</option>
                                            @endforeach
                                        </select>
                                        <div class="account-balance-wrapper mt-1 ms-1" style="display:none; font-size: 11px;">
                                            <span class="text-muted">Available Balance:</span> 
                                            <span class="fw-bold text-primary balance-amt">0.00</span>
                                        </div>
                                    </div>
                                    <div style="width: 160px;">
                                        <input type="number" name="payment_amount[]" class="fi rv-amount text-end" step="0.01" placeholder="0.00" style="height: 38px;">
                                    </div>
                                    <div style="width: 75px;">
                                        <button type="button" class="btn btn-outline-primary btn-sm w-100 fw-bold" id="btnAddRV" style="height: 38px; border-radius: 7px;">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>
                                </div>
                                <div class="text-end mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-bold" style="font-size: 12px;">Total Paid Amount:</span>
                                    <span class="fw-bold text-success fs-6" id="totalPaidDisplay">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Totals Summary Card --}}
            <div class="col-lg-5">
                <div class="summary-card">
                    <h6 class="fw-bold text-dark mb-3" style="font-size: 13px;">
                        <i class="fas fa-calculator text-primary me-1"></i> Invoice Summary
                    </h6>

                    <div class="summary-row">
                        <span class="summary-label">Items Subtotal</span>
                        <span class="summary-value" id="dispSubtotal">0.00</span>
                    </div>

                    <div class="summary-row">
                        <span class="summary-label">Additional Discount</span>
                        <div style="width: 130px;">
                            <input type="number" name="discount" id="overallDiscount" class="fi text-end py-1" step="0.01" value="0">
                        </div>
                    </div>

                    <div class="summary-row">
                        <span class="summary-label">Freight / Misc Charges</span>
                        <div style="width: 130px;">
                            <input type="number" name="extra_cost" id="extraCost" class="fi text-end py-1" step="0.01" value="0">
                        </div>
                    </div>

                    <div class="summary-row summary-total">
                        <span class="summary-label text-primary fs-6">Invoice Net Total</span>
                        <span class="summary-value" id="dispNet">0.00</span>
                        <input type="hidden" name="net_amount" id="netAmount" value="0">
                    </div>

                    <div class="summary-row outstanding-row mt-2 pt-2 border-top" style="display: none;">
                        <span class="summary-label text-danger">Outstanding Balance</span>
                        <span class="summary-value text-danger" id="dispOutstanding">0.00</span>
                    </div>

                    <button type="submit" class="btn-inw-submit w-100 justify-content-center py-2 mt-4" style="font-size: 13.5px;">
                        <i class="fas fa-check-circle"></i> Complete &amp; Post Invoice
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {

    // --- Recalculation Logic (Grouped) ---
    function recalc() {
        let subtotal = 0;

        $('.item-group-row').each(function() {
            const $groupRow = $(this);
            const masterPrice = parseFloat($groupRow.find('.master-price-input').val()) || 0;
            const masterDiscVal = parseFloat($groupRow.find('.master-disc-input-visual').val()) || 0;
            const masterDiscType = $groupRow.find('.master-disc-type-input').val() || 'amount';
            const totalGroupQty = parseFloat($groupRow.find('.group-total-qty').val()) || 0;

            let groupDiscAmt = 0;
            if (masterDiscType === 'percent') {
                groupDiscAmt = (totalGroupQty * masterPrice) * (masterDiscVal / 100);
            } else {
                groupDiscAmt = masterDiscVal;
            }

            // Sync values to sub-items
            const $subItems = $groupRow.find('.hidden-item-inputs');
            
            $subItems.each(function() {
                const $sub = $(this);
                const subQty = parseFloat($sub.find('.sub-item-qty').val()) || 0;
                
                // Set price for this sub-item
                $sub.find('.sub-item-price-hidden').val(masterPrice.toFixed(2));
                
                // Distribute discount proportionally by quantity if it's a fixed amount, 
                // or just use percent if it's percent.
                let subDiscAmt = 0;
                if (masterDiscType === 'percent') {
                    subDiscAmt = (subQty * masterPrice) * (masterDiscVal / 100);
                } else {
                    if (totalGroupQty > 0) {
                        subDiscAmt = (subQty / totalGroupQty) * masterDiscVal;
                    }
                }
                $sub.find('.sub-item-disc-hidden').val(subDiscAmt.toFixed(4));
            });

            // Update UI for the group
            $groupRow.find('.group-disc-amt-display').val(groupDiscAmt.toFixed(2));
            
            const groupLineTotal = (totalGroupQty * masterPrice) - groupDiscAmt;
            $groupRow.find('.group-line-total').val(groupLineTotal.toFixed(2));
            
            subtotal += groupLineTotal;
        });

        $('#dispSubtotal').text(subtotal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        const overDisc = parseFloat($('#overallDiscount').val()) || 0;
        const extra    = parseFloat($('#extraCost').val())        || 0;
        const net      = (subtotal - overDisc) + extra;

        $('#dispNet').text(net.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#netAmount').val(net.toFixed(2));
        calcPayments();
    }

    // Run on any price / disc / overhead change
    $(document).on('input', '.master-price-input, .master-disc-input-visual, #overallDiscount, #extraCost', recalc);

    // Discount Type Toggle (Rs / %)
    $(document).on('click', '.disc-type-toggle', function() {
        let type = $(this).attr('data-type');
        const $groupRow = $(this).closest('.item-group-row');
        
        if(type === 'amount') {
            $(this).attr('data-type', 'percent');
            $(this).text('%');
            $groupRow.find('.master-disc-type-input').val('percent');
        } else {
            $(this).attr('data-type', 'amount');
            $(this).text('Rs');
            $groupRow.find('.master-disc-type-input').val('amount');
        }
        $groupRow.find('.master-disc-input-visual').focus();
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

        let html = '<option value="" disabled selected>-- Select Payment Account --</option>';
        window.PAYMENT_ACCOUNTS.forEach(function(acc) {
            const accId = String(acc.id);
            if (!usedAccounts.includes(accId) || accId === String(currentVal)) {
                html += `<option value="${accId}">${acc.title} (${acc.account_code || ''})</option>`;
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
        $btn.attr('id', '').removeClass('btn-outline-primary').addClass('btn-outline-danger btn-remove-rv').html('<i class="fas fa-trash-alt"></i>');
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

    recalc();

    $('#billForm').on('submit', function(e) {
        let emptyPrice = false;
        $('.master-price-input').each(function() {
            if(parseFloat($(this).val()) <= 0) emptyPrice = true;
        });
        if(emptyPrice) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Check Prices!', text: 'Kuch items ka cost price 0 ya khaali hai. Please check karein.' });
            } else {
                alert('Warning: Kuch items ka cost price 0 ya khaali hai. Please check karein.');
            }
        }
    });
});
</script>
@endsection

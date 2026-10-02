@extends('admin_panel.layout.app')

@section('content')
<style>
    /* ==========================================================================
       OUTWARD GATEPASS CREATION - COMPACT CORPORATE ERP UI (#1e3a5f & #c8973a)
       ========================================================================== */
    :root {
        --navy-main: #1e3a5f;
        --navy-light: #2c5282;
        --navy-bg: #eef2f7;
        --gold-main: #c8973a;
        --gold-hover: #b3822a;
        --slate-dark: #0f172a;
        --slate-muted: #64748b;
        --border-color: #cbd5e1;
        --card-bg: #ffffff;
    }

    .gp-wrapper {
        max-width: 1360px;
        margin: 0 auto;
        padding: 4px 10px 25px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Compact Top Header Bar */
    .gp-compact-bar {
        background: linear-gradient(135deg, #1e3a5f 0%, #0f2744 100%);
        border-radius: 8px;
        padding: 10px 18px;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .gp-compact-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #ffffff !important;
    }
    .gp-compact-title i {
        color: #fbbf24 !important;
    }
    .gp-meta-tag {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #ffffff;
    }

    /* Ultra Compact Form Cards */
    .gp-card {
        background: var(--card-bg);
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        margin-bottom: 10px;
        overflow: hidden;
    }
    .gp-card-header {
        background: #f1f5f9;
        border-bottom: 1px solid #cbd5e1;
        padding: 6px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .gp-card-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--navy-main);
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .gp-card-title i {
        color: var(--gold-main);
    }
    .gp-card-body {
        padding: 10px 14px;
    }

    /* Dense Form Inputs */
    .gp-label {
        font-size: 0.74rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 2px;
        display: block;
        white-space: nowrap;
    }
    .gp-control {
        width: 100%;
        height: 30px;
        padding: 2px 8px;
        font-size: 0.82rem;
        font-weight: 500;
        color: #0f172a;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .gp-control:focus {
        border-color: var(--navy-main);
        box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.18);
        outline: none;
    }
    .gp-control[readonly] {
        background-color: #f8fafc;
        color: #334155;
        font-weight: 600;
    }
    .gp-textarea-compact {
        height: 52px;
        resize: vertical;
        padding: 4px 8px;
        font-size: 0.82rem;
    }

    /* Compact High-Density Grid Table */
    .gp-table-wrap {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        overflow: hidden;
        background: #fff;
    }
    .gp-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .gp-table thead th {
        background: var(--navy-main);
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 7px 10px;
        border: none;
        vertical-align: middle;
    }
    .gp-table tbody td {
        padding: 4px 8px;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.82rem;
    }
    .gp-table tbody tr:last-child td {
        border-bottom: none;
    }
    .gp-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Product Autocomplete Dropdown */
    .searchWrap { position: relative; }
    .searchResults {
        position: absolute;
        top: calc(100% + 2px);
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        max-height: 200px;
        overflow-y: auto;
        z-index: 9999;
        display: none;
    }
    .searchResults .result {
        padding: 6px 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
    }
    .searchResults .result:hover { background: #eef2f7; }
    .searchResults .result-title { font-weight: 600; color: #1e293b; font-size: 0.82rem; }
    .searchResults .result-meta { font-size: 0.74rem; color: #64748b; }

    /* Action Buttons */
    .btn-navy {
        background: var(--navy-main);
        color: #ffffff;
        border: none;
        font-weight: 600;
        font-size: 0.82rem;
        padding: 5px 16px;
        border-radius: 6px;
        transition: background 0.15s ease;
    }
    .btn-navy:hover {
        background: #152b48;
        color: #fff;
    }
    .btn-gold {
        background: var(--gold-main);
        color: #ffffff;
        border: none;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 6px 20px;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(200, 151, 58, 0.3);
        transition: background 0.15s ease;
    }
    .btn-gold:hover {
        background: var(--gold-hover);
        color: #fff;
    }
    .btn-sm-outline {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
    }
    .btn-sm-outline:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .btn-remove-row {
        width: 26px;
        height: 26px;
        padding: 0;
        border-radius: 5px;
        border: 1px solid #fca5a5;
        background: #fef2f2;
        color: #dc2626;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
    }
    .btn-remove-row:hover {
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
    }

    .gp-foot-bar {
        padding: 8px 14px;
        background: #f8fafc;
        border-top: 1px solid #cbd5e1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

<div class="gp-wrapper">

    <!-- Compact Top Bar Header -->
    <div class="gp-compact-bar">
        <div class="d-flex align-items-center gap-3">
            <h1 class="gp-compact-title">
                <i class="fa fa-truck-ramp-box"></i> Outward Gate Pass
            </h1>
            <span class="gp-meta-tag"><i class="fa fa-file-lines me-1"></i> DC: {{ $order->dc_no ?? 'N/A' }}</span>
            <span class="gp-meta-tag"><i class="fa fa-receipt me-1"></i> Inv: {{ $prefillData['invoice_no'] ?? 'N/A' }}</span>
            <span class="gp-meta-tag"><i class="fa fa-location-dot me-1"></i> {{ $prefillData['warehouse_name'] ?? 'N/A' }}</span>
            <span class="gp-meta-tag"><i class="fa fa-user me-1"></i> {{ $prefillData['customer_name'] ?? 'N/A' }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('OutwardGatepass.list') }}" class="btn-sm-outline text-decoration-none">
                <i class="fa fa-list me-1"></i> List
            </a>
            <a href="{{ route('OutwardGatepass.home') }}" class="btn-sm-outline text-decoration-none">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="alert alert-danger py-1 px-3 mb-2 small">
            <strong>❌ Error:</strong> {{ implode(', ', $errors->all()) }}
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success py-1 px-3 mb-2 small">
            <strong>✅ Success:</strong> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-1 px-3 mb-2 small">
            <strong>❌ Error:</strong> {{ session('error') }}
        </div>
    @endif

    <!-- Main Gatepass Form -->
    <form action="{{ route('store.OutwardGatepass') }}" method="POST" id="gatepassForm">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id ?? '' }}">
        <input type="hidden" name="items_text" id="items_text">
        <input type="hidden" name="warehouse_id" value="{{ $order->warehouse_id ?? '' }}">

        <!-- Top Compact Info Card (Grid 4x3) -->
        <div class="gp-card">
            <div class="gp-card-header">
                <h6 class="gp-card-title"><i class="fa fa-circle-info"></i> Gatepass & Logistics Info</h6>
                <span class="badge bg-secondary style-badge" style="font-size:0.7rem;">Quick Entry</span>
            </div>
            <div class="gp-card-body">
                <div class="row g-2">
                    
                    <!-- Row 1 -->
                    <div class="col-md-2">
                        <label class="gp-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="gatepass_date" class="gp-control" value="{{ old('gatepass_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="gp-label">DC No</label>
                        <input type="text" name="dc_no" class="gp-control" value="{{ $order->dc_no ?? old('dc_no') }}" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="gp-label">Invoice No</label>
                        <input type="text" name="invoice_no" class="gp-control" value="{{ old('invoice_no', $prefillData['invoice_no'] ?? '') }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="gp-label">Customer Name</label>
                        <input type="text" name="customer_name" class="gp-control" value="{{ old('customer_name', $prefillData['customer_name'] ?? '') }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="gp-label">Delivery City</label>
                        <input type="text" name="delivery_city" class="gp-control" placeholder="Destination city" value="{{ old('delivery_city', $prefillData['delivery_city'] ?? '') }}">
                    </div>

                    <!-- Row 2 -->
                    <div class="col-md-2">
                        <label class="gp-label">Bilty No</label>
                        <input type="text" name="billty_no" class="gp-control" placeholder="BLT No" value="{{ old('billty_no') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="gp-label">Bilty Date</label>
                        <input type="date" name="billty_date" class="gp-control" value="{{ old('billty_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="gp-label">Bilty Amount (Rs)</label>
                        <input type="number" step="0.01" name="billty_amount" class="gp-control text-end font-monospace" placeholder="0.00" value="{{ old('billty_amount') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="gp-label">Transporter</label>
                        <input type="text" name="transporter" class="gp-control" placeholder="Goods Transport" value="{{ old('transporter') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="gp-label">Issued By</label>
                        <input type="text" name="issued_by" class="gp-control" value="{{ old('issued_by', auth()->user()->name ?? '') }}" readonly>
                    </div>

                    <!-- Row 3 -->
                    <div class="col-md-2">
                        <label class="gp-label">Driver Name</label>
                        <input type="text" name="driver_name" class="gp-control" placeholder="Driver name" value="{{ old('driver_name') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="gp-label">Vehicle Type</label>
                        <input type="text" name="vehicle_type" class="gp-control" placeholder="Truck/Mazda" value="{{ old('vehicle_type') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="gp-label">Vehicle Number</label>
                        <input type="text" name="vehicle_number" class="gp-control text-uppercase" placeholder="LEB-1234" value="{{ old('vehicle_number') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="gp-label text-primary fw-bold"><i class="fa fa-money-bill-transfer me-1"></i> Freight / Rent (Rs)</label>
                        <input type="number" step="0.01" name="transport_rent" class="gp-control text-end font-monospace fw-bold" placeholder="0.00" value="{{ old('transport_rent', $prefillData['transport_rent'] ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="gp-label text-navy fw-bold"><i class="fa fa-wallet text-warning me-1"></i> Payment / Expense Account</label>
                        <select name="expense_account_id" class="gp-control" style="font-weight:600; border-color:#94a3b8;">
                            <option value="">-- Select Payment Account (Optional) --</option>
                            @if(isset($accounts) && count($accounts) > 0)
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('expense_account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->title }} {{ $acc->account_code ? '('.$acc->account_code.')' : '' }} {{ isset($acc->head?->name) ? '['.$acc->head->name.']' : '' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- General Remarks -->
                    <div class="col-12 mt-1">
                        <label class="gp-label">General Remarks</label>
                        <input type="text" name="remarks" class="gp-control" placeholder="Special delivery instructions..." value="{{ old('remarks') }}">
                    </div>

                    <!-- Packing Note -->
                    <div class="col-12 mt-1">
                        <label class="gp-label">Packing Type / Special Notes</label>
                        <textarea name="note" class="gp-control gp-textarea-compact" placeholder="Coils, bundles, bags, or special instructions...">{{ old('note') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        <!-- Items Table Grid Card -->
        <div class="gp-card mb-2">
            <div class="gp-card-header">
                <h6 class="gp-card-title"><i class="fa fa-boxes-packing"></i> Delivery Items Allocation</h6>
                <span class="badge bg-primary rounded-pill px-2" id="itemCountBadge" style="font-size:0.7rem;">0 Products</span>
            </div>

            <div class="gp-table-wrap">
                <table class="gp-table">
                    <thead>
                        <tr>
                            <th style="min-width:240px;">Product Name</th>
                            <th style="min-width:110px;">Item Code</th>
                            <th style="min-width:100px;">Brand</th>
                            <th style="min-width:70px;" class="text-center">Unit</th>
                            <th style="min-width:100px;" class="text-end">Available</th>
                            <th style="min-width:110px;" class="text-end">Deliver Qty</th>
                            <th style="min-width:100px;" class="text-end">Remaining</th>
                            <th style="width:45px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="gatepassItems">
                        @php
                            if (empty($prefillData) || !isset($prefillData['invoice_no'])) {
                                $prefillData = [
                                    'invoice_no' => null,
                                    'customer_name' => null,
                                    'delivery_city' => null,
                                ];
                            }
                            
                            $itemsPrefill = [];
                            if (!empty($prefill) && is_array($prefill)) {
                                $itemsPrefill = $prefill;
                            } elseif (!empty($order->items) && is_array($order->items)) {
                                $itemsPrefill = $order->items;
                            } elseif (!empty($sale) && $sale->saleItems->count() > 0) {
                                $itemsPrefill = $sale->saleItems
                                    ->filter(function ($si) use ($order) {
                                        return empty($order->warehouse_id) ||
                                            $si->warehouse_id == $order->warehouse_id;
                                    })
                                    ->map(function ($si) {
                                        return [
                                            'product_id' => $si->product_id,
                                            'product_name' => $si->product->item_name ?? null,
                                            'item_code' => $si->product->item_code ?? null,
                                            'qty' => $si->sales_qty ?? ($si->qty ?? 0),
                                            'retail_price' => $si->retail_price ?? null,
                                            'amount' => $si->amount ?? null,
                                        ];
                                    })
                                    ->values()
                                    ->toArray();
                            }
                        @endphp

                        @forelse($itemsPrefill as $p)
                            <tr>
                                <td class="searchWrap">
                                    <input type="hidden" name="product_id[]" class="product_id" value="{{ $p['product_id'] ?? '' }}">
                                    <input type="text" class="gp-control productSearch" placeholder="Search product..." autocomplete="off" value="{{ $p['product_name'] ?? '' }}">
                                    <div class="searchResults"></div>
                                </td>
                                <td>
                                    <input type="text" name="item_code[]" class="gp-control" readonly value="{{ $p['item_code'] ?? '' }}">
                                </td>
                                <td>
                                    <input type="text" name="brand[]" class="gp-control" readonly value="{{ $p['brand'] ?? ($productsMap[$p['product_id']]['brand'] ?? '') }}">
                                </td>
                                <td>
                                    <input type="text" name="unit[]" class="gp-control text-center" readonly value="{{ $p['unit'] ?? ($productsMap[$p['product_id']]['unit'] ?? '') }}">
                                </td>
                                <td>
                                    <input type="number" name="available_qty[]" class="gp-control available_qty text-end font-monospace fw-bold" readonly value="{{ $p['available_qty'] ?? $p['qty'] ?? 1 }}">
                                </td>
                                <td>
                                    <input type="number" name="qty[]" class="gp-control quantity text-end fw-bold text-primary" min="0.01" step="any" value="{{ $p['qty'] ?? 1 }}" 
                                        data-product-id="{{ $p['product_id'] ?? '' }}" 
                                        data-total-sale-qty="{{ $p['total_sale_qty'] ?? $p['available_qty'] ?? 0 }}"
                                        data-previously-delivered="{{ $p['previously_delivered'] ?? 0 }}">
                                </td>
                                <td>
                                    <input type="number" name="remaining_qty[]" class="gp-control remaining_qty text-end font-monospace fw-bold text-danger" readonly value="{{ $p['remaining_qty'] ?? 0 }}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn-remove-row remove-row" title="Remove"><i class="fa fa-xmark"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="searchWrap">
                                    <input type="hidden" name="product_id[]" class="product_id">
                                    <input type="text" class="gp-control productSearch" placeholder="Search product..." autocomplete="off">
                                    <div class="searchResults"></div>
                                </td>
                                <td><input type="text" name="item_code[]" class="gp-control" readonly></td>
                                <td><input type="text" name="brand[]" class="gp-control" readonly></td>
                                <td><input type="text" name="unit[]" class="gp-control text-center" readonly></td>
                                <td><input type="number" name="available_qty[]" class="gp-control available_qty text-end font-monospace fw-bold" readonly value="0"></td>
                                <td><input type="number" name="qty[]" class="gp-control quantity text-end fw-bold text-primary" min="0.01" step="any" value="1"></td>
                                <td><input type="number" name="remaining_qty[]" class="gp-control remaining_qty text-end font-monospace fw-bold text-danger" readonly value="0"></td>
                                <td class="text-center">
                                    <button type="button" class="btn-remove-row remove-row" title="Remove"><i class="fa fa-xmark"></i></button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Action Bar -->
            <div class="gp-foot-bar">
                <button type="button" id="addRowBtn" class="btn-navy">
                    <i class="fa fa-plus me-1"></i> Add Product Row
                </button>
                
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('OutwardGatepass.home') }}" class="btn-sm-outline text-decoration-none">Cancel</a>
                    <button type="submit" class="btn-gold">
                        <i class="fa fa-check me-1"></i> Submit Outward Gatepass
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

@endsection

<!-- Script dependencies & Logic -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(function() {
        
        function updateItemCount() {
            const count = $('#gatepassItems tr').length;
            $('#itemCountBadge').text(count + (count === 1 ? ' Product' : ' Products'));
        }

        function escapeHtml(t) {
            return String(t || '').replace(/[&<>"'`=\/]/g, s => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#47;',
                '`': '&#96;',
                '=': '&#61;'
            } [s]));
        }

        function appendBlankRow() {
            $('#gatepassItems').append(
                `<tr>
                    <td class="searchWrap">
                        <input type="hidden" name="product_id[]" class="product_id">
                        <input type="text" class="gp-control productSearch" placeholder="Search product..." autocomplete="off">
                        <div class="searchResults"></div>
                    </td>
                    <td><input type="text" name="item_code[]" class="gp-control" readonly></td>
                    <td><input type="text" name="brand[]" class="gp-control" readonly></td>
                    <td><input type="text" name="unit[]" class="gp-control text-center" readonly></td>
                    <td><input type="number" name="available_qty[]" class="gp-control available_qty text-end font-monospace fw-bold" readonly value="0"></td>
                    <td><input type="number" name="qty[]" class="gp-control quantity text-end fw-bold text-primary" min="0.01" step="any" value="1"></td>
                    <td><input type="number" name="remaining_qty[]" class="gp-control remaining_qty text-end font-monospace fw-bold text-danger" readonly value="0"></td>
                    <td class="text-center"><button type="button" class="btn-remove-row remove-row" title="Remove"><i class="fa fa-xmark"></i></button></td>
                </tr>`
            );
            updateItemCount();
        }

        // Live calculation of remaining quantity
        $(document).on('change keyup', '.quantity', function() {
            const $row = $(this).closest('tr');
            const available = parseFloat($row.find('.available_qty').val()) || 0;
            const delivered = parseFloat($(this).val()) || 0;
            const totalSaleQty = parseFloat($(this).data('total-sale-qty')) || available;
            const previouslyDelivered = parseFloat($(this).data('previously-delivered')) || 0;
            
            // Remaining = Total - Previously Delivered - Current Delivery
            const remaining = Math.max(0, totalSaleQty - previouslyDelivered - delivered);
            
            $row.find('.remaining_qty').val(remaining.toFixed(2));
            
            // Highlight row if delivery > available
            if (delivered > available && available > 0) {
                $(this).css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
            } else {
                $(this).css({'border-color': '', 'background-color': ''});
            }
        });

        // Fetch available stock when product is selected
        function fetchAvailableStock(productId, $row) {
            if (!productId) return;
            
            const warehouseId = $('input[name="warehouse_id"]').val();
            if (!warehouseId) return;
            
            $.get("{{ route('get-warehouse-stock') }}", {
                product_id: productId,
                warehouse_id: warehouseId
            }, function(data) {
                const availableQty = data && data.quantity ? parseFloat(data.quantity) : 0;
                $row.find('.available_qty').val(availableQty.toFixed(2));
                
                // Reset delivered qty to available if previously was more
                const deliveredQty = parseFloat($row.find('.quantity').val()) || 0;
                if (deliveredQty === 1 || deliveredQty > availableQty) {
                    $row.find('.quantity').val(availableQty.toFixed(2)).trigger('change');
                } else {
                    $row.find('.quantity').trigger('change');
                }
            }).fail(function() {
                $row.find('.available_qty').val('0');
                $row.find('.quantity').trigger('change');
            });
        }

        $('#addRowBtn').on('click', appendBlankRow);

        $(document).on('keyup', '.productSearch', function() {
            const $inp = $(this),
                q = $inp.val().trim(),
                $wrap = $inp.closest('.searchWrap'),
                $box = $wrap.find('.searchResults');
            if (!q) {
                $box.hide().empty();
                return;
            }
            $.get("{{ route('search-products') }}", {
                q
            }, function(data) {
                let html = '';
                (data || []).forEach(p => {
                    const brand = p.brand && p.brand.name ? p.brand.name : '';
                    const unitName = (p.unit && p.unit.name) ? p.unit.name : (p.unit_name || p.unit || p.unit_id || '');
                    html += `
                        <div class="result" data-id="${p.id||''}" data-name="${escapeHtml(p.item_name||'')}" data-code="${escapeHtml(p.item_code||'')}" data-brand="${escapeHtml(brand)}" data-unit="${escapeHtml(unitName)}">
                            <div>
                                <div class="result-title">${escapeHtml(p.item_name||'')}</div>
                                <div class="result-meta">${escapeHtml(p.item_code||'')}</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark border">${escapeHtml(brand || 'N/A')}</span>
                                <small class="text-muted d-block">${escapeHtml(unitName || '')}</small>
                            </div>
                        </div>`;
                });
                $box.html(html).show();
            });
        });

        $(document).on('click', '.searchResults .result', function() {
            const $r = $(this),
                $tr = $r.closest('tr');
            const productId = $r.data('id');
            
            $tr.find('.product_id').val(productId);
            $tr.find('.productSearch').val($r.data('name'));
            $tr.find('input[name="item_code[]"]').val($r.data('code'));
            $tr.find('input[name="brand[]"]').val($r.data('brand'));
            $tr.find('input[name="unit[]"]').val($r.data('unit'));
            $r.parent().hide().empty();
            
            fetchAvailableStock(productId, $tr);
            
            if ($('#gatepassItems tr:last .product_id').val()) {
                appendBlankRow();
                $('#gatepassItems tr:last .productSearch').focus();
            }
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.searchWrap').length) {
                $('.searchResults').hide().empty();
            }
        });

        $(document).on('click', '.remove-row', function() {
            if ($('#gatepassItems tr').length > 1) {
                $(this).closest('tr').remove();
                updateItemCount();
            }
        });

        // Form Submit Handler
        $('#gatepassForm').on('submit', function(e) {
            e.preventDefault();
            
            $('#gatepassItems tr').each(function() {
                if (!$(this).find('.product_id').val() && !$(this).find('.productSearch').val())
                    $(this).remove();
            });
            
            let validCount = 0;
            const lines = [];

            $('#gatepassItems tr').each(function() {
                const productId = $(this).find('.product_id').val();
                const productName = $(this).find('.productSearch').val();
                const code = $(this).find('input[name="item_code[]"]').val() || '';
                const qty = parseFloat($(this).find('input[name="qty[]"]').val()) || 0;
                
                if ((productId || productName) && qty > 0) {
                    validCount++;
                    lines.push((productName || code) + (qty ? ' | Qty: ' + qty : ''));
                }
            });
            
            $('#items_text').val(lines.join('\n'));
            
            if (validCount === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'No Items Added',
                    text: 'Please add at least one product with quantity > 0 to create a gate pass',
                    confirmButtonColor: '#1e3a5f'
                });
                return false;
            }
            
            Swal.fire({
                title: 'Issuing Outward Gatepass...',
                text: 'Creating gatepass with ' + validCount + ' product(s)...',
                didOpen: () => Swal.showLoading(),
                allowOutsideClick: false,
                allowEscapeKey: false
            });
            
            this.submit();
        });

        $('#gatepassForm').on('keypress', function(e) {
            if (e.key === 'Enter' && e.target.type !== 'textarea') {
                e.preventDefault();
            }
        });

        updateItemCount();
    });
</script>

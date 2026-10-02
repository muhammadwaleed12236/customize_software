@extends('admin_panel.layout.app')
@section('content')
<style>
    .delivery-card { border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .badge-pending { background: #fbbf24; color: #92400e; }
    .badge-partial { background: #60a5fa; color: #1e3a8a; }
    .badge-delivered { background: #34d399; color: #064e3b; }
    .qty-input { width: 100px; text-align: center; border: 2px solid #e2e8f0; border-radius: 8px; padding: 6px; transition: border-color 0.2s; }
    .qty-input:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
    .progress-bar-custom { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; }
    .progress-bar-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, #6366f1, #8b5cf6); transition: width 0.3s; }
    .summary-box { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border-radius: 12px; padding: 20px; }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="card delivery-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="fas fa-truck text-primary me-2"></i>
                        Partial Delivery
                    </h4>
                    <p class="text-muted mb-0">Deliver items partially against booking</p>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-primary fs-5">{{ $booking->invoice_no }}</div>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}</small>
                </div>
            </div>

            <hr>

            <div class="row g-3">
                <div class="col-sm-3">
                    <div class="text-muted small mb-1">Customer</div>
                    <div class="fw-semibold">
                        @if($booking->party_type === 'credit' || $booking->party_type === 'cash')
                            {{ $booking->customer->customer_name ?? 'N/A' }}
                        @else
                            {{ $booking->customer_name ?? 'Walking Customer' }}
                        @endif
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="text-muted small mb-1">Party Type</div>
                    <div class="fw-semibold text-capitalize">{{ $booking->party_type }}</div>
                </div>
                <div class="col-sm-3">
                    <div class="text-muted small mb-1">Delivery Status</div>
                    <span class="badge px-3 py-2 rounded-pill
                        @if(($booking->delivery_status ?? 'pending') === 'delivered') badge-delivered
                        @elseif(($booking->delivery_status ?? 'pending') === 'partial') badge-partial
                        @else badge-pending @endif">
                        {{ ucfirst($booking->delivery_status ?? 'pending') }}
                    </span>
                </div>
                <div class="col-sm-3">
                    <div class="text-muted small mb-1">Booking Total</div>
                    <div class="fw-semibold text-success">Rs. {{ number_format($booking->total_balance, 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <!-- Delivery Form -->
    <form action="{{ route('bookings.deliver.store', $booking->id) }}" method="POST" id="deliveryForm">
        @csrf

        <div class="card delivery-card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold"><i class="fas fa-boxes me-2 text-primary"></i>Items to Deliver</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Product</th>
                                <th class="text-center">Booked</th>
                                <th class="text-center">Delivered</th>
                                <th class="text-center">Remaining</th>
                                <th class="text-center">Progress</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Deliver Now</th>
                                <th>Warehouse</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                            @php
                                $product      = $item->product;
                                $booked       = floatval($item->sales_qty);
                                $delivered    = floatval($item->delivered_qty ?? 0);
                                $remaining    = max(0, $booked - $delivered);
                                $progressPct  = $booked > 0 ? round(($delivered / $booked) * 100) : 0;
                                $itemWarehouses = $warehouses[$item->product_id] ?? collect();
                            @endphp
                            <tr data-item-id="{{ $item->id }}" data-remaining="{{ $remaining }}" data-price="{{ $item->retail_price }}">
                                <td class="text-muted ps-3">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $product->item_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $product->item_code ?? '' }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ number_format($booked, 0) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info text-white">{{ number_format($delivered, 0) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark remaining-badge">{{ number_format($remaining, 0) }}</span>
                                </td>
                                <td class="text-center" style="min-width:110px">
                                    <div class="progress-bar-custom">
                                        <div class="progress-bar-fill" style="width: {{ $progressPct }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $progressPct }}%</small>
                                </td>
                                <td class="text-center">
                                    <span class="text-success fw-semibold">Rs.{{ number_format($item->retail_price, 0) }}</span>
                                </td>
                                <td class="text-center">
                                    <input type="number"
                                        name="deliver_qty[{{ $item->id }}]"
                                        class="qty-input deliver-qty-input"
                                        min="0"
                                        max="{{ $remaining }}"
                                        step="0.01"
                                        value="0"
                                        data-item-id="{{ $item->id }}"
                                        data-remaining="{{ $remaining }}"
                                        data-price="{{ $item->retail_price }}"
                                        placeholder="0">
                                </td>
                                <td style="min-width:170px">
                                    <select name="warehouse_id[{{ $item->id }}]" class="form-select form-select-sm">
                                        <option value="">-- Branch Stock --</option>
                                        @foreach($itemWarehouses as $ws)
                                            <option value="{{ $ws->warehouse_id }}"
                                                {{ $item->warehouse_id == $ws->warehouse_id ? 'selected' : '' }}>
                                                {{ $ws->warehouse->name ?? 'WH-'.$ws->warehouse_id }}
                                                ({{ number_format($ws->quantity, 0) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary & Submit -->
        <div class="row g-4 align-items-start">
            <div class="col-md-7">
                <div class="card delivery-card">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Delivery Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Items Selected</span>
                            <span class="fw-semibold" id="totalItems">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Qty to Deliver</span>
                            <span class="fw-semibold" id="totalQty">0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Estimated Amount</span>
                            <span class="fw-bold text-primary fs-5" id="totalAmount">Rs. 0</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="summary-box">
                    <h6 class="fw-bold mb-3 opacity-75">Quick Actions</h6>
                    <button type="button" class="btn btn-light w-100 mb-2 fw-semibold" id="fillAllBtn">
                        <i class="fas fa-fill me-2"></i> Fill All Remaining
                    </button>
                    <button type="button" class="btn btn-outline-light w-100 mb-3" id="clearAllBtn">
                        <i class="fas fa-times me-2"></i> Clear All
                    </button>
                    <button type="submit" class="btn btn-warning w-100 fw-bold py-3" id="submitBtn">
                        <i class="fas fa-truck me-2"></i> Process Delivery &amp; Generate Invoice
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Bookings
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.deliver-qty-input');

    function updateSummary() {
        let totalItems = 0, totalQty = 0, totalAmount = 0;
        inputs.forEach(function (inp) {
            const qty = parseFloat(inp.value) || 0;
            if (qty > 0) {
                totalItems++;
                totalQty += qty;
                totalAmount += qty * parseFloat(inp.dataset.price || 0);
            }
        });
        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('totalQty').textContent = totalQty.toLocaleString();
        document.getElementById('totalAmount').textContent = 'Rs. ' + Math.round(totalAmount).toLocaleString();
    }

    inputs.forEach(function (inp) {
        inp.addEventListener('input', function () {
            const max = parseFloat(this.dataset.remaining);
            if (parseFloat(this.value) > max) {
                this.value = max;
                this.style.borderColor = '#ef4444';
                setTimeout(() => this.style.borderColor = '', 1000);
            }
            updateSummary();
        });
    });

    document.getElementById('fillAllBtn').addEventListener('click', function () {
        inputs.forEach(function (inp) {
            inp.value = inp.dataset.remaining;
        });
        updateSummary();
    });

    document.getElementById('clearAllBtn').addEventListener('click', function () {
        inputs.forEach(function (inp) { inp.value = 0; });
        updateSummary();
    });

    document.getElementById('deliveryForm').addEventListener('submit', function (e) {
        let hasQty = false;
        inputs.forEach(function (inp) { if (parseFloat(inp.value) > 0) hasQty = true; });
        if (!hasQty) {
            e.preventDefault();
            alert('Please enter at least one delivery quantity.');
        }
    });

    updateSummary();
});
</script>
@endsection

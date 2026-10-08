{{-- @php --}}
    
{{-- // echo "<pre>";
//         print_r($sales); 
//     echo "</pre>"; --}}
{{-- @endphp --}}
@extends('admin_panel.layout.app')

@section('content')
<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    .container-fluid {
        padding-left: 0;
        padding-right: 0;
        width: 100%;
        max-width: 100%;
    }

    .card {
        margin-left: 0;
        margin-right: 0;
        width: 100%;
    }

    .card-body {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        margin-bottom: 0;
    }

    .table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fa;
        vertical-align: middle;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
        white-space: nowrap;
        padding: 0.5rem;
    }

    .table-responsive {
        /* Default Bootstrap handles this, no relative needed */
    }

    .btn {
        font-size: 0.85rem;
        padding: 0.35rem 0.5rem;
    }

    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.4rem;
    }

    .card-header {
        padding: 1rem;
    }

    .card-header h5 {
        font-size: 1.1rem;
    }

    /* Button CSS for dropdown */
    .action-dropdown {
        border-radius: 12px;
        padding: 6px;
        min-width: 210px;
    }

    .action-dropdown .dropdown-item {
        padding: 9px 14px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.25s ease;
    }

    .action-dropdown .dropdown-item:hover {
        background: linear-gradient(90deg, #f8f9fa, #eef1f5);
        transform: translateX(4px);
    }

    /* Fix dropdown overflow - make sure it doesn't get hidden */
    .btn-group {
        position: relative;
    }

    .dropdown-menu {
        z-index: 10000 !important;
        max-height: 300px;
        overflow-y: auto;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }



    /* Higher z-index for card to sit above footer */
    .card {
        position: relative;
        z-index: 10;
    }

    .container-fluid {
        position: relative;
        z-index: 1;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .card {
            margin-top: 0.5rem;
        }

        .card-header {
            flex-direction: column;
            gap: 0.5rem;
        }

        .card-header h5 {
            font-size: 1rem;
        }

        .card-header > div {
            width: 100%;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .card-header .btn {
            flex: 1;
            min-width: 120px;
        }

        .table {
            font-size: 0.75rem;
        }

        .table thead th {
            font-size: 0.7rem;
            padding: 0.3rem;
        }

        .table td {
            font-size: 0.7rem;
            padding: 0.3rem;
        }

        .btn {
            font-size: 0.7rem;
            padding: 0.2rem 0.3rem;
        }

        .btn-sm {
            font-size: 0.65rem;
            padding: 0.15rem 0.25rem;
        }

        .action-dropdown {
            min-width: 180px;
        }
    }

    @media (max-width: 576px) {
        .table {
            font-size: 0.65rem;
        }

        .table thead th {
            font-size: 0.6rem;
            padding: 0.25rem;
        }

        .table td {
            font-size: 0.6rem;
            padding: 0.25rem;
        }

        .btn {
            font-size: 0.65rem;
            padding: 0.15rem 0.25rem;
        }

        .action-dropdown {
            min-width: 160px;
        }

        .action-dropdown .dropdown-item {
            padding: 6px 10px;
            font-size: 0.7rem;
        }
    }
</style>

<div class="container-fluid">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
            <h5 class="mb-0">Sales Records</h5>
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <input type="text" id="invoiceSearch" class="form-control form-control-sm" placeholder="🔍 Search Invoice No..." style="width: 250px; border-radius: 8px;">
                <a href="{{ route('sale.add') }}" class="btn btn-primary btn-sm me-1">Add Sale</a>
                <a href="{{ url('bookings') }}" class="btn btn-primary btn-sm">All Sale Orders</a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#ID</th>
                        @if(Auth::check() && Auth::user()->hasRole('super admin'))
                        <th>branch</th>
                        @endif
                        <th>Invoice No</th>
                        <th>Customer Type</th>
                        <th>Customer name</th>
                        {{-- <th>Quantity</th> --}}
                        <th>Subtotal</th>
                        <th>Discount %</th>
                        <th>Discount Amount</th>
                        <th>Total Balance</th>
                        <th>Delivery / Remaining Qty</th>
                        {{-- <th>Receipt</th> --}}
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>@foreach($sales as $sale)
<tr>
    <td>{{ $sale->id }}</td>
    @if(Auth::check() && Auth::user()->hasRole('super admin'))
    <td>{{ $sale->branch->name ?? optional($sale->customer->branch)->name ?? 'N/A' }}</td>
    @endif
    <td>{{ $sale->invoice_no }}</td>
    <td>{{ $sale->party_type }}</td>
    <!-- Customer Name: prefer linked customer, otherwise show sub_customer (walking) -->
    <td>{{ optional($sale->customer)->customer_name ?? $sale->sub_customer ?? 'N/A' }}</td>
    {{-- <td>{{ $sale->quantity ?? 0 }}</td> --}}
    <td>{{ number_format($sale->sub_total1, 2) }}</td>
    <td>
        @if($sale->saleItems && $sale->saleItems->count() > 0)
            @php
                $avgDiscountPercent = $sale->saleItems->avg('discount_percent');
            @endphp
            {{ number_format($avgDiscountPercent, 2) }}%
        @else
            {{ number_format($sale->discount_percent, 2) }}%
        @endif
    </td>
    <td>
        @if($sale->saleItems && $sale->saleItems->count() > 0)
            @php
                $totalDiscountAmount = $sale->saleItems->sum('discount_amount');
            @endphp
            {{ number_format($totalDiscountAmount, 2) }}
        @else
            {{ number_format($sale->discount_amount, 2) }}
        @endif
    </td>
    <td>{{ number_format(($sale->party_type== 'credit')?($sale->total_balance):($sale->total_net), 2) }}</td>
    <td>
        @php
            $rem = $sale->total_remaining_qty ?? 0;
            $del = $sale->total_delivered_qty ?? 0;
            $ord = $sale->total_ordered_qty ?? 0;
        @endphp
        @if($rem <= 0)
            <span class="badge bg-success text-white py-1 px-2" style="font-size: 0.72rem; font-weight: 500;">
                <i class="fas fa-check-circle me-1"></i>Delivered (0 Rem)
            </span>
        @elseif($del > 0)
            <span class="badge bg-secondary text-white py-1 px-2 mb-1 d-inline-block" style="font-size: 0.72rem; font-weight: 500;">
                <i class="fas fa-truck-loading me-1"></i>Partial ({{ number_format($rem) }} Rem)
            </span>
            <div class="text-muted" style="font-size: 0.68rem;">Delivered: {{ number_format($del) }} / {{ number_format($ord) }}</div>
        @else
            <span class="badge bg-dark text-white py-1 px-2 mb-1 d-inline-block" style="font-size: 0.72rem; font-weight: 500;">
                <i class="fas fa-clock me-1"></i>Pending ({{ number_format($rem) }} Rem)
            </span>
            <div class="text-muted" style="font-size: 0.68rem;">Total Qty: {{ number_format($ord) }}</div>
        @endif
        <div>
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none view-movement-btn mt-1" data-sale-id="{{ $sale->id }}" style="font-size: 0.72rem; color: #0d6efd; font-weight: 600;">
                <i class="fas fa-eye me-1"></i>View Details
            </button>
        </div>
    </td>
    {{-- <td>{{ number_format($sale->receipt1 + $sale->receipt2, 2) }}</td> --}}
    <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d-m-Y') }}</td>
    <td class="text-center">
        <!-- PRIMARY ACTION -->
        <a href="{{ route('sale.invoice', $sale->id) }}" class="btn btn-sm btn-info text-white me-1" title="View Invoice">
            <i class="fas fa-file-invoice"></i> Invoice
        </a>

        <!-- DELETE BUTTON -->
        @can('sale.delete')
        <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline me-1" onsubmit="return confirm('Are you sure you want to delete Invoice #{{ $sale->invoice_no }}? Stock, customer ledger, and account balances will be reverted.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="Delete Sale Invoice">
                <i class="fas fa-trash-alt"></i> Delete
            </button>
        </form>
        @endcan

        <!-- MORE OPTIONS DROPDOWN -->
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-dark dropdown-toggle dropdown-toggle-split" data-boundary="window" aria-expanded="false">
                <i class="fas fa-ellipsis-v"></i> More
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-lg action-dropdown">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 view-movement-btn" href="javascript:void(0)" data-sale-id="{{ $sale->id }}">
                        <i class="fas fa-history text-info"></i> Movement Details
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('sales.return.create', $sale->id) }}">
                        <i class="fas fa-undo text-warning"></i> Return Sale
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('sales.edit', $sale->id) }}">
                        <i class="fas fa-edit text-primary"></i> Edit Sale
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('sale.invoice', $sale->id) }}">
                        <i class="fas fa-receipt text-danger"></i> Receipt
                    </a>
                </li>
                <li>
                    @php
                        // ✅ Check if DC already exists for this sale
                        $dcExists = \App\Models\WarehouseOrder::where('sale_id', $sale->id)->exists();
                        
                        // ✅ Check if sale is in draft_posted mode AND no DC created yet
                        $draftBooking = $sale;
                        $isDraftPosted = $draftBooking && $draftBooking->status === 'draft_posted';
                        $needsWarehouseSelection = $isDraftPosted && !$dcExists;
                    @endphp

                    @if($needsWarehouseSelection)
                        {{-- 🎯 DRAFT MODE (First Time): Show warehouse selection before DC --}}
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('sale.warehouse.select', $sale->id) }}" title="Select warehouse for delivery">
                            <i class="fas fa-warehouse text-warning"></i> Select Warehouse
                        </a>
                    @else
                        {{-- 📦 REGULAR MODE OR DC EXISTS: Direct DC generation/display --}}
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('sale.dc', $sale->id) }}" title="Generate or display delivery challan">
                            <i class="fas fa-truck text-success"></i> Delivery Challan
                        </a>
                    @endif
                </li>
                @can('sale.delete')
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete Invoice #{{ $sale->invoice_no }}? Stock, customer ledger, and account balances will be reverted.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger fw-semibold">
                            <i class="fas fa-trash-alt text-danger"></i> Delete Sale
                        </button>
                    </form>
                </li>
                @endcan
            </ul>
        </div>
    </td>
</tr>
@endforeach

                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<!-- MOVEMENT & DELIVERY DETAILS MODAL -->
<div class="modal fade" id="movementModal" tabindex="-1" aria-labelledby="movementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fs-6 fw-bold" id="movementModalLabel">
                    <i class="fas fa-truck-loading me-2 text-info"></i>Sale Movement & Delivery History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="movementModalBody">
                <div class="text-center py-5" id="movementModalLoader">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted mb-0" style="font-size: 0.85rem;">Fetching item breakdown & movement details...</p>
                </div>

                <div id="movementModalContent" style="display: none;">
                    <!-- Sale Summary Header Card -->
                    <div class="card bg-light border-0 mb-3 shadow-sm" style="border-radius: 10px;">
                        <div class="card-body p-3">
                            <div class="row g-2 text-dark" style="font-size: 0.85rem;">
                                <div class="col-md-3"><strong>Invoice #:</strong> <span id="mdInvoiceNo" class="text-primary font-monospace fw-bold"></span></div>
                                <div class="col-md-3"><strong>Date:</strong> <span id="mdSaleDate"></span></div>
                                <div class="col-md-3"><strong>Customer:</strong> <span id="mdCustomerName" class="fw-bold"></span></div>
                                <div class="col-md-3"><strong>Party Type:</strong> <span id="mdPartyType"></span></div>
                                <div class="col-md-4 mt-2"><strong>Total Ordered Qty:</strong> <span id="mdTotalOrdered" class="badge bg-secondary ms-1"></span></div>
                                <div class="col-md-4 mt-2"><strong>Total Delivered Qty:</strong> <span id="mdTotalDelivered" class="badge bg-success ms-1"></span></div>
                                <div class="col-md-4 mt-2"><strong>Total Remaining Qty:</strong> <span id="mdTotalRemaining" class="badge bg-dark ms-1"></span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items Remaining Breakdown Table -->
                    <h6 class="fw-bold mb-2 text-dark d-flex align-items-center" style="font-size: 0.9rem;">
                        <i class="fas fa-boxes me-2 text-primary"></i>Ordered vs Delivered Items Breakdown
                    </h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.82rem;">
                            <thead class="table-secondary">
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Code</th>
                                    <th>Unit</th>
                                    <th class="text-center">Ordered Qty</th>
                                    <th class="text-center">Delivered Qty</th>
                                    <th class="text-center">Remaining Qty</th>
                                </tr>
                            </thead>
                            <tbody id="mdItemsTableBody">
                                <!-- Loaded dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Movement History Log Timeline -->
                    <h6 class="fw-bold mb-2 text-dark d-flex align-items-center" style="font-size: 0.9rem;">
                        <i class="fas fa-stream me-2 text-info"></i>Movement & Dispatch Logs (When & How Delivered)
                    </h6>
                    <div id="mdMovementsList">
                        <!-- Loaded dynamically -->
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('invoiceSearch');
        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                var filterTrim = this.value.toUpperCase().trim();
                var isNum = /^\d+$/.test(filterTrim);
                var numVal = isNum ? parseInt(filterTrim, 10).toString() : null;

                var rows = document.querySelectorAll('table tbody tr');
                
                rows.forEach(function(row) {
                    var match = false;
                    
                    if (filterTrim === "") {
                        match = true;
                    } else if (isNum) {
                        var cells = row.querySelectorAll('td');
                        cells.forEach(function(cell) {
                            var cellText = (cell.textContent || cell.innerText).trim().toUpperCase();
                            
                            // 1. Smart Invoice Match
                            if (cellText.startsWith('INV-')) {
                                var nums = cellText.match(/\d+/g);
                                if (nums && parseInt(nums[0], 10).toString() === numVal) {
                                    match = true;
                                }
                            }
                            // 2. Exact ID match
                            else if (cellText === numVal) {
                                match = true;
                            }
                            // 3. General loose match (only for numbers longer than 2 digits)
                            else if (filterTrim.length > 2 && cellText.indexOf(filterTrim) > -1) {
                                var isDate = /^\d{2}-\d{2}-\d{4}$/.test(cellText);
                                if (!isDate) {
                                    match = true;
                                }
                            }
                        });
                    } else {
                        // Standard text match
                        var upperText = (row.textContent || row.innerText).toUpperCase();
                        if (upperText.indexOf(filterTrim) > -1) {
                            match = true;
                        }
                    }

                    row.style.display = match ? "" : "none";
                });
            });
        }
        
        // Fix for dropdown clipping in responsive tables
        function openDropdown($el) {
            var $dropdown = $el.data('dropdown-menu');
            if (!$dropdown || $dropdown.length === 0) {
                $dropdown = $el.closest('.btn-group').find('.dropdown-menu');
                $el.data('dropdown-menu', $dropdown);
            }
            if (!$dropdown || $dropdown.length === 0) return;

            // Hide others
            $('.dropdown-appended').not($dropdown).hide();

            // Append to body
            if (!$dropdown.hasClass('dropdown-appended')) {
                $('body').append($dropdown);
                $dropdown.addClass('dropdown-appended');
            }
            
            $dropdown.show();
            var offset = $el.offset();
            var leftPos = offset.left - ($dropdown.outerWidth() - $el.outerWidth());
            if (leftPos < 0) leftPos = 10;

            $dropdown.css({
                'position': 'absolute',
                'top': offset.top + $el.outerHeight(),
                'left': leftPos,
                'z-index': 10500
            });
        }

        // Open on Hover
        $(document).on('mouseenter', '.dropdown-toggle', function() {
            clearTimeout(closeTimer);
            openDropdown($(this));
        });

        // Open on Click
        $(document).on('click', '.dropdown-toggle', function (e) {
            e.stopPropagation();
            openDropdown($(this));
        });

        // Close when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.dropdown-toggle').length && !$(e.target).closest('.dropdown-menu').length) {
                $('.dropdown-appended').hide();
            }
        });

        var closeTimer;

        // Small delay to move from button to menu
        $(document).on('mouseleave', '.dropdown-toggle', function() {
            var $el = $(this);
            var $dropdown = $el.data('dropdown-menu');
            if ($dropdown && $dropdown.is(':visible')) {
                closeTimer = setTimeout(function() {
                    $dropdown.hide();
                }, 150); 
            }
        });

        // Keep open if moving into menu
        $(document).on('mouseenter', '.dropdown-appended', function() {
            clearTimeout(closeTimer);
        });

        // Close when leaving menu
        $(document).on('mouseleave', '.dropdown-appended', function() {
            $(this).hide();
        });

        // ✅ Movement Details Modal AJAX Trigger
        $(document).on('click', '.view-movement-btn', function(e) {
            e.preventDefault();
            $('.dropdown-appended').hide(); // Hide any active open dropdown

            var saleId = $(this).data('sale-id');
            if (!saleId) return;
            
            $('#movementModal').modal('show');
            $('#movementModalLoader').show();
            $('#movementModalContent').hide();

            $.ajax({
                url: '/sale/' + saleId + '/movement-details',
                method: 'GET',
                success: function(res) {
                    if (res.success) {
                        $('#mdInvoiceNo').text(res.sale.invoice_no);
                        $('#mdSaleDate').text(res.sale.date);
                        $('#mdCustomerName').text(res.sale.customer_name);
                        $('#mdPartyType').text(res.sale.party_type);
                        $('#mdTotalOrdered').text(res.sale.total_ordered);
                        $('#mdTotalDelivered').text(res.sale.total_delivered);
                        $('#mdTotalRemaining').text(res.sale.total_remaining);

                        // Populate Items Table
                        var itemsHtml = '';
                        $.each(res.items, function(idx, item) {
                            var remClass = item.remaining_qty > 0 ? 'fw-bold text-dark' : 'text-success';
                            itemsHtml += '<tr>' +
                                '<td>' + (idx + 1) + '</td>' +
                                '<td><strong>' + item.product_name + '</strong></td>' +
                                '<td>' + (item.item_code || '-') + '</td>' +
                                '<td>' + (item.unit || '-') + '</td>' +
                                '<td class="text-center">' + item.ordered_qty + '</td>' +
                                '<td class="text-center text-success fw-bold">' + item.delivered_qty + '</td>' +
                                '<td class="text-center ' + remClass + '">' + item.remaining_qty + '</td>' +
                            '</tr>';
                        });
                        $('#mdItemsTableBody').html(itemsHtml);

                        // Populate Movements Timeline
                        var movHtml = '';
                        if (res.movements && res.movements.length > 0) {
                            $.each(res.movements, function(idx, mov) {
                                var statusBadge = mov.status === 'Delivered' ? 'bg-success' : 'bg-dark text-white';
                                
                                var itemsList = '';
                                $.each(mov.items, function(iIdx, mi) {
                                    itemsList += '<span class="badge bg-light text-dark border me-1 mb-1" style="font-weight: 500; font-size: 0.78rem;">' + 
                                        mi.product_name + ': <strong>' + mi.qty + ' ' + (mi.unit || '') + '</strong></span> ';
                                });

                                movHtml += '<div class="card border mb-2 shadow-sm" style="border-radius: 8px;">' +
                                    '<div class="card-header bg-white py-2 d-flex justify-content-between align-items-center" style="font-size: 0.82rem;">' +
                                        '<div><strong class="text-dark"><i class="fas fa-truck text-primary me-1"></i>' + mov.type + ' (' + mov.doc_no + ')</strong>' +
                                        (mov.dc_no && mov.dc_no !== 'N/A' ? ' <span class="text-muted ms-2">[DC #: ' + mov.dc_no + ']</span>' : '') + '</div>' +
                                        '<div><span class="badge ' + statusBadge + ' me-2">' + mov.status + '</span><small class="text-muted"><i class="far fa-calendar-alt me-1"></i>' + mov.date + '</small></div>' +
                                    '</div>' +
                                    '<div class="card-body p-3" style="font-size: 0.8rem;">' +
                                        '<div class="row g-2 mb-2 text-muted">' +
                                            '<div class="col-md-4"><i class="fas fa-map-marker-alt text-danger me-1"></i>Location: <strong class="text-dark">' + mov.location + '</strong></div>' +
                                            '<div class="col-md-4"><i class="fas fa-shipping-fast text-info me-1"></i>Transporter: <strong class="text-dark">' + mov.transporter + '</strong></div>' +
                                            '<div class="col-md-4"><i class="fas fa-user-check text-secondary me-1"></i>Driver/Vehicle: <strong class="text-dark">' + mov.driver + ' (' + mov.vehicle + ')</strong></div>' +
                                        '</div>' +
                                        '<div><strong>Dispatched Items:</strong><div class="mt-1">' + itemsList + '</div></div>' +
                                        (mov.remarks ? '<div class="mt-2 text-muted"><small><em>Remarks: ' + mov.remarks + '</em></small></div>' : '') +
                                    '</div>' +
                                '</div>';
                            });
                        } else {
                            movHtml = '<div class="alert alert-secondary py-3 text-center mb-0" style="font-size: 0.85rem;">No movement or gatepass records found for this sale.</div>';
                        }
                        $('#mdMovementsList').html(movHtml);

                        $('#movementModalLoader').hide();
                        $('#movementModalContent').show();
                    } else {
                        alert(res.message || 'Could not load movement details.');
                        $('#movementModal').modal('hide');
                    }
                },
                error: function() {
                    alert('Error fetching movement details.');
                    $('#movementModal').modal('hide');
                }
            });
        });
        
    });
</script>
@endsection

@endsection

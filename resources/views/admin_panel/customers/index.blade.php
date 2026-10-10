@extends('admin_panel.layout.app')
@section('content')
<style>
    :root {
        --primary-color: #1e3a5f;
        --secondary-color: #64748b;
        --success-color: #10b981;
        --info-color: #0ea5e9;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --purple-color: #6f42c1;
        --light-bg: #f8fafc;
        --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .main-content {
        background-color: #f0f4f8;
        min-height: 100vh;
        padding: 1.5rem;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        background: #ffffff;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
        border: 1px solid #e2e8f0;
    }

    .page-title {
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .page-title i {
        color: var(--primary-color);
    }

    .btn-action-group {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .card-filter {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 1.25rem;
        background: #ffffff;
    }

    .card-filter .card-body {
        padding: 1.25rem;
    }

    .filter-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--secondary-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.35rem;
    }

    /* Table Styling */
    .table-container {
        background: #ffffff;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.03em;
        padding: 1rem 0.85rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    .table tbody td {
        padding: 0.85rem;
        vertical-align: middle;
        font-size: 0.85rem;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    .table tbody tr:hover {
        background-color: rgba(30, 58, 95, 0.02);
    }

    /* Badges */
    .badge-erp {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        border-radius: 0.35rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-cash { background-color: #e0f2fe; color: #0369a1; }
    .badge-credit { background-color: #fef3c7; color: #b45309; }
    .badge-active { background-color: #d1fae5; color: #047857; }
    .badge-inactive { background-color: #fee2e2; color: #b91c1c; }

    .balance-val {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        font-weight: 700;
        text-align: right;
    }

    .balance-positive { color: var(--success-color); }
    .balance-negative { color: var(--danger-color); }

    /* Action Buttons */
    .btn-erp {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-edit { background: rgba(30, 58, 95, 0.1); color: var(--primary-color); }
    .btn-edit:hover { background: var(--primary-color); color: #fff; }

    .btn-history { background: rgba(111, 66, 193, 0.1); color: var(--purple-color); }
    .btn-history:hover { background: var(--purple-color); color: #fff; }

    .btn-toggle { background: rgba(14, 165, 233, 0.1); color: var(--info-color); }
    .btn-toggle:hover { background: var(--info-color); color: #fff; }

    .btn-delete { background: rgba(239, 68, 68, 0.1); color: var(--danger-color); }
    .btn-delete:hover { background: var(--danger-color); color: #fff; }

    /* History Modal Styles */
    .modal-header-custom {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        color: #ffffff;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
</style>

<div class="main-content">
    <div class="page-header">
        <h3 class="page-title"><i class="fa-solid fa-users-rectangle"></i> Customer Management</h3>
        <div class="btn-action-group">
            <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"><i class="fa fa-plus me-1"></i> Add New</a>
            <a href="{{ route('customers.ledger') }}" class="btn btn-info btn-sm rounded-pill px-3 shadow-sm text-white"><i class="fa fa-book me-1"></i> Ledger</a>
            <a href="{{ route('customer.payments') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm"><i class="fa fa-wallet me-1"></i> Payment</a>
            <a href="{{ route('customers.inactive') }}" class="btn btn-secondary btn-sm rounded-pill px-3 shadow-sm"><i class="fa fa-user-slash me-1"></i> Inactive</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center mb-3">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Search Filter Card --}}
    <div class="card card-filter">
        <div class="card-body">
            <form action="{{ route('customers.index') }}" method="GET" class="row g-3">
                @if(Auth::user()->hasRole('super admin'))
                    <div class="col-md-2">
                        <label class="filter-label">Branch</label>
                        <select name="branch_id" id="branch_filter" class="form-control select2">
                            <option value="">All Branches</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-2">
                    <label class="filter-label">Type</label>
                    <select name="customer_type" id="type_filter" class="form-control select2">
                        <option value="">All Types</option>
                        <option value="Cash" {{ request('customer_type') == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="Credit" {{ request('customer_type') == 'Credit' ? 'selected' : '' }}>Credit</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="filter-label">Search Customer</label>
                    <select name="customer_id" id="customer_filter" class="form-control select2">
                        <option value="">All Customers</option>
                        @foreach($allCustomers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->customer_name }} ({{ $c->customer_id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-6">
                    <label class="filter-label">From</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>

                <div class="col-md-2 col-6">
                    <label class="filter-label">To</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>

                <div class="col-md-1 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm" title="Search"><i class="fa fa-search"></i></button>
                    <a href="{{ route('customers.index') }}" class="btn btn-light w-100 shadow-sm border" title="Reset"><i class="fa fa-refresh"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Customers Data Table --}}
    <div class="table-container">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        @if(Auth::check() && Auth::user()->hasRole('super admin'))
                            <th>Branch</th>
                        @endif
                        <th>Customer ID</th>
                        <th>Customer Name</th>
                        <th>Contact</th>
                        <th>Zone/Area</th>
                        <th>Type</th>
                        <th class="text-end">Opening</th>
                        <th class="text-end">Closing</th>
                        <th class="text-end">Credit Limit</th>
                        <th>Filer</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            @if(Auth::check() && Auth::user()->hasRole('super admin'))
                                <td><span class="fw-bold text-dark">{{ $customer->branch->name ?? 'N/A' }}</span></td>
                            @endif
                            <td><span class="text-primary fw-bold">{{ $customer->customer_id }}</span></td>
                            <td>
                                <div class="fw-bold text-dark">{{ $customer->customer_name }}</div>
                                <small class="text-muted">{{ $customer->customer_type }} Customer</small>
                            </td>
                            <td>{{ $customer->mobile ?? '-' }}</td>
                            <td><i class="fa-solid fa-location-dot me-1 text-secondary"></i> {{ $customer->address ?? '-' }}</td>
                            <td>
                                <span class="badge-erp {{ strtolower($customer->customer_type) == 'cash' ? 'badge-cash' : 'badge-credit' }}">
                                    {{ $customer->customer_type }}
                                </span>
                            </td>
                            <td class="balance-val">Rs. {{ number_format($customer->opening_balance, 2) }}</td>
                            <td class="balance-val {{ $customer->closing_balance < 0 ? 'balance-negative' : 'balance-positive' }}">
                                Rs. {{ number_format($customer->closing_balance, 2) }}
                            </td>
                            <td class="balance-val text-info">Rs. {{ number_format($customer->credit_limit ?? 0, 2) }}</td>
                            <td><span class="text-capitalize">{{ $customer->filer_type ?? '-' }}</span></td>
                            <td>
                                <span class="badge-erp {{ $customer->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $customer->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- Edit Button --}}
                                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn-erp btn-edit" title="Edit Customer">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    {{-- Quick Opening Balance History Modal Trigger Button --}}
                                    <button type="button" class="btn-erp btn-history btn-view-history" 
                                            data-id="{{ $customer->id }}" 
                                            data-name="{{ $customer->customer_name }}"
                                            data-code="{{ $customer->customer_id }}"
                                            title="View Opening Balance History">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </button>

                                    {{-- Toggle Status --}}
                                    <a href="{{ route('customers.toggleStatus', $customer->id) }}" class="btn-erp btn-toggle" title="Toggle Status">
                                        <i class="fa-solid {{ $customer->status === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    </a>

                                    {{-- Delete --}}
                                    <a href="{{ route('customers.destroy', $customer->id) }}" class="btn-erp btn-delete" 
                                       onclick="return confirm('Delete this customer?')" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center py-5">
                                <img src="https://illustrations.popsy.co/amber/no-data.svg" style="height: 140px;" class="mb-3">
                                <h5 class="text-muted">No customers found matching your criteria.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- QUICK OPENING BALANCE HISTORY MODAL -->
<div class="modal fade" id="openingBalanceHistoryModal" tabindex="-1" role="dialog" aria-labelledby="historyModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header modal-header-custom d-flex align-items-center justify-content-between">
                <h5 class="modal-title text-white mb-0" id="historyModalTitle">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i> 
                    Opening Balance History: <span id="modalCustomerName" class="fw-bold text-warning"></span> 
                    <span id="modalCustomerCode" class="badge bg-light text-dark ms-1"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="background: transparent; border: none; font-size: 1.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="historyLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Fetching edit history log...</p>
                </div>

                <div id="historyContent" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3" style="width: 40px;">#</th>
                                    <th>Edited By (User)</th>
                                    <th>Date & Time</th>
                                    <th class="text-end">Old Balance</th>
                                    <th class="text-end">New Balance</th>
                                    <th class="text-center">Difference</th>
                                    <th class="text-end bg-warning text-dark">Resulting Ledger</th>
                                    <th class="pe-3">Reason / Note</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%',
        allowClear: true,
        placeholder: 'Select One'
    });

    // Dynamic Filter
    function fetchCustomers() {
        const branchId = $('#branch_filter').val();
        const type     = $('#type_filter').val();
        const $customerFilter = $('#customer_filter');

        $customerFilter.empty().append('<option value="">Loading...</option>').trigger('change');

        $.ajax({
            url: "{{ url('sale/customers') }}",
            type: "GET",
            data: { branch_id: branchId, type: type },
            success: function(data) {
                $customerFilter.empty().append('<option value="">All Customers</option>');
                $.each(data, function(index, customer) {
                    $customerFilter.append(`<option value="${customer.id}">${customer.customer_name} (${customer.customer_id})</option>`);
                });
                $customerFilter.trigger('change');
            },
            error: function() {
                $customerFilter.empty().append('<option value="">All Customers</option>').trigger('change');
            }
        });
    }

    $('#branch_filter, #type_filter').on('change', function() {
        fetchCustomers();
    });

    // OPENING BALANCE HISTORY MODAL HANDLER
    $(document).on('click', '.btn-view-history', function() {
        const customerId   = $(this).data('id');
        const customerName = $(this).data('name');
        const customerCode = $(this).data('code');

        $('#modalCustomerName').text(customerName);
        $('#modalCustomerCode').text(customerCode);
        $('#historyLoading').show();
        $('#historyContent').hide();
        $('#historyTableBody').empty();

        $('#openingBalanceHistoryModal').modal('show');

        // Fetch history via AJAX
        $.ajax({
            url: "{{ url('/customers') }}/" + customerId + "/history",
            type: "GET",
            success: function(response) {
                $('#historyLoading').hide();
                $('#historyContent').show();

                if (response.success && response.histories.length > 0) {
                    let rowsHtml = '';
                    $.each(response.histories, function(index, item) {
                        let diffBadge = '';
                        if (item.diff > 0) {
                            diffBadge = `<span class="badge bg-success text-white">+ Rs. ${numberFormat(item.diff)}</span>`;
                        } else if (item.diff < 0) {
                            diffBadge = `<span class="badge bg-danger text-white">- Rs. ${numberFormat(Math.abs(item.diff))}</span>`;
                        } else {
                            diffBadge = `<span class="badge bg-secondary text-white">No Change</span>`;
                        }

                        rowsHtml += `
                            <tr>
                                <td class="ps-3 font-monospace">${index + 1}</td>
                                <td>
                                    <span class="badge bg-light text-dark border font-weight-bold">
                                        <i class="fa fa-user me-1 text-primary"></i> ${item.user_name}
                                    </span>
                                </td>
                                <td><i class="fa fa-clock text-muted me-1"></i> ${item.date}</td>
                                <td class="text-end font-monospace">Rs. ${numberFormat(item.old_opening_balance)}</td>
                                <td class="text-end font-monospace fw-bold text-dark">Rs. ${numberFormat(item.new_opening_balance)}</td>
                                <td class="text-center">${diffBadge}</td>
                                <td class="text-end font-monospace fw-bold text-primary bg-light">Rs. ${numberFormat(item.resulting_closing_balance)}</td>
                                <td class="pe-3 text-secondary">${item.remarks}</td>
                            </tr>
                        `;
                    });
                    $('#historyTableBody').html(rowsHtml);
                } else {
                    $('#historyTableBody').html(`
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fa fa-info-circle fa-2x mb-2 d-block text-secondary"></i>
                                No opening balance edit history found for this customer.
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                $('#historyLoading').hide();
                $('#historyContent').show();
                $('#historyTableBody').html(`
                    <tr>
                        <td colspan="7" class="text-center py-4 text-danger">
                            <i class="fa fa-exclamation-circle me-1"></i> Failed to load history records.
                        </td>
                    </tr>
                `);
            }
        });
    });

    function numberFormat(num) {
        return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
});
</script>
@endsection

@extends('admin_panel.layout.app')

@section('content')
<style>
    .customer-edit-compact {
        padding: 0.75rem 1rem;
        max-width: 100%;
    }

    .compact-card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border: 1px solid #cbd5e1;
        overflow: hidden;
    }

    .compact-card-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        color: #ffffff;
        padding: 0.6rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .compact-card-header h6 {
        margin: 0;
        font-weight: 700;
        font-size: 0.98rem;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .compact-card-body {
        padding: 1rem;
    }

    .compact-section-header {
        font-size: 0.82rem;
        font-weight: 700;
        color: #1e3a5f;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding-bottom: 0.25rem;
        margin-bottom: 0.6rem;
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .form-label-sm {
        font-weight: 600;
        color: #334155;
        font-size: 0.78rem;
        margin-bottom: 0.2rem;
        display: block;
    }

    .form-control-sm, .form-select-sm {
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        padding: 0.3rem 0.5rem;
        font-size: 0.82rem;
        height: auto;
    }

    .form-control-sm:focus, .form-select-sm:focus {
        border-color: #2c5282;
        box-shadow: 0 0 0 2px rgba(44, 82, 130, 0.15);
    }

    .input-group-sm .input-group-text {
        font-size: 0.78rem;
        padding: 0.3rem 0.5rem;
        border-radius: 6px 0 0 6px;
        background-color: #f1f5f9;
        font-weight: 600;
    }

    .input-group-sm .form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .btn-compact {
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.82rem;
        padding: 0.4rem 1.1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-primary-custom {
        background: #1e3a5f;
        border-color: #1e3a5f;
        color: #ffffff;
    }
    .btn-primary-custom:hover {
        background: #2c5282;
        border-color: #2c5282;
        color: #ffffff;
    }

    .history-table-sm th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 0.45rem 0.6rem;
    }

    .history-table-sm td {
        vertical-align: middle;
        padding: 0.45rem 0.6rem;
        font-size: 0.8rem;
    }

    .badge-diff-plus { background-color: #d1fae5; color: #065f46; font-weight: 700; font-size: 0.75rem; }
    .badge-diff-minus { background-color: #fee2e2; color: #991b1b; font-weight: 700; font-size: 0.75rem; }
    .badge-diff-equal { background-color: #f3f4f6; color: #4b5563; font-weight: 600; font-size: 0.75rem; }

    .modal-header-custom {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        color: #ffffff;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="customer-edit-compact">
            
            <div class="compact-card">
                <div class="compact-card-header">
                    <h6 class="mb-0">
                        <i class="fa fa-user-edit"></i> Edit Customer: 
                        <span class="text-warning me-1">{{ $customer->customer_name }}</span>
                        <span class="badge bg-light text-dark ms-1" style="font-size: 0.75rem;">{{ $customer->customer_id }}</span>
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-warning text-dark font-weight-bold btn-compact py-1 px-2" data-toggle="modal" data-target="#quickHistoryModal">
                            <i class="fa-solid fa-clock-rotate-left"></i> History Log (Modal)
                        </button>
                        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-light btn-compact py-1 px-2">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="compact-card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger py-1 px-3 mb-2 small" role="alert">
                            <strong><i class="fa fa-exclamation-triangle me-1"></i> Errors:</strong> {{ implode(', ', $errors->all()) }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success py-1 px-3 mb-2 small" role="alert">
                            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                        @csrf

                        <!-- THREE-COLUMN COMPACT FORM LAYOUT -->
                        <div class="row g-2">
                            
                            <!-- COLUMN 1: BASIC INFO -->
                            <div class="col-md-4 border-end pe-3">
                                <div class="compact-section-header">
                                    <i class="fa fa-id-card text-primary"></i> Basic Profile
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-sm">Customer ID:</label>
                                        <input type="text" class="form-control form-control-sm bg-light fw-bold" name="customer_id" readonly value="{{ $customer->customer_id }}">
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label-sm">Type <span class="text-danger">*</span>:</label>
                                        <select class="form-select form-select-sm" id="customerType" name="customer_type" required>
                                            <option value="credit" {{ old('customer_type', $customer->customer_type) === 'credit' ? 'selected' : '' }}>Credit</option>
                                            <option value="cash" {{ old('customer_type', $customer->customer_type) === 'cash' ? 'selected' : '' }}>Cash</option>
                                        </select>
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label-sm">Customer Name <span class="text-danger">*</span>:</label>
                                        <input type="text" class="form-control form-control-sm" name="customer_name" value="{{ old('customer_name', $customer->customer_name) }}" required>
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label-sm">کسٹمر کا نام (Urdu):</label>
                                        <input type="text" class="form-control form-control-sm text-end" name="customer_name_ur" dir="rtl" value="{{ old('customer_name_ur', $customer->customer_name_ur) }}">
                                    </div>

                                    @if(auth()->user() && auth()->user()->hasRole('super admin'))
                                        <div class="col-6">
                                            <label class="form-label-sm">Branch <span class="text-danger">*</span>:</label>
                                            <select class="form-select form-select-sm" name="branch_id">
                                                @foreach($branches as $b)
                                                    <option value="{{ $b->id }}" {{ old('branch_id', $customer->branch_id) == $b->id ? 'selected' : '' }}>
                                                        {{ $b->name ?? 'Branch '. $b->id }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <input type="hidden" name="branch_id" value="{{ $customer->branch_id ?? auth()->user()->branch_id ?? 0 }}">
                                    @endif

                                    <div class="col-6">
                                        <label class="form-label-sm">NTN / CNIC:</label>
                                        <input type="text" class="form-control form-control-sm" name="cnic" value="{{ old('cnic', $customer->cnic) }}">
                                    </div>
                                </div>
                            </div>

                            <!-- COLUMN 2: CONTACT & ADDRESS -->
                            <div class="col-md-4 border-end px-3">
                                <div class="compact-section-header">
                                    <i class="fa fa-phone-alt text-primary"></i> Contact & Location
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-sm">Mobile Number:</label>
                                        <input type="text" class="form-control form-control-sm" name="mobile" value="{{ old('mobile', $customer->mobile) }}">
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label-sm">Filer Type <span class="text-danger">*</span>:</label>
                                        <select class="form-select form-select-sm" name="filer_type" required>
                                            <option value="filer" {{ old('filer_type', $customer->filer_type) === 'filer' ? 'selected' : '' }}>Filer</option>
                                            <option value="non filer" {{ old('filer_type', $customer->filer_type) === 'non filer' ? 'selected' : '' }}>Non Filer</option>
                                            <option value="exempt" {{ old('filer_type', $customer->filer_type) === 'exempt' ? 'selected' : '' }}>Exempt</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label-sm">Zone / Area:</label>
                                        <input type="text" class="form-control form-control-sm" name="address" value="{{ old('address', $customer->address) }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label-sm">Address Details:</label>
                                        <textarea rows="2" class="form-control form-control-sm" name="address_details">{{ old('address_details', $customer->address_details) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- COLUMN 3: FINANCIAL & OPENING BALANCE -->
                            <div class="col-md-4 ps-3">
                                <div class="compact-section-header">
                                    <i class="fa fa-wallet text-primary"></i> Financials & Balance Edit
                                </div>

                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label-sm text-primary fw-bold">
                                            <i class="fa fa-edit text-primary me-1"></i> Opening Balance (Editable):
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-primary text-white fw-bold">Rs.</span>
                                            <input type="number" class="form-control form-control-sm border-primary fw-bold text-dark" id="opening_balance" name="opening_balance" step="0.01" value="{{ old('opening_balance', $customer->opening_balance ?? 0) }}">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label-sm">Reason / Note for Edit (Optional):</label>
                                        <input type="text" class="form-control form-control-sm" name="opening_balance_note" placeholder="Saved in audit history log">
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label-sm">Current Ledger Balance:</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rs.</span>
                                            <input type="text" class="form-control form-control-sm bg-light fw-bold text-success" value="{{ number_format($customer->closing_balance ?? 0, 2) }}" readonly>
                                        </div>
                                    </div>

                                    <div id="creditFieldsContainer" class="col-6">
                                        <label class="form-label-sm">Credit Limit:</label>
                                        <input type="number" class="form-control form-control-sm" id="credit_limit" name="credit_limit" step="0.01" min="0" value="{{ old('credit_limit', $customer->credit_limit ?? 0) }}" {{ $customer->no_credit_limit ? 'disabled' : '' }}>
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" id="noLimit" name="no_credit_limit" value="1" {{ old('no_credit_limit', $customer->no_credit_limit) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="noLimit">No Limit</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ACTION BUTTONS -->
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <button type="button" class="btn btn-sm btn-outline-purple text-dark btn-compact" data-toggle="collapse" data-target="#inlineHistoryCollapse">
                                <i class="fa-solid fa-history"></i> Toggle History Table Below
                            </button>
                            <div class="d-flex gap-2">
                                <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-compact">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                                <button class="btn btn-primary-custom btn-compact" type="submit">
                                    <i class="fa fa-save"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- COLLAPSIBLE INLINE HISTORY LOG TABLE (TAKES 0 SPACE UNLESS TOGGLED) -->
            <div class="collapse mt-2" id="inlineHistoryCollapse">
                <div class="compact-card">
                    <div class="compact-card-header bg-secondary py-1">
                        <h6 class="mb-0 text-white" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Opening Balance Audit History Log
                        </h6>
                    </div>
                    <div class="compact-card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 history-table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>Edited By (User)</th>
                                        <th>Date & Time</th>
                                        <th class="text-end">Old Opening</th>
                                        <th class="text-end">New Opening</th>
                                        <th class="text-center">Difference</th>
                                        <th class="text-end bg-warning text-dark">Resulting Ledger Balance</th>
                                        <th>Reason / Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customer->openingBalanceHistories as $index => $history)
                                        @php
                                            $diff = (float)$history->new_opening_balance - (float)$history->old_opening_balance;
                                            $resulting = (float)($history->resulting_closing_balance ?? $customer->closing_balance);
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="fa fa-user text-primary me-1"></i>
                                                    {{ $history->user->name ?? 'User #' . ($history->user_id ?? 'Unknown') }}
                                                </span>
                                            </td>
                                            <td><i class="fa fa-clock text-muted me-1"></i> {{ $history->created_at ? $history->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                            <td class="text-end font-monospace">Rs. {{ number_format($history->old_opening_balance, 2) }}</td>
                                            <td class="text-end font-monospace fw-bold">Rs. {{ number_format($history->new_opening_balance, 2) }}</td>
                                            <td class="text-center">
                                                @if($diff > 0)
                                                    <span class="badge badge-diff-plus">+ Rs. {{ number_format($diff, 2) }}</span>
                                                @elseif($diff < 0)
                                                    <span class="badge badge-diff-minus">- Rs. {{ number_format(abs($diff), 2) }}</span>
                                                @else
                                                    <span class="badge badge-diff-equal">No Change</span>
                                                @endif
                                            </td>
                                            <!-- RESULTING CURRENT LEDGER BALANCE COLUMN -->
                                            <td class="text-end font-monospace fw-bold text-dark bg-light">
                                                Rs. {{ number_format($resulting, 2) }}
                                            </td>
                                            <td class="text-secondary small">{{ $history->remarks ?? 'Opening balance updated' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-3 text-muted">
                                                No opening balance edit history found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- QUICK HISTORY MODAL POPUP -->
<div class="modal fade" id="quickHistoryModal" tabindex="-1" role="dialog" aria-labelledby="quickHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header modal-header-custom d-flex align-items-center justify-content-between py-2">
                <h6 class="modal-title text-white mb-0" id="quickHistoryModalLabel">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i> Opening Balance Edit Audit Log: 
                    <span class="text-warning fw-bold">{{ $customer->customer_name }}</span> 
                    <span class="badge bg-light text-dark ms-1">{{ $customer->customer_id }}</span>
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="background: transparent; border: none; font-size: 1.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0 history-table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3" style="width: 40px;">#</th>
                                <th>Edited By (User)</th>
                                <th>Date & Time</th>
                                <th class="text-end">Old Opening</th>
                                <th class="text-end">New Opening</th>
                                <th class="text-center">Difference</th>
                                <th class="text-end bg-warning text-dark">Resulting Ledger</th>
                                <th class="pe-3">Reason / Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->openingBalanceHistories as $index => $history)
                                @php
                                    $diff = (float)$history->new_opening_balance - (float)$history->old_opening_balance;
                                    $resulting = (float)($history->resulting_closing_balance ?? $customer->closing_balance);
                                @endphp
                                <tr>
                                    <td class="ps-3 font-monospace">{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-weight-bold">
                                            <i class="fa fa-user me-1 text-primary"></i>
                                            {{ $history->user->name ?? 'User #' . ($history->user_id ?? 'Unknown') }}
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fa fa-clock text-muted me-1"></i>
                                        {{ $history->created_at ? $history->created_at->format('d M Y, h:i A') : 'N/A' }}
                                    </td>
                                    <td class="text-end font-monospace">
                                        Rs. {{ number_format($history->old_opening_balance, 2) }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-dark">
                                        Rs. {{ number_format($history->new_opening_balance, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($diff > 0)
                                            <span class="badge badge-diff-plus">+ Rs. {{ number_format($diff, 2) }}</span>
                                        @elseif($diff < 0)
                                            <span class="badge badge-diff-minus">- Rs. {{ number_format(abs($diff), 2) }}</span>
                                        @else
                                            <span class="badge badge-diff-equal">No Change</span>
                                        @endif
                                    </td>
                                    <!-- RESULTING CURRENT LEDGER BALANCE -->
                                    <td class="text-end font-monospace fw-bold text-primary bg-light">
                                        Rs. {{ number_format($resulting, 2) }}
                                    </td>
                                    <td class="pe-3 text-secondary small">
                                        {{ $history->remarks ?? 'Opening balance updated' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fa fa-info-circle fa-2x mb-2 d-block text-secondary"></i>
                                        No opening balance edit history found for this customer.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#noLimit').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('#credit_limit').prop('disabled', isChecked);
            if (isChecked) {
                $('#credit_limit').val('');
            }
        });

        $('#customerType').on('change', function() {
            const type = $(this).val();
            if (type === 'cash') {
                $('#creditFieldsContainer').slideUp();
            } else {
                $('#creditFieldsContainer').slideDown();
            }
        });

        if ($('#customerType').val() === 'cash') {
            $('#creditFieldsContainer').hide();
        }
    });
</script>
@endsection

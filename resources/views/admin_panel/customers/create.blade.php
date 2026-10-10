@extends('admin_panel.layout.app')

@section('content')
<style>
    .customer-create-compact {
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
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="customer-create-compact">
            
            <div class="compact-card">
                <div class="compact-card-header">
                    <h6 class="mb-0">
                        <i class="fa fa-user-plus"></i> Add New Customer
                    </h6>
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-light btn-compact py-1 px-2">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
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

                    <form id="customerForm" action="{{ route('customers.store') }}" method="POST">
                        @csrf

                        @php
                            $branchesList = $branches ?? \App\Models\Branch::all();
                        @endphp

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
                                        <input type="text" class="form-control form-control-sm bg-light fw-bold" name="customer_id" readonly value="{{ $latestId }}">
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label-sm">Type <span class="text-danger">*</span>:</label>
                                        <select class="form-select form-select-sm" name="customer_type" id="customer_type_select" required>
                                            <option value="credit">Credit</option>
                                            <option value="cash">Cash</option>
                                        </select>
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label-sm">Customer Name <span class="text-danger">*</span>:</label>
                                        <input type="text" class="form-control form-control-sm" name="customer_name" value="{{ old('customer_name') }}" required placeholder="Enter customer name">
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label-sm">کسٹمر کا نام (Urdu):</label>
                                        <input type="text" class="form-control form-control-sm text-end" name="customer_name_ur" dir="rtl" value="{{ old('customer_name_ur') }}" placeholder="اردو نام">
                                    </div>

                                    @if(auth()->user() && auth()->user()->hasRole('super admin'))
                                        <div class="col-6">
                                            <label class="form-label-sm">Branch <span class="text-danger">*</span>:</label>
                                            <select class="form-select form-select-sm" name="branch_id" id="branch_id_select">
                                                <option value="">Select Branch</option>
                                                @foreach($branchesList as $b)
                                                    <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                                        {{ $b->name ?? $b->branch_name ?? 'Branch '. $b->id }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id ?? 0 }}">
                                    @endif

                                    <div class="col-6">
                                        <label class="form-label-sm">NTN / CNIC No:</label>
                                        <input type="text" class="form-control form-control-sm" name="cnic" value="{{ old('cnic') }}" placeholder="CNIC or NTN">
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
                                        <input type="text" class="form-control form-control-sm" name="mobile" value="{{ old('mobile') }}" placeholder="0300-1234567">
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label-sm">Filer Type <span class="text-danger">*</span>:</label>
                                        <select class="form-select form-select-sm" name="filer_type" required>
                                            <option value="filer">Filer</option>
                                            <option value="non filer">Non Filer</option>
                                            <option value="exempt">Exempt</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label-sm">Zone / Area:</label>
                                        <input type="text" class="form-control form-control-sm" name="address" value="{{ old('address') }}" placeholder="Zone or Area name">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label-sm">Address Details:</label>
                                        <textarea rows="2" class="form-control form-control-sm" name="address_details" placeholder="Full address details">{{ old('address_details') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- COLUMN 3: FINANCIAL & CREDIT SETUP -->
                            <div class="col-md-4 ps-3">
                                <div class="compact-section-header">
                                    <i class="fa fa-wallet text-primary"></i> Financials & Credit Setup
                                </div>

                                <div id="creditFieldsContainer" class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label-sm text-primary fw-bold">Opening Balance (Rs.):</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-primary text-white fw-bold">Rs.</span>
                                            <input type="number" class="form-control form-control-sm border-primary fw-bold" id="opening_balance" name="opening_balance" step="0.01" value="{{ old('opening_balance', '0') }}" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label-sm">Credit Limit (Rs.):</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rs.</span>
                                            <input type="number" class="form-control form-control-sm" id="credit_limit" name="credit_limit" step="0.01" min="0" value="{{ old('credit_limit', '0') }}" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="no_credit_limit" name="no_credit_limit" value="1">
                                            <label class="form-check-label small fw-bold text-dark" for="no_credit_limit">
                                                No Credit Limit (Unlimited Credit)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ACTION BUTTONS -->
                        <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-compact me-2">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                            <button class="btn btn-primary-custom btn-compact" type="submit" id="saveCustomerBtn">
                                <i class="fa fa-save"></i> Save Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Auto fetch customer ID on branch select
            $('#branch_id_select').on('change', function() {
                const branchId = $(this).val();
                const inputId = $('input[name="customer_id"]');
                if (branchId) {
                    $.getJSON('{{ route("customers.nextId") }}', { branch_id: branchId }, function(data) {
                        inputId.val(data.customer_id);
                    });
                } else {
                    inputId.val('');
                }
            });

            // Handle Customer Type Change
            $('#customer_type_select').on('change', function() {
                const type = $(this).val();
                if (type === 'cash') {
                    $('#creditFieldsContainer').slideUp();
                    $('#opening_balance').val('0');
                    $('#credit_limit').val('0');
                } else {
                    $('#creditFieldsContainer').slideDown();
                }
            });

            // Handle No Credit Limit checkbox
            $('#no_credit_limit').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('#credit_limit').prop('disabled', isChecked);
                if (isChecked) {
                    $('#credit_limit').val('');
                }
            });
        });
    </script>
@endsection

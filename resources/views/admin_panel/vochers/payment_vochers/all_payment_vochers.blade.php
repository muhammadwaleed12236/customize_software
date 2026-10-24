@extends('admin_panel.layout.app')
@section('content')

<style>
    /* ===== Compact Table Fix ===== */
    .custom-compact-table { font-size: 13px; }
    .custom-compact-table th, .custom-compact-table td {
        padding: 8px 10px !important;
        vertical-align: middle !important;
        white-space: normal !important;
        word-break: break-word;
    }
    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        justify-content: flex-start;
    }
    .action-buttons form {
        margin: 0;
    }
    .custom-compact-table th {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
    }
    .table-light th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }
    div.dataTables_wrapper div.dataTables_length select {
        width: 75px !important;
        padding: 4px 8px;
        border-radius: 6px;
    }
    div.dataTables_wrapper div.dataTables_filter input {
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
    }
    .badge-branch-chip {
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        background: rgba(30, 58, 95, 0.08);
        color: #1e3a5f;
        border: 1px solid rgba(30, 58, 95, 0.15);
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }
    .branch-filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 18px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }
    .branch-filter-select {
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        min-width: 240px;
        background-color: #f8fafc;
    }
    /* Action Dropdown Styling */
    .manage-dropdown {
        position: relative;
        display: inline-block;
    }
    .btn-manage {
        transition: all 0.2s ease;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #1e293b;
        border-radius: 6px;
        font-weight: 700;
        font-size: 12px;
        padding: 5px 12px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .manage-dropdown:hover .btn-manage, .btn-manage:focus, .manage-dropdown.show .btn-manage {
        background: #1e3a5f;
        color: #ffffff;
        border-color: #1e3a5f;
        box-shadow: 0 4px 10px rgba(30, 58, 95, 0.2);
    }
    .manage-dropdown .dropdown-menu {
        position: absolute !important;
        right: 0 !important;
        left: auto !important;
        top: 100% !important;
        min-width: 185px !important;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        padding: 6px;
        z-index: 1060 !important;
        background: #ffffff !important;
        margin-top: 2px !important;
    }
    .manage-dropdown .dropdown-item {
        padding: 7px 12px !important;
        font-size: 12.5px !important;
        font-weight: 600;
        color: #334155;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap !important;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .manage-dropdown .dropdown-item:hover {
        background-color: #f1f5f9;
        color: #1e3a5f;
    }
    .table-responsive {
        padding-bottom: 60px;
        margin-bottom: -60px;
    }
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            
            @if (session()->has('success'))
                <div class="alert alert-success mt-2"><strong>Success!</strong> {{ session('success') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger mt-2"><strong>Error!</strong> {{ session('error') }}</div>
            @endif

            @php
                $totalPaid = $receipts->where('status', '!=', 'voided')->sum('total_amount');
                $activeCount = $receipts->where('status', '!=', 'voided')->count();
                $voidedCount = $receipts->where('status', '===', 'voided')->count();
            @endphp

            {{-- Branch Filter for Super Admin / Multi-branch users --}}
            @if($isSuperAdmin || (isset($branches) && $branches->count() > 1))
                <div class="branch-filter-card mt-3">
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <div style="width: 34px; height: 34px; background: rgba(30, 58, 95, 0.08); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #1e3a5f;">
                            <i class="fas fa-filter"></i>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">
                                Branch Filter Scope
                            </div>
                            <div style="font-size: 13px; font-weight: 700; color: #1e293b;">
                                Filter Payment Vouchers by Branch
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <label for="branchFilter" class="small font-weight-bold text-muted mb-0 d-none d-sm-inline">
                            Branch:
                        </label>
                        <select id="branchFilter" class="form-select branch-filter-select">
                            <option value="all" {{ ($selectedBranch == 'all' || empty($selectedBranch)) ? 'selected' : '' }}>
                                🌐 All Branches (Consolidated View)
                            </option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ ($selectedBranch == $b->id) ? 'selected' : '' }}>
                                    🏬 {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($selectedBranch && $selectedBranch != 'all')
                            <a href="{{ route('all-Payment-vochers') }}" class="btn btn-sm btn-outline-secondary" title="Clear Filter">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- KPI Summary Cards --}}
            <div class="row g-2 mb-3 mt-2">
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center" style="gap: 14px; border-radius: 10px;">
                        <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(30, 58, 95, 0.1); display: flex; align-items: center; justify-content: center; color: #1e3a5f;">
                            <i class="fas fa-file-invoice-dollar fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small font-weight-bold text-uppercase">Total Vouchers</div>
                            <div class="h5 mb-0 fw-bold">{{ $receipts->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center" style="gap: 14px; border-radius: 10px;">
                        <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(220, 38, 38, 0.1); display: flex; align-items: center; justify-content: center; color: #dc2626;">
                            <i class="fas fa-money-bill-wave fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small font-weight-bold text-uppercase">Total Amount Paid</div>
                            <div class="h5 mb-0 fw-bold text-danger">PKR {{ number_format($totalPaid, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center" style="gap: 14px; border-radius: 10px;">
                        <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(13, 159, 110, 0.1); display: flex; align-items: center; justify-content: center; color: #0d9f6e;">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small font-weight-bold text-uppercase">Active Payments</div>
                            <div class="h5 mb-0 fw-bold text-success">{{ $activeCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center" style="gap: 14px; border-radius: 10px;">
                        <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                            <i class="fas fa-ban fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small font-weight-bold text-uppercase">Voided Payments</div>
                            <div class="h5 mb-0 fw-bold text-warning">{{ $voidedCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mt-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">💵 Payment Vouchers</h5>
                        <small class="text-muted">Manage and track all payment vouchers & receiving proofs</small>
                    </div>
                    @can('payment.voucher.create')
                    <a class="btn btn-primary" href="{{ route('Payment-vochers') }}">
                        <i class="fas fa-plus mr-1"></i> Add Payment Voucher
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="paymentVoucherTable" class="table table-striped table-bordered align-middle custom-compact-table" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 85px; min-width: 80px; text-align: center;">ID</th>
                                    <th>Voucher No</th>
                                    <th>Branch</th>
                                    <th>Receipt Date</th>
                                    <th>Entry Date</th>
                                    <th>Type</th>
                                    <th>Party</th>
                                    <th>Reference No</th>
                                    <th>Remarks</th>
                                    <th>Amount</th>
                                    <th>Total Amount</th>
                                    <th>Created At</th>
                                    <th style="width: 100px; min-width: 95px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($receipts as $item)
                                @php
                                // JSON decode for fields that are stored as arrays
                                $amounts = json_decode($item->amount, true);
                                $amount = is_array($amounts) ? (float)($amounts[0] ?? 0) : (float)$item->amount;

                                $refs = json_decode($item->reference_no, true);
                                $reference = is_array($refs) ? implode(', ', $refs) : $item->reference_no;

                                $narrations = json_decode($item->narration_id, true);
                                $narration = is_array($narrations) ? implode(', ', $narrations) : $item->narration_id;
                                @endphp
                                <tr>
                                    <td data-order="{{ $item->id }}" style="width: 85px; min-width: 80px; text-align: center;">
                                        <span class="badge" style="background: #f1f5f9; color: #1e3a5f; font-family: monospace; font-size: 12.5px; font-weight: 700; padding: 5px 10px; border: 1px solid #cbd5e1; border-radius: 6px;">#{{ $item->id }}</span>
                                    </td>
                                    <td><strong>{{ $item->pvid }}</strong></td>
                                    <td>
                                        <span class="badge-branch-chip">
                                            <i class="fas fa-building text-primary" style="font-size: 10px;"></i>
                                            {{ $item->branch->name ?? $item->branch_name ?? 'Branch #' . $item->branch_id }}
                                        </span>
                                    </td>
                                    <td>{{ $item->receipt_date }}</td>
                                    <td>{{ $item->entry_date }}</td>
                                    <td><span class="badge bg-secondary text-white">{{ $item->type_label }}</span></td>
                                    <td><strong>{{ $item->party_name }}</strong></td>
                                    <td>{{ $reference }}</td>
                                    <td>{{ $item->remarks }}</td>
                                    <td>{{ number_format($amount, 2) }}</td>
                                    <td><strong class="text-success">{{ number_format((float)$item->total_amount, 2) }}</strong></td>
                                    <td>{{ $item->created_at }}</td>
                                    <td style="text-align: center;">
                                        <div class="dropdown manage-dropdown">
                                            <button class="btn btn-manage dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cog text-primary mr-1"></i> Action
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right shadow">
                                                @if($item->status !== 'voided')
                                                    <a class="dropdown-item" href="{{ route('PaymentVoucher.print', $item->id) }}" target="_blank">
                                                        <i class="fas fa-print text-danger" style="width: 16px;"></i> Print Voucher
                                                    </a>

                                                    @can('payment.voucher.create')
                                                        <a class="dropdown-item" href="{{ route('payment-vouchers.edit', $item->id) }}">
                                                        <i class="fas fa-edit text-primary" style="width: 16px;"></i> Edit Voucher
                                                        </a>
                                                    @endcan

                                                    @if($item->receiving_proof)
                                                        <a href="javascript:void(0)" 
                                                            class="dropdown-item text-success btn-view-proof" 
                                                            data-pvid="{{ $item->pvid }}" 
                                                            data-url="{{ asset('uploads/receipts/' . $item->receiving_proof) }}">
                                                            <i class="fas fa-image text-success" style="width: 16px;"></i> View Proof
                                                        </a>
                                                        <a href="javascript:void(0)" 
                                                            class="dropdown-item text-secondary btn-upload-proof" 
                                                            data-id="{{ $item->id }}" 
                                                            data-pvid="{{ $item->pvid }}">
                                                            <i class="fas fa-sync-alt text-secondary" style="width: 16px;"></i> Change Proof
                                                        </a>
                                                    @else
                                                        <a href="javascript:void(0)" 
                                                            class="dropdown-item text-info btn-upload-proof" 
                                                            data-id="{{ $item->id }}" 
                                                            data-pvid="{{ $item->pvid }}">
                                                            <i class="fas fa-upload text-info" style="width: 16px;"></i> Upload Proof
                                                        </a>
                                                    @endif

                                                    @can('payment.voucher.create')
                                                        <div class="dropdown-divider my-1"></div>
                                                        <form action="{{ route('payment-vouchers.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to VOID this payment? Ledgers will be reversed.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger" style="background: none; border: none; width: 100%; text-align: left;">
                                                                <i class="fas fa-ban text-danger" style="width: 16px;"></i> Void Payment
                                                            </button>
                                                        </form>
                                                    @endcan
                                                @else
                                                    <div class="px-3 py-1">
                                                        <span class="badge bg-danger text-white">Voided</span>
                                                    </div>
                                                    <a class="dropdown-item" href="{{ route('PaymentVoucher.print', $item->id) }}" target="_blank">
                                                        <i class="fas fa-print text-muted" style="width: 16px;"></i> Print Voided
                                                    </a>
                                                @endif
                                            </div>
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
    </div>
</div>

{{-- Modal for uploading receiving proof --}}
<div class="modal fade" id="uploadProofModal" tabindex="-1" role="dialog" aria-labelledby="uploadProofModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="uploadProofForm" action="" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content" style="border-radius:12px; overflow:hidden;">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="uploadProofModalLabel">
                        <i class="fas fa-upload mr-2"></i> Upload Receiving Proof
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 py-2 small" style="background:#e8f4ff; color:#0055aa;">
                        <i class="fas fa-info-circle mr-1"></i> Upload the vendor's signed receipt or bank transaction slip for Voucher: <strong id="modal-voucher-pvid"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Select Receipt Image <span class="text-danger">*</span></label>
                        <input type="file" name="receiving_proof" class="form-control" accept="image/*" required>
                        <div class="form-text text-muted mt-1" style="font-size:0.75rem;">Supported formats: JPG, JPEG, PNG, WEBP. Max size: 5MB.</div>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8fafc;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Upload Proof</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal for viewing receiving proof --}}
<div class="modal fade" id="viewProofModal" tabindex="-1" role="dialog" aria-labelledby="viewProofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:12px; overflow:hidden;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="viewProofModalLabel">
                    <i class="fas fa-file-image mr-2"></i> Receiving Proof — <span id="view-voucher-pvid"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-4" style="background:#f8fafc;">
                <img id="proof-image-preview" src="" alt="Receiving Proof" class="img-fluid rounded border shadow-sm" style="max-height: 500px; object-fit: contain;">
            </div>
            <div class="modal-footer" style="background:#f8fafc;">
                <a id="proof-download-link" href="" download class="btn btn-success"><i class="fas fa-download"></i> Download Image</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).ready(function() {
    // Branch filter redirect
    $('#branchFilter').on('change', function() {
        var val = $(this).val();
        window.location.href = "{{ route('all-Payment-vochers') }}?branch_id=" + val;
    });

    // Dropdown hover & click interaction
    $(document).on('mouseenter', '.manage-dropdown', function() {
        $('.manage-dropdown .dropdown-menu').removeClass('show');
        $(this).find('.dropdown-menu').addClass('show');
    }).on('mouseleave', '.manage-dropdown', function() {
        $(this).find('.dropdown-menu').removeClass('show');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.manage-dropdown').length) {
            $('.manage-dropdown .dropdown-menu').removeClass('show');
        }
    });

    // Initialize DataTable with newest on top (ID descending by numeric sequence)
    $('#paymentVoucherTable').DataTable({
        "order": [[ 0, "desc" ]],
        "pageLength": 25,
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search vouchers..."
        }
    });

    // Handle opening upload proof modal
    $(document).on('click', '.btn-upload-proof', function() {
        var voucherId = $(this).data('id');
        var pvid = $(this).data('pvid');
        
        // Dynamically set form action
        var actionUrl = '/payment-vouchers/' + voucherId + '/upload-proof';
        $('#uploadProofForm').attr('action', actionUrl);
        $('#modal-voucher-pvid').text(pvid);
        
        // Open the modal (Bootstrap 4 format)
        $('#uploadProofModal').modal('show');
    });

    // Handle opening view proof modal
    $(document).on('click', '.btn-view-proof', function() {
        var pvid = $(this).data('pvid');
        var imgUrl = $(this).data('url');
        
        $('#view-voucher-pvid').text(pvid);
        $('#proof-image-preview').attr('src', imgUrl);
        $('#proof-download-link').attr('href', imgUrl);
        
        // Open the modal (Bootstrap 4 format)
        $('#viewProofModal').modal('show');
    });
});
</script>
@endsection

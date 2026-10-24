@extends('admin_panel.layout.app')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

    .pv-page { background: #f0f4f8; min-height: 100vh; padding: 1.5rem; }

    /* Header */
    .pv-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        border-radius: 14px;
        padding: 1.25rem 1.75rem;
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 6px 20px rgba(37,99,235,0.25);
    }
    .pv-header h3 { color: #fff; font-weight: 700; font-size: 1.3rem; margin: 0; }
    .pv-header p  { color: rgba(255,255,255,0.75); margin: 0; font-size: 0.82rem; }
    .pv-badge {
        background: rgba(255,255,255,0.18);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 8px;
        padding: 0.5rem 1.25rem;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 1px;
    }

    /* Card */
    .pv-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.07);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .pv-card-header {
        padding: 0.9rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 600;
        font-size: 0.92rem;
        border-bottom: 1px solid #f0f4f8;
    }
    .pv-card-header.green  { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .pv-card-header.blue   { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .pv-card-header.gray   { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
    .pv-card-body { padding: 1.5rem; }

    /* Form Controls */
    .form-label { font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.35rem; display: block; }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        padding: 0.55rem 0.75rem;
        font-size: 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        width: 100%;
        background: #fff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        outline: none;
    }
    .form-control[readonly] { background: #f8fafc; color: #94a3b8; }

    /* Balance Info Strip */
    .balance-strip {
        background: linear-gradient(90deg, #eff6ff, #f0fdf4);
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 0.75rem 1.25rem;
        margin-top: 0.75rem;
        display: flex;
        gap: 2rem;
        align-items: center;
        flex-wrap: wrap;
        font-size: 0.83rem;
    }
    .balance-strip .bl-item { display: flex; flex-direction: column; gap: 2px; }
    .balance-strip .bl-label { color: #64748b; font-weight: 500; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; }
    .balance-strip .bl-value { font-weight: 700; font-size: 1rem; color: #1e293b; }

    /* TO Table */
    .to-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .to-table thead th {
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.7rem 0.75rem;
        border-bottom: 2px solid #bfdbfe;
    }
    .to-table tbody td { padding: 0.5rem 0.5rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
    .to-table tbody tr:last-child td { border-bottom: none; }

    /* Row Number */
    .row-num {
        width: 28px; height: 28px;
        background: #eff6ff; color: #2563eb;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
    }

    /* Add Row Button */
    .add-row-btn {
        border: 2px dashed #93c5fd;
        background: #eff6ff;
        color: #2563eb;
        border-radius: 10px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        width: 100%;
        margin-top: 0.75rem;
        transition: all 0.2s;
        display: flex; align-items: center; justify-content: center; gap: 0.4rem;
    }
    .add-row-btn:hover { background: #dbeafe; border-color: #3b82f6; transform: translateY(-1px); }

    /* Total Box */
    .total-box {
        background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 100%);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        color: #fff;
    }
    .total-box .total-label { font-size: 0.82rem; opacity: 0.8; margin-bottom: 0.2rem; }
    .total-box .total-value { font-size: 2rem; font-weight: 800; letter-spacing: -0.02em; }

    /* Buttons */
    .btn-save {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff; border: none;
        border-radius: 10px; padding: 0.75rem 2rem;
        font-weight: 700; font-size: 0.95rem;
        cursor: pointer; width: 100%;
        box-shadow: 0 4px 12px rgba(37,99,235,0.35);
        transition: all 0.2s;
        display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(37,99,235,0.4); }
    .btn-outline-link {
        color: #64748b; text-decoration: none; border: 1.5px solid #e2e8f0;
        border-radius: 10px; padding: 0.7rem 1.5rem;
        font-weight: 600; font-size: 0.875rem; width: 100%;
        display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        transition: all 0.2s; background: #fff;
    }
    .btn-outline-link:hover { background: #f8fafc; border-color: #cbd5e1; }
    .btn-remove-row { border: none; background: none; color: #dc2626; cursor: pointer; padding: 0.3rem; border-radius: 6px; }
    .btn-remove-row:hover { background: #fee2e2; }

    .pv-alert { border-radius: 10px; padding: 0.85rem 1.2rem; font-size: 0.875rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .pv-alert.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .pv-alert.error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .pv-alert.warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
</style>

<div class="pv-page">
    <div class="container-fluid" style="max-width: 1200px;">

        {{-- Header --}}
        <div class="pv-header">
            <div>
                <h3><i class="bi bi-pencil-square me-2"></i>Edit Payment Voucher</h3>
                <p>Modify payment details. Upon saving, all ledger balances and transaction records will automatically synchronize.</p>
            </div>
            <div class="pv-badge">#{{ $voucher->pvid }}</div>
        </div>

        @if(session('success'))
            <div class="pv-alert success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="pv-alert error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
        @endif

        <div class="pv-alert warning">
            <i class="bi bi-info-circle-fill"></i>
            <span><strong>ERP Ledger Sync:</strong> Updating this voucher will automatically reverse previous ledger postings for voucher #{{ $voucher->pvid }} and re-post the new values accurately.</span>
        </div>

        <form action="{{ route('payment-vouchers.update', $voucher->id) }}" method="POST" id="paymentForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="pvid" value="{{ $voucher->pvid }}">

            {{-- --- VOUCHER META --- --}}
            <div class="pv-card">
                <div class="pv-card-header gray">
                    <i class="bi bi-calendar3"></i> Voucher Information
                </div>
                <div class="pv-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Receipt Date <span class="text-danger">*</span></label>
                            <input type="date" name="receipt_date" class="form-control" value="{{ $voucher->receipt_date }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Entry Date</label>
                            <input type="date" name="entry_date" class="form-control" value="{{ $voucher->entry_date ?? now()->toDateString() }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Voucher Remarks</label>
                            <input type="text" name="remarks" class="form-control" value="{{ $voucher->remarks }}" placeholder="e.g. Payment made to vendor for raw materials...">
                        </div>
                        @if($isSuperAdmin)
                        <div class="col-md-3">
                            <label class="form-label">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" class="form-select" required>
                                <option value="">-- Select Branch --</option>
                                @foreach ($Branch as $b)
                                    <option value="{{ $b->id }}" {{ $currentBranch == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="branch_id" id="branch_id" value="{{ $currentBranch }}">
                        @endif
                    </div>
                </div>
            </div>

                        {{-- ─── TO (Paid To Party) ─── --}}
            <div class="pv-card">
                <div class="pv-card-header green">
                    <i class="bi bi-box-arrow-right"></i> TO &mdash; Paid To Party
                    <span style="font-weight: 400; font-size: 0.78rem; margin-left: auto; opacity: 0.8;">Select vendors/parties receiving payment</span>
                </div>
                <div class="pv-card-body" style="padding: 1rem;">
                    <div class="table-responsive">
                        <table class="to-table" id="partyTable">
                            <thead>
                                <tr>
                                    <th style="width:3%">#</th>
                                    <th style="width:20%">Type</th>
                                    <th style="width:30%">Party / Vendor Name</th>
                                    <th style="width:16%">Mobile / Code</th>
                                    <th style="width:14%">Current Balance</th>
                                    <th style="width:14%">Payment Amount (Rs.)</th>
                                    <th style="width:3%"></th>
                                </tr>
                            </thead>
                            <tbody id="partyRows">
                                @foreach($partyRows as $idx => $pRow)
                                <tr class="party-row">
                                    <td><div class="row-num party-row-num">{{ $idx + 1 }}</div></td>
                                    <td>
                                        <select name="party_type[]" class="form-select partyTypeSelect" required>
                                            <option value="">-- Select Type --</option>
                                            @foreach ($AccountHeads as $head)
                                                <option value="{{ $head->id }}" data-kind="account" {{ (string)$pRow['type'] === (string)$head->id ? 'selected' : '' }}>{{ $head->name }}</option>
                                            @endforeach
                                            <option value="vendor" data-kind="party" {{ $pRow['type'] === 'vendor' ? 'selected' : '' }}>Vendor</option>
                                            <option value="customer" data-kind="party" {{ in_array($pRow['type'], ['customer', 'walkin']) ? 'selected' : '' }}>Customer</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="party_id[]" class="form-select partySelect" required data-selected="{{ $pRow['id'] }}">
                                            @if($pRow['id'])
                                                <option value="{{ $pRow['id'] }}" selected>{{ $pRow['name'] ?: 'Selected Party' }}</option>
                                            @else
                                                <option value="">-- Select Party --</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="party_tel[]" class="form-control partyTel" value="{{ $pRow['tel'] }}" readonly placeholder="Auto-filled">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control partyBal text-end" value="{{ number_format($pRow['balance'], 2) }}" readonly placeholder="0.00">
                                    </td>
                                    <td>
                                        <input type="number" name="party_amount[]" step="0.01" min="0" class="form-control partyAmount" value="{{ $pRow['amount'] }}" placeholder="0.00" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn-remove-row removePartyRow" title="Remove party">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8fafc; font-weight: 600;">
                                    <td colspan="5" class="text-end" style="padding: 0.6rem 1rem;">Total Party Payment:</td>
                                    <td style="padding: 0.6rem;">
                                        <span id="partyTotalDisplay" style="color: #15803d; font-size: 1rem; font-weight: 700;">Rs. {{ number_format($voucher->total_amount, 2) }}</span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <button type="button" class="add-row-btn mt-2" id="addPartyRowBtn">
                        <i class="bi bi-plus-lg"></i> Add Another Vendor / Party Row
                    </button>
                </div>
            </div>

            {{-- --- FROM (Paid From Accounts) --- --}}
            <div class="pv-card">
                <div class="pv-card-header blue">
                    <i class="bi bi-bank"></i> FROM &mdash; Paid From Accounts (Cash / Bank)
                    <span style="font-weight: 400; font-size: 0.78rem; margin-left: auto; opacity: 0.8;">You can split the amount into multiple accounts</span>
                </div>
                <div class="pv-card-body" style="padding: 1rem;">
                    <div class="table-responsive">
                        <table class="to-table" id="voucherTable">
                            <thead>
                                <tr>
                                    <th style="width:3%">#</th>
                                    <th style="width:24%">Narration</th>
                                    <th style="width:14%">Reference #</th>
                                    <th style="width:22%">Account Head</th>
                                    <th style="width:24%">Account Name</th>
                                    <th style="width:10%">Amount (Rs.)</th>
                                    <th style="width:3%"></th>
                                </tr>
                            </thead>
                            <tbody id="voucherRows">
                                @forelse ($rows as $index => $row)
                                <tr class="voucher-row">
                                    <td><div class="row-num">{{ $index + 1 }}</div></td>
                                    <td>
                                        <div class="d-flex flex-nowrap gap-1">
                                            <select name="narration_id[]" class="form-select narrationSelect" required>
                                                <option value="">-- Select Narration --</option>
                                                @foreach ($narrations as $id => $name)
                                                    <option value="{{ $id }}" {{ (string)$row['narration_id'] === (string)$id ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-outline-primary addNarrationBtn" type="button" title="Create New Narration" style="flex-shrink: 0;">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td><input name="reference_no[]" type="text" class="form-control" value="{{ $row['reference_no'] }}" placeholder="Ref / Cheque #"></td>
                                    <td>
                                        <select name="row_account_head[]" class="form-select rowAccountHead" required>
                                            <option value="">-- Select Head --</option>
                                            @foreach ($AccountHeads as $head)
                                                <option value="{{ $head->id }}" {{ (string)$row['row_account_head'] === (string)$head->id ? 'selected' : '' }}>{{ $head->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="row_account_id[]" class="form-select rowAccountSub" required>
                                            <option value="">-- Select Account --</option>
                                            @if(!empty($row['sub_accounts']))
                                                @foreach ($row['sub_accounts'] as $sub)
                                                    <option value="{{ $sub->id }}" {{ (string)$row['row_account_id'] === (string)$sub->id ? 'selected' : '' }}>{{ $sub->title }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </td>
                                    <td><input name="amount[]" type="number" step="0.01" min="0" class="form-control amount" value="{{ $row['amount'] }}" placeholder="0.00" required></td>
                                    <td>
                                        <button type="button" class="btn-remove-row removeRow" title="Remove row">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr class="voucher-row">
                                    <td><div class="row-num">1</div></td>
                                    <td>
                                        <div class="d-flex flex-nowrap gap-1">
                                            <select name="narration_id[]" class="form-select narrationSelect" required>
                                                <option value="">-- Select Narration --</option>
                                                @foreach ($narrations as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-outline-primary addNarrationBtn" type="button" title="Create New Narration" style="flex-shrink: 0;">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td><input name="reference_no[]" type="text" class="form-control" placeholder="Ref / Cheque #"></td>
                                    <td>
                                        <select name="row_account_head[]" class="form-select rowAccountHead" required>
                                            <option value="">-- Select Head --</option>
                                            @foreach ($AccountHeads as $head)
                                                <option value="{{ $head->id }}">{{ $head->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="row_account_id[]" class="form-select rowAccountSub" required>
                                            <option value="">-- Select Account --</option>
                                        </select>
                                    </td>
                                    <td><input name="amount[]" type="number" step="0.01" min="0" class="form-control amount" placeholder="0.00" required></td>
                                    <td>
                                        <button type="button" class="btn-remove-row removeRow" title="Remove row">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="add-row-btn" id="addRowBtn">
                        <i class="bi bi-plus-lg"></i> Add Another Account Row
                    </button>
                </div>
            </div>

            {{-- --- FOOTER: Total + Actions --- --}}
            <div class="row g-3">
                <div class="col-md-7">
                    <div class="pv-card">
                        <div class="pv-card-header gray"><i class="bi bi-shield-check"></i> Safe Update Verification</div>
                        <div class="pv-card-body" style="padding: 1rem 1.5rem;">
                            <p style="margin:0; font-size: 0.85rem; color: #475569;">
                                Any modified amount or account will update General Ledger entries and customer/vendor balances in real time.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="total-box mb-3">
                        <div class="total-label"><i class="bi bi-cash-stack me-1"></i> Total Paid Amount</div>
                        <div class="total-value">Rs. <span id="totalAmountDisplay">{{ number_format($voucher->total_amount, 2) }}</span></div>
                        <input type="hidden" name="total_amount" id="totalAmount" value="{{ $voucher->total_amount }}">
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" class="btn-save">
                            <i class="bi bi-check2-all"></i> Save Changes &amp; Synchronize
                        </button>
                        <a href="{{ route('all-Payment-vochers') }}" class="btn-outline-link">
                            <i class="bi bi-arrow-left"></i> Cancel &amp; Back to Payments
                        </a>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

{{-- Quick Add Narration Modal --}}
<div class="modal fade" id="addNarrationModal" tabindex="-1" aria-labelledby="addNarrationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 12px;">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="addNarrationModalLabel"><i class="bi bi-chat-left-text me-2"></i>Add New Narration</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Narration Text <span class="text-danger">*</span></label>
          <input type="text" id="new_narration_text" class="form-control" placeholder="e.g. Paid via Online Bank Transfer...">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveNarrationModalBtn">Save Narration</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('js')
<script>
$(document).ready(function () {

    var activeNarrationSelect = null;

    // ─── PARTY AJAX LOADER FUNCTION ─────────────────────────────────────────
    function loadPartiesForRow($row, defaultId) {
        var $typeSelect  = $row.find('.partyTypeSelect');
        var $partySelect = $row.find('.partySelect');
        var type         = $typeSelect.val();
        var kind         = $typeSelect.find(':selected').data('kind');
        var branchId     = $('#branch_id').val() || '';

        $partySelect.html('<option value="" class="select-loading">Loading...</option>');

        if (!type) {
            $partySelect.html('<option value="">— Select Type First —</option>');
            $row.find('.partyTel').val('');
            $row.find('.partyBal').val('');
            return;
        }

        if (kind === 'party') {
            $.getJSON('{{ route("receipt.party.list") }}', { type: type, branch_id: branchId }, function (data) {
                var html = '<option value="">— Select ' + (type === 'vendor' ? 'Vendor' : 'Customer') + ' —</option>';
                data.forEach(function (item) {
                    var isSel = (defaultId && String(defaultId) === String(item.id)) ? 'selected' : '';
                    html += '<option value="' + item.id + '" '
                        + 'data-bal="' + (item.closing_balance || 0) + '" '
                        + 'data-mobile="' + (item.mobile || '') + '" ' + isSel + '>'
                        + item.text + '</option>';
                });
                $partySelect.html(html);

                if (defaultId) {
                    $partySelect.val(defaultId);
                }
                $partySelect.trigger('change');
            }).fail(function () {
                $partySelect.html('<option value="">Error loading data</option>');
            });
        } else if (kind === 'account') {
            $.getJSON('{{ url("get-accounts-by-head") }}/' + type, { branch_id: branchId }, function (data) {
                var html = '<option value="">— Select Account —</option>';
                data.forEach(function (acc) {
                    var isSel = (defaultId && String(defaultId) === String(acc.id)) ? 'selected' : '';
                    html += '<option value="' + acc.id + '" '
                        + 'data-bal="' + (acc.closing_balance !== undefined ? acc.closing_balance : (acc.opening_balance || 0)) + '" '
                        + 'data-mobile="' + (acc.account_code || '') + '" ' + isSel + '>'
                        + acc.title + ' (' + (acc.account_code || '') + ')</option>';
                });
                $partySelect.html(html);

                if (defaultId) {
                    $partySelect.val(defaultId);
                }
                $partySelect.trigger('change');
            }).fail(function () {
                $partySelect.html('<option value="">Error loading accounts</option>');
            });
        }
    }

    // ─── PARTY TYPE CHANGE ───────────────────────────────────────────────────
    $(document).on('change', '.partyTypeSelect', function () {
        var $row = $(this).closest('.party-row');
        loadPartiesForRow($row);
    });

    // ─── PARTY SELECT CHANGE ─────────────────────────────────────────────────
    $(document).on('change', '.partySelect', function () {
        var $row = $(this).closest('.party-row');
        var $opt = $(this).find(':selected');
        var bal  = parseFloat($opt.data('bal')) || 0;
        var mob  = $opt.data('mobile') || '';

        if (mob) $row.find('.partyTel').val(mob);
        if ($opt.data('bal') !== undefined) $row.find('.partyBal').val(formatNum(bal));
    });

    // Branch change → reload all parties
    $('#branch_id').on('change', function () {
        $('.party-row').each(function () {
            var $row = $(this);
            var currentVal = $row.find('.partySelect').val();
            loadPartiesForRow($row, currentVal);
        });
        $('.rowAccountHead').trigger('change');
    });

    // Populate full dropdown options for existing party rows on edit page
    $('.party-row').each(function () {
        var $row = $(this);
        var existingSelectedId = $row.find('.partySelect').data('selected');
        loadPartiesForRow($row, existingSelectedId);
    });

    // ─── ADD PARTY ROW ───────────────────────────────────────────────────────
    $('#addPartyRowBtn').on('click', function () {
        var rowCount = $('.party-row').length + 1;
        var headOptions = '';
        @foreach ($AccountHeads as $head)
            headOptions += '<option value="{{ $head->id }}" data-kind="account">{{ $head->name }}</option>';
        @endforeach

        var $newPartyRow = $(`
            <tr class="party-row">
                <td><div class="row-num party-row-num">${rowCount}</div></td>
                <td>
                    <select name="party_type[]" class="form-select partyTypeSelect" required>
                        <option value="">— Select Type —</option>
                        ${headOptions}
                        <option value="vendor" data-kind="party" selected>Vendor</option>
                        <option value="customer" data-kind="party">Customer</option>
                    </select>
                </td>
                <td>
                    <select name="party_id[]" class="form-select partySelect" required>
                        <option value="">Loading...</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="party_tel[]" class="form-control partyTel" readonly placeholder="Auto-filled">
                </td>
                <td>
                    <input type="text" class="form-control partyBal text-end" readonly placeholder="0.00">
                </td>
                <td>
                    <input type="number" name="party_amount[]" step="0.01" min="0" class="form-control partyAmount" placeholder="0.00" required>
                </td>
                <td>
                    <button type="button" class="btn-remove-row removePartyRow" title="Remove party">
                        <i class="bi bi-trash3"></i>
                    </button>
                </td>
            </tr>
        `);

        $('#partyRows').append($newPartyRow);
        renumberPartyRows();
        loadPartiesForRow($newPartyRow);
    });

    // ─── REMOVE PARTY ROW ────────────────────────────────────────────────────
    $(document).on('click', '.removePartyRow', function () {
        if ($('.party-row').length > 1) {
            $(this).closest('tr').remove();
            renumberPartyRows();
            calculatePartyTotal();
        } else {
            alert('At least one party row is required.');
        }
    });

    function renumberPartyRows() {
        $('.party-row').each(function (i) {
            $(this).find('.party-row-num').text(i + 1);
        });
    }

    // Account Head change
    $(document).on('change', '.rowAccountHead', function () {
        var headId = $(this).val();
        var $rowAccountSub = $(this).closest('tr').find('.rowAccountSub');

        if (!headId) {
            $rowAccountSub.html('<option value="">— Select Account —</option>');
            return;
        }

        $rowAccountSub.html('<option value="">Loading...</option>');

        $.ajax({
            url: "/get-accounts-by-head/" + headId,
            type: "GET",
            success: function (data) {
                var options = '<option value="">— Select Account —</option>';
                $.each(data, function (index, item) {
                    options += `<option value="${item.id}">${item.title}</option>`;
                });
                $rowAccountSub.html(options);
            }
        });
    });

    // Add Row
    $('#addRowBtn').on('click', function () {
        var rowCount = $('#voucherRows tr').length + 1;
        var firstRow = $('#voucherRows tr:first');
        var newRow = firstRow.clone();

        newRow.find('.row-num').text(rowCount);
        newRow.find('input').val('');
        newRow.find('select').val('');
        newRow.find('.rowAccountSub').html('<option value="">— Select Account —</option>');

        $('#voucherRows').append(newRow);
        calculateTotal();
    });

    // Remove Row
    $(document).on('click', '.removeRow', function () {
        if ($('#voucherRows tr').length > 1) {
            $(this).closest('tr').remove();
            $('#voucherRows tr').each(function (index) {
                $(this).find('.row-num').text(index + 1);
            });
            calculateTotal();
        } else {
            alert('At least one account row is required.');
        }
    });

    // Amount change & sync
    $(document).on('input', '.partyAmount', function () {
        calculatePartyTotal();
    });

    $(document).on('input', '.amount', function () {
        calculateTotal();
    });

    function calculatePartyTotal() {
        var partyTotal = 0;
        $('.partyAmount').each(function () {
            partyTotal += parseFloat($(this).val()) || 0;
        });
        $('#partyTotalDisplay').text('Rs. ' + formatNum(partyTotal));

        if ($('#voucherRows tr').length === 1) {
            $('#voucherRows tr:first .amount').val(partyTotal > 0 ? partyTotal.toFixed(2) : '');
            calculateTotal();
        }
    }

    function calculateTotal() {
        var total = 0;
        $('.amount').each(function () {
            var val = parseFloat($(this).val()) || 0;
            total += val;
        });
        $('#totalAmount').val(total.toFixed(2));
        $('#totalAmountDisplay').text(formatNum(total));
    }

    function formatNum(n) {
        return parseFloat(n).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Modal Narration
    $(document).on('click', '.addNarrationBtn', function () {
        activeNarrationSelect = $(this).closest('td').find('.narrationSelect');
        $('#new_narration_text').val('');
        var modal = new bootstrap.Modal(document.getElementById('addNarrationModal'));
        modal.show();
    });

    $('#saveNarrationModalBtn').on('click', function () {
        var text = $('#new_narration_text').val().trim();
        if (!text) {
            alert('Please enter narration text.');
            return;
        }

        $.ajax({
            url: "{{ route('store.narration.ajax') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                narration: text
            },
            success: function (res) {
                if (res.success) {
                    var newOpt = new Option(res.text, res.id, true, true);
                    $('.narrationSelect').append(new Option(res.text, res.id));
                    if (activeNarrationSelect) {
                        activeNarrationSelect.append(newOpt).trigger('change');
                    }
                    var modalEl = document.getElementById('addNarrationModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                }
            }
        });
    });

    // ─── FORM SUBMIT VALIDATION ──────────────────────────────────────────────
    $('#paymentForm').on('submit', function () {
        var partyTotal   = 0;
        var accountTotal = 0;

        $('.partyAmount').each(function () {
            partyTotal += parseFloat($(this).val()) || 0;
        });

        $('.amount').each(function () {
            accountTotal += parseFloat($(this).val()) || 0;
        });

        if (partyTotal <= 0) {
            alert('Please enter at least one valid party payment amount greater than zero.');
            return false;
        }

        if (accountTotal <= 0) {
            alert('Total account amount must be greater than zero.');
            return false;
        }

        if (Math.abs(partyTotal - accountTotal) > 0.01) {
            alert('Total Party Amount (Rs. ' + partyTotal.toFixed(2) + ') must match Total Account Amount (Rs. ' + accountTotal.toFixed(2) + '). Please balance both sides before saving.');
            return false;
        }

        return true;
    });

    calculatePartyTotal();
    calculateTotal();
});
</script>
@endsection

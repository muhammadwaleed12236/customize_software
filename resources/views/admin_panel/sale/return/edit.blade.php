@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 1100px;">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fa fa-undo-alt text-primary me-2"></i> Edit Sale Return
                    <span class="badge bg-secondary ms-2">#{{ $saleReturn->reference ?? $saleReturn->id }}</span>
                </h5>
                <small class="text-muted">
                    Original Invoice: <strong>{{ $saleReturn->sale?->invoice_no ?? 'N/A' }}</strong>
                </small>
            </div>
            <div>
                <a href="{{ route('sale.returns.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                    <i class="fa fa-arrow-left me-1"></i> Back to Sale Returns
                </a>
            </div>
        </div>

        <div class="card-body p-4">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fa fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Information Note --}}
            <div class="alert alert-info border-0 d-flex align-items-center mb-4">
                <i class="fa fa-info-circle fs-4 me-3 text-info"></i>
                <div>
                    <strong>Data Integrity Protection:</strong>
                    Only the <strong>Return Date</strong> can be updated. Products, quantities, prices, and accounting totals are locked.
                </div>
            </div>

            <form action="{{ route('sale.returns.update', $saleReturn->id) }}" method="POST" id="editSaleReturnForm">
                @csrf
                @method('PUT')

                {{-- Header Details --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Return Reference</label>
                        <input type="text" class="form-control bg-light" value="{{ $saleReturn->reference ?? ('RET-' . $saleReturn->id) }}" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Original Invoice No</label>
                        <input type="text" class="form-control bg-light" value="{{ $saleReturn->sale?->invoice_no ?? 'N/A' }}" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Customer</label>
                        <input type="text" class="form-control bg-light" value="{{ $saleReturn->sale?->customer?->customer_name ?? $saleReturn->customerRelation?->customer_name ?? $saleReturn->customer ?? 'Walk-in Customer' }}" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-primary small fw-bold">
                            <i class="fa fa-calendar-alt me-1"></i> Return Date (Editable) *
                        </label>
                        <input type="date" 
                               name="return_date" 
                               id="return_date" 
                               class="form-control border-primary fw-bold" 
                               style="background-color: #fff9e6;"
                               value="{{ old('return_date', $saleReturn->created_at ? $saleReturn->created_at->format('Y-m-d') : date('Y-m-d')) }}" 
                               required>
                    </div>
                </div>

                {{-- Return Note --}}
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">Return Note / Reason</label>
                    <input type="text" class="form-control bg-light" value="{{ $saleReturn->return_note ?? 'No note provided' }}" readonly>
                </div>

                {{-- Returned Items Table --}}
                <div class="card border rounded-3 mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 fw-bold text-dark small text-uppercase">
                            <i class="fa fa-box me-1"></i> Returned Products (Locked)
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0 align-middle">
                            <thead class="table-light text-center small">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th class="text-start">Product</th>
                                    <th>Code</th>
                                    <th>Brand / UOM</th>
                                    <th>Unit</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-center">Return Qty</th>
                                    <th class="text-end">Net Total</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @forelse($items as $idx => $item)
                                    <tr>
                                        <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                        <td class="fw-bold text-dark text-start">{{ $item['name'] }}</td>
                                        <td class="text-center text-muted">{{ $item['code'] ?: '-' }}</td>
                                        <td class="text-center text-muted">{{ $item['brand'] ?: '-' }}</td>
                                        <td class="text-center text-muted">{{ $item['unit'] ?: '-' }}</td>
                                        <td class="text-end">{{ number_format($item['price'], 2) }}</td>
                                        <td class="text-end text-muted">{{ number_format($item['discount'], 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">{{ $item['qty'] }}</span>
                                        </td>
                                        <td class="text-end fw-bold text-dark">{{ number_format($item['total'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-3">No individual items recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Financial Totals Summary --}}
                <div class="row justify-content-end mb-4">
                    <div class="col-md-5">
                        <div class="card bg-light border-0 shadow-sm p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Items Returned:</span>
                                <span class="fw-bold">{{ $saleReturn->total_items }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <span>{{ number_format((float)($saleReturn->total_bill_amount ?? $saleReturn->total_net), 2) }}</span>
                            </div>
                            @if(!empty($saleReturn->total_extradiscount) && (float)$saleReturn->total_extradiscount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Extra Cost / Discount:</span>
                                <span class="text-danger">- {{ number_format((float)$saleReturn->total_extradiscount, 2) }}</span>
                            </div>
                            @endif
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold fs-6">Net Return Total:</span>
                                <span class="fw-bold fs-6 text-success">Rs. {{ number_format((float)$saleReturn->total_net, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('sale.returns.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fa fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSubmitReturn">
                        <i class="fa fa-save me-1"></i> Update Return Date
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const returnDateInput = document.getElementById('return_date');
    const form = document.getElementById('editSaleReturnForm');
    const btnSubmit = document.getElementById('btnSubmitReturn');
    const saleDateStr = "{{ $saleDate ?? '' }}";
    const saleDateFormatted = "{{ $saleDateFormatted ?? '' }}";

    function checkReturnDate(e) {
        if (!saleDateStr || !returnDateInput) return true;

        const selectedDate = returnDateInput.value;
        if (selectedDate && selectedDate < saleDateStr) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            const alertMessage = 'Return date cannot be earlier than the original sale date (' + saleDateFormatted + ').';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Return Date',
                    text: alertMessage,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            } else {
                alert(alertMessage);
            }

            returnDateInput.classList.add('is-invalid');
            returnDateInput.focus();
            return false;
        }

        returnDateInput.classList.remove('is-invalid');
        return true;
    }

    if (btnSubmit) {
        btnSubmit.addEventListener('click', function (e) {
            if (!checkReturnDate(e)) {
                return false;
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            if (!checkReturnDate(e)) {
                return false;
            }
        });
    }

    if (returnDateInput) {
        returnDateInput.addEventListener('input', function () {
            this.classList.remove('is-invalid');
        });
    }
});
</script>
@endsection

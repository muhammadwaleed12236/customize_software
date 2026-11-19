@extends('admin_panel.layout.app')
@section('content')

<div class="container-fluid">
    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mt-3" role="alert">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mt-3" role="alert">
            <i class="fa fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Sale Returns</h5>
            <div>
                <a href="{{ url('sale') }}" class="btn btn-secondary btn-sm me-1">
                    <i class="fa fa-arrow-left me-1"></i> Back to Sales
                </a>
                <a href="{{ route('sale.add') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus me-1"></i> Add Sale
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Total Items</th>
                            <th>Total Net</th>
                            <th>Return Note</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesReturns as $return)
                        <tr>
                            <td>{{ $return->id }}</td>
                            <td>
                                <strong>{{ $return->sale->invoice_no ?? 'N/A' }}</strong>
                                @if(!empty($return->reference))
                                    <br><small class="text-muted">{{ $return->reference }}</small>
                                @endif
                            </td>
                            <td>{{ optional($return->sale->customer)->customer_name ?? $return->sale->sub_customer ?? $return->customer ?? 'Walk-in Customer' }}</td>
                            <td>{{ $return->total_items }}</td>
                            <td>{{ number_format((float)$return->total_net, 2) }}</td>
                            <td>{{ $return->return_note }}</td>
                            <td>
                                <span class="fw-bold">{{ $return->created_at ? $return->created_at->format('d-m-Y') : 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">Return</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('sale.returns.edit', $return->id) }}" 
                                   class="btn btn-sm btn-outline-primary px-3" 
                                   title="Edit Sale Return">
                                    <i class="fa fa-edit me-1"></i> Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No sale returns found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection

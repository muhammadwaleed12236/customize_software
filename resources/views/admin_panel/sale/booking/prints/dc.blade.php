{{-- @php 
echo "<pre>";
        print_r($customer); 
    echo "</pre>";
@endphp --}}

@extends('admin_panel.layout.app')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        font-size: 13px;
        color: #000;
    }

    .dc-box {
        border: 1px solid #000;
        padding: 15px;
    }

    .dc-title {
        font-size: 22px;
        font-weight: 700;
        text-align: center;
        letter-spacing: 1px;
    }

    .dc-sub {
        text-align: center;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .table th,
    .table td {
        border: 1px solid #000 !important;
        text-align: center;
        vertical-align: middle;
        padding: 6px;
    }

    .sign-box {
        height: 80px;
        border-top: 1px solid #000;
        margin-top: 40px;
        text-align: center;
        padding-top: 5px;
        font-weight: 600;
    }

    /* ===== PRINT SETTINGS ===== */
    @media print {

        body {
            font-size: 12px;
        }

        .no-print {
            display: none !important;
        }

        .dc-box {
            border: none;
            padding: 0;
        }

        textarea,
        input,
        button {
            display: none !important;
        }

        .container {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>

<div class="container mt-4">

    {{-- ===== PRINT BUTTON ===== --}}
    <div class="text-end mb-2 no-print">
        <button class="btn btn-sm btn-primary" onclick="printDC()">
            🖨 Print DC
        </button>
    </div>

    <div class="dc-box">

        {{-- ================= HEADER ================= --}}
        <div class="dc-title">ZAIN TRADERS</div>
        <div class="dc-sub">DELIVERY CHALLAN</div>
        <hr>

        {{-- ================= INFO ================= --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <strong>Invoice No:</strong><br>
                {{ $booking->invoice_no ?? 'N/A' }}
            </div>

            <div class="col-md-4">
                <strong>Delivery Date:</strong><br>
                {{ \Carbon\Carbon::parse($booking->created_at)->format('d-m-Y') }}
            </div>

            <div class="col-md-4">
                <strong>Warehouse / Location:</strong><br>
                {{ $items->first()->warehouse_name ?? 'Not Available' }}
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Customer Name:</strong><br>
                {{ $customer->customer_name ?? 'Walking Customer' }}
            </div>

            <div class="col-md-6">
                <strong>Customer Contact:</strong><br>
                {{ $customer->mobile ?? 'N/A' }}
            </div>
        </div>

        {{-- ================= ITEMS TABLE ================= --}}
        <table class="table table-sm mt-3">
            <thead>
                <tr>
                    <th style="width:5%">#</th>
                    <th>Item Name</th>
                    <th>Warehouse</th>
                    <th style="width:15%">Quantity</th>
                    <th style="width:20%">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->warehouse_name }}</td>
                        <td>{{ number_format($item->sales_qty, 0) }}</td>
                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No items found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- ================= NOTES ================= --}}
        <div class="mt-3">
            <strong>Notes:</strong>
            <div style="border:1px solid #000; min-height:40px;"></div>
        </div>

        {{-- ================= SIGNATURES ================= --}}
        <div class="row text-center">
            <div class="col-3">
                <div class="sign-box">Prepared By</div>
            </div>
            <div class="col-3">
                <div class="sign-box">Checked By</div>
            </div>
            <div class="col-3">
                <div class="sign-box">Driver</div>
            </div>
            <div class="col-3">
                <div class="sign-box">Receiver</div>
            </div>
        </div>

    </div>

</div>

<script>
function printDC() {
    window.print();
}
</script>

@endsection

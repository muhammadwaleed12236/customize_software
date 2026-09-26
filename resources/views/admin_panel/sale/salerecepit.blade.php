<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt</title>

<style>
body {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    margin: 0;
}
.receipt-container {
    max-width: 340px;
    margin: auto;
    padding: 10px;
}
.center { text-align:center }
.bold { font-weight:bold }
.line { border-top:1px dashed #000; margin:6px 0 }
table { width:100%; border-collapse:collapse }
th, td { font-size:11px; padding:2px 0 }
th { text-align:left }
td:last-child, th:last-child { text-align:right }
.footer { text-align:center; margin-top:8px; font-size:11px }
</style>
</head>

<body>
<div class="receipt-container">

    {{-- HEADER --}}
    <div class="center">
        <div class="bold" style="font-size:14px;">AMEER & SONS</div>
        <div>EXCLUSIVE STORE</div>
        <div>Hyderabad</div>
    </div>

    <div class="line"></div>
    <div class="center bold">DELIVERY RECEIPT</div>
    <div class="line"></div>

    {{-- DETAILS --}}
    <table>
        <tr>
            <th>Invoice:</th>
            <td>{{ $booking->invoice_no }}</td>
        </tr>
        <tr>
            <th>Customer:</th>
            <td>{{ $booking->party_type }}</td>
        </tr>
        <tr>
            <th>Address:</th>
            <td>{{ $booking->address }}</td>
        </tr>
        <tr>
            <th>Date:</th>
            <td>{{ \Carbon\Carbon::parse($booking->created_at)->format('d-m-Y H:i') }}</td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- ITEMS --}}
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Amt</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $it)
            @php
                $pUrdu = $it->product->item_name_urdu ?? $it->product->urdu_name ?? null;
            @endphp
            <tr>
                <td>{{ !empty($pUrdu) ? $pUrdu : ($it->product->item_name ?? '-') }}</td>
                <td>{{ $it->sales_qty }}</td>
                <td>{{ number_format($it->retail_price,0) }}</td>
                <td>{{ number_format($it->amount,0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    {{-- TOTALS --}}
    <table>
        <tr>
            <th>Total Qty</th>
            <td>{{ $booking->quantity }}</td>
        </tr>
        <tr>
            <th>Sub Total</th>
            <td>{{ number_format($booking->sub_total2,0) }}</td>
        </tr>
        <tr>
            <th>Discount</th>
            <td>{{ number_format($booking->discount_amount,0) }}</td>
        </tr>
        <tr class="bold">
            <th>Net Amount</th>
            <td>{{ number_format($booking->sub_total2 - $booking->discount_amount,0) }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="footer">
        <div>Goods once sold will not be returned</div>
        <div>*** THANK YOU – VISIT AGAIN ***</div>
    </div>

</div>

<script>
window.onload = function () {
    window.print();
};
</script>

</body>
</html>

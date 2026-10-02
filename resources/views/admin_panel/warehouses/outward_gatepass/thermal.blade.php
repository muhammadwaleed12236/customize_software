<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gatepass Thermal {{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size:11px; color:#111; margin:0; padding:0; }
        .wrap { width:300px; margin:0 auto; padding:8px; }
        .center { text-align:center; }
        .brand-title { font-size:16px; font-weight:bold; margin:0; text-transform:uppercase; }
        .brand-sub { font-size:10px; color:#555; text-transform:uppercase; letter-spacing:1px; }
        .hr { border-top:1px dashed #444; margin:6px 0; }
        .bold { font-weight:700; }
        .text-right { text-align:right; }
        .small { font-size:10px; }
        table { width:100%; border-collapse:collapse; margin-top:4px; }
        th, td { padding:3px 2px; font-size:10px; }
        th { border-bottom:1px solid #111; text-align:left; }
    </style>
</head>
<body onload="setTimeout(()=>window.print(), 300)">
    <div class="wrap">
        <div class="center">
            <div class="brand-title">AMIN &amp; SONS</div>
            <div class="brand-sub">Outward Gate Pass</div>
            <div class="bold" style="font-size:13px; margin-top:4px;">{{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</div>
            <div class="small">{{ optional($gp->created_at)->format('Y-m-d H:i') ?? '' }}</div>
        </div>

        <div class="hr"></div>

        <table style="width:100%">
            <tr><td><strong>DC No:</strong></td><td class="text-right">{{ $gp->dc_no ?? ($order->dc_no ?? '-') }}</td></tr>
            <tr><td><strong>Invoice:</strong></td><td class="text-right">{{ $gp->invoice_no ?? '-' }}</td></tr>
            <tr><td><strong>Customer:</strong></td><td class="text-right">{{ Str::limit($gp->customer_name ?? '-', 20) }}</td></tr>
            <tr><td><strong>City:</strong></td><td class="text-right">{{ $gp->delivery_city ?? '-' }}</td></tr>
            <tr><td><strong>Location:</strong></td><td class="text-right">{{ optional(\App\Models\Warehouse::find($gp->warehouse_id))->warehouse_name ?? ($gp->location_name ?? '-') }}</td></tr>
        </table>

        <div class="hr"></div>

        <table>
            <thead>
                <tr>
                    <th style="width:20px">#</th>
                    <th>Product</th>
                    <th style="width:40px" class="text-center">Unit</th>
                    <th style="width:50px" class="text-right">Qty</th>
                </tr>
            </thead>
            <tbody>
                @php $items = $gp->items ?? []; $totQty = 0; @endphp
                @forelse($items as $k => $it)
                    @php 
                        $row = is_array($it) ? $it : (is_object($it) ? (array)$it : ['text'=>$it]); 
                        $q = (float)($row['qty'] ?? 0);
                        $totQty += $q;
                    @endphp
                    <tr>
                        <td>{{ $k+1 }}</td>
                        <td>{{ Str::limit($row['product_name'] ?? $row['text'] ?? '-', 28) }}</td>
                        <td class="text-center">{{ $row['unit'] ?? '-' }}</td>
                        <td class="text-right bold">{{ $q > 0 ? number_format($q, 2) : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="center small">No items</td></tr>
                @endforelse
            </tbody>
            @if(count($items))
                <tfoot>
                    <tr>
                        <td colspan="3" class="bold text-right" style="border-top:1px dashed #444;">Total Qty:</td>
                        <td class="text-right bold" style="border-top:1px dashed #444;">{{ number_format($totQty, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="hr"></div>

        <table style="width:100%">
            <tr><td><strong>Vehicle:</strong></td><td class="text-right">{{ $gp->vehicle_number ?? '-' }}</td></tr>
            <tr><td><strong>Driver:</strong></td><td class="text-right">{{ $gp->driver_name ?? '-' }}</td></tr>
            <tr><td><strong>Transporter:</strong></td><td class="text-right">{{ $gp->transporter ?? '-' }}</td></tr>
            <tr><td><strong>Freight / Rent:</strong></td><td class="text-right bold">Rs. {{ $gp->transport_rent ? number_format($gp->transport_rent, 0) : '0' }}</td></tr>
            <tr><td><strong>Bilty No:</strong></td><td class="text-right">{{ $gp->billty_no ?? '-' }}</td></tr>
            <tr><td><strong>Bilty Amount:</strong></td><td class="text-right">Rs. {{ $gp->billty_amount ? number_format($gp->billty_amount, 0) : '0' }}</td></tr>
            @if(!empty($gp->expense_account_name))
            <tr><td><strong>Account:</strong></td><td class="text-right">{{ $gp->expense_account_name }}</td></tr>
            @endif
        </table>

        @if(!empty($gp->packing_notes))
        <div style="margin-top:6px;" class="small">
            <strong>Packing Notes:</strong> {{ $gp->packing_notes }}
        </div>
        @endif

        <div class="hr"></div>

        <table style="width:100%; margin-top:15px; text-align:center;" class="small">
            <tr>
                <td style="width:50%;">_________________<br>Receiver Sign</td>
                <td style="width:50%;">_________________<br>Issued By</td>
            </tr>
        </table>
    </div>
</body>
</html>
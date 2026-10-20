<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Outward Gate Pass {{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color:#000000; line-height: 1.4; }
        .wrap{ max-width:100%; margin:0 auto; padding: 12px; border: 2px solid #000000; }
        .brand-header { background: #ffffff; color: #000000; padding: 0 0 10px 0; border-bottom: 2px solid #000000; margin-bottom: 12px; }
        .brand-title { font-size: 20px; font-weight: bold; margin: 0; color: #000000; text-transform: uppercase; }
        .brand-subtitle { font-size: 11px; color: #000000; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold; margin-top: 2px; }
        
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .details-table td { width: 50%; vertical-align: top; padding: 0 4px; }
        
        .box { border: 2px solid #000000; border-radius: 4px; padding: 8px 10px; background: #ffffff; }
        .box-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #000000; border-bottom: 1.5px solid #000000; padding: 2px 4px 4px 4px; margin-bottom: 6px; background: #e8e8e8; }
        
        .field-row { margin-bottom: 4px; border-bottom: 1px solid #000000; padding-bottom: 2px; }
        .field-label { color: #000000; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .field-val { color: #000000; font-weight: bold; font-size: 11px; }

        table.items { width:100%; border-collapse:collapse; margin-top:8px; border: 2px solid #000000; }
        table.items th { background: #dcdcdc; color: #000000; padding: 7px 8px; font-size: 11px; text-transform: uppercase; text-align: left; border: 1.5px solid #000000; font-weight: bold; }
        table.items td { padding: 6px 8px; border: 1.5px solid #000000; font-size: 11px; color: #000000; font-weight: bold; }
        table.items tfoot td { background: #dcdcdc; font-weight: bold; font-size: 12px; border: 1.5px solid #000000; border-top: 2px solid #000000; color: #000000; }
        
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        
        .signatures { margin-top: 30px; width: 100%; border-collapse: collapse; }
        .signatures td { width: 33%; text-align: center; vertical-align: bottom; }
        .sign-line { border-top: 2px solid #000000; margin-top: 35px; padding-top: 5px; font-weight: bold; font-size: 10px; text-transform: uppercase; color: #000000; }
    </style>
</head>
<body>
    <div class="wrap">
        <!-- Brand Header -->
        <div class="brand-header">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td>
                        <div class="brand-title">{{ $gp->branch_name ?? 'Branch' }}</div>
                        <div class="brand-subtitle">Outward Gate Pass & Delivery Challan</div>
                        @if(!empty($gp->branch_address))
                        <div style="font-size: 9px; color: #333333; margin-top: 2px;">{{ $gp->branch_address }}</div>
                        @endif
                    </td>
                    <td class="text-end" style="color: #000000;">
                        <div style="font-size: 14px; font-weight: bold;">{{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</div>
                        <div style="font-size: 10px; color: #333333;">Date: {{ optional($gp->created_at)->format('d-M-Y') ?? '-' }}</div>
                        <div style="font-size: 10px; color: #333333;">DC: {{ $gp->dc_no ?? ($order->dc_no ?? 'N/A') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Stacked Full-Width Information Containers -->
        <div style="margin-bottom: 8px;">
            <div class="box" style="margin-bottom: 8px;">
                <div class="box-title">Customer & Destination Details</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top; padding-right: 12px; border-right: 1.5px solid #000000;">
                            <div class="field-row"><span class="field-label">Customer Name:</span> <span class="field-val" style="float: right;">{{ $gp->customer_name ?? 'N/A' }}</span><div style="clear:both;"></div></div>
                            <div class="field-row"><span class="field-label">Customer Type:</span> <span class="field-val" style="float: right;">{{ !empty($gp->is_walking_customer) ? 'Walking Customer' : 'Regular Customer' }}</span><div style="clear:both;"></div></div>
                            <div class="field-row" style="border-bottom:none;"><span class="field-label">Delivery Destination:</span> <span class="field-val" style="float: right;">{{ $gp->delivery_city ?? 'N/A' }}</span><div style="clear:both;"></div></div>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding-left: 12px;">
                            <div class="field-row"><span class="field-label">DC Number:</span> <span class="field-val" style="float: right;">{{ $gp->dc_no ?? ($order->dc_no ?? 'N/A') }}</span><div style="clear:both;"></div></div>
                            <div class="field-row"><span class="field-label">Invoice Number:</span> <span class="field-val" style="float: right;">{{ $gp->invoice_no ?? '-' }}</span><div style="clear:both;"></div></div>
                            <div class="field-row" style="border-bottom:none;"><span class="field-label">Order Reference:</span> <span class="field-val" style="float: right;">#{{ $gp->order_id }}</span><div style="clear:both;"></div></div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="box">
                <div class="box-title">Vehicle & Logistics Details</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top; padding-right: 12px; border-right: 1.5px solid #000000;">
                            <div class="field-row"><span class="field-label">Vehicle & Driver:</span> <span class="field-val" style="float: right;">{{ $gp->vehicle_number ?? '-' }} {{ !empty($gp->driver_name) ? '('.$gp->driver_name.')' : '' }}</span><div style="clear:both;"></div></div>
                            <div class="field-row"><span class="field-label">Transporter:</span> <span class="field-val" style="float: right;">{{ $gp->transporter ?? '-' }}</span><div style="clear:both;"></div></div>
                            <div class="field-row" style="border-bottom:none;"><span class="field-label">Vehicle Type:</span> <span class="field-val" style="float: right;">{{ $gp->vehicle_type ?? 'Standard' }}</span><div style="clear:both;"></div></div>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding-left: 12px;">
                            <div class="field-row"><span class="field-label">Bilty No & Date:</span> <span class="field-val" style="float: right;">{{ $gp->billty_no ?? '-' }} {{ $gp->billty_date ? '('.$gp->billty_date.')' : '' }}</span><div style="clear:both;"></div></div>
                            <div class="field-row"><span class="field-label">Freight / Rent:</span> <span class="field-val" style="float: right;">Rs. {{ $gp->transport_rent ? number_format($gp->transport_rent, 2) : '0.00' }}</span><div style="clear:both;"></div></div>
                            <div class="field-row" style="border-bottom:none;"><span class="field-label">Issued / Prepared By:</span> <span class="field-val" style="float: right;">{{ $gp->issued_by ?? '-' }}{{ !empty($gp->prepared_by) ? ' / ' . $gp->prepared_by : '' }}</span><div style="clear:both;"></div></div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @php
            $items = $gp->items ?? [];
            $totalQty = 0;
        @endphp

        <!-- Items Manifest Table -->
        <table class="items">
            <thead>
                <tr>
                    <th style="width:30px" class="text-center">#</th>
                    <th>Product Description</th>
                    <th style="width:110px">Item Code</th>
                    <th style="width:90px">Brand</th>
                    <th style="width:60px" class="text-center">Unit</th>
                    <th style="width:80px" class="text-end">Delivered Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $k => $it)
                    @php
                        $row = is_array($it) ? $it : (is_object($it) ? (array)$it : ['text' => $it]);
                        $qty = isset($row['qty']) ? (float)$row['qty'] : 0;
                        $totalQty += $qty;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $k+1 }}</td>
                        <td><strong>{{ $row['product_name'] ?? $row['text'] ?? '-' }}</strong></td>
                        <td>{{ $row['item_code'] ?? '-' }}</td>
                        <td>{{ $row['brand'] ?? '-' }}</td>
                        <td class="text-center">{{ $row['unit'] ?? '-' }}</td>
                        <td class="text-end" style="font-weight: bold;">{{ $qty ? number_format($qty, 2) : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No items recorded for this gate pass.</td></tr>
                @endforelse
            </tbody>
            @if(count($items))
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end">Total Quantity Delivered:</td>
                        <td class="text-end" style="color: #1e3a5f;">{{ number_format($totalQty, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <!-- Packing & Remarks -->
        @if(!empty($gp->packing_notes) || !empty($gp->remarks))
        <div style="margin-top: 10px; padding: 8px 10px; background: #fffdf5; border: 1px solid #fde68a; border-radius: 6px;">
            @if(!empty($gp->packing_notes))
                <div><strong>Packing Notes:</strong> {{ $gp->packing_notes }}</div>
            @endif
            @if(!empty($gp->remarks))
                <div style="margin-top:4px;"><strong>Remarks:</strong> {{ $gp->remarks }}</div>
            @endif
        </div>
        @endif

        <!-- Signatures -->
        <table class="signatures">
            <tr>
                <td><div class="sign-line">Driver Signature</div></td>
                <td><div class="sign-line">Receiver Signature</div></td>
                <td><div class="sign-line">Authorized Signature</div></td>
            </tr>
        </table>
    </div>
</body>
</html>

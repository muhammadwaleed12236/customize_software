<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Outward Gate Pass {{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color:#0f172a; line-height: 1.4; }
        .wrap{ max-width:100%; margin:0 auto; padding: 10px; }
        .brand-header { background: #1e3a5f; color: #ffffff; padding: 12px 16px; border-radius: 6px; margin-bottom: 12px; }
        .brand-title { font-size: 18px; font-weight: bold; margin: 0; }
        .brand-subtitle { font-size: 10px; color: #fbbf24; text-transform: uppercase; letter-spacing: 1px; }
        
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .details-table td { width: 50%; vertical-align: top; padding: 0 4px; }
        
        .box { border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; background: #f8fafc; }
        .box-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #1e3a5f; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-bottom: 6px; }
        
        .field-row { margin-bottom: 3px; }
        .field-label { color: #64748b; font-weight: bold; }
        .field-val { color: #0f172a; font-weight: bold; }

        table.items { width:100%; border-collapse:collapse; margin-top:8px; border: 1px solid #cbd5e1; }
        table.items th { background: #1e3a5f; color: #ffffff; padding: 6px 8px; font-size: 10px; text-transform: uppercase; text-align: left; }
        table.items td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        table.items tfoot td { background: #f1f5f9; font-weight: bold; font-size: 11px; border-top: 2px solid #cbd5e1; }
        
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        
        .signatures { margin-top: 30px; width: 100%; border-collapse: collapse; }
        .signatures td { width: 33%; text-align: center; vertical-align: bottom; }
        .sign-line { border-top: 1px solid #94a3b8; margin-top: 35px; padding-top: 4px; font-weight: bold; font-size: 9px; text-transform: uppercase; color: #475569; }
    </style>
</head>
<body>
    <div class="wrap">
        <!-- Brand Header -->
        <div class="brand-header">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td>
                        <div class="brand-title">AMIN & SONS</div>
                        <div class="brand-subtitle">Outward Gate Pass & Delivery Challan</div>
                    </td>
                    <td class="text-end" style="color: #ffffff;">
                        <div style="font-size: 14px; font-weight: bold;">{{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</div>
                        <div style="font-size: 10px; color: #e2e8f0;">Date: {{ optional($gp->created_at)->format('d-M-Y') ?? '-' }}</div>
                        <div style="font-size: 10px; color: #e2e8f0;">DC: {{ $gp->dc_no ?? ($order->dc_no ?? 'N/A') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- 2x2 Box Details -->
        <table class="details-table">
            <tr>
                <td>
                    <div class="box">
                        <div class="box-title">Customer & Destination</div>
                        <div class="field-row"><span class="field-label">Customer Name:</span> <span class="field-val">{{ $gp->customer_name ?? 'N/A' }}</span></div>
                        <div class="field-row"><span class="field-label">Delivery City:</span> <span class="field-val">{{ $gp->delivery_city ?? 'N/A' }}</span></div>
                        <div class="field-row"><span class="field-label">Invoice No:</span> <span class="field-val">{{ $gp->invoice_no ?? '-' }}</span></div>
                        <div class="field-row"><span class="field-label">Order ID:</span> <span class="field-val">#{{ $gp->order_id }}</span></div>
                    </div>
                </td>
                <td>
                    <div class="box">
                        <div class="box-title">Logistics & Vehicle</div>
                        <div class="field-row"><span class="field-label">Transporter:</span> <span class="field-val">{{ $gp->transporter ?? '-' }}</span></div>
                        <div class="field-row"><span class="field-label">Vehicle Type:</span> <span class="field-val">{{ $gp->vehicle_type ?? '-' }}</span></div>
                        <div class="field-row"><span class="field-label">Vehicle No:</span> <span class="field-val">{{ $gp->vehicle_number ?? '-' }}</span></div>
                        <div class="field-row"><span class="field-label">Driver Name:</span> <span class="field-val">{{ $gp->driver_name ?? '-' }}</span></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 8px;">
                    <div class="box">
                        <div class="box-title">Bilty & Freight Details</div>
                        <div class="field-row"><span class="field-label">Bilty No:</span> <span class="field-val">{{ $gp->billty_no ?? '-' }}</span></div>
                        <div class="field-row"><span class="field-label">Bilty Date:</span> <span class="field-val">{{ $gp->billty_date ?? '-' }}</span></div>
                        <div class="field-row"><span class="field-label">Bilty Amount:</span> <span class="field-val">Rs. {{ $gp->billty_amount ? number_format($gp->billty_amount, 2) : '0.00' }}</span></div>
                        <div class="field-row"><span class="field-label">Freight / Rent:</span> <span class="field-val">Rs. {{ $gp->transport_rent ? number_format($gp->transport_rent, 2) : '0.00' }}</span></div>
                    </div>
                </td>
                <td style="padding-top: 8px;">
                    <div class="box">
                        <div class="box-title">Dispatch & Issuer</div>
                        <div class="field-row"><span class="field-label">Dispatch Location:</span> <span class="field-val">{{ $gp->location_name ?? 'N/A' }}</span></div>
                        <div class="field-row"><span class="field-label">Issued By:</span> <span class="field-val">{{ $gp->issued_by ?? '-' }}</span></div>
                        <div class="field-row"><span class="field-label">Prepared By:</span> <span class="field-val">{{ $gp->prepared_by ?? '-' }}</span></div>
                        <div class="field-row"><span class="field-label">Payment Account:</span> <span class="field-val">{{ $gp->expense_account_name ?? 'Not Linked' }}</span></div>
                    </div>
                </td>
            </tr>
        </table>

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

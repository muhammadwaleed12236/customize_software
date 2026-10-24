<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Outward Gate Pass {{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        * {
            color: #000000 !important;
        }
        .wrap {
            width: 100%;
            margin: 0 auto;
            border: 1.5px solid #000000;
            padding: 0;
            page-break-inside: avoid;
        }
        .brand-header {
            background: #ffffff;
            color: #000000;
            padding: 10px 12px;
            border-bottom: 1.5px solid #000000;
        }
        .brand-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            color: #000000;
            text-transform: uppercase;
        }
        .brand-subtitle {
            font-size: 9px;
            color: #000000;
            font-weight: bold;
            margin-top: 2px;
        }
        .doc-badge {
            display: inline-block;
            background: #000000;
            color: #ffffff !important;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 6px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .meta-table {
            border: 1.5px solid #000000;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-left: auto;
        }
        .meta-table td {
            padding: 2px 6px;
            border: 1px solid #000000;
        }
        .meta-lbl {
            background: #f0f0f0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
        }
        .meta-val {
            font-weight: bold;
            font-family: monospace;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #000000;
        }
        .info-col {
            width: 50%;
            vertical-align: top;
            padding: 6px 10px;
        }
        .info-col:first-child {
            border-right: 1.5px solid #000000;
        }

        .box-title {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
            border-bottom: 1px solid #000000;
            padding: 2px 4px 3px 4px;
            margin-bottom: 4px;
            background: #f0f0f0;
        }
        
        .field-row {
            margin-bottom: 2px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 2px;
            font-size: 9.5px;
        }
        .field-row:last-child {
            border-bottom: none;
        }
        .field-label {
            color: #000000;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
        }
        .field-val {
            color: #000000;
            font-weight: bold;
            float: right;
            text-align: right;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #000000;
        }
        table.items th {
            background: #f0f0f0;
            color: #000000;
            padding: 4px 6px;
            font-size: 9.5px;
            text-transform: uppercase;
            text-align: left;
            border: 1px solid #000000;
            font-weight: bold;
        }
        table.items td {
            padding: 4px 6px;
            border: 1px solid #000000;
            font-size: 9.5px;
            color: #000000;
            font-weight: bold;
        }
        table.items tfoot td {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #000000;
            border-top: 1.5px solid #000000;
            color: #000000;
        }
        
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        
        .notes-box {
            padding: 6px 10px;
            border-bottom: 1.5px solid #000000;
            font-size: 9px;
        }
        .notes-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            margin-bottom: 2px;
        }

        .signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        .signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 15px;
        }
        .sign-line {
            border-top: 1.5px solid #000000;
            padding-top: 3px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            color: #000000;
        }
        .sign-sub {
            font-size: 8px;
            color: #333333;
        }
    </style>
</head>
<body>
    @php
        $orderCustomer = !empty($order->customer_id) ? \App\Models\Customer::find($order->customer_id) : null;
        $destAddress = !empty($gp->delivery_city) ? $gp->delivery_city : ($orderCustomer->address ?? ($orderCustomer->city ?? 'Hyderabad Latifabad'));
        $custMobile = !empty($gp->customer_mobile) ? $gp->customer_mobile : ($orderCustomer->mobile ?? '-');
        $custCode = !empty($gp->customer_code) ? $gp->customer_code : ($orderCustomer->customer_id ?? ('CUST-' . ($order->customer_id ?? 'N/A')));
    @endphp

    <div class="wrap">
        <!-- Brand Header -->
        <div class="brand-header">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="vertical-align: top; width: 62%;">
                        <div class="brand-title">{{ !empty($gp->company_name) ? $gp->company_name : 'AMIN & SONS' }}</div>
                        <div class="brand-subtitle">
                            <strong>{{ strtoupper($gp->branch_name ?? 'Branch') }} BRANCH</strong>
                            @if(!empty($gp->branch_address))
                                &bull; {{ $gp->branch_address }}
                            @endif
                            @if(!empty($gp->branch_phone))
                                &bull; Tel: {{ $gp->branch_phone }}
                            @endif
                        </div>
                        <div style="margin-top: 5px;">
                            <span class="doc-badge">OUTWARD GATE PASS &amp; DELIVERY CHALLAN</span>
                            <span style="display: inline-block; font-size: 8.5px; font-weight: bold; border: 1px solid #000; padding: 2px 6px; margin-left: 4px; background: #fff;">
                                <strong>Dispatch:</strong> {{ $gp->location_name ?? 'Main Store' }}
                            </span>
                        </div>
                    </td>
                    <td class="text-end" style="vertical-align: top; width: 38%;">
                        <table class="meta-table">
                            <tr>
                                <td class="meta-lbl">Gatepass No:</td>
                                <td class="meta-val" style="font-size: 13px;">{{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</td>
                            </tr>
                            <tr>
                                <td class="meta-lbl">Date &amp; Time:</td>
                                <td class="meta-val">{{ optional($gp->created_at)->format('d-M-Y h:i A') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="meta-lbl">DC Reference:</td>
                                <td class="meta-val">{{ $gp->dc_no ?? ($order->dc_no ?? 'N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="meta-lbl">Invoice No:</td>
                                <td class="meta-val">{{ $gp->invoice_no ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- 2-Column Information Grid -->
        <table class="info-table">
            <tr>
                <td class="info-col">
                    <div class="box-title">Customer & Destination Details</div>
                    <div class="field-row"><span class="field-label">Customer Name:</span> <span class="field-val">{{ $gp->customer_name ?? ($orderCustomer->customer_name ?? 'N/A') }}</span><div style="clear:both;"></div></div>
                    <div class="field-row"><span class="field-label">Customer Code:</span> <span class="field-val">{{ $custCode }}</span><div style="clear:both;"></div></div>
                    <div class="field-row"><span class="field-label">Contact / Phone:</span> <span class="field-val">{{ $custMobile }}</span><div style="clear:both;"></div></div>
                    <div class="field-row"><span class="field-label">Delivery Destination:</span> <span class="field-val">{{ $destAddress }}</span><div style="clear:both;"></div></div>
                    <div class="field-row" style="border-bottom:none;"><span class="field-label">Customer Type:</span> <span class="field-val">{{ !empty($gp->is_walking_customer) ? 'Walking Customer' : 'Regular Customer' }}</span><div style="clear:both;"></div></div>
                </td>
                <td class="info-col">
                    <div class="box-title">Vehicle & Logistics Details</div>
                    <div class="field-row"><span class="field-label">Vehicle & Driver:</span> <span class="field-val">{{ $gp->vehicle_number ?? '-' }} {{ !empty($gp->driver_name) ? '('.$gp->driver_name.')' : '' }}</span><div style="clear:both;"></div></div>
                    <div class="field-row"><span class="field-label">Transporter:</span> <span class="field-val">{{ $gp->transporter ?? '-' }}</span><div style="clear:both;"></div></div>
                    <div class="field-row"><span class="field-label">Bilty No & Date:</span> <span class="field-val">{{ $gp->billty_no ?? '-' }} {{ $gp->billty_date ? '('.$gp->billty_date.')' : '' }}</span><div style="clear:both;"></div></div>
                    <div class="field-row"><span class="field-label">Freight / Rent:</span> <span class="field-val">Rs. {{ $gp->transport_rent ? number_format($gp->transport_rent, 2) : '0.00' }}</span><div style="clear:both;"></div></div>
                    <div class="field-row" style="border-bottom:none;"><span class="field-label">Issued / Prepared By:</span> <span class="field-val">{{ $gp->issued_by ?? 'admin' }}{{ !empty($gp->prepared_by) ? ' / ' . $gp->prepared_by : '' }}</span><div style="clear:both;"></div></div>
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
                    <th style="width: 25px;" class="text-center">#</th>
                    <th style="width: 48%;">Product Description</th>
                    <th style="width: 15%;" class="text-center">Item Code</th>
                    <th style="width: 12%;" class="text-center">Brand</th>
                    <th style="width: 8%;" class="text-center">Unit</th>
                    <th style="width: 12%;" class="text-end">Delivered Qty</th>
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
                        <td class="text-center">{{ $row['item_code'] ?? '-' }}</td>
                        <td class="text-center">{{ $row['brand'] ?? '-' }}</td>
                        <td class="text-center">{{ $row['unit'] ?? '-' }}</td>
                        <td class="text-end" style="font-weight: bold;">{{ $qty ? number_format($qty, 2) : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-2">No items recorded for this gate pass.</td></tr>
                @endforelse
            </tbody>
            @if(count($items))
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end">Grand Total Quantity Delivered:</td>
                        <td class="text-end">{{ number_format($totalQty, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <!-- Packing & Remarks -->
        <div class="notes-box">
            <div class="notes-title">Packing & Handling Notes:</div>
            <div>{{ !empty($gp->packing_notes) ? $gp->packing_notes : 'Goods received in satisfactory condition without any damages.' }}</div>
            @if(!empty($gp->remarks))
                <div style="margin-top: 4px;"><strong>Remarks:</strong> {{ $gp->remarks }}</div>
            @endif
        </div>

        <!-- 3-Column Guaranteed Horizontal Signatures -->
        <table class="signatures">
            <tr>
                <td>
                    <div class="sign-line">Driver Signature</div>
                    <div class="sign-sub">Name / Contact</div>
                </td>
                <td>
                    <div class="sign-line">Receiver Signature</div>
                    <div class="sign-sub">Stamp & Signature</div>
                </td>
                <td>
                    <div class="sign-line">Authorized Officer</div>
                    <div class="sign-sub">Warehouse / Incharge</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

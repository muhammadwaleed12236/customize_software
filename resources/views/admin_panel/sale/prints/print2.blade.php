@extends('admin_panel.layout.app')

@section('content')

<style>
    /* Reset & Base Styles - Pure Black High-Contrast POS Thermal */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        color: #000000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    body {
        font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace;
        background: #eef2f5;
        font-size: 11px;
        line-height: 1.25;
        color: #000000 !important;
        font-weight: 600;
    }

    /* Thermal Receipt Container (80mm) */
    .thermal-receipt {
        width: 80mm;
        max-width: 80mm;
        margin: 20px auto 40px auto;
        background: #ffffff;
        padding: 5mm 4mm;
        border: 2px solid #000000;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        font-size: 11px;
    }

    /* Top Action Bar (Screen Only) */
    .receipt-action-bar {
        width: 80mm;
        max-width: 80mm;
        margin: 15px auto 5px auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }

    .btn-thermal-print {
        background: #000000 !important;
        color: #ffffff !important;
        border: 2px solid #000000 !important;
        font-weight: 800 !important;
        font-size: 13px !important;
        padding: 8px 16px !important;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        flex: 1;
        justify-content: center;
    }

    .btn-thermal-print * {
        color: #ffffff !important;
    }

    .btn-thermal-print:hover {
        background: #222222 !important;
        color: #ffffff !important;
    }

    .btn-thermal-close {
        background: #ffffff !important;
        color: #000000 !important;
        border: 2px solid #000000 !important;
        font-weight: 800 !important;
        font-size: 13px !important;
        padding: 8px 16px !important;
        border-radius: 4px;
        cursor: pointer;
        text-transform: uppercase;
    }

    /* Dotted & Solid Separators */
    .border-dotted-sep {
        border: none;
        border-top: 1px dotted #000000;
        margin: 4px 0;
    }

    .border-dashed-sep {
        border: none;
        border-top: 1px dashed #000000;
        margin: 5px 0;
    }

    .border-double-sep {
        border: none;
        border-top: 3px double #000000;
        margin: 6px 0;
    }

    .border-solid-thick {
        border: none;
        border-top: 2px solid #000000;
        margin: 5px 0;
    }

    /* Header Section */
    .receipt-header {
        text-align: center;
        padding-bottom: 4px;
    }

    .receipt-company-title {
        font-size: 16px;
        font-weight: 900;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 2px;
        line-height: 1.15;
    }

    .receipt-tagline {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .receipt-contact {
        font-size: 10px;
        font-weight: 700;
        line-height: 1.3;
    }

    /* Invoice Title Banner */
    .receipt-banner {
        text-align: center;
        margin: 4px 0;
        padding: 3px 0;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        border-top: 1px dashed #000000;
        border-bottom: 1px dashed #000000;
    }

    /* Key-Value Details Rows */
    .kv-table {
        width: 100%;
        margin: 4px 0;
        border-collapse: collapse;
    }

    .kv-table td {
        padding: 1.5px 0;
        vertical-align: top;
        font-size: 10.5px;
    }

    .kv-label {
        font-weight: 800;
        width: 32%;
        text-align: left;
    }

    .kv-value {
        font-weight: 700;
        width: 68%;
        text-align: right;
        word-break: break-word;
    }

    /* Items Table */
    .receipt-items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 4px 0;
        table-layout: fixed;
    }

    .receipt-items-table th {
        font-weight: 900;
        font-size: 9.5px;
        text-transform: uppercase;
        padding: 3px 0;
        border-top: 1px dashed #000000;
        border-bottom: 1px dashed #000000;
    }

    .receipt-items-table td {
        padding: 3px 0;
        font-size: 10.5px;
        vertical-align: top;
    }

    .receipt-items-table tbody tr {
        border-bottom: 1px dotted #000000;
    }

    .receipt-items-table tbody tr:last-child {
        border-bottom: none;
    }

    .col-sr {
        width: 7%;
        text-align: left;
    }

    .col-desc {
        width: 45%;
        text-align: left;
        padding-right: 2px;
    }

    .col-qty {
        width: 14%;
        text-align: right;
    }

    .col-rate {
        width: 16%;
        text-align: right;
    }

    .col-amt {
        width: 18%;
        text-align: right;
    }

    .item-primary-name {
        font-weight: 800;
        line-height: 1.15;
    }

    .item-secondary-info {
        font-size: 9px;
        font-weight: 700;
        margin-top: 1px;
    }

    /* Summary & Totals */
    .receipt-totals-table {
        width: 100%;
        border-collapse: collapse;
        margin: 4px 0;
    }

    .receipt-totals-table td {
        padding: 2px 0;
        font-size: 11px;
    }

    .total-title {
        font-weight: 800;
        text-align: left;
    }

    .total-amt {
        font-weight: 800;
        text-align: right;
        white-space: nowrap;
    }

    /* Grand Payable Box */
    .grand-payable-row {
        border-top: 2px solid #000000;
        border-bottom: 2px solid #000000;
    }

    .grand-payable-row td {
        padding: 4px 0 !important;
        font-size: 13px !important;
        font-weight: 900 !important;
    }

    /* Closing Balance Box */
    .closing-balance-row {
        border-top: 2px dashed #000000;
        border-bottom: 2px solid #000000;
    }

    .closing-balance-row td {
        padding: 4px 0 !important;
        font-size: 12.5px !important;
        font-weight: 900 !important;
    }

    /* Footer Section */
    .receipt-footer {
        text-align: center;
        margin-top: 6px;
        padding-top: 4px;
        border-top: 1px dashed #000000;
        font-size: 10px;
    }

    .thank-you-title {
        font-size: 11.5px;
        font-weight: 900;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
        text-transform: uppercase;
    }

    .policy-note {
        font-size: 9px;
        font-weight: 700;
        line-height: 1.25;
        margin: 3px 0;
    }

    .barcode-section {
        margin: 5px 0 2px 0;
        text-align: center;
    }

    .barcode-text {
        font-family: 'Courier New', monospace;
        letter-spacing: 2px;
        font-size: 12px;
        font-weight: 900;
    }

    .timestamp-note {
        font-size: 8.5px;
        font-weight: 700;
        margin-top: 3px;
    }

    /* PRINT SPECIFIC STYLES */
    @media print {
        html, body {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 80mm !important;
        }

        .no-print,
        .receipt-action-bar,
        .navbar,
        .app-header,
        .sidebar,
        .main-header,
        .main-sidebar,
        footer,
        .footer {
            display: none !important;
        }

        .content-wrapper,
        .main-content,
        .container-fluid,
        .container {
            margin: 0 !important;
            padding: 0 !important;
            width: 80mm !important;
            max-width: 80mm !important;
        }

        .thermal-receipt {
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            padding: 2mm 3mm !important;
            width: 80mm !important;
            max-width: 80mm !important;
        }

        @page {
            size: 80mm auto;
            margin: 0mm;
        }
    }
</style>

<div class="container-fluid p-0">
    <!-- Screen-Only Action Bar -->
    <div class="receipt-action-bar no-print">
        <button onclick="window.print()" class="btn-thermal-print">
            <i class="fas fa-print"></i> Print Thermal Receipt
        </button>
        <button onclick="window.close()" class="btn-thermal-close">
            Close
        </button>
    </div>

    <!-- Thermal Receipt Card -->
    <div class="thermal-receipt">
        {{-- 1. BUSINESS HEADER --}}
        <div class="receipt-header">
            <div class="receipt-company-title">{{ strtoupper($branch->name ?? 'ZAIN TRADERS') }}</div>
            <div class="receipt-tagline">Electronics & Home Appliances</div>
            <div class="receipt-contact">
                @if(!empty($branch->address))
                    {{ $branch->address }}<br>
                @else
                    17th-Brandreth Road, Lahore<br>
                @endif
                @if(!empty($branch->mobile) || !empty($branch->phone))
                    Tel: {{ $branch->mobile ?? $branch->phone }}
                @else
                    Mob: 0300-4235114 / 042-37635383
                @endif
            </div>
        </div>

        {{-- 2. RECEIPT TYPE BANNER --}}
        <div class="receipt-banner">
            *** SALE INVOICE ***
        </div>

        {{-- 3. INVOICE META DETAILS --}}
        <table class="kv-table">
            <tr>
                <td class="kv-label">INVOICE #:</td>
                <td class="kv-value" style="font-size: 12px; font-weight: 900;">{{ $sale->invoice_no }}</td>
            </tr>
            <tr>
                <td class="kv-label">DATE & TIME:</td>
                <td class="kv-value">{{ $sale->created_at ? $sale->created_at->format('d-m-Y h:i A') : date('d-m-Y h:i A') }}</td>
            </tr>
            <tr>
                <td class="kv-label">CUSTOMER:</td>
                <td class="kv-value">
                    @php
                        $custName = optional($sale->customer)->customer_name ?? $sale->sub_customer ?? 'Walking Customer';
                    @endphp
                    <strong>{{ strtoupper($custName) }}</strong>
                </td>
            </tr>
            @php 
                $mobileNo = optional($sale->customer)->mobile_2 ?? optional($sale->customer)->mobile ?? $sale->tel ?? '';
            @endphp
            @if(!empty(trim($mobileNo)))
            <tr>
                <td class="kv-label">PHONE:</td>
                <td class="kv-value">{{ $mobileNo }}</td>
            </tr>
            @endif
            <tr>
                <td class="kv-label">PAY TYPE:</td>
                <td class="kv-value" style="font-weight: 900;">
                    {{ strtoupper($sale->party_type ?? 'CASH') }}
                </td>
            </tr>
            @if(!empty($sale->remarks))
            <tr>
                <td class="kv-label">NOTE:</td>
                <td class="kv-value">{{ $sale->remarks }}</td>
            </tr>
            @endif
        </table>

        {{-- 4. ITEMS TABLE --}}
        <table class="receipt-items-table">
            <thead>
                <tr>
                    <th class="col-sr">#</th>
                    <th class="col-desc">ITEM</th>
                    <th class="col-qty">QTY</th>
                    <th class="col-rate">RATE</th>
                    <th class="col-amt">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalQtyCounter = 0;
                @endphp
                @foreach($sale->saleItems as $idx => $item)
                @php
                    $totalQtyCounter += (float)($item->sales_qty ?? 0);
                    $engName = $item->product->item_name ?? $item->product->product_name ?? 'Item';
                    $rawUrdu = $item->product->item_name_urdu ?? $item->product->urdu_name ?? null;
                    $hasUrdu = !empty(trim($rawUrdu ?? ''));
                    $itemDisc = (float)($item->discount_amount ?? 0);
                @endphp
                <tr>
                    <td class="col-sr">{{ $idx + 1 }}</td>
                    <td class="col-desc">
                        <div class="item-primary-name">{{ $engName }}</div>
                        @if($hasUrdu)
                            <div class="item-secondary-info" style="direction: rtl; text-align: left;">{{ trim($rawUrdu) }}</div>
                        @endif
                        @if($itemDisc > 0)
                            <div class="item-secondary-info">Disc: -{{ number_format($itemDisc, 2) }}</div>
                        @endif
                    </td>
                    <td class="col-qty">{{ number_format($item->sales_qty ?? 0, 0) }}</td>
                    <td class="col-rate">{{ number_format($item->retail_price ?? $item->sales_price ?? 0, 2) }}</td>
                    <td class="col-amt">{{ number_format($item->amount ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ITEMS SUMMARY STRIP --}}
        <div style="display: flex; justify-content: space-between; font-size: 10px; font-weight: 800; padding: 2px 0; border-top: 1px dotted #000000; border-bottom: 1px dashed #000000;">
            <span>TOTAL ITEMS: {{ count($sale->saleItems) }}</span>
            <span>TOTAL PCS: {{ number_format($totalQtyCounter, 0) }}</span>
        </div>

        {{-- 5. TOTALS & CALCULATIONS --}}
        @php
            $subTotal = (float)($sale->sub_total1 ?? $sale->sub_total2 ?? 0);
            $lineDiscount = (float)($sale->discount_amount ?? 0);
            $extraDiscount = (float)($sale->additional_discount ?? 0);
            $extraCharges = (float)($sale->extra_charges ?? 0);
            
            $netPayable = (float)($sale->sub_total2 ?? ($subTotal - $lineDiscount - $extraDiscount + $extraCharges));

            // Received from vouchers or sale
            $totalReceived = 0;
            if(isset($sale) && isset($sale->invoice_no)){
                $receipts = \App\Models\ReceiptsVoucher::where('reference_no', $sale->invoice_no)
                    ->where('type', 'SALE_RECEIPT')
                    ->get();
                $totalReceived = (float)$receipts->sum('amount');
            }
            if($totalReceived == 0 && isset($sale->receipt1)){
                $totalReceived = (float)($sale->receipt1 ?? 0) + (float)($sale->receipt2 ?? 0);
            }

            // Customer ledger calculations for credit customers
            $displayPrevious = 0;
            $displayClosing = 0;
            $isCreditCustomer = strtolower($sale->party_type ?? '') === 'credit';
            
            if($isCreditCustomer && isset($sale->customer)){
                $ledgerData = \App\Models\CustomerLedger::where('customer_id', $sale->customer->id)
                    ->latest('id')
                    ->first();
                
                if($ledgerData){
                    $displayPrevious = floatval($ledgerData->previous_balance ?? 0);
                    $displayClosing = floatval($ledgerData->closing_balance ?? 0);
                } else {
                    $displayPrevious = floatval($sale->customer->opening_balance ?? 0);
                    $displayClosing = $netPayable - $totalReceived + $displayPrevious;
                }
            } else {
                $displayClosing = $netPayable - $totalReceived;
            }
        @endphp

        <table class="receipt-totals-table">
            <tr>
                <td class="total-title">SUB TOTAL (GROSS):</td>
                <td class="total-amt">Rs. {{ number_format($subTotal, 2) }}</td>
            </tr>

            @if($lineDiscount > 0)
            <tr>
                <td class="total-title">ITEM DISCOUNT:</td>
                <td class="total-amt">- Rs. {{ number_format($lineDiscount, 2) }}</td>
            </tr>
            @endif

            @if($extraDiscount > 0)
            <tr>
                <td class="total-title">EXTRA DISCOUNT:</td>
                <td class="total-amt">- Rs. {{ number_format($extraDiscount, 2) }}</td>
            </tr>
            @endif

            @if($extraCharges > 0)
            <tr>
                <td class="total-title">EXTRA CHARGES:</td>
                <td class="total-amt">+ Rs. {{ number_format($extraCharges, 2) }}</td>
            </tr>
            @endif

            {{-- NET PAYABLE ROW --}}
            <tr class="grand-payable-row">
                <td class="total-title" style="letter-spacing: 0.5px;">NET TOTAL PAYABLE:</td>
                <td class="total-amt">Rs. {{ number_format($netPayable, 2) }}</td>
            </tr>

            @if($totalReceived > 0)
            <tr>
                <td class="total-title" style="padding-top: 3px;">AMOUNT PAID / RCVD:</td>
                <td class="total-amt" style="padding-top: 3px;">Rs. {{ number_format($totalReceived, 2) }}</td>
            </tr>
            @if(!$isCreditCustomer && ($totalReceived > $netPayable))
            <tr>
                <td class="total-title">CHANGE RETURN:</td>
                <td class="total-amt">Rs. {{ number_format($totalReceived - $netPayable, 2) }}</td>
            </tr>
            @endif
            @endif

            {{-- CREDIT LEDGER DETAILS --}}
            @if($isCreditCustomer)
            <tr>
                <td class="total-title" style="padding-top: 3px;">PREVIOUS BALANCE:</td>
                <td class="total-amt" style="padding-top: 3px;">Rs. {{ number_format($displayPrevious, 2) }}</td>
            </tr>
            <tr class="closing-balance-row">
                <td class="total-title" style="letter-spacing: 0.5px;">NET CLOSING BALANCE:</td>
                <td class="total-amt">Rs. {{ number_format($displayClosing, 2) }}</td>
            </tr>
            @endif
        </table>

        {{-- 6. BARCODE SECTION --}}
        <div class="barcode-section">
            <div class="border-dotted-sep"></div>
            <div class="barcode-text">*{{ $sale->invoice_no }}*</div>
            <div class="border-dotted-sep"></div>
        </div>

        {{-- 7. FOOTER & POLICIES --}}
        <div class="receipt-footer">
            <div class="thank-you-title">*** THANK YOU FOR YOUR VISIT! ***</div>
            <div class="policy-note">
                • Please check items and warranty before leaving.<br>
                • Goods once sold can be exchanged within 3 days with bill.<br>
                • Warranty claims as per manufacturer policy.
            </div>
            <div class="timestamp-note">
                Printed: {{ now()->format('d-m-Y h:i:s A') }} | POS v2.0
            </div>
        </div>
    </div>
</div>

@endsection

@extends('admin_panel.layout.app')

@section('content')

@php
    $settings = $settings ?? \App\Models\SaleSetting::getSettings();
@endphp

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
        font-family: 'Segoe UI', 'Courier New', Courier, Arial, monospace;
        background: #eef2f5;
        font-size: 11px;
        line-height: 1.25;
        color: #000000 !important;
    }

    /* Thermal Receipt Container (80mm) */
    .thermal-receipt {
        width: 80mm;
        max-width: 80mm;
        margin: 20px auto 40px auto;
        background: #ffffff;
        padding: 4mm 4mm;
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

    /* Header styling */
    .receipt-header-center {
        text-align: center;
        padding-bottom: 2px;
    }

    .receipt-logo-wrap {
        max-height: 44px;
        max-width: 140px;
        margin: 0 auto 2px auto;
    }

    .receipt-logo-wrap img {
        max-height: 44px;
        max-width: 140px;
        object-fit: contain;
    }

    .receipt-logo-badge {
        display: inline-block;
        border: 2px solid #000000;
        border-radius: 20px;
        padding: 2px 14px;
        font-weight: 900;
        font-size: 20px;
        letter-spacing: 1px;
        line-height: 1.1;
    }

    .receipt-tagline {
        font-size: 11px;
        font-weight: 800;
        margin-top: 2px;
        text-transform: capitalize;
    }

    .receipt-sub-desc {
        font-size: 8.5px;
        font-weight: 600;
        line-height: 1.25;
        max-width: 260px;
        margin: 2px auto 0 auto;
        white-space: pre-line;
    }

    /* Contacts 2-column strip */
    .receipt-contacts-strip {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-top: 6px;
        font-size: 9px;
        line-height: 1.3;
    }

    .contacts-col-left {
        width: 48%;
        text-align: left;
    }

    .contacts-col-right {
        width: 48%;
        text-align: right;
    }

    /* Black highlighted bar */
    .receipt-highlight-bar {
        background: #000000 !important;
        color: #ffffff !important;
        padding: 3.5px 4px;
        margin: 6px 0 5px 0;
        text-align: center;
        font-weight: 900;
        font-size: 9.5px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .receipt-highlight-bar * {
        color: #ffffff !important;
    }

    /* Metadata table */
    .receipt-meta-table {
        width: 100%;
        border-collapse: collapse;
        margin: 4px 0;
        font-size: 10.5px;
    }

    .receipt-meta-table td {
        padding: 1.5px 0;
        vertical-align: top;
    }

    .meta-lbl {
        font-weight: 800;
        width: 32%;
        text-align: left;
    }

    .meta-val {
        font-weight: 700;
        width: 68%;
        text-align: right;
    }

    /* Dashed separator */
    .dashed-line {
        border-top: 1px dashed #000000;
        margin: 5px 0;
    }

    /* Items table */
    .receipt-items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 4px 0;
        table-layout: fixed;
    }

    .receipt-items-table th {
        font-size: 9.5px;
        font-weight: 900;
        padding: 3px 0;
        border-top: 1px dashed #000000;
        border-bottom: 1px dashed #000000;
    }

    .receipt-items-table td {
        padding: 3px 0;
        font-size: 10px;
        vertical-align: top;
    }

    .col-sr { width: 7%; text-align: left; font-weight: 800; }
    .col-desc { width: 45%; text-align: left; font-weight: 800; padding-right: 2px; }
    .col-qty { width: 16%; text-align: right; font-weight: 700; }
    .col-rate { width: 14%; text-align: right; font-weight: 700; }
    .col-amt { width: 18%; text-align: right; font-weight: 800; }

    /* Totals */
    .receipt-totals-table {
        width: 100%;
        border-collapse: collapse;
        margin: 4px 0;
        font-size: 11px;
    }

    .receipt-totals-table td {
        padding: 2px 0;
    }

    .tot-lbl {
        font-weight: 800;
        text-align: left;
    }

    .tot-val {
        font-weight: 800;
        text-align: right;
        white-space: nowrap;
    }

    .payable-box-row td {
        border-top: 1.5px solid #000000;
        border-bottom: 1.5px solid #000000;
        padding: 4px 0 !important;
        font-size: 12.5px !important;
        font-weight: 900 !important;
    }

    .closing-box-row td {
        border: 1.5px solid #000000;
        padding: 3px 4px !important;
        font-size: 12px !important;
        font-weight: 900 !important;
    }

    /* Footer */
    .receipt-footer {
        text-align: center;
        margin-top: 8px;
        padding-top: 4px;
        border-top: 1px dashed #000000;
        font-size: 9.5px;
        line-height: 1.35;
    }

    .footer-branch {
        font-weight: 700;
        font-size: 9px;
        margin-bottom: 3px;
    }

    .footer-thanks {
        font-weight: 800;
        font-size: 10.5px;
        margin: 5px 0 3px 0;
        text-transform: capitalize;
    }

    .footer-credit {
        font-size: 8.5px;
        font-weight: 700;
        color: #000000;
        margin-top: 2px;
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
            <i class="fas fa-print"></i> Print Receipt
        </button>
        <button onclick="window.close()" class="btn-thermal-close">
            Close
        </button>
    </div>

    <!-- Thermal Receipt Card -->
    <div class="thermal-receipt">

        {{-- 1. BUSINESS HEADER (DYNAMIC FROM SETTINGS) --}}
        <div class="receipt-header-center">
            @if(!empty($settings->invoice_logo) && file_exists(public_path($settings->invoice_logo)))
                <div class="receipt-logo-wrap">
                    <img src="{{ asset($settings->invoice_logo) }}" alt="Logo">
                </div>
            @else
                <div class="receipt-logo-badge">
                    {{ $settings->invoice_company_name ?? 'GLC' }}
                </div>
            @endif

            <div class="receipt-tagline">
                {{ $settings->invoice_tagline ?? 'Long Life Accessories' }}
            </div>

            <div class="receipt-sub-desc">
                {!! nl2br(e($settings->invoice_sub_description ?? "Power Bank, Battery, Data Cable, Charger, Head Phone\nHandsfree, Bluetooth & Multimedia")) !!}
            </div>
        </div>

        {{-- 2. CONTACT NUMBERS (LEFT & RIGHT) --}}
        <div class="receipt-contacts-strip">
            {{-- Left Contacts --}}
            <div class="contacts-col-left">
                @php
                    $cLeft = array_filter(array_map('trim', explode("\n", $settings->invoice_contacts_left ?? "Faisal Mehmood\n0325 4682466\nFurqaan Shakeel\n03044576200")));
                @endphp
                @for($k = 0; $k < count($cLeft); $k += 2)
                    <div style="font-weight: 800;">{{ $cLeft[$k] ?? '' }}</div>
                    <div style="font-weight: 700; margin-bottom: 2px;">{{ $cLeft[$k+1] ?? '' }}</div>
                @endfor
            </div>

            {{-- Right Contacts --}}
            <div class="contacts-col-right">
                @php
                    $cRight = array_filter(array_map('trim', explode("\n", $settings->invoice_contacts_right ?? "Anjum Shakeel\n03008033184\nTayyab Shakeel\n03054453139\nPTCL #: 042 37322272\n042 37232370")));
                @endphp
                @foreach($cRight as $line)
                    @if(str_contains(strtolower($line), 'ptcl'))
                        <div style="font-weight: 800; margin-top: 2px;">{{ $line }}</div>
                    @elseif(preg_match('/[a-zA-Z]/', $line))
                        <div style="font-weight: 800;">{{ $line }}</div>
                    @else
                        <div style="font-weight: 700; margin-bottom: 2px;">{{ $line }}</div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- 3. HIGHLIGHTED BLACK BAR (NOTE 1) --}}
        @php
            $firstNote = '1. NO RETURN NO EXCHANGE WITHOUT BILL';
            if (!empty($settings->invoice_terms_notes)) {
                $lines = array_filter(array_map('trim', explode("\n", $settings->invoice_terms_notes)));
                if (!empty($lines)) {
                    $firstNote = mb_strtoupper($lines[0]);
                }
            }
        @endphp
        <div class="receipt-highlight-bar">
            {{ $firstNote }}
        </div>

        {{-- 4. INVOICE META DETAILS --}}
        <table class="receipt-meta-table">
            <tr>
                <td class="meta-lbl">Invoice #:</td>
                <td class="meta-val" style="font-weight: 900;">{{ $sale->invoice_no }}</td>
            </tr>
            <tr>
                <td class="meta-lbl">Date:</td>
                <td class="meta-val">{{ $sale->created_at ? $sale->created_at->format('d/m/Y h:i A') : date('d/m/Y h:i A') }}</td>
            </tr>
            <tr>
                <td class="meta-lbl">Customer:</td>
                <td class="meta-val">
                    @php
                        $custName = optional($sale->customer)->customer_name ?? $sale->sub_customer ?? 'Walking Customer';
                    @endphp
                    <strong>{{ strtoupper($custName) }}</strong>
                </td>
            </tr>
            @php
                $salespersonName = optional($sale->salesman)->name ?? (Auth::user()?->name ?? 'Super Admin');
            @endphp
            <tr>
                <td class="meta-lbl">Salesperson:</td>
                <td class="meta-val">{{ $salespersonName }}</td>
            </tr>
        </table>

        {{-- 5. ITEMS TABLE --}}
        <table class="receipt-items-table">
            <thead>
                <tr>
                    <th class="col-sr">S.</th>
                    <th class="col-desc">DESCRIPTION</th>
                    <th class="col-qty">QTY</th>
                    <th class="col-rate">RATE</th>
                    <th class="col-amt">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sr = 1;
                    $calcSubtotal = 0;
                    $totalQty = 0;
                @endphp
                @if($sale->saleItems && $sale->saleItems->count() > 0)
                    @foreach($sale->saleItems as $item)
                        @php
                            $qty = (float) $item->sales_qty;
                            $rate = (float) $item->sales_price;
                            $amt = (float) ($item->amount > 0 ? $item->amount : ($qty * $rate));
                            $calcSubtotal += $amt;
                            $totalQty += $qty;
                            $pName = optional($item->product)->item_name ?? 'Item';
                            $unit = optional(optional($item->product)->unit)->name ?? 'Pcs';
                        @endphp
                        <tr>
                            <td class="col-sr">{{ $sr++ }}</td>
                            <td class="col-desc">
                                <div style="font-weight: 800; line-height: 1.15;">{{ $pName }}</div>
                            </td>
                            <td class="col-qty">
                                <div style="font-weight: 800;">{{ (int)$qty == $qty ? (int)$qty : number_format($qty, 2) }}</div>
                                <div style="font-size: 8.5px; font-weight: 600;">{{ $unit }}</div>
                            </td>
                            <td class="col-rate">{{ number_format($rate, 0) }}</td>
                            <td class="col-amt">{{ number_format($amt, 0) }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <div class="dashed-line"></div>

        {{-- 6. TOTALS & BALANCES --}}
        @php
            $subTotal = (float)($sale->sub_total1 ?? 0);
            if ($subTotal <= 0) {
                $subTotal = $calcSubtotal;
            }

            $totalPayable = (float)($sale->party_type == 'credit' ? ($sale->total_balance ?? $sale->total_net) : $sale->total_net);
            if ($totalPayable <= 0) {
                $totalPayable = $subTotal;
            }

            // Customer ledger balances
            $prevBalance = 0;
            $closingBalance = 0;
            $paidAmount = (float)(($sale->receipt1 ?? 0) + ($sale->receipt2 ?? 0));

            if ($sale->customer) {
                $lastLedger = \App\Models\CustomerLedger::where('customer_id', $sale->customer->id)->latest('id')->first();
                if ($lastLedger) {
                    $prevBalance = (float) ($lastLedger->previous_balance ?? 0);
                    $closingBalance = (float) ($lastLedger->closing_balance ?? 0);
                } else {
                    $prevBalance = (float) (optional($sale->customer)->opening_balance ?? 0);
                    $closingBalance = $totalPayable - $paidAmount + $prevBalance;
                }
            } else {
                $closingBalance = $totalPayable - $paidAmount;
            }
        @endphp

        <table class="receipt-totals-table">
            <tr>
                <td class="tot-lbl">Sub Total:</td>
                <td class="tot-val">{{ number_format($subTotal, 0) }}</td>
            </tr>
            <tr class="payable-box-row">
                <td class="tot-lbl">TOTAL PAYABLE:</td>
                <td class="tot-val">{{ number_format($totalPayable, 0) }}</td>
            </tr>
            <tr>
                <td class="tot-lbl" style="padding-top: 4px;">Prev Balance:</td>
                <td class="tot-val" style="padding-top: 4px;">{{ number_format($prevBalance, 0) }} Dr</td>
            </tr>
            <tr>
                <td class="tot-lbl">Paid Amount:</td>
                <td class="tot-val">{{ number_format($paidAmount, 0) }}</td>
            </tr>
            <tr class="closing-box-row">
                <td class="tot-lbl">CLOSING BALANCE:</td>
                <td class="tot-val">{{ number_format($closingBalance, 0) }} Dr</td>
            </tr>
        </table>

        {{-- 7. FOOTER BRANCHES & CREDITS (DYNAMIC FROM SETTINGS) --}}
        <div class="receipt-footer">
            <div class="footer-branch">
                {{ $settings->invoice_branch_1 ?? 'Branch#1: Shop #22,45, Basement Madina Center The Mall Hall Road, Lhr.' }}
            </div>
            <div class="footer-branch">
                {{ $settings->invoice_branch_2 ?? 'Branch#2: Shop #40,43, 3rd Floor Hassan Center Hall Road, Lhr.' }}
            </div>

            <div class="footer-thanks">
                Thank you for shopping with us!
            </div>

            @if(!empty($settings->invoice_footer_credit))
                <div class="footer-credit">
                    {{ $settings->invoice_footer_credit }}
                </div>
            @endif
        </div>

    </div>
</div>

@endsection

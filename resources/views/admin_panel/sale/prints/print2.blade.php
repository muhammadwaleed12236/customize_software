<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $sale->invoice_no }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @php
        $settings = $settings ?? \App\Models\SaleSetting::getSettings();
    @endphp

    <style>
        /* Reset & Base Styles - Clean Arial Thermal POS */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            color: #000000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #eef2f5;
            font-size: 11px;
            line-height: 1.3;
            color: #000000 !important;
            -webkit-font-smoothing: antialiased;
        }

        /* Thermal Receipt Container (80mm) */
        .thermal-receipt {
            width: 80mm;
            max-width: 80mm;
            margin: 20px auto 40px auto;
            background: #ffffff;
            padding: 5mm 5mm;
            border: 1px solid #333333;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
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
            border: 1px solid #000000 !important;
            font-weight: bold !important;
            font-size: 12px !important;
            padding: 7px 14px !important;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
            flex: 1;
            justify-content: center;
            font-family: Arial, Helvetica, sans-serif;
        }

        .btn-thermal-print * {
            color: #ffffff !important;
        }

        .btn-thermal-close {
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid #000000 !important;
            font-weight: bold !important;
            font-size: 12px !important;
            padding: 7px 14px !important;
            border-radius: 4px;
            cursor: pointer;
            text-transform: uppercase;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* Header styling */
        .receipt-header-center {
            text-align: center;
            padding-bottom: 2px;
        }

        .receipt-logo-wrap {
            max-height: 50px;
            max-width: 140px;
            margin: 0 auto 3px auto;
        }

        .receipt-logo-wrap img {
            max-height: 50px;
            max-width: 140px;
            object-fit: contain;
        }

        .receipt-company-title {
            font-size: 19px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
            text-align: center;
        }

        .receipt-tagline {
            font-size: 11px;
            font-weight: normal;
            margin-top: 2px;
            text-align: center;
        }

        .receipt-sub-desc {
            font-size: 11px;
            font-weight: normal;
            line-height: 1.3;
            margin: 2px auto 0 auto;
            text-align: center;
            white-space: pre-line;
        }

        /* Contacts 2-column strip */
        .receipt-contacts-strip {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 6px;
            font-size: 11px;
            line-height: 1.35;
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
            margin: 6px 0 6px 0;
            text-align: center;
            font-weight: bold;
            font-size: 10.5px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .receipt-highlight-bar * {
            color: #ffffff !important;
        }

        /* Table Resets */
        .thermal-receipt table {
            border-collapse: collapse !important;
            margin: 2px 0 !important;
            width: 100% !important;
        }

        .thermal-receipt table tr th,
        .thermal-receipt table tr td,
        .thermal-receipt table th,
        .thermal-receipt table td,
        .receipt-meta-table tr td,
        .receipt-meta-table td,
        .receipt-items-table tr th,
        .receipt-items-table tr td,
        .receipt-items-table th,
        .receipt-items-table td,
        .receipt-totals-table tr td,
        .receipt-totals-table td {
            padding: 1px 0 !important;
            vertical-align: top !important;
            height: auto !important;
            min-height: 0 !important;
            line-height: 1.3 !important;
        }

        /* Metadata table */
        .receipt-meta-table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin: 3px 0 !important;
            font-size: 11px;
        }

        .receipt-meta-table tr td,
        .receipt-meta-table td {
            padding: 1.5px 0 !important;
            vertical-align: top !important;
            line-height: 1.3 !important;
        }

        .meta-lbl {
            font-weight: normal;
            width: 30%;
            text-align: left;
        }

        .meta-val {
            font-weight: normal;
            width: 70%;
            text-align: right;
        }

        /* Dashed separator */
        .dashed-line {
            border-top: 1px dashed #000000 !important;
            margin: 4px 0 !important;
        }

        /* Items table */
        .receipt-items-table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin: 3px 0 !important;
            table-layout: fixed;
        }

        .receipt-items-table tr th,
        .receipt-items-table th {
            font-size: 10.5px;
            font-weight: bold;
            padding: 3px 0 !important;
            border-top: 1px dashed #000000 !important;
            border-bottom: 1px dashed #000000 !important;
            line-height: 1.25 !important;
        }

        .receipt-items-table tr td,
        .receipt-items-table td {
            padding: 2.5px 0 !important;
            font-size: 11px;
            font-weight: normal;
            vertical-align: top !important;
            line-height: 1.25 !important;
        }

        .col-sr { width: 6%; text-align: left; }
        .col-desc { width: 48%; text-align: left; padding-right: 2px; word-break: break-word; }
        .col-qty { width: 16%; text-align: right; white-space: nowrap; }
        .col-rate { width: 14%; text-align: right; }
        .col-amt { width: 16%; text-align: right; }

        /* Totals */
        .receipt-totals-table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin: 3px 0 !important;
            font-size: 11px;
        }

        .receipt-totals-table tr td,
        .receipt-totals-table td {
            padding: 2px 0 !important;
            line-height: 1.3 !important;
        }

        .tot-lbl {
            font-weight: bold;
            text-align: left;
        }

        .tot-val {
            font-weight: bold;
            text-align: right;
            white-space: nowrap;
        }

        .payable-box-row td {
            border-top: 1.5px solid #000000 !important;
            border-bottom: 1.5px solid #000000 !important;
            padding: 3.5px 0 !important;
            font-size: 11.5px !important;
            font-weight: bold !important;
        }

        .closing-box-row td {
            border: 1.5px solid #000000 !important;
            padding: 2.5px 4px !important;
            font-size: 11.5px !important;
            font-weight: bold !important;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            margin-top: 8px !important;
            padding-top: 4px !important;
            font-size: 11px;
            line-height: 1.3;
        }

        .footer-branch {
            font-size: 10px;
            margin-bottom: 2px;
            line-height: 1.25;
        }

        .footer-thanks {
            font-size: 11px;
            margin: 4px 0 2px 0;
        }

        .footer-credit {
            font-size: 9px;
            color: #000000;
            margin-top: 2px;
        }

        /* PRINT SPECIFIC STYLES */
        @page {
            size: 80mm auto;
            margin: 0mm;
        }

        @media print {
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
            }

            .no-print,
            .receipt-action-bar {
                display: none !important;
            }

            .thermal-receipt {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 1.5mm 2.5mm !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .receipt-sub-desc {
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>

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
            @elseif(!empty($settings->invoice_company_name))
                <div class="receipt-company-title">
                    {{ $settings->invoice_company_name }}
                </div>
            @endif

            @if(!empty(trim($settings->invoice_tagline ?? '')))
                <div class="receipt-tagline">
                    {{ $settings->invoice_tagline }}
                </div>
            @endif

            @if(!empty(trim($settings->invoice_sub_description ?? '')))
                <div class="receipt-sub-desc">
                    {!! nl2br(e($settings->invoice_sub_description)) !!}
                </div>
            @endif
        </div>

        {{-- 2. CONTACT NUMBERS (LEFT & RIGHT - ONLY IF FILLED) --}}
        @php
            $cLeft = !empty(trim($settings->invoice_contacts_left ?? ''))
                ? array_filter(array_map('trim', explode("\n", $settings->invoice_contacts_left)))
                : [];
            $cRight = !empty(trim($settings->invoice_contacts_right ?? ''))
                ? array_filter(array_map('trim', explode("\n", $settings->invoice_contacts_right)))
                : [];
        @endphp

        @if(!empty($cLeft) || !empty($cRight))
        <div class="receipt-contacts-strip">
            {{-- Left Contacts --}}
            <div class="contacts-col-left">
                @for($k = 0; $k < count($cLeft); $k += 2)
                    <div style="font-weight: 600;">{{ $cLeft[$k] ?? '' }}</div>
                    <div style="margin-bottom: 2px;">{{ $cLeft[$k+1] ?? '' }}</div>
                @endfor
            </div>

            {{-- Right Contacts --}}
            <div class="contacts-col-right">
                @foreach($cRight as $line)
                    @if(str_contains(strtolower($line), 'ptcl'))
                        <div style="font-weight: 600; margin-top: 2px;">{{ $line }}</div>
                    @elseif(preg_match('/[a-zA-Z]/', $line))
                        <div style="font-weight: 600;">{{ $line }}</div>
                    @else
                        <div style="margin-bottom: 2px;">{{ $line }}</div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- 3. HIGHLIGHTED BLACK BAR (NOTE 1 - ONLY IF FILLED) --}}
        @if(!empty(trim($settings->invoice_terms_notes ?? '')))
            @php
                $lines = array_filter(array_map('trim', explode("\n", $settings->invoice_terms_notes)));
                $firstNote = !empty($lines) ? mb_strtoupper($lines[0]) : '';
            @endphp
            @if(!empty($firstNote))
                <div class="receipt-highlight-bar">
                    {{ $firstNote }}
                </div>
            @endif
        @endif

        {{-- 4. INVOICE META DETAILS --}}
        <table class="receipt-meta-table">
            <tr>
                <td class="meta-lbl">Invoice #:</td>
                <td class="meta-val">{{ $sale->invoice_no }}</td>
            </tr>
            <tr>
                <td class="meta-lbl">Date:</td>
                <td class="meta-val">{{ $sale->created_at ? \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y h:i A') : date('d/m/Y h:i A') }}</td>
            </tr>
            <tr>
                <td class="meta-lbl">Customer:</td>
                <td class="meta-val">
                    @php
                        $custName = optional($sale->customer)->customer_name ?? $sale->sub_customer ?? 'Walk-in Customer';
                    @endphp
                    {{ $custName }}
                </td>
            </tr>
            @php
                $salespersonName = optional($sale->salesman)->name ?? (Auth::user()?->name ?? 'Admin');
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
                            $rate = (float) ($item->sales_price > 0 ? $item->sales_price : ($item->retail_price > 0 ? $item->retail_price : ($qty > 0 ? ($item->amount / $qty) : 0)));
                            $amt = (float) ($item->amount > 0 ? $item->amount : ($qty * $rate));
                            $calcSubtotal += $amt;
                            $totalQty += $qty;
                            $pName = optional($item->product)->item_name ?? 'Item';
                            $unit = optional(optional($item->product)->unit)->name ?? 'Pcs';
                            $ppp = (float)($item->product->piece_per_pack ?? 1);
                            $isCartonProd = $ppp > 1;
                            $isPcsUnit = (isset($item->sale_unit) && strtolower($item->sale_unit) === 'pcs');
                            if ($isPcsUnit) {
                                $qtyDisplay = ((int)$qty == $qty ? (int)$qty : number_format($qty, 2));
                                $unitDisplay = '';
                            } elseif ($isCartonProd) {
                                $totalPieces = $qty * $ppp;
                                $qtyDisplay = \App\Models\Product::formatCartonPieces($totalPieces, $ppp, $unit);
                                $unitDisplay = '';
                            } else {
                                $qtyDisplay = ((int)$qty == $qty ? (int)$qty : number_format($qty, 2));
                                $unitDisplay = (strtolower($unit) === 'pcs') ? '' : $unit;
                            }
                        @endphp
                        <tr>
                            <td class="col-sr">{{ $sr++ }}</td>
                            <td class="col-desc">{{ $pName }}</td>
                            <td class="col-qty">{{ $qtyDisplay }}@if(!empty($unitDisplay) && strtolower($unitDisplay) !== 'pcs') {{ $unitDisplay }}@endif</td>
                            @php
                                $hasDec = (round($rate, 2) != round($rate, 0)) || (round($amt, 2) != round($amt, 0));
                            @endphp
                            <td class="col-rate">{{ $hasDec ? number_format($rate, 2) : number_format($rate, 0) }}</td>
                            <td class="col-amt">{{ $hasDec ? number_format($amt, 2) : number_format($amt, 0) }}</td>
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

            $orderLevelDiscount = (float)($sale->discount_amount ?? 0);
            $additionalDiscount = (float)($sale->additional_discount ?? 0);
            if ($orderLevelDiscount > 0 && $additionalDiscount > 0 && abs($orderLevelDiscount - $additionalDiscount) < 0.01) {
                $thermalDiscount = $additionalDiscount;
            } else {
                $thermalDiscount = $orderLevelDiscount + $additionalDiscount;
            }

            $calcThermalNet = max(0, $subTotal - $thermalDiscount + (float)($sale->extra_charges ?? 0));
            $currentInvoiceNet = (float)($sale->total_net ?? 0);
            if ($currentInvoiceNet <= 0 || ($subTotal > 0 && $currentInvoiceNet > $calcThermalNet)) {
                $currentInvoiceNet = $calcThermalNet;
            }

            $totalPayable = $currentInvoiceNet;
            if ($totalPayable <= 0) {
                $totalPayable = $subTotal;
            }

            // Customer ledger balances
            $prevBalance = 0;
            $closingBalance = 0;
            $receipts = \App\Models\ReceiptsVoucher::with('Account')
                ->where(function($q) use ($sale) {
                    $q->where('sale_id', $sale->id)
                      ->orWhere(function($q2) use ($sale) {
                          $q2->whereNull('sale_id')
                             ->where('reference_no', $sale->invoice_no);
                          if ($sale->customer_id) {
                              $q2->where('party_id', (string)$sale->customer_id);
                          }
                      });
                })
                ->where(function($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'voided');
                })
                ->get();
            $paidAmount = (float)$receipts->sum('total_amount');
            if ($paidAmount <= 0) {
                $paidAmount = (float)$receipts->sum('amount');
            }
            if ($paidAmount <= 0) {
                $paidAmount = (float)(($sale->receipt1 ?? 0) + ($sale->receipt2 ?? 0));
            }

            if ($sale->customer) {
                // 1. Get the customer's previous balance prior to this specific sale invoice
                $saleLedger = \App\Models\CustomerLedger::where('customer_id', $sale->customer->id)
                    ->where('reference_id', (string)$sale->id)
                    ->where('transaction_type', 'Sale')
                    ->first();

                if ($saleLedger) {
                    $prevBalance = (float) ($saleLedger->previous_balance ?? 0);
                } elseif (isset($sale->previous_balance) && $sale->previous_balance !== null) {
                    $prevBalance = (float) ($sale->previous_balance ?? 0);
                } else {
                    $prevBalance = (float) (optional($sale->customer)->opening_balance ?? 0);
                }

                // Safety check: if customer has 0 opening balance and no prior transactions before this invoice, previous balance must be 0
                $hasPriorTransactions = \App\Models\CustomerLedger::where('customer_id', $sale->customer->id)
                    ->where('id', '<', optional($saleLedger)->id ?? PHP_INT_MAX)
                    ->exists();
                if (!$hasPriorTransactions && floatval(optional($sale->customer)->opening_balance ?? 0) == 0) {
                    $prevBalance = 0.0;
                }

                // 2. Per-invoice closing balance (not overall ledger balance)
                $closingBalance = ($currentInvoiceNet + $prevBalance) - $paidAmount;
            } else {
                $closingBalance = $currentInvoiceNet - $paidAmount;
            }
        @endphp

        <table class="receipt-totals-table">
            <tr>
                <td class="tot-lbl">Sub Total:</td>
                <td class="tot-val">{{ round($subTotal, 2) == round($subTotal, 0) ? number_format($subTotal, 0) : number_format($subTotal, 2) }}</td>
            </tr>
            @if(isset($thermalDiscount) && $thermalDiscount > 0)
            <tr>
                <td class="tot-lbl">Discount:</td>
                <td class="tot-val">- {{ round($thermalDiscount, 2) == round($thermalDiscount, 0) ? number_format($thermalDiscount, 0) : number_format($thermalDiscount, 2) }}</td>
            </tr>
            @endif
            <tr class="payable-box-row">
                <td class="tot-lbl">TOTAL PAYABLE:</td>
                <td class="tot-val">{{ round($totalPayable, 2) == round($totalPayable, 0) ? number_format($totalPayable, 0) : number_format($totalPayable, 2) }}</td>
            </tr>
            <tr>
                <td style="font-weight: normal; padding-top: 3px;">Prev Balance:</td>
                <td style="font-weight: normal; text-align: right; padding-top: 3px;">{{ round($prevBalance, 2) == round($prevBalance, 0) ? number_format($prevBalance, 0) : number_format($prevBalance, 2) }} Dr</td>
            </tr>
            <tr>
                <td style="font-weight: normal;">Paid Amount:</td>
                <td style="font-weight: normal; text-align: right;">{{ round($paidAmount, 2) == round($paidAmount, 0) ? number_format($paidAmount, 0) : number_format($paidAmount, 2) }}</td>
            </tr>
            <tr class="closing-box-row">
                <td class="tot-lbl">CLOSING BALANCE:</td>
                <td class="tot-val">{{ round($closingBalance, 2) == round($closingBalance, 0) ? number_format($closingBalance, 0) : number_format($closingBalance, 2) }} Dr</td>
            </tr>
        </table>

        {{-- 7. FOOTER BRANCHES & CREDITS (DYNAMIC FROM SETTINGS) --}}
        @php
            $hasBranch1 = !empty(trim($settings->invoice_branch_1 ?? ''));
            $hasBranch2 = !empty(trim($settings->invoice_branch_2 ?? ''));
            $hasCredit = !empty(trim($settings->invoice_footer_credit ?? ''));
        @endphp

        <div class="receipt-footer">
            @if($hasBranch1)
                <div class="footer-branch">
                    {{ $settings->invoice_branch_1 }}
                </div>
            @endif
            @if($hasBranch2)
                <div class="footer-branch">
                    {{ $settings->invoice_branch_2 }}
                </div>
            @endif

            <div class="footer-thanks">
                Thank you for shopping with us!
            </div>

            @if($hasCredit)
                <div class="footer-credit">
                    {{ $settings->invoice_footer_credit }}
                </div>
            @endif
        </div>

    </div>

    <script>
        // Auto print after page load
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>

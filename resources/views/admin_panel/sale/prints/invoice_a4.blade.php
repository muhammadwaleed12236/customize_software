<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $sale->invoice_no }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700;800;900&family=Inter:wght@400;600;700;800&display=swap');

        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Segoe UI', 'Inter', Arial, Helvetica, sans-serif;
            background: #eef2f6;
            margin: 0;
            padding: 20px 0;
            color: {{ $settings->invoice_text_color ?? '#000000' }};
            font-size: 11px;
            line-height: 1.35;
        }

        /* Screen Control Bar */
        .no-print-bar {
            width: 210mm;
            max-width: 96%;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 10px 18px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .no-print-bar .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: 0.2s ease;
        }

        .btn-print {
            background: #c23321;
            color: #ffffff;
        }
        .btn-print:hover {
            background: #a12818;
        }

        .btn-settings {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1 !important;
        }
        .btn-settings:hover {
            background: #e2e8f0;
        }

        .btn-back {
            background: #64748b;
            color: #ffffff;
        }
        .btn-back:hover {
            background: #475569;
        }

        /* A4 Page Container */
        .invoice-a4-page {
            width: 210mm;
            max-width: 100%;
            margin: 0 auto;
            background: #ffffff;
            padding: 8mm 12mm 10mm 12mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            position: relative;
            box-sizing: border-box;
        }

        /* Dynamic Theme Colors */
        :root {
            --primary-color: {{ $settings->invoice_primary_color ?? '#c23321' }};
            --text-color: {{ $settings->invoice_text_color ?? '#000000' }};
        }

        /* Header Layout */
        .invoice-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
            position: relative;
        }

        .header-col-left {
            width: 27%;
            text-align: left;
            font-size: 10px;
            line-height: 1.3;
        }

        .header-col-center {
            width: 46%;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .header-col-right {
            width: 27%;
            text-align: right;
            font-size: 10px;
            line-height: 1.3;
        }

        .contact-name {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 11px;
        }
        .contact-number {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 11px;
            margin-bottom: 3px;
        }

        /* Center Logo & Taglines */
        .logo-wrap {
            max-width: 170px;
            max-height: 60px;
            margin-bottom: 2px;
        }
        .logo-wrap img {
            max-width: 100%;
            max-height: 60px;
            object-fit: contain;
        }

        .logo-fallback-badge {
            display: inline-block;
            border: 3px solid var(--primary-color);
            border-radius: 40px;
            padding: 3px 22px;
            font-weight: 900;
            font-size: 26px;
            color: var(--primary-color);
            letter-spacing: 2px;
            line-height: 1.1;
        }

        .company-tagline {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 11.5px;
            margin-top: 2px;
            margin-bottom: 2px;
            text-transform: capitalize;
        }

        .sub-description {
            color: var(--primary-color);
            font-size: 9px;
            font-weight: 600;
            line-height: 1.25;
            max-width: 320px;
            margin: 0 auto;
            text-align: center;
            white-space: pre-line;
        }

        /* Metadata & Customer Bar */
        .meta-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
            font-weight: 700;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .inv-num-label {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 12.5px;
        }
        .inv-num-val {
            color: var(--text-color);
            font-weight: 900;
            font-size: 13.5px;
            margin-left: 4px;
        }

        .date-box {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .date-label {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 12px;
        }
        .date-underline {
            min-width: 110px;
            border-bottom: 1.5px solid #000;
            text-align: center;
            padding: 0 6px;
            font-weight: 700;
            font-size: 12px;
            color: var(--text-color);
        }

        .customer-row {
            display: flex;
            align-items: baseline;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
            border-bottom: 1.5px solid #000;
            padding-bottom: 2px;
        }
        .customer-label {
            color: var(--primary-color);
            font-weight: 800;
            font-size: 12px;
            margin-right: 6px;
            white-space: nowrap;
        }
        .customer-name {
            color: var(--text-color);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11.5px;
            letter-spacing: 0.3px;
        }

        /* Table Styling */
        .table-wrap {
            position: relative;
            flex-grow: 1;
            margin-bottom: 8px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid var(--primary-color) !important;
            position: relative;
            z-index: 2;
        }

        .invoice-table thead th {
            border: 1.5px solid var(--primary-color) !important;
            color: var(--primary-color) !important;
            font-weight: 800;
            font-size: 11px;
            padding: 5px 6px;
            text-align: center;
            background: #ffffff;
            letter-spacing: 0.5px;
        }

        .invoice-table tbody td {
            border: 1px solid var(--primary-color) !important;
            padding: 4px 6px;
            font-size: 10.5px;
            color: var(--text-color);
            height: 25px;
        }

        .invoice-table tbody tr.empty-row td {
            height: 25px;
            border: 1px solid var(--primary-color) !important;
        }

        .col-sr { width: 5%; text-align: center; font-weight: 700; }
        .col-desc { width: 55%; text-align: left; font-weight: 700; }
        .col-qty { width: 12%; text-align: center; font-weight: 700; }
        .col-rate { width: 13%; text-align: right; font-weight: 600; padding-right: 8px !important; }
        .col-amt { width: 15%; text-align: right; font-weight: 700; padding-right: 8px !important; }

        /* Watermark */
        .watermark-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            pointer-events: none;
            opacity: 0.08;
            text-align: center;
            width: 80%;
        }
        .watermark-container img {
            max-width: 320px;
            max-height: 220px;
            object-fit: contain;
        }
        .watermark-text {
            font-size: 58px;
            font-weight: 900;
            color: var(--primary-color);
            letter-spacing: 4px;
            line-height: 1;
            text-transform: uppercase;
        }

        /* Bottom Section: Notes & Total */
        .bottom-summary-wrap {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 6px;
            margin-bottom: 8px;
        }

        .notes-col {
            width: 55%;
            font-size: 9.5px;
            line-height: 1.4;
        }
        .notes-col strong {
            display: block;
            font-size: 10px;
            font-weight: 800;
            color: var(--text-color);
            margin-bottom: 2px;
        }
        .notes-list {
            margin: 0;
            padding: 0;
            list-style: none;
            color: var(--text-color);
            font-weight: 600;
        }

        .total-col {
            width: 42%;
            text-align: right;
        }

        .total-row {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 14px;
            margin-bottom: 14px;
        }
        .total-label {
            font-size: 13px;
            font-weight: 900;
            color: var(--primary-color);
            letter-spacing: 0.5px;
        }
        .total-amount {
            font-size: 14px;
            font-weight: 900;
            color: var(--text-color);
            border-bottom: 3px double #000;
            min-width: 130px;
            text-align: right;
            padding-bottom: 2px;
        }

        .signature-row {
            display: flex;
            align-items: baseline;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 18px;
        }
        .signature-label {
            font-weight: 700;
            font-size: 11px;
            color: var(--text-color);
        }
        .signature-line {
            display: inline-block;
            width: 140px;
            border-bottom: 1.5px solid #000;
        }

        /* Footer Banner */
        .invoice-footer-banner {
            background-color: var(--primary-color);
            color: #ffffff;
            padding: 6px 12px;
            text-align: center;
            border-radius: 4px;
            margin-top: 14px;
        }
        .branch-line {
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.2px;
            line-height: 1.35;
        }

        /* Footer Credit */
        .footer-credit {
            text-align: center;
            font-size: 8.5px;
            font-weight: 600;
            color: #555555;
            margin-top: 4px;
            letter-spacing: 0.2px;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .invoice-a4-page {
                box-shadow: none !important;
                width: 100% !important;
                height: 100% !important;
                min-height: 0 !important;
                padding: 0 !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    {{-- Top Action Bar (Screen Only) --}}
    <div class="no-print-bar">
        <div>
            <a href="{{ route('sale.index') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Sales
            </a>
            <a href="{{ route('sale.settings.index') }}" class="btn btn-settings ms-2" target="_blank">
                <i class="fas fa-cog"></i> Invoice Settings
            </a>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-print">
                <i class="fas fa-print"></i> Print A4 Invoice
            </button>
        </div>
    </div>

    {{-- A4 Page Container --}}
    <div class="invoice-a4-page">

        {{-- Header --}}
        <div class="invoice-header">
                {{-- Left Contacts --}}
                <div class="header-col-left">
                    @php
                        $contactsLeft = !empty(trim($settings->invoice_contacts_left ?? ''))
                            ? array_filter(array_map('trim', explode("\n", $settings->invoice_contacts_left)))
                            : [];
                    @endphp
                    @for($i = 0; $i < count($contactsLeft); $i += 2)
                        <div class="contact-name">{{ $contactsLeft[$i] ?? '' }}</div>
                        <div class="contact-number">{{ $contactsLeft[$i + 1] ?? '' }}</div>
                    @endfor
                </div>

                {{-- Center Logo / Brand --}}
                <div class="header-col-center">
                    @if(!empty($settings->invoice_logo) && file_exists(public_path($settings->invoice_logo)))
                        <div class="logo-wrap">
                            <img src="{{ asset($settings->invoice_logo) }}" alt="Logo">
                        </div>
                    @elseif(!empty($settings->invoice_company_name))
                        <div class="logo-fallback-badge">
                            {{ $settings->invoice_company_name }}
                        </div>
                    @endif

                    @if(!empty(trim($settings->invoice_tagline ?? '')))
                        <div class="company-tagline">{{ $settings->invoice_tagline }}</div>
                    @endif

                    @if(!empty(trim($settings->invoice_sub_description ?? '')))
                        <div class="sub-description">{!! nl2br(e($settings->invoice_sub_description)) !!}</div>
                    @endif
                </div>

                {{-- Right Contacts --}}
                <div class="header-col-right">
                    @php
                        $contactsRight = !empty(trim($settings->invoice_contacts_right ?? ''))
                            ? array_filter(array_map('trim', explode("\n", $settings->invoice_contacts_right)))
                            : [];
                    @endphp
                    @foreach($contactsRight as $cLine)
                        @if(str_contains(strtolower($cLine), 'ptcl'))
                            <div class="contact-name mt-1">{{ $cLine }}</div>
                        @elseif(preg_match('/[a-zA-Z]/', $cLine))
                            <div class="contact-name">{{ $cLine }}</div>
                        @else
                            <div class="contact-number">{{ $cLine }}</div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Metadata Bar: Invoice No & Date --}}
            <div class="meta-bar">
                <div>
                    <span class="inv-num-label">No:</span>
                    <span class="inv-num-val">{{ $sale->invoice_no }}</span>
                </div>
                <div class="date-box">
                    <span class="date-label">Date:</span>
                    <span class="date-underline">{{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}</span>
                </div>
            </div>

            {{-- Customer Name / Party --}}
            <div class="customer-row">
                <span class="customer-label">M/S:</span>
                <span class="customer-name">
                    @php
                        $custName = optional($sale->customer)->customer_name ?? $sale->sub_customer ?? 'N/A';
                        $custUrdu = optional($sale->customer)->customer_name_ur;
                        $custAddr = optional($sale->customer)->address;
                    @endphp
                    {{ $custName }}
                    @if($custAddr)
                        - {{ $custAddr }}
                    @endif
                </span>
            </div>

            {{-- Table Wrapper with Watermark --}}
            <div class="table-wrap">
                @if($settings->invoice_show_watermark ?? true)
                    <div class="watermark-container">
                        @if($settings->invoice_logo && file_exists(public_path($settings->invoice_logo)))
                            <img src="{{ asset($settings->invoice_logo) }}" alt="Watermark">
                        @else
                            <div class="watermark-text">{{ $settings->invoice_company_name ?? 'GLC' }}</div>
                        @endif
                    </div>
                @endif

                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th class="col-sr">S#</th>
                            <th class="col-desc">PARTICULARS</th>
                            <th class="col-qty">QTY.</th>
                            <th class="col-rate">RATE</th>
                            <th class="col-amt">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sr = 1;
                            $computedTotal = 0;
                        @endphp

                        @if($sale->saleItems && $sale->saleItems->count() > 0)
                            @foreach($sale->saleItems as $item)
                                @php
                                    $itemQty = (float) $item->sales_qty;
                                    $itemRate = (float) $item->sales_price;
                                    $lineAmt = (float) ($item->amount > 0 ? $item->amount : ($itemQty * $itemRate));
                                    $computedTotal += $lineAmt;
                                    $productName = optional($item->product)->item_name ?? 'Item';
                                    $productCode = optional($item->product)->item_code;
                                @endphp
                                <tr>
                                    <td class="col-sr">{{ $sr++ }}</td>
                                    <td class="col-desc">
                                        {{ $productName }}
                                        @if($productCode)
                                            <span style="font-weight: 600; font-size: 9.5px; opacity: 0.85;">({{ $productCode }})</span>
                                        @endif
                                    </td>
                                    <td class="col-qty">
                                        {{ $itemQty == (int)$itemQty ? (int)$itemQty : number_format($itemQty, 2) }}
                                    </td>
                                    <td class="col-rate">{{ number_format($itemRate, 2) }}</td>
                                    <td class="col-amt">{{ number_format($lineAmt, 2) }}</td>
                                </tr>
                            @endforeach
                        @endif

                        {{-- Fill Remaining Empty Rows to Maintain Exact A4 Height Structure --}}
                        @for($k = 0; $k < $emptyRowsCount; $k++)
                            <tr class="empty-row">
                                <td class="col-sr">{{ $sr++ }}</td>
                                <td class="col-desc"></td>
                                <td class="col-qty"></td>
                                <td class="col-rate"></td>
                                <td class="col-amt"></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- Summary & Notes --}}
            <div class="bottom-summary-wrap">
                {{-- Notes / Policy --}}
                <div class="notes-col">
                    <strong>NOTE:</strong>
                    <ul class="notes-list">
                        @php
                            $notes = array_filter(array_map('trim', explode("\n", $settings->invoice_terms_notes ?? "1. No Return no Exchange without bill\n2. Change of Mind is not accepted\n3. Damaged or Burned not Accepted")));
                        @endphp
                        @foreach($notes as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                </div>

                {{-- Total & Signature --}}
                <div class="total-col">
                    <div class="total-row">
                        <span class="total-label">TOTAL:</span>
                        <span class="total-amount">
                            @php
                                $finalTotal = ($sale->party_type == 'credit') ? ($sale->total_balance ?? $sale->total_net) : $sale->total_net;
                                if ($finalTotal <= 0 && $computedTotal > 0) {
                                    $finalTotal = $computedTotal;
                                }
                                $receiptsA4 = \App\Models\ReceiptsVoucher::with('Account')
                                    ->where(function($q) use ($sale) {
                                        $q->where('sale_id', $sale->id)
                                          ->orWhere('reference_no', $sale->invoice_no);
                                    })
                                    ->where(function($q) {
                                        $q->whereNull('status')->orWhere('status', '!=', 'voided');
                                    })
                                    ->get();
                                $paidA4 = (float)$receiptsA4->sum('total_amount');
                                if ($paidA4 <= 0) {
                                    $paidA4 = (float)$receiptsA4->sum('amount');
                                }
                                if ($paidA4 <= 0) {
                                    $paidA4 = (float)(($sale->receipt1 ?? 0) + ($sale->receipt2 ?? 0));
                                }
                                $balanceA4 = max(0, $finalTotal - $paidA4);
                            @endphp
                            {{ number_format($finalTotal, 2) }}
                        </span>
                    </div>

                    @if($paidA4 > 0)
                    <div class="total-row" style="margin-top: 4px;">
                        <span class="total-label" style="font-size: 11.5px; color: #166534;">PAID:</span>
                        <span class="total-amount" style="font-size: 12px; color: #166534; border-bottom: 1.5px solid #166534;">
                            {{ number_format($paidA4, 2) }}
                        </span>
                    </div>
                    <div class="total-row" style="margin-top: 4px;">
                        <span class="total-label" style="font-size: 11.5px; color: {{ $balanceA4 > 0 ? '#b91c1c' : '#000' }};">BALANCE:</span>
                        <span class="total-amount" style="font-size: 12px; color: {{ $balanceA4 > 0 ? '#b91c1c' : '#000' }}; border-bottom: 2px solid {{ $balanceA4 > 0 ? '#b91c1c' : '#000' }};">
                            {{ number_format($balanceA4, 2) }}
                        </span>
                    </div>
                    @endif

                    <div class="signature-row">
                        <span class="signature-label">Signature</span>
                        <span class="signature-line"></span>
                    </div>
                </div>
            </div>

        {{-- Footer Banner & Credit --}}
        @php
            $hasBranch1 = !empty(trim($settings->invoice_branch_1 ?? ''));
            $hasBranch2 = !empty(trim($settings->invoice_branch_2 ?? ''));
            $hasCredit = !empty(trim($settings->invoice_footer_credit ?? ''));
        @endphp

        @if($hasBranch1 || $hasBranch2 || $hasCredit)
        <div>
            @if($hasBranch1 || $hasBranch2)
            <div class="invoice-footer-banner">
                @if($hasBranch1)
                    <div class="branch-line">{{ $settings->invoice_branch_1 }}</div>
                @endif
                @if($hasBranch2)
                    <div class="branch-line">{{ $settings->invoice_branch_2 }}</div>
                @endif
            </div>
            @endif

            @if($hasCredit)
                <div class="footer-credit">
                    {{ $settings->invoice_footer_credit }}
                </div>
            @endif
        </div>
        @endif

    </div>

</body>
</html>

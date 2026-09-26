@extends('admin_panel.layout.app')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Nastaliq+Urdu:wght@400;700&display=swap');

body {
    font-family: 'Inter', Arial, Helvetica, sans-serif;
    background: #f4f6f9;
    color: #000;
}

.invoice-wrapper {
    background: #fff;
    padding: 8px 14px;
    max-width: 950px;
    margin: 8px auto;
    border-radius: 4px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    color: #000;
}

.invoice-badge-box {
    border: 1.5px solid #000;
    border-radius: 6px;
    padding: 1px 10px;
    text-align: center;
    background: #fff;
    display: flex;
    align-items: center;
    gap: 8px;
}

.invoice-badge-title {
    font-size: 17px;
    font-weight: 800;
    letter-spacing: 1px;
    color: #000;
    line-height: 1;
    text-transform: uppercase;
}

.invoice-badge-inv {
    font-size: 12px;
    font-weight: 800;
    color: #000;
}

.invoice-badge-date {
    font-size: 11.5px;
    font-weight: 700;
    color: #000;
}

.header-divider {
    border-top: 1.5px dashed #000;
    margin: 1px 0 2px 0;
}

.pandi-box {
    font-size: 12.5px;
    font-weight: bold;
    color: #000;
    direction: rtl;
    text-align: right;
    padding: 1px 4px;
}

.pandi-label {
    font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Tahoma', sans-serif;
    font-size: 13.5px;
    margin-left: 3px;
    font-weight: 700;
}

.pandi-line {
    border-bottom: 1px solid #000;
    display: inline-block;
    min-width: 150px;
    padding-right: 4px;
    font-weight: 700;
    font-size: 12px;
    text-align: right;
}

.customer-info-line {
    border: 1px solid #000;
    border-radius: 3px;
    padding: 1px 6px;
    font-size: 12px;
    font-weight: 800;
    color: #000;
    background: #fff;
    direction: rtl;
    display: flex;
    align-items: center;
    gap: 5px;
}

.customer-info-label {
    font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Tahoma', sans-serif;
    font-size: 13px;
    font-weight: 700;
}

.invoice-table {
    width: 100%;
    border-collapse: collapse;
    border: 1.5px solid #000;
    margin-top: 2px;
}

.invoice-table th {
    border: 1px solid #000;
    padding: 1px 3px;
    text-align: center;
    font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Tahoma', sans-serif;
    font-size: 13.5px;
    font-weight: 800;
    background: #ffffff;
    color: #000;
    vertical-align: middle;
    line-height: 1.0;
}

.invoice-table td {
    border: 1px solid #000;
    padding: 0px 2px;
    font-size: 10.5px;
    color: #000;
    vertical-align: middle;
    line-height: 1.0;
}

.num-bold {
    font-weight: 800 !important;
    color: #000 !important;
}

.item-desc-eng {
    font-weight: 700;
    font-size: 10px;
    color: #000;
    display: inline;
    line-height: 1.0;
    letter-spacing: 0.1px;
}

.item-desc-urdu {
    font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Tahoma', sans-serif;
    font-size: 10.5px;
    font-weight: 700;
    color: #000;
    display: inline;
    text-align: right;
    direction: rtl;
    margin-top: 0px;
    line-height: 1.0;
}

.summary-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
    font-size: 10.5px;
    font-weight: 700;
}

.summary-table td {
    padding: 0px 3px;
    border: 1px solid #000;
    line-height: 1.0;
}

.payment-remarks-title {
    font-size: 11.5px;
    font-weight: 800;
    color: #000;
    margin-bottom: 1px;
}

.payment-remarks-line {
    border-top: 1.5px solid #000;
    margin-bottom: 2px;
    width: 100%;
}

.amount-in-words {
    font-size: 10.5px;
    font-weight: 800;
    color: #000;
    line-height: 1.0;
}

@media print {
    .no-print { display: none !important; }
    body { background: #fff !important; margin: 0; padding: 0; }
    .invoice-wrapper {
        box-shadow: none !important;
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 2px 4px !important;
        border: none !important;
    }
    @page {
        size: A4 portrait;
        margin: 2mm 3mm;
    }
    .invoice-table th {
        padding: 1px 2px !important;
        font-size: 12.5px !important;
        line-height: 1.0 !important;
    }
    .invoice-table td {
        padding: 0px 2px !important;
        font-size: 10px !important;
        line-height: 1.0 !important;
    }
    .item-desc-eng {
        font-size: 10.5px !important;
        font-weight: 800 !important;
        line-height: 1.0 !important;
    }
    .item-desc-urdu {
        font-size: 10.5px !important;
        font-weight: 700 !important;
        line-height: 1.0 !important;
    }
    .summary-table td {
        padding: 0px 2px !important;
        line-height: 1.0 !important;
    }
    tr { page-break-inside: avoid; }
}
</style>

@php
if (!function_exists('invoiceNumberToWords')) {
    function invoiceNumberToWords($number) {
        $number = (float) $number;
        $fraction = round(($number - floor($number)) * 100);
        $number = floor($number);
        
        $words = [
            0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
            20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        ];

        if ($number == 0) {
            return 'Rs. Zero Only';
        }

        $convertThreeDigits = function($num) use ($words) {
            $str = '';
            if ($num >= 100) {
                $hundreds = (int)($num / 100);
                $str .= $words[$hundreds] . ' Hundred';
                $num %= 100;
                if ($num > 0) $str .= ' ';
            }
            if ($num > 0) {
                if ($num < 20) {
                    $str .= $words[$num];
                } else {
                    $tens = ((int)($num / 10)) * 10;
                    $units = $num % 10;
                    $str .= $words[$tens];
                    if ($units > 0) $str .= ' ' . $words[$units];
                }
            }
            return $str;
        };

        $res = '';
        if ($number >= 10000000) {
            $crores = (int)($number / 10000000);
            $res .= $convertThreeDigits($crores) . ' Crore ';
            $number %= 10000000;
        }
        if ($number >= 100000) {
            $lakhs = (int)($number / 100000);
            $res .= $convertThreeDigits($lakhs) . ' Lakh ';
            $number %= 100000;
        }
        if ($number >= 1000) {
            $thousands = (int)($number / 1000);
            $res .= $convertThreeDigits($thousands) . ' Thousand ';
            $number %= 1000;
        }
        if ($number > 0) {
            $res .= $convertThreeDigits($number);
        }

        $res = trim($res);
        if ($fraction > 0) {
            $res .= ' and ' . $convertThreeDigits($fraction) . ' Cents';
        }

        return 'Rs. ' . $res . ' Only';
    }
}

if (!function_exists('getUrduProductName')) {
    function getUrduProductName($item) {
        $product = $item->product ?? null;
        if ($product) {
            if (!empty($product->item_name_urdu)) return $product->item_name_urdu;
            if (!empty($product->urdu_name)) return $product->urdu_name;
            if (!empty($product->name_urdu)) return $product->name_urdu;
        }
        if (!empty($item->item_name_urdu)) return $item->item_name_urdu;
        if (!empty($item->product_name_urdu)) return $item->product_name_urdu;
        
        $engName = strtoupper(trim($product->item_name ?? $item->product_name ?? $item->item_name ?? ''));
        if (empty($engName)) return '';

        $dictionary = [
            'RING'      => 'رنگ',
            'RINGS'     => 'رنگ',
            'NTR'       => 'این ٹی آر',
            'ARMATURE'  => 'آر میچر',
            'GRINDER'   => 'گرائنڈر',
            'BOSCH'     => 'بوش',
            'HITACHI'   => 'اٹہیچی',
            'MAKITA'    => 'مکیٹا',
            'DEWALT'    => 'ڈیوائلٹ',
            'DEW'       => 'ڈیوائلٹ',
            'SHENZHANG' => 'شنژنگ',
            'AEG'       => 'AEG',
            'SAW'       => 'آری',
            'CIRCULAR'  => 'سرکلر',
            'HAMMER'    => 'ہیمر',
            'ROUTER'    => 'روٹر',
            'BATTERY'   => 'بیٹری',
            'BLADE'     => 'بلیڈ',
            'CHISEL'    => 'چھینی',
            'CHUCK'     => 'چک',
            'NUT'       => 'نٹ',
            'CORE'      => 'کور',
            'BIT'       => 'بٹ',
            'FIELD'     => 'فیلڈ',
            'FILTER'    => 'فلٹر',
            'GEAR'      => 'گراری',
            'HEAD'      => 'ہیڈ',
            'CUP'       => 'کپ',
            'SPRING'    => 'سپرنگ',
            'JALIBI'    => 'جلیبی',
            'SHAFT'     => 'شافٹ',
            'STOCKER'   => 'سٹوکر',
            'SWITCH'    => 'سوئچ',
            'BLOWER'    => 'بلور',
            'WASHER'    => 'واشر',
            'MITRE'     => 'میٹر',
            'TRIMMER'   => 'ٹریمر',
            'NEW'       => 'نیا',
            'MODEL'     => 'ماڈل',
            'SMALL'     => 'چھوٹا',
            'NORMAL'    => 'عام',
            'SEGMENTED' => 'جھرری',
            'TESTING'   => 'ٹیسٹنگ',
            'TEST'      => 'ٹیسٹ',
            'BEARING'   => 'بیرنگ',
            'BUSH'      => 'بش',
            'PIN'       => 'پن',
            'OIL'       => 'ائل',
            'SEAL'      => 'سیل',
            'GASKET'    => 'گاسکٹ',
            'PUMP'      => 'پمپ',
            'VALVE'     => 'والو',
            'PLUG'      => 'پلگ',
            'PADI'      => 'پانڈی',
            'PLATE'     => 'پلیٹ',
            'ROD'       => 'راڈ',
            'DISC'      => 'ڈسک',
            'CLUTCH'    => 'کلچ',
            'PAD'       => 'پیڈ',
            'COVER'     => 'کور',
            'BODY'      => 'باڈی',
            'LEVER'     => 'لیور',
            'ARM'       => 'آرم',
            'FAN'       => 'فین',
            'CABLE'     => 'کیبل',
            'WIRE'      => 'وائر',
            'HOSE'      => 'ہوز',
            'PIPE'      => 'پائپ',
            'WHEEL'     => 'ویہل',
            'LOCK'      => 'لاک',
            'KEY'       => 'کی',
            'SCREW'     => 'سکرو',
            'BOLT'      => 'بولٹ',
            'KIT'       => 'کِٹ',
            'ASSY'      => 'اسمبلی',
            'SET'       => 'سیٹ',
            'STD'       => 'سٹینڈرڈ',
            'STANDARD'  => 'سٹینڈرڈ',
            'BIG'       => 'بڑا',
            'HEAVY'     => 'ہیوی',
            'DUTY'      => 'ڈیوٹی',
            'SPECIAL'   => 'سپیشل',
            'SUPER'     => 'سپر',
            'AUTO'      => 'آٹو',
            'GENUINE'   => 'جینوئن',
            'ORIGINAL'  => 'اورجنل',
            'JAPAN'     => 'جاپان',
            'CHINA'     => 'چائنا',
            'TAIWAN'    => 'تائیوان',
            'KOREA'     => 'کوریا',
            'GERMANY'   => 'جرمنی',
            'HONDA'     => 'ہونڈا',
            'YAMAHA'    => 'یا مہا',
            'SUZUKI'    => 'سوزوکی',
            'TOYOTA'    => 'ٹویوٹا',
            'CROWN'     => 'کراؤن',
            'LIFAN'     => 'لیفان',
        ];

        $letterAbbr = [
            'A' => 'اے', 'B' => 'بی', 'C' => 'سی', 'D' => 'ڈی', 'E' => 'ای',
            'F' => 'ایف', 'G' => 'جی', 'H' => 'ایچ', 'I' => 'آئی', 'J' => 'جے',
            'K' => 'کے', 'L' => 'ایل', 'M' => 'ایم', 'N' => 'این', 'O' => 'او',
            'P' => 'پی', 'Q' => 'کیو', 'R' => 'آر', 'S' => 'ایس', 'T' => 'ٹی',
            'U' => 'یو', 'V' => 'وی', 'W' => 'ڈبلیو', 'X' => 'ایکس', 'Y' => 'وائے', 'Z' => 'زیڈ'
        ];

        $urduWords = [];
        $words = explode(' ', $engName);
        foreach ($words as $word) {
            $cleanWord = trim($word, '()[]"\'*,.-');
            if (empty($cleanWord)) continue;

            if (isset($dictionary[$cleanWord])) {
                $urduWords[] = $dictionary[$cleanWord];
            } else if (preg_match('/^[A-Z]{2,4}$/', $cleanWord)) {
                $letters = str_split($cleanWord);
                $abbrArr = [];
                foreach ($letters as $l) {
                    $abbrArr[] = $letterAbbr[$l] ?? $l;
                }
                $urduWords[] = implode(' ', $abbrArr);
            } else if (preg_match('/^([A-Z])(\d+)$/i', $cleanWord, $m)) {
                $let = strtoupper($m[1]);
                $num = $m[2];
                $letUrdu = $letterAbbr[$let] ?? $let;
                $urduWords[] = $letUrdu . ' ' . $num;
            } else if (preg_match('/^\d+["\']?$/', $cleanWord) || preg_match('/^[A-Z0-9\/]+$/i', $cleanWord)) {
                $urduWords[] = $cleanWord;
            }
        }

        if (!empty($urduWords)) {
            return implode(' ', $urduWords);
        }

        return $engName;
    }
}

// Calculations
$grossTotal = 0;
if(isset($sale->saleItems)){
    foreach($sale->saleItems as $item){
        $grossTotal += (float)($item->amount ?? 0);
    }
}
if($grossTotal == 0 && isset($sale->sub_total1)){
    $grossTotal = (float)$sale->sub_total1;
}

$orderLevelDiscount = (float)($sale->discount_amount ?? 0);
$additionalDiscount = (float)($sale->additional_discount ?? 0);
$extraCharges = (float)($sale->extra_charges ?? 0);

$netTotal = $grossTotal - $orderLevelDiscount - $additionalDiscount + $extraCharges;
$netTotal = max(0, $netTotal);

$totalReceived = 0;
if(isset($sale) && isset($sale->invoice_no)){
    $receipts = \App\Models\ReceiptsVoucher::where('reference_no', $sale->invoice_no)
        ->where('type', 'SALE_RECEIPT')
        ->get();
    $totalReceived = (float) $receipts->sum('total_amount');
}

$displayPrevious = 0;
$displayClosing = 0;
$isCreditCustomer = ($sale->party_type ?? '') === 'credit';

if($isCreditCustomer && isset($sale->customer)){
    $ledgerData = \App\Models\CustomerLedger::where('customer_id', $sale->customer->id)
        ->latest('id')
        ->first();
    
    if($ledgerData){
        $displayPrevious = floatval($ledgerData->previous_balance ?? 0);
        $displayClosing = floatval($ledgerData->closing_balance ?? 0);
    } else {
        $displayPrevious = floatval($sale->customer->opening_balance ?? 0);
        $displayClosing = $netTotal - $totalReceived + $displayPrevious;
    }
} else {
    $displayClosing = max(0, $netTotal - $totalReceived);
}

$grandTotal = $netTotal + $displayPrevious;
$balanceDue = $displayClosing;
@endphp

<div class="container-fluid mt-3">

    {{-- ACTION BUTTONS --}}
    <div class="text-end mb-3 no-print d-flex justify-content-end gap-2">
        <button type="button" onclick="shareWhatsApp()" class="btn btn-outline-success shadow-sm" style="border-color:#25D366; color:#25D366; background: #fff;">
            <i class="fab fa-whatsapp me-1"></i> WhatsApp
        </button>
        <button type="button" onclick="showExportOptions()" class="btn btn-outline-info shadow-sm" style="background: #fff;">
            <i class="fas fa-download me-1"></i> Export
        </button>
        <button onclick="window.print()" class="btn btn-dark px-4 shadow-sm">
            🖨️ Print Invoice
        </button>
        <button onclick="window.open('{{ url('sale/print2') }}/{{ $sale->id }}', '_blank')" class="btn btn-primary px-4 shadow-sm">
            🧾 Thermal
        </button>
    </div>

    <div class="invoice-wrapper" id="invoiceContent">

        {{-- HEADER SECTION (Company details removed) --}}
        <div class="d-flex justify-content-between align-items-center">
            <div class="invoice-badge-title">
                INVOICE
            </div>
            <div class="invoice-badge-box">
                <span class="invoice-badge-inv">INV #: {{ $sale->invoice_no }}</span>
                <span style="border-right: 1px solid #000; height: 14px; display: inline-block;"></span>
                <span class="invoice-badge-date">DATE: {{ $sale->created_at ? $sale->created_at->format('d M Y') : date('d M Y') }}</span>
            </div>
        </div>

        {{-- DASHED DIVIDER --}}
        <div class="header-divider"></div>

        {{-- INFO SECTION (ADDA & CUSTOMER DETAILS IN A SINGLE COMPACT LINE) --}}
        @php
            $custUrdu = $sale->customer->customer_name_ur ?? $sale->customer->name_urdu ?? $sale->customer_name_ur ?? '';
            $custEng = $sale->party_type === 'walking' ? ($sale->sub_customer ?? 'N/A') : ($sale->customer->customer_name ?? 'N/A');
            $custDisplay = !empty($custUrdu) ? ($custUrdu . ($custEng !== 'N/A' && $custEng !== $custUrdu ? ' (' . $custEng . ')' : '')) : $custEng;
        @endphp
        <div class="d-flex justify-content-between align-items-center my-1">
            <div class="pandi-box">
                <span class="pandi-label">اڈا:</span>
                <span class="pandi-line">
                    {{ $sale->remarks ?? ($sale->address ?? '') }}
                </span>
            </div>

            <div class="customer-info-line">
                <span class="customer-info-label">نام خریدار:</span>
                <span style="font-weight: 800; color: #000; margin-left: 2px; margin-right: 6px; font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Inter', sans-serif;">
                    {{ $custDisplay }}
                </span>
                <span style="border-right: 1.5px solid #000; height: 12px; display: inline-block; margin: 0 4px;"></span>
                <span class="customer-info-label">فون نمبر:</span>
                <span style="font-weight: 800; color: #000; margin-left: 2px;">
                    {{ $sale->tel ?? ($sale->customer->mobile ?? 'N/A') }}
                </span>
            </div>
        </div>

        {{-- MAIN ITEMS TABLE --}}
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 13%;">رقم</th>
                    <th style="width: 11%;">قیمت</th>
                    <th style="width: 11%;">ریٹ</th>
                    <th style="width: 52%;">تفصیل</th>
                    <th style="width: 8%;">تعداد</th>
                    <th style="width: 5%;">سیریل</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $index => $item)
                @php
                    $qty = (float)($item->sales_qty ?? 0);
                    $rate = (float)($item->retail_price ?? $item->sales_price ?? 0);
                    $amt = (float)($item->amount ?? ($qty * $rate));
                    $unitPrice = $qty > 0 ? ($amt / $qty) : $rate;
                    
                    $productEng = strtoupper($item->product->item_name ?? $item->product->product_name ?? 'N/A');
                    $productUrdu = getUrduProductName($item);
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: 800; font-size: 11px; color: #000;">
                        {{ number_format($amt, 0) }}
                    </td>
                    <td style="text-align: center; font-weight: 800; font-size: 11px; color: #000;">
                        {{ number_format($unitPrice, 0) }}
                    </td>
                    <td style="text-align: center; font-weight: 800; font-size: 11px; color: #000;">
                        {{ number_format($rate, 0) }}
                    </td>
                    <td style="direction: rtl; text-align: right; padding: 0px 3px;">
                        @if(!empty($productUrdu) && $productUrdu !== $productEng)
                            <span class="item-desc-urdu" style="display: inline; margin-left: 5px;">{{ $productUrdu }}</span>
                        @endif
                        <span class="item-desc-eng" style="display: inline;">{{ $productEng }}</span>
                    </td>
                    <td style="text-align: center; font-weight: 800; font-size: 11.5px; color: #000;">
                        {{ (int)$qty == $qty ? (int)$qty : number_format($qty, 2) }}
                    </td>
                    <td style="text-align: center; font-weight: 800; font-size: 11px; color: #000;">
                        {{ $index + 1 }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- SUMMARY & REMARKS SECTION --}}
        <div class="d-flex justify-content-between align-items-start mt-2" style="page-break-inside: avoid;">
            {{-- LEFT: TOTALS TABLE --}}
            <div style="width: 45%;">
                <table class="summary-table">
                    <tr>
                        <td style="background: #fafafa; width: 45%;">Subtotal</td>
                        <td style="text-align: right; width: 55%; font-weight: 800; color: #000;">Rs. {{ number_format($netTotal, 2) }}</td>
                    </tr>
                    @if($displayPrevious != 0)
                    <tr>
                        <td style="background: #fafafa;">Previous Balance</td>
                        <td style="text-align: right; font-weight: 800; color: #000;">Rs. {{ number_format($displayPrevious, 2) }}</td>
                    </tr>
                    @endif
                    <tr style="font-size: 12px; background: #fafafa;">
                        <td>Grand Total</td>
                        <td style="text-align: right; font-weight: 800; color: #000;">Rs. {{ number_format($grandTotal, 2) }}</td>
                    </tr>
                    <tr style="font-size: 12px;">
                        <td>Balance Due</td>
                        <td style="text-align: right; font-weight: 800; color: #000;">Rs. {{ number_format($balanceDue, 2) }}</td>
                    </tr>
                </table>
            </div>

            {{-- RIGHT: PAYMENT REMARKS & AMOUNT IN WORDS --}}
            <div style="width: 50%;">
                <div class="payment-remarks-title">
                    Payment Remarks:
                </div>
                <div class="payment-remarks-line"></div>
                <div class="amount-in-words">
                    {{ invoiceNumberToWords($grandTotal) }}
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('js')
<script>
window.shareWhatsApp = function() {
    Swal.fire({
        title: 'Preparing WhatsApp Share...',
        text: 'Generating PDF document to share.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    var element = document.getElementById('invoiceContent');
    var opt = {
      margin:       [0.2, 0.2, 0.2, 0.2],
      filename:     'Sale_Invoice_{{ $sale->invoice_no }}.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
        var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
        
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({
                title: 'Sale Invoice',
                text: 'Please find attached Sale Invoice #{{ $sale->invoice_no }}.',
                files: [file]
            }).then(() => {
                Swal.close();
            }).catch((error) => {
                fallbackWaShare(pdfBlob, opt.filename);
            });
        } else {
            fallbackWaShare(pdfBlob, opt.filename);
        }
    });
};

function fallbackWaShare(pdfBlob, filename) {
    Swal.fire({
        icon: 'info',
        title: 'Share PDF via WhatsApp',
        text: 'The PDF will be downloaded now. WhatsApp will open allowing you to choose any chat.',
        confirmButtonText: 'Download & Open WhatsApp'
    }).then(() => {
        var url = URL.createObjectURL(pdfBlob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        
        var msg = "*Sale Invoice #{{ $sale->invoice_no }}*\nPlease find attached PDF document.";
        var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
        window.open(waUrl, '_blank');
    });
}

window.showExportOptions = function() {
    Swal.fire({
        title: 'Export Sale Invoice',
        text: 'Choose your preferred export format:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        confirmButtonText: '<i class="fas fa-file-excel me-1"></i> Excel (CSV)',
        cancelButtonText: '<i class="fas fa-file-pdf me-1"></i> PDF',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            exportCSV();
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            exportPDF();
        }
    });
};

window.exportPDF = function() {
    Swal.fire({
        title: 'Generating PDF...',
        text: 'Please wait while your PDF is being prepared.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    var element = document.getElementById('invoiceContent');
    var opt = {
      margin:       [0.2, 0.2, 0.2, 0.2],
      filename:     'Sale_Invoice_{{ $sale->invoice_no }}.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save().then(function() {
        Swal.close();
    });
};

window.exportCSV = function () {
    var rows = [['رقم (Amount)', 'قیمت (Price)', 'ریٹ (Rate)', 'تفصیل (Details)', 'تعداد (Qty)']];
    
    $('.invoice-table tbody tr').each(function () {
        var cells = [];
        $(this).find('td').each(function () {
            var text = $(this).text().trim().replace(/\s+/g, ' ').replace(/"/g, '""');
            cells.push('"' + text + '"');
        });
        if (cells.length) rows.push(cells);
    });
    
    rows.push([]);
    rows.push(['Grand Total', '', '', '', 'Rs. {{ number_format($grandTotal, 2) }}']);
    rows.push(['Balance Due', '', '', '', 'Rs. {{ number_format($balanceDue, 2) }}']);

    var csv  = rows.map(function(r){return r.join(',');}).join('\n');
    var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8;'});
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href   = url;
    a.download = 'Sale_Invoice_{{ $sale->invoice_no }}.csv';
    a.click();
};
</script>
@endsection

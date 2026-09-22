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
    padding: 30px 40px;
    max-width: 950px;
    margin: 20px auto;
    border-radius: 4px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    color: #000;
}

.company-title {
    font-size: 32px;
    font-weight: 800;
    color: #7A0000;
    margin: 0;
    line-height: 1.1;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.company-address {
    font-size: 13.5px;
    color: #333;
    margin-top: 4px;
    font-weight: 500;
}

.company-phones {
    font-size: 14px;
    color: #000;
    font-weight: 700;
    margin-top: 4px;
    line-height: 1.35;
}

.invoice-badge-box {
    border: 2px solid #000;
    border-radius: 18px;
    padding: 8px 30px;
    text-align: center;
    min-width: 200px;
    background: #fff;
}

.invoice-badge-title {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: 1px;
    color: #000;
    line-height: 1.1;
}

.invoice-badge-inv {
    font-size: 15px;
    font-weight: 700;
    color: #000;
    margin-top: 3px;
}

.invoice-badge-date {
    font-size: 14px;
    font-weight: 600;
    color: #222;
    margin-top: 2px;
}

.header-divider {
    border-top: 1.5px dashed #000;
    margin: 14px 0 16px 0;
}

.pandi-box {
    font-size: 16px;
    font-weight: bold;
    color: #000;
    direction: rtl;
    text-align: right;
}

.pandi-label {
    font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Tahoma', sans-serif;
    font-size: 19px;
    margin-left: 8px;
}

.pandi-line {
    border-bottom: 1px solid #000;
    display: inline-block;
    min-width: 260px;
    padding-right: 10px;
    font-weight: 600;
    font-size: 14px;
    text-align: right;
}

.customer-card-box {
    border: 1px solid #000;
    border-radius: 3px;
    width: 360px;
    border-collapse: collapse;
}

.customer-card-box table {
    width: 100%;
    border-collapse: collapse;
}

.customer-card-box td {
    padding: 5px 12px;
    font-size: 14px;
    font-weight: 700;
    color: #000;
}

.customer-card-label {
    font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Tahoma', sans-serif;
    font-size: 17px;
    text-align: right;
    width: 35%;
    border-left: 1px solid #000;
    background: #fafafa;
    direction: rtl;
}

.customer-card-val {
    text-align: right;
    width: 65%;
    direction: rtl;
}

.invoice-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
    margin-top: 14px;
}

.invoice-table th {
    border: 1px solid #000;
    padding: 6px 8px;
    text-align: center;
    font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Tahoma', sans-serif;
    font-size: 19px;
    font-weight: 700;
    background: #ffffff;
    color: #000;
    vertical-align: middle;
}

.invoice-table td {
    border: 1px solid #000;
    padding: 6px 10px;
    font-size: 14px;
    color: #000;
    vertical-align: middle;
}

.item-desc-eng {
    font-weight: 700;
    font-size: 13.5px;
    color: #000;
    line-height: 1.35;
    letter-spacing: 0.2px;
}

.item-desc-urdu {
    font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Tahoma', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: #111;
    text-align: right;
    direction: rtl;
    margin-top: 4px;
    line-height: 1.45;
}

.summary-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
    font-size: 14px;
    font-weight: 700;
}

.summary-table td {
    padding: 6px 12px;
    border: 1px solid #000;
}

.payment-remarks-title {
    font-size: 18px;
    font-weight: 800;
    color: #000;
    margin-bottom: 4px;
}

.payment-remarks-line {
    border-top: 1.5px solid #000;
    margin-bottom: 12px;
    width: 100%;
}

.amount-in-words {
    font-size: 14.5px;
    font-weight: 700;
    color: #000;
    line-height: 1.4;
}

@media print {
    .no-print { display: none !important; }
    body { background: #fff !important; margin: 0; padding: 0; }
    .invoice-wrapper {
        box-shadow: none !important;
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 15px 20px !important;
        border: none !important;
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

if (!function_exists('getUrduProductNameBooking')) {
    function getUrduProductNameBooking($item) {
        $product = $item->product ?? null;
        if ($product) {
            if (!empty($product->item_name_urdu)) return $product->item_name_urdu;
            if (!empty($product->urdu_name)) return $product->urdu_name;
            if (!empty($product->name_urdu)) return $product->name_urdu;
        }
        
        $engName = strtoupper($product->item_name ?? $item->product_name ?? $item->item_name ?? '');
        
        $dictionary = [
            'ARMATURE' => 'آر میچر',
            'GRINDER'  => 'گرائنڈر',
            'BOSCH'    => 'بوش',
            'HITACHI'  => 'اٹہیچی',
            'MAKITA'   => 'مکیٹا',
            'DEWALT'   => 'ڈیوائلٹ',
            'DEW'      => 'ڈیوائلٹ',
            'SHENZHANG' => 'شنژنگ',
            'AEG'      => 'AEG',
            'SAW'      => 'آری',
            'CIRCULAR' => 'سرکلر',
            'HAMMER'   => 'ہیمر',
            'ROUTER'   => 'روٹر',
            'BATTERY'  => 'بیٹری',
            'BLADE'    => 'بلیڈ',
            'CHISEL'   => 'چھینی',
            'CHUCK'    => 'چک',
            'NUT'      => 'نٹ',
            'CORE'     => 'کور',
            'BIT'      => 'بٹ',
            'FIELD'    => 'فیلڈ',
            'FILTER'   => 'فلٹر',
            'GEAR'     => 'گراری',
            'HEAD'     => 'ہیڈ',
            'CUP'      => 'کپ',
            'SPRING'   => 'سپرنگ',
            'JALIBI'   => 'جلیبی',
            'SHAFT'    => 'شافٹ',
            'STOCKER'  => 'سٹوکر',
            'SWITCH'   => 'سوئچ',
            'BLOWER'   => 'بلور',
            'WASHER'   => 'واشر',
            'MITRE'    => 'میٹر',
            'TRIMMER'  => 'ٹریمر',
            'NEW'      => 'نیا',
            'MODEL'    => 'ماڈل',
            'SMALL'    => 'چھوٹا',
            'NORMAL'   => 'عام',
            'SEGMENTED'=> 'جھرری',
            'TESTING'  => 'ٹیسٹنگ',
            'TEST'     => 'ٹیسٹ',
        ];

        $urduWords = [];
        $words = explode(' ', $engName);
        foreach ($words as $word) {
            $cleanWord = trim($word, '()[]"\'*,.-');
            if (isset($dictionary[$cleanWord])) {
                $urduWords[] = $dictionary[$cleanWord];
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

// Calculations for Booking
$grossTotal = 0;
if(isset($booking->items)){
    foreach($booking->items as $item){
        $grossTotal += (float)($item->amount ?? 0);
    }
}
if($grossTotal == 0 && isset($booking->sub_total1)){
    $grossTotal = (float)$booking->sub_total1;
}

$orderLevelDiscount = (float)($booking->discount_amount ?? 0);
$additionalDiscount = (float)($booking->additional_discount ?? 0);
$extraCharges = (float)($booking->extra_charges ?? 0);

$netTotal = $grossTotal - $orderLevelDiscount - $additionalDiscount + $extraCharges;
$netTotal = max(0, $netTotal);

$totalReceived = 0;
if(isset($booking) && isset($booking->invoice_no)){
    $receipts = \App\Models\ReceiptsVoucher::where('reference_no', $booking->invoice_no)
        ->where('type', 'SALE_RECEIPT')
        ->get();
    $totalReceived = (float) $receipts->sum('total_amount');
}

$displayPrevious = 0;
$displayClosing = 0;
$isCreditCustomer = ($booking->party_type ?? '') === 'credit';

if($isCreditCustomer && isset($booking->customer)){
    $ledgerData = \App\Models\CustomerLedger::where('customer_id', $booking->customer->id)
        ->latest('id')
        ->first();
    
    if($ledgerData){
        $displayPrevious = floatval($ledgerData->previous_balance ?? 0);
        $displayClosing = floatval($ledgerData->closing_balance ?? 0);
    } else {
        $displayPrevious = floatval($booking->customer->opening_balance ?? 0);
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
        <button onclick="window.open('{{ url('booking/print2') }}/{{ $booking->id }}', '_blank')" class="btn btn-primary px-4 shadow-sm">
            🧾 Thermal
        </button>
    </div>

    <div class="invoice-wrapper" id="invoiceContent">

        {{-- HEADER SECTION --}}
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="company-title">
                    {{ strtoupper($branch->name ?? 'ZAIN TRADERS') }}
                </h1>
                <div class="company-address">
                    {{ $branch->address ?? '17th-Brandreth Road, Lahore, Pakistan.' }}
                </div>
                <div class="company-phones">
                    @if(!empty($branch->mobile))
                        {{ $branch->mobile }}
                    @else
                        0300-4235114 &nbsp; 0300-4235114
                    @endif
                    <br>
                    @if(!empty($branch->phone))
                        {{ $branch->phone }}
                    @else
                        042-37635383 &nbsp; 042-37651862
                    @endif
                </div>
            </div>

            <div>
                <div class="invoice-badge-box">
                    <div class="invoice-badge-title">INVOICE</div>
                    <div class="invoice-badge-inv">{{ $booking->invoice_no }}</div>
                    <div class="invoice-badge-date">
                        {{ $booking->created_at ? $booking->created_at->format('d M Y') : date('d M Y') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- DASHED DIVIDER --}}
        <div class="header-divider"></div>

        {{-- INFO SECTION (PANDI & CUSTOMER DETAILS) --}}
        <div class="d-flex justify-content-between align-items-end mb-2">
            <div class="pandi-box">
                <span class="pandi-label">پانڈی:</span>
                <span class="pandi-line">
                    {{ $booking->remarks ?? ($booking->address ?? '') }}
                </span>
            </div>

            <div class="customer-card-box">
                <table>
                    <tr style="border-bottom: 1px solid #000;">
                        <td class="customer-card-val">
                            {{ $booking->party_type === 'walking' ? ($booking->customer_name ?? 'N/A') : ($booking->customer->customer_name ?? 'N/A') }}
                        </td>
                        <td class="customer-card-label">
                            نام خریدار
                        </td>
                    </tr>
                    <tr>
                        <td class="customer-card-val">
                            {{ $booking->tel ?? ($booking->customer->mobile ?? 'N/A') }}
                        </td>
                        <td class="customer-card-label">
                            فون نمبر
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- MAIN ITEMS TABLE --}}
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 13%;">رقم</th>
                    <th style="width: 11%;">قیمت</th>
                    <th style="width: 11%;">ریٹ</th>
                    <th style="width: 57%;">تفصیل</th>
                    <th style="width: 8%;">تعداد</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->items as $index => $item)
                @php
                    $qty = (float)($item->sales_qty ?? 0);
                    $rate = (float)($item->retail_price ?? $item->sales_price ?? 0);
                    $amt = (float)($item->amount ?? ($qty * $rate));
                    $unitPrice = $qty > 0 ? ($amt / $qty) : $rate;
                    
                    $productEng = strtoupper($item->product->item_name ?? $item->product_name ?? 'N/A');
                    $productUrdu = getUrduProductNameBooking($item);
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: 700; font-size: 15px;">
                        {{ number_format($amt, 0) }}
                    </td>
                    <td style="text-align: center; font-size: 15px;">
                        {{ number_format($unitPrice, 0) }}
                    </td>
                    <td style="text-align: center; font-size: 15px;">
                        {{ number_format($rate, 0) }}
                    </td>
                    <td>
                        <div class="item-desc-eng">
                            {{ $productEng }}
                        </div>
                        @if(!empty($productUrdu) && $productUrdu !== $productEng)
                        <div class="item-desc-urdu">
                            {{ $productUrdu }}
                        </div>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: 800; font-size: 16px;">
                        {{ (int)$qty == $qty ? (int)$qty : number_format($qty, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- SUMMARY & REMARKS SECTION --}}
        <div class="d-flex justify-content-between align-items-start mt-4" style="page-break-inside: avoid;">
            {{-- LEFT: TOTALS TABLE --}}
            <div style="width: 45%;">
                <table class="summary-table">
                    <tr>
                        <td style="background: #fafafa; width: 45%;">Subtotal</td>
                        <td style="text-align: right; width: 55%;">Rs. {{ number_format($netTotal, 2) }}</td>
                    </tr>
                    @if($displayPrevious != 0)
                    <tr>
                        <td style="background: #fafafa;">Previous Balance</td>
                        <td style="text-align: right;">Rs. {{ number_format($displayPrevious, 2) }}</td>
                    </tr>
                    @endif
                    <tr style="font-size: 15px; background: #fafafa;">
                        <td>Grand Total</td>
                        <td style="text-align: right; color: #000;">Rs. {{ number_format($grandTotal, 2) }}</td>
                    </tr>
                    <tr style="font-size: 15px;">
                        <td>Balance Due</td>
                        <td style="text-align: right; color: #000;">Rs. {{ number_format($balanceDue, 2) }}</td>
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
      filename:     'Sale_Invoice_{{ $booking->invoice_no }}.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
        var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
        
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({
                title: 'Sale Invoice',
                text: 'Please find attached Sale Invoice #{{ $booking->invoice_no }}.',
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
        
        var msg = "*Sale Invoice #{{ $booking->invoice_no }}*\nPlease find attached PDF document.";
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
      filename:     'Sale_Invoice_{{ $booking->invoice_no }}.pdf',
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
    a.download = 'Sale_Invoice_{{ $booking->invoice_no }}.csv';
    a.click();
};
</script>
@endsection

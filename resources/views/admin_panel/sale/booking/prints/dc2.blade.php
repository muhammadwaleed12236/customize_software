@extends('admin_panel.layout.app')

@section('content')

<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f1f5f9;
        font-size: 13px;
        color: #1e293b;
    }

    .dc-wrapper {
        background: #ffffff;
        width: 100%;
        max-width: 960px;
        margin: 20px auto;
        padding: 35px 40px;
        border-radius: 6px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        page-break-after: always;
        border: 1px solid #cbd5e1;
    }

    /* HEADER */
    .top-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 12px;
        margin-bottom: 20px;
    }

    .company-details h3 {
        margin: 0 0 4px 0;
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: 0.5px;
    }

    .company-details p {
        margin: 2px 0;
        font-size: 12px;
        color: #475569;
    }

    .dc-title-box {
        text-align: right;
    }

    .dc-title-box h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .dc-badge-info {
        display: inline-block;
        background: #f1f5f9;
        color: #0f172a;
        padding: 4px 12px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 12px;
        margin-top: 5px;
        border: 1px solid #cbd5e1;
    }

    /* INFO GRID */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px 20px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
    }

    .info-item {
        display: flex;
        font-size: 13px;
    }

    .info-label {
        font-weight: 600;
        color: #334155;
        width: 140px;
        flex-shrink: 0;
    }

    .info-value {
        color: #0f172a;
        font-weight: 500;
    }

    /* SECTION TITLES */
    .section-title {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        margin: 20px 0 8px 0;
        display: flex;
        align-items: center;
        gap: 6px;
        border-bottom: 1px solid #cbd5e1;
        padding-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* TABLES */
    table.custom-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
        margin-bottom: 15px;
    }

    table.custom-table th {
        background: #334155;
        color: #ffffff;
        padding: 8px 10px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid #334155;
    }

    table.custom-table td {
        padding: 8px 10px;
        border: 1px solid #cbd5e1;
        font-size: 13px;
        color: #1e293b;
    }

    table.custom-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }

    .text-left { text-align: left; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }

    /* SUMMARY CARDS */
    .ledger-summary-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 15px;
    }

    .summary-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 12px 16px;
    }

    .summary-card h5 {
        margin: 0 0 8px 0;
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 4px;
        text-transform: uppercase;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        font-size: 13px;
    }

    .summary-line.total {
        font-weight: 700;
        font-size: 13px;
        border-top: 1px dashed #cbd5e1;
        padding-top: 5px;
        margin-top: 5px;
        color: #0f172a;
    }

    /* BADGES */
    .badge-status {
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-completed { background: #f1f5f9; color: #166534; border: 1px solid #bbf7d0; }
    .badge-partial { background: #fefce8; color: #854d0e; border: 1px solid #fef08a; }
    .badge-pending { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

    /* FOOTER SIGNATURES */
    .footer-signatures {
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
        gap: 20px;
    }

    .sig-box {
        flex: 1;
        text-align: center;
    }

    .sig-line {
        border-top: 1px solid #475569;
        margin-top: 35px;
        padding-top: 5px;
        font-weight: 600;
        font-size: 12px;
        color: #334155;
    }

    .note-box {
        margin-top: 15px;
        font-size: 12px;
        background: #f8fafc;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        color: #334155;
    }

    .no-print {
        margin-bottom: 15px;
    }

    @media print {
        .no-print { display: none !important; }
        body { background: #ffffff !important; font-size: 12px; }
        .dc-wrapper {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        .container-fluid { padding: 0 !important; }
    }
</style>

<div class="container-fluid">

    {{-- ACTION BUTTONS --}}
    <div class="no-print d-flex justify-content-end gap-2 my-3">
        <a href="{{ route('sale.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Sales
        </a>
        <button type="button" onclick="shareWhatsApp()" class="btn btn-outline-success shadow-sm" style="border-color:#25D366; color:#25D366; background: #fff;">
            <i class="fab fa-whatsapp me-1"></i> WhatsApp
        </button>
        <button type="button" onclick="showExportOptions()" class="btn btn-outline-info shadow-sm" style="background: #fff;">
            <i class="fas fa-download me-1"></i> Export
        </button>
        <button onclick="window.print()" class="btn btn-dark shadow-sm ms-2">
            <i class="fas fa-print me-1"></i> Print All
        </button>
        <a href="{{ route('sale.dc.thermal', is_object($sale) ? $sale->id : $sale) }}" target="_blank" class="btn btn-secondary shadow-sm">
            <i class="fas fa-barcode me-1"></i> Thermal Print
        </a>

        @if(!empty($dcData) && count($dcData) > 0)
            @php
                $firstDC = $dcData[0];
                $warehouseOrderId = $firstDC['warehouse_order_id'] ?? null;
            @endphp
            @if($warehouseOrderId)
                <a href="{{ route('outward_gatepass.create', $warehouseOrderId) }}" 
                   class="btn btn-success shadow-sm" 
                   title="Create Gate Pass for this Delivery">
                    <i class="fas fa-truck-loading me-1"></i> Create Gate Pass
                </a>
            @endif
        @endif
    </div>

    <div id="dcContent">
        @foreach($dcData as $dc)

        <div class="dc-wrapper" id="dc-{{ $loop->index }}">

            <div class="no-print d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-secondary fs-6">DC #{{ $loop->iteration }} of {{ count($dcData) }}</span>
                <button type="button" class="btn btn-sm btn-outline-dark" onclick="printSingleDC('dc-{{ $loop->index }}')">
                    <i class="fas fa-print me-1"></i> Print This DC
                </button>
            </div>

            <!-- HEADER -->
            <div class="top-header">
                <div class="company-details">
                    <h3>{{ strtoupper($branch->name ?? 'ZAIN TRADERS') }}</h3>
                    <p>Electronics & Glass Dealer</p>
                    <p>{{ $branch->address ?? 'Main Store / Branch Address' }}</p>
                    <p>Phone: {{ $branch->phone ?? '0300-0000000' }}</p>
                </div>

                <div class="dc-title-box">
                    <h2>Invoice & Delivery Challan</h2>
                    <div class="dc-badge-info">
                        DC No: <strong>{{ $dc['dc_no'] }}</strong>
                    </div>
                </div>
            </div>

            <!-- INFO GRID -->
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Customer Name:</span>
                    <span class="info-value">
                        @if ($sale->party_type == 'credit' || $sale->party_type == 'cash')
                            {{ $sale->customer->customer_name ?? '-' }}
                        @else
                            {{ $sale->sub_customer ?? '-' }}
                        @endif
                    </span>
                </div>

                <div class="info-item">
                    <span class="info-label">Invoice No:</span>
                    <span class="info-value"><strong>{{ $sale->invoice_no }}</strong></span>
                </div>

                <div class="info-item">
                    <span class="info-label">Contact / Mobile:</span>
                    <span class="info-value">
                        @if ($sale->party_type == 'credit' || $sale->party_type == 'cash')
                            {{ $sale->customer->mobile ?? '-' }}
                        @else
                            {{ $sale->tel ?? '-' }}
                        @endif
                    </span>
                </div>

                <div class="info-item">
                    <span class="info-label">Invoice Date:</span>
                    <span class="info-value">{{ $sale->created_at ? $sale->created_at->format('d-M-Y') : date('d-M-Y') }}</span>
                </div>

                <div class="info-item">
                    <span class="info-label">Dispatch Location:</span>
                    <span class="info-value">
                        @php
                            $locationName = $dc['location_name'] ?? '-';
                        @endphp
                        <strong>{{ $locationName }}</strong>
                    </span>
                </div>

                <div class="info-item">
                    <span class="info-label">Delivery Address:</span>
                    <span class="info-value">
                        @php
                            $address = '-';
                            if (isset($dc['delivery_location_type'])) {
                                if ($dc['delivery_location_type'] === 'branch' && isset($dc['branch'])) {
                                    $address = is_array($dc['branch']) ? ($dc['branch']['address'] ?? '-') : ($dc['branch']->address ?? '-');
                                } elseif ($dc['delivery_location_type'] === 'warehouse' && isset($dc['warehouse'])) {
                                    $address = is_array($dc['warehouse']) ? ($dc['warehouse']['address'] ?? '-') : ($dc['warehouse']->address ?? '-');
                                }
                            }
                            if ($address === '-') {
                                if ($sale->party_type == 'credit' || $sale->party_type == 'cash') {
                                    $address = $sale->customer->address ?? '-';
                                } else {
                                    $address = $sale->address ?? '-';
                                }
                            }
                        @endphp
                        {{ $address }}
                    </span>
                </div>
            </div>

            <!-- CURRENT DC DISPATCHED ITEMS TABLE -->
            <div class="section-title">
                Items Dispatched In This Delivery Challan ({{ $dc['dc_no'] }})
            </div>

            @php $currentDcTotalQty = 0; $currentDcTotalAmount = 0; @endphp

            <table class="custom-table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th class="text-left">Product Item Details</th>
                        <th width="14%" class="text-right">Price (Rs.)</th>
                        <th width="14%" class="text-center">DC Qty (Di Qty)</th>
                        <th width="16%" class="text-right">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dc['items'] as $index => $item)
                        @php
                            if (is_array($item) || $item instanceof \Illuminate\Support\Fluent) {
                                $qty = (float) ($item['qty'] ?? $item['sales_qty'] ?? 0);
                                $productName = $item['product_name'] ?? ($item['product']['item_name'] ?? '-');
                                $productCode = $item['item_code'] ?? ($item['product']['item_code'] ?? '');
                                $price = (float) ($item['retail_price'] ?? $item['sales_price'] ?? 0);
                                $amount = (float) ($item['amount'] ?? ($price * $qty));
                            } else {
                                $qty = (float) ($item->sales_qty ?? $item->qty ?? 0);
                                $productName = $item->product->item_name ?? ($item->product_name ?? '-');
                                $productCode = $item->product->item_code ?? ($item->item_code ?? '');
                                $price = (float) ($item->retail_price ?? $item->sales_price ?? 0);
                                $amount = (float) ($item->amount ?? ($price * $qty));
                            }

                            $currentDcTotalQty += $qty;
                            $currentDcTotalAmount += $amount;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index+1 }}</td>
                            <td class="text-left">
                                <strong>{{ $productName }}</strong>
                                @if(!empty($productCode))
                                    <small class="text-muted"> ({{ $productCode }})</small>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format($price, 2) }}</td>
                            <td class="text-center"><strong>{{ $qty }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($amount, 2) }}</strong></td>
                        </tr>
                    @endforeach
                    <tr style="background:#f8fafc; font-weight:700;">
                        <td colspan="3" class="text-right">Total Current DC Dispatch:</td>
                        <td class="text-center">{{ $currentDcTotalQty }}</td>
                        <td class="text-right">{{ number_format($currentDcTotalAmount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- QUANTITY DISPATCH & STOCK REMAINING LEDGER SUMMARY -->
            <div class="section-title">
                Complete Stock Ledger (Booked, Dispatched & Remaining)
            </div>

            @php
                $totBooked = 0;
                $totDispatched = 0;
                $totRemaining = 0;
            @endphp

            <table class="custom-table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th class="text-left">Product Description</th>
                        <th width="15%" class="text-center">Booked Qty</th>
                        <th width="15%" class="text-center">Dispatched Qty</th>
                        <th width="15%" class="text-center">Remaining Qty</th>
                        <th width="18%" class="text-center">Delivery Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($sale->saleItems) && count($sale->saleItems) > 0)
                        @foreach($sale->saleItems as $idx => $sItem)
                            @php
                                $pId = $sItem->product_id;
                                $booked = (float)($sItem->sales_qty ?? $sItem->qty ?? 0);
                                
                                // Look up in CustomerRemaining table
                                $remRecord = null;
                                if (isset($remainingItems) && count($remainingItems) > 0) {
                                    $remRecord = $remainingItems->where('product_id', $pId)->first();
                                }
                                
                                if ($remRecord) {
                                    $remaining = (float)$remRecord->remaining_qty;
                                    $dispatched = max(0, $booked - $remaining);
                                    $status = $remRecord->status;
                                } else {
                                    // Fallback: calculate dispatched from warehouse orders
                                    $dispatched = 0;
                                    if (!empty($dcData)) {
                                        foreach ($dcData as $d) {
                                            foreach ($d['items'] as $itemArr) {
                                                $itemPid = is_array($itemArr) ? ($itemArr['product_id'] ?? null) : ($itemArr->product_id ?? null);
                                                if ($itemPid == $pId) {
                                                    $dispatched += (float)(is_array($itemArr) ? ($itemArr['qty'] ?? 0) : ($itemArr->qty ?? 0));
                                                }
                                            }
                                        }
                                    }
                                    $remaining = max(0, $booked - $dispatched);
                                    if ($remaining <= 0) {
                                        $status = 'completed';
                                    } elseif ($dispatched > 0) {
                                        $status = 'partial';
                                    } else {
                                        $status = 'pending';
                                    }
                                }

                                $totBooked += $booked;
                                $totDispatched += $dispatched;
                                $totRemaining += $remaining;

                                $pName = optional($sItem->product)->item_name ?? $sItem->product_name ?? 'Product #'.$pId;
                                $pCode = optional($sItem->product)->item_code ?? '';
                            @endphp
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td class="text-left">
                                    <strong>{{ $pName }}</strong>
                                    @if(!empty($pCode))
                                        <small class="text-muted"> ({{ $pCode }})</small>
                                    @endif
                                </td>
                                <td class="text-center"><strong>{{ $booked }}</strong></td>
                                <td class="text-center"><strong>{{ $dispatched }}</strong></td>
                                <td class="text-center"><strong>{{ $remaining }}</strong></td>
                                <td class="text-center">
                                    @if($status === 'completed' || $remaining <= 0)
                                        <span class="badge-status badge-completed">Fully Delivered</span>
                                    @elseif($status === 'partial' || ($dispatched > 0 && $remaining > 0))
                                        <span class="badge-status badge-partial">Partially Out</span>
                                    @else
                                        <span class="badge-status badge-pending">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center text-muted">No stock ledger details available for this sale.</td>
                        </tr>
                    @endif

                    <tr style="background:#f8fafc; font-weight:700;">
                        <td colspan="2" class="text-right">Overall Stock Totals:</td>
                        <td class="text-center">{{ $totBooked }}</td>
                        <td class="text-center">{{ $totDispatched }}</td>
                        <td class="text-center">{{ $totRemaining }}</td>
                        <td class="text-center">
                            @if($totRemaining <= 0)
                                <span class="badge-status badge-completed">Fully Delivered</span>
                            @else
                                <span class="badge-status badge-partial">{{ $totRemaining }} Pending</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- FINANCIAL & CUSTOMER LEDGER SUMMARY GRID -->
            <div class="ledger-summary-grid">
                
                <!-- INVOICE BILL SUMMARY -->
                <div class="summary-card">
                    <h5>Invoice Financial Summary</h5>
                    @php
                        $subTotal = (float)($sale->total_bill_amount ?? $sale->total_subtotal ?? $sale->total_amount ?? $currentDcTotalAmount);
                        $discount = (float)($sale->total_discount ?? $sale->total_extradiscount ?? $sale->discount_amount ?? 0);
                        $netTotal = (float)($sale->total_net ?? $sale->net_amount ?? $sale->grand_total ?? ($subTotal - $discount));
                        $paidAmount = (float)($sale->cash ?? $sale->paid_amount ?? $sale->advance_amount ?? 0);
                        $currentInvoiceBalance = max(0, $netTotal - $paidAmount);
                    @endphp

                    <div class="summary-line">
                        <span>Invoice Total Amount:</span>
                        <span>Rs. {{ number_format($subTotal, 2) }}</span>
                    </div>
                    @if($discount > 0)
                    <div class="summary-line">
                        <span>Discount:</span>
                        <span>- Rs. {{ number_format($discount, 2) }}</span>
                    </div>
                    @endif
                    <div class="summary-line total">
                        <span>Net Payable Amount:</span>
                        <span>Rs. {{ number_format($netTotal, 2) }}</span>
                    </div>
                    <div class="summary-line">
                        <span>Paid / Advance:</span>
                        <span>Rs. {{ number_format($paidAmount, 2) }}</span>
                    </div>
                    <div class="summary-line" style="font-weight:600;">
                        <span>Invoice Balance Due:</span>
                        <span>Rs. {{ number_format($currentInvoiceBalance, 2) }}</span>
                    </div>
                </div>

                <!-- CUSTOMER OVERALL LEDGER BALANCE -->
                <div class="summary-card">
                    <h5>Customer Ledger Balance</h5>
                    @php
                        $ledgerBal = 0;
                        if (isset($latestLedger) && isset($latestLedger->closing_balance)) {
                            $ledgerBal = (float)$latestLedger->closing_balance;
                        } elseif (isset($sale->customer->closing_balance)) {
                            $ledgerBal = (float)$sale->customer->closing_balance;
                        }
                    @endphp

                    <div class="summary-line">
                        <span>Customer Name:</span>
                        <span><strong>{{ $sale->customer->customer_name ?? $sale->sub_customer ?? 'Walk-in Customer' }}</strong></span>
                    </div>
                    <div class="summary-line">
                        <span>Customer Code:</span>
                        <span>{{ $sale->customer->customer_code ?? 'CUST-'.$sale->customer_id }}</span>
                    </div>
                    <div class="summary-line total" style="margin-top:14px;">
                        <span>Closing Ledger Balance:</span>
                        <span>
                            Rs. {{ number_format(abs($ledgerBal), 2) }}
                            <small>({{ $ledgerBal >= 0 ? 'Dr' : 'Cr' }})</small>
                        </span>
                    </div>
                </div>

            </div>

            <!-- TERMS & REMARKS -->
            @if(!empty($sale->remarks))
            <div class="note-box">
                <strong>Remarks / Notes:</strong> {{ $sale->remarks }}
            </div>
            @endif

            <div class="note-box">
                Goods received in good condition. Please verify product count and details at the time of delivery.
            </div>

            <!-- SIGNATURES -->
            <div class="footer-signatures">
                <div class="sig-box">
                    <div class="sig-line">Customer / Receiver Signature</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line">Sales Officer</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line">Warehouse Manager</div>
                </div>
            </div>

        </div>

        @endforeach
    </div> {{-- end #dcContent --}}

</div>

@endsection

@section('js')
<script>
    function printSingleDC(id) {
        try {
            const wrapper = document.getElementById(id);
            if (!wrapper) return alert('DC not found');
            const styles = Array.from(document.querySelectorAll('style, link[rel="stylesheet"]'))
                .map(n => n.outerHTML).join('\n');
            const html = '<!doctype html><html><head><meta charset="utf-8"><title>Print DC</title>' + styles + '</head><body>' + wrapper.outerHTML + '</body></html>';
            const win = window.open('', '_blank', 'toolbar=0,scrollbars=1,resizable=1,width=950,height=750');
            win.document.write(html);
            win.document.close();
            win.focus();
            setTimeout(() => { win.print(); }, 300);
        } catch (err) {
            console.error(err);
            alert('Print failed: ' + err.message);
        }
    }

/* ---------- WhatsApp Share ---------- */
window.shareWhatsApp = function() {
    Swal.fire({
        title: 'Preparing WhatsApp Share...',
        text: 'Generating PDF document to share.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    var element = document.getElementById('dcContent');
    var opt = {
      margin:       [0.3, 0.3, 0.3, 0.3],
      filename:     'Invoice_Delivery_Challan_{{ $sale->invoice_no }}.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
        var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
        
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({
                title: 'Invoice & Delivery Challan',
                text: 'Please find attached Invoice & Delivery Challan #{{ $sale->invoice_no }}.',
                files: [file]
            }).then(() => {
                Swal.close();
            }).catch((error) => {
                console.log('Error sharing', error);
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
        text: 'The PDF will download automatically. Please attach it to WhatsApp.',
        confirmButtonText: 'Download & Open WhatsApp'
    }).then(() => {
        var url = URL.createObjectURL(pdfBlob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        
        var msg = "*Invoice & Delivery Challan #{{ $sale->invoice_no }}*\nPlease find attached PDF.";
        var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
        window.open(waUrl, '_blank');
    });
}

/* ---------- Export Options & PDF ---------- */
window.showExportOptions = function() {
    Swal.fire({
        title: 'Export Document',
        text: 'Choose your preferred export format:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#334155',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-file-excel me-1"></i> Excel (CSV)',
        cancelButtonText: '<i class="fas fa-file-pdf me-1"></i> PDF Document',
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

    var element = document.getElementById('dcContent');
    var opt = {
      margin:       [0.3, 0.3, 0.3, 0.3],
      filename:     'Invoice_Delivery_Challan_{{ $sale->invoice_no }}.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save().then(function() {
        Swal.close();
    });
};

window.exportCSV = function () {
    var rows = [['#', 'Product', 'Price', 'Qty', 'Amount']];
    $('.dc-wrapper').each(function() {
        var dcNo = $(this).find('.dc-title-box strong').text().trim();
        rows.push(['DC: ' + dcNo]);
        $(this).find('table.custom-table tbody tr').each(function () {
            var cells = [];
            $(this).find('td').each(function () {
                var text = $(this).text().trim().replace(/"/g, '""');
                cells.push('"' + text + '"');
            });
            if (cells.length) rows.push(cells);
        });
        rows.push([]);
    });
    var csv  = rows.map(function(r){return r.join(',');}).join('\n');
    var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8;'});
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href   = url;
    a.download = 'Invoice_Delivery_Challan_{{ $sale->invoice_no }}.csv';
    a.click();
};
</script>
@endsection
@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --inv-navy: #0f1f38;
        --inv-navy-light: #1e3a5f;
        --inv-gold: #c8973a;
        --inv-emerald: #059669;
        --inv-slate: #475569;
        --inv-border: #cbd5e1;
        --inv-bg: #f8fafc;
    }

    .inv-wrapper {
        background-color: #f1f5f9;
        min-height: 100vh;
        padding: 1.5rem 0 3rem 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .inv-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(15, 31, 56, 0.12);
        overflow: hidden;
        max-width: 980px;
        margin: 0 auto;
        border: 1px solid var(--inv-border);
    }

    /* Top Action Bar */
    .inv-toolbar {
        max-width: 980px;
        margin: 0 auto 1.25rem auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Header Banner */
    .inv-header-banner {
        background: linear-gradient(135deg, var(--inv-navy) 0%, var(--inv-navy-light) 100%);
        color: #ffffff;
        padding: 2.25rem 2.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 4px solid var(--inv-gold);
    }

    .inv-brand-title {
        font-size: 1.45rem;
        font-weight: 900;
        letter-spacing: -0.5px;
        color: #ffffff !important;
        margin-bottom: 3px;
        text-transform: uppercase;
    }

    .inv-brand-sub {
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.85);
        font-weight: 500;
    }

    .inv-doc-badge {
        background: rgba(200, 151, 58, 0.2);
        border: 1px solid var(--inv-gold);
        color: #fef08a;
        padding: 4px 12px;
        border-radius: 5px;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        display: inline-block;
    }

    .inv-meta-tag {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 10px 18px;
        border-radius: 8px;
        text-align: right;
    }

    /* Information Cards Grid */
    .inv-info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
        padding: 1.75rem 2.5rem;
        background: #ffffff;
        border-bottom: 1px solid var(--inv-border);
    }

    .inv-info-box {
        padding: 1rem 1.25rem;
        border-radius: 8px;
        background: var(--inv-bg);
        border: 1px solid var(--inv-border);
        border-top: 3px solid var(--inv-navy-light);
    }

    .inv-info-box.vendor-box { border-top-color: var(--inv-emerald); }
    .inv-info-box.dest-box { border-top-color: var(--inv-gold); }

    .inv-info-label {
        font-size: 0.65rem;
        font-weight: 800;
        color: var(--inv-slate);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .inv-info-value {
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--inv-navy);
        line-height: 1.3;
    }

    .inv-info-sub {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 3px;
    }

    /* Table Styling */
    .inv-table {
        width: 100%;
        border-collapse: collapse;
    }

    .inv-table thead th {
        background: var(--inv-navy) !important;
        color: #ffffff !important;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 11px 14px;
        border: none;
    }

    .inv-table tbody td {
        padding: 11px 14px;
        border-bottom: 1px solid #cbd5e1;
        font-size: 0.85rem;
        vertical-align: middle;
        color: #1e293b;
    }

    .inv-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    .inv-table tbody tr:hover {
        background-color: #f1f5f9;
    }

    .item-title { font-weight: 700; color: var(--inv-navy); font-size: 0.9rem; }
    .item-brand { font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600; }

    /* Summary Section */
    .inv-summary-section {
        padding: 1.75rem 2.5rem;
        background: #ffffff;
        border-top: 1px solid var(--inv-border);
    }

    .inv-note-box {
        background: var(--inv-bg);
        border: 1px dashed var(--inv-slate);
        border-radius: 8px;
        padding: 1rem 1.25rem;
    }

    .inv-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        font-size: 0.875rem;
        color: #475569;
        font-weight: 600;
    }

    .inv-summary-total {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 2px solid var(--inv-navy);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1.15rem;
        font-weight: 900;
        color: var(--inv-emerald);
    }

    .inv-due-box {
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        padding: 8px 14px;
        border-radius: 6px;
        font-weight: 800;
        font-size: 0.95rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }

    .inv-paid-badge {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        padding: 8px 14px;
        border-radius: 6px;
        font-weight: 800;
        font-size: 0.85rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 8px;
    }

    /* Signature Section */
    .inv-sig-section {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        padding: 3rem 2.5rem 1.5rem;
        text-align: center;
    }

    .inv-sig-line {
        border-top: 1.5px solid #cbd5e1;
        padding-top: 6px;
        font-size: 0.725rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--inv-slate);
        letter-spacing: 0.05em;
    }

    .inv-footer-bar {
        padding: 1rem 2.5rem;
        background: #f8fafc;
        border-top: 1px solid var(--inv-border);
        text-align: center;
        font-size: 0.75rem;
        color: #94a3b8;
    }

    /* Print Styles */
    @media print {
        .no-print { display: none !important; }
        body { background: #ffffff !important; }
        .inv-wrapper { padding: 0 !important; background: #ffffff !important; }
        .inv-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; max-width: 100% !important; border-radius: 0 !important; }
        .inv-header-banner { background: #0f1f38 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .inv-table thead th { background: #0f1f38 !important; color: #ffffff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>

<div class="inv-wrapper">
    <div class="container-fluid px-3">
        
        {{-- Top Toolbar --}}
        <div class="inv-toolbar no-print">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('Purchase.home') }}" class="btn btn-sm btn-outline-dark font-weight-bold" style="border-radius: 6px;">
                    <i class="fas fa-arrow-left me-1"></i> Back to Purchases
                </a>
                <span class="text-muted">|</span>
                <span class="font-weight-bold text-dark" style="font-size: 14px;">Purchase Invoice #{{ $purchase->invoice_no }}</span>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-dark font-weight-bold shadow-sm" style="border-radius: 6px;">
                    <i class="fas fa-print me-1"></i> Print Invoice
                </button>
                <button onclick="exportPDF()" class="btn btn-sm btn-danger font-weight-bold shadow-sm" style="border-radius: 6px;">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </button>
                <button onclick="shareWhatsApp()" class="btn btn-sm text-white font-weight-bold shadow-sm" style="background: #25D366; border-radius: 6px;">
                    <i class="fab fa-whatsapp me-1"></i> Share WhatsApp
                </button>
            </div>
        </div>

        {{-- Main Invoice Document Card --}}
        <div id="pi-content" class="inv-card">
            
            {{-- Header Banner --}}
            <div class="inv-header-banner">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h1 class="inv-brand-title">PURCHASE INVOICE</h1>
                        <span class="inv-doc-badge">
                            <i class="fas fa-check-circle me-1"></i> {{ $purchase->due_amount <= 0 ? 'PAID & RECEIVED' : 'CREDIT / PARTIAL' }}
                        </span>
                    </div>
                    <div class="inv-brand-sub">
                        <span><i class="fas fa-barcode me-1" style="color: var(--inv-gold);"></i> INVOICE NO: <strong>{{ $purchase->invoice_no }}</strong></span>
                    </div>
                </div>
                <div class="inv-meta-tag">
                    <div class="font-weight-bold text-uppercase" style="font-size: 13px; color: #ffffff;">
                        <i class="fas fa-building me-1 text-warning"></i> {{ $purchase->branch->name ?? 'Ameen & Sons Main' }}
                    </div>
                    <div style="font-size: 11px; color: rgba(255,255,255,0.75);" class="mt-1">
                        <i class="fas fa-map-marker-alt me-1"></i> {{ $purchase->branch->address ?? 'Corporate Procurement HQ' }}
                    </div>
                </div>
            </div>

            {{-- 3-Box Information Grid --}}
            <div class="inv-info-grid">
                {{-- Box 1: Invoice Meta --}}
                <div class="inv-info-box">
                    <div class="inv-info-label"><i class="fas fa-calendar-alt text-primary"></i> Invoice Details</div>
                    <div class="inv-info-value">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }}</div>
                    <div class="inv-info-sub">Type: <strong>{{ ucfirst($purchase->purchase_type ?? 'local') }} Purchase</strong></div>
                    
                    @if($purchase->inwardGatepasses && $purchase->inwardGatepasses->count() > 0)
                        <div class="mt-2 pt-2 border-top">
                            <div class="inv-info-label" style="font-size: 9px; color: #2563eb;">Gatepass Ref</div>
                            <div class="small font-weight-bold text-dark">
                                {{ $purchase->inwardGatepasses->map(function($ig) { 
                                    return 'GP-' . str_pad($ig->id, 4, '0', STR_PAD_LEFT); 
                                })->implode(', ') }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Box 2: Vendor / Supplier --}}
                <div class="inv-info-box vendor-box">
                    <div class="inv-info-label"><i class="fas fa-truck text-success"></i> Supplier / Vendor</div>
                    <div class="inv-info-value text-uppercase" style="color: var(--inv-emerald);">
                        {{ $purchase->vendor->name ?? $purchase->vendor_name ?? 'Local Market Supplier' }}
                    </div>
                    <div class="inv-info-sub"><i class="fas fa-phone-alt me-1 text-muted"></i> {{ $purchase->vendor->phone ?? 'Walk-In Market' }}</div>
                </div>

                {{-- Box 3: Warehouse / Destination --}}
                <div class="inv-info-box dest-box">
                    <div class="inv-info-label"><i class="fas fa-warehouse text-warning"></i> Destination Warehouse</div>
                    <div class="inv-info-value">{{ $purchase->warehouse->warehouse_name ?? 'Branch Direct Display' }}</div>
                    <div class="inv-info-sub"><i class="fas fa-map-pin me-1 text-muted"></i> {{ $purchase->warehouse->location ?? 'Branch Inventory' }}</div>
                </div>
            </div>

            {{-- Table Section --}}
            <div class="inv-table-wrapper">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">#</th>
                            <th style="text-align: left;">Product Description</th>
                            <th style="text-align: center; width: 120px;">Packing</th>
                            <th style="text-align: center; width: 90px;">Qty</th>
                            <th style="text-align: right; width: 110px;">Unit Rate</th>
                            <th style="text-align: right; width: 90px;">Disc</th>
                            <th style="text-align: right; width: 130px;" class="pe-4">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedItems = $purchase->items->groupBy(function($item) {
                                return $item->product_id . '-' . $item->packing_type . '-' . $item->unit;
                            });
                            $srNo = 1;
                        @endphp
                        @foreach($groupedItems as $groupKey => $items)
                            @php
                                $first = $items->first();
                                $totalQty = $items->sum('qty');
                                $totalLine = $items->sum('line_total');
                                $totalDisc = $items->sum('item_discount');
                            @endphp
                            <tr>
                                <td class="text-center font-weight-bold text-muted">{{ $srNo++ }}</td>
                                <td>
                                    <div class="item-title">{{ $first->product->item_name ?? 'N/A' }}</div>
                                    <div class="item-brand">{{ $first->product->brand_name ?? $first->product->brand->name ?? '' }}</div>
                                    
                                    @if($items->count() > 1 || ($items->count() == 1 && $first->color))
                                        <div class="mt-1 d-flex flex-wrap gap-1">
                                            @foreach($items as $sub)
                                                @if($sub->color)
                                                    <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 9.5px; font-weight: 600;">
                                                        {{ strtoupper($sub->color) }}: {{ (float)$sub->qty }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 10px; font-weight: 700;">{{ strtoupper($first->packing_type ?? 'Standard') }}</span>
                                    @if($first->packing_qty > 0)
                                        <div class="text-muted mt-1" style="font-size: 10px;">{{ (float)$first->packing_qty }} x {{ (float)$first->item_per_piece }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="font-weight-bold" style="font-family: monospace; font-size: 0.95rem;">{{ (float)$totalQty }}</div>
                                    <div class="small text-muted text-uppercase" style="font-size: 9px;">{{ $first->unit }}</div>
                                </td>
                                <td class="text-end font-weight-bold" style="font-family: monospace;">Rs. {{ number_format($first->price, 2) }}</td>
                                <td class="text-end text-danger small font-weight-bold" style="font-family: monospace;">
                                    {{ $totalDisc > 0 ? '-Rs. ' . number_format($totalDisc, 2) : '0.00' }}
                                </td>
                                <td class="text-end pe-4 font-weight-bold" style="font-family: monospace; color: var(--inv-navy); font-size: 0.95rem;">
                                    Rs. {{ number_format($totalLine, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary & Notes Section --}}
            <div class="inv-summary-section">
                <div class="row g-4">
                    {{-- Left: Notes & Settlement --}}
                    <div class="col-md-6">
                        <div class="inv-note-box mb-3">
                            <h6 class="font-weight-bold text-uppercase small mb-1 text-slate-700"><i class="fas fa-sticky-note me-1 text-primary"></i> Remarks / Terms</h6>
                            <p class="small text-muted m-0" style="font-style: italic;">{{ $purchase->note ?? 'No additional remarks entered for this invoice.' }}</p>
                        </div>

                        @if($purchase->paid_amount > 0)
                            <div class="inv-paid-badge">
                                <span><i class="fas fa-check-circle me-1"></i> Paid Settlement Amount:</span>
                                <span class="font-weight-bold" style="font-family: monospace;">Rs. {{ number_format($purchase->paid_amount, 2) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Right: Financial Totals --}}
                    <div class="col-md-6">
                        <div class="inv-summary-row">
                            <span>Subtotal Amount</span>
                            <span style="font-family: monospace;">Rs. {{ number_format($purchase->subtotal, 2) }}</span>
                        </div>
                        @if($purchase->extra_cost > 0)
                            <div class="inv-summary-row">
                                <span class="text-info">Extra Charges (+)</span>
                                <span class="text-info" style="font-family: monospace;">+Rs. {{ number_format($purchase->extra_cost, 2) }}</span>
                            </div>
                        @endif
                        @if($purchase->discount > 0)
                            <div class="inv-summary-row">
                                <span class="text-danger">Invoice Discount (-)</span>
                                <span class="text-danger" style="font-family: monospace;">-Rs. {{ number_format($purchase->discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="inv-summary-total">
                            <span class="text-uppercase" style="font-size: 0.9rem;">Net Payable</span>
                            <span style="font-family: monospace;">Rs. {{ number_format($purchase->net_amount, 2) }}</span>
                        </div>

                        <div class="mt-2 pt-2 border-top">
                            <div class="d-flex justify-content-between text-success font-weight-bold small">
                                <span>Total Paid</span>
                                <span style="font-family: monospace;">Rs. {{ number_format($purchase->paid_amount, 2) }}</span>
                            </div>
                            <div class="inv-due-box">
                                <span class="text-uppercase" style="font-size: 0.8rem;">Due Balance</span>
                                <span style="font-family: monospace;">Rs. {{ number_format($purchase->due_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Signature Lines --}}
            <div class="inv-sig-section">
                <div class="inv-sig-line">Purchase Officer</div>
                <div class="inv-sig-line">Warehouse Receiver</div>
                <div class="inv-sig-line">Accounts Verified</div>
            </div>

            {{-- Footer Branding --}}
            <div class="inv-footer-bar">
                <div>"This is a system generated Purchase Invoice created via Ameen & Sons Corporate ERP."</div>
                <div class="mt-1 font-weight-bold text-uppercase" style="font-size: 9px; letter-spacing: 1px;">Generated: {{ now()->format('d M Y | h:i A') }}</div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function exportPDF() {
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Preparing purchase invoice PDF.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const element = document.getElementById('pi-content');
        const opt = {
            margin: [0.2, 0.2, 0.2, 0.2],
            filename: 'Purchase_Invoice_{{ $purchase->invoice_no }}.pdf',
            image: { type: 'jpeg', quality: 1.0 },
            html2canvas: { scale: 3, useCORS: true, letterRendering: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            Swal.close();
        });
    }

    function shareWhatsApp() {
        Swal.fire({
            title: 'Preparing Share...',
            text: 'Generating PDF for WhatsApp.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const element = document.getElementById('pi-content');
        const opt = {
            margin: [0.1, 0.1, 0.1, 0.1],
            filename: 'Purchase_Invoice_{{ $purchase->invoice_no }}.pdf',
            image: { type: 'jpeg', quality: 1.0 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then((pdfBlob) => {
            const file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Purchase Invoice',
                    text: 'Please find attached Purchase Invoice #{{ $purchase->invoice_no }}.',
                    files: [file]
                }).then(() => Swal.close())
                .catch(() => fallbackWaShare(pdfBlob, opt.filename));
            } else {
                fallbackWaShare(pdfBlob, opt.filename);
            }
        });
    }

    function fallbackWaShare(pdfBlob, filename) {
        Swal.fire({
            icon: 'info',
            title: 'Share via WhatsApp',
            text: 'PDF generated. Downloading now, then WhatsApp will open to attach.',
            confirmButtonText: 'Download & Open WA'
        }).then(() => {
            const url = URL.createObjectURL(pdfBlob);
            const a = document.createElement('a');
            a.href = url; a.download = filename;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            
            const msg = "*Purchase Invoice #{{ $purchase->invoice_no }}*\nGenerated via Ameen & Sons Corporate ERP.";
            window.open("https://wa.me/?text=" + encodeURIComponent(msg), '_blank');
        });
    }
</script>
@endsection



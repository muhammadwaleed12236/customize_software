@extends('admin_panel.layout.app')

@section('content')
<style>
    /* ==========================================================================
       EXECUTIVE ERP OUTWARD GATE PASS & DELIVERY CHALLAN SHOW UI (#1e3a5f & #c8973a)
       ========================================================================== */
    :root {
        --navy-dark: #0f2744;
        --navy-main: #1e3a5f;
        --navy-light: #2c5282;
        --navy-soft: #eef2f7;
        --gold-main: #c8973a;
        --gold-light: #fef08a;
        --slate-dark: #0f172a;
        --slate-muted: #64748b;
        --border-color: #cbd5e1;
        --card-bg: #ffffff;
    }

    .gp-view-wrapper {
        max-width: 1360px;
        margin: 0 auto;
        padding: 6px 12px 30px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Action Toolbar Top Header */
    .gp-action-bar {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 10px 18px;
        margin-bottom: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .gp-page-title {
        font-size: 1.0rem;
        font-weight: 700;
        color: var(--navy-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .gp-btn {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 6px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .gp-btn-navy {
        background: var(--navy-main);
        color: #ffffff !important;
    }
    .gp-btn-navy:hover {
        background: var(--navy-dark);
        box-shadow: 0 3px 8px rgba(30,58,95,0.2);
    }

    .gp-btn-gold {
        background: var(--gold-main);
        color: #ffffff !important;
    }
    .gp-btn-gold:hover {
        background: #b3822a;
        box-shadow: 0 3px 8px rgba(200,151,58,0.25);
    }

    .gp-btn-success {
        background: #10b981;
        color: #ffffff !important;
    }
    .gp-btn-success:hover {
        background: #059669;
    }

    .gp-btn-outline {
        background: #ffffff;
        border: 1px solid var(--border-color);
        color: #334155 !important;
    }
    .gp-btn-outline:hover {
        background: #f1f5f9;
        color: #0f172a !important;
    }

    /* Main Gate Pass Document Container */
    .gp-doc-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    /* Company & Header Banner */
    .gp-header-banner {
        background: linear-gradient(135deg, #1e3a5f 0%, #0f2744 100%);
        border-bottom: 3px solid var(--gold-main);
        padding: 16px 22px;
        color: #ffffff;
    }

    .gp-company-brand {
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: 0.01em;
        margin: 0;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .gp-company-brand span {
        color: #fbbf24;
    }

    .gp-doc-tag {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.22);
        padding: 2px 10px;
        border-radius: 20px;
        color: #ffffff;
        display: inline-block;
    }

    .gp-header-meta-box {
        text-align: right;
    }
    .gp-meta-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8;
    }
    .gp-meta-number {
        font-size: 1.25rem;
        font-weight: 800;
        color: #fbbf24;
        font-family: 'Monaco', 'Consolas', monospace;
        letter-spacing: 0.02em;
    }
    .gp-pill-meta {
        font-size: 0.72rem;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.22);
        padding: 2px 8px;
        border-radius: 4px;
        color: #f1f5f9;
    }

    /* 4-Grid Information Section (Strict Balanced 2x2 Grid) */
    .gp-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        padding: 12px 16px 10px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
    }
    @media (max-width: 768px) {
        .gp-info-grid {
            grid-template-columns: 1fr;
        }
    }

    .gp-info-box {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 10px 12px;
    }
    .gp-box-title {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--navy-main);
        border-bottom: 2px solid var(--navy-soft);
        padding-bottom: 3px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .gp-box-title i {
        color: var(--gold-main);
    }

    .gp-data-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.8rem;
        padding: 2px 0;
        border-bottom: 1px dashed #f1f5f9;
    }
    .gp-data-row:last-child {
        border-bottom: none;
    }
    .gp-data-label {
        color: #64748b;
        font-weight: 600;
    }
    .gp-data-val {
        color: #0f172a;
        font-weight: 700;
        text-align: right;
    }

    /* Product Manifest Table */
    .gp-manifest-section {
        padding: 14px 16px;
    }
    .gp-section-heading {
        font-size: 0.82rem;
        font-weight: 800;
        color: var(--navy-main);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .gp-table-custom {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        overflow: hidden;
    }
    .gp-table-custom thead th {
        background: var(--navy-main);
        color: #ffffff;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        padding: 7px 10px;
        border: none;
    }
    .gp-table-custom tbody td {
        padding: 6px 10px;
        font-size: 0.8rem;
        border-bottom: 1px solid #e2e8f0;
        color: #1e293b;
        vertical-align: middle;
    }
    .gp-table-custom tbody tr:hover {
        background-color: #f8fafc;
    }
    .gp-table-custom tfoot td {
        background: #f1f5f9;
        font-weight: 800;
        font-size: 0.82rem;
        padding: 8px 10px;
        border-top: 2px solid #cbd5e1;
    }

    /* Bottom Section & Signatures */
    .gp-bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        padding: 10px 20px 20px;
    }

    .gp-notes-card {
        background: #fffdf5;
        border: 1px solid #fde68a;
        border-radius: 8px;
        padding: 12px 14px;
    }
    .gp-notes-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #92400e;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .gp-sign-box {
        border-top: 2px solid #cbd5e1;
        margin-top: 40px;
        padding-top: 6px;
        text-align: center;
        font-size: 0.78rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
    }

    /* Precision A4 Print Formatting */
    @media print {
        @page {
            size: A4 portrait;
            margin: 6mm 8mm;
        }
        html, body {
            background: #ffffff !important;
            color: #0f172a !important;
            font-size: 11px !important;
            padding: 0 !important;
            margin: 0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .no-print {
            display: none !important;
        }
        .gp-view-wrapper {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .gp-doc-card {
            border: 1px solid #94a3b8 !important;
            box-shadow: none !important;
            border-radius: 4px !important;
            page-break-inside: avoid;
        }
        .gp-header-banner {
            background: #1e3a5f !important;
            color: #ffffff !important;
            padding: 14px 18px !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .gp-badge-card {
            border: 1px solid #cbd5e1 !important;
            padding: 8px 12px !important;
            box-shadow: none !important;
        }
        .gp-info-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 8px !important;
            padding: 10px 14px !important;
            background: #f8fafc !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .gp-info-box {
            padding: 8px 10px !important;
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .gp-box-title {
            font-size: 0.72rem !important;
            margin-bottom: 4px !important;
            padding-bottom: 2px !important;
        }
        .gp-data-row {
            font-size: 0.78rem !important;
            padding: 2px 0 !important;
        }
        .gp-manifest-section {
            padding: 10px 14px !important;
        }
        .gp-table-custom {
            border: 1px solid #94a3b8 !important;
        }
        .gp-table-custom thead th {
            background: #1e3a5f !important;
            color: #ffffff !important;
            padding: 6px 10px !important;
            font-size: 0.75rem !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .gp-table-custom tbody td {
            padding: 5px 10px !important;
            font-size: 0.8rem !important;
        }
        .gp-table-custom tfoot td {
            padding: 6px 10px !important;
            font-size: 0.82rem !important;
            background: #f1f5f9 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .gp-bottom-grid {
            padding: 8px 14px 14px !important;
            gap: 14px !important;
            page-break-inside: avoid;
        }
        .gp-sign-box {
            margin-top: 30px !important;
            font-size: 0.72rem !important;
            border-top: 1px dashed #64748b !important;
        }
        .gp-notes-card {
            padding: 8px 10px !important;
            background: #fffdf5 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<div class="gp-view-wrapper">

    <!-- Alerts -->
    @if (session('success'))
        <div class="alert alert-success py-2 px-3 mb-3 small shadow-sm border-0">
            <strong>✅ Success:</strong> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-2 px-3 mb-3 small shadow-sm border-0">
            <strong>❌ Error:</strong> {{ session('error') }}
        </div>
    @endif

    <!-- Top Action Toolbar -->
    <div class="gp-action-bar no-print">
        <div class="d-flex align-items-center gap-3">
            <h1 class="gp-page-title">
                <i class="fa fa-truck-ramp-box text-warning"></i> Outward Gate Pass
            </h1>
            <span class="badge bg-success rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                <i class="fa fa-circle-check me-1"></i> {{ strtoupper($gp->status ?? 'DELIVERED') }}
            </span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" onclick="shareWhatsApp()" class="gp-btn gp-btn-success" style="background:#25D366; color:#fff;">
                <i class="fab fa-whatsapp"></i> Share WhatsApp
            </button>
            <a href="{{ route('OutwardGatepass.pdf', $gp->id) }}" class="gp-btn gp-btn-navy">
                <i class="fa fa-file-pdf"></i> Download PDF
            </a>
            <a href="#" id="thermalBtn" class="gp-btn gp-btn-gold">
                <i class="fa fa-receipt"></i> Thermal Slip
            </a>
            <button type="button" onclick="window.print()" class="gp-btn gp-btn-outline">
                <i class="fa fa-print"></i> Print Document
            </button>
            <a href="{{ route('OutwardGatepass.list') }}" class="gp-btn gp-btn-outline">
                <i class="fa fa-list"></i> List
            </a>
            <a href="{{ route('OutwardGatepass.home') }}" class="gp-btn gp-btn-outline">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Main Outward Gate Pass Document Card -->
    <div class="gp-doc-card" id="gpContent">
        
        <!-- Integrated Header Banner -->
        <div class="gp-header-banner">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="gp-company-brand">
                        <i class="fa fa-industry"></i> AMIN <span>& SONS</span>
                    </h2>
                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                        <span class="gp-doc-tag"><i class="fa fa-file-invoice me-1"></i> Outward Gate Pass & Delivery Challan</span>
                        <span class="small text-white-50"><i class="fa fa-location-dot me-1 text-warning"></i> Location: <strong>{{ $gp->location_name ?? 'Head Office' }}</strong></span>
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                    <div class="gp-header-meta-box">
                        <div class="gp-meta-label">Gatepass Number</div>
                        <div class="gp-meta-number">{{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</div>
                        <div class="d-flex justify-content-md-end gap-2 mt-1 flex-wrap">
                            <span class="gp-pill-meta"><i class="fa fa-calendar me-1"></i> {{ optional($gp->created_at)->format('d-M-Y h:i A') ?? '-' }}</span>
                            <span class="gp-pill-meta"><i class="fa fa-file-lines me-1"></i> DC: {{ $gp->dc_no ?? ($order->dc_no ?? 'N/A') }}</span>
                            @if(!empty($gp->invoice_no))
                            <span class="gp-pill-meta"><i class="fa fa-receipt me-1"></i> Inv: {{ $gp->invoice_no }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4-Grid Information Boxes -->
        <div class="gp-info-grid">
            
            <!-- Box 1: Customer & Delivery Destination -->
            <div class="gp-info-box">
                <div class="gp-box-title"><i class="fa fa-user-tag"></i> Customer & Destination</div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Customer Name:</span>
                    <span class="gp-data-val text-primary">{{ $gp->customer_name ?? 'N/A' }}</span>
                </div>
                @if(!empty($gp->is_walking_customer))
                <div class="gp-data-row">
                    <span class="gp-data-label">Customer Type:</span>
                    <span class="badge bg-warning text-dark style-badge" style="font-size:0.68rem;">Walking Customer</span>
                </div>
                @endif
                <div class="gp-data-row">
                    <span class="gp-data-label">Delivery City:</span>
                    <span class="gp-data-val">{{ $gp->delivery_city ?? 'N/A' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Invoice No:</span>
                    <span class="gp-data-val">{{ $gp->invoice_no ?? '-' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Order Reference:</span>
                    <span class="gp-data-val">#{{ $gp->order_id }}</span>
                </div>
            </div>

            <!-- Box 2: Vehicle & Logistics -->
            <div class="gp-info-box">
                <div class="gp-box-title"><i class="fa fa-truck-moving"></i> Vehicle & Driver Details</div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Transporter:</span>
                    <span class="gp-data-val">{{ $gp->transporter ?? '-' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Vehicle Type:</span>
                    <span class="gp-data-val">{{ $gp->vehicle_type ?? '-' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Vehicle Number:</span>
                    <span class="gp-data-val text-uppercase font-monospace fw-bold">{{ $gp->vehicle_number ?? '-' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Driver Name:</span>
                    <span class="gp-data-val">{{ $gp->driver_name ?? '-' }}</span>
                </div>
            </div>

            <!-- Box 3: Bilty & Freight Details -->
            <div class="gp-info-box">
                <div class="gp-box-title"><i class="fa fa-file-invoice-dollar"></i> Bilty & Freight Costs</div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Bilty Number:</span>
                    <span class="gp-data-val font-monospace">{{ $gp->billty_no ?? '-' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Bilty Date:</span>
                    <span class="gp-data-val">{{ $gp->billty_date ?? '-' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Bilty Amount:</span>
                    <span class="gp-data-val font-monospace text-primary">Rs. {{ $gp->billty_amount ? number_format($gp->billty_amount, 2) : '0.00' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Freight / Rent:</span>
                    <span class="gp-data-val font-monospace text-danger">Rs. {{ $gp->transport_rent ? number_format($gp->transport_rent, 2) : '0.00' }}</span>
                </div>
            </div>

            <!-- Box 4: Dispatch Origin & Accounting -->
            <div class="gp-info-box">
                <div class="gp-box-title"><i class="fa fa-building-columns"></i> Dispatch & Accounting</div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Dispatch Location:</span>
                    <span class="gp-data-val">{{ $gp->location_name }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Issued By:</span>
                    <span class="gp-data-val">{{ $gp->issued_by ?? 'N/A' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Prepared By:</span>
                    <span class="gp-data-val">{{ $gp->prepared_by ?? 'N/A' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Payment Account:</span>
                    <span class="gp-data-val text-success">
                        @if(!empty($gp->expense_account_name))
                            <i class="fa fa-wallet me-1"></i> {{ $gp->expense_account_name }}
                        @else
                            <span class="text-muted fw-normal">Not Linked</span>
                        @endif
                    </span>
                </div>
            </div>

        </div>

        <!-- Product Manifest Table Section -->
        <div class="gp-manifest-section">
            <h6 class="gp-section-heading">
                <i class="fa fa-boxes-packing text-warning"></i> Itemized Goods Manifest
            </h6>

            @php
                $items = $gp->items ?? [];
                $totalQty = 0; 
                $totalAmount = 0;
            @endphp

            <div class="table-responsive">
                <table class="gp-table-custom">
                    <thead>
                        <tr>
                            <th style="width: 45px;" class="text-center">#</th>
                            <th>Product Description</th>
                            <th style="width: 140px;">Item Code</th>
                            <th style="width: 120px;">Brand</th>
                            <th style="width: 90px;" class="text-center">Unit</th>
                            <th style="width: 120px;" class="text-end">Delivered Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $k => $it)
                            @php
                                $row = is_array($it) ? $it : (is_object($it) ? (array)$it : ['text' => $it]);
                                $qty = (float)($row['qty'] ?? 0);
                                $totalQty += $qty;
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-bold">{{ $k + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $row['product_name'] ?? $row['text'] ?? '-' }}</div>
                                </td>
                                <td><code class="text-dark bg-light px-2 py-1 rounded border">{{ $row['item_code'] ?? '-' }}</code></td>
                                <td>{{ $row['brand'] ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $row['unit'] ?? '-' }}</span>
                                </td>
                                <td class="text-end font-monospace fw-bold text-primary" style="font-size: 0.82rem;">
                                    {{ $qty > 0 ? number_format($qty, 2) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fa fa-box-open fa-2x mb-2 d-block opacity-50"></i>
                                    No items recorded for this outward gate pass.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($items) > 0)
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end text-uppercase" style="letter-spacing:0.04em;">Grand Total Quantity Delivered:</td>
                                <td class="text-end font-monospace text-primary" style="font-size: 0.88rem;">
                                    {{ number_format($totalQty, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <!-- Transport Receipt Image Preview (if uploaded) -->
            @if(!empty($gp->transport_receipt_path))
            <div class="mt-3 p-3 bg-light border rounded">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-navy small"><i class="fa fa-image text-warning me-1"></i> Transport Receipt Copy</span>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0" onclick="viewTransportReceipt('{{ $gp->id }}', '{{ route('OutwardGatepass.receiptFile', $gp->id) }}')">
                        <i class="fa fa-expand me-1"></i> Zoom Image
                    </button>
                </div>
                <div class="text-center">
                    <img src="{{ route('OutwardGatepass.receiptFile', $gp->id) }}" 
                         onclick="viewTransportReceipt('{{ $gp->id }}', '{{ route('OutwardGatepass.receiptFile', $gp->id) }}')"
                         alt="Transport Receipt" 
                         style="max-height: 120px; border-radius: 6px; border: 1px solid #cbd5e1; cursor: pointer;">
                </div>
            </div>
            @endif
        </div>

        <!-- Bottom Notes & Official Signatures -->
        <div class="gp-bottom-grid">
            
            <!-- Left: Notes & Remarks -->
            <div>
                <div class="gp-notes-card mb-3">
                    <div class="gp-notes-title"><i class="fa fa-pen-to-square"></i> Packing & Handling Notes</div>
                    <textarea id="packingNotes" rows="2" class="form-control border-0 bg-transparent p-0 small" placeholder="Enter packing instructions or special handling note...">{{ old('packing_notes', $gp->packing_notes ?? '') }}</textarea>
                    <div class="mt-2 no-print d-flex align-items-center gap-2">
                        <button id="savePacking" class="gp-btn gp-btn-navy py-1 px-3" style="font-size:0.75rem;">Save Notes</button>
                        <span id="packingStatus" class="text-success small fw-bold" style="display:none">✔ Saved</span>
                    </div>
                </div>

                @if(!empty($gp->remarks))
                <div class="p-2 border rounded bg-light">
                    <div class="small fw-bold text-muted uppercase">General Remarks:</div>
                    <div class="small text-dark italic">{{ $gp->remarks }}</div>
                </div>
                @endif
            </div>

            <!-- Right: Official Authorization Signatures -->
            <div>
                <div class="row g-3 text-center align-items-end" style="margin-top: 20px;">
                    <div class="col-4">
                        <div class="gp-sign-box">
                            Driver Signature
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="gp-sign-box">
                            Receiver Signature
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="gp-sign-box">
                            Authorized Officer
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Receipt Modal -->
<div class="modal fade" id="viewReceiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 10px; border: none; overflow: hidden;">
            <div class="modal-header" style="background: var(--navy-main); color: white;">
                <h6 class="modal-title mb-0"><i class="fa fa-image me-2 text-warning"></i> Transport Receipt View</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center bg-light p-4">
                <img id="fullReceiptImage" src="" style="max-width: 100%; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
            </div>
            <div class="modal-footer">
                <a id="downloadReceiptLink" href="#" class="gp-btn gp-btn-navy" download>
                    <i class="fa fa-download"></i> Download Image
                </a>
                <button type="button" class="gp-btn gp-btn-outline" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function() {
        // Thermal Print Window
        $('#thermalBtn').on('click', function(e) {
            e.preventDefault();
            const w = window.open("{{ route('OutwardGatepass.thermal', $gp->id) }}", 'thermal', 'width=380,height=700');
            if(!w) alert('Please allow popups for thermal print.');
        });

        // AJAX Save for Packing Notes
        $('#savePacking').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const notes = $('#packingNotes').val();
            
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

            fetch("{{ route('OutwardGatepass.updatePackingNotes', $gp->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ packing_notes: notes })
            })
            .then(r => r.json())
            .then(j => {
                if(j.status === 'ok') {
                    $('#packingStatus').stop().fadeIn().delay(2000).fadeOut();
                } else {
                    alert('Error saving notes.');
                }
            })
            .catch(e => { alert('Network error: ' + e.message); })
            .finally(() => {
                $btn.prop('disabled', false).text('Save Notes');
            });
        });

        // WhatsApp Share Function
        window.shareWhatsApp = function() {
            Swal.fire({
                title: 'Generating PDF...',
                text: 'Preparing document for WhatsApp sharing',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const element = document.getElementById('gpContent');
            const opt = {
                margin: [0.2, 0.2, 0.2, 0.2],
                filename: 'Outward_Gatepass_{{ $gp->gatepass_number ?? $gp->id }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
                Swal.close();
                const file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
                
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    navigator.share({
                        title: 'Outward Gatepass #{{ $gp->gatepass_number ?? $gp->id }}',
                        text: 'Please find attached Outward Gatepass #{{ $gp->gatepass_number ?? $gp->id }}.',
                        files: [file]
                    }).catch(() => fallbackWaShare(pdfBlob, opt.filename));
                } else {
                    fallbackWaShare(pdfBlob, opt.filename);
                }
            });
        };

        function fallbackWaShare(pdfBlob, filename) {
            Swal.fire({
                icon: 'info',
                title: 'WhatsApp PDF Sharing',
                text: 'PDF will download now. Attach it in WhatsApp chat.',
                confirmButtonText: 'Download & Open WhatsApp'
            }).then(() => {
                const url = URL.createObjectURL(pdfBlob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                const msg = "*Outward Gatepass #{{ $gp->gatepass_number ?? $gp->id }}*\nPlease see attached PDF.";
                window.open("https://wa.me/?text=" + encodeURIComponent(msg), '_blank');
            });
        }

        let receiptModal = null;
        window.viewTransportReceipt = function(gpId, receiptUrl) {
            if (!receiptModal) {
                receiptModal = new bootstrap.Modal(document.getElementById('viewReceiptModal'));
            }
            $('#fullReceiptImage').attr('src', receiptUrl);
            $('#downloadReceiptLink').attr('href', receiptUrl);
            receiptModal.show();
        };
    });
</script>
@endsection
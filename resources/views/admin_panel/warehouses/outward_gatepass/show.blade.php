@extends('admin_panel.layout.app')

@section('content')
<style>
    /* ==========================================================================
       EXECUTIVE ERP OUTWARD GATE PASS & DELIVERY CHALLAN (#000000 Monochrome Standard)
       ========================================================================== */
    :root {
        --navy-dark: #0f172a;
        --navy-main: #1e293b;
        --border-color: #cbd5e1;
        --card-bg: #ffffff;
    }

    .gp-view-wrapper {
        max-width: 1100px;
        margin: 0 auto;
        padding: 10px 16px 40px;
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    }

    /* Action Toolbar Top Header */
    .gp-action-bar {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 10px 18px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .gp-page-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .gp-btn {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 6px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .gp-btn-dark {
        background: #0f172a;
        color: #ffffff !important;
    }
    .gp-btn-dark:hover {
        background: #000000;
    }

    .gp-btn-success {
        background: #16a34a;
        color: #ffffff !important;
    }
    .gp-btn-success:hover {
        background: #15803d;
    }

    .gp-btn-outline {
        background: #ffffff;
        border: 1px solid var(--border-color);
        color: #1e293b !important;
    }
    .gp-btn-outline:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    /* Main Gate Pass Document Container */
    .gp-doc-card {
        background: var(--card-bg);
        border: 2px solid #000000;
        border-radius: 4px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    /* Company & Header Banner */
    .gp-header-banner {
        background: #ffffff;
        border-bottom: 2px solid #000000;
        padding: 14px 18px;
        color: #000000;
    }

    .gp-company-brand {
        font-size: 1.35rem;
        font-weight: 900;
        letter-spacing: 0.02em;
        margin: 0;
        color: #000000;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .gp-header-address {
        font-size: 0.78rem;
        color: #334155;
        font-weight: 600;
        margin-top: 2px;
    }

    .gp-doc-title-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 5px;
    }

    .gp-doc-title-badge {
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: #000000;
        color: #ffffff;
        padding: 3px 9px;
        border-radius: 3px;
        display: inline-block;
    }

    .gp-location-chip {
        font-size: 0.74rem;
        font-weight: 600;
        border: 1px solid #000000;
        background: #f8fafc;
        color: #000000;
        padding: 2px 8px;
        border-radius: 3px;
        display: inline-block;
    }

    /* Meta Table on Header Right */
    .gp-header-meta-box {
        text-align: right;
        display: inline-block;
    }

    .gp-meta-table {
        border: 1.5px solid #000000;
        border-collapse: collapse;
        margin-left: auto;
        font-size: 0.76rem;
    }
    .gp-meta-table td {
        padding: 3px 8px;
        border: 1px solid #000000;
    }
    .gp-meta-table .meta-lbl {
        font-weight: 700;
        text-transform: uppercase;
        background: #f1f5f9;
        color: #000000;
        font-size: 0.70rem;
    }
    .gp-meta-table .meta-val {
        font-weight: 800;
        color: #000000;
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 0.78rem;
    }
    .gp-meta-table .gp-no-cell {
        font-size: 0.95rem;
        font-weight: 900;
        color: #000000;
    }

    /* Stacked Full-Width Information Section (2 Columns Grid) */
    .gp-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 10px 16px;
        background: #ffffff;
        border-bottom: 2px solid #000000;
    }

    .gp-info-box {
        background: #ffffff;
        border: 1.5px solid #000000;
        border-radius: 3px;
        padding: 6px 10px 8px;
    }

    .gp-box-title {
        font-size: 0.75rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #000000;
        background: #f1f5f9;
        border-bottom: 1.5px solid #000000;
        padding: 3px 8px;
        margin: -6px -10px 6px -10px;
    }

    .gp-data-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.78rem;
        padding: 3px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .gp-data-row:last-child {
        border-bottom: none;
    }

    .gp-data-label {
        color: #000000;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.72rem;
    }
    .gp-data-val {
        color: #000000;
        font-weight: 800;
        text-align: right;
    }

    /* Product Manifest Table Section */
    .gp-manifest-section {
        padding: 10px 16px;
    }
    .gp-section-heading {
        font-size: 0.8rem;
        font-weight: 900;
        color: #000000;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
    }

    .gp-table-custom {
        width: 100%;
        border-collapse: collapse;
        border: 1.5px solid #000000;
    }
    .gp-table-custom thead th {
        background: #f1f5f9;
        color: #000000;
        font-size: 0.74rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 5px 8px;
        border: 1px solid #000000;
        border-bottom: 2px solid #000000;
    }
    .gp-table-custom tbody td {
        padding: 5px 8px;
        font-size: 0.78rem;
        border: 1px solid #000000;
        color: #000000;
        vertical-align: middle;
        font-weight: 700;
    }
    .gp-table-custom tbody td .fw-bold {
        color: #000000 !important;
        font-weight: 800;
    }
    .gp-table-custom tbody td code {
        font-family: 'Consolas', monospace;
        font-size: 0.76rem;
        font-weight: 800;
        color: #000000;
        background: transparent;
        padding: 0;
    }
    .gp-table-custom tbody tr:hover {
        background-color: #f8fafc;
    }
    .gp-table-custom tfoot td {
        background: #f1f5f9;
        font-weight: 900;
        font-size: 0.82rem;
        padding: 6px 8px;
        border: 1px solid #000000;
        border-top: 2px solid #000000;
        color: #000000;
    }

    /* Notes & Signatures Section */
    .gp-bottom-section {
        padding: 10px 16px 16px;
        border-top: 2px solid #000000;
    }

    .gp-notes-card {
        border: 1.5px solid #000000;
        border-radius: 3px;
        padding: 6px 10px;
        background: #ffffff;
        margin-bottom: 14px;
    }
    .gp-notes-title {
        font-size: 0.72rem;
        font-weight: 900;
        color: #000000;
        text-transform: uppercase;
        margin-bottom: 4px;
        border-bottom: 1px solid #cbd5e1;
        padding-bottom: 2px;
    }

    .gp-print-notes {
        font-size: 0.76rem;
        font-weight: 700;
        color: #000000;
        white-space: pre-wrap;
        min-height: 22px;
    }

    .gp-general-remarks {
        border: 1.5px solid #000000;
        border-radius: 3px;
        padding: 5px 10px;
        background: #ffffff;
        margin-top: 6px;
    }
    .gp-general-remarks-title {
        font-size: 0.70rem;
        font-weight: 900;
        color: #000000;
        text-transform: uppercase;
        border-bottom: 1px solid #cbd5e1;
        padding-bottom: 2px;
        margin-bottom: 2px;
    }
    .gp-general-remarks-text {
        font-size: 0.76rem;
        font-weight: 700;
        color: #000000;
    }

    /* Dedicated 3-Column Horizontal Signatures Grid */
    .gp-signatures-wrap {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 30px;
        margin-top: 25px;
        text-align: center;
    }

    .gp-sign-col {
        padding-top: 25px;
        position: relative;
    }

    .gp-sign-line {
        border-top: 1.5px solid #000000;
        margin-bottom: 4px;
        width: 100%;
    }

    .gp-sign-title {
        font-size: 0.74rem;
        font-weight: 900;
        color: #000000;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .gp-sign-sub {
        font-size: 0.65rem;
        font-weight: 600;
        color: #475569;
    }

    /* ==========================================================================
       PRINT STYLES (@media print) - Engineered strictly for Single Page A4
       ========================================================================== */
    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }

        /* 1. Hide all website layout elements */
        header, footer, nav, aside,
        .navbar, .main-header, .main-sidebar, .sidebar, .app-header, .app-sidebar,
        .top_nav, .nav-bottom, .footer_area, .footer,
        .gp-action-bar, .no-print, .alert, .modal, .modal-backdrop {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        html, body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 11px !important;
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .main-content, .content-wrapper, .container-fluid, .gp-view-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
            background: #ffffff !important;
        }

        /* Enforce pure black on every text, border and element */
        * {
            color: #000000 !important;
            text-shadow: none !important;
            box-shadow: none !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Main Outer Container */
        .gp-doc-card {
            border: 2px solid #000000 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            margin: 0 auto !important;
            width: 100% !important;
            background: #ffffff !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* Header Banner */
        .gp-header-banner {
            background: #ffffff !important;
            color: #000000 !important;
            border-bottom: 2px solid #000000 !important;
            padding: 8px 12px !important;
        }

        .gp-company-brand {
            font-size: 20px !important;
            font-weight: 900 !important;
            color: #000000 !important;
            letter-spacing: 0.5px !important;
            text-transform: uppercase !important;
            line-height: 1.1 !important;
        }

        .gp-header-address {
            font-size: 10.5px !important;
            color: #000000 !important;
            font-weight: 700 !important;
            margin-top: 1px !important;
        }

        .gp-doc-title-bar {
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin-top: 4px !important;
        }

        .gp-doc-title-badge {
            background: #000000 !important;
            color: #ffffff !important;
            font-size: 9.5px !important;
            font-weight: 900 !important;
            padding: 2px 7px !important;
            border: 1px solid #000000 !important;
            display: inline-block !important;
            letter-spacing: 0.3px !important;
        }

        .gp-location-chip {
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid #000000 !important;
            font-size: 9px !important;
            font-weight: 700 !important;
            padding: 2px 6px !important;
            display: inline-block !important;
        }

        /* Meta Table in Print */
        .gp-meta-table {
            border: 1.5px solid #000000 !important;
            font-size: 10.5px !important;
        }
        .gp-meta-table td {
            padding: 2px 6px !important;
            border: 1px solid #000000 !important;
        }
        .gp-meta-table .meta-lbl {
            background: #f0f0f0 !important;
            color: #000000 !important;
            font-weight: 800 !important;
            font-size: 9.5px !important;
        }
        .gp-meta-table .meta-val {
            color: #000000 !important;
            font-weight: 800 !important;
            font-size: 10.5px !important;
        }
        .gp-meta-table .gp-no-cell {
            font-size: 15px !important;
            font-weight: 900 !important;
        }

        /* Two Column Stacked Details Grid */
        .gp-info-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 8px !important;
            padding: 6px 12px !important;
            border-bottom: 2px solid #000000 !important;
            background: #ffffff !important;
        }

        .gp-info-box {
            border: 1.5px solid #000000 !important;
            border-radius: 0 !important;
            padding: 5px 8px 6px !important;
            background: #ffffff !important;
        }

        .gp-box-title {
            font-size: 10.5px !important;
            font-weight: 900 !important;
            text-transform: uppercase !important;
            color: #000000 !important;
            background: #f0f0f0 !important;
            border-bottom: 1.5px solid #000000 !important;
            padding: 2px 6px !important;
            margin: -5px -8px 5px -8px !important;
            letter-spacing: 0.5px !important;
        }

        .gp-data-row {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 2px 0 !important;
            border-bottom: 1px solid #000000 !important;
            font-size: 10.5px !important;
            line-height: 1.3 !important;
        }
        .gp-data-row:last-child {
            border-bottom: none !important;
        }

        .gp-data-label {
            font-weight: 800 !important;
            color: #000000 !important;
            font-size: 9.5px !important;
            text-transform: uppercase !important;
        }

        .gp-data-val {
            font-weight: 800 !important;
            color: #000000 !important;
            font-size: 10.5px !important;
            text-align: right !important;
        }

        /* Table Manifest */
        .gp-manifest-section {
            padding: 6px 12px !important;
        }

        .gp-section-heading {
            font-size: 11px !important;
            font-weight: 900 !important;
            text-transform: uppercase !important;
            color: #000000 !important;
            margin-bottom: 4px !important;
            letter-spacing: 0.5px !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .gp-table-custom {
            width: 100% !important;
            border-collapse: collapse !important;
            border: 1.5px solid #000000 !important;
            margin-bottom: 0 !important;
        }

        .gp-table-custom thead th {
            background-color: #f0f0f0 !important;
            color: #000000 !important;
            border: 1px solid #000000 !important;
            border-bottom: 1.5px solid #000000 !important;
            padding: 4px 6px !important;
            font-size: 10px !important;
            font-weight: 900 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
        }

        .gp-table-custom tbody td {
            border: 1px solid #000000 !important;
            padding: 4px 6px !important;
            font-size: 10.5px !important;
            font-weight: 700 !important;
            color: #000000 !important;
            vertical-align: middle !important;
        }

        .gp-table-custom tbody td .fw-bold {
            font-size: 11px !important;
            font-weight: 900 !important;
            color: #000000 !important;
        }

        .gp-table-custom tbody td code {
            font-family: 'Consolas', monospace !important;
            font-size: 10.5px !important;
            font-weight: 800 !important;
            background: transparent !important;
            border: none !important;
            color: #000000 !important;
            padding: 0 !important;
        }

        .gp-table-custom tfoot td {
            background-color: #f0f0f0 !important;
            color: #000000 !important;
            border: 1px solid #000000 !important;
            border-top: 1.5px solid #000000 !important;
            padding: 5px 6px !important;
            font-size: 11px !important;
            font-weight: 900 !important;
            text-transform: uppercase !important;
        }

        /* Bottom Section & Signatures */
        .gp-bottom-section {
            padding: 6px 12px 10px !important;
            border-top: 2px solid #000000 !important;
            background: #ffffff !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .gp-notes-card {
            border: 1px solid #000000 !important;
            border-radius: 0 !important;
            padding: 4px 8px !important;
            background: #ffffff !important;
            margin-bottom: 8px !important;
            min-height: 30px !important;
        }

        .gp-notes-title {
            font-size: 9.5px !important;
            font-weight: 900 !important;
            text-transform: uppercase !important;
            color: #000000 !important;
            margin-bottom: 2px !important;
            border-bottom: 1px solid #000000 !important;
            padding-bottom: 2px !important;
        }

        textarea#packingNotes {
            display: none !important;
        }

        .gp-print-notes {
            display: block !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            color: #000000 !important;
            min-height: 18px !important;
            white-space: pre-wrap !important;
        }

        .gp-general-remarks {
            border: 1px solid #000000 !important;
            border-radius: 0 !important;
            padding: 4px 8px !important;
            background: #ffffff !important;
            margin-top: 4px !important;
        }

        .gp-general-remarks-title {
            font-size: 9.5px !important;
            font-weight: 900 !important;
            text-transform: uppercase !important;
            color: #000000 !important;
            margin-bottom: 2px !important;
            border-bottom: 1px solid #000000 !important;
            padding-bottom: 1px !important;
        }

        .gp-general-remarks-text {
            font-size: 10px !important;
            font-weight: 700 !important;
            color: #000000 !important;
        }

        /* Guaranteed 3-Column Horizontal Signatures */
        .gp-signatures-wrap {
            display: grid !important;
            grid-template-columns: 1fr 1fr 1fr !important;
            gap: 25px !important;
            margin-top: 22px !important;
            text-align: center !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .gp-sign-col {
            padding-top: 15px !important;
        }

        .gp-sign-line {
            border-top: 1.5px solid #000000 !important;
            margin-bottom: 3px !important;
            width: 100% !important;
        }

        .gp-sign-title {
            font-size: 10px !important;
            font-weight: 900 !important;
            color: #000000 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }

        .gp-sign-sub {
            font-size: 8.5px !important;
            color: #000000 !important;
            font-weight: 600 !important;
        }
    }
</style>

<div class="gp-view-wrapper">

    <!-- Alerts -->
    @if (session('success'))
        <div class="alert alert-success py-2 px-3 mb-3 small shadow-sm border-0 no-print">
            <strong>✅ Success:</strong> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-2 px-3 mb-3 small shadow-sm border-0 no-print">
            <strong>❌ Error:</strong> {{ session('error') }}
        </div>
    @endif

    <!-- Top Action Toolbar (No Print) -->
    <div class="gp-action-bar no-print">
        <div class="d-flex align-items-center gap-2">
            <h1 class="gp-page-title">
                <i class="fa fa-truck-ramp-box text-dark"></i> Outward Gate Pass
            </h1>
            <span class="badge bg-dark rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.76rem;">
                {{ strtoupper($gp->status ?? 'DELIVERED') }}
            </span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" onclick="shareWhatsApp()" class="gp-btn gp-btn-success">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </button>
            <a href="{{ route('OutwardGatepass.pdf', $gp->id) }}" class="gp-btn gp-btn-dark">
                <i class="fa fa-file-pdf"></i> Download PDF
            </a>
            <a href="#" id="thermalBtn" class="gp-btn gp-btn-outline">
                <i class="fa fa-receipt"></i> Thermal Slip
            </a>
            <button type="button" onclick="window.print()" class="gp-btn gp-btn-dark">
                <i class="fa fa-print"></i> Print Document (A4)
            </button>
            <a href="{{ route('OutwardGatepass.list') }}" class="gp-btn gp-btn-outline">
                <i class="fa fa-list"></i> List
            </a>
            <a href="{{ route('OutwardGatepass.home') }}" class="gp-btn gp-btn-outline">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Main Outward Gate Pass / Delivery Challan Card -->
    <div class="gp-doc-card" id="gpContent">
        
        <!-- Integrated Header Banner -->
        <div class="gp-header-banner">
            <div class="row align-items-center">
                <div class="col-7">
                    <h2 class="gp-company-brand">
                        {{ !empty($gp->company_name) ? $gp->company_name : 'AMIN & SONS' }}
                    </h2>
                    <div class="gp-header-address">
                        <strong>{{ strtoupper($gp->branch_name ?? 'Branch') }} BRANCH</strong>
                        @if(!empty($gp->branch_address))
                            &bull; {{ $gp->branch_address }}
                        @endif
                        @if(!empty($gp->branch_phone))
                            &bull; Tel: {{ $gp->branch_phone }}
                        @endif
                    </div>
                    <div class="gp-doc-title-bar">
                        <span class="gp-doc-title-badge">DELIVERY CHALLAN &amp; OUTWARD GATE PASS</span>
                        <span class="gp-location-chip"><strong>Dispatch:</strong> {{ $gp->location_name ?? 'Main Store' }}</span>
                    </div>
                </div>
                <div class="col-5 text-end">
                    <div class="gp-header-meta-box">
                        <table class="gp-meta-table">
                            <tr>
                                <td class="meta-lbl">Gatepass No:</td>
                                <td class="meta-val gp-no-cell">{{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</td>
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
                    </div>
                </div>
            </div>
        </div>

        <!-- 2-Column Side-by-Side Information Cards -->
        @php
            $orderCustomer = !empty($order->customer_id) ? \App\Models\Customer::find($order->customer_id) : null;
            $destAddress = !empty($gp->delivery_city) ? $gp->delivery_city : ($orderCustomer->address ?? ($orderCustomer->city ?? 'Hyderabad Latifabad'));
            $custMobile = !empty($gp->customer_mobile) ? $gp->customer_mobile : ($orderCustomer->mobile ?? '-');
            $custCode = !empty($gp->customer_code) ? $gp->customer_code : ($orderCustomer->customer_id ?? ('CUST-' . ($order->customer_id ?? 'N/A')));
        @endphp
        <div class="gp-info-grid">
            
            <!-- Box 1: Customer & Destination Details -->
            <div class="gp-info-box">
                <div class="gp-box-title">Customer & Destination Details</div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Customer Name:</span>
                    <span class="gp-data-val">{{ $gp->customer_name ?? ($orderCustomer->customer_name ?? 'N/A') }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Customer Code:</span>
                    <span class="gp-data-val font-monospace">{{ $custCode }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Contact / Phone:</span>
                    <span class="gp-data-val">{{ $custMobile }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Delivery Destination:</span>
                    <span class="gp-data-val">{{ $destAddress }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Customer Type:</span>
                    <span class="gp-data-val">
                        {{ !empty($gp->is_walking_customer) ? 'Walking Customer' : 'Regular Customer' }}
                    </span>
                </div>
            </div>

            <!-- Box 2: Vehicle & Logistics Details -->
            <div class="gp-info-box">
                <div class="gp-box-title">Vehicle & Logistics Details</div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Vehicle & Driver:</span>
                    <span class="gp-data-val text-uppercase font-monospace">
                        {{ $gp->vehicle_number ?? '-' }}
                        @if(!empty($gp->driver_name))
                            ({{ $gp->driver_name }})
                        @endif
                    </span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Transporter:</span>
                    <span class="gp-data-val">{{ $gp->transporter ?? '-' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Bilty No & Date:</span>
                    <span class="gp-data-val font-monospace">{{ $gp->billty_no ?? '-' }} {{ $gp->billty_date ? '('.$gp->billty_date.')' : '' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Freight / Rent:</span>
                    <span class="gp-data-val font-monospace">Rs. {{ $gp->transport_rent ? number_format($gp->transport_rent, 2) : '0.00' }}</span>
                </div>
                <div class="gp-data-row">
                    <span class="gp-data-label">Issued / Prepared By:</span>
                    <span class="gp-data-val">{{ $gp->issued_by ?? 'admin' }}{{ !empty($gp->prepared_by) ? ' / ' . $gp->prepared_by : '' }}</span>
                </div>
            </div>

        </div>

        <!-- Product Manifest Table Section -->
        <div class="gp-manifest-section">
            <h6 class="gp-section-heading">Itemized Goods Manifest</h6>

            @php
                $items = $gp->items ?? [];
                $totalQty = 0; 
            @endphp

            <div class="table-responsive">
                <table class="gp-table-custom">
                    <thead>
                        <tr>
                            <th style="width: 5%;" class="text-center">#</th>
                            <th style="width: 45%;">Product Description</th>
                            <th style="width: 15%;" class="text-center">Item Code</th>
                            <th style="width: 15%;" class="text-center">Brand</th>
                            <th style="width: 8%;" class="text-center">Unit</th>
                            <th style="width: 12%;" class="text-end">Delivered Qty</th>
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
                                <td class="text-center">{{ $k + 1 }}</td>
                                <td>
                                    <div class="fw-bold">{{ $row['product_name'] ?? $row['text'] ?? '-' }}</div>
                                </td>
                                <td class="text-center font-monospace">{{ $row['item_code'] ?? '-' }}</td>
                                <td class="text-center">{{ $row['brand'] ?? '-' }}</td>
                                <td class="text-center">{{ $row['unit'] ?? '-' }}</td>
                                <td class="text-end font-monospace fw-bold">
                                    {{ $qty > 0 ? number_format($qty, 2) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">
                                    No items recorded for this outward gate pass.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($items) > 0)
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end text-uppercase">Grand Total Quantity Delivered:</td>
                                <td class="text-end font-monospace">
                                    {{ number_format($totalQty, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <!-- Transport Receipt Image Preview (if uploaded, no print) -->
            @if(!empty($gp->transport_receipt_path))
            <div class="mt-2 p-2 bg-light border rounded no-print">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="fw-bold text-dark small"><i class="fa fa-image me-1"></i> Transport Receipt Copy</span>
                    <button type="button" class="btn btn-sm btn-outline-dark py-0" onclick="viewTransportReceipt('{{ $gp->id }}', '{{ route('OutwardGatepass.receiptFile', $gp->id) }}')">
                        <i class="fa fa-expand me-1"></i> Zoom Image
                    </button>
                </div>
                <div class="text-center">
                    <img src="{{ route('OutwardGatepass.receiptFile', $gp->id) }}" 
                         onclick="viewTransportReceipt('{{ $gp->id }}', '{{ route('OutwardGatepass.receiptFile', $gp->id) }}')"
                         alt="Transport Receipt" 
                         style="max-height: 100px; border-radius: 4px; border: 1px solid #cbd5e1; cursor: pointer;">
                </div>
            </div>
            @endif
        </div>

        <!-- Notes & Signatures Section -->
        <div class="gp-bottom-section">
            
            <!-- Notes Card -->
            <div class="gp-notes-card">
                <div class="gp-notes-title">Packing & Handling Notes / Instructions</div>
                <textarea id="packingNotes" rows="2" class="form-control border-0 bg-transparent p-0 small no-print" placeholder="Enter packing instructions or special handling note...">{{ old('packing_notes', $gp->packing_notes ?? '') }}</textarea>
                <div class="gp-print-notes">{{ !empty($gp->packing_notes) ? $gp->packing_notes : 'Goods received in satisfactory condition without any damages.' }}</div>
                <div class="mt-2 no-print d-flex align-items-center gap-2">
                    <button id="savePacking" class="gp-btn gp-btn-dark py-1 px-3" style="font-size:0.75rem;">Save Notes</button>
                    <span id="packingStatus" class="text-success small fw-bold" style="display:none">✔ Saved</span>
                </div>
            </div>

            @if(!empty($gp->remarks))
            <div class="gp-general-remarks">
                <div class="gp-general-remarks-title">General Remarks</div>
                <div class="gp-general-remarks-text">{{ $gp->remarks }}</div>
            </div>
            @endif

            <!-- Dedicated 3-Column Horizontal Signatures Grid -->
            <div class="gp-signatures-wrap">
                <div class="gp-sign-col">
                    <div class="gp-sign-line"></div>
                    <div class="gp-sign-title">Driver Signature</div>
                    <div class="gp-sign-sub">Name / Contact</div>
                </div>
                <div class="gp-sign-col">
                    <div class="gp-sign-line"></div>
                    <div class="gp-sign-title">Receiver Signature</div>
                    <div class="gp-sign-sub">Stamp & Signature</div>
                </div>
                <div class="gp-sign-col">
                    <div class="gp-sign-line"></div>
                    <div class="gp-sign-title">Authorized Officer</div>
                    <div class="gp-sign-sub">Warehouse / Incharge</div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Receipt Modal -->
<div class="modal fade" id="viewReceiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 8px; border: none; overflow: hidden;">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title mb-0"><i class="fa fa-image me-2"></i> Transport Receipt View</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center bg-light p-4">
                <img id="fullReceiptImage" src="" style="max-width: 100%; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
            </div>
            <div class="modal-footer">
                <a id="downloadReceiptLink" href="#" class="gp-btn gp-btn-dark" download>
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

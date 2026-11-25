@extends('admin_panel.layout.app')

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')
@can('product.edit')

<style>
    :root {
        --primary-blue: #0284c7;
        --primary-blue-dark: #0369a1;
        --primary-blue-light: #e0f2fe;
        --border-color: #e2e8f0;
        --bg-main: #f8fafc;
        --text-dark: #0f172a;
        --text-muted: #64748b;
        --card-radius: 12px;
    }

    body {
        background-color: var(--bg-main);
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .ose-wrapper {
        padding: 1.25rem;
        max-width: 1440px;
        margin: 0 auto;
    }

    /* â”€â”€ 1. Header Bar â”€â”€ */
    .ose-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .ose-header-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.02em;
    }
    .badge-erp {
        background: #e0f2fe;
        color: #0284c7;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        border: 1px solid #bae6fd;
    }
    .ose-header-sub {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 3px;
    }
    .ose-header-sub strong {
        color: var(--text-dark);
    }
    .ose-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-ose-outline {
        background: #ffffff;
        border: 1.5px solid var(--border-color);
        color: var(--text-muted);
        font-size: 12.5px;
        font-weight: 700;
        padding: 7px 14px;
        border-radius: 8px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-ose-outline:hover {
        background: #f1f5f9;
        color: var(--text-dark);
        border-color: #cbd5e1;
    }
    .btn-ose-blue-outline {
        background: #ffffff;
        border: 1.5px solid #bae6fd;
        color: #0284c7;
        font-size: 12.5px;
        font-weight: 700;
        padding: 7px 14px;
        border-radius: 8px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-ose-blue-outline:hover {
        background: #f0f9ff;
        color: #0369a1;
        border-color: #7dd3fc;
    }

    /* â”€â”€ 2. Top Info Grid (3 Cards) â”€â”€ */
    .ose-top-grid {
        display: grid;
        grid-template-columns: 1.25fr 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    @media (max-width: 992px) {
        .ose-top-grid {
            grid-template-columns: 1fr;
        }
    }
    .ose-card {
        background: #ffffff;
        border: 1.5px solid var(--border-color);
        border-radius: var(--card-radius);
        padding: 1rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    /* Product Card Details */
    .ose-prod-card {
        display: flex;
        gap: 14px;
        align-items: center;
    }
    .ose-prod-thumb {
        width: 64px;
        height: 64px;
        border-radius: 10px;
        background: #f1f5f9;
        border: 1.5px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    .ose-prod-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .ose-prod-thumb i {
        font-size: 26px;
        color: #94a3b8;
    }
    .ose-prod-details {
        flex: 1;
        min-width: 0;
    }
    .ose-prod-title-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }
    .ose-prod-name {
        font-size: 15px;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .badge-sku-pill {
        background: #e0f2fe;
        color: #0284c7;
        font-size: 10.5px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 6px;
        letter-spacing: 0.3px;
    }
    .ose-prod-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px 12px;
        font-size: 11.5px;
        color: var(--text-muted);
    }
    .ose-prod-meta-grid strong {
        color: var(--text-dark);
        font-weight: 700;
    }

    /* Branch Target Card */
    .ose-branch-card-inner {
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: 100%;
    }
    .ose-card-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #0284c7;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .ose-branch-select-wrap {
        position: relative;
    }
    .ose-branch-select {
        width: 100%;
        border: 1.5px solid var(--border-color);
        border-radius: 8px;
        padding: 8px 12px 8px 36px;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--text-dark);
        background: #ffffff;
        appearance: none;
        -webkit-appearance: none;
        outline: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .ose-branch-select:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
    }
    .ose-branch-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 13px;
        pointer-events: none;
    }
    .ose-branch-arrow {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 11px;
        pointer-events: none;
    }
    .ose-branch-info-text {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* System-Wide Stock Card */
    .ose-stock-card-inner {
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: 100%;
    }
    .ose-stock-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #059669;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .ose-stock-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 6px 12px;
    }
    .ose-stock-branch-name {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--text-dark);
    }
    .ose-stock-qty-val {
        font-size: 15px;
        font-weight: 900;
        color: var(--text-dark);
        font-family: monospace;
    }
    .badge-stock-active {
        background: #dcfce7;
        color: #15803d;
        font-size: 10px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 12px;
        margin-left: 6px;
    }

    /* â”€â”€ 3. Table Card â”€â”€ */
    .ose-main-card {
        background: #ffffff;
        border: 1.5px solid var(--border-color);
        border-radius: var(--card-radius);
        overflow: hidden;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .ose-card-header-bar {
        padding: 12px 18px;
        background: #ffffff;
        border-bottom: 1.5px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .ose-card-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .ose-header-icon-square {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #e0f2fe;
        color: #0284c7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .ose-table-title {
        font-size: 14px;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0;
    }
    .ose-table-sub {
        font-size: 11.5px;
        color: var(--text-muted);
        margin: 0;
    }
    .ose-card-header-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .btn-add-location {
        background: #0284c7;
        color: #ffffff !important;
        border: none;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 700;
        padding: 7px 16px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
    }
    .btn-add-location:hover {
        background: #0369a1;
        transform: translateY(-1px);
    }
    .total-allocated-pill {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-muted);
    }
    .total-allocated-pill strong {
        color: #0284c7;
        font-size: 15px;
        font-weight: 800;
        font-family: monospace;
    }

    /* Clean Styled Table */
    .ose-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    .ose-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        text-transform: capitalize;
        letter-spacing: 0.02em;
        padding: 11px 14px;
        border-bottom: 1.5px solid var(--border-color);
        white-space: nowrap;
    }
    .ose-table tbody td {
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 13px;
    }
    .ose-table tbody tr:hover td {
        background: #fafcff;
    }

    /* Clean Form Elements inside Table */
    .tbl-input-group {
        display: flex;
        align-items: center;
        border: 1.5px solid var(--border-color);
        border-radius: 7px;
        background: #ffffff;
        overflow: hidden;
        transition: all 0.2s;
    }
    .tbl-input-group:focus-within {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    .tbl-input-group input {
        border: none;
        outline: none;
        padding: 6px 10px;
        font-size: 13px;
        font-weight: 700;
        color: var(--text-dark);
        width: 100%;
        background: transparent;
    }
    .tbl-input-group .tbl-addon {
        padding: 6px 10px;
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        border-left: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .tbl-select-wrap {
        position: relative;
        width: 100%;
    }
    .tbl-select-wrap select {
        width: 100%;
        border: 1.5px solid var(--border-color);
        border-radius: 7px;
        padding: 6px 26px 6px 30px;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--text-dark);
        background: #ffffff;
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tbl-select-wrap select:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    .tbl-select-wrap i.prefix-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #0284c7;
        font-size: 12px;
        pointer-events: none;
    }
    .tbl-select-wrap i.arrow-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 10px;
        pointer-events: none;
    }

    .tbl-input-clean {
        border: 1.5px solid var(--border-color);
        border-radius: 7px;
        padding: 6px 10px;
        font-size: 13px;
        font-weight: 700;
        color: var(--text-dark);
        width: 100%;
        outline: none;
        transition: all 0.2s;
    }
    .tbl-input-clean:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    .tbl-input-readonly {
        background: #f8fafc;
        border: 1.5px solid var(--border-color);
        border-radius: 7px;
        padding: 6px 10px;
        font-size: 13px;
        font-weight: 800;
        color: var(--text-dark);
        width: 100%;
        outline: none;
        font-family: monospace;
    }

    .btn-open-serials {
        display: none;
        align-items: center;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        border: 1px solid #f59e0b;
        background: #fef3c7;
        color: #b45309;
        white-space: nowrap;
        height: 26px;
        outline: none;
    }
    .btn-open-serials:hover {
        background: #fde68a;
        color: #92400e;
    }
    .btn-open-serials.serials-completed {
        border-color: #86efac;
        background: #dcfce7;
        color: #15803d;
    }
    .btn-open-serials.serials-completed:hover {
        background: #bbf7d0;
        color: #166534;
    }

    .btn-trash-action {
        width: 32px;
        height: 32px;
        border-radius: 7px;
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 13px;
    }
    .btn-trash-action:hover {
        background: #dc2626;
        color: #ffffff;
    }

    /* Warning strip below table row */
    .ose-warning-strip {
        background: #fffbeb;
        border-top: 1px solid #fef3c7;
        padding: 8px 18px;
        font-size: 11.5px;
        color: #b45309;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* â”€â”€ 4. Bottom Allocation Summary Cards (4 Cards) â”€â”€ */
    .ose-summary-section {
        margin-bottom: 1.5rem;
    }
    .ose-summary-title {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .ose-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }
    @media (max-width: 992px) {
        .ose-summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 576px) {
        .ose-summary-grid {
            grid-template-columns: 1fr;
        }
    }
    .ose-stat-card {
        background: #ffffff;
        border: 1.5px solid var(--border-color);
        border-radius: var(--card-radius);
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .ose-stat-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .icon-blue-bg   { background: #eff6ff; color: #2563eb; }
    .icon-green-bg  { background: #ecfdf5; color: #059669; }
    .icon-amber-bg  { background: #fffbeb; color: #d97706; }
    .icon-purple-bg { background: #faf5ff; color: #9333ea; }

    .ose-stat-info {
        flex: 1;
        min-width: 0;
    }
    .ose-stat-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 2px;
    }
    .ose-stat-value {
        font-size: 20px;
        font-weight: 900;
        color: var(--text-dark);
        line-height: 1.2;
        font-family: monospace;
    }
    .ose-stat-unit {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        margin-left: 4px;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* â”€â”€ 5. Sticky/Clean Footer â”€â”€ */
    .ose-footer-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 10px;
        gap: 1rem;
    }
    .btn-ose-cancel {
        background: #ffffff;
        border: 1.5px solid var(--border-color);
        color: var(--text-dark);
        font-size: 13px;
        font-weight: 700;
        padding: 9px 20px;
        border-radius: 8px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-ose-cancel:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .btn-ose-submit {
        background: #0284c7;
        border: none;
        color: #ffffff;
        font-size: 13.5px;
        font-weight: 800;
        padding: 10px 26px;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
    }
    .btn-ose-submit:hover {
        background: #0369a1;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(2, 132, 199, 0.4);
    }
</style>

<div class="ose-wrapper">

    {{-- Top Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm py-2 px-3 mb-3 small font-weight-bold" style="border-left: 4px solid #10b981 !important; border-radius: 8px;">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm py-2 px-3 mb-3 small font-weight-bold" style="border-left: 4px solid #ef4444 !important; border-radius: 8px;">
            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- â”€â”€ 1. Page Header Bar â”€â”€ --}}
    <div class="ose-header">
        <div>
            <div class="ose-header-title">
                Edit Opening Stock
                <span class="badge-erp">ERP Standard</span>
            </div>
            <div class="ose-header-sub">
                Editing stock &amp; allocations for: <strong>{{ $product->item_name }}</strong> (SKU: {{ $product->item_code }})
            </div>
        </div>
        <div class="ose-actions">
            <a href="{{ route('opening.stocks.index') }}" class="btn-ose-outline">
                <i class="fas fa-arrow-left"></i> Back to Opening Stocks List
            </a>
            <a href="{{ route('opening.stocks.create') }}" class="btn-ose-blue-outline">
                <i class="fas fa-plus"></i> Add New Stock
            </a>
            <a href="{{ route('product') }}" class="btn-ose-outline">
                <i class="fas fa-cube"></i> Products Catalog
            </a>
        </div>
    </div>

    {{-- â”€â”€ 2. Top Info Row (Product + Branch + Live Stock) â”€â”€ --}}
    @php
    $isCarton = $product->isCartonProduct();
    $ppp = $isCarton ? (float)($product->piece_per_pack ?? 1) : 1;
    $displayCurrentStock = ($ppp > 1) ? ($currentStock / $ppp) : $currentStock;
@endphp
<div class="ose-top-grid">

        {{-- Card 1: Product Specs --}}
        <div class="ose-card ose-prod-card">
            <div class="ose-prod-thumb">
                @if($product->image && file_exists(public_path($product->image)))
                    <img src="{{ asset($product->image) }}" alt="{{ $product->item_name }}">
                @else
                    <i class="fas fa-fan"></i>
                @endif
            </div>
            <div class="ose-prod-details">
                <div class="ose-prod-title-wrap">
                    <span class="ose-prod-name" title="{{ $product->item_name }}">{{ $product->item_name }}</span>
                    @if(!empty($selectedVariant))
                        <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe; font-size: 11.5px; font-weight: 800; border-radius: 6px; padding: 2px 9px; margin-left: 6px;">
                            <i class="fas fa-palette mr-1"></i>{{ $selectedVariant->color ?: $selectedVariant->variant_name }}
                        </span>
                    @endif
                    <span class="badge-sku-pill">{{ $selectedVariant?->barcode ?: $product->item_code }}</span>
                    @if($product->isSerial())
                        <span class="serial-badge-pill" style="display:inline-flex; align-items:center; gap:4px; font-size:10.5px; font-weight:800; color:#d97706; background:#fef3c7; border:1px solid #fde68a; border-radius:12px; padding:2px 8px; margin-left:6px;"><i class="fas fa-barcode"></i> Serial Tracked</span>
                    @endif
                </div>
                <div class="ose-prod-meta-grid">
                    <div>Category: <strong>{{ $product->category_relation?->name ?? 'General' }}</strong></div>
                    <div>Urdu Name: <strong>{{ $product->item_name_urdu ?? 'N/A' }}</strong></div>
                    <div>Unit: <strong>{{ $product->unit?->name ?? 'Pcs' }}</strong></div>
                    <div>Brand: <strong>{{ $product->brand?->name ?? ($product->brand_name ?? 'N/A') }}</strong></div>
                </div>
            </div>
        </div>

        {{-- Card 2: Target Branch Selection --}}
        <div class="ose-card">
            <div class="ose-branch-card-inner">
                <div class="ose-card-label">
                    <i class="fas fa-building"></i> Target Branch <span class="text-danger">*</span>
                </div>
                <div class="ose-branch-select-wrap">
                    <i class="fas fa-store ose-branch-icon"></i>
                    <select class="ose-branch-select" id="branch_switcher" onchange="switchBranch(this.value)">
                        @if($isSuperAdmin)
                            @foreach($allBranches as $b)
                                <option value="{{ $b->id }}" {{ $b->id == $selectedBranchId ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="{{ $selectedBranchId }}" selected>{{ $selectedBranch?->name ?? 'Current Branch' }}</option>
                        @endif
                    </select>
                    <i class="fas fa-chevron-down ose-branch-arrow"></i>
                </div>
                <div class="ose-branch-info-text">
                    <i class="fas fa-info-circle text-primary"></i>
                    Allocations and stock are loaded for <strong>{{ $selectedBranch?->name ?? 'selected branch' }}</strong>
                </div>
            </div>
        </div>

        {{-- Card 3: System-Wide Live Stock Chip --}}
        <div class="ose-card">
            <div class="ose-stock-card-inner">
                <div class="ose-stock-label">
                    <i class="fas fa-cubes"></i> System-Wide Live Stock Across Branches
                </div>
                <div class="ose-stock-row">
                    <span class="ose-stock-branch-name">{{ $selectedBranch?->name ?? 'Main Branch' }}</span>
                    <div>
                        @if(($ppp ?? 1) > 1)
                            <span class="ose-stock-qty-val" id="top_live_stock_display">{{ \App\Models\Product::formatCartonPieces($currentStock, $ppp, $product->unit?->name) }}</span>
                            <small class="text-muted font-weight-bold">({{ number_format($currentStock, 2) }} Pcs)</small>
                        @else
                            <span class="ose-stock-qty-val" id="top_live_stock_display">{{ number_format($currentStock, 2) }}</span>
                        @endif
                        <span class="badge-stock-active">Active</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Main Update Form --}}
    <form id="oseEditForm" method="POST" action="{{ route('opening.stocks.update', $product->id) }}">
        @csrf
        <input type="hidden" name="branch_id" id="form_branch_id" value="{{ $selectedBranchId }}">
        <input type="hidden" name="variant_id" id="form_variant_id" value="{{ $selectedVariant?->id ?? '' }}">
        <input type="hidden" name="allocation_data" id="allocation_data" value="[]">

        {{-- â”€â”€ 3. Opening Stock Details Table Card â”€â”€ --}}
        <div class="ose-main-card">
            <div class="ose-card-header-bar">
                <div class="ose-card-header-left">
                    <div class="ose-header-icon-square">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <h4 class="ose-table-title">Opening Stock Details</h4>
                        <p class="ose-table-sub">Manage locations, quantities and pricing for this product</p>
                    </div>
                </div>
                <div class="ose-card-header-right">
                    <button type="button" class="btn-add-location" onclick="addNewLocationRow()">
                        <i class="fas fa-plus"></i> Add Location
                    </button>
                    <div class="total-allocated-pill">
                        Total Allocated: <strong id="header_total_allocated">0.00</strong>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="ose-table" id="allocTable">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">#</th>
                            <th style="width: 250px;">Location / Warehouse <span class="text-danger">*</span></th>
                            <th style="width: 170px;">Allocated Qty</th>
                            <th style="width: 150px;">Wholesale Price (PKR)</th>
                            <th style="width: 150px;">Retail Price (PKR)</th>
                            <th style="width: 110px;">Alert Qty</th>
                            <th style="width: 140px;">Opening Qty</th>
                            <th style="width: 90px; text-align: center;">Status</th>
                            <th style="width: 60px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="alloc_rows_body">
                        @php
                            $nonZeroAllocs = $currentAllocs->filter(fn($a) => (float)$a->quantity != 0);
                        @endphp
                        @if($nonZeroAllocs->isNotEmpty())
                            @foreach($nonZeroAllocs as $index => $alloc)
                                <tr class="alloc-table-row">
                                    <td style="text-align: center; color: #94a3b8; font-weight: 700;" class="row-num-cell">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="tbl-select-wrap">
                                            <i class="fas fa-store prefix-icon"></i>
                                            <select class="alloc-type-select" onchange="calcTotal()">
                                                <option value="shop" {{ is_null($alloc->warehouse_id) ? 'selected' : '' }}>Branch / Shop Stock</option>
                                                @if(count($warehouses) > 0)
                                                    <optgroup label="── Warehouses ──">
                                                        @foreach($warehouses as $w)
                                                            <option value="wh_{{ $w->id }}" {{ (string)$alloc->warehouse_id === (string)$w->id ? 'selected' : '' }}>
                                                                {{ $w->warehouse_name ?? $w->name }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            </select>
                                            <i class="fas fa-chevron-down arrow-icon"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="tbl-input-group">
                                            @php
                                                $val = (float)$alloc->quantity;
                                                $displayQty = ($ppp > 1) ? ($val / $ppp) : $val;
                                                $locKey = $alloc->warehouse_id ? ('wh_' . $alloc->warehouse_id) : 'shop';
                                                $locSerials = $existingSerials[$locKey] ?? [];
                                                $intQty = (int)$displayQty;
                                                $enteredCount = count($locSerials);
                                            @endphp
                                            <input type="number" class="alloc-qty-input text-end" step="0.01" min="0" placeholder="0.00" value="{{ $displayQty > 0 ? (float)$displayQty : '' }}" oninput="handleQtyInput(this)">
                                            <span class="tbl-addon">{{ $product->unit?->name ?? 'Carton' }}</span>
                                        </div>
                                        @if($product->isSerial())
                                            <div class="mt-1">
                                                <button type="button" class="btn-open-serials {{ ($enteredCount === $intQty && $intQty > 0) ? 'serials-completed' : '' }}" onclick="triggerSerialModalForLine(this)" style="{{ $intQty > 0 ? 'display:inline-flex;' : 'display:none;' }}" title="Add or View Serial Numbers">
                                                    <i class="fas {{ ($enteredCount === $intQty && $intQty > 0) ? 'fa-check-circle' : 'fa-barcode' }} mr-1"></i>Serials (<span class="serials-entered-count">{{ $enteredCount }}</span>/<span class="serials-required-count">{{ $intQty }}</span>)
                                                </button>
                                                <input type="hidden" class="alloc-serials-json" value="{{ json_encode(array_values($locSerials)) }}">
                                            </div>
                                        @endif
                                        @if($ppp > 1)
                                            <div class="small text-muted font-weight-bold mt-1 alloc-pcs-preview" style="font-size: 11px;">
                                                @if($val > 0)
                                                    = {{ \App\Models\Product::formatCartonPieces($val, $ppp, $product->unit?->name) }} ({{ number_format($val, 2) }} Pcs)
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    @if($index === 0)
                                        <td rowspan="1" class="shared-field-wholesale">
                                            <div class="tbl-input-group">
                                                <input type="number" step="0.01" min="0" id="wholesale_price" name="wholesale_price" class="text-end" value="{{ (float)($product->wholesale_price ?? 0) > 0 ? (float)$product->wholesale_price : '' }}" placeholder="0" oninput="checkPriceWarning()">
                                                <span class="tbl-addon">PKR</span>
                                            </div>
                                        </td>
                                        <td rowspan="1" class="shared-field-retail">
                                            <div class="tbl-input-group">
                                                <input type="number" step="0.01" min="0" id="retail_price" name="retail_price" class="text-end" value="{{ (float)($product->price ?? 0) > 0 ? (float)$product->price : '' }}" placeholder="0" oninput="checkPriceWarning()">
                                                <span class="tbl-addon">PKR</span>
                                            </div>
                                        </td>
                                        <td rowspan="1" class="shared-field-alert">
                                            <input type="number" step="0.01" min="0" id="alert_qty" name="alert_qty" class="tbl-input-clean text-center" value="{{ (float)($product->alert_quantity ?? 0) > 0 ? (float)$product->alert_quantity : '' }}" placeholder="0" oninput="updateSummaryCards()">
                                        </td>
                                        <td rowspan="1" class="shared-field-opening">
                                            @php
                                                $displayOpening = ($ppp > 1) ? ($currentStock / $ppp) : $currentStock;
                                            @endphp
                                            <input type="number" step="0.01" id="opening_qty" name="opening_qty" class="tbl-input-readonly text-end" value="{{ (float)$displayOpening }}" readonly>
                                            <input type="hidden" id="carton_qty" name="carton_qty" value="{{ (float)$displayOpening }}">
                                        </td>
                                    @else
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @endif
                                    <td style="text-align: center;">
                                        <span class="badge" style="background: #ecfdf5; color: #047857; font-weight: 800; padding: 4px 10px; border-radius: 6px;">Active</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-trash-action" onclick="removeLocationRow(this)" title="Remove location">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            {{-- Fallback 1 Default Row --}}
                            <tr class="alloc-table-row">
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;" class="row-num-cell">1</td>
                                <td>
                                    <div class="tbl-select-wrap">
                                        <i class="fas fa-store prefix-icon"></i>
                                        <select class="alloc-type-select" onchange="calcTotal()">
                                            <option value="shop" selected>Branch / Shop Stock</option>
                                            @if(count($warehouses) > 0)
                                                <optgroup label="── Warehouses ──">
                                                    @foreach($warehouses as $w)
                                                        <option value="wh_{{ $w->id }}">{{ $w->warehouse_name ?? $w->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        </select>
                                        <i class="fas fa-chevron-down arrow-icon"></i>
                                    </div>
                                </td>
                                <td>
                                    <div class="tbl-input-group">
                                        @php
                                            $fallbackSerials = $existingSerials['shop'] ?? [];
                                            $fallbackIntQty = (int)$displayCurrentStock;
                                            $fallbackEnteredCount = count($fallbackSerials);
                                        @endphp
                                        <input type="number" class="alloc-qty-input text-end" step="0.01" min="0" placeholder="0.00" value="{{ ((float)($product->piece_per_pack ?? 1) > 1 ? ($currentStock / (float)$product->piece_per_pack) : $currentStock) > 0 ? (float)((float)($product->piece_per_pack ?? 1) > 1 ? ($currentStock / (float)$product->piece_per_pack) : $currentStock) : '' }}" oninput="handleQtyInput(this)">
                                        <span class="tbl-addon">{{ $product->unit?->name ?? 'Carton' }}</span>
                                    </div>
                                    @if($product->isSerial())
                                        <div class="mt-1">
                                            <button type="button" class="btn-open-serials {{ ($fallbackEnteredCount === $fallbackIntQty && $fallbackIntQty > 0) ? 'serials-completed' : '' }}" onclick="triggerSerialModalForLine(this)" style="{{ $fallbackIntQty > 0 ? 'display:inline-flex;' : 'display:none;' }}" title="Add or View Serial Numbers">
                                                <i class="fas {{ ($fallbackEnteredCount === $fallbackIntQty && $fallbackIntQty > 0) ? 'fa-check-circle' : 'fa-barcode' }} mr-1"></i>Serials (<span class="serials-entered-count">{{ $fallbackEnteredCount }}</span>/<span class="serials-required-count">{{ $fallbackIntQty }}</span>)
                                            </button>
                                            <input type="hidden" class="alloc-serials-json" value="{{ json_encode(array_values($fallbackSerials)) }}">
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="tbl-input-group">
                                        <input type="number" step="0.01" min="0" id="wholesale_price" name="wholesale_price" class="text-end" value="{{ (float)($product->wholesale_price ?? 0) > 0 ? (float)$product->wholesale_price : '' }}" placeholder="0" oninput="checkPriceWarning()">
                                        <span class="tbl-addon">PKR</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="tbl-input-group">
                                        <input type="number" step="0.01" min="0" id="retail_price" name="retail_price" class="text-end" value="{{ (float)($product->price ?? 0) > 0 ? (float)$product->price : '' }}" placeholder="0" oninput="checkPriceWarning()">
                                        <span class="tbl-addon">PKR</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" id="alert_qty" name="alert_qty" class="tbl-input-clean text-center" value="{{ (float)($product->alert_quantity ?? 0) > 0 ? (float)$product->alert_quantity : '' }}" placeholder="0" oninput="updateSummaryCards()">
                                </td>
                                <td>
                                    <input type="number" step="0.01" id="opening_qty" name="opening_qty" class="tbl-input-readonly text-end" value="{{ (float)$displayCurrentStock }}" readonly>
                                    <input type="hidden" id="carton_qty" name="carton_qty" value="{{ (float)$displayCurrentStock }}">
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge" style="background: #ecfdf5; color: #047857; font-weight: 800; padding: 4px 10px; border-radius: 6px;">Active</span>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-trash-action" onclick="removeLocationRow(this)" title="Remove location">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Price Warning Notification Strip --}}
            <div id="price_warning_bar" class="ose-warning-strip" style="display: none;">
                <i class="fas fa-exclamation-triangle"></i>
                <span id="price_warning_text">Retail price is less than or equal to wholesale price.</span>
            </div>
        </div>

        {{-- â”€â”€ 4. Bottom Allocation Summary (Current Allocation Summary) â”€â”€ --}}
        <div class="ose-summary-section">
            <div class="ose-summary-title">
                <i class="fas fa-chart-line text-primary"></i>
                Current Allocation Summary ({{ $selectedBranch?->name ?? 'Main Branch' }})
            </div>
            <div class="ose-summary-grid">

                {{-- Stat 1: Live Stock --}}
                <div class="ose-stat-card">
                    <div class="ose-stat-icon-wrap icon-blue-bg">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div class="ose-stat-info">
                        <div class="ose-stat-label">Live Stock</div>
                        <div>
                            <span class="ose-stat-value" id="card_live_stock">{{ number_format($displayCurrentStock, 2) }}</span>
                            <span class="ose-stat-unit">{{ $product->unit?->name ?? 'Carton' }}</span>
                            @if($ppp > 1)
                                <div style="font-size: 11px; color: #64748b; font-weight: 600;">{{ number_format($currentStock, 2) }} Pcs</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Stat 2: Total Allocated --}}
                <div class="ose-stat-card">
                    <div class="ose-stat-icon-wrap icon-green-bg">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="ose-stat-info">
                        <div class="ose-stat-label">Total Allocated</div>
                        <div>
                            <span class="ose-stat-value" id="card_total_allocated">0.00</span>
                            <span class="ose-stat-unit">{{ $product->unit?->name ?? 'Carton' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Stat 3: Alert Quantity --}}
                <div class="ose-stat-card">
                    <div class="ose-stat-icon-wrap icon-amber-bg">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="ose-stat-info">
                        <div class="ose-stat-label">Alert Quantity</div>
                        <div>
                            <span class="ose-stat-value" id="card_alert_qty">{{ (float)($product->alert_quantity ?? 0) }}</span>
                            <span class="ose-stat-unit">{{ $product->unit?->name ?? 'Carton' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Stat 4: Total Opening Qty --}}
                <div class="ose-stat-card">
                    <div class="ose-stat-icon-wrap icon-purple-bg">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="ose-stat-info">
                        <div class="ose-stat-label">Total Opening Qty</div>
                        <div>
                            <span class="ose-stat-value" id="card_total_opening">0.00</span>
                            <span class="ose-stat-unit">{{ $product->unit?->name ?? 'Carton' }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- â”€â”€ 5. Bottom Action Bar â”€â”€ --}}
        <div class="ose-footer-bar">
            <a href="{{ route('opening.stocks.index') }}" class="btn-ose-cancel">
                <i class="fas fa-arrow-left"></i> Cancel &amp; Back
            </a>
            <button type="submit" class="btn-ose-submit" id="btn_submit_ose">
                <i class="fas fa-save"></i> Update Opening Stock
            </button>
        </div>

    </form>

    {{-- ── Serial / IMEI Numbers Entry Modal ── --}}
    <div class="modal fade" id="serialModal" tabindex="-1" role="dialog" aria-labelledby="serialModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.2); overflow: hidden;">
                <div class="modal-header py-2 px-3" style="background: #1e3a5f; color: #fff; border-bottom: 2px solid #0284c7;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-barcode mr-2" style="font-size: 18px; color: #38bdf8;"></i>
                        <div>
                            <h6 class="modal-title m-0 font-weight-bold" id="serialModalLabel" style="font-size: 14px; letter-spacing: 0.3px;">
                                Add Serial / IMEI Numbers
                            </h6>
                            <small style="color: #94a3b8; font-size: 11px;">Enter or scan serial numbers for each allocated unit</small>
                        </div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; outline: none; background: none; border: none; font-size: 20px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-3" style="background: #f8fafc;">
                    <!-- Product & Location Summary Banner -->
                    <div class="d-flex justify-content-between align-items-center p-2 mb-3 rounded" style="background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div>
                            <div class="font-weight-bold text-dark" id="sm_product_name" style="font-size: 13.5px;">
                                {{ $product->item_name }}
                                @if(!empty($selectedVariant))
                                    <span class="badge badge-info ml-1" style="font-size: 11px;">{{ $selectedVariant->color ?: $selectedVariant->variant_name }}</span>
                                @endif
                            </div>
                            <div class="text-muted small" style="font-size: 11px;">
                                <span id="sm_product_code" class="font-weight-bold text-secondary">SKU: {{ $selectedVariant?->barcode ?: $product->item_code }}</span> &bull; 
                                Location: <span class="font-weight-bold text-primary" id="sm_location_name">Branch / Shop Stock</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-primary px-2 py-1" style="font-size: 12px; font-weight: 700; background: #0284c7;" id="sm_qty_badge">
                                Target: 0 Serials
                            </span>
                        </div>
                    </div>

                    <!-- Toolbar & Modes (Slots & Quick Paste / Scan) -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small font-weight-bold">
                            <span id="sm_count_label" class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11.5px;">
                                0 / 0 Entered
                            </span>
                        </span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-secondary active font-weight-bold" id="btn_mode_slots" style="font-size: 11px;">
                                <i class="fas fa-list-ol mr-1"></i> Slots
                            </button>
                            <button type="button" class="btn btn-outline-secondary font-weight-bold" id="btn_mode_bulk" style="font-size: 11px;">
                                <i class="fas fa-paste mr-1"></i> Quick Paste / Scan
                            </button>
                        </div>
                    </div>

                    <!-- View 1: Slots (1 to N) -->
                    <div id="sm_slots_container" style="max-height: 320px; overflow-y: auto; padding-right: 4px;">
                        <!-- Dynamically generated input fields -->
                    </div>

                    <!-- View 2: Quick Bulk Paste / Scan Textarea (Hidden by default) -->
                    <div id="sm_bulk_container" style="display: none;">
                        <p class="small text-muted mb-1">
                            <i class="fas fa-info-circle mr-1 text-primary"></i> Paste serial numbers separated by newlines, commas, or scan continuously with a barcode gun:
                        </p>
                        <textarea id="sm_bulk_textarea" class="form-control font-monospace" rows="7" placeholder="Scan or paste serial numbers here...
SN-1001
SN-1002
SN-1003" style="font-size: 12px; line-height: 1.5;"></textarea>
                        <div class="d-flex justify-content-end mt-2">
                            <button type="button" class="btn btn-sm btn-primary" id="btn_apply_bulk_serials" style="font-size: 11.5px; font-weight: 700; background: #0284c7; border-color: #0284c7;">
                                <i class="fas fa-check mr-1"></i> Apply to Slots
                            </button>
                        </div>
                    </div>

                    <!-- Duplicate or Error Alert -->
                    <div id="sm_alert" class="alert alert-danger py-2 px-3 mt-2 mb-0 small" style="display: none; font-size: 11.5px; font-weight: 600;"></div>
                </div>

                <div class="modal-footer py-2 px-3" style="background: #ffffff; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-light border text-secondary font-weight-bold px-3" data-dismiss="modal" style="font-size: 12px;">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-sm btn-primary px-3 font-weight-bold" id="btn_save_serials" style="font-size: 12px; background: #0284c7; border-color: #0284c7;">
                        <i class="fas fa-check-circle mr-1"></i> Save Serials
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@else
    <div class="alert alert-danger m-4">You do not have permission to manage opening stocks.</div>
@endcan
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {

    var warehousesData  = @json(collect($warehouses)->map(fn($w) => ['id' => $w->id, 'warehouse_name' => $w->warehouse_name ?? $w->name]));
    var unitName        = "{{ $product->unit?->name ?? 'Carton' }}";
    var liveStockVal    = parseFloat("{{ $currentStock }}") || 0;
    var isSerialProduct = {{ $product->isSerial() ? 'true' : 'false' }};
    var ppp             = {{ $product->isCartonProduct() ? (float)($product->piece_per_pack ?? 1) : 1 }};

    var currentActiveAllocRow  = null;
    var currentTargetQty       = 0;
    var qtySerialDebounceTimer = null;

    // Switch Branch (Super Admin)
    window.switchBranch = function(branchId) {
        if (!branchId) return;
        var url = "{{ route('opening.stocks.edit', $product->id) }}?branch_id=" + branchId;
        var vId = $('#form_variant_id').val();
        if (vId) url += '&variant_id=' + vId;
        window.location.href = url;
    };

    // Build Location Options HTML
    function buildWarehouseOptions() {
        var opts = '<option value="shop">Branch / Shop Stock</option>';
        if (warehousesData.length > 0) {
            opts += '<optgroup label="── Warehouses ──">';
            warehousesData.forEach(function(w) {
                opts += '<option value="wh_' + w.id + '">' + (w.warehouse_name || 'Warehouse') + '</option>';
            });
            opts += '</optgroup>';
        }
        return opts;
    }

    // Add New Location Row
    window.addNewLocationRow = function() {
        var rowCount = $('#alloc_rows_body tr').length + 1;
        var serialBtnHtml = '';
        if (isSerialProduct) {
            serialBtnHtml = `
                <div class="mt-1">
                    <button type="button" class="btn-open-serials" onclick="triggerSerialModalForLine(this)" style="display:none;" title="Add or View Serial Numbers">
                        <i class="fas fa-barcode mr-1"></i>Serials (<span class="serials-entered-count">0</span>/<span class="serials-required-count">0</span>)
                    </button>
                    <input type="hidden" class="alloc-serials-json" value="[]">
                </div>
            `;
        }

        var newRowHtml = `
            <tr class="alloc-table-row">
                <td style="text-align: center; color: #94a3b8; font-weight: 700;" class="row-num-cell">${rowCount}</td>
                <td>
                    <div class="tbl-select-wrap">
                        <i class="fas fa-warehouse prefix-icon"></i>
                        <select class="alloc-type-select" onchange="calcTotal()">
                            ${buildWarehouseOptions()}
                        </select>
                        <i class="fas fa-chevron-down arrow-icon"></i>
                    </div>
                </td>
                <td>
                    <div class="tbl-input-group">
                        <input type="number" class="alloc-qty-input text-end" step="0.01" min="0" placeholder="0.00" value="0.00" oninput="handleQtyInput(this)">
                        <span class="tbl-addon">${unitName}</span>
                    </div>
                    ${serialBtnHtml}
                    ${ppp > 1 ? '<div class="small text-muted font-weight-bold mt-1 alloc-pcs-preview" style="font-size: 11px;"></div>' : ''}
                </td>
                <td class="text-muted small text-center">—</td>
                <td class="text-muted small text-center">—</td>
                <td class="text-muted small text-center">—</td>
                <td class="text-muted small text-center">—</td>
                <td style="text-align: center;">
                    <span class="badge" style="background: #ecfdf5; color: #047857; font-weight: 800; padding: 4px 10px; border-radius: 6px;">Active</span>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn-trash-action" onclick="removeLocationRow(this)" title="Remove location">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#alloc_rows_body').append(newRowHtml);
        reindexRows();
        calcTotal();
    };

    // Remove Location Row
    window.removeLocationRow = function(btn) {
        if ($('#alloc_rows_body tr').length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'At least one location required',
                text: 'You cannot remove the only location row. You can set its quantity to 0.',
                confirmButtonColor: '#0284c7'
            });
            return;
        }

        var $row = $(btn).closest('tr');
        var isFirstRow = $row.index() === 0;

        if (isFirstRow) {
            // Transfer pricing/opening inputs to the next row before removing
            var $nextRow = $row.next();
            if ($nextRow.length) {
                var wholesaleVal = $('#wholesale_price').val();
                var retailVal    = $('#retail_price').val();
                var alertVal     = $('#alert_qty').val();
                var openingVal   = $('#opening_qty').val();
                var baseIdx      = 3;

                $nextRow.find('td').eq(baseIdx).html(`
                    <div class="tbl-input-group">
                        <input type="number" step="0.01" min="0" id="wholesale_price" name="wholesale_price" class="text-end" value="${wholesaleVal}" placeholder="0" oninput="checkPriceWarning()">
                        <span class="tbl-addon">PKR</span>
                    </div>
                `);
                $nextRow.find('td').eq(baseIdx + 1).html(`
                    <div class="tbl-input-group">
                        <input type="number" step="0.01" min="0" id="retail_price" name="retail_price" class="text-end" value="${retailVal}" placeholder="0" oninput="checkPriceWarning()">
                        <span class="tbl-addon">PKR</span>
                    </div>
                `);
                $nextRow.find('td').eq(baseIdx + 2).html(`
                    <input type="number" step="0.01" min="0" id="alert_qty" name="alert_qty" class="tbl-input-clean text-center" value="${alertVal}" placeholder="0" oninput="updateSummaryCards()">
                `);
                $nextRow.find('td').eq(baseIdx + 3).html(`
                    <input type="number" step="0.01" id="opening_qty" name="opening_qty" class="tbl-input-readonly text-end" value="${openingVal}" readonly>
                    <input type="hidden" id="carton_qty" name="carton_qty" value="${openingVal}">
                `);
            }
        }

        $row.remove();
        reindexRows();
        calcTotal();
    };

    function reindexRows() {
        $('#alloc_rows_body tr').each(function(i) {
            $(this).find('.row-num-cell').text(i + 1);
        });
    }

    // Calculations
    window.calcTotal = function() {
        var total = 0;
        var data = [];

        var variantId = $('#form_variant_id').val() ? parseInt($('#form_variant_id').val()) : null;

        $('#alloc_rows_body tr').each(function() {
            var type = $(this).find('.alloc-type-select').val();
            var qty  = parseFloat($(this).find('.alloc-qty-input').val()) || 0;
            total += qty;

            var serials = [];
            if (isSerialProduct) {
                try {
                    serials = JSON.parse($(this).find('.alloc-serials-json').val() || '[]');
                } catch(e) {}
            }

            if (ppp > 1) {
                var pieceCount = qty * ppp;
                var previewText = '';
                if (qty > 0) {
                    var ctn = Math.floor(qty);
                    var loose = Math.round((pieceCount - (ctn * ppp)) * 100) / 100;
                    var parts = [];
                    if (ctn > 0) parts.push(ctn + ' Ctn');
                    if (loose > 0 || parts.length === 0) {
                        var lStr = (loose % 1 === 0) ? loose : loose.toFixed(2);
                        parts.push(lStr + ' Pcs');
                    }
                    previewText = '= ' + parts.join(' + ') + ' (' + pieceCount.toFixed(2) + ' Pcs)';
                }
                $(this).find('.alloc-pcs-preview').text(previewText);
            }

            if (type === 'shop') {
                data.push({
                    location_type: 'shop',
                    quantity: qty,
                    variant_id: variantId,
                    serials: serials
                });
            } else if (type && type.indexOf('wh_') === 0) {
                data.push({
                    location_type: 'warehouse',
                    warehouse_id: parseInt(type.replace('wh_', '')),
                    variant_id: variantId,
                    quantity: qty,
                    serials: serials
                });
            }
        });

        var formattedTotal = total.toFixed(2);
        $('#opening_qty').val(formattedTotal);
        $('#carton_qty').val(formattedTotal);
        $('#header_total_allocated').text(formattedTotal);
        $('#card_total_allocated').text(formattedTotal);
        $('#card_total_opening').text(formattedTotal);
        $('#allocation_data').val(JSON.stringify(data));

        checkPriceWarning();
    };

    window.updateSummaryCards = function() {
        var alertVal = parseFloat($('#alert_qty').val()) || 0;
        $('#card_alert_qty').text(alertVal);
    };

    window.checkPriceWarning = function() {
        var wholesale = parseFloat($('#wholesale_price').val()) || 0;
        var retail    = parseFloat($('#retail_price').val()) || 0;
        var warningEl = $('#price_warning_bar');
        var textEl    = $('#price_warning_text');

        if (wholesale > 0 && retail > 0 && retail <= wholesale) {
            textEl.text(`Retail price (PKR ${retail.toLocaleString()}) is less than or equal to wholesale price (PKR ${wholesale.toLocaleString()}).`);
            warningEl.slideDown(150);
        } else {
            warningEl.slideUp(150);
        }
    };

    /* Update Serials button appearance & counts on allocation lines */
    window.updateAllocSerialButtons = function() {
        if (!isSerialProduct) return;
        $('#alloc_rows_body tr').each(function() {
            var $row = $(this);
            var $btn = $row.find('.btn-open-serials');
            var qty = parseInt($row.find('.alloc-qty-input').val()) || 0;
            if (qty <= 0) {
                $btn.hide();
                return;
            }
            var serials = [];
            try {
                serials = JSON.parse($row.find('.alloc-serials-json').val() || '[]');
            } catch(e) {}
            var enteredCount = serials.filter(function(s) { return String(s).trim().length > 0; }).length;

            $btn.find('.serials-entered-count').text(enteredCount);
            $btn.find('.serials-required-count').text(qty);

            if (enteredCount === qty && qty > 0) {
                $btn.addClass('serials-completed').html('<i class="fas fa-check-circle mr-1"></i>Serials (' + enteredCount + '/' + qty + ')');
            } else {
                $btn.removeClass('serials-completed').html('<i class="fas fa-barcode mr-1"></i>Serials (' + enteredCount + '/' + qty + ')');
            }
            $btn.css('display', 'inline-flex');
        });
    };

    /* Handle Qty typing / change on allocation row */
    window.handleQtyInput = function(input) {
        calcTotal();
        if (!isSerialProduct) return;

        var $row = $(input).closest('tr');
        var qty = parseInt($(input).val()) || 0;
        updateAllocSerialButtons();

        if (qty > 0) {
            clearTimeout(qtySerialDebounceTimer);
            qtySerialDebounceTimer = setTimeout(function() {
                if ($('#serialModal').hasClass('show')) return;
                var currentSerials = [];
                try {
                    currentSerials = JSON.parse($row.find('.alloc-serials-json').val() || '[]');
                } catch(e) {}
                var validCount = currentSerials.filter(function(s) { return String(s).trim().length > 0; }).length;
                if (validCount !== qty) {
                    openSerialModal($row, qty);
                }
            }, 600);
        }
    };

    /* Trigger Serial Modal for Line */
    window.triggerSerialModalForLine = function(btn) {
        var $row = $(btn).closest('tr');
        var qty = parseInt($row.find('.alloc-qty-input').val()) || 0;
        if (qty <= 0) {
            Swal.fire({
                icon: 'info',
                title: 'Quantity Needed',
                text: 'Please enter a quantity first before adding serial numbers.',
                confirmButtonColor: '#0284c7'
            });
            $row.find('.alloc-qty-input').focus();
            return;
        }
        openSerialModal($row, qty);
    };

    /* Open Serial Modal */
    window.openSerialModal = function($row, qty) {
        currentActiveAllocRow  = $row;
        currentTargetQty       = parseInt(qty) || 0;

        var locSelect = $row.find('.alloc-type-select');
        var locName   = locSelect.find('option:selected').text() || 'Branch / Shop Stock';

        $('#sm_location_name').text(locName.trim());
        $('#sm_qty_badge').text('Target: ' + currentTargetQty + ' Serials');

        var existing = [];
        try {
            existing = JSON.parse($row.find('.alloc-serials-json').val() || '[]');
        } catch(e) {}

        renderSerialSlots(currentTargetQty, existing);

        // Switch to Slots tab by default
        $('#sm_slots_container').show();
        $('#sm_bulk_container').hide();
        $('#btn_mode_slots').addClass('active btn-secondary').removeClass('btn-outline-secondary');
        $('#btn_mode_bulk').removeClass('active btn-secondary').addClass('btn-outline-secondary');
        $('#sm_alert').hide().text('');

        $('#serialModal').modal('show');
        setTimeout(function() {
            var firstEmpty = $('#sm_slots_container input.sm-slot-input').filter(function() { return !$(this).val(); }).first();
            if (firstEmpty.length) {
                firstEmpty.focus();
            } else {
                $('#sm_slots_container input.sm-slot-input').first().focus();
            }
        }, 400);
    };

    /* Render Slot inputs for N serials */
    function renderSerialSlots(qty, existingValues) {
        var html = '<div class="row g-2">';
        existingValues = existingValues || [];
        for (var i = 0; i < qty; i++) {
            var val = (existingValues[i] !== undefined && existingValues[i] !== null) ? existingValues[i] : '';
            html += `
                <div class="col-md-6 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text font-weight-bold" style="background:#f1f5f9; color:#475569; font-size:11.5px; width:40px; justify-content:center;">#${i + 1}</span>
                        </div>
                        <input type="text" class="form-control sm-slot-input font-monospace" data-slot="${i}" value="${val}" placeholder="Serial / IMEI #${i + 1}" style="font-size:12px; height:32px;">
                    </div>
                </div>
            `;
        }
        html += '</div>';
        $('#sm_slots_container').html(html);
        updateModalCounter();
    }

    /* Modal counter & duplicate validation */
    function updateModalCounter() {
        var entered = 0;
        var seen = {};
        var hasDup = false;
        $('.sm-slot-input').each(function() {
            var v = $(this).val().trim();
            if (v) {
                entered++;
                if (seen[v.toLowerCase()]) {
                    hasDup = true;
                    $(this).css('border-color', '#ef4444');
                } else {
                    seen[v.toLowerCase()] = true;
                    $(this).css('border-color', '');
                }
            } else {
                $(this).css('border-color', '');
            }
        });

        var $badge = $('#sm_count_label');
        $badge.text(entered + ' / ' + currentTargetQty + ' Entered');

        if (hasDup) {
            $('#sm_alert').show().text('Duplicate serial numbers detected! Each serial number must be unique.');
            $badge.removeClass('badge-success badge-warning').addClass('badge-danger');
        } else {
            $('#sm_alert').hide().text('');
            if (entered >= currentTargetQty && currentTargetQty > 0) {
                $badge.removeClass('badge-danger badge-warning').addClass('badge-success');
            } else {
                $badge.removeClass('badge-danger badge-success').addClass('badge-warning');
            }
        }
    }

    $(document).on('input', '.sm-slot-input', function() {
        updateModalCounter();
    });

    /* Enter key in slot jumps to next slot */
    $(document).on('keydown', '.sm-slot-input', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var nextSlot = $(this).closest('.col-md-6').next().find('.sm-slot-input');
            if (nextSlot.length) {
                nextSlot.focus();
            } else {
                $('#btn_save_serials').focus();
            }
        }
    });

    /* Paste multi-line text into a slot auto-distributes */
    $(document).on('paste', '.sm-slot-input', function(e) {
        var clipboardData = e.originalEvent.clipboardData || window.clipboardData;
        var pastedData = clipboardData ? clipboardData.getData('text') : '';
        if (pastedData && (pastedData.indexOf('\n') !== -1 || pastedData.indexOf('\r') !== -1 || pastedData.indexOf('\t') !== -1)) {
            e.preventDefault();
            var lines = pastedData.split(/[\r\n\t]+/).map(function(s) { return s.trim(); }).filter(function(s) { return s.length > 0; });
            var startSlot = parseInt($(this).data('slot')) || 0;
            var $slots = $('.sm-slot-input');
            for (var i = 0; i < lines.length && (startSlot + i) < $slots.length; i++) {
                $slots.eq(startSlot + i).val(lines[i]);
            }
            updateModalCounter();
        }
    });

    /* Tab switching: Slots vs Bulk */
    $('#btn_mode_slots').on('click', function() {
        $('#sm_slots_container').show();
        $('#sm_bulk_container').hide();
        $(this).addClass('active btn-secondary').removeClass('btn-outline-secondary');
        $('#btn_mode_bulk').removeClass('active btn-secondary').addClass('btn-outline-secondary');
    });

    $('#btn_mode_bulk').on('click', function() {
        var lines = [];
        $('.sm-slot-input').each(function() {
            var v = $(this).val().trim();
            if (v) lines.push(v);
        });
        $('#sm_bulk_textarea').val(lines.join('\n'));

        $('#sm_slots_container').hide();
        $('#sm_bulk_container').show();
        $(this).addClass('active btn-secondary').removeClass('btn-outline-secondary');
        $('#btn_mode_slots').removeClass('active btn-secondary').addClass('btn-outline-secondary');
        $('#sm_bulk_textarea').focus();
    });

    $('#btn_apply_bulk_serials').on('click', function() {
        var text = $('#sm_bulk_textarea').val() || '';
        var lines = text.split(/[\r\n,]+/).map(function(s) { return s.trim(); }).filter(function(s) { return s.length > 0; });
        var $slots = $('.sm-slot-input');
        $slots.val('');
        for (var i = 0; i < lines.length && i < $slots.length; i++) {
            $slots.eq(i).val(lines[i]);
        }
        $('#btn_mode_slots').trigger('click');
        updateModalCounter();
    });

    /* Save serials from modal */
    $('#btn_save_serials').on('click', function() {
        var serials = [];
        var duplicates = [];
        var seen = {};

        $('.sm-slot-input').each(function() {
            var v = $(this).val().trim();
            if (v) {
                if (seen[v.toLowerCase()]) {
                    duplicates.push(v);
                } else {
                    seen[v.toLowerCase()] = true;
                }
                serials.push(v);
            }
        });

        if (duplicates.length > 0) {
            $('#sm_alert').show().text('Duplicate serial numbers detected: ' + duplicates.join(', ') + '. Each serial number must be unique.');
            return;
        }

        if (serials.length < currentTargetQty) {
            Swal.fire({
                title: 'Incomplete Serials',
                text: 'You have entered ' + serials.length + ' of ' + currentTargetQty + ' serial numbers. Save anyway?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0284c7',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Save',
                cancelButtonText: 'Back to Edit'
            }).then(function(res) {
                if (res.isConfirmed) {
                    commitSerials(serials);
                }
            });
        } else {
            commitSerials(serials);
        }
    });

    function commitSerials(serials) {
        if (currentActiveAllocRow) {
            currentActiveAllocRow.find('.alloc-serials-json').val(JSON.stringify(serials));
            updateAllocSerialButtons();
            calcTotal();
        }
        $('#serialModal').modal('hide');
    }

    // AJAX Form Submit
    $('#oseEditForm').on('submit', function(e) {
        e.preventDefault();
        calcTotal();

        var wholesale = parseFloat($('#wholesale_price').val()) || 0;
        var retail    = parseFloat($('#retail_price').val()) || 0;

        if (wholesale > 0 && retail > 0 && retail < wholesale) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Pricing',
                text: 'Retail price cannot be strictly less than Wholesale price.',
                confirmButtonColor: '#0284c7'
            });
            return;
        }

        // Validate serials if serial product
        if (isSerialProduct) {
            var serialError = null;
            $('#alloc_rows_body tr').each(function() {
                var qty = parseInt($(this).find('.alloc-qty-input').val()) || 0;
                if (qty > 0) {
                    var serials = [];
                    try {
                        serials = JSON.parse($(this).find('.alloc-serials-json').val() || '[]');
                    } catch(e) {}
                    var validCount = serials.filter(function(s) { return String(s).trim().length > 0; }).length;
                    if (validCount !== qty) {
                        var locName = $(this).find('.alloc-type-select option:selected').text().trim();
                        serialError = 'Location "' + locName + '" requires ' + qty + ' serial numbers, but only ' + validCount + ' were provided.';
                        return false;
                    }
                }
            });

            if (serialError) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Serial Numbers',
                    text: serialError,
                    confirmButtonColor: '#0284c7'
                });
                return;
            }
        }

        var $btn = $('#btn_submit_ose');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating Stock…');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Opening Stock');
                if (res && res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated Successfully!',
                        text: res.message || 'Opening stock updated successfully.',
                        confirmButtonColor: '#0284c7',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(function() {
                        window.location.href = "{{ route('opening.stocks.index') }}";
                    }, 1400);
                } else {
                    window.location.href = "{{ route('opening.stocks.index') }}";
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Opening Stock');
                var msg = 'Failed to update opening stock. Please check inputs.';
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (r.error) msg = r.error;
                    if (r.message) msg = r.message;
                } catch(err) {}
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: msg,
                    confirmButtonColor: '#0284c7'
                });
            }
        });
    });

    // Initial Trigger
    calcTotal();
    updateAllocSerialButtons();
    updateSummaryCards();
});
</script>
@endsection

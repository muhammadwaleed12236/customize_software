@extends('admin_panel.layout.app')

@section('content')
@can('product.edit')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    .edit-wrap {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #f8fafc;
        min-height: 100vh;
        padding: 1.5rem;
    }

    .edit-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1.5px solid #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        max-width: 980px;
        margin: 0 auto;
        overflow: hidden;
    }

    /* Banner */
    .prod-banner {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        color: #ffffff;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
    }
    .banner-icon {
        width: 52px;
        height: 52px;
        background: rgba(99, 102, 241, 0.2);
        border: 1.5px solid rgba(129, 140, 248, 0.4);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: #818cf8;
        flex-shrink: 0;
    }
    .prod-banner h5 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.01em;
    }
    .prod-banner small {
        font-size: 12.5px;
        color: #94a3b8;
        display: block;
        margin-top: 2px;
    }
    .banner-badge {
        background: rgba(99, 102, 241, 0.25);
        border: 1px solid rgba(165, 180, 252, 0.3);
        color: #c7d2fe;
        font-size: 11.5px;
        font-weight: 700;
        padding: 5px 14px;
        border-radius: 20px;
        margin-left: auto;
    }

    /* Stock summary stats bar */
    .stock-info-bar {
        background: #f0fdf4;
        border-bottom: 1.5px solid #bbf7d0;
        padding: 1rem 1.5rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 1rem;
    }
    .sib-item {
        background: #ffffff;
        border: 1px solid #dcfce7;
        padding: 10px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 2px 6px rgba(22, 101, 52, 0.03);
    }
    .sib-val {
        font-size: 18px;
        font-weight: 800;
        color: #166534;
    }
    .sib-lbl {
        font-size: 10px;
        font-weight: 800;
        color: #15803d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    /* Sections */
    .form-section {
        padding: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .form-section h6 {
        font-weight: 800;
        color: #0f172a;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .input-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
    }
    .fg label {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
        display: block;
    }
    .fi {
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        padding: 9px 12px;
        font-size: 14px;
        width: 100%;
        background: #f8fafc;
        transition: all 0.2s;
        color: #0f172a;
        font-weight: 500;
    }
    .fi:focus {
        border-color: #6366f1;
        background: #ffffff;
        outline: none;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
    }
    .fi-num {
        text-align: right;
        font-weight: 700;
    }

    /* Input prefix (Currency ₨) */
    .input-with-prefix {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }
    .input-with-prefix .prefix {
        position: absolute;
        left: 12px;
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
        pointer-events: none;
    }
    .input-with-prefix input {
        padding-left: 28px !important;
    }

    .delta-info {
        font-size: 11.5px;
        margin-top: 6px;
        font-weight: 700;
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
    }
    .delta-pos { color: #166534; background: #dcfce7; }
    .delta-neg { color: #991b1b; background: #fee2e2; }
    .delta-zero { color: #92400e; background: #fef3c7; }

    /* Allocation table */
    .alloc-panel {
        background: #eef2ff;
        border: 1.5px dashed #a5b4fc;
        border-radius: 12px;
        padding: 1rem;
    }
    .alloc-grid-head {
        display: grid;
        grid-template-columns: 1fr 140px 40px;
        gap: 10px;
        font-size: 11px;
        font-weight: 800;
        color: #4f46e5;
        text-transform: uppercase;
        margin-bottom: 8px;
        padding: 0 4px;
    }
    .alloc-grid-row {
        display: grid;
        grid-template-columns: 1fr 140px 40px;
        gap: 10px;
        align-items: center;
        margin-bottom: 8px;
        background: #ffffff;
        padding: 6px 8px;
        border-radius: 10px;
        border: 1px solid #c7d2fe;
    }
    .alloc-grid-row select {
        font-size: 13px;
        border: none;
        background: transparent;
        font-weight: 600;
        width: 100%;
        color: #0f172a;
        outline: none;
    }
    .alloc-grid-row input {
        font-size: 14px;
        border: none;
        background: transparent;
        text-align: right;
        font-weight: 800;
        width: 100%;
        color: #4f46e5;
        outline: none;
    }
    .btn-del-alloc {
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 8px;
        width: 34px;
        height: 34px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .btn-del-alloc:hover {
        background: #dc2626;
        color: #ffffff;
    }
    .btn-add-alloc {
        background: #6366f1;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 8px 18px;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 8px;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-add-alloc:hover {
        background: #4f46e5;
        transform: translateY(-1px);
    }
    .alloc-status {
        font-size: 12px;
        font-weight: 700;
        margin-top: 10px;
    }

    /* Action bar */
    .action-bar {
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 0 0 16px 16px;
        background: #f8fafc;
        border-top: 1.5px solid #e2e8f0;
        gap: 1rem;
    }
    .btn-cancel {
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        border-radius: 10px;
        padding: 10px 22px;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-cancel:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .btn-save {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 11px 32px;
        font-size: 14.5px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
    }

    .flash-success {
        background: #dcfce7;
        border: 1.5px solid #86efac;
        color: #166534;
        padding: 12px 18px;
        border-radius: 12px;
        margin-bottom: 1.25rem;
        font-weight: 700;
        font-size: 13.5px;
    }

    @media (max-width: 767.98px) {
        .edit-wrap {
            padding: 0.75rem;
        }
        .banner-badge {
            margin-left: 0;
            width: 100%;
            text-align: center;
        }
        .alloc-grid-head {
            display: none;
        }
        .alloc-grid-row {
            grid-template-columns: 1fr 100px 34px;
            gap: 6px;
        }
        .action-bar {
            flex-direction: column-reverse;
        }
        .btn-cancel, .btn-save {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }
</style>

<div class="edit-wrap">
    @if(session('success'))
        <div class="flash-success">✅ {{ session('success') }}</div>
    @endif

    <div class="edit-card">

        {{-- Product Banner --}}
        <div class="prod-banner">
            <div class="banner-icon">
                <i class="las la-box"></i>
            </div>
            <div style="flex:1;">
                <h5>{{ $product->item_name }}</h5>
                <small>SKU: <strong>{{ $product->item_code }}</strong> &nbsp;|&nbsp; Unit: <strong>{{ $product->unit?->name ?? 'PCS' }}</strong> &nbsp;|&nbsp; Branch: <strong>{{ $product->branch?->name ?? '—' }}</strong></small>
            </div>
            <span class="banner-badge">Edit Stock &amp; Pricing</span>
        </div>

        {{-- Super Admin Branch Switcher --}}
        @if($isSuperAdmin)
        <div style="background:linear-gradient(135deg,#1e293b,#0f172a);padding:1rem 1.5rem;border-bottom:1px solid #334155;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:18px;">🏠</span>
                <span style="color:#93c5fd;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;">Editing Branch Stock:</span>
            </div>
            <select id="branch_switcher"
                    style="border:2px solid #3b82f6;border-radius:10px;padding:7px 14px;font-size:14px;font-weight:700;color:#0f172a;background:#eff6ff;min-width:220px;cursor:pointer;">
                @foreach($availableBranches as $br)
                    <option value="{{ $br->id }}" {{ $br->id == $selectedBranchId ? 'selected' : '' }}>
                        {{ $br->name }}
                    </option>
                @endforeach
            </select>
            @if($availableBranches->count() > 1)
            <span style="color:#fbbf24;font-size:11.5px;font-weight:700;">⚠️ Stock available across {{ $availableBranches->count() }} branches &mdash; choose target branch.</span>
            @endif
            <a href="{{ route('opening.stocks.edit', $product->id) }}" style="color:#93c5fd;font-size:12px;margin-left:auto;font-weight:700;text-decoration:underline;">Reset selection</a>
        </div>
        @if($isSuperAdmin && count($globalStockSummary) > 0)
        <div style="background:#f8fafc; padding:1rem 1.5rem; border-bottom:1.5px solid #e2e8f0;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:0.8rem;">
                <i class="las la-globe" style="color:#6366f1; font-size:18px;"></i>
                <span style="color:#475569; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">System-Wide Stock Distribution:</span>
            </div>
            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                @foreach($globalStockSummary as $row)
                    <div style="display:flex; align-items:center; gap:6px; background:#fff; border:1px solid #cbd5e1; padding:5px 12px; border-radius:8px; font-size:12.5px; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
                        <span style="color:#64748b; font-weight:600;">{{ $row->branch_name }}:</span>
                        <span style="color:#0f172a; font-weight:800;">{{ number_format($row->qty, 2) }}</span>
                        @if($row->branch_id != $selectedBranchId)
                            <a href="{{ route('opening.stocks.edit', $product->id) }}?branch_id={{ $row->branch_id }}" style="margin-left:4px; font-size:11px; color:#6366f1; font-weight:700; text-decoration:underline;">Switch</a>
                        @else
                            <span class="badge bg-success" style="font-size:9.5px; padding:3px 8px; border-radius:12px;">Active</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        @endif

        {{-- Current Stock Summary Stats Bar --}}
        <div class="stock-info-bar">
            <div class="sib-item">
                <div class="sib-val">{{ number_format($currentStock, 2) }}</div>
                <div class="sib-lbl">Current Stock</div>
            </div>
            <div class="sib-item">
                <div class="sib-val">₨ {{ number_format($product->wholesale_price ?? 0, 2) }}</div>
                <div class="sib-lbl">Wholesale</div>
            </div>
            <div class="sib-item">
                <div class="sib-val">₨ {{ number_format($product->price ?? 0, 2) }}</div>
                <div class="sib-lbl">Retail</div>
            </div>
            <div class="sib-item">
                <div class="sib-val">{{ number_format($product->alert_quantity ?? 0, 2) }}</div>
                <div class="sib-lbl">Alert Qty</div>
            </div>
        </div>

        {{-- Form --}}
        <form id="editForm" method="POST" action="{{ route('opening.stocks.update', $product->id) }}">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">

            {{-- Stock & Pricing --}}
            <div class="form-section">
                <h6><i class="las la-boxes" style="font-size:18px;color:#6366f1;"></i> Stock &amp; Valuation</h6>
                <div class="input-row">
                    <div class="fg">
                        <label>New Total Stock Qty <small style="font-size:11px;color:#6366f1;font-weight:600;">(auto from locations below)</small></label>
                        <input type="number" id="new_qty" name="opening_qty" class="fi fi-num"
                               value="{{ $currentStock }}" step="0.01" min="0" required
                               readonly
                               style="background:#eef2ff;border-color:#a5b4fc;color:#4f46e5;font-weight:800;cursor:not-allowed;"
                               title="Auto-calculated from warehouse allocations below">
                        <div class="delta-info delta-zero" id="delta_info">No change from current stock</div>
                    </div>
                    <div class="fg">
                        <label>Alert Qty (Low Stock Warning)</label>
                        <input type="number" name="alert_qty" class="fi fi-num"
                               value="{{ $product->alert_quantity ?? 0 }}" step="0.01" min="0">
                    </div>
                    <div class="fg">
                        <label>Wholesale Price</label>
                        <div class="input-with-prefix">
                            <span class="prefix">₨</span>
                            <input type="number" id="wholesale_price" name="wholesale_price" class="fi fi-num"
                                   value="{{ $product->wholesale_price ?? 0 }}" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="fg">
                        <label>Retail Price</label>
                        <div class="input-with-prefix">
                            <span class="prefix">₨</span>
                            <input type="number" id="retail_price" name="retail_price" class="fi fi-num"
                                   value="{{ $product->price ?? 0 }}" step="0.01" min="0">
                        </div>
                        <div id="price_warning" style="display:none; color:#dc2626; font-size:11px; font-weight:700; margin-top:5px; background:#fff1f2; padding:6px 10px; border-radius:6px; border:1px solid #fca5a5;">
                            ⚠️ Retail price should not be less than wholesale
                        </div>
                    </div>
                </div>
            </div>

            {{-- Warehouse Allocation --}}
            <div class="form-section">
                <h6><i class="las la-map-marker" style="font-size:18px;color:#6366f1;"></i> Warehouse &amp; Branch Allocation</h6>
                <p style="font-size:12.5px;color:#64748b;margin-bottom:1rem;">
                    Specify how opening stock is allocated across locations. Saving will update stock for the active branch.
                </p>
                <input type="hidden" name="allocation_data" id="alloc_data_edit" value="[]">

                <div class="alloc-panel">
                    <div class="alloc-grid-head">
                        <span>Target Location</span>
                        <span style="text-align:right;">Allocated Qty</span>
                        <span></span>
                    </div>
                    <div id="alloc_edit_rows">

                        {{-- Pre-fill: show rows where quantity > 0 --}}
                        @php $hasAllocations = false; @endphp
                        @foreach($currentAllocs as $alloc)
                            @if($alloc->quantity > 0)
                                @php $hasAllocations = true; @endphp
                                <div class="alloc-grid-row">
                                    <select class="alloc-type-sel" onchange="updateEditAllocStatus()">
                                        <option value="shop" {{ is_null($alloc->warehouse_id) ? 'selected' : '' }}>
                                            🏪 Branch / Shop Stock
                                        </option>
                                        @foreach($warehouses as $wh)
                                        <option value="wh_{{ $wh->id }}"
                                            {{ (string)$alloc->warehouse_id === (string)$wh->id ? 'selected' : '' }}>
                                            📦 {{ $wh->warehouse_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <input type="number" class="alloc-qty-in fi-num"
                                           value="{{ number_format((float)$alloc->quantity, 2, '.', '') }}"
                                           step="0.01" min="0"
                                           oninput="updateEditAllocStatus()">
                                    <button type="button" class="btn-del-alloc"
                                            onclick="$(this).closest('.alloc-grid-row').remove();updateEditAllocStatus();">✕</button>
                                </div>
                            @endif
                        @endforeach

                        @if(!$hasAllocations)
                        {{-- No allocation yet — show one default row with total stock --}}
                        <div class="alloc-grid-row">
                            <select class="alloc-type-sel" onchange="updateEditAllocStatus()">
                                <option value="shop" selected>🏪 Branch / Shop Stock</option>
                                @foreach($warehouses as $wh)
                                <option value="wh_{{ $wh->id }}">📦 {{ $wh->warehouse_name }}</option>
                                @endforeach
                            </select>
                            <input type="number" class="alloc-qty-in fi-num"
                                   value="{{ $currentStock }}"
                                   step="0.01" min="0"
                                   oninput="updateEditAllocStatus()">
                            <button type="button" class="btn-del-alloc"
                                    onclick="$(this).closest('.alloc-grid-row').remove();updateEditAllocStatus();">✕</button>
                        </div>
                        @endif

                    </div>

                    <button type="button" class="btn-add-alloc" id="btn_add_edit_alloc">
                        <i class="las la-plus"></i> Add Location Row
                    </button>
                    <div class="alloc-status" id="edit_alloc_status"></div>
                </div>
            </div>

            {{-- Action Bar --}}
            <div class="action-bar">
                <a href="{{ route('opening.stocks.index') }}" class="btn-cancel">← Cancel</a>
                <button type="submit" class="btn-save">
                    <i class="las la-save" style="font-size:17px;"></i> Update Opening Stock
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {

    var currentStock = {{ $currentStock }};
    var warehouses   = @json(collect($warehouses)->map(fn($w) => ['id' => $w->id, 'name' => $w->warehouse_name]));

    // ── Delta Calculation ────────────────────────────────────────────────
    window.calcDelta = function() {
        var newQty = parseFloat($('#new_qty').val()) || 0;
        var delta  = newQty - currentStock;
        var el     = $('#delta_info');
        if (Math.abs(delta) < 0.001) {
            el.text('No change from current stock (' + currentStock + ')').attr('class','delta-info delta-zero');
        } else if (delta > 0) {
            el.text('▲ +' + delta.toFixed(2) + ' units will be ADDED').attr('class','delta-info delta-pos');
        } else {
            el.text('▼ ' + delta.toFixed(2) + ' units will be REMOVED').attr('class','delta-info delta-neg');
        }
    };

    // ── Allocation Status ─────────────────────────────────────────────────
    window.updateEditAllocStatus = function() {
        var total = 0;
        $('#alloc_edit_rows .alloc-qty-in').each(function() {
            total += parseFloat($(this).val()) || 0;
        });

        $('#new_qty').val(total > 0 ? total.toFixed(2) : '0.00');

        var delta = total - currentStock;
        var deltaEl = $('#delta_info');
        if (Math.abs(delta) < 0.001) {
            deltaEl.text('No change from current stock (' + currentStock + ')').attr('class','delta-info delta-zero');
        } else if (delta > 0) {
            deltaEl.text('▲ +' + delta.toFixed(2) + ' units will be ADDED').attr('class','delta-info delta-pos');
        } else {
            deltaEl.text('▼ ' + delta.toFixed(2) + ' units will be REMOVED').attr('class','delta-info delta-neg');
        }

        var rows = $('#alloc_edit_rows .alloc-grid-row').length;
        var el = $('#edit_alloc_status');
        if (rows === 0) { el.text(''); return; }
        el.text('📦 Total Allocated: ' + total.toFixed(2) + ' units').css('color', total > 0 ? '#166534' : '#92400e');
    };

    // ── Add allocation row ────────────────────────────────────────────────
    $('#btn_add_edit_alloc').on('click', function() {
        var opts = '<option value="shop">🏪 Branch / Shop Stock</option>';
        warehouses.forEach(function(w) {
            opts += '<option value="wh_' + w.id + '">📦 ' + w.name + '</option>';
        });
        var row = '<div class="alloc-grid-row">' +
            '<select class="alloc-type-sel" onchange="updateEditAllocStatus()">' + opts + '</select>' +
            '<input type="number" class="alloc-qty-in fi-num" value="" step="0.01" min="0" placeholder="0" oninput="updateEditAllocStatus()">' +
            '<button type="button" class="btn-del-alloc" onclick="$(this).closest(\'.alloc-grid-row\').remove();updateEditAllocStatus()">✕</button>' +
            '</div>';
        $('#alloc_edit_rows').append(row);
        updateEditAllocStatus();
    });

    // ── Build allocation JSON before submit ───────────────────────────────
    $('#editForm').on('submit', function() {
        var wholesale = parseFloat($('#wholesale_price').val()) || 0;
        var retail    = parseFloat($('#retail_price').val()) || 0;
        if (wholesale > 0 && retail > 0 && retail < wholesale) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Pricing',
                text: 'Retail price cannot be less than Wholesale price.',
                confirmButtonColor: '#6366f1'
            });
            return false;
        }

        var data = [];
        $('#alloc_edit_rows .alloc-grid-row').each(function() {
            var type = $(this).find('.alloc-type-sel').val();
            var qty  = parseFloat($(this).find('.alloc-qty-in').val()) || 0;
            if (type === 'shop') {
                data.push({ location_type: 'shop', quantity: qty });
            } else if (type && type.indexOf('wh_') === 0) {
                data.push({ location_type: 'warehouse', warehouse_id: type.replace('wh_',''), quantity: qty });
            }
        });
        $('#alloc_data_edit').val(JSON.stringify(data));
    });

    // ── Branch Switcher (Super Admin) ─────────────────────────────────────
    $('#branch_switcher').on('change', function() {
        var branchId = $(this).val();
        if (!branchId) return;
        var url = new URL(window.location.href);
        url.searchParams.set('branch_id', branchId);
        window.location.href = url.toString();
    });

    // ── Price Validation ────────────────────────────────────────────────
    function validatePrices() {
        var wholesale = parseFloat($('#wholesale_price').val()) || 0;
        var retail    = parseFloat($('#retail_price').val()) || 0;
        var warning   = $('#price_warning');

        if (wholesale > 0 && retail > 0 && retail < wholesale) {
            $('#wholesale_price, #retail_price').css({'border-color': '#dc2626', 'background': '#fff1f2'});
            warning.show();
            return false;
        } else {
            $('#wholesale_price, #retail_price').css({'border-color': '', 'background': ''});
            warning.hide();
            return true;
        }
    }

    $('#wholesale_price, #retail_price').on('input change', validatePrices);

    // ── Init ─────────────────────────────────────────────────────────────
    calcDelta();
    updateEditAllocStatus();
    validatePrices();
});
</script>
@else
    <div class="alert alert-danger m-4">You do not have permission to edit opening stocks.</div>
@endcan
@endsection


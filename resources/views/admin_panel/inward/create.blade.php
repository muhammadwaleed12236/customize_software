@extends('admin_panel.layout.app')

@section('content')
    <style>
        :root {
            --inw-navy:       #0f172a;
            --inw-navy-mid:   #1e293b;
            --inw-blue:       #2563eb;
            --inw-blue-light: #3b82f6;
            --inw-border:     #e2e8f0;
            --inw-bg:         #f1f5f9;
            --inw-muted:      #64748b;
        }

        .osm-wrap { font-family:'Plus Jakarta Sans',sans-serif; background:var(--inw-bg); min-height:100vh; padding:16px 20px 50px; }

        /* Top Banner */
        .inw-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #2c5282 100%);
            border-radius: 12px; padding: 14px 22px;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            margin-bottom: 14px; box-shadow: 0 4px 18px rgba(15,23,42,.18);
        }
        .inw-banner-left { display: flex; align-items: center; gap: 12px; }
        .inw-banner-icon {
            width: 40px; height: 40px; border-radius: 10px;
            background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #fcd34d; flex-shrink: 0;
        }
        .inw-banner-title { font-size: 18px; font-weight: 800; color: #fff; margin: 0; }
        .inw-banner-badge {
            background: rgba(252,211,77,.15); border: 1px solid rgba(252,211,77,.4);
            color: #fcd34d; font-size: 10px; font-weight: 800;
            padding: 2px 9px; border-radius: 20px; text-transform: uppercase;
        }
        .inw-banner-sub { font-size: 11.5px; color: rgba(255,255,255,.65); margin-top: 2px; }
        .inw-banner-actions { display: flex; align-items: center; gap: 8px; }
        .btn-inw-outline {
            background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
            color: #fff !important; padding: 7px 14px; border-radius: 7px;
            font-size: 12px; font-weight: 700; text-decoration: none !important;
            display: inline-flex; align-items: center; gap: 5px; transition: .2s;
        }
        .btn-inw-outline:hover { background: rgba(255,255,255,.2); }
        .btn-inw-submit {
            background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;
            color: #fff !important; padding: 8px 22px; border-radius: 7px;
            font-size: 12.5px; font-weight: 800; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            box-shadow: 0 3px 12px rgba(37,99,235,.35); transition: .2s;
        }
        .btn-inw-submit:hover { transform: translateY(-1px); box-shadow: 0 5px 16px rgba(37,99,235,.45); }

        /* Compact Form Card */
        .inw-fields-card {
            background: #fff; border-radius: 10px;
            border: 1px solid var(--inw-border);
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
            padding: 16px 20px; margin-bottom: 14px;
        }
        .inw-label {
            font-size: 10px; font-weight: 700; color: var(--inw-muted);
            text-transform: uppercase; letter-spacing: .04em;
            margin-bottom: 4px; display: block;
        }
        
        /* Clean white inputs (replacing gray background) */
        .fi {
            border: 1.5px solid var(--inw-border); border-radius: 7px;
            padding: 7px 10px; font-size: 12.5px; font-weight: 600;
            width: 100%; transition: .2s; background: #ffffff !important;
            color: #0f172a; outline: none;
        }
        .fi:focus {
            border-color: var(--inw-blue-light); background: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(59,130,246,.1);
        }

        /* Clean Select2 dropdown styling */
        .select2-container--default .select2-selection--single {
            border: 1.5px solid var(--inw-border) !important;
            border-radius: 7px !important; height: 36px !important;
            background: #ffffff !important; display: flex !important; align-items: center !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px !important; font-size: 12.5px !important;
            font-weight: 600 !important; color: #0f172a !important; padding-left: 10px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px !important; right: 6px !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: var(--inw-blue-light) !important;
            box-shadow: 0 0 0 3px rgba(59,130,246,.1) !important;
        }

        /* Table Card */
        .table-card {
            background: #fff; border-radius: 10px;
            border: 1px solid var(--inw-border);
            box-shadow: 0 2px 8px rgba(0,0,0,.04); overflow: hidden;
        }
        .osm-thead { background: var(--inw-navy-mid); color: #fff; }
        .osm-thead th {
            padding: 10px 12px; font-size: 10.5px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .04em; border: none; white-space: nowrap;
        }
        .osm-row td { vertical-align: middle; padding: 8px 10px; border-bottom: 1px solid #f1f5f9; background: #fff; }
        .osm-row:hover td { background: #fafcff; }

        .osm-footer {
            background: #f8fafc; border-top: 1px solid var(--inw-border);
            padding: 12px 20px; display: flex; justify-content: space-between;
            align-items: center; border-radius: 0 0 10px 10px;
        }
        .btn-add-row {
            background: #fff; border: 1.5px dashed var(--inw-blue);
            color: var(--inw-blue); border-radius: 7px; padding: 7px 18px;
            font-size: 12.5px; font-weight: 700; cursor: pointer; transition: .2s;
        }
        .btn-add-row:hover { background: #eff6ff; }
        .btn-save-all {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff; border: none; border-radius: 7px; padding: 9px 24px;
            font-size: 13px; font-weight: 800; cursor: pointer;
            box-shadow: 0 3px 12px rgba(37,99,235,.3); transition: .2s;
        }
        .btn-save-all:hover { transform: translateY(-1px); }

        .btn-del-row {
            background: #fee2e2; color: #dc2626; border: 1px solid #fecaca;
            border-radius: 6px; width: 28px; height: 28px; display: inline-flex;
            align-items: center; justify-content: center; cursor: pointer; font-size: 12px; transition: .2s;
        }
        .btn-del-row:hover { background: #dc2626; color: #fff; }

        /* Color Breakdown */
        .color-breakdown-row td { background: #fdfdfd !important; padding: 0 !important; border-bottom: 2px solid #eef2f7 !important; }
        .breakdown-container { padding: 12px 20px; background: #fafbff; border-left: 4px solid var(--inw-blue); margin: 4px 0; }
        .breakdown-title { font-size: 11px; font-weight: 800; color: #334155; text-transform: uppercase; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .breakdown-item { display: flex; gap: 8px; align-items: center; margin-bottom: 7px; }
        .breakdown-qty { width: 100px; }
        .btn-remove-color { color: #ef4444; cursor: pointer; padding: 4px; transition: 0.2s; }
        .btn-remove-color:hover { color: #b91c1c; transform: scale(1.1); }
        .qty-readonly { background-color: #f0fdf4 !important; font-weight: 800; color: #065f46; }
    </style>

    <div class="osm-wrap">

        {{-- Top Navy Banner --}}
        <div class="inw-banner">
            <div class="inw-banner-left">
                <div class="inw-banner-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div>
                    <h4 class="inw-banner-title">
                        @if($purchase)
                            Receive Purchase #{{ $purchase->id }}
                        @elseif(isset($po) && $po)
                            Receive PO #{{ $po->po_number }}
                        @else
                            Add Inward Gatepass
                        @endif
                        <span class="inw-banner-badge">ERP Standard</span>
                    </h4>
                    <div class="inw-banner-sub">
                        @if($purchase)
                            Create inward gatepass for purchase from {{ $purchase->vendor->name ?? 'N/A' }}
                        @elseif(isset($po) && $po)
                            Create inward gatepass for PO from {{ $po->vendor->name ?? 'N/A' }}
                        @else
                            Create &amp; manage inward stock entries
                        @endif
                    </div>
                </div>
            </div>
            <div class="inw-banner-actions">
                <a href="{{ route('InwardGatepass.home') }}" class="btn-inw-outline">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <button type="button" class="btn-inw-submit" onclick="$('#gatepassForm').submit()">
                    <i class="fas fa-save"></i> Save Gatepass
                </button>
            </div>
        </div>

        <div class="gp-body">
            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-2 small fw-bold border-0" style="border-left:4px solid #ef4444!important;border-radius:8px;">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success py-2 px-3 mb-2 small fw-bold border-0" style="border-left:4px solid #10b981!important;border-radius:8px;">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger py-2 px-3 mb-2 small fw-bold border-0" style="border-left:4px solid #ef4444!important;border-radius:8px;">{{ session('error') }}</div>
            @endif

            <form action="{{ route('store.InwardGatepass') }}" method="POST" id="gatepassForm">
                @csrf

                {{-- Hidden purchase_id if from purchase --}}
                @if($purchase)
                    <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                    <input type="hidden" id="isFromPurchase" value="1">
                @elseif(isset($po) && $po)
                    <input type="hidden" name="purchase_order_id" value="{{ $po->id }}">
                    <input type="hidden" id="isFromPurchase" value="1">
                @else
                    <input type="hidden" id="isFromPurchase" value="0">
                @endif

                {{-- ── Compact All-In-One Fields Card (No separate headers, White Inputs) ── --}}
                <div class="inw-fields-card">
                    <div class="row g-2">

                        {{-- Date --}}
                        <div class="col-6 col-md-2">
                            <label class="inw-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="gatepass_date" class="fi" value="{{ old('gatepass_date', date('Y-m-d')) }}" required>
                        </div>

                        {{-- Branch (Super Admin) --}}
                        @if($isSuperAdmin)
                            <div class="col-6 col-md-2">
                                <label class="inw-label">Branch <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select select2">
                                    <option value="">Select Branch</option>
                                    @foreach ($branches as $item)
                                        <option value="{{ $item->id }}" {{ old('branch_id', ($purchase?->branch_id ?? $po?->branch_id)) == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="branch_id" value="{{ Auth::user()->branch_id ?? 1 }}">
                        @endif

                        {{-- Warehouse --}}
                        <div class="col-6 col-md-2">
                            <label class="inw-label">Warehouse <span class="text-danger">*</span></label>
                            <select name="warehouse_id" class="form-select select2" required>
                                <option value="">Select Warehouse</option>
                                @foreach ($warehouses as $item)
                                    <option value="{{ $item->id }}" {{ old('warehouse_id', ($purchase?->warehouse_id ?? $po?->warehouse_id)) == $item->id ? 'selected' : '' }}>
                                        {{ $item->warehouse_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Vendor --}}
                        <div class="col-6 col-md-3">
                            <label class="inw-label">Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" id="vendor_id" class="form-select select2" required>
                                <option value="">Select Vendor</option>
                                @foreach ($vendors as $item)
                                    <option value="{{ $item->id }}" {{ old('vendor_id', ($purchase?->vendor_id ?? $po?->vendor_id)) == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Delivery Challan No --}}
                        <div class="col-6 col-md-3">
                            <label class="inw-label">Delivery Challan No <span class="text-muted small">(Optional)</span></label>
                            <input type="text" name="delivery_challan_no" class="fi" value="{{ old('delivery_challan_no') }}" placeholder="Challan #">
                        </div>

                        {{-- Bilty No --}}
                        <div class="col-6 col-md-2">
                            <label class="inw-label">Bilty No</label>
                            <input type="text" name="bilty_no" class="fi" value="{{ old('bilty_no') }}" placeholder="Bilty #">
                        </div>

                        {{-- Freight Charges --}}
                        <div class="col-6 col-md-2">
                            <label class="inw-label">Freight Charges (PKR)</label>
                            <input type="number" step="0.01" min="0" name="freight_charges" class="fi" value="{{ old('freight_charges', 0) }}" placeholder="0.00">
                        </div>

                        {{-- Freight Provider --}}
                        <div class="col-6 col-md-2">
                            <label class="inw-label">Freight Provider</label>
                            <select name="freight_vendor_id" class="form-select select2">
                                <option value="">Select Provider</option>
                                @foreach ($vendors as $item)
                                    <option value="{{ $item->id }}" {{ old('freight_vendor_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Transport Name --}}
                        <div class="col-6 col-md-3">
                            <label class="inw-label">Transport Name</label>
                            <input type="text" name="transport_name" class="fi" value="{{ old('transport_name', ($purchase?->note ? 'From Purchase #'.$purchase->id : '')) }}" placeholder="e.g. Bilal Goods">
                        </div>

                        {{-- Vehicle Type --}}
                        <div class="col-6 col-md-3">
                            <label class="inw-label">Vehicle Type</label>
                            <input type="text" name="vehicle_type" class="fi" value="{{ old('vehicle_type') }}" placeholder="e.g. Truck, Mazda, Van">
                        </div>

                        {{-- Vehicle No --}}
                        <div class="col-6 col-md-2">
                            <label class="inw-label">Vehicle No</label>
                            <input type="text" name="vehicle_no" class="fi" value="{{ old('vehicle_no') }}" placeholder="e.g. LES-1234">
                        </div>

                        {{-- Dispatch Date --}}
                        <div class="col-6 col-md-2">
                            <label class="inw-label">Dispatch Date</label>
                            <input type="date" name="dispatch_date" class="fi" value="{{ old('dispatch_date') }}">
                        </div>

                        {{-- Driver Name --}}
                        <div class="col-6 col-md-2">
                            <label class="inw-label">Driver Name</label>
                            <input type="text" name="driver_name" class="fi" value="{{ old('driver_name') }}" placeholder="Driver Name">
                        </div>

                        {{-- Driver Contact --}}
                        <div class="col-6 col-md-3">
                            <label class="inw-label">Driver Contact No</label>
                            <input type="text" name="driver_no" class="fi" value="{{ old('driver_no') }}" placeholder="03001234567">
                        </div>

                        {{-- Note --}}
                        <div class="col-12 col-md-3">
                            <label class="inw-label">Note / Instructions</label>
                            <input type="text" name="note" class="fi" value="{{ old('note', ($purchase ? 'Ref: Purchase #'.$purchase->id : '')) }}" placeholder="Any instructions…">
                        </div>

                    </div>
                </div>

                {{-- Delivery Type Indicator (ERP Standard) --}}
                @if($purchase)
                    @php
                        $hasPartialDelivery = $vendorRemaining && $vendorRemaining->count() > 0;
                        $totalPending = $vendorRemaining->sum('remaining_qty') ?? 0;
                    @endphp
                    
                    @if($hasPartialDelivery)
                        <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                            <i class="fa fa-hourglass-half"></i>
                            <strong>Partial Delivery Mode:</strong> 
                            This is a subsequent delivery. 
                            <strong>{{ $totalPending }} units</strong> still pending from purchase #{{ $purchase->id }}.
                            The "Received Qty" field is pre-filled with the <strong>remaining quantity</strong> from the last delivery.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @else
                        <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                            <i class="fa fa-box"></i>
                            <strong>First Delivery:</strong> 
                            Creating first inward gatepass for purchase #{{ $purchase->id }}.
                            You can modify the "Received Qty" if receiving partial quantities.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                @endif

                {{-- Items table --}}
                {{-- Items table --}}
                <div class="table-card">
                    <table class="table mb-0">
                        <thead class="osm-thead">
                            <tr>
                                <th style="min-width:300px;">Product Specification</th>
                                <th style="min-width:120px;">Item Code</th>
                                <th style="min-width:120px;">Brand</th>
                                <th style="min-width:140px;">Packing Type</th>
                                <th style="min-width:140px;">Unit/Pack</th>
                                <th class="pack-qty-col" style="min-width:90px; display:none;">Pack Qty</th>
                                <th class="item-per-piece-col" style="min-width:110px; display:none;">Item Per Piece</th>
                                <th class="loose-pcs-col" style="min-width:90px; display:none;">Loose Pcs</th>
                                <th style="min-width:120px;" class="text-center">Received Qty</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="gatepassItems">
                            <!-- Rows will be added dynamically -->
                        </tbody>
                    </table>
                    <tfoot style="background: #f8fafc;">
                    </tfoot>
                </div>
                <div class="osm-footer">
                    <button type="button" class="btn-add-row" id="addRowBtn">+ Add Product Row</button>
                    <button type="submit" class="btn-save-all">Save Inward Gatepass</button>
                </div>
                </div>
            </form>
            <!-- Modal for Add/Edit Vendor -->
<div class="modal fade" id="vendorModal">
    <div class="modal-dialog">
        <form action="{{ url('vendor/store') }}" method="POST">@csrf
            <input type="hidden" id="vendor_id" name="id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add/Edit Vendor</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <input class="form-control" name="name" id="vname" placeholder="Name" required>
                    </div>
                    <div class="mb-2">
                        <input class="form-control" name="opening_balance" id="opening_balance" placeholder="Opening Balance" required>
                    </div>
                    <div class="mb-2">
                        <input class="form-control" name="phone" id="vphone" placeholder="Phone">
                    </div>
                    <div class="mb-2">
                        <textarea class="form-control" name="address" id="vaddress" placeholder="Address"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

        </div>
    </div>
    </div>
    
@endsection

@section('js')
<script>
$(document).ready(function () {
    function checkGlobalColumns() {
        let anyCustomize = false;
        $('.packing-type').each(function() {
            if ($(this).val() === 'Customize') {
                anyCustomize = true;
                return false; // break loop
            }
        });

        if (anyCustomize) {
            $('.pack-qty-col, .item-per-piece-col, .loose-pcs-col').show();
            $('.pack-qty-cell, .item-per-piece-cell, .loose-pcs-cell').show();
        } else {
            $('.pack-qty-col, .item-per-piece-col, .loose-pcs-col').hide();
            $('.pack-qty-cell, .item-per-piece-cell, .loose-pcs-cell').hide();
        }
    }

    function syncInitialVisibility() {
        $('.osm-row').each(function() {
            var tr = $(this);
            var val = tr.find('.packing-type').val();
            if (val === 'Standard') {
                tr.find('.unit-readonly').show().prop('disabled', false);
                tr.find('.unit-select').prop('disabled', true).hide();
                tr.find('.unit-select').next('.select2-container').hide();
                tr.find('.packing-qty, .item-per-piece, .loose-piece').hide();
                tr.find('.pack-qty-cell, .item-per-piece-cell, .loose-pcs-cell').hide();
            } else if (val === 'Customize') {
                tr.find('.unit-readonly').hide().prop('disabled', true);
                tr.find('.unit-select').prop('disabled', false).show();
                tr.find('.unit-select').next('.select2-container').show();
                tr.find('.packing-qty, .item-per-piece, .loose-piece').show();
                tr.find('.pack-qty-cell, .item-per-piece-cell, .loose-pcs-cell').show();
            }
        });
        checkGlobalColumns();
    }

    // Initial check on page load
    syncInitialVisibility();

    // Clear modal fields function
    window.clearVendor = function () {
        $('#vendor_id').val('');
        $('#vname').val('');
        $('#opening_balance').val('').prop('readonly', false);
        $('#vphone').val('');
        $('#vaddress').val('');
    };

    // Edit Vendor functionality
    $('.btn-edit-vendor').click(function () {
        var row = $(this).closest('tr');
        var id = $(this).data('id');
        var name = row.find('td:eq(1)').text().trim();
        var phone = row.find('td:eq(2)').text().trim();
        var balance = row.find('td:eq(3)').text().trim();
        var address = row.find('td:eq(4)').text().trim();

        $('#vendor_id').val(id);
        $('#vname').val(name);
        $('#vphone').val(phone);
        $('#opening_balance').val(balance).prop('readonly', true);
        $('#vaddress').val(address);

        var modal = new bootstrap.Modal(document.getElementById('vendorModal'));
        modal.show();
    });

    // ── Select2 init ──────────────────────────────────────────────
    function initProductSelect2($selector) {
        $selector.select2({
            width: '100%',
            placeholder: 'Search Product...',
            allowClear: true,
            ajax: {
                url: '{{ route("search-products") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        vendor_id: $('#vendor_id').val()
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(p => ({
                            id: p.id,
                            text: p.item_name + ' (' + p.item_code + ')',
                            code: p.item_code,
                            brand: p.brand_name || '',
                            unit: p.unit_name
                        }))
                    };
                },
                cache: true
            }
        }).on('select2:opening', function(e) {
            if (!$('#vendor_id').val()) {
                e.preventDefault();
                Swal.fire('Wait!', 'Please select a vendor first to see their authorized products.', 'info');
            }
        });
    }

    initProductSelect2($('.product-select'));

    $('.select2:not(.product-select)').select2({
        width: '100%',
        placeholder: 'Select One',
        allowClear: true
    });

    // ── Append blank product row ───────────────────────────────────
        let unitOptionsHtml = '<option value="">Select Unit</option>';
        @foreach($allUnits as $u)
            unitOptionsHtml += `<option value="{{ $u->name }}">{{ $u->name }}</option>`;
        @endforeach

        // Removed redundant blank row call here to prevent duplicates


    function appendBlankRow() {
        addNewRow();
    }

    let rowIndex = 0;

    function addNewRow(preData = null) {
        const idx = rowIndex++;
        
        let initialOrdered = 0;
        let initialPending = 0;
        let initialUnitPrice = 0;
        let initialReceived = 0;

        if (preData) {
            const items = Array.isArray(preData) ? preData : [preData];
            items.forEach(item => {
                const q = parseFloat(item.qty || 0);
                const r = parseFloat(item.received_qty || 0);
                initialOrdered += q;
                initialReceived += r;
                initialPending += (q - r);
            });
            initialUnitPrice = items[0].unit_price || items[0].price || 0;
        }

        const row = `
            <tr class="osm-row" data-index="${idx}">
                <td>
                    <select name="items[${idx}][product_id]" class="form-select select2 product-select" required>
                        <option value="">Search Product...</option>
                    </select>
                    <div class="mt-2">
                        <button type="button" class="btn btn-outline-info btn-sm btn-add-breakdown" style="font-size: 10px;">
                            <i class="fa fa-palette"></i> Add Color Breakdown (Optional)
                        </button>
                    </div>
                </td>
                <td><input type="text" name="items[${idx}][item_code]" class="fi item_code" readonly></td>
                <td><input type="text" name="items[${idx}][brand]" class="fi brand" readonly></td>
                <td>
                    <select name="items[${idx}][packing_type]" class="form-select packing-type" required>
                        <option value="">Select</option>
                        <option value="Standard" ${preData ? 'selected' : ''}>Standard</option>
                        <option value="Customize">Customize</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="items[${idx}][unit]" class="fi unit-readonly" style="display:none;" value="Piece" readonly disabled>
                    <div class="unit-select-wrapper">
                        <select name="items[${idx}][unit]" class="form-select select2 unit-select" required>
                            <option value="">Select Unit</option>
                            @foreach($allUnits as $u)
                                <option value="{{ $u->name }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="pack-qty-cell" style="display:none;"><input type="number" name="items[${idx}][packing_qty]" class="fi fi-num packing-qty" placeholder="Pack Qty"></td>
                <td class="item-per-piece-cell" style="display:none;"><input type="number" name="items[${idx}][item_per_piece]" class="fi fi-num item-per-piece" placeholder="Item/Piece"></td>
                <td class="loose-pcs-cell" style="display:none;"><input type="number" name="items[${idx}][loose_piece]" class="fi fi-num loose-piece" placeholder="Loose Pcs"></td>
                <td class="d-none">
                    <input type="number" step="0.01" name="items[${idx}][unit_price]" class="fi text-end unit_price" value="${initialUnitPrice}" min="0">
                </td>
                <td>
                    <input type="hidden" name="items[${idx}][ordered_qty]" class="ordered-qty" value="${initialOrdered}">
                    <input type="hidden" class="pending-qty-for-delivery" value="${initialPending}">
                    <input type="number" name="items[${idx}][received_qty]" class="fi fi-num text-center quantity received-qty" min="0" step="1" value="${initialPending}">
                </td>
                <td class="d-none">
                    <input type="text" name="items[${idx}][line_total]" class="fi text-end line_total fw-bold" readonly value="0.00" style="background: #f8fafc;">
                </td>
                <td class="text-center"><button type="button" class="btn-del-row remove-row">✕</button></td>
            </tr>
            <tr class="color-breakdown-row d-none" id="breakdown_row_${idx}">
                <td colspan="10">
                    <div class="breakdown-container">
                        <div class="breakdown-title">
                            <i class="fa fa-layer-group"></i> Color Breakdown Details
                            <button type="button" class="btn btn-success btn-sm btn-add-color-item" data-row-index="${idx}">
                                <i class="fa fa-plus"></i> Add Color
                            </button>
                        </div>
                        <div class="breakdown-list" id="breakdown_list_${idx}">
                            <!-- Color inputs will go here -->
                        </div>
                        <div class="mt-2 text-muted small italic">
                            * Total quantity will be locked and calculated from colors automatically.
                        </div>
                    </div>
                </td>
            </tr>`;
        
        $('#gatepassItems').append(row);
        const $tr = $(`tr[data-index="${idx}"]`);
        initProductSelect2($tr.find('.product-select'));
        $tr.find('.unit-select').select2({ width: '100%' });

        if (preData) {
            const items = Array.isArray(preData) ? preData : [preData];
            const first = items[0];
            
            const $prodSelect = $tr.find('.product-select');
            const newOption = new Option(first.product.item_name + ' (' + first.product.item_code + ')', first.product_id, true, true);
            $prodSelect.append(newOption).trigger('change');
            $tr.find('.item_code').val(first.product.item_code);
            $tr.find('.brand').val(first.product.brand ? (first.product.brand.name || '') : '');
            
            const unitVal = first.unit || (first.product && first.product.unit ? first.product.unit.name : 'Piece');
            $tr.find('.unit-select').val(unitVal).trigger('change');
            $tr.find('.unit-readonly').val(unitVal);
            $tr.find('.unit_price').val(first.unit_price || first.price || 0);

            if (first.packing_type) {
                $tr.find('.packing-type').val(first.packing_type).trigger('change');
                $tr.find('.packing-qty').val(first.packing_qty);
                $tr.find('.item-per-piece').val(first.item_per_piece);
                $tr.find('.loose-piece').val(first.loose_piece);
            }

            let hasColors = false;
            items.forEach(item => {
                const pending = (item.qty || 0) - (item.received_qty || 0);
                if (item.color) {
                    hasColors = true;
                    if ($(`#breakdown_row_${idx}`).hasClass('d-none')) {
                        $(`#breakdown_row_${idx}`).removeClass('d-none');
                    }
                    addColorBreakdownItem(idx, item.color, pending);
                }
            });

            if (hasColors) {
                updateQtyLock(idx);
            }
        }

        checkGlobalColumns();
        calculateTotals();
    }

    // Toggle Breakdown
    $(document).on('click', '.btn-add-breakdown', function() {
        const idx = $(this).closest('tr').data('index');
        $(`#breakdown_row_${idx}`).toggleClass('d-none');
        
        if (!$(`#breakdown_row_${idx}`).hasClass('d-none') && $(`#breakdown_list_${idx}`).children().length === 0) {
            addColorBreakdownItem(idx);
        }
        updateQtyLock(idx);
    });

    function addColorBreakdownItem(rowIdx, colorVal = '', qtyVal = '') {
        const colorHtml = `
            <div class="breakdown-item">
                <div style="flex: 1;">
                    <select name="items[${rowIdx}][colors][]" class="form-select color-select-dynamic">
                        <option value=""></option>
                        @foreach($existingColors as $color)
                            <option value="{{ $color }}">{{ $color }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="breakdown-qty">
                    <input type="number" step="0.01" name="items[${rowIdx}][color_qtys][]" class="form-control color_qty" placeholder="Qty" value="${qtyVal}" required>
                </div>
                <div class="btn-remove-color">
                    <i class="fa fa-trash"></i>
                </div>
            </div>`;
        
        const $list = $(`#breakdown_list_${rowIdx}`);
        $list.append(colorHtml);
        
        const $newSelect = $list.find('.color-select-dynamic').last();
        $newSelect.select2({
            tags: true,
            multiple: false,
            placeholder: "Select Color",
            allowClear: true,
            width: '100%'
        });

        if (colorVal) {
            if ($newSelect.find(`option[value="${colorVal}"]`).length === 0) {
                const newOption = new Option(colorVal, colorVal, true, true);
                $newSelect.append(newOption).trigger('change');
            } else {
                $newSelect.val(colorVal).trigger('change');
            }
        }

        updateQtyLock(rowIdx);
    }

    $(document).on('click', '.btn-add-color-item', function() {
        const idx = $(this).data('row-index');
        addColorBreakdownItem(idx);
    });

    $(document).on('click', '.btn-remove-color', function() {
        const $list = $(this).closest('.breakdown-list');
        const rowIdx = $list.attr('id').split('_').pop();
        $(this).closest('.breakdown-item').remove();
        updateQtyLock(rowIdx);
    });

    $(document).on('input', '.color_qty', function() {
        const rowIdx = $(this).closest('.breakdown-list').attr('id').split('_').pop();
        updateQtyLock(rowIdx);
    });

    function updateQtyLock(rowIdx) {
        const $mainRow = $(`tr[data-index="${rowIdx}"]`);
        const $qtyInput = $mainRow.find('.received-qty');
        const $breakdownList = $(`#breakdown_list_${rowIdx}`);
        
        let sum = 0;
        let hasBreakdown = $breakdownList.children().length > 0 && !$(`#breakdown_row_${rowIdx}`).hasClass('d-none');
        
        if (hasBreakdown) {
            $breakdownList.find('.color_qty').each(function() {
                sum += parseFloat($(this).val()) || 0;
            });
            $qtyInput.val(sum).addClass('qty-readonly').prop('readonly', true);
        } else {
            $qtyInput.removeClass('qty-readonly').prop('readonly', false);
        }
    }

    // Pre-load logic with grouping
    const poItems = @json($po ? $po->items : ($purchase ? $purchase->items : []));
    const grouped = {};
    poItems.forEach(item => {
        const key = item.product_id + '_' + (item.packing_type || '') + '_' + (item.unit || '');
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push(item);
    });

    if (Object.keys(grouped).length > 0) {
        Object.values(grouped).forEach(items => {
            addNewRow(items);
        });
    } else {
        addNewRow();
    }

    $('#addRowBtn').on('click', function() {
        addNewRow();
    });

    // ── Branch-Vendor & Warehouse Dependency (For Super Admin) ────────────────
    $('select[name="branch_id"]').on('change', function() {
        var branchId = $(this).val();
        if (branchId) {
            // Fetch Vendors
            $.ajax({
                url: "{{ route('vendors-by-branch') }}",
                type: "GET",
                data: { branch_id: branchId },
                success: function(data) {
                    var $vendorSelect = $('select[name="vendor_id"]');
                    $vendorSelect.empty();
                    $vendorSelect.append('<option value="">Select One</option>');
                    $.each(data, function(key, vendor) {
                        $vendorSelect.append('<option value="' + vendor.id + '">' + vendor.customer_name + '</option>');
                    });
                    $vendorSelect.trigger('change');
                }
            });

            // Fetch Warehouses
            $.ajax({
                url: "{{ route('warehouses-by-branch') }}",
                type: "GET",
                data: { branch_id: branchId },
                success: function(data) {
                    var $warehouseSelect = $('select[name="warehouse_id"]');
                    $warehouseSelect.empty();
                    $warehouseSelect.append('<option value="">Select One</option>');
                    $.each(data, function(key, wh) {
                        $warehouseSelect.append('<option value="' + wh.id + '">' + wh.warehouse_name + '</option>');
                    });
                    $warehouseSelect.trigger('change');
                }
            });
        }
    });

    // ── Financial Calculations ─────────────────────────────────────
    function calculateTotals() {
        let grandTotal = 0;
        $('.osm-row').each(function() {
            const $tr = $(this);
            const price = parseFloat($tr.find('.unit_price').val()) || 0;
            const qty = parseFloat($tr.find('.received-qty').val()) || 0;
            const lineTotal = price * qty;
            $tr.find('.line_total').val(lineTotal.toFixed(2));
            grandTotal += lineTotal;
        });
        $('#grandTotal').text(grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2}));
    }

    $(document).on('keyup change', '.unit_price, .received-qty', function() {
        calculateTotals();
    });

    // ── Product select change ──────────────────────────────────────
    $(document).on('select2:select', '.product-select', function(e) {
        const data = e.params.data;
        const $tr  = $(this).closest('tr');

        if (data) {
            $tr.find('.item_code').val(data.code);
            $tr.find('.brand').val(data.brand);
            
            // Set price from AJAX data if available
            if (data.price) {
                $tr.find('.unit_price').val(data.price);
            }

            if (data.unit) {
                $tr.find('.unit-select').val(data.unit).trigger('change');
                $tr.find('.unit-readonly').val(data.unit);
            }
            calculateTotals();
        }
    });

    // ── Received qty validation & color coding ─────────────────────
    function calculateRemaining($tr) {
        const isFromPurchase = $('#isFromPurchase').val() === '1';
        const pendingQtyForDelivery = parseFloat($tr.find('.pending-qty-for-delivery').val()) || 0;
        const orderedQty = parseFloat($tr.find('.ordered-qty').val()) || 0;
        const maxQtyForThisDelivery = pendingQtyForDelivery > 0 ? pendingQtyForDelivery : orderedQty;

        let receivedQty = parseFloat($tr.find('.received-qty').val()) || 0;

        if (isFromPurchase && receivedQty > maxQtyForThisDelivery && maxQtyForThisDelivery > 0) {
            receivedQty = maxQtyForThisDelivery;
            $tr.find('.received-qty').val(receivedQty);
            Swal.fire({
                title: '⚠️ Cannot Exceed Available Qty',
                text: `Received qty set to maximum: ${maxQtyForThisDelivery} units`,
                icon: 'warning',
                timer: 2000,
                toast: true,
                position: 'top-end'
            });
        }

        const remaining = isFromPurchase && maxQtyForThisDelivery > 0 ? (maxQtyForThisDelivery - receivedQty) : 0;
        const $receivedInput = $tr.find('.received-qty');

        if (!isFromPurchase || maxQtyForThisDelivery === 0) {
            $receivedInput.css({ 'background': '#fff', 'border': '1.5px solid #e2e8f0' });
        } else if (remaining > 0) {
            $receivedInput.css({ 'background': '#fff9e6', 'border': '1px solid #ffc107' });
        } else if (remaining === 0) {
            $receivedInput.css({ 'background': '#e6ffe6', 'border': '1px solid #28a745' });
        } else {
            $receivedInput.css({ 'background': '#ffe0e0', 'border': '2px solid #dc3545' });
        }
        
        calculateTotals();
    }

    $(document).on('change keyup', '.received-qty', function() {
        calculateRemaining($(this).closest('tr'));
    });

    // ── Remove row ────────────────────────────────────────────────
    $(document).on('click', '.remove-row', function() {
        const idx = $(this).closest('tr').data('index');
        $(this).closest('tr').remove();
        $(`#breakdown_row_${idx}`).remove();
        checkGlobalColumns();
        calculateTotals();
    });

    // ── Submit guard ──────────────────────────────────────────────
    $('#gatepassForm').on('submit', function(e) {
        let hasProduct = false;
        $('.product-select').each(function() {
            if ($(this).val()) hasProduct = true;
        });
        if (!hasProduct) {
            e.preventDefault();
            Swal.fire('Error', 'Please add at least one product for the gatepass', 'error');
            return false;
        }
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    });

    // ── Qty Calculation from Packing ──────────────────────────────
    function calculateQtyFromPacking(tr) {
        var packingType = tr.find('.packing-type').val();
        var packQty = parseFloat(tr.find('.packing-qty').val()) || 0;
        var itemPerPiece = parseFloat(tr.find('.item-per-piece').val()) || 0;
        var loosePcs = parseFloat(tr.find('.loose-piece').val()) || 0;
        var qty = 0;

        if (packingType === 'Customize') {
            qty = (packQty * itemPerPiece) + loosePcs;
            tr.find('.received-qty').val(qty);
            calculateRemaining(tr);
        }
        calculateTotals();
    }

    $(document).on('keyup change', '.packing-qty, .item-per-piece, .loose-piece', function() {
        calculateQtyFromPacking($(this).closest('tr'));
    });

    // ── Packing Type toggle ────────────────────────────────────────
    $(document).on('change', '.packing-type', function() {
        var tr = $(this).closest('tr');
        var val = $(this).val();

        if (val === 'Customize') {
            tr.find('.pack-qty-cell, .item-per-piece-cell, .loose-pcs-cell').show();
            tr.find('.packing-qty, .item-per-piece, .loose-piece').show();
            tr.find('.unit-readonly').hide().prop('disabled', true);
            tr.find('.unit-select-wrapper').show();
            tr.find('.unit-select').prop('disabled', false);
        } else {
            tr.find('.pack-qty-cell, .item-per-piece-cell, .loose-pcs-cell').hide();
            tr.find('.packing-qty, .item-per-piece, .loose-piece').hide().val('');
            tr.find('.unit-readonly').show().prop('disabled', false).css('background', '#fff');
            tr.find('.unit-select-wrapper').hide();
            tr.find('.unit-select').prop('disabled', true);
        }
        checkGlobalColumns();
        calculateQtyFromPacking(tr);
    });
});
</script>
@endsection

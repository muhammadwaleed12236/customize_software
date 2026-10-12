@extends('admin_panel.layout.app')

@section('content')
    @can('product.edit')
        @section('css')
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic:wght@500;600;700&family=Noto+Sans+Arabic:wght@500;600;700&display=swap');

            /* Urdu Input Styling - Compact yet Legible */
            .urdu-input, #item_name_urdu, input[name="item_name_urdu"] {
                direction: rtl !important;
                font-family: 'Noto Naskh Arabic', 'Noto Sans Arabic', 'Segoe UI', 'Tahoma', sans-serif !important;
                font-size: 14.5px !important;
                font-weight: 600 !important;
                height: 34px !important;
                line-height: 1.4 !important;
                padding: 2px 10px !important;
                color: #0f172a !important;
            }
            .urdu-label {
                font-family: 'Noto Naskh Arabic', 'Noto Sans Arabic', sans-serif !important;
                font-size: 12.5px !important;
                font-weight: 700 !important;
            }

            /* Compact Scroll-Free Layout */
            .main-content-inner { background: #fff; padding: 14px 20px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
            .form-section { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-start; }
            .image-col { width: 190px; flex-shrink: 0; }
            .fields-col { flex: 1; min-width: 0; }
            
            /* Field Styling */
            .field-group { margin-bottom: 0px; flex: 1; min-width: 0; }
            .field-label { display: block; font-weight: 600; color: #334155; margin-bottom: 3px; font-size: 11.5px; line-height: 1.2; }
            .field-label i { color: #2563eb; margin-right: 4px; }
            
            .custom-input, .custom-select {
                width: 100%;
                border: 1px solid #cbd5e1 !important;
                border-radius: 6px;
                padding: 4px 10px;
                height: 34px;
                font-size: 12.5px;
                background-color: #fff;
                transition: all 0.2s;
            }
            .custom-input:focus, .custom-select:focus { border-color: #2563eb !important; outline: none; box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1); }
            
            /* Grouped Currency Input (Seamless Rs. + input) */
            .input-group-currency, .input-group {
                display: flex !important;
                flex-wrap: nowrap !important;
                align-items: stretch !important;
                width: 100% !important;
                position: relative !important;
            }
            .input-group-currency .currency-addon, .input-group > .input-group-text {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                background: #f1f5f9 !important;
                color: #334155 !important;
                font-weight: 700 !important;
                font-size: 12px !important;
                padding: 0 10px !important;
                border: 1px solid #cbd5e1 !important;
                border-right: none !important;
                border-radius: 6px 0 0 6px !important;
                white-space: nowrap !important;
                user-select: none !important;
                height: 34px !important;
                flex-shrink: 0 !important;
                box-sizing: border-box !important;
            }
            .input-group-currency .custom-input, .input-group > .custom-input {
                flex: 1 1 auto !important;
                width: 1% !important;
                min-width: 0 !important;
                border-top-left-radius: 0 !important;
                border-bottom-left-radius: 0 !important;
                height: 34px !important;
                box-sizing: border-box !important;
            }
            .input-group-currency:focus-within .currency-addon, .input-group:focus-within > .input-group-text {
                border-color: #2563eb !important;
                color: #2563eb !important;
                background: #eff6ff !important;
            }
            .input-group-currency:focus-within .custom-input, .input-group:focus-within > .custom-input {
                border-color: #2563eb !important;
            }

            /* Action Buttons (+ and Gen) */
            .btn-plus-sm, .btn-plus-inline { 
                background: #2563eb; color: #fff; border: none; width: 32px; height: 34px; 
                border-radius: 0 6px 6px 0 !important; display: flex; align-items: center; justify-content: center; 
                cursor: pointer; font-size: 14px; font-weight: bold; flex-shrink: 0; transition: 0.2s; margin: 0 !important;
            }
            .btn-plus-sm:hover, .btn-plus-inline:hover { background: #1d4ed8; }
            
            .btn-gen { 
                background: #2563eb; color: #fff; border: none; padding: 0 12px; 
                border-radius: 0 6px 6px 0 !important; height: 34px; cursor: pointer; font-size: 11.5px;
                transition: 0.2s; font-weight: 600; flex-shrink: 0;
            }
            .btn-gen:hover { background: #1d4ed8; }
            
            /* Row Layout */
            .field-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px 14px; margin-bottom: 0; }
            
            /* Image Preview Area */
            .image-box { 
                width: 100%; height: 140px; background: #f8fafc; 
                border: 1px solid #e2e8f0; border-radius: 8px; 
                position: relative; overflow: hidden; display: flex; 
                align-items: center; justify-content: center; margin-bottom: 8px;
                box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
            }
            #preview { max-width: 90%; max-height: 90%; object-fit: contain; }
            .close-img { 
                position: absolute; top: 6px; right: 6px; background: rgba(15, 23, 42, 0.7); 
                color: #fff; width: 22px; height: 22px; border-radius: 50%; 
                display: flex; align-items: center; justify-content: center; cursor: pointer; border: none;
                transition: 0.2s; font-size: 12px;
            }
            .close-img:hover { background: #ef4444; }

            /* Select2 Customization */
            .select2-container { width: 100% !important; }
            .select2-container--default .select2-selection--multiple { border: 1px solid #cbd5e1 !important; min-height: 34px; border-radius: 6px; padding: 1px 5px; }
            .select2-container--default.select2-container--focus .select2-selection--multiple { border-color: #2563eb !important; }
            .select2-container--default .select2-selection--multiple { position: relative; padding-right: 25px; }
            .select2-container--default .select2-selection--multiple::after {
                content: "\f107"; font-family: "Line Awesome Free"; font-weight: 900;
                position: absolute; top: 50%; right: 8px; transform: translateY(-50%);
                color: #888; pointer-events: none; font-size: 11px;
            }
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                margin-top: 3px !important;
                padding: 1px 6px !important;
                font-size: 11px !important;
            }

            .btn-save-main {
                background: #2563eb; color: #fff; border: none;
                border-radius: 6px; font-weight: 700; font-size: 13px; 
                cursor: pointer; transition: 0.2s; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
                display: flex; align-items: center; justify-content: center;
            }
            .btn-save-main:hover { background: #1d4ed8; }

            /* Mobile Responsiveness */
            @media (max-width: 1200px) {
                .field-row { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            }
            @media (max-width: 768px) {
                .main-content-inner { padding: 12px; }
                .form-section { flex-direction: column; gap: 14px; }
                .image-col { width: 100%; flex-shrink: 1; }
                .image-box { height: 160px; }
                .fields-col { width: 100%; min-width: 100%; }
                .field-row { grid-template-columns: 1fr; gap: 10px; }
                .btn-save-main { width: 100%; height: 38px !important; }
            }
            @media (max-width: 480px) {
                .main-content-inner { padding: 10px; }
                .field-label { font-size: 11.5px; margin-bottom: 2px; }
                .custom-input, .custom-select { font-size: 12px; height: 32px; }
                .btn-plus-inline, .btn-gen { height: 32px; width: 30px; }
            }
        </style>
        @endsection

        <div class="main-content">
            <div class="main-content-inner">
                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                    <h6 class="m-0 font-weight-bold" style="color: #1e293b; font-size: 15px;">📋 Edit Product Profile</h6>
                    <a href="{{ route('product') }}" class="btn btn-sm btn-outline-primary px-3 py-1" style="font-size: 12px;">
                        <i class="las la-arrow-left"></i> Back to List
                    </a>
                </div>

                @if (session('swal_error'))
                    <script>Swal.fire({ icon: 'error', title: 'Error', text: "{{ session('swal_error') }}" });</script>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}
                    </div>
                @endif

                <form id="productForm" action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="packing_type" value="Standard">

                    <div class="form-section">
                        <!-- Left: Image Area -->
                        <div class="image-col">
                            <div class="image-box">
                                <img id="preview" src="{{ $product->image ? asset('uploads/products/' . $product->image) : asset('assets/images/placeholder-img.png') }}" alt="Product Image">
                                <button type="button" class="close-img" id="clearImageBtn" title="Remove Image">&times;</button>
                            </div>
                            <label class="field-label">Product Image</label>
                            <input type="file" id="imageInput" name="image" class="custom-input mb-1" accept="image/*">
                            <p class="text-muted mb-0" style="font-size: 11px;">📌 PNG/JPG up to 2MB</p>
                        </div>

                        <!-- Right: Fields Area -->
                        <div class="fields-col">
                            <!-- Row 1: Branch, Category, SubCategory, Type -->
                            <div class="field-row">
                                <div class="field-group">
                                    <label class="field-label">🏢 Branch</label>
                                    @if($isSuperAdmin)
                                        <select name="branch_id" class="custom-select" required>
                                            <option value="">-- Select Branch --</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ $product->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" class="custom-input bg-light" value="{{ $product->branch?->name ?? $branches->first()?->name ?? 'Default Branch' }}" readonly>
                                        <input type="hidden" name="branch_id" value="{{ $product->branch_id ?? 1 }}">
                                    @endif
                                    <p class="text-danger mt-1 mb-0" style="font-size: 10px; line-height: 1.1;">📌 Select the branch this product belongs to</p>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Category</label>
                                    <div class="d-flex align-items-center">
                                        <select id="category-dropdown" name="category_id" class="custom-select" style="border-radius: 4px 0 0 4px !important;" required>
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-plus-inline" data-toggle="modal" data-target="#categoryModal" title="Add Category">+</button>
                                    </div>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Sub Category</label>
                                    <div class="d-flex align-items-center">
                                        <select id="subcategory-dropdown" name="sub_category_id" class="custom-select" style="border-radius: 4px 0 0 4px !important;" required>
                                            <option value="">Select Sub category</option>
                                            @foreach ($subcategories as $sub)
                                                <option value="{{ $sub->id }}" {{ $product->sub_category_id == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-plus-inline" data-toggle="modal" data-target="#subcategoryModal" title="Add Sub Category">+</button>
                                    </div>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Type</label>
                                    <div class="d-flex align-items-center">
                                        <select name="type_id" class="custom-select" style="border-radius: 4px 0 0 4px !important;">
                                            <option value="">Select Type</option>
                                            @foreach ($types as $type)
                                                <option value="{{ $type->id }}" {{ $product->type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-plus-inline" data-toggle="modal" data-target="#typeModal" title="Add Type">+</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Brand, Item Code, Barcode / SKU, Unit -->
                            <div class="field-row mt-2">
                                <div class="field-group">
                                    <label class="field-label">Brand</label>
                                    <div class="d-flex align-items-center">
                                        <select name="brand_id" class="custom-select" style="border-radius: 4px 0 0 4px !important;">
                                            <option value="">Select One</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-plus-inline" data-toggle="modal" data-target="#brandcategoryModal" title="Add Brand">+</button>
                                    </div>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">🏷️ Item Code</label>
                                    <input type="text" id="item_code" name="item_code" class="custom-input fw-bold text-primary" value="{{ $product->item_code }}" placeholder="Item Code (e.g. ITEM-0001)">
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Barcode / SKU</label>
                                    <div class="d-flex align-items-center">
                                        <input type="text" id="barcodeInput" name="barcode_path" class="custom-input" value="{{ $product->barcode_path }}" placeholder="Barcode / SKU" style="border-radius: 4px 0 0 4px !important;">
                                        <button class="btn-gen" type="button" id="generateBarcodeBtn" title="Generate Custom SKU">Gen</button>
                                    </div>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Unit</label>
                                    <div class="d-flex align-items-center">
                                        <select id="unit_select" name="unit" class="custom-select" style="border-radius: 4px 0 0 4px !important;" required>
                                            <option value="">Select Unit</option>
                                            @foreach ($units as $u)
                                                <option value="{{ $u->id }}" {{ $product->unit_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-plus-inline" data-toggle="modal" data-target="#unitModal" title="Add Unit">+</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Carton / Pack Packing Details Box (Dynamically shown when Crtn/Carton/Box unit selected) -->
                            @php
                                $isCartonPrefill = ($product->pack_type === 'Customize' || ($product->piece_per_pack ?? 0) > 0);
                            @endphp
                            <div id="packingDetailsBox" class="card p-2 px-3 my-2 shadow-none" style="{{ $isCartonPrefill ? '' : 'display: none;' }} background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="font-weight-bold text-success m-0" style="font-size: 12.5px;">
                                        <i class="fas fa-box-open mr-1"></i> 📦 Carton Packing Details (کارٹن / پیکنگ تفصیلات)
                                    </h6>
                                    <span class="badge badge-success px-2 py-1" style="font-size: 10px;">Carton Unit Enabled</span>
                                </div>
                                <div class="field-row" style="grid-template-columns: repeat(2, 1fr); gap: 10px;">
                                    <div class="field-group mb-0">
                                        <label class="field-label text-dark">Packing Type</label>
                                        <select name="packing_type" id="packing_type" class="custom-select">
                                            <option value="Customize" {{ ($product->pack_type ?? 'Customize') === 'Customize' ? 'selected' : '' }}>Carton / Custom Packing</option>
                                            <option value="Standard" {{ ($product->pack_type ?? '') === 'Standard' ? 'selected' : '' }}>Standard Unit Only</option>
                                        </select>
                                    </div>

                                    <div class="field-group mb-0">
                                        <label class="field-label text-dark">Pcs per Carton / Pack (پیکنگ کوانٹیٹی / 1 کارٹن میں پیس)</label>
                                        <input type="number" step="0.01" min="0" id="piece_per_pack" name="piece_per_pack" class="custom-input fw-bold text-success" value="{{ $product->piece_per_pack ?? '' }}" placeholder="e.g. 20 (Pcs per Crtn)">
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: Item Description, Item Name (Urdu), Model, HS Code -->
                            <div class="field-row mt-2">
                                <div class="field-group">
                                    <label class="field-label">Item Description (English)</label>
                                    <input type="text" id="product_name" name="product_name" class="custom-input" value="{{ $product->item_name }}" placeholder="Product Name" required>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Item Name (Urdu) / <span class="urdu-label">اردو نام</span></label>
                                    <input type="text" id="item_name_urdu" name="item_name_urdu" class="custom-input urdu-input" value="{{ $product->item_name_urdu ?? $product->urdu_name ?? '' }}" placeholder="مثلاً: سولر سسٹم 2 کلو واٹ">
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Model</label>
                                    <input type="text" id="model" name="model" class="custom-input" value="{{ $product->model }}" placeholder="Model No.">
                                </div>

                                <div class="field-group">
                                    <label class="field-label">HS Code</label>
                                    <input type="text" id="hs_code" name="hs_code" class="custom-input" value="{{ $product->hs_code }}" placeholder="HS Code" required>
                                </div>
                            </div>

                            <!-- Row 4: Pricing & Stock Section -->
                            <div class="card p-2 px-3 my-2 shadow-none" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px;">
                                <h6 class="font-weight-bold text-primary mb-2" style="font-size: 12.5px;"><i class="las la-tags me-1"></i> Pricing & Stock Details (قیمت اور اسٹاک)</h6>
                                <div class="field-row">
                                    <div class="field-group mb-0">
                                        <label class="field-label">Wholesale Price (ہول سیل قیمت)</label>
                                        <div class="input-group-currency">
                                            <span class="currency-addon">Rs.</span>
                                            <input type="number" step="0.01" min="0" name="wholesale_price" class="custom-input fw-bold" value="{{ $product->wholesale_price ?? 0 }}" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="field-group mb-0">
                                        <label class="field-label">Sale / Retail Price (ریٹیل قیمت)</label>
                                        <div class="input-group-currency">
                                            <span class="currency-addon">Rs.</span>
                                            <input type="number" step="0.01" min="0" name="price" class="custom-input fw-bold" value="{{ $product->price ?? 0 }}" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="field-group mb-0">
                                        <label class="field-label">Alert Qty (منیمم الرٹ)</label>
                                        <input type="number" step="1" min="0" name="alert_quantity" class="custom-input" value="{{ $product->alert_quantity ?? 0 }}" placeholder="0">
                                    </div>

                                    <div class="field-group mb-0">
                                        <label class="field-label">📦 Stock Quantity (اسٹاک کوانٹیٹی)</label>
                                        <input type="number" step="0.01" name="initial_stock" class="custom-input fw-bold text-success" value="{{ $product->current_branch_stock ?? $product->stock->qty ?? $product->initial_stock ?? 0 }}" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <!-- Row 5: Color & Inline Save Button -->
                            @php
                                $selectedColors = [];
                                if (!empty($product->color)) {
                                    if (is_array($product->color)) {
                                        $selectedColors = $product->color;
                                    } elseif (is_string($product->color)) {
                                        $decoded = json_decode($product->color, true);
                                        if (is_array($decoded)) {
                                            $selectedColors = $decoded;
                                        } else {
                                            $selectedColors = array_map('trim', explode(',', $product->color));
                                        }
                                    }
                                }
                            @endphp
                            <div class="field-row mt-2 align-items-end">
                                <div class="field-group mb-0">
                                    <label class="field-label">Color</label>
                                    <select name="color[]" id="color-select" class="custom-select" multiple="multiple">
                                        @foreach (['Black', 'White', 'Red', 'Blue', 'Silver', 'Golden'] as $col)
                                            <option value="{{ $col }}" {{ in_array($col, $selectedColors) ? 'selected' : '' }}>{{ $col }}</option>
                                        @endforeach
                                        @foreach ($selectedColors as $col)
                                            @if(!in_array($col, ['Black', 'White', 'Red', 'Blue', 'Silver', 'Golden']))
                                                <option value="{{ $col }}" selected>{{ $col }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field-group mb-0" style="grid-column: span 3;">
                                    <button type="submit" class="btn-save-main w-100" style="height: 34px; padding: 0; line-height: 34px;">
                                        <i class="fas fa-save me-2"></i> UPDATE PRODUCT
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="is_part" value="{{ $product->is_part ?? 0 }}">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modals -->
        <div id="categoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Category</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('store.category') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div id="subcategoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Subcategory</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('store.subcategory') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id" class="form-control" required>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div id="brandcategoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Brand</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('store.Brand') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="form-group"><label>Brand Name</label><input type="text" name="name" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div id="typeModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Product Type</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('store.type') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="form-group"><label>Type Name</label><input type="text" name="name" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div id="unitModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Unit</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('store.Unit') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="form-group"><label>Unit Name</label><input type="text" name="name" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>

        @section('script')
        <script>
            // 1. Image Upload Preview & Clear
            document.getElementById('imageInput').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        document.getElementById('preview').src = evt.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            document.getElementById('clearImageBtn').addEventListener('click', function() {
                document.getElementById('imageInput').value = '';
                document.getElementById('preview').src = "{{ asset('assets/images/placeholder-img.png') }}";
            });

            // 2. Barcode Generator
            document.getElementById('generateBarcodeBtn').addEventListener('click', function() {
                const randomBarcode = '100' + Math.floor(10000 + Math.random() * 90000);
                document.getElementById('barcodeInput').value = randomBarcode;
            });

            // 3. Category -> Subcategory Dependent Dropdown
            $('#category-dropdown').on('change', function () {
                const categoryId = $(this).val();
                const $sub = $('#subcategory-dropdown');
                if (categoryId) {
                    $.ajax({
                        url: `/get-subcategories/${categoryId}`,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            $sub.empty().append('<option selected disabled>Select Sub category</option>');
                            $.each(data, function (_, v) { $sub.append(`<option value="${v.id}">${v.name}</option>`); });
                        }
                    });
                } else {
                    $sub.empty().append('<option value="">Select Sub category</option>');
                }
            });

            // 3b. Dynamic Unit Change -> Toggle Carton Packing Details Box
            function checkUnitPacking() {
                var selectedText = $('#unit_select option:selected').text().toLowerCase().trim();
                var isCartonUnit = selectedText.includes('crtn') || 
                                   selectedText.includes('carton') || 
                                   selectedText.includes('box') || 
                                   selectedText.includes('pack') || 
                                   selectedText.includes('pkt') || 
                                   selectedText.includes('bundle') || 
                                   selectedText.includes('ctn');

                if (isCartonUnit) {
                    $('#packingDetailsBox').slideDown(250);
                    $('#packing_type').val('Customize');
                } else {
                    if (!$('#piece_per_pack').val() || parseFloat($('#piece_per_pack').val()) <= 0) {
                        $('#packingDetailsBox').slideUp(200);
                        $('#packing_type').val('Standard');
                    }
                }
            }

            $('#unit_select').on('change', checkUnitPacking);
            if ($('#unit_select').val()) {
                checkUnitPacking();
            }

            // 4. Initialize Select2
            $(document).ready(function () {
                $('#color-select').select2({
                    tags: true,
                    placeholder: "Select or type color(s)",
                    allowClear: true,
                    width: '100%'
                });
                $('[data-toggle="tooltip"]').tooltip();
            });

            // 5. Form Submission Validation
            $('#productForm').on('submit', function (e) {
                const name = $('#product_name').val().trim();
                const cat = $('#category-dropdown').val();
                const hsCode = $('#hs_code').val() ? $('#hs_code').val().trim() : '';
                const unit = $('#unit_select').val();
                if (!name || !cat) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'Missing Info', text: 'Product name and category are required.' });
                    return false;
                }
                if (!hsCode) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'Missing HS Code', text: 'Product cannot be stored without HS Code.' });
                    return false;
                }
                if (!unit) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'Missing Unit', text: 'Please select product unit.' });
                    return false;
                }
            });
        </script>
        @endsection
    @else
        <div class="container py-5 text-center">
            <div class="alert alert-danger">Access Denied: Product Editing is restricted.</div>
        </div>
    @endcan
@endsection

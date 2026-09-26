@extends('admin_panel.layout.app')

@section('content')
    @can('product.edit')
        @section('css')
        <style>
            /* Layout Matching Product Create Screen */
            .main-content-inner { background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
            .form-section { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; }
            .image-col { width: 240px; flex-shrink: 0; }
            .fields-col { flex: 1; min-width: 0; }
            
            /* Field Styling */
            .field-group { margin-bottom: 16px; flex: 1; min-width: 0; }
            .field-label { display: block; font-weight: 600; color: #334155; margin-bottom: 6px; font-size: 13px; }
            .field-label i { color: #2563eb; margin-right: 5px; }
            
            .custom-input, .custom-select {
                width: 100%;
                border: 1px solid #cbd5e1 !important;
                border-radius: 6px;
                padding: 6px 12px;
                height: 38px;
                font-size: 13px;
                background-color: #fff;
                transition: all 0.2s;
            }
            .custom-input:focus, .custom-select:focus { border-color: #2563eb !important; outline: none; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
            
            /* Action Buttons (+ and Gen) */
            .btn-plus-sm, .btn-plus-inline { 
                background: #2563eb; color: #fff; border: none; width: 36px; height: 38px; 
                border-radius: 0 6px 6px 0 !important; display: flex; align-items: center; justify-content: center; 
                cursor: pointer; font-size: 15px; font-weight: bold; flex-shrink: 0; transition: 0.2s; margin: 0 !important;
            }
            .btn-plus-sm:hover, .btn-plus-inline:hover { background: #1d4ed8; }
            
            .btn-gen { 
                background: #2563eb; color: #fff; border: none; padding: 0 14px; 
                border-radius: 0 6px 6px 0 !important; height: 38px; cursor: pointer; font-size: 12px;
                transition: 0.2s; font-weight: 600; flex-shrink: 0;
            }
            .btn-gen:hover { background: #1d4ed8; }
            
            /* Row Layout */
            .field-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 0; }
            
            /* Image Preview Area */
            .image-box { 
                width: 100%; height: 220px; background: #f8fafc; 
                border: 1px solid #e2e8f0; border-radius: 12px; 
                position: relative; overflow: hidden; display: flex; 
                align-items: center; justify-content: center; margin-bottom: 12px;
                box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
            }
            #preview { max-width: 90%; max-height: 90%; object-fit: contain; }
            .close-img { 
                position: absolute; top: 10px; right: 10px; background: rgba(15, 23, 42, 0.7); 
                color: #fff; width: 26px; height: 26px; border-radius: 50%; 
                display: flex; align-items: center; justify-content: center; cursor: pointer; border: none;
                transition: 0.2s; font-size: 14px;
            }
            .close-img:hover { background: #ef4444; }

            /* Select2 Customization */
            .select2-container { width: 100% !important; }
            .select2-container--default .select2-selection--multiple { border: 1px solid #cbd5e1 !important; min-height: 38px; border-radius: 6px; }
            .select2-container--default.select2-container--focus .select2-selection--multiple { border-color: #2563eb !important; }
            .select2-container--default .select2-selection--multiple { position: relative; padding-right: 30px; }
            .select2-container--default .select2-selection--multiple::after {
                content: "\f107"; font-family: "Line Awesome Free"; font-weight: 900;
                position: absolute; top: 50%; right: 10px; transform: translateY(-50%);
                color: #888; pointer-events: none;
            }

            .btn-save-main {
                background: #2563eb; color: #fff; border: none;
                border-radius: 6px; font-weight: 700; font-size: 14px; 
                cursor: pointer; transition: 0.2s; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
                display: flex; align-items: center; justify-content: center;
            }
            .btn-save-main:hover { background: #1d4ed8; }

            /* Mobile Responsiveness */
            @media (max-width: 1200px) {
                .field-row { grid-template-columns: repeat(2, 1fr); gap: 14px; }
            }
            @media (max-width: 768px) {
                .main-content-inner { padding: 15px; }
                .form-section { flex-direction: column; gap: 20px; }
                .image-col { width: 100%; flex-shrink: 1; }
                .image-box { height: 200px; }
                .fields-col { width: 100%; min-width: 100%; }
                .field-row { grid-template-columns: 1fr; gap: 12px; }
                .btn-save-main { width: 100%; height: 42px !important; }
            }
            @media (max-width: 480px) {
                .main-content-inner { padding: 10px; }
                .field-label { font-size: 12.5px; margin-bottom: 4px; }
                .custom-input, .custom-select { font-size: 12.5px; height: 36px; }
                .btn-plus-inline, .btn-gen { height: 36px; width: 34px; }
            }
        </style>
        @endsection

        <div class="main-content">
            <div class="main-content-inner">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <h5 class="m-0 font-weight-bold" style="color: #1e293b;">📋 Edit Product Profile</h5>
                    <a href="{{ route('product') }}" class="btn btn-sm btn-outline-primary px-3">
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
                            <input type="file" id="imageInput" name="image" class="custom-input mb-2" accept="image/*">
                            <p class="small text-muted mb-0">📌 PNG/JPG up to 2MB</p>
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
                                    <p class="text-danger mt-1 mb-0" style="font-size: 10.5px; line-height: 1.2;">📌 Select the branch this product belongs to</p>
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
                            <div class="field-row mt-3">
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

                            <!-- Row 3: Item Description, Item Name (Urdu), Model, HS Code -->
                            <div class="field-row mt-3">
                                <div class="field-group">
                                    <label class="field-label">Item Description (English)</label>
                                    <input type="text" id="product_name" name="product_name" class="custom-input" value="{{ $product->item_name }}" placeholder="Product Name" required>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Item Name (Urdu) / اردو نام</label>
                                    <input type="text" id="item_name_urdu" name="item_name_urdu" class="custom-input" value="{{ $product->item_name_urdu ?? $product->urdu_name ?? '' }}" placeholder="مثلاً: سولر سسٹم 2 کلو واٹ" style="direction: rtl; font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', sans-serif;">
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

                            <!-- Row 4: Color & Inline Save Button -->
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
                            <div class="field-row mt-3 align-items-end">
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
                                    <button type="submit" class="btn-save-main w-100" style="height: 40px; padding: 0; line-height: 40px;">
                                        <i class="fas fa-save me-2"></i> UPDATE PRODUCT
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="is_part" value="{{ $product->is_part ?? 0 }}">
                            <input type="hidden" name="is_assembled" value="{{ $product->is_assembled ?? 0 }}">
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

@extends('admin_panel.layout.app')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        /* Subcategory Page Modern Design System */
        .subcategory-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }
        .header-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header-icon-box {
            width: 40px;
            height: 40px;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            border: 1px solid #dbeafe;
        }
        .subcategory-page-title {
            margin: 0;
            font-weight: 700;
            color: #0f172a;
            font-size: 19px;
            display: flex;
            align-items: center;
            gap: 8px;
            line-height: 1.2;
        }
        .subcat-total-badge {
            background: #f1f5f9;
            color: #2563eb;
            font-weight: 700;
            font-size: 11.5px;
            padding: 3px 9px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            display: inline-block;
        }
        .subcategory-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            padding: 18px;
        }
        .btn-create-subcat {
            background: #2563eb;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 13.5px;
            padding: 8px 18px;
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            cursor: pointer;
            white-space: nowrap;
        }
        .btn-create-subcat:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.25);
        }

        /* DataTables Controls Desktop */
        div.dataTables_wrapper div.dataTables_length label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #475569;
            font-weight: 500;
            margin: 0;
        }
        div.dataTables_wrapper div.dataTables_length select {
            height: 36px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            font-size: 13px;
            background-color: #f8fafc;
            color: #1e293b;
            font-weight: 600;
            outline: none;
            transition: all 0.2s;
        }
        div.dataTables_wrapper div.dataTables_length select:focus {
            border-color: #2563eb;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        div.dataTables_wrapper div.dataTables_filter {
            text-align: right;
        }
        div.dataTables_wrapper div.dataTables_filter label {
            margin: 0;
            display: inline-flex;
            align-items: center;
        }
        div.dataTables_wrapper div.dataTables_filter input {
            height: 36px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            font-size: 13px;
            background-color: #f8fafc;
            outline: none;
            transition: all 0.2s;
            min-width: 220px;
            margin-left: 0;
        }
        div.dataTables_wrapper div.dataTables_filter input:focus {
            background-color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .subcategory-table th {
            background: #f8fafc !important;
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 12.5px !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: none !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 12px 14px !important;
        }
        .subcategory-table td {
            vertical-align: middle !important;
            padding: 12px 14px !important;
            color: #1e293b;
            font-size: 13.5px;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        .subcategory-table tbody tr:hover {
            background-color: #f8fafc;
        }
        .subcat-id-badge {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-family: monospace;
            display: inline-block;
        }
        .subcat-name-text {
            font-weight: 600;
            color: #0f172a;
        }
        .btn-edit-action {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            font-weight: 600;
            font-size: 12.5px;
            padding: 5px 12px;
            border-radius: 6px;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
        }
        .btn-edit-action:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        /* Modal Styling */
        .modal-subcat .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .modal-subcat .modal-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 18px;
        }
        .modal-subcat .modal-title {
            font-weight: 700;
            font-size: 15.5px;
            color: #0f172a;
            display: flex;
            align-items: center;
            margin: 0;
        }
        .modal-subcat .modal-body {
            padding: 20px 18px;
        }
        .modal-subcat .modal-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 12px 18px;
        }
        .modal-subcat .form-control {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            height: 40px;
            font-size: 13.5px;
            transition: 0.2s;
        }
        .modal-subcat .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .subcategory-table {
            width: 100% !important;
        }

        /* Mobile Responsive Card-Based Design (max-width: 768px) */
        @media (max-width: 768px) {
            .main-content {
                padding: 10px 4px !important;
            }
            .main-content-inner {
                padding: 8px 4px !important;
            }
            .container {
                padding-left: 0 !important;
                padding-right: 0 !important;
                max-width: 100% !important;
            }
            .row {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
            .col-lg-12 {
                padding-left: 4px !important;
                padding-right: 4px !important;
            }
            .subcategory-page-header {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: space-between !important;
                margin-bottom: 12px !important;
                padding: 0 !important;
                gap: 8px !important;
            }
            .header-title-wrap {
                gap: 8px !important;
            }
            .header-icon-box {
                width: 34px !important;
                height: 34px !important;
                font-size: 17px !important;
                border-radius: 8px !important;
            }
            .subcategory-page-title {
                font-size: 15.5px !important;
                gap: 6px !important;
                margin: 0 !important;
            }
            .subcat-total-badge {
                font-size: 11px !important;
                padding: 2px 7px !important;
            }
            .btn-create-subcat {
                font-size: 12px !important;
                padding: 6px 12px !important;
                height: 34px !important;
                white-space: nowrap !important;
                border-radius: 7px !important;
                box-shadow: 0 1px 3px rgba(37, 99, 235, 0.2) !important;
            }
            .subcategory-card {
                padding: 12px 10px !important;
                border-radius: 12px !important;
                background: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
            }

            /* --- Table into Clean Modern Cards on Mobile --- */
            .table-responsive {
                overflow: visible !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .subcategory-table {
                border: none !important;
                background: transparent !important;
            }
            .subcategory-table thead {
                display: none !important; /* Hide column headers in card view */
            }
            .subcategory-table tbody {
                display: flex !important;
                flex-direction: column !important;
                gap: 10px !important;
            }
            .subcategory-table tbody tr {
                display: block !important;
                background: #f8fafc !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 10px !important;
                padding: 12px 14px !important;
                box-shadow: 0 1px 2px rgba(0,0,0,0.03) !important;
                position: relative !important;
                transition: all 0.2s ease !important;
            }
            .subcategory-table tbody tr:hover {
                border-color: #cbd5e1 !important;
                box-shadow: 0 2px 6px rgba(0,0,0,0.07) !important;
            }
            .subcategory-table tbody tr td {
                display: block !important;
                border: none !important;
                padding: 0 !important;
                text-align: left !important;
                background: transparent !important;
            }
            /* Row Top: ID Badge & Parent Category Badge */
            .subcategory-table tbody tr td.col-id {
                display: inline-flex !important;
                align-items: center !important;
                margin-bottom: 6px !important;
            }
            .subcategory-table tbody tr td.col-parent {
                display: inline-flex !important;
                align-items: center !important;
                float: right !important;
                margin-bottom: 6px !important;
            }
            /* Row Middle: Sub Category Name */
            .subcategory-table tbody tr td.col-name {
                clear: both !important;
                display: block !important;
                margin-top: 4px !important;
                margin-bottom: 10px !important;
            }
            .subcat-name-text {
                font-size: 14.5px !important;
                font-weight: 700 !important;
                color: #0f172a !important;
                line-height: 1.35 !important;
            }
            .subcat-id-badge {
                font-size: 11px !important;
                font-weight: 700 !important;
                color: #475569 !important;
                background: #f1f5f9 !important;
                padding: 3px 8px !important;
                border-radius: 6px !important;
                border: 1px solid #e2e8f0 !important;
            }
            .category_name {
                font-size: 11.5px !important;
                font-weight: 600 !important;
                color: #2563eb !important;
                background: #eff6ff !important;
                border: 1px solid #bfdbfe !important;
                padding: 3px 9px !important;
                border-radius: 6px !important;
                white-space: nowrap !important;
                max-width: 150px !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }
            /* Row Bottom: Edit Action Button */
            .subcategory-table tbody tr td.col-action {
                display: block !important;
                margin-top: 8px !important;
                border-top: 1px solid #e2e8f0 !important;
                padding-top: 8px !important;
                text-align: right !important;
            }
            .btn-edit-action {
                width: 100% !important;
                height: 34px !important;
                font-size: 12.5px !important;
                font-weight: 600 !important;
                border-radius: 6px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 5px !important;
                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                color: #1e293b !important;
            }
            .btn-edit-action:hover, .btn-edit-action:active {
                background: #2563eb !important;
                color: #ffffff !important;
                border-color: #2563eb !important;
            }
            /* DataTables Mobile Controls - Compact Row */
            div.dataTables_wrapper {
                padding: 0 !important;
            }
            div.dataTables_wrapper .row:first-child {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 8px !important;
                margin-bottom: 12px !important;
                padding: 0 !important;
            }
            div.dataTables_wrapper div.dataTables_length {
                flex-shrink: 0 !important;
                margin: 0 !important;
                width: auto !important;
            }
            div.dataTables_wrapper div.dataTables_length label {
                display: inline-flex !important;
                align-items: center !important;
                gap: 4px !important;
                margin: 0 !important;
                font-size: 12px !important;
                color: #64748b !important;
            }
            div.dataTables_wrapper div.dataTables_length select {
                height: 34px !important;
                border-radius: 7px !important;
                border: 1px solid #cbd5e1 !important;
                padding: 2px 8px !important;
                font-size: 12px !important;
                background-color: #f8fafc !important;
                font-weight: 600 !important;
                color: #1e293b !important;
            }
            div.dataTables_wrapper div.dataTables_filter {
                flex-grow: 1 !important;
                margin: 0 !important;
                text-align: right !important;
                width: auto !important;
            }
            div.dataTables_wrapper div.dataTables_filter label {
                width: 100% !important;
                margin: 0 !important;
            }
            div.dataTables_wrapper div.dataTables_filter input {
                width: 100% !important;
                min-width: 0 !important;
                margin-left: 0 !important;
                height: 34px !important;
                border-radius: 7px !important;
                border: 1px solid #cbd5e1 !important;
                padding: 4px 10px !important;
                font-size: 12px !important;
                background-color: #f8fafc !important;
                transition: all 0.2s !important;
            }
            div.dataTables_wrapper div.dataTables_filter input:focus {
                background-color: #ffffff !important;
                border-color: #2563eb !important;
                outline: none !important;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
            }
            div.dataTables_wrapper div.dataTables_info,
            div.dataTables_wrapper div.dataTables_paginate {
                text-align: center !important;
                margin-top: 10px !important;
                width: 100% !important;
                font-size: 11.5px !important;
                float: none !important;
            }
            div.dataTables_wrapper div.dataTables_paginate ul.pagination {
                justify-content: center !important;
                flex-wrap: wrap !important;
                margin: 6px 0 0 0 !important;
            }
            div.dataTables_wrapper div.dataTables_paginate .page-link {
                padding: 4px 8px !important;
                font-size: 11.5px !important;
            }
            /* Mobile Modal Adjustments */
            .modal-subcat .modal-dialog {
                margin: 10px auto;
                max-width: 96%;
            }
            .modal-subcat .modal-body {
                padding: 14px 12px;
            }
            .modal-subcat .modal-footer {
                display: flex;
                flex-direction: row;
                gap: 8px;
                padding: 10px 12px;
            }
            .modal-subcat .modal-footer button {
                flex: 1;
                height: 38px;
                font-size: 12.5px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Top Page Header -->
                    <div class="subcategory-page-header">
                        <div class="header-title-wrap">
                            <div class="header-icon-box">
                                <i class="las la-sitemap"></i>
                            </div>
                            <div>
                                <h3 class="subcategory-page-title">
                                    Sub Category
                                    <span class="subcat-total-badge">Total: {{ count($subcategory) }}</span>
                                </h3>
                            </div>
                        </div>
                        <button type="button" class="btn-create-subcat" data-toggle="modal" data-target="#exampleModal" id="reset">
                            <i class="las la-plus-circle mr-1"></i> Add Sub Category
                        </button>
                    </div>

                    <!-- Subcategory List Card -->
                    <div class="subcategory-card">
                        <div class="table-responsive">
                            <table id="default-datatable" class="table subcategory-table w-100">
                                <thead class="text-center">
                                    <tr>
                                        <th class="text-center col-id" style="width: 60px;">ID</th>
                                        <th class="text-left col-name">Sub Category Name</th>
                                        <th class="text-left col-parent" style="width: 140px;">Parent Category</th>
                                        <th class="text-center col-action" style="width: 90px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($subcategory as $company)
                                    <tr data-id="{{ $company->id }}" data-category-id="{{ $company->category_id }}">
                                        <td class="text-center col-id">
                                            <span class="subcat-id-badge id">{{ $company->id }}</span>
                                        </td>
                                        <td class="text-left col-name">
                                            <span class="subcat-name-text name">{{ $company->name }}</span>
                                        </td>
                                        <td class="text-left col-parent">
                                            <span class="badge badge-light border text-primary font-weight-bold px-2 py-1 category_name" style="font-size: 12px; border-radius: 6px;">
                                                <i class="las la-folder mr-1 text-muted d-none d-sm-inline"></i>{{ $company->category->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-center col-action">
                                            <button type="button" class="btn-edit-action edit-btn"
                                                data-id="{{ $company->id }}"
                                                data-name="{{ $company->name }}"
                                                data-category-id="{{ $company->category_id }}"
                                                data-url="{{ route('store.subcategory') }}"
                                                title="Edit Subcategory">
                                                <i class="las la-edit mr-1"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subcategory Add / Edit Modal -->
<div class="modal fade modal-subcat" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="myform" action="{{ route('store.subcategory') }}" method="POST">
                @csrf
                <input type="hidden" name="edit_id" id="id" />
                
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="las la-folder-plus text-primary mr-1" id="modalHeaderIcon" style="font-size: 19px;"></i> 
                        <span id="modalHeaderText">Add Sub Category</span>
                    </h5>
                    <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close" style="border: none; background: transparent; font-size: 22px; cursor: pointer; outline: none; margin: -10px -5px -10px auto;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="category_id" class="font-weight-bold text-dark mb-1" style="font-size: 13px;">
                            Parent Category <span class="text-danger">*</span>
                        </label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <option value="">-- Select Category --</option>
                            @foreach ($category as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label for="name" class="font-weight-bold text-dark mb-1" style="font-size: 13px;">
                            Sub Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" id="name" placeholder="Enter sub category name..." required autocomplete="off">
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                            مثال: Mono Perc Solar Panels, Hybrid Inverters وغیرہ
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" style="font-weight: 600; border-radius: 6px;">
                        <i class="las la-times mr-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary font-weight-bold save-btn shadow-sm" id="modalSubmitBtn" style="border-radius: 6px;">
                        <i class="las la-save mr-1"></i> <span id="submitBtnText">Save Sub Category</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
    <!-- DataTable JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="{{ asset('assets/js/mycode.js') }}"></script>
    <script>
        $(document).on('submit', '.myform', function(e) {
            e.preventDefault();
            var formdata = new FormData(this);
            var url = $(this).attr('action');
            var method = $(this).attr('method');
            $(this).find(':submit').prop('disabled', true);
            myAjax(url, formdata, method);
        });

        // Reset for Create modal
        $(document).on('click', '#reset', function() {
            $('.myform')[0].reset();
            $('#id').val('');
            $('#name').val('');
            $('#category_id').val('');
            $('#modalHeaderIcon').attr('class', 'las la-folder-plus text-primary mr-1');
            $('#modalHeaderText').text('Add Sub Category');
            $('#submitBtnText').text('Save Sub Category');
            $('#modalSubmitBtn').prop('disabled', false);
        });

        // Populate for Edit modal
        $(document).on('click', '.edit-btn', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var category_id = $(this).data('category-id');

            // Fallback from row text if data attr missing
            if (!id) {
                var tr = $(this).closest("tr");
                id = tr.find(".id").text().trim();
                name = tr.find(".name").text().trim();
                category_id = tr.data('category-id');
            }

            $('#id').val(id);
            $('#name').val(name);
            $('#category_id').val(category_id);
            $('#modalHeaderIcon').attr('class', 'las la-edit text-primary mr-1');
            $('#modalHeaderText').text('Edit Sub Category');
            $('#submitBtnText').text('Update Sub Category');
            $('#modalSubmitBtn').prop('disabled', false);
            $("#exampleModal").modal("show");
        });

        // Focus input automatically on modal open
        $('#exampleModal').on('shown.bs.modal', function () {
            $('#name').trigger('focus');
        });

        // Initialize DataTable
        $(document).ready(function() {
            $('#default-datatable').DataTable({
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50, 100],
                "order": [
                    [0, 'desc']
                ],
                "language": {
                    "search": "",
                    "searchPlaceholder": "🔍 Search Sub Category...",
                    "lengthMenu": "Show _MENU_ entries"
                }
            });
        });
    </script>
@endsection


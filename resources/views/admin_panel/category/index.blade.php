@extends('admin_panel.layout.app')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        /* Category Page Modern Design System */
        .category-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .category-page-title {
            margin: 0;
            font-weight: 700;
            color: #1e293b;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .category-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            padding: 18px;
        }
        .category-table th {
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
        .category-table td {
            vertical-align: middle !important;
            padding: 12px 14px !important;
            color: #1e293b;
            font-size: 13.5px;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        .category-table tbody tr:hover {
            background-color: #f8fafc;
        }
        .cat-id-badge {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-family: monospace;
            display: inline-block;
        }
        .cat-name-text {
            font-weight: 600;
            color: #0f172a;
        }
        .btn-create-category {
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
        }
        .btn-create-category:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.25);
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
        .modal-category .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .modal-category .modal-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 18px;
        }
        .modal-category .modal-title {
            font-weight: 700;
            font-size: 15.5px;
            color: #0f172a;
            display: flex;
            align-items: center;
            margin: 0;
        }
        .modal-category .modal-body {
            padding: 20px 18px;
        }
        .modal-category .modal-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 12px 18px;
        }
        .modal-category .form-control {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            height: 40px;
            font-size: 13.5px;
            transition: 0.2s;
        }
        .modal-category .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        /* Mobile Responsive Optimizations */
        @media (max-width: 768px) {
            .main-content {
                padding: 10px 4px !important;
            }
            .main-content-inner {
                padding: 8px 4px !important;
            }
            .container {
                padding-left: 2px !important;
                padding-right: 2px !important;
                max-width: 100% !important;
            }
            .category-page-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 12px;
                padding: 0 4px;
            }
            .category-page-title {
                font-size: 16px !important;
            }
            .btn-create-category {
                font-size: 12.5px;
                padding: 6px 12px;
                height: 36px;
                white-space: nowrap;
            }
            .category-card {
                padding: 10px 6px;
                border-radius: 10px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            }
            .table-responsive {
                margin: 4px 0 !important;
                border: none;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .category-table th {
                font-size: 11px !important;
                padding: 8px 6px !important;
                white-space: nowrap;
            }
            .category-table td {
                font-size: 12.5px !important;
                padding: 9px 6px !important;
                white-space: normal;
                word-break: break-word;
            }
            .cat-id-badge {
                font-size: 11px;
                padding: 2px 6px;
            }
            .btn-edit-action {
                padding: 5px 8px;
                font-size: 11.5px;
                min-height: 30px;
                white-space: nowrap;
            }
            /* DataTables Mobile Controls */
            div.dataTables_wrapper {
                padding: 0 !important;
            }
            div.dataTables_wrapper div.dataTables_filter {
                text-align: left !important;
                margin-top: 4px;
                width: 100%;
                float: none !important;
            }
            div.dataTables_wrapper div.dataTables_filter label {
                width: 100%;
                margin-bottom: 6px;
                font-size: 12px;
                display: block;
            }
            div.dataTables_wrapper div.dataTables_filter input {
                width: 100% !important;
                margin-left: 0 !important;
                height: 36px;
                border-radius: 6px;
                border: 1px solid #cbd5e1;
                padding: 6px 10px;
                font-size: 13px;
                display: block;
            }
            div.dataTables_wrapper div.dataTables_length {
                text-align: left !important;
                float: none !important;
                margin-bottom: 6px;
            }
            div.dataTables_wrapper div.dataTables_length select {
                height: 30px;
                border-radius: 6px;
                font-size: 12px;
                display: inline-block;
                width: auto;
            }
            div.dataTables_wrapper div.dataTables_info,
            div.dataTables_wrapper div.dataTables_paginate {
                text-align: center !important;
                margin-top: 10px !important;
                width: 100%;
                font-size: 11.5px;
                float: none !important;
            }
            div.dataTables_wrapper div.dataTables_paginate ul.pagination {
                justify-content: center !important;
                flex-wrap: wrap;
                margin: 6px 0 0 0 !important;
            }
            div.dataTables_wrapper div.dataTables_paginate .page-link {
                padding: 4px 8px !important;
                font-size: 11.5px !important;
            }
            /* Mobile Modal Adjustments */
            .modal-category .modal-dialog {
                margin: 10px auto;
                max-width: 96%;
            }
            .modal-category .modal-body {
                padding: 14px 12px;
            }
            .modal-category .modal-footer {
                display: flex;
                flex-direction: row;
                gap: 8px;
                padding: 10px 12px;
            }
            .modal-category .modal-footer button {
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
                    <div class="category-page-header">
                        <div class="d-flex align-items-center gap-2">
                            <h3 class="category-page-title">
                                <i class="las la-tags text-primary"></i> Category
                            </h3>
                            <span class="badge badge-light border text-primary font-weight-bold ml-2" style="font-size: 11.5px; border-radius: 20px; padding: 4px 8px;">
                                Total: {{ count($category) }}
                            </span>
                        </div>
                        <button type="button" class="btn-create-category" data-toggle="modal" data-target="#exampleModal" id="reset">
                            <i class="las la-plus-circle mr-1" style="font-size: 16px;"></i> Create Category
                        </button>
                    </div>

                    <!-- Category List Card -->
                    <div class="category-card">
                        <div class="table-responsive">
                            <table id="default-datatable" class="table category-table w-100">
                                <thead class="text-center">
                                    <tr>
                                        <th class="text-center" style="width: 70px;">ID</th>
                                        <th class="text-left">Category Name</th>
                                        <th class="text-center" style="width: 110px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($category as $company)
                                    <tr>
                                        <td class="text-center">
                                            <span class="cat-id-badge id">{{ $company->id }}</span>
                                        </td>
                                        <td class="text-left">
                                            <span class="cat-name-text name">{{ $company->name }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn-edit-action edit-btn"
                                                data-url="{{ route('store.category') }}"
                                                title="Edit Category">
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

<!-- Category Add / Edit Modal -->
<div class="modal fade modal-category" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="myform" action="{{ route('store.categorybypage') }}" method="POST">
                @csrf
                <input type="hidden" name="edit_id" id="id" />
                
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="las la-folder-plus text-primary mr-1" id="modalHeaderIcon" style="font-size: 19px;"></i> 
                        <span id="modalHeaderText">Add Category</span>
                    </h5>
                    <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close" style="border: none; background: transparent; font-size: 22px; cursor: pointer; outline: none; margin: -10px -5px -10px auto;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label for="name" class="font-weight-bold text-dark mb-1" style="font-size: 13px;">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" id="name" placeholder="Enter category title..." required autocomplete="off">
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                            مثال: Solar Panels, Inverters, Cables وغیرہ
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" style="font-weight: 600; border-radius: 6px;">
                        <i class="las la-times mr-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary font-weight-bold save-btn shadow-sm" id="modalSubmitBtn" style="border-radius: 6px;">
                        <i class="las la-save mr-1"></i> <span id="submitBtnText">Save Category</span>
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
            $('#modalHeaderIcon').attr('class', 'las la-folder-plus text-primary mr-1');
            $('#modalHeaderText').text('Add Category');
            $('#submitBtnText').text('Save Category');
            $('#modalSubmitBtn').prop('disabled', false);
        });

        // Populate for Edit modal
        $(document).on('click', '.edit-btn', function() {
            var tr = $(this).closest("tr");
            var id = tr.find(".id").text().trim();
            var name = tr.find(".name").text().trim();
            $('#id').val(id);
            $('#name').val(name);
            $('#modalHeaderIcon').attr('class', 'las la-edit text-primary mr-1');
            $('#modalHeaderText').text('Edit Category');
            $('#submitBtnText').text('Update Category');
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
                    "searchPlaceholder": "🔍 Search Category...",
                    "lengthMenu": "Show _MENU_ entries"
                }
            });
        });
    </script>
@endsection
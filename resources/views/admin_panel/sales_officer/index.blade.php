@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid py-3">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="font-weight-bold text-dark mb-0">
                            <i class="fas fa-user-tie text-primary mr-2"></i>Sales Officers (Salesmen)
                        </h3>
                        @can('sales.officer.create')
                        <button type="button" class="btn btn-primary font-weight-bold shadow-sm" 
                                data-toggle="modal" data-target="#createModal" 
                                data-bs-toggle="modal" data-bs-target="#createModal" 
                                id="btnOpenCreate">
                            <i class="fas fa-plus-circle mr-1"></i> Add Sales Officer
                        </button>
                        @endcan
                    </div>

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show font-weight-bold" role="alert">
                        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show font-weight-bold" role="alert">
                        <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    <div class="card shadow-sm border-0 rounded-lg">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="salesOfficerTable" class="table table-bordered table-hover w-100">
                                    <thead class="thead-light text-center">
                                        <tr>
                                            <th style="width: 80px;">ID</th>
                                            <th>Name</th>
                                            <th>نام (Urdu)</th>
                                            <th>Mobile</th>
                                            <th>Branch</th>
                                            <th style="width: 140px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        @foreach ($officers as $officer)
                                            <tr id="row-{{ $officer->id }}">
                                                <td class="font-weight-bold text-muted">{{ $officer->id }}</td>
                                                <td class="font-weight-bold text-dark text-left">{{ $officer->name }}</td>
                                                <td class="text-right" dir="rtl">{{ $officer->name_urdu ?? '-' }}</td>
                                                <td><span class="badge badge-info px-2 py-1 font-monospace">{{ $officer->mobile }}</span></td>
                                                <td>
                                                    <span class="badge badge-light border">
                                                        <i class="fas fa-store mr-1 text-secondary"></i>{{ $officer->branch->name ?? 'Default Branch' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @can('sales.officer.edit')
                                                    <button class="btn btn-sm btn-outline-primary edit-btn"
                                                            data-id="{{ $officer->id }}"
                                                            title="Edit Sales Officer">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    @endcan
                                                    @can('sales.officer.delete')
                                                    <button class="btn btn-sm btn-outline-danger delete-btn"
                                                            data-id="{{ $officer->id }}"
                                                            title="Delete Sales Officer">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                    @endcan
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
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="create-form w-100" id="createOfficerForm" action="{{ route('sales-officer.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold text-white mb-0" id="createModalLabel">
                        <i class="fas fa-user-plus mr-1"></i> Add Sales Officer
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Enter officer name" required />
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small">نام (Urdu Name - Optional)</label>
                        <input type="text" name="name_urdu" class="form-control text-right" dir="rtl" placeholder="اردو نام درج کریں" />
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small">Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" class="form-control" placeholder="e.g. 03001234567" required />
                    </div>
                    @if(isset($branches) && count($branches) > 1 && !empty($isSuperAdmin))
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark small">Assigned Branch</label>
                        <select name="branch_id" class="form-control">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold" id="btnSaveOfficer">
                        <i class="fas fa-save mr-1"></i> Save Sales Officer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="edit-form w-100" id="editOfficerForm" action="{{ route('sales-officer.store') }}" method="POST">
            @csrf
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold text-white mb-0" id="editModalLabel">
                        <i class="fas fa-user-edit mr-1"></i> Edit Sales Officer
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required />
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small">نام (Urdu Name - Optional)</label>
                        <input type="text" name="name_urdu" id="edit_name_urdu" class="form-control text-right" dir="rtl" />
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small">Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" id="edit_mobile" class="form-control" required />
                    </div>
                    @if(isset($branches) && count($branches) > 1 && !empty($isSuperAdmin))
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark small">Assigned Branch</label>
                        <select name="branch_id" id="edit_branch_id" class="form-control">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold" id="btnUpdateOfficer">
                        <i class="fas fa-check mr-1"></i> Update Sales Officer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function () {
        // Initialize DataTable
        if ($.fn.DataTable && $('#salesOfficerTable').length) {
            $('#salesOfficerTable').DataTable({
                pageLength: 25,
                order: [[0, 'desc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search sales officers..."
                }
            });
        }

        // Open create modal manually if data-toggle hasn't triggered
        $(document).on('click', '#btnOpenCreate', function(e) {
            e.preventDefault();
            $('#createOfficerForm')[0].reset();
            $('#createModal').modal('show');
        });

        // CREATE FORM AJAX SUBMIT
        $('#createOfficerForm').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $submitBtn = $('#btnSaveOfficer');
            var originalText = $submitBtn.html();

            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

            var formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: $form.attr('action'),
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    $('#createModal').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Success!',
                            text: res.message || 'Sales Officer created successfully.',
                            icon: 'success',
                            confirmButtonColor: '#1e3a5f'
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        alert(res.message || 'Sales Officer created successfully.');
                        location.reload();
                    }
                },
                error: function (xhr) {
                    $submitBtn.prop('disabled', false).html(originalText);
                    var errorMsg = 'Failed to create sales officer.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        errorMsg = Object.values(errors).flat().join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Error!',
                            html: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#1e3a5f'
                        });
                    } else {
                        alert(errorMsg);
                    }
                }
            });
        });

        // LOAD EDIT DATA
        $(document).on('click', '.edit-btn', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var url = "{{ url('sales-officers/edit') }}/" + id;

            $.get(url, function (data) {
                $('#edit_id').val(data.id);
                $('#edit_name').val(data.name);
                $('#edit_name_urdu').val(data.name_urdu || '');
                $('#edit_mobile').val(data.mobile);
                if (data.branch_id) {
                    $('#edit_branch_id').val(data.branch_id);
                }
                $('#editModal').modal('show');
            }).fail(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Unable to fetch sales officer data.', 'error');
                } else {
                    alert('Unable to fetch sales officer data.');
                }
            });
        });

        // UPDATE FORM AJAX SUBMIT
        $('#editOfficerForm').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $submitBtn = $('#btnUpdateOfficer');
            var originalText = $submitBtn.html();

            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');

            var formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: $form.attr('action'),
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    $('#editModal').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Updated!',
                            text: res.message || 'Sales Officer updated successfully.',
                            icon: 'success',
                            confirmButtonColor: '#1e3a5f'
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        alert(res.message || 'Sales Officer updated successfully.');
                        location.reload();
                    }
                },
                error: function (xhr) {
                    $submitBtn.prop('disabled', false).html(originalText);
                    var errorMsg = 'Failed to update sales officer.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        errorMsg = Object.values(errors).flat().join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Error!',
                            html: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#1e3a5f'
                        });
                    } else {
                        alert(errorMsg);
                    }
                }
            });
        });

        // DELETE
        $(document).on('click', '.delete-btn', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var url = "{{ url('sales-officers') }}/" + id;

            var performDelete = function () {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#row-' + id).fadeOut(300, function () { $(this).remove(); });
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Deleted!', 'Sales Officer has been deleted.', 'success');
                        } else {
                            alert('Sales Officer deleted.');
                        }
                    },
                    error: function (xhr) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', 'Delete failed. Please try again.', 'error');
                        } else {
                            alert('Delete failed.');
                        }
                    }
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete this sales officer?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        performDelete();
                    }
                });
            } else {
                if (confirm('Are you sure you want to delete this sales officer?')) {
                    performDelete();
                }
            }
        });
    });
</script>
@endsection
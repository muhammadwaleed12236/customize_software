@extends('admin_panel.layout.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Naskh+Arabic:wght@500;600;700&display=swap');

    .bulk-assign-wrap {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .urdu-font {
        font-family: 'Noto Naskh Arabic', 'Segoe UI', Tahoma, sans-serif;
        direction: rtl;
    }

    .main-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .card-header-custom {
        padding: 18px 24px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .header-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-title .icon-box {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .header-title h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .header-title small {
        color: #64748b;
        font-size: 12.5px;
    }

    /* Filter Card */
    .filter-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
    }

    .filter-label {
        font-size: 11.5px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        display: block;
    }

    .form-control-custom, .form-select-custom {
        height: 38px;
        border-radius: 8px;
        border: 1.5px solid #cbd5e1;
        font-size: 13px;
        padding: 6px 12px;
        width: 100%;
        background-color: #ffffff;
        transition: all 0.2s;
    }

    .form-control-custom:focus, .form-select-custom:focus {
        border-color: #2563eb;
        outline: none;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    /* Action Control Box (Sticky) */
    .action-control-box {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        border-radius: 12px;
        padding: 18px 22px;
        margin-bottom: 20px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
    }

    .branch-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.1);
        border: 1.5px solid rgba(255, 255, 255, 0.2);
        color: #ffffff;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s;
    }

    .branch-pill:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .branch-pill input[type="checkbox"] {
        accent-color: #38bdf8;
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .branch-pill.active {
        background: #2563eb;
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
    }

    /* Table Styles */
    .table-custom {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-custom th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border-bottom: 1.5px solid #e2e8f0;
        white-space: nowrap;
    }

    .table-custom td {
        padding: 12px 16px;
        vertical-align: middle;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
    }

    .table-custom tbody tr {
        transition: background-color 0.15s;
    }

    .table-custom tbody tr:hover {
        background-color: #f8fafc;
    }

    .table-custom tbody tr.selected-row {
        background-color: #eff6ff !important;
    }

    .product-thumb {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .badge-branch {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 600;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        margin: 2px 3px 2px 0;
    }

    .badge-owner {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }

    .select-all-box {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
    }
</style>

<div class="bulk-assign-wrap container-fluid py-3">

    {{-- ══════════════ CARD: HEADER & BREADCRUMB ══════════════ --}}
    <div class="main-card">
        <div class="card-header-custom">
            <div class="header-title">
                <div class="icon-box">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h4>Bulk Product Branch Assigning</h4>
                    <small>Select multiple products and assign/share permissions with other branches in 1-click</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('product') }}" class="btn btn-outline-secondary btn-sm" style="font-weight: 600; border-radius: 8px; padding: 7px 14px;">
                    <i class="fas fa-arrow-left me-1"></i> Back to Products
                </a>
            </div>
        </div>

        <div style="padding: 20px 24px;">

            {{-- ══════════════ FILTER BAR ══════════════ --}}
            <form method="GET" action="{{ route('products.bulk_assign') }}" class="filter-card">
                <div class="row g-3 align-items-end">
                    
                    {{-- Source Branch (Owner) --}}
                    @if($isSuperAdmin)
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label"><i class="fas fa-store me-1 text-primary"></i> Source Branch (Owner)</label>
                        <select name="source_branch_id" class="form-select-custom" onchange="this.form.submit()">
                            <option value="all" {{ empty($sourceBranchId) ? 'selected' : '' }}>-- All Branches --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ (string)$sourceBranchId === (string)$b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label"><i class="fas fa-store me-1 text-primary"></i> Your Branch</label>
                        <input type="text" class="form-control-custom" value="{{ Auth::user()->branch->name ?? 'Branch #' . $userBranch }}" readonly style="background: #e2e8f0; font-weight: 600;">
                    </div>
                    @endif

                    {{-- Category Filter --}}
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label"><i class="fas fa-tag me-1 text-info"></i> Category</label>
                        <select name="category_id" class="form-select-custom">
                            <option value="">-- All Categories --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Shared With Filter --}}
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label"><i class="fas fa-share-alt me-1 text-warning"></i> Shared With</label>
                        <select name="shared_with" class="form-select-custom">
                            <option value="">-- Any Branch --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ request('shared_with') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Search Text --}}
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label"><i class="fas fa-search me-1 text-secondary"></i> Search Product</label>
                        <input type="text" name="search" class="form-control-custom" placeholder="Search name, code, model..." value="{{ request('search') }}">
                    </div>

                    {{-- Filter Action Buttons --}}
                    <div class="col-lg-1 col-md-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill" style="height: 38px; border-radius: 8px; font-weight: 600;" title="Apply Filter">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="{{ route('products.bulk_assign') }}" class="btn btn-outline-secondary btn-sm" style="height: 38px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center;" title="Reset Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>

            {{-- ══════════════ ACTION BAR: TARGET BRANCHES & BULK ACTIONS ══════════════ --}}
            <div class="action-control-box">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.15);">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge" style="background: #2563eb; font-size: 13px; font-weight: 700; padding: 7px 14px; border-radius: 8px;">
                            <i class="fas fa-check-square me-1"></i> <span id="selectedCount">0</span> Products Selected
                        </span>
                        <span style="font-size: 13px; color: #94a3b8;">
                            Select products from the table below, choose target branches, and click an action.
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-success btn-sm" id="btnAssignAction" style="font-weight: 700; border-radius: 8px; padding: 8px 18px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);">
                            <i class="fas fa-plus-circle me-1"></i> Assign to Branches
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" id="btnRevokeAction" style="font-weight: 700; border-radius: 8px; padding: 8px 18px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);">
                            <i class="fas fa-minus-circle me-1"></i> Remove from Branches
                        </button>
                    </div>
                </div>

                {{-- Target Branches Selection --}}
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #cbd5e1; margin: 0;">
                            <i class="fas fa-code-branch me-1 text-primary"></i> Target Branches to Assign / Remove:
                        </label>
                        <button type="button" id="toggleAllTargetBranches" class="btn btn-link btn-sm text-info p-0 text-decoration-none" style="font-size: 12px; font-weight: 600;">
                            Select All Branches
                        </button>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        @foreach($branches as $b)
                            <label class="branch-pill" id="pill_branch_{{ $b->id }}">
                                <input type="checkbox" name="target_branches[]" value="{{ $b->id }}" class="target-branch-cb">
                                <span><i class="fas fa-building me-1 opacity-75"></i> {{ $b->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ══════════════ PRODUCTS TABLE ══════════════ --}}
            <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">
                                <input type="checkbox" id="selectAllProducts" style="width: 17px; height: 17px; accent-color: #2563eb; cursor: pointer;" title="Select all on this page">
                            </th>
                            <th style="width: 60px;">Image</th>
                            <th style="width: 140px;">Code / Model</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Owner Branch</th>
                            <th>Currently Shared With</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $prod)
                        <tr id="row_prod_{{ $prod->id }}" class="product-row" data-id="{{ $prod->id }}" style="cursor: pointer;">
                            <td style="text-align: center;" onclick="event.stopPropagation();">
                                <input type="checkbox" name="selected_products[]" value="{{ $prod->id }}" class="prod-checkbox" style="width: 17px; height: 17px; accent-color: #2563eb; cursor: pointer;">
                            </td>
                            <td>
                                @if(!empty($prod->image) && file_exists(public_path('uploads/products/' . $prod->image)))
                                    <img src="{{ asset('uploads/products/' . $prod->image) }}" class="product-thumb" alt="Product Image">
                                @else
                                    <div class="product-thumb d-flex align-items-center justify-content-center text-muted" style="font-size: 14px;">
                                        <i class="fas fa-box"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">{{ $prod->item_code ?? '-' }}</strong>
                                @if(!empty($prod->model))
                                    <br><small class="text-muted"><i class="fas fa-barcode me-1"></i>{{ $prod->model }}</small>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a; font-size: 13.5px;">
                                    {{ $prod->item_name }}
                                </div>
                                @if(!empty($prod->item_name_urdu))
                                    <div class="urdu-font text-muted" style="font-size: 13px; line-height: 1.4;">
                                        {{ $prod->item_name_urdu }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $prod->category_relation->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-owner">
                                    <i class="fas fa-building me-1"></i> {{ $prod->branch->name ?? 'Branch #' . $prod->branch_id }}
                                </span>
                            </td>
                            <td id="shared_badges_{{ $prod->id }}">
                                @if($prod->sharedBranches->isNotEmpty())
                                    @foreach($prod->sharedBranches as $sb)
                                        <span class="badge-branch" title="Shared with {{ $sb->name }}">
                                            <i class="fas fa-check text-success"></i> {{ $sb->name }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted" style="font-size: 12px; font-style: italic;">
                                        <i class="fas fa-lock me-1"></i> Only Owner Branch
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-bold">No Products Found</h6>
                                    <p class="small mb-0">Try adjusting your filters or search term.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ══════════════ PAGINATION ══════════════ --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <div class="text-muted" style="font-size: 12.5px;">
                    Showing <strong>{{ $products->firstItem() ?? 0 }}</strong> to <strong>{{ $products->lastItem() ?? 0 }}</strong> of <strong>{{ $products->total() }}</strong> products
                </div>
                <div>
                    {{ $products->links('pagination::bootstrap-4') }}
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════ SCRIPTS ══════════════ --}}
@section('scripts')
<script>
    $(document).ready(function() {

        // Checkbox multi-select update counter
        function updateSelectedCount() {
            var selected = $('.prod-checkbox:checked').length;
            $('#selectedCount').text(selected);

            var total = $('.prod-checkbox').length;
            if (total > 0 && selected === total) {
                $('#selectAllProducts').prop('checked', true).prop('indeterminate', false);
            } else if (selected > 0) {
                $('#selectAllProducts').prop('checked', false).prop('indeterminate', true);
            } else {
                $('#selectAllProducts').prop('checked', false).prop('indeterminate', false);
            }
        }

        // Toggle Select All Products on this page
        $('#selectAllProducts').on('change', function() {
            var isChecked = $(this).prop('checked');
            $('.prod-checkbox').prop('checked', isChecked);
            $('.product-row').toggleClass('selected-row', isChecked);
            updateSelectedCount();
        });

        // Individual Product Checkbox change
        $(document).on('change', '.prod-checkbox', function(e) {
            e.stopPropagation();
            var row = $(this).closest('tr');
            row.toggleClass('selected-row', $(this).prop('checked'));
            updateSelectedCount();
        });

        // Clicking Row toggles checkbox
        $(document).on('click', '.product-row', function(e) {
            if ($(e.target).is('input') || $(e.target).is('a') || $(e.target).is('button')) {
                return;
            }
            var cb = $(this).find('.prod-checkbox');
            cb.prop('checked', !cb.prop('checked')).trigger('change');
        });

        // Target Branch Pill Styling
        $(document).on('change', '.target-branch-cb', function() {
            var pill = $(this).closest('.branch-pill');
            pill.toggleClass('active', $(this).prop('checked'));
        });

        // Toggle All Target Branches
        var allBranchesSelected = false;
        $('#toggleAllTargetBranches').on('click', function() {
            allBranchesSelected = !allBranchesSelected;
            $('.target-branch-cb').prop('checked', allBranchesSelected).trigger('change');
            $(this).text(allBranchesSelected ? 'Deselect All Branches' : 'Select All Branches');
        });

        // Process Bulk Action (Assign or Revoke)
        function executeBulkAction(actionType) {
            var selectedProductIds = [];
            $('.prod-checkbox:checked').each(function() {
                selectedProductIds.push(parseInt($(this).val()));
            });

            if (selectedProductIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Products Selected',
                    text: 'Please select at least one product using the checkboxes.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            var targetBranchIds = [];
            var targetBranchNames = [];
            $('.target-branch-cb:checked').each(function() {
                targetBranchIds.push(parseInt($(this).val()));
                targetBranchNames.push($(this).closest('.branch-pill').find('span').text().trim());
            });

            if (targetBranchIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Target Branches Selected',
                    text: 'Please choose at least one branch to ' + (actionType === 'assign' ? 'assign to' : 'remove from') + '.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            var actionTitle = actionType === 'assign' ? 'Assign Permission' : 'Remove Permission';
            var actionConfirmText = actionType === 'assign' ? 'Yes, Assign Now' : 'Yes, Remove';
            var actionConfirmColor = actionType === 'assign' ? '#10b981' : '#ef4444';

            var branchesHtml = targetBranchNames.map(function(n) {
                return '<span class="badge bg-primary me-1 mb-1">' + n + '</span>';
            }).join(' ');

            Swal.fire({
                title: actionTitle + '?',
                html: '<div class="text-start">' +
                      '<p><strong>Selected Products:</strong> <span class="badge bg-dark">' + selectedProductIds.length + ' products</span></p>' +
                      '<p class="mb-1"><strong>Target Branches:</strong></p>' +
                      '<div>' + branchesHtml + '</div>' +
                      '<p class="small text-muted mt-3 mb-0">' +
                      (actionType === 'assign' 
                        ? 'These branches will be able to view and sell these products from their own stock.' 
                        : 'Access to these products will be revoked for the selected branches.') +
                      '</p>' +
                      '</div>',
                icon: actionType === 'assign' ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: actionConfirmColor,
                cancelButtonColor: '#64748b',
                confirmButtonText: actionConfirmText,
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Updating branch permissions...',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('products.bulk_assign.save') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            product_ids: selectedProductIds,
                            target_branch_ids: targetBranchIds,
                            action: actionType
                        },
                        success: function(resp) {
                            if (resp.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: resp.message,
                                    timer: 1800,
                                    showConfirmButton: false
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: resp.message || 'Something went wrong.'
                                });
                            }
                        },
                        error: function(xhr) {
                            var errMsg = 'Failed to update branch permissions.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errMsg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errMsg
                            });
                        }
                    });
                }
            });
        }

        $('#btnAssignAction').on('click', function() {
            executeBulkAction('assign');
        });

        $('#btnRevokeAction').on('click', function() {
            executeBulkAction('revoke');
        });

    });
</script>
@endsection
@endsection

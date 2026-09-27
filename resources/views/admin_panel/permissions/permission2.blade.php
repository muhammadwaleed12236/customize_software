@extends('admin_panel.layout.app')
@section('content')

<style>
    :root {
        --perm-navy: #0f1f38;
        --perm-primary: #4f46e5;
        --perm-success: #10b981;
        --perm-warning: #f59e0b;
        --perm-danger: #ef4444;
        --perm-info: #0ea5e9;
        --perm-bg: #f8fafc;
        --perm-card: #ffffff;
        --perm-border: #cbd5e1;
        --perm-text: #1e293b;
        --perm-muted: #64748b;
    }

    .main-content {
        background-color: var(--perm-bg);
        min-height: 100vh;
        padding: 16px 0 40px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .page-header-card {
        background: #ffffff;
        border: 1px solid var(--perm-border);
        border-radius: 12px;
        padding: 18px 24px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--perm-navy);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-subtitle {
        color: var(--perm-muted);
        font-size: 0.875rem;
        margin-top: 4px;
        margin-bottom: 0;
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 16px 20px;
        border: 1px solid var(--perm-border);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .stat-card .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .stat-card.primary .stat-icon { background: #eef2ff; color: var(--perm-primary); }
    .stat-card.success .stat-icon { background: #dcfce7; color: var(--perm-success); }
    .stat-card.warning .stat-icon { background: #fef3c7; color: var(--perm-warning); }
    .stat-card.info .stat-icon { background: #e0f2fe; color: var(--perm-info); }

    .stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--perm-navy);
        line-height: 1;
    }

    .stat-card .stat-label {
        font-size: 0.75rem;
        color: var(--perm-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 4px;
        font-weight: 700;
    }

    /* Controls Bar */
    .controls-card {
        background: #ffffff;
        border: 1px solid var(--perm-border);
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    .role-select-box {
        min-width: 260px;
    }

    .role-select-box select {
        height: 40px;
        border-radius: 8px;
        border: 1.5px solid var(--perm-border);
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--perm-navy);
        background-color: #ffffff;
    }

    .search-box-wrapper {
        position: relative;
        flex: 1;
        max-width: 380px;
    }

    .search-box-wrapper input {
        width: 100%;
        height: 40px;
        padding: 8px 14px 8px 40px;
        border: 1.5px solid var(--perm-border);
        border-radius: 8px;
        font-size: 0.9rem;
        background: #f8fafc;
        transition: all 0.2s;
    }

    .search-box-wrapper input:focus {
        outline: none;
        border-color: var(--perm-primary);
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .search-box-wrapper i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--perm-muted);
    }

    /* Excel Permission Matrix Table */
    .matrix-wrapper {
        background: #ffffff;
        border: 1px solid var(--perm-border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    #matrixTable {
        border-collapse: collapse !important;
        width: 100%;
        margin-bottom: 0 !important;
    }

    #matrixTable thead th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 12px 10px !important;
        border: 1px solid #1e3a5f !important;
        vertical-align: middle;
        white-space: nowrap;
    }

    #matrixTable tbody td {
        padding: 10px 12px !important;
        vertical-align: middle;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff;
        font-size: 13px;
    }

    #matrixTable tbody tr:hover td {
        background-color: #f8fafc !important;
    }

    /* Checkbox Styling */
    .perm-checkbox {
        width: 19px;
        height: 19px;
        cursor: pointer;
        accent-color: #2563eb;
        vertical-align: middle;
    }

    .perm-check-label {
        cursor: pointer;
        user-select: none;
        margin-bottom: 0;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--perm-text);
    }

    .badge-action {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 5px;
        text-transform: uppercase;
    }

    .badge-view { background: #dbeafe; color: #1e40af; }
    .badge-create { background: #dcfce7; color: #166534; }
    .badge-edit { background: #fef3c7; color: #92400e; }
    .badge-delete { background: #fee2e2; color: #991b1b; }
    .badge-other { background: #f1f5f9; color: #475569; }

    .other-perm-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f8fafc;
        border: 1px solid var(--perm-border);
        border-radius: 6px;
        padding: 4px 8px;
        margin: 2px;
        font-size: 12px;
        transition: all 0.15s;
    }

    .other-perm-pill:hover {
        background: #eef2ff;
        border-color: var(--perm-primary);
    }

    /* Modal Styling */
    .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .modal-header.gradient {
        background: linear-gradient(135deg, #0f1f38, #1e3a5f);
        color: white;
        padding: 20px 24px;
    }

    .modal-header.gradient .btn-close {
        filter: brightness(0) invert(1);
    }
</style>

<div class="main-content">
    <div class="container-fluid px-3">

        <!-- Page Header -->
        <div class="page-header-card d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="page-title">
                    <i class="fa fa-user-shield text-primary"></i> System Permission Matrix
                </h1>
                <p class="page-subtitle">Configure module permissions, action capabilities, and role access with master & single-click toggles.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary font-weight-bold" id="addPermBtn">
                    <i class="fa fa-plus-circle mr-1"></i> Add New Permission
                </button>
                <button type="button" class="btn btn-success font-weight-bold shadow-sm" id="btnSavePermissions" style="background: linear-gradient(135deg, #059669, #047857); border: none;">
                    <i class="fa fa-save mr-1"></i> SAVE ROLE PERMISSIONS
                </button>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-card primary">
                <div class="stat-icon"><i class="fa fa-key"></i></div>
                <div>
                    <div class="stat-value">{{ $permissions->count() }}</div>
                    <div class="stat-label">Total System Permissions</div>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon"><i class="fa fa-th-large"></i></div>
                <div>
                    <div class="stat-value">{{ count($permissionModules) }}</div>
                    <div class="stat-label">Configured Modules</div>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon"><i class="fa fa-user-tag"></i></div>
                <div>
                    <div class="stat-value">{{ $roles->count() }}</div>
                    <div class="stat-label">Active User Roles</div>
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                <div>
                    <div class="stat-value" id="activePermCountDisplay">0</div>
                    <div class="stat-label">Assigned To Role</div>
                </div>
            </div>
        </div>

        <!-- Controls Bar -->
        <div class="controls-card d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="role-select-box">
                    <label class="form-label small font-weight-bold text-uppercase text-muted mb-1 d-block">Selected Role to Manage</label>
                    <select id="roleSelect" class="form-select">
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" data-perms="{{ json_encode($r->permissions->pluck('name')) }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="search-box-wrapper mt-3 mt-sm-0">
                    <label class="form-label small font-weight-bold text-uppercase text-muted mb-1 d-block">Search Filter</label>
                    <div class="position-relative">
                        <i class="fa fa-search"></i>
                        <input type="search" id="matrixSearch" placeholder="Search module or action (e.g. Products, View, Edit)...">
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <span class="badge bg-light text-dark border p-2 font-weight-bold" style="font-size: 12px;">
                    <i class="fa fa-info-circle text-primary mr-1"></i> Toggle <strong>All</strong> checkbox to grant/revoke entire module.
                </span>
            </div>
        </div>

        <!-- Permission Matrix Table -->
        <form id="matrixForm">
            @csrf
            <input type="hidden" name="edit_id" id="selectedRoleId" value="{{ $roles->first()->id ?? '' }}">

            <div class="matrix-wrapper">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="matrixTable">
                        <thead>
                            <tr>
                                <th style="width: 24%;">Module / Feature</th>
                                <th style="width: 8%; text-align: center;">
                                    <label class="mb-0 cursor-pointer" title="Toggle all permissions across entire module row">
                                        <input type="checkbox" id="globalMasterCheckbox" class="perm-checkbox mb-1"><br>
                                        <span>ALL</span>
                                    </label>
                                </th>
                                <th style="width: 11%; text-align: center;">
                                    <label class="mb-0 cursor-pointer" title="Toggle VIEW for all modules">
                                        <input type="checkbox" id="colMasterView" class="perm-checkbox col-master mb-1" data-action="view"><br>
                                        <span>VIEW</span>
                                    </label>
                                </th>
                                <th style="width: 11%; text-align: center;">
                                    <label class="mb-0 cursor-pointer" title="Toggle CREATE for all modules">
                                        <input type="checkbox" id="colMasterCreate" class="perm-checkbox col-master mb-1" data-action="create"><br>
                                        <span>CREATE</span>
                                    </label>
                                </th>
                                <th style="width: 11%; text-align: center;">
                                    <label class="mb-0 cursor-pointer" title="Toggle EDIT for all modules">
                                        <input type="checkbox" id="colMasterEdit" class="perm-checkbox col-master mb-1" data-action="edit"><br>
                                        <span>EDIT</span>
                                    </label>
                                </th>
                                <th style="width: 11%; text-align: center;">
                                    <label class="mb-0 cursor-pointer" title="Toggle DELETE for all modules">
                                        <input type="checkbox" id="colMasterDelete" class="perm-checkbox col-master mb-1" data-action="delete"><br>
                                        <span>DELETE</span>
                                    </label>
                                </th>
                                <th style="width: 24%;">Other / Custom Module Actions</th>
                            </tr>
                        </thead>
                        <tbody id="matrixTableBody">
                            @foreach($permissionModules as $modKey => $modDef)
                                @php
                                    $modLabel = $modDef['label'] ?? ucfirst($modKey);
                                    $modIcon  = $modDef['icon']  ?? 'fa-folder';
                                    $modColor = $modDef['color'] ?? '#6366f1';
                                    $permsMap = $modDef['permissions'] ?? [];

                                    // Classify standard actions
                                    $viewPerm   = null;
                                    $createPerm = null;
                                    $editPerm   = null;
                                    $deletePerm = null;
                                    $otherPerms = [];

                                    foreach($permsMap as $pName => $pLabel) {
                                        $last = collect(explode('.', $pName))->last();
                                        if ($last === 'view' || $last === 'read') {
                                            $viewPerm = ['name' => $pName, 'label' => $pLabel];
                                        } elseif ($last === 'create' || $last === 'add') {
                                            $createPerm = ['name' => $pName, 'label' => $pLabel];
                                        } elseif ($last === 'edit' || $last === 'update') {
                                            $editPerm = ['name' => $pName, 'label' => $pLabel];
                                        } elseif ($last === 'delete' || $last === 'remove') {
                                            $deletePerm = ['name' => $pName, 'label' => $pLabel];
                                        } else {
                                            $otherPerms[] = ['name' => $pName, 'label' => $pLabel, 'action' => $last];
                                        }
                                    }
                                @endphp
                                <tr class="module-row" data-module="{{ $modKey }}" data-search="{{ strtolower($modLabel . ' ' . implode(' ', array_keys($permsMap))) }}">
                                    <!-- Module Title Cell -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-flex align-items-center justify-content-center text-white font-weight-bold rounded" style="width: 32px; height: 32px; background: {{ $modColor }}; flex-shrink:0;">
                                                <i class="fa {{ $modIcon }}"></i>
                                            </span>
                                            <div>
                                                <strong class="text-dark font-weight-bold" style="font-size: 13.5px;">{{ $modLabel }}</strong>
                                                <small class="text-muted d-block" style="font-size: 11px;">{{ count($permsMap) }} permissions</small>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Row ALL Checkbox -->
                                    <td class="text-center bg-light">
                                        <input type="checkbox" class="perm-checkbox row-master-all" title="Select All in {{ $modLabel }}">
                                    </td>

                                    <!-- VIEW Cell -->
                                    <td class="text-center">
                                        @if($viewPerm)
                                            <label class="perm-check-label w-100 py-1">
                                                <input type="checkbox" name="permissions[]" value="{{ $viewPerm['name'] }}" class="perm-checkbox perm-check action-view" data-module="{{ $modKey }}">
                                            </label>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>

                                    <!-- CREATE Cell -->
                                    <td class="text-center">
                                        @if($createPerm)
                                            <label class="perm-check-label w-100 py-1">
                                                <input type="checkbox" name="permissions[]" value="{{ $createPerm['name'] }}" class="perm-checkbox perm-check action-create" data-module="{{ $modKey }}">
                                            </label>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>

                                    <!-- EDIT Cell -->
                                    <td class="text-center">
                                        @if($editPerm)
                                            <label class="perm-check-label w-100 py-1">
                                                <input type="checkbox" name="permissions[]" value="{{ $editPerm['name'] }}" class="perm-checkbox perm-check action-edit" data-module="{{ $modKey }}">
                                            </label>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>

                                    <!-- DELETE Cell -->
                                    <td class="text-center">
                                        @if($deletePerm)
                                            <label class="perm-check-label w-100 py-1">
                                                <input type="checkbox" name="permissions[]" value="{{ $deletePerm['name'] }}" class="perm-checkbox perm-check action-delete" data-module="{{ $modKey }}">
                                            </label>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>

                                    <!-- OTHER ACTIONS Cell -->
                                    <td>
                                        @if(count($otherPerms) > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($otherPerms as $op)
                                                    <label class="other-perm-pill mb-0 cursor-pointer">
                                                        <input type="checkbox" name="permissions[]" value="{{ $op['name'] }}" class="perm-checkbox perm-check action-other" data-module="{{ $modKey }}">
                                                        <span>{{ $op['label'] }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>

    </div>
</div>

<!-- Add/Edit Permission Modal -->
<div class="modal fade" id="permModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header gradient">
                <h5 class="modal-title font-weight-bold mb-0">
                    <i class="fa fa-key mr-2"></i><span id="permModalTitleText">Add New Permission</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="permForm" action="{{ route('permissions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="edit_id" id="permEditId">
                <input type="hidden" name="name" id="permNameInput">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold small text-uppercase">Module Name</label>
                        <select id="moduleSelect" class="form-select" required>
                            <option value="">-- Select Module --</option>
                            @foreach($permissionModules as $mk => $mv)
                                <option value="{{ $mk }}">{{ $mv['label'] }} ({{ $mk }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold small text-uppercase">Action Name</label>
                        <select id="actionSelect" class="form-select" required>
                            <option value="">-- Select Action --</option>
                            <option value="view">View</option>
                            <option value="create">Create</option>
                            <option value="edit">Edit</option>
                            <option value="delete">Delete</option>
                            <option value="print">Print</option>
                            <option value="approve">Approve</option>
                            <option value="export">Export</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold small text-uppercase">Permission Code Preview</label>
                        <input type="text" id="permPreviewInput" class="form-control bg-light font-weight-bold" readonly placeholder="module.action">
                    </div>
                </div>

                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary font-weight-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4">
                        <i class="fa fa-check mr-1"></i> Save Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {

    // --- Load Selected Role's Assigned Permissions ---
    function loadRolePermissions() {
        var $selectedOption = $('#roleSelect').find(':selected');
        var roleId = $('#roleSelect').val();
        $('#selectedRoleId').val(roleId);

        var assignedPerms = $selectedOption.data('perms') || [];

        // Uncheck all checkboxes first
        $('.perm-check, .row-master-all, .col-master, #globalMasterCheckbox').prop('checked', false);

        // Check assigned permissions
        assignedPerms.forEach(function(permName) {
            $('.perm-check[value="' + permName + '"]').prop('checked', true);
        });

        // Update row master "ALL" checkboxes & active count
        updateAllMasterStates();
    }

    // Update Row Master "ALL" state based on individual checkboxes ("one by one" check)
    function updateRowMasterState($row) {
        var $permChecks = $row.find('.perm-check');
        if ($permChecks.length > 0) {
            var allChecked = ($permChecks.length === $permChecks.filter(':checked').length);
            $row.find('.row-master-all').prop('checked', allChecked);
        }
    }

    // Update all row & column master checkbox states
    function updateAllMasterStates() {
        var totalChecked = 0;
        $('.module-row').each(function() {
            var $row = $(this);
            updateRowMasterState($row);
            totalChecked += $row.find('.perm-check:checked').length;
        });

        $('#activePermCountDisplay').text(totalChecked);

        // Column masters sync
        ['view', 'create', 'edit', 'delete'].forEach(function(action) {
            var $actionChecks = $('.action-' + action);
            if ($actionChecks.length > 0) {
                var colChecked = ($actionChecks.length === $actionChecks.filter(':checked').length);
                $('.col-master[data-action="' + action + '"]').prop('checked', colChecked);
            }
        });

        // Global master sync
        var $allPerms = $('.perm-check');
        var globalAll = ($allPerms.length > 0 && $allPerms.length === $allPerms.filter(':checked').length);
        $('#globalMasterCheckbox').prop('checked', globalAll);
    }

    // Role Selection Change
    $('#roleSelect').on('change', loadRolePermissions);

    // Initial Load
    loadRolePermissions();

    // ===== MASTER "ALL" CHECKBOX LOGIC =====

    // 1. Row Master "ALL" toggle -> toggles all permissions in that row
    $(document).on('change', '.row-master-all', function() {
        var checked = $(this).is(':checked');
        var $row = $(this).closest('tr.module-row');
        $row.find('.perm-check').prop('checked', checked);
        updateAllMasterStates();
    });

    // 2. Column Master toggle -> toggles all checkboxes for that action column across all rows
    $(document).on('change', '.col-master', function() {
        var action = $(this).data('action');
        var checked = $(this).is(':checked');
        $('.action-' + action).prop('checked', checked);
        updateAllMasterStates();
    });

    // 3. Global Master toggle -> toggles ALL permissions across entire matrix
    $('#globalMasterCheckbox').on('change', function() {
        var checked = $(this).is(':checked');
        $('.perm-checkbox').prop('checked', checked);
        updateAllMasterStates();
    });

    // 4. Individual ("one by one") checkbox change -> updates master state dynamically
    $(document).on('change', '.perm-check', function() {
        var $row = $(this).closest('tr.module-row');
        updateRowMasterState($row);
        updateAllMasterStates();
    });

    // ===== SEARCH FILTER =====
    $('#matrixSearch').on('input', function() {
        var term = $(this).val().toLowerCase().trim();
        $('.module-row').each(function() {
            var searchData = $(this).data('search') || '';
            var text = $(this).text().toLowerCase();
            if (term === '' || searchData.indexOf(term) > -1 || text.indexOf(term) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // ===== SAVE PERMISSIONS VIA AJAX =====
    $('#btnSavePermissions').click(function() {
        var roleId = $('#roleSelect').val();
        var roleName = $('#roleSelect').find(':selected').text();
        
        var selectedPermissions = [];
        $('.perm-check:checked').each(function() {
            selectedPermissions.push($(this).val());
        });

        Swal.fire({
            title: 'Save Permissions?',
            text: 'Role "' + roleName + '" ke permissions update karne ke liye ready hain?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Save Permissions!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                
                $.ajax({
                    url: "{{ route('roles.update.permission') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        edit_id: roleId,
                        permissions: selectedPermissions
                    },
                    success: function(res) {
                        // Update local option data attribute
                        $('#roleSelect').find(':selected').data('perms', selectedPermissions);

                        Swal.fire({
                            icon: 'success',
                            title: 'Permissions Saved!',
                            text: 'Role "' + roleName + '" permissions have been updated successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Saving!',
                            text: 'Permissions save karne mein issue hua. Please try again.'
                        });
                    }
                });
            }
        });
    });

    // ===== ADD NEW PERMISSION MODAL =====
    $('#addPermBtn').click(function() {
        $('#permEditId').val('');
        $('#moduleSelect').val('');
        $('#actionSelect').val('');
        $('#permPreviewInput').val('');
        $('#permModalTitleText').text('Add New Permission');
        $('#permModal').modal('show');
    });

    function updatePreview() {
        var mod = $('#moduleSelect').val();
        var act = $('#actionSelect').val();
        var code = (mod && act) ? mod + '.' + act : '';
        $('#permPreviewInput').val(code);
        $('#permNameInput').val(code);
    }

    $(document).on('change', '#moduleSelect, #actionSelect', updatePreview);

    $('#permForm').on('submit', function(e) {
        e.preventDefault();
        var code = $('#permNameInput').val();
        if(!code) {
            Swal.fire('Warning', 'Module and Action selection required.', 'warning');
            return;
        }

        var formData = $(this).serialize();
        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: formData,
            success: function(res) {
                $('#permModal').modal('hide');
                Swal.fire('Success', 'New permission added successfully!', 'success').then(() => {
                    location.reload();
                });
            },
            error: function(err) {
                Swal.fire('Error', 'Failed to save permission.', 'error');
            }
        });
    });

});
</script>
@endsection

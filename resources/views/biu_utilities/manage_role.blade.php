@extends('biu_layout.admin')

@section('title', 'BI-U: Role Management')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<div class="table-header">
    <h3><i class="fas fa-user-shield"></i> Role Management</h3>
    <div class="header-controls">
        @if($userRole && $userRole->permissions->contains('slug', 'add-new-role'))
        <button class="add-btn" onclick="showAddRoleModal()">
            <i class="fas fa-plus"></i> New Role
        </button>
        @endif
    </div>
</div>
<div class="content-wrapper">
    <div class="table-container">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Role Name</th>
                    @if($userRole && ($userRole->permissions->contains('slug', 'manage-role-permissions') || 
                        $userRole->permissions->contains('slug', 'edit-role') || 
                        $userRole->permissions->contains('slug', 'delete-role')))
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                <tr>
                    <td>{{ $role->role_id }}</td>
                    <td>{{ $role->name }}</td>
                    <td>
                        <div class="action-buttons">
                            @if($userRole && $userRole->permissions->contains('slug', 'manage-role-permissions'))
                            <a href="{{ route('role-permissions', ['role_id' => $role->role_id]) }}" class="btn_uni btn-view">
                                <i class="fas fa-lock"></i> Permissions
                            </a>
                            @endif
                            
                            @if($userRole && $userRole->permissions->contains('slug', 'edit-role'))
                            <button class="btn_uni btn-view" onclick="editRole({{ $role->role_id }})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            @endif
                            
                            @if($userRole && $userRole->permissions->contains('slug', 'delete-role'))
                            <button class="btn_uni btn-deactivate" onclick="deleteRole({{ $role->role_id }})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Add Role Modal -->
<div id="addRoleModal" class="modal">
    <div class="modal-content">
        <h3>Add New Role</h3>
        <form id="addRoleForm" onsubmit="handleRoleSubmit(event)">
            @csrf
            <div class="form-group">
                <label for="roleName">Role Name:</label>
                <input type="text" id="roleName" name="name" class="form-control" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closeAddRoleModal()">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Create</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Role Confirmation Modal -->
<div id="deleteRoleModal" class="modal">
    <div class="modal-content">
        <h3>Delete Role</h3>
        <p>Are you sure you want to delete this role?</p>
        <p>This action cannot be undone.</p>
        <div class="modal-actions">
            <button class="btn_modal btn_cancel" onclick="closeRoleDeleteModal()">Cancel</button>
            <button class="btn_modal btn_delete" onclick="confirmDeleteRole()">Delete</button>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div id="editRoleModal" class="modal">
    <div class="modal-content">
        <h3>Edit Role</h3>
        <form id="editRoleForm" onsubmit="updateRole(event)">
            <div class="form-group">
                <label for="editRoleName">Role Name:</label>
                <input type="text" id="editRoleName" name="name" class="form-control" required>
                <div class="invalid-feedback"></div>
                <input type="hidden" id="editRoleId">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closeRoleEditModal()">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Result Modal -->
<div id="resultModal" class="modal">
    <div class="modal-content result-modal">
        <div id="resultIcon"></div>
        <h3 id="resultTitle"></h3>
        <p id="resultMessage"></p>
        <div class="modal-actions">
            <button class="btn_modal btn_verify" onclick="closeRoleResultModal()">OK</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/role.js') }}"></script>
@endsection

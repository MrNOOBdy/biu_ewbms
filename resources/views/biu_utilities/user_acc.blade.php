@extends('biu_layout.admin')

@section('title', 'BI-U: User Management')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
<div class="table-header">
    <h3><i class="fas fa-users"></i> User Data Management</h3>
    <div class="header-controls">
        <div class="filter-section">
            <select id="userRoleFilter" onchange="filterUserTable()">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
            <select id="userStatusFilter" onchange="filterUserTable()">
                <option value="">All Status</option>
                <option value="activate">Active</option>
                <option value="deactivate">Inactive</option>
            </select>
        </div>
        <div class="search-container">
            <input type="text" id="userSearchInput" placeholder="Search users...">
            <button class="btn-search" onclick="filterUserTable()">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
        @if($canAddUser)
            <button class="add-btn" type="button" onclick="showAddUserModal()">
                <i class="fas fa-plus-circle"></i> Add New User
            </button>
        @endif
    </div>
</div>
        
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-wrapper">
    <div id="userPermissions" 
        data-can-update="{{ $userRole->hasPermission('update-user') ? 'true' : 'false' }}"
        data-can-deactivate="{{ $userRole->hasPermission('deactivate-user') ? 'true' : 'false' }}"
        style="display: none;">
    </div>
    <div class="table-container">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Contact Number</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    @if($userRole->hasPermission('update-user') || $userRole->hasPermission('deactivate-user'))
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->user_id }}</td>
                        <td>{{ $user->firstname }}</td>
                        <td>{{ $user->lastname }}</td>
                        <td>{{ $user->contactnum }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->role ?? 'N/A' }}</td>
                        <td>
                            <span class="status-badge 
                                {{ $user->status === 'activate' ? 'status-active' : 'status-inactive' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        @if($userRole->hasPermission('update-user') || $userRole->hasPermission('deactivate-user'))
                            <td>
                                <div class="action-buttons">
                                    @if($userRole->hasPermission('update-user'))
                                        <button class="btn_uni btn-view" title="Update" onclick="viewUser('{{ $user->user_id }}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endif
                                    
                                    @if($userRole->hasPermission('deactivate-user'))
                                        @if ($user->status === 'activate')
                                            <button class="btn_uni btn-deactivate" title="Deactivate" onclick="toggleUserStatus('{{ $user->user_id }}', 'deactivate')">
                                                <i class="fas fa-user-times"></i>
                                            </button>
                                        @else
                                            <button class="btn_uni btn-activate" title="Activate" onclick="toggleUserStatus('{{ $user->user_id }}', 'activate')">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($userRole->hasPermission('update-user') || $userRole->hasPermission('deactivate-user')) ? 9 : 8 }}" class="empty-state">
                            <i class="fas fa-users-slash"></i>
                            <p>No user account data found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="pagination-wrapper">
            {{ $users->links('pagination.custom') }}
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content large-modal">
        <h3><i class="fas fa-user-plus"></i>Add New User</h3>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Left Column -->
                <div>
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="contactnum">Contact Number</label>
                        <input type="text" id="contactnum" name="contactnum" class="form-control" required maxlength="11" pattern="[0-9]{11}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group" style="width: 100%">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group" style="width: 100%">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <input type="hidden" name="status" value="activate">

            <div class="modal-actions" style="margin-top: 20px;">
                <button type="button" class="btn_modal btn_cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Add User</button>
            </div>
        </form>
    </div>
</div>

<!-- Status Change Modal -->
<div id="statusChangeModal" class="modal">
    <div class="modal-content">
        <h3 id="statusModalTitle"></h3>
        <p id="statusModalMessage"></p>
        <div class="modal-actions">
            <button class="btn_modal btn_cancel" onclick="closeModal()">Cancel</button>
            <button id="confirmStatusBtn" class="btn_back"></button>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div id="statusResultModal" class="modal">
    <div class="modal-content result-modal">
        <div id="statusResultIcon"></div>
        <h3 id="statusResultTitle"></h3>
        <p id="statusResultMessage"></p>
        <div class="modal-actions">
            <button class="btn_modal btn_verify" onclick="closeModal('reload')">OK</button>
        </div>
    </div>
</div>

<!-- Update User Modal -->
<div id="updateUserModal" class="modal">
    <div class="modal-content large-modal">
        <h3><i class="fas fa-user-edit"></i>Update User</h3>
        <form id="updateUserForm" onsubmit="handleUpdateUserSubmit(event)">
            @csrf
            @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Left Column -->
                <div>
                    <div class="form-group">
                        <label for="edit_firstname">First Name</label>
                        <input type="text" id="edit_firstname" name="firstname" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_lastname">Last Name</label>
                        <input type="text" id="edit_lastname" name="lastname" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_contactnum">Contact Number</label>
                        <input type="text" id="edit_contactnum" name="contactnum" class="form-control" required maxlength="11" pattern="[0-9]{11}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <div class="form-group">
                        <label for="edit_username">Username</label>
                        <input type="text" id="edit_username" name="username" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_email">Email Address</label>
                        <input type="email" id="edit_email" name="email" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_role">Role</label>
                        <select id="edit_role" name="role" class="form-control" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="edit_user_id" name="user_id">

            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn_modal btn_warning" onclick="initiatePasswordReset()">Reset Password</button>
                <button type="button" class="btn_modal btn_delete" onclick="deleteUser()">Delete User</button>
                <button type="submit" class="btn_modal btn_verify">Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- Password Verification Modal for Delete -->
<div id="deleteVerificationModal" class="modal">
    <div class="modal-content">
        <h3>Authentication Required</h3>
        <p>Please enter the user's current password to confirm deletion:</p>
        <div class="form-group">
            <label for="delete_password">User's Password:</label>
            <input type="password" class="form-control" id="delete_password" required>
            <button type="button" id="toggleDeletePassword" class="toggle-password">
                <i class="fas fa-eye-slash"></i>
            </button>
            <div class="invalid-feedback" style="display: none;"></div>
        </div>
        <div class="modal-actions">
            <button class="btn_modal btn_cancel" onclick="deleteModalClose()">Cancel</button>
            <button class="btn_modal btn_verify" onclick="verifyDeletePassword()">Verify</button>
        </div>
    </div>
</div>

<!-- Delete User Confirmation Modal -->
<div id="deleteUserModal" class="modal">
    <div class="modal-content">
        <h3>Delete User</h3>
        <p>Are you sure you want to delete this user?</p>
        <p>This action cannot be undone.</p>
        <div class="modal-actions">
            <button class="btn_modal btn_cancel" onclick="closeModal()">Cancel</button>
            <button class="btn_modal btn_delete" onclick="confirmDeleteUser()">Delete</button>
        </div>
    </div>
</div>

<!-- Password Reset Verification Modal -->
<div id="pwdResetVerificationModal" class="modal">
    <div class="modal-content">
        <h3>Authentication Required</h3>
        <p>Please enter the user's current password to proceed with password reset:</p>
        <div class="form-group">
            <label for="reset_verify_password">User's Password:</label>
            <input type="password" class="form-control" id="reset_verify_password" required>
            <button type="button" id="toggleResetVerifyPassword" class="toggle-password">
                <i class="fas fa-eye-slash"></i>
            </button>
            <div class="invalid-feedback" style="display: none;"></div>
        </div>
        <div class="modal-actions">
            <button class="btn_modal btn_cancel" onclick="resetModalClose()">Cancel</button>
            <button class="btn_modal btn_verify" onclick="verifyPasswordForReset()">Verify</button>
        </div>
    </div>
</div>

<!-- Password Reset Form Modal -->
<div id="pwdResetFormModal" class="modal">
    <div class="modal-content">
        <h3>Reset Password</h3>
        <form id="pwdResetForm">
            @csrf
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
                <button type="button" id="toggleNewPassword" class="toggle-password">
                    <i class="fas fa-eye-slash"></i>
                </button>
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label for="new_password_confirmation">Confirm New Password:</label>
                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                <button type="button" id="toggleNewPasswordConfirm" class="toggle-password">
                    <i class="fas fa-eye-slash"></i>
                </button>
                <div class="invalid-feedback"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<!-- Password Reset Result Modal -->
<div id="pwdResetResultModal" class="modal">
    <div class="modal-content result-modal">
        <div id="pwdResetResultIcon"></div>
        <h3 id="pwdResetResultTitle"></h3>
        <p id="pwdResetResultMessage"></p>
        <div class="modal-actions">
            <button class="btn_modal btn_verify" onclick="closeModal('reload')">OK</button>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div id="userResultModal" class="modal">
    <div class="modal-content result-modal">
        <div id="userResultIcon"></div>
        <h3 id="userResultTitle"></h3>
        <p id="userResultMessage"></p>
        <div class="modal-actions">
            <button class="btn_modal btn_verify" onclick="closeModal('reload')">OK</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/user_access.js') }}"></script>
<script src="{{ asset('js/user_access2.js') }}"></script>
<script>
    function filterUserTable() {
        const roleFilter = document.getElementById('userRoleFilter').value;
        const statusFilter = document.getElementById('userStatusFilter').value;
        const searchQuery = document.getElementById('userSearchInput').value.trim();
        const tableBody = document.querySelector('.uni-table tbody');
        const paginationContainer = document.querySelector('.pagination-wrapper');

        const permissions = document.getElementById('userPermissions');
        const canUpdate = permissions.dataset.canUpdate === 'true';
        const canDeactivate = permissions.dataset.canDeactivate === 'true';
        const hasPermissions = canUpdate || canDeactivate;

        fetch(`/users/search?query=${encodeURIComponent(searchQuery)}&role=${encodeURIComponent(roleFilter)}&status=${encodeURIComponent(statusFilter)}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tableBody.innerHTML = '';

                if (data.users.length === 0) {
                    const colspan = document.querySelector('.uni-table thead tr').children.length;
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="${colspan}" class="empty-state">
                                <i class="fas fa-users-slash"></i>
                                <p>No user account data found</p>
                            </td>
                        </tr>
                    `;
                } else {
                    data.users.forEach(user => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${user.user_id}</td>
                            <td>${user.firstname}</td>
                            <td>${user.lastname}</td>
                            <td>${user.contactnum}</td>
                            <td>${user.email}</td>
                            <td>${user.username}</td>
                            <td>${user.role || 'N/A'}</td>
                            <td>
                                <span class="status-badge ${user.status === 'activate' ? 'status-active' : 'status-inactive'}">
                                    ${user.status === 'activate' ? 'Active' : 'Inactive'}
                                </span>
                            </td>
                            ${hasPermissions ? `
                            <td>
                                <div class="action-buttons">
                                    ${canUpdate ? `
                                        <button class="btn_uni btn-view" title="Update" onclick="viewUser('${user.user_id}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    ` : ''}
                                    ${canDeactivate ? `
                                        ${user.status === 'activate' ? `
                                            <button class="btn_uni btn-deactivate" title="Deactivate" onclick="toggleUserStatus('${user.user_id}', 'deactivate')">
                                                <i class="fas fa-user-times"></i>
                                            </button>
                                        ` : `
                                            <button class="btn_uni btn-activate" title="Activate" onclick="toggleUserStatus('${user.user_id}', 'activate')">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        `}
                                    ` : ''}
                                </div>
                            </td>` : ''}
                        `;
                        tableBody.appendChild(row);
                    });
                }

                if (paginationContainer) {
                    paginationContainer.style.display = searchQuery || roleFilter || statusFilter ? 'none' : 'flex';
                }
            } else {
                showUserResultModal(false, 'Error', data.message);
            }
        })
        .catch(error => {
            showUserResultModal(false, 'Error', 'Failed to search users');
        });
    }
</script>
@endsection
@extends('biu_layout.admin')

@section('title', 'BI-U: Role Permissions')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/permissions.css') }}">

<div class="table-header">
    <h3>Permissions for Role: {{ $role->name }}</h3>
    <div class="header-controls">
        <button class="add-btn" onclick="updateRolePermissions({{ $role->role_id }})">
            <i class="fas fa-save"></i> Update Role Permissions
        </button>
        <a href="{{ route('roles.index') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Roles List
        </a>
    </div>
</div>

<div class="permissions-container" data-role-id="{{ $role->role_id }}">
    <div class="permissions-sidebar">
        <div class="search-permissions">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search permissions..." id="permissionSearch">
        </div>
        <div class="section-navigator">
            @foreach(['Navigation Access', 'Role Management', 'User Management', 
                     'Notification Management', 'Bill Rate Management', 'Block Management',
                     'Coverage Date Management', 'Fee Management', 'Consumer Management',
                     'Connection Payment Permissions'] as $section)
                <button class="section-nav-item" data-section="{{ Str::slug($section) }}">
                    <i class="fas fa-{{ getSectionIcon($section) }}"></i>
                    <span>{{ $section }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="permissions-content">
        @foreach(['Navigation Access', 'Role Management', 'User Management', 
                 'Notification Management', 'Bill Rate Management', 'Block Management',
                 'Coverage Date Management', 'Fee Management', 'Consumer Management',
                 'Connection Payment Permissions'] as $section)
            <div class="permission-section" id="{{ Str::slug($section) }}">
                <div class="section-header">
                    <h3>
                        <i class="fas fa-{{ getSectionIcon($section) }}"></i>
                        {{ $section }}
                    </h3>
                    <button class="btn_uni toggle-all btn-activate" data-section="{{ Str::slug($section) }}">
                        Toggle All
                    </button>
                </div>
                <div class="permissions-grid">
                    @foreach($permissions as $permission)
                        @if(in_array($permission->slug, getPermissionSlugsForSection($section)))
                            <div class="permission-card" data-slug="{{ $permission->slug }}">
                                <label class="permission-toggle">
                                    <input type="checkbox" 
                                           id="permission_{{ $permission->permission_id }}"
                                           {{ $role->permissions->contains($permission->permission_id) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <div class="permission-info">
                                    <span class="permission-name">{{ $permission->name }}</span>
                                    <span class="permission-desc">{{ $permission->description ?? 'No description available' }}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

@php
function getSectionIcon($section) {
    $icons = [
        'Navigation Access' => 'compass',
        'Role Management' => 'user-shield',
        'User Management' => 'users',
        'Notification Management' => 'bell',
        'Bill Rate Management' => 'money-bill',
        'Block Management' => 'th-large',
        'Coverage Date Management' => 'calendar-alt',
        'Fee Management' => 'receipt',
        'Consumer Management' => 'user-friends',
        'Connection Payment Permissions' => 'credit-card'
    ];
    return $icons[$section] ?? 'circle';
}

function getPermissionSlugsForSection($section) {
    switch($section) {
        case 'Navigation Access':
            return ['access-dashboard', 'access-consumers', 'access-connection-payment', 
                   'access-billing', 'billing-payment', 'access-meter-reading', 'access-meter-readers-block',
                   'access-reports', 'access-settings', 'access-utilities'];
        case 'Role Management':
            return ['add-new-role', 'edit-role', 'manage-role-permissions', 
                   'delete-role', 'view-role-management'];
        case 'User Management':
            return ['view-user-management', 'add-new-user', 'update-user', 'deactivate-user'];
        case 'Notification Management':
            return ['view-notification-management', 'add-new-notice', 'edit-notice', 'delete-notice'];
        case 'Bill Rate Management':
            return ['access-bill-rate', 'add-bill-rate', 'edit-bill-rate', 'delete-bill-rate'];
        case 'Block Management':
            return ['access-block-management', 'add-new-block', 'edit-block', 'delete-block'];
        case 'Coverage Date Management':
            return ['access-coverage-date', 'add-coverage-date', 'edit-coverage-date', 
                   'delete-coverage-date'];
        case 'Fee Management':
            return ['access-fee-management', 'edit-fees'];
        case 'Consumer Management':
            return ['add-new-consumer', 'edit-consumer', 'view-consumer-billings', 
                   'delete-consumer', 'reconnect-consumer'];
        case 'Connection Payment Permissions':
            return ['access-application-fee', 'process-application-payment', 'print-application',
                   'service-fee-access', 'service-pay', 'service-print'];
        default:
            return [];
    }
}
@endphp

<!-- Result Modal -->
<div id="resultModal" class="modal">
    <div class="modal-content result-modal">
        <div id="resultIcon"></div>
        <h3 id="resultTitle"></h3>
        <p id="resultMessage"></p>
        <div class="modal-actions">
            <button class="btn_modal btn_verify" onclick="closePermissionResultModal()">OK</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/role.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('permissionSearch');
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.permission-card').forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(searchTerm) ? 'flex' : 'none';
        });
    });

    document.querySelectorAll('.section-nav-item').forEach(button => {
        button.addEventListener('click', function() {
            const sectionId = this.dataset.section;
            document.getElementById(sectionId).scrollIntoView({ behavior: 'smooth' });
        });
    });

    document.querySelectorAll('.toggle-all').forEach(button => {
        button.addEventListener('click', function() {
            const section = this.closest('.permission-section');
            const checkboxes = section.querySelectorAll('input[type="checkbox"]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
        });
    });
});
</script>
@endsection
@extends('biu_layout.admin')

@section('title', 'BI-U: Role Permissions')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<div class="table-header">
    <h3>Permissions for Role: {{ $role->name }}</h3>
    <div class="header-controls">
        <button class="upt-button" onclick="updateRolePermissions({{ $role->role_id }})">
            <i class="fas fa-save"></i> Update Role Permissions
        </button>
        <a href="{{ route('roles.index') }}" class="btn add-btn">
            <i class="fas fa-arrow-left"></i> Back to Roles List
        </a>
    </div>
</div>

<div class="content-wrapper" data-role-id="{{ $role->role_id }}">
    <div class="permi_cont">
        @foreach(['Navigation Access', 'Role Management', 'User Management', 
                 'Notification Management', 'Bill Rate Management', 'Block Management',
                 'Coverage Date Management', 'Fee Management', 'Consumer Management',
                 'Connection Payment Permissions'] as $section)
            <div class="permission-section">
                <h3 class="section-title">{{ $section }}</h3>
                <div class="permission-grid">
                    @foreach($permissions as $permission)
                        @if(in_array($permission->slug, getPermissionSlugsForSection($section)))
                            <div class="permission-item" data-slug="{{ $permission->slug }}">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="custom-checkbox">
                                        <input type="checkbox" 
                                            id="permission_{{ $permission->permission_id }}"
                                            {{ ($role->name === 'Administrator' && in_array($permission->slug, [
                                                'access-dashboard', 'access-consumers', 'access-utilities',
                                                'access-settings', 'view-role-management', 'add-new-role',
                                                'edit-role', 'manage-role-permissions', 'delete-role'
                                            ])) ? 'checked disabled' : '' }}
                                            {{ $role->permissions->contains($permission->permission_id) ? 'checked' : '' }}>
                                    </div>
                                    {{ $permission->name }}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

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

@php
function getPermissionSlugsForSection($section) {
    switch($section) {
        case 'Navigation Access':
            return ['access-dashboard', 'access-consumers', 'access-connection-payment', 
                   'access-billing', 'billing-payment', 'access-meter-reading', 
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

<style>
    .permi_cont {
        height: 98%;
        overflow-y: auto;
        width: 80%;
        padding-right: var(--spacing-slg);
        margin: 20px auto;
        background-color: var(--background-color);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-sm);
    }
    
    .upt-button {
        background: var(--primary-color);
        color: var(--text-color-light);
        padding: var(--spacing-sm) var(--spacing-md);
        border: none;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        transition: background-color var(--transition-fast);
    }

    .upt-button:hover {
        background: var(--primary-color-hover);
    }

    .permission-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-md);
        padding: var(--spacing-md);
    }

    .permission-item {
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: var(--border-radius-md);
        transition: background-color var(--transition-fast);
    }

    .permission-item:hover {
        background: var(--light-bg);
    }

    .permission-section {
        margin-bottom: var(--spacing-md);
    }

    .section-title {
        color: var(--text-color-dark);
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: var(--spacing-slg);
        border-bottom: 2px solid var(--border-color-light);
    }

    .custom-checkbox {
        display: flex;
        align-items: center;
    }

    .custom-checkbox {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .custom-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-right: var(--spacing-md);
        cursor: pointer;
        appearance: none;
        border: 2px solid var(--primary-color);
        border-radius: var(--border-radius-sm);
    }

    .custom-checkbox input[type="checkbox"]:checked {
        background-color: var(--primary-color);
    }

    .custom-checkbox input[type="checkbox"]:checked::before {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: var(--text-color-light);
    }

    .custom-checkbox input[type="checkbox"]:hover {
        border-color: var(--primary-color-hover);
    }

    .custom-checkbox input[type="checkbox"]:disabled {
        background-color: var(--primary-color);
        opacity: 0.6;
        cursor: not-allowed;
    }

    .custom-checkbox input[type="checkbox"]:disabled:hover {
        border-color: var(--primary-color);
    }

    .dark-mode .permi_cont {
        background-color: var(--dark-secondary);
    }

    .dark-mode .custom-checkbox input[type="checkbox"] {
        border-color: var (--primary-color-light);
    }

    .dark-mode .custom-checkbox input[type="checkbox"]:checked {
        background-color: var(--primary-color-light);
    }

    .dark-mode .custom-checkbox input[type="checkbox"]:disabled {
        background-color: var(--primary-color-light);
        opacity: 0.6;
    }

    .dark-mode .permission-item {
        background: var(--dark-secondary);
        border-color: var(--border-color-dark);
    }

    .dark-mode .section-title {
        color: var(--text-color-light);
    }

    .content-wrapper {
        max-width: 1000px;
        margin: auto;
    }
</style>

<script src="{{ asset('js/role.js') }}"></script>
@endsection
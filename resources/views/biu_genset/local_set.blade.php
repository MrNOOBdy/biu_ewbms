@extends('biu_layout.admin')

@section('title', 'BI-U: Local Settings')

@section('tab-content')
<style>
    .settings-container {
        padding: var(--spacing-xl);
        max-width: 600px;
        margin: 0 auto;
        overflow-y: auto;
    }

    .settings-group {
        background: var(--background-color);
        border-radius: var(--border-radius-md);
        border: 1px solid var(--border-color-light);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-xl);
        box-shadow: var(--shadow-sm);
    }

    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-md) 0;
    }

    .setting-label {
        font-weight: 500;
        color: var(--text-color-dark);
    }

    .settings-select {
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: var(--border-radius-md);
        border: 1px solid var(--border-color-light);
        background-color: var(--background-color);
        font-size: 14px;
        width: 150px;
        cursor: pointer;
        transition: all var(--transition-fast);
    }

    .settings-select:hover {
        border-color: var(--primary-color);
    }

    .settings-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(48, 82, 220, 0.2);
    }

    .fee-input {
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: var(--border-radius-md);
        border: 1px solid var(--border-color-light);
        background-color: var(--background-color);
        font-size: 14px;
        width: 150px;
        transition: all var(--transition-fast);
    }

    .fee-input:hover {
        border-color: var(--primary-color);
    }

    .fee-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(48, 82, 220, 0.2);
    }

    .save-btn {
        background-color: var(--success-color);
        color: var(--text-color-light);
        padding: var(--spacing-sm) var(--spacing-lg);
        border: none;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        font-weight: 500;
        transition: background-color var(--transition-fast);
    }

    .save-btn:hover {
        background-color: #218838;
    }

    .fee-display {
        display: flex;
        align-items: center;
        gap: var(--spacing-slg);
    }

    .fee-amount {
        font-size: 16px;
        font-weight: 500;
        color: var(--text-color-dark);
    }

    .edit-btn {
        background-color: var(--primary-color);
        color: var(--text-color-light);
        padding: 10px var(--spacing-md);
        margin-left: auto;
        height: 10%;
        border: none;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
        transition: background-color var(--transition-fast);
    }

    .edit-btn:hover {
        background-color: var(--primary-color-hover);
    }

    .dark-mode .settings-group {
        background: var(--dark-secondary);
        border: 1px solid var(--border-color-dark);
    }

    .dark-mode .setting-label {
        color: var(--text-color-light);
    }

    .dark-mode .settings-select {
        background-color: var(--dark-bg);
        border-color: var(--border-color-dark);
        color: var(--text-color-light);
    }

    .dark-mode .settings-select:hover {
        border-color: var(--primary-color);
    }

    .dark-mode .settings-select option {
        background-color: var(--dark-bg);
        color: var(--text-color-light);
    }

    .dark-mode .fee-input {
        background-color: var(--dark-bg);
        border-color: var(--border-color-dark);
        color: var(--text-color-light);
    }

    .dark-mode .fee-amount {
        color: var(--text-color-light);
    }
</style>
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<div class="table-header">
    <h3><i class="fas fa-cogs"></i> Local Settings</h3>
</div>

<div class="settings-container">
    <div class="settings-group">
        <div class="setting-item">
            <label class="setting-label">Dark Mode</label>
            <select id="darkModeSelect" class="settings-select">
                <option value="off">Off</option>
                <option value="on">On</option>
                <option value="system">Match System</option>
            </select>
        </div>
    </div>

    <div class="settings-group">
        <div style="display:flex;">
            <h3>Fee Management</h3>
            @if(auth()->user()->hasPermission('edit-fees'))
            <button type="button" class="edit-btn" onclick="editFees()">
                <i class="fas fa-edit"></i> Edit Fees
            </button>
            @endif
        </div>
        <div class="setting-item">
            <label class="setting-label">Application Fee</label>
            <div class="fee-display">
                <span class="fee-amount">₱{{ number_format($fees['Application Fee'] ?? 1050.00, 2) }}</span>
            </div>
        </div>
        <div class="setting-item">
            <label class="setting-label">Reconnection Fee</label>
            <div class="fee-display">
                <span class="fee-amount">₱{{ number_format($fees['Reconnection Fee'] ?? 700.00, 2) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Edit Fee Modal -->
<div id="editFeeModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-edit"></i> Edit Fees</h3>
        <form id="feeForm" onsubmit="return false;">
            @csrf
            <div class="form-group">
                <label for="application_fee">Application Fee:</label>
                <input type="number" 
                       step="0.01" 
                       class="form-control" 
                       id="application_fee"
                       name="application_fee" 
                       value="{{ $fees['Application Fee'] ?? 1050.00 }}"
                       required>
            </div>
            <div class="form-group">
                <label for="reconnection_fee">Reconnection Fee:</label>
                <input type="number" 
                       step="0.01" 
                       class="form-control" 
                       id="reconnection_fee"
                       name="reconnection_fee" 
                       value="{{ $fees['Reconnection Fee'] ?? 700.00 }}"
                       required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closeEditFeeModal()">Cancel</button>
                <button type="button" class="btn_modal btn_verify" onclick="updateFees()">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Fee Result Modal -->
<div id="feeResultModal" class="modal">
    <div class="modal-content result-modal">
        <div id="feeResultIcon" class="success">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 id="feeResultTitle">Success</h3>
        <p id="feeResultMessage"></p>
        <div class="modal-actions">
            <button onclick="closeFeeResultModal()" class="btn_modal btn_verify">OK</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/fees.js') }}"></script>
@endsection

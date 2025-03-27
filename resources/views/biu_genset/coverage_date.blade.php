@extends('biu_layout.admin')

@section('title', 'BI-U: Coverage Date Management')

@section('tab-content')
<style>
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        position: sticky;
        height: 30px;
        top: 0;
        padding: 10px;
    }

    .table-header h3 i {
        margin-right: 8px;
    }

    .active-header {
        padding: 4px 8px;
        border-radius: 4px;
        background-color: #a6f0b7;
        margin-top: -20px;
        margin-bottom: -20px;
    }

    .dark-mode .active-header {
        background-color: #134a1e;
        color: #7dd992;
    }

    .closed-header {
        padding: 4px 8px;
        border-radius: 4px;
        background-color: #f0a6a6;
        margin-top: 30px;
    }

    .dark-mode .closed-header {
        background-color: #4a1313;
        color: #d97d7d;
    }

    .active-header i,
    .closed-header i {
        margin-right: 5px;
    }

    .date-filter-container {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .date-range-group {
        position: absolute;
        right: 15%;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .date-picker,
    .flatpickr-input {
        width: 110px;
        height: 22px;
        padding: 4px 8px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 13px;
        color: #495057;
        background-color: #fff;
    }

    .date-picker:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    .flatpickr-input {
        background-color: white !important;
        cursor: pointer;
        border: 1px solid var(--border-color-light);
        width: 130px;
        font-size: 14px;
        color: var(--text-color-dark);
        padding: 8px 12px;
        border-radius: var(--border-radius-sm);
    }

    .flatpickr-alt-input {
        color: #495057 !important;
    }

    .flatpickr-input.form-control[readonly] {
        background-color: white !important;
    }

    .flatpickr-input::placeholder {
        color: #6c757d;
    }

    .flatpickr-input:focus {
        outline: none;
        box-shadow: none;
    }

    .flatpickr-calendar {
        z-index: 99999 !important;
    }

    .flatpickr-monthDropdown-months,
    .flatpickr-yearDropdown {
        padding: 5px !important;
    }

    .dark-mode .date-range-group p {
        color: var(--text-color-light);
    }

    .date-range-group p {
        margin: 0;
        color: #6c757d;
        font-size: 13px;
    }

    .dark-mode .date-range-group input {
        background-color: var(--dark-bg);
        border-color: var(--border-color-dark);
        color: var(--text-color-light);
    }

    .filter-buttons {
        display: flex;
        gap: 8px;
    }

    .filter-btn,
    .add-coverage-btn {
        height: 32px;
        padding: 0 12px;
        border: none;
        border-radius: 4px;
        font-size: 13px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .filter-apply {
        background-color: var(--primary-color);
        color: white;
    }

    .filter-apply:hover {
        background-color: #0056b3;
    }

    .filter-reset {
        background-color: var(--secondary-color);
        color: white;
    }

    .filter-reset:hover {
        background-color: #5a6268;
    }

    .add-coverage-btn {
        background-color: var(--success-color);
        color: white;
        margin-left: auto;
    }

    .add-coverage-btn:hover {
        background-color: #218838;
    }

    .modal input[type="date"]::placeholder {
        color: var(--text-color-dark);
        opacity: 0.5;
    }

    .modal input[type="date"]::-webkit-datetime-edit-text,
    .modal input[type="date"]::-webkit-datetime-edit-month-field,
    .modal input[type="date"]::-webkit-datetime-edit-day-field,
    .modal input[type="date"]::-webkit-datetime-edit-year-field {
        color: transparent;
    }

    .modal input[type="date"]:focus::-webkit-datetime-edit-text,
    .modal input[type="date"]:focus::-webkit-datetime-edit-month-field,
    .modal input[type="date"]:focus::-webkit-datetime-edit-day-field,
    .modal input[type="date"]:focus::-webkit-datetime-edit-year-field,
    .modal input[type="date"]:valid::-webkit-datetime-edit-text,
    .modal input[type="date"]:valid::-webkit-datetime-edit-month-field,
    .modal input[type="date"]:valid::-webkit-datetime-edit-day-field,
    .modal input[type="date"]:valid::-webkit-datetime-edit-year-field {
        color: var(--text-color-dark);
    }

    .modal select:-moz-focusring {
        color: transparent;
        text-shadow: 0 0 0 var(--text-color-dark);
    }

    .inline-form-group {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        gap: 10px;
    }

    .inline-form-group label {
        flex: 0 0 160px;
        text-align: right;
        margin-bottom: 0;
        padding-left: 0;
    }

    .inline-form-group .form-control {
        flex: 0 0 200px;
    }

    .inline-form-group .invalid-feedback {
        position: absolute;
        margin-left: 140px;
        margin-top: 40px;
    } 
</style>
<link rel="stylesheet" href="{{ asset('css/flatpickr.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<div class="table-header">
    <h3><i class="fas fa-calendar-alt"></i> Coverage Date Management</h3>
    <div class="header-controls">
        <div class="date-filter-container">
            @if(Auth::user()->hasPermission('add-coverage-date'))
            <button class="add-coverage-btn" type="button" onclick="showAddModal()">
                <i class="fas fa-plus-circle"></i> Add Coverage Date
            </button>
            @endif
        </div>
    </div>
</div>

<!-- Active Coverage Date Section -->
<div class="table-header">
    <h4 class="active-header">
        <i class="fas fa-calendar-check"></i>
        Active Coverage Period
    </h4>
</div>
<div class="content-wrapper" style="height: 10%;">
    <div class="table-container active-table">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>Coverage From</th>
                    <th>Coverage To</th>
                    <th>Reading Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    @if(Auth::user()->hasPermission('edit-coverage-date'))
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php
                    $activeCoverage = $coverage_dates->where('status', 'Open')->first();
                @endphp
                @if ($activeCoverage)
                    <tr data-covdate-id="{{ $activeCoverage->covdate_id }}" data-status="{{ $activeCoverage->status }}">
                        <td>{{ date('M d, Y', strtotime($activeCoverage->coverage_date_from)) }}</td>
                        <td>{{ date('M d, Y', strtotime($activeCoverage->coverage_date_to)) }}</td>
                        <td>{{ date('M d, Y', strtotime($activeCoverage->reading_date)) }}</td>
                        <td>{{ date('M d, Y', strtotime($activeCoverage->due_date)) }}</td>
                        <td>
                            <span class="status-badge status-active">
                                {{ $activeCoverage->status }}
                            </span>
                        </td>
                        @if(Auth::user()->hasPermission('edit-coverage-date'))
                        <td>
                            <div class="action-buttons">
                                <button class="btn_uni btn-view" onclick="editCoverageDate({{ $activeCoverage->covdate_id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                @else
                    <tr>
                        <td colspan="{{ Auth::user()->hasPermission('edit-coverage-date') ? '6' : '5' }}" class="empty-state">
                            <i class="fas fa-calendar-times empty-calendar"></i>
                            <p>No active coverage period</p>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Closed Coverage Dates Section -->
<div class="table-header">
    <h4 class="closed-header">
        <i class="fas fa-calendar-times"></i>
        Previous/Closed Coverage Periods
    </h4>
    <div class="date-range-group">
        <input type="text" id="dateFrom" class="form-control flatpickr-input" placeholder="From date">
        <p>to</p>
        <input type="text" id="dateTo" class="form-control flatpickr-input" placeholder="To date">
    </div>
    <div class="filter-buttons">
        <button type="button" class="filter-btn filter-apply" onclick="filterTable()">
            <i class="fas fa-filter"></i> Filter
        </button>
        <button type="button" class="filter-btn filter-reset" onclick="clearFilter()">
            <i class="fas fa-undo"></i> Reset
        </button>
    </div>
</div>
<div class="content-wrapper">
    <div class="table-container" style="height: 63%;">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>Coverage From</th>
                    <th>Coverage To</th>
                    <th>Reading Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    @if(Auth::user()->hasPermission('edit-coverage-date') || Auth::user()->hasPermission('delete-coverage-date'))
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php
                    $closedCoverages = $coverage_dates->where('status', 'Close');
                @endphp
                @forelse ($closedCoverages as $date)
                    <tr data-covdate-id="{{ $date->covdate_id }}" data-status="{{ $date->status }}">
                        <td>{{ date('M d, Y', strtotime($date->coverage_date_from)) }}</td>
                        <td>{{ date('M d, Y', strtotime($date->coverage_date_to)) }}</td>
                        <td>{{ date('M d, Y', strtotime($date->reading_date)) }}</td>
                        <td>{{ date('M d, Y', strtotime($date->due_date)) }}</td>
                        <td>
                            <span class="status-badge status-inactive">
                                {{ $date->status }}
                            </span>
                        </td>
                        @if(Auth::user()->hasPermission('edit-coverage-date') || Auth::user()->hasPermission('delete-coverage-date'))
                        <td>
                            <div class="action-buttons">
                                @if(Auth::user()->hasPermission('edit-coverage-date'))
                                    <button class="btn_uni btn-view" onclick="editCoverageDate({{ $date->covdate_id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                                @if(Auth::user()->hasPermission('delete-coverage-date'))
                                    <button class="btn_uni btn-deactivate" onclick="deleteCoverageDate({{ $date->covdate_id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ (Auth::user()->hasPermission('edit-coverage-date') || Auth::user()->hasPermission('delete-coverage-date')) ? '6' : '5' }}" class="empty-state">
                            <i class="fas fa-calendar-times empty-calendar"></i>
                            <p>No closed coverage dates found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Coverage Date Modal -->
<div id="addCoverageDateModal" class="modal">
    <div class="modal-content">
        <h3>Add Coverage Date</h3>
        <form id="addCoverageDateForm" onsubmit="handleFormSubmit(event)">
            @csrf
            <div class="inline-form-group">
                <label for="coverage_date_from">Coverage Date From:</label>
                <input type="text" id="coverage_date_from" name="coverage_date_from" class="form-control date-picker" placeholder="Select date" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="inline-form-group">
                <label for="coverage_date_to">Coverage Date To:</label>
                <input type="text" id="coverage_date_to" name="coverage_date_to" class="form-control date-picker" placeholder="Select date" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="inline-form-group">
                <label for="reading_date">Reading Date:</label>
                <input type="text" id="reading_date" name="reading_date" class="form-control date-picker" placeholder="Select date" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="inline-form-group">
                <label for="due_date">Due Date:</label>
                <input type="text" id="due_date" name="due_date" class="form-control date-picker" placeholder="Select date" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="inline-form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="Open">Open</option>
                    <option value="Close">Close</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closeModal('addCoverageDateModal')">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Add Coverage Date</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Coverage Date Modal -->
<div id="editCoverageDateModal" class="modal">
    <div class="modal-content">
        <h3>Edit Coverage Date</h3>
        <form id="editCoverageDateForm" onsubmit="handleEditFormSubmit(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_covdate_id" name="covdate_id">
            <div class="inline-form-group">
                <label for="edit_coverage_date_from">Coverage Date From:</label>
                <input type="text" id="edit_coverage_date_from" name="coverage_date_from" class="form-control date-picker" placeholder="Select date" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="inline-form-group">
                <label for="edit_coverage_date_to">Coverage Date To:</label>
                <input type="text" id="edit_coverage_date_to" name="coverage_date_to" class="form-control date-picker" placeholder="Select date" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="inline-form-group">
                <label for="edit_reading_date">Reading Date:</label>
                <input type="text" id="edit_reading_date" name="reading_date" class="form-control date-picker" placeholder="Select date" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="inline-form-group">
                <label for="edit_due_date">Due Date:</label>
                <input type="text" id="edit_due_date" name="due_date" class="form-control date-picker" placeholder="Select date" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="inline-form-group">
                <label for="edit_status">Status</label>
                <select id="edit_status" name="status" class="form-control" required>
                    <option value="Open">Open</option>
                    <option value="Close">Close</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closeModal('editCoverageDateModal')">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Update Coverage Date</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteCoverageDateModal" class="modal">
    <div class="modal-content">
        <h3>Delete Coverage Date</h3>
        <p>Are you sure you want to delete this coverage date?</p>
        <div class="modal-actions">
            <button type="button" class="btn_modal btn_cancel" onclick="closeModal('deleteCoverageDateModal')">Cancel</button>
            <button type="button" class="btn_modal btn_delete" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div id="resultModal" class="modal" data-static="true">
    <div class="modal-content result-modal">
        <div id="resultIcon"></div>
        <h3 id="resultTitle"></h3>
        <p id="resultMessage"></p>
        <div class="modal-actions">
            <button class="btn_modal btn_verify" onclick="closeResultModal()">OK</button>
        </div>
    </div>
</div>

<!-- Status Switch Confirmation Modal -->
<div id="statusSwitchModal" class="modal">
    <div class="modal-content">
        <h3>Switch Coverage Status</h3>
        <p>Setting this coverage period as Open will automatically close the currently active coverage period. Are you sure you want to proceed?</p>
        <div class="modal-actions">
            <button type="button" class="btn_modal btn_cancel" onclick="closeModal('statusSwitchModal')">Cancel</button>
            <button type="button" class="btn_modal btn_verify" onclick="confirmStatusSwitch()">Confirm</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/flatpickr.min.js') }}"></script>
<script src="{{ asset('js/covdate.js') }}"></script>
<script>
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof flatpickr === 'function') {
            setTimeout(initializeAllDatePickers, 100);
        }
    });
</script>
@endpush

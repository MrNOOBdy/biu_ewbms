@extends('biu_layout.admin')

@section('title', 'BI-U: Coverage Date Management')

@section('tab-content')
<style>
    .active-header-container,
    .closed-header-container {
        padding: var(--spacing-semi-md);
        border-radius: var(--border-radius-md) var(--border-radius-md) 0 0;
        margin-left: var(--spacing-md);
        width:95%;
        display: flex;
        align-items: center;
        box-shadow: var(--shadow-md);
        color: var(--text-color-light);
    }

    .active-header-container {
        background: var(--success-color);
    }

    .dark-mode .active-header-container {
        background: var(--success-color-dark);
    }

    .closed-header-container {
        background: var(--primary-color-light);
        justify-content: space-between;
    }

    .dark-mode .closed-header-container {
        background: var(--primary-color-dark);
    }

    .active-header,
    .closed-header {
        margin: 0;
        font-size: 1.1rem;
    }

    .active-header i,
    .closed-header i {
        margin-right: var(--spacing-sm);
    }

    .date-range-group {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        margin: 0 var(--spacing-xl);
    }

    .date-range-group input {
        background: var(--bg-overlay-light);
        border: 1px solid var(--border-color-light);
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: var(--border-radius-sm);
        color: var(--text-color-light);
        width: 150px;
    }

    .date-range-group p {
        margin: 0;
        color: var(--text-color-light);
    }

    .filter-btn {
        padding: var(--spacing-sm) var(--spacing-md);
        border: none;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
        transition: var(--transition-fast);
    }

    .filter-reset {
        background: var(--danger-color);
        color: var(--text-color-light);
    }

    .filter-reset:hover {
        background: color-mix(in srgb, var(--danger-color) 85%, black);
    }

    .date-range-group .flatpickr-input::placeholder {
        color: white;
    }

    .date-range-group .flatpickr-input:focus {
        outline: none;
        border-color: var(--primary-color-light);
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.2);
    }
</style>
<link rel="stylesheet" href="{{ asset('css/flatpickr.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">

<div class="table-header">
    <h3><i class="fas fa-calendar-alt"></i> Coverage Date Management</h3>
    <div class="header-controls">
        <div class="date-filter-container">
            @if(Auth::user()->hasPermission('add-coverage-date'))
            <button class="add-btn" type="button" onclick="showAddModal()">
                <i class="fas fa-plus-circle"></i> Add Coverage Date
            </button>
            @endif
        </div>
    </div>
</div>

<!-- Active Coverage Date Section -->
<div class="active-header-container">
    <h4 class="active-header">
        <i class="fas fa-calendar-check"></i>
        Active Coverage Period
    </h4>
</div>
<div class="content-wrapper" style="height: 12%;">
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
                @if ($active_coverage)
                    <tr data-covdate-id="{{ $active_coverage->covdate_id }}" data-status="{{ $active_coverage->status }}">
                        <td>{{ date('M d, Y', strtotime($active_coverage->coverage_date_from)) }}</td>
                        <td>{{ date('M d, Y', strtotime($active_coverage->coverage_date_to)) }}</td>
                        <td>{{ date('M d, Y', strtotime($active_coverage->reading_date)) }}</td>
                        <td>{{ date('M d, Y', strtotime($active_coverage->due_date)) }}</td>
                        <td>
                            <span class="status-badge status-active">
                                {{ $active_coverage->status }}
                            </span>
                        </td>
                        @if(Auth::user()->hasPermission('edit-coverage-date'))
                        <td>
                            <div class="action-buttons">
                                <button class="btn_uni btn-view" onclick="editCoverageDate({{ $active_coverage->covdate_id }})">
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
<div class="closed-header-container" style="margin-top: 1rem;">
    <h4 class="closed-header">
        <i class="fas fa-calendar-times"></i>
        Previous/Closed Coverage Periods
    </h4>
    <div class="date-range-group">
        <input type="text" id="dateFrom" class="form-control flatpickr-input" placeholder="From date">
        <p>to</p>
        <input type="text" id="dateTo" class="form-control flatpickr-input" placeholder="To date">
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
                @forelse ($coverage_dates as $date)
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
        @if($coverage_dates->hasPages())
            <div class="pagination-wrapper">
                {{ $coverage_dates->links('pagination.custom') }}
            </div>
        @endif
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

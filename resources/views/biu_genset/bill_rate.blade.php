@extends('biu_layout.admin')

@section('title', 'BI-U: Bill Rates')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<div class="table-header">
    <h3><i class="fas fa-file-invoice-dollar"></i> Bill Rates</h3>
    <div class="header-controls">
        @if($userRole->hasPermission('add-bill-rate'))
        <button class="add-btn" onclick="BillRateModule.showAddModal()">
            <i class="fas fa-plus"></i> Add Bill Rate Type
        </button>
        @endif
    </div>
</div>

<div class="content-wrapper">
    <div class="table-container">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>Consumer Type</th>
                    <th>Cubic Meter</th>
                    <th>Value</th>
                    <th>Excess Value Per Cubic</th>
                    @if($userRole->hasPermission('edit-bill-rate') || $userRole->hasPermission('delete-bill-rate'))
                        <th>Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($billRates as $billRate)
                    <tr>
                        <td>{{ $billRate->consumer_type }}</td>
                        <td>{{ $billRate->cubic_meter }} m<sup>3</sup></td>
                        <td>₱ {{ $billRate->value }}</td>
                        <td>₱ {{ $billRate->excess_value_per_cubic }}</td>
                        @if($userRole->hasPermission('edit-bill-rate') || $userRole->hasPermission('delete-bill-rate'))
                            <td>
                                <div class="action-buttons">
                                    @if($userRole->hasPermission('edit-bill-rate'))
                                    <button class="btn_uni btn-view" onclick="BillRateModule.editBillRate({{ $billRate->billrate_id }})">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    @endif
                                    @if($userRole->hasPermission('delete-bill-rate'))
                                    <button class="btn_uni btn-deactivate" onclick="BillRateModule.showDeleteBillRateModal({{ $billRate->billrate_id }})">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($userRole->hasPermission('edit-bill-rate') || $userRole->hasPermission('delete-bill-rate')) ? '5' : '4' }}" class="text-center">
                            No bill rates found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Bill Rate Modal -->
<div id="addBillRateModal" class="modal">
    <div class="modal-content">
        <h3>Add New Bill Rate</h3>
        <form id="billRateForm">
            <div class="form-group">
                <label for="consumer_type">Consumer Type</label>
                <input type="text" class="form-control" id="consumer_type" name="consumer_type" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label for="cubic_meter">Cubic Meter (m<sup>3</sup>)</label>
                <input type="number" step="0.01" class="form-control" id="cubic_meter" name="cubic_meter" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label for="value">Value</label>
                <input type="number" step="0.01" class="form-control" id="value" name="value" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label for="excess_value_per_cubic">Excess Value Per Cubic</label>
                <input type="number" step="0.01" class="form-control" id="excess_value_per_cubic" name="excess_value_per_cubic" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="BillRateModule.closeAddModal()">Cancel</button>
                <button type="button" class="btn_modal btn_verify" onclick="BillRateModule.saveBillRate()">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Bill Rate Modal -->
<div id="editBillRateModal" class="modal">
    <div class="modal-content">
        <h3>Edit Bill Rate</h3>
        <form id="editBillRateForm">
            <input type="hidden" id="editBillRateId" name="billrate_id">
            <div class="form-group">
                <label for="edit_consumer_type">Consumer Type</label>
                <input type="text" class="form-control" id="edit_consumer_type" name="consumer_type" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label for="edit_cubic_meter">Cubic Meter (m<sup>3</sup>)</label>
                <input type="number" step="0.01" class="form-control" id="edit_cubic_meter" name="cubic_meter" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label for="edit_value">Value</label>
                <input type="number" step="0.01" class="form-control" id="edit_value" name="value" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label for="edit_excess_value_per_cubic">Excess Value Per Cubic</label>
                <input type="number" step="0.01" class="form-control" id="edit_excess_value_per_cubic" name="excess_value_per_cubic" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="BillRateModule.closeEditModal()">Cancel</button>
                <button type="button" class="btn_modal btn_verify" onclick="BillRateModule.updateBillRate()">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteBillRateModal" class="modal">
    <div class="modal-content">
        <h3>Delete Bill Rate</h3>
        <p>Are you sure you want to delete this bill rate?</p>
        <p>This action cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" class="btn_modal btn_cancel" onclick="BillRateModule.closeDeleteBillRateModal()">Cancel</button>
            <button type="button" class="btn_modal btn_delete" onclick="BillRateModule.confirmDeleteBillRate()">Delete</button>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div id="billRateResultModal" class="modal">
    <div class="modal-content result-modal">
        <div id="billRateResultIcon"></div>
        <h3 id="billRateResultTitle">Success</h3>
        <p id="billRateResultMessage"></p>
        <div class="modal-actions">
            <button type="button" class="btn_modal btn_verify" onclick="BillRateModule.closeResultModal()">OK</button>
        </div>
    </div>
</div>

<!-- Warning Modal -->
<div id="warningBillRateModal" class="modal">
    <div class="modal-content result-modal">
        <div id="warningBillRateIcon" class="warning">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>Warning</h3>
        <p id="warningBillRateMessage"></p>
        <div class="modal-actions">
            <button type="button" class="btn_modal btn_verify" onclick="BillRateModule.closeWarningModal()">OK</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/bill_rate.js') }}"></script>
@endsection

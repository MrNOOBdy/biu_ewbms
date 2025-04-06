@extends('biu_layout.admin')

@section('title', 'BI-U: Water Consumers')

@section('tab-content')
<style>
    .sidebar-collapsed ~ .block-contents .table-container {;
        margin-left: 5%;
        width: 75rem;
    }
</style>
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
<div class="table-header">
    <h3><i class="fas fa-users"></i> Water Consumers</h3>
    <div class="header-controls">
        <div class="filter-section">
            <select id="blockFilter" onchange="filterConsumers()">
                <option value="">All Blocks</option>
                @foreach($blocks as $block)
                    <option value="{{ $block->block_id }}">Block {{ $block->block_id }}</option>
                @endforeach
            </select>
            <select id="statusFilter" onchange="filterConsumers()">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Pending">Pending</option>
            </select>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search consumers...">
            <button class="btn-search" onclick="filterConsumers()">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
        @if($userRole->hasPermission('add-new-consumer'))
            <button class="add-btn" type="button" onclick="showAddConsumerModal()">
                <i class="fas fa-plus-circle"></i> Add New Consumer
            </button>
        @endif
    </div>
</div>

<div class="content-wrapper">
    <div class="table-container">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>Block</th>
                    <th>Consumer ID</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Address</th>
                    <th>Contact No.</th>
                    <th>Consumer Type</th>
                    <th>Status</th>
                    @if($userRole->hasPermission('edit-consumer') || 
                        $userRole->hasPermission('view-consumer-billings') || 
                        $userRole->hasPermission('delete-consumer') || 
                        $userRole->hasPermission('reconnect-consumer'))
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($consumers as $consumer)
                    <tr>
                        <td>Block {{ $consumer->block_id }}</td>
                        <td>{{ $consumer->customer_id }}</td>
                        <td>{{ $consumer->firstname }}</td>
                        <td>{{ $consumer->middlename }}</td>
                        <td>{{ $consumer->lastname }}</td>
                        <td>{{ $consumer->address }}</td>
                        <td>{{ $consumer->contact_no }}</td>
                        <td>{{ $consumer->consumer_type }}</td>
                        <td>
                            <span class="status-badge 
                                {{ $consumer->status === 'Active' ? 'status-active' : 
                                   ($consumer->status === 'Inactive' ? 'status-inactive' : 'status-pending') }}">
                                {{ $consumer->status }}
                            </span>
                        </td>
                        @if($userRole->hasPermission('edit-consumer') || 
                            $userRole->hasPermission('view-consumer-billings') || 
                            $userRole->hasPermission('delete-consumer') || 
                            $userRole->hasPermission('reconnect-consumer'))
                            <td>
                                <div class="action-buttons">
                                    @if($userRole->hasPermission('edit-consumer'))
                                        <button class="btn_uni btn-view" title="View/Edit Consumer" onclick="viewConsumer('{{ $consumer->customer_id }}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endif

                                    @if($consumer->status === 'Active' && $userRole->hasPermission('view-consumer-billings'))
                                        <button class="btn_uni btn-billing" title="View Billings" onclick="viewBillings('{{ $consumer->customer_id }}')">
                                            <i class="fas fa-file-invoice"></i>
                                        </button>
                                    @endif

                                    @if($consumer->status === 'Inactive' && $userRole->hasPermission('reconnect-consumer'))
                                        <button class="btn_uni btn-activate" title="Reconnect Consumer" onclick="showReconnectConfirmationModal('{{ $consumer->customer_id }}')">
                                            <i class="fas fa-plug"></i>
                                        </button>
                                    @endif

                                    @if(($consumer->status === 'Pending' || $consumer->status === 'Inactive') && $userRole->hasPermission('delete-consumer'))
                                        <button class="btn_uni btn-deactivate" title="Delete Consumer" onclick="showDeleteConfirmationModal('{{ $consumer->customer_id }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($userRole->hasAnyPermission(['edit-consumer', 'view-consumer-billings', 'delete-consumer', 'reconnect-consumer'])) ? 10 : 9 }}" class="empty-state">
                            <i class="fas fa-users-slash"></i>
                            <p>No consumers found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $consumers->links('pagination.custom') }}
    </div>
</div>

<!-- Add Consumer Modal -->
<div id="addConsumerModal" class="modal">
    <div class="modal-content large-modal">
        <h3><i class="fas fa-users"></i>Add New Consumer</h3>
        <form id="addConsumerForm" onsubmit="handleConsumerSubmit(event)">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Left Column -->
                <div>
                    <div class="form-group">
                        <label for="block_id">Block Number</label>
                        <select id="block_id" name="block_id" class="form-control" required onchange="updateBarangays()">
                            <option value="">Select Block</option>
                            @foreach($blocks as $block)
                                <option value="{{ $block->block_id }}" data-barangays='@json($block->barangays)'>Block {{ $block->block_id }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="middlename">Middle Name</label>
                        <input type="text" id="middlename" name="middlename" class="form-control">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="street">Street/Purok</label>
                        <input type="text" id="street" name="street" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <div class="form-group">
                        <label for="barangay">Barangay</label>
                        <select id="barangay" name="barangay" class="form-control" required>
                            <option value="">Select Barangay</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="contact_no">Contact Number</label>
                        <input type="text" id="contact_no" name="contact_no" class="form-control" required maxlength="11" pattern="[0-9]{11}">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="consumer_type">Consumer Type</label>
                        <select id="consumer_type" name="consumer_type" class="form-control" required>
                            <option value="">Select Type</option>
                            @foreach($billRates as $rate)
                                <option value="{{ $rate->consumer_type }}">{{ $rate->consumer_type }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group" style="display: none;">
                        <label for="status">Status</label>
                        <input type="hidden" id="status" name="status" value="Pending">
                    </div>

                    <div class="form-group">
                        <label for="application_fee">Application Fee</label>
                        <input type="number" 
                               id="application_fee" 
                               name="application_fee" 
                               class="form-control"
                               value="{{ $fees['Application Fee'] }}"
                               readonly
                               style="background-color: #e9ecef; border: none; color: #495057; cursor: not-allowed; pointer-events: none; opacity: 0.7;">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            <div class="modal-actions" style="margin-top: 20px;">
                <button type="button" class="btn_modal btn_cancel" onclick="closeModal('addConsumerModal')">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Add Consumer</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Consumer Modal -->
<div id="editConsumerModal" class="modal">
    <div class="modal-content large-modal">
        <h3>Edit Consumer</h3>
        <form id="editConsumerForm" onsubmit="handleEditConsumerSubmit(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_customer_id" name="customer_id">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Left Column -->
                <div>
                    <div class="form-group">
                        <label for="edit_block_id">Block Number</label>
                        <select id="edit_block_id" name="block_id" class="form-control" required onchange="updateEditBarangays()">
                            <option value="">Select Block</option>
                            @foreach($blocks as $block)
                                <option value="{{ $block->block_id }}" data-barangays='@json($block->barangays)'>Block {{ $block->block_id }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_firstname">First Name</label>
                        <input type="text" id="edit_firstname" name="firstname" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_middlename">Middle Name</label>
                        <input type="text" id="edit_middlename" name="middlename" class="form-control">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_lastname">Last Name</label>
                        <input type="text" id="edit_lastname" name="lastname" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_street">Street/Purok</label>
                        <input type="text" id="edit_street" name="street" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <div class="form-group">
                        <label for="edit_barangay">Barangay</label>
                        <select id="edit_barangay" name="barangay" class="form-control" required>
                            <option value="">Select Barangay</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_contact_no">Contact Number</label>
                        <input type="text" id="edit_contact_no" name="contact_no" class="form-control" required maxlength="11" pattern="[0-9]{11}">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_consumer_type">Consumer Type</label>
                        <select id="edit_consumer_type" name="consumer_type" class="form-control" required>
                            <option value="">Select Type</option>
                            @foreach($billRates as $rate)
                                <option value="{{ $rate->consumer_type }}">{{ $rate->consumer_type }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select id="edit_status" name="status" class="form-control" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            <div class="modal-actions" style="margin-top: 20px;">
                <button type="button" class="btn_modal btn_cancel" onclick="closeModal('editConsumerModal')">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Update Consumer</button>
            </div>
        </form>
    </div>
</div>

<!-- Result Modal -->
<div id="consumerResultModal" class="modal">
    <div class="modal-content result-modal">
        <div id="consumerResultIcon"></div>
        <h3 id="consumerResultTitle"></h3>
        <p id="consumerResultMessage"></p>
        <div class="modal-actions">
            <button class="btn_modal btn_verify" onclick="closeConsumerResultModal()">OK</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmationModal" class="modal">
    <div class="modal-content delete-modal">
        <h3><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
        <p class="warning-text">You are about to delete this consumer. Please review the following information carefully:</p>
        <div class="confirmation-details">
            <div class="consumer-info" id="deleteConsumerInfo">
                <!-- Consumer info will be populated dynamically -->
            </div>
            <div class="warning-message">
                <p><strong>Important:</strong> This action will:</p>
                <ul>
                    <li>Permanently remove the consumer's profile</li>
                    <li>Delete all associated billing records</li>
                    <li>Remove all payment history</li>
                    <li>Delete any pending fees or charges</li>
                </ul>
                <p class="final-warning">This action cannot be undone. Are you sure you want to proceed?</p>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn_modal btn_cancel" onclick="closeModal('deleteConfirmationModal')">Cancel</button>
            <button type="button" class="btn_modal btn_verify" id="confirmDeleteBtn">Yes, Delete Consumer</button>
        </div>
    </div>
</div>

<!-- Reconnect Confirmation Modal -->
<div id="reconnectConfirmationModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-plug"></i> Confirm Reconnection</h3>
        <div class="confirmation-message">
            <p>You are about to reconnect this consumer's water service.</p>
            <div id="reconnectStatus">
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <p>Checking reconnection fee payment status...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update the paymentRequiredModal -->
<div id="paymentRequiredModal" class="modal">
    <div class="modal-content">
        <div class="result-modal">
            <div id="paymentRequiredIcon" class="warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Payment Required</h3>
            <p>Cannot activate consumer. Application fee must be paid first. Please process the payment in the Application Fees section.</p>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_verify" onclick="closeModal('paymentRequiredModal')">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/wat_consumer.js') }}"></script>
@endsection

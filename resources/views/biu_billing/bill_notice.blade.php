@extends('biu_layout.admin')

@section('title', 'BI-U: Bill Notice')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<div class="table-header">
    <h3><i class="fas fa-file-invoice"></i> Bill Notice</h3>
    <div class="header-controls">
        <button class="add-btn" onclick="openNoticeModal()">
            <i class="fas fa-bell"></i> Send Bill Notice
        </button>
    </div>
</div>

<div class="table-container" style="height: 90%;">
    <div class="select-controls" style="margin-bottom: 15px;">
        <label class="checkbox-container">
            <input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes()">
            <span class="checkmark"></span>
            Select/Unselect All
        </label>
    </div>

    <table class="uni-table">
        <thead>
            <tr>
                <th>
                    <span class="checkbox-header">Select</span>
                </th>
                <th>Consumer ID</th>
                <th>Full Name</th>
                <th>Contact No</th>
                <th>Due Date</th>
                <th>Present Reading</th>
                <th>Consumption</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @if($bills->isEmpty())
                <tr>
                    <td colspan="10" class="text-center">
                        <i class="fas fa-exclamation-circle"></i> No bills available.
                    </td>
                </tr>
            @else
                @foreach($bills as $bill)
                    @php
                        $consumption = $bill->calculateConsumption();
                        $billAmount = $bill->calculateBill();
                    @endphp
                    <tr>
                        <td>
                            <label class="checkbox-container">
                                <input type="checkbox" class="bill-checkbox" value="{{ $bill->consread_id }}">
                                <span class="checkmark"></span>
                            </label>
                        </td>
                        <td>{{ $bill->consumer->customer_id }}</td>
                        <td>{{ $bill->consumer->firstname }} {{ $bill->consumer->lastname }}</td>
                        <td>{{ $bill->consumer->contact_no }}</td>
                        <td>{{ date('M d, Y', strtotime($bill->due_date)) }}</td>
                        <td>{{ $bill->present_reading }}</td>
                        <td>{{ $consumption }}</td>
                        <td>₱{{ number_format($billAmount, 2) }}</td>
                        <td>
                            <span class="badge {{ ($bill->billPayments && $bill->billPayments->bill_status == 'paid') ? 'bg-success' : 'bg-danger' }}">
                                {{ $bill->billPayments ? ucfirst($bill->billPayments->bill_status) : 'Pending' }}
                            </span>
                        </td>
                        <td>
                            <button class="btn_uni btn-view" onclick="viewDetails({{ $bill->consread_id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    @if($bills->hasPages())
        <div class="pagination-wrapper" style="margin-top: 15px;">
            {{ $bills->links('pagination.custom') }}
        </div>
    @endif
</div>

<style>
    .select-controls {
        padding: 10px;
        background: var(--light-bg);
        border-radius: var(--border-radius-sm);
    }

    .checkbox-container {
        display: inline-flex;
        align-items: center;
        position: relative;
        padding-left: 25px;
        margin-right: 15px;
        cursor: pointer;
        font-size: 0.9rem;
        user-select: none;
    }

    .checkbox-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .checkmark {
        position: absolute;
        left: 0;
        height: 18px;
        width: 18px;
        background-color: var(--light-bg);
        border: 2px solid var(--primary-color);
        border-radius: 3px;
    }

    .checkbox-container:hover input ~ .checkmark {
        background-color: var(--hover-bg);
    }

    .checkbox-container input:checked ~ .checkmark {
        background-color: var(--primary-color);
    }

    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    .checkbox-container input:checked ~ .checkmark:after {
        display: block;
    }

    .checkbox-container .checkmark:after {
        left: 5px;
        top: 2px;
        width: 4px;
        height: 8px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .checkbox-header {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .sms-results {
        margin-top: 15px;
        max-height: 200px;
        overflow-y: auto;
    }

    .sms-result-item {
        display: flex;
        justify-content: space-between;
        padding: 8px;
        border-bottom: 1px solid var(--border-color-light);
    }

    .sms-result-item.success {
        color: var(--success-color);
    }

    .sms-result-item.error {
        color: var(--danger-color);
    }
</style>

<!-- Notice Modal -->
<div id="noticeModal" class="modal">
    <div class="modal-content large-modal">
        <h3><i class="fas fa-bell"></i> Send Bill Notice</h3>
        <div class="modal-body">
            <div class="notice-selection">
                <label for="noticeSelect">Select Notice Type</label>
                <select class="form-control" id="noticeSelect">
                    <option value="">Select Announcement</option>
                    @foreach($notices as $notice)
                        <option value="{{ $notice->notice_id }}">{{ $notice->type }} - {{ $notice->announcement }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label for="announcementText">Message</label>
                <textarea 
                    class="form-control" 
                    id="announcementText" 
                    placeholder="Enter your announcement message here..."
                ></textarea>
            </div>
        </div>
        
        <div class="modal-actions">
            <button class="btn_modal btn_cancel" onclick="closeNoticeModal()">Cancel</button>
            <button class="btn_modal btn_verify" onclick="sendNotice()">Send Notice</button>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div id="viewDetailsModal" class="modal">
    <div class="modal-content large-modal">
        <h3><i class="fas fa-info-circle"></i> Bill Details</h3>
        <div class="modal-body">
            <div class="info-group">
                <!-- Consumer Information -->
                <div class="consumer-info">
                    <h4>Consumer Details</h4>
                    <p><strong>Customer ID:</strong> <span id="detail_customerId"></span></p>
                    <p><strong>Name:</strong> <span id="detail_consumerName"></span></p>
                    <p><strong>Contact No:</strong> <span id="detail_contactNo"></span></p>
                    <p><strong>Address:</strong> <span id="detail_address"></span></p>
                    <p><strong>Consumer Type:</strong> <span id="detail_consumerType"></span></p>
                </div>

                <!-- Bill Information -->
                <div class="consumer-info">
                    <h4>Reading Details</h4>
                    <p><strong>Reading Date:</strong> <span id="detail_readingDate"></span></p>
                    <p><strong>Due Date:</strong> <span id="detail_dueDate"></span></p>
                    <p><strong>Previous Reading:</strong> <span id="detail_previousReading"></span></p>
                    <p><strong>Present Reading:</strong> <span id="detail_presentReading"></span></p>
                    <p><strong>Consumption:</strong> <span id="detail_consumption"></span>m³</p>
                    <p><strong>Base Rate:</strong> <span id="detail_baseRate"></span></p>
                    <p><strong>Excess Charges:</strong> <span id="detail_excessCharges"></span></p>
                    <p><strong>Total Amount:</strong> ₱<span id="detail_billAmount"></span></p>
                    <p><strong>Bill Status:</strong> <span id="detail_billStatus"></span></p>
                </div>
            </div>
        </div>
        
        <div class="modal-actions">
            <button class="btn_modal btn_cancel" onclick="closeViewDetailsModal()">Close</button>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div id="confirmModal" class="modal">
    <div class="modal-content result-modal">
        <h3><i class="fas fa-question-circle"></i> Confirm Action</h3>
        <p id="confirmMessage"></p>
        <div class="modal-actions">
            <button class="btn_modal btn_cancel" onclick="closeConfirmModal(false)">Cancel</button>
            <button class="btn_modal btn_verify" onclick="closeConfirmModal(true)">Confirm</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/bill_notice.js') }}"></script>
@endsection
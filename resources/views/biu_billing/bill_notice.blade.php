@extends('biu_layout.admin')

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

<div class="table-container" style="height: 82%;">
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
                        <td>{{ $bill->consumption }}</td>
                        <td>₱{{ number_format($bill->billPayments->total_amount ?? 0, 2) }}</td>
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
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fas fa-bell"></i> Send Bill Notice</h3>
        </div>
        
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
            
            <div class="form-group" style="margin-top: 15px;">
                <label for="announcementText">Message</label>
                <textarea 
                    class="announcement-textarea" 
                    id="announcementText" 
                    placeholder="Enter your announcement message here..."
                    style="max-height: 200px;"
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
    <div class="modal-content" style="max-width: 500px; width: 90%; padding: 15px; background: var(--background-color); border-radius: var(--border-radius-md); box-shadow: var(--shadow-md);">
        <div class="modal-header" style="margin-bottom: 15px;">
            <h3 style="color: var(--primary-color); font-size: 1.2rem;"><i class="fas fa-info-circle" style="margin-right: 8px;"></i> Bill Details</h3>
        </div>
        
        <div class="modal-body" style="font-size: 0.9rem;">
            <div class="info-group" style="display: flex; flex-wrap: wrap; gap: 15px;">
                <!-- Consumer Information -->
                <div class="consumer-info" style="flex: 1; min-width: 250px; background: var(--light-bg); padding: 10px; border-radius: var(--border-radius-sm);">
                    <h4 style="font-size: 1rem; margin-bottom: 10px;">Consumer Details</h4>
                    <p style="margin: 5px 0; display: flex; justify-content: space-between;"><strong style="color: var(--text-muted);">Customer ID:</strong> <span id="detail_customerId"></span></p>
                    <p style="margin: 5px 0; display: flex; justify-content: space-between;"><strong style="color: var(--text-muted);">Name:</strong> <span id="detail_consumerName"></span></p>
                    <p style="margin: 5px 0; display: flex; justify-content: space-between;"><strong style="color: var(--text-muted);">Contact No:</strong> <span id="detail_contactNo"></span></p>
                    <p style="margin: 5px 0; display: flex; justify-content: space-between;"><strong style="color: var(--text-muted);">Address:</strong> <span id="detail_address"></span></p>
                    <p style="margin: 5px 0; display: flex; justify-content: space-between;"><strong style="color: var(--text-muted);">Consumer Type:</strong> <span id="detail_consumerType"></span></p>
                </div>

                <!-- Bill Information -->
                <div class="bill-details" style="flex: 1; min-width: 250px; background: var(--light-bg); padding: 10px; border-radius: var(--border-radius-sm);">
                    <h4 style="font-size: 1rem; margin-bottom: 10px;">Reading Details</h4>
                    <p style="margin: 5px 0; display: flex; justify-content: space-between;"><strong style="color: var(--text-muted);">Reading Date:</strong> <span id="detail_readingDate"></span></p>
                    <p style="margin: 5px 0; display: flex; justify-content: space-between;"><strong style="color: var(--text-muted);">Due Date:</strong> <span id="detail_dueDate"></span></p>
                    <p style="margin: 5px 0; display: flex; justify-content: space-between;"><strong style="color: var(--text-muted);">Previous Reading:</strong> <span id="detail_previousReading"></span></p>
                    <p style="margin: 5px 0; display: flex; justify-content: space-between;"><strong style="color: var(--text-muted);">Present Reading:</strong> <span id="detail_presentReading"></span></p>
                    <p style="margin: 5px 0; display: flex; justify-content: space-between;"><strong style="color: var(--text-muted);">Consumption:</strong> <span id="detail_consumption"></span> m³</p>
                    <p style="margin: 5px 0; display: flex; justify-content: space-between;"><strong style="color: var(--text-muted);">Bill Amount:</strong> ₱<span id="detail_billAmount"></span></p>
                    <p style="margin: 5px 0; display: flex; justify-content: space-between;"><strong style="color: var (--text-muted);">Bill Status:</strong> <span id="detail_billStatus"></span></p>
                </div>
            </div>
        </div>
        
        <div class="modal-actions" style="margin-top: 15px; display: flex; justify-content: flex-end;">
            <button class="btn_modal btn_cancel" onclick="closeViewDetailsModal()" style="padding: 6px 15px; background: var(--danger-color); color: white; border: none; border-radius: var(--border-radius-sm); cursor: pointer;">Close</button>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div id="confirmModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3><i class="fas fa-question-circle"></i> Confirm Action</h3>
        </div>
        
        <div class="modal-body">
            <p id="confirmMessage" style="margin: 20px 0; text-align: center;"></p>
        </div>
        
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
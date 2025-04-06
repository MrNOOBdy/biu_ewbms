@extends('biu_layout.admin')

@section('title', 'BI-U: Latest Bills')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<style>
    .modal-content {
        padding: 15px;
        max-height: none;
        overflow-y: visible;
        max-width: 800px;
        width: 90%;
        display: flex;
        flex-direction: column;
    }

    .modal-body {
        padding: 0;
        flex-grow: 1;
    }

    .info-group {
        font-size: 0.9rem;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: space-between;
    }

    .consumer-info,
    .coverage-info,
    .bill-details {
        flex: 1;
        min-width: 300px;
        background: var(--light-bg);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
    }

    .consumer-info p,
    .coverage-info p,
    .bill-details p {
        display: flex;
        justify-content: space-between;
        margin: 8px 0;
        padding: 4px 0;
        border-bottom: 1px solid var(--border-color-light);
    }

    .consumer-info p:last-child,
    .coverage-info p:last-child,
    .bill-details p:last-child {
        border-bottom: none;
    }

    .modal-actions {
        margin-top: 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    @media (max-width: 900px) {
        .info-group {
            flex-direction: column;
        }
        
        .consumer-info,
        .coverage-info,
        .bill-details {
            min-width: 100%;
        }
    }
</style>

<div class="table-header">
    <h3><i class="fas fa-file-invoice"></i> Latest Bills</h3>
    <div class="header-controls">
        <div class="filter-section">
            <select id="statusFilter" onchange="filterBills()">
                <option value="">All Unconfirmed Bills</option>
                <option value="Pending">Pending Confirmation</option>
                <option value="unpaid">Confirmed Bills</option>
            </select>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search bills...">
            <button class="btn-search" onclick="filterBills()">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <div class="table-container">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>Consumer ID</th>
                    <th>Contact No.</th>
                    <th>Name</th>
                    <th>Reading Date</th>
                    <th>Due Date</th>
                    <th>Prev. Reading</th>
                    <th>Pres. Reading</th>
                    <th>Consumption m<sup>3</sup></th>
                    <th>Bill Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                    @php
                        $billPayment = \App\Models\ConsBillPay::where('consread_id', $bill->consread_id)->first();
                    @endphp
                    <tr>
                        <td>{{ $bill->consumer->customer_id }}</td>
                        <td>{{ $bill->consumer->contact_no }}</td>
                        <td>{{ $bill->consumer->firstname }} {{ $bill->consumer->lastname }}</td>
                        <td>{{ date('M d, Y', strtotime($bill->reading_date)) }}</td>
                        <td>{{ date('M d, Y', strtotime($bill->due_date)) }}</td>
                        <td>{{ $bill->previous_reading }}</td>
                        <td>{{ $bill->present_reading }}</td>
                        <td>{{ $bill->consumption }}</td>
                        <td>
                            <span class="status-badge {{ !$billPayment ? 'status-pending' : ($billPayment->bill_status == 'paid' ? 'status-active' : 'status-inactive') }}">
                                {{ !$billPayment ? 'Pending' : $billPayment->bill_status }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                @if(!$billPayment)
                                    <button class="btn_uni btn-view" onclick="showAddBillModal({{ $bill->consread_id }})">
                                        <i class="fas fa-plus-circle"></i> Add Bill
                                    </button>
                                @else
                                    <button class="btn_uni btn-billing" onclick="sendBill({{ $bill->consread_id }})">
                                        <i class="fas fa-paper-plane"></i> Send Bill SMS
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            <p>No bills found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $bills->links('pagination.custom') }}
    </div>
</div>

<!-- Add Bill Modal -->
<div id="addBillModal" class="modal">
    <div class="modal-content" style="max-width: 800px; width: 90%;">
        <h3><i class="fas fa-file-invoice"></i> Add Bill Details</h3>
        <div class="modal-body">
            <input type="hidden" id="consread_id">

            <div class="info-group" style="display: flex; flex-wrap: wrap; gap: 20px;">
                <!-- Consumer Information -->
                <div class="consumer-info" style="flex: 1; min-width: 300px;">
                    <p><strong>Customer ID:</strong> <span id="customerId"></span></p>
                    <p><strong>Consumer Name:</strong> <span id="consumerName"></span></p>
                    <p><strong>Contact No:</strong> <span id="contactNo"></span></p>
                    <p><strong>Consumer Type:</strong> <span id="consumerType"></span></p>
                    <p><strong>Previous Bill Status:</strong> <span id="prevBillStatus"></span></p>
                </div>

                <!-- Coverage Dates -->
                <div class="coverage-info" style="flex: 1; min-width: 300px;">
                    <p><strong>Coverage From:</strong> <span id="coverageDateFrom"></span></p>
                    <p><strong>Coverage To:</strong> <span id="coverageDateTo"></span></p>
                </div>

                <!-- Bill Details -->
                <div class="bill-details" style="flex: 1; min-width: 300px;">
                    <p><strong>Reading Date:</strong> <span id="readingDate"></span></p>
                    <p><strong>Due Date:</strong> <span id="dueDate"></span></p>
                    <p><strong>Previous Reading:</strong> <span id="previousReading"></span></p>
                    <p><strong>Present Reading:</strong> <span id="presentReading"></span></p>
                    <p><strong>Consumption:</strong> <span id="consumption"></span></p>
                    <p><strong>Total Amount:</strong> ₱<span id="totalAmount"></span></p>
                </div>
            </div>
        </div>

        <div class="modal-actions" style="padding: 15px; border-top: 1px solid var(--border-color-light);">
            <button class="btn_modal btn_cancel" onclick="closeModal('addBillModal')">Cancel</button>
            <button class="btn_modal btn_verify" onclick="addBill()">Confirm</button>
        </div>
    </div>
</div>


<!-- Send Bill Modal -->
<div id="sendBillModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-paper-plane"></i> Send Bill Notification</h3>
        <div class="modal-body">
            <input type="hidden" id="send_consread_id">
            <div class="consumer-info">
                <p><strong>Consumer Name:</strong> <span id="sms_consumerName"></span></p>
                <p><strong>Contact No:</strong> <span id="sms_contactNo"></span></p>
                <p><strong>Present Reading:</strong> <span id="sms_presentReading"></span> m³</p>
                <p><strong>Consumption:</strong> <span id="sms_consumption"></span>m³</p>
                <div class="form-group">    
                    <label for="sms_message">Message Preview:</label>
                    <textarea id="sms_message" class="form-control" readonly></textarea>
                </div>
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn_modal btn_cancel" onclick="closeModal('sendBillModal')">Cancel</button>
            <button class="btn_modal btn_verify" onclick="confirmSendBill()">Send SMS</button>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div id="billResultModal" class="modal">
    <div class="modal-content">
        <div class="result-modal">
            <div id="billResultIcon"></div>
            <h3 id="billResultTitle"></h3>
            <p id="billResultMessage"></p>
            <div class="modal-actions">
                <button class="btn_modal btn_verify" onclick="closeBillResultModal()">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/latest_bills.js') }}"></script>
@endsection
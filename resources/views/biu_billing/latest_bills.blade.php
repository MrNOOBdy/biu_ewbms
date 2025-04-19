@extends('biu_layout.admin')

@section('title', 'BI-U: Latest Bills')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<style>
    .lg-modal {
        padding: 15px;
        min-height: 85vh;
        overflow-y: visible;
        max-width: 800px;
        width: 90%;
        display: flex;
        flex-direction: column;
    }
</style>

<div class="table-header">
    <h3><i class="fas fa-file-invoice"></i> Latest Bills</h3>
    @if($currentCoverage)
    <div class="coverage-period">
        <p>Coverage Period: {{ date('M d, Y', strtotime($currentCoverage->coverage_date_from)) }} - {{ date('M d, Y', strtotime($currentCoverage->coverage_date_to)) }}</p>
    </div>
    <input type="hidden" id="currentCoverageId" value="{{ $currentCoverage->covdate_id }}">
    @endif
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
        @if(!$currentCoverage)
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <p>No active coverage period found. Please set an active coverage period first.</p>
            </div>
        @else
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
                        <th>Amount</th>
                        <th>Bill Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                        @php
                            $billPayment = \App\Models\ConsBillPay::where('consread_id', $bill->consread_id)->first();
                            $consumption = $bill->calculateConsumption();
                            $totalAmount = $bill->calculateBill();
                        @endphp
                        <tr>
                            <td>{{ $bill->consumer->customer_id }}</td>
                            <td>{{ $bill->consumer->contact_no }}</td>
                            <td>{{ $bill->consumer->firstname }} {{ $bill->consumer->lastname }}</td>
                            <td>{{ date('M d, Y', strtotime($bill->reading_date)) }}</td>
                            <td>{{ date('M d, Y', strtotime($bill->due_date)) }}</td>
                            <td>{{ $bill->previous_reading }}</td>
                            <td>{{ $bill->present_reading }}</td>
                            <td>{{ $consumption }}</td>
                            <td>₱{{ number_format($totalAmount, 2) }}</td>
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
                                        <button class="btn_uni btn-billing {{ $bill->sms_sent ? 'disabled' : '' }}" 
                                                onclick="sendBill({{ $bill->consread_id }})"
                                                {{ $bill->sms_sent ? 'disabled title=SMS already sent' : '' }}
                                                style="{{ $bill->sms_sent ? 'cursor: not-allowed; opacity: 0.6;' : '' }}">
                                            <i class="fas fa-paper-plane"></i> {{ $bill->sms_sent ? 'SMS Sent' : 'Send Bill SMS' }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="empty-state">
                                <i class="fas fa-file-invoice"></i>
                                <p>No bills found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
        {{ $bills->links('pagination.custom') }}
    </div>
</div>

<!-- Add Bill Modal -->
<div id="addBillModal" class="modal">
    <div class="modal-content lg-modal" style="max-width: 800px; width: 90%;">
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

                <!-- Current Bill Details -->
                <div class="bill-details" style="flex: 1; min-width: 300px;">
                    <h4>Current Bill</h4>
                    <p><strong>Reading Date:</strong> <span id="readingDate"></span></p>
                    <p><strong>Due Date:</strong> <span id="dueDate"></span></p>
                    <p><strong>Previous Reading:</strong> <span id="previousReading"></span></p>
                    <p><strong>Present Reading:</strong> <span id="presentReading"></span></p>
                    <p><strong>Consumption:</strong> <span id="consumption"></span>m³</p>
                    <p><strong>Current Bill Amount:</strong> ₱<span id="currentBillAmount"></span></p>
                </div>

                <!-- Last Month's Unpaid Bill -->
                <div id="lastMonthUnpaidSection" class="last-month-unpaid" style="flex: 1; min-width: 300px; display: none; border-top: 1px solid #ddd; margin-top: 15px; padding-top: 15px;">
                    <h4 style="color: #dc3545;"><i class="fas fa-exclamation-circle"></i> Last Month's Unpaid Bill</h4>
                    <p><strong>Reading Date:</strong> <span id="lastMonthReadingDate"></span></p>
                    <p><strong>Due Date:</strong> <span id="lastMonthDueDate"></span></p>
                    <p><strong>Consumption:</strong> <span id="lastMonthConsumption"></span>m³</p>
                    <p><strong>Amount Due:</strong> ₱<span id="lastMonthAmount"></span></p>
                    <p><strong>Penalty:</strong> ₱<span id="lastMonthPenalty"></span></p>
                </div>

                <!-- Total Combined Amount -->
                <div class="total-amount" style="flex: 1 100%; margin-top: 20px; padding-top: 20px; border-top: 2px solid #333;">
                    <h3 style="color: #333; margin-bottom: 10px;">Total Amount Due</h3>
                    <p style="font-size: 1.2em;"><strong>Total Amount:</strong> ₱<span id="totalAmount"></span></p>
                    <p class="text-muted" style="font-size: 0.9em;">(Includes current bill, previous unpaid bill, and penalties if applicable)</p>
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
    <div class="modal-content lg-modal">
        <h3><i class="fas fa-paper-plane"></i> Send Bill Notification</h3>
        <div class="modal-body">
            <input type="hidden" id="send_consread_id">
            <div class="consumer-info">
                <p><strong>Consumer Name:</strong> <span id="sms_consumerName"></span></p>
                <p><strong>Contact No:</strong> <span id="sms_contactNo"></span></p>
                <p><strong>Present Reading:</strong> <span id="sms_presentReading"></span></p>
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
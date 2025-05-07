@extends('biu_layout.admin')

@section('title', 'BI-U: Latest Bills')

@section('tab-content')
<style>
    /* Modal Override */
    .modal-content.large-modal {
    max-width: 900px;
    width: 95%;
    padding: 1.5rem;
    border-radius: 1rem;
    background: #fdfdfd;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    font-family: 'Segoe UI', sans-serif;
    }

    /* Header */
    .modal-content.large-modal h3 {
    margin-bottom: 1rem;
    font-size: 1.75rem;
    color: #333;
    border-bottom: 2px solid #eee;
    padding-bottom: 0.5rem;
    }

    /* Body Grid */
    .info-group {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(240px,1fr));
    gap: 1.25rem;
    margin-top: 1rem;
    }

    /* Card style for each section */
    .info-group > div {
    background: #fff;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #ececec;
    }

    /* Section Titles */
    .info-group h4 {
    margin-top: 0;
    font-size: 1.25rem;
    color: #444;
    border-bottom: 1px dashed #ddd;
    padding-bottom: 0.25rem;
    margin-bottom: 0.75rem;
    }

    /* Key/Value lines */
    .consumer-info p,
    .bill-details p,
    .coverage-info p,
    .last-month-unpaid p,
    .total-amount p {
    margin: 0.4rem 0;
    font-size: 0.95rem;
    color: #555;
    }
    .consumer-info p strong,
    .bill-details p strong {
    color: #333;
    }

    /* Warning Card (Unpaid Bill) */
    .last-month-unpaid {
    border-left: 4px solid #e05c5c;
    background: #fff5f5;
    }
    .last-month-unpaid h4 {
    color: #b94a48;
    }
    .last-month-unpaid i {
    color: #b94a48;
    }

    /* Total Amount Card */
    .total-amount {
    grid-column: span 2; /* make it wider */
    text-align: center;
    background: #f0f8ff;
    border-left: 4px solid #4a90e2;
    }
    .total-amount h3 {
    margin: 0;
    color: #2a5d9f;
    font-size: 1.5rem;
    }
    .total-amount-value {
    font-size: 1.4rem;
    margin: 0.5rem 0;
    }
    .total-amount .text-muted {
    font-size: 0.8rem;
    color: #888;
    }

    /* Actions */
    .modal-actions {
    margin-top: 1.5rem;
    text-align: right;
    }
    .modal-actions .btn_modal {
    padding: 0.6rem 1.4rem;
    border-radius: 0.4rem;
    font-size: 0.95rem;
    margin-left: 0.5rem;
    }
    .btn_cancel {
    background: #fff;
    border: 1px solid #ccc;
    color: #333;
    }
    .btn_verify {
    background: #4a90e2;
    border: none;
    color: #fff;
    }
    .btn_cancel:hover { background: #fafafa; }
    .btn_verify:hover { background: #4078c0; }
</style>
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
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
                        <tr data-reading-id="{{ $bill->consread_id }}">
                            <td>{{ $bill->consumer->customer_id }}</td>
                            <td>{{ $bill->consumer->contact_no }}</td>
                            <td>{{ $bill->consumer->firstname }} {{ $bill->consumer->lastname }}</td>
                            <td>{{ date('M d, Y', strtotime($bill->reading_date)) }}</td>
                            <td>{{ date('M d, Y', strtotime($bill->due_date)) }}</td>
                            <td class="previous-reading">{{ $bill->previous_reading }}</td>
                            <td class="present-reading">{{ $bill->present_reading }}</td>
                            <td class="consumption">{{ $consumption }}</td>
                            <td class="bill-amount">₱{{ number_format($totalAmount, 2) }}</td>
                            <td>
                                <span class="status-badge {{ !$billPayment ? 'status-pending' : ($billPayment->bill_status == 'paid' ? 'status-active' : 'status-inactive') }}">
                                    {{ !$billPayment ? 'Pending' : $billPayment->bill_status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @if(!$billPayment)
                                        <button class="btn_uni btn-edit" onclick="editReading({{ $bill->consread_id }}, {{ $bill->present_reading }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn_uni btn-view" onclick="showAddBillModal({{ $bill->consread_id }})">
                                            <i class="fas fa-plus-circle"></i> Add Bill
                                        </button>
                                    @else
                                        <button class="btn_uni btn-billing {{ $bill->sms_sent ? 'disabled' : '' }}" 
                                                onclick="sendBill({{ $bill->consread_id }})"
                                                {{ $bill->sms_sent ? 'disabled title=SMS already sent' : '' }}>
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
    <div class="modal-content large-modal">
        <div class="modal-body">
            <input type="hidden" id="consread_id">

            <!-- Consumer Details Grid -->
            <div class="bill-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                <!-- Left Column -->
                <div class="info-section">
                    <div class="consumer-info" style="background-color: transparent; border: none; padding: 0;">
                        <p><strong style="width: 100px;">Cust. ID:</strong> <span id="customerId"></span></p>
                        <p><strong style="width: 100px;">Block No:</strong> <span id="consumerBlock"></span></p>
                        <p><strong style="width: 100px;">First Name:</strong> <span id="consumerFirstName"></span></p>
                        <p><strong style="width: 100px;">Last Name:</strong> <span id="consumerLastName"></span></p>
                    </div>
                </div>
                <!-- Right Column -->
                <div class="meter-info">
                    <div class="consumer-info" style="background-color: transparent; border: none; padding: 0;">
                        <p><strong style="width: 100px;">From:</strong> <span id="coverageDateFrom"></span></p>
                        <p><strong style="width: 100px;">To:</strong> <span id="coverageDateTo"></span></p>
                        <p><strong style="width: 100px;">Reading:</strong> <span id="readingDate"></span></p>
                        <p><strong style="width: 100px;">Due Date:</strong> <span id="dueDate"></span></p>
                    </div>
                </div>
            </div>

            <!-- Readings Section -->
            <div class="readings-section" style="margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <tr style="background-color: #f8f9fa;">
                        <th style="padding: 8px; border: 1px solid #ddd;">Previous</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Present</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Cons.</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Base Rate (10)</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Excess</th>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><span id="previousReading"></span></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><span id="presentReading"></span></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><span id="consumption"></span></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><span id="baseRateValue"></span></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><span id="excessValue"></span></td>
                    </tr>
                </table>
            </div>

            <!-- Billing Summary -->
            <div class="billing-summary" style="margin-bottom: 20px;">
                <!-- Current Bill -->
                <div style="display: flex; justify-content: space-between; padding: 10px; background-color: #f8f9fa;">
                    <span style="font-weight: 500;">Current Bill Amount:</span>
                    <span>₱<span id="currentBillAmount"></span></span>
                </div>

                <!-- Previous Balance if exists -->
                <div id="lastMonthUnpaidSection" style="display: none;">
                    <div style="display: flex; justify-content: space-between; padding: 10px; border-top: 1px solid #ddd;">
                        <span style="font-weight: 500;">Previous Balance:</span>
                        <span>₱<span id="lastMonthAmount">0.00</span></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px; border-top: 1px solid #ddd;">
                        <span style="font-weight: 500;">Penalty:</span>
                        <span>₱<span id="lastMonthPenalty">0.00</span></span>
                    </div>
                </div>

                <!-- Total Amount -->
                <div style="display: flex; justify-content: space-between; padding: 10px; border-top: 2px solid #000; font-weight: bold; margin-top: 10px;">
                    <span>Total Amount:</span>
                    <span>₱<span id="totalAmount">0.00</span></span>
                </div>
            </div>
        </div>

        <div class="modal-actions">
            <button class="btn_modal btn_cancel" onclick="closeModal('addBillModal')">Cancel</button>
            <button class="btn_modal btn_verify" onclick="addBill()">Confirm</button>
        </div>
    </div>
</div>

<!-- Send Bill Modal -->
<div id="sendBillModal" class="modal">
    <div class="modal-content large-modal">
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

<!-- Edit Reading Modal -->
<div id="editReadingModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-edit"></i> Edit Meter Reading</h3>
        <div class="modal-body">
            <form id="editReadingForm" onsubmit="updateReading(event)">
                @csrf
                <input type="hidden" id="editReadingId">
                <div class="form-group">
                    <label for="editPresentReading">Present Reading</label>
                    <input type="number" id="editPresentReading" name="present_reading" class="form-control" required min="0" step="1">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn_modal btn_cancel" onclick="closeModal('editReadingModal')">Cancel</button>
                    <button type="submit" class="btn_modal btn_verify">Update Reading</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div id="billResultModal" class="modal">
    <div class="modal-content result-modal">
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
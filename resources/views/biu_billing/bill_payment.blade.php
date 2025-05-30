@extends('biu_layout.admin')

@section('title', 'BI-U: Bill Payment')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<div class="table-header">
    <h3><i class="fas fa-money-bill-wave"></i> Bill Payment</h3>
    @if($currentCoverage)
    <div class="coverage-period">
        <p>Coverage Period: {{ date('M d, Y', strtotime($currentCoverage->coverage_date_from)) }} - {{ date('M d, Y', strtotime($currentCoverage->coverage_date_to)) }}</p>
    </div>
    <input type="hidden" id="currentCoverageId" value="{{ $currentCoverage->covdate_id }}">
    @endif
    <div class="header-controls">
        <div class="filter-section">
            <select id="statusFilter" onchange="filterBills()">
                <option value="">All Bills</option>
                <option value="paid">Paid Bills</option>
                <option value="unpaid">Unpaid Bills</option>
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
                        <th>Name</th>
                        <th>Due Date</th>
                        <th>Previous Reading</th>
                        <th>Present Reading</th>
                        <th>Consumption</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                        @php
                            $totalAmount = $bill->calculateBill();
                        @endphp
                        <tr>
                            <td>{{ $bill->consumer->customer_id }}</td>
                            <td>{{ $bill->consumer->firstname }} {{ $bill->consumer->lastname }}</td>
                            <td>{{ date('M d, Y', strtotime($bill->due_date)) }}</td>
                            <td>{{ $bill->previous_reading }}</td>
                            <td>{{ $bill->present_reading }}</td>
                            <td>{{ $bill->calculateConsumption() }}</td>
                            <td>₱{{ number_format($totalAmount, 2) }}</td>
                            <td>
                                <span class="status-badge {{ $bill->billPayments->bill_status == 'paid' ? 'status-active' : 'status-pending' }}">
                                    {{ ucfirst($bill->billPayments->bill_status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @if($bill->billPayments->bill_status == 'unpaid')
                                        <button class="btn_uni btn-billing" onclick="handlePayment('{{ $bill->consread_id }}')">
                                            <i class="fas fa-money-bill-wave"></i> Pay
                                        </button>
                                    @endif
                                    @if($bill->billPayments->bill_status == 'paid')
                                    <button class="btn_uni btn-billing" title="Print Bill" onclick="printBill('{{ $bill->consread_id }}')">
                                        <i class="fas fa-print"></i>Print
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-state">
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

<!-- Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-money-bill-wave"></i> Process Payment</h3>
        <form id="paymentForm" onsubmit="processPayment(event)">
            @csrf
            <input type="hidden" id="billId" name="bill_id">
            <div class="form-group">
                <label>Current Bill Amount</label>
                <input type="text" id="present_reading" class="form-control" readonly>
            </div>
            <!-- Last unpaid amount will be dynamically inserted here -->
            <div class="form-group">
                <label>Penalty Amount</label>
                <input type="text" id="penalty_amount" name="penalty_amount" class="form-control" readonly>
                <small class="text-muted penalty-info" style="display: none;">
                    <i class="fas fa-info-circle"></i> ₱20 penalty applied due to unpaid bill/past due date
                </small>
            </div>
            <div class="form-group">
                <label>Total Amount to Pay</label>
                <input type="text" id="total_amount" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label>Amount Tendered</label>
                <input type="number" id="bill_tendered_amount" name="bill_tendered_amount" class="form-control" step="0.01" min="0">
                <div class="invalid-feedback"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closeModal('paymentModal')">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Process Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- Result Modal -->
<div id="paymentResultModal" class="modal">
    <div class="modal-content">
        <div class="result-modal">
            <div id="paymentResultIcon"></div>
            <h3 id="paymentResultTitle"></h3>
            <p id="paymentResultMessage"></p>
            <div class="modal-actions">
                <button class="btn_modal btn_verify" onclick="closeModal('paymentResultModal')">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/billpay.js') }}"></script>
@endsection
@extends('biu_layout.admin')

@section('title', 'BI-U: Application Fee Payments')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<div class="table-header">
    <h3><i class="fas fa-file-invoice-dollar"></i> Application Fee Payments</h3>
    <div class="header-controls">
        <div class="filter-section">
            <select id="blockFilter" onchange="filterApplications()">
                <option value="">All Blocks</option>
                @foreach($blocks as $block)
                    <option value="{{ $block->block_id }}">Block {{ $block->block_id }}</option>
                @endforeach
            </select>
            <select id="statusFilter" onchange="filterApplications()">
                <option value="">All Status</option>
                <option value="paid">Paid</option>
                <option value="unpaid">Unpaid</option>
            </select>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search applications..." onkeyup="filterApplications()">
            <i class="fas fa-search search-icon"></i>
        </div>
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
                    <th>Application Fee</th>
                    <th>Amount Paid</th>
                    <th>Status</th>
                    @if($userRole->hasPermission('process-application-payment') || 
                        $userRole->hasPermission('print-application'))
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($connPayments as $payment)
                    @if($payment->consumer)
                        <tr data-customer-id="{{ $payment->customer_id }}">
                            <td>Block {{ $payment->consumer->block_id ?? 'N/A' }}</td>
                            <td>{{ $payment->customer_id }}</td>
                            <td>{{ $payment->consumer->firstname ?? 'N/A' }}</td>
                            <td>{{ $payment->consumer->middlename ?? 'N/A' }}</td>
                            <td>{{ $payment->consumer->lastname ?? 'N/A' }}</td>
                            <td>₱{{ number_format($payment->application_fee, 2) }}</td>
                            <td>₱{{ number_format($payment->conn_amount_paid, 2) }}</td>
                            <td>
                                <span class="status-badge {{ $payment->conn_pay_status === 'paid' ? 'status-active' : 'status-inactive' }}">
                                    {{ ucfirst($payment->conn_pay_status) }}
                                </span>
                            </td>
                            @if($userRole->hasPermission('process-application-payment') || 
                                $userRole->hasPermission('print-application'))
                                <td>
                                    <div class="action-buttons">
                                        @if($payment->conn_pay_status === 'unpaid' && $userRole->hasPermission('process-application-payment'))
                                            <button class="btn_uni btn-activate" title="Pay Application Fee" onclick="showPaymentModal('{{ $payment->customer_id }}')">
                                                <i class="fas fa-money-bill-wave"></i>Pay
                                            </button>
                                        @endif
                                        @if($userRole->hasPermission('print-application') && $payment->conn_pay_status === 'paid')
                                            <button class="btn_uni btn-billing" title="Print Receipt" onclick="printReceipt('{{ $payment->customer_id }}')">
                                                <i class="fas fa-print"></i>Print
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ ($userRole->hasPermission('process-application-payment') || 
                                       $userRole->hasPermission('print-application')) ? 9 : 8 }}" class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            <p>No application payments found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-money-bill-wave"></i> Process Payment</h3>
        <form id="paymentForm" onsubmit="return handlePayment(event)">
            @csrf
            <input type="hidden" id="paymentCustomerId" name="customer_id">
            <div class="form-group">
                <label class="unauth-label">Application Fee</label>
                <input type="text" id="applicationFee" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label class="unauth-label">Amount Tendered</label>
                <input type="number" id="amountTendered" name="amount_tendered" class="form-control" required step="0.01" min="0">
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
<div id="paymentResultModal" class="modal" data-refresh="false">
    <div class="modal-content">
        <div class="result-modal">
            <div id="paymentResultIcon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 id="paymentResultTitle"></h3>
            <p id="paymentResultMessage"></p>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_verify" onclick="closePaymentResultModal()">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/conn_pay.js') }}"></script>
@endsection

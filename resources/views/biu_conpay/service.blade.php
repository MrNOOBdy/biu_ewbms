@extends('biu_layout.admin')

@section('title', 'BI-U: Service Fee Payments')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<div class="table-header">
    <h3><i class="fas fa-tools"></i> Service Fee Payments</h3>
    <div class="header-controls">
        <div class="filter-section">
            <select id="blockFilter" onchange="filterServices()">
                <option value="">All Blocks</option>
                @foreach($blocks as $block)
                    <option value="{{ $block->block_id }}">Block {{ $block->block_id }}</option>
                @endforeach
            </select>
            <select id="statusFilter" onchange="filterServices()">
                <option value="">All Status</option>
                <option value="paid">Paid</option>
                <option value="unpaid">Unpaid</option>
            </select>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search reconnections..." onkeyup="filterServices()">
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
                    <th>Reconnection Fee</th>
                    <th>Amount Paid</th>
                    <th>Status</th>
                    @if($userRole->hasPermission('service-pay') || 
                        $userRole->hasPermission('service-print'))
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($servicePayments as $payment)
                    @if($payment->consumer)
                        <tr data-customer-id="{{ $payment->customer_id }}">
                            <td>Block {{ $payment->consumer->block_id ?? 'N/A' }}</td>
                            <td>{{ $payment->customer_id }}</td>
                            <td>{{ $payment->consumer->firstname ?? 'N/A' }}</td>
                            <td>{{ $payment->consumer->middlename ?? 'N/A' }}</td>
                            <td>{{ $payment->consumer->lastname ?? 'N/A' }}</td>
                            <td>₱{{ number_format($payment->reconnection_fee, 2) }}</td>
                            <td>₱{{ number_format($payment->service_amount_paid, 2) }}</td>
                            <td>
                                <span class="status-badge {{ $payment->service_paid_status === 'paid' ? 'status-active' : 'status-inactive' }}">
                                    {{ ucfirst($payment->service_paid_status) }}
                                </span>
                            </td>
                            @if($userRole->hasPermission('service-pay') || 
                                $userRole->hasPermission('service-print'))
                                <td>
                                    <div class="action-buttons">
                                        @if($payment->service_paid_status === 'unpaid' && $payment->reconnection_fee > 0 && $userRole->hasPermission('service-pay'))
                                            <button class="btn_uni btn-activate" title="Pay Reconnection Fee" onclick="showServicePaymentModal('{{ $payment->customer_id }}')">
                                                <i class="fas fa-money-bill-wave"></i>Pay
                                            </button>
                                        @endif
                                        @if($userRole->hasPermission('service-print') && $payment->service_paid_status === 'paid')
                                            <button class="btn_uni btn-billing" title="Print Receipt" onclick="printServiceReceipt('{{ $payment->customer_id }}')">
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
                        <td colspan="9" class="empty-state">
                            <i class="fas fa-tools"></i>
                            <p>No reconnection payments found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Payment Modal -->
<div id="servicePaymentModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-tools"></i> Process Reconnection Payment</h3>
        <form id="servicePaymentForm" onsubmit="handleServicePayment(event)">
            @csrf
            <input type="hidden" id="servicePaymentCustomerId" name="customer_id">
            <div class="form-group">
                <label class="unauth-label">Reconnection Fee</label>
                <input type="text" id="reconnectionFee" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label class="unauth-label">Amount Tendered</label>
                <input type="number" id="serviceAmountTendered" name="amount_tendered" class="form-control" required step="0.01" min="0">
                <div class="invalid-feedback"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closeModal('servicePaymentModal')">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Process Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- Result Modal -->
<div id="serviceResultModal" class="modal">
    <div class="modal-content">
        <div class="result-modal">
            <div id="serviceResultIcon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 id="serviceResultTitle"></h3>
            <p id="serviceResultMessage"></p>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_verify" onclick="closeServiceResultModal()">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/recon.js') }}"></script>
@endsection

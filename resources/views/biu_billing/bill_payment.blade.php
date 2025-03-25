@extends('biu_layout.admin')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
<div class="table-header">
    <h3><i class="fas fa-money-bill-wave"></i> Bill Payment</h3>
</div>
<div class="content-wrapper">
    <div class="table-container">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>Consumer ID</th>
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
                @if($bills->isEmpty())
                    <tr>
                        <td colspan="9" class="text-center"><i class="fas fa-exclamation-circle"></i>  No billing payments available.</td>
                    </tr>
                @else
                    @foreach($bills as $bill)
                        <tr>
                            <td>{{ $bill->consumer->customer_id }}</td>
                            <td>{{ date('M d, Y', strtotime($bill->due_date)) }}</td>
                            <td>{{ $bill->previous_reading }}</td>
                            <td>{{ $bill->present_reading }}</td>
                            <td>{{ $bill->consumption }}</td>
                            <td>₱{{ number_format($bill->total_amount, 2) }}</td>
                            <td><span class="badge {{ $bill->bill_status == 'PAID' ? 'bg-success' : 'bg-danger' }}">
                                {{ $bill->bill_status }}
                            </span></td>
                            <td>
                                @if($bill->bill_status == 'UNPAID')
                                    <button class="btn btn-primary btn-sm" 
                                            data-bill-id="{{ $bill->consread_id }}"
                                            onclick="handlePayment(this)">
                                        <i class="fas fa-money-bill-wave"></i> Pay
                                    </button>
                                @endif
                                <button class="btn btn-info btn-sm" 
                                        data-bill-id="{{ $bill->consread_id }}"
                                        onclick="printBill(this)">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        
        {{-- Add pagination links --}}
        @if($bills->hasPages())
            <div class="pagination-wrapper">
                {{ $bills->links('pagination.custom') }}
            </div>
        @endif
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
                <label>Present Reading</label>
                <input type="text" id="presentReading" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label>Penalty Amount</label>
                <input type="text" id="penaltyAmount" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label>Total Amount Due</label>
                <input type="text" id="totalAmount" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label>Amount Tendered</label>
                <input type="number" id="amountTendered" name="amount_tendered" class="form-control" required step="0.01" min="0">
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label>Change</label>
                <input type="text" id="changeAmount" class="form-control" readonly>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closePaymentModal()">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Process Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/billpay.js') }}"></script>
@endsection
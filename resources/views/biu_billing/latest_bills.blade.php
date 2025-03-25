@extends('biu_layout.admin')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
<div class="table-header">
    <h3><i class="fas fa-file-invoice-dollar"></i> Latest Bills</h3>
    <div class="header-controls">
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search bills...">
            <i class="fas fa-search search-icon"></i>
        </div>
        <button class="add-btn" type="button">
            <i class="fas fa-plus-circle"></i> Add Bill
        </button>
    </div>
</div>
<div class="content-wrapper">
    <div class="table-container">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>Consumer ID</th>
                    <th>Contact No.</th>
                    <th>Reading Date</th>
                    <th>Due Date</th>
                    <th>Present Reading</th>
                    <th>Consumption</th>
                    <th>Total Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($bills->isEmpty())
                    <tr>
                        <td colspan="10" class="text-center"><i class="fas fa-exclamation-circle"></i>  No bills available.</td>
                    </tr>
                @else
                    @foreach($bills as $bill)
                        <tr>
                            <td>{{ $bill->consumer->customer_id }}</td>
                            <td>{{ $bill->consumer->contact_no }}</td>
                            <td>{{ date('M d, Y', strtotime($bill->reading_date)) }}</td>
                            <td>{{ date('M d, Y', strtotime($bill->due_date)) }}</td>
                            <td>{{ $bill->present_reading }}</td>
                            <td>{{ $bill->consumption }}</td>
                            <td>₱{{ number_format($bill->total_amount, 2) }}</td>
                            <td>
                                    <button class="btn_uni btn-view" 
                                            data-bill-id="{{ $bill->consread_id }}"
                                            onclick="sendBill(this)">
                                        <i class="fas fa-paper-plane"></i> Send Bill
                                    </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        
        @if($bills->hasPages())
            <div class="pagination-wrapper">
                {{ $bills->links('pagination.custom') }}
            </div>
        @endif
    </div>
</div>

<!-- Send Bill Modal -->
<div id="sendBillModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-paper-plane"></i> Send Bill</h3>
        <form id="sendBillForm" onsubmit="processSendBill(event)">
            @csrf
            <input type="hidden" id="billId" name="bill_id">
            <div class="form-group">
                <label>Send Method</label>
                <select class="form-control" id="sendMethod" name="send_method" required>
                    <option value="sms">SMS</option>
                    <option value="email">Email</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closeSendBillModal()">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Send</button>
            </div>
        </form>
    </div>
</div>
@endsection
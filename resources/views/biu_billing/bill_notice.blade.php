@extends('biu_layout.admin')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<style>
    .notice-form-container {
        background: var(--background-color);
        padding: 20px;
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-md);
        margin: -50px 0 0 41%;
        width: 600px;
        position: relative;
        z-index: 100;
    }

    .notice-selection {
        display: flex;
        gap: 15px;
        position: relative;
        z-index: 101;
    }

    .notice-selection select {
        flex: 1;
        padding: 8px;
        border: 1px solid var(--border-color-light);
        border-radius: var(--border-radius-sm);
        background-color: var(--background-color);
        cursor: pointer;
        position: relative;
        z-index: 102;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    .notice-selection select:focus {
        outline: 2px solid var(--primary-color);
        border-color: var(--primary-color);
    }

    /* Add a custom dropdown arrow */
    .notice-selection::after {
        content: '▼';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: var(--text-muted);
    }

    /* Ensure the table container doesn't overlap */
    .table-container {
        margin-top: 20px;
        position: relative;
        z-index: 99;
    }

    .announcement-textarea {
        width: 100%;
        min-height: 100px;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid var(--border-color-light);
        border-radius: var(--border-radius-sm);
        resize: none;
    }

    .send-button {
        background: var(--primary-color);
        color: white;
        padding: 8px 20px;
        border: none;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        transition: var(--transition-fast);
    }

    .send-button:hover {
        background: var(--primary-color-hover);
    }

    .table-container {
        margin-top: 20px;
    }
</style>

<div class="table-header">
    <h3><i class="fas fa-bell"></i> Bill Notice</h3>
</div>
<div class="content-wrapper">
    <div class="notice-form-container">
        <div class="notice-selection">
            <select class="form-control" id="noticeSelect">
                <option value="">Select Announcement</option>
                @foreach($notices as $notice)
                    <option value="{{ $notice->notice_id }}">{{ $notice->type }} - {{ $notice->announcement }}</option>
                @endforeach
            </select>
        </div>
        <textarea 
            class="announcement-textarea" 
            id="announcementText" 
            placeholder="Enter your announcement message here..."
        ></textarea>
        <button class="send-button" onclick="sendNotice()">
            <i class="fas fa-paper-plane"></i> Send Notice
        </button>
    </div>

    <div class="table-container" style="height: 63%;">
        <table class="uni-table">
            <thead>
                <tr>
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
                        <td colspan="9" class="text-center">
                            <i class="fas fa-exclamation-circle"></i> No bills available.
                        </td>
                    </tr>
                @else
                    @foreach($bills as $bill)
                        <tr>
                            <td>{{ $bill->consumer->customer_id }}</td>
                            <td>{{ $bill->consumer->firstname }} {{ $bill->consumer->lastname }}</td>
                            <td>{{ $bill->consumer->contact_no }}</td>
                            <td>{{ date('M d, Y', strtotime($bill->due_date)) }}</td>
                            <td>{{ $bill->present_reading }}</td>
                            <td>{{ $bill->consumption }}</td>
                            <td>₱{{ number_format($bill->total_amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $bill->bill_status == 'PAID' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $bill->bill_status }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="viewDetails({{ $bill->consread_id }})">
                                    <i class="fas fa-eye"></i>
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
@endsection

@section('scripts')
<script src="{{ asset('js/bill_notice.js') }}"></script>
@endsection

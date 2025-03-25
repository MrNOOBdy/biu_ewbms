@extends('biu_layout.admin')

@section('tab-content')
<style>
    .table-header {
        margin-bottom: 20px;
    }

    .header-container {
        display: flex;
        position: relative;
    }

    .consumer-info-billing {
        font-size: 14px;
        color: #495057;
        margin-bottom: 20px;
    }

    .consumer-info-billing p {
        margin: 0 0 4px 0;
    }

    .back-button {
        position: absolute;
        top: 0;
        right: 30px;
        height: 35px;
        padding: 8px 15px;
    }
</style>

<div class="table-header">
    <div class="header-container">
        <div class="header-content">
            <div class="consumer-info-billing">
                <p><strong>Consumer ID:</strong> {{ $consumer->customer_id }}</p>
                <p><strong>Name:</strong> {{ $consumer->firstname }} {{ $consumer->middlename }} {{ $consumer->lastname }}</p>
                <p><strong>Type:</strong> {{ $consumer->consumer_type }}</p>
            </div>
        </div>
        <a href="{{ route('consumers.index') }}" class="btn_uni btn-view back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="content-wrapper">
    <div class="table-container">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>Reading Date</th>
                    <th>Due Date</th>
                    <th>Previous Reading</th>
                    <th>Present Reading</th>
                    <th>Consumption (m³)</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($billings as $billing)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($billing->reading_date)->format('M d, Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($billing->due_date)->format('M d, Y') }}</td>
                        <td>{{ number_format($billing->previous_reading, 2) }} m³</td>
                        <td>{{ number_format($billing->present_reading, 2) }} m³</td>
                        <td>{{ number_format($billing->consumption, 2) }} m³</td>
                        <td>₱ {{ number_format($billing->total_amount, 2) }}</td>
                        <td>
                            <span class="status-badge {{ $billing->bill_status === 'paid' ? 'status-active' : 'status-inactive' }}">
                                {{ ucfirst($billing->bill_status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            <p>No billing history found for {{ $consumer->firstname }} {{ $consumer->lastname }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

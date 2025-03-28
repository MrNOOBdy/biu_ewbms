@extends('biu_layout.admin')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
<div class="cons-table-header" style="padding: 10px;">
    <div class="header-container" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="header-content">
            <div class="consumer-info-billing" style="line-height: 1.6; font-size: 0.9rem;">
                <p style="margin: 0;"><strong>Consumer ID:</strong> {{ $consumer->customer_id }}</p>
                <p style="margin: 0;"><strong>Name:</strong> {{ $consumer->firstname }} {{ $consumer->middlename }} {{ $consumer->lastname }}</p>
                <p style="margin: 0;"><strong>Type:</strong> {{ $consumer->consumer_type }}</p>
            </div>
        </div>
        <a href="{{ route('consumers.index') }}" class="btn_uni btn-view back-button" style="text-decoration: none; color: #fff; background-color: #007bff; padding: 10px 15px; border-radius: 5px; display: inline-flex; align-items: center;">
            <i class="fas fa-arrow-left" style="margin-right: 5px;"></i> Back
        </a>
    </div>
</div>

<div class="content-wrapper">
    <div class="table-container" style="height: 90%;">
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
                        <td>{{ $billing->due_date ? \Carbon\Carbon::parse($billing->due_date)->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ number_format($billing->previous_reading, 2) }} m³</td>
                        <td>{{ number_format($billing->present_reading, 2) }} m³</td>
                        <td>{{ number_format($billing->consumption, 2) }} m³</td>
                        <td>₱ {{ number_format($billing->total_amount, 2) }}</td>
                        <td>
                            <span class="badge {{ $billing->bill_status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst($billing->bill_status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <div style="text-align: center; padding: 20px;">
                                <i class="fas fa-file-invoice" style="font-size: 2em; color: #ccc;"></i>
                                <p style="margin-top: 10px;">No paid billing history found for {{ $consumer->firstname }} {{ $consumer->lastname }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($billings->hasPages())
            <div class="pagination-container" style="margin-top: 20px;">
                {{ $billings->links('pagination.custom') }}
            </div>
        @endif
    </div>
</div>
@endsection

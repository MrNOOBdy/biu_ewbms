@extends('biu_layout.admin')

@section('title', 'BI-U: Application Fee Income')

@section('tab-content')
<div class="table-header">
    <h3><i class="fas fa-chart-line"></i> Application Fee Income</h3>
    <div class="header-controls">
        <div class="filter-section">
            <select id="monthFilter" onchange="filterIncome()">
                <option value="">All Months</option>
                @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                    <option value="{{ $loop->iteration }}" {{ date('n') == $loop->iteration ? 'selected' : '' }}>
                        {{ $month }}
                    </option>
                @endforeach
            </select>
            <select id="yearFilter" onchange="filterIncome()">
                <option value="">All Years</option>
                @foreach(range(date('Y'), 2020) as $year)
                    <option value="{{ $year }}" {{ date('Y') == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
            <select id="blockFilter" onchange="filterIncome()">
                <option value="">All Blocks</option>
                @foreach($blocks as $block)
                    <option value="{{ $block->block_id }}">Block {{ $block->block_id }}</option>
                @endforeach
            </select>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search..." onkeyup="filterIncome()">
            <i class="fas fa-search search-icon"></i>
        </div>
    </div>
</div>

@php
    $totalAmountPaid = $connPayments->sum('conn_amount_paid');
@endphp

<div class="appli-balance" style="margin-top: -20px;">
    <p><strong>Total Application Income:</strong> ₱{{ number_format($totalAmountPaid, 2) }}</p>
    
</div>

<div class="content-wrapper">
    <div class="table-container" style="height: 93%;">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>Block</th>
                    <th>Consumer Name</th>
                    <th>Application Fee</th>
                    <th>Amount Paid</th>
                    <th>Balance</th>
                    <th>Payment Date</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalApplicationFee = 0;
                    $totalAmountPaid = 0;
                    $totalBalance = 0;
                @endphp
                
                @forelse ($connPayments as $payment)
                    @if($payment->consumer)
                        @php
                            $balance = $payment->application_fee - $payment->conn_amount_paid;
                            $totalApplicationFee += $payment->application_fee;
                            $totalAmountPaid += $payment->conn_amount_paid;
                            $totalBalance += $balance;
                        @endphp
                        <tr>
                            <td>Block {{ $payment->consumer->block_id ?? 'N/A' }}</td>
                            <td>{{ $payment->consumer->firstname }} {{ $payment->consumer->middlename }} {{ $payment->consumer->lastname }}</td>
                            <td>₱{{ number_format($payment->application_fee, 2) }}</td>
                            <td>₱{{ number_format($payment->conn_amount_paid, 2) }}</td>
                            <td>₱{{ number_format($balance, 2) }}</td>
                            <td>{{ $payment->conn_pay_status == 'unpaid' ? 'Not paid yet' : ($payment->updated_at ? $payment->updated_at->format('M d, Y h:i A') : 'Not yet paid') }}</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-chart-bar"></i>
                            <p>No application income data found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot style="position: sticky; bottom: 0; z-index: 1;">
                <tr class="total-row">
                    <td colspan="2"><strong>Total</strong></td>
                    <td><strong>₱{{ number_format($totalApplicationFee, 2) }}</strong></td>
                    <td><strong>₱{{ number_format($totalAmountPaid, 2) }}</strong></td>
                    <td><strong>₱{{ number_format($totalBalance, 2) }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@extends('biu_layout.admin')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
<div class="table-header">
    <h3><i class="fas fa-coins"></i> Monthly Income Report</h3>
    <div class="header-controls">
        <div class="filter-section">
            <select id="monthFilter" onchange="filterIncome()">
                <option value="">Select Month</option>
                @foreach(range(1, 12) as $month)
                    <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                @endforeach
            </select>
            <select id="yearFilter" onchange="filterIncome()">
                <option value="">Select Year</option>
                @foreach(range(date('Y'), 2021) as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search income..." onkeyup="filterIncome()">
            <i class="fas fa-search search-icon"></i>
        </div>
    </div>
</div>

<div class="table-container">
    <table class="uni-table">
        <thead>
            <tr>
                <th>Block ID</th>
                <th>Consumer Name</th>
                <th>Amount Paid</th>
                <th>Date Paid</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($paidBills as $bill)
            <tr>
                <td>{{ $bill->block_id }}</td>
                <td>{{ $bill->firstname }} {{ $bill->lastname }}</td>
                <td>₱{{ number_format($bill->total_amount, 2) }}</td>
                <td>{{ $bill->date_paid ? $bill->date_paid->format('M d, Y') : 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No paid bills found</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right"><strong>Total Income:</strong></td>
                <td colspan="2"><strong>₱{{ number_format($totalIncome, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    {{ $paidBills->links('pagination.custom') }}
</div>

<script src="{{ asset('js/report.js') }}"></script>
@endsection
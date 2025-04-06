@extends('biu_layout.admin')

@section('title', 'BI-U: Monthly Income Report')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
<div class="table-header">
    <h3><i class="fas fa-coins"></i> Monthly Income Report</h3>
    <div class="header-controls">
        <div class="filter-section">
            <select id="monthFilter">
                <option value="">All Months</option>
                @php
                    $currentMonth = date('n');
                    foreach(range(1, 12) as $month) {
                        $selected = $month == $currentMonth ? 'selected' : '';
                        echo "<option value='{$month}' {$selected}>" . date('F', mktime(0, 0, 0, $month, 1)) . "</option>";
                    }
                @endphp
            </select>
            <select id="yearFilter">
                <option value="">All Years</option>
                @foreach(range(date('Y'), 2021) as $year)
                    <option value="{{ $year }}" {{ date('Y') == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
            <button class="btn-filter" onclick="IncomeReport.filterIncome()">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Generate...">
            <button class="btn-search" onclick="IncomeReport.filterIncome()">
                <i class="fas fa-file-alt"></i> Generate
            </button>
        </div>
    </div>
</div>

<div class="content-wrapper">
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
                    <td colspan="4" class="empty-state">
                        <i class="fas fa-coins"></i>
                        <p>No paid bills found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="text-right"><strong>Total Income:</strong></td>
                    <td colspan="2"><strong>₱{{ number_format($totalIncome, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="pagination-container">
            {{ $paidBills->links('pagination.custom') }}
        </div>
    </div>
</div>

<script src="{{ asset('js/income_report.js') }}"></script>
@endsection
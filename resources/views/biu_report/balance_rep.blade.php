@extends('biu_layout.admin')

@section('title', 'BI-U: Balance Report')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
<div class="table-header">
    <h3><i class="fas fa-balance-scale"></i> Balance Report</h3>
    <div class="header-controls">
        <div class="filter-section">
            <select id="monthFilter">
                <option value="">All Months</option>
                @foreach($availableMonths as $month)
                    <option value="{{ $month['number'] }}" {{ date('n') == $month['number'] ? 'selected' : '' }}>
                        {{ $month['name'] }}
                    </option>
                @endforeach
            </select>
            <select id="yearFilter">
                <option value="">All Years</option>
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ date('Y') == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
            <select id="blockFilter">
                <option value="">All Blocks</option>
                @foreach($unpaidBills->pluck('block_id')->unique() as $blockId)
                    <option value="{{ $blockId }}">Block {{ $blockId }}</option>
                @endforeach
            </select>
            <button class="btn-filter" onclick="BalanceReport.filterBalance()">
                <i class="fas fa-filter"></i> Filter
            </button>
            <button class="btn-print" onclick="BalanceReport.printReport()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Generate...">
            <button class="btn-search" onclick="BalanceReport.filterBalance()">
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
                    <th>Balance Amount</th>
                    <th>Reading Date</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($unpaidBills as $bill)
                <tr>
                    <td>{{ $bill->block_id }}</td>
                    <td>{{ $bill->firstname }} {{ $bill->lastname }}</td>
                    <td>₱{{ number_format($bill->total_amount, 2) }}</td>
                    <td>{{ $bill->reading_date ? Carbon\Carbon::parse($bill->reading_date)->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $bill->due_date ? $bill->due_date->format('M d, Y') : 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <p>No unpaid bills found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="filtered-total-row" style="display: none;">
                    <td colspan="2" class="text-right"><strong>Filtered Balance:</strong></td>
                    <td colspan="3"><strong class="filtered-total">₱0.00</strong></td>
                </tr>
                <tr class="overall-total-row">
                    <td colspan="2" class="text-right"><strong>Overall Balance:</strong></td>
                    <td colspan="3"><strong>₱{{ number_format($totalBalance, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="pagination-container">
            {{ $unpaidBills->links('pagination.custom') }}
        </div>
    </div>
</div>

<script src="{{ asset('js/balance_report.js') }}"></script>
@endsection
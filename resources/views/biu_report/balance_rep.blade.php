@extends('biu_layout.admin')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
<div class="table-header">
    <h3><i class="fas fa-balance-scale"></i> Balance Report</h3>
    <div class="header-controls">
        <div class="filter-section">
            <select id="blockFilter" onchange="filterBalance()">
                <option value="">All Blocks</option>
                @foreach(range(1, 10) as $block)
                    <option value="{{ $block }}">Block {{ $block }}</option>
                @endforeach
            </select>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search balance..." onkeyup="filterBalance()">
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
                <th>Balance Amount</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($unpaidBills as $bill)
            <tr>
                <td>{{ $bill->block_id }}</td>
                <td>{{ $bill->firstname }} {{ $bill->lastname }}</td>
                <td>₱{{ number_format($bill->total_amount, 2) }}</td>
                <td>{{ $bill->due_date ? $bill->due_date->format('M d, Y') : 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No unpaid bills found</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right"><strong>Total Balance:</strong></td>
                <td colspan="2"><strong>₱{{ number_format($totalBalance, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    {{ $unpaidBills->links('pagination.custom') }}
</div>

<script src="{{ asset('js/report.js') }}"></script>
@endsection
@extends('biu_layout.admin')

@section('title', 'BI-U: Service Fee Report')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
<div class="table-header">
    <h3><i class="fas fa-chart-line"></i> Service Fee Report</h3>
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
                @foreach($blocks as $blockId)
                    @if($blockId)
                        <option value="{{ $blockId }}">Block {{ $blockId }}</option>
                    @endif
                @endforeach
            </select>
            <button class="btn-filter" onclick="ServiceReport.filterReport()">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Generate...">
            <button class="btn-search" onclick="ServiceReport.filterReport()">
                <i class="fas fa-file-alt"></i> Generate
            </button>
        </div>
    </div>
</div>

@php
    $totalServiceAmount = $totalAmounts->total_service_amount ?? 0;
    $totalReconnectionFee = $totalAmounts->total_reconnection_fee ?? 0;
@endphp

<div class="service-totals" style="margin-top: -20px;">
    <p><strong>Total Service Fee:</strong> ₱{{ number_format($totalReconnectionFee, 2) }}</p>
</div>

<div class="content-wrapper">
    <div class="table-container" style="height: 93%;">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>Block</th>
                    <th>Consumer Name</th>
                    <th>Service Amount</th>
                    <th>Reconnection Fee</th>
                    <th>Status</th>
                    <th>Payment Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($serviceFees as $fee)
                    @if($fee->consumer)
                        <tr>
                            <td>Block {{ $fee->consumer->block_id ?? 'N/A' }}</td>
                            <td>{{ $fee->consumer->firstname }} {{ $fee->consumer->middlename }} {{ $fee->consumer->lastname }}</td>
                            <td>₱{{ number_format($fee->service_amount_paid, 2) }}</td>
                            <td>₱{{ number_format($fee->reconnection_fee, 2) }}</td>
                            <td>{{ $fee->service_paid_status }}</td>
                            <td>{{ $fee->updated_at ? $fee->updated_at->format('M d, Y h:i A') : 'N/A' }}</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-chart-bar"></i>
                            <p>No service fee data found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot style="position: sticky; bottom: 0; z-index: 1; background-color: #f8f9fa;">
                <tr class="total-row">
                    <td colspan="2"><strong>Total</strong></td>
                    <td><strong>₱{{ number_format($totalAmounts->total_service_amount ?? 0, 2) }}</strong></td>
                    <td><strong>₱{{ number_format($totalAmounts->total_reconnection_fee ?? 0, 2) }}</strong></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        <div class="pagination-container">
            {{ $serviceFees->links('pagination.custom') }}
        </div>
    </div>
</div>

<script src="{{ asset('js/service_rep.js') }}"></script>
@endsection

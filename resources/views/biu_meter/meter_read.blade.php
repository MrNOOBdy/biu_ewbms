@extends('biu_layout.admin')

@section('title', 'BI-U: Meter Readings')

@section('tab-content')
<style>
    .sidebar-collapsed ~ .block-contents .table-container {
        margin-left: 5%;
        width: 75rem;
    }
</style>
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">

<div class="table-header">
    <h3><i class="fas fa-tachometer-alt"></i> Meter Readings</h3>
    @if($currentCoverage)
    <input type="hidden" id="currentCoverageId" value="{{ $currentCoverage->covdate_id }}">
    <div class="coverage-period">
        <p>Coverage Period: {{ date('M d, Y', strtotime($currentCoverage->coverage_date_from)) }} - {{ date('M d, Y', strtotime($currentCoverage->coverage_date_to)) }}</p>
    </div>
    @endif
    <div class="header-controls">
        <div class="filter-section">
            <select id="blockFilter" onchange="MeterReadings.filter()">
                <option value="">All Blocks</option>
                @foreach($blocks as $block)
                    <option value="{{ $block->block_id }}">Block {{ $block->block_id }}</option>
                @endforeach
            </select>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search...">
            <button class="btn-search" onclick="MeterReadings.filter()">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <div class="table-container">
        @if(!$currentCoverage)
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <p>No active coverage period found. Please set an active coverage period first.</p>
            </div>
        @else
            <table class="uni-table">
                <thead>
                    <tr>
                        <th>Block</th>
                        <th>Consumer ID</th>
                        <th>Consumer Name</th>
                        <th>Consumer Type</th>
                        <th>Previous Reading</th>
                        <th>Present Reading</th>
                        <th>Consumption</th>
                        <th>Meter Reader</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($readings as $reading)
                        <tr>
                            <td>Block {{ $reading->consumer->block_id }}</td>
                            <td>{{ $reading->customer_id }}</td>
                            <td>{{ $reading->consumer->firstname }} {{ $reading->consumer->lastname }}</td>
                            <td>{{ $reading->consumer->consumer_type }}</td>
                            <td>{{ $reading->previous_reading }}</td>
                            <td>{{ $reading->present_reading }}</td>
                            <td>{{ $reading->calculateConsumption() }}</td>
                            <td>{{ $reading->meter_reader }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-tachometer-alt"></i>
                                <p>No readings found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
        <div class="pagination-container">
            {{ $readings->links('pagination.custom') }}
        </div>
    </div>
</div>

<script src="{{ asset('js/meter_readings.js') }}"></script>
@endsection
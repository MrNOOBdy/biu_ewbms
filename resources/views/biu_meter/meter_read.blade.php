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
    <div class="header-controls">
        <div class="filter-section">
            <select id="blockFilter">
                <option value="">All Blocks</option>
                @foreach($blocks as $block)
                    <option value="{{ $block->block_id }}">Block {{ $block->block_id }}</option>
                @endforeach
            </select>
            <button class="btn-filter" onclick="MeterReadings.filter()">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Generate...">
            <button class="btn-search" onclick="MeterReadings.filter()">
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
        <div class="pagination-container">
            {{ $readings->links('pagination.custom') }}
        </div>
    </div>
</div>

<script src="{{ asset('js/meter_readings.js') }}"></script>
@endsection
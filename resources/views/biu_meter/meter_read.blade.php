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
            <select id="blockFilter" onchange="filterReadings()">
                <option value="">All Blocks</option>
                @foreach($blocks as $block)
                    <option value="{{ $block->block_id }}">Block {{ $block->block_id }}</option>
                @endforeach
            </select>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search readings..." onkeyup="filterReadings()">
            <i class="fas fa-search search-icon"></i>
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
                    <th>Consumer Type</th>
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
                        <td>{{ $reading->consumer->consumer_type }}</td>
                        <td>{{ $reading->present_reading }}</td>
                        <td>{{ $reading->consumption }}</td>
                        <td>{{ $reading->meter_reader }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-tachometer-alt"></i>
                            <p>No readings found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $readings->links('pagination.custom') }}
    </div>
</div>

<script>
function filterReadings() {
    // Add your filtering logic here
    // Similar to the water_consumer.js filtering function
}
</script>
@endsection
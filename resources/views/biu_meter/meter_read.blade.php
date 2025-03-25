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
                    <th>Consumer Name</th>
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
                        <td>{{ $reading->consumer->full_name }}</td>
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
    var blockFilter = document.getElementById('blockFilter').value;
    var searchInput = document.getElementById('searchInput').value.toUpperCase();
    var table = document.querySelector('.uni-table');
    var rows = table.getElementsByTagName('tr');

    for (var i = 1; i < rows.length; i++) {
        var block = rows[i].getElementsByTagName('td')[0].innerText;
        var consumerId = rows[i].getElementsByTagName('td')[1].innerText;
        var consumerType = rows[i].getElementsByTagName('td')[2].innerText;
        var presentReading = rows[i].getElementsByTagName('td')[3].innerText;
        var consumption = rows[i].getElementsByTagName('td')[4].innerText;
        var meterReader = rows[i].getElementsByTagName('td')[5].innerText;

        if (blockFilter && blockFilter !== block.split(' ')[1]) {
            rows[i].style.display = 'none';
        } else if (block.toUpperCase().indexOf(searchInput) > -1 ||
            consumerId.toUpperCase().indexOf(searchInput) > -1 ||
            consumerType.toUpperCase().indexOf(searchInput) > -1 ||
            presentReading.toUpperCase().indexOf(searchInput) > -1 ||
            consumption.toUpperCase().indexOf(searchInput) > -1 ||
            meterReader.toUpperCase().indexOf(searchInput) > -1) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}
</script>
@endsection
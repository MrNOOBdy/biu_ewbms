@extends('biu_layout.admin')

@section('title', 'BI-U: Meter Readings')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">

<style>
    #addReadingModal .modal-content {
        max-width: 1000px !important;
        width: 95% !important;
    }

    #addReadingModal .modal-body {
        max-height: 50vh;
        overflow-y: auto;
        overflow-x: hidden;
    }

    #addReadingModal .table-container {
        margin: 0;
        width: 100%;
        position: relative;
    }

    #addReadingModal .coverage-period {
        text-align: center;
        margin-bottom: 20px;
        padding: 10px;
        background-color: rgba(0, 0, 0, 0.03);
        border-radius: 4px;
    }

    #addReadingModal .coverage-period p {
        margin: 0;
        color: #333;
        font-weight: 500;
    }

    #addReadingModal table thead {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8f9fa;
    }

    #addReadingModal table th {
        background: #f8f9fa;
    }
</style>

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
        @if($userRole->hasPermission('add-meter-reading'))
        <button class="add-btn" onclick="MeterReadings.showAddReadingModal()">
            <i class="fas fa-plus"></i> Add Meter Reading
        </button>
        @endif
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
                            <td colspan="7" class="empty-state">
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

<!-- Add Meter Reading Modal -->
<div id="addReadingModal" class="modal">
    <div class="modal-content large-modal">
        <h3><i class="fas fa-plus"></i> Add Meter Reading</h3>
        @if($currentCoverage)
        <div class="coverage-period">
            <p>Coverage Period: {{ date('M d, Y', strtotime($currentCoverage->coverage_date_from)) }} - {{ date('M d, Y', strtotime($currentCoverage->coverage_date_to)) }}</p>
        </div>
        @endif
        <div class="header-controls" style="margin-bottom: 20px;">
            <div class="filter-section">
                <select id="modalBlockFilter" onchange="MeterReadings.filterConsumers()">
                    <option value="">All Blocks</option>
                    @foreach($blocks as $block)
                        <option value="{{ $block->block_id }}">Block {{ $block->block_id }}</option>
                    @endforeach
                </select>
            </div>
            <div class="search-container">
                <input type="text" id="modalSearchInput" placeholder="Search consumers...">
                <button class="btn-search" onclick="MeterReadings.filterConsumers()">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>
        <div class="modal-body">
            <div class="table-container">
                <table class="uni-table">
                    <thead>
                        <tr>
                            <th>Cons ID</th>
                            <th>Cons Name</th>
                            <th>Cons Type</th>
                            <th>Prev Reading</th>
                            <th>Pres Reading</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="consumerTableBody">
                        <!-- Will be populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn_modal btn_cancel" onclick="MeterReadings.closeAddReadingModal()">Close</button>
            <button type="button" class="btn_modal btn_verify" onclick="MeterReadings.saveAllReadings()">Save All Readings</button>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div id="meterReadingResultModal" class="modal">
    <div class="modal-content result-modal">
        <div id="meterReadingResultIcon"></div>
        <h3 id="meterReadingResultTitle"></h3>
        <p id="meterReadingResultMessage"></p>
        <div class="modal-actions">
            <button type="button" class="btn_modal btn_verify" onclick="MeterReadings.closeResultModal()">OK</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/meter_readings.js') }}"></script>
@endsection
@extends('biu_layout.admin')

@section('title', 'BI-U: Dashboard')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<div class="table-header">
    <div class="header-content">
        <h3><i class="fas fa-chart-line"></i> Dashboard</h3>
        @if($activeCoverage)
        <div class="coverage-period">
            <i class="fas fa-calendar-alt"></i> Coverage Period: 
            <span>{{ \Carbon\Carbon::parse($activeCoverage->coverage_date_from)->format('M d, Y') }} - 
                  {{ \Carbon\Carbon::parse($activeCoverage->coverage_date_to)->format('M d, Y') }}</span>
        </div>
        @endif
    </div>
</div>
<div class="dashboard-container">
    <div class="stats-container">
        <div class="stat-box">
            <i class="fas fa-users fa-2x" style="color: var(--primary-color); margin-bottom: 10px;"></i>
            <h2 class="stat-number">{{ $totalConsumers }}</h2>
            <p>Total Consumers</p>
        </div>
        <div class="stat-box">
            <i class="fas fa-file-invoice fa-2x" style="color: var(--primary-color); margin-bottom: 10px;"></i>
            <h2 class="stat-number">{{ $totalBills }}</h2>
            <p>Total Bills</p>
        </div>
        <div class="stat-box">
            <i class="fas fa-exclamation-circle fa-2x" style="color: var(--warning-color); margin-bottom: 10px;"></i>
            <h2 class="stat-number">{{ $unpaidBills }}</h2>
            <p>Unpaid Bills</p>
        </div>
        <div class="stat-box">
            <i class="fas fa-coins fa-2x" style="color: var(--success-color); margin-bottom: 10px;"></i>
            <h2 class="stat-number">₱{{ number_format($totalIncome, 2) }}</h2>
            <p>Total Income</p>
        </div>
    </div>

    <div class="main-content">
        <div class="rate-box" style="display: block;">
            <div class="rate-header">
                <h3 ><i class="fas fa-hand-holding-water"></i> Water Bill Rates</h3>
            </div>
            <div class="rate-section">
                @foreach($billRates as $type => $rates)
                    <h3 style="font-size: 1rem">{{ ucwords(str_replace('_', ' ', $type)) }}</h3>
                    @foreach($rates as $rate)
                        <p>1 - {{ $rate->cubic_meter }} m³: ₱{{ number_format($rate->value, 2) }}</p>
                        <p class="excess-rate">Excess: ₱{{ number_format($rate->excess_value_per_cubic, 2) }}/m³</p>
                    @endforeach
                @endforeach
            </div>
        </div>

        <div class="chart-section">
            <div style="display: flex; justify-content: space-between;">
                <h3><i class="fas fa-chart-column" style="margin-right: 10px;"></i>Water Consumption</h3>
                <div class="chart-controls">
                    <button class="chart-toggle-btn active" data-view="monthly">Monthly</button>
                    <button class="chart-toggle-btn" data-view="yearly">Yearly</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="waterConsumptionChart"></canvas>
            </div>
        </div>
    </div>
</div>
<script>
    const monthlyConsumptionData = @json($monthlyConsumption);
    const yearlyConsumptionData = @json($yearlyConsumption);
</script>
<script src="{{ asset('js/dash_chart.js') }}"></script>
@endsection

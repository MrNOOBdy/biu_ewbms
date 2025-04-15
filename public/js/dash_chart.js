let chartInstance = null;
let currentView = 'monthly';

function initChartControls() {
    document.querySelectorAll('.chart-toggle-btn').forEach(btn => {
        btn.removeEventListener('click', handleViewToggle);
        btn.addEventListener('click', handleViewToggle);
    });
}

function handleViewToggle(event) {
    const button = event.target.closest('.chart-toggle-btn');
    if (!button) return;

    const newView = button.dataset.view;
    if (newView !== currentView) {
        currentView = newView;
        updateToggleButtons();
        initChart();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    initChart();
    initChartControls();

    document.addEventListener('tabChanged', function () {
        setTimeout(() => {
            initChart();
            initChartControls();
        }, 100);
    });
});

function updateToggleButtons() {
    document.querySelectorAll('.chart-toggle-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.view === currentView);
    });
}

function initChart() {
    const canvas = document.getElementById('waterConsumptionChart');
    if (!canvas) return;

    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }

    requestAnimationFrame(() => {
        const ctx = canvas.getContext('2d');

        const data = currentView === 'monthly'
            ? getMonthlyData()
            : getYearlyData();

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: `Water Consumption (${currentView === 'monthly' ? 'Monthly' : 'Yearly'})`,
                    data: data.values,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Consumption by Coverage Period (m³)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: currentView === 'monthly' ? 'Coverage Months' : 'Coverage Years'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `Coverage Period Consumption: ${context.raw} m³`;
                            }
                        }
                    }
                }
            }
        });
    });
}

function getMonthlyData() {
    const months = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];

    const values = new Array(12).fill(0);

    for (let month = 1; month <= 12; month++) {
        if (monthlyConsumptionData[month]) {
            values[month - 1] = monthlyConsumptionData[month];
        }
    }

    return {
        labels: months,
        values: values
    };
}

function getYearlyData() {
    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 6 }, (_, i) => (currentYear - 5 + i).toString());
    const values = years.map(year => yearlyConsumptionData[year] || 0);

    return {
        labels: years,
        values: values
    };
}

window.addEventListener('beforeTabChange', function () {
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
});

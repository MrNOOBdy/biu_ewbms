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
                            text: 'Cubic Meters'
                        }
                    },
                    x: {
                        title: {
                            display: true,
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
                                return context.dataset.label + ': ' + context.raw + ' m³';
                            }
                        }
                    }
                }
            }
        });
    });
}

function getMonthlyData() {
    return {
        labels: ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'],
        values: [30, 59, 80, 81, 56, 55, 40, 45, 60, 70, 75, 68]
    };
}

function getYearlyData() {
    return {
        labels: ['2019', '2020', '2021', '2022', '2023', '2024'],
        values: [580, 620, 750, 800, 680, 320]
    };
}

window.addEventListener('beforeTabChange', function () {
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
});

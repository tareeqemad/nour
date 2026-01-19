import { Chart, registerables } from 'chart.js';

// Register Chart.js components
Chart.register(...registerables);

document.addEventListener('DOMContentLoaded', function() {
    // Get chart data from window object (will be set by Blade template)
    if (typeof window.dashboardChartData === 'undefined') {
        return;
    }

    const chartData = window.dashboardChartData;
    
    // Chart configuration
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                rtl: true,
            },
            tooltip: {
                rtl: true,
                titleFont: {
                    family: 'Cairo, Arial, sans-serif'
                },
                bodyFont: {
                    family: 'Cairo, Arial, sans-serif'
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    font: {
                        family: 'Cairo, Arial, sans-serif'
                    }
                }
            },
            y: {
                ticks: {
                    font: {
                        family: 'Cairo, Arial, sans-serif'
                    }
                }
            }
        }
    };

    // Helper function to create charts
    function createChart(canvasId, type, label, data, borderColor, backgroundColor, isBar = false) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        const config = {
            type: type,
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: label,
                    data: data,
                    borderColor: borderColor,
                    backgroundColor: backgroundColor,
                    ...(isBar ? { borderWidth: 1 } : { tension: 0.4, fill: true })
                }]
            },
            options: chartOptions
        };

        new Chart(ctx, config);
    }

    // Energy Charts (Line Chart)
    createChart('energyChart', 'line', 'الطاقة المنتجة (kWh)', chartData.energy, 'rgb(75, 192, 192)', 'rgba(75, 192, 192, 0.1)');
    createChart('energyChartOwner', 'line', 'الطاقة المنتجة (kWh)', chartData.energy, 'rgb(75, 192, 192)', 'rgba(75, 192, 192, 0.1)');
    createChart('energyChartAdmin', 'line', 'الطاقة المنتجة (kWh)', chartData.energy, 'rgb(75, 192, 192)', 'rgba(75, 192, 192, 0.1)');

    // Fuel Charts (Line Chart)
    createChart('fuelChart', 'line', 'الوقود المستهلك (لتر)', chartData.fuel, 'rgb(255, 99, 132)', 'rgba(255, 99, 132, 0.1)');
    createChart('fuelChartOwner', 'line', 'الوقود المستهلك (لتر)', chartData.fuel, 'rgb(255, 99, 132)', 'rgba(255, 99, 132, 0.1)');
    createChart('fuelChartAdmin', 'line', 'الوقود المستهلك (لتر)', chartData.fuel, 'rgb(255, 99, 132)', 'rgba(255, 99, 132, 0.1)');

    // Records Charts (Bar Chart)
    createChart('recordsChart', 'bar', 'عدد السجلات', chartData.records, 'rgb(54, 162, 235)', 'rgba(54, 162, 235, 0.6)', true);
    createChart('recordsChartOwner', 'bar', 'عدد السجلات', chartData.records, 'rgb(54, 162, 235)', 'rgba(54, 162, 235, 0.6)', true);
    createChart('recordsChartAdmin', 'bar', 'عدد السجلات', chartData.records, 'rgb(54, 162, 235)', 'rgba(54, 162, 235, 0.6)', true);

    // Load Charts (Line Chart)
    createChart('loadChart', 'line', 'نسبة التحميل (%)', chartData.load, 'rgb(255, 206, 86)', 'rgba(255, 206, 86, 0.1)');
    createChart('loadChartOwner', 'line', 'نسبة التحميل (%)', chartData.load, 'rgb(255, 206, 86)', 'rgba(255, 206, 86, 0.1)');
    createChart('loadChartAdmin', 'line', 'نسبة التحميل (%)', chartData.load, 'rgb(255, 206, 86)', 'rgba(255, 206, 86, 0.1)');
});


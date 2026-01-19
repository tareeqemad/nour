@push('scripts')
@if(isset($chartData))
<script>
    // Pass chart data to JavaScript
    window.dashboardChartData = @json($chartData);
</script>
<!-- Chart.js Local -->
<script src="{{ asset('assets/admin/libs/chart.js/chart.umd.min.js') }}"></script>
<script>
(function() {
    function initCharts() {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded. File path: {{ asset("assets/admin/libs/chart.js/chart.umd.min.js") }}');
            return;
        }
        
        // Check if chart data is available
        if (typeof window.dashboardChartData === 'undefined') {
            console.error('Chart data is not available');
            return;
        }
        
        const chartData = window.dashboardChartData;
    
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true, position: 'top', rtl: true },
            tooltip: {
                rtl: true,
                titleFont: { family: 'Tajawal, Arial, sans-serif' },
                bodyFont: { family: 'Tajawal, Arial, sans-serif' }
            }
        },
        scales: {
            x: { ticks: { font: { family: 'Tajawal, Arial, sans-serif' } } },
            y: { ticks: { font: { family: 'Tajawal, Arial, sans-serif' } } }
        }
    };

    function createChart(canvasId, type, label, data, borderColor, backgroundColor, isBar = false) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        new Chart(ctx, {
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
        });
    }

    // Energy Charts
    createChart('energyChart', 'line', 'الطاقة المنتجة (kWh)', chartData.energy, 'rgb(75, 192, 192)', 'rgba(75, 192, 192, 0.1)');
    createChart('energyChartOwner', 'line', 'الطاقة المنتجة (kWh)', chartData.energy, 'rgb(75, 192, 192)', 'rgba(75, 192, 192, 0.1)');
    createChart('energyChartAdmin', 'line', 'الطاقة المنتجة (kWh)', chartData.energy, 'rgb(75, 192, 192)', 'rgba(75, 192, 192, 0.1)');
    
    // Fuel Charts
    createChart('fuelChart', 'line', 'الوقود المستهلك (لتر)', chartData.fuel, 'rgb(255, 99, 132)', 'rgba(255, 99, 132, 0.1)');
    createChart('fuelChartOwner', 'line', 'الوقود المستهلك (لتر)', chartData.fuel, 'rgb(255, 99, 132)', 'rgba(255, 99, 132, 0.1)');
    createChart('fuelChartAdmin', 'line', 'الوقود المستهلك (لتر)', chartData.fuel, 'rgb(255, 99, 132)', 'rgba(255, 99, 132, 0.1)');
    
    // Records Charts
    createChart('recordsChart', 'bar', 'عدد السجلات', chartData.records, 'rgb(54, 162, 235)', 'rgba(54, 162, 235, 0.6)', true);
    createChart('recordsChartOwner', 'bar', 'عدد السجلات', chartData.records, 'rgb(54, 162, 235)', 'rgba(54, 162, 235, 0.6)', true);
    createChart('recordsChartAdmin', 'bar', 'عدد السجلات', chartData.records, 'rgb(54, 162, 235)', 'rgba(54, 162, 235, 0.6)', true);
    
    // Load Charts
    createChart('loadChart', 'line', 'نسبة التحميل (%)', chartData.load, 'rgb(255, 206, 86)', 'rgba(255, 206, 86, 0.1)');
    createChart('loadChartOwner', 'line', 'نسبة التحميل (%)', chartData.load, 'rgb(255, 206, 86)', 'rgba(255, 206, 86, 0.1)');
    createChart('loadChartAdmin', 'line', 'نسبة التحميل (%)', chartData.load, 'rgb(255, 206, 86)', 'rgba(255, 206, 86, 0.1)');

        // Advanced Energy & Fuel Chart (Dual Y-axis)
        if (chartData.advanced_chart && chartData.advanced_chart.labels && chartData.advanced_chart.labels.length > 0) {
            const advancedChartData = chartData.advanced_chart;
            
            function createAdvancedEnergyFuelChart(canvasId) {
                const ctx = document.getElementById(canvasId);
                if (!ctx) return;
    
                const ctx2d = ctx.getContext('2d');
                const energyGradient = ctx2d.createLinearGradient(0, 0, 0, 400);
                energyGradient.addColorStop(0, 'rgba(75, 192, 192, 0.4)');
                energyGradient.addColorStop(1, 'rgba(75, 192, 192, 0.05)');
    
                const fuelGradient = ctx2d.createLinearGradient(0, 0, 0, 400);
                fuelGradient.addColorStop(0, 'rgba(255, 99, 132, 0.3)');
                fuelGradient.addColorStop(1, 'rgba(255, 99, 132, 0.05)');
    
                const fuelLossGradient = ctx2d.createLinearGradient(0, 0, 0, 400);
                fuelLossGradient.addColorStop(0, 'rgba(255, 159, 64, 0.5)');
                fuelLossGradient.addColorStop(1, 'rgba(255, 159, 64, 0.1)');
    
                const datasets = [
                    {
                        label: 'الطاقة المنتجة',
                        data: advancedChartData.energy,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: energyGradient,
                        borderWidth: 4,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointHoverRadius: 9,
                        pointBackgroundColor: 'rgb(75, 192, 192)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'الوقود المستهلك',
                        data: advancedChartData.fuel_consumed,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: fuelGradient,
                        borderWidth: 4,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointHoverRadius: 9,
                        pointBackgroundColor: 'rgb(255, 99, 132)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 3,
                        yAxisID: 'y1'
                    }
                ];
    
                // إضافة الفاقد في الوقود إذا كان موجوداً
                if (advancedChartData.fuel_loss && advancedChartData.fuel_loss.length > 0) {
                    datasets.push({
                        label: 'الفاقد في الوقود',
                        data: advancedChartData.fuel_loss,
                        type: 'bar',
                        backgroundColor: fuelLossGradient,
                        borderColor: 'rgb(255, 159, 64)',
                        borderWidth: 2,
                        borderRadius: 4,
                        yAxisID: 'y1',
                        order: 0 // عرضه خلف الخطوط
                    });
                }
    
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: advancedChartData.labels,
                        datasets: datasets
                    },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            rtl: true,
                            labels: {
                                font: {
                                    family: 'Tajawal, Arial, sans-serif',
                                    size: 14,
                                    weight: '700'
                                },
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            rtl: true,
                            backgroundColor: 'rgba(0, 0, 0, 0.85)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            padding: 14,
                            titleFont: {
                                family: 'Tajawal, Arial, sans-serif',
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                family: 'Tajawal, Arial, sans-serif',
                                size: 13
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                font: {
                                    family: 'Tajawal, Arial, sans-serif',
                                    size: 12
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'الطاقة المنتجة (kWh)',
                                font: {
                                    family: 'Tajawal, Arial, sans-serif',
                                    size: 14,
                                    weight: 'bold'
                                },
                                color: 'rgb(75, 192, 192)'
                            },
                            ticks: {
                                font: {
                                    family: 'Tajawal, Arial, sans-serif',
                                    size: 12
                                },
                                color: 'rgb(75, 192, 192)'
                            },
                            grid: {
                                color: 'rgba(75, 192, 192, 0.2)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'الوقود (لتر)',
                                font: {
                                    family: 'Tajawal, Arial, sans-serif',
                                    size: 14,
                                    weight: 'bold'
                                },
                                color: 'rgb(255, 99, 132)'
                            },
                            ticks: {
                                font: {
                                    family: 'Tajawal, Arial, sans-serif',
                                    size: 12
                                },
                                color: 'rgb(255, 99, 132)'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        }

        // Create charts for all user types
        createAdvancedEnergyFuelChart('advancedEnergyFuelChart');
        createAdvancedEnergyFuelChart('advancedEnergyFuelChartOwner');
        createAdvancedEnergyFuelChart('advancedEnergyFuelChartAdmin');
    }

    // Fuel by Generator Chart
    function createFuelByGeneratorChart(canvasId) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'الوقود المستهلك (لتر)',
                    data: chartData.fuel,
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        rtl: true,
                        labels: {
                            font: {
                                family: 'Tajawal, Arial, sans-serif',
                                size: 12
                            },
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        rtl: true,
                        titleFont: {
                            family: 'Tajawal, Arial, sans-serif',
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: 'Tajawal, Arial, sans-serif',
                            size: 12
                        },
                        padding: 12,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.2)',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: {
                                family: 'Tajawal, Arial, sans-serif',
                                size: 11
                            }
                        },
                        grid: {
                            display: true
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'الوقود المستهلك (لتر)',
                            font: {
                                family: 'Tajawal, Arial, sans-serif',
                                size: 13,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            font: {
                                family: 'Tajawal, Arial, sans-serif',
                                size: 11
                            },
                            callback: function(value) {
                                return value.toLocaleString('ar') + ' لتر';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                }
            }
        });
    }

    // Create Fuel by Generator charts
    createFuelByGeneratorChart('fuelByGeneratorChart');
    createFuelByGeneratorChart('fuelByGeneratorChartOwner');
    createFuelByGeneratorChart('fuelByGeneratorChartAdmin');
    }

    // Wait for both DOM and Chart.js to be ready
    function waitForChart() {
        if (typeof Chart !== 'undefined') {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCharts);
            } else {
                initCharts();
            }
        } else {
            // Chart.js not loaded yet, wait a bit and try again
            setTimeout(waitForChart, 100);
        }
    }

    // Start waiting for Chart.js
    waitForChart();
})();
</script>
@endif

@if(isset($pieChartData))
<script>
(function() {
    function initPieCharts() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded for pie charts');
            return;
        }

        if (typeof window.dashboardChartData === 'undefined' || typeof window.pieChartData === 'undefined') {
            console.error('Pie chart data is not available');
            return;
        }

        const pieData = window.pieChartData;

        // Generate colors for pie charts
        function generateColors(count) {
            const colors = [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(199, 199, 199, 0.8)',
                'rgba(83, 102, 255, 0.8)',
                'rgba(255, 99, 255, 0.8)',
                'rgba(99, 255, 132, 0.8)',
            ];
            return colors.slice(0, count);
        }

        // Create Horizontal Bar Chart for Energy Distribution (Better than Pie Chart)
        function createEnergyDistributionChart(canvasId, labels, data, details, title) {
            const ctx = document.getElementById(canvasId);
            if (!ctx || labels.length === 0 || data.length === 0) return;

            const colors = generateColors(labels.length);
            
            // Calculate percentages for display
            const total = data.reduce((sum, val) => sum + val, 0);
            const percentages = data.map(val => total > 0 ? ((val / total) * 100).toFixed(1) : 0);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels.map((label, i) => {
                        const value = data[i];
                        const percent = percentages[i];
                        return `${label} (${value.toLocaleString('ar')} kWh - ${percent}%)`;
                    }),
                    datasets: [{
                        label: title,
                        data: data,
                        backgroundColor: colors,
                        borderColor: colors.map(c => c.replace('0.8', '1')),
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    }],
                    details: details || []
                },
                options: {
                    indexAxis: 'y', // Horizontal bar chart
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 10,
                            top: 10,
                            bottom: 10
                        }
                    },
                    plugins: {
                        legend: {
                            display: false // Hide legend as labels contain the info
                        },
                        tooltip: {
                            rtl: true,
                            titleFont: {
                                family: 'Tajawal, Arial, sans-serif',
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                family: 'Tajawal, Arial, sans-serif',
                                size: 12
                            },
                            padding: 12,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            callbacks: {
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    return labels[index] || '';
                                },
                                label: function(context) {
                                    const index = context.dataIndex;
                                    const detail = context.chart.data.details && context.chart.data.details[index] 
                                        ? context.chart.data.details[index] 
                                        : null;
                                    
                                    let lines = [];
                                    const value = context.parsed.x;
                                    const percent = percentages[index];
                                    lines.push(`الطاقة المنتجة: ${value.toLocaleString('ar')} kWh (${percent}%)`);
                                    
                                    if (detail) {
                                        if (detail.fuel_consumed > 0) {
                                            lines.push(`الوقود المستهلك: ${detail.fuel_consumed.toLocaleString('ar')} لتر`);
                                        }
                                        if (detail.fuel_efficiency > 0) {
                                            lines.push(`كفاءة الوقود: ${detail.fuel_efficiency.toLocaleString('ar')} kWh/لتر`);
                                        }
                                        if (detail.avg_load > 0) {
                                            lines.push(`متوسط نسبة التحميل: ${detail.avg_load.toLocaleString('ar')}%`);
                                        }
                                        if (detail.records_count > 0) {
                                            lines.push(`عدد السجلات: ${detail.records_count.toLocaleString('ar')}`);
                                        }
                                    }
                                    
                                    return lines;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    family: 'Tajawal, Arial, sans-serif',
                                    size: 11
                                },
                                callback: function(value) {
                                    return value.toLocaleString('ar') + ' kWh';
                                }
                            },
                            title: {
                                display: true,
                                text: 'الطاقة المنتجة (kWh)',
                                font: {
                                    family: 'Tajawal, Arial, sans-serif',
                                    size: 13,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    family: 'Tajawal, Arial, sans-serif',
                                    size: 11
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

        // Generators Energy Distribution Charts (Horizontal Bar)
        if (pieData.generators && pieData.generators.labels.length > 0) {
            const genDetails = pieData.generators.details || [];
            createEnergyDistributionChart('generatorsPieChart', pieData.generators.labels, pieData.generators.data, genDetails, 'الطاقة المنتجة (kWh)');
            createEnergyDistributionChart('generatorsPieChartOwner', pieData.generators.labels, pieData.generators.data, genDetails, 'الطاقة المنتجة (kWh)');
            createEnergyDistributionChart('generatorsPieChartAdmin', pieData.generators.labels, pieData.generators.data, genDetails, 'الطاقة المنتجة (kWh)');
        }

        // Operators Energy Distribution Charts (Horizontal Bar)
        if (pieData.operators && pieData.operators.labels.length > 0) {
            const opDetails = pieData.operators.details || [];
            createEnergyDistributionChart('operatorsPieChart', pieData.operators.labels, pieData.operators.data, opDetails, 'الطاقة المنتجة (kWh)');
            createEnergyDistributionChart('operatorsPieChartOwner', pieData.operators.labels, pieData.operators.data, opDetails, 'الطاقة المنتجة (kWh)');
            createEnergyDistributionChart('operatorsPieChartAdmin', pieData.operators.labels, pieData.operators.data, opDetails, 'الطاقة المنتجة (kWh)');
        }

        // Governorates Energy Distribution Charts (Horizontal Bar)
        if (pieData.governorates && pieData.governorates.labels.length > 0) {
            const govDetails = pieData.governorates.details || [];
            createEnergyDistributionChart('governoratesPieChart', pieData.governorates.labels, pieData.governorates.data, govDetails, 'الطاقة المنتجة (kWh)');
            createEnergyDistributionChart('governoratesPieChartOwner', pieData.governorates.labels, pieData.governorates.data, govDetails, 'الطاقة المنتجة (kWh)');
            createEnergyDistributionChart('governoratesPieChartAdmin', pieData.governorates.labels, pieData.governorates.data, govDetails, 'الطاقة المنتجة (kWh)');
        }

    }

    // Pass pie chart data to JavaScript
    window.pieChartData = @json($pieChartData);

    // Wait for Chart.js to be ready
    function waitForPieChart() {
        if (typeof Chart !== 'undefined') {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPieCharts);
            } else {
                initPieCharts();
            }
        } else {
            setTimeout(waitForPieChart, 100);
        }
    }

    waitForPieChart();
})();
</script>
@endif
@endpush


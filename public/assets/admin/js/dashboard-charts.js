/**
 * Dashboard Charts Module — Nour Admin Panel
 * Loads chart data via AJAX and creates Chart.js instances
 */
const DashboardCharts = {
    config: null,
    charts: {},

    init(config) {
        this.config = config;
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }

        // Load charts via AJAX
        this.loadLineCharts();
        this.loadPieCharts();

        // Load comparison tables if applicable
        if (config.role === 'energy_authority' || config.role === 'super_admin') {
            this.loadComparisonTables();
        }
    },

    // ─── Shared Chart Options ───
    baseOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top', rtl: true, labels: { font: { family: 'Tajawal, Arial, sans-serif' } } },
                tooltip: { rtl: true, titleFont: { family: 'Tajawal, Arial, sans-serif' }, bodyFont: { family: 'Tajawal, Arial, sans-serif' } }
            },
            scales: {
                x: { ticks: { font: { family: 'Tajawal, Arial, sans-serif' } } },
                y: { ticks: { font: { family: 'Tajawal, Arial, sans-serif' } } }
            }
        };
    },

    colors: [
        '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6',
        '#1abc9c', '#e67e22', '#34495e', '#16a085', '#d35400'
    ],

    // ─── Line/Bar Charts (Performance Analysis) ───
    loadLineCharts() {
        const url = this.config.routes.chartData;

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                this.hideSkeleton('advancedEnergyFuelChart-skeleton');
                const hasAnyData = data.energy?.some(v => v > 0) || data.fuel?.some(v => v > 0);
                if (!data.labels || data.labels.length === 0 || !hasAnyData) {
                    this.showEmpty('advancedEnergyFuelChart');
                    return;
                }
                this.createAdvancedEnergyFuelChart(data);
                this.createFuelByGeneratorChart(data);
                this.createLoadChart(data);
            })
            .catch(err => {
                console.error('Failed to load chart data:', err);
                this.hideSkeleton('advancedEnergyFuelChart-skeleton');
                this.showEmpty('advancedEnergyFuelChart');
            });
    },

    createAdvancedEnergyFuelChart(data) {
        const ctx = document.getElementById('advancedEnergyFuelChart');
        if (!ctx || !data.advanced_chart || !data.advanced_chart.labels.length) return;
        this.hideSkeleton('advancedEnergyFuelChart-skeleton');

        const adv = data.advanced_chart;
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
                label: 'الطاقة المنتجة', data: adv.energy,
                borderColor: 'rgb(75, 192, 192)', backgroundColor: energyGradient,
                borderWidth: 4, fill: true, tension: 0.4,
                pointRadius: 6, pointHoverRadius: 9, pointBackgroundColor: 'rgb(75, 192, 192)',
                pointBorderColor: '#fff', pointBorderWidth: 3, yAxisID: 'y'
            },
            {
                label: 'الوقود المستهلك', data: adv.fuel_consumed,
                borderColor: 'rgb(255, 99, 132)', backgroundColor: fuelGradient,
                borderWidth: 4, fill: true, tension: 0.4,
                pointRadius: 6, pointHoverRadius: 9, pointBackgroundColor: 'rgb(255, 99, 132)',
                pointBorderColor: '#fff', pointBorderWidth: 3, yAxisID: 'y1'
            }
        ];

        if (adv.fuel_loss && adv.fuel_loss.length > 0) {
            datasets.push({
                label: 'الفاقد في الوقود', data: adv.fuel_loss, type: 'bar',
                backgroundColor: fuelLossGradient, borderColor: 'rgb(255, 159, 64)',
                borderWidth: 2, borderRadius: 4, yAxisID: 'y1', order: 0
            });
        }

        this.charts.advancedEnergyFuel = new Chart(ctx, {
            type: 'line',
            data: { labels: adv.labels, datasets },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: true, position: 'top', rtl: true, labels: { font: { family: 'Tajawal, Arial, sans-serif', size: 14, weight: '700' }, padding: 20, usePointStyle: true } },
                    tooltip: { rtl: true, backgroundColor: 'rgba(0,0,0,0.85)', titleColor: '#fff', bodyColor: '#fff', borderColor: 'rgba(255,255,255,0.2)', borderWidth: 1, padding: 14, titleFont: { family: 'Tajawal, Arial, sans-serif', size: 14, weight: 'bold' }, bodyFont: { family: 'Tajawal, Arial, sans-serif', size: 13 } }
                },
                scales: {
                    x: { ticks: { font: { family: 'Tajawal, Arial, sans-serif', size: 12 } }, grid: { color: 'rgba(0,0,0,0.08)' } },
                    y: { type: 'linear', display: true, position: 'left', beginAtZero: true, title: { display: true, text: 'الطاقة المنتجة (kWh)', font: { family: 'Tajawal, Arial, sans-serif', size: 14, weight: 'bold' }, color: 'rgb(75, 192, 192)' }, ticks: { font: { family: 'Tajawal, Arial, sans-serif', size: 12 }, color: 'rgb(75, 192, 192)' }, grid: { color: 'rgba(75, 192, 192, 0.2)' } },
                    y1: { type: 'linear', display: true, position: 'right', beginAtZero: true, title: { display: true, text: 'الوقود (لتر)', font: { family: 'Tajawal, Arial, sans-serif', size: 14, weight: 'bold' }, color: 'rgb(255, 99, 132)' }, ticks: { font: { family: 'Tajawal, Arial, sans-serif', size: 12 }, color: 'rgb(255, 99, 132)' }, grid: { drawOnChartArea: false } }
                }
            }
        });
    },

    createFuelByGeneratorChart(data) {
        const ctx = document.getElementById('fuelByGeneratorChart');
        if (!ctx) return;

        this.charts.fuelByGenerator = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'الوقود المستهلك (لتر)', data: data.fuel,
                    backgroundColor: 'rgba(255, 99, 132, 0.7)', borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 2, borderRadius: 6
                }]
            },
            options: { ...this.baseOptions(), plugins: { ...this.baseOptions().plugins, legend: { display: true, position: 'top', rtl: true, labels: { font: { family: 'Tajawal, Arial, sans-serif', size: 13, weight: '600' } } } } }
        });
    },

    createLoadChart(data) {
        const ctx = document.getElementById('loadChart');
        if (!ctx) return;

        this.charts.load = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'نسبة التحميل (%)', data: data.load,
                    borderColor: 'rgb(255, 206, 86)', backgroundColor: 'rgba(255, 206, 86, 0.1)',
                    tension: 0.4, fill: true
                }]
            },
            options: this.baseOptions()
        });
    },

    // ─── Side Chart (operators for admin, generators for others) ───
    loadPieCharts() {
        const url = this.config.routes.pieChartData;
        const role = this.config.role;

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                // Pick dataset based on role
                const isAdmin = role === 'super_admin' || role === 'energy_authority';
                const dataset = isAdmin ? data.operators : data.generators;
                const hasData = dataset?.data?.length > 0;

                if (!hasData) {
                    this.showEmpty('sideChart');
                    return;
                }
                this.createDistributionChart('sideChart', dataset);
            })
            .catch(err => {
                console.error('Failed to load side chart:', err);
                this.showEmpty('sideChart');
            });
    },

    createDistributionChart(canvasId, dataset) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || !dataset || !dataset.labels || dataset.labels.length === 0) return;

        this.charts[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dataset.labels,
                datasets: [{
                    label: 'الطاقة المنتجة (kWh)',
                    data: dataset.data,
                    backgroundColor: this.colors.slice(0, dataset.data.length),
                    borderWidth: 1, borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: (ctx) => {
                                const detail = dataset.details?.[ctx.dataIndex];
                                if (detail) {
                                    return [
                                        `الطاقة: ${ctx.parsed.x.toLocaleString()} kWh`,
                                        `الوقود: ${detail.fuel_consumed?.toLocaleString()} لتر`,
                                        `الكفاءة: ${detail.fuel_efficiency} kWh/لتر`,
                                        `التحميل: ${detail.avg_load}%`
                                    ];
                                }
                                return ctx.parsed.x.toLocaleString() + ' kWh';
                            }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { font: { family: 'Tajawal, Arial, sans-serif' }, callback: v => v.toLocaleString() } },
                    y: { ticks: { font: { family: 'Tajawal, Arial, sans-serif', size: 12 } } }
                }
            }
        });
    },

    // ─── Comparison Tables (AJAX HTML) ───
    loadComparisonTables() {
        const ops = this.config.routes.operatorsComparison;
        const units = this.config.routes.generationUnitsComparison;

        if (ops) {
            fetch(ops, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => {
                    const el = document.getElementById('operators-comparison-container');
                    if (el) el.innerHTML = html;
                })
                .catch(err => console.error('Failed to load operators comparison:', err));
        }

        if (units) {
            fetch(units, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => {
                    const el = document.getElementById('units-comparison-container');
                    if (el) el.innerHTML = html;
                })
                .catch(err => console.error('Failed to load units comparison:', err));
        }
    },

    // ─── Helpers ───
    hideSkeleton(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    },

    showEmpty(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (canvas) {
            const container = canvas.closest('.chart-container') || canvas.parentElement;
            container.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:200px;color:#9CA3AF;"><i class="bi bi-bar-chart" style="font-size:2.5rem;opacity:0.4;margin-bottom:0.5rem;"></i><p style="margin:0;font-size:0.88rem;">لا توجد بيانات كافية لعرض المخطط</p></div>';
        }
    },

    fadeIn(sectionId) {
        const el = document.getElementById(sectionId);
        if (el) {
            requestAnimationFrame(() => el.classList.add('loaded'));
        }
    }
};

<!-- Operations Statistics - لسلطة الطاقة -->
@if(isset($operationStats) && $operationStats['total'] > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-graph-up me-2"></i>
                        إحصائيات التشغيل الشاملة
                    </h5>
                    <p class="dashboard-card-subtitle">نظرة شاملة على أداء قطاع التوليد</p>
                </div>
                <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-outline-primary btn-sm">
                    عرض جميع السجلات <i class="bi bi-arrow-left ms-1"></i>
                </a>
            </div>
            <div class="dashboard-card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-2">
                        <div class="dashboard-stat-mini dashboard-stat-mini-info">
                            <div class="dashboard-stat-mini-icon">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <div class="dashboard-stat-mini-label">إجمالي السجلات</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total']) }}</div>
                            <div class="dashboard-stat-mini-badges">
                                <span class="badge badge-info">{{ $operationStats['this_month'] }} هذا الشهر</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="dashboard-stat-mini dashboard-stat-mini-success">
                            <div class="dashboard-stat-mini-icon">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                            <div class="dashboard-stat-mini-label">الطاقة المنتجة</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total_energy'], 0) }}</div>
                            <div class="dashboard-stat-mini-unit">kWh</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="dashboard-stat-mini dashboard-stat-mini-warning">
                            <div class="dashboard-stat-mini-icon">
                                <i class="bi bi-fuel-pump-fill"></i>
                            </div>
                            <div class="dashboard-stat-mini-label">الوقود المستهلك</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total_fuel'], 0) }}</div>
                            <div class="dashboard-stat-mini-unit">لتر</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="dashboard-stat-mini dashboard-stat-mini-primary">
                            <div class="dashboard-stat-mini-icon">
                                <i class="bi bi-speedometer2"></i>
                            </div>
                            <div class="dashboard-stat-mini-label">الكفاءة</div>
                            @php
                                $efficiency = $operationStats['total_fuel'] > 0 
                                    ? round($operationStats['total_energy'] / $operationStats['total_fuel'], 2) 
                                    : 0;
                            @endphp
                            <div class="dashboard-stat-mini-value">{{ $efficiency }}</div>
                            <div class="dashboard-stat-mini-unit">kWh/لتر</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="dashboard-stat-mini dashboard-stat-mini-secondary">
                            <div class="dashboard-stat-mini-icon">
                                <i class="bi bi-percent"></i>
                            </div>
                            <div class="dashboard-stat-mini-label">متوسط التحميل</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['avg_load'], 1) }}%</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="dashboard-stat-mini dashboard-stat-mini-danger">
                            <div class="dashboard-stat-mini-icon">
                                <i class="bi bi-arrow-down-circle"></i>
                            </div>
                            <div class="dashboard-stat-mini-label">هذا الأسبوع</div>
                            <div class="dashboard-stat-mini-value">{{ $operationStats['this_week'] }}</div>
                            <div class="dashboard-stat-mini-unit">سجل</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- رسم بياني للإنتاج مقابل الفاقد حسب المشغل --}}
@if(isset($operatorsComparison) && count($operatorsComparison) > 0)
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-6">
        <div class="dashboard-card h-100">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-bar-chart-fill me-2"></i>
                        الإنتاج حسب المشغل
                    </h5>
                    <p class="dashboard-card-subtitle">مقارنة الطاقة المنتجة لكل مشغل</p>
                </div>
            </div>
            <div class="dashboard-card-body">
                <canvas id="operatorsProductionChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="dashboard-card h-100">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-pie-chart-fill me-2"></i>
                        نسبة الفاقد حسب المشغل
                    </h5>
                    <p class="dashboard-card-subtitle">توزيع نسب الفاقد بين المشغلين</p>
                </div>
            </div>
            <div class="dashboard-card-body">
                <canvas id="operatorsLossChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
        <div class="dashboard-card h-100">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-graph-up-arrow me-2"></i>
                        مقارنة الكفاءة بين المشغلين
                    </h5>
                    <p class="dashboard-card-subtitle">كفاءة تحويل الوقود إلى طاقة (kWh/لتر)</p>
                </div>
            </div>
            <div class="dashboard-card-body">
                <canvas id="operatorsEfficiencyChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="dashboard-card h-100">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-shield-check me-2"></i>
                        حالة الامتثال
                    </h5>
                    <p class="dashboard-card-subtitle">توزيع حالات شهادات السلامة</p>
                </div>
            </div>
            <div class="dashboard-card-body">
                <canvas id="complianceStatusChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // بيانات المشغلين
    const operatorsData = @json($operatorsComparison);
    
    // ألوان متنوعة
    const colors = [
        '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6',
        '#1abc9c', '#e67e22', '#34495e', '#16a085', '#d35400'
    ];
    
    // رسم بياني الإنتاج
    const productionCtx = document.getElementById('operatorsProductionChart');
    if (productionCtx) {
        new Chart(productionCtx, {
            type: 'bar',
            data: {
                labels: operatorsData.map(o => o.name),
                datasets: [{
                    label: 'الطاقة المنتجة (kWh)',
                    data: operatorsData.map(o => o.total_energy),
                    backgroundColor: colors,
                    borderColor: colors.map(c => c),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y.toLocaleString() + ' kWh';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    // رسم بياني الفاقد
    const lossCtx = document.getElementById('operatorsLossChart');
    if (lossCtx) {
        new Chart(lossCtx, {
            type: 'doughnut',
            data: {
                labels: operatorsData.map(o => o.name),
                datasets: [{
                    data: operatorsData.map(o => o.energy_loss),
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        rtl: true,
                        labels: { font: { family: 'Cairo, sans-serif' } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const operator = operatorsData[context.dataIndex];
                                return `${operator.name}: ${context.parsed.toLocaleString()} kWh (${operator.loss_percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // رسم بياني الكفاءة
    const efficiencyCtx = document.getElementById('operatorsEfficiencyChart');
    if (efficiencyCtx) {
        new Chart(efficiencyCtx, {
            type: 'bar',
            data: {
                labels: operatorsData.map(o => o.name),
                datasets: [{
                    label: 'الكفاءة (kWh/لتر)',
                    data: operatorsData.map(o => o.efficiency),
                    backgroundColor: operatorsData.map(o => 
                        o.efficiency >= 3 ? '#2ecc71' : (o.efficiency >= 2 ? '#f39c12' : '#e74c3c')
                    ),
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x + ' kWh/لتر';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 5,
                        title: { display: true, text: 'kWh/لتر' }
                    }
                }
            }
        });
    }
    
    // رسم بياني حالة الامتثال
    const complianceCtx = document.getElementById('complianceStatusChart');
    if (complianceCtx) {
        const complianceCounts = {
            VALID: operatorsData.filter(o => o.compliance_status === 'VALID').length,
            EXPIRED: operatorsData.filter(o => o.compliance_status === 'EXPIRED').length,
            PENDING: operatorsData.filter(o => o.compliance_status === 'PENDING').length,
            UNKNOWN: operatorsData.filter(o => !['VALID', 'EXPIRED', 'PENDING'].includes(o.compliance_status)).length
        };
        
        new Chart(complianceCtx, {
            type: 'pie',
            data: {
                labels: ['سارية', 'منتهية', 'معلقة', 'غير محدد'],
                datasets: [{
                    data: [complianceCounts.VALID, complianceCounts.EXPIRED, complianceCounts.PENDING, complianceCounts.UNKNOWN],
                    backgroundColor: ['#2ecc71', '#e74c3c', '#f39c12', '#95a5a6'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        rtl: true,
                        labels: { font: { family: 'Cairo, sans-serif' } }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endif

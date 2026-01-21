<!-- Operations Statistics - للمشغل -->
@if(isset($operationStats) && $operationStats['total'] > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dashboard-card dashboard-card-enhanced">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-graph-up-arrow me-2"></i>
                        إحصائيات التشغيل
                    </h5>
                    <p class="dashboard-card-subtitle">نظرة شاملة على أداء المولدات والطاقة المنتجة</p>
                </div>
                <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-outline-primary btn-sm">
                    عرض التفاصيل <i class="bi bi-arrow-left ms-1"></i>
                </a>
            </div>
            <div class="dashboard-card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="dashboard-stat-mini dashboard-stat-mini-primary">
                            <div class="dashboard-stat-mini-icon">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <div class="dashboard-stat-mini-content">
                                <div class="dashboard-stat-mini-label">إجمالي السجلات</div>
                                <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total']) }}</div>
                                <div class="dashboard-stat-mini-badges">
                                    <span class="badge badge-info">{{ $operationStats['this_month'] }} هذا الشهر</span>
                                    <span class="badge badge-primary">{{ $operationStats['this_week'] }} هذا الأسبوع</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dashboard-stat-mini dashboard-stat-mini-success">
                            <div class="dashboard-stat-mini-icon">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                            <div class="dashboard-stat-mini-content">
                                <div class="dashboard-stat-mini-label">الطاقة المنتجة</div>
                                <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total_energy'], 2) }}</div>
                                <div class="dashboard-stat-mini-unit">kWh</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dashboard-stat-mini dashboard-stat-mini-warning">
                            <div class="dashboard-stat-mini-icon">
                                <i class="bi bi-fuel-pump"></i>
                            </div>
                            <div class="dashboard-stat-mini-content">
                                <div class="dashboard-stat-mini-label">الوقود المستهلك</div>
                                <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total_fuel'], 2) }}</div>
                                <div class="dashboard-stat-mini-unit">لتر</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dashboard-stat-mini dashboard-stat-mini-info">
                            <div class="dashboard-stat-mini-icon">
                                <i class="bi bi-speedometer2"></i>
                            </div>
                            <div class="dashboard-stat-mini-content">
                                <div class="dashboard-stat-mini-label">متوسط نسبة التحميل</div>
                                <div class="dashboard-stat-mini-value">{{ number_format($operationStats['avg_load'], 1) }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif





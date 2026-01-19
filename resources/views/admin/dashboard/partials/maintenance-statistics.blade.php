<!-- Maintenance Statistics - للفني -->
@if(auth()->user()->isTechnician() && isset($maintenanceStats) && $maintenanceStats['total'] > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-tools me-2"></i>
                        إحصائيات الصيانة
                    </h5>
                    <p class="dashboard-card-subtitle">نظرة شاملة على سجلات الصيانة</p>
                </div>
                <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-outline-danger btn-sm">
                    عرض التفاصيل <i class="bi bi-arrow-left ms-1"></i>
                </a>
            </div>
            <div class="dashboard-card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="dashboard-stat-mini">
                            <div class="dashboard-stat-mini-label">إجمالي السجلات</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($maintenanceStats['total']) }}</div>
                            <div class="dashboard-stat-mini-badges">
                                <span class="badge badge-warning">{{ $maintenanceStats['this_month'] }} هذا الشهر</span>
                            </div>
                        </div>
                    </div>
                    @if($maintenanceStats['total_cost'] > 0)
                    <div class="col-6 col-md-4">
                        <div class="dashboard-stat-mini">
                            <div class="dashboard-stat-mini-label">التكلفة الإجمالية</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($maintenanceStats['total_cost'], 0) }}</div>
                            <div class="dashboard-stat-mini-unit">₪</div>
                        </div>
                    </div>
                    @endif
                    @if($maintenanceStats['total_downtime'] > 0)
                    <div class="col-6 col-md-4">
                        <div class="dashboard-stat-mini">
                            <div class="dashboard-stat-mini-label">وقت التوقف</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($maintenanceStats['total_downtime'], 1) }}</div>
                            <div class="dashboard-stat-mini-unit">ساعة</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif





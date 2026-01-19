{{-- إحصائيات الموظف والفني --}}
<!-- Generators -->
<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-success">
        <div class="dashboard-stat-icon">
            <i class="bi bi-lightning-charge-fill"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">المولدات</div>
            <div class="dashboard-stat-value">{{ number_format($stats['generators']['total'] ?? 0) }}</div>
            @if(isset($stats['generators']['active']))
                <div class="dashboard-stat-badges">
                    <span class="badge badge-success">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ $stats['generators']['active'] }} نشطة
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Operators -->
<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-info">
        <div class="dashboard-stat-icon">
            <i class="bi bi-building"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">المشغل</div>
            <div class="dashboard-stat-value">{{ number_format($stats['operators']['total'] ?? 0) }}</div>
        </div>
    </div>
</div>

<!-- Operation Logs -->
@if(isset($operationStats) && $operationStats['total'] > 0)
<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-warning">
        <div class="dashboard-stat-icon">
            <i class="bi bi-journal-text"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">سجلات التشغيل</div>
            <div class="dashboard-stat-value">{{ number_format($operationStats['total']) }}</div>
            <div class="dashboard-stat-badges">
                <span class="badge badge-info">{{ $operationStats['this_month'] }} هذا الشهر</span>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Maintenance Records (للفني فقط) -->
@if(auth()->user()->isTechnician() && isset($maintenanceStats) && $maintenanceStats['total'] > 0)
<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-danger">
        <div class="dashboard-stat-icon">
            <i class="bi bi-tools"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">سجلات الصيانة</div>
            <div class="dashboard-stat-value">{{ number_format($maintenanceStats['total']) }}</div>
            <div class="dashboard-stat-badges">
                <span class="badge badge-warning">{{ $maintenanceStats['this_month'] }} هذا الشهر</span>
            </div>
        </div>
    </div>
</div>
@endif






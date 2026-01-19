<!-- Users -->
<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-primary">
        <div class="dashboard-stat-icon">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">المستخدمون</div>
            <div class="dashboard-stat-value">{{ number_format($stats['users']['total']) }}</div>
            <div class="dashboard-stat-badges">
                <span class="badge badge-primary">{{ $stats['users']['super_admins'] }} مدير</span>
                <span class="badge badge-info">{{ $stats['users']['company_owners'] }} صاحب</span>
                <span class="badge badge-success">{{ $stats['users']['employees'] }} موظف</span>
            </div>
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
            <div class="dashboard-stat-label">المشغلون</div>
            <div class="dashboard-stat-value">{{ number_format($stats['operators']['total']) }}</div>
            @if(isset($stats['operators']['active']))
                <div class="dashboard-stat-badges">
                    <span class="badge badge-success">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ $stats['operators']['active'] }} نشط
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Generators -->
<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-success">
        <div class="dashboard-stat-icon">
            <i class="bi bi-lightning-charge-fill"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">المولدات</div>
            <div class="dashboard-stat-value">{{ number_format($stats['generators']['total']) }}</div>
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





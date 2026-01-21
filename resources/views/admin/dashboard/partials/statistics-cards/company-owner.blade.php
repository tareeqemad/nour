{{-- إحصائيات المشغل - مرتبة حسب الأهمية --}}
<!-- Generation Units -->
@if(isset($stats['generation_units']))
<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-primary">
        <div class="dashboard-stat-icon">
            <i class="bi bi-building-fill"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">وحدات التوليد</div>
            <div class="dashboard-stat-value">{{ number_format($stats['generation_units']['total'] ?? 0) }}</div>
            @if(isset($stats['generation_units']['active']))
                <div class="dashboard-stat-badges">
                    <span class="badge badge-success">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ $stats['generation_units']['active'] }} نشطة
                    </span>
                </div>
            @endif
        </div>
        <a href="{{ route('admin.generation-units.index') }}" class="dashboard-stat-link">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>
@endif

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
        <a href="{{ route('admin.generators.index') }}" class="dashboard-stat-link">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>

<!-- Employees -->
@if(isset($stats['employees']))
<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-warning">
        <div class="dashboard-stat-icon">
            <i class="bi bi-person-badge"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">الموظفون</div>
            <div class="dashboard-stat-value">{{ number_format($stats['employees']['total']) }}</div>
        </div>
        @php
            $operator = auth()->user()->ownedOperators->first();
        @endphp
        @if($operator)
            <a href="{{ route('admin.operators.employees', $operator) }}" class="dashboard-stat-link">
                <i class="bi bi-arrow-left"></i>
            </a>
        @endif
    </div>
</div>
@endif

<!-- Operators -->
<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-info">
        <div class="dashboard-stat-icon">
            <i class="bi bi-building"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">المشغل</div>
            <div class="dashboard-stat-value">{{ number_format($stats['operators']['total'] ?? 0) }}</div>
            @if(isset($stats['operators']['active']))
                <div class="dashboard-stat-badges">
                    <span class="badge badge-success">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ $stats['operators']['active'] }} نشط
                    </span>
                </div>
            @endif
        </div>
        <a href="{{ route('admin.operators.profile') }}" class="dashboard-stat-link">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>






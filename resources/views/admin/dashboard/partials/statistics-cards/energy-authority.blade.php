{{-- إحصائيات سلطة الطاقة الشاملة --}}

<!-- Row 1: المشغلين ووحدات التوليد والمولدات -->
<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-info">
        <div class="dashboard-stat-icon">
            <i class="bi bi-building"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">المشغلون</div>
            <div class="dashboard-stat-value">{{ number_format($stats['operators']['total'] ?? 0) }}</div>
            <div class="dashboard-stat-badges">
                <span class="badge badge-success">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ $stats['operators']['active'] ?? 0 }} نشط
                </span>
                @if(($stats['operators']['pending'] ?? 0) > 0)
                <span class="badge badge-warning">
                    <i class="bi bi-hourglass-split me-1"></i>
                    {{ $stats['operators']['pending'] }} بانتظار الموافقة
                </span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-primary">
        <div class="dashboard-stat-icon">
            <i class="bi bi-diagram-3-fill"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">وحدات التوليد</div>
            <div class="dashboard-stat-value">{{ number_format($stats['generation_units']['total'] ?? 0) }}</div>
            <div class="dashboard-stat-badges">
                <span class="badge badge-success">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ $stats['generation_units']['active'] ?? 0 }} نشطة
                </span>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-success">
        <div class="dashboard-stat-icon">
            <i class="bi bi-lightning-charge-fill"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">المولدات</div>
            <div class="dashboard-stat-value">{{ number_format($stats['generators']['total'] ?? 0) }}</div>
            <div class="dashboard-stat-badges">
                <span class="badge badge-success">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ $stats['generators']['active'] ?? 0 }} نشطة
                </span>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-warning">
        <div class="dashboard-stat-icon">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">إجمالي المستفيدين</div>
            <div class="dashboard-stat-value">{{ number_format($stats['capacity']['total_beneficiaries'] ?? 0) }}</div>
        </div>
    </div>
</div>

<!-- Row 2: الإنتاج والوقود -->
<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-energy">
        <div class="dashboard-stat-icon">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">إجمالي الطاقة المنتجة</div>
            <div class="dashboard-stat-value">{{ number_format($stats['production']['total_energy'] ?? 0, 0) }}</div>
            <div class="dashboard-stat-unit">kWh</div>
            <div class="dashboard-stat-badges">
                <span class="badge badge-info">
                    <i class="bi bi-calendar-month me-1"></i>
                    {{ number_format($stats['production']['this_month_energy'] ?? 0, 0) }} هذا الشهر
                </span>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-fuel">
        <div class="dashboard-stat-icon">
            <i class="bi bi-fuel-pump-fill"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">إجمالي الوقود المستهلك</div>
            <div class="dashboard-stat-value">{{ number_format($stats['production']['total_fuel'] ?? 0, 0) }}</div>
            <div class="dashboard-stat-unit">لتر</div>
            <div class="dashboard-stat-badges">
                <span class="badge badge-info">
                    <i class="bi bi-calendar-month me-1"></i>
                    {{ number_format($stats['production']['this_month_fuel'] ?? 0, 0) }} هذا الشهر
                </span>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-efficiency">
        <div class="dashboard-stat-icon">
            <i class="bi bi-speedometer2"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">متوسط الكفاءة</div>
            <div class="dashboard-stat-value">{{ $stats['production']['avg_efficiency'] ?? 0 }}</div>
            <div class="dashboard-stat-unit">kWh/لتر</div>
        </div>
    </div>
</div>

<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-capacity">
        <div class="dashboard-stat-icon">
            <i class="bi bi-battery-charging"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">إجمالي القدرة المركبة</div>
            <div class="dashboard-stat-value">{{ number_format($stats['capacity']['total_generator_capacity'] ?? 0, 0) }}</div>
            <div class="dashboard-stat-unit">kVA</div>
        </div>
    </div>
</div>

<!-- Row 3: الامتثال والصيانة -->
<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-compliance">
        <div class="dashboard-stat-icon">
            <i class="bi bi-shield-check"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">شهادات السلامة</div>
            <div class="dashboard-stat-value">{{ $stats['compliance']['total'] ?? 0 }}</div>
            <div class="dashboard-stat-badges">
                <span class="badge badge-success">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ $stats['compliance']['valid'] ?? 0 }} سارية
                </span>
                @if(($stats['compliance']['expired'] ?? 0) > 0)
                <span class="badge badge-danger">
                    <i class="bi bi-x-circle me-1"></i>
                    {{ $stats['compliance']['expired'] }} منتهية
                </span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-maintenance">
        <div class="dashboard-stat-icon">
            <i class="bi bi-tools"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">صيانة هذا الشهر</div>
            <div class="dashboard-stat-value">{{ $stats['maintenance']['this_month'] ?? 0 }}</div>
            <div class="dashboard-stat-badges">
                <span class="badge badge-warning">
                    <i class="bi bi-clock me-1"></i>
                    {{ $stats['maintenance']['total_downtime'] ?? 0 }} ساعة توقف
                </span>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-cost">
        <div class="dashboard-stat-icon">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">تكاليف الصيانة</div>
            <div class="dashboard-stat-value">{{ number_format($stats['maintenance']['total_cost'] ?? 0, 0) }}</div>
            <div class="dashboard-stat-unit">₪</div>
        </div>
    </div>
</div>

<div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card dashboard-stat-units">
        <div class="dashboard-stat-icon">
            <i class="bi bi-gear-wide-connected"></i>
        </div>
        <div class="dashboard-stat-content">
            <div class="dashboard-stat-label">إجمالي قدرة الوحدات</div>
            <div class="dashboard-stat-value">{{ number_format($stats['capacity']['total_unit_capacity'] ?? 0, 0) }}</div>
            <div class="dashboard-stat-unit">kW</div>
        </div>
    </div>
</div>

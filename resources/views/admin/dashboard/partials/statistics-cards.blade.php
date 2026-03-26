@php $user = auth()->user(); @endphp

@if($user->isSuperAdmin() || $user->isAdmin())
<div class="d-kpi-grid mb-3">
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #24308F, #1a2270);">
        <i class="bi bi-building d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($stats['operators']['total'] ?? 0) }}</div>
        <div class="d-kpi-block-label">مشغل</div>
        @if(($stats['operators']['active'] ?? 0) > 0)
        <div class="d-kpi-block-badge">{{ $stats['operators']['active'] }} نشط</div>
        @endif
    </div>
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #059669, #047857);">
        <i class="bi bi-lightning-charge-fill d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($stats['generators']['total'] ?? 0) }}</div>
        <div class="d-kpi-block-label">مولد</div>
        @if(($stats['generators']['active'] ?? 0) > 0)
        <div class="d-kpi-block-badge">{{ $stats['generators']['active'] }} نشط</div>
        @endif
    </div>
    @if($user->isSuperAdmin())
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #0284C7, #0369A1);">
        <i class="bi bi-people-fill d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($stats['users']['total'] ?? 0) }}</div>
        <div class="d-kpi-block-label">مستخدم</div>
    </div>
    @endif
    @if(isset($operationStats) && ($operationStats['total'] ?? 0) > 0)
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #D97706, #B45309);">
        <i class="bi bi-journal-text d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($operationStats['total']) }}</div>
        <div class="d-kpi-block-label">سجل تشغيل</div>
        @if(($operationStats['this_month'] ?? 0) > 0)
        <div class="d-kpi-block-badge">{{ $operationStats['this_month'] }} هذا الشهر</div>
        @endif
    </div>
    @endif
</div>

@elseif($user->isEnergyAuthority())
<div class="d-kpi-grid mb-3">
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #24308F, #1a2270);">
        <i class="bi bi-building d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($stats['operators']['total'] ?? 0) }}</div>
        <div class="d-kpi-block-label">مشغل</div>
        @if(($stats['operators']['active'] ?? 0) > 0)
        <div class="d-kpi-block-badge">{{ $stats['operators']['active'] }} نشط</div>
        @endif
    </div>
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #059669, #047857);">
        <i class="bi bi-lightning-charge-fill d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($stats['generators']['total'] ?? 0) }}</div>
        <div class="d-kpi-block-label">مولد</div>
    </div>
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #0284C7, #0369A1);">
        <i class="bi bi-diagram-3-fill d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($stats['generation_units']['total'] ?? 0) }}</div>
        <div class="d-kpi-block-label">وحدة توليد</div>
    </div>
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #D97706, #B45309);">
        <i class="bi bi-people-fill d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($stats['capacity']['total_beneficiaries'] ?? 0) }}</div>
        <div class="d-kpi-block-label">مستفيد</div>
    </div>
</div>

@elseif($user->isCompanyOwner())
<div class="d-kpi-grid mb-3">
    @if(isset($stats['generation_units']))
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #24308F, #1a2270);">
        <i class="bi bi-building-fill d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($stats['generation_units']['total'] ?? 0) }}</div>
        <div class="d-kpi-block-label">وحدة توليد</div>
        @if(($stats['generation_units']['active'] ?? 0) > 0)
        <div class="d-kpi-block-badge">{{ $stats['generation_units']['active'] }} نشطة</div>
        @endif
    </div>
    @endif
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #059669, #047857);">
        <i class="bi bi-lightning-charge-fill d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($stats['generators']['total'] ?? 0) }}</div>
        <div class="d-kpi-block-label">مولد</div>
        @if(($stats['generators']['active'] ?? 0) > 0)
        <div class="d-kpi-block-badge">{{ $stats['generators']['active'] }} نشط</div>
        @endif
    </div>
    @if(isset($stats['employees']))
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #7C3AED, #6D28D9);">
        <i class="bi bi-person-badge d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($stats['employees']['total']) }}</div>
        <div class="d-kpi-block-label">موظف</div>
    </div>
    @endif
</div>

@elseif($user->isTechnician() || $user->isCivilDefense())
@if(isset($tasksData) && isset($tasksData['stats']))
<div class="d-kpi-grid mb-3">
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #24308F, #1a2270);">
        <i class="bi bi-clipboard-check d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($tasksData['stats']['total'] ?? 0) }}</div>
        <div class="d-kpi-block-label">إجمالي المهام</div>
    </div>
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #D97706, #B45309);">
        <i class="bi bi-clock-history d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($tasksData['stats']['pending'] ?? 0) }}</div>
        <div class="d-kpi-block-label">بانتظار</div>
    </div>
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #0284C7, #0369A1);">
        <i class="bi bi-arrow-repeat d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($tasksData['stats']['in_progress'] ?? 0) }}</div>
        <div class="d-kpi-block-label">قيد التنفيذ</div>
    </div>
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #059669, #047857);">
        <i class="bi bi-check-circle d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($tasksData['stats']['completed'] ?? 0) }}</div>
        <div class="d-kpi-block-label">مكتملة</div>
    </div>
</div>
@endif

@elseif($user->isEmployee())
<div class="d-kpi-grid mb-3">
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #059669, #047857);">
        <i class="bi bi-lightning-charge-fill d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($stats['generators']['total'] ?? 0) }}</div>
        <div class="d-kpi-block-label">مولد</div>
    </div>
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #24308F, #1a2270);">
        <i class="bi bi-building d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($stats['operators']['total'] ?? 0) }}</div>
        <div class="d-kpi-block-label">مشغل</div>
    </div>
    @if(isset($operationStats) && $operationStats['total'] > 0)
    <div class="d-kpi-block" style="background: linear-gradient(135deg, #D97706, #B45309);">
        <i class="bi bi-journal-text d-kpi-block-watermark"></i>
        <div class="d-kpi-block-value">{{ number_format($operationStats['total']) }}</div>
        <div class="d-kpi-block-label">سجل تشغيل</div>
    </div>
    @endif
</div>
@endif

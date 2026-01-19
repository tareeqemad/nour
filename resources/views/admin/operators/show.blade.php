@extends('layouts.admin')

@section('title', 'تفاصيل المشغل')

@php
    $breadcrumbTitle = 'تفاصيل المشغل';
    $breadcrumbParent = 'إدارة المشغلين';
    $breadcrumbParentUrl = route('admin.operators.index');
@endphp

@push('styles')
<style>
    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
        height: 100%;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #fff;
        flex-shrink: 0;
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
    }

    .generation-unit-item {
        border-bottom: 1px solid #e5e7eb;
    }

    .generation-unit-item:last-child {
        border-bottom: none;
    }

    .generator-item-small {
        padding: 0.5rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .generator-item-small:last-child {
        border-bottom: none;
    }

    .info-item {
        margin-bottom: 1rem;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
    }

    .info-value {
        font-size: 0.95rem;
        color: #1f2937;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            {{-- Header Card with Summary --}}
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-person-badge me-2"></i>
                                {{ $operator->name }}
                            </h5>
                            <div class="general-subtitle d-flex align-items-center gap-2 flex-wrap">
                                <span>{{ $operator->unit_number ? $operator->unit_number . ' - ' : '' }}{{ $operator->unit_name ?? '—' }}</span>
                                @if($operator->getGovernorateLabel())
                                    <span>|</span>
                                    <span>{{ $operator->getGovernorateLabel() }}</span>
                                @endif
                                @if($operator->is_approved !== null)
                                    <span>|</span>
                                    <span class="badge {{ $operator->is_approved ? 'bg-success' : 'bg-warning' }}">
                                        <i class="bi bi-{{ $operator->is_approved ? 'check-circle' : 'clock' }} me-1"></i>
                                        {{ $operator->is_approved ? 'معتمد' : 'في انتظار الاعتماد' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('approve', $operator)
                                <form action="{{ route('admin.operators.toggle-approval', $operator) }}" method="POST" class="d-inline" id="approvalForm">
                                    @csrf
                                    <button type="submit" class="btn {{ $operator->is_approved ? 'btn-warning' : 'btn-success' }}" id="approvalBtn">
                                        <i class="bi bi-{{ $operator->is_approved ? 'x-circle' : 'check-circle' }} me-2"></i>
                                        {{ $operator->is_approved ? 'إلغاء الاعتماد' : 'اعتماد المشغل' }}
                                    </button>
                                </form>
                            @endcan
                            @can('viewAny', [\App\Models\ElectricityTariffPrice::class, $operator])
                                <a href="{{ route('admin.operators.tariff-prices.index', $operator) }}" class="btn btn-info">
                                    <i class="bi bi-currency-exchange me-2"></i>
                                    أسعار التعرفة
                                </a>
                            @endcan
                            @can('update', $operator)
                                <a href="{{ route('admin.operators.edit', $operator) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil me-2"></i>
                                    تعديل
                                </a>
                            @endcan
                            <a href="{{ route('admin.operators.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                رجوع
                            </a>
                        </div>
                    </div>

                    {{-- Statistics Cards --}}
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">وحدات التوليد</div>
                                        <div class="stat-value">{{ $operator->generation_units_count ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon bg-info">
                                        <i class="bi bi-lightning-charge"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">عدد المولدات</div>
                                        <div class="stat-value">{{ $operator->generators_count ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon bg-success">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">عدد الموظفين</div>
                                        <div class="stat-value">{{ $operator->users_count ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon bg-warning">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">سجلات التشغيل</div>
                                        <div class="stat-value">{{ $operator->operation_logs_count ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon {{ $operator->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">الحالة</div>
                                        <div class="stat-value">
                                            {{ $operator->status === 'active' ? 'فعال' : 'غير فعال' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Information --}}
            <div class="col-12 col-lg-8">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-info-circle me-2"></i>
                                معلومات المشغل
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Basic Information --}}
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">اسم المشغل</label>
                                    <div class="info-value">{{ $operator->name ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">البريد الإلكتروني</label>
                                    <div class="info-value">{{ $operator->email ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">رقم الهاتف</label>
                                    <div class="info-value">{{ $operator->phone ?? '—' }}</div>
                                </div>
                            </div>
                            @if($operator->phone_alt)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">رقم الهاتف البديل</label>
                                        <div class="info-value">{{ $operator->phone_alt }}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">اسم الوحدة</label>
                                    <div class="info-value">{{ $operator->unit_name ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">رقم الوحدة</label>
                                    <div class="info-value">{{ $operator->unit_number ?? '—' }}</div>
                                </div>
                            </div>
                            @if($operator->unit_code)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">رمز الوحدة</label>
                                        <div class="info-value">{{ $operator->unit_code }}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">المحافظة</label>
                                    <div class="info-value">{{ $operator->getGovernorateLabel() ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">المدينة</label>
                                    <div class="info-value">{{ $operator->getCityName() ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="info-item">
                                    <label class="info-label">العنوان التفصيلي</label>
                                    <div class="info-value">{{ $operator->detailed_address ?? ($operator->address ?? '—') }}</div>
                                </div>
                            </div>
                            @if($operator->latitude && $operator->longitude)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">خط العرض</label>
                                        <div class="info-value">{{ $operator->latitude }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">خط الطول</label>
                                        <div class="info-value">{{ $operator->longitude }}</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Owner Information --}}
                            <div class="col-12 mt-3">
                                <h6 class="fw-bold text-muted mb-3">معلومات المالك</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">صاحب المشغل</label>
                                    <div class="info-value">
                                        @if($operator->owner)
                                            {{ $operator->owner->name }}
                                            <small class="text-muted ms-2">({{ $operator->owner->username }})</small>
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($operator->owner_name)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">اسم المالك</label>
                                        <div class="info-value">{{ $operator->owner_name }}</div>
                                    </div>
                                </div>
                            @endif
                            @if($operator->owner_id_number)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">رقم هوية المالك</label>
                                        <div class="info-value">{{ $operator->owner_id_number }}</div>
                                    </div>
                                </div>
                            @endif
                            @if($operator->operator_id_number)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">رقم هوية المشغل</label>
                                        <div class="info-value">{{ $operator->operator_id_number }}</div>
                                    </div>
                                </div>
                            @endif
                            @if($operator->operation_entity)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">جهة التشغيل</label>
                                        <div class="info-value">
                                            {{ $operator->operation_entity === 'same_owner' ? 'نفس المالك' : 'طرف آخر' }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Technical Information --}}
                            @if($operator->total_capacity || $operator->generators_count || $operator->synchronization_available !== null)
                                <div class="col-12 mt-3">
                                    <h6 class="fw-bold text-muted mb-3">المعلومات الفنية</h6>
                                </div>
                                @if($operator->total_capacity)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">القدرة الإجمالية (KVA)</label>
                                            <div class="info-value">{{ number_format($operator->total_capacity, 2) }}</div>
                                        </div>
                                    </div>
                                @endif
                                @if($operator->generators_count !== null)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">عدد المولدات المسموح</label>
                                            <div class="info-value">{{ $operator->generators_count }}</div>
                                        </div>
                                    </div>
                                @endif
                                @if($operator->synchronization_available !== null)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">مزامنة المولدات</label>
                                            <div class="info-value">
                                                {{ $operator->synchronization_available ? 'متوفرة' : 'غير متوفرة' }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($operator->max_synchronization_capacity)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">قدرة المزامنة القصوى (KVA)</label>
                                            <div class="info-value">{{ number_format($operator->max_synchronization_capacity, 2) }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            {{-- Beneficiaries Information --}}
                            @if($operator->beneficiaries_count || $operator->beneficiaries_description)
                                <div class="col-12 mt-3">
                                    <h6 class="fw-bold text-muted mb-3">معلومات المستفيدين</h6>
                                </div>
                                @if($operator->beneficiaries_count)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">عدد المستفيدين</label>
                                            <div class="info-value">{{ $operator->beneficiaries_count }}</div>
                                        </div>
                                    </div>
                                @endif
                                @if($operator->beneficiaries_description)
                                    <div class="col-md-12">
                                        <div class="info-item">
                                            <label class="info-label">وصف المستفيدين</label>
                                            <div class="info-value">{{ $operator->beneficiaries_description }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @if($operator->environmental_compliance_status)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">حالة الامتثال البيئي</label>
                                        <div class="info-value">{{ $operator->environmental_compliance_status }}</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Dates --}}
                            <div class="col-12 mt-3">
                                <h6 class="fw-bold text-muted mb-3">التواريخ</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">تاريخ الإنشاء</label>
                                    <div class="info-value">{{ $operator->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">آخر تحديث</label>
                                    <div class="info-value">{{ $operator->updated_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar: Generation Units List --}}
            <div class="col-12 col-lg-4">
                <div class="general-card">
                    <div class="general-card-header">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <h5 class="general-title">
                                    <i class="bi bi-lightning-charge me-2"></i>
                                    وحدات التوليد
                                </h5>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($operator->generation_units_count > 0)
                                    <span class="badge bg-primary">{{ $operator->generation_units_count }}</span>
                                @endif
                                @can('create', App\Models\GenerationUnit::class)
                                    <a href="{{ route('admin.generation-units.create', ['operator_id' => $operator->id]) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        إضافة وحدة توليد
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($operator->generationUnits->count() > 0)
                            <div class="generation-units-list">
                                @foreach($operator->generationUnits as $unit)
                                    <div class="generation-unit-item mb-3 pb-3 border-bottom">
                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold d-flex align-items-center gap-2">
                                                    <i class="bi bi-building"></i>
                                                    {{ $unit->name }}
                                                </div>
                                                @if($unit->unit_code)
                                                    <small class="text-muted">
                                                        <code>{{ $unit->unit_code }}</code>
                                                    </small>
                                                @endif
                                                <div class="mt-2 d-flex gap-2 align-items-center">
                                                    <span class="badge bg-info">
                                                        {{ $unit->generators()->count() }} / {{ $unit->generators_count }} مولد
                                                    </span>
                                                    @if($unit->statusDetail)
                                                        <span class="badge {{ $unit->statusDetail->code === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }}">
                                                            {{ $unit->statusDetail->label }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">غير محدد</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- عرض المولدات داخل وحدة التوليد --}}
                                        @if($unit->generators->count() > 0)
                                            <div class="generators-list ms-3 mt-2">
                                                @foreach($unit->generators as $generator)
                                                    <div class="generator-item-small d-flex align-items-center justify-content-between py-1">
                                                        <div class="flex-grow-1">
                                                            <div class="small fw-semibold">{{ $generator->name }}</div>
                                                            @if($generator->generator_number)
                                                                <small class="text-muted">{{ $generator->generator_number }}</small>
                                                            @endif
                                                        </div>
                                                        <a href="{{ route('admin.generators.show', $generator) }}" class="btn btn-xs btn-outline-primary" title="عرض التفاصيل">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </div>
                                                @endforeach
                                                @if($unit->generators()->count() > $unit->generators->count())
                                                    <div class="text-center mt-2">
                                                        <a href="{{ route('admin.generators.index', ['generation_unit_id' => $unit->id]) }}" class="btn btn-sm btn-outline-primary">
                                                            عرض جميع المولدات ({{ $unit->generators()->count() }})
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-muted small ms-3 mt-2">
                                                <i class="bi bi-info-circle"></i>
                                                لا توجد مولدات في هذه الوحدة
                                                @can('create', App\Models\Generator::class)
                                                    <a href="{{ route('admin.generators.create', ['generation_unit_id' => $unit->id]) }}" class="btn btn-xs btn-primary ms-2">
                                                        إضافة مولد
                                                    </a>
                                                @endcan
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-lightning-charge fs-1 d-block mb-2"></i>
                                <p>لا توجد وحدات توليد</p>
                                <p class="small">ابدأ بإضافة وحدة توليد جديدة من الزر أعلاه</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Profile Completion Status --}}
                <div class="general-card mt-3">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-check-circle me-2"></i>
                                حالة الملف
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($operator->isProfileComplete())
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle me-2"></i>
                                الملف مكتمل
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                الملف غير مكتمل
                            </div>
                            @php
                                $missing = $operator->getMissingFields();
                            @endphp
                            @if(count($missing) > 0)
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-1">الحقول الناقصة:</small>
                                    <ul class="mb-0 ps-3 small">
                                        @foreach($missing as $field)
                                            <li>{{ $field }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function() {
    function notify(type, msg, title) {
        if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
            window.adminNotifications[type](msg, title);
            return;
        }
        alert(msg);
    }

    const approvalForm = document.getElementById('approvalForm');
    const approvalBtn = document.getElementById('approvalBtn');

    if (approvalForm && approvalBtn) {
        approvalForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const originalText = approvalBtn.innerHTML;
            const isCurrentlyApproved = approvalBtn.classList.contains('btn-warning');
            const newText = isCurrentlyApproved ? 'جاري إلغاء الاعتماد...' : 'جاري الاعتماد...';

            approvalBtn.disabled = true;
            approvalBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                ${newText}
            `;

            try {
                const formData = new FormData(approvalForm);
                const response = await fetch(approvalForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    notify('success', data.message || 'تمت العملية بنجاح');

                    // Update button state
                    if (data.operator && data.operator.is_approved !== undefined) {
                        if (data.operator.is_approved) {
                            approvalBtn.classList.remove('btn-success');
                            approvalBtn.classList.add('btn-warning');
                            approvalBtn.innerHTML = '<i class="bi bi-x-circle me-2"></i>إلغاء الاعتماد';
                        } else {
                            approvalBtn.classList.remove('btn-warning');
                            approvalBtn.classList.add('btn-success');
                            approvalBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>اعتماد المشغل';
                        }
                    } else {
                        // Refresh page to update state
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    notify('error', data.message || 'حدث خطأ أثناء تنفيذ العملية');
                    approvalBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                notify('error', 'حدث خطأ أثناء الاتصال بالخادم');
                approvalBtn.innerHTML = originalText;
            } finally {
                approvalBtn.disabled = false;
            }
        });
    }
})();
</script>
@endpush

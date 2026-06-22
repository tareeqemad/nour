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

    a.stat-card {
        color: inherit;
        cursor: pointer;
    }

    a.stat-card:hover {
        color: inherit;
    }

    .stat-card-hint {
        font-size: 0.75rem;
        color: #3b82f6;
        margin-top: 0.25rem;
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
    @php
        $operationLogsParams = ['operator_id' => $operator->id];
        if ($operator->generationUnits->count() === 1) {
            $operationLogsParams['generation_unit_id'] = $operator->generationUnits->first()->id;
        }
    @endphp
    <div class="general-page">
        <div class="row g-3">
            {{-- Header Card with Summary --}}
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form :title="$operator->name" icon="bi-person-badge" :backRoute="route('admin.operators.index')">
                        @can('approve', $operator)
                            <form action="{{ route('admin.operators.toggle-approval', $operator) }}" method="POST" class="d-inline" id="approvalForm">
                                @csrf
                                <button type="submit" class="btn {{ $operator->is_approved ? 'btn-warning' : 'btn-success' }}" id="approvalBtn">
                                    <i class="bi bi-{{ $operator->is_approved ? 'x-circle' : 'check-circle' }} me-2"></i>
                                    {{ $operator->is_approved ? 'إلغاء الاعتماد' : 'اعتماد المشغل' }}
                                </button>
                            </form>

                            {{-- حالة الطلب / الترخيص — تغيير الحالة يرسل SMS للمشغّل --}}
                            <form action="{{ route('admin.operators.license-status', $operator) }}" method="POST"
                                  class="d-inline-flex align-items-center gap-1 flex-wrap"
                                  onsubmit="return confirm('سيتم تغيير حالة الطلب وإرسال رسالة SMS للمشغّل. متابعة؟');">
                                @csrf
                                <select name="license_status" class="form-select" style="width:auto;" title="حالة الطلب">
                                    @foreach(\App\Models\Operator::licenseStatuses() as $val => $label)
                                        <option value="{{ $val }}" {{ $operator->license_status === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="note" class="form-control" style="width:210px;" maxlength="500"
                                       placeholder="سبب/ملاحظة للمشغّل (اختياري)" title="تُضاف إلى رسالة SMS للمشغّل">
                                <button type="submit" class="btn btn-outline-primary" title="تحديث الحالة وإرسال SMS">
                                    <i class="bi bi-send-check me-1"></i>تحديث الحالة
                                </button>
                            </form>

                            {{-- رسالة توجيه للمشغّل الجديد (يدوي، قابلة لإعادة الإرسال) --}}
                            <form action="{{ route('admin.operators.onboarding-sms', $operator) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('سيتم إرسال رسالة توجيه (SMS) للمشغّل لتعبئة بياناته الفنية. متابعة؟');">
                                @csrf
                                <button type="submit" class="btn btn-outline-info" title="إرسال رسالة توجيه للمشغّل لتعبئة بياناته الفنية">
                                    <i class="bi bi-chat-dots me-1"></i>رسالة توجيه
                                </button>
                            </form>

                            {{-- إرسال بيانات الدخول (يولّد كلمة مرور جديدة ويرسلها عبر SMS) --}}
                            <form action="{{ route('admin.operators.send-credentials', $operator) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('سيتم إنشاء كلمة مرور جديدة للمشغّل وإرسال بيانات الدخول (اسم المستخدم + كلمة المرور + الرابط) عبر SMS. كلمة المرور الحالية ستصبح غير صالحة. متابعة؟');">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary" title="إنشاء كلمة مرور جديدة وإرسال بيانات الدخول عبر SMS">
                                    <i class="bi bi-key me-1"></i>إرسال بيانات الدخول
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
                    </x-admin.card-header-form>

                    {{-- Statistics Cards --}}
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('admin.generation-units.index', ['operator_id' => $operator->id]) }}" target="_blank" rel="noopener" class="stat-card" title="عرض وحدات التوليد في تبويب جديد">
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">وحدات التوليد</div>
                                        <div class="stat-value">{{ $operator->generation_units_count ?? 0 }}</div>
                                        <div class="stat-card-hint">اضغط للعرض</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('admin.generators.index', ['operator_id' => $operator->id]) }}" target="_blank" rel="noopener" class="stat-card" title="عرض المولدات في تبويب جديد">
                                    <div class="stat-icon bg-info">
                                        <i class="bi bi-lightning-charge"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">عدد المولدات</div>
                                        <div class="stat-value">{{ $operator->generators_count ?? 0 }}</div>
                                        <div class="stat-card-hint">اضغط للعرض</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('admin.operators.employees', $operator) }}" target="_blank" rel="noopener" class="stat-card" title="عرض الموظفين في تبويب جديد">
                                    <div class="stat-icon bg-success">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">عدد الموظفين</div>
                                        <div class="stat-value">{{ $operator->users_count ?? 0 }}</div>
                                        <div class="stat-card-hint">اضغط للعرض</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('admin.operation-logs.index', $operationLogsParams) }}" target="_blank" rel="noopener" class="stat-card" title="عرض سجلات التشغيل في تبويب جديد">
                                    <div class="stat-icon bg-warning">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">سجلات التشغيل</div>
                                        <div class="stat-value">{{ $operator->operation_logs_count ?? 0 }}</div>
                                        <div class="stat-card-hint">اضغط للعرض</div>
                                    </div>
                                </a>
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
            </x-admin.card>

            {{-- Main Information --}}
            <div class="col-12 col-lg-8">
                <x-admin.card>
                    <x-admin.card-header-form title="معلومات المشغل" icon="bi-info-circle">
                    </x-admin.card-header-form>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">اسم المشغل</label>
                                    <div class="info-value">{{ $operator->name ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">رقم المشغل</label>
                                    <div class="info-value">{{ $operator->unit_number ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">رقم هوية المشغل</label>
                                    <div class="info-value">{{ $operator->operator_id_number ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">اسم المالك</label>
                                    <div class="info-value">{{ $operator->owner_name ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">رقم هوية المالك</label>
                                    <div class="info-value">{{ $operator->owner_id_number ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">رقم الهاتف</label>
                                    <div class="info-value">{{ $operator->phone ?? '—' }}</div>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <h6 class="fw-bold text-muted mb-3">حساب المالك</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">صاحب الحساب</label>
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
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">البريد الإلكتروني</label>
                                    <div class="info-value">{{ $operator->owner?->email ?? '—' }}</div>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <h6 class="fw-bold text-muted mb-3">الحالة والاعتماد</h6>
                            </div>
                            <div class="col-md-4">
                                <div class="info-item">
                                    <label class="info-label">الحالة</label>
                                    <div class="info-value">{{ $operator->status === 'active' ? 'فعال' : 'غير فعال' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-item">
                                    <label class="info-label">الاعتماد</label>
                                    <div class="info-value">{{ $operator->is_approved ? 'معتمد' : 'غير معتمد' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-item">
                                    <label class="info-label">اكتمال الملف</label>
                                    <div class="info-value">{{ $operator->isProfileComplete() ? 'مكتمل' : 'غير مكتمل' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-item">
                                    <label class="info-label">حالة الطلب</label>
                                    <div class="info-value">
                                        <span class="badge bg-{{ $operator->license_status_badge }}">{{ $operator->license_status_label }}</span>
                                    </div>
                                </div>
                            </div>
                            @if($operator->territory_radius_km)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">نصف قطر المنطقة (كم)</label>
                                        <div class="info-value">{{ number_format($operator->territory_radius_km, 2) }}</div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12 mt-2">
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

                            @if($operator->generationUnits->count() > 0)
                                <div class="col-12 mt-3">
                                    <h6 class="fw-bold text-muted mb-3">تفاصيل وحدات التوليد</h6>
                                    <p class="text-muted small mb-3">
                                        البيانات التفصيلية (الموقع، القدرات، المستفيدين...) مُخزّنة ضمن كل وحدة توليد.
                                    </p>
                                    @foreach($operator->generationUnits as $generationUnit)
                                        @include('admin.operators.partials.generation-unit-info', ['generationUnit' => $generationUnit])
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </x-admin.card>
            </div>

            {{-- Sidebar: Generation Units List --}}
            <div class="col-12 col-lg-4">
                <x-admin.card class="mb-3">
                    <x-admin.card-header-form title="مرفقات المشغل" icon="bi-paperclip">
                        @if($operator->attachments_count > 0)
                            <span class="badge bg-primary">{{ $operator->attachments_count }}</span>
                        @endif
                    </x-admin.card-header-form>
                    <div class="card-body">
                        @if($operator->attachments->isEmpty())
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                لا توجد مرفقات لهذا المشغل.
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($operator->attachments as $attachment)
                                    <div class="list-group-item px-0 d-flex align-items-center justify-content-between gap-3">
                                        <div class="min-w-0">
                                            <div class="fw-semibold">{{ $attachment->name }}</div>
                                            <div class="small text-muted">
                                                {{ $attachment->original_filename ?? '—' }}
                                                <span class="mx-1">•</span>
                                                {{ $attachment->size_for_humans }}
                                            </div>
                                            @if($attachment->uploader)
                                                <div class="small text-muted">أضيف بواسطة: {{ $attachment->uploader->name }}</div>
                                            @endif
                                        </div>
                                        <a href="{{ route('admin.operators.attachments.download', $attachment) }}" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                            <i class="bi bi-download me-1"></i>
                                            تحميل
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </x-admin.card>

                <x-admin.card>
                    <x-admin.card-header-form title="وحدات التوليد" icon="bi-lightning-charge">
                        @if($operator->generation_units_count > 0)
                            <span class="badge bg-primary">{{ $operator->generation_units_count }}</span>
                        @endif
                        @can('create', App\Models\GenerationUnit::class)
                            <a href="{{ route('admin.generation-units.create', ['operator_id' => $operator->id]) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>
                                إضافة وحدة توليد
                            </a>
                        @endcan
                    </x-admin.card-header-form>
                    <div class="card-body">
                        @if($operator->generationUnits->count() > 0)
                            <div class="generation-units-list">
                                @foreach($operator->generationUnits as $unit)
                                    <div class="generation-unit-item mb-3 pb-3 border-bottom">
                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold d-flex align-items-center gap-2">
                                                    <i class="bi bi-building"></i>
                                                    @can('view', $unit)
                                                        <a href="{{ route('admin.generation-units.show', $unit) }}" target="_blank" rel="noopener" class="text-decoration-none">
                                                            {{ $unit->name }}
                                                        </a>
                                                    @else
                                                        {{ $unit->name }}
                                                    @endcan
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
                                                        <a href="{{ route('admin.generators.show', $generator) }}" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary" title="عرض التفاصيل">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </div>
                                                @endforeach
                                                @if($unit->generators()->count() > $unit->generators->count())
                                                    <div class="text-center mt-2">
                                                        <a href="{{ route('admin.generators.index', ['generation_unit_id' => $unit->id, 'operator_id' => $operator->id]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
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
                </x-admin.card>

                {{-- Profile Completion Status --}}
                <x-admin.card class="mt-3">
                    <x-admin.card-header-form title="حالة الملف" icon="bi-check-circle">
                    </x-admin.card-header-form>
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
                </x-admin.card>
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

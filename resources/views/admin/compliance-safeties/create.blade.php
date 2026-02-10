@extends('layouts.admin')

@section('title', 'إضافة سجل امتثال وسلامة')

@php
    $breadcrumbTitle = 'إضافة سجل امتثال وسلامة';
    $breadcrumbParent = 'سجلات الامتثال والسلامة';
    $breadcrumbParentUrl = route('admin.compliance-safeties.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/compliance-safeties.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
@endpush

@section('content')
    <div class="compliance-safeties-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h1 class="log-title mb-0">
                                <i class="bi bi-shield-check me-2"></i>
                                إضافة سجل امتثال وسلامة جديد
                            </h1>
                            <div class="log-subtitle text-muted mt-1">قم بإدخال بيانات الامتثال والسلامة</div>
                        </div>
                        <a href="{{ route('admin.compliance-safeties.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('admin.compliance-safeties.store') }}" method="POST" id="complianceSafetyForm">
                            @csrf

                            {{-- Section: Basic info (light heading, no nested card) --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">المعلومات الأساسية</h6>
                            <div class="row g-3 mb-4">
                                @include('admin.partials.cascading-selects', [
                                    'operators' => $operators ?? collect(),
                                    'showGenerator' => true,
                                    'showGenerationUnit' => true,
                                    'generationUnits' => $generationUnits ?? collect(),
                                    'generators' => $generators ?? collect(),
                                    'colClass' => 'col-md-6 col-lg-3',
                                    'operatorRequired' => true,
                                    'generationUnitRequired' => true,
                                    'generatorRequired' => true,
                                    'selectedOperatorId' => old('operator_id', auth()->user()->isCompanyOwner() ? auth()->user()->ownedOperators()->first()?->id : null),
                                    'useSelect2' => true,
                                ])
                                <div class="col-md-6 col-lg-3">
                                    <label class="form-label fw-semibold">حالة شهادة السلامة <span class="text-danger">*</span></label>
                                    <select name="safety_certificate_status_id" class="form-select select2 @error('safety_certificate_status_id') is-invalid @enderror" required>
                                        <option value="">اختر الحالة</option>
                                        @foreach($constants['safety_certificate_status'] ?? [] as $status)
                                            <option value="{{ $status->id }}" {{ old('safety_certificate_status_id') == $status->id ? 'selected' : '' }}>
                                                {{ $status->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('safety_certificate_status_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Section: Inspection details (light heading) --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">تفاصيل الزيارة التفقدية</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ آخر زيارة</label>
                                    <input type="date" name="last_inspection_date"
                                           class="form-control @error('last_inspection_date') is-invalid @enderror"
                                           value="{{ old('last_inspection_date') }}">
                                    @error('last_inspection_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">الجهة المنفذة</label>
                                    <input type="text" name="inspection_authority"
                                           class="form-control @error('inspection_authority') is-invalid @enderror"
                                           value="{{ old('inspection_authority') }}" placeholder="اسم الجهة المنفذة">
                                    @error('inspection_authority')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">نتيجة الزيارة</label>
                                    <textarea name="inspection_result"
                                              class="form-control @error('inspection_result') is-invalid @enderror"
                                              rows="3" placeholder="تفاصيل نتيجة الزيارة">{{ old('inspection_result') }}</textarea>
                                    @error('inspection_result')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">المخالفات المسجلة</label>
                                    <textarea name="violations"
                                              class="form-control @error('violations') is-invalid @enderror"
                                              rows="3" placeholder="تفاصيل المخالفات إن وجدت">{{ old('violations') }}</textarea>
                                    @error('violations')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2">
                                <a href="{{ route('admin.compliance-safeties.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-check-lg me-2"></i>
                                    حفظ البيانات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
<script src="{{ asset('assets/admin/libs/select2/i18n/ar.js') }}"></script>
@include('admin.partials.cascading-selects-scripts', [
    'cascadingRoutes' => [
        'generationUnits' => url('/admin/operators') . '/__OPERATOR__/generation-units-for-compliance-safeties',
        'generators' => url('/admin/generation-units') . '/__UNIT__/generators-for-compliance-safeties',
    ],
    'initialOperatorId' => old('operator_id', auth()->user()->isCompanyOwner() ? auth()->user()->ownedOperators()->first()?->id : null),
    'initialGenerationUnitId' => old('generation_unit_id'),
    'initialGeneratorId' => old('generator_id'),
])
<script>
$(document).ready(function() {
    $('select[name="safety_certificate_status_id"]').select2({ dir: document.documentElement.dir || 'rtl', width: '100%' });
    AdminCRUD.submitForm({
        form: '#complianceSafetyForm',
        method: 'POST',
        onSuccess: function() {
            setTimeout(function() { window.location.href = '{{ route('admin.compliance-safeties.index') }}'; }, 500);
        }
    });
});
</script>
@endpush

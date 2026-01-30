@extends('layouts.admin')

@section('title', 'إضافة سجل امتثال وسلامة')

@php
    $breadcrumbTitle = 'إضافة سجل امتثال وسلامة';
    $breadcrumbParent = 'سجلات الامتثال والسلامة';
    $breadcrumbParentUrl = route('admin.compliance-safeties.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/compliance-safeties.css') }}">
@endpush

@section('content')
    <div class="compliance-safeties-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
                            <div>
                                <div class="log-title">
                                    <i class="bi bi-shield-check me-2"></i>
                                    إضافة سجل امتثال وسلامة جديد
                                </div>
                                <div class="log-subtitle">
                                    قم بإدخال بيانات الامتثال والسلامة بشكل كامل
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('admin.compliance-safeties.store') }}" method="POST" id="complianceSafetyForm">
                            @csrf

                            <!-- Basic Information Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    المعلومات الأساسية
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-building text-info me-1"></i>
                                            المشغل <span class="text-danger">*</span>
                                        </label>
                                        <select name="operator_id" class="form-select @error('operator_id') is-invalid @enderror"
                                            @if(auth()->user()->isCompanyOwner()) disabled @endif>
                                            <option value="">اختر المشغل</option>
                                            @foreach($operators as $operator)
                                                <option value="{{ $operator->id }}" 
                                                        {{ old('operator_id', auth()->user()->isCompanyOwner() ? auth()->user()->ownedOperators()->first()->id : '') == $operator->id ? 'selected' : '' }}>
                                                    {{ $operator->name }}
                                                    @if($operator->unit_number)
                                                        - {{ $operator->unit_number }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @if(auth()->user()->isCompanyOwner())
                                            <input type="hidden" name="operator_id" value="{{ auth()->user()->ownedOperators()->first()->id }}">
                                        @endif
                                        @error('operator_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-shield-check text-success me-1"></i>
                                            حالة شهادة السلامة <span class="text-danger">*</span>
                                        </label>
                                        <select name="safety_certificate_status_id" class="form-select @error('safety_certificate_status_id') is-invalid @enderror">
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
                            </div>

                            <hr class="my-4">

                            <!-- Inspection Details Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-clipboard-check text-warning me-2"></i>
                                    تفاصيل الزيارة التفقدية
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar3 text-primary me-1"></i>
                                            تاريخ آخر زيارة تفقدية
                                        </label>
                                        <input type="date" name="last_inspection_date" 
                                               class="form-control @error('last_inspection_date') is-invalid @enderror" 
                                               value="{{ old('last_inspection_date') }}">
                                        @error('last_inspection_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-building-check text-info me-1"></i>
                                            الجهة المنفذة
                                        </label>
                                        <input type="text" name="inspection_authority" 
                                               class="form-control @error('inspection_authority') is-invalid @enderror" 
                                               value="{{ old('inspection_authority') }}" 
                                               placeholder="اسم الجهة المنفذة للزيارة">
                                        @error('inspection_authority')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-file-text text-secondary me-1"></i>
                                            نتيجة الزيارة
                                        </label>
                                        <textarea name="inspection_result" 
                                                  class="form-control @error('inspection_result') is-invalid @enderror" 
                                                  rows="4"
                                                  placeholder="تفاصيل نتيجة الزيارة التفقدية">{{ old('inspection_result') }}</textarea>
                                        @error('inspection_result')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                                            المخالفات المسجلة
                                        </label>
                                        <textarea name="violations" 
                                                  class="form-control @error('violations') is-invalid @enderror" 
                                                  rows="4"
                                                  placeholder="تفاصيل المخالفات المسجلة إن وجدت">{{ old('violations') }}</textarea>
                                        @error('violations')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center">
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
<script>
    $(document).ready(function() {
        AdminCRUD.submitForm({
            form: '#complianceSafetyForm',
            method: 'POST',
            onSuccess: function(response) {
                setTimeout(function() {
                    window.location.href = '{{ route('admin.compliance-safeties.index') }}';
                }, 500);
            }
        });
    });
</script>
@endpush

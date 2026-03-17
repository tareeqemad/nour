@extends('layouts.admin')

@section('title', 'إضافة نسبة خصم جديدة')

@php
    $breadcrumbTitle = 'إضافة نسبة خصم جديدة';
    $breadcrumbParent = 'نسب خصم الموظفين';
    $breadcrumbParentUrl = route('admin.employee-discount-rates.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/fuel-efficiencies.css') }}">
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
                            <div>
                                <div class="general-title">
                                    <i class="bi bi-percent me-2"></i>
                                    إضافة نسبة خصم جديدة
                                </div>
                                <div class="general-subtitle">
                                    قم بإدخال بيانات نسبة خصم موظفي الشركة الجديدة
                                </div>
                            </div>
                            <a href="{{ route('admin.employee-discount-rates.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-2"></i>
                                العودة للقائمة
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('admin.employee-discount-rates.store') }}" method="POST" id="discountRateForm">
                            @csrf

                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    معلومات نسبة الخصم
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            تاريخ بداية التطبيق <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="start_date"
                                               class="form-control @error('start_date') is-invalid @enderror"
                                               value="{{ old('start_date', date('Y-m-d')) }}">
                                        @error('start_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            تاريخ نهاية التطبيق (اختياري)
                                        </label>
                                        <input type="date" name="end_date"
                                               class="form-control @error('end_date') is-invalid @enderror"
                                               value="{{ old('end_date') }}">
                                        <small class="text-muted">اتركه فارغاً إذا كانت النسبة لا تزال سارية</small>
                                        @error('end_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            نسبة الخصم (%) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="0.01" name="discount_rate"
                                               class="form-control @error('discount_rate') is-invalid @enderror"
                                               value="{{ old('discount_rate') }}"
                                               placeholder="0.00" min="0" max="100">
                                        @error('discount_rate')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            الحالة
                                        </label>
                                        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>نشط</option>
                                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                        </select>
                                        @error('is_active')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">
                                            ملاحظات (اختياري)
                                        </label>
                                        <textarea name="notes"
                                                  class="form-control @error('notes') is-invalid @enderror"
                                                  rows="3"
                                                  placeholder="مثال: تعديل نسبة خصم الموظفين - يناير 2026">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.employee-discount-rates.index') }}" class="btn btn-outline-secondary">
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>
                                    حفظ
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

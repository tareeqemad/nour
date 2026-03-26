@extends('layouts.admin')

@section('title', 'تعديل نسبة خصم الموظفين')

@php
    $breadcrumbTitle = 'تعديل نسبة خصم الموظفين';
    $breadcrumbParent = 'نسب خصم الموظفين';
    $breadcrumbParentUrl = route('admin.employee-discount-rates.index');
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form title="تعديل نسبة خصم الموظفين" icon="bi-pencil" :backRoute="route('admin.employee-discount-rates.index')" />

                    <div class="card-body p-4">
                        <form action="{{ route('admin.employee-discount-rates.update', $discountRate) }}" method="POST">
                            @csrf
                            @method('PUT')

                            {{-- Section: فترة التطبيق --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">فترة التطبيق</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        تاريخ بداية التطبيق <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="start_date"
                                           class="form-control @error('start_date') is-invalid @enderror"
                                           value="{{ old('start_date', $discountRate->start_date->format('Y-m-d')) }}">
                                    @error('start_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        تاريخ نهاية التطبيق
                                    </label>
                                    <input type="date" name="end_date"
                                           class="form-control @error('end_date') is-invalid @enderror"
                                           value="{{ old('end_date', $discountRate->end_date?->format('Y-m-d')) }}">
                                    <small class="form-text text-muted">اتركه فارغاً إذا كانت النسبة لا تزال سارية</small>
                                    @error('end_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Section: نسبة الخصم والحالة --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">نسبة الخصم والحالة</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        نسبة الخصم (%) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" name="discount_rate"
                                           class="form-control @error('discount_rate') is-invalid @enderror"
                                           value="{{ old('discount_rate', $discountRate->discount_rate) }}"
                                           placeholder="0.00" min="0" max="100">
                                    @error('discount_rate')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">الحالة</label>
                                    <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                        <option value="1" {{ old('is_active', $discountRate->is_active) == '1' ? 'selected' : '' }}>نشط</option>
                                        <option value="0" {{ old('is_active', $discountRate->is_active) == '0' ? 'selected' : '' }}>غير نشط</option>
                                    </select>
                                    @error('is_active')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">ملاحظات</label>
                                    <textarea name="notes"
                                              class="form-control @error('notes') is-invalid @enderror"
                                              rows="3"
                                              placeholder="مثال: تعديل نسبة خصم الموظفين - يناير 2026">{{ old('notes', $discountRate->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                                <a href="{{ route('admin.employee-discount-rates.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-1"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>
                                    حفظ التعديلات
                                </button>
                            </div>
                        </form>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

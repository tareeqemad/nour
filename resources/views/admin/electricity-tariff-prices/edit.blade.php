@extends('layouts.admin')

@section('title', 'تعديل سعر التعرفة')

@php
    $breadcrumbTitle = 'تعديل سعر التعرفة';
    $breadcrumbParent = 'أسعار التعرفة الكهربائية';
    $breadcrumbParentUrl = route('admin.electricity-tariff-prices.index');
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form title="تعديل سعر التعرفة" icon="bi-pencil" :backRoute="route('admin.electricity-tariff-prices.index')" />

                    <div class="card-body p-4">
                        <form action="{{ route('admin.electricity-tariff-prices.update', $tariffPrice) }}" method="POST">
                            @csrf
                            @method('PUT')

                            {{-- Section: فترة التطبيق --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">فترة التطبيق</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        تاريخ بداية تطبيق السعر <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="start_date"
                                           class="form-control @error('start_date') is-invalid @enderror"
                                           value="{{ old('start_date', $tariffPrice->start_date->format('Y-m-d')) }}">
                                    @error('start_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        تاريخ نهاية تطبيق السعر
                                    </label>
                                    <input type="date" name="end_date"
                                           class="form-control @error('end_date') is-invalid @enderror"
                                           value="{{ old('end_date', $tariffPrice->end_date?->format('Y-m-d')) }}">
                                    <small class="form-text text-muted">اتركه فارغاً إذا كان السعر لا يزال ساري</small>
                                    @error('end_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Section: السعر والحالة --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">السعر والحالة</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        سعر التعرفة (₪/kWh) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" name="price_per_kwh"
                                           class="form-control @error('price_per_kwh') is-invalid @enderror"
                                           value="{{ old('price_per_kwh', $tariffPrice->price_per_kwh) }}"
                                           placeholder="0.00">
                                    @error('price_per_kwh')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">الحالة</label>
                                    <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                        <option value="1" {{ old('is_active', $tariffPrice->is_active) == '1' ? 'selected' : '' }}>نشط</option>
                                        <option value="0" {{ old('is_active', $tariffPrice->is_active) == '0' ? 'selected' : '' }}>غير نشط</option>
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
                                              placeholder="مثال: تغيير السعر الشهري - ديسمبر 2025">{{ old('notes', $tariffPrice->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                                <a href="{{ route('admin.electricity-tariff-prices.index') }}" class="btn btn-outline-secondary">
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

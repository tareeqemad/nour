@extends('layouts.admin')

@section('title', 'تعديل سعر التعرفة')

@php
    $breadcrumbTitle = 'تعديل سعر التعرفة';
    $breadcrumbParent = $operator->name;
    $breadcrumbParentUrl = route('admin.operators.show', $operator);
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/fuel-efficiencies.css') }}">
@endpush

@section('content')
    <div class="fuel-efficiencies-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
                            <div>
                                <div class="log-title">
                                    <i class="bi bi-currency-exchange me-2"></i>
                                    تعديل سعر التعرفة - {{ $operator->name }}
                                </div>
                                <div class="log-subtitle">
                                    قم بتعديل بيانات سعر التعرفة الكهربائية
                                </div>
                            </div>
                            <a href="{{ route('admin.operators.tariff-prices.index', $operator) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-2"></i>
                                العودة للقائمة
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('admin.operators.tariff-prices.update', [$operator, $tariffPrice]) }}" method="POST" id="tariffPriceForm">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    معلومات السعر
                                </h6>
                                <div class="row g-3">
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
                                            تاريخ نهاية تطبيق السعر (اختياري)
                                        </label>
                                        <input type="date" name="end_date" 
                                               class="form-control @error('end_date') is-invalid @enderror" 
                                               value="{{ old('end_date', $tariffPrice->end_date?->format('Y-m-d')) }}">
                                        <small class="text-muted">اتركه فارغاً إذا كان السعر لا يزال ساري</small>
                                        @error('end_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            سعر التعرفة (₪/kWh) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="0.0001" name="price_per_kwh" 
                                               class="form-control @error('price_per_kwh') is-invalid @enderror" 
                                               value="{{ old('price_per_kwh', $tariffPrice->price_per_kwh) }}" 
                                               min="0" 
                                               max="500"
                                               placeholder="0.0000">
                                        <small class="text-muted">مثال: في غزة قد يصل السعر إلى 30 شيكل أو أكثر</small>
                                        @error('price_per_kwh')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            الحالة
                                        </label>
                                        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                            <option value="1" {{ old('is_active', $tariffPrice->is_active) == '1' ? 'selected' : '' }}>نشط</option>
                                            <option value="0" {{ old('is_active', $tariffPrice->is_active) == '0' ? 'selected' : '' }}>غير نشط</option>
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
                                                  placeholder="مثال: تغيير السعر الشهري - ديسمبر 2025">{{ old('notes', $tariffPrice->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.operators.tariff-prices.index', $operator) }}" class="btn btn-outline-secondary">
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


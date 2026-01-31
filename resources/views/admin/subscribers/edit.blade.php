@extends('layouts.admin')

@section('title', 'تعديل بيانات المشترك')

@php
    $breadcrumbTitle = 'تعديل بيانات المشترك';
    $breadcrumbParent = 'إدارة بيانات المشتركين';
    $breadcrumbParentUrl = route('admin.subscribers.index');
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-pencil me-2"></i>
                                تعديل بيانات المشترك
                            </h5>
                            <div class="general-subtitle">
                                تعديل بيانات المشترك: {{ $subscriber->subscriber_name }}
                            </div>
                        </div>
                        <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.subscribers.update', $subscriber) }}" method="POST" id="subscriberForm">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                {{-- البيانات الأساسية --}}
                                <div class="col-12">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        البيانات الأساسية
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">رقم الاشتراك 
                                        @if(auth()->user()->isSuperAdmin())
                                            <span class="text-danger">*</span>
                                        @else
                                            <span class="badge bg-secondary ms-1">غير قابل للتعديل</span>
                                        @endif
                                    </label>
                                    <input type="text" name="subscription_number" class="form-control @error('subscription_number') is-invalid @enderror" 
                                           value="{{ old('subscription_number', $subscriber->subscription_number) }}" 
                                           @if(!auth()->user()->isSuperAdmin()) readonly style="background-color: #f8f9fa; cursor: not-allowed;" @else required @endif>
                                    @if(!auth()->user()->isSuperAdmin())
                                        <small class="form-text text-muted">لا يمكنك تعديل رقم الاشتراك</small>
                                    @endif
                                    @error('subscription_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">رقم هوية المشترك <span class="text-danger">*</span></label>
                                    <input type="text" name="subscriber_id_number" class="form-control @error('subscriber_id_number') is-invalid @enderror" 
                                           value="{{ old('subscriber_id_number', $subscriber->subscriber_id_number) }}" required>
                                    @error('subscriber_id_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">اسم المشترك <span class="text-danger">*</span></label>
                                    <input type="text" name="subscriber_name" class="form-control @error('subscriber_name') is-invalid @enderror" 
                                           value="{{ old('subscriber_name', $subscriber->subscriber_name) }}" required>
                                    @error('subscriber_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">رقم الجوال <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $subscriber->phone) }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">عنوان المشترك <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" required>{{ old('address', $subscriber->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ الاشتراك <span class="text-danger">*</span></label>
                                    <input type="date" name="subscription_date" class="form-control @error('subscription_date') is-invalid @enderror" 
                                           value="{{ old('subscription_date', $subscriber->subscription_date?->format('Y-m-d')) }}" required>
                                    @error('subscription_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- التصنيفات --}}
                                <div class="col-12 mt-4">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="bi bi-tags me-2"></i>
                                        التصنيفات
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تصنيف الاشتراك <span class="text-danger">*</span></label>
                                    <select name="subscription_category" class="form-select @error('subscription_category') is-invalid @enderror" required>
                                        <option value="">اختر التصنيف</option>
                                        <option value="1" {{ old('subscription_category', $subscriber->subscription_category) == '1' ? 'selected' : '' }}>منزلي</option>
                                        <option value="2" {{ old('subscription_category', $subscriber->subscription_category) == '2' ? 'selected' : '' }}>تجاري</option>
                                        <option value="3" {{ old('subscription_category', $subscriber->subscription_category) == '3' ? 'selected' : '' }}>خدماتي</option>
                                        <option value="4" {{ old('subscription_category', $subscriber->subscription_category) == '4' ? 'selected' : '' }}>صناعي</option>
                                    </select>
                                    @error('subscription_category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نوع الفاز <span class="text-danger">*</span></label>
                                    <select name="phase_type" class="form-select @error('phase_type') is-invalid @enderror" required>
                                        <option value="">اختر النوع</option>
                                        <option value="1" {{ old('phase_type', $subscriber->phase_type) == '1' ? 'selected' : '' }}>1 فاز</option>
                                        <option value="2" {{ old('phase_type', $subscriber->phase_type) == '2' ? 'selected' : '' }}>3 فاز</option>
                                    </select>
                                    @error('phase_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">حالة الاشتراك <span class="text-danger">*</span></label>
                                    <select name="subscription_status" class="form-select @error('subscription_status') is-invalid @enderror" required>
                                        <option value="">اختر الحالة</option>
                                        <option value="1" {{ old('subscription_status', $subscriber->subscription_status) == '1' ? 'selected' : '' }}>نشط</option>
                                        <option value="2" {{ old('subscription_status', $subscriber->subscription_status) == '2' ? 'selected' : '' }}>موقوف</option>
                                        <option value="3" {{ old('subscription_status', $subscriber->subscription_status) == '3' ? 'selected' : '' }}>مغلق</option>
                                    </select>
                                    @error('subscription_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نوع الخدمة <span class="text-danger">*</span></label>
                                    <select name="service_type" class="form-select @error('service_type') is-invalid @enderror" required>
                                        <option value="">اختر النوع</option>
                                        <option value="1" {{ old('service_type', $subscriber->service_type) == '1' ? 'selected' : '' }}>مولد</option>
                                        <option value="2" {{ old('service_type', $subscriber->service_type) == '2' ? 'selected' : '' }}>شمسي</option>
                                        <option value="3" {{ old('service_type', $subscriber->service_type) == '3' ? 'selected' : '' }}>هجين</option>
                                    </select>
                                    @error('service_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">رقم العداد</label>
                                    <input type="text" name="meter_number" class="form-control @error('meter_number') is-invalid @enderror" 
                                           value="{{ old('meter_number', $subscriber->meter_number) }}">
                                    @error('meter_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- وحدات التوليد --}}
                                <div class="col-12 mt-4">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="bi bi-lightning-charge me-2"></i>
                                        وحدات التوليد
                                    </h6>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">وحدات التوليد المرتبطة <span class="text-danger">*</span></label>
                                    <select name="generation_unit_ids[]" class="form-select @error('generation_unit_ids') is-invalid @enderror" multiple size="5" required>
                                        @foreach($generationUnits as $unit)
                                            <option value="{{ $unit->id }}" {{ in_array($unit->id, old('generation_unit_ids', $subscriber->generationUnits->pluck('id')->toArray())) ? 'selected' : '' }}>
                                                {{ $unit->name }} ({{ $unit->unit_code }}) - {{ $unit->operator->name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">يجب اختيار وحدة توليد واحدة على الأقل (اضغط Ctrl/Cmd للاختيار المتعدد)</small>
                                    @error('generation_unit_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle me-1"></i>
                                            إلغاء
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-1"></i>
                                            حفظ التعديلات
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


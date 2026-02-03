@extends('layouts.admin')

@section('title', 'إضافة مشترك جديد')

@php
    $breadcrumbTitle = 'إضافة مشترك جديد';
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
                                <i class="bi bi-person-plus me-2"></i>
                                إضافة مشترك جديد
                            </h5>
                            <div class="general-subtitle">
                                قم بإدخال جميع بيانات المشترك
                            </div>
                        </div>
                        <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.subscribers.store') }}" method="POST" id="subscriberForm">
                            @csrf

                            <div class="row g-3">
                                {{-- البيانات الأساسية --}}
                                <div class="col-12">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        البيانات الأساسية
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">رقم هوية المشترك <span class="text-danger">*</span></label>
                                    <input type="text" name="subscriber_id_number" class="form-control @error('subscriber_id_number') is-invalid @enderror" 
                                           value="{{ old('subscriber_id_number') }}" required>
                                    @error('subscriber_id_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">اسم المشترك <span class="text-danger">*</span></label>
                                    <input type="text" name="subscriber_name" class="form-control @error('subscriber_name') is-invalid @enderror" 
                                           value="{{ old('subscriber_name') }}" required>
                                    @error('subscriber_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">رقم الموبايل <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone') }}" pattern="^05[69]\d{7}$" maxlength="10" 
                                           placeholder="0591234567 أو 0561234567" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المحافظة</label>
                                    <input type="text" name="governorate_name" class="form-control @error('governorate_name') is-invalid @enderror" 
                                           value="{{ old('governorate_name', $defaultGovernorate ?? '') }}" readonly>
                                    @error('governorate_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">عنوان المشترك <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" required>{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ الاشتراك <span class="text-danger">*</span></label>
                                    <input type="date" name="subscription_date" class="form-control @error('subscription_date') is-invalid @enderror" 
                                           value="{{ old('subscription_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
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
                                        <option value="1" {{ old('subscription_category', '1') == '1' ? 'selected' : '' }}>منزلي</option>
                                        <option value="2" {{ old('subscription_category', '1') == '2' ? 'selected' : '' }}>تجاري</option>
                                        <option value="3" {{ old('subscription_category', '1') == '3' ? 'selected' : '' }}>خدماتي</option>
                                        <option value="4" {{ old('subscription_category', '1') == '4' ? 'selected' : '' }}>صناعي</option>
                                    </select>
                                    @error('subscription_category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نوع الفاز <span class="text-danger">*</span></label>
                                    <select name="phase_type" class="form-select @error('phase_type') is-invalid @enderror" required>
                                        <option value="">اختر النوع</option>
                                        <option value="1" {{ old('phase_type', '1') == '1' ? 'selected' : '' }}>1 فاز</option>
                                        <option value="2" {{ old('phase_type', '1') == '2' ? 'selected' : '' }}>3 فاز</option>
                                    </select>
                                    @error('phase_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">حالة الاشتراك <span class="text-danger">*</span></label>
                                    <select name="subscription_status" class="form-select @error('subscription_status') is-invalid @enderror" required>
                                        <option value="">اختر الحالة</option>
                                        <option value="1" {{ old('subscription_status', '1') == '1' ? 'selected' : '' }}>نشط</option>
                                        <option value="2" {{ old('subscription_status', '1') == '2' ? 'selected' : '' }}>موقوف</option>
                                        <option value="3" {{ old('subscription_status', '1') == '3' ? 'selected' : '' }}>مغلق</option>
                                    </select>
                                    @error('subscription_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نوع الخدمة <span class="text-danger">*</span></label>
                                    <select name="service_type" class="form-select @error('service_type') is-invalid @enderror" required>
                                        <option value="">اختر النوع</option>
                                        <option value="1" {{ old('service_type', '1') == '1' ? 'selected' : '' }}>مولّد</option>
                                        <option value="2" {{ old('service_type', '1') == '2' ? 'selected' : '' }}>شمسي</option>
                                        <option value="3" {{ old('service_type', '1') == '3' ? 'selected' : '' }}>هجين</option>
                                    </select>
                                    @error('service_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">رقم العداد <span class="text-danger">*</span></label>
                                    <input type="text" name="meter_number" class="form-control @error('meter_number') is-invalid @enderror" 
                                           value="{{ old('meter_number') }}" required>
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
                                    <div class="alert alert-info py-2 mb-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        اختر وحدة توليد واحدة على الأقل من الوحدات المسجلة باسمك
                                    </div>
                                    <div class="border rounded p-3 @error('generation_unit_ids') border-danger @enderror" style="max-height: 250px; overflow-y: auto; background-color: #f8f9fa;">
                                        @forelse($generationUnits as $unit)
                                            <div class="d-flex align-items-center mb-2 p-2 rounded {{ in_array($unit->id, old('generation_unit_ids', [])) ? 'bg-primary bg-opacity-10' : 'bg-white' }}" style="border: 1px solid #dee2e6;">
                                                <input class="form-check-input generation-unit-checkbox m-0 me-3" 
                                                       type="checkbox" 
                                                       name="generation_unit_ids[]" 
                                                       value="{{ $unit->id }}" 
                                                       id="unit_{{ $unit->id }}"
                                                       style="width: 20px; height: 20px; cursor: pointer;"
                                                       {{ in_array($unit->id, old('generation_unit_ids', [])) ? 'checked' : '' }}>
                                                <label class="d-flex align-items-center justify-content-between flex-grow-1 m-0" for="unit_{{ $unit->id }}" style="cursor: pointer;">
                                                    <span>
                                                        <i class="bi bi-lightning-charge text-warning me-2"></i>
                                                        <strong>{{ $unit->name }}</strong>
                                                    </span>
                                                    <span>
                                                        <span class="badge bg-secondary">{{ $unit->unit_code }}</span>
                                                        @if($unit->operator)
                                                            <small class="text-muted me-2">
                                                                <i class="bi bi-building me-1"></i>{{ $unit->operator->name }}
                                                            </small>
                                                        @endif
                                                    </span>
                                                </label>
                                            </div>
                                        @empty
                                            <div class="text-center text-muted py-3">
                                                <i class="bi bi-exclamation-circle fs-4 d-block mb-2"></i>
                                                لا توجد وحدات توليد متاحة
                                            </div>
                                        @endforelse
                                    </div>
                                    <small class="form-text text-muted mt-1 d-block">
                                        <i class="bi bi-check2-square me-1"></i>
                                        تم اختيار <span id="selectedUnitsCount" class="fw-bold text-primary">0</span> وحدة توليد
                                    </small>
                                    @error('generation_unit_ids')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
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
                                            حفظ
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

<script>
// التحقق من تكرار البيانات في الوقت الفعلي
document.addEventListener('DOMContentLoaded', function() {
    const subscriberIdInput = document.querySelector('input[name="subscriber_id_number"]');
    const phoneInput = document.querySelector('input[name="phone"]');
    const meterNumberInput = document.querySelector('input[name="meter_number"]');

    // التحقق من رقم الهوية
    if (subscriberIdInput) {
        subscriberIdInput.addEventListener('blur', function() {
            checkUniqueness('subscriber_id_number', this.value, this);
        });
    }

    // التحقق من رقم الهاتف
    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            if (this.value.match(/^05[69]\d{7}$/)) {
                checkUniqueness('phone', this.value, this);
            }
        });
    }

    // التحقق من رقم العداد
    if (meterNumberInput) {
        meterNumberInput.addEventListener('blur', function() {
            if (this.value.trim()) {
                checkUniqueness('meter_number', this.value, this);
            }
        });
    }

    function checkUniqueness(field, value, input) {
        if (!value.trim()) return;

        fetch('{{ route("admin.subscribers.check-unique") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ field: field, value: value })
        })
        .then(response => response.json())
        .then(data => {
            const feedback = input.parentElement.querySelector('.unique-feedback');
            
            if (feedback) {
                feedback.remove();
            }

            input.classList.remove('is-invalid', 'is-valid');

            if (data.exists) {
                input.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback unique-feedback';
                errorDiv.textContent = data.message;
                input.parentElement.appendChild(errorDiv);
            } else {
                input.classList.add('is-valid');
            }
        })
        .catch(error => {
            console.error('خطأ في التحقق من البيانات:', error);
        });
    }

    // تحديث عداد وحدات التوليد المختارة
    const unitCheckboxes = document.querySelectorAll('.generation-unit-checkbox');
    const selectedCountSpan = document.getElementById('selectedUnitsCount');
    
    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.generation-unit-checkbox:checked').length;
        selectedCountSpan.textContent = checkedCount;
        
        // تحديث لون الخلفية للوحدات المختارة
        unitCheckboxes.forEach(checkbox => {
            const parentDiv = checkbox.closest('.form-check');
            if (checkbox.checked) {
                parentDiv.classList.add('bg-primary', 'bg-opacity-10');
                parentDiv.classList.remove('bg-white');
            } else {
                parentDiv.classList.remove('bg-primary', 'bg-opacity-10');
                parentDiv.classList.add('bg-white');
            }
        });
    }
    
    // إضافة event listener لكل checkbox
    unitCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // تحديث العداد عند تحميل الصفحة
    updateSelectedCount();
});
</script>
@endsection
@extends('layouts.admin')

@section('title', 'إضافة مشترك جديد')

@php
    $breadcrumbTitle = 'إضافة مشترك جديد';
    $breadcrumbParent = 'إدارة بيانات المشتركين';
    $breadcrumbParentUrl = route('admin.subscribers.index');
    $subscriptionCategories = \App\Helpers\ConstantsHelper::get(23);
    $phaseTypes             = \App\Helpers\ConstantsHelper::get(24);
    $subscriptionStatuses   = \App\Helpers\ConstantsHelper::get(25);
    $serviceTypes           = \App\Helpers\ConstantsHelper::get(26);
    $ampereOptions          = \App\Helpers\ConstantsHelper::get(31);
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
                                           value="{{ old('phone') }}" maxlength="10" 
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
                                        @foreach($subscriptionCategories as $item)
                                            <option value="{{ $item->value }}" {{ old('subscription_category', '1') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
                                        @endforeach
                                    </select>
                                    @error('subscription_category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نوع الفاز <span class="text-danger">*</span></label>
                                    <select name="phase_type" class="form-select @error('phase_type') is-invalid @enderror" required>
                                        <option value="">اختر النوع</option>
                                        @foreach($phaseTypes as $item)
                                            <option value="{{ $item->value }}" {{ old('phase_type', '1') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
                                        @endforeach
                                    </select>
                                    @error('phase_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">حالة الاشتراك <span class="text-danger">*</span></label>
                                    <select name="subscription_status" class="form-select @error('subscription_status') is-invalid @enderror" required>
                                        <option value="">اختر الحالة</option>
                                        @foreach($subscriptionStatuses as $item)
                                            <option value="{{ $item->value }}" {{ old('subscription_status', '1') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
                                        @endforeach
                                    </select>
                                    @error('subscription_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نوع الخدمة <span class="text-danger">*</span></label>
                                    <select name="service_type" class="form-select @error('service_type') is-invalid @enderror" required>
                                        <option value="">اختر النوع</option>
                                        @foreach($serviceTypes as $item)
                                            <option value="{{ $item->value }}" {{ old('service_type', '1') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
                                        @endforeach
                                    </select>
                                    @error('service_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">رقم العداد</label>
                                    <input type="text" name="meter_number" class="form-control @error('meter_number') is-invalid @enderror" 
                                           value="{{ old('meter_number') }}">
                                    @error('meter_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">قيمة أمبير الاشتراك</label>
                                    <select name="ampere" class="form-select @error('ampere') is-invalid @enderror">
                                        <option value="">-- اختر القيمة --</option>
                                        @foreach($ampereOptions as $item)
                                            <option value="{{ $item->value }}" {{ old('ampere') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
                                        @endforeach
                                    </select>
                                    @error('ampere')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">قراءة العداد الافتتاحية</label>
                                    <input type="number" name="opening_reading" class="form-control @error('opening_reading') is-invalid @enderror" 
                                           value="{{ old('opening_reading') }}" min="0" step="0.01" placeholder="اختياري">
                                    @error('opening_reading')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 d-flex align-items-center">
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" name="is_employee_subscription" id="is_employee_subscription" value="1" {{ old('is_employee_subscription') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_employee_subscription">
                                            <i class="bi bi-person-badge me-1"></i>
                                            اشتراك موظف الشركة
                                        </label>
                                        <div class="form-text text-muted">تحديد هذا الخيار يُطبّق نسبة الخصم المستحقة عند الفوترة</div>
                                    </div>
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
// التحقق من صحة البيانات في الوقت الفعلي
document.addEventListener('DOMContentLoaded', function() {
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
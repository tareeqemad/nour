@extends('layouts.admin')

@section('title', 'تعديل قضية تفتيش وتعدي')

@php
    $breadcrumbTitle = 'تعديل قضية';
    $breadcrumbParent = 'قضايا التفتيش والتعدي';
    $breadcrumbParentUrl = route('admin.inspection-violation-cases.index');
@endphp

@section('content')
    <div class="general-page" id="inspectionCasesEditPage">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-shield-exclamation me-2"></i>
                                تعديل قضية تفتيش وتعدي
                            </h5>
                            <div class="general-subtitle">
                                @if($inspection_violation_case->subscriber_name)
                                    {{ $inspection_violation_case->subscriber_name }}
                                    @if($inspection_violation_case->case_date)
                                        · {{ $inspection_violation_case->case_date->format('Y-m-d') }}
                                    @endif
                                @else
                                    تعديل بيانات القضية
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('admin.inspection-violation-cases.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                    </div>
                    <div class="card-body pb-4">
                    <form action="{{ route('admin.inspection-violation-cases.update', $inspection_violation_case) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">المحافظة</label>
                                <select name="governorate_id" class="form-select @error('governorate_id') is-invalid @enderror">
                                    <option value="">-- اختر المحافظة --</option>
                                    @foreach($governorates ?? [] as $gov)
                                        <option value="{{ $gov->value }}" {{ old('governorate_id', $inspection_violation_case->governorateDetail?->value) == $gov->value ? 'selected' : '' }}>{{ $gov->label }}</option>
                                    @endforeach
                                </select>
                                @error('governorate_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">رقم الاشتراك</label>
                                <input type="text" name="subscription_number" class="form-control @error('subscription_number') is-invalid @enderror" value="{{ old('subscription_number', $inspection_violation_case->subscription_number) }}">
                                @error('subscription_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">اسم المشترك <span class="text-danger">*</span></label>
                                <input type="text" name="subscriber_name" class="form-control @error('subscriber_name') is-invalid @enderror" value="{{ old('subscriber_name', $inspection_violation_case->subscriber_name) }}" required>
                                @error('subscriber_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">اسم المنتفع</label>
                                <input type="text" name="beneficiary_name" class="form-control @error('beneficiary_name') is-invalid @enderror" value="{{ old('beneficiary_name', $inspection_violation_case->beneficiary_name) }}">
                                @error('beneficiary_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تاريخ القضية <span class="text-danger">*</span></label>
                                <input type="date" name="case_date" class="form-control @error('case_date') is-invalid @enderror" value="{{ old('case_date', $inspection_violation_case->case_date?->format('Y-m-d')) }}" required>
                                @error('case_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">حالة القضية <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="1" {{ old('status', $inspection_violation_case->status) == 1 ? 'selected' : '' }}>ادخال</option>
                                    <option value="2" {{ old('status', $inspection_violation_case->status) == 2 ? 'selected' : '' }}>محكومة</option>
                                    <option value="3" {{ old('status', $inspection_violation_case->status) == 3 ? 'selected' : '' }}>منتهية</option>
                                </select>
                                @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">بيان القضية/التعدي</label>
                                <textarea name="statement" class="form-control @error('statement') is-invalid @enderror" rows="4">{{ old('statement', $inspection_violation_case->statement) }}</textarea>
                                @error('statement')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            @if(($operators ?? collect())->isNotEmpty())
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المشغل</label>
                                    <select name="operator_id" id="caseOperatorId" class="form-select">
                                        <option value="">-- اختر المشغل --</option>
                                        @foreach($operators as $op)
                                            <option value="{{ $op->id }}" {{ old('operator_id', $inspection_violation_case->operator_id) == $op->id ? 'selected' : '' }}>{{ $op->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">وحدة التوليد</label>
                                    <select name="generation_unit_id" id="caseGenerationUnitId" class="form-select" {{ $inspection_violation_case->operator_id ? '' : 'disabled' }}>
                                        <option value="">-- اختر وحدة التوليد --</option>
                                        @foreach($generationUnits ?? [] as $gu)
                                            <option value="{{ $gu->id }}" data-operator="{{ $gu->operator_id }}" {{ old('generation_unit_id', $inspection_violation_case->generation_unit_id) == $gu->id ? 'selected' : '' }}>{{ $gu->name }} ({{ $gu->unit_code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-12 pt-2 d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.inspection-violation-cases.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-check-lg me-2"></i>
                                    حفظ التعديلات
                                </button>
                            </div>
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
(function() {
    const operatorSelect = document.getElementById('caseOperatorId');
    const generationUnitSelect = document.getElementById('caseGenerationUnitId');
    
    if (!operatorSelect || !generationUnitSelect) return;

    // القيمة الحالية لوحدة التوليد (من القضية أو old)
    const savedGenerationUnitId = @json(old('generation_unit_id', $inspection_violation_case->generation_unit_id));

    // جمع خيارات المشغلين (التي لها قيمة فعلية)
    const operatorOptions = Array.from(operatorSelect.options).filter(opt => opt.value !== '');

    // إذا كان هناك مشغل واحد فقط ولم يكن محدداً، اختره تلقائياً
    if (operatorOptions.length === 1 && !operatorSelect.value) {
        operatorSelect.value = operatorOptions[0].value;
        loadGenerationUnits(operatorOptions[0].value);
    }

    // عند تغيير المشغل
    operatorSelect.addEventListener('change', function() {
        const operatorId = this.value;
        if (!operatorId) {
            generationUnitSelect.innerHTML = '<option value="">-- اختر المشغل أولاً --</option>';
            generationUnitSelect.disabled = true;
            return;
        }
        loadGenerationUnits(operatorId);
    });

    // تحميل وحدات التوليد عبر AJAX
    async function loadGenerationUnits(operatorId) {
        try {
            generationUnitSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            generationUnitSelect.disabled = true;

            const response = await fetch('/admin/operators/' + operatorId + '/generation-units', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response error:', response.status, errorText);
                throw new Error('فشل تحميل وحدات التوليد: ' + response.status);
            }

            const data = await response.json();
            
            generationUnitSelect.innerHTML = '<option value="">-- اختر وحدة التوليد --</option>';
            
            if (data.generation_units && data.generation_units.length > 0) {
                data.generation_units.forEach(function(unit) {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.name + ' (' + (unit.unit_code || '') + ')';
                    generationUnitSelect.appendChild(option);
                });
                generationUnitSelect.disabled = false;

                // تحديد وحدة التوليد المحفوظة
                if (savedGenerationUnitId) {
                    generationUnitSelect.value = savedGenerationUnitId;
                }
            } else {
                generationUnitSelect.innerHTML = '<option value="">لا توجد وحدات توليد</option>';
            }
        } catch (error) {
            console.error('Error loading generation units:', error);
            generationUnitSelect.innerHTML = '<option value="">خطأ في التحميل</option>';
        }
    }
})();
</script>
@endpush

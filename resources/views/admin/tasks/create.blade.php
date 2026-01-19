@extends('layouts.admin')

@section('title', 'تكليف مهمة جديدة')

@php
    $breadcrumbTitle = 'تكليف مهمة جديدة';
    $breadcrumbParent = 'المهام';
    $breadcrumbParentUrl = route('admin.tasks.index');
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-clipboard-plus me-2"></i>
                                تكليف مهمة جديدة
                            </h5>
                            <div class="general-subtitle">
                                قم بتكليف فني أو دفاع مدني بمهمة صيانة أو فحص سلامة
                            </div>
                        </div>
                        <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.tasks.store') }}" method="POST" id="taskForm">
                            @csrf

                            <!-- معلومات المهمة -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    معلومات المهمة
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-tag text-info me-1"></i>
                                            نوع المهمة <span class="text-danger">*</span>
                                        </label>
                                        <select name="type" id="taskType" 
                                                class="form-select @error('type') is-invalid @enderror" required>
                                            <option value="">اختر نوع المهمة</option>
                                            <option value="maintenance" {{ old('type') == 'maintenance' ? 'selected' : '' }}>صيانة</option>
                                            <option value="safety_inspection" {{ old('type') == 'safety_inspection' ? 'selected' : '' }}>فحص سلامة</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-person-check text-success me-1"></i>
                                            المكلف <span class="text-danger">*</span>
                                        </label>
                                        <select name="assigned_to" id="assignedTo" 
                                                class="form-select @error('assigned_to') is-invalid @enderror" required>
                                            <option value="">اختر المكلف</option>
                                            <optgroup label="فنيون" id="techniciansGroup">
                                                @foreach($technicians as $tech)
                                                    <option value="{{ $tech->id }}" 
                                                            data-role="technician"
                                                            {{ old('assigned_to') == $tech->id ? 'selected' : '' }}>
                                                        {{ $tech->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="دفاع مدني" id="civilDefenseGroup">
                                                @foreach($civilDefense as $cd)
                                                    <option value="{{ $cd->id }}" 
                                                            data-role="civil_defense"
                                                            {{ old('assigned_to') == $cd->id ? 'selected' : '' }}>
                                                        {{ $cd->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                        @error('assigned_to')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">سيتم إرسال SMS وإشعار للمكلف</small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- موقع المهمة -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-building text-primary me-2"></i>
                                    موقع المهمة
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-building text-primary me-1"></i>
                                            المشغل <span class="text-danger">*</span>
                                        </label>
                                        <select name="operator_id" id="operatorId" 
                                                class="form-select @error('operator_id') is-invalid @enderror" required>
                                            <option value="">اختر المشغل</option>
                                            @foreach($operators as $operator)
                                                <option value="{{ $operator->id }}" {{ old('operator_id') == $operator->id ? 'selected' : '' }}>
                                                    {{ $operator->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('operator_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-lightning-charge text-warning me-1"></i>
                                            وحدة التوليد
                                        </label>
                                        <select name="generation_unit_id" id="generationUnitId" 
                                                class="form-select @error('generation_unit_id') is-invalid @enderror">
                                            <option value="">اختر وحدة التوليد</option>
                                        </select>
                                        @error('generation_unit_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-lightning-charge-fill text-danger me-1"></i>
                                            المولد
                                        </label>
                                        <select name="generator_id" id="generatorId" 
                                                class="form-select @error('generator_id') is-invalid @enderror">
                                            <option value="">اختر المولد</option>
                                        </select>
                                        @error('generator_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- تفاصيل المهمة -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-file-text text-info me-2"></i>
                                    تفاصيل المهمة
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-card-text text-primary me-1"></i>
                                            وصف المهمة <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="description" id="description" rows="4" 
                                                  class="form-control @error('description') is-invalid @enderror" 
                                                  placeholder="أدخل وصف المهمة..." required>{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-event text-success me-1"></i>
                                            تاريخ الاستحقاق
                                        </label>
                                        <input type="date" name="due_date" id="dueDate" 
                                               class="form-control @error('due_date') is-invalid @enderror" 
                                               value="{{ old('due_date') }}"
                                               min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                        @error('due_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-sticky text-secondary me-1"></i>
                                            ملاحظات
                                        </label>
                                        <textarea name="notes" id="notes" rows="2" 
                                                  class="form-control @error('notes') is-invalid @enderror" 
                                                  placeholder="ملاحظات إضافية (اختياري)">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary" id="cancelBtn">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-check-lg me-2"></i>
                                    <span class="btn-text">تكليف المهمة</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        جاري المعالجة...
                                    </span>
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
document.addEventListener('DOMContentLoaded', function() {
    const taskType = document.getElementById('taskType');
    const assignedTo = document.getElementById('assignedTo');
    const operatorId = document.getElementById('operatorId');
    const generationUnitId = document.getElementById('generationUnitId');
    const generatorId = document.getElementById('generatorId');

    // تحديث خيارات المكلف حسب نوع المهمة
    taskType.addEventListener('change', function() {
        const type = this.value;
        const options = assignedTo.querySelectorAll('option[data-role]');
        
        options.forEach(option => {
            if (type === 'maintenance') {
                // صيانة: إظهار الفنيين فقط
                option.style.display = option.dataset.role === 'technician' ? '' : 'none';
            } else if (type === 'safety_inspection') {
                // فحص سلامة: إظهار الدفاع المدني فقط
                option.style.display = option.dataset.role === 'civil_defense' ? '' : 'none';
            } else {
                // إظهار الجميع
                option.style.display = '';
            }
        });

        // إعادة تعيين المكلف إذا لم يكن مناسباً
        const selectedOption = assignedTo.options[assignedTo.selectedIndex];
        if (selectedOption && selectedOption.dataset.role) {
            if ((type === 'maintenance' && selectedOption.dataset.role !== 'technician') ||
                (type === 'safety_inspection' && selectedOption.dataset.role !== 'civil_defense')) {
                assignedTo.value = '';
            }
        }
    });

    // جلب وحدات التوليد عند اختيار المشغل
    operatorId.addEventListener('change', function() {
        const operatorIdValue = this.value;
        generationUnitId.innerHTML = '<option value="">جاري التحميل...</option>';
        generationUnitId.disabled = true;
        generatorId.innerHTML = '<option value="">اختر المولد</option>';
        generatorId.disabled = true;

        if (!operatorIdValue) {
            generationUnitId.innerHTML = '<option value="">اختر وحدة التوليد</option>';
            generationUnitId.disabled = false;
            return;
        }

        fetch(`{{ route('admin.tasks.generation-units', ':operator') }}`.replace(':operator', operatorIdValue), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })
        .then(response => response.json())
        .then(data => {
            generationUnitId.innerHTML = '<option value="">اختر وحدة التوليد</option>';
            if (data.success && data.data && data.data.length > 0) {
                data.data.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.name + (unit.unit_code ? ` (${unit.unit_code})` : '');
                    generationUnitId.appendChild(option);
                });
            }
            generationUnitId.disabled = false;
        })
        .catch(error => {
            console.error('Error fetching generation units:', error);
            generationUnitId.innerHTML = '<option value="">حدث خطأ في تحميل وحدات التوليد</option>';
            generationUnitId.disabled = false;
        });
    });

    // جلب المولدات عند اختيار وحدة التوليد
    generationUnitId.addEventListener('change', function() {
        const generationUnitIdValue = this.value;
        generatorId.innerHTML = '<option value="">جاري التحميل...</option>';
        generatorId.disabled = true;

        if (!generationUnitIdValue) {
            generatorId.innerHTML = '<option value="">اختر المولد</option>';
            generatorId.disabled = false;
            return;
        }

        fetch(`{{ route('admin.tasks.generators', ':generationUnit') }}`.replace(':generationUnit', generationUnitIdValue), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })
        .then(response => response.json())
        .then(data => {
            generatorId.innerHTML = '<option value="">اختر المولد</option>';
            if (data.success && data.data && data.data.length > 0) {
                data.data.forEach(generator => {
                    const option = document.createElement('option');
                    option.value = generator.id;
                    option.textContent = generator.name + (generator.generator_number ? ` (${generator.generator_number})` : '');
                    generatorId.appendChild(option);
                });
            }
            generatorId.disabled = false;
        })
        .catch(error => {
            console.error('Error fetching generators:', error);
            generatorId.innerHTML = '<option value="">حدث خطأ في تحميل المولدات</option>';
            generatorId.disabled = false;
        });
    });

    // تشغيل change event عند تحميل الصفحة إذا كان هناك قيمة محفوظة
    @if(old('operator_id'))
        operatorId.dispatchEvent(new Event('change'));
        setTimeout(() => {
            @if(old('generation_unit_id'))
                generationUnitId.value = {{ old('generation_unit_id') }};
                generationUnitId.dispatchEvent(new Event('change'));
                setTimeout(() => {
                    @if(old('generator_id'))
                        generatorId.value = {{ old('generator_id') }};
                    @endif
                }, 500);
            @endif
        }, 500);
    @endif

    // التحقق من صحة النموذج قبل الإرسال وتعطيل الزر
    const taskForm = document.getElementById('taskForm');
    const submitBtn = document.getElementById('submitBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnSpinner = submitBtn.querySelector('.btn-spinner');

    function enableSubmitButton() {
        submitBtn.disabled = false;
        cancelBtn.style.pointerEvents = 'auto';
        cancelBtn.style.opacity = '1';
        btnText.classList.remove('d-none');
        btnSpinner.classList.add('d-none');
    }

    function disableSubmitButton() {
        submitBtn.disabled = true;
        cancelBtn.style.pointerEvents = 'none';
        cancelBtn.style.opacity = '0.6';
        btnText.classList.add('d-none');
        btnSpinner.classList.remove('d-none');
    }

    taskForm.addEventListener('submit', function(e) {
        const type = taskType.value;
        const assignedToValue = assignedTo.value;
        const selectedOption = assignedTo.options[assignedTo.selectedIndex];

        // التحقق من تطابق نوع المهمة مع دور المكلف
        if (type && assignedToValue && selectedOption && selectedOption.dataset.role) {
            if ((type === 'maintenance' && selectedOption.dataset.role !== 'technician') ||
                (type === 'safety_inspection' && selectedOption.dataset.role !== 'civil_defense')) {
                e.preventDefault();
                alert('نوع المهمة لا يطابق دور المكلف. يرجى اختيار المكلف المناسب.');
                return false;
            }
        }

        // تعطيل الزر وإظهار spinner
        disableSubmitButton();

        // إعادة تفعيل الزر بعد 30 ثانية كحد أقصى (في حالة حدوث خطأ في الشبكة)
        setTimeout(function() {
            if (submitBtn.disabled) {
                enableSubmitButton();
            }
        }, 30000);
    });

    // إعادة تفعيل الزر عند حدوث خطأ في تحميل الصفحة (validation errors)
    // عند حدوث validation errors، Laravel سيعيد تحميل الصفحة، لذا الزر سيعود لحالته الطبيعية
    // لكن إذا كان هناك خطأ في الشبكة، نعيد تفعيل الزر بعد 30 ثانية
});
</script>
@endpush

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
                <x-admin.card>
                    <x-admin.card-header-form title="تكليف مهمة جديدة" icon="bi-clipboard-plus" :backRoute="route('admin.tasks.index')" />

                    <div class="card-body p-4">
                        <form action="{{ route('admin.tasks.store') }}" method="POST" id="taskForm">
                            @csrf

                            {{-- Section: معلومات المهمة --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">معلومات المهمة</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
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
                                    <small class="text-muted mt-1 d-block">سيتم إرسال SMS وإشعار للمكلف</small>
                                </div>
                            </div>

                            {{-- Section: موقع المهمة --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">موقع المهمة</h6>
                            <div class="row g-3 mb-4">
                                <x-admin.operator-cascade
                                    :operators="$operators"
                                    :showGenerator="true"
                                    :showGenerationUnit="true"
                                    :operatorRequired="true"
                                    :generationUnitRequired="false"
                                    :generatorRequired="false"
                                    :selectedOperatorId="old('operator_id')"
                                    :selectedGenerationUnitId="old('generation_unit_id')"
                                    :selectedGeneratorId="old('generator_id')"
                                    colClass="col-md-4"
                                />
                            </div>

                            {{-- Section: تفاصيل المهمة --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">تفاصيل المهمة</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
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
                                    <label class="form-label fw-semibold">تاريخ الاستحقاق</label>
                                    <input type="date" name="due_date" id="dueDate"
                                           class="form-control @error('due_date') is-invalid @enderror"
                                           value="{{ old('due_date') }}"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    @error('due_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">ملاحظات</label>
                                    <textarea name="notes" id="notes" rows="2"
                                              class="form-control @error('notes') is-invalid @enderror"
                                              placeholder="ملاحظات إضافية (اختياري)">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                                <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary" id="cancelBtn">
                                    <i class="bi bi-arrow-right me-1"></i>
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
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const taskType = document.getElementById('taskType');
    const assignedTo = document.getElementById('assignedTo');

    // تحديث خيارات المكلف حسب نوع المهمة
    taskType.addEventListener('change', function() {
        const type = this.value;
        const options = assignedTo.querySelectorAll('option[data-role]');

        options.forEach(option => {
            if (type === 'maintenance') {
                option.style.display = option.dataset.role === 'technician' ? '' : 'none';
            } else if (type === 'safety_inspection') {
                option.style.display = option.dataset.role === 'civil_defense' ? '' : 'none';
            } else {
                option.style.display = '';
            }
        });

        const selectedOption = assignedTo.options[assignedTo.selectedIndex];
        if (selectedOption && selectedOption.dataset.role) {
            if ((type === 'maintenance' && selectedOption.dataset.role !== 'technician') ||
                (type === 'safety_inspection' && selectedOption.dataset.role !== 'civil_defense')) {
                assignedTo.value = '';
            }
        }
    });

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

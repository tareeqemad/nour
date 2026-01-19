@extends('layouts.admin')

@section('title', 'تعديل سجل تشغيل')

@php
    $breadcrumbTitle = 'تعديل سجل تشغيل';
    $breadcrumbParent = 'سجلات التشغيل';
    $breadcrumbParentUrl = route('admin.operation-logs.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/operation-logs.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-pencil-square me-2"></i>
                                تعديل سجل التشغيل
                            </h5>
                            <div class="general-subtitle">
                                المولد: {{ $operationLog->generator->name ?? 'مولد محذوف' }}@if($operationLog->generator && $operationLog->generator->trashed()) <span class="badge bg-secondary">محذوف</span>@endif | 
                                التاريخ: {{ $operationLog->operation_date->format('Y-m-d') }}
                            </div>
                        </div>
                        <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.operation-logs.update', $operationLog) }}" method="POST" id="operationLogForm">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-info-circle text-primary me-2"></i>
                                        المعلومات الأساسية
                                    </h6>
                                </div>

                                @if(auth()->user()->isSuperAdmin())
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            المشغل <span class="text-danger">*</span>
                                        </label>
                                        <select name="operator_id" id="operator_id" 
                                                class="form-select select2 @error('operator_id') is-invalid @enderror">
                                            <option value="0">-- اختر المشغل --</option>
                                            @foreach($operators as $operator)
                                                <option value="{{ $operator->id }}" 
                                                        {{ old('operator_id', $operationLog->operator_id) == $operator->id ? 'selected' : '' }}>
                                                    {{ $operator->name }}
                                                    @if($operator->unit_number)
                                                        - {{ $operator->unit_number }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('operator_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            وحدة التوليد <span class="text-danger">*</span>
                                        </label>
                                        <select name="generation_unit_id" id="generation_unit_id" 
                                                class="form-select select2 @error('generation_unit_id') is-invalid @enderror" 
                                                required>
                                            <option value="0">-- اختر وحدة التوليد --</option>
                                        </select>
                                        @error('generation_unit_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            المولد <span class="text-danger">*</span>
                                        </label>
                                        <select name="generator_id" id="generator_id" 
                                                class="form-select select2 @error('generator_id') is-invalid @enderror">
                                            <option value="0">-- اختر المولد --</option>
                                        </select>
                                        @error('generator_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            المشغل <span class="text-danger">*</span>
                                        </label>
                                        <select name="operator_id" id="operator_id" 
                                                class="form-select select2 @error('operator_id') is-invalid @enderror" 
                                                disabled>
                                            <option value="{{ $operators->first()->id }}" selected>
                                                {{ $operators->first()->name }}
                                                @if($operators->first()->unit_number)
                                                    - {{ $operators->first()->unit_number }}
                                                @endif
                                            </option>
                                        </select>
                                        <input type="hidden" name="operator_id" value="{{ $operationLog->operator_id }}">
                                        @error('operator_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            وحدة التوليد <span class="text-danger">*</span>
                                        </label>
                                        <select name="generation_unit_id" id="generation_unit_id" 
                                                class="form-select select2 @error('generation_unit_id') is-invalid @enderror" 
                                                required>
                                            <option value="0">-- اختر وحدة التوليد --</option>
                                            @foreach($generationUnits as $unit)
                                                <option value="{{ $unit->id }}" 
                                                        {{ old('generation_unit_id', $operationLog->generator->generation_unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->name }} ({{ $unit->unit_code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('generation_unit_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            المولد <span class="text-danger">*</span>
                                        </label>
                                        <select name="generator_id" id="generator_id" 
                                                class="form-select select2 @error('generator_id') is-invalid @enderror">
                                            <option value="0">-- اختر المولد --</option>
                                            @foreach($generators as $generator)
                                                <option value="{{ $generator->id }}" 
                                                        data-generation-unit-id="{{ $generator->generation_unit_id }}"
                                                        {{ old('generator_id', $operationLog->generator_id) == $generator->id ? 'selected' : '' }}>
                                                    {{ $generator->generator_number }} - {{ $generator->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('generator_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-calendar-event text-success me-2"></i>
                                        التاريخ والوقت
                                    </h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        تاريخ التشغيل <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="operation_date" 
                                           class="form-control @error('operation_date') is-invalid @enderror" 
                                           value="{{ old('operation_date', $operationLog->operation_date->format('Y-m-d')) }}">
                                    @error('operation_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        وقت البدء <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" name="start_time" 
                                           class="form-control @error('start_time') is-invalid @enderror" 
                                           value="{{ old('start_time', $operationLog->start_time ? $operationLog->start_time->format('H:i') : '') }}">
                                    @error('start_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        وقت الإيقاف <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" name="end_time" 
                                           class="form-control @error('end_time') is-invalid @enderror" 
                                           value="{{ old('end_time', $operationLog->end_time ? $operationLog->end_time->format('H:i') : '') }}">
                                    @error('end_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-speedometer2 text-warning me-2"></i>
                                        الأداء ونسبة التحميل
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        نسبة التحميل (%)
                                    </label>
                                    <input type="number" step="0.01" name="load_percentage" 
                                           class="form-control @error('load_percentage') is-invalid @enderror" 
                                           value="{{ old('load_percentage', $operationLog->load_percentage) }}" 
                                           min="0" max="100" 
                                           placeholder="0.00">
                                    <small class="text-muted">النسبة المئوية للتحميل أثناء التشغيل</small>
                                    @error('load_percentage')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-fuel-pump text-danger me-2"></i>
                                        قراءات عداد الوقود
                                    </h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        قراءة عداد الوقود عند البدء
                                    </label>
                                    <input type="number" step="0.01" name="fuel_meter_start" 
                                           class="form-control @error('fuel_meter_start') is-invalid @enderror" 
                                           value="{{ old('fuel_meter_start', $operationLog->fuel_meter_start) }}" 
                                           min="0" 
                                           placeholder="0.00">
                                    @error('fuel_meter_start')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        قراءة عداد الوقود عند الانتهاء
                                    </label>
                                    <input type="number" step="0.01" name="fuel_meter_end" 
                                           class="form-control @error('fuel_meter_end') is-invalid @enderror" 
                                           value="{{ old('fuel_meter_end', $operationLog->fuel_meter_end) }}" 
                                           min="0" 
                                           placeholder="0.00">
                                    @error('fuel_meter_end')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        كمية الوقود المستهلك (لتر)
                                    </label>
                                    <input type="number" step="0.01" name="fuel_consumed" 
                                           class="form-control calculated-field @error('fuel_consumed') is-invalid @enderror" 
                                           value="{{ old('fuel_consumed', $operationLog->fuel_consumed) }}" 
                                           min="0" 
                                           placeholder="0.00"
                                           id="fuel_consumed"
                                           readonly
                                           tabindex="-1">
                                    <small class="text-muted">يتم الحساب تلقائياً من قراءات البداية والنهاية</small>
                                    @error('fuel_consumed')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-lightning-charge text-warning me-2"></i>
                                        قراءات عداد الطاقة
                                    </h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        قراءة عداد الطاقة عند البدء
                                    </label>
                                    <input type="number" step="0.01" name="energy_meter_start" 
                                           class="form-control @error('energy_meter_start') is-invalid @enderror" 
                                           value="{{ old('energy_meter_start', $operationLog->energy_meter_start) }}" 
                                           min="0" 
                                           placeholder="0.00">
                                    @error('energy_meter_start')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        قراءة عداد الطاقة عند الإيقاف
                                    </label>
                                    <input type="number" step="0.01" name="energy_meter_end" 
                                           class="form-control @error('energy_meter_end') is-invalid @enderror" 
                                           value="{{ old('energy_meter_end', $operationLog->energy_meter_end) }}" 
                                           min="0" 
                                           placeholder="0.00">
                                    @error('energy_meter_end')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        كمية الطاقة المنتجة (kWh)
                                    </label>
                                    <input type="number" step="0.01" name="energy_produced" 
                                           class="form-control calculated-field @error('energy_produced') is-invalid @enderror" 
                                           value="{{ old('energy_produced', $operationLog->energy_produced) }}" 
                                           min="0" 
                                           placeholder="0.00"
                                           id="energy_produced"
                                           readonly
                                           tabindex="-1">
                                    <small class="text-muted">يتم الحساب تلقائياً من قراءات البداية والنهاية</small>
                                    @error('energy_produced')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        سعر التعرفة الكهربائية (₪/kWh)
                                    </label>
                                    <input type="number" step="0.0001" name="electricity_tariff_price" 
                                           class="form-control @error('electricity_tariff_price') is-invalid @enderror" 
                                           value="{{ old('electricity_tariff_price', $operationLog->electricity_tariff_price) }}" 
                                           min="0" 
                                           max="500"
                                           placeholder="0.0000"
                                           id="electricity_tariff_price">
                                    <small class="text-muted">سيتم تعبئته تلقائياً حسب المشغل وتاريخ التشغيل (يمكن التعديل) - مثال: في غزة قد يصل السعر إلى 30+ شيكل</small>
                                    @error('electricity_tariff_price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-journal-text text-primary me-2"></i>
                                        الملاحظات والأعطال
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        ملاحظات تشغيلية
                                    </label>
                                    <textarea name="operational_notes" 
                                              class="form-control @error('operational_notes') is-invalid @enderror" 
                                              rows="4" 
                                              placeholder="أدخل أي ملاحظات متعلقة بتشغيل المولد...">{{ old('operational_notes', $operationLog->operational_notes) }}</textarea>
                                    @error('operational_notes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        الأعطال المسجلة (إن وجدت)
                                    </label>
                                    <textarea name="malfunctions" 
                                              class="form-control @error('malfunctions') is-invalid @enderror" 
                                              rows="4" 
                                              placeholder="أدخل تفاصيل أي أعطال تمت ملاحظتها...">{{ old('malfunctions', $operationLog->malfunctions) }}</textarea>
                                    @error('malfunctions')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-2"></i>
                                إلغاء
                            </a>
                            <button type="submit" form="operationLogForm" class="btn btn-primary" id="submitOperationLogBtn">
                                <i class="bi bi-check-lg me-2"></i>
                                حفظ التعديلات
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
@if(file_exists(public_path('assets/admin/libs/select2/i18n/ar.js')))
    <script src="{{ asset('assets/admin/libs/select2/i18n/ar.js') }}"></script>
@endif
<script>
    (function($) {
        $(document).ready(function() {
            const $form = $('#operationLogForm');
            // البحث عن الزر باستخدام ID أولاً، ثم البحث في النموذج
            let $submitBtn = $('#submitOperationLogBtn');
            if (!$submitBtn.length) {
                $submitBtn = $form.find('button[type="submit"]');
            }

            // Initialize Select2 for all selects
            $('.select2').select2({
                dir: 'rtl',
                language: 'ar',
                allowClear: true,
                width: '100%'
            });

            const $operatorSelect = $('#operator_id');
            const $generationUnitSelect = $('#generation_unit_id');
            const $generatorSelect = $('#generator_id');
            
            @php
                $currentOperatorId = old('operator_id', $operationLog->operator_id);
                $currentGenerationUnitId = old('generation_unit_id', $operationLog->generator->generation_unit_id ?? null);
                $currentGeneratorId = old('generator_id', $operationLog->generator_id);
            @endphp
            
            @if(auth()->user()->isSuperAdmin())
                // للسوبر أدمن: المشغل → وحدة التوليد → المولد
                // عند اختيار المشغل
                $operatorSelect.on('change', async function() {
                    const operatorId = $(this).val();
                    
                    // إعادة تهيئة Select2 لوحدة التوليد
                    $generationUnitSelect.empty().append('<option value="0">-- اختر وحدة التوليد --</option>').select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: true,
                        width: '100%'
                    }).prop('disabled', true);
                    
                    // إعادة تهيئة Select2 للمولد
                    $generatorSelect.empty().append('<option value="0">-- اختر المولد --</option>').select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: true,
                        width: '100%'
                    }).prop('disabled', true);
                    
                    if (!operatorId || operatorId == '0') {
                        $generationUnitSelect.empty().append('<option value="0">-- اختر وحدة التوليد --</option>').prop('disabled', true);
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/admin/operators/${operatorId}/generation-units-for-logs`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            $generationUnitSelect.empty().append('<option value="0">-- اختر وحدة التوليد --</option>');
                            
                            if (data.generation_units && data.generation_units.length > 0) {
                                data.generation_units.forEach(unit => {
                                    const isSelected = unit.id == {{ $currentGenerationUnitId ?? 0 }};
                                    $generationUnitSelect.append(new Option(unit.label, unit.id, false, isSelected));
                                });
                                $generationUnitSelect.prop('disabled', false).trigger('change');
                                
                                // إذا كان هناك قيمة محفوظة، تحميل المولدات تلقائياً
                                @if($currentGenerationUnitId)
                                    $generationUnitSelect.trigger('change');
                                @endif
                            } else {
                                $generationUnitSelect.append('<option value="">لا توجد وحدات توليد</option>');
                            }
                        }
                    } catch (error) {
                        console.error('Error loading generation units:', error);
                        $generationUnitSelect.empty().append('<option value="">حدث خطأ في التحميل</option>');
                    }
                });
                
                // عند اختيار وحدة التوليد
                $generationUnitSelect.on('change', async function() {
                    const generationUnitId = $(this).val();
                    
                    // إعادة تهيئة Select2 للمولد
                    $generatorSelect.empty().append('<option value="0">-- اختر المولد --</option>').select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: true,
                        width: '100%'
                    }).prop('disabled', true);
                    
                    if (!generationUnitId || generationUnitId == '0') {
                        $generatorSelect.empty().append('<option value="0">-- اختر المولد --</option>').prop('disabled', true);
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/admin/generation-units/${generationUnitId}/generators-for-logs`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            $generatorSelect.empty().append('<option value="0">-- اختر المولد --</option>');
                            
                            if (data.generators && data.generators.length > 0) {
                                data.generators.forEach(generator => {
                                    const isSelected = generator.id == {{ $currentGeneratorId ?? 0 }};
                                    $generatorSelect.append(new Option(generator.label, generator.id, false, isSelected));
                                });
                                $generatorSelect.prop('disabled', false).trigger('change');
                            } else {
                                $generatorSelect.append('<option value="">لا توجد مولدات</option>');
                            }
                        }
                    } catch (error) {
                        console.error('Error loading generators:', error);
                        $generatorSelect.empty().append('<option value="">حدث خطأ في التحميل</option>');
                    }
                });
                
                // تحميل البيانات الأولية عند تحميل الصفحة
                @if($currentOperatorId)
                    (async function() {
                        try {
                            const response = await fetch(`/admin/operators/{{ $currentOperatorId }}/generation-units-for-logs`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                }
                            });
                            
                            if (response.ok) {
                                const data = await response.json();
                                $generationUnitSelect.empty().append('<option value="0">-- اختر وحدة التوليد --</option>');
                                
                                if (data.generation_units && data.generation_units.length > 0) {
                                    data.generation_units.forEach(unit => {
                                        const isSelected = unit.id == {{ $currentGenerationUnitId ?? 0 }};
                                        $generationUnitSelect.append(new Option(unit.label, unit.id, false, isSelected));
                                    });
                                    $generationUnitSelect.prop('disabled', false).trigger('change');
                                    
                                    // تحميل المولدات تلقائياً
                                    @if($currentGenerationUnitId)
                                        const genUnitResponse = await fetch(`/admin/generation-units/{{ $currentGenerationUnitId }}/generators-for-logs`, {
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                            }
                                        });
                                        
                                        if (genUnitResponse.ok) {
                                            const genData = await genUnitResponse.json();
                                            $generatorSelect.empty().append('<option value="0">-- اختر المولد --</option>');
                                            
                                            if (genData.generators && genData.generators.length > 0) {
                                                genData.generators.forEach(generator => {
                                                    const isSelected = generator.id == {{ $currentGeneratorId ?? 0 }};
                                                    $generatorSelect.append(new Option(generator.label, generator.id, false, isSelected));
                                                });
                                                $generatorSelect.prop('disabled', false).trigger('change');
                                            }
                                        }
                                    @endif
                                }
                            }
                        } catch (error) {
                            console.error('Error loading initial data:', error);
                        }
                    })();
                @endif
            @else
                // للمشغل/الموظف: المشغل محدد → وحدات التوليد تظهر تلقائياً → يختار المولد
                $generationUnitSelect.on('change', function() {
                    const generationUnitId = $(this).val();
                    const currentValue = $generatorSelect.val();
                    
                    // تصفية المولدات حسب وحدة التوليد
                    $generatorSelect.find('option').each(function() {
                        const $option = $(this);
                        if (!$option.val()) return; // تجاهل option الفارغ
                        
                        const optionGenerationUnitId = $option.data('generation-unit-id');
                        if (generationUnitId && optionGenerationUnitId == generationUnitId) {
                            $option.prop('disabled', false).show();
                        } else if (!generationUnitId) {
                            $option.prop('disabled', false).show();
                        } else {
                            $option.prop('disabled', true).hide();
                            if ($option.val() === currentValue) {
                                $generatorSelect.val('').trigger('change');
                            }
                        }
                    });
                });
                
                @if($currentGenerationUnitId)
                    $generationUnitSelect.trigger('change');
                @endif
            @endif
            
            const fuelStart = document.querySelector('input[name="fuel_meter_start"]');
            const fuelEnd = document.querySelector('input[name="fuel_meter_end"]');
            const fuelConsumed = document.getElementById('fuel_consumed');
            
            function calculateFuel() {
                if (fuelStart && fuelEnd && fuelConsumed) {
                    const start = parseFloat(fuelStart.value) || 0; // قراءة عداد الوقود عند البدء
                    const end = parseFloat(fuelEnd.value) || 0; // قراءة عداد الوقود عند الانتهاء
                    if (start > 0 && end > 0) {
                        if (end >= start) {
                            // المعادلة: كمية الوقود المستهلك = قراءة عداد الوقود عند الانتهاء - قراءة عداد الوقود عند البدء
                            fuelConsumed.value = (end - start).toFixed(2);
                            // إزالة أي تحذير سابق
                            fuelStart.classList.remove('is-invalid');
                            fuelEnd.classList.remove('is-invalid');
                            const existingAlert = fuelStart.parentElement.querySelector('.fuel-reading-alert');
                            if (existingAlert) {
                                existingAlert.remove();
                            }
                        } else {
                            // قراءة الانتهاء أقل من قراءة البدء - تحذير
                            fuelConsumed.value = '';
                            fuelStart.classList.add('is-invalid');
                            fuelEnd.classList.add('is-invalid');
                            
                            // إزالة أي تحذير سابق
                            const existingAlert = fuelStart.parentElement.querySelector('.fuel-reading-alert');
                            if (existingAlert) {
                                existingAlert.remove();
                            }
                            
                            // إضافة تحذير
                            const alert = document.createElement('div');
                            alert.className = 'alert alert-warning fuel-reading-alert mt-2';
                            alert.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i><strong>تحذير:</strong> قراءة عداد الوقود عند الانتهاء (' + end + ') أقل من قراءة البدء (' + start + '). يرجى التحقق من صحة القراءات المدخلة.';
                            fuelStart.parentElement.appendChild(alert);
                            
                            // إظهار إشعار
                            if (typeof window.showToast === 'function') {
                                window.showToast('تحذير: قراءة عداد الوقود عند الانتهاء أقل من قراءة البدء. يرجى التحقق من صحة القراءات.', 'warning');
                            }
                        }
                    } else {
                        fuelConsumed.value = '';
                        // إزالة التحذيرات إذا كانت الحقول فارغة
                        fuelStart.classList.remove('is-invalid');
                        fuelEnd.classList.remove('is-invalid');
                        const existingAlert = fuelStart.parentElement.querySelector('.fuel-reading-alert');
                        if (existingAlert) {
                            existingAlert.remove();
                        }
                    }
                }
            }
            
            if (fuelStart) fuelStart.addEventListener('input', calculateFuel);
            if (fuelEnd) fuelEnd.addEventListener('input', calculateFuel);
            
            const energyStart = document.querySelector('input[name="energy_meter_start"]');
            const energyEnd = document.querySelector('input[name="energy_meter_end"]');
            const energyProduced = document.getElementById('energy_produced');
            
            function calculateEnergy() {
                if (energyStart && energyEnd && energyProduced) {
                    const start = parseFloat(energyStart.value) || 0; // قراءة عداد الطاقة عند البدء
                    const end = parseFloat(energyEnd.value) || 0; // قراءة عداد الطاقة عند الإيقاف
                    if (start > 0 && end > 0) {
                        if (end >= start) {
                            // المعادلة: كمية الطاقة المنتجة = قراءة عداد الطاقة عند الإيقاف - قراءة عداد الطاقة عند البدء
                            energyProduced.value = (end - start).toFixed(2);
                            // إزالة أي تحذير سابق
                            energyStart.classList.remove('is-invalid');
                            energyEnd.classList.remove('is-invalid');
                            const existingAlert = energyStart.parentElement.querySelector('.energy-reading-alert');
                            if (existingAlert) {
                                existingAlert.remove();
                            }
                        } else {
                            // قراءة الإيقاف أقل من قراءة البدء - تحذير
                            energyProduced.value = '';
                            energyStart.classList.add('is-invalid');
                            energyEnd.classList.add('is-invalid');
                            
                            // إزالة أي تحذير سابق
                            const existingAlert = energyStart.parentElement.querySelector('.energy-reading-alert');
                            if (existingAlert) {
                                existingAlert.remove();
                            }
                            
                            // إضافة تحذير
                            const alert = document.createElement('div');
                            alert.className = 'alert alert-warning energy-reading-alert mt-2';
                            alert.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i><strong>تحذير:</strong> قراءة عداد الطاقة عند الإيقاف (' + end + ') أقل من قراءة البدء (' + start + '). يرجى التحقق من صحة القراءات المدخلة.';
                            energyStart.parentElement.appendChild(alert);
                            
                            // إظهار إشعار
                            if (typeof window.showToast === 'function') {
                                window.showToast('تحذير: قراءة عداد الطاقة عند الإيقاف أقل من قراءة البدء. يرجى التحقق من صحة القراءات.', 'warning');
                            }
                        }
                    } else {
                        energyProduced.value = '';
                        // إزالة التحذيرات إذا كانت الحقول فارغة
                        energyStart.classList.remove('is-invalid');
                        energyEnd.classList.remove('is-invalid');
                        const existingAlert = energyStart.parentElement.querySelector('.energy-reading-alert');
                        if (existingAlert) {
                            existingAlert.remove();
                        }
                    }
                }
            }
            
            if (energyStart) energyStart.addEventListener('input', calculateEnergy);
            if (energyEnd) energyEnd.addEventListener('input', calculateEnergy);

            // Auto-fill electricity tariff price based on operator and date
            const tariffPriceInput = document.getElementById('electricity_tariff_price');
            const operationDateInput = document.querySelector('input[name="operation_date"]');
            const operatorSelect = document.getElementById('operator_id');
            
            async function loadTariffPrice() {
                const operatorId = operatorSelect?.value || {{ $operationLog->operator_id }};
                const operationDate = operationDateInput?.value || '{{ $operationLog->operation_date->format('Y-m-d') }}';
                
                if (!operatorId || !operationDate || !tariffPriceInput) return;
                
                try {
                    const response = await fetch(`/admin/operators/${operatorId}/api/tariff-price?date=${operationDate}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.price && !tariffPriceInput.value) {
                            tariffPriceInput.value = parseFloat(data.price).toFixed(4);
                        }
                    }
                } catch (error) {
                    console.error('Error loading tariff price:', error);
                }
            }
            
            if (operatorSelect) operatorSelect.addEventListener('change', loadTariffPrice);
            if (operationDateInput) operationDateInput.addEventListener('change', loadTariffPrice);
            
            // Load on page load
            loadTariffPrice();

            $form.on('submit', function(e) {
                e.preventDefault();
                
                // التحقق من الحقول المطلوبة
                let hasErrors = false;
                let errorMessages = [];
                
                const $operator = $form.find('[name="operator_id"]');
                const $generationUnit = $form.find('[name="generation_unit_id"]');
                const $generator = $form.find('[name="generator_id"]');
                
                if ($operator.length && (!$operator.val() || $operator.val() == '0')) {
                    $operator.addClass('is-invalid');
                    errorMessages.push('المشغل مطلوب');
                    hasErrors = true;
                } else {
                    $operator.removeClass('is-invalid');
                }
                
                if ($generationUnit.length && (!$generationUnit.val() || $generationUnit.val() == '0')) {
                    $generationUnit.addClass('is-invalid');
                    errorMessages.push('وحدة التوليد مطلوبة');
                    hasErrors = true;
                } else {
                    $generationUnit.removeClass('is-invalid');
                }
                
                if ($generator.length && (!$generator.val() || $generator.val() == '0')) {
                    $generator.addClass('is-invalid');
                    errorMessages.push('المولد مطلوب');
                    hasErrors = true;
                } else {
                    $generator.removeClass('is-invalid');
                }
                
                // عرض رسائل الخطأ في Toast
                if (hasErrors && errorMessages.length > 0) {
                    const errorMsg = errorMessages.join('، ');
                    if (typeof window.showToast === 'function') {
                        window.showToast(errorMsg, 'error');
                    }
                }
                
                // التحقق من صحة النموذج
                if (!$form[0].checkValidity() || hasErrors) {
                    if (hasErrors) {
                        // التمرير إلى أول حقل فيه خطأ
                        const $firstError = $form.find('.is-invalid').first();
                        if ($firstError.length) {
                            $('html, body').animate({
                                scrollTop: $firstError.offset().top - 100
                            }, 500);
                        }
                    } else {
                        $form[0].reportValidity();
                    }
                    return false;
                }

                // التحقق من صحة قراءات عداد الوقود
                const fuelStart = parseFloat($('input[name="fuel_meter_start"]').val()) || 0;
                const fuelEnd = parseFloat($('input[name="fuel_meter_end"]').val()) || 0;
                if (fuelStart > 0 && fuelEnd > 0 && fuelEnd < fuelStart) {
                    if (typeof window.showToast === 'function') {
                        window.showToast('خطأ: قراءة عداد الوقود عند الانتهاء أقل من قراءة البدء. يرجى تصحيح القراءات قبل الحفظ.', 'error');
                    } else {
                        alert('خطأ: قراءة عداد الوقود عند الانتهاء أقل من قراءة البدء. يرجى تصحيح القراءات قبل الحفظ.');
                    }
                    $('input[name="fuel_meter_start"]').focus();
                    return false;
                }

                // التحقق من صحة قراءات عداد الطاقة
                const energyStart = parseFloat($('input[name="energy_meter_start"]').val()) || 0;
                const energyEnd = parseFloat($('input[name="energy_meter_end"]').val()) || 0;
                if (energyStart > 0 && energyEnd > 0 && energyEnd < energyStart) {
                    if (typeof window.showToast === 'function') {
                        window.showToast('خطأ: قراءة عداد الطاقة عند الإيقاف أقل من قراءة البدء. يرجى تصحيح القراءات قبل الحفظ.', 'error');
                    } else {
                        alert('خطأ: قراءة عداد الطاقة عند الإيقاف أقل من قراءة البدء. يرجى تصحيح القراءات قبل الحفظ.');
                    }
                    $('input[name="energy_meter_start"]').focus();
                    return false;
                }

                // تعطيل الزر فوراً - استخدام عدة طرق للتأكد
                const submitBtnElement = document.getElementById('submitOperationLogBtn') || $submitBtn[0];
                if (submitBtnElement) {
                    submitBtnElement.disabled = true;
                    submitBtnElement.setAttribute('disabled', 'disabled');
                }
                $submitBtn.prop('disabled', true);
                $submitBtn.attr('disabled', 'disabled');
                $submitBtn.addClass('disabled');
                const originalText = $submitBtn.html();
                $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');

                const formData = new FormData(this);
                
                // Remove calculated fields from form data (server will calculate them)
                formData.delete('fuel_consumed');
                formData.delete('energy_produced');

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            // الزر يبقى معطلاً حتى يتم التوجيه
                            if (typeof window.showToast === 'function') {
                                window.showToast(response.message || 'تم تحديث سجل التشغيل بنجاح', 'success');
                            } else {
                                alert(response.message || 'تم تحديث سجل التشغيل بنجاح');
                            }
                            setTimeout(function() {
                                window.location.href = '{{ route('admin.operation-logs.index') }}';
                            }, 500);
                        } else {
                            // في حالة عدم النجاح، إعادة تفعيل الزر
                            $submitBtn.prop('disabled', false);
                            $submitBtn.html(originalText);
                            if (typeof window.showToast === 'function') {
                                window.showToast(response.message || 'حدث خطأ أثناء حفظ البيانات', 'error');
                            } else {
                                alert(response.message || 'حدث خطأ أثناء حفظ البيانات');
                            }
                        }
                    },
                    error: function(xhr) {
                        // عند حدوث خطأ، إعادة تفعيل الزر فوراً
                        const submitBtnElement = document.getElementById('submitOperationLogBtn') || $submitBtn[0];
                        if (submitBtnElement) {
                            submitBtnElement.disabled = false;
                            submitBtnElement.removeAttribute('disabled');
                        }
                        $submitBtn.prop('disabled', false);
                        $submitBtn.removeAttr('disabled');
                        $submitBtn.removeClass('disabled');
                        $submitBtn.html(originalText);

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON?.errors || {};
                            let firstError = '';
                            
                            $form.find('.is-invalid').removeClass('is-invalid');
                            $form.find('.invalid-feedback').remove();

                            $.each(errors, function(field, messages) {
                                const $field = $form.find('[name="' + field + '"]');
                                if ($field.length) {
                                    $field.addClass('is-invalid');
                                    const errorMsg = Array.isArray(messages) ? messages[0] : messages;
                                    if (!firstError) firstError = errorMsg;
                                    
                                    // إزالة أي رسالة خطأ سابقة في نفس الـ container
                                    const $parent = $field.closest('.col-md-4, .col-md-6, .col-12');
                                    $parent.find('.invalid-feedback').remove();
                                    
                                    // إضافة رسالة الخطأ في parent container
                                    $parent.append('<div class="invalid-feedback d-block">' + errorMsg + '</div>');
                                }
                            });

                            // التمرير إلى أول حقل فيه خطأ
                            const $firstErrorField = $form.find('.is-invalid').first();
                            if ($firstErrorField.length) {
                                $('html, body').animate({
                                    scrollTop: $firstErrorField.offset().top - 100
                                }, 500);
                                $firstErrorField.focus();
                            }

                            if (typeof window.showToast === 'function') {
                                window.showToast(firstError || 'يرجى التحقق من الحقول المطلوبة', 'error');
                            } else {
                                alert(firstError || 'يرجى التحقق من الحقول المطلوبة');
                            }
                        } else {
                            const errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء حفظ البيانات';
                            if (typeof window.showToast === 'function') {
                                window.showToast(errorMsg, 'error');
                            } else {
                                alert(errorMsg);
                            }
                        }
                    },
                    complete: function() {
                        // هذا يتم تنفيذه دائماً بعد success أو error
                        // لكننا نترك الزر معطلاً في حالة النجاح (لأننا سننتقل للصفحة الأخرى)
                        // وإعادة تفعيله في حالة الخطأ (تم في error handler)
                    }
                });
            });
        });
    })(jQuery);
</script>
@endpush

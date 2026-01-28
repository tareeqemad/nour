@extends('layouts.admin')

@section('title', 'إضافة مولد جديد')

@php
    $breadcrumbTitle = 'إضافة مولد جديد';
    $breadcrumbParent = 'إدارة المولدات';
    $breadcrumbParentUrl = route('admin.generators.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/generators.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/cascading-selects.css') }}">
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-lightning-charge me-2"></i>
                                إضافة مولد جديد
                            </h5>
                            <div class="general-subtitle">
                                قم بإدخال جميع بيانات المولد الكهربائي
                            </div>
                        </div>
                        <a href="{{ route('admin.generators.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.generators.store') }}" method="POST" enctype="multipart/form-data" id="generatorForm">
                            @csrf

                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs mb-3" id="generatorTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" 
                                    data-bs-target="#basic" type="button" role="tab" aria-controls="basic" aria-selected="true">
                                <i class="bi bi-info-circle me-1"></i> البيانات الأساسية
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="specs-tab" data-bs-toggle="tab" 
                                    data-bs-target="#specs" type="button" role="tab" aria-controls="specs" aria-selected="false">
                                <i class="bi bi-gear-wide-connected me-1"></i> المواصفات الفنية
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="fuel-tab" data-bs-toggle="tab" 
                                    data-bs-target="#fuel" type="button" role="tab" aria-controls="fuel" aria-selected="false">
                                <i class="bi bi-droplet me-1"></i> الوقود والتشغيل
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="technical-tab" data-bs-toggle="tab" 
                                    data-bs-target="#technical" type="button" role="tab" aria-controls="technical" aria-selected="false">
                                <i class="bi bi-clipboard-check me-1"></i> الحالة الفنية
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="control-tab" data-bs-toggle="tab" 
                                    data-bs-target="#control" type="button" role="tab" aria-controls="control" aria-selected="false">
                                <i class="bi bi-sliders me-1"></i> نظام التحكم
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content pt-3" id="generatorTabsContent">
                        <!-- المعلومات الأساسية -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            @php
                                $affiliatedOperator = auth()->user()->getAffiliatedOperator();
                                $selectedGenerationUnitId = request()->query('generation_unit_id') ?? old('generation_unit_id');
                                $canSelect = !auth()->user()->isAffiliatedWithOperator();
                            @endphp
                            <div class="row g-3">
                                {{-- Cascading Selects: المشغل → وحدة التوليد --}}
                                @include('admin.partials.cascading-selects', [
                                    'operators' => $operators ?? collect(),
                                    'affiliatedOperator' => $affiliatedOperator,
                                    'showGenerator' => false,
                                    'showGenerationUnit' => true,
                                    'generationUnits' => $affiliatedOperator?->generationUnits ?? collect(),
                                    'selectedGenerationUnitId' => $selectedGenerationUnitId,
                                    'colClass' => 'col-md-3',
                                ])
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">رقم المولد</label>
                                    <input type="text" name="generator_number" id="generator_number" class="form-control @error('generator_number') is-invalid @enderror" 
                                           value="{{ old('generator_number') }}" readonly placeholder="مثال: GU-MD-DR-001-G01" style="background-color: #f8f9fa;">
                                    <div class="form-text">يتم توليده تلقائياً بناءً على وحدة التوليد.</div>
                                    @error('generator_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">اسم المولد <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">الوصف</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">حالة المولد <span class="text-danger">*</span></label>
                                    <select name="status_id" class="form-select @error('status_id') is-invalid @enderror" required>
                                        <option value="">اختر الحالة</option>
                                        @foreach($constants['status'] ?? [] as $status)
                                            <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                                {{ $status->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- المواصفات الفنية -->
                        <div class="tab-pane fade" id="specs" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">قدرة المولد (KVA)</label>
                                <input type="number" step="1" name="capacity_kva" class="form-control @error('capacity_kva') is-invalid @enderror" 
                                       value="{{ old('capacity_kva', '250') }}" min="1">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">معامل القدرة (P.F)</label>
                                <input type="number" step="0.01" name="power_factor" class="form-control @error('power_factor') is-invalid @enderror" 
                                       value="{{ old('power_factor', '0.8') }}" min="0" max="1">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الجهد الناتج (V)</label>
                                <input type="number" name="voltage" class="form-control @error('voltage') is-invalid @enderror" 
                                       value="{{ old('voltage', '400') }}" min="0">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">التردد (Hz)</label>
                                <input type="number" name="frequency" class="form-control @error('frequency') is-invalid @enderror" 
                                       value="{{ old('frequency', '50') }}" min="0">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">سنة التصنيع (YYYY)</label>
                                <input type="text" name="manufacturing_year" id="manufacturing_year" class="form-control @error('manufacturing_year') is-invalid @enderror" 
                                       value="{{ old('manufacturing_year', '2022') }}" placeholder="اختر السنة">
                                @error('manufacturing_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">نوع المحرك</label>
                                <select name="engine_type_id" class="form-select @error('engine_type_id') is-invalid @enderror">
                                    <option value="">اختر نوع المحرك</option>
                                    @foreach($constants['engine_type'] ?? [] as $engineType)
                                        <option value="{{ $engineType->id }}" {{ old('engine_type_id') == $engineType->id ? 'selected' : '' }}>
                                            {{ $engineType->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('engine_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>
                    </div>
                        </div>

                        <!-- التشغيل والوقود -->
                        <div class="tab-pane fade" id="fuel" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">نظام الحقن</label>
                                <select name="injection_system_id" class="form-select @error('injection_system_id') is-invalid @enderror">
                                    <option value="">اختر نظام الحقن</option>
                                    @foreach($constants['injection_system'] ?? [] as $injection)
                                        <option value="{{ $injection->id }}" {{ old('injection_system_id') == $injection->id ? 'selected' : '' }}>
                                            {{ $injection->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('injection_system_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">معدل استهلاك الوقود (لتر/ساعة)</label>
                                <input type="number" step="0.01" name="fuel_consumption_rate" class="form-control @error('fuel_consumption_rate') is-invalid @enderror" 
                                       value="{{ old('fuel_consumption_rate') }}" min="0">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">كفاءة الوقود المثالية (kWh/لتر)</label>
                                <input type="number" step="0.001" name="ideal_fuel_efficiency" class="form-control @error('ideal_fuel_efficiency') is-invalid @enderror" 
                                       value="{{ old('ideal_fuel_efficiency', '0.5') }}" min="0" max="10" placeholder="0.5">
                                <small class="form-text text-muted">تستخدم لحساب الفاقد في الوقود في لوحة التحكم (القيمة الافتراضية: 0.5)</small>
                                @error('ideal_fuel_efficiency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">سعة خزان الوقود الداخلي (لتر)</label>
                                <input type="number" name="internal_tank_capacity" class="form-control @error('internal_tank_capacity') is-invalid @enderror" 
                                       value="{{ old('internal_tank_capacity') }}" min="0">
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">مؤشر القياس</label>
                                <select name="measurement_indicator_id" class="form-select @error('measurement_indicator_id') is-invalid @enderror">
                                    <option value="">اختر الحالة</option>
                                    @foreach($constants['measurement_indicator'] ?? [] as $indicator)
                                        <option value="{{ $indicator->id }}" {{ old('measurement_indicator_id') == $indicator->id ? 'selected' : '' }}>
                                            {{ $indicator->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('measurement_indicator_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>
                    </div>
                        </div>

                        <!-- الحالة الفنية والتوثيق -->
                        <div class="tab-pane fade" id="technical" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الحالة الفنية</label>
                                <select name="technical_condition_id" class="form-select @error('technical_condition_id') is-invalid @enderror">
                                    <option value="">اختر الحالة</option>
                                    @foreach($constants['technical_condition'] ?? [] as $condition)
                                        <option value="{{ $condition->id }}" {{ old('technical_condition_id') == $condition->id ? 'selected' : '' }}>
                                            {{ $condition->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('technical_condition_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تاريخ آخر صيانة كبرى</label>
                                <input type="date" name="last_major_maintenance_date" class="form-control @error('last_major_maintenance_date') is-invalid @enderror" 
                                       value="{{ old('last_major_maintenance_date') }}" max="{{ date('Y-m-d') }}">
                                @error('last_major_maintenance_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">صورة لوحة البيانات للمحرك</label>
                                <input type="file" name="engine_data_plate_image" class="form-control image-input @error('engine_data_plate_image') is-invalid @enderror" 
                                       accept="image/*" data-preview="engine_data_plate_preview">
                                <div class="image-preview-container mt-2" id="engine_data_plate_preview" style="display: none;">
                                    <img src="" alt="معاينة" class="image-preview">
                                    <button type="button" class="btn btn-sm btn-danger remove-image" onclick="removeImagePreview('engine_data_plate_image', 'engine_data_plate_preview')">
                                        <i class="bi bi-x-circle"></i> إزالة
                                    </button>
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">صورة لوحة البيانات للمولد</label>
                                <input type="file" name="generator_data_plate_image" class="form-control image-input @error('generator_data_plate_image') is-invalid @enderror" 
                                       accept="image/*" data-preview="generator_data_plate_preview">
                                <div class="image-preview-container mt-2" id="generator_data_plate_preview" style="display: none;">
                                    <img src="" alt="معاينة" class="image-preview">
                                    <button type="button" class="btn btn-sm btn-danger remove-image" onclick="removeImagePreview('generator_data_plate_image', 'generator_data_plate_preview')">
                                        <i class="bi bi-x-circle"></i> إزالة
                                    </button>
                                </div>
                                
                        </div>
                    </div>
                        </div>

                        <!-- نظام التحكم -->
                        <div class="tab-pane fade" id="control" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">لوحة التحكم</label>
                                <select name="control_panel_available" class="form-select @error('control_panel_available') is-invalid @enderror">
                                    <option value="0" {{ old('control_panel_available', '0') == '0' ? 'selected' : '' }}>غير متوفرة</option>
                                    <option value="1" {{ old('control_panel_available') == '1' ? 'selected' : '' }}>متوفرة</option>
                                </select>
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">نوع لوحة التحكم</label>
                                <select name="control_panel_type_id" class="form-select @error('control_panel_type_id') is-invalid @enderror">
                                    <option value="">اختر النوع</option>
                                    @foreach($constants['control_panel_type'] ?? [] as $panelType)
                                        <option value="{{ $panelType->id }}" {{ old('control_panel_type_id') == $panelType->id ? 'selected' : '' }}>
                                            {{ $panelType->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('control_panel_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">حالة لوحة التحكم</label>
                                <select name="control_panel_status_id" class="form-select @error('control_panel_status_id') is-invalid @enderror">
                                    <option value="">اختر الحالة</option>
                                    @foreach($constants['control_panel_status'] ?? [] as $panelStatus)
                                        <option value="{{ $panelStatus->id }}" {{ old('control_panel_status_id') == $panelStatus->id ? 'selected' : '' }}>
                                            {{ $panelStatus->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('control_panel_status_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">صورة لوحة التحكم</label>
                                <input type="file" name="control_panel_image" class="form-control image-input @error('control_panel_image') is-invalid @enderror" 
                                       accept="image/*" data-preview="control_panel_preview">
                                <div class="image-preview-container mt-2" id="control_panel_preview" style="display: none;">
                                    <img src="" alt="معاينة" class="image-preview">
                                    <button type="button" class="btn btn-sm btn-danger remove-image" onclick="removeImagePreview('control_panel_image', 'control_panel_preview')">
                                        <i class="bi bi-x-circle"></i> إزالة
                                    </button>
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">قراءة ساعات التشغيل الحالية</label>
                                <input type="number" name="operating_hours" class="form-control @error('operating_hours') is-invalid @enderror" 
                                       value="{{ old('operating_hours') }}" min="0">
                                
                        </div>
                    </div>
                        </div>
                    </div>
                    </form>
                </div>

                    <!-- Navigation Buttons -->
                <div class="card-footer border-top py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-primary btn-lg px-4 rounded-pill shadow-sm" id="prevBtn" onclick="navigateTabs(-1)" style="display: none;">
                            <i class="bi bi-arrow-right me-2"></i>السابق
                        </button>
                        <div class="d-flex gap-2">
                        <a href="{{ route('admin.generators.index') }}" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">
                            <i class="bi bi-x-circle me-2"></i>إلغاء
                        </a>
                            <button type="button" class="btn btn-primary btn-lg px-5 rounded-pill shadow" id="nextBtn" onclick="navigateTabs(1)">
                                التالي<i class="bi bi-arrow-left ms-2"></i>
                            </button>
                            <button type="submit" form="generatorForm" class="btn btn-success btn-lg px-5 rounded-pill shadow" id="submitBtn" style="display: none;">
                                <i class="bi bi-check-lg me-2"></i>حفظ البيانات
                            </button>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* تبسيط التابات */
    .nav-tabs {
        border-bottom: 2px solid #e9ecef;
    }
    
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 0.75rem 1.25rem;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s ease;
        background: transparent;
    }
    
    .nav-tabs .nav-link:hover {
        color: #19228f;
        border-bottom-color: #dee2e6;
    }
    
    .nav-tabs .nav-link.active {
        color: #19228f;
        border-bottom-color: #19228f;
        background: transparent;
        font-weight: 600;
    }
    
    /* استجابة للشاشات الصغيرة */
    @media (max-width: 768px) {
        .nav-tabs .nav-link {
            font-size: 0.8rem;
            padding: 0.6rem 0.8rem;
        }
    }
    
    
    /* تحسين الأزرار */
    .btn {
        border-radius: 0.5rem;
        padding: 0.625rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-lg.rounded-pill {
        border-radius: 50rem !important;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        border: none;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    }
    
    .btn-success {
        background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
        border: none;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
        background: linear-gradient(135deg, #166534 0%, #16a34a 100%);
    }
    
    .btn-outline-primary:hover {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        border-color: transparent;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    
    .btn-outline-secondary:hover {
        transform: translateY(-2px);
    }
    
    /* تحسين الكاردات */
    .card {
        border-radius: 1rem;
        overflow: hidden;
    }
    
    .card-header {
        border: none;
    }
    
    /* تحسين التاب المحتوى */
    .tab-content {
        min-height: 400px;
    }
    
    .tab-pane {
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* تحسين المساحات */
    .row.g-3 > * {
        margin-bottom: 0.5rem;
    }
    
    /* تأثيرات المؤشرات */
    #sessionTimer {
        animation: pulse 2s infinite;
        font-weight: 600;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }
    
    /* تحسين التنبيهات */
    .alert {
        border-radius: 0.75rem;
    }
    
    .alert-info {
        border-left: 4px solid #2563eb;
    }
    
    /* معاينة الصور */
    .image-preview-container {
        position: relative;
        border-radius: 0.5rem;
        overflow: hidden;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 0.5rem;
        border: 2px dashed #dee2e6;
        transition: all 0.3s ease;
    }
    
    .image-preview-container:hover {
        border-color: #2563eb;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    }
    
    .image-preview {
        width: 100%;
        max-height: 200px;
        object-fit: contain;
        border-radius: 0.375rem;
        display: block;
        transition: transform 0.3s ease;
    }
    
    .image-preview:hover {
        transform: scale(1.02);
    }
    
    .remove-image {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 0.25rem;
        z-index: 10;
        transition: all 0.3s ease;
    }
    
    .remove-image:hover {
        transform: scale(1.1);
    }
    
    /* تحسين الفورم للشاشات الصغيرة */
    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem !important;
        }
        
        .image-preview {
            max-height: 150px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
<script src="{{ asset('assets/admin/libs/select2/i18n/ar.js') }}"></script>
<script src="{{ asset('assets/admin/js/cascading-selects.js') }}"></script>
<script>
    // تمرير الثوابت للـ JavaScript
    window.GENERATOR_CONSTANTS = {
        material: @json(($constants['material'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
        usage: @json(($constants['usage'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
        measurement_method: @json(($constants['measurement_method'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
    };

    document.addEventListener('DOMContentLoaded', function() {
        // تهيئة Select2 للحقول
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('.select2').select2({
                dir: 'rtl',
                language: 'ar',
                allowClear: true,
                width: '100%'
            });
        }

        let currentTab = 0;
        const tabs = ['basic', 'specs', 'fuel', 'technical', 'control'];
        let lastRefreshTime = Date.now();
        const refreshInterval = 10 * 60 * 1000; // 10 دقائق
        
        // عرض وقت التحديث التالي
        function updateSessionTimer() {
            const timePassed = Date.now() - lastRefreshTime;
            const timeRemaining = Math.max(0, refreshInterval - timePassed);
            const minutesRemaining = Math.ceil(timeRemaining / 60000);
            
            const timerBadge = document.getElementById('sessionTimer');
            if (timerBadge) {
                if (minutesRemaining > 5) {
                    timerBadge.className = 'badge bg-success ms-2';
                    timerBadge.textContent = `نشط - التحديث بعد ${minutesRemaining} دقيقة`;
                } else if (minutesRemaining > 2) {
                    timerBadge.className = 'badge bg-warning ms-2';
                    timerBadge.textContent = `التحديث بعد ${minutesRemaining} دقائق`;
                } else if (minutesRemaining > 0) {
                    timerBadge.className = 'badge bg-danger ms-2';
                    timerBadge.textContent = `التحديث قريباً (${minutesRemaining} دقيقة)`;
                }
            }
        }
        
        // تحديث المؤقت كل 30 ثانية
        setInterval(updateSessionTimer, 30000);
        updateSessionTimer();
        
        // تحديث CSRF Token كل 10 دقائق لتجنب مشكلة Page Expired
        function refreshCSRFToken() {
            // استخدام route أبسط بدلاً من create route لتجنب أي مشاكل
            fetch('{{ route("admin.dashboard") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newToken = doc.querySelector('meta[name="csrf-token"]') || doc.querySelector('input[name="_token"]');
                if (newToken) {
                    const tokenValue = newToken.getAttribute('content') || newToken.value;
                    const tokenInput = document.querySelector('input[name="_token"]');
                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    
                    if (tokenInput) {
                        tokenInput.value = tokenValue;
                    }
                    if (tokenMeta) {
                        tokenMeta.setAttribute('content', tokenValue);
                    }
                    
                    lastRefreshTime = Date.now();
                    updateSessionTimer();
                    
                    // عرض إشعار نجاح
                    const timerBadge = document.getElementById('sessionTimer');
                    if (timerBadge) {
                        timerBadge.className = 'badge bg-success ms-2';
                        timerBadge.textContent = '✓ تم التحديث';
                        setTimeout(() => updateSessionTimer(), 2000);
                    }
                }
            })
            .catch(error => {
                console.error('خطأ في تحديث CSRF token:', error);
                const timerBadge = document.getElementById('sessionTimer');
                if (timerBadge) {
                    timerBadge.className = 'badge bg-danger ms-2';
                    timerBadge.textContent = '⚠ خطأ في التحديث';
                }
            });
        }
        
        // تحديث التوكن كل 10 دقائق
        setInterval(refreshCSRFToken, refreshInterval);
        
        // عرض تحذير قبل مغادرة الصفحة إذا تم تعديل الفورم
        let formModified = false;
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', () => formModified = true);
        });
        
        window.addEventListener('beforeunload', function(e) {
            if (formModified) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        // AJAX form submission
        const $form = $('#generatorForm');
        const $submitBtn = $('#submitBtn');

        $form.on('submit', function(e) {
            e.preventDefault();
            formModified = false;

            // إزالة HTML5 validation constraints من الحقول في الـ tabs المخفية
            // لتجنب مشكلة "not focusable" عندما يكون الحقل لديه min/max لكنه مخفي
            const hiddenTabs = document.querySelectorAll('.tab-pane:not(.show)');
            const hiddenInputs = [];
            
            hiddenTabs.forEach(tab => {
                const inputs = tab.querySelectorAll('input[type="number"], input[type="text"], input[type="date"]');
                inputs.forEach(input => {
                    // حفظ القيود الحالية
                    const constraints = {
                        min: input.getAttribute('min'),
                        max: input.getAttribute('max'),
                        required: input.hasAttribute('required')
                    };
                    
                    if (constraints.min || constraints.max || constraints.required) {
                        hiddenInputs.push({ input: input, constraints: constraints });
                        
                        // إزالة القيود مؤقتاً
                        if (constraints.min) input.removeAttribute('min');
                        if (constraints.max) input.removeAttribute('max');
                        if (constraints.required) input.removeAttribute('required');
                    }
                });
            });

            // التحقق من صحة الفورم
            if (!$form[0].checkValidity()) {
                // استعادة القيود
                hiddenInputs.forEach(({ input, constraints }) => {
                    if (constraints.min) input.setAttribute('min', constraints.min);
                    if (constraints.max) input.setAttribute('max', constraints.max);
                    if (constraints.required) input.setAttribute('required', 'required');
                });
                $form[0].reportValidity();
                return false;
            }

            // استعادة القيود قبل الإرسال
            hiddenInputs.forEach(({ input, constraints }) => {
                if (constraints.min) input.setAttribute('min', constraints.min);
                if (constraints.max) input.setAttribute('max', constraints.max);
                if (constraints.required) input.setAttribute('required', 'required');
            });

            $submitBtn.prop('disabled', true);
            const originalText = $submitBtn.html();
            $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');

            const formData = new FormData(this);

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
                        if (typeof window.showToast === 'function') {
                            window.showToast(response.message || 'تم إنشاء المولد بنجاح', 'success');
                        } else {
                            alert(response.message || 'تم إنشاء المولد بنجاح');
                        }
                        setTimeout(function() {
                            window.location.href = '{{ route('admin.generators.index') }}';
                        }, 500);
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false);
                    $submitBtn.html(originalText);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        let errorMessages = [];
                        let firstError = '';

                        $form.find('.is-invalid').removeClass('is-invalid');
                        $form.find('.invalid-feedback').remove();

                        // خريطة أسماء الحقول العربية
                        const fieldLabels = {
                            'name': 'اسم المولد',
                            'generator_number': 'رقم المولد',
                            'operator_id': 'المشغل',
                            'generation_unit_id': 'وحدة التوليد',
                            'status': 'حالة المولد',
                            'description': 'الوصف',
                            'capacity_kva': 'قدرة المولد (KVA)',
                            'power_factor': 'معامل القدرة (P.F)',
                            'voltage': 'الجهد الناتج (V)',
                            'frequency': 'التردد (Hz)',
                            'engine_type': 'نوع المحرك',
                            'manufacturing_year': 'سنة التصنيع',
                            'injection_system': 'نظام الحقن',
                            'fuel_consumption_rate': 'معدل استهلاك الوقود',
                            'ideal_fuel_efficiency': 'كفاءة الوقود المثالية',
                            'internal_tank_capacity': 'سعة خزان الوقود الداخلي',
                            'measurement_indicator': 'مؤشر القياس',
                            'technical_condition': 'الحالة الفنية',
                            'last_major_maintenance_date': 'تاريخ آخر صيانة كبرى',
                            'engine_data_plate_image': 'صورة لوحة البيانات للمحرك',
                            'generator_data_plate_image': 'صورة لوحة البيانات للمولد',
                            'control_panel_available': 'لوحة التحكم',
                            'control_panel_type': 'نوع لوحة التحكم',
                            'control_panel_status': 'حالة لوحة التحكم',
                            'control_panel_image': 'صورة لوحة التحكم',
                            'operating_hours': 'قراءة ساعات التشغيل الحالية'
                        };

                        $.each(errors, function(field, messages) {
                            const errorMsg = Array.isArray(messages) ? messages[0] : messages;
                            const fieldLabel = fieldLabels[field] || field;
                            
                            if (!firstError) {
                                firstError = fieldLabel + ': ' + errorMsg;
                            }
                            
                            errorMessages.push(fieldLabel + ': ' + errorMsg);
                            
                            // معالجة الحقول
                            const $field = $form.find('[name="' + field + '"]');
                            if ($field.length) {
                                $field.addClass('is-invalid');
                                $field.closest('.col-md-6, .col-md-4, .col-md-12').find('.invalid-feedback').remove();
                                $field.after('<div class="invalid-feedback d-block">' + errorMsg + '</div>');
                            }
                        });

                        const $firstError = $form.find('.is-invalid').first();
                        if ($firstError.length) {
                            $('html, body').animate({
                                scrollTop: $firstError.offset().top - 100
                            }, 500);
                        }

                        // عرض جميع الأخطاء في إشعار أحمر
                        let errorMessage = 'يرجى تصحيح الأخطاء التالية:\n\n';
                        errorMessage += errorMessages.join('\n');
                        
                        if (typeof window.showToast === 'function') {
                            window.showToast(errorMessage, 'error', 'تحقق من الأخطاء');
                        } else if (window.adminNotifications && typeof window.adminNotifications.error === 'function') {
                            window.adminNotifications.error(errorMessage, 'تحقق من الأخطاء');
                        } else {
                            // Fallback: استخدام notify من الصفحة نفسها إذا كان متاحاً
                            console.error('Validation errors:', errorMessages);
                        }
                    } else {
                        const errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء حفظ البيانات';
                        if (typeof window.showToast === 'function') {
                            window.showToast(errorMsg, 'error');
                        } else if (window.adminNotifications && typeof window.adminNotifications.error === 'function') {
                            window.adminNotifications.error(errorMsg);
                        } else {
                            console.error('Error:', errorMsg);
                        }
                    }
                }
            });
        });
        
        // استعادة البيانات المحفوظة إن وُجدت
        const savedFormData = localStorage.getItem('generator_form_backup');
        if (savedFormData) {
            try {
                const data = JSON.parse(savedFormData);
                Object.keys(data).forEach(key => {
                    const field = document.querySelector(`[name="${key}"]`);
                    if (field && !field.value) {
                        field.value = data[key];
                    }
                });
                
                // عرض رسالة نجاح
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>تم استعادة البيانات بنجاح!</strong> البيانات التي أدخلتها سابقاً تم استعادتها.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('form').insertBefore(alert, document.querySelector('form').firstChild);
                
                // حذف البيانات المحفوظة
                localStorage.removeItem('generator_form_backup');
            } catch (e) {
                console.error('خطأ في استعادة البيانات:', e);
                localStorage.removeItem('generator_form_backup');
            }
        }
        
        // Tab Navigation Function
        window.navigateTabs = function(direction) {
            currentTab += direction;
            
            if (currentTab < 0) currentTab = 0;
            if (currentTab >= tabs.length) currentTab = tabs.length - 1;
            
            // Activate the tab
            const tabButton = document.getElementById(tabs[currentTab] + '-tab');
            if (tabButton) {
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
            }
            
            updateNavigationButtons();
        };
        
        // Update navigation buttons visibility
        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');
            
            prevBtn.style.display = currentTab === 0 ? 'none' : 'inline-block';
            nextBtn.style.display = currentTab === tabs.length - 1 ? 'none' : 'inline-block';
            submitBtn.style.display = currentTab === tabs.length - 1 ? 'inline-block' : 'none';
        }
        
        // Listen to tab changes
        const tabButtons = document.querySelectorAll('#generatorTabs button[data-bs-toggle="tab"]');
        tabButtons.forEach((tabButton, index) => {
            // Listen for Bootstrap tab shown event
            tabButton.addEventListener('shown.bs.tab', function() {
                currentTab = index;
                updateNavigationButtons();
            });
            
            // Also listen for click events as fallback to update currentTab
            tabButton.addEventListener('click', function() {
                // Find which tab this is based on data-bs-target
                const tabId = this.getAttribute('data-bs-target');
                if (tabId) {
                    const tabName = tabId.replace('#', '');
                    const tabIndex = tabs.indexOf(tabName);
                    if (tabIndex !== -1) {
                        currentTab = tabIndex;
                        // updateNavigationButtons will be called after tab is shown
                    }
                }
            });
        });
        
        updateNavigationButtons();
        
        // معالجة معاينة الصور
        document.querySelectorAll('.image-input').forEach(input => {
            input.addEventListener('change', function() {
                const previewId = this.getAttribute('data-preview');
                const previewContainer = document.getElementById(previewId);
                const file = this.files[0];
                
                if (file) {
                    // التحقق من نوع الملف
                    if (!file.type.startsWith('image/')) {
                        window.adminNotifications.error('يرجى اختيار ملف صورة صالح', 'خطأ');
                        this.value = '';
                        previewContainer.style.display = 'none';
                        return;
                    }
                    
                    // التحقق من حجم الملف (أقل من 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        window.adminNotifications.error('حجم الصورة كبير جداً. يرجى اختيار صورة أقل من 5 ميجابايت', 'خطأ');
                        this.value = '';
                        previewContainer.style.display = 'none';
                        return;
                    }
                    
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const img = previewContainer.querySelector('.image-preview');
                        img.src = e.target.result;
                        previewContainer.style.display = 'block';
                        
                        // إضافة تأثير fade-in
                        previewContainer.style.opacity = '0';
                        setTimeout(() => {
                            previewContainer.style.transition = 'opacity 0.3s ease';
                            previewContainer.style.opacity = '1';
                        }, 10);
                    };
                    
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.style.display = 'none';
                }
            });
        });
        
        // وظيفة إزالة الصورة
        window.removeImagePreview = function(inputName, previewId) {
            const input = document.querySelector(`input[name="${inputName}"]`);
            const previewContainer = document.getElementById(previewId);
            
            if (input) {
                input.value = '';
            }
            if (previewContainer) {
                // إضافة تأثير fade-out
                previewContainer.style.transition = 'opacity 0.3s ease';
                previewContainer.style.opacity = '0';
                
                setTimeout(() => {
                    previewContainer.style.display = 'none';
                    const img = previewContainer.querySelector('.image-preview');
                    if (img) {
                        img.src = '';
                    }
                    previewContainer.style.opacity = '1';
                }, 300);
            }
        };
        
        // تهيئة date picker لسنة التصنيع (سنوات فقط)
        const manufacturingYearInput = document.getElementById('manufacturing_year');
        if (manufacturingYearInput) {
            function initYearPicker() {
                if (typeof flatpickr === 'undefined') {
                    setTimeout(initYearPicker, 100);
                    return;
                }

                const currentYear = new Date().getFullYear();
                const defaultYear = manufacturingYearInput.value || null;

                flatpickr(manufacturingYearInput, {
                    dateFormat: 'Y',
                    mode: 'single',
                    minDate: '1900-01-01',
                    maxDate: new Date(),
                    locale: 'ar',
                    allowInput: true,
                    defaultDate: defaultYear ? defaultYear + '-01-01' : null,
                    onChange: function(selectedDates, dateStr, instance) {
                        // استخراج السنة فقط من التاريخ
                        if (dateStr) {
                            const year = parseInt(dateStr.split('-')[0]);
                            if (year >= 1900 && year <= currentYear) {
                                manufacturingYearInput.value = year;
                            } else {
                                manufacturingYearInput.value = '';
                            }
                        }
                    },
                    onReady: function(selectedDates, dateStr, instance) {
                        // جعل التقويم يظهر السنة فقط عند النقر
                        const monthNav = instance.calendarContainer.querySelector('.flatpickr-month');
                        if (monthNav) {
                            monthNav.style.cursor = 'pointer';
                        }
                    }
                });
            }
            initYearPicker();
        }

        // تحميل وحدات التوليد عند اختيار المشغل (للسوبر أدمن فقط)
        const operatorSelect = document.getElementById('operator_id');
        const generationUnitSelect = document.getElementById('generation_unit_id');
        const generationUnitHelp = document.getElementById('generation_unit_help');
        const generatorNumberInput = document.getElementById('generator_number');
        
        if (operatorSelect && generationUnitSelect) {
            operatorSelect.addEventListener('change', async function() {
                const operatorId = this.value;
                
                // إعادة تعيين حقل وحدة التوليد
                generationUnitSelect.innerHTML = '<option value="">اختر وحدة التوليد</option>';
                generationUnitSelect.disabled = !operatorId;
                generatorNumberInput.value = '';
                
                if (!operatorId) {
                    if (generationUnitHelp) {
                        generationUnitHelp.textContent = 'يجب اختيار المشغل أولاً لعرض وحدات التوليد';
                    }
                    return;
                }

                // تحميل وحدات التوليد
                try {
                    const response = await fetch(`/admin/operators/${operatorId}/generation-units`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (data.success && data.generation_units && data.generation_units.length > 0) {
                        // قراءة generation_unit_id من query parameter
                        const urlParams = new URLSearchParams(window.location.search);
                        const generationUnitIdFromUrl = urlParams.get('generation_unit_id');
                        
                        data.generation_units.forEach(unit => {
                            const option = document.createElement('option');
                            option.value = unit.id;
                            option.textContent = unit.label;
                            option.dataset.available = unit.available;
                            if (!unit.available) {
                                option.disabled = true;
                                option.textContent += ' (ممتلئة)';
                            }
                            // تحديد الوحدة إذا كانت في الـ URL
                            if (generationUnitIdFromUrl && generationUnitIdFromUrl == unit.id) {
                                option.selected = true;
                            }
                            generationUnitSelect.appendChild(option);
                        });
                        
                        // إذا تم تحديد وحدة من الـ URL، قم بتوليد رقم المولد
                        if (generationUnitIdFromUrl && generationUnitSelect.value == generationUnitIdFromUrl) {
                            generationUnitSelect.dispatchEvent(new Event('change'));
                        }
                        
                        if (generationUnitHelp) {
                            generationUnitHelp.textContent = 'اختر وحدة التوليد لإضافة المولد';
                        }
                    } else {
                        if (generationUnitHelp) {
                            generationUnitHelp.textContent = 'لا توجد وحدات توليد متاحة لهذا المشغل';
                        }
                    }
                } catch (error) {
                    console.error('Error loading generation units:', error);
                    if (generationUnitHelp) {
                        generationUnitHelp.textContent = 'حدث خطأ أثناء تحميل وحدات التوليد';
                    }
                }
            });

            // تحميل وحدات التوليد تلقائياً إذا كان المشغل محدد مسبقاً
            if (operatorSelect.value) {
                operatorSelect.dispatchEvent(new Event('change'));
            }
        }

        // توليد رقم المولد تلقائياً عند اختيار وحدة التوليد
        if (generationUnitSelect && generatorNumberInput) {
            generationUnitSelect.addEventListener('change', async function() {
                const generationUnitId = this.value;
                if (!generationUnitId) {
                    generatorNumberInput.value = '';
                    return;
                }

                // التحقق من أن الوحدة متاحة
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.dataset.available === 'false') {
                    generatorNumberInput.value = '';
                    alert('هذه الوحدة ممتلئة. يرجى اختيار وحدة أخرى.');
                    return;
                }

                try {
                    // استخدام route helper مع معامل ديناميكي
                    const baseUrl = '{{ url("/admin/generators/generate-number") }}';
                    const url = `${baseUrl}/${generationUnitId}`;
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (data.success && data.generator_number) {
                        generatorNumberInput.value = data.generator_number;
                    } else {
                        generatorNumberInput.value = '';
                        if (data.message) {
                            console.warn(data.message);
                        }
                    }
                } catch (error) {
                    console.error('Error generating generator number:', error);
                }
            });

            // توليد الرقم تلقائياً إذا كانت وحدة التوليد محددة مسبقاً (من old value أو URL)
            const urlParams = new URLSearchParams(window.location.search);
            const generationUnitIdFromUrl = urlParams.get('generation_unit_id');
            const generationUnitIdFromOld = @json(old('generation_unit_id'));
            
            if (generationUnitSelect.value || generationUnitIdFromUrl || generationUnitIdFromOld) {
                // إذا كانت القيمة من URL وليست محددة بعد، انتظر قليلاً ثم قم بالتحديد
                if (generationUnitIdFromUrl && !generationUnitSelect.value) {
                    setTimeout(() => {
                        if (generationUnitSelect.querySelector(`option[value="${generationUnitIdFromUrl}"]`)) {
                            generationUnitSelect.value = generationUnitIdFromUrl;
                            generationUnitSelect.dispatchEvent(new Event('change'));
                        }
                    }, 100);
                } else if (generationUnitSelect.value) {
                    generationUnitSelect.dispatchEvent(new Event('change'));
                }
            }
        }
        
        // اختيار وحدة التوليد تلقائياً من الـ URL وتوليد رقم المولد (للمشغلين الذين لا يستخدمون AJAX)
        @if(!auth()->user()->isSuperAdmin())
            const urlParamsForCompanyOwner = new URLSearchParams(window.location.search);
            const generationUnitIdFromUrl = urlParamsForCompanyOwner.get('generation_unit_id');
            const generationUnitSelectForCompanyOwner = document.getElementById('generation_unit_id');
            const generatorNumberInputForCompanyOwner = document.getElementById('generator_number');
            
            if (generationUnitIdFromUrl && generationUnitSelectForCompanyOwner) {
                // تحديد الوحدة إذا كانت موجودة في الـ select
                if (generationUnitSelectForCompanyOwner.querySelector(`option[value="${generationUnitIdFromUrl}"]`)) {
                    generationUnitSelectForCompanyOwner.value = generationUnitIdFromUrl;
                    // توليد رقم المولد تلقائياً
                    if (generationUnitSelectForCompanyOwner.value) {
                    generationUnitSelectForCompanyOwner.dispatchEvent(new Event('change'));
                }
                }
            } else if (generationUnitSelectForCompanyOwner && generationUnitSelectForCompanyOwner.value && generatorNumberInputForCompanyOwner && !generatorNumberInputForCompanyOwner.value) {
                // إذا كانت الوحدة محددة مسبقاً (من old value) ولم يتم توليد رقم المولد بعد
                generationUnitSelectForCompanyOwner.dispatchEvent(new Event('change'));
            }
        @endif
    });
</script>
@endpush







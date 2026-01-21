@extends('layouts.admin')

@section('title', 'إضافة وحدة توليد جديدة')

@php
    $breadcrumbTitle = 'إضافة وحدة توليد جديدة';
    $breadcrumbParent = 'وحدات التوليد';
    $breadcrumbParentUrl = route('admin.generation-units.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}" />
    <style>
        .territory-popup-own {
            border-left: 4px solid #28a745;
        }
        .territory-popup-other {
            border-left: 4px solid #dc3545;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 8px;
        }
        .leaflet-popup-content {
            margin: 15px;
        }
        #territoryLegend {
            font-size: 13px;
        }
    </style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card position-relative" id="generationUnitCard">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-lightning-charge me-2"></i>
                            إضافة وحدة توليد جديدة
                        </h5>
                        <div class="general-subtitle">
                            إدخال بيانات وحدة التوليد
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.generation-units.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-1"></i>
                            العودة
                        </a>
                        <button class="btn btn-primary" id="saveBtn" type="button">
                            <i class="bi bi-check-lg me-1"></i>
                            حفظ
                        </button>
                    </div>
                </div>

                <div class="card-body pb-4">
                    <form id="generationUnitForm" action="{{ route('admin.generation-units.store') }}" method="POST">
                        @csrf

                        {{-- فقط CompanyOwner يمكنه إضافة وحدات التوليد - المشغل تلقائي --}}
                        @if($operator)
                            <input type="hidden" name="operator_id" id="operator_id" value="{{ $operator->id }}">
                        @endif

                        <ul class="nav nav-pills mb-3" id="profileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-basic" type="button" role="tab">
                                    <i class="bi bi-info-circle me-1"></i> البيانات الأساسية
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-owner" type="button" role="tab">
                                    <i class="bi bi-person-badge me-1"></i> الملكية
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-location" type="button" role="tab">
                                    <i class="bi bi-geo-alt me-1"></i> الموقع
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-tech" type="button" role="tab">
                                    <i class="bi bi-lightning-charge me-1"></i> القدرات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-benef" type="button" role="tab">
                                    <i class="bi bi-people me-1"></i> المستفيدون
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-status" type="button" role="tab">
                                    <i class="bi bi-activity me-1"></i> الحالة
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-tanks" type="button" role="tab">
                                    <i class="bi bi-droplet me-1"></i> الخزانات
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-3">
                            {{-- TAB: BASIC INFO --}}
                            <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">رقم الوحدة</label>
                                        <input type="text" name="unit_number" id="unit_number" class="form-control @error('unit_number') is-invalid @enderror"
                                               value="{{ old('unit_number') }}"
                                               placeholder="سيتم توليده تلقائياً"
                                               maxlength="3"
                                               readonly
                                               style="background-color: #f8f9fa;">
                                        <div class="form-text">يتم توليده تلقائياً بناءً على المحافظة والمدينة</div>
                                        @error('unit_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">اسم وحدة التوليد <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name') }}"
                                               placeholder="مثال: وحدة التوليد الرئيسية" required>
                                        <div class="form-text">الاسم الرسمي لوحدة التوليد</div>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">عدد المولدات المطلوبة</label>
                                        <input type="number" name="generators_count" id="generators_count" class="form-control @error('generators_count') is-invalid @enderror"
                                               value="{{ old('generators_count', 1) }}" min="1" max="99">
                                        <div class="form-text">عدد المولدات التي يجب أن تكون في هذه الوحدة (يمكن تحديده لاحقاً)</div>
                                        @error('generators_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>يمكنك إدخال الحد الأدنى من البيانات الآن:</strong> اسم الوحدة، المحافظة، المدينة، العنوان التفصيلي، والإحداثيات. باقي البيانات يمكن ملؤها لاحقاً عند التعديل.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: OWNER --}}
                            <div class="tab-pane fade" id="tab-owner" role="tabpanel">
                                <div class="row g-3">
                                    {{-- جهة التشغيل (أول حقل وإجباري) --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">جهة التشغيل <span class="text-danger">*</span></label>
                                        <select name="operation_entity_id" id="operation_entity_id" class="form-select @error('operation_entity_id') is-invalid @enderror" required>
                                            <option value="">اختر جهة التشغيل</option>
                                            @foreach($constants['operation_entity'] as $entity)
                                                <option value="{{ $entity->id }}" data-code="{{ $entity->code }}" {{ old('operation_entity_id') == $entity->id ? 'selected' : '' }}>
                                                    {{ $entity->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('operation_entity_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- اسم المالك --}}
                                    <div class="col-md-4" id="owner_name_wrapper">
                                        <label class="form-label fw-semibold">اسم المالك</label>
                                        <input type="text" name="owner_name" id="owner_name" class="form-control @error('owner_name') is-invalid @enderror"
                                               value="{{ old('owner_name') }}">
                                        @error('owner_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- رقم هوية المالك --}}
                                    <div class="col-md-4" id="owner_id_number_wrapper">
                                        <label class="form-label fw-semibold">رقم هوية المالك</label>
                                        <input type="text" name="owner_id_number" id="owner_id_number" class="form-control @error('owner_id_number') is-invalid @enderror"
                                               value="{{ old('owner_id_number') }}">
                                        @error('owner_id_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- اسم المشغل (يظهر فقط في حالة "طرف آخر" ومطلوب) --}}
                                    <div class="col-md-4" id="operator_name_wrapper" style="display: none;">
                                        <label class="form-label fw-semibold">اسم المشغل <span class="text-danger">*</span></label>
                                        <input type="text" name="operator_name" id="operator_name" class="form-control @error('operator_name') is-invalid @enderror"
                                               value="{{ old('operator_name') }}">
                                        @error('operator_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- رقم هوية المشغل (مطلوب في حالة "طرف آخر") --}}
                                    <div class="col-md-4" id="operator_id_number_wrapper">
                                        <label class="form-label fw-semibold" id="operator_id_number_label">رقم هوية المشغل</label>
                                        <input type="text" name="operator_id_number" id="operator_id_number" class="form-control @error('operator_id_number') is-invalid @enderror"
                                               value="{{ old('operator_id_number') }}" maxlength="9" pattern="[0-9]{9}">
                                        @error('operator_id_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- رقم الموبايل --}}
                                    <div class="col-md-4" id="phone_wrapper">
                                        <label class="form-label fw-semibold">رقم الموبايل</label>
                                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                               value="{{ old('phone') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">رقم بديل</label>
                                        <input type="text" name="phone_alt" class="form-control @error('phone_alt') is-invalid @enderror"
                                               value="{{ old('phone_alt') }}">
                                        @error('phone_alt')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">البريد الإلكتروني</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email') }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: LOCATION --}}
                            <div class="tab-pane fade" id="tab-location" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">المحافظة <span class="text-danger">*</span></label>
                                        <select name="governorate_id" id="governorate" class="form-select @error('governorate_id') is-invalid @enderror" required>
                                            <option value="">اختر</option>
                                            @forelse($governorates as $gov)
                                                <option value="{{ $gov->id }}"
                                                    data-governorate-code="{{ $gov->code }}"
                                                    {{ old('governorate_id', $selectedGovernorateId) == $gov->id ? 'selected' : '' }}>
                                                    {{ $gov->label }} ({{ $gov->code }})
                                                </option>
                                            @empty
                                                <option value="" disabled>لا توجد محافظات متاحة</option>
                                            @endforelse
                                        </select>
                                        @if($governorates->isEmpty())
                                            <div class="form-text text-danger">تحذير: لا توجد محافظات في الثوابت. يرجى تشغيل ConstantSeeder.</div>
                                        @endif
                                        @error('governorate_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">المدينة <span class="text-danger">*</span></label>
                                        <select name="city_id" id="city_id" class="form-select @error('city_id') is-invalid @enderror" required>
                                            <option value="">اختر المدينة</option>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->id }}"
                                                    {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                                    {{ $city->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('city_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- العنوان التفصيلي، الإحداثيات، ونصف القطر في صف واحد --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">العنوان التفصيلي <span class="text-danger">*</span></label>
                                        <input type="text" name="detailed_address" class="form-control @error('detailed_address') is-invalid @enderror"
                                               value="{{ old('detailed_address') }}" required>
                                        @error('detailed_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Latitude <span class="text-danger">*</span></label>
                                        <input type="number" step="0.00000001" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                               value="{{ old('latitude') }}" readonly required>
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Longitude <span class="text-danger">*</span></label>
                                        <input type="number" step="0.00000001" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                               value="{{ old('longitude') }}" readonly required>
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-tag me-1"></i>
                                            اسم المنطقة <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="territory_name" id="territory_name" 
                                               class="form-control @error('territory_name') is-invalid @enderror"
                                               value="{{ old('territory_name') }}" 
                                               placeholder="مثال: منطقة الشمال"
                                               maxlength="255" required>
                                        @error('territory_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            أدخل اسماً مميزاً للمنطقة الجغرافية التي تريد حجزها.
                                        </small>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-rulers me-1"></i>
                                            مساحة المنطقة (كم²) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="0.1" name="territory_area_km2" id="territory_area_km2" 
                                               class="form-control @error('territory_area_km2') is-invalid @enderror"
                                               value="{{ old('territory_area_km2', 5) }}" 
                                               min="0.1" max="360" required>
                                        @error('territory_area_km2')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            حدد المساحة الجغرافية التي تريد حجزها (0.1 - 360 كم²). مساحة قطاع غزة الكلية: 360 كم².
                                        </small>
                                        <input type="hidden" name="territory_radius_km" id="territory_radius_km" value="">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">تحديد الموقع على الخريطة <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-2 mb-2 flex-wrap align-items-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="loadTerritoriesBtn">
                                                <i class="bi bi-map me-1"></i>
                                                عرض المناطق الجغرافية
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearTerritoriesBtn" style="display: none;">
                                                <i class="bi bi-x-circle me-1"></i>
                                                إخفاء المناطق
                                            </button>
                                            <div id="territoryLegend" class="ms-auto d-none" style="background: white; padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                                <div class="d-flex gap-3 align-items-center flex-wrap">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div style="width: 20px; height: 20px; background: #28a745; border: 2px solid #28a745; border-radius: 50%;"></div>
                                                        <small class="fw-semibold">مناطقك</small>
                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div style="width: 20px; height: 20px; background: #dc3545; border: 2px solid #dc3545; border-radius: 50%;"></div>
                                                        <small class="fw-semibold">محجوزة لمشغل آخر</small>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div style="width: 20px; height: 20px; background: #ffc107; border: 2px solid #ffc107; border-radius: 50%;"></div>
                                                        <small class="fw-semibold">موقعك المحدد</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="territoryAlert" class="alert d-none mb-2"></div>
                                        <div id="map" class="op-map" style="position: relative;"></div>
                                        <div class="form-text mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            اضغط على الخريطة لتحديد الموقع. سيتم التحقق تلقائياً من توفر الموقع. المناطق الخضراء مملوكة لك، والحمراء محجوزة لمشغلين آخرين.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: TECH --}}
                            <div class="tab-pane fade" id="tab-tech" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">إجمالي القدرة (KVA) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="total_capacity" class="form-control @error('total_capacity') is-invalid @enderror"
                                               value="{{ old('total_capacity') }}" required min="0.01">
                                        @error('total_capacity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">مزامنة المولدات</label>
                                        <select name="synchronization_available_id" id="synchronization_available_id" class="form-select @error('synchronization_available_id') is-invalid @enderror">
                                            <option value="">اختر</option>
                                            @foreach($constants['synchronization_available'] as $sync)
                                                <option value="{{ $sync->id }}" 
                                                    data-code="{{ $sync->code }}"
                                                    {{ old('synchronization_available_id') == $sync->id ? 'selected' : '' }}>
                                                    {{ $sync->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('synchronization_available_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6" id="max_sync_capacity_wrapper" style="display: none;">
                                        <label class="form-label fw-semibold">قدرة المزامنة القصوى (KVA) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="max_synchronization_capacity" id="max_synchronization_capacity" class="form-control @error('max_synchronization_capacity') is-invalid @enderror" min="0.01"
                                               value="{{ old('max_synchronization_capacity') }}">
                                        @error('max_synchronization_capacity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: BENEF --}}
                            <div class="tab-pane fade" id="tab-benef" role="tabpanel">
                                <div class="row g-3">
                                    @php
                                        $isCompanyOwner = auth()->user()->isCompanyOwner();
                                        // جلب قيمة "تحت التقييم" للمشغل
                                        $underEvaluationId = null;
                                        if ($isCompanyOwner) {
                                            $underEvaluation = \App\Helpers\ConstantsHelper::findByCode(14, 'UNDER_EVALUATION');
                                            $underEvaluationId = $underEvaluation ? $underEvaluation->id : null;
                                        }
                                    @endphp
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">عدد المستفيدين</label>
                                        <input type="number" name="beneficiaries_count" class="form-control @error('beneficiaries_count') is-invalid @enderror" min="0"
                                               value="{{ old('beneficiaries_count') }}">
                                        @error('beneficiaries_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">الامتثال البيئي</label>
                                        <select name="environmental_compliance_status_id" class="form-select @error('environmental_compliance_status_id') is-invalid @enderror"
                                                {{ $isCompanyOwner ? 'disabled' : '' }}
                                                style="{{ $isCompanyOwner ? 'background-color: #e9ecef;' : '' }}">
                                            <option value="">اختر</option>
                                            @foreach($constants['environmental_compliance_status'] as $compliance)
                                                <option value="{{ $compliance->id }}" 
                                                    {{ ($isCompanyOwner && $compliance->code === 'UNDER_EVALUATION') || old('environmental_compliance_status_id') == $compliance->id ? 'selected' : '' }}>
                                                    {{ $compliance->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($isCompanyOwner && $underEvaluationId)
                                            <input type="hidden" name="environmental_compliance_status_id" value="{{ $underEvaluationId }}">
                                        @endif
                                        @error('environmental_compliance_status_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">وصف المستفيدين</label>
                                        <textarea name="beneficiaries_description" class="form-control @error('beneficiaries_description') is-invalid @enderror" rows="3">{{ old('beneficiaries_description') }}</textarea>
                                        @error('beneficiaries_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: STATUS --}}
                            <div class="tab-pane fade" id="tab-status" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">حالة الوحدة</label>
                                        <select name="status_id" class="form-select @error('status_id') is-invalid @enderror">
                                            <option value="">اختر</option>
                                            @foreach($constants['status'] as $status)
                                                <option value="{{ $status->id }}" {{ old('status_id', $constants['status']->firstWhere('code', 'ACTIVE')?->id) == $status->id ? 'selected' : '' }}>
                                                    {{ $status->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text">سيتم تعيين حالة "فعال" تلقائياً إذا لم يتم الاختيار</div>
                                        @error('status_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: TANKS --}}
                            <div class="tab-pane fade" id="tab-tanks" role="tabpanel">
                                <div class="mb-4">
                                    <!-- خزان وقود خارجي -->
                                    <div class="card mb-4 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">خزان وقود خارجي <span class="text-danger">*</span></label>
                                                    <select name="external_fuel_tank" id="external_fuel_tank" class="form-select @error('external_fuel_tank') is-invalid @enderror">
                                                        <option value="0" {{ old('external_fuel_tank', '0') == '0' ? 'selected' : '' }}>لا</option>
                                                        <option value="1" {{ old('external_fuel_tank') == '1' ? 'selected' : '' }}>نعم</option>
                                                    </select>
                                                    @error('external_fuel_tank')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6" id="fuel_tanks_count_wrapper" style="display: none;">
                                                    <label class="form-label fw-semibold">عدد خزانات الوقود (1-10) <span class="text-danger">*</span></label>
                                                    <select name="fuel_tanks_count" id="fuel_tanks_count" class="form-select @error('fuel_tanks_count') is-invalid @enderror">
                                                        <option value="0">اختر العدد</option>
                                                        @for($i = 1; $i <= 10; $i++)
                                                            <option value="{{ $i }}" {{ old('fuel_tanks_count') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                    @error('fuel_tanks_count')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <!-- حقل hidden لإرسال القيمة الافتراضية عندما يكون external_fuel_tank = 0 -->
                                                <input type="hidden" id="fuel_tanks_count_hidden" name="fuel_tanks_count" value="{{ old('fuel_tanks_count', '0') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- خزانات الوقود الديناميكية -->
                                    <div id="fuel_tanks_container"></div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="d-none" id="hiddenSubmitBtn"></button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('assets/admin/js/general-helpers.js') }}"></script>
<script>
(function () {
    function notify(type, msg, title) {
        if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
            window.adminNotifications[type](msg, title);
            return;
        }
        alert(msg);
    }

    const form = document.getElementById('generationUnitForm');
    const saveBtn = document.getElementById('saveBtn');

    /**
     * Set loading state on save button
     * @param {boolean} on - Whether loading is active
     */
    function setLoading(on) {
        saveBtn.disabled = on;
        if (on) {
            // Add spinner to button
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>جاري الحفظ...';
        } else {
            // Restore original button content
            saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>حفظ';
        }
    }

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    }

    // دالة لترجمة رسائل الخطأ من الإنجليزية للعربية
    function translateErrorMessage(errorMsg) {
        let translated = errorMsg;
        
        // معالجة الرسائل التي تحتوي على "The ... field"
        // مثل: "The fuel_tanks.0.capacity field must not be greater than 10000."
        const fieldPattern = /The\s+([^\s]+)\s+field\s+(.+)/i;
        const match = translated.match(fieldPattern);
        
        if (match) {
            const fieldName = match[1];
            const restOfMessage = match[2];
            
            // ترجمة اسم الحقل
            let translatedFieldName = fieldName;
            const fieldMatch = fieldName.match(/fuel_tanks\.(\d+)\.(\w+)/);
            if (fieldMatch) {
                const tankIndex = parseInt(fieldMatch[1]) + 1; // +1 لأن الفهرس يبدأ من 0
                const subField = fieldMatch[2];
                if (subField === 'capacity') {
                    translatedFieldName = `سعة الخزان ${tankIndex}`;
                } else if (subField === 'location_id') {
                    translatedFieldName = `موقع الخزان ${tankIndex}`;
                } else {
                    translatedFieldName = `خزان ${tankIndex} - ${subField}`;
                }
            }
            
            // ترجمة باقي الرسالة
            let translatedRest = restOfMessage;
            const messageTranslations = {
                'must not be greater than': 'يجب ألا يتجاوز',
                'must be at least': 'يجب أن يكون على الأقل',
                'is required': 'مطلوب',
                'must be a number': 'يجب أن يكون رقماً',
                'must be an integer': 'يجب أن يكون رقماً صحيحاً',
                'must be a valid email': 'يجب أن يكون بريد إلكتروني صحيح',
            };
            
            Object.keys(messageTranslations).forEach(key => {
                if (translatedRest.includes(key)) {
                    translatedRest = translatedRest.replace(key, messageTranslations[key]);
                }
            });
            
            // بناء الرسالة المترجمة بشكل مفهوم
            translated = `${translatedFieldName}: ${translatedRest}`;
        } else {
            // إذا لم تكن الرسالة بتنسيق "The ... field"، ترجمها بشكل عام
            const translations = {
                'must not be greater than': 'يجب ألا يتجاوز',
                'must be at least': 'يجب أن يكون على الأقل',
                'is required': 'مطلوب',
                'must be a number': 'يجب أن يكون رقماً',
                'must be an integer': 'يجب أن يكون رقماً صحيحاً',
                'must be a valid email': 'يجب أن يكون بريد إلكتروني صحيح',
            };
            
            Object.keys(translations).forEach(key => {
                if (translated.includes(key)) {
                    translated = translated.replace(key, translations[key]);
                }
            });
            
            // ترجمة أسماء الحقول في الرسالة
            translated = translated.replace(/fuel_tanks\.(\d+)\.capacity/gi, (match, index) => {
                return `سعة الخزان ${parseInt(index) + 1}`;
            });
            translated = translated.replace(/fuel_tanks\.(\d+)\.location_id/gi, (match, index) => {
                return `موقع الخزان ${parseInt(index) + 1}`;
            });
        }
        
        // إزالة أي "field" متبقية
        translated = translated.replace(/\s+field\s+/gi, ' ');
        translated = translated.replace(/The\s+/gi, '');
        
        return translated;
    }

    function showErrors(errors) {
        // خريطة أسماء الحقول العربية
        const fieldLabels = {
            'name': 'اسم وحدة التوليد',
            'operator_id': 'المشغل',
            'unit_number': 'رقم الوحدة',
            'unit_code': 'كود الوحدة',
            'generators_count': 'عدد المولدات',
            'status': 'الحالة',
            'owner_name': 'اسم المالك',
            'owner_id_number': 'رقم هوية المالك',
            'operation_entity_id': 'جهة التشغيل',
            'operation_entity': 'كيان التشغيل',
            'operator_id_number': 'رقم هوية المشغل',
            'phone': 'رقم الهاتف',
            'phone_alt': 'رقم الهاتف البديل',
            'email': 'البريد الإلكتروني',
            'governorate_id': 'المحافظة',
            'governorate': 'المحافظة',
            'city_id': 'المدينة',
            'detailed_address': 'العنوان التفصيلي',
            'latitude': 'خط العرض',
            'longitude': 'خط الطول',
            'total_capacity': 'السعة الإجمالية',
            'synchronization_available_id': 'مزامنة المولدات',
            'synchronization_available': 'التزامن متاح',
            'max_synchronization_capacity': 'السعة القصوى للتزامن',
            'beneficiaries_count': 'عدد المستفيدين',
            'beneficiaries_description': 'وصف المستفيدين',
            'environmental_compliance_status_id': 'الامتثال البيئي',
            'environmental_compliance_status': 'حالة الامتثال البيئي',
            'status_id': 'حالة الوحدة',
            'external_fuel_tank': 'خزان وقود خارجي',
            'fuel_tanks_count': 'عدد خزانات الوقود',
            'fuel_tanks': 'خزانات الوقود'
        };

        // ترتيب أولويات الحقول حسب التابات (من الأول للأخير)
        const fieldOrder = [
            // tab-basic: البيانات الأساسية
            'operator_id', 'name', 'generators_count',
            // tab-owner: الملكية
            'operation_entity_id', 'owner_name', 'owner_id_number', 'operator_id_number', 'phone', 'phone_alt', 'email',
            // tab-location: الموقع
            'governorate_id', 'city_id', 'detailed_address', 'latitude', 'longitude',
            // tab-tech: القدرات
            'total_capacity', 'synchronization_available_id', 'max_synchronization_capacity',
            // tab-benef: المستفيدون
            'beneficiaries_count', 'beneficiaries_description', 'environmental_compliance_status_id',
            // tab-status: الحالة
            'status_id',
            // tab-tanks: الخزانات
            'external_fuel_tank', 'fuel_tanks_count', 'fuel_tanks'
        ];

        // إيجاد أول حقل خطأ حسب ترتيب الأولويات
        let firstField = null;
        for (const field of fieldOrder) {
            if (errors[field]) {
                firstField = field;
                break;
            }
        }
        
        // إذا لم يتم العثور على الحقل في الترتيب المحدد، استخدم أول حقل في الأخطاء
        if (!firstField && Object.keys(errors).length > 0) {
            firstField = Object.keys(errors)[0];
        }

        const errorMessages = [];
        
        // جمع جميع رسائل الأخطاء حسب ترتيب الأولويات
        for (const field of fieldOrder) {
            if (errors[field]) {
                let errorMsg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                errorMsg = translateErrorMessage(errorMsg);
                const fieldLabel = fieldLabels[field] || field;
                errorMessages.push(fieldLabel + ': ' + errorMsg);
            }
        }
        
        // إضافة أي حقول خطأ أخرى غير موجودة في fieldOrder
        Object.keys(errors || {}).forEach(field => {
            if (!fieldOrder.includes(field)) {
                let errorMsg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                errorMsg = translateErrorMessage(errorMsg);
                
                // معالجة الحقول المدمجة مثل fuel_tanks.0.capacity
                let fieldLabel = fieldLabels[field];
                if (!fieldLabel) {
                    // محاولة استخراج اسم الحقل من fuel_tanks.0.capacity
                    const match = field.match(/fuel_tanks\.(\d+)\.(\w+)/);
                    if (match) {
                        const tankIndex = parseInt(match[1]) + 1; // +1 لأن الفهرس يبدأ من 0
                        const subField = match[2];
                        if (subField === 'capacity') {
                            fieldLabel = `سعة الخزان ${tankIndex}`;
                        } else if (subField === 'location_id') {
                            fieldLabel = `موقع الخزان ${tankIndex}`;
                        } else {
                            fieldLabel = `خزان ${tankIndex} - ${subField}`;
                        }
                    } else {
                        fieldLabel = field;
                    }
                }
                
                errorMessages.push(fieldLabel + ': ' + errorMsg);
            }
        });

        // عرض جميع رسائل الأخطاء في إشعار أحمر
        if (errorMessages.length > 0) {
            let errorMessage = 'يرجى تصحيح الأخطاء التالية:\n\n';
            errorMessage += errorMessages.join('\n');
            notify('error', errorMessage, 'تحقق من الأخطاء');
        }

        // عرض أول حقل خطأ وفتح التاب المناسب
        if (firstField) {
            const originalField = firstField;
            // معالجة الحقول المدمجة مثل fuel_tanks.0.capacity
            let fieldSelector = firstField;
            let isFuelTankField = false;
            const match = firstField.match(/fuel_tanks\.(\d+)\.(\w+)/);
            if (match) {
                const index = match[1];
                const subField = match[2];
                fieldSelector = `fuel_tanks[${index}][${subField}]`;
                isFuelTankField = true;
            }
            
            let input = form.querySelector(`[name="${CSS.escape(fieldSelector)}"]`);
            
            // إذا لم نجد الحقل، جرب البحث بدون escape للـ brackets
            if (!input) {
                const alternativeSelector = fieldSelector.replace(/\[/g, '\\[').replace(/\]/g, '\\]');
                input = form.querySelector(`[name="${alternativeSelector}"]`);
            }
            
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                let errorMsg = Array.isArray(errors[originalField]) ? errors[originalField][0] : errors[originalField];
                div.textContent = translateErrorMessage(errorMsg);
                input.insertAdjacentElement('afterend', div);

                // افتح التاب اللي فيه الحقل
                // إذا كان خطأ في fuel_tanks، افتح تاب الخزانات
                const pane = input.closest('.tab-pane') || (isFuelTankField ? document.getElementById('tab-tanks') : null);
                if (pane && pane.id) {
                    const tabBtn = document.querySelector(`[data-bs-target="#${pane.id}"]`);
                    if (tabBtn) new bootstrap.Tab(tabBtn).show();
                }

                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else if (isFuelTankField) {
                // إذا لم نجد الحقل لكن الخطأ في fuel_tanks، افتح تاب الخزانات على أي حال
                const tanksTab = document.getElementById('tab-tanks');
                if (tanksTab) {
                    const tabBtn = document.querySelector('[data-bs-target="#tab-tanks"]');
                    if (tabBtn) new bootstrap.Tab(tabBtn).show();
                }
            }
        }

        // عرض جميع الأخطاء على الحقول
        Object.keys(errors || {}).forEach(field => {
            // معالجة الحقول المدمجة مثل fuel_tanks.0.capacity -> fuel_tanks[0][capacity]
            let fieldSelector = field;
            const match = field.match(/fuel_tanks\.(\d+)\.(\w+)/);
            if (match) {
                const index = match[1];
                const subField = match[2];
                fieldSelector = `fuel_tanks[${index}][${subField}]`;
            }
            
            const input = form.querySelector(`[name="${CSS.escape(fieldSelector)}"]`);
            if (!input) {
                // إذا لم نجد الحقل، جرب البحث بدون escape للـ brackets
                const alternativeSelector = fieldSelector.replace(/\[/g, '\\[').replace(/\]/g, '\\]');
                const input2 = form.querySelector(`[name="${alternativeSelector}"]`);
                if (input2) {
                    input2.classList.add('is-invalid');
                    if (input2.nextElementSibling && input2.nextElementSibling.classList.contains('invalid-feedback')) return;
                    const div = document.createElement('div');
                    div.className = 'invalid-feedback';
                    let errorMsg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                    div.textContent = translateErrorMessage(errorMsg);
                    input2.insertAdjacentElement('afterend', div);
                }
                return;
            }
            
            input.classList.add('is-invalid');
            if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) return;
            const div = document.createElement('div');
            div.className = 'invalid-feedback';
            let errorMsg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            div.textContent = translateErrorMessage(errorMsg);
            input.insertAdjacentElement('afterend', div);
        });
    }

    // ====== جلب بيانات المشغل تلقائياً عند اختيار "نفس المالك" ======
    const operationEntitySelect = document.getElementById('operation_entity_id');
    const operatorIdSelect = document.getElementById('operator_id');
    const ownerNameInput = document.getElementById('owner_name');
    const ownerIdNumberInput = document.getElementById('owner_id_number');
    const operatorNameInput = document.getElementById('operator_name');
    const operatorIdNumberInput = document.getElementById('operator_id_number');
    const phoneInput = document.getElementById('phone');

    // Wrappers للتحكم في الإظهار/الإخفاء
    const ownerNameWrapper = document.getElementById('owner_name_wrapper');
    const ownerIdNumberWrapper = document.getElementById('owner_id_number_wrapper');
    const operatorNameWrapper = document.getElementById('operator_name_wrapper');
    const operatorIdNumberWrapper = document.getElementById('operator_id_number_wrapper');
    const operatorIdNumberLabel = document.getElementById('operator_id_number_label');
    const phoneWrapper = document.getElementById('phone_wrapper');

    /**
     * جلب بيانات المشغل الذي يتبعه المستخدم (صاحب المشغل أو الموظف التابع له)
     * @param {string} operatorId - ID المشغل (من hidden input - المشغل الذي يتبعه المستخدم)
     * @param {boolean} isSameOwner - true إذا كان "نفس المالك"، false إذا كان "طرف آخر"
     */
    function loadOperatorData(operatorId, isSameOwner = false) {
        if (!operatorId) return;

        // جلب بيانات المشغل الذي يتبعه المستخدم (CompanyOwner أو Employee/Technician)
        fetch(`{{ url('admin/operators') }}/${operatorId}/data`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.operator) {
                // ملء بيانات المالك (من المشغل الذي يتبعه المستخدم)
                if (ownerNameInput) ownerNameInput.value = data.operator.owner_name || '';
                if (ownerIdNumberInput) ownerIdNumberInput.value = data.operator.owner_id_number || '';
                if (phoneInput) phoneInput.value = data.operator.phone || '';
                // في حالة "نفس المالك" فقط، رقم هوية المشغل = رقم هوية المالك
                if (isSameOwner && operatorIdNumberInput) {
                    operatorIdNumberInput.value = data.operator.owner_id_number || '';
                }
            }
        })
        .catch(err => {
            console.error('Error loading operator data:', err);
        });
    }

    function toggleFieldsForSameOwner() {
        // إظهار: اسم المالك، رقم هوية المالك، رقم الجوال
        if (ownerNameWrapper) ownerNameWrapper.style.display = 'block';
        if (ownerIdNumberWrapper) ownerIdNumberWrapper.style.display = 'block';
        if (phoneWrapper) phoneWrapper.style.display = 'block';
        
        // إخفاء: اسم المشغل، رقم هوية المشغل
        if (operatorNameWrapper) operatorNameWrapper.style.display = 'none';
        if (operatorIdNumberWrapper) operatorIdNumberWrapper.style.display = 'none';
        
        // جعل الحقول للقراءة فقط وإزالة required
        if (ownerNameInput) {
            ownerNameInput.readOnly = true;
            ownerNameInput.style.backgroundColor = '#f8f9fa';
        }
        if (ownerIdNumberInput) {
            ownerIdNumberInput.readOnly = true;
            ownerIdNumberInput.style.backgroundColor = '#f8f9fa';
        }
        if (phoneInput) {
            phoneInput.readOnly = true;
            phoneInput.style.backgroundColor = '#f8f9fa';
        }
        
        // إزالة required من حقول المشغل (لأنها مخفية)
        if (operatorNameInput) {
            operatorNameInput.removeAttribute('required');
        }
        if (operatorIdNumberInput) {
            operatorIdNumberInput.removeAttribute('required');
        }
        // إزالة النجمة (*) من label رقم هوية المشغل
        if (operatorIdNumberLabel) {
            operatorIdNumberLabel.innerHTML = operatorIdNumberLabel.innerHTML.replace(/<span class="text-danger">\*<\/span>/g, '').trim();
        }
    }

    function toggleFieldsForOtherParty() {
        // إظهار: اسم المالك (read-only)، رقم هوية المالك (read-only)، اسم المشغل (editable + required)، رقم هوية المشغل (editable + required)، رقم الجوال (editable)
        if (ownerNameWrapper) ownerNameWrapper.style.display = 'block';
        if (ownerIdNumberWrapper) ownerIdNumberWrapper.style.display = 'block';
        if (operatorNameWrapper) operatorNameWrapper.style.display = 'block';
        if (operatorIdNumberWrapper) operatorIdNumberWrapper.style.display = 'block';
        if (phoneWrapper) phoneWrapper.style.display = 'block';
        
        // جعل بيانات المالك للقراءة فقط
        if (ownerNameInput) {
            ownerNameInput.readOnly = true;
            ownerNameInput.style.backgroundColor = '#f8f9fa';
        }
        if (ownerIdNumberInput) {
            ownerIdNumberInput.readOnly = true;
            ownerIdNumberInput.style.backgroundColor = '#f8f9fa';
        }
        
        // جعل بيانات المشغل ورقم الجوال قابلة للتحرير ومطلوبة
        if (operatorNameInput) {
            operatorNameInput.readOnly = false;
            operatorNameInput.style.backgroundColor = '';
            operatorNameInput.setAttribute('required', 'required');
            operatorNameInput.value = '';
        }
        if (operatorIdNumberInput) {
            operatorIdNumberInput.readOnly = false;
            operatorIdNumberInput.style.backgroundColor = '';
            operatorIdNumberInput.setAttribute('required', 'required');
            operatorIdNumberInput.value = '';
        }
        // إضافة نجمة (*) على label رقم هوية المشغل
        if (operatorIdNumberLabel) {
            if (!operatorIdNumberLabel.innerHTML.includes('<span class="text-danger">*</span>')) {
                operatorIdNumberLabel.innerHTML = 'رقم هوية المشغل <span class="text-danger">*</span>';
            }
        }
        if (phoneInput) {
            phoneInput.readOnly = false;
            phoneInput.style.backgroundColor = '';
            phoneInput.value = '';
        }
    }

    operationEntitySelect?.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const code = selectedOption ? selectedOption.getAttribute('data-code') : '';
        const isSameOwner = code === 'SAME_OWNER';

        if (isSameOwner) {
            // إذا كان "نفس المالك": جلب بيانات المشغل الذي يتبعه المستخدم (CompanyOwner أو Employee/Technician)
            // البيانات ستكون: اسم المالك، رقم هوية المالك، رقم الجوال، ورقم هوية المشغل = رقم هوية المالك
            const operatorId = operatorIdSelect ? operatorIdSelect.value : 
                              (form.querySelector('input[name="operator_id"]') ? form.querySelector('input[name="operator_id"]').value : null);
            if (operatorId) {
                loadOperatorData(operatorId, true); // true = isSameOwner
            }
            toggleFieldsForSameOwner();
        } else {
            // إذا كان "طرف آخر": جلب بيانات المالك فقط من المشغل الذي يتبعه المستخدم (للإظهار كـ read-only)
            // المستخدم يجب أن يدخل يدوياً: اسم المشغل ورقم هوية المشغل
            const operatorId = operatorIdSelect ? operatorIdSelect.value : 
                              (form.querySelector('input[name="operator_id"]') ? form.querySelector('input[name="operator_id"]').value : null);
            if (operatorId) {
                loadOperatorData(operatorId, false); // false = not same owner
            }
            toggleFieldsForOtherParty();
        }
    });

    // عند تغيير المشغل (للسوبر أدمن فقط)
    operatorIdSelect?.addEventListener('change', function() {
        if (operationEntitySelect) {
            const selectedOption = operationEntitySelect.options[operationEntitySelect.selectedIndex];
            const code = selectedOption ? selectedOption.getAttribute('data-code') : '';
            if (code === 'SAME_OWNER') {
                loadOperatorData(this.value);
                toggleFieldsForSameOwner();
            } else {
                loadOperatorData(this.value);
                toggleFieldsForOtherParty();
            }
        }
    });

    // ====== التحكم في حقل قدرة المزامنة القصوى بناءً على اختيار مزامنة المولدات ======
    (function() {
        const syncSelect = document.getElementById('synchronization_available_id');
        const maxSyncWrapper = document.getElementById('max_sync_capacity_wrapper');
        const maxSyncInput = document.getElementById('max_synchronization_capacity');

        function toggleMaxSyncCapacity() {
            if (!syncSelect || !maxSyncWrapper || !maxSyncInput) return;

            const selectedOption = syncSelect.options[syncSelect.selectedIndex];
            const syncCode = selectedOption ? selectedOption.getAttribute('data-code') : null;

            if (syncCode === 'AVAILABLE') {
                // إذا اختار "متوفرة" - إظهار الحقل وجعله مطلوب
                maxSyncWrapper.style.display = 'block';
                maxSyncInput.setAttribute('required', 'required');
                maxSyncInput.setAttribute('min', '0.01');
            } else {
                // إذا اختار "غير متوفرة" أو لم يختر - إخفاء الحقل وجعله صفر
                maxSyncWrapper.style.display = 'none';
                maxSyncInput.removeAttribute('required');
                maxSyncInput.value = '0';
            }
        }

        if (syncSelect) {
            syncSelect.addEventListener('change', toggleMaxSyncCapacity);
            // التحقق عند تحميل الصفحة
            document.addEventListener('DOMContentLoaded', toggleMaxSyncCapacity);
            // التحقق فوراً أيضاً (في حالة كان DOM جاهزاً)
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', toggleMaxSyncCapacity);
            } else {
                toggleMaxSyncCapacity();
            }
        }
    })();

    // عند تحميل الصفحة، تهيئة الحقول حسب القيمة المحددة
    document.addEventListener('DOMContentLoaded', function() {
        if (operationEntitySelect) {
            const selectedOption = operationEntitySelect.options[operationEntitySelect.selectedIndex];
            const code = selectedOption ? selectedOption.getAttribute('data-code') : '';
            // الحصول على operator_id من hidden input (للمشغل والموظفين)
            const operatorId = operatorIdSelect ? operatorIdSelect.value : 
                              (form.querySelector('input[name="operator_id"]') ? form.querySelector('input[name="operator_id"]').value : null);
            
            if (code === 'SAME_OWNER') {
                // إذا كان "نفس المالك"، جلب بيانات المشغل الذي يتبعه المستخدم
                if (operatorId) {
                    loadOperatorData(operatorId, true);
                }
                toggleFieldsForSameOwner();
            } else if (code) {
                // إذا كان "طرف آخر"، جلب بيانات المالك فقط (للإظهار كـ read-only)
                // بيانات المشغل (اسم المشغل ورقم هوية المشغل) يجب إدخالها يدوياً
                if (operatorId) {
                    loadOperatorData(operatorId, false);
                }
                toggleFieldsForOtherParty();
            }
        }
    });

    // ====== تحديث المدن عند تغيير المحافظة ======
    const governorateSelect = document.getElementById('governorate');
    const citySelect = document.getElementById('city_id');

    governorateSelect?.addEventListener('change', function() {
        if (typeof GeneralHelpers !== 'undefined' && GeneralHelpers.updateCitiesSelect) {
            // الآن governorateSelect.value هو ID وليس code
            const governorateId = this.value;
            if (governorateId) {
                // تفعيل المدينة قبل تحديثها (في حالة كانت معطلة)
                if (citySelect) {
                    citySelect.disabled = false;
                }
                GeneralHelpers.updateCitiesSelect('#governorate', '#city_id');
            } else {
                // إذا لم يتم اختيار محافظة، تعطيل المدينة
                if (citySelect) {
                    citySelect.innerHTML = '<option value="">اختر المدينة</option>';
                    citySelect.disabled = true;
                }
            }
        }
    });

    // تحميل المدن تلقائياً عند تحميل الصفحة إذا كانت المحافظة محددة
    document.addEventListener('DOMContentLoaded', function() {
        // تعطيل المدينة في البداية إذا لم تكن هناك مدن محملة (أي إذا كان هناك فقط option واحد "اختر المدينة")
        if (citySelect && citySelect.options.length <= 1) {
            citySelect.disabled = true;
        }
        
        if (governorateSelect && governorateSelect.value) {
            // الآن governorateSelect.value هو ID وليس code
            const governorateId = governorateSelect.value;
            if (governorateId && typeof GeneralHelpers !== 'undefined' && GeneralHelpers.updateCitiesSelect) {
                // تفعيل المدينة قبل تحديثها
                if (citySelect) {
                    citySelect.disabled = false;
                }
                const cityId = citySelect ? citySelect.value : null;
                GeneralHelpers.updateCitiesSelect('#governorate', '#city_id', {
                    selectedValue: cityId
                });
            }
        }
    });

    // ====== حماية حقول Latitude و Longitude من إزالة readonly ======
    (function() {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        function enforceReadonly() {
            if (latInput && !latInput.hasAttribute('readonly')) {
                latInput.setAttribute('readonly', 'readonly');
            }
            if (lngInput && !lngInput.hasAttribute('readonly')) {
                lngInput.setAttribute('readonly', 'readonly');
            }
        }

        // مراقبة التغييرات على الحقول
        if (latInput) {
            // استخدام MutationObserver لمراقبة تغيير readonly attribute
            const latObserver = new MutationObserver(enforceReadonly);
            latObserver.observe(latInput, {
                attributes: true,
                attributeFilter: ['readonly']
            });

            // منع إزالة readonly عبر addEventListener
            Object.defineProperty(latInput, 'readOnly', {
                get: function() { return true; },
                set: function() { enforceReadonly(); }
            });

            // منع التعديل المباشر
            latInput.addEventListener('mousedown', function(e) {
                e.preventDefault();
            });
            latInput.addEventListener('keydown', function(e) {
                // السماح فقط بمفاتيح التنقل
                if (![8, 9, 27, 13, 37, 38, 39, 40, 35, 36, 46].includes(e.keyCode) &&
                    !(e.ctrlKey && [65, 67, 86, 88].includes(e.keyCode))) {
                    e.preventDefault();
                }
            });
        }

        if (lngInput) {
            // استخدام MutationObserver لمراقبة تغيير readonly attribute
            const lngObserver = new MutationObserver(enforceReadonly);
            lngObserver.observe(lngInput, {
                attributes: true,
                attributeFilter: ['readonly']
            });

            // منع إزالة readonly عبر addEventListener
            Object.defineProperty(lngInput, 'readOnly', {
                get: function() { return true; },
                set: function() { enforceReadonly(); }
            });

            // منع التعديل المباشر
            lngInput.addEventListener('mousedown', function(e) {
                e.preventDefault();
            });
            lngInput.addEventListener('keydown', function(e) {
                // السماح فقط بمفاتيح التنقل
                if (![8, 9, 27, 13, 37, 38, 39, 40, 35, 36, 46].includes(e.keyCode) &&
                    !(e.ctrlKey && [65, 67, 86, 88].includes(e.keyCode))) {
                    e.preventDefault();
                }
            });
        }

        // فرض readonly عند تحميل الصفحة وبشكل دوري
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', enforceReadonly);
        } else {
            enforceReadonly();
        }

        // مراقبة دورية للتأكد من أن readonly موجود دائماً
        setInterval(enforceReadonly, 1000);
    })();

    // ====== Interactive Map Module (Lazy Initialization) ======
    let mapInited = false;
    let map, marker;
    let userLocationObtained = false;
    let territories = [];
    let territoryCircles = [];
    let currentOperatorId = null;
    let previewCircle = null; // Preview circle for selected location
    let operatorRadiusKm = 5; // Default radius in kilometers

    // Local marker icon paths
    const markerIconsBase = '{{ asset("assets/leaflet/images/markers") }}';
    const markerShadowPath = '{{ asset("assets/leaflet/images/marker-shadow.png") }}';

    // DOM element references
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const territoryAlert = document.getElementById('territoryAlert');
    const loadTerritoriesBtn = document.getElementById('loadTerritoriesBtn');
    const clearTerritoriesBtn = document.getElementById('clearTerritoriesBtn');
    const territoryAreaInput = document.getElementById('territory_area_km2');
    const territoryRadiusInput = document.getElementById('territory_radius_km');
    
    /**
     * Calculate radius from area using circle area formula: r = √(Area / π)
     * @param {number} areaKm2 - Area in square kilometers
     * @returns {number} Radius in kilometers
     */
    function calculateRadiusFromArea(areaKm2) {
        if (!areaKm2 || areaKm2 <= 0) return 0;
        return Math.sqrt(areaKm2 / Math.PI);
    }
    
    /**
     * Get radius from input field (calculated from area)
     * @returns {number} Radius in kilometers
     */
    function getRadiusFromInput() {
        if (territoryAreaInput && territoryAreaInput.value) {
            const area = parseFloat(territoryAreaInput.value);
            if (!isNaN(area) && area > 0) {
                const radius = calculateRadiusFromArea(area);
                // Update hidden input for form submission
                if (territoryRadiusInput) {
                    territoryRadiusInput.value = radius.toFixed(8);
                }
                return radius;
            }
        }
        return operatorRadiusKm; // Return default value
    }
    
    /**
     * Update preview circle based on area input value
     * Automatically recalculates radius and redraws circle
     */
    function updateCircleFromArea() {
        const area = parseFloat(territoryAreaInput.value);
        if (!isNaN(area) && area > 0 && area <= 360) {
            const radius = calculateRadiusFromArea(area);
            // Update hidden input
            if (territoryRadiusInput) {
                territoryRadiusInput.value = radius.toFixed(8);
            }
            // Redraw circle immediately if map is ready
            if (map) {
                let lat, lng;
                // Try to get coordinates from input fields first
                const latValue = parseFloat(latInput.value);
                const lngValue = parseFloat(lngInput.value);
                if (!isNaN(latValue) && !isNaN(lngValue) && latValue !== 0 && lngValue !== 0) {
                    lat = latValue;
                    lng = lngValue;
                } else if (marker) {
                    // If marker exists, use its coordinates
                    const markerPos = marker.getLatLng();
                    lat = markerPos.lat;
                    lng = markerPos.lng;
                    // Update input fields as well
                    latInput.value = lat.toFixed(8);
                    lngInput.value = lng.toFixed(8);
                } else {
                    // No coordinates available, don't draw circle
                    return;
                }
                
                // Draw circle
                if (typeof window.drawPreviewCircle === 'function') {
                    window.drawPreviewCircle(lat, lng, radius);
                }
            }
        }
    }
    
    // Monitor area input changes to automatically calculate radius and update circle
    if (territoryAreaInput) {
        // Listen to input field changes
        territoryAreaInput.addEventListener('input', updateCircleFromArea);
        territoryAreaInput.addEventListener('change', updateCircleFromArea);
        territoryAreaInput.addEventListener('keyup', updateCircleFromArea);
        
        // Calculate initial value
        if (territoryAreaInput.value) {
            const initialRadius = getRadiusFromInput();
            operatorRadiusKm = initialRadius;
        } else {
            // If no value, use default 5 km²
            territoryAreaInput.value = 5;
            const defaultRadius = calculateRadiusFromArea(5);
            if (territoryRadiusInput) {
                territoryRadiusInput.value = defaultRadius.toFixed(8);
            }
            operatorRadiusKm = defaultRadius;
        }
    }

    /**
     * Get user's current location using browser Geolocation API
     * @param {Function} callback - Callback function with {lat, lng} or null
     */
    function getUserLocation(callback) {
        if (!navigator.geolocation) {
            // Browser doesn't support Geolocation
            callback(null);
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Successfully obtained location
                callback({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                });
            },
            function(error) {
                // Failed to get location (user denied or error)
                console.log('Failed to get user location:', error.message);
                callback(null);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    /**
     * Initialize map (lazy initialization when location tab opens)
     */
    function initMap() {
        if (mapInited) return;
        mapInited = true;

        // Check if there are existing values in input fields (from old values)
        let defaultLat = parseFloat(latInput.value);
        let defaultLng = parseFloat(lngInput.value);
        const hasExistingValues = defaultLat && defaultLng && !isNaN(defaultLat) && !isNaN(defaultLng) && defaultLat !== 0 && defaultLng !== 0;

        // If no existing values, get user location first
        if (!hasExistingValues) {
            // Attempt to get user location immediately
            getUserLocation(function(userLocation) {
                if (userLocation) {
                    // Use user location
                    defaultLat = userLocation.lat;
                    defaultLng = userLocation.lng;
                    userLocationObtained = true;
                    
                    // Update input fields with coordinates
                    latInput.value = defaultLat.toFixed(8);
                    lngInput.value = defaultLng.toFixed(8);
                } else {
                    // Use default location (Gaza)
                    defaultLat = 31.3547;
                    defaultLng = 34.3088;
                    
                    // Update input fields with default coordinates
                    latInput.value = defaultLat.toFixed(8);
                    lngInput.value = defaultLng.toFixed(8);
                }
                
                // Initialize map with final location and place marker
                initializeMapWithLocation(defaultLat, defaultLng, false);
            });
            return; // initializeMapWithLocation will be called from callback
        }

        // If existing values found, use them directly
        initializeMapWithLocation(defaultLat, defaultLng, true);
    }

    /**
     * Initialize map with specific location and set up boundaries
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @param {boolean} hasExistingValues - Whether existing values are present
     */
    function initializeMapWithLocation(lat, lng, hasExistingValues = false) {
        // Set default zoom level high to show neighborhoods and streets clearly
        const defaultZoomLevel = 14; // High zoom level for detail visibility
        map = L.map('map').setView([lat, lng], defaultZoomLevel);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Define Gaza Strip bounding box
        // Gaza Strip approximate boundaries:
        // North: 31.6°N, South: 31.2°N
        // East: 34.6°E, West: 34.2°E
        const gazaBounds = L.latLngBounds(
            [31.2, 34.2], // Southwest
            [31.6, 34.6]  // Northeast
        );
        
        // Set maximum bounds to prevent user from navigating outside Gaza Strip
        map.setMaxBounds(gazaBounds);
        
        // Set allowed zoom levels
        // Allow slight zoom out (12) and zoom in up to 16
        map.setMinZoom(12); // Allow slight zoom out
        map.setMaxZoom(16);
        
        // Ensure map stays within bounds when dragging
        map.on('drag', function() {
            map.panInsideBounds(gazaBounds, { animate: false });
        });
        
        // Ensure map stays within bounds when zooming
        map.on('zoomend', function() {
            if (!gazaBounds.contains(map.getBounds())) {
                map.fitBounds(gazaBounds);
            }
        });

        /**
         * Calculate distance between two points using Haversine formula
         * @param {number} lat1 - Latitude of first point
         * @param {number} lon1 - Longitude of first point
         * @param {number} lat2 - Latitude of second point
         * @param {number} lon2 - Longitude of second point
         * @returns {number} Distance in kilometers
         */
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Earth's radius in kilometers
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = 
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c; // Distance in kilometers
        }

        /**
         * Draw preview circle for selected location (global function accessible from anywhere)
         * @param {number} lat - Latitude
         * @param {number} lng - Longitude
         * @param {number} radiusKm - Radius in kilometers
         */
        window.drawPreviewCircle = function(lat, lng, radiusKm) {
            // Ensure radiusKm is valid - if not, calculate from area
            let actualRadiusKm = radiusKm;
            if (!radiusKm || radiusKm <= 0 || isNaN(radiusKm)) {
                // Get area input value directly
                const areaInput = territoryAreaInput ? parseFloat(territoryAreaInput.value) : null;
                if (!isNaN(areaInput) && areaInput > 0) {
                    actualRadiusKm = Math.sqrt(areaInput / Math.PI);
                } else {
                    actualRadiusKm = 1.26; // Default for 5 km²
                }
            }
            
            // Get area input value directly for display
            const areaInput = territoryAreaInput ? parseFloat(territoryAreaInput.value) : null;
            const actualAreaKm2 = (!isNaN(areaInput) && areaInput > 0) ? areaInput : (Math.PI * actualRadiusKm * actualRadiusKm);
            
            // Remove previous circle if exists
            if (previewCircle) {
                map.removeLayer(previewCircle);
                if (previewCircle._distanceLine) {
                    map.removeLayer(previewCircle._distanceLine);
                }
                if (previewCircle._edgeMarker) {
                    map.removeLayer(previewCircle._edgeMarker);
                }
            }
            
            // Draw new circle - Leaflet calculates distance in meters correctly
            // radius in meters = actualRadiusKm * 1000
            const radiusMeters = actualRadiusKm * 1000;
            
            // Create circle using L.circle which calculates distance correctly
            // Leaflet uses Haversine formula internally for actual distance calculation
            previewCircle = L.circle([lat, lng], {
                radius: radiusMeters, // In meters - Leaflet calculates actual distance on Earth
                color: '#ffc107',
                fillColor: '#ffc107',
                fillOpacity: 0.2,
                weight: 2,
                dashArray: '5, 5'
            }).addTo(map);
            
            // Verify actual distance: use Leaflet's distance calculation
            // Get circle bounds from Leaflet itself
            const bounds = previewCircle.getBounds();
            const northEast = bounds.getNorthEast();
            const center = previewCircle.getLatLng();
            
            // Calculate actual distance from center to northeast (farthest point)
            const actualDistanceNE = calculateDistance(
                center.lat, 
                center.lng, 
                northEast.lat, 
                northEast.lng
            );
            
            // Calculate distance from center to direct north point
            // At latitude ~31 (Gaza), 1 degree latitude ≈ 111.32 km
            const latOffset = actualRadiusKm / 111.32;
            const northPoint = L.latLng(lat + latOffset, lng);
            const actualDistanceNorth = calculateDistance(lat, lng, northPoint.lat, northPoint.lng);
            
            // Add line showing distance from center to circle edge (for verification)
            const distanceLine = L.polyline([[lat, lng], [northPoint.lat, northPoint.lng]], {
                color: '#ffc107',
                weight: 1,
                dashArray: '3, 3',
                opacity: 0.5
            }).addTo(map);
            
            // Use input area directly (don't calculate from radius)
            // Add popup showing area only (without mentioning radius)
            previewCircle.bindPopup(`
                <div class="text-center">
                    <strong>منطقة الحجز المقترحة</strong><br>
                    <div style="margin-top: 8px;">
                        <div style="font-size: 14px; margin: 4px 0;">
                            <strong>المساحة:</strong> ${actualAreaKm2.toFixed(2)} كم²
                        </div>
                        <div style="font-size: 11px; color: #999; margin-top: 4px;">
                            (نسبة من مساحة القطاع: ${((actualAreaKm2 / 360) * 100).toFixed(1)}%)
                        </div>
                        ${((actualAreaKm2 / 360) * 100) > 50 ? '<div style="color: #dc3545; margin-top: 6px; padding: 4px; background: #fff3cd; border-radius: 3px; font-size: 11px;">⚠️ تحذير: هذه المساحة تغطي أكثر من نصف القطاع!</div>' : ''}
                    </div>
                </div>
            `);
            
            // Store elements for verification
            previewCircle._distanceLine = distanceLine;
            
            // Don't change zoom level - keep it at default (14)
            // Only move map to circle location
            if (map) {
                map.setView([lat, lng], map.getZoom());
            }
        }

        /**
         * Set marker on map at specified location
         * @param {number} lat - Latitude
         * @param {number} lng - Longitude
         * @param {string} popupText - Popup text
         */
        function setMarker(lat, lng, popupText) {
            if (marker) map.removeLayer(marker);
            
            // Create custom yellow icon for selected location
            const yellowIcon = L.icon({
                iconUrl: `${markerIconsBase}/marker-icon-2x-yellow.png`,
                shadowUrl: markerShadowPath,
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            
            marker = L.marker([lat, lng], { 
                draggable: true,
                icon: yellowIcon
            }).addTo(map);
            
            marker.bindPopup(popupText || 'موقع وحدة التوليد').openPopup();

            // Draw preview circle based on input value
            const radius = getRadiusFromInput();
            if (typeof window.drawPreviewCircle === 'function') {
                window.drawPreviewCircle(lat, lng, radius);
            }

            marker.on('dragend', async function () {
                const p = marker.getLatLng();
                latInput.value = p.lat.toFixed(8);
                lngInput.value = p.lng.toFixed(8);
                // Redraw preview circle at new location based on input value
                // Always use the radius calculated from territory_area_km2 input
                const radius = getRadiusFromInput();
                if (typeof window.drawPreviewCircle === 'function') {
                    window.drawPreviewCircle(p.lat, p.lng, radius);
                }
                // Check location availability after drag
                await checkTerritoryAvailability(p.lat, p.lng);
            });
        }

        if (hasExistingValues && latInput.value && lngInput.value) {
            // If existing values present, use them
            const existingLat = parseFloat(latInput.value);
            const existingLng = parseFloat(lngInput.value);
            if (!isNaN(existingLat) && !isNaN(existingLng)) {
                setMarker(existingLat, existingLng, 'موقع وحدة التوليد الحالي');
                return; // Don't change existing values
            }
        }

        // Set location (either user location or default)
        const popupText = userLocationObtained ? 'موقعك الحالي' : 'موقع وحدة التوليد';
        setMarker(lat, lng, popupText);
        latInput.value = lat.toFixed(8);
        lngInput.value = lng.toFixed(8);

        map.on('click', async function (e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            latInput.value = lat.toFixed(8);
            lngInput.value = lng.toFixed(8);
            setMarker(lat, lng, 'موقع وحدة التوليد المحدد');
            
            // Immediate availability check
            await checkTerritoryAvailability(lat, lng);
        });

        // Update circle when radius value changes
        if (territoryRadiusInput) {
            territoryRadiusInput.addEventListener('input', function() {
                if (marker) {
                    const p = marker.getLatLng();
                    const radius = getRadiusFromInput();
                    if (typeof window.drawPreviewCircle === 'function') {
                        window.drawPreviewCircle(p.lat, p.lng, radius);
                    }
                }
            });
        }

        // When marker is dragged
        if (marker) {
            marker.on('dragend', async function() {
                const p = marker.getLatLng();
                await checkTerritoryAvailability(p.lat, p.lng);
            });
        }
    }

    /**
     * Load territories from server
     */
    async function loadTerritories() {
        if (!map) return;
        
        try {
            const response = await fetch('{{ route("admin.territories.all") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Expected JSON but got:', text.substring(0, 200));
                return;
            }
            
            const data = await response.json();
            
            if (data.success) {
                territories = data.territories || [];
                currentOperatorId = data.current_operator_id;
                
                // Get radius from operator (from first territory of current operator)
                if (currentOperatorId && territories.length > 0) {
                    const currentOperatorTerritory = territories.find(t => t.operator_id == currentOperatorId);
                    if (currentOperatorTerritory) {
                        operatorRadiusKm = parseFloat(currentOperatorTerritory.radius_km);
                    }
                }
                
                displayTerritories();
                loadTerritoriesBtn.style.display = 'none';
                clearTerritoriesBtn.style.display = 'inline-block';
                document.getElementById('territoryLegend').classList.remove('d-none');
            }
        } catch (error) {
            console.error('Failed to load territories:', error);
        }
    }

    /**
     * Display territories on map
     */
    function displayTerritories() {
        // Remove previous territories
        clearTerritories();
        
        territories.forEach(territory => {
            const isCurrentOperator = territory.is_current_operator;
            
            // Different and clear colors
            const color = isCurrentOperator ? '#28a745' : '#dc3545';
            const fillColor = isCurrentOperator ? '#28a745' : '#dc3545';
            const fillOpacity = isCurrentOperator ? 0.25 : 0.2;
            const weight = isCurrentOperator ? 3 : 2;
            
            // Convert radius from km to meters
            const radiusMeters = territory.radius_km * 1000;
            
            const circle = L.circle([territory.center_latitude, territory.center_longitude], {
                radius: radiusMeters,
                color: color,
                fillColor: fillColor,
                fillOpacity: fillOpacity,
                weight: weight,
                opacity: 0.8,
            }).addTo(map);
            
            // Add hover effects
            circle.on('mouseover', function(e) {
                const layer = e.target;
                layer.setStyle({
                    weight: weight + 2,
                    fillOpacity: fillOpacity + 0.1,
                });
            });
            
            circle.on('mouseout', function(e) {
                const layer = e.target;
                layer.setStyle({
                    weight: weight,
                    fillOpacity: fillOpacity,
                });
            });
            
            // Calculate area from radius
            const areaKm2 = Math.PI * territory.radius_km * territory.radius_km;
            
            // Add simplified popup (only essential information)
            const popupContent = `
                <div style="min-width: 200px; text-align: center;">
                    <div style="margin-bottom: 10px;">
                        <strong style="font-size: 16px; color: ${color};">
                            <i class="bi bi-geo-alt-fill me-1"></i>
                            ${territory.name || 'منطقة جغرافية'}
                        </strong>
                    </div>
                    <div style="border-top: 1px solid #ddd; padding-top: 8px; margin-top: 8px;">
                        <div style="margin-bottom: 5px;">
                            <i class="bi bi-building" style="color: #666;"></i>
                            <strong>المشغل:</strong> ${territory.operator_name || 'غير محدد'}
                        </div>
                        <div style="margin-bottom: 5px;">
                            <i class="bi bi-person" style="color: #666;"></i>
                            <strong>المالك:</strong> ${territory.owner_name || 'غير محدد'}
                        </div>
                        <div style="margin-bottom: 5px;">
                            <i class="bi bi-rulers" style="color: #666;"></i>
                            <strong>المساحة:</strong> ${areaKm2.toFixed(2)} كم²
                        </div>
                        <div style="margin-top: 10px;">
                            ${isCurrentOperator 
                                ? '<span class="badge bg-success" style="font-size: 12px;"><i class="bi bi-check-circle me-1"></i>منطقتك</span>' 
                                : '<span class="badge bg-danger" style="font-size: 12px;"><i class="bi bi-x-circle me-1"></i>محجوزة</span>'}
                        </div>
                    </div>
                </div>
            `;
            circle.bindPopup(popupContent, {
                className: isCurrentOperator ? 'territory-popup-own' : 'territory-popup-other',
                maxWidth: 250
            });
            
            territoryCircles.push(circle);
        });
    }

    /**
     * Remove territories from map
     */
    function clearTerritories() {
        territoryCircles.forEach(circle => {
            map.removeLayer(circle);
        });
        territoryCircles = [];
        document.getElementById('territoryLegend').classList.add('d-none');
    }

    /**
     * Check location availability for territory reservation
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     */
    async function checkTerritoryAvailability(lat, lng) {
        if (!currentOperatorId) {
            // Try to get operator_id from form
            const operatorIdInput = document.querySelector('input[name="operator_id"], select[name="operator_id"]');
            if (operatorIdInput) {
                currentOperatorId = operatorIdInput.value || operatorIdInput.selectedOptions[0]?.value;
            }
        }
        
        if (!currentOperatorId) {
            territoryAlert.className = 'alert alert-warning mb-2';
            territoryAlert.textContent = 'يرجى تحديد المشغل أولاً';
            territoryAlert.classList.remove('d-none');
            return;
        }
        
        try {
            const response = await fetch(`{{ route("admin.territories.check") }}?latitude=${lat}&longitude=${lng}&operator_id=${currentOperatorId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Expected JSON but got:', text.substring(0, 200));
                territoryAlert.className = 'alert alert-warning mb-2';
                territoryAlert.textContent = 'حدث خطأ أثناء التحقق من الموقع';
                territoryAlert.classList.remove('d-none');
                return;
            }
            
            const data = await response.json();
            
            // Update radius from response if available (for reference only)
            // But always use the radius calculated from territory_area_km2 input for drawing
            if (data.operator_radius_km) {
                operatorRadiusKm = parseFloat(data.operator_radius_km);
            }
            
            // Always redraw circle using the radius from territory_area_km2 input
            if (marker) {
                const p = marker.getLatLng();
                const radius = getRadiusFromInput(); // Always use input value, not operatorRadiusKm
                if (typeof window.drawPreviewCircle === 'function') {
                    window.drawPreviewCircle(p.lat, p.lng, radius);
                }
            }
            
            if (data.success && data.available) {
                territoryAlert.className = 'alert alert-success mb-2';
                territoryAlert.textContent = '✓ ' + (data.message || 'الموقع متاح') + ` (سيتم حجز ${operatorRadiusKm} كم)`;
                territoryAlert.classList.remove('d-none');
            } else {
                territoryAlert.className = 'alert alert-danger mb-2';
                let errorMessage = data.message || 'الموقع غير متاح';
                
                // If conflict is with another generation unit, show more details
                if (data.conflict_type === 'generation_unit' && data.conflict_data) {
                    const conflict = data.conflict_data;
                    errorMessage = `⚠ يوجد وحدة توليد أخرى للمشغل "${conflict.operator_name}" (اسم الوحدة: "${conflict.generation_unit_name}") في نفس الموقع أو قريبة جداً (المسافة: ${conflict.distance.toFixed(3)} كم). الحد الأدنى للمسافة بين وحدات التوليد: 0.1 كم.`;
                }
                
                territoryAlert.innerHTML = '<strong>⚠ تحذير:</strong> ' + errorMessage;
                territoryAlert.classList.remove('d-none');
            }
        } catch (error) {
            console.error('Failed to check location availability:', error);
            territoryAlert.className = 'alert alert-warning mb-2';
            territoryAlert.textContent = 'حدث خطأ أثناء التحقق من الموقع';
            territoryAlert.classList.remove('d-none');
        }
    }

    // Button event handlers
    loadTerritoriesBtn?.addEventListener('click', loadTerritories);
    clearTerritoriesBtn?.addEventListener('click', function() {
        clearTerritories();
        loadTerritoriesBtn.style.display = 'inline-block';
        clearTerritoriesBtn.style.display = 'none';
    });

    /**
     * Initialize map and place marker at user location when location tab is shown
     */
    document.querySelector('[data-bs-target="#tab-location"]')?.addEventListener('shown.bs.tab', function () {
        // If map is not initialized yet, initialize it
        if (!mapInited) {
        initMap();
        } else if (map && !marker) {
            // If map is ready but no marker exists, get user location and place it immediately
            getUserLocation(function(userLocation) {
                if (userLocation) {
                    // Use user location
                    const userLat = userLocation.lat;
                    const userLng = userLocation.lng;
                    userLocationObtained = true;
                    
                    // Update input fields
                    if (latInput) latInput.value = userLat.toFixed(8);
                    if (lngInput) lngInput.value = userLng.toFixed(8);
                    
                    // Place marker at user location immediately
                    if (typeof setMarker === 'function') {
                        setMarker(userLat, userLng, 'موقعك الحالي');
                    }
                } else {
                    // If failed to get location, use default location
                    const defaultLat = 31.3547;
                    const defaultLng = 34.3088;
                    if (latInput) latInput.value = defaultLat.toFixed(8);
                    if (lngInput) lngInput.value = defaultLng.toFixed(8);
                    if (typeof setMarker === 'function') {
                        setMarker(defaultLat, defaultLng, 'موقع وحدة التوليد');
                    }
                }
            });
        }
        
        setTimeout(() => { 
            if (map) {
                map.invalidateSize(); 
                // Automatically load territories when location tab opens
                loadTerritories();
            }
        }, 300);
    });

    // ====== AJAX submit ======
    async function submitForm() {
        clearErrors();
        setLoading(true);

        try {
            // Ensure territory_area_km2 has a valid value
            if (territoryAreaInput) {
                const area = parseFloat(territoryAreaInput.value);
                if (isNaN(area) || area <= 0 || area > 360) {
                    // Set default value if invalid
                    territoryAreaInput.value = 5;
                }
            }

            // Ensure territory_radius_km is calculated and set before submission
            if (territoryAreaInput && territoryRadiusInput) {
                const area = parseFloat(territoryAreaInput.value);
                if (!isNaN(area) && area > 0) {
                    const radius = calculateRadiusFromArea(area);
                    territoryRadiusInput.value = radius.toFixed(8);
                } else {
                    // Use default if area is invalid
                    const defaultRadius = calculateRadiusFromArea(5);
                    territoryRadiusInput.value = defaultRadius.toFixed(8);
                }
            }

            const fd = new FormData(form);

            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: fd
            });

            // Check if response is JSON
            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await res.text();
                console.error('Expected JSON but got:', text.substring(0, 200));
                notify('error', 'حدث خطأ أثناء الحفظ. يرجى المحاولة مرة أخرى.');
                return;
            }

            const data = await res.json();

            if (res.status === 422) {
                // Check if it's a validation error (with errors object) or a general error message
                if (data.errors && Object.keys(data.errors).length > 0) {
                    showErrors(data.errors || {});
                } else if (data.message) {
                    // Show the error message clearly
                    notify('error', data.message);
                    // Also show in territory alert if it exists
                    const territoryAlert = document.getElementById('territoryAlert');
                    if (territoryAlert) {
                        territoryAlert.className = 'alert alert-danger mb-2';
                        territoryAlert.innerHTML = '<strong>⚠ خطأ:</strong> ' + data.message;
                        territoryAlert.classList.remove('d-none');
                        // Scroll to alert
                        territoryAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                return;
            }

            if (data && data.success) {
                notify('success', data.message || 'تم الحفظ');
                setTimeout(() => {
                    window.location.href = '{{ route('admin.generation-units.index') }}';
                }, 1500);
            } else {
                notify('error', (data && data.message) ? data.message : 'فشل الحفظ');
            }

        } catch (e) {
            notify('error', 'حدث خطأ أثناء الحفظ');
        } finally {
            setLoading(false);
        }
    }

    saveBtn.addEventListener('click', submitForm);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitForm();
    });

    // ====== تمرير الثوابت للـ JavaScript ======
    window.GENERATION_UNIT_CONSTANTS = {
        location: @json(($constants['location'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
        material: @json(($constants['material'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
        usage: @json(($constants['usage'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
        measurement_method: @json(($constants['measurement_method'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
        tank_condition: @json(($constants['tank_condition'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
    };

    // ====== إدارة خزانات الوقود الديناميكية ======
    const externalFuelTankSelect = document.getElementById('external_fuel_tank');
    const fuelTanksCountWrapper = document.getElementById('fuel_tanks_count_wrapper');
    const fuelTanksCountSelect = document.getElementById('fuel_tanks_count');
    const fuelTanksCountHidden = document.getElementById('fuel_tanks_count_hidden');
    const fuelTanksContainer = document.getElementById('fuel_tanks_container');

    // عند تغيير "خزان وقود خارجي"
    if (externalFuelTankSelect) {
        externalFuelTankSelect.addEventListener('change', function() {
            if (this.value === '1') {
                fuelTanksCountWrapper.style.display = 'block';
                if (fuelTanksCountSelect) fuelTanksCountSelect.required = true;
                if (fuelTanksCountHidden) {
                    fuelTanksCountHidden.removeAttribute('name');
                    fuelTanksCountHidden.disabled = true;
                }
                if (fuelTanksCountSelect) fuelTanksCountSelect.disabled = false;
            } else {
                fuelTanksCountWrapper.style.display = 'none';
                if (fuelTanksCountSelect) fuelTanksCountSelect.required = false;
                if (fuelTanksCountSelect) fuelTanksCountSelect.value = '0';
                if (fuelTanksContainer) fuelTanksContainer.innerHTML = '';
                if (fuelTanksCountHidden) {
                    fuelTanksCountHidden.setAttribute('name', 'fuel_tanks_count');
                    fuelTanksCountHidden.disabled = false;
                }
                if (fuelTanksCountSelect) fuelTanksCountSelect.disabled = true;
            }
        });
    }

    // عند تغيير عدد الخزانات
    if (fuelTanksCountSelect) {
        fuelTanksCountSelect.addEventListener('change', function() {
            const count = parseInt(this.value);
            if (count > 0 && count <= 10) {
                renderFuelTanks(count);
            } else {
                if (fuelTanksContainer) fuelTanksContainer.innerHTML = '';
            }
        });
    }

    // دالة لرسم خزانات الوقود
    function renderFuelTanks(count) {
        if (!fuelTanksContainer) return;
        fuelTanksContainer.innerHTML = '';

        for (let i = 1; i <= count; i++) {
            const tankHtml = `
                <div class="card mb-3 border-0 shadow-sm" id="tank_${i}">
                    <div class="card-header" style="background: linear-gradient(135deg, #2563eb 0%, #60a5fa 100%); padding: 1rem;">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-droplet-fill me-2"></i>خزان الوقود ${i}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">سعة الخزان ${i} (لتر) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="fuel_tanks[${i-1}][capacity]" 
                                       class="form-control" 
                                       min="0" 
                                       max="10000" 
                                       step="1"
                                       placeholder="أدخل السعة باللتر">
                                <small class="form-text text-muted">يمكن إدخال سعة تصل إلى 10000 لتر</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">موقع الخزان ${i} <span class="text-danger">*</span></label>
                                <select name="fuel_tanks[${i-1}][location_id]" class="form-select" required>
                                    <option value="">اختر الموقع</option>
                                    ${(window.GENERATION_UNIT_CONSTANTS.location && window.GENERATION_UNIT_CONSTANTS.location.length > 0) 
                                        ? window.GENERATION_UNIT_CONSTANTS.location.map(loc => `<option value="${loc.id}">${loc.label}</option>`).join('')
                                        : ''
                                    }
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">نظام الفلترة ${i}</label>
                                <select name="fuel_tanks[${i-1}][filtration_system_available]" class="form-select">
                                    <option value="0">غير متوفر</option>
                                    <option value="1">متوفر</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">حالة الخزان ${i}</label>
                                <select name="fuel_tanks[${i-1}][condition_id]" class="form-select">
                                    <option value="">اختر الحالة</option>
                                    ${(window.GENERATION_UNIT_CONSTANTS.tank_condition && window.GENERATION_UNIT_CONSTANTS.tank_condition.length > 0) 
                                        ? window.GENERATION_UNIT_CONSTANTS.tank_condition.map(cond => `<option value="${cond.id}">${cond.label}</option>`).join('')
                                        : ''
                                    }
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">مادة التصنيع ${i}</label>
                                <select name="fuel_tanks[${i-1}][material_id]" class="form-select">
                                    <option value="">اختر المادة</option>
                                    ${(window.GENERATION_UNIT_CONSTANTS.material && window.GENERATION_UNIT_CONSTANTS.material.length > 0) 
                                        ? window.GENERATION_UNIT_CONSTANTS.material.map(mat => `<option value="${mat.id}">${mat.label}</option>`).join('')
                                        : ''
                                    }
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">استخدامه ${i}</label>
                                <select name="fuel_tanks[${i-1}][usage_id]" class="form-select">
                                    <option value="">اختر الاستخدام</option>
                                    ${(window.GENERATION_UNIT_CONSTANTS.usage && window.GENERATION_UNIT_CONSTANTS.usage.length > 0) 
                                        ? window.GENERATION_UNIT_CONSTANTS.usage.map(use => `<option value="${use.id}">${use.label}</option>`).join('')
                                        : ''
                                    }
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">طريقة القياس ${i}</label>
                                <select name="fuel_tanks[${i-1}][measurement_method_id]" class="form-select">
                                    <option value="">اختر الطريقة</option>
                                    ${(window.GENERATION_UNIT_CONSTANTS.measurement_method && window.GENERATION_UNIT_CONSTANTS.measurement_method.length > 0) 
                                        ? window.GENERATION_UNIT_CONSTANTS.measurement_method.map(method => `<option value="${method.id}">${method.label}</option>`).join('')
                                        : ''
                                    }
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            fuelTanksContainer.insertAdjacentHTML('beforeend', tankHtml);
        }
    }

    // تهيئة أولية إذا كان هناك old data
    @if(old('external_fuel_tank') == '1' && old('fuel_tanks_count'))
        renderFuelTanks({{ old('fuel_tanks_count') }});
    @endif

})();

// ====== التحقق من رقم هوية المشغل (9 أرقام فقط) ======
(function() {
    function setupOperatorIdNumberInput() {
        const operatorIdNumberInput = document.getElementById('operator_id_number');
        if (!operatorIdNumberInput) return;

        operatorIdNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length > 9) {
                value = value.substring(0, 9);
            }
            e.target.value = value;
        });

        operatorIdNumberInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const numbers = paste.replace(/[^0-9]/g, '').substring(0, 9);
            e.target.value = numbers;
        });

        operatorIdNumberInput.addEventListener('keydown', function(e) {
            if ([8, 9, 27, 13, 46, 37, 38, 39, 40].indexOf(e.keyCode) !== -1 ||
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                return;
            }
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupOperatorIdNumberInput);
    } else {
        setupOperatorIdNumberInput();
    }
})();
</script>
@endpush


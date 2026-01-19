@extends('layouts.admin')

@section('title', 'إضافة وحدة توليد جديدة')

@php
    $breadcrumbTitle = 'إضافة وحدة توليد جديدة';
    $breadcrumbParent = 'وحدات التوليد';
    $breadcrumbParentUrl = route('admin.generation-units.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">العنوان التفصيلي <span class="text-danger">*</span></label>
                                        <input type="text" name="detailed_address" class="form-control @error('detailed_address') is-invalid @enderror"
                                               value="{{ old('detailed_address') }}" required>
                                        @error('detailed_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Latitude <span class="text-danger">*</span></label>
                                        <input type="number" step="0.00000001" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                               value="{{ old('latitude') }}" readonly required>
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Longitude <span class="text-danger">*</span></label>
                                        <input type="number" step="0.00000001" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                               value="{{ old('longitude') }}" readonly required>
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">تحديد الموقع على الخريطة <span class="text-danger">*</span></label>
                                        <div id="map" class="op-map"></div>
                                        <div class="form-text">اضغط على الخريطة لتحديد الموقع.</div>
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

                <div class="data-table-loading d-none" id="loading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted fw-semibold">جاري الحفظ...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
    const loading = document.getElementById('loading');

    function setLoading(on) {
        loading.classList.toggle('d-none', !on);
        saveBtn.disabled = on;
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

    // ====== Map (lazy init when tab opens) ======
    let mapInited = false;
    let map, marker;
    let userLocationObtained = false;

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    // الحصول على موقع المستخدم تلقائياً
    function getUserLocation(callback) {
        if (!navigator.geolocation) {
            // المتصفح لا يدعم Geolocation
            callback(null);
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                // نجح الحصول على الموقع
                callback({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                });
            },
            function(error) {
                // فشل الحصول على الموقع (المستخدم رفض أو خطأ)
                console.log('فشل الحصول على الموقع:', error.message);
                callback(null);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    function initMap() {
        if (mapInited) return;
        mapInited = true;

        // إذا كانت هناك قيم موجودة في الحقول (من old values)، استخدمها
        let defaultLat = parseFloat(latInput.value);
        let defaultLng = parseFloat(lngInput.value);
        const hasExistingValues = defaultLat && defaultLng && !isNaN(defaultLat) && !isNaN(defaultLng);

        // إذا لم تكن هناك قيم، احصل على موقع المستخدم
        if (!hasExistingValues) {
            // محاولة الحصول على موقع المستخدم
            getUserLocation(function(userLocation) {
                if (userLocation) {
                    // استخدام موقع المستخدم
                    defaultLat = userLocation.lat;
                    defaultLng = userLocation.lng;
                    userLocationObtained = true;
                } else {
                    // استخدام موقع افتراضي (غزة)
                    defaultLat = 31.3547;
                    defaultLng = 34.3088;
                }
                
                // تهيئة الخريطة بالموقع النهائي
                initializeMapWithLocation(defaultLat, defaultLng, hasExistingValues);
            });
            return; // سيتم استدعاء initializeMapWithLocation من callback
        }

        // إذا كانت هناك قيم موجودة، استخدمها مباشرة
        initializeMapWithLocation(defaultLat, defaultLng, true);
    }

    function initializeMapWithLocation(lat, lng, hasExistingValues = false) {
        // تحديد مستوى التكبير: 13 إذا كان موقع المستخدم، 11 للافتراضي
        const zoomLevel = userLocationObtained ? 13 : 11;
        map = L.map('map').setView([lat, lng], zoomLevel);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        function setMarker(lat, lng, popupText) {
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.bindPopup(popupText || 'موقع وحدة التوليد').openPopup();

            marker.on('dragend', function () {
                const p = marker.getLatLng();
                latInput.value = p.lat.toFixed(8);
                lngInput.value = p.lng.toFixed(8);
            });
        }

        if (hasExistingValues && latInput.value && lngInput.value) {
            // إذا كانت هناك قيم موجودة مسبقاً، استخدمها
            const existingLat = parseFloat(latInput.value);
            const existingLng = parseFloat(lngInput.value);
            if (!isNaN(existingLat) && !isNaN(existingLng)) {
                setMarker(existingLat, existingLng, 'موقع وحدة التوليد الحالي');
                return; // لا نغير القيم الموجودة
            }
        }

        // تعيين الموقع (إما موقع المستخدم أو الافتراضي)
        const popupText = userLocationObtained ? 'موقعك الحالي' : 'موقع وحدة التوليد';
        setMarker(lat, lng, popupText);
        latInput.value = lat.toFixed(8);
        lngInput.value = lng.toFixed(8);

        map.on('click', function (e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            latInput.value = lat.toFixed(8);
            lngInput.value = lng.toFixed(8);
            setMarker(lat, lng, 'موقع وحدة التوليد المحدد');
        });
    }

    // when location tab shows
    document.querySelector('[data-bs-target="#tab-location"]')?.addEventListener('shown.bs.tab', function () {
        initMap();
        setTimeout(() => { map && map.invalidateSize(); }, 200);
    });

    // ====== AJAX submit ======
    async function submitForm() {
        clearErrors();
        setLoading(true);

        try {
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

            const data = await res.json();

            if (res.status === 422) {
                showErrors(data.errors || {});
                // لا نحتاج رسالة إضافية هنا لأن showErrors تعرض الرسائل بشكل مفصل
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


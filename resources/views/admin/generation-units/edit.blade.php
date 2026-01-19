@extends('layouts.admin')

@section('title', 'تعديل وحدة التوليد')

@php
    $breadcrumbTitle = 'تعديل وحدة التوليد';
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
                            تعديل وحدة التوليد
                        </h5>
                        <div class="general-subtitle">{{ $generationUnit->name }}</div>
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
                    <form id="generationUnitForm" action="{{ route('admin.generation-units.update', $generationUnit) }}" method="POST">
                        @csrf
                        @method('PUT')

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
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">كود الوحدة</label>
                                        <input type="text" class="form-control" value="{{ $generationUnit->unit_code }}" readonly>
                                        <div class="form-text">كود الوحدة (يتم توليده تلقائياً ولا يمكن تعديله)</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">اسم وحدة التوليد <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name', $generationUnit->name) }}"
                                               placeholder="مثال: وحدة التوليد الرئيسية">
                                        <div class="form-text">الاسم الرسمي لوحدة التوليد</div>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">عدد المولدات المطلوبة <span class="text-danger">*</span></label>
                                        <input type="number" name="generators_count" id="generators_count" class="form-control @error('generators_count') is-invalid @enderror"
                                               value="{{ old('generators_count', $generationUnit->generators_count) }}" min="1" max="99" required>
                                        <div class="form-text">عدد المولدات التي يجب أن تكون في هذه الوحدة</div>
                                        @error('generators_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                                                <option value="{{ $entity->id }}" data-code="{{ $entity->code }}" {{ old('operation_entity_id', $generationUnit->operation_entity_id) == $entity->id ? 'selected' : '' }}>
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
                                        <label class="form-label fw-semibold">اسم المالك <span class="text-danger">*</span></label>
                                        <input type="text" name="owner_name" id="owner_name" class="form-control @error('owner_name') is-invalid @enderror"
                                               value="{{ old('owner_name', $generationUnit->owner_name) }}" required>
                                        @error('owner_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- رقم هوية المالك --}}
                                    <div class="col-md-4" id="owner_id_number_wrapper">
                                        <label class="form-label fw-semibold">رقم هوية المالك</label>
                                        <input type="text" name="owner_id_number" id="owner_id_number" class="form-control @error('owner_id_number') is-invalid @enderror"
                                               value="{{ old('owner_id_number', $generationUnit->owner_id_number) }}">
                                        @error('owner_id_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- اسم المشغل (يظهر فقط في حالة "طرف آخر" ومطلوب) --}}
                                    <div class="col-md-4" id="operator_name_wrapper" style="display: none;">
                                        <label class="form-label fw-semibold">اسم المشغل <span class="text-danger">*</span></label>
                                        <input type="text" name="operator_name" id="operator_name" class="form-control @error('operator_name') is-invalid @enderror"
                                               value="{{ old('operator_name', $generationUnit->operator_name) }}">
                                        @error('operator_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- رقم هوية المشغل (مطلوب في حالة "طرف آخر") --}}
                                    <div class="col-md-4" id="operator_id_number_wrapper">
                                        <label class="form-label fw-semibold" id="operator_id_number_label">رقم هوية المشغل</label>
                                        <input type="text" name="operator_id_number" id="operator_id_number" class="form-control @error('operator_id_number') is-invalid @enderror"
                                               value="{{ old('operator_id_number', $generationUnit->operator_id_number) }}" maxlength="9" pattern="[0-9]{9}">
                                        @error('operator_id_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- رقم الموبايل --}}
                                    <div class="col-md-4" id="phone_wrapper">
                                        <label class="form-label fw-semibold">رقم الموبايل</label>
                                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                               value="{{ old('phone', $generationUnit->phone) }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">رقم بديل</label>
                                        <input type="text" name="phone_alt" class="form-control @error('phone_alt') is-invalid @enderror"
                                               value="{{ old('phone_alt', $generationUnit->phone_alt) }}">
                                        @error('phone_alt')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">البريد الإلكتروني</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email', $generationUnit->email) }}">
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
                                                    {{ old('governorate_id', $selectedGovernorateId ?? $generationUnit->governorate_id) == $gov->id ? 'selected' : '' }}>
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
                                        <select name="city_id" id="city_id" class="form-select @error('city_id') is-invalid @enderror" {{ empty($cities) ? 'disabled' : '' }} required>
                                            <option value="">اختر المدينة</option>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->id }}"
                                                    {{ old('city_id', $generationUnit->city_id) == $city->id ? 'selected' : '' }}>
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
                                               value="{{ old('detailed_address', $generationUnit->detailed_address) }}" required>
                                        @error('detailed_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">تحديد الموقع على الخريطة <span class="text-danger">*</span></label>
                                        <div id="map" class="op-map"></div>
                                        <div class="form-text">اضغط على الخريطة لتحديد الموقع.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Latitude <span class="text-danger">*</span></label>
                                        <input type="number" step="0.00000001" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                               value="{{ old('latitude', $generationUnit->latitude) }}" required>
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Longitude <span class="text-danger">*</span></label>
                                        <input type="number" step="0.00000001" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                               value="{{ old('longitude', $generationUnit->longitude) }}" required>
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: TECH --}}
                            <div class="tab-pane fade" id="tab-tech" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">إجمالي القدرة (KVA) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="total_capacity" class="form-control @error('total_capacity') is-invalid @enderror"
                                               value="{{ old('total_capacity', $generationUnit->total_capacity) }}" required min="0.01">
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
                                                    {{ old('synchronization_available_id', $generationUnit->synchronization_available_id) == $sync->id ? 'selected' : '' }}>
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
                                               value="{{ old('max_synchronization_capacity', $generationUnit->max_synchronization_capacity) }}">
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
                                               value="{{ old('beneficiaries_count', $generationUnit->beneficiaries_count) }}">
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
                                                    {{ ($isCompanyOwner && $compliance->code === 'UNDER_EVALUATION') || (!$isCompanyOwner && old('environmental_compliance_status_id', $generationUnit->environmental_compliance_status_id) == $compliance->id) ? 'selected' : '' }}>
                                                    {{ $compliance->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($isCompanyOwner)
                                            {{-- للمشغل: إرسال قيمة "تحت التقييم" دائماً --}}
                                            <input type="hidden" name="environmental_compliance_status_id" value="{{ $underEvaluationId ?? old('environmental_compliance_status_id', $generationUnit->environmental_compliance_status_id) }}">
                                        @endif
                                        @error('environmental_compliance_status_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">وصف المستفيدين</label>
                                        <textarea name="beneficiaries_description" class="form-control @error('beneficiaries_description') is-invalid @enderror" rows="3">{{ old('beneficiaries_description', $generationUnit->beneficiaries_description) }}</textarea>
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
                                        <label class="form-label fw-semibold">حالة الوحدة <span class="text-danger">*</span></label>
                                        <select name="status_id" class="form-select @error('status_id') is-invalid @enderror" required>
                                            <option value="">اختر</option>
                                            @foreach($constants['status'] as $status)
                                                <option value="{{ $status->id }}" {{ old('status_id', $generationUnit->status_id) == $status->id ? 'selected' : '' }}>
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
                                                        <option value="0" {{ old('external_fuel_tank', $generationUnit->fuelTanks->count() > 0 ? '1' : '0') == '0' ? 'selected' : '' }}>لا</option>
                                                        <option value="1" {{ old('external_fuel_tank', $generationUnit->fuelTanks->count() > 0 ? '1' : '0') == '1' ? 'selected' : '' }}>نعم</option>
                                                    </select>
                                                    @error('external_fuel_tank')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6" id="fuel_tanks_count_wrapper" style="{{ $generationUnit->fuelTanks->count() > 0 ? 'display: block;' : 'display: none;' }}">
                                                    <label class="form-label fw-semibold">عدد خزانات الوقود (1-10) <span class="text-danger">*</span></label>
                                                    <select name="fuel_tanks_count" id="fuel_tanks_count" class="form-select @error('fuel_tanks_count') is-invalid @enderror">
                                                        <option value="0">اختر العدد</option>
                                                        @for($i = 1; $i <= 10; $i++)
                                                            <option value="{{ $i }}" {{ old('fuel_tanks_count', $generationUnit->fuelTanks->count()) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                    @error('fuel_tanks_count')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <!-- حقل hidden لإرسال القيمة الافتراضية عندما يكون external_fuel_tank = 0 -->
                                                <input type="hidden" id="fuel_tanks_count_hidden" name="fuel_tanks_count" value="{{ old('fuel_tanks_count', $generationUnit->fuelTanks->count()) }}">
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
                        <div class="spinner-border" role="status"></div>
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
            'operation_entity': 'كيان التشغيل',
            'operator_id_number': 'رقم هوية المشغل',
            'phone': 'رقم الهاتف',
            'phone_alt': 'رقم الهاتف البديل',
            'email': 'البريد الإلكتروني',
            'governorate': 'المحافظة',
            'city_id': 'المدينة',
            'detailed_address': 'العنوان التفصيلي',
            'latitude': 'خط العرض',
            'longitude': 'خط الطول',
            'total_capacity': 'السعة الإجمالية',
            'synchronization_available': 'التزامن متاح',
            'max_synchronization_capacity': 'السعة القصوى للتزامن',
            'beneficiaries_count': 'عدد المستفيدين',
            'beneficiaries_description': 'وصف المستفيدين',
            'environmental_compliance_status': 'حالة الامتثال البيئي',
            'external_fuel_tank': 'خزان وقود خارجي',
            'fuel_tanks_count': 'عدد خزانات الوقود',
            'fuel_tanks': 'خزانات الوقود'
        };

        const errorMessages = [];
        const firstField = Object.keys(errors || {})[0];
        
        // جمع جميع رسائل الأخطاء
        Object.keys(errors || {}).forEach(field => {
            const errorMsg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            const fieldLabel = fieldLabels[field] || field;
            errorMessages.push(fieldLabel + ': ' + errorMsg);
        });

        // عرض جميع الأخطاء في إشعار أحمر
        if (errorMessages.length > 0) {
            let errorMessage = 'يرجى تصحيح الأخطاء التالية:\n\n';
            errorMessage += errorMessages.join('\n');
            notify('error', errorMessage, 'تحقق من الأخطاء');
        }

        if (firstField) {
            const input = form.querySelector(`[name="${CSS.escape(firstField)}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = errors[firstField][0];
                input.insertAdjacentElement('afterend', div);

                // افتح التاب اللي فيه الحقل
                const pane = input.closest('.tab-pane');
                if (pane && pane.id) {
                    const tabBtn = document.querySelector(`[data-bs-target="#${pane.id}"]`);
                    if (tabBtn) new bootstrap.Tab(tabBtn).show();
                }

                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        Object.keys(errors || {}).forEach(field => {
            const input = form.querySelector(`[name="${CSS.escape(field)}"]`);
            if (!input) return;
            input.classList.add('is-invalid');
            if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) return;
            const div = document.createElement('div');
            div.className = 'invalid-feedback';
            div.textContent = errors[field][0];
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
        
        // جعل الحقول للقراءة فقط
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
        
        // إزالة required من حقول المشغل (لأنها مخفية) وإزالة النجمة
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
        // إظهار: اسم المالك (read-only)، رقم هوية المالك (read-only)، اسم المشغل (editable)، رقم هوية المشغل (editable)، رقم الجوال (editable)
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
        // لا نمسح القيمة إذا كانت موجودة (في حالة التعديل)
        if (operatorNameInput) {
            operatorNameInput.readOnly = false;
            operatorNameInput.style.backgroundColor = '';
            operatorNameInput.setAttribute('required', 'required');
            // لا نمسح القيمة إذا كانت موجودة بالفعل
        }
        if (operatorIdNumberInput) {
            operatorIdNumberInput.readOnly = false;
            operatorIdNumberInput.style.backgroundColor = '';
            operatorIdNumberInput.setAttribute('required', 'required');
            // لا نمسح القيمة إذا كانت موجودة بالفعل
        }
        // إضافة نجمة (*) على label رقم هوية المشغل
        if (operatorIdNumberLabel) {
            if (!operatorIdNumberLabel.innerHTML.includes('<span class="text-danger">*</span>')) {
                operatorIdNumberLabel.innerHTML = 'رقم هوية المشغل <span class="text-danger">*</span>';
            }
        }
        }
        if (phoneInput) {
            phoneInput.readOnly = false;
            phoneInput.style.backgroundColor = '';
            // لا نمسح القيمة إذا كانت موجودة بالفعل
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
            // المستخدم يجب أن يدخل يدوياً: اسم المشغل ورقم هوية المشغل (أو يبقى القيمة الموجودة في حالة التعديل)
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
            const isSameOwner = code === 'SAME_OWNER';
            if (isSameOwner) {
                loadOperatorData(this.value, true);
                toggleFieldsForSameOwner();
            } else {
                loadOperatorData(this.value, false);
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
            const operatorId = operatorIdSelect ? operatorIdSelect.value : 
                              (form.querySelector('input[name="operator_id"]') ? form.querySelector('input[name="operator_id"]').value : null);
            const isSameOwner = code === 'SAME_OWNER';
            
            if (code === 'SAME_OWNER') {
                // إذا كان "نفس المالك"، جلب بيانات المشغل الذي يتبعه المستخدم
                if (operatorId) {
                    loadOperatorData(operatorId, true);
                }
                toggleFieldsForSameOwner();
            } else if (code) {
                // إذا كان "طرف آخر" أو أي قيمة أخرى
                // عرض حقل اسم المشغل إذا كانت القيمة موجودة (في حالة التعديل)
                if (operatorNameWrapper && operatorNameInput && operatorNameInput.value) {
                    operatorNameWrapper.style.display = 'block';
                }
                // جلب بيانات المالك من المشغل الذي يتبعه المستخدم
                if (operatorId) {
                    loadOperatorData(operatorId, false);
                }
                toggleFieldsForOtherParty();
            } else {
                // إذا لم يكن هناك قيمة محددة، تحقق من وجود قيمة في حقل اسم المشغل
                // (في حالة التعديل، قد تكون القيمة موجودة من قبل)
                if (operatorNameWrapper && operatorNameInput && operatorNameInput.value) {
                    operatorNameWrapper.style.display = 'block';
                    toggleFieldsForOtherParty();
                }
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
                GeneralHelpers.updateCitiesSelect('#governorate', '#city_id');
            }
        }
    });

    // تحميل المدن تلقائياً عند تحميل الصفحة إذا كانت المحافظة محددة
    document.addEventListener('DOMContentLoaded', function() {
        if (governorateSelect && governorateSelect.value) {
            // الآن governorateSelect.value هو ID وليس code
            const governorateId = governorateSelect.value;
            if (governorateId && typeof GeneralHelpers !== 'undefined' && GeneralHelpers.updateCitiesSelect) {
                const cityId = citySelect ? citySelect.value : null;
                GeneralHelpers.updateCitiesSelect('#governorate', '#city_id', {
                    selectedValue: cityId
                });
            }
        }
    });

    // ====== Map (lazy init when tab opens) ======
    let mapInited = false;
    let map, marker;

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    function initMap() {
        if (mapInited) return;
        mapInited = true;

        const defaultLat = parseFloat(latInput.value || '31.3547');
        const defaultLng = parseFloat(lngInput.value || '34.3088');

        map = L.map('map').setView([defaultLat, defaultLng], 11);

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

        if (latInput.value && lngInput.value) {
            setMarker(parseFloat(latInput.value), parseFloat(lngInput.value), 'موقع وحدة التوليد الحالي');
        } else {
            // تعيين موقع افتراضي
            setMarker(defaultLat, defaultLng, 'موقع وحدة التوليد');
            latInput.value = defaultLat.toFixed(8);
            lngInput.value = defaultLng.toFixed(8);
        }

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
                notify('error', 'تحقق من الحقول المطلوبة');
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

        // تهيئة أولية
        if (externalFuelTankSelect.value === '1') {
            fuelTanksCountWrapper.style.display = 'block';
            if (fuelTanksCountSelect) fuelTanksCountSelect.required = true;
            if (fuelTanksCountHidden) {
                fuelTanksCountHidden.removeAttribute('name');
                fuelTanksCountHidden.disabled = true;
            }
            if (fuelTanksCountSelect) fuelTanksCountSelect.disabled = false;
            if (fuelTanksCountSelect && fuelTanksCountSelect.value && fuelTanksCountSelect.value !== '0') {
                renderFuelTanks(parseInt(fuelTanksCountSelect.value));
            }
        } else {
            if (fuelTanksCountHidden) {
                fuelTanksCountHidden.setAttribute('name', 'fuel_tanks_count');
                fuelTanksCountHidden.disabled = false;
            }
            if (fuelTanksCountSelect) fuelTanksCountSelect.disabled = true;
        }
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

    // تهيئة أولية إذا كانت هناك خزانات موجودة
    @if($generationUnit->fuelTanks->count() > 0)
        renderFuelTanks({{ $generationUnit->fuelTanks->count() }});
        @foreach($generationUnit->fuelTanks as $index => $tank)
            document.querySelector(`input[name="fuel_tanks[{{ $index }}][capacity]"]`).value = '{{ $tank->capacity }}';
            document.querySelector(`select[name="fuel_tanks[{{ $index }}][location_id]"]`).value = '{{ $tank->location_id ?? '' }}';
            document.querySelector(`select[name="fuel_tanks[{{ $index }}][filtration_system_available]"]`).value = '{{ $tank->filtration_system_available ? 1 : 0 }}';
            document.querySelector(`select[name="fuel_tanks[{{ $index }}][condition_id]"]`).value = '{{ $tank->condition_id ?? '' }}';
            document.querySelector(`select[name="fuel_tanks[{{ $index }}][material_id]"]`).value = '{{ $tank->material_id ?? '' }}';
            document.querySelector(`select[name="fuel_tanks[{{ $index }}][usage_id]"]`).value = '{{ $tank->usage_id ?? '' }}';
            document.querySelector(`select[name="fuel_tanks[{{ $index }}][measurement_method_id]"]`).value = '{{ $tank->measurement_method_id ?? '' }}';
        @endforeach
    @elseif(old('external_fuel_tank') == '1' && old('fuel_tanks_count'))
        renderFuelTanks({{ old('fuel_tanks_count') }});
    @endif

})();
</script>
@endpush


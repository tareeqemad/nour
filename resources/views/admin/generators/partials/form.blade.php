{{-- Shared Generator Form Partial - Used by both create.blade.php and edit.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    $isEdit = isset($generator);
    $affiliatedOperator = auth()->user()->getAffiliatedOperator();
    $canSelect = !auth()->user()->isAffiliatedWithOperator();

    // Helper to get field value (old or model value)
    $val = fn($field, $default = null) => old($field, $isEdit ? data_get($generator, $field) : $default);
    $selVal = fn($field) => old($field, $isEdit ? data_get($generator, $field) : null);
@endphp

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
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- Tab 1: البيانات الأساسية --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="basic" role="tabpanel">
        <div class="row g-3">
            {{-- Cascading Selects: المشغل → وحدة التوليد --}}
            <x-admin.operator-cascade
                :operators="$operators ?? collect()"
                :affiliatedOperator="$affiliatedOperator"
                :showGenerator="false"
                :showGenerationUnit="true"
                :generationUnits="$affiliatedOperator?->generationUnits ?? collect()"
                :selectedOperatorId="$isEdit ? old('operator_id', $generator->operator_id) : old('operator_id')"
                :selectedGenerationUnitId="$isEdit
                    ? old('generation_unit_id', $generator->generation_unit_id)
                    : (request()->query('generation_unit_id') ?? old('generation_unit_id'))"
                colClass="col-md-6"
                :routes="[
                    'generationUnits' => url('/admin/operators') . '/__OPERATOR__/generation-units',
                    'generators' => url('/admin/generation-units') . '/__UNIT__/generators-list',
                ]"
            />

            <div class="col-md-6">
                <label class="form-label fw-semibold">رقم المولد</label>
                <input type="text" name="generator_number" id="generator_number" class="form-control @error('generator_number') is-invalid @enderror"
                       value="{{ $val('generator_number') }}" readonly placeholder="مثال: GU-MD-DR-001-G01">
                <div class="form-text">يتم توليده تلقائياً بناءً على وحدة التوليد.</div>
                @error('generator_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">اسم المولد <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ $val('name') }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">حالة المولد <span class="text-danger">*</span></label>
                <select name="status_id" class="form-select @error('status_id') is-invalid @enderror" required>
                    <option value="">اختر الحالة</option>
                    @foreach($constants['status'] ?? [] as $status)
                        <option value="{{ $status->id }}" {{ $selVal('status_id') == $status->id ? 'selected' : '' }}>
                            {{ $status->label }}
                        </option>
                    @endforeach
                </select>
                @error('status_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">الوصف</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ $val('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- Tab 2: المواصفات الفنية --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="specs" role="tabpanel">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">قدرة المولد (KVA)</label>
                <input type="number" step="1" name="capacity_kva" class="form-control @error('capacity_kva') is-invalid @enderror"
                       value="{{ $val('capacity_kva', '250') }}" min="1">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">معامل القدرة (P.F)</label>
                <input type="number" step="0.01" name="power_factor" class="form-control @error('power_factor') is-invalid @enderror"
                       value="{{ $val('power_factor', '0.8') }}" min="0" max="1">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">الجهد الناتج (V)</label>
                <input type="number" name="voltage" class="form-control @error('voltage') is-invalid @enderror"
                       value="{{ $val('voltage', '400') }}" min="0">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">التردد (Hz)</label>
                <input type="number" name="frequency" class="form-control @error('frequency') is-invalid @enderror"
                       value="{{ $val('frequency', '50') }}" min="0">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">سنة التصنيع (YYYY)</label>
                <input type="text" name="manufacturing_year" id="manufacturing_year" class="form-control @error('manufacturing_year') is-invalid @enderror"
                       value="{{ $val('manufacturing_year', '2022') }}" placeholder="اختر السنة">
                @error('manufacturing_year')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">نوع المحرك</label>
                <select name="engine_type_id" class="form-select @error('engine_type_id') is-invalid @enderror">
                    <option value="">اختر نوع المحرك</option>
                    @foreach($constants['engine_type'] ?? [] as $engineType)
                        <option value="{{ $engineType->id }}" {{ $selVal('engine_type_id') == $engineType->id ? 'selected' : '' }}>
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

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- Tab 3: الوقود والتشغيل --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="fuel" role="tabpanel">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">نظام الحقن</label>
                <select name="injection_system_id" class="form-select @error('injection_system_id') is-invalid @enderror">
                    <option value="">اختر نظام الحقن</option>
                    @foreach($constants['injection_system'] ?? [] as $injection)
                        <option value="{{ $injection->id }}" {{ $selVal('injection_system_id') == $injection->id ? 'selected' : '' }}>
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
                       value="{{ $val('fuel_consumption_rate') }}" min="0">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">كفاءة الوقود المثالية (kWh/لتر)</label>
                <input type="number" step="0.01" name="ideal_fuel_efficiency" id="ideal_fuel_efficiency" class="form-control @error('ideal_fuel_efficiency') is-invalid @enderror"
                       value="{{ $val('ideal_fuel_efficiency', '0.5') }}" min="0" max="10" placeholder="0.5" readonly>
                @error('ideal_fuel_efficiency')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">سعة خزان الوقود الداخلي (لتر)</label>
                <input type="number" name="internal_tank_capacity" class="form-control @error('internal_tank_capacity') is-invalid @enderror"
                       value="{{ $val('internal_tank_capacity') }}" min="0">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">مؤشر القياس</label>
                <select name="measurement_indicator_id" class="form-select @error('measurement_indicator_id') is-invalid @enderror">
                    <option value="">اختر الحالة</option>
                    @foreach($constants['measurement_indicator'] ?? [] as $indicator)
                        <option value="{{ $indicator->id }}" {{ $selVal('measurement_indicator_id') == $indicator->id ? 'selected' : '' }}>
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

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- Tab 4: الحالة الفنية والتوثيق --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="technical" role="tabpanel">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">الحالة الفنية</label>
                <select name="technical_condition_id" class="form-select @error('technical_condition_id') is-invalid @enderror">
                    <option value="">اختر الحالة</option>
                    @foreach($constants['technical_condition'] ?? [] as $condition)
                        <option value="{{ $condition->id }}" {{ $selVal('technical_condition_id') == $condition->id ? 'selected' : '' }}>
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
                       value="{{ old('last_major_maintenance_date', $isEdit ? $generator->last_major_maintenance_date?->format('Y-m-d') : null) }}" max="{{ date('Y-m-d') }}">
                @error('last_major_maintenance_date')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- صورة لوحة البيانات للمحرك --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">صورة لوحة البيانات للمحرك</label>
                <input type="file" name="engine_data_plate_image" class="form-control image-input @error('engine_data_plate_image') is-invalid @enderror"
                       accept="image/*" data-preview="engine_data_plate_preview">
                <div class="image-preview-container mt-2" id="engine_data_plate_preview"
                     style="{{ $isEdit && $generator->engine_data_plate_image ? 'display: block;' : 'display: none;' }}">
                    <img src="{{ $isEdit && $generator->engine_data_plate_image ? Storage::url($generator->engine_data_plate_image) : '' }}" alt="معاينة" class="image-preview">
                    <button type="button" class="btn btn-sm btn-danger remove-image" onclick="removeImagePreview('engine_data_plate_image', 'engine_data_plate_preview')">
                        <i class="bi bi-x-circle"></i> إزالة
                    </button>
                </div>
                @if($isEdit && $generator->engine_data_plate_image)
                    <p class="text-muted small mt-1"><i class="bi bi-info-circle"></i> الصورة الحالية</p>
                @endif
            </div>

            {{-- صورة لوحة البيانات للمولد --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">صورة لوحة البيانات للمولد</label>
                <input type="file" name="generator_data_plate_image" class="form-control image-input @error('generator_data_plate_image') is-invalid @enderror"
                       accept="image/*" data-preview="generator_data_plate_preview">
                <div class="image-preview-container mt-2" id="generator_data_plate_preview"
                     style="{{ $isEdit && $generator->generator_data_plate_image ? 'display: block;' : 'display: none;' }}">
                    <img src="{{ $isEdit && $generator->generator_data_plate_image ? Storage::url($generator->generator_data_plate_image) : '' }}" alt="معاينة" class="image-preview">
                    <button type="button" class="btn btn-sm btn-danger remove-image" onclick="removeImagePreview('generator_data_plate_image', 'generator_data_plate_preview')">
                        <i class="bi bi-x-circle"></i> إزالة
                    </button>
                </div>
                @if($isEdit && $generator->generator_data_plate_image)
                    <p class="text-muted small mt-1"><i class="bi bi-info-circle"></i> الصورة الحالية</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- Tab 5: نظام التحكم --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="control" role="tabpanel">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">لوحة التحكم</label>
                @php
                    $controlPanelVal = old('control_panel_available', $isEdit ? ($generator->control_panel_available ? '1' : '0') : '0');
                @endphp
                <select name="control_panel_available" class="form-select @error('control_panel_available') is-invalid @enderror">
                    <option value="0" {{ $controlPanelVal == '0' ? 'selected' : '' }}>غير متوفرة</option>
                    <option value="1" {{ $controlPanelVal == '1' ? 'selected' : '' }}>متوفرة</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">نوع لوحة التحكم</label>
                <select name="control_panel_type_id" class="form-select @error('control_panel_type_id') is-invalid @enderror">
                    <option value="">اختر النوع</option>
                    @foreach($constants['control_panel_type'] ?? [] as $panelType)
                        <option value="{{ $panelType->id }}" {{ $selVal('control_panel_type_id') == $panelType->id ? 'selected' : '' }}>
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
                        <option value="{{ $panelStatus->id }}" {{ $selVal('control_panel_status_id') == $panelStatus->id ? 'selected' : '' }}>
                            {{ $panelStatus->label }}
                        </option>
                    @endforeach
                </select>
                @error('control_panel_status_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- صورة لوحة التحكم --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">صورة لوحة التحكم</label>
                <input type="file" name="control_panel_image" class="form-control image-input @error('control_panel_image') is-invalid @enderror"
                       accept="image/*" data-preview="control_panel_preview">
                <div class="image-preview-container mt-2" id="control_panel_preview"
                     style="{{ $isEdit && $generator->control_panel_image ? 'display: block;' : 'display: none;' }}">
                    <img src="{{ $isEdit && $generator->control_panel_image ? Storage::url($generator->control_panel_image) : '' }}" alt="معاينة" class="image-preview">
                    <button type="button" class="btn btn-sm btn-danger remove-image" onclick="removeImagePreview('control_panel_image', 'control_panel_preview')">
                        <i class="bi bi-x-circle"></i> إزالة
                    </button>
                </div>
                @if($isEdit && $generator->control_panel_image)
                    <p class="text-muted small mt-1"><i class="bi bi-info-circle"></i> الصورة الحالية</p>
                @endif
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">قراءة ساعات التشغيل الحالية</label>
                <input type="number" name="operating_hours" class="form-control @error('operating_hours') is-invalid @enderror"
                       value="{{ $val('operating_hours') }}" min="0">
            </div>
        </div>
    </div>
</div>

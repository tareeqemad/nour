@extends('layouts.admin')

@section('title', 'عرض وحدة التوليد')

@php
    $breadcrumbTitle = 'عرض وحدة التوليد';
    $breadcrumbParent = 'وحدات التوليد';
    $breadcrumbParentUrl = route('admin.generation-units.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}" />
    <style>
        /* Use dash-kpi from Design System instead of stat-card */
        .info-item { margin-bottom: 0.85rem; }
        .info-label {
            font-size: 0.78rem; color: var(--color-text-muted, #5B6780);
            font-weight: 600; margin-bottom: 0.25rem;
            display: flex; align-items: center; gap: 0.4rem;
        }
        .info-value {
            font-size: 0.92rem; color: var(--color-text-main, #1F2937); font-weight: 500;
        }
        .generator-item {
            padding: 0.75rem; background: #fff;
            border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 8px;
            margin-bottom: 0.5rem; display: flex; justify-content: space-between;
            align-items: center; transition: all 0.15s ease;
        }
        .generator-item:hover { background: #FAFCFF; }
        .fuel-tank-item {
            padding: 0.75rem; background: #fff;
            border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 8px;
            margin-bottom: 0.5rem; transition: all 0.15s ease;
        }
        .fuel-tank-item:hover { background: #FAFCFF; }
        #map { height: 400px; border-radius: 10px; border: 1px solid var(--color-border, #E5E7EB); }
    </style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        {{-- Header Card with Summary Stats --}}
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form :title="$generationUnit->name ?? 'غير محدد'" icon="bi-lightning-charge" :backRoute="route('admin.generation-units.index')" backLabel="العودة">
                    @can('generateQrCode', $generationUnit)
                        <a href="{{ route('admin.generation-units.qr-code', $generationUnit) }}" target="_blank" class="btn btn-success">
                            <i class="bi bi-qr-code me-1"></i>
                            طباعة QR Code
                        </a>
                    @endcan
                    @can('update', $generationUnit)
                        <a href="{{ route('admin.generation-units.edit', $generationUnit) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i>
                            تعديل
                        </a>
                    @endcan
                    @can('create', App\Models\Generator::class)
                        <a href="{{ route('admin.generators.create', ['generation_unit_id' => $generationUnit->id]) }}" class="btn btn-success">
                            <i class="bi bi-plus-circle me-1"></i>
                            إضافة مولد
                        </a>
                    @endcan
                    @can('delete', $generationUnit)
                        @php
                            $deletionCheck = $generationUnit->canBeDeleted();
                        @endphp
                        @if(!$deletionCheck['can_delete'])
                            <div class="alert alert-warning mb-0 mt-3" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>تحذير:</strong> لا يمكن حذف وحدة التوليد هذه لوجود سجلات مرتبطة بها:
                                <ul class="mb-0 mt-2">
                                    @foreach($deletionCheck['related_records'] as $record)
                                        <li>{{ $record['label'] }} ({{ $record['count'] }})</li>
                                    @endforeach
                                </ul>
                                @if(!empty($deletionCheck['generators_with_records']))
                                    <div class="mt-2">
                                        <strong>المولدات التي تحتوي على سجلات:</strong>
                                        <ul class="mb-0">
                                            @foreach($deletionCheck['generators_with_records'] as $gen)
                                                <li>
                                                    {{ $gen['name'] }} ({{ $gen['generator_number'] }})
                                                    <ul>
                                                        @foreach($gen['related_records'] as $type => $count)
                                                            <li>{{ $type }} ({{ $count }})</li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endcan
                </x-admin.card-header-form>

                <div class="card-body">
                    {{-- KPI Cards --}}
                    <div class="row g-3 mb-4">
                        @if($generationUnit->unit_code)
                        <div class="col-md-3 col-sm-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-primary"><i class="bi bi-hash"></i></div>
                                <div>
                                    <div class="dash-kpi-value">{{ $generationUnit->unit_code }}</div>
                                    <div class="dash-kpi-label">كود الوحدة</div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($generationUnit->statusDetail)
                        <div class="col-md-3 col-sm-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon {{ ($generationUnit->statusDetail->code === 'ACTIVE') ? 'kpi-success' : 'kpi-danger' }}">
                                    <i class="bi bi-{{ ($generationUnit->statusDetail->code === 'ACTIVE') ? 'check-circle' : 'x-circle' }}"></i>
                                </div>
                                <div>
                                    <div class="dash-kpi-value">{{ $generationUnit->statusDetail->label }}</div>
                                    <div class="dash-kpi-label">الحالة</div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($generationUnit->generators)
                        <div class="col-md-3 col-sm-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-warning"><i class="bi bi-lightning-charge"></i></div>
                                <div>
                                    <div class="dash-kpi-value">{{ $generationUnit->generators->count() }} / {{ $generationUnit->generators_count }}</div>
                                    <div class="dash-kpi-label">المولدات</div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($generationUnit->total_capacity)
                        <div class="col-md-3 col-sm-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-info"><i class="bi bi-speedometer2"></i></div>
                                <div>
                                    <div class="dash-kpi-value">{{ number_format($generationUnit->total_capacity, 0) }} KVA</div>
                                    <div class="dash-kpi-label">القدرة الإجمالية</div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Information Cards --}}
                    <div class="row g-3">
                        {{-- المعلومات الأساسية --}}
                        <div class="col-lg-6">
                            <x-admin.card>
                                <x-admin.card-header-form title="المعلومات الأساسية" icon="bi-info-circle">
                                </x-admin.card-header-form>
                                <div class="card-body">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-lightning-charge me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                            اسم وحدة التوليد
                                        </div>
                                        <div class="info-value">{{ $generationUnit->name ?? 'غير محدد' }}</div>
                                    </div>
                                    @if($generationUnit->operator)
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-building text-secondary"></i>
                                                المشغل
                                            </div>
                                            <div class="info-value">{{ $generationUnit->operator->name }}</div>
                                        </div>
                                    @endif
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-gear-wide-connected me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                            عدد المولدات المطلوبة
                                        </div>
                                        <div class="info-value">{{ $generationUnit->generators_count }}</div>
                                    </div>
                                    @if($generationUnit->statusDetail)
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-funnel text-{{ ($generationUnit->statusDetail->code === 'ACTIVE') ? 'success' : 'danger' }}"></i>
                                                الحالة
                                            </div>
                                            <div class="info-value">
                                                <span class="badge bg-{{ ($generationUnit->statusDetail->code === 'ACTIVE') ? 'success' : 'danger' }} px-3 py-2">
                                                    {{ $generationUnit->statusDetail->label }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </x-admin.card>
                        </div>

                        {{-- الملكية والتشغيل --}}
                        @if($generationUnit->owner_name || $generationUnit->operationEntityDetail || $generationUnit->operator_id_number || $generationUnit->phone || $generationUnit->email)
                            <div class="col-lg-6 mb-4">
                                <x-admin.card>
                                    <x-admin.card-header-form title="الملكية والتشغيل" icon="bi-person-badge">
                                    </x-admin.card-header-form>
                                    <div class="card-body">
                                        @if($generationUnit->owner_name)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-person me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    اسم المالك
                                                </div>
                                                <div class="info-value">{{ $generationUnit->owner_name }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->owner_id_number)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-card-text text-secondary"></i>
                                                    رقم هوية المالك
                                                </div>
                                                <div class="info-value">{{ $generationUnit->owner_id_number }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->operationEntityDetail)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-building me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    جهة التشغيل
                                                </div>
                                                <div class="info-value">{{ $generationUnit->operationEntityDetail->label }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->operator_name)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-person-badge me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    اسم المشغل
                                                </div>
                                                <div class="info-value">{{ $generationUnit->operator_name }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->operator_id_number)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-card-heading me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    رقم هوية المشغل
                                                </div>
                                                <div class="info-value">{{ $generationUnit->operator_id_number }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->phone)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-telephone me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    رقم الموبايل
                                                </div>
                                                <div class="info-value">{{ $generationUnit->phone }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->phone_alt)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-telephone-forward text-secondary"></i>
                                                    رقم بديل
                                                </div>
                                                <div class="info-value">{{ $generationUnit->phone_alt }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->email)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-envelope me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    البريد الإلكتروني
                                                </div>
                                                <div class="info-value">{{ $generationUnit->email }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </x-admin.card>
                            </div>
                        @endif

                        <div class="col-12">

                        </div>

                        {{-- الموقع --}}
                        @if($generationUnit->city || $generationUnit->detailed_address || $generationUnit->latitude)
                            <div class="col-lg-6 mb-4">
                                <x-admin.card>
                                    <x-admin.card-header-form title="الموقع" icon="bi-geo-alt">
                                    </x-admin.card-header-form>
                                    <div class="card-body">
                                        @if($generationUnit->governorateDetail)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-geo-alt-fill me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    المحافظة
                                                </div>
                                                <div class="info-value">{{ $generationUnit->governorateDetail->label }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->city)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-geo me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    المدينة
                                                </div>
                                                <div class="info-value">{{ $generationUnit->city->label }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->detailed_address)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-pin-map me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    العنوان التفصيلي
                                                </div>
                                                <div class="info-value">{{ $generationUnit->detailed_address }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->latitude && $generationUnit->longitude)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-globe me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    الإحداثيات
                                                </div>
                                                <div class="info-value">
                                                    <span class="badge bg-light text-dark me-2">Lat: {{ number_format($generationUnit->latitude, 8) }}</span>
                                                    <span class="badge bg-light text-dark">Lng: {{ number_format($generationUnit->longitude, 8) }}</span>
                                                </div>
                                            </div>
                                            <div class="info-item">
                                                <div id="map" class="mt-2"></div>
                                            </div>
                                        @endif
                                    </div>
                                </x-admin.card>
                            </div>
                        @endif

                        {{-- القدرات الفنية --}}
                        @if($generationUnit->total_capacity || $generationUnit->synchronizationAvailableDetail || $generationUnit->max_synchronization_capacity)
                            <div class="col-lg-6 mb-4">
                                <x-admin.card>
                                    <x-admin.card-header-form title="القدرات الفنية" icon="bi-lightning-charge">
                                    </x-admin.card-header-form>
                                    <div class="card-body">
                                        @if($generationUnit->total_capacity)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-speedometer2 me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    إجمالي القدرة
                                                </div>
                                                <div class="info-value">{{ number_format($generationUnit->total_capacity, 0) }} KVA</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->synchronizationAvailableDetail)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-arrows-angle-contract me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    مزامنة المولدات
                                                </div>
                                                <div class="info-value">{{ $generationUnit->synchronizationAvailableDetail->label }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->max_synchronization_capacity)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-lightning me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    القدرة القصوى للمزامنة
                                                </div>
                                                <div class="info-value">{{ number_format($generationUnit->max_synchronization_capacity, 2) }} KVA</div>
                                            </div>
                                        @endif
                                    </div>
                                </x-admin.card>
                            </div>
                        @endif

                        {{-- المولدات --}}
                        @if($generationUnit->generators && $generationUnit->generators->count() > 0)
                            <div class="col-lg-6 mb-4">
                                <x-admin.card>
                                    <x-admin.card-header-form :title="'المولدات (' . $generationUnit->generators->count() . ' / ' . $generationUnit->generators_count . ')'" icon="bi-lightning-charge">
                                    </x-admin.card-header-form>
                                    <div class="card-body">
                                        <div class="generators-list">
                                            @foreach($generationUnit->generators as $gen)
                                                <div class="generator-item">
                                                    <div>
                                                        <strong>{{ $gen->name ?? 'غير محدد' }}</strong>
                                                        @if($gen->generator_number)
                                                            <span class="badge bg-secondary ms-2">{{ $gen->generator_number }}</span>
                                                        @endif
                                                        @if($gen->statusDetail)
                                                            <span class="badge bg-{{ ($gen->statusDetail->code === 'ACTIVE') ? 'success' : 'danger' }} ms-2">
                                                                {{ $gen->statusDetail->label }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        @can('view', $gen)
                                                            <a href="{{ route('admin.generators.show', $gen) }}" class="btn btn-sm btn-outline-info">
                                                                <i class="bi bi-eye me-1"></i>
                                                                عرض
                                                            </a>
                                                        @endcan
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </x-admin.card>
                            </div>
                        @endif

                        {{-- خزانات الوقود --}}
                        @if($generationUnit->fuelTanks && $generationUnit->fuelTanks->count() > 0)
                            <div class="col-lg-6 mb-4">
                                <x-admin.card>
                                    <x-admin.card-header-form :title="'خزانات الوقود (' . $generationUnit->fuelTanks->count() . ')'" icon="bi-droplet">
                                    </x-admin.card-header-form>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            @foreach($generationUnit->fuelTanks as $tank)
                                                <div class="col-md-6">
                                                    <div class="fuel-tank-item">
                                                        <div class="info-label">
                                                            <i class="bi bi-droplet-fill me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                            خزان #{{ $tank->order }}
                                                            @if($tank->tank_code)
                                                                <span class="badge bg-secondary ms-2">{{ $tank->tank_code }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="info-value mt-2">
                                                            @if($tank->capacity)
                                                                <strong>السعة:</strong> {{ number_format($tank->capacity, 2) }} لتر
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </x-admin.card>
                            </div>
                        @endif

                        <div class="col-12">

                        </div>

                        {{-- المستفيدون والبيئة --}}
                        @if($generationUnit->beneficiaries_count || $generationUnit->beneficiaries_description || $generationUnit->environmentalComplianceStatusDetail)
                            <div class="col-lg-6 mb-4">
                                <x-admin.card>
                                    <x-admin.card-header-form title="المستفيدون والبيئة" icon="bi-people">
                                    </x-admin.card-header-form>
                                    <div class="card-body">
                                        @if($generationUnit->beneficiaries_count)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-people-fill me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    عدد المستفيدين
                                                </div>
                                                <div class="info-value">{{ number_format($generationUnit->beneficiaries_count) }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->beneficiaries_description)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-file-text me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    وصف المستفيدين
                                                </div>
                                                <div class="info-value">{{ $generationUnit->beneficiaries_description }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->environmentalComplianceStatusDetail)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-clipboard-check me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                    حالة الامتثال البيئي
                                                </div>
                                                <div class="info-value">{{ $generationUnit->environmentalComplianceStatusDetail->label }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </x-admin.card>
                            </div>
                        @endif

                        {{-- معلومات إضافية --}}
                        <div class="col-lg-6 mb-4">
                            <x-admin.card>
                                <x-admin.card-header-form title="معلومات إضافية" icon="bi-info-circle">
                                </x-admin.card-header-form>
                                <div class="card-body">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-calendar-plus me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                            تاريخ الإنشاء
                                        </div>
                                        <div class="info-value">{{ $generationUnit->created_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                    @if($generationUnit->creator)
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-person-plus me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                أنشأ بواسطة
                                            </div>
                                            <div class="info-value">{{ $generationUnit->creator->name }}</div>
                                        </div>
                                    @endif
                                    
                                    
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-pencil me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                            آخر تحديث
                                        </div>
                                        <div class="info-value">{{ $generationUnit->updated_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                    @if($generationUnit->updater)
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-person-check me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                آخر تحديث بواسطة
                                            </div>
                                            <div class="info-value">{{ $generationUnit->updater->name }}</div>
                                        </div>
                                    @endif
                                    
                                    @if($generationUnit->qr_code_generated_at)
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-qr-code me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                تاريخ توليد QR Code
                                            </div>
                                            <div class="info-value">{{ $generationUnit->qr_code_generated_at->format('Y-m-d H:i:s') }}</div>
                                        </div>
                                    @endif
                                </div>
                            </x-admin.card>
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @if($generationUnit->latitude && $generationUnit->longitude)
        <script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const lat = {{ $generationUnit->latitude }};
                const lng = {{ $generationUnit->longitude }};
                
                const map = L.map('map').setView([lat, lng], 13);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(map);
                
                L.marker([lat, lng])
                    .addTo(map)
                    .bindPopup('موقع وحدة التوليد: {{ $generationUnit->name }}')
                    .openPopup();
            });
        </script>
    @endif
@endpush

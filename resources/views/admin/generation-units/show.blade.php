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
        .stat-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            flex-shrink: 0;
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }

        .info-item {
            margin-bottom: 1rem;
        }

        .info-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-value {
            font-size: 0.95rem;
            color: #1f2937;
            font-weight: 500;
        }

        .generator-item {
            padding: 0.75rem;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
        }

        .generator-item:hover {
            background: #f8f9fa;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .fuel-tank-item {
            padding: 0.75rem;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
        }

        .fuel-tank-item:hover {
            background: #f8f9fa;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #map {
            height: 400px;
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }
    </style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        {{-- Header Card with Summary Stats --}}
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-lightning-charge me-2"></i>
                            {{ $generationUnit->name ?? 'غير محدد' }}
                        </h5>
                        <div class="general-subtitle">
                            تفاصيل وحدة التوليد
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
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
                        <a href="{{ route('admin.generation-units.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-1"></i>
                            العودة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Statistics Cards --}}
                    <div class="row g-3 mb-4">
                        @if($generationUnit->unit_code)
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-hash"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">كود الوحدة</div>
                                        <div class="stat-value">{{ $generationUnit->unit_code }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($generationUnit->statusDetail)
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon bg-{{ ($generationUnit->statusDetail->code === 'ACTIVE') ? 'success' : 'danger' }}">
                                        <i class="bi bi-{{ ($generationUnit->statusDetail->code === 'ACTIVE') ? 'check-circle' : 'x-circle' }}"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">الحالة</div>
                                        <div class="stat-value">{{ $generationUnit->statusDetail->label }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($generationUnit->generators)
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon bg-warning">
                                        <i class="bi bi-lightning-charge"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">المولدات</div>
                                        <div class="stat-value">{{ $generationUnit->generators->count() }} / {{ $generationUnit->generators_count }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($generationUnit->total_capacity)
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon bg-info">
                                        <i class="bi bi-speedometer2"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">القدرة الإجمالية</div>
                                        <div class="stat-value">{{ number_format($generationUnit->total_capacity, 2) }} KVA</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <hr class="my-4">

                    {{-- Information Cards --}}
                    <div class="row g-3">
                        {{-- المعلومات الأساسية --}}
                        <div class="col-lg-6">
                            <div class="general-card">
                                <div class="general-card-header">
                                    <h6 class="general-title mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        المعلومات الأساسية
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-lightning-charge text-warning"></i>
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
                                            <i class="bi bi-gear-wide-connected text-primary"></i>
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
                            </div>
                        </div>

                        {{-- الملكية والتشغيل --}}
                        @if($generationUnit->owner_name || $generationUnit->operationEntityDetail || $generationUnit->operator_id_number || $generationUnit->phone || $generationUnit->email)
                            <div class="col-lg-6 mb-4">
                                <div class="general-card">
                                    <div class="general-card-header">
                                        <h6 class="general-title mb-0">
                                            <i class="bi bi-person-badge me-2"></i>
                                            الملكية والتشغيل
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($generationUnit->owner_name)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-person text-info"></i>
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
                                                    <i class="bi bi-building text-primary"></i>
                                                    جهة التشغيل
                                                </div>
                                                <div class="info-value">{{ $generationUnit->operationEntityDetail->label }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->operator_name)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-person-badge text-success"></i>
                                                    اسم المشغل
                                                </div>
                                                <div class="info-value">{{ $generationUnit->operator_name }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->operator_id_number)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-card-heading text-success"></i>
                                                    رقم هوية المشغل
                                                </div>
                                                <div class="info-value">{{ $generationUnit->operator_id_number }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->phone)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-telephone text-info"></i>
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
                                                    <i class="bi bi-envelope text-primary"></i>
                                                    البريد الإلكتروني
                                                </div>
                                                <div class="info-value">{{ $generationUnit->email }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-12">
                            <hr class="my-4">
                        </div>

                        {{-- الموقع --}}
                        @if($generationUnit->city || $generationUnit->detailed_address || $generationUnit->latitude)
                            <div class="col-lg-6 mb-4">
                                <div class="general-card">
                                    <div class="general-card-header">
                                        <h6 class="general-title mb-0">
                                            <i class="bi bi-geo-alt me-2"></i>
                                            الموقع
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($generationUnit->governorateDetail)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-geo-alt-fill text-info"></i>
                                                    المحافظة
                                                </div>
                                                <div class="info-value">{{ $generationUnit->governorateDetail->label }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->city)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-geo text-primary"></i>
                                                    المدينة
                                                </div>
                                                <div class="info-value">{{ $generationUnit->city->label }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->detailed_address)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-pin-map text-warning"></i>
                                                    العنوان التفصيلي
                                                </div>
                                                <div class="info-value">{{ $generationUnit->detailed_address }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->latitude && $generationUnit->longitude)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-globe text-success"></i>
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
                                </div>
                            </div>
                        @endif

                        {{-- القدرات الفنية --}}
                        @if($generationUnit->total_capacity || $generationUnit->synchronizationAvailableDetail || $generationUnit->max_synchronization_capacity)
                            <div class="col-lg-6 mb-4">
                                <div class="general-card">
                                    <div class="general-card-header">
                                        <h6 class="general-title mb-0">
                                            <i class="bi bi-lightning-charge me-2"></i>
                                            القدرات الفنية
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($generationUnit->total_capacity)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-speedometer2 text-warning"></i>
                                                    إجمالي القدرة
                                                </div>
                                                <div class="info-value">{{ number_format($generationUnit->total_capacity, 2) }} KVA</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->synchronizationAvailableDetail)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-arrows-angle-contract text-primary"></i>
                                                    مزامنة المولدات
                                                </div>
                                                <div class="info-value">{{ $generationUnit->synchronizationAvailableDetail->label }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->max_synchronization_capacity)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-lightning text-info"></i>
                                                    القدرة القصوى للمزامنة
                                                </div>
                                                <div class="info-value">{{ number_format($generationUnit->max_synchronization_capacity, 2) }} KVA</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- المولدات --}}
                        @if($generationUnit->generators && $generationUnit->generators->count() > 0)
                            <div class="col-lg-6 mb-4">
                                <div class="general-card">
                                    <div class="general-card-header">
                                        <h6 class="general-title mb-0">
                                            <i class="bi bi-lightning-charge me-2"></i>
                                            المولدات ({{ $generationUnit->generators->count() }} / {{ $generationUnit->generators_count }})
                                        </h6>
                                    </div>
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
                                </div>
                            </div>
                        @endif

                        {{-- خزانات الوقود --}}
                        @if($generationUnit->fuelTanks && $generationUnit->fuelTanks->count() > 0)
                            <div class="col-lg-6 mb-4">
                                <div class="general-card">
                                    <div class="general-card-header">
                                        <h6 class="general-title mb-0">
                                            <i class="bi bi-droplet me-2"></i>
                                            خزانات الوقود ({{ $generationUnit->fuelTanks->count() }})
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            @foreach($generationUnit->fuelTanks as $tank)
                                                <div class="col-md-6">
                                                    <div class="fuel-tank-item">
                                                        <div class="info-label">
                                                            <i class="bi bi-droplet-fill text-primary"></i>
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
                                </div>
                            </div>
                        @endif

                        <div class="col-12">
                            <hr class="my-4">
                        </div>

                        {{-- المستفيدون والبيئة --}}
                        @if($generationUnit->beneficiaries_count || $generationUnit->beneficiaries_description || $generationUnit->environmentalComplianceStatusDetail)
                            <div class="col-lg-6 mb-4">
                                <div class="general-card">
                                    <div class="general-card-header">
                                        <h6 class="general-title mb-0">
                                            <i class="bi bi-people me-2"></i>
                                            المستفيدون والبيئة
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($generationUnit->beneficiaries_count)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-people-fill text-success"></i>
                                                    عدد المستفيدين
                                                </div>
                                                <div class="info-value">{{ number_format($generationUnit->beneficiaries_count) }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->beneficiaries_description)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-file-text text-info"></i>
                                                    وصف المستفيدين
                                                </div>
                                                <div class="info-value">{{ $generationUnit->beneficiaries_description }}</div>
                                            </div>
                                        @endif
                                        @if($generationUnit->environmentalComplianceStatusDetail)
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-clipboard-check text-primary"></i>
                                                    حالة الامتثال البيئي
                                                </div>
                                                <div class="info-value">{{ $generationUnit->environmentalComplianceStatusDetail->label }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- معلومات إضافية --}}
                        <div class="col-lg-6 mb-4">
                            <div class="general-card">
                                <div class="general-card-header">
                                    <h6 class="general-title mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        معلومات إضافية
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-calendar-plus text-info"></i>
                                            تاريخ الإنشاء
                                        </div>
                                        <div class="info-value">{{ $generationUnit->created_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                    @if($generationUnit->creator)
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-person-plus text-success"></i>
                                                أنشأ بواسطة
                                            </div>
                                            <div class="info-value">{{ $generationUnit->creator->name }}</div>
                                        </div>
                                    @endif
                                    
                                    <hr class="my-3">
                                    
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-pencil text-primary"></i>
                                            آخر تحديث
                                        </div>
                                        <div class="info-value">{{ $generationUnit->updated_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                    @if($generationUnit->updater)
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-person-check text-warning"></i>
                                                آخر تحديث بواسطة
                                            </div>
                                            <div class="info-value">{{ $generationUnit->updater->name }}</div>
                                        </div>
                                    @endif
                                    
                                    @if($generationUnit->qr_code_generated_at)
                                        <hr class="my-3">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-qr-code text-success"></i>
                                                تاريخ توليد QR Code
                                            </div>
                                            <div class="info-value">{{ $generationUnit->qr_code_generated_at->format('Y-m-d H:i:s') }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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

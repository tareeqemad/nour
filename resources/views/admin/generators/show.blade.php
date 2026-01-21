@extends('layouts.admin')

@section('title', 'عرض المولد')

@php
    $breadcrumbTitle = 'عرض المولد';
    $breadcrumbParent = 'إدارة المولدات';
    $breadcrumbParentUrl = route('admin.generators.index');
    use Illuminate\Support\Facades\Storage;
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/generators.css') }}">
    <style>
        .info-section {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
        }
        
        .info-item {
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-right: 3px solid #0d6efd;
        }
        
        .info-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #212529;
        }
        
        .info-value.empty {
            color: #adb5bd;
            font-style: italic;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            padding: 0.5rem;
            background: white;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .image-preview:hover {
            transform: scale(1.05);
        }
        
        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-weight: 500;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .generator-header-stats {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .accordion-item {
            border: none;
            margin-bottom: 1rem;
        }
        
        .accordion-button {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 12px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        
        .accordion-button:not(.collapsed) {
            background: #f8f9fa;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .accordion-body {
            padding: 1.5rem;
        }
        
        .info-section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }
    </style>
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
                            {{ $generator->name ?? 'غير محدد' }}
                        </h5>
                        <div class="general-subtitle">
                            تفاصيل المولد الكهربائي
                        </div>
                        <div class="generator-header-stats mt-3">
                            @if($generator->generator_number)
                                <div class="stat-badge">
                                    <i class="bi bi-hash text-primary"></i>
                                    <span>رقم المولد:</span>
                                    <strong class="text-primary">{{ $generator->generator_number }}</strong>
                                </div>
                            @endif
                            @if($generator->statusDetail)
                                <div class="stat-badge">
                                    <i class="bi bi-{{ ($generator->statusDetail->code === 'ACTIVE') ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }}"></i>
                                    <span>الحالة:</span>
                                    <strong class="text-{{ ($generator->statusDetail->code === 'ACTIVE') ? 'success' : 'danger' }}">
                                        {{ $generator->statusDetail->label }}
                                    </strong>
                                </div>
                            @endif
                            @if($generator->generationUnit)
                                <div class="stat-badge">
                                    <i class="bi bi-building text-info"></i>
                                    <span>وحدة التوليد:</span>
                                    <strong class="text-info">{{ $generator->generationUnit->name }}</strong>
                                </div>
                            @endif
                            @if($generator->operator)
                                <div class="stat-badge">
                                    <i class="bi bi-person-badge text-secondary"></i>
                                    <span>المشغل:</span>
                                    <strong class="text-secondary">{{ $generator->operator->name }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.generators.qr-code', $generator) }}" target="_blank" class="btn btn-success">
                            <i class="bi bi-qr-code me-1"></i>
                            طباعة QR Code
                        </a>
                        @can('update', $generator)
                            <a href="{{ route('admin.generators.edit', $generator) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-1"></i>
                                تعديل
                            </a>
                        @endcan
                        @can('transfer', $generator)
                            <a href="{{ route('admin.generators.transfer', $generator) }}" class="btn btn-warning">
                                <i class="bi bi-arrow-left-right me-1"></i>
                                نقل لمشغل آخر
                            </a>
                        @endcan
                        @can('delete', $generator)
                            @php
                                $deletionCheck = $generator->canBeDeleted();
                            @endphp
                            @if(!$deletionCheck['can_delete'])
                                <div class="alert alert-warning mb-0 mt-3" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>تحذير:</strong> لا يمكن حذف هذا المولد لوجود سجلات مرتبطة به:
                                    <ul class="mb-0 mt-2">
                                        @foreach($deletionCheck['related_records'] as $type => $count)
                                            <li>{{ $type }} ({{ $count }})</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endcan
                        <a href="{{ route('admin.generators.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="accordion" id="generatorAccordion">
                <!-- المعلومات الأساسية -->
                <div class="accordion-item info-section">
                    <h2 class="accordion-header" id="headingBasic">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBasic" aria-expanded="true" aria-controls="collapseBasic">
                            <span class="info-section-title">
                                <i class="bi bi-info-circle text-primary"></i>
                                المعلومات الأساسية
                            </span>
                        </button>
                    </h2>
                    <div id="collapseBasic" class="accordion-collapse collapse show" aria-labelledby="headingBasic" data-bs-parent="#generatorAccordion">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-lightning-charge text-warning"></i>
                                            اسم المولد
                                        </div>
                                        <div class="info-value">{{ $generator->name ?? 'غير محدد' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-hash text-primary"></i>
                                            رقم المولد
                                        </div>
                                        <div class="info-value">
                                            <span class="badge bg-primary badge-large">{{ $generator->generator_number ?? 'غير محدد' }}</span>
                                        </div>
                                    </div>
                                </div>
                                @if($generator->generationUnit)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-building text-info"></i>
                                                وحدة التوليد
                                            </div>
                                            <div class="info-value">
                                                {{ $generator->generationUnit->name }}
                                                @if($generator->generationUnit->unit_code)
                                                    <span class="badge bg-info ms-2">{{ $generator->generationUnit->unit_code }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($generator->operator)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-person-badge text-secondary"></i>
                                                المشغل
                                            </div>
                                            <div class="info-value">{{ $generator->operator->name }}</div>
                                        </div>
                                    </div>
                                @endif
                                @if($generator->statusDetail)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-toggle-{{ ($generator->statusDetail->code === 'ACTIVE') ? 'on text-success' : 'off text-danger' }}"></i>
                                                الحالة
                                            </div>
                                            <div class="info-value">
                                                <span class="badge bg-{{ ($generator->statusDetail->code === 'ACTIVE') ? 'success' : 'danger' }} badge-large">
                                                    {{ $generator->statusDetail->label }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($generator->description)
                                    <div class="col-12">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-file-text text-muted"></i>
                                                الوصف
                                            </div>
                                            <div class="info-value">{{ $generator->description }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- المواصفات الفنية -->
                @if($generator->capacity_kva || $generator->power_factor || $generator->voltage || $generator->frequency || $generator->engineTypeDetail || $generator->manufacturing_year)
                    <div class="accordion-item info-section">
                        <h2 class="accordion-header" id="headingSpecs">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpecs" aria-expanded="false" aria-controls="collapseSpecs">
                                <span class="info-section-title">
                                    <i class="bi bi-gear text-primary"></i>
                                    المواصفات الفنية
                                </span>
                            </button>
                        </h2>
                        <div id="collapseSpecs" class="accordion-collapse collapse" aria-labelledby="headingSpecs" data-bs-parent="#generatorAccordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    @if($generator->capacity_kva)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-speedometer2 text-warning"></i>
                                                    القدرة
                                                </div>
                                                <div class="info-value">{{ number_format($generator->capacity_kva, 2) }} KVA</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->power_factor)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-percent text-info"></i>
                                                    معامل القدرة
                                                </div>
                                                <div class="info-value">{{ number_format($generator->power_factor, 2) }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->voltage)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-lightning text-danger"></i>
                                                    الجهد
                                                </div>
                                                <div class="info-value">{{ $generator->voltage }}V</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->frequency)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-arrow-repeat text-primary"></i>
                                                    التردد
                                                </div>
                                                <div class="info-value">{{ $generator->frequency }} Hz</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->manufacturing_year)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-calendar text-info"></i>
                                                    سنة الصنع
                                                </div>
                                                <div class="info-value">{{ $generator->manufacturing_year }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->engineTypeDetail)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-gear-wide text-secondary"></i>
                                                    نوع المحرك
                                                </div>
                                                <div class="info-value">{{ $generator->engineTypeDetail->label }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- التشغيل والوقود -->
                @if($generator->injectionSystemDetail || $generator->fuel_consumption_rate || $generator->ideal_fuel_efficiency || $generator->internal_tank_capacity || $generator->measurementIndicatorDetail)
                    <div class="accordion-item info-section">
                        <h2 class="accordion-header" id="headingFuel">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFuel" aria-expanded="false" aria-controls="collapseFuel">
                                <span class="info-section-title">
                                    <i class="bi bi-fuel-pump text-primary"></i>
                                    التشغيل والوقود
                                </span>
                            </button>
                        </h2>
                        <div id="collapseFuel" class="accordion-collapse collapse" aria-labelledby="headingFuel" data-bs-parent="#generatorAccordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    @if($generator->injectionSystemDetail)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-droplet text-primary"></i>
                                                    نظام الحقن
                                                </div>
                                                <div class="info-value">{{ $generator->injectionSystemDetail->label }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->fuel_consumption_rate)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-speedometer text-warning"></i>
                                                    معدل استهلاك الوقود
                                                </div>
                                                <div class="info-value">{{ number_format($generator->fuel_consumption_rate, 2) }} لتر/ساعة</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->ideal_fuel_efficiency)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-graph-up text-success"></i>
                                                    الكفاءة المثالية
                                                </div>
                                                <div class="info-value">{{ number_format($generator->ideal_fuel_efficiency, 3) }} kWh/لتر</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->internal_tank_capacity)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-droplet-fill text-primary"></i>
                                                    سعة الخزان الداخلي
                                                </div>
                                                <div class="info-value">{{ number_format($generator->internal_tank_capacity, 2) }} لتر</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->measurementIndicatorDetail)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-rulers text-secondary"></i>
                                                    مؤشر القياس
                                                </div>
                                                <div class="info-value">{{ $generator->measurementIndicatorDetail->label }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- الحالة الفنية -->
                @if($generator->technicalConditionDetail || $generator->last_major_maintenance_date || $generator->engine_data_plate_image || $generator->generator_data_plate_image)
                    <div class="accordion-item info-section">
                        <h2 class="accordion-header" id="headingTechnical">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTechnical" aria-expanded="false" aria-controls="collapseTechnical">
                                <span class="info-section-title">
                                    <i class="bi bi-clipboard-check text-primary"></i>
                                    الحالة الفنية
                                </span>
                            </button>
                        </h2>
                        <div id="collapseTechnical" class="accordion-collapse collapse" aria-labelledby="headingTechnical" data-bs-parent="#generatorAccordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    @if($generator->technicalConditionDetail)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-clipboard-check text-success"></i>
                                                    الحالة الفنية
                                                </div>
                                                <div class="info-value">{{ $generator->technicalConditionDetail->label }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->last_major_maintenance_date)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-calendar-event text-warning"></i>
                                                    آخر صيانة كبرى
                                                </div>
                                                <div class="info-value">{{ $generator->last_major_maintenance_date->format('Y-m-d') }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->engine_data_plate_image)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-image text-info"></i>
                                                    صورة لوحة بيانات المحرك
                                                </div>
                                                <div class="info-value">
                                                    <img src="{{ Storage::url($generator->engine_data_plate_image) }}" alt="لوحة بيانات المحرك" class="image-preview">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->generator_data_plate_image)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-image text-info"></i>
                                                    صورة لوحة بيانات المولد
                                                </div>
                                                <div class="info-value">
                                                    <img src="{{ Storage::url($generator->generator_data_plate_image) }}" alt="لوحة بيانات المولد" class="image-preview">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- نظام التحكم -->
                @if($generator->control_panel_available)
                    <div class="accordion-item info-section">
                        <h2 class="accordion-header" id="headingControl">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseControl" aria-expanded="false" aria-controls="collapseControl">
                                <span class="info-section-title">
                                    <i class="bi bi-cpu text-primary"></i>
                                    نظام التحكم
                                </span>
                            </button>
                        </h2>
                        <div id="collapseControl" class="accordion-collapse collapse" aria-labelledby="headingControl" data-bs-parent="#generatorAccordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-check-circle text-success"></i>
                                                لوحة التحكم متوفرة
                                            </div>
                                            <div class="info-value">
                                                <span class="badge bg-success badge-large">نعم</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if($generator->controlPanelTypeDetail)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-gear text-primary"></i>
                                                    نوع لوحة التحكم
                                                </div>
                                                <div class="info-value">{{ $generator->controlPanelTypeDetail->label }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->controlPanelStatusDetail)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-activity text-info"></i>
                                                    حالة لوحة التحكم
                                                </div>
                                                <div class="info-value">{{ $generator->controlPanelStatusDetail->label }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->operating_hours)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-clock text-warning"></i>
                                                    ساعات التشغيل
                                                </div>
                                                <div class="info-value">{{ number_format($generator->operating_hours, 2) }} ساعة</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->control_panel_image)
                                        <div class="col-12">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-image text-info"></i>
                                                    صورة لوحة التحكم
                                                </div>
                                                <div class="info-value">
                                                    <img src="{{ Storage::url($generator->control_panel_image) }}" alt="لوحة التحكم" class="image-preview">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- خزانات الوقود -->
                @if($generator->external_fuel_tank || ($generator->generationUnit && $generator->generationUnit->fuelTanks && $generator->generationUnit->fuelTanks->count() > 0))
                    <div class="accordion-item info-section">
                        <h2 class="accordion-header" id="headingTanks">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTanks" aria-expanded="false" aria-controls="collapseTanks">
                                <span class="info-section-title">
                                    <i class="bi bi-droplet text-primary"></i>
                                    خزانات الوقود
                                </span>
                            </button>
                        </h2>
                        <div id="collapseTanks" class="accordion-collapse collapse" aria-labelledby="headingTanks" data-bs-parent="#generatorAccordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    @if($generator->external_fuel_tank)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-check-circle text-success"></i>
                                                    خزان وقود خارجي
                                                </div>
                                                <div class="info-value">
                                                    <span class="badge bg-success badge-large">نعم</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->fuel_tanks_count)
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">
                                                    <i class="bi bi-hash text-primary"></i>
                                                    عدد الخزانات
                                                </div>
                                                <div class="info-value">{{ $generator->fuel_tanks_count }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($generator->generationUnit && $generator->generationUnit->fuelTanks && $generator->generationUnit->fuelTanks->count() > 0)
                                        <div class="col-12">
                                            <div class="info-item">
                                                <div class="info-label mb-3">
                                                    <i class="bi bi-list-ul text-info"></i>
                                                    تفاصيل الخزانات
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>الكود</th>
                                                                <th>السعة (لتر)</th>
                                                                <th>الترتيب</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($generator->generationUnit->fuelTanks as $tank)
                                                                <tr>
                                                                    <td>{{ $tank->id }}</td>
                                                                    <td><span class="badge bg-secondary">{{ $tank->tank_code ?? 'N/A' }}</span></td>
                                                                    <td>{{ number_format($tank->capacity, 2) }}</td>
                                                                    <td>{{ $tank->order }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- معلومات إضافية -->
                <div class="accordion-item info-section">
                    <h2 class="accordion-header" id="headingAdditional">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdditional" aria-expanded="false" aria-controls="collapseAdditional">
                            <span class="info-section-title">
                                <i class="bi bi-info-circle text-primary"></i>
                                معلومات إضافية
                            </span>
                        </button>
                    </h2>
                    <div id="collapseAdditional" class="accordion-collapse collapse" aria-labelledby="headingAdditional" data-bs-parent="#generatorAccordion">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-calendar-plus text-info"></i>
                                            تاريخ الإنشاء
                                        </div>
                                        <div class="info-value">{{ $generator->created_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-pencil text-primary"></i>
                                            آخر تحديث
                                        </div>
                                        <div class="info-value">{{ $generator->updated_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                </div>
                                @if($generator->qr_code_generated_at)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-qr-code text-success"></i>
                                                تاريخ توليد QR Code
                                            </div>
                                            <div class="info-value">{{ $generator->qr_code_generated_at->format('Y-m-d H:i:s') }}</div>
                                        </div>
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

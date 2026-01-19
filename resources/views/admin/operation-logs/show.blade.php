@extends('layouts.admin')

@section('title', 'تفاصيل سجل التشغيل')

@php
    $breadcrumbTitle = 'تفاصيل سجل التشغيل';
    $breadcrumbParent = 'سجلات التشغيل';
    $breadcrumbParentUrl = route('admin.operation-logs.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/operation-logs.css') }}">
@endpush

@section('content')
    <div class="operation-logs-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
                            <div>
                                <div class="log-title">
                                    <i class="bi bi-journal-text me-2"></i>
                                    تفاصيل سجل التشغيل
                                    @if($operationLog->sequence)
                                        #{{ $operationLog->formatted_sequence }}
                                    @else
                                        #{{ $operationLog->id }}
                                    @endif
                                </div>
                                <div class="log-subtitle">
                                    @if($operationLog->generator)
                                        المولد: {{ $operationLog->generator->name }} |
                                    @endif
                                    التاريخ: {{ $operationLog->operation_date->format('Y-m-d') }}
                                    @if($operationLog->start_time && $operationLog->end_time)
                                        | الوقت: {{ $operationLog->start_time->format('H:i') }} - {{ $operationLog->end_time->format('H:i') }}
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                @can('update', $operationLog)
                                    <a href="{{ route('admin.operation-logs.edit', $operationLog) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil me-1"></i>
                                        تعديل
                                    </a>
                                @endcan
                                <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-1"></i>
                                    رجوع
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- Basic Information Section -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-muted">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                المعلومات الأساسية
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-list-ol text-primary me-1"></i>
                                        التسلسل
                                    </label>
                                    <p class="form-control-plaintext">
                                        @if($operationLog->sequence)
                                            <span class="badge bg-primary">{{ $operationLog->formatted_sequence }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-calendar3 text-primary me-1"></i>
                                        تاريخ التشغيل
                                    </label>
                                    <p class="form-control-plaintext">
                                        {{ $operationLog->operation_date->format('Y-m-d') }}
                                        <small class="text-muted ms-2">({{ $operationLog->operation_date->diffForHumans() }})</small>
                                    </p>
                                </div>

                                @if($operationLog->start_time && $operationLog->end_time)
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-clock text-info me-1"></i>
                                            وقت البدء
                                        </label>
                                        <p class="form-control-plaintext">
                                            {{ $operationLog->start_time ? $operationLog->start_time->format('H:i') : '-' }}
                                        </p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-clock-history text-danger me-1"></i>
                                            وقت الإيقاف
                                        </label>
                                        <p class="form-control-plaintext">
                                            {{ $operationLog->end_time ? $operationLog->end_time->format('H:i') : '-' }}
                                        </p>
                                    </div>

                                    @php
                                        if ($operationLog->start_time && $operationLog->end_time) {
                                            // دمج التاريخ مع الوقت للحصول على datetime كامل
                                            $startDateTime = \Carbon\Carbon::parse($operationLog->operation_date->format('Y-m-d') . ' ' . $operationLog->start_time->format('H:i:s'));
                                            $endDateTime = \Carbon\Carbon::parse($operationLog->operation_date->format('Y-m-d') . ' ' . $operationLog->end_time->format('H:i:s'));
                                            
                                            // إذا كان وقت الإيقاف قبل وقت البدء، يعني أنه تجاوز منتصف الليل
                                            if ($endDateTime->lt($startDateTime)) {
                                                $endDateTime->addDay();
                                            }
                                            
                                            $totalMinutes = $startDateTime->diffInMinutes($endDateTime);
                                            $hours = floor($totalMinutes / 60);
                                            $minutes = $totalMinutes % 60;
                                        } else {
                                            $hours = 0;
                                            $minutes = 0;
                                        }
                                    @endphp
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-hourglass-split text-warning me-1"></i>
                                            مدة التشغيل
                                        </label>
                                        <p class="form-control-plaintext">
                                            @if($hours > 0 || $minutes > 0)
                                                @if($hours > 0)
                                                    {{ $hours }} ساعة
                                                @endif
                                                @if($minutes > 0)
                                                    {{ $hours > 0 ? ' و ' : '' }}{{ $minutes }} دقيقة
                                                @endif
                                                <small class="text-muted ms-2">({{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }})</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Load & Performance Section -->
                        @if($operationLog->load_percentage !== null)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-muted">
                                <i class="bi bi-speedometer2 text-warning me-2"></i>
                                الأداء ونسبة التحميل
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-percent text-primary me-1"></i>
                                        نسبة التحميل (%)
                                    </label>
                                    <p class="form-control-plaintext">
                                        {{ number_format($operationLog->load_percentage, 2) }}%
                                        @php
                                            $loadColor = 'success';
                                            if ($operationLog->load_percentage < 50) {
                                                $loadColor = 'warning';
                                            } elseif ($operationLog->load_percentage > 90) {
                                                $loadColor = 'danger';
                                            }
                                        @endphp
                                        <span class="badge bg-{{ $loadColor }} ms-2">
                                            @if($operationLog->load_percentage < 50)
                                                منخفض
                                            @elseif($operationLog->load_percentage > 90)
                                                عالي جداً
                                            @else
                                                طبيعي
                                            @endif
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        @endif

                        <!-- Fuel Meter Section -->
                        @if($operationLog->fuel_meter_start !== null || $operationLog->fuel_meter_end !== null || $operationLog->fuel_consumed !== null)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-muted">
                                <i class="bi bi-fuel-pump text-danger me-2"></i>
                                قراءات عداد الوقود
                            </h6>
                            <div class="row g-3">
                                @if($operationLog->fuel_meter_start !== null)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-arrow-right-circle text-success me-1"></i>
                                        قراءة عداد الوقود عند البدء
                                    </label>
                                    <p class="form-control-plaintext">{{ number_format($operationLog->fuel_meter_start, 2) }} لتر</p>
                                </div>
                                @endif

                                @if($operationLog->fuel_meter_end !== null)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-arrow-left-circle text-danger me-1"></i>
                                        قراءة عداد الوقود عند الانتهاء
                                    </label>
                                    <p class="form-control-plaintext">{{ number_format($operationLog->fuel_meter_end, 2) }} لتر</p>
                                </div>
                                @endif

                                @if($operationLog->fuel_consumed !== null)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-calculator text-info me-1"></i>
                                        كمية الوقود المستهلك
                                    </label>
                                    <p class="form-control-plaintext">
                                        <strong class="text-danger">{{ number_format($operationLog->fuel_consumed, 2) }} لتر</strong>
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <hr class="my-4">
                        @endif

                        <!-- Energy Meter Section -->
                        @if($operationLog->energy_meter_start !== null || $operationLog->energy_meter_end !== null || $operationLog->energy_produced !== null)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-muted">
                                <i class="bi bi-lightning-charge text-warning me-2"></i>
                                قراءات عداد الطاقة
                            </h6>
                            <div class="row g-3">
                                @if($operationLog->energy_meter_start !== null)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-arrow-right-circle text-success me-1"></i>
                                        قراءة عداد الطاقة عند البدء
                                    </label>
                                    <p class="form-control-plaintext">{{ number_format($operationLog->energy_meter_start, 2) }} kWh</p>
                                </div>
                                @endif

                                @if($operationLog->energy_meter_end !== null)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-arrow-left-circle text-danger me-1"></i>
                                        قراءة عداد الطاقة عند الإيقاف
                                    </label>
                                    <p class="form-control-plaintext">{{ number_format($operationLog->energy_meter_end, 2) }} kWh</p>
                                </div>
                                @endif

                                @if($operationLog->energy_produced !== null)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-calculator text-info me-1"></i>
                                        كمية الطاقة المنتجة
                                    </label>
                                    <p class="form-control-plaintext">
                                        <strong class="text-success">{{ number_format($operationLog->energy_produced, 2) }} kWh</strong>
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <hr class="my-4">
                        @endif

                        <!-- Notes & Malfunctions Section -->
                        @if($operationLog->operational_notes || $operationLog->malfunctions)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-muted">
                                <i class="bi bi-journal-text text-primary me-2"></i>
                                الملاحظات والأعطال
                            </h6>
                            <div class="row g-3">
                                @if($operationLog->operational_notes)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-sticky text-info me-1"></i>
                                        ملاحظات تشغيلية
                                    </label>
                                    <div class="form-control-plaintext bg-light p-3 rounded">
                                        {{ $operationLog->operational_notes }}
                                    </div>
                                </div>
                                @endif

                                @if($operationLog->malfunctions)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                                        الأعطال المسجلة
                                    </label>
                                    <div class="form-control-plaintext bg-light p-3 rounded border border-danger">
                                        {{ $operationLog->malfunctions }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <hr class="my-4">
                        @endif

                        <!-- Generator Information Section -->
                        @if($operationLog->generator)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-muted">
                                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>
                                معلومات المولد
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">اسم المولد</label>
                                    <p class="form-control-plaintext">
                                        @can('view', $operationLog->generator)
                                            <a href="{{ route('admin.generators.show', $operationLog->generator) }}">
                                                {{ $operationLog->generator->name }}
                                            </a>
                                        @else
                                            {{ $operationLog->generator->name }}
                                        @endcan
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">رقم المولد</label>
                                    <p class="form-control-plaintext">
                                        <code class="text-primary">{{ $operationLog->generator->generator_number }}</code>
                                    </p>
                                </div>
                                @if($operationLog->operator)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المشغل</label>
                                    <p class="form-control-plaintext">
                                        @can('view', $operationLog->operator)
                                            <a href="{{ route('admin.operators.show', $operationLog->operator) }}">
                                                {{ $operationLog->operator->name }}
                                            </a>
                                        @else
                                            {{ $operationLog->operator->name }}
                                        @endcan
                                    </p>
                                </div>
                                @endif
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">قدرة المولد (KVA)</label>
                                    <p class="form-control-plaintext">{{ number_format($operationLog->generator->capacity_kva, 2) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">حالة المولد</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-{{ $operationLog->generator->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ $operationLog->generator->status === 'active' ? 'نشط' : 'غير نشط' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        @endif

                        <!-- Audit Information -->
                        <div>
                            <h6 class="fw-bold mb-3 text-muted">
                                <i class="bi bi-clock-history text-info me-2"></i>
                                معلومات التدقيق
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ الإنشاء</label>
                                    <p class="form-control-plaintext">{{ $operationLog->created_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">آخر تحديث</label>
                                    <p class="form-control-plaintext">{{ $operationLog->updated_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">المدة منذ التشغيل</label>
                                    <p class="form-control-plaintext">{{ $operationLog->operation_date->diffForHumans() }}</p>
                                </div>
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
    .form-control-plaintext {
        min-height: 38px;
        padding: 0.5rem 0;
        line-height: 1.5;
        display: flex;
        align-items: center;
    }
    
    .form-control-plaintext.bg-light {
        min-height: auto;
        padding: 0.75rem;
        white-space: pre-wrap;
    }
    
    code {
        background-color: rgba(0, 86, 179, 0.1);
        padding: 0.2em 0.4em;
        border-radius: 3px;
        font-size: 0.9em;
    }
    
    a.text-decoration-none:hover {
        text-decoration: underline !important;
    }
    
    .border.border-danger {
        border-color: #dc3545 !important;
        border-width: 2px !important;
    }
</style>
@endpush


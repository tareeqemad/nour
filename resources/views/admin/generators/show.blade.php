@extends('layouts.admin')

@section('title', 'عرض المولد')

@php
    $breadcrumbTitle = 'عرض المولد';
    $breadcrumbParent = 'إدارة المولدات';
    $breadcrumbParentUrl = route('admin.generators.index');
    use Illuminate\Support\Facades\Storage;
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/generators.css') }}">
<style>
    .gen-field {
        margin-bottom: 0.5rem; padding: 0.6rem 0.75rem;
        background: #fff; border-radius: 8px; border: 1px solid var(--color-border-soft, #EDF1F5);
    }
    .gen-field-label { font-size: 0.75rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.2rem; }
    .gen-field-value { font-size: 0.9rem; color: var(--color-text-main, #1F2937); font-weight: 600; }
    .gen-section {
        margin-bottom: 1rem; padding: 1rem 1.25rem; border-radius: 10px;
        background: #FAFCFF; border: 1px solid var(--color-border-soft, #EDF1F5);
    }
    .gen-section-title {
        font-size: 0.88rem; font-weight: 700; color: var(--color-primary, #24308F);
        margin-bottom: 0.75rem; padding-bottom: 0.5rem;
        border-bottom: 1.5px solid var(--color-border-soft, #EDF1F5);
        display: flex; align-items: center; gap: 0.4rem;
    }
    .gen-section-title i { opacity: .7; }
    .gen-img { max-width: 100%; max-height: 200px; border-radius: 8px; border: 1.5px solid var(--color-border, #E5E7EB); padding: 0.35rem; background: #FAFCFF; }
</style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form :title="($generator->name ?? 'مولد')" icon="bi-lightning-charge" :backRoute="route('admin.generators.index')">
                    @can('update', $generator)
                        <a href="{{ route('admin.generators.edit', $generator) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i>تعديل
                        </a>
                    @endcan
                    <a href="{{ route('admin.generators.qr-code', $generator) }}" target="_blank" class="btn btn-outline-success">
                        <i class="bi bi-qr-code me-1"></i>QR
                    </a>
                    @can('transfer', $generator)
                        <a href="{{ route('admin.generators.transfer', $generator) }}" class="btn btn-outline-warning">
                            <i class="bi bi-arrow-left-right me-1"></i>نقل
                        </a>
                    @endcan
                </x-admin.card-header-form>

                <div class="card-body">
                    {{-- KPI --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon {{ $generator->statusDetail && $generator->statusDetail->code === 'ACTIVE' ? 'kpi-success' : 'kpi-danger' }}">
                                    <i class="bi bi-{{ $generator->statusDetail && $generator->statusDetail->code === 'ACTIVE' ? 'check-circle' : 'x-circle' }}"></i>
                                </div>
                                <div>
                                    <div class="dash-kpi-value">{{ $generator->statusDetail->label ?? '—' }}</div>
                                    <div class="dash-kpi-label">الحالة</div>
                                </div>
                            </div>
                        </div>
                        @if($generator->capacity_kva)
                        <div class="col-md-3 col-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-primary"><i class="bi bi-speedometer2"></i></div>
                                <div>
                                    <div class="dash-kpi-value">{{ number_format($generator->capacity_kva, 0) }}</div>
                                    <div class="dash-kpi-label">القدرة KVA</div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($generator->operating_hours)
                        <div class="col-md-3 col-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-warning"><i class="bi bi-clock"></i></div>
                                <div>
                                    <div class="dash-kpi-value">{{ number_format($generator->operating_hours, 0) }}</div>
                                    <div class="dash-kpi-label">ساعات التشغيل</div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($generator->generationUnit)
                        <div class="col-md-3 col-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-info"><i class="bi bi-building"></i></div>
                                <div>
                                    <div class="dash-kpi-value" style="font-size: 1rem;">{{ $generator->generationUnit->name }}</div>
                                    <div class="dash-kpi-label">وحدة التوليد</div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    @can('delete', $generator)
                        @php $deletionCheck = $generator->canBeDeleted(); @endphp
                        @if(!$deletionCheck['can_delete'])
                            <div style="background: var(--color-warning-bg, #FFFBEB); border: 1px solid var(--color-warning-border, #FCD34D); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.85rem; color: var(--color-warning-text, #B45309);">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                لا يمكن حذف هذا المولد — سجلات مرتبطة:
                                @foreach($deletionCheck['related_records'] as $type => $count)
                                    <strong>{{ $type }}</strong> ({{ $count }}){{ !$loop->last ? '،' : '' }}
                                @endforeach
                            </div>
                        @endif
                    @endcan

                    {{-- المعلومات الأساسية --}}
                    <div class="gen-section">
                        <div class="gen-section-title"><i class="bi bi-info-circle"></i> المعلومات الأساسية</div>
                        <div class="row g-3">
                            @foreach([
                                ['label' => 'المشغل', 'value' => $generator->operator->name ?? null],
                                ['label' => 'وحدة التوليد', 'value' => ($generator->generationUnit->name ?? '') . ($generator->generationUnit->unit_code ? ' ('.$generator->generationUnit->unit_code.')' : '')],
                                ['label' => 'سنة الصنع', 'value' => $generator->manufacturing_year],
                                ['label' => 'نوع المحرك', 'value' => $generator->engineTypeDetail->label ?? null],
                            ] as $field)
                                @if($field['value'])
                                <div class="col-md-3 col-6">
                                    <div class="gen-field">
                                        <div class="gen-field-label">{{ $field['label'] }}</div>
                                        <div class="gen-field-value">{{ $field['value'] }}</div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                            @if($generator->description)
                            <div class="col-12">
                                <div class="gen-field">
                                    <div class="gen-field-label">الوصف</div>
                                    <div class="gen-field-value">{{ $generator->description }}</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- المواصفات الفنية --}}
                    @if($generator->capacity_kva || $generator->power_factor || $generator->voltage || $generator->frequency)
                    <div class="gen-section">
                        <div class="gen-section-title"><i class="bi bi-gear"></i> المواصفات الفنية</div>
                        <div class="row g-3">
                            @foreach([
                                ['label' => 'القدرة', 'value' => $generator->capacity_kva ? number_format($generator->capacity_kva, 0).' KVA' : null],
                                ['label' => 'معامل القدرة', 'value' => $generator->power_factor ? number_format($generator->power_factor, 2) : null],
                                ['label' => 'الجهد', 'value' => $generator->voltage ? $generator->voltage.'V' : null],
                                ['label' => 'التردد', 'value' => $generator->frequency ? $generator->frequency.' Hz' : null],
                            ] as $field)
                                @if($field['value'])
                                <div class="col-md-3 col-6">
                                    <div class="gen-field">
                                        <div class="gen-field-label">{{ $field['label'] }}</div>
                                        <div class="gen-field-value">{{ $field['value'] }}</div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- التشغيل والوقود --}}
                    @if($generator->injectionSystemDetail || $generator->fuel_consumption_rate || $generator->ideal_fuel_efficiency || $generator->internal_tank_capacity)
                    <div class="gen-section">
                        <div class="gen-section-title"><i class="bi bi-fuel-pump"></i> التشغيل والوقود</div>
                        <div class="row g-3">
                            @foreach([
                                ['label' => 'نظام الحقن', 'value' => $generator->injectionSystemDetail->label ?? null],
                                ['label' => 'استهلاك الوقود', 'value' => $generator->fuel_consumption_rate ? number_format($generator->fuel_consumption_rate, 2).' لتر/ساعة' : null],
                                ['label' => 'الكفاءة المثالية', 'value' => $generator->ideal_fuel_efficiency ? number_format($generator->ideal_fuel_efficiency, 3).' kWh/لتر' : null],
                                ['label' => 'سعة الخزان الداخلي', 'value' => $generator->internal_tank_capacity ? number_format($generator->internal_tank_capacity, 2).' لتر' : null],
                                ['label' => 'مؤشر القياس', 'value' => $generator->measurementIndicatorDetail->label ?? null],
                            ] as $field)
                                @if($field['value'])
                                <div class="col-md-3 col-6">
                                    <div class="gen-field">
                                        <div class="gen-field-label">{{ $field['label'] }}</div>
                                        <div class="gen-field-value">{{ $field['value'] }}</div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- الحالة الفنية --}}
                    @if($generator->technicalConditionDetail || $generator->last_major_maintenance_date)
                    <div class="gen-section">
                        <div class="gen-section-title"><i class="bi bi-clipboard-check"></i> الحالة الفنية</div>
                        <div class="row g-3">
                            @if($generator->technicalConditionDetail)
                            <div class="col-md-3 col-6">
                                <div class="gen-field">
                                    <div class="gen-field-label">الحالة الفنية</div>
                                    <div class="gen-field-value">{{ $generator->technicalConditionDetail->label }}</div>
                                </div>
                            </div>
                            @endif
                            @if($generator->last_major_maintenance_date)
                            <div class="col-md-3 col-6">
                                <div class="gen-field">
                                    <div class="gen-field-label">آخر صيانة كبرى</div>
                                    <div class="gen-field-value">{{ $generator->last_major_maintenance_date->format('Y-m-d') }}</div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @if($generator->engine_data_plate_image || $generator->generator_data_plate_image)
                        <div class="row g-3 mt-2">
                            @if($generator->engine_data_plate_image)
                            <div class="col-md-6">
                                <div class="gen-field-label mb-1">لوحة بيانات المحرك</div>
                                <img src="{{ Storage::url($generator->engine_data_plate_image) }}" alt="لوحة المحرك" class="gen-img">
                            </div>
                            @endif
                            @if($generator->generator_data_plate_image)
                            <div class="col-md-6">
                                <div class="gen-field-label mb-1">لوحة بيانات المولد</div>
                                <img src="{{ Storage::url($generator->generator_data_plate_image) }}" alt="لوحة المولد" class="gen-img">
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- نظام التحكم --}}
                    @if($generator->control_panel_available)
                    <div class="gen-section">
                        <div class="gen-section-title"><i class="bi bi-cpu"></i> نظام التحكم</div>
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <div class="gen-field">
                                    <div class="gen-field-label">لوحة التحكم</div>
                                    <div class="gen-field-value"><span class="badge-status-active">متوفرة</span></div>
                                </div>
                            </div>
                            @foreach([
                                ['label' => 'نوع لوحة التحكم', 'value' => $generator->controlPanelTypeDetail->label ?? null],
                                ['label' => 'حالة لوحة التحكم', 'value' => $generator->controlPanelStatusDetail->label ?? null],
                                ['label' => 'ساعات التشغيل', 'value' => $generator->operating_hours ? number_format($generator->operating_hours, 2).' ساعة' : null],
                            ] as $field)
                                @if($field['value'])
                                <div class="col-md-3 col-6">
                                    <div class="gen-field">
                                        <div class="gen-field-label">{{ $field['label'] }}</div>
                                        <div class="gen-field-value">{{ $field['value'] }}</div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                        @if($generator->control_panel_image)
                        <div class="mt-2">
                            <div class="gen-field-label mb-1">صورة لوحة التحكم</div>
                            <img src="{{ Storage::url($generator->control_panel_image) }}" alt="لوحة التحكم" class="gen-img">
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- خزانات الوقود --}}
                    @if($generator->external_fuel_tank || ($generator->generationUnit && $generator->generationUnit->fuelTanks->count() > 0))
                    <div class="gen-section">
                        <div class="gen-section-title"><i class="bi bi-droplet"></i> خزانات الوقود</div>
                        <div class="row g-3">
                            @if($generator->external_fuel_tank)
                            <div class="col-md-3 col-6">
                                <div class="gen-field">
                                    <div class="gen-field-label">خزان خارجي</div>
                                    <div class="gen-field-value"><span class="badge-status-active">متوفر</span></div>
                                </div>
                            </div>
                            @endif
                            @if($generator->fuel_tanks_count)
                            <div class="col-md-3 col-6">
                                <div class="gen-field">
                                    <div class="gen-field-label">عدد الخزانات</div>
                                    <div class="gen-field-value">{{ $generator->fuel_tanks_count }}</div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @if($generator->generationUnit && $generator->generationUnit->fuelTanks->count() > 0)
                        <div class="table-responsive mt-2">
                            <table class="table table-hover align-middle mb-0 general-table">
                                <thead>
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
                                        <td><span class="badge-neutral">{{ $tank->tank_code ?? '—' }}</span></td>
                                        <td>{{ number_format($tank->capacity, 2) }}</td>
                                        <td>{{ $tank->order }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- معلومات إضافية --}}
                    <div class="gen-section">
                        <div class="gen-section-title"><i class="bi bi-clock-history"></i> معلومات إضافية</div>
                        <div class="row g-3">
                            <div class="col-md-4 col-6">
                                <div class="gen-field">
                                    <div class="gen-field-label">تاريخ الإنشاء</div>
                                    <div class="gen-field-value">{{ $generator->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="gen-field">
                                    <div class="gen-field-label">آخر تحديث</div>
                                    <div class="gen-field-value">{{ $generator->updated_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                            @if($generator->qr_code_generated_at)
                            <div class="col-md-4 col-6">
                                <div class="gen-field">
                                    <div class="gen-field-label">توليد QR</div>
                                    <div class="gen-field-value">{{ $generator->qr_code_generated_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

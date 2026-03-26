@extends('layouts.admin')

@section('title', 'تفاصيل سجل الامتثال والسلامة')

@php
    $breadcrumbTitle = 'تفاصيل سجل الامتثال والسلامة';
    $breadcrumbParent = 'الامتثال والسلامة';
    $breadcrumbParentUrl = route('admin.compliance-safeties.index');
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form title="تفاصيل سجل الامتثال والسلامة" icon="bi-shield-check" :backRoute="route('admin.compliance-safeties.index')">
                        @can('update', $complianceSafety)
                            <a href="{{ route('admin.compliance-safeties.edit', $complianceSafety) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil me-1"></i>تعديل
                            </a>
                        @endcan
                    </x-admin.card-header-form>

                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-lg-4">
                                <x-admin.section title="معلومات إضافية" icon="bi-info-circle" :boxed="true">
                                    @if($complianceSafety->generationUnit)
                                        <div class="mb-2">
                                            <x-admin.field label="وحدة التوليد" :value="false" />
                                            <a href="{{ route('admin.generation-units.show', $complianceSafety->generationUnit) }}" class="text-decoration-none" style="font-size: 0.92rem;">{{ $complianceSafety->generationUnit->name }} {{ $complianceSafety->generationUnit->unit_code ? '(' . $complianceSafety->generationUnit->unit_code . ')' : '' }}</a>
                                        </div>
                                    @endif
                                    @if($complianceSafety->generator)
                                        <div class="mb-2">
                                            <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.15rem;">المولد</div>
                                            <a href="{{ route('admin.generators.show', $complianceSafety->generator) }}" class="text-decoration-none" style="font-size: 0.92rem;">{{ $complianceSafety->generator->generator_number }} — {{ $complianceSafety->generator->name }}</a>
                                        </div>
                                    @endif
                                    <x-admin.field label="جهة التفتيش" :value="$complianceSafety->inspection_authority ?: '—'" />
                                    <x-admin.field label="المُنشئ" :value="$complianceSafety->creator?->name ?? '—'" />
                                </x-admin.section>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <x-admin.section title="نتيجة التفتيش" icon="bi-clipboard-check" :boxed="true">
                                    <div class="text-break" style="font-size: 0.92rem; color: var(--color-text-main, #1F2937);">{{ $complianceSafety->inspection_result ?: '—' }}</div>
                                </x-admin.section>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <x-admin.section title="المخالفات المسجلة" icon="bi-exclamation-triangle" :boxed="true">
                                    @if($complianceSafety->violations)
                                        <div class="text-break" style="font-size: 0.92rem; color: var(--color-text-main, #1F2937);">{{ $complianceSafety->violations }}</div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </x-admin.section>
                            </div>
                            <div class="col-12">
                                <x-admin.section title="معلومات النظام" icon="bi-clock-history" :boxed="true">
                                    <div class="d-flex flex-wrap gap-4">
                                        <x-admin.field label="تاريخ الإنشاء" :value="$complianceSafety->created_at?->format('Y-m-d H:i') ?? '—'" />
                                        <x-admin.field label="آخر تحديث" :value="$complianceSafety->updated_at?->format('Y-m-d H:i') ?? '—'" />
                                    </div>
                                </x-admin.section>
                            </div>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

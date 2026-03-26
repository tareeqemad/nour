@extends('layouts.admin')

@section('title', 'تفاصيل قضية تفتيش وتعدي')

@php
    $breadcrumbTitle = 'تفاصيل القضية';
    $breadcrumbParent = 'قضايا التفتيش والتعدي';
    $breadcrumbParentUrl = route('admin.inspection-violation-cases.index');
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form title="تفاصيل قضية تفتيش وتعدي" icon="bi-shield-exclamation" :backRoute="route('admin.inspection-violation-cases.index')">
                        @can('update', $inspection_violation_case)
                            <a href="{{ route('admin.inspection-violation-cases.edit', $inspection_violation_case) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil me-1"></i>
                                تعديل
                            </a>
                        @endcan
                    </x-admin.card-header-form>

                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-lg-4">
                                <x-admin.section title="بيانات المشترك" icon="bi-person" :boxed="true">
                                    <x-admin.field label="المحافظة" :value="$inspection_violation_case->governorateDetail?->label ?? '—'" />
                                    <x-admin.field label="رقم الاشتراك" :value="$inspection_violation_case->subscription_number ?: '—'" />
                                    <x-admin.field label="اسم المشترك" :value="$inspection_violation_case->subscriber_name" />
                                    <x-admin.field label="اسم المنتفع" :value="$inspection_violation_case->beneficiary_name ?: '—'" />
                                </x-admin.section>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <x-admin.section title="تفاصيل القضية" icon="bi-clipboard-data" :boxed="true">
                                    <x-admin.field label="تاريخ القضية" :value="$inspection_violation_case->case_date?->format('Y-m-d') ?? '—'" />
                                    <div class="mb-2">
                                        <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.15rem;">حالة القضية</div>
                                        <span class="badge {{ $badgeClass }}">{{ \App\Models\InspectionViolationCase::statusLabels()[$inspection_violation_case->status] ?? $inspection_violation_case->status }}</span>
                                    </div>
                                    <x-admin.field label="المشغل" :value="$inspection_violation_case->operator?->name ?? '—'" />
                                    <x-admin.field label="وحدة التوليد" :value="$inspection_violation_case->generationUnit ? $inspection_violation_case->generationUnit->name . ($inspection_violation_case->generationUnit->unit_code ? ' (' . $inspection_violation_case->generationUnit->unit_code . ')' : '') : '—'" />
                                </x-admin.section>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <x-admin.section title="معلومات النظام" icon="bi-clock-history" :boxed="true">
                                    <x-admin.field label="المُدخل" :value="$inspection_violation_case->creator?->name ?? '—'" />
                                    <x-admin.field label="تاريخ الإدخال" :value="$inspection_violation_case->created_at?->format('Y-m-d H:i') ?? '—'" />
                                    <x-admin.field label="آخر من عدّل" :value="$inspection_violation_case->updater?->name ?? '—'" />
                                    <x-admin.field label="تاريخ التعديل" :value="$inspection_violation_case->updated_at?->format('Y-m-d H:i') ?? '—'" />
                                </x-admin.section>
                            </div>

                            <div class="col-12">
                                <x-admin.section title="بيان القضية / التعدي" icon="bi-journal-text" :boxed="true">
                                    <div class="text-break" style="font-size: 0.92rem; color: var(--color-text-main, #1F2937);">{{ $inspection_violation_case->statement ?: '—' }}</div>
                                </x-admin.section>
                            </div>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

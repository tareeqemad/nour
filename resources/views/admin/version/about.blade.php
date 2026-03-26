@extends('layouts.admin')

@section('title', 'حول النظام')

@php
    $breadcrumbTitle = 'حول النظام';
    $siteName = \App\Models\Setting::get('site_name', 'نور');
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="حول النظام" icon="bi-info-circle">
                    <x-slot:actions>
                        <a href="{{ route('admin.changelog') }}" class="btn btn-primary">
                            <i class="bi bi-journal-text me-1"></i>
                            سجل التغييرات
                        </a>
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.versions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-gear me-1"></i>
                                إدارة الإصدارات
                            </a>
                        @endif
                    </x-slot:actions>
                </x-admin.card-header>

                <div class="card-body">
                    <div class="row g-4">
                        {{-- بطاقة الإصدار --}}
                        <div class="col-lg-4">
                            <div style="background: #FAFCFF; border: 1px solid var(--color-border-soft, #EDF1F5); border-top: 2.5px solid var(--color-primary, #24308F); border-radius: 12px; padding: 1.75rem; text-align: center; height: 100%;">
                                <i class="bi bi-box-seam" style="font-size: 2rem; color: var(--color-primary, #24308F); opacity: 0.6;"></i>
                                <h4 class="fw-bold mt-2 mb-1" style="color: var(--color-text-main, #1F2937);">{{ $siteName }}</h4>
                                <span class="badge-role-system" style="font-size: 1rem; padding: 0.35rem 1rem;">
                                    v{{ $systemInfo['version'] }}
                                </span>
                                @if($currentVersion)
                                    <p class="mb-0 mt-2" style="color: var(--color-text-muted, #5B6780); font-size: 0.82rem;">
                                        {{ $currentVersion->title }}<br>
                                        <small style="color: var(--color-text-light, #98A2B3);">{{ $currentVersion->release_date->format('Y/m/d') }}</small>
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- معلومات تقنية --}}
                        <div class="col-lg-8">
                            <div style="border: 1px solid var(--color-border, #E5E7EB); border-radius: 12px; height: 100%;">
                                <div style="padding: 0.75rem 1.25rem; background: #FAFCFF; border-bottom: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 12px 12px 0 0;">
                                    <h6 class="mb-0 fw-bold" style="color: var(--color-primary, #24308F); font-size: 0.92rem;">
                                        <i class="bi bi-gear me-2" style="opacity: .75;"></i>
                                        المعلومات التقنية
                                    </h6>
                                </div>
                                <div style="padding: 0;">
                                    <table class="table table-hover align-middle mb-0">
                                        <tbody>
                                            <tr>
                                                <td style="color: var(--color-text-muted, #5B6780); width: 50%;">
                                                    <i class="bi bi-box me-2"></i>إصدار التطبيق
                                                </td>
                                                <td><span class="badge-role-system">v{{ $systemInfo['version'] }}</span></td>
                                            </tr>
                                            <tr>
                                                <td style="color: var(--color-text-muted, #5B6780);">
                                                    <i class="bi bi-code-slash me-2"></i>Laravel
                                                </td>
                                                <td><span class="badge-neutral">v{{ $systemInfo['laravel_version'] }}</span></td>
                                            </tr>
                                            <tr>
                                                <td style="color: var(--color-text-muted, #5B6780);">
                                                    <i class="bi bi-filetype-php me-2"></i>PHP
                                                </td>
                                                <td><span class="badge-neutral">v{{ $systemInfo['php_version'] }}</span></td>
                                            </tr>
                                            <tr>
                                                <td style="color: var(--color-text-muted, #5B6780);">
                                                    <i class="bi bi-globe me-2"></i>البيئة
                                                </td>
                                                <td>
                                                    @if($systemInfo['environment'] === 'production')
                                                        <span class="badge-status-active">production</span>
                                                    @else
                                                        <span class="badge-warning">{{ $systemInfo['environment'] }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="color: var(--color-text-muted, #5B6780);">
                                                    <i class="bi bi-clock me-2"></i>المنطقة الزمنية
                                                </td>
                                                <td><span class="badge-neutral">{{ $systemInfo['timezone'] }}</span></td>
                                            </tr>
                                            <tr>
                                                <td style="color: var(--color-text-muted, #5B6780);">
                                                    <i class="bi bi-translate me-2"></i>اللغة
                                                </td>
                                                <td><span class="badge-neutral">{{ $systemInfo['locale'] }}</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- وصف النظام --}}
                    <div class="mt-4" style="background: #FAFCFF; border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 12px; padding: 1.25rem;">
                        <h6 class="fw-bold mb-3" style="color: var(--color-primary, #24308F); font-size: 0.92rem;">
                            <i class="bi bi-file-text me-2" style="opacity: .75;"></i>
                            عن المنصة
                        </h6>
                        <p style="color: var(--color-text-secondary, #3B4863); font-size: 0.88rem; line-height: 1.7; margin-bottom: 1rem;">
                            منصة رقمية شاملة لإدارة المولدات الكهربائية والمشغلين في قطاع غزة.
                            تشمل إدارة المشغلين، وحدات التوليد، سجلات التشغيل والصيانة، الفوترة، والامتثال.
                        </p>
                        <div class="row g-2">
                            @foreach([
                                'إدارة المستخدمين والصلاحيات',
                                'إدارة المشغلين ووحدات التوليد',
                                'سجلات التشغيل والصيانة',
                                'إدارة المشتركين والفوترة',
                                'الشكاوى والمقترحات',
                                'نظام المراسلات والإشعارات',
                            ] as $feature)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2" style="font-size: 0.85rem; color: var(--color-text-main, #1F2937);">
                                        <i class="bi bi-check-circle-fill" style="color: var(--color-success, #10B981); font-size: 0.9rem;"></i>
                                        {{ $feature }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

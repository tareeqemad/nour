@extends('layouts.admin')

@section('title', 'الدليل الإرشادي')

@php
    $user = auth()->user();
    $siteName = \App\Models\Setting::get('site_name', 'نور');
    $siteDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'gazarased.com';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/guide.css') }}">
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="الدليل الإرشادي" icon="bi-book" />

                {{-- TOC - Quick Navigation --}}
                <div class="guide-toc">
                    <a href="#overview"><i class="bi bi-house-door"></i> نظرة عامة</a>
                    <a href="#role-guide"><i class="bi bi-person-badge"></i> دليل دورك</a>
                    @if($user->isCompanyOwner())
                        <a href="#work-steps"><i class="bi bi-list-check"></i> خطوات العمل</a>
                    @endif
                    @if($user->isSuperAdmin() || $user->isEnergyAuthority() || $user->isCompanyOwner())
                        <a href="#user-creation"><i class="bi bi-person-plus"></i> إنشاء المستخدمين</a>
                    @endif
                    <a href="#quick-links"><i class="bi bi-link-45deg"></i> وصول سريع</a>
                </div>

                {{-- Section: Overview --}}
                @include('admin.guide.partials.overview')

                {{-- Section: Role Guide --}}
                <div class="d-section" id="role-guide">
                    <div class="d-section-title">
                        <i class="bi bi-person-badge"></i>
                        دليل دورك في النظام
                    </div>

                    <div class="d-accordion" id="roleAccordion">
                        @if($user->isSuperAdmin())
                            @include('admin.guide.partials.super-admin')
                        @elseif($user->isEnergyAuthority())
                            @include('admin.guide.partials.energy-authority')
                        @elseif($user->isAdmin())
                            @include('admin.guide.partials.admin')
                        @elseif($user->isCompanyOwner())
                            @include('admin.guide.partials.company-owner')
                            @include('admin.guide.partials.custom-roles')
                        @elseif($user->isEmployee())
                            @include('admin.guide.partials.employee')
                        @elseif($user->isTechnician())
                            @include('admin.guide.partials.technician')
                        @endif
                    </div>
                </div>

                {{-- Section: Work Steps --}}
                @if($user->isCompanyOwner())
                    @include('admin.guide.partials.work-steps')
                @endif

                {{-- Section: User Creation --}}
                @if($user->isSuperAdmin() || $user->isEnergyAuthority() || $user->isCompanyOwner())
                    @include('admin.guide.partials.user-creation')
                @endif

                {{-- Section: Quick Links --}}
                @include('admin.guide.partials.useful-links')

            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Smooth scroll for TOC links
document.querySelectorAll('.guide-toc a').forEach(a => {
    a.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>
@endpush

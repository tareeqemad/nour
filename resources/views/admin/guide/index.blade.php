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
                <x-admin.card-header title="الدليل الإرشادي" icon="bi-book">
                </x-admin.card-header>

                <div class="card-body pb-4">
                    {{-- نظرة عامة على النظام --}}
                    @include('admin.guide.partials.overview')

                    {{-- دليل الدور الحالي للمستخدم --}}
                    <div class="guide-section">
                        <x-admin.card>
                            <div class="section-header">
                                <h5>
                                    <i class="bi bi-person-badge"></i>
                                    دليل دورك في النظام
                                </h5>
                            </div>

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
                        </x-admin.card>
                    </div>

                    {{-- خطوات العمل للمشغل --}}
                    @if($user->isCompanyOwner())
                        @include('admin.guide.partials.work-steps')
                    @endif

                    {{-- آلية إنشاء المستخدمين --}}
                    @if($user->isSuperAdmin() || $user->isEnergyAuthority() || $user->isCompanyOwner())
                        @include('admin.guide.partials.user-creation')
                    @endif

                    {{-- روابط مفيدة --}}
                    @include('admin.guide.partials.useful-links')
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

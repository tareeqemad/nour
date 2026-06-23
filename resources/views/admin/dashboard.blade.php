@extends('layouts.admin')

@section('title', 'لوحة التحكم')

@php
    $hideBreadcrumb = true;
    $user = auth()->user();
@endphp

@section('content')
<div class="general-page dashboard-page">

    {{-- 1. Welcome Bar --}}
    @include('admin.dashboard.partials.welcome-card')

    {{-- 2. KPI Blocks --}}
    @include('admin.dashboard.partials.statistics-cards')

    @include('admin.dashboard.partials.alerts')
    @include('admin.dashboard.partials.empty-data-hint')

    {{-- 2.5 Billing & Collection Overview (role-scoped inside DashboardService;
         the partial self-hides when $billingDashboard is null) --}}
    @include('admin.dashboard.partials.billing-overview')

    {{-- 3. Charts — always show for non-CivilDefense --}}
    @if(!$user->isCivilDefense())
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-8">
            <div class="d-section h-100">
                <div class="d-section-head">
                    <span class="d-section-title"><i class="bi bi-graph-up me-1"></i> تحليل الأداء — آخر 30 يوم</span>
                </div>
                <div class="d-section-body">
                    <div class="chart-container" style="height: 320px;">
                        <canvas id="advancedEnergyFuelChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="d-section h-100">
                <div class="d-section-head">
                    @if($user->isSuperAdmin() || $user->isEnergyAuthority())
                    <span class="d-section-title"><i class="bi bi-building me-1"></i> الإنتاج حسب المشغل</span>
                    @else
                    <span class="d-section-title"><i class="bi bi-lightning-charge me-1"></i> الإنتاج حسب المولد</span>
                    @endif
                </div>
                <div class="d-section-body">
                    <div class="chart-container" style="height: 320px;">
                        <canvas id="sideChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 4. Bottom Row: Top Operators + Quick Actions --}}
    <div class="row g-3 mb-3">
        @if($user->isEnergyAuthority() || $user->isSuperAdmin())
        <div class="col-12 col-lg-6">
            <div class="d-section">
                <div class="d-section-head">
                    <span class="d-section-title"><i class="bi bi-trophy me-1"></i> أعلى المشغلين إنتاجاً</span>
                    <a href="{{ route('admin.operators.index') }}" class="d-section-link">الكل <i class="bi bi-arrow-left"></i></a>
                </div>
                <div class="d-section-body" id="operators-comparison-container">
                    <x-admin.skeleton type="table" :lines="3" />
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            @include('admin.dashboard.partials.quick-actions')
        </div>
        @else
        <div class="col-12">
            @include('admin.dashboard.partials.quick-actions')
        </div>
        @endif
    </div>

    {{-- 5. Tasks — Technician/CivilDefense --}}
    @if(($user->isTechnician() || $user->isCivilDefense()) && isset($tasksData) && isset($tasksData['tasks']))
        @include('admin.dashboard.partials.tasks')
    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/dashboard.css') }}">
@endpush

@include('admin.dashboard.partials.scripts')

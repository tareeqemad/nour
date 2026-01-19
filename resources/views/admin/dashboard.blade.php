@extends('layouts.admin')

@section('title', 'لوحة التحكم')

@php
    $breadcrumbTitle = 'لوحة التحكم';
    use Carbon\Carbon;
@endphp

@section('content')
<div class="dashboard-page">
    @include('admin.dashboard.partials.welcome-card')

    @include('admin.dashboard.partials.alerts')

    @include('admin.dashboard.partials.quick-actions')

    @include('admin.dashboard.partials.statistics-cards')

    @include('admin.dashboard.partials.operations-statistics')

    @include('admin.dashboard.partials.performance-analysis')

    @include('admin.dashboard.partials.energy-distribution')

    @include('admin.dashboard.partials.maintenance-statistics')

    @include('admin.dashboard.partials.recent-items')

    {{-- المهام (للفني والدفاع المدني) --}}
    @if((auth()->user()->isTechnician() || auth()->user()->isCivilDefense()) && isset($tasksData) && isset($tasksData['tasks']))
        @include('admin.dashboard.partials.tasks')
    @endif

    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/dashboard.css') }}">
@endpush

@include('admin.dashboard.partials.scripts')

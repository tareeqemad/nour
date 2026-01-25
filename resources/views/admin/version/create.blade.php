@extends('layouts.admin')

@section('title', 'إنشاء إصدار جديد')

@php
    $breadcrumbTitle = 'إنشاء إصدار جديد';
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-plus-circle me-2"></i>
                            إنشاء إصدار جديد
                        </h5>
                        <div class="general-subtitle">
                            أضف إصداراً جديداً للمنصة مع سجل التغييرات
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.versions.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @include('admin.version.partials.form', ['nextVersions' => $nextVersions])
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('admin.version.partials.form-scripts', ['nextVersions' => $nextVersions])
@endpush

@extends('layouts.admin')

@section('title', 'تعديل الإصدار ' . $version->version)

@php
    $breadcrumbTitle = 'تعديل الإصدار';
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-pencil me-2"></i>
                            تعديل الإصدار v{{ $version->version }}
                        </h5>
                        <div class="general-subtitle">
                            تعديل بيانات الإصدار وسجل التغييرات
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.versions.update', $version) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @include('admin.version.partials.form', ['version' => $version])
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('admin.version.partials.form-scripts')
@endpush

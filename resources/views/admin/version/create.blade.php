@extends('layouts.admin')

@section('title', 'إنشاء إصدار جديد')

@php
    $breadcrumbTitle = 'إنشاء إصدار جديد';
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form title="إنشاء إصدار جديد" icon="bi-plus-circle" :backRoute="route('admin.versions.index')" />

                <form action="{{ route('admin.versions.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @include('admin.version.partials.form', ['nextVersions' => $nextVersions])
                    </div>
                </form>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('admin.version.partials.form-scripts', ['nextVersions' => $nextVersions])
@endpush

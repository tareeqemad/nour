@extends('layouts.admin')

@section('title', 'تعديل الإصدار ' . $version->version)

@php
    $breadcrumbTitle = 'تعديل الإصدار';
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form :title="'تعديل الإصدار v' . $version->version" icon="bi-pencil" :backRoute="route('admin.versions.index')" />

                <form action="{{ route('admin.versions.update', $version) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @include('admin.version.partials.form', ['version' => $version])
                    </div>
                </form>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('admin.version.partials.form-scripts')
@endpush

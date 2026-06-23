@extends('layouts.admin')

@section('title', 'تعديل مولد')

@php
    $breadcrumbTitle = 'تعديل مولد';
    $breadcrumbParent = 'إدارة المولدات';
    $breadcrumbParentUrl = route('admin.generators.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/generators.css') }}">
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form title="تعديل: {{ $generator->name }}" icon="bi-lightning-charge" :backRoute="route('admin.generators.index')" />

                    <div class="card-body">
                        <form action="{{ route('admin.generators.update', $generator) }}" method="POST" enctype="multipart/form-data" id="generatorForm" novalidate>
                            @csrf
                            @method('PUT')
                            @include('admin.generators.partials.form')
                        </form>
                    </div>

                    {{-- Navigation Buttons --}}
                    <div class="gen-nav-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="navigateTabs(-1)" style="display: none;">
                                <i class="bi bi-arrow-right me-1"></i>السابق
                            </button>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.generators.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>إلغاء
                                </a>
                                <button type="button" class="btn btn-primary" id="nextBtn" onclick="navigateTabs(1)">
                                    التالي<i class="bi bi-arrow-left ms-1"></i>
                                </button>
                                <button type="submit" form="generatorForm" class="btn btn-success" id="submitBtn" style="display: none;">
                                    <i class="bi bi-check-lg me-1"></i>حفظ
                                </button>
                            </div>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    window.GENERATOR_CONSTANTS = {
        material: @json(($constants['material'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
        usage: @json(($constants['usage'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
        measurement_method: @json(($constants['measurement_method'] ?? collect())->map(fn($c) => ['id' => $c->id, 'label' => $c->label])->values()),
    };
    window.generatorFormConfig = {
        isEdit: true,
        isSuperAdmin: @json(auth()->user()->isSuperAdmin()),
        csrfRefreshUrl: '{{ route("admin.generators.edit", $generator) }}',
        redirectUrl: '{{ route("admin.generators.index") }}',
        generateNumberUrl: '{{ url("/admin/generators/generate-number") }}'
    };
</script>
<script src="{{ asset('assets/admin/js/generators-form.js') }}"></script>
@endpush

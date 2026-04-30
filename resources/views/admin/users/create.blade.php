@extends('layouts.admin')

@section('title', 'إضافة مستخدم')

@php
    $authUser = auth()->user();

    $breadcrumbTitle = $authUser->isCompanyOwner() ? 'إضافة موظف/فني' : 'إضافة مستخدم';
    $breadcrumbParent = 'إدارة المستخدمين';
    $breadcrumbParentUrl = route('admin.users.index');

    $mode = 'create';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/users.css') }}">
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form :title="$breadcrumbTitle" icon="bi-person-plus" :backRoute="route('admin.users.index')" />

                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf

                        <div class="card-body">
                            @include('admin.users.partials.form', [
                                'mode' => $mode,
                                'user' => null,
                                'defaultRole' => $defaultRole ?? '',
                                'operatorFieldName' => 'operator_id',
                            ])

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-lg me-1"></i> حفظ
                                </button>
                            </div>
                        </div>
                    </form>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('roleSelect');
            const operatorField = document.getElementById('operatorField');

            if (!roleSelect || !operatorField) return;

            function toggleOperator() {
                const val = roleSelect.value;
                const needOp = (val === '{{ \App\Enums\Role::Employee->value }}' || val === '{{ \App\Enums\Role::Technician->value }}');

                operatorField.style.display = needOp ? '' : 'none';
                const star = document.getElementById('opReqStar');
                if (star) star.style.display = needOp ? '' : 'none';

                if (!needOp) {
                    const opSelect = document.getElementById('operatorSelect');
                    if (opSelect) opSelect.value = '';
                }
            }

            roleSelect.addEventListener('change', toggleOperator);
            toggleOperator();
        });
    </script>
@endpush

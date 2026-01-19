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
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-person-plus me-2"></i>
                                {{ $breadcrumbTitle }}
                            </h5>
                            <div class="general-subtitle">
                                @if($authUser->isCompanyOwner())
                                    سيتم ربط المستخدم تلقائيًا بالمشغل الخاص بك.
                                @else
                                    أنشئ مستخدمًا جديدًا وحدّد الدور وربطه بالمشغل عند الحاجة.
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            رجوع
                        </a>
                    </div>

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
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> حفظ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
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
                const needOp = (val === '{{ \App\Role::Employee->value }}' || val === '{{ \App\Role::Technician->value }}');

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

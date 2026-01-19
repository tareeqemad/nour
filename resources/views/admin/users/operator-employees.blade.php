@extends('layouts.admin')

@section('title', 'موظفي المشغل')

@php
    $breadcrumbTitle = 'موظفي المشغل';
    $breadcrumbParent = 'إدارة المستخدمين';
    $breadcrumbParentUrl = route('admin.users.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/users.css') }}">
@endpush

@section('content')
    <div class="users-page">

        <div class="users-page-header">
            <div>
                <h1 class="users-title">موظفي المشغل</h1>
                <div class="users-subtitle">{{ $operator->name }} — العدد: {{ $employees->total() }}</div>
            </div>

            <a href="{{ route('admin.users.index') }}" class="btn btn-light-subtle">
                <i class="bi bi-arrow-right me-1"></i> رجوع
            </a>
        </div>

        <div class="card ui-card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.operators.employees', $operator) }}" class="users-filters">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-6">
                            <label class="form-label">بحث</label>
                            <div class="ui-input-icon">
                                <i class="bi bi-search icon"></i>
                                <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control"
                                       placeholder="اسم / اسم مستخدم / بريد...">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">الدور</label>
                            <select name="role" class="form-select">
                                <option value="">الكل</option>
                                <option value="{{ \App\Role::Employee->value }}" {{ ($roleFilter ?? '') === \App\Role::Employee->value ? 'selected' : '' }}>موظف</option>
                                <option value="{{ \App\Role::Technician->value }}" {{ ($roleFilter ?? '') === \App\Role::Technician->value ? 'selected' : '' }}>فني</option>
                            </select>
                        </div>
                        <div class="col-lg-3 d-flex gap-2">
                            <button class="btn btn-primary flex-grow-1">بحث</button>
                            <a class="btn btn-light-subtle" href="{{ route('admin.operators.employees', $operator) }}">إلغاء</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table users-table mb-0">
                        <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>اسم المستخدم</th>
                            <th>البريد</th>
                            <th>الدور</th>
                            <th>تاريخ الإنشاء</th>
                            <th>إجراءات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($employees as $emp)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle">{{ mb_substr($emp->name, 0, 1) }}</div>
                                        <div class="fw-semibold">{{ $emp->name }}</div>
                                    </div>
                                </td>
                                <td>{{ $emp->username }}</td>
                                <td>{{ $emp->email }}</td>
                                <td><span class="badge bg-success">{{ $emp->role_name }}</span></td>
                                <td><span class="text-muted">{{ optional($emp->created_at)->format('Y-m-d') }}</span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.users.show', $emp) }}"><i class="bi bi-eye"></i></a>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $emp) }}"><i class="bi bi-pencil"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">لا يوجد موظفين.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($employees->hasPages())
                <div class="card-footer bg-white">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection

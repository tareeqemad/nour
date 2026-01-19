@extends('layouts.admin')

@section('title', 'تفاصيل الدور')

@php
    $breadcrumbTitle = 'تفاصيل الدور';
    $breadcrumbParent = 'إدارة الأدوار';
    $breadcrumbParentUrl = route('admin.roles.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/roles.css') }}">
    <style>
        .info-item {
            margin-bottom: 1rem;
        }
        .info-item:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }
        .info-value {
            font-size: 0.95rem;
            color: #1f2937;
            font-weight: 500;
        }
        .info-value code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .card-header .btn-link {
            color: #212529;
            text-decoration: none;
        }
        .card-header .btn-link:hover {
            color: #0d6efd;
        }
        .card-header .btn-link:focus {
            box-shadow: none;
        }
        .collapse-icon {
            transition: transform 0.3s ease;
            font-size: 0.875rem;
        }
        .card-header .btn-link[aria-expanded="false"] .collapse-icon {
            transform: rotate(180deg);
        }
    </style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-shield-check me-2"></i>
                            {{ $role->label }}
                        </h5>
                        <div class="general-subtitle">
                            @if($role->is_system)
                                <span class="badge bg-danger me-2">دور نظامي</span>
                            @else
                                <span class="badge bg-success me-2">دور مخصص</span>
                            @endif
                            <code class="text-muted">{{ $role->name }}</code>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @can('update', $role)
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-1"></i>
                                تعديل
                            </a>
                        @endcan
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            رجوع
                        </a>
                    </div>
                </div>
                <div class="card-body pb-4">
                    {{-- Statistics Cards --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="card border-0 bg-primary bg-opacity-10">
                                <div class="card-body text-center">
                                    <div class="fs-2 fw-bold text-primary">{{ $role->users_count ?? 0 }}</div>
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-people me-1"></i>
                                        مستخدم
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card border-0 bg-success bg-opacity-10">
                                <div class="card-body text-center">
                                    <div class="fs-2 fw-bold text-success">{{ $role->permissions->count() }}</div>
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-shield-check me-1"></i>
                                        صلاحية
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card border-0 bg-info bg-opacity-10">
                                <div class="card-body text-center">
                                    <div class="fs-2 fw-bold text-info">{{ $role->permissions->groupBy('group')->count() }}</div>
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-folder me-1"></i>
                                        مجموعة صلاحيات
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card border-0 bg-warning bg-opacity-10">
                                <div class="card-body text-center">
                                    <div class="fs-2 fw-bold text-warning">{{ $role->order }}</div>
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-sort-numeric-down me-1"></i>
                                        ترتيب العرض
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Basic Information --}}
                    <div class="general-card mb-3">
                        <div class="general-card-header">
                            <div>
                                <h5 class="general-title">
                                    <i class="bi bi-info-circle me-2"></i>
                                    المعلومات الأساسية
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">
                                            <i class="bi bi-tag text-primary me-2"></i>
                                            اسم الدور
                                        </label>
                                        <div class="info-value">
                                            <code class="text-primary">{{ $role->name }}</code>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">
                                            <i class="bi bi-card-heading text-primary me-2"></i>
                                            التسمية
                                        </label>
                                        <div class="info-value">{{ $role->label }}</div>
                                    </div>
                                </div>
                                @if($role->description)
                                <div class="col-12">
                                    <div class="info-item">
                                        <label class="info-label">
                                            <i class="bi bi-file-text text-primary me-2"></i>
                                            الوصف
                                        </label>
                                        <div class="info-value">{{ $role->description }}</div>
                                    </div>
                                </div>
                                @endif
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">
                                            <i class="bi bi-shield-check text-primary me-2"></i>
                                            نوع الدور
                                        </label>
                                        <div class="info-value">
                                            @if($role->is_system)
                                                <span class="badge bg-danger">نظامي</span>
                                            @else
                                                <span class="badge bg-success">مخصص</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($role->operator)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">
                                            <i class="bi bi-building text-primary me-2"></i>
                                            المشغل المرتبط
                                        </label>
                                        <div class="info-value">{{ $role->operator->name }}</div>
                                    </div>
                                </div>
                                @endif
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">
                                            <i class="bi bi-calendar text-primary me-2"></i>
                                            تاريخ الإنشاء
                                        </label>
                                        <div class="info-value">{{ $role->created_at->format('Y-m-d H:i') }}</div>
                                    </div>
                                </div>
                                @if($role->creator)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">
                                            <i class="bi bi-person-plus text-primary me-2"></i>
                                            أنشأ بواسطة
                                        </label>
                                        <div class="info-value">{{ $role->creator->name }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Permissions Section --}}
                    <div class="general-card">
                        <div class="general-card-header">
                            <div>
                                <h5 class="general-title">
                                    <i class="bi bi-shield-check me-2"></i>
                                    الصلاحيات المرتبطة
                                    <span class="badge bg-primary ms-2">{{ $role->permissions->count() }}</span>
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($role->permissions->count() > 0)
                                <div class="row g-3">
                                    @foreach($role->permissions->groupBy('group') as $group => $groupPermissions)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card border">
                                                <div class="card-header bg-light p-0">
                                                    <h6 class="mb-0 fw-bold">
                                                        <button class="btn btn-link text-decoration-none p-3 w-100 text-start d-flex align-items-center justify-content-between" 
                                                                type="button" 
                                                                data-bs-toggle="collapse" 
                                                                data-bs-target="#permissionGroup{{ $loop->index }}" 
                                                                aria-expanded="true"
                                                                aria-controls="permissionGroup{{ $loop->index }}">
                                                            <span>
                                                                <i class="bi bi-folder me-2"></i>
                                                                {{ $groupPermissions->first()->group_label }}
                                                            </span>
                                                            <span class="badge bg-primary me-2">{{ $groupPermissions->count() }}</span>
                                                            <i class="bi bi-chevron-down collapse-icon"></i>
                                                        </button>
                                                    </h6>
                                                </div>
                                                <div id="permissionGroup{{ $loop->index }}" class="collapse show">
                                                    <div class="card-body">
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach($groupPermissions as $permission)
                                                                <li class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                                                    <div class="d-flex align-items-start">
                                                                        <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                                        <div class="flex-grow-1">
                                                                            <div class="fw-semibold">{{ $permission->label }}</div>
                                                                            @if($permission->description)
                                                                                <small class="text-muted d-block mt-1">{{ $permission->description }}</small>
                                                                            @endif
                                                                            <code class="text-muted small d-block mt-1" style="font-size: 0.75rem;">{{ $permission->name }}</code>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning text-center mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    لا توجد صلاحيات مرتبطة بهذا الدور
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

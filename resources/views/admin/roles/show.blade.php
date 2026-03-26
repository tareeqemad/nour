@extends('layouts.admin')

@section('title', 'تفاصيل الدور')

@php
    $breadcrumbTitle = 'تفاصيل الدور';
    $breadcrumbParent = 'إدارة الأدوار';
    $breadcrumbParentUrl = route('admin.roles.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/roles.css') }}">
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form :title="$role->label" icon="bi-shield-check" :backRoute="route('admin.roles.index')">
                    @can('update', $role)
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i>
                            تعديل
                        </a>
                    @endcan
                </x-admin.card-header-form>
                <div class="card-body">
                    {{-- KPI Cards --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-primary">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div>
                                    <div class="dash-kpi-value">{{ $role->users_count ?? 0 }}</div>
                                    <div class="dash-kpi-label">مستخدم</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-success">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div>
                                    <div class="dash-kpi-value">{{ $role->permissions->count() }}</div>
                                    <div class="dash-kpi-label">صلاحية</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-info">
                                    <i class="bi bi-folder"></i>
                                </div>
                                <div>
                                    <div class="dash-kpi-value">{{ $role->permissions->groupBy('group')->count() }}</div>
                                    <div class="dash-kpi-label">مجموعة</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-warning">
                                    <i class="bi bi-sort-numeric-down"></i>
                                </div>
                                <div>
                                    <div class="dash-kpi-value">{{ $role->order }}</div>
                                    <div class="dash-kpi-label">ترتيب</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Basic Info --}}
                    <div style="background: #FAFCFF; border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 10px; padding: 1.25rem; margin-bottom: 1rem;">
                        <h6 class="fw-bold mb-3" style="color: var(--color-primary, #24308F); font-size: 0.92rem;">
                            <i class="bi bi-info-circle me-2" style="opacity: .75;"></i>
                            المعلومات الأساسية
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label style="font-size: 0.82rem; color: var(--color-text-muted, #5B6780); font-weight: 600;">اسم الدور</label>
                                    <div style="font-size: 0.92rem; color: var(--color-text-main, #1F2937); font-weight: 500; margin-top: 0.25rem;">
                                        <code style="background: var(--badge-primary-bg, #EEF2FF); color: var(--badge-primary-text, #24308F); padding: 2px 8px; border-radius: 5px;">{{ $role->name }}</code>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label style="font-size: 0.82rem; color: var(--color-text-muted, #5B6780); font-weight: 600;">التسمية</label>
                                    <div style="font-size: 0.92rem; color: var(--color-text-main, #1F2937); font-weight: 500; margin-top: 0.25rem;">{{ $role->label }}</div>
                                </div>
                            </div>
                            @if($role->description)
                            <div class="col-12">
                                <div class="mb-3">
                                    <label style="font-size: 0.82rem; color: var(--color-text-muted, #5B6780); font-weight: 600;">الوصف</label>
                                    <div style="font-size: 0.88rem; color: var(--color-text-secondary, #3B4863); margin-top: 0.25rem; line-height: 1.6;">{{ $role->description }}</div>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-4">
                                <label style="font-size: 0.82rem; color: var(--color-text-muted, #5B6780); font-weight: 600;">نوع الدور</label>
                                <div style="margin-top: 0.25rem;">
                                    @if($role->is_system)
                                        <span class="badge-role-system">نظامي</span>
                                    @else
                                        <span class="badge-role-custom">مخصص</span>
                                    @endif
                                </div>
                            </div>
                            @if($role->operator)
                            <div class="col-md-4">
                                <label style="font-size: 0.82rem; color: var(--color-text-muted, #5B6780); font-weight: 600;">المشغل</label>
                                <div style="font-size: 0.92rem; color: var(--color-text-main, #1F2937); margin-top: 0.25rem;">{{ $role->operator->name }}</div>
                            </div>
                            @endif
                            <div class="col-md-4">
                                <label style="font-size: 0.82rem; color: var(--color-text-muted, #5B6780); font-weight: 600;">تاريخ الإنشاء</label>
                                <div style="font-size: 0.88rem; color: var(--color-text-secondary, #3B4863); margin-top: 0.25rem;">{{ $role->created_at->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Permissions --}}
                    <div style="border: 1px solid var(--color-border, #E5E7EB); border-radius: 10px; overflow: hidden;">
                        <div style="background: #FAFCFF; border-bottom: 1px solid var(--color-border-soft, #EDF1F5); padding: 0.75rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
                            <h6 class="mb-0 fw-bold" style="color: var(--color-primary, #24308F); font-size: 0.92rem;">
                                <i class="bi bi-shield-check me-2" style="opacity: .75;"></i>
                                الصلاحيات
                            </h6>
                            <span class="badge-permissions-count">{{ $role->permissions->count() }}</span>
                        </div>
                        <div style="padding: 1.25rem;">
                            @if($role->permissions->count() > 0)
                                <div class="row g-3">
                                    @foreach($role->permissions->groupBy('group') as $group => $groupPermissions)
                                        <div class="col-md-6 col-lg-4">
                                            <div style="border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 8px; overflow: hidden;">
                                                <div style="background: #FAFCFF; padding: 0.6rem 1rem; border-bottom: 1px solid var(--color-border-soft, #EDF1F5); display: flex; align-items: center; justify-content: space-between;">
                                                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--color-text-main, #1F2937);">
                                                        <i class="bi bi-folder me-1" style="color: var(--color-primary, #24308F); opacity: .6;"></i>
                                                        {{ $groupPermissions->first()->group_label }}
                                                    </span>
                                                    <span class="badge-neutral" style="font-size: 0.7rem;">{{ $groupPermissions->count() }}</span>
                                                </div>
                                                <div style="padding: 0.75rem 1rem;">
                                                    @foreach($groupPermissions as $permission)
                                                        <div class="d-flex align-items-start gap-2 {{ !$loop->last ? 'mb-2 pb-2' : '' }}" style="{{ !$loop->last ? 'border-bottom: 1px solid var(--color-border-soft, #EDF1F5);' : '' }}">
                                                            <i class="bi bi-check-circle-fill mt-1" style="color: var(--color-success, #10B981); font-size: 0.8rem;"></i>
                                                            <div>
                                                                <div style="font-size: 0.82rem; font-weight: 600; color: var(--color-text-main, #1F2937);">{{ $permission->label }}</div>
                                                                @if($permission->description)
                                                                    <div style="font-size: 0.75rem; color: var(--color-text-muted, #5B6780); margin-top: 0.15rem;">{{ $permission->description }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4" style="color: var(--color-text-muted, #5B6780);">
                                    <i class="bi bi-shield-x" style="font-size: 2rem; opacity: .4;"></i>
                                    <p class="mt-2 mb-0">لا توجد صلاحيات مرتبطة بهذا الدور</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

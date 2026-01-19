@extends('layouts.admin')

@section('title', 'تفاصيل سجل الصلاحية')

@php
    $breadcrumbTitle = 'تفاصيل سجل الصلاحية';
@endphp

@push('styles')
    <style>
        .perm-audit-detail-page {
            --r-border: #e5e7eb;
            --r-border2: #eef2f7;
            --r-surface: #ffffff;
            --r-subtle: #f8fafc;
            --r-text: #0f172a;
            --r-muted: #64748b;
            margin-bottom: 2rem;
        }

        .perm-audit-detail-page .perm-audit-card {
            position: relative;
            border: 1px solid var(--r-border);
            border-radius: 14px;
            background: var(--r-surface);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(15, 23, 42, .04);
        }

        .perm-audit-detail-page .perm-audit-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: rgba(var(--primary-rgb), .85);
        }

        .perm-audit-detail-page .perm-audit-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--r-border2);
            background: var(--r-surface);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .perm-audit-detail-page .perm-detail-title {
            font-weight: 900;
            font-size: 1.05rem;
            margin: 0;
            color: var(--r-text);
        }

        .perm-audit-detail-page .perm-detail-subtitle {
            margin-top: .25rem;
            color: var(--r-muted);
            font-size: .85rem;
        }

        .perm-detail-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .75rem 0;
            border-bottom: 1px solid var(--r-border2);
        }

        .perm-detail-row:last-child {
            border-bottom: none;
        }

        .perm-detail-label {
            font-weight: 700;
            color: var(--r-muted);
            font-size: .85rem;
            min-width: 150px;
        }

        .perm-detail-value {
            flex: 1;
            text-align: right;
            font-weight: 600;
            color: var(--r-text);
        }

        .perm-detail-badge {
            font-weight: 600;
            font-size: .85rem;
            padding: .4rem .75rem;
            border-radius: 8px;
        }

        .perm-detail-badge.granted {
            background: #d1fae5;
            color: #065f46;
        }

        .perm-detail-badge.revoked {
            background: #fee2e2;
            color: #991b1b;
        }

        .perm-detail-user {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .perm-detail-user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(var(--primary-rgb), .1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: rgba(var(--primary-rgb), 1);
            font-size: 1.1rem;
        }

        .perm-detail-notes {
            background: var(--r-subtle);
            border: 1px solid var(--r-border2);
            border-radius: 12px;
            padding: 1rem;
            margin-top: .5rem;
        }
    </style>
@endpush

@section('content')
<div class="perm-audit-detail-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="perm-audit-card">
                <div class="perm-audit-card-header">
                    <div>
                        <h5 class="perm-detail-title">
                            <i class="bi bi-shield-check me-2"></i>
                            تفاصيل سجل الصلاحية
                        </h5>
                        <div class="perm-detail-subtitle">
                            عرض تفاصيل التغيير على الصلاحية
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.permission-audit-logs.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            رجوع
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-person me-2"></i>
                                        المستخدم
                                    </h6>
                                    <div class="perm-detail-user mb-3">
                                        <div class="perm-detail-user-avatar">
                                            {{ mb_substr($permissionAuditLog->user->name ?? '?', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $permissionAuditLog->user->name ?? '—' }}</div>
                                            <div class="small text-muted">{{ $permissionAuditLog->user->username ?? '—' }}</div>
                                            <div class="small text-muted">{{ $permissionAuditLog->user->email ?? '—' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-shield-check me-2"></i>
                                        الصلاحية
                                    </h6>
                                    <div>
                                        <div class="fw-bold mb-2">{{ $permissionAuditLog->permission->label ?? '—' }}</div>
                                        <code class="small d-block mb-2">{{ $permissionAuditLog->permission->name ?? '—' }}</code>
                                        @if($permissionAuditLog->permission->description)
                                            <div class="small text-muted">{{ $permissionAuditLog->permission->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-activity me-2"></i>
                                        الإجراء
                                    </h6>
                                    <span class="perm-detail-badge {{ $permissionAuditLog->action === 'granted' ? 'granted' : 'revoked' }}">
                                        @if($permissionAuditLog->action === 'granted')
                                            <i class="bi bi-check-circle me-1"></i>
                                            منح الصلاحية
                                        @else
                                            <i class="bi bi-x-circle me-1"></i>
                                            إلغاء/منع الصلاحية
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-person-badge me-2"></i>
                                        نفذ بواسطة
                                    </h6>
                                    <div class="perm-detail-user">
                                        <div class="perm-detail-user-avatar">
                                            {{ mb_substr($permissionAuditLog->performedBy->name ?? '?', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $permissionAuditLog->performedBy->name ?? '—' }}</div>
                                            <div class="small text-muted">{{ $permissionAuditLog->performedBy->username ?? '—' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-calendar me-2"></i>
                                        التاريخ والوقت
                                    </h6>
                                    <div>
                                        <div class="fw-bold mb-1">{{ $permissionAuditLog->created_at->format('Y-m-d') }}</div>
                                        <div class="small text-muted">{{ $permissionAuditLog->created_at->format('H:i:s') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($permissionAuditLog->notes)
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-sticky me-2"></i>
                                            الملاحظات
                                        </h6>
                                        <div class="text-muted">
                                            {{ $permissionAuditLog->notes }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

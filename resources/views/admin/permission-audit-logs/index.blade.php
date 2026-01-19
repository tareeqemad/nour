@extends('layouts.admin')

@section('title', 'سجل تغييرات الصلاحيات')

@php
    $breadcrumbTitle = 'سجل تغييرات الصلاحيات';
    $isSuperAdmin = auth()->user()->isSuperAdmin();
    $isCompanyOwner = auth()->user()->isCompanyOwner();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <style>
        .perm-audit-badge {
            font-weight: 600;
            font-size: .75rem;
            padding: .35rem .65rem;
            border-radius: 8px;
        }

        .perm-audit-badge.granted {
            background: #d1fae5;
            color: #065f46;
        }

        .perm-audit-badge.revoked {
            background: #fee2e2;
            color: #991b1b;
        }

        .perm-audit-user {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .perm-audit-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(var(--primary-rgb), .12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: rgba(var(--primary-rgb), .95);
            font-size: 0.875rem;
            flex-shrink: 0;
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
                            سجل تغييرات الصلاحيات
                        </h5>
                        <div class="general-subtitle">
                            {{ $isSuperAdmin ? 'متابعة جميع التغييرات على الصلاحيات في النظام' : 'متابعة تغييرات الصلاحيات للموظفين والفنيين التابعين لمشغلك' }}
                        </div>
                    </div>
                </div>

                <div class="card-body pb-4">
                    <div class="table-responsive data-table-container" id="auditLogsTableContainer">
                        @if($auditLogs->count() > 0)
                            <table class="table table-hover align-middle mb-0 general-table">
                                <thead>
                                    <tr>
                                        <th style="min-width: 200px;">المستخدم</th>
                                        <th style="min-width: 200px;">الصلاحية</th>
                                        <th style="min-width: 120px;">الإجراء</th>
                                        <th style="min-width: 180px;">نفذ بواسطة</th>
                                        <th style="min-width: 150px;">التاريخ والوقت</th>
                                        <th style="min-width: 100px;">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auditLogs as $log)
                                        <tr>
                                            <td>
                                                <div class="perm-audit-user">
                                                    <div class="perm-audit-user-avatar">
                                                        {{ mb_substr($log->user->name ?? '?', 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $log->user->name ?? '—' }}</div>
                                                        <div class="small text-muted">{{ $log->user->username ?? '—' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $log->permission->label ?? '—' }}</div>
                                                    <code class="small text-muted">{{ $log->permission->name ?? '—' }}</code>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="perm-audit-badge {{ $log->action === 'granted' ? 'granted' : 'revoked' }}">
                                                    @if($log->action === 'granted')
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        منح
                                                    @else
                                                        <i class="bi bi-x-circle me-1"></i>
                                                        إلغاء/منع
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $log->performedBy->name ?? '—' }}</div>
                                                    <div class="small text-muted">{{ $log->performedBy->username ?? '—' }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $log->created_at->format('Y-m-d') }}</div>
                                                    <div class="small text-muted">{{ $log->created_at->format('H:i:s') }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.permission-audit-logs.show', $log) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="عرض التفاصيل">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <div class="fw-bold">لا توجد سجلات</div>
                                <div>لم يتم تسجيل أي تغييرات على الصلاحيات بعد</div>
                            </div>
                        @endif
                    </div>

                    @if($auditLogs->hasPages())
                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                            <div class="small text-muted">
                                عرض {{ $auditLogs->firstItem() ?? 0 }} - {{ $auditLogs->lastItem() ?? 0 }} من {{ $auditLogs->total() }}
                            </div>
                            <nav>
                                {{ $auditLogs->links() }}
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

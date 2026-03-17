@extends('layouts.admin')

@section('title', 'المستخدمين النشطين')

@php
    $breadcrumbTitle = 'المستخدمين النشطين';
    $breadcrumbParent = 'إدارة النظام';
    $breadcrumbParentUrl = route('admin.dashboard');
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
                                <i class="bi bi-activity me-2"></i>
                                المستخدمين النشطين
                                <span class="badge bg-primary fw-normal ms-2">{{ $activeUsers->count() }}</span>
                            </h5>
                        </div>
                    </div>

                    <div class="card-body pt-3">
                        @if($activeUsers->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-people text-muted" style="font-size: 2.5rem;"></i>
                                <p class="text-muted mt-2 mb-0">لا يوجد مستخدمين نشطين حالياً</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 general-table">
                                    <thead>
                                        <tr>
                                            <th>المستخدم</th>
                                            <th class="text-center">الدور</th>
                                            <th class="text-center">آخر نشاط</th>
                                            <th class="text-center">الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activeUsers as $user)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar-pill flex-shrink-0">{{ mb_substr($user->name, 0, 1) }}</div>
                                                        <div>
                                                            <div class="fw-bold">{{ $user->name }}</div>
                                                            <div class="text-muted small">{{ $user->username }}{{ $user->phone ? ' · ' . $user->phone : '' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $user->isSuperAdmin() ? 'danger' : ($user->isAdmin() ? 'info' : ($user->isEnergyAuthority() ? 'warning' : ($user->isCompanyOwner() ? 'primary' : 'success'))) }}">
                                                        {{ $user->getRoleLabel() }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($user->last_activity)
                                                        <div>{{ $user->last_activity->diffForHumans() }}</div>
                                                        <small class="text-muted">{{ $user->last_activity->format('H:i:s') }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($user->last_activity && $user->last_activity->diffInMinutes(now()) <= 5)
                                                        <span class="badge bg-success"><i class="bi bi-circle-fill me-1" style="font-size:.5rem;"></i>نشط الآن</span>
                                                    @elseif($user->last_activity && $user->last_activity->diffInMinutes(now()) <= 15)
                                                        <span class="badge bg-info">نشط</span>
                                                    @else
                                                        <span class="badge bg-secondary">غير نشط</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($stats['by_role']->count() > 1)
                                <div class="general-stats mt-3 pt-3 border-top">
                                    @foreach($stats['by_role'] as $roleValue => $count)
                                        <div class="general-stat">
                                            <div class="label">{{ \App\Models\User::getRoleLabelFromValue($roleValue) }}</div>
                                            <div class="value">{{ $count }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        setInterval(function() { location.reload(); }, 30000);
    </script>
@endpush

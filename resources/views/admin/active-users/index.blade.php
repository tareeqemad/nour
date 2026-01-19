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
                            </h5>
                            <div class="general-subtitle">
                                المستخدمين الذين فتحوا النظام في آخر 15 دقيقة
                                <span class="badge bg-primary ms-2">{{ $activeUsers->count() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if($activeUsers->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-3">لا يوجد مستخدمين نشطين حالياً</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>المستخدم</th>
                                            <th>الدور</th>
                                            <th>البريد الإلكتروني</th>
                                            <th>رقم الجوال</th>
                                            <th>آخر نشاط</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activeUsers as $user)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-2">
                                                            <div class="avatar-title bg-primary text-white rounded-circle">
                                                                {{ mb_substr($user->name, 0, 1) }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">{{ $user->name }}</div>
                                                            <small class="text-muted">{{ $user->username }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $user->isSuperAdmin() ? 'danger' : ($user->isAdmin() ? 'info' : ($user->isEnergyAuthority() ? 'warning' : ($user->isCompanyOwner() ? 'primary' : 'success'))) }}">
                                                        {{ $user->getRoleLabel() }}
                                                    </span>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->phone ?? '-' }}</td>
                                                <td>
                                                    @if($user->last_activity)
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-circle-fill text-success me-2" style="font-size: 0.5rem;"></i>
                                                            <span>{{ $user->last_activity->diffForHumans() }}</span>
                                                        </div>
                                                        <small class="text-muted">{{ $user->last_activity->format('Y-m-d H:i:s') }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->last_activity && $user->last_activity->diffInMinutes(now()) <= 5)
                                                        <span class="badge bg-success">نشط الآن</span>
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

                            @if($stats['by_role']->isNotEmpty())
                                <div class="mt-4 pt-4 border-top">
                                    <h6 class="fw-bold mb-3">الإحصائيات حسب الدور:</h6>
                                    <div class="row g-3">
                                        @foreach($stats['by_role'] as $roleValue => $count)
                                            @php
                                                $roleLabel = \App\Models\User::getRoleLabelFromValue($roleValue);
                                            @endphp
                                            <div class="col-md-3">
                                                <div class="card border">
                                                    <div class="card-body text-center">
                                                        <div class="h4 mb-0">{{ $count }}</div>
                                                        <small class="text-muted">{{ $roleLabel }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
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
        // تحديث الصفحة كل 30 ثانية
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
@endpush

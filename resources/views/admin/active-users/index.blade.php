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
            <x-admin.card>
                <x-admin.card-header title="المستخدمين النشطين" icon="bi-activity">
                    <x-slot:actions>
                        <span class="stat-chip"><strong>{{ $activeUsers->count() }}</strong></span>
                    </x-slot:actions>
                </x-admin.card-header>

                <div class="card-body">
                    @if($activeUsers->isEmpty())
                        <x-admin.empty-state icon="bi-people" message="لا يوجد مستخدمين نشطين حالياً" />
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
                                                        <div class="fw-bold" style="color: var(--color-text-main, #1F2937);">{{ $user->name }}</div>
                                                        <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780);">{{ $user->username }}{{ $user->phone ? ' · ' . $user->phone : '' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <x-admin.badge type="primary">{{ $user->getRoleLabel() }}</x-admin.badge>
                                            </td>
                                            <td class="text-center">
                                                @if($user->last_activity)
                                                    <div style="font-size: 0.85rem; color: var(--color-text-main, #1F2937);">{{ $user->last_activity->diffForHumans() }}</div>
                                                    <small style="color: var(--color-text-muted, #5B6780);">{{ $user->last_activity->format('H:i:s') }}</small>
                                                @else
                                                    <span style="color: var(--color-text-light, #98A2B3);">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($user->last_activity && $user->last_activity->diffInMinutes(now()) <= 5)
                                                    <x-admin.badge type="success">
                                                        <i class="bi bi-circle-fill me-1" style="font-size: .45rem;"></i>نشط الآن
                                                    </x-admin.badge>
                                                @elseif($user->last_activity && $user->last_activity->diffInMinutes(now()) <= 15)
                                                    <x-admin.badge type="info">نشط</x-admin.badge>
                                                @else
                                                    <x-admin.badge type="neutral">غير نشط</x-admin.badge>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($stats['by_role']->count() > 1)
                            <div class="general-stats mt-3 pt-3" style="border-top: 1px solid var(--color-border-soft, #EDF1F5);">
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
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    setInterval(function() { location.reload(); }, 30000);
</script>
@endpush

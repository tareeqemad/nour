@extends('layouts.admin')

@section('title', 'تفاصيل سجل النشاط')

@php
    $breadcrumbTitle = 'تفاصيل سجل النشاط';
    $breadcrumbParent = 'سجل النشاطات';
    $breadcrumbParentUrl = route('admin.activity-logs.index');
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-info-circle me-2"></i>
                    تفاصيل سجل النشاط #{{ $auditLog->id }}
                </h5>
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-right me-1"></i>
                    رجوع
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">المستخدم</label>
                            <div>
                                @if($auditLog->user)
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-bold">{{ $auditLog->user->name }}</div>
                                            <small class="text-muted">{{ $auditLog->user->username }} - {{ $auditLog->user->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">الإجراء</label>
                            <div>
                                @php
                                    $actionColors = [
                                        'create' => 'success',
                                        'update' => 'primary',
                                        'delete' => 'danger',
                                        'view' => 'info',
                                        'login' => 'success',
                                        'logout' => 'secondary',
                                    ];
                                    $actionLabels = [
                                        'create' => 'إنشاء',
                                        'update' => 'تحديث',
                                        'delete' => 'حذف',
                                        'view' => 'عرض',
                                        'login' => 'دخول',
                                        'logout' => 'خروج',
                                    ];
                                    $color = $actionColors[$auditLog->action] ?? 'secondary';
                                    $label = $actionLabels[$auditLog->action] ?? $auditLog->action;
                                @endphp
                                <span class="badge bg-{{ $color }} fs-6">{{ $label }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">نوع الموديل</label>
                            <div>
                                @if($auditLog->model_type)
                                    <code>{{ $auditLog->model_type }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">معرف الموديل</label>
                            <div>
                                @if($auditLog->model_id)
                                    <code>#{{ $auditLog->model_id }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">الوصف</label>
                            <div>
                                @if($auditLog->description)
                                    <p class="mb-0">{{ $auditLog->description }}</p>
                                @else
                                    <span class="text-muted">لا يوجد وصف</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">الوقت</label>
                            <div>
                                <div>{{ $auditLog->created_at->format('Y-m-d H:i:s') }}</div>
                                <small class="text-muted">{{ $auditLog->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>

                    @if($auditLog->route)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">المسار</label>
                                <div>
                                    <code>{{ $auditLog->route }}</code>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($auditLog->ip_address)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">عنوان IP</label>
                                <div>
                                    <code>{{ $auditLog->ip_address }}</code>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($auditLog->old_values && !empty($auditLog->old_values))
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">القيم القديمة</label>
                                <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                        </div>
                    @endif

                    @if($auditLog->new_values && !empty($auditLog->new_values))
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">القيم الجديدة</label>
                                <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-top">
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right me-2"></i>
                        رجوع
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




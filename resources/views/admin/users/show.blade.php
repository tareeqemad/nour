@extends('layouts.admin')

@section('title', 'عرض المستخدم: ' . $user->name)

@php
    $breadcrumbTitle = 'عرض المستخدم';
    $breadcrumbParent = 'إدارة المستخدمين';
    $breadcrumbParentUrl = route('admin.users.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-person me-2"></i>
                            عرض المستخدم: {{ $user->name }}
                        </h5>
                        <div class="general-subtitle">
                            تفاصيل المستخدم والصلاحيات
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @can('update', $user)
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-1"></i>
                                تعديل
                            </a>
                        @endcan
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة
                        </a>
                    </div>
                </div>

                <div class="card-body pb-4">
                    {{-- المعلومات الأساسية --}}
                    <div class="info-section mb-4">
                        <h5 class="mb-4 fw-bold">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            المعلومات الأساسية
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-person text-primary me-2"></i>
                                    الاسم (عربي)
                                </div>
                                <div class="info-value">{{ $user->name }}</div>
                            </div>
                            @if($user->name_en)
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-person text-primary me-2"></i>
                                    الاسم (إنجليزي)
                                </div>
                                <div class="info-value">{{ $user->name_en }}</div>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-at text-primary me-2"></i>
                                    اسم المستخدم
                                </div>
                                <div class="info-value">
                                    <code class="text-primary">{{ $user->username }}</code>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-envelope text-primary me-2"></i>
                                    البريد الإلكتروني
                                </div>
                                <div class="info-value">
                                    @if($user->email)
                                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </div>
                            </div>
                            @if($user->phone)
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-phone text-primary me-2"></i>
                                    رقم الجوال
                                </div>
                                <div class="info-value">
                                    <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-shield-check text-primary me-2"></i>
                                    الدور
                                </div>
                                <div class="info-value">
                                    @php
                                        $roleLabels = [
                                            'super_admin' => 'مدير النظام',
                                            'admin' => 'سلطة الطاقة',
                                            'energy_authority' => 'سلطة الطاقة',
                                            'company_owner' => 'مشغل',
                                            'employee' => 'موظف',
                                            'technician' => 'فني',
                                        ];
                                        $roleLabel = $roleLabels[$user->role->value] ?? $user->role->value;
                                    @endphp
                                    <span class="badge bg-primary">{{ $roleLabel }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-activity text-primary me-2"></i>
                                    الحالة
                                </div>
                                <div class="info-value">
                                    @if($user->status === 'active')
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-danger">غير فعال</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-calendar text-primary me-2"></i>
                                    تاريخ الإنشاء
                                </div>
                                <div class="info-value">{{ $user->created_at->format('Y-m-d H:i') }}</div>
                            </div>
                            @if($user->updated_at && $user->updated_at != $user->created_at)
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-clock-history text-primary me-2"></i>
                                    آخر تحديث
                                </div>
                                <div class="info-value">{{ $user->updated_at->format('Y-m-d H:i') }}</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- معلومات المشغل (إذا كان المستخدم مشغل أو سوبر أدمن) --}}
                    @if($operator)
                    <div class="info-section mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-building me-2 text-primary"></i>
                                ملف المشغل
                            </h5>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isEnergyAuthority())
                                <a href="{{ route('admin.operators.profile', ['operator_id' => $operator->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye me-1"></i>
                                    عرض ملف المشغل الكامل
                                </a>
                            @elseif($user->isCompanyOwner())
                                <a href="{{ route('admin.operators.profile') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye me-1"></i>
                                    عرض ملف المشغل الكامل
                                </a>
                            @endif
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-building text-primary me-2"></i>
                                    اسم المشغل
                                </div>
                                <div class="info-value">{{ $operator->name }}</div>
                            </div>
                            @if($operator->name_en)
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-building text-primary me-2"></i>
                                    اسم المشغل (إنجليزي)
                                </div>
                                <div class="info-value">{{ $operator->name_en }}</div>
                            </div>
                            @endif
                            @if($operator->unit_code)
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-hash text-primary me-2"></i>
                                    كود الوحدة
                                </div>
                                <div class="info-value">
                                    <code class="text-primary">{{ $operator->unit_code }}</code>
                                </div>
                            </div>
                            @endif
                            @if($operator->phone)
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-phone text-primary me-2"></i>
                                    رقم الهاتف
                                </div>
                                <div class="info-value">
                                    <a href="tel:{{ $operator->phone }}">{{ $operator->phone }}</a>
                                </div>
                            </div>
                            @endif
                            @if($operator->email)
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-envelope text-primary me-2"></i>
                                    البريد الإلكتروني
                                </div>
                                <div class="info-value">
                                    <a href="mailto:{{ $operator->email }}">{{ $operator->email }}</a>
                                </div>
                            </div>
                            @endif
                            @if($operator->cityDetail)
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-geo-alt text-primary me-2"></i>
                                    المدينة
                                </div>
                                <div class="info-value">{{ $operator->cityDetail->label }}</div>
                            </div>
                            @endif
                            @if($operator->total_capacity)
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-speedometer2 text-primary me-2"></i>
                                    القدرة الإجمالية
                                </div>
                                <div class="info-value">{{ number_format($operator->total_capacity, 2) }} KVA</div>
                            </div>
                            @endif
                            @if($operator->generationUnits)
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-lightning-charge text-primary me-2"></i>
                                    عدد وحدات التوليد
                                </div>
                                <div class="info-value">
                                    <span class="badge bg-info">{{ $operator->generationUnits->count() }}</span>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-activity text-primary me-2"></i>
                                    حالة المشغل
                                </div>
                                <div class="info-value">
                                    @if($operator->status === 'active')
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-secondary">غير فعال</span>
                                    @endif
                                </div>
                            </div>
                            @if($operator->is_approved !== null)
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-check-circle text-primary me-2"></i>
                                    حالة الاعتماد
                                </div>
                                <div class="info-value">
                                    @if($operator->is_approved)
                                        <span class="badge bg-success">معتمد</span>
                                    @else
                                        <span class="badge bg-warning">في انتظار الاعتماد</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- المشغلين المرتبطين (للموظفين والفنيين) --}}
                    @if(($user->isEmployee() || $user->isTechnician()) && $user->operators->count() > 0)
                    <div class="info-section mb-4">
                        <h5 class="mb-4 fw-bold">
                            <i class="bi bi-building me-2 text-primary"></i>
                            المشغلين المرتبطين
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>اسم المشغل</th>
                                        <th>كود الوحدة</th>
                                        <th>الحالة</th>
                                        <th class="text-end">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->operators as $op)
                                    <tr>
                                        <td>{{ $op->name }}</td>
                                        <td>
                                            @if($op->unit_code)
                                                <code class="text-primary">{{ $op->unit_code }}</code>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($op->status === 'active')
                                                <span class="badge bg-success">فعال</span>
                                            @else
                                                <span class="badge bg-secondary">غير فعال</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.operators.show', $op) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- الصلاحيات --}}
                    @if($user->permissions->count() > 0)
                    <div class="info-section mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-shield-check me-2 text-primary"></i>
                                الصلاحيات الممنوحة
                            </h5>
                            <a href="{{ route('admin.permissions.index', ['user_id' => $user->id]) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-gear me-1"></i>
                                إدارة الصلاحيات
                            </a>
                        </div>
                        <div class="row g-2">
                            @foreach($user->permissions->groupBy('group') as $group => $permissions)
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-2">
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold mb-2 text-primary">{{ $permissions->first()->group_label ?? $group }}</h6>
                                        <ul class="list-unstyled mb-0">
                                            @foreach($permissions as $perm)
                                            <li class="small">
                                                <i class="bi bi-check-circle text-success me-1"></i>
                                                {{ $perm->label }}
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="info-section mb-4">
                        <h5 class="mb-3 fw-bold">
                            <i class="bi bi-shield-check me-2 text-primary"></i>
                            الصلاحيات
                        </h5>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            لا توجد صلاحيات مخصصة لهذا المستخدم. الصلاحيات تُحدد حسب الدور.
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.info-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.info-value {
    color: #212529;
    font-size: 1rem;
}
.info-value code {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.9em;
}
</style>
@endsection

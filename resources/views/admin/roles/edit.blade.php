@extends('layouts.admin')

@section('title', 'تعديل الدور')

@php
    $breadcrumbTitle = 'تعديل الدور';
    $breadcrumbParent = 'إدارة الأدوار';
    $breadcrumbParentUrl = route('admin.roles.index');
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-pencil-square me-2"></i>
                            تعديل الدور
                        </h5>
                        <div class="general-subtitle">
                            تعديل بيانات الدور والصلاحيات
                        </div>
                    </div>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right me-2"></i>
                        رجوع
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.roles.update', $role) }}" method="POST" id="roleForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-info-circle text-primary me-2"></i>
                                بيانات الدور الأساسية
                            </h6>

                            <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">اسم الدور (مفتاح) <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="roleName" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $role->name) }}" 
                                       {{ $role->is_system ? 'readonly' : '' }} 
                                       pattern="[a-z_]+"
                                       placeholder="مثال: custom_role">
                                @if($role->is_system)
                                    <small class="form-text text-warning">لا يمكن تعديل اسم الدور النظامي</small>
                                @else
                                    <small class="form-text">يجب أن يكون باللغة الإنجليزية، أحرف صغيرة وشرطات سفلية فقط</small>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">التسمية (العربية) <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control @error('label') is-invalid @enderror" 
                                       value="{{ old('label', $role->label) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3">{{ old('description', $role->description) }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" 
                                       value="{{ old('order', $role->order) }}" min="0">
                            </div>
                            
                            @if(auth()->user()->isSuperAdmin() && !$role->is_system)
                                <div class="col-md-6">
                                    <label class="form-label">المشغل (اختياري)</label>
                                    <select name="operator_id" 
                                            class="form-select @error('operator_id') is-invalid @enderror">
                                        <option value="">دور عام (غير معرف لمشغل)</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->id }}" {{ old('operator_id', $role->operator_id) == $operator->id ? 'selected' : '' }}>
                                                {{ $operator->name }}
                                                @if($operator->unit_number)
                                                    - {{ $operator->unit_number }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('operator_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        إذا تركت فارغاً، سيكون الدور عاماً (المشغلين لا يشوفونه). 
                                        إذا اخترت مشغل، سيكون الدور خاص بهذا المشغل فقط.
                                    </small>
                                </div>
                            @endif

                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-shield-check text-primary me-2"></i>
                                الصلاحيات
                            </h6>

                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle me-2"></i>
                                <div>
                                    <strong>اختيار الصلاحيات:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>يمكنك اختيار مجموعة كاملة من الصلاحيات أو اختيار صلاحيات محددة</li>
                                        <li>الصلاحيات المحددة هي التي سيحصل عليها المستخدمون المرتبطون بهذا الدور</li>
                                        @if(auth()->user()->isCompanyOwner())
                                            <li class="text-warning"><strong>ملاحظة:</strong> الصلاحيات النظامية (إدارة المستخدمين والمشغلين) غير متاحة للمشغلين</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            <div class="permissions-container">
                                @foreach($permissions as $group => $groupPermissions)
                                    <div class="permission-group">
                                        <div class="permission-group-header">
                                            <input type="checkbox" 
                                                   class="form-check-input group-checkbox" 
                                                   data-group="{{ $group }}" 
                                                   id="group_{{ $group }}">
                                            <label class="form-check-label fw-bold" for="group_{{ $group }}">
                                                {{ $groupPermissions->first()->group_label }}
                                                <span class="badge bg-primary ms-2">{{ $groupPermissions->count() }}</span>
                                            </label>
                                        </div>
                                        <div class="permission-items">
                                            @foreach($groupPermissions as $permission)
                                                <div class="permission-item">
                                                    <div class="form-check">
                                                        <input class="form-check-input permission-checkbox" 
                                                               type="checkbox" 
                                                               name="permissions[]" 
                                                               value="{{ $permission->id }}" 
                                                               id="permission_{{ $permission->id }}"
                                                               data-group="{{ $group }}"
                                                               {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                            <span class="permission-name">{{ $permission->label }}</span>
                                                            @if($permission->description)
                                                                <span class="permission-description">{{ $permission->description }}</span>
                                                            @endif
                                                            <span class="permission-key">{{ $permission->name }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-lg me-2"></i>
                                حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/roles-forms.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('.group-checkbox').on('change', function() {
            const group = $(this).data('group');
            const isChecked = $(this).is(':checked');
            
            $('.permission-checkbox[data-group="' + group + '"]').prop('checked', isChecked);
        });
        
        $('.permission-checkbox').on('change', function() {
            const group = $(this).data('group');
            const groupCheckbox = $('#group_' + group);
            const groupPermissions = $('.permission-checkbox[data-group="' + group + '"]');
            const checkedCount = groupPermissions.filter(':checked').length;
            
            if (checkedCount === 0) {
                groupCheckbox.prop('checked', false).prop('indeterminate', false);
            } else if (checkedCount === groupPermissions.length) {
                groupCheckbox.prop('checked', true).prop('indeterminate', false);
            } else {
                groupCheckbox.prop('checked', false).prop('indeterminate', true);
            }
        });
        
        $('.group-checkbox').each(function() {
            const group = $(this).data('group');
            const groupPermissions = $('.permission-checkbox[data-group="' + group + '"]');
            const checkedCount = groupPermissions.filter(':checked').length;
            
            if (checkedCount === groupPermissions.length) {
                $(this).prop('checked', true);
            } else if (checkedCount > 0) {
                $(this).prop('indeterminate', true);
            }
        });
    });
</script>
@endpush

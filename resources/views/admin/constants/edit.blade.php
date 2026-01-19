@extends('layouts.admin')

@section('title', 'تعديل الثابت')

@php
    $breadcrumbTitle = 'تعديل الثابت';
    $breadcrumbParent = 'إدارة الثوابت';
    $breadcrumbParentUrl = route('admin.constants.index');
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/constants.css') }}">
<style>
    .editable-field.is-saving {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }
    .editable-field.is-saved {
        border-color: #198754;
        background-color: #d1e7dd;
    }
    .editable-field.is-invalid {
        border-color: #dc3545;
        background-color: #f8d7da;
    }
    .editable-field:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    .editable-field {
        transition: all 0.3s ease;
    }
</style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <!-- Card تعديل الثابت الرئيسي -->
            <div class="general-card mb-4">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-pencil me-2"></i>
                            تعديل الثابت الرئيسي
                        </h5>
                        <div class="general-subtitle">
                            تعديل بيانات الثابت رقم {{ $constant->constant_number }}
                        </div>
                    </div>
                    <a href="{{ route('admin.constants.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right me-2"></i>
                        رجوع
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.constants.update', $constant) }}" method="POST" id="constantForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">رقم الثابت <span class="text-danger">*</span></label>
                                <input type="number" name="constant_number" class="form-control @error('constant_number') is-invalid @enderror" 
                                       value="{{ old('constant_number', $constant->constant_number) }}">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">اسم الثابت <span class="text-danger">*</span></label>
                                <input type="text" name="constant_name" class="form-control @error('constant_name') is-invalid @enderror" 
                                       value="{{ old('constant_name', $constant->constant_name) }}">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3">{{ old('description', $constant->description) }}</textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" 
                                       value="{{ old('order', $constant->order) }}" min="0">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">الحالة</label>
                                <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                    <option value="1" {{ old('is_active', $constant->is_active) ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ !old('is_active', $constant->is_active) ? 'selected' : '' }}>غير نشط</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Card تفاصيل الثابت -->
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-list-ul me-2"></i>
                            تفاصيل الثابت
                        </h5>
                        <div class="general-subtitle">
                            إدارة تفاصيل الثابت (يمكن التعديل مباشرة في الجدول)
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" id="addNewRowBtn">
                        <i class="bi bi-plus-circle me-1"></i>
                        إضافة تفصيل
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">البيان</th>
                                    <th class="text-nowrap d-none d-md-table-cell">الترميز</th>
                                    <th class="text-nowrap d-none d-lg-table-cell">القيمة</th>
                                    <th class="text-nowrap d-none d-lg-table-cell">الملاحظة</th>
                                    <th class="text-nowrap">الحالة</th>
                                    <th class="text-nowrap d-none d-xl-table-cell">ترتيب العرض</th>
                                    <th class="text-nowrap">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="detailsTableBody">
                                @forelse($constant->allDetails as $detail)
                                    <tr data-detail-id="{{ $detail->id }}">
                                        <td class="text-nowrap">
                                            <input type="text" 
                                                   class="form-control form-control-sm editable-field" 
                                                   name="label" 
                                                   value="{{ $detail->label }}" 
                                                   data-original="{{ $detail->label }}"
                                                   data-field="label">
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <input type="text" 
                                                   class="form-control form-control-sm editable-field" 
                                                   name="code" 
                                                   value="{{ $detail->code ?? '' }}" 
                                                   data-original="{{ $detail->code ?? '' }}"
                                                   data-field="code">
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <input type="text" 
                                                   class="form-control form-control-sm editable-field" 
                                                   name="value" 
                                                   value="{{ $detail->value ?? '' }}" 
                                                   data-original="{{ $detail->value ?? '' }}"
                                                   data-field="value">
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <input type="text" 
                                                   class="form-control form-control-sm editable-field" 
                                                   name="notes" 
                                                   value="{{ $detail->notes ?? '' }}" 
                                                   data-original="{{ $detail->notes ?? '' }}"
                                                   data-field="notes"
                                                   placeholder="ملاحظة">
                                        </td>
                                        <td class="text-nowrap">
                                            <select class="form-select form-select-sm editable-field" 
                                                    name="is_active" 
                                                    data-original="{{ $detail->is_active ? 1 : 0 }}"
                                                    data-field="is_active">
                                                <option value="1" {{ $detail->is_active ? 'selected' : '' }}>نشط</option>
                                                <option value="0" {{ !$detail->is_active ? 'selected' : '' }}>غير نشط</option>
                                            </select>
                                        </td>
                                        <td class="d-none d-xl-table-cell">
                                            <input type="number" 
                                                   class="form-control form-control-sm editable-field" 
                                                   name="order" 
                                                   value="{{ $detail->order ?? 0 }}" 
                                                   data-original="{{ $detail->order ?? 0 }}"
                                                   data-field="order"
                                                   min="0">
                                        </td>
                                        <td class="text-nowrap">
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-detail-btn" 
                                                        data-id="{{ $detail->id }}"
                                                        data-label="{{ $detail->label }}"
                                                        title="حذف">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="emptyRow">
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            لا توجد تفاصيل لهذا الثابت
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.constants.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>
                        إلغاء
                    </a>
                    <button type="submit" form="constantForm" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>
                        حفظ جميع التغييرات
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Main form AJAX submission
        const $mainForm = $('#constantForm');
        const $submitBtns = $('[form="constantForm"], #constantForm button[type="submit"]');

        $mainForm.on('submit', function(e) {
            e.preventDefault();

            if (!$mainForm[0].checkValidity()) {
                $mainForm[0].reportValidity();
                return false;
            }

            $submitBtns.prop('disabled', true);
            const originalText = $submitBtns.first().html();
            $submitBtns.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');

            const formData = new FormData(this);

            $.ajax({
                url: $mainForm.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof window.showToast === 'function') {
                            window.showToast(response.message || 'تم تحديث الثابت بنجاح', 'success');
                        } else {
                            alert(response.message || 'تم تحديث الثابت بنجاح');
                        }
                        setTimeout(function() {
                            window.location.href = '{{ route('admin.constants.index') }}';
                        }, 500);
                    }
                },
                error: function(xhr) {
                    $submitBtns.prop('disabled', false);
                    $submitBtns.html(originalText);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        let firstError = '';

                        $mainForm.find('.is-invalid').removeClass('is-invalid');
                        $mainForm.find('.invalid-feedback').remove();

                        $.each(errors, function(field, messages) {
                            const $field = $mainForm.find('[name="' + field + '"]');
                            if ($field.length) {
                                $field.addClass('is-invalid');
                                const errorMsg = Array.isArray(messages) ? messages[0] : messages;
                                if (!firstError) firstError = errorMsg;
                                $field.after('<div class="invalid-feedback d-block">' + errorMsg + '</div>');
                            }
                        });

                        if (typeof window.showToast === 'function') {
                            window.showToast(firstError || 'يرجى التحقق من الحقول المطلوبة', 'error');
                        } else {
                            alert(firstError || 'يرجى التحقق من الحقول المطلوبة');
                        }
                    } else {
                        const errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء حفظ البيانات';
                        if (typeof window.showToast === 'function') {
                            window.showToast(errorMsg, 'error');
                        } else {
                            alert(errorMsg);
                        }
                    }
                }
            });
        });

        // إضافة تفصيل
        $('#addDetailForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            
            $.ajax({
                url: '{{ route("admin.constant-details.store") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        window.adminNotifications.success(response.message || 'تم إضافة التفصيل بنجاح', 'نجح');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        window.adminNotifications.error(response.message || 'حدث خطأ أثناء الإضافة', 'خطأ');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMsg = 'حدث خطأ أثناء الإضافة';
                    if (response && response.errors) {
                        errorMsg = Object.values(response.errors).flat().join('<br>');
                    } else if (response && response.message) {
                        errorMsg = response.message;
                    }
                    window.adminNotifications.error(errorMsg, 'خطأ');
                }
            });
        });
        
        // تعديل مباشر في الجدول
        let saveTimeout;
        $(document).on('blur change', '.editable-field', function() {
            const $field = $(this);
            const $row = $field.closest('tr');
            const detailId = $row.data('detail-id');
            const fieldName = $field.data('field');
            const newValue = $field.val();
            const originalValue = $field.data('original');
            const isNewRow = detailId === 'new';
            
            // إذا كان صف جديد، يجب أن يكون البيان مملوءاً
            if (isNewRow && fieldName === 'label' && !newValue.trim()) {
                return; // لا تحفظ إذا كان البيان فارغاً
            }
            
            // إذا لم يتغير القيمة، لا حاجة للحفظ
            if (newValue == originalValue && !isNewRow) {
                return;
            }
            
            // إلغاء أي عملية حفظ سابقة
            clearTimeout(saveTimeout);
            
            // إضافة فئة loading
            $field.addClass('is-saving');
            
            // حفظ بعد 500ms من آخر تغيير (debounce)
            saveTimeout = setTimeout(function() {
                const formData = {
                    _token: '{{ csrf_token() }}',
                    constant_master_id: {{ $constant->id }}
                };
                
                // إضافة باقي الحقول من نفس الصف
                $row.find('.editable-field').each(function() {
                    const field = $(this).data('field');
                    const value = $(this).val();
                    formData[field] = value;
                });
                
                // تحديد URL والطريقة
                let url, method;
                if (isNewRow) {
                    // إضافة جديد
                    url = '{{ route("admin.constant-details.store") }}';
                    method = 'POST';
                } else {
                    // تحديث موجود
                    url = '{{ route("admin.constant-details.update", ":id") }}'.replace(':id', detailId);
                    method = 'POST';
                    formData._method = 'PUT';
                }
                
                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            $field.removeClass('is-saving').addClass('is-saved');
                            
                            // إذا كان صف جديد، تحديث data-detail-id بالـ ID الجديد
                            if (isNewRow && response.data && response.data.id) {
                                $row.attr('data-detail-id', response.data.id);
                                $row.removeClass('new-row');
                                // تغيير زر الحذف
                                $row.find('.delete-new-row-btn')
                                    .removeClass('delete-new-row-btn')
                                    .addClass('delete-detail-btn')
                                    .attr('data-id', response.data.id)
                                    .attr('data-label', formData.label || '');
                            }
                            
                            // تحديث القيمة الأصلية
                            $row.find('.editable-field').each(function() {
                                $(this).data('original', $(this).val());
                            });
                            
                            // إزالة فئة is-saved بعد ثانية
                            setTimeout(function() {
                                $field.removeClass('is-saved');
                            }, 1000);
                            
                            if (window.adminNotifications) {
                                window.adminNotifications.success(isNewRow ? 'تم إضافة التفصيل بنجاح' : 'تم حفظ التغييرات بنجاح');
                            }
                        } else {
                            $field.removeClass('is-saving').addClass('is-invalid');
                            if (window.adminNotifications) {
                                window.adminNotifications.error(response.message || 'حدث خطأ أثناء الحفظ');
                            }
                        }
                    },
                    error: function(xhr) {
                        $field.removeClass('is-saving').addClass('is-invalid');
                        const response = xhr.responseJSON;
                        let errorMsg = 'حدث خطأ أثناء الحفظ';
                        if (response && response.errors) {
                            errorMsg = Object.values(response.errors).flat().join('<br>');
                        } else if (response && response.message) {
                            errorMsg = response.message;
                        }
                        if (window.adminNotifications) {
                            window.adminNotifications.error(errorMsg);
                        }
                    }
                });
            }, 500);
        });
        
        // حفظ عند الضغط على Enter
        $(document).on('keydown', '.editable-field', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).blur();
            }
        });
        
        // حذف تفصيل
        $(document).on('click', '.delete-detail-btn', function() {
            const id = $(this).data('id');
            const label = $(this).data('label');
            
            if (confirm('هل أنت متأكد من حذف التفصيل "' + label + '"؟')) {
                $.ajax({
                    url: '{{ route("admin.constant-details.destroy", ":id") }}'.replace(':id', id),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            window.adminNotifications.success(response.message || 'تم حذف التفصيل بنجاح', 'نجح');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            window.adminNotifications.error(response.message || 'حدث خطأ أثناء الحذف', 'خطأ');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        window.adminNotifications.error(response?.message || 'حدث خطأ أثناء الحذف', 'خطأ');
                    }
                });
            }
        });
    });
</script>
@endpush

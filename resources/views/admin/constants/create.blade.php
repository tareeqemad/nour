@extends('layouts.admin')

@section('title', 'إضافة ثابت جديد')

@php
    $breadcrumbTitle = 'إضافة ثابت جديد';
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
            <!-- Card إضافة الثابت الرئيسي -->
            <div class="general-card mb-4">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-plus-circle me-2"></i>
                            إضافة ثابت جديد
                        </h5>
                        <div class="general-subtitle">
                            قم بإدخال بيانات الثابت الرئيسي
                        </div>
                    </div>
                    <a href="{{ route('admin.constants.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right me-2"></i>
                        رجوع
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.constants.store') }}" method="POST" id="constantForm">
                        @csrf
                        
                        <div class="row g-3">
                            {{-- Select للثوابت الموجودة (للاستنساخ) --}}
                            @if(isset($existingConstants) && $existingConstants->count() > 0)
                                <div class="col-12">
                                    <div class="alert alert-info d-flex align-items-center mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <div>
                                            <strong>استنساخ ثابت موجود (اختياري):</strong>
                                            <small class="d-block text-muted">يمكنك اختيار ثابت موجود لجلب بياناته واستنساخها</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">استنساخ من ثابت موجود</label>
                                    <select id="existingConstantSelect" class="form-select">
                                        <option value="">-- اختر ثابت موجود للاستنساخ (اختياري) --</option>
                                        @foreach($existingConstants as $existing)
                                            <option value="{{ $existing->constant_number }}">
                                                {{ $existing->constant_number }} - {{ $existing->constant_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label">رقم الثابت <span class="text-danger">*</span></label>
                                <input type="number" name="constant_number" id="constant_number" 
                                       class="form-control @error('constant_number') is-invalid @enderror" 
                                       value="{{ old('constant_number') }}" min="1">
                                @error('constant_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">رقم فريد للثابت (مثال: 1, 2, 3...)</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">اسم الثابت <span class="text-danger">*</span></label>
                                <input type="text" name="constant_name" id="constant_name" 
                                       class="form-control @error('constant_name') is-invalid @enderror" 
                                       value="{{ old('constant_name') }}">
                                @error('constant_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">اسم الثابت (مثال: المحافظة، المدينة)</small>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" id="description" 
                                          class="form-control @error('description') is-invalid @enderror" 
                                          rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" name="order" id="order" 
                                       class="form-control @error('order') is-invalid @enderror" 
                                       value="{{ old('order', 0) }}" min="0">
                                @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">الحالة</label>
                                <select name="is_active" id="is_active" 
                                        class="form-select @error('is_active') is-invalid @enderror">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                            إضافة وإدارة تفاصيل الثابت
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
                                <tr id="emptyRow">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        لا توجد تفاصيل. اضغط على "إضافة تفصيل" لإضافة تفاصيل جديدة
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.constants.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>
                        إلغاء
                    </a>
                    <button type="submit" form="constantForm" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check-lg me-2"></i>
                        حفظ الثابت والتفاصيل
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
        const $form = $('#constantForm');
        const $submitBtn = $('#submitBtn');
        const $existingConstantSelect = $('#existingConstantSelect');
        const $constantNumber = $('#constant_number');
        const $constantName = $('#constant_name');
        const $description = $('#description');
        const $order = $('#order');
        const $isActive = $('#is_active');
        const $detailsTableBody = $('#detailsTableBody');
        let constantMasterId = null; // سيتم تعيينه بعد حفظ الثابت الرئيسي

        // Template لصف جديد
        function getNewRowHtml() {
            return `
                <tr class="new-row" data-detail-id="new">
                    <td class="text-nowrap">
                        <input type="text" 
                               class="form-control form-control-sm editable-field" 
                               name="label" 
                               value="" 
                               data-original=""
                               data-field="label"
                               placeholder="البيان">
                    </td>
                    <td class="d-none d-md-table-cell">
                        <input type="text" 
                               class="form-control form-control-sm editable-field" 
                               name="code" 
                               value="" 
                               data-original=""
                               data-field="code"
                               placeholder="الترميز">
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <input type="text" 
                               class="form-control form-control-sm editable-field" 
                               name="value" 
                               value="" 
                               data-original=""
                               data-field="value"
                               placeholder="القيمة">
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <input type="text" 
                               class="form-control form-control-sm editable-field" 
                               name="notes" 
                               value="" 
                               data-original=""
                               data-field="notes"
                               placeholder="ملاحظة">
                    </td>
                    <td class="text-nowrap">
                        <select class="form-select form-select-sm editable-field" 
                                name="is_active" 
                                data-original="1"
                                data-field="is_active">
                            <option value="1" selected>نشط</option>
                            <option value="0">غير نشط</option>
                        </select>
                    </td>
                    <td class="d-none d-xl-table-cell">
                        <input type="number" 
                               class="form-control form-control-sm editable-field" 
                               name="order" 
                               value="0" 
                               data-original="0"
                               data-field="order"
                               min="0">
                    </td>
                    <td class="text-nowrap">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger delete-new-row-btn" 
                                    title="حذف">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        // إضافة صف جديد
        $('#addNewRowBtn').on('click', function() {
            // إزالة emptyRow إذا كان موجوداً
            $('#emptyRow').remove();
            
            const newRow = getNewRowHtml();
            $detailsTableBody.append(newRow);
            
            // Focus على أول حقل
            $detailsTableBody.find('tr.new-row').last().find('input[data-field="label"]').focus();
        });

        // حذف صف جديد (غير محفوظ)
        $(document).on('click', '.delete-new-row-btn', function() {
            const $row = $(this).closest('tr');
            $row.fadeOut(300, function() {
                $(this).remove();
                // إذا لم يعد هناك صفوف، أظهر emptyRow
                if ($detailsTableBody.find('tr').length === 0) {
                    $detailsTableBody.html(`
                        <tr id="emptyRow">
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                لا توجد تفاصيل. اضغط على "إضافة تفصيل" لإضافة تفاصيل جديدة
                            </td>
                        </tr>
                    `);
                }
            });
        });

        // Load constant data when selecting from existing constants
        if ($existingConstantSelect.length) {
            $existingConstantSelect.on('change', function() {
                const constantNumber = $(this).val();
                if (!constantNumber) {
                    // Clear fields if no constant selected
                    $constantName.val('');
                    $description.val('');
                    $order.val('0');
                    $isActive.val('1');
                    $detailsTableBody.html(`
                        <tr id="emptyRow">
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                لا توجد تفاصيل. اضغط على "إضافة تفصيل" لإضافة تفاصيل جديدة
                            </td>
                        </tr>
                    `);
                    return;
                }

                // Show loading
                const $option = $(this).find('option:selected');
                const originalText = $option.text();
                $option.text('جاري التحميل...');

                $.ajax({
                    url: `{{ route('admin.constants.get-by-number', ['number' => '__NUMBER__']) }}`.replace('__NUMBER__', constantNumber),
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            // Fill form fields with constant data (but keep constant_number empty for new constant)
                            $constantName.val(response.data.constant_name || '');
                            $description.val(response.data.description || '');
                            $order.val(response.data.order || '0');
                            $isActive.val(response.data.is_active ? '1' : '0');
                            
                            // Load details
                            $detailsTableBody.empty();
                            if (response.data.details && response.data.details.length > 0) {
                                response.data.details.forEach(function(detail) {
                                    const newRow = `
                                        <tr class="new-row" data-detail-id="new">
                                            <td class="text-nowrap">
                                                <input type="text" 
                                                       class="form-control form-control-sm editable-field" 
                                                       name="label" 
                                                       value="${detail.label || ''}" 
                                                       data-original="${detail.label || ''}"
                                                       data-field="label">
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <input type="text" 
                                                       class="form-control form-control-sm editable-field" 
                                                       name="code" 
                                                       value="${detail.code || ''}" 
                                                       data-original="${detail.code || ''}"
                                                       data-field="code">
                                            </td>
                                            <td class="d-none d-lg-table-cell">
                                                <input type="text" 
                                                       class="form-control form-control-sm editable-field" 
                                                       name="value" 
                                                       value="${detail.value || ''}" 
                                                       data-original="${detail.value || ''}"
                                                       data-field="value">
                                            </td>
                                            <td class="d-none d-lg-table-cell">
                                                <input type="text" 
                                                       class="form-control form-control-sm editable-field" 
                                                       name="notes" 
                                                       value="${detail.notes || ''}" 
                                                       data-original="${detail.notes || ''}"
                                                       data-field="notes">
                                            </td>
                                            <td class="text-nowrap">
                                                <select class="form-select form-select-sm editable-field" 
                                                        name="is_active" 
                                                        data-original="${detail.is_active ? 1 : 0}"
                                                        data-field="is_active">
                                                    <option value="1" ${detail.is_active ? 'selected' : ''}>نشط</option>
                                                    <option value="0" ${!detail.is_active ? 'selected' : ''}>غير نشط</option>
                                                </select>
                                            </td>
                                            <td class="d-none d-xl-table-cell">
                                                <input type="number" 
                                                       class="form-control form-control-sm editable-field" 
                                                       name="order" 
                                                       value="${detail.order || 0}" 
                                                       data-original="${detail.order || 0}"
                                                       data-field="order"
                                                       min="0">
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-new-row-btn" 
                                                            title="حذف">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    `;
                                    $detailsTableBody.append(newRow);
                                });
                            } else {
                                $detailsTableBody.html(`
                                    <tr id="emptyRow">
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            لا توجد تفاصيل. اضغط على "إضافة تفصيل" لإضافة تفاصيل جديدة
                                        </td>
                                    </tr>
                                `);
                            }
                        }
                        $option.text(originalText);
                    },
                    error: function(xhr) {
                        $option.text(originalText);
                        if (xhr.status === 404) {
                            alert('الثابت المحدد غير موجود');
                        } else {
                            alert('حدث خطأ أثناء جلب بيانات الثابت');
                        }
                    }
                });
            });
        }

        // حفظ الثابت الرئيسي أولاً
        $form.on('submit', function(e) {
            e.preventDefault();

            if (!$form[0].checkValidity()) {
                $form[0].reportValidity();
                return false;
            }

            $submitBtn.prop('disabled', true);
            const originalText = $submitBtn.html();
            $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');

            const formData = new FormData(this);

            $.ajax({
                url: $form.attr('action'),
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
                        constantMasterId = response.data?.id || response.data?.constant?.id;
                        
                        // حفظ جميع التفاصيل
                        const $rows = $detailsTableBody.find('tr:not(#emptyRow)');
                        if ($rows.length === 0) {
                            // لا توجد تفاصيل، اكتمل الحفظ
                            if (typeof window.showToast === 'function') {
                                window.showToast(response.message || 'تم إنشاء الثابت بنجاح', 'success');
                            } else {
                                alert(response.message || 'تم إنشاء الثابت بنجاح');
                            }
                            setTimeout(function() {
                                window.location.href = '{{ route('admin.constants.index') }}';
                            }, 500);
                            return;
                        }

                        // حفظ التفاصيل واحداً تلو الآخر
                        let savedCount = 0;
                        const totalRows = $rows.length;
                        
                        $rows.each(function() {
                            const $row = $(this);
                            const detailData = {
                                _token: '{{ csrf_token() }}',
                                constant_master_id: constantMasterId
                            };
                            
                            $row.find('.editable-field').each(function() {
                                const field = $(this).data('field');
                                const value = $(this).val();
                                detailData[field] = value;
                            });

                            $.ajax({
                                url: '{{ route("admin.constant-details.store") }}',
                                type: 'POST',
                                data: detailData,
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                success: function(detailResponse) {
                                    savedCount++;
                                    if (savedCount === totalRows) {
                                        // تم حفظ جميع التفاصيل
                                        if (typeof window.showToast === 'function') {
                                            window.showToast(response.message || 'تم إنشاء الثابت والتفاصيل بنجاح', 'success');
                                        } else {
                                            alert(response.message || 'تم إنشاء الثابت والتفاصيل بنجاح');
                                        }
                                        setTimeout(function() {
                                            window.location.href = '{{ route('admin.constants.index') }}';
                                        }, 500);
                                    }
                                },
                                error: function(xhr) {
                                    console.error('Error saving detail:', xhr);
                                    savedCount++;
                                    if (savedCount === totalRows) {
                                        // حتى لو فشل بعض التفاصيل، نكمل
                                        if (typeof window.showToast === 'function') {
                                            window.showToast('تم إنشاء الثابت، لكن حدث خطأ في حفظ بعض التفاصيل', 'warning');
                                        } else {
                                            alert('تم إنشاء الثابت، لكن حدث خطأ في حفظ بعض التفاصيل');
                                        }
                                        setTimeout(function() {
                                            window.location.href = '{{ route('admin.constants.index') }}';
                                        }, 500);
                                    }
                                }
                            });
                        });
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false);
                    $submitBtn.html(originalText);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        let firstError = '';

                        $form.find('.is-invalid').removeClass('is-invalid');
                        $form.find('.invalid-feedback').remove();

                        $.each(errors, function(field, messages) {
                            const $field = $form.find('[name="' + field + '"]');
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

        // حفظ عند الضغط على Enter
        $(document).on('keydown', '.editable-field', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).blur();
            }
        });
    });
</script>
@endpush

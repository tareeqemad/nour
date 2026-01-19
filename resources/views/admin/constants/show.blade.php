@extends('layouts.admin')

@section('title', 'تفاصيل الثابت')

@php
    $breadcrumbTitle = 'تفاصيل الثابت';
    $breadcrumbParent = 'إدارة الثوابت';
    $breadcrumbParentUrl = route('admin.constants.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/constants.css') }}">
@endpush

@section('content')
<div class="constants-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="constants-card">
                <div class="constants-card-header">
                    <div>
                        <h5 class="constants-title">
                            <i class="bi bi-database me-2"></i>
                            تفاصيل الثابت: {{ $constant->constant_name }}
                        </h5>
                        <div class="constants-subtitle">
                            رقم الثابت: {{ $constant->constant_number }} | إدارة تفاصيل الثابت
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        @can('update', $constant)
                            <a href="{{ route('admin.constants.edit', $constant) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-1"></i>
                                تعديل الثابت
                            </a>
                        @endcan
                        <a href="{{ route('admin.constants.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-1"></i>
                            رجوع
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- معلومات الثابت --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        معلومات الثابت
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="fw-semibold" style="width: 40%;">اسم الثابت:</td>
                                            <td>{{ $constant->constant_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">الوصف:</td>
                                            <td>{{ $constant->description ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">الحالة:</td>
                                            <td>
                                                @if($constant->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">ترتيب العرض:</td>
                                            <td>{{ $constant->order }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-list-ul me-2"></i>
                                        الإحصائيات
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="text-center p-3 bg-white rounded">
                                                <div class="fs-2 fw-bold text-info">{{ $constant->allDetails()->count() }}</div>
                                                <div class="text-muted small">إجمالي التفاصيل</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-3 bg-white rounded">
                                                <div class="fs-2 fw-bold text-success">{{ $constant->details()->count() }}</div>
                                                <div class="text-muted small">نشط</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- كارد واحد للفلاتر --}}
                    <div class="filter-card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <i class="bi bi-funnel me-2"></i>
                                فلاتر البحث
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-search me-1"></i>
                                        بحث
                                    </label>
                                    <input type="text" class="form-control" id="detailsSearch" placeholder="البيان / الترميز / القيمة / الملاحظة..." autocomplete="off" value="{{ request('search', '') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-funnel me-1"></i>
                                        الحالة
                                    </label>
                                    <select class="form-select" id="detailsStatusFilter">
                                        <option value="">الكل</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط فقط</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط فقط</option>
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="d-flex gap-2 w-100">
                                        <button class="btn btn-primary flex-fill" id="btnDetailsSearch">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary {{ request('search') || request('status') ? '' : 'd-none' }}" id="btnResetDetailsFilters">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- كارد للجدول --}}
                <div class="card border mt-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-list-ul me-2"></i>
                            تفاصيل الثابت
                        </h6>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDetailModal">
                            <i class="bi bi-plus-circle me-1"></i>
                            إضافة تفصيل
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 constants-table">
                                <thead>
                                    <tr>
                                        <th>البيان</th>
                                        <th>الترميز</th>
                                        <th>القيمة</th>
                                        <th class="d-none d-md-table-cell">الملاحظة</th>
                                        <th class="text-center">الحالة</th>
                                        <th class="text-center d-none d-lg-table-cell">ترتيب العرض</th>
                                        <th style="min-width:140px;" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="detailsTbody">
                                    @include('admin.constants.partials.details-tbody-rows', ['details' => $details])
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                            <div class="small text-muted" id="detailsMeta">
                                @if($details->total() > 0)
                                    عرض {{ $details->firstItem() }} - {{ $details->lastItem() }} من {{ $details->total() }}
                                @else
                                    —
                                @endif
                            </div>
                            <nav>
                                <ul class="pagination mb-0" id="detailsPagination">
                                    @include('admin.constants.partials.details-pagination', ['details' => $details])
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <!-- Modal إضافة تفصيل -->
    <div class="modal fade" id="addDetailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة تفصيل جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addDetailForm">
                    @csrf
                    <input type="hidden" name="constant_master_id" value="{{ $constant->id }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">البيان <span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الترميز</label>
                            <input type="text" name="code" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">القيمة</label>
                            <input type="text" name="value" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الملاحظة</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" name="order" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الحالة</label>
                                <select name="is_active" class="form-select">
                                    <option value="1">نشط</option>
                                    <option value="0">غير نشط</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal تعديل تفصيل -->
    <div class="modal fade" id="editDetailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل التفصيل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editDetailForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="detail_id" id="edit_detail_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">البيان <span class="text-danger">*</span></label>
                            <input type="text" name="label" id="edit_label" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الترميز</label>
                            <input type="text" name="code" id="edit_code" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">القيمة</label>
                            <input type="text" name="value" id="edit_value" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الملاحظة</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" name="order" id="edit_order" class="form-control" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الحالة</label>
                                <select name="is_active" id="edit_is_active" class="form-select">
                                    <option value="1">نشط</option>
                                    <option value="0">غير نشط</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const showUrl = '{{ route('admin.constants.show', $constant) }}';

    // Initialize list with AdminCRUD
    AdminCRUD.initList({
        url: showUrl,
        container: '#detailsTbody',
        filters: {
            search: '#detailsSearch',
            status: '#detailsStatusFilter'
        },
        searchButton: '#btnDetailsSearch',
        clearButton: '#btnResetDetailsFilters',
        paginationContainer: '#detailsPagination',
        perPage: 15,
        listId: 'detailsList',
        onSuccess: function(response, state) {
            // Update meta info
            if (response.count !== undefined && response.count > 0) {
                const count = response.count;
                const perPage = 15;
                const from = state.page === 1 ? 1 : ((state.page - 1) * perPage) + 1;
                const to = Math.min(state.page * perPage, count);
                $('#detailsMeta').text(`عرض ${from} - ${to} من ${count}`);
            } else {
                $('#detailsMeta').text('—');
            }
        }
    });

    // Handle clear search button visibility
    $('#detailsSearch').on('input', function() {
        const hasValue = $(this).val().trim().length > 0;
        const hasStatus = $('#detailsStatusFilter').val() !== '';
        $('#btnResetDetailsFilters').toggleClass('d-none', !hasValue && !hasStatus);
    });

    $('#detailsStatusFilter').on('change', function() {
        const hasValue = $('#detailsSearch').val().trim().length > 0;
        const hasStatus = $(this).val() !== '';
        $('#btnResetDetailsFilters').toggleClass('d-none', !hasValue && !hasStatus);
    });

    // إضافة تفصيل
    $('#addDetailForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("admin.constant-details.store") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                window.adminNotifications.success('تم إضافة التفصيل بنجاح', 'نجح');
                $('#addDetailModal').modal('hide');
                $('#addDetailForm')[0].reset();
                
                // Refresh list
                const listController = AdminCRUD.activeLists.get('detailsList');
                if (listController) {
                    listController.refresh();
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMsg = 'حدث خطأ أثناء الإضافة';
                if (response && response.message) {
                    errorMsg = response.message;
                } else if (response && response.errors) {
                    errorMsg = Object.values(response.errors).flat().join('<br>');
                }
                window.adminNotifications.error(errorMsg, 'خطأ');
            }
        });
    });
    
    // تعديل تفصيل
    $(document).on('click', '.edit-detail-btn', function() {
        const id = $(this).data('id');
        $('#edit_detail_id').val(id);
        $('#edit_label').val($(this).data('label'));
        $('#edit_code').val($(this).data('code') || '');
        $('#edit_value').val($(this).data('value') || '');
        $('#edit_notes').val($(this).data('notes') || '');
        $('#edit_order').val($(this).data('order'));
        $('#edit_is_active').val($(this).data('is-active') ? '1' : '0');
        $('#editDetailModal').modal('show');
    });
    
    $('#editDetailForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit_detail_id').val();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("admin.constant-details.update", ":id") }}'.replace(':id', id),
            type: 'POST',
            data: formData + '&_method=PUT',
            success: function(response) {
                window.adminNotifications.success('تم تحديث التفصيل بنجاح', 'نجح');
                $('#editDetailModal').modal('hide');
                
                // Refresh list
                const listController = AdminCRUD.activeLists.get('detailsList');
                if (listController) {
                    listController.refresh();
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMsg = 'حدث خطأ أثناء التعديل';
                if (response && response.message) {
                    errorMsg = response.message;
                } else if (response && response.errors) {
                    errorMsg = Object.values(response.errors).flat().join('<br>');
                }
                window.adminNotifications.error(errorMsg, 'خطأ');
            }
        });
    });
    
    // حذف تفصيل
    $(document).on('click', '.delete-detail-btn', function() {
        const id = $(this).data('id');
        const row = $(this).closest('tr');
        
        if (confirm('هل أنت متأكد من حذف هذا التفصيل؟')) {
            $.ajax({
                url: '{{ route("admin.constant-details.destroy", ":id") }}'.replace(':id', id),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    window.adminNotifications.success('تم حذف التفصيل بنجاح', 'نجح');
                    
                    // Refresh list
                    const listController = AdminCRUD.activeLists.get('detailsList');
                    if (listController) {
                        listController.refresh();
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

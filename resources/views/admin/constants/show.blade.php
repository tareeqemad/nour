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
<div class="general-page" id="constantShowPage" data-show-url="{{ route('admin.constants.show', $constant) }}">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form :title="$constant->constant_name" icon="bi-database" :backRoute="route('admin.constants.index')">
                    @can('update', $constant)
                        <a href="{{ route('admin.constants.edit', $constant) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i>
                            تعديل
                        </a>
                    @endcan
                </x-admin.card-header-form>

                <div class="card-body">
                    {{-- KPI + معلومات --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-primary"><i class="bi bi-hash"></i></div>
                                <div>
                                    <div class="dash-kpi-value">{{ $constant->constant_number }}</div>
                                    <div class="dash-kpi-label">رقم الثابت</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-info"><i class="bi bi-list-ul"></i></div>
                                <div>
                                    <div class="dash-kpi-value">{{ $constant->allDetails()->count() }}</div>
                                    <div class="dash-kpi-label">إجمالي التفاصيل</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-success"><i class="bi bi-check-circle"></i></div>
                                <div>
                                    <div class="dash-kpi-value">{{ $constant->details()->count() }}</div>
                                    <div class="dash-kpi-label">نشط</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="dash-kpi">
                                <div class="dash-kpi-icon kpi-warning"><i class="bi bi-sort-numeric-down"></i></div>
                                <div>
                                    <div class="dash-kpi-value">{{ $constant->order }}</div>
                                    <div class="dash-kpi-label">الترتيب</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- معلومات الثابت --}}
                    <div style="background: #FAFCFF; border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600;">الوصف</label>
                                <div style="font-size: 0.88rem; color: var(--color-text-main, #1F2937); margin-top: 0.2rem;">{{ $constant->description ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600;">الحالة</label>
                                <div style="margin-top: 0.2rem;">
                                    @if($constant->is_active)
                                        <span class="badge-status-active">نشط</span>
                                    @else
                                        <span class="badge-status-inactive">غير نشط</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600;">تاريخ الإنشاء</label>
                                <div style="font-size: 0.88rem; color: var(--color-text-secondary, #3B4863); margin-top: 0.2rem;">{{ $constant->created_at->format('Y-m-d') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- فلاتر --}}
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">بحث</label>
                            <input type="text" class="form-control" id="detailsSearch" placeholder="البيان / الترميز / القيمة..." autocomplete="off" value="{{ request('search', '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">الحالة</label>
                            <select class="form-select" id="detailsStatusFilter">
                                <option value="">الكل</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary" id="btnDetailsSearch">
                            <i class="bi bi-search me-1"></i>
                            بحث
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnResetDetailsFilters">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            تفريغ
                        </button>
                    </div>
                </div>

                {{-- جدول التفاصيل --}}
                <div style="border-top: 1px solid var(--color-border-soft, #EDF1F5);">
                    <div style="background: #FAFCFF; padding: 0.65rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                        <h6 class="mb-0 fw-bold" style="color: var(--color-primary, #24308F); font-size: 0.88rem;">
                            <i class="bi bi-list-ul me-2" style="opacity: .75;"></i>
                            تفاصيل الثابت
                        </h6>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDetailModal">
                            <i class="bi bi-plus-circle me-1"></i>
                            إضافة تفصيل
                        </button>
                    </div>
                    <div style="padding: 0 1.25rem 1.25rem;">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 general-table">
                                <thead>
                                    <tr>
                                        <th>البيان</th>
                                        <th>الترميز</th>
                                        <th>القيمة</th>
                                        <th class="d-none d-md-table-cell">الملاحظة</th>
                                        <th class="text-center">الحالة</th>
                                        <th class="text-center d-none d-lg-table-cell">الترتيب</th>
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
            </x-admin.card>
        </div>
    </div>
</div>

{{-- Modal إضافة تفصيل --}}
<div class="modal fade" id="addDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i> إضافة تفصيل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addDetailForm">
                @csrf
                <input type="hidden" name="constant_master_id" value="{{ $constant->id }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">البيان <span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الترميز</label>
                            <input type="text" name="code" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">القيمة</label>
                            <input type="text" name="value" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">الملاحظة</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الترتيب</label>
                            <input type="number" name="order" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الحالة</label>
                            <select name="is_active" class="form-select">
                                <option value="1">نشط</option>
                                <option value="0">غير نشط</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal تعديل تفصيل --}}
<div class="modal fade" id="editDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-1"></i> تعديل التفصيل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDetailForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="detail_id" id="edit_detail_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">البيان <span class="text-danger">*</span></label>
                            <input type="text" name="label" id="edit_label" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الترميز</label>
                            <input type="text" name="code" id="edit_code" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">القيمة</label>
                            <input type="text" name="value" id="edit_value" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">الملاحظة</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الترتيب</label>
                            <input type="number" name="order" id="edit_order" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الحالة</label>
                            <select name="is_active" id="edit_is_active" class="form-select">
                                <option value="1">نشط</option>
                                <option value="0">غير نشط</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> حفظ</button>
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

    AdminCRUD.initList({
        url: showUrl,
        container: '#detailsTbody',
        filters: { search: '#detailsSearch', status: '#detailsStatusFilter' },
        searchButton: '#btnDetailsSearch',
        clearButton: '#btnResetDetailsFilters',
        paginationContainer: '#detailsPagination',
        perPage: 15,
        listId: 'detailsList',
        onSuccess: function(response, state) {
            if (response.count > 0) {
                const perPage = 15, from = (state.page - 1) * perPage + 1, to = Math.min(state.page * perPage, response.count);
                $('#detailsMeta').text(`عرض ${from} - ${to} من ${response.count}`);
            } else {
                $('#detailsMeta').text('—');
            }
        }
    });

    // إضافة تفصيل
    $('#addDetailForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("admin.constant-details.store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function() {
                window.adminNotifications.success('تم إضافة التفصيل بنجاح');
                $('#addDetailModal').modal('hide');
                $('#addDetailForm')[0].reset();
                AdminCRUD.activeLists.get('detailsList')?.refresh();
            },
            error: function(xhr) {
                const r = xhr.responseJSON;
                window.adminNotifications.error(r?.message || Object.values(r?.errors || {}).flat().join(', ') || 'خطأ');
            }
        });
    });

    // تعديل تفصيل
    $(document).on('click', '.edit-detail-btn', function() {
        const $b = $(this);
        $('#edit_detail_id').val($b.data('id'));
        $('#edit_label').val($b.data('label'));
        $('#edit_code').val($b.data('code') || '');
        $('#edit_value').val($b.data('value') || '');
        $('#edit_notes').val($b.data('notes') || '');
        $('#edit_order').val($b.data('order'));
        $('#edit_is_active').val($b.data('is-active') ? '1' : '0');
        $('#editDetailModal').modal('show');
    });

    $('#editDetailForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit_detail_id').val();
        $.ajax({
            url: '{{ route("admin.constant-details.update", ":id") }}'.replace(':id', id),
            type: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            success: function() {
                window.adminNotifications.success('تم التحديث بنجاح');
                $('#editDetailModal').modal('hide');
                AdminCRUD.activeLists.get('detailsList')?.refresh();
            },
            error: function(xhr) {
                const r = xhr.responseJSON;
                window.adminNotifications.error(r?.message || 'خطأ');
            }
        });
    });

    // حذف تفصيل
    $(document).on('click', '.delete-detail-btn', function() {
        const id = $(this).data('id');
        const label = $(this).data('label') || 'هذا التفصيل';
        Swal.fire({
            title: 'تأكيد الحذف',
            html: `هل أنت متأكد من حذف <strong>${label}</strong>؟<br><small style="color: var(--color-danger-text, #B91C1C);">هذا الإجراء لا يمكن التراجع عنه</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'var(--color-danger, #EF4444)',
            cancelButtonColor: 'var(--btn-secondary-border, #D1D5DB)',
            confirmButtonText: '<i class="bi bi-trash3 me-1"></i>حذف',
            cancelButtonText: 'إلغاء',
            reverseButtons: true,
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ route("admin.constant-details.destroy", ":id") }}'.replace(':id', id),
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    window.adminNotifications.success('تم الحذف');
                    AdminCRUD.activeLists.get('detailsList')?.refresh();
                },
                error: function(xhr) {
                    window.adminNotifications.error(xhr.responseJSON?.message || 'خطأ');
                }
            });
        });
    });
});
</script>
@endpush

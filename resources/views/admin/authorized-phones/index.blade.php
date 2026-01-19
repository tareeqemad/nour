@extends('layouts.admin')

@section('title', 'إدارة الأرقام المصرح بها')

@php
    $breadcrumbTitle = 'إدارة الأرقام المصرح بها';
@endphp

@push('styles')
<style>
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
</style>
@endpush

@section('content')
<div class="general-page" id="authorizedPhonesPage">
    <div class="row g-3">
        <div class="col-12">
            {{-- Statistics --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">إجمالي الأرقام المفعلة</h6>
                            <h3 class="mb-0 text-primary" id="totalActive">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">مسجلين</h6>
                            <h3 class="mb-0 text-success" id="registeredCount">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">متبقيين</h6>
                            <h3 class="mb-0 text-warning" id="pendingCount">-</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-phone me-2"></i>
                            إدارة الأرقام المصرح بها
                        </h5>
                        <div class="general-subtitle">
                            إدارة أرقام الجوالات المسموح لها بالتسجيل في المنصة
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" id="btnImportExcel">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            استيراد من Excel
                        </button>
                        <button type="button" class="btn btn-warning" id="btnNotifyPending">
                            <i class="bi bi-bell me-1"></i>
                            إشعار المتبقيين
                        </button>
                        <button type="button" class="btn btn-danger" id="btnDeleteAll">
                            <i class="bi bi-trash me-1"></i>
                            حذف الكل
                        </button>
                        <a href="{{ route('admin.authorized-phones.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>
                            إضافة رقم جديد
                        </a>
                    </div>
                </div>

                <div class="card-body pb-4 position-relative">
                    {{-- Filters --}}
                    <div class="filter-card mb-3">
                        <div class="card-header">
                            <h6 class="card-title">
                                <i class="bi bi-funnel me-2"></i>
                                فلاتر البحث
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">بحث</label>
                                    <input type="text" id="searchInput" class="form-control" 
                                           placeholder="ابحث بالرقم أو الاسم..." autocomplete="off">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">الحالة</label>
                                    <select id="statusFilter" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="1">مفعل</option>
                                        <option value="0">معطل</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">حالة التسجيل</label>
                                    <select id="registeredFilter" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="1">مسجل</option>
                                        <option value="0">غير مسجل</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" id="btnSearch">
                                        <i class="bi bi-search me-1"></i>
                                        بحث
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Loading Overlay --}}
                    <div class="loading-overlay d-none" id="loadingOverlay">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <p class="text-muted mb-0">جاري التحميل...</p>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 general-table">
                            <thead>
                                <tr>
                                    <th style="min-width:80px;">#</th>
                                    <th style="min-width:150px;">رقم الجوال</th>
                                    <th>الاسم</th>
                                    <th class="d-none d-md-table-cell">الملاحظات</th>
                                    <th class="text-center" style="min-width:100px;">الحالة</th>
                                    <th class="text-center" style="min-width:120px;">حالة التسجيل</th>
                                    <th class="text-center d-none d-lg-table-cell" style="min-width:120px;">تاريخ الإضافة</th>
                                    <th style="min-width:180px;" class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="phonesTableBody">
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2 text-muted">جاري تحميل البيانات...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="paginationContainer" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Import Excel Modal --}}
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importExcelModalLabel">
                    <i class="bi bi-file-earmark-excel me-2 text-success"></i>
                    استيراد من ملف Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importExcelForm" action="{{ route('admin.authorized-phones.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-4 p-3 bg-light rounded border-start border-3 border-info">
                        <h6 class="mb-3 fw-semibold text-dark">
                            <i class="bi bi-info-circle me-2 text-info"></i>
                            تعليمات:
                        </h6>
                        <ul class="mb-0 small text-muted" style="list-style: none; padding: 0;">
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <strong>العمود الأول (A):</strong> الاسم (مطلوب)
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <strong>العمود الثاني (B):</strong> رقم الجوال (مطلوب)
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-circle text-secondary me-2"></i>
                                <strong>العمود الثالث (C):</strong> الملاحظات (اختياري)
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-file-earmark-excel text-success me-2"></i>
                                الصيغ المدعومة: <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code>
                            </li>
                            <li class="mb-0">
                                <i class="bi bi-list-ul text-info me-2"></i>
                                يمكن إضافة headers في السطر الأول (الاسم، رقم الجوال، الملاحظات)
                            </li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">اختر ملف Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="excelFile" class="form-control" 
                               accept=".xlsx,.xls,.csv" required>
                        <small class="form-text text-muted">الحد الأقصى: 10 ميجابايت</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success" id="btnConfirmImport">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="importSpinner"></span>
                        <i class="bi bi-upload me-1"></i>
                        استيراد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    
    const indexUrl = '{{ route("admin.authorized-phones.index") }}';
    let currentPage = 1;

    // Load phones
    function loadPhones(page = 1) {
        const params = new URLSearchParams({
            ajax: '1',
            page: page,
            per_page: '15',
            search: document.getElementById('searchInput')?.value || '',
            is_active: document.getElementById('statusFilter')?.value || '',
            is_registered: document.getElementById('registeredFilter')?.value || ''
        });

        document.getElementById('loadingOverlay')?.classList.remove('d-none');

        fetch(`${indexUrl}?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('loadingOverlay')?.classList.add('d-none');
            
            if (data.ok && data.data) {
                renderTable(data.data, data.meta);
                renderPagination(data.meta);
                updateStats(data.stats);
                currentPage = page;
            } else {
                document.getElementById('phonesTableBody').innerHTML = 
                    '<tr><td colspan="8" class="text-center py-5 text-danger">حدث خطأ أثناء تحميل البيانات</td></tr>';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            document.getElementById('loadingOverlay')?.classList.add('d-none');
            document.getElementById('phonesTableBody').innerHTML = 
                '<tr><td colspan="8" class="text-center py-5 text-danger">حدث خطأ أثناء تحميل البيانات</td></tr>';
        });
    }

    // Update stats
    function updateStats(stats) {
        if (stats) {
            document.getElementById('totalActive').textContent = stats.total_active || 0;
            document.getElementById('registeredCount').textContent = stats.registered || 0;
            document.getElementById('pendingCount').textContent = stats.pending || 0;
        }
    }

    // Render table
    function renderTable(phones, meta) {
        const tbody = document.getElementById('phonesTableBody');
        if (phones.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted">لا توجد أرقام مسجلة</td></tr>';
            return;
        }

        const startNum = meta?.from || ((currentPage - 1) * 15 + 1);
        
        tbody.innerHTML = phones.map((phone, i) => {
            const date = new Date(phone.created_at).toLocaleDateString('ar-EG');
            const statusBadge = phone.is_active 
                ? '<span class="badge bg-success">مفعل</span>'
                : '<span class="badge bg-danger">معطل</span>';
            const regBadge = phone.is_registered 
                ? `<span class="badge bg-success">مسجل</span>`
                : '<span class="badge bg-warning text-dark">غير مسجل</span>';
            const operatorLink = phone.operator 
                ? `<a href="${indexUrl.replace('authorized-phones', 'operators')}/${phone.operator.id}" class="btn btn-sm btn-outline-info" title="عرض المشغل"><i class="bi bi-eye"></i></a>`
                : '';

            return `
                <tr>
                    <td>${startNum + i}</td>
                    <td><strong>${phone.phone}</strong></td>
                    <td>${phone.name || '<span class="text-muted">—</span>'}</td>
                    <td class="d-none d-md-table-cell"><small class="text-muted">${phone.notes || '—'}</small></td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">${regBadge}</td>
                    <td class="text-center d-none d-lg-table-cell"><small class="text-muted">${date}</small></td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            ${operatorLink}
                            <button onclick="toggleStatus(${phone.id}, ${phone.is_active})" class="btn btn-sm btn-outline-${phone.is_active ? 'warning' : 'success'}" title="${phone.is_active ? 'إيقاف' : 'تفعيل'}">
                                <i class="bi bi-${phone.is_active ? 'pause' : 'play'}-fill"></i>
                            </button>
                            <a href="${indexUrl}/${phone.id}/edit" class="btn btn-sm btn-outline-primary" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button onclick="deletePhone(${phone.id})" class="btn btn-sm btn-outline-danger" title="حذف">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Render pagination
    function renderPagination(meta) {
        const container = document.getElementById('paginationContainer');
        if (!meta || meta.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="d-flex justify-content-between align-items-center"><div class="small text-muted">';
        html += meta.total > 0 ? `عرض ${meta.from} - ${meta.to} من ${meta.total}` : '—';
        html += '</div><nav><ul class="pagination mb-0">';

        if (meta.current_page > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadPhones(${meta.current_page - 1}); return false;">السابق</a></li>`;
        }

        for (let i = 1; i <= meta.last_page; i++) {
            html += i === meta.current_page
                ? `<li class="page-item active"><span class="page-link">${i}</span></li>`
                : `<li class="page-item"><a class="page-link" href="#" onclick="loadPhones(${i}); return false;">${i}</a></li>`;
        }

        if (meta.current_page < meta.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadPhones(${meta.current_page + 1}); return false;">التالي</a></li>`;
        }

        html += '</ul></nav></div>';
        container.innerHTML = html;
    }

    // Helper: Show notification
    function notify(type, message) {
        if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
            window.adminNotifications[type](message);
        } else {
            alert(message);
        }
    }

    // Helper: AJAX request
    function ajaxRequest(url, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        };

        if (data && method !== 'GET') {
            if (data instanceof FormData) {
                options.body = data;
            } else {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }
        }

        return fetch(url, options).then(res => res.json());
    }

    // Toggle status
    window.toggleStatus = function(id, currentStatus) {
        const action = currentStatus ? 'إيقاف' : 'تفعيل';
        if (!confirm(`هل أنت متأكد من ${action} هذا الرقم؟`)) return;

        ajaxRequest(`${indexUrl}/${id}/toggle-status`, 'POST')
            .then(data => {
                if (data.success) {
                    notify('success', data.message);
                    loadPhones(currentPage);
                } else {
                    notify('error', data.message || 'حدث خطأ');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                notify('error', 'حدث خطأ أثناء تغيير الحالة');
            });
    };

    // Delete phone
    window.deletePhone = function(id) {
        if (!confirm('هل أنت متأكد من حذف هذا الرقم؟')) return;

        ajaxRequest(`${indexUrl}/${id}`, 'DELETE')
            .then(data => {
                if (data.success) {
                    notify('success', data.message);
                    loadPhones(currentPage);
                } else {
                    notify('error', data.message || 'حدث خطأ');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                notify('error', 'حدث خطأ أثناء الحذف');
            });
    };

    // Event listeners
    document.getElementById('btnSearch')?.addEventListener('click', () => loadPhones(1));
    document.getElementById('statusFilter')?.addEventListener('change', () => loadPhones(1));
    document.getElementById('registeredFilter')?.addEventListener('change', () => loadPhones(1));
    document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') loadPhones(1);
    });

    // Import Excel
    const importModal = new bootstrap.Modal(document.getElementById('importExcelModal'));
    document.getElementById('btnImportExcel')?.addEventListener('click', () => {
        document.getElementById('excelFile').value = '';
        importModal.show();
    });

    document.getElementById('importExcelForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const btn = document.getElementById('btnConfirmImport');
        const spinner = document.getElementById('importSpinner');

        btn.disabled = true;
        spinner.classList.remove('d-none');

        ajaxRequest(this.action, 'POST', formData)
            .then(data => {
                importModal.hide();
                if (data.success) {
                    notify('success', data.message);
                    loadPhones(1);
                    if (data.errors && data.errors.length > 0) {
                        setTimeout(() => {
                            const errors = data.errors.slice(0, 10).join('\n');
                            alert('الأخطاء أثناء الاستيراد:\n\n' + errors);
                        }, 500);
                    }
                } else {
                    notify('error', data.message || 'حدث خطأ أثناء الاستيراد');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                importModal.hide();
                notify('error', 'حدث خطأ أثناء الاستيراد');
            })
            .finally(() => {
                btn.disabled = false;
                spinner.classList.add('d-none');
                document.getElementById('excelFile').value = '';
            });
    });

    // Notify pending
    document.getElementById('btnNotifyPending')?.addEventListener('click', function() {
        if (!confirm('هل أنت متأكد من إرسال إشعار لجميع المشغلين المتبقيين؟')) return;

        ajaxRequest(`${indexUrl}/notify-pending`, 'POST')
            .then(data => {
                if (data.success) {
                    notify('success', data.message);
                } else {
                    notify('error', data.message || 'حدث خطأ');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                notify('error', 'حدث خطأ أثناء إرسال الإشعارات');
            });
    });

    // Delete all
    document.getElementById('btnDeleteAll')?.addEventListener('click', function() {
        const confirmText = prompt('لحذف جميع الأرقام، اكتب "حذف الكل" بالضبط:');
        if (confirmText !== 'حذف الكل') {
            alert('لم يتم التأكيد. تم الإلغاء.');
            return;
        }

        if (!confirm('هل أنت متأكد 100% من حذف جميع الأرقام؟ لا يمكن التراجع عن هذا الإجراء!')) return;

        ajaxRequest(`${indexUrl}/delete-all`, 'DELETE')
            .then(data => {
                if (data.success) {
                    notify('success', data.message);
                    loadPhones(1);
                } else {
                    notify('error', data.message || 'حدث خطأ');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                notify('error', 'حدث خطأ أثناء الحذف');
            });
    });

    // Make loadPhones global
    window.loadPhones = loadPhones;

    // Initial load
    loadPhones(1);
})();
</script>
@endpush

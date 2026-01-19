@extends('layouts.admin')

@section('title', 'سجل الأخطاء')

@php
    $breadcrumbTitle = 'سجل الأخطاء';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
<style>
    .log-entry {
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
    }
    .log-level-error {
        color: #dc3545;
        font-weight: bold;
    }
    .log-level-warning {
        color: #ffc107;
        font-weight: bold;
    }
    .log-level-info {
        color: #0dcaf0;
    }
    .log-level-debug {
        color: #6c757d;
    }
    .log-timestamp {
        color: #6c757d;
        font-size: 0.8rem;
    }
    .log-context {
        background-color: #f8f9fa;
        padding: 0.5rem;
        border-radius: 0.25rem;
        margin-top: 0.5rem;
        font-size: 0.8rem;
        white-space: pre-wrap;
        word-break: break-all;
        max-height: 200px;
        overflow-y: auto;
    }
    .log-trace {
        background-color: #fff3cd;
        padding: 0.5rem;
        border-radius: 0.25rem;
        margin-top: 0.5rem;
        font-size: 0.75rem;
        white-space: pre-wrap;
        word-break: break-all;
        max-height: 300px;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div class="general-page" id="logsPage" data-index-url="{{ route('admin.logs.index') }}">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-file-text me-2"></i>
                            سجل الأخطاء
                        </h5>
                        <div class="general-subtitle">
                            عرض أخطاء النظام من ملف الـ logs
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-danger" id="btnClearLogs" title="حذف جميع الـ logs">
                            <i class="bi bi-trash me-1"></i>
                            حذف الكل
                        </button>
                        <a href="{{ route('admin.logs.download') }}" class="btn btn-outline-primary" title="تحميل ملف الـ logs">
                            <i class="bi bi-download me-1"></i>
                            تحميل
                        </a>
                    </div>
                </div>

                <div class="card-body pb-4">
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
                                <div class="col-lg-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-search me-1"></i>
                                        بحث
                                    </label>
                                    <div class="general-search">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="searchInput" class="form-control" 
                                               placeholder="ابحث في الرسائل أو الأخطاء..." 
                                               autocomplete="off">
                                        <button type="button" class="general-clear" id="btnClearSearch" title="إلغاء البحث" style="display: none;">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-funnel me-1"></i>
                                        مستوى الخطأ
                                    </label>
                                    <select id="levelFilter" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="ERROR">ERROR</option>
                                        <option value="WARNING">WARNING</option>
                                        <option value="INFO">INFO</option>
                                        <option value="DEBUG">DEBUG</option>
                                    </select>
                                </div>

                                <div class="col-lg-3 d-flex align-items-end">
                                    <div class="d-flex gap-2 w-100">
                                        <button type="button" class="btn btn-primary flex-fill" id="btnSearch">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="table-responsive" id="logsTableContainer">
                        <table class="table table-hover align-middle mb-0 general-table">
                            <thead>
                                <tr>
                                    <th style="min-width:80px;">#</th>
                                    <th style="min-width:150px;">الوقت</th>
                                    <th style="min-width:100px;">المستوى</th>
                                    <th>الرسالة</th>
                                    <th style="min-width:100px;" class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody">
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="data-table-loading">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">جاري التحميل...</span>
                                            </div>
                                            <p class="mt-2 text-muted">جاري تحميل البيانات...</p>
                                        </div>
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

{{-- Modal: عرض تفاصيل الخطأ --}}
<div class="modal fade" id="logDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-info-circle me-2"></i>
                    تفاصيل الخطأ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="mb-3">
                    <strong>الوقت:</strong>
                    <span id="detailTimestamp" class="log-timestamp"></span>
                </div>
                <div class="mb-3">
                    <strong>المستوى:</strong>
                    <span id="detailLevel" class="log-level"></span>
                </div>
                <div class="mb-3">
                    <strong>الرسالة:</strong>
                    <div id="detailMessage" class="log-entry mt-2"></div>
                </div>
                <div class="mb-3" id="detailContextContainer" style="display: none;">
                    <strong>السياق:</strong>
                    <div id="detailContext" class="log-context mt-2"></div>
                </div>
                <div id="detailTraceContainer" style="display: none;">
                    <strong>Stack Trace:</strong>
                    <div id="detailTrace" class="log-trace mt-2"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: تأكيد حذف الـ logs --}}
<div class="modal fade" id="clearLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header border-danger bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    تأكيد الحذف
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>تحذير:</strong> هل أنت متأكد من حذف جميع سجلات الأخطاء؟
                    <br><br>
                    هذا الإجراء سيحذف <strong>جميع الـ logs</strong> ولا يمكن التراجع عنه!
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger" id="btnConfirmClearLogs">
                    <span class="spinner-border spinner-border-sm me-2 d-none" id="clearLogsSpinner"></span>
                    <i class="bi bi-trash me-1"></i>
                    حذف الكل
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/js/data-table-loading.js') }}"></script>
<script>
(function() {
    const indexUrl = '{{ route("admin.logs.index") }}';
    let currentPage = 1;
    let currentMeta = null;
    let searchTimeout;
    const $search = document.getElementById('searchInput');
    const $clearSearch = document.getElementById('btnClearSearch');
    const $btnSearch = document.getElementById('btnSearch');
    const $levelFilter = document.getElementById('levelFilter');
    const $container = document.getElementById('logsTableContainer');

    function loadLogs(page = 1) {
        const search = $search.value.trim();
        const level = $levelFilter.value || '';
        const url = new URL(indexUrl);
        url.searchParams.set('ajax', '1');
        url.searchParams.set('per_page', '50');
        url.searchParams.set('page', page);
        if (search) {
            url.searchParams.set('search', search);
        }
        if (level) {
            url.searchParams.set('level', level);
        }

        const tbody = document.getElementById('logsTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="data-table-loading">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">جاري التحميل...</p>
                    </div>
                </td>
            </tr>
        `;

        if (window.DataTableLoading) {
            window.DataTableLoading.show($container);
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (window.DataTableLoading) {
                window.DataTableLoading.hide($container);
            }
            if (data.ok && data.data) {
                currentMeta = data.meta;
                renderLogs(data.data, data.meta);
                renderPagination(data.meta);
                currentPage = page;
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger">حدث خطأ أثناء تحميل البيانات</td></tr>';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            if (window.DataTableLoading) {
                window.DataTableLoading.hide($container);
            }
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger">حدث خطأ أثناء تحميل البيانات</td></tr>';
        });
    }

    function getLevelClass(level) {
        const levelUpper = level.toUpperCase();
        if (levelUpper === 'ERROR') return 'log-level-error';
        if (levelUpper === 'WARNING') return 'log-level-warning';
        if (levelUpper === 'INFO') return 'log-level-info';
        if (levelUpper === 'DEBUG') return 'log-level-debug';
        return '';
    }

    function getLevelBadge(level) {
        const levelUpper = level.toUpperCase();
        const badges = {
            'ERROR': 'bg-danger',
            'WARNING': 'bg-warning text-dark',
            'INFO': 'bg-info text-dark',
            'DEBUG': 'bg-secondary',
        };
        return badges[levelUpper] || 'bg-secondary';
    }

    function renderLogs(logs, meta) {
        const tbody = document.getElementById('logsTableBody');
        if (logs.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        لا توجد سجلات
                    </td>
                </tr>
            `;
            return;
        }

        const startNumber = meta && meta.from ? meta.from : ((currentPage - 1) * (meta?.per_page || 50) + 1);

        tbody.innerHTML = logs.map((log, index) => {
            const levelClass = getLevelClass(log.level);
            const levelBadge = getLevelBadge(log.level);
            const messagePreview = log.message.length > 100 
                ? log.message.substring(0, 100) + '...' 
                : log.message;
            
            return `
                <tr>
                    <td class="text-nowrap">${startNumber + index}</td>
                    <td class="text-nowrap"><small class="log-timestamp">${log.timestamp}</small></td>
                    <td class="text-center">
                        <span class="badge ${levelBadge}">${log.level}</span>
                    </td>
                    <td>
                        <div class="log-entry ${levelClass}">${escapeHtml(messagePreview)}</div>
                    </td>
                    <td class="text-center">
                        <button onclick="showLogDetail(${index})" 
                                class="btn btn-sm btn-outline-primary" 
                                title="عرض التفاصيل"
                                data-log-index="${index}">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        // حفظ الـ logs في window للوصول إليها من showLogDetail
        window.logsData = logs;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function renderPagination(meta) {
        const container = document.getElementById('paginationContainer');
        if (meta.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">';
        html += `<div class="small text-muted">`;
        if (meta.total > 0) {
            html += `عرض ${meta.from} - ${meta.to} من ${meta.total}`;
        } else {
            html += '—';
        }
        html += '</div>';
        html += '<nav><ul class="pagination mb-0">';
        
        // Previous
        if (meta.current_page > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadLogs(${meta.current_page - 1}); return false;">السابق</a></li>`;
        } else {
            html += '<li class="page-item disabled"><span class="page-link">السابق</span></li>';
        }

        // Pages
        for (let i = 1; i <= meta.last_page; i++) {
            if (i === meta.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadLogs(${i}); return false;">${i}</a></li>`;
            }
        }

        // Next
        if (meta.current_page < meta.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadLogs(${meta.current_page + 1}); return false;">التالي</a></li>`;
        } else {
            html += '<li class="page-item disabled"><span class="page-link">التالي</span></li>';
        }

        html += '</ul></nav></div>';
        container.innerHTML = html;
    }

    // Search functionality
    if ($btnSearch) {
        $btnSearch.addEventListener('click', function() {
            loadLogs(1);
        });
    }

    if ($search) {
        $search.addEventListener('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                loadLogs(1);
            }
        });

        $search.addEventListener('input', function() {
            if ($search.value.trim()) {
                $clearSearch.style.display = 'block';
            } else {
                $clearSearch.style.display = 'none';
            }
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadLogs(1);
            }, 500);
        });
    }

    if ($clearSearch) {
        $clearSearch.addEventListener('click', function() {
            $search.value = '';
            $clearSearch.style.display = 'none';
            loadLogs(1);
        });
    }

    if ($levelFilter) {
        $levelFilter.addEventListener('change', function() {
            loadLogs(1);
        });
    }

    // Make loadLogs global
    window.loadLogs = loadLogs;

    // Show log detail
    window.showLogDetail = function(index) {
        if (!window.logsData || !window.logsData[index]) return;
        
        const log = window.logsData[index];
        const modal = new bootstrap.Modal(document.getElementById('logDetailModal'));
        
        document.getElementById('detailTimestamp').textContent = log.timestamp;
        document.getElementById('detailLevel').textContent = log.level;
        document.getElementById('detailLevel').className = `log-level ${getLevelClass(log.level)}`;
        document.getElementById('detailMessage').textContent = log.message;
        
        if (log.context && log.context.trim()) {
            document.getElementById('detailContext').textContent = log.context.trim();
            document.getElementById('detailContextContainer').style.display = 'block';
        } else {
            document.getElementById('detailContextContainer').style.display = 'none';
        }
        
        if (log.trace && log.trace.trim()) {
            document.getElementById('detailTrace').textContent = log.trace.trim();
            document.getElementById('detailTraceContainer').style.display = 'block';
        } else {
            document.getElementById('detailTraceContainer').style.display = 'none';
        }
        
        modal.show();
    };

    // Clear logs
    const btnClearLogs = document.getElementById('btnClearLogs');
    const clearLogsModal = document.getElementById('clearLogsModal') ? new bootstrap.Modal(document.getElementById('clearLogsModal')) : null;
    const btnConfirmClearLogs = document.getElementById('btnConfirmClearLogs');
    const clearLogsSpinner = document.getElementById('clearLogsSpinner');

    if (btnClearLogs) {
        btnClearLogs.addEventListener('click', function() {
            if (clearLogsModal) {
                clearLogsModal.show();
            }
        });
    }

    if (btnConfirmClearLogs) {
        btnConfirmClearLogs.addEventListener('click', function() {
            btnConfirmClearLogs.disabled = true;
            clearLogsSpinner.classList.remove('d-none');

            fetch(`${indexUrl}/clear`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (clearLogsModal) clearLogsModal.hide();
                if (data.success) {
                    if (window.adminNotifications && typeof window.adminNotifications.success === 'function') {
                        window.adminNotifications.success(data.message);
                    } else {
                        alert(data.message);
                    }
                    loadLogs(1);
                } else {
                    alert(data.message || 'حدث خطأ أثناء الحذف');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                if (clearLogsModal) clearLogsModal.hide();
                alert('حدث خطأ أثناء حذف الـ logs');
            })
            .finally(() => {
                btnConfirmClearLogs.disabled = false;
                clearLogsSpinner.classList.add('d-none');
            });
        });
    }

    // Initial load
    loadLogs(1);
})();
</script>
@endpush





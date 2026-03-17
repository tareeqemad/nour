@extends('layouts.admin')

@section('title', 'الطلبات المقدمة')

@php
    $breadcrumbTitle = 'الطلبات المقدمة';
@endphp

@push('styles')
<style>
    /* ── Loading Overlay ── */
    .loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.92);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: .5rem;
    }

    /* ── Status Badge ── */
    .status-badge {
        font-size: .78rem;
        padding: .3rem .65rem;
        border-radius: 30px;
        font-weight: 600;
        letter-spacing: .3px;
        white-space: nowrap;
    }

    /* ── Details Modal Sections ── */
    .section-header {
        background: var(--primary-color, #19228f);
        color: #fff;
        padding: .55rem 1rem;
        border-radius: .4rem;
        font-weight: 700;
        font-size: .9rem;
        margin-bottom: .75rem;
    }
    .detail-field {
        display: flex;
        gap: .5rem;
        padding: .35rem .6rem;
        border-radius: .3rem;
        border: 1px solid #eef0f7;
        background: #f9fafc;
        margin-bottom: .4rem;
        font-size: .85rem;
        flex-wrap: wrap;
    }
    .detail-field .fn {
        font-weight: 700;
        color: #3b4863;
        min-width: 160px;
        flex-shrink: 0;
    }
    .detail-field .fv {
        color: #555;
    }
    .dt-row-separator {
        border: 0;
        border-top: 1px dashed #d0d5e8;
        margin: .5rem 0;
    }

    /* ── Responsive table ── */
    @media (max-width: 768px) {
        .detail-field .fn { min-width: 100%; margin-bottom: .15rem; }
    }

    /* ── Stat card hover ── */
    .stat-card { transition: transform .15s, box-shadow .15s; cursor: default; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.1) !important; }
</style>
@endpush

@section('content')
<div class="general-page" id="portalRequestsPage">
    <div class="row g-3">
        <div class="col-12">

            {{-- ── إحصائيات ── --}}
            <div class="row g-3 mb-3" id="statsRow">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm stat-card">
                        <div class="card-body text-center py-3">
                            <div class="text-muted small mb-1">
                                <i class="bi bi-collection me-1"></i> إجمالي (هذه الصفحة)
                            </div>
                            <h3 class="mb-0 fw-bold text-primary" id="statTotal">—</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm stat-card">
                        <div class="card-body text-center py-3">
                            <div class="text-muted small mb-1">
                                <i class="bi bi-check-circle me-1"></i> تم التنفيذ
                            </div>
                            <h3 class="mb-0 fw-bold text-success" id="statDone">—</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm stat-card">
                        <div class="card-body text-center py-3">
                            <div class="text-muted small mb-1">
                                <i class="bi bi-hourglass-split me-1"></i> قيد الطلب
                            </div>
                            <h3 class="mb-0 fw-bold text-info" id="statPending">—</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm stat-card">
                        <div class="card-body text-center py-3">
                            <div class="text-muted small mb-1">
                                <i class="bi bi-x-circle me-1"></i> مرفوضة
                            </div>
                            <h3 class="mb-0 fw-bold text-danger" id="statReturned">—</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── البطاقة الرئيسية ── --}}
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-inbox me-2"></i>
                            الطلبات المقدمة
                        </h5>
                        <div class="general-subtitle">
                            الطلبات المقدمة عبر البوابة الرقمية الفلسطينية
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnRefresh" title="تحديث">
                            <i class="bi bi-arrow-clockwise me-1"></i> تحديث
                        </button>
                    </div>
                </div>

                <div class="card-body pb-4 position-relative">

                    {{-- ── فلاتر البحث ── --}}
                    <div class="filter-card mb-3">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-funnel me-2"></i> فلاتر البحث
                            </h6>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" id="btnClearFilters">
                                <i class="bi bi-x-circle me-1"></i>مسح الفلاتر
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">

                                {{-- بحث برقم الطلب --}}
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-hash me-1 text-muted"></i>رقم الطلب
                                    </label>
                                    <input type="text" id="filterAppNo" class="form-control"
                                           placeholder="مثال: 785765" autocomplete="off">
                                </div>

                                {{-- بحث برقم الهوية --}}
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person-badge me-1 text-muted"></i>رقم هوية مقدم الطلب
                                    </label>
                                    <input type="text" id="filterApplicantId" class="form-control"
                                           placeholder="مثال: 802490599" autocomplete="off">
                                </div>

                                {{-- فلتر الحالة --}}
                                <div class="col-md-2 col-sm-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-flag me-1 text-muted"></i>حالة الطلب
                                    </label>
                                    <select id="filterStatus" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="0">مرفوض</option>
                                        <option value="1">قيد الطلب</option>
                                        <option value="90">مرجع للتعديل</option>
                                        <option value="100">تم تنفيذ الطلب بنجاح</option>
                                    </select>
                                </div>

                                {{-- فلتر التاريخ --}}
                                <div class="col-md-2 col-sm-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-calendar3 me-1 text-muted"></i>مقدمة منذ تاريخ
                                    </label>
                                    <input type="date" id="filterDate" class="form-control">
                                </div>

                                {{-- عدد النتائج --}}
                                <div class="col-md-1 col-sm-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-list-ol me-1 text-muted"></i>لكل صفحة
                                    </label>
                                    <select id="filterPerPage" class="form-select">
                                        <option value="10">10</option>
                                        <option value="15" selected>15</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="99">99</option>
                                    </select>
                                </div>

                                {{-- زر البحث --}}
                                <div class="col-md-1 col-sm-6 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" id="btnSearch">
                                        <i class="bi bi-search me-1"></i>بحث
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- ── Loading Overlay ── --}}
                    <div class="loading-overlay d-none" id="loadingOverlay">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-2" role="status"
                                 style="width:2.5rem;height:2.5rem;"></div>
                            <p class="text-muted mb-0 fw-semibold">جاري تحميل البيانات...</p>
                        </div>
                    </div>

                    {{-- ── الجدول ── --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 general-table">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th style="min-width:110px;">رقم الطلب</th>
                                    <th style="min-width:180px;">اسم مقدم الطلب</th>
                                    <th style="min-width:130px;">رقم الهوية</th>
                                    <th style="min-width:150px;">الحالة</th>
                                    <th class="d-none d-lg-table-cell" style="min-width:180px;">ملاحظة الحالة</th>
                                    <th class="d-none d-md-table-cell" style="min-width:130px;">تاريخ التقديم</th>
                                    <th class="d-none d-xl-table-cell" style="min-width:130px;">آخر تعديل</th>
                                    <th class="text-center" style="min-width:120px;">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2 text-muted mb-0">جاري تحميل البيانات...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- ── Pagination ── --}}
                    <div id="paginationContainer" class="mt-3"></div>

                </div>{{-- end card-body --}}
            </div>{{-- end general-card --}}
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     Modal: تفاصيل الطلب
════════════════════════════════════════════ --}}
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    تفاصيل الطلب رقم <span id="modalAppNo" class="text-primary fw-bold"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">جاري تحميل التفاصيل...</p>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div id="detailsStatusInfo" class="small text-muted"></div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     Modal: تغيير حالة الطلب
════════════════════════════════════════════ --}}
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeStatusModalLabel">
                    <i class="bi bi-arrow-left-right me-2 text-warning"></i>
                    تغيير حالة الطلب رقم <span id="csModalAppNo" class="text-primary fw-bold"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="csLoadingNextStatus" class="text-center py-3 d-none">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                    <span class="text-muted">جاري جلب الحالات المتاحة...</span>
                </div>
                <div id="csFormContainer">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            الحالة الجديدة <span class="text-danger">*</span>
                        </label>
                        <select id="csNewStatus" class="form-select">
                            <option value="">اختر الحالة...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ملاحظة (اختياري)</label>
                        <textarea id="csNote" class="form-control" rows="3"
                                  placeholder="أدخل ملاحظة تخص تغيير الحالة..."></textarea>
                    </div>
                    <div class="p-3 bg-light rounded border-start border-3 border-warning">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1 text-warning"></i>
                            سيتم تغيير حالة الطلب على منظومة البوابة الرقمية الفلسطينية مباشرة.
                            تأكد من اختيار الحالة الصحيحة قبل الحفظ.
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-warning" id="btnConfirmStatus">
                    <span class="spinner-border spinner-border-sm me-2 d-none" id="csSpinner"></span>
                    <i class="bi bi-check2 me-1"></i>
                    تأكيد التغيير
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* ─── إعدادات أساسية ─────────────────────────────────────────── */
    const indexUrl      = '{{ route("admin.portal-requests.index") }}';
    const showUrlBase   = '{{ url("admin/portal-requests") }}/';
    const changeUrlBase = '{{ url("admin/portal-requests") }}/';
    const csrfToken     = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let currentPage     = 1;
    let currentAppIdCS  = '';   // app_no المحدد لتغيير الحالة

    /* ─── ألوان الحالات ─────────────────────────────────────────── */
    function statusColor(code) {
        const c = parseInt(code, 10);
        if (c === 100) return 'success';   // تم تنفيذ الطلب
        if (c === 90)  return 'warning';   // مرجع للتعديل
        if (c === 0)   return 'danger';    // مرفوض
        if (c === 1)   return 'info';      // قيد الطلب
        return 'secondary';
    }

    /* ─── تحليل pure_data / pureData ───────────────────────────── */
    function parsePureData(raw) {
        if (!raw) return [];
        if (Array.isArray(raw)) return raw;
        try { return JSON.parse(raw); } catch { return []; }
    }

    /* ─── استخراج الاسم من pure_data ──────────────────────────── */
    function extractName(raw) {
        const sections = parsePureData(raw);
        if (!sections.length) return '';
        // القسم الأول – حقل "الاسم كاملاً"
        const firstSection = sections[0];
        const rows = firstSection?.dt_data ?? [];
        for (const row of rows) {
            if (!Array.isArray(row)) continue;
            for (const field of row) {
                if (field.fn && field.fn.includes('الاسم') && field.fv?.length) {
                    return field.fv[0] || '';
                }
            }
        }
        return '';
    }

    /* ─── تنسيق التاريخ ─────────────────────────────────────────── */
    function fmtDate(d) {
        if (!d) return '—';
        try {
            let parsed;
            const s = String(d).trim();

            // صيغة DD/MM/YYYY - نحولها يدوياً لأن JS يفسر / كـ MM/DD/YYYY
            const slashMatch = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (slashMatch) {
                parsed = new Date(+slashMatch[3], +slashMatch[2] - 1, +slashMatch[1]);
            } else {
                // استبدال المسافة بـ T لضمان تحليل ISO صحيح
                parsed = new Date(s.includes('T') ? s : s.replace(' ', 'T'));
            }

            if (isNaN(parsed.getTime())) return s;
            return parsed.toLocaleDateString('ar-EG', {
                year: 'numeric', month: 'short', day: 'numeric'
            });
        } catch { return d; }
    }

    /* ─── إشعارات ───────────────────────────────────────────────── */
    function notify(type, msg) {
        if (window.adminNotifications?.[type]) {
            window.adminNotifications[type](msg);
        } else { alert(msg); }
    }

    /* ─── جلب البيانات ──────────────────────────────────────────── */
    function loadData(page = 1) {
        const params = new URLSearchParams({
            ajax:         '1',
            page:         page,
            per_page:     document.getElementById('filterPerPage')?.value   || '15',
            app_no:       document.getElementById('filterAppNo')?.value     || '',
            applicant_id: document.getElementById('filterApplicantId')?.value || '',
            status:       document.getElementById('filterStatus')?.value    || '',
            date:         document.getElementById('filterDate')?.value      || '',
        });

        const overlay = document.getElementById('loadingOverlay');
        overlay?.classList.remove('d-none');

        fetch(`${indexUrl}?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            overlay?.classList.add('d-none');
            if (data.ok) {
                renderTable(data.data, data.pagination);
                renderPagination(data.pagination, data.total);
                updateStats(data.data);
                currentPage = page;
            } else {
                showTableError(data.message || 'حدث خطأ أثناء جلب البيانات');
            }
        })
        .catch(() => {
            overlay?.classList.add('d-none');
            showTableError('تعذر الاتصال بالخادم');
        });
    }

    /* ─── عرض الجدول ───────────────────────────────────────────── */
    function renderTable(items, pagination) {
        const tbody = document.getElementById('tableBody');

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                  <td colspan="9" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox display-6 d-block mb-2 opacity-25"></i>
                    لا توجد طلبات مطابقة للبحث
                  </td>
                </tr>`;
            return;
        }

        const startNum = ((pagination?.currentPage || 1) - 1)
                         * parseInt(document.getElementById('filterPerPage').value || 15, 10) + 1;

        tbody.innerHTML = items.map((item, i) => {
            const appNo    = item.app_no          || '—';
            const appId    = item.applicant_id     || '';
            const color    = statusColor(item.app_status);
            const statusTxt= item.desc_app_status  || item.app_status || '—';
            const rawNote  = item.status_note       || null;
            const note     = rawNote
                             ? `<span class="text-muted small" title="${escHtml(rawNote)}">${escHtml(String(rawNote).substring(0,50))}${String(rawNote).length>50?'...':''}</span>`
                             : '<span class="text-muted">—</span>';
            const pureDataSrc = item.pure_data      || null;
            const name     = escHtml(extractName(pureDataSrc));
            const insAt    = fmtDate(item.inserted_at);
            const chgAt    = fmtDate(item.changed_at);

            return `
            <tr>
              <td class="text-muted small">${startNum + i}</td>
              <td><strong class="text-primary">${escHtml(appNo)}</strong></td>
              <td>${name || '<span class="text-muted small">—</span>'}</td>
              <td><span class="font-monospace small">${escHtml(appId) || '—'}</span></td>
              <td>
                <span class="status-badge badge bg-${color}">
                  ${escHtml(statusTxt)}
                </span>
              </td>
              <td class="d-none d-lg-table-cell">${note}</td>
              <td class="d-none d-md-table-cell small text-muted">${insAt}</td>
              <td class="d-none d-xl-table-cell small text-muted">${chgAt}</td>
              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button onclick="showDetails('${escAttr(appNo)}')"
                          class="btn btn-sm btn-outline-primary" title="عرض التفاصيل">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button onclick="openChangeStatus('${escAttr(appNo)}')"
                          class="btn btn-sm btn-outline-warning" title="تغيير الحالة">
                    <i class="bi bi-arrow-left-right"></i>
                  </button>
                </div>
              </td>
            </tr>`;
        }).join('');
    }

    /* ─── إظهار خطأ في الجدول ──────────────────────────────────── */
    function showTableError(msg) {
        document.getElementById('tableBody').innerHTML = `
            <tr><td colspan="9" class="text-center py-5 text-danger">
              <i class="bi bi-exclamation-circle display-6 d-block mb-2"></i>
              ${escHtml(msg)}
            </td></tr>`;
        document.getElementById('paginationContainer').innerHTML = '';
        document.getElementById('statTotal').textContent = '—';
        document.getElementById('statDone').textContent  = '—';
        document.getElementById('statPending').textContent = '—';
        document.getElementById('statReturned').textContent = '—';
    }

    /* ─── إحصائيات ─────────────────────────────────────────────── */
    function updateStats(items) {
        if (!items) return;
        const done     = items.filter(x => parseInt(x.app_status, 10) === 100).length;
        const rejected = items.filter(x => parseInt(x.app_status, 10) === 0).length;
        const pending  = items.filter(x => parseInt(x.app_status, 10) === 1).length;

        document.getElementById('statTotal').textContent    = items.length;
        document.getElementById('statDone').textContent     = done;
        document.getElementById('statPending').textContent  = pending;
        document.getElementById('statReturned').textContent = rejected;
    }

    /* ─── Pagination ────────────────────────────────────────────── */
    function renderPagination(pagination, total) {
        const container  = document.getElementById('paginationContainer');
        const allPages   = pagination?.allPages    || 1;
        const curPage    = pagination?.currentPage || 1;

        if (allPages <= 1) {
            container.innerHTML = total > 0
                ? `<div class="small text-muted">عرض ${total} طلب</div>` : '';
            return;
        }

        let html = `<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="small text-muted">صفحة ${curPage} من ${allPages}</div>
            <nav><ul class="pagination pagination-sm mb-0">`;

        if (curPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#"
                        onclick="window.loadData(${curPage - 1});return false;">السابق</a></li>`;
        }

        // نعرض 5 صفحات حول الحالية
        const start = Math.max(1, curPage - 2);
        const end   = Math.min(allPages, curPage + 2);

        if (start > 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        for (let p = start; p <= end; p++) {
            html += p === curPage
                ? `<li class="page-item active"><span class="page-link">${p}</span></li>`
                : `<li class="page-item"><a class="page-link" href="#"
                       onclick="window.loadData(${p});return false;">${p}</a></li>`;
        }
        if (end < allPages) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;

        if (curPage < allPages) {
            html += `<li class="page-item"><a class="page-link" href="#"
                        onclick="window.loadData(${curPage + 1});return false;">التالي</a></li>`;
        }

        html += `</ul></nav></div>`;
        container.innerHTML = html;
    }

    /* ─── تفاصيل الطلب ─────────────────────────────────────────── */
    window.showDetails = function (appNo) {
        document.getElementById('modalAppNo').textContent = appNo;
        document.getElementById('detailsStatusInfo').textContent = '';
        document.getElementById('detailsModalBody').innerHTML = `
            <div class="text-center py-5">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted">جاري تحميل التفاصيل...</p>
            </div>`;

        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        modal.show();

        fetch(`${showUrlBase}${encodeURIComponent(appNo)}?ajax=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(res => {
            if (!res.ok) {
                document.getElementById('detailsModalBody').innerHTML =
                    `<div class="alert alert-danger">${escHtml(res.message || 'حدث خطأ')}</div>`;
                return;
            }

            const appData    = res.data;
            const pureData   = appData.pureData || appData.pure_data || [];
            const sections   = Array.isArray(pureData) ? pureData : parsePureData(pureData);
            const statusInfo = appData.status || {};
            const servInfo   = appData.data   || {};

            // رأس معلومات الطلب
            let html = `
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <div class="p-3 bg-light rounded border">
                  <div class="text-muted small mb-1">اسم الخدمة</div>
                  <div class="fw-semibold">${escHtml(servInfo.serv_name || '—')}</div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="p-3 bg-light rounded border">
                  <div class="text-muted small mb-1">رقم الخدمة</div>
                  <div class="fw-bold font-monospace">${escHtml(String(servInfo.serv_no || '—'))}</div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="p-3 bg-light rounded border">
                  <div class="text-muted small mb-1">حالة الطلب</div>
                  <span class="status-badge badge bg-${statusColor(statusInfo.status)}">
                    ${escHtml(String(statusInfo.desc || statusInfo.status || '—'))}
                  </span>
                </div>
              </div>
            </div>`;

            // أقسام البيانات
            for (const section of sections) {
                const dtName = section.dt_name || '';
                const dtData = section.dt_data || [];

                html += `<div class="mb-4">
                  <div class="section-header">
                    <i class="bi bi-folder2-open me-2"></i>${escHtml(dtName)}
                  </div>`;

                const rows = Array.isArray(dtData) ? dtData : [];
                rows.forEach((row, rowIdx) => {
                    if (rowIdx > 0) html += '<hr class="dt-row-separator">';
                    if (!Array.isArray(row)) return;
                    for (const field of row) {
                        const fn  = field.fn  || '';
                        const fvArr = Array.isArray(field.fv) ? field.fv.filter(Boolean) : [];
                        const fv  = fvArr.join('، ') || '—';
                        html += `<div class="detail-field">
                          <span class="fn">${escHtml(fn)}</span>
                          <span class="fv">${escHtml(fv)}</span>
                        </div>`;
                    }
                });

                html += `</div>`;
            }

            if (!sections.length) {
                html += `<div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>لا توجد بيانات تفصيلية متاحة لهذا الطلب
                </div>`;
            }

            document.getElementById('detailsModalBody').innerHTML = html;

            // معلومات الحالة في footer
            const nextStatuses = (appData.nextStatus || []).filter(s => s.APP_STATUS_CD);
            document.getElementById('detailsStatusInfo').innerHTML =
                nextStatuses.length
                    ? `<span class="text-muted">الحالات المتاحة للتغيير: </span>` +
                      nextStatuses.map(s =>
                          `<span class="badge bg-secondary me-1">${escHtml(s.APP_STATUS_DESC || s.APP_STATUS_CD)}</span>`
                      ).join('')
                    : '<span class="text-muted small">لا توجد حالات متاحة للتغيير</span>';
        })
        .catch(() => {
            document.getElementById('detailsModalBody').innerHTML =
                '<div class="alert alert-danger">حدث خطأ أثناء تحميل البيانات</div>';
        });
    };

    /* ─── فتح modal تغيير الحالة ────────────────────────────────── */
    window.openChangeStatus = function (appNo) {
        currentAppIdCS = appNo;
        document.getElementById('csModalAppNo').textContent = appNo;
        document.getElementById('csNote').value = '';

        // إعادة ضبط الـ select
        const sel = document.getElementById('csNewStatus');
        sel.innerHTML = '<option value="">جاري التحميل...</option>';
        sel.disabled  = true;

        document.getElementById('csLoadingNextStatus').classList.remove('d-none');

        const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
        modal.show();

        // جلب الحالات المتاحة
        fetch(`${showUrlBase}${encodeURIComponent(appNo)}?ajax=1`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            document.getElementById('csLoadingNextStatus').classList.add('d-none');

            if (!res.ok) {
                sel.innerHTML = '<option value="">تعذر جلب الحالات</option>';
                return;
            }

            const nextStatuses = (res.data?.nextStatus || []).filter(s => s.APP_STATUS_CD);
            sel.innerHTML = '<option value="">اختر الحالة الجديدة...</option>';

            // الحالات الثابتة كـ fallback دائم (حسب البيانات الفعلية للـ API)
            const allStatuses = [
                { cd: '0',   desc: 'مرفوض' },
                { cd: '1',   desc: 'قيد الطلب' },
                { cd: '90',  desc: 'مرجع للتعديل' },
                { cd: '100', desc: 'تم تنفيذ الطلب بنجاح' },
            ];

            if (nextStatuses.length) {
                // نعرض الحالات القادمة من الـ API
                nextStatuses.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value       = s.APP_STATUS_CD;
                    opt.textContent = `${s.APP_STATUS_CD} – ${s.APP_STATUS_DESC || ''}`;
                    sel.appendChild(opt);
                });
                sel.disabled = false;
            } else {
                // نعرض كل الحالات المعروفة كـ fallback
                allStatuses.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value       = s.cd;
                    opt.textContent = `${s.cd} – ${s.desc}`;
                    sel.appendChild(opt);
                });
                sel.disabled = false;
            }
        })
        .catch(() => {
            document.getElementById('csLoadingNextStatus').classList.add('d-none');
            sel.innerHTML = '<option value="">تعذر جلب الحالات</option>';
        });
    };

    /* ─── تأكيد تغيير الحالة ────────────────────────────────────── */
    document.getElementById('btnConfirmStatus')?.addEventListener('click', function () {
        const sel        = document.getElementById('csNewStatus');
        const newStatus  = sel.value.trim();
        const note       = document.getElementById('csNote')?.value.trim() || '';

        if (!newStatus) {
            notify('error', 'يرجى تحديد الحالة الجديدة');
            return;
        }

        if (!currentAppIdCS) return;

        const btn     = this;
        const spinner = document.getElementById('csSpinner');
        btn.disabled  = true;
        spinner.classList.remove('d-none');

        const csParams = new URLSearchParams({ newStatus, note });
        fetch(`${changeUrlBase}${encodeURIComponent(currentAppIdCS)}/change-status?${csParams}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                bootstrap.Modal.getInstance(document.getElementById('changeStatusModal'))?.hide();
                notify('success', res.message || 'تم تغيير الحالة بنجاح');
                loadData(currentPage);
            } else {
                notify('error', res.message || 'فشل تغيير الحالة');
            }
        })
        .catch(() => notify('error', 'حدث خطأ أثناء الاتصال'))
        .finally(() => {
            btn.disabled = false;
            spinner.classList.add('d-none');
        });
    });

    /* ─── Helpers ───────────────────────────────────────────────── */
    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;');
    }
    function escAttr(str) { return escHtml(str); }

    /* ─── Event Listeners ───────────────────────────────────────── */
    document.getElementById('btnSearch')?.addEventListener('click', () => loadData(1));
    document.getElementById('btnRefresh')?.addEventListener('click', () => loadData(currentPage));

    document.getElementById('filterPerPage')?.addEventListener('change', () => loadData(1));
    document.getElementById('filterStatus')?.addEventListener('change', () => loadData(1));

    // بحث بـ Enter
    ['filterAppNo','filterApplicantId'].forEach(id => {
        document.getElementById(id)?.addEventListener('keypress', e => {
            if (e.key === 'Enter') loadData(1);
        });
    });

    document.getElementById('filterDate')?.addEventListener('change', () => loadData(1));

    // مسح الفلاتر
    document.getElementById('btnClearFilters')?.addEventListener('click', () => {
        ['filterAppNo','filterApplicantId','filterDate'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const status = document.getElementById('filterStatus');
        if (status) status.value = '';
        const perPage = document.getElementById('filterPerPage');
        if (perPage) perPage.value = '15';
        loadData(1);
    });

    // reset عند إغلاق modal تغيير الحالة
    document.getElementById('changeStatusModal')?.addEventListener('hidden.bs.modal', () => {
        currentAppIdCS = '';
        const sel = document.getElementById('csNewStatus');
        sel.innerHTML = '<option value="">اختر الحالة...</option>';
        sel.disabled  = false;
    });

    /* ─── تصدير للـ Pagination ──────────────────────────────────── */
    window.loadData = loadData;

    /* ─── تحميل أولي ───────────────────────────────────────────── */
    loadData(1);

})();
</script>
@endpush

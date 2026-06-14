@extends('layouts.admin')

@section('title', 'أرشيف الطلبات')

@php
    $breadcrumbTitle = 'أرشيف الطلبات';
    $breadcrumbParent = 'البوابة';

    $statusColor = function ($code) {
        return match ((int) $code) {
            100 => 'success',
            90  => 'warning',
            0   => 'danger',
            1   => 'info',
            default => 'secondary',
        };
    };
@endphp

@push('styles')
<style>
    .status-badge { font-size: .78rem; padding: .3rem .65rem; border-radius: 30px; font-weight: 600; white-space: nowrap; }
    .portal-section-header {
        background: var(--color-bg-card-header, #F8FBFD);
        border: 1px solid var(--color-border-soft, #EDF1F5);
        border-inline-end: 3px solid var(--color-primary, #24308F);
        color: var(--color-primary, #24308F);
        padding: .55rem 1rem; border-radius: .4rem; font-weight: 700; font-size: .88rem; margin-bottom: .75rem;
    }
    .detail-field {
        display: flex; gap: .5rem; padding: .35rem .6rem; border-radius: .3rem;
        border: 1px solid var(--color-border-soft, #EDF1F5); background: #FAFCFF;
        margin-bottom: .4rem; font-size: .85rem; flex-wrap: wrap;
    }
    .detail-field .fn { font-weight: 700; color: var(--color-text-secondary, #3B4863); min-width: 160px; flex-shrink: 0; }
    .detail-field .fv { color: var(--color-text-main, #1F2937); }
    .dt-row-separator { border: 0; border-top: 1px dashed var(--color-border-soft, #EDF1F5); margin: .5rem 0; }
    @media (max-width: 768px) { .detail-field .fn { min-width: 100%; margin-bottom: .15rem; } }
</style>
@endpush

@section('content')
<div class="general-page" id="portalArchivePage">
    <div class="row g-3">
        <div class="col-12">

            {{-- شريط معلومات: مصدر محلي + آخر مزامنة --}}
            <div class="alert alert-secondary d-flex align-items-start gap-2 mb-3" role="alert">
                <i class="bi bi-database-check mt-1"></i>
                <div class="flex-grow-1">
                    <strong>أرشيف محلي:</strong>
                    هذه البيانات مخزّنة في قاعدة البيانات المحلية (جدول <code>portal_requests</code>) ولا تتصل بالـ API.
                    إجمالي السجلات: <strong>{{ number_format($stats['total']) }}</strong> —
                    آخر مزامنة:
                    <strong>{{ $stats['last_sync'] ? \Illuminate\Support\Carbon::parse($stats['last_sync'])->format('Y-m-d H:i') : 'لم تتم بعد' }}</strong>.
                    @if(!$stats['total'])
                        <div class="mt-1 text-danger">
                            الجدول فارغ — شغّل الأمر: <code>php artisan portal:sync-requests</code>
                        </div>
                    @endif
                </div>
                <a href="{{ route('admin.portal-requests.index') }}" class="btn btn-sm btn-outline-primary text-nowrap">
                    <i class="bi bi-broadcast me-1"></i> العرض المباشر من الـ API
                </a>
            </div>

            <x-admin.card>
                <x-admin.card-header title="أرشيف الطلبات المخزّنة" icon="bi-archive">
                    <x-slot:actions>
                        <span class="badge bg-light text-dark border">
                            النتائج: {{ number_format($requests->total()) }}
                        </span>
                    </x-slot:actions>
                </x-admin.card-header>

                <div class="card-body pb-4">

                    {{-- ── فلاتر البحث (GET) ── --}}
                    <form method="GET" action="{{ route('admin.portal-archive.index') }}" class="row g-3 align-items-end mb-3">
                        <div class="col-md-4 col-sm-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-search me-1 text-muted"></i>بحث (رقم الطلب / الهوية / الاسم)
                            </label>
                            <input type="text" name="search" value="{{ $search }}" class="form-control"
                                   placeholder="مثال: 785765 أو 802490599 أو اسم" autocomplete="off">
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-flag me-1 text-muted"></i>حالة الطلب
                            </label>
                            <select name="status" class="form-select">
                                <option value="">الكل</option>
                                <option value="0"   {{ $status === '0'   ? 'selected' : '' }}>مرفوض</option>
                                <option value="1"   {{ $status === '1'   ? 'selected' : '' }}>قيد الطلب</option>
                                <option value="90"  {{ $status === '90'  ? 'selected' : '' }}>مرجع للتعديل</option>
                                <option value="100" {{ $status === '100' ? 'selected' : '' }}>تم تنفيذ الطلب بنجاح</option>
                            </select>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar3 me-1 text-muted"></i>تاريخ التقديم
                            </label>
                            <input type="date" name="date" value="{{ $date }}" class="form-control">
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-list-ol me-1 text-muted"></i>لكل صفحة
                            </label>
                            <select name="per_page" class="form-select">
                                @foreach([25, 50, 100, 200] as $pp)
                                    <option value="{{ $pp }}" {{ $perPage === $pp ? 'selected' : '' }}>{{ $pp }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 col-sm-6 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-search me-1"></i>بحث
                            </button>
                            <a href="{{ route('admin.portal-archive.index') }}" class="btn btn-outline-secondary" title="تفريغ">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </form>

                    {{-- ── الجدول ── --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 general-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th style="min-width:110px;">رقم الطلب</th>
                                    <th style="min-width:180px;">اسم مقدم الطلب</th>
                                    <th style="min-width:130px;">رقم الهوية</th>
                                    <th style="min-width:150px;">حالة الطلب</th>
                                    <th class="d-none d-md-table-cell" style="min-width:130px;">تاريخ التقديم</th>
                                    <th class="d-none d-xl-table-cell" style="min-width:130px;">آخر تعديل</th>
                                    <th class="d-none d-lg-table-cell" style="min-width:130px;">آخر مزامنة</th>
                                    <th class="text-center" style="width:90px;">تفاصيل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $r)
                                    <tr>
                                        <td class="text-muted small">{{ $requests->firstItem() + $loop->index }}</td>
                                        <td><strong class="text-primary">{{ $r->app_no }}</strong></td>
                                        <td>{{ $r->applicant_name ?: '—' }}</td>
                                        <td><span class="font-monospace small">{{ $r->applicant_id ?: '—' }}</span></td>
                                        <td>
                                            <span class="status-badge badge bg-{{ $statusColor($r->app_status) }}">
                                                {{ $r->desc_app_status ?: ($r->app_status ?: '—') }}
                                            </span>
                                        </td>
                                        <td class="d-none d-md-table-cell small text-muted">
                                            {{ $r->inserted_at ? $r->inserted_at->format('Y-m-d') : '—' }}
                                        </td>
                                        <td class="d-none d-xl-table-cell small text-muted">
                                            {{ $r->changed_at ? $r->changed_at->format('Y-m-d') : '—' }}
                                        </td>
                                        <td class="d-none d-lg-table-cell small text-muted">
                                            {{ $r->synced_at ? $r->synced_at->format('Y-m-d H:i') : '—' }}
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="showArchiveDetails('{{ $r->app_no }}')" title="عرض التفاصيل">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox display-6 d-block mb-2 opacity-25"></i>
                                            لا توجد طلبات مطابقة
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $requests->links() }}
                    </div>

                </div>
            </x-admin.card>
        </div>
    </div>
</div>

{{-- نافذة التفاصيل --}}
<div class="modal fade" id="archiveDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    تفاصيل الطلب رقم <span id="archiveModalAppNo" class="text-primary fw-bold"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="archiveModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">جاري تحميل التفاصيل...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const showUrlBase = '{{ url("admin/portal-archive") }}/';

    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function statusColor(code) {
        const c = parseInt(code, 10);
        if (c === 100) return 'success';
        if (c === 90)  return 'warning';
        if (c === 0)   return 'danger';
        if (c === 1)   return 'info';
        return 'secondary';
    }

    function parsePureData(raw) {
        if (!raw) return [];
        if (Array.isArray(raw)) return raw;
        try { return JSON.parse(raw); } catch { return []; }
    }

    window.showArchiveDetails = function (appNo) {
        document.getElementById('archiveModalAppNo').textContent = appNo;
        document.getElementById('archiveModalBody').innerHTML = `
            <div class="text-center py-5">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted">جاري تحميل التفاصيل...</p>
            </div>`;

        new bootstrap.Modal(document.getElementById('archiveDetailsModal')).show();

        fetch(`${showUrlBase}${encodeURIComponent(appNo)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            if (!res.ok) {
                document.getElementById('archiveModalBody').innerHTML =
                    `<div class="alert alert-danger">${escHtml(res.message || 'حدث خطأ')}</div>`;
                return;
            }

            const d = res.data;
            const sections = parsePureData(d.pure_data);

            let html = `
            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <div class="p-3 bg-light rounded border">
                  <div class="text-muted small mb-1">اسم مقدم الطلب</div>
                  <div class="fw-semibold">${escHtml(d.applicant_name || '—')}</div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="p-3 bg-light rounded border">
                  <div class="text-muted small mb-1">رقم الهوية</div>
                  <div class="fw-bold font-monospace">${escHtml(d.applicant_id || '—')}</div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="p-3 bg-light rounded border">
                  <div class="text-muted small mb-1">رقم الخدمة</div>
                  <div class="fw-bold font-monospace">${escHtml(d.ser_no || '—')}</div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="p-3 bg-light rounded border">
                  <div class="text-muted small mb-1">حالة الطلب</div>
                  <span class="status-badge badge bg-${statusColor(d.app_status)}">
                    ${escHtml(d.desc_app_status || d.app_status || '—')}
                  </span>
                </div>
              </div>
            </div>`;

            if (d.status_note) {
                html += `<div class="alert alert-warning py-2"><strong>ملاحظة الحالة:</strong> ${escHtml(d.status_note)}</div>`;
            }

            for (const section of sections) {
                const dtName = section.dt_name || '';
                const dtData = Array.isArray(section.dt_data) ? section.dt_data : [];
                html += `<div class="mb-4">
                  <div class="portal-section-header"><i class="bi bi-folder2-open me-2"></i>${escHtml(dtName)}</div>`;
                dtData.forEach((row, rowIdx) => {
                    if (rowIdx > 0) html += '<hr class="dt-row-separator">';
                    if (!Array.isArray(row)) return;
                    for (const field of row) {
                        const fn = field.fn || '';
                        const fvArr = Array.isArray(field.fv) ? field.fv.filter(Boolean) : [];
                        const fv = fvArr.join('، ') || '—';
                        html += `<div class="detail-field"><span class="fn">${escHtml(fn)}</span><span class="fv">${escHtml(fv)}</span></div>`;
                    }
                });
                html += `</div>`;
            }

            if (!sections.length) {
                html += `<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>لا توجد بيانات تفصيلية مخزّنة لهذا الطلب</div>`;
            }

            html += `<div class="text-muted small mt-2">
                تاريخ التقديم: ${escHtml(d.inserted_at || '—')} ·
                آخر تعديل: ${escHtml(d.changed_at || '—')} ·
                آخر مزامنة: ${escHtml(d.synced_at || '—')}
            </div>`;

            document.getElementById('archiveModalBody').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('archiveModalBody').innerHTML =
                '<div class="alert alert-danger">تعذر تحميل التفاصيل</div>';
        });
    };
})();
</script>
@endpush

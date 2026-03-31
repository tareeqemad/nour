@extends('layouts.admin')

@section('title', 'كشف القراءات الجماعية')

@php
    $breadcrumbTitle = 'كشف القراءات الجماعية';
    $breadcrumbParent = 'قراءات العدادات';
    $breadcrumbParentUrl = route('admin.meter-readings.index');
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
<style>
    .bulk-table input[type="number"] { width:140px; text-align:center; }
    .bulk-table td { vertical-align:middle; white-space:nowrap; }
    .consumption-cell { font-weight:700; }
    .consumption-cell.negative { color:var(--color-danger,#EF4444); }
    .consumption-cell.positive { color:var(--color-success,#10B981); }
    .total-row { background:#f0f9ff; font-weight:700; }
</style>
@endpush

@section('content')
<input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="كشف القراءات الجماعية" icon="bi-journal-text">
                    <x-slot:actions>
                        <a href="{{ route('admin.meter-readings.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>رجوع
                        </a>
                    </x-slot:actions>
                </x-admin.card-header>

                <div class="card-body">
                    {{-- الفلاتر --}}
                    <div class="row g-3">
                        {{-- المشغل --}}
                        @if($operator === null)
                            {{-- SuperAdmin: operator dropdown --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-building me-1"></i>المشغل <span class="text-danger">*</span>
                                </label>
                                <select id="operatorSelect" class="form-select" required>
                                    <option value="">اختر المشغل</option>
                                    @foreach($operators as $op)
                                        <option value="{{ $op->id }}">{{ $op->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            {{-- Affiliated user: locked operator --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-building me-1"></i>المشغل
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill text-muted"></i></span>
                                    <input type="text" class="form-control bg-light" value="{{ $operator->name }}" disabled readonly>
                                </div>
                                <input type="hidden" id="operatorSelect" value="{{ $operator->id }}">
                            </div>
                        @endif

                        {{-- وحدة التوليد --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-lightning-charge me-1"></i>وحدة التوليد <span class="text-danger">*</span>
                            </label>
                            <select id="unitFilter" class="form-select" required @if($operator === null) disabled @endif>
                                @if($operator === null)
                                    <option value="">اختر المشغل أولاً</option>
                                @else
                                    <option value="">-- اختر --</option>
                                    @foreach($generationUnits as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->unit_code }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-mailbox me-1"></i>رقم الصندوق
                            </label>
                            <input type="text" id="boxFilter" class="form-control" placeholder="0000" maxlength="4">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar3 me-1"></i>تاريخ القراءة الحالية <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="readingDate" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button class="btn btn-primary" id="fetchBtn">
                                <i class="bi bi-search me-1"></i>جلب المشتركين
                            </button>
                        </div>
                    </div>

                    {{-- معلومات --}}
                    <div id="infoBar" class="d-none mt-3" style="background:var(--color-info-bg,#F0F9FF);border:1px solid var(--color-info-border,#BAE6FD);border-radius:8px;padding:0.5rem 1rem;font-size:0.85rem;color:var(--color-info-text,#0369A1);">
                        <i class="bi bi-people me-1"></i>
                        تم جلب <strong id="rowCount">0</strong> مشترك. أدخل القراءات الحالية ثم اضغط حفظ.
                    </div>

                    {{-- الجدول --}}
                    <div id="tableWrap" class="d-none mt-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0 text-center bulk-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>رقم القراءة</th>
                                        <th>رقم الصندوق</th>
                                        <th>رقم الاشتراك</th>
                                        <th>اسم المشترك</th>
                                        <th>القراءة السابقة</th>
                                        <th>تاريخ السابقة</th>
                                        <th>القراءة الحالية</th>
                                        <th>كمية الاستهلاك</th>
                                        <th>إجراء</th>
                                    </tr>
                                </thead>
                                <tbody id="readingsBody"></tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="8" class="text-start fw-bold">إجمالي الاستهلاك</td>
                                        <td id="totalConsumption" class="fw-bold text-primary">0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- أزرار --}}
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button class="btn btn-success" id="saveBtn">
                                <i class="bi bi-check-lg me-1"></i>حفظ القراءات
                            </button>
                            <button class="btn btn-outline-info" id="exportBtn">
                                <i class="bi bi-file-earmark-excel me-1"></i>تصدير Excel
                            </button>
                        </div>
                    </div>

                    {{-- Loading --}}
                    <div id="bulkLoading" class="d-none text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted fw-semibold" id="loadingText">جاري جلب المشتركين...</div>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const fetchUrl = @json(route('admin.meter-readings.bulk-create.fetch'));
    const storeUrl = @json(route('admin.meter-readings.bulk-create.store'));
    const exportUrl = @json(route('admin.meter-readings.bulk-create.export'));
    const csrf = $('#csrfToken').val();

    let rows = [];

    // ===== تحميل وحدات التوليد عند تغيير المشغل =====
    $('#operatorSelect').on('change', function() {
        var operatorId = $(this).val();
        var $unitSelect = $('#unitFilter');

        $unitSelect.empty();

        if (!operatorId) {
            $unitSelect.append('<option value="">اختر المشغل أولاً</option>');
            $unitSelect.prop('disabled', true);
            return;
        }

        $unitSelect.append('<option value="">جاري التحميل...</option>');
        $unitSelect.prop('disabled', true);

        $.ajax({
            url: '/admin/operators/' + operatorId + '/generation-units',
            success: function(data) {
                $unitSelect.empty().append('<option value="">-- اختر وحدة التوليد --</option>');
                if (data.generation_units) {
                    data.generation_units.forEach(function(u) {
                        $unitSelect.append($('<option>', { value: u.id, text: u.label || (u.name + ' (' + u.unit_code + ')') }));
                    });
                }
                $unitSelect.prop('disabled', false);
            },
            error: function() {
                $unitSelect.empty().append('<option value="">خطأ في التحميل</option>');
                $unitSelect.prop('disabled', true);
            }
        });
    });

    // ===== جلب المشتركين =====
    $('#fetchBtn').on('click', function() {
        const unitId = $('#unitFilter').val();
        const boxNumber = $('#boxFilter').val();
        const readingDate = $('#readingDate').val();

        if (!unitId && !boxNumber) {
            alert('يرجى اختيار وحدة التوليد أو إدخال رقم الصندوق');
            return;
        }
        if (!readingDate) {
            alert('يرجى تحديد تاريخ القراءة');
            return;
        }

        $('#tableWrap, #infoBar').addClass('d-none');
        $('#bulkLoading').removeClass('d-none');

        $.ajax({
            url: fetchUrl,
            method: 'POST',
            data: { generation_unit_id: unitId, box_number: boxNumber, reading_date: readingDate, _token: csrf },
            success: function(res) {
                if (res.success) {
                    rows = res.rows;
                    renderTable();
                    $('#rowCount').text(res.count);
                    $('#infoBar').removeClass('d-none');
                    if (res.count > 0) $('#tableWrap').removeClass('d-none');
                } else {
                    alert(res.message || 'حدث خطأ');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'حدث خطأ أثناء جلب البيانات');
            },
            complete: function() {
                $('#bulkLoading').addClass('d-none');
            }
        });
    });

    // ===== رسم الجدول =====
    function renderTable() {
        const $body = $('#readingsBody');
        if (rows.length === 0) {
            $body.html('<tr><td colspan="10" class="text-muted py-4">لا يوجد مشتركين مطابقين</td></tr>');
            return;
        }

        let html = '';
        rows.forEach((r, i) => {
            html += `<tr data-idx="${i}">
                <td>${i + 1}</td>
                <td><code class="text-primary">${r.reading_number}</code></td>
                <td>${r.box_number || '—'}</td>
                <td><code>${r.subscription_number}</code></td>
                <td class="text-start">${r.subscriber_name}</td>
                <td>${parseFloat(r.previous_reading).toFixed(2)}</td>
                <td>${r.previous_reading_date || '—'}</td>
                <td>
                    <input type="number" class="form-control form-control-sm current-reading"
                           data-idx="${i}" step="0.01" min="0" placeholder="—">
                </td>
                <td class="consumption-cell" data-idx="${i}">—</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger remove-row" data-idx="${i}" title="حذف">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
        });
        $body.html(html);
        updateTotal();
    }

    // ===== حساب الاستهلاك فوري =====
    $(document).on('input', '.current-reading', function() {
        const idx = $(this).data('idx');
        const current = parseFloat($(this).val()) || 0;
        const prev = parseFloat(rows[idx].previous_reading) || 0;
        const consumption = current - prev;
        const $cell = $(`.consumption-cell[data-idx="${idx}"]`);

        if ($(this).val() === '') {
            $cell.text('—').removeClass('positive negative');
        } else {
            $cell.text(consumption.toFixed(2))
                 .toggleClass('positive', consumption >= 0)
                 .toggleClass('negative', consumption < 0);
        }
        updateTotal();
    });

    // ===== حذف صف =====
    $(document).on('click', '.remove-row', function() {
        const idx = $(this).data('idx');
        rows.splice(idx, 1);
        renderTable();
        $('#rowCount').text(rows.length);
    });

    // ===== تغيير التاريخ → يحدّث أرقام القراءات =====
    $('#readingDate').on('change', function() {
        const date = $(this).val().replace(/-/g, '');
        rows.forEach((r, i) => {
            r.reading_number = date + '-' + String(i + 1).padStart(5, '0');
        });
        // تحديث العرض بدون مسح القراءات المدخلة
        const inputs = {};
        $('.current-reading').each(function() {
            const val = $(this).val();
            if (val) inputs[$(this).data('idx')] = val;
        });
        renderTable();
        // إعادة القيم المدخلة
        Object.entries(inputs).forEach(([idx, val]) => {
            $(`.current-reading[data-idx="${idx}"]`).val(val).trigger('input');
        });
    });

    // ===== إجمالي الاستهلاك =====
    function updateTotal() {
        let total = 0;
        $('.current-reading').each(function() {
            const val = parseFloat($(this).val());
            if (!isNaN(val)) {
                const idx = $(this).data('idx');
                const prev = parseFloat(rows[idx]?.previous_reading) || 0;
                const c = val - prev;
                if (c > 0) total += c;
            }
        });
        $('#totalConsumption').text(total.toFixed(2));
    }

    // ===== جمع بيانات الجدول =====
    function collectData() {
        const readingDate = $('#readingDate').val();
        const readings = [];

        $('.current-reading').each(function() {
            const val = $(this).val();
            if (val !== '' && val !== null) {
                const idx = $(this).data('idx');
                readings.push({
                    subscriber_id: rows[idx].subscriber_id,
                    current_reading: parseFloat(val),
                    reading_date: readingDate,
                });
            }
        });

        return readings;
    }

    function collectExportData() {
        const readingDate = $('#readingDate').val();
        const data = [];

        rows.forEach((r, i) => {
            const $input = $(`.current-reading[data-idx="${i}"]`);
            data.push({
                reading_number: r.reading_number,
                box_number: r.box_number,
                subscription_number: r.subscription_number,
                subscriber_name: r.subscriber_name,
                previous_reading: r.previous_reading,
                previous_reading_date: r.previous_reading_date,
                current_reading: $input.val() || '',
                reading_date: readingDate,
            });
        });

        return data;
    }

    // ===== حفظ =====
    $('#saveBtn').on('click', function() {
        const readings = collectData();
        if (readings.length === 0) {
            alert('لم يتم إدخال أي قراءة حالية');
            return;
        }
        if (!confirm(`هل تريد حفظ ${readings.length} قراءة؟`)) return;

        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>جاري الحفظ...');

        $.ajax({
            url: storeUrl,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ readings: readings, _token: csrf }),
            headers: { 'X-CSRF-TOKEN': csrf },
            success: function(res) {
                if (res.success) {
                    alert(res.message);
                    if (res.errors && res.errors.length > 0) {
                        console.warn('Errors:', res.errors);
                    }
                    // إعادة جلب لإزالة المحفوظة
                    $('#fetchBtn').trigger('click');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'حدث خطأ أثناء الحفظ');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>حفظ القراءات');
            }
        });
    });

    // ===== تصدير Excel =====
    $('#exportBtn').on('click', function() {
        const data = collectExportData();
        if (data.length === 0) {
            alert('لا توجد بيانات للتصدير');
            return;
        }

        // إرسال عبر form مخفي (لأن POST download)
        const $form = $('<form>', { method: 'POST', action: exportUrl });
        $form.append($('<input>', { type: 'hidden', name: '_token', value: csrf }));
        data.forEach((r, i) => {
            Object.entries(r).forEach(([key, val]) => {
                $form.append($('<input>', { type: 'hidden', name: `rows[${i}][${key}]`, value: val }));
            });
        });
        $('body').append($form);
        $form.submit().remove();
    });
})();
</script>
@endpush

@extends('layouts.admin')

@section('title', 'إنشاء فاتورة جديدة')

@php $breadcrumbTitle = 'إنشاء فاتورة جديدة'; @endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form title="إنشاء فاتورة جديدة" icon="bi-receipt" :backRoute="route('admin.invoices.index')" />

                <div class="card-body">
                    @if($errors->any())
                        <div style="background: var(--color-danger-bg, #FEF2F2); border: 1px solid var(--color-danger-border, #FECACA); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.85rem; color: var(--color-danger-text, #B91C1C);">
                            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.invoices.store') }}" id="invoiceForm">
                        @csrf

                        {{-- 1. ربط بقراءة عداد --}}
                        <div class="invoice-section">
                            <div class="invoice-section-header">
                                <i class="bi bi-link-45deg"></i>
                                ربط بقراءة عداد
                            </div>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">
                                        القراءة المعتمدة <small class="text-muted">(اختياري)</small>
                                    </label>
                                    <select name="meter_reading_id" id="readingSelect" class="form-select">
                                        <option value="">-- بدون ربط قراءة --</option>
                                        @foreach($readings as $r)
                                            <option value="{{ $r->id }}" {{ (isset($selectedReading) && $selectedReading->id === $r->id) ? 'selected' : '' }}>
                                                {{ $r->reading_number }} | {{ $r->subscriber->subscriber_name }} | {{ $r->reading_date->format('Y-m-d') }} | {{ number_format($r->consumption_kwh, 2) }} kWh
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" id="loadReadingBtn" class="btn btn-outline-info w-100">
                                        <i class="bi bi-arrow-down-circle me-1"></i>
                                        تحميل البيانات
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- 2. بيانات المشترك --}}
                        <div class="invoice-section">
                            <div class="invoice-section-header">
                                <i class="bi bi-person"></i>
                                بيانات الاشتراك
                            </div>
                            <input type="hidden" name="subscriber_id" id="subscriberId" value="{{ old('subscriber_id') }}">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">رقم الاشتراك</label>
                                    <input type="text" id="subscriptionNumber" class="form-control" readonly placeholder="يُجلب تلقائياً">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">اسم المشترك</label>
                                    <input type="text" id="subscriberName" class="form-control" readonly placeholder="يُجلب تلقائياً">
                                </div>
                            </div>
                        </div>

                        {{-- 3. بيانات الفترة والاستهلاك --}}
                        <div class="invoice-section">
                            <div class="invoice-section-header">
                                <i class="bi bi-calendar-range"></i>
                                الفترة والاستهلاك
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">تاريخ الفاتورة <span class="text-danger">*</span></label>
                                    <input type="date" name="invoice_date" id="invoiceDate" class="form-control @error('invoice_date') is-invalid @enderror"
                                        value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required>
                                    @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">الفترة (أيام)</label>
                                    <input type="number" name="consumption_period_days" id="periodDays" class="form-control"
                                        value="{{ old('consumption_period_days', 0) }}" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">الاستهلاك kWh <span class="text-danger">*</span></label>
                                    <input type="number" name="consumption_kwh" id="consumptionKwh" class="form-control @error('consumption_kwh') is-invalid @enderror"
                                        value="{{ old('consumption_kwh', 0) }}" step="0.01" required>
                                    @error('consumption_kwh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">الاستحقاق</label>
                                    <input type="date" name="due_date" id="dueDate" class="form-control @error('due_date') is-invalid @enderror"
                                        value="{{ old('due_date') }}">
                                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- 4. التسعير --}}
                        <div class="invoice-section">
                            <div class="invoice-section-header">
                                <i class="bi bi-calculator"></i>
                                التسعير
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        سعر الكيلوواط (₪)
                                        @if($activeTariff)
                                            <small style="color: var(--color-success-text, #047857);">({{ $activeTariff->price_per_kwh }})</small>
                                        @endif
                                    </label>
                                    <input type="number" name="price_per_kwh" id="pricePerKwh" class="form-control @error('price_per_kwh') is-invalid @enderror"
                                        value="{{ old('price_per_kwh', $activeTariff?->price_per_kwh ?? 0) }}" step="0.0001" min="0" required>
                                    @error('price_per_kwh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">الخصم (%)</label>
                                    <input type="number" name="discount_rate" id="discountRate" class="form-control @error('discount_rate') is-invalid @enderror"
                                        value="{{ old('discount_rate', 0) }}" step="0.01" min="0" max="100">
                                    @error('discount_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">الحد الأدنى (₪)</label>
                                    <input type="number" name="minimum_charge" id="minimumCharge" class="form-control"
                                        value="{{ old('minimum_charge', $minimumCharge) }}" step="0.01" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">ثمن الاستهلاك (₪)</label>
                                    <input type="text" id="consumptionCost" class="form-control" readonly placeholder="0.00">
                                    <small class="text-muted">kWh × سعر × (1-خصم)</small>
                                </div>
                            </div>
                        </div>

                        {{-- 5. ملخص الفاتورة --}}
                        <div class="invoice-section" style="background: #FAFCFF; border: 1px solid var(--color-border, #E5E7EB); border-radius: 10px; padding: 1.25rem;">
                            <div class="invoice-section-header" style="color: var(--color-primary, #24308F); font-size: 0.95rem;">
                                <i class="bi bi-receipt"></i>
                                ملخص الفاتورة
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">قيمة الفاتورة (₪)</label>
                                    <input type="number" name="invoice_amount" id="invoiceAmount" class="form-control fw-bold"
                                        value="{{ old('invoice_amount', 0) }}" step="0.01" readonly>
                                    <small class="text-muted">max(استهلاك, حد أدنى)</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">الرصيد السابق (₪)</label>
                                    <input type="number" name="previous_balance" id="previousBalance" class="form-control"
                                        value="{{ old('previous_balance', 0) }}" step="0.01">
                                    <small class="text-muted">موجب=مديون · سالب=دائن</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold" style="color: var(--color-primary, #24308F);">المبلغ المطلوب (₪)</label>
                                    <input type="number" name="total_amount" id="totalAmount" class="form-control fw-bold" style="font-size: 1.1rem; color: var(--color-primary, #24308F); border-color: var(--color-primary, #24308F);"
                                        value="{{ old('total_amount', 0) }}" step="0.01" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">ملاحظات</label>
                                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                            <div class="d-none mt-3" id="creditNote" style="background: var(--color-info-bg, #F0F9FF); border: 1px solid var(--color-info-border, #BAE6FD); border-radius: 8px; padding: 0.65rem 1rem; font-size: 0.85rem; color: var(--color-info-text, #0369A1);">
                                <i class="bi bi-info-circle me-1"></i>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid var(--color-border-soft, #EDF1F5);">
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i>حفظ كمسودة
                            </button>
                        </div>
                    </form>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const readingDataUrl = @json(route('admin.invoices.reading-data'));

    function calcInvoice() {
        const kwh       = parseFloat($('#consumptionKwh').val()) || 0;
        const price     = parseFloat($('#pricePerKwh').val()) || 0;
        const discount  = parseFloat($('#discountRate').val()) || 0;
        const minCharge = parseFloat($('#minimumCharge').val()) || 0;
        const prevBal   = parseFloat($('#previousBalance').val()) || 0;

        // الخطوة 1: ثمن الاستهلاك = kWh × سعر × (1 - خصم/100)
        const consumptionCost = kwh * price * (1 - discount / 100);

        // الخطوة 2: قيمة الفاتورة = max(ثمن الاستهلاك, الحد الأدنى)
        const invoiceAmount = Math.max(consumptionCost, minCharge);

        // الخطوة 3: المبلغ المطلوب = قيمة_الفاتورة + رصيد_سابق (لا يقل عن صفر)
        const rawTotal    = invoiceAmount + prevBal;
        const totalAmount = Math.max(rawTotal, 0);

        $('#consumptionCost').val(consumptionCost.toFixed(2));
        $('#invoiceAmount').val(invoiceAmount.toFixed(2));
        $('#totalAmount').val(totalAmount.toFixed(2));

        if (rawTotal < 0) {
            $('#creditNote').html('<i class="bi bi-info-circle me-1"></i>ملاحظة: المبلغ الناتج سالب (' + rawTotal.toFixed(2) + ' ₪) سيُرحَّل كرصيد دائن لصالح المشترك.').removeClass('d-none');
        } else {
            $('#creditNote').addClass('d-none').html('');
        }
    }

    $('#loadReadingBtn').on('click', function () {
        const rid = $('#readingSelect').val();
        if (!rid) { alert('يرجى اختيار قراءة أولاً.'); return; }

        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.get(readingDataUrl, { reading_id: rid }, function (res) {
            if (!res.success) { alert(res.message); return; }
            const d = res.data;
            $('#subscriberId').val(d.subscriber_id);
            $('#subscriptionNumber').val(d.subscription_number);
            $('#subscriberName').val(d.subscriber_name);
            $('#consumptionKwh').val(d.consumption_kwh);
            $('#periodDays').val(d.consumption_period_days);
            $('#invoiceDate').val(d.reading_date);
            $('#pricePerKwh').val(d.price_per_kwh);
            $('#discountRate').val(d.discount_rate);
            $('#minimumCharge').val(d.minimum_charge ?? 0);
            $('#previousBalance').val(d.previous_balance);
            $('#consumptionCost').val(d.consumption_cost);
            $('#invoiceAmount').val(d.invoice_amount);
            $('#totalAmount').val(d.total_amount);
            // تشغيل إعادة الاحتساب لتحديث تنبيه الرصيد الدائن
            calcInvoice();
        }).always(function () {
            $('#loadReadingBtn').prop('disabled', false)
                .html('<i class="bi bi-arrow-down-circle me-1"></i>تحميل بيانات القراءة');
        });
    });

    $('#consumptionKwh, #pricePerKwh, #discountRate, #minimumCharge, #previousBalance').on('input', calcInvoice);

    // تحميل تلقائي إذا كانت قراءة محددة مسبقاً
    @if(isset($selectedReading))
        $('#loadReadingBtn').trigger('click');
    @endif
})();
</script>
@endpush

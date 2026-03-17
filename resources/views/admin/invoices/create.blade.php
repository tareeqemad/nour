@extends('layouts.admin')

@section('title', 'إنشاء فاتورة جديدة')

@php $breadcrumbTitle = 'إنشاء فاتورة جديدة'; @endphp

@section('content')
<div class="general-page">
    <div class="row g-3 justify-content-center">
        <div class="col-lg-10">
            <div class="general-card">
                <div class="general-card-header">
                    <h5 class="general-title"><i class="bi bi-receipt me-2"></i>إنشاء فاتورة جديدة</h5>
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>العودة للقائمة
                    </a>
                </div>

                <div class="card-body pb-4">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.invoices.store') }}" id="invoiceForm">
                        @csrf

                        {{-- اختيار القراءة المرتبطة --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold border-bottom pb-2">
                                    <i class="bi bi-link-45deg me-1"></i>
                                    ربط بقراءة عداد
                                </h6>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">
                                    القراءة المعتمدة <small class="text-muted">(اختر قراءة لملء البيانات تلقائياً)</small>
                                </label>
                                <select name="meter_reading_id" id="readingSelect" class="form-select">
                                    <option value="">-- بدون ربط قراءة --</option>
                                    @foreach($readings as $r)
                                        <option value="{{ $r->id }}"
                                            {{ (isset($selectedReading) && $selectedReading->id === $r->id) ? 'selected' : '' }}>
                                            {{ $r->reading_number }} |
                                            {{ $r->subscriber->subscription_number }} - {{ $r->subscriber->subscriber_name }} |
                                            {{ $r->reading_date->format('Y-m-d') }} |
                                            {{ number_format($r->consumption_kwh, 2) }} kWh
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" id="loadReadingBtn" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-arrow-down-circle me-1"></i>
                                    تحميل بيانات القراءة
                                </button>
                            </div>
                        </div>

                        {{-- بيانات المشترك --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold border-bottom pb-2">
                                    <i class="bi bi-person me-1"></i>بيانات الاشتراك
                                </h6>
                            </div>
                            <input type="hidden" name="subscriber_id" id="subscriberId" value="{{ old('subscriber_id') }}">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">رقم الاشتراك</label>
                                <input type="text" id="subscriptionNumber" class="form-control" readonly placeholder="يُجلب تلقائياً">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">اسم المشترك</label>
                                <input type="text" id="subscriberName" class="form-control" readonly placeholder="يُجلب تلقائياً">
                            </div>
                        </div>

                        {{-- بيانات الفترة --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold border-bottom pb-2">
                                    <i class="bi bi-calendar-range me-1"></i>بيانات الفترة والاستهلاك
                                </h6>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">تاريخ الفاتورة <span class="text-danger">*</span></label>
                                <input type="date" name="invoice_date" id="invoiceDate" class="form-control @error('invoice_date') is-invalid @enderror"
                                    value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required>
                                @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">فترة الاستهلاك (أيام)</label>
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
                                <label class="form-label fw-semibold">تاريخ الاستحقاق</label>
                                <input type="date" name="due_date" id="dueDate" class="form-control @error('due_date') is-invalid @enderror"
                                    value="{{ old('due_date') }}">
                                @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- التسعير والاحتساب --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold border-bottom pb-2">
                                    <i class="bi bi-calculator me-1"></i>التسعير والاحتساب
                                </h6>
                            </div>
                            {{-- مدخلات التسعير --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    سعر الكيلوواط (₪)
                                    @if($activeTariff)
                                        <small class="text-success">(نشط: {{ $activeTariff->price_per_kwh }})</small>
                                    @endif
                                </label>
                                <input type="number" name="price_per_kwh" id="pricePerKwh" class="form-control @error('price_per_kwh') is-invalid @enderror"
                                    value="{{ old('price_per_kwh', $activeTariff?->price_per_kwh ?? 0) }}"
                                    step="0.0001" min="0" required>
                                @error('price_per_kwh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">نسبة الخصم (%)</label>
                                <input type="number" name="discount_rate" id="discountRate" class="form-control @error('discount_rate') is-invalid @enderror"
                                    value="{{ old('discount_rate', 0) }}" step="0.01" min="0" max="100">
                                @error('discount_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">الحد الأدنى (₪)</label>
                                <input type="number" name="minimum_charge" id="minimumCharge" class="form-control"
                                    value="{{ old('minimum_charge', $minimumCharge) }}" step="0.01" min="0">
                            </div>
                            {{-- خطوة 1: ثمن الاستهلاك = kwh × price × (1 - discount/100) --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <span class="badge bg-secondary me-1">1</span>
                                    ثمن الاستهلاك (₪)
                                    <small class="text-muted d-block">kWh × سعر × (1-خصم)</small>
                                </label>
                                <input type="text" id="consumptionCost" class="form-control bg-light" readonly placeholder="0.00">
                            </div>
                        </div>

                        {{-- نتائج الاحتساب --}}
                        <div class="row g-3 mb-4">
                            {{-- خطوة 2: قيمة الفاتورة = max(ثمن الاستهلاك, الحد الأدنى) --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <span class="badge bg-secondary me-1">2</span>
                                    قيمة الفاتورة (₪)
                                    <small class="text-muted d-block">max(ثمن الاستهلاك, حد أدنى)</small>
                                </label>
                                <input type="number" name="invoice_amount" id="invoiceAmount" class="form-control fw-bold"
                                    value="{{ old('invoice_amount', 0) }}" step="0.01" readonly>
                            </div>
                            {{-- الرصيد السابق: موجب = مديون على المشترك, سالب = دائن لصالحه --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">الرصيد السابق (₪)
                                    <small class="text-muted d-block">موجب=مديون · سالب=دائن</small>
                                </label>
                                <input type="number" name="previous_balance" id="previousBalance" class="form-control"
                                    value="{{ old('previous_balance', 0) }}" step="0.01">
                            </div>
                            {{-- خطوة 3: المبلغ المطلوب = max(قيمة_الفاتورة + رصيد_سابق, 0) --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-primary">
                                    <span class="badge bg-primary me-1">3</span>
                                    المبلغ المطلوب (₪)
                                    <small class="text-muted d-block">قيمة + رصيد (لا يقل عن صفر)</small>
                                </label>
                                <input type="number" name="total_amount" id="totalAmount" class="form-control fw-bold fs-5 text-primary"
                                    value="{{ old('total_amount', 0) }}" step="0.01" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">ملاحظات</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <div class="alert alert-warning d-none mb-3" id="creditNote" role="alert">
                            <i class="bi bi-info-circle me-1"></i>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>حفظ كمسودة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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

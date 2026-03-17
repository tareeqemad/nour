@extends('layouts.admin')

@section('title', 'تعديل الفاتورة ' . $invoice->invoice_number)

@php $breadcrumbTitle = 'تعديل الفاتورة'; @endphp

@section('content')
<div class="general-page">
    <div class="row g-3 justify-content-center">
        <div class="col-lg-10">
            <div class="general-card">
                <div class="general-card-header">
                    <h5 class="general-title"><i class="bi bi-pencil me-2"></i>تعديل الفاتورة: {{ $invoice->invoice_number }}</h5>
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>العودة
                    </a>
                </div>

                <div class="card-body pb-4">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.invoices.update', $invoice) }}" id="invoiceForm">
                        @csrf @method('PUT')

                        {{-- بيانات المشترك (للعرض فقط) --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold border-bottom pb-2"><i class="bi bi-person me-1"></i>بيانات الاشتراك</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">رقم الاشتراك</label>
                                <input type="text" class="form-control" value="{{ $invoice->subscriber->subscription_number }}" readonly>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">اسم المشترك</label>
                                <input type="text" class="form-control" value="{{ $invoice->subscriber->subscriber_name }}" readonly>
                            </div>
                        </div>

                        {{-- بيانات الفترة --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold border-bottom pb-2"><i class="bi bi-calendar-range me-1"></i>بيانات الفترة والاستهلاك</h6>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">تاريخ الفاتورة <span class="text-danger">*</span></label>
                                <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                                    value="{{ old('invoice_date', $invoice->invoice_date?->format('Y-m-d')) }}" required>
                                @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">فترة الاستهلاك (أيام)</label>
                                <input type="number" name="consumption_period_days" id="periodDays" class="form-control"
                                    value="{{ old('consumption_period_days', $invoice->consumption_period_days) }}" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">الاستهلاك kWh</label>
                                <input type="number" name="consumption_kwh" id="consumptionKwh" class="form-control"
                                    value="{{ old('consumption_kwh', $invoice->consumption_kwh) }}" step="0.01" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">تاريخ الاستحقاق</label>
                                <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                    value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}">
                                @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- التسعير --}}
                        @php
                            $sub = $invoice->subscriber;
                            $isEmployee = $sub ? $sub->isEligibleForEmployeeDiscount() : false;
                            // الحد الأدنى من جدول الموديل المركزي
                            $ampereKey = intval($sub->ampere ?? 0);
                            $phaseKey  = $sub->phase_type ?? 1;
                            $autoMinCharge = \App\Models\MinimumChargeRule::allCached()[$ampereKey][$phaseKey] ?? null;
                            // نسبة خصم الموظفين من جدول employee_discount_rates (حسب تاريخ الفاتورة)
                            $empDiscountRate = $isEmployee
                                ? \App\Models\Invoice::getEmployeeDiscountRate($invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date) : null)
                                : 0;
                        @endphp
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold border-bottom pb-2"><i class="bi bi-calculator me-1"></i>التسعير والاحتساب</h6>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">سعر الكيلوواط (₪)</label>
                                <input type="number" name="price_per_kwh" id="pricePerKwh" class="form-control"
                                    value="{{ old('price_per_kwh', number_format((float)$invoice->price_per_kwh, 2, '.', '')) }}" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">نسبة الخصم (%)
                                    @if($isEmployee)<small class="text-success fw-normal me-1"><i class="bi bi-person-badge"></i> موظف شركة</small>@endif
                                </label>
                                @if($isEmployee)
                                    <input type="number" step="0.01" min="0" max="100" class="form-control border-success" value="{{ $empDiscountRate }}" readonly disabled>
                                    <input type="hidden" name="discount_rate" value="{{ $empDiscountRate }}">
                                    <small class="text-muted">يُطبَّق خصم {{ $empDiscountRate }}% تلقائياً (موظف شركة + منزلي + 1 فاز)</small>
                                @else
                                    <input type="number" step="0.01" min="0" max="100" name="discount_rate" id="discountRate" class="form-control"
                                        value="{{ old('discount_rate', number_format((float)$invoice->discount_rate, 2, '.', '')) }}">
                                @endif
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">الحد الأدنى (₪)
                                    @if($autoMinCharge !== null)<small class="text-info fw-normal">{{ intval($sub->ampere) }} أمبير / {{ $sub->phase_type == 2 ? '3' : '1' }} فاز</small>@endif
                                </label>
                                <input type="number" name="minimum_charge" id="minimumCharge" class="form-control {{ $autoMinCharge !== null ? 'border-info' : '' }}"
                                    value="{{ old('minimum_charge', ($invoice->minimum_charge > 0 ? $invoice->minimum_charge : $autoMinCharge)) }}" step="0.01" min="0"
                                    data-auto-min="{{ $autoMinCharge ?? '' }}">
                                @if($autoMinCharge !== null)
                                    <small class="text-muted">القيمة التلقائية حسب التعريفة: {{ $autoMinCharge }} ₪</small>
                                @endif
                            </div>
                            {{-- خطوة 1: ثمن الاستهلاك --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <span class="badge bg-secondary me-1">1</span>
                                    ثمن الاستهلاك (₪)
                                    <small class="text-muted d-block">kWh × سعر × (1-خصم)</small>
                                </label>
                                <input type="text" id="consumptionCost" class="form-control bg-light"
                                    value="{{ $invoice->consumption_cost }}" readonly>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            {{-- خطوة 2: قيمة الفاتورة --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <span class="badge bg-secondary me-1">2</span>
                                    قيمة الفاتورة (₪)
                                    <small class="text-muted d-block">max(ثمن الاستهلاك, حد أدنى)</small>
                                </label>
                                <input type="number" name="invoice_amount" id="invoiceAmount" class="form-control fw-bold"
                                    value="{{ old('invoice_amount', $invoice->invoice_amount) }}" step="0.01" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">الرصيد السابق (₪)
                                    <small class="text-muted d-block">موجب=مديون · سالب=دائن</small>
                                </label>
                                <input type="number" name="previous_balance" id="previousBalance" class="form-control"
                                    value="{{ old('previous_balance', $invoice->previous_balance) }}" step="0.01">
                            </div>
                            {{-- خطوة 3: المبلغ المطلوب --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-primary">
                                    <span class="badge bg-primary me-1">3</span>
                                    المبلغ المطلوب (₪)
                                    <small class="text-muted d-block">قيمة + رصيد (لا يقل عن صفر)</small>
                                </label>
                                <input type="number" name="total_amount" id="totalAmount" class="form-control fw-bold fs-5 text-primary"
                                    value="{{ old('total_amount', $invoice->total_amount) }}" step="0.01" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">ملاحظات</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $invoice->notes) }}</textarea>
                            </div>
                        </div>
                        <div class="alert alert-warning d-none mb-3" id="creditNote" role="alert">
                            <i class="bi bi-info-circle me-1"></i>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-outline-secondary">إلغاء</a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-save me-1"></i>حفظ التعديلات
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
    $('#consumptionKwh, #pricePerKwh, #discountRate, #minimumCharge, #previousBalance').on('input change', calcInvoice);
    // احتساب تلقائي عند تحميل الصفحة
    calcInvoice();
})();
</script>
@endpush

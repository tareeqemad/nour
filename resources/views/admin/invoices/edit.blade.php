@extends('layouts.admin')

@section('title', 'تعديل الفاتورة ' . $invoice->invoice_number)

@php $breadcrumbTitle = 'تعديل الفاتورة'; @endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form :title="'تعديل: ' . $invoice->invoice_number" icon="bi-pencil" :backRoute="route('admin.invoices.show', $invoice)" />

                <div class="card-body">
                    @if($errors->any())
                        <div style="background: var(--color-danger-bg, #FEF2F2); border: 1px solid var(--color-danger-border, #FECACA); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.85rem; color: var(--color-danger-text, #B91C1C);">
                            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.invoices.update', $invoice) }}" id="invoiceForm">
                        @csrf @method('PUT')

                        {{-- 1. بيانات المشترك --}}
                        <div class="invoice-section">
                            <div class="invoice-section-header">
                                <i class="bi bi-person"></i>
                                بيانات الاشتراك
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">رقم الاشتراك</label>
                                    <input type="text" class="form-control" value="{{ $invoice->subscriber->subscription_number }}" readonly>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">اسم المشترك</label>
                                    <input type="text" class="form-control" value="{{ $invoice->subscriber->subscriber_name }}" readonly>
                                </div>
                            </div>
                        </div>

                        {{-- 2. الفترة والاستهلاك --}}
                        <div class="invoice-section">
                            <div class="invoice-section-header">
                                <i class="bi bi-calendar-range"></i>
                                الفترة والاستهلاك
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">تاريخ الفاتورة <span class="text-danger">*</span></label>
                                    <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                                        value="{{ old('invoice_date', $invoice->invoice_date?->format('Y-m-d')) }}" required>
                                    @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">الفترة (أيام)</label>
                                    <input type="number" name="consumption_period_days" id="periodDays" class="form-control"
                                        value="{{ old('consumption_period_days', $invoice->consumption_period_days) }}" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">الاستهلاك kWh</label>
                                    <input type="number" name="consumption_kwh" id="consumptionKwh" class="form-control"
                                        value="{{ old('consumption_kwh', $invoice->consumption_kwh) }}" step="0.01" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">الاستحقاق</label>
                                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                        value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}">
                                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- 3. التسعير --}}
                        @php
                            $sub = $invoice->subscriber;
                            $isEmployee = $sub ? $sub->isEligibleForEmployeeDiscount() : false;
                            $ampereKey = intval($sub->ampere ?? 0);
                            $phaseKey  = $sub->phase_type ?? 1;
                            $editOperatorId = $sub?->generationUnits->first()?->operator_id ?? 0;
                            $autoMinCharge = \App\Models\MinimumChargeRule::cachedForOperator($editOperatorId)[$ampereKey][$phaseKey] ?? null;
                            $empDiscountRate = $isEmployee
                                ? \App\Models\Invoice::getEmployeeDiscountRate($invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date) : null)
                                : 0;
                        @endphp
                        <div class="invoice-section">
                            <div class="invoice-section-header">
                                <i class="bi bi-calculator"></i>
                                التسعير
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">سعر الكيلوواط (₪)</label>
                                    <input type="number" name="price_per_kwh" id="pricePerKwh" class="form-control"
                                        value="{{ old('price_per_kwh', number_format((float)$invoice->price_per_kwh, 2, '.', '')) }}" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        الخصم (%)
                                        @if($isEmployee)<small style="color: var(--color-success-text, #047857);"><i class="bi bi-person-badge"></i> موظف</small>@endif
                                    </label>
                                    @if($isEmployee)
                                        <input type="number" class="form-control" value="{{ $empDiscountRate }}" readonly disabled>
                                        <input type="hidden" name="discount_rate" value="{{ $empDiscountRate }}">
                                        <small class="text-muted">خصم {{ $empDiscountRate }}% تلقائي</small>
                                    @else
                                        <input type="number" step="0.01" min="0" max="100" name="discount_rate" id="discountRate" class="form-control"
                                            value="{{ old('discount_rate', number_format((float)$invoice->discount_rate, 2, '.', '')) }}">
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        الحد الأدنى (₪)
                                        @if($autoMinCharge !== null)<small style="color: var(--color-info-text, #0369A1);">{{ intval($sub->ampere) }}A / {{ $sub->phase_type == 2 ? '3' : '1' }}φ</small>@endif
                                    </label>
                                    <input type="number" name="minimum_charge" id="minimumCharge" class="form-control"
                                        value="{{ old('minimum_charge', ($invoice->minimum_charge > 0 ? $invoice->minimum_charge : $autoMinCharge)) }}" step="0.01" min="0"
                                        data-auto-min="{{ $autoMinCharge ?? '' }}">
                                    @if($autoMinCharge !== null)
                                        <small class="text-muted">تلقائي: {{ $autoMinCharge }} ₪</small>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">ثمن الاستهلاك (₪)</label>
                                    <input type="text" id="consumptionCost" class="form-control" value="{{ $invoice->consumption_cost }}" readonly>
                                    <small class="text-muted">kWh × سعر × (1-خصم)</small>
                                </div>
                            </div>
                        </div>

                        {{-- 4. ملخص الفاتورة --}}
                        <div class="invoice-section" style="background: #FAFCFF; border: 1px solid var(--color-border, #E5E7EB); border-radius: 10px; padding: 1.25rem;">
                            <div class="invoice-section-header" style="color: var(--color-primary, #24308F); font-size: 0.95rem;">
                                <i class="bi bi-receipt"></i>
                                ملخص الفاتورة
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">قيمة الفاتورة (₪)</label>
                                    <input type="number" name="invoice_amount" id="invoiceAmount" class="form-control fw-bold"
                                        value="{{ old('invoice_amount', $invoice->invoice_amount) }}" step="0.01" readonly>
                                    <small class="text-muted">max(استهلاك, حد أدنى)</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">الرصيد السابق (₪)</label>
                                    <input type="number" name="previous_balance" id="previousBalance" class="form-control"
                                        value="{{ old('previous_balance', $invoice->previous_balance) }}" step="0.01">
                                    <small class="text-muted">موجب=مديون · سالب=دائن</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold" style="color: var(--color-primary, #24308F);">المبلغ المطلوب (₪)</label>
                                    <input type="number" name="total_amount" id="totalAmount" class="form-control fw-bold" style="font-size: 1.1rem; color: var(--color-primary, #24308F); border-color: var(--color-primary, #24308F);"
                                        value="{{ old('total_amount', $invoice->total_amount) }}" step="0.01" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">ملاحظات</label>
                                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $invoice->notes) }}</textarea>
                                </div>
                            </div>
                            <div class="d-none mt-3" id="creditNote" style="background: var(--color-info-bg, #F0F9FF); border: 1px solid var(--color-info-border, #BAE6FD); border-radius: 8px; padding: 0.65rem 1rem; font-size: 0.85rem; color: var(--color-info-text, #0369A1);">
                                <i class="bi bi-info-circle me-1"></i>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid var(--color-border-soft, #EDF1F5);">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i>حفظ التعديلات
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
    function calcInvoice() {
        const kwh       = parseFloat($('#consumptionKwh').val()) || 0;
        const price     = parseFloat($('#pricePerKwh').val()) || 0;
        const discount  = parseFloat($('#discountRate').val()) || 0;
        const minCharge = parseFloat($('#minimumCharge').val()) || 0;
        const prevBal   = parseFloat($('#previousBalance').val()) || 0;

        const consumptionCost = kwh * price * (1 - discount / 100);
        const invoiceAmount = Math.max(consumptionCost, minCharge);
        const rawTotal    = invoiceAmount + prevBal;
        const totalAmount = Math.max(rawTotal, 0);

        $('#consumptionCost').val(consumptionCost.toFixed(2));
        $('#invoiceAmount').val(invoiceAmount.toFixed(2));
        $('#totalAmount').val(totalAmount.toFixed(2));

        if (rawTotal < 0) {
            $('#creditNote').html('<i class="bi bi-info-circle me-1"></i>المبلغ سالب (' + rawTotal.toFixed(2) + ' ₪) سيُرحَّل كرصيد دائن.').removeClass('d-none');
        } else {
            $('#creditNote').addClass('d-none').html('');
        }
    }
    $('#consumptionKwh, #pricePerKwh, #discountRate, #minimumCharge, #previousBalance').on('input change', calcInvoice);
    calcInvoice();
})();
</script>
@endpush

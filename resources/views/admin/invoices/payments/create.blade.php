@extends('layouts.admin')

@section('title', 'تسجيل دفعة — ' . ($invoice->invoice_number ?? 'فاتورة'))

@php $breadcrumbTitle = 'تسجيل دفعة'; @endphp

@section('content')
<div class="general-page">
    <div class="row justify-content-center g-3">

        {{-- ===== بيانات الفاتورة (دائماً ظاهرة) ===== --}}
        <div class="col-lg-4">
            <x-admin.card class="h-100">
                <x-admin.card-header-form title="بيانات الفاتورة" icon="bi-receipt" />
                <div class="card-body">

                    {{-- رقم الفاتورة والحالة --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <span class="fw-bold fs-6">{{ $invoice->invoice_number }}</span>
                        <span class="badge bg-{{ $invoice->status_badge_class }}">
                            {{ $invoice->status_name }}
                        </span>
                    </div>

                    {{-- المشترك --}}
                    <div class="mb-3">
                        <div class="text-muted small mb-1"><i class="bi bi-person me-1"></i>المشترك</div>
                        <div class="fw-semibold">{{ $invoice->subscriber->subscriber_name }}</div>
                        <div class="text-muted small">{{ $invoice->subscriber->subscription_number }}</div>
                    </div>

                    {{-- تواريخ --}}
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="text-muted small mb-1">تاريخ الفاتورة</div>
                            <div class="small fw-semibold">{{ $invoice->invoice_date?->format('Y-m-d') }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small mb-1">تاريخ الاستحقاق</div>
                            <div class="small fw-semibold {{ $invoice->due_date?->isPast() ? 'text-danger' : '' }}">
                                {{ $invoice->due_date?->format('Y-m-d') ?? '—' }}
                            </div>
                        </div>
                    </div>

                    {{-- الأرقام المالية --}}
                    <div class="border rounded overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light">
                            <span class="text-muted small">إجمالي الفاتورة</span>
                            <span class="fw-bold">{{ number_format($invoice->total_amount, 2) }} ₪</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <span class="text-muted small">إجمالي المسدد</span>
                            <span class="fw-bold text-success">{{ number_format($invoice->paidAmount(), 2) }} ₪</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-danger bg-opacity-10">
                            <span class="text-danger small fw-semibold">المتبقي للسداد</span>
                            <span class="fw-bold text-danger fs-5">{{ number_format($remainingAmount, 2) }} ₪</span>
                        </div>
                    </div>

                    {{-- شريط التقدم --}}
                    @php
                        $paidPct = $invoice->total_amount > 0
                            ? min(round(($invoice->paidAmount() / $invoice->total_amount) * 100), 100)
                            : 0;
                    @endphp
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>نسبة السداد</span>
                            <span>{{ $paidPct }}%</span>
                        </div>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar bg-success" style="width:{{ $paidPct }}%"></div>
                        </div>
                    </div>

                </div>
            </x-admin.card>
        </div>

        {{-- ===== نموذج الدفعة ===== --}}
        <div class="col-lg-6">
            <x-admin.card>
                <x-admin.card-header-form title="بيانات الدفعة" icon="bi-cash-coin" :backRoute="route('admin.invoices.show', $invoice)" />

                <div class="card-body">

                    {{-- رسائل الأخطاء --}}
                    @if($errors->any())
                        <div class="alert alert-danger py-2 mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li class="small">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.invoices.payments.store', $invoice) }}"
                          method="POST" id="paymentForm" autocomplete="off">
                        @csrf

                        <div class="row g-3">

                            {{-- تاريخ السداد --}}
                            <div class="col-md-6">
                                <label for="payment_date" class="form-label fw-semibold">
                                    تاريخ السداد <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       class="form-control @error('payment_date') is-invalid @enderror"
                                       id="payment_date" name="payment_date"
                                       value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                                       max="{{ now()->format('Y-m-d') }}" required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- طريقة الدفع --}}
                            <div class="col-md-6">
                                <label for="payment_method" class="form-label fw-semibold">
                                    طريقة الدفع <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('payment_method') is-invalid @enderror"
                                        id="payment_method" name="payment_method" required>
                                    @foreach($methodLabels as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('payment_method', 'bank_transfer') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- المبلغ المسدد --}}
                            <div class="col-12">
                                <label for="amount_paid" class="form-label fw-semibold">
                                    المبلغ المسدد <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0.01"
                                           class="form-control form-control-lg @error('amount_paid') is-invalid @enderror"
                                           id="amount_paid" name="amount_paid"
                                           value="{{ old('amount_paid', number_format($remainingAmount, 2, '.', '')) }}"
                                           required>
                                    <span class="input-group-text">₪</span>
                                    @error('amount_paid')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- ملخص مباشر --}}
                                <div id="paymentSummary" class="mt-2 p-2 rounded border small d-none">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">المتبقي بعد الدفعة:</span>
                                        <span id="afterPayment" class="fw-semibold"></span>
                                    </div>
                                </div>
                                <div id="overpaymentHint" class="form-text text-info d-none mt-1">
                                    <i class="bi bi-info-circle me-1"></i>
                                    المبلغ يتجاوز المتبقي — القيمة الزائدة
                                    (<span id="overpaymentAmt"></span> ₪)
                                    ستُسجَّل كرصيد دائن للفاتورة القادمة.
                                </div>
                            </div>

                            {{-- رقم الإيصال --}}
                            <div class="col-md-6">
                                <label for="receipt_number" class="form-label fw-semibold">رقم الإيصال</label>
                                <input type="text"
                                       class="form-control @error('receipt_number') is-invalid @enderror"
                                       id="receipt_number" name="receipt_number"
                                       value="{{ old('receipt_number') }}" maxlength="60"
                                       placeholder="اختياري">
                                @error('receipt_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- ملاحظات --}}
                            <div class="col-md-6">
                                <label for="notes" class="form-label fw-semibold">ملاحظات</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="2"
                                          maxlength="500">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <hr>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('admin.invoices.show', $invoice) }}"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-x me-1"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-check-circle me-1"></i>تسجيل الدفعة
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
    const remaining    = {{ $remainingAmount }};
    const amountInput  = document.getElementById('amount_paid');
    const summary      = document.getElementById('paymentSummary');
    const afterPayment = document.getElementById('afterPayment');
    const overpayHint  = document.getElementById('overpaymentHint');
    const overpayAmt   = document.getElementById('overpaymentAmt');

    function update() {
        const val = parseFloat(amountInput.value) || 0;
        if (val <= 0) { summary.classList.add('d-none'); return; }

        summary.classList.remove('d-none');
        const after = remaining - val;

        if (after > 0.001) {
            afterPayment.textContent = after.toFixed(2) + ' ₪';
            afterPayment.className   = 'fw-semibold text-warning';
            overpayHint.classList.add('d-none');
        } else if (Math.abs(after) < 0.001) {
            afterPayment.textContent = 'مسددة بالكامل ✓';
            afterPayment.className   = 'fw-semibold text-success';
            overpayHint.classList.add('d-none');
        } else {
            afterPayment.textContent = 'مسددة بالكامل ✓';
            afterPayment.className   = 'fw-semibold text-success';
            overpayAmt.textContent   = Math.abs(after).toFixed(2);
            overpayHint.classList.remove('d-none');
        }
    }

    amountInput.addEventListener('input', update);
    update();
})();
</script>
@endpush

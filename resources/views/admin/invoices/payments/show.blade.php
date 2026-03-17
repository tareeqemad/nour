@extends('layouts.admin')

@section('title', 'تفاصيل الدفعة')

@php $breadcrumbTitle = 'تفاصيل الدفعة'; @endphp

@section('content')
<div class="general-page">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="general-card">
                <div class="general-card-header">
                    <h5 class="general-title">
                        <i class="bi bi-receipt me-2"></i>تفاصيل الدفعة
                    </h5>
                    <div class="d-flex gap-2">
                        @can('delete', $payment)
                            <button type="button" class="btn btn-outline-danger btn-sm"
                                    data-action="{{ route('admin.invoices.payments.destroy', [$invoice, $payment]) }}"
                                    data-csrf="{{ csrf_token() }}"
                                    onclick="swalDeletePayment(this)">
                                <i class="bi bi-trash me-1"></i>حذف
                            </button>
                        @endcan
                        <a href="{{ route('admin.invoices.payments.index', $invoice) }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>سجل الدفعات
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <dl class="row g-2">
                        <dt class="col-sm-4 text-muted">رقم الفاتورة</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('admin.invoices.show', $invoice) }}">
                                {{ $invoice->invoice_number ?? 'مسودة' }}
                            </a>
                        </dd>

                        <dt class="col-sm-4 text-muted">المشترك</dt>
                        <dd class="col-sm-8">
                            {{ $invoice->subscriber->subscriber_name ?? '—' }}
                            <span class="text-muted small">({{ $invoice->subscriber->subscription_number ?? '' }})</span>
                        </dd>

                        <dt class="col-sm-4 text-muted">تاريخ السداد</dt>
                        <dd class="col-sm-8">{{ $payment->payment_date->format('Y-m-d') }}</dd>

                        <dt class="col-sm-4 text-muted">المبلغ المسدد</dt>
                        <dd class="col-sm-8 fw-bold text-success">
                            {{ number_format($payment->amount_paid, 2) }}
                        </dd>

                        @if($payment->overpayment > 0)
                        <dt class="col-sm-4 text-muted">زيادة (رصيد دائن)</dt>
                        <dd class="col-sm-8 text-info">
                            {{ number_format($payment->overpayment, 2) }}
                            <small class="text-muted">— محوّلة لفاتورة قادمة</small>
                        </dd>
                        @endif

                        <dt class="col-sm-4 text-muted">طريقة الدفع</dt>
                        <dd class="col-sm-8">{{ $payment->method_label }}</dd>

                        <dt class="col-sm-4 text-muted">رقم الإيصال</dt>
                        <dd class="col-sm-8">{{ $payment->receipt_number ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">ملاحظات</dt>
                        <dd class="col-sm-8">{{ $payment->notes ?: '—' }}</dd>

                        <dt class="col-sm-4 text-muted">سجّله</dt>
                        <dd class="col-sm-8">
                            {{ $payment->creator?->name ?? '—' }}
                            <span class="text-muted small">
                                {{ $payment->created_at?->format('Y-m-d H:i') }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function swalDeletePayment(btn) {
    Swal.fire({
        title: 'حذف الدفعة',
        text: 'هل أنت متأكد من حذف هذه الدفعة؟ سيتم إعادة حالة الفاتورة تلقائياً.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء',
    }).then(result => {
        if (!result.isConfirmed) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = btn.dataset.action;
        const csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = btn.dataset.csrf;
        const method = document.createElement('input');
        method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE';
        form.appendChild(csrf);
        form.appendChild(method);
        document.body.appendChild(form);
        form.submit();
    });
}
</script>
@endpush

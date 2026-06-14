@extends('layouts.admin')

@section('title', 'تفاصيل الدفعة')

@php $breadcrumbTitle = 'تفاصيل الدفعة'; @endphp

@section('content')
<div class="general-page">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <x-admin.card>
                <x-admin.card-header-form title="تفاصيل الدفعة" icon="bi-receipt" :backRoute="route('admin.invoices.payments.index', $invoice)" backLabel="سجل الدفعات">
                    @can('delete', $payment)
                        <button type="button" class="btn btn-outline-danger btn-sm"
                                data-action="{{ route('admin.invoices.payments.destroy', [$invoice, $payment]) }}"
                                data-csrf="{{ csrf_token() }}"
                                onclick="swalDeletePayment(this)">
                            <i class="bi bi-trash me-1"></i>حذف
                        </button>
                    @endcan
                </x-admin.card-header-form>

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
            </x-admin.card>

        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'دفعات الفاتورة: ' . ($invoice->invoice_number ?? 'مسودة'))

@php $breadcrumbTitle = 'سجل الدفعات'; @endphp

@section('content')
<div class="general-page">
    <div class="row g-3 justify-content-center">
        <div class="col-lg-10">

            {{-- رأس الصفحة --}}
            <x-admin.card>
                <x-admin.card-header title="سجل الدفعات" icon="bi-cash-stack">
                    <x-slot:actions>
                        @can('createForInvoice', [App\Models\Payment::class, $invoice])
                            <a href="{{ route('admin.invoices.payments.create', $invoice) }}"
                               class="btn btn-success btn-sm">
                                <i class="bi bi-plus-circle me-1"></i>تسجيل دفعة
                            </a>
                        @endcan
                        <a href="{{ route('admin.invoices.show', $invoice) }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>الفاتورة
                        </a>
                    </x-slot:actions>
                </x-admin.card-header>

                {{-- ملخص الفاتورة --}}
                <div class="card-body border-bottom pb-3 mb-3">
                    <div class="row text-center g-2">
                        <div class="col-sm-3">
                            <div class="border rounded p-2">
                                <div class="text-muted small">المبلغ الإجمالي</div>
                                <div class="fw-bold">{{ number_format($invoice->total_amount, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="border rounded p-2">
                                <div class="text-muted small">إجمالي المسدد</div>
                                <div class="fw-bold text-success">{{ number_format($invoice->paidAmount(), 2) }}</div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="border rounded p-2">
                                <div class="text-muted small">المتبقي</div>
                                <div class="fw-bold text-danger">{{ number_format($invoice->remainingAmount(), 2) }}</div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="border rounded p-2">
                                <div class="text-muted small">الحالة</div>
                                <div>
                                    <span class="badge bg-{{ $invoice->status_badge_class }}">
                                        {{ $invoice->status_name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if($payments->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            لا توجد دفعات مسجلة بعد
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>تاريخ السداد</th>
                                        <th>المبلغ المسدد</th>
                                        <th>طريقة الدفع</th>
                                        <th>رقم الإيصال</th>
                                        <th>سجّله</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                        <td class="fw-bold text-success">
                                            {{ number_format($payment->amount_paid, 2) }}
                                            @if($payment->overpayment > 0)
                                                <br><small class="text-info">
                                                    <i class="bi bi-info-circle"></i>
                                                    زيادة: {{ number_format($payment->overpayment, 2) }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>{{ $payment->method_label }}</td>
                                        <td>{{ $payment->receipt_number ?? '—' }}</td>
                                        <td>{{ $payment->creator?->name ?? '—' }}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('admin.invoices.payments.show', [$invoice, $payment]) }}"
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('delete', $payment)
                                                    <button type="button"
                                                            class="btn btn-outline-danger btn-sm"
                                                            data-action="{{ route('admin.invoices.payments.destroy', [$invoice, $payment]) }}"
                                                            data-csrf="{{ csrf_token() }}"
                                                            onclick="swalDeletePayment(this)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $payments->links() }}
                    @endif
                </div>
            </x-admin.card>

        </div>
    </div>
</div>
@endsection

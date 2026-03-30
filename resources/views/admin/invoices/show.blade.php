@extends('layouts.admin')

@section('title', 'فاتورة: ' . $invoice->invoice_number)

@php $breadcrumbTitle = 'عرض الفاتورة'; @endphp

@section('content')
<div class="general-page">
    <div class="row g-3 justify-content-center">
        <div class="col-lg-10">

            {{-- رأس الصفحة --}}
            <x-admin.card>
                <x-admin.card-header :title="$invoice->invoice_number" icon="bi-receipt">
                    <x-slot:actions>
                        <a href="{{ route('admin.invoices.print', $invoice) }}" class="btn btn-info btn-sm" target="_blank">
                            <i class="bi bi-printer me-1"></i>طباعة
                        </a>
                        <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-danger btn-sm">
                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                        </a>
                        @can('update', $invoice)
                            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil me-1"></i>تعديل
                            </a>
                        @endcan
                        @can('issue', $invoice)
                            <button type="button" class="btn btn-success btn-sm"
                                    data-action="{{ route('admin.invoices.issue', $invoice) }}"
                                    data-csrf="{{ csrf_token() }}"
                                    onclick="swalIssue(this)">
                                <i class="bi bi-send me-1"></i>إصدار
                            </button>
                        @endcan
                        @can('cancel', $invoice)
                            <button type="button" class="btn btn-danger btn-sm"
                                    data-action="{{ route('admin.invoices.cancel', $invoice) }}"
                                    data-csrf="{{ csrf_token() }}"
                                    onclick="swalCancel(this)">
                                <i class="bi bi-x-circle me-1"></i>إلغاء الفاتورة
                            </button>
                        @endcan
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>القائمة
                        </a>
                    </x-slot:actions>
                </x-admin.card-header>

                <div class="card-body pb-4">
                    <div class="row g-4">

                        {{-- بيانات الاشتراك --}}
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="fw-bold mb-3 pb-2 border-bottom text-primary">
                                    <i class="bi bi-person me-1"></i>بيانات الاشتراك
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th class="text-muted" width="40%">رقم الاشتراك</th>
                                        <td>
                                            @if($invoice->subscriber)
                                                <a href="{{ route('admin.subscribers.show', $invoice->subscriber) }}" class="fw-semibold">
                                                    {{ $invoice->subscriber->subscription_number }}
                                                </a>
                                            @else — @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">اسم المشترك</th>
                                        <td>{{ $invoice->subscriber?->subscriber_name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">نوع المشترك</th>
                                        <td>
                                            @if($invoice->subscriber?->is_employee_subscription)
                                                <span class="badge bg-info">موظف شركة</span>
                                                @if(!$invoice->subscriber->isEligibleForEmployeeDiscount())
                                                    <small class="text-muted d-block mt-1">غير مستحق للخصم (يشترط منزلي + 1 فاز)</small>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">مشترك عادي</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">المشغل</th>
                                        <td>{{ $invoice->subscriber?->generationUnits->first()?->operator?->name ?? '—' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        {{-- بيانات الفاتورة --}}
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="fw-bold mb-3 pb-2 border-bottom text-primary">
                                    <i class="bi bi-info-circle me-1"></i>بيانات الفاتورة
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th class="text-muted" width="40%">رقم الفاتورة</th>
                                        <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">تاريخ الفاتورة</th>
                                        <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">تاريخ الاستحقاق</th>
                                        <td>
                                            @if($invoice->due_date)
                                                <span class="{{ $invoice->due_date->isPast() && $invoice->invoice_status < 3 ? 'text-danger fw-bold' : '' }}">
                                                    {{ $invoice->due_date->format('Y-m-d') }}
                                                </span>
                                            @else — @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">القراءة المرتبطة</th>
                                        <td>
                                            @if($invoice->meterReading)
                                                <a href="{{ route('admin.meter-readings.show', $invoice->meterReading) }}">
                                                    {{ $invoice->meterReading->reading_number }}
                                                </a>
                                            @else — @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">الحالة</th>
                                        <td><span class="badge bg-{{ $invoice->status_badge_class }}">{{ $invoice->status_name }}</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        {{-- تفاصيل الاستهلاك والاحتساب --}}
                        @php
                            $showSub = $invoice->subscriber;
                            $showIsEmployee = $showSub ? $showSub->isEligibleForEmployeeDiscount() : false;
                            $showAmpere = intval($showSub?->ampere ?? 0);
                            $showPhase  = $showSub?->phase_type ?? 1;
                            $showPhaseLabel = $showPhase == 2 ? '3' : '1';
                            $showOperatorId = $showSub?->generationUnits->first()?->operator_id ?? 0;
                            $showAutoMin = \App\Models\MinimumChargeRule::cachedForOperator($showOperatorId)[$showAmpere][$showPhase] ?? null;
                            // للفواتير المسودة: تطبيق القواعد تلقائياً عبر الدالة المركزية
                            $isDraft = $invoice->invoice_status === \App\Models\Invoice::STATUS_DRAFT;
                            if ($isDraft && $showSub) {
                                $showInvDate = $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date) : null;
                                [$eDiscount, $eMinCharge] = \App\Models\Invoice::applySubscriberRules(
                                    $showSub, (float)$invoice->discount_rate, (float)$invoice->minimum_charge, $showInvDate
                                );
                                $eCalc = \App\Models\Invoice::calculateAmounts(
                                    (float)$invoice->consumption_kwh, (float)$invoice->price_per_kwh,
                                    $eDiscount, $eMinCharge, (float)$invoice->previous_balance
                                );
                                $eCost = $eCalc['consumption_cost'];
                                $eInvAmt = $eCalc['invoice_amount'];
                                $eTotal = $eCalc['total_amount'];
                            } else {
                                $eDiscount  = (float)$invoice->discount_rate;
                                $eMinCharge = (float)$invoice->minimum_charge;
                                $eCost      = $invoice->consumption_cost;
                                $eInvAmt    = (float)$invoice->invoice_amount;
                                $eTotal     = (float)$invoice->total_amount;
                            }
                            $ePrevBal = (float)$invoice->previous_balance;
                        @endphp
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <h6 class="fw-bold mb-3 pb-2 border-bottom text-primary">
                                    <i class="bi bi-calculator me-1"></i>تفاصيل الاحتساب
                                </h6>
                                <div class="row g-3 text-center">
                                    <div class="col-md-2 col-6">
                                        <div class="bg-light rounded p-3">
                                            <div class="fs-5 fw-bold">{{ number_format($invoice->consumption_kwh, 2) }}</div>
                                            <div class="text-muted small">الاستهلاك kWh</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <div class="bg-light rounded p-3">
                                            <div class="fs-5 fw-bold">{{ $invoice->consumption_period_days }}</div>
                                            <div class="text-muted small">فترة الاستهلاك (يوم)</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <div class="bg-light rounded p-3">
                                            <div class="fs-5 fw-bold">{{ number_format($invoice->price_per_kwh, 2) }} ₪</div>
                                            <div class="text-muted small">سعر الكيلوواط</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <div class="bg-light rounded p-3">
                                            <div class="fs-5 fw-bold text-success">
                                                {{ number_format($eDiscount, 0) }}%
                                                @if($showIsEmployee)
                                                    <i class="bi bi-person-badge text-info" title="موظف شركة"></i>
                                                @endif
                                            </div>
                                            <div class="text-muted small">نسبة الخصم</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <div class="bg-light rounded p-3">
                                            <div class="fs-5 fw-bold">{{ number_format($eMinCharge, 2) }} ₪</div>
                                            <div class="text-muted small">الحد الأدنى
                                                @if($showAmpere && $showAutoMin !== null)
                                                    <span class="d-block" style="font-size:0.75rem">{{ $showAmpere }} أمبير / {{ $showPhaseLabel }} فاز</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <div class="bg-light rounded p-3">
                                            <div class="fs-5 fw-bold text-info">{{ number_format($eCost, 2) }} ₪</div>
                                            <div class="text-muted small">ثمن الاستهلاك</div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                {{-- تفصيل الاحتساب (5 خطوات) --}}
                                <div class="row justify-content-end g-2">
                                    <div class="col-md-5">
                                        <table class="table table-sm mb-0">
                                            <tr>
                                                <th class="text-muted">
                                                    <span class="badge bg-secondary me-1">1</span>
                                                    ثمن الاستهلاك
                                                    <small class="d-block text-muted fw-normal">
                                                        الاستهلاك × سعر الكيلوواط{{ $eDiscount > 0 ? ' × (1 − ' . number_format($eDiscount, 0) . '%)' : '' }}
                                                        @if($showIsEmployee && $eDiscount > 0)
                                                            &mdash; <span class="text-success">خصم موظف شركة</span>
                                                        @endif
                                                    </small>
                                                </th>
                                                <td class="text-end fw-semibold">{{ number_format($eCost, 2) }} ₪</td>
                                            </tr>
                                            @if($eMinCharge > 0)
                                            <tr>
                                                <th class="text-muted">
                                                    <span class="badge bg-secondary me-1">2</span>
                                                    الحد الأدنى للفاتورة
                                                    @if($showAmpere && $showAutoMin !== null)
                                                        <small class="d-block text-muted fw-normal">حسب اشتراك {{ $showAmpere }} أمبير / {{ $showPhaseLabel }} فاز</small>
                                                    @endif
                                                </th>
                                                <td class="text-end fw-semibold">{{ number_format($eMinCharge, 2) }} ₪</td>
                                            </tr>
                                            @endif
                                            <tr style="background:#eff6ff">
                                                <th class="text-primary">
                                                    <span class="badge bg-primary me-1">3</span>
                                                    قيمة الفاتورة
                                                    @if($eMinCharge > 0)
                                                        <small class="d-block text-muted fw-normal">القيمة الأعلى بين ثمن الاستهلاك والحد الأدنى</small>
                                                    @endif
                                                </th>
                                                <td class="text-end fw-semibold text-primary">{{ number_format($eInvAmt, 2) }} ₪</td>
                                            </tr>
                                            <tr class="{{ $ePrevBal > 0 ? 'table-danger' : ($ePrevBal < 0 ? 'table-success' : '') }}">
                                                <th class="text-muted">
                                                    <span class="badge bg-secondary me-1">4</span>
                                                    الرصيد السابق
                                                    @if($ePrevBal > 0)
                                                        <small class="d-block text-muted fw-normal">مديون على المشترك — يُضاف</small>
                                                    @elseif($ePrevBal < 0)
                                                        <small class="d-block text-muted fw-normal">دائن لصالح المشترك — يُخصم</small>
                                                    @else
                                                        <small class="d-block text-muted fw-normal">لا يوجد رصيد سابق</small>
                                                    @endif
                                                </th>
                                                <td class="text-end fw-semibold {{ $ePrevBal > 0 ? 'text-danger' : ($ePrevBal < 0 ? 'text-success' : '') }}">
                                                    {{ $ePrevBal > 0 ? '+' : '' }}{{ number_format($ePrevBal, 2) }} ₪
                                                </td>
                                            </tr>
                                            <tr class="table-primary">
                                                <th>
                                                    <span class="badge bg-primary me-1">5</span>
                                                    المبلغ المطلوب
                                                    <small class="d-block fw-normal">قيمة الفاتورة + الرصيد السابق</small>
                                                </th>
                                                <td class="text-end fw-bold fs-5">{{ number_format($eTotal, 2) }} ₪</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ملاحظات --}}
                        @if($invoice->notes)
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <h6 class="fw-bold mb-2"><i class="bi bi-chat-text me-1"></i>ملاحظات</h6>
                                <p class="mb-0">{{ $invoice->notes }}</p>
                            </div>
                        </div>
                        @endif

                        {{-- بيانات التدقيق --}}
                        <div class="col-12">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-1"></i>سجل الإجراءات</h6>
                                <div class="row g-3 text-muted small">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-person-plus text-secondary mt-1"></i>
                                            <div>
                                                <div class="fw-semibold text-dark">أُنشئت بواسطة</div>
                                                <div>{{ $invoice->creator?->name ?? '—' }}</div>
                                                <div>{{ $invoice->created_at?->format('Y-m-d H:i') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    @if($invoice->issued_by)
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-send-check text-success mt-1"></i>
                                            <div>
                                                <div class="fw-semibold text-dark">اعتمدت وأُصدرت بواسطة</div>
                                                <div>{{ $invoice->issuer?->name ?? '—' }}</div>
                                                <div>{{ $invoice->issued_at?->format('Y-m-d H:i') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @if($invoice->last_updated_by && $invoice->last_updated_by !== $invoice->issued_by)
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-pencil-square text-warning mt-1"></i>
                                            <div>
                                                <div class="fw-semibold text-dark">آخر تعديل بواسطة</div>
                                                <div>{{ $invoice->updater?->name ?? '—' }}</div>
                                                <div>{{ $invoice->updated_at?->format('Y-m-d H:i') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>{{-- end row --}}
                </div>
            </x-admin.card>

            {{-- قسم الدفعات --}}
            @if(!$invoice->isEditable() && $invoice->invoice_status !== \App\Models\Invoice::STATUS_CANCELLED)
            <x-admin.card class="mt-3">
                <x-admin.card-header title="الدفعات" icon="bi-cash-stack">
                    <x-slot:actions>
                        @php $payments = $invoice->payments()->with('creator')->latest('payment_date')->get(); @endphp
                        @can('createForInvoice', [App\Models\Payment::class, $invoice])
                            <a href="{{ route('admin.invoices.payments.create', $invoice) }}"
                               class="btn btn-success btn-sm">
                                <i class="bi bi-plus-circle me-1"></i>تسجيل دفعة
                            </a>
                        @endcan
                        @if($payments->isNotEmpty())
                            <a href="{{ route('admin.invoices.payments.index', $invoice) }}"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-list-ul me-1"></i>كل الدفعات
                            </a>
                        @endif
                    </x-slot:actions>
                </x-admin.card-header>

                <div class="card-body">
                    {{-- شريط السداد --}}
                    @php
                        $paidAmt   = $invoice->paidAmount();
                        $totalAmt  = (float)$invoice->total_amount;
                        $paidPct   = $totalAmt > 0 ? min(round(($paidAmt / $totalAmt) * 100), 100) : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>مسدد: {{ number_format($paidAmt, 2) }}</span>
                            <span>متبقي: {{ number_format($invoice->remainingAmount(), 2) }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $paidPct }}%"
                                 title="{{ $paidPct }}%"></div>
                        </div>
                    </div>

                    @if($payments->isEmpty())
                        <p class="text-muted text-center py-2 mb-0">
                            <i class="bi bi-inbox me-1"></i>لا توجد دفعات بعد
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>المبلغ</th>
                                        <th>الطريقة</th>
                                        <th>الإيصال</th>
                                        <th>سجّله</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $p)
                                    <tr>
                                        <td>{{ $p->payment_date->format('Y-m-d') }}</td>
                                        <td class="text-success fw-semibold">{{ number_format($p->amount_paid, 2) }}</td>
                                        <td>{{ $p->method_label }}</td>
                                        <td>{{ $p->receipt_number ?? '—' }}</td>
                                        <td>{{ $p->creator?->name ?? '—' }}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('admin.invoices.payments.show', [$invoice, $p]) }}"
                                                   class="btn btn-outline-secondary btn-xs py-0 px-2">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.invoices.payments.receipt', [$invoice, $p]) }}"
                                                   class="btn btn-outline-success btn-xs py-0 px-2" title="طباعة إيصال" target="_blank">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                @can('delete', $p)
                                                    <button type="button"
                                                            class="btn btn-outline-danger btn-xs py-0 px-2"
                                                            data-action="{{ route('admin.invoices.payments.destroy', [$invoice, $p]) }}"
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
                    @endif
                </div>
            </x-admin.card>
            @endif

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

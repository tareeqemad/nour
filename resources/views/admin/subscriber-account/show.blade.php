@extends('layouts.admin')

@section('title', 'حساب المشترك — ' . $subscriber->subscriber_name)

@php
    $breadcrumbTitle = 'حساب المشترك';
    $breadcrumbParent = 'حساب المشترك';
    $breadcrumbParentUrl = route('admin.subscriber-account.index');
@endphp

@push('styles')
<style>
    .stat-card { border-radius: .75rem; padding: 1.25rem 1.5rem; }
    .stat-card .stat-icon { width: 48px; height: 48px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-size:1.4rem; }
    .stat-card .stat-value { font-size: 1.4rem; font-weight: 700; line-height: 1.2; }
    .stat-card .stat-label { font-size: .8rem; color: #6c757d; margin-top: 2px; }
    .balance-debit  { background: #fff3cd; border: 1px solid #ffc107; }
    .balance-credit { background: #d1e7dd; border: 1px solid #198754; }
    .balance-zero   { background: #f8f9fa; border: 1px solid #dee2e6; }
    .account-section-title {
        font-size: .75rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: #6c757d; margin-bottom: .75rem;
        padding-bottom: .4rem; border-bottom: 2px solid #e9ecef;
    }
    .subscriber-info-item { display: flex; gap: .5rem; align-items: baseline; margin-bottom: .4rem; }
    .subscriber-info-item .label { min-width: 130px; color: #6c757d; font-size:.85rem; }
    .subscriber-info-item .value { font-weight: 600; font-size:.9rem; }
    .nav-tabs .nav-link.active { color: #0d6efd; border-bottom-color: #0d6efd; }
</style>
@endpush

@section('content')
<div class="general-page">

    {{-- ===== شريط الإجراءات علوي ===== --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <a href="{{ route('admin.subscriber-account.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right me-1"></i>
                رجوع للبحث
            </a>
        </div>
        <div class="d-flex gap-2">
            @can('view', $subscriber)
                <a href="{{ route('admin.subscribers.show', $subscriber) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-person-badge me-1"></i>
                    بيانات المشترك
                </a>
            @endcan
            <a href="{{ route('admin.invoices.index', ['subscriber_id' => $subscriber->id]) }}" class="btn btn-sm btn-outline-info">
                <i class="bi bi-receipt me-1"></i>
                الفواتير
            </a>
        </div>
    </div>

    <div class="row g-3">

        {{-- ===== عمود يسار: بيانات المشترك ===== --}}
        <div class="col-lg-3 col-md-4">
            <x-admin.card class="h-100">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="mx-auto mb-2 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                             style="width:64px;height:64px;">
                            <i class="bi bi-person-fill text-primary" style="font-size:2rem;"></i>
                        </div>
                        <h6 class="fw-bold mb-0">{{ $subscriber->subscriber_name }}</h6>
                        <small class="text-muted">{{ $subscriber->subscription_number }}</small>

                        @php
                            $statusClass = match($subscriber->subscription_status) {
                                1 => 'success', 2 => 'warning', 3 => 'danger', default => 'secondary'
                            };
                        @endphp
                        <div class="mt-1">
                            <span class="badge bg-{{ $statusClass }}">{{ $subscriber->subscription_status_name }}</span>
                        </div>
                    </div>

                    <div class="account-section-title">بيانات الاشتراك</div>

                    <div class="subscriber-info-item">
                        <span class="label"><i class="bi bi-phone me-1"></i>الجوال</span>
                        <span class="value">{{ $subscriber->phone ?? '—' }}</span>
                    </div>
                    <div class="subscriber-info-item">
                        <span class="label"><i class="bi bi-speedometer2 me-1"></i>رقم العداد</span>
                        <span class="value">{{ $subscriber->meter_number ?? '—' }}</span>
                    </div>
                    <div class="subscriber-info-item">
                        <span class="label"><i class="bi bi-tags me-1"></i>التصنيف</span>
                        <span class="value">{{ $subscriber->subscription_category_name }}</span>
                    </div>
                    <div class="subscriber-info-item">
                        <span class="label"><i class="bi bi-lightning-charge me-1"></i>نوع الخدمة</span>
                        <span class="value">{{ $subscriber->service_type_name }}</span>
                    </div>
                    <div class="subscriber-info-item">
                        <span class="label"><i class="bi bi-diagram-3 me-1"></i>الفاز</span>
                        <span class="value">{{ $subscriber->phase_type_name }}</span>
                    </div>
                    <div class="subscriber-info-item">
                        <span class="label"><i class="bi bi-calendar me-1"></i>تاريخ الاشتراك</span>
                        <span class="value">{{ $subscriber->subscription_date ? $subscriber->subscription_date->format('Y-m-d') : '—' }}</span>
                    </div>

                    @if($subscriber->address)
                        <div class="subscriber-info-item">
                            <span class="label"><i class="bi bi-geo-alt me-1"></i>العنوان</span>
                            <span class="value">{{ $subscriber->address }}</span>
                        </div>
                    @endif

                    @if($subscriber->governorate_display_name !== '-')
                        <div class="subscriber-info-item">
                            <span class="label"><i class="bi bi-map me-1"></i>المحافظة</span>
                            <span class="value">{{ $subscriber->governorate_display_name }}</span>
                        </div>
                    @endif

                    <div class="account-section-title mt-3">آخر نشاط</div>

                    <div class="subscriber-info-item">
                        <span class="label"><i class="bi bi-receipt me-1"></i>آخر فاتورة</span>
                        <span class="value">
                            @if($lastInvoice)
                                {{ $lastInvoice->invoice_date->format('Y-m-d') }}
                            @else
                                <span class="text-muted">لا توجد</span>
                            @endif
                        </span>
                    </div>
                    <div class="subscriber-info-item">
                        <span class="label"><i class="bi bi-cash-coin me-1"></i>آخر دفعة</span>
                        <span class="value">
                            @if($lastPayment)
                                {{ $lastPayment->payment_date->format('Y-m-d') }}
                            @else
                                <span class="text-muted">لا توجد</span>
                            @endif
                        </span>
                    </div>
                </div>
            </x-admin.card>
        </div>

        {{-- ===== عمود يمين: الحساب والكشف ===== --}}
        <div class="col-lg-9 col-md-8">

            {{-- ===== بطاقات الملخص المالي ===== --}}
            <div class="row g-3 mb-3">

                {{-- الرصيد الحالي --}}
                @php
                    $balanceClass = $currentBalance > 0 ? 'balance-debit' : ($currentBalance < 0 ? 'balance-credit' : 'balance-zero');
                    $balanceIcon  = $currentBalance > 0 ? 'bi-arrow-up-circle-fill text-danger' : ($currentBalance < 0 ? 'bi-arrow-down-circle-fill text-success' : 'bi-check-circle-fill text-secondary');
                    $balanceLabel = $currentBalance > 0 ? 'مدين (على المشترك)' : ($currentBalance < 0 ? 'دائن (لصالح المشترك)' : 'متوازن');
                    $balanceColor = $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-success' : 'text-secondary');
                @endphp
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card {{ $balanceClass }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-white shadow-sm">
                                <i class="bi {{ $balanceIcon }}"></i>
                            </div>
                            <div>
                                <div class="stat-value {{ $balanceColor }}">
                                    {{ number_format(abs($currentBalance), 2) }}
                                </div>
                                <div class="stat-label fw-semibold">{{ $balanceLabel }}</div>
                            </div>
                        </div>
                        <div class="mt-2 small text-muted">الرصيد الحالي</div>
                    </div>
                </div>

                {{-- إجمالي قيمة الفواتير --}}
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card bg-light border">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-primary bg-opacity-10">
                                <i class="bi bi-receipt text-primary"></i>
                            </div>
                            <div>
                                <div class="stat-value text-primary">{{ number_format($totalInvoiced, 2) }}</div>
                                <div class="stat-label">إجمالي الفواتير</div>
                            </div>
                        </div>
                        <div class="mt-2 small text-muted">جميع الفواتير الصادرة</div>
                    </div>
                </div>

                {{-- إجمالي المسدد --}}
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card bg-light border">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-success bg-opacity-10">
                                <i class="bi bi-cash-coin text-success"></i>
                            </div>
                            <div>
                                <div class="stat-value text-success">{{ number_format($totalPaid, 2) }}</div>
                                <div class="stat-label">إجمالي المسدد</div>
                            </div>
                        </div>
                        <div class="mt-2 small text-muted">مجموع كل الدفعات</div>
                    </div>
                </div>

                {{-- الفواتير غير المسددة --}}
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card {{ $unpaidCount > 0 ? 'bg-danger bg-opacity-10 border border-danger border-opacity-25' : 'bg-light border' }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon {{ $unpaidCount > 0 ? 'bg-danger bg-opacity-10' : 'bg-secondary bg-opacity-10' }}">
                                <i class="bi bi-exclamation-circle {{ $unpaidCount > 0 ? 'text-danger' : 'text-secondary' }}"></i>
                            </div>
                            <div>
                                <div class="stat-value {{ $unpaidCount > 0 ? 'text-danger' : 'text-secondary' }}">
                                    {{ $unpaidCount }}
                                </div>
                                <div class="stat-label">فاتورة غير مسددة</div>
                            </div>
                        </div>
                        <div class="mt-2 small {{ $unpaidCount > 0 ? 'text-danger' : 'text-muted' }}">
                            @if($unpaidCount > 0)
                                متبقي: <strong>{{ number_format($unpaidTotal, 2) }}</strong>
                            @else
                                لا توجد مستحقات
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== فلتر الفترة الزمنية ===== --}}
            <x-admin.card class="mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.subscriber-account.show', $subscriber) }}" class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label fw-semibold small mb-1">
                                <i class="bi bi-calendar-range me-1"></i>
                                كشف حساب من
                            </label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label fw-semibold small mb-1">إلى</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-funnel me-1"></i>
                                تصفية
                            </button>
                            @if($dateFrom || $dateTo)
                                <a href="{{ route('admin.subscriber-account.show', $subscriber) }}" class="btn btn-outline-secondary btn-sm ms-1">
                                    <i class="bi bi-x-circle me-1"></i>
                                    إلغاء الفلتر
                                </a>
                            @endif
                        </div>
                        @if($dateFrom || $dateTo)
                            <div class="col-auto ms-auto">
                                <div class="d-flex gap-3 align-items-center small text-muted">
                                    <span>فواتير الفترة: <strong class="text-dark">{{ number_format($filteredInvoiced, 2) }}</strong></span>
                                    <span>دفعات الفترة: <strong class="text-success">{{ number_format($filteredPaid, 2) }}</strong></span>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </x-admin.card>

            {{-- ===== تبويبات: الفواتير + الدفعات ===== --}}
            <x-admin.card>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="accountTabs">
                        <li class="nav-item">
                            <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#invoicesTab">
                                <i class="bi bi-receipt me-1"></i>
                                سجل الفواتير
                                <span class="badge bg-primary ms-1">{{ $invoices->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#paymentsTab">
                                <i class="bi bi-cash-coin me-1"></i>
                                سجل الدفعات
                                <span class="badge bg-success ms-1">{{ $payments->count() }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">

                        {{-- ===== تبويب الفواتير ===== --}}
                        <div class="tab-pane fade show active" id="invoicesTab">
                            @if($invoices->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="fw-semibold">رقم الفاتورة</th>
                                                <th class="text-center fw-semibold">تاريخ الفاتورة</th>
                                                <th class="text-center fw-semibold">الاستهلاك (kWh)</th>
                                                <th class="text-center fw-semibold">الرصيد السابق</th>
                                                <th class="text-center fw-semibold">قيمة الفاتورة</th>
                                                <th class="text-center fw-semibold">الإجمالي</th>
                                                <th class="text-center fw-semibold">المسدد</th>
                                                <th class="text-center fw-semibold">المتبقي</th>
                                                <th class="text-center fw-semibold">الحالة</th>
                                                <th class="text-end fw-semibold"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($invoices as $inv)
                                                @php
                                                    $paid      = (float) $inv->payments_sum_amount_paid;
                                                    $remaining = max((float) $inv->total_amount - $paid, 0);
                                                    $statusInfo = match($inv->invoice_status) {
                                                        0 => ['label' => 'مسودة',          'class' => 'bg-secondary'],
                                                        1 => ['label' => 'جديدة',           'class' => 'bg-primary'],
                                                        2 => ['label' => 'مسددة جزئياً',   'class' => 'bg-warning text-dark'],
                                                        3 => ['label' => 'مسددة',           'class' => 'bg-success'],
                                                        4 => ['label' => 'ملغاة',           'class' => 'bg-danger'],
                                                        default => ['label' => '—',          'class' => 'bg-secondary'],
                                                    };
                                                @endphp
                                                <tr class="{{ $inv->invoice_status == 4 ? 'opacity-50' : '' }}">
                                                    <td>
                                                        <code class="text-primary small">{{ $inv->invoice_number ?? '—' }}</code>
                                                    </td>
                                                    <td class="text-center">{{ $inv->invoice_date->format('Y-m-d') }}</td>
                                                    <td class="text-center">
                                                        {{ $inv->consumption_kwh ? number_format($inv->consumption_kwh, 2) : '—' }}
                                                    </td>
                                                    <td class="text-center">
                                                        @if((float) $inv->previous_balance > 0)
                                                            <span class="text-danger">{{ number_format($inv->previous_balance, 2) }}</span>
                                                        @elseif((float) $inv->previous_balance < 0)
                                                            <span class="text-success">{{ number_format($inv->previous_balance, 2) }}</span>
                                                        @else
                                                            <span class="text-muted">0.00</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ number_format($inv->invoice_amount, 2) }}</td>
                                                    <td class="text-center fw-semibold">{{ number_format($inv->total_amount, 2) }}</td>
                                                    <td class="text-center text-success">
                                                        {{ $paid > 0 ? number_format($paid, 2) : '—' }}
                                                    </td>
                                                    <td class="text-center">
                                                        @if($inv->invoice_status == 4)
                                                            <span class="text-muted">—</span>
                                                        @elseif($remaining > 0)
                                                            <span class="text-danger fw-semibold">{{ number_format($remaining, 2) }}</span>
                                                        @else
                                                            <span class="text-success">0.00</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="{{ route('admin.invoices.show', $inv) }}"
                                                           class="btn btn-xs btn-outline-secondary py-0 px-1"
                                                           title="عرض الفاتورة">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-semibold">
                                            <tr>
                                                <td colspan="4" class="text-end">الإجماليات:</td>
                                                <td class="text-center">
                                                    {{ number_format($invoices->whereNotIn('invoice_status',[0,4])->sum('invoice_amount'), 2) }}
                                                </td>
                                                <td class="text-center">
                                                    {{ number_format($invoices->whereNotIn('invoice_status',[0,4])->sum('total_amount'), 2) }}
                                                </td>
                                                <td class="text-center text-success">
                                                    {{ number_format($invoices->sum('payments_sum_amount_paid'), 2) }}
                                                </td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-receipt" style="font-size:2.5rem;color:#ccc;"></i>
                                    <p class="text-muted mt-2">لا توجد فواتير في هذه الفترة</p>
                                </div>
                            @endif
                        </div>

                        {{-- ===== تبويب الدفعات ===== --}}
                        <div class="tab-pane fade" id="paymentsTab">
                            @if($payments->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="fw-semibold">رقم الإيصال</th>
                                                <th class="text-center fw-semibold">تاريخ الدفع</th>
                                                <th class="fw-semibold">الفاتورة المرتبطة</th>
                                                <th class="text-center fw-semibold">طريقة الدفع</th>
                                                <th class="text-center fw-semibold">المبلغ المسدد</th>
                                                <th class="fw-semibold">ملاحظات</th>
                                                <th class="text-end fw-semibold"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($payments as $pay)
                                                @php
                                                    $methodLabels = \App\Models\Payment::methodLabels();
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <code class="text-success small">{{ $pay->receipt_number ?? '—' }}</code>
                                                    </td>
                                                    <td class="text-center">{{ $pay->payment_date->format('Y-m-d') }}</td>
                                                    <td>
                                                        @if($pay->invoice)
                                                            <a href="{{ route('admin.invoices.show', $pay->invoice) }}" class="text-primary small fw-semibold">
                                                                {{ $pay->invoice->invoice_number ?? '#' . $pay->invoice_id }}
                                                            </a>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-secondary">
                                                            {{ $methodLabels[$pay->payment_method] ?? $pay->payment_method }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center fw-bold text-success">
                                                        {{ number_format($pay->amount_paid, 2) }}
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ Str::limit($pay->notes ?? '—', 50) }}</small>
                                                    </td>
                                                    <td class="text-end">
                                                        @if($pay->invoice)
                                                            <a href="{{ route('admin.invoices.payments.show', [$pay->invoice, $pay]) }}"
                                                               class="btn btn-xs btn-outline-secondary py-0 px-1"
                                                               title="عرض الإيصال">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-semibold">
                                            <tr>
                                                <td colspan="4" class="text-end">إجمالي الدفعات:</td>
                                                <td class="text-center text-success">
                                                    {{ number_format($payments->sum('amount_paid'), 2) }}
                                                </td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-cash-coin" style="font-size:2.5rem;color:#ccc;"></i>
                                    <p class="text-muted mt-2">لا توجد دفعات في هذه الفترة</p>
                                </div>
                            @endif
                        </div>

                    </div>{{-- /.tab-content --}}
                </div>
            </x-admin.card>{{-- /.general-card --}}

        </div>{{-- /.col-lg-9 --}}
    </div>{{-- /.row --}}
</div>
@endsection

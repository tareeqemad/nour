@if(!empty($append))
    {{-- وضع الإلحاق: صفوف فقط --}}
    @foreach($invoices as $i => $invoice)
    <tr>
        <td>{{ ($invoices->currentPage() - 1) * $invoices->perPage() + $loop->iteration }}</td>
        <td>
            <a href="{{ route('admin.invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">
                @if($invoice->invoice_number)
                    {{ $invoice->invoice_number }}
                @else
                    <span class="text-muted fst-italic">مسودة</span>
                @endif
            </a>
        </td>
        <td>{{ $invoice->subscriber->subscription_number ?? '-' }}</td>
        <td>{{ $invoice->subscriber->subscriber_name ?? '-' }}</td>
        <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
        <td>{{ number_format($invoice->consumption_kwh, 2) }}</td>
        <td>
            @php $prevBal = (float)$invoice->previous_balance; @endphp
            @if($prevBal == 0)
                <span class="text-muted">—</span>
            @elseif($prevBal > 0)
                <span class="text-danger fw-semibold" data-bs-toggle="tooltip" title="مديون على المشترك">
                    +{{ number_format($prevBal, 2) }} ₪
                </span>
            @else
                <span class="text-success fw-semibold" data-bs-toggle="tooltip" title="رصيد دائن لصالح المشترك">
                    {{ number_format($prevBal, 2) }} ₪
                </span>
            @endif
        </td>
        <td>{{ number_format($invoice->invoice_amount, 2) }} ₪</td>
        @php
            $paid      = (float)($invoice->payments_sum_amount_paid ?? 0);
            $remaining = max((float)$invoice->total_amount - $paid, 0);
        @endphp
        <td class="fw-bold">
            {{ number_format($remaining, 2) }} ₪
            @if($paid > 0)
                <br><small class="text-success fw-normal">
                    <i class="bi bi-check-circle me-1"></i>مسدد: {{ number_format($paid, 2) }}
                </small>
            @endif
        </td>
        <td>
            <span class="badge bg-{{ $invoice->status_badge_class }}">
                {{ $invoice->status_name }}
            </span>
        </td>
        <td>
            @if($invoice->due_date)
                <span class="{{ $invoice->due_date->isPast() && $invoice->invoice_status < 3 ? 'text-danger fw-semibold' : '' }}">
                    {{ $invoice->due_date->format('Y-m-d') }}
                </span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-1">
                <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-info" title="عرض">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('admin.invoices.print', $invoice) }}" class="btn btn-sm btn-outline-secondary" title="طباعة" target="_blank">
                    <i class="bi bi-printer"></i>
                </a>
                @can('update', $invoice)
                <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-warning" title="تعديل">
                    <i class="bi bi-pencil"></i>
                </a>
                @endcan
                @can('issue', $invoice)
                <button type="button" class="btn btn-sm btn-success"
                        data-action="{{ route('admin.invoices.issue', $invoice) }}"
                        data-csrf="{{ csrf_token() }}"
                        onclick="swalIssue(this)" title="إصدار">
                    <i class="bi bi-send"></i>
                </button>
                @endcan
                @can('createForInvoice', [App\Models\Payment::class, $invoice])
                <a href="{{ route('admin.invoices.payments.create', $invoice) }}"
                   class="btn btn-sm btn-outline-success" title="تسجيل دفعة">
                    <i class="bi bi-cash-coin"></i>
                </a>
                @endcan
                @can('cancel', $invoice)
                    @if($invoice->invoice_status === \App\Models\Invoice::STATUS_DRAFT)
                        <form action="{{ route('admin.invoices.cancel', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه المسودة؟')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف المسودة">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    @elseif($invoice->invoice_status === \App\Models\Invoice::STATUS_ISSUED)
                        <button type="button" class="btn btn-sm btn-outline-danger cancel-invoice-btn"
                            data-id="{{ $invoice->id }}"
                            data-number="{{ $invoice->invoice_number }}"
                            data-url="{{ route('admin.invoices.cancel', $invoice) }}"
                            title="إلغاء الفاتورة">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    @endif
                @endcan
            </div>
        </td>
    </tr>
    @endforeach
@elseif($invoices->count())
    {{-- وضع كامل: الجدول + العداد --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <small class="text-muted">
            <i class="bi bi-receipt me-1"></i>
            إجمالي النتائج: <strong id="invoicesCount">{{ $invoices->total() }}</strong> فاتورة
        </small>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="invoicesTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>رقم الفاتورة</th>
                    <th>رقم الاشتراك</th>
                    <th>اسم المشترك</th>
                    <th>تاريخ الفاتورة</th>
                    <th>الاستهلاك kWh</th>
                    <th>
                        الرصيد السابق
                        <i class="bi bi-question-circle text-muted ms-1"
                           data-bs-toggle="tooltip" data-bs-placement="top"
                           title="رصيد المشترك قبل إصدار الفاتورة. موجب = مديون عليه، سالب = دائن له"></i>
                    </th>
                    <th>
                        قيمة الفاتورة
                        <i class="bi bi-question-circle text-muted ms-1"
                           data-bs-toggle="tooltip" data-bs-placement="top"
                           title="القيمة المستحقة عن الفترة الحالية = max(ثمن الاستهلاك، الحد الأدنى)"></i>
                    </th>
                    <th>
                        المبلغ المطلوب
                        <i class="bi bi-question-circle text-muted ms-1"
                           data-bs-toggle="tooltip" data-bs-placement="top"
                           title="صافي المبلغ المطلوب من المشترك = قيمة الفاتورة + الرصيد السابق"></i>
                    </th>
                    <th>حالة الفاتورة</th>
                    <th>تاريخ الاستحقاق</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody id="invoicesBody">
                @foreach($invoices as $i => $invoice)
                <tr>
                    <td>{{ $invoices->firstItem() + $i }}</td>
                    <td>
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">
                            @if($invoice->invoice_number)
                                {{ $invoice->invoice_number }}
                            @else
                                <span class="text-muted fst-italic">مسودة</span>
                            @endif
                        </a>
                    </td>
                    <td>{{ $invoice->subscriber->subscription_number ?? '-' }}</td>
                    <td>{{ $invoice->subscriber->subscriber_name ?? '-' }}</td>
                    <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                    <td>{{ number_format($invoice->consumption_kwh, 2) }}</td>
                    <td>
                        @php $prevBal = (float)$invoice->previous_balance; @endphp
                        @if($prevBal == 0)
                            <span class="text-muted">—</span>
                        @elseif($prevBal > 0)
                            <span class="text-danger fw-semibold"
                                  data-bs-toggle="tooltip" title="مديون على المشترك">
                                +{{ number_format($prevBal, 2) }} ₪
                            </span>
                        @else
                            <span class="text-success fw-semibold"
                                  data-bs-toggle="tooltip" title="رصيد دائن لصالح المشترك">
                                {{ number_format($prevBal, 2) }} ₪
                            </span>
                        @endif
                    </td>
                    <td>{{ number_format($invoice->invoice_amount, 2) }} ₪</td>
                    @php
                        $paid      = (float)($invoice->payments_sum_amount_paid ?? 0);
                        $remaining = max((float)$invoice->total_amount - $paid, 0);
                    @endphp
                    <td class="fw-bold">
                        {{ number_format($remaining, 2) }} ₪
                        @if($paid > 0)
                            <br><small class="text-success fw-normal">
                                <i class="bi bi-check-circle me-1"></i>مسدد: {{ number_format($paid, 2) }}
                            </small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $invoice->status_badge_class }}">
                            {{ $invoice->status_name }}
                        </span>
                    </td>
                    <td>
                        @if($invoice->due_date)
                            <span class="{{ $invoice->due_date->isPast() && $invoice->invoice_status < 3 ? 'text-danger fw-semibold' : '' }}">
                                {{ $invoice->due_date->format('Y-m-d') }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-info" title="عرض">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.invoices.print', $invoice) }}" class="btn btn-sm btn-outline-secondary" title="طباعة" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                            @can('update', $invoice)
                            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-warning" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('issue', $invoice)
                            <button type="button" class="btn btn-sm btn-success"
                                    data-action="{{ route('admin.invoices.issue', $invoice) }}"
                                    data-csrf="{{ csrf_token() }}"
                                    onclick="swalIssue(this)" title="إصدار">
                                <i class="bi bi-send"></i>
                            </button>
                            @endcan
                            @can('createForInvoice', [App\Models\Payment::class, $invoice])
                            <a href="{{ route('admin.invoices.payments.create', $invoice) }}"
                               class="btn btn-sm btn-outline-success" title="تسجيل دفعة">
                                <i class="bi bi-cash-coin"></i>
                            </a>
                            @endcan
                            @can('cancel', $invoice)
                                @if($invoice->invoice_status === \App\Models\Invoice::STATUS_DRAFT)
                                    <form action="{{ route('admin.invoices.cancel', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه المسودة؟')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف المسودة">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @elseif($invoice->invoice_status === \App\Models\Invoice::STATUS_ISSUED)
                                    <button type="button" class="btn btn-sm btn-outline-danger cancel-invoice-btn"
                                        data-id="{{ $invoice->id }}"
                                        data-number="{{ $invoice->invoice_number }}"
                                        data-url="{{ route('admin.invoices.cancel', $invoice) }}"
                                        title="إلغاء الفاتورة">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Scroll loading indicator --}}
    @if($invoices->hasMorePages())
        <div id="scrollLoader" class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="text-muted me-2">جاري تحميل المزيد...</span>
        </div>
    @endif
@else
    <div class="empty-state text-center py-5">
        <i class="bi bi-receipt fs-1 text-muted"></i>
        <p class="mt-3 text-muted">لا توجد فواتير مطابقة للبحث.</p>
    </div>
@endif

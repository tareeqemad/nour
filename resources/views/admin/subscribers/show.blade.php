@extends('layouts.admin')

@section('title', 'المشترك — ' . $subscriber->subscriber_name)

@php
    $breadcrumbTitle = 'تفاصيل المشترك';
    $breadcrumbParent = 'إدارة بيانات المشتركين';
    $breadcrumbParentUrl = route('admin.subscribers.index');
    $statusClass = match((int)$subscriber->subscription_status) {
        1 => 'success', 2 => 'warning', 3 => 'danger', default => 'secondary',
    };
@endphp

@push('styles')
<style>
    .info-item { display:flex; gap:.5rem; align-items:baseline; margin-bottom:.5rem; }
    .info-item .label { min-width:120px; color:#6c757d; font-size:.83rem; }
    .info-item .value { font-weight:600; font-size:.88rem; }
    .section-title {
        font-size:.78rem; font-weight:700; text-transform:uppercase;
        letter-spacing:.05em; color:#6c757d; margin-bottom:.65rem;
        padding-bottom:.35rem; border-bottom:2px solid #e9ecef;
    }
    .stat-card { border-radius:.75rem; padding:1rem 1.25rem; text-align:center; }
    .stat-card .stat-value { font-size:1.3rem; font-weight:700; line-height:1.2; }
    .stat-card .stat-label { font-size:.78rem; color:#6c757d; margin-top:2px; }
</style>
@endpush

@section('content')
<div class="general-page">

    {{-- شريط الإجراءات --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right me-1"></i>رجوع
        </a>
        <div class="d-flex gap-2">
            @can('update', $subscriber)
                <a href="{{ route('admin.subscribers.edit', $subscriber) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>تعديل
                </a>
            @endcan
            <a href="{{ route('admin.subscriber-account.show', $subscriber) }}" class="btn btn-sm btn-outline-info">
                <i class="bi bi-wallet2 me-1"></i>حساب المشترك
            </a>
        </div>
    </div>

    <div class="row g-3">

        {{-- ===== عمود جانبي: بطاقة المشترك ===== --}}
        <div class="col-lg-3 col-md-4">
            <x-admin.card class="h-100">
                <div class="card-body">
                    {{-- الصورة والاسم --}}
                    <div class="text-center mb-3">
                        <div class="mx-auto mb-2 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:64px;height:64px;">
                            <i class="bi bi-person-fill text-primary" style="font-size:2rem;"></i>
                        </div>
                        <h6 class="fw-bold mb-0">{{ $subscriber->subscriber_name }}</h6>
                        <code class="text-primary">{{ $subscriber->subscription_number }}</code>
                        <div class="mt-1">
                            <span class="badge bg-{{ $statusClass }}">{{ $subscriber->subscription_status_name }}</span>
                            @if($subscriber->is_employee_subscription)
                                <span class="badge bg-info">موظف</span>
                            @endif
                        </div>
                    </div>

                    {{-- بيانات سريعة --}}
                    <div class="section-title">بيانات الاتصال</div>
                    <div class="info-item">
                        <span class="label"><i class="bi bi-credit-card me-1"></i>الهوية</span>
                        <span class="value">{{ $subscriber->subscriber_id_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label"><i class="bi bi-phone me-1"></i>الجوال</span>
                        <span class="value">{{ $subscriber->phone ?? '—' }}</span>
                    </div>
                    @if($subscriber->alt_phone)
                    <div class="info-item">
                        <span class="label"><i class="bi bi-phone me-1"></i>بديل</span>
                        <span class="value">{{ $subscriber->alt_phone }}</span>
                    </div>
                    @endif
                    <div class="info-item">
                        <span class="label"><i class="bi bi-geo-alt me-1"></i>المحافظة</span>
                        <span class="value">{{ $subscriber->governorate_display_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label"><i class="bi bi-house me-1"></i>العنوان</span>
                        <span class="value" style="font-size:.82rem;">{{ $subscriber->address ?? '—' }}</span>
                    </div>
                    @if($subscriber->box_number)
                    <div class="info-item">
                        <span class="label"><i class="bi bi-mailbox me-1"></i>الصندوق</span>
                        <span class="value">{{ $subscriber->box_number }}</span>
                    </div>
                    @endif

                    <div class="section-title mt-3">بيانات الاشتراك</div>
                    <div class="info-item">
                        <span class="label"><i class="bi bi-tags me-1"></i>التصنيف</span>
                        <span class="value">{{ $subscriber->subscription_category_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label"><i class="bi bi-diagram-3 me-1"></i>الفاز</span>
                        <span class="value">{{ $subscriber->phase_type_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label"><i class="bi bi-gear me-1"></i>الخدمة</span>
                        <span class="value">{{ $subscriber->service_type_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label"><i class="bi bi-speedometer2 me-1"></i>العداد</span>
                        <span class="value">{{ $subscriber->meter_number ?? '—' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label"><i class="bi bi-lightning-charge me-1"></i>الأمبير</span>
                        <span class="value">{{ $subscriber->ampere ? $subscriber->ampere . ' A' : '—' }}</span>
                    </div>
                    @if($subscriber->opening_reading !== null)
                    <div class="info-item">
                        <span class="label"><i class="bi bi-speedometer me-1"></i>افتتاحية</span>
                        <span class="value">{{ number_format($subscriber->opening_reading, 2) }}</span>
                    </div>
                    @endif

                    <div class="section-title mt-3">التواريخ</div>
                    <div class="info-item">
                        <span class="label"><i class="bi bi-calendar me-1"></i>الاشتراك</span>
                        <span class="value">{{ $subscriber->subscription_date?->format('Y-m-d') ?? '—' }}</span>
                    </div>
                    @if($subscriber->request_date)
                    <div class="info-item">
                        <span class="label"><i class="bi bi-calendar-check me-1"></i>الطلب</span>
                        <span class="value">{{ $subscriber->request_date->format('Y-m-d') }}</span>
                    </div>
                    @endif
                </div>
            </x-admin.card>
        </div>

        {{-- ===== عمود رئيسي ===== --}}
        <div class="col-lg-9 col-md-8">

            {{-- إحصائيات مالية --}}
            <div class="row g-3 mb-3">
                <div class="col-md-3 col-6">
                    <div class="stat-card" style="background:var(--color-info-bg,#F0F9FF);border:1px solid var(--color-info-border,#BAE6FD);">
                        <div class="stat-value" style="color:var(--color-primary,#24308F);">{{ $invoiceStats['total'] }}</div>
                        <div class="stat-label">إجمالي الفواتير</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card" style="background:#d1e7dd;border:1px solid #198754;">
                        <div class="stat-value" style="color:#198754;">{{ number_format($invoiceStats['paid'], 2) }} ₪</div>
                        <div class="stat-label">إجمالي المدفوع</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    @php
                        $balClass = $invoiceStats['balance'] > 0 ? 'background:#fff3cd;border:1px solid #ffc107;' : 'background:#d1e7dd;border:1px solid #198754;';
                        $balColor = $invoiceStats['balance'] > 0 ? '#856404' : '#198754';
                    @endphp
                    <div class="stat-card" style="{{ $balClass }}">
                        <div class="stat-value" style="color:{{ $balColor }};">{{ number_format($invoiceStats['balance'], 2) }} ₪</div>
                        <div class="stat-label">الرصيد المستحق</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card" style="background:#f8f9fa;border:1px solid #dee2e6;">
                        <div class="stat-value" style="color:#6c757d;">{{ $invoiceStats['draft_count'] }}</div>
                        <div class="stat-label">فواتير مسودة</div>
                    </div>
                </div>
            </div>

            {{-- آخر قراءة عداد --}}
            @if($lastReading)
            <x-admin.card class="mb-3">
                <div class="card-body">
                    <div class="section-title">
                        <i class="bi bi-speedometer2 me-1"></i>آخر قراءة عداد
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <small class="text-muted">تاريخ القراءة</small>
                            <div class="fw-bold">{{ $lastReading->reading_date?->format('Y-m-d') ?? '—' }}</div>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">السابقة</small>
                            <div class="fw-bold">{{ number_format($lastReading->previous_reading, 2) }}</div>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">الحالية</small>
                            <div class="fw-bold">{{ number_format($lastReading->current_reading, 2) }}</div>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">الاستهلاك</small>
                            <div class="fw-bold text-primary">{{ number_format($lastReading->consumption_kwh, 2) }} kWh</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">الحالة</small>
                            <div>
                                @if($lastReading->action_status == 0)
                                    <span class="badge bg-secondary">غير معتمدة</span>
                                @elseif($lastReading->action_status == 1)
                                    <span class="badge bg-success">معتمدة</span>
                                @else
                                    <span class="badge bg-info">مفوترة</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </x-admin.card>
            @endif

            {{-- آخر الفواتير --}}
            <x-admin.card class="mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="section-title mb-0 pb-0 border-0">
                            <i class="bi bi-receipt me-1"></i>آخر الفواتير
                        </div>
                        <a href="{{ route('admin.subscriber-account.show', $subscriber) }}" class="btn btn-sm btn-outline-primary">
                            عرض الكل
                        </a>
                    </div>
                    @if($recentInvoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0 text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الفاتورة</th>
                                        <th>التاريخ</th>
                                        <th>الاستهلاك</th>
                                        <th>المبلغ</th>
                                        <th>المدفوع</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentInvoices as $inv)
                                        @php
                                            $invStatusClass = match((int)$inv->invoice_status) {
                                                0 => 'bg-secondary', 1 => 'bg-warning text-dark', 2 => 'bg-info',
                                                3 => 'bg-success', 4 => 'bg-danger', default => 'bg-secondary',
                                            };
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.invoices.show', $inv) }}" class="text-primary fw-semibold text-decoration-none">
                                                    {{ $inv->invoice_number ?? 'مسودة' }}
                                                </a>
                                            </td>
                                            <td>{{ $inv->invoice_date?->format('Y-m-d') ?? '—' }}</td>
                                            <td>{{ number_format($inv->consumption_kwh, 2) }} kWh</td>
                                            <td class="fw-bold">{{ number_format($inv->total_amount, 2) }} ₪</td>
                                            <td>{{ number_format($inv->paidAmount(), 2) }} ₪</td>
                                            <td><span class="badge {{ $invStatusClass }}">{{ $inv->status_name }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-receipt" style="font-size:2rem;opacity:.3;"></i>
                            <p class="mt-2 mb-0">لا توجد فواتير بعد</p>
                        </div>
                    @endif
                </div>
            </x-admin.card>

            {{-- وحدات التوليد --}}
            <x-admin.card class="mb-3">
                <div class="card-body">
                    <div class="section-title">
                        <i class="bi bi-lightning-charge me-1"></i>وحدات التوليد المرتبطة
                    </div>
                    @if($subscriber->generationUnits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0 text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>اسم الوحدة</th>
                                        <th>كود الوحدة</th>
                                        <th>المشغل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subscriber->generationUnits as $unit)
                                        <tr>
                                            <td class="fw-semibold">{{ $unit->name }}</td>
                                            <td><code>{{ $unit->unit_code }}</code></td>
                                            <td>{{ $unit->operator->name ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">لا توجد وحدات توليد مرتبطة</div>
                    @endif
                </div>
            </x-admin.card>

            {{-- ملاحظات + معلومات التتبع --}}
            <div class="row g-3">
                @if($subscriber->notes)
                <div class="col-md-6">
                    <x-admin.card class="h-100">
                        <div class="card-body">
                            <div class="section-title"><i class="bi bi-chat-left-text me-1"></i>ملاحظات</div>
                            <p class="mb-0" style="font-size:.88rem;">{{ $subscriber->notes }}</p>
                        </div>
                    </x-admin.card>
                </div>
                @endif
                <div class="{{ $subscriber->notes ? 'col-md-6' : 'col-12' }}">
                    <x-admin.card class="h-100">
                        <div class="card-body">
                            <div class="section-title"><i class="bi bi-clock-history me-1"></i>معلومات التتبع</div>
                            <div class="info-item">
                                <span class="label">أُنشئ بواسطة</span>
                                <span class="value">{{ $subscriber->creator->name ?? '—' }} — {{ $subscriber->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                            @if($subscriber->last_updated_by)
                            <div class="info-item">
                                <span class="label">آخر تعديل</span>
                                <span class="value">{{ $subscriber->updater->name ?? '—' }} — {{ $subscriber->updated_at->format('Y-m-d H:i') }}</span>
                            </div>
                            @endif
                        </div>
                    </x-admin.card>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'تفاصيل المشترك')

@php
    $breadcrumbTitle = 'تفاصيل المشترك';
    $breadcrumbParent = 'إدارة بيانات المشتركين';
    $breadcrumbParentUrl = route('admin.subscribers.index');
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-person me-2"></i>
                                تفاصيل المشترك
                            </h5>
                            <div class="general-subtitle">
                                {{ $subscriber->subscriber_name }}
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-2"></i>
                                العودة للقائمة
                            </a>
                            @can('update', $subscriber)
                                <a href="{{ route('admin.subscribers.edit', $subscriber) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil me-2"></i>
                                    تعديل
                                </a>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            {{-- البيانات الأساسية --}}
                            <div class="col-12">
                                <h6 class="fw-semibold mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    البيانات الأساسية
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">رقم الاشتراك</label>
                                <div class="form-control-plaintext">
                                    <code class="text-primary">{{ $subscriber->subscription_number }}</code>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">رقم هوية المشترك</label>
                                <div class="form-control-plaintext">{{ $subscriber->subscriber_id_number }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">اسم المشترك</label>
                                <div class="form-control-plaintext fw-medium">{{ $subscriber->subscriber_name }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">رقم الجوال</label>
                                <div class="form-control-plaintext">
                                    {{ $subscriber->phone ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-muted">عنوان المشترك</label>
                                <div class="form-control-plaintext">{{ $subscriber->address ?? '-' }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">تاريخ الاشتراك</label>
                                <div class="form-control-plaintext">
                                    {{ $subscriber->subscription_date?->format('Y-m-d') ?? '-' }}
                                </div>
                            </div>

                            {{-- التصنيفات --}}
                            <div class="col-12 mt-4">
                                <h6 class="fw-semibold mb-3">
                                    <i class="bi bi-tags me-2"></i>
                                    التصنيفات
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">تصنيف الاشتراك</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-info">{{ $subscriber->subscription_category_name }}</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">نوع الفاز</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-secondary">{{ $subscriber->phase_type_name }}</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">حالة الاشتراك</label>
                                <div class="form-control-plaintext">
                                    @php
                                        $statusClass = match($subscriber->subscription_status) {
                                            1 => 'bg-success',
                                            2 => 'bg-warning',
                                            3 => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $subscriber->subscription_status_name }}</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">نوع الخدمة</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-primary">{{ $subscriber->service_type_name }}</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">رقم العداد</label>
                                <div class="form-control-plaintext">
                                    {{ $subscriber->meter_number ?? '-' }}
                                </div>
                            </div>

                            {{-- وحدات التوليد --}}
                            <div class="col-12 mt-4">
                                <h6 class="fw-semibold mb-3">
                                    <i class="bi bi-lightning-charge me-2"></i>
                                    وحدات التوليد المرتبطة
                                </h6>
                            </div>

                            <div class="col-md-12">
                                @if($subscriber->generationUnits->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>اسم الوحدة</th>
                                                    <th>كود الوحدة</th>
                                                    <th>المشغل</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($subscriber->generationUnits as $unit)
                                                    <tr>
                                                        <td>{{ $unit->name }}</td>
                                                        <td><code>{{ $unit->unit_code }}</code></td>
                                                        <td>{{ $unit->operator->name ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">لا توجد وحدات توليد مرتبطة</div>
                                @endif
                            </div>

                            {{-- معلومات التتبع --}}
                            <div class="col-12 mt-4">
                                <h6 class="fw-semibold mb-3">
                                    <i class="bi bi-clock-history me-2"></i>
                                    معلومات التتبع
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">المدخل</label>
                                <div class="form-control-plaintext">
                                    {{ $subscriber->creator->name ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">تاريخ الإدخال</label>
                                <div class="form-control-plaintext">
                                    {{ $subscriber->created_at->format('Y-m-d H:i') }}
                                </div>
                            </div>

                            @if($subscriber->last_updated_by)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-muted">آخر من عدل</label>
                                    <div class="form-control-plaintext">
                                        {{ $subscriber->updater->name ?? '-' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-muted">تاريخ التعديل</label>
                                    <div class="form-control-plaintext">
                                        {{ $subscriber->updated_at->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


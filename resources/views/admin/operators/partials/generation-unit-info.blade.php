@php
    $unit = $generationUnit;
@endphp

<div class="generation-unit-detail-card border rounded-3 p-3 mb-3">
    <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-1">
                <i class="bi bi-lightning-charge text-primary me-1"></i>
                {{ $unit->name }}
            </h6>
            @if($unit->unit_code)
                <code class="text-primary">{{ $unit->unit_code }}</code>
            @endif
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap justify-content-end">
            @if($unit->statusDetail)
                <span class="badge {{ $unit->statusDetail->code === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }}">
                    {{ $unit->statusDetail->label }}
                </span>
            @endif
            @can('view', $unit)
                <a href="{{ route('admin.generation-units.show', $unit) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i>
                    عرض التفاصيل
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3">
        @if($unit->phone || $unit->phone_alt || $unit->email)
            <div class="col-md-6">
                <div class="info-item">
                    <label class="info-label">التواصل</label>
                    <div class="info-value">
                        @if($unit->phone)
                            <div><i class="bi bi-telephone me-1 text-muted"></i>{{ $unit->phone }}</div>
                        @endif
                        @if($unit->phone_alt)
                            <div><i class="bi bi-telephone-forward me-1 text-muted"></i>{{ $unit->phone_alt }}</div>
                        @endif
                        @if($unit->email)
                            <div><i class="bi bi-envelope me-1 text-muted"></i>{{ $unit->email }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($unit->governorateDetail || $unit->cityDetail || $unit->detailed_address)
            <div class="col-md-6">
                <div class="info-item">
                    <label class="info-label">الموقع</label>
                    <div class="info-value">
                        @if($unit->governorateDetail)
                            <div>{{ $unit->governorateDetail->label }}</div>
                        @endif
                        @if($unit->cityDetail)
                            <div>{{ $unit->cityDetail->label }}</div>
                        @endif
                        @if($unit->detailed_address)
                            <div class="text-muted small">{{ $unit->detailed_address }}</div>
                        @endif
                        @if($unit->latitude && $unit->longitude)
                            <div class="text-muted small">Lat: {{ $unit->latitude }} — Lng: {{ $unit->longitude }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($unit->operationEntityDetail || $unit->operator_id_number)
            <div class="col-md-6">
                <div class="info-item">
                    <label class="info-label">التشغيل</label>
                    <div class="info-value">
                        @if($unit->operationEntityDetail)
                            <div>{{ $unit->operationEntityDetail->label }}</div>
                        @endif
                        @if($unit->operator_id_number)
                            <div class="text-muted small">رقم هوية المشغل: {{ $unit->operator_id_number }}</div>
                        @endif
                        @if($unit->operator_name)
                            <div class="text-muted small">اسم المشغل: {{ $unit->operator_name }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($unit->total_capacity || $unit->synchronizationAvailableDetail || $unit->max_synchronization_capacity)
            <div class="col-md-6">
                <div class="info-item">
                    <label class="info-label">القدرات الفنية</label>
                    <div class="info-value">
                        @if($unit->total_capacity)
                            <div>القدرة الإجمالية: {{ number_format($unit->total_capacity) }} KVA</div>
                        @endif
                        @if($unit->synchronizationAvailableDetail)
                            <div>مزامنة المولدات: {{ $unit->synchronizationAvailableDetail->label }}</div>
                        @endif
                        @if($unit->max_synchronization_capacity)
                            <div>قدرة المزامنة القصوى: {{ number_format($unit->max_synchronization_capacity, 2) }} KVA</div>
                        @endif
                        <div class="text-muted small">
                            المولدات: {{ $unit->generators->count() }} / {{ $unit->generators_count ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($unit->beneficiaries_count || $unit->beneficiaries_description || $unit->environmentalComplianceStatusDetail)
            <div class="col-12">
                <div class="info-item mb-0">
                    <label class="info-label">المستفيدون والبيئة</label>
                    <div class="info-value">
                        @if($unit->beneficiaries_count)
                            <div>عدد المستفيدين: {{ number_format($unit->beneficiaries_count) }}</div>
                        @endif
                        @if($unit->beneficiaries_description)
                            <div class="text-muted small">{{ $unit->beneficiaries_description }}</div>
                        @endif
                        @if($unit->environmentalComplianceStatusDetail)
                            <div>الامتثال البيئي: {{ $unit->environmentalComplianceStatusDetail->label }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

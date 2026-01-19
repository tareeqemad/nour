@if($groupedLogs->isNotEmpty())
    @foreach($groupedLogs as $generatorId => $logs)
        @php
            $generator = $logs->first()->generator;
        @endphp
        
        @if($generator)
            {{-- Generator Header --}}
            <div class="log-generator-group mb-4">
                <div class="log-generator-header bg-light p-3 rounded-top border">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="log-generator-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-lightning-charge fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">
                                    {{ $generator->name }}
                                    <span class="badge bg-secondary ms-2">{{ $generator->generator_number }}</span>
                                </h5>
                                <div class="text-muted small">
                                    {{ $generator->capacity_kva ?? '-' }} KVA
                                    @if($generator->operator)
                                        | {{ $generator->operator->name }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-list-ul me-1"></i>
                            عدد السجلات: <strong>{{ $logs->count() }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Logs for this generator --}}
                <div class="log-list bg-white border border-top-0 rounded-bottom p-3">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>نوع الصيانة</th>
                                    <th>تاريخ الصيانة</th>
                                    <th>اسم الفني</th>
                                    <th>زمن التوقف</th>
                                    <th>تكلفة الصيانة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $record)
                                    <tr>
                                        <td>{{ $record->id }}</td>
                                        <td>
                                            <span class="badge bg-{{ $record->maintenanceTypeDetail?->getBadgeColor() ?? 'secondary' }}">{{ $record->maintenanceTypeDetail?->label ?? '-' }}</span>
                                        </td>
                                        <td>{{ $record->maintenance_date->format('Y-m-d') }}</td>
                                        <td>{{ $record->technician_name ?? '-' }}</td>
                                        <td>
                                            @if($record->downtime_hours)
                                                {{ number_format($record->downtime_hours, 2) }} ساعة
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->maintenance_cost)
                                                {{ number_format($record->maintenance_cost, 2) }} ₪
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="log-row-actions">
                                                @can('view', $record)
                                                    <a href="{{ route('admin.maintenance-records.show', $record) }}" class="btn btn-xs btn-outline-info" title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $record)
                                                    <a href="{{ route('admin.maintenance-records.edit', $record) }}" class="btn btn-xs btn-outline-primary" title="تعديل">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $record)
                                                    <button type="button" class="btn btn-xs btn-outline-danger maintenance-record-delete-btn" 
                                                            data-maintenance-record-id="{{ $record->id }}"
                                                            data-maintenance-record-name="سجل #{{ $record->id }}"
                                                            title="حذف">
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
                </div>
            </div>
        @endif
    @endforeach

    {{-- Pagination for grouped view --}}
    @if(isset($maintenanceRecords) && $maintenanceRecords->hasPages())
        <div class="log-pagination mt-4">
            {{ $maintenanceRecords->links() }}
        </div>
    @endif
@else
    <div class="log-empty-state text-center py-5">
        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">لا توجد سجلات صيانة</h5>
        <p class="text-muted">لم يتم العثور على سجلات صيانة تطابق البحث</p>
    </div>
@endif



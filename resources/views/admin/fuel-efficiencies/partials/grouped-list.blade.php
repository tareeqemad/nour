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
                                    <th>تاريخ الاستهلاك</th>
                                    <th>ساعات التشغيل</th>
                                    <th>كفاءة الوقود</th>
                                    <th>كفاءة الطاقة</th>
                                    <th>التكلفة الإجمالية</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $efficiency)
                                    <tr>
                                        <td>{{ $efficiency->id }}</td>
                                        <td>{{ $efficiency->consumption_date->format('Y-m-d') }}</td>
                                        <td>
                                            @if($efficiency->operating_hours)
                                                {{ number_format($efficiency->operating_hours, 2) }} ساعة
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($efficiency->fuel_efficiency_percentage)
                                                <span class="text-dark fw-semibold">
                                                    {{ $efficiency->fuel_efficiency_percentage }}%
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($efficiency->energy_distribution_efficiency)
                                                <span class="text-dark fw-semibold">
                                                    {{ $efficiency->energy_distribution_efficiency }}%
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($efficiency->total_operating_cost)
                                                {{ number_format($efficiency->total_operating_cost, 2) }} ₪
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="log-row-actions">
                                                @can('view', $efficiency)
                                                    <a href="{{ route('admin.fuel-efficiencies.show', $efficiency) }}" class="btn btn-xs btn-outline-info" title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $efficiency)
                                                    <a href="{{ route('admin.fuel-efficiencies.edit', $efficiency) }}" class="btn btn-xs btn-outline-primary" title="تعديل">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $efficiency)
                                                    <button type="button" class="btn btn-xs btn-outline-danger fuel-efficiency-delete-btn" 
                                                            data-fuel-efficiency-id="{{ $efficiency->id }}"
                                                            data-fuel-efficiency-name="سجل #{{ $efficiency->id }}"
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
    @if(isset($fuelEfficiencies) && $fuelEfficiencies->hasPages())
        <div class="log-pagination mt-4">
            {{ $fuelEfficiencies->links() }}
        </div>
    @endif
@else
    <div class="log-empty-state text-center py-5">
        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">لا توجد سجلات كفاءة وقود</h5>
        <p class="text-muted">لم يتم العثور على سجلات كفاءة وقود تطابق البحث</p>
    </div>
@endif



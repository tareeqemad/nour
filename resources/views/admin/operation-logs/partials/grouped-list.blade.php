@if($groupedLogs->isNotEmpty())
    {{-- بطاقة معلومات المولد/وحدة التوليد --}}
    @include('admin.operation-logs.partials.generator-info-card', ['operationLogs' => $operationLogs, 'totalStats' => $totalStats])
    
    @foreach($groupedLogs as $groupKey => $logs)
        @php
            $firstLog = $logs->first();
            $generator = $firstLog->generator;
            $generationUnit = $generator ? $generator->generationUnit : null;
        @endphp
        
        @if($generator)
            {{-- عنوان المجموعة --}}
            <div class="card border mt-3">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-lightning-charge fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">
                                {{ $generator->name }}
                                <span class="badge bg-secondary ms-2">{{ $generator->generator_number }}</span>
                            </h5>
                            @if($generationUnit)
                                <small class="text-muted">
                                    <i class="bi bi-grid-3x3 me-1"></i>
                                    {{ $generationUnit->name }} ({{ $generationUnit->unit_code }})
                                </small>
                            @endif
                        </div>
                        <div class="ms-auto">
                            <span class="badge bg-info">{{ $logs->count() }} سجل</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 120px;">رقم السجل</th>
                                    <th style="min-width: 100px;">التاريخ</th>
                                    <th style="min-width: 120px;">الوقت</th>
                                    <th style="min-width: 100px;">المدة</th>
                                    <th style="min-width: 100px;">نسبة التحميل</th>
                                    <th style="min-width: 120px;">الوقود المستهلك</th>
                                    <th style="min-width: 120px;">الطاقة المنتجة</th>
                                    <th style="min-width: 100px;" class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td>
                                            @if($log->sequence)
                                                <span class="fw-bold">#{{ $log->formatted_sequence }}</span>
                                            @else
                                                <span class="fw-bold">#{{ $log->id }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $log->operation_date->format('Y-m-d') }}</span>
                                        </td>
                                        <td>
                                            @if($log->start_time && $log->end_time)
                                                <div class="small">
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ date('H:i', strtotime($log->start_time)) }} - {{ date('H:i', strtotime($log->end_time)) }}
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->start_time && $log->end_time)
                                                @php
                                                    $startTime = \Carbon\Carbon::parse($log->operation_date->format('Y-m-d') . ' ' . $log->start_time->format('H:i:s'));
                                                    $endTime = \Carbon\Carbon::parse($log->operation_date->format('Y-m-d') . ' ' . $log->end_time->format('H:i:s'));
                                                    if ($endTime->lt($startTime)) {
                                                        $endTime->addDay();
                                                    }
                                                    $totalMinutes = $startTime->diffInMinutes($endTime);
                                                    $hours = floor($totalMinutes / 60);
                                                    $minutes = $totalMinutes % 60;
                                                @endphp
                                                <div class="small">
                                                    @if($hours > 0){{ $hours }}س @endif
                                                    @if($minutes > 0){{ $minutes }}د @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->load_percentage)
                                                <span class="badge bg-{{ $log->load_percentage >= 80 ? 'success' : ($log->load_percentage >= 50 ? 'warning' : 'danger') }}">
                                                    {{ number_format($log->load_percentage, 2) }}%
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->fuel_consumed)
                                                <div class="small">
                                                    <i class="bi bi-fuel-pump me-1 text-success"></i>
                                                    {{ number_format($log->fuel_consumed, 2) }} لتر
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->energy_produced)
                                                <div class="small">
                                                    <i class="bi bi-lightning me-1 text-warning"></i>
                                                    {{ number_format($log->energy_produced, 2) }} kWh
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                @can('view', $log)
                                                    <a href="{{ route('admin.operation-logs.show', $log) }}" class="btn btn-xs btn-outline-info" title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $log)
                                                    <a href="{{ route('admin.operation-logs.edit', $log) }}" class="btn btn-xs btn-outline-primary" title="تعديل">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $log)
                                                    <button type="button" class="btn btn-xs btn-outline-danger log-delete-btn" 
                                                            data-operation-log-id="{{ $log->id }}"
                                                            data-operation-log-name="سجل #{{ $log->id }}"
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

    @if($operationLogs->hasPages())
        <div class="mt-4">
            {{ $operationLogs->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="bi bi-inbox fs-1 text-muted"></i>
        <p class="text-muted mt-3">لا توجد نتائج للبحث</p>
    </div>
@endif

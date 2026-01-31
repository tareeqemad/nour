@if($meterReadings->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th class="fw-semibold">
                        <i class="bi bi-hash me-1"></i>
                        رقم القراءة
                    </th>
                    <th class="fw-semibold">
                        <i class="bi bi-person me-1"></i>
                        المشترك
                    </th>
                    <th class="fw-semibold">
                        <i class="bi bi-speedometer2 me-1"></i>
                        رقم العداد
                    </th>
                    <th class="text-center fw-semibold">
                        <i class="bi bi-arrow-left-right me-1"></i>
                        القراءات
                    </th>
                    <th class="text-center fw-semibold">
                        <i class="bi bi-lightning-charge me-1"></i>
                        الاستهلاك (Kwh)
                    </th>
                    <th class="text-center fw-semibold">
                        <i class="bi bi-calendar me-1"></i>
                        تاريخ القراءة
                    </th>
                    <th class="text-center fw-semibold">
                        <i class="bi bi-clock me-1"></i>
                        الفترة (يوم)
                    </th>
                    <th class="text-center fw-semibold">
                        <i class="bi bi-funnel me-1"></i>
                        الحالة
                    </th>
                    <th class="text-end fw-semibold">
                        <i class="bi bi-gear me-1"></i>
                        الإجراءات
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($meterReadings as $reading)
                    <tr>
                        <td>
                            <code class="text-primary fw-semibold">{{ $reading->reading_number }}</code>
                        </td>
                        <td>
                            <div>
                                <span class="fw-medium">{{ $reading->subscriber->subscriber_name }}</span>
                                <br>
                                <small class="text-muted">{{ $reading->subscriber->subscription_number }}</small>
                            </div>
                        </td>
                        <td>
                            <span>{{ $reading->meter_number }}</span>
                        </td>
                        <td class="text-center">
                            <div>
                                <small class="text-muted">سابق: </small>
                                <span class="fw-medium">{{ number_format($reading->previous_reading, 2) }}</span>
                                <br>
                                <small class="text-muted">حالي: </small>
                                <span class="fw-bold text-primary">{{ number_format($reading->current_reading, 2) }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info px-3 py-2">
                                <i class="bi bi-lightning-charge me-1"></i>
                                {{ number_format($reading->consumption_kwh, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span>{{ $reading->reading_date->format('Y-m-d') }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $reading->consumption_period_days }}</span>
                        </td>
                        <td class="text-center">
                            @php
                                $statusClass = match($reading->reading_status) {
                                    1 => 'bg-success',
                                    2 => 'bg-warning',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }} px-3 py-2">
                                {{ $reading->reading_status_name }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                @can('view', $reading)
                                    <a href="{{ route('admin.meter-readings.show', $reading) }}" class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @can('update', $reading)
                                    <a href="{{ route('admin.meter-readings.edit', $reading) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @if(auth()->user()->isSuperAdmin())
                                    <form action="{{ route('admin.meter-readings.destroy', $reading) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه القراءة؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($meterReadings->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $meterReadings->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
        <p class="text-muted mt-3">لا توجد قراءات عدادات</p>
        @can('create', App\Models\MeterReading::class)
            <a href="{{ route('admin.meter-readings.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-plus-lg me-1"></i>
                إضافة قراءة جديدة
            </a>
        @endcan
    </div>
@endif


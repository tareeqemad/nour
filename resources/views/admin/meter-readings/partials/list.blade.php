@if($meterReadings->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle table-striped mb-0">
            <thead class="table-light">
                <tr>
                    {{-- خانة التحديد --}}
                    <th class="text-center" style="width: 40px;">
                        <input type="checkbox" id="selectAllReadings" class="form-check-input" title="تحديد الكل">
                    </th>
                    <th class="fw-semibold text-center" style="width: 50px;">#</th>
                    <th class="fw-semibold">رقم القراءة</th>
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
                        <i class="bi bi-activity me-1"></i>
                        الحالة
                    </th>
                    <th class="text-center fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i>
                        إجراء القراءة
                    </th>
                    <th class="text-end fw-semibold">
                        <i class="bi bi-gear me-1"></i>
                        الإجراءات
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($meterReadings as $reading)
                    @php
                        $editable = $reading->isEditable();
                        $isAbnormal = $reading->isAbnormal();
                        $actionStatusClass = match($reading->action_status) {
                            0 => 'bg-secondary',
                            1 => 'bg-success',
                            2 => 'bg-info',
                            default => 'bg-secondary',
                        };
                        $readingStatusClass = match($reading->reading_status) {
                            1 => 'bg-success',
                            2 => 'bg-warning text-dark',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <tr class="{{ $isAbnormal && $editable ? 'table-warning' : '' }}">
                        {{-- خانة التحديد: تظهر للقراءات الطبيعية غير المعتمدة فقط --}}
                        <td class="text-center">
                            @if($editable && !$isAbnormal)
                                <input type="checkbox" class="form-check-input reading-checkbox" value="{{ $reading->id }}">
                            @elseif($editable && $isAbnormal)
                                <span class="text-warning fs-6" title="قراءة غير طبيعية — تحتاج اعتماداً منفرداً مع ذكر السبب"><i class="bi bi-exclamation-triangle-fill"></i></span>
                            @else
                                <span class="text-muted" title="{{ $reading->action_status_name }}">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ ($meterReadings->currentPage() - 1) * $meterReadings->perPage() + $loop->iteration }}</span>
                        </td>
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
                            <span class="badge {{ $reading->consumption_kwh < 0 ? 'bg-danger' : 'bg-info' }} px-3 py-2">
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
                            <span class="badge {{ $readingStatusClass }} px-2 py-1">
                                @if($isAbnormal)<i class="bi bi-exclamation-triangle-fill me-1"></i>@endif
                                {{ $reading->reading_status_name }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $actionStatusClass }} px-2 py-1">
                                {{ $reading->action_status_name }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                @can('view', $reading)
                                    <a href="{{ route('admin.meter-readings.show', $reading) }}" class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan

                                @if($editable && $isAbnormal)
                                    @can('create', App\Models\MeterReading::class)
                                        <button type="button"
                                            class="btn btn-sm btn-warning approve-abnormal-btn"
                                            title="اعتماد قراءة غير طبيعية"
                                            data-id="{{ $reading->id }}"
                                            data-number="{{ $reading->reading_number }}"
                                            data-url="{{ route('admin.meter-readings.approve-abnormal', $reading) }}">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                            اعتماد
                                        </button>
                                    @endcan
                                @endif

                                @if($editable)
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
                                @else
                                    {{-- قراءة معتمدة أو مفوترة: إخفاء أزرار التعديل والحذف --}}
                                    @can('update', $reading)
                                        <button class="btn btn-sm btn-outline-secondary" disabled title="لا يمكن تعديل قراءة {{ $reading->action_status_name }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($meterReadings->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $meterReadings->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5 empty-state">
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


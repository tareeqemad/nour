@if($fuelEfficiencies->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>رقم المولد</th>
                    <th>تاريخ الاستهلاك</th>
                    <th>ساعات التشغيل</th>
                    <th>كفاءة الوقود</th>
                    <th>كفاءة الطاقة</th>
                    <th>التكلفة الإجمالية</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fuelEfficiencies as $efficiency)
                    <tr>
                        <td>{{ $efficiency->id }}</td>
                        <td>
                            @if($efficiency->generator)
                                <span class="badge bg-secondary">{{ $efficiency->generator->generator_number }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
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

    @if($fuelEfficiencies->hasPages())
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



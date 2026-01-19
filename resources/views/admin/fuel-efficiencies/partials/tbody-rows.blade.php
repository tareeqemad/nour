@foreach($fuelEfficiencies as $efficiency)
    <tr>
        <td>{{ $fuelEfficiencies->firstItem() + $loop->index }}</td>
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


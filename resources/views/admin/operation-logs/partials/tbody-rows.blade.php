@foreach($operationLogs as $log)
    <tr>
        <td>{{ $log->id }}</td>
        <td>
            @if($log->generator)
                <span class="badge bg-secondary">{{ $log->generator->generator_number }}</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>{{ $log->operation_date->format('Y-m-d') }}</td>
        <td>
            @if($log->start_time && $log->end_time)
                {{ \Carbon\Carbon::parse($log->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($log->end_time)->format('H:i') }}
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @if($log->operating_hours)
                {{ number_format($log->operating_hours, 2) }} ساعة
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @if($log->operator)
                <span class="badge bg-info">{{ $log->operator->name }}</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            <div class="log-row-actions">
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
                    <button type="button" class="btn btn-xs btn-outline-danger operation-log-delete-btn" 
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




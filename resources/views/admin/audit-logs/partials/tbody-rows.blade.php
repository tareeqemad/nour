@forelse($logs as $log)
    <tr>
        <td>{{ $logs->firstItem() + $loop->index }}</td>
        <td>
            @if($log->user)
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $log->user->name }}</div>
                        <small class="text-muted">{{ $log->user->username }}</small>
                    </div>
                </div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @php
                $actionColors = [
                    'create' => 'success',
                    'update' => 'primary',
                    'delete' => 'danger',
                    'view' => 'info',
                    'login' => 'success',
                    'logout' => 'secondary',
                ];
                $actionLabels = [
                    'create' => 'إنشاء',
                    'update' => 'تحديث',
                    'delete' => 'حذف',
                    'view' => 'عرض',
                    'login' => 'دخول',
                    'logout' => 'خروج',
                ];
                $color = $actionColors[$log->action] ?? 'secondary';
                $label = $actionLabels[$log->action] ?? $log->action;
            @endphp
            <span class="badge bg-{{ $color }}">{{ $label }}</span>
        </td>
        <td>
            @if($log->description)
                <div class="text-truncate" style="max-width: 300px;" title="{{ $log->description }}">
                    {{ $log->description }}
                </div>
            @elseif($log->model_type)
                <small class="text-muted">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</small>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        @if(auth()->user()->isSuperAdmin())
            <td>
                @if($log->model_type)
                    <small class="text-muted">{{ class_basename($log->model_type) }}</small>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
        @endif
        <td>
            <div>
                <div>{{ $log->created_at->format('Y-m-d') }}</div>
                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
            </div>
        </td>
        <td class="text-center">
            <a href="{{ route('admin.activity-logs.show', $log) }}" class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                <i class="bi bi-eye"></i>
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="{{ auth()->user()->isSuperAdmin() ? '7' : '6' }}" class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            لا توجد سجلات نشاطات
        </td>
    </tr>
@endforelse




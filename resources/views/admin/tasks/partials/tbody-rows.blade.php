@forelse($tasks as $task)
    <tr>
        <td>
            <span class="badge badge-type-{{ $task->type }}">
                {{ $task->type_label }}
            </span>
        </td>
        <td>
            <span class="fw-semibold">{{ $task->assignedTo->name }}</span>
            <br>
            <small class="text-muted">{{ $task->assignedTo->getRoleLabel() }}</small>
        </td>
        <td>
            <span class="fw-semibold">{{ $task->operator->name }}</span>
        </td>
        <td>
            @if($task->generationUnit)
                <span class="fw-semibold">{{ $task->generationUnit->name }}</span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @if($task->generator)
                <span class="fw-semibold">{{ $task->generator->name }}</span>
                <br>
                <small class="text-muted">{{ $task->generator->generator_number }}</small>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td class="text-center">
            <span class="badge badge-status-{{ $task->status }}">
                {{ $task->status_label }}
            </span>
        </td>
        <td>
            @if($task->due_date)
                <span class="fw-semibold">{{ $task->due_date->format('Y-m-d') }}</span>
                @if($task->due_date->isPast() && $task->status !== 'completed')
                    <br>
                    <small class="text-danger">متأخرة</small>
                @endif
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            <small class="text-muted">
                {{ $task->created_at->format('Y-m-d H:i') }}
            </small>
        </td>
        <td class="text-center">
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('admin.tasks.show', $task) }}" 
                   class="btn btn-sm btn-outline-primary" title="عرض التفاصيل">
                    <i class="bi bi-eye"></i>
                </a>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority())
                    <button type="button" class="btn btn-sm btn-outline-danger task-delete-btn" 
                            data-task-id="{{ $task->id }}"
                            data-task-type="{{ $task->type_label }}"
                            title="حذف">
                        <i class="bi bi-trash"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            @if(request('search') || request('type') || request('status') || request('assigned_to'))
                لا توجد نتائج للبحث
            @else
                لا توجد مهام
            @endif
        </td>
    </tr>
@endforelse

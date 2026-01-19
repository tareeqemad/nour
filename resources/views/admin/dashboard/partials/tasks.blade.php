{{-- قسم المهام للفني والدفاع المدني --}}
@php
    use Illuminate\Support\Str;
@endphp
@if(isset($tasksData) && isset($tasksData['tasks']) && $tasksData['tasks']->count() > 0)
<div class="row g-3 mt-3">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-clipboard-check me-2"></i>
                        مهامي
                    </h5>
                    <p class="dashboard-card-subtitle">آخر المهام المكلف بها</p>
                </div>
                <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-primary btn-sm">
                    عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                </a>
            </div>
            <div class="dashboard-card-body p-0">
                <div class="dashboard-list-container">
                    @foreach($tasksData['tasks'] as $task)
                        <div class="dashboard-list-item">
                            <div class="dashboard-list-item-icon">
                                @if($task->type === 'maintenance')
                                    <i class="bi bi-tools text-warning"></i>
                                @else
                                    <i class="bi bi-shield-check text-info"></i>
                                @endif
                            </div>
                            <div class="dashboard-list-item-content">
                                <h6 class="dashboard-list-item-title">
                                    {{ $task->type_label }}
                                    @if($task->generator)
                                        - {{ $task->generator->name }}
                                    @elseif($task->operator)
                                        - {{ $task->operator->name }}
                                    @endif
                                </h6>
                                <div class="dashboard-list-item-meta">
                                    <span class="badge badge-{{ $task->status === 'pending' ? 'warning' : ($task->status === 'in_progress' ? 'info' : ($task->status === 'completed' ? 'success' : 'secondary')) }}">
                                        {{ $task->status_label }}
                                    </span>
                                    @if($task->operator)
                                        <span class="dashboard-list-item-text">
                                            <i class="bi bi-building me-1"></i>
                                            {{ $task->operator->name }}
                                        </span>
                                    @endif
                                    @if($task->assignedBy)
                                        <span class="dashboard-list-item-text">
                                            <i class="bi bi-person me-1"></i>
                                            من: {{ $task->assignedBy->name }}
                                        </span>
                                    @endif
                                </div>
                                @if($task->description)
                                    <p class="dashboard-list-item-text mb-1">{{ Str::limit($task->description, 100) }}</p>
                                @endif
                                <small class="dashboard-list-item-time">
                                    @if($task->due_date)
                                        <i class="bi bi-calendar-event me-1"></i>
                                        تاريخ الاستحقاق: {{ $task->due_date->format('Y-m-d') }}
                                        @if($task->due_date->lt(now()) && $task->status !== 'completed')
                                            <span class="text-danger ms-2">(متأخرة)</span>
                                        @endif
                                        <span class="mx-2">|</span>
                                    @endif
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $task->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <div class="dashboard-list-item-action">
                                <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary">
                                    عرض التفاصيل
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

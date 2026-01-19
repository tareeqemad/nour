{{-- إحصائيات الدفاع المدني --}}
@if(isset($tasksData) && isset($tasksData['stats']))
    <div class="col-lg-3 col-md-6">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-2 small">إجمالي المهام</h6>
                        <h3 class="mb-0 text-primary">{{ number_format($tasksData['stats']['total'] ?? 0) }}</h3>
                    </div>
                    <div class="avatar avatar-md bg-primary-transparent">
                        <i class="bi bi-clipboard-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-2 small">قيد الانتظار</h6>
                        <h3 class="mb-0 text-warning">{{ number_format($tasksData['stats']['pending'] ?? 0) }}</h3>
                    </div>
                    <div class="avatar avatar-md bg-warning-transparent">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-2 small">قيد التنفيذ</h6>
                        <h3 class="mb-0 text-info">{{ number_format($tasksData['stats']['in_progress'] ?? 0) }}</h3>
                    </div>
                    <div class="avatar avatar-md bg-info-transparent">
                        <i class="bi bi-arrow-repeat fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-2 small">مكتملة</h6>
                        <h3 class="mb-0 text-success">{{ number_format($tasksData['stats']['completed'] ?? 0) }}</h3>
                    </div>
                    <div class="avatar avatar-md bg-success-transparent">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($tasksData['stats']['overdue']) && $tasksData['stats']['overdue'] > 0)
    <div class="col-lg-3 col-md-6">
        <div class="card border-danger">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-2 small">متأخرة</h6>
                        <h3 class="mb-0 text-danger">{{ number_format($tasksData['stats']['overdue']) }}</h3>
                    </div>
                    <div class="avatar avatar-md bg-danger-transparent">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endif

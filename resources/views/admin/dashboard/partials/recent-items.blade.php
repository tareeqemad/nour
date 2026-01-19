<!-- Recent Items & Alerts - Unified with Tabs -->
<div class="row g-3">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-clock-history me-2"></i>
                        العناصر الأخيرة
                    </h5>
                    <p class="dashboard-card-subtitle">نظرة سريعة على آخر الأنشطة والعناصر</p>
                </div>
            </div>
            <div class="dashboard-card-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs nav-tabs-custom mb-3" id="recentItemsTab" role="tablist">
                    @if(isset($generatorsNeedingMaintenance) && $generatorsNeedingMaintenance->count() > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance-content" type="button" role="tab">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            مولدات تحتاج صيانة
                            @if($generatorsNeedingMaintenance->count() > 0)
                                <span class="badge bg-warning ms-1">{{ $generatorsNeedingMaintenance->count() }}</span>
                            @endif
                        </button>
                    </li>
                    @endif
                    @if(isset($recentOperationLogs) && $recentOperationLogs->count() > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ (!isset($generatorsNeedingMaintenance) || $generatorsNeedingMaintenance->count() == 0) ? 'active' : '' }}" id="operation-logs-tab" data-bs-toggle="tab" data-bs-target="#operation-logs-content" type="button" role="tab">
                            <i class="bi bi-journal-text me-1"></i>
                            آخر سجلات التشغيل
                        </button>
                    </li>
                    @endif
                    @if(isset($recentGenerators) && $recentGenerators->count() > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ ((!isset($generatorsNeedingMaintenance) || $generatorsNeedingMaintenance->count() == 0) && (!isset($recentOperationLogs) || $recentOperationLogs->count() == 0)) ? 'active' : '' }}" id="recent-generators-tab" data-bs-toggle="tab" data-bs-target="#recent-generators-content" type="button" role="tab">
                            <i class="bi bi-lightning-charge-fill me-1"></i>
                            آخر المولدات
                        </button>
                    </li>
                    @endif
                    @if((auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) && isset($recentOperators) && $recentOperators->count() > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ ((!isset($generatorsNeedingMaintenance) || $generatorsNeedingMaintenance->count() == 0) && (!isset($recentOperationLogs) || $recentOperationLogs->count() == 0) && (!isset($recentGenerators) || $recentGenerators->count() == 0)) ? 'active' : '' }}" id="recent-operators-tab" data-bs-toggle="tab" data-bs-target="#recent-operators-content" type="button" role="tab">
                            <i class="bi bi-building me-1"></i>
                            آخر المشغلين
                        </button>
                    </li>
                    @endif
                    @if(isset($expiringCompliance) && $expiringCompliance->count() > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ ((!isset($generatorsNeedingMaintenance) || $generatorsNeedingMaintenance->count() == 0) && (!isset($recentOperationLogs) || $recentOperationLogs->count() == 0) && (!isset($recentGenerators) || $recentGenerators->count() == 0) && (!(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) || !isset($recentOperators) || $recentOperators->count() == 0)) ? 'active' : '' }}" id="expiring-compliance-tab" data-bs-toggle="tab" data-bs-target="#expiring-compliance-content" type="button" role="tab">
                            <i class="bi bi-shield-exclamation me-1"></i>
                            شهادات منتهية أو قريبة من الانتهاء
                            <span class="badge bg-danger ms-1">{{ $expiringCompliance->count() }}</span>
                        </button>
                    </li>
                    @endif
                    @if((auth()->user()->isTechnician() || auth()->user()->isCivilDefense()) && isset($tasksData) && isset($tasksData['tasks']) && $tasksData['tasks']->count() > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ ((!isset($generatorsNeedingMaintenance) || $generatorsNeedingMaintenance->count() == 0) && (!isset($recentOperationLogs) || $recentOperationLogs->count() == 0) && (!isset($recentGenerators) || $recentGenerators->count() == 0) && (!(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) || !isset($recentOperators) || $recentOperators->count() == 0) && (!isset($expiringCompliance) || $expiringCompliance->count() == 0)) ? 'active' : '' }}" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks-content" type="button" role="tab">
                            <i class="bi bi-clipboard-check me-1"></i>
                            مهامي
                            <span class="badge bg-primary ms-1">{{ $tasksData['tasks']->count() }}</span>
                        </button>
                    </li>
                    @endif
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="recentItemsTabContent">
                    @if(isset($generatorsNeedingMaintenance) && $generatorsNeedingMaintenance->count() > 0)
                    <!-- Maintenance Tab -->
                    <div class="tab-pane fade show active" id="maintenance-content" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="text-muted mb-0">مولدات تحتاج إلى صيانة فورية</p>
                            <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-outline-warning btn-sm">
                                عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                            </a>
                        </div>
                        <div class="dashboard-list-container">
                            @foreach($generatorsNeedingMaintenance as $generator)
                                <div class="dashboard-list-item">
                                    <div class="dashboard-list-item-icon">
                                        <i class="bi bi-exclamation-circle text-danger"></i>
                                    </div>
                                    <div class="dashboard-list-item-content">
                                        <h6 class="dashboard-list-item-title">{{ $generator->name }}</h6>
                                        <div class="dashboard-list-item-meta">
                                            @if($generator->generationUnit && $generator->generationUnit->operator && $generator->generationUnit->operator->name)
                                                <span class="dashboard-list-item-text">
                                                    <i class="bi bi-building me-1"></i>
                                                    {{ $generator->generationUnit->operator->name }}
                                                </span>
                                            @endif
                                        </div>
                                        <small class="dashboard-list-item-time">
                                            @if($generator->last_major_maintenance_date)
                                                <i class="bi bi-calendar-x me-1"></i>
                                                آخر صيانة: {{ $generator->last_major_maintenance_date->diffForHumans() }}
                                            @else
                                                <span class="text-danger">
                                                    <i class="bi bi-exclamation-circle me-1"></i>
                                                    لم يتم تسجيل صيانة
                                                </span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(isset($recentOperationLogs) && $recentOperationLogs->count() > 0)
                    <!-- Operation Logs Tab -->
                    <div class="tab-pane fade {{ (!isset($generatorsNeedingMaintenance) || $generatorsNeedingMaintenance->count() == 0) ? 'show active' : '' }}" id="operation-logs-content" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="text-muted mb-0">آخر السجلات المسجلة</p>
                            <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-outline-primary btn-sm">
                                عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                            </a>
                        </div>
                        <div class="dashboard-list-container">
                            @foreach($recentOperationLogs as $log)
                                <div class="dashboard-list-item">
                                    <div class="dashboard-list-item-icon">
                                        <i class="bi bi-lightning-charge text-success"></i>
                                    </div>
                                    <div class="dashboard-list-item-content">
                                        <h6 class="dashboard-list-item-title">
                                            {{ $log->generator->name ?? 'مولد محذوف' }}
                                            @if($log->generator && $log->generator->trashed())
                                                <span class="badge bg-secondary ms-1" title="مولد محذوف">محذوف</span>
                                            @endif
                                        </h6>
                                        <div class="dashboard-list-item-meta">
                                            @if($log->energy_produced)
                                                <span class="badge badge-success">{{ number_format($log->energy_produced, 2) }} kWh</span>
                                            @endif
                                            @if($log->operator && $log->operator->name)
                                                <span class="dashboard-list-item-text">
                                                    <i class="bi bi-building me-1"></i>
                                                    {{ $log->operator->name }}
                                                </span>
                                            @endif
                                        </div>
                                        <small class="dashboard-list-item-time">
                                            <i class="bi bi-calendar me-1"></i>
                                            {{ $log->operation_date->format('Y-m-d') }}
                                            <span class="mx-2">|</span>
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $log->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(isset($recentGenerators) && $recentGenerators->count() > 0)
                    <!-- Recent Generators Tab -->
                    <div class="tab-pane fade {{ ((!isset($generatorsNeedingMaintenance) || $generatorsNeedingMaintenance->count() == 0) && (!isset($recentOperationLogs) || $recentOperationLogs->count() == 0)) ? 'show active' : '' }}" id="recent-generators-content" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="text-muted mb-0">آخر المولدات المضافة للنظام</p>
                            <a href="{{ route('admin.generators.index') }}" class="btn btn-outline-primary btn-sm">
                                عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                            </a>
                        </div>
                        <div class="dashboard-list-container">
                            @forelse($recentGenerators as $generator)
                                <div class="dashboard-list-item">
                                    <div class="dashboard-list-item-icon">
                                        <i class="bi bi-lightning-charge-fill text-success"></i>
                                    </div>
                                    <div class="dashboard-list-item-content">
                                        <h6 class="dashboard-list-item-title">{{ $generator->name }}</h6>
                                        <div class="dashboard-list-item-meta">
                                            <span class="badge badge-{{ $generator->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ $generator->status === 'active' ? 'نشط' : 'غير نشط' }}
                                            </span>
                                            @if($generator->generationUnit && $generator->generationUnit->operator && $generator->generationUnit->operator->name)
                                                <span class="dashboard-list-item-text">
                                                    <i class="bi bi-building me-1"></i>
                                                    {{ $generator->generationUnit->operator->name }}
                                                </span>
                                            @endif
                                        </div>
                                        <small class="dashboard-list-item-time">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $generator->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @empty
                                <div class="dashboard-empty-state">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p>لا توجد مولدات</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @endif

                    @if((auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) && isset($recentOperators) && $recentOperators->count() > 0)
                    <!-- Recent Operators Tab -->
                    <div class="tab-pane fade {{ ((!isset($generatorsNeedingMaintenance) || $generatorsNeedingMaintenance->count() == 0) && (!isset($recentOperationLogs) || $recentOperationLogs->count() == 0) && (!isset($recentGenerators) || $recentGenerators->count() == 0)) ? 'show active' : '' }}" id="recent-operators-content" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="text-muted mb-0">آخر المشغلين المسجلين</p>
                            <a href="{{ route('admin.operators.index') }}" class="btn btn-outline-primary btn-sm">
                                عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                            </a>
                        </div>
                        <div class="dashboard-list-container">
                            @forelse($recentOperators as $operator)
                                <div class="dashboard-list-item">
                                    <div class="dashboard-list-item-icon">
                                        <i class="bi bi-building text-info"></i>
                                    </div>
                                    <div class="dashboard-list-item-content">
                                        <h6 class="dashboard-list-item-title">{{ $operator->name }}</h6>
                                        <div class="dashboard-list-item-meta">
                                            @if($operator->owner && $operator->owner->name)
                                                <span class="dashboard-list-item-text">
                                                    <i class="bi bi-person me-1"></i>
                                                    {{ $operator->owner->name }}
                                                </span>
                                            @endif
                                        </div>
                                        <small class="dashboard-list-item-time">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $operator->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @empty
                                <div class="dashboard-empty-state">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p>لا توجد مشغلين</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @endif

                    @if(isset($expiringCompliance) && $expiringCompliance->count() > 0)
                    <!-- Expiring Compliance Tab -->
                    <div class="tab-pane fade {{ ((!isset($generatorsNeedingMaintenance) || $generatorsNeedingMaintenance->count() == 0) && (!isset($recentOperationLogs) || $recentOperationLogs->count() == 0) && (!isset($recentGenerators) || $recentGenerators->count() == 0) && (!(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) || !isset($recentOperators) || $recentOperators->count() == 0)) ? 'show active' : '' }}" id="expiring-compliance-content" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="text-muted mb-0">شهادات تحتاج إلى متابعة فورية</p>
                            <a href="{{ route('admin.compliance-safeties.index') }}" class="btn btn-outline-danger btn-sm">
                                عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                            </a>
                        </div>
                        <div class="dashboard-list-container">
                            @foreach($expiringCompliance as $compliance)
                                <div class="dashboard-list-item">
                                    <div class="dashboard-list-item-icon">
                                        <i class="bi bi-shield-exclamation text-danger"></i>
                                    </div>
                                    <div class="dashboard-list-item-content">
                                        <h6 class="dashboard-list-item-title">{{ $compliance->operator->name ?? '-' }}</h6>
                                        <div class="dashboard-list-item-meta">
                                            @php
                                                $statusCode = $compliance->safetyCertificateStatusDetail->code ?? '';
                                                $isExpired = $statusCode === 'EXPIRED';
                                            @endphp
                                            <span class="badge badge-{{ $isExpired ? 'danger' : 'warning' }}">
                                                {{ $isExpired ? 'منتهية' : 'قريبة من الانتهاء' }}
                                            </span>
                                            @if($compliance->inspection_authority)
                                                <span class="dashboard-list-item-text">
                                                    <i class="bi bi-building me-1"></i>
                                                    {{ $compliance->inspection_authority }}
                                                </span>
                                            @endif
                                        </div>
                                        <small class="dashboard-list-item-time">
                                            @if($compliance->last_inspection_date)
                                                <i class="bi bi-calendar-x me-1"></i>
                                                آخر فحص: {{ $compliance->last_inspection_date->format('Y-m-d') }}
                                                @if($compliance->last_inspection_date->lt(now()->subMonths(6)))
                                                    <span class="text-danger ms-2">(منذ {{ $compliance->last_inspection_date->diffForHumans() }})</span>
                                                @endif
                                            @else
                                                <span class="text-danger">
                                                    <i class="bi bi-exclamation-circle me-1"></i>
                                                    لم يتم تسجيل فحص
                                                </span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif


                    @if((auth()->user()->isTechnician() || auth()->user()->isCivilDefense()) && isset($tasksData) && isset($tasksData['tasks']) && $tasksData['tasks']->count() > 0)
                    <!-- Tasks Tab -->
                    <div class="tab-pane fade {{ ((!isset($generatorsNeedingMaintenance) || $generatorsNeedingMaintenance->count() == 0) && (!isset($recentOperationLogs) || $recentOperationLogs->count() == 0) && (!isset($recentGenerators) || $recentGenerators->count() == 0) && (!(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) || !isset($recentOperators) || $recentOperators->count() == 0) && (!isset($expiringCompliance) || $expiringCompliance->count() == 0)) ? 'show active' : '' }}" id="tasks-content" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="text-muted mb-0">آخر المهام المكلف بها</p>
                            <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-primary btn-sm">
                                عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                            </a>
                        </div>
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
                                            @if($task->generator && $task->generator->name)
                                                - {{ $task->generator->name }}
                                            @elseif($task->operator && $task->operator->name)
                                                - {{ $task->operator->name }}
                                            @endif
                                        </h6>
                                        <div class="dashboard-list-item-meta">
                                            <span class="badge badge-{{ $task->status === 'pending' ? 'warning' : ($task->status === 'in_progress' ? 'info' : ($task->status === 'completed' ? 'success' : 'secondary')) }}">
                                                {{ $task->status_label }}
                                            </span>
                                            @if($task->operator && $task->operator->name)
                                                <span class="dashboard-list-item-text">
                                                    <i class="bi bi-building me-1"></i>
                                                    {{ $task->operator->name }}
                                                </span>
                                            @endif
                                        </div>
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
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Recent Items -->
<div class="row g-3 mt-3">
    @if(isset($expiringCompliance) && $expiringCompliance->count() > 0)
        <!-- Expiring Compliance -->
        <div class="col-12 col-lg-6">
            <div class="dashboard-card dashboard-card-danger">
                <div class="dashboard-card-header">
                    <div>
                        <h5 class="dashboard-card-title text-danger">
                            <i class="bi bi-shield-exclamation me-2"></i>
                            شهادات منتهية أو قريبة من الانتهاء
                        </h5>
                        <p class="dashboard-card-subtitle">شهادات تحتاج إلى متابعة</p>
                    </div>
                    <a href="{{ route('admin.compliance-safeties.index') }}" class="btn btn-outline-danger btn-sm">
                        عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </div>
                <div class="dashboard-card-body p-0">
                    <div class="dashboard-list-container">
                        @foreach($expiringCompliance as $compliance)
                            <div class="dashboard-list-item">
                                <div class="dashboard-list-item-icon">
                                    <i class="bi bi-shield-exclamation text-danger"></i>
                                </div>
                                <div class="dashboard-list-item-content">
                                    <h6 class="dashboard-list-item-title">{{ $compliance->operator->name ?? '-' }}</h6>
                                    <div class="dashboard-list-item-meta">
                                        @php
                                            $statusCode = $compliance->safetyCertificateStatusDetail->code ?? '';
                                            $isExpired = $statusCode === 'EXPIRED';
                                        @endphp
                                        <span class="badge badge-{{ $isExpired ? 'danger' : 'warning' }}">
                                            {{ $isExpired ? 'منتهية' : 'قريبة من الانتهاء' }}
                                        </span>
                                    </div>
                                    <small class="dashboard-list-item-time">
                                        @if($compliance->last_inspection_date)
                                            <i class="bi bi-calendar-x me-1"></i>
                                            آخر فحص: {{ $compliance->last_inspection_date->format('Y-m-d') }}
                                            @if($compliance->last_inspection_date->lt(now()->subMonths(6)))
                                                <span class="text-danger ms-2">(منذ {{ $compliance->last_inspection_date->diffForHumans() }})</span>
                                            @endif
                                        @else
                                            <span class="text-danger">
                                                <i class="bi bi-exclamation-circle me-1"></i>
                                                لم يتم تسجيل فحص
                                            </span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>




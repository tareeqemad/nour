<!-- Quick Actions - للمشغل -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-lightning-charge me-2"></i>
                        إجراءات سريعة
                    </h5>
                    <p class="dashboard-card-subtitle">وصول سريع للصفحات المهمة</p>
                </div>
            </div>
            <div class="dashboard-card-body">
                <div class="dashboard-quick-actions">
                    <a href="{{ route('admin.generation-units.create') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-primary">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">إضافة وحدة توليد</div>
                            <div class="dashboard-quick-action-desc">تسجيل وحدة توليد جديدة</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.generators.create') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-success">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">إضافة مولد</div>
                            <div class="dashboard-quick-action-desc">تسجيل مولد جديد</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.operation-logs.create') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-warning">
                            <i class="bi bi-journal-plus"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجل تشغيل</div>
                            <div class="dashboard-quick-action-desc">إضافة سجل جديد</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.maintenance-records.create') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-danger">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجل صيانة</div>
                            <div class="dashboard-quick-action-desc">تسجيل عملية صيانة</div>
                        </div>
                    </a>
                    @php
                        $operator = auth()->user()->ownedOperators->first();
                    @endphp
                    @if($operator)
                        <a href="{{ route('admin.operators.employees', $operator) }}" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon bg-info">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="dashboard-quick-action-text">
                                <div class="dashboard-quick-action-title">إدارة الموظفين</div>
                                <div class="dashboard-quick-action-desc">عرض وإدارة الموظفين</div>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>





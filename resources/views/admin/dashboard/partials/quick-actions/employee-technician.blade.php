<!-- Quick Actions - للموظف والفني -->
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
                    <a href="{{ route('admin.operation-logs.create') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-warning">
                            <i class="bi bi-journal-plus"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجل تشغيل</div>
                            <div class="dashboard-quick-action-desc">إضافة سجل تشغيل جديد</div>
                        </div>
                    </a>
                    @if(auth()->user()->isTechnician())
                        <a href="{{ route('admin.maintenance-records.create') }}" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon bg-danger">
                                <i class="bi bi-tools"></i>
                            </div>
                            <div class="dashboard-quick-action-text">
                                <div class="dashboard-quick-action-title">سجل صيانة</div>
                                <div class="dashboard-quick-action-desc">تسجيل عملية صيانة</div>
                            </div>
                        </a>
                    @endif
                    <a href="{{ route('admin.generators.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-success">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">المولدات</div>
                            <div class="dashboard-quick-action-desc">عرض المولدات المرتبطة</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.operation-logs.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-info">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجلات التشغيل</div>
                            <div class="dashboard-quick-action-desc">عرض جميع السجلات</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>





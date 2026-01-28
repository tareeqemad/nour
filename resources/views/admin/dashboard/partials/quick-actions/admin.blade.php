<!-- Quick Actions - للأدمن (سلطة الطاقة) -->
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
                    @can('viewAny', App\Models\Operator::class)
                    <a href="{{ route('admin.operators.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-info">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">المشغلون</div>
                            <div class="dashboard-quick-action-desc">عرض جميع المشغلين</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\Generator::class)
                    <a href="{{ route('admin.generators.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-success">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">المولدات</div>
                            <div class="dashboard-quick-action-desc">عرض جميع المولدات</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\OperationLog::class)
                    <a href="{{ route('admin.operation-logs.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-warning">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجلات التشغيل</div>
                            <div class="dashboard-quick-action-desc">عرض جميع السجلات</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\ComplianceSafety::class)
                    <a href="{{ route('admin.compliance-safeties.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-danger">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">الامتثال والسلامة</div>
                            <div class="dashboard-quick-action-desc">عرض الشهادات والامتثال</div>
                        </div>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>





<!-- Quick Actions - لسلطة الطاقة -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-lightning-charge me-2"></i>
                        إجراءات سريعة - سلطة الطاقة
                    </h5>
                    <p class="dashboard-card-subtitle">إدارة ومراقبة قطاع الطاقة</p>
                </div>
            </div>
            <div class="dashboard-card-body">
                <div class="dashboard-quick-actions">
                    <a href="{{ route('admin.operators.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-info">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">المشغلون</div>
                            <div class="dashboard-quick-action-desc">إدارة ومراقبة المشغلين</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.generation-units.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-primary">
                            <i class="bi bi-diagram-3-fill"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">وحدات التوليد</div>
                            <div class="dashboard-quick-action-desc">عرض جميع وحدات التوليد</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.generators.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-success">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">المولدات</div>
                            <div class="dashboard-quick-action-desc">عرض جميع المولدات</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.operation-logs.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-warning">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجلات التشغيل</div>
                            <div class="dashboard-quick-action-desc">متابعة الإنتاج والاستهلاك</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.fuel-efficiencies.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-secondary">
                            <i class="bi bi-fuel-pump-fill"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">كفاءة الوقود</div>
                            <div class="dashboard-quick-action-desc">تحليل استهلاك الوقود</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.compliance-safeties.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-danger">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">الامتثال والسلامة</div>
                            <div class="dashboard-quick-action-desc">مراقبة معايير السلامة</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.maintenance-records.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-dark">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجلات الصيانة</div>
                            <div class="dashboard-quick-action-desc">متابعة أعمال الصيانة</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="dashboard-quick-action">
                        <div class="dashboard-quick-action-icon bg-purple">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">المستخدمون</div>
                            <div class="dashboard-quick-action-desc">إدارة المستخدمين</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

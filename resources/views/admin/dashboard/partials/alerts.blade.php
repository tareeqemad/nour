<!-- Important Alerts -->
@if((isset($generatorsNeedingMaintenance) && $generatorsNeedingMaintenance->count() > 0) || 
    (isset($expiringCompliance) && $expiringCompliance->count() > 0))
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dashboard-alerts">
            <div class="dashboard-alerts-header">
                <h5 class="dashboard-alerts-title">
                    <i class="bi bi-bell-fill me-2"></i>
                    تنبيهات مهمة
                </h5>
            </div>
            <div class="dashboard-alerts-body">
                @if(isset($generatorsNeedingMaintenance) && $generatorsNeedingMaintenance->count() > 0)
                    <div class="dashboard-alert-item dashboard-alert-warning">
                        <div class="dashboard-alert-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div class="dashboard-alert-content">
                            <div class="dashboard-alert-title">مولدات تحتاج صيانة</div>
                            <div class="dashboard-alert-desc">{{ $generatorsNeedingMaintenance->count() }} مولد يحتاج إلى صيانة فورية</div>
                        </div>
                        <a href="{{ route('admin.maintenance-records.index') }}" class="dashboard-alert-action">
                            عرض <i class="bi bi-arrow-left ms-1"></i>
                        </a>
                    </div>
                @endif


                @if(isset($expiringCompliance) && $expiringCompliance->count() > 0)
                    <div class="dashboard-alert-item dashboard-alert-danger">
                        <div class="dashboard-alert-icon">
                            <i class="bi bi-shield-exclamation"></i>
                        </div>
                        <div class="dashboard-alert-content">
                            <div class="dashboard-alert-title">شهادات منتهية أو قريبة من الانتهاء</div>
                            <div class="dashboard-alert-desc">{{ $expiringCompliance->count() }} شهادة تحتاج إلى متابعة</div>
                        </div>
                        <a href="{{ route('admin.compliance-safeties.index') }}" class="dashboard-alert-action">
                            عرض <i class="bi bi-arrow-left ms-1"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif





@if(auth()->user()->isTechnician() && isset($maintenanceStats) && $maintenanceStats['total'] > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <x-admin.card>
            <x-admin.card-header title="إحصائيات الصيانة" icon="bi-tools">
                <x-slot:actions>
                    <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-outline-primary btn-sm">
                        عرض التفاصيل <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </x-slot:actions>
            </x-admin.card-header>
            <div class="p-3">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="d-kpi d-kpi-sm">
                            <div class="d-kpi-body">
                                <span class="d-kpi-label">إجمالي السجلات</span>
                                <span class="d-kpi-value">{{ number_format($maintenanceStats['total']) }}</span>
                                <div class="d-kpi-tags" style="justify-content:center;">
                                    <span class="d-kpi-tag t-warning">{{ $maintenanceStats['this_month'] }} هذا الشهر</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($maintenanceStats['total_cost'] > 0)
                    <div class="col-6 col-md-4">
                        <div class="d-kpi d-kpi-sm">
                            <div class="d-kpi-body">
                                <span class="d-kpi-label">التكلفة الإجمالية</span>
                                <span class="d-kpi-value">{{ number_format($maintenanceStats['total_cost'], 0) }}</span>
                                <span class="d-kpi-unit">₪</span>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($maintenanceStats['total_downtime'] > 0)
                    <div class="col-6 col-md-4">
                        <div class="d-kpi d-kpi-sm">
                            <div class="d-kpi-body">
                                <span class="d-kpi-label">وقت التوقف</span>
                                <span class="d-kpi-value">{{ number_format($maintenanceStats['total_downtime'], 1) }}</span>
                                <span class="d-kpi-unit">ساعة</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </x-admin.card>
    </div>
</div>
@endif

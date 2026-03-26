@php $user = auth()->user(); @endphp

<div class="d-section h-100">
    <div class="d-section-head">
        <span class="d-section-title"><i class="bi bi-lightning-charge me-1"></i> إجراءات سريعة</span>
    </div>
    <div class="d-section-body">
        <div class="dashboard-quick-actions">

@if($user->isSuperAdmin())
                    @can('create', App\Models\Generator::class)
                    <a href="{{ route('admin.generators.create') }}" class="dashboard-quick-action qa-green">
                        <div class="dashboard-quick-action-icon bg-success"><i class="bi bi-lightning-charge-fill"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">إضافة مولد</div>
                            <div class="dashboard-quick-action-desc">تسجيل مولد جديد</div>
                        </div>
                    </a>
                    @endcan
                    @can('create', App\Models\OperationLog::class)
                    <a href="{{ route('admin.operation-logs.create') }}" class="dashboard-quick-action qa-amber">
                        <div class="dashboard-quick-action-icon bg-warning"><i class="bi bi-journal-plus"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجل تشغيل</div>
                            <div class="dashboard-quick-action-desc">إضافة سجل جديد</div>
                        </div>
                    </a>
                    @endcan
                    @can('create', App\Models\MaintenanceRecord::class)
                    <a href="{{ route('admin.maintenance-records.create') }}" class="dashboard-quick-action qa-red">
                        <div class="dashboard-quick-action-icon bg-danger"><i class="bi bi-tools"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجل صيانة</div>
                            <div class="dashboard-quick-action-desc">تسجيل عملية صيانة</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\ElectricityTariffPrice::class)
                    <a href="{{ route('admin.electricity-tariff-prices.index') }}" class="dashboard-quick-action qa-primary">
                        <div class="dashboard-quick-action-icon bg-primary"><i class="bi bi-currency-exchange"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">أسعار التعرفة</div>
                            <div class="dashboard-quick-action-desc">إدارة أسعار التعرفة الكهربائية</div>
                        </div>
                    </a>
                    @endcan

@elseif($user->isAdmin())
                    @can('viewAny', App\Models\Operator::class)
                    <a href="{{ route('admin.operators.index') }}" class="dashboard-quick-action qa-cyan">
                        <div class="dashboard-quick-action-icon bg-info"><i class="bi bi-building"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">المشغلون</div>
                            <div class="dashboard-quick-action-desc">عرض جميع المشغلين</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\Generator::class)
                    <a href="{{ route('admin.generators.index') }}" class="dashboard-quick-action qa-green">
                        <div class="dashboard-quick-action-icon bg-success"><i class="bi bi-lightning-charge-fill"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">المولدات</div>
                            <div class="dashboard-quick-action-desc">عرض جميع المولدات</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\OperationLog::class)
                    <a href="{{ route('admin.operation-logs.index') }}" class="dashboard-quick-action qa-amber">
                        <div class="dashboard-quick-action-icon bg-warning"><i class="bi bi-journal-text"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجلات التشغيل</div>
                            <div class="dashboard-quick-action-desc">عرض جميع السجلات</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\ComplianceSafety::class)
                    <a href="{{ route('admin.compliance-safeties.index') }}" class="dashboard-quick-action qa-red">
                        <div class="dashboard-quick-action-icon bg-danger"><i class="bi bi-shield-check"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">الامتثال والسلامة</div>
                            <div class="dashboard-quick-action-desc">عرض الشهادات والامتثال</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\ElectricityTariffPrice::class)
                    <a href="{{ route('admin.electricity-tariff-prices.index') }}" class="dashboard-quick-action qa-primary">
                        <div class="dashboard-quick-action-icon bg-primary"><i class="bi bi-currency-exchange"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">أسعار التعرفة</div>
                            <div class="dashboard-quick-action-desc">إدارة أسعار التعرفة الكهربائية</div>
                        </div>
                    </a>
                    @endcan

@elseif($user->isEnergyAuthority())
                    @can('viewAny', App\Models\Operator::class)
                    <a href="{{ route('admin.operators.index') }}" class="dashboard-quick-action qa-cyan">
                        <div class="dashboard-quick-action-icon bg-info"><i class="bi bi-building"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">المشغلون</div>
                            <div class="dashboard-quick-action-desc">إدارة ومراقبة المشغلين</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\GenerationUnit::class)
                    <a href="{{ route('admin.generation-units.index') }}" class="dashboard-quick-action qa-primary">
                        <div class="dashboard-quick-action-icon bg-primary"><i class="bi bi-diagram-3-fill"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">وحدات التوليد</div>
                            <div class="dashboard-quick-action-desc">عرض جميع وحدات التوليد</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\Generator::class)
                    <a href="{{ route('admin.generators.index') }}" class="dashboard-quick-action qa-green">
                        <div class="dashboard-quick-action-icon bg-success"><i class="bi bi-lightning-charge-fill"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">المولدات</div>
                            <div class="dashboard-quick-action-desc">عرض جميع المولدات</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\OperationLog::class)
                    <a href="{{ route('admin.operation-logs.index') }}" class="dashboard-quick-action qa-amber">
                        <div class="dashboard-quick-action-icon bg-warning"><i class="bi bi-journal-text"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجلات التشغيل</div>
                            <div class="dashboard-quick-action-desc">متابعة الإنتاج والاستهلاك</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\ComplianceSafety::class)
                    <a href="{{ route('admin.compliance-safeties.index') }}" class="dashboard-quick-action qa-red">
                        <div class="dashboard-quick-action-icon bg-danger"><i class="bi bi-shield-check"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">الامتثال والسلامة</div>
                            <div class="dashboard-quick-action-desc">مراقبة معايير السلامة</div>
                        </div>
                    </a>
                    @endcan
                    @can('viewAny', App\Models\MaintenanceRecord::class)
                    <a href="{{ route('admin.maintenance-records.index') }}" class="dashboard-quick-action qa-dark">
                        <div class="dashboard-quick-action-icon bg-dark"><i class="bi bi-tools"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجلات الصيانة</div>
                            <div class="dashboard-quick-action-desc">متابعة أعمال الصيانة</div>
                        </div>
                    </a>
                    @endcan

@elseif($user->isCompanyOwner())
                    @can('create', App\Models\GenerationUnit::class)
                    <a href="{{ route('admin.generation-units.create') }}" class="dashboard-quick-action qa-primary">
                        <div class="dashboard-quick-action-icon bg-primary"><i class="bi bi-building"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">إضافة وحدة توليد</div>
                            <div class="dashboard-quick-action-desc">تسجيل وحدة توليد جديدة</div>
                        </div>
                    </a>
                    @endcan
                    @can('create', App\Models\Generator::class)
                    <a href="{{ route('admin.generators.create') }}" class="dashboard-quick-action qa-green">
                        <div class="dashboard-quick-action-icon bg-success"><i class="bi bi-lightning-charge-fill"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">إضافة مولد</div>
                            <div class="dashboard-quick-action-desc">تسجيل مولد جديد</div>
                        </div>
                    </a>
                    @endcan
                    @can('create', App\Models\OperationLog::class)
                    <a href="{{ route('admin.operation-logs.create') }}" class="dashboard-quick-action qa-amber">
                        <div class="dashboard-quick-action-icon bg-warning"><i class="bi bi-journal-plus"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجل تشغيل</div>
                            <div class="dashboard-quick-action-desc">إضافة سجل جديد</div>
                        </div>
                    </a>
                    @endcan
                    @can('create', App\Models\MaintenanceRecord::class)
                    <a href="{{ route('admin.maintenance-records.create') }}" class="dashboard-quick-action qa-red">
                        <div class="dashboard-quick-action-icon bg-danger"><i class="bi bi-tools"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجل صيانة</div>
                            <div class="dashboard-quick-action-desc">تسجيل عملية صيانة</div>
                        </div>
                    </a>
                    @php $operator = $user->ownedOperators->first(); @endphp
                    @if($operator)
                    <a href="{{ route('admin.operators.employees', $operator) }}" class="dashboard-quick-action qa-cyan">
                        <div class="dashboard-quick-action-icon bg-info"><i class="bi bi-people"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">إدارة الموظفين</div>
                            <div class="dashboard-quick-action-desc">عرض وإدارة الموظفين</div>
                        </div>
                    </a>
                    @endif
                    @endcan

@elseif($user->isEmployee() || $user->isTechnician() || $user->isCivilDefense())
                    <a href="{{ route('admin.operation-logs.create') }}" class="dashboard-quick-action qa-amber">
                        <div class="dashboard-quick-action-icon bg-warning"><i class="bi bi-journal-plus"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجل تشغيل</div>
                            <div class="dashboard-quick-action-desc">إضافة سجل تشغيل جديد</div>
                        </div>
                    </a>
                    @if($user->isTechnician())
                    <a href="{{ route('admin.maintenance-records.create') }}" class="dashboard-quick-action qa-red">
                        <div class="dashboard-quick-action-icon bg-danger"><i class="bi bi-tools"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجل صيانة</div>
                            <div class="dashboard-quick-action-desc">تسجيل عملية صيانة</div>
                        </div>
                    </a>
                    @endif
                    <a href="{{ route('admin.generators.index') }}" class="dashboard-quick-action qa-green">
                        <div class="dashboard-quick-action-icon bg-success"><i class="bi bi-lightning-charge-fill"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">المولدات</div>
                            <div class="dashboard-quick-action-desc">عرض المولدات المرتبطة</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.operation-logs.index') }}" class="dashboard-quick-action qa-cyan">
                        <div class="dashboard-quick-action-icon bg-info"><i class="bi bi-journal-text"></i></div>
                        <div class="dashboard-quick-action-text">
                            <div class="dashboard-quick-action-title">سجلات التشغيل</div>
                            <div class="dashboard-quick-action-desc">عرض جميع السجلات</div>
                        </div>
                    </a>
@endif

        </div>
    </div>
</div>

@php $user = auth()->user(); @endphp

@if(isset($operationStats) && $operationStats['total'] > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <x-admin.card>
            <x-admin.card-header title="إحصائيات التشغيل" icon="bi-graph-up">
                <x-slot:actions>
                    <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-outline-primary btn-sm">
                        عرض التفاصيل <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </x-slot:actions>
            </x-admin.card-header>
            <div class="p-3">
                @if($user->isEnergyAuthority())
                {{-- Energy Authority: enhanced stats --}}
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="dashboard-stat-mini dashboard-stat-mini-info">
                            <div class="dashboard-stat-mini-icon"><i class="bi bi-journal-text"></i></div>
                            <div class="dashboard-stat-mini-label">إجمالي السجلات</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total']) }}</div>
                            <div class="dashboard-stat-mini-badges">
                                <span class="badge badge-info">{{ $operationStats['this_month'] }} هذا الشهر</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="dashboard-stat-mini dashboard-stat-mini-secondary">
                            <div class="dashboard-stat-mini-icon"><i class="bi bi-percent"></i></div>
                            <div class="dashboard-stat-mini-label">متوسط التحميل</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['avg_load'], 1) }}%</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="dashboard-stat-mini dashboard-stat-mini-danger">
                            <div class="dashboard-stat-mini-icon"><i class="bi bi-arrow-down-circle"></i></div>
                            <div class="dashboard-stat-mini-label">هذا الأسبوع</div>
                            <div class="dashboard-stat-mini-value">{{ $operationStats['this_week'] }}</div>
                            <div class="dashboard-stat-mini-unit">سجل</div>
                        </div>
                    </div>
                </div>

                @elseif($user->isCompanyOwner())
                {{-- Company Owner: enhanced with icons --}}
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="dashboard-stat-mini dashboard-stat-mini-primary">
                            <div class="dashboard-stat-mini-icon"><i class="bi bi-journal-text"></i></div>
                            <div class="dashboard-stat-mini-label">إجمالي السجلات</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total']) }}</div>
                            <div class="dashboard-stat-mini-badges">
                                <span class="badge badge-info">{{ $operationStats['this_month'] }} هذا الشهر</span>
                                <span class="badge badge-primary">{{ $operationStats['this_week'] }} هذا الأسبوع</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dashboard-stat-mini dashboard-stat-mini-success">
                            <div class="dashboard-stat-mini-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                            <div class="dashboard-stat-mini-label">الطاقة المنتجة</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total_energy'], 2) }}</div>
                            <div class="dashboard-stat-mini-unit">kWh</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dashboard-stat-mini dashboard-stat-mini-warning">
                            <div class="dashboard-stat-mini-icon"><i class="bi bi-fuel-pump"></i></div>
                            <div class="dashboard-stat-mini-label">الوقود المستهلك</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total_fuel'], 2) }}</div>
                            <div class="dashboard-stat-mini-unit">لتر</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dashboard-stat-mini dashboard-stat-mini-info">
                            <div class="dashboard-stat-mini-icon"><i class="bi bi-speedometer2"></i></div>
                            <div class="dashboard-stat-mini-label">متوسط نسبة التحميل</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['avg_load'], 1) }}%</div>
                        </div>
                    </div>
                </div>

                @else
                {{-- SuperAdmin / Admin / Employee / Technician --}}
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="dashboard-stat-mini">
                            <div class="dashboard-stat-mini-label">إجمالي السجلات</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total']) }}</div>
                            <div class="dashboard-stat-mini-badges">
                                <span class="badge badge-info">{{ $operationStats['this_month'] }} هذا الشهر</span>
                                <span class="badge badge-primary">{{ $operationStats['this_week'] }} هذا الأسبوع</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dashboard-stat-mini">
                            <div class="dashboard-stat-mini-label">الطاقة المنتجة</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total_energy'], 2) }}</div>
                            <div class="dashboard-stat-mini-unit">kWh</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dashboard-stat-mini">
                            <div class="dashboard-stat-mini-label">الوقود المستهلك</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total_fuel'], 2) }}</div>
                            <div class="dashboard-stat-mini-unit">لتر</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dashboard-stat-mini">
                            <div class="dashboard-stat-mini-label">متوسط نسبة التحميل</div>
                            <div class="dashboard-stat-mini-value">{{ number_format($operationStats['avg_load'], 1) }}%</div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </x-admin.card>
    </div>
</div>
@endif

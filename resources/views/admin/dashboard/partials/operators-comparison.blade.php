{{-- جدول مقارنة المشغلين - سلطة الطاقة --}}
@if(isset($operatorsComparison) && count($operatorsComparison) > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-bar-chart-line me-2"></i>
                        مقارنة أداء المشغلين
                    </h5>
                    <p class="dashboard-card-subtitle">تحليل شامل للإنتاج والكفاءة والفاقد لكل مشغل</p>
                </div>
                <div class="dashboard-card-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="exportOperatorsTable()">
                        <i class="bi bi-download me-1"></i>
                        تصدير
                    </button>
                </div>
            </div>
            <div class="dashboard-card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped operators-comparison-table" id="operatorsComparisonTable">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th>المشغل</th>
                                <th class="text-center">وحدات التوليد</th>
                                <th class="text-center">المولدات</th>
                                <th class="text-center">القدرة المركبة</th>
                                <th class="text-center">الطاقة المنتجة</th>
                                <th class="text-center">هذا الشهر</th>
                                <th class="text-center">الوقود المستهلك</th>
                                <th class="text-center">الكفاءة</th>
                                <th class="text-center">الفاقد</th>
                                <th class="text-center">نسبة الفاقد</th>
                                <th class="text-center">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($operatorsComparison as $index => $operator)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('admin.operators.show', $operator['id']) }}" class="text-primary fw-bold text-decoration-none">
                                        {{ $operator['name'] }}
                                    </a>
                                    @if(!$operator['is_approved'])
                                        <span class="badge bg-warning ms-1" title="بانتظار الموافقة">
                                            <i class="bi bi-hourglass-split"></i>
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $operator['generation_units_count'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $operator['generators_count'] }}</span>
                                </td>
                                <td class="text-center">
                                    <strong>{{ number_format($operator['installed_capacity']) }}</strong>
                                    <small class="text-muted">kVA</small>
                                </td>
                                <td class="text-center">
                                    <strong class="text-success">{{ number_format($operator['total_energy']) }}</strong>
                                    <small class="text-muted">kWh</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ number_format($operator['this_month_energy']) }} kWh</span>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{{ number_format($operator['total_fuel']) }}</strong>
                                    <small class="text-muted">لتر</small>
                                </td>
                                <td class="text-center">
                                    @php
                                        $efficiencyClass = $operator['efficiency'] >= 3 ? 'success' : ($operator['efficiency'] >= 2 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $efficiencyClass }}">
                                        {{ $operator['efficiency'] }} kWh/لتر
                                    </span>
                                </td>
                                <td class="text-center">
                                    <strong class="text-danger">{{ number_format($operator['energy_loss']) }}</strong>
                                    <small class="text-muted">kWh</small>
                                </td>
                                <td class="text-center">
                                    @php
                                        $lossClass = $operator['loss_percentage'] <= 10 ? 'success' : ($operator['loss_percentage'] <= 20 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $lossClass }}">
                                        {{ $operator['loss_percentage'] }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $complianceClass = match($operator['compliance_status']) {
                                            'VALID' => 'success',
                                            'EXPIRED' => 'danger',
                                            'PENDING' => 'warning',
                                            default => 'secondary'
                                        };
                                        $complianceLabel = match($operator['compliance_status']) {
                                            'VALID' => 'سارية',
                                            'EXPIRED' => 'منتهية',
                                            'PENDING' => 'معلقة',
                                            default => 'غير محدد'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $complianceClass }}">
                                        {{ $complianceLabel }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="2" class="text-end">الإجمالي:</th>
                                <th class="text-center">{{ collect($operatorsComparison)->sum('generation_units_count') }}</th>
                                <th class="text-center">{{ collect($operatorsComparison)->sum('generators_count') }}</th>
                                <th class="text-center">{{ number_format(collect($operatorsComparison)->sum('installed_capacity')) }} kVA</th>
                                <th class="text-center text-success">{{ number_format(collect($operatorsComparison)->sum('total_energy')) }} kWh</th>
                                <th class="text-center">{{ number_format(collect($operatorsComparison)->sum('this_month_energy')) }} kWh</th>
                                <th class="text-center text-warning">{{ number_format(collect($operatorsComparison)->sum('total_fuel')) }} لتر</th>
                                <th class="text-center">-</th>
                                <th class="text-center text-danger">{{ number_format(collect($operatorsComparison)->sum('energy_loss')) }} kWh</th>
                                <th class="text-center">-</th>
                                <th class="text-center">-</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

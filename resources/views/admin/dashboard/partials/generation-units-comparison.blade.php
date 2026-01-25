{{-- جدول مقارنة وحدات التوليد - سلطة الطاقة --}}
@if(isset($generationUnitsComparison) && count($generationUnitsComparison) > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-diagram-3-fill me-2"></i>
                        تحليل وحدات التوليد
                    </h5>
                    <p class="dashboard-card-subtitle">تفاصيل الإنتاج والفاقد لكل وحدة توليد</p>
                </div>
                <div class="dashboard-card-actions">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary" onclick="filterUnits('all')">الكل</button>
                        <button class="btn btn-sm btn-outline-success" onclick="filterUnits('high')">كفاءة عالية</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="filterUnits('low')">كفاءة منخفضة</button>
                    </div>
                </div>
            </div>
            <div class="dashboard-card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped generation-units-table" id="generationUnitsTable">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th>كود الوحدة</th>
                                <th>المشغل</th>
                                <th class="text-center">الموقع</th>
                                <th class="text-center">المولدات</th>
                                <th class="text-center">القدرة</th>
                                <th class="text-center">المستفيدين</th>
                                <th class="text-center">الطاقة المنتجة</th>
                                <th class="text-center">هذا الشهر</th>
                                <th class="text-center">الكفاءة</th>
                                <th class="text-center">الفاقد</th>
                                <th class="text-center">نسبة الفاقد</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($generationUnitsComparison as $index => $unit)
                            <tr data-efficiency="{{ $unit['efficiency'] }}" data-loss="{{ $unit['loss_percentage'] }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('admin.generation-units.show', $unit['id']) }}" class="text-primary fw-bold text-decoration-none">
                                        {{ $unit['unit_code'] ?? $unit['name'] }}
                                    </a>
                                    @if($unit['name'] && $unit['name'] !== $unit['unit_code'])
                                        <br><small class="text-muted">{{ $unit['name'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.operators.show', $unit['operator_id']) }}" class="text-decoration-none">
                                        {{ $unit['operator_name'] ?? 'غير محدد' }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <small>
                                        {{ $unit['governorate'] ?? '-' }}
                                        @if($unit['city'])
                                            <br>{{ $unit['city'] }}
                                        @endif
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $unit['generators_count'] }}</span>
                                </td>
                                <td class="text-center">
                                    <strong>{{ number_format($unit['installed_capacity']) }}</strong>
                                    <small class="text-muted">kVA</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ number_format($unit['beneficiaries_count']) }}</span>
                                </td>
                                <td class="text-center">
                                    <strong class="text-success">{{ number_format($unit['total_energy']) }}</strong>
                                    <small class="text-muted">kWh</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ number_format($unit['this_month_energy']) }} kWh</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $efficiencyClass = $unit['efficiency'] >= 3 ? 'success' : ($unit['efficiency'] >= 2 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $efficiencyClass }}">
                                        {{ $unit['efficiency'] }} kWh/لتر
                                    </span>
                                </td>
                                <td class="text-center">
                                    <strong class="text-danger">{{ number_format($unit['energy_loss']) }}</strong>
                                    <small class="text-muted">kWh</small>
                                </td>
                                <td class="text-center">
                                    @php
                                        $lossClass = $unit['loss_percentage'] <= 10 ? 'success' : ($unit['loss_percentage'] <= 20 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $lossClass }}">
                                        {{ $unit['loss_percentage'] }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="4" class="text-end">الإجمالي:</th>
                                <th class="text-center">{{ collect($generationUnitsComparison)->sum('generators_count') }}</th>
                                <th class="text-center">{{ number_format(collect($generationUnitsComparison)->sum('installed_capacity')) }} kVA</th>
                                <th class="text-center">{{ number_format(collect($generationUnitsComparison)->sum('beneficiaries_count')) }}</th>
                                <th class="text-center text-success">{{ number_format(collect($generationUnitsComparison)->sum('total_energy')) }} kWh</th>
                                <th class="text-center">{{ number_format(collect($generationUnitsComparison)->sum('this_month_energy')) }} kWh</th>
                                <th class="text-center">-</th>
                                <th class="text-center text-danger">{{ number_format(collect($generationUnitsComparison)->sum('energy_loss')) }} kWh</th>
                                <th class="text-center">-</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterUnits(type) {
    const rows = document.querySelectorAll('#generationUnitsTable tbody tr');
    rows.forEach(row => {
        const efficiency = parseFloat(row.dataset.efficiency) || 0;
        const loss = parseFloat(row.dataset.loss) || 0;
        
        if (type === 'all') {
            row.style.display = '';
        } else if (type === 'high') {
            row.style.display = efficiency >= 3 ? '' : 'none';
        } else if (type === 'low') {
            row.style.display = efficiency < 2 || loss > 20 ? '' : 'none';
        }
    });
}

function exportOperatorsTable() {
    // يمكن إضافة وظيفة التصدير لاحقاً
    alert('سيتم تفعيل التصدير قريباً');
}
</script>
@endpush
@endif

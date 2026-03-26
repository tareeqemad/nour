@push('scripts')
<script src="{{ asset('assets/admin/libs/chart.js/chart.umd.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/dashboard-charts.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @php
        $user = auth()->user();
        $roleKey = match(true) {
            $user->isSuperAdmin() => 'super_admin',
            $user->isAdmin() => 'admin',
            $user->isEnergyAuthority() => 'energy_authority',
            $user->isCompanyOwner() => 'company_owner',
            $user->isEmployee() => 'employee',
            $user->isTechnician() => 'technician',
            $user->isCivilDefense() => 'civil_defense',
            default => 'unknown',
        };
    @endphp

    DashboardCharts.init({
        role: '{{ $roleKey }}',
        routes: {
            chartData: '{{ route("admin.dashboard.chart-data") }}',
            pieChartData: '{{ route("admin.dashboard.pie-chart-data") }}',
            @if($user->isEnergyAuthority() || $user->isSuperAdmin())
            operatorsComparison: '{{ route("admin.dashboard.operators-comparison") }}',
            generationUnitsComparison: '{{ route("admin.dashboard.generation-units-comparison") }}',
            @endif
        }
    });
});
</script>

{{-- Energy Authority inline charts for operations-statistics section --}}
@if($user->isEnergyAuthority() && isset($operatorsComparison) && is_array($operatorsComparison) && count($operatorsComparison) > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const operatorsData = @json($operatorsComparison);
    const colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#34495e', '#16a085', '#d35400'];

    // Production chart
    const productionCtx = document.getElementById('operatorsProductionChart');
    if (productionCtx) {
        new Chart(productionCtx, {
            type: 'bar',
            data: { labels: operatorsData.map(o => o.name), datasets: [{ label: 'الطاقة المنتجة (kWh)', data: operatorsData.map(o => o.total_energy), backgroundColor: colors, borderWidth: 1 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ctx.parsed.y.toLocaleString() + ' kWh' } } }, scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() } } } }
        });
    }

    // Loss chart
    const lossCtx = document.getElementById('operatorsLossChart');
    if (lossCtx) {
        new Chart(lossCtx, {
            type: 'doughnut',
            data: { labels: operatorsData.map(o => o.name), datasets: [{ data: operatorsData.map(o => o.energy_loss), backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', rtl: true, labels: { font: { family: 'Cairo, sans-serif' } } }, tooltip: { callbacks: { label: ctx => { const op = operatorsData[ctx.dataIndex]; return `${op.name}: ${ctx.parsed.toLocaleString()} kWh (${op.loss_percentage}%)`; } } } } }
        });
    }

    // Efficiency chart
    const efficiencyCtx = document.getElementById('operatorsEfficiencyChart');
    if (efficiencyCtx) {
        new Chart(efficiencyCtx, {
            type: 'bar',
            data: { labels: operatorsData.map(o => o.name), datasets: [{ label: 'الكفاءة (kWh/لتر)', data: operatorsData.map(o => o.efficiency), backgroundColor: operatorsData.map(o => o.efficiency >= 3 ? '#2ecc71' : (o.efficiency >= 2 ? '#f39c12' : '#e74c3c')), borderWidth: 1 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ctx.parsed.x + ' kWh/لتر' } } }, scales: { x: { beginAtZero: true, max: 5, title: { display: true, text: 'kWh/لتر' } } } }
        });
    }

    // Compliance chart
    const complianceCtx = document.getElementById('complianceStatusChart');
    if (complianceCtx) {
        const counts = {
            VALID: operatorsData.filter(o => o.compliance_status === 'VALID').length,
            EXPIRED: operatorsData.filter(o => o.compliance_status === 'EXPIRED').length,
            PENDING: operatorsData.filter(o => o.compliance_status === 'PENDING').length,
            UNKNOWN: operatorsData.filter(o => !['VALID', 'EXPIRED', 'PENDING'].includes(o.compliance_status)).length
        };
        new Chart(complianceCtx, {
            type: 'pie',
            data: { labels: ['سارية', 'منتهية', 'معلقة', 'غير محدد'], datasets: [{ data: [counts.VALID, counts.EXPIRED, counts.PENDING, counts.UNKNOWN], backgroundColor: ['#2ecc71', '#e74c3c', '#f39c12', '#95a5a6'], borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', rtl: true, labels: { font: { family: 'Cairo, sans-serif' } } } } }
        });
    }
});
</script>
@endif
@endpush

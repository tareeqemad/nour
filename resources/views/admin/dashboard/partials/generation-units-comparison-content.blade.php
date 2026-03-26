@if(isset($generationUnitsComparison) && count($generationUnitsComparison) > 0)
<div class="d-rank-list">
    @foreach(array_slice($generationUnitsComparison, 0, 5) as $index => $unit)
    <a href="{{ route('admin.generation-units.show', $unit['id']) }}" class="d-rank-item">
        <span class="d-rank-num">{{ $index + 1 }}</span>
        <div class="d-rank-info">
            <span class="d-rank-name">{{ $unit['unit_code'] ?? $unit['name'] }}</span>
            <span class="d-rank-meta">{{ $unit['operator_name'] ?? '' }} &middot; {{ $unit['generators_count'] }} مولد</span>
        </div>
        <div class="d-rank-value">
            <span class="d-rank-main">{{ number_format($unit['total_energy']) }}</span>
            <span class="d-rank-unit">kWh</span>
        </div>
    </a>
    @endforeach
</div>
@else
<div style="text-align:center;padding:2rem;color:#98A2B3;">
    <i class="bi bi-diagram-3" style="font-size:1.5rem;opacity:0.4;display:block;margin-bottom:0.4rem;"></i>
    <span style="font-size:0.82rem;">لا توجد بيانات وحدات توليد</span>
</div>
@endif

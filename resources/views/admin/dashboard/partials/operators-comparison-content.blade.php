@if(isset($operatorsComparison) && count($operatorsComparison) > 0)
<div class="d-rank-list">
    @foreach(array_slice($operatorsComparison, 0, 5) as $index => $operator)
    <a href="{{ route('admin.operators.show', $operator['id']) }}" class="d-rank-item">
        <span class="d-rank-num">{{ $index + 1 }}</span>
        <div class="d-rank-info">
            <span class="d-rank-name">{{ $operator['name'] }}</span>
            <span class="d-rank-meta">{{ $operator['generators_count'] }} مولد &middot; {{ number_format($operator['installed_capacity']) }} kVA</span>
        </div>
        <div class="d-rank-value">
            <span class="d-rank-main">{{ number_format($operator['total_energy']) }}</span>
            <span class="d-rank-unit">kWh</span>
        </div>
    </a>
    @endforeach
</div>
@else
<div style="text-align:center;padding:2rem;color:#98A2B3;">
    <i class="bi bi-building" style="font-size:1.5rem;opacity:0.4;display:block;margin-bottom:0.4rem;"></i>
    <span style="font-size:0.82rem;">لا توجد بيانات مشغلين</span>
</div>
@endif

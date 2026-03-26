{{--
    Partial لحقل التغييرات
    المتغيرات: $fieldName, $label, $icon, $colorClass, $placeholder, $items
--}}
@php
    $colors = [
        'success' => ['text' => '#047857', 'bg' => '#ECFDF5', 'border' => '#A7F3D0'],
        'danger'  => ['text' => '#B91C1C', 'bg' => '#FEF2F2', 'border' => '#FECACA'],
        'info'    => ['text' => '#0369A1', 'bg' => '#F0F9FF', 'border' => '#BAE6FD'],
        'warning' => ['text' => '#B45309', 'bg' => '#FFFBEB', 'border' => '#FCD34D'],
    ];
    $c = $colors[$colorClass] ?? $colors['info'];
@endphp

<div class="mb-3">
    <label class="form-label fw-semibold" style="color: {{ $c['text'] }}; font-size: 0.88rem;">
        <i class="bi {{ $icon }} me-1"></i>
        {{ $label }}
    </label>
    <div id="{{ $fieldName }}-container">
        @forelse($items as $item)
            <div class="input-group mb-2">
                <input type="text" name="{{ $fieldName }}[]" class="form-control" value="{{ $item }}">
                <button type="button" class="btn btn-outline-danger remove-field" style="border-color: {{ $c['border'] }}; color: {{ $c['text'] }};">
                    <i class="bi bi-dash-lg"></i>
                </button>
            </div>
        @empty
        @endforelse
        <div class="input-group mb-2">
            <input type="text" name="{{ $fieldName }}[]" class="form-control" placeholder="{{ $placeholder }}">
            <button type="button" class="btn add-field" data-target="{{ $fieldName }}" style="background: {{ $c['bg'] }}; border: 1.5px solid {{ $c['border'] }}; color: {{ $c['text'] }};">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
    </div>
</div>

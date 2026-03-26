{{-- Empty State --}}
@props(['icon' => 'bi-inbox', 'message' => 'لا توجد بيانات'])

<div class="text-center py-5" style="color: var(--color-text-muted, #5B6780);">
    <i class="bi {{ $icon }}" style="font-size: 2.5rem; opacity: .4;"></i>
    <p class="mt-2 mb-0">{{ $message }}</p>
    @if(isset($action))
        <div class="mt-3">{{ $action }}</div>
    @endif
</div>

{{-- 
    Partial لحقل التغييرات (features, fixes, improvements, security)
    المتغيرات المطلوبة:
    - $fieldName: اسم الحقل (features, fixes, improvements, security)
    - $label: العنوان
    - $icon: أيقونة Bootstrap
    - $colorClass: لون الـ Bootstrap (success, danger, info, warning)
    - $placeholder: النص التوضيحي
    - $items: العناصر الموجودة (array)
--}}

<div class="mb-4">
    <label class="form-label fw-semibold text-{{ $colorClass }}">
        <i class="bi {{ $icon }} me-1"></i>
        {{ $label }}
    </label>
    <div id="{{ $fieldName }}-container">
        @forelse($items as $item)
            <div class="input-group mb-2">
                <input type="text" name="{{ $fieldName }}[]" class="form-control" value="{{ $item }}">
                <button type="button" class="btn btn-outline-danger remove-field">
                    <i class="bi bi-dash-lg"></i>
                </button>
            </div>
        @empty
            {{-- حقل فارغ افتراضي --}}
        @endforelse
        {{-- حقل الإضافة --}}
        <div class="input-group mb-2">
            <input type="text" name="{{ $fieldName }}[]" class="form-control" placeholder="{{ $placeholder }}">
            <button type="button" class="btn btn-outline-{{ $colorClass }} add-field" data-target="{{ $fieldName }}">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
    </div>
</div>

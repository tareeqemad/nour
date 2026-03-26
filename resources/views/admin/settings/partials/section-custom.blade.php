{{-- قسم الإعدادات المخصصة --}}
@if(isset($settings) && $settings->has('custom') && $settings['custom']->count() > 0)
<div class="settings-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="settings-section-title mb-0">
            <i class="bi bi-sliders"></i>
            إعدادات مخصصة
        </h6>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSettingModal">
            <i class="bi bi-plus-lg me-1"></i>
            إضافة
        </button>
    </div>

    <div class="row g-3">
        @foreach($settings['custom'] as $setting)
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    {{ $setting->label ?? ucfirst(str_replace('_', ' ', $setting->key)) }}
                    @if($setting->description)
                        <small class="text-muted d-block fw-normal">{{ $setting->description }}</small>
                    @endif
                </label>

                @switch($setting->type)
                    @case('textarea')
                        <textarea name="settings[{{ $setting->key }}]" class="form-control" rows="3">{{ $setting->value }}</textarea>
                        @break
                    @case('number')
                        <input type="number" name="settings[{{ $setting->key }}]" class="form-control" value="{{ $setting->value }}">
                        @break
                    @default
                        <input type="text" name="settings[{{ $setting->key }}]" class="form-control" value="{{ $setting->value }}">
                @endswitch

                <div class="mt-2">
                    <form action="{{ route('admin.settings.destroy', $setting) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('هل أنت متأكد من حذف هذا الإعداد؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash me-1"></i>حذف
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

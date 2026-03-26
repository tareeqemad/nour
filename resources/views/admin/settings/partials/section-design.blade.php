{{-- قسم إعدادات التصميم والألوان --}}
@php
    $primaryColor = \App\Models\Setting::get('primary_color', '#24308f');
    $darkColor = \App\Models\Setting::get('dark_color', '#3b4863');
    $headerColor = \App\Models\Setting::get('header_color', '#24308f');
    $menuStyles = \App\Models\Setting::get('menu_styles', 'light');
    $headerStyles = \App\Models\Setting::get('header_styles', 'light');
@endphp

<div class="settings-section">
    <h6 class="settings-section-title">
        <i class="bi bi-palette"></i>
        التصميم والألوان
    </h6>

    {{-- عرض الألوان الحالية --}}
    <div class="settings-color-preview">
        <div class="d-flex flex-wrap gap-3">
            <span class="color-swatch">
                <span class="swatch" style="background: {{ $primaryColor }};"></span>
                Primary: {{ $primaryColor }}
            </span>
            <span class="color-swatch">
                <span class="swatch" style="background: {{ $darkColor }};"></span>
                Dark: {{ $darkColor }}
            </span>
            <span class="color-swatch">
                <span class="swatch" style="background: {{ $headerColor }};"></span>
                Header: {{ $headerColor }}
            </span>
        </div>
    </div>

    <div class="row g-3">
        {{-- اللون الأساسي --}}
        <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-semibold mb-0">اللون الأساسي</label>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="resetPrimaryColor">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>إعادة ضبط
                </button>
            </div>
            <div class="input-group">
                <input type="color" name="settings[primary_color]"
                       class="form-control form-control-color"
                       id="primaryColorInput"
                       value="{{ $primaryColor }}">
                <input type="text" name="settings[primary_color_hex]"
                       class="form-control"
                       id="primaryColorHex"
                       value="{{ $primaryColor }}"
                       placeholder="#24308f"
                       pattern="^#[0-9A-Fa-f]{6}$">
            </div>
            <small class="form-text text-muted">الأزرار والروابط والعناصر الرئيسية</small>
        </div>

        {{-- ستايل السايدبار --}}
        <div class="col-md-6">
            <label class="form-label fw-semibold">ستايل القائمة الجانبية</label>
            <select name="settings[menu_styles]" class="form-select" id="menuStylesSelect">
                <option value="light" {{ $menuStyles === 'light' ? 'selected' : '' }}>فاتح (Light)</option>
                <option value="dark" {{ $menuStyles === 'dark' ? 'selected' : '' }}>داكن (Dark)</option>
                <option value="color" {{ $menuStyles === 'color' ? 'selected' : '' }}>ملون (Color)</option>
            </select>
        </div>

        {{-- اللون الداكن (يظهر فقط عند Dark) --}}
        <div class="col-12" id="darkColorPickerRow" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-semibold mb-0">اللون الداكن</label>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="resetDarkColor">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>إعادة ضبط
                </button>
            </div>
            <div class="input-group">
                <input type="color" name="settings[dark_color]"
                       class="form-control form-control-color"
                       id="darkColorInput"
                       value="{{ $darkColor }}">
                <input type="text" name="settings[dark_color_hex]"
                       class="form-control"
                       id="darkColorHex"
                       value="{{ $darkColor }}"
                       placeholder="#3b4863"
                       pattern="^#[0-9A-Fa-f]{6}$">
            </div>
            <small class="form-text text-muted">يظهر فقط عند اختيار Dark</small>
        </div>

        {{-- ستايل الهيدر --}}
        <div class="col-md-6">
            <label class="form-label fw-semibold">ستايل الـ Header</label>
            <select name="settings[header_styles]" class="form-select" id="headerStylesSelect">
                <option value="light" {{ $headerStyles === 'light' ? 'selected' : '' }}>فاتح (Light)</option>
                <option value="dark" {{ $headerStyles === 'dark' ? 'selected' : '' }}>داكن (Dark)</option>
                <option value="color" {{ $headerStyles === 'color' ? 'selected' : '' }}>ملون (Color)</option>
            </select>
        </div>

        {{-- لون الهيدر (يظهر عند Dark/Color) --}}
        <div class="col-12" id="headerColorPickerRow" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-semibold mb-0">لون الـ Header</label>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="resetHeaderColor">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>إعادة ضبط
                </button>
            </div>
            <div class="input-group">
                <input type="color" name="settings[header_color]"
                       class="form-control form-control-color"
                       id="headerColorInput"
                       value="{{ $headerColor }}">
                <input type="text" name="settings[header_color_hex]"
                       class="form-control"
                       id="headerColorHex"
                       value="{{ $headerColor }}"
                       placeholder="#24308f"
                       pattern="^#[0-9A-Fa-f]{6}$">
            </div>
            <small class="form-text text-muted">يظهر عند اختيار Dark أو Color</small>
        </div>
    </div>
</div>

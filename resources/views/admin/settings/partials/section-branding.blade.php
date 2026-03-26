{{-- قسم اللوجو والأيقونات --}}
@php
    $currentLogo = \App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/nour_logo.png');
    $currentFavicon = \App\Models\Setting::get('site_favicon', 'assets/admin/images/brand-logos/favicon.ico');
@endphp

<div class="settings-section">
    <h6 class="settings-section-title">
        <i class="bi bi-image"></i>
        اللوجو والأيقونات
    </h6>

    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label fw-semibold">لوجو الموقع</label>
            <div class="img-preview">
                <img src="{{ asset($currentLogo) }}" alt="Logo" id="logoPreview"
                     style="max-height: 120px;"
                     onerror="this.src='{{ asset('assets/admin/images/brand-logos/nour_logo.png') }}'">
            </div>
            <input type="file" name="logo" id="logoInput" class="form-control"
                   accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml">
            <small class="form-text text-muted">PNG, JPG, WEBP, SVG (الحد: 2MB)</small>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">أيقونة الموقع (Favicon)</label>
            <div class="img-preview">
                <img src="{{ asset($currentFavicon) }}" alt="Favicon" id="faviconPreview"
                     style="max-height: 48px; max-width: 48px;"
                     onerror="this.src='{{ asset('assets/admin/images/brand-logos/favicon.ico') }}'">
            </div>
            <input type="file" name="favicon" id="faviconInput" class="form-control"
                   accept=".ico,image/x-icon">
            <small class="form-text text-muted">ملف .ico فقط (الحد: 512KB)</small>
        </div>
    </div>
</div>

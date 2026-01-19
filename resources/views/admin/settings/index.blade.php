@extends('layouts.admin')

@section('title', 'إعدادات الموقع')

@php
    $breadcrumbTitle = 'إعدادات الموقع';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/settings.css') }}">
@endpush

@section('content')
    @if(!auth()->user()->isSuperAdmin())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            غير مصرح لك بالوصول إلى هذه الصفحة
        </div>
    @else
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-gear me-2"></i>
                                إعدادات الموقع
                            </h5>
                            <div class="general-subtitle">
                                قم بتعديل إعدادات الموقع واللوجو والبيانات الأساسية
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <div class="mb-4 px-3">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-image text-primary me-2"></i>
                                    إعدادات اللوجو والأيقونات
                                </h6>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">لوجو الموقع</label>
                                        <div>
                                            @php
                                                $currentLogo = \App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/rased_logo.png');
                                            @endphp
                                            <div class="img-preview">
                                                <img src="{{ asset($currentLogo) }}" alt="Logo" id="logoPreview" style="max-height: 150px;" onerror="this.src='{{ asset('assets/admin/images/brand-logos/rased_logo.png') }}'">
                                            </div>
                                        </div>
                                        <input type="file" name="logo" id="logoInput" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml">
                                        <small class="text-muted d-block mt-2">الصيغ المدعومة: PNG, JPG, JPEG, WEBP, SVG (الحد الأقصى: 2MB)</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">أيقونة الموقع (Favicon)</label>
                                        <div>
                                            @php
                                                $currentFavicon = \App\Models\Setting::get('site_favicon', 'assets/admin/images/brand-logos/favicon.ico');
                                            @endphp
                                            <div class="img-preview">
                                                <img src="{{ asset($currentFavicon) }}" alt="Favicon" id="faviconPreview" style="max-height: 64px; max-width: 64px;" onerror="this.src='{{ asset('assets/admin/images/brand-logos/favicon.ico') }}'">
                                            </div>
                                        </div>
                                        <input type="file" name="favicon" id="faviconInput" class="form-control" accept=".ico,image/x-icon">
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            يجب أن يكون ملف Favicon بامتداد <strong>.ico</strong> فقط (الحد الأقصى: 512KB)
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4 px-3">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-palette text-primary me-2"></i>
                                    إعدادات التصميم
                                </h6>
                                
                                {{-- عرض الألوان المحفوظة حالياً --}}
                                <div class="alert alert-info d-flex align-items-center gap-3 mb-3" style="background: #e7f3ff; border: 1px solid #b3d9ff;">
                                    <i class="bi bi-info-circle fs-5"></i>
                                    <div class="flex-grow-1">
                                        <strong class="d-block mb-1">الألوان المحفوظة حالياً:</strong>
                                        <div class="d-flex flex-wrap gap-3 small">
                                            <span>
                                                <strong>اللون الأساسي:</strong> 
                                                <span class="badge" style="background-color: {{ \App\Models\Setting::get('primary_color', '#19228f') }}; color: #fff; padding: 4px 8px;">
                                                    {{ \App\Models\Setting::get('primary_color', '#19228f') }}
                                                </span>
                                            </span>
                                            <span>
                                                <strong>اللون الداكن:</strong> 
                                                <span class="badge" style="background-color: {{ \App\Models\Setting::get('dark_color', '#3b4863') }}; color: #fff; padding: 4px 8px;">
                                                    {{ \App\Models\Setting::get('dark_color', '#3b4863') }}
                                                </span>
                                            </span>
                                            <span>
                                                <strong>لون الـ Header:</strong> 
                                                <span class="badge" style="background-color: {{ \App\Models\Setting::get('header_color', '#19228f') }}; color: #fff; padding: 4px 8px;">
                                                    {{ \App\Models\Setting::get('header_color', '#19228f') }}
                                                </span>
                                            </span>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-lightbulb me-1"></i>
                                            عند الضغط على "إعادة ضبط" لأي لون، سيعود إلى القيمة الافتراضية الأصلية (#19228f للون الأساسي و Header، #3b4863 للون الداكن)
                                        </small>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold mb-0">اللون الأساسي (Primary Color)</label>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="resetPrimaryColor" title="إعادة ضبط اللون الأساسي">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                إعادة ضبط
                                            </button>
                                        </div>
                                        <div class="input-group">
                                            <input type="color" name="settings[primary_color]" 
                                                   class="form-control form-control-color" 
                                                   id="primaryColorInput"
                                                   value="{{ \App\Models\Setting::get('primary_color', '#19228f') }}"
                                                   title="اختر اللون الأساسي">
                                            <input type="text" name="settings[primary_color_hex]" 
                                                   class="form-control" 
                                                   id="primaryColorHex"
                                                   value="{{ \App\Models\Setting::get('primary_color', '#19228f') }}"
                                                   placeholder="#19228f"
                                                   pattern="^#[0-9A-Fa-f]{6}$">
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            اللون الأساسي المستخدم في الأزرار والروابط والعناصر الرئيسية
                                        </small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">ستايل القائمة الجانبية (Sidebar Menu Style)</label>
                                        <select name="settings[menu_styles]" class="form-select" id="menuStylesSelect">
                                            <option value="light" {{ \App\Models\Setting::get('menu_styles', 'color') === 'light' ? 'selected' : '' }}>فاتح (Light)</option>
                                            <option value="dark" {{ \App\Models\Setting::get('menu_styles', 'color') === 'dark' ? 'selected' : '' }}>داكن (Dark)</option>
                                            <option value="color" {{ \App\Models\Setting::get('menu_styles', 'color') === 'color' ? 'selected' : '' }}>ملون (Color)</option>
                                        </select>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            اختر نمط عرض القائمة الجانبية
                                        </small>
                                    </div>
                                </div>

                                <div class="row g-3 mt-2" id="darkColorPickerRow" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold mb-0">درجة اللون الداكن (Dark Color)</label>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="resetDarkColor" title="إعادة ضبط اللون الداكن">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                إعادة ضبط
                                            </button>
                                        </div>
                                        <div class="input-group">
                                            <input type="color" name="settings[dark_color]" 
                                                   class="form-control form-control-color" 
                                                   id="darkColorInput"
                                                   value="{{ \App\Models\Setting::get('dark_color', '#3b4863') }}"
                                                   title="اختر درجة اللون الداكن">
                                            <input type="text" name="settings[dark_color_hex]" 
                                                   class="form-control" 
                                                   id="darkColorHex"
                                                   value="{{ \App\Models\Setting::get('dark_color', '#3b4863') }}"
                                                   placeholder="#3b4863"
                                                   pattern="^#[0-9A-Fa-f]{6}$">
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            اختر درجة اللون الداكن المستخدم في القائمة الجانبية (يظهر فقط عند اختيار Dark)
                                        </small>
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">ستايل الـ Header (Header Style)</label>
                                        <select name="settings[header_styles]" class="form-select" id="headerStylesSelect">
                                            <option value="light" {{ \App\Models\Setting::get('header_styles', 'color') === 'light' ? 'selected' : '' }}>فاتح (Light)</option>
                                            <option value="dark" {{ \App\Models\Setting::get('header_styles', 'color') === 'dark' ? 'selected' : '' }}>داكن (Dark)</option>
                                            <option value="color" {{ \App\Models\Setting::get('header_styles', 'color') === 'color' ? 'selected' : '' }}>ملون (Color)</option>
                                        </select>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            اختر نمط عرض الـ Header العلوي
                                        </small>
                                    </div>
                                </div>

                                <div class="row g-3 mt-2" id="headerColorPickerRow" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold mb-0">لون الـ Header (Header Color)</label>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="resetHeaderColor" title="إعادة ضبط لون الـ Header">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                إعادة ضبط
                                            </button>
                                        </div>
                                        <div class="input-group">
                                            <input type="color" name="settings[header_color]" 
                                                   class="form-control form-control-color" 
                                                   id="headerColorInput"
                                                   value="{{ \App\Models\Setting::get('header_color', '#19228f') }}"
                                                   title="اختر لون الـ Header">
                                            <input type="text" name="settings[header_color_hex]" 
                                                   class="form-control" 
                                                   id="headerColorHex"
                                                   value="{{ \App\Models\Setting::get('header_color', '#19228f') }}"
                                                   placeholder="#19228f"
                                                   pattern="^#[0-9A-Fa-f]{6}$">
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            اختر لون الـ Header (يظهر فقط عند اختيار Dark أو Color)
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4 px-3">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-info-circle text-info me-2"></i>
                                    المعلومات العامة
                                </h6>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">اسم الموقع</label>
                                        <input type="text" name="settings[site_name]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('site_name', 'نور') }}"
                                               placeholder="اسم الموقع">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">وصف الموقع</label>
                                        <input type="text" name="settings[site_description]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('site_description', '') }}"
                                               placeholder="وصف مختصر للموقع">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">البريد الإلكتروني</label>
                                        <input type="email" name="settings[site_email]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('site_email', '') }}"
                                               placeholder="info@example.com">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">رقم الهاتف</label>
                                        <input type="text" name="settings[site_phone]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('site_phone', '') }}"
                                               placeholder="+970 59 123 4567">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">العنوان</label>
                                        <textarea name="settings[site_address]" 
                                                  class="form-control" 
                                                  rows="2"
                                                  placeholder="عنوان الموقع الكامل">{{ \App\Models\Setting::get('site_address', '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4 px-3">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-share text-primary me-2"></i>
                                    وسائل التواصل الاجتماعي
                                </h6>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">فيسبوك</label>
                                        <input type="url" name="settings[social_facebook]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('social_facebook', '') }}"
                                               placeholder="https://facebook.com/...">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">تويتر</label>
                                        <input type="url" name="settings[social_twitter]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('social_twitter', '') }}"
                                               placeholder="https://twitter.com/...">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">إنستغرام</label>
                                        <input type="url" name="settings[social_instagram]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('social_instagram', '') }}"
                                               placeholder="https://instagram.com/...">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">لينكد إن</label>
                                        <input type="url" name="settings[social_linkedin]" 
                                               class="form-control" 
                                               value="{{ \App\Models\Setting::get('social_linkedin', '') }}"
                                               placeholder="https://linkedin.com/...">
                                    </div>
                                </div>
                            </div>

                            @if(isset($settings) && $settings->has('custom') && $settings['custom']->count() > 0)
                            <hr class="my-4">

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0">
                                        <i class="bi bi-sliders text-warning me-2"></i>
                                        إعدادات مخصصة
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSettingModal">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        إضافة إعداد جديد
                                    </button>
                                </div>

                                <div class="row g-3">
                                    @foreach($settings['custom'] as $setting)
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                {{ $setting->label ?? ucfirst(str_replace('_', ' ', $setting->key)) }}
                                                @if($setting->description)
                                                    <small class="text-muted d-block">{{ $setting->description }}</small>
                                                @endif
                                            </label>
                                            @if($setting->type === 'textarea')
                                                <textarea name="settings[{{ $setting->key }}]" 
                                                          class="form-control" 
                                                          rows="3">{{ $setting->value }}</textarea>
                                            @elseif($setting->type === 'number')
                                                <input type="number" name="settings[{{ $setting->key }}]" 
                                                       class="form-control" 
                                                       value="{{ $setting->value }}">
                                            @else
                                                <input type="text" name="settings[{{ $setting->key }}]" 
                                                       class="form-control" 
                                                       value="{{ $setting->value }}">
                                            @endif
                                            <div class="mt-2">
                                                <form action="{{ route('admin.settings.destroy', $setting) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإعداد؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash me-1"></i>
                                                        حذف
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <hr class="my-4">

                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-check-lg me-2"></i>
                                    حفظ الإعدادات
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(isset($settings) && $settings->has('custom'))
    <div class="modal fade" id="addSettingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.settings.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة إعداد جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">المفتاح (Key) <span class="text-danger">*</span></label>
                            <input type="text" name="key" class="form-control" placeholder="custom_setting_key">
                            <small class="text-muted">استخدم أحرف إنجليزية وأرقام وشرطة سفلية فقط</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">القيمة</label>
                            <input type="text" name="value" class="form-control" placeholder="القيمة الافتراضية">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">النوع <span class="text-danger">*</span></label>
                            <select name="type" class="form-select">
                                <option value="text">نص</option>
                                <option value="textarea">نص طويل</option>
                                <option value="number">رقم</option>
                                <option value="email">بريد إلكتروني</option>
                                <option value="url">رابط</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">المجموعة <span class="text-danger">*</span></label>
                            <input type="text" name="group" class="form-control" value="custom" placeholder="custom">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">التسمية</label>
                            <input type="text" name="label" class="form-control" placeholder="اسم الإعداد بالعربية">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">الوصف</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="وصف مختصر للإعداد"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endif
@endsection

@push('scripts')
<script>
    (function($) {
        $(document).ready(function() {
            const logoInput = $('#logoInput');
            const logoPreview = $('#logoPreview');
            const faviconInput = $('#faviconInput');
            const faviconPreview = $('#faviconPreview');
            const settingsForm = $('#settingsForm');
            const submitBtn = $('#submitBtn');

            if (logoInput.length && logoPreview.length) {
                logoInput.on('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            logoPreview.attr('src', e.target.result);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            if (faviconInput.length && faviconPreview.length) {
                faviconInput.on('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            faviconPreview.attr('src', e.target.result);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Primary Color Picker Sync & Dynamic Preview
            const primaryColorInput = $('#primaryColorInput');
            const primaryColorHex = $('#primaryColorHex');
            const resetPrimaryColorBtn = $('#resetPrimaryColor');
            // الألوان الافتراضية الأصلية (لا تتغير حتى لو غير المستخدم الألوان)
            const ORIGINAL_DEFAULT_PRIMARY_COLOR = '#19228f';
            const ORIGINAL_DEFAULT_DARK_COLOR = '#3b4863';
            const ORIGINAL_DEFAULT_HEADER_COLOR = '#19228f';
            
            /**
             * Convert hex color to RGB values (format: "25, 34, 143")
             */
            function hexToRgb(hex) {
                // Remove # if present
                hex = hex.replace('#', '');
                
                // Handle 3-digit hex colors (e.g., #fff -> #ffffff)
                if (hex.length === 3) {
                    hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
                }
                
                // Convert to RGB
                const r = parseInt(hex.substring(0, 2), 16);
                const g = parseInt(hex.substring(2, 4), 16);
                const b = parseInt(hex.substring(4, 6), 16);
                
                return `${r}, ${g}, ${b}`;
            }
            
            /**
             * Update CSS variable --primary-rgb dynamically
             */
            function updatePrimaryColor(hexColor) {
                if (!hexColor || !/^#[0-9A-Fa-f]{3,6}$/i.test(hexColor)) {
                    return;
                }
                
                const rgb = hexToRgb(hexColor);
                document.documentElement.style.setProperty('--primary-rgb', rgb);
            }
            
            if (primaryColorInput.length && primaryColorHex.length) {
                // Sync color picker with text input
                primaryColorInput.on('change', function() {
                    const colorValue = $(this).val();
                    primaryColorHex.val(colorValue);
                    updatePrimaryColor(colorValue);
                });
                
                primaryColorHex.on('input', function() {
                    const value = $(this).val();
                    if (/^#[0-9A-Fa-f]{6}$/i.test(value)) {
                        primaryColorInput.val(value);
                        updatePrimaryColor(value);
                    }
                });
                
                // Initialize with current color (only if not overridden by localStorage)
                // Don't auto-apply on page load to avoid changing header/sidebar unexpectedly
                // The color will be applied from layouts/admin.blade.php on initial load
            }
            
            // Reset Primary Color Button - يعيد إلى القيمة الافتراضية الأصلية
            if (resetPrimaryColorBtn.length) {
                resetPrimaryColorBtn.on('click', function() {
                    if (confirm('هل أنت متأكد من إعادة ضبط اللون إلى القيمة الافتراضية الأصلية؟')) {
                        primaryColorInput.val(ORIGINAL_DEFAULT_PRIMARY_COLOR);
                        primaryColorHex.val(ORIGINAL_DEFAULT_PRIMARY_COLOR);
                        updatePrimaryColor(ORIGINAL_DEFAULT_PRIMARY_COLOR);
                    }
                });
            }

            // Menu Style Dynamic Preview
            const menuStyleSelect = $('#menuStylesSelect');
            const darkColorPickerRow = $('#darkColorPickerRow');
            const darkColorInput = $('#darkColorInput');
            const darkColorHex = $('#darkColorHex');
            
            /**
             * Update data-menu-styles attribute dynamically
             */
            function updateMenuStyle(style) {
                if (style && ['light', 'dark', 'color'].includes(style)) {
                    document.documentElement.setAttribute('data-menu-styles', style);
                    
                    // Show/hide dark color picker based on selection
                    if (style === 'dark') {
                        darkColorPickerRow.slideDown(200);
                        // Update --menu-bg with current dark color
                        const currentDarkColor = darkColorInput.val();
                        if (currentDarkColor) {
                            document.documentElement.style.setProperty('--menu-bg', currentDarkColor);
                        }
                    } else {
                        darkColorPickerRow.slideUp(200);
                        // Remove custom --menu-bg if not dark
                        document.documentElement.style.removeProperty('--menu-bg');
                    }
                }
            }
            
            /**
             * Convert hex color to RGB values (format: "59, 72, 99")
             */
            function hexToRgbForDark(hex) {
                hex = hex.replace('#', '');
                if (hex.length === 3) {
                    hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
                }
                const r = parseInt(hex.substring(0, 2), 16);
                const g = parseInt(hex.substring(2, 4), 16);
                const b = parseInt(hex.substring(4, 6), 16);
                return `${r}, ${g}, ${b}`;
            }
            
            /**
             * Update CSS variable --dark-rgb and --menu-bg dynamically
             */
            function updateDarkColor(hexColor) {
                if (!hexColor || !/^#[0-9A-Fa-f]{3,6}$/i.test(hexColor)) {
                    return;
                }
                const rgb = hexToRgbForDark(hexColor);
                document.documentElement.style.setProperty('--dark-rgb', rgb);
                
                // Update --menu-bg if dark menu style is selected
                if (menuStyleSelect.val() === 'dark') {
                    document.documentElement.style.setProperty('--menu-bg', hexColor);
                }
            }
            
            if (menuStyleSelect.length) {
                // Initialize with current style on page load
                const currentMenuStyle = menuStyleSelect.val();
                if (currentMenuStyle === 'dark') {
                    darkColorPickerRow.show();
                } else {
                    darkColorPickerRow.hide();
                }
                
                menuStyleSelect.on('change', function() {
                    const selectedStyle = $(this).val();
                    updateMenuStyle(selectedStyle);
                    // Update localStorage when user changes it manually
                    localStorage.setItem('nowaMenu', selectedStyle);
                });
            }
            
            // Dark Color Picker Sync & Dynamic Preview
            const resetDarkColorBtn = $('#resetDarkColor');
            
            if (darkColorInput.length && darkColorHex.length) {
                // Sync color picker with text input
                darkColorInput.on('change', function() {
                    const colorValue = $(this).val();
                    darkColorHex.val(colorValue);
                    updateDarkColor(colorValue);
                });
                
                darkColorHex.on('input', function() {
                    const value = $(this).val();
                    if (/^#[0-9A-Fa-f]{6}$/i.test(value)) {
                        darkColorInput.val(value);
                        updateDarkColor(value);
                    }
                });
                
                // Initialize with current color (only if dark is selected and user manually changes)
                // Don't auto-apply on page load to avoid changing header/sidebar unexpectedly
                // The color will be applied from layouts/admin.blade.php on initial load
            }
            
            // Reset Dark Color Button - يعيد إلى القيمة الافتراضية الأصلية
            if (resetDarkColorBtn.length) {
                resetDarkColorBtn.on('click', function() {
                    if (confirm('هل أنت متأكد من إعادة ضبط اللون إلى القيمة الافتراضية الأصلية؟')) {
                        darkColorInput.val(ORIGINAL_DEFAULT_DARK_COLOR);
                        darkColorHex.val(ORIGINAL_DEFAULT_DARK_COLOR);
                        updateDarkColor(ORIGINAL_DEFAULT_DARK_COLOR);
                    }
                });
            }

            // Header Style Dynamic Preview
            const headerStyleSelect = $('#headerStylesSelect');
            const headerColorPickerRow = $('#headerColorPickerRow');
            const headerColorInput = $('#headerColorInput');
            const headerColorHex = $('#headerColorHex');
            
            /**
             * Update data-header-styles attribute dynamically
             */
            function updateHeaderStyle(style) {
                if (style && ['light', 'dark', 'color'].includes(style)) {
                    document.documentElement.setAttribute('data-header-styles', style);
                    
                    // Show/hide header color picker based on selection
                    if (style === 'dark' || style === 'color') {
                        headerColorPickerRow.slideDown(200);
                        // Update header background color with current header color
                        const currentHeaderColor = headerColorInput.val();
                        if (currentHeaderColor) {
                            updateHeaderColor(currentHeaderColor);
                        }
                    } else {
                        headerColorPickerRow.slideUp(200);
                        // Remove custom header background color if light
                        const headerEl = document.querySelector('.app-header');
                        if (headerEl) {
                            headerEl.style.backgroundColor = '';
                        }
                        // Reset search input and button styles for light header
                        const searchInput = document.querySelector('.header-content-left .form-control');
                        if (searchInput) {
                            searchInput.style.backgroundColor = '';
                            searchInput.style.borderColor = '';
                            searchInput.style.color = '';
                        }
                        const searchBtn = document.querySelector('.header-content-left .btn');
                        if (searchBtn) {
                            searchBtn.style.backgroundColor = '';
                            searchBtn.style.borderColor = '';
                            searchBtn.style.color = '';
                        }
                    }
                }
            }
            
            /**
             * Convert hex color to RGB values (format: "25, 34, 143")
             */
            function hexToRgbForHeader(hex) {
                hex = hex.replace('#', '');
                if (hex.length === 3) {
                    hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
                }
                const r = parseInt(hex.substring(0, 2), 16);
                const g = parseInt(hex.substring(2, 4), 16);
                const b = parseInt(hex.substring(4, 6), 16);
                return `${r}, ${g}, ${b}`;
            }
            
            /**
             * Update CSS variable --header-rgb and header background color dynamically
             */
            function updateHeaderColor(hexColor) {
                if (!hexColor || !/^#[0-9A-Fa-f]{3,6}$/i.test(hexColor)) {
                    return;
                }
                const rgb = hexToRgbForHeader(hexColor);
                document.documentElement.style.setProperty('--header-rgb', rgb);
                
                // Update header background color if dark or color style is selected
                if (headerStyleSelect.val() === 'dark' || headerStyleSelect.val() === 'color') {
                    const headerEl = document.querySelector('.app-header');
                    if (headerEl) {
                        headerEl.style.backgroundColor = hexColor;
                    }
                    
                    // Update search input in header to match header color
                    const searchInput = document.querySelector('.header-content-left .form-control');
                    if (searchInput) {
                        searchInput.style.backgroundColor = hexColor;
                        searchInput.style.borderColor = 'rgba(255, 255, 255, 0.2)';
                        searchInput.style.color = '#fff';
                    }
                    
                    // Update search button in header to match header color
                    const searchBtn = document.querySelector('.header-content-left .btn');
                    if (searchBtn) {
                        searchBtn.style.backgroundColor = hexColor;
                        searchBtn.style.borderColor = 'rgba(255, 255, 255, 0.2)';
                        searchBtn.style.color = '#fff';
                    }
                } else {
                    // Reset search input and button styles for light header
                    const searchInput = document.querySelector('.header-content-left .form-control');
                    if (searchInput) {
                        searchInput.style.backgroundColor = '';
                        searchInput.style.borderColor = '';
                        searchInput.style.color = '';
                    }
                    const searchBtn = document.querySelector('.header-content-left .btn');
                    if (searchBtn) {
                        searchBtn.style.backgroundColor = '';
                        searchBtn.style.borderColor = '';
                        searchBtn.style.color = '';
                    }
                }
            }
            
            if (headerStyleSelect.length) {
                // Initialize with current style on page load
                const currentHeaderStyle = headerStyleSelect.val();
                if (currentHeaderStyle === 'dark' || currentHeaderStyle === 'color') {
                    headerColorPickerRow.show();
                } else {
                    headerColorPickerRow.hide();
                }
                
                headerStyleSelect.on('change', function() {
                    const selectedStyle = $(this).val();
                    updateHeaderStyle(selectedStyle);
                    // Update localStorage when user changes it manually
                    localStorage.setItem('nowaHeader', selectedStyle);
                });
            }
            
            // Header Color Picker Sync & Dynamic Preview
            const resetHeaderColorBtn = $('#resetHeaderColor');
            
            if (headerColorInput.length && headerColorHex.length) {
                // Sync color picker with text input
                headerColorInput.on('change', function() {
                    const colorValue = $(this).val();
                    headerColorHex.val(colorValue);
                    updateHeaderColor(colorValue);
                });
                
                headerColorHex.on('input', function() {
                    const value = $(this).val();
                    if (/^#[0-9A-Fa-f]{6}$/i.test(value)) {
                        headerColorInput.val(value);
                        updateHeaderColor(value);
                    }
                });
            }
            
            // Reset Header Color Button - يعيد إلى القيمة الافتراضية الأصلية
            if (resetHeaderColorBtn.length) {
                resetHeaderColorBtn.on('click', function() {
                    if (confirm('هل أنت متأكد من إعادة ضبط اللون إلى القيمة الافتراضية الأصلية؟')) {
                        headerColorInput.val(ORIGINAL_DEFAULT_HEADER_COLOR);
                        headerColorHex.val(ORIGINAL_DEFAULT_HEADER_COLOR);
                        updateHeaderColor(ORIGINAL_DEFAULT_HEADER_COLOR);
                    }
                });
            }

            if (settingsForm.length) {
                settingsForm.on('submit', function(e) {
                    if (logoInput.length && logoInput[0].files.length > 0) {
                        const logoFile = logoInput[0].files[0];
                        if (logoFile.size > 2 * 1024 * 1024) {
                            e.preventDefault();
                            alert('حجم ملف اللوجو كبير جداً. الحد الأقصى هو 2MB');
                            return false;
                        }
                    }

                    if (faviconInput.length && faviconInput[0].files.length > 0) {
                        const faviconFile = faviconInput[0].files[0];
                        if (faviconFile.size > 512 * 1024) {
                            e.preventDefault();
                            alert('حجم ملف الأيقونة كبير جداً. الحد الأقصى هو 512KB');
                            return false;
                        }
                    }

                    // Clear localStorage for menu_styles and header_styles to use database values
                    // This ensures that saved settings from database take precedence
                    localStorage.removeItem('nowaMenu');
                    localStorage.removeItem('nowaHeader');

                    submitBtn.prop('disabled', true);
                    const originalText = submitBtn.html();
                    submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');
                });
            }

            const phoneInput = $('input[name="settings[site_phone]"]');
            if (phoneInput.length) {
                phoneInput.on('input', function(e) {
                    let value = $(this).val().replace(/\D/g, '');
                    if (value.length > 0 && !value.startsWith('+')) {
                        if (value.startsWith('970')) {
                            value = '+' + value;
                        } else if (value.startsWith('0')) {
                            value = '+970' + value.substring(1);
                        }
                    }
                    $(this).val(value);
                });
            }

            const urlInputs = $('input[type="url"]');
            urlInputs.on('blur', function() {
                const value = $(this).val().trim();
                if (value && !value.match(/^https?:\/\//)) {
                    this.setCustomValidity('يجب أن يبدأ الرابط بـ http:// أو https://');
                } else {
                    this.setCustomValidity('');
                }
            });
        });
    })(jQuery);
</script>
@endpush

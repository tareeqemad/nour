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
                <x-admin.card>
                    <x-admin.card-header-form title="إعدادات الموقع" icon="bi-gear" :backRoute="null">
                    </x-admin.card-header-form>

                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            @include('admin.settings.partials.section-branding')
                            @include('admin.settings.partials.section-design')
                            @include('admin.settings.partials.section-general')
                            @include('admin.settings.partials.section-territory')
                            @include('admin.settings.partials.section-social')
                            @include('admin.settings.partials.section-custom')

                            {{-- أزرار الحفظ --}}
                            <div class="settings-actions">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <i class="bi bi-check-lg me-1"></i>
                                    حفظ الإعدادات
                                </button>
                            </div>
                        </div>
                    </form>
                </x-admin.card>
            </div>
        </div>
    </div>

    @include('admin.settings.partials.modal-add-setting')
    @endif
@endsection

@push('scripts')
<script>
(function($) {
    $(document).ready(function() {
        const DEFAULTS = {
            primaryColor: '#24308f',
            darkColor: '#3b4863',
            headerColor: '#24308f'
        };

        // === File Preview ===
        function initFilePreview(inputId, previewId) {
            const $input = $(inputId);
            const $preview = $(previewId);
            if (!$input.length || !$preview.length) return;

            $input.on('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => $preview.attr('src', e.target.result);
                reader.readAsDataURL(file);
            });
        }

        initFilePreview('#logoInput', '#logoPreview');
        initFilePreview('#faviconInput', '#faviconPreview');

        // === Color Helpers ===
        function hexToRgb(hex) {
            hex = hex.replace('#', '');
            if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
            return `${parseInt(hex.substring(0,2),16)}, ${parseInt(hex.substring(2,4),16)}, ${parseInt(hex.substring(4,6),16)}`;
        }

        function initColorPicker(colorId, hexId, resetId, defaultColor, cssVar) {
            const $color = $(colorId);
            const $hex = $(hexId);
            const $reset = $(resetId);
            if (!$color.length || !$hex.length) return;

            function apply(val) {
                if (!/^#[0-9A-Fa-f]{6}$/i.test(val)) return;
                if (cssVar) document.documentElement.style.setProperty(cssVar, hexToRgb(val));
            }

            $color.on('change', function() { $hex.val(this.value); apply(this.value); });
            $hex.on('input', function() {
                if (/^#[0-9A-Fa-f]{6}$/i.test(this.value)) { $color.val(this.value); apply(this.value); }
            });

            if ($reset.length) {
                $reset.on('click', function() {
                    if (!confirm('إعادة ضبط اللون إلى القيمة الافتراضية؟')) return;
                    $color.val(defaultColor);
                    $hex.val(defaultColor);
                    apply(defaultColor);
                });
            }
        }

        initColorPicker('#primaryColorInput', '#primaryColorHex', '#resetPrimaryColor', DEFAULTS.primaryColor, '--primary-rgb');
        initColorPicker('#darkColorInput', '#darkColorHex', '#resetDarkColor', DEFAULTS.darkColor, '--dark-rgb');
        initColorPicker('#headerColorInput', '#headerColorHex', '#resetHeaderColor', DEFAULTS.headerColor, null);

        // === Style Selectors ===
        const $menuStyle = $('#menuStylesSelect');
        const $darkRow = $('#darkColorPickerRow');
        const $headerStyle = $('#headerStylesSelect');
        const $headerRow = $('#headerColorPickerRow');

        function toggleMenuStyle(style) {
            if (['light','dark','color'].includes(style)) {
                document.documentElement.setAttribute('data-menu-styles', style);
                $darkRow[style === 'dark' ? 'slideDown' : 'slideUp'](200);
                if (style === 'dark') {
                    document.documentElement.style.setProperty('--menu-bg', $('#darkColorInput').val());
                } else {
                    document.documentElement.style.removeProperty('--menu-bg');
                }
            }
        }

        function toggleHeaderStyle(style) {
            if (['light','dark','color'].includes(style)) {
                document.documentElement.setAttribute('data-header-styles', style);
                $headerRow[['dark','color'].includes(style) ? 'slideDown' : 'slideUp'](200);
                if (['dark','color'].includes(style)) {
                    localStorage.setItem('nowaHeader', style);
                } else {
                    localStorage.removeItem('nowaHeader');
                }
            }
        }

        if ($menuStyle.length) {
            $darkRow[$menuStyle.val() === 'dark' ? 'show' : 'hide']();
            $menuStyle.on('change', function() {
                toggleMenuStyle(this.value);
                localStorage.setItem('nowaMenu', this.value);
            });
        }

        if ($headerStyle.length) {
            $headerRow[['dark','color'].includes($headerStyle.val()) ? 'show' : 'hide']();
            $headerStyle.on('change', function() { toggleHeaderStyle(this.value); });
        }

        // === Form Submit ===
        $('#settingsForm').on('submit', function() {
            const $btn = $('#submitBtn');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');
        });
    });
})(jQuery);
</script>
@endpush

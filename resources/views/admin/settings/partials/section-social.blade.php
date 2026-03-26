{{-- قسم وسائل التواصل الاجتماعي --}}
@php
    $socialLinks = [
        ['key' => 'social_facebook',  'label' => 'فيسبوك',   'icon' => 'bi-facebook',  'placeholder' => 'https://facebook.com/...'],
        ['key' => 'social_twitter',   'label' => 'تويتر',    'icon' => 'bi-twitter-x', 'placeholder' => 'https://twitter.com/...'],
        ['key' => 'social_instagram', 'label' => 'إنستغرام', 'icon' => 'bi-instagram',  'placeholder' => 'https://instagram.com/...'],
        ['key' => 'social_linkedin',  'label' => 'لينكد إن', 'icon' => 'bi-linkedin',   'placeholder' => 'https://linkedin.com/...'],
    ];
@endphp

<div class="settings-section">
    <h6 class="settings-section-title">
        <i class="bi bi-share"></i>
        وسائل التواصل الاجتماعي
    </h6>

    <div class="row g-3">
        @foreach($socialLinks as $link)
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="bi {{ $link['icon'] }} me-1" style="opacity: .6;"></i>
                    {{ $link['label'] }}
                </label>
                <input type="url" name="settings[{{ $link['key'] }}]" class="form-control"
                       value="{{ \App\Models\Setting::get($link['key'], '') }}"
                       placeholder="{{ $link['placeholder'] }}">
            </div>
        @endforeach
    </div>
</div>

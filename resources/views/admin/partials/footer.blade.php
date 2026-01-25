@php
    $appVersion = \App\Models\VersionLog::getCurrentVersionNumber();
@endphp
<footer class="footer mt-auto py-3 bg-white text-center border-top">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
            <span class="d-inline-flex align-items-center gap-1">
                <i class="bi bi-lightning-charge-fill text-warning"></i>
                <span>
                    © {{ date('Y') }}
                    <a href="#" class="text-primary fw-semibold text-decoration-underline">
                        {{ \App\Models\Setting::get('site_name', 'نور') }}
                    </a>
                    — جميع الحقوق محفوظة.
                </span>
            </span>
            <span class="text-muted">|</span>
            <a href="{{ route('admin.about') }}" class="badge bg-primary text-decoration-none" title="حول النظام">
                <i class="bi bi-box-seam me-1"></i>
                v{{ $appVersion }}
            </a>
        </div>
    </div>
</footer>


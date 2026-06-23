@extends('layouts.admin')

@section('title', 'حول النظام')

@php
    $breadcrumbTitle = 'حول النظام';
    $siteName = \App\Models\Setting::get('site_name', 'نور');
    $logoUrl  = asset(\App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/nour_logo.png'));
    $changes  = $currentVersion ? $currentVersion->getCategorizedChanges() : ['features' => [], 'improvements' => [], 'fixes' => [], 'security' => []];

    $statMeta = [
        'operators'        => ['label' => 'المشغّلون',        'icon' => 'bi-people-fill',        'color' => '#24308F'],
        'generation_units' => ['label' => 'وحدات التوليد',    'icon' => 'bi-building',           'color' => '#0EA5E9'],
        'generators'       => ['label' => 'المولدات',         'icon' => 'bi-cpu-fill',           'color' => '#F59E0B'],
        'subscribers'      => ['label' => 'المشتركون',        'icon' => 'bi-person-lines-fill',  'color' => '#10B981'],
        'invoices'         => ['label' => 'الفواتير',         'icon' => 'bi-receipt',            'color' => '#2330B3'],
        'users'            => ['label' => 'المستخدمون',       'icon' => 'bi-person-badge-fill',  'color' => '#5B6780'],
    ];

    $modules = [
        ['icon' => 'bi-shield-lock',       'title' => 'المستخدمون والصلاحيات',  'desc' => 'أدوار وصلاحيات دقيقة ودعم الأدوار المخصّصة.'],
        ['icon' => 'bi-people',            'title' => 'المشغّلون والترخيص',     'desc' => 'دورة ترخيص (6 حالات)، اعتماد، ومرفقات وملف لكل مشغّل.'],
        ['icon' => 'bi-building',           'title' => 'وحدات التوليد والمولدات','desc' => 'إدارة المحطّات والمولدات وبياناتها الفنية.'],
        ['icon' => 'bi-wrench-adjustable', 'title' => 'التشغيل والصيانة',       'desc' => 'سجلّات تشغيل وصيانة وكفاءة وقود وامتثال.'],
        ['icon' => 'bi-person-lines-fill',  'title' => 'المشتركون والعدّادات',   'desc' => 'إدارة المشتركين والقراءات واستيراد/تصدير Excel.'],
        ['icon' => 'bi-receipt-cutoff',    'title' => 'الفوترة والتحصيل',       'desc' => 'فواتير ومدفوعات وإيصالات وتوزيع FIFO وتعرفة.'],
        ['icon' => 'bi-graph-up-arrow',    'title' => 'التقارير المالية',       'desc' => 'تقارير الإيراد والتحصيل والمتأخرات وأرصدة المشتركين.'],
        ['icon' => 'bi-chat-dots',         'title' => 'الرسائل والإشعارات',     'desc' => 'مراسلات داخلية ورسائل SMS بقوالب قابلة للتعديل.'],
        ['icon' => 'bi-clipboard-check',   'title' => 'الشكاوى والمهام',        'desc' => 'شكاوى ومقترحات وإدارة مهام الفرق.'],
        ['icon' => 'bi-book',              'title' => 'دليل التسجيل',           'desc' => 'فيديو تعريفي وخطوات وقاموس مصطلحات.'],
    ];
@endphp

@push('styles')
<style>
    .about-hero {
        position: relative; overflow: hidden;
        background: linear-gradient(135deg, #303FA3 0%, #4E5CC2 100%);
        border-radius: 16px; padding: 2rem; color: #fff;
        box-shadow: 0 8px 22px rgba(78,92,194,0.15);
    }
    .about-hero::after {
        content: ""; position: absolute; inset: 0;
        background-image: radial-gradient(rgba(255,255,255,0.10) 1px, transparent 1px);
        background-size: 16px 16px; opacity: .5; pointer-events: none;
    }
    .about-hero-inner { position: relative; z-index: 1; display: flex; flex-wrap: wrap; align-items: center; gap: 1.5rem; }
    .about-logo {
        width: 84px; height: 84px; border-radius: 18px; background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.25); display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .about-logo img { max-width: 60px; max-height: 60px; object-fit: contain; }
    .about-hero h2 { font-weight: 800; margin: 0 0 .35rem; font-size: 1.6rem; text-shadow: 0 1px 3px rgba(0,0,0,0.18); }
    .about-hero .tagline { color: rgba(255,255,255,0.95); margin: 0; font-size: .95rem; text-shadow: 0 1px 2px rgba(0,0,0,0.12); }
    .about-pill {
        display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .9rem; border-radius: 40px;
        background: rgba(255,255,255,0.20); border: 1px solid rgba(255,255,255,0.40); color: #fff;
        font-size: .85rem; font-weight: 700;
    }
    .about-pill.version { background: #fff; color: #24308F; border-color: #fff; }
    .about-pill .bi.text-green { color: #34d399; }

    /* أزرار الهيرو — تباين صريح على الخلفية الزرقاء */
    .about-hero .btn-hero-light { background: #fff; color: #24308F; border: 1px solid #fff; font-weight: 700; }
    .about-hero .btn-hero-light:hover { background: #EEF2FF; color: #24308F; }
    .about-hero .btn-hero-outline { background: rgba(255,255,255,0.16); color: #fff; border: 1px solid rgba(255,255,255,0.70); font-weight: 600; }
    .about-hero .btn-hero-outline:hover { background: rgba(255,255,255,0.30); color: #fff; }

    .about-stat {
        background: #fff; border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 14px;
        padding: 1.1rem 1rem; text-align: center; height: 100%; transition: transform .15s, box-shadow .15s;
    }
    .about-stat:hover { transform: translateY(-3px); box-shadow: 0 8px 22px rgba(31,41,55,0.08); }
    .about-stat .ic { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: .5rem; }
    .about-stat .num { font-size: 1.5rem; font-weight: 800; color: var(--color-text-main, #1F2937); line-height: 1; }
    .about-stat .lbl { font-size: .8rem; color: var(--color-text-muted, #5B6780); margin-top: .25rem; }

    .about-panel { border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 14px; height: 100%; overflow: hidden; background: #fff; }
    .about-panel-head { padding: .8rem 1.15rem; background: #F8FBFD; border-bottom: 1px solid var(--color-border-soft, #EDF1F5); }
    .about-panel-head h6 { margin: 0; font-weight: 800; font-size: .92rem; color: var(--color-primary, #24308F); }

    .about-kv { display: flex; align-items: center; justify-content: space-between; padding: .7rem 1.15rem; border-bottom: 1px solid var(--color-border-soft, #EDF1F5); }
    .about-kv:last-child { border-bottom: 0; }
    .about-kv .k { color: var(--color-text-muted, #5B6780); font-size: .88rem; }

    .about-change-chip { display: inline-flex; align-items: center; gap: .4rem; padding: .3rem .7rem; border-radius: 10px; font-size: .82rem; font-weight: 700; }

    .about-module { display: flex; gap: .85rem; padding: 1rem; border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 12px; height: 100%; background: #fff; transition: border-color .15s, box-shadow .15s; }
    .about-module:hover { border-color: #C7D2FE; box-shadow: 0 6px 18px rgba(36,48,143,0.07); }
    .about-module .m-ic { flex-shrink: 0; width: 40px; height: 40px; border-radius: 10px; background: var(--color-primary-soft, #EEF2FF); color: var(--color-primary, #24308F); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; }
    .about-module .m-title { font-weight: 700; font-size: .9rem; color: var(--color-text-main, #1F2937); margin: 0 0 .15rem; }
    .about-module .m-desc { font-size: .8rem; color: var(--color-text-muted, #5B6780); margin: 0; line-height: 1.5; }

</style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">

        {{-- ── Hero ── --}}
        <div class="col-12">
            <div class="about-hero">
                <div class="about-hero-inner">
                    <div class="about-logo">
                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" onerror="this.style.display='none';this.parentElement.innerHTML='<i class=\'bi bi-lightning-charge-fill\' style=\'font-size:2rem;color:#fff;\'></i>';">
                    </div>
                    <div class="flex-grow-1">
                        <h2>منصة {{ $siteName }}</h2>
                        <p class="tagline">منصة رقمية متكاملة لتنظيم ومراقبة سوق الطاقة وإدارة المشغّلين والمشتركين والفوترة.</p>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <span class="about-pill version"><i class="bi bi-box-seam"></i> v{{ $systemInfo['version'] }}</span>
                            <span class="about-pill"><i class="bi bi-patch-check-fill text-green"></i> معتمدة ومرخصة من سلطة الطاقة</span>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('admin.changelog') }}" class="btn btn-sm btn-hero-light">
                            <i class="bi bi-journal-text me-1"></i> سجل التغييرات
                        </a>
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.versions.index') }}" class="btn btn-sm btn-hero-outline">
                                <i class="bi bi-gear me-1"></i> إدارة الإصدارات
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── إحصائيات (سوبر أدمن) ── --}}
        @if(!empty($stats))
            <div class="col-12">
                <div class="row g-3">
                    @foreach($stats as $key => $value)
                        @php $m = $statMeta[$key] ?? ['label' => $key, 'icon' => 'bi-dot', 'color' => '#24308F']; @endphp
                        <div class="col-6 col-md-4 col-xl-2">
                            <div class="about-stat">
                                <span class="ic" style="background: {{ $m['color'] }}1A; color: {{ $m['color'] }};">
                                    <i class="bi {{ $m['icon'] }}"></i>
                                </span>
                                <div class="num">{{ number_format($value) }}</div>
                                <div class="lbl">{{ $m['label'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── الإصدار الحالي + المعلومات التقنية ── --}}
        <div class="col-lg-7">
            <div class="about-panel">
                <div class="about-panel-head"><h6><i class="bi bi-stars me-2"></i>الإصدار الحالي</h6></div>
                <div class="p-3">
                    @if($currentVersion)
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge {{ $currentVersion->getTypeBadgeClass() }}">{{ $currentVersion->getTypeLabel() }}</span>
                            <span class="badge-role-system">v{{ $currentVersion->version }}</span>
                            <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i>{{ $currentVersion->release_date->format('Y/m/d') }}</span>
                        </div>
                        <h6 class="fw-bold mb-2" style="color: var(--color-text-main, #1F2937);">{{ $currentVersion->title }}</h6>
                        @if($currentVersion->description)
                            <p class="text-secondary mb-3" style="font-size: .86rem; line-height: 1.7;">{{ $currentVersion->description }}</p>
                        @endif
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @if(count($changes['features']))
                                <span class="about-change-chip" style="background:#d4edda;color:#198754;"><i class="bi bi-stars"></i> {{ count($changes['features']) }} ميزة</span>
                            @endif
                            @if(count($changes['improvements']))
                                <span class="about-change-chip" style="background:#cce5ff;color:#0d6efd;"><i class="bi bi-arrow-up-circle"></i> {{ count($changes['improvements']) }} تحسين</span>
                            @endif
                            @if(count($changes['fixes']))
                                <span class="about-change-chip" style="background:#f8d7da;color:#dc3545;"><i class="bi bi-bug"></i> {{ count($changes['fixes']) }} إصلاح</span>
                            @endif
                            @if(count($changes['security']))
                                <span class="about-change-chip" style="background:#fff3cd;color:#b8860b;"><i class="bi bi-shield-check"></i> {{ count($changes['security']) }} أمان</span>
                            @endif
                        </div>
                        <a href="{{ route('admin.versions.show', $currentVersion) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-list-check me-1"></i> عرض تفاصيل الإصدار
                        </a>
                    @else
                        <p class="text-muted mb-0">لا يوجد إصدار حالي مسجّل.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="about-panel">
                <div class="about-panel-head"><h6><i class="bi bi-cpu me-2"></i>المعلومات التقنية</h6></div>
                <div>
                    <div class="about-kv"><span class="k"><i class="bi bi-box me-2"></i>إصدار التطبيق</span><span class="badge-role-system">v{{ $systemInfo['version'] }}</span></div>
                    <div class="about-kv"><span class="k"><i class="bi bi-code-slash me-2"></i>Laravel</span><span class="badge-neutral">v{{ $systemInfo['laravel_version'] }}</span></div>
                    <div class="about-kv"><span class="k"><i class="bi bi-filetype-php me-2"></i>PHP</span><span class="badge-neutral">v{{ $systemInfo['php_version'] }}</span></div>
                    <div class="about-kv">
                        <span class="k"><i class="bi bi-globe me-2"></i>البيئة</span>
                        @if($systemInfo['environment'] === 'production')
                            <span class="badge-status-active">production</span>
                        @else
                            <span class="badge-warning">{{ $systemInfo['environment'] }}</span>
                        @endif
                    </div>
                    <div class="about-kv"><span class="k"><i class="bi bi-clock me-2"></i>المنطقة الزمنية</span><span class="badge-neutral">{{ $systemInfo['timezone'] }}</span></div>
                    <div class="about-kv"><span class="k"><i class="bi bi-translate me-2"></i>اللغة</span><span class="badge-neutral">{{ $systemInfo['locale'] }}</span></div>
                    @if($versionsCount)
                        <div class="about-kv"><span class="k"><i class="bi bi-journals me-2"></i>عدد الإصدارات</span><span class="badge-neutral">{{ $versionsCount }}</span></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── وحدات النظام ── --}}
        <div class="col-12">
            <div class="about-panel">
                <div class="about-panel-head"><h6><i class="bi bi-grid-1x2 me-2"></i>وحدات المنصة</h6></div>
                <div class="p-3">
                    <div class="row g-3">
                        @foreach($modules as $mod)
                            <div class="col-md-6 col-xl-4">
                                <div class="about-module">
                                    <div class="m-ic"><i class="bi {{ $mod['icon'] }}"></i></div>
                                    <div>
                                        <p class="m-title">{{ $mod['title'] }}</p>
                                        <p class="m-desc">{{ $mod['desc'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

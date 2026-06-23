@php
    $siteName = \App\Models\Setting::get('site_name', 'نور');
    $siteLogo = \App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/nour_logo.png');
    $logoUrl  = str_starts_with($siteLogo, 'http') ? $siteLogo : asset($siteLogo);

    $docNo = 'NOUR-REL-' . str_replace('.', '', $version->version) . '-' . $version->release_date->format('Ymd');

    $sections = [
        ['key' => 'features',     'title' => 'الميزات الجديدة', 'color' => '#16a34a', 'bg' => '#eaf7ee'],
        ['key' => 'improvements', 'title' => 'التحسينات',      'color' => '#2563eb', 'bg' => '#e8f0fe'],
        ['key' => 'fixes',        'title' => 'الإصلاحات',       'color' => '#dc2626', 'bg' => '#fdeaea'],
        ['key' => 'security',     'title' => 'تحديثات الأمان',  'color' => '#b8860b', 'bg' => '#fbf3da'],
    ];
    $totalChanges = collect($changes)->flatten()->count();
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير إصدار {{ $version->version }} - {{ $siteName }}</title>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tajawal-font.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        @page { size: A4; margin: 12mm 10mm; }
        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Arial, sans-serif;
            font-size: 13px; line-height: 1.7; color: #1e293b; background: #f1f5f9;
            direction: rtl; -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }

        .doc {
            max-width: 210mm; margin: 20px auto; background: #fff; padding: 32px 38px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-radius: 16px; position: relative; overflow: hidden;
        }
        .doc::before {
            content: ''; position: absolute; top: 0; right: 0; left: 0; height: 5px;
            background: linear-gradient(90deg, #24308F 0%, #10b981 50%, #24308F 100%);
        }

        /* ===== Letterhead ===== */
        .head { display: flex; align-items: center; gap: 16px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
        .head .logo { width: 78px; height: 78px; object-fit: contain; border-radius: 12px; flex-shrink: 0; }
        .head .org { flex-grow: 1; }
        .head .org h1 { font-size: 22px; font-weight: 800; color: #24308F; margin-bottom: 2px; }
        .head .org .sub { font-size: 12.5px; color: #64748b; }
        .head .org .lic { display: inline-flex; align-items: center; gap: 5px; margin-top: 6px; font-size: 11.5px; font-weight: 700; color: #166534; background: #eaf7ee; border: 1px solid #bbe6c9; padding: 3px 10px; border-radius: 30px; }
        .head .org .lic svg { width: 13px; height: 13px; fill: #16a34a; }
        .head .docmeta { text-align: left; flex-shrink: 0; }
        .head .docmeta .no { font-size: 11px; color: #94a3b8; }
        .head .docmeta .no b { color: #475569; font-weight: 700; }
        .head .docmeta .date { font-size: 11px; color: #94a3b8; margin-top: 3px; }

        /* ===== Title bar ===== */
        .title-bar {
            margin: 18px 0; padding: 12px 18px; background: linear-gradient(135deg, #24308F 0%, #11186B 100%);
            color: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: space-between;
        }
        .title-bar h2 { font-size: 17px; font-weight: 800; }
        .title-bar .vpill { background: #fff; color: #24308F; font-weight: 800; font-size: 14px; padding: 4px 14px; border-radius: 30px; }

        /* ===== Meta grid ===== */
        .meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px 18px; margin-bottom: 18px; }
        .meta-item { display: flex; justify-content: space-between; padding: 8px 12px; background: #f8fafc; border: 1px solid #eef2f6; border-radius: 8px; }
        .meta-item .k { color: #64748b; font-weight: 600; font-size: 12px; }
        .meta-item .v { color: #1e293b; font-weight: 700; font-size: 12px; }
        .type-badge { display: inline-block; padding: 1px 10px; border-radius: 5px; font-size: 11px; font-weight: 700; color: #fff; }
        .type-major { background: #dc2626; } .type-minor { background: #f59e0b; } .type-patch { background: #0ea5e9; }
        .cur-yes { color: #16a34a; font-weight: 800; } .cur-no { color: #94a3b8; }

        /* ===== Description ===== */
        .desc { background: #f8fafc; border: 1px solid #eef2f6; border-right: 3px solid #24308F; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 12.5px; color: #334155; }
        .desc .lbl { font-weight: 800; color: #24308F; font-size: 12px; margin-bottom: 4px; }

        /* ===== Change sections ===== */
        .sec { margin-bottom: 16px; border: 1px solid #eef2f6; border-radius: 10px; overflow: hidden; }
        .sec-head { display: flex; align-items: center; justify-content: space-between; padding: 8px 14px; font-weight: 800; font-size: 13px; }
        .sec-head .cnt { font-size: 11px; font-weight: 700; background: rgba(255,255,255,0.6); padding: 1px 9px; border-radius: 20px; }
        .sec-body { padding: 4px 0; }
        .sec ul { list-style: none; }
        .sec li { display: flex; gap: 10px; padding: 6px 16px; font-size: 12.5px; color: #334155; border-bottom: 1px dashed #f1f5f9; }
        .sec li:last-child { border-bottom: none; }
        .sec li .n { flex-shrink: 0; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; color: #fff; }

        /* ===== Signatures ===== */
        .sign-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 26px; }
        .sign-box { text-align: center; }
        .sign-line { border-top: 1.5px solid #94a3b8; margin: 42px 10px 6px; }
        .sign-role { font-size: 12px; font-weight: 700; color: #475569; }
        .sign-hint { font-size: 10.5px; color: #94a3b8; }

        /* ===== Footer ===== */
        .foot { margin-top: 22px; padding-top: 12px; border-top: 2px solid #e2e8f0; text-align: center; font-size: 10.5px; color: #94a3b8; }
        .foot strong { color: #24308F; }

        /* ===== Controls ===== */
        .controls { text-align: center; max-width: 210mm; margin: 18px auto 0; }
        .controls button, .controls a {
            display: inline-flex; align-items: center; gap: 6px; padding: 9px 26px; margin: 0 6px;
            border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none;
            font-family: inherit;
        }
        .btn-print { background: linear-gradient(135deg, #24308F 0%, #11186B 100%); color: #fff; box-shadow: 0 8px 20px rgba(36,48,143,0.25); }
        .btn-back { background: #64748b; color: #fff; }

        @media print {
            body { background: #fff; }
            .doc { box-shadow: none; margin: 0; padding: 0; border-radius: 0; max-width: 100%; }
            .controls, .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="controls no-print">
        <button class="btn-print" onclick="window.print()">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 10a1 1 0 0 1-1-1v-2h8v2a1 1 0 0 1-1 1H5z"/></svg>
            طباعة الوثيقة
        </button>
        <a class="btn-back" href="{{ route('admin.versions.show', $version) }}">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/></svg>
            رجوع
        </a>
    </div>

    <div class="doc">

        {{-- ===== Letterhead ===== --}}
        <div class="head">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="logo" onerror="this.style.display='none'">
            <div class="org">
                <h1>منصة {{ $siteName }}</h1>
                <div class="sub">منصة رقمية متكاملة لتنظيم ومراقبة سوق الطاقة</div>
                <span class="lic">
                    <svg viewBox="0 0 16 16"><path d="M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01-.622-.636zm.287 5.984-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708.708z"/></svg>
                    معتمدة ومرخصة من سلطة الطاقة
                </span>
            </div>
            <div class="docmeta">
                <div class="no">رقم الوثيقة: <b>{{ $docNo }}</b></div>
                <div class="date">تاريخ الطباعة: {{ now()->format('Y/m/d') }}</div>
            </div>
        </div>

        {{-- ===== Title ===== --}}
        <div class="title-bar">
            <h2>تقرير إصدار رسمي — سجل التغييرات</h2>
            <span class="vpill">v{{ $version->version }}</span>
        </div>

        {{-- ===== Meta ===== --}}
        <div class="meta-grid">
            <div class="meta-item"><span class="k">عنوان الإصدار</span><span class="v">{{ $version->title }}</span></div>
            <div class="meta-item"><span class="k">رقم الإصدار</span><span class="v">v{{ $version->version }}</span></div>
            <div class="meta-item">
                <span class="k">نوع الإصدار</span>
                <span class="v"><span class="type-badge type-{{ $version->type }}">{{ $version->getTypeLabel() }}</span></span>
            </div>
            <div class="meta-item"><span class="k">تاريخ الإصدار</span><span class="v">{{ $version->release_date->format('Y/m/d') }}</span></div>
            <div class="meta-item">
                <span class="k">الحالة</span>
                <span class="v">{!! $version->is_current ? '<span class="cur-yes">الإصدار الحالي</span>' : '<span class="cur-no">إصدار سابق</span>' !!}</span>
            </div>
            <div class="meta-item"><span class="k">إجمالي التغييرات</span><span class="v">{{ $totalChanges }} بند</span></div>
        </div>

        {{-- ===== Description ===== --}}
        @if($version->description)
            <div class="desc">
                <div class="lbl">وصف الإصدار</div>
                {{ $version->description }}
            </div>
        @endif

        {{-- ===== Change sections ===== --}}
        @foreach($sections as $s)
            @php $items = $changes[$s['key']] ?? []; @endphp
            @if(count($items))
                <div class="sec">
                    <div class="sec-head" style="background: {{ $s['bg'] }}; color: {{ $s['color'] }};">
                        <span>{{ $s['title'] }}</span>
                        <span class="cnt">{{ count($items) }}</span>
                    </div>
                    <div class="sec-body">
                        <ul>
                            @foreach($items as $i => $item)
                                <li>
                                    <span class="n" style="background: {{ $s['color'] }};">{{ $i + 1 }}</span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- ===== Signatures ===== --}}
        <div class="sign-grid">
            <div class="sign-box">
                <div class="sign-line"></div>
                <div class="sign-role">إعداد — إدارة المنصة</div>
                <div class="sign-hint">{{ optional($version->releasedBy)->name ?? $siteName }}</div>
            </div>
            <div class="sign-box">
                <div class="sign-line"></div>
                <div class="sign-role">اعتماد — مدير المشروع</div>
                <div class="sign-hint">التوقيع والتاريخ</div>
            </div>
            <div class="sign-box">
                <div class="sign-line"></div>
                <div class="sign-role">الختم الرسمي</div>
                <div class="sign-hint">ختم المنصة</div>
            </div>
        </div>

        {{-- ===== Footer ===== --}}
        <div class="foot">
            <strong>منصة {{ $siteName }}</strong> — معتمدة ومرخصة من سلطة الطاقة · صادر إلكترونياً وهو صالح للعرض على الإدارة
            <br>
            تاريخ الطباعة: {{ now()->format('Y/m/d H:i') }} — جميع الحقوق محفوظة © {{ date('Y') }} {{ $siteName }}
        </div>

    </div>

</body>
</html>

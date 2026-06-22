@extends('layouts.admin')

@section('title', 'دليل التسجيل والمصطلحات')

@php
    $breadcrumbTitle = 'دليل التسجيل والمصطلحات';
    $breadcrumbParent = 'الدعم';

    // خطوات التسجيل
    $steps = [
        ['icon' => 'bi-person-vcard',     'title' => 'إكمال ملف المشغّل',        'desc' => 'أدخل بيانات المالك والمشغّل ورقم الهوية والمنطقة الجغرافية كاملة.'],
        ['icon' => 'bi-lightning-charge', 'title' => 'إضافة وحدات التوليد',      'desc' => 'سجّل كل وحدة توليد (محطة) تابعة لك مع موقعها وبياناتها الفنية.'],
        ['icon' => 'bi-cpu',              'title' => 'إضافة المولدات',           'desc' => 'أضف المولدات داخل كل وحدة توليد مع قدراتها ومواصفاتها.'],
        ['icon' => 'bi-people',           'title' => 'إضافة المشتركين وعدّاداتهم', 'desc' => 'سجّل جميع المشتركين وعدّاداتهم — خطوة إلزامية لاستكمال الترخيص.'],
        ['icon' => 'bi-hourglass-split',  'title' => 'متابعة حالة الطلب',        'desc' => 'تابع حالتك: موافقة مبدئية ← (تعديل إن لزم) ← الحصول على الرخصة.'],
    ];

    // قاموس المصطلحات
    $glossary = [
        ['term' => 'مشغّل', 'def' => 'الجهة/الشركة المرخّصة من سلطة الطاقة لتشغيل وإدارة شبكة كهرباء (مولدات) وتزويد المشتركين بالطاقة في منطقة جغرافية محددة.'],
        ['term' => 'وحدة توليد', 'def' => 'محطة/موقع توليد يتبع للمشغّل، تضم مولداً أو أكثر وتغذّي منطقة محددة بالكهرباء.'],
        ['term' => 'مولد', 'def' => 'جهاز توليد الكهرباء (الماكينة) ضمن وحدة التوليد، له قدرة (أمبير/كيلوواط) ومواصفات فنية.'],
        ['term' => 'مشترك', 'def' => 'المستهلك (منزل/محل/منشأة) المشترك بخدمة الكهرباء من المشغّل عبر عدّاد.'],
        ['term' => 'عدّاد', 'def' => 'جهاز قياس استهلاك المشترك للكهرباء، تُسجَّل قراءاته دورياً لإصدار الفواتير.'],
        ['term' => 'قراءة العدّاد', 'def' => 'قيمة استهلاك المشترك المسجّلة من العدّاد في فترة محددة، وهي أساس احتساب الفاتورة.'],
        ['term' => 'فاتورة', 'def' => 'مطالبة مالية للمشترك مبنية على استهلاكه (القراءة) والتعرفة المعتمدة.'],
        ['term' => 'التعرفة', 'def' => 'سعر الكيلوواط/ساعة المعتمد لاحتساب قيمة استهلاك المشترك.'],
        ['term' => 'الموافقة المبدئية', 'def' => 'موافقة أولية من سلطة الطاقة على طلب المشغّل بعد استيفاء البيانات الفنية، تسبق الحصول على الرخصة النهائية.'],
        ['term' => 'الرخصة', 'def' => 'الترخيص النهائي الذي يخوّل المشغّل العمل رسمياً بعد استكمال كل المتطلبات.'],
    ];
@endphp

@push('styles')
<style>
    .video-frame { position: relative; width: 100%; padding-top: 56.25%; border-radius: .6rem; overflow: hidden; background: #0c1050; }
    .video-frame iframe, .video-frame video { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
    .video-empty {
        display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;
        color: #fff; padding: 2rem; gap: .5rem;
    }
    .reg-step {
        display: flex; gap: .9rem; padding: 1rem; border: 1px solid var(--color-border-soft, #EDF1F5);
        border-radius: .6rem; height: 100%; background: #fff;
    }
    .reg-step-num {
        flex-shrink: 0; width: 38px; height: 38px; border-radius: 50%;
        background: var(--color-primary, #24308F); color: #fff; font-weight: 800;
        display: flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    .reg-step .bi { color: var(--color-primary, #24308F); }
    .term-chip {
        display: inline-block; background: var(--badge-primary-bg, #EEF2FF); color: var(--badge-primary-text, #24308F);
        font-weight: 700; padding: .15rem .6rem; border-radius: 30px; font-size: .9rem;
    }
</style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">

        {{-- ── الفيديو التعريفي ── --}}
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="فيديو تعريفي: إجراءات التسجيل" icon="bi-play-circle" />
                <div class="card-body">
                    @if($embedUrl)
                        <div class="video-frame">
                            @if(\Illuminate\Support\Str::endsWith(strtolower($embedUrl), ['.mp4', '.webm', '.ogg']))
                                <video controls preload="metadata" src="{{ $embedUrl }}"></video>
                            @else
                                <iframe src="{{ $embedUrl }}" title="فيديو تعريفي" allowfullscreen
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                            @endif
                        </div>
                    @else
                        <div class="video-frame">
                            <div class="video-empty">
                                <i class="bi bi-camera-video-off" style="font-size:2.5rem;opacity:.6;"></i>
                                <div class="fw-bold">لم يُضَف الفيديو بعد</div>
                                @if(auth()->user()->isSuperAdmin())
                                    <div class="small opacity-75">أضِف رابط الفيديو من <strong>الإعدادات</strong> بالمفتاح <code style="color:#FBBF24;">intro_video_url</code> (رابط YouTube أو ملف MP4).</div>
                                @else
                                    <div class="small opacity-75">سيتم إضافة الفيديو التعريفي قريباً.</div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </x-admin.card>
        </div>

        {{-- ── خطوات التسجيل ── --}}
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="خطوات التسجيل" icon="bi-list-ol" />
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($steps as $i => $step)
                            <div class="col-md-6 col-xl-4">
                                <div class="reg-step">
                                    <div class="reg-step-num">{{ $i + 1 }}</div>
                                    <div>
                                        <div class="fw-bold mb-1"><i class="bi {{ $step['icon'] }} me-1"></i>{{ $step['title'] }}</div>
                                        <div class="text-muted small">{{ $step['desc'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-admin.card>
        </div>

        {{-- ── أهمية إضافة المشتركين والعدّادات ── --}}
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-start gap-2 mb-0" role="alert">
                <i class="bi bi-exclamation-triangle-fill mt-1 fs-5"></i>
                <div>
                    <strong>أهمية إضافة المشتركين وعدّاداتهم:</strong>
                    يُعدّ تسجيل جميع المشتركين وعدّاداتهم على المنصة <strong>إلزامياً</strong> وفق قرارات الحكومة والنيابة العامة،
                    لضمان الشفافية والرقابة على سوق الطاقة وحماية حقوق المشتركين.
                    عدم استكمال هذه البيانات قد يؤخّر الحصول على الموافقة المبدئية أو الرخصة.
                </div>
            </div>
        </div>

        {{-- ── قاموس المصطلحات ── --}}
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="قاموس المصطلحات" icon="bi-journal-text" />
                <div class="card-body">
                    <div class="accordion" id="glossaryAccordion">
                        @foreach($glossary as $i => $item)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }}" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#term{{ $i }}">
                                        <span class="term-chip">{{ $item['term'] }}</span>
                                    </button>
                                </h2>
                                <div id="term{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}"
                                     data-bs-parent="#glossaryAccordion">
                                    <div class="accordion-body text-secondary">
                                        {{ $item['def'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-admin.card>
        </div>

    </div>
</div>
@endsection

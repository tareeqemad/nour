@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'الإحصائيات - ' . $siteName)
@section('description', 'إحصائيات شاملة عن المشغلين والمولدات في محافظات غزة')

@push('styles')
<style>
/* ===== Stats Page — Nour Design System ===== */

/* Hero */
.st-hero {
    background: linear-gradient(155deg, #1a2478 0%, #11186B 40%, #0c1050 100%);
    padding: 3rem 0 2.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.st-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 25% 35%, rgba(255,255,255,0.06) 0%, transparent 50%),
        radial-gradient(circle at 75% 65%, rgba(255,255,255,0.04) 0%, transparent 50%);
    pointer-events: none;
}

.st-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
    background-size: 60px 60px;
    pointer-events: none;
}

.st-hero-inner {
    position: relative;
    z-index: 1;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.st-hero h1 {
    font-size: 2.25rem;
    font-weight: 900;
    color: #fff;
    margin-bottom: 0.5rem;
}

.st-hero h1 i { color: #FBBF24; margin-left: 0.4rem; }

.st-hero p {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.7);
    font-weight: 500;
    margin: 0;
    max-width: 500px;
    margin: 0 auto;
}

/* ---- KPI Cards (inside hero) ---- */
.st-kpi-grid {
    max-width: 1200px;
    margin: 2rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    position: relative;
    z-index: 1;
}

.st-kpi {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 0.75rem;
    padding: 1.5rem 1rem;
    text-align: center;
    backdrop-filter: blur(6px);
    transition: all 0.25s;
}

.st-kpi:hover {
    background: rgba(255,255,255,0.14);
    transform: translateY(-3px);
}

.st-kpi-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.65rem;
    font-size: 1.2rem;
}

.st-kpi-icon.blue { background: rgba(59,130,246,0.2); color: #60A5FA; }
.st-kpi-icon.green { background: rgba(16,185,129,0.2); color: #34D399; }
.st-kpi-icon.amber { background: rgba(251,191,36,0.2); color: #FBBF24; }
.st-kpi-icon.cyan { background: rgba(14,165,233,0.2); color: #38BDF8; }

.st-kpi-val {
    font-size: 2rem;
    font-weight: 900;
    color: #fff;
    line-height: 1.2;
    margin-bottom: 0.15rem;
}

.st-kpi-label {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.6);
    font-weight: 500;
}

/* ---- Sections ---- */
.st-section {
    padding: 3rem 0;
}

.st-section-alt { background: #F8FAFC; }

.st-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.st-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1F2937;
    text-align: center;
    margin-bottom: 0.3rem;
}

.st-subtitle {
    font-size: 0.95rem;
    color: #5B6780;
    text-align: center;
    margin-bottom: 2rem;
    font-weight: 500;
}

/* ---- Governorate Cards ---- */
.gov-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
}

.gov-card {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #E5E7EB;
    border-right: 3px solid var(--gov-color, #24308F);
    padding: 1.5rem 1rem;
    text-align: center;
    transition: all 0.2s;
}

.gov-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}

.gov-val {
    font-size: 2.25rem;
    font-weight: 900;
    color: var(--gov-color, #24308F);
    line-height: 1.2;
    margin-bottom: 0.2rem;
}

.gov-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #3B4863;
}

/* ---- Info Cards ---- */
.info-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.info-card {
    background: #F8FAFC;
    border: 1px solid #E5E7EB;
    border-radius: 0.75rem;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.2s;
}

.info-card:hover {
    border-color: #24308F;
    box-shadow: 0 4px 16px rgba(36,48,143,0.08);
}

.info-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    background: #EEF2FF;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
}

.info-icon i { font-size: 1.2rem; color: #24308F; }

.info-card h3 {
    font-size: 0.95rem;
    font-weight: 800;
    color: #1F2937;
    margin-bottom: 0.3rem;
}

.info-card p {
    font-size: 0.82rem;
    color: #5B6780;
    font-weight: 500;
    line-height: 1.5;
    margin: 0;
}

/* ---- CTA ---- */
.st-cta {
    padding: 3rem 0;
    background: linear-gradient(135deg, #24308F 0%, #11186B 100%);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.st-cta::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
    background-size: 50px 50px;
    pointer-events: none;
}

.st-cta h2 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 0.4rem;
    position: relative; z-index: 1;
}

.st-cta p {
    font-size: 0.95rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 1.25rem;
    font-weight: 500;
    position: relative; z-index: 1;
}

.st-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1.75rem;
    background: #fff;
    color: #24308F;
    border-radius: 0.6rem;
    font-family: 'Tajawal', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
    position: relative; z-index: 1;
    box-shadow: 0 4px 14px rgba(0,0,0,0.15);
}

.st-cta-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    color: #24308F;
}

/* ---- Animations ---- */
.animate-in {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s, transform 0.5s;
}

.animate-in.visible {
    opacity: 1;
    transform: translateY(0);
}

/* ---- Responsive ---- */
@media (max-width: 1024px) {
    .st-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .info-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .st-hero h1 { font-size: 1.6rem; }
    .st-hero p { font-size: 0.95rem; }
    .st-kpi-grid { grid-template-columns: 1fr 1fr; gap: 0.6rem; }
    .st-kpi-val { font-size: 1.5rem; }
    .st-kpi { padding: 1rem 0.75rem; }
    .info-grid { grid-template-columns: 1fr; }
    .gov-grid { grid-template-columns: 1fr 1fr; }
    .st-cta h2 { font-size: 1.3rem; }
}
</style>
@endpush

@section('content')

    {{-- ===== Hero + KPI ===== --}}
    <section class="st-hero">
        <div class="st-hero-inner">
            <h1><i class="bi bi-bar-chart-fill"></i> الإحصائيات الشاملة</h1>
            <p>نظرة شاملة على بيانات قطاع الطاقة في محافظات غزة</p>
        </div>

        <div class="st-kpi-grid">
            <div class="st-kpi">
                <div class="st-kpi-icon blue"><i class="bi bi-buildings"></i></div>
                <div class="st-kpi-val">{{ number_format($stats['total_operators']) }}</div>
                <div class="st-kpi-label">مشغل نشط</div>
            </div>
            <div class="st-kpi">
                <div class="st-kpi-icon green"><i class="bi bi-lightning-charge"></i></div>
                <div class="st-kpi-val">{{ number_format($stats['total_generators']) }}</div>
                <div class="st-kpi-label">مولد مسجل</div>
            </div>
            <div class="st-kpi">
                <div class="st-kpi-icon amber"><i class="bi bi-activity"></i></div>
                <div class="st-kpi-val">{{ number_format($stats['total_capacity'] / 1000, 1) }}K</div>
                <div class="st-kpi-label">كيلو فولت أمبير (KVA)</div>
            </div>
            <div class="st-kpi">
                <div class="st-kpi-icon cyan"><i class="bi bi-check-circle"></i></div>
                <div class="st-kpi-val">{{ number_format($stats['active_generators']) }}</div>
                <div class="st-kpi-label">مولد نشط</div>
            </div>
        </div>
    </section>

    {{-- ===== Governorate Breakdown ===== --}}
    @if($stats['operators_by_governorate']->count() > 0)
    <section class="st-section">
        <div class="st-container">
            <h2 class="st-title">توزيع المشغلين حسب المحافظة</h2>
            <p class="st-subtitle">عدد المشغلين النشطين في كل محافظة</p>

            <div class="gov-grid">
                @php
                    $govColors = ['#24308F', '#10B981', '#F59E0B', '#EF4444', '#0EA5E9', '#8B5CF6'];
                    $i = 0;
                @endphp
                @foreach($stats['operators_by_governorate'] as $governorate => $count)
                <div class="gov-card animate-in" style="--gov-color: {{ $govColors[$i % count($govColors)] }}">
                    <div class="gov-val">{{ number_format($count) }}</div>
                    <div class="gov-name">{{ $governorate }}</div>
                </div>
                @php $i++; @endphp
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ===== Info ===== --}}
    <section class="st-section st-section-alt">
        <div class="st-container">
            <h2 class="st-title">ما يميز المنصة</h2>
            <p class="st-subtitle">بيانات دقيقة وأدوات فعّالة</p>

            <div class="info-grid">
                <div class="info-card animate-in">
                    <div class="info-icon"><i class="bi bi-arrow-repeat"></i></div>
                    <h3>تحديث مستمر</h3>
                    <p>البيانات محدثة ومتابعة بشكل دوري ومنتظم</p>
                </div>
                <div class="info-card animate-in">
                    <div class="info-icon"><i class="bi bi-shield-check"></i></div>
                    <h3>بيانات موثوقة</h3>
                    <p>تم التحقق من جميع البيانات وتوثيقها رسمياً</p>
                </div>
                <div class="info-card animate-in">
                    <div class="info-icon"><i class="bi bi-geo-alt"></i></div>
                    <h3>تغطية شاملة</h3>
                    <p>جميع محافظات قطاع غزة بدون استثناء</p>
                </div>
                <div class="info-card animate-in">
                    <div class="info-icon"><i class="bi bi-telephone"></i></div>
                    <h3>تواصل مباشر</h3>
                    <p>تواصل مع المشغلين مباشرة من المنصة</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== CTA ===== --}}
    <section class="st-cta">
        <h2>استكشف المواقع على الخريطة</h2>
        <p>شاهد مواقع وحدات التوليد والمشغلين على خريطة تفاعلية</p>
        <a href="{{ route('front.map') }}" class="st-cta-btn">
            <i class="bi bi-geo-alt-fill"></i>
            استكشف الخريطة
        </a>
    </section>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var els = document.querySelectorAll('.animate-in');
    if (!els.length) return;

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    els.forEach(function(el) { observer.observe(el); });
});
</script>
@endpush

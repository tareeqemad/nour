@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'من نحن - ' . $siteName)
@section('description', 'تعرف على منصة ' . $siteName . ' ورسالتنا وأهدافنا')

@push('styles')
<style>
/* ===== About Page — Nour Design System ===== */

/* Hero */
.ab-hero {
    background: linear-gradient(155deg, #1a2478 0%, #11186B 40%, #0c1050 100%);
    padding: 3.5rem 0 3rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.ab-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 30% 40%, rgba(255,255,255,0.06) 0%, transparent 50%),
        radial-gradient(circle at 70% 60%, rgba(255,255,255,0.04) 0%, transparent 50%);
    pointer-events: none;
}

.ab-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
    background-size: 60px 60px;
    pointer-events: none;
}

.ab-hero-inner {
    position: relative; z-index: 1;
    max-width: 700px;
    margin: 0 auto;
    padding: 0 2rem;
}

.ab-hero h1 {
    font-size: 2.5rem;
    font-weight: 900;
    color: #fff;
    margin-bottom: 0.75rem;
}

.ab-hero h1 i { color: #FBBF24; margin-left: 0.4rem; }

.ab-hero p {
    font-size: 1.15rem;
    color: rgba(255,255,255,0.75);
    font-weight: 500;
    line-height: 1.8;
    margin: 0;
}

/* Sections */
.ab-section { padding: 3.5rem 0; }
.ab-section-alt { background: #F8FAFC; }

.ab-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 2rem;
}

.ab-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #1F2937;
    text-align: center;
    margin-bottom: 0.3rem;
}

.ab-subtitle {
    font-size: 0.95rem;
    color: #5B6780;
    text-align: center;
    margin-bottom: 2.5rem;
    font-weight: 500;
}

/* Who we are - text block */
.ab-text-block {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.ab-text-block p {
    font-size: 1.05rem;
    color: #3B4863;
    line-height: 1.9;
    font-weight: 500;
    margin-bottom: 1rem;
}

.ab-text-block p:last-child { margin-bottom: 0; }

/* Mission list */
.ab-mission-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    max-width: 800px;
    margin: 2rem auto 0;
}

.ab-mission-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #E5E7EB;
    border-right: 2.5px solid #24308F;
    transition: box-shadow 0.2s;
}

.ab-mission-item:hover { box-shadow: 0 3px 12px rgba(0,0,0,0.05); }

.ab-mission-item i {
    color: #24308F;
    font-size: 1.1rem;
    margin-top: 2px;
    flex-shrink: 0;
}

.ab-mission-item span {
    font-size: 0.92rem;
    font-weight: 600;
    color: #1F2937;
    line-height: 1.5;
}

/* Goals grid */
.ab-goals-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}

.ab-goal {
    background: #fff;
    border: 1px solid #E5E7EB;
    border-top: 2.5px solid #24308F;
    border-radius: 0.75rem;
    padding: 1.75rem 1.25rem;
    text-align: center;
    transition: all 0.2s;
}

.ab-goal:hover {
    box-shadow: 0 4px 16px rgba(36,48,143,0.08);
    transform: translateY(-2px);
}

.ab-goal-icon {
    width: 50px; height: 50px;
    border-radius: 14px;
    background: #EEF2FF;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.85rem;
}

.ab-goal-icon i { font-size: 1.3rem; color: #24308F; }

.ab-goal h3 {
    font-size: 1rem;
    font-weight: 800;
    color: #1F2937;
    margin-bottom: 0.3rem;
}

.ab-goal p {
    font-size: 0.82rem;
    color: #5B6780;
    font-weight: 500;
    line-height: 1.5;
    margin: 0;
}

/* Services */
.ab-services-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.ab-service {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem;
    background: #F8FAFC;
    border: 1px solid #E5E7EB;
    border-radius: 0.75rem;
    transition: all 0.2s;
}

.ab-service:hover {
    border-color: #24308F;
    box-shadow: 0 4px 16px rgba(36,48,143,0.06);
}

.ab-service-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: #EEF2FF;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ab-service-icon i { font-size: 1.15rem; color: #24308F; }

.ab-service-text h4 {
    font-size: 0.95rem;
    font-weight: 800;
    color: #1F2937;
    margin-bottom: 0.2rem;
}

.ab-service-text p {
    font-size: 0.82rem;
    color: #5B6780;
    font-weight: 500;
    line-height: 1.5;
    margin: 0;
}

/* CTA */
.ab-cta {
    padding: 3rem 0;
    background: linear-gradient(135deg, #24308F 0%, #11186B 100%);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.ab-cta::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
    background-size: 50px 50px;
    pointer-events: none;
}

.ab-cta h2 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 0.4rem;
    position: relative; z-index: 1;
}

.ab-cta p {
    font-size: 0.95rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 1.25rem;
    font-weight: 500;
    position: relative; z-index: 1;
}

.ab-cta-btns {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    flex-wrap: wrap;
    position: relative; z-index: 1;
}

.ab-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1.5rem;
    border-radius: 0.6rem;
    font-family: 'Tajawal', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
}

.ab-cta-btn-primary {
    background: #fff;
    color: #24308F;
    box-shadow: 0 4px 14px rgba(0,0,0,0.15);
}

.ab-cta-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    color: #24308F;
}

.ab-cta-btn-ghost {
    background: rgba(255,255,255,0.1);
    color: #fff;
    border: 1.5px solid rgba(255,255,255,0.25);
}

.ab-cta-btn-ghost:hover {
    background: rgba(255,255,255,0.18);
    color: #fff;
}

/* Animations */
.anim {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s, transform 0.5s;
}

.anim.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Responsive */
@media (max-width: 1024px) {
    .ab-goals-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .ab-hero h1 { font-size: 1.8rem; }
    .ab-hero p { font-size: 1rem; }
    .ab-mission-grid { grid-template-columns: 1fr; }
    .ab-goals-grid { grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    .ab-services-grid { grid-template-columns: 1fr; }
    .ab-cta h2 { font-size: 1.3rem; }
}
</style>
@endpush

@section('content')

    {{-- ===== Hero ===== --}}
    <section class="ab-hero">
        <div class="ab-hero-inner">
            <h1><i class="bi bi-info-circle-fill"></i> من نحن</h1>
            <p>
                {{ $siteName }} منصة رقمية متكاملة لتنظيم ومراقبة سوق الطاقة في محافظات غزة —
                تربط المشغلين والمشتركين والجهات الرقابية في مكان واحد
            </p>
        </div>
    </section>

    {{-- ===== About + Mission ===== --}}
    <section class="ab-section">
        <div class="ab-container">
            <h2 class="ab-title">رؤيتنا ورسالتنا</h2>
            <p class="ab-subtitle">نعمل على تطوير بيئة رقمية متقدمة لإدارة قطاع الطاقة</p>

            <div class="ab-text-block anim">
                <p>
                    نسعى لتوفير منصة رقمية شاملة وموثوقة تسهّل التواصل بين جميع الأطراف —
                    المشغلين، المشتركين، والجهات الرقابية — مع ضمان الشفافية والكفاءة في إدارة سوق الطاقة.
                </p>
            </div>

            <div class="ab-mission-grid">
                <div class="ab-mission-item anim">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>تسهيل الوصول إلى المعلومات والخدمات لجميع المستخدمين</span>
                </div>
                <div class="ab-mission-item anim">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>ضمان دقة وموثوقية البيانات المقدمة</span>
                </div>
                <div class="ab-mission-item anim">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>تعزيز الشفافية والتواصل بين الأطراف</span>
                </div>
                <div class="ab-mission-item anim">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>تطوير وتحسين الخدمات بشكل مستمر</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Goals ===== --}}
    <section class="ab-section ab-section-alt">
        <div class="ab-container">
            <h2 class="ab-title">أهدافنا</h2>
            <p class="ab-subtitle">نعمل لتحقيق أهداف واضحة تخدم المجتمع</p>

            <div class="ab-goals-grid">
                <div class="ab-goal anim">
                    <div class="ab-goal-icon"><i class="bi bi-globe2"></i></div>
                    <h3>الشمولية</h3>
                    <p>تغطية جميع محافظات غزة والمشغلين النشطين</p>
                </div>
                <div class="ab-goal anim">
                    <div class="ab-goal-icon"><i class="bi bi-shield-check"></i></div>
                    <h3>الموثوقية</h3>
                    <p>ضمان دقة وموثوقية جميع البيانات المقدمة</p>
                </div>
                <div class="ab-goal anim">
                    <div class="ab-goal-icon"><i class="bi bi-phone"></i></div>
                    <h3>سهولة الاستخدام</h3>
                    <p>واجهة بسيطة تعمل على جميع الأجهزة</p>
                </div>
                <div class="ab-goal anim">
                    <div class="ab-goal-icon"><i class="bi bi-chat-dots"></i></div>
                    <h3>التواصل</h3>
                    <p>تسهيل التواصل بين المشتركين والمشغلين</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Services ===== --}}
    <section class="ab-section">
        <div class="ab-container">
            <h2 class="ab-title">خدماتنا</h2>
            <p class="ab-subtitle">مجموعة متكاملة من الأدوات الرقمية</p>

            <div class="ab-services-grid">
                <div class="ab-service anim">
                    <div class="ab-service-icon"><i class="bi bi-geo-alt"></i></div>
                    <div class="ab-service-text">
                        <h4>خريطة تفاعلية</h4>
                        <p>استكشف مواقع وحدات التوليد والمشغلين على خريطة تفاعلية شاملة</p>
                    </div>
                </div>
                <div class="ab-service anim">
                    <div class="ab-service-icon"><i class="bi bi-bar-chart"></i></div>
                    <div class="ab-service-text">
                        <h4>إحصائيات شاملة</h4>
                        <p>بيانات وأرقام مفصلة عن المشغلين والمولدات والقدرات</p>
                    </div>
                </div>
                <div class="ab-service anim">
                    <div class="ab-service-icon"><i class="bi bi-chat-left-text"></i></div>
                    <div class="ab-service-text">
                        <h4>شكاوي ومقترحات</h4>
                        <p>أرسل شكاويك وتابع حالتها بسهولة وشفافية</p>
                    </div>
                </div>
                <div class="ab-service anim">
                    <div class="ab-service-icon"><i class="bi bi-telephone"></i></div>
                    <div class="ab-service-text">
                        <h4>معلومات الاتصال</h4>
                        <p>بيانات التواصل الكاملة لكل مشغل ووحدة توليد</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== CTA ===== --}}
    <section class="ab-cta">
        <h2>ابدأ الاستكشاف الآن</h2>
        <p>تعرف على المشغلين وتصفح البيانات بكل سهولة</p>
        <div class="ab-cta-btns">
            <a href="{{ route('front.map') }}" class="ab-cta-btn ab-cta-btn-primary">
                <i class="bi bi-geo-alt-fill"></i> استكشف الخريطة
            </a>
            <a href="{{ route('front.stats') }}" class="ab-cta-btn ab-cta-btn-ghost">
                <i class="bi bi-bar-chart-fill"></i> الإحصائيات
            </a>
        </div>
    </section>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var els = document.querySelectorAll('.anim');
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

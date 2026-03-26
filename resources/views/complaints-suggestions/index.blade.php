@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'الشكاوي والمقترحات - ' . $siteName)

@push('styles')
<style>
/* ===== Complaints Page — Nour Design System ===== */

.cs-hero {
    background: linear-gradient(155deg, #1a2478 0%, #11186B 40%, #0c1050 100%);
    padding: 3rem 0 2.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.cs-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(circle at 25% 35%, rgba(255,255,255,0.06) 0%, transparent 50%),
        radial-gradient(circle at 75% 65%, rgba(255,255,255,0.04) 0%, transparent 50%);
    pointer-events: none;
}

.cs-hero::after {
    content: '';
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
    background-size: 60px 60px;
    pointer-events: none;
}

.cs-hero-inner {
    position: relative; z-index: 1;
    max-width: 600px; margin: 0 auto; padding: 0 2rem;
}

.cs-hero h1 {
    font-size: 2.25rem; font-weight: 900; color: #fff; margin-bottom: 0.5rem;
}

.cs-hero h1 i { color: #FBBF24; margin-left: 0.4rem; }

.cs-hero p {
    font-size: 1.05rem; color: rgba(255,255,255,0.7); font-weight: 500; margin: 0;
}

/* Cards */
.cs-cards {
    max-width: 800px;
    margin: -1.5rem auto 3rem;
    padding: 0 1.5rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    position: relative;
    z-index: 2;
}

.cs-card {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #E5E7EB;
    border-top: 2.5px solid #24308F;
    padding: 2rem 1.5rem;
    text-align: center;
    text-decoration: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transition: all 0.25s;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.cs-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(36,48,143,0.12);
    border-top-color: #2330B3;
}

.cs-card-icon {
    width: 64px; height: 64px;
    border-radius: 16px;
    background: #EEF2FF;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
    transition: all 0.25s;
}

.cs-card:hover .cs-card-icon {
    background: #24308F;
}

.cs-card-icon i {
    font-size: 1.6rem;
    color: #24308F;
    transition: color 0.25s;
}

.cs-card:hover .cs-card-icon i {
    color: #fff;
}

.cs-card-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: #1F2937;
    margin-bottom: 0.5rem;
}

.cs-card-desc {
    font-size: 0.88rem;
    color: #5B6780;
    font-weight: 500;
    line-height: 1.6;
    margin: 0;
}

.cs-card-arrow {
    margin-top: 1rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: #24308F;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    opacity: 0;
    transform: translateY(4px);
    transition: all 0.25s;
}

.cs-card:hover .cs-card-arrow {
    opacity: 1;
    transform: translateY(0);
}

/* Info */
.cs-info {
    max-width: 800px;
    margin: 0 auto 3rem;
    padding: 0 1.5rem;
    text-align: center;
}

.cs-info-text {
    font-size: 0.9rem;
    color: #5B6780;
    font-weight: 500;
    line-height: 1.7;
    background: #F8FAFC;
    border: 1px solid #E5E7EB;
    border-radius: 0.75rem;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.cs-info-text i {
    color: #24308F;
    font-size: 1.2rem;
    flex-shrink: 0;
}

@media (max-width: 640px) {
    .cs-hero h1 { font-size: 1.6rem; }
    .cs-cards { grid-template-columns: 1fr; }
    .cs-card { padding: 1.5rem 1.25rem; }
}
</style>
@endpush

@section('content')

    {{-- Hero --}}
    <section class="cs-hero">
        <div class="cs-hero-inner">
            <h1><i class="bi bi-chat-dots-fill"></i> الشكاوي والمقترحات</h1>
            <p>نقدّر ملاحظاتك ونسعى لتحسين خدماتنا باستمرار</p>
        </div>
    </section>

    {{-- Cards --}}
    <div class="cs-cards">
        <a href="{{ route('complaints-suggestions.create') }}" class="cs-card">
            <div class="cs-card-icon"><i class="bi bi-chat-left-text"></i></div>
            <div class="cs-card-title">تقديم شكوى أو مقترح</div>
            <p class="cs-card-desc">قدّم شكواك أو اقتراحك وسنراجعه ونرد عليك بأسرع وقت</p>
            <div class="cs-card-arrow"><i class="bi bi-arrow-left"></i> ابدأ الآن</div>
        </a>

        <a href="{{ route('complaints-suggestions.track') }}" class="cs-card">
            <div class="cs-card-icon"><i class="bi bi-search"></i></div>
            <div class="cs-card-title">متابعة طلب سابق</div>
            <p class="cs-card-desc">تابع حالة شكواك أو مقترحك السابق برمز التتبع</p>
            <div class="cs-card-arrow"><i class="bi bi-arrow-left"></i> تتبع الآن</div>
        </a>
    </div>

    {{-- Info --}}
    <div class="cs-info">
        <div class="cs-info-text">
            <i class="bi bi-shield-check"></i>
            جميع الشكاوي والمقترحات تُعالج بسرية تامة ويتم الرد عليها خلال أيام عمل قليلة
        </div>
    </div>

@endsection

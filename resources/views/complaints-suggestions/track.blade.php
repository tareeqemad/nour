@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'متابعة الطلب - ' . $siteName)

@push('styles')
<style>
/* ===== Track Page — Nour Design System ===== */

.tk-hero {
    background: linear-gradient(155deg, #1a2478 0%, #11186B 40%, #0c1050 100%);
    padding: 2.5rem 0 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.tk-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(circle at 25% 35%, rgba(255,255,255,0.06) 0%, transparent 50%),
        radial-gradient(circle at 75% 65%, rgba(255,255,255,0.04) 0%, transparent 50%);
    pointer-events: none;
}

.tk-hero::after {
    content: '';
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
    background-size: 60px 60px;
    pointer-events: none;
}

.tk-hero-inner { position: relative; z-index: 1; max-width: 600px; margin: 0 auto; padding: 0 2rem; }
.tk-hero h1 { font-size: 2rem; font-weight: 900; color: #fff; margin-bottom: 0.4rem; }
.tk-hero h1 i { color: #FBBF24; margin-left: 0.4rem; }
.tk-hero p { font-size: 1rem; color: rgba(255,255,255,0.7); font-weight: 500; margin: 0; }

/* Search */
.tk-wrap { max-width: 800px; margin: -1.5rem auto 2rem; padding: 0 1.5rem; position: relative; z-index: 2; }

.tk-search {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #E5E7EB;
    border-top: 2.5px solid #24308F;
    padding: 1.25rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}

.tk-search form {
    display: flex;
    gap: 0.75rem;
}

.tk-search-input {
    flex: 1;
    height: 44px;
    padding: 0 0.85rem;
    border: 1.5px solid #D9E0EA;
    border-radius: 0.5rem;
    font-family: 'Tajawal', sans-serif;
    font-size: 0.95rem;
    font-weight: 500;
    color: #1F2937;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.tk-search-input::placeholder { color: #98A2B3; }
.tk-search-input:focus { border-color: #24308F; box-shadow: 0 0 0 3px rgba(36,48,143,0.08); }

.tk-search-btn {
    height: 44px;
    padding: 0 1.5rem;
    background: #24308F;
    color: #fff;
    border: none;
    border-radius: 0.5rem;
    font-family: 'Tajawal', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: background 0.2s;
    white-space: nowrap;
}

.tk-search-btn:hover { background: #2330B3; }

/* Alerts */
.tk-alert {
    max-width: 800px;
    margin: 0 auto 1rem;
    padding: 0 1.5rem;
}

.tk-alert-box {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.88rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tk-alert-success { background: #D1FAE5; color: #065F46; border-right: 3px solid #10B981; }
.tk-alert-error { background: #FEF2F2; color: #EF4444; border-right: 3px solid #EF4444; }

/* Result card */
.tk-result { max-width: 800px; margin: 0 auto 3rem; padding: 0 1.5rem; }

.tk-card {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #E5E7EB;
    overflow: hidden;
}

/* Tracking code banner */
.tk-code {
    background: #EEF2FF;
    padding: 1.25rem;
    text-align: center;
    border-bottom: 1px solid #E5E7EB;
}

.tk-code-label { font-size: 0.78rem; color: #5B6780; font-weight: 600; margin-bottom: 0.2rem; }

.tk-code-val {
    font-size: 1.75rem;
    font-weight: 900;
    color: #24308F;
    letter-spacing: 2px;
}

/* Header row */
.tk-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #E5E7EB;
    background: #F8FBFD;
}

.tk-header-title { font-size: 1rem; font-weight: 800; color: #1F2937; }

.tk-badge {
    padding: 0.3rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.78rem;
    font-weight: 700;
}

.tk-badge-pending { background: #FEF3C7; color: #92400E; }
.tk-badge-in_progress { background: #DBEAFE; color: #2563EB; }
.tk-badge-resolved { background: #D1FAE5; color: #059669; }
.tk-badge-rejected { background: #FEE2E2; color: #991B1B; }

/* Body */
.tk-body { padding: 1.25rem; }

.tk-section-title {
    font-size: 0.88rem;
    font-weight: 800;
    color: #1F2937;
    margin: 1.25rem 0 0.75rem;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid #E5E7EB;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.tk-section-title:first-child { margin-top: 0; }
.tk-section-title i { color: #24308F; font-size: 0.9rem; }

.tk-info-row {
    display: flex;
    gap: 1.25rem;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
}

.tk-info-item { flex: 1; min-width: 180px; }
.tk-info-label { font-size: 0.78rem; color: #98A2B3; font-weight: 600; margin-bottom: 0.15rem; }
.tk-info-val { font-size: 0.92rem; color: #1F2937; font-weight: 700; }

/* Message box */
.tk-msg {
    background: #F8FAFC;
    border: 1px solid #E5E7EB;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 0.75rem;
}

.tk-msg-label { font-size: 0.78rem; color: #98A2B3; font-weight: 600; margin-bottom: 0.35rem; }
.tk-msg-text { font-size: 0.92rem; color: #1F2937; line-height: 1.7; font-weight: 500; }

/* Response */
.tk-response {
    background: #EEF2FF;
    border-right: 3px solid #24308F;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
}

.tk-response-label { font-size: 0.88rem; font-weight: 800; color: #24308F; margin-bottom: 0.4rem; }
.tk-response-text { font-size: 0.92rem; color: #1F2937; line-height: 1.7; font-weight: 500; }
.tk-response-date { font-size: 0.75rem; color: #98A2B3; margin-top: 0.5rem; }

/* Pending response */
.tk-pending {
    background: #FEF3C7;
    border-right: 3px solid #F59E0B;
    border-radius: 0.5rem;
    padding: 0.85rem 1rem;
    margin-top: 1rem;
    font-size: 0.88rem;
    color: #92400E;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

/* No result */
.tk-empty {
    text-align: center;
    padding: 3rem 1.5rem;
    background: #F8FAFC;
    border: 1px solid #E5E7EB;
    border-radius: 0.75rem;
}

.tk-empty i { font-size: 2.5rem; color: #98A2B3; display: block; margin-bottom: 0.5rem; }
.tk-empty p { font-size: 0.92rem; color: #5B6780; font-weight: 600; margin: 0; }

/* Image */
.tk-image img {
    max-width: 100%;
    border-radius: 0.5rem;
    border: 1px solid #E5E7EB;
    margin-top: 0.5rem;
}

@media (max-width: 640px) {
    .tk-hero h1 { font-size: 1.5rem; }
    .tk-search form { flex-direction: column; }
    .tk-header { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
    .tk-code-val { font-size: 1.3rem; }
}
</style>
@endpush

@section('content')

    <section class="tk-hero">
        <div class="tk-hero-inner">
            <h1><i class="bi bi-search"></i> متابعة الطلب</h1>
            <p>تابع حالة شكواك أو مقترحك برمز التتبع</p>
        </div>
    </section>

    <div class="tk-wrap">
        <div class="tk-search">
            <form method="POST" action="{{ route('complaints-suggestions.search') }}">
                @csrf
                <input type="text" name="code" id="trackingCodeInput" class="tk-search-input" placeholder="أدخل رمز التتبع..." value="{{ $code ?? '' }}" required>
                <button type="submit" class="tk-search-btn"><i class="bi bi-search"></i> بحث</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="tk-alert"><div class="tk-alert-box tk-alert-success"><i class="bi bi-check-circle"></i> {{ session('success') }}</div></div>
    @endif

    @if(session('error'))
        <div class="tk-alert"><div class="tk-alert-box tk-alert-error"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div></div>
    @endif

    <div class="tk-result">
        @if($complaintSuggestion ?? false)
            <div class="tk-card">
                <div class="tk-code">
                    <div class="tk-code-label">رمز التتبع</div>
                    <div class="tk-code-val">{{ $complaintSuggestion->tracking_code }}</div>
                </div>

                <div class="tk-header">
                    <div class="tk-header-title">بيانات الطلب</div>
                    <span class="tk-badge tk-badge-{{ $complaintSuggestion->status }}">{{ $complaintSuggestion->status_label }}</span>
                </div>

                <div class="tk-body">
                    <div class="tk-section-title"><i class="bi bi-person"></i> مقدم الطلب</div>
                    <div class="tk-info-row">
                        <div class="tk-info-item">
                            <div class="tk-info-label">الاسم</div>
                            <div class="tk-info-val">{{ $complaintSuggestion->name }}</div>
                        </div>
                        <div class="tk-info-item">
                            <div class="tk-info-label">الهاتف</div>
                            <div class="tk-info-val">{{ $complaintSuggestion->phone }}</div>
                        </div>
                    </div>
                    @if($complaintSuggestion->email)
                        <div class="tk-info-row">
                            <div class="tk-info-item">
                                <div class="tk-info-label">البريد الإلكتروني</div>
                                <div class="tk-info-val">{{ $complaintSuggestion->email }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="tk-section-title"><i class="bi bi-file-text"></i> تفاصيل الطلب</div>
                    <div class="tk-info-row">
                        <div class="tk-info-item">
                            <div class="tk-info-label">النوع</div>
                            <div class="tk-info-val">{{ $complaintSuggestion->type_label }}</div>
                        </div>
                        <div class="tk-info-item">
                            <div class="tk-info-label">المحافظة</div>
                            <div class="tk-info-val">{{ $complaintSuggestion->getGovernorateLabel() ?? 'غير محدد' }}</div>
                        </div>
                    </div>
                    @if($complaintSuggestion->generator)
                        <div class="tk-info-row">
                            <div class="tk-info-item">
                                <div class="tk-info-label">المولد</div>
                                <div class="tk-info-val">{{ $complaintSuggestion->generator->name }}</div>
                            </div>
                        </div>
                    @endif
                    <div class="tk-info-row">
                        <div class="tk-info-item">
                            <div class="tk-info-label">تاريخ الإرسال</div>
                            <div class="tk-info-val">{{ $complaintSuggestion->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>

                    <div class="tk-msg">
                        <div class="tk-msg-label">الرسالة</div>
                        <div class="tk-msg-text">{{ $complaintSuggestion->message }}</div>
                    </div>

                    @if($complaintSuggestion->image)
                        <div class="tk-msg tk-image">
                            <div class="tk-msg-label">الصورة المرفقة</div>
                            <img src="{{ asset('storage/' . $complaintSuggestion->image) }}" alt="صورة مرفقة">
                        </div>
                    @endif

                    @if($complaintSuggestion->response)
                        <div class="tk-response">
                            <div class="tk-response-label"><i class="bi bi-reply-fill"></i> رد الإدارة</div>
                            <div class="tk-response-text">{{ $complaintSuggestion->response }}</div>
                            @if($complaintSuggestion->responded_at)
                                <div class="tk-response-date">بتاريخ: {{ $complaintSuggestion->responded_at->format('Y-m-d H:i') }}</div>
                            @endif
                        </div>
                    @else
                        <div class="tk-pending">
                            <i class="bi bi-hourglass-split"></i> الطلب قيد المراجعة — سيتم الرد عليك قريباً
                        </div>
                    @endif
                </div>
            </div>
        @elseif(isset($code) && $code)
            <div class="tk-empty">
                <i class="bi bi-search"></i>
                <p>لم يتم العثور على طلب بهذا الرمز. يرجى التحقق والمحاولة مرة أخرى.</p>
            </div>
        @endif
    </div>

@endsection

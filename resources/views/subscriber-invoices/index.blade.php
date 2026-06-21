@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'فواتيري - ' . $siteName)
@section('description', 'استعلم عن فواتيرك المستحقة برقم الاشتراك ورقم الهوية ورقم الجوال')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/libs/flatpickr/flatpickr.min.css') }}">
<style>
/* ===== Invoice Inquiry — Nour Design System ===== */

.inv-hero {
    background: linear-gradient(155deg, #1a2478 0%, #11186B 40%, #0c1050 100%);
    padding: 2.5rem 0 3.25rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.inv-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(circle at 25% 35%, rgba(255,255,255,0.06) 0%, transparent 50%),
        radial-gradient(circle at 75% 65%, rgba(255,255,255,0.04) 0%, transparent 50%);
    pointer-events: none;
}
.inv-hero::after {
    content: '';
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
    background-size: 60px 60px;
    pointer-events: none;
}
.inv-hero-inner { position: relative; z-index: 1; max-width: 640px; margin: 0 auto; padding: 0 2rem; }
.inv-hero h1 { font-size: 2rem; font-weight: 900; color: #fff; margin-bottom: 0.4rem; }
.inv-hero h1 i { color: #FBBF24; margin-left: 0.4rem; }
.inv-hero p { font-size: 1rem; color: rgba(255,255,255,0.72); font-weight: 500; margin: 0; }

/* ===== Layout wrap ===== */
.inv-wrap { max-width: 920px; margin: -2rem auto 3rem; padding: 0 1.25rem; position: relative; z-index: 2; }

/* ===== Search card ===== */
.inv-search {
    background: #fff;
    border-radius: 0.85rem;
    border: 1px solid #E5E7EB;
    border-top: 2.5px solid #24308F;
    padding: 1.5rem;
    box-shadow: 0 8px 30px rgba(17,24,107,0.08);
}
.inv-search-head { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; }
.inv-search-head i { color: #24308F; font-size: 1.15rem; }
.inv-search-head h2 { font-size: 1.05rem; font-weight: 800; color: #1F2937; margin: 0; }

.inv-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.inv-field { display: flex; flex-direction: column; gap: 0.35rem; }
.inv-field.inv-col-span { grid-column: 1 / -1; }
.inv-label { font-size: 0.82rem; font-weight: 700; color: #3B4863; display: flex; align-items: center; gap: 0.35rem; }
.inv-label i { color: #24308F; font-size: 0.85rem; }
.inv-label .inv-req { color: #EF4444; font-weight: 700; }

.inv-input {
    height: 44px;
    padding: 0 0.85rem;
    border: 1.5px solid #D9E0EA;
    border-radius: 0.55rem;
    font-family: 'Tajawal', sans-serif;
    font-size: 0.95rem;
    font-weight: 500;
    color: #1F2937;
    background: #fff;
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s;
    width: 100%;
}
.inv-input::placeholder { color: #98A2B3; font-weight: 500; }
.inv-input:focus { border-color: #24308F; box-shadow: 0 0 0 3px rgba(36,48,143,0.08); }
.inv-input.is-invalid { border-color: #EF4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.08); }
.inv-error { font-size: 0.74rem; color: #EF4444; font-weight: 600; min-height: 0; }

/* Period row */
.inv-period { margin-top: 1.1rem; padding-top: 1.1rem; border-top: 1px dashed #E5E7EB; }
.inv-period-head { font-size: 0.82rem; font-weight: 700; color: #3B4863; margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.35rem; }
.inv-period-head i { color: #24308F; }
.inv-chips { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.85rem; }
.inv-chip {
    border: 1.5px solid #D9E0EA;
    background: #fff;
    color: #3B4863;
    font-family: 'Tajawal', sans-serif;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.4rem 0.9rem;
    border-radius: 2rem;
    cursor: pointer;
    transition: all 0.15s;
}
.inv-chip:hover { border-color: #24308F; color: #24308F; }
.inv-chip.active { background: #EEF2FF; border-color: #24308F; color: #24308F; }
.inv-dates { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

/* Date inputs: calendar icon + clickable cursor (flatpickr altInput keeps .inv-input) */
.inv-dates .inv-input {
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2324308F' stroke-width='1.7' stroke-linecap='round' viewBox='0 0 24 24'%3E%3Crect x='3' y='4.5' width='18' height='17' rx='2.5'/%3E%3Cpath d='M16 2.5v4M8 2.5v4M3 9.5h18'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: left 0.8rem center;
    padding-left: 2.3rem;
}
.inv-dates .inv-input::placeholder { color: #98A2B3; }

/* ===== flatpickr theming → Nour navy ===== */
.flatpickr-calendar {
    border-radius: 0.7rem;
    box-shadow: 0 10px 34px rgba(17,24,107,0.16);
    border: 1px solid #E5E7EB;
    font-family: 'Tajawal', sans-serif;
}
.flatpickr-months .flatpickr-month { color: #24308F; }
.flatpickr-current-month .flatpickr-monthDropdown-months, .flatpickr-current-month input.cur-year { font-weight: 800; color: #1F2937; }
.flatpickr-weekday { color: #5B6780; font-weight: 700; }
.flatpickr-day { color: #1F2937; font-weight: 600; border-radius: 0.45rem; }
.flatpickr-day:hover { background: #EEF2FF; border-color: #EEF2FF; }
.flatpickr-day.today { border-color: #24308F; }
.flatpickr-day.selected, .flatpickr-day.selected:hover,
.flatpickr-day.startRange, .flatpickr-day.endRange {
    background: #24308F; border-color: #24308F; color: #fff;
}
.flatpickr-day.inRange { background: #EEF2FF; border-color: #EEF2FF; box-shadow: -5px 0 0 #EEF2FF, 5px 0 0 #EEF2FF; }
.flatpickr-months .flatpickr-prev-month:hover svg, .flatpickr-months .flatpickr-next-month:hover svg { fill: #24308F; }
.flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover { color: #D9E0EA; }

/* Submit */
.inv-actions { margin-top: 1.5rem; display: flex; gap: 0.6rem; align-items: stretch; }
.inv-btn {
    flex: 0 0 auto;
    min-width: 220px;
    height: 52px;
    padding: 0 2.25rem;
    background: linear-gradient(135deg, #2b38ab 0%, #24308F 55%, #1b237a 100%);
    color: #fff;
    border: none;
    border-radius: 0.7rem;
    font-family: 'Tajawal', sans-serif;
    font-size: 1.02rem;
    font-weight: 800;
    letter-spacing: 0.2px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    box-shadow: 0 6px 18px rgba(36,48,143,0.28);
    transition: transform 0.12s ease, box-shadow 0.2s ease, filter 0.2s ease;
}
.inv-btn i { font-size: 1.12rem; }
.inv-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 11px 26px rgba(36,48,143,0.34); filter: brightness(1.07); }
.inv-btn:active:not(:disabled) { transform: translateY(0); box-shadow: 0 4px 12px rgba(36,48,143,0.25); }
.inv-btn:disabled { opacity: 0.78; cursor: not-allowed; box-shadow: none; }

/* Reset → secondary button with label */
.inv-btn--ghost {
    flex: 0 0 auto;
    min-width: auto;
    height: 52px;
    padding: 0 1.5rem;
    background: #fff;
    color: #5B6780;
    border: 1.5px solid #E5E7EB;
    border-radius: 0.7rem;
    box-shadow: none;
    font-size: 0.97rem;
}
.inv-btn--ghost i { font-size: 1.05rem; }
.inv-btn--ghost:hover:not(:disabled) { transform: none; filter: none; background: #F8FAFC; border-color: #24308F; color: #24308F; box-shadow: none; }

.inv-spinner {
    width: 17px; height: 17px;
    border: 2.5px solid rgba(255,255,255,0.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: inv-spin 0.7s linear infinite;
    display: none;
}
@keyframes inv-spin { to { transform: rotate(360deg); } }

.inv-hint {
    margin-top: 0.85rem;
    font-size: 0.78rem;
    color: #98A2B3;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.inv-hint i { color: #0EA5E9; }

/* ===== Results ===== */
.inv-results { margin-top: 1.5rem; }
.inv-results[hidden] { display: none; }

.inv-anim { animation: inv-rise 0.35s ease both; }
@keyframes inv-rise { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

/* Subscriber banner */
.inv-sub {
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 2rem;
    align-items: center;
    margin-bottom: 1rem;
}
.inv-sub-name { font-size: 1.05rem; font-weight: 900; color: #1F2937; display: flex; align-items: center; gap: 0.5rem; }
.inv-sub-name i { color: #24308F; }
.inv-sub-meta { display: flex; flex-wrap: wrap; gap: 0.35rem 1.5rem; margin-right: auto; }
.inv-sub-meta span { font-size: 0.83rem; color: #5B6780; font-weight: 600; }
.inv-sub-meta b { color: #1F2937; font-weight: 800; }

/* Balance card */
.inv-balance {
    border-radius: 0.85rem;
    padding: 1.5rem;
    text-align: center;
    margin-bottom: 1rem;
    border: 1px solid;
}
.inv-balance--due { background: linear-gradient(160deg,#FFF7F7,#FEF2F2); border-color: #FCA5A5; }
.inv-balance--clear { background: linear-gradient(160deg,#F0FDF9,#ECFDF5); border-color: #6EE7B7; }
.inv-balance-label { font-size: 0.88rem; font-weight: 700; margin-bottom: 0.3rem; }
.inv-balance--due .inv-balance-label { color: #B91C1C; }
.inv-balance--clear .inv-balance-label { color: #047857; }
.inv-balance-amount { font-size: 2.6rem; font-weight: 900; line-height: 1.1; letter-spacing: -1px; }
.inv-balance--due .inv-balance-amount { color: #DC2626; }
.inv-balance--clear .inv-balance-amount { color: #059669; }
.inv-balance-cur { font-size: 1.1rem; font-weight: 800; margin-right: 0.25rem; }
.inv-balance-note { font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem; }
.inv-balance--due .inv-balance-note { color: #92400E; }
.inv-balance--clear .inv-balance-note { color: #047857; }

/* Stat chips */
.inv-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.85rem; margin-bottom: 1.1rem; }
.inv-stat {
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 0.7rem;
    padding: 0.9rem 1rem;
    text-align: center;
}
.inv-stat-val { font-size: 1.4rem; font-weight: 900; color: #24308F; }
.inv-stat-label { font-size: 0.76rem; color: #5B6780; font-weight: 700; margin-top: 0.15rem; }

/* Invoices table */
.inv-table-card {
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 0.75rem;
    overflow: hidden;
}
.inv-table-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.95rem 1.25rem;
    background: #F8FBFD;
    border-bottom: 1px solid #E5E7EB;
}
.inv-table-head h3 { font-size: 0.97rem; font-weight: 800; color: #1F2937; margin: 0; display: flex; align-items: center; gap: 0.45rem; }
.inv-table-head h3 i { color: #24308F; }
.inv-head-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.inv-print-btn, .inv-export-btn {
    background: #fff; border: 1.5px solid #D1D5DB; color: #3B4863;
    font-family: 'Tajawal', sans-serif; font-size: 0.8rem; font-weight: 700;
    padding: 0.4rem 0.85rem; border-radius: 0.5rem; cursor: pointer;
    display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s;
}
.inv-print-btn:hover { border-color: #24308F; color: #24308F; }
.inv-export-btn { border-color: #10B981; color: #047857; }
.inv-export-btn:hover { background: #ECFDF5; border-color: #059669; }
.inv-export-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.inv-export-btn .inv-mini-spin {
    width: 13px; height: 13px; border: 2px solid rgba(4,120,87,0.3);
    border-top-color: #047857; border-radius: 50%; animation: inv-spin 0.7s linear infinite;
}

/* Print-only statement header (hidden on screen) */
.inv-print-header { display: none; }

.inv-table-scroll { overflow-x: auto; }
.inv-table { width: 100%; border-collapse: collapse; font-size: 0.86rem; }
.inv-table thead th {
    text-align: right;
    padding: 0.7rem 1rem;
    font-size: 0.76rem;
    font-weight: 800;
    color: #5B6780;
    background: #FCFCFD;
    border-bottom: 1px solid #E5E7EB;
    white-space: nowrap;
}
.inv-table tbody td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #F1F4F8;
    color: #1F2937;
    font-weight: 600;
    white-space: nowrap;
}
.inv-table tbody tr:last-child td { border-bottom: none; }
.inv-table tbody tr.is-overdue { background: #FFFBFB; }
.inv-num { font-family: 'Tajawal', monospace; font-weight: 700; color: #3B4863; font-size: 0.82rem; }
.inv-amount { font-weight: 800; }
.inv-remaining-due { color: #DC2626; font-weight: 800; }
.inv-remaining-paid { color: #98A2B3; }

.inv-badge {
    display: inline-block;
    padding: 0.22rem 0.6rem;
    border-radius: 2rem;
    font-size: 0.72rem;
    font-weight: 800;
    white-space: nowrap;
}
/* Per-invoice print button */
.inv-row-print {
    background: #fff;
    border: 1.5px solid #C7CDDB;
    color: #24308F;
    font-family: 'Tajawal', sans-serif;
    font-size: 0.76rem;
    font-weight: 700;
    padding: 0.3rem 0.8rem;
    border-radius: 0.45rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
    transition: all 0.15s;
}
.inv-row-print:hover:not(:disabled) { background: #EEF2FF; border-color: #24308F; }
.inv-row-print:disabled { opacity: 0.6; cursor: not-allowed; }
.inv-row-print .inv-mini-spin { border-color: rgba(36,48,143,0.3); border-top-color: #24308F; }

.inv-badge--paid { background: #D1FAE5; color: #047857; }
.inv-badge--partial { background: #FEF3C7; color: #92400E; }
.inv-badge--due { background: #DBEAFE; color: #1D4ED8; }
.inv-badge--overdue { background: #FEE2E2; color: #B91C1C; }

/* Empty / message states */
.inv-empty {
    text-align: center;
    padding: 2.5rem 1.5rem;
    background: #F8FAFC;
    border: 1px solid #E5E7EB;
    border-radius: 0.75rem;
}
.inv-empty i { font-size: 2.4rem; display: block; margin-bottom: 0.5rem; }
.inv-empty.is-info i { color: #98A2B3; }
.inv-empty.is-good i { color: #10B981; }
.inv-empty p { font-size: 0.93rem; color: #5B6780; font-weight: 700; margin: 0; }

.inv-alert {
    padding: 0.85rem 1.1rem;
    border-radius: 0.6rem;
    font-size: 0.88rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.55rem;
}
.inv-alert--error { background: #FEF2F2; color: #B91C1C; border-right: 3px solid #EF4444; }
.inv-alert[hidden] { display: none; }

/* Period summary line under table */
.inv-period-summary {
    padding: 0.85rem 1.25rem;
    background: #F8FBFD;
    border-top: 1px solid #E5E7EB;
    font-size: 0.85rem;
    font-weight: 700;
    color: #3B4863;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.inv-period-summary b { color: #DC2626; }

/* Responsive */
@media (max-width: 720px) {
    .inv-hero h1 { font-size: 1.55rem; }
    .inv-grid { grid-template-columns: 1fr; }
    .inv-dates { grid-template-columns: 1fr; }
    .inv-stats { grid-template-columns: 1fr; }
    .inv-balance-amount { font-size: 2.1rem; }
    .inv-btn { flex: 1 1 auto; min-width: 0; } /* عرض كامل على الموبايل للمس المريح */

    /* Stacked table → cards */
    .inv-table thead { display: none; }
    .inv-table, .inv-table tbody, .inv-table tr, .inv-table td { display: block; width: 100%; }
    .inv-table tbody tr {
        border: 1px solid #E5E7EB;
        border-radius: 0.6rem;
        margin: 0.75rem;
        padding: 0.35rem 0;
        overflow: hidden;
    }
    .inv-table tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        white-space: normal;
        border-bottom: 1px solid #F1F4F8;
        padding: 0.55rem 0.95rem;
    }
    .inv-table tbody td::before {
        content: attr(data-label);
        font-size: 0.74rem;
        font-weight: 800;
        color: #98A2B3;
    }
}

/* Print */
@media print {
    /* اطبع الألوان والخلفيات كما هي */
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

    .public-nav, .public-footer, .inv-hero, .inv-search, .nav-toggle,
    .inv-head-actions, .inv-hint, .inv-alert, .inv-stats { display: none !important; }

    .inv-wrap { margin: 0 !important; max-width: 100% !important; padding: 0 !important; }
    .inv-results { margin: 0 !important; }
    .inv-table-card, .inv-sub, .inv-balance { box-shadow: none !important; border-radius: 0 !important; }
    body { background: #fff !important; }

    /* ترويسة الكشف للطباعة */
    .inv-print-header {
        display: block !important;
        text-align: center;
        padding-bottom: 0.75rem;
        margin-bottom: 1rem;
        border-bottom: 2px solid #24308F;
    }
    .inv-print-header .iph-title { font-size: 1.3rem; font-weight: 900; color: #24308F; }
    .inv-print-header .iph-sub { font-size: 0.85rem; color: #5B6780; font-weight: 600; margin-top: 0.2rem; }

    /* تجنّب قطع صفوف الجدول بين الصفحات */
    .inv-table { font-size: 0.8rem; }
    .inv-table thead { display: table-header-group; }
    .inv-table tr { page-break-inside: avoid; }
    .inv-table-scroll { overflow: visible !important; }

    @page { margin: 1.2cm; }
}
</style>
@endpush

@section('content')

<section class="inv-hero">
    <div class="inv-hero-inner">
        <h1><i class="bi bi-receipt-cutoff"></i> فواتيري</h1>
        <p>استعلم عن فواتيرك والمبالغ المستحقة عليك بسهولة</p>
    </div>
</section>

<div class="inv-wrap">

    {{-- نموذج الاستعلام --}}
    <div class="inv-search">
        <div class="inv-search-head">
            <i class="bi bi-search"></i>
            <h2>أدخل بيانات اشتراكك</h2>
        </div>

        <form id="invForm" novalidate>
            <div class="inv-grid">
                <div class="inv-field">
                    <label class="inv-label" for="subscription_number">
                        <i class="bi bi-hash"></i> رقم الاشتراك <span class="inv-req">*</span>
                    </label>
                    <input type="text" id="subscription_number" name="subscription_number" class="inv-input"
                           inputmode="numeric" autocomplete="off" placeholder="مثال: 013010137">
                    <div class="inv-error" data-error-for="subscription_number"></div>
                </div>

                <div class="inv-field">
                    <label class="inv-label" for="id_number">
                        <i class="bi bi-person-vcard"></i> رقم الهوية <span class="inv-req">*</span>
                    </label>
                    <input type="text" id="id_number" name="id_number" class="inv-input"
                           inputmode="numeric" autocomplete="off" placeholder="رقم الهوية المسجّل">
                    <div class="inv-error" data-error-for="id_number"></div>
                </div>

                <div class="inv-field">
                    <label class="inv-label" for="phone">
                        <i class="bi bi-telephone"></i> رقم الجوال <span class="inv-req">*</span>
                    </label>
                    <input type="text" id="phone" name="phone" class="inv-input"
                           inputmode="numeric" autocomplete="off" placeholder="مثال: 0599xxxxxx">
                    <div class="inv-error" data-error-for="phone"></div>
                </div>
            </div>

            <div class="inv-period">
                <div class="inv-period-head"><i class="bi bi-calendar-range"></i> الفترة</div>
                <div class="inv-chips" id="invChips">
                    <button type="button" class="inv-chip" data-range="3m">آخر 3 أشهر</button>
                    <button type="button" class="inv-chip" data-range="6m">آخر 6 أشهر</button>
                    <button type="button" class="inv-chip" data-range="year">هذه السنة</button>
                    <button type="button" class="inv-chip active" data-range="all">كل الفواتير</button>
                </div>
                <div class="inv-dates">
                    <div class="inv-field">
                        <label class="inv-label" for="from_date"><i class="bi bi-calendar-event"></i> من تاريخ</label>
                        <input type="text" id="from_date" name="from_date" class="inv-input" placeholder="اختر التاريخ" readonly>
                    </div>
                    <div class="inv-field">
                        <label class="inv-label" for="to_date"><i class="bi bi-calendar-event"></i> إلى تاريخ</label>
                        <input type="text" id="to_date" name="to_date" class="inv-input" placeholder="اختر التاريخ" readonly>
                    </div>
                </div>
                <div class="inv-error" data-error-for="to_date"></div>
            </div>

            <div class="inv-actions">
                <button type="submit" class="inv-btn" id="invSubmit">
                    <span class="inv-spinner" id="invSpinner"></span>
                    <span id="invSubmitText"><i class="bi bi-search"></i> عرض فواتيري</span>
                </button>
                <button type="reset" class="inv-btn inv-btn--ghost" id="invReset" title="تفريغ البيانات">
                    <i class="bi bi-arrow-counterclockwise"></i> تفريغ البيانات
                </button>
            </div>

            <div class="inv-hint">
                <i class="bi bi-shield-lock"></i>
                تُستخدم بياناتك للتحقق من هويتك فقط، ولا يتم عرض الفواتير إلا بتطابق الحقول الثلاثة.
            </div>
        </form>
    </div>

    {{-- رسالة خطأ عامة --}}
    <div class="inv-alert inv-alert--error" id="invAlert" hidden role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span id="invAlertText"></span>
    </div>

    {{-- النتائج (تُبنى بـ JavaScript) --}}
    <div class="inv-results" id="invResults" hidden aria-live="polite"></div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/admin/libs/flatpickr/l10n/ar.js') }}"></script>
<script>
(function () {
    'use strict';

    const SEARCH_URL = "{{ route('subscriber-invoices.search') }}";
    const EXPORT_URL = "{{ route('subscriber-invoices.export') }}";
    const PRINT_LINK_URL = "{{ route('subscriber-invoices.print-link') }}";
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const SITE_NAME = @json($siteName ?? 'نور');
    let lastQuery = null;

    const form        = document.getElementById('invForm');
    const submitBtn   = document.getElementById('invSubmit');
    const submitText  = document.getElementById('invSubmitText');
    const spinner     = document.getElementById('invSpinner');
    const resultsBox  = document.getElementById('invResults');
    const alertBox    = document.getElementById('invAlert');
    const alertText   = document.getElementById('invAlertText');
    const chips       = document.getElementById('invChips');
    const fromInput   = document.getElementById('from_date');
    const toInput     = document.getElementById('to_date');

    // ---------- Helpers ----------
    function fmt(n) {
        return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }
    function pad(n) { return String(n).padStart(2, '0'); }
    function ymd(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }

    function clearErrors() {
        document.querySelectorAll('.inv-error').forEach(e => e.textContent = '');
        document.querySelectorAll('.inv-input.is-invalid').forEach(e => e.classList.remove('is-invalid'));
        alertBox.hidden = true;
    }
    function fieldError(name, msg) {
        const box = document.querySelector('[data-error-for="' + name + '"]');
        if (box) box.textContent = msg;
        const input = document.getElementById(name);
        if (input) input.classList.add('is-invalid');
    }
    function showAlert(msg) {
        alertText.textContent = msg;
        alertBox.hidden = false;
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // ---------- Date pickers (flatpickr) ----------
    function deactivateChips() {
        chips.querySelectorAll('.inv-chip').forEach(c => c.classList.remove('active'));
    }

    let fpFrom = null, fpTo = null;
    if (typeof flatpickr !== 'undefined') {
        const fpOpts = {
            locale: 'ar',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'j F Y',
            altInputClass: 'inv-input',
            disableMobile: true,
            maxDate: 'today',
        };
        fpFrom = flatpickr(fromInput, Object.assign({}, fpOpts, {
            onChange: function (sel) {
                if (fpTo) fpTo.set('minDate', sel[0] || null);
                deactivateChips();
            }
        }));
        fpTo = flatpickr(toInput, Object.assign({}, fpOpts, {
            onChange: function (sel) {
                if (fpFrom) fpFrom.set('maxDate', sel[0] || 'today');
                deactivateChips();
            }
        }));
    }

    // ---------- Period presets ----------
    chips.addEventListener('click', function (e) {
        const btn = e.target.closest('.inv-chip');
        if (!btn) return;
        deactivateChips();
        btn.classList.add('active');

        const today = new Date();
        today.setHours(0, 0, 0, 0); // تطبيع ليتوافق مع maxDate:'today' في flatpickr
        const range = btn.dataset.range;
        if (range === 'all') {
            if (fpFrom) { fpFrom.clear(); fpFrom.set('maxDate', 'today'); } else fromInput.value = '';
            if (fpTo) { fpTo.clear(); fpTo.set('minDate', null); } else toInput.value = '';
            return;
        }
        let from = new Date(today);
        if (range === '3m') from.setMonth(from.getMonth() - 3);
        else if (range === '6m') from.setMonth(from.getMonth() - 6);
        else if (range === 'year') from = new Date(today.getFullYear(), 0, 1);
        // set without triggering onChange so the active chip stays highlighted
        if (fpFrom) { fpFrom.setDate(from, false); } else fromInput.value = ymd(from);
        if (fpTo) { fpTo.setDate(today, false); fpTo.set('minDate', from); } else toInput.value = ymd(today);
    });

    // ---------- Client validation ----------
    function validate(data) {
        const invalid = [];
        if (!data.subscription_number) { fieldError('subscription_number', 'يرجى إدخال رقم الاشتراك'); invalid.push('subscription_number'); }
        if (!data.id_number) { fieldError('id_number', 'يرجى إدخال رقم الهوية'); invalid.push('id_number'); }
        if (!data.phone) { fieldError('phone', 'يرجى إدخال رقم الجوال'); invalid.push('phone'); }
        if (data.from_date && data.to_date && data.to_date < data.from_date) {
            fieldError('to_date', 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية'); invalid.push('to_date');
        }
        // مرّر المستخدم إلى أول حقل خاطئ حتى يرى سبب عدم ظهور النتائج
        if (invalid.length) {
            const first = document.getElementById(invalid[0]);
            if (first) {
                first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                first.focus({ preventScroll: true });
            }
            return false;
        }
        return true;
    }

    function setLoading(on) {
        submitBtn.disabled = on;
        spinner.style.display = on ? 'inline-block' : 'none';
        submitText.innerHTML = on
            ? 'جاري البحث...'
            : '<i class="bi bi-search"></i> عرض فواتيري';
    }

    // ---------- Submit ----------
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors();
        resultsBox.hidden = true;

        const data = {
            subscription_number: document.getElementById('subscription_number').value.trim(),
            id_number:           document.getElementById('id_number').value.trim(),
            phone:               document.getElementById('phone').value.trim(),
            from_date:           fromInput.value || null,
            to_date:             toInput.value || null,
        };

        if (!validate(data)) return;

        setLoading(true);
        try {
            const res = await fetch(SEARCH_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(data),
            });

            if (res.status === 422) {
                const body = await res.json();
                if (body.errors) {
                    Object.keys(body.errors).forEach(k => fieldError(k, body.errors[k][0]));
                }
                return;
            }
            if (res.status === 429) {
                showAlert('لقد قمت بعدد كبير من المحاولات. يرجى الانتظار دقيقة ثم المحاولة مجدداً.');
                return;
            }
            if (!res.ok) {
                showAlert('حدث خطأ غير متوقع. يرجى المحاولة لاحقاً.');
                return;
            }

            const body = await res.json();
            if (!body.found) {
                showAlert(body.message || 'لم يتم العثور على بيانات مطابقة.');
                return;
            }
            lastQuery = data;
            renderResults(body);
        } catch (err) {
            showAlert('تعذّر الاتصال بالخادم. تحقّق من اتصالك بالإنترنت وحاول مرة أخرى.');
        } finally {
            setLoading(false);
        }
    });

    form.addEventListener('reset', function () {
        clearErrors();
        resultsBox.hidden = true;
        deactivateChips();
        chips.querySelector('[data-range="all"]').classList.add('active');
        // يُنفّذ بعد إعادة التعيين الأصلية للنموذج
        setTimeout(function () {
            if (fpFrom) { fpFrom.clear(); fpFrom.set('maxDate', 'today'); } else fromInput.value = '';
            if (fpTo) { fpTo.clear(); fpTo.set('minDate', null); } else toInput.value = '';
        }, 0);
    });

    // ---------- Render ----------
    function renderResults(d) {
        const s = d.summary;
        const sub = d.subscriber;
        const hasDue = s.net_balance > 0;

        let html = '';

        // ترويسة الكشف (تظهر عند الطباعة فقط)
        html += '<div class="inv-print-header">'
            + '<div class="iph-title">' + esc(SITE_NAME) + ' — كشف حساب المشترك</div>'
            + '<div class="iph-sub">' + esc(sub.name) + ' · رقم الاشتراك: ' + esc(sub.subscription_number)
            + ' · تاريخ الطباعة: ' + new Date().toLocaleDateString('en-CA') + '</div>'
            + '</div>';

        // Subscriber banner
        html += '<div class="inv-sub">'
            + '<div class="inv-sub-name"><i class="bi bi-person-circle"></i>' + esc(sub.name) + '</div>'
            + '<div class="inv-sub-meta">'
            + '<span>رقم الاشتراك: <b>' + esc(sub.subscription_number) + '</b></span>'
            + (sub.meter_number ? '<span>رقم العداد: <b>' + esc(sub.meter_number) + '</b></span>' : '')
            + '</div></div>';

        // Balance card
        if (hasDue) {
            html += '<div class="inv-balance inv-balance--due">'
                + '<div class="inv-balance-label"><i class="bi bi-exclamation-circle-fill"></i> إجمالي المبلغ المستحق عليك</div>'
                + '<div class="inv-balance-amount">' + fmt(s.net_balance) + '<span class="inv-balance-cur">₪</span></div>'
                + '<div class="inv-balance-note">يشمل جميع فواتيرك غير المسددة حتى تاريخه</div>'
                + '</div>';
        } else {
            html += '<div class="inv-balance inv-balance--clear">'
                + '<div class="inv-balance-label"><i class="bi bi-check-circle-fill"></i> لا توجد مبالغ مستحقة عليك</div>'
                + '<div class="inv-balance-amount">0.00<span class="inv-balance-cur">₪</span></div>'
                + '<div class="inv-balance-note">'
                + (s.credit_balance > 0
                    ? 'جميع فواتيرك مسددة، ولديك رصيد دائن لصالحك: ' + fmt(s.credit_balance) + ' ₪'
                    : 'جميع فواتيرك مسددة. شكراً لالتزامك 🎉')
                + '</div></div>';
        }

        // Stats
        html += '<div class="inv-stats">'
            + '<div class="inv-stat"><div class="inv-stat-val">' + s.unpaid_count + '</div><div class="inv-stat-label">فاتورة غير مسددة</div></div>'
            + '<div class="inv-stat"><div class="inv-stat-val">' + s.total_invoices + '</div><div class="inv-stat-label">إجمالي الفواتير</div></div>'
            + '<div class="inv-stat"><div class="inv-stat-val">' + s.period_count + '</div><div class="inv-stat-label">الفواتير المعروضة</div></div>'
            + '</div>';

        // Invoices table
        if (d.invoices.length === 0) {
            html += '<div class="inv-empty is-info">'
                + '<i class="bi bi-calendar-x"></i>'
                + '<p>لا توجد فواتير ضمن الفترة المحددة. جرّب توسيع الفترة أو اختر «كل الفواتير».</p>'
                + '</div>';
        } else {
            html += '<div class="inv-table-card">'
                + '<div class="inv-table-head">'
                + '<h3><i class="bi bi-list-ul"></i> تفاصيل الفواتير</h3>'
                + '<div class="inv-head-actions">'
                + '<button type="button" class="inv-export-btn" id="invExportBtn"><i class="bi bi-file-earmark-excel"></i> تصدير Excel</button>'
                + '<button type="button" class="inv-print-btn" id="invPrintBtn"><i class="bi bi-printer"></i> طباعة</button>'
                + '</div>'
                + '</div>'
                + '<div class="inv-table-scroll"><table class="inv-table"><thead><tr>'
                + '<th>رقم الفاتورة</th><th>تاريخ الفاتورة</th><th>الاستهلاك (ك.و.س)</th>'
                + '<th>قيمة الفاتورة</th><th>المتبقي</th><th>تاريخ الاستحقاق</th><th>الحالة</th><th>طباعة</th>'
                + '</tr></thead><tbody>';

            d.invoices.forEach(function (inv) {
                const remCell = inv.remaining > 0
                    ? '<span class="inv-remaining-due">' + fmt(inv.remaining) + ' ₪</span>'
                    : '<span class="inv-remaining-paid">—</span>';
                html += '<tr class="' + (inv.is_overdue ? 'is-overdue' : '') + '">'
                    + '<td data-label="رقم الفاتورة"><span class="inv-num">' + esc(inv.invoice_number) + '</span></td>'
                    + '<td data-label="تاريخ الفاتورة">' + esc(inv.invoice_date || '—') + '</td>'
                    + '<td data-label="الاستهلاك (ك.و.س)">' + fmt(inv.consumption_kwh) + '</td>'
                    + '<td data-label="قيمة الفاتورة"><span class="inv-amount">' + fmt(inv.amount) + ' ₪</span></td>'
                    + '<td data-label="المتبقي">' + remCell + '</td>'
                    + '<td data-label="تاريخ الاستحقاق">' + esc(inv.due_date || '—') + '</td>'
                    + '<td data-label="الحالة"><span class="inv-badge inv-badge--' + inv.status_key + '">' + esc(inv.status_label) + '</span></td>'
                    + '<td data-label="طباعة"><button type="button" class="inv-row-print" data-id="' + inv.id + '"><i class="bi bi-printer"></i> طباعة</button></td>'
                    + '</tr>';
            });

            html += '</tbody></table></div>';

            if (s.period_due > 0) {
                html += '<div class="inv-period-summary">'
                    + '<span>المبلغ المستحق ضمن الفواتير المعروضة</span>'
                    + '<b>' + fmt(s.period_due) + ' ₪</b>'
                    + '</div>';
            }
            html += '</div>';
        }

        resultsBox.innerHTML = html;

        // ربط أزرار الإجراءات (قد لا توجد إن كانت الفترة بلا فواتير)
        const printBtn = document.getElementById('invPrintBtn');
        if (printBtn) printBtn.addEventListener('click', function () { window.print(); });
        const exportBtn = document.getElementById('invExportBtn');
        if (exportBtn) exportBtn.addEventListener('click', exportExcel);

        resultsBox.hidden = false;
        resultsBox.classList.remove('inv-anim');
        void resultsBox.offsetWidth; // reflow to restart animation
        resultsBox.classList.add('inv-anim');
        resultsBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // ---------- Excel export ----------
    async function exportExcel() {
        if (!lastQuery) return;
        const btn = document.getElementById('invExportBtn');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="inv-mini-spin"></span> جاري التصدير...';
        try {
            const res = await fetch(EXPORT_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(lastQuery),
            });
            if (!res.ok) {
                showAlert(res.status === 429
                    ? 'لقد قمت بعدد كبير من المحاولات. يرجى الانتظار دقيقة ثم المحاولة مجدداً.'
                    : 'تعذّر تصدير الملف. يرجى المحاولة مرة أخرى.');
                return;
            }
            const blob = await res.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'فواتير-' + (lastQuery.subscription_number || 'subscriber') + '.xlsx';
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(() => URL.revokeObjectURL(url), 1500);
        } catch (e) {
            showAlert('تعذّر الاتصال بالخادم أثناء التصدير. حاول مرة أخرى.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = original;
        }
    }

    // ---------- Per-invoice print ----------
    // تفويض الحدث: صندوق النتائج ثابت بينما الجدول يُعاد بناؤه
    resultsBox.addEventListener('click', function (e) {
        const btn = e.target.closest('.inv-row-print');
        if (btn) openInvoicePrint(btn);
    });

    async function openInvoicePrint(btn) {
        if (!lastQuery) return;
        const invoiceId = btn.dataset.id;
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="inv-mini-spin"></span> فتح...';

        // افتح التبويب فوراً ضمن إيماءة المستخدم (تفادياً لحظر النوافذ المنبثقة)
        const win = window.open('', '_blank');

        try {
            const res = await fetch(PRINT_LINK_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(Object.assign({}, lastQuery, { invoice_id: invoiceId })),
            });
            if (!res.ok) {
                if (win) win.close();
                showAlert(res.status === 429
                    ? 'لقد قمت بعدد كبير من المحاولات. يرجى الانتظار دقيقة ثم المحاولة مجدداً.'
                    : 'تعذّر فتح الفاتورة. يرجى المحاولة مرة أخرى.');
                return;
            }
            const body = await res.json();
            if (win) {
                win.location = body.url;
            } else {
                // في حال حُظرت النافذة المنبثقة
                window.open(body.url, '_blank');
            }
        } catch (e) {
            if (win) win.close();
            showAlert('تعذّر الاتصال بالخادم. يرجى المحاولة مرة أخرى.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = original;
        }
    }

    // Focus first field on load
    document.getElementById('subscription_number').focus();
})();
</script>
@endpush

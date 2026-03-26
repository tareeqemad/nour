@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'خريطة وحدات التوليد - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}" />
<style>
/* ===== Immersive Map Page ===== */
.map-page {
    position: relative;
    height: calc(100vh - 64px);
    overflow: hidden;
    background: #0c1050;
}

/* Full-screen map */
#map {
    position: absolute;
    inset: 0;
    z-index: 1;
}

/* ---- Floating Controls (Top Right) ---- */
.map-toolbar {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.map-filter-card {
    background: rgba(255,255,255,0.96);
    backdrop-filter: blur(12px);
    border-radius: 0.75rem;
    padding: 0.85rem 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    border: 1px solid rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.map-filter-card select {
    height: 38px;
    min-width: 180px;
    padding: 0 0.7rem;
    border: 1.5px solid #D9E0EA;
    border-radius: 0.5rem;
    font-family: 'Tajawal', sans-serif;
    font-size: 0.88rem;
    font-weight: 600;
    color: #1F2937;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.2s;
}

.map-filter-card select:focus {
    outline: none;
    border-color: #24308F;
    box-shadow: 0 0 0 2px rgba(36,48,143,0.1);
}

.map-filter-btn {
    height: 38px;
    padding: 0 1rem;
    background: #24308F;
    color: #fff;
    border: none;
    border-radius: 0.5rem;
    font-family: 'Tajawal', sans-serif;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    transition: background 0.2s;
    white-space: nowrap;
}

.map-filter-btn:hover { background: #2330B3; }

.map-toggle-label {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: #3B4863;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
}

.map-toggle-label input {
    width: 16px; height: 16px;
    accent-color: #24308F;
    cursor: pointer;
}

/* Map type switcher */
.map-type-card {
    background: rgba(255,255,255,0.96);
    backdrop-filter: blur(12px);
    border-radius: 0.5rem;
    padding: 0.3rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    display: flex;
    gap: 0.25rem;
    align-self: flex-end;
}

.map-type-btn {
    padding: 0.35rem 0.7rem;
    border: none;
    background: transparent;
    border-radius: 0.4rem;
    font-family: 'Tajawal', sans-serif;
    font-size: 0.78rem;
    font-weight: 600;
    color: #3B4863;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.map-type-btn:hover { background: #EEF2FF; color: #24308F; }
.map-type-btn.active { background: #24308F; color: #fff; }

/* ---- Sidebar (Left) ---- */
.map-sidebar {
    position: absolute;
    top: 1rem;
    left: 1rem;
    bottom: 1rem;
    width: 340px;
    z-index: 1000;
    background: rgba(255,255,255,0.97);
    backdrop-filter: blur(12px);
    border-radius: 0.75rem;
    box-shadow: 0 4px 24px rgba(0,0,0,0.12);
    border: 1px solid rgba(255,255,255,0.8);
    display: none;
    flex-direction: column;
    overflow: hidden;
    transition: transform 0.3s;
}

.map-sidebar.visible { display: flex; }
.map-sidebar { border-top: 2.5px solid #24308F; }

.sidebar-head {
    padding: 0.85rem 1rem;
    background: #24308F;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}

.sidebar-head h3 {
    font-size: 0.92rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.sidebar-badge {
    background: rgba(255,255,255,0.18);
    padding: 0.2rem 0.55rem;
    border-radius: 1rem;
    font-size: 0.72rem;
    font-weight: 600;
}

.sidebar-body {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem;
}

.sidebar-body::-webkit-scrollbar { width: 4px; }
.sidebar-body::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 3px; }

/* Sidebar items */
.units-list { list-style: none; padding: 0; margin: 0; }

.governorate-section { margin-bottom: 0.75rem; }

.governorate-header {
    padding: 0.45rem 0.65rem;
    background: #F8FAFC;
    border-radius: 0.4rem;
    border: 1px solid #E5E7EB;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.35rem;
}

.governorate-header span:first-child {
    font-weight: 700;
    color: #1F2937;
    font-size: 0.82rem;
}

.governorate-badge {
    background: #24308F;
    color: #fff;
    padding: 0.1rem 0.45rem;
    border-radius: 0.6rem;
    font-size: 0.68rem;
    font-weight: 700;
}

.unit-item {
    padding: 0.6rem 0.65rem;
    margin-bottom: 0.3rem;
    background: #fff;
    border-radius: 0.4rem;
    cursor: pointer;
    transition: all 0.15s;
    border: 1px solid transparent;
}

.unit-item:hover {
    background: #EEF2FF;
    border-color: #24308F;
}

.unit-item.active {
    background: #EEF2FF;
    border-color: #24308F;
    border-right: 2.5px solid #24308F;
}

.unit-name {
    font-weight: 700;
    color: #1F2937;
    font-size: 0.85rem;
    margin-bottom: 0.2rem;
}

.unit-details {
    font-size: 0.75rem;
    color: #5B6780;
    line-height: 1.5;
}

.unit-details i { color: #24308F; margin-left: 0.15rem; }

/* ---- Stats Bar (Bottom) ---- */
.map-stats-bar {
    position: absolute;
    bottom: 1rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    background: rgba(255,255,255,0.96);
    backdrop-filter: blur(12px);
    border-radius: 0.75rem;
    padding: 0.6rem 1.25rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    display: none;
    gap: 1.5rem;
}

.map-stats-bar.visible { display: flex; }

.map-stat {
    text-align: center;
}

.map-stat-val {
    font-size: 1.25rem;
    font-weight: 900;
    color: #24308F;
    line-height: 1.2;
}

.map-stat-label {
    font-size: 0.7rem;
    color: #5B6780;
    font-weight: 600;
}

/* ---- Loading / Empty ---- */
.map-loading {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.88);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    flex-direction: column;
}

.map-loading.active { display: flex; }

.map-spinner {
    width: 36px; height: 36px;
    border: 3px solid #E5E7EB;
    border-top-color: #24308F;
    border-radius: 50%;
    animation: mapSpin 0.7s linear infinite;
}

@keyframes mapSpin { to { transform: rotate(360deg); } }

.map-loading-text {
    margin-top: 0.6rem;
    color: #5B6780;
    font-weight: 600;
    font-size: 0.88rem;
}

.map-empty {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(8px);
    padding: 1.5rem 2rem;
    border-radius: 0.75rem;
    text-align: center;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    display: none;
}

.map-empty.active { display: block; }
.map-empty i { font-size: 2rem; color: #98A2B3; display: block; margin-bottom: 0.4rem; }
.map-empty h3 { font-size: 0.95rem; color: #5B6780; font-weight: 700; margin-bottom: 0.2rem; }
.map-empty p { font-size: 0.82rem; color: #98A2B3; margin: 0; }

/* ---- Nour Popup ---- */
.leaflet-popup-content-wrapper {
    border-radius: 0.75rem !important;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15) !important;
    padding: 0 !important;
    overflow: hidden;
    border: 1px solid #E5E7EB !important;
}

.leaflet-popup-content {
    margin: 0 !important;
    min-width: 280px;
}

.leaflet-popup-tip {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

.nour-popup {
    font-family: 'Tajawal', sans-serif;
    direction: rtl;
}

.nour-popup-head {
    background: linear-gradient(135deg, #24308F 0%, #11186B 100%);
    padding: 0.85rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.nour-popup-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.nour-popup-icon i {
    color: #FBBF24;
    font-size: 1.1rem;
}

.nour-popup-icon-territory { background: rgba(251,191,36,0.2); }
.nour-popup-icon-territory i { color: #FBBF24; }

.nour-popup-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.3;
}

.nour-popup-code {
    font-size: 0.72rem;
    color: rgba(255,255,255,0.6);
    font-weight: 500;
    margin-top: 0.1rem;
}

.nour-popup-body {
    padding: 0.5rem 0;
}

.nour-popup-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 1rem;
    border-bottom: 1px solid #F3F4F6;
    font-size: 0.82rem;
}

.nour-popup-row:last-child { border-bottom: none; }

.nour-popup-row > i {
    color: #98A2B3;
    font-size: 0.85rem;
    width: 16px;
    text-align: center;
    flex-shrink: 0;
}

.nour-popup-label {
    color: #5B6780;
    font-weight: 600;
    white-space: nowrap;
    min-width: 60px;
}

.nour-popup-val {
    color: #1F2937;
    font-weight: 700;
    margin-right: auto;
    text-align: left;
}

.nour-popup-phone .nour-popup-val,
.nour-popup-phone a {
    color: #24308F !important;
    text-decoration: none;
    font-weight: 700;
    direction: ltr;
}

.nour-popup-phone a:hover { text-decoration: underline; }

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .map-sidebar {
        width: calc(100% - 2rem);
        max-height: 45vh;
        bottom: auto;
        top: auto;
        bottom: 1rem;
    }

    .map-toolbar { right: 0.5rem; top: 0.5rem; }

    .map-filter-card {
        flex-direction: column;
        align-items: stretch;
    }

    .map-filter-card select { min-width: auto; width: 100%; }

    .map-stats-bar {
        width: calc(100% - 2rem);
        justify-content: space-around;
        bottom: 0.5rem;
    }
}
</style>
@endpush

@section('content')
<div class="map-page">
    {{-- Full-screen Map --}}
    <div id="map"></div>

    {{-- Loading --}}
    <div class="map-loading" id="loading">
        <div class="map-spinner"></div>
        <div class="map-loading-text">جاري تحميل البيانات...</div>
    </div>

    {{-- Empty State --}}
    <div class="map-empty" id="noOperators">
        <i class="bi bi-inbox"></i>
        <h3>لا توجد وحدات توليد</h3>
        <p>لا توجد وحدات في المحافظة المختارة</p>
    </div>

    {{-- Floating Toolbar (Top Right) --}}
    <div class="map-toolbar">
        <div class="map-filter-card">
            <select id="governorate">
                <option value="">-- اختر المحافظة --</option>
                @foreach($governorates as $governorate)
                    <option value="{{ $governorate->value }}">{{ $governorate->label }}</option>
                @endforeach
                <option value="all">جميع المحافظات</option>
            </select>
            <button class="map-filter-btn" id="searchBtn">
                <i class="bi bi-search"></i> بحث
            </button>
            <label class="map-toggle-label">
                <input type="checkbox" id="showTerritories" checked>
                المناطق الجغرافية
            </label>
        </div>

        <div class="map-type-card">
            <button class="map-type-btn active" id="mapTypeStreet">
                <i class="bi bi-map"></i> تفصيلية
            </button>
            <button class="map-type-btn" id="mapTypeSatellite">
                <i class="bi bi-globe"></i> قمر صناعي
            </button>
        </div>
    </div>

    {{-- Sidebar (Left) --}}
    <div class="map-sidebar" id="sidebar">
        <div class="sidebar-head">
            <h3><i class="bi bi-list-ul"></i> وحدات التوليد</h3>
            <span class="sidebar-badge"><span id="sidebarCount">0</span> وحدة</span>
        </div>
        <div class="sidebar-body">
            <ul class="units-list" id="unitsList"></ul>
        </div>
    </div>

    {{-- Stats Bar (Bottom Center) --}}
    <div class="map-stats-bar" id="stats"></div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
<script>
    window.markerIconsBase = '{{ asset("assets/leaflet/images/markers") }}';
    window.markerShadowPath = '{{ asset("assets/leaflet/images/marker-shadow.png") }}';
    window.territoriesRoute = '{{ route("front.territories.map") }}';
    window.operatorsRoute = '{{ route("front.operators.map") }}';
</script>
<script src="{{ asset('assets/front/js/map.js') }}"></script>
@endpush

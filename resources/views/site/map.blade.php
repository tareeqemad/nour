@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'خريطة وحدات التوليد - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/front/css/map.css') }}" />
@endpush

@section('content')
    <div class="map-page">
    <!-- Hero Section -->
    <div class="map-hero-section">
        <div class="map-hero-content">
            <h1 class="map-hero-title">
                <i class="bi bi-geo-alt-fill me-2"></i>
                خريطة وحدات التوليد
            </h1>
            <p class="map-hero-subtitle">
                استكشف مواقع وحدات التوليد على خريطة تفاعلية شاملة لجميع محافظات قطاع غزة
            </p>
        </div>
    </div>

        <div class="map-container-wrapper">
        <!-- Controls Panel -->
        <div class="map-controls-panel">
            <div class="controls-header">
                <i class="bi bi-funnel-fill"></i>
                <h3>فلترة وحدات التوليد</h3>
            </div>
            
            <div class="filter-row">
                <div class="filter-group">
                    <label for="governorate">
                        <i class="bi bi-geo-alt"></i>
                        المحافظة
                    </label>
                    <select id="governorate" name="governorate" class="form-select">
                        <option value="">-- اختر المحافظة --</option>
                        @foreach($governorates as $governorate)
                            <option value="{{ $governorate->value }}">{{ $governorate->label }}</option>
                        @endforeach
                        <option value="all">جميع المحافظات</option>
                    </select>
                </div>
                
                <div class="search-button-group">
                    <button type="button" id="searchBtn" class="search-btn">
                        <i class="bi bi-search me-1"></i>
                        بحث
                    </button>
                </div>
                
                <div class="territory-toggle">
                    <input type="checkbox" id="showTerritories" checked>
                    <label for="showTerritories">إظهار المناطق الجغرافية</label>
                </div>
            </div>
            
            <div class="stats-grid" id="stats" style="display: none;">
                <!-- سيتم ملؤها ديناميكياً -->
            </div>
        </div>

        <!-- Main Map Layout -->
        <div class="main-map-layout hidden" id="mainMapLayout">
            <!-- Sidebar -->
            <div class="map-sidebar" id="sidebar" style="display: none;">
                    <div class="sidebar-header">
                    <h4>
                        <i class="bi bi-list-ul me-2"></i>
                        قائمة وحدات التوليد
                    </h4>
                    <div class="sidebar-count">
                        <i class="bi bi-lightning-charge"></i>
                        <span id="sidebarCount">0</span> وحدة
                    </div>
                </div>
                <div class="sidebar-content">
                    <ul class="units-list" id="unitsList">
                        <!-- سيتم ملؤها ديناميكياً -->
                    </ul>
                </div>
                </div>
                
            <!-- Map -->
                <div class="map-wrapper">
                    <div class="map-container">
                    <div class="map-type-controls">
                        <button id="mapTypeStreet" class="map-type-btn active">
                            <i class="bi bi-map me-1"></i>
                            خريطة تفصيلية
                        </button>
                        <button id="mapTypeSatellite" class="map-type-btn">
                            <i class="bi bi-globe me-1"></i>
                            قمر صناعي
                        </button>
                        </div>
                        
                    <div class="loading-overlay" id="loading">
                        <div style="text-align: center;">
                            <div class="loading-spinner"></div>
                            <p class="loading-text">جاري تحميل البيانات...</p>
                        </div>
                        </div>
                        
                    <div class="empty-state" id="noOperators">
                        <div class="empty-state-icon">
                            <i class="bi bi-inbox"></i>
                        </div>
                            <h3>لا توجد وحدات توليد</h3>
                            <p>لا توجد وحدات توليد في المحافظة المختارة</p>
                        </div>
                        
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
<script>
    // Pass configuration to JavaScript
    window.markerIconsBase = '{{ asset("assets/leaflet/images/markers") }}';
    window.markerShadowPath = '{{ asset("assets/leaflet/images/marker-shadow.png") }}';
    window.territoriesRoute = '{{ route("front.territories.map") }}';
    window.operatorsRoute = '{{ route("front.operators.map") }}';
</script>
<script src="{{ asset('assets/front/js/map.js') }}"></script>
@endpush

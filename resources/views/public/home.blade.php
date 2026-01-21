@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'خريطة المشغلين - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}" />
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }
        
        body {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);
            min-height: 100vh;
        }
        
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .main-content {
            display: flex;
            gap: 20px;
            flex-direction: row;
        }
        
        .sidebar {
            width: 350px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px;
            max-height: 700px;
            overflow-y: auto;
            flex-shrink: 0;
        }
        
        .sidebar-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .sidebar-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .sidebar-header .count {
            font-size: 14px;
            color: #64748b;
        }
        
        .operators-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .operators-list li {
            padding: 12px;
            margin-bottom: 8px;
            background: #f8fafc;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        
        .operators-list li:hover {
            background: #f1f5f9;
            border-color: #3b82f6;
            transform: translateX(-3px);
        }
        
        .operators-list li.active {
            background: #eff6ff;
            border-color: #3b82f6;
        }
        
        .governorate-section {
            margin-bottom: 20px;
        }
        
        .governorate-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .governorate-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .governorate-operators {
            padding-right: 8px;
        }
        
        .operator-item-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
            margin-bottom: 4px;
        }
        
        .operator-item-details {
            font-size: 12px;
            color: #64748b;
        }
        
        .map-wrapper {
            flex: 1;
            min-width: 0;
        }
        
        .map-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 0;
            background: transparent;
            border: none;
            box-shadow: none;
            position: relative;
        }
        
        .map-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            display: inline-block;
            padding-bottom: 1.5rem;
        }
        
        .map-header h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 4px;
            background: linear-gradient(90deg, transparent 0%, #3b82f6 20%, #10b981 50%, #3b82f6 80%, transparent 100%);
            border-radius: 2px;
        }
        
        .map-header p {
            font-size: 1.2rem;
            color: #64748b;
            font-weight: 500;
            position: relative;
            padding-bottom: 2rem;
            margin-top: 1rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .map-header p::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            max-width: 600px;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, rgba(59, 130, 246, 0.2) 20%, rgba(16, 185, 129, 0.3) 50%, rgba(59, 130, 246, 0.2) 80%, transparent 100%);
        }
        
        .controls {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .controls h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1e293b;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #475569;
            font-size: 14px;
        }
        
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Tajawal', sans-serif;
            background: #ffffff;
            color: #1e293b;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .map-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            overflow: hidden;
            height: 600px;
            position: relative;
            isolation: isolate;
        }
        
        #map {
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: none;
        }
        
        .loading.active {
            display: block;
        }
        
        .loading-spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .no-operators {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            text-align: center;
            display: none;
        }
        
        .no-operators.active {
            display: block;
        }
        
        .no-operators h3 {
            color: #64748b;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .no-operators p {
            color: #94a3b8;
            font-size: 14px;
        }
        
        .info-window {
            padding: 10px;
        }
        
        .info-window h3 {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .info-window p {
            font-size: 14px;
            color: #64748b;
            margin: 4px 0;
        }
        
        .info-window .label {
            font-weight: 600;
            color: #475569;
        }
        
        .stats {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .stat-card {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #e2e8f0;
        }
        
        .stat-card-governorate {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .stat-card-count {
            font-size: 24px;
            font-weight: 700;
            color: #3b82f6;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stat-label {
            font-weight: 600;
            color: #64748b;
            font-size: 14px;
        }
        
        .stat-value {
            font-weight: 700;
            color: #3b82f6;
            font-size: 18px;
        }
        
        .info-window {
            min-width: 280px;
            max-width: 350px;
        }
        
        .info-window h3 {
            background: #2563eb;
            color: white;
            padding: 12px 15px;
            margin: -10px -10px 12px -10px;
            border-radius: 8px 8px 0 0;
            font-size: 16px;
            font-weight: 700;
        }
        
        .info-window .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .info-window .info-row:last-child {
            border-bottom: none;
        }
        
        .info-window .info-label {
            font-weight: 600;
            color: #475569;
            font-size: 13px;
        }
        
        .info-window .info-value {
            color: #1e293b;
            font-size: 13px;
            text-align: left;
            flex: 1;
            margin-right: 10px;
        }
        
        .info-window .info-value a {
            color: #2563eb;
            text-decoration: underline;
            font-weight: 500;
        }
        
        .info-window .info-value a:hover {
            color: #1d4ed8;
        }
        
        .info-window .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 5px;
        }
        
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-info {
            background: #dbeafe;
            color: #3b82f6;
        }
        
        .map-container {
            height: 700px;
        }
        
        /* تخصيص النوافذ المنبثقة */
        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .leaflet-popup-content {
            margin: 0;
        }
        
        .custom-popup .leaflet-popup-content-wrapper {
            padding: 0;
        }
        
        /* تخصيص العلامات */
        .custom-marker {
            background: transparent !important;
            border: none !important;
        }
        
        .map-controls {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 1000;
            background: white;
            padding: 8px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 140px;
            pointer-events: auto;
        }
        
        .map-controls button {
            display: block;
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Tajawal', sans-serif;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            transition: all 0.2s ease;
            text-align: right;
        }
        
        .map-controls button:hover {
            background: #f8fafc;
            border-color: #3b82f6;
            color: #3b82f6;
            transform: translateX(-2px);
        }
        
        .map-controls button.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-color: #3b82f6;
            color: white;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);
        }
        
        .map-controls button.active:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateX(-2px);
        }
        
        @media (max-width: 1024px) {
            .main-content {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                max-height: 300px;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 14px 15px;
            }
            
            .map-header h1 {
                font-size: 2rem;
            }
            
            .map-header p {
                font-size: 1rem;
            }
            
            .map-container {
                height: 500px;
            }
            
            .info-window {
                min-width: 250px;
                max-width: 300px;
            }
            
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .map-controls {
                top: 10px;
                right: 10px;
                padding: 6px;
                min-width: 120px;
            }
            
            .map-controls button {
                padding: 8px 12px;
                font-size: 12px;
            }
        }
        
        @media (max-width: 480px) {
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="map-header">
            <h1>خريطة وحدات التوليد</h1>
            <p>استكشف مواقع وحدات التوليد على خريطة تفاعلية شاملة لجميع محافظات غزة</p>
        </div>
        
        <div class="controls">
            <h2>اختر المحافظة</h2>
            <div class="form-group">
                <label for="governorate">المحافظة</label>
                <select id="governorate" name="governorate">
                    <option value="">-- اختر المحافظة --</option>
                    @foreach($governorates as $governorate)
                        <option value="{{ $governorate->value }}">{{ $governorate->label }}</option>
                    @endforeach
                    <option value="all">جميع المحافظات</option>
                </select>
            </div>
            
            <div class="stats" id="stats" style="display: none;">
                <!-- سيتم ملؤها ديناميكياً -->
            </div>
        </div>
        
        <div class="main-content">
            <div class="sidebar" id="sidebar" style="display: none;">
                <div class="sidebar-header">
                    <h3>قائمة وحدات التوليد</h3>
                    <div class="count">عدد الوحدات: <span id="sidebarCount">0</span></div>
                </div>
                <ul class="operators-list" id="operatorsList">
                    <!-- سيتم ملؤها ديناميكياً -->
                </ul>
            </div>
            
            <div class="map-wrapper">
                <div class="map-container">
                    <div class="map-controls">
                        <button id="mapTypeStreet" class="active">خريطة تفصيلية</button>
                        <button id="mapTypeSatellite">قمر صناعي</button>
                    </div>
                    
                    <div class="loading" id="loading">
                        <div class="loading-spinner"></div>
                        <p>جاري تحميل البيانات...</p>
                    </div>
                    
                    <div class="no-operators" id="noOperators">
                        <h3>لا توجد وحدات توليد</h3>
                        <p>لا توجد وحدات توليد في المحافظة المختارة</p>
                    </div>
                    
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>
    
@endsection

@push('scripts')
<script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
<script>
        // إحداثيات قطاع غزة الافتراضية (لإظهار القطاع كاملاً)
        const defaultLat = 31.3547;
        const defaultLng = 34.3088;
        const defaultZoom = 10.5;
        
        // تهيئة الخريطة
        let map = L.map('map').setView([defaultLat, defaultLng], defaultZoom);
        
        // طبقات الخريطة مع معالم أكثر
        const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        });
        
        // طبقة OpenStreetMap مع معالم محسّنة
        const detailedStreetLayer = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Tiles style by <a href="https://www.hot.openstreetmap.org/" target="_blank">HOT</a>',
            maxZoom: 19
        });
        
        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '&copy; <a href="https://www.esri.com/">Esri</a>',
            maxZoom: 19
        });
        
        
        // إضافة الطبقة الافتراضية (مع معالم محسّنة)
        let currentLayer = detailedStreetLayer;
        currentLayer.addTo(map);
        
        // مجموعة العلامات
        let markersGroup = L.layerGroup().addTo(map);
        
        // تخزين المشغلين الحاليين
        let currentOperators = [];
        let currentMarkers = {};
        
        // عناصر DOM
        const governorateSelect = document.getElementById('governorate');
        const loadingDiv = document.getElementById('loading');
        const noOperatorsDiv = document.getElementById('noOperators');
        const sidebar = document.getElementById('sidebar');
        const operatorsList = document.getElementById('operatorsList');
        const sidebarCount = document.getElementById('sidebarCount');
        const statsDiv = document.getElementById('stats');
        
        // أزرار تغيير نوع الخريطة
        const mapTypeStreet = document.getElementById('mapTypeStreet');
        const mapTypeSatellite = document.getElementById('mapTypeSatellite');
        
        // معالجة تغيير نوع الخريطة
        function changeMapType(type) {
            map.removeLayer(currentLayer);
            
            // إزالة active من جميع الأزرار
            mapTypeStreet.classList.remove('active');
            mapTypeSatellite.classList.remove('active');
            
            switch(type) {
                case 'street':
                    currentLayer = detailedStreetLayer; // استخدام الطبقة المحسّنة
                    mapTypeStreet.classList.add('active');
                    break;
                case 'satellite':
                    currentLayer = satelliteLayer;
                    mapTypeSatellite.classList.add('active');
                    break;
            }
            
            currentLayer.addTo(map);
        }
        
        mapTypeStreet.addEventListener('click', () => changeMapType('street'));
        mapTypeSatellite.addEventListener('click', () => changeMapType('satellite'));
        
        // دالة لإظهار/إخفاء التحميل
        function showLoading(show) {
            if (show) {
                loadingDiv.classList.add('active');
                noOperatorsDiv.classList.remove('active');
            } else {
                loadingDiv.classList.remove('active');
            }
        }
        
        // دالة لإظهار رسالة عدم وجود مشغلين
        function showNoOperators(show) {
            if (show) {
                noOperatorsDiv.classList.add('active');
            } else {
                noOperatorsDiv.classList.remove('active');
            }
        }
        
        // دالة لتحميل المشغلين وعرضهم على الخريطة
        async function loadOperators(governorate) {
            if (!governorate || governorate === '') {
                markersGroup.clearLayers();
                showNoOperators(false);
                statsDiv.style.display = 'none';
                sidebar.style.display = 'none';
                currentOperators = [];
                currentMarkers = {};
                // إعادة الخريطة للإحداثيات الافتراضية
                map.setView([defaultLat, defaultLng], defaultZoom);
                return;
            }
            
            showLoading(true);
            markersGroup.clearLayers();
            statsDiv.style.display = 'none';
            sidebar.style.display = 'none';
            currentOperators = [];
            currentMarkers = {};
            
            try {
                const response = await fetch(`{{ route('front.operators.map') }}?governorate=${governorate}`);
                const data = await response.json();
                
                showLoading(false);
                
                if (data.success && data.data.length > 0) {
                    showNoOperators(false);
                    
                    // حفظ البيانات
                    currentOperators = data.data;
                    currentMarkers = {};
                    
                    // تحديث الإحصائيات
                    updateStats(data.data);
                    
                    // إظهار القائمة الجانبية
                    sidebar.style.display = 'block';
                    updateSidebar(data.data);
                    
                    // إضافة علامات للمشغلين
                    const bounds = [];
                    
                    // ألوان مختلفة حسب المحافظة (ألوان Leaflet الأصلية)
                    const markerColors = {
                        'غزة': 'blue',
                        'الوسطى': 'green',
                        'خانيونس': 'orange',
                        'رفح': 'red'
                    };
                    
                    // دالة لإنشاء أيقونة Leaflet بألوان مختلفة
                    function createColoredIcon(color) {
                        return L.icon({
                            iconUrl: `${markerIconsBase}/marker-icon-2x-${color}.png`,
                            shadowUrl: markerShadowPath,
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        });
                    }
                    
                    data.data.forEach((unit, index) => {
                        const color = markerColors[unit.governorate] || 'blue';
                        const icon = createColoredIcon(color);
                        
                        const marker = L.marker([unit.latitude, unit.longitude], {
                            icon: icon
                        }).addTo(markersGroup);
                        
                        // حفظ المرجع
                        currentMarkers[unit.id] = marker;
                        
                        // إنشاء محتوى النافذة المعلوماتية - البيانات الأساسية فقط
                        let popupContent = `
                            <div class="info-window">
                                <h3>${unit.name}</h3>
                                ${unit.governorate ? `
                                    <div class="info-row">
                                        <span class="info-label">المحافظة:</span>
                                        <span class="info-value">${unit.governorate}</span>
                                    </div>
                                ` : ''}
                                ${unit.city ? `
                                    <div class="info-row">
                                        <span class="info-label">المدينة:</span>
                                        <span class="info-value">${unit.city}</span>
                                    </div>
                                ` : ''}
                                ${unit.operator_name ? `
                                    <div class="info-row">
                                        <span class="info-label">المشغل:</span>
                                        <span class="info-value">${unit.operator_name}</span>
                                    </div>
                                ` : ''}
                                ${unit.phone ? `
                                    <div class="info-row">
                                        <span class="info-label">الهاتف:</span>
                                        <span class="info-value"><a href="tel:${unit.phone}">${unit.phone}</a></span>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                        
                        marker.bindPopup(popupContent, {
                            maxWidth: 350,
                            className: 'custom-popup'
                        });
                        
                        // إضافة حدث للنقر على العلامة
                        marker.on('click', function() {
                            highlightOperatorInSidebar(unit.id);
                        });
                        
                        bounds.push([unit.latitude, unit.longitude]);
                    });
                    
                    // تكبير الخريطة لتشمل جميع العلامات
                    if (bounds.length > 0) {
                        if (bounds.length === 1) {
                            map.setView(bounds[0], 15);
                        } else {
                            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
                        }
                    }
                } else {
                    showNoOperators(true);
                    sidebar.style.display = 'none';
                    statsDiv.style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading operators:', error);
                showLoading(false);
                showNoOperators(true);
                alert('حدث خطأ أثناء تحميل البيانات. يرجى المحاولة مرة أخرى.');
            }
        }
        
        // دالة لتحديث الإحصائيات
        function updateStats(units) {
            const stats = {};
            
            units.forEach(unit => {
                const gov = unit.governorate || 'غير محدد';
                stats[gov] = (stats[gov] || 0) + 1;
            });
            
            let statsHTML = '';
            Object.keys(stats).forEach(gov => {
                statsHTML += `
                    <div class="stat-card">
                        <div class="stat-card-governorate">${gov}</div>
                        <div class="stat-card-count">${stats[gov]}</div>
                    </div>
                `;
            });
            
            statsDiv.innerHTML = statsHTML;
            statsDiv.style.display = 'grid';
        }
        
        // دالة لتحديث القائمة الجانبية
        function updateSidebar(units) {
            sidebarCount.textContent = units.length;
            operatorsList.innerHTML = '';
            
            // التحقق إذا كان هناك أكثر من محافظة (جميع المحافظات)
            const uniqueGovernorates = [...new Set(units.map(unit => unit.governorate).filter(Boolean))];
            const isMultipleGovernorates = uniqueGovernorates.length > 1;
            
            if (isMultipleGovernorates) {
                // تجميع وحدات التوليد حسب المحافظة
                const groupedByGovernorate = {};
                units.forEach(unit => {
                    const gov = unit.governorate || 'غير محدد';
                    if (!groupedByGovernorate[gov]) {
                        groupedByGovernorate[gov] = [];
                    }
                    groupedByGovernorate[gov].push(unit);
                });
                
                // ترتيب المحافظات
                const governorateOrder = ['غزة', 'الوسطى', 'خانيونس', 'رفح'];
                const sortedGovernorates = Object.keys(groupedByGovernorate).sort((a, b) => {
                    const indexA = governorateOrder.indexOf(a);
                    const indexB = governorateOrder.indexOf(b);
                    if (indexA === -1 && indexB === -1) return a.localeCompare(b);
                    if (indexA === -1) return 1;
                    if (indexB === -1) return -1;
                    return indexA - indexB;
                });
                
                // إنشاء أقسام لكل محافظة
                sortedGovernorates.forEach(governorate => {
                    const section = document.createElement('div');
                    section.className = 'governorate-section';
                    
                    const header = document.createElement('div');
                    header.className = 'governorate-header';
                    header.innerHTML = `
                        <span>${governorate}</span>
                        <span class="governorate-count">${groupedByGovernorate[governorate].length}</span>
                    `;
                    section.appendChild(header);
                    
                    const unitsContainer = document.createElement('div');
                    unitsContainer.className = 'governorate-operators';
                    
                    groupedByGovernorate[governorate].forEach(unit => {
                        const li = document.createElement('li');
                        li.dataset.operatorId = unit.id;
                        li.innerHTML = `
                            <div class="operator-item-name">${unit.name}</div>
                            <div class="operator-item-details">
                                ${unit.unit_code ? unit.unit_code + '<br>' : ''}
                                ${unit.city ? unit.city : ''}
                                ${unit.phone ? '<br>📞 ' + unit.phone : ''}
                            </div>
                        `;
                        
                        li.addEventListener('click', function() {
                            const marker = currentMarkers[unit.id];
                            if (marker) {
                                map.setView([unit.latitude, unit.longitude], 15);
                                marker.openPopup();
                                highlightOperatorInSidebar(unit.id);
                            }
                        });
                        
                        unitsContainer.appendChild(li);
                    });
                    
                    section.appendChild(unitsContainer);
                    operatorsList.appendChild(section);
                });
            } else {
                // محافظة واحدة - عرض عادي بدون أقسام
                units.forEach(unit => {
                    const li = document.createElement('li');
                    li.dataset.operatorId = unit.id;
                    li.innerHTML = `
                        <div class="operator-item-name">${unit.name}</div>
                        <div class="operator-item-details">
                            ${unit.unit_code ? unit.unit_code + '<br>' : ''}
                            ${unit.city ? unit.city : ''}
                            ${unit.phone ? '<br>📞 ' + unit.phone : ''}
                        </div>
                    `;
                    
                    li.addEventListener('click', function() {
                        const marker = currentMarkers[unit.id];
                        if (marker) {
                            map.setView([unit.latitude, unit.longitude], 15);
                            marker.openPopup();
                            highlightOperatorInSidebar(unit.id);
                        }
                    });
                    
                    operatorsList.appendChild(li);
                });
            }
        }
        
        // دالة لتحديد المشغل في القائمة الجانبية
        function highlightOperatorInSidebar(operatorId) {
            const items = operatorsList.querySelectorAll('li');
            items.forEach(item => {
                if (item.dataset.operatorId == operatorId) {
                    item.classList.add('active');
                    // Scroll to the parent section if exists
                    const section = item.closest('.governorate-section');
                    if (section) {
                        section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } else {
                        item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                } else {
                    item.classList.remove('active');
                }
            });
        }
        
        // الاستماع لتغيير المحافظة
        governorateSelect.addEventListener('change', function() {
            const governorate = this.value;
            loadOperators(governorate);
        });
    </script>
@endpush


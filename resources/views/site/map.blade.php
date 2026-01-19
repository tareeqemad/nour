@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'خريطة المشغلين - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="{{ asset('assets/front/css/map.css') }}">
@endpush

@section('content')
    <div class="map-page">
        <div class="map-container-wrapper">
            <div class="map-header">
                <h1>خريطة وحدات التوليد</h1>
                <p>استكشف مواقع وحدات التوليد على خريطة تفاعلية شاملة لجميع محافظات غزة</p>
            </div>
            
            <div class="filter-card">
                <div class="card-header">
                    <h6 class="card-title">
                        <i class="bi bi-funnel me-2"></i>
                        اختر المحافظة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="governorate">
                                <i class="bi bi-geo-alt me-1"></i>
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
                    </div>
                    
                    <div class="stats mt-3" id="stats" style="display: none;">
                        <!-- سيتم ملؤها ديناميكياً -->
                    </div>
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
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
                        iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${color}.png`,
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
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

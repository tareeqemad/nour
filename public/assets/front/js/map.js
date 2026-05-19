/**
 * Public Map JavaScript
 * Handles interactive map functionality for generation units
 */
(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        defaultLat: 31.3547,
        defaultLng: 34.3088,
        defaultZoom: 10.5,
        markerIconsBase: window.markerIconsBase || '/assets/leaflet/images/markers',
        markerShadowPath: window.markerShadowPath || '/assets/leaflet/images/marker-shadow.png',
        routes: {
            territories: window.territoriesRoute || '/api/territories/map',
            operators: window.operatorsRoute || '/api/operators/map'
        }
    };

        // Initialize map
    let map = L.map('map').setView([CONFIG.defaultLat, CONFIG.defaultLng], CONFIG.defaultZoom);
    
    // Map layers
    const detailedStreetLayer = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Tiles style by <a href="https://www.hot.openstreetmap.org/" target="_blank">HOT</a>',
            maxZoom: 19
        });
        
        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; <a href="https://www.esri.com/">Esri</a>',
            maxZoom: 19
        });
        
    let currentLayer = detailedStreetLayer;
        currentLayer.addTo(map);
        
    // Layer groups
        let markersGroup = L.layerGroup().addTo(map);
    let territoriesGroup = L.layerGroup().addTo(map);
    
    // Data storage
    let currentUnits = [];
        let currentMarkers = {};
    let currentTerritories = [];
    let territoryCircles = {};
        
    // DOM elements
    const governorateSelect = document.getElementById('governorate');
    const searchBtn = document.getElementById('searchBtn');
    const showTerritoriesCheckbox = document.getElementById('showTerritories');
    const loadingOverlay = document.getElementById('loading');
    const noOperatorsDiv = document.getElementById('noOperators');
    const sidebar = document.getElementById('sidebar');
    const unitsList = document.getElementById('unitsList');
    const sidebarCount = document.getElementById('sidebarCount');
    const statsDiv = document.getElementById('stats');
    const mapTypeStreet = document.getElementById('mapTypeStreet');
    const mapTypeSatellite = document.getElementById('mapTypeSatellite');
    const mainMapLayout = null; // Removed in new layout
    
    /**
     * Change map type
     */
    function changeMapType(type) {
        map.removeLayer(currentLayer);
        mapTypeStreet.classList.remove('active');
        mapTypeSatellite.classList.remove('active');
        
        if (type === 'street') {
            currentLayer = detailedStreetLayer;
            mapTypeStreet.classList.add('active');
        } else {
            currentLayer = satelliteLayer;
            mapTypeSatellite.classList.add('active');
        }
        
        currentLayer.addTo(map);
    }
    
    /**
     * Show/hide loading overlay
     */
        function showLoading(show) {
            if (show) {
            loadingOverlay.classList.add('active');
            noOperatorsDiv.classList.remove('active');
            } else {
            loadingOverlay.classList.remove('active');
        }
        }
        
    /**
     * Show/hide empty state
     */
        function showNoOperators(show) {
            if (show) {
            noOperatorsDiv.classList.add('active');
            } else {
            noOperatorsDiv.classList.remove('active');
        }
    }
    
    /**
     * Calculate distance between two points (Haversine formula)
     */
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = 
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    /**
     * Load territories from server
     */
    async function loadTerritories() {
        const showTerritories = showTerritoriesCheckbox ? showTerritoriesCheckbox.checked : true;
        
        if (!showTerritories) {
            territoriesGroup.clearLayers();
            territoryCircles = {};
            return;
        }
        
        try {
            const response = await fetch(CONFIG.routes.territories);
            const data = await response.json();
            
            if (data.success && data.territories) {
                territoriesGroup.clearLayers();
                territoryCircles = {};
                
                data.territories.forEach(territory => {
                    const radiusMeters = territory.radius_km * 1000;
                    const colors = ['#24308F', '#10B981', '#F59E0B', '#EF4444', '#0EA5E9', '#8B5CF6', '#06b6d4'];
                    const colorIndex = territory.operator_id % colors.length;
                    const color = colors[colorIndex];
                    
                    const circle = L.circle([territory.center_latitude, territory.center_longitude], {
                        radius: radiusMeters,
                        color: color,
                        fillColor: color,
                        fillOpacity: 0.2,
                        weight: 2
                    }).addTo(territoriesGroup);
                    
                    const areaKm2 = Math.PI * territory.radius_km * territory.radius_km;
                    
                    const popupContent = `
                        <div class="nour-popup">
                            <div class="nour-popup-head">
                                <div class="nour-popup-icon nour-popup-icon-territory"><i class="bi bi-bullseye"></i></div>
                                <div>
                                    <div class="nour-popup-title">${territory.name || 'منطقة جغرافية'}</div>
                                    <div class="nour-popup-code">${areaKm2.toFixed(2)} كم²</div>
                                </div>
                            </div>
                            <div class="nour-popup-body">
                                <div class="nour-popup-row">
                                    <i class="bi bi-building"></i>
                                    <span class="nour-popup-label">المشغل</span>
                                    <span class="nour-popup-val">${territory.operator_name || 'غير محدد'}</span>
                                </div>
                                <div class="nour-popup-row">
                                    <i class="bi bi-person"></i>
                                    <span class="nour-popup-label">المالك</span>
                                    <span class="nour-popup-val">${territory.owner_name || 'غير محدد'}</span>
                                </div>
                                ${territory.generation_unit ? `
                                    <div class="nour-popup-row">
                                        <i class="bi bi-lightning-charge"></i>
                                        <span class="nour-popup-label">وحدة التوليد</span>
                                        <span class="nour-popup-val">${territory.generation_unit.name}</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                    
                    circle.bindPopup(popupContent, {
                        maxWidth: 300,
                        className: 'territory-popup-wrapper'
                    });
                    
                    territoryCircles[territory.id] = circle;
                });
                
                currentTerritories = data.territories;
            }
        } catch (error) {
            console.error('Error loading territories:', error);
        }
    }
    
    /**
     * Load generation units from server
     */
    async function loadUnits(governorate) {
        if (!governorate || governorate === '') {
            // Hide map layout when no governorate is selected
            if (mainMapLayout) {
                mainMapLayout.classList.add('hidden');
            }
            markersGroup.clearLayers();
            showNoOperators(false);
            statsDiv.classList.remove('visible');
            sidebar.classList.remove('visible');
            currentUnits = [];
            currentMarkers = {};
            map.setView([CONFIG.defaultLat, CONFIG.defaultLng], CONFIG.defaultZoom);
            return;
        }
        
        // Show map layout when governorate is selected
        if (mainMapLayout) {
            mainMapLayout.classList.remove('hidden');
            // Make map full width initially
            mainMapLayout.classList.add('full-width');
        }
            
            showLoading(true);
            markersGroup.clearLayers();
        statsDiv.classList.remove('visible');
            sidebar.classList.remove('visible');
        currentUnits = [];
            currentMarkers = {};
            
            try {
            const response = await fetch(`${CONFIG.routes.operators}?governorate=${governorate}`);
                const data = await response.json();
                
                showLoading(false);
                
                if (data.success && data.data.length > 0) {
                    showNoOperators(false);
                    currentUnits = data.data;
                    currentMarkers = {};
                    
                    // Remove full-width class to show sidebar
                    if (mainMapLayout) {
                        mainMapLayout.classList.remove('full-width');
                    }
                    
                    updateStats(data.data);
                    sidebar.classList.add('visible');
                    updateSidebar(data.data);
                    
                    const bounds = [];
                const markerColors = {
                    'غزة': 'blue',
                    'الوسطى': 'green',
                    'خانيونس': 'orange',
                    'رفح': 'red'
                };
                
                function createColoredIcon(color) {
                    return L.icon({
                        iconUrl: `${CONFIG.markerIconsBase}/marker-icon-2x-${color}.png`,
                        shadowUrl: CONFIG.markerShadowPath,
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });
                }
                
                data.data.forEach((unit) => {
                    const color = markerColors[unit.governorate] || 'blue';
                        const icon = createColoredIcon(color);
                        
                    const marker = L.marker([unit.latitude, unit.longitude], {
                            icon: icon
                        }).addTo(markersGroup);
                        
                    currentMarkers[unit.id] = marker;
                    
                    const popupContent = `
                        <div class="nour-popup">
                            <div class="nour-popup-head">
                                <div class="nour-popup-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                                <div>
                                    <div class="nour-popup-title">${unit.name}</div>
                                    ${unit.unit_code ? `<div class="nour-popup-code">${unit.unit_code}</div>` : ''}
                                </div>
                            </div>
                            <div class="nour-popup-body">
                                ${unit.operator_name ? `
                                    <div class="nour-popup-row">
                                        <i class="bi bi-building"></i>
                                        <span class="nour-popup-label">المشغل</span>
                                        <span class="nour-popup-val">${unit.operator_name}</span>
                                    </div>
                                ` : ''}
                                ${unit.governorate ? `
                                    <div class="nour-popup-row">
                                        <i class="bi bi-geo-alt"></i>
                                        <span class="nour-popup-label">المحافظة</span>
                                        <span class="nour-popup-val">${unit.governorate}</span>
                                    </div>
                                ` : ''}
                                ${unit.city ? `
                                    <div class="nour-popup-row">
                                        <i class="bi bi-pin-map"></i>
                                        <span class="nour-popup-label">المدينة</span>
                                        <span class="nour-popup-val">${unit.city}</span>
                                    </div>
                                ` : ''}
                                ${unit.detailed_address ? `
                                    <div class="nour-popup-row">
                                        <i class="bi bi-signpost-2"></i>
                                        <span class="nour-popup-label">العنوان</span>
                                        <span class="nour-popup-val">${unit.detailed_address}</span>
                                    </div>
                                ` : ''}
                                ${unit.phone ? `
                                    <div class="nour-popup-row nour-popup-phone">
                                        <i class="bi bi-telephone-fill"></i>
                                        <span class="nour-popup-label">الهاتف</span>
                                        <a href="tel:${unit.phone}" class="nour-popup-val">${unit.phone}</a>
                                    </div>
                                ` : ''}
                                ${unit.phone_alt ? `
                                    <div class="nour-popup-row nour-popup-phone">
                                        <i class="bi bi-telephone"></i>
                                        <span class="nour-popup-label">هاتف بديل</span>
                                        <a href="tel:${unit.phone_alt}" class="nour-popup-val">${unit.phone_alt}</a>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                        `;
                        
                    marker.bindPopup(popupContent, {
                        maxWidth: 350,
                        className: 'custom-popup'
                    });
                        
                        marker.on('click', function() {
                        highlightUnitInSidebar(unit.id);
                    });
                    
                    bounds.push([unit.latitude, unit.longitude]);
                });
                
                    if (bounds.length > 0) {
                        if (bounds.length === 1) {
                            map.setView(bounds[0], 15);
                        } else {
                            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
                        }
                    }
                
                await loadTerritories();
                } else {
                    // Keep map full width when no units found
                    if (mainMapLayout) {
                        mainMapLayout.classList.add('full-width');
                    }
                    showNoOperators(true);
                    sidebar.classList.remove('visible');
                    statsDiv.classList.remove('visible');
                    await loadTerritories();
                }
        } catch (error) {
            console.error('Error loading units:', error);
            showLoading(false);
            showNoOperators(true);
        }
    }
    
    /**
     * Update statistics
     */
    function updateStats(units) {
        const stats = {};
        
        units.forEach(unit => {
            const gov = unit.governorate || 'غير محدد';
                stats[gov] = (stats[gov] || 0) + 1;
            });
            
        let statsHTML = `<div class="map-stat"><div class="map-stat-val">${units.length}</div><div class="map-stat-label">إجمالي الوحدات</div></div>`;
            Object.keys(stats).forEach(gov => {
                statsHTML += `
                <div class="map-stat">
                    <div class="map-stat-val">${stats[gov]}</div>
                    <div class="map-stat-label">${gov}</div>
                </div>
                `;
            });
        
        statsDiv.innerHTML = statsHTML;
        statsDiv.classList.add('visible');
    }
    
    /**
     * Update sidebar
     */
    function updateSidebar(units) {
        sidebarCount.textContent = units.length;
        unitsList.innerHTML = '';
        
        const uniqueGovernorates = [...new Set(units.map(unit => unit.governorate).filter(Boolean))];
            const isMultipleGovernorates = uniqueGovernorates.length > 1;
            
            if (isMultipleGovernorates) {
                const groupedByGovernorate = {};
            units.forEach(unit => {
                const gov = unit.governorate || 'غير محدد';
                    if (!groupedByGovernorate[gov]) {
                        groupedByGovernorate[gov] = [];
                    }
                groupedByGovernorate[gov].push(unit);
                });
                
                const governorateOrder = ['غزة', 'الوسطى', 'خانيونس', 'رفح'];
                const sortedGovernorates = Object.keys(groupedByGovernorate).sort((a, b) => {
                    const indexA = governorateOrder.indexOf(a);
                    const indexB = governorateOrder.indexOf(b);
                    if (indexA === -1 && indexB === -1) return a.localeCompare(b);
                    if (indexA === -1) return 1;
                    if (indexB === -1) return -1;
                    return indexA - indexB;
                });
                
                sortedGovernorates.forEach(governorate => {
                    const section = document.createElement('div');
                    section.className = 'governorate-section';
                    
                    const header = document.createElement('div');
                    header.className = 'governorate-header';
                    header.innerHTML = `
                        <span>${governorate}</span>
                    <span class="governorate-badge">${groupedByGovernorate[governorate].length}</span>
                    `;
                    section.appendChild(header);
                    
                const unitsContainer = document.createElement('div');
                unitsContainer.className = 'governorate-operators';
                
                groupedByGovernorate[governorate].forEach(unit => {
                    const li = document.createElement('li');
                    li.className = 'unit-item';
                    li.dataset.unitId = unit.id;
                    li.innerHTML = `
                        <div class="unit-name">${unit.name}</div>
                        <div class="unit-details">
                            ${unit.unit_code ? `<i class="bi bi-hash"></i>${unit.unit_code}<br>` : ''}
                            ${unit.city ? `<i class="bi bi-geo-alt"></i>${unit.city}` : ''}
                            ${unit.phone ? `<br><i class="bi bi-telephone"></i>${unit.phone}` : ''}
                        </div>
                    `;
                    
                    li.addEventListener('click', function() {
                        const marker = currentMarkers[unit.id];
                        if (marker) {
                            map.setView([unit.latitude, unit.longitude], 15);
                            marker.openPopup();
                            highlightUnitInSidebar(unit.id);
                        }
                    });
                    
                    unitsContainer.appendChild(li);
                });
                
                section.appendChild(unitsContainer);
                unitsList.appendChild(section);
                });
            } else {
            units.forEach(unit => {
            const li = document.createElement('li');
                li.className = 'unit-item';
                li.dataset.unitId = unit.id;
            li.innerHTML = `
                    <div class="unit-name">${unit.name}</div>
                    <div class="unit-details">
                        ${unit.unit_code ? `<i class="bi bi-hash"></i>${unit.unit_code}<br>` : ''}
                        ${unit.city ? `<i class="bi bi-geo-alt"></i>${unit.city}` : ''}
                        ${unit.phone ? `<br><i class="bi bi-telephone"></i>${unit.phone}` : ''}
                </div>
            `;
            
            li.addEventListener('click', function() {
                    const marker = currentMarkers[unit.id];
                if (marker) {
                        map.setView([unit.latitude, unit.longitude], 15);
                    marker.openPopup();
                        highlightUnitInSidebar(unit.id);
                    }
                });
                
                unitsList.appendChild(li);
            });
        }
    }
    
    /**
     * Highlight unit in sidebar
     */
    function highlightUnitInSidebar(unitId) {
        const items = unitsList.querySelectorAll('.unit-item');
            items.forEach(item => {
            if (item.dataset.unitId == unitId) {
                    item.classList.add('active');
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
        
        /**
     * Handle search button click
     */
    function handleSearch() {
        const governorate = governorateSelect ? governorateSelect.value : '';
        if (governorate) {
            loadUnits(governorate);
        } else {
            // Show message if no governorate selected
            alert('يرجى اختيار المحافظة أولاً');
        }
    }
    
    // Ensure map renders correctly in full-screen layout
    setTimeout(function() { map.invalidateSize(); }, 200);

    // Event listeners
    if (mapTypeStreet) {
        mapTypeStreet.addEventListener('click', () => changeMapType('street'));
    }
    
    if (mapTypeSatellite) {
        mapTypeSatellite.addEventListener('click', () => changeMapType('satellite'));
    }
    
    // Search button click event
    if (searchBtn) {
        searchBtn.addEventListener('click', handleSearch);
    }
    
    // Allow Enter key to trigger search
    if (governorateSelect) {
        governorateSelect.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSearch();
            }
        });
    }
    
    if (showTerritoriesCheckbox) {
        showTerritoriesCheckbox.addEventListener('change', function() {
            loadTerritories();
        });
    }
    
    // Don't load territories on page load - wait for user to select governorate
    // loadTerritories();
    
    // Reload territories on zoom/move (only if map is visible)
    map.on('zoomend', function() {
        if (mainMapLayout && !mainMapLayout.classList.contains('hidden')) {
            loadTerritories();
        }
    });
    map.on('moveend', function() {
        if (mainMapLayout && !mainMapLayout.classList.contains('hidden')) {
            loadTerritories();
        }
    });
})();

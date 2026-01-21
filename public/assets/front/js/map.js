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
    const mainMapLayout = document.getElementById('mainMapLayout');
    
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
                    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];
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
                        <div class="territory-popup">
                            <div class="territory-popup-header">
                                <h4>
                                    <i class="bi bi-geo-alt-fill"></i>
                                    ${territory.name || 'منطقة جغرافية'}
                                </h4>
                            </div>
                            <div class="territory-popup-content">
                                <div class="territory-info-row">
                                    <span class="territory-info-label">المشغل:</span>
                                    <span class="territory-info-value">${territory.operator_name || 'غير محدد'}</span>
                                </div>
                                <div class="territory-info-row">
                                    <span class="territory-info-label">المالك:</span>
                                    <span class="territory-info-value">${territory.owner_name || 'غير محدد'}</span>
                                </div>
                                <div class="territory-info-row">
                                    <span class="territory-info-label">المساحة:</span>
                                    <span class="territory-info-value">${areaKm2.toFixed(2)} كم²</span>
                                </div>
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
            statsDiv.style.display = 'none';
            sidebar.style.display = 'none';
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
        statsDiv.style.display = 'none';
            sidebar.style.display = 'none';
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
                    sidebar.style.display = 'flex';
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
                        <div class="unit-popup">
                            <div class="unit-popup-header">
                                <h3>${unit.name}</h3>
                            </div>
                            <div class="unit-popup-content">
                                ${unit.governorate ? `
                                    <div class="unit-info-row">
                                        <span class="unit-info-label">المحافظة:</span>
                                        <span class="unit-info-value">${unit.governorate}</span>
                                    </div>
                                ` : ''}
                                ${unit.city ? `
                                    <div class="unit-info-row">
                                        <span class="unit-info-label">المدينة:</span>
                                        <span class="unit-info-value">${unit.city}</span>
                                    </div>
                                ` : ''}
                                ${unit.operator_name ? `
                                    <div class="unit-info-row">
                                        <span class="unit-info-label">المشغل:</span>
                                        <span class="unit-info-value">${unit.operator_name}</span>
                                    </div>
                                ` : ''}
                                ${unit.phone ? `
                                    <div class="unit-info-row">
                                        <span class="unit-info-label">الهاتف:</span>
                                        <span class="unit-info-value"><a href="tel:${unit.phone}">${unit.phone}</a></span>
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
                    sidebar.style.display = 'none';
                    statsDiv.style.display = 'none';
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
            
        let statsHTML = '';
            Object.keys(stats).forEach(gov => {
                statsHTML += `
                <div class="stat-card">
                    <div class="stat-card-label">${gov}</div>
                    <div class="stat-card-value">${stats[gov]}</div>
                    </div>
                `;
            });
        
        statsDiv.innerHTML = statsHTML;
        statsDiv.style.display = 'grid';
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

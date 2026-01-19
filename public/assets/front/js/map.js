// Map Page JavaScript
(function() {
    'use strict';

    // Use variables defined in page, or fallback to defaults
    const lat = typeof window.defaultLat !== 'undefined' ? window.defaultLat : 31.3547;
    const lng = typeof window.defaultLng !== 'undefined' ? window.defaultLng : 34.3088;
    const zoom = typeof window.defaultZoom !== 'undefined' ? window.defaultZoom : 10.5;
    const apiUrl = typeof window.mapApiUrl !== 'undefined' ? window.mapApiUrl : '/api/operators/map';

    // Wait for DOM and Leaflet to be ready
    function initMap() {
        // Check if Leaflet is loaded
        if (typeof L === 'undefined') {
            console.error('Leaflet library not loaded');
            setTimeout(initMap, 100);
            return;
        }
        
        // Check if map element exists
        const mapElement = document.getElementById('map');
        if (!mapElement) {
            console.error('Map element not found');
            setTimeout(initMap, 100);
            return;
        }

        // Initialize map
        const map = L.map('map').setView([lat, lng], zoom);
        
        // Tile layers
        const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        });
        
        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '&copy; Esri',
            maxZoom: 19
        });
        
        let currentLayer = streetLayer;
        currentLayer.addTo(map);
        
        // Markers group
        let markersGroup = L.layerGroup().addTo(map);
        let currentOperators = [];
        let currentMarkers = {};
        
        // DOM elements
        const governorateSelect = document.getElementById('governorate');
        const loadingDiv = document.getElementById('loading');
        const noOperatorsDiv = document.getElementById('noOperators');
        const sidebar = document.getElementById('sidebar');
        const operatorsList = document.getElementById('operatorsList');
        const sidebarCount = document.getElementById('sidebarCount');
        const statsPreview = document.getElementById('statsPreview');
        
        if (!governorateSelect || !loadingDiv || !noOperatorsDiv || !sidebar || !operatorsList || !sidebarCount || !statsPreview) {
            console.error('Some required DOM elements not found');
            return;
        }
        
        // Marker colors by governorate
        const markerColors = {
            'غزة': 'blue',
            'الوسطى': 'green',
            'خانيونس': 'orange',
            'رفح': 'red'
        };
        
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
        
        function showLoading(show) {
            if (show) {
                loadingDiv.style.display = 'flex';
                noOperatorsDiv.style.display = 'none';
            } else {
                loadingDiv.style.display = 'none';
            }
        }
        
        function showNoOperators(show) {
            if (show) {
                noOperatorsDiv.style.display = 'flex';
                sidebar.style.display = 'none';
                statsPreview.style.display = 'none';
            } else {
                noOperatorsDiv.style.display = 'none';
            }
        }
        
        async function loadOperators(governorate) {
            if (!governorate || governorate === '') {
                markersGroup.clearLayers();
                showNoOperators(false);
                sidebar.style.display = 'none';
                statsPreview.style.display = 'none';
                currentOperators = [];
                currentMarkers = {};
                map.setView([lat, lng], zoom);
                return;
            }
            
            showLoading(true);
            markersGroup.clearLayers();
            sidebar.style.display = 'none';
            statsPreview.style.display = 'none';
            currentOperators = [];
            currentMarkers = {};
            
            try {
                const response = await fetch(`${apiUrl}?governorate=${governorate}`);
                const data = await response.json();
                
                showLoading(false);
                
                if (data.success && data.data.length > 0) {
                    showNoOperators(false);
                    currentOperators = data.data;
                    currentMarkers = {};
                    
                    // Update stats
                    updateStats(data.data);
                    
                    // Show sidebar
                    sidebar.style.display = 'block';
                    updateSidebar(data.data);
                    
                    // Add markers
                    const bounds = [];
                    data.data.forEach((operator) => {
                        const color = markerColors[operator.governorate] || 'blue';
                        const icon = createColoredIcon(color);
                        
                        const marker = L.marker([operator.latitude, operator.longitude], {
                            icon: icon
                        }).addTo(markersGroup);
                        
                        currentMarkers[operator.id] = marker;
                        
                        let popupContent = `
                            <div class="popup-content">
                                <h3>${operator.name}</h3>
                                ${operator.governorate ? `<p><strong>المحافظة:</strong> ${operator.governorate}</p>` : ''}
                                ${operator.city ? `<p><strong>المدينة:</strong> ${operator.city}</p>` : ''}
                                ${operator.phone ? `<p><strong>الهاتف:</strong> <a href="tel:${operator.phone}">${operator.phone}</a></p>` : ''}
                            </div>
                        `;
                        
                        marker.bindPopup(popupContent);
                        
                        marker.on('click', function() {
                            highlightOperatorInSidebar(operator.id);
                        });
                        
                        bounds.push([operator.latitude, operator.longitude]);
                    });
                    
                    // Fit bounds
                    if (bounds.length > 0) {
                        if (bounds.length === 1) {
                            map.setView(bounds[0], 15);
                        } else {
                            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
                        }
                    }
                } else {
                    showNoOperators(true);
                }
            } catch (error) {
                console.error('Error loading operators:', error);
                showLoading(false);
                showNoOperators(true);
                alert('حدث خطأ أثناء تحميل البيانات. يرجى المحاولة مرة أخرى.');
            }
        }
        
        function updateStats(operators) {
            const stats = {};
            operators.forEach(op => {
                const gov = op.governorate || 'غير محدد';
                stats[gov] = (stats[gov] || 0) + 1;
            });
            
            let statsHTML = '<div class="stats-grid">';
            Object.keys(stats).forEach(gov => {
                statsHTML += `
                    <div class="stat-item">
                        <span class="stat-label">${gov}</span>
                        <span class="stat-value">${stats[gov]}</span>
                    </div>
                `;
            });
            statsHTML += '</div>';
            
            statsPreview.innerHTML = statsHTML;
            statsPreview.style.display = 'block';
        }
        
        function updateSidebar(operators) {
            sidebarCount.textContent = operators.length;
            operatorsList.innerHTML = '';
            
            const uniqueGovernorates = [...new Set(operators.map(op => op.governorate).filter(Boolean))];
            const isMultipleGovernorates = uniqueGovernorates.length > 1;
            
            if (isMultipleGovernorates) {
                const groupedByGovernorate = {};
                operators.forEach(operator => {
                    const gov = operator.governorate || 'غير محدد';
                    if (!groupedByGovernorate[gov]) {
                        groupedByGovernorate[gov] = [];
                    }
                    groupedByGovernorate[gov].push(operator);
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
                        <span class="count-badge">${groupedByGovernorate[governorate].length}</span>
                    `;
                    section.appendChild(header);
                    
                    const operatorsContainer = document.createElement('div');
                    operatorsContainer.className = 'governorate-operators';
                    
                    groupedByGovernorate[governorate].forEach(operator => {
                        const li = createOperatorListItem(operator);
                        operatorsContainer.appendChild(li);
                    });
                    
                    section.appendChild(operatorsContainer);
                    operatorsList.appendChild(section);
                });
            } else {
                operators.forEach(operator => {
                    const li = createOperatorListItem(operator);
                    operatorsList.appendChild(li);
                });
            }
        }
        
        function createOperatorListItem(operator) {
            const li = document.createElement('li');
            li.dataset.operatorId = operator.id;
            const cityHtml = operator.city ? `<span>${operator.city}</span>` : '';
            const phoneHtml = operator.phone ? `<span>📞 ${operator.phone}</span>` : '';
            li.innerHTML = `
                <div class="operator-name">${operator.name}</div>
                <div class="operator-details">
                    ${cityHtml}
                    ${phoneHtml}
                </div>
            `;
            
            li.addEventListener('click', function() {
                const marker = currentMarkers[operator.id];
                if (marker) {
                    map.setView([operator.latitude, operator.longitude], 15);
                    marker.openPopup();
                    highlightOperatorInSidebar(operator.id);
                }
            });
            
            return li;
        }
        
        function highlightOperatorInSidebar(operatorId) {
            const items = operatorsList.querySelectorAll('li');
            items.forEach(item => {
                if (item.dataset.operatorId == operatorId) {
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
        
        // Event listeners
        governorateSelect.addEventListener('change', function() {
            loadOperators(this.value);
        });
        
        // Hide loading overlay initially - map is ready
        showLoading(false);
        
        // Trigger map resize after a short delay to ensure proper rendering
        setTimeout(function() {
            map.invalidateSize();
        }, 100);
    }
    
    // Initialize when DOM and scripts are ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initMap, 200);
        });
    } else {
        setTimeout(initMap, 200);
    }
})();

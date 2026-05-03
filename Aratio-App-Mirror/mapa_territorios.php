<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor Territorial - Aratio</title>
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🗺️</text></svg>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        magenta: '#FF00FF',
                        gold: '#ae9454',
                        dark: '#0f172a',
                        'dark-semitransparent': 'rgba(15, 23, 42, 0.95)',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { margin: 0; padding: 0; overflow: hidden; background: #0f172a; }
        #map { height: 100vh; width: 100%; z-index: 0; }
        
        /* Custom Scrollbar for Sidebar */
        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        
        /* Leaflet Controls Customization */
        .leaflet-control-zoom { border: none !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important; }
        .leaflet-control-zoom a { background-color: #1e293b !important; color: white !important; border-bottom: 1px solid #334155 !important; }
        .leaflet-control-zoom a:hover { background-color: #334155 !important; color: #FF00FF !important; }

        [x-cloak] { display: none !important; }
        
        /* Glassmorphism */
        .glass {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body x-data="{ sidebarOpen: false, isLoading: false }" 
      @loading-state.window="isLoading = $event.detail"
      @close-sidebar.window="sidebarOpen = false"
      class="text-white">

    <!-- Loading Overlay -->
    <div x-show="isLoading" class="fixed inset-0 z-[3000] bg-dark/80 backdrop-blur-sm flex items-center justify-center" x-cloak>
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-magenta"></div>
    </div>

    <!-- Mobile Header (Visible only on small screens) -->
    <div class="md:hidden fixed top-0 left-0 right-0 z-[2000] p-4 flex justify-between items-center glass border-b border-gray-800">
        <div class="flex items-center gap-2">
            <span class="text-2xl">🗺️</span>
            <span class="font-bold text-lg text-gold tracking-wide">VISOR TERRITORIAL</span>
        </div>
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 bg-gray-800/50 rounded-lg text-magenta hover:bg-gray-700 transition">
            <i data-lucide="filter" class="w-6 h-6"></i>
        </button>
    </div>

    <!-- Sidebar / Filter Panel -->
    <div 
        class="fixed inset-y-0 left-0 z-[2500] w-80 glass transform transition-transform duration-300 ease-in-out md:translate-x-0 flex flex-col shadow-2xl"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        <!-- Sidebar Header -->
        <div class="p-6 border-b border-gray-800 flex justify-between items-center bg-gradient-to-r from-dark to-transparent">
            <div>
                <h2 class="text-xl font-bold text-gold flex items-center gap-2">
                    <span class="hidden md:inline">🗺️</span>
                    Filtros & Datos
                </h2>
                <p class="text-xs text-slate-400 mt-1">Explora la jerarquía territorial</p>
            </div>
            <button class="md:hidden text-slate-400 hover:text-white" @click="sidebarOpen = false">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Filters Scrollable Area -->
        <div class="flex-1 overflow-y-auto p-6 space-y-5 custom-scroll">
            
            <!-- Departamento -->
            <div class="space-y-2">
                <label class="text-xs font-semibold text-magenta uppercase tracking-wider">Departamento</label>
                <div class="relative">
                    <select id="departamento" class="w-full bg-slate-800/50 border border-slate-700 text-white text-sm rounded-lg focus:ring-magenta focus:border-magenta block p-2.5 appearance-none">
                        <option value="VALLE DEL CAUCA">Valle del Cauca</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <!-- Buscador Rápido -->
            <div class="space-y-2">
                <label class="text-xs font-semibold text-gold uppercase tracking-wider">Buscador Rápido</label>
                <div class="relative group">
                    <input 
                        type="text" 
                        id="searchBox" 
                        placeholder="Puesto o barrio..."
                        class="w-full bg-slate-800/50 border border-slate-700 text-white text-sm rounded-lg focus:ring-magenta focus:border-magenta block p-2.5 pl-10 transition-all outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center px-3 pointer-events-none text-slate-500">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <!-- Municipio -->
            <div class="space-y-2">
                <label class="text-xs font-semibold text-magenta uppercase tracking-wider">Municipio</label>
                <div class="relative">
                    <select id="municipio" class="w-full bg-slate-800/50 border border-slate-700 text-white text-sm rounded-lg focus:ring-magenta focus:border-magenta block p-2.5 appearance-none">
                        <option value="">Seleccione...</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <!-- Tipo de Territorio -->
            <div class="space-y-2">
                <label class="text-xs font-semibold text-magenta uppercase tracking-wider">Tipo Territorio</label>
                <div class="relative">
                    <select id="tipo" disabled class="w-full bg-slate-800/50 border border-slate-700 text-slate-300 text-sm rounded-lg focus:ring-magenta focus:border-magenta block p-2.5 disabled:opacity-50 appearance-none">
                        <option value="">Seleccione...</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <!-- Sector / Comuna -->
            <div class="space-y-2">
                <label class="text-xs font-semibold text-magenta uppercase tracking-wider">Sector / Comuna</label>
                <div class="relative">
                    <select id="territorio" disabled class="w-full bg-slate-800/50 border border-slate-700 text-slate-300 text-sm rounded-lg focus:ring-magenta focus:border-magenta block p-2.5 disabled:opacity-50 appearance-none">
                        <option value="">Seleccione...</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                        <i data-lucide="grid" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <!-- Barrio / Vereda -->
            <div class="space-y-2">
                <label class="text-xs font-semibold text-magenta uppercase tracking-wider">Barrio / Vereda</label>
                <div class="relative">
                    <select id="barrio" disabled class="w-full bg-slate-800/50 border border-slate-700 text-slate-300 text-sm rounded-lg focus:ring-magenta focus:border-magenta block p-2.5 disabled:opacity-50 appearance-none">
                        <option value="">Seleccione...</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                        <i data-lucide="home" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="mt-6 p-4 bg-gold/10 border-l-4 border-gold rounded-r-lg">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gold font-medium">Polígonos visibles</span>
                    <span id="stats" class="text-xl font-bold text-white">0</span>
                </div>
            </div>

        </div>

        <!-- Footer / Back Button -->
        <div class="p-4 border-t border-gray-800 bg-slate-900/50">
            <?php if (!isset($_GET['mode']) || $_GET['mode'] !== 'embed'): ?>
            <a href="index.php" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition text-sm font-medium border border-slate-700">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Volver al Dashboard</span>
            </a>
            <?php else: ?>
            <div class="text-center">
                <span class="text-xs text-slate-500 flex items-center justify-center gap-1">
                    <i data-lucide="mouse-pointer-2" class="w-3 h-3"></i>
                    Modo Interactivo
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Map Info Card (Floating) -->
    <div id="info" class="hidden absolute bottom-6 right-6 z-[1000] w-72 glass rounded-xl p-4 border border-gold/30 shadow-2xl animate-fade-in-up">
        <!-- Content injected by JS -->
    </div>

    <!-- Main Map Container -->
    <div class="absolute inset-0 z-0">
        <div id="map"></div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Init Icons
        lucide.createIcons();

        // Config
        const API_URL = 'api_territorios_verified.php';
        const GEO_API_URL = 'api_territorios_geojson.php';
        
        // DOM Elements
        const selDep = document.getElementById('departamento');
        const selMun = document.getElementById('municipio');
        const selTipo = document.getElementById('tipo');
        const selTer = document.getElementById('territorio');
        const selBar = document.getElementById('barrio');
        const statsEl = document.getElementById('stats');
        const searchBox = document.getElementById('searchBox');
        
        let allPuestos = []; // Caché para búsqueda instantánea
        
        // DOM Elements
        // (Selectores ya definidos arriba)
        
        // Alpine Store shortcut (FIXED)
        const setLoading = (state) => {
            // Disparar evento personalizado que Alpine escuchará
            window.dispatchEvent(new CustomEvent('loading-state', { detail: state }));
        };

        // --- MAP INIT ---
        const map = L.map('map', { zoomControl: false }).setView([3.4516, -76.5320], 12);
        
        // Zoom control on right (Desktop) or hidden/custom place
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CARTO'
        }).addTo(map);

        const geojsonLayer = L.layerGroup().addTo(map);
        const markersLayer = L.layerGroup().addTo(map);

        // --- LAYER CONTROL ---
        const overlays = {
            "Territorios": geojsonLayer,
            "Puestos": markersLayer
        };
        // Simple dark layer control
        L.control.layers(null, overlays, { collapsed: true, position: 'topright' }).addTo(map);

        // --- LOGIC ---

        window.onload = async () => {
            setLoading(true);
            await loadOptions(selMun, 'municipios', { departamento: selDep.value });
            setLoading(false);
        };

        selMun.onchange = async () => {
            resetSelects([selTipo, selTer, selBar]);
            if (!selMun.value) {
                geojsonLayer.clearLayers();
                markersLayer.clearLayers();
                return;
            }
            
            setLoading(true);

            // 1. Cargar opciones de UI primero (rápido)
            try {
                await loadOptions(selTipo, 'tipos_territorio', { 
                    departamento: selDep.value, 
                    municipio: selMun.value 
                });
            } catch (e) { console.error("Error cargando tipos:", e); }

            // 2. Actualizar mapa después (lento)
            try {
                await updateMap(); 
            } catch (e) { console.error("Error actualizando mapa:", e); }
            
            setLoading(false);
        };

        selTipo.onchange = async () => {
            resetSelects([selTer, selBar]);
            setLoading(true);
            await updateMap();
            if (selTipo.value) {
                await loadOptions(selTer, 'territorios', { 
                    departamento: selDep.value, 
                    municipio: selMun.value,
                    tipo_territorio: selTipo.value
                });
            }
            setLoading(false);
        };

        selTer.onchange = async () => {
            resetSelects([selBar]);
            setLoading(true);
            await updateMap();
            if (selTer.value) {
                await loadOptions(selBar, 'barrios', { 
                    departamento: selDep.value, 
                    municipio: selMun.value,
                    tipo_territorio: selTipo.value,
                    territorio: selTer.value
                });
            }
            setLoading(false);
        };

        selBar.onchange = () => {
            if (!selBar.value) {
                // Si se deselecciona barrio, volver a vista de sector
                updateMap();
                return;
            }
            
            // Buscar y enfocar el barrio específico dentro de la capa actual
            let found = false;
            geojsonLayer.eachLayer(geoLayer => {
                geoLayer.eachLayer(layer => {
                    if (layer.feature.properties.barrio === selBar.value) {
                        map.fitBounds(layer.getBounds(), { maxZoom: 16 });
                        highlightFeature({ target: layer });
                        found = true;
                        
                        // Close sidebar on mobile
                        if(window.innerWidth < 768) {
                            window.dispatchEvent(new CustomEvent('close-sidebar'));
                        }
                    }
                });
            });
            
            if (!found) {
                // Fallback por si el barrio no está renderizado (raro si la lógica es correcta)
                console.warn("Barrio seleccionado no encontrado en la capa actual");
            }
        };

        // --- BUSCADOR ---
        searchBox.oninput = () => {
            const term = searchBox.value.toLowerCase().trim();
            if (!term) {
                renderMarkers(allPuestos);
                return;
            }
            const filtered = allPuestos.filter(p => 
                (p.puesto && p.puesto.toLowerCase().includes(term)) || 
                (p.direccion && p.direccion.toLowerCase().includes(term)) ||
                (p.comuna && p.comuna.toLowerCase().includes(term))
            );
            renderMarkers(filtered);
        };

        // --- HELPERS ---

        async function updateMap() {
            // Construir filtros activos
            const filters = {
                municipio: selMun.value,
                tipo: selTipo.value,
                territorio: selTer.value
            };
            
            // Cargar polígonos filtrados
            await showPolygons(filters);
            
            // Cargar puestos de votación del municipio (siempre visible si hay municipio)
            if (selMun.value) {
                const boothFilters = { municipio: selMun.value };
                if (selTer.value && selTipo.value === 'Urbano') boothFilters.comuna = selTer.value;
                await loadVotersBooths(boothFilters);
            } else {
                markersLayer.clearLayers();
            }
        }

        async function loadOptions(selectEl, accion, params) {
            if (!selectEl) return;
            selectEl.disabled = true;
            const queryParams = new URLSearchParams({ accion, ...params }).toString();
            
            try {
                const res = await fetch(`${API_URL}?${queryParams}`);
                const json = await res.json();
                
                if (json.success && json.data) {
                    selectEl.innerHTML = '<option value="">Seleccione...</option>';
                    json.data.forEach(val => {
                        if (!val) return;
                        const opt = document.createElement('option');
                        opt.value = opt.textContent = val;
                        selectEl.appendChild(opt);
                    });
                }
            } catch (e) { 
                console.error(e);
            } finally {
                selectEl.disabled = false;
            }
        }

        function resetSelects(selects) {
            selects.forEach(s => {
                s.innerHTML = '<option value="">Seleccione...</option>';
                s.disabled = true;
            });
            if(searchBox) searchBox.value = '';
        }

        async function showPolygons(filters) {
            if (!filters.municipio) {
                geojsonLayer.clearLayers();
                return;
            }
            
            const queryParams = new URLSearchParams(filters).toString();
            try {
                const res = await fetch(`${GEO_API_URL}?${queryParams}`);
                const data = await res.json();
                
                geojsonLayer.clearLayers();
                const newLayer = L.geoJSON(data, {
                    style: featStyle,
                    onEachFeature: onEachFeature
                });
                geojsonLayer.addLayer(newLayer);

                if (data.features && data.features.length > 0) {
                    map.fitBounds(newLayer.getBounds(), { padding: [20, 20] });
                    statsEl.textContent = data.metadata.count;
                } else {
                    statsEl.textContent = "0";
                }
            } catch (err) { console.error(err); }
        }

        async function loadVotersBooths(filters) {
            if (!filters || !filters.municipio) {
                markersLayer.clearLayers();
                allPuestos = [];
                return;
            }
            try {
                const queryParams = new URLSearchParams(filters).toString();
                const res = await fetch(`api_puestos_standalone.php?${queryParams}`);
                allPuestos = await res.json();
                renderMarkers(allPuestos);
            } catch(e) { console.error(e); }
        }

        function renderMarkers(data) {
            markersLayer.clearLayers();
            const pinSvg = `<svg width="30" height="42" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21C16 16.8 19 13.5 19 9.33C19 5.28 15.87 2 12 2C8.13 2 5 5.28 5 9.33C5 13.5 8 16.8 12 21ZM12 11.83C10.62 11.83 9.5 10.71 9.5 9.33C9.5 7.95 10.62 6.83 12 6.83C13.38 6.83 14.5 7.95 14.5 9.33C14.5 10.71 13.38 11.83 12 11.83Z" fill="#FFD700" stroke="#000" stroke-width="1"/></svg>`;
            const icon = L.divIcon({ html: pinSvg, className: '', iconSize: [30, 42], iconAnchor: [15, 42] });

            data.forEach(p => {
                if (p.latitud && p.longitud) {
                    const popup = `
                        <div class="text-dark p-1" style="min-width:160px;">
                            <strong class="block text-sm border-b pb-1 mb-1">${p.puesto}</strong>
                            <div class="text-[11px] text-gray-600 space-y-1">
                                <p class="flex items-center gap-1">📍 ${p.direccion}</p>
                                ${p.comuna ? `<p class="font-bold text-magenta uppercase text-[9px]">Sector: ${p.comuna}</p>` : ''}
                            </div>
                        </div>`;
                    L.marker([p.latitud, p.longitud], { icon: icon }).bindPopup(popup).addTo(markersLayer);
                }
            });
        }

        // --- STYLES ---

        function featStyle(feature) {
            return {
                fillColor: feature.properties.Tipo_territorio === 'Urbano' ? '#FF00FF' : '#ae9454',
                weight: 1,
                opacity: 1,
                color: 'white',
                dashArray: '3',
                fillOpacity: 0.2
            };
        }

        function highlightFeature(e) {
            var layer = e.target;
            layer.setStyle({ weight: 3, color: '#FFD700', dashArray: '', fillOpacity: 0.6 });

            var props = layer.feature.properties;
            var info = document.getElementById('info');
            info.classList.remove('hidden');
            info.innerHTML = `
                <h4 class="text-gold font-bold text-lg mb-1">${props.barrio}</h4>
                <div class="text-sm text-slate-300">
                    <p><strong>Sector:</strong> ${props.Territorio}</p>
                    <p class="text-xs mt-1 text-slate-400">${props.Tipo_territorio} • ${props.municipio}</p>
                </div>
            `;
        }

        function resetHighlight(e) {
            if (selBar.value !== e.target.feature.properties.barrio) {
                geojsonLayer.eachLayer(g => { if(g.resetStyle) g.resetStyle(e.target); });
                document.getElementById('info').classList.add('hidden');
            }
        }

        function onEachFeature(feature, layer) {
            layer.on({
                mouseover: highlightFeature,
                mouseout: resetHighlight,
                click: (e) => {
                    map.fitBounds(e.target.getBounds());
                    if(window.innerWidth < 768) {
                         window.dispatchEvent(new CustomEvent('close-sidebar'));
                    }
                }
            });
        }
    </script>
</body>
</html>

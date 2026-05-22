<?php
$masterPath = BASE_PATH . '/storage/maestro_instagram.json';
$data = null;

if (file_exists($masterPath)) {
    $data = json_decode(file_get_contents($masterPath), true);
}

if (!$data) {
    echo '<div class="p-8 text-center text-gray-500">No hay datos de coordenadas disponibles.</div>';
    return;
}

$posts = [];
foreach ($data['timeline'] as $month => $items) {
    foreach ($items as $p) {
        if (!empty($p['lat']) && !empty($p['lng'])) {
            $posts[] = $p;
        }
    }
}
$total_coords = count($posts);

$cat_counts = [];
foreach ($posts as $p) {
    $cat = $p['categoria'] ?? 'general';
    $cat_counts[$cat] = ($cat_counts[$cat] ?? 0) + 1;
}
ksort($cat_counts);

$colores = [
    'obra' => '#0369a1', 'reunion' => '#d97706', 'general' => '#6b7280',
    'territorio' => '#059669', 'evento' => '#7c3aed', 'proyecto' => '#0d9488',
    'social' => '#db2777', 'gestion' => '#ea580c', 'denuncia' => '#dc2626',
];
?>
<style>
    #mapaInstagram { height: 75vh; width: 100%; border-radius: 1rem; z-index: 1; }
    .map-filter-bar { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .map-filter-chip { padding: 4px 14px; border-radius: 9999px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.2s; border: 2px solid; background: white; }
    .map-filter-chip.active { opacity: 1; }
    .map-filter-chip.inactive { opacity: 0.3; }
    .leaflet-popup-content { margin: 12px 16px; min-width: 200px; }
    .leaflet-popup-content-wrapper { border-radius: 12px !important; }
    .popup-cat { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 4px; }
    .popup-text { font-size: 12px; color: #374151; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin: 6px 0; }
    .popup-meta { font-size: 10px; color: #9ca3af; display: flex; gap: 10px; }
    .popup-link { font-size: 10px; font-weight: 700; color: #1e3a5f; text-decoration: none; }
    .popup-link:hover { text-decoration: underline; }
    .popup-shortcode { font-size: 8px; color: #d1d5db; margin-top: 4px; }
</style>

<div class="space-y-4 pb-12">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-[#1e3a5f] tracking-tight">Mapa de Gestión</h1>
            <p class="text-gray-500 font-medium"><?= $total_coords ?> publicaciones geolocalizadas</p>
        </div>
        <div class="text-sm text-gray-400 font-medium">@<?= $data['concejal']['username'] ?? '' ?></div>
    </div>

    <div id="filtros" class="map-filter-bar">
        <button id="filterAll" class="map-filter-chip active" style="border-color:#1e3a5f;color:#1e3a5f">Todas (<?= $total_coords ?>)</button>
        <?php foreach ($cat_counts as $cat => $cnt):
            $color = $colores[$cat] ?? '#6b7280'; ?>
            <button data-cat="<?= $cat ?>" class="map-filter-chip active" style="border-color:<?= $color ?>;color:<?= $color ?>"><?= $cat ?> (<?= $cnt ?>)</button>
        <?php endforeach; ?>
    </div>

    <div id="mapaInstagram"></div>
</div>

<script>
var posts = <?= json_encode($posts, JSON_UNESCAPED_UNICODE) ?>;
var colores = <?= json_encode($colores, JSON_UNESCAPED_UNICODE) ?>;

function iniciarMapa() {
    if (typeof L === 'undefined') { setTimeout(iniciarMapa, 100); return; }

    var map = L.map('mapaInstagram', { center: [3.45, -76.53], zoom: 12, zoomControl: false });
    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18, attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var allMarkers = [];

    posts.forEach(function(p) {
        var cat = p.categoria || 'general';
        var color = colores[cat] || '#6b7280';
        var icon = L.divIcon({
            className: '',
            html: '<div style="background:'+color+';width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,0.3)"></div>',
            iconSize: [14, 14], iconAnchor: [7, 7]
        });

        var html = '<div>';
        html += '<div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">';
        html += '<span class="popup-cat" style="background:'+color+'"></span>';
        html += '<span style="font-size:10px;font-weight:700;text-transform:uppercase;color:'+color+'">'+cat+'</span>';
        html += '<span style="margin-left:auto;font-size:10px;color:#9ca3af">'+(p.fecha||'')+'</span></div>';
        if (p.ubicacion && p.ubicacion !== 'Ubicaciones') {
            html += '<div style="font-size:10px;color:#6b7280;margin-bottom:4px">📍 '+p.ubicacion+'</div>';
        }
        html += '<div class="popup-text">'+(p.texto||'').substring(0,200)+'</div>';
        html += '<div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px">';
        html += '<div class="popup-meta"><span>❤ '+(p.likes!==null?p.likes:'?')+'</span><span>💬 '+(p.comentarios!==null?p.comentarios:'?')+'</span></div>';
        html += '<a href="'+p.url+'" target="_blank" class="popup-link">Ver →</a></div>';
        html += '<div class="popup-shortcode">'+(p.shortcode||'')+'</div></div>';

        var marker = L.marker([p.lat, p.lng], { icon: icon });
        marker.bindPopup(html, { maxWidth: 280, className: '' });
        marker.categoria = cat;
        marker.addTo(map);
        allMarkers.push(marker);
    });

    // Filter logic
    document.querySelectorAll('.map-filter-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            if (this.id === 'filterAll') {
                document.querySelectorAll('.map-filter-chip[data-cat]').forEach(function(c) {
                    c.classList.remove('inactive'); c.classList.add('active');
                });
            } else {
                this.classList.toggle('active');
                this.classList.toggle('inactive');
                var allInactive = true;
                document.querySelectorAll('.map-filter-chip[data-cat]').forEach(function(c) {
                    if (c.classList.contains('active')) allInactive = false;
                });
                if (allInactive) {
                    document.getElementById('filterAll').click();
                    return;
                }
            }
            aplicarFiltro();
        });
    });

    function aplicarFiltro() {
        var active = [];
        document.querySelectorAll('.map-filter-chip[data-cat].active').forEach(function(c) {
            active.push(c.dataset.cat);
        });
        allMarkers.forEach(function(m) {
            if (active.indexOf(m.categoria) !== -1) {
                if (!map.hasLayer(m)) m.addTo(map);
            } else {
                if (map.hasLayer(m)) map.removeLayer(m);
            }
        });
    }

    if (allMarkers.length > 0) {
        var group = L.featureGroup(allMarkers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
}

iniciarMapa();
</script>

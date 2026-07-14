<?php
/**
 * Vista: Eventos de Campaña
 * Mapa Leaflet + eventos futuros/pasados
 */
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Agenda de <span class="text-[#DAA520]">Campaña</span></h1>
            <p class="text-gray-500 mt-1">Eventos y actividades de tu campaña electoral</p>
        </div>
        <div class="flex gap-2">
            <a href="?page=portal_eventos&show=future" 
               class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2 <?= ($show ?? 'future') === 'future' ? 'bg-[#002244] text-white shadow-lg' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                Próximos
            </a>
            <a href="?page=portal_eventos&show=past" 
               class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2 <?= ($show ?? '') === 'past' ? 'bg-[#DAA520] text-white shadow-lg' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                <i data-lucide="calendar-check" class="w-4 h-4"></i>
                Pasados
            </a>
        </div>
    </div>

    <!-- Mapa de Eventos -->
    <?php if (!empty($mapEvents)): ?>
    <div class="glass-card overflow-hidden border border-gray-100 shadow-sm bg-white">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-lg font-bold flex items-center gap-2 text-gray-800">
                <i data-lucide="map" class="w-5 h-5 text-[#DAA520]"></i>
                Mapa de Eventos
            </h3>
            <p class="text-xs text-gray-500 mt-1"><?= count($mapEvents) ?> eventos con ubicación registrada</p>
        </div>
        <div id="eventsMap" style="height: 350px; width: 100%;"></div>
    </div>
    <?php endif; ?>

    <!-- Filtros por Tipo -->
    <div class="flex gap-2 overflow-x-auto pb-2" x-data="{ tipoFilter: 'all' }">
        <button @click="tipoFilter = 'all'" 
            class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all"
            :class="tipoFilter === 'all' ? 'bg-[#002244] text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'">
            Todos
        </button>
        <button @click="tipoFilter = 'Reunión'" 
            class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all"
            :class="tipoFilter === 'Reunión' ? 'bg-[#002244] text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'">
            Reuniones
        </button>
        <button @click="tipoFilter = 'Caminata'" 
            class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all"
            :class="tipoFilter === 'Caminata' ? 'bg-[#002244] text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'">
            Caminatas
        </button>
        <button @click="tipoFilter = 'Capacitación'" 
            class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all"
            :class="tipoFilter === 'Capacitación' ? 'bg-[#002244] text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'">
            Capacitación
        </button>
        <button @click="tipoFilter = 'Acto Político'" 
            class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all"
            :class="tipoFilter === 'Acto Político' ? 'bg-[#002244] text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'">
            Actos Políticos
        </button>
    </div>

    <!-- Lista de Eventos -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3" x-data="{ tipoFilter: 'all' }">
        <?php if (empty($events)): ?>
            <div class="col-span-full text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-gray-100">
                    <i data-lucide="<?= ($show ?? 'future') === 'past' ? 'calendar-off' : 'calendar-plus' ?>" class="w-10 h-10 text-gray-300"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">
                    <?= ($show ?? 'future') === 'past' ? 'No hay eventos pasados' : 'No hay eventos programados' ?>
                </h3>
                <p class="text-gray-500 max-w-sm mx-auto">
                    <?= ($show ?? 'future') === 'past' 
                        ? 'Los eventos completados aparecerán aquí.' 
                        : 'Próximamente se agregarán nuevas convocatorias.' ?>
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all group <?= ($show ?? 'future') === 'past' ? 'opacity-75' : '' ?>">
                    <!-- Header con fecha -->
                    <div class="h-36 bg-gradient-to-br from-[#002244] to-[#004488] relative p-6 flex items-end">
                        <div class="absolute top-4 right-4 flex gap-2">
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-sm text-white text-xs rounded-full uppercase font-bold tracking-wider">
                                <?= htmlspecialchars($event['tipo'] ?? 'Evento') ?>
                            </span>
                            <?php if (($show ?? 'future') === 'past'): ?>
                                <span class="px-3 py-1 bg-green-500/80 text-white text-xs rounded-full font-bold">Completado</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-white">
                            <p class="text-4xl font-black leading-none"><?= date('d', strtotime($event['fecha_inicio'])) ?></p>
                            <p class="text-blue-200 uppercase text-sm font-bold tracking-wider"><?= date('M Y', strtotime($event['fecha_inicio'])) ?></p>
                        </div>
                    </div>

                    <!-- Contenido -->
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-[#002244] transition-colors">
                            <?= htmlspecialchars($event['nombre']) ?>
                        </h3>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i data-lucide="clock" class="w-4 h-4 text-[#DAA520]"></i>
                                <span><?= date('h:i A', strtotime($event['fecha_inicio'])) ?></span>
                                <?php if (!empty($event['fecha_fin'])): ?>
                                    <span class="text-gray-400">—</span>
                                    <span><?= date('h:i A', strtotime($event['fecha_fin'])) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-start gap-2 text-sm text-gray-600">
                                <i data-lucide="map-pin" class="w-4 h-4 text-[#DAA520] mt-0.5 flex-shrink-0"></i>
                                <span class="line-clamp-1"><?= htmlspecialchars($event['lugar'] ?? $event['ubicacion'] ?? $event['direccion'] ?? 'Ubicación por definir') ?></span>
                            </div>
                        </div>

                        <?php if (!empty($event['descripcion'])): ?>
                            <p class="text-sm text-gray-500 line-clamp-2 mb-4"><?= htmlspecialchars($event['descripcion']) ?></p>
                        <?php endif; ?>

                        <div class="flex gap-2">
                            <button class="flex-1 py-2.5 px-4 border border-[#002244] text-[#002244] rounded-xl hover:bg-[#002244] hover:text-white font-bold text-sm transition-all flex items-center justify-center gap-2">
                                <i data-lucide="info" class="w-4 h-4"></i>
                                Detalles
                            </button>
                            <?php if (!empty($event['latitud']) && !empty($event['longitud'])): ?>
                            <a href="https://www.google.com/maps?q=<?= $event['latitud'] ?>,<?= $event['longitud'] ?>" target="_blank"
                               class="py-2.5 px-4 bg-[#DAA520]/10 text-[#DAA520] rounded-xl hover:bg-[#DAA520] hover:text-white font-bold text-sm transition-all flex items-center justify-center gap-2">
                                <i data-lucide="navigation" class="w-4 h-4"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Paginación -->
    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="flex justify-center gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=portal_eventos&show=<?= $show ?? 'future' ?>&p=<?= $i ?>" 
               class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold transition-all <?= $i === ($page ?? 1) ? 'bg-[#002244] text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<?php if (!empty($mapEvents)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('eventsMap').setView([3.4372, -76.5225], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    const eventos = <?= json_encode(array_map(function($e) {
        return [
            'nombre' => $e['nombre'],
            'tipo' => $e['tipo'] ?? 'Evento',
            'fecha' => date('d/M/Y', strtotime($e['fecha_inicio'])),
            'lugar' => $e['lugar'] ?? $e['ubicacion'] ?? 'Sin lugar',
            'lat' => (float)$e['latitud'],
            'lng' => (float)$e['longitud'],
            'estado' => $e['estado'] ?? 'programado'
        ];
    }, $mapEvents)) ?>;

    const tipoColors = {
        'Reunión': '#002244',
        'Caminata': '#DAA520',
        'Capacitación': '#10B981',
        'Acto Político': '#FF00FF',
        'default': '#3B82F6'
    };

    eventos.forEach(ev => {
        const color = tipoColors[ev.tipo] || tipoColors['default'];
        const marker = L.circleMarker([ev.lat, ev.lng], {
            radius: 10,
            fillColor: color,
            color: '#fff',
            weight: 2,
            fillOpacity: 0.9
        }).addTo(map);

        marker.bindPopup(`
            <div style="min-width:200px">
                <strong style="font-size:14px">${ev.nombre}</strong><br>
                <span style="background:${color};color:#fff;padding:2px 8px;border-radius:12px;font-size:10px;font-weight:bold">${ev.tipo}</span>
                <br><br>
                <small>📅 ${ev.fecha}</small><br>
                <small>📍 ${ev.lugar}</small>
            </div>
        `);
    });

    if (eventos.length > 0) {
        const bounds = L.latLngBounds(eventos.map(e => [e.lat, e.lng]));
        map.fitBounds(bounds, { padding: [30, 30] });
    }
});
</script>
<?php endif; ?>

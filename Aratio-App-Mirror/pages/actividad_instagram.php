<?php
// Preferir el maestro fusionado (todas las corridas), fallback a última corrida
$masterPath = BASE_PATH . '/storage/maestro_instagram.json';
$jsonPath = BASE_PATH . '/storage/instagram_data.json';
$data = null;

if (file_exists($masterPath)) {
    $data = json_decode(file_get_contents($masterPath), true);
} elseif (file_exists($jsonPath)) {
    $data = json_decode(file_get_contents($jsonPath), true);
}

if (!$data) {
    echo '<div class="p-8 text-center text-gray-500">No hay datos de actividad de Instagram disponibles. Ejecute el scraper para actualizar.</div>';
    return;
}

$concejal = $data['concejal'];
$stats = $data['estadisticas'];
$timeline = $data['timeline'];
krsort($timeline); // más reciente primero
foreach ($timeline as $periodo => $posts) {
    $timeline[$periodo] = array_reverse($posts);
}
$acciones = $data['lista_acciones'];
?>

<style>
    .instagram-gradient { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); }
    .post-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .post-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.15); }
    .category-badge { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 2px 8px; border-radius: 9999px; }
    .cat-obra { background: #e0f2fe; color: #0369a1; }
    .cat-reunion { background: #fef3c7; color: #92400e; }
    .cat-general { background: #f3f4f6; color: #374151; }
    .ig-modal { display: none; }
    .ig-modal.active { display: flex; }
</style>

<div class="space-y-8 pb-12" id="igApp">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 instagram-gradient rounded-2xl flex items-center justify-center shadow-lg">
                <i data-lucide="camera" class="w-10 h-10 text-white"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black text-[#1e3a5f] tracking-tight">Monitor de Actividad</h1>
                <p class="text-gray-500 font-medium">Análisis de presencia digital y gestión política</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
                <button id="igSyncBtn"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-[#d4af37] text-white hover:bg-[#c5a032] shadow-md hover:shadow-lg transition-all duration-300"
                    onclick="igSync.iniciar()">
                    <span id="igSyncIcon"><i data-lucide="refresh-cw" class="w-4 h-4"></i></span>
                    <span id="igSyncText">Actualizar Vista</span>
                </button>

            <div class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                <div class="px-4 py-2 text-center border-r border-gray-100">
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Publicaciones</p>
                    <p class="text-lg font-black text-[#1e3a5f]"><?= $stats['total_publicaciones'] ?></p>
                </div>
                <div class="px-4 py-2 text-center">
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Acciones Relevantes</p>
                    <p class="text-lg font-black text-[#d4af37]"><?= $stats['publicaciones_relevantes'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach($stats['categorias'] as $cat => $count): ?>
        <div class="glass-card p-6 rounded-3xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1"><?= $cat ?></p>
                <p class="text-2xl font-black text-[#1e3a5f]"><?= $count ?> <span class="text-sm font-medium text-gray-400">posts</span></p>
            </div>
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-gray-50 border border-gray-100">
                <i data-lucide="<?= $cat == 'obra' ? 'hammer' : ($cat == 'reunion' ? 'users' : 'hash') ?>" class="w-6 h-6 text-[#1e3a5f]/40"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Timeline -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-black text-[#1e3a5f]">Timeline de Gestión</h2>
                <span class="text-xs font-bold text-gray-400"><?= count($timeline) ?> Periodos Detectados</span>
            </div>

            <?php foreach($timeline as $periodo => $posts): ?>
            <div class="relative pl-8 space-y-6">
                <div class="absolute left-3 top-0 bottom-0 w-0.5 bg-gray-100"></div>
                <div class="absolute left-0 top-0 w-6 h-6 bg-white border-4 border-[#1e3a5f] rounded-full -translate-x-1/2"></div>
                <h3 class="text-sm font-black text-[#1e3a5f] uppercase tracking-tighter mb-4"><?= $periodo ?></h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach($posts as $post): ?>
                    <div class="post-card bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-bold text-gray-400"><?= $post['fecha'] ?></span>
                                <span class="category-badge cat-<?= $post['categoria'] ?>"><?= $post['categoria'] ?></span>
                            </div>
                            <?php if (!empty($post['ubicacion'])): ?>
                            <div class="flex items-center gap-1 mb-2">
                                <i data-lucide="map-pin" class="w-3 h-3 text-gray-400"></i>
                                <span class="text-[10px] font-semibold text-gray-500"><?= htmlspecialchars($post['ubicacion']) ?></span>
                            </div>
                            <?php endif; ?>
                            <p class="text-sm text-gray-600 line-clamp-4 mb-4">
                                <?= htmlspecialchars($post['texto']) ?>
                            </p>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="heart" class="w-3 h-3 text-red-500"></i>
                                    <span class="text-xs font-bold text-gray-500"><?= $post['likes'] ?? 0 ?></span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i data-lucide="award" class="w-3 h-3 text-[#d4af37]"></i>
                                    <span class="text-xs font-bold text-gray-500"><?= $post['relevancia_politica'] ?>/10</span>
                                </div>
                            </div>
                            <a href="<?= $post['url'] ?>" target="_blank" class="text-[#1e3a5f] hover:text-[#d4af37] transition">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Acciones Destacadas -->
        <div class="space-y-6">
            <h2 class="text-xl font-black text-[#1e3a5f]">Acciones Clave</h2>
            
            <div class="space-y-4">
                <?php foreach($acciones as $accion): ?>
                <div class="glass-card p-5 rounded-3xl border-l-4 border-l-[#d4af37]">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-black text-[#1e3a5f] uppercase tracking-wider"><?= $accion['accion'] ?></span>
                        <div class="flex items-center gap-1 bg-green-50 px-2 py-0.5 rounded-full">
                            <i data-lucide="trending-up" class="w-3 h-3 text-green-600"></i>
                            <span class="text-[10px] font-bold text-green-700">+<?= $accion['relevancia'] ?></span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed mb-3">
                        <?= htmlspecialchars($accion['descripcion']) ?>
                    </p>
                    <?php if (!empty($accion['ubicacion'])): ?>
                    <div class="flex items-center gap-1 mb-3">
                        <i data-lucide="map-pin" class="w-3 h-3 text-gray-400"></i>
                        <span class="text-[10px] font-medium text-gray-400"><?= htmlspecialchars($accion['ubicacion']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-gray-400"><?= $accion['fecha'] ?></span>
                        <a href="<?= $accion['url'] ?>" target="_blank" class="text-xs font-black text-[#1e3a5f] uppercase hover:underline">Ver Post</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Category Chart -->
            <div class="glass-card p-8 rounded-3xl mt-8">
                <h3 class="text-sm font-black text-[#1e3a5f] uppercase mb-6 text-center">Distribución de Contenido</h3>
                <div class="h-48 flex items-center justify-center">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sync Progress Modal -->
<div id="igModal" class="ig-modal fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-md w-full mx-4">
        <div class="text-center space-y-6">
            <div id="igModalIconRunning" class="w-16 h-16 mx-auto instagram-gradient rounded-2xl flex items-center justify-center animate-pulse" style="display:none">
                <i data-lucide="refresh-cw" class="w-8 h-8 text-white animate-spin"></i>
            </div>
            <div id="igModalIconComplete" class="w-16 h-16 mx-auto bg-green-100 rounded-2xl flex items-center justify-center" style="display:none">
                <i data-lucide="check-circle" class="w-8 h-8 text-green-600"></i>
            </div>
            <div id="igModalIconFailed" class="w-16 h-16 mx-auto bg-red-100 rounded-2xl flex items-center justify-center" style="display:none">
                <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
            </div>

            <div>
                <h3 id="igModalTitle" class="text-lg font-black text-[#1e3a5f]">Sincronizando Actividad</h3>
                <p id="igModalMsg" class="text-sm text-gray-500 mt-2">Iniciando...</p>
            </div>

            <div id="igProgressWrap" class="space-y-2">
                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                    <div id="igProgressBar" class="h-full instagram-gradient rounded-full transition-all duration-500 ease-out" style="width:25%"></div>
                </div>
                <p id="igProgressLabel" class="text-xs text-gray-400 font-medium">Preparando...</p>
            </div>

            <p id="igCountdown" class="text-xs text-gray-400" style="display:none">Recargando en <span id="igCountdownNum">3</span>s...</p>

            <div id="igModalActions" style="display:none">
                <button id="igModalCloseBtn"
                    class="px-6 py-2.5 bg-[#1e3a5f] text-white font-bold rounded-xl hover:bg-[#162d4a] transition-colors text-sm"
                    onclick="igSync.cerrar()">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
var igSync = {
    estado: 'idle',
    polling: null,
    countdownVal: 3,

    iniciar: function() {
        var btn = document.getElementById('igSyncBtn');
        btn.disabled = true;
        btn.className = 'flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-gray-200 text-gray-400 cursor-not-allowed transition-all duration-300';
        document.getElementById('igSyncText').textContent = 'Sincronizando...';
        document.getElementById('igSyncIcon').innerHTML = '<i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i>';
        if (window.lucide) lucide.createIcons();

        this.estado = 'running';
        this.mostrarModal('running', 'Iniciando...', 0, 0);
        
        fetch('api/instagram_sync.php?action=start')
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (data.status === 'started') {
                    igSync.iniciarPolling();
                } else {
                    igSync.mostrarError(data.message || 'Error al iniciar');
                }
            })
            .catch(function(err){
                igSync.mostrarError('Error de conexion: ' + err.message);
            });
    },

    iniciarPolling: function() {
        this.polling = setInterval(function(){ igSync.consultarEstado(); }, 2000);
    },

    consultarEstado: function() {
        fetch('api/instagram_sync.php?action=status')
            .then(function(r){ return r.json(); })
            .then(function(data){
                igSync.estado = data.status || 'idle';
                igSync.mostrarModal(igSync.estado, data.message || '', data.current || 0, data.total || 0);

                if (data.status === 'completed') {
                    clearInterval(igSync.polling);
                    igSync.iniciarCountdown();
                } else if (data.status === 'failed') {
                    clearInterval(igSync.polling);
                }
            })
            .catch(function(err){
                igSync.mostrarError('Error de conexion: ' + err.message);
                clearInterval(igSync.polling);
            });
    },

    iniciarCountdown: function() {
        this.countdownVal = 3;
        document.getElementById('igModalActions').style.display = 'block';
        document.getElementById('igModalCloseBtn').textContent = 'Recargar Ahora';
        document.getElementById('igCountdown').style.display = 'block';
        var interval = setInterval(function(){
            igSync.countdownVal--;
            document.getElementById('igCountdownNum').textContent = igSync.countdownVal;
            if (igSync.countdownVal <= 0) {
                clearInterval(interval);
                window.location.reload();
            }
        }, 1000);
    },

    mostrarModal: function(status, msg, current, total) {
        var m = document.getElementById('igModal');
        m.className = 'ig-modal active fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm';

        document.getElementById('igModalIconRunning').style.display = status === 'running' ? 'flex' : 'none';
        document.getElementById('igModalIconComplete').style.display = status === 'completed' ? 'flex' : 'none';
        document.getElementById('igModalIconFailed').style.display = status === 'failed' ? 'flex' : 'none';

        var titles = { running: 'Sincronizando Actividad', completed: 'Sincronización Completa', failed: 'Error de Sincronización' };
        document.getElementById('igModalTitle').textContent = titles[status] || 'Sincronización';
        document.getElementById('igModalMsg').textContent = msg || '';

        var pw = document.getElementById('igProgressWrap');
        if (status === 'running') {
            pw.style.display = 'block';
            var pct = total > 0 ? (current / total * 100) : 25;
            document.getElementById('igProgressBar').style.width = pct + '%';
            document.getElementById('igProgressLabel').textContent = total > 0 ? current + ' de ' + total + ' publicaciones' : 'Preparando...';
        } else {
            pw.style.display = 'none';
        }

        if (status !== 'running') {
            document.getElementById('igModalActions').style.display = 'block';
            document.getElementById('igModalCloseBtn').textContent = status === 'completed' ? 'Recargar Ahora' : 'Cerrar';
        } else {
            document.getElementById('igModalActions').style.display = 'none';
        }
        
        if (window.lucide) lucide.createIcons();
    },

    mostrarError: function(msg) {
        clearInterval(this.polling);
        this.mostrarModal('failed', msg, 0, 0);
        var btn = document.getElementById('igSyncBtn');
        btn.disabled = false;
        btn.className = 'flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-[#d4af37] text-white hover:bg-[#c5a032] shadow-md hover:shadow-lg transition-all duration-300';
        document.getElementById('igSyncText').textContent = 'Sincronizar Actividad';
        document.getElementById('igSyncIcon').innerHTML = '<i data-lucide="refresh-cw" class="w-4 h-4"></i>';
        if (window.lucide) lucide.createIcons();
    },

    cerrar: function() {
        document.getElementById('igModal').className = 'ig-modal fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm';
        if (this.estado === 'completed') {
            window.location.reload();
        }
    }
};

lucide.createIcons();

var catCtx = document.getElementById('categoryChart');
if (catCtx) {
    new Chart(catCtx, {
        type: 'polarArea',
        data: {
            labels: <?= json_encode(array_keys($stats['categorias'])) ?>,
            datasets: [{
                data: <?= json_encode(array_values($stats['categorias'])) ?>,
                backgroundColor: [
                    'rgba(30, 58, 95, 0.7)',
                    'rgba(212, 175, 55, 0.7)',
                    'rgba(100, 116, 139, 0.7)'
                ],
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    ticks: { display: false },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: { size: 10, weight: 'bold' },
                        usePointStyle: true
                    }
                }
            }
        }
    });
}
</script>

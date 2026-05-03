<?php
// Obtener estadísticas del dashboard con sistema de caché
$db = getDB();
$cache = $GLOBALS['cache'] ?? null;
$campanaId = $campanaActiva['id'] ?? null;

// Función para obtener estadísticas cacheadas
function getDashboardStats($db, $cache, $campanaId) {
    if (!$campanaId) return null;
    
    return cache_remember("dashboard_stats_v2_{$campanaId}", function() use ($db, $campanaId) {
        $stats = [];
        
        try {
            // Donaciones
            $stmt = $db->prepare("SELECT COUNT(*) as total_donaciones, SUM(monto) as total_recaudado FROM donaciones WHERE campana_id = ? AND estado = 'confirmada'");
            $stmt->execute([$campanaId]);
            $stats['donaciones'] = $stmt->fetch();
        } catch (Exception $e) { $stats['donaciones'] = ['total_donaciones' => 0, 'total_recaudado' => 0]; }

        try {
            // Eventos
            $stmt = $db->prepare("SELECT COUNT(*) as total_eventos, SUM(asistentes_confirmados) as total_asistentes FROM eventos WHERE campana_id = ?");
            $stmt->execute([$campanaId]);
            $stats['eventos'] = $stmt->fetch();
        } catch (Exception $e) { $stats['eventos'] = ['total_eventos' => 0, 'total_asistentes' => 0]; }

        try {
            // Acciones/Votos
            $stmt = $db->prepare("SELECT COUNT(*) as total_acciones, SUM(personas_contactadas) as total_contactadas, SUM(compromisos_obtenidos) as total_votos_comprometidos FROM acciones_comunitarias WHERE campana_id = ?");
            $stmt->execute([$campanaId]);
            $stats['acciones'] = $stmt->fetch();
        } catch (Exception $e) { $stats['acciones'] = ['total_acciones' => 0, 'total_contactadas' => 0, 'total_votos_comprometidos' => 0]; }

        try {
            // Compromisos
            $stmt = $db->prepare("SELECT COUNT(*) as total_compromisos, SUM(CASE WHEN estado = 'cumplido' THEN 1 ELSE 0 END) as compromisos_cumplidos FROM compromisos WHERE campana_id = ?");
            $stmt->execute([$campanaId]);
            $stats['compromisos'] = $stmt->fetch();
        } catch (Exception $e) { $stats['compromisos'] = ['total_compromisos' => 0, 'compromisos_cumplidos' => 0]; }

        return $stats;
    }, 120); // Cache por 2 minutos
}

$stats = getDashboardStats($db, $cache, $campanaId);
$donaciones = $stats['donaciones'] ?? ['total_donaciones' => 0, 'total_recaudado' => 0];
$eventos = $stats['eventos'] ?? ['total_eventos' => 0, 'total_asistentes' => 0];
$acciones = $stats['acciones'] ?? ['total_acciones' => 0, 'total_contactadas' => 0, 'total_votos_comprometidos' => 0];
$compromisos = $stats['compromisos'] ?? ['total_compromisos' => 0, 'compromisos_cumplidos' => 0];

// Cargar listas recientes (sin caché para inmediatez visual)
$donaciones_recientes = [];
$eventos_proximos = [];
if ($campanaId) {
    $stmt = $db->prepare("SELECT d.*, u.nombre as recaudador FROM donaciones d LEFT JOIN usuarios u ON d.recaudador_id = u.id WHERE d.campana_id = ? ORDER BY d.fecha_donacion DESC LIMIT 5");
    $stmt->execute([$campanaId]);
    $donaciones_recientes = $stmt->fetchAll();

    $stmt = $db->prepare("SELECT e.*, u.nombre as responsable FROM eventos e LEFT JOIN usuarios u ON e.responsable_id = u.id WHERE e.campana_id = ? AND e.fecha_inicio >= NOW() ORDER BY e.fecha_inicio ASC LIMIT 5");
    $stmt->execute([$campanaId]);
    $eventos_proximos = $stmt->fetchAll();
}

$diasRestantes = 0;
if ($campanaActiva) {
    $fechaFin = new DateTime($campanaActiva['fecha_fin']);
    $hoy = new DateTime();
    $diasRestantes = $hoy->diff($fechaFin)->days;
}
?>

<style>
    .gold-gradient { background: linear-gradient(135deg, #1e3a5f 0%, #d4af37 100%); }
    .gold-text { color: #d4af37; }
    .gold-border { border-color: #d4af37; }
    .glass-card { 
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(212, 175, 55, 0.2);
        box-shadow: 0 8px 32px 0 rgba(30, 58, 95, 0.05);
    }
    .stat-value { font-family: 'Inter', sans-serif; letter-spacing: -0.02em; }
</style>

<div class="space-y-8 pb-12">
    <!-- Header Premium -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#1e3a5f] tracking-tight">Panel de Control</h1>
            <?php if ($campanaActiva): ?>
                <div class="flex items-center gap-2 mt-2">
                    <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                    <p class="text-gray-500 font-medium"><?= htmlspecialchars($campanaActiva['nombre']) ?></p>
                </div>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-100">
                <i data-lucide="calendar" class="w-5 h-5 text-[#1e3a5f]"></i>
                <span class="text-sm font-semibold text-gray-700"><?= date('d M, Y') ?></span>
            </div>
        </div>
    </div>

    <!-- Acceso Rápido Día D -->
    <div class="bg-gradient-to-r from-[#1e3a5f] to-[#2c5282] rounded-3xl p-1 shadow-xl">
        <div class="bg-white/10 backdrop-blur-md rounded-[1.4rem] p-6 flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4 text-white">
                <div class="p-3 bg-[#d4af37] rounded-2xl shadow-lg transform -rotate-3">
                    <i data-lucide="vote" class="w-8 h-8 text-[#1e3a5f]"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black uppercase tracking-tight">Módulo Día D</h2>
                    <p class="text-white/70 text-sm font-medium">Control de jornada electoral en tiempo real</p>
                </div>
            </div>
            
            <div class="flex flex-wrap justify-center gap-4 w-full lg:w-auto">
                <a href="<?= url('reporte-diaD') ?>" class="flex-1 lg:flex-none flex items-center justify-center gap-3 bg-[#d4af37] text-[#1e3a5f] px-6 py-3 rounded-2xl font-black text-sm uppercase hover:bg-white transition-all transform hover:scale-105 shadow-lg border-2 border-[#d4af37]">
                    <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                    Reportar Votos
                </a>
                <a href="<?= url('dashboard-diaD') ?>" class="flex-1 lg:flex-none flex items-center justify-center gap-3 bg-[#1e3a5f] text-white px-6 py-3 rounded-2xl font-black text-sm uppercase hover:bg-[#d4af37] hover:text-[#1e3a5f] transition-all transform hover:scale-105 shadow-xl border-2 border-[#1e3a5f]/50">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    Resultados Vivos
                </a>
                <a href="<?= url('mapa-puestos') ?>" class="flex-1 lg:flex-none flex items-center justify-center gap-3 bg-white/20 text-white px-6 py-3 rounded-2xl font-black text-sm uppercase hover:bg-white/40 transition-all backdrop-blur-sm border border-white/30">
                    <i data-lucide="map" class="w-5 h-5"></i>
                    Mapa Operativo
                </a>
            </div>
        </div>
    </div>

    <!-- Acceso Rápido Módulo JAC -->
    <?php
    $organizacionesCount = 0;
    if ($campanaId) {
        try {
            $stmtOrg = $db->prepare("SELECT COUNT(*) FROM jac_registros WHERE id_campana = ? AND estado = 'activa'");
            $stmtOrg->execute([$campanaId]);
            $organizacionesCount = intval($stmtOrg->fetchColumn());
        } catch (Exception $e) { $organizacionesCount = 0; }
    }
    ?>
    <div class="bg-gradient-to-r from-[#0f2035] to-[#1e3a5f] rounded-3xl p-1 shadow-xl">
        <div class="bg-white/10 backdrop-blur-md rounded-[1.4rem] p-5 flex flex-col lg:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 text-white">
                <div class="p-2.5 bg-[#d4af37]/20 border border-[#d4af37]/40 rounded-2xl">
                    <i data-lucide="building-2" class="w-6 h-6 text-[#d4af37]"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-black uppercase tracking-tight">Módulo Organizaciones</h2>
                        <?php if ($organizacionesCount > 0): ?>
                        <span class="bg-[#d4af37] text-[#1e3a5f] text-[10px] font-black px-2 py-0.5 rounded-full"><?= $organizacionesCount ?> activas</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-white/60 text-xs font-medium">Entidades y Organizaciones — Valle</p>
                </div>
            </div>
            <div class="flex flex-wrap justify-center gap-3 w-full lg:w-auto">
                <a href="<?= url('?page=organizaciones') ?>" class="flex-1 lg:flex-none flex items-center justify-center gap-2 bg-[#d4af37] text-[#1e3a5f] px-5 py-2.5 rounded-2xl font-black text-xs uppercase hover:bg-white transition-all transform hover:scale-105 shadow-lg">
                    <i data-lucide="list" class="w-4 h-4"></i> Gestionar
                </a>
                <?php if ($campanaId): ?>
                <a href="<?= url('?page=dashboard_organizaciones_publico&campana_id=' . $campanaId) ?>" target="_blank"
                   class="flex-1 lg:flex-none flex items-center justify-center gap-2 bg-white/15 text-white px-5 py-2.5 rounded-2xl font-black text-xs uppercase hover:bg-white/30 transition-all backdrop-blur-sm border border-white/20">
                    <i data-lucide="external-link" class="w-4 h-4"></i> Dashboard Público
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!$campanaActiva): ?>
        <div class="glass-card rounded-3xl p-12 text-center max-w-2xl mx-auto border-dashed border-2 gold-border">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="flag" class="w-10 h-10 text-gray-300"></i>
            </div>
            <h2 class="text-2xl font-bold text-[#002244] mb-3">Tu centro de mando está listo</h2>
            <p class="text-gray-600 mb-8 px-4">Aún no tienes una campaña activa. Empieza hoy mismo seleccionando una campaña existente o creando una nueva estrategia para tu equipo.</p>
            <a href="?page=campanas" class="bg-[#002244] text-white px-8 py-4 rounded-2xl font-bold hover:bg-[#003366] transition inline-flex items-center gap-3">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                Nueva Campaña
            </a>
        </div>
    <?php else: ?>

        <!-- Stats Grid Premium -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- RECAUDACIÓN -->
            <div class="glass-card p-6 rounded-3xl transition-transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center border border-green-100">
                        <i data-lucide="dollar-sign" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Finanzas</span>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-[#1e3a5f] stat-value">
                        <?= formatCurrency($donaciones['total_recaudado'] ?? 0) ?>
                    </p>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-green-600">+<?= $donaciones['total_donaciones'] ?></span>
                        <span class="text-xs text-gray-500 font-medium">aportes confirmados</span>
                    </div>
                </div>
            </div>

            <!-- ACTIVACIÓN -->
            <div class="glass-card p-6 rounded-3xl transition-transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center border border-blue-100">
                        <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Territorio</span>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-[#002244] stat-value">
                        <?= number_format($acciones['total_votos_comprometidos'] ?? 0) ?>
                    </p>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-blue-600"><?= number_format($acciones['total_contactadas'] ?? 0) ?></span>
                        <span class="text-xs text-gray-500 font-medium">líderes contactados</span>
                    </div>
                </div>
            </div>

            <!-- GESTIÓN -->
            <div class="glass-card p-6 rounded-3xl transition-transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center border border-purple-100">
                        <i data-lucide="calendar" class="w-6 h-6 text-purple-600"></i>
                    </div>
                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Agenda</span>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-[#002244] stat-value">
                        <?= number_format($eventos['total_asistentes'] ?? 0) ?>
                    </p>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-purple-600"><?= $eventos['total_eventos'] ?></span>
                        <span class="text-xs text-gray-500 font-medium">eventos realizados</span>
                    </div>
                </div>
            </div>

            <!-- TIEMPO -->
            <div class="glass-card p-6 rounded-3xl transition-transform hover:-translate-y-1 bg-[#1e3a5f]">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center">
                        <i data-lucide="flag" class="w-6 h-6 text-[#d4af37]"></i>
                    </div>
                    <span class="text-[10px] uppercase tracking-wider font-bold text-white/40">Día D</span>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-[#d4af37] stat-value">
                        <?= $diasRestantes ?>
                    </p>
                    <p class="text-xs text-white/70 font-medium">Días para la victoria</p>
                </div>
            </div>
        </div>

        <!-- Progress and Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Meta de Votos -->
            <div class="glass-card rounded-3xl p-8 overflow-hidden relative">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-extrabold text-[#1e3a5f]">Progreso de Meta Elector</h3>
                    <div class="px-3 py-1 bg-[#d4af37]/10 text-[#1e3a5f] rounded-full text-xs font-black">OBJETIVO: <?= number_format($campanaActiva['meta_votos']) ?></div>
                </div>
                
                <div class="relative h-64 flex items-center justify-center">
                    <canvas id="metaChartRadial"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <?php 
                        $porcentaje = ($campanaActiva['meta_votos'] > 0) ? min(100, ($campanaActiva['votos_actuales'] / $campanaActiva['meta_votos']) * 100) : 0;
                        ?>
                        <span class="text-4xl font-black text-[#002244]"><?= number_format($porcentaje, 1) ?>%</span>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Completado</span>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 rounded-2xl">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-1">Actual</p>
                        <p class="text-lg font-black text-[#002244]"><?= number_format($campanaActiva['votos_actuales']) ?></p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-1">Restante</p>
                        <p class="text-lg font-black text-gray-900"><?= number_format(max(0, $campanaActiva['meta_votos'] - $campanaActiva['votos_actuales'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- Estados de Compromisos -->
            <div class="glass-card rounded-3xl p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-extrabold text-[#1e3a5f]">Estado de Compromisos</h3>
                    <i data-lucide="trending-up" class="w-6 h-6 text-[#FFD700]"></i>
                </div>
                
                <div class="space-y-5">
                    <?php
                    $stmt = $db->prepare("SELECT estado, COUNT(*) as total FROM compromisos WHERE campana_id = ? GROUP BY estado");
                    $stmt->execute([$campanaId]);
                    $estados_compromisos = $stmt->fetchAll();
                    $total_c = array_sum(array_column($estados_compromisos, 'total')) ?: 1;

                    $palette = [
                        'pendiente' => ['lbl' => 'Pendientes', 'color' => '#64748b', 'bg' => '#f1f5f9'],
                        'en-gestion' => ['lbl' => 'En Proceso', 'color' => '#3b82f6', 'bg' => '#eff6ff'],
                        'cumplido' => ['lbl' => 'Finalizados', 'color' => '#10b981', 'bg' => '#ecfdf5'],
                        'incumplido' => ['lbl' => 'Críticos', 'color' => '#ef4444', 'bg' => '#fef2f2']
                    ];

                    foreach ($palette as $key => $style):
                        $count = 0;
                        foreach($estados_compromisos as $ec) if($ec['estado']==$key) $count = $ec['total'];
                        $pct = ($count / $total_c) * 100;
                    ?>
                    <div class="group">
                        <div class="flex justify-between items-end mb-2">
                            <div>
                                <span class="text-sm font-black text-[#1e3a5f] uppercase tracking-wide"><?= $style['lbl'] ?></span>
                                <p class="text-xs text-gray-400 font-bold"><?= $count ?> solicitudes</p>
                            </div>
                            <span class="text-sm font-black text-gray-900"><?= number_format($pct, 1) ?>%</span>
                        </div>
                        <div class="w-full h-3 rounded-full bg-gray-100 p-0.5 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000" style="width: <?= $pct ?>%; background-color: <?= $style['color'] ?>;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Activity Feed Section -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- DONACIONES RECIENTES -->
            <div class="glass-card rounded-3xl overflow-hidden">
                <div class="p-8 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-[#FFD700]/20 rounded-xl">
                            <i data-lucide="layers" class="w-5 h-5 text-[#002244]"></i>
                        </div>
                        <h3 class="text-lg font-black text-[#1e3a5f]">Últimos Aportes</h3>
                    </div>
                    <a href="?page=donaciones" class="text-xs font-black text-[#1e3a5f] hover:text-[#d4af37] uppercase tracking-widest transition">Ver Todo</a>
                </div>
                <div class="p-4 space-y-3">
                    <?php if (empty($donaciones_recientes)): ?>
                        <div class="py-12 text-center text-gray-400">Sin movimientos recientes</div>
                    <?php else: ?>
                        <?php foreach ($donaciones_recientes as $d): ?>
                        <div class="flex items-center justify-between p-4 rounded-2xl hover:bg-gray-50 transition border border-transparent hover:border-gray-100 group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-[#1e3a5f]/10 flex items-center justify-center font-bold text-[#1e3a5f]">
                                    <?= substr($d['nombre_donante'],0,1) ?>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-[#1e3a5f]"><?= htmlspecialchars($d['nombre_donante']) ?></p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase"><?= formatDate($d['fecha_donacion'], 'd M, H:i') ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-green-600"><?= formatCurrency($d['monto']) ?></p>
                                <span class="text-[9px] font-black uppercase text-gray-400"><?= $d['metodo_pago'] ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- EVENTOS PRÓXIMOS -->
            <div class="glass-card rounded-3xl overflow-hidden">
                <div class="p-8 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 rounded-xl">
                            <i data-lucide="clock" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-black text-[#1e3a5f]">Agenda Estratégica</h3>
                    </div>
                    <a href="?page=eventos" class="text-xs font-black text-[#1e3a5f] hover:text-[#d4af37] uppercase tracking-widest transition">Ver Mapa</a>
                </div>
                <div class="p-4 space-y-3">
                    <?php if (empty($eventos_proximos)): ?>
                        <div class="py-12 text-center text-gray-400">Sin eventos en radar</div>
                    <?php else: ?>
                        <?php foreach ($eventos_proximos as $e): ?>
                        <div class="flex items-center gap-4 p-4 rounded-2xl hover:bg-gray-50 transition border border-transparent hover:border-gray-100">
                            <div class="flex-shrink-0 w-14 h-14 bg-[#1e3a5f] rounded-2xl flex flex-col items-center justify-center text-white">
                                <span class="text-xs font-black uppercase"><?= formatDate($e['fecha_inicio'], 'M') ?></span>
                                <span class="text-xl font-black leading-none"><?= formatDate($e['fecha_inicio'], 'd') ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-black text-[#1e3a5f] truncate"><?= htmlspecialchars($e['nombre']) ?></p>
                                <div class="flex items-center gap-3 mt-1">
                                    <div class="flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-gray-400"></i>
                                        <span class="text-[10px] text-gray-500 font-bold"><?= htmlspecialchars($e['ubicacion'] ?? 'Consultar') ?></span>
                                    </div>
                                    <span class="text-[10px] text-gray-300">|</span>
                                    <span class="text-[10px] text-blue-600 font-black uppercase"><?= $e['tipo'] ?></span>
                                </div>
                            </div>
                            <i data-lucide="chevron-right" class="w-5 h-5 text-gray-200"></i>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<script>
    // Iniciar Lucide
    lucide.createIcons();

    // Gráfico Radial de Meta con Estilo Premium
    <?php if ($campanaActiva): ?>
        const ctxRadial = document.getElementById('metaChartRadial');
        if (ctxRadial) {
            new Chart(ctxRadial, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [
                            <?= $campanaActiva['votos_actuales'] ?>,
                            <?= max(1, $campanaActiva['meta_votos'] - $campanaActiva['votos_actuales']) ?>
                        ],
                        backgroundColor: ['#1e3a5f', '#f1f5f9'],
                        borderWidth: 0,
                        hoverOffset: 4,
                        borderRadius: 20,
                        spacing: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '85%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }
    <?php endif; ?>
</script>
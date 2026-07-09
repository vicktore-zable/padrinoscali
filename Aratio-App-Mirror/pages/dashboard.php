<?php
$campanaId = $campanaActiva['id'] ?? null;
$db = getDB();
$diasRestantes = 0;
if ($campanaActiva) {
    $fechaFin = new DateTime($campanaActiva['fecha_fin']);
    $diasRestantes = (new DateTime())->diff($fechaFin)->days;
}

$metaVotos = $campanaActiva['meta_votos'] ?? 0;
$votosActuales = $campanaActiva['votos_actuales'] ?? 0;
$porcentajeMeta = ($metaVotos > 0) ? min(100, round(($votosActuales / $metaVotos) * 100, 1)) : 0;
$votosRestantes = max(0, $metaVotos - $votosActuales);

$compromisos = [];
$estadosCompromisos = [];
$totalCompromisos = 0;
if ($campanaId) {
    try {
        $stmt = $db->prepare("SELECT estado, COUNT(*) as total FROM compromisos WHERE campana_id = ? GROUP BY estado");
        $stmt->execute([$campanaId]);
        $estadosCompromisos = $stmt->fetchAll();
        $totalCompromisos = array_sum(array_column($estadosCompromisos, 'total')) ?: 1;
    } catch (Exception $e) {}
}
$palette = [
    'pendiente' => ['lbl' => 'Pendientes', 'color' => '#64748b', 'bg' => '#f1f5f9'],
    'en-gestion' => ['lbl' => 'En Proceso', 'color' => '#3b82f6', 'bg' => '#eff6ff'],
    'cumplido' => ['lbl' => 'Finalizados', 'color' => '#10b981', 'bg' => '#ecfdf5'],
    'incumplido' => ['lbl' => 'Críticos', 'color' => '#ef4444', 'bg' => '#fef2f2']
];
?>

<div class="space-y-8 pb-12" x-data="dashboardData()" x-init="init()">
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

    <?php if (!$campanaActiva): ?>
        <div class="bg-white rounded-3xl p-12 text-center max-w-2xl mx-auto border-2 border-dashed border-gray-300 shadow-xl">
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

    <!-- KPIs principales -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
                <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Líderes</span>
            </div>
            <p class="text-2xl font-black text-[#1e3a5f]" x-text="kpi.lideres">0</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="award" class="w-4 h-4 text-amber-600"></i>
                <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Padrinos</span>
            </div>
            <p class="text-2xl font-black text-[#1e3a5f]" x-text="kpi.padrinos">0</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="user-plus" class="w-4 h-4 text-green-600"></i>
                <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Seguidores</span>
            </div>
            <p class="text-2xl font-black text-[#1e3a5f]" x-text="kpi.seguidores">0</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="calendar" class="w-4 h-4 text-purple-600"></i>
                <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Eventos</span>
            </div>
            <p class="text-2xl font-black text-[#1e3a5f]" x-text="kpi.eventos">0</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
                <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Asistencia</span>
            </div>
            <p class="text-2xl font-black text-[#1e3a5f]" x-text="kpi.asistentes">0</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="camera" class="w-4 h-4 text-pink-600"></i>
                <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Instagram</span>
            </div>
            <p class="text-2xl font-black text-[#1e3a5f]" x-text="kpi.instagram">0</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="globe" class="w-4 h-4 text-blue-700"></i>
                <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Facebook</span>
            </div>
            <p class="text-2xl font-black text-[#1e3a5f]" x-text="kpi.facebook">0</p>
        </div>
    </div>

    <!-- Geografía y Meta -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Cobertura Geográfica -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-5">
                <i data-lucide="map-pin" class="w-5 h-5 text-[#1e3a5f]"></i>
                <h3 class="text-sm font-black text-[#1e3a5f] uppercase tracking-wide">Cobertura Geográfica</h3>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-blue-50 rounded-xl text-center">
                    <p class="text-2xl font-black text-blue-700" x-text="kpi.comunas">0</p>
                    <p class="text-[10px] font-bold text-blue-500 uppercase mt-1">Comunas</p>
                </div>
                <div class="p-4 bg-green-50 rounded-xl text-center">
                    <p class="text-2xl font-black text-green-700" x-text="kpi.barrios">0</p>
                    <p class="text-[10px] font-bold text-green-500 uppercase mt-1">Barrios</p>
                </div>
                <div class="p-4 bg-amber-50 rounded-xl text-center">
                    <p class="text-2xl font-black text-amber-700" x-text="kpi.urbano">0</p>
                    <p class="text-[10px] font-bold text-amber-500 uppercase mt-1">Urbano</p>
                </div>
                <div class="p-4 bg-emerald-50 rounded-xl text-center">
                    <p class="text-2xl font-black text-emerald-700" x-text="kpi.rural">0</p>
                    <p class="text-[10px] font-bold text-emerald-500 uppercase mt-1">Rural</p>
                </div>
            </div>
            <div class="mt-4 p-3 bg-gray-50 rounded-xl flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500">Municipios activos</span>
                <span class="text-lg font-black text-[#1e3a5f]" x-text="kpi.municipios">1</span>
            </div>
        </div>

        <!-- Meta de Votos -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <i data-lucide="target" class="w-5 h-5 text-[#d4af37]"></i>
                    <h3 class="text-sm font-black text-[#1e3a5f] uppercase tracking-wide">Meta de Votos</h3>
                </div>
                <span class="text-[10px] font-black text-gray-400">META: <?= number_format($metaVotos) ?></span>
            </div>
            <div class="relative h-56 flex items-center justify-center">
                <canvas id="metaChartRadial"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-4xl font-black text-[#002244]"><?= $porcentajeMeta ?>%</span>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Completado</span>
                </div>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="p-3 bg-green-50 rounded-xl text-center">
                    <p class="text-[10px] font-bold text-green-500 uppercase mb-1">Reportados</p>
                    <p class="text-lg font-black text-green-700"><?= number_format($votosActuales) ?></p>
                </div>
                <div class="p-3 bg-amber-50 rounded-xl text-center">
                    <p class="text-[10px] font-bold text-amber-500 uppercase mb-1">Restantes</p>
                    <p class="text-lg font-black text-amber-700"><?= number_format($votoRestante) ?></p>
                </div>
            </div>
        </div>

        <!-- Compromisos -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-5">
                <i data-lucide="handshake" class="w-5 h-5 text-[#1e3a5f]"></i>
                <h3 class="text-sm font-black text-[#1e3a5f] uppercase tracking-wide">Estado de Compromisos</h3>
            </div>
            <div class="space-y-4">
                <?php foreach ($palette as $key => $style):
                    $count = 0;
                    foreach ($estadosCompromisos as $ec) if ($ec['estado'] === $key) $count = $ec['total'];
                    $pct = ($count / $totalCompromisos) * 100;
                ?>
                <div>
                    <div class="flex justify-between items-end mb-1.5">
                        <span class="text-xs font-black text-[#1e3a5f] uppercase"><?= $style['lbl'] ?></span>
                        <span class="text-xs font-black text-gray-700"><?= $count ?> (<?= number_format($pct, 0) ?>%)</span>
                    </div>
                    <div class="w-full h-2.5 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-1000" style="width: <?= $pct ?>%; background-color: <?= $style['color'] ?>;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 p-3 bg-gray-50 rounded-xl flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500">Total compromisos</span>
                <span class="text-lg font-black text-[#1e3a5f]"><?= number_format($totalCompromisos) ?></span>
            </div>
        </div>
    </div>

    <!-- Cards de resumen -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-5 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="dollar-sign" class="w-4 h-4 text-blue-200"></i>
                <span class="text-[10px] uppercase font-bold text-blue-200">Donaciones</span>
            </div>
            <p class="text-xl font-black" x-text="'$' + numberFormat(kpi.recaudado)">$0</p>
            <p class="text-xs text-blue-200 mt-1" x-text="kpi.donaciones + ' aportes'">0 aportes</p>
        </div>
        <div class="bg-gradient-to-br from-purple-600 to-purple-800 rounded-2xl p-5 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="phone-call" class="w-4 h-4 text-purple-200"></i>
                <span class="text-[10px] uppercase font-bold text-purple-200">Phone Banking</span>
            </div>
            <p class="text-xl font-black" x-text="kpi.llamadas || '0'">0</p>
            <p class="text-xs text-purple-200 mt-1" x-text="kpi.llamadas_pendientes + ' pendientes'">0 pendientes</p>
        </div>
        <div class="bg-gradient-to-br from-pink-600 to-pink-800 rounded-2xl p-5 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="message-circle" class="w-4 h-4 text-pink-200"></i>
                <span class="text-[10px] uppercase font-bold text-pink-200">WhatsApp</span>
            </div>
            <p class="text-xl font-black" x-text="kpi.whatsapp_activos || '0'">0</p>
            <p class="text-xs text-pink-200 mt-1" x-text="kpi.whatsapp_no_leidos + ' sin leer'">0 sin leer</p>
        </div>
        <div class="bg-gradient-to-br from-amber-600 to-amber-800 rounded-2xl p-5 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="mail" class="w-4 h-4 text-amber-200"></i>
                <span class="text-[10px] uppercase font-bold text-amber-200">Campañas Email</span>
            </div>
            <p class="text-xl font-black" x-text="kpi.emails_enviados || '0'">0</p>
            <p class="text-xs text-amber-200 mt-1" x-text="kpi.emails_pendientes + ' en cola'">0 en cola</p>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
lucide.createIcons();

<?php if ($campanaActiva): ?>
const ctxRadial = document.getElementById('metaChartRadial');
if (ctxRadial) {
    new Chart(ctxRadial, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [<?= $votosActuales ?>, <?= max(1, $votoRestante) ?>],
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
            cutout: '82%',
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            animation: { animateScale: true, animateRotate: true, duration: 2000, easing: 'easeOutQuart' }
        }
    });
}

function numberFormat(n) {
    return parseInt(n || 0).toLocaleString('es-CO');
}

function dashboardData() {
    return {
        kpi: {},
        init() {
            fetch('api/dashboard.php?action=kpi&campana_id=<?= $campanaId ?>')
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.data) return;
                    const d = res.data;
                    this.kpi = {
                        colaboradores: d.colaboradores || 0,
                        lideres: d.lideres || 0,
                        padrinos: d.padrinos || 0,
                        seguidores: d.seguidores || 0,
                        eventos: d.eventos || 0,
                        asistentes: d.asistentes || 0,
                        donaciones: d.donaciones || 0,
                        recaudado: d.recaudado || 0,
                        municipios: d.total_municipios || 0,
                        comunas: d.comunas || 0,
                        barrios: d.barrios || 0,
                        urbano: d.urbano || 0,
                        rural: d.rural || 0,
                        instagram: d.instagram || 0,
                        facebook: d.facebook || 0,
                        llamadas: d.llamadas || 0,
                        llamadas_pendientes: d.llamadas_pendientes || 0,
                        whatsapp_activos: d.whatsapp_activos || 0,
                        whatsapp_no_leidos: d.whatsapp_no_leidos || 0,
                        emails_enviados: d.emails_enviados || 0,
                        emails_pendientes: d.emails_pendientes || 0
                    };
                });
        }
    };
}
<?php endif; ?>
</script>
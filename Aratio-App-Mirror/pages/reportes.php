<?php
/**
 * MÓDULO: Reportes
 * Sistema completo de reportes con gráficos interactivos
 */

if (!$campanaActiva) {
    echo '<div class="card text-center py-12"><i data-lucide="flag" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i><h2 class="text-xl font-bold text-gray-900 mb-2">Selecciona una campaña</h2></div>';
    return;
}

// NUEVO: Consultar datos de Líderes y cantidad de colaboradores a cargo (solo activos y de la campaña actual)
$db = getDB();
$campanaId = $campanaActiva['id'];
$queryLideres = "
    SELECT 
        l.documento as lider_documento,
        l.nombres as lider_nombres,
        l.apellidos as lider_apellidos,
        COUNT(c.id) as total_colaboradores
    FROM 
        colaboradores l
    LEFT JOIN 
        colaboradores c ON l.documento = c.lider_directo AND c.campana_id = ? AND c.estado NOT IN ('inactivo', 'Inactivo')
    WHERE 
        l.perfil IN ('Lider Comunitario', 'Líder / Coordinador') AND l.campana_id = ? AND l.estado NOT IN ('inactivo', 'Inactivo')
    GROUP BY 
        l.documento, l.nombres, l.apellidos
    ORDER BY 
        total_colaboradores DESC
";
$stmt = $db->prepare($queryLideres);
$stmt->execute([$campanaId, $campanaId]);
$lideresReporte = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats for geographic report
$totalColaboradores = $db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id = $campanaId AND estado NOT IN ('inactivo', 'Inactivo')")->fetchColumn();
$totalLideres = $db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id = $campanaId AND perfil LIKE '%Lider%' AND estado NOT IN ('inactivo', 'Inactivo')")->fetchColumn();
$totalMujeres = $db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id = $campanaId AND genero = 'F' AND estado NOT IN ('inactivo', 'Inactivo')")->fetchColumn();
$totalHombres = $db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id = $campanaId AND genero = 'M' AND estado NOT IN ('inactivo', 'Inactivo')")->fetchColumn();
$totalEventos = $db->query("SELECT COUNT(*) FROM eventos WHERE campana_id = $campanaId AND estado != 'cancelado'")->fetchColumn();
$totalOrganizaciones = $db->query("SELECT COUNT(*) FROM jac_registros")->fetchColumn();

// Stats por nivel
$nivelStats = $db->query("SELECT nivel_participacion as nivel, COUNT(*) as total FROM colaboradores WHERE campana_id = $campanaId AND nivel_participacion IS NOT NULL AND nivel_participacion != '' GROUP BY nivel_participacion ORDER BY total DESC")->fetchAll();

// Stats por municipio
$muniStats = $db->query("SELECT municipio, COUNT(*) as total FROM colaboradores WHERE campana_id = $campanaId AND municipio IS NOT NULL AND municipio != '' GROUP BY municipio ORDER BY total DESC")->fetchAll();

// Stats por departamento
$deptoStats = $db->query("SELECT departamento, COUNT(*) as total, SUM(CASE WHEN perfil LIKE '%Lider%' THEN 1 ELSE 0 END) as lideres FROM colaboradores WHERE campana_id = $campanaId AND departamento IS NOT NULL AND departamento != '' GROUP BY departamento ORDER BY total DESC")->fetchAll();

// Stats por perfil
$perfilStats = $db->query("SELECT perfil, COUNT(*) as total FROM colaboradores WHERE campana_id = $campanaId AND perfil IS NOT NULL AND perfil != '' GROUP BY perfil ORDER BY total DESC")->fetchAll();

// Geographic drill-down filters
$geoDepto = $_GET['depto'] ?? '';
$geoMpio = $_GET['mpio'] ?? '';
$geoBarrio = $_GET['barrio'] ?? '';

// Build WHERE dynamically
$geoW = ["campana_id = $campanaId", "estado NOT IN ('inactivo', 'Inactivo')"];
if ($geoDepto) $geoW[] = "departamento = '$geoDepto'";
if ($geoMpio) $geoW[] = "municipio = '$geoMpio'";
if ($geoBarrio) $geoW[] = "barrio = '$geoBarrio'";
$geoWhereSQL = implode(" AND ", $geoW);

// Stats
$geoTotal = $db->query("SELECT COUNT(*) FROM colaboradores WHERE $geoWhereSQL")->fetchColumn();
$geoLideres = $db->query("SELECT COUNT(*) FROM colaboradores WHERE $geoWhereSQL AND perfil LIKE '%Lider%'")->fetchColumn();
$geoMujeres = $db->query("SELECT COUNT(*) FROM colaboradores WHERE $geoWhereSQL AND genero = 'F'")->fetchColumn();
$geoHombres = $db->query("SELECT COUNT(*) FROM colaboradores WHERE $geoWhereSQL AND genero = 'M'")->fetchColumn();

// Drill down
if (!$geoDepto) {
    $geoDeptos = $db->query("SELECT departamento, COUNT(*) as total FROM colaboradores WHERE campana_id = $campanaId AND estado NOT IN ('inactivo', 'Inactivo') AND departamento IS NOT NULL GROUP BY departamento ORDER BY total DESC")->fetchAll();
} elseif (!$geoMpio) {
    $geoMpiOS = $db->query("SELECT municipio, COUNT(*) as total FROM colaboradores WHERE campana_id = $campanaId AND departamento = '$geoDepto' AND municipio IS NOT NULL GROUP BY municipio ORDER BY total DESC")->fetchAll();
} elseif (!$geoBarrio) {
    $geoBarrios = $db->query("SELECT barrio, COUNT(*) as total FROM colaboradores WHERE campana_id = $campanaId AND municipio = '$geoMpio' AND barrio IS NOT NULL AND barrio != '' GROUP BY barrio ORDER BY total DESC")->fetchAll();
} else {
    $geoColaboradores = $db->query("SELECT documento, nombres, apellidos, perfil, nivel_participacion FROM colaboradores WHERE $geoWhereSQL ORDER BY nombres")->fetchAll();
}
?>

<div class="space-y-6" x-data="reportesData()">
    <?php $defaultTab = $_GET['reporte'] ?? 'general'; ?>
    <?php 
    // Build current URL params for drill-down links
    $geoParams = $_GET;
    unset($geoParams['page']);
    $geoBaseUrl = '?page=reportes&reporte=geografico' . ($geoParams ? '&' . http_build_query(array_filter($geoParams)) : '');
    ?>
    <!-- GLOBAL KPI DASHBOARD -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl p-4 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-1"><i data-lucide="users" class="w-4 h-4"></i><span class="text-blue-100 text-xs">Total</span></div>
            <p class="text-2xl font-extrabold"><?= number_format($totalColaboradores) ?></p>
            <p class="text-blue-100 text-[10px]">Colaboradores</p>
        </div>
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-4 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-1"><i data-lucide="crown" class="w-4 h-4"></i><span class="text-amber-100 text-xs">Total</span></div>
            <p class="text-2xl font-extrabold"><?= number_format($totalLideres) ?></p>
            <p class="text-amber-100 text-[10px]">Líderes</p>
        </div>
        <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl p-4 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-1"><i data-lucide="venus" class="w-4 h-4"></i><span class="text-pink-100 text-xs">Total</span></div>
            <p class="text-2xl font-extrabold"><?= number_format($totalMujeres) ?></p>
            <p class="text-pink-100 text-[10px]">Mujeres</p>
        </div>
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-4 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-1"><i data-lucide="mars" class="w-4 h-4"></i><span class="text-indigo-100 text-xs">Total</span></div>
            <p class="text-2xl font-extrabold"><?= number_format($totalHombres) ?></p>
            <p class="text-indigo-100 text-[10px]">Hombres</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-4 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-1"><i data-lucide="calendar" class="w-4 h-4"></i><span class="text-green-100 text-xs">Total</span></div>
            <p class="text-2xl font-extrabold"><?= number_format($totalEventos) ?></p>
            <p class="text-green-100 text-[10px]">Eventos</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-4 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-1"><i data-lucide="landmark" class="w-4 h-4"></i><span class="text-purple-100 text-xs">Total</span></div>
            <p class="text-2xl font-extrabold"><?= number_format($totalOrganizaciones) ?></p>
            <p class="text-purple-100 text-[10px]">Organizaciones</p>
        </div>
    </div>
    
    <!-- Secondary Stats Row -->
    <div class="flex flex-wrap gap-3">
        <div class="bg-white rounded-lg border p-3 flex items-center gap-2">
            <i data-lucide="map-pin" class="w-4 h-4 text-gray-500"></i>
            <span class="text-sm text-gray-600"><?= count($deptoStats) ?> deptos</span>
        </div>
        <div class="bg-white rounded-lg border p-3 flex items-center gap-2">
            <i data-lucide="landmark" class="w-4 h-4 text-gray-500"></i>
            <span class="text-sm text-gray-600"><?= count($muniStats) ?> municipios</span>
        </div>
        <div class="bg-white rounded-lg border p-3 flex items-center gap-2">
            <i data-lucide="bar-chart-2" class="w-4 h-4 text-gray-500"></i>
            <span class="text-sm text-gray-600"><?= count($perfilStats) ?> perfiles</span>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <div><h1 class="text-3xl font-bold text-gray-900">Reportes y Estadísticas</h1><p class="text-gray-600 mt-2"><?= htmlspecialchars($campanaActiva['nombre']) ?></p></div>
        <button @click="exportarTodo()" class="btn-primary"><i data-lucide="download" class="w-5 h-5 inline mr-2"></i>Exportar Todo</button>
    </div>

    <!-- Modern Tab Navigation -->
    <div class="flex flex-wrap gap-2 p-1 bg-gray-100 rounded-xl">
        <button @click="reporteActual = 'general'" :class="reporteActual === 'general' ? 'bg-white shadow-md text-primary' : 'text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>General
        </button>
        <button @click="reporteActual = 'lideres'" :class="reporteActual === 'lideres' ? 'bg-white shadow-md text-primary' : 'text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="users-2" class="w-4 h-4"></i>Líderes
        </button>
        <button @click="reporteActual = 'geografico'" :class="reporteActual === 'geografico' ? 'bg-white shadow-md text-primary' : 'text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="map-pin" class="w-4 h-4"></i>Geográfico
        </button>
        <button @click="reporteActual = 'territorial'" :class="reporteActual === 'territorial' ? 'bg-white shadow-md text-primary' : 'text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="map" class="w-4 h-4"></i>Territorial
        </button>
        <button @click="reporteActual = 'donaciones'" :class="reporteActual === 'donaciones' ? 'bg-white shadow-md text-primary' : 'text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="dollar-sign" class="w-4 h-4"></i>Donaciones
        </button>
        <button @click="reporteActual = 'eventos'" :class="reporteActual === 'eventos' ? 'bg-white shadow-md text-primary' : 'text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="calendar" class="w-4 h-4"></i>Eventos
        </button>
        <button @click="reporteActual = 'acciones'" :class="reporteActual === 'acciones' ? 'bg-white shadow-md text-primary' : 'text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="map-pin" class="w-4 h-4"></i>Acciones
        </button>
        <button @click="reporteActual = 'compromisos'" :class="reporteActual === 'compromisos' ? 'bg-white shadow-md text-primary' : 'text-gray-600 hover:text-gray-900'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="handshake" class="w-4 h-4"></i>Compromisos
        </button>
    </div>

    <!-- Report Content Card -->
    <div class="bg-white rounded-2xl shadow-lg border overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary/10 rounded-lg">
                    <i data-lucide="bar-chart-2" class="w-5 h-5 text-primary"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900" x-text="getTituloReporte()"></h3>
            </div>
            <button @click="exportarReporte()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition">
                <i data-lucide="download" class="w-4 h-4"></i>Exportar
            </button>
        </div>
        
        <!-- Content -->
        <div class="p-6">

        <div x-show="reporteActual === 'donaciones'">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div><h4 class="font-medium mb-4">Donaciones por Método de Pago</h4><canvas id="chartDonacionesMetodo" height="250"></canvas></div>
                <div><h4 class="font-medium mb-4">Evolución Temporal de Donaciones</h4><canvas id="chartDonacionesTiempo" height="250"></canvas></div>
            </div>
        </div>

        <div x-show="reporteActual === 'eventos'">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div><h4 class="font-medium mb-4">Eventos por Tipo</h4><canvas id="chartEventosTipo" height="250"></canvas></div>
                <div><h4 class="font-medium mb-4">Asistencia por Evento</h4><canvas id="chartEventosAsistencia" height="250"></canvas></div>
            </div>
        </div>

        <div x-show="reporteActual === 'acciones'">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div><h4 class="font-medium mb-4">Acciones por Tipo</h4><canvas id="chartAccionesTipo" height="250"></canvas></div>
                <div><h4 class="font-medium mb-4">Personas Contactadas vs Compromisos</h4><canvas id="chartAccionesEfectividad" height="250"></canvas></div>
            </div>
        </div>

        <div x-show="reporteActual === 'compromisos'">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div><h4 class="font-medium mb-4">Compromisos por Estado</h4><canvas id="chartCompromisosEstado" height="250"></canvas></div>
                <div><h4 class="font-medium mb-4">Compromisos por Tipo</h4><canvas id="chartCompromisosTipo" height="250"></canvas></div>
            </div>
        </div>

        <div x-show="reporteActual === 'territorial'">
            <div class="space-y-6">
                <!-- KPI Cards Premium -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl p-4 text-white shadow-lg">
                        <div class="flex items-center gap-2 mb-1"><i data-lucide="users" class="w-4 h-4"></i><span class="text-blue-100 text-xs">Total</span></div>
                        <p class="text-3xl font-bold"><?= number_format($totalColaboradores) ?></p>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl p-4 text-white shadow-lg">
                        <div class="flex items-center gap-2 mb-1"><i data-lucide="crown" class="w-4 h-4"></i><span class="text-green-100 text-xs">Líderes</span></div>
                        <p class="text-3xl font-bold"><?= number_format($totalLideres) ?></p>
                    </div>
                    <div class="bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl p-4 text-white shadow-lg">
                        <div class="flex items-center gap-2 mb-1"><i data-lucide="map-pin" class="w-4 h-4"></i><span class="text-orange-100 text-xs">Deptos</span></div>
                        <p class="text-3xl font-bold"><?= count($deptoStats) ?></p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl p-4 text-white shadow-lg">
                        <div class="flex items-center gap-2 mb-1"><i data-lucide="landmark" class="w-4 h-4"></i><span class="text-purple-100 text-xs">Municipios</span></div>
                        <p class="text-3xl font-bold"><?= count($muniStats) ?></p>
                    </div>
                </div>

                <!-- Por Departamento (Drill down) -->
                <div class="bg-gray-50 rounded-xl p-5 border">
                    <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-5 h-5 text-blue-600"></i>Distribución por Departamento
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Departamento</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Colaboradores</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">%</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Líderes</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Densidad</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php foreach($deptoStats as $d): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium"><?= htmlspecialchars($d['departamento']) ?></td>
                                    <td class="px-4 py-2 text-right"><?= $d['total'] ?></td>
                                    <td class="px-4 py-2 text-right text-gray-500"><?= round($d['total'] / $totalColaboradores * 100, 1) ?>%</td>
                                    <td class="px-4 py-2 text-right"><?= $d['lideres'] ?? 0 ?></td>
                                    <td class="px-4 py-2 text-right">
                                        <div class="w-20 bg-gray-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-blue-500 h-2" style="width: <?= $d['total'] / $totalColaboradores * 100 ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($deptoStats)): ?>
                                <tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Sin datos</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Por Municipio con barra de progreso -->
                <div class="bg-gray-50 rounded-xl p-5 border">
                    <h4 class="font-medium mb-4">Top 10 Municipios</h4>
                    <?php $topMun = array_slice($muniStats, 0, 10); ?>
                    <?php foreach($topMun as $m): ?>
                    <div class="mb-3">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium"><?= htmlspecialchars($m['municipio']) ?></span>
                            <span class="text-gray-500"><?= $m['total'] ?> (<?= round($m['total'] / $totalColaboradores * 100, 1) ?>)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full" style="width: <?= $m['total'] / $totalColaboradores * 100 ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Por Perfil -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 rounded-xl p-5 border">
                        <h4 class="font-medium mb-4">Por Perfil</h4>
                        <canvas id="chartPerfil" height="200"></canvas>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-5 border">
                        <h4 class="font-medium mb-4">Por Nivel de Participación</h4>
                        <canvas id="chartNivel" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="reporteActual === 'general'">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div><h4 class="font-medium mb-4">Resumen General</h4><canvas id="chartGeneral" height="250"></canvas></div>
                <div><h4 class="font-medium mb-4">Tendencias</h4><canvas id="chartTendencias" height="250"></canvas></div>
            </div>
        </div>

        <div x-show="reporteActual === 'lideres'" x-cloak>
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <h4 class="font-medium mb-4 text-gray-800">Crecimiento Logrado por Líderes de la Campaña</h4>
                    <p class="text-sm text-gray-500 mb-4">Solo toma en cuenta los colaboradores activos bajo el mando directo de los líderes registrados.</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Líder</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Colaboradores Patrocinados</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($lideresReporte)): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">No se encontraron líderes o colaboradores en esta campaña.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($lideresReporte as $lr): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-l-4 <?= $lr['total_colaboradores'] > 0 ? 'border-primary' : 'border-gray-200' ?>">
                                            <?= htmlspecialchars($lr['lider_nombres'] . ' ' . $lr['lider_apellidos']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($lr['lider_documento']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold <?= $lr['total_colaboradores'] > 0 ? 'bg-primary/20 text-primary' : 'bg-gray-100 text-gray-600' ?>">
                                                <i data-lucide="users" class="w-4 h-4 mr-1"></i>
                                                <?= $lr['total_colaboradores'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- REPORTE GEOGRÁFICO -->
        <div x-show="reporteActual === 'geografico'">
            <div class="space-y-6">
                <!-- Breadcrumb para navegación jerárquica - preserve all params -->
                <?php 
                $baseGeoUrl = '?page=reportes&reporte=geografico';
                $currParams = array_filter($_GET, fn($v, $k) => $k !== 'depto' && $k !== 'mpio' && $k !== 'barrio', ARRAY_FILTER_USE_BOTH);
                if (!empty($currParams)) $baseGeoUrl .= '&' . http_build_query($currParams);
                ?>
                <div class="flex items-center gap-2 text-sm flex-wrap">
                    <a href="<?= $baseGeoUrl ?>" class="px-3 py-1 rounded-full <?= !$geoDepto ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>">Todos</a>
                    <?php if($geoDepto): ?>
                    <span class="text-gray-400">›</span>
                    <a href="<?= $baseGeoUrl ?>&depto=<?= urlencode($geoDepto) ?>" class="px-3 py-1 rounded-full <?= !$geoMpio ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>"><?= htmlspecialchars($geoDepto) ?></a>
                    <?php endif; ?>
                    <?php if($geoMpio): ?>
                    <span class="text-gray-400">›</span>
                    <a href="<?= $baseGeoUrl ?>&depto=<?= urlencode($geoDepto) ?>&mpio=<?= urlencode($geoMpio) ?>" class="px-3 py-1 rounded-full <?= !$geoBarrio ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>"><?= htmlspecialchars($geoMpio) ?></a>
                    <?php endif; ?>
                    <?php if($geoBarrio): ?>
                    <span class="text-gray-400">›</span>
                    <span class="px-3 py-1 rounded-full bg-blue-600 text-white"><?= htmlspecialchars($geoBarrio) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Stats Premium Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Colaboradores -->
                    <div class="relative overflow-hidden bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition transform hover:scale-[1.02]">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="users" class="w-5 h-5"></i>
                                <span class="text-blue-100 text-sm font-medium">Total</span>
                            </div>
                            <p class="text-4xl font-extrabold"><?= number_format($geoTotal) ?></p>
                            <p class="text-blue-100 text-xs mt-1">Colaboradores activos</p>
                        </div>
                    </div>
                    
                    <!-- Líderes -->
                    <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition transform hover:scale-[1.02]">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="crown" class="w-5 h-5"></i>
                                <span class="text-amber-100 text-sm font-medium">Líderes</span>
                            </div>
                            <p class="text-4xl font-extrabold"><?= number_format($geoLideres) ?></p>
                            <p class="text-amber-100 text-xs mt-1">Coordinadores activos</p>
                        </div>
                    </div>
                    
                    <!-- Mujeres -->
                    <div class="relative overflow-hidden bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition transform hover:scale-[1.02]">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="venus" class="w-5 h-5"></i>
                                <span class="text-pink-100 text-sm font-medium">Mujeres</span>
                            </div>
                            <p class="text-4xl font-extrabold"><?= number_format($geoMujeres) ?></p>
                            <p class="text-pink-100 text-xs mt-1"><?= $geoTotal > 0 ? round($geoMujeres/$geoTotal*100,1).'%' : '0%' ?> del total</p>
                        </div>
                    </div>
                    
                    <!-- Hombres -->
                    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition transform hover:scale-[1.02]">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="mars" class="w-5 h-5"></i>
                                <span class="text-indigo-100 text-sm font-medium">Hombres</span>
                            </div>
                            <p class="text-4xl font-extrabold"><?= number_format($geoHombres) ?></p>
                            <p class="text-indigo-100 text-xs mt-1"><?= $geoTotal > 0 ? round($geoHombres/$geoTotal*100,1).'%' : '0%' ?> del total</p>
                        </div>
                    </div>
                </div>

                <!-- Drill down por nivel -->
                <?php if(!$geoDepto): ?>
                <div class="bg-gray-50 rounded-xl p-5 border">
                    <h4 class="font-medium mb-4">Departamentos</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <?php foreach($geoDeptos as $d): ?>
                        <a href="<?= $baseGeoUrl ?>&depto=<?= urlencode($d['departamento']) ?>" class="p-3 rounded-lg border hover:border-blue-500 hover:bg-blue-50 transition flex justify-between">
                            <span class="font-medium"><?= htmlspecialchars($d['departamento']) ?></span>
                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-sm"><?= $d['total'] ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php elseif(!$geoMpio): ?>
                <div class="bg-gray-50 rounded-xl p-5 border">
                    <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i data-lucide="landmark" class="w-5 h-5 text-blue-600"></i>Municipios en <?= htmlspecialchars($geoDepto) ?>
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <?php foreach($geoMpiOS as $m): ?>
                        <a href="<?= $baseGeoUrl ?>&depto=<?= urlencode($geoDepto) ?>&mpio=<?= urlencode($m['municipio']) ?>" class="p-3 rounded-lg border hover:border-blue-500 hover:bg-blue-50 transition flex justify-between">
                            <span class="font-medium"><?= htmlspecialchars($m['municipio']) ?></span>
                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-sm"><?= $m['total'] ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php elseif(!$geoBarrio): ?>
                <div class="bg-gray-50 rounded-xl p-5 border">
                    <h4 class="font-medium mb-4">Barrios en <?= htmlspecialchars($geoMpio) ?></h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <?php foreach($geoBarrios as $b): ?>
                        <a href="<?= $baseGeoUrl ?>&depto=<?= urlencode($geoDepto) ?>&mpio=<?= urlencode($geoMpio) ?>&barrio=<?= urlencode($b['barrio']) ?>" class="p-3 rounded-lg border hover:border-blue-500 hover:bg-blue-50 transition flex justify-between">
                            <span class="font-medium text-sm"><?= htmlspecialchars($b['barrio']) ?></span>
                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-sm"><?= $b['total'] ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-gray-50 rounded-xl p-5 border">
                    <h4 class="font-medium mb-4">Colaboradores en <?= htmlspecialchars($geoBarrio) ?></h4>
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Nombre</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Documento</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Perfil</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Nivel</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($geoColaboradores as $c): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium"><?= htmlspecialchars($c['nombres'].' '.$c['apellidos']) ?></td>
                                <td class="px-3 py-2 text-gray-500 text-sm"><?= $c['documento'] ?></td>
                                <td class="px-3 py-2"><span class="px-2 py-0.5 rounded-full text-xs bg-blue-100"><?= htmlspecialchars($c['perfil']) ?></span></td>
                                <td class="px-3 py-2 text-gray-500 text-sm"><?= htmlspecialchars($c['nivel_participacion']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        </div><!-- End Report Content -->
    </div>
</div>

<script>
function reportesData() {
    return {
        reporteActual: 'donaciones',
        charts: {},
        mapa: null,
        geo: {},

        init() {
            // Load initial tab from URL
            const urlParams = new URLSearchParams(window.location.search);
            this.reporteActual = urlParams.get('reporte') || 'general';
            
            this.$watch('reporteActual', () => {
                this.$nextTick(() => {
                    this.renderCharts();
                    lucide.createIcons();
                });
            });
            this.renderCharts();
        },

        getTituloReporte() {
            const titulos = {
                donaciones: 'Reporte de Donaciones',
                eventos: 'Reporte de Eventos',
                acciones: 'Reporte de Acciones Comunitarias',
                compromisos: 'Reporte de Compromisos',
                territorial: 'Análisis Territorial',
                general: 'Reporte General de Campaña',
                lideres: 'Red de Líderes y Reclutamiento',
                geografico: 'Distribución Geográfica'
            };
            return titulos[this.reporteActual] || 'Reporte';
        },

        renderCharts() {
            setTimeout(() => {
                if (this.reporteActual === 'donaciones') {
                    this.renderChartDonaciones();
                } else if (this.reporteActual === 'territorial') {
                    this.renderChartsTerritorial();
                }
            }, 100);
        },

        renderChartsTerritorial() {
            // Chart por perfil
            const ctxPerfil = document.getElementById('chartPerfil');
            if (ctxPerfil) {
                const perfLabels = <?= json_encode(array_column($perfilStats, 'perfil')) ?>;
                const perfData = <?= json_encode(array_column($perfilStats, 'total')) ?>;
                new Chart(ctxPerfil, {
                    type: 'doughnut',
                    data: { labels: perfLabels, datasets: [{ data: perfData, backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#EC4899'] }] }
                });
            }
            // Chart por nivel
            const ctxNivel = document.getElementById('chartNivel');
            if (ctxNivel) {
                const nivLabels = <?= json_encode(array_column($nivelStats, 'nivel')) ?>;
                const nivData = <?= json_encode(array_column($nivelStats, 'total')) ?>;
                new Chart(ctxNivel, {
                    type: 'doughnut',
                    data: { labels: nivLabels, datasets: [{ data: nivData, backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'] }] }
                });
            }
        },

        renderChartDonaciones() {
            const ctx = document.getElementById('chartDonacionesMetodo');
            if (!ctx) return;
            if (this.charts.donaciones) this.charts.donaciones.destroy();
            this.charts.donaciones = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Efectivo', 'Transferencia', 'Especie'],
                    datasets: [{
                        data: [45, 35, 20],
                        backgroundColor: ['#FF00FF', '#FFD700', '#00A859']
                    }]
                }
            });
        },

        renderMapaTerritorial() {
            if (!this.mapa) {
                this.mapa = L.map('mapaTerritorial').setView([4.570868, -74.297333], 6);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.mapa);
            }
        },

        // Geographic report functions
        async loadGeoMunicipios() {
            this.geo.municipio = '';
            this.geo.zona = '';
            this.geo.barrio = '';
            this.geo.municipios = [];
            if (!this.geo.departamento) return;
            try {
                const r = await fetch('/territorios/municipios-cascada?departamento=' + encodeURIComponent(this.geo.departamento));
                const d = await r.json();
                if (d.success) this.geo.municipios = d.data;
            } catch(e) { console.error(e); }
            this.loadColaboradores();
        },

        async loadGeoTerritorios() {
            this.geo.zona = '';
            this.geo.barrio = '';
            this.geo.zonas = [];
            if (!this.geo.municipio) return;
            try {
                const r = await fetch('/territorios/tipos?departamento=' + encodeURIComponent(this.geo.departamento) + '&municipio=' + encodeURIComponent(this.geo.municipio));
                const d = await r.json();
                if (d.success) this.geo.zonas = d.data;
            } catch(e) { console.error(e); }
            this.loadColaboradores();
        },

        async loadGeoBarrios() {
            this.geo.barrio = '';
            this.geo.barrios = [];
            if (!this.geo.zona) return;
            try {
                const r = await fetch('/territorios/territorios?departamento=' + encodeURIComponent(this.geo.departamento) + '&municipio=' + encodeURIComponent(this.geo.municipio) + '&tipo=' + encodeURIComponent(this.geo.zona));
                const d = await r.json();
                if (d.success) this.geo.barrios = d.data;
            } catch(e) { console.error(e); }
            this.loadColaboradores();
        },

        async loadColaboradores() {
            // Build query params from current filters
            let params = new URLSearchParams();
            if (this.geo.departamento) params.append('departamento', this.geo.departamento);
            if (this.geo.municipio) params.append('municipio', this.geo.municipio);
            if (this.geo.zona) params.append('tipo_territorio', this.geo.zona);
            if (this.geo.barrio) params.append('barrio', this.geo.barrio);
            params.append('campana_id', '<?= $campanaId ?>');
            
            try {
                const response = await fetch('/?api=colaboradores_list&' + params);
                const data = await response.json();
                if (data.colaboradores) {
                    this.geo.colaboradores = data.colaboradores;
                    this.geo.total = data.colaboradores.length;
                    this.geo.lideres = data.colaboradores.filter(c => c.perfil && c.perfil.includes('Lider')).length;
                    this.geo.simpatizantes = data.colaboradores.filter(c => c.nivel_participacion === 'Simpatizante').length;
                    this.geo.movilizadores = data.colaboradores.filter(c => c.nivel_participacion === 'Movilizador').length;
                }
            } catch(e) { console.error('Error:', e); }
        },

        exportarReporte() {
            alert('Exportar ' + this.getTituloReporte() + ' a PDF/Excel');
        },

        exportarTodo() {
            alert('Exportar todos los reportes');
        }
    }
}
lucide.createIcons();
</script>

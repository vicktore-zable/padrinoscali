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
                <!-- Stats Premium Cards (reactivos, se actualizan desde Alpine) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="relative overflow-hidden bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition transform hover:scale-[1.02]">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="users" class="w-5 h-5"></i>
                                <span class="text-blue-100 text-sm font-medium">Total</span>
                            </div>
                            <p class="text-4xl font-extrabold" x-text="geoResumen.total_colaboradores || '0'"></p>
                            <p class="text-blue-100 text-xs mt-1">Colaboradores activos</p>
                        </div>
                    </div>
                    <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition transform hover:scale-[1.02]">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="crown" class="w-5 h-5"></i>
                                <span class="text-amber-100 text-sm font-medium">Líderes</span>
                            </div>
                            <p class="text-4xl font-extrabold" x-text="geoResumen.total_lideres || '0'"></p>
                            <p class="text-amber-100 text-xs mt-1">Coordinadores activos</p>
                        </div>
                    </div>
                    <div class="relative overflow-hidden bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition transform hover:scale-[1.02]">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="venus" class="w-5 h-5"></i>
                                <span class="text-pink-100 text-sm font-medium">Mujeres</span>
                            </div>
                            <p class="text-4xl font-extrabold" x-text="geoResumen.total_mujeres || '0'"></p>
                            <p class="text-pink-100 text-xs mt-1" x-text="porcentajeGeo('total_mujeres')"></p>
                        </div>
                    </div>
                    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition transform hover:scale-[1.02]">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="mars" class="w-5 h-5"></i>
                                <span class="text-indigo-100 text-sm font-medium">Hombres</span>
                            </div>
                            <p class="text-4xl font-extrabold" x-text="geoResumen.total_hombres || '0'"></p>
                            <p class="text-indigo-100 text-xs mt-1" x-text="porcentajeGeo('total_hombres')"></p>
                        </div>
                    </div>
                </div>

                <!-- Filtros en cascada (Cali por defecto) -->
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2 text-sm">
                        <i data-lucide="map-pin" class="w-4 h-4 text-primary"></i>
                        <span class="font-medium text-gray-700">Cali, Valle del Cauca</span>
                    </div>
                    <div>
                        <select x-model="geoFiltro.tipo" @change="onCambioTipo()" class="input text-sm">
                            <option value="">Todos los tipos</option>
                            <template x-for="t in geoOpciones.tipos" :key="t">
                                <option :value="t" x-text="t"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <select x-model="geoFiltro.territorio" @change="onCambioTerritorio()" class="input text-sm" :disabled="!geoFiltro.tipo">
                            <option value="">Todas las comunas</option>
                            <template x-for="t in geoOpciones.territorios" :key="t">
                                <option :value="t" x-text="t"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <select x-model="geoFiltro.barrio" @change="onCambioBarrio()" class="input text-sm" :disabled="!geoFiltro.territorio">
                            <option value="">Todos los barrios</option>
                            <template x-for="b in geoOpciones.barrios" :key="b">
                                <option :value="b" x-text="b"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Panel dividido: Mapa + Lista lateral -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Mapa -->
                    <div class="lg:col-span-2 bg-gray-50 rounded-xl border overflow-hidden relative" style="min-height: 500px;">
                        <div id="mapaGeografico" style="height: 500px; width: 100%;"></div>
                        <div x-show="geoLoading" class="absolute inset-0 bg-white/70 flex items-center justify-center z-10">
                            <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-lg">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-primary"></div>
                                <span class="text-sm text-gray-600">Cargando mapa...</span>
                            </div>
                        </div>
                    </div>
                    <!-- Lista lateral -->
                    <div class="bg-white rounded-xl border max-h-[500px] overflow-y-auto">
                        <div class="p-3 border-b bg-gray-50 sticky top-0">
                            <h4 class="font-bold text-sm text-gray-700 flex items-center gap-1">
                                <i data-lucide="list" class="w-4 h-4"></i>
                                Territorios
                                 <span class="ml-auto text-xs font-normal text-gray-500" x-text="geoListaFiltrada.length + ' con datos'"></span>
                            </h4>
                        </div>
                        <div class="divide-y">
                            <template x-for="(item, idx) in geoListaFiltrada" :key="idx">
                                <div @click="seleccionarTerritorio(item)"
                                     class="p-3 cursor-pointer transition"
                                     :class="geoItemSeleccionado?.nombre === item.nombre ? 'bg-primary/10 border-l-4 border-primary' : 'hover:bg-gray-50 border-l-4 border-transparent'">
                                    <div class="flex justify-between items-start">
                                        <span class="font-medium text-sm text-gray-900" x-text="item.nombre"></span>
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                                              :class="item.total_colaboradores > 0 ? 'bg-pink-100 text-pink-700' : 'bg-gray-100 text-gray-500'"
                                              x-text="item.total_colaboradores"></span>
                                    </div>
                                    <div class="flex gap-3 text-xs text-gray-500 mt-1">
                                        <span>👑 <span x-text="item.total_lideres"></span> líderes</span>
                                        <span>♀️ <span x-text="item.total_mujeres"></span></span>
                                        <span>♂️ <span x-text="item.total_hombres"></span></span>
                                    </div>
                                    <!-- Barra de densidad -->
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2 overflow-hidden">
                                        <div class="h-1.5 rounded-full transition-all duration-500"
                                             :style="'width: ' + densidadRelativa(item.total_colaboradores) + '%'"
                                             :class="colorDensidadClase(item.total_colaboradores)"></div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="geoListaFiltrada.length === 0" class="p-6 text-center text-gray-400 text-sm">
                                <i data-lucide="map-pin" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                <p>Selecciona un tipo</p>
                                <p class="text-xs mt-1">para ver los territorios</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla detallada (al seleccionar un polígono/territorio) -->
                <div x-show="geoDetalleActivo" class="bg-white rounded-xl border overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-3 border-b flex items-center justify-between">
                        <h4 class="font-bold text-gray-800 flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-primary"></i>
                            Colaboradores en <span class="text-primary" x-text="geoDetalle.nombre"></span>
                        </h4>
                        <button @click="cerrarDetalle()" class="text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="overflow-x-auto p-4" x-show="geoDetalle.colaboradores.length > 0">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Nombre</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Documento</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Perfil</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Nivel</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Barrio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <template x-for="c in geoDetalle.colaboradores" :key="c.id || c.documento">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 font-medium text-sm" x-text="c.nombres + ' ' + (c.apellidos || '')"></td>
                                        <td class="px-3 py-2 text-gray-500 text-sm" x-text="c.documento"></td>
                                        <td class="px-3 py-2"><span class="px-2 py-0.5 rounded-full text-xs bg-blue-100" x-text="c.perfil"></span></td>
                                        <td class="px-3 py-2 text-gray-500 text-sm" x-text="c.nivel_participacion"></td>
                                        <td class="px-3 py-2 text-gray-500 text-sm" x-text="c.barrio || '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div x-show="geoDetalle.colaboradores.length === 0" class="p-6 text-center text-gray-400 text-sm">
                        No hay colaboradores en este territorio
                    </div>
                </div>
            </div>
        </div>
        </div><!-- End Report Content -->
    </div>
</div>

<style>
.polygon-label {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    font-size: 10px;
    font-weight: 600;
    color: #374151;
    text-shadow: 0 0 2px #fff, 0 0 2px #fff, 0 0 3px #fff;
    white-space: nowrap;
    pointer-events: none;
}
.polygon-label::before { display: none !important; }
</style>
<script>
function reportesData() {
    return {
        // ─── Propiedades existentes ───
        reporteActual: 'donaciones',
        charts: {},
        mapa: null,
        geo: {},

        // ─── Nuevas propiedades geográficas ───
        geoFiltro: { municipio: '', tipo: '', territorio: '', barrio: '' },
        geoOpciones: { municipios: [], tipos: [], territorios: [], barrios: [] },
        geoResumen: { total_colaboradores: 0, total_lideres: 0, total_mujeres: 0, total_hombres: 0 },
        geoListaTerritorios: [],
        geoDetalle: { nombre: '', colaboradores: [] },
        geoDetalleActivo: false,
        geoLoading: false,
        geoMapa: null,
        geoLayer: null,
        geoMapaConteos: {},
        geoItemSeleccionado: null,
        geoCacheGeoJSON: null,
        geoUltimoMunicipio: '',

        get geoListaFiltrada() {
            return this.geoListaTerritorios.filter(t => t.total_colaboradores > 0);
        },

        // ─── Init ───
        init() {
            const urlParams = new URLSearchParams(window.location.search);
            this.reporteActual = urlParams.get('reporte') || 'general';

            this.$watch('reporteActual', (val) => {
                this.$nextTick(() => {
                    this.renderCharts();
                    lucide.createIcons();
                    if (val === 'geografico') {
                        this.initMapaGeografico();
                        this.loadGeoMunicipios();
                    }
                });
            });

            this.renderCharts();

            // Inicializar geográfico si la URL ya trae ese tab
            if (this.reporteActual === 'geografico') {
                this.$nextTick(() => {
                    this.initMapaGeografico();
                    this.loadGeoMunicipios();
                });
            }
        },

        // ─── Init Mapa ───
        initMapaGeografico() {
            if (this.geoMapa) return;
            const el = document.getElementById('mapaGeografico');
            if (!el || el._leaflet_id) return;
            this.geoMapa = L.map('mapaGeografico', { zoomControl: true }).setView([3.4516, -76.5320], 12);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://carto.com/">CARTO</a>'
            }).addTo(this.geoMapa);
            this.geoLayer = L.layerGroup().addTo(this.geoMapa);
        },

        async loadGeoMunicipios() {
            this.geoFiltro.municipio = 'CALI';
            this.onCambioMunicipio();
        },

        // ─── Handlers de filtros en cascada ───
        async onCambioMunicipio() {
            this.geoFiltro.tipo = '';
            this.geoFiltro.territorio = '';
            this.geoFiltro.barrio = '';
            this.geoOpciones.tipos = [];
            this.geoOpciones.territorios = [];
            this.geoOpciones.barrios = [];
            if (!this.geoFiltro.municipio) {
                this.limpiarMapa();
                return;
            }
            try {
                const r = await fetch('/aratio/api/territorios.php?accion=tipos_territorio&departamento=VALLE DEL CAUCA&municipio=' + encodeURIComponent(this.geoFiltro.municipio));
                const d = await r.json();
                if (d.success) this.geoOpciones.tipos = d.data;
            } catch (e) { console.error(e); }
            this.cargarGeoDatos();
        },

        async onCambioTipo() {
            this.geoFiltro.territorio = '';
            this.geoFiltro.barrio = '';
            this.geoOpciones.territorios = [];
            this.geoOpciones.barrios = [];
            if (!this.geoFiltro.tipo) {
                this.cargarGeoDatos();
                return;
            }
            try {
                const r = await fetch('/aratio/api/territorios.php?accion=territorios&departamento=VALLE DEL CAUCA&municipio=' + encodeURIComponent(this.geoFiltro.municipio) + '&tipo_territorio=' + encodeURIComponent(this.geoFiltro.tipo));
                const d = await r.json();
                if (d.success) this.geoOpciones.territorios = d.data;
            } catch (e) { console.error(e); }
            this.cargarGeoDatos();
        },

        async onCambioTerritorio() {
            this.geoFiltro.barrio = '';
            this.geoOpciones.barrios = [];
            if (!this.geoFiltro.territorio) {
                this.cargarGeoDatos();
                return;
            }
            try {
                const r = await fetch('/aratio/api/territorios.php?accion=barrios&departamento=VALLE DEL CAUCA&municipio=' + encodeURIComponent(this.geoFiltro.municipio) + '&tipo_territorio=' + encodeURIComponent(this.geoFiltro.tipo) + '&territorio=' + encodeURIComponent(this.geoFiltro.territorio));
                const d = await r.json();
                if (d.success) this.geoOpciones.barrios = d.data;
            } catch (e) { console.error(e); }
            this.cargarGeoDatos();
        },

        onCambioBarrio() {
            this.cargarGeoDatos();
        },

        // ─── Carga de datos del mapa ───
        async cargarGeoDatos() {
            if (!this.geoFiltro.municipio) {
                this.limpiarMapa();
                return;
            }
            this.geoLoading = true;
            this.cerrarDetalle();

            try {
                // 1. GeoJSON: cachear por municipio (solo refetch si cambia municipio)
                const municipioCambio = this.geoFiltro.municipio !== this.geoUltimoMunicipio;
                if (municipioCambio || !this.geoCacheGeoJSON) {
                    this.geoUltimoMunicipio = this.geoFiltro.municipio;
                    const geoRes = await fetch('/aratio/api_territorios_geojson.php?municipio=' + encodeURIComponent(this.geoFiltro.municipio));
                    this.geoCacheGeoJSON = await geoRes.json();
                }

                // 2. Filtrar polígonos localmente
                let filtered = this.geoCacheGeoJSON;
                if (this.geoFiltro.tipo || this.geoFiltro.territorio || this.geoFiltro.barrio) {
                    const features = (this.geoCacheGeoJSON.features || []).filter(f => {
                        const p = f.properties || {};
                        if (this.geoFiltro.tipo && p.Tipo_territorio !== this.geoFiltro.tipo) return false;
                        if (this.geoFiltro.territorio && p.Territorio !== this.geoFiltro.territorio) return false;
                        if (this.geoFiltro.barrio && p.barrio !== this.geoFiltro.barrio) return false;
                        return true;
                    });
                    filtered = { ...this.geoCacheGeoJSON, features };
                }

                // 3. Conteos (siempre a servidor porque dependen de filtros)
                const paramsCount = new URLSearchParams({ municipio: this.geoFiltro.municipio });
                if (this.geoFiltro.tipo) paramsCount.append('tipo_territorio', this.geoFiltro.tipo);
                if (this.geoFiltro.territorio) paramsCount.append('territorio', this.geoFiltro.territorio);
                if (this.geoFiltro.barrio) paramsCount.append('barrio', this.geoFiltro.barrio);

                const countRes = await fetch('/aratio/api/reporte_geo_colaboradores.php?campana_id=<?= $campanaId ?>&' + paramsCount.toString());
                const countData = await countRes.json();

                // 4. Actualizar resumen
                if (countData.success) {
                    this.geoResumen = countData.resumen;
                    this.geoListaTerritorios = countData.lista_territorios || [];
                    this.geoMapaConteos = countData.mapa_conteos || {};
                }

                // 5. Renderizar polígonos
                this.renderizarPoligonos(filtered);

            } catch (e) {
                console.error('Error cargando datos geográficos:', e);
            } finally {
                this.geoLoading = false;
            }
        },

        renderizarPoligonos(geoData) {
            if (!this.geoLayer || !this.geoMapa) return;

            // Unbind tooltips de capas viejas antes de removerlas (evita Tooltip.js errors)
            this.geoLayer.eachLayer(function(l) {
                if (l.unbindTooltip) l.unbindTooltip();
                if (l.unbindPopup) l.unbindPopup();
            });
            this.geoMapa.removeLayer(this.geoLayer);
            this.geoLayer = L.layerGroup().addTo(this.geoMapa);

            if (!geoData.features || geoData.features.length === 0) return;

            const self = this;
            const layer = L.geoJSON(geoData, {
                style: function (feature) {
                    return self.calcularEstilo(feature);
                },
                onEachFeature: function (feature, leafletLayer) {
                    self.onEachFeature(feature, leafletLayer);
                }
            });

            this.geoLayer.addLayer(layer);
            try {
                this.geoMapa.fitBounds(layer.getBounds(), { padding: [20, 20], animate: false });
            } catch (e) {
                // bounds inválidos (geometría degenerada), ignorar
            }
        },

        calcularEstilo(feature) {
            const props = feature.properties;
            const id = props.id || feature.id;
            const conteo = this.geoMapaConteos ? this.geoMapaConteos[id] : null;
            const total = conteo ? conteo.total : 0;

            if (total === 0) {
                return {
                    fillColor: 'transparent',
                    fillOpacity: 0,
                    weight: 0.5,
                    opacity: 0.3,
                    color: '#d1d5db'
                };
            }

            const color = this.getColorDensidad(total);
            const opacity = Math.min(0.25 + (total / 15) * 0.5, 0.75);
            return {
                fillColor: color,
                fillOpacity: opacity,
                weight: 1.5,
                opacity: 0.8,
                color: '#ffffff'
            };
        },

        getColorDensidad(total) {
            if (total >= 15) return '#7c3aed';
            if (total >= 10) return '#a855f7';
            if (total >= 6) return '#d946ef';
            if (total >= 3) return '#ec4899';
            if (total > 0) return '#f472b6';
            return 'transparent';
        },

        onEachFeature(feature, leafletLayer) {
            const self = this;
            const props = feature.properties;
            const id = props.id || feature.id;
            const conteo = this.geoMapaConteos ? this.geoMapaConteos[id] : null;
            const total = conteo ? conteo.total : 0;
            const lideres = conteo ? conteo.lideres : 0;
            const mujeres = conteo ? conteo.mujeres : 0;
            const hombres = conteo ? conteo.hombres : 0;

            const nombre = props.barrio || props.Territorio || 'Sin nombre';
            const sector = props.Territorio || '';
            const tipo = props.Tipo_territorio || '';

            // Tooltip en hover
            leafletLayer.bindTooltip(nombre, {
                direction: 'center',
                className: 'polygon-label'
            });

            leafletLayer.bindPopup(`
                <div style="min-width: 220px; font-family: system-ui, sans-serif;">
                    <div style="font-weight: 700; font-size: 14px; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 6px;">
                        🏘️ ${nombre}
                    </div>
                    ${sector ? `<div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">${tipo} — ${sector}</div>` : ''}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px; font-size: 13px;">
                        <div>👥 <strong>${total}</strong> colaboradores</div>
                        <div>👑 <strong>${lideres}</strong> líderes</div>
                        <div>♀️ <strong>${mujeres}</strong> mujeres</div>
                        <div>♂️ <strong>${hombres}</strong> hombres</div>
                    </div>
                    <button id="btn-ver-lista-${id}"
                            style="margin-top: 8px; width: 100%; padding: 6px; background: #1e3a5f; color: white; border: none; border-radius: 6px; font-size: 12px; cursor: pointer;">
                        Ver lista →
                    </button>
                </div>
            `);

            leafletLayer.on('popupopen', function(e) {
                const btnId = 'btn-ver-lista-' + id;
                setTimeout(function() {
                    const btn = document.getElementById(btnId);
                    if (btn) {
                        btn.onclick = function() {
                            self.seleccionarTerritorioPorBarrio(nombre, sector);
                        };
                    }
                }, 50);
            });

            leafletLayer.on({
                mouseover: function (e) {
                    const layer = e.target;
                    layer.setStyle({
                        weight: 3,
                        color: '#FFD700',
                        dashArray: '',
                        fillOpacity: 0.6
                    });
                    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                        layer.bringToFront();
                    }
                },
                mouseout: function (e) {
                    const layer = e.target;
                    if (self.calcularEstilo) {
                        layer.setStyle(self.calcularEstilo(layer.feature));
                    }
                },
                click: function (e) {
                    const layer = e.target;
                    if (self.geoMapa) {
                        self.geoMapa.fitBounds(layer.getBounds(), { maxZoom: 16 });
                    }
                    const id = layer.feature.properties.id || layer.feature.id;
                    const conteo = self.geoMapaConteos ? self.geoMapaConteos[id] : null;
                    if (conteo) {
                        self.seleccionarTerritorio({
                            nombre: conteo.territorio || conteo.barrio || 'Sin nombre',
                            barrio: conteo.barrio,
                            total_colaboradores: conteo.total,
                            total_lideres: conteo.lideres,
                            total_mujeres: conteo.mujeres,
                            total_hombres: conteo.hombres
                        });
                    }
                }
            });
        },

        // ─── Selección de territorio ───
        seleccionarTerritorio(item) {
            this.geoItemSeleccionado = item;
            this.geoDetalleActivo = true;
            this.geoDetalle = { nombre: item.nombre, colaboradores: [] };

            // Si viene de sidebar (comuna con varios barrios), pasar todos
            const barrioVal = item.barrios
                ? item.barrios.map(b => b.barrio).join(',')
                : (item.barrio || item.nombre);

            const params = new URLSearchParams({
                campana_id: '<?= $campanaId ?>',
                barrio: barrioVal,
                municipio: this.geoFiltro.municipio
            });

            const url = '/aratio/api/colaboradores.php?' + params.toString();
            console.log('Fetching colaboradores:', url);
            fetch(url)
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(data => {
                    console.log('Respuesta colaboradores:', data);
                    if (data.success && data.data) {
                        this.geoDetalle.colaboradores = data.data;
                    } else if (data.data && data.data.length === 0) {
                        this.geoDetalle.colaboradores = [];
                    }
                })
                .catch(e => {
                    console.error('Error fetching colaboradores:', e);
                    this.geoDetalle.colaboradores = [];
                });
        },

        seleccionarTerritorioPorBarrio(nombreBarrio, sector) {
            const item = this.geoListaTerritorios.find(t =>
                t.nombre === (sector || nombreBarrio)
            );
            if (item) {
                this.seleccionarTerritorio(item);
            } else {
                this.seleccionarTerritorio({
                    nombre: nombreBarrio,
                    barrio: nombreBarrio,
                    total_colaboradores: 0,
                    total_lideres: 0,
                    total_mujeres: 0,
                    total_hombres: 0
                });
            }
        },

        cerrarDetalle() {
            this.geoDetalleActivo = false;
            this.geoDetalle = { nombre: '', colaboradores: [] };
            this.geoItemSeleccionado = null;
        },

        // ─── Helpers ───
        porcentajeGeo(campo) {
            const total = this.geoResumen.total_colaboradores || 1;
            const val = this.geoResumen[campo] || 0;
            return Math.round(val / total * 100) + '% del total';
        },

        densidadRelativa(total) {
            const max = Math.max(...this.geoListaTerritorios.map(t => t.total_colaboradores), 1);
            return Math.min((total / max) * 100, 100);
        },

        colorDensidadClase(total) {
            if (total >= 15) return 'bg-violet-600';
            if (total >= 10) return 'bg-purple-500';
            if (total >= 6) return 'bg-fuchsia-500';
            if (total >= 3) return 'bg-pink-500';
            if (total > 0) return 'bg-pink-400';
            return 'bg-gray-200';
        },

        limpiarMapa() {
            if (this.geoLayer) this.geoLayer.clearLayers();
            this.geoResumen = { total_colaboradores: 0, total_lideres: 0, total_mujeres: 0, total_hombres: 0 };
            this.geoListaTerritorios = [];
            this.cerrarDetalle();
        },

        // ─── Métodos existentes ───
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
            const ctxPerfil = document.getElementById('chartPerfil');
            if (ctxPerfil) {
                const perfLabels = <?= json_encode(array_column($perfilStats, 'perfil')) ?>;
                const perfData = <?= json_encode(array_column($perfilStats, 'total')) ?>;
                new Chart(ctxPerfil, {
                    type: 'doughnut',
                    data: { labels: perfLabels, datasets: [{ data: perfData, backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#EC4899'] }] }
                });
            }
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

<?php
if (!$campanaActiva) {
    echo '<div class="card text-center py-12"><i data-lucide="flag" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i><h2 class="text-xl font-bold text-gray-900 mb-2">Selecciona una campaña</h2></div>';
    return;
}

$db = getDB();
$campanaId = $campanaActiva['id'];

$stmt = $db->prepare("SELECT perfil, COUNT(*) as total FROM colaboradores WHERE campana_id = ? AND perfil IS NOT NULL AND perfil != '' GROUP BY perfil ORDER BY total DESC");
$stmt->execute([$campanaId]);
$perfiles = $stmt->fetchAll();

$stmt = $db->prepare("SELECT nivel_participacion, COUNT(*) as total FROM colaboradores WHERE campana_id = ? AND nivel_participacion IS NOT NULL AND nivel_participacion != '' GROUP BY nivel_participacion ORDER BY total DESC");
$stmt->execute([$campanaId]);
$niveles = $stmt->fetchAll();

$stmt = $db->prepare("SELECT municipio, COUNT(*) as total FROM colaboradores WHERE campana_id = ? AND municipio IS NOT NULL AND municipio != '' GROUP BY municipio ORDER BY total DESC");
$stmt->execute([$campanaId]);
$municipios = $stmt->fetchAll();

$stmt = $db->prepare("SELECT departamento, COUNT(*) as total, SUM(CASE WHEN perfil LIKE '%Lider%' THEN 1 ELSE 0 END) as lideres FROM colaboradores WHERE campana_id = ? AND departamento IS NOT NULL AND departamento != '' GROUP BY departamento ORDER BY total DESC");
$stmt->execute([$campanaId]);
$departamentos = $stmt->fetchAll();

$stmt = $db->prepare("SELECT l.id, l.documento, l.nombres, l.apellidos, l.municipio, l.territorio, l.barrio, l.telefono, COUNT(c.id) as seguidores FROM colaboradores l LEFT JOIN colaboradores c ON l.documento = c.lider_directo AND c.campana_id = ? AND c.estado NOT IN ('inactivo','Inactivo') WHERE l.campana_id = ? AND (l.perfil LIKE '%Lider%' OR l.perfil LIKE '%Líder%' OR l.perfil LIKE '%Candidat%') AND l.estado NOT IN ('inactivo','Inactivo') GROUP BY l.id ORDER BY seguidores DESC");
$stmt->execute([$campanaId, $campanaId]);
$lideres = $stmt->fetchAll();

$stmt = $db->prepare("SELECT tipo, COUNT(*) as total FROM eventos WHERE campana_id = ? GROUP BY tipo ORDER BY total DESC");
$stmt->execute([$campanaId]);
$eventosTipo = $stmt->fetchAll();

$stmt = $db->prepare("SELECT nombre, asistentes_confirmados FROM eventos WHERE campana_id = ? ORDER BY asistentes_confirmados DESC");
$stmt->execute([$campanaId]);
$eventosAsistencia = $stmt->fetchAll();

$stmt = $db->prepare("SELECT estado, COUNT(*) as total FROM compromisos WHERE campana_id = ? GROUP BY estado ORDER BY total DESC");
$stmt->execute([$campanaId]);
$compromisosEstado = $stmt->fetchAll();

$kpiColab = $db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id = $campanaId AND estado NOT IN ('inactivo','Inactivo')")->fetchColumn();
$kpiLideres = $db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id = $campanaId AND (perfil LIKE '%Lider%' OR perfil LIKE '%Líder%' OR perfil LIKE '%Candidat%') AND estado NOT IN ('inactivo','Inactivo')")->fetchColumn();
$kpiMujeres = $db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id = $campanaId AND genero = 'Femenino' AND estado NOT IN ('inactivo','Inactivo')")->fetchColumn();
$kpiHombres = $db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id = $campanaId AND genero = 'Masculino' AND estado NOT IN ('inactivo','Inactivo')")->fetchColumn();
$kpiCompromisosTotal = $db->query("SELECT COUNT(*) FROM compromisos WHERE campana_id = $campanaId")->fetchColumn();
$kpiCompromisosCumplidos = $db->query("SELECT COUNT(*) FROM compromisos WHERE campana_id = $campanaId AND estado = 'cumplido'")->fetchColumn();

$tabs = [
    'general' => ['icon' => 'bar-chart-3', 'label' => 'General'],
    'lideres' => ['icon' => 'users-2', 'label' => 'Líderes'],
    'geografico' => ['icon' => 'map-pin', 'label' => 'Geográfico'],
    'eventos' => ['icon' => 'calendar', 'label' => 'Eventos'],
    'compromisos' => ['icon' => 'handshake', 'label' => 'Compromisos'],
    'donaciones' => ['icon' => 'dollar-sign', 'label' => 'Donaciones'],
];
$defaultTab = $_GET['reporte'] ?? 'general';
if (!isset($tabs[$defaultTab])) $defaultTab = 'general';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="space-y-6" x-data="reportesData()" x-init="init()">
    <!-- KPIs -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="stat-card">
            <p class="text-2xl font-black text-[#1e3a5f]"><?= number_format($kpiColab) ?></p>
            <p class="text-xs font-semibold text-gray-400 uppercase mt-1">Colaboradores</p>
        </div>
        <div class="stat-card">
            <p class="text-2xl font-black text-amber-600"><?= number_format($kpiLideres) ?></p>
            <p class="text-xs font-semibold text-gray-400 uppercase mt-1">Líderes</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <span class="text-2xl font-black text-pink-600"><?= number_format($kpiMujeres) ?></span>
                <span class="text-gray-300 text-lg">/</span>
                <span class="text-2xl font-black text-indigo-600"><?= number_format($kpiHombres) ?></span>
            </div>
            <p class="text-xs font-semibold text-gray-400 uppercase mt-1">Mujeres / Hombres</p>
        </div>
        <div class="stat-card">
            <span class="text-2xl font-black text-green-600"><?= number_format($kpiCompromisosCumplidos) ?>/<?= number_format($kpiCompromisosTotal) ?></span>
            <p class="text-xs font-semibold text-gray-400 uppercase mt-1">Compromisos</p>
        </div>
    </div>

    <!-- Tabs como pills -->
    <div class="flex flex-wrap gap-2">
        <?php foreach ($tabs as $key => $tab): ?>
        <button @click="tab = '<?= $key ?>'"
                :class="tab === '<?= $key ?>' ? 'bg-[#1e3a5f] text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all">
            <i data-lucide="<?= $tab['icon'] ?>" class="w-4 h-4"></i>
            <?= htmlspecialchars($tab['label']) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- ==================== GENERAL ==================== -->
    <div x-show="tab === 'general'" x-cloak>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Mini KPIs -->
            <div class="md:col-span-3 grid grid-cols-2 md:grid-cols-5 gap-3">
                <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl p-4 text-white shadow-lg">
                    <p class="text-2xl font-black"><?= number_format($kpiColab) ?></p>
                    <p class="text-blue-100 text-xs mt-1">Colaboradores</p>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-amber-700 rounded-xl p-4 text-white shadow-lg">
                    <p class="text-2xl font-black"><?= number_format($kpiLideres) ?></p>
                    <p class="text-amber-100 text-xs mt-1">Líderes</p>
                </div>
                <div class="bg-gradient-to-br from-pink-500 to-pink-700 rounded-xl p-4 text-white shadow-lg">
                    <p class="text-2xl font-black"><?= number_format($kpiMujeres) ?></p>
                    <p class="text-pink-100 text-xs mt-1">Mujeres</p>
                </div>
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl p-4 text-white shadow-lg">
                    <p class="text-2xl font-black"><?= number_format($kpiHombres) ?></p>
                    <p class="text-indigo-100 text-xs mt-1">Hombres</p>
                </div>
                <div class="bg-gradient-to-br from-green-600 to-green-800 rounded-xl p-4 text-white shadow-lg">
                    <p class="text-2xl font-black"><?= number_format($kpiCompromisosCumplidos) ?>/<?= number_format($kpiCompromisosTotal) ?></p>
                    <p class="text-green-100 text-xs mt-1">Compromisos</p>
                </div>
            </div>
            <!-- Dona de perfiles (más pequeña) -->
            <div class="card">
                <h3 class="text-xs font-black text-[#1e3a5f] uppercase tracking-wide mb-3">Perfiles</h3>
                <div class="flex items-center gap-4">
                    <div class="w-32 h-32 flex-shrink-0">
                        <canvas id="chartPerfiles" width="128" height="128"></canvas>
                    </div>
                    <div class="flex-1 space-y-1.5 min-w-0">
                        <?php $i=0; foreach ($perfiles as $p):
                            $pct = round($p['total'] / max($kpiColab, 1) * 100);
                            $colors = ['#1e3a5f','#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316','#6366f1'];
                        ?>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?= $colors[$i % 10] ?>"></span>
                            <span class="text-gray-600 truncate"><?= htmlspecialchars($p['perfil']) ?></span>
                            <span class="ml-auto font-semibold text-gray-900"><?= $p['total'] ?></span>
                            <span class="text-gray-400 w-8 text-right"><?= $pct ?>%</span>
                        </div>
                        <?php $i++; endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- Nivel de participación -->
            <div class="card">
                <h3 class="text-xs font-black text-[#1e3a5f] uppercase tracking-wide mb-3">Nivel de Participación</h3>
                <div class="space-y-2">
                    <?php foreach ($niveles as $n):
                        $pct = round($n['total'] / max($kpiColab, 1) * 100);
                    ?>
                    <div>
                        <div class="flex justify-between text-xs mb-0.5">
                            <span class="text-gray-600"><?= htmlspecialchars($n['nivel_participacion']) ?></span>
                            <span class="font-semibold text-gray-900"><?= $n['total'] ?></span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Quick stats -->
            <div class="card">
                <h3 class="text-xs font-black text-[#1e3a5f] uppercase tracking-wide mb-3">Indicadores Rápidos</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                        <span class="text-xs text-gray-500">Municipios activos</span>
                        <span class="text-sm font-bold text-[#1e3a5f]"><?= count($municipios) ?></span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                        <span class="text-xs text-gray-500">Departamentos</span>
                        <span class="text-sm font-bold text-[#1e3a5f]"><?= count($departamentos) ?></span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                        <span class="text-xs text-gray-500">Perfiles distintos</span>
                        <span class="text-sm font-bold text-[#1e3a5f]"><?= count($perfiles) ?></span>
                    </div>
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-xs text-gray-500">% Mujeres</span>
                        <span class="text-sm font-bold text-pink-600"><?= $kpiColab > 0 ? round($kpiMujeres / $kpiColab * 100) : 0 ?>%</span>
                    </div>
                </div>
            </div>
            <!-- Top 5 municipios -->
            <div class="md:col-span-2 card">
                <h3 class="text-xs font-black text-[#1e3a5f] uppercase tracking-wide mb-3">Top Municipios</h3>
                <div class="space-y-2">
                    <?php foreach (array_slice($municipios, 0, 5) as $m):
                        $pct = round($m['total'] / max($kpiColab, 1) * 100, 1);
                    ?>
                    <div>
                        <div class="flex justify-between text-xs mb-0.5">
                            <span class="font-medium text-gray-700"><?= htmlspecialchars($m['municipio']) ?></span>
                            <span class="text-gray-500"><?= $m['total'] ?> (<?= $pct ?>%)</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Departamentos compactos -->
            <div class="card md:col-span-3">
                <h3 class="text-xs font-black text-[#1e3a5f] uppercase tracking-wide mb-3">Distribución por Departamento</h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr class="border-b text-left text-xs text-gray-500 uppercase">
                                <th class="pb-2 pr-4">Departamento</th>
                                <th class="pb-2 pr-4 text-right">Colab</th>
                                <th class="pb-2 pr-4 text-right">%</th>
                                <th class="pb-2 pr-4 text-right">Líderes</th>
                                <th class="pb-2 text-right">Barra</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php $maxDepto = $departamentos[0]['total'] ?? 1; ?>
                            <?php foreach ($departamentos as $d):
                                $pct = round($d['total'] / max($kpiColab, 1) * 100, 1);
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 pr-4 font-medium text-sm"><?= htmlspecialchars($d['departamento']) ?></td>
                                <td class="py-2 pr-4 text-right text-sm"><?= $d['total'] ?></td>
                                <td class="py-2 pr-4 text-right text-sm text-gray-500"><?= $pct ?>%</td>
                                <td class="py-2 pr-4 text-right text-sm"><?= $d['lideres'] ?? 0 ?></td>
                                <td class="py-2 text-right">
                                    <div class="w-24 bg-gray-100 rounded-full h-2 ml-auto">
                                        <div class="bg-[#1e3a5f] h-2 rounded-full" style="width: <?= round($d['total'] / $maxDepto * 100) ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== LÍDERES ==================== -->
    <div x-show="tab === 'lideres'" x-cloak>
        <div class="card p-0">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-sm font-black text-[#1e3a5f] uppercase tracking-wide">Ranking de Padrinos</h3>
                <p class="text-xs text-gray-400 mt-1">Haz clic en un padrino para ver su ficha completa.</p>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Padrino</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Seguidores</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Territorio</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (empty($lideres)): ?>
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No hay líderes con seguidores en esta campaña</td></tr>
                        <?php else:
                            $maxSeg = max(array_column($lideres, 'seguidores')) ?: 1;
                        ?>
                        <?php foreach ($lideres as $i => $lr): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 font-mono"><?= $i + 1 ?></td>
                            <td class="px-4 py-3">
                                <div class="font-medium"><?= htmlspecialchars($lr['nombres'] . ' ' . $lr['apellidos']) ?></div>
                                <div class="text-xs text-gray-400"><?= htmlspecialchars($lr['documento']) ?></div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1 badge font-bold <?= $lr['seguidore'] > 0 ? 'badge-info' : 'bg-gray-100 text-gray-500' ?>">
                                    <i data-lucide="users" class="w-3 h-3"></i>
                                    <?= $lr['seguidores'] ?>
                                </span>
                                <div class="w-24 bg-gray-100 rounded-full h-1.5 mx-auto mt-2">
                                    <div class="h-1.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600" style="width: <?= round($lr['seguidores'] / $maxSeg * 100) ?>%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                <?= htmlspecialchars($lr['municipio'] ?? '') ?>
                                <?= $lr['territorio'] ? '— ' . htmlspecialchars($lr['territorio']) : '' ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1.5">
                                <a href="?page=colaborador_detalle&id=<?= $lr['id'] ?>"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-[#1e3a5f] rounded-lg hover:bg-[#2a4f7f] transition">
                                    <i data-lucide="eye" class="w-3 h-3"></i>
                                    Ficha
                                </a>
                                <a href="?page=colaboradores_red&focus=<?= htmlspecialchars($lr['documento']) ?>"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-fuchsia-600 rounded-lg hover:bg-fuchsia-700 transition">
                                    <i data-lucide="network" class="w-3 h-3"></i>
                                    Red
                                </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== GEOGRÁFICO (Mapa con polígonos) ==================== -->
    <div x-show="tab === 'geografico'" x-cloak>
        <div class="relative bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" style="min-height: 600px;">
            <!-- Filtros -->
            <div class="p-4 border-b border-gray-100 flex flex-wrap gap-3 items-center">
                <span class="text-xs font-black text-[#1e3a5f] uppercase tracking-wide">Cobertura Territorial</span>
                <div class="flex-1"></div>
                <select x-model="geoFiltro.tipo" @change="cargarGeo()" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white">
                    <option value="">Todos los tipos</option>
                    <template x-for="t in geoOpciones.tipos" :key="t">
                        <option :value="t" x-text="t"></option>
                    </template>
                </select>
                <select x-model="geoFiltro.territorio" @change="cargarGeo()" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white" :disabled="!geoFiltro.tipo">
                    <option value="">Todas las comunas</option>
                    <template x-for="t in geoOpciones.territorios" :key="t">
                        <option :value="t" x-text="t"></option>
                    </template>
                </select>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="w-3 h-3 rounded-full bg-[#7c3aed]"></span> &ge;15
                    <span class="w-3 h-3 rounded-full bg-[#d946ef]"></span> 6-14
                    <span class="w-3 h-3 rounded-full bg-[#f472b6]"></span> 1-5
                </div>
            </div>
            <!-- Mapa -->
            <div id="mapaGeografico" style="height: 600px; width: 100%;"></div>
            <!-- Loading -->
            <div x-show="geoLoading" class="absolute inset-0 bg-white/70 flex items-center justify-center z-10" style="top: 52px;">
                <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-lg">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-primary"></div>
                    <span class="text-sm text-gray-600">Cargando mapa...</span>
                </div>
            </div>
            <!-- Detalle lateral -->
            <div x-show="geoDetalleActivo" class="absolute bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white rounded-xl shadow-2xl border z-20">
                <div class="p-4 border-b flex items-center justify-between">
                    <h4 class="font-bold text-sm" x-text="geoDetalle.nombre"></h4>
                    <button @click="geoDetalleActivo = false" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <div class="p-4 max-h-64 overflow-y-auto">
                    <template x-for="c in geoDetalle.colaboradores" :key="c.id || c.documento">
                        <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                            <div>
                                <span class="text-sm font-medium" x-text="c.nombres + ' ' + (c.apellidos || '')"></span>
                                <span class="text-xs text-gray-400 block" x-text="c.perfil"></span>
                            </div>
                            <span class="text-xs text-gray-500" x-text="c.nivel_participacion || '-'"></span>
                        </div>
                    </template>
                    <div x-show="geoDetalle.colaboradores.length === 0" class="text-center text-gray-400 py-4 text-sm">
                        No hay colaboradores en este polígono
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== EVENTOS ==================== -->
    <div x-show="tab === 'eventos'" x-cloak>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-xs font-black text-[#1e3a5f] uppercase tracking-wide mb-4">Eventos por Tipo</h3>
                <canvas id="chartEventosTipo" height="200"></canvas>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-xs font-black text-[#1e3a5f] uppercase tracking-wide mb-4">Asistencia por Evento</h3>
                <canvas id="chartEventosAsistencia" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- ==================== COMPROMISOS ==================== -->
    <div x-show="tab === 'compromisos'" x-cloak>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-xs font-black text-[#1e3a5f] uppercase tracking-wide mb-4">Compromisos por Estado</h3>
                <canvas id="chartCompromisosEstado" height="200"></canvas>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-5xl font-black text-[#1e3a5f]"><?= $kpiCompromisosCumplidos ?> / <?= $kpiCompromisosTotal ?></p>
                        <p class="text-sm text-gray-400 mt-2">compromisos cumplidos</p>
                    </div>
                    <div class="w-28 h-28 rounded-full bg-green-50 flex items-center justify-center">
                        <span class="text-3xl font-black text-green-600">
                            <?= $kpiCompromisosTotal > 0 ? round($kpiCompromisosCumplidos / $kpiCompromisosTotal * 100) : 0 ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== DONACIONES ==================== -->
    <div x-show="tab === 'donaciones'" x-cloak>
        <div class="bg-white rounded-2xl p-12 shadow-sm border border-gray-100 text-center">
            <i data-lucide="dollar-sign" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
            <p class="text-lg font-semibold text-gray-500">Donaciones registradas</p>
            <p class="text-sm text-gray-400 mt-1">Usa el módulo de Donaciones en Gestión Social para ver el detalle completo.</p>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
.polygon-label { background: none !important; border: none !important; box-shadow: none !important; font-size: 10px; font-weight: 600; color: #374151; text-shadow: 0 0 2px #fff, 0 0 2px #fff, 0 0 3px #fff; white-space: nowrap; pointer-events: none; }
.polygon-label::before { display: none !important; }
</style>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function reportesData() {
    return {
        tab: '<?= $defaultTab ?>',
        charts: {},
        // Geo
        geoMapa: null,
        geoLayer: null,
        geoMapaConteos: {},
        geoCacheGeoJSON: null,
        geoLoading: false,
        geoFiltro: { tipo: '', territorio: '' },
        geoOpciones: { tipos: [], territorios: [] },
        geoDetalle: { nombre: '', colaboradores: [] },
        geoDetalleActivo: false,

        init() {
            this.$nextTick(() => {
                lucide.createIcons();
                setTimeout(() => this.renderCharts(), 100);
            });
            this.$watch('tab', (val) => {
                this.destroyCharts();
                if (this.geoMapa) {
                    this.geoMapa.eachLayer(l => {
                        if (l.unbindTooltip) try { l.unbindTooltip(); } catch(e) {}
                        if (l.unbindPopup) try { l.unbindPopup(); } catch(e) {}
                    });
                }
                this.$nextTick(() => {
                    lucide.createIcons();
                    setTimeout(() => {
                        if (['general','eventos','compromisos'].includes(val)) this.renderCharts();
                    }, 50);
                    if (val === 'geografico') setTimeout(() => this.initMapa(), 100);
                });
            });
            if (this.tab === 'geografico') setTimeout(() => this.initMapa(), 300);
        },

        // ─── MAPA ───
        async initMapa() {
            if (this.geoMapa) return;
            const el = document.getElementById('mapaGeografico');
            if (!el || el._leaflet_id) return;
            this.geoMapa = L.map('mapaGeografico', { zoomControl: true }).setView([3.4516, -76.5320], 12);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://carto.com/">CARTO</a>'
            }).addTo(this.geoMapa);
            this.geoLayer = L.layerGroup().addTo(this.geoMapa);
            await this.cargarTipos();
            await this.cargarGeo();
        },

        async cargarTipos() {
            try {
                const r = await fetch('/aratio/api/territorios.php?accion=tipos_territorio&departamento=VALLE DEL CAUCA&municipio=CALI');
                const d = await r.json();
                if (d.success) this.geoOpciones.tipos = d.data;
            } catch (e) { console.error(e); }
        },

        async cargarGeo() {
            this.geoLoading = true;
            this.geoDetalleActivo = false;
            try {
                if (!this.geoCacheGeoJSON) {
                    const geoRes = await fetch('/aratio/api_territorios_geojson.php?municipio=CALI');
                    this.geoCacheGeoJSON = await geoRes.json();
                }
                let filtered = this.geoCacheGeoJSON;
                if (this.geoFiltro.tipo || this.geoFiltro.territorio) {
                    const features = (this.geoCacheGeoJSON.features || []).filter(f => {
                        const p = f.properties || {};
                        if (this.geoFiltro.tipo && p.Tipo_territorio !== this.geoFiltro.tipo) return false;
                        if (this.geoFiltro.territorio && p.Territorio !== this.geoFiltro.territorio) return false;
                        return true;
                    });
                    filtered = { ...this.geoCacheGeoJSON, features };
                }
                if (this.geoFiltro.tipo && this.geoOpciones.territorios.length === 0) {
                    const r = await fetch('/aratio/api/territorios.php?accion=territorios&departamento=VALLE DEL CAUCA&municipio=CALI&tipo_territorio=' + encodeURIComponent(this.geoFiltro.tipo));
                    const d = await r.json();
                    if (d.success) this.geoOpciones.territorios = d.data;
                }
                const countRes = await fetch('/aratio/api/reporte_geo_colaboradores.php?campana_id=<?= $campanaId ?>&municipio=CALI' + (this.geoFiltro.tipo ? '&tipo_territorio=' + encodeURIComponent(this.geoFiltro.tipo) : '') + (this.geoFiltro.territorio ? '&territorio=' + encodeURIComponent(this.geoFiltro.territorio) : ''));
                const countData = await countRes.json();
                if (countData.success) this.geoMapaConteos = countData.mapa_conteos || {};

                this.renderizarPoligonos(filtered);
            } catch (e) { console.error(e); }
            finally { this.geoLoading = false; }
        },

        renderizarPoligonos(geoData) {
            if (!this.geoLayer || !this.geoMapa) return;
            this.geoLayer.eachLayer(l => { if (l.unbindTooltip) l.unbindTooltip(); if (l.unbindPopup) l.unbindPopup(); });
            this.geoMapa.removeLayer(this.geoLayer);
            this.geoLayer = L.layerGroup().addTo(this.geoMapa);
            if (!geoData.features || geoData.features.length === 0) return;
            const self = this;
            const layer = L.geoJSON(geoData, {
                style(feature) { return self.calcularEstilo(feature); },
                onEachFeature(feature, leafletLayer) { self.onEachFeature(feature, leafletLayer); }
            });
            this.geoLayer.addLayer(layer);
            try { this.geoMapa.fitBounds(layer.getBounds(), { padding: [20, 20], animate: false }); } catch(e) {}
        },

        calcularEstilo(feature) {
            const tid = feature.id || feature.properties?.id;
            const conteo = tid ? (this.geoMapaConteos[tid] || null) : null;
            const total = conteo ? conteo.total : 0;
            if (total === 0) return { fillColor: 'transparent', fillOpacity: 0, weight: 0.5, opacity: 0.3, color: '#d1d5db' };
            const color = total >= 15 ? '#7c3aed' : total >= 6 ? '#d946ef' : total > 0 ? '#f472b6' : 'transparent';
            return { fillColor: color, fillOpacity: Math.min(0.25 + (total / 15) * 0.5, 0.75), weight: 1.5, opacity: 0.8, color: '#ffffff' };
        },

        onEachFeature(feature, leafletLayer) {
            const self = this;
            const props = feature.properties || {};
            const id = props.id || feature.id;
            const conteo = this.geoMapaConteos[id] || null;
            const total = conteo ? conteo.total : 0;
            const lideres = conteo ? conteo.lideres : 0;
            const nombre = props.barrio || props.Territorio || 'Sin nombre';
            leafletLayer.bindTooltip(nombre, { direction: 'center', className: 'polygon-label' });
            leafletLayer.bindPopup(`
                <div style="font-family:system-ui,sans-serif;min-width:200px;">
                    <div style="font-weight:700;font-size:14px;border-bottom:1px solid #e5e7eb;padding-bottom:6px;margin-bottom:6px;">${nombre}</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;font-size:13px;">
                        <div>👥 <strong>${total}</strong> colaboradores</div>
                        <div>👑 <strong>${lideres}</strong> líderes</div>
                    </div>
                    <button id="btn-detalle-${id}" style="margin-top:8px;width:100%;padding:6px;background:#1e3a5f;color:white;border:none;border-radius:6px;font-size:12px;cursor:pointer;">Ver detalle →</button>
                </div>
            `);
            leafletLayer.on('popupopen', () => {
                setTimeout(() => {
                    const btn = document.getElementById('btn-detalle-' + id);
                    if (btn) btn.onclick = () => self.abrirDetalle(props, conteo);
                }, 50);
            });
        },

        async abrirDetalle(props, conteo) {
            const nombre = props.barrio || props.Territorio || 'Sin nombre';
            const sector = props.Territorio || '';
            const params = new URLSearchParams({ campana_id: '<?= $campanaId ?>', municipio: 'CALI' });
            if (props.Tipo_territorio) params.append('tipo_territorio', props.Tipo_territorio);
            if (sector) params.append('territorio', sector);
            if (props.barrio) params.append('barrio', props.barrio);
            try {
                const r = await fetch('/aratio/api/reporte_geo_colaboradores.php?' + params.toString());
                const d = await r.json();
                const lista = d.lista_colaboradores || [];
                this.geoDetalle = { nombre, colaboradores: lista };
                this.geoDetalleActivo = true;
            } catch(e) { console.error(e); }
        },

        // ─── CHARTS ───
        renderCharts() {
            this.destroyCharts();
            if (!['general','eventos','compromisos'].includes(this.tab)) return;
            if (this.tab === 'general') {
                this.makeChart('chartPerfiles', 'doughnut',
                    <?= json_encode(array_column($perfiles, 'perfil')) ?>,
                    <?= json_encode(array_column($perfiles, 'total')) ?>,
                    ['#1e3a5f','#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316','#6366f1']);
            }
            if (this.tab === 'eventos') {
                this.makeChart('chartEventosTipo', 'doughnut',
                    <?=json_encode(array_column($eventosTipo, 'tipo')) ?>,
                    <?=json_encode(array_column($eventosTipo, 'total')) ?>,
                    ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899']);
                this.makeChart('chartEventosAsistencia', 'bar',
                    <?=json_encode(array_column($eventosAsistencia, 'nombre')) ?>,
                    <?=json_encode(array_column($eventosAsistencia, 'asistentes_confirmados')) ?>,
                    ['#1e3a5f','#3b82f6','#10b981','#f59e0b','#8b5cf6']);
            }
            if (this.tab === 'compromisos') {
                this.makeChart('chartCompromisosEstado', 'doughnut',
                    <?=json_encode(array_column($compromisosEstado, 'estado')) ?>,
                    <?=json_encode(array_column($compromisosEstado, 'total')) ?>,
                    ['#64748b','#3b82f6','#10b981','#ef4444']);
            }
        },
        destroyCharts() {
            Object.values(this.charts).forEach(c => {
                if (c) {
                    try { c.destroy(); } catch(e) {}
                }
            });
            this.charts = {};
        },
        makeChart(id, type, labels, data, colors) {
            const el = document.getElementById(id);
            if (!el) return;
            try {
                const ctx = el.getContext('2d');
                if (!ctx) return;
            } catch(e) { return; }
            if (this.charts[id]) {
                try { this.charts[id].destroy(); } catch(e) {}
            }
            const isBar = type === 'bar';
            const isDoughnut = type === 'doughnut';
            this.charts[id] = new Chart(el, {
                type, data: { labels, datasets: [{ data, backgroundColor: isBar ? '#1e3a5f' : colors.slice(0, labels.length), borderWidth: 0, borderRadius: isBar ? 6 : 0, borderSkipped: false }] },
                options: {
                    responsive: true, maintainAspectRatio: true,
                    cutout: isDoughnut ? '75%' : undefined,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: isBar ? { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } } : {}
                }
            });
        }
    };
}
</script>
<?php
/**
 * Dashboard de Consulta Electoral - Heatmap
 */
require_once __DIR__ . '/../../../config/config.php';

// Obtener filtros
$anio = $_GET['anio'] ?? 2023;
$corporacion = $_GET['corporacion'] ?? '';
$municipio = $_GET['municipio'] ?? '';
$partido = $_GET['partido'] ?? '';
$candidato = $_GET['candidato'] ?? '';

$db = getDB();

// Obtener listas para filtros (Casca inicial)
$anios = $db->query("SELECT DISTINCT anio FROM mod_elecciones ORDER BY anio DESC")->fetchAll(PDO::FETCH_COLUMN);

$corporaciones = [];
if ($anio) {
    $stmt = $db->prepare("SELECT DISTINCT corporacion FROM mod_elecciones WHERE anio = ? ORDER BY corporacion ASC");
    $stmt->execute([$anio]);
    $corporaciones = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$municipios = [];
if ($anio && $corporacion) {
    $stmt = $db->prepare("SELECT DISTINCT municipio FROM mod_elecciones WHERE anio = ? AND corporacion = ? ORDER BY municipio ASC");
    $stmt->execute([$anio, $corporacion]);
    $municipios = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$partidos = [];
if ($anio && $corporacion && $municipio) {
    $stmt = $db->prepare("SELECT DISTINCT partido FROM mod_elecciones WHERE anio = ? AND corporacion = ? AND municipio = ? ORDER BY partido ASC");
    $stmt->execute([$anio, $corporacion, $municipio]);
    $partidos = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$candidatos = [];
if ($anio && $corporacion && $municipio && $partido) {
    $stmt = $db->prepare("SELECT DISTINCT candidato FROM mod_elecciones WHERE anio = ? AND corporacion = ? AND municipio = ? AND partido = ? ORDER BY candidato ASC");
    $stmt->execute([$anio, $corporacion, $municipio, $partido]);
    $candidatos = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Consulta para el heatmap
$data = [];
$vertTotals = [];
$grandTotal = 0;
if ($candidato && $corporacion && $municipio) {
    $sql = "SELECT puesto, mesa, votos 
            FROM mod_elecciones 
            WHERE anio = ? AND candidato = ? AND corporacion = ? AND municipio = ?
            ORDER BY puesto ASC, mesa ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$anio, $candidato, $corporacion, $municipio]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organizar datos para la matriz y calcular totales por puesto
    foreach ($results as $row) {
        $p = $row['puesto'];
        $v = (int)$row['votos'];
        $data[$row['mesa']][$p] = $v;
        
        if (!isset($vertTotals[$p])) $vertTotals[$p] = 0;
        $vertTotals[$p] += $v;
        $grandTotal += $v;
    }
}

// Obtener todos los puestos únicos y ordenarlos por votos DESC
$puestos = array_keys($vertTotals);
arsort($vertTotals); // Ordenar totales de mayor a menor
$puestos = array_keys($vertTotals); // Reasignar $puestos en el nuevo orden

// Consulta para el gráfico de barras (Competencia en el municipio)
$municipioData = [];
if ($municipio && $corporacion) {
    $sql = "SELECT candidato, partido, SUM(votos) as total_votos 
            FROM mod_elecciones 
            WHERE anio = ? AND municipio = ? AND corporacion = ? 
            GROUP BY candidato, partido 
            ORDER BY total_votos DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$anio, $municipio, $corporacion]);
    $municipioData = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta Electoral - Aratio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@0.321.0/font/lucide.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'aratio-blue': '<?= COLOR_PRIMARY ?>',
                        'aratio-aqua': '<?= COLOR_ACCENT ?>',
                        'aratio-gold': '<?= COLOR_SECONDARY ?>',
                        'aratio-primary': '<?= COLOR_PRIMARY ?>',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#f0f4f8] min-h-screen font-sans">
    <nav class="bg-aratio-blue shadow-lg border-b border-aratio-aqua/20 px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-black text-white flex items-center gap-2 tracking-tight">
                <i class="lucide-vote text-aratio-aqua"></i>
                CONSULTA ELECTORAL <span class="text-aratio-aqua opacity-50 font-light">|</span> <span class="text-aratio-gold">ARATIO</span>
            </h1>
            <a href="<?= url('/') ?>" class="text-sm font-bold text-aratio-aqua hover:text-white transition-all flex items-center gap-2">
                <i class="lucide-external-link w-4 h-4"></i>
                Volver al Panel
            </a>
        </div>
    </nav>

    <main class="p-6">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <main class="p-6">
        <!-- Filtros y Gráfico de Municipio -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Filtros (2/3) -->
            <div class="lg:col-span-2 bg-white p-8 rounded-3xl shadow-xl shadow-aratio-blue/5 border border-white h-full relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-aratio-aqua/5 rounded-full -mr-16 -mt-16"></div>
                <form id="filterForm" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6 h-full relative z-10">
                    <input type="hidden" name="page" value="elecciones">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-aratio-blue/40 uppercase tracking-widest mb-2">Año Fiscal</label>
                            <select name="anio" id="filter_anio" class="w-full border-gray-100 rounded-2xl p-4 bg-gray-50/50 focus:ring-4 focus:ring-aratio-aqua/20 focus:border-aratio-aqua focus:bg-white transition-all text-sm font-bold text-aratio-blue">
                                <?php foreach ($anios as $a): ?>
                                    <option value="<?= $a ?>" <?= $anio == $a ? 'selected' : '' ?>><?= $a ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-aratio-blue/40 uppercase tracking-widest mb-2">Corporación</label>
                            <select name="corporacion" id="filter_corporacion" class="w-full border-gray-100 rounded-2xl p-4 bg-gray-50/50 focus:ring-4 focus:ring-aratio-aqua/20 focus:border-aratio-aqua focus:bg-white transition-all text-sm font-bold text-aratio-blue">
                                <option value="">Seleccione Corporación</option>
                                <?php foreach ($corporaciones as $c): ?>
                                    <option value="<?= $c ?>" <?= $corporacion == $c ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-aratio-blue/40 uppercase tracking-widest mb-2">Jurisdicción</label>
                            <select name="municipio" id="filter_municipio" class="w-full border-gray-100 rounded-2xl p-4 bg-gray-50/50 focus:ring-4 focus:ring-aratio-aqua/20 focus:border-aratio-aqua focus:bg-white transition-all text-sm font-bold text-aratio-blue">
                                <option value="">Seleccione Municipio</option>
                                <?php foreach ($municipios as $m): ?>
                                    <option value="<?= $m ?>" <?= $municipio == $m ? 'selected' : '' ?>><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-aratio-blue/40 uppercase tracking-widest mb-2">Aval Político</label>
                            <select name="partido" id="filter_partido" class="w-full border-gray-100 rounded-2xl p-4 bg-gray-50/50 focus:ring-4 focus:ring-aratio-aqua/20 focus:border-aratio-aqua focus:bg-white transition-all text-sm font-bold text-aratio-blue">
                                <option value="">Seleccione Partido</option>
                                <?php foreach ($partidos as $p): ?>
                                    <option value="<?= $p ?>" <?= $partido == $p ? 'selected' : '' ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col justify-between">
                        <div>
                            <label class="block text-xs font-black text-aratio-blue/40 uppercase tracking-widest mb-2">Candidatura</label>
                            <select name="candidato" id="filter_candidato" class="w-full border-gray-100 rounded-2xl p-4 bg-gray-50/50 focus:ring-4 focus:ring-aratio-aqua/20 focus:border-aratio-aqua focus:bg-white transition-all text-sm font-bold text-aratio-blue">
                                <option value="">Seleccione Candidato</option>
                                <?php foreach ($candidatos as $c): ?>
                                    <option value="<?= $c ?>" <?= $candidato == $c ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="pt-4">
                            <button type="submit" class="w-full bg-aratio-blue text-white px-4 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-aratio-blue/90 hover:shadow-2xl hover:shadow-aratio-blue/20 transition-all transform active:scale-95 flex items-center justify-center gap-3 border-b-4 border-black/20">
                                <i class="lucide-zap w-5 h-5 text-aratio-gold"></i>
                                PROYECTAR
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Gráfico Comparativo Municipio (1/3) -->
            <div class="bg-aratio-blue p-8 rounded-3xl shadow-xl shadow-aratio-blue/20 border-t border-aratio-aqua/20 flex flex-col min-h-[300px] group relative overflow-hidden">
                <div class="absolute bottom-0 left-0 w-full h-1 bg-aratio-gold"></div>
                <?php if (!empty($municipioData)): ?>
                    <div class="w-full h-full flex flex-col relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-aratio-aqua/10 flex items-center justify-center border border-aratio-aqua/20">
                                    <i class="lucide-bar-chart-3 text-aratio-aqua w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-[10px] font-black text-aratio-aqua/40 uppercase tracking-[0.2em]">Competencia</h3>
                                    <p class="text-xs font-bold text-white uppercase italic">Análisis Local</p>
                                </div>
                            </div>
                            <button onclick="openChartModal()" class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 text-white flex items-center justify-center hover:bg-aratio-gold hover:text-aratio-blue transition-all duration-300">
                                <i class="lucide-maximize-2 w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="flex-1 relative">
                            <canvas id="municipioChart"></canvas>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center text-white/20 flex-1 flex flex-col items-center justify-center space-y-4">
                        <div class="w-16 h-16 rounded-3xl bg-white/5 flex items-center justify-center border border-white/5 animate-pulse">
                            <i class="lucide-map-pin w-8 h-8 opacity-40"></i>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em]">Esperando Datos</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($candidato && !empty($data)): ?>
            <div class="bg-white p-10 rounded-[3rem] shadow-2xl shadow-aratio-blue/5 border border-white overflow-hidden relative">
                <div class="absolute top-0 right-0 p-8">
                    <div class="bg-aratio-aqua/10 text-aratio-blue px-6 py-2 rounded-2xl font-black border border-aratio-aqua/20 flex items-center gap-2">
                        <i class="lucide-award text-aratio-gold"></i>
                        TOTAL: <?= number_format($grandTotal) ?> VOTOS
                    </div>
                </div>

                <div class="mb-10">
                    <h2 class="text-3xl font-black text-aratio-blue leading-tight uppercase tracking-tighter">Proyección Territorial</h2>
                    <p class="text-aratio-blue/40 font-bold text-sm tracking-widest uppercase mt-1">
                        <span class="text-aratio-gold"><?= $candidato ?></span> 
                        <span class="mx-2 opacity-20">/</span> 
                        <?= $partido ?> 
                        <span class="mx-2 opacity-20">/</span> 
                        <?= $corporacion ?>
                    </p>
                </div>
                
                <div class="overflow-x-auto rounded-3xl border border-gray-100">
                    <table class="min-w-full border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-aratio-blue text-aratio-aqua text-[9px] uppercase font-black tracking-[0.2em]">
                                <th class="p-5 text-center sticky left-0 bg-aratio-blue z-10 w-20 border-r border-white/5">Mesa</th>
                                <?php foreach ($puestos as $p): ?>
                                    <th class="p-5 min-w-[140px] border-l border-white/5 text-center"><?= $p ?></th>
                                <?php endforeach; ?>
                                <th class="p-5 bg-aratio-gold text-aratio-blue font-black w-28 text-center italic">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php 
                            ksort($data);
                            foreach ($data as $mesa => $puestoVotos): 
                                $horizTotal = 0;
                            ?>
                                <tr class="group hover:bg-aratio-aqua/5 transition-all">
                                    <td class="p-5 bg-gray-50 group-hover:bg-aratio-blue group-hover:text-white font-black text-center sticky left-0 border-r border-gray-100 z-10 text-aratio-blue transition-all"><?= $mesa ?></td>
                                    <?php foreach ($puestos as $p): 
                                        $v = $puestoVotos[$p] ?? 0;
                                        $horizTotal += $v;
                                        
                                        // Gold Heatmap Logic
                                        $bg = "background-color: transparent";
                                        $textColor = "text-gray-300 font-light opacity-50";
                                        
                                        if ($v > 0) {
                                            $textColor = "text-aratio-blue";
                                            if ($v >= 31) {
                                                $bg = "background-color: #D4AF37"; // Solid Gold
                                                $textColor = "text-white";
                                            } elseif ($v >= 11) {
                                                $bg = "background-color: #E6BE8A"; // Mid Gold
                                                $textColor = "text-white";
                                            } else {
                                                $bg = "background-color: #F9F295"; // Pale Gold
                                            }
                                        }
                                    ?>
                                        <td class="border-l border-gray-100 p-5 text-center text-sm font-black <?= $textColor ?> transition-all relative" style="<?= $bg ?>">
                                            <?= $v ?: '·' ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="border-l border-gray-100 p-5 bg-gray-50 group-hover:bg-aratio-aqua/10 font-black text-center text-aratio-blue italic"><?= number_format($horizTotal) ?></td>
                                </tr>
                            <?php 
                            endforeach; 
                            ?>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr class="text-[10px] text-aratio-blue font-black border-t-2 border-aratio-blue/10">
                                <td class="p-6 text-center uppercase tracking-widest opacity-40">Gran Total</td>
                                <?php foreach ($puestos as $p): ?>
                                    <td class="border-l border-gray-100 p-6 text-center"><?= number_format($vertTotals[$p]) ?></td>
                                <?php endforeach; ?>
                                <td class="border-l border-gray-100 p-6 text-center bg-aratio-gold text-white text-sm"><?= number_format($grandTotal) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modal para Gráfico Ampliado -->
    <div id="chartModal" class="hidden fixed inset-0 z-[100] bg-aratio-blue/95 backdrop-blur-xl flex items-center justify-center p-6 sm:p-12 transition-all duration-500 opacity-0">
        <div class="bg-white w-full max-w-6xl h-full max-h-[80vh] rounded-[3rem] shadow-2xl relative flex flex-col p-10 transform scale-95 transition-all duration-500">
            <button onclick="closeChartModal()" class="absolute top-8 right-8 w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center hover:bg-aratio-gold hover:text-white transition-all">
                <i class="lucide-x w-6 h-6"></i>
            </button>
            <div class="mb-8">
                <h3 class="text-3xl font-black text-aratio-blue uppercase tracking-tighter">Detalle de Competencia <span class="text-aratio-gold">VOTOS</span></h3>
                <p class="text-gray-400 font-bold uppercase tracking-widest text-xs mt-1">Análisis Comparativo por Municipio y Corporación</p>
            </div>
            <div class="flex-1 relative">
                <canvas id="municipioChartModal"></canvas>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const partyColors = {
                'PARTIDO LIBERAL COLOMBIANO': '#EF4444',
                'PARTIDO CONSERVADOR COLOMBIANO': '#3B82F6',
                'PARTIDO CAMBIO RADICAL': '#1E3A8A',
                'PARTIDO CENTRO DEMOCRÁTICO': '#F59E0B',
                'PARTIDO ALIANZA VERDE': '#10B981',
                'MOVIMIENTO AUTORIDADES INDÍGENAS DE COLOMBIA "AICO"': '#059669',
                'PARTIDO DE LA UNIÓN POR LA GENTE "PARTIDO DE LA U"': '#FCD34D',
                'PARTIDO POLÍTICO MIRA': '#4338CA',
                'PARTIDO POLÍTICO DIGNIDAD & COMPROMISO': '#EC4899',
                'MOVIMIENTO ALTERNATIVO INDÍGENA Y SOCIAL "MAIS"': '#047857',
                'PACTO HISTÓRICO': '#7F1D1D',
            };

            function getPartyColor(party) {
                if (!party) return '#E5E7EB';
                const p = party.toUpperCase();
                return partyColors[p] || '#64748B'; // Default gray for unknown
            }

            let mainChart = null;
            let modalChart = null;

            const chartData = {
                labels: <?= json_encode(array_column($municipioData, 'candidato')) ?>,
                datasets: [{
                    label: 'Votos',
                    data: <?= json_encode(array_column($municipioData, 'total_votos')) ?>,
                    backgroundColor: <?= json_encode(array_map(function($row) use ($candidato) {
                        // We will handle specific candidate selection in JS for dynamic feedback
                        return ''; 
                    }, $municipioData)) ?>,
                    borderRadius: 8
                }]
            };

            // Calculate colors in JS to handle selection
            const parties = <?= json_encode(array_column($municipioData, 'partido')) ?>;
            const fullColors = parties.map(p => getPartyColor(p));
            
            <?php if (!empty($municipioData)): ?>
            const ctxMun = document.getElementById('municipioChart').getContext('2d');
            mainChart = new Chart(ctxMun, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        ...chartData.datasets[0],
                        backgroundColor: fullColors.map((c, i) => {
                            return chartData.labels[i] === <?= json_encode($candidato) ?> ? '#D4AF37' : c + '44'; // 44 is alpha for 25% opacity
                        }),
                        borderColor: fullColors.map((c, i) => {
                            return chartData.labels[i] === <?= json_encode($candidato) ?> ? '#D4AF37' : c;
                        }),
                        borderWidth: 2
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#003366',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 12,
                            cornerRadius: 12,
                            callbacks: {
                                label: (item) => ` ${item.raw.toLocaleString()} votos (${parties[item.dataIndex]})`
                            }
                        }
                    },
                    scales: {
                        x: { display: false, grid: { display: false } },
                        y: { 
                            grid: { display: false },
                            ticks: { 
                                font: { size: 9, weight: 'bold' },
                                color: (ctx) => chartData.labels[ctx.index] === <?= json_encode($candidato) ?> ? '#D4AF37' : '#94a3b8'
                            }
                        }
                    }
                }
            });

            window.openChartModal = function() {
                const modal = document.getElementById('chartModal');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.add('opacity-100');
                    modal.querySelector('div').classList.remove('scale-95');
                }, 10);

                const ctxModal = document.getElementById('municipioChartModal').getContext('2d');
                if (modalChart) modalChart.destroy();
                
                modalChart = new Chart(ctxModal, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            ...chartData.datasets[0],
                            backgroundColor: fullColors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        indexAxis: 'x', // Change to vertical for the modal
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#003366',
                                padding: 20,
                                cornerRadius: 15,
                                callbacks: {
                                    label: (item) => ` ${item.raw.toLocaleString()} Votos | ${parties[item.dataIndex]}`
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                            x: { grid: { display: false }, ticks: { font: { weight: 'bold', size: 11 } } }
                        }
                    }
                });
            };

            window.closeChartModal = function() {
                const modal = document.getElementById('chartModal');
                modal.classList.remove('opacity-100');
                modal.querySelector('div').classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 500);
            };
            <?php endif; ?>

            const apiBase = '<?= url('mod_elecciones/get_filters.php') ?>';
            
            async function updateDropdown(targetId, type, params) {
                const target = document.getElementById(targetId);
                target.innerHTML = '<option value="">Cargando...</option>';
                target.disabled = true;

                try {
                    const query = new URLSearchParams({ type, ...params }).toString();
                    const response = await fetch(`${apiBase}?${query}`);
                    const json = await response.json();

                    if (json.success) {
                        target.innerHTML = `<option value="">Seleccione ${type.charAt(0).toUpperCase() + type.slice(1, -1)}</option>`;
                        json.data.forEach(val => {
                            const opt = document.createElement('option');
                            opt.value = val;
                            opt.textContent = val;
                            target.appendChild(opt);
                        });
                        target.disabled = false;
                    }
                } catch (e) {
                    console.error('Error loading filters:', e);
                    target.innerHTML = '<option value="">Error al cargar</option>';
                }
            }

            const anioSel = document.getElementById('filter_anio');
            const corpSel = document.getElementById('filter_corporacion');
            const muniSel = document.getElementById('filter_municipio');
            const partSel = document.getElementById('filter_partido');
            const candSel = document.getElementById('filter_candidato');

            anioSel.addEventListener('change', () => {
                updateDropdown('filter_corporacion', 'corporaciones', { anio: anioSel.value });
                muniSel.innerHTML = '<option value="">Seleccione Municipio</option>';
                partSel.innerHTML = '<option value="">Seleccione Partido</option>';
                candSel.innerHTML = '<option value="">Seleccione Candidato</option>';
            });

            corpSel.addEventListener('change', () => {
                updateDropdown('filter_municipio', 'municipios', { anio: anioSel.value, corporacion: corpSel.value });
                partSel.innerHTML = '<option value="">Seleccione Partido</option>';
                candSel.innerHTML = '<option value="">Seleccione Candidato</option>';
            });

            muniSel.addEventListener('change', () => {
                updateDropdown('filter_partido', 'partidos', { anio: anioSel.value, corporacion: corpSel.value, municipio: muniSel.value });
                candSel.innerHTML = '<option value="">Seleccione Candidato</option>';
            });

            partSel.addEventListener('change', () => {
                updateDropdown('filter_candidato', 'candidatos', { 
                    anio: anioSel.value, 
                    corporacion: corpSel.value, 
                    municipio: muniSel.value,
                    partido: partSel.value 
                });
            });
        });
    </script>
</body>
</html>

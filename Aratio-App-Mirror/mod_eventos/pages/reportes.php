<?php
$db = getDB();
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_eventos,
        SUM(asistentes_confirmados) as total_asistentes,
        SUM(asistentes_esperados) as total_esperados,
        SUM(CASE WHEN estado = 'finalizado' THEN 1 ELSE 0 END) as finalizados,
        SUM(CASE WHEN estado = 'programado' THEN 1 ELSE 0 END) as programados,
        SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
        ROUND(AVG(asistentes_confirmados)) as promedio_asistentes,
        ROUND(SUM(asistentes_confirmados) * 100.0 / NULLIF(SUM(asistentes_esperados), 0), 1) as tasa_ocupacion
    FROM eventos WHERE campana_id = ?
");
$stmt->execute([$campanaId]);
$reporte = $stmt->fetch();
$apiBase = 'mod_eventos/api';
$chartColors = ['#E6007E', '#5B2A86', '#F9B000', '#41B853', '#3B82F6', '#F97316', '#8B5CF6', '#EC4899', '#14B8A6', '#F43F5E'];
?>
<div class="space-y-6" x-data="reportesData()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold" style="color: #5B2A86;">Reportes</h1>
            <p class="text-gray-600 mt-2">Estadísticas generales de eventos y asistentes</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 rounded-xl p-1" style="background: #F3F0FF; border: 1px solid rgba(91,42,134,0.1);">
        <button @click="tab = 'general'" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium transition-all"
                :style="tab === 'general' ? 'background: white; color: #5B2A86; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #6B7280;'">
            <i data-lucide="bar-chart-3" class="w-4 h-4 inline mr-1.5"></i> General
        </button>
        <button @click="tab = 'consolidado'; cargarConsolidado()" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium transition-all"
                :style="tab === 'consolidado' ? 'background: white; color: #5B2A86; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #6B7280;'">
            <i data-lucide="pie-chart" class="w-4 h-4 inline mr-1.5"></i> Consolidado
        </button>
    </div>

    <!-- Tab General -->
    <div x-show="tab === 'general'">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl p-6 border border-gray-100 bg-white shadow-sm">
                <p class="text-3xl font-bold" style="color: #E6007E;"><?= number_format($reporte['total_eventos'] ?? 0) ?></p>
                <p class="text-xs font-medium uppercase tracking-wider mt-1" style="color: #9CA3AF;">Total Eventos</p>
            </div>
            <div class="rounded-2xl p-6 border border-gray-100 bg-white shadow-sm">
                <p class="text-3xl font-bold" style="color: #5B2A86;"><?= number_format($reporte['total_asistentes'] ?? 0) ?></p>
                <p class="text-xs font-medium uppercase tracking-wider mt-1" style="color: #9CA3AF;">Total Asistentes</p>
            </div>
            <div class="rounded-2xl p-6 border border-gray-100 bg-white shadow-sm">
                <p class="text-3xl font-bold" style="color: #41B853;"><?= number_format($reporte['finalizados'] ?? 0) ?></p>
                <p class="text-xs font-medium uppercase tracking-wider mt-1" style="color: #9CA3AF;">Finalizados</p>
            </div>
            <div class="rounded-2xl p-6 border border-gray-100 bg-white shadow-sm">
                <p class="text-3xl font-bold" style="color: #F9B000;"><?= $reporte['tasa_ocupacion'] ?? 0 ?>%</p>
                <p class="text-xs font-medium uppercase tracking-wider mt-1" style="color: #9CA3AF;">Tasa Ocupación</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="rounded-2xl p-6 border border-gray-100 bg-white shadow-sm">
                <h3 class="font-bold mb-4" style="color: #5B2A86;">Distribución por Estado</h3>
                <div class="space-y-3">
                    <?php
                    $estados = [
                        ['label' => 'Programados', 'count' => $reporte['programados'] ?? 0, 'color' => '#5B2A86'],
                        ['label' => 'Finalizados', 'count' => $reporte['finalizados'] ?? 0, 'color' => '#41B853'],
                        ['label' => 'Cancelados', 'count' => $reporte['cancelados'] ?? 0, 'color' => '#dc2626'],
                    ];
                    $total = max($reporte['total_eventos'] ?? 1, 1);
                    foreach ($estados as $est):
                        $pct = round(($est['count'] / $total) * 100);
                    ?>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span style="color: #4B5563;"><?= $est['label'] ?></span>
                            <span class="font-semibold" style="color: #2F2F35;"><?= $est['count'] ?> (<?= $pct ?>%)</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-gray-100">
                            <div class="h-2 rounded-full transition-all" style="width: <?= $pct ?>%; background: <?= $est['color'] ?>;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="rounded-2xl p-6 border border-gray-100 bg-white shadow-sm">
                <h3 class="font-bold mb-4" style="color: #5B2A86;">Resumen General</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm" style="color: #4B5563;">Promedio asistentes por evento</span>
                        <span class="font-bold" style="color: #2F2F35;"><?= number_format($reporte['promedio_asistentes'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm" style="color: #4B5563;">Total esperados</span>
                        <span class="font-bold" style="color: #2F2F35;"><?= number_format($reporte['total_esperados'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm" style="color: #4B5563;">Eventos programados próximos</span>
                        <span class="font-bold" style="color: #2F2F35;"><?= number_format($reporte['programados'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Consolidado -->
    <div x-show="tab === 'consolidado'" x-cloak>
        <div x-show="!consolidadoCargado" class="text-center py-12" style="color: #9CA3AF;">
            <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-2 animate-spin"></i>
            <p class="text-sm">Cargando datos consolidados...</p>
        </div>

        <div x-show="consolidadoCargado">
            <!-- KPIs -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="rounded-2xl p-5 border border-gray-100 bg-white shadow-sm text-center">
                    <p class="text-2xl font-bold" style="color: #5B2A86;" x-text="consolidado.totales?.barrios_con_asistencia || 0"></p>
                    <p class="text-xs font-medium uppercase tracking-wider mt-1" style="color: #9CA3AF;">Barrios</p>
                </div>
                <div class="rounded-2xl p-5 border border-gray-100 bg-white shadow-sm text-center">
                    <p class="text-2xl font-bold" style="color: #E6007E;" x-text="consolidado.totales?.municipios_con_asistencia || 0"></p>
                    <p class="text-xs font-medium uppercase tracking-wider mt-1" style="color: #9CA3AF;">Municipios</p>
                </div>
                <div class="rounded-2xl p-5 border border-gray-100 bg-white shadow-sm text-center">
                    <p class="text-2xl font-bold" style="color: #F9B000;" x-text="consolidado.totales?.eventos_con_asistencia || 0"></p>
                    <p class="text-xs font-medium uppercase tracking-wider mt-1" style="color: #9CA3AF;">Eventos con asistencia</p>
                </div>
                <div class="rounded-2xl p-5 border border-gray-100 bg-white shadow-sm text-center">
                    <p class="text-2xl font-bold" style="color: #41B853;" x-text="consolidado.totales?.total_asistencias || 0"></p>
                    <p class="text-xs font-medium uppercase tracking-wider mt-1" style="color: #9CA3AF;">Total asistencias</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="rounded-2xl p-6 border border-gray-100 bg-white shadow-sm">
                    <h3 class="font-bold mb-4 flex items-center gap-2" style="color: #5B2A86;">
                        <i data-lucide="map-pin" class="w-4 h-4" style="color: #E6007E;"></i>
                        Top 10 Barrios por Asistentes
                    </h3>
                    <div class="h-72"><canvas id="chartBarrios"></canvas></div>
                </div>

                <div class="rounded-2xl p-6 border border-gray-100 bg-white shadow-sm">
                    <h3 class="font-bold mb-4 flex items-center gap-2" style="color: #5B2A86;">
                        <i data-lucide="globe" class="w-4 h-4" style="color: #F9B000;"></i>
                        Asistentes por Municipio
                    </h3>
                    <div class="h-72"><canvas id="chartMunicipios"></canvas></div>
                </div>

                <div class="rounded-2xl p-6 border border-gray-100 bg-white shadow-sm">
                    <h3 class="font-bold mb-4 flex items-center gap-2" style="color: #5B2A86;">
                        <i data-lucide="pie-chart" class="w-4 h-4" style="color: #41B853;"></i>
                        Eventos por Tipo
                    </h3>
                    <div class="h-72"><canvas id="chartTipo"></canvas></div>
                </div>

                <div class="rounded-2xl p-6 border border-gray-100 bg-white shadow-sm">
                    <h3 class="font-bold mb-4 flex items-center gap-2" style="color: #5B2A86;">
                        <i data-lucide="bar-chart-3" class="w-4 h-4" style="color: #5B2A86;"></i>
                        Eventos vs Asistentes por Municipio
                    </h3>
                    <div class="h-72"><canvas id="chartComparativo"></canvas></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    function reportesData() {
        return {
            tab: 'general',
            consolidado: null,
            consolidadoCargado: false,
            charts: {},

            async cargarConsolidado() {
                if (this.consolidadoCargado) return;
                try {
                    const res = await fetch(`/aratio/<?= $apiBase ?>/reportes.php?action=consolidado&campana_id=<?= $campanaId ?>`);
                    const json = await res.json();
                    if (json.success) {
                        this.consolidado = json.data;
                        this.consolidadoCargado = true;
                        this.$nextTick(() => this.renderCharts());
                    }
                } catch(e) { console.error('Error cargando consolidado:', e); }
            },

            renderCharts() {
                const colors = <?= json_encode($chartColors) ?>;
                const d = this.consolidado;

                // Limpiar charts previos
                Object.values(this.charts).forEach(c => c.destroy());
                this.charts = {};

                // Chart 1: Top 10 Barrios
                const barrios = (d.por_barrio || []).slice(0, 10);
                const labelsB = barrios.map(b => b.barrio?.substring(0, 18) || '');
                const dataB = barrios.map(b => parseInt(b.total_asistentes));
                this.charts.barrios = new Chart(document.getElementById('chartBarrios'), {
                    type: 'bar',
                    data: { labels: labelsB, datasets: [{ label: 'Asistentes', data: dataB, backgroundColor: colors[0], borderRadius: 6 }] },
                    options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: { x: { grid: { display: false } }, y: { grid: { display: false } } } }
                });

                // Chart 2: Municipios
                const muns = (d.por_municipio || []).slice(0, 10);
                const labelsM = muns.map(m => m.municipio?.substring(0, 20) || '');
                const dataM = muns.map(m => parseInt(m.total_asistentes));
                this.charts.municipios = new Chart(document.getElementById('chartMunicipios'), {
                    type: 'doughnut',
                    data: { labels: labelsM, datasets: [{ data: dataM, backgroundColor: colors }] },
                    options: { responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } } }
                });

                // Chart 3: Tipo de Evento
                const tipos = d.por_tipo || [];
                const labelsT = tipos.map(t => t.tipo?.charAt(0).toUpperCase() + t.tipo?.slice(1) || '');
                const dataTE = tipos.map(t => parseInt(t.total_eventos));
                this.charts.tipo = new Chart(document.getElementById('chartTipo'), {
                    type: 'pie',
                    data: { labels: labelsT, datasets: [{ data: dataTE, backgroundColor: colors }] },
                    options: { responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } } }
                });

                // Chart 4: Comparativo Municipios
                const muns2 = (d.por_municipio || []).slice(0, 8);
                const labelsC = muns2.map(m => m.municipio?.substring(0, 15) || '');
                const dataCE = muns2.map(m => parseInt(m.total_eventos));
                const dataCA = muns2.map(m => parseInt(m.total_asistentes));
                this.charts.comparativo = new Chart(document.getElementById('chartComparativo'), {
                    type: 'bar',
                    data: {
                        labels: labelsC,
                        datasets: [
                            { label: 'Eventos', data: dataCE, backgroundColor: colors[1], borderRadius: 4 },
                            { label: 'Asistentes', data: dataCA, backgroundColor: colors[0], borderRadius: 4 }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 10 } } } },
                        scales: { x: { grid: { display: false } }, y: { grid: { display: false } } } }
                });
            }
        }
    }
    lucide.createIcons();
</script>

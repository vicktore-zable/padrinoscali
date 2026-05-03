<?php
/**
 * Página: Reportes de Colaboradores
 * Dashboard con métricas y gráficos Chart.js
 */

$campanaId = $_SESSION['campana_activa'] ?? null;
$campanaName = $_SESSION['campana_nombre'] ?? 'Sin campaña';

if (!$campanaId) {
    echo '<div class="p-6"><div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
        <p>Seleccione una campaña para ver los reportes.</p>
    </div></div>';
    return;
}
?>

<div x-data="reportesColaboradores()" x-init="init()" class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <i data-lucide="bar-chart-3" class="w-7 h-7 mr-2 text-fuchsia-600"></i>
                Reportes de Colaboradores
            </h1>
            <p class="text-gray-600 mt-1">Análisis y métricas de <span class="font-medium"><?= htmlspecialchars($campanaName) ?></span></p>
        </div>

        <div class="flex gap-2 mt-4 md:mt-0">
            <a href="api/colaboradores.php?action=export&formato=csv&campana_id=<?= $campanaId ?>"
               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                Exportar CSV
            </a>
            <a href="index.php?page=colaboradores" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Volver
            </a>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center h-64">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-fuchsia-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Cargando datos...</p>
        </div>
    </div>

    <!-- Content -->
    <div x-show="!loading" class="space-y-6">
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-fuchsia-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Colaboradores</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.total"></p>
                    </div>
                    <div class="w-12 h-12 bg-fuchsia-100 rounded-full flex items-center justify-center">
                        <i data-lucide="users" class="w-6 h-6 text-fuchsia-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Nuevos</p>
                        <p class="text-2xl font-bold text-blue-600" x-text="stats.por_estado?.Nuevo || 0"></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i data-lucide="user-plus" class="w-6 h-6 text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Crecieron</p>
                        <p class="text-2xl font-bold text-green-600" x-text="stats.por_estado?.Crecio || 0"></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i data-lucide="trending-up" class="w-6 h-6 text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-gray-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Igual</p>
                        <p class="text-2xl font-bold text-gray-600" x-text="stats.por_estado?.Igual || 0"></p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                        <i data-lucide="minus" class="w-6 h-6 text-gray-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Decrecen</p>
                        <p class="text-2xl font-bold text-red-600" x-text="stats.por_estado?.Decrece || 0"></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i data-lucide="trending-down" class="w-6 h-6 text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center">
                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center mr-4">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i>
                </div>
                <div>
                    <p class="font-medium text-amber-800">Sin líder asignado</p>
                    <p class="text-sm text-amber-600"><span x-text="stats.colaboradores_sin_lider || 0"></span> colaboradores necesitan líder</p>
                </div>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                    <i data-lucide="user-x" class="w-5 h-5 text-purple-600"></i>
                </div>
                <div>
                    <p class="font-medium text-purple-800">Líderes sin seguidores</p>
                    <p class="text-sm text-purple-600"><span x-text="stats.lideres_sin_seguidores || 0"></span> líderes sin equipo</p>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Chart: Por Perfil -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <i data-lucide="pie-chart" class="w-5 h-5 mr-2 text-fuchsia-600"></i>
                    Distribución por Perfil
                </h3>
                <div class="h-64">
                    <canvas id="chartPerfil"></canvas>
                </div>
            </div>

            <!-- Chart: Por Estado -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <i data-lucide="activity" class="w-5 h-5 mr-2 text-fuchsia-600"></i>
                    Distribución por Estado
                </h3>
                <div class="h-64">
                    <canvas id="chartEstado"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Líderes -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <i data-lucide="trophy" class="w-5 h-5 mr-2 text-fuchsia-600"></i>
                    Top 10 Líderes
                </h3>
                <div x-show="topLideres.length === 0" class="text-center text-gray-500 py-8">
                    No hay datos de líderes disponibles
                </div>
                <div x-show="topLideres.length > 0" class="space-y-3">
                    <template x-for="(lider, idx) in topLideres.slice(0, 10)" :key="lider.id">
                        <div class="flex items-center">
                            <span class="w-6 text-sm font-medium text-gray-400" x-text="idx + 1"></span>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-fuchsia-500 to-amber-500 text-white flex items-center justify-center text-xs font-bold mr-3">
                                <span x-text="lider.nombres.charAt(0)"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate" x-text="lider.nombres + ' ' + lider.apellidos"></p>
                                <p class="text-xs text-gray-500" x-text="lider.municipio"></p>
                            </div>
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-fuchsia-600 h-2 rounded-full"
                                         :style="'width: ' + Math.min((lider.total_seguidores / (topLideres[0]?.total_seguidores || 1)) * 100, 100) + '%'"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-600 w-8 text-right" x-text="lider.total_seguidores"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Por Territorio -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <i data-lucide="map" class="w-5 h-5 mr-2 text-fuchsia-600"></i>
                    Top Municipios
                </h3>
                <div x-show="porTerritorio.length === 0" class="text-center text-gray-500 py-8">
                    No hay datos territoriales disponibles
                </div>
                <div x-show="porTerritorio.length > 0" class="space-y-3">
                    <template x-for="(mun, idx) in porTerritorio.slice(0, 10)" :key="idx">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800" x-text="mun.municipio"></p>
                                <p class="text-xs text-gray-500" x-text="mun.departamento"></p>
                            </div>
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-amber-500 h-2 rounded-full"
                                         :style="'width: ' + Math.min((mun.cantidad / (porTerritorio[0]?.cantidad || 1)) * 100, 100) + '%'"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-600 w-8 text-right" x-text="mun.cantidad"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function reportesColaboradores() {
    return {
        loading: true,
        campanaId: <?= json_encode($campanaId) ?>,
        stats: {},
        topLideres: [],
        porTerritorio: [],
        porPerfil: [],
        chartPerfil: null,
        chartEstado: null,

        async init() {
            await this.loadData();
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        },

        async loadData() {
            this.loading = true;
            try {
                // Cargar reportes en paralelo
                const [statsRes, lideresRes, territorioRes, perfilRes] = await Promise.all([
                    fetch(`api/colaboradores.php?action=reportes&tipo=general&campana_id=${this.campanaId}`),
                    fetch(`api/colaboradores.php?action=reportes&tipo=lideres&campana_id=${this.campanaId}`),
                    fetch(`api/colaboradores.php?action=reportes&tipo=territorio&campana_id=${this.campanaId}`),
                    fetch(`api/colaboradores.php?action=reportes&tipo=perfil&campana_id=${this.campanaId}`)
                ]);

                const stats = await statsRes.json();
                const lideres = await lideresRes.json();
                const territorio = await territorioRes.json();
                const perfil = await perfilRes.json();

                if (stats.success) this.stats = stats.data;
                if (lideres.success) this.topLideres = lideres.data;
                if (territorio.success) this.porTerritorio = territorio.data;
                if (perfil.success) this.porPerfil = perfil.data;

                // Renderizar gráficos
                this.$nextTick(() => {
                    this.renderCharts();
                });

            } catch (error) {
                console.error('Error cargando datos:', error);
            } finally {
                this.loading = false;
            }
        },

        renderCharts() {
            // Colores por perfil
            const perfilColors = {
                'Lider Comunitario': '#FF00FF',
                'Lider Social': '#3B82F6',
                'Lider Gremial': '#10B981',
                'Activista': '#F59E0B',
                'Simpatizante': '#6B7280'
            };

            // Chart: Por Perfil (Doughnut)
            const ctxPerfil = document.getElementById('chartPerfil');
            if (ctxPerfil && this.porPerfil.length > 0) {
                if (this.chartPerfil) this.chartPerfil.destroy();

                const labels = this.porPerfil.map(p => p.perfil);
                const data = this.porPerfil.map(p => p.cantidad);
                const colors = labels.map(l => perfilColors[l] || '#6B7280');

                this.chartPerfil = new Chart(ctxPerfil, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: colors,
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            }

            // Chart: Por Estado (Bar)
            const ctxEstado = document.getElementById('chartEstado');
            if (ctxEstado && this.stats.por_estado) {
                if (this.chartEstado) this.chartEstado.destroy();

                const estados = ['Nuevo', 'Crecio', 'Igual', 'Decrece'];
                const estadoColors = ['#3B82F6', '#10B981', '#6B7280', '#EF4444'];
                const data = estados.map(e => this.stats.por_estado[e] || 0);

                this.chartEstado = new Chart(ctxEstado, {
                    type: 'bar',
                    data: {
                        labels: estados,
                        datasets: [{
                            label: 'Colaboradores',
                            data: data,
                            backgroundColor: estadoColors,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    };
}
</script>

<?php
/**
 * MÓDULO: Reportes
 * Sistema completo de reportes con gráficos interactivos y datos reales
 */

if (!$campanaActiva) {
    echo '<div class="card text-center py-12"><i data-lucide="flag" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i><h2 class="text-xl font-bold text-gray-900 mb-2">Selecciona una campaña</h2></div>';
    return;
}

$db = getDB();
$campanaId = $campanaActiva['id'];

// ===== DATOS PARA REPORTES =====

// 1. Donaciones
$stmtDonaciones = $db->prepare("
    SELECT
        metodo_pago,
        COUNT(*) as cantidad,
        SUM(monto) as total
    FROM donaciones
    WHERE campana_id = ?
    GROUP BY metodo_pago
");
$stmtDonaciones->execute([$campanaId]);
$donacionesPorMetodo = $stmtDonaciones->fetchAll(PDO::FETCH_ASSOC);

// 2. Eventos
$stmtEventos = $db->prepare("
    SELECT
        tipo,
        COUNT(*) as cantidad
    FROM eventos
    WHERE campana_id = ?
    GROUP BY tipo
");
$stmtEventos->execute([$campanaId]);
$eventosPorTipo = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);

// 3. Acciones Comunitarias
$stmtAcciones = $db->prepare("
    SELECT
        tipo,
        COUNT(*) as cantidad,
        SUM(personas_contactadas) as contactadas,
        SUM(compromisos_obtenidos) as compromisos
    FROM acciones_comunitarias
    WHERE campana_id = ?
    GROUP BY tipo
");
$stmtAcciones->execute([$campanaId]);
$accionesPorTipo = $stmtAcciones->fetchAll(PDO::FETCH_ASSOC);

// 4. Compromisos
$stmtCompromisos = $db->prepare("
    SELECT
        estado,
        COUNT(*) as cantidad
    FROM compromisos
    WHERE campana_id = ?
    GROUP BY estado
");
$stmtCompromisos->execute([$campanaId]);
$compromisosPorEstado = $stmtCompromisos->fetchAll(PDO::FETCH_ASSOC);

// 5. Resumen general
$stmtGeneral = $db->prepare("
    SELECT
        (SELECT COUNT(*) FROM donaciones WHERE campana_id = ?) as total_donaciones,
        (SELECT COUNT(*) FROM eventos WHERE campana_id = ?) as total_eventos,
        (SELECT COUNT(*) FROM acciones_comunitarias WHERE campana_id = ?) as total_acciones,
        (SELECT COUNT(*) FROM compromisos WHERE campana_id = ?) as total_compromisos
");
$stmtGeneral->execute([$campanaId, $campanaId, $campanaId, $campanaId]);
$resumenGeneral = $stmtGeneral->fetch(PDO::FETCH_ASSOC);
?>

<div class="space-y-6" x-data="reportesData()">
    <div class="flex items-center justify-between">
        <div><h1 class="text-3xl font-bold text-gray-900">Reportes y Estadísticas</h1><p class="text-gray-600 mt-2"><?= htmlspecialchars($campanaActiva['nombre']) ?></p></div>
        <button @click="exportarTodo()" class="btn-primary"><i data-lucide="download" class="w-5 h-5 inline mr-2"></i>Exportar Todo</button>
    </div>

    <!-- Selector de reportes -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <button @click="reporteActual = 'donaciones'" :class="reporteActual === 'donaciones' ? 'bg-primary/10 border-primary' : 'bg-white hover:bg-gray-50'" class="p-4 rounded-xl border-2 text-center transition">
            <i data-lucide="dollar-sign" class="w-8 h-8 mx-auto mb-2" :class="reporteActual === 'donaciones' ? 'text-primary' : 'text-gray-400'"></i>
            <p class="text-sm font-medium">Donaciones</p>
        </button>
        <button @click="reporteActual = 'eventos'" :class="reporteActual === 'eventos' ? 'bg-primary/10 border-primary' : 'bg-white hover:bg-gray-50'" class="p-4 rounded-xl border-2 text-center transition">
            <i data-lucide="calendar" class="w-8 h-8 mx-auto mb-2" :class="reporteActual === 'eventos' ? 'text-primary' : 'text-gray-400'"></i>
            <p class="text-sm font-medium">Eventos</p>
        </button>
        <button @click="reporteActual = 'acciones'" :class="reporteActual === 'acciones' ? 'bg-primary/10 border-primary' : 'bg-white hover:bg-gray-50'" class="p-4 rounded-xl border-2 text-center transition">
            <i data-lucide="map-pin" class="w-8 h-8 mx-auto mb-2" :class="reporteActual === 'acciones' ? 'text-primary' : 'text-gray-400'"></i>
            <p class="text-sm font-medium">Acciones</p>
        </button>
        <button @click="reporteActual = 'compromisos'" :class="reporteActual === 'compromisos' ? 'bg-primary/10 border-primary' : 'bg-white hover:bg-gray-50'" class="p-4 rounded-xl border-2 text-center transition">
            <i data-lucide="handshake" class="w-8 h-8 mx-auto mb-2" :class="reporteActual === 'compromisos' ? 'text-primary' : 'text-gray-400'"></i>
            <p class="text-sm font-medium">Compromisos</p>
        </button>
        <button @click="reporteActual = 'territorial'" :class="reporteActual === 'territorial' ? 'bg-primary/10 border-primary' : 'bg-white hover:bg-gray-50'" class="p-4 rounded-xl border-2 text-center transition">
            <i data-lucide="map" class="w-8 h-8 mx-auto mb-2" :class="reporteActual === 'territorial' ? 'text-primary' : 'text-gray-400'"></i>
            <p class="text-sm font-medium">Territorial</p>
        </button>
        <button @click="reporteActual = 'general'" :class="reporteActual === 'general' ? 'bg-primary/10 border-primary' : 'bg-white hover:bg-gray-50'" class="p-4 rounded-xl border-2 text-center transition">
            <i data-lucide="bar-chart-3" class="w-8 h-8 mx-auto mb-2" :class="reporteActual === 'general' ? 'text-primary' : 'text-gray-400'"></i>
            <p class="text-sm font-medium">General</p>
        </button>
    </div>

    <!-- Contenido del reporte -->
    <div class="card">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold" x-text="getTituloReporte()"></h3>
            <button @click="exportarReporte()" class="btn-ghost"><i data-lucide="download" class="w-4 h-4 inline mr-2"></i>Exportar</button>
        </div>

        <!-- Reporte Donaciones -->
        <div x-show="reporteActual === 'donaciones'">
            <?php if (empty($donacionesPorMetodo)): ?>
                <p class="text-center text-gray-500 py-8">No hay donaciones registradas aún</p>
            <?php else: ?>
            <div class="grid grid-cols-1 gap-6">
                <div><h4 class="font-medium mb-4">Donaciones por Método de Pago</h4><canvas id="chartDonacionesMetodo" height="100"></canvas></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Reporte Eventos -->
        <div x-show="reporteActual === 'eventos'">
            <?php if (empty($eventosPorTipo)): ?>
                <p class="text-center text-gray-500 py-8">No hay eventos registrados aún</p>
            <?php else: ?>
            <div class="grid grid-cols-1 gap-6">
                <div><h4 class="font-medium mb-4">Eventos por Tipo</h4><canvas id="chartEventosTipo" height="100"></canvas></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Reporte Acciones -->
        <div x-show="reporteActual === 'acciones'">
            <?php if (empty($accionesPorTipo)): ?>
                <p class="text-center text-gray-500 py-8">No hay acciones comunitarias registradas aún</p>
            <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div><h4 class="font-medium mb-4">Acciones por Tipo</h4><canvas id="chartAccionesTipo" height="200"></canvas></div>
                <div><h4 class="font-medium mb-4">Efectividad</h4><canvas id="chartAccionesEfectividad" height="200"></canvas></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Reporte Compromisos -->
        <div x-show="reporteActual === 'compromisos'">
            <?php if (empty($compromisosPorEstado)): ?>
                <p class="text-center text-gray-500 py-8">No hay compromisos registrados aún</p>
            <?php else: ?>
            <div class="grid grid-cols-1 gap-6">
                <div><h4 class="font-medium mb-4">Compromisos por Estado</h4><canvas id="chartCompromisosEstado" height="100"></canvas></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Reporte Territorial -->
        <div x-show="reporteActual === 'territorial'">
            <div class="grid grid-cols-1 gap-6">
                <div><h4 class="font-medium mb-4">Mapa Territorial</h4><div id="mapaTerritorial" style="height: 400px; border-radius: 8px;"></div></div>
            </div>
        </div>

        <!-- Reporte General -->
        <div x-show="reporteActual === 'general'">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card">
                    <p class="text-3xl font-bold text-blue-600"><?= number_format($resumenGeneral['total_donaciones'] ?? 0) ?></p>
                    <p class="text-sm text-gray-600">Donaciones</p>
                </div>
                <div class="stat-card">
                    <p class="text-3xl font-bold text-green-600"><?= number_format($resumenGeneral['total_eventos'] ?? 0) ?></p>
                    <p class="text-sm text-gray-600">Eventos</p>
                </div>
                <div class="stat-card">
                    <p class="text-3xl font-bold text-purple-600"><?= number_format($resumenGeneral['total_acciones'] ?? 0) ?></p>
                    <p class="text-sm text-gray-600">Acciones</p>
                </div>
                <div class="stat-card">
                    <p class="text-3xl font-bold text-orange-600"><?= number_format($resumenGeneral['total_compromisos'] ?? 0) ?></p>
                    <p class="text-sm text-gray-600">Compromisos</p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-6">
                <div><h4 class="font-medium mb-4">Resumen de Actividades</h4><canvas id="chartGeneral" height="100"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script>
// Datos PHP convertidos a JavaScript
const datosReportes = {
    donaciones: <?= json_encode($donacionesPorMetodo) ?>,
    eventos: <?= json_encode($eventosPorTipo) ?>,
    acciones: <?= json_encode($accionesPorTipo) ?>,
    compromisos: <?= json_encode($compromisosPorEstado) ?>,
    general: <?= json_encode($resumenGeneral) ?>
};

function reportesData() {
    return {
        reporteActual: 'donaciones',
        charts: {},
        mapa: null,

        init() {
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
                general: 'Reporte General de Campaña'
            };
            return titulos[this.reporteActual] || 'Reporte';
        },

        renderCharts() {
            setTimeout(() => {
                if (this.reporteActual === 'donaciones') {
                    this.renderDonaciones();
                } else if (this.reporteActual === 'eventos') {
                    this.renderEventos();
                } else if (this.reporteActual === 'acciones') {
                    this.renderAcciones();
                } else if (this.reporteActual === 'compromisos') {
                    this.renderCompromisos();
                } else if (this.reporteActual === 'territorial') {
                    this.renderMapaTerritorial();
                } else if (this.reporteActual === 'general') {
                    this.renderGeneral();
                }
            }, 100);
        },

        renderDonaciones() {
            const metodos = datosReportes.donaciones;
            if (metodos.length === 0) return;

            const ctx = document.getElementById('chartDonacionesMetodo');
            if (ctx) {
                if (this.charts.donaciones) this.charts.donaciones.destroy();
                this.charts.donaciones = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: metodos.map(m => m.metodo_pago.charAt(0).toUpperCase() + m.metodo_pago.slice(1)),
                        datasets: [{
                            data: metodos.map(m => m.cantidad),
                            backgroundColor: ['#FF00FF', '#FFD700', '#00A859']
                        }]
                    },
                    options: {
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        },

        renderEventos() {
            const tipos = datosReportes.eventos;
            if (tipos.length === 0) return;

            const ctx = document.getElementById('chartEventosTipo');
            if (ctx) {
                if (this.charts.eventos) this.charts.eventos.destroy();
                this.charts.eventos = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: tipos.map(t => t.tipo.charAt(0).toUpperCase() + t.tipo.slice(1)),
                        datasets: [{
                            label: 'Cantidad',
                            data: tipos.map(t => t.cantidad),
                            backgroundColor: '#FF00FF'
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        },

        renderAcciones() {
            const tipos = datosReportes.acciones;
            if (tipos.length === 0) return;

            const ctx1 = document.getElementById('chartAccionesTipo');
            if (ctx1) {
                if (this.charts.accionesTipo) this.charts.accionesTipo.destroy();
                this.charts.accionesTipo = new Chart(ctx1, {
                    type: 'pie',
                    data: {
                        labels: tipos.map(t => t.tipo.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase())),
                        datasets: [{
                            data: tipos.map(t => t.cantidad),
                            backgroundColor: ['#FF00FF', '#FFD700', '#00A859', '#3B82F6', '#EF4444', '#8B5CF6', '#F59E0B']
                        }]
                    },
                    options: {
                        plugins: { legend: { position: 'right' } }
                    }
                });
            }

            const ctx2 = document.getElementById('chartAccionesEfectividad');
            if (ctx2) {
                if (this.charts.accionesEfectividad) this.charts.accionesEfectividad.destroy();
                this.charts.accionesEfectividad = new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: tipos.map(t => t.tipo.replace(/-/g, ' ')),
                        datasets: [
                            {
                                label: 'Personas Contactadas',
                                data: tipos.map(t => t.contactadas),
                                backgroundColor: '#3B82F6'
                            },
                            {
                                label: 'Compromisos',
                                data: tipos.map(t => t.compromisos),
                                backgroundColor: '#10B981'
                            }
                        ]
                    },
                    options: {
                        plugins: { legend: { position: 'top' } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        },

        renderCompromisos() {
            const estados = datosReportes.compromisos;
            if (estados.length === 0) return;

            const ctx = document.getElementById('chartCompromisosEstado');
            if (ctx) {
                if (this.charts.compromisos) this.charts.compromisos.destroy();
                this.charts.compromisos = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: estados.map(e => e.estado.charAt(0).toUpperCase() + e.estado.slice(1)),
                        datasets: [{
                            data: estados.map(e => e.cantidad),
                            backgroundColor: ['#F59E0B', '#3B82F6', '#10B981', '#EF4444']
                        }]
                    },
                    options: {
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        },

        renderMapaTerritorial() {
            if (!this.mapa) {
                this.mapa = L.map('mapaTerritorial').setView([4.570868, -74.297333], 6);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.mapa);
            }
        },

        renderGeneral() {
            const general = datosReportes.general;

            const ctx = document.getElementById('chartGeneral');
            if (ctx) {
                if (this.charts.general) this.charts.general.destroy();
                this.charts.general = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Donaciones', 'Eventos', 'Acciones', 'Compromisos'],
                        datasets: [{
                            label: 'Total',
                            data: [
                                general.total_donaciones || 0,
                                general.total_eventos || 0,
                                general.total_acciones || 0,
                                general.total_compromisos || 0
                            ],
                            backgroundColor: ['#FF00FF', '#FFD700', '#00A859', '#8B5CF6']
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
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

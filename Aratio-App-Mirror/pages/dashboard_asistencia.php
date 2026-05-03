<?php
/**
 * MÓDULO: Dashboard de Asistencia a Eventos
 * Dashboard con filtros avanzados por territorio, género, grupo etario y áreas de interés
 */

if (!$campanaActiva) {
    echo '<div class="card text-center py-12">
        <i data-lucide="flag" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Selecciona una campaña</h2>
        <p class="text-gray-600">Debes seleccionar una campaña para ver el dashboard de asistencia</p>
    </div>';
    return;
}

$db = getDB();
$campanaId = $campanaActiva['id'];

// Obtener eventos de la campaña
try {
    $stmt = $db->prepare("
        SELECT id, nombre, fecha_inicio, fecha_fin
        FROM eventos
        WHERE campana_id = ?
        ORDER BY fecha_inicio DESC
    ");
    $stmt->execute([$campanaId]);
    $eventos = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error loading eventos: " . $e->getMessage());
    $eventos = [];
}
?>

<div class="space-y-6" x-data="dashboardAsistenciaData()" x-init="init()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard de Asistencia</h1>
            <p class="text-gray-600 mt-2"><?= htmlspecialchars($campanaActiva['nombre']) ?></p>
        </div>
        <button @click="exportarDatos()" class="btn-primary">
            <i data-lucide="download" class="w-5 h-5 inline mr-2"></i>
            Exportar Datos
        </button>
    </div>

    <!-- Filtros Avanzados -->
    <div class="card">
        <h3 class="font-bold text-gray-900 mb-4">Filtros</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Evento -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Evento</label>
                <select x-model="filtros.evento_id" @change="cargarDatos()" class="input">
                    <option value="">Todos los eventos</option>
                    <?php foreach ($eventos as $ev): ?>
                        <option value="<?= $ev['id'] ?>">
                            <?= htmlspecialchars($ev['nombre']) ?>
                            (<?= formatDate($ev['fecha_inicio'], 'd/m/Y') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Departamento -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Departamento</label>
                <select x-model="filtros.departamento" @change="cargarMunicipiosFiltro(); cargarDatos()" class="input">
                    <option value="">Todos</option>
                    <template x-for="dep in listas.departamentos" :key="dep">
                        <option :value="dep" x-text="dep"></option>
                    </template>
                </select>
            </div>

            <!-- Municipio -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Municipio</label>
                <select x-model="filtros.municipio" @change="cargarDatos()" class="input" :disabled="!filtros.departamento">
                    <option value="">Todos</option>
                    <template x-for="mun in listas.municipios_filtro" :key="mun">
                        <option :value="mun" x-text="mun"></option>
                    </template>
                </select>
            </div>

            <!-- Barrio/Vereda -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Barrio/Vereda</label>
                <input type="text" x-model="filtros.barrio" @input="cargarDatos()"
                    placeholder="Buscar barrio..." class="input">
            </div>

            <!-- Género -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Género</label>
                <select x-model="filtros.genero" @change="cargarDatos()" class="input">
                    <option value="">Todos</option>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                    <option value="otro">Otro</option>
                    <option value="prefiero-no-decir">Prefiero no decir</option>
                </select>
            </div>

            <!-- Grupo Etario -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Grupo Etario</label>
                <select x-model="filtros.grupo_etareo" @change="cargarDatos()" class="input">
                    <option value="">Todos</option>
                    <option value="Menor de 18">Menor de 18</option>
                    <option value="18-25">18-25 años</option>
                    <option value="26-35">26-35 años</option>
                    <option value="36-45">36-45 años</option>
                    <option value="46-55">46-55 años</option>
                    <option value="56-65">56-65 años</option>
                    <option value="Mayor de 65">Mayor de 65</option>
                </select>
            </div>

            <!-- Área de Interés -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Área de Interés</label>
                <select x-model="filtros.area_interes" @change="cargarDatos()" class="input">
                    <option value="">Todas</option>
                    <option value="Educación">Educación</option>
                    <option value="Salud">Salud</option>
                    <option value="Seguridad">Seguridad</option>
                    <option value="Empleo">Empleo</option>
                    <option value="Infraestructura">Infraestructura</option>
                    <option value="Cultura">Cultura</option>
                    <option value="Deporte">Deporte</option>
                    <option value="Medio Ambiente">Medio Ambiente</option>
                    <option value="Vivienda">Vivienda</option>
                </select>
            </div>

            <!-- Habeas Data -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Habeas Data</label>
                <select x-model="filtros.habeas_data" @change="cargarDatos()" class="input">
                    <option value="">Todos</option>
                    <option value="1">Aceptó</option>
                    <option value="0">No aceptó</option>
                </select>
            </div>
        </div>

        <!-- Botón limpiar filtros -->
        <div class="mt-4">
            <button @click="limpiarFiltros()" class="btn-ghost text-sm">
                <i data-lucide="x-circle" class="w-4 h-4 inline mr-2"></i>
                Limpiar Filtros
            </button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1" x-text="stats.total || 0"></h3>
            <p class="text-sm text-gray-600">Total Asistentes</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i data-lucide="user" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1" x-text="stats.masculino || 0"></h3>
            <p class="text-sm text-gray-600">Masculino</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-pink-100 rounded-lg">
                    <i data-lucide="user" class="w-6 h-6 text-pink-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1" x-text="stats.femenino || 0"></h3>
            <p class="text-sm text-gray-600">Femenino</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1" x-text="stats.habeas_data || 0"></h3>
            <p class="text-sm text-gray-600">Habeas Data Aceptado</p>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Distribución por Género -->
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Distribución por Género</h3>
            <canvas id="chartGenero" height="200"></canvas>
        </div>

        <!-- Distribución por Grupo Etario -->
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Distribución por Grupo Etario</h3>
            <canvas id="chartGrupoEtareo" height="200"></canvas>
        </div>

        <!-- Áreas de Interés Más Populares -->
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Áreas de Interés Más Populares</h3>
            <canvas id="chartAreasInteres" height="200"></canvas>
        </div>

        <!-- Distribución Territorial -->
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Top 10 Municipios</h3>
            <canvas id="chartMunicipios" height="200"></canvas>
        </div>
    </div>

    <!-- Tabla de Datos -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Listado de Asistentes (<span x-text="asistentes.length"></span>)</h3>
            <input type="text" x-model="busqueda" @input="filtrarAsistentes()"
                placeholder="Buscar por nombre o documento..." class="input w-64">
        </div>

        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Documento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Género</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grupo Etario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Municipio</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barrio</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="asistente in asistentesFiltrados" :key="asistente.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900" x-text="asistente.nombre"></td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <span x-text="asistente.tipo_documento"></span>
                                <span x-text="asistente.documento"></span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 capitalize" x-text="asistente.genero"></td>
                            <td class="px-4 py-3 text-sm text-gray-600" x-text="asistente.grupo_etareo || '-'"></td>
                            <td class="px-4 py-3 text-sm text-gray-600" x-text="asistente.municipio"></td>
                            <td class="px-4 py-3 text-sm text-gray-600" x-text="asistente.barrio || '-'"></td>
                            <td class="px-4 py-3 text-sm text-gray-600"
                                x-text="new Date(asistente.fecha_registro).toLocaleDateString('es-CO')"></td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div x-show="asistentesFiltrados.length === 0" class="text-center py-12 text-gray-500">
                No hay registros que coincidan con los filtros
            </div>
        </div>
    </div>
</div>

<script>
function dashboardAsistenciaData() {
    return {
        asistentes: [],
        asistentesFiltrados: [],
        stats: {},
        busqueda: '',
        filtros: {
            evento_id: '',
            departamento: '',
            municipio: '',
            barrio: '',
            genero: '',
            grupo_etareo: '',
            area_interes: '',
            habeas_data: ''
        },
        listas: {
            departamentos: [],
            municipios_filtro: []
        },
        charts: {
            genero: null,
            grupoEtareo: null,
            areasInteres: null,
            municipios: null
        },

        async init() {
            await this.cargarDepartamentos();
            await this.cargarDatos();
            lucide.createIcons();
        },

        async cargarDepartamentos() {
            try {
                const response = await fetch('/aratio/api/territorios.php?accion=departamentos');
                const result = await response.json();
                if (result.success) {
                    this.listas.departamentos = result.data;
                }
            } catch (error) {
                console.error('Error cargando departamentos:', error);
            }
        },

        async cargarMunicipiosFiltro() {
            this.filtros.municipio = '';
            this.listas.municipios_filtro = [];

            if (!this.filtros.departamento) return;

            try {
                const response = await fetch(`/aratio/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.filtros.departamento)}`);
                const result = await response.json();
                if (result.success) {
                    this.listas.municipios_filtro = result.data;
                }
            } catch (error) {
                console.error('Error cargando municipios:', error);
            }
        },

        async cargarDatos() {
            try {
                // Construir query string con filtros
                const params = new URLSearchParams();
                params.append('campana_id', <?= $campanaId ?>);

                Object.keys(this.filtros).forEach(key => {
                    if (this.filtros[key]) {
                        params.append(key, this.filtros[key]);
                    }
                });

                const response = await fetch(`/aratio/api/dashboard_asistencia.php?${params.toString()}`);
                const result = await response.json();

                if (result.success) {
                    this.asistentes = result.data;
                    this.stats = result.stats;
                    this.filtrarAsistentes();
                    this.renderizarGraficos(result.charts);
                }
            } catch (error) {
                console.error('Error cargando datos:', error);
            }
        },

        filtrarAsistentes() {
            if (!this.busqueda) {
                this.asistentesFiltrados = this.asistentes;
                return;
            }

            const busqueda = this.busqueda.toLowerCase();
            this.asistentesFiltrados = this.asistentes.filter(a =>
                a.nombre.toLowerCase().includes(busqueda) ||
                a.documento.toLowerCase().includes(busqueda)
            );
        },

        renderizarGraficos(chartsData) {
            // Destruir gráficos anteriores
            Object.values(this.charts).forEach(chart => {
                if (chart) chart.destroy();
            });

            // Gráfico de Género
            const ctxGenero = document.getElementById('chartGenero');
            this.charts.genero = new Chart(ctxGenero, {
                type: 'doughnut',
                data: chartsData.genero,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // Gráfico de Grupo Etario
            const ctxGrupoEtareo = document.getElementById('chartGrupoEtareo');
            this.charts.grupoEtareo = new Chart(ctxGrupoEtareo, {
                type: 'bar',
                data: chartsData.grupoEtareo,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // Gráfico de Áreas de Interés
            const ctxAreasInteres = document.getElementById('chartAreasInteres');
            this.charts.areasInteres = new Chart(ctxAreasInteres, {
                type: 'bar',
                data: chartsData.areasInteres,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // Gráfico de Municipios
            const ctxMunicipios = document.getElementById('chartMunicipios');
            this.charts.municipios = new Chart(ctxMunicipios, {
                type: 'bar',
                data: chartsData.municipios,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        },

        limpiarFiltros() {
            this.filtros = {
                evento_id: '',
                departamento: '',
                municipio: '',
                barrio: '',
                genero: '',
                grupo_etareo: '',
                area_interes: '',
                habeas_data: ''
            };
            this.cargarDatos();
        },

        exportarDatos() {
            alert('Exportar datos a Excel (funcionalidad pendiente)');
        }
    }
}

lucide.createIcons();
</script>

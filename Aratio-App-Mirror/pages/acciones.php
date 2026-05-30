<?php
/**
 * MÓDULO: Acciones Comunitarias
 * Gestión de acciones con jerarquía territorial de 5 niveles
 */

if (!$campanaActiva) {
    echo '<div class="card text-center py-12"><i data-lucide="flag" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i><h2 class="text-xl font-bold text-gray-900 mb-2">Selecciona una campaña</h2></div>';
    return;
}

$db = getDB();
$campanaId = $campanaActiva['id'];

$stmt = $db->prepare("SELECT a.*, u.nombre as usuario_nombre FROM acciones_comunitarias a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.campana_id = ? ORDER BY a.fecha_accion DESC");
$stmt->execute([$campanaId]);
$acciones = $stmt->fetchAll();

$stmt = $db->prepare("SELECT COUNT(*) as total, SUM(personas_contactadas) as contactadas, SUM(compromisos_obtenidos) as compromisos FROM acciones_comunitarias WHERE campana_id = ?");
$stmt->execute([$campanaId]);
$stats = $stmt->fetch();
?>

<div class="space-y-6" x-data="accionesData()" x-init="init()">
    <div class="flex items-center justify-between">
        <div><h1 class="text-3xl font-bold text-gray-900">Acciones Comunitarias</h1><p class="text-gray-600 mt-2"><?= htmlspecialchars($campanaActiva['nombre']) ?></p></div>
        <button @click="abrirModalNuevo()" class="btn-primary"><i data-lucide="plus" class="w-5 h-5 inline mr-2"></i>Nueva Acción</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="stat-card"><div class="p-3 bg-blue-100 rounded-lg mb-4"><i data-lucide="activity" class="w-6 h-6 text-blue-600"></i></div><h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total'] ?? 0) ?></h3><p class="text-sm text-gray-600">Total Acciones</p></div>
        <div class="stat-card"><div class="p-3 bg-green-100 rounded-lg mb-4"><i data-lucide="users" class="w-6 h-6 text-green-600"></i></div><h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['contactadas'] ?? 0) ?></h3><p class="text-sm text-gray-600">Personas Contactadas</p></div>
        <div class="stat-card"><div class="p-3 bg-purple-100 rounded-lg mb-4"><i data-lucide="handshake" class="w-6 h-6 text-purple-600"></i></div><h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['compromisos'] ?? 0) ?></h3><p class="text-sm text-gray-600">Compromisos Obtenidos</p></div>
    </div>

    <div class="card"><h3 class="font-bold text-gray-900 mb-4">Mapa de Acciones</h3><div id="mapaAcciones" style="height: 400px;"></div></div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ubicación</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contactadas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Compromisos</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr></thead>
                <tbody class="divide-y">
                    <?php foreach ($acciones as $a): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4"><p class="font-medium"><?= htmlspecialchars(substr($a['descripcion'], 0, 50)) ?>...</p><p class="text-xs text-gray-500"><?= htmlspecialchars($a['usuario_nombre']) ?></p></td>
                        <td class="px-6 py-4"><span class="badge badge-info"><?= ucfirst($a['tipo']) ?></span></td>
                        <td class="px-6 py-4 text-sm"><?= formatDate($a['fecha_accion'], 'd/m/Y') ?></td>
                        <td class="px-6 py-4 text-sm"><?= htmlspecialchars($a['municipio']) ?></td>
                        <td class="px-6 py-4 text-sm"><?= $a['personas_contactadas'] ?></td>
                        <td class="px-6 py-4 text-sm font-bold text-green-600"><?= $a['compromisos_obtenidos'] ?></td>
                        <td class="px-6 py-4 text-right">
                            <button @click="editar(<?= htmlspecialchars(json_encode($a)) ?>)" class="text-yellow-600 hover:text-yellow-800 mr-2" title="Editar">
                                <i data-lucide="edit" class="w-4 h-4 inline"></i>
                            </button>
                            <button @click="eliminar(<?= $a['id'] ?>)" class="text-red-600 hover:text-red-800" title="Eliminar">
                                <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="modalNuevo" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]" style="display: none;">
        <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto relative z-[10000]">
            <div class="p-6 border-b"><h2 class="text-xl font-bold">Nueva Acción Comunitaria</h2></div>
            <form @submit.prevent="guardar()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium mb-2">Tipo de Acción *</label>
                        <select x-model="form.tipo" required class="input">
                            <option value="">Seleccionar...</option>
                            <option value="puerta-puerta">Puerta a Puerta</option>
                            <option value="brigada-salud">Brigada de Salud</option>
                            <option value="jornada-social">Jornada Social</option>
                            <option value="recoleccion-firmas">Recolección de Firmas</option>
                            <option value="encuesta">Encuesta</option>
                            <option value="entrega-volantes">Entrega de Volantes</option>
                            <option value="reunion-comunitaria">Reunión Comunitaria</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium mb-2">Fecha *</label><input type="datetime-local" x-model="form.fecha_accion" required class="input"></div>
                    <div class="col-span-2"><label class="block text-sm font-medium mb-2">Descripción *</label><textarea x-model="form.descripcion" rows="3" required class="input"></textarea></div>
                    <!-- Selectores Geográficos en Cascada -->
                    <div><label class="block text-sm font-medium mb-2">Departamento *</label>
                        <select x-model="form.departamento" @change="cargarMunicipios()" required class="input">
                            <option value="">Seleccionar departamento...</option>
                            <template x-for="dep in listas.departamentos" :key="dep">
                                <option :value="dep" x-text="dep"></option>
                            </template>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium mb-2">Municipio *</label>
                        <select x-model="municipio_raw" @change="selectMunicipio()" required class="input" :disabled="!form.departamento">
                            <option value="">Seleccionar municipio...</option>
                            <template x-for="mun in listas.municipios" :key="mun.cod_mpio">
                                <option :value="JSON.stringify(mun)" x-text="mun.municipio"></option>
                            </template>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium mb-2">Tipo Territorio</label>
                        <select x-model="form.tipo_territorio" @change="cargarTerritorios()" class="input" :disabled="!form.municipio">
                            <option value="">Seleccionar tipo...</option>
                            <template x-for="tipo in listas.tipos_territorio" :key="tipo">
                                <option :value="tipo" x-text="tipo"></option>
                            </template>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium mb-2">Territorio</label>
                        <select x-model="form.territorio" @change="cargarBarrios()" class="input" :disabled="!form.tipo_territorio">
                            <option value="">Seleccionar territorio...</option>
                            <template x-for="terr in listas.territorios" :key="terr">
                                <option :value="terr" x-text="terr"></option>
                            </template>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium mb-2">Barrio/Vereda</label>
                        <select x-model="form.barrio" class="input" :disabled="!form.territorio">
                            <option value="">Seleccionar barrio...</option>
                            <template x-for="barrio in listas.barrios" :key="barrio">
                                <option :value="barrio" x-text="barrio"></option>
                            </template>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium mb-2">Personas Contactadas</label><input type="number" x-model="form.personas_contactadas" min="0" class="input"></div>
                    <div><label class="block text-sm font-medium mb-2">Compromisos Obtenidos</label><input type="number" x-model="form.compromisos_obtenidos" min="0" class="input"></div>
                    <div class="col-span-2"><label class="block text-sm font-medium mb-2">Observaciones</label><textarea x-model="form.observaciones" rows="2" class="input"></textarea></div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="btn-primary flex-1"><i data-lucide="save" class="w-5 h-5 inline mr-2"></i>Guardar</button>
                    <button type="button" @click="cerrarModal()" class="btn-ghost">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function accionesData() {
    return {
        modalNuevo: false,
        form: {
            id: null,
            campana_id: <?= $campanaId ?>,
            tipo: '',
            fecha_accion: '',
            descripcion: '',
            departamento: '',
            municipio: '',
            tipo_territorio: '',
            territorio: '',
            barrio: '',
            latitud: null,
            longitud: null,
            personas_contactadas: 0,
            compromisos_obtenidos: 0,
            observaciones: ''
        },
        municipio_raw: '',
        listas: {
            departamentos: [],
            municipios: [],
            tipos_territorio: [],
            territorios: [],
            barrios: []
        },
        loading: false,

        async init() {
            // Cargar departamentos al iniciar
            await this.cargarDepartamentos();

            setTimeout(() => {
                const mapa = L.map('mapaAcciones').setView([4.570868, -74.297333], 6);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapa);
                <?php foreach ($acciones as $a): if ($a['latitud'] && $a['longitud']): ?>
                L.marker([<?= $a['latitud'] ?>, <?= $a['longitud'] ?>]).addTo(mapa).bindPopup('<?= addslashes($a['descripcion']) ?>');
                <?php endif; endforeach; ?>
            }, 100);
        },

        async abrirModalNuevo() {
            this.modalNuevo = true;
            // Asegurar que los departamentos estén cargados
            if (this.listas.departamentos.length === 0) {
                await this.cargarDepartamentos();
            }
        },

        async cargarDepartamentos() {
            try {
                const response = await fetch('/api/territorios.php?accion=departamentos');
                const result = await response.json();
                if (result.success) {
                    this.listas.departamentos = result.data;
                }
            } catch (error) {
                console.error('Error cargando departamentos:', error);
            }
        },

        async cargarMunicipios() {
            this.form.municipio = '';
            this.municipio_raw = '';
            this.form.tipo_territorio = '';
            this.form.territorio = '';
            this.form.barrio = '';
            this.listas.municipios = [];
            this.listas.tipos_territorio = [];
            this.listas.territorios = [];
            this.listas.barrios = [];

            if (!this.form.departamento) return;

            try {
                const response = await fetch(`/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.form.departamento)}`);
                const result = await response.json();
                if (result.success) {
                    this.listas.municipios = result.data;
                }
            } catch (error) {
                console.error('Error cargando municipios:', error);
            }
        },

        async selectMunicipio() {
            if (!this.municipio_raw) return;
            try {
                const munObj = JSON.parse(this.municipio_raw);
                this.form.municipio = munObj.municipio;
                await this.cargarTiposTerritorio();
            } catch (e) {
                console.error('Error parsing municipio:', e);
            }
        },

        async cargarTiposTerritorio() {
            this.form.tipo_territorio = '';
            this.form.territorio = '';
            this.form.barrio = '';
            this.listas.tipos_territorio = [];
            this.listas.territorios = [];
            this.listas.barrios = [];

            if (!this.form.departamento || !this.form.municipio) return;

            try {
                const response = await fetch(`/api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}`);
                const result = await response.json();
                if (result.success) {
                    this.listas.tipos_territorio = result.data;
                }
            } catch (error) {
                console.error('Error cargando tipos de territorio:', error);
            }
        },

        async cargarTerritorios() {
            this.form.territorio = '';
            this.form.barrio = '';
            this.listas.territorios = [];
            this.listas.barrios = [];

            if (!this.form.departamento || !this.form.municipio || !this.form.tipo_territorio) return;

            try {
                const response = await fetch(`/api/territorios.php?accion=territorios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(this.form.tipo_territorio)}`);
                const result = await response.json();
                if (result.success) {
                    this.listas.territorios = result.data;
                }
            } catch (error) {
                console.error('Error cargando territorios:', error);
            }
        },

        async cargarBarrios() {
            this.form.barrio = '';
            this.listas.barrios = [];

            if (!this.form.departamento || !this.form.municipio || !this.form.tipo_territorio || !this.form.territorio) return;

            try {
                const response = await fetch(`/api/territorios.php?accion=barrios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(this.form.tipo_territorio)}&territorio=${encodeURIComponent(this.form.territorio)}`);
                const result = await response.json();
                if (result.success) {
                    this.listas.barrios = result.data;
                }
            } catch (error) {
                console.error('Error cargando barrios:', error);
            }
        },
        async guardar() {
            this.loading = true;
            const method = this.form.id ? 'PUT' : 'POST';

            try {
                const response = await fetch('/api/acciones.php', {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    this.modalNuevo = false;
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error de conexión: ' + error.message);
            }
            this.loading = false;
        },

        async eliminar(id) {
            if (!confirm('¿Estás seguro de eliminar esta acción?')) return;

            try {
                const response = await fetch(`/api/acciones.php?id=${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error de conexión: ' + error.message);
            }
        },

        async editar(accion) {
            this.form = {
                id: accion.id,
                campana_id: accion.campana_id,
                tipo: accion.tipo,
                fecha_accion: accion.fecha_accion.replace(' ', 'T').substring(0, 16),
                descripcion: accion.descripcion,
                departamento: accion.departamento,
                municipio: accion.municipio,
                tipo_territorio: accion.tipo_territorio || '',
                territorio: accion.territorio || '',
                barrio: accion.barrio || '',
                latitud: accion.latitud,
                longitud: accion.longitud,
                personas_contactadas: accion.personas_contactadas,
                compromisos_obtenidos: accion.compromisos_obtenidos,
                observaciones: accion.observaciones || ''
            };

            // Cargar las listas en cascada para edición
            if (this.form.departamento) {
                await this.cargarMunicipios();
                if (this.form.municipio) {
                    const munMatch = this.listas.municipios.find(m => m.municipio === this.form.municipio);
                    if (munMatch) {
                        this.municipio_raw = JSON.stringify(munMatch);
                    }
                }

                if (this.form.municipio) {
                    await this.cargarTiposTerritorio();
                    if (this.form.tipo_territorio) {
                        await this.cargarTerritorios();
                        if (this.form.territorio) {
                            await this.cargarBarrios();
                        }
                    }
                }
            }

            // Restaurar valores finales
            this.form.municipio = accion.municipio;
            this.form.tipo_territorio = accion.tipo_territorio || '';
            this.form.territorio = accion.territorio || '';
            this.form.barrio = accion.barrio || '';

            this.modalNuevo = true;
        },

        cerrarModal() {
            this.modalNuevo = false;
            this.form = {
                id: null,
                campana_id: <?= $campanaId ?>,
                tipo: '',
                fecha_accion: '',
                descripcion: '',
                departamento: '',
                municipio: '',
                tipo_territorio: '',
                territorio: '',
                barrio: '',
                latitud: null,
                longitud: null,
                personas_contactadas: 0,
                compromisos_obtenidos: 0,
                observaciones: ''
            };
            this.listas.municipios = [];
            this.listas.tipos_territorio = [];
            this.listas.territorios = [];
            this.listas.barrios = [];
        }
    }
}
lucide.createIcons();
</script>

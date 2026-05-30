<?php
/**
 * MÓDULO: Compromisos
 * Sistema de gestión de compromisos comunitarios con las 5 preguntas
 */

if (!$campanaActiva) {
    echo '<div class="card text-center py-12"><i data-lucide="flag" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i><h2 class="text-xl font-bold text-gray-900 mb-2">Selecciona una campaña</h2></div>';
    return;
}

$db = getDB();
$campanaId = $campanaActiva['id'];

$stmt = $db->prepare("SELECT c.*, u.nombre as usuario_nombre FROM compromisos c LEFT JOIN usuarios u ON c.usuario_id = u.id WHERE c.campana_id = ? ORDER BY c.fecha_compromiso DESC");
$stmt->execute([$campanaId]);
$compromisos = $stmt->fetchAll();

$stmt = $db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN estado='cumplido' THEN 1 ELSE 0 END) as cumplidos, SUM(presupuesto_estimado) as presupuesto, SUM(beneficiarios_estimados) as beneficiarios FROM compromisos WHERE campana_id = ?");
$stmt->execute([$campanaId]);
$stats = $stmt->fetch();
?>

<div class="space-y-6" x-data="compromisosData()" x-init="init()">
    <div class="flex items-center justify-between">
        <div><h1 class="text-3xl font-bold text-gray-900">Compromisos Comunitarios</h1><p class="text-gray-600 mt-2"><?= htmlspecialchars($campanaActiva['nombre']) ?></p></div>
        <button @click="abrirModalNuevo()" class="btn-primary"><i data-lucide="plus" class="w-5 h-5 inline mr-2"></i>Nuevo Compromiso</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="stat-card"><div class="p-3 bg-blue-100 rounded-lg mb-4"><i data-lucide="handshake" class="w-6 h-6 text-blue-600"></i></div><h3 class="text-2xl font-bold"><?= number_format($stats['total'] ?? 0) ?></h3><p class="text-sm text-gray-600">Total Compromisos</p></div>
        <div class="stat-card"><div class="p-3 bg-green-100 rounded-lg mb-4"><i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i></div><h3 class="text-2xl font-bold"><?= number_format($stats['cumplidos'] ?? 0) ?></h3><p class="text-sm text-gray-600">Cumplidos</p></div>
        <div class="stat-card"><div class="p-3 bg-yellow-100 rounded-lg mb-4"><i data-lucide="dollar-sign" class="w-6 h-6 text-yellow-600"></i></div><h3 class="text-2xl font-bold"><?= formatCurrency($stats['presupuesto'] ?? 0) ?></h3><p class="text-sm text-gray-600">Presupuesto Total</p></div>
        <div class="stat-card"><div class="p-3 bg-purple-100 rounded-lg mb-4"><i data-lucide="users" class="w-6 h-6 text-purple-600"></i></div><h3 class="text-2xl font-bold"><?= number_format($stats['beneficiarios'] ?? 0) ?></h3><p class="text-sm text-gray-600">Beneficiarios</p></div>
    </div>

    <div class="card">
        <h3 class="font-bold text-gray-900 mb-4">Compromisos por Tipo</h3>
        <div class="space-y-3">
            <?php
            $stmt = $db->prepare("SELECT tipo, COUNT(*) as total FROM compromisos WHERE campana_id = ? GROUP BY tipo");
            $stmt->execute([$campanaId]);
            $tipos = $stmt->fetchAll();
            $total_general = array_sum(array_column($tipos, 'total'));
            foreach ($tipos as $tipo):
                $pct = $total_general > 0 ? ($tipo['total'] / $total_general) * 100 : 0;
            ?>
            <div><div class="flex justify-between text-sm mb-1"><span><?= ucfirst($tipo['tipo']) ?></span><span class="font-medium"><?= $tipo['total'] ?> (<?= number_format($pct, 1) ?>%)</span></div><div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-gradient-to-r from-primary to-secondary h-2 rounded-full" style="width: <?= $pct ?>%"></div></div></div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Compromiso</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Líder</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridad</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avance</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr></thead>
                <tbody class="divide-y">
                    <?php foreach ($compromisos as $c): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4"><p class="font-medium"><?= htmlspecialchars($c['titulo']) ?></p><p class="text-xs text-gray-500"><?= htmlspecialchars(substr($c['descripcion'], 0, 50)) ?>...</p></td>
                        <td class="px-6 py-4"><span class="badge badge-info"><?= ucfirst($c['tipo']) ?></span></td>
                        <td class="px-6 py-4 text-sm"><?= htmlspecialchars($c['lider_nombre']) ?></td>
                        <td class="px-6 py-4 text-sm"><?= formatDate($c['fecha_compromiso'], 'd/m/Y') ?></td>
                        <td class="px-6 py-4">
                            <?php $prioClass = ['alta' => 'badge-error', 'media' => 'badge-warning', 'baja' => 'badge-info']; ?>
                            <span class="badge <?= $prioClass[$c['prioridad']] ?>"><?= ucfirst($c['prioridad']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <?php $estClass = ['pendiente' => 'badge-warning', 'en-gestion' => 'badge-info', 'cumplido' => 'badge-success', 'incumplido' => 'badge-error']; ?>
                            <span class="badge <?= $estClass[$c['estado']] ?>"><?= ucfirst($c['estado']) ?></span>
                        </td>
                        <td class="px-6 py-4"><div class="w-24"><div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-green-500 h-2 rounded-full" style="width: <?= $c['avance_porcentaje'] ?>%"></div></div><p class="text-xs text-gray-500 mt-1"><?= number_format($c['avance_porcentaje'], 0) ?>%</p></div></td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-blue-600 hover:text-blue-800 mr-2"><i data-lucide="eye" class="w-4 h-4 inline"></i></button>
                            <button class="text-yellow-600 hover:text-yellow-800 mr-2"><i data-lucide="edit" class="w-4 h-4 inline"></i></button>
                            <button class="text-red-600 hover:text-red-800"><i data-lucide="trash-2" class="w-4 h-4 inline"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="modalNuevo" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]" style="display: none;">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto relative z-[10000]">
            <div class="p-6 border-b"><h2 class="text-xl font-bold">Nuevo Compromiso - Las 5 Preguntas</h2></div>
            <form @submit.prevent="guardar()" class="p-6 space-y-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-bold text-blue-900 mb-2">Metodología de las 5 Preguntas</h3>
                    <ol class="text-sm text-blue-700 space-y-1 list-decimal list-inside">
                        <li><strong>¿Qué?</strong> - Definir el compromiso específico</li>
                        <li><strong>¿Quién?</strong> - Identificar al líder comunitario</li>
                        <li><strong>¿Cuándo?</strong> - Establecer fecha estimada</li>
                        <li><strong>¿Dónde?</strong> - Ubicación geográfica precisa</li>
                        <li><strong>¿Cómo?</strong> - Metodología de ejecución</li>
                    </ol>
                </div>

                <div class="space-y-4">
                    <div class="border-l-4 border-primary pl-4"><h4 class="font-bold text-gray-900 mb-3">1. ¿Qué se va a hacer?</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium mb-2">Tipo de Compromiso *</label>
                                <select x-model="form.tipo" required class="input">
                                    <option value="">Seleccionar...</option>
                                    <option value="infraestructura">Infraestructura</option>
                                    <option value="servicios-publicos">Servicios Públicos</option>
                                    <option value="salud">Salud</option>
                                    <option value="educacion">Educación</option>
                                    <option value="seguridad">Seguridad</option>
                                    <option value="deporte">Deporte</option>
                                    <option value="cultura">Cultura</option>
                                    <option value="medio-ambiente">Medio Ambiente</option>
                                    <option value="empleo">Empleo</option>
                                    <option value="vivienda">Vivienda</option>
                                    <option value="movilidad">Movilidad</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div><label class="block text-sm font-medium mb-2">Prioridad *</label>
                                <select x-model="form.prioridad" required class="input">
                                    <option value="alta">Alta</option>
                                    <option value="media">Media</option>
                                    <option value="baja">Baja</option>
                                </select>
                            </div>
                            <div class="col-span-2"><label class="block text-sm font-medium mb-2">Título del Compromiso *</label><input type="text" x-model="form.titulo" required class="input" placeholder="Ej: Pavimentación de vía principal"></div>
                            <div class="col-span-2"><label class="block text-sm font-medium mb-2">Descripción Detallada *</label><textarea x-model="form.descripcion" rows="3" required class="input"></textarea></div>
                        </div>
                    </div>

                    <div class="border-l-4 border-secondary pl-4"><h4 class="font-bold text-gray-900 mb-3">2. ¿Quién es el líder comunitario?</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium mb-2">Nombre del Líder *</label><input type="text" x-model="form.lider_nombre" required class="input"></div>
                            <div><label class="block text-sm font-medium mb-2">Teléfono *</label><input type="tel" x-model="form.lider_telefono" required class="input"></div>
                            <div><label class="block text-sm font-medium mb-2">Email</label><input type="email" x-model="form.lider_email" class="input"></div>
                            <div><label class="block text-sm font-medium mb-2">Cargo/Rol</label><input type="text" x-model="form.lider_cargo" class="input" placeholder="Ej: Presidente JAC"></div>
                        </div>
                    </div>

                    <div class="border-l-4 border-primary pl-4"><h4 class="font-bold text-gray-900 mb-3">3. ¿Cuándo se realizará?</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium mb-2">Fecha de Compromiso *</label><input type="date" x-model="form.fecha_compromiso" required class="input"></div>
                            <div><label class="block text-sm font-medium mb-2">Fecha Estimada de Cumplimiento</label><input type="date" x-model="form.fecha_cumplimiento_estimada" class="input"></div>
                        </div>
                    </div>

                    <div class="border-l-4 border-secondary pl-4"><h4 class="font-bold text-gray-900 mb-3">4. ¿Dónde se ejecutará?</h4>
                        <div class="grid grid-cols-2 gap-4">
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
                        </div>
                    </div>

                    <div class="border-l-4 border-primary pl-4"><h4 class="font-bold text-gray-900 mb-3">5. ¿Cómo se ejecutará?</h4>
                        <div class="space-y-4">
                            <div><label class="block text-sm font-medium mb-2">Metodología de Ejecución *</label><textarea x-model="form.metodologia" rows="3" required class="input" placeholder="Describe cómo se llevará a cabo el compromiso"></textarea></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="block text-sm font-medium mb-2">Presupuesto Estimado</label><input type="number" x-model="form.presupuesto_estimado" min="0" step="0.01" class="input"></div>
                                <div><label class="block text-sm font-medium mb-2">Beneficiarios Estimados</label><input type="number" x-model="form.beneficiarios_estimados" min="0" class="input"></div>
                            </div>
                        </div>
                    </div>

                    <div><label class="block text-sm font-medium mb-2">Observaciones Adicionales</label><textarea x-model="form.observaciones" rows="2" class="input"></textarea></div>
                </div>

                <div class="flex gap-3 pt-4 border-t">
                    <button type="submit" class="btn-primary flex-1"><i data-lucide="save" class="w-5 h-5 inline mr-2"></i>Guardar Compromiso</button>
                    <button type="button" @click="cerrarModal()" class="btn-ghost">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function compromisosData() {
    return {
        modalNuevo: false,
        form: {
            id: null,
            campana_id: <?= $campanaId ?>,
            tipo: '',
            titulo: '',
            descripcion: '',
            lider_nombre: '',
            lider_telefono: '',
            lider_email: '',
            lider_cargo: '',
            fecha_compromiso: '',
            fecha_cumplimiento_estimada: '',
            departamento: '',
            municipio: '',
            tipo_territorio: '',
            territorio: '',
            barrio: '',
            latitud: null,
            longitud: null,
            metodologia: '',
            presupuesto_estimado: '',
            beneficiarios_estimados: '',
            prioridad: 'media',
            estado: 'pendiente',
            avance_porcentaje: 0,
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
            const method = this.form.id ? "PUT" : "POST";
            try {
                const response = await fetch("/api/compromisos.php", {
                    method: method,
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify(this.form)
                });
                const result = await response.json();
                if (result.success) {
                    alert(result.message);
                    this.modalNuevo = false;
                    window.location.reload();
                } else {
                    alert("Error: " + result.message);
                }
            } catch (error) {
                alert("Error de conexión: " + error.message);
            }
            this.loading = false;
        },

        async eliminar(id) {
            if (!confirm('¿Estás seguro de eliminar este compromiso?')) return;

            try {
                const response = await fetch(`/api/compromisos.php?id=${id}`, {
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

        async editar(compromiso) {
            this.form = {
                id: compromiso.id,
                campana_id: compromiso.campana_id,
                tipo: compromiso.tipo,
                titulo: compromiso.titulo,
                descripcion: compromiso.descripcion,
                lider_nombre: compromiso.lider_nombre,
                lider_telefono: compromiso.lider_telefono,
                lider_email: compromiso.lider_email || '',
                lider_cargo: compromiso.lider_cargo || '',
                fecha_compromiso: compromiso.fecha_compromiso,
                fecha_cumplimiento_estimada: compromiso.fecha_cumplimiento_estimada || '',
                departamento: compromiso.departamento,
                municipio: compromiso.municipio,
                tipo_territorio: compromiso.tipo_territorio || '',
                territorio: compromiso.territorio || '',
                barrio: compromiso.barrio || '',
                latitud: compromiso.latitud,
                longitud: compromiso.longitud,
                metodologia: compromiso.metodologia || '',
                presupuesto_estimado: compromiso.presupuesto_estimado || '',
                beneficiarios_estimados: compromiso.beneficiarios_estimados || '',
                prioridad: compromiso.prioridad || 'media',
                estado: compromiso.estado || 'pendiente',
                avance_porcentaje: compromiso.avance_porcentaje || 0,
                observaciones: compromiso.observaciones || ''
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
            this.form.municipio = compromiso.municipio;
            this.form.tipo_territorio = compromiso.tipo_territorio || '';
            this.form.territorio = compromiso.territorio || '';
            this.form.barrio = compromiso.barrio || '';

            this.modalNuevo = true;
        },

        cerrarModal() {
            this.listas.municipios = [];
            this.listas.tipos_territorio = [];
            this.listas.territorios = [];
            this.listas.barrios = [];
            this.modalNuevo = false;
            this.form = {
                id: null,
                campana_id: <?= $campanaId ?>,
                tipo: '',
                titulo: '',
                descripcion: '',
                lider_nombre: '',
                lider_telefono: '',
                lider_email: '',
                lider_cargo: '',
                fecha_compromiso: '',
                fecha_cumplimiento_estimada: '',
                departamento: '',
                municipio: '',
                tipo_territorio: '',
                territorio: '',
                barrio: '',
                latitud: null,
                longitud: null,
                metodologia: '',
                presupuesto_estimado: '',
                beneficiarios_estimados: '',
                prioridad: 'media',
                estado: 'pendiente',
                avance_porcentaje: 0,
                observaciones: ''
            };
        }
    }
}
lucide.createIcons();
</script>

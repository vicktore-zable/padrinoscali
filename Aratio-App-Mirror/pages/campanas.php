<?php
/**
 * MÓDULO: Campañas
 * CRUD completo de campañas electorales
 */

$db = getDB();

// Obtener campañas del usuario
// Obtener campañas del usuario
try {
    $stmt = $db->prepare("
        SELECT c.*, cand.nombre_completo as candidato_nombre, e.nombre as eleccion_nombre
        FROM campanas c
        LEFT JOIN candidatos cand ON c.candidato_id = cand.id
        LEFT JOIN elecciones e ON c.eleccion_id = e.id
        INNER JOIN usuarios_campanas uc ON c.id = uc.campana_id
        WHERE uc.usuario_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $campanas = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error campanas list: " . $e->getMessage());
    $campanas = [];
}

// Obtener candidatos disponibles
try {
    $candidatos = $db->query("SELECT id, nombre_completo, cargo_aspira FROM candidatos WHERE estado = 'activo' ORDER BY nombre_completo")->fetchAll();
} catch (Exception $e) {
    error_log("Error candidatos list: " . $e->getMessage());
    $candidatos = [];
}

// Obtener elecciones disponibles
try {
    $elecciones = $db->query("SELECT id, nombre, tipo, fecha_eleccion FROM elecciones WHERE estado IN ('programada', 'activa') ORDER BY fecha_eleccion")->fetchAll();
} catch (Exception $e) {
    error_log("Error elecciones list: " . $e->getMessage());
    $elecciones = [];
}

// Pre-cargar departamentos para el modal
try {
    $stmt = $db->query("SELECT DISTINCT departamento FROM territorios WHERE departamento IS NOT NULL AND departamento != '' ORDER BY departamento");
    $departamentosSSR = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Fallback si no hay datos
    if (empty($departamentosSSR)) {
        $departamentosSSR = ['Valle del Cauca']; // Valor por defecto
    }
} catch (Exception $e) {
    error_log("Error SSR departamentos: " . $e->getMessage());
    $departamentosSSR = ['Valle del Cauca']; // Valor por defecto en caso de error
}
?>

<div class="space-y-6" x-data="campanasData()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Campañas</h1>
            <p class="text-gray-600 mt-2">Administra todas las campañas electorales</p>
        </div>
        <button @click="modalNuevo = true" class="btn-primary"><i data-lucide="plus"
                class="w-5 h-5 inline mr-2"></i>Nueva Campaña</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($campanas as $c): ?>
            <div class="card hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                        style="background: linear-gradient(135deg, <?= $c['color_primario'] ?? COLOR_PRIMARY ?> 0%, <?= $c['color_secundario'] ?? COLOR_SECONDARY ?> 100%);">
                        <i data-lucide="flag" class="w-6 h-6 text-white"></i>
                    </div>
                    <?php
                    $estadoClass = ['planificacion' => 'badge-warning', 'activa' => 'badge-success', 'finalizada' => 'badge-info', 'suspendida' => 'badge-error'];
                    ?>
                    <span class="badge <?= $estadoClass[$c['estado']] ?>"><?= ucfirst($c['estado']) ?></span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2"><?= htmlspecialchars($c['nombre']) ?></h3>
                <?php if ($c['slogan']): ?>
                    <p class="text-sm text-gray-600 mb-3 italic">"<?= htmlspecialchars($c['slogan']) ?>"</p>
                <?php endif; ?>
                <div class="space-y-2 text-sm text-gray-600 mb-4">
                    <p><i data-lucide="user-circle" class="w-4 h-4 inline"></i>
                        <?= htmlspecialchars($c['candidato_nombre']) ?></p>
                    <p><i data-lucide="vote" class="w-4 h-4 inline"></i> <?= htmlspecialchars($c['eleccion_nombre']) ?></p>
                    <p><i data-lucide="map-pin" class="w-4 h-4 inline"></i> <?= htmlspecialchars($c['municipio']) ?>,
                        <?= htmlspecialchars($c['departamento']) ?></p>
                    <p><i data-lucide="calendar" class="w-4 h-4 inline"></i> <?= formatDate($c['fecha_inicio'], 'd/m/Y') ?>
                        - <?= formatDate($c['fecha_fin'], 'd/m/Y') ?></p>
                </div>
                <div class="mb-4">
                    <div class="flex justify-between text-xs mb-1"><span>Meta de Votos</span><span
                            class="font-medium"><?= number_format($c['votos_actuales']) ?> /
                            <?= number_format($c['meta_votos']) ?></span></div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <?php $pct = $c['meta_votos'] > 0 ? min(100, ($c['votos_actuales'] / $c['meta_votos']) * 100) : 0; ?>
                        <div class="h-2 rounded-full"
                            style="width: <?= $pct ?>%; background: linear-gradient(135deg, <?= $c['color_primario'] ?> 0%, <?= $c['color_secundario'] ?> 100%);">
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="?campana=<?= $c['id'] ?>" class="btn-primary flex-1 text-center text-sm py-2">Seleccionar</a>
                    <a href="?page=colaboradores&view=campaign&campana=<?= $c['id'] ?>" 
                       class="btn-ghost flex-1 text-center text-sm py-2 border border-gray-200"
                       title="Ver Colaboradores">
                        <i data-lucide="users" class="w-4 h-4 inline mr-1"></i> Colabs
                    </a>
                    <button @click="editar(<?= htmlspecialchars(json_encode($c)) ?>)" class="btn-ghost px-3 border border-gray-200"><i
                            data-lucide="edit" class="w-4 h-4"></i></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div x-show="modalNuevo || modalEditar"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]" style="display: none;">
        <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto relative z-[10000]">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold" x-text="modalEditar ? 'Editar Campaña' : 'Nueva Campaña'"></h2>
            </div>
            <form @submit.prevent="guardar()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label class="block text-sm font-medium mb-2">Nombre de la Campaña
                            *</label><input type="text" x-model="form.nombre" required class="input"></div>
                    <div class="col-span-2"><label class="block text-sm font-medium mb-2">Slogan</label><input
                            type="text" x-model="form.slogan" class="input" placeholder="Lema o frase representativa">
                    </div>
                    <div class="col-span-2"><label class="block text-sm font-medium mb-2">Descripción</label><textarea
                            x-model="form.descripcion" rows="3" class="input"></textarea></div>
                    <div><label class="block text-sm font-medium mb-2">Código *</label><input type="text"
                            x-model="form.codigo" required class="input" placeholder="CAMP-2027-001"></div>
                    <div><label class="block text-sm font-medium mb-2">Estado *</label>
                        <select x-model="form.estado" required class="input">
                            <option value="planificacion">Planificación</option>
                            <option value="activa">Activa</option>
                            <option value="finalizada">Finalizada</option>
                            <option value="suspendida">Suspendida</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium mb-2">Candidato *</label>
                        <select x-model="form.candidato_id" required class="input">
                            <option value="">Seleccionar candidato...</option>
                            <?php foreach ($candidatos as $cand): ?>
                                <option value="<?= $cand['id'] ?>"><?= htmlspecialchars($cand['nombre_completo']) ?> -
                                    <?= htmlspecialchars($cand['cargo_aspira']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium mb-2">Elección *</label>
                        <select x-model="form.eleccion_id" required class="input">
                            <option value="">Seleccionar elección...</option>
                            <?php foreach ($elecciones as $elec): ?>
                                <option value="<?= $elec['id'] ?>"><?= htmlspecialchars($elec['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                     <div>
                        <label class="block text-sm font-medium mb-2">Departamento *</label>
                        <select x-model="form.departamento" @change="cargarMunicipios()" required class="input">
                            <option value="">Seleccionar departamento...</option>
                            <template x-for="dep in listas.departamentos" :key="dep">
                                <option :value="dep" x-text="dep"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Municipio *</label>
                        <select x-model="municipio_raw" @change="selectMunicipio()" required class="input" :disabled="!form.departamento || loadingMunicipios">
                            <option value="">
                                <span x-show="!loadingMunicipios">Seleccionar municipio...</span>
                                <span x-show="loadingMunicipios">Cargando...</span>
                            </option>
                            <template x-for="mun in listas.municipios" :key="mun.cod_mpio">
                                <option :value="JSON.stringify(mun)" x-text="mun.municipio"></option>
                            </template>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium mb-2">Fecha Inicio *</label><input type="date"
                            x-model="form.fecha_inicio" required class="input"></div>
                    <div><label class="block text-sm font-medium mb-2">Fecha Fin *</label><input type="date"
                            x-model="form.fecha_fin" required class="input"></div>
                    <div><label class="block text-sm font-medium mb-2">Meta de Votos</label><input type="number"
                            x-model="form.meta_votos" min="0" class="input"></div>
                    <div><label class="block text-sm font-medium mb-2">Presupuesto</label><input type="number"
                            x-model="form.presupuesto" min="0" step="0.01" class="input"></div>
                    <div><label class="block text-sm font-medium mb-2">Color Primario</label><input type="color"
                            x-model="form.color_primario" class="input h-12"></div>
                    <div><label class="block text-sm font-medium mb-2">Color Secundario</label><input type="color"
                            x-model="form.color_secundario" class="input h-12"></div>
                </div>
                <div class="flex gap-3 pt-4 border-t">
                    <button type="submit" class="btn-primary flex-1" :disabled="loading">
                        <span x-show="!loading"><i data-lucide="save" class="w-5 h-5 inline mr-2"></i>Guardar</span>
                        <span x-show="loading">Guardando...</span>
                    </button>
                    <button type="button" @click="cerrarModal()" class="btn-ghost" :disabled="loading">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function campanasData() {
        const API_BASE = '<?= url('api/') ?>';
        return {
            modalNuevo: false,
            modalEditar: false,
            loading: false,
            loadingMunicipios: false,
                color_primario: '#FF00FF', color_secundario: '#FFD700'
            },
            municipio_raw: '',
            listas: {
                departamentos: <?= json_encode($departamentosSSR) ?>,
                municipios: []
            },
            async init() {
                // Si la lista de departamentos está vacía o solo tiene el fallback, intentar cargar desde API
                if (this.listas.departamentos.length <= 1) {
                    await this.cargarDepartamentos();
                }
            },
            async cargarDepartamentos() {
                try {
                    const response = await fetch(`${API_BASE}territorios.php?accion=departamentos`);
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
                this.listas.municipios = [];
                if (!this.form.departamento) return;

                this.loadingMunicipios = true;
                try {
                    const response = await fetch(`${API_BASE}territorios.php?accion=municipios&departamento=${encodeURIComponent(this.form.departamento)}`);
                    const result = await response.json();
                    if (result.success) {
                        this.listas.municipios = result.data;
                    }
                } catch (error) {
                    console.error('Error cargando municipios:', error);
                } finally {
                    this.loadingMunicipios = false;
                }
            },
            async selectMunicipio() {
                if (!this.municipio_raw) return;
                try {
                    const munObj = JSON.parse(this.municipio_raw);
                    this.form.municipio = munObj.municipio;
                } catch (e) {
                    console.error('Error parsing municipio:', e);
                }
            },
            async editar(campana) {
                this.form = {
                    id: campana.id,
                    codigo: campana.codigo || '',
                    nombre: campana.nombre || '',
                    slogan: campana.slogan || '',
                    descripcion: campana.descripcion || '',
                    estado: campana.estado || 'planificacion',
                    candidato_id: campana.candidato_id || '',
                    eleccion_id: campana.eleccion_id || '',
                    departamento: campana.departamento || '',
                    municipio: campana.municipio || '',
                    fecha_inicio: campana.fecha_inicio || '',
                    fecha_fin: campana.fecha_fin || '',
                    meta_votos: campana.meta_votos || '',
                    presupuesto: campana.presupuesto || '',
                    color_primario: campana.color_primario || '#FF00FF',
                    color_secundario: campana.color_secundario || '#FFD700'
                };
                
                // Cargar municipios si hay departamento
                if (this.form.departamento) {
                    await this.cargarMunicipios();
                    if (this.form.municipio) {
                        const munMatch = this.listas.municipios.find(m => m.municipio === this.form.municipio);
                        if (munMatch) {
                            this.municipio_raw = JSON.stringify(munMatch);
                        }
                    }
                }

                this.modalEditar = true;
                this.$nextTick(() => {
                    lucide.createIcons();
                });
            },
            async guardar() {
                this.loading = true;
                const method = this.form.id ? 'PUT' : 'POST';
                try {
                    const response = await fetch(`${API_BASE}campanas.php`, {
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
                        this.cerrarModal();
                        window.location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Error de conexión: ' + error.message);
                } finally {
                    this.loading = false;
                }
            },
            cerrarModal() {
                this.modalNuevo = false;
                this.modalEditar = false;
                this.form = {
                    id: null, codigo: '', nombre: '', slogan: '', descripcion: '', estado: 'planificacion',
                    candidato_id: '', eleccion_id: '', departamento: '', municipio: '',
                    fecha_inicio: '', fecha_fin: '', meta_votos: '', presupuesto: '',
                    color_primario: '<?= COLOR_PRIMARY ?>', color_secundario: '<?= COLOR_SECONDARY ?>'
                };
                this.listas.municipios = [];
            }
        }
    }
    lucide.createIcons();
</script>
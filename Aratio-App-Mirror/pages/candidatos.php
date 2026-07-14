<?php
$db = getDB();
$stmt = $db->query("
    SELECT c.*, g.nombre as grupo_nombre, g.sigla as grupo_sigla, g.color as grupo_color,
           e.nombre as eleccion_nombre
    FROM candidatos c
    LEFT JOIN grupos_politicos g ON c.grupo_politico_id = g.id
    LEFT JOIN elecciones e ON c.eleccion_id = e.id
    ORDER BY c.created_at DESC
");
$candidatos = $stmt->fetchAll();

// Obtener grupos y elecciones para los selects
$grupos = $db->query("SELECT id, nombre, sigla FROM grupos_politicos WHERE activo = 1 ORDER BY nombre")->fetchAll();
$elecciones = $db->query("SELECT id, nombre, tipo FROM elecciones WHERE estado IN ('programada', 'en-campana') ORDER BY fecha_eleccion")->fetchAll();

// Pre-cargar departamentos para el modal
try {
    $stmt = $db->query("SELECT DISTINCT departamento FROM territorios WHERE departamento IS NOT NULL AND departamento != '' ORDER BY departamento");
    $departamentosSSR = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($departamentosSSR)) $departamentosSSR = ['Valle del Cauca'];
} catch (Exception $e) {
    $departamentosSSR = ['Valle del Cauca'];
}
?>
<div class="space-y-6" x-data="candidatosData()" x-init="init()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Candidatos</h1>
            <p class="text-gray-600 mt-2">Registro y gestión de candidatos políticos</p>
        </div>
        <button @click="modalNuevo = true" class="btn-primary">
            <i data-lucide="plus" class="w-5 h-5 inline mr-2"></i>Nuevo Candidato
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($candidatos as $c): ?>
            <div class="card hover:shadow-lg transition">
                <div class="flex items-start gap-4">
                    <?php if ($c['foto_url']): ?>
                        <img src="<?= htmlspecialchars($c['foto_url']) ?>" alt="<?= htmlspecialchars($c['nombre_completo']) ?>"
                            class="w-16 h-16 rounded-full object-cover flex-shrink-0">
                    <?php else: ?>
                        <div class="w-16 h-16 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-2xl font-bold text-white"><?= substr($c['nombres'], 0, 1) ?><?= substr($c['apellidos'], 0, 1) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900"><?= htmlspecialchars($c['nombre_completo']) ?></h3>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($c['cargo_aspira']) ?></p>
                        <?php if ($c['grupo_nombre']): ?>
                            <div class="flex items-center gap-2 mt-2">
                                <?php if ($c['grupo_color']): ?>
                                    <span class="w-3 h-3 rounded-full inline-block" style="background-color: <?= $c['grupo_color'] ?>;"></span>
                                <?php endif; ?>
                                <span class="text-xs text-gray-500"><?= htmlspecialchars($c['grupo_sigla'] ?? $c['grupo_nombre']) ?></span>
                            </div>
                        <?php else: ?>
                            <span class="text-xs text-gray-500 italic">Independiente</span>
                        <?php endif; ?>
                        <div class="flex gap-2 mt-3 flex-wrap">
                            <span class="badge badge-primary text-xs"><?= htmlspecialchars($c['tipo_documento']) ?> <?= htmlspecialchars($c['documento']) ?></span>
                            <?php $ec = ['inscrito' => 'badge-warning', 'activo' => 'badge-success', 'retirado' => 'badge-error', 'elegido' => 'badge-info']; ?>
                            <span class="badge <?= $ec[$c['estado']] ?> text-xs"><?= ucfirst($c['estado']) ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($c['municipio'] || $c['email'] || $c['telefono']): ?>
                    <div class="mt-4 pt-4 border-t space-y-1 text-sm text-gray-600">
                        <?php if ($c['municipio']): ?>
                            <p><i data-lucide="map-pin" class="w-4 h-4 inline"></i> <?= htmlspecialchars($c['municipio']) ?><?= $c['departamento'] ? ', ' . htmlspecialchars($c['departamento']) : '' ?></p>
                        <?php endif; ?>
                        <?php if ($c['email']): ?>
                            <p><i data-lucide="mail" class="w-4 h-4 inline"></i> <?= htmlspecialchars($c['email']) ?></p>
                        <?php endif; ?>
                        <?php if ($c['telefono']): ?>
                            <p><i data-lucide="phone" class="w-4 h-4 inline"></i> <?= htmlspecialchars($c['telefono']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="flex gap-2 mt-4 pt-4 border-t">
                    <button @click="editar(<?= htmlspecialchars(json_encode($c)) ?>)" class="btn-primary flex-1 text-sm py-2">
                        <i data-lucide="edit" class="w-4 h-4 inline mr-1"></i>Editar
                    </button>
                    <button @click="eliminar(<?= $c['id'] ?>)" class="btn-ghost px-3 text-red-600 hover:bg-red-50">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($candidatos)): ?>
            <div class="col-span-full text-center py-12 text-gray-500">
                <i data-lucide="inbox" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                <p>No hay candidatos registrados</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Nuevo/Editar Candidato -->
    <div x-show="modalNuevo" x-cloak class="modal">
        <div class="modal-content max-w-4xl" @click.away="cerrarModal()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold" x-text="form.id ? 'Editar Candidato' : 'Nuevo Candidato'"></h3>
                <button @click="cerrarModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form @submit.prevent="guardar()">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Información Personal -->
                    <div class="col-span-full">
                        <h4 class="text-lg font-semibold mb-3 text-gray-700">Información Personal</h4>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Nombres *</label>
                        <input type="text" x-model="form.nombres" class="form-input" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Apellidos *</label>
                        <input type="text" x-model="form.apellidos" class="form-input" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Tipo de Documento *</label>
                        <select x-model="form.tipo_documento" class="form-select" required>
                            <option value="CC">Cédula de Ciudadanía (CC)</option>
                            <option value="CE">Cédula de Extranjería (CE)</option>
                            <option value="PA">Pasaporte (PA)</option>
                            <option value="NIT">NIT</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Número de Documento *</label>
                        <input type="text" x-model="form.documento" class="form-input" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" x-model="form.email" class="form-input">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Teléfono</label>
                        <input type="tel" x-model="form.telefono" class="form-input">
                    </div>

                    <!-- Información Política -->
                    <div class="col-span-full mt-4">
                        <h4 class="text-lg font-semibold mb-3 text-gray-700">Información Política</h4>
                    </div>

                    <div class="col-span-full">
                        <label class="block text-sm font-medium mb-2">Cargo al que Aspira *</label>
                        <input type="text" x-model="form.cargo_aspira" class="form-input" required
                            placeholder="Ej: Alcalde, Concejal, Gobernador, etc.">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Grupo Político</label>
                        <select x-model="form.grupo_politico_id" class="form-select">
                            <option value="">Independiente</option>
                            <?php foreach ($grupos as $grupo): ?>
                                <option value="<?= $grupo['id'] ?>"><?= htmlspecialchars($grupo['nombre']) ?> (<?= htmlspecialchars($grupo['sigla']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Elección</label>
                        <select x-model="form.eleccion_id" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php foreach ($elecciones as $eleccion): ?>
                                <option value="<?= $eleccion['id'] ?>"><?= htmlspecialchars($eleccion['nombre']) ?> (<?= ucfirst($eleccion['tipo']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Departamento</label>
                        <select x-model="form.departamento" @change="cargarMunicipios()" class="form-select">
                            <option value="">Seleccionar...</option>
                            <template x-for="dep in listas.departamentos" :key="dep">
                                <option :value="dep" x-text="dep"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Municipio</label>
                        <select x-model="municipio_raw" @change="selectMunicipio()" class="form-select" :disabled="!form.departamento || loadingMunicipios">
                            <option value="">
                                <span x-show="!loadingMunicipios">Seleccionar...</span>
                                <span x-show="loadingMunicipios">Cargando...</span>
                            </option>
                            <template x-for="mun in listas.municipios" :key="mun.cod_mpio">
                                <option :value="JSON.stringify(mun)" x-text="mun.municipio"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Estado</label>
                        <select x-model="form.estado" class="form-select">
                            <option value="inscrito">Inscrito</option>
                            <option value="activo">Activo</option>
                            <option value="retirado">Retirado</option>
                            <option value="elegido">Elegido</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">URL de Foto</label>
                        <input type="url" x-model="form.foto_url" class="form-input"
                            placeholder="https://ejemplo.com/foto.jpg">
                    </div>

                    <div class="col-span-full">
                        <label class="block text-sm font-medium mb-2">Biografía</label>
                        <textarea x-model="form.biografia" rows="3" class="form-input"
                            placeholder="Breve descripción del candidato..."></textarea>
                    </div>

                    <div class="col-span-full">
                        <label class="block text-sm font-medium mb-2">Propuestas</label>
                        <textarea x-model="form.propuestas" rows="4" class="form-input"
                            placeholder="Principales propuestas del candidato..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                    <button type="button" @click="cerrarModal()" class="btn-secondary" :disabled="loading">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary" :disabled="loading">
                        <span x-show="!loading"><i data-lucide="save" class="w-4 h-4 inline mr-2"></i>Guardar</span>
                        <span x-show="loading">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function candidatosData() {
        return {
            modalNuevo: false,
            loading: false,
            form: {
                id: null,
                nombres: '',
                apellidos: '',
                tipo_documento: 'CC',
                documento: '',
                cargo_aspira: '',
                grupo_politico_id: '',
                eleccion_id: '',
                email: '',
                telefono: '',
                foto_url: '',
                departamento: '',
                municipio: '',
                biografia: '',
                propuestas: '',
                estado: 'inscrito'
            },
            municipio_raw: '',
            listas: {
                departamentos: <?= json_encode($departamentosSSR) ?>,
                municipios: []
            },
            loadingMunicipios: false,

            async init() {
                if (this.listas.departamentos.length <= 1) {
                    await this.cargarDepartamentos();
                }
            },

            async cargarDepartamentos() {
                try {
                    const response = await fetch('/aratio/api/territorios.php?accion=departamentos');
                    const result = await response.json();
                    if (result.success) this.listas.departamentos = result.data;
                } catch (error) {
                    console.error('Error:', error);
                }
            },

            async cargarMunicipios() {
                this.form.municipio = '';
                this.municipio_raw = '';
                this.listas.municipios = [];
                if (!this.form.departamento) return;
                
                this.loadingMunicipios = true;
                try {
                    const response = await fetch(`/aratio/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.form.departamento)}`);
                    const result = await response.json();
                    if (result.success) this.listas.municipios = result.data;
                } catch (error) {
                    console.error('Error:', error);
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

            async editar(candidato) {
                this.form = {
                    id: candidato.id,
                    nombres: candidato.nombres,
                    apellidos: candidato.apellidos,
                    tipo_documento: candidato.tipo_documento,
                    documento: candidato.documento,
                    cargo_aspira: candidato.cargo_aspira,
                    grupo_politico_id: candidato.grupo_politico_id || '',
                    eleccion_id: candidato.eleccion_id || '',
                    email: candidato.email || '',
                    telefono: candidato.telefono || '',
                    foto_url: candidato.foto_url || '',
                    departamento: candidato.departamento || '',
                    municipio: candidato.municipio || '',
                    biografia: candidato.biografia || '',
                    propuestas: candidato.propuestas || '',
                    estado: candidato.estado
                };
                this.modalNuevo = true;
                if (this.form.departamento) {
                    await this.cargarMunicipios();
                    if (this.form.municipio) {
                        const munMatch = this.listas.municipios.find(m => m.municipio === this.form.municipio);
                        if (munMatch) {
                            this.municipio_raw = JSON.stringify(munMatch);
                        }
                    }
                }
                this.$nextTick(() => lucide.createIcons());
            },

            async guardar() {
                this.loading = true;
                const method = this.form.id ? 'PUT' : 'POST';
                try {
                    const response = await fetch('/aratio/api/candidatos.php', {
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

            async eliminar(id) {
                if (!confirm('¿Estás seguro de eliminar este candidato?\n\nEsta acción no se puede deshacer.')) return;
                try {
                    const response = await fetch(`/aratio/api/candidatos.php?id=${id}`, {
                        method: 'DELETE',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
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

            cerrarModal() {
                this.modalNuevo = false;
                this.form = {
                    id: null,
                    nombres: '',
                    apellidos: '',
                    tipo_documento: 'CC',
                    documento: '',
                    cargo_aspira: '',
                    grupo_politico_id: '',
                    eleccion_id: '',
                    email: '',
                    telefono: '',
                    foto_url: '',
                    departamento: '',
                    municipio: '',
                    biografia: '',
                    propuestas: '',
                    estado: 'inscrito'
                };
            }
        };
    }

    lucide.createIcons();
</script>
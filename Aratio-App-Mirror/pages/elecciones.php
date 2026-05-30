<?php
$db = getDB();
$stmt = $db->query("SELECT * FROM elecciones ORDER BY fecha_eleccion DESC");
$elecciones = $stmt->fetchAll();
?>
<div class="space-y-6" x-data="eleccionesData()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Elecciones</h1>
            <p class="text-gray-600 mt-2">Gestión de procesos electorales</p>
        </div>
        <button @click="modalNuevo = true" class="btn-primary">
            <i data-lucide="plus" class="w-5 h-5 inline mr-2"></i>Nueva Elección
        </button>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ámbito</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($elecciones as $e): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium"><?= htmlspecialchars($e['codigo']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($e['nombre']) ?></td>
                        <td class="px-6 py-4"><span class="badge badge-info"><?= ucfirst($e['tipo']) ?></span></td>
                        <td class="px-6 py-4"><span class="badge badge-primary"><?= ucfirst($e['ambito']) ?></span></td>
                        <td class="px-6 py-4 text-sm"><?= formatDate($e['fecha_eleccion'], 'd/m/Y') ?></td>
                        <td class="px-6 py-4">
                            <?php $ec = ['programada' => 'badge-warning', 'en-campana' => 'badge-info', 'finalizada' => 'badge-success', 'cancelada' => 'badge-error']; ?>
                            <span class="badge <?= $ec[$e['estado']] ?>"><?= ucfirst($e['estado']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="editar(<?= htmlspecialchars(json_encode($e)) ?>)"
                                class="text-yellow-600 hover:text-yellow-800 mr-2">
                                <i data-lucide="edit" class="w-4 h-4 inline"></i>
                            </button>
                            <button @click="eliminar(<?= $e['id'] ?>)" class="text-red-600 hover:text-red-800">
                                <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($elecciones)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i data-lucide="inbox" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                            <p>No hay elecciones registradas</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Nuevo/Editar Elección -->
    <div x-show="modalNuevo" x-cloak class="modal">
        <div class="modal-content max-w-2xl" @click.away="cerrarModal()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold" x-text="form.id ? 'Editar Elección' : 'Nueva Elección'"></h3>
                <button @click="cerrarModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form @submit.prevent="guardar()">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Código -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Código *</label>
                        <input type="text" x-model="form.codigo" class="form-input" required
                            placeholder="Ej: ELEC-2027-001">
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Estado *</label>
                        <select x-model="form.estado" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="programada">Programada</option>
                            <option value="en-campana">En Campaña</option>
                            <option value="finalizada">Finalizada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>

                    <!-- Descripción (col-span-2) -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium mb-2">Descripción</label>
                        <textarea x-model="form.descripcion" class="form-input" rows="3" placeholder="Descripción opcional de la elección..."></textarea>
                    </div>

                    <!-- Nombre -->
                    <div class="col-span-full">
                        <label class="block text-sm font-medium mb-2">Nombre de la Elección *</label>
                        <input type="text" x-model="form.nombre" class="form-input" required
                            placeholder="Ej: Elecciones Regionales 2027">
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Tipo *</label>
                        <select x-model="form.tipo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="presidencial">Presidencial</option>
                            <option value="congreso">Congreso</option>
                            <option value="regional">Regional</option>
                            <option value="local">Local</option>
                            <option value="referendo">Referendo</option>
                            <option value="consulta">Consulta</option>
                        </select>
                    </div>

                    <!-- Ámbito -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Ámbito *</label>
                        <select x-model="form.ambito" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="nacional">Nacional</option>
                            <option value="departamental">Departamental</option>
                            <option value="municipal">Municipal</option>
                            <option value="local">Local</option>
                        </select>
                    </div>

                    <!-- Fecha de Elección -->
                    <div class="col-span-full">
                        <label class="block text-sm font-medium mb-2">Fecha de la Elección *</label>
                        <input type="date" x-model="form.fecha_eleccion" class="form-input" required>
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
    function eleccionesData() {
        return {
            modalNuevo: false,
            loading: false,
            form: {
                id: null,
                codigo: '',
                nombre: '',
                tipo: '',
                ambito: '',
                fecha_eleccion: '',
                periodo_inicio: '',
                periodo_fin: '',
                estado: 'programada',
                descripcion: ''
            },

            editar(eleccion) {
                this.form = {
                    id: eleccion.id,
                    codigo: eleccion.codigo,
                    nombre: eleccion.nombre,
                    tipo: eleccion.tipo,
                    ambito: eleccion.ambito,
                    fecha_eleccion: eleccion.fecha_eleccion,
                    periodo_inicio: eleccion.periodo_inicio,
                    periodo_fin: eleccion.periodo_fin,
                    fecha_eleccion: eleccion.fecha_eleccion,
                    estado: eleccion.estado
                };
                this.modalNuevo = true;
                this.$nextTick(() => lucide.createIcons());
            },

            async guardar() {
                this.loading = true;
                const method = this.form.id ? 'PUT' : 'POST';
                try {
                    const response = await fetch('/api/elecciones.php', {
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
                } catch (e) {
                    alert('Error de conexión: ' + e.message);
                } finally {
                    this.loading = false;
                }
            },

            async eliminar(id) {
                if (!confirm('¿Estás seguro de eliminar esta elección?\n\nEsta acción no se puede deshacer y puede afectar campañas y candidatos asociados.')) return;
                try {
                    const response = await fetch(`/api/elecciones.php?id=${id}`, {
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
                } catch (e) {
                    alert('Error de conexión: ' + e.message);
                }
            },

            cerrarModal() {
                this.modalNuevo = false;
                this.form = {
                    id: null,
                    codigo: '',
                    nombre: '',
                    tipo: '',
                    ambito: '',
                    fecha_eleccion: '',
                    estado: 'programada'
                };
            }
        };
    }

    lucide.createIcons();
</script>
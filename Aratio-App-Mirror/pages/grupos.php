<?php
$db = getDB();
$stmt = $db->query("SELECT * FROM grupos_politicos WHERE activo = 1 ORDER BY nombre ASC");
$grupos = $stmt->fetchAll();
?>
<div class="space-y-6" x-data="gruposData()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Grupos Políticos</h1>
            <p class="text-gray-600 mt-2">Partidos, movimientos y coaliciones políticas</p>
        </div>
        <button @click="modalNuevo = true" class="btn-primary">
            <i data-lucide="plus" class="w-5 h-5 inline mr-2"></i>Nuevo Grupo
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($grupos as $g): ?>
            <div class="card hover:shadow-lg transition">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                        style="background-color: <?= $g['color'] ?>;">
                        <span class="text-white font-bold text-lg"><?= htmlspecialchars($g['sigla']) ?></span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900"><?= htmlspecialchars($g['nombre']) ?></h3>
                        <p class="text-xs text-gray-500"><?= ucfirst(str_replace('-', ' ', $g['tipo'])) ?></p>
                    </div>
                </div>

                <div class="space-y-2 text-sm text-gray-600 mb-4">
                    <?php if ($g['representante_legal']): ?>
                        <p><i data-lucide="user" class="w-4 h-4 inline"></i> <?= htmlspecialchars($g['representante_legal']) ?></p>
                    <?php endif; ?>
                    <?php if ($g['numero_afiliados']): ?>
                        <p><i data-lucide="users" class="w-4 h-4 inline"></i> <?= number_format($g['numero_afiliados']) ?> afiliados</p>
                    <?php endif; ?>
                    <?php if ($g['email']): ?>
                        <p><i data-lucide="mail" class="w-4 h-4 inline"></i> <?= htmlspecialchars($g['email']) ?></p>
                    <?php endif; ?>
                    <?php if ($g['telefono']): ?>
                        <p><i data-lucide="phone" class="w-4 h-4 inline"></i> <?= htmlspecialchars($g['telefono']) ?></p>
                    <?php endif; ?>
                    <?php if ($g['web']): ?>
                        <p><i data-lucide="globe" class="w-4 h-4 inline"></i> <a href="<?= htmlspecialchars($g['web']) ?>" target="_blank" class="text-primary hover:underline"><?= htmlspecialchars($g['web']) ?></a></p>
                    <?php endif; ?>
                </div>

                <div class="flex gap-2 mt-4 pt-4 border-t">
                    <button @click="editar(<?= htmlspecialchars(json_encode($g)) ?>)" class="btn-primary flex-1 text-sm py-2">
                        <i data-lucide="edit" class="w-4 h-4 inline mr-1"></i>Editar
                    </button>
                    <button @click="eliminar(<?= $g['id'] ?>)" class="btn-ghost px-3 text-red-600 hover:bg-red-50">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($grupos)): ?>
            <div class="col-span-full text-center py-12 text-gray-500">
                <i data-lucide="inbox" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                <p>No hay grupos políticos registrados</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Nuevo/Editar Grupo -->
    <div x-show="modalNuevo" x-cloak class="modal">
        <div class="modal-content max-w-3xl" @click.away="cerrarModal()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold" x-text="form.id ? 'Editar Grupo Político' : 'Nuevo Grupo Político'"></h3>
                <button @click="cerrarModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form @submit.prevent="guardar()">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nombre Completo -->
                    <div class="col-span-full">
                        <label class="block text-sm font-medium mb-2">Nombre Completo *</label>
                        <input type="text" x-model="form.nombre" class="form-input" required
                            placeholder="Ej: Partido Liberal Colombiano">
                    </div>

                    <!-- Sigla -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Sigla *</label>
                        <input type="text" x-model="form.sigla" class="form-input" required
                            placeholder="Ej: PLC" maxlength="20">
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Tipo *</label>
                        <select x-model="form.tipo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="partido">Partido</option>
                            <option value="movimiento">Movimiento</option>
                            <option value="coalicion">Coalición</option>
                            <option value="grupo-significativo">Grupo Significativo</option>
                        </select>
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Color Representativo *</label>
                        <input type="color" x-model="form.color" class="form-input h-12">
                    </div>

                    <!-- Fecha de Fundación -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Fecha de Fundación</label>
                        <input type="date" x-model="form.fecha_fundacion" class="form-input">
                    </div>

                    <!-- Representante Legal -->
                    <div class="col-span-full">
                        <label class="block text-sm font-medium mb-2">Representante Legal</label>
                        <input type="text" x-model="form.representante_legal" class="form-input"
                            placeholder="Nombre completo del representante legal">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" x-model="form.email" class="form-input"
                            placeholder="contacto@partido.com">
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Teléfono</label>
                        <input type="tel" x-model="form.telefono" class="form-input"
                            placeholder="+57 300 123 4567">
                    </div>

                    <!-- Sitio Web -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Sitio Web</label>
                        <input type="url" x-model="form.web" class="form-input"
                            placeholder="https://www.partido.com">
                    </div>

                    <!-- Número de Afiliados -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Número de Afiliados</label>
                        <input type="number" x-model="form.numero_afiliados" class="form-input" min="0"
                            placeholder="0">
                    </div>

                    <!-- Logo URL -->
                    <div class="col-span-full">
                        <label class="block text-sm font-medium mb-2">URL del Logo</label>
                        <input type="url" x-model="form.logo_url" class="form-input"
                            placeholder="https://ejemplo.com/logo.png">
                    </div>

                    <!-- Estado Activo -->
                    <div class="col-span-full">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" x-model="form.activo" class="w-4 h-4 text-primary rounded">
                            <span class="text-sm font-medium">Grupo activo</span>
                        </label>
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
    function gruposData() {
        return {
            modalNuevo: false,
            loading: false,
            form: {
                id: null,
                nombre: '',
                sigla: '',
                tipo: '',
                color: '#FF0000',
                logo_url: '',
                fecha_fundacion: '',
                representante_legal: '',
                email: '',
                telefono: '',
                web: '',
                numero_afiliados: '',
                activo: true
            },

            editar(grupo) {
                this.form = {
                    id: grupo.id,
                    nombre: grupo.nombre,
                    sigla: grupo.sigla,
                    tipo: grupo.tipo,
                    color: grupo.color || '#FF0000',
                    logo_url: grupo.logo_url || '',
                    fecha_fundacion: grupo.fecha_fundacion || '',
                    representante_legal: grupo.representante_legal || '',
                    email: grupo.email || '',
                    telefono: grupo.telefono || '',
                    web: grupo.web || '',
                    numero_afiliados: grupo.numero_afiliados || '',
                    activo: grupo.activo == 1
                };
                this.modalNuevo = true;
                this.$nextTick(() => lucide.createIcons());
            },

            async guardar() {
                this.loading = true;
                const method = this.form.id ? 'PUT' : 'POST';
                try {
                    const response = await fetch('/aratio/api/grupos.php', {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            ...this.form,
                            activo: this.form.activo ? 1 : 0
                        })
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
                if (!confirm('¿Estás seguro de eliminar este grupo político?\n\nEsta acción no se puede deshacer.')) return;
                try {
                    const response = await fetch(`/aratio/api/grupos.php?id=${id}`, {
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
                    nombre: '',
                    sigla: '',
                    tipo: '',
                    color: '#FF0000',
                    logo_url: '',
                    fecha_fundacion: '',
                    representante_legal: '',
                    email: '',
                    telefono: '',
                    web: '',
                    numero_afiliados: '',
                    activo: true
                };
            }
        };
    }

    lucide.createIcons();
</script>
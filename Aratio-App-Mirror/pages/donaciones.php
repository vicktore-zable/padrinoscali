<?php
/**
 * MÓDULO: Donaciones
 * Gestión completa de donaciones de campaña
 */

if (!$campanaActiva) {
    echo '<div class="card text-center py-12">
        <i data-lucide="flag" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Selecciona una campaña</h2>
        <p class="text-gray-600">Debes seleccionar una campaña para ver las donaciones</p>
    </div>';
    return;
}

$db = getDB();
$campanaId = $campanaActiva['id'];

// Obtener donaciones
try {
    $stmt = $db->prepare("
        SELECT d.*, u.nombre as recaudador
        FROM donaciones d
        LEFT JOIN usuarios u ON d.recaudador_id = u.id
        WHERE d.campana_id = ?
        ORDER BY d.fecha_donacion DESC
    ");
    $stmt->execute([$campanaId]);
    $donaciones = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error donaciones list: " . $e->getMessage());
    $donaciones = [];
}

// Estadísticas
try {
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(monto) as total_recaudado,
            SUM(CASE WHEN estado = 'confirmada' THEN monto ELSE 0 END) as confirmado,
            SUM(CASE WHEN estado = 'pendiente' THEN monto ELSE 0 END) as pendiente
        FROM donaciones 
        WHERE campana_id = ?
    ");
    $stmt->execute([$campanaId]);
    $stats = $stmt->fetch();
} catch (Exception $e) {
    error_log("Error donaciones stats: " . $e->getMessage());
    $stats = ['total' => 0, 'total_recaudado' => 0, 'confirmado' => 0, 'pendiente' => 0];
}
?>

<div class="space-y-6" x-data="donacionesData()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Donaciones</h1>
            <p class="text-gray-600 mt-2"><?= htmlspecialchars($campanaActiva['nombre']) ?></p>
        </div>
        <button @click="modalNuevo = true" class="btn-primary">
            <i data-lucide="plus" class="w-5 h-5 inline mr-2"></i>
            Nueva Donación
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i data-lucide="dollar-sign" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">
                <?= formatCurrency($stats['total_recaudado'] ?? 0) ?>
            </h3>
            <p class="text-sm text-gray-600">Total Recaudado</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i data-lucide="check-circle" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">
                <?= formatCurrency($stats['confirmado'] ?? 0) ?>
            </h3>
            <p class="text-sm text-gray-600">Confirmado</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <i data-lucide="clock" class="w-6 h-6 text-yellow-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">
                <?= formatCurrency($stats['pendiente'] ?? 0) ?>
            </h3>
            <p class="text-sm text-gray-600">Pendiente</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i data-lucide="users" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">
                <?= number_format($stats['total'] ?? 0) ?>
            </h3>
            <p class="text-sm text-gray-600">Total Donaciones</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" x-model="filtros.buscar" @input="filtrar()" placeholder="Buscar donante..."
                class="input">
            <select x-model="filtros.estado" @change="filtrar()" class="input">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="confirmada">Confirmada</option>
                <option value="rechazada">Rechazada</option>
            </select>
            <select x-model="filtros.metodo" @change="filtrar()" class="input">
                <option value="">Todos los métodos</option>
                <option value="efectivo">Efectivo</option>
                <option value="transferencia">Transferencia</option>
                <option value="especie">Especie</option>
            </select>
            <button @click="exportar()" class="btn-ghost">
                <i data-lucide="download" class="w-5 h-5 inline mr-2"></i>
                Exportar
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Donante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recaudador</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($donaciones)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No hay donaciones registradas
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($donaciones as $donacion): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            <?= htmlspecialchars($donacion['nombre_donante']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($donacion['tipo_donante']) ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-green-600">
                                    <?= formatCurrency($donacion['monto']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="badge badge-info"><?= ucfirst($donacion['metodo_pago']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $estadoClass = [
                                        'pendiente' => 'badge-warning',
                                        'confirmada' => 'badge-success',
                                        'rechazada' => 'badge-error'
                                    ];
                                    ?>
                                    <span class="badge <?= $estadoClass[$donacion['estado']] ?>">
                                        <?= ucfirst($donacion['estado']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= formatDate($donacion['fecha_donacion'], 'd/m/Y H:i') ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= htmlspecialchars($donacion['recaudador'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="ver(<?= htmlspecialchars(json_encode($donacion)) ?>)"
                                        class="text-blue-600 hover:text-blue-800 mr-2">
                                        <i data-lucide="eye" class="w-4 h-4 inline"></i>
                                    </button>
                                    <button @click="editar(<?= htmlspecialchars(json_encode($donacion)) ?>)"
                                        class="text-yellow-600 hover:text-yellow-800 mr-2">
                                        <i data-lucide="edit" class="w-4 h-4 inline"></i>
                                    </button>
                                    <button @click="eliminar(<?= $donacion['id'] ?>)" class="text-red-600 hover:text-red-800">
                                        <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Nueva/Editar Donación -->
    <div x-show="modalNuevo || modalEditar"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]" style="display: none;">
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.away="cerrarModal()">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900" x-text="modalEditar ? 'Editar Donación' : 'Nueva Donación'">
                </h2>
            </div>
            <form @submit.prevent="guardar()" class="p-6 space-y-4">
                <!-- Tipo de Donante -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Donante *</label>
                    <select x-model="form.tipo_donante" required class="input">
                        <option value="">Seleccionar...</option>
                        <option value="persona-natural">Persona Natural</option>
                        <option value="persona-juridica">Persona Jurídica</option>
                        <option value="anonimo">Anónimo</option>
                    </select>
                </div>

                <!-- Nombre Donante -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Donante *</label>
                    <input type="text" x-model="form.nombre_donante" required class="input">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Documento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Documento</label>
                        <input type="text" x-model="form.documento_donante" class="input">
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                        <input type="tel" x-model="form.telefono_donante" class="input">
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" x-model="form.email_donante" class="input">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Monto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Monto *</label>
                        <input type="number" x-model="form.monto" required min="0" step="0.01" class="input">
                    </div>

                    <!-- Método de Pago -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago *</label>
                        <select x-model="form.metodo_pago" required class="input">
                            <option value="">Seleccionar...</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="especie">Especie</option>
                        </select>
                    </div>
                </div>

                <!-- Referencia de Pago -->
                <div x-show="form.metodo_pago === 'transferencia'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Referencia de Pago</label>
                    <input type="text" x-model="form.referencia_pago" class="input">
                </div>

                <!-- Descripción Especie -->
                <div x-show="form.metodo_pago === 'especie'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción del Bien/Servicio</label>
                    <textarea x-model="form.descripcion_especie" rows="3" class="input"></textarea>
                </div>

                <!-- Fecha de Donación -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Donación *</label>
                    <input type="datetime-local" x-model="form.fecha_donacion" required class="input">
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado *</label>
                    <select x-model="form.estado" required class="input">
                        <option value="pendiente">Pendiente</option>
                        <option value="confirmada">Confirmada</option>
                        <option value="rechazada">Rechazada</option>
                    </select>
                </div>

                <!-- Notas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notas</label>
                    <textarea x-model="form.notas" rows="3" class="input"></textarea>
                </div>

                <!-- Botones -->
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="btn-primary flex-1">
                        <i data-lucide="save" class="w-5 h-5 inline mr-2"></i>
                        Guardar
                    </button>
                    <button type="button" @click="cerrarModal()" class="btn-ghost">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function donacionesData() {
    return {
        modalNuevo: false,
        modalEditar: false,
        loading: false,
        filtros: {
            buscar: '',
            estado: '',
            metodo: ''
        },
        form: {
            id: null,
            campana_id: <?= $campanaId ?>,
            tipo_donante: '',
            nombre_donante: '',
            documento_donante: '',
            email_donante: '',
            telefono_donante: '',
            monto: '',
            metodo_pago: '',
            referencia_pago: '',
            descripcion_especie: '',
            fecha_donacion: new Date().toISOString().slice(0, 16),
            estado: 'pendiente',
            notas: ''
        },

        filtrar() {
            // Implementar filtrado en el futuro
            console.log('Filtrar:', this.filtros);
        },

        ver(donacion) {
            alert('Ver detalle de donación: ' + donacion.nombre_donante);
        },

        editar(donacion) {
            this.form = { 
                ...donacion,
                fecha_donacion: donacion.fecha_donacion.replace(' ', 'T').slice(0, 16)
            };
            this.modalEditar = true;
            this.$nextTick(() => lucide.createIcons());
        },

        async eliminar(id) {
            if (!confirm('¿Estás seguro de eliminar esta donación?')) return;
            
            try {
                const response = await fetch(`/aratio/api/donaciones.php?id=${id}`, {
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

        async guardar() {
            this.loading = true;
            const method = this.form.id ? 'PUT' : 'POST';
            
            try {
                const response = await fetch('/aratio/api/donaciones.php', {
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
            }
            this.loading = false;
        },

        cerrarModal() {
            this.modalNuevo = false;
            this.modalEditar = false;
            this.form = {
                id: null,
                campana_id: <?= $campanaId ?>,
                tipo_donante: '',
                nombre_donante: '',
                documento_donante: '',
                email_donante: '',
                telefono_donante: '',
                monto: '',
                metodo_pago: '',
                referencia_pago: '',
                descripcion_especie: '',
                fecha_donacion: new Date().toISOString().slice(0, 16),
                estado: 'pendiente',
                notas: ''
            };
        },

        exportar() {
            alert('Exportar donaciones a Excel');
        }
    }
}

    // Inicializar iconos
    lucide.createIcons();
</script>
```
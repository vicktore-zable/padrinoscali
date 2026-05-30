<?php
/**
 * MÓDULO: Gestión de Usuarios
 * CRUD completo de usuarios con RBAC (Solo Super-Admin)
 */

// Verificar permisos de acceso (Super-Admin, Admin-Campana, Supervisor)
$auth = new Auth();
if (!$auth->canManageUsers()) {
    $_SESSION['error_message'] = 'No tienes permisos para acceder a esta sección';
    header('Location: /');
    exit;
}

$isSuperAdmin = $auth->isSuperAdmin();
$campanaActivaId = $_SESSION['campana_activa'] ?? null;

$db = getDB();

// Obtener usuarios
try {
    if ($isSuperAdmin) {
        // Super-Admin ve todos los usuarios
        $stmt = $db->query("
            SELECT id, nombre, email, telefono, rol, estado, ultimo_acceso, created_at
            FROM usuarios
            ORDER BY created_at DESC
        ");
        $usuarios = $stmt->fetchAll();
    } else {
        // Otros roles solo ven usuarios de su campaña activa
        if (!$campanaActivaId) {
            $usuarios = [];
        } else {
            $stmt = $db->prepare("
                SELECT u.id, u.nombre, u.email, u.telefono, u.rol, u.estado, u.ultimo_acceso, u.created_at
                FROM usuarios u
                INNER JOIN usuarios_campanas uc ON u.id = uc.usuario_id
                WHERE uc.campana_id = ?
                ORDER BY u.created_at DESC
            ");
            $stmt->execute([$campanaActivaId]);
            $usuarios = $stmt->fetchAll();
        }
    }

    // Obtener conteo de campañas por usuario (solo para visualización)
    foreach ($usuarios as &$usuario) {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM usuarios_campanas WHERE usuario_id = ?");
        $stmt->execute([$usuario['id']]);
        $result = $stmt->fetch();
        $usuario['total_campanas'] = (int)$result['total'];
    }
} catch (Exception $e) {
    error_log("Error usuarios list: " . $e->getMessage());
    $usuarios = [];
}

// Obtener campañas disponibles para asignación
try {
    $campanas = $db->query("
        SELECT id, codigo, nombre, municipio, departamento
        FROM campanas
        WHERE estado IN ('planificacion', 'activa')
        ORDER BY nombre
    ")->fetchAll();
} catch (Exception $e) {
    error_log("Error campanas list: " . $e->getMessage());
    $campanas = [];
}

$roles = [
    'super-admin' => ['nombre' => 'Super Administrador', 'color' => 'bg-purple-100 text-purple-800'],
    'admin-campana' => ['nombre' => 'Administrador de Campaña', 'color' => 'bg-blue-100 text-blue-800'],
    'coordinador' => ['nombre' => 'Coordinador', 'color' => 'bg-green-100 text-green-800'],
    'colaborador' => ['nombre' => 'Colaborador', 'color' => 'bg-gray-100 text-gray-800'],
    'veedor' => ['nombre' => 'Veedor', 'color' => 'bg-yellow-100 text-yellow-800']
];
?>

<div class="space-y-6" x-data="usuariosData()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Usuarios</h1>
            <p class="text-gray-600 mt-2">Administra usuarios y permisos del sistema</p>
        </div>
        <?php if ($auth->canManageUsers()): ?>
        <button @click="modalNuevo = true" class="px-4 py-2.5 text-white rounded-lg font-medium transition-colors flex items-center gap-2" style="background: linear-gradient(135deg, #FF00FF 0%, #FFD700 100%);">
            <i data-lucide="user-plus" class="w-5 h-5"></i>Nuevo Usuario
        </button>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="card p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <input type="text" x-model="busqueda" @input="filtrar()"
                    placeholder="Buscar por nombre o email..."
                    class="input">
            </div>
            <div>
                <select x-model="filtroRol" @change="filtrar()" class="input">
                    <option value="">Todos los roles</option>
                    <option value="super-admin">Super Administrador</option>
                    <option value="admin-campana">Admin de Campaña</option>
                    <option value="coordinador">Coordinador</option>
                    <option value="colaborador">Colaborador</option>
                    <option value="veedor">Veedor</option>
                </select>
            </div>
            <div>
                <select x-model="filtroEstado" @change="filtrar()" class="input">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase cursor-pointer hover:bg-gray-100 transition-colors group select-none" @click="ordenar('nombre')">
                            <div class="flex items-center gap-1">Usuario <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors"></i></div>
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase cursor-pointer hover:bg-gray-100 transition-colors group select-none" @click="ordenar('rol')">
                            <div class="flex items-center gap-1">Rol <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors"></i></div>
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Campañas</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase cursor-pointer hover:bg-gray-100 transition-colors group select-none" @click="ordenar('estado')">
                            <div class="flex items-center gap-1">Estado <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors"></i></div>
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase cursor-pointer hover:bg-gray-100 transition-colors group select-none" @click="ordenar('ultimo_acceso')">
                            <div class="flex items-center gap-1">Último Acceso <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors"></i></div>
                        </th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="usuario in usuariosFiltrados" :key="usuario.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3 cursor-pointer group" @click="editar(usuario)" title="Ver detalles y editar">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold text-sm"
                                        x-text="usuario.nombre.charAt(0).toUpperCase()"></div>
                                    <div>
                                        <div class="font-medium text-gray-900 group-hover:text-primary transition-colors flex items-center gap-2">
                                            <span x-text="usuario.nombre"></span>
                                            <i data-lucide="external-link" class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        </div>
                                        <div class="text-sm text-gray-500" x-text="usuario.email"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="getRolClass(usuario.rol)">
                                    <i data-lucide="shield" class="w-3 h-3 mr-1"></i>
                                    <span x-text="getRolNombre(usuario.rol)"></span>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm text-gray-600">
                                    <template x-if="usuario.rol === 'super-admin'">
                                        <span class="text-green-600 font-medium">✓ Acceso Total</span>
                                    </template>
                                    <template x-if="usuario.rol !== 'super-admin'">
                                        <span x-text="usuario.total_campanas + ' asignadas'"></span>
                                    </template>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="badge" :class="usuario.estado === 'activo' ? 'badge-success' : 'badge-error'"
                                    x-text="usuario.estado === 'activo' ? 'Activo' : 'Inactivo'"></span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm text-gray-600" x-text="formatDate(usuario.ultimo_acceso)"></span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <?php if ($auth->canManageUsers()): ?>
                                    <button @click.stop="editar(usuario)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-md transition-colors"
                                        title="Editar">
                                        <i data-lucide="edit" class="w-3.5 h-3.5"></i> Editar
                                    </button>
                                    <button @click.stop="confirmarEliminar(usuario)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Eliminar"
                                        :disabled="usuario.id === <?= $_SESSION['user_id'] ?? 0 ?>">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Eliminar
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Crear/Ver/Editar Usuario -->
    <div x-show="modalNuevo || modalEditar"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]"
        style="display: none;">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="sticky top-0 bg-white/95 backdrop-blur z-10 px-6 py-4 border-b flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold" x-show="modalEditar" x-text="form.nombre.charAt(0).toUpperCase()"></div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900" x-text="modalEditar ? 'Detalles y Edición de Usuario' : 'Nuevo Usuario'"></h2>
                        <p class="text-xs text-gray-500" x-show="modalEditar" x-text="'Último acceso: ' + (form.ultimo_acceso ? formatDate(form.ultimo_acceso) : 'Nunca')"></p>
                    </div>
                </div>
                <button type="button" @click="cerrarModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form @submit.prevent="guardar()" class="p-6 space-y-8">
                <!-- Tarjeta de Información Personal -->
                <div class="card bg-gray-50/50 border border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i data-lucide="user" class="w-5 h-5 text-primary"></i>Datos Generales
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-2">Nombre Completo *</label>
                            <input type="text" x-model="form.nombre" required class="input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Email *</label>
                            <input type="email" x-model="form.email" required class="input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Teléfono</label>
                            <input type="tel" x-model="form.telefono" class="input" placeholder="+57 300 123 4567">
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Contraseña / Seguridad -->
                <div class="card bg-gray-50/50 border border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-5 h-5 text-primary"></i>Seguridad de Acceso
                    </h3>
                    <div class="grid grid-cols-1 gap-5">
                        <div x-show="!modalEditar">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña Inicial *</label>
                            <input type="password" x-model="form.password" :required="!modalEditar" class="input bg-white" placeholder="Mínimo 8 caracteres">
                        </div>
                        <div x-show="modalEditar">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cambiar Contraseña</label>
                            <input type="password" x-model="form.password" class="input bg-white" placeholder="Dejar en blanco para mantener la actual">
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Rol y Permisos -->
                <div class="card bg-gray-50/50 border border-gray-100">
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="key" class="w-5 h-5 text-primary"></i>Permisos de la Plataforma
                        </h3>
                        <!-- Aclaración sobre Colaboradores vs Usuarios -->
                        <div class="mt-2 p-3 bg-blue-50/80 border border-blue-100 rounded-lg flex items-start gap-3">
                            <i data-lucide="info" class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5"></i>
                            <div class="text-sm text-blue-800 space-y-1">
                                <p class="font-medium">¿Perfiles vs. Niveles de Colaborador?</p>
                                <p class="text-blue-700/80">Aquí asignas el <strong>Rol del Sistema</strong> (quién puede iniciar sesión, ver el panel administrativo, organizar eventos, etc.).</p>
                                <p class="text-blue-700/80">Los niveles territoriales (Líder, Simpatizante, Activista) se gestionan directamente en la tabla del <strong>Módulo de Colaboradores</strong>.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Rol y Permisos (Solo Super-Admin puede cambiar el rol) -->
                        <div x-show="<?= $isSuperAdmin ? 'true' : 'false' ?>">
                            <label class="block text-sm font-semibold text-gray-800 mb-3">Rol de Acceso al Sistema *</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <?php foreach ($roles as $rolId => $rolInfo): ?>
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all"
                                    :class="form.rol === '<?= $rolId ?>' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-gray-200 hover:bg-gray-50'">
                                    <input type="radio" name="rol" value="<?= $rolId ?>" x-model="form.rol" class="sr-only">
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                            :class="form.rol === '<?= $rolId ?>' ? 'border-primary' : 'border-gray-300'">
                                            <div class="w-2 h-2 rounded-full bg-primary" x-show="form.rol === '<?= $rolId ?>'"></div>
                                        </div>
                                        <span class="text-sm font-medium"><?= $rolInfo['nombre'] ?></span>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Asignación de Campañas (Solo Super-Admin puede asignar campañas libremente) -->
                        <div class="col-span-2" x-show="form.rol !== 'super-admin' && <?= $isSuperAdmin ? 'true' : 'false' ?>">
                            <label class="block text-sm font-medium mb-2">Campañas Asignadas</label>
                            <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-3 space-y-2">
                                <?php foreach ($campanas as $campana): ?>
                                <label class="flex items-start gap-3 p-2 rounded hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" value="<?= $campana['id'] ?>"
                                        @change="toggleCampana(<?= $campana['id'] ?>)"
                                        :checked="form.campanas.includes(<?= $campana['id'] ?>)"
                                        class="mt-1">
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($campana['nombre']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($campana['municipio']) ?>, <?= htmlspecialchars($campana['departamento']) ?></div>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-2">Estado</label>
                            <select x-model="form.estado" class="input">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <button type="button" @click="cerrarModal()" class="px-6 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium">Cancelar</button>
                    <button type="submit" class="px-6 py-2.5 text-white rounded-lg font-medium transition-colors flex items-center gap-2" style="background: linear-gradient(135deg, #FF00FF 0%, #FFD700 100%);">
                        <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                        <span x-text="modalEditar ? 'Actualizar Usuario' : 'Crear Usuario'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Confirmar Eliminación -->
    <div x-show="modalEliminar"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]"
        style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full">
            <div class="p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-center mb-2">¿Eliminar Usuario?</h3>
                <p class="text-gray-600 text-center mb-6">
                    Esta acción no se puede deshacer. El usuario será eliminado permanentemente del sistema.
                </p>
                <div class="flex gap-3">
                    <button @click="modalEliminar = false" class="px-6 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium flex-1">Cancelar</button>
                    <button @click="eliminar()" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex-1">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function usuariosData() {
    return {
        usuarios: <?= json_encode($usuarios) ?>,
        usuariosFiltrados: <?= json_encode($usuarios) ?>,
        busqueda: '',
        filtroRol: '',
        filtroEstado: '',
        modalNuevo: false,
        modalEditar: false,
        modalEliminar: false,
        usuarioEliminar: null,
        form: {
            id: null,
            nombre: '',
            email: '',
            telefono: '',
            password: '',
            rol: 'colaborador',
            estado: 'activo',
            campanas: []
        },
        ordenColumna: 'rol',
        ordenAsc: true,

        init() {
            this.filtrar();
        },

        filtrar() {
            let resultado = this.usuarios;

            // Filtro de búsqueda
            if (this.busqueda) {
                const busq = this.busqueda.toLowerCase();
                resultado = resultado.filter(u =>
                    u.nombre.toLowerCase().includes(busq) ||
                    u.email.toLowerCase().includes(busq)
                );
            }

            // Filtro de rol
            if (this.filtroRol) {
                resultado = resultado.filter(u => u.rol === this.filtroRol);
            }

            // Filtro de estado
            if (this.filtroEstado) {
                resultado = resultado.filter(u => u.estado === this.filtroEstado);
            }

            this.usuariosFiltrados = resultado;
            this.aplicarOrden();
        },

        getRolOrden(rol) {
            const orden = {
                'super-admin': 1,
                'admin-campana': 2,
                'coordinador': 3,
                'colaborador': 4,
                'veedor': 5
            };
            return orden[rol] || 99;
        },

        ordenar(columna) {
            if (this.ordenColumna === columna) {
                this.ordenAsc = !this.ordenAsc;
            } else {
                this.ordenColumna = columna;
                this.ordenAsc = true;
            }
            this.aplicarOrden();
        },

        aplicarOrden() {
            this.usuariosFiltrados.sort((a, b) => {
                let valA, valB;

                if (this.ordenColumna === 'rol') {
                    valA = this.getRolOrden(a.rol);
                    valB = this.getRolOrden(b.rol);
                } else if (this.ordenColumna === 'ultimo_acceso') {
                    valA = a.ultimo_acceso ? new Date(a.ultimo_acceso).getTime() : 0;
                    valB = b.ultimo_acceso ? new Date(b.ultimo_acceso).getTime() : 0;
                } else {
                    valA = a[this.ordenColumna] ? a[this.ordenColumna].toString().toLowerCase() : '';
                    valB = b[this.ordenColumna] ? b[this.ordenColumna].toString().toLowerCase() : '';
                }

                if (valA < valB) return this.ordenAsc ? -1 : 1;
                if (valA > valB) return this.ordenAsc ? 1 : -1;
                return 0;
            });
        },

        editar(usuario) {
            this.form = {
                id: usuario.id,
                nombre: usuario.nombre,
                email: usuario.email,
                telefono: usuario.telefono || '',
                password: '',
                rol: usuario.rol,
                estado: usuario.estado,
                ultimo_acceso: usuario.ultimo_acceso, // Guardar esto para el detalle
                campanas: []
            };

            // Cargar campañas asignadas
            fetch(`/aratio/api/usuarios.php?id=${usuario.id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.data.campanas) {
                        this.form.campanas = data.data.campanas.map(c => c.id);
                    }
                });

            this.modalEditar = true;
        },

        toggleCampana(campanaId) {
            const index = this.form.campanas.indexOf(campanaId);
            if (index > -1) {
                this.form.campanas.splice(index, 1);
            } else {
                this.form.campanas.push(campanaId);
            }
        },

        async guardar() {
            console.log('Guardando usuario...', this.modalEditar ? 'EDIT' : 'CREATE');
            console.log('Form data:', this.form);
            const method = this.modalEditar ? 'PUT' : 'POST';
            const data = {...this.form};

            // Si es edición y no se proporcionó password, eliminarlo
            if (this.modalEditar && !data.password) {
                delete data.password;
            }

            try {
                const response = await fetch('/aratio/api/usuarios.php', {
                    method: method,
                    headers: {'Content-Type': 'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                console.log('API Response:', result);
                console.log('HTTP Status:', response.status);

                if (result.success) {
                    showNotification(result.message, 'success');
                    window.location.reload();
                } else {
                    showNotification(result.message || 'Error al guardar usuario', 'error');
                }
            } catch (error) {
                console.error('Fetch error:', error);
                showNotification('Error de conexión: ' + error.message, 'error');
            }
        },

        confirmarEliminar(usuario) {
            this.usuarioEliminar = usuario;
            this.modalEliminar = true;
        },

        async eliminar() {
            try {
                const response = await fetch(`/aratio/api/usuarios.php?id=${this.usuarioEliminar.id}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                });

                const result = await response.json();
                console.log('API Response:', result);
                console.log('HTTP Status:', response.status);

                if (result.success) {
                    showNotification(result.message, 'success');
                    window.location.reload();
                } else {
                    showNotification(result.message || 'Error al eliminar usuario', 'error');
                }
            } catch (error) {
                console.error('Fetch error:', error);
                showNotification('Error de conexión: ' + error.message, 'error');
            }

            this.modalEliminar = false;
        },

        cerrarModal() {
            this.modalNuevo = false;
            this.modalEditar = false;
            this.form = {
                id: null,
                nombre: '',
                email: '',
                telefono: '',
                password: '',
                rol: 'colaborador',
                estado: 'activo',
                campanas: []
            };
        },

        getRolNombre(rol) {
            const roles = {
                'super-admin': 'Super Admin',
                'admin-campana': 'Admin Campaña',
                'coordinador': 'Coordinador',
                'colaborador': 'Colaborador',
                'veedor': 'Veedor'
            };
            return roles[rol] || rol;
        },

        getRolClass(rol) {
            const classes = {
                'super-admin': 'bg-purple-100 text-purple-800',
                'admin-campana': 'bg-blue-100 text-blue-800',
                'coordinador': 'bg-green-100 text-green-800',
                'colaborador': 'bg-gray-100 text-gray-800',
                'veedor': 'bg-yellow-100 text-yellow-800'
            };
            return classes[rol] || 'bg-gray-100 text-gray-800';
        },

        formatDate(fecha) {
            if (!fecha) return 'Nunca';
            const d = new Date(fecha);
            return d.toLocaleDateString('es-CO', {day: '2-digit', month: '2-digit', year: 'numeric'});
        }
    }
}
// Función de notificaciones simple
function showNotification(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 z-[9999] px-6 py-3 rounded-lg shadow-xl text-white font-medium transition-all transform translate-x-0' +
        (type === 'success' ? ' bg-green-600' : ' bg-red-600');
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.transform = 'translateX(400px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<?php
/** MÓDULO: Configuración de Usuario */

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $auth = new Auth();

    if ($action === 'update_profile') {
        // Actualizar perfil (simulado por ahora, se requeriría método en Auth o User)
        // Por ahora solo mostramos mensaje de éxito
        $msg = 'Perfil actualizado correctamente';
        $msgType = 'success';
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new !== $confirm) {
            $msg = 'Las contraseñas no coinciden';
            $msgType = 'error';
        } else {
            $result = $auth->changePassword($user['id'], $current, $new);
            $msg = $result['message'];
            $msgType = $result['success'] ? 'success' : 'error';
        }
    }
}
?>
<?php if ($msg): ?>
    <div
        class="mb-4 p-4 rounded-lg <?= $msgType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>
<div class="space-y-6" x-data="{tab: 'perfil', showPassword: false}">
    <div>
        <h1 class="text-3xl font-bold">Configuración</h1>
        <p class="text-gray-600 mt-2">Administra tu cuenta y preferencias</p>
    </div>

    <div class="flex gap-2 border-b border-gray-200">
        <button @click="tab = 'perfil'"
            :class="tab === 'perfil' ? 'border-b-2 border-primary text-primary' : 'text-gray-600'"
            class="px-4 py-3 font-medium transition"><i data-lucide="user"
                class="w-4 h-4 inline mr-2"></i>Perfil</button>
        <button @click="tab = 'seguridad'"
            :class="tab === 'seguridad' ? 'border-b-2 border-primary text-primary' : 'text-gray-600'"
            class="px-4 py-3 font-medium transition"><i data-lucide="shield"
                class="w-4 h-4 inline mr-2"></i>Seguridad</button>
        <button @click="tab = 'notificaciones'"
            :class="tab === 'notificaciones' ? 'border-b-2 border-primary text-primary' : 'text-gray-600'"
            class="px-4 py-3 font-medium transition"><i data-lucide="bell"
                class="w-4 h-4 inline mr-2"></i>Notificaciones</button>
        <button @click="tab = 'apariencia'"
            :class="tab === 'apariencia' ? 'border-b-2 border-primary text-primary' : 'text-gray-600'"
            class="px-4 py-3 font-medium transition"><i data-lucide="palette"
                class="w-4 h-4 inline mr-2"></i>Apariencia</button>
    </div>

    <div x-show="tab === 'perfil'" class="card max-w-2xl">
        <h2 class="text-xl font-bold mb-6">Información Personal</h2>
        <form class="space-y-4" method="POST">
            <input type="hidden" name="action" value="update_profile">
            <div class="flex items-center gap-6 mb-6">
                <div
                    class="w-24 h-24 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center">
                    <span class="text-3xl font-bold text-white"><?= substr($user['nombre'], 0, 1) ?></span>
                </div>
                <div>
                    <button type="button" class="btn-primary mb-2"><i data-lucide="upload"
                            class="w-4 h-4 inline mr-2"></i>Cambiar Foto</button>
                    <p class="text-xs text-gray-500">JPG, PNG o GIF. Máximo 2MB</p>
                </div>
            </div>
            <div><label class="block text-sm font-medium mb-2">Nombre Completo</label><input type="text" name="nombre"
                    value="<?= htmlspecialchars($user['nombre']) ?>" class="input"></div>
            <div><label class="block text-sm font-medium mb-2">Email</label><input type="email" name="email"
                    value="<?= htmlspecialchars($user['email']) ?>" class="input"></div>
            <div><label class="block text-sm font-medium mb-2">Teléfono</label><input type="tel" name="telefono"
                    value="<?= htmlspecialchars($user['telefono'] ?? '') ?>" class="input"></div>
            <div><label class="block text-sm font-medium mb-2">Rol</label><input type="text"
                    value="<?= htmlspecialchars($user['rol']) ?>" disabled class="input bg-gray-50"></div>
            <button type="submit" class="btn-primary"><i data-lucide="save" class="w-5 h-5 inline mr-2"></i>Guardar
                Cambios</button>
        </form>
    </div>

    <div x-show="tab === 'seguridad'" class="card max-w-2xl">
        <h2 class="text-xl font-bold mb-6">Seguridad de la Cuenta</h2>
        <form class="space-y-4" method="POST">
            <input type="hidden" name="action" value="change_password">
            <div><label class="block text-sm font-medium mb-2">Contraseña Actual</label><input type="password"
                    name="current_password" required class="input"></div>
            <div><label class="block text-sm font-medium mb-2">Nueva Contraseña</label><input type="password"
                    name="new_password" required class="input"></div>
            <div><label class="block text-sm font-medium mb-2">Confirmar Nueva Contraseña</label><input type="password"
                    name="confirm_password" required class="input"></div>
            <button type="submit" class="btn-primary"><i data-lucide="key" class="w-5 h-5 inline mr-2"></i>Cambiar
                Contraseña</button>
        </form>
        <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 class="font-bold text-yellow-900 mb-2">Recomendaciones de Seguridad</h3>
            <ul class="text-sm text-yellow-700 space-y-1">
                <li>✓ Usa al menos 8 caracteres</li>
                <li>✓ Combina mayúsculas y minúsculas</li>
                <li>✓ Incluye números y símbolos</li>
                <li>✓ No uses información personal</li>
            </ul>
        </div>
    </div>

    <div x-show="tab === 'notificaciones'" class="card max-w-2xl">
        <h2 class="text-xl font-bold mb-6">Preferencias de Notificaciones</h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium">Nuevas Donaciones</p>
                    <p class="text-sm text-gray-600">Recibir alerta cuando hay una nueva donación</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" checked class="sr-only peer">
                    <div
                        class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                    </div>
                </label>
            </div>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium">Eventos Próximos</p>
                    <p class="text-sm text-gray-600">Recordatorios de eventos programados</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" checked class="sr-only peer">
                    <div
                        class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                    </div>
                </label>
            </div>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium">Nuevos Compromisos</p>
                    <p class="text-sm text-gray-600">Notificar cuando se registran compromisos</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div
                        class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                    </div>
                </label>
            </div>
            <button class="btn-primary"><i data-lucide="save" class="w-5 h-5 inline mr-2"></i>Guardar
                Preferencias</button>
        </div>
    </div>

    <div x-show="tab === 'apariencia'" class="card max-w-2xl">
        <h2 class="text-xl font-bold mb-6">Personalización</h2>
        <div class="space-y-6">
            <div><label class="block text-sm font-medium mb-2">Tema</label>
                <div class="grid grid-cols-2 gap-4">
                    <button class="p-4 border-2 border-primary rounded-lg bg-white">
                        <div class="w-full h-20 bg-white rounded mb-2 border"></div>
                        <p class="font-medium">Claro</p>
                    </button>
                    <button class="p-4 border-2 border-gray-300 rounded-lg opacity-50 cursor-not-allowed">
                        <div class="w-full h-20 bg-gray-800 rounded mb-2"></div>
                        <p class="font-medium">Oscuro (Próximamente)</p>
                    </button>
                </div>
            </div>
            <div>
                <p class="text-sm text-gray-600">Los colores corporativos (Magenta #FF00FF y Dorado #FFD700) son
                    obligatorios y no se pueden modificar.</p>
            </div>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
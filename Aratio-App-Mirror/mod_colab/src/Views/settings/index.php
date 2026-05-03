<?php
/**
 * Vista de Configuración
 */
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Configuración del Sistema</h1>
            <p class="text-gray-600 mt-1">Estado del entorno y variables de configuración</p>
        </div>
        <div class="flex gap-3">
            <button onclick="location.reload()" class="btn btn-outline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refrescar
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Información del Sistema -->
        <div class="card">
            <div class="card-header bg-gray-50">
                <h2 class="font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Estado del Sistema
                </h2>
            </div>
            <div class="card-body p-0">
                <table class="min-w-full divide-y divide-gray-200">
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 bg-gray-50 w-1/3">PHP Version</td>
                            <td class="px-6 py-3 text-sm text-gray-900"><?= $system_info['php_version'] ?></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 bg-gray-50">Base de Datos</td>
                            <td class="px-6 py-3 text-sm">
                                <?php if ($system_info['db_status']): ?>
                                    <span class="text-green-600 flex items-center">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span> Conectado
                                    </span>
                                <?php else: ?>
                                    <span class="text-red-600 flex items-center">
                                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span> Error de conexión
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 bg-gray-50">Ambiente</td>
                            <td class="px-6 py-3 text-sm text-gray-900 capitalize"><?= $system_info['app_env'] ?></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 bg-gray-50">Timezone</td>
                            <td class="px-6 py-3 text-sm text-gray-900"><?= $system_info['timezone'] ?></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 bg-gray-50">Max Upload</td>
                            <td class="px-6 py-3 text-sm text-gray-900"><?= $system_info['max_upload'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Configuración API -->
        <div class="card">
            <div class="card-header bg-gray-50">
                <h2 class="font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Integración API
                </h2>
            </div>
            <div class="card-body p-0">
                <table class="min-w-full divide-y divide-gray-200">
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 bg-gray-50 w-1/3">Estado API</td>
                            <td class="px-6 py-3 text-sm text-gray-900">
                                <?= $api_config['enabled'] ? '<span class="badge bg-green-100 text-green-800">Activa</span>' : '<span class="badge bg-red-100 text-red-800">Inactiva</span>' ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 bg-gray-50">Versión</td>
                            <td class="px-6 py-3 text-sm text-gray-900"><?= $api_config['version'] ?></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 bg-gray-50">API Key</td>
                            <td class="px-6 py-3 text-sm text-gray-900">
                                <code class="bg-gray-100 px-2 py-1 rounded text-xs"><?= $api_config['key'] ?></code>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="p-4 bg-purple-50 text-xs text-purple-700">
                    <p><strong>Nota:</strong> Estas configuraciones se administran desde el archivo <code>.env</code> para mayor seguridad.</p>
                </div>
            </div>
        </div>

        <!-- Mantenimiento -->
        <div class="card md:col-span-2">
            <div class="card-header bg-gray-50">
                <h2 class="font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Herramientas de Mantenimiento
                </h2>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <form action="/settings/clear-cache" method="POST">
                        <button type="submit" class="w-full btn btn-outline border-orange-200 hover:bg-orange-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Limpiar Caché
                        </button>
                    </form>
                    <form action="/settings/cleanup-logs" method="POST">
                        <button type="submit" class="w-full btn btn-outline">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Depurar Logs
                        </button>
                    </form>
                    <form action="/settings/test-email" method="POST">
                        <button type="submit" class="w-full btn btn-outline">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Prueba de Email
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


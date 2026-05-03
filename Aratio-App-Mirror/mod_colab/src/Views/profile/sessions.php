<?php
/**
 * Vista de Sesiones Activas
 */

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Sesiones Activas</h1>
                <p class="text-gray-600">Gestiona las sesiones activas de tu cuenta</p>
            </div>
            <a href="/profile" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                ← Volver al Perfil
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">
                Sesiones Activas (<?= count($activeSessions) ?>)
            </h2>
        </div>

        <div class="divide-y divide-gray-200">
            <?php if (empty($activeSessions)): ?>
                <div class="px-6 py-8 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay sesiones activas</h3>
                    <p class="mt-1 text-sm text-gray-500">No tienes sesiones activas en este momento.</p>
                </div>
            <?php else: ?>
                <?php foreach ($activeSessions as $session): ?>
                    <div class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <?php if ($session['token_sesion'] === session_id()): ?>
                                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php if ($session['token_sesion'] === session_id()): ?>
                                                Sesión Actual
                                            <?php else: ?>
                                                Sesión Activa
                                            <?php endif; ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            IP: <?= htmlspecialchars($session['ip_address']) ?> •
                                            <?= htmlspecialchars($session['user_agent']) ?> •
                                            Última actividad: <?= date('d/m/Y H:i', strtotime($session['ultima_actividad'])) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <?php if ($session['token_sesion'] !== session_id()): ?>
                                    <form method="POST" action="/profile/sessions/close/<?= $session['token_sesion'] ?>" class="inline">
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm"
                                                onclick="return confirm('¿Estás seguro de que quieres cerrar esta sesión?')">
                                            Cerrar Sesión
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs text-green-600 font-medium">Sesión Actual</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información de seguridad -->
    <div class="mt-8 bg-yellow-50 rounded-lg p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Consejos de seguridad</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Cierra las sesiones que no reconozcas</li>
                        <li>Si has perdido acceso a un dispositivo, cierra todas las sesiones activas</li>
                        <li>Revisa regularmente tus sesiones activas</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


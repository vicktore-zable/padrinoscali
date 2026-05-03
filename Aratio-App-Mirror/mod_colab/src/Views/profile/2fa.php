<?php
/**
 * Vista de Configuración 2FA
 */

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Autenticación de Dos Factores</h1>
                    <p class="text-gray-600">Añade una capa extra de seguridad a tu cuenta</p>
                </div>
                <a href="/profile" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    ← Volver al Perfil
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">
                    Estado: <?= $user['require_2fa'] ? 'Habilitado' : 'Deshabilitado' ?>
                </h2>
            </div>
            <div class="p-6">
                <?php if (!$user['require_2fa']): ?>
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Habilitar 2FA</h3>
                        <p class="text-gray-600 mb-6">
                            La autenticación de dos factores añade una capa adicional de seguridad a tu cuenta.
                            Además de tu contraseña, necesitarás un código generado por una aplicación de autenticación.
                        </p>
                        <form method="POST" action="/profile/2fa/enable">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Habilitar 2FA
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">2FA Habilitado</h3>
                        <p class="text-gray-600 mb-6">
                            La autenticación de dos factores está habilitada para tu cuenta.
                            Recibirás códigos de verificación adicionales al iniciar sesión.
                        </p>
                        <form method="POST" action="/profile/2fa/disable">
                            <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Deshabilitar 2FA
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="mt-8 bg-blue-50 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">¿Qué es la autenticación de dos factores?</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>
                            La autenticación de dos factores (2FA) es un método de seguridad que requiere dos formas diferentes de verificación
                            antes de conceder acceso a tu cuenta. Esto significa que además de tu contraseña, necesitarás un código adicional
                            generado por una aplicación de autenticación en tu teléfono.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


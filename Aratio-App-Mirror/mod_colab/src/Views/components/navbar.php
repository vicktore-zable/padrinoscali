<?php
use App\Utils\Helpers;
?>

<nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Mobile menu button -->
            <div class="flex items-center lg:hidden">
                <button @click="sidebarOpen = !sidebarOpen"
                        class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <!-- Search Bar -->
            <div class="flex-1 flex items-center justify-center px-4 lg:justify-start">
                <div class="max-w-lg w-full">
                    <form action="/colaboradores/search" method="GET" class="relative">
                        <input type="text"
                               name="q"
                               placeholder="Buscar colaboradores..."
                               class="form-input pl-10 pr-4 w-full"
                               autocomplete="off">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right side buttons -->
            <div class="flex items-center gap-4">

                <!-- Quick Actions -->
                <a href="/colaboradores/create"
                   class="hidden md:inline-flex btn btn-primary btn-sm"
                   title="Agregar Colaborador">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="ml-2">Nuevo</span>
                </a>

                <!-- Notifications -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <!-- Notification badge -->
                        <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500"></span>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition
                         class="dropdown-menu w-80">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="text-sm font-semibold text-gray-900">Notificaciones</p>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <a href="#" class="dropdown-item flex items-start py-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm text-gray-900">Nuevo colaborador registrado</p>
                                    <p class="text-xs text-gray-500 mt-1">Hace 5 minutos</p>
                                </div>
                            </a>
                        </div>
                        <div class="px-4 py-3 border-t border-gray-200">
                            <a href="/notifications" class="text-sm text-primary-600 hover:text-primary-700">
                                Ver todas las notificaciones
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode"
                        class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg x-show="!darkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                <!-- User Menu -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex items-center gap-2 text-sm focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold">
                            <?= strtoupper(substr($user['nombres'] ?? 'U', 0, 1)) ?>
                        </div>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition
                         class="dropdown-menu">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($user['nombres'] ?? 'Usuario') ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?= htmlspecialchars($user['email'] ?? '') ?>
                            </p>
                        </div>
                        <a href="/profile" class="dropdown-item">
                            Mi Perfil
                        </a>
                        <a href="/profile/sessions" class="dropdown-item">
                            Sesiones Activas
                        </a>
                        <?php if (has_permission($user['tipo_usuario'] ?? '', 'configuracion', 'view')): ?>
                        <a href="/settings" class="dropdown-item">
                            Configuración
                        </a>
                        <?php endif; ?>
                        <div class="border-t border-gray-200"></div>
                        <a href="/logout" class="dropdown-item text-red-600 hover:bg-red-50">
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<?php
use App\Utils\Security;

$currentPath = $_SERVER['REQUEST_URI'];
$userRole = $user['tipo_usuario'] ?? '';

// Función auxiliar para determinar si una ruta está activa
$isActive = function($path) use ($currentPath) {
    return strpos($currentPath, $path) === 0;
};
?>

<!-- Mobile sidebar backdrop -->
<div x-show="sidebarOpen"
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-gray-600 bg-opacity-75 z-20 lg:hidden"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
</div>

<!-- Sidebar -->
<div class="sidebar transform -translate-x-full lg:translate-x-0"
     :class="{ 'translate-x-0': sidebarOpen }"
     x-transition>

    <div class="sidebar-nav">
        <!-- Logo -->
        <div class="flex items-center justify-between mb-6 px-2">
            <div>
                <h2 class="text-xl font-bold text-white">A Ratio</h2>
                <p class="text-xs text-gray-400">Sistema de Colaboradores</p>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- User Info -->
        <div class="mb-6 p-3 bg-gray-700 rounded-lg">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold">
                    <?= strtoupper(substr($user['nombres'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white"><?= htmlspecialchars($user['nombres'] ?? 'Usuario') ?></p>
                    <p class="text-xs text-gray-400"><?= htmlspecialchars($user['tipo_usuario'] ?? '') ?></p>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-1">

            <!-- Dashboard -->
            <a href="/dashboard" class="sidebar-link <?= $isActive('/dashboard') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <!-- Colaboradores -->
            <a href="/colaboradores" class="sidebar-link <?= $isActive('/colaboradores') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Colaboradores
            </a>

            <!-- Reportes -->
            <a href="/reports" class="sidebar-link <?= $isActive('/reports') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Reportes
            </a>

            <?php
            // Lógica de enlace para el Módulo Día D
            $rolesDiaD_Dashboard = ['supervisor_diad', 'admin_diad', 'supervisor', 'admin', 'super-admin'];
            $linkDiaD = in_array($userRole, $rolesDiaD_Dashboard) ? '/dashboard-diaD' : '/reporte-diaD';
            ?>
            <!-- Módulo Día D -->
            <a href="<?= $linkDiaD ?>" class="sidebar-link <?= $isActive('/reporte-diaD') || $isActive('/dashboard-diaD') || $isActive('/mod_diaD') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Módulo Día D
            </a>

            <?php if (has_permission($userRole, 'usuarios', 'view')): ?>
            <!-- Usuarios (Solo Admin) -->
            <a href="/usuarios" class="sidebar-link <?= $isActive('/usuarios') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Usuarios
            </a>
            <?php endif; ?>

            <?php if (has_permission($userRole, 'logs', 'view')): ?>
            <!-- Logs (Solo Admin) -->
            <a href="/logs" class="sidebar-link <?= $isActive('/logs') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Logs
            </a>
            <?php endif; ?>

            <!-- Divider -->
            <div class="border-t border-gray-700 my-3"></div>

            <!-- Mi Perfil -->
            <a href="/profile" class="sidebar-link <?= $isActive('/profile') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Mi Perfil
            </a>

            <?php if (has_permission($userRole, 'configuracion', 'view')): ?>
            <!-- Configuración (Solo Admin) -->
            <a href="/settings" class="sidebar-link <?= $isActive('/settings') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Configuración
            </a>
            <?php endif; ?>

            <!-- Logout -->
            <a href="/logout" class="sidebar-link text-red-400 hover:bg-red-900 hover:text-white">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Cerrar Sesión
            </a>
        </nav>
    </div>
</div>

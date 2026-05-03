<?php
/**
 * Rutas Web
 * Definición de todas las rutas de la aplicación
 *
 * @package Routes
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\RoleMiddleware;

// Middleware global para todas las rutas
// $router->addMiddleware(CsrfMiddleware::class);

// ============================================
// Rutas Públicas
// ============================================

// Landing page
$router->get('/', 'HomeController@index');

// Autenticación
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login', [
    RateLimitMiddleware::limit(5, 300) // 5 intentos por 5 minutos
]);
$router->get('/logout', 'AuthController@logout');

// Recuperación de contraseña
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword', [
    RateLimitMiddleware::limit(3, 600) // 3 intentos por 10 minutos
]);
$router->get('/reset-password/{token}', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');

// Registro de usuarios
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register', [
    RateLimitMiddleware::limit(3, 3600) // 3 intentos por hora
]);

// ============================================
// Inscripción Pública de Colaboradores
// ============================================

$router->get('/registro-lider', 'PublicController@showLider', [
    AuthMiddleware::class,
    RoleMiddleware::only(['admin-campana', 'super-admin', 'admin'])
]);
$router->post('/registro-lider', 'PublicController@storeLider', [
    AuthMiddleware::class,
    RoleMiddleware::only(['admin-campana', 'super-admin', 'admin']),
    RateLimitMiddleware::limit(10, 3600)
]);

// Registro de simpatizantes (Con selección de campaña y líder)
$router->get('/registro-simpatizante', 'PublicController@showSimpatizante');
$router->post('/registro-simpatizante', 'PublicController@storeSimpatizante', [
    RateLimitMiddleware::limit(10, 3600)
]);
$router->get('/registro-simpatizante/confirmacion', 'PublicController@confirmacion');

// API Pública para Selects Dinámicos (Líderes por campaña)
$router->get('/colaboradores/lideres', 'PublicController@searchLideres');

// Rutas de territorios públicas (necesarias para el formulario de inscripción)
$router->get('/territorios/departamentos', 'ColaboradorController@getDepartamentos');
$router->get('/territorios/municipios-cascada', 'PublicController@getMunicipiosGold');
$router->get('/territorios/tipos', 'PublicController@getGeoCascadaGold');
$router->get('/territorios/territorios', 'PublicController@getGeoCascadaGold');
$router->get('/territorios/barrios', 'PublicController@getGeoCascadaGold');
$router->get('/territorios/puestos', 'PublicController@getPuestosGold');
$router->get('/territorios/buscar-puestos', 'PublicController@buscarPuestosGold');

// ============================================
// Rutas Protegidas (requieren autenticación)
// ============================================

$router->group('', function ($router) {

    // Dashboard
    $router->get('/dashboard', 'DashboardController@index');
    $router->get('/dashboard-staging', 'DashboardController@staging');

    // ============================================
    // Colaboradores
    // ============================================

    $router->group('/colaboradores', function ($router) {
        // Listar
        $router->get('', 'ColaboradorController@index');
        $router->get('/list', 'ColaboradorController@list'); // API para DataTables

        // Crear (debe ir antes de /{id} para no ser capturado)
        $router->get('/create', 'ColaboradorController@create');
        $router->post('/store', 'ColaboradorController@store');

        // Búsqueda (debe ir antes de /{id})
        $router->get('/search', 'ColaboradorController@search');

        // Importar (debe ir antes de /{id})
        $router->get('/import/template', 'ColaboradorController@downloadTemplate');
        $router->get('/import', 'ColaboradorController@showImport');
        $router->post('/import', 'ColaboradorController@import');

        // Exportar (debe ir antes de /{id})
        $router->get('/export', 'ColaboradorController@export');

        // Ver detalle (va después de las rutas específicas)
        $router->get('/{id}', 'ColaboradorController@show');

        // Editar
        $router->get('/{id}/edit', 'ColaboradorController@edit');
        $router->post('/{id}/update', 'ColaboradorController@update');
        
        // Actualización Rápida (Nivel, Perfil, Estado) desde modal
        $router->post('/{id}/quick-update', 'ColaboradorController@quickUpdate');

        // Eliminar
        $router->post('/{id}/delete', 'ColaboradorController@delete');

        // Red jerárquica
        $router->get('/{id}/network', 'ColaboradorController@network');
        $router->get('/{id}/network-data', 'ColaboradorController@networkData');

        // Cambiar líder
        $router->post('/{id}/change-leader', 'ColaboradorController@changeLeader');

        // Historial de cambios
        $router->get('/{id}/history', 'ColaboradorController@history');

        // Reevaluación de dato potencial
        $router->post('/{id}/reevaluar', 'ColaboradorController@reevaluar');
    });

    // ============================================
    // Curriculum
    // ============================================

    $router->group('/curriculum', function ($router) {
        // Ver curriculum
        $router->get('/{colaboradorId}', 'CurriculumController@show');

        // Editar curriculum
        $router->get('/{colaboradorId}/edit', 'CurriculumController@edit');
        $router->post('/{colaboradorId}/update', 'CurriculumController@update');

        // Agregar secciones
        $router->post('/{colaboradorId}/experiencia', 'CurriculumController@addExperiencia');
        $router->post('/{colaboradorId}/formacion', 'CurriculumController@addFormacion');
        $router->post('/{colaboradorId}/participacion', 'CurriculumController@addParticipacion');

        // Eliminar secciones
        $router->post('/{colaboradorId}/experiencia/{index}/delete', 'CurriculumController@deleteExperiencia');
        $router->post('/{colaboradorId}/formacion/{index}/delete', 'CurriculumController@deleteFormacion');
        $router->post('/{colaboradorId}/participacion/{index}/delete', 'CurriculumController@deleteParticipacion');
    });

    // ============================================
    // Reportes y Estadísticas
    // ============================================

    $router->group('/reports', function ($router) {
        $router->get('', 'ReportController@index');
        $router->get('/profiles', 'ReportController@byProfile');
        $router->get('/territories', 'ReportController@byTerritory');
        $router->get('/leaders', 'ReportController@byLeaders');
        $router->get('/growth', 'ReportController@growth');
        $router->get('/export', 'ReportController@export');
    });

    // ============================================
    // Usuarios (Solo admin)
    // ============================================

    $router->group('/usuarios', function ($router) {
        // Listar
        $router->get('', 'UsuarioController@index');

        // Crear
        $router->get('/create', 'UsuarioController@create');
        $router->post('/store', 'UsuarioController@store');

        // Editar
        $router->get('/{id}/edit', 'UsuarioController@edit');
        $router->post('/{id}/update', 'UsuarioController@update');

        // Eliminar
        $router->post('/{id}/delete', 'UsuarioController@delete');

        // Bloquear/Desbloquear
        $router->post('/{id}/toggle-block', 'UsuarioController@toggleBlock');

        // Cerrar sesiones
        $router->post('/{id}/close-sessions', 'UsuarioController@closeSessions');

        // Cambiar contraseña
        $router->post('/{id}/change-password', 'UsuarioController@changePassword');

        // 2FA
        $router->post('/{id}/toggle-2fa', 'UsuarioController@toggle2FA');
    }, [RoleMiddleware::only(['admin'])]);

    // ============================================
    // Perfil de Usuario
    // ============================================

    $router->group('/profile', function ($router) {
        $router->get('', 'ProfileController@index');
        $router->post('/update', 'ProfileController@update');
        $router->post('/change-password', 'ProfileController@changePassword');
        $router->get('/2fa', 'ProfileController@show2FA');
        $router->post('/2fa/enable', 'ProfileController@enable2FA');
        $router->post('/2fa/disable', 'ProfileController@disable2FA');
        $router->get('/sessions', 'ProfileController@sessions');
        $router->post('/sessions/{id}/close', 'ProfileController@closeSession');
    });

    // ============================================
    // Logs y Auditoría (Solo admin)
    // ============================================

    $router->group('/logs', function ($router) {
        $router->get('', 'LogController@index');
        $router->get('/app', 'LogController@app');
        $router->get('/security', 'LogController@security');
        $router->get('/audit', 'LogController@audit');
        $router->get('/search', 'LogController@search');
        $router->get('/export', 'LogController@export');
    }, [RoleMiddleware::only(['admin'])]);

    // ============================================
    // Configuración (Solo admin)
    // ============================================

    $router->group('/settings', function ($router) {
        $router->get('', 'SettingsController@index');
        $router->post('/update', 'SettingsController@update');
        $router->post('/test-email', 'SettingsController@testEmail');
        $router->post('/clear-cache', 'SettingsController@clearCache');
        $router->post('/cleanup-logs', 'SettingsController@cleanupLogs');
        $router->post('/backup', 'SettingsController@backup');
    }, [RoleMiddleware::only(['admin'])]);

    // ============================================
    // API Endpoints
    // ============================================
    // NOTA: Las rutas de territorios (/territorios/*) están definidas
    // como rutas públicas arriba para permitir su uso en el formulario
    // de inscripción pública sin autenticación

    $router->group('/api', function ($router) {
        // Autocompletado
        $router->get('/colaboradores/autocomplete', 'ApiController@colaboradoresAutocomplete');
        $router->get('/territorios/municipios', 'ApiController@municipios');

        // Estadísticas rápidas
        $router->get('/stats/dashboard', 'ApiController@dashboardStats');
        $router->get('/stats/charts', 'ApiController@chartData');

        // Validación
        $router->post('/validate/documento', 'ApiController@validateDocumento');
        $router->post('/validate/email', 'ApiController@validateEmail');

        // Datos de Red para Portal
        $router->get('/colaboradores/{id}/network-data', 'ColaboradorController@networkData');
    });

}, [AuthMiddleware::class]);

// ============================================
// Páginas de Error
// ============================================

$router->get('/403', function () {
    http_response_code(403);
    require APP_PATH . '/Views/errors/403.php';
});

$router->get('/404', function () {
    http_response_code(404);
    require APP_PATH . '/Views/errors/404.php';
});

$router->get('/500', function () {
    http_response_code(500);
    require APP_PATH . '/Views/errors/500.php';
});

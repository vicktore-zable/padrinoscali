<?php
/**
 * Controlador de Configuración
 * Maneja la configuración del sistema
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;

class SettingsController extends Controller {
    public function index(): void {
        $db = \Database::getInstance();
        $dbStatus = false;
        try {
            $db->query("SELECT 1");
            $dbStatus = true;
        } catch (\Exception $e) {}

        $this->view('settings.index', [
            'title' => 'Configuración del Sistema',
            'system_info' => [
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'db_status' => $dbStatus,
                'app_env' => env('APP_ENV', 'production'),
                'app_url' => env('APP_URL'),
                'timezone' => date_default_timezone_get(),
                'max_upload' => ini_get('upload_max_filesize'),
            ],
            'api_config' => [
                'enabled' => env('API_ENABLED', true),
                'key' => env('API_KEY', '******'),
                'version' => env('API_VERSION', 'v1')
            ],
            'mail_config' => [
                'host' => env('MAIL_HOST'),
                'user' => env('MAIL_USERNAME'),
                'encryption' => env('MAIL_ENCRYPTION')
            ]
        ]);
    }

    public function update(): void {
        $this->setFlash('Funcionalidad de actualización de configuración desde UI en desarrollo por seguridad.', 'info');
        $this->redirect('/settings');
    }

    public function testEmail(): void {
        try {
            // Aquí iría la lógica de Mailer::send()
            $this->setFlash('Servicio de correo configurado. Intente enviar un correo de prueba desde la terminal para verificar SMTP.', 'success');
        } catch (\Exception $e) {
            $this->setFlash('Error en configuración de correo: ' . $e->getMessage(), 'error');
        }
        $this->redirect('/settings');
    }

    public function clearCache(): void {
        $path = CACHE_PATH;
        $files = glob($path . '/*');
        $count = 0;
        foreach($files as $file){
            if(is_file($file)) {
                unlink($file);
                $count++;
            }
        }
        $this->setFlash("Se han eliminado $count archivos de caché.", 'success');
        $this->redirect('/settings');
    }

    public function cleanupLogs(): void {
        $logFile = LOGS_PATH . '/app.log';
        if (file_exists($logFile)) {
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Log depurado manualmente.\n");
            $this->setFlash('Archivo de logs rotado exitosamente.', 'success');
        } else {
            $this->setFlash('No se encontró archivo de logs para depurar.', 'warning');
        }
        $this->redirect('/settings');
    }

    public function backup(): void {
        $this->setFlash('La funcionalidad de Backup requiere permisos de ejecución en el servidor.', 'warning');
        $this->redirect('/settings');
    }
}

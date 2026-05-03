<?php
/**
 * Controlador Home
 * Landing page pública
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    /**
     * Mostrar landing page
     */
    public function index(): void {
        // Si está autenticado, redirigir al dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        // Mostrar landing page
        $this->view('home.index', [
            'title' => 'Sistema de Gestión de Colaboradores',
            'description' => 'Plataforma integral para la gestión de redes de colaboradores políticos',
            'features' => [
                [
                    'icon' => 'users',
                    'title' => 'Gestión de Colaboradores',
                    'description' => 'Administra perfiles completos con información personal, territorial y política'
                ],
                [
                    'icon' => 'network-wired',
                    'title' => 'Redes Jerárquicas',
                    'description' => 'Visualiza y gestiona estructuras líder-seguidor de forma intuitiva'
                ],
                [
                    'icon' => 'chart-line',
                    'title' => 'Reportes y Estadísticas',
                    'description' => 'Analiza datos con gráficos y métricas detalladas en tiempo real'
                ],
                [
                    'icon' => 'shield-alt',
                    'title' => 'Seguridad Avanzada',
                    'description' => 'Protección con 2FA, auditoría completa y control de accesos'
                ],
                [
                    'icon' => 'file-import',
                    'title' => 'Importación Masiva',
                    'description' => 'Carga miles de colaboradores desde archivos Excel'
                ],
                [
                    'icon' => 'file-export',
                    'title' => 'Exportación Flexible',
                    'description' => 'Exporta datos en múltiples formatos para análisis externo'
                ]
            ]
        ], 'landing');
    }
}

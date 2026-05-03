<?php
/**
 * Controlador de Logs
 * Maneja la visualización de logs del sistema
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;

class LogController extends Controller {
    /**
     * Obtener logs con filtros
     */
    private function getLogs(string $logType, int $page, int $perPage, ?string $level, ?string $search): array {
        $offset = ($page - 1) * $perPage;

        if ($search) {
            $logs = \App\Utils\Logger::search($search, $logType, $perPage * $page);
            $logs = array_slice($logs, $offset, $perPage);
        } elseif ($level) {
            $logs = \App\Utils\Logger::filterByLevel($level, $perPage * $page);
            $logs = array_slice($logs, $offset, $perPage);
        } else {
            $logs = \App\Utils\Logger::tail($logType, $perPage * $page);
            $logs = array_slice($logs, $offset, $perPage);
        }

        return $logs;
    }
    public function index(): void {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 50);
        $logType = $_GET['type'] ?? 'app';
        $level = $_GET['level'] ?? null;
        $search = $_GET['search'] ?? null;

        // Obtener logs
        $logs = $this->getLogs($logType, $page, $perPage, $level, $search);

        // Obtener estadísticas
        $stats = \App\Utils\Logger::getStats($logType);

        $this->view('logs.index', [
            'title' => 'Logs del Sistema',
            'logs' => $logs,
            'stats' => $stats,
            'currentPage' => $page,
            'perPage' => $perPage,
            'logType' => $logType,
            'level' => $level,
            'search' => $search,
            'totalPages' => ceil($stats['total_lines'] / $perPage)
        ]);
    }

    public function app(): void {
        $_GET['type'] = 'app';
        $this->index();
    }

    public function security(): void {
        $_GET['type'] = 'security';
        $this->index();
    }

    public function audit(): void {
        $_GET['type'] = 'security';
        $_GET['level'] = 'SECURITY';
        $this->index();
    }

    public function search(): void {
        $query = $_GET['q'] ?? '';
        $logType = $_GET['type'] ?? 'app';

        if (empty($query)) {
            $this->setFlash('Debe proporcionar un término de búsqueda', 'warning');
            $this->redirect('/logs');
        }

        $results = \App\Utils\Logger::search($query, $logType, 100);

        $this->view('logs.search', [
            'title' => 'Resultados de Búsqueda en Logs',
            'results' => $results,
            'query' => $query,
            'logType' => $logType,
            'totalResults' => count($results)
        ]);
    }

    public function export(): void {
        $logType = $_GET['type'] ?? 'app';
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $filename = sprintf('logs_%s_%s.txt', $logType, date('Y-m-d_H-i-s'));
        $filepath = sys_get_temp_dir() . '/' . $filename;

        if (\App\Utils\Logger::export($filepath, $logType, $startDate, $endDate)) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            unlink($filepath);
            exit;
        } else {
            $this->setFlash('Error al exportar los logs', 'error');
            $this->redirect('/logs');
        }
    }
}

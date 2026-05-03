<?php
/**
 * Controlador de Reportes
 * Maneja la generación y exportación de reportes
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Colaborador;

class ReportController extends Controller {
    /**
     * Modelo de Colaborador
     * @var Colaborador
     */
    private Colaborador $colaboradorModel;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->colaboradorModel = new Colaborador();
    }

    /**
     * Página principal de reportes
     */
    public function index(): void {
        $this->view('reports.index', [
            'title' => 'Reportes y Estadísticas'
        ]);
    }

    /**
     * Reporte por perfiles
     */
    public function byProfile(): void {
        try {
            $stats = $this->colaboradorModel->getEstadisticasPorPerfil();

            $this->view('reports.by-profile', [
                'title' => 'Reporte por Perfiles',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            $this->setFlash('Error al generar reporte: ' . $e->getMessage(), 'error');
            $this->redirect('/reports');
        }
    }

        /**
     * Reporte por territorios (mejorado con tableros consolidados)
     */
    public function byTerritory(): void {
        try {
            // Obtener estadísticas consolidadas por municipio
            $municipios = $this->colaboradorModel->getEstadisticasPorMunicipio();

            // Si se solicita detalles de un municipio específico
            $municipioSeleccionado = $_GET['municipio'] ?? null;
            $detallesMunicipio = null;

            if ($municipioSeleccionado) {
                $detallesMunicipio = $this->colaboradorModel->getDetallesMunicipio($municipioSeleccionado);
            }

            $this->view('reports.by-territory-v2', [
                'title' => 'Reporte por Territorios',
                'municipios' => $municipios,
                'municipioSeleccionado' => $municipioSeleccionado,
                'detalles' => $detallesMunicipio
            ]);
        } catch (\Exception $e) {
            $this->setFlash('Error al generar reporte: ' . $e->getMessage(), 'error');
            $this->redirect('/reports');
        }
    }
    /**
     * Reporte por líderes
     */
    public function byLeaders(): void {
        try {
            $leaders = $this->colaboradorModel->getTopLeaders(50);

            $this->view('reports.by-leaders-v2', [
                'title' => 'Reporte de Líderes',
                'leaders' => $leaders
            ]);
        } catch (\Exception $e) {
            $this->setFlash('Error al generar reporte: ' . $e->getMessage(), 'error');
            $this->redirect('/reports');
        }
    }

    /**
     * Reporte de crecimiento
     */
    public function growth(): void {
        try {
            // Obtener datos de crecimiento por mes
            $growth = $this->colaboradorModel->getCrecimientoMensual();

            $this->view('reports.growth', [
                'title' => 'Reporte de Crecimiento',
                'growth' => $growth
            ]);
        } catch (\Exception $e) {
            $this->setFlash('Error al generar reporte: ' . $e->getMessage(), 'error');
            $this->redirect('/reports');
        }
    }

    /**
     * Exportar reportes
     */
    public function export(): void {
        $type = $this->input('type', 'general');
        $format = $this->input('format', 'csv');

        try {
            switch ($type) {
                case 'profiles':
                    $data = $this->colaboradorModel->getEstadisticasPorPerfil();
                    $filename = 'reporte_perfiles';
                    break;

                case 'territories':
                    $data = $this->colaboradorModel->getEstadisticasPorTerritorio();
                    $filename = 'reporte_territorios';
                    break;

                case 'leaders':
                    $data = $this->colaboradorModel->getTopLeaders(100);
                    $filename = 'reporte_lideres';
                    break;

                case 'general':
                default:
                    $data = $this->colaboradorModel->getAll();
                    $filename = 'reporte_general';
                    break;
            }

            if ($format === 'csv') {
                $this->exportToCSV($data, $filename);
            } elseif ($format === 'excel') {
                $this->exportToExcel($data, $filename);
            } else {
                throw new \Exception('Formato no soportado');
            }

        } catch (\Exception $e) {
            $this->setFlash('Error al exportar: ' . $e->getMessage(), 'error');
            $this->redirect('/reports');
        }
    }

    /**
     * Exportar a CSV
     *
     * @param array $data
     * @param string $filename
     */
    private function exportToCSV(array $data, string $filename): void {
        if (empty($data)) {
            throw new \Exception('No hay datos para exportar');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezados
        fputcsv($output, array_keys($data[0]));

        // Datos
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * Exportar a Excel
     *
     * @param array $data
     * @param string $filename
     */
    private function exportToExcel(array $data, string $filename): void {
        // TODO: Implementar con PHPSpreadsheet
        // Por ahora usar CSV como fallback
        $this->exportToCSV($data, $filename);
    }
}

<?php
/**
 * Controlador de API
 * Maneja endpoints de API y servicios para integraciones externas
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Colaborador;
use App\Models\Territorio;

class ApiController extends Controller {
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
     * Endpoint para obtener colaboradores de una campaña (Integración Aratio)
     * GET /api/v1/colaboradores?api_key=xxx&campana_id=yyy
     */
    public function getColaboradoresExterno(): void {
        $campanaId = $this->input('campana_id') ?? $this->input('campaign_id');
        
        if (!$campanaId) {
            $this->json([
                'success' => false,
                'message' => 'El parámetro campana_id es requerido'
            ], 400);
            return;
        }

        $filtros = [
            'campana_id' => $campanaId
        ];

        // Obtener todos los colaboradores de esa campaña
        $colaboradores = $this->colaboradorModel->getAll(1, 1000, $filtros);

        $this->json([
            'success' => true,
            'data' => $colaboradores,
            'count' => count($colaboradores)
        ]);
    }

    /**
     * Autocompletado de colaboradores
     * GET /api/colaboradores/autocomplete?q=xxx
     */
    public function colaboradoresAutocomplete(): void {
        $query = $this->input('q');
        
        if (strlen($query) < 2) {
            $this->json(['success' => true, 'data' => []]);
            return;
        }

        // Usamos el método search que ya existe en el modelo
        $results = $this->colaboradorModel->getAll(1, 10, ['busqueda' => $query]);
        
        $data = array_map(function($col) {
            return [
                'id' => $col['id'],
                'documento' => $col['documento'],
                'nombre' => $col['nombres'] . ' ' . $col['apellidos'],
                'text' => $col['nombres'] . ' ' . $col['apellidos'] . ' (' . $col['documento'] . ')'
            ];
        }, $results);

        $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Municipios filtrados por departamento
     * GET /api/territorios/municipios?departamento=xxx
     */
    public function municipios(): void {
        $departamento = $this->input('departamento');
        
        if (!$departamento) {
            $this->json(['success' => false, 'message' => 'Departamento requerido'], 400);
            return;
        }

        $territorioModel = new Territorio();
        $data = $territorioModel->getMunicipiosByDepartamento($departamento);

        $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Estadísticas rápidas para el dashboard
     * GET /api/stats/dashboard
     */
    public function dashboardStats(): void {
        $db = \Database::getInstance();
        $stats = $db->getEstadisticasGenerales();
        
        $this->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Datos para gráficos
     * GET /api/stats/charts
     */
    public function chartData(): void {
        $tipo = $this->input('tipo', 'perfiles');
        $data = [];

        switch ($tipo) {
            case 'perfiles':
                $data = $this->colaboradorModel->getEstadisticasPorPerfil();
                break;
            case 'estados':
                // Implementar según necesidad
                break;
        }

        $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Validar documento único
     * POST /api/validate/documento
     */
    public function validateDocumento(): void {
        $documento = $this->input('documento');
        $exists = $this->colaboradorModel->getByDocumento($documento);

        $this->json([
            'success' => true,
            'available' => empty($exists)
        ]);
    }

    /**
     * Validar email único
     * POST /api/validate/email
     */
    public function validateEmail(): void {
        $email = $this->input('email');
        // Usamos findByColumn o similar si existe
        $db = \Database::getInstance();
        $exists = $db->exists('colaboradores', 'email = ?', [$email]);

        $this->json([
            'success' => true,
            'available' => !$exists
        ]);
    }

}

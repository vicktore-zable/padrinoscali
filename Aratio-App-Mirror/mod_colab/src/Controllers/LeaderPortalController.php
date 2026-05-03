<?php
/**
 * Controlador del Portal del Líder
 * Gestiona el área privada para líderes con sus estadísticas y red
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Colaborador;
use App\Models\Usuario;
use App\Models\Evento;
use App\Utils\Logger; // Si existe

class LeaderPortalController extends Controller {
    /**
     * Modelo de Colaborador
     * @var Colaborador
     */
    private Colaborador $colaboradorModel;

    /**
     * Modelo de Usuario
     * @var Usuario
     */
    private Usuario $usuarioModel;

    /**
     * Modelo de Evento
     * @var Evento
     */
    private Evento $eventoModel;

    /**
     * ID del Líder Logueado
     * @var int|null
     */
    private ?int $liderId = null;

    /**
     * Documento del Líder Logueado
     * @var string|null
     */
    private ?string $liderDocumento = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->colaboradorModel = new Colaborador();
        $this->usuarioModel = new Usuario();
        $this->eventoModel = new Evento();

        // Verificar si el usuario tiene un perfil de colaborador asociado
        if (isset($_SESSION['user']['colaborador_id']) && !empty($_SESSION['user']['colaborador_id'])) {
            $this->liderId = $_SESSION['user']['colaborador_id'];
            
            // Obtener documento para consultas
            $liderData = $this->colaboradorModel->getById($this->liderId);
            if ($liderData) {
                $this->liderDocumento = $liderData['documento'];
            }
        }
    }

    /**
     * Dashboard Principal del Líder
     */
    public function index(): void {
        // Validación de Acceso: Solo usuarios vinculados a un colaborador pueden entrar
        if (!$this->liderId) {
            // Si es admin, permitirle ver como demo o redirigir
            if ($this->isAdmin()) {
                $this->setFlash('Acceso de administrador: Vista de demostración (sin datos reales)', 'info');
                $this->redirect('/dashboard');
                return;
            }
            
            $this->setFlash('No tienes un perfil de líder asociado. Contacta al administrador.', 'error');
            $this->redirect('/dashboard');
            return;
        }

        try {
            // Obtener datos del líder
            $lider = $this->colaboradorModel->getById($this->liderId);

            // Obtener seguidores directos (Nivel 1)
            $directos = $this->colaboradorModel->getSeguidoresDirectos($this->liderId);

            // Calcular estadísticas básicas usando la nueva función recursiva
            // Esto obtiene TODOS los nodos de la red descendente
            $downline = $this->colaboradorModel->getDownlineDocumentos($this->liderDocumento);
            $totalRed = count($downline);

            // Meta Personal (Ejemplo estático, o podría venir de DB)
            $metaPersonal = 20; 
            $progreso = min(100, ($totalRed / $metaPersonal) * 100);

            // Generar Link de Referido
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $refLink = "$protocol://$host/registro-simpatizante?lider=" . $this->liderDocumento;

            // Desglose por territorio (Municipio / Barrio)
            $desgloseTerritorio = $this->colaboradorModel->getNeighborhoodBreakdown($downline);

            // Renderizar vista
            $this->view('portal.dashboard', [
                'lider' => $lider,
                'directos' => $directos,
                'totalDirectos' => count($directos),
                'totalRed' => $totalRed,
                'meta' => $metaPersonal,
                'progreso' => $progreso,
                'refLink' => $refLink,
                'desgloseTerritorio' => $desgloseTerritorio,
                'pageTitle' => 'Mi Portal de Líder'
            ], 'portal');

        } catch (\Exception $e) {
            $this->view('errors.500', ['error' => $e->getMessage()], null);
        }
    }

    /**
     * Ver Mi Red (Visualización Gráfica)
     */
    public function myNetwork(): void {
        if (!$this->liderId) {
            $this->redirect('/dashboard');
            return;
        }
        
        // Pasamos el documento raíz y el token CSRF
        $this->view('portal.network', [
            'liderId' => $this->liderId,
            'liderDocumento' => $this->liderDocumento,
            'rootDocumento' => $this->liderDocumento
        ], 'portal');
    }

    /**
     * Ver Eventos de Campaña
     */
    public function events(): void {
        if (!$this->liderId) {
            $this->redirect('/dashboard');
            return;
        }

        // Obtener campaña activa del líder si es posible, o todas
        // Por ahora obtenemos los próximos eventos
        $page = (int) ($this->input('p', 1));
        $perPage = 10;
        
        $filters = [
            'estado' => 'programado', // Solo futuros
             // 'campana_id' => ... (si tuvieramos la campaña del lider en sesión)
        ];

        // Intento obtener campaña de la sesión
        if (isset($_SESSION['campana_activa'])) {
             $filters['campana_id'] = $_SESSION['campana_activa'];
        }

        $events = $this->eventoModel->getAll($page, $perPage, $filters);
        $total = $this->eventoModel->count($filters);
        
        $this->view('portal.events', [
            'events' => $events,
            'page' => $page,
            'totalPages' => ceil($total / $perPage),
            'pageTitle' => 'Agenda de Campaña'
        ], 'portal');
    }

    /**
     * Helper para verificar si es admin
     */
    private function isAdmin(): bool {
        return isset($_SESSION['user']['tipo_usuario']) && 
               in_array($_SESSION['user']['tipo_usuario'], ['admin', 'coordinador']);
    }
}

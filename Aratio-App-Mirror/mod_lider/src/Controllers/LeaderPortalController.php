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
            
            // Obtener documento para consultas y datos para el layout
            $liderData = $this->colaboradorModel->getById($this->liderId);
            if ($liderData) {
                $this->liderDocumento = $liderData['documento'];
                $this->data['lider'] = $liderData;
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
                $this->redirect('index.php?page=dashboard');
                return;
            }
            
            $this->setFlash('No tienes un perfil de líder asociado. Contacta al administrador.', 'error');
            $this->redirect('index.php?page=dashboard');
            return;
        }

        // Manejar Acciones POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->input('action');
            if ($action === 'update_profile') {
                $this->updateProfile();
                return;
            }
            if ($action === 'update_team') {
                $this->updateTeamMember();
                return;
            }
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
            $this->redirect('?page=dashboard');
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
     * Actualiza el perfil del líder (datos propios)
     */
    public function updateProfile(): void {
        if (!$this->liderId) return;

        $email = $this->input('email');
        $telefono = $this->input('telefono');
        $password = $this->input('password');

        try {
            // 1. Datos para actualizar en colaboradores
            $updateData = [
                'telefono' => $telefono,
                'email' => $email,
                'departamento' => $this->input('departamento'),
                'municipio' => $this->input('municipio'),
                'tipo_territorio' => $this->input('tipo_territorio'),
                'territorio' => $this->input('territorio'),
                'barrio' => $this->input('barrio'),
                'puesto_votacion' => $this->input('puesto_votacion'),
                'mesa_votacion' => $this->input('mesa_votacion')
            ];

            $this->colaboradorModel->update($this->liderId, $updateData);

            // 2. Actualizar tabla usuarios (email y password si viene)
            $userId = $_SESSION['user']['id'];
            $userData = ['email' => $email];
            if (!empty($password)) {
                $userData['password'] = password_hash($password, PASSWORD_BCRYPT);
            }
            $this->usuarioModel->update($userId, $userData);

            // Actualizar sesión
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['nombres'] = $this->input('nombres') ?? $_SESSION['user']['nombres'];
            
            $this->setFlash('Perfil actualizado correctamente', 'success');
            $this->redirect('?page=portal_dashboard');

        } catch (\Exception $e) {
            $this->setFlash('Error: ' . $e->getMessage(), 'error');
            $this->redirect('?page=portal_dashboard');
        }
    }

    /**
     * Actualiza la información de un miembro del equipo
     */
    public function updateTeamMember(): void {
        if (!$this->liderId) return;

        $id = (int)$this->input('id');
        
        try {
            // Verificar que el miembro sea realmente de su equipo directivo
            $miembro = $this->colaboradorModel->getById($id);
            if (!$miembro || $miembro['lider_directo'] !== $this->liderDocumento) {
                throw new \Exception('No tienes permiso para editar este colaborador');
            }

            $updateData = [
                'nombres' => $this->input('nombres'),
                'apellidos' => $this->input('apellidos'),
                'telefono' => $this->input('telefono'),
                'email' => $this->input('email'),
                'departamento' => $this->input('departamento'),
                'municipio' => $this->input('municipio'),
                'tipo_territorio' => $this->input('tipo_territorio'),
                'territorio' => $this->input('territorio'),
                'barrio' => $this->input('barrio'),
                'puesto_votacion' => $this->input('puesto_votacion'),
                'mesa_votacion' => $this->input('mesa_votacion')
            ];

            $this->colaboradorModel->update($id, $updateData);

            $this->setFlash('Colaborador actualizado correctamente', 'success');
            $this->redirect('?page=portal_dashboard');

        } catch (\Exception $e) {
            $this->setFlash('Error: ' . $e->getMessage(), 'error');
            $this->redirect('?page=portal_dashboard');
        }
    }

    public function exportTeamCSV(): void {
        if (!$this->liderId) return;

        try {
            $directos = $this->colaboradorModel->getSeguidoresDirectos($this->liderId);
            
            $filename = "equipo_" . $this->liderDocumento . "_" . date('Ymd') . ".xlsx";
            
            $excelData = [];
            // Cabeceras
            $excelData[] = [
                'Nombres', 
                'Apellidos', 
                'Documento', 
                'Celular', 
                'Email', 
                'Departamento', 
                'Municipio', 
                'Barrio', 
                'Estado'
            ];
            
            // Datos
            foreach ($directos as $m) {
                $excelData[] = [
                    $m['nombres'],
                    $m['apellidos'],
                    $m['documento'],
                    $m['celular'] ?? $m['telefono'] ?? '',
                    $m['email'] ?? '',
                    $m['departamento'] ?? '',
                    $m['municipio'] ?? '',
                    $m['barrio'] ?? '',
                    $m['estado'] ?? 'Activo'
                ];
            }
            
            if (class_exists('\Shuchkin\SimpleXLSXGen')) {
                $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($excelData);
                $xlsx->downloadAs($filename);
            } else {
                throw new \Exception("La librería Excel (SimpleXLSXGen) no está instalada o cargada.");
            }
            exit;

        } catch (\Exception $e) {
            $this->setFlash('Error al exportar: ' . $e->getMessage(), 'error');
            $this->redirect('?page=portal_dashboard');
        }
    }

    /**
     * Helper para verificar si es admin
     */
    private function isAdmin(): bool {
        return isset($_SESSION['user']['tipo_usuario']) && 
               in_array($_SESSION['user']['tipo_usuario'], ['admin', 'coordinador']);
    }
}

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
use App\Models\Curriculum;
use App\Models\Evento;
use App\Utils\Logger; // Si existe

class LeaderPortalController extends Controller {
    /**
     * Modelo de Colaborador
     * @var Colaborador
     */
    private Colaborador $colaboradorModel;

    /**
     * Modelo de Evento
     * @var Evento
     */
    private Evento $eventoModel;

    /**
     * Modelo de Curriculum
     * @var Curriculum
     */
    private Curriculum $curriculumModel;

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
        $this->eventoModel = new Evento();
        $this->curriculumModel = new Curriculum();

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

            // Red consolidada: TODOS los miembros de la red (líder + descendientes)
            $redCompleta = $this->getRedCompleta($this->liderDocumento);

            // Meta Personal (Ejemplo estático, o podría venir de DB)
            $metaPersonal = 20; 
            $progreso = min(100, ($totalRed / $metaPersonal) * 100);

            // Generar Link de Referido
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $refLink = "$protocol://$host/aratio/index.php?page=voluntario_registro&lider=" . $this->liderDocumento;

            // Desglose por territorio (Municipio / Barrio)
            $desgloseTerritorio = $this->colaboradorModel->getNeighborhoodBreakdown($downline);

            // Renderizar vista
            $this->view('portal.dashboard', [
                'lider' => $lider,
                'directos' => $directos,
                'totalDirectos' => count($directos),
                'totalRed' => $totalRed,
                'redCompleta' => $redCompleta,
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
     * Obtener la red completa del líder (todos los descendientes)
     */
    private function getRedCompleta(string $documentoLider): array {
        $db = \Database::getInstance();
        $campanaId = $_SESSION['user']['campana_id'] ?? null;
        if (!$campanaId) return [];

        $sql = "
            WITH RECURSIVE red_jerarquica AS (
                SELECT 
                    id, documento, nombres, apellidos, perfil, nivel_participacion,
                    dato_potencial, lider_directo, municipio, barrio, telefono, email, estado,
                    0 as nivel_jerarquico
                FROM colaboradores
                WHERE documento = ? AND campana_id = ?
                
                UNION ALL
                
                SELECT 
                    c.id, c.documento, c.nombres, c.apellidos, c.perfil, c.nivel_participacion,
                    c.dato_potencial, c.lider_directo, c.municipio, c.barrio, c.telefono, c.email, c.estado,
                    rj.nivel_jerarquico + 1
                FROM colaboradores c
                INNER JOIN red_jerarquica rj ON c.lider_directo = rj.documento
                WHERE c.campana_id = ?
            )
            SELECT * FROM red_jerarquica
            ORDER BY nivel_jerarquico, nombres
        ";
        return $db->fetchAll($sql, [$documentoLider, $campanaId, $campanaId]);
    }

    /**
     * Ver Mi Red (Visualización Gráfica)
     */
    public function myNetwork(): void {
        if (!$this->liderId) {
            $this->redirect('index.php?page=dashboard');
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
     * Registrar Simpatizante desde el Portal del Líder
     */
    public function registrarSimpatizante(): void {
        if (!$this->liderId) {
            $this->redirect('index.php?page=dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeSimpatizante();
            return;
        }

        $lider = $this->colaboradorModel->getById($this->liderId);
        $db = \Database::getInstance();
        $departamentos = $db->fetchAll(
            "SELECT DISTINCT departamento FROM territorios WHERE departamento IS NOT NULL AND departamento != '' ORDER BY departamento"
        );

        $this->view('portal.registrar_simpatizante', [
            'lider' => $lider,
            'liderDocumento' => $this->liderDocumento,
            'campanaId' => $lider['campana_id'] ?? null,
            'departamentos' => $departamentos,
            'pageTitle' => 'Registrar Simpatizante'
        ], 'portal');
    }

    /**
     * Procesa el POST del formulario de registro de simpatizante
     */
    private function storeSimpatizante(): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                throw new \Exception('Datos inválidos.');
            }

            $required = ['nombres', 'apellidos', 'documento', 'municipio', 'departamento', 'genero'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new \Exception("El campo $field es obligatorio.");
                }
            }

            $lider = $this->colaboradorModel->getById($this->liderId);
            $campanaId = $lider['campana_id'] ?? null;
            if (!$campanaId) {
                throw new \Exception('No tienes una campaña asignada. Contacta al administrador.');
            }

            $db = \Database::getInstance();
            $exists = $db->fetchOne(
                "SELECT id FROM colaboradores WHERE documento = ? AND campana_id = ?",
                [$data['documento'], $campanaId]
            );
            if ($exists) {
                throw new \Exception('Ya existe un registro con este documento en tu campaña.');
            }

            $fotoPath = null;
            if (!empty($data['foto']) && function_exists('saveBase64Image')) {
                $fotoPath = saveBase64Image($data['foto'], 'simp_');
            }

            $insertData = [
                'campana_id' => $campanaId,
                'nombres' => $this->sanitize($data['nombres']),
                'apellidos' => $this->sanitize($data['apellidos']),
                'tipo_documento' => $data['tipo_documento'] ?? 'CC',
                'documento' => $this->sanitize($data['documento']),
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'genero' => $data['genero'],
                'perfil' => 'Simpatizante',
                'nivel_participacion' => 'Simpatizante',
                'departamento' => $this->sanitize($data['departamento']),
                'municipio' => $this->sanitize($data['municipio']),
                'tipo_territorio' => $this->sanitize($data['tipo_territorio'] ?? null),
                'territorio' => $this->sanitize($data['territorio'] ?? null),
                'barrio' => $this->sanitize($data['barrio'] ?? null),
                'puesto_votacion' => $this->sanitize($data['puesto_votacion'] ?? null),
                'mesa_votacion' => $this->sanitize($data['mesa_votacion'] ?? null),
                'lider_directo' => $this->liderDocumento,
                'email' => $this->sanitize($data['email'] ?? null),
                'telefono' => $this->sanitize($data['telefono'] ?? null),
                'dato_potencial' => 1,
                'dato_historico' => 0,
                'observaciones' => 'Registrado por líder ' . $this->liderDocumento,
                'created_at' => date('Y-m-d H:i:s'),
                'foto' => $fotoPath
            ];

            $newId = $db->insert('colaboradores', $insertData);
            if (!$newId) {
                throw new \Exception('Error al guardar el registro en la base de datos.');
            }

            try {
                $checkTable = $db->fetchAll("SHOW TABLES LIKE 'campana_colaboradores'");
                if (!empty($checkTable)) {
                    $db->insert('campana_colaboradores', [
                        'campana_id' => $campanaId,
                        'colaborador_id' => $newId,
                        'rol' => 'Simpatizante',
                        'estado' => 'Activo',
                        'fecha_vinculacion' => date('Y-m-d H:i:s')
                    ]);
                }
            } catch (\Exception $e) {
            }

            echo json_encode(['success' => true, 'message' => '¡Simpatizante registrado exitosamente en tu red!']);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Sanitizar string
     */
    private function sanitize($value): string {
        if ($value === null) return '';
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Ver Eventos de Campaña
     */
    public function events(): void {
        if (!$this->liderId) {
            $this->redirect('?page=dashboard');
            return;
        }

        $show = $this->input('show', 'future');
        $page = (int) ($this->input('p', 1));
        $perPage = 20;
        
        $filters = [];
        if ($show === 'past') {
            $filters['estado'] = 'completado';
        } else {
            $filters['estado'] = 'programado';
        }

        if (isset($_SESSION['campana_activa'])) {
            $filters['campana_id'] = $_SESSION['campana_activa'];
        }

        $events = $this->eventoModel->getAll($page, $perPage, $filters);
        $total = $this->eventoModel->count($filters);

        // Obtener todos los eventos para el mapa (sin filtro de estado)
        $mapFilters = [];
        if (isset($_SESSION['campana_activa'])) {
            $mapFilters['campana_id'] = $_SESSION['campana_activa'];
        }
        $allEvents = $this->eventoModel->getAll(1, 200, $mapFilters);
        
        // Filtrar eventos con coordenadas para el mapa
        $mapEvents = array_filter($allEvents, function($e) {
            return !empty($e['latitud']) && !empty($e['longitud']);
        });

        $this->view('portal.events', [
            'events' => $events,
            'mapEvents' => array_values($mapEvents),
            'show' => $show,
            'page' => $page,
            'totalPages' => ceil($total / $perPage),
            'total' => $total,
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

        try {
            // Actualizar solo colaboradores (NO se toca la tabla usuarios)
            $updateData = [
                'telefono'         => $telefono,
                'email'            => $email,
                'departamento'     => $this->input('departamento'),
                'municipio'        => $this->input('municipio'),
                'tipo_territorio'  => $this->input('tipo_territorio'),
                'territorio'       => $this->input('territorio'),
                'barrio'           => $this->input('barrio'),
                'puesto_votacion'  => $this->input('puesto_votacion'),
                'mesa_votacion'    => $this->input('mesa_votacion')
            ];

            $this->colaboradorModel->update($this->liderId, $updateData);

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
     * Mi Perfil - Vista independiente
     */
    public function profile(): void {
        if (!$this->liderId) {
            $this->redirect('?page=portal_landing');
            return;
        }

        // Manejar POST (actualizar perfil)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateProfileFromProfile();
            return;
        }

        $lider = $this->colaboradorModel->getById($this->liderId);
        if (!$lider) {
            $this->setFlash('Error: no se encontró tu perfil', 'error');
            $this->redirect('?page=portal_dashboard');
            return;
        }

        // Estadísticas del líder
        $directos = $this->colaboradorModel->getSeguidoresDirectos($this->liderId);
        $downline = $this->colaboradorModel->getDownlineDocumentos($this->liderDocumento);

        // Departamentos para el desplegable de edición
        $db = \Database::getInstance();
        $departamentos = $db->fetchAll(
            "SELECT DISTINCT departamento FROM territorios WHERE departamento IS NOT NULL AND departamento != '' ORDER BY departamento"
        );

        // Curriculum del líder
        $curriculum = $this->curriculumModel->getByColaboradorId($this->liderId);

        $this->view('portal.profile', [
            'lider' => $lider,
            'totalDirectos' => count($directos),
            'totalRed' => count($downline),
            'departamentos' => $departamentos,
            'curriculum' => $curriculum,
            'pageTitle' => 'Mi Perfil'
        ], 'portal');
    }

    /**
     * Actualizar perfil desde la vista de perfil
     */
    private function updateProfileFromProfile(): void {
        if (!$this->liderId) return;

        try {
            // Soportar JSON POST (Alpine.js fetch) y form traditional POST
            $input = $this->getJsonInput();

            $updateData = [
                'nombres'           => $input['nombres'] ?? $this->input('nombres'),
                'apellidos'         => $input['apellidos'] ?? $this->input('apellidos'),
                'telefono'          => $input['telefono'] ?? $this->input('telefono'),
                'email'             => $input['email'] ?? $this->input('email'),
                'departamento'      => $input['departamento'] ?? $this->input('departamento'),
                'municipio'         => $input['municipio'] ?? $this->input('municipio'),
                'cod_mpio'          => $input['cod_mpio'] ?? $this->input('cod_mpio'),
                'tipo_territorio'   => $input['tipo_territorio'] ?? $this->input('tipo_territorio'),
                'territorio'        => $input['territorio'] ?? $this->input('territorio'),
                'barrio'            => $input['barrio'] ?? $this->input('barrio'),
                'puesto_votacion'   => $input['puesto_votacion'] ?? $this->input('puesto_votacion'),
                'mesa_votacion'     => $input['mesa_votacion'] ?? $this->input('mesa_votacion'),
            ];

            $this->colaboradorModel->update($this->liderId, $updateData);

            // Guardar curriculum si viene en el POST
            $curriculumData = $input['curriculum'] ?? null;
            if ($curriculumData && is_array($curriculumData)) {
                $this->curriculumModel->save($this->liderId, [
                    'formacion_academica'   => $curriculumData['formacion_academica'] ?? [],
                    'experiencia_laboral'   => $curriculumData['experiencia_laboral'] ?? [],
                    'participacion_politica'=> $curriculumData['participacion_politica'] ?? [],
                    'hijos_data'            => $curriculumData['hijos_data'] ?? [],
                    'hijos_discapacidad'    => $curriculumData['hijos_discapacidad'] ?? 0,
                    'equipo_futbol'         => $curriculumData['equipo_futbol'] ?? null,
                    'practica_deportiva'    => $curriculumData['practica_deportiva'] ?? null,
                    'resumen_profesional'   => $curriculumData['resumen_profesional'] ?? null,
                    'habilidades'           => $curriculumData['habilidades'] ?? null,
                    'idiomas'               => $curriculumData['idiomas'] ?? null,
                    'reconocimientos'       => $curriculumData['reconocimientos'] ?? null,
                    'referencias'           => $curriculumData['referencias'] ?? null,
                ]);
            }

            // Actualizar sesión
            $_SESSION['user']['nombres'] = $updateData['nombres'] ?? $_SESSION['user']['nombres'];
            $_SESSION['user']['email'] = $updateData['email'] ?? $_SESSION['user']['email'];

            // Responder JSON si es fetch, redirect si es form
            $isJson = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);
                exit;
            }

            $this->setFlash('Perfil actualizado correctamente', 'success');
            $this->redirect('?page=portal_perfil');

        } catch (\Exception $e) {
            $isJson = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
            $this->setFlash('Error: ' . $e->getMessage(), 'error');
            $this->redirect('?page=portal_perfil');
        }
    }

    /**
     * Obtener input JSON del body si es Content-Type JSON
     */
    private function getJsonInput(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }
        return [];
    }

    /**
     * Helper para verificar si es admin
     */
    private function isAdmin(): bool {
        return isset($_SESSION['user']['tipo_usuario']) && 
               in_array($_SESSION['user']['tipo_usuario'], ['admin', 'coordinador']);
    }
}

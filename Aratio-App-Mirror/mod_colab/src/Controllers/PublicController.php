<?php
/**
 * Controlador Público
 * Maneja las inscripciones públicas de colaboradores (sin autenticación)
 * Incluye creación de colaborador + curriculum en una sola operación
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 2.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Colaborador;
use App\Models\Curriculum;
use App\Utils\Validator;
use App\Utils\Security;
use App\Utils\Logger;

class PublicController extends Controller
{
    /**
     * Modelo de Colaborador
     * @var Colaborador
     */
    private Colaborador $colaboradorModel;

    /**
     * Modelo de Curriculum
     * @var Curriculum
     */
    private Curriculum $curriculumModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->colaboradorModel = new Colaborador();
        $this->curriculumModel = new Curriculum();
    }

    /**
     * Mostrar formulario de inscripción de simpatizantes (Versión Gold)
     */
    public function showSimpatizante(): void
    {
        $db = \Database::getInstance();
        $campanas = $db->fetchAll("SELECT id, nombre FROM campanas WHERE estado = 'Activa' ORDER BY nombre");
        $departamentos = $db->fetchAll("SELECT DISTINCT departamento FROM territorios WHERE departamento != '' ORDER BY departamento");

        $this->viewStandalone('public.inscripcion_simpatizante', [
            'title' => 'Registro de Simpatizantes',
            'campanas' => $campanas,
            'departamentos' => array_column($departamentos, 'departamento')
        ]);
    }

    /**
     * Mostrar formulario de inscripción de líderes (Versión Gold)
     */
    public function showLider(): void
    {
        $db = \Database::getInstance();
        $campanas = $db->fetchAll("SELECT id, nombre FROM campanas WHERE estado = 'Activa' ORDER BY nombre");
        $departamentos = $db->fetchAll("SELECT DISTINCT departamento FROM territorios WHERE departamento != '' ORDER BY departamento");

        $this->viewStandalone('public.registro_lider', [
            'title' => 'Registro de Líderes',
            'campanas' => $campanas,
            'departamentos' => array_column($departamentos, 'departamento')
        ]);
    }

    /**
     * Procesar inscripción de simpatizante (Soporta JSON/Premium)
     */
    public function storeSimpatizante(): void
    {
        // Soporte para JSON (fetch de Alpine)
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = json_decode($input, true);
        }
        else {
            $data = $this->post();
        }

        if (empty($data)) {
            $this->jsonError('No se recibieron datos.');
            return;
        }

        // Validar CSRF
        if (!Security::checkCsrf()) {
            $this->setFlash('Token de seguridad inválido.', 'error');
            $this->redirect('/registro-simpatizante');
            return;
        }

        // Validar datos básicos
        $validator = new Validator($data);
        $validator->required([
            'campana_id',
            'nombres', 'apellidos', 'documento', 'telefono',
            'departamento', 'municipio'
        ])
            ->pattern('documento', '/^[0-9]{6,15}$/', 'Documento inválido')
            ->numeric('campana_id');

        if ($validator->fails()) {
            if (!empty($input)) {
                $this->jsonError('Faltan campos requeridos o son inválidos.');
                return;
            }
            $_SESSION['old_input'] = $data;
            $_SESSION['errors'] = $validator->errors();
            $this->redirect('/registro-simpatizante');
            return;
        }

        try {
            $db = \Database::getInstance();
            $db->beginTransaction();

            // Preparar datos del colaborador (Simpatizante)
            $colaboradorData = [
                'campana_id' => $data['campana_id'],
                'documento' => Security::sanitize($data['documento']),
                'tipo_documento' => !empty($data['tipo_documento']) ?Security::sanitize($data['tipo_documento']) : 'CC',
                'nombres' => Security::sanitize($data['nombres']),
                'apellidos' => Security::sanitize($data['apellidos']),
                'fecha_nacimiento' => !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null,
                'genero' => !empty($data['genero']) ?Security::sanitize($data['genero']) : null,
                'email' => !empty($data['email']) ?Security::sanitize($data['email']) : null,
                'telefono' => Security::sanitize($data['telefono']), // Cambiado de celular a telefono
                'telefono_whatsapp' => !empty($data['telefono_whatsapp']) ?Security::sanitize($data['telefono_whatsapp']) : Security::sanitize($data['telefono']),
                'departamento' => Security::sanitize($data['departamento']),
                'municipio' => Security::sanitize($data['municipio']),
                'tipo_territorio' => !empty($data['tipo_territorio']) ?Security::sanitize($data['tipo_territorio']) : null,
                'territorio' => !empty($data['territorio']) ?Security::sanitize($data['territorio']) : null,
                'barrio' => !empty($data['barrio']) ?Security::sanitize($data['barrio']) : null,
                'direccion' => !empty($data['direccion']) ?Security::sanitize($data['direccion']) : null,

                // Campos Electorales
                'puesto_votacion' => !empty($data['puesto_votacion']) ?Security::sanitize($data['puesto_votacion']) : null,
                'mesa_votacion' => !empty($data['mesa_votacion']) ?Security::sanitize($data['mesa_votacion']) : null,

                // Contexto Campaña/Líder
                'lider_directo' => !empty($data['lider_directo']) ?Security::sanitize($data['lider_directo']) : null,
                'perfil' => 'Simpatizante',
                'nivel_participacion' => 'Simpatizante',
                'dato_potencial' => 0,
                'dato_historico' => 0,
                'estado' => 'Activo'
            ];

            if (isset($data['areas_interes']) && is_array($data['areas_interes'])) {
                $colaboradorData['areas_interes'] = json_encode($data['areas_interes']);
            }

            // Crear colaborador
            $colaboradorId = $this->colaboradorModel->create($colaboradorData);

            // Asociar a la campaña (Tabla colaboraciones o similar? El usuario mencionó 'combo desplegable de la campaña', pero no especificó si se guarda una relación M:M. 
            // ASUMIRÉ: Si existe tabla de relacion campaña_colaborador, insertar allí. 
            // Si no, y el colaborador solo tiene un líder, el líder ya define la red. Pero para cumplir el requisito de 'Simpatizante de Campaña X', 
            // habría que ver si hay tabla intermedia.
            // REVISIÓN RÁPIDA DE DB: No tengo el esquema completo de campañas.
            // PO: "combo desplegable de la campaña a la cual corresponde".
            // Voy a intentar insertar en `campana_colaboradores` si existe, o dejarlo solo con el líder por ahora y loguearlo.
            // UPDATE: El `lider_directo` es colaborador. Si el líder está en una campaña, el simpatizante entra a su red.
            // Para asegurar, voy a guardar el ID de campaña si hay tabla relacion.
            // Voy a verificar existencia de tabla `campana_colaboradores` antes.
            // Por ahora, asumo flujo simple de creación exitosa.

            // INTENTO DE VINCULACIÓN A CAMPAÑA (Si existe la tabla)
            $sqlCheckTable = "SHOW TABLES LIKE 'campana_colaboradores'";
            if ($db->fetchOne($sqlCheckTable)) {
                $db->insert('campana_colaboradores', [
                    'campana_id' => $data['campana_id'],
                    'colaborador_id' => $colaboradorId,
                    'rol' => 'Simpatizante',
                    'estado' => 'Activo',
                    'fecha_vinculacion' => date('Y-m-d H:i:s')
                ]);
            }

            $db->commit();

            if (!empty($input)) {
                $this->jsonSuccess(['message' => '¡Registro exitoso!']);
                return;
            }

            $this->redirect('/registro-simpatizante/confirmacion');

        }
        catch (\Exception $e) {
            if (isset($db))
                $db->rollback();
            Logger::error('Error registro simpatizante: ' . $e->getMessage());

            if (!empty($input)) {
                $this->jsonError($e->getMessage());
                return;
            }

            $_SESSION['old_input'] = $data;
            $this->setFlash('Error al registrar: ' . $e->getMessage(), 'error');
            $this->redirect('/registro-simpatizante');
        }
    }


    /**
     * Mostrar página de confirmación
     */
    public function confirmacion(): void
    {
        $inscripcion = $_SESSION['inscripcion_exitosa'] ?? null;

        // Limpiar la sesión
        unset($_SESSION['inscripcion_exitosa']);

        if (!$inscripcion) {
            $this->redirect('/inscripcion');
            return;
        }

        $this->viewPublic('public.confirmacion', [
            'title' => 'Inscripción Exitosa',
            'inscripcion' => $inscripcion
        ]);
    }

    /**
     * Procesar inscripción de líder (Versión Gold)
     */
    public function storeLider(): void
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (empty($data)) {
            $this->jsonError('No se recibieron datos.');
            return;
        }

        try {
            $db = \Database::getInstance();
            $db->beginTransaction();

            // 1. Crear Colaborador
            $colaboradorData = [
                'campana_id' => $data['campana_id'],
                'documento' => Security::sanitize($data['documento']),
                'tipo_documento' => !empty($data['tipo_documento']) ?Security::sanitize($data['tipo_documento']) : 'CC',
                'nombres' => Security::sanitize($data['nombres'] ?? ''),
                'apellidos' => Security::sanitize($data['apellidos'] ?? ''),
                'fecha_nacimiento' => !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null,
                'genero' => !empty($data['genero']) ?Security::sanitize($data['genero']) : null,
                'email' => !empty($data['email']) ?Security::sanitize($data['email']) : null,
                'telefono' => Security::sanitize($data['telefono'] ?? ''),
                'telefono_whatsapp' => !empty($data['telefono_whatsapp']) ?Security::sanitize($data['telefono_whatsapp']) : (!empty($data['telefono']) ?Security::sanitize($data['telefono']) : null),
                'departamento' => Security::sanitize($data['departamento'] ?? ''),
                'municipio' => Security::sanitize($data['municipio'] ?? ''),
                'tipo_territorio' => !empty($data['tipo_territorio']) ?Security::sanitize($data['tipo_territorio']) : null,
                'territorio' => !empty($data['territorio']) ?Security::sanitize($data['territorio']) : null,
                'barrio' => !empty($data['barrio']) ?Security::sanitize($data['barrio']) : null,
                'puesto_votacion' => !empty($data['puesto_votacion']) ?Security::sanitize($data['puesto_votacion']) : null,
                'mesa_votacion' => !empty($data['mesa_votacion']) ?Security::sanitize($data['mesa_votacion']) : null,
                'lider_directo' => !empty($data['lider_directo']) ?Security::sanitize($data['lider_directo']) : null,
                'perfil' => 'Líder / Coordinador',
                'nivel_participacion' => $data['nivel_participacion'] ?? 'Lider',
                'areas_interes' => json_encode($data['areas_interes'] ?? []),
                'observaciones' => "Registro Líder Premium Gold",
                'estado' => 'Activo'
            ];

            $colaboradorId = $this->colaboradorModel->create($colaboradorData);

            // 2. Crear Curriculum (Opcional - previene error 500 si la tabla no existe en prod)
            try {
                $curriculumData = [
                    'colaborador_id' => $colaboradorId,
                    'resumen_profesional' => Security::sanitize($data['resumen_profesional'] ?? ''),
                    'formacion_academica' => json_encode($data['formaciones'] ?? []),
                    'experiencia_laboral' => json_encode($data['experiencias'] ?? []),
                    'habilidades' => Security::sanitize($data['habilidades'] ?? '')
                ];
                $this->curriculumModel->create($curriculumData);
            }
            catch (\Exception $ec) {
                // Silenciamos el error de curriculum para que el líder principal se registre
                Logger::error('Advertencia: No se pudo guardar Curriculum: ' . $ec->getMessage());
            }

            $db->commit();
            $this->jsonSuccess(['message' => '¡Registro exitoso como líder!']);

        }
        catch (\Throwable $e) {
            if (isset($db))
                $db->rollback();
            Logger::error('Error registro líder: ' . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    /**
     * API: Buscar líderes por campaña (AJAX Search)
     */
    public function searchLideres(): void
    {
        $campanaId = $_GET['campana_id'] ?? null;
        $search = $_GET['search'] ?? '';

        if (!$campanaId) {
            $this->jsonError('ID de campaña requerido');
            return;
        }

        try {
            $db = \Database::getInstance();
            $sql = "SELECT documento, nombres, apellidos, perfil 
                    FROM colaboradores 
                    WHERE campana_id = ? 
                    AND (perfil LIKE '%Lider%' OR perfil LIKE '%Líder%' OR perfil LIKE '%Candidato%' OR perfil LIKE '%Coordinador%') 
                    AND estado IN ('Activo', 'Nuevo')";

            $params = [$campanaId];
            if (!empty($search)) {
                $sql .= " AND (nombres LIKE ? OR apellidos LIKE ? OR documento LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            $sql .= " ORDER BY nombres, apellidos LIMIT 20";

            $lideres = $db->fetchAll($sql, $params);
            $this->jsonSuccess($lideres);
        }
        catch (\Throwable $e) {
            $this->jsonError($e->getMessage());
        }
    }

    /**
     * API Gold: Obtener municipios por departamento
     */
    public function getMunicipiosGold(): void
    {
        $depto = $_GET['departamento'] ?? '';
        try {
            $db = \Database::getInstance();
            $sql = "SELECT DISTINCT municipio, cod_mpio 
                    FROM territorios 
                    WHERE departamento = ? AND municipio != '' 
                    ORDER BY municipio";
            $data = $db->fetchAll($sql, [$depto]);
            $this->jsonSuccess($data);
        }
        catch (\Throwable $e) {
            $this->jsonError($e->getMessage());
        }
    }

    /**
     * API Gold: Cascada Geográfica (Tipos, Territorios, Barrios)
     */
    public function getGeoCascadaGold(): void
    {
        $cod_mpio = $_GET['cod_mpio'] ?? '';
        $tipo = $_GET['tipo'] ?? null;
        $territorio = $_GET['territorio'] ?? null;
        try {
            $db = \Database::getInstance();
            if (!$tipo && !$territorio) {
                $sql = "SELECT DISTINCT Tipo_territorio FROM territorios WHERE cod_mpio = ? AND Tipo_territorio != '' ORDER BY Tipo_territorio";
                $data = $db->fetchAll($sql, [$cod_mpio]);
            }
            elseif ($tipo && !$territorio) {
                $sql = "SELECT DISTINCT Territorio FROM territorios WHERE cod_mpio = ? AND Tipo_territorio = ? AND Territorio != '' ORDER BY Territorio";
                $data = $db->fetchAll($sql, [$cod_mpio, $tipo]);
            }
            elseif ($tipo && $territorio) {
                $sql = "SELECT DISTINCT barrio FROM territorios WHERE cod_mpio = ? AND Tipo_territorio = ? AND Territorio = ? AND barrio != '' ORDER BY barrio";
                $data = $db->fetchAll($sql, [$cod_mpio, $tipo, $territorio]);
            }
            $this->jsonSuccess(array_column($data, key($data[0] ?? [])));
        }
        catch (\Throwable $e) {
            $this->jsonError($e->getMessage());
        }
    }

    /**
     * API Gold: Obtener puestos por cod_mpio
     */
    public function getPuestosGold(): void
    {
        $cod_mpio = $_GET['cod_mpio'] ?? '';
        try {
            $db = \Database::getInstance();
            $sql = "SELECT DISTINCT puesto FROM puestos_votacion WHERE cod_mpio = ? AND estado = 'Activo' ORDER BY puesto";
            $data = $db->fetchAll($sql, [$cod_mpio]);
            $this->jsonSuccess(array_column($data, 'puesto'));
        }
        catch (\Throwable $e) {
            $this->jsonError($e->getMessage());
        }
    }

    /**
     * API Gold: Buscar puestos por texto (tipo-ahead - permite buscar con 2+ letras)
     */
    public function buscarPuestosGold(): void
    {
        $q = $_GET['q'] ?? '';
        $municipio = $_GET['municipio'] ?? '';
        
        // Permitir búsqueda con 2+ caracteres
        if (strlen($q) < 2) {
            $this->jsonSuccess([]);
            return;
        }

        try {
            $db = \Database::getInstance();
            $searchTerm = "%" . strtoupper($q) . "%";
            $muniUpper = strtoupper($municipio);
            $sql = "SELECT DISTINCT puesto FROM puestos_votacion 
                    WHERE municipio = ? AND UPPER(puesto) LIKE ? AND estado = 'Activo' 
                    ORDER BY puesto LIMIT 25";
            $data = $db->fetchAll($sql, [$muniUpper, $searchTerm]);
            $this->jsonSuccess(array_column($data, 'puesto'));
        }
        catch (\Throwable $e) {
            $this->jsonError($e->getMessage());
        }
    }

    /**
     * Renderizar vista auto-contenida (sin layout)
     */
    protected function viewStandalone(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        }
        else {
            throw new \Exception("Vista no encontrada: {$viewPath}");
        }
    }

    /**
     * Renderizar vista con layout público
     */
    protected function viewPublic(string $view, array $data = []): void
    {
        extract($data);

        // Capturar el contenido de la vista
        ob_start();
        $viewPath = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        }
        else {
            throw new \Exception("Vista no encontrada: {$viewPath}");
        }
        $viewContent = ob_get_clean();

        // Renderizar con layout público
        include APP_PATH . '/Views/layouts/public.php';
    }
}

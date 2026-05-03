<?php
/**
 * Controlador de Colaboradores
 * CRUD completo y funcionalidades avanzadas
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Colaborador;
use App\Utils\Validator;
use App\Utils\Security;
use App\Utils\Logger;
use App\Utils\Helpers;

class ColaboradorController extends Controller
{
    /**
     * Modelo de Colaborador
     * @var Colaborador
     */
    private Colaborador $colaboradorModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->colaboradorModel = new Colaborador();
    }

    /**
     * Listar colaboradores
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_ver');

        // Obtener filtros
        $filtros = [
            'search' => $this->input('search'),
            'perfil' => $this->input('perfil'),
            'estado' => $this->input('estado'),
            'departamento' => $this->input('departamento'),
            'nivel_participacion' => $this->input('nivel_participacion'),
        ];

        // Paginación
        $page = (int) $this->input('page', 1);
        $perPage = 20;

        // Obtener colaboradores
        $colaboradores = $this->colaboradorModel->getAll($page, $perPage, $filtros);
        $total = $this->colaboradorModel->count($filtros);
        $totalPages = ceil($total / $perPage);

        $this->view('colaboradores.index', [
            'pageTitle' => 'Colaboradores',
            'pageDescription' => 'Gestión de colaboradores',
            'colaboradores' => $colaboradores,
            'filtros' => $filtros,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalRecords' => $total
            ],
            'perfiles' => PERFILES,
            'departamentos' => DEPARTAMENTOS_COLOMBIA
        ]);
    }

    /**
     * Ver detalle de colaborador
     */
    public function show(string $id): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_ver');

        $colaborador = $this->colaboradorModel->getById((int) $id);

        if (!$colaborador) {
            $this->setFlash('Colaborador no encontrado', 'error');
            $this->redirect('/colaboradores');
        }

        // Obtener líder directo si existe
        $liderDirecto = null;
        if (!empty($colaborador['lider_directo'])) {
            $liderDirecto = $this->colaboradorModel->getByDocumento($colaborador['lider_directo']);
        }

        // Obtener seguidores directos
        $seguidores = $this->colaboradorModel->getSeguidoresDirectos((int) $id);

        // Calcular total de seguidores incluyendo sub-niveles
        $totalSeguidores = count($seguidores);

        // Agregar información de seguidores de segundo nivel
        foreach ($seguidores as &$seguidor) {
            $subSeguidores = $this->colaboradorModel->getSeguidoresDirectos((int) $seguidor['id']);
            $seguidor['total_subseguidores'] = count($subSeguidores);
            $seguidor['subseguidores'] = $subSeguidores;

            // Agregar info de tercer nivel para cada subseguidor
            foreach ($seguidor['subseguidores'] as &$subSeguidor) {
                $subSubSeguidores = $this->colaboradorModel->getSeguidoresDirectos((int) $subSeguidor['id']);
                $subSeguidor['total_subseguidores'] = count($subSubSeguidores);
            }
        }

        // Obtener red completa
        $redCompleta = $this->colaboradorModel->getRedJerarquica($colaborador['documento']);
        $totalRedCompleta = count($redCompleta);

        // Calcular niveles de profundidad
        $nivelesJerarquia = $this->calcularNivelesJerarquia($seguidores);

        // Obtener curriculum
        $curriculumModel = new \App\Models\Curriculum();
        $curriculum = $curriculumModel->getByColaboradorId((int) $id);

        // Obtener historial de cambios de líder
        $historialCambios = $this->colaboradorModel->getHistorialCambiosLider($colaborador['documento']);

        // Obtener historial de estados (trazabilidad)
        $historialEstados = $this->colaboradorModel->getHistorialEstados((int) $id);

        $this->view('colaboradores.show', [
            'pageTitle' => Helpers::formatFullName($colaborador['nombres'], $colaborador['apellidos']),
            'colaborador' => $colaborador,
            'liderDirecto' => $liderDirecto,
            'seguidores' => $seguidores,
            'totalSeguidores' => $totalSeguidores,
            'totalRedCompleta' => $totalRedCompleta,
            'nivelesJerarquia' => $nivelesJerarquia,
            'curriculum' => $curriculum,
            'historialCambios' => $historialCambios,
            'historialEstados' => $historialEstados,
            'breadcrumbs' => [
                ['label' => 'Colaboradores', 'url' => '/colaboradores'],
                ['label' => 'Detalle']
            ]
        ]);
    }

    /**
     * Calcular profundidad de niveles en la jerarquía
     */
    private function calcularNivelesJerarquia(array $seguidores): int
    {
        if (empty($seguidores)) {
            return 0;
        }

        $maxNivel = 1;
        foreach ($seguidores as $seguidor) {
            if (!empty($seguidor['subseguidores'])) {
                $nivelSub = 1 + $this->calcularNivelesJerarquia($seguidor['subseguidores']);
                $maxNivel = max($maxNivel, $nivelSub);
            }
        }

        return $maxNivel;
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_crear');

        // Obtener solo colaboradores con perfil de líder para el dropdown
        $lideres = $this->colaboradorModel->getLideresParaAsignar();

        $this->view('colaboradores.create', [
            'pageTitle' => 'Nuevo Colaborador',
            'pageDescription' => 'Registrar un nuevo colaborador',
            'perfiles' => PERFILES,
            'niveles' => NIVELES_PARTICIPACION,
            'areas' => AREAS_INTERES,
            'generos' => GENEROS,
            'documentos' => TIPOS_DOCUMENTO,
            'departamentos' => DEPARTAMENTOS_COLOMBIA,
            'lideres' => $lideres,
            'breadcrumbs' => [
                ['label' => 'Colaboradores', 'url' => '/colaboradores'],
                ['label' => 'Nuevo']
            ]
        ]);
    }

    /**
     * Guardar nuevo colaborador
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_crear');

        $data = $this->post();

        // Validar
        $validator = new Validator($data);
        $validator->required([
            'documento',
            'tipo_documento',
            'nombres',
            'apellidos',
            'fecha_nacimiento',
            'genero',
            'email',
            'celular',
            'departamento',
            'municipio',
            'direccion',
            'perfil',
            'nivel_participacion',
            'dato_potencial',
            'dato_historico'
        ])
            ->pattern('documento', '/^[0-9]{6,15}$/', 'Documento inválido')
            ->email('email')
            ->unique('documento', 'colaboradores', 'documento')
            ->unique('email', 'colaboradores', 'email')
            ->date('fecha_nacimiento')
            ->in('perfil', array_keys(PERFILES))
            ->in('nivel_participacion', array_keys(NIVELES_PARTICIPACION))
            ->in('nivel_participacion', array_keys(NIVELES_PARTICIPACION))
            ->numeric('dato_potencial')
            ->numeric('dato_historico');
            // puesto_votacion y mesa_votacion son opcionales y texto libre

        if ($validator->fails()) {
            $_SESSION['old_input'] = $data;
            $_SESSION['errors'] = $validator->errors();
            $this->redirect('/colaboradores/create');
        }

        try {
            // Procesar áreas de interés
            if (isset($data['areas_interes']) && is_array($data['areas_interes'])) {
                $data['areas_interes'] = json_encode($data['areas_interes']);
            }

            // Crear colaborador
            $id = $this->colaboradorModel->create($data);

            Logger::info('Colaborador creado', [
                'colaborador_id' => $id,
                'documento' => $data['documento'],
                'user_id' => $this->user['id']
            ]);

            $this->setFlash('Colaborador creado con éxito', 'success');
            $this->redirect("/colaboradores/{$id}");

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'crear_colaborador']);
            $_SESSION['old_input'] = $data;
            $this->setFlash('Error al crear colaborador: ' . $e->getMessage(), 'error');
            $this->redirect('/colaboradores/create');
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(string $id): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        $colaborador = $this->colaboradorModel->getById((int) $id);

        if (!$colaborador) {
            $this->setFlash('Colaborador no encontrado', 'error');
            $this->redirect('/colaboradores');
        }

        // Decodificar áreas de interés
        if (!empty($colaborador['areas_interes']) && is_string($colaborador['areas_interes'])) {
            $colaborador['areas_interes'] = json_decode($colaborador['areas_interes'], true);
        }

        $this->view('colaboradores.edit', [
            'pageTitle' => 'Editar Colaborador',
            'pageDescription' => Helpers::formatFullName($colaborador['nombres'], $colaborador['apellidos']),
            'colaborador' => $colaborador,
            'perfiles' => PERFILES,
            'niveles' => NIVELES_PARTICIPACION,
            'areas' => AREAS_INTERES,
            'generos' => GENEROS,
            'documentos' => TIPOS_DOCUMENTO,
            'departamentos' => DEPARTAMENTOS_COLOMBIA,
            'breadcrumbs' => [
                ['label' => 'Colaboradores', 'url' => '/colaboradores'],
                ['label' => $colaborador['documento'], 'url' => "/colaboradores/{$id}"],
                ['label' => 'Editar']
            ]
        ]);
    }

    /**
     * Actualizar colaborador
     */
    public function update(string $id): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        $colaborador = $this->colaboradorModel->getById((int) $id);

        if (!$colaborador) {
            $this->jsonError('Colaborador no encontrado', null, 404);
        }

        $data = $this->post();

        // Validar
        $validator = new Validator($data);
        $validator->required([
            'nombres',
            'apellidos',
            'fecha_nacimiento',
            'genero',
            'email',
            'celular',
            'departamento',
            'municipio',
            'direccion',
            'perfil',
            'nivel_participacion',
            'dato_potencial',
            'dato_historico'
        ])
            ->email('email')
            ->unique('email', 'colaboradores', 'email', (int) $id)
            ->date('fecha_nacimiento')
            ->in('perfil', array_keys(PERFILES))
            ->in('nivel_participacion', array_keys(NIVELES_PARTICIPACION))
            ->numeric('dato_potencial')
            ->numeric('dato_historico');
            // puesto_votacion y mesa_votacion son opcionales y texto libre

        if ($validator->fails()) {
            if ($this->isAjax()) {
                $this->jsonError('Datos inválidos', $validator->errors());
            } else {
                $_SESSION['old_input'] = $data;
                $_SESSION['errors'] = $validator->errors();
                $this->redirect("/colaboradores/{$id}/edit");
            }
        }

        try {
            // Procesar áreas de interés
            if (isset($data['areas_interes']) && is_array($data['areas_interes'])) {
                $data['areas_interes'] = json_encode($data['areas_interes']);
            }

            // Actualizar
            $this->colaboradorModel->update((int) $id, $data);

            Logger::info('Colaborador actualizado', [
                'colaborador_id' => $id,
                'documento' => $colaborador['documento'],
                'user_id' => $this->user['id']
            ]);

            if ($this->isAjax()) {
                $this->jsonSuccess(null, 'Colaborador actualizado con éxito');
            } else {
                $this->setFlash('Colaborador actualizado con éxito', 'success');
                $this->redirect("/colaboradores/{$id}");
            }

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'actualizar_colaborador', 'id' => $id]);

            if ($this->isAjax()) {
                $this->jsonError('Error al actualizar: ' . $e->getMessage());
            } else {
                $_SESSION['old_input'] = $data;
                $this->setFlash('Error al actualizar: ' . $e->getMessage(), 'error');
                $this->redirect("/colaboradores/{$id}/edit");
            }
        }
    }

    /**
     * Actualización rápida (Nivel, Perfil, Estado) desde modal
     */
    public function quickUpdate(string $id): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        if (!$this->isAjax()) {
            $this->redirect('/colaboradores');
            return;
        }

        $colaborador = $this->colaboradorModel->getById((int) $id);

        if (!$colaborador) {
            $this->jsonError('Colaborador no encontrado', null, 404);
        }

        $data = $this->post();

        // Validar solo los campos permitidos en Quick Edit
        $validator = new Validator($data);
        $validator->required(['perfil', 'estado', 'nivel_participacion'])
            ->in('perfil', array_keys(PERFILES))
            ->in('estado', ['Nuevo', 'Creció', 'Igual', 'Decrece', 'Desvinculado'])
            ->in('nivel_participacion', array_keys(NIVELES_PARTICIPACION));

        if ($validator->fails()) {
            $this->jsonError('Datos inválidos', $validator->errors());
            return;
        }

        try {
            // Mapeo del estado virtual a valores numéricos (dato_potencial, dato_historico)
            $potencial = 0;
            $historico = 0;
            switch ($data['estado']) {
                case 'Nuevo': 
                    $potencial = 0; $historico = 0; break;
                case 'Desvinculado': 
                    $potencial = 1; $historico = 0; break;
                case 'Creció': 
                    $potencial = 1; $historico = 2; break;
                case 'Decrece': 
                    $potencial = 2; $historico = 1; break;
                case 'Igual': 
                    $potencial = 1; $historico = 1; break;
            }

            // Se actualizan únicamente estos campos (estado no es real, altera los datos numéricos)
            $updateData = [
                'perfil' => $data['perfil'],
                'nivel_participacion' => $data['nivel_participacion'],
                'dato_potencial' => $potencial,
                'dato_historico' => $historico
            ];

            // Pasamos partial = true a update si el modelo lo permite, 
            // sino debemos usar update base o crear uno parcial.
            // Dado que el update actual sobrescribe los campos enviados, esto funcionará si update hace merge o parcial.
            $this->db()->update('colaboradores', $updateData, ['id' => $id]);

            Logger::info('Colaborador actualización rápida', [
                'colaborador_id' => $id,
                'user_id' => $this->user['id']
            ]);

            $this->jsonSuccess(null, 'Colaborador actualizado con éxito');
            
        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'quick_update_colaborador', 'id' => $id]);
            $this->jsonError('Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar colaborador
     */
    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_eliminar');

        $colaborador = $this->colaboradorModel->getById((int) $id);

        if (!$colaborador) {
            $this->jsonError('Colaborador no encontrado', null, 404);
        }

        try {
            $this->colaboradorModel->delete((int) $id);

            Logger::warning('Colaborador eliminado', [
                'colaborador_id' => $id,
                'documento' => $colaborador['documento'],
                'user_id' => $this->user['id']
            ]);

            $this->jsonSuccess(null, 'Colaborador eliminado con éxito');

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'eliminar_colaborador', 'id' => $id]);
            $this->jsonError('Error al eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Búsqueda de colaboradores
     */
    public function search(): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_ver');

        $query = $this->input('q');

        if (empty($query)) {
            $this->redirect('/colaboradores');
        }

        $resultados = $this->colaboradorModel->search($query);

        $this->view('colaboradores.search', [
            'pageTitle' => 'Resultados de búsqueda',
            'pageDescription' => "Búsqueda: {$query}",
            'query' => $query,
            'resultados' => $resultados,
            'breadcrumbs' => [
                ['label' => 'Colaboradores', 'url' => '/colaboradores'],
                ['label' => 'Búsqueda']
            ]
        ]);
    }

    /**
     * Ver red jerárquica
     */
    public function network(string $id): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_ver');

        $colaborador = $this->colaboradorModel->getById((int) $id);

        if (!$colaborador) {
            $this->setFlash('Colaborador no encontrado', 'error');
            $this->redirect('/colaboradores');
        }

        $this->view('colaboradores.network', [
            'pageTitle' => 'Red Jerárquica',
            'pageDescription' => Helpers::formatFullName($colaborador['nombres'], $colaborador['apellidos']),
            'colaborador' => $colaborador,
            'breadcrumbs' => [
                ['label' => 'Colaboradores', 'url' => '/colaboradores'],
                ['label' => $colaborador['documento'], 'url' => "/colaboradores/{$id}"],
                ['label' => 'Red']
            ]
        ]);
    }

    /**
     * Obtener datos de red jerárquica (JSON)
     */
    public function networkData(string $id): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_ver');

        $colaborador = $this->colaboradorModel->getById((int) $id);

        if (!$colaborador) {
            $this->jsonError('Colaborador no encontrado', null, 404);
        }

        try {
            $db = $this->db();
            $red = $db->getRedJerarquica($colaborador['documento']);

            // Transformar datos para Vis.js
            $nodes = [];
            $edges = [];

            foreach ($red as $nodo) {
                $nodes[] = [
                    'id' => $nodo['documento'],
                    'label' => $nodo['nombres'] . ' ' . $nodo['apellidos'],
                    'title' => "Perfil: {$nodo['perfil']}\nSeguidores: {$nodo['seguidores_directos']}",
                    'level' => (int) $nodo['nivel'],
                    'color' => $this->getColorByPerfil($nodo['perfil']),
                    'shape' => 'dot',
                    'size' => 20 + ($nodo['seguidores_directos'] * 2)
                ];
            }

            $nodeIds = array_column($nodes, 'id');
            foreach ($red as $nodo) {
                if ($nodo['lider_directo'] && in_array($nodo['lider_directo'], $nodeIds)) {
                    $edges[] = [
                        'from' => $nodo['lider_directo'],
                        'to' => $nodo['documento'],
                        'arrows' => 'to'
                    ];
                }
            }

            $this->jsonSuccess([
                'nodes' => $nodes,
                'edges' => $edges,
                'stats' => [
                    'total' => count($nodes),
                    'niveles' => max(array_column($red, 'nivel'))
                ]
            ]);

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'network_data', 'id' => $id]);
            $this->jsonError('Error al cargar red: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar líder de un colaborador
     */
    public function changeLeader(string $id): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        $nuevoLider = $this->input('nuevo_lider');
        $motivo = $this->input('motivo', 'Cambio manual');

        if (empty($nuevoLider)) {
            $this->jsonError('Debe especificar el nuevo líder');
        }

        try {
            $db = $this->db();
            $db->cambiarLider((int) $id, $nuevoLider, $motivo);

            Logger::info('Líder cambiado', [
                'colaborador_id' => $id,
                'nuevo_lider' => $nuevoLider,
                'user_id' => $this->user['id']
            ]);

            $this->jsonSuccess(null, 'Líder actualizado con éxito');

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'cambiar_lider', 'id' => $id]);
            $this->jsonError('Error al cambiar líder: ' . $e->getMessage());
        }
    }

    /**
     * Importar colaboradores desde Excel
     */
    /**
     * Importar colaboradores desde Excel
     */
    public function import(): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_crear');

        if (!$this->hasFile('archivo')) {
            $this->jsonError('Debe seleccionar un archivo');
        }

        $file = $this->file('archivo');
        $inputFileName = $file['tmp_name'];

        try {
            // Identificar el tipo de archivo
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            // Crear el reader
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            // Cargar el archivo
            $spreadsheet = $reader->load($inputFileName);
            // Obtener la hoja activa
            $sheet = $spreadsheet->getActiveSheet();
            // Obtener las dimensiones
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            // Obtener encabezados (fila 1)
            $headers = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE)[0];

            // Normalizar encabezados para búsqueda
            $normalizedHeaders = array_map(function ($h) {
                return strtolower(trim(str_replace(['.', ' '], '_', $this->removeAccents($h))));
            }, $headers);

            $colaboradores = [];
            $errores = [];

            // Iterar desde la fila 2
            for ($row = 2; $row <= $highestRow; $row++) {
                // Leer fila
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE)[0];

                // Si la fila está vacía, saltar
                if (empty(array_filter($rowData))) {
                    continue;
                }

                // Mapear datos según encabezados (o posición si no coinciden)
                // Se intenta buscar por nombre de columna, si no, se usa posición fija

                $data = [
                    'documento' => $this->getValueByHeader($rowData, $normalizedHeaders, ['documento', 'cedula', 'identificacion'], 0),
                    'nombres' => $this->getValueByHeader($rowData, $normalizedHeaders, ['nombres', 'nombre'], 1),
                    'apellidos' => $this->getValueByHeader($rowData, $normalizedHeaders, ['apellidos', 'apellido'], 2),
                    'celular' => $this->getValueByHeader($rowData, $normalizedHeaders, ['celular', 'telefono', 'movil'], 3),
                    'email' => $this->getValueByHeader($rowData, $normalizedHeaders, ['email', 'correo'], 4),
                    'departamento' => $this->getValueByHeader($rowData, $normalizedHeaders, ['departamento'], 5),
                    'municipio' => $this->getValueByHeader($rowData, $normalizedHeaders, ['municipio', 'ciudad'], 6),
                    'perfil' => $this->getValueByHeader($rowData, $normalizedHeaders, ['perfil', 'rol'], 7),
                    'nivel_participacion' => $this->getValueByHeader($rowData, $normalizedHeaders, ['nivel', 'participacion'], 8),
                    'dato_potencial' => (int) $this->getValueByHeader($rowData, $normalizedHeaders, ['potencial', 'dato_potencial', 'votos_potenciales'], 9),
                    'dato_historico' => (int) $this->getValueByHeader($rowData, $normalizedHeaders, ['historico', 'dato_historico', 'votos_historicos'], 10),
                    'lider_directo' => $this->getValueByHeader($rowData, $normalizedHeaders, ['lider', 'lider_directo', 'documento_lider'], 11),
                    'observaciones' => $this->getValueByHeader($rowData, $normalizedHeaders, ['observaciones', 'notas'], 12),
                ];

                // Valores por defecto y limpieza
                $data['tipo_documento'] = 'CC'; // Por defecto
                $data['fecha_nacimiento'] = '2000-01-01'; // Fecha dummy si no viene
                $data['genero'] = 'Otro';

                // Validar perfil (mapeo simple si viene diferente)
                $data['perfil'] = $this->normalizePerfil($data['perfil']);

                // Validar nivel
                $data['nivel_participacion'] = $this->normalizeNivel($data['nivel_participacion']);

                $colaboradores[] = $data;
            }

            if (empty($colaboradores)) {
                $this->jsonError('No se encontraron datos válidos en el archivo');
            }

            // Procesar importación masiva
            $resultado = $this->colaboradorModel->importarMasivo($colaboradores, $this->user['id']);

            Logger::info('Importación masiva Excel', [
                'archivo' => $file['name'],
                'exitosos' => $resultado['exitosos'],
                'errores' => count($resultado['errores']),
                'user_id' => $this->user['id']
            ]);

            $this->jsonSuccess($resultado, 'Importación completada');

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'importar_colaboradores_excel']);
            $this->jsonError('Error en importación: ' . $e->getMessage());
        }
    }

    /**
     * Helper para obtener valor por header o índice
     */
    private function getValueByHeader($row, $headers, $searchKeys, $defaultIndex)
    {
        foreach ($searchKeys as $key) {
            $index = array_search($key, $headers);
            if ($index !== false && isset($row[$index])) {
                return trim($row[$index]);
            }
        }
        // Fallback a índice numérico si existe
        return isset($row[$defaultIndex]) ? trim($row[$defaultIndex]) : '';
    }

    /**
     * Helper para remover acentos
     */
    private function removeAccents($string)
    {
        $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
        $string = preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string);
        return $string;
    }

    /**
     * Normalizar nombre de perfil
     */
    private function normalizePerfil($input)
    {
        if (empty($input))
            return 'Lider Comunitario'; // Default

        // Mapeo de posibles entradas a claves de PERFILES
        // Se intenta buscar coincidencia exacta o parcial
        foreach (PERFILES as $key => $value) {
            if (stripos($input, $value) !== false || stripos($input, $key) !== false) {
                return $key;
            }
        }

        return 'Lider Comunitario'; // Fallback
    }

    /**
     * Normalizar nivel de participación
     */
    private function normalizeNivel($input)
    {
        if (empty($input))
            return 'Simpatizante'; // Default

        foreach (NIVELES_PARTICIPACION as $key => $value) {
            if (stripos($input, $value) !== false || stripos($input, $key) !== false) {
                return $key;
            }
        }

        return 'Simpatizante'; // Fallback
    }

    /**
     * Exportar colaboradores
     */
    public function export(): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_ver');

        $formato = $this->input('formato', 'excel');
        $filtros = [
            'perfil' => $this->input('perfil'),
            'estado' => $this->input('estado'),
            'departamento' => $this->input('departamento'),
        ];

        try {
            $colaboradores = $this->colaboradorModel->getAll(999999, 0, $filtros);

            if ($formato === 'csv') {
                $this->exportCSV($colaboradores);
            } else {
                $this->exportExcel($colaboradores);
            }

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'exportar_colaboradores']);
            $this->setFlash('Error al exportar: ' . $e->getMessage(), 'error');
            $this->redirect('/colaboradores');
        }
    }

    /**
     * Mostrar formulario de importación
     */
    public function showImport(): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_crear');

        $this->view('colaboradores.import', [
            'pageTitle' => 'Importar Colaboradores',
            'pageDescription' => 'Carga masiva desde Excel',
            'breadcrumbs' => [
                ['label' => 'Colaboradores', 'url' => '/colaboradores'],
                ['label' => 'Importar']
            ]
        ]);
    }

    /**
     * Descargar plantilla de importación
     */
    public function downloadTemplate(): void
    {
        $this->requireAuth();

        // Headers para descarga de Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="plantilla_importacion_colaboradores.xlsx"');
        header('Cache-Control: max-age=0');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $headers = [
            'Documento',
            'Nombres',
            'Apellidos',
            'Celular',
            'Email',
            'Departamento',
            'Municipio',
            'Perfil',
            'Nivel Participación',
            'Dato Potencial',
            'Dato Histórico',
            'Líder Directo',
            'Observaciones'
        ];

        // Escribir encabezados
        $sheet->fromArray([$headers], NULL, 'A1');

        // Estilo para encabezados
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        $sheet->getStyle('A1:M1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCCCCC');

        // Datos de ejemplo
        $example = [
            '123456789',
            'Juan',
            'Pérez',
            '3001234567',
            'juan@ejemplo.com',
            'Antioquia',
            'Medellín',
            'Lider Comunitario',
            'Militante',
            '50',
            '20',
            '987654321',
            'Colaborador activo en comuna 1'
        ];

        $sheet->fromArray([$example], NULL, 'A2');

        // Ajustar ancho de columnas
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Agregar validación de datos para Perfil (Lista desplegable)
        $validation = $sheet->getCell('H2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"' . implode(',', array_keys(PERFILES)) . '"');

        // Copiar validación a más filas (ej. hasta 100)
        for ($i = 3; $i <= 100; $i++) {
            $sheet->getCell("H$i")->setDataValidation(clone $validation);
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Exportar a CSV
     */
    private function exportCSV(array $data): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="colaboradores_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Headers
        fputcsv($output, [
            'Documento',
            'Nombres',
            'Apellidos',
            'Email',
            'Celular',
            'Departamento',
            'Municipio',
            'Perfil',
            'Estado',
            'Dato Potencial',
            'Dato Histórico'
        ]);

        // Datos
        foreach ($data as $row) {
            fputcsv($output, [
                $row['documento'],
                $row['nombres'],
                $row['apellidos'],
                $row['email'],
                $row['celular'],
                $row['departamento'],
                $row['municipio'],
                $row['perfil'],
                $row['estado'],
                $row['dato_potencial'],
                $row['dato_historico']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Exportar a Excel (requiere PhpSpreadsheet)
     */
    private function exportExcel(array $data): void
    {
        // TODO: Implementar con PhpSpreadsheet cuando se instale composer
        $this->exportCSV($data);
    }

    /**
     * Obtener color por perfil
     */
    private function getColorByPerfil(string $perfil): string
    {
        $colores = COLORES_PERFIL;
        return $colores[$perfil] ?? '#6b7280';
    }

    /**
     * Historial de cambios
     */
    public function history(string $id): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_ver');

        $colaborador = $this->colaboradorModel->getById((int) $id);

        if (!$colaborador) {
            $this->jsonError('Colaborador no encontrado', null, 404);
        }

        $historial = $this->colaboradorModel->getHistorialCambiosLider((int) $id);

        $this->jsonSuccess($historial);
    }

    /**
     * Reevaluar dato potencial de un colaborador
     * POST /colaboradores/{id}/reevaluar
     */
    public function reevaluar(string $id): void
    {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        try {
            // Obtener datos JSON
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                $this->jsonError('Datos inválidos', null, 400);
                return;
            }

            $nuevoPotencial = (int) ($input['nuevo_dato_potencial'] ?? 0);
            $motivo = trim($input['motivo'] ?? '');

            if (empty($motivo)) {
                $this->jsonError('El motivo es requerido', null, 400);
                return;
            }

            // Verificar que el colaborador existe
            $colaborador = $this->colaboradorModel->getById((int) $id);
            if (!$colaborador) {
                $this->jsonError('Colaborador no encontrado', null, 404);
                return;
            }

            // Registrar la reevaluación
            $success = $this->colaboradorModel->registrarReevaluacion(
                (int) $id,
                $nuevoPotencial,
                $motivo,
                $_SESSION['user_id'] ?? null
            );

            if ($success) {
                $this->jsonSuccess([
                    'message' => 'Reevaluación registrada correctamente',
                    'nuevo_dato_potencial' => $nuevoPotencial,
                    'colaborador_id' => $id
                ]);
            } else {
                $this->jsonError('No se pudo registrar la reevaluación', null, 500);
            }

        } catch (\Exception $e) {
            \App\Utils\Logger::exception($e, ['action' => 'reevaluar', 'colaborador_id' => $id]);
            $this->jsonError('Error interno: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * API: Obtener departamentos
     * GET /api/territorios/departamentos
     */
    public function getDepartamentos(): void
    {
        try {
            $territorioModel = new \App\Models\Territorio();
            $departamentos = $territorioModel->getDepartamentos();

            $this->jsonSuccess($departamentos);
        } catch (\Exception $e) {
            Logger::error('Error al obtener departamentos: ' . $e->getMessage());
            $this->jsonError('Error al obtener departamentos');
        }
    }

    /**
     * API: Obtener municipios por departamento
     * GET /api/territorios/municipios?departamento=xxx
     */
    public function getMunicipios(): void
    {
        $departamento = $this->input('departamento');

        if (empty($departamento)) {
            $this->jsonError('Departamento requerido', null, 400);
            return;
        }

        try {
            $territorioModel = new \App\Models\Territorio();
            $municipios = $territorioModel->getMunicipiosByDepartamento($departamento);

            $this->jsonSuccess($municipios);
        } catch (\Exception $e) {
            Logger::error('Error al obtener municipios: ' . $e->getMessage());
            $this->jsonError('Error al obtener municipios');
        }
    }

    /**
     * API: Obtener tipos de territorio por departamento y municipio
     * GET /api/territorios/tipos?departamento=xxx&municipio=yyy
     */
    public function getTiposTerritor(): void
    {
        $departamento = $this->input('departamento');
        $municipio = $this->input('municipio');

        if (empty($departamento) || empty($municipio)) {
            $this->jsonError('Departamento y municipio requeridos', null, 400);
            return;
        }

        try {
            $territorioModel = new \App\Models\Territorio();
            $tipos = $territorioModel->getTiposTerritorio($departamento, $municipio);

            $this->jsonSuccess($tipos);
        } catch (\Exception $e) {
            Logger::error('Error al obtener tipos de territorio: ' . $e->getMessage());
            $this->jsonError('Error al obtener tipos de territorio');
        }
    }

    /**
     * API: Obtener territorios
     * GET /api/territorios/territorios?departamento=xxx&municipio=yyy&tipo=zzz
     */
    public function getTerritorios(): void
    {
        $departamento = $this->input('departamento');
        $municipio = $this->input('municipio');
        $tipoTerritorio = $this->input('tipo');

        if (empty($departamento) || empty($municipio) || empty($tipoTerritorio)) {
            $this->jsonError('Departamento, municipio y tipo requeridos', null, 400);
            return;
        }

        try {
            $territorioModel = new \App\Models\Territorio();
            $territorios = $territorioModel->getTerritorios($departamento, $municipio, $tipoTerritorio);

            $this->jsonSuccess($territorios);
        } catch (\Exception $e) {
            Logger::error('Error al obtener territorios: ' . $e->getMessage());
            $this->jsonError('Error al obtener territorios');
        }
    }

    /**
     * API: Obtener barrios
     * GET /api/territorios/barrios?departamento=xxx&municipio=yyy&tipo=zzz&territorio=www
     */
    public function getBarrios(): void
    {
        $departamento = $this->input('departamento');
        $municipio = $this->input('municipio');
        $tipoTerritorio = $this->input('tipo');
        $territorio = $this->input('territorio');

        if (empty($departamento) || empty($municipio) || empty($tipoTerritorio) || empty($territorio)) {
            $this->jsonError('Todos los parámetros son requeridos', null, 400);
            return;
        }

        try {
            $territorioModel = new \App\Models\Territorio();
            $barrios = $territorioModel->getBarrios($departamento, $municipio, $tipoTerritorio, $territorio);

            $this->jsonSuccess($barrios);
        } catch (\Exception $e) {
            Logger::error('Error al obtener barrios: ' . $e->getMessage());
            $this->jsonError('Error al obtener barrios');
        }
    }

    /**
     * API: Obtener puestos de votación por municipio (o cod_mpio)
     * GET /api/territorios/puestos?municipio=xxx&departamento=yyy
     */
    public function getPuestos(): void
    {
        $municipio = $this->input('municipio');
        $departamento = $this->input('departamento');

        if (empty($municipio) || empty($departamento)) {
            $this->jsonError('Municipio y departamento requeridos', null, 400);
            return;
        }

        try {
            $db = \Database::getInstance();
            // Buscar en la tabla puestos_votacion si existe, o en territorios si es fallback
            // El sistema suele usar puestos_votacion
            $sql = "SELECT DISTINCT puesto 
                    FROM puestos_votacion 
                    WHERE municipio = ? AND departamento = ? AND estado = 'Activo' 
                    ORDER BY puesto ASC";
            $puestos = $db->fetchAll($sql, [$municipio, $departamento]);
            
            if (empty($puestos)) {
                // Intento alternativo: buscar por cod_mpio si está disponible en territorios
                $sqlM = "SELECT DISTINCT cod_mpio FROM territorios WHERE municipio = ? AND departamento = ? LIMIT 1";
                $cod = $db->fetchOne($sqlM, [$municipio, $departamento]);
                if ($cod) {
                    $sql = "SELECT DISTINCT puesto FROM puestos_votacion WHERE cod_mpio = ? AND estado = 'Activo' ORDER BY puesto ASC";
                    $puestos = $db->fetchAll($sql, [$cod['cod_mpio']]);
                }
            }

            $this->jsonSuccess(array_column($puestos, 'puesto'));
        } catch (\Exception $e) {
            Logger::error('Error al obtener puestos: ' . $e->getMessage());
            $this->jsonError('Error al obtener puestos');
        }
    }
}

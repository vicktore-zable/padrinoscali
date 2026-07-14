<?php
/**
 * Modelo Colaborador
 * Gestión de colaboradores con relaciones jerárquicas
 *
 * @package App\Models
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Models;

class Colaborador {
    /**
     * Nombre de la tabla
     */
    const TABLE = 'colaboradores';

    /**
     * Instancia de Database
     * @var \Database
     */
    private \Database $db;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = \Database::getInstance();
    }

    /**
     * Obtener todos los colaboradores con paginación
     *
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return array
     */
    public function getAll(int $page = 1, int $perPage = 25, array $filters = []): array {
        $offset = ($page - 1) * $perPage;
        $where = $this->buildFilters($filters);

        $sql = "SELECT * FROM v_colaboradores_completo";

        if (!empty($where['where'])) {
            $sql .= " WHERE " . $where['where'];
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params = array_merge($where['params'], [$perPage, $offset]);

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Contar colaboradores con filtros
     *
     * @param array $filters
     * @return int
     */
    public function count(array $filters = []): int {
        $where = $this->buildFilters($filters);
        $sql = "SELECT COUNT(*) FROM " . self::TABLE;

        if (!empty($where['where'])) {
            $sql .= " WHERE " . $where['where'];
        }

        return (int) $this->db->fetchColumn($sql, $where['params']);
    }

    /**
     * Obtener colaborador por ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array {
        $sql = "SELECT * FROM v_colaboradores_completo WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Obtener colaborador por documento
     *
     * @param string $documento
     * @return array|null
     */
    public function getByDocumento(string $documento): ?array {
        $sql = "SELECT * FROM v_colaboradores_completo WHERE documento = ?";
        return $this->db->fetchOne($sql, [$documento]);
    }

    /**
     * Crear colaborador
     *
     * @param array $data
     * @return int|false ID del colaborador creado o false
     */
    public function create(array $data) {
        // Validar que el documento no exista
        if ($this->existsByDocumento($data['documento'])) {
            throw new \Exception('El documento ya está registrado');
        }

        // Validar que el líder exista (si se proporciona)
        if (!empty($data['lider_directo'])) {
            if (!$this->existsByDocumento($data['lider_directo'])) {
                throw new \Exception('El líder especificado no existe');
            }
        }

        // Convertir areas_interes a JSON si es array
        if (isset($data['areas_interes']) && is_array($data['areas_interes'])) {
            $data['areas_interes'] = json_encode($data['areas_interes']);
        }

        // Remover campos calculados si vienen en $data
        unset($data['estado'], $data['grupo_etareo'], $data['id']);

        return $this->db->insert(self::TABLE, $data);
    }

    /**
     * Actualizar colaborador
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        // Validar que exista
        if (!$this->exists($id)) {
            throw new \Exception('El colaborador no existe');
        }

        // Si cambia documento, validar que no exista
        if (isset($data['documento'])) {
            $current = $this->getById($id);
            if ($data['documento'] !== $current['documento']) {
                if ($this->existsByDocumento($data['documento'])) {
                    throw new \Exception('El documento ya está registrado');
                }
            }
        }

        // Validar líder si se proporciona
        if (isset($data['lider_directo']) && !empty($data['lider_directo'])) {
            if (!$this->existsByDocumento($data['lider_directo'])) {
                throw new \Exception('El líder especificado no existe');
            }
        }

        // Convertir areas_interes a JSON si es array
        if (isset($data['areas_interes']) && is_array($data['areas_interes'])) {
            $data['areas_interes'] = json_encode($data['areas_interes']);
        }

        // Remover campos que no se deben actualizar
        unset($data['estado'], $data['grupo_etareo'], $data['id'], $data['created_at']);

        $result = $this->db->update(self::TABLE, $data, 'id = ?', [$id]);
        return $result > 0;
    }

    /**
     * Actualizar contraseña de colaborador con hash bcrypt
     *
     * @param int $id
     * @param string $password
     * @return bool
     */
    public function updatePassword(int $id, string $password): bool {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $result = $this->db->update(self::TABLE, ['password' => $hash], 'id = ?', [$id]);
        return $result > 0;
    }

    /**
     * Eliminar colaborador
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        // Verificar si tiene seguidores
        $seguidores = $this->getSeguidoresDirectos($id);
        if (count($seguidores) > 0) {
            throw new \Exception('No se puede eliminar un colaborador que tiene seguidores asignados');
        }

        $result = $this->db->delete(self::TABLE, 'id = ?', [$id]);
        return $result > 0;
    }

    /**
     * Verificar si existe por ID
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool {
        return $this->db->exists(self::TABLE, 'id = ?', [$id]);
    }

    /**
     * Verificar si existe por documento
     *
     * @param string $documento
     * @return bool
     */
    public function existsByDocumento(string $documento): bool {
        return $this->db->exists(self::TABLE, 'documento = ?', [$documento]);
    }

    /**
     * Obtener seguidores directos de un colaborador
     *
     * @param int $id
     * @return array
     */
    public function getSeguidoresDirectos(int $id): array {
        $colaborador = $this->getById($id);
        if (!$colaborador) {
            return [];
        }

        $sql = "SELECT * FROM v_colaboradores_completo
                WHERE lider_directo = ?
                ORDER BY nombres, apellidos";

        return $this->db->fetchAll($sql, [$colaborador['documento']]);
    }

    /**
     * Obtener red jerárquica completa de un líder
     *
     * @param string $documento
     * @return array
     */
    public function getRedJerarquica(string $documento): array {
        return $this->db->getRedJerarquica($documento);
    }

    /**
     * Cambiar líder de un colaborador
     *
     * @param string $documentoColaborador
     * @param string $documentoNuevoLider
     * @param string $motivo
     * @param int $usuarioId
     * @return bool
     */
    public function cambiarLider(
        string $documentoColaborador,
        string $documentoNuevoLider,
        string $motivo,
        int $usuarioId
    ): bool {
        return $this->db->cambiarLider(
            $documentoColaborador,
            $documentoNuevoLider,
            $motivo,
            $usuarioId
        );
    }

    /**
     * Obtener historial de cambios de líder
     *
     * @param string $documento
     * @return array
     */
    public function getHistorialCambiosLider(string $documento): array {
        $sql = "SELECT h.*,
                       u.usuario as usuario_nombre,
                       CONCAT(la.nombres, ' ', la.apellidos) as lider_anterior_nombre,
                       CONCAT(ln.nombres, ' ', ln.apellidos) as lider_nuevo_nombre
                FROM historial_cambios_lider h
                LEFT JOIN usuarios u ON h.usuario_cambio = u.id
                LEFT JOIN colaboradores la ON h.lider_anterior = la.documento
                LEFT JOIN colaboradores ln ON h.lider_nuevo = ln.documento
                WHERE h.colaborador_documento = ?
                ORDER BY h.fecha_cambio DESC";

        return $this->db->fetchAll($sql, [$documento]);
    }

    /**
     * Obtener colaboradores por perfil
     *
     * @param string $perfil
     * @return array
     */
    public function getByPerfil(string $perfil): array {
        $sql = "SELECT * FROM v_colaboradores_completo WHERE perfil = ? ORDER BY nombres, apellidos";
        return $this->db->fetchAll($sql, [$perfil]);
    }

    /**
     * Obtener colaboradores por territorio
     *
     * @param string $territorio
     * @return array
     */
    public function getByTerritorio(string $territorio): array {
        $sql = "SELECT * FROM v_colaboradores_completo WHERE territorio = ? ORDER BY nombres, apellidos";
        return $this->db->fetchAll($sql, [$territorio]);
    }

    /**
     * Obtener colaboradores por estado
     *
     * @param string $estado
     * @return array
     */
    public function getByEstado(string $estado): array {
        $sql = "SELECT * FROM v_colaboradores_completo WHERE estado = ? ORDER BY nombres, apellidos";
        return $this->db->fetchAll($sql, [$estado]);
    }

    /**
     * Obtener líderes (colaboradores con seguidores)
     *
     * @return array
     */
    public function getLideres(): array {
        $sql = "SELECT * FROM v_lideres_metricas ORDER BY total_seguidores_directos DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtener colaboradores con perfil de líder para asignar como líder directo
     * Retorna colaboradores cuyo perfil contiene la palabra "Líder"
     *
     * @return array
     */
    public function getLideresParaAsignar(): array {
        $sql = "SELECT id, documento, nombres, apellidos, perfil
                FROM colaboradores
                WHERE perfil LIKE '%Líder%' OR perfil LIKE '%Lider%'
                ORDER BY nombres ASC, apellidos ASC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Buscar colaboradores
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(string $query, int $limit = 20): array {
        $query = $this->db->escapeLike($query);

        $sql = "SELECT * FROM v_colaboradores_completo
                WHERE nombres LIKE ?
                   OR apellidos LIKE ?
                   OR documento LIKE ?
                   OR CONCAT(nombres, ' ', apellidos) LIKE ?
                ORDER BY nombres, apellidos
                LIMIT ?";

        $param = "%$query%";
        return $this->db->fetchAll($sql, [$param, $param, $param, $param, $limit]);
    }

    /**
     * Obtener estadísticas por perfil
     *
     * @return array
     */
    public function getEstadisticasPorPerfil(): array {
        return $this->db->fetchAll("SELECT * FROM v_estadisticas_perfil");
    }

    /**
     * Obtener estadísticas por territorio
     *
     * @return array
     */
    public function getEstadisticasPorTerritorio(): array {
        return $this->db->fetchAll("SELECT * FROM v_estadisticas_territorio");
    }

    /**
     * Obtener colaboradores recientes
     *
     * @param int $days
     * @param int $limit
     * @return array
     */
    public function getRecientes(int $days = 30, int $limit = 10): array {
        $sql = "SELECT * FROM v_colaboradores_completo
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$days, $limit]);
    }

    /**
     * Obtener datos para exportación
     *
     * @param array $filters
     * @return array
     */
    public function getForExport(array $filters = []): array {
        $where = $this->buildFilters($filters);

        $sql = "SELECT
                    documento,
                    nombres,
                    apellidos,
                    tipo_documento,
                    fecha_nacimiento,
                    edad,
                    genero,
                    grupo_etareo,
                    perfil,
                    nivel_participacion,
                    areas_interes,
                    dato_potencial,
                    dato_historico,
                    estado,
                    territorio,
                    tipo_territorio,
                    barrio,
                    nombre_lider,
                    total_seguidores,
                    observaciones,
                    created_at
                FROM v_colaboradores_completo";

        if (!empty($where['where'])) {
            $sql .= " WHERE " . $where['where'];
        }

        $sql .= " ORDER BY nombres, apellidos";

        return $this->db->fetchAll($sql, $where['params']);
    }

    /**
     * Importar colaboradores desde array
     *
     * @param array $colaboradores
     * @param int $usuarioId
     * @return array ['exitosos' => int, 'fallidos' => int, 'errores' => array]
     */
    public function importarMasivo(array $colaboradores, int $usuarioId): array {
        $exitosos = 0;
        $fallidos = 0;
        $errores = [];

        $this->db->beginTransaction();

        try {
            foreach ($colaboradores as $index => $data) {
                try {
                    $this->create($data);
                    $exitosos++;
                } catch (\Exception $e) {
                    $fallidos++;
                    $errores[] = [
                        'fila' => $index + 2, // +2 por header y índice base 0
                        'error' => $e->getMessage(),
                        'datos' => $data
                    ];
                }
            }

            $this->db->commit();

            // Registrar importación
            $this->db->insert('importaciones_excel', [
                'nombre_archivo' => 'importacion_' . date('Y-m-d_H-i-s'),
                'registros_procesados' => count($colaboradores),
                'registros_exitosos' => $exitosos,
                'registros_fallidos' => $fallidos,
                'errores' => json_encode($errores),
                'usuario_id' => $usuarioId
            ]);

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return [
            'exitosos' => $exitosos,
            'fallidos' => $fallidos,
            'errores' => $errores
        ];
    }

    /**
     * Construir filtros SQL
     *
     * @param array $filters
     * @return array ['where' => string, 'params' => array]
     */
    private function buildFilters(array $filters): array {
        $conditions = [];
        $params = [];

        if (!empty($filters['perfil'])) {
            $conditions[] = "perfil = ?";
            $params[] = $filters['perfil'];
        }

        if (!empty($filters['nivel_participacion'])) {
            $conditions[] = "nivel_participacion = ?";
            $params[] = $filters['nivel_participacion'];
        }

        if (!empty($filters['territorio'])) {
            $conditions[] = "territorio = ?";
            $params[] = $filters['territorio'];
        }

        if (!empty($filters['estado'])) {
            $conditions[] = "estado = ?";
            $params[] = $filters['estado'];
        }

        if (!empty($filters['genero'])) {
            $conditions[] = "genero = ?";
            $params[] = $filters['genero'];
        }

        if (!empty($filters['grupo_etareo'])) {
            $conditions[] = "grupo_etareo = ?";
            $params[] = $filters['grupo_etareo'];
        }

        if (!empty($filters['lider_directo'])) {
            $conditions[] = "lider_directo = ?";
            $params[] = $filters['lider_directo'];
        }

        if (isset($filters['busqueda']) && $filters['busqueda'] !== '') {
            $busqueda = $this->db->escapeLike($filters['busqueda']);
            $conditions[] = "(nombres LIKE ? OR apellidos LIKE ? OR documento LIKE ?)";
            $param = "%$busqueda%";
            $params[] = $param;
            $params[] = $param;
            $params[] = $param;
        }

        return [
            'where' => implode(' AND ', $conditions),
            'params' => $params
        ];
    }

    /**
     * Validar datos de colaborador
     *
     * @param array $data
     * @param bool $isUpdate
     * @return array Errores encontrados
     */
    public function validate(array $data, bool $isUpdate = false): array {
        $errors = [];

        // Campos requeridos
        if (!$isUpdate || isset($data['nombres'])) {
            if (empty($data['nombres'])) {
                $errors['nombres'] = 'El nombre es requerido';
            } elseif (strlen($data['nombres']) > 100) {
                $errors['nombres'] = 'El nombre no puede exceder 100 caracteres';
            }
        }

        if (!$isUpdate || isset($data['apellidos'])) {
            if (empty($data['apellidos'])) {
                $errors['apellidos'] = 'Los apellidos son requeridos';
            } elseif (strlen($data['apellidos']) > 100) {
                $errors['apellidos'] = 'Los apellidos no pueden exceder 100 caracteres';
            }
        }

        if (!$isUpdate || isset($data['documento'])) {
            if (empty($data['documento'])) {
                $errors['documento'] = 'El documento es requerido';
            } elseif (strlen($data['documento']) > 20) {
                $errors['documento'] = 'El documento no puede exceder 20 caracteres';
            }
        }

        if (!$isUpdate || isset($data['fecha_nacimiento'])) {
            if (empty($data['fecha_nacimiento'])) {
                $errors['fecha_nacimiento'] = 'La fecha de nacimiento es requerida';
            } else {
                $fecha = strtotime($data['fecha_nacimiento']);
                if (!$fecha || $fecha > time()) {
                    $errors['fecha_nacimiento'] = 'La fecha de nacimiento no es válida';
                }
            }
        }

        // Validar enums
        if (isset($data['perfil']) && !array_key_exists($data['perfil'], PERFILES)) {
            $errors['perfil'] = 'El perfil seleccionado no es válido';
        }

        if (isset($data['nivel_participacion']) && !array_key_exists($data['nivel_participacion'], NIVELES_PARTICIPACION)) {
            $errors['nivel_participacion'] = 'El nivel de participación seleccionado no es válido';
        }

        if (isset($data['genero']) && !array_key_exists($data['genero'], GENEROS)) {
            $errors['genero'] = 'El género seleccionado no es válido';
        }

        if (isset($data['tipo_documento']) && !array_key_exists($data['tipo_documento'], TIPOS_DOCUMENTO)) {
            $errors['tipo_documento'] = 'El tipo de documento seleccionado no es válido';
        }

        return $errors;
    }

    /**
     * Obtener top líderes
     *
     * @param int $limit
     * @return array
     */
    public function getTopLeaders(int $limit = 10): array {
        $sql = "SELECT *
                FROM v_lideres_metricas
                WHERE total_seguidores_directos > 0
                ORDER BY total_seguidores_directos DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Obtener colaboradores recientes
     *
     * @param int $days
     * @return array
     */
    public function getRecentColaboradores(int $days = 30): array {
        $sql = "SELECT *
                FROM v_colaboradores_completo
                WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                ORDER BY created_at DESC
                LIMIT 20";

        return $this->db->fetchAll($sql, [$days]);
    }

    /**
     * Obtener actividad reciente
     *
     * @param int $limit
     * @return array
     */
    public function getRecentActivity(int $limit = 10): array {
        $sql = "SELECT hcl.*,
                       CONCAT(c.nombres, ' ', c.apellidos) as colaborador_nombre,
                       CONCAT(l_ant.nombres, ' ', l_ant.apellidos) as lider_anterior_nombre,
                       CONCAT(l_nue.nombres, ' ', l_nue.apellidos) as lider_nuevo_nombre
                FROM historial_cambios_lider hcl
                INNER JOIN colaboradores c ON hcl.colaborador_documento = c.documento
                LEFT JOIN colaboradores l_ant ON hcl.lider_anterior = l_ant.documento
                LEFT JOIN colaboradores l_nue ON hcl.lider_nuevo = l_nue.documento
                ORDER BY hcl.fecha_cambio DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Obtener crecimiento mensual
     *
     * @param int $months
     * @return array
     */
    public function getCrecimientoMensual(int $months = 12): array {
        $sql = "SELECT
                    DATE_FORMAT(created_at, '%Y-%m') as mes,
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'Creciendo' THEN 1 ELSE 0 END) as creciendo,
                    SUM(CASE WHEN estado = 'Estancado' THEN 1 ELSE 0 END) as estancado,
                    SUM(CASE WHEN estado = 'Decreciendo' THEN 1 ELSE 0 END) as decreciendo
                FROM " . self::TABLE . "
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY mes
                ORDER BY mes DESC";

        return $this->db->fetchAll($sql, [$months]);
    }

    /**
     * Obtener estadísticas consolidadas por municipio
     *
     * @return array
     */
    public function getEstadisticasPorMunicipio(): array {
        $sql = "SELECT
                    municipio,
                    departamento,
                    COUNT(*) as total_colaboradores,
                    COUNT(DISTINCT barrio) as total_barrios,
                    COUNT(DISTINCT territorio) as total_territorios,
                    SUM(CASE WHEN tipo_territorio = 'Urbano' THEN 1 ELSE 0 END) as urbano,
                    SUM(CASE WHEN tipo_territorio = 'Rural' THEN 1 ELSE 0 END) as rural,
                    SUM(CASE WHEN estado = 'Creció' THEN 1 ELSE 0 END) as creciendo,
                    SUM(CASE WHEN estado = 'Igual' THEN 1 ELSE 0 END) as estancado,
                    SUM(CASE WHEN estado = 'Decrece' THEN 1 ELSE 0 END) as decreciendo
                FROM " . self::TABLE . "
                WHERE municipio IS NOT NULL AND municipio != ''
                GROUP BY municipio, departamento
                ORDER BY municipio";

        return $this->db->fetchAll($sql);
    }

    /**
     * Obtener colaboradores por perfil en un municipio
     *
     * @param string $municipio
     * @return array
     */
    public function getPerfilesPorMunicipio(string $municipio): array {
        $sql = "SELECT
                    perfil,
                    COUNT(*) as total
                FROM " . self::TABLE . "
                WHERE municipio = ?
                GROUP BY perfil
                ORDER BY total DESC";

        return $this->db->fetchAll($sql, [$municipio]);
    }

    /**
     * Obtener colaboradores por barrio en un municipio
     *
     * @param string $municipio
     * @return array
     */
    public function getBarriosPorMunicipio(string $municipio): array {
        $sql = "SELECT
                    COALESCE(barrio, 'Sin barrio') as barrio,
                    COUNT(*) as total,
                    SUM(CASE WHEN tipo_territorio = 'Urbano' THEN 1 ELSE 0 END) as urbano,
                    SUM(CASE WHEN tipo_territorio = 'Rural' THEN 1 ELSE 0 END) as rural
                FROM " . self::TABLE . "
                WHERE municipio = ?
                GROUP BY barrio
                ORDER BY total DESC";

        return $this->db->fetchAll($sql, [$municipio]);
    }

    /**
     * Obtener colaboradores por territorio en un municipio
     *
     * @param string $municipio
     * @return array
     */
    public function getTerritoriosPorMunicipio(string $municipio): array {
        $sql = "SELECT
                    COALESCE(territorio, 'Sin territorio') as territorio,
                    tipo_territorio,
                    COUNT(*) as total,
                    COUNT(DISTINCT barrio) as barrios
                FROM " . self::TABLE . "
                WHERE municipio = ?
                GROUP BY territorio, tipo_territorio
                ORDER BY total DESC";

        return $this->db->fetchAll($sql, [$municipio]);
    }

    /**
     * Obtener colaboradores por tipo de territorio en un municipio
     *
     * @param string $municipio
     * @return array
     */
    public function getTipoTerritorioPorMunicipio(string $municipio): array {
        $sql = "SELECT
                    COALESCE(tipo_territorio, 'Sin definir') as tipo_territorio,
                    COUNT(*) as total,
                    COUNT(DISTINCT territorio) as territorios,
                    COUNT(DISTINCT barrio) as barrios
                FROM " . self::TABLE . "
                WHERE municipio = ?
                GROUP BY tipo_territorio
                ORDER BY total DESC";

        return $this->db->fetchAll($sql, [$municipio]);
    }

    /**
     * Obtener detalles completos de un municipio
     *
     * @param string $municipio
     * @return array
     */
    public function getDetallesMunicipio(string $municipio): array {
        return [
            'perfiles' => $this->getPerfilesPorMunicipio($municipio),
            'barrios' => $this->getBarriosPorMunicipio($municipio),
            'territorios' => $this->getTerritoriosPorMunicipio($municipio),
            'tipos_territorio' => $this->getTipoTerritorioPorMunicipio($municipio)
        ];
    }

    /**
     * Obtener áreas de interés consolidadas
     * Cuenta cuántas personas tienen cada área de interés
     *
     * @return array
     */
    public function getAreasInteresConsolidadas(): array {
        $sql = "SELECT areas_interes FROM " . self::TABLE . " WHERE areas_interes IS NOT NULL AND areas_interes != ''";
        $rows = $this->db->fetchAll($sql);

        $areasCount = [];

        foreach ($rows as $row) {
            $areas = json_decode($row['areas_interes'], true);
            if (is_array($areas)) {
                foreach ($areas as $area) {
                    $area = trim($area);
                    if (!empty($area)) {
                        if (!isset($areasCount[$area])) {
                            $areasCount[$area] = 0;
                        }
                        $areasCount[$area]++;
                    }
                }
            }
        }

        // Ordenar por cantidad descendente
        arsort($areasCount);

        // Convertir a formato para gráficos
        $result = [];
        foreach ($areasCount as $area => $count) {
            $result[] = [
                'area' => $area,
                'total' => $count
            ];
        }

        return $result;
    }

    /**
     * Obtener colaboradores por municipio con conteo de líderes
     * Para el gráfico de burbujas
     *
     * @param int $limit
     * @return array
     */
    public function getColaboradoresPorMunicipio(int $limit = 20): array {
        $sql = "SELECT
                    municipio,
                    departamento,
                    COUNT(*) as total_colaboradores,
                    SUM(CASE WHEN lider_directo IS NULL OR lider_directo = '' THEN 1 ELSE 0 END) as total_lideres
                FROM " . self::TABLE . "
                WHERE municipio IS NOT NULL AND municipio != ''
                GROUP BY municipio, departamento
                ORDER BY total_colaboradores DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    // =========================================================================
    // MÉTODOS PARA TRAZABILIDAD HISTÓRICA DE ESTADOS
    // =========================================================================

    /**
     * Obtener historial de estados de un colaborador
     *
     * @param int $colaboradorId
     * @return array
     */
    public function getHistorialEstados(int $colaboradorId): array {
        $sql = "SELECT
                    h.*,
                    u.usuario as usuario_cambio
                FROM historial_estados h
                LEFT JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.colaborador_id = ?
                ORDER BY h.fecha_registro ASC";

        return $this->db->fetchAll($sql, [$colaboradorId]);
    }

    /**
     * Obtener trazabilidad completa de un colaborador usando la vista
     *
     * @param int $colaboradorId
     * @return array
     */
    public function getTrazabilidadCompleta(int $colaboradorId): array {
        $sql = "SELECT * FROM v_trazabilidad_colaborador WHERE colaborador_id = ? ORDER BY fecha_registro ASC";
        return $this->db->fetchAll($sql, [$colaboradorId]);
    }

    /**
     * Registrar cambio manual de estado/dato_potencial con motivo
     *
     * @param int $colaboradorId
     * @param int $datoPotencial
     * @param string $motivo
     * @param int $usuarioId
     * @return bool
     */
    public function registrarReevaluacion(int $colaboradorId, int $datoPotencial, string $motivo, int $usuarioId): bool {
        // Obtener colaborador actual
        $colaborador = $this->getById($colaboradorId);
        if (!$colaborador) {
            return false;
        }

        // Actualizar el colaborador (esto disparará el trigger automáticamente)
        $updated = $this->db->update(self::TABLE, [
            'dato_potencial' => $datoPotencial
        ], 'id = ?', [$colaboradorId]);

        if ($updated > 0) {
            // Actualizar el último registro de historial con el usuario y motivo
            $sql = "UPDATE historial_estados
                    SET usuario_id = ?, motivo = ?, tipo_cambio = 'reevaluacion'
                    WHERE colaborador_id = ?
                    ORDER BY fecha_registro DESC
                    LIMIT 1";

            $this->db->query($sql, [$usuarioId, $motivo, $colaboradorId]);
            return true;
        }

        return false;
    }

    /**
     * Obtener estadísticas de evolución de estados para el dashboard
     *
     * @param int $days Días hacia atrás
     * @return array
     */
    public function getEvolucionEstados(int $days = 30): array {
        $sql = "SELECT
                    DATE(fecha_registro) as fecha,
                    estado,
                    COUNT(*) as total_cambios,
                    ROUND(AVG(dato_potencial), 1) as promedio_potencial
                FROM historial_estados
                WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(fecha_registro), estado
                ORDER BY fecha ASC, estado";

        return $this->db->fetchAll($sql, [$days]);
    }

    /**
     * Obtener resumen de estados actuales vs históricos
     *
     * @return array
     */
    public function getResumenEstadosHistoricos(): array {
        $sql = "SELECT
                    estado,
                    COUNT(DISTINCT colaborador_id) as total_colaboradores,
                    COUNT(*) as total_registros,
                    ROUND(AVG(dato_potencial), 1) as promedio_potencial,
                    MIN(dato_potencial) as min_potencial,
                    MAX(dato_potencial) as max_potencial
                FROM historial_estados
                GROUP BY estado
                ORDER BY total_colaboradores DESC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Obtener tendencia de dato_potencial por fecha (para gráfico de líneas)
     *
     * @param int $colaboradorId
     * @return array
     */
    public function getTendenciaPotencial(int $colaboradorId): array {
        $sql = "SELECT
                    DATE(fecha_registro) as fecha,
                    dato_potencial,
                    dato_historico,
                    estado
                FROM historial_estados
                WHERE colaborador_id = ?
                ORDER BY fecha_registro ASC";

        return $this->db->fetchAll($sql, [$colaboradorId]);
    }

    /**
     * Obtener datos para gráfico de líneas del dashboard (tendencia general)
     *
     * @param int $days
     * @return array
     */
    public function getTendenciaGeneralPotencial(int $days = 30): array {
        $sql = "SELECT
                    DATE(fecha_registro) as fecha,
                    ROUND(AVG(dato_potencial), 1) as promedio_potencial,
                    COUNT(DISTINCT colaborador_id) as colaboradores_activos,
                    SUM(CASE WHEN estado = 'Nuevo' THEN 1 ELSE 0 END) as nuevos,
                    SUM(CASE WHEN estado = 'Creció' THEN 1 ELSE 0 END) as crecio,
                    SUM(CASE WHEN estado = 'Igual' THEN 1 ELSE 0 END) as igual,
                    SUM(CASE WHEN estado = 'Decrece' THEN 1 ELSE 0 END) as decrece,
                    SUM(CASE WHEN estado = 'Desvinculado' THEN 1 ELSE 0 END) as desvinculados
                FROM historial_estados
                WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(fecha_registro)
                ORDER BY fecha ASC";

        return $this->db->fetchAll($sql, [$days]);
    }

    /**
     * Obtener último cambio de estado de un colaborador
     *
     * @param int $colaboradorId
     * @return array|null
     */
    public function getUltimoCambioEstado(int $colaboradorId): ?array {
        $sql = "SELECT * FROM historial_estados
                WHERE colaborador_id = ?
                ORDER BY fecha_registro DESC
                LIMIT 1";

        return $this->db->fetchOne($sql, [$colaboradorId]);
    }

    /**
     * Contar total de cambios de estado de un colaborador
     *
     * @param int $colaboradorId
     * @return int
     */
    public function contarCambiosEstado(int $colaboradorId): int {
        $sql = "SELECT COUNT(*) FROM historial_estados WHERE colaborador_id = ?";
        return (int) $this->db->fetchColumn($sql, [$colaboradorId]);
    }

    // =========================================================================
    // MÉTODOS PARA PORTAL DEL LÍDER
    // =========================================================================

    /**
     * Obtener todos los documentos de la red descendiente de un líder
     * Método recursivo para obtener toda la red
     *
     * @param string $documentoLider Documento del líder raíz
     * @return array Lista de documentos de toda la red
     */
    public function getDownlineDocumentos(string $documentoLider): array {
        $documentos = [];
        $this->getDownlineRecursive($documentoLider, $documentos);
        return $documentos;
    }

    /**
     * Método recursivo para obtener la red descendiente
     *
     * @param string $documentoLider
     * @param array $documentos
     * @param int $depth
     */
    private function getDownlineRecursive(string $documentoLider, array &$documentos, int $depth = 0): void {
        // Prevenir recursión infinita
        if ($depth > 50) {
            return;
        }

        // Obtener seguidores directos
        $sql = "SELECT documento FROM " . self::TABLE . " WHERE lider_directo = ?";
        $seguidores = $this->db->fetchAll($sql, [$documentoLider]);

        foreach ($seguidores as $seguidor) {
            $doc = $seguidor['documento'];
            if (!in_array($doc, $documentos)) {
                $documentos[] = $doc;
                // Recursivamente obtener los seguidores de este seguidor
                $this->getDownlineRecursive($doc, $documentos, $depth + 1);
            }
        }
    }

    /**
     * Obtener desglose por barrio/territorio de una lista de documentos
     *
     * @param array $documentos Lista de documentos
     * @return array Desglose por territorio
     */
    public function getNeighborhoodBreakdown(array $documentos): array {
        if (empty($documentos)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($documentos), '?'));
        $sql = "SELECT 
                    COALESCE(municipio, 'Sin definir') as municipio,
                    COALESCE(barrio, 'Sin barrio') as barrio,
                    COUNT(*) as total
                FROM " . self::TABLE . "
                WHERE documento IN ($placeholders)
                GROUP BY municipio, barrio
                ORDER BY total DESC";

        return $this->db->fetchAll($sql, $documentos);
    }
}
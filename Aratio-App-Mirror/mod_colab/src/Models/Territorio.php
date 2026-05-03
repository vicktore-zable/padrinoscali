<?php
/**
 * Modelo Territorio
 * Gestiona la jerarquía geográfica desde la tabla territorios
 *
 * @package App\Models
 */

namespace App\Models;

use Database;

class Territorio {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener lista de departamentos únicos
     *
     * @return array
     */
    public function getDepartamentos(): array {
        $sql = "SELECT DISTINCT departamento
                FROM territorios
                WHERE departamento IS NOT NULL
                AND departamento != ''
                ORDER BY departamento ASC";

        $result = $this->db->fetchAll($sql);
        return array_column($result, 'departamento');
    }

    /**
     * Obtener municipios por departamento
     *
     * @param string $departamento
     * @return array
     */
    public function getMunicipiosByDepartamento(string $departamento): array {
        $sql = "SELECT DISTINCT municipio
                FROM territorios
                WHERE departamento = ?
                AND municipio IS NOT NULL
                AND municipio != ''
                ORDER BY municipio ASC";

        $result = $this->db->fetchAll($sql, [$departamento]);
        return array_column($result, 'municipio');
    }

    /**
     * Obtener tipos de territorio por departamento y municipio
     *
     * @param string $departamento
     * @param string $municipio
     * @return array
     */
    public function getTiposTerritorio(string $departamento, string $municipio): array {
        $sql = "SELECT DISTINCT Tipo_territorio
                FROM territorios
                WHERE departamento = ?
                AND municipio = ?
                AND Tipo_territorio IS NOT NULL
                AND Tipo_territorio != ''
                ORDER BY Tipo_territorio ASC";

        $result = $this->db->fetchAll($sql, [$departamento, $municipio]);
        return array_column($result, 'Tipo_territorio');
    }

    /**
     * Obtener territorios por departamento, municipio y tipo
     * Ordenamiento numérico para territorios tipo "Comuna 1", "Comuna 2", etc.
     *
     * @param string $departamento
     * @param string $municipio
     * @param string $tipoTerritorio
     * @return array
     */
    public function getTerritorios(string $departamento, string $municipio, string $tipoTerritorio): array {
        $sql = "SELECT DISTINCT Territorio
                FROM territorios
                WHERE departamento = ?
                AND municipio = ?
                AND Tipo_territorio = ?
                AND Territorio IS NOT NULL
                AND Territorio != ''
                ORDER BY
                    CAST(SUBSTRING_INDEX(Territorio, ' ', -1) AS UNSIGNED) ASC,
                    Territorio ASC";

        $result = $this->db->fetchAll($sql, [$departamento, $municipio, $tipoTerritorio]);
        return array_column($result, 'Territorio');
    }

    /**
     * Obtener barrios por departamento, municipio, tipo y territorio
     *
     * @param string $departamento
     * @param string $municipio
     * @param string $tipoTerritorio
     * @param string $territorio
     * @return array
     */
    public function getBarrios(string $departamento, string $municipio, string $tipoTerritorio, string $territorio): array {
        $sql = "SELECT DISTINCT barrio
                FROM territorios
                WHERE departamento = ?
                AND municipio = ?
                AND Tipo_territorio = ?
                AND Territorio = ?
                AND barrio IS NOT NULL
                AND barrio != ''
                ORDER BY barrio ASC";

        $result = $this->db->fetchAll($sql, [$departamento, $municipio, $tipoTerritorio, $territorio]);
        return array_column($result, 'barrio');
    }

    /**
     * Verificar si existe una combinación geográfica válida
     *
     * @param array $params
     * @return bool
     */
    public function existeCombinacion(array $params): bool {
        $conditions = [];
        $values = [];

        if (!empty($params['departamento'])) {
            $conditions[] = "departamento = ?";
            $values[] = $params['departamento'];
        }
        if (!empty($params['municipio'])) {
            $conditions[] = "municipio = ?";
            $values[] = $params['municipio'];
        }
        if (!empty($params['tipo_territorio'])) {
            $conditions[] = "Tipo_territorio = ?";
            $values[] = $params['tipo_territorio'];
        }
        if (!empty($params['territorio'])) {
            $conditions[] = "Territorio = ?";
            $values[] = $params['territorio'];
        }
        if (!empty($params['barrio'])) {
            $conditions[] = "barrio = ?";
            $values[] = $params['barrio'];
        }

        if (empty($conditions)) {
            return false;
        }

        $sql = "SELECT COUNT(*) as count
                FROM territorios
                WHERE " . implode(' AND ', $conditions);

        $result = $this->db->fetchOne($sql, $values);
        return ($result['count'] ?? 0) > 0;
    }
}

<?php
/**
 * Modelo Campaña
 * Gestión de múltiples campañas compatibles con aratio.mrmtech.net
 *
 * @package App\Models
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Models;

class Campana {
    /**
     * Nombre de la tabla
     */
    const TABLE = 'campanas';

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
     * Obtener todas las campañas
     *
     * @param bool $soloActivas
     * @return array
     */
    public function getAll(bool $soloActivas = false): array {
        $sql = "SELECT * FROM " . self::TABLE;
        if ($soloActivas) {
            $sql .= " WHERE activo = 1";
        }
        $sql .= " ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtener campaña por ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Obtener campaña por código externo (aratio.mrmtech.net ID)
     *
     * @param string $codigo
     * @return array|null
     */
    public function getByCodigoExterno(string $codigo): ?array {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE codigo_externo = ?";
        return $this->db->fetchOne($sql, [$codigo]);
    }

    /**
     * Crear una nueva campaña
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data) {
        return $this->db->insert(self::TABLE, $data);
    }

    /**
     * Actualizar una campaña
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $result = $this->db->update(self::TABLE, $data, 'id = ?', [$id]);
        return $result > 0;
    }

    /**
     * Eliminar una campaña (lógica o física según se prefiera, aquí física pero con cuidado)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        // Primero verificar si tiene colaboradores
        $sql = "SELECT COUNT(*) FROM colaboradores WHERE campana_id = ?";
        $count = (int) $this->db->fetchColumn($sql, [$id]);
        
        if ($count > 0) {
            throw new \Exception("No se puede eliminar la campaña porque tiene {$count} colaboradores asociados.");
        }

        $result = $this->db->delete(self::TABLE, 'id = ?', [$id]);
        return $result > 0;
    }

    /**
     * Obtener administradores asociados a una campaña
     *
     * @param int $id
     * @return array
     */
    public function getAdministradores(int $id): array {
        $sql = "SELECT id, usuario, email, tipo_usuario, activo 
                FROM usuarios 
                WHERE campana_id = ?";
        return $this->db->fetchAll($sql, [$id]);
    }

    /**
     * Obtener resumen de estadísticas de la campaña
     *
     * @param int $id
     * @return array
     */
    public function getStats(int $id): array {
        $sql = "SELECT 
                    COUNT(*) as total_colaboradores,
                    COUNT(DISTINCT lider_directo) as total_lideres,
                    SUM(CASE WHEN estado = 'Creció' THEN 1 ELSE 0 END) as crecieron,
                    SUM(CASE WHEN estado = 'Nuevo' THEN 1 ELSE 0 END) as nuevos
                FROM colaboradores 
                WHERE campana_id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
}

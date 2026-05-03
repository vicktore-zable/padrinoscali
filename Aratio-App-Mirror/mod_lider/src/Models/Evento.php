<?php
/**
 * Modelo de Eventos
 * 
 * @package App\Models
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Models;

class Evento {
    /**
     * Nombre de la tabla
     */
    const TABLE = 'eventos';
    
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
     * Obtener todos los eventos con paginación y filtros
     * 
     * @param int $page Página actual
     * @param int $perPage Registros por página
     * @param array $filters Filtros a aplicar
     * @return array
     */
    public function getAll(int $page = 1, int $perPage = 10, array $filters = []): array {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1=1';
        $params = [];
        
        if (!empty($filters['estado'])) {
            $where .= ' AND estado = ?';
            $params[] = $filters['estado'];
        }
        
        if (!empty($filters['campana_id'])) {
            $where .= ' AND campana_id = ?';
            $params[] = $filters['campana_id'];
        }
        
        if (!empty($filters['tipo'])) {
            $where .= ' AND tipo = ?';
            $params[] = $filters['tipo'];
        }
        
        $sql = "SELECT * FROM " . self::TABLE . " $where ORDER BY fecha_inicio ASC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Contar eventos con filtros
     * 
     * @param array $filters Filtros a aplicar
     * @return int
     */
    public function count(array $filters = []): int {
        $where = 'WHERE 1=1';
        $params = [];
        
        if (!empty($filters['estado'])) {
            $where .= ' AND estado = ?';
            $params[] = $filters['estado'];
        }
        
        if (!empty($filters['campana_id'])) {
            $where .= ' AND campana_id = ?';
            $params[] = $filters['campana_id'];
        }
        
        if (!empty($filters['tipo'])) {
            $where .= ' AND tipo = ?';
            $params[] = $filters['tipo'];
        }
        
        $sql = "SELECT COUNT(*) as total FROM " . self::TABLE . " $where";
        $result = $this->db->fetchOne($sql, $params);
        
        return (int) ($result['total'] ?? 0);
    }
    
    /**
     * Obtener evento por ID
     * 
     * @param int $id ID del evento
     * @return array|null
     */
    public function getById(int $id): ?array {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = ?";
        $result = $this->db->fetchOne($sql, [$id]);
        
        return $result ?: null;
    }
    
    /**
     * Obtener próximos eventos
     * 
     * @param int $limit Límite de resultados
     * @param int|null $campanaId ID de campaña opcional
     * @return array
     */
    public function getUpcoming(int $limit = 5, ?int $campanaId = null): array {
        $where = 'WHERE fecha_inicio >= CURDATE() AND estado IN ("programado", "confirmado")';
        $params = [];
        
        if ($campanaId) {
            $where .= ' AND campana_id = ?';
            $params[] = $campanaId;
        }
        
        $sql = "SELECT * FROM " . self::TABLE . " $where ORDER BY fecha_inicio ASC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtener eventos por campaña
     * 
     * @param int $campanaId ID de la campaña
     * @return array
     */
    public function getByCampana(int $campanaId): array {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE campana_id = ? ORDER BY fecha_inicio DESC";
        return $this->db->fetchAll($sql, [$campanaId]);
    }
}

<?php
/**
 * Modelo de Sesiones del Portal de Líderes
 * Tabla independiente: sesiones_lideres (NO usa la tabla sesiones ni usuarios)
 *
 * @package App\Models
 */

namespace App\Models;

class SesionLider {
    const TABLE = 'sesiones_lideres';

    private \Database $db;

    public function __construct() {
        $this->db = \Database::getInstance();
    }

    /**
     * Crear una nueva sesión para un líder
     *
     * @param int $colaboradorId ID del colaborador
     * @param string $ip Dirección IP del cliente
     * @param string $userAgent User-Agent del navegador
     * @return string Token de sesión
     */
    public function create(int $colaboradorId, string $ip, string $userAgent): string {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + (int)(SESSION_CONFIG['lifetime'] ?? 7200));

        $this->db->insert(self::TABLE, [
            'colaborador_id' => $colaboradorId,
            'token_sesion'   => $token,
            'ip_address'     => $ip,
            'user_agent'     => $userAgent,
            'expira_en'      => $expiresAt
        ]);

        return $token;
    }

    /**
     * Verificar si un token de sesión es válido
     *
     * @param string $token Token a verificar
     * @return array|null Datos del colaborador si es válido, null si expiró o no existe
     */
    public function verify(string $token): ?array {
        $sql = "SELECT sl.*, c.id AS col_id, c.documento, c.nombres, c.apellidos,
                       c.email, c.telefono, c.campana_id, c.foto
                FROM " . self::TABLE . " sl
                INNER JOIN colaboradores c ON sl.colaborador_id = c.id
                WHERE sl.token_sesion = ?
                  AND sl.expira_en > NOW()
                LIMIT 1";

        $result = $this->db->fetchOne($sql, [$token]);

        if ($result) {
            // Refrescar expiración (sliding window)
            $this->db->update(self::TABLE, [
                'expira_en' => date('Y-m-d H:i:s', time() + (int)(SESSION_CONFIG['lifetime'] ?? 7200))
            ], 'token_sesion = ?', [$token]);

            return $result;
        }

        return null;
    }

    /**
     * Eliminar una sesión específica
     *
     * @param string $token
     * @return bool
     */
    public function destroy(string $token): bool {
        $result = $this->db->delete(self::TABLE, 'token_sesion = ?', [$token]);
        return $result > 0;
    }

    /**
     * Eliminar todas las sesiones de un líder
     *
     * @param int $colaboradorId
     * @return bool
     */
    public function destroyAll(int $colaboradorId): bool {
        $result = $this->db->delete(self::TABLE, 'colaborador_id = ?', [$colaboradorId]);
        return $result > 0;
    }

    /**
     * Obtener sesiones activas de un líder
     *
     * @param int $colaboradorId
     * @return array
     */
    public function getActiveSessions(int $colaboradorId): array {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE colaborador_id = ? AND expira_en > NOW()
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, [$colaboradorId]);
    }

    /**
     * Limpiar sesiones expiradas
     *
     * @return int Número de sesiones eliminadas
     */
    public function cleanExpired(): int {
        $result = $this->db->delete(self::TABLE, 'expira_en < NOW()');
        return $result > 0 ? $result : 0;
    }
}

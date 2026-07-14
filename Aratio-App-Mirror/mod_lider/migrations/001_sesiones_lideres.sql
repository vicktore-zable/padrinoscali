-- =====================================================
-- Migración: Tabla sesiones_lideres
-- Fecha: 2026-06-17
-- Descripción: Tabla de sesiones independiente para el
--              Portal de Líderes (separada de usuarios)
-- =====================================================

CREATE TABLE IF NOT EXISTS sesiones_lideres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    token_sesion VARCHAR(128) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    expira_en DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_token (token_sesion),
    KEY idx_colaborador (colaborador_id),
    KEY idx_expira (expira_en),
    CONSTRAINT fk_sesiones_lider_col FOREIGN KEY (colaborador_id)
        REFERENCES colaboradores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Limpiar sesiones antiguas de la tabla sesiones que pertenecían a líderes
-- (Opcional: ejecutar solo si se quiere limpiar el historial)
-- DELETE FROM sesiones WHERE usuario_id NOT IN (SELECT id FROM usuarios);

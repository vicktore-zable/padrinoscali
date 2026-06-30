-- ============================================
-- MIGRACION: ALAS - Timeline Unificado
-- Sistema de Registro de Actividades
-- Version: 1.0.0 | 2026-06-29
-- ============================================

CREATE TABLE IF NOT EXISTS actividad_colaborador (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL COMMENT 'Tipo de actividad: evento_asistio, donacion_hizo, etc.',
    descripcion VARCHAR(255) NOT NULL,
    metadata_json JSON DEFAULT NULL COMMENT 'Datos adicionales',
    referencia_id INT DEFAULT NULL COMMENT 'ID del registro origen',
    referencia_tabla VARCHAR(50) DEFAULT NULL COMMENT 'Tabla origen',
    creado_por INT DEFAULT NULL COMMENT 'Usuario/admin que registró',
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
    INDEX idx_colaborador_tipo (colaborador_id, tipo),
    INDEX idx_colaborador_fecha (colaborador_id, creado_en DESC),
    INDEX idx_tipo_fecha (tipo, creado_en),
    INDEX idx_referencia (referencia_tabla, referencia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SISTEMA DE TRAZABILIDAD HISTÓRICA DE COLABORADORES
-- Migración: create_historial_estados
-- Fecha: 2025-11-22
-- =====================================================

-- Tabla para registrar el historial de estados y dato_potencial
CREATE TABLE IF NOT EXISTS historial_estados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT UNSIGNED NOT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dato_potencial INT NOT NULL DEFAULT 0,
    dato_historico INT NOT NULL DEFAULT 0,
    estado VARCHAR(20) NOT NULL,
    usuario_id INT UNSIGNED NULL COMMENT 'Usuario que realizó el cambio',
    motivo VARCHAR(255) NULL COMMENT 'Motivo del cambio (opcional)',
    tipo_cambio ENUM('inicial', 'actualizacion', 'reevaluacion') DEFAULT 'actualizacion',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_colaborador (colaborador_id),
    INDEX idx_fecha (fecha_registro),
    INDEX idx_estado (estado),
    INDEX idx_colaborador_fecha (colaborador_id, fecha_registro),

    CONSTRAINT fk_historial_colaborador
        FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_historial_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de cambios en dato_potencial y estado de colaboradores';

-- =====================================================
-- TRIGGER: Registrar cambio inicial al crear colaborador
-- =====================================================
DROP TRIGGER IF EXISTS trg_historial_estados_insert;

DELIMITER //
CREATE TRIGGER trg_historial_estados_insert
AFTER INSERT ON colaboradores
FOR EACH ROW
BEGIN
    INSERT INTO historial_estados (
        colaborador_id,
        fecha_registro,
        dato_potencial,
        dato_historico,
        estado,
        tipo_cambio
    ) VALUES (
        NEW.id,
        NOW(),
        NEW.dato_potencial,
        NEW.dato_historico,
        NEW.estado,
        'inicial'
    );
END//
DELIMITER ;

-- =====================================================
-- TRIGGER: Registrar cambio al actualizar colaborador
-- =====================================================
DROP TRIGGER IF EXISTS trg_historial_estados_update;

DELIMITER //
CREATE TRIGGER trg_historial_estados_update
AFTER UPDATE ON colaboradores
FOR EACH ROW
BEGIN
    -- Solo registrar si cambió dato_potencial o estado
    IF (OLD.dato_potencial != NEW.dato_potencial) OR (OLD.estado != NEW.estado) THEN
        INSERT INTO historial_estados (
            colaborador_id,
            fecha_registro,
            dato_potencial,
            dato_historico,
            estado,
            tipo_cambio
        ) VALUES (
            NEW.id,
            NOW(),
            NEW.dato_potencial,
            NEW.dato_historico,
            NEW.estado,
            'actualizacion'
        );
    END IF;
END//
DELIMITER ;

-- =====================================================
-- VISTA: Trazabilidad completa por colaborador
-- =====================================================
DROP VIEW IF EXISTS v_trazabilidad_colaborador;

CREATE VIEW v_trazabilidad_colaborador AS
SELECT
    h.id AS historial_id,
    h.colaborador_id,
    c.documento,
    CONCAT(c.nombres, ' ', c.apellidos) AS nombre_completo,
    c.perfil,
    h.fecha_registro,
    h.dato_potencial,
    h.dato_historico,
    h.estado,
    h.tipo_cambio,
    h.motivo,
    h.usuario_id,
    u.username AS usuario_cambio,
    -- Calcular variación respecto al registro anterior
    LAG(h.dato_potencial) OVER (PARTITION BY h.colaborador_id ORDER BY h.fecha_registro) AS dato_potencial_anterior,
    h.dato_potencial - LAG(h.dato_potencial) OVER (PARTITION BY h.colaborador_id ORDER BY h.fecha_registro) AS variacion_potencial,
    LAG(h.estado) OVER (PARTITION BY h.colaborador_id ORDER BY h.fecha_registro) AS estado_anterior
FROM historial_estados h
INNER JOIN colaboradores c ON h.colaborador_id = c.id
LEFT JOIN usuarios u ON h.usuario_id = u.id
ORDER BY h.colaborador_id, h.fecha_registro;

-- =====================================================
-- VISTA: Resumen de evolución de estados (para dashboard)
-- =====================================================
DROP VIEW IF EXISTS v_evolucion_estados;

CREATE VIEW v_evolucion_estados AS
SELECT
    DATE(fecha_registro) AS fecha,
    estado,
    COUNT(*) AS total_cambios,
    AVG(dato_potencial) AS promedio_potencial
FROM historial_estados
GROUP BY DATE(fecha_registro), estado
ORDER BY fecha DESC, estado;

-- =====================================================
-- PROCEDIMIENTO: Insertar historial inicial para colaboradores existentes
-- =====================================================
DROP PROCEDURE IF EXISTS sp_inicializar_historial;

DELIMITER //
CREATE PROCEDURE sp_inicializar_historial()
BEGIN
    -- Insertar registro inicial para colaboradores que no tienen historial
    INSERT INTO historial_estados (colaborador_id, fecha_registro, dato_potencial, dato_historico, estado, tipo_cambio)
    SELECT
        c.id,
        COALESCE(c.created_at, NOW()),
        c.dato_potencial,
        c.dato_historico,
        c.estado,
        'inicial'
    FROM colaboradores c
    LEFT JOIN historial_estados h ON c.id = h.colaborador_id
    WHERE h.id IS NULL;

    SELECT ROW_COUNT() AS registros_creados;
END//
DELIMITER ;

-- =====================================================
-- Ejecutar inicialización para colaboradores existentes
-- =====================================================
CALL sp_inicializar_historial();

-- Mostrar estado de la migración
SELECT 'Migración completada exitosamente' AS status;
SELECT COUNT(*) AS total_registros_historial FROM historial_estados;

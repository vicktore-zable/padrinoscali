-- ============================================
-- MIGRACION: ALAS - Workflow Engine
-- Sistema de Automatización de Seguimiento
-- Version: 1.0.0 | 2026-06-29
-- ============================================

CREATE TABLE IF NOT EXISTS workflow_reglas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    trigger_evento VARCHAR(50) NOT NULL COMMENT 'colaborador.registrado, evento.proximo, etc.',
    condiciones_json JSON DEFAULT NULL COMMENT 'Condiciones adicionales',
    acciones_json JSON NOT NULL COMMENT 'Array de acciones a ejecutar',
    activo BOOLEAN DEFAULT TRUE,
    prioridad INT DEFAULT 0,
    ejecuciones_total INT DEFAULT 0,
    ejecuciones_exitosas INT DEFAULT 0,
    ejecuciones_fallidas INT DEFAULT 0,
    creado_por INT DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_trigger_activo (trigger_evento, activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS workflow_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    regla_id INT NOT NULL,
    colaborador_id INT DEFAULT NULL,
    trigger_evento VARCHAR(50) NOT NULL,
    resultado ENUM('exitoso','fallido','pendiente') DEFAULT 'pendiente',
    acciones_ejecutadas INT DEFAULT 0,
    acciones_fallidas INT DEFAULT 0,
    error TEXT DEFAULT NULL,
    ejecutado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_regla (regla_id),
    INDEX idx_colaborador (colaborador_id),
    INDEX idx_fecha (ejecutado_en),
    FOREIGN KEY (regla_id) REFERENCES workflow_reglas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS workflow_acciones_pendientes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    regla_id INT NOT NULL,
    workflow_log_id BIGINT DEFAULT NULL,
    colaborador_id INT DEFAULT NULL,
    datos_json JSON NOT NULL,
    accion_tipo VARCHAR(50) NOT NULL COMMENT 'whatsapp, email, notificar, asignar_lider, cambiar_estado',
    accion_params JSON DEFAULT NULL,
    programado_para DATETIME DEFAULT NULL,
    estado ENUM('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
    error TEXT DEFAULT NULL,
    procesado_en DATETIME DEFAULT NULL,
    reintentos INT DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_programado (programado_para, estado),
    FOREIGN KEY (regla_id) REFERENCES workflow_reglas(id) ON DELETE CASCADE,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reglas predefinidas
INSERT INTO workflow_reglas (nombre, trigger_evento, condiciones_json, acciones_json, activo, prioridad) VALUES
('Bienvenida al registrarse', 'colaborador.registrado', '{}',
 '[{"tipo":"whatsapp","plantilla":"bienvenida","variables":["nombre"]},{"tipo":"asignar_lider","criterio":"territorio"}]',
 TRUE, 10),

('Recordatorio 24h antes del evento', 'evento.proximo', '{"dias_antes":1}',
 '[{"tipo":"whatsapp","plantilla":"recordatorio_evento","variables":["nombre","evento","hora","lugar"]}]',
 TRUE, 20),

('Agradecimiento post-evento', 'evento.finalizado', '{}',
 '[{"tipo":"whatsapp","plantilla":"gracias_asistencia","variables":["nombre","evento"]}]',
 TRUE, 30),

('Agradecimiento donación', 'donacion.recibida', '{}',
 '[{"tipo":"whatsapp","plantilla":"gracias_donacion","variables":["nombre","monto"]}]',
 TRUE, 40),

('Re-enganche por inactividad 30 días', 'colaborador.inactivo_30d', '{"dias":30}',
 '[{"tipo":"whatsapp","plantilla":"reactivacion","variables":["nombre"]},{"tipo":"cambiar_estado","estado":"Decrecio","motivo":"inactividad_30d"}]',
 TRUE, 50);

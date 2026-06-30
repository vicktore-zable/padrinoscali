CREATE TABLE IF NOT EXISTS llamadas_campanas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    objetivo TEXT COMMENT 'Script/guion para el agente',
    filtros_json JSON DEFAULT NULL COMMENT 'Filtros de segmentación para generar cola',
    estado ENUM('borrador','activa','pausada','completada') DEFAULT 'borrador',
    total_colaboradores INT DEFAULT 0,
    llamadas_completadas INT DEFAULT 0,
    creado_por INT DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS llamadas_cola (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    campana_id INT NOT NULL,
    colaborador_id INT NOT NULL,
    estado ENUM('pendiente','en_progreso','completada','saltada','no_contesta') DEFAULT 'pendiente',
    agente_id INT DEFAULT NULL COMMENT 'Usuario asignado',
    duracion_seg INT DEFAULT 0,
    programado_para DATETIME DEFAULT NULL,
    contactado_en DATETIME DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campana_id) REFERENCES llamadas_campanas(id) ON DELETE CASCADE,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
    INDEX idx_campana_estado (campana_id, estado),
    INDEX idx_agente (agente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS llamadas_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cola_id BIGINT NOT NULL,
    campana_id INT NOT NULL,
    colaborador_id INT NOT NULL,
    agente_id INT DEFAULT NULL,
    resultado VARCHAR(30) NOT NULL,
    notas TEXT,
    duracion_seg INT DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cola_id) REFERENCES llamadas_cola(id) ON DELETE CASCADE,
    FOREIGN KEY (campana_id) REFERENCES llamadas_campanas(id),
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
    INDEX idx_agente_fecha (agente_id, creado_en),
    INDEX idx_resultado (resultado),
    INDEX idx_campana (campana_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO workflow_reglas (nombre, trigger_evento, condiciones_json, acciones_json, prioridad, activo) VALUES
('Seguimiento llamada exitosa', 'llamada.finalizada', '{"resultado":"contestó"}', '[{"tipo":"whatsapp","plantilla":"gracias_llamada","variables":["nombre"]}]', 50, 1),
('Reagendar llamada fallida', 'llamada.finalizada', '{"resultado":"no_contesta"}', '[{"tipo":"cambiar_estado","estado":"llamar_despues"}]', 50, 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

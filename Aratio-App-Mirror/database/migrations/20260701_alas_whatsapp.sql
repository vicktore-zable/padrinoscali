-- ============================================
-- MIGRACION: ALAS - WhatsApp Bidireccional
-- Sistema de Comunicaciones Inteligentes
-- Version: 1.0.0 | 2026-06-29
-- ============================================

CREATE TABLE IF NOT EXISTS whatsapp_conversaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    wa_phone VARCHAR(20) NOT NULL COMMENT 'Número WhatsApp del colaborador',
    ultimo_mensaje TEXT,
    ultimo_tipo ENUM('enviado','recibido') DEFAULT NULL,
    ultimo_timestamp DATETIME DEFAULT NULL,
    unread INT DEFAULT 0,
    estado ENUM('activa','archivada','bloqueada') DEFAULT 'activa',
    metadata JSON DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
    UNIQUE KEY uk_colaborador (colaborador_id),
    INDEX idx_estado (estado),
    INDEX idx_unread (unread)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_mensajes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    conversacion_id INT NOT NULL,
    wa_message_id VARCHAR(100) DEFAULT NULL,
    direccion ENUM('enviado','recibido') NOT NULL,
    tipo ENUM('texto','imagen','template','interactivo') DEFAULT 'texto',
    contenido TEXT NOT NULL,
    metadata JSON DEFAULT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (conversacion_id) REFERENCES whatsapp_conversaciones(id) ON DELETE CASCADE,
    INDEX idx_timestamp (timestamp),
    INDEX idx_conversacion (conversacion_id, direccion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_plantillas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(50) NOT NULL COMMENT 'Código interno para el sistema',
    categoria ENUM('MARKETING','UTILITY','AUTHENTICATION') DEFAULT 'UTILITY',
    cuerpo TEXT NOT NULL COMMENT 'Template con {{variables}}',
    variables JSON DEFAULT NULL COMMENT 'Lista de nombres de variables',
    estado ENUM('pending','approved','rejected','paused') DEFAULT 'pending',
    meta_template_id VARCHAR(100) DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_nombre (nombre),
    UNIQUE KEY uk_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_broadcast_queue (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    plantilla_codigo VARCHAR(50) NOT NULL,
    colaborador_id INT NOT NULL,
    variables_json JSON DEFAULT NULL,
    estado ENUM('pending','sent','failed','cancelled') DEFAULT 'pending',
    error TEXT DEFAULT NULL,
    wa_message_id VARCHAR(100) DEFAULT NULL,
    programado_para DATETIME DEFAULT NULL,
    enviado_en DATETIME DEFAULT NULL,
    reintentos INT DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
    INDEX idx_estado_fecha (estado, programado_para),
    INDEX idx_programado (programado_para)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Plantillas predefinidas
INSERT INTO whatsapp_plantillas (nombre, codigo, categoria, cuerpo, variables, estado) VALUES
('Bienvenida', 'bienvenida', 'UTILITY', 'Hola {{nombre}}, bienvenido a Padrinos Cali! Te has registrado exitosamente. Pronto un líder de tu territorio te contactará. Juntos haremos la diferencia en Cali.', '["nombre"]', 'pending'),
('Recordatorio Evento', 'recordatorio_evento', 'UTILITY', 'Hola {{nombre}}, te recordamos que mañana es el evento {{evento}} a las {{hora}} en {{lugar}}. Te esperamos!', '["nombre","evento","hora","lugar"]', 'pending'),
('Gracias Asistencia', 'gracias_asistencia', 'MARKETING', 'Gracias {{nombre}} por asistir a {{evento}}! Tu participación fortalece nuestra comunidad. Pronto te informaremos de las próximas actividades. Un abrazo, Padrinos Cali.', '["nombre","evento"]', 'pending'),
('Gracias Donación', 'gracias_donacion', 'UTILITY', 'Gracias {{nombre}} por tu donación de ${{monto}}. Tu apoyo es fundamental para construir el Cali que queremos. Recibirás tu comprobante por este medio.', '["nombre","monto"]', 'pending'),
('Reactivación', 'reactivacion', 'MARKETING', 'Hola {{nombre}}, hacía tiempo que no sabíamos de ti! Queremos invitarte a retomar tu participación activa en Padrinos Cali. Responde este mensaje para saber cómo estás.', '["nombre"]', 'pending');

-- Seed: crear conversaciones para colaboradores existentes con teléfono
-- (opcional, se crean bajo demanda al recibir/enviar primer mensaje)

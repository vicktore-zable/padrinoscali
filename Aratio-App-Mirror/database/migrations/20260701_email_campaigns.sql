CREATE TABLE IF NOT EXISTS email_plantillas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    asunto VARCHAR(500) NOT NULL,
    cuerpo_html TEXT NOT NULL,
    variables JSON DEFAULT NULL COMMENT 'Lista de nombres de variables',
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_campanas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    asunto VARCHAR(500) NOT NULL,
    cuerpo_html TEXT NOT NULL,
    programada_para DATETIME DEFAULT NULL,
    estado ENUM('borrador','activa','enviando','completada','pausada') DEFAULT 'borrador',
    total_destinatarios INT DEFAULT 0,
    enviados INT DEFAULT 0,
    abiertos INT DEFAULT 0,
    creado_por INT DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_programada (programada_para)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_cola (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    campana_id INT NOT NULL,
    colaborador_id INT DEFAULT NULL,
    email_destino VARCHAR(200) NOT NULL,
    asunto VARCHAR(500) NOT NULL,
    cuerpo_html TEXT NOT NULL,
    estado ENUM('pendiente','enviado','fallido','abierto') DEFAULT 'pendiente',
    error TEXT DEFAULT NULL,
    enviado_en DATETIME DEFAULT NULL,
    abierto_en DATETIME DEFAULT NULL,
    reintentos INT DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campana_id) REFERENCES email_campanas(id) ON DELETE CASCADE,
    INDEX idx_estado (estado),
    INDEX idx_campana (campana_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    campana_id INT NOT NULL,
    colaborador_id INT DEFAULT NULL,
    email_destino VARCHAR(200) NOT NULL,
    estado ENUM('enviado','fallido','abierto') NOT NULL,
    error TEXT DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campana_id) REFERENCES email_campanas(id) ON DELETE CASCADE,
    INDEX idx_campana (campana_id),
    INDEX idx_estado_fecha (estado, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO email_plantillas (nombre, asunto, cuerpo_html, variables) VALUES
('Bienvenida', 'Bienvenido a Padrinos Cali, {{nombre}}',
 '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;padding:20px;max-width:600px;margin:0 auto"><h2 style="color:#2563eb">¡Bienvenido a Padrinos Cali!</h2><p>Hola <strong>{{nombre}}</strong>,</p><p>Nos alegra que te hayas unido a nuestra red de liderazgo social. Tu compromiso es el motor del cambio que Cali necesita.</p><p>En los próximos días un líder de tu territorio se comunicará contigo para coordinar las actividades en tu zona.</p><p>Mientras tanto, te invitamos a conocer más sobre nuestros programas y eventos.</p><br><p>Con gratitud,</p><p><strong>Equipo Padrinos Cali</strong></p><hr style="border:none;border-top:1px solid #e5e7eb"><p style="font-size:12px;color:#9ca3af">Este mensaje fue enviado desde el Sistema Aratio.</p></body></html>',
 '["nombre"]'),
('Invitación Evento', 'Invitación: {{evento}} — {{fecha}}',
 '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;padding:20px;max-width:600px;margin:0 auto"><h2 style="color:#2563eb">¡Te esperamos!</h2><p>Hola <strong>{{nombre}}</strong>,</p><p>Te invitamos al evento <strong>{{evento}}</strong> que se realizará el <strong>{{fecha}}</strong> a las <strong>{{hora}}</strong> en <strong>{{lugar}}</strong>.</p><p>Tu asistencia es muy importante para fortalecer nuestra red de liderazgo en Cali.</p><br><p>Confirma tu asistencia respondiendo a este correo.</p><br><p>¡Te esperamos!</p><p><strong>Equipo Padrinos Cali</strong></p><hr style="border:none;border-top:1px solid #e5e7eb"><p style="font-size:12px;color:#9ca3af">Este mensaje fue enviado desde el Sistema Aratio.</p></body></html>',
 '["nombre","evento","fecha","hora","lugar"]'),
('Boletín Mensual', 'Boletín Padrinos Cali — {{mes}} {{año}}',
 '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;padding:20px;max-width:600px;margin:0 auto"><h2 style="color:#2563eb">Boletín Padrinos Cali</h2><p><strong>{{mes}} {{año}}</strong></p><p>Hola <strong>{{nombre}}</strong>,</p><p>Este mes hemos logrado:</p><ul><li>{{logro1}}</li><li>{{logro2}}</li><li>{{logro3}}</li></ul><p>Gracias a tu participación, seguimos construyendo el Cali que queremos.</p><br><p>Sigue atento a nuestras convocatorias.</p><br><p><strong>Equipo Padrinos Cali</strong></p><hr style="border:none;border-top:1px solid #e5e7eb"><p style="font-size:12px;color:#9ca3af">Este mensaje fue enviado desde el Sistema Aratio.</p></body></html>',
 '["nombre","mes","ano","logro1","logro2","logro3"]')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

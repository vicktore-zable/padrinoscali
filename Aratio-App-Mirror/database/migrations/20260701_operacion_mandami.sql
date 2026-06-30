CREATE TABLE IF NOT EXISTS mandami_triggers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(100) NOT NULL,
    label VARCHAR(255) NOT NULL DEFAULT '',
    auto_reply_template TEXT NOT NULL,
    landing_url VARCHAR(500) DEFAULT NULL,
    enabled TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_keyword (keyword)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mandami_capture_flow (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    fb_user_id VARCHAR(100) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    trigger_keyword VARCHAR(100) NOT NULL,
    fb_post_id VARCHAR(100) DEFAULT NULL,
    fb_comment_id VARCHAR(100) DEFAULT NULL,
    dm_sent TINYINT(1) DEFAULT 0,
    dm_sent_at TIMESTAMP NULL DEFAULT NULL,
    dm_message_id VARCHAR(255) DEFAULT NULL,
    link_clicked TINYINT(1) DEFAULT 0,
    link_clicked_at TIMESTAMP NULL DEFAULT NULL,
    formulario_completado TINYINT(1) DEFAULT 0,
    formulario_completado_at TIMESTAMP NULL DEFAULT NULL,
    nombres VARCHAR(255) DEFAULT NULL,
    comuna VARCHAR(100) DEFAULT NULL,
    celular VARCHAR(50) DEFAULT NULL,
    colaborador_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fb_user (fb_user_id),
    INDEX idx_dm_sent (dm_sent),
    INDEX idx_form_completado (formulario_completado),
    INDEX idx_created (created_at DESC),
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO mandami_triggers (keyword, label, auto_reply_template, landing_url, enabled) VALUES
('quiero', 'Voluntariado', '¡Gracias por querer sumarte a esta campaña! Contame en qué comuna vivís para conectarte con tu líder de zona 👇', '/mandami/captura?origen=quiero', 1),
('comuna', 'Identificación territorial', '¡Gracias por comentar! Contame más de vos para sumarte al equipo de tu comuna 👇', '/mandami/captura?origen=comuna', 1),
('Cali', 'General', '¡Qué bueno que te interesa Cali! Dejame tus datos para mantenerte al tanto de todo 👇', '/mandami/captura?origen=cali', 1),
('evento', 'Asistencia a eventos', '¡Te esperamos en el próximo evento! Dejame tus datos para avisarte 👇', '/mandami/captura?origen=evento', 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), auto_reply_template = VALUES(auto_reply_template), landing_url = VALUES(landing_url), enabled = VALUES(enabled);

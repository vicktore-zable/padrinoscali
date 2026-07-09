-- ============================================
-- MIGRACION: Sistema de Cumpleaños por WhatsApp
-- Fecha: 2026-06-28
-- Version: 1.0.0
-- ============================================

CREATE TABLE IF NOT EXISTS whatsapp_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    colaborador_nombre VARCHAR(200),
    telefono_whatsapp VARCHAR(20),
    perfil VARCHAR(50),
    template_used VARCHAR(50),
    mensaje_enviado TEXT,
    estado ENUM('enviado','fallido','pendiente','sin_whatsapp') NOT NULL DEFAULT 'pendiente',
    error_msg TEXT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fecha (sent_at),
    INDEX idx_estado (estado),
    INDEX idx_colaborador (colaborador_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

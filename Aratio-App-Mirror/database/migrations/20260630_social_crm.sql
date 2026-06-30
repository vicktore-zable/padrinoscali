-- =============================================
-- Social CRM: Facebook + Instagram → CRM
-- v2.15.0 — 2026-06-30
-- =============================================

-- 1. Posts de Facebook
CREATE TABLE IF NOT EXISTS fb_posts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    fb_post_id VARCHAR(100) NOT NULL UNIQUE,
    page_id VARCHAR(100) NOT NULL DEFAULT '',
    texto LONGTEXT,
    fecha DATETIME,
    url VARCHAR(500),
    tipo VARCHAR(50) DEFAULT 'status',
    likes_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    shares_count INT DEFAULT 0,
    metadata_json JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fecha (fecha DESC),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Reacciones de Facebook (likes con usuario)
CREATE TABLE IF NOT EXISTS fb_reactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    fb_post_id VARCHAR(100) NOT NULL,
    fb_user_id VARCHAR(100) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    tipo_reaccion VARCHAR(50) DEFAULT 'like',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    colaborador_id INT DEFAULT NULL,
    UNIQUE KEY uk_post_user (fb_post_id, fb_user_id),
    INDEX idx_user (fb_user_id),
    INDEX idx_colaborador (colaborador_id),
    INDEX idx_fecha (fecha DESC),
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Comentarios de Facebook
CREATE TABLE IF NOT EXISTS fb_comments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    fb_post_id VARCHAR(100) NOT NULL,
    fb_comment_id VARCHAR(100) NOT NULL UNIQUE,
    fb_user_id VARCHAR(100) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    texto TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    colaborador_id INT DEFAULT NULL,
    INDEX idx_user (fb_user_id),
    INDEX idx_post (fb_post_id),
    INDEX idx_colaborador (colaborador_id),
    INDEX idx_fecha (fecha DESC),
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Perfiles de usuarios de Facebook (no identificados)
CREATE TABLE IF NOT EXISTS fb_commenters (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    fb_user_id VARCHAR(100) NOT NULL UNIQUE,
    user_name VARCHAR(255) NOT NULL,
    total_reactions INT DEFAULT 0,
    total_comments INT DEFAULT 0,
    first_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    colaborador_id INT DEFAULT NULL,
    estado ENUM('nuevo','pendiente_revision','convertido','ignorado') DEFAULT 'nuevo',
    INDEX idx_estado (estado),
    INDEX idx_colaborador (colaborador_id),
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Leads de redes sociales (unificado)
CREATE TABLE IF NOT EXISTS social_leads (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    red ENUM('facebook','instagram') NOT NULL,
    user_id VARCHAR(100) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    telefono VARCHAR(50) DEFAULT NULL,
    total_interacciones INT DEFAULT 0,
    primera_interaccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_interaccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    colaborador_id INT DEFAULT NULL,
    estado ENUM('nuevo','pendiente_revision','convertido','ignorado') DEFAULT 'nuevo',
    metadata_json JSON DEFAULT NULL,
    UNIQUE KEY uk_red_user (red, user_id),
    INDEX idx_colaborador (colaborador_id),
    INDEX idx_estado (estado),
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Instagram menciones extraídas de captions
CREATE TABLE IF NOT EXISTS ig_menciones (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    post_url VARCHAR(500) NOT NULL,
    fecha DATE,
    username VARCHAR(100) NOT NULL,
    texto_contexto TEXT,
    categoria VARCHAR(50) DEFAULT 'general',
    colaborador_id INT DEFAULT NULL,
    estado ENUM('nuevo','pendiente_revision','convertido','ignorado') DEFAULT 'nuevo',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_colaborador (colaborador_id),
    INDEX idx_estado (estado),
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ALTER colaboradores: campos de redes sociales
ALTER TABLE colaboradores
    ADD COLUMN IF NOT EXISTS facebook_id VARCHAR(100) DEFAULT NULL AFTER mesa_votacion,
    ADD COLUMN IF NOT EXISTS instagram_username VARCHAR(100) DEFAULT NULL AFTER facebook_id,
    ADD COLUMN IF NOT EXISTS instagram_id VARCHAR(100) DEFAULT NULL AFTER instagram_username,
    ADD INDEX IF NOT EXISTS idx_facebook_id (facebook_id),
    ADD INDEX IF NOT EXISTS idx_instagram (instagram_username);

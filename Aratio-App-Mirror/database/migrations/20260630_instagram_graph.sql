-- Instagram Graph API: comments y media real
-- v2.15.1 — 2026-06-30

CREATE TABLE IF NOT EXISTS ig_media (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    ig_media_id VARCHAR(100) NOT NULL UNIQUE,
    caption TEXT,
    media_type VARCHAR(20) DEFAULT 'IMAGE',
    media_url VARCHAR(1000) DEFAULT '',
    permalink VARCHAR(500) DEFAULT '',
    timestamp DATETIME,
    like_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp DESC),
    INDEX idx_media_type (media_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ig_comments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    ig_comment_id VARCHAR(100) NOT NULL UNIQUE,
    ig_media_id VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL,
    texto TEXT,
    timestamp DATETIME,
    colaborador_id INT DEFAULT NULL,
    INDEX idx_media (ig_media_id),
    INDEX idx_username (username),
    INDEX idx_colaborador (colaborador_id),
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

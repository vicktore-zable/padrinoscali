-- Migration: Create password_reset_tokens table
-- Description: Stores password reset tokens with expiration
-- Date: 2025-10-21

USE aratio;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at),
    INDEX idx_usuario_id (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tokens para recuperación de contraseña';

-- Clean up expired tokens automatically (optional)
-- Can be run as a scheduled job
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_limpiar_tokens_expirados()
BEGIN
    DELETE FROM password_reset_tokens
    WHERE expires_at < NOW() OR used = TRUE;
END//

DELIMITER ;

-- Add methods to Usuario model to handle these tokens
-- The following SQL is documentation for the methods:
/*

-- In Usuario::createPasswordResetToken($userId, $token)
INSERT INTO password_reset_tokens (usuario_id, token, expires_at)
VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR));

-- In Usuario::verifyPasswordResetToken($token)
SELECT usuario_id FROM password_reset_tokens
WHERE token = ?
  AND expires_at > NOW()
  AND used = FALSE
LIMIT 1;

-- In Usuario::invalidatePasswordResetToken($token)
UPDATE password_reset_tokens
SET used = TRUE
WHERE token = ?;

*/

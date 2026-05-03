-- =============================================
-- ACTUALIZACIÓN: asistencia_eventos
-- Sistema de registro completo con firma y análisis
-- =============================================

-- Eliminar tabla existente (solo en desarrollo)
DROP TABLE IF EXISTS `asistencia_eventos`;

-- Crear tabla mejorada
CREATE TABLE `asistencia_eventos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `evento_id` INT(11) NOT NULL,

  -- Información básica
  `nombre` VARCHAR(255) NOT NULL,
  `documento` VARCHAR(50) NOT NULL,
  `tipo_documento` ENUM('cc','ce','ti','pasaporte','otro') NOT NULL DEFAULT 'cc',
  `telefono` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,

  -- Datos demográficos
  `fecha_nacimiento` DATE DEFAULT NULL,
  `edad` INT(3) DEFAULT NULL,
  `genero` ENUM('masculino','femenino','otro','prefiero-no-decir') NOT NULL,

  -- Ubicación (donde vive)
  `departamento` VARCHAR(100) NOT NULL,
  `municipio` VARCHAR(100) NOT NULL,
  `barrio` VARCHAR(100) DEFAULT NULL,
  `direccion` TEXT DEFAULT NULL,

  -- Firma digital (base64)
  `firma` LONGTEXT DEFAULT NULL,

  -- Observaciones para análisis de sentimientos
  `observaciones` TEXT DEFAULT NULL,
  `sentimiento_analizado` ENUM('positivo','neutral','negativo','pendiente') DEFAULT 'pendiente',
  `sentimiento_score` DECIMAL(3,2) DEFAULT NULL COMMENT 'Score de -1 (negativo) a +1 (positivo)',

  -- Metadata del registro
  `fecha_registro` DATETIME NOT NULL,
  `metodo_registro` ENUM('qr','manual','web') NOT NULL DEFAULT 'qr',
  `ip_registro` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `asistio` BOOLEAN NOT NULL DEFAULT TRUE,

  -- Timestamps
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_evento` (`evento_id`),
  KEY `idx_documento` (`documento`),
  KEY `idx_fecha_registro` (`fecha_registro`),
  KEY `idx_genero` (`genero`),
  KEY `idx_municipio` (`municipio`),
  KEY `idx_sentimiento` (`sentimiento_analizado`),

  CONSTRAINT `fk_asistencia_evento` FOREIGN KEY (`evento_id`)
    REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro completo de asistencia a eventos con firma y análisis de sentimientos';

-- Índice compuesto para evitar registros duplicados
CREATE UNIQUE INDEX `idx_evento_documento` ON `asistencia_eventos` (`evento_id`, `documento`);

-- =============================================
-- DATOS DE PRUEBA (Opcional)
-- =============================================
-- INSERT INTO `asistencia_eventos`
-- (`evento_id`, `nombre`, `documento`, `tipo_documento`, `telefono`, `email`,
--  `fecha_nacimiento`, `edad`, `genero`, `departamento`, `municipio`, `barrio`,
--  `observaciones`, `fecha_registro`, `metodo_registro`)
-- VALUES
-- (1, 'María González', '1234567890', 'cc', '3001234567', 'maria@example.com',
--  '1990-05-15', 34, 'femenino', 'Cundinamarca', 'Bogotá', 'Usaquén',
--  'Excelente evento, muy informativo', NOW(), 'qr');

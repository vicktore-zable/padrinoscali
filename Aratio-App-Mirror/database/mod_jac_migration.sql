-- =======================================================
-- MIGRACIÓN: Módulo mod_jac (Juntas de Acción Comunal)
-- Sistema Aratio v1.5.0 - 2026-03-17
-- =======================================================

-- Tabla principal de registros JAC
CREATE TABLE IF NOT EXISTS `jac_registros` (
  `id`                  INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `id_campana`          INT UNSIGNED     NOT NULL,
  `nombre_jac`          VARCHAR(200)     NOT NULL                COMMENT 'Nombre de la JAC',
  `presidente`          VARCHAR(200)     DEFAULT NULL            COMMENT 'Nombre del presidente de la JAC',
  `telefono`            VARCHAR(20)      DEFAULT NULL,
  `email`               VARCHAR(150)     DEFAULT NULL,
  `direccion`           VARCHAR(250)     DEFAULT NULL,

  -- Territorio (basado en territorio_valle)
  `territorio_valle`    VARCHAR(150)     NOT NULL                COMMENT 'Territorio del Valle asignado',
  `municipio`           VARCHAR(100)     NOT NULL,
  `sector`              VARCHAR(100)     DEFAULT NULL            COMMENT 'Barrio / vereda / sector',
  `comuna`              VARCHAR(60)      DEFAULT NULL,

  -- Gestión electoral
  `votos_comprometidos` INT              DEFAULT 0,
  `afiliados_count`     INT              DEFAULT 0              COMMENT 'Total de afiliados de la JAC',
  `estado`              ENUM('activa','inactiva','en_proceso')  NOT NULL DEFAULT 'activa',

  -- Metadata
  `observaciones`       TEXT             DEFAULT NULL,
  `usuario_id`          INT UNSIGNED     DEFAULT NULL           COMMENT 'Usuario que registró',
  `created_at`          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_jac_campana`          (`id_campana`),
  KEY `idx_jac_municipio`        (`municipio`),
  KEY `idx_jac_territorio_valle` (`territorio_valle`),
  KEY `idx_jac_estado`           (`estado`),
  KEY `idx_jac_usuario`          (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Juntas de Acción Comunal - Módulo mod_jac';

-- Insertar datos de prueba (opcional, comentar en producción)
-- INSERT INTO `jac_registros` (`id_campana`, `nombre_jac`, `presidente`, `telefono`, `territorio_valle`, `municipio`, `sector`, `votos_comprometidos`, `estado`)
-- VALUES
--   (1, 'JAC Barrio El Prado',      'Carlos Rodríguez',  '3001234567', 'Yumbo Norte',  'Yumbo',   'El Prado',    45, 'activa'),
--   (1, 'JAC Comunidad La Gloria',  'María López',       '3109876543', 'Cali Norte',   'Cali',    'La Gloria',   80, 'activa'),
--   (1, 'JAC Ciudadela Comfandi',   'Pedro Martínez',    '3157654321', 'Cali Oriente', 'Cali',    'Comfandi',    62, 'en_proceso'),
--   (1, 'JAC Vereda El Porvenir',   'Ana García',        '3168765432', 'Palmira Sur',  'Palmira', 'El Porvenir', 30, 'activa');

-- ============================================
-- MIGRACIÓN: Selectores Geográficos 5 Niveles
-- Fecha: 26 de Noviembre de 2025
-- Versión: 1.1.0
-- ============================================

-- IMPORTANTE: Ejecutar este script en la base de datos de PRODUCCIÓN
-- Base de datos: u156469157_aratio_v1
-- Host: auth-db690.hstgr.io

-- ============================================
-- PASO 1: Crear tabla territorios
-- ============================================

CREATE TABLE IF NOT EXISTS `territorios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `departamento` varchar(255) DEFAULT NULL,
  `municipio` varchar(255) DEFAULT NULL,
  `Tipo_territorio` varchar(255) DEFAULT NULL,
  `cod_mpio` varchar(255) DEFAULT NULL,
  `Código` varchar(255) DEFAULT NULL,
  `Territorio` varchar(255) DEFAULT NULL,
  `barrio` varchar(255) DEFAULT NULL,
  `Geom` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PASO 2: Agregar columnas a tabla eventos
-- ============================================

-- Verificar si la columna tipo_territorio existe
SET @dbname = DATABASE();
SET @tablename = 'eventos';
SET @columnname = 'tipo_territorio';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER municipio')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar si la columna territorio existe
SET @columnname = 'territorio';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER tipo_territorio')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar si la columna barrio existe
SET @columnname = 'barrio';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER territorio')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- PASO 3: Verificación
-- ============================================

-- Mostrar estructura de tabla eventos
DESCRIBE eventos;

-- Mostrar estructura de tabla territorios
DESCRIBE territorios;

-- Contar registros en territorios (debería ser 0 antes de importar datos)
SELECT COUNT(*) as total_territorios FROM territorios;

-- ============================================
-- NOTAS IMPORTANTES
-- ============================================

-- 1. Después de ejecutar este script, importar el archivo:
--    territorios_data.sql (643 registros)
--
-- 2. Verificar que las columnas se agregaron correctamente:
--    DESCRIBE eventos;
--
-- 3. Las tablas acciones_comunitarias y compromisos
--    ya tienen los campos necesarios (no requieren cambios)
--
-- 4. Verificar total de registros:
--    SELECT COUNT(*) FROM territorios;  -- Debe ser 643
--
-- 5. Probar API:
--    https://aratio.mrmtech.net/api/territorios.php?accion=departamentos
--
-- ============================================
-- FIN DE MIGRACIÓN
-- ============================================

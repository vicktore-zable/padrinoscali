-- ============================================
-- MIGRACIÓN: Agregar Columnas de Territorio
-- Fecha: 2026-01-29
-- ============================================

-- 1. Asegurar columnas en tabla colaboradores
SET @dbname = DATABASE();
SET @tablename = 'colaboradores';

-- Columna tipo_territorio
SET @columnname = 'tipo_territorio';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER municipio')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Columna territorio
SET @columnname = 'territorio';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER tipo_territorio')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Columna barrio
SET @columnname = 'barrio';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER territorio')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Actualizar Vista v_colaboradores_completo
CREATE OR REPLACE VIEW v_colaboradores_completo AS
SELECT
    c.*,
    l.nombres as nombre_lider,
    (SELECT COUNT(*) FROM colaboradores s WHERE s.lider_directo = c.documento) as total_seguidores
FROM colaboradores c
LEFT JOIN colaboradores l ON c.lider_directo = l.documento;

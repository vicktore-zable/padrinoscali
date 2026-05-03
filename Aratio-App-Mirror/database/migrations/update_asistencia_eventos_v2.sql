-- =============================================
-- MIGRACIÓN V2: Actualizar tabla asistencia_eventos
-- Fecha: 2025-11-29
-- Descripción: Agregar solo los campos faltantes
-- =============================================

-- Verificar si las columnas existen antes de agregarlas
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'u156469157_aratio_v1'
    AND TABLE_NAME = 'asistencia_eventos'
    AND COLUMN_NAME = 'tipo_territorio');

SET @query = IF(@exist = 0,
    'ALTER TABLE asistencia_eventos ADD COLUMN tipo_territorio VARCHAR(100) DEFAULT NULL AFTER municipio',
    'SELECT "Column tipo_territorio already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- territorio
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'u156469157_aratio_v1'
    AND TABLE_NAME = 'asistencia_eventos'
    AND COLUMN_NAME = 'territorio');

SET @query = IF(@exist = 0,
    'ALTER TABLE asistencia_eventos ADD COLUMN territorio VARCHAR(100) DEFAULT NULL AFTER tipo_territorio',
    'SELECT "Column territorio already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- grupo_etareo
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'u156469157_aratio_v1'
    AND TABLE_NAME = 'asistencia_eventos'
    AND COLUMN_NAME = 'grupo_etareo');

SET @query = IF(@exist = 0,
    'ALTER TABLE asistencia_eventos ADD COLUMN grupo_etareo VARCHAR(20) DEFAULT NULL AFTER genero',
    'SELECT "Column grupo_etareo already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- areas_interes
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'u156469157_aratio_v1'
    AND TABLE_NAME = 'asistencia_eventos'
    AND COLUMN_NAME = 'areas_interes');

SET @query = IF(@exist = 0,
    'ALTER TABLE asistencia_eventos ADD COLUMN areas_interes JSON DEFAULT NULL AFTER barrio',
    'SELECT "Column areas_interes already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Renombrar firma a firma_digital (si existe)
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'u156469157_aratio_v1'
    AND TABLE_NAME = 'asistencia_eventos'
    AND COLUMN_NAME = 'firma');

SET @query = IF(@exist > 0,
    'ALTER TABLE asistencia_eventos CHANGE COLUMN firma firma_digital LONGTEXT DEFAULT NULL',
    'SELECT "Column firma does not exist" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Renombrar observaciones a notas (si existe)
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'u156469157_aratio_v1'
    AND TABLE_NAME = 'asistencia_eventos'
    AND COLUMN_NAME = 'observaciones');

SET @query = IF(@exist > 0,
    'ALTER TABLE asistencia_eventos CHANGE COLUMN observaciones notas TEXT DEFAULT NULL',
    'SELECT "Column observaciones does not exist" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- habeas_data
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'u156469157_aratio_v1'
    AND TABLE_NAME = 'asistencia_eventos'
    AND COLUMN_NAME = 'habeas_data');

SET @query = IF(@exist = 0,
    'ALTER TABLE asistencia_eventos ADD COLUMN habeas_data BOOLEAN NOT NULL DEFAULT FALSE AFTER firma_digital',
    'SELECT "Column habeas_data already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- acepta_comunicaciones
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'u156469157_aratio_v1'
    AND TABLE_NAME = 'asistencia_eventos'
    AND COLUMN_NAME = 'acepta_comunicaciones');

SET @query = IF(@exist = 0,
    'ALTER TABLE asistencia_eventos ADD COLUMN acepta_comunicaciones BOOLEAN NOT NULL DEFAULT FALSE AFTER habeas_data',
    'SELECT "Column acepta_comunicaciones already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar índices si no existen
-- grupo_etareo
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = 'u156469157_aratio_v1'
    AND TABLE_NAME = 'asistencia_eventos'
    AND INDEX_NAME = 'idx_grupo_etareo');

SET @query = IF(@exist = 0,
    'ALTER TABLE asistencia_eventos ADD KEY idx_grupo_etareo (grupo_etareo)',
    'SELECT "Index idx_grupo_etareo already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- tipo_territorio
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = 'u156469157_aratio_v1'
    AND TABLE_NAME = 'asistencia_eventos'
    AND INDEX_NAME = 'idx_tipo_territorio');

SET @query = IF(@exist = 0,
    'ALTER TABLE asistencia_eventos ADD KEY idx_tipo_territorio (tipo_territorio)',
    'SELECT "Index idx_tipo_territorio already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear o reemplazar trigger para calcular grupo etario
DROP TRIGGER IF EXISTS calcular_grupo_etareo_asistencia;
DROP TRIGGER IF EXISTS calcular_grupo_etareo_asistencia_update;

DELIMITER //
CREATE TRIGGER calcular_grupo_etareo_asistencia
BEFORE INSERT ON asistencia_eventos
FOR EACH ROW
BEGIN
  IF NEW.fecha_nacimiento IS NOT NULL THEN
    SET @edad = TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE());
    SET NEW.grupo_etareo = CASE
      WHEN @edad < 18 THEN 'Menor de 18'
      WHEN @edad BETWEEN 18 AND 25 THEN '18-25'
      WHEN @edad BETWEEN 26 AND 35 THEN '26-35'
      WHEN @edad BETWEEN 36 AND 45 THEN '36-45'
      WHEN @edad BETWEEN 46 AND 55 THEN '46-55'
      WHEN @edad BETWEEN 56 AND 65 THEN '56-65'
      WHEN @edad > 65 THEN 'Mayor de 65'
      ELSE 'No especificado'
    END;
  END IF;
END//

CREATE TRIGGER calcular_grupo_etareo_asistencia_update
BEFORE UPDATE ON asistencia_eventos
FOR EACH ROW
BEGIN
  IF NEW.fecha_nacimiento IS NOT NULL THEN
    SET @edad = TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE());
    SET NEW.grupo_etareo = CASE
      WHEN @edad < 18 THEN 'Menor de 18'
      WHEN @edad BETWEEN 18 AND 25 THEN '18-25'
      WHEN @edad BETWEEN 26 AND 35 THEN '26-35'
      WHEN @edad BETWEEN 36 AND 45 THEN '36-45'
      WHEN @edad BETWEEN 46 AND 55 THEN '46-55'
      WHEN @edad BETWEEN 56 AND 65 THEN '56-65'
      WHEN @edad > 65 THEN 'Mayor de 65'
      ELSE 'No especificado'
    END;
  END IF;
END//
DELIMITER ;

SELECT 'Migración completada exitosamente' AS status;

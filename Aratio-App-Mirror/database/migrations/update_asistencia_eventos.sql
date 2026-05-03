-- =============================================
-- MIGRACIÓN: Actualizar tabla asistencia_eventos
-- Fecha: 2025-11-29
-- Descripción: Agregar campos de jerarquía territorial, género, grupo etario, firma, habeas data y áreas de interés
-- =============================================

-- Agregar campos faltantes a la tabla asistencia_eventos
ALTER TABLE `asistencia_eventos`
  -- Tipo de documento
  ADD COLUMN `tipo_documento` ENUM('CC','CE','PA','TI','RC','NIT','Otro') NOT NULL DEFAULT 'CC' AFTER `documento`,

  -- Jerarquía territorial (5 niveles)
  ADD COLUMN `departamento` VARCHAR(100) DEFAULT NULL AFTER `email`,
  ADD COLUMN `municipio` VARCHAR(100) DEFAULT NULL AFTER `departamento`,
  ADD COLUMN `tipo_territorio` VARCHAR(100) DEFAULT NULL AFTER `municipio`,
  ADD COLUMN `territorio` VARCHAR(100) DEFAULT NULL AFTER `tipo_territorio`,
  ADD COLUMN `barrio` VARCHAR(100) DEFAULT NULL AFTER `territorio`,

  -- Datos demográficos
  ADD COLUMN `genero` ENUM('masculino','femenino','otro','prefiero-no-decir') DEFAULT NULL AFTER `barrio`,
  ADD COLUMN `fecha_nacimiento` DATE DEFAULT NULL AFTER `genero`,
  ADD COLUMN `grupo_etareo` VARCHAR(20) DEFAULT NULL AFTER `fecha_nacimiento`,

  -- Áreas de interés (JSON para múltiples selecciones)
  ADD COLUMN `areas_interes` JSON DEFAULT NULL AFTER `grupo_etareo`,

  -- Firma digital y consentimientos
  ADD COLUMN `firma_digital` TEXT DEFAULT NULL AFTER `areas_interes`,
  ADD COLUMN `habeas_data` BOOLEAN NOT NULL DEFAULT FALSE AFTER `firma_digital`,
  ADD COLUMN `acepta_comunicaciones` BOOLEAN NOT NULL DEFAULT FALSE AFTER `habeas_data`,

  -- Índices para mejorar búsquedas
  ADD KEY `idx_departamento` (`departamento`),
  ADD KEY `idx_municipio` (`municipio`),
  ADD KEY `idx_genero` (`genero`),
  ADD KEY `idx_grupo_etareo` (`grupo_etareo`);

-- Crear trigger para calcular automáticamente el grupo etario
DELIMITER //
CREATE TRIGGER `calcular_grupo_etareo_asistencia`
BEFORE INSERT ON `asistencia_eventos`
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

-- Crear trigger para actualización
DELIMITER //
CREATE TRIGGER `calcular_grupo_etareo_asistencia_update`
BEFORE UPDATE ON `asistencia_eventos`
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

-- Comentario de aplicación
-- Aplicar con: mysql -u root u156469157_aratio_v1 < update_asistencia_eventos.sql
-- O desde phpMyAdmin en producción

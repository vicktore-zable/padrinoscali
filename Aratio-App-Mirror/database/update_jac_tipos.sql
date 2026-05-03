-- =======================================================
-- MIGRACIÓN: Ampliación de Módulo JAC a Organizaciones
-- Añade columna para clasificar los registros según su tipo
-- =======================================================

-- Añadir tipo_organizacion si no existe
ALTER TABLE `jac_registros` 
ADD COLUMN `tipo_organizacion` VARCHAR(50) NOT NULL DEFAULT 'JAC' AFTER `nombre_jac`
COMMENT 'Tipo de actor: JAC, Deporte, Cultura, Gestor Social, etc.';

-- =======================================================
-- OPICIONAL: Actualizar índices si se desea filtrar rápido
-- =======================================================
ALTER TABLE `jac_registros` ADD INDEX `idx_jac_tipo_org` (`tipo_organizacion`);

-- Migración: Agregar soporte para campañas
-- Fecha: 2025-01-23

-- Agregar columna campana_id a la tabla colaboradores
ALTER TABLE colaboradores ADD COLUMN campana_id INT NULL AFTER id;

-- Agregar índice para mejorar el rendimiento de las búsquedas por campaña
CREATE INDEX idx_campana_id ON colaboradores(campana_id);

-- Opcional: Si se desea usar un nombre de campaña directamente
ALTER TABLE colaboradores ADD COLUMN campana_nombre VARCHAR(100) NULL AFTER campana_id;

-- Comentario: Esta columna permitirá filtrar colaboradores por la campaña proveniente de aratio.mrmtech.net

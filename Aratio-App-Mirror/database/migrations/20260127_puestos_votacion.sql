-- ============================================
-- MIGRACION: Agregar campos de votacion
-- Fecha: 2026-01-27
-- ============================================

-- Agregar campos para informacion electoral
ALTER TABLE colaboradores ADD COLUMN IF NOT EXISTS puesto_votacion VARCHAR(255) DEFAULT NULL COMMENT 'Nombre del puesto de votacion';
ALTER TABLE colaboradores ADD COLUMN IF NOT EXISTS mesa_votacion VARCHAR(10) DEFAULT NULL COMMENT 'Numero de mesa';

-- Crear indice para busquedas por puesto
ALTER TABLE colaboradores ADD INDEX IF NOT EXISTS idx_puesto_mesa (campana_id, puesto_votacion, mesa_votacion);

-- Actualizar vista de estadisticas (opcional, si se quiere contar puestos)
-- Por ahora no es necesario tocar las vistas complejas

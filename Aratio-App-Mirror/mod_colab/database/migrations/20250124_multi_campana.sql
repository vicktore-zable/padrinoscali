-- Migración: Soporte para múltiples campañas y administradores por campaña
-- Fecha: 2025-01-24

-- 1. Crear tabla de campañas
CREATE TABLE IF NOT EXISTS campanas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_externo VARCHAR(50) UNIQUE COMMENT 'ID de aratio.mrmtech.net, ej: CAMP-2027-003',
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    candidato_nombre VARCHAR(150),
    lider_nombre VARCHAR(150),
    territorio VARCHAR(150),
    fecha_inicio DATE,
    fecha_fin DATE,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo_externo (codigo_externo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Agregar campana_id a la tabla usuarios para restringir acceso
ALTER TABLE usuarios ADD COLUMN campana_id INT NULL AFTER tipo_usuario;
ALTER TABLE usuarios ADD CONSTRAINT fk_usuario_campana FOREIGN KEY (campana_id) REFERENCES campanas(id) ON DELETE SET NULL;

-- 3. Asegurar que campana_id en colaboradores sea compatible (ya existe como INT según add_campaign_support.sql)
-- Si no existe, lo creamos (por seguridad)
-- ALTER TABLE colaboradores ADD COLUMN IF NOT EXISTS campana_id INT NULL AFTER id;
-- ALTER TABLE colaboradores ADD COLUMN IF NOT EXISTS campana_nombre VARCHAR(100) NULL AFTER campana_id;

-- 4. Insertar la campaña inicial de Orley Lozano mencionada por el usuario
INSERT INTO campanas (codigo_externo, nombre, candidato_nombre, territorio, fecha_inicio, fecha_fin)
VALUES (
    'CAMP-2027-003', 
    'CONCEJO DE YUMBO 2028-2031 "VAMOS Q VAMOS"', 
    'Orley Lozano', 
    'YUMBO, VALLE', 
    '2025-11-27', 
    '2027-10-24'
) ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- 5. Actualizar los colaboradores existentes que pertenecen a esta campaña
-- Basado en el requerimiento del usuario
UPDATE colaboradores 
SET campana_id = (SELECT id FROM campanas WHERE codigo_externo = 'CAMP-2027-003'),
    campana_nombre = 'CONCEJO DE YUMBO 2028-2031 "VAMOS Q VAMOS"'
WHERE (campana_id IS NULL OR campana_id = 0) 
  AND (municipio = 'YUMBO' OR lider_directo = 'CAMP-2027-003' OR documento = 'CAMP-2027-003');

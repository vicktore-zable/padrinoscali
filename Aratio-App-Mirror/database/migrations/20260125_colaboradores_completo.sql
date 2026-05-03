-- ============================================
-- MIGRACION: Integracion Completa Colaboradores
-- Fecha: 2026-01-25
-- Version: 1.0.0
-- ============================================

-- --------------------------------------------
-- Tabla: curriculum (Hoja de vida profesional)
-- Almacena experiencia laboral, formacion y participacion politica
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS curriculum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    experiencia_laboral JSON COMMENT 'Array de objetos: [{cargo, empresa, fecha_inicio, fecha_fin, descripcion}]',
    formacion_academica JSON COMMENT 'Array de objetos: [{titulo, institucion, fecha_inicio, fecha_fin, nivel}]',
    participacion_politica JSON COMMENT 'Array de objetos: [{cargo, organizacion, fecha_inicio, fecha_fin, descripcion}]',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
    UNIQUE KEY uk_colaborador (colaborador_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabla: historial_cambios_lider (Auditoria de cambios de lider)
-- Registra cada vez que se cambia el lider directo de un colaborador
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS historial_cambios_lider (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    colaborador_documento VARCHAR(20) NOT NULL,
    lider_anterior VARCHAR(20) COMMENT 'Documento del lider anterior',
    lider_nuevo VARCHAR(20) COMMENT 'Documento del nuevo lider',
    motivo TEXT COMMENT 'Razon del cambio',
    usuario_cambio INT NOT NULL COMMENT 'Usuario que realizo el cambio',
    campana_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_cambio) REFERENCES usuarios(id),
    FOREIGN KEY (campana_id) REFERENCES campanas(id),
    INDEX idx_colaborador (colaborador_id),
    INDEX idx_campana (campana_id),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabla: historial_estados (Trazabilidad de estados y evaluaciones)
-- Registra cambios en dato_potencial, dato_historico y estado
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS historial_estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    campana_id INT NOT NULL,
    dato_potencial_anterior INT,
    dato_potencial_nuevo INT,
    dato_historico_anterior INT,
    dato_historico_nuevo INT,
    estado_anterior VARCHAR(20),
    estado_nuevo VARCHAR(20),
    motivo TEXT COMMENT 'Razon del cambio o reevaluacion',
    tipo_cambio ENUM('creacion', 'actualizacion', 'reevaluacion') DEFAULT 'actualizacion',
    usuario_id INT NOT NULL COMMENT 'Usuario que realizo el cambio',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
    FOREIGN KEY (campana_id) REFERENCES campanas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_colaborador (colaborador_id),
    INDEX idx_campana (campana_id),
    INDEX idx_fecha (created_at),
    INDEX idx_tipo (tipo_cambio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Indices adicionales para optimizacion de consultas jerarquicas
-- --------------------------------------------
ALTER TABLE colaboradores ADD INDEX IF NOT EXISTS idx_jerarquia (campana_id, lider_directo, documento);
ALTER TABLE colaboradores ADD INDEX IF NOT EXISTS idx_territorio_campana (campana_id, departamento, municipio);
ALTER TABLE colaboradores ADD INDEX IF NOT EXISTS idx_perfil_estado (campana_id, perfil, estado);

-- --------------------------------------------
-- Vista: v_colaboradores_red
-- Colaboradores con metricas de red (seguidores, datos del lider)
-- --------------------------------------------
CREATE OR REPLACE VIEW v_colaboradores_red AS
SELECT
    c.id,
    c.documento,
    c.nombres,
    c.apellidos,
    c.perfil,
    c.nivel_participacion,
    c.estado,
    c.dato_potencial,
    c.dato_historico,
    c.lider_directo,
    c.campana_id,
    c.departamento,
    c.municipio,
    c.email,
    c.telefono,
    c.created_at,
    (SELECT COUNT(*) FROM colaboradores s WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id) as total_seguidores,
    l.nombres as lider_nombres,
    l.apellidos as lider_apellidos,
    l.perfil as lider_perfil,
    l.id as lider_id
FROM colaboradores c
LEFT JOIN colaboradores l ON c.lider_directo = l.documento AND c.campana_id = l.campana_id;

-- --------------------------------------------
-- Vista: v_estadisticas_campana
-- Estadisticas agregadas por campana
-- --------------------------------------------
CREATE OR REPLACE VIEW v_estadisticas_campana AS
SELECT
    campana_id,
    COUNT(*) as total_colaboradores,
    COUNT(DISTINCT lider_directo) as total_lideres_unicos,
    SUM(CASE WHEN lider_directo IS NULL OR lider_directo = '' THEN 1 ELSE 0 END) as sin_lider,
    SUM(CASE WHEN perfil LIKE '%Lider%' THEN 1 ELSE 0 END) as total_lideres,
    COUNT(DISTINCT municipio) as municipios,
    COUNT(DISTINCT departamento) as departamentos,
    SUM(CASE WHEN estado = 'Nuevo' THEN 1 ELSE 0 END) as nuevos,
    SUM(CASE WHEN estado = 'Crecio' THEN 1 ELSE 0 END) as crecieron,
    SUM(CASE WHEN estado = 'Igual' THEN 1 ELSE 0 END) as igual,
    SUM(CASE WHEN estado = 'Decrecio' THEN 1 ELSE 0 END) as decrecieron,
    AVG(dato_potencial) as promedio_potencial,
    AVG(dato_historico) as promedio_historico
FROM colaboradores
GROUP BY campana_id;

-- --------------------------------------------
-- Verificacion de migracion
-- --------------------------------------------
SELECT 'Migracion completada exitosamente' as status,
       (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'curriculum') as curriculum_existe,
       (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'historial_cambios_lider') as historial_lider_existe,
       (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'historial_estados') as historial_estados_existe;

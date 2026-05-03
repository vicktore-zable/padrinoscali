-- ============================================
-- MIGRACIÓN: Actualización de Vistas (Territorio)
-- Fecha: 2026-01-29
-- ============================================

-- 1. Actualizar Vista v_colaboradores_red
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
    c.tipo_territorio,
    c.territorio,
    c.barrio,
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

-- 2. Actualizar Vista v_estadisticas_campana
CREATE OR REPLACE VIEW v_estadisticas_campana AS
SELECT
    campana_id,
    COUNT(*) as total_colaboradores,
    COUNT(DISTINCT lider_directo) as total_lideres_unicos,
    SUM(CASE WHEN lider_directo IS NULL OR lider_directo = '' THEN 1 ELSE 0 END) as sin_lider,
    SUM(CASE WHEN perfil LIKE '%Lider%' THEN 1 ELSE 0 END) as total_lideres,
    COUNT(DISTINCT municipio) as municipios,
    COUNT(DISTINCT departamento) as departamentos,
    COUNT(DISTINCT territorio) as territorios,
    COUNT(DISTINCT barrio) as barrios,
    SUM(CASE WHEN estado = 'Nuevo' THEN 1 ELSE 0 END) as nuevos,
    SUM(CASE WHEN estado = 'Crecio' THEN 1 ELSE 0 END) as crecieron,
    SUM(CASE WHEN estado = 'Igual' THEN 1 ELSE 0 END) as igual,
    SUM(CASE WHEN estado = 'Decrecio' THEN 1 ELSE 0 END) as decrecieron,
    AVG(dato_potencial) as promedio_potencial,
    AVG(dato_historico) as promedio_historico
FROM colaboradores
GROUP BY campana_id;

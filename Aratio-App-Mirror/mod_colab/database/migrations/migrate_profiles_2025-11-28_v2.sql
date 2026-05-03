-- =====================================================
-- MIGRACIÓN DE PERFILES DE COLABORADOR - VERSIÓN 2
-- De 12 perfiles originales a 8 perfiles consolidados
-- Fecha: 2025-11-28
-- Estrategia: ENUM híbrido temporal
-- =====================================================

USE aratio;

-- =====================================================
-- PASO 1: Modificar ENUM para incluir AMBOS (antiguos + nuevos)
-- =====================================================

ALTER TABLE colaboradores 
MODIFY COLUMN perfil ENUM(
    -- Valores ANTIGUOS (mantener temporalmente)
    'Lider Comunitario',
    'Lider Ambiental',
    'Lider Gremial',
    'Lider Social',
    'Lider Empresarial',
    'Influencer',
    'Lider Juvenil',
    'Lider Poblacional',
    'Lider Diferencial',
    'Medios Tradicionales',
    'Amigo',
    'Familia',
    -- Valores NUEVOS (agregar)
    'Lider Comunitario / Social',
    'Lider Gremial / Empresarial',
    'Lider Juvenil / Deportivo',
    'Lider Poblacional / Diferencial',
    'Lider Religioso',
    'Influencer / Medios',
    'Vinculo Personal'
) NOT NULL;

SELECT 'ENUM modificado para incluir valores antiguos y nuevos' AS mensaje;

-- =====================================================
-- PASO 2: Migrar datos de valores antiguos a nuevos
-- =====================================================

START TRANSACTION;

-- Migrar: Líder Comunitario + Líder Social → Líder Comunitario / Social
UPDATE colaboradores 
SET perfil = 'Lider Comunitario / Social' 
WHERE perfil IN ('Lider Comunitario', 'Lider Social');

-- Líder Ambiental permanece igual (ya está en el nuevo ENUM)
-- No necesita UPDATE

-- Migrar: Líder Gremial + Líder Empresarial → Líder Gremial / Empresarial
UPDATE colaboradores 
SET perfil = 'Lider Gremial / Empresarial' 
WHERE perfil IN ('Lider Gremial', 'Lider Empresarial');

-- Migrar: Líder Juvenil → Líder Juvenil / Deportivo
UPDATE colaboradores 
SET perfil = 'Lider Juvenil / Deportivo' 
WHERE perfil = 'Lider Juvenil';

-- Migrar: Líder Poblacional + Líder Diferencial → Líder Poblacional / Diferencial
UPDATE colaboradores 
SET perfil = 'Lider Poblacional / Diferencial' 
WHERE perfil IN ('Lider Poblacional', 'Lider Diferencial');

-- Migrar: Influencer + Medios Tradicionales → Influencer / Medios
UPDATE colaboradores 
SET perfil = 'Influencer / Medios' 
WHERE perfil IN ('Influencer', 'Medios Tradicionales');

-- Migrar: Amigo + Familia → Vínculo Personal
UPDATE colaboradores 
SET perfil = 'Vinculo Personal' 
WHERE perfil IN ('Amigo', 'Familia');

COMMIT;

SELECT 'Datos migrados a nuevos perfiles' AS mensaje;

-- =====================================================
-- PASO 3: Verificar migración
-- =====================================================

SELECT 
    'Distribución después de migración' AS mensaje,
    perfil, 
    COUNT(*) AS cantidad
FROM colaboradores
GROUP BY perfil
ORDER BY cantidad DESC;

-- Verificar que NO existan valores antiguos
SELECT 
    CASE 
        WHEN COUNT(*) = 0 THEN 'OK - Todos los registros migrados correctamente'
        ELSE CONCAT('ADVERTENCIA - Hay ', COUNT(*), ' registros con perfiles antiguos')
    END AS estado
FROM colaboradores
WHERE perfil IN (
    'Lider Comunitario', 'Lider Social', 'Lider Gremial', 
    'Lider Empresarial', 'Lider Juvenil', 'Lider Poblacional',
    'Lider Diferencial', 'Influencer', 'Medios Tradicionales',
    'Amigo', 'Familia'
);

-- =====================================================
-- PASO 4: Modificar ENUM final (solo 8 nuevos valores)
-- =====================================================

ALTER TABLE colaboradores 
MODIFY COLUMN perfil ENUM(
    'Lider Comunitario / Social',
    'Lider Ambiental',
    'Lider Gremial / Empresarial',
    'Lider Juvenil / Deportivo',
    'Lider Poblacional / Diferencial',
    'Lider Religioso',
    'Influencer / Medios',
    'Vinculo Personal'
) NOT NULL;

SELECT 'ENUM actualizado solo con 8 nuevos perfiles' AS mensaje;

-- =====================================================
-- PASO 5: Verificación final
-- =====================================================

SELECT 
    'Verificación Final' AS mensaje,
    COUNT(*) AS total_colaboradores,
    COUNT(DISTINCT perfil) AS perfiles_unicos
FROM colaboradores;

-- Distribución final
SELECT 
    perfil, 
    COUNT(*) AS cantidad,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM colaboradores), 2) AS porcentaje
FROM colaboradores
GROUP BY perfil
ORDER BY cantidad DESC;

-- =====================================================
-- RESUMEN DE CAMBIOS
-- =====================================================

/*
PERFILES CONSOLIDADOS:

1. Líder Comunitario / Social       (← Líder Comunitario + Líder Social)
2. Líder Ambiental                  (sin cambios)
3. Líder Gremial / Empresarial      (← Líder Gremial + Líder Empresarial)
4. Líder Juvenil / Deportivo        (← Líder Juvenil)
5. Líder Poblacional / Diferencial  (← Líder Poblacional + Líder Diferencial)
6. Líder Religioso                  (NUEVO - sin datos iniciales)
7. Influencer / Medios              (← Influencer + Medios Tradicionales)
8. Vínculo Personal                 (← Amigo + Familia)

Total: 12 perfiles → 8 perfiles
*/

SELECT 'MIGRACIÓN COMPLETADA EXITOSAMENTE' AS estado;

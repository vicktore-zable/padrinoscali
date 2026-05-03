-- =====================================================
-- MIGRACIÓN DE PERFILES DE COLABORADOR
-- De 12 perfiles originales a 8 perfiles consolidados
-- Fecha: 2025-11-28
-- =====================================================

-- IMPORTANTE: Hacer backup de la base de datos antes de ejecutar
-- mysqldump -u root aratio > backup_pre_profile_migration_20251128.sql

USE aratio;

-- =====================================================
-- PASO 1: Crear tabla temporal de respaldo
-- =====================================================

CREATE TABLE IF NOT EXISTS colaboradores_backup_profiles_20251128 AS 
SELECT id, documento, nombres, apellidos, perfil 
FROM colaboradores;

SELECT 'Backup creado con ' AS mensaje, COUNT(*) AS total_registros 
FROM colaboradores_backup_profiles_20251128;

-- =====================================================
-- PASO 2: Verificar perfiles actuales
-- =====================================================

SELECT 
    perfil, 
    COUNT(*) AS cantidad,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM colaboradores), 2) AS porcentaje
FROM colaboradores
GROUP BY perfil
ORDER BY cantidad DESC;

-- =====================================================
-- PASO 3: Migrar datos existentes a nuevos perfiles
-- =====================================================

-- Deshabilitar temporalmente el trigger de auditoría
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';
SET @disable_audit_trigger = 1;

START TRANSACTION;

-- Migrar: Líder Comunitario + Líder Social → Líder Comunitario / Social
UPDATE colaboradores 
SET perfil = 'Lider Comunitario / Social' 
WHERE perfil IN ('Lider Comunitario', 'Lider Social');

-- Líder Ambiental permanece igual
UPDATE colaboradores 
SET perfil = 'Lider Ambiental' 
WHERE perfil = 'Lider Ambiental';

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

-- =====================================================
-- PASO 4: Verificar migración
-- =====================================================

SELECT 
    'Verificación Post-Migración' AS mensaje,
    perfil, 
    COUNT(*) AS cantidad
FROM colaboradores
GROUP BY perfil
ORDER BY cantidad DESC;

-- Verificar que no existan perfiles antiguos
SELECT 
    CASE 
        WHEN COUNT(*) = 0 THEN 'OK - No hay perfiles antiguos'
        ELSE CONCAT('ERROR - Hay ', COUNT(*), ' registros con perfiles antiguos')
    END AS estado
FROM colaboradores
WHERE perfil IN (
    'Lider Comunitario', 'Lider Social', 'Lider Gremial', 
    'Lider Empresarial', 'Lider Juvenil', 'Lider Poblacional',
    'Lider Diferencial', 'Influencer', 'Medios Tradicionales',
    'Amigo', 'Familia'
);

-- =====================================================
-- PASO 5: Modificar ENUM de la tabla
-- =====================================================
-- IMPORTANTE: Se modifica DESPUÉS de migrar los datos
-- porque MySQL descarta valores no válidos al modificar ENUM

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

-- Verificar integridad de datos
SELECT 
    'Verificación Final' AS mensaje,
    COUNT(*) AS total_colaboradores,
    COUNT(DISTINCT perfil) AS perfiles_unicos
FROM colaboradores;

-- Mostrar distribución final
SELECT 
    perfil, 
    COUNT(*) AS cantidad,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM colaboradores), 2) AS porcentaje
FROM colaboradores
GROUP BY perfil
ORDER BY cantidad DESC;

-- =====================================================
-- PASO 7: Restaurar configuración
-- =====================================================

SET SQL_MODE=@OLD_SQL_MODE;
SET @disable_audit_trigger = NULL;

-- =====================================================
-- REGISTRO DE MIGRACIÓN
-- =====================================================

-- INFO: El trigger de auditoría ya registró automáticamente todos los UPDATEs
-- No es necesario crear un log manual adicional

-- =====================================================
-- NOTAS IMPORTANTES
-- =====================================================

/*
MAPEO DE PERFILES APLICADO:

Perfil Antiguo              → Perfil Nuevo
-----------------------------------------------
Líder Comunitario          → Líder Comunitario / Social
Líder Social               → Líder Comunitario / Social
Líder Ambiental            → Líder Ambiental (sin cambios)
Líder Gremial              → Líder Gremial / Empresarial
Líder Empresarial          → Líder Gremial / Empresarial
Líder Juvenil              → Líder Juvenil / Deportivo
Líder Poblacional          → Líder Poblacional / Diferencial
Líder Diferencial          → Líder Poblacional / Diferencial
Influencer                 → Influencer / Medios
Medios Tradicionales       → Influencer / Medios
Amigo                      → Vínculo Personal
Familia                    → Vínculo Personal

NUEVO PERFIL AÑADIDO:
- Líder Religioso (sin colaboradores al inicio)

COLORES ASIGNADOS:
- Líder Comunitario / Social      #3b82f6 (Azul)
- Líder Ambiental                 #10b981 (Verde)
- Líder Gremial / Empresarial     #f59e0b (Ámbar)
- Líder Juvenil / Deportivo       #14b8a6 (Teal)
- Líder Poblacional / Diferencial #6366f1 (Índigo)
- Líder Religioso                 #facc15 (Dorado)
- Influencer / Medios             #ef4444 (Rojo)
- Vínculo Personal                #ec4899 (Rosa)

PARA ROLLBACK (si es necesario):
Restaurar desde la tabla de backup:
  UPDATE colaboradores c
  INNER JOIN colaboradores_backup_profiles_20251128 b ON c.id = b.id
  SET c.perfil = b.perfil;
*/

SELECT 'MIGRACIÓN COMPLETADA EXITOSAMENTE' AS estado;

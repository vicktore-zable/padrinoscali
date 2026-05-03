-- =====================================================
-- LIMPIEZA DE BASE DE DATOS - PRODUCCIÓN (Hostinger)
-- Fecha: 2025-11-28
-- Propósito: Eliminar colaboradores de prueba y preparar
--            para datos reales con nuevos perfiles
-- =====================================================

USE u156469157_aratio;

-- =====================================================
-- PASO 1: Ver estado actual antes de limpiar
-- =====================================================

SELECT 'Estado ANTES de limpieza:' AS mensaje;

SELECT 
    'Colaboradores' AS tabla,
    COUNT(*) AS total_registros
FROM colaboradores;

SELECT 
    'Usuarios' AS tabla,
    COUNT(*) AS total_registros
FROM usuarios;

-- =====================================================
-- PASO 2: Limpiar datos de desarrollo
-- =====================================================

-- Deshabilitar checks de foreign keys temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar todos los colaboradores
TRUNCATE TABLE colaboradores;

-- Limpiar tablas relacionadas
TRUNCATE TABLE curriculum;
TRUNCATE TABLE historial_cambios_lider;

-- Restablecer foreign keys
SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Tablas de colaboradores limpiadas' AS mensaje;

-- =====================================================
-- PASO 3: Limpiar usuarios de prueba (opcional)
-- =====================================================
-- Mantener solo el usuario admin

DELETE FROM sesiones WHERE usuario_id != 1;
DELETE FROM usuarios WHERE id != 1;

-- Resetear password del admin a Admin123!
-- Hash bcrypt de 'Admin123!' con costo 10
UPDATE usuarios 
SET 
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    intentos_fallidos = 0,
    bloqueado_hasta = NULL,
    activo = 1
WHERE id = 1;

SELECT 'Usuarios limpiados - Solo queda admin con contraseña Admin123!' AS mensaje;

-- =====================================================
-- PASO 4: Actualizar ENUM a los 8 nuevos perfiles
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

SELECT 'ENUM de perfiles actualizado a 8 nuevos valores' AS mensaje;

-- =====================================================
-- PASO 5: Resetear auto_increment
-- =====================================================

ALTER TABLE colaboradores AUTO_INCREMENT = 1;
ALTER TABLE curriculum AUTO_INCREMENT = 1;

SELECT 'Auto-increment reseteado' AS mensaje;

-- =====================================================
-- PASO 6: Verificación final
-- =====================================================

SELECT 'Estado DESPUÉS de limpieza:' AS mensaje;

SELECT 
    'Colaboradores' AS tabla,
    COUNT(*) AS total_registros
FROM colaboradores;

SELECT 
    'Usuarios' AS tabla,
    COUNT(*) AS total_registros,
    GROUP_CONCAT(usuario) AS usuarios_activos
FROM usuarios;

SELECT 
    'Curriculum' AS tabla,
    COUNT(*) AS total_registros
FROM curriculum;

-- Mostrar estructura actualizada de perfil
SELECT COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'u156469157_aratio' 
AND TABLE_NAME = 'colaboradores' 
AND COLUMN_NAME = 'perfil';

-- =====================================================
-- RESUMEN
-- =====================================================

SELECT '✅ PRODUCCIÓN LIMPIA Y LISTA PARA NUEVOS COLABORADORES' AS estado;

/*
PERFILES DISPONIBLES (8):

1. Líder Comunitario / Social
2. Líder Ambiental
3. Líder Gremial / Empresarial
4. Líder Juvenil / Deportivo
5. Líder Poblacional / Diferencial
6. Líder Religioso
7. Influencer / Medios
8. Vínculo Personal

USUARIO DISPONIBLE:
- Username: admin
- Password: Admin123!
- Tipo: admin
*/

-- =====================================================
-- SISTEMA DE GESTIÓN INTEGRAL DE COLABORADORES
-- Base de Datos: Hostinger - MariaDB Compatible
-- Versión: 1.0
-- =====================================================

-- Eliminar tablas existentes (orden inverso por foreign keys)
DROP TABLE IF EXISTS logs_auditoria;
DROP TABLE IF EXISTS importaciones_excel;
DROP TABLE IF EXISTS historial_cambios_lider;
DROP TABLE IF EXISTS sesiones;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS curriculum;
DROP TABLE IF EXISTS colaboradores;

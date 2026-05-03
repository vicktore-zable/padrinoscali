-- Limpiar base de datos antes de importar
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar stored procedures
DROP PROCEDURE IF EXISTS sp_obtener_red_jerarquica;
DROP PROCEDURE IF EXISTS sp_cambiar_lider;
DROP PROCEDURE IF EXISTS sp_estadisticas_generales;
DROP PROCEDURE IF EXISTS sp_limpiar_sesiones_expiradas;
DROP PROCEDURE IF EXISTS sp_limpiar_tokens_expirados;

-- Eliminar vistas
DROP VIEW IF EXISTS v_colaboradores_completo;
DROP VIEW IF EXISTS v_estadisticas_perfil;
DROP VIEW IF EXISTS v_estadisticas_territorio;
DROP VIEW IF EXISTS v_lideres_metricas;

-- Eliminar triggers
DROP TRIGGER IF EXISTS trg_colaboradores_insert;
DROP TRIGGER IF EXISTS trg_colaboradores_update;
DROP TRIGGER IF EXISTS trg_colaboradores_delete;
DROP TRIGGER IF EXISTS trg_usuarios_insert;
DROP TRIGGER IF EXISTS trg_usuarios_update;
DROP TRIGGER IF EXISTS trg_usuarios_delete;

-- Eliminar tablas
DROP TABLE IF EXISTS logs_auditoria;
DROP TABLE IF EXISTS importaciones_excel;
DROP TABLE IF EXISTS historial_cambios_lider;
DROP TABLE IF EXISTS sesiones;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS curriculum;
DROP TABLE IF EXISTS colaboradores;

SET FOREIGN_KEY_CHECKS = 1;

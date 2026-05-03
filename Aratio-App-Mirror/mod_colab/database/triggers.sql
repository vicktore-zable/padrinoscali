-- =====================================================
-- TRIGGERS - SISTEMA DE GESTIÓN DE COLABORADORES
-- Triggers para auditoría y validaciones automáticas
-- =====================================================

USE aratio;

DELIMITER $$

-- =====================================================
-- TRIGGERS PARA AUDITORÍA EN COLABORADORES
-- =====================================================

-- Trigger: Auditar INSERT en colaboradores
DROP TRIGGER IF EXISTS trg_colaboradores_after_insert$$
CREATE TRIGGER trg_colaboradores_after_insert
AFTER INSERT ON colaboradores
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (
        usuario_id,
        accion,
        tabla_afectada,
        registro_id,
        datos_nuevos,
        ip_address,
        user_agent
    ) VALUES (
        @current_user_id,
        'INSERT',
        'colaboradores',
        NEW.id,
        JSON_OBJECT(
            'id', NEW.id,
            'documento', NEW.documento,
            'nombres', NEW.nombres,
            'apellidos', NEW.apellidos,
            'perfil', NEW.perfil,
            'lider_directo', NEW.lider_directo
        ),
        @current_ip,
        @current_user_agent
    );
END$$

-- Trigger: Auditar UPDATE en colaboradores
DROP TRIGGER IF EXISTS trg_colaboradores_after_update$$
CREATE TRIGGER trg_colaboradores_after_update
AFTER UPDATE ON colaboradores
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (
        usuario_id,
        accion,
        tabla_afectada,
        registro_id,
        datos_anteriores,
        datos_nuevos,
        ip_address,
        user_agent
    ) VALUES (
        @current_user_id,
        'UPDATE',
        'colaboradores',
        NEW.id,
        JSON_OBJECT(
            'documento', OLD.documento,
            'nombres', OLD.nombres,
            'apellidos', OLD.apellidos,
            'perfil', OLD.perfil,
            'nivel_participacion', OLD.nivel_participacion,
            'lider_directo', OLD.lider_directo,
            'dato_potencial', OLD.dato_potencial,
            'dato_historico', OLD.dato_historico,
            'territorio', OLD.territorio
        ),
        JSON_OBJECT(
            'documento', NEW.documento,
            'nombres', NEW.nombres,
            'apellidos', NEW.apellidos,
            'perfil', NEW.perfil,
            'nivel_participacion', NEW.nivel_participacion,
            'lider_directo', NEW.lider_directo,
            'dato_potencial', NEW.dato_potencial,
            'dato_historico', NEW.dato_historico,
            'territorio', NEW.territorio
        ),
        @current_ip,
        @current_user_agent
    );
END$$

-- Trigger: Auditar DELETE en colaboradores
DROP TRIGGER IF EXISTS trg_colaboradores_after_delete$$
CREATE TRIGGER trg_colaboradores_after_delete
AFTER DELETE ON colaboradores
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (
        usuario_id,
        accion,
        tabla_afectada,
        registro_id,
        datos_anteriores,
        ip_address,
        user_agent
    ) VALUES (
        @current_user_id,
        'DELETE',
        'colaboradores',
        OLD.id,
        JSON_OBJECT(
            'id', OLD.id,
            'documento', OLD.documento,
            'nombres', OLD.nombres,
            'apellidos', OLD.apellidos,
            'perfil', OLD.perfil
        ),
        @current_ip,
        @current_user_agent
    );
END$$

-- =====================================================
-- TRIGGERS PARA VALIDACIONES EN COLABORADORES
-- =====================================================

-- Trigger: Validar que un colaborador no pueda ser su propio líder
DROP TRIGGER IF EXISTS trg_colaboradores_before_insert_validar$$
CREATE TRIGGER trg_colaboradores_before_insert_validar
BEFORE INSERT ON colaboradores
FOR EACH ROW
BEGIN
    -- Validar que no sea su propio líder
    IF NEW.lider_directo = NEW.documento THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Un colaborador no puede ser su propio líder';
    END IF;

    -- Validar formato de documento (solo números para CC y TI)
    IF NEW.tipo_documento IN ('CC', 'TI') AND NEW.documento NOT REGEXP '^[0-9]+$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El documento debe contener solo números para tipo CC o TI';
    END IF;

    -- Validar fecha de nacimiento no sea futura
    IF NEW.fecha_nacimiento > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La fecha de nacimiento no puede ser futura';
    END IF;

    -- Validar edad mínima (mayor de 13 años)
    IF TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE()) < 13 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El colaborador debe tener al menos 13 años';
    END IF;
END$$

-- Trigger: Validar UPDATE en colaboradores
DROP TRIGGER IF EXISTS trg_colaboradores_before_update_validar$$
CREATE TRIGGER trg_colaboradores_before_update_validar
BEFORE UPDATE ON colaboradores
FOR EACH ROW
BEGIN
    -- Validar que no sea su propio líder
    IF NEW.lider_directo = NEW.documento THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Un colaborador no puede ser su propio líder';
    END IF;

    -- Validar fecha de nacimiento no sea futura
    IF NEW.fecha_nacimiento > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La fecha de nacimiento no puede ser futura';
    END IF;

    -- Validar edad mínima
    IF TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE()) < 13 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El colaborador debe tener al menos 13 años';
    END IF;

    -- Registrar cambio de líder automáticamente
    IF OLD.lider_directo <> NEW.lider_directo OR (OLD.lider_directo IS NULL AND NEW.lider_directo IS NOT NULL) OR (OLD.lider_directo IS NOT NULL AND NEW.lider_directo IS NULL) THEN
        INSERT INTO historial_cambios_lider (
            colaborador_documento,
            lider_anterior,
            lider_nuevo,
            motivo,
            usuario_cambio
        ) VALUES (
            NEW.documento,
            OLD.lider_directo,
            NEW.lider_directo,
            'Cambio automático',
            @current_user_id
        );
    END IF;
END$$

-- =====================================================
-- TRIGGERS PARA AUDITORÍA EN USUARIOS
-- =====================================================

-- Trigger: Auditar cambios en usuarios
DROP TRIGGER IF EXISTS trg_usuarios_after_update$$
CREATE TRIGGER trg_usuarios_after_update
AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    -- Solo auditar cambios significativos (no último_acceso ni intentos_fallidos)
    IF OLD.usuario <> NEW.usuario
        OR OLD.email <> NEW.email
        OR OLD.tipo_usuario <> NEW.tipo_usuario
        OR OLD.activo <> NEW.activo
        OR OLD.require_2fa <> NEW.require_2fa
    THEN
        INSERT INTO logs_auditoria (
            usuario_id,
            accion,
            tabla_afectada,
            registro_id,
            datos_anteriores,
            datos_nuevos,
            ip_address,
            user_agent
        ) VALUES (
            @current_user_id,
            'UPDATE',
            'usuarios',
            NEW.id,
            JSON_OBJECT(
                'usuario', OLD.usuario,
                'email', OLD.email,
                'tipo_usuario', OLD.tipo_usuario,
                'activo', OLD.activo,
                'require_2fa', OLD.require_2fa
            ),
            JSON_OBJECT(
                'usuario', NEW.usuario,
                'email', NEW.email,
                'tipo_usuario', NEW.tipo_usuario,
                'activo', NEW.activo,
                'require_2fa', NEW.require_2fa
            ),
            @current_ip,
            @current_user_agent
        );
    END IF;
END$$

-- Trigger: Auditar creación de usuarios
DROP TRIGGER IF EXISTS trg_usuarios_after_insert$$
CREATE TRIGGER trg_usuarios_after_insert
AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (
        usuario_id,
        accion,
        tabla_afectada,
        registro_id,
        datos_nuevos,
        ip_address,
        user_agent
    ) VALUES (
        @current_user_id,
        'INSERT',
        'usuarios',
        NEW.id,
        JSON_OBJECT(
            'id', NEW.id,
            'usuario', NEW.usuario,
            'email', NEW.email,
            'tipo_usuario', NEW.tipo_usuario
        ),
        @current_ip,
        @current_user_agent
    );
END$$

-- =====================================================
-- TRIGGERS PARA VALIDACIONES EN USUARIOS
-- =====================================================

-- Trigger: Validar email antes de insertar
DROP TRIGGER IF EXISTS trg_usuarios_before_insert_validar$$
CREATE TRIGGER trg_usuarios_before_insert_validar
BEFORE INSERT ON usuarios
FOR EACH ROW
BEGIN
    -- Validar formato de email
    IF NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Formato de email inválido';
    END IF;

    -- Validar longitud mínima de usuario
    IF LENGTH(NEW.usuario) < 4 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El usuario debe tener al menos 4 caracteres';
    END IF;

    -- Validar longitud de password (hash)
    IF LENGTH(NEW.password) < 60 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La contraseña debe estar hasheada correctamente';
    END IF;
END$$

-- Trigger: Validar email antes de actualizar
DROP TRIGGER IF EXISTS trg_usuarios_before_update_validar$$
CREATE TRIGGER trg_usuarios_before_update_validar
BEFORE UPDATE ON usuarios
FOR EACH ROW
BEGIN
    -- Validar formato de email
    IF NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Formato de email inválido';
    END IF;

    -- Validar longitud mínima de usuario
    IF LENGTH(NEW.usuario) < 4 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El usuario debe tener al menos 4 caracteres';
    END IF;

    -- Si cambió la contraseña, validar hash
    IF OLD.password <> NEW.password AND LENGTH(NEW.password) < 60 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La contraseña debe estar hasheada correctamente';
    END IF;
END$$

-- =====================================================
-- TRIGGERS PARA CURRICULUM
-- =====================================================

-- Trigger: Auditar INSERT en curriculum
DROP TRIGGER IF EXISTS trg_curriculum_after_insert$$
CREATE TRIGGER trg_curriculum_after_insert
AFTER INSERT ON curriculum
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (
        usuario_id,
        accion,
        tabla_afectada,
        registro_id,
        datos_nuevos,
        ip_address,
        user_agent
    ) VALUES (
        @current_user_id,
        'INSERT',
        'curriculum',
        NEW.id,
        JSON_OBJECT(
            'colaborador_id', NEW.colaborador_id,
            'id', NEW.id
        ),
        @current_ip,
        @current_user_agent
    );
END$$

-- Trigger: Auditar UPDATE en curriculum
DROP TRIGGER IF EXISTS trg_curriculum_after_update$$
CREATE TRIGGER trg_curriculum_after_update
AFTER UPDATE ON curriculum
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (
        usuario_id,
        accion,
        tabla_afectada,
        registro_id,
        datos_anteriores,
        datos_nuevos,
        ip_address,
        user_agent
    ) VALUES (
        @current_user_id,
        'UPDATE',
        'curriculum',
        NEW.id,
        JSON_OBJECT(
            'experiencia_laboral', OLD.experiencia_laboral,
            'formacion_academica', OLD.formacion_academica,
            'participacion_politica', OLD.participacion_politica
        ),
        JSON_OBJECT(
            'experiencia_laboral', NEW.experiencia_laboral,
            'formacion_academica', NEW.formacion_academica,
            'participacion_politica', NEW.participacion_politica
        ),
        @current_ip,
        @current_user_agent
    );
END$$

DELIMITER ;

-- =====================================================
-- NOTAS DE USO
-- =====================================================
-- Para que los triggers de auditoría funcionen correctamente,
-- debes establecer las variables de sesión antes de cada operación:
--
-- SET @current_user_id = 1;
-- SET @current_ip = '192.168.1.1';
-- SET @current_user_agent = 'Mozilla/5.0...';
--
-- Esto se hará automáticamente desde PHP usando la clase Database
-- =====================================================

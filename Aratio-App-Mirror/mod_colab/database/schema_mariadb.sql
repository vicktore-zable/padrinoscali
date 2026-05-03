-- Eliminar tablas existentes (orden inverso por foreign keys)
DROP TABLE IF EXISTS logs_auditoria;
DROP TABLE IF EXISTS importaciones_excel;
DROP TABLE IF EXISTS historial_cambios_lider;
DROP TABLE IF EXISTS sesiones;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS curriculum;
DROP TABLE IF EXISTS colaboradores;

-- =====================================================
-- TABLA: colaboradores
-- =====================================================
CREATE TABLE colaboradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    tipo_documento ENUM('CC', 'TI', 'CE', 'PA', 'RC', 'NIT') NOT NULL DEFAULT 'CC',
    documento VARCHAR(20) NOT NULL UNIQUE,
    fecha_nacimiento DATE NOT NULL,

    -- Perfil del colaborador
    perfil ENUM(
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
        'Familia'
    ) NOT NULL,

    -- Nivel de participación
    nivel_participacion ENUM(
        'Simpatizante',
        'Aportante',
        'Activista de Opinión',
        'Movilizador',
        'Contradictor'
    ) NOT NULL DEFAULT 'Simpatizante',

    -- Áreas de interés (JSON array)
    areas_interes JSON COMMENT 'Array de áreas: Educación, Salud, Medio Ambiente, Economía, Seguridad, Cultura, Deportes, Tecnología, Derechos Humanos, Juventud, Comercio, Plan Centro, Proyectos',

    -- Datos de seguimiento
    dato_potencial INT DEFAULT 0 COMMENT 'Dato potencial del colaborador',
    dato_historico INT DEFAULT 0 COMMENT 'Dato histórico del colaborador',
    estado VARCHAR(20) GENERATED ALWAYS AS (
        CASE
            WHEN dato_potencial = 0 THEN 'Nuevo'
            WHEN dato_historico = 0 THEN 'Desvinculado'
            WHEN dato_historico > dato_potencial THEN 'Creció'
            WHEN dato_historico < dato_potencial THEN 'Decrece'
            ELSE 'Igual'
        END
    ) VIRTUAL,

    -- Información adicional
    observaciones TEXT,
    tipo_territorio VARCHAR(50),
    territorio VARCHAR(100),
    revision DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Relación jerárquica
    lider_directo VARCHAR(20) COMMENT 'FK: documento del líder',

    -- Curriculum
    id_curriculum INT,

    -- Demografía
    genero ENUM('Masculino', 'Femenino', 'Otro', 'Prefiero no decir') NOT NULL,
    grupo_etareo VARCHAR(30) GENERATED ALWAYS AS (
        CASE
            WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 0 AND 12 THEN 'Infancia (0-12 años)'
            WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 13 AND 17 THEN 'Adolescencia (13-17 años)'
            WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 18 AND 28 THEN 'Juventud (18-28 años)'
            WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 29 AND 40 THEN 'Adultez Joven (29-40 años)'
            WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 41 AND 60 THEN 'Adultez Media (41-60 años)'
            ELSE 'Adultez Mayor (61+ años)'
        END
    ) VIRTUAL,

    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_documento (documento),
    INDEX idx_lider_directo (lider_directo),
    INDEX idx_perfil (perfil),
    INDEX idx_nivel_participacion (nivel_participacion),
    INDEX idx_territorio (territorio),
    INDEX idx_estado (estado),
    INDEX idx_grupo_etareo (grupo_etareo),
    INDEX idx_created_at (created_at),

    -- Foreign key al líder directo
    FOREIGN KEY (lider_directo) REFERENCES colaboradores(documento) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: curriculum
-- =====================================================
CREATE TABLE curriculum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,

    -- Experiencia laboral (JSON array de objetos)
    experiencia_laboral JSON COMMENT 'Array de {cargo, empresa, fecha_inicio, fecha_fin, descripcion}',

    -- Formación académica (JSON array de objetos)
    formacion_academica JSON COMMENT 'Array de {titulo, institucion, fecha_inicio, fecha_fin, nivel}',

    -- Participación política (JSON array de objetos)
    participacion_politica JSON COMMENT 'Array de {cargo, organizacion, fecha_inicio, fecha_fin, descripcion}',

    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign key
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
    INDEX idx_colaborador_id (colaborador_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: usuarios
-- =====================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt/Argon2',

    -- Tipo de usuario y permisos
    tipo_usuario ENUM('admin', 'lider', 'consulta') NOT NULL DEFAULT 'consulta',
    documento_colaborador VARCHAR(20) COMMENT 'FK para líderes/consultores',

    -- Estado de la cuenta
    activo BOOLEAN DEFAULT TRUE,
    ultimo_acceso DATETIME,

    -- Seguridad
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    token_2fa VARCHAR(255) NULL,
    require_2fa BOOLEAN DEFAULT FALSE,

    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_usuario (usuario),
    INDEX idx_email (email),
    INDEX idx_tipo_usuario (tipo_usuario),
    INDEX idx_documento_colaborador (documento_colaborador),
    INDEX idx_activo (activo),

    -- Foreign key
    FOREIGN KEY (documento_colaborador) REFERENCES colaboradores(documento) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: sesiones
-- =====================================================
CREATE TABLE sesiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token_sesion VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    ultima_actividad DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expira_en DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_token_sesion (token_sesion),
    INDEX idx_expira_en (expira_en),
    INDEX idx_ultima_actividad (ultima_actividad),

    -- Foreign key
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: historial_cambios_lider
-- =====================================================
CREATE TABLE historial_cambios_lider (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_documento VARCHAR(20) NOT NULL,
    lider_anterior VARCHAR(20),
    lider_nuevo VARCHAR(20),
    motivo TEXT,
    usuario_cambio INT,
    fecha_cambio DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_colaborador_documento (colaborador_documento),
    INDEX idx_fecha_cambio (fecha_cambio),
    INDEX idx_usuario_cambio (usuario_cambio),

    -- Foreign keys
    FOREIGN KEY (colaborador_documento) REFERENCES colaboradores(documento) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (lider_anterior) REFERENCES colaboradores(documento) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (lider_nuevo) REFERENCES colaboradores(documento) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (usuario_cambio) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: importaciones_excel
-- =====================================================
CREATE TABLE importaciones_excel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    registros_procesados INT DEFAULT 0,
    registros_exitosos INT DEFAULT 0,
    registros_fallidos INT DEFAULT 0,
    errores JSON COMMENT 'Array de errores encontrados',
    usuario_id INT,
    fecha_importacion DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_fecha_importacion (fecha_importacion),

    -- Foreign key
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: logs_auditoria
-- =====================================================
CREATE TABLE logs_auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(50),
    registro_id INT,
    datos_anteriores JSON,
    datos_nuevos JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_tabla_afectada (tabla_afectada),
    INDEX idx_created_at (created_at),

    -- Foreign key
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- VISTAS ÚTILES
-- =====================================================

-- Vista: Colaboradores con información completa
CREATE OR REPLACE VIEW v_colaboradores_completo AS
SELECT
    c.*,
    CONCAT(c.nombres, ' ', c.apellidos) AS nombre_completo,
    TIMESTAMPDIFF(YEAR, c.fecha_nacimiento, CURDATE()) AS edad,
    CONCAT(l.nombres, ' ', l.apellidos) AS nombre_lider,
    l.documento AS documento_lider_completo,
    COUNT(DISTINCT s.id) AS total_seguidores
FROM colaboradores c
LEFT JOIN colaboradores l ON c.lider_directo = l.documento
LEFT JOIN colaboradores s ON s.lider_directo = c.documento
GROUP BY c.id;

-- Vista: Estadísticas por perfil
CREATE OR REPLACE VIEW v_estadisticas_perfil AS
SELECT
    perfil,
    COUNT(*) AS total,
    COUNT(CASE WHEN estado = 'Creció' THEN 1 END) AS crecio,
    COUNT(CASE WHEN estado = 'Decrece' THEN 1 END) AS decrece,
    COUNT(CASE WHEN estado = 'Nuevo' THEN 1 END) AS nuevos,
    AVG(dato_potencial) AS promedio_potencial
FROM colaboradores
GROUP BY perfil;

-- Vista: Estadísticas por territorio
CREATE OR REPLACE VIEW v_estadisticas_territorio AS
SELECT
    territorio,
    tipo_territorio,
    COUNT(*) AS total_colaboradores,
    COUNT(DISTINCT lider_directo) AS total_lideres,
    AVG(dato_potencial) AS promedio_potencial
FROM colaboradores
WHERE territorio IS NOT NULL
GROUP BY territorio, tipo_territorio;

-- Vista: Líderes con sus métricas
CREATE OR REPLACE VIEW v_lideres_metricas AS
SELECT
    c.id,
    c.documento,
    CONCAT(c.nombres, ' ', c.apellidos) AS nombre_completo,
    c.perfil,
    c.territorio,
    COUNT(DISTINCT s.id) AS total_seguidores_directos,
    AVG(s.dato_potencial) AS promedio_potencial_seguidores,
    SUM(CASE WHEN s.estado = 'Creció' THEN 1 ELSE 0 END) AS seguidores_creciendo
FROM colaboradores c
LEFT JOIN colaboradores s ON s.lider_directo = c.documento
GROUP BY c.id, c.documento, c.nombres, c.apellidos, c.perfil, c.territorio
HAVING total_seguidores_directos > 0
ORDER BY total_seguidores_directos DESC;

-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS
-- =====================================================

DELIMITER $$

-- Procedimiento: Obtener red jerárquica de un líder
CREATE PROCEDURE sp_obtener_red_jerarquica(IN p_documento_lider VARCHAR(20))
BEGIN
    WITH RECURSIVE red_jerarquica AS (
        -- Caso base: el líder inicial
        SELECT
            id, documento, nombres, apellidos, perfil,
            nivel_participacion, lider_directo, dato_potencial,
            0 AS nivel, documento AS raiz
        FROM colaboradores
        WHERE documento = p_documento_lider

        UNION ALL

        -- Caso recursivo: seguidores
        SELECT
            c.id, c.documento, c.nombres, c.apellidos, c.perfil,
            c.nivel_participacion, c.lider_directo, c.dato_potencial,
            rj.nivel + 1, rj.raiz
        FROM colaboradores c
        INNER JOIN red_jerarquica rj ON c.lider_directo = rj.documento
        WHERE rj.nivel < 10  -- Límite de profundidad
    )
    SELECT * FROM red_jerarquica ORDER BY nivel, nombres;
END$$

-- Procedimiento: Cambiar líder de un colaborador
CREATE PROCEDURE sp_cambiar_lider(
    IN p_documento_colaborador VARCHAR(20),
    IN p_documento_nuevo_lider VARCHAR(20),
    IN p_motivo TEXT,
    IN p_usuario_id INT
)
BEGIN
    DECLARE v_lider_anterior VARCHAR(20);

    -- Obtener líder anterior
    SELECT lider_directo INTO v_lider_anterior
    FROM colaboradores
    WHERE documento = p_documento_colaborador;

    -- Actualizar líder
    UPDATE colaboradores
    SET lider_directo = p_documento_nuevo_lider
    WHERE documento = p_documento_colaborador;

    -- Registrar en historial
    INSERT INTO historial_cambios_lider (
        colaborador_documento, lider_anterior, lider_nuevo, motivo, usuario_cambio
    ) VALUES (
        p_documento_colaborador, v_lider_anterior, p_documento_nuevo_lider, p_motivo, p_usuario_id
    );
END$$

-- Procedimiento: Limpiar sesiones expiradas
CREATE PROCEDURE sp_limpiar_sesiones_expiradas()
BEGIN
    DELETE FROM sesiones WHERE expira_en < NOW();
END$$

-- Procedimiento: Obtener estadísticas generales
CREATE PROCEDURE sp_estadisticas_generales()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM colaboradores) AS total_colaboradores,
        (SELECT COUNT(DISTINCT lider_directo) FROM colaboradores WHERE lider_directo IS NOT NULL) AS total_lideres,
        (SELECT COUNT(DISTINCT territorio) FROM colaboradores WHERE territorio IS NOT NULL) AS total_territorios,
        (SELECT COUNT(*) FROM colaboradores WHERE estado = 'Nuevo') AS colaboradores_nuevos,
        (SELECT COUNT(*) FROM colaboradores WHERE estado = 'Creció') AS colaboradores_creciendo,
        (SELECT COUNT(*) FROM usuarios WHERE activo = TRUE) AS usuarios_activos;
END$$

DELIMITER ;

-- =====================================================
-- EVENTOS PROGRAMADOS
-- =====================================================

-- Habilitar el event scheduler
SET GLOBAL event_scheduler = ON;

-- Evento: Limpiar sesiones expiradas cada hora
CREATE EVENT IF NOT EXISTS evt_limpiar_sesiones
ON SCHEDULE EVERY 1 HOUR
DO
    CALL sp_limpiar_sesiones_expiradas();

-- =====================================================
-- ÍNDICES ADICIONALES PARA PERFORMANCE
-- =====================================================

-- Índices compuestos para consultas frecuentes
CREATE INDEX idx_colaborador_lider_estado ON colaboradores(lider_directo, estado);
CREATE INDEX idx_colaborador_territorio_perfil ON colaboradores(territorio, perfil);
CREATE INDEX idx_colaborador_perfil_nivel ON colaboradores(perfil, nivel_participacion);

-- =====================================================
-- SCRIPT COMPLETADO
-- =====================================================
-- Para importar este script:
-- mysql -u root -p < schema.sql
-- =====================================================

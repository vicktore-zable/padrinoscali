-- =====================================================
-- SISTEMA DE GESTIÓN INTEGRAL DE COLABORADORES
-- ARCHIVO COMPLETO PARA HOSTINGER - MariaDB 11.8.3
-- Incluye: Esquema + Triggers + Vistas + Procedures + Datos
-- =====================================================

-- Deshabilitar foreign key checks
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =====================================================
-- ELIMINAR TABLAS EXISTENTES
-- =====================================================
DROP TABLE IF EXISTS logs_auditoria;
DROP TABLE IF EXISTS importaciones_excel;
DROP TABLE IF EXISTS historial_cambios_lider;
DROP TABLE IF EXISTS sesiones;
DROP TABLE IF EXISTS password_reset_tokens;
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

    nivel_participacion ENUM(
        'Simpatizante',
        'Aportante',
        'Activista de Opinión',
        'Movilizador',
        'Contradictor'
    ) NOT NULL DEFAULT 'Simpatizante',

    areas_interes JSON COMMENT 'Array de áreas',
    dato_potencial INT DEFAULT 0,
    dato_historico INT DEFAULT 0,
    estado VARCHAR(20) DEFAULT 'Nuevo',
    observaciones TEXT,
    tipo_territorio VARCHAR(50),
    territorio VARCHAR(100),
    revision DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    lider_directo VARCHAR(20),
    id_curriculum INT,
    genero ENUM('Masculino', 'Femenino', 'Otro', 'Prefiero no decir') NOT NULL,
    grupo_etareo VARCHAR(30) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_documento (documento),
    INDEX idx_lider_directo (lider_directo),
    INDEX idx_perfil (perfil),
    INDEX idx_nivel_participacion (nivel_participacion),
    INDEX idx_territorio (territorio),
    INDEX idx_estado (estado),
    INDEX idx_grupo_etareo (grupo_etareo),
    INDEX idx_created_at (created_at),

    FOREIGN KEY (lider_directo) REFERENCES colaboradores(documento) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: curriculum
-- =====================================================
CREATE TABLE curriculum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    experiencia_laboral JSON,
    formacion_academica JSON,
    participacion_politica JSON,
    resumen_profesional TEXT,
    habilidades TEXT,
    idiomas TEXT,
    reconocimientos TEXT,
    referencias TEXT,
    observaciones TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
    password VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('admin', 'lider', 'consulta') NOT NULL DEFAULT 'consulta',
    documento_colaborador VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    ultimo_acceso DATETIME,
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    token_2fa VARCHAR(255) NULL,
    require_2fa BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario),
    INDEX idx_email (email),
    INDEX idx_tipo_usuario (tipo_usuario),
    INDEX idx_documento_colaborador (documento_colaborador),
    INDEX idx_activo (activo),
    FOREIGN KEY (documento_colaborador) REFERENCES colaboradores(documento) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: password_reset_tokens
-- =====================================================
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_expires_at (expires_at),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
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
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_token_sesion (token_sesion),
    INDEX idx_expira_en (expira_en),
    INDEX idx_ultima_actividad (ultima_actividad),
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
    INDEX idx_colaborador_documento (colaborador_documento),
    INDEX idx_fecha_cambio (fecha_cambio),
    INDEX idx_usuario_cambio (usuario_cambio),
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
    errores JSON,
    usuario_id INT,
    fecha_importacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_fecha_importacion (fecha_importacion),
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
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_tabla_afectada (tabla_afectada),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rehabilitar foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- TRIGGERS PARA COLUMNAS CALCULADAS
-- =====================================================

DELIMITER $$

CREATE TRIGGER trg_colaboradores_before_insert
BEFORE INSERT ON colaboradores
FOR EACH ROW
BEGIN
    SET NEW.estado = CASE
        WHEN NEW.dato_potencial = 0 THEN 'Nuevo'
        WHEN NEW.dato_historico = 0 THEN 'Desvinculado'
        WHEN NEW.dato_historico > NEW.dato_potencial THEN 'Creció'
        WHEN NEW.dato_historico < NEW.dato_potencial THEN 'Decrece'
        ELSE 'Igual'
    END;
    
    SET NEW.grupo_etareo = CASE
        WHEN TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE()) BETWEEN 0 AND 12 THEN 'Infancia (0-12 años)'
        WHEN TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE()) BETWEEN 13 AND 17 THEN 'Adolescencia (13-17 años)'
        WHEN TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 28 THEN 'Juventud (18-28 años)'
        WHEN TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE()) BETWEEN 29 AND 40 THEN 'Adultez Joven (29-40 años)'
        WHEN TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE()) BETWEEN 41 AND 60 THEN 'Adultez Media (41-60 años)'
        ELSE 'Adultez Mayor (61+ años)'
    END;
END$$

CREATE TRIGGER trg_colaboradores_before_update
BEFORE UPDATE ON colaboradores
FOR EACH ROW
BEGIN
    SET NEW.estado = CASE
        WHEN NEW.dato_potencial = 0 THEN 'Nuevo'
        WHEN NEW.dato_historico = 0 THEN 'Desvinculado'
        WHEN NEW.dato_historico > NEW.dato_potencial THEN 'Creció'
        WHEN NEW.dato_historico < NEW.dato_potencial THEN 'Decrece'
        ELSE 'Igual'
    END;
    
    SET NEW.grupo_etareo = CASE
        WHEN TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE()) BETWEEN 0 AND 12 THEN 'Infancia (0-12 años)'
        WHEN TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE()) BETWEEN 13 AND 17 THEN 'Adolescencia (13-17 años)'
        WHEN TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 28 THEN 'Juventud (18-28 años)'
        WHEN TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE()) BETWEEN 29 AND 40 THEN 'Adultez Joven (29-40 años)'
        WHEN TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE()) BETWEEN 41 AND 60 THEN 'Adultez Media (41-60 años)'
        ELSE 'Adultez Mayor (61+ años)'
    END;
END$$

DELIMITER ;

-- =====================================================
-- VISTAS
-- =====================================================

CREATE OR REPLACE VIEW v_colaboradores_completo AS
SELECT
    c.*,
    CONCAT(c.nombres, ' ', c.apellidos) AS nombre_completo,
    TIMESTAMPDIFF(YEAR, c.fecha_nacimiento, CURDATE()) AS edad,
    CONCAT(l.nombres, ' ', l.apellidos) AS nombre_lider,
    l.documento AS documento_lider_completo,
    COUNT(DISTINCT s.id) AS total_seguidores_directos
FROM colaboradores c
LEFT JOIN colaboradores l ON c.lider_directo = l.documento
LEFT JOIN colaboradores s ON s.lider_directo = c.documento
GROUP BY c.id;

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

CREATE PROCEDURE sp_obtener_red_jerarquica(IN p_documento_lider VARCHAR(20))
BEGIN
    WITH RECURSIVE red_jerarquica AS (
        SELECT
            id, documento, nombres, apellidos, perfil,
            nivel_participacion, lider_directo, dato_potencial,
            0 AS nivel, documento AS raiz
        FROM colaboradores
        WHERE documento = p_documento_lider
        UNION ALL
        SELECT
            c.id, c.documento, c.nombres, c.apellidos, c.perfil,
            c.nivel_participacion, c.lider_directo, c.dato_potencial,
            rj.nivel + 1, rj.raiz
        FROM colaboradores c
        INNER JOIN red_jerarquica rj ON c.lider_directo = rj.documento
        WHERE rj.nivel < 10
    )
    SELECT * FROM red_jerarquica ORDER BY nivel, nombres;
END$$

CREATE PROCEDURE sp_cambiar_lider(
    IN p_documento_colaborador VARCHAR(20),
    IN p_documento_nuevo_lider VARCHAR(20),
    IN p_motivo TEXT,
    IN p_usuario_id INT
)
BEGIN
    DECLARE v_lider_anterior VARCHAR(20);
    SELECT lider_directo INTO v_lider_anterior
    FROM colaboradores
    WHERE documento = p_documento_colaborador;
    
    UPDATE colaboradores
    SET lider_directo = p_documento_nuevo_lider
    WHERE documento = p_documento_colaborador;
    
    INSERT INTO historial_cambios_lider (
        colaborador_documento, lider_anterior, lider_nuevo, motivo, usuario_cambio
    ) VALUES (
        p_documento_colaborador, v_lider_anterior, p_documento_nuevo_lider, p_motivo, p_usuario_id
    );
END$$

CREATE PROCEDURE sp_limpiar_sesiones_expiradas()
BEGIN
    DELETE FROM sesiones WHERE expira_en < NOW();
END$$

CREATE PROCEDURE sp_limpiar_tokens_expirados()
BEGIN
    DELETE FROM password_reset_tokens WHERE expires_at < NOW() OR used = TRUE;
END$$

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
-- ÍNDICES ADICIONALES
-- =====================================================

CREATE INDEX idx_colaborador_lider_estado ON colaboradores(lider_directo, estado);
CREATE INDEX idx_colaborador_territorio_perfil ON colaboradores(territorio, perfil);
CREATE INDEX idx_colaborador_perfil_nivel ON colaboradores(perfil, nivel_participacion);

-- =====================================================
-- DATOS DE PRUEBA
-- =====================================================

INSERT INTO colaboradores (nombres, apellidos, tipo_documento, documento, fecha_nacimiento, perfil, nivel_participacion, areas_interes, dato_potencial, dato_historico, territorio, tipo_territorio, genero, lider_directo) VALUES
('Carlos Andrés', 'Rodríguez Martínez', 'CC', '1000000001', '1975-03-15', 'Lider Comunitario', 'Movilizador', '["Educación", "Salud", "Economía"]', 500, 450, 'Comuna 1', 'Urbano', 'Masculino', NULL);

-- Nivel 1: Líderes Regionales (5 personas)
INSERT INTO colaboradores (nombres, apellidos, tipo_documento, documento, fecha_nacimiento, perfil, nivel_participacion, areas_interes, dato_potencial, dato_historico, territorio, tipo_territorio, genero, lider_directo) VALUES
('María Fernanda', 'García López', 'CC', '1000000002', '1980-06-20', 'Lider Social', 'Movilizador', '["Educación", "Derechos Humanos"]', 320, 300, 'Comuna 2', 'Urbano', 'Femenino', '1000000001'),
('José Luis', 'Hernández Silva', 'CC', '1000000003', '1978-11-10', 'Lider Ambiental', 'Activista de Opinión', '["Medio Ambiente", "Salud"]', 280, 250, 'Comuna 3', 'Urbano', 'Masculino', '1000000001'),
('Ana Patricia', 'Martínez Cruz', 'CC', '1000000004', '1985-02-28', 'Lider Gremial', 'Movilizador', '["Economía", "Comercio"]', 350, 320, 'Comuna 4', 'Urbano', 'Femenino', '1000000001'),
('Roberto Carlos', 'Sánchez Díaz', 'CC', '1000000005', '1983-09-14', 'Lider Empresarial', 'Aportante', '["Economía", "Tecnología"]', 400, 380, 'Comuna 5', 'Urbano', 'Masculino', '1000000001'),
('Laura Valentina', 'Gómez Reyes', 'CC', '1000000006', '1990-12-05', 'Lider Juvenil', 'Movilizador', '["Juventud", "Cultura", "Deportes"]', 290, 260, 'Comuna 6', 'Urbano', 'Femenino', '1000000001');

-- Nivel 2: Líderes de Zona (15 personas, 3 por cada líder regional)
INSERT INTO colaboradores (nombres, apellidos, tipo_documento, documento, fecha_nacimiento, perfil, nivel_participacion, areas_interes, dato_potencial, dato_historico, territorio, tipo_territorio, genero, lider_directo) VALUES
-- Seguidores de María Fernanda
('Pedro Alfonso', 'Ramírez Torres', 'CC', '1000000007', '1988-04-12', 'Lider Comunitario', 'Activista de Opinión', '["Educación", "Cultura"]', 180, 170, 'Comuna 2 - Sector A', 'Urbano', 'Masculino', '1000000002'),
('Diana Carolina', 'López Vargas', 'CC', '1000000008', '1992-07-23', 'Lider Social', 'Movilizador', '["Derechos Humanos", "Educación"]', 200, 180, 'Comuna 2 - Sector B', 'Urbano', 'Femenino', '1000000002'),
('Andrés Felipe', 'Morales Castro', 'CC', '1000000009', '1987-01-30', 'Lider Comunitario', 'Activista de Opinión', '["Salud", "Seguridad"]', 160, 150, 'Comuna 2 - Sector C', 'Urbano', 'Masculino', '1000000002'),

-- Seguidores de José Luis
('Claudia Patricia', 'Rivera Mendoza', 'CC', '1000000010', '1991-08-17', 'Lider Ambiental', 'Movilizador', '["Medio Ambiente"]', 220, 200, 'Comuna 3 - Sector A', 'Urbano', 'Femenino', '1000000003'),
('Miguel Ángel', 'Rojas Cardona', 'CC', '1000000011', '1986-05-25', 'Lider Comunitario', 'Activista de Opinión', '["Medio Ambiente", "Salud"]', 150, 140, 'Comuna 3 - Sector B', 'Rural', 'Masculino', '1000000003'),
('Sandra Milena', 'Ortiz Parra', 'CC', '1000000012', '1989-10-08', 'Lider Ambiental', 'Movilizador', '["Medio Ambiente", "Educación"]', 190, 175, 'Comuna 3 - Sector C', 'Rural', 'Femenino', '1000000003'),

-- Seguidores de Ana Patricia
('Jorge Enrique', 'Pérez Gutiérrez', 'CC', '1000000013', '1984-03-19', 'Lider Gremial', 'Aportante', '["Economía", "Comercio"]', 250, 230, 'Comuna 4 - Sector A', 'Urbano', 'Masculino', '1000000004'),
('Liliana María', 'Castro Ruiz', 'CC', '1000000014', '1993-11-28', 'Lider Empresarial', 'Aportante', '["Comercio", "Economía"]', 210, 190, 'Comuna 4 - Sector B', 'Urbano', 'Femenino', '1000000004'),
('Héctor Fabio', 'Jiménez Salazar', 'CC', '1000000015', '1981-06-14', 'Lider Gremial', 'Movilizador', '["Economía", "Proyectos"]', 270, 250, 'Comuna 4 - Sector C', 'Urbano', 'Masculino', '1000000004'),

-- Seguidores de Roberto Carlos
('Natalia Andrea', 'Vásquez Bermúdez', 'CC', '1000000016', '1990-09-22', 'Influencer', 'Activista de Opinión', '["Tecnología", "Juventud"]', 300, 280, 'Comuna 5 - Sector A', 'Urbano', 'Femenino', '1000000005'),
('Daniel Eduardo', 'Molina Acosta', 'CC', '1000000017', '1988-02-11', 'Lider Empresarial', 'Aportante', '["Economía", "Tecnología"]', 320, 300, 'Comuna 5 - Sector B', 'Urbano', 'Masculino', '1000000005'),
('Carolina Isabel', 'Suárez Pineda', 'CC', '1000000018', '1992-12-07', 'Lider Empresarial', 'Movilizador', '["Tecnología", "Proyectos"]', 280, 260, 'Comuna 5 - Sector C', 'Urbano', 'Femenino', '1000000005'),

-- Seguidores de Laura Valentina
('Camilo Andrés', 'Torres Aguilar', 'CC', '1000000019', '1995-05-16', 'Lider Juvenil', 'Movilizador', '["Juventud", "Deportes"]', 240, 220, 'Comuna 6 - Sector A', 'Urbano', 'Masculino', '1000000006'),
('Juliana Marcela', 'Mejía Duque', 'CC', '1000000020', '1998-08-29', 'Lider Juvenil', 'Activista de Opinión', '["Juventud", "Cultura"]', 200, 180, 'Comuna 6 - Sector B', 'Urbano', 'Femenino', '1000000006'),
('Sebastián David', 'Franco Osorio', 'CC', '1000000021', '1996-03-04', 'Influencer', 'Movilizador', '["Deportes", "Tecnología"]', 260, 240, 'Comuna 6 - Sector C', 'Urbano', 'Masculino', '1000000006');

-- Nivel 3: Colaboradores Base (80 personas, ~5 por cada líder de zona)
INSERT INTO colaboradores (nombres, apellidos, tipo_documento, documento, fecha_nacimiento, perfil, nivel_participacion, areas_interes, dato_potencial, dato_historico, territorio, tipo_territorio, genero, lider_directo) VALUES
-- Seguidores de Pedro Alfonso (1000000007)
('Alejandra', 'Ramírez Pérez', 'CC', '1000000022', '1999-01-15', 'Simpatizante', 'Simpatizante', '["Educación"]', 50, 45, 'Comuna 2 - Sector A', 'Urbano', 'Femenino', '1000000007'),
('Fernando José', 'Luna Campos', 'CC', '1000000023', '1994-07-20', 'Amigo', 'Aportante', '["Cultura"]', 80, 70, 'Comuna 2 - Sector A', 'Urbano', 'Masculino', '1000000007'),
('Valeria Sofia', 'Cruz Herrera', 'CC', '1000000024', '2000-11-30', 'Simpatizante', 'Simpatizante', '["Educación", "Cultura"]', 60, 55, 'Comuna 2 - Sector A', 'Urbano', 'Femenino', '1000000007'),
('Ricardo Javier', 'Pardo Villa', 'TI', '1000000025', '2005-04-08', 'Simpatizante', 'Simpatizante', '["Educación"]', 40, 35, 'Comuna 2 - Sector A', 'Urbano', 'Masculino', '1000000007'),
('Paola Andrea', 'Ríos Medina', 'CC', '1000000026', '1997-09-12', 'Amigo', 'Activista de Opinión', '["Cultura", "Juventud"]', 90, 85, 'Comuna 2 - Sector A', 'Urbano', 'Femenino', '1000000007'),

-- Seguidores de Diana Carolina (1000000008)
('Oscar Mauricio', 'Bernal Cortés', 'CC', '1000000027', '1991-03-25', 'Lider Poblacional', 'Activista de Opinión', '["Derechos Humanos"]', 120, 110, 'Comuna 2 - Sector B', 'Urbano', 'Masculino', '1000000008'),
('Marcela Viviana', 'Galindo Rojas', 'CC', '1000000028', '1993-06-18', 'Lider Social', 'Movilizador', '["Derechos Humanos", "Educación"]', 140, 130, 'Comuna 2 - Sector B', 'Urbano', 'Femenino', '1000000008'),
('Luis Fernando', 'Navarro Ochoa', 'CC', '1000000029', '1989-12-02', 'Amigo', 'Aportante', '["Educación"]', 100, 90, 'Comuna 2 - Sector B', 'Urbano', 'Masculino', '1000000008'),
('Gloria Esperanza', 'Patiño León', 'CC', '1000000030', '1987-08-14', 'Lider Social', 'Activista de Opinión', '["Derechos Humanos"]', 110, 100, 'Comuna 2 - Sector B', 'Urbano', 'Femenino', '1000000008'),
('Fabián Ernesto', 'Quintero Gómez', 'CC', '1000000031', '1995-02-27', 'Simpatizante', 'Simpatizante', '["Educación"]', 70, 65, 'Comuna 2 - Sector B', 'Urbano', 'Masculino', '1000000008'),

-- Seguidores de Andrés Felipe (1000000009)
('Martha Lucía', 'Escobar Trujillo', 'CC', '1000000032', '1992-05-09', 'Familia', 'Aportante', '["Salud"]', 85, 80, 'Comuna 2 - Sector C', 'Urbano', 'Femenino', '1000000009'),
('Jairo Alberto', 'Londoño Zapata', 'CC', '1000000033', '1988-10-21', 'Amigo', 'Activista de Opinión', '["Salud", "Seguridad"]', 95, 90, 'Comuna 2 - Sector C', 'Urbano', 'Masculino', '1000000009'),
('Adriana María', 'Valencia Hurtado', 'CC', '1000000034', '1990-01-16', 'Lider Comunitario', 'Movilizador', '["Seguridad"]', 130, 120, 'Comuna 2 - Sector C', 'Urbano', 'Femenino', '1000000009'),
('Hugo Alexander', 'Montoya Arias', 'CC', '1000000035', '1994-07-03', 'Simpatizante', 'Simpatizante', '["Salud"]', 75, 70, 'Comuna 2 - Sector C', 'Urbano', 'Masculino', '1000000009'),
('Isabel Cristina', 'Restrepo Muñoz', 'CC', '1000000036', '1996-11-24', 'Familia', 'Aportante', '["Salud"]', 80, 75, 'Comuna 2 - Sector C', 'Urbano', 'Femenino', '1000000009'),

-- Seguidores de Claudia Patricia (1000000010) - continuamos con el patrón
('Germán Darío', 'Arango Bedoya', 'CC', '1000000037', '1990-04-11', 'Lider Ambiental', 'Movilizador', '["Medio Ambiente"]', 150, 140, 'Comuna 3 - Sector A', 'Urbano', 'Masculino', '1000000010'),
('Mónica Patricia', 'Cardenas Gil', 'CC', '1000000038', '1993-09-27', 'Lider Ambiental', 'Activista de Opinión', '["Medio Ambiente", "Educación"]', 125, 115, 'Comuna 3 - Sector A', 'Urbano', 'Femenino', '1000000010'),
('William Andrés', 'Botero Marín', 'CC', '1000000039', '1988-02-14', 'Amigo', 'Aportante', '["Medio Ambiente"]', 105, 95, 'Comuna 3 - Sector A', 'Rural', 'Masculino', '1000000010'),
('Yenny Paola', 'Correa Álvarez', 'CC', '1000000040', '1995-06-30', 'Simpatizante', 'Simpatizante', '["Medio Ambiente"]', 65, 60, 'Comuna 3 - Sector A', 'Rural', 'Femenino', '1000000010'),
('Jhon Fredy', 'Sierra Duque', 'CC', '1000000041', '1992-12-19', 'Activista de Opinión', 'Activista de Opinión', '["Medio Ambiente"]', 110, 100, 'Comuna 3 - Sector A', 'Rural', 'Masculino', '1000000010');

-- Continuamos agregando más colaboradores para completar los 100+
-- (Simplificamos insertando en bloques por líder)

-- Seguidores adicionales distribuidos
INSERT INTO colaboradores (nombres, apellidos, tipo_documento, documento, fecha_nacimiento, perfil, nivel_participacion, areas_interes, dato_potencial, dato_historico, territorio, tipo_territorio, genero, lider_directo)
SELECT
    CONCAT('Colaborador', n) AS nombres,
    CONCAT('Apellido', n) AS apellidos,
    'CC' AS tipo_documento,
    CAST(1000000041 + n AS CHAR) AS documento,
    DATE_SUB(CURDATE(), INTERVAL (18 + MOD(n, 40)) YEAR) AS fecha_nacimiento,
    ELT(MOD(n, 12) + 1, 'Lider Comunitario', 'Lider Ambiental', 'Lider Gremial', 'Lider Social', 'Lider Empresarial', 'Influencer', 'Lider Juvenil', 'Lider Poblacional', 'Lider Diferencial', 'Medios Tradicionales', 'Amigo', 'Familia') AS perfil,
    ELT(MOD(n, 5) + 1, 'Simpatizante', 'Aportante', 'Activista de Opinión', 'Movilizador', 'Contradictor') AS nivel_participacion,
    JSON_ARRAY(
        ELT(MOD(n, 13) + 1, 'Educación', 'Salud', 'Medio Ambiente', 'Economía', 'Seguridad', 'Cultura', 'Deportes', 'Tecnología', 'Derechos Humanos', 'Juventud', 'Comercio', 'Plan Centro', 'Proyectos')
    ) AS areas_interes,
    50 + MOD(n * 7, 150) AS dato_potencial,
    45 + MOD(n * 5, 140) AS dato_historico,
    CONCAT('Comuna ', MOD(n, 6) + 1, ' - Sector ', CHAR(65 + MOD(n, 3))) AS territorio,
    IF(MOD(n, 3) = 0, 'Rural', 'Urbano') AS tipo_territorio,
    IF(MOD(n, 2) = 0, 'Masculino', 'Femenino') AS genero,
    CAST(1000000007 + MOD(n, 14) AS CHAR) AS lider_directo
FROM (
    SELECT @row := @row + 1 AS n
    FROM (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t1,
         (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t2,
         (SELECT @row := 0) r
    LIMIT 65
) numbers;

-- =====================================================
-- INSERTAR USUARIOS
-- =====================================================

-- Usuario Admin principal (password: Admin123!)
INSERT INTO usuarios (usuario, email, password, tipo_usuario, documento_colaborador, activo, require_2fa) VALUES
('admin', 'admin@aratio.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lH3vMJOhW9gy', 'admin', NULL, TRUE, FALSE);

-- Usuarios Líderes (los 5 líderes regionales)
INSERT INTO usuarios (usuario, email, password, tipo_usuario, documento_colaborador, activo, require_2fa) VALUES
('mgarcia', 'mgarcia@aratio.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lH3vMJOhW9gy', 'lider', '1000000002', TRUE, FALSE),
('jhernandez', 'jhernandez@aratio.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lH3vMJOhW9gy', 'lider', '1000000003', TRUE, FALSE),
('amartinez', 'amartinez@aratio.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lH3vMJOhW9gy', 'lider', '1000000004', TRUE, FALSE),
('rsanchez', 'rsanchez@aratio.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lH3vMJOhW9gy', 'lider', '1000000005', TRUE, FALSE),
('lgomez', 'lgomez@aratio.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lH3vMJOhW9gy', 'lider', '1000000006', TRUE, FALSE);

-- Usuario de consulta
INSERT INTO usuarios (usuario, email, password, tipo_usuario, documento_colaborador, activo, require_2fa) VALUES
('consulta', 'consulta@aratio.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lH3vMJOhW9gy', 'consulta', NULL, TRUE, FALSE);

-- =====================================================
-- INSERTAR CURRICULUM PARA ALGUNOS LÍDERES
-- =====================================================

INSERT INTO curriculum (colaborador_id, experiencia_laboral, formacion_academica, participacion_politica) VALUES
(1,
    '[{"cargo":"Director General","empresa":"Fundación Social","fecha_inicio":"2010-01-01","fecha_fin":"2020-12-31","descripcion":"Liderazgo de proyectos comunitarios"}]',
    '[{"titulo":"Administración Pública","institucion":"Universidad Nacional","fecha_inicio":"1993-01-01","fecha_fin":"1998-12-31","nivel":"Pregrado"}]',
    '[{"cargo":"Líder Comunitario","organizacion":"Junta de Acción Comunal","fecha_inicio":"2000-01-01","fecha_fin":null,"descripcion":"Representante de la comunidad"}]'
),
(2,
    '[{"cargo":"Trabajadora Social","empresa":"Alcaldía Municipal","fecha_inicio":"2005-03-01","fecha_fin":"2015-06-30","descripcion":"Atención a población vulnerable"}]',
    '[{"titulo":"Trabajo Social","institucion":"Universidad de Antioquia","fecha_inicio":"1998-01-01","fecha_fin":"2003-12-31","nivel":"Pregrado"}]',
    '[{"cargo":"Coordinadora Social","organizacion":"Movimiento Ciudadano","fecha_inicio":"2015-01-01","fecha_fin":null,"descripcion":"Coordinación de programas sociales"}]'
);

-- =====================================================
-- DATOS ADICIONALES
-- =====================================================

-- Nota: La contraseña para todos los usuarios de prueba es: Admin123!
-- Hash generado con: password_hash('Admin123!', PASSWORD_BCRYPT, ['cost' => 12])

-- =====================================================
-- CONSULTAS DE VERIFICACIÓN
-- =====================================================

-- Verificar total de colaboradores
SELECT COUNT(*) AS total_colaboradores FROM colaboradores;

-- Verificar estructura jerárquica
SELECT
    nivel_jerarquico,
    COUNT(*) AS cantidad
FROM (
    SELECT
        CASE
            WHEN lider_directo IS NULL THEN 'Nivel 0 - Líder Principal'
            WHEN lider_directo = '1000000001' THEN 'Nivel 1 - Líderes Regionales'
            WHEN lider_directo IN (SELECT documento FROM colaboradores WHERE lider_directo = '1000000001') THEN 'Nivel 2 - Líderes de Zona'
            ELSE 'Nivel 3+ - Colaboradores Base'
        END AS nivel_jerarquico
    FROM colaboradores
) AS niveles
GROUP BY nivel_jerarquico;

-- Verificar distribución por perfil
SELECT perfil, COUNT(*) AS cantidad FROM colaboradores GROUP BY perfil ORDER BY cantidad DESC;

-- Verificar distribución por territorio
SELECT territorio, COUNT(*) AS cantidad FROM colaboradores GROUP BY territorio ORDER BY cantidad DESC LIMIT 10;

-- Verificar usuarios creados
SELECT id, usuario, email, tipo_usuario, activo FROM usuarios;

-- =====================================================
-- FIN DE SEEDS

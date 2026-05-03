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
    ) STORED,

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

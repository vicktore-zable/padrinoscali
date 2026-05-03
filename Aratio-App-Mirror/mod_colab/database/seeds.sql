-- =====================================================
-- DATOS DE PRUEBA (SEEDS)
-- Sistema de Gestión de Colaboradores
-- 100+ colaboradores con relaciones jerárquicas
-- =====================================================

USE aratio;

-- Deshabilitar foreign key checks temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- Limpiar tablas (solo para desarrollo)
TRUNCATE TABLE logs_auditoria;
TRUNCATE TABLE importaciones_excel;
TRUNCATE TABLE historial_cambios_lider;
TRUNCATE TABLE sesiones;
TRUNCATE TABLE usuarios;
TRUNCATE TABLE curriculum;
TRUNCATE TABLE colaboradores;

-- Rehabilitar foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- INSERTAR COLABORADORES (Estructura Jerárquica)
-- =====================================================

-- Nivel 0: Líder Principal
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
('admin', 'admin@aratio.com', '$2y$12$jJUpKd3XORjihRd64FZjeOjdo4V7WgjOVVFCCgmBIE1Ol8Yu0N85kye', 'admin', NULL, TRUE, FALSE);

-- Usuarios Líderes (los 5 líderes regionales)
INSERT INTO usuarios (usuario, email, password, tipo_usuario, documento_colaborador, activo, require_2fa) VALUES
('mgarcia', 'mgarcia@aratio.com', '$2y$12$jJUpKd3XORjihRd64FZjeOjdo4V7WgjOVVFCCgmBIE1Ol8Yu0N85kye', 'lider', '1000000002', TRUE, FALSE),
('jhernandez', 'jhernandez@aratio.com', '$2y$12$jJUpKd3XORjihRd64FZjeOjdo4V7WgjOVVFCCgmBIE1Ol8Yu0N85kye', 'lider', '1000000003', TRUE, FALSE),
('amartinez', 'amartinez@aratio.com', '$2y$12$jJUpKd3XORjihRd64FZjeOjdo4V7WgjOVVFCCgmBIE1Ol8Yu0N85kye', 'lider', '1000000004', TRUE, FALSE),
('rsanchez', 'rsanchez@aratio.com', '$2y$12$jJUpKd3XORjihRd64FZjeOjdo4V7WgjOVVFCCgmBIE1Ol8Yu0N85kye', 'lider', '1000000005', TRUE, FALSE),
('lgomez', 'lgomez@aratio.com', '$2y$12$jJUpKd3XORjihRd64FZjeOjdo4V7WgjOVVFCCgmBIE1Ol8Yu0N85kye', 'lider', '1000000006', TRUE, FALSE);

-- Usuario de consulta
INSERT INTO usuarios (usuario, email, password, tipo_usuario, documento_colaborador, activo, require_2fa) VALUES
('consulta', 'consulta@aratio.com', '$2y$12$jJUpKd3XORjihRd64FZjeOjdo4V7WgjOVVFCCgmBIE1Ol8Yu0N85kye', 'consulta', NULL, TRUE, FALSE);

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
-- =====================================================

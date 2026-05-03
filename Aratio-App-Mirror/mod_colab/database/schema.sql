-- =====================================================
-- DATOS INICIALES - MUNICIPIOS ACTIVOS
-- =====================================================

-- Insertar municipios activos del Valle del Cauca
INSERT INTO colaboradores (nombres, apellidos, documento, fecha_nacimiento, perfil, nivel_participacion, genero, municipio, departamento, pais, barrio, detalle, unidad, tipo_territorio, territorio) VALUES
-- Cali
('Juan', 'Pérez', '12345678', '1980-01-01', 'Lider Comunitario', 'Activista de Opinión', 'Masculino', 'Cali', 'Valle del Cauca', 'Colombia', 'Centro', 'Zona urbana principal', 'Unidad 1', 'Municipio', 'Cali Centro'),
('María', 'García', '87654321', '1985-05-15', 'Lider Ambiental', 'Movilizador', 'Femenino', 'Cali', 'Valle del Cauca', 'Colombia', 'Norte', 'Sector residencial', 'Unidad 2', 'Municipio', 'Cali Norte'),
('Carlos', 'Rodríguez', '11223344', '1975-03-20', 'Lider Social', 'Contradictor', 'Masculino', 'Cali', 'Valle del Cauca', 'Colombia', 'Sur', 'Zona industrial', 'Unidad 1', 'Municipio', 'Cali Sur'),

-- Yumbo
('Ana', 'Martínez', '44332211', '1990-07-10', 'Lider Juvenil', 'Simpatizante', 'Femenino', 'Yumbo', 'Valle del Cauca', 'Colombia', 'Centro', 'Plaza principal', 'Unidad 3', 'Municipio', 'Yumbo Centro'),
('Pedro', 'López', '55667788', '1982-11-25', 'Lider Empresarial', 'Aportante', 'Masculino', 'Yumbo', 'Valle del Cauca', 'Colombia', 'Industrial', 'Zona fabril', 'Unidad 4', 'Municipio', 'Yumbo Industrial'),

-- Palmira
('Laura', 'Hernández', '88776655', '1988-09-05', 'Lider Gremial', 'Activista de Opinión', 'Femenino', 'Palmira', 'Valle del Cauca', 'Colombia', 'Centro', 'Centro histórico', 'Unidad 2', 'Municipio', 'Palmira Centro'),
('Diego', 'Gómez', '33445566', '1978-12-12', 'Influencer', 'Movilizador', 'Masculino', 'Palmira', 'Valle del Cauca', 'Colombia', 'Rural', 'Zona agrícola', 'Unidad 3', 'Municipio', 'Palmira Rural'),

-- La Cumbre
('Sofia', 'Torres', '77889900', '1992-04-18', 'Lider Poblacional', 'Simpatizante', 'Femenino', 'La Cumbre', 'Valle del Cauca', 'Colombia', 'Centro', 'Cabecera municipal', 'Unidad 1', 'Municipio', 'La Cumbre Centro'),
('Miguel', 'Ramírez', '00112233', '1983-08-30', 'Lider Diferencial', 'Aportante', 'Masculino', 'La Cumbre', 'Valle del Cauca', 'Colombia', 'Rural', 'Zona cafetera', 'Unidad 4', 'Municipio', 'La Cumbre Rural')

ON DUPLICATE KEY UPDATE
    municipio = VALUES(municipio),
    departamento = VALUES(departamento),
    pais = VALUES(pais),
    barrio = VALUES(barrio),
    detalle = VALUES(detalle),
    unidad = VALUES(unidad),
    tipo_territorio = VALUES(tipo_territorio),
    territorio = VALUES(territorio);

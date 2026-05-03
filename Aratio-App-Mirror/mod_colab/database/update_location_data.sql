-- Script para actualizar datos de ubicación en la base de datos
-- Departamento: Valle del Cauca
-- Ciudades: Cali, Yumbo, Palmira, La Cumbre (distribuidas aleatoriamente)

-- Primero, actualizar todos los registros existentes con departamento Valle del Cauca
UPDATE colaboradores SET departamento = 'Valle del Cauca' WHERE departamento IS NULL OR departamento = '';

-- Actualizar país por defecto
UPDATE colaboradores SET pais = 'Colombia' WHERE pais IS NULL OR pais = '';

-- Crear una tabla temporal con las ciudades del Valle del Cauca
CREATE TEMPORARY TABLE temp_ciudades_valle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ciudad VARCHAR(100)
);

INSERT INTO temp_ciudades_valle (ciudad) VALUES
('Cali'),
('Yumbo'),
('Palmira'),
('La Cumbre');

-- Actualizar municipios de forma aleatoria para registros del Valle del Cauca
SET @counter = 0;
UPDATE colaboradores
SET municipio = (
    SELECT ciudad FROM temp_ciudades_valle
    WHERE id = (@counter := @counter + 1) % 4 + 1
)
WHERE departamento = 'Valle del Cauca' AND (municipio IS NULL OR municipio = '');

-- Limpiar la tabla temporal
DROP TEMPORARY TABLE temp_ciudades_valle;

-- Verificar la distribución
SELECT
    departamento,
    municipio,
    COUNT(*) as total_colaboradores
FROM colaboradores
WHERE departamento = 'Valle del Cauca'
GROUP BY departamento, municipio
ORDER BY municipio;
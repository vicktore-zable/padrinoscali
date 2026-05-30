-- Ejemplo de Query Avanzada para filtrar usuarios con hijos nacidos después de un año específico (ej. 2010)
-- Utilizando la función JSON_EXTRACT / operador ->>

SELECT 
    c.id AS colaborador_id,
    c.nombres,
    c.apellidos,
    cur.hijos_data
FROM 
    colaboradores c
JOIN 
    curriculum cur ON c.id = cur.colaborador_id
WHERE 
    -- Comprueba si el array JSON contiene al menos un objeto con fecha mayor o igual a '2010-01-01'
    JSON_EXTRACT(cur.hijos_data, '$[*].fecha') >= '2010-01-01';

-- Alternativamente, utilizando JSON_TABLE (MySQL 8.0+)
SELECT DISTINCT 
    c.id AS colaborador_id,
    c.nombres,
    c.apellidos
FROM 
    colaboradores c
JOIN 
    curriculum cur ON c.id = cur.colaborador_id,
    JSON_TABLE(
        cur.hijos_data,
        '$[*]' COLUMNS (
            fecha_nacimiento DATE PATH '$.fecha'
        )
    ) jt
WHERE 
    jt.fecha_nacimiento >= '2010-01-01';

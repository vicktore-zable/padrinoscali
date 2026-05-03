-- Esquema de Base de Datos para Módulo mod_diaD (Actualizado)

CREATE TABLE IF NOT EXISTS supervisores_telefonos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telefono VARCHAR(20) NOT NULL,
    nombre_supervisor VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reportes_diaD (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_campaña VARCHAR(20) DEFAULT '02',
    id_colaborador INT NOT NULL,
    id_puesto INT DEFAULT NULL,
    id_mesa INT DEFAULT NULL,
    votos_nuevos INT NOT NULL,
    votos_total INT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado_semaforo VARCHAR(20) DEFAULT NULL, -- ROJO, AMARILLO, VERDE
    FOREIGN KEY (id_colaborador) REFERENCES usuarios(id) ON DELETE CASCADE
);

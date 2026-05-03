<?php
// import_puestos.php

// Configuración básica
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';

// Aumentar tiempo de ejecución y memoria
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Iniciando proceso de importación de Puestos de Votación...\n";

// 1. Crear la tabla si no existe
$sqlCreateTable = "
CREATE TABLE IF NOT EXISTS puestos_votacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento VARCHAR(100) NOT NULL,
    municipio VARCHAR(100) NOT NULL,
    puesto VARCHAR(255) NOT NULL,
    comuna VARCHAR(100) DEFAULT NULL,
    direccion VARCHAR(255) DEFAULT NULL,
    latitud DECIMAL(10, 8) DEFAULT NULL,
    longitud DECIMAL(11, 8) DEFAULT NULL,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_municipio (municipio),
    INDEX idx_departamento (departamento),
    INDEX idx_puesto (puesto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $conn->exec($sqlCreateTable);
    echo "Tabla 'puestos_votacion' verificada/creada correctamente.\n";
} catch (PDOException $e) {
    die("Error creando la tabla: " . $e->getMessage() . "\n");
}

// 2. Leer el archivo CSV
$csvFile = __DIR__ . '/Divipole_Elecciones_Territoritoriales_2023_con_georreferenciación_20260212_valle).csv';

if (!file_exists($csvFile)) {
    die("Error: No se encuentra el archivo CSV: $csvFile\n");
}

$handle = fopen($csvFile, "r");
if ($handle === FALSE) {
    die("Error al abrir el archivo CSV.\n");
}

// 3. Preparar la sentencia SQL
$sqlInsert = "INSERT INTO puestos_votacion (departamento, municipio, puesto, comuna, direccion, latitud, longitud) 
              VALUES (:departamento, :municipio, :puesto, :comuna, :direccion, :latitud, :longitud)";
$stmt = $conn->prepare($sqlInsert);

// 4. Procesar el CSV
$headers = fgetcsv($handle, 1000, ","); // Leer cabeceras
// Mapeo de índices basado en la cabecera:
// 0: DEPARTAMENTO, 1: MUNICIPIO, 2: PUESTO, 3: COMUNA, 4: DIRECCIÓN, 5: LATITUD, 6: LONGITUD

$count = 0;
$errors = 0;
$conn->beginTransaction();

while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    // Validar datos básicos
    if (count($data) < 7) {
        $errors++;
        continue;
    }

    try {
        $lat = filter_var($data[5], FILTER_VALIDATE_FLOAT);
        $lon = filter_var($data[6], FILTER_VALIDATE_FLOAT);

        $params = [
            ':departamento' => trim($data[0]),
            ':municipio' => trim($data[1]),
            ':puesto' => trim($data[2]),
            ':comuna' => trim($data[3]),
            ':direccion' => trim($data[4]),
            ':latitud' => $lat !== false ? $lat : null,
            ':longitud' => $lon !== false ? $lon : null
        ];

        $stmt->execute($params);
        $count++;

        if ($count % 500 == 0) {
            $conn->commit();
            $conn->beginTransaction();
            echo "Procesados $count registros...\n";
        }

    } catch (Exception $e) {
        $errors++;
        echo "Error en fila $count: " . $e->getMessage() . "\n";
    }
}

$conn->commit();
fclose($handle);

echo "\n Importación finalizada.\n";
echo "Registros importados: $count\n";
echo "Errores: $errors\n";

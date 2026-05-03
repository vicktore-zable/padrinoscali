<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

$host = 'auth-db690.hstgr.io';
$db   = 'u156469157_aratio';
$user = 'u156469157_aratio';
$pass = '15zxCeBbvgsR';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<h1>Conexión Exitosa - Importación</h1>";

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

    $pdo->exec($sqlCreateTable);
    echo "Tabla 'puestos_votacion' verificada/creada correctamente.<br>";

    // 2. Leer el archivo CSV
    $csvFile = __DIR__ . '/Divipole_Elecciones_Territoritoriales_2023_con_georreferenciación_20260212_valle).csv';
    
    if (!file_exists($csvFile)) {
        die("Error: No se encuentra el archivo CSV: $csvFile<br>");
    }

    $handle = fopen($csvFile, "r");
    if ($handle === FALSE) {
        die("Error al abrir el archivo CSV.<br>");
    }

    $sqlInsert = "INSERT INTO puestos_votacion (departamento, municipio, puesto, comuna, direccion, latitud, longitud) 
                  VALUES (:departamento, :municipio, :puesto, :comuna, :direccion, :latitud, :longitud)";
    $stmt = $pdo->prepare($sqlInsert);

    $cols = fgetcsv($handle, 1000, ","); // Headers
    $count = 0;
    $errors = 0;
    $pdo->beginTransaction();

    echo "Iniciando importación...<br>";

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if (count($data) < 7) { $errors++; continue; }
        try {
            $lat = filter_var($data[5], FILTER_VALIDATE_FLOAT);
            $lon = filter_var($data[6], FILTER_VALIDATE_FLOAT);
            
            // Reemplazo de binds por seguridad
            $stmt->bindValue(':departamento', trim($data[0]));
            $stmt->bindValue(':municipio', trim($data[1]));
            $stmt->bindValue(':puesto', trim($data[2]));
            $stmt->bindValue(':comuna', trim($data[3]));
            $stmt->bindValue(':direccion', trim($data[4]));
            $stmt->bindValue(':latitud', $lat !== false ? $lat : null);
            $stmt->bindValue(':longitud', $lon !== false ? $lon : null);

            $stmt->execute();
            $count++;
            
            if ($count % 500 == 0) {
                $pdo->commit();
                $pdo->beginTransaction();
                echo ". ";
                flush();
            }
        } catch (\Exception $e) {
            $errors++;
            // echo "Error en fila $count: " . $e->getMessage() . "<br>";
        }
    }
    $pdo->commit();
    fclose($handle);
    echo "<br><strong>Importación finalizada.</strong> Registros insertados: $count. Errores: $errors<br>";

} catch (\PDOException $e) {
     echo "❌ Error de Conexión: " . $e->getMessage();
}
?>

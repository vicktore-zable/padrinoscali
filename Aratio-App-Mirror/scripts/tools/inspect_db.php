<?php
// Configuración básica (hardcoded para evitar dependencias)
$host = 'localhost';
$port = 3306;
$db   = 'aratio';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "--- TABLA USUARIOS ---\n";
    $stmt = $pdo->query("DESCRIBE usuarios");
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . " | " . $row['Type'] . "\n";
    }

} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage();
}

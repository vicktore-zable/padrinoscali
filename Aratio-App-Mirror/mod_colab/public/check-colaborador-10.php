<?php
/**
 * Verificar si existe colaborador con ID 10
 */

// Configuración de BD
$host = 'auth-db690.hstgr.io';
$port = 3306;
$dbname = 'u156469157_aratio';
$user = 'u156469157_aratio';
$pass = '15zxCeBbvgsR';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<h1>Verificación de Colaborador ID=10</h1>";
    echo "<pre>";

    // Buscar por ID
    $stmt = $pdo->prepare("SELECT * FROM colaboradores WHERE id = ?");
    $stmt->execute([10]);
    $colab = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($colab) {
        echo "✓ COLABORADOR ENCONTRADO POR ID\n\n";
        echo "ID: " . $colab['id'] . "\n";
        echo "Documento: " . $colab['documento'] . "\n";
        echo "Nombre: " . $colab['nombres'] . " " . $colab['apellidos'] . "\n";
        echo "Perfil: " . $colab['perfil'] . "\n";
    } else {
        echo "✗ NO EXISTE colaborador con ID = 10\n\n";
    }

    // Listar todos los IDs disponibles
    $stmt = $pdo->query("SELECT id, documento, nombres, apellidos FROM colaboradores ORDER BY id LIMIT 20");
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n=== COLABORADORES DISPONIBLES (Primeros 20) ===\n";
    foreach ($todos as $c) {
        echo "ID: {$c['id']} | Doc: {$c['documento']} | Nombre: {$c['nombres']} {$c['apellidos']}\n";
    }

    // Contar total
    $total = $pdo->query("SELECT COUNT(*) FROM colaboradores")->fetchColumn();
    echo "\nTotal colaboradores en BD: {$total}\n";

    echo "</pre>";

} catch (PDOException $e) {
    echo "<h1>Error de Conexión</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}

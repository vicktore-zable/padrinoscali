<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Migración Simple - Historial Estados</h1><pre>";

// Conexión directa a la base de datos
$host = 'auth-db690.hstgr.io';
$dbname = 'u156469157_aratio';
$username = 'u156469157_aratio';
$password = '15zxCeBbvgsR';

try {
    echo "1. Conectando a la base de datos...\n";
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "   ✅ Conexión exitosa\n\n";

    // Crear tabla
    echo "2. Creando tabla historial_estados...\n";
    $sql = "CREATE TABLE IF NOT EXISTS historial_estados (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        colaborador_id INT UNSIGNED NOT NULL,
        fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        dato_potencial INT NOT NULL DEFAULT 0,
        dato_historico INT NOT NULL DEFAULT 0,
        estado VARCHAR(20) NOT NULL,
        usuario_id INT UNSIGNED NULL,
        motivo VARCHAR(255) NULL,
        tipo_cambio ENUM('inicial', 'actualizacion', 'reevaluacion') DEFAULT 'actualizacion',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_colaborador (colaborador_id),
        INDEX idx_fecha (fecha_registro),
        INDEX idx_estado (estado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "   ✅ Tabla creada/verificada\n\n";

    // Contar registros actuales
    echo "3. Verificando registros...\n";
    $count = $pdo->query("SELECT COUNT(*) FROM historial_estados")->fetchColumn();
    echo "   Registros actuales: $count\n\n";

    if ($count == 0) {
        echo "4. Inicializando historial desde colaboradores...\n";
        $sql = "INSERT INTO historial_estados (colaborador_id, fecha_registro, dato_potencial, dato_historico, estado, tipo_cambio)
                SELECT id, COALESCE(created_at, NOW()), dato_potencial, dato_historico, estado, 'inicial'
                FROM colaboradores";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $inserted = $stmt->rowCount();
        echo "   ✅ $inserted registros inicializados\n\n";
    } else {
        echo "4. Ya hay datos, no es necesario inicializar.\n\n";
    }

    // Mostrar algunos registros de ejemplo
    echo "5. Primeros 5 registros del historial:\n";
    $registros = $pdo->query("SELECT h.*, c.nombres, c.apellidos FROM historial_estados h
                              JOIN colaboradores c ON h.colaborador_id = c.id
                              ORDER BY h.id DESC LIMIT 5")->fetchAll();
    foreach ($registros as $r) {
        echo "   - {$r['nombres']} {$r['apellidos']}: Potencial={$r['dato_potencial']}, Estado={$r['estado']}\n";
    }

    // Resumen
    $finalCount = $pdo->query("SELECT COUNT(*) FROM historial_estados")->fetchColumn();
    echo "\n========================================\n";
    echo "✅ MIGRACIÓN COMPLETADA\n";
    echo "Total registros: $finalCount\n";
    echo "========================================\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<br><br>";
echo "<a href='/dashboard' style='padding:10px 20px; background:#3b82f6; color:white; text-decoration:none; border-radius:5px;'>Ir al Dashboard</a> ";
echo "<a href='/colaboradores/1' style='padding:10px 20px; background:#10b981; color:white; text-decoration:none; border-radius:5px;'>Ver Colaborador</a>";
echo "<br><br><strong>⚠️ ELIMINAR este archivo después de usar</strong>";

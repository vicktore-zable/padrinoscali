<?php
/**
 * Script para ejecutar la migración de historial_estados
 * Este script debe ejecutarse una sola vez
 * ELIMINAR DESPUÉS DE EJECUTAR
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Migración: Sistema de Trazabilidad Histórica</h1>";
echo "<pre>";

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // 1. Verificar/Crear tabla historial_estados
    echo "1. Verificando/Creando tabla historial_estados...\n";

    $createTable = "CREATE TABLE IF NOT EXISTS historial_estados (
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
        INDEX idx_estado (estado),
        INDEX idx_colaborador_fecha (colaborador_id, fecha_registro)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($createTable);
    echo "   ✅ Tabla verificada/creada\n\n";

    // 2. Verificar si ya existen datos
    $count = $pdo->query("SELECT COUNT(*) FROM historial_estados")->fetchColumn();
    echo "2. Registros actuales en historial_estados: $count\n\n";

    if ($count == 0) {
        // 3. Inicializar historial para colaboradores existentes
        echo "3. Inicializando historial para colaboradores existentes...\n";

        $initSql = "INSERT INTO historial_estados (colaborador_id, fecha_registro, dato_potencial, dato_historico, estado, tipo_cambio)
        SELECT
            id,
            COALESCE(created_at, NOW()),
            dato_potencial,
            dato_historico,
            estado,
            'inicial'
        FROM colaboradores";

        $stmt = $pdo->prepare($initSql);
        $stmt->execute();
        $inserted = $stmt->rowCount();
        echo "   ✅ $inserted colaboradores inicializados\n\n";
    } else {
        echo "3. Los datos ya están inicializados, saltando...\n\n";
    }

    // 4. Verificar triggers (mostrar info solamente)
    echo "4. Estado de triggers:\n";
    $triggers = $pdo->query("SHOW TRIGGERS LIKE 'colaboradores'")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($triggers)) {
        echo "   ⚠️  No hay triggers en la tabla colaboradores\n";
        echo "   Nota: Los triggers deben crearse manualmente desde phpMyAdmin o consola MySQL\n\n";
    } else {
        foreach ($triggers as $trigger) {
            echo "   - " . $trigger['Trigger'] . " (" . $trigger['Event'] . ")\n";
        }
        echo "\n";
    }

    // 5. Verificar vistas
    echo "5. Verificando vistas...\n";
    try {
        $pdo->query("SELECT 1 FROM v_trazabilidad_colaborador LIMIT 1");
        echo "   ✅ v_trazabilidad_colaborador existe\n";
    } catch (Exception $e) {
        echo "   ⚠️  v_trazabilidad_colaborador no existe (crear manualmente)\n";
    }

    try {
        $pdo->query("SELECT 1 FROM v_evolucion_estados LIMIT 1");
        echo "   ✅ v_evolucion_estados existe\n";
    } catch (Exception $e) {
        echo "   ⚠️  v_evolucion_estados no existe (crear manualmente)\n";
    }

    // Resumen
    $finalCount = $pdo->query("SELECT COUNT(*) FROM historial_estados")->fetchColumn();

    echo "\n========================================\n";
    echo "✅ MIGRACIÓN COMPLETADA\n";
    echo "========================================\n";
    echo "Total registros en historial_estados: $finalCount\n";
    echo "\n";
    echo "⚠️  IMPORTANTE:\n";
    echo "1. Elimina este archivo después de verificar\n";
    echo "2. Los triggers y vistas avanzadas deben crearse desde phpMyAdmin\n";
    echo "   usando el archivo: database/migrations/create_historial_estados.sql\n";
    echo "</pre>";

    echo "<br><br>";
    echo "<a href='/dashboard' style='padding:10px 20px; background:#3b82f6; color:white; text-decoration:none; border-radius:5px; margin-right:10px;'>Ir al Dashboard</a>";
    echo "<a href='/colaboradores/1' style='padding:10px 20px; background:#10b981; color:white; text-decoration:none; border-radius:5px;'>Ver Colaborador #1</a>";

} catch (Exception $e) {
    echo "\n\n❌ ERROR:\n";
    echo $e->getMessage() . "\n";
    echo "\nArchivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
echo "</pre>";

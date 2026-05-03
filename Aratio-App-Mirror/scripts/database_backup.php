<?php
/**
 * ARATIO - Sistema de Gestión Electoral
 * Script de Respaldo Automatizado de Base de Datos
 */

require_once __DIR__ . '/../config/config.php';

// 1. Verificación de Seguridad
$key = $_GET['key'] ?? '';
if (php_sapi_name() === 'cli') {
    // Si es CLI, buscar el argumento key=valor
    foreach ($argv as $arg) {
        if (strpos($arg, 'key=') === 0) {
            $key = substr($arg, 4);
        }
    }
}

if (empty(BACKUP_KEY) || $key !== BACKUP_KEY) {
    header('HTTP/1.1 403 Forbidden');
    die("Acceso denegado: Clave de respaldo inválida.");
}

// 2. Configuración de Rutas
$backupDir = __DIR__ . '/../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$filename = 'backup_' . DB_NAME . '_' . date('Y-m-d_H-i-s') . '.sql.gz';
$filepath = $backupDir . '/' . $filename;

// 3. Ejecución del Respaldo
try {
    $db = getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Abrir archivo comprimido
    $zp = gzopen($filepath, "w9");
    if (!$zp) {
        throw new Exception("No se pudo crear el archivo de respaldo.");
    }

    gzwrite($zp, "-- ARATIO DATABASE BACKUP\n");
    gzwrite($zp, "-- Date: " . date('Y-m-d H:i:s') . "\n");
    gzwrite($zp, "-- Database: " . DB_NAME . "\n\n");

    // Desactivar chequeo de llaves foráneas
    gzwrite($zp, "SET FOREIGN_KEY_CHECKS=0;\n\n");

    // Obtener todas las tablas
    $tables = [];
    $stmt = $db->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    foreach ($tables as $table) {
        gzwrite($zp, "-- Structure for item: $table\n");
        $stmt = $db->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $createSql = $row['Create Table'] ?? $row['Create View'] ?? null;
        if ($createSql) {
            gzwrite($zp, $createSql . ";\n\n");
        }

        // Si es una vista, no intentar exportar datos
        if (isset($row['Create View'])) {
            continue;
        }

        gzwrite($zp, "-- Data for table: $table\n");
        $stmt = $db->query("SELECT * FROM `$table`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $keys = array_keys($row);
            $values = array_values($row);
            
            // Escapar valores
            $escapedValues = array_map(function($val) use ($db) {
                if ($val === null) return 'NULL';
                return $db->quote($val);
            }, $values);

            $sql = "INSERT INTO `$table` (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
            gzwrite($zp, $sql);
        }
        gzwrite($zp, "\n\n");
    }

    // Reactivar chequeo de llaves foráneas
    gzwrite($zp, "SET FOREIGN_KEY_CHECKS=1;\n");

    gzclose($zp);

    echo "Respaldo completado exitosamente: $filename\n";

    // 4. Limpieza de respaldos antiguos (más de 30 días)
    $files = glob($backupDir . '/*.sql.gz');
    $now = time();
    $days30 = 30 * 24 * 60 * 60;

    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) >= $days30) {
                unlink($file);
                echo "Archivo antiguo eliminado: " . basename($file) . "\n";
            }
        }
    }

} catch (Exception $e) {
    if (isset($zp)) gzclose($zp);
    if (file_exists($filepath)) unlink($filepath);
    die("Error durante el respaldo: " . $e->getMessage());
}

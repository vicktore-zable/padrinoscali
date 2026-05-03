<?php
/**
 * Sincronizador de Bases de Datos (DB a DB)
 * Este script replica la funcionalidad de 'import mysql.py' para ser ejecutado vía Cron Job en Hostinger.
 * Sincroniza desde 'u156469157_aratio' (Origen) a 'u156469157_aratio_v1' (Destino).
 */

// Si se ejecuta desde web, requerimos cierta seguridad o estar logueado, 
// pero para Cron Jobs (CLI) no hay sesión de navegador.
// Verificamos si es ejecución por consola o si se define una clave secreta en la URL.

// Clave simple para ejecución web cron si es necesario: ?key=sincronizacion_segura_123
$cronKey = 'sincronizacion_segura_123';

if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['key']) || $_GET['key'] !== $cronKey) {
        die('Acceso denegado');
    }
}

// Configuración de BD Origen (Hostinger)
$dbSource = [
    'host' => 'auth-db690.hstgr.io',
    'dbname' => 'u156469157_aratio',
    'user' => 'u156469157_aratio',
    'pass' => '15zxCeBbvgsR'
];

// Configuración de BD Destino (Hostinger - V1)
$dbTarget = [
    'host' => 'auth-db690.hstgr.io',
    'dbname' => 'u156469157_aratio_v1',
    'user' => 'u156469157_aratio_v1',
    'pass' => '15zxCeBbvgsR'
];

try {
    // 1. Conexión Origen
    echo "Conectando a Origen...\n";
    $pdoSource = new PDO("mysql:host={$dbSource['host']};dbname={$dbSource['dbname']};charset=utf8mb4", $dbSource['user'], $dbSource['pass']);
    $pdoSource->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Conexión Destino
    echo "Conectando a Destino...\n";
    $pdoTarget = new PDO("mysql:host={$dbTarget['host']};dbname={$dbTarget['dbname']};charset=utf8mb4", $dbTarget['user'], $dbTarget['pass']);
    $pdoTarget->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 3. Obtener datos Origen
    $stmt = $pdoSource->query("SELECT * FROM colaboradores");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        die("No hay datos en origen para sincronizar.\n");
    }

    echo "Procesando " . count($rows) . " registros...\n";

    // 4. Preparar UPSERT en Destino
    $sql = "INSERT INTO colaboradores (
                campana_id, nombres, apellidos, tipo_documento, documento, 
                fecha_nacimiento, genero, telefono, email,
                departamento, municipio, puesto_votacion, mesa_votacion,
                lider_directo, perfil, nivel_participacion, dato_potencial,
                created_at, updated_at
            ) VALUES (
                :campana_id, :nombres, :apellidos, :tipo_documento, :documento, 
                :fecha_nacimiento, :genero, :telefono, :email,
                :departamento, :municipio, :puesto_votacion, :mesa_votacion,
                :lider_directo, :perfil, :nivel_participacion, :dato_potencial,
                :created_at, :updated_at
            )
            ON DUPLICATE KEY UPDATE 
                nombres = VALUES(nombres),
                apellidos = VALUES(apellidos),
                genero = VALUES(genero),
                telefono = VALUES(telefono),
                email = VALUES(email),
                departamento = VALUES(departamento),
                municipio = VALUES(municipio),
                puesto_votacion = VALUES(puesto_votacion),
                mesa_votacion = VALUES(mesa_votacion),
                lider_directo = VALUES(lider_directo),
                perfil = VALUES(perfil),
                nivel_participacion = VALUES(nivel_participacion),
                dato_potencial = VALUES(dato_potencial),
                updated_at = NOW()";
    
    $upsert = $pdoTarget->prepare($sql);

    $count = 0;
    foreach ($rows as $row) {
        $params = [
            ':campana_id' => 2, // Forzado a Campaña 2
            ':nombres' => $row['nombres'],
            ':apellidos' => $row['apellidos'],
            ':tipo_documento' => $row['tipo_documento'],
            ':documento' => $row['documento'],
            ':fecha_nacimiento' => $row['fecha_nacimiento'],
            ':genero' => $row['genero'],
            ':telefono' => $row['telefono'] ?? null,
            ':email' => $row['email'] ?? null,
            ':departamento' => $row['departamento'] ?? 'Valle del Cauca',
            ':municipio' => $row['municipio'] ?? 'Yumbo',
            ':puesto_votacion' => $row['puesto_votacion'] ?? null,
            ':mesa_votacion' => $row['mesa_votacion'] ?? null,
            ':lider_directo' => $row['lider_directo'] ?? null,
            ':perfil' => $row['perfil'] ?? 'Simpatizante',
            ':nivel_participacion' => $row['nivel_participacion'] ?? 'Simpatizante',
            ':dato_potencial' => $row['dato_potencial'] ?? 0,
            ':created_at' => $row['created_at'],
            ':updated_at' => $row['updated_at']
        ];

        $upsert->execute($params);
        $count++;
    }

    echo "Sincronización completada. {$count} registros procesados.\n";

} catch (PDOException $e) {
    die("Error de Base de Datos: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("Error General: " . $e->getMessage() . "\n");
}
?>

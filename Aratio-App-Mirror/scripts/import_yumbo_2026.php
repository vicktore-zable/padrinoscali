<?php
/**
 * Script de importación para archivo: AMIGOS QUE VOTAN EN YUMBO - MARZO - 08 - 2026.xlsx
 * Versión optimizada para CLI y mínimas dependencias.
 */

// Rutas absolutas
$basePath = 'h:/Mi unidad/2025/5d/app/Multi-Campaign Management System';
require_once $basePath . '/config/config.php';

// Intentar cargar SimpleXLSX
$xlsxPath = $basePath . '/includes/SimpleXLSX.php';
if (!file_exists($xlsxPath)) {
    die("ERROR: No se encuentra SimpleXLSX.php en $xlsxPath\n");
}
require_once $xlsxPath;

use Shuchkin\SimpleXLSX;

$file = $basePath . '/storage/AMIGOS QUE VOTAN EN YUMBO - MARZO - 08 - 2026.xlsx';

echo "Iniciando importación directa...\n";

try {
    $db = getDB();
} catch (Exception $e) {
    die("ERROR de conexión BD: " . $e->getMessage() . "\n");
}

if ($xlsx = SimpleXLSX::parse($file)) {
    $rows = $xlsx->rows();
    
    $exitosos = 0;
    $fallidos = 0;
    $duplicados = 0;
    $errores = [];

    // Preparar INSERT
    $sql = "INSERT INTO colaboradores (
        documento, tipo_documento, nombres, apellidos, fecha_nacimiento, 
        genero, email, telefono, telefono_whatsapp, departamento, 
        municipio, direccion, barrio, perfil, nivel_participacion, 
        puesto_votacion, mesa_votacion, lider_directo, estado,
        dato_potencial, dato_historico
    ) VALUES (
        ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, 
        ?, ?, ?, ?,
        ?, ?
    )";
    $stmt = $db->prepare($sql);

    // Preparar CHECK DUPLICADO
    $checkSql = "SELECT id FROM colaboradores WHERE documento = ?";
    $checkStmt = $db->prepare($checkSql);

    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        if (empty(array_filter($row))) continue;

        $fullName = trim($row[0]);
        $documento = trim($row[1]);
        $direccion = trim($row[2]);
        $mesa = trim($row[3]);
        $barrio = trim($row[4]);
        $puesto = trim($row[5]);
        $lider_directo = trim($row[6]);
        $perfil = trim($row[7]);
        $participacion = trim($row[8]);
        $fecha_nacimiento = trim($row[9]);
        $telefono = trim($row[10]);
        $email = trim($row[11]);

        // Separar nombres y apellidos
        $nameParts = explode(' ', $fullName);
        if (count($nameParts) >= 4) {
            $nombres = $nameParts[0] . ' ' . $nameParts[1];
            $apellidos = $nameParts[2] . ' ' . $nameParts[3];
        } else if (count($nameParts) == 3) {
            $nombres = $nameParts[0];
            $apellidos = $nameParts[1] . ' ' . $nameParts[2];
        } else if (count($nameParts) == 2) {
            $nombres = $nameParts[0];
            $apellidos = $nameParts[1];
        } else {
            $nombres = $fullName;
            $apellidos = '';
        }

        $f_nac = ($fecha_nacimiento == '0000-00-00' || empty($fecha_nacimiento)) ? '2000-01-01' : $fecha_nacimiento;

        try {
            // Check duplicado
            $checkStmt->execute([$documento]);
            if ($checkStmt->fetch()) {
                $duplicados++;
                continue;
            }

            $stmt->execute([
                $documento, 'CC', $nombres, $apellidos, $f_nac,
                'Otro', $email, $telefono, $telefono, 'VALLE DEL CAUCA',
                'YUMBO', $direccion, $barrio, $perfil ?: 'Simpatizante', $participacion ?: 'Simpatizante',
                $puesto, $mesa, $lider_directo, 'Nuevo',
                0, 0
            ]);
            
            $exitosos++;
            if ($exitosos % 50 == 0) echo "Procesados: $exitosos...\n";

        } catch (Exception $e) {
            $fallidos++;
            $errores[] = "Fila $i ($documento): " . $e->getMessage();
        }
    }

    echo "\nRESUMEN FINAL:\n";
    echo "- Exitosos: $exitosos\n";
    echo "- Duplicados saltados: $duplicados\n";
    echo "- Fallidos: $fallidos\n";

    if (!empty($errores)) {
        echo "\nDETALLE DE ERRORES (primeros 5):\n";
        print_r(array_slice($errores, 0, 5));
    }

} else {
    echo "ERROR al abrir Excel: " . SimpleXLSX::parseError() . "\n";
    echo "\nNOTA: Es posible que falte la extensión php_xml en su entorno CLI.\n";
}


<?php
/**
 * Debug Script - Red Jerárquica
 * Verificar error en API de red
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

$db = getDB();
$rootDoc = '10556527';
$campanaId = 2;

try {
    echo "=== DEBUG RED JERÁRQUICA ===\n\n";

    // 1. Verificar que el colaborador existe
    echo "1. Verificando colaborador $rootDoc...\n";
    $stmt = $db->prepare("SELECT id, documento, nombres, apellidos FROM colaboradores WHERE documento = ? AND campana_id = ?");
    $stmt->execute([$rootDoc, $campanaId]);
    $colab = $stmt->fetch();

    if ($colab) {
        echo "✓ Colaborador encontrado: " . $colab['nombres'] . " " . $colab['apellidos'] . "\n\n";
    } else {
        echo "✗ Colaborador NO encontrado\n\n";
        exit;
    }

    // 2. Verificar si existe el stored procedure
    echo "2. Verificando stored procedure sp_obtener_red_jerarquica...\n";
    $stmt = $db->prepare("SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'sp_obtener_red_jerarquica'");
    $stmt->execute();
    $proc = $stmt->fetch();

    if ($proc) {
        echo "✓ Stored procedure existe\n\n";
    } else {
        echo "✗ Stored procedure NO existe\n\n";
        echo "Intentando con query directa...\n\n";

        // Alternativa: Query recursiva directa
        $stmt = $db->prepare("
            WITH RECURSIVE red_jerarquica AS (
                -- Nodo raíz
                SELECT 
                    id, documento, nombres, apellidos, perfil, nivel_participacion,
                    dato_potencial, dato_historico, lider_directo, municipio,
                    0 as nivel_jerarquico
                FROM colaboradores
                WHERE documento = ? AND campana_id = ?
                
                UNION ALL
                
                -- Descendientes recursivos
                SELECT 
                    c.id, c.documento, c.nombres, c.apellidos, c.perfil, c.nivel_participacion,
                    c.dato_potencial, c.dato_historico, c.lider_directo, c.municipio,
                    rj.nivel_jerarquico + 1
                FROM colaboradores c
                INNER JOIN red_jerarquica rj ON c.lider_directo = rj.documento
                WHERE c.campana_id = ?
            )
            SELECT * FROM red_jerarquica
            ORDER BY nivel_jerarquico, nombres
        ");
        $stmt->execute([$rootDoc, $campanaId, $campanaId]);
        $colaboradores = $stmt->fetchAll();

        echo "✓ Query recursiva ejecutada\n";
        echo "Total colaboradores en red: " . count($colaboradores) . "\n\n";

        foreach ($colaboradores as $c) {
            echo "  - Nivel {$c['nivel_jerarquico']}: {$c['nombres']} {$c['apellidos']} (Doc: {$c['documento']})\n";
        }

        exit;
    }

    // 3. Intentar ejecutar el stored procedure
    echo "3. Ejecutando stored procedure...\n";
    $stmt = $db->prepare("CALL sp_obtener_red_jerarquica(?)");
    $stmt->execute([$rootDoc]);
    $colaboradores = $stmt->fetchAll();

    echo "✓ Stored procedure ejecutado exitosamente\n";
    echo "Total colaboradores en red: " . count($colaboradores) . "\n\n";

    foreach ($colaboradores as $c) {
        echo "  - {$c['nombres']} {$c['apellidos']} (Doc: {$c['documento']})\n";
    }

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

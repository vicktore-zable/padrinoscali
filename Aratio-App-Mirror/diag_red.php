<?php
/**
 * DIAGNÓSTICO: Red Jerárquica - Solo para depuración, eliminar después
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=utf-8');

$root_doc = $_GET['root_doc'] ?? null;

echo "=== DIAGNÓSTICO RED JERÁRQUICA ===\n\n";

// 1. Sesión
echo "--- 1. SESIÓN ---\n";
echo "session_id: " . session_id() . "\n";
echo "campana_activa: " . ($_SESSION['campana_activa'] ?? 'NO DEFINIDA') . "\n";
echo "campana_nombre: " . ($_SESSION['campana_nombre'] ?? 'NO DEFINIDA') . "\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NO AUTENTICADO') . "\n\n";

// 2. Parámetro root_doc
echo "--- 2. GET PARAMS ---\n";
echo "root_doc: " . ($root_doc ?? 'NO ENVIADO') . "\n\n";

// 3. Base de datos
echo "--- 3. BASE DE DATOS ---\n";
try {
    $db = getDB();
    echo "Conexión: OK\n";
    
    // Verificar estructura de la tabla campanas
    $cols = $db->query("DESCRIBE campanas")->fetchAll(PDO::FETCH_COLUMN);
    echo "Columnas campanas: " . implode(', ', $cols) . "\n\n";
    
    // Verificar si el root_doc existe en colaboradores
    if ($root_doc) {
        echo "--- 4. BÚSQUEDA POR root_doc ($root_doc) ---\n";
        $stmt = $db->prepare("SELECT documento, campana_id, nombres, apellidos FROM colaboradores WHERE documento = ? LIMIT 1");
        $stmt->execute([$root_doc]);
        $col = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($col) {
            echo "Colaborador encontrado:\n";
            print_r($col);
            
            // Buscar la campaña
            $stmtCam = $db->prepare("SELECT id, nombre FROM campanas WHERE id = ? LIMIT 1");
            $stmtCam->execute([$col['campana_id']]);
            $cam = $stmtCam->fetch(PDO::FETCH_ASSOC);
            echo "\nCampaña asociada:\n";
            print_r($cam);
        } else {
            echo "ERROR: Documento '$root_doc' NO encontrado en colaboradores\n";
            
            // Mostrar algunos documentos para verificar formato
            $sample = $db->query("SELECT documento, campana_id FROM colaboradores LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            echo "\nEjemplos de documentos en BD:\n";
            print_r($sample);
        }
    }
    
    // 5. Verificar constante ROOT_PATH
    echo "\n--- 5. CONSTANTES DE RUTAS ---\n";
    echo "BASE_PATH: " . (defined('BASE_PATH') ? BASE_PATH : 'NO DEFINIDA') . "\n";
    echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'NO DEFINIDA') . "\n";
    echo "IS_LOCAL: " . (IS_LOCAL ? 'true (LOCAL)' : 'false (PRODUCCIÓN)') . "\n";
    echo "ROOT_PATH: " . (defined('ROOT_PATH') ? ROOT_PATH : 'NO DEFINIDA') . "\n";
    echo "instagram_data.json existe: " . (file_exists(BASE_PATH . '/storage/instagram_data.json') ? 'SÍ' : 'NO') . "\n";
    
    // 6. Verificar API de red
    echo "\n--- 6. API NETWORK ---\n";
    $campanaId = $_SESSION['campana_activa'] ?? null;
    if (!$campanaId && $root_doc) {
        $stmt2 = $db->prepare("SELECT c.campana_id FROM colaboradores c WHERE c.documento = ? LIMIT 1");
        $stmt2->execute([$root_doc]);
        $r = $stmt2->fetch();
        $campanaId = $r['campana_id'] ?? null;
    }
    echo "campana_id a usar para la red: " . ($campanaId ?? 'NINGUNA') . "\n";
    
    if ($campanaId) {
        // Simular la query de la red
        $stmtNet = $db->prepare("SELECT c.documento, c.nombres, c.apellidos, c.lider_directo, c.perfil FROM colaboradores c WHERE c.campana_id = ? AND c.estado NOT IN ('inactivo','Inactivo') LIMIT 5");
        $stmtNet->execute([$campanaId]);
        $netSample = $stmtNet->fetchAll(PDO::FETCH_ASSOC);
        echo "Muestra de nodos de red (" . count($netSample) . " resultados):\n";
        print_r($netSample);
    }

} catch (Exception $e) {
    echo "ERROR DB: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DIAGNÓSTICO ===\n";

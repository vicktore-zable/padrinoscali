<?php
require_once __DIR__ . '/config/config.php';

try {
    $db = getDB();
    $campanaId = 4;
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM colaboradores WHERE campana_id = ? AND (nombres IS NULL OR apellidos IS NULL OR perfil IS NULL OR municipio IS NULL OR departamento IS NULL)");
    $stmt->execute([$campanaId]);
    $nullCount = $stmt->fetchColumn();
    echo "Records with NULL critical fields: $nullCount\n";
    
    $stmt = $db->prepare("SELECT id, nombres, apellidos FROM colaboradores WHERE campana_id = ? AND (nombres = '' OR apellidos = '')");
    $stmt->execute([$campanaId]);
    $emptyNames = $stmt->fetchAll();
    echo "Records with empty names: " . count($emptyNames) . "\n";
    
    $stmt = $db->prepare("SELECT id, documento, lider_directo FROM colaboradores WHERE campana_id = ?");
    $stmt->execute([$campanaId]);
    $rows = $stmt->fetchAll();
    
    $docs = array_column($rows, 'documento');
    $invalidLiders = 0;
    foreach ($rows as $row) {
        if ($row['lider_directo'] && !in_array($row['lider_directo'], $docs)) {
            // Check if leader exists in OTHER campaigns (maybe that's "forgotten"?)
            $s2 = $db->prepare("SELECT COUNT(*) FROM colaboradores WHERE documento = ?");
            $s2->execute([$row['lider_directo']]);
            if ($s2->fetchColumn() == 0) {
                $invalidLiders++;
            }
        }
    }
    echo "Records with non-existent leader documents: $invalidLiders\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

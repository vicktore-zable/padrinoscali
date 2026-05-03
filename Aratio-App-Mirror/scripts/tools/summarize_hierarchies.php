<?php
require_once __DIR__ . '/../../config/config.php';
$db = getDB();

$res = [];
foreach ([1, 2, 3, 4] as $cid) {
    $stmt = $db->prepare("SELECT COUNT(*) as total, COUNT(CASE WHEN lider_directo != '' AND lider_directo IS NOT NULL THEN 1 END) as with_lider FROM colaboradores WHERE campana_id = ?");
    $stmt->execute([$cid]);
    $res[$cid] = $stmt->fetch();
}
print_r($res);
?>

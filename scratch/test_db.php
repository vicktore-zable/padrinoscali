<?php
require_once 'config/config.php';
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM colaboradores WHERE nombres LIKE '%edi%' OR apellidos LIKE '%edi%' OR documento LIKE '%edi%' LIMIT 5");
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}

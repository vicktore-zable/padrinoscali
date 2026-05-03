<?php
require_once __DIR__ . '/../../config/config.php';
$db = getDB();
$stmt = $db->query("DESCRIBE colaboradores");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($res, JSON_PRETTY_PRINT);

<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $host = 'auth-db690.hstgr.io';
    $dbname = 'u156469157_aratio_v1';
    $user = 'u156469157_aratio_v1';
    $pass = '15zxCeBbvgsR';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT DISTINCT departamento FROM territorios WHERE departamento IS NOT NULL AND departamento != '' ORDER BY departamento");
    $data = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

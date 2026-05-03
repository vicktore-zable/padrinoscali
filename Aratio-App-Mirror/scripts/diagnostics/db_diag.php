<?php
$host = 'auth-db690.hstgr.io';
$db1 = 'u156469157_aratio';
$db2 = 'u156469157_aratio_v1';
$user = 'u156469157_aratio_v1';
$pass = '15zxCeBbvgsR';

function check_db($h, $d, $u, $p) {
    echo "--- Checking $d ---\n";
    try {
        $pdo = new PDO("mysql:host=$h;dbname=$d;charset=utf8mb4", $u, $p);
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables: " . implode(", ", $tables) . "\n";
        
        if (in_array('territorios', $tables)) {
            $count = $pdo->query("SELECT COUNT(*) FROM territorios")->fetchColumn();
            echo "Territorios: $count\n";
        }
        if (in_array('colaboradores', $tables)) {
            $count = $pdo->query("SELECT COUNT(*) FROM colaboradores")->fetchColumn();
            echo "Colaboradores: $count\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

check_db($host, $db1, $user, $pass);
check_db($host, $db2, $user, $pass);

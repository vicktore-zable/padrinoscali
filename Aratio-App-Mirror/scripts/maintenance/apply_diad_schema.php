<?php
$host = "auth-db690.hstgr.io";
$dbname = "u156469157_aratio_v1";
$user = "u156469157_aratio_v1";
$pass = "15zxCeBbvgsR";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents(__DIR__ . '/database/schema_diaD.sql');
    $pdo->exec($sql);
    
    echo "DiaD schema applied successfully.\n";
    
    // Verify changes
    $q = $pdo->query("SHOW TABLES LIKE '%diaD%'");
    print_r($q->fetchAll(PDO::FETCH_ASSOC));
    $q2 = $pdo->query("SHOW TABLES LIKE 'supervisores_telefonos'");
    print_r($q2->fetchAll(PDO::FETCH_ASSOC));
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

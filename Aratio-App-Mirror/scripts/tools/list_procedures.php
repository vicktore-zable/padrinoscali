<?php
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';

try {
    $db = Database::getInstance();
    $sql = "SHOW PROCEDURE STATUS WHERE Db = 'u156469157_aratio_v1'";
    $res = $db->fetchAll($sql);
    foreach ($res as $row) {
        echo $row['Name'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

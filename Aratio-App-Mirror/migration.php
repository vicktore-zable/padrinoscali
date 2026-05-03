<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();

try {
    $db->exec("ALTER TABLE `jac_registros` ADD COLUMN `tipo_organizacion` VARCHAR(50) NOT NULL DEFAULT 'JAC' AFTER `nombre_jac`;");
    $db->exec("CREATE INDEX `idx_jac_tipo_org` ON `jac_registros`(`tipo_organizacion`);");
    echo "Migration Success";
} catch(PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
         echo "Migration Success (Already applied)";
    } else {
         echo "Error: " . $e->getMessage();
    }
}

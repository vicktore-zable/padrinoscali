<?php
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';
try {
    $db = Database::getInstance();
    $res = $db->fetchOne("SHOW CREATE VIEW v_colaboradores_completo");
    file_put_contents('view_definition.sql', $res['Create View']);
    echo "Done";
} catch (Exception $e) {
    echo $e->getMessage();
}

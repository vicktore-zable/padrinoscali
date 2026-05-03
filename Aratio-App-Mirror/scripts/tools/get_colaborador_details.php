<?php
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';

$db = Database::getInstance();
$documento = '1006054676';

try {
    $colab = $db->fetchOne("SELECT id, documento, telefono, telefono_whatsapp, email, nombres, apellidos FROM colaboradores WHERE documento = ?", [$documento]);
    if ($colab) {
        echo "Colaborador found:\n";
        print_r($colab);
    } else {
        echo "Colaborador NOT found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

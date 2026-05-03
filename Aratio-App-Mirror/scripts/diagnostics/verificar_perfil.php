<?php
header('Content-Type: application/json; charset=utf-8');
$TOKEN = 'aratio_migrar_2026';
if (($_GET['token'] ?? '') !== $TOKEN) { die('token'); }

try {
    $db = new PDO(
        'mysql:host=auth-db690.hstgr.io;port=3306;dbname=u156469157_aratio_v1;charset=utf8mb4',
        'u156469157_aratio_v1', '15zxCeBbvgsR',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"]
    );

    // Check perfil values
    $stmt = $db->query("SELECT id, nombres, apellidos, perfil, nivel_participacion, estado, genero, departamento, municipio FROM colaboradores ORDER BY id");
    $rows = $stmt->fetchAll();

    // Also check column definition
    $stmt = $db->query("SHOW COLUMNS FROM colaboradores WHERE Field = 'perfil'");
    $colDef = $stmt->fetch();

    // Fix if needed
    if (isset($_GET['fix']) && $_GET['fix'] == '1') {
        // Get source data
        $dbOrigen = new PDO(
            'mysql:host=auth-db690.hstgr.io;port=3306;dbname=u156469157_aratio;charset=utf8mb4',
            'u156469157_aratio', '15zxCeBbvgsR',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"]
        );

        $stmt = $dbOrigen->query("SELECT documento, perfil FROM colaboradores ORDER BY id");
        $fuente = $stmt->fetchAll();

        $fixed = 0;
        foreach ($fuente as $f) {
            $stmt = $db->prepare("UPDATE colaboradores SET perfil = ? WHERE documento = ?");
            $stmt->execute([$f['perfil'], $f['documento']]);
            if ($stmt->rowCount() > 0) $fixed++;
        }

        echo json_encode(['fixed' => $fixed, 'source_perfiles' => $fuente], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'column_definition' => $colDef,
        'colaboradores' => $rows
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

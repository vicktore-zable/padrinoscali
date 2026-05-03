<?php
/**
 * API para obtener filtros dinámicos de mod_elecciones
 */
require_once __DIR__ . '/../config/config.php';

$type = $_GET['type'] ?? '';
$anio = $_GET['anio'] ?? '';
$corporacion = $_GET['corporacion'] ?? '';
$municipio = $_GET['municipio'] ?? '';
$partido = $_GET['partido'] ?? '';

$db = getDB();
$response = [];

try {
    switch ($type) {
        case 'corporaciones':
            $stmt = $db->prepare("SELECT DISTINCT corporacion FROM mod_elecciones WHERE anio = ? ORDER BY corporacion ASC");
            $stmt->execute([$anio]);
            $response = $stmt->fetchAll(PDO::FETCH_COLUMN);
            break;

        case 'municipios':
            $stmt = $db->prepare("SELECT DISTINCT municipio FROM mod_elecciones WHERE anio = ? AND corporacion = ? ORDER BY municipio ASC");
            $stmt->execute([$anio, $corporacion]);
            $response = $stmt->fetchAll(PDO::FETCH_COLUMN);
            break;

        case 'partidos':
            $stmt = $db->prepare("SELECT DISTINCT partido FROM mod_elecciones WHERE anio = ? AND corporacion = ? AND municipio = ? ORDER BY partido ASC");
            $stmt->execute([$anio, $corporacion, $municipio]);
            $response = $stmt->fetchAll(PDO::FETCH_COLUMN);
            break;

        case 'candidatos':
            $stmt = $db->prepare("SELECT DISTINCT candidato FROM mod_elecciones WHERE anio = ? AND corporacion = ? AND municipio = ? AND partido = ? ORDER BY candidato ASC");
            $stmt->execute([$anio, $corporacion, $municipio, $partido]);
            $response = $stmt->fetchAll(PDO::FETCH_COLUMN);
            break;
            
        case 'anios':
        default:
            $response = $db->query("SELECT DISTINCT anio FROM mod_elecciones ORDER BY anio DESC")->fetchAll(PDO::FETCH_COLUMN);
            break;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $response]);

} catch (Exception $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

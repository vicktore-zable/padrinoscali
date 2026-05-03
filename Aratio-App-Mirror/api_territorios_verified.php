<?php
/**
 * API REST para Territorios - VERSIÓN ULTRA-SIMPLIFICADA
 * Garantiza siempre respuesta JSON válida
 */

// Headers primero
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejo de OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Inicializar respuesta por defecto
$response = ['success' => false, 'data' => [], 'message' => 'Error desconocido'];

try {
    // Cargar configuración
    $config_paths = [
        __DIR__ . '/config/config.php',
        dirname(__DIR__) . '/config/config.php'
    ];
    
    $configPath = null;
    foreach ($config_paths as $path) {
        if (file_exists($path)) {
            $configPath = $path;
            break;
        }
    }
    
    if (!$configPath) {
        throw new Exception('Config no encontrado');
    }
    
    require_once $configPath;
    
    // Obtener conexión
    $db = getDB();
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    // Obtener acción
    $accion = $_GET['accion'] ?? 'departamentos';
    
    // Ejecutar según acción
    switch ($accion) {
        case 'departamentos':
            $stmt = $db->query("SELECT DISTINCT departamento FROM territorios WHERE departamento IS NOT NULL AND departamento != '' ORDER BY departamento");
            $data = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $response = ['success' => true, 'data' => $data, 'count' => count($data)];
            break;
            
        case 'municipios':
            $departamento = $_GET['departamento'] ?? null;
            if (!$departamento) {
                throw new Exception('Departamento requerido');
            }
            $stmt = $db->prepare("SELECT DISTINCT municipio FROM territorios WHERE departamento = ? AND municipio IS NOT NULL AND municipio != '' ORDER BY municipio");
            $stmt->execute([$departamento]);
            $data = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $response = ['success' => true, 'data' => $data, 'count' => count($data)];
            break;
            
        case 'tipos_territorio':
            $departamento = $_GET['departamento'] ?? null;
            $municipio = $_GET['municipio'] ?? null;
            if (!$departamento || !$municipio) {
                throw new Exception('Departamento y municipio requeridos');
            }
            $stmt = $db->prepare("SELECT DISTINCT Tipo_territorio FROM territorios WHERE departamento = ? AND municipio = ? AND Tipo_territorio IS NOT NULL AND Tipo_territorio != '' ORDER BY Tipo_territorio");
            $stmt->execute([$departamento, $municipio]);
            $data = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $response = ['success' => true, 'data' => $data, 'count' => count($data)];
            break;
            
        case 'territorios':
            $departamento = $_GET['departamento'] ?? null;
            $municipio = $_GET['municipio'] ?? null;
            $tipo_territorio = $_GET['tipo_territorio'] ?? null;
            if (!$departamento || !$municipio || !$tipo_territorio) {
                throw new Exception('Departamento, municipio y tipo de territorio requeridos');
            }
            $stmt = $db->prepare("SELECT DISTINCT Territorio FROM territorios WHERE departamento = ? AND municipio = ? AND Tipo_territorio = ? AND Territorio IS NOT NULL AND Territorio != '' ORDER BY Territorio");
            $stmt->execute([$departamento, $municipio, $tipo_territorio]);
            $data = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $response = ['success' => true, 'data' => $data, 'count' => count($data)];
            break;
            
        case 'barrios':
            $departamento = $_GET['departamento'] ?? null;
            $municipio = $_GET['municipio'] ?? null;
            $tipo_territorio = $_GET['tipo_territorio'] ?? null;
            $territorio = $_GET['territorio'] ?? null;
            if (!$departamento || !$municipio || !$tipo_territorio || !$territorio) {
                throw new Exception('Todos los niveles anteriores son requeridos');
            }
            $stmt = $db->prepare("SELECT DISTINCT barrio FROM territorios WHERE departamento = ? AND municipio = ? AND Tipo_territorio = ? AND Territorio = ? AND barrio IS NOT NULL AND barrio != '' ORDER BY barrio");
            $stmt->execute([$departamento, $municipio, $tipo_territorio, $territorio]);
            $data = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $response = ['success' => true, 'data' => $data, 'count' => count($data)];
            break;
            
        default:
            throw new Exception('Acción no válida: ' . $accion);
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'data' => [],
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ];
}

// SIEMPRE devolver JSON válido
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;

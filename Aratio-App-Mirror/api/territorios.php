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
    $configPath = __DIR__ . '/../config/config.php';
    if (!file_exists($configPath)) {
        throw new Exception('Config no encontrado');
    }
    
    // Cargar configuración de forma segura
    // Nota: config.php ya tiene protecciones if(!function_exists)
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
            $stmt = $db->prepare("SELECT DISTINCT municipio, cod_mpio FROM territorios WHERE departamento = ? AND municipio IS NOT NULL AND municipio != '' ORDER BY municipio");
            $stmt->execute([$departamento]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            if (!$departamento || !$municipio) {
                throw new Exception('Departamento y municipio requeridos');
            }
            
            $sql = "SELECT DISTINCT Territorio FROM territorios WHERE departamento = ? AND municipio = ? AND Territorio IS NOT NULL AND Territorio != ''";
            $params = [$departamento, $municipio];
            
            if ($tipo_territorio) {
                $sql .= " AND Tipo_territorio = ?";
                $params[] = $tipo_territorio;
            }
            
            $sql .= " ORDER BY Territorio";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $response = ['success' => true, 'data' => $data, 'count' => count($data)];
            break;
            
        case 'barrios':
            $departamento = $_GET['departamento'] ?? null;
            $municipio = $_GET['municipio'] ?? null;
            $tipo_territorio = $_GET['tipo_territorio'] ?? null;
            $territorio = $_GET['territorio'] ?? null;
            if (!$departamento || !$municipio || !$territorio) {
                throw new Exception('Departamento, municipio y territorio requeridos');
            }
            
            $sql = "SELECT DISTINCT barrio FROM territorios WHERE departamento = ? AND municipio = ? AND Territorio = ? AND barrio IS NOT NULL AND barrio != ''";
            $params = [$departamento, $municipio, $territorio];
            
            if ($tipo_territorio) {
                $sql .= " AND Tipo_territorio = ?";
                $params[] = $tipo_territorio;
            }
            
            $sql .= " ORDER BY barrio";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $response = ['success' => true, 'data' => $data, 'count' => count($data)];
            break;

        case 'barrios_v2':
            $departamento = $_GET['departamento'] ?? null;
            $municipio = $_GET['municipio'] ?? null;
            $tipo_territorio = $_GET['tipo_territorio'] ?? null;
            $territorio = $_GET['territorio'] ?? null;
            
            if (!$departamento || !$municipio || !$tipo_territorio) {
                throw new Exception('Departamento, municipio y tipo son requeridos');
            }
            
            $sql = "SELECT id, barrio FROM territorios WHERE departamento = ? AND municipio = ? AND Tipo_territorio = ? AND barrio IS NOT NULL AND barrio != ''";
            $params = [$departamento, $municipio, $tipo_territorio];
            
            if ($territorio) {
                $sql .= " AND Territorio = ?";
                $params[] = $territorio;
            }
            
            $sql .= " GROUP BY barrio ORDER BY barrio";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ['success' => true, 'data' => $data, 'count' => count($data)];
            break;

        case 'puestos':
            $municipio = $_GET['municipio'] ?? null;
            $cod_mpio = $_GET['cod_mpio'] ?? null;
            $departamento = $_GET['departamento'] ?? null;
            
            // Normalización de departamento para la tabla puestos_votacion (Mismatch histórico)
            if ($departamento && strtoupper(trim($departamento)) === 'VALLE DEL CAUCA') {
                $departamento = 'VALLE';
            }

            if (!$municipio && !$cod_mpio) {
                throw new Exception('Municipio o Código de municipio requerido');
            }
            
            try {
                if ($cod_mpio) {
                    $stmt = $db->prepare("SELECT id, puesto, direccion FROM puestos_votacion WHERE cod_mpio = ? ORDER BY puesto");
                    $stmt->execute([$cod_mpio]);
                } else {
                    $sql = "SELECT id, puesto, direccion FROM puestos_votacion WHERE municipio = ?";
                    $params = [$municipio];
                    if ($departamento) {
                        $sql .= " AND departamento = ?";
                        $params[] = $departamento;
                    }
                    $sql .= " ORDER BY puesto";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                }
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $data = [];
            }
            
            $response = ['success' => true, 'data' => $data, 'count' => count($data)];
            break;
            
        case 'detalle_territorio':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID de territorio requerido');
            }
            $stmt = $db->prepare("SELECT id, departamento, municipio, cod_mpio, Tipo_territorio, Territorio, barrio FROM territorios WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) {
                throw new Exception('Territorio no encontrado');
            }
            $response = ['success' => true, 'data' => $data];
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

<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

// Cargar configuración centralizada del sistema
$configPath = __DIR__ . '/config/config.php';
if (!file_exists($configPath)) {
    $configPath = __DIR__ . '/../config/config.php';
}

try {
    if (file_exists($configPath)) {
        require_once $configPath;
        $pdo = getDB();
    } else {
        // Fallback manual si falla la carga del sistema
        $host = 'auth-db690.hstgr.io';
        $db   = 'u156469157_aratio_v1';
        $user = 'u156469157_aratio_v1';
        $pass = '15zxCeBbvgsR';
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    
    $municipio = $_GET['municipio'] ?? '';
    $comuna = $_GET['comuna'] ?? '';
    $search = $_GET['search'] ?? '';
    
    if ($municipio) {
        $sql = "SELECT id, puesto, direccion, comuna, latitud, longitud FROM puestos_votacion WHERE municipio = :municipio AND latitud IS NOT NULL";
        $params = [':municipio' => $municipio];
        
        if ($comuna) {
            $sql .= " AND (comuna = :comuna OR comuna LIKE :comuna_like)";
            $params[':comuna'] = $comuna;
            $num = ltrim(preg_replace('/[^0-9]/', '', $comuna), '0');
            $params[':comuna_like'] = $num ? "%$num%" : "%$comuna%";
        }

        if ($search) {
            $sql .= " AND (puesto LIKE :search OR direccion LIKE :search OR comuna LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY puesto ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        $sql = "SELECT id, departamento, municipio, puesto, comuna, latitud, longitud FROM puestos_votacion WHERE latitud IS NOT NULL";
        $params = [];
        if ($search) {
            $sql .= " AND (puesto LIKE :search OR municipio LIKE :search OR comuna LIKE :search)";
            $params[':search'] = "%$search%";
            $stmt = $pdo->prepare($sql . " LIMIT 1000");
            $stmt->execute($params);
        } else {
            $stmt = $pdo->query($sql . " LIMIT 1000");
        }
    }
    
    $data = $stmt->fetchAll();
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

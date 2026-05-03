<?php
require_once __DIR__ . '/../config/config.php';
// requireAuth(); // Permitir reportes públicos por ahora

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Extract fields
$id_campaña = $input['id_campaña'] ?? '02';
$id_colaborador = (int)($input['id_colaborador'] ?? 0);
$id_puesto = (int)($input['id_puesto'] ?? 0);
$id_mesa = (int)($input['id_mesa'] ?? 0);
$votos_nuevos = (int)($input['votos_nuevos'] ?? 0);

if (!$id_colaborador || !$id_puesto || !$id_mesa) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios']);
    exit;
}

try {
    $db = getDB();

    // Calculate current total for this mesa
    $stmtSum = $db->prepare("SELECT SUM(votos_nuevos) as previo FROM reportes_diaD WHERE id_puesto = ? AND id_mesa = ? AND id_campaña = ?");
    $stmtSum->execute([$id_puesto, $id_mesa, $id_campaña]);
    $previo = (int)($stmtSum->fetch(PDO::FETCH_ASSOC)['previo'] ?? 0);
    
    $votos_total = $previo + $votos_nuevos;

    // Meta Calculation (Simple arbitrary rule: Target 45 votes per mesa)
    // Less than 70% (31) = ROJO
    // 70% to 95% (32 to 42) = AMARILLO
    // > 95% (43+) = VERDE
    $meta = 45;
    $porcentaje = ($votos_total / $meta) * 100;

    $estado_semaforo = 'VERDE';
    if ($porcentaje < 70) {
        $estado_semaforo = 'ROJO';
    } elseif ($porcentaje <= 95) {
        $estado_semaforo = 'AMARILLO';
    }

    $stmt = $db->prepare("
        INSERT INTO reportes_diaD (id_campaña, id_colaborador, id_puesto, id_mesa, votos_nuevos, votos_total, estado_semaforo)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $id_campaña,
        $id_colaborador,
        $id_puesto,
        $id_mesa,
        $votos_nuevos,
        $votos_total,
        $estado_semaforo
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Reporte guardado exitosamente',
        'estado_semaforo' => $estado_semaforo,
        'votos_total' => $votos_total
    ]);

} catch (Exception $e) {
    error_log("Error diaD_reportes API: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al guardar el reporte']);
}

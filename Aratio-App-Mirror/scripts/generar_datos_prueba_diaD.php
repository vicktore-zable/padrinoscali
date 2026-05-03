<?php
require_once __DIR__ . '/config/config.php';

$municipios = [
    'Yumbo' => 5,
    'Cali' => 3,
    'Vijes' => 1,
    'Palmira' => 1
];

$db = getDB();

// Find a valid leader
$leaderStmt = $db->query("SELECT id FROM colaboradores ORDER BY RAND() LIMIT 1");
$leaderId = (int)($leaderStmt->fetch(PDO::FETCH_ASSOC)['id'] ?? 0);

if (!$leaderId) {
    die("No leaders found in colaboradores table.\n");
}

echo "Using Leader ID: $leaderId\n";

foreach ($municipios as $mun => $qty) {
    echo "Processing $mun ($qty reports)...\n";
    
    // Get puestos for this municipio
    $puestosStmt = $db->prepare("SELECT id FROM puestos_votacion WHERE municipio = ? LIMIT ?");
    $puestosStmt->execute([$mun, $qty]);
    $puestos = $puestosStmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($puestos)) {
        echo "No puestos found for $mun. Skipping.\n";
        continue;
    }
    
    for ($i = 0; $i < $qty; $i++) {
        $puestoId = (int)$puestos[$i % count($puestos)];
        $mesa = rand(1, 20);
        $votos = rand(10, 80);
        
        $id_campaña = '02';
        
        // Sum previous
        $stmtSum = $db->prepare("SELECT SUM(votos_nuevos) as previo FROM reportes_diaD WHERE id_puesto = ? AND id_mesa = ? AND id_campaña = ?");
        $stmtSum->execute([$puestoId, $mesa, $id_campaña]);
        $previo = (int)($stmtSum->fetch(PDO::FETCH_ASSOC)['previo'] ?? 0);
        
        $votos_total = $previo + $votos;
        
        // Semaphore
        $meta = 45;
        $porcentaje = ($votos_total / $meta) * 100;
        $estado_semaforo = 'VERDE';
        if ($porcentaje < 70) $estado_semaforo = 'ROJO';
        elseif ($porcentaje <= 95) $estado_semaforo = 'AMARILLO';
        
        // Insert
        $stmt = $db->prepare("INSERT INTO reportes_diaD (id_campaña, id_colaborador, id_puesto, id_mesa, votos_total, votos_nuevos, estado_semaforo, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $id_campaña,
            $leaderId,
            $puestoId,
            $mesa,
            $votos_total,
            $votos,
            $estado_semaforo
        ]);
        
        echo "Inserted report for $mun: Puesto $puestoId, Mesa $mesa, Votos +$votos (Total $votos_total)\n";
    }
}
echo "Finished test data generation.\n";

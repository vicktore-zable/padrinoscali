<?php
require_once __DIR__ . '/../config/config.php';

// Script temporal para generar datos de prueba solicitados
header('Content-Type: text/plain; charset=utf-8');

$municipios = [
    'Yumbo' => 5,
    'Cali' => 3,
    'Vijes' => 1,
    'Palmira' => 1
];

try {
    $db = getDB();
    
    // Obtener un líder al azar para los reportes
    $leaderStmt = $db->query("SELECT id FROM colaboradores WHERE perfil IN ('Lider Comunitario', 'Lider / Coordinador') LIMIT 1");
    $leaderId = $leaderStmt->fetch(PDO::FETCH_ASSOC)['id'] ?? null;
    
    if (!$leaderId) {
        // Fallback a cualquier colaborador
        $leaderId = $db->query("SELECT id FROM colaboradores LIMIT 1")->fetch(PDO::FETCH_ASSOC)['id'] ?? null;
    }
    
    if (!$leaderId) die("Error: No se encontró ningún líder en la tabla colaboradores.");

    echo "Generando reportes con Líder ID: $leaderId\n\n";

    foreach ($municipios as $mun => $qty) {
        echo "Procesando $mun ($qty registros)...\n";
        
        // Puestos de ese municipio
        $stmtP = $db->prepare("SELECT id, puesto FROM puestos_votacion WHERE municipio = ? LIMIT ?");
        $stmtP->execute([$mun, $qty]);
        $puestos = $stmtP->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($puestos)) {
            echo " - No se encontraron puestos en $mun. Saltando.\n";
            continue;
        }

        for ($i = 0; $i < $qty; $i++) {
            $puesto = $puestos[$i % count($puestos)];
            $puestoId = $puesto['id'];
            $mesa = rand(1, 15);
            $votos = rand(20, 100);
            $id_campaña = '02';

            // Cálculo de acumulado
            $stmtSum = $db->prepare("SELECT SUM(votos_nuevos) as previo FROM reportes_diaD WHERE id_puesto = ? AND id_mesa = ? AND id_campaña = ?");
            $stmtSum->execute([$puestoId, $mesa, $id_campaña]);
            $previo = (int)($stmtSum->fetch(PDO::FETCH_ASSOC)['previo'] ?? 0);
            $votos_total = $previo + $votos;

            // Semáforo
            $meta = 45;
            $porcentaje = ($votos_total / $meta) * 100;
            $estado = 'VERDE';
            if ($porcentaje < 70) $estado = 'ROJO';
            elseif ($porcentaje <= 95) $estado = 'AMARILLO';

            $stmtIns = $db->prepare("INSERT INTO reportes_diaD (id_campaña, id_colaborador, id_puesto, id_mesa, votos_nuevos, votos_total, estado_semaforo, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmtIns->execute([$id_campaña, $leaderId, $puestoId, $mesa, $votos, $votos_total, $estado]);

            echo " - OK: {$puesto['puesto']} | Mesa $mesa | +$votos votos\n";
        }
    }

    echo "\n¡Prueba completada con éxito!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

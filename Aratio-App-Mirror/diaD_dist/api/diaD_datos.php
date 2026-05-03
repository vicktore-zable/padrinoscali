<?php
require_once __DIR__ . '/../config/config.php';
// requireAuth(); // Acceso público a datos base por ahora

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$db = getDB();

try {
    switch ($action) {
        case 'puestos':
            // Fetch Puestos for "Yumbo" or the active territory
            // As per specs: puestos_votacion: id, nombre, municipio, coordenadas, mesas[]
            // We'll just fetch all or where municipio='Yumbo'
            $stmt = $db->query("SELECT id, puesto as nombre, municipio, latitud, longitud FROM puestos_votacion ORDER BY puesto ASC");
            $puestos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $puestos]);
            break;

        case 'mesas':
            $puesto_id = (int)($_GET['puesto_id'] ?? 0);
            if (!$puesto_id) {
                echo json_encode(['success' => false, 'message' => 'ID puesto requerido']);
                exit;
            }
            
            // "mesas[]" is likely a JSON array or comma-separated list in puestos_votacion
            // Let's decode it or generate it if it's text.
            $stmt = $db->prepare("SELECT mesas FROM puestos_votacion WHERE id = ?");
            $stmt->execute([$puesto_id]);
            $puesto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$puesto) {
                echo json_encode(['success' => false, 'message' => 'Puesto no encontrado']);
                exit;
            }
            
            // mesas[] can be JSON or a simple string "1,2,3"
            $mesasRaw = $puesto['mesas'];
            $mesasData = [];
            
            if ($mesasRaw) {
                $decoded = json_decode($mesasRaw, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $m) {
                        // Sometimes it's a list of integers, sometimes list of objects
                        if (is_array($m) && isset($m['numero'])) {
                            $mesasData[] = ['id_mesa' => $m['numero'], 'numero' => $m['numero']];
                        } else {
                            $mesasData[] = ['id_mesa' => $m, 'numero' => $m];
                        }
                    }
                } else {
                    // Fallback to comma separated
                    $parts = explode(',', $mesasRaw);
                    foreach ($parts as $p) {
                        $m = trim($p);
                        if ($m !== '') {
                            $mesasData[] = ['id_mesa' => $m, 'numero' => $m];
                        }
                    }
                }
            } else {
                // Generar fallback dummy si esta vacia para pruebas, asumiendo max 5 mesas por puesto
                for($i=1; $i<=5; $i++) {
                    $mesasData[] = ['id_mesa' => $i, 'numero' => $i];
                }
            }
            
            echo json_encode(['success' => true, 'data' => $mesasData]);
            break;

        case 'buscar_lider':
            $q = trim($_GET['q'] ?? '');
            if (strlen($q) < 3) {
                echo json_encode(['success' => true, 'data' => []]);
                exit;
            }
            
            // Schema: colaboradores: id, documento, nombres, apellidos, telefono
            $stmt = $db->prepare("
                SELECT id, documento as cedula, CONCAT(nombres, ' ', apellidos) as nombre_completo, telefono,
                       NULL as supervisor_telefono, 'Sin Asignar' as nombre_supervisor
                FROM colaboradores
                WHERE (CONCAT(nombres, ' ', apellidos) LIKE ? OR documento LIKE ?)
                  AND (perfil IN ('Lider Comunitario', 'Lider / Coordinador'))
                LIMIT 10
            ");
            $term = "%$q%";
            $stmt->execute([$term, $term]);
            $lideres = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Map names if null
            foreach ($lideres as &$lider) {
                if (!$lider['supervisor_telefono']) {
                    $lider['supervisor_telefono'] = '';
                    $lider['nombre_supervisor'] = 'Sin Asignar';
                }
            }
            
            echo json_encode(['success' => true, 'data' => $lideres]);
            break;

        case 'dashboard':
            // Verify roles and auth for dashboard
            requireAuth();
            $user = getSessionUser();
            $rolesDiaD_Dashboard = ['supervisor_diad', 'admin_diad', 'supervisor', 'admin', 'super-admin'];
            /*
            if (!in_array($user['rol'], $rolesDiaD_Dashboard)) {
                echo json_encode(['success' => false, 'message' => 'No autorizado']);
                exit;
            }
            */

            // Latest report ID per mesa (grouped by puesto AND mesa number)
            $latest_ids_subquery = "SELECT MAX(id) FROM reportes_diaD GROUP BY id_puesto, id_mesa";

            // Calculated Totals by summing all votos_nuevos per mesa
            $sums_stmt = $db->query("
                SELECT id_puesto, id_mesa, SUM(votos_nuevos) as votos_calculados
                FROM reportes_diaD
                GROUP BY id_puesto, id_mesa
            ");
            $sums_map = [];
            while ($s = $sums_stmt->fetch(PDO::FETCH_ASSOC)) {
                $sums_map[$s['id_puesto'] . '-' . $s['id_mesa']] = (int)$s['votos_calculados'];
            }

            // kpis: sum of ALL votos_nuevos
            $stmt_kpis = $db->query("SELECT SUM(votos_nuevos) as total_votos FROM reportes_diaD");
            $total_v_res = $stmt_kpis->fetch(PDO::FETCH_ASSOC);

            // Coverage KPIs (Count unique mesas that have at least one report)
            $stmt_coverage = $db->query("
                SELECT COUNT(DISTINCT id_puesto, id_mesa) as mesas_reportadas,
                       COUNT(DISTINCT id_puesto) as puestos_activos
                FROM reportes_diaD
            ");
            $cov_res = $stmt_coverage->fetch(PDO::FETCH_ASSOC);

            $kpis = [
                'total_votos' => (int)($total_v_res['total_votos'] ?? 0),
                'mesas_reportadas' => (int)($cov_res['mesas_reportadas'] ?? 0),
                'puestos_activos' => (int)($cov_res['puestos_activos'] ?? 0)
            ];

            // Alertas (estado_semaforo = 'ROJO' in the LATEST report)
            $stmt_alertas = $db->query("
                SELECT r.id_puesto, r.id_mesa, r.votos_total, p.puesto as puesto_nombre, p.municipio
                FROM reportes_diaD r
                LEFT JOIN puestos_votacion p ON r.id_puesto = p.id
                WHERE r.estado_semaforo = 'ROJO'
                AND r.id IN ($latest_ids_subquery)
            ");
            $alertas = $stmt_alertas->fetchAll(PDO::FETCH_ASSOC);
            foreach ($alertas as &$a) {
                $key = $a['id_puesto'] . '-' . $a['id_mesa'];
                $a['votos_total'] = $sums_map[$key] ?? 0; // Use calculated total instead of reported total
            }
            
            // Map stats for Leaflet marker + List
            $stmt_all_puestos = $db->query("
                SELECT p.id, p.puesto as nombre, p.municipio, p.latitud, p.longitud, 
                       COUNT(DISTINCT r.id_mesa) as mesas_activas
                FROM puestos_votacion p
                INNER JOIN reportes_diaD r ON p.id = r.id_puesto
                WHERE r.id IN ($latest_ids_subquery)
                GROUP BY p.id
            ");
            $puestosRaw = $stmt_all_puestos->fetchAll(PDO::FETCH_ASSOC);
            $puestos_stats = [];
            $muns_map = [];

            // Calculate votes per puesto by summing relevant mesas
            $puesto_votes_map = [];
            $stmt_pvotes = $db->query("SELECT id_puesto, SUM(votos_nuevos) as p_votos FROM reportes_diaD GROUP BY id_puesto");
            while ($pv = $stmt_pvotes->fetch(PDO::FETCH_ASSOC)) {
                $puesto_votes_map[$pv['id_puesto']] = (int)$pv['p_votos'];
            }

            foreach ($puestosRaw as $pr) {
                $lat = $pr['latitud'] ? (float)$pr['latitud'] : null;
                $lng = $pr['longitud'] ? (float)$pr['longitud'] : null;

                $estado = 'OK';
                if (array_filter($alertas, fn($a) => $a['id_puesto'] == $pr['id'])) {
                    $estado = 'ALERTA';
                }

                $total_v = $puesto_votes_map[$pr['id']] ?? 0;

                $puestos_stats[] = [
                    'id' => $pr['id'],
                    'nombre' => $pr['nombre'],
                    'municipio' => $pr['municipio'],
                    'latitud' => $lat,
                    'longitud' => $lng,
                    'total_votos' => $total_v,
                    'mesas_activas' => (int)$pr['mesas_activas'],
                    'estado_general' => $estado
                ];

                $m = $pr['municipio'] ?: 'No Definido';
                if (!isset($muns_map[$m])) {
                    $muns_map[$m] = ['nombre' => $m, 'total_votos' => 0, 'puestos_activos' => 0];
                }
                $muns_map[$m]['total_votos'] += $total_v;
                $muns_map[$m]['puestos_activos']++;
            }

            // Mesa by Mesa breakdown
            $stmt_mesas = $db->query("
                SELECT r.id_puesto, p.puesto as puesto_nombre, p.municipio, r.id_mesa, r.votos_nuevos as ultimo_reporte, r.votos_total as reporte_total_lider, r.estado_semaforo, r.timestamp
                FROM reportes_diaD r
                LEFT JOIN puestos_votacion p ON r.id_puesto = p.id
                WHERE r.id IN ($latest_ids_subquery)
                ORDER BY p.municipio, p.puesto, r.id_mesa
            ");
            $mesas_stats = $stmt_mesas->fetchAll(PDO::FETCH_ASSOC);
            foreach ($mesas_stats as &$ms) {
                $key = $ms['id_puesto'] . '-' . $ms['id_mesa'];
                $ms['votos_total'] = $sums_map[$key] ?? 0; // Calculated Total
            }

            echo json_encode([
                'success' => true, 
                'kpis' => $kpis, 
                'alertas' => $alertas,
                'puestos_stats' => $puestos_stats,
                'municipios_stats' => array_values($muns_map),
                'mesas_stats' => $mesas_stats
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }

} catch (Exception $e) {
    error_log("Error diaD_datos API: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

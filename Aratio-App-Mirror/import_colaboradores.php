<?php
/**
 * Importador de Colaboradores desde API Externa
 * Sincroniza datos desde colaboradores.aratio.mrmtech.net
 */

require_once 'config/config.php';

// Solo permitir admin
requireAuth();
$user = getSessionUser();
if ($user['rol'] !== 'admin' && $user['rol'] !== 'superadmin') {
    jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
}

// Configuración
$apiUrl = 'https://colaboradores.aratio.mrmtech.net/api/v1/colaboradores';
$apiKey = 'aratio_prod_secure_token_5d_2025';
$targetCampanaId = 2; // CONCEJO DE YUMBO 2028-2031

try {
    // 1. Obtener datos de API Externa
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . $apiKey,
        'Accept: application/json'
    ]);
    // Ignorar verificación SSL si es necesaria (solo para evitar bloqueos por certs)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        throw new Exception('Error CURL: ' . curl_error($ch));
    }
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Error API Externa (Código $httpCode): " . substr($response, 0, 100));
    }

    $json = json_decode($response, true);
    if (!$json || !isset($json['data'])) {
        throw new Exception('Respuesta API inválida');
    }

    $colaboradoresExternos = $json['data'];
    $stats = ['procesados' => 0, 'insertados' => 0, 'actualizados' => 0, 'errores' => 0];
    
    $db = getDB();

    // Prepara sentencias
    $stmtCheck = $db->prepare("SELECT id FROM colaboradores WHERE documento = ? AND campana_id = ?");
    
    $sqlInsert = "INSERT INTO colaboradores (
                    campana_id, nombres, apellidos, documento, telefono, email, 
                    departamento, municipio, puesto_votacion, mesa_votacion, 
                    lider_directo, perfil, nivel_participacion, created_at, dato_potencial
                  ) VALUES (
                    ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, 
                    ?, ?, ?, NOW(), 1
                  )";
    $stmtInsert = $db->prepare($sqlInsert);

    $sqlUpdate = "UPDATE colaboradores SET 
                    nombres = ?, apellidos = ?, telefono = ?, email = ?, 
                    departamento = ?, municipio = ?, puesto_votacion = ?, mesa_votacion = ?, 
                    lider_directo = ?, perfil = ?, nivel_participacion = ?, updated_at = NOW()
                  WHERE id = ?";
    $stmtUpdate = $db->prepare($sqlUpdate);

    foreach ($colaboradoresExternos as $ext) {
        $stats['procesados']++;
        
        try {
            // Mapeo de datos (Ajustar según respuesta real de la API)
            // Asumimos estructura estándar, ajustar si falla
            $doc = $ext['documento'] ?? $ext['cedula'] ?? null;
            if (!$doc) continue;

            $params = [
                sanitize($ext['nombres'] ?? ''),
                sanitize($ext['apellidos'] ?? ''),
                sanitize($ext['telefono'] ?? ''),
                sanitize($ext['email'] ?? ''),
                sanitize($ext['departamento'] ?? 'Valle del Cauca'),
                sanitize($ext['municipio'] ?? 'Yumbo'),
                sanitize($ext['puesto_votacion'] ?? ''),
                sanitize($ext['mesa_votacion'] ?? ''),
                sanitize($ext['lider_referente'] ?? ''), // Ajustar key
                'Simpatizante', // Perfil forzado
                'Simpatizante'  // Nivel pasivo
            ];

            // Verificar existencia
            $stmtCheck->execute([$doc, $targetCampanaId]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Actualizar
                // Añadir params al final para WHERE
                $updateParams = $params;
                $updateParams[] = $existing['id'];
                $stmtUpdate->execute($updateParams);
                $stats['actualizados']++;
            } else {
                // Insertar
                // Añadir params al inicio: campana_id, nombres, ... documento, ...
                $insertParams = array_merge(
                    [$targetCampanaId], 
                    array_slice($params, 0, 2), // nombres, apellidos
                    [$doc], // documento
                    array_slice($params, 2) // resto
                );
                
                $stmtInsert->execute($insertParams);
                $stats['insertados']++;
            }

        } catch (Exception $e) {
            $stats['errores']++;
            // Log error silencioso
        }
    }

    echo json_encode(['success' => true, 'stats' => $stats, 'message' => "Proceso completado. Nuevos: {$stats['insertados']}, Actualizados: {$stats['actualizados']}"]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

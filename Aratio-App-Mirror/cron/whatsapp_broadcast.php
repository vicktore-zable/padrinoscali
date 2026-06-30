<?php
/**
 * ALAS - Procesador de Broadcast WhatsApp
 * Ejecutar cada 1 minuto via cron Hostinger
 * 
 * * * * * * php /home/u577647812/domains/padrinoscali.org/public_html/aratio/cron/whatsapp_broadcast.php
 */

require_once __DIR__ . '/../config/config.php';

if (php_sapi_name() !== 'cli') {
    die('Este script solo se ejecuta desde CLI');
}

$db = getDB();
$api = new WhatsAppCloudApi();
$procesados = 0;

$stmt = $db->query("
    SELECT wbq.*, c.nombres, c.apellidos, c.telefono_whatsapp, c.telefono,
           wp.codigo, wp.cuerpo, wp.variables
    FROM whatsapp_broadcast_queue wbq
    JOIN colaboradores c ON wbq.colaborador_id = c.id
    LEFT JOIN whatsapp_plantillas wp ON wbq.plantilla_codigo = wp.codigo
    WHERE wbq.estado = 'pending'
      AND (wbq.programado_para IS NULL OR wbq.programado_para <= NOW())
    ORDER BY wbq.id ASC
    LIMIT 50
");

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($items as $item) {
    try {
        $db->prepare("UPDATE whatsapp_broadcast_queue SET estado = 'processing' WHERE id = ?")
           ->execute([$item['id']]);

        $telefono = $item['telefono_whatsapp'] ?: $item['telefono'];
        if (empty($telefono)) {
            $db->prepare("UPDATE whatsapp_broadcast_queue SET estado = 'failed', error = 'Sin teléfono' WHERE id = ?")
               ->execute([$item['id']]);
            continue;
        }

        $variables = json_decode($item['variables_json'], true) ?? [];
        $resultado = $api->sendTemplate($telefono, $item['plantilla_codigo'], array_values($variables));

        if ($resultado['success']) {
            $db->prepare("
                UPDATE whatsapp_broadcast_queue 
                SET estado = 'sent', wa_message_id = ?, enviado_en = NOW() 
                WHERE id = ?
            ")->execute([$resultado['message_id'] ?? '', $item['id']]);

            if ($item['colaborador_id'] > 0 && class_exists('ActivityLogger')) {
                ActivityLogger::log(
                    (int)$item['colaborador_id'],
                    'whatsapp_enviado',
                    "Broadcast: template {$item['plantilla_codigo']}",
                    ['plantilla' => $item['plantilla_codigo'], 'broadcast_id' => $item['id']],
                    'whatsapp_broadcast_queue',
                    (int)$item['id']
                );
            }
        } else {
            $reintentos = (int)$item['reintentos'] + 1;
            if ($reintentos >= 3) {
                $db->prepare("UPDATE whatsapp_broadcast_queue SET estado = 'failed', error = ?, reintentos = ? WHERE id = ?")
                   ->execute([$resultado['error'] ?? 'Error desconocido', $reintentos, $item['id']]);
            } else {
                $db->prepare("UPDATE whatsapp_broadcast_queue SET estado = 'pending', reintentos = ?, programado_para = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id = ?")
                   ->execute([$reintentos, $item['id']]);
            }
        }

        $procesados++;
    } catch (Throwable $e) {
        error_log("ALAS Broadcast error: " . $e->getMessage());
        $db->prepare("UPDATE whatsapp_broadcast_queue SET estado = 'failed', error = ? WHERE id = ?")
           ->execute([$e->getMessage(), $item['id']]);
    }
}

echo date('[Y-m-d H:i:s]') . " ALAS Broadcast: Procesados {$procesados} mensajes\n";

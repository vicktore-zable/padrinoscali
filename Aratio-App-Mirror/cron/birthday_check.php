<?php

require_once __DIR__ . '/../config/config.php';

$db = getDB();

$hoy = date('m-d');

$stmt = $db->prepare("
    SELECT c.id, c.nombres, c.apellidos, c.telefono_whatsapp, c.perfil, c.telefono, c.campana_id
    FROM colaboradores c
    WHERE DATE_FORMAT(c.fecha_nacimiento, '%m-%d') = ?
      AND c.telefono_whatsapp IS NOT NULL AND c.telefono_whatsapp != ''
      AND (c.estado IS NULL OR c.estado NOT IN ('inactivo','Inactivo'))
");
$stmt->execute([$hoy]);
$cumpleaneros = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cumpleaneros)) {
    error_log("Birthday cron: Sin cumpleañeros hoy ({$hoy})");
    exit(0);
}

$api = new WhatsAppApi();

foreach ($cumpleaneros as $p) {
    try {
        $telefono = $p['telefono_whatsapp'] ?: $p['telefono'];
        if (empty($telefono)) {
            $api->log($db, $p, '', '', 'sin_whatsapp', 'Sin teléfono disponible');
            continue;
        }

        $templateName = 'birthday_' . strtolower(str_replace(' ', '_', trim($p['perfil'] ?? 'default')));
        $template = $api->getTemplate($p['perfil']);
        $mensaje = $api->fillTemplate($template, $p['nombres']);

        $telefonoFormateado = $api->formatPhone($telefono);

        $resultado = $api->send($telefonoFormateado, $mensaje);

        if ($resultado['success']) {
            $api->log($db, $p, $templateName, $mensaje, 'enviado', null);
            error_log("Birthday cron: Enviado a {$p['nombres']} ({$p['id']}) - tel: {$telefonoFormateado}");
        } else {
            $api->log($db, $p, $templateName, $mensaje, 'fallido', $resultado['error']);
            error_log("Birthday cron: Falló envío a {$p['nombres']} ({$p['id']}): " . $resultado['error']);
        }
    } catch (Exception $e) {
        $api->log($db, $p, $templateName ?? '', $mensaje ?? '', 'fallido', $e->getMessage());
        error_log("Birthday cron error [{$p['id']}]: " . $e->getMessage());
    }
}

error_log("Birthday cron: Procesados " . count($cumpleaneros) . " cumpleañeros");

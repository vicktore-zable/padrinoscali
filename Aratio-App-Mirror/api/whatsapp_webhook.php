<?php

require_once __DIR__ . '/../config/config.php';

$api = new WhatsAppCloudApi();

// Webhook verification (GET) — WhatsApp Cloud API handshake
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    $result = $api->verifyWebhook($mode, $token, $challenge);
    if ($result !== null) {
        header('Content-Type: text/plain');
        echo $result;
        exit;
    }

    http_response_code(403);
    echo 'Error de verificación';
    exit;
}

// Incoming messages (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        exit;
    }

    $messages = $api->processWebhook($input);
    $db = getDB();

    foreach ($messages as $msg) {
        try {
            $phone = $api->formatPhone($msg['from']);

            $stmt = $db->prepare("
                SELECT id FROM whatsapp_conversaciones WHERE wa_phone = ?
            ");
            $stmt->execute([$phone]);
            $conv = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($conv) {
                $convId = $conv['id'];
            } else {
                $stmt = $db->prepare("
                    SELECT id, nombres, apellidos FROM colaboradores 
                    WHERE (telefono_whatsapp = ? OR telefono = ?) 
                      AND (estado IS NULL OR estado NOT IN ('inactivo','Inactivo'))
                    LIMIT 1
                ");
                $stmt->execute([$msg['from'], $msg['from']]);
                $colab = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $db->prepare("
                    INSERT INTO whatsapp_conversaciones (colaborador_id, wa_phone, estado)
                    VALUES (?, ?, 'activa')
                ");
                $stmt->execute([$colab ? $colab['id'] : 0, $phone]);
                $convId = (int)$db->lastInsertId();
            }

            $stmt = $db->prepare("
                INSERT INTO whatsapp_mensajes (conversacion_id, wa_message_id, direccion, tipo, contenido, timestamp)
                VALUES (?, ?, 'recibido', ?, ?, FROM_UNIXTIME(?))
            ");
            $stmt->execute([
                $convId,
                $msg['wa_message_id'],
                $msg['type'],
                $msg['text'],
                $msg['timestamp']
            ]);

            $stmt = $db->prepare("
                UPDATE whatsapp_conversaciones 
                SET ultimo_mensaje = ?, ultimo_tipo = 'recibido', ultimo_timestamp = NOW(), unread = unread + 1
                WHERE id = ?
            ");
            $stmt->execute([$msg['text'], $convId]);

            $stmt = $db->prepare("SELECT colaborador_id FROM whatsapp_conversaciones WHERE id = ?");
            $stmt->execute([$convId]);
            $colabId = (int)$stmt->fetchColumn();

            if ($colabId > 0) {
                if (class_exists('ActivityLogger')) {
                    ActivityLogger::log(
                        $colabId,
                        'whatsapp_recibido',
                        "WhatsApp: " . mb_substr($msg['text'], 0, 100),
                        ['contenido' => $msg['text'], 'tipo' => $msg['type']],
                        'whatsapp_mensajes',
                        null
                    );
                }
            }

        } catch (Throwable $e) {
            error_log("ALAS Webhook error: " . $e->getMessage());
        }
    }

    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

http_response_code(405);
echo 'Method Not Allowed';

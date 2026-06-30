<?php
/**
 * Operación Mandami — Escáner de comentarios
 * Ejecutar cada 5 minutos desde Hostinger:
 * php /home/u577647812/domains/padrinoscali.org/public_html/aratio/cron/mandami_scan.php
 */

require_once __DIR__ . '/../config/config.php';

$db = getDB();
require_once __DIR__ . '/../includes/MessengerBot.php';

$bot = new MessengerBot($db);

if (!$bot->isConfigured()) {
    echo "[" . date('Y-m-d H:i:s') . "] Facebook no configurado. Configura FB_PAGE_TOKEN\n";
    exit(1);
}

$start = microtime(true);
$result = $bot->scanComments(24);

$elapsed = round(microtime(true) - $start, 2);
$status = empty($result['errors']) ? 'OK' : 'ERRORS';

echo "[{$result['scanned']} comentarios escaneados] = {$result['matched']} match keywords → {$result['dm_sent']} DM enviados | {$status} | {$elapsed}s\n";

if (!empty($result['errors'])) {
    foreach (array_slice($result['errors'], 0, 5) as $err) {
        echo "  ERROR: $err\n";
    }
}

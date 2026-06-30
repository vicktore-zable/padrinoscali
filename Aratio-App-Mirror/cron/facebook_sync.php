<?php
/**
 * Cron: Facebook Sync
 * Ejecutar cada 6 horas desde Hostinger
 * php /home/u577647812/domains/padrinoscali.org/public_html/aratio/cron/facebook_sync.php
 */

require_once __DIR__ . '/../config/config.php';

$start = microtime(true);

try {
    $db = getDB();
    require_once __DIR__ . '/../includes/FacebookApi.php';

    $fb = new FacebookApi($db);

    if (!$fb->isConfigured()) {
        echo "[Facebook Sync] ERROR: Facebook no configurado (token vacío)\n";
        exit(1);
    }

    $since = date('Y-m-d', strtotime('-7 days'));
    $result = $fb->syncAll(50);

    $elapsed = round(microtime(true) - $start, 2);

    echo "[Facebook Sync] " . date('Y-m-d H:i:s') . "\n";
    echo "  Posts: {$result['posts']}\n";
    echo "  Reacciones: {$result['reactions']}\n";
    echo "  Comentarios: {$result['comments']}\n";
    echo "  Errores: " . count($result['errors']) . "\n";
    if (!empty($result['errors'])) {
        foreach (array_slice($result['errors'], 0, 5) as $e) {
            echo "  - $e\n";
        }
    }
    echo "  Time: {$elapsed}s\n";
    echo "[Facebook Sync] OK\n";

} catch (Exception $e) {
    echo "[Facebook Sync] ERROR: " . $e->getMessage() . "\n";
    error_log("Facebook Sync cron error: " . $e->getMessage());
    exit(1);
}

<?php
require_once __DIR__ . '/../config/config.php';

$start = microtime(true);
$db = getDB();

require_once __DIR__ . '/../includes/EmailCampaigns.php';

$resultado = EmailCampaigns::procesarCola(30);

$elapsed = round(microtime(true) - $start, 2);
echo "[" . date('Y-m-d H:i:s') . "] Email processor: {$resultado['enviados']} enviados, {$resultado['fallidos']} fallidos en {$elapsed}s\n";
if (!empty($resultado['errores'])) {
    foreach ($resultado['errores'] as $err) {
        echo "  Error: $err\n";
    }
}

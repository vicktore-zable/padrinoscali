<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'health':
            jsonResponse(['success' => true, 'data' => getHealthStatus()]);
            break;
        case 'test_fb':
            jsonResponse(['success' => true, 'data' => testFacebook()]);
            break;
        case 'test_ig':
            jsonResponse(['success' => true, 'data' => testInstagram()]);
            break;
        case 'test_db':
            jsonResponse(['success' => true, 'data' => testDatabase()]);
            break;
        case 'test_email':
            jsonResponse(['success' => true, 'data' => testEmail()]);
            break;
        case 'test_whatsapp':
            jsonResponse(['success' => true, 'data' => testWhatsApp()]);
            break;
        case 'logs':
            $lines = (int)($_GET['lines'] ?? 50);
            jsonResponse(['success' => true, 'data' => getRecentLogs($lines)]);
            break;
        case 'install_migrations':
            jsonResponse(['success' => true, 'data' => runPendingMigrations()]);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}

function getHealthStatus(): array
{
    $db = getDB();

    // PHP version
    $phpVersion = phpversion();

    // DB connection
    $dbOk = false;
    $dbInfo = '';
    try {
        $db->query("SELECT 1");
        $dbOk = true;
        $dbInfo = $db->getAttribute(PDO::ATTR_SERVER_VERSION);
    } catch (Exception $e) {
        $dbInfo = $e->getMessage();
    }

    // Required extensions
    $extensions = [
        'curl' => extension_loaded('curl'),
        'pdo_mysql' => extension_loaded('pdo_mysql'),
        'json' => extension_loaded('json'),
        'mbstring' => extension_loaded('mbstring'),
        'gd' => extension_loaded('gd'),
        'openssl' => extension_loaded('openssl'),
    ];

    // Config status
    $configs = [
        'facebook' => [
            'label' => 'Facebook Graph API',
            'configured' => defined('FB_PAGE_TOKEN') && !empty(FB_PAGE_TOKEN),
            'page_id' => defined('FB_PAGE_ID') ? FB_PAGE_ID : '(no definido)',
            'has_token' => defined('FB_PAGE_TOKEN') && !empty(FB_PAGE_TOKEN),
        ],
        'instagram_graph' => [
            'label' => 'Instagram Graph API',
            'configured' => defined('IG_BUSINESS_ID') && !empty(IG_BUSINESS_ID) && defined('FB_PAGE_TOKEN') && !empty(FB_PAGE_TOKEN),
            'business_id' => defined('IG_BUSINESS_ID') ? IG_BUSINESS_ID : '(no definido)',
        ],
        'email' => [
            'label' => 'SMTP Email',
            'configured' => defined('SMTP_HOST') && !empty(SMTP_HOST) && defined('SMTP_PASS') && !empty(SMTP_PASS) && SMTP_PASS !== 'tu_password_email',
            'host' => defined('SMTP_HOST') ? SMTP_HOST : '(no definido)',
        ],
        'whatsapp_wati' => [
            'label' => 'WhatsApp (WATI)',
            'configured' => defined('WATI_API_KEY') && !empty(WATI_API_KEY),
        ],
        'whatsapp_meta' => [
            'label' => 'WhatsApp Cloud API',
            'configured' => defined('META_WHATSAPP_TOKEN') && !empty(META_WHATSAPP_TOKEN),
        ],
    ];

    // DB table counts
    $tables = [];
    foreach ([
        'colaboradores' => 'Colaboradores',
        'fb_posts' => 'FB Posts',
        'fb_reactions' => 'FB Reacciones',
        'fb_comments' => 'FB Comentarios',
        'ig_menciones' => 'IG Menciones',
        'ig_comments' => 'IG Comentarios',
        'actividad_colaborador' => 'Actividad',
        'whatsapp_mensajes' => 'WhatsApp Mensajes',
    ] as $table => $label) {
        try {
            $count = (int)$db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            $tables[] = ['table' => $table, 'label' => $label, 'count' => $count, 'ok' => true];
        } catch (Exception $e) {
            $tables[] = ['table' => $table, 'label' => $label, 'count' => 0, 'ok' => false];
        }
    }

    // Instagram legacy
    $igJsonPath = __DIR__ . '/../storage/maestro_instagram.json';
    $igJsonExists = file_exists($igJsonPath);
    $igJsonSize = $igJsonExists ? filesize($igJsonPath) : 0;

    // Last sync times
    $lastSync = [];
    $syncFile = __DIR__ . '/../storage/instagram_sync_status.json';
    if (file_exists($syncFile)) {
        $syncData = json_decode(file_get_contents($syncFile), true);
        $lastSync['instagram'] = $syncData['last_sync'] ?? 'Nunca';
    }
    // Check most recent fb_post
    try {
        $lastFbPost = $db->query("SELECT MAX(fecha) FROM fb_posts")->fetchColumn();
        $lastSync['facebook'] = $lastFbPost ?: 'Nunca';
    } catch (Exception $e) {
        $lastSync['facebook'] = 'Error';
    }

    return [
        'php_version' => $phpVersion,
        'db' => ['ok' => $dbOk, 'info' => $dbInfo, 'name' => DB_NAME],
        'extensions' => $extensions,
        'configs' => $configs,
        'tables' => $tables,
        'ig_json' => ['exists' => $igJsonExists, 'size_kb' => round($igJsonSize / 1024, 1)],
        'last_sync' => $lastSync,
        'app_url' => APP_URL,
        'app_env' => APP_ENV,
        'app_subpath' => APP_SUBPATH,
    ];
}

function testFacebook(): array
{
    require_once __DIR__ . '/../includes/FacebookApi.php';

    if (!defined('FB_PAGE_TOKEN') || empty(FB_PAGE_TOKEN)) {
        return ['ok' => false, 'message' => 'FB_PAGE_TOKEN no configurado'];
    }
    if (!defined('FB_PAGE_ID') || empty(FB_PAGE_ID)) {
        return ['ok' => false, 'message' => 'FB_PAGE_ID no configurado'];
    }

    $db = getDB();
    $fb = new FacebookApi($db);

    try {
        $posts = $fb->fetchPosts(3);
        $count = count($posts);
        if ($count > 0) {
            return ['ok' => true, 'message' => "{$count} posts obtenidos correctamente", 'data' => array_map(fn($p) => [
                'id' => $p['fb_post_id'],
                'texto' => mb_substr($p['texto'], 0, 100),
                'fecha' => $p['fecha'],
            ], $posts)];
        }
        return ['ok' => true, 'message' => 'Conexión exitosa, sin posts recientes'];
    } catch (Exception $e) {
        return ['ok' => false, 'message' => $e->getMessage()];
    }
}

function testInstagram(): array
{
    require_once __DIR__ . '/../includes/InstagramGraphApi.php';

    if (!defined('IG_BUSINESS_ID') || empty(IG_BUSINESS_ID)) {
        return ['ok' => false, 'message' => 'IG_BUSINESS_ID no configurado'];
    }
    if (!defined('FB_PAGE_TOKEN') || empty(FB_PAGE_TOKEN)) {
        return ['ok' => false, 'message' => 'FB_PAGE_TOKEN no configurado'];
    }

    $db = getDB();
    $ig = new InstagramGraphApi($db);

    try {
        $media = $ig->fetchMedia(3);
        $count = count($media);
        if ($count > 0) {
            $comments = $ig->fetchComments($media[0]['ig_media_id']);
            return ['ok' => true, 'message' => "{$count} media obtenidos, {$media[0]['comments_count']} comentarios en el primero", 'data' => array_map(fn($m) => [
                'id' => $m['ig_media_id'],
                'caption' => mb_substr($m['caption'], 0, 100),
                'comments' => $m['comments_count'],
                'likes' => $m['like_count'],
            ], $media)];
        }
        return ['ok' => true, 'message' => 'Conexión exitosa, sin media reciente'];
    } catch (Exception $e) {
        return ['ok' => false, 'message' => $e->getMessage()];
    }
}

function testDatabase(): array
{
    try {
        $db = getDB();
        $db->query("SELECT 1");
        $version = $db->getAttribute(PDO::ATTR_SERVER_VERSION);
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        $size = 0;
        $stmt = $db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
        $size = (float)$stmt->fetchColumn();

        return ['ok' => true, 'message' => "MySQL {$version}, {$size}MB", 'tables' => count($tables)];
    } catch (Exception $e) {
        return ['ok' => false, 'message' => $e->getMessage()];
    }
}

function testEmail(): array
{
    if (!defined('SMTP_HOST') || empty(SMTP_HOST)) {
        return ['ok' => false, 'message' => 'SMTP no configurado'];
    }
    if (!defined('SMTP_PASS') || SMTP_PASS === 'tu_password_email' || empty(SMTP_PASS)) {
        return ['ok' => false, 'message' => 'SMTP_PASS no configurado (placeholder)'];
    }

    try {
        $headers = "From: " . SMTP_FROM . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $ok = mail(SMTP_USER, "[Aratio] Test " . date('Y-m-d H:i:s'), "Test de configuración SMTP desde Aratio CRM", $headers);
        return ['ok' => $ok, 'message' => $ok ? 'Email de prueba enviado a ' . SMTP_USER : 'Falló el envío (revisar logs)'];
    } catch (Exception $e) {
        return ['ok' => false, 'message' => $e->getMessage()];
    }
}

function testWhatsApp(): array
{
    $results = [];

    if (defined('WATI_API_KEY') && !empty(WATI_API_KEY)) {
        $results['wati'] = ['ok' => true, 'message' => 'WATI configurado'];
    } else {
        $results['wati'] = ['ok' => false, 'message' => 'WATI_API_KEY vacío'];
    }

    if (defined('META_WHATSAPP_TOKEN') && !empty(META_WHATSAPP_TOKEN)) {
        $results['meta'] = ['ok' => true, 'message' => 'Meta WhatsApp configurado'];
    } else {
        $results['meta'] = ['ok' => false, 'message' => 'META_WHATSAPP_TOKEN vacío'];
    }

    return $results;
}

function getRecentLogs(int $maxLines = 50): array
{
    $logFile = defined('UPLOAD_PATH') ? dirname(UPLOAD_PATH) . '/logs/aratio-' . date('Y-m-d') . '.log' : null;

    if (!$logFile || !file_exists($logFile)) {
        // Fallback to php error log
        $logFile = ini_get('error_log');
        if (!$logFile || !file_exists($logFile)) {
            return [];
        }
    }

    $lines = file($logFile);
    $lines = array_slice($lines, -$maxLines);
    return array_map(fn($l) => trim($l), $lines);
}

function runPendingMigrations(): array
{
    $migrationsDir = __DIR__ . '/../database/migrations/';
    $db = getDB();

    $executed = [];
    $stmt = $db->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $files = glob($migrationsDir . '*.sql');
    sort($files);

    foreach ($files as $file) {
        $filename = basename($file);
        $sql = file_get_contents($file);
        $statements = explode(';', $sql);
        $ran = 0;
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (!empty($stmt)) {
                try {
                    $db->exec($stmt);
                    $ran++;
                } catch (Exception $e) {
                    // Ignore "already exists" errors
                }
            }
        }
        if ($ran > 0) {
            $executed[] = ['file' => $filename, 'statements' => $ran];
        }
    }

    return ['migrations' => $executed, 'total_files' => count($files)];
}

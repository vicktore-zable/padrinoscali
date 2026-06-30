<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$action = $_GET['action'] ?? '';
$jsonPath = __DIR__ . '/../storage/maestro_instagram.json';

try {
    switch ($action) {
        case 'ig_stats':
            handleIgStats($jsonPath);
            break;
        case 'ig_geo':
            handleIgGeo($jsonPath);
            break;
        case 'ig_categories':
            handleIgCategories($jsonPath);
            break;
        case 'ig_timeline':
            handleIgTimeline($jsonPath);
            break;
        case 'fb_stats':
            handleFbStats();
            break;
        case 'combined':
            handleCombined($jsonPath);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    error_log("SocialTerritorial API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno'], 500);
}

function cargarIG(): array
{
    global $jsonPath;
    if (!file_exists($jsonPath)) return ['posts' => [], 'stats' => []];
    $data = json_decode(file_get_contents($jsonPath), true);
    if (!$data || !isset($data['timeline'])) return ['posts' => [], 'stats' => []];

    $posts = [];
    foreach ($data['timeline'] as $mes => $mesPosts) {
        foreach ($mesPosts as $p) {
            $posts[] = $p;
        }
    }

    return ['posts' => $posts, 'stats' => $data['estadisticas'] ?? []];
}

function handleIgStats(string $jsonPath): void
{
    $ig = cargarIG();
    $posts = $ig['posts'];
    $stats = $ig['stats'];

    $total = count($posts);
    $conGeo = 0;
    $enCali = 0;
    $likesTotal = 0;
    $commentsTotal = 0;
    $porCategoria = [];

    foreach ($posts as $p) {
        $likesTotal += (int)($p['likes'] ?? 0);
        $commentsTotal += (int)($p['comentarios'] ?? 0);
        $cat = $p['categoria'] ?? 'general';
        if (!isset($porCategoria[$cat])) $porCategoria[$cat] = ['total' => 0, 'con_geo' => 0, 'likes' => 0, 'comments' => 0];
        $porCategoria[$cat]['total']++;
        $porCategoria[$cat]['likes'] += (int)($p['likes'] ?? 0);
        $porCategoria[$cat]['comments'] += (int)($p['comentarios'] ?? 0);

        if (!empty($p['lat']) && !empty($p['lng'])) {
            $conGeo++;
            $porCategoria[$cat]['con_geo']++;
            $lat = (float)$p['lat'];
            $lng = (float)$p['lng'];
            if ($lat >= 3.3 && $lat <= 3.6 && $lng >= -76.7 && $lng <= -76.3) {
                $enCali++;
            }
        }
    }

    // Engagement rate
    $engagementRate = $total > 0 ? round((($likesTotal + $commentsTotal) / $total), 1) : 0;

    jsonResponse(['success' => true, 'data' => [
        'total_posts' => $total,
        'con_geo' => $conGeo,
        'en_cali' => $enCali,
        'likes_total' => $likesTotal,
        'comments_total' => $commentsTotal,
        'engagement_rate' => $engagementRate,
        'categorias' => $stats['categorias'] ?? [],
        'por_categoria' => $porCategoria,
        'periodos' => $stats['periodos_cubiertos'] ?? [],
    ]]);
}

function handleIgGeo(string $jsonPath): void
{
    $ig = cargarIG();
    $posts = $ig['posts'];
    $features = [];

    foreach ($posts as $p) {
        if (empty($p['lat']) || empty($p['lng'])) continue;
        $lat = (float)$p['lat'];
        $lng = (float)$p['lng'];
        if ($lat < 3.3 || $lat > 3.6 || $lng < -76.7 || $lng > -76.3) continue;

        $features[] = [
            'type' => 'Feature',
            'geometry' => ['type' => 'Point', 'coordinates' => [$lng, $lat]],
            'properties' => [
                'id' => $p['url'] ?? '',
                'fecha' => $p['fecha'] ?? '',
                'categoria' => $p['categoria'] ?? 'general',
                'ubicacion' => $p['ubicacion'] ?? '',
                'texto' => mb_substr($p['texto'] ?? '', 0, 150),
                'likes' => (int)($p['likes'] ?? 0),
                'comentarios' => (int)($p['comentarios'] ?? 0),
                'url' => $p['url'] ?? '',
                'relevancia' => (int)($p['relevancia_politica'] ?? 0),
            ],
        ];
    }

    jsonResponse(['success' => true, 'data' => [
        'type' => 'FeatureCollection',
        'features' => $features,
    ]]);
}

function handleIgCategories(string $jsonPath): void
{
    $ig = cargarIG();
    $posts = $ig['posts'];

    $categorias = [];
    foreach ($posts as $p) {
        $cat = $p['categoria'] ?? 'general';
        $fecha = $p['fecha'] ?? '';
        $mes = substr($fecha, 0, 7);

        if (!isset($categorias[$cat])) $categorias[$cat] = ['total' => 0, 'por_mes' => []];
        $categorias[$cat]['total']++;
        if (!isset($categorias[$cat]['por_mes'][$mes])) $categorias[$cat]['por_mes'][$mes] = 0;
        $categorias[$cat]['por_mes'][$mes]++;
    }

    // Top ubicaciones
    $ubicaciones = [];
    foreach ($posts as $p) {
        $ubi = trim($p['ubicacion'] ?? '');
        if (empty($ubi)) continue;
        if (!isset($ubicaciones[$ubi])) $ubicaciones[$ubi] = 0;
        $ubicaciones[$ubi]++;
    }
    arsort($ubicaciones);
    $topUbicaciones = array_slice($ubicaciones, 0, 20);

    jsonResponse(['success' => true, 'data' => [
        'categorias' => $categorias,
        'top_ubicaciones' => $topUbicaciones,
    ]]);
}

function handleIgTimeline(string $jsonPath): void
{
    if (!file_exists($jsonPath)) {
        jsonResponse(['success' => false, 'message' => 'JSON no encontrado'], 404);
    }
    $data = json_decode(file_get_contents($jsonPath), true);
    if (!$data || !isset($data['timeline'])) {
        jsonResponse(['success' => false, 'message' => 'JSON inválido'], 400);
    }

    $timeline = [];
    foreach ($data['timeline'] as $mes => $posts) {
        $timeline[$mes] = [
            'total' => count($posts),
            'likes' => array_sum(array_column($posts, 'likes')),
            'comments' => array_sum(array_column($posts, 'comentarios')),
            'relevantes' => count(array_filter($posts, fn($p) => ($p['relevancia_politica'] ?? 0) >= 5)),
        ];
    }

    // Últimos 20 posts
    $allPostsFlat = [];
    foreach ($data['timeline'] as $posts) {
        foreach ($posts as $p) {
            $allPostsFlat[] = $p;
        }
    }
    usort($allPostsFlat, fn($a, $b) => strcmp($b['fecha'] ?? '', $a['fecha'] ?? ''));
    $recientes = array_slice($allPostsFlat, 0, 20);

    jsonResponse(['success' => true, 'data' => [
        'timeline' => $timeline,
        'recientes' => $recientes,
    ]]);
}

function handleFbStats(): void
{
    $db = getDB();

    $totalPosts = (int)$db->query("SELECT COUNT(*) FROM fb_posts")->fetchColumn();
    $totalReactions = (int)$db->query("SELECT COUNT(*) FROM fb_reactions")->fetchColumn();
    $totalComments = (int)$db->query("SELECT COUNT(*) FROM fb_comments")->fetchColumn();
    $matched = (int)$db->query("SELECT COUNT(*) FROM fb_commenters WHERE colaborador_id IS NOT NULL")->fetchColumn();

    // Posts por mes
    $stmt = $db->query("
        SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as total,
               SUM(likes_count) as likes, SUM(comments_count) as comments
        FROM fb_posts WHERE fecha IS NOT NULL
        GROUP BY mes ORDER BY mes DESC LIMIT 12
    ");
    $porMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(['success' => true, 'data' => [
        'configured' => defined('FB_PAGE_TOKEN') && !empty(FB_PAGE_TOKEN),
        'total_posts' => $totalPosts,
        'total_reactions' => $totalReactions,
        'total_comments' => $totalComments,
        'matched_colaboradores' => $matched,
        'por_mes' => $porMes,
    ]]);
}

function handleCombined(string $jsonPath): void
{
    $db = getDB();

    // IG mentions por categoría
    $stmt = $db->query("
        SELECT categoria, COUNT(*) as total, COUNT(DISTINCT username) as usuarios
        FROM ig_menciones GROUP BY categoria ORDER BY total DESC
    ");
    $igMenciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // FB stats
    $fbStats = [];
    if (defined('FB_PAGE_TOKEN') && !empty(FB_PAGE_TOKEN)) {
        $fbStats['posts'] = (int)$db->query("SELECT COUNT(*) FROM fb_posts")->fetchColumn();
        $fbStats['reactions'] = (int)$db->query("SELECT COUNT(*) FROM fb_reactions")->fetchColumn();
        $fbStats['comments'] = (int)$db->query("SELECT COUNT(*) FROM fb_comments")->fetchColumn();
    }

    jsonResponse(['success' => true, 'data' => [
        'ig_menciones' => $igMenciones,
        'fb' => $fbStats,
    ]]);
}

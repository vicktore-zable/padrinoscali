<?php
/**
 * EJEMPLO DE USO - Sistema de Optimización de BD
 * 
 * Este archivo muestra cómo usar DatabaseManager, CacheManager y RateLimiter
 * para reducir el throttling de conexiones en Hostinger
 */

require_once __DIR__ . '/includes/DatabaseManager.php';
require_once __DIR__ . '/includes/CacheManager.php';
require_once __DIR__ . '/includes/RateLimiter.php';

// ============================================
// 1. EJEMPLO: DatabaseManager
// ============================================

// ❌ ANTES (Problemático - abre nueva conexión cada vez)
function getColaboradoresAntes()
{
    $db = new PDO("mysql:host=localhost;dbname=aratio", "user", "pass");
    $stmt = $db->query("SELECT * FROM colaboradores");
    return $stmt->fetchAll();
}

// ✅ DESPUÉS (Optimizado - reutiliza conexión)
function getColaboradoresDespues()
{
    $db = DatabaseManager::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM colaboradores");
    return $stmt->fetchAll();
}

// ============================================
// 2. EJEMPLO: CacheManager + DatabaseManager
// ============================================

function getColaboradoresCampana($campanaId)
{
    $cache = new CacheManager();

    // Intentar obtener del caché primero
    $cacheKey = "colaboradores_campana_{$campanaId}";

    return $cache->remember($cacheKey, function () use ($campanaId) {
        // Solo se ejecuta si no está en caché
        $db = DatabaseManager::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM colaboradores WHERE campana_id = ?");
        $stmt->execute([$campanaId]);
        return $stmt->fetchAll();
    }, 600); // Cache por 10 minutos
}

// ============================================
// 3. EJEMPLO: Invalidar caché al actualizar
// ============================================

function actualizarColaborador($id, $data, $campanaId)
{
    $db = DatabaseManager::getInstance()->getConnection();

    // Actualizar en BD
    $stmt = $db->prepare("UPDATE colaboradores SET nombre = ?, email = ? WHERE id = ?");
    $stmt->execute([$data['nombre'], $data['email'], $id]);

    // Invalidar caché relacionado
    $cache = new CacheManager();
    $cache->delete("colaboradores_campana_{$campanaId}");
    $cache->invalidatePattern("colaboradores_*"); // Invalida todos los cachés de colaboradores

    return true;
}

// ============================================
// 4. EJEMPLO: RateLimiter en API
// ============================================

function apiEndpointConRateLimit()
{
    $rateLimiter = new RateLimiter(null, 60, 60); // 60 requests por minuto

    // Identificador único (IP o user_id)
    $identifier = $_SERVER['REMOTE_ADDR'];

    // Verificar límite
    $result = $rateLimiter->check($identifier);

    if (!$result['allowed']) {
        http_response_code(429); // Too Many Requests
        header('Retry-After: ' . $result['retry_after']);
        echo json_encode([
            'success' => false,
            'message' => $result['message'],
            'retry_after' => $result['retry_after']
        ]);
        exit;
    }

    // Headers informativos
    header('X-RateLimit-Limit: 60');
    header('X-RateLimit-Remaining: ' . $result['remaining']);

    // Continuar con la lógica normal
    // ...
}

// ============================================
// 5. EJEMPLO: Uso completo en endpoint real
// ============================================

function apiGetCampanas()
{
    // 1. Rate Limiting
    $rateLimiter = new RateLimiter();
    $userId = $_SESSION['user_id'] ?? $_SERVER['REMOTE_ADDR'];

    $rateCheck = $rateLimiter->check($userId);
    if (!$rateCheck['allowed']) {
        http_response_code(429);
        echo json_encode(['error' => $rateCheck['message']]);
        exit;
    }

    // 2. Caché
    $cache = new CacheManager();
    $cacheKey = "campanas_usuario_{$userId}";

    $campanas = $cache->remember($cacheKey, function () use ($userId) {
        // 3. Database con conexión persistente
        $db = DatabaseManager::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT c.*, COUNT(col.id) as total_colaboradores
            FROM campanas c
            LEFT JOIN colaboradores col ON c.id = col.campana_id
            INNER JOIN usuarios_campanas uc ON c.id = uc.campana_id
            WHERE uc.usuario_id = ?
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }, 300); // Cache 5 minutos

    // 4. Respuesta
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $campanas,
        'cached' => true
    ]);
}

// ============================================
// 6. EJEMPLO: Batch Processing
// ============================================

function procesarColaboradoresBatch($colaboradores)
{
    $db = DatabaseManager::getInstance()->getConnection();
    $batchSize = 50;
    $batches = array_chunk($colaboradores, $batchSize);

    foreach ($batches as $index => $batch) {
        $db->beginTransaction();

        try {
            foreach ($batch as $colaborador) {
                $stmt = $db->prepare("
                    INSERT INTO colaboradores (nombre, email, campana_id) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $colaborador['nombre'],
                    $colaborador['email'],
                    $colaborador['campana_id']
                ]);
            }

            $db->commit();

            // Pausa breve entre lotes para no saturar
            if ($index < count($batches) - 1) {
                usleep(100000); // 0.1 segundos
            }

        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error en batch {$index}: " . $e->getMessage());
            throw $e;
        }
    }

    // Invalidar caché
    $cache = new CacheManager();
    $cache->invalidatePattern('colaboradores_*');
}

// ============================================
// 7. EJEMPLO: Limpieza de caché (cron job)
// ============================================

function limpiezaCacheProgramada()
{
    $cache = new CacheManager();

    // Limpiar caché expirado
    $expiredCleared = $cache->clearExpired();

    // Limpiar rate limit antiguo
    $rateLimiter = new RateLimiter();
    $rateLimitCleared = $rateLimiter->cleanup();

    error_log("Limpieza: {$expiredCleared} cachés expirados, {$rateLimitCleared} rate limits antiguos");

    // Mostrar estadísticas
    $stats = $cache->getStats();
    error_log("Cache Stats: " . json_encode($stats));
}

// ============================================
// 8. EJEMPLO: Monitoreo de performance
// ============================================

function mostrarEstadisticas()
{
    // Stats de Database
    $dbStats = DatabaseManager::getInstance()->getStats();

    // Stats de Cache
    $cache = new CacheManager();
    $cacheStats = $cache->getStats();

    // Stats de Rate Limit
    $rateLimiter = new RateLimiter();
    $userId = $_SESSION['user_id'] ?? 'guest';
    $rateLimitStats = $rateLimiter->getStats($userId);

    return [
        'database' => $dbStats,
        'cache' => $cacheStats,
        'rate_limit' => $rateLimitStats
    ];
}

// ============================================
// EJEMPLO DE SALIDA
// ============================================

/*
// Ejecutar limpieza
limpiezaCacheProgramada();

// Ver estadísticas
$stats = mostrarEstadisticas();
print_r($stats);

// Output:
Array (
    [database] => Array (
        [query_count] => 15
        [is_connected] => 1
        [persistent] => 1
    )
    [cache] => Array (
        [enabled] => 1
        [total_items] => 23
        [active_items] => 18
        [expired_items] => 5
        [total_size_mb] => 0.45
    )
    [rate_limit] => Array (
        [requests_count] => 12
        [remaining] => 48
        [limit] => 60
    )
)
*/

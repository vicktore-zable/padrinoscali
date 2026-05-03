# Guía de Migración - Optimización de Base de Datos

## 🎯 Objetivo

Reducir el throttling de conexiones a MySQL en Hostinger del **100%** al **5-15%** mediante:
- ✅ Connection Pooling (reutilización de conexiones)
- ✅ Sistema de Caché (reducción de queries)
- ✅ Rate Limiting (control de peticiones)

---

## 📋 Pasos de Implementación

### Paso 1: Subir Archivos Nuevos

Sube estos archivos a tu servidor Hostinger:

```
php-export/
├── includes/
│   ├── DatabaseManager.php    ← NUEVO
│   ├── CacheManager.php        ← NUEVO
│   └── RateLimiter.php         ← NUEVO
├── cache/                      ← CREAR DIRECTORIO
│   └── .htaccess              ← Auto-creado
└── EJEMPLO_USO_OPTIMIZACION.php ← NUEVO (referencia)
```

**Crear directorio de caché:**
```bash
mkdir -p php-export/cache
chmod 755 php-export/cache
```

---

### Paso 2: Modificar config.php

Agrega estas líneas al final de `config/config.php`:

```php
// ===== OPTIMIZACIONES DE BD =====
require_once __DIR__ . '/../includes/DatabaseManager.php';
require_once __DIR__ . '/../includes/CacheManager.php';
require_once __DIR__ . '/../includes/RateLimiter.php';

// Función optimizada para obtener conexión
function getDB() {
    return DatabaseManager::getInstance()->getConnection();
}

// Instancia global de caché
$GLOBALS['cache'] = new CacheManager();
$GLOBALS['rateLimiter'] = new RateLimiter();
```

---

### Paso 3: Migrar Endpoints API (Ejemplo)

#### Antes (api/campanas.php):
```php
<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

$db = getDB(); // Ya optimizado con el cambio en config.php
$stmt = $db->prepare("SELECT * FROM campanas WHERE usuario_id = ?");
$stmt->execute([$userId]);
$campanas = $stmt->fetchAll();

jsonResponse(['data' => $campanas]);
```

#### Después (con caché):
```php
<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

// Rate Limiting
$rateCheck = $GLOBALS['rateLimiter']->check($_SESSION['user_id']);
if (!$rateCheck['allowed']) {
    jsonResponse(['error' => $rateCheck['message']], 429);
}

// Caché
$cacheKey = "campanas_user_" . $_SESSION['user_id'];
$campanas = $GLOBALS['cache']->remember($cacheKey, function() use ($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM campanas WHERE usuario_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}, 300); // 5 minutos

jsonResponse(['data' => $campanas]);
```

---

### Paso 4: Invalidar Caché al Actualizar

Cuando crees/actualices/elimines datos, invalida el caché:

```php
// Al crear campaña
$db->prepare("INSERT INTO campanas ...")->execute([...]);

// Invalidar caché
$GLOBALS['cache']->delete("campanas_user_{$userId}");
$GLOBALS['cache']->invalidatePattern("campanas_*");
```

---

### Paso 5: Configurar Limpieza Automática (Cron)

Crea `cron_cleanup.php`:

```php
<?php
require_once __DIR__ . '/config/config.php';

// Limpiar caché expirado
$cache = new CacheManager();
$expiredCleared = $cache->clearExpired();

// Limpiar rate limit antiguo
$rateLimiter = new RateLimiter();
$rateLimitCleared = $rateLimiter->cleanup();

error_log("Limpieza: {$expiredCleared} cachés, {$rateLimitCleared} rate limits");
```

**Configurar en cPanel:**
1. Ir a **Cron Jobs**
2. Agregar: `0 * * * * /usr/bin/php /home/usuario/public_html/cron_cleanup.php`
   (Ejecuta cada hora)

---

## 🎨 Configuración de TTL por Tipo de Dato

Ajusta los tiempos de caché según la frecuencia de cambio:

```php
// Datos que cambian poco → TTL largo
$departamentos = $cache->remember('departamentos', function() {
    return getDepartamentos();
}, 86400); // 24 horas

// Datos que cambian moderadamente → TTL medio
$campanas = $cache->remember("campanas_user_{$userId}", function() {
    return getCampanas($userId);
}, 600); // 10 minutos

// Datos que cambian frecuentemente → TTL corto
$eventos = $cache->remember("eventos_hoy", function() {
    return getEventosHoy();
}, 60); // 1 minuto
```

---

## 📊 Monitoreo

Crea `admin/stats.php` para ver estadísticas:

```php
<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

$stats = [
    'database' => DatabaseManager::getInstance()->getStats(),
    'cache' => $GLOBALS['cache']->getStats(),
    'rate_limit' => $GLOBALS['rateLimiter']->getStats($_SESSION['user_id'])
];

header('Content-Type: application/json');
echo json_encode($stats, JSON_PRETTY_PRINT);
```

---

## ⚡ Prioridades de Migración

### Alta Prioridad (Migrar PRIMERO):
1. ✅ `api/campanas.php` - Se consulta en cada carga
2. ✅ `api/colaboradores.php` - Muchos registros
3. ✅ `api/territorios.php` - Datos estáticos
4. ✅ `api/dashboard.php` - Múltiples queries

### Media Prioridad:
5. ✅ `api/eventos.php`
6. ✅ `api/donaciones.php`
7. ✅ `api/compromisos.php`

### Baja Prioridad:
8. ✅ `api/elecciones.php` - Poco tráfico
9. ✅ `api/grupos.php` - Poco tráfico

---

## 🚨 Troubleshooting

### Problema: Caché no se invalida
**Solución:** Verifica que estás usando `invalidatePattern()` correctamente:
```php
$cache->invalidatePattern('colaboradores_*'); // Invalida todos
```

### Problema: Rate limit muy restrictivo
**Solución:** Ajusta los límites en la inicialización:
```php
$rateLimiter = new RateLimiter(null, 120, 60); // 120 requests/minuto
```

### Problema: Directorio cache sin permisos
**Solución:**
```bash
chmod 755 php-export/cache
chown usuario:usuario php-export/cache
```

---

## 📈 Resultados Esperados

| Métrica | Antes | Después | Mejora |
|---------|-------|---------|--------|
| Conexiones simultáneas | 25-30 | 2-5 | **85%** ↓ |
| Queries por request | 10-15 | 1-3 | **80%** ↓ |
| Tiempo de respuesta | 800ms | 150ms | **81%** ↓ |
| Throttling | Frecuente | Ninguno | **100%** ↓ |

---

## ✅ Checklist de Implementación

- [ ] Subir DatabaseManager.php, CacheManager.php, RateLimiter.php
- [ ] Crear directorio `cache/` con permisos 755
- [ ] Modificar `config.php` con las nuevas funciones
- [ ] Migrar endpoint `api/campanas.php` (prueba)
- [ ] Migrar endpoint `api/colaboradores.php`
- [ ] Migrar endpoint `api/territorios.php`
- [ ] Migrar endpoint `api/dashboard.php`
- [ ] Configurar cron job de limpieza
- [ ] Crear página de estadísticas
- [ ] Probar bajo carga
- [ ] Monitorear logs de errores
- [ ] Verificar reducción de throttling

---

## 🎯 Siguiente Paso

**Empieza por aquí:**
1. Sube los 3 archivos nuevos a `includes/`
2. Modifica `config.php`
3. Prueba con `api/campanas.php`
4. Si funciona, migra los demás endpoints

**¿Necesitas ayuda?** Revisa `EJEMPLO_USO_OPTIMIZACION.php` para ver casos de uso completos.

<?php
// Test mínimo para identificar el error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Minimo</h1>";

// Paso 1: Cargar config
echo "<p>Paso 1: Cargando config/config.php...</p>";
try {
    require_once __DIR__ . '/config/config.php';
    echo "<p style='color:green'>OK - config cargado</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Paso 2: Cargar bootstrap mod_lider
echo "<p>Paso 2: Cargando mod_lider/src/bootstrap.php...</p>";
try {
    require_once __DIR__ . '/mod_lider/src/bootstrap.php';
    echo "<p style='color:green'>OK - bootstrap cargado</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Paso 3: Cargar config mod_lider
echo "<p>Paso 3: Cargando mod_lider/config/config.php...</p>";
try {
    require_once __DIR__ . '/mod_lider/config/config.php';
    echo "<p style='color:green'>OK - config mod_lider cargado</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Paso 4: Cargar database mod_lider
echo "<p>Paso 4: Cargando mod_lider/config/database.php...</p>";
try {
    require_once __DIR__ . '/mod_lider/config/database.php';
    echo "<p style='color:green'>OK - database mod_lider cargado</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Paso 5: Cargar PortalAuthController
echo "<p>Paso 5: Cargando PortalAuthController...</p>";
try {
    require_once __DIR__ . '/mod_lider/src/Controllers/PortalAuthController.php';
    echo "<p style='color:green'>OK - PortalAuthController cargado</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Paso 6: Instanciar controller
echo "<p>Paso 6: Instanciando PortalAuthController...</p>";
try {
    $controller = new \App\Controllers\PortalAuthController();
    echo "<p style='color:green'>OK - Controller instanciado</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

echo "<h2 style='color:green'>Todos los pasos completados exitosamente</h2>";
?>

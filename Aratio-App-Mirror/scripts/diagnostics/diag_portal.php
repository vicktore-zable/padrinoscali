<?php
// Diagnóstico detallado del portal_login
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnostico Portal Login</h1>";

// 1. Verificar estructura de archivos
echo "<h2>1. Archivos Criticos</h2>";
$criticalFiles = [
    'config/config.php' => 'Configuracion principal',
    'mod_lider/src/bootstrap.php' => 'Autoloader mod_lider',
    'mod_lider/config/config.php' => 'Config mod_lider',
    'mod_lider/config/database.php' => 'Database mod_lider',
    'mod_lider/src/Models/Evento.php' => 'Modelo Evento',
    'mod_lider/src/Models/Colaborador.php' => 'Modelo Colaborador',
    'mod_lider/src/Controllers/PortalAuthController.php' => 'Controller Auth',
    'mod_lider/src/Controllers/LeaderPortalController.php' => 'Controller Portal',
    'pages/portal_login.php' => 'Pagina login',
];

foreach ($criticalFiles as $file => $desc) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    $color = $exists ? 'green' : 'red';
    echo "<p style='color:$color'>" . ($exists ? "✓" : "✗") . " $desc: $file</p>";
}

// 2. Test de carga de clases
echo "<h2>2. Test de Carga</h2>";
try {
    // Cargar config principal
    require_once __DIR__ . '/../../config/config.php';
    echo "<p style='color:green'>✓ config/config.php cargado</p>";
    
    // Cargar bootstrap mod_lider
    require_once __DIR__ . '/mod_lider/src/bootstrap.php';
    echo "<p style='color:green'>✓ mod_lider bootstrap cargado</p>";
    
    // Cargar database mod_lider
    require_once __DIR__ . '/mod_lider/config/database.php';
    echo "<p style='color:green'>✓ mod_lider database cargado</p>";
    
    // Verificar clases
    if (class_exists('App\Models\Evento')) {
        echo "<p style='color:green'>✓ Clase Evento existe</p>";
        $evento = new \App\Models\Evento();
        echo "<p style='color:green'>✓ Evento instanciado</p>";
    } else {
        echo "<p style='color:red'>✗ Clase Evento NO existe</p>";
    }
    
    if (class_exists('App\Models\Colaborador')) {
        echo "<p style='color:green'>✓ Clase Colaborador existe</p>";
        $colab = new \App\Models\Colaborador();
        echo "<p style='color:green'>✓ Colaborador instanciado</p>";
        
        if (method_exists($colab, 'getDownlineDocumentos')) {
            echo "<p style='color:green'>✓ Metodo getDownlineDocumentos existe</p>";
        } else {
            echo "<p style='color:red'>✗ Metodo getDownlineDocumentos NO existe</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Clase Colaborador NO existe</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// 3. Test de conexion BD
echo "<h2>3. Base de Datos</h2>";
try {
    $db = getDB();
    echo "<p style='color:green'>✓ Conexion BD exitosa</p>";
    
    // Verificar tabla usuarios
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios");
    $count = $stmt->fetchColumn();
    echo "<p>Usuarios en BD: $count</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>ERROR BD: " . $e->getMessage() . "</p>";
}

echo "<hr><p style='color:red'><strong>ELIMINAR este archivo despues del diagnostico</strong></p>";
?>

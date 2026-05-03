<?php
session_start();
require_once 'mod_lider/src/bootstrap.php';
require_once 'mod_lider/config/config.php';
require_once 'mod_lider/config/database.php';

use App\Controllers\PortalAuthController;
use App\Models\Usuario;
use App\Models\Colaborador;

echo "--- VERIFICANDO CREDENCIALES LIDER 1130665763 ---\n";

 = '1130665763';
 = '3178386580';

 = new Colaborador();
 = ->getByDocumento();

if (!) {
    echo "ERROR: Colaborador no encontrado.\n";
    exit;
}

echo "Colaborador encontrado: " . ['nombres'] . "\n";
echo "Telefono en DB: " . ['telefono'] . "\n";

 = new Usuario();
 = ->getByUsuario();

if () {
    echo "Usuario ya existe en tabla usuarios. Email: " . ['email'] . "\n";
    if (strpos(['email'], '@aratio.tmp') !== false) {
        echo "LOGICA: El usuario TIENE email temporal. Debería ir a setup.\n";
    } else {
        echo "LOGICA: El usuario ya tiene email real. Debería ir a dashboard.\n";
    }
} else {
    echo "Usuario NO existe. El login automático lo creará con email temporal.\n";
}

echo "\n--- SIMULANDO PROCESO DE LOGIN ---\n";
// El controlador hace: if (\ === \ && !empty(\))
if ( === ['telefono']) {
    echo "PASS MATCH! (Telefono == Password)\n";
    if (!) {
        echo "SIMULACION: Creando usuario con email temporal lider_" .  . "@aratio.tmp\n";
    }
    echo "SIMULACION: Redirigiendo a LOGIN_USER...\n";
    
     =  ? ['email'] : 'lider_' .  . '@aratio.tmp';
    if (strpos(, '@aratio.tmp') !== false) {
        echo "DESTINO FINAL: portal_setup (CORRECTO)\n";
    } else {
        echo "DESTINO FINAL: portal_dashboard\n";
    }
} else {
    echo "PASS NO MATCH! Credenciales incorrectas.\n";
}
?>
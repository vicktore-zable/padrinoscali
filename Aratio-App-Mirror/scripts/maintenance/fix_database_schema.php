<?php
if (ob_get_level()) ob_end_clean();
define('ROOT_PATH', __DIR__);
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';

try {
    $db = Database::getInstance();
    
    echo "Iniciando reparación de esquema de base de datos...\n";

    // 1. Añadir columna 'usuario'
    try {
        $db->query("SELECT usuario FROM usuarios LIMIT 1");
        echo "- Columna 'usuario' ya existe.\n";
    } catch (Exception $e) {
        $db->query("ALTER TABLE usuarios ADD COLUMN usuario VARCHAR(50) UNIQUE AFTER id");
        echo "- Columna 'usuario' añadida.\n";
        // Llenar 'usuario' con parte del email o nombre para registros existentes
        $db->query("UPDATE usuarios SET usuario = SUBSTRING_INDEX(email, '@', 1) WHERE usuario IS NULL");
    }

    // 2. Añadir/Verificar 'tipo_usuario' vs 'rol'
    try {
        $db->query("SELECT tipo_usuario FROM usuarios LIMIT 1");
        echo "- Columna 'tipo_usuario' ya existe.\n";
    } catch (Exception $e) {
        // Si existe rol, podríamos querer copiarlo o renombrarlo. Vamos a añadir tipo_usuario.
        $db->query("ALTER TABLE usuarios ADD COLUMN tipo_usuario VARCHAR(20) DEFAULT 'consulta' AFTER rol");
        echo "- Columna 'tipo_usuario' añadida.\n";
        // Migrar datos de rol si existen
        $db->query("UPDATE usuarios SET tipo_usuario = rol WHERE rol IS NOT NULL AND tipo_usuario = 'consulta'");
    }

    // 3. Añadir 'colaborador_id'
    try {
        $db->query("SELECT colaborador_id FROM usuarios LIMIT 1");
        echo "- Columna 'colaborador_id' ya existe.\n";
    } catch (Exception $e) {
        $db->query("ALTER TABLE usuarios ADD COLUMN colaborador_id INT NULL AFTER tipo_usuario");
        echo "- Columna 'colaborador_id' añadida.\n";
    }

    // 4. Añadir 'documento_colaborador' (usado en Usuario.php JOINS)
    try {
        $db->query("SELECT documento_colaborador FROM usuarios LIMIT 1");
        echo "- Columna 'documento_colaborador' ya existe.\n";
    } catch (Exception $e) {
        $db->query("ALTER TABLE usuarios ADD COLUMN documento_colaborador VARCHAR(20) NULL AFTER colaborador_id");
        echo "- Columna 'documento_colaborador' añadida.\n";
    }

    // 5. Otros campos faltantes importantes para AuthController
    $camposBase = [
        'activo' => "BOOLEAN DEFAULT 1",
        'intentos_fallidos' => "INT DEFAULT 0",
        'bloqueado_hasta' => "TIMESTAMP NULL",
        'require_2fa' => "BOOLEAN DEFAULT 0",
        'token_2fa' => "VARCHAR(255) NULL",
        'reset_token' => "VARCHAR(255) NULL",
        'reset_token_expira' => "TIMESTAMP NULL"
    ];

    foreach ($camposBase as $campo => $def) {
        try {
            $db->query("SELECT $campo FROM usuarios LIMIT 1");
        } catch (Exception $e) {
            $db->query("ALTER TABLE usuarios ADD COLUMN $campo $def");
            echo "- Columna '$campo' añadida.\n";
        }
    }

    // 6. Si 'apellidos' o 'nombres' faltan (la tabla tiene 'nombre')
    try {
        $db->query("SELECT nombres FROM usuarios LIMIT 1");
    } catch (Exception $e) {
         $db->query("ALTER TABLE usuarios ADD COLUMN nombres VARCHAR(100) AFTER email");
         $db->query("UPDATE usuarios SET nombres = nombre"); 
    }
    try {
        $db->query("SELECT apellidos FROM usuarios LIMIT 1");
    } catch (Exception $e) {
         $db->query("ALTER TABLE usuarios ADD COLUMN apellidos VARCHAR(100) AFTER nombres");
         // No podemos adivinar apellidos fácil, dejar null o vacio
    }

    echo "Esquema de usuarios reparado exitosamente.\n";

} catch (Exception $e) {
    echo "Error crítico reparando esquema: " . $e->getMessage() . "\n";
}

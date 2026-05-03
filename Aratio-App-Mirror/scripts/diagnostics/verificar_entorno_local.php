<?php
/**
 * Script de Verificación de Entorno Local - Aratio
 */
header('Content-Type: text/plain; charset=utf-8');

echo "====================================================\n";
echo "📊 VERIFICACIÓN DE ENTORNO LOCAL - ARATIO v1.3.2\n";
echo "====================================================\n\n";

// 1. Verificar Versión de PHP
echo "[1] Verificando PHP...\n";
if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    echo "✅ PHP " . PHP_VERSION . " detectado.\n";
} else {
    echo "❌ PHP " . PHP_VERSION . " detectado. Se recomienda PHP 8.0+.\n";
}

// 2. Cargar Configuración
echo "\n[2] Cargando configuración...\n";
if (file_exists(__DIR__ . '/../../config/config.php')) {
    require_once __DIR__ . '/../../config/config.php';
    echo "✅ Configuración principal cargada.\n";
} else {
    echo "❌ No se encontró config/config.php\n";
    die();
}

// 3. Verificar Base de Datos
echo "\n[3] Verificando Conexión a Base de Datos...\n";
try {
    $db = getDB();
    echo "✅ Conexión establecida con éxito.\n";
    
    // 3.1 Verificar tabla sesiones
    $stmt = $db->query("DESCRIBE sesiones");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredSesionCols = ['token', 'expires_at', 'usuario_id'];
    $missingSesion = array_diff($requiredSesionCols, $cols);
    if (empty($missingSesion)) {
        echo "✅ Tabla 'sesiones' correctamente configurada.\n";
    } else {
        echo "❌ Tabla 'sesiones' incompleta. Faltan: " . implode(', ', $missingSesion) . "\n";
    }

    // 3.2 Verificar tabla eventos
    $stmt = $db->query("DESCRIBE eventos");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredEventoCols = ['fecha_inicio', 'ubicacion', 'tipo']; // Usamos ubicacion ya que list_fields.php la mostró
    $missingEvento = array_diff($requiredEventoCols, $cols);
    if (empty($missingEvento)) {
        echo "✅ Tabla 'eventos' correctamente configurada.\n";
    } else {
        echo "❌ Tabla 'eventos' incompleta. Faltan: " . implode(', ', $missingEvento) . "\n";
        echo "   Columnas encontradas: " . implode(', ', $cols) . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error de BD: " . $e->getMessage() . "\n";
}

// 4. Verificar Módulos
echo "\n[4] Verificando Estructura de Módulos...\n";
$modulos = [
    'mod_colab' => 'Módulo de Colaboradores',
    'mod_lider' => 'Módulo Portal del Líder'
];
foreach ($modulos as $dir => $nombre) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        echo "✅ $nombre detectado.\n";
        // Verificar .env en mod_colab
        if ($dir === 'mod_colab' && file_exists(__DIR__ . '/mod_colab/.env')) {
            $envContent = file_get_contents(__DIR__ . '/mod_colab/.env');
            if (strpos($envContent, 'SESSION_NAME=ARATIO_SESSION') !== false) {
                echo "   ✅ .env de mod_colab sincronizado.\n";
            } else {
                echo "   ⚠️ .env de mod_colab con SESSION_NAME incorrecto.\n";
            }
        }
    } else {
        echo "❌ No se encontró el directorio /$dir\n";
    }
}

// 5. Verificar Session Name Global
echo "\n[5] Verificando Nombre de Sesión Unificado...\n";
if (defined('SESSION_NAME') && SESSION_NAME === 'ARATIO_SESSION') {
    echo "✅ SESSION_NAME unificado como 'ARATIO_SESSION'.\n";
} else {
    echo "⚠️ SESSION_NAME no coincide o no está definido.\n";
}

echo "\n====================================================\n";
echo "🏁 VERIFICACIÓN COMPLETADA\n";
echo "Si todos los puntos marcan ✅, el entorno está listo.\n";
echo "====================================================\n";

<?php
/**
 * Script para subir TODAS las vistas al servidor de producción
 */

$ftpServer = "212.1.208.241";
$ftpUser = "u156469157.aratio.mrmtech.net";
$ftpPass = "sthLX6bJPoGh";
$remoteBase = "/public_html/mod_colab";

// Conectar
$ftp = ftp_connect($ftpServer);
if (!$ftp) {
    die("Error conectando a FTP\n");
}

$login = ftp_login($ftp, $ftpUser, $ftpPass);
if (!$login) {
    die("Error en login FTP\n");
}

ftp_pasv($ftp, true);

echo "=== SUBIENDO TODAS LAS VISTAS ===\n\n";

// Lista completa de vistas
$views = [
    // Auth
    'src/Views/auth/forgot-password.php',
    'src/Views/auth/login.php',
    'src/Views/auth/register.php',
    'src/Views/auth/reset-password.php',
    'src/Views/auth/two-factor.php',

    // Colaboradores
    'src/Views/colaboradores/create.php',
    'src/Views/colaboradores/edit.php',
    'src/Views/colaboradores/index.php',
    'src/Views/colaboradores/network.php',
    'src/Views/colaboradores/show.php',

    // Components
    'src/Views/components/navbar.php',
    'src/Views/components/sidebar.php',

    // Curriculum
    'src/Views/curriculum/edit.php',
    'src/Views/curriculum/edit_v2.php',
    'src/Views/curriculum/show.php',

    // Dashboard
    'src/Views/dashboard/index.php',

    // Errors
    'src/Views/errors/403.php',
    'src/Views/errors/404.php',
    'src/Views/errors/500.php',

    // Home
    'src/Views/home/index.php',

    // Layouts
    'src/Views/layouts/default.php',
    'src/Views/layouts/landing.php',

    // Logs
    'src/Views/logs/index.php',
    'src/Views/logs/search.php',

    // Profile
    'src/Views/profile/2fa.php',
    'src/Views/profile/index.php',
    'src/Views/profile/sessions.php',

    // Reports
    'src/Views/reports/by-leaders.php',
    'src/Views/reports/by-leaders-v2.php',
    'src/Views/reports/by-profile.php',
    'src/Views/reports/by-territory.php',
    'src/Views/reports/by-territory-v2.php',
    'src/Views/reports/growth.php',
    'src/Views/reports/index.php',

    // Settings
    'src/Views/settings/index.php',

    // Usuarios
    'src/Views/usuarios/create.php',
    'src/Views/usuarios/edit.php',
    'src/Views/usuarios/index.php',
    'src/Views/usuarios/lider_dashboard.php',
    'src/Views/usuarios/lider_no_colaborador.php',
    'src/Views/usuarios/lider_no_encontrado.php',
];

$uploaded = 0;
$errors = 0;

foreach ($views as $localFile) {
    // Crear estructura de directorios
    $remotePath = str_replace('src/Views/', '', $localFile);
    $remoteDir = $remoteBase . '/src/Views/' . dirname($remotePath);
    $remoteFile = $remoteBase . '/' . $localFile;

    // Crear directorios recursivamente
    $dirs = explode('/', dirname($remotePath));
    $currentPath = $remoteBase . '/src/Views';

    foreach ($dirs as $dir) {
        if (empty($dir)) continue;
        $currentPath .= '/' . $dir;
        @ftp_mkdir($ftp, $currentPath);
    }

    // Subir archivo
    if (file_exists($localFile)) {
        if (ftp_put($ftp, $remoteFile, $localFile, FTP_BINARY)) {
            echo "✓ $localFile\n";
            $uploaded++;
        } else {
            echo "✗ ERROR: $localFile\n";
            $errors++;
        }
    } else {
        echo "⚠ NO EXISTE: $localFile\n";
        $errors++;
    }
}

ftp_close($ftp);

echo "\n=== RESUMEN ===\n";
echo "Total archivos: " . count($views) . "\n";
echo "Subidos: $uploaded\n";
echo "Errores: $errors\n";

if ($errors === 0) {
    echo "\n✅ TODAS LAS VISTAS SUBIDAS CORRECTAMENTE\n";
} else {
    echo "\n⚠ Hubo algunos errores\n";
}

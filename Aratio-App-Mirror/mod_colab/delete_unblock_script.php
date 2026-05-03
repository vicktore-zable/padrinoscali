<?php
/**
 * Script para eliminar el script de desbloqueo de producción
 */

// Configuración FTP
$ftp_server = "212.1.208.241";
$ftp_user = "u156469157.aratio.mrmtech.net";
$ftp_pass = "sthLX6bJPoGh";

$remote_file = "/public_html/mod_colab/public/unblock_admin.php";

echo "Conectando a FTP para eliminar script de seguridad...\n";

$conn_id = @ftp_connect($ftp_server, 21, 30);
if (!$conn_id)
    die("Error conexión FTP");

if (!@ftp_login($conn_id, $ftp_user, $ftp_pass))
    die("Error login FTP");

if (ftp_delete($conn_id, $remote_file)) {
    echo "✅ Archivo unblock_admin.php eliminado exitosamente de producción.\n";
} else {
    echo "❌ No se pudo eliminar el archivo (o ya no existe).\n";
}

ftp_close($conn_id);

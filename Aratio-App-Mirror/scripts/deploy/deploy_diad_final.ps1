# deploy_diad_final.ps1
$user = "u156469157.aratio.mrmtech.net"
$pass = "sthLX6bJPoGh"
$hostinger = "ftp://212.1.208.241"

function Upload-File($localPath, $remotePath) {
    Write-Host "Uploading $localPath to $remotePath..."
    $ftpUrl = "${hostinger}/${remotePath}"
    curl.exe --silent --show-error -T "$localPath" "$ftpUrl" --user "${user}:${pass}"
}

# Ensure mod_diaD exists (by uploading index.php to it)
# Note: curl -T creates parent directories on some FTP servers, Hostinger usually allows this or we do it via SSH
Upload-File "mod_diaD/index.php" "mod_diaD/index.php"
Upload-File "mod_diaD/dashboard.php" "mod_diaD/dashboard.php"
Upload-File "api/diaD_datos.php" "api/diaD_datos.php"
Upload-File "api/diaD_reportes.php" "api/diaD_reportes.php"
Upload-File "api/create_diad_dir.php" "api/create_diad_dir.php"
Upload-File "api/update_htaccess.php" "api/update_htaccess.php"
Upload-File "api/read_htaccess_final.php" "api/read_htaccess_final.php"
Upload-File "api/list_root.php" "api/list_root.php"
Upload-File "api/cleanup_deployment.php" "api/cleanup_deployment.php"

Write-Host "Upload complete."

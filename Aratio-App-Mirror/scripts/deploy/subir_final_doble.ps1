# Script de subida a múltiples rutas potenciales
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

function Upload-FtpFile($local, $remote) {
    try {
        $client = New-Object System.Net.WebClient
        $client.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $client.UploadFile("$ftpServer/$remote", "STOR", $local)
        Write-Host "SUCCESS: $remote" -ForegroundColor Green
    } catch {
        Write-Host "X FAILED: $remote - $($_.Exception.Message)" -ForegroundColor Red
    }
}

$filesToUpload = @(
    "api/colaboradores.php",
    "pages/colaboradores.php",
    "pages/colaboradores_red.php"
)

Write-Host "--- Iniciando Subida Doble (Root + public_html) ---" -ForegroundColor Yellow

foreach ($f in $filesToUpload) {
    # Subir a Root
    Upload-FtpFile $f $f
    # Subir a public_html
    Upload-FtpFile $f "public_html/$f"
}

Write-Host "--- Fin ---"

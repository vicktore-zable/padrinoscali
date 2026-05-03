# Script PowerShell para subir constants.php a producción via FTP
# Fecha: 2025-11-28

$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPass = "sthLX6bJPoGh"
$localFile = "h:\Mi unidad\2025\5d\app\colaboradores\config\constants.php"
$remoteFile = "/public_html/mod_colab/config/constants.php"

Write-Host "Subiendo constants.php a producción..." -ForegroundColor Cyan

try {
    # Crear objeto FtpWebRequest
    $ftpUri = "$ftpServer$remoteFile"
    $request = [System.Net.FtpWebRequest]::Create($ftpUri)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
    $request.UseBinary = $true
    $request.UsePassive = $true

    # Leer archivo local
    $fileContent = [System.IO.File]::ReadAllBytes($localFile)
    $request.ContentLength = $fileContent.Length

    # Subir archivo
    $requestStream = $request.GetRequestStream()
    $requestStream.Write($fileContent, 0, $fileContent.Length)
    $requestStream.Close()

    # Obtener respuesta
    $response = $request.GetResponse()
    Write-Host "✅ Archivo subido exitosamente: $($response.StatusDescription)" -ForegroundColor Green
    $response.Close()

} catch {
    Write-Host "❌ Error al subir archivo: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host "`n✅ constants.php actualizado en producción" -ForegroundColor Green

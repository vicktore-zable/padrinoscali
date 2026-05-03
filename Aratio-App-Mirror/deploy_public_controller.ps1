# Script PowerShell para Despliegue de Controlador en Produccion
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

$localFile = "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_colab\src\Controllers\PublicController.php"
$remotePath = "mod_colab/src/Controllers/PublicController.php"

Write-Host "`n🚀 Desplegando PublicController.php (Módulo MVC)..." -ForegroundColor Cyan

try {
    $ftpPath = "$ftpServer/$remotePath"
    $request = [System.Net.FtpWebRequest]::Create($ftpPath)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true

    $content = [System.IO.File]::ReadAllBytes($localFile)
    $request.ContentLength = $content.Length

    $stream = $request.GetRequestStream()
    $stream.Write($content, 0, $content.Length)
    $stream.Close()

    Write-Host "✅ OK: Archivo actualizado en producción." -ForegroundColor Green
}
catch {
    Write-Host "❌ Error Fatal: $($_.Exception.Message)" -ForegroundColor Red
}

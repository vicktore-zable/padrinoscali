# Script PowerShell para despliegue de las mejoras del Mapa
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

function Upload-FtpFile {
    param([string]$localFile, [string]$remotePath)
    try {
        $request = [System.Net.FtpWebRequest]::Create("$ftpServer/$remotePath")
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $request.UseBinary = $true
        
        $content = [System.IO.File]::ReadAllBytes($localFile)
        $request.ContentLength = $content.Length
        
        $stream = $request.GetRequestStream()
        $stream.Write($content, 0, $content.Length)
        $stream.Close()
        
        Write-Host "✅ EXITO: $remotePath" -ForegroundColor Green
    } catch {
        Write-Host "❌ ERROR: $remotePath - $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "🚀 Iniciando despliegue a producción (aratio.mrmtech.net)..." -ForegroundColor Cyan

# Subir archivos base
Upload-FtpFile -localFile "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\api_puestos_standalone.php" -remotePath "api_puestos_standalone.php"
Upload-FtpFile -localFile "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mapa_territorios.php" -remotePath "mapa_territorios.php"
Upload-FtpFile -localFile "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\CHANGELOG.md" -remotePath "CHANGELOG.md"

# Subir archivos del módulo JAC
Upload-FtpFile -localFile "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_jac\index.php" -remotePath "mod_jac/index.php"
Upload-FtpFile -localFile "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_jac\grupos_de_interes.php" -remotePath "mod_jac/grupos_de_interes.php"
Upload-FtpFile -localFile "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_jac\dashboard.php" -remotePath "mod_jac/dashboard.php"
Upload-FtpFile -localFile "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\api\jac.php" -remotePath "api/jac.php"
Upload-FtpFile -localFile "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\pages\jac.php" -remotePath "pages/jac.php"

Write-Host "🏁 Despliegue completado con éxito." -ForegroundColor Cyan

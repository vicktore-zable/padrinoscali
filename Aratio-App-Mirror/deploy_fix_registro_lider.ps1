# Script PowerShell - Fix Error 500 registro-lider.php
# Autor: Antigravity | Fecha: 2026-03-08

$ftpServer   = "ftp://212.1.208.241"
$ftpUser     = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

function Upload-FtpFile {
    param([string]$localFile, [string]$remotePath)
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

        Write-Host "✅ OK: $remotePath" -ForegroundColor Green
    } catch {
        Write-Host "❌ Error: $remotePath - $($_.Exception.Message)" -ForegroundColor Red
    }
}

$base = "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System"

Write-Host "`n🚀 Desplegando fix Error 500 en registro-lider..." -ForegroundColor Cyan
Write-Host "   Corrección: INSERT a curriculum es ahora opcional (try/catch)" -ForegroundColor Yellow
Write-Host "   Corrección: Campos NULL-safe para campos opcionales`n" -ForegroundColor Yellow

Upload-FtpFile "$base\registro-lider.php" "registro-lider.php"

Write-Host "`n✅ Despliegue completado. Probar en: https://aratio.mrmtech.net/registro-lider" -ForegroundColor Cyan

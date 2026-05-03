# Script de despliegue para migración y fix de login
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
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
        
        Write-Host "OK: $remotePath" -ForegroundColor Green
    } catch {
        Write-Host "Error: $remotePath - $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Archivos a subir
$baseDir = "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System"

Upload-FtpFile -localFile "$baseDir\mod_jac\migrate_v1_7_1.php" -remotePath "mod_jac/migrate_v1_7_1.php"
Upload-FtpFile -localFile "$baseDir\mod_jac\login.php" -remotePath "mod_jac/login.php"

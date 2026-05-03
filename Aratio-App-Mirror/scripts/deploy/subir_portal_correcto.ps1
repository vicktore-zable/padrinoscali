# Subir archivos del portal a la ubicacion correcta (public_html raiz)
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
        $request.KeepAlive = $false
        
        $fileContent = [System.IO.File]::ReadAllBytes($localFile)
        $request.ContentLength = $fileContent.Length
        
        $stream = $request.GetRequestStream()
        $stream.Write($fileContent, 0, $fileContent.Length)
        $stream.Close()
        
        $response = $request.GetResponse()
        $response.Close()
        Write-Host "OK: $remotePath" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

Write-Host "Subiendo archivos a public_html (raiz)..." -ForegroundColor Yellow

# Subir index.php actualizado
Upload-FtpFile -localFile "index.php" -remotePath "public_html/index.php"

# Subir paginas del portal
Upload-FtpFile -localFile "pages/portal_login.php" -remotePath "public_html/pages/portal_login.php"
Upload-FtpFile -localFile "pages/portal_landing.php" -remotePath "public_html/pages/portal_landing.php"
Upload-FtpFile -localFile "pages/portal_auth.php" -remotePath "public_html/pages/portal_auth.php"
Upload-FtpFile -localFile "pages/portal_dashboard.php" -remotePath "public_html/pages/portal_dashboard.php"
Upload-FtpFile -localFile "pages/portal_red.php" -remotePath "public_html/pages/portal_red.php"
Upload-FtpFile -localFile "pages/portal_eventos.php" -remotePath "public_html/pages/portal_eventos.php"

# Subir config
Upload-FtpFile -localFile "config/config.php" -remotePath "public_html/config/config.php"

Write-Host ""
Write-Host "Archivos subidos. Probar:" -ForegroundColor Cyan
Write-Host "https://aratio.mrmtech.net/index.php?page=portal_login" -ForegroundColor Cyan

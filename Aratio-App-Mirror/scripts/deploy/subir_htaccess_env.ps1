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

Write-Host "Subiendo .htaccess y .env a produccion..." -ForegroundColor Yellow

# Subir .htaccess
Upload-FtpFile -localFile "htaccess_simple.txt" -remotePath "domains/aratio.mrmtech.net/public_html/.htaccess"

# Subir .env
Upload-FtpFile -localFile "env_production.txt" -remotePath "domains/aratio.mrmtech.net/public_html/.env"

Write-Host ""
Write-Host "Archivos subidos. Probar:" -ForegroundColor Cyan
Write-Host "https://aratio.mrmtech.net/index.php?page=portal_login" -ForegroundColor Cyan

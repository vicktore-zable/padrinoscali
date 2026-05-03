# Forzar actualizacion eliminando y subiendo de nuevo
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

function Delete-FtpFile {
    param([string]$remotePath)
    
    try {
        $ftpPath = "$ftpServer/$remotePath"
        $request = [System.Net.FtpWebRequest]::Create($ftpPath)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        
        $response = $request.GetResponse()
        $response.Close()
        Write-Host "Eliminado: $remotePath" -ForegroundColor Yellow
        return $true
    }
    catch {
        Write-Host "Error eliminando: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

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
        Write-Host "Subido: $remotePath" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "Error subiendo: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

$basePath = "domains/aratio.mrmtech.net/public_html"

Write-Host "Eliminando archivos antiguos..." -ForegroundColor Cyan

# Eliminar archivos antiguos
Delete-FtpFile -remotePath "$basePath/pages/portal_login.php"
Delete-FtpFile -remotePath "$basePath/pages/portal_landing.php"

Write-Host ""
Write-Host "Subiendo archivos nuevos..." -ForegroundColor Cyan

# Subir archivos nuevos
Upload-FtpFile -localFile "pages/portal_login.php" -remotePath "$basePath/pages/portal_login.php"
Upload-FtpFile -localFile "pages/portal_landing.php" -remotePath "$basePath/pages/portal_landing.php"

Write-Host ""
Write-Host "Probar: https://aratio.mrmtech.net/index.php?page=portal_login" -ForegroundColor Cyan

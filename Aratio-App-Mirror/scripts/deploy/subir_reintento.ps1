# Script de reintento de subida con verificación
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
        
        $fileBytes = [System.IO.File]::ReadAllBytes($localFile)
        $request.ContentLength = $fileBytes.Length
        
        $stream = $request.GetRequestStream()
        $stream.Write($fileBytes, 0, $fileBytes.Length)
        $stream.Close()
        
        $response = $request.GetResponse()
        $response.Close()
        Write-Host "OK: $remotePath" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "X Error en $remotePath : $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

Write-Host "--- Iniciando Subida de Reintento ---" -ForegroundColor Yellow

# Subir a la raíz pública del dominio
$remotePrefix = "domains/aratio.mrmtech.net/public_html"

Upload-FtpFile -localFile "debug_path.php" -remotePath "$remotePrefix/debug_path.php"
Upload-FtpFile -localFile "api/colaboradores.php" -remotePath "$remotePrefix/api/colaboradores.php"
Upload-FtpFile -localFile "pages/colaboradores.php" -remotePath "$remotePrefix/pages/colaboradores.php"
Upload-FtpFile -localFile "pages/colaboradores_red.php" -remotePath "$remotePrefix/pages/colaboradores_red.php"

Write-Host "--- Proceso Terminado ---" -ForegroundColor Cyan

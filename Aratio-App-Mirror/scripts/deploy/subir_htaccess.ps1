# Script para subir .htaccess
Write-Host "SUBIENDO .htaccess" -ForegroundColor Cyan

$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

function Upload-FtpFile {
    param([string]$localPath, [string]$remotePath)
    if (!(Test-Path $localPath)) { Write-Host "X Local no existe: $localPath" -ForegroundColor Red; return $false }
    try {
        $uri = "$ftpServer/$remotePath"
        $request = [System.Net.FtpWebRequest]::Create($uri)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $request.UseBinary = $true
        $content = [System.IO.File]::ReadAllBytes($localPath)
        $request.ContentLength = $content.Length
        $s = $request.GetRequestStream(); $s.Write($content, 0, $content.Length); $s.Close()
        $r = $request.GetResponse(); $r.Close()
        Write-Host "OK Subido: $remotePath" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "X Error subiendo $remotePath : $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

Upload-FtpFile -localPath ".htaccess" -remotePath "domains/aratio.mrmtech.net/public_html/.htaccess"

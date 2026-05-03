# Script para descargar y leer public_html/index.php
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"
$remoteFile = "public_html/index.php"
$localFile = "temp_index_public.php"

try {
    $request = [System.Net.FtpWebRequest]::Create("$ftpServer/$remoteFile")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    
    $response = $request.GetResponse()
    $stream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $content = $reader.ReadToEnd()
    
    Set-Content -Path $localFile -Value $content
    
    Write-Host "Contenido de public_html/index.php:"
    Write-Host $content -ForegroundColor Cyan
    
    $reader.Close()
    $response.Close()
} catch {
    Write-Host "Error descargando: $($_.Exception.Message)"
}

# Script para listar directorio FTP Middleware
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"
$path = "domains/aratio.mrmtech.net/public_html/mod_colab/src/Middleware/"

try {
    $uri = "$ftpServer/$path"
    $request = [System.Net.FtpWebRequest]::Create($uri)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectoryDetails
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    
    $response = $request.GetResponse()
    $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
    $content = $reader.ReadToEnd()
    $reader.Close()
    $response.Close()
    
    Write-Host "Contenido de ${path}:"
    Write-Host $content
} catch {
    Write-Host "Error listando: $($_.Exception.Message)"
}

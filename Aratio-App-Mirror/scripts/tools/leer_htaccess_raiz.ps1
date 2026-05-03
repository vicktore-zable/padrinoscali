# Leer el .htaccess del raiz del FTP
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

try {
    $request = [System.Net.FtpWebRequest]::Create("$ftpServer/.htaccess")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true
    
    $response = $request.GetResponse()
    $stream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $content = $reader.ReadToEnd()
    $reader.Close()
    $response.Close()
    
    Write-Host "Contenido de .htaccess en el raiz:" -ForegroundColor Yellow
    Write-Host $content
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
}

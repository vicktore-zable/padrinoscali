# Listar archivos en el directorio domains/aratio.mrmtech.net/
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

try {
    $request = [System.Net.FtpWebRequest]::Create("$ftpServer/domains/aratio.mrmtech.net/")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectoryDetails
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true
    
    $response = $request.GetResponse()
    $stream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $content = $reader.ReadToEnd()
    $reader.Close()
    $response.Close()
    
    Write-Host "Contenido de domains/aratio.mrmtech.net/:" -ForegroundColor Yellow
    Write-Host $content
} catch {
    Write-Host "Error: $_"
}

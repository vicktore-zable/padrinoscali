# Eliminar index.html del raiz del FTP
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

try {
    $request = [System.Net.FtpWebRequest]::Create("$ftpServer/index.html")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    
    $response = $request.GetResponse()
    $response.Close()
    
    Write-Host "index.html eliminado del raiz" -ForegroundColor Green
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
}

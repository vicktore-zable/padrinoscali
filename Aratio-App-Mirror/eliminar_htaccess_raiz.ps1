# Eliminar el .htaccess del raiz que esta interfiriendo
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

try {
    $request = [System.Net.FtpWebRequest]::Create("$ftpServer/.htaccess")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    
    $response = $request.GetResponse()
    $response.Close()
    
    Write-Host ".htaccess del raiz eliminado" -ForegroundColor Green
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
}

# Subir archivo de diagnostico simple
$ftpServer = "ftp://212.1.208.241"
$username = "u156469157.aratio.mrmtech.net"
$password = "sthLX6bJPoGh"

$localFile = "diag_simple.php"
$remotePath = "domains/aratio.mrmtech.net/public_html/diag_simple.php"

Write-Host "Subiendo $localFile a produccion..."

try {
    $webRequest = [System.Net.WebRequest]::Create("$ftpServer/$remotePath")
    $webRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $webRequest.Credentials = New-Object System.Net.NetworkCredential($username, $password)
    $webRequest.UseBinary = $true
    
    $fileContent = [System.IO.File]::ReadAllBytes($localFile)
    $webRequest.ContentLength = $fileContent.Length
    
    $requestStream = $webRequest.GetRequestStream()
    $requestStream.Write($fileContent, 0, $fileContent.Length)
    $requestStream.Close()
    
    $response = $webRequest.GetResponse()
    Write-Host "OK: $remotePath"
    $response.Close()
    
    Write-Host "`nProbar: https://aratio.mrmtech.net/diag_simple.php"
} catch {
    Write-Host "Error: $_"
}

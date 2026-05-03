$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"
$remotePath = "domains/aratio.mrmtech.net/public_html/index.php"

try {
    $request = [System.Net.FtpWebRequest]::Create("$ftpServer/$remotePath")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true
    
    $response = $request.GetResponse()
    $stream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $content = $reader.ReadToEnd()
    $reader.Close()
    $response.Close()
    
    Write-Host "---INDEX START---"
    Write-Host $content
    Write-Host "---INDEX END---"
} catch {
    Write-Host "Error: $_"
}

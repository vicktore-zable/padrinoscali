$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"
$remotePath = "domains/aratio.mrmtech.net/public_html/version_fix_test.txt"

try {
    $request = [System.Net.FtpWebRequest]::Create("$ftpServer/$remotePath")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true
    
    $content = [System.Text.Encoding]::UTF8.GetBytes("TEST_ALIVE_V1")
    $request.ContentLength = $content.Length
    
    $stream = $request.GetRequestStream()
    $stream.Write($content, 0, $content.Length)
    $stream.Close()
    
    $response = $request.GetResponse()
    $response.Close()
    Write-Host "OK"
} catch {
    Write-Host "Error: $_"
}

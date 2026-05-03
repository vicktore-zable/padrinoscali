$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

function Upload-FtpFile {
    param([string]$localFile, [string]$remotePath)
    $ftpPath = "$ftpServer/$remotePath"
    $request = [System.Net.FtpWebRequest]::Create($ftpPath)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true
    $request.KeepAlive = $false
    $fileContent = [System.IO.File]::ReadAllBytes($localFile)
    $request.ContentLength = $fileContent.Length
    $stream = $request.GetRequestStream()
    $stream.Write($fileContent, 0, $fileContent.Length)
    $stream.Close()
    $response = $request.GetResponse()
    $response.Close()
    return $true
}

Upload-FtpFile -localFile "error_test.php" -remotePath "domains/aratio.mrmtech.net/public_html/error_test.php"
Write-Host "Probar: https://aratio.mrmtech.net/error_test.php" -ForegroundColor Cyan

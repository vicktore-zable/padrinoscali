$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

try {
    $request = [System.Net.FtpWebRequest]::Create("$ftpServer/")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectoryDetails
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true
    
    $response = $request.GetResponse()
    $stream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $content = $reader.ReadToEnd()
    $reader.Close()
    $response.Close()
    
    [System.IO.File]::WriteAllText("h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\full_root_list.txt", $content, [System.Text.Encoding]::UTF8)
    Write-Host "OK"
} catch {
    Write-Host "Error: $_"
}

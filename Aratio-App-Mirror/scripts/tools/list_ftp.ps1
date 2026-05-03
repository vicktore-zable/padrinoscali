$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

function List-FtpDir {
    param([string]$remoteDir)
    try {
        $ftpPath = "$ftpServer/$remoteDir"
        $request = [System.Net.FtpWebRequest]::Create($ftpPath)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        
        $response = $request.GetResponse()
        $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
        $content = $reader.ReadToEnd()
        $reader.Close()
        $response.Close()
        
        Write-Host "Listing: $remoteDir" -ForegroundColor Cyan
        Write-Host $content
    } catch {
        Write-Host "Error listing $remoteDir: $($_.Exception.Message)" -ForegroundColor Red
    }
}

List-FtpDir ""
List-FtpDir "mod_elecciones"

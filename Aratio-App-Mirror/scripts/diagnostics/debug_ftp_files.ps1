$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net" 
$ftpPassword = "sthLX6bJPoGh"

function List-FtpDirectory {
    param([string]$remotePath)
    try {
        $ftpPath = "$ftpServer/$remotePath"
        $request = [System.Net.FtpWebRequest]::Create($ftpPath)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        
        $response = $request.GetResponse()
        $stream = $response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($stream)
        $content = $reader.ReadToEnd()
        $reader.Close()
        $response.Close()
        
        Write-Host "Contenido de $remotePath`:"
        Write-Host $content
    } catch {
        Write-Host "Error en $remotePath`: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "--- mod_colab/src/Views/ ---"
List-FtpDirectory "mod_colab/src/Views/"




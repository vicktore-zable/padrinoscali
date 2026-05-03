# Leer el contenido de index.html en el raiz del FTP
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

try {
    $request = [System.Net.FtpWebRequest]::Create("$ftpServer/index.html")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true
    
    $response = $request.GetResponse()
    $stream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $content = $reader.ReadToEnd()
    $reader.Close()
    $response.Close()
    
    Write-Host "Primeras 30 lineas de index.html en el raiz:" -ForegroundColor Yellow
    Write-Host "========================================"
    $lines = $content -split "`n"
    for ($i = 0; $i -lt [Math]::Min(30, $lines.Count); $i++) {
        Write-Host "$($i+1): $($lines[$i])"
    }
} catch {
    Write-Host "Error: $_"
}

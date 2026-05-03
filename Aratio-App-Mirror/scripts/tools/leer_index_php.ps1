# Leer el contenido de index.php en produccion
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

try {
    $request = [System.Net.FtpWebRequest]::Create("$ftpServer/domains/aratio.mrmtech.net/public_html/index.php")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true
    
    $response = $request.GetResponse()
    $stream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $content = $reader.ReadToEnd()
    $reader.Close()
    $response.Close()
    
    Write-Host "Primeras 50 lineas de index.php en produccion:" -ForegroundColor Yellow
    Write-Host "========================================"
    $lines = $content -split "`n"
    for ($i = 0; $i -lt [Math]::Min(50, $lines.Count); $i++) {
        Write-Host "$($i+1): $($lines[$i])"
    }
} catch {
    Write-Host "Error: $_"
}

# Leer los ultimos errores del log de PHP
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

try {
    # Leer el archivo php-errors.log
    $request = [System.Net.FtpWebRequest]::Create("$ftpServer/php-errors.log")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true
    
    $response = $request.GetResponse()
    $stream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $content = $reader.ReadToEnd()
    $reader.Close()
    $response.Close()
    
    # Mostrar las ultimas 50 lineas
    $lines = $content -split "`n"
    $totalLines = $lines.Count
    $startLine = [Math]::Max(0, $totalLines - 50)
    
    Write-Host "Ultimas 50 lineas de php-errors.log (total: $totalLines lineas):" -ForegroundColor Yellow
    Write-Host "========================================"
    for ($i = $startLine; $i -lt $totalLines; $i++) {
        Write-Host $lines[$i]
    }
} catch {
    Write-Host "Error: $_"
}

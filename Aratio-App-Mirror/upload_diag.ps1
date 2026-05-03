$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

$archivos = @("diag_dara.php", "diag_dara2.php", "diag_fix_dara.php")

foreach ($archivo in $archivos) {
    try {
        $ftpPath = "$ftpServer/$archivo"
        $request = [System.Net.FtpWebRequest]::Create($ftpPath)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $response = $request.GetResponse()
        $response.Close()
        Write-Host "ELIMINADO: $archivo" -ForegroundColor Green
    }
    catch {
        Write-Host "No encontrado (OK): $archivo" -ForegroundColor Yellow
    }
}
Write-Host "Limpieza completada." -ForegroundColor Cyan

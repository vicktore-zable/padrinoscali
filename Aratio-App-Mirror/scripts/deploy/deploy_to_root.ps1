# Script PowerShell para subir correcciones de jerarquía geográfica a la RAÍZ de Hostinger
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " CORRIGIENDO RUTA DE DESPLIEGUE (RAÍZ)" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

function Upload-FtpFile {
    param([string]$localFile, [string]$remotePath)
    try {
        $ftpPath = "$ftpServer/$remotePath"
        $request = [System.Net.FtpWebRequest]::Create($ftpPath)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $request.UseBinary = $true
        $request.KeepAlive = $false
        
        $content = [System.IO.File]::ReadAllBytes($localFile)
        $request.ContentLength = $content.Length
        
        $stream = $request.GetRequestStream()
        $stream.Write($content, 0, $content.Length)
        $stream.Close()
        
        Write-Host "OK: $remotePath" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "Error: $remotePath - $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

$archivos = @(
    @{L="pages/colaboradores.php"; R="pages/colaboradores.php"},
    @{L="pages/eventos.php"; R="pages/eventos.php"},
    @{L="pages/campanas.php"; R="pages/campanas.php"},
    @{L="pages/candidatos.php"; R="pages/candidatos.php"},
    @{L="pages/acciones.php"; R="pages/acciones.php"},
    @{L="pages/compromisos.php"; R="pages/compromisos.php"},
    @{L="registro_asistencia.php"; R="registro_asistencia.php"}
)

$exitos = 0
foreach ($a in $archivos) {
    if (Upload-FtpFile -localFile $a.L -remotePath $a.R) { $exitos++ }
}

Write-Host ""
Write-Host "Despliegue en RAÍZ finalizado: $exitos / $($archivos.Count) archivos subidos." -ForegroundColor Cyan

# Script PowerShell para subir correcciones de jerarquía geográfica a Hostinger
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " DESPLEGANDO CORRECCIONES GEOGRÁFICAS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Configuración FTP (Credenciales del sistema)
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"
$remoteBase = "domains/aratio.mrmtech.net/public_html"

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

# Archivos a subir
$archivos = @(
    @{L="pages/colaboradores.php"; R="$remoteBase/pages/colaboradores.php"},
    @{L="pages/eventos.php"; R="$remoteBase/pages/eventos.php"},
    @{L="pages/campanas.php"; R="$remoteBase/pages/campanas.php"},
    @{L="pages/candidatos.php"; R="$remoteBase/pages/candidatos.php"},
    @{L="pages/acciones.php"; R="$remoteBase/pages/acciones.php"},
    @{L="pages/compromisos.php"; R="$remoteBase/pages/compromisos.php"},
    @{L="registro_asistencia.php"; R="$remoteBase/registro_asistencia.php"}
)

$exitos = 0
foreach ($a in $archivos) {
    if (Upload-FtpFile -localFile $a.L -remotePath $a.R) { $exitos++ }
}

Write-Host ""
Write-Host "Despliegue finalizado: $exitos / $($archivos.Count) archivos subidos." -ForegroundColor Cyan

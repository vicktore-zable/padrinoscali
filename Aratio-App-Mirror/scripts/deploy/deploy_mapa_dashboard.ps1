# Script PowerShell para subir mapa, dashboard y eventos a Hostinger via FTP
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " DESPLIEGUE MAPA, DASHBOARD Y EVENTOS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configuración FTP (Credenciales extraídas de subir-a-hostinger.ps1)
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157" # Note: often the username is just u156469157 without the domain in some clients, but previous script used u156469157.aratio.mrmtech.net. I will try the one from the file first.
# Re-reading the file, it was: $ftpUser = "u156469157.aratio.mrmtech.net"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

# Base remota
$remoteBase = "public_html"

Write-Host "Servidor FTP: $ftpServer" -ForegroundColor Yellow
Write-Host "Usuario: $ftpUser" -ForegroundColor Yellow
Write-Host ""

# Función para subir archivo
function Upload-FtpFile {
    param(
        [string]$localFile,
        [string]$remotePath
    )
    
    if (!(Test-Path $localFile)) {
        Write-Host "X Archivo local no encontrado: $localFile" -ForegroundColor Red
        return $false
    }
    
    try {
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
        
        Write-Host "OK Subido: $remotePath" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "X Error subiendo: $remotePath" -ForegroundColor Red
        Write-Host "  Detalle: $($_.Exception.Message)" -ForegroundColor DarkRed
        return $false
    }
}

# Lista de archivos a subir
# Formato: @{Local="ruta\local"; Remote="ruta/remota"}
# CORRECCIÓN: Subir a la raíz del FTP donde está index.php para evitar el error 404 -> index -> loop
$archivos = @(
    @{Local="mapa_territorios.php"; Remote="mapa_territorios.php"},
    @{Local="api_territorios_verified.php"; Remote="api_territorios_verified.php"},
    @{Local="api_territorios_geojson.php"; Remote="api_territorios_geojson.php"},
    @{Local="api_puestos_standalone.php"; Remote="api_puestos_standalone.php"},
    @{Local="pages\dashboard.php"; Remote="pages/dashboard.php"},
    @{Local="pages\eventos.php"; Remote="pages/eventos.php"}
)

Write-Host "Iniciando carga de archivos..." -ForegroundColor Yellow

foreach ($archivo in $archivos) {
    Upload-FtpFile -localFile $archivo.Local -remotePath $archivo.Remote
}

Write-Host ""
Write-Host "Proceso finalizado." -ForegroundColor Cyan

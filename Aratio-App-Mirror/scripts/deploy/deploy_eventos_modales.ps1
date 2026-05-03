# Script PowerShell para desplegar modales de eventos a Hostinger via FTP
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " DESPLIEGUE MODALES EVENTOS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

Write-Host "Servidor FTP: $ftpServer" -ForegroundColor Yellow
Write-Host "Usuario: $ftpUser" -ForegroundColor Yellow
Write-Host ""

# Función para crear directorio
function Create-FtpDirectory {
    param(
        [string]$remotePath
    )
    try {
        $ftpPath = "$ftpServer/$remotePath"
        $request = [System.Net.FtpWebRequest]::Create($ftpPath)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $response = $request.GetResponse()
        $response.Close()
        Write-Host "OK Directorio creado: $remotePath" -ForegroundColor Green
        return $true
    } catch {
        # Ignorar error si ya existe (550)
        return $false
    }
}

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

# 1. Crear directorio pages/eventos
Create-FtpDirectory -remotePath "pages/eventos"

# 2. Subir archivos
$archivos = @(
    @{Local="pages\eventos\modales.php"; Remote="pages/eventos/modales.php"},
    @{Local="pages\eventos\modal_detalle.php"; Remote="pages/eventos/modal_detalle.php"}
)

Write-Host "Iniciando carga de archivos..." -ForegroundColor Yellow

foreach ($archivo in $archivos) {
    Upload-FtpFile -localFile $archivo.Local -remotePath $archivo.Remote
}

Write-Host ""
Write-Host "Proceso finalizado." -ForegroundColor Cyan

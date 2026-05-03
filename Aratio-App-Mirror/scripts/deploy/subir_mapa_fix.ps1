# Script PowerShell para subir arreglos del mapa a Hostinger via FTP
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " SUBIENDO ARREGLOS DE MAPA A HOSTINGER" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configuración FTP
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

Write-Host "Servidor FTP: $ftpServer" -ForegroundColor Yellow
Write-Host "Usuario: $ftpUser" -ForegroundColor Yellow
Write-Host ""

# Función para subir archivo
function Upload-FtpFile {
    param(
        [string]$localPath,
        [string]$remotePath
    )
    
    if (!(Test-Path $localPath)) {
        Write-Host "X Archivo no encontrado: $localPath" -ForegroundColor Red
        return $false
    }
    
    try {
        # Construir URI correcta.
        $uri = "$ftpServer/$remotePath"
        
        $request = [System.Net.FtpWebRequest]::Create($uri)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $request.UseBinary = $true
        $request.KeepAlive = $false
        
        $fileContent = [System.IO.File]::ReadAllBytes($localPath)
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
$archivos = @(
    @{Local="index.php"; Remote="domains/aratio.mrmtech.net/public_html/index.php"},
    @{Local="mod_colab\src\Views\public\mapa_territorios.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/src/Views/public/mapa_territorios.php"},
    @{Local="mod_colab\config\config.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/config/config.php"},
    @{Local="mod_colab\config\constants.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/config/constants.php"},
    @{Local="mod_colab\src\Controllers\AuthController.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/src/Controllers/AuthController.php"},
    @{Local="mod_colab\src\Controllers\HomeController.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/src/Controllers/HomeController.php"},
    @{Local="mod_colab\src\Middleware\AuthMiddleware.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/src/Middleware/AuthMiddleware.php"}
)

$exitosos = 0
$fallidos = 0

foreach ($archivo in $archivos) {
    # Resolver ruta absoluta local
    $localFile = $archivo.Local
    $remoteFile = $archivo.Remote
    
    Write-Host "Subiendo: $localFile..." -ForegroundColor Yellow
    
    if (Upload-FtpFile -localPath $localFile -remotePath $remoteFile) {
        $exitosos++
    } else {
        $fallidos++
    }
    Start-Sleep -Milliseconds 200
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " RESUMEN" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Exitosos: $exitosos" -ForegroundColor Green
Write-Host "Fallidos: $fallidos" -ForegroundColor Red

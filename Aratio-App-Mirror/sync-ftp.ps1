# Script PowerShell para descargar archivos desde FTP
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " DESCARGANDO ARCHIVOS DE PRODUCCION" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configuración FTP
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"
$localPath = ".\produccion_backup"

# Crear carpeta local si no existe
if (!(Test-Path $localPath)) {
    New-Item -ItemType Directory -Path $localPath | Out-Null
}

Write-Host "Conectando a FTP: $ftpServer" -ForegroundColor Yellow
Write-Host "Usuario: $ftpUser" -ForegroundColor Yellow
Write-Host ""

# Función para descargar archivo
function Download-FtpFile {
    param(
        [string]$ftpPath,
        [string]$localFile
    )
    
    try {
        $request = [System.Net.FtpWebRequest]::Create($ftpPath)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        
        $response = $request.GetResponse()
        $stream = $response.GetResponseStream()
        
        $fileStream = [System.IO.File]::Create($localFile)
        $buffer = New-Object byte[] 1024
        
        do {
            $read = $stream.Read($buffer, 0, 1024)
            $fileStream.Write($buffer, 0, $read)
        } while ($read -ne 0)
        
        $fileStream.Close()
        $stream.Close()
        $response.Close()
        
        Write-Host "✓ Descargado: $localFile" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "✗ Error descargando: $localFile - $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Función para listar directorio FTP
function Get-FtpDirectory {
    param([string]$ftpPath)
    
    try {
        $request = [System.Net.FtpWebRequest]::Create($ftpPath)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectoryDetails
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        
        $response = $request.GetResponse()
        $stream = $response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($stream)
        
        $files = @()
        while (!$reader.EndOfStream) {
            $line = $reader.ReadLine()
            $files += $line
        }
        
        $reader.Close()
        $stream.Close()
        $response.Close()
        
        return $files
    }
    catch {
        Write-Host "Error listando directorio: $ftpPath - $($_.Exception.Message)" -ForegroundColor Red
        return @()
    }
}

Write-Host "IMPORTANTE: La descarga completa puede tardar varios minutos..." -ForegroundColor Yellow
Write-Host ""
Write-Host "Archivos clave a descargar:" -ForegroundColor Cyan
Write-Host "  - index.php" -ForegroundColor White
Write-Host "  - .htaccess" -ForegroundColor White
Write-Host "  - src/ (Controllers, Models, Views)" -ForegroundColor White
Write-Host "  - routes/web.php" -ForegroundColor White
Write-Host "  - config/config.php" -ForegroundColor White
Write-Host ""

# Descargar archivos principales
$mainFiles = @(
    "index.php",
    ".htaccess",
    ".env"
)

foreach ($file in $mainFiles) {
    $ftpFile = "$ftpServer/$file"
    $localFile = Join-Path $localPath $file
    
    Write-Host "Descargando: $file..." -ForegroundColor Yellow
    Download-FtpFile -ftpPath $ftpFile -localFile $localFile
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " DESCARGA COMPLETADA" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Los archivos se encuentran en: $localPath" -ForegroundColor Green
Write-Host ""
Write-Host "NOTA: Para una sincronización completa, usa WinSCP o FileZilla" -ForegroundColor Yellow
Write-Host "y descarga toda la carpeta public_html" -ForegroundColor Yellow
Write-Host ""

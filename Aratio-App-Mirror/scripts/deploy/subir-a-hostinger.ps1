# Script PowerShell para subir archivos modificados a Hostinger via FTP
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " SUBIENDO ARCHIVOS A HOSTINGER" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configuración FTP
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"
$localBase = ".\mod_colab"

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
        Write-Host "X Archivo no encontrado: $localFile" -ForegroundColor Red
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

# Archivos a subir
$archivos = @(
    @{Local="src\Controllers\PublicController.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/src/Controllers/PublicController.php"},
    @{Local="src\Controllers\TerritorioController.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/src/Controllers/TerritorioController.php"},
    @{Local="src\Views\public\inscripcion_simpatizante.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/src/Views/public/inscripcion_simpatizante.php"},
    @{Local="src\Views\public\registro_lider.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/src/Views/public/registro_lider.php"},
    @{Local="routes\web.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/routes/web.php"}
)

Write-Host "Archivos a subir:" -ForegroundColor Cyan
foreach ($archivo in $archivos) {
    $fileName = Split-Path $archivo.Local -Leaf
    Write-Host "  - $fileName" -ForegroundColor White
}
Write-Host ""

$confirmacion = Read-Host "Desea continuar con la subida? (S/N)"
if ($confirmacion -ne "S" -and $confirmacion -ne "s") {
    Write-Host "Operacion cancelada." -ForegroundColor Yellow
    exit
}

Write-Host ""
Write-Host "Iniciando subida..." -ForegroundColor Yellow
Write-Host ""

$exitosos = 0
$fallidos = 0

foreach ($archivo in $archivos) {
    $localPath = Join-Path $localBase $archivo.Local
    $fileName = Split-Path $archivo.Local -Leaf
    Write-Host "Subiendo: $fileName..." -ForegroundColor Yellow
    
    if (Upload-FtpFile -localFile $localPath -remotePath $archivo.Remote) {
        $exitosos++
    } else {
        $fallidos++
    }
    Start-Sleep -Milliseconds 500
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " RESUMEN" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Exitosos: $exitosos" -ForegroundColor Green
if ($fallidos -gt 0) {
    Write-Host "Fallidos: $fallidos" -ForegroundColor Red
} else {
    Write-Host "Fallidos: $fallidos" -ForegroundColor Green
}
Write-Host ""

if ($exitosos -gt 0) {
    Write-Host "OK Archivos subidos correctamente a Hostinger" -ForegroundColor Green
    Write-Host ""
    Write-Host "IMPORTANTE: Ejecutar migracion en produccion:" -ForegroundColor Yellow
    Write-Host "https://aratio.mrmtech.net/setup/migrar-codmpio" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Luego probar:" -ForegroundColor Yellow
    Write-Host "- Registro Simpatizante: https://aratio.mrmtech.net/registro-simpatizante" -ForegroundColor Cyan
    Write-Host "- Registro Lider: https://aratio.mrmtech.net/registro-lider" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "Presione cualquier tecla para continuar..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

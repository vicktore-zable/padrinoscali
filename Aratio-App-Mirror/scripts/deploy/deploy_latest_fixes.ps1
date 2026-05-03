$ErrorActionPreference = "Stop"

$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

$baseDir = "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System"

function Upload-FtpFile {
    param([string]$localFile, [string]$remotePath)
    try {
        if (-Not (Test-Path $localFile)) {
            Write-Host "WARNING: Archivo local no encontrado: $localFile" -ForegroundColor Yellow
            return
        }

        $ftpPath = "$ftpServer/$remotePath"
        # Asegurar que los parent folders existan (simplificado: asumimos que directorios ya existen en el server)
        
        $request = [System.Net.FtpWebRequest]::Create($ftpPath)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $request.UseBinary = $true
        
        $content = [System.IO.File]::ReadAllBytes($localFile)
        $request.ContentLength = $content.Length
        
        $stream = $request.GetRequestStream()
        $stream.Write($content, 0, $content.Length)
        $stream.Close()
        
        Write-Host "OK: $remotePath" -ForegroundColor Green
    } catch {
        Write-Host "Error: $remotePath - $($_.Exception.Message)" -ForegroundColor Red
    }
}

$filesToDeploy = @(
    "login.php",
    "mod_colab/src/Views/components/sidebar.php",
    "mod_colab/src/Controllers/ColaboradorController.php",
    "mod_colab/routes/web.php",
    "mod_colab/src/Views/colaboradores/index.php",
    ".htaccess",
    "mod_colab/public/index.php",
    "index_new_root.php",
    "mod_lider/public/index.php",
    "mod_lider/routes/web.php",
    "mod_colab/src/Controllers/PublicController.php",
    "pages/colaboradores.php",
    "api_colaboradores_verified.php"
)

Write-Host "Iniciando despliegue de archivos actualizados a Hostinger..." -ForegroundColor Cyan

foreach ($file in $filesToDeploy) {
    $localPath = Join-Path $baseDir $file
    Upload-FtpFile -localFile $localPath -remotePath $file
}

Write-Host "Despliegue finalizado." -ForegroundColor Cyan

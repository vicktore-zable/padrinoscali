# Script PowerShell v2 para subir arreglos y crear carpetas
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " SUBIENDO MAPA FIX V2" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

function Create-FtpDirectory {
    param([string]$remotePath)
    try {
        $uri = "$ftpServer/$remotePath"
        $request = [System.Net.FtpWebRequest]::Create($uri)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $response = $request.GetResponse()
        $response.Close()
        Write-Host "OK Directorio creado: $remotePath" -ForegroundColor Green
        return $true
    } catch {
        # Ignorar error si ya existe (550) o reportar info
        Write-Host "Directorio quizas ya existe o error: $($_.Exception.Message)" -ForegroundColor Gray
        return $false
    }
}

function Upload-FtpFile {
    param([string]$localPath, [string]$remotePath)
    if (!(Test-Path $localPath)) { Write-Host "X Local no existe: $localPath" -ForegroundColor Red; return $false }
    try {
        $uri = "$ftpServer/$remotePath"
        $request = [System.Net.FtpWebRequest]::Create($uri)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $request.UseBinary = $true
        $content = [System.IO.File]::ReadAllBytes($localPath)
        $request.ContentLength = $content.Length
        $s = $request.GetRequestStream(); $s.Write($content, 0, $content.Length); $s.Close()
        $r = $request.GetResponse(); $r.Close()
        Write-Host "OK Subido: $remotePath" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "X Error subiendo $remotePath : $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# 1. Crear directorio Middleware si falta
Create-FtpDirectory -remotePath "domains/aratio.mrmtech.net/public_html/mod_colab/src/Middleware"

# 2. Subir Middlewares
$middlewares = @("AuthMiddleware.php", "CsrfMiddleware.php", "RateLimitMiddleware.php", "RoleMiddleware.php")
foreach ($m in $middlewares) {
    Upload-FtpFile -localPath "mod_colab\src\Middleware\$m" -remotePath "domains/aratio.mrmtech.net/public_html/mod_colab/src/Middleware/$m"
}

# 3. Subir Controllers (Reintento)
Upload-FtpFile -localPath "mod_colab\src\Controllers\AuthController.php" -remotePath "domains/aratio.mrmtech.net/public_html/mod_colab/src/Controllers/AuthController.php"
Upload-FtpFile -localPath "mod_colab\src\Controllers\HomeController.php" -remotePath "domains/aratio.mrmtech.net/public_html/mod_colab/src/Controllers/HomeController.php"

# 4. Otros archivos importantes
Upload-FtpFile -localPath "mod_colab\src\Views\public\mapa_territorios.php" -remotePath "domains/aratio.mrmtech.net/public_html/mod_colab/src/Views/public/mapa_territorios.php"
Upload-FtpFile -localPath "index.php" -remotePath "domains/aratio.mrmtech.net/public_html/index.php"

$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net" 
$ftpPassword = "sthLX6bJPoGh"

function Create-FtpDirectory {
    param([string]$remotePath)
    try {
        $ftpPath = "$ftpServer/$remotePath"
        $request = [System.Net.FtpWebRequest]::Create($ftpPath)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $response = $request.GetResponse()
        $response.Close()
        Write-Host "Directorio creado: $remotePath" -ForegroundColor Green
    } catch {
        # Si ya existe, ignoramos el error
        Write-Host "Aviso: Directorio $remotePath ya existe o error: $($_.Exception.Message)" -ForegroundColor Yellow
    }
}

function Upload-FtpFile {
    param([string]$localFile, [string]$remotePath)
    try {
        $ftpPath = "$ftpServer/$remotePath"
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
        Write-Host "Error en $remotePath`: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Crear carpetas necesarias
Create-FtpDirectory "mod_colab/src/Views/portal"

# Subir archivos faltantes
Write-Host "Resubiendo vistas del portal..." -ForegroundColor Cyan
Upload-FtpFile "mod_colab/src/Views/portal/landing.php" "mod_colab/src/Views/portal/landing.php"
Upload-FtpFile "mod_colab/src/Views/portal/dashboard.php" "mod_colab/src/Views/portal/dashboard.php"
Upload-FtpFile "mod_colab/src/Views/portal/network.php" "mod_colab/src/Views/portal/network.php"
Upload-FtpFile "mod_colab/src/Views/portal/events.php" "mod_colab/src/Views/portal/events.php"

# Verificar layouts también
Upload-FtpFile "mod_colab/src/Views/layouts/portal.php" "mod_colab/src/Views/layouts/portal.php"

Write-Host "Proceso finalizado."

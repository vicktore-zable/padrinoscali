# Subir archivos del portal al RAIZ del FTP (donde el servidor web realmente sirve)
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
        
        $fileContent = [System.IO.File]::ReadAllBytes($localFile)
        $request.ContentLength = $fileContent.Length
        
        $stream = $request.GetRequestStream()
        $stream.Write($fileContent, 0, $fileContent.Length)
        $stream.Close()
        
        $response = $request.GetResponse()
        $response.Close()
        Write-Host "OK: $remotePath" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

function Create-FtpDirectory {
    param([string]$remotePath)
    
    try {
        $ftpPath = "$ftpServer/$remotePath"
        $request = [System.Net.FtpWebRequest]::Create($ftpPath)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        
        $response = $request.GetResponse()
        $response.Close()
        Write-Host "Directorio creado: $remotePath" -ForegroundColor Yellow
    }
    catch {
        # El directorio probablemente ya existe
    }
}

Write-Host "Subiendo archivos al RAIZ del FTP..." -ForegroundColor Yellow

# Crear directorios necesarios
Create-FtpDirectory -remotePath "mod_lider"
Create-FtpDirectory -remotePath "mod_lider/src"
Create-FtpDirectory -remotePath "mod_lider/src/Controllers"
Create-FtpDirectory -remotePath "mod_lider/src/Models"
Create-FtpDirectory -remotePath "mod_lider/config"
Create-FtpDirectory -remotePath "config"
Create-FtpDirectory -remotePath "pages"

# Subir index.php actualizado
Upload-FtpFile -localFile "index.php" -remotePath "index.php"

# Subir paginas del portal
Upload-FtpFile -localFile "pages/portal_login.php" -remotePath "pages/portal_login.php"
Upload-FtpFile -localFile "pages/portal_landing.php" -remotePath "pages/portal_landing.php"
Upload-FtpFile -localFile "pages/portal_auth.php" -remotePath "pages/portal_auth.php"
Upload-FtpFile -localFile "pages/portal_dashboard.php" -remotePath "pages/portal_dashboard.php"
Upload-FtpFile -localFile "pages/portal_red.php" -remotePath "pages/portal_red.php"
Upload-FtpFile -localFile "pages/portal_eventos.php" -remotePath "pages/portal_eventos.php"

# Subir config principal
Upload-FtpFile -localFile "config/config.php" -remotePath "config/config.php"

# Subir mod_lider
Upload-FtpFile -localFile "mod_lider/src/bootstrap.php" -remotePath "mod_lider/src/bootstrap.php"
Upload-FtpFile -localFile "mod_lider/config/config.php" -remotePath "mod_lider/config/config.php"
Upload-FtpFile -localFile "mod_lider/config/database.php" -remotePath "mod_lider/config/database.php"

# Subir Controllers
Upload-FtpFile -localFile "mod_lider/src/Controllers/PortalAuthController.php" -remotePath "mod_lider/src/Controllers/PortalAuthController.php"
Upload-FtpFile -localFile "mod_lider/src/Controllers/LeaderPortalController.php" -remotePath "mod_lider/src/Controllers/LeaderPortalController.php"

# Subir Models
Upload-FtpFile -localFile "mod_lider/src/Models/Colaborador.php" -remotePath "mod_lider/src/Models/Colaborador.php"
Upload-FtpFile -localFile "mod_lider/src/Models/Evento.php" -remotePath "mod_lider/src/Models/Evento.php"
Upload-FtpFile -localFile "mod_lider/src/Models/Usuario.php" -remotePath "mod_lider/src/Models/Usuario.php"
Upload-FtpFile -localFile "mod_lider/src/Models/Campana.php" -remotePath "mod_lider/src/Models/Campana.php"
Upload-FtpFile -localFile "mod_lider/src/Models/Curriculum.php" -remotePath "mod_lider/src/Models/Curriculum.php"
Upload-FtpFile -localFile "mod_lider/src/Models/Territorio.php" -remotePath "mod_lider/src/Models/Territorio.php"

# Subir .htaccess
Upload-FtpFile -localFile "htaccess_simple.txt" -remotePath ".htaccess"

Write-Host ""
Write-Host "Archivos subidos. Probar:" -ForegroundColor Cyan
Write-Host "https://aratio.mrmtech.net/index.php?page=portal_login" -ForegroundColor Cyan

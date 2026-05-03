# Subir directorio Views
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

Write-Host "Subiendo directorio Views..." -ForegroundColor Yellow

# Crear estructura de directorios
Create-FtpDirectory -remotePath "mod_lider/src/Views"
Create-FtpDirectory -remotePath "mod_lider/src/Views/layouts"
Create-FtpDirectory -remotePath "mod_lider/src/Views/portal"
Create-FtpDirectory -remotePath "mod_lider/src/Views/portal/auth"

# Subir layouts
Upload-FtpFile -localFile "mod_lider/src/Views/layouts/portal_public.php" -remotePath "mod_lider/src/Views/layouts/portal_public.php"
Upload-FtpFile -localFile "mod_lider/src/Views/layouts/portal.php" -remotePath "mod_lider/src/Views/layouts/portal.php"

# Subir portal
Upload-FtpFile -localFile "mod_lider/src/Views/portal/dashboard.php" -remotePath "mod_lider/src/Views/portal/dashboard.php"
Upload-FtpFile -localFile "mod_lider/src/Views/portal/events.php" -remotePath "mod_lider/src/Views/portal/events.php"
Upload-FtpFile -localFile "mod_lider/src/Views/portal/landing.php" -remotePath "mod_lider/src/Views/portal/landing.php"
Upload-FtpFile -localFile "mod_lider/src/Views/portal/network.php" -remotePath "mod_lider/src/Views/portal/network.php"

# Subir portal/auth
Upload-FtpFile -localFile "mod_lider/src/Views/portal/auth/login.php" -remotePath "mod_lider/src/Views/portal/auth/login.php"
Upload-FtpFile -localFile "mod_lider/src/Views/portal/auth/register.php" -remotePath "mod_lider/src/Views/portal/auth/register.php"
Upload-FtpFile -localFile "mod_lider/src/Views/portal/auth/forgot-password.php" -remotePath "mod_lider/src/Views/portal/auth/forgot-password.php"
Upload-FtpFile -localFile "mod_lider/src/Views/portal/auth/reset-password.php" -remotePath "mod_lider/src/Views/portal/auth/reset-password.php"
Upload-FtpFile -localFile "mod_lider/src/Views/portal/auth/two-factor.php" -remotePath "mod_lider/src/Views/portal/auth/two-factor.php"

Write-Host ""
Write-Host "Probar: https://aratio.mrmtech.net/index.php?page=portal_login" -ForegroundColor Cyan

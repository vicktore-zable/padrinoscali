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
    }
}

function Upload-FtpFile {
    param([string]$localFile, [string]$remotePath)
    if (!(Test-Path $localFile)) { return }
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

# Crear estructura en servidor
Write-Host "Creando estructura mod_lider..." -ForegroundColor Cyan
Create-FtpDirectory "mod_lider"
Create-FtpDirectory "mod_lider/src"
Create-FtpDirectory "mod_lider/src/Controllers"
Create-FtpDirectory "mod_lider/src/Models"
Create-FtpDirectory "mod_lider/src/Core"
Create-FtpDirectory "mod_lider/src/Utils"
Create-FtpDirectory "mod_lider/src/Middleware"
Create-FtpDirectory "mod_lider/src/Views"
Create-FtpDirectory "mod_lider/src/Views/portal"
Create-FtpDirectory "mod_lider/src/Views/portal/auth"
Create-FtpDirectory "mod_lider/src/Views/layouts"
Create-FtpDirectory "mod_lider/routes"
Create-FtpDirectory "mod_lider/public"

# Subir archivos
Write-Host "Subiendo archivos..." -ForegroundColor Cyan
Get-ChildItem -Path "mod_lider/src" -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Replace((Get-Item .).FullName + "\", "").Replace("\", "/")
    Upload-FtpFile $_.FullName $relativePath
}

Get-ChildItem -Path "mod_lider/routes" -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Replace((Get-Item .).FullName + "\", "").Replace("\", "/")
    Upload-FtpFile $_.FullName $relativePath
}

Get-ChildItem -Path "mod_lider/public" -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Replace((Get-Item .).FullName + "\", "").Replace("\", "/")
    Upload-FtpFile $_.FullName $relativePath
}

# Subir pages actualizadas
Write-Host "Subiendo pages actualizadas..." -ForegroundColor Cyan
Upload-FtpFile "pages/portal_dashboard.php" "pages/portal_dashboard.php"
Upload-FtpFile "pages/portal_red.php" "pages/portal_red.php"
Upload-FtpFile "pages/portal_eventos.php" "pages/portal_eventos.php"
Upload-FtpFile "pages/portal_auth.php" "pages/portal_auth.php"
Upload-FtpFile "pages/portal_login.php" "pages/portal_login.php"
Upload-FtpFile "pages/portal_landing.php" "pages/portal_landing.php"

Write-Host "Despliegue de mod_lider finalizado." -ForegroundColor Green

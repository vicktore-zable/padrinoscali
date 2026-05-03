# Subir archivo Usuario.php
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

Write-Host "Subiendo Models restantes..." -ForegroundColor Yellow

# Subir Usuario.php
Upload-FtpFile -localFile "mod_lider/src/Models/Usuario.php" -remotePath "public_html/mod_lider/src/Models/Usuario.php"

# Subir Campana.php
Upload-FtpFile -localFile "mod_lider/src/Models/Campana.php" -remotePath "public_html/mod_lider/src/Models/Campana.php"

# Subir Curriculum.php
Upload-FtpFile -localFile "mod_lider/src/Models/Curriculum.php" -remotePath "public_html/mod_lider/src/Models/Curriculum.php"

# Subir Territorio.php
Upload-FtpFile -localFile "mod_lider/src/Models/Territorio.php" -remotePath "public_html/mod_lider/src/Models/Territorio.php"

Write-Host ""
Write-Host "Probar: https://aratio.mrmtech.net/index.php?page=portal_login" -ForegroundColor Cyan

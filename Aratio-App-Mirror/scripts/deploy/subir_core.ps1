# Subir directorio Core
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

Write-Host "Subiendo directorio Core..." -ForegroundColor Yellow

Create-FtpDirectory -remotePath "mod_lider/src/Core"
Upload-FtpFile -localFile "mod_lider/src/Core/Controller.php" -remotePath "mod_lider/src/Core/Controller.php"
Upload-FtpFile -localFile "mod_lider/src/Core/Router.php" -remotePath "mod_lider/src/Core/Router.php"

Write-Host ""
Write-Host "Probar: https://aratio.mrmtech.net/index.php?page=portal_login" -ForegroundColor Cyan

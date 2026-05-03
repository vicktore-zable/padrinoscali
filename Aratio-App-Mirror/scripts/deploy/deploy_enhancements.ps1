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

# Upload files
Upload-FtpFile "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_elecciones\get_filters.php" "debug_filters.php"
Upload-FtpFile "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_elecciones\get_filters.php" "mod_elecciones/get_filters.php"
Upload-FtpFile "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_elecciones\src\Views\dashboard.php" "mod_elecciones/src/Views/dashboard.php"
Upload-FtpFile "h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\index.html" "index.html"

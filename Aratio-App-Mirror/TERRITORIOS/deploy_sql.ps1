# Script PowerShell para subir el SQL de territorios a producción
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

function Upload-FtpFile {
    param([string]$localFile, [string]$remotePath)
    Write-Host "Subiendo $localFile a $remotePath..." -ForegroundColor Cyan
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
        
        Write-Host "¡ÉXITO: $remotePath subido!" -ForegroundColor Green
    } catch {
        Write-Host "ERROR: No se pudo subir $remotePath - $($_.Exception.Message)" -ForegroundColor Red
    }
}

# 1. Subir el archivo SQL (13.7MB)
Upload-FtpFile -localFile "TERRITORIOS/territorios_21022026.sql" -remotePath "territorios_21022026.sql"


# 2. Subir el script de importación
Upload-FtpFile -localFile "import_territorios_final.php" -remotePath "import_territorios_final.php"
Upload-FtpFile -localFile "import_territorios_2026.php" -remotePath "import_territorios_2026.php"


# 3. Subir el script de verificación
Upload-FtpFile -localFile "check_prod_count_tmp.php" -remotePath "check_prod_count_tmp.php"

# 4. Subir el script de limpieza
Upload-FtpFile -localFile "cleanup_prod_tmp.php" -remotePath "cleanup_prod_tmp.php"

# 5. Subir scripts de verificación y exportación
Upload-FtpFile -localFile "verify_geometry_tmp.php" -remotePath "verify_geometry_tmp.php"
Upload-FtpFile -localFile "api_check_geom_tmp.php" -remotePath "api_check_geom_tmp.php"
Upload-FtpFile -localFile "api_export_geojson_tmp.php" -remotePath "api_export_geojson_tmp.php"

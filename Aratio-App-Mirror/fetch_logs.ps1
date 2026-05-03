# Script PowerShell para descargar el log de errores de Hostinger
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"
$remoteFile = "error_log" # Usualmente PHP escribe aquí en Hostinger
$localFile = "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\prod_error_log.txt"

try {
    Write-Host "Iniciando descarga de $remoteFile..."
    $ftpPath = "$ftpServer/$remoteFile"
    $request = [System.Net.FtpWebRequest]::Create($ftpPath)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true

    $response = $request.GetResponse()
    $responseStream = $response.GetResponseStream()
    
    $fileStream = New-Object System.IO.FileStream($localFile, [System.IO.FileMode]::Create)
    $responseStream.CopyTo($fileStream)
    
    $fileStream.Close()
    $responseStream.Close()
    $response.Close()
    
    Write-Host "✅ OK: $remoteFile descargado correctamente a $localFile" -ForegroundColor Green
}
catch {
    Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Intentar descargar también logs de mod_colab a ver si hay algo allí
$remoteFile2 = "mod_colab/storage/logs/error.log"
$localFile2 = "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\prod_mod_colab_error.log"

try {
    Write-Host "Intentando descargar $remoteFile2..."
    $ftpPath = "$ftpServer/$remoteFile2"
    $request = [System.Net.FtpWebRequest]::Create($ftpPath)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true

    $response = $request.GetResponse()
    $responseStream = $response.GetResponseStream()
    
    $fileStream = New-Object System.IO.FileStream($localFile2, [System.IO.FileMode]::Create)
    $responseStream.CopyTo($fileStream)
    
    $fileStream.Close()
    $responseStream.Close()
    $response.Close()
    
    Write-Host "✅ OK: $remoteFile2 descargado" -ForegroundColor Green
}
catch {
    Write-Host "❌ Info: No se encontro mod_colab/storage/logs/error.log (puede estar vacío o no existir)" -ForegroundColor Yellow
}

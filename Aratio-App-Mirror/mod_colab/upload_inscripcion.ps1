$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

$archivos = @(
    @{Local="h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_colab\src\Controllers\PublicController.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/src/Controllers/PublicController.php"},
    @{Local="h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_colab\src\Views\public\inscripcion.php"; Remote="domains/aratio.mrmtech.net/public_html/mod_colab/src/Views/public/inscripcion.php"}
)

foreach ($archivo in $archivos) {
    $ftpPath = "$ftpServer/$($archivo.Remote)"
    Write-Host "Subiendo: $($archivo.Local) a $ftpPath"
    
    $request = [System.Net.FtpWebRequest]::Create($ftpPath)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UseBinary = $true
    
    $fileContent = [System.IO.File]::ReadAllBytes($archivo.Local)
    $request.ContentLength = $fileContent.Length
    
    $stream = $request.GetRequestStream()
    $stream.Write($fileContent, 0, $fileContent.Length)
    $stream.Close()
    
    try {
        $response = $request.GetResponse()
        Write-Host "OK Subido" -ForegroundColor Green
        $response.Close()
    } catch {
        Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

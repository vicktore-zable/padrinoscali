# Despliegue de Vistas Publicas
$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

$filesToUpload = @(
    @{
        Local  = "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_colab\src\Views\public\inscripcion_simpatizante.php"
        Remote = "mod_colab/src/Views/public/inscripcion_simpatizante.php"
    },
    @{
        Local  = "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_colab\src\Views\public\registro_lider.php"
        Remote = "mod_colab/src/Views/public/registro_lider.php"
    }
)

Write-Host "Realizando parche de Vistas (cambio v2.6.9)..."

foreach ($file in $filesToUpload) {
    try {
        $ftpPath = "$ftpServer/$($file.Remote)"
        $request = [System.Net.FtpWebRequest]::Create($ftpPath)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $request.UseBinary = $true

        $content = [System.IO.File]::ReadAllBytes($file.Local)
        $request.ContentLength = $content.Length

        $stream = $request.GetRequestStream()
        $stream.Write($content, 0, $content.Length)
        $stream.Close()
        Write-Host "✅ $($file.Remote) subido correctamente." -ForegroundColor Green
    }
    catch {
        Write-Host "❌ Error subiendo $($file.Remote): $($_.Exception.Message)" -ForegroundColor Red
    }
}

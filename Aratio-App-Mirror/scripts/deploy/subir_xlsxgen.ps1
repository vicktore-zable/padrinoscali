$user = "u156469157.aratio.mrmtech.net"
$pass = "sthLX6bJPoGh"
$hostinger = "ftp://212.1.208.241"

function Quick-Upload($file) {
    if (Test-Path $file) {
        echo "Subiendo $file..."
        # Usamos try/catch y curl para subir por FTP
        # Reemplazar \ con / para la ruta FTP en Windows
        $remotePath = $file.Replace('\', '/')
        # Crear directorios remotos si es necesario con ftp u otro metodo (curl base lo sube si el dir existe)
        $process = Start-Process -FilePath "curl.exe" -ArgumentList "--ftp-create-dirs", "-T", "`"$file`"", "$hostinger/$remotePath", "--user", "$user`:$pass" -Wait -PassThru
        if ($process.ExitCode -eq 0) {
            Write-Host "Correcto: $file" -ForegroundColor Green
        } else {
            Write-Host "Error subiendo $file" -ForegroundColor Red
        }
    } else {
        Write-Host "Archivo no existe: $file" -ForegroundColor Red
    }
}

Quick-Upload "includes\SimpleXLSXGen.php"
Quick-Upload "config\config.php"
Quick-Upload "api\colaboradores.php"

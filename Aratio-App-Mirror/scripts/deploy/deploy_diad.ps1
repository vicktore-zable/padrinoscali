# Deployment Script for mod_diaD
$FilesToUpload = @(
    "login.php",
    "mod_diaD\index.php",
    "mod_diaD\dashboard.php",
    "api\diaD_datos.php",
    "api\diaD_reportes.php"
)

Write-Host "Iniciando despliegue de archivos mod_diaD..." -ForegroundColor Cyan

foreach ($file in $FilesToUpload) {
    if (Test-Path "subir_reintento.ps1") {
        Write-Host "Subiendo $file..." -ForegroundColor Yellow
        .\subir_reintento.ps1 $file
    } else {
        Write-Host "Error: No se encuentra subir_reintento.ps1. Sube el archivo $file usando tu método FTP." -ForegroundColor Red
    }
}

Write-Host "Despliegue finalizado. Si hay cambios de BD, asegúrate que se hayan aplicado." -ForegroundColor Green

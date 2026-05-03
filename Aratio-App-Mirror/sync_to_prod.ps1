# Script para sincronizar local a producción
# Uso: .\sync_to_prod.ps1

$source = "F:\xampp2\htdocs\aratio\*"
$dest = "u577647812@157.173.208.254:/home/u577647812/domains/edisongiraldo.com/public_html/aratio/"

# Archivos modificados (desde el último commit)
$files = @(
    "pages\reportes.php",
    "index.php"
)

$pass = 'E=j$`01yHi^?XfpoM@|CD"5H4'

foreach ($file in $files) {
    $srcPath = "F:\xampp2\htdocs\aratio\$file"
    $dstPath = "u577647812@157.173.208.254:/home/u577647812/domains/edisongiraldo.com/public_html/aratio/$file"
    
    Write-Host "Subiendo $file..."
    & pscp -P 65002 -pw $pass $srcPath $dstPath
}

Write-Host "¡Completado!"
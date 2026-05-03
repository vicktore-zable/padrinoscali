$ftpServer = "ftp://212.1.208.241"
$ftpUser = "u156469157.aratio.mrmtech.net"
$ftpPassword = "sthLX6bJPoGh"

function Search-Ftp {
    param($path)
    try {
        $request = [System.Net.FtpWebRequest]::Create("$ftpServer/$path")
        $request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectoryDetails
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        
        $response = $request.GetResponse()
        $stream = $response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($stream)
        $lines = $reader.ReadToEnd() -split "`r`n"
        $reader.Close()
        $response.Close()
        
        foreach ($line in $lines) {
            if ($line -match "colaboradores.php") {
                Write-Host "ENCONTRADO en: $path -> $line"
            }
            if ($line -match "^d") {
                # Extraer nombre de carpeta (formato varía, tomamos el final)
                $parts = $line -split "\s+"
                $dirName = $parts[-1]
                if ($dirName -ne "." -and $dirName -ne "..") {
                    Search-Ftp "$path/$dirName"
                }
            }
        }
    } catch {
        # Ignorar errores de acceso
    }
}

Write-Host "Iniciando búsqueda de colaboradores.php..."
Search-Ftp ""

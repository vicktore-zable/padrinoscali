$ftp = "ftp://212.1.208.241"
$user = "u156469157.aratio.mrmtech.net"
$pass = "sthLX6bJPoGh"
$credentials = new-object System.Net.NetworkCredential($user, $pass)

function Create-Directory($remotePath) {
    if (!$remotePath) { return }
    $parts = $remotePath.Split("/")
    $currentPath = ""
    foreach ($part in $parts) {
        if (!$part) { continue }
        $currentPath = if ($currentPath) { "$currentPath/$part" } else { $part }
        $url = "$ftp/$currentPath"
        try {
            $request = [System.Net.FtpWebRequest]::Create($url)
            $request.Credentials = $credentials
            $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $response = $request.GetResponse()
            $response.Close()
            Write-Host "Created directory $currentPath"
        } catch {
            # Skip if exists
        }
    }
}

function Upload-File($localPath, $remotePath) {
    $remoteDir = [System.IO.Path]::GetDirectoryName($remotePath).Replace("\", "/")
    Create-Directory $remoteDir

    Write-Host "Uploading $localPath to $remotePath..."
    $url = "$ftp/$remotePath"
    try {
        $request = [System.Net.FtpWebRequest]::Create($url)
        $request.Credentials = $credentials
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        
        $fileContent = [System.IO.File]::ReadAllBytes($localPath)
        $request.ContentLength = $fileContent.Length
        
        $requestStream = $request.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()
        $requestStream.Dispose()
        
        $response = $request.GetResponse()
        $response.Close()
    } catch {
        Write-Error "Failed to upload $localPath : $_"
    }
}

function Upload-Directory($localDir, $remoteBase) {
    if (!(Test-Path $localDir)) { 
        Write-Warning "Local directory $localDir not found"
        return 
    }
    $localPathFull = (Get-Item $localDir).FullName
    $items = Get-ChildItem -Path $localDir -Recurse
    foreach ($item in $items) {
        if (!$item.PSIsContainer) {
            $relativePart = $item.FullName.Substring($localPathFull.Length).TrimStart("\").Replace("\", "/")
            $remotePath = if ($relativePart) { "$remoteBase/$relativePart" } else { $remoteBase }
            Upload-File $item.FullName $remotePath
        }
    }
}

# Fix .env first
Upload-File "mod_colab/.env.prod" "mod_colab/.env"

# Logic and Views
Upload-Directory "mod_colab/src" "mod_colab/src"
Upload-Directory "mod_colab/routes" "mod_colab/routes"
Upload-Directory "mod_colab/config" "mod_colab/config"

# Root files
Upload-File "index.php" "index.php"
Upload-File ".htaccess" ".htaccess"
Upload-File "mod_colab/public/index.php" "mod_colab/public/index.php"

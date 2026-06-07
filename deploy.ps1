param(
    [string]$FtpHost = "ftpupload.net",
    [string]$Username = "",
    [string]$Password = "",
    [string]$RemoteDir = "/htdocs",
    [string]$LocalDir = "simlab"
)

if (-not $Username -or -not $Password) {
    Write-Host "ERROR: FTP credentials not provided." -ForegroundColor Red
    exit 1
}

$localPath = (Resolve-Path $LocalDir).Path
$files = Get-ChildItem -Path $localPath -Recurse -File
$total = $files.Count
$ok = 0
$fail = 0
$created = @{}

Write-Host "Deploying $total files to ftp://$FtpHost$RemoteDir ..." -ForegroundColor Cyan
Write-Host ""

$webclient = New-Object System.Net.WebClient
$webclient.Credentials = New-Object System.Net.NetworkCredential($Username, $Password)

foreach ($file in $files) {
    $relative = $file.FullName.Substring($localPath.Length + 1)
    $remotePath = "$RemoteDir/$($relative.Replace('\', '/'))"
    $remoteDir = [System.IO.Path]::GetDirectoryName($remotePath).Replace('\', '/')

    # Create directory if not yet created
    if (-not $created.ContainsKey($remoteDir)) {
        $dirParts = $remoteDir.Split('/')
        $acc = ""
        foreach ($part in $dirParts) {
            if ([string]::IsNullOrEmpty($part)) { continue }
            $acc = "$acc/$part"
            if (-not $created.ContainsKey($acc)) {
                try {
                    $makeDir = [System.Net.WebRequest]::Create("ftp://$FtpHost$acc/")
                    $makeDir.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                    $makeDir.Credentials = New-Object System.Net.NetworkCredential($Username, $Password)
                    $makeDir.GetResponse() | Out-Null
                } catch { }
                $created[$acc] = $true
            }
        }
    }

    try {
        $webclient.UploadFile("ftp://$FtpHost$remotePath", "STOR", $file.FullName)
        Write-Host "  OK $relative" -ForegroundColor Green
        $ok++
    } catch {
        Write-Host "  FAIL $relative : $($_.Exception.Message)" -ForegroundColor Red
        $fail++
    }
}

$webclient.Dispose()
Write-Host ""
Write-Host "Done! $ok OK, $fail FAIL (of $total total)" -ForegroundColor Cyan

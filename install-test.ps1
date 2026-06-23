param(
    [string]$AdbPath = "C:\android-sdk\platform-tools\adb.exe",
    [string]$Repo = "laurmj4-eng/TALAbahan-system"
)

$ErrorActionPreference = "Stop"

# 1. Check ADB
if (-not (Test-Path $AdbPath)) {
    Write-Host "ADB not found at $AdbPath" -ForegroundColor Red
    exit 1
}
Write-Host "ADB found" -ForegroundColor Green

# 2. Check device
$devices = & $AdbPath devices
if (-not ($devices -match "device$")) {
    Write-Host "No device connected. Plug in your phone via USB and enable USB debugging." -ForegroundColor Red
    exit 1
}
Write-Host "Device connected" -ForegroundColor Green

# 3. Download latest APK from GitHub Actions
$token = $env:GH_TOKEN
if (-not $token) {
    $token = Read-Host "Enter GitHub Personal Access Token (classic, with repo scope)"
}

$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/vnd.github+json"
}

Write-Host "Fetching latest successful workflow run..." -ForegroundColor Cyan
$runs = Invoke-RestMethod -Uri "https://api.github.com/repos/$Repo/actions/runs?branch=main&status=success&per_page=1" -Headers $headers
if ($runs.total_count -eq 0) {
    Write-Host "No successful runs found." -ForegroundColor Red
    exit 1
}

$runId = $runs.workflow_runs[0].id
Write-Host "Run #$runId" -ForegroundColor Cyan

$artifacts = Invoke-RestMethod -Uri "https://api.github.com/repos/$Repo/actions/runs/$runId/artifacts" -Headers $headers
$artifact = $artifacts.artifacts | Where-Object { $_.name -eq "TALAbahan-app" } | Select-Object -First 1
if (-not $artifact) {
    Write-Host "APK artifact not found in latest run." -ForegroundColor Red
    exit 1
}

$tmpDir = Join-Path $env:TEMP "talabahan-apk"
if (-not (Test-Path $tmpDir)) { New-Item -ItemType Directory -Path $tmpDir -Force | Out-Null }
$zipPath = Join-Path $tmpDir "artifact.zip"
$apkPath = Join-Path $tmpDir "app-debug.apk"

Write-Host "Downloading artifact..." -ForegroundColor Cyan
Invoke-RestMethod -Uri $artifact.archive_download_url -Headers $headers -OutFile $zipPath

Write-Host "Extracting APK..." -ForegroundColor Cyan
Expand-Archive -Path $zipPath -DestinationPath $tmpDir -Force

$apk = Get-ChildItem $tmpDir -Recurse -Filter "*.apk" | Select-Object -First 1
if (-not $apk) {
    Write-Host "No APK found in artifact." -ForegroundColor Red
    exit 1
}

# 4. Install via ADB
Write-Host "Installing $($apk.Name) ..." -ForegroundColor Cyan
& $AdbPath install -r $apk.FullName

if ($LASTEXITCODE -eq 0) {
    Write-Host "Install successful!" -ForegroundColor Green
} else {
    Write-Host "Install failed." -ForegroundColor Red
}

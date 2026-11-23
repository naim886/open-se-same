param(
    [int]$SleepSeconds = 3,
    [int]$Tries = 3,
    [int]$Timeout = 90,
    [int]$MaxTime = 3600,
    [int]$Memory = 512
)

$ErrorActionPreference = 'Continue'

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

# Prefer PHP from PATH, otherwise fall back to the newest Laragon PHP install.
$php = (Get-Command php -ErrorAction SilentlyContinue).Source

if (-not $php) {
    $php = Get-ChildItem 'C:\laragon\bin\php' -Directory -ErrorAction SilentlyContinue |
        Sort-Object Name -Descending |
        ForEach-Object { Join-Path $_.FullName 'php.exe' } |
        Where-Object { Test-Path $_ } |
        Select-Object -First 1
}

if (-not $php) {
    throw 'PHP executable not found. Install PHP or add it to PATH.'
}

$logDir = Join-Path $projectRoot 'storage\logs'
if (-not (Test-Path $logDir)) {
    New-Item -ItemType Directory -Path $logDir | Out-Null
}

$logFile = Join-Path $logDir 'queue-worker.log'

# Ask any previously running worker to stop so new code and settings are used.
& $php artisan queue:restart *>> $logFile

while ($true) {
    $startedAt = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    "[$startedAt] Starting queue worker..." | Out-File -FilePath $logFile -Append -Encoding utf8

    & $php artisan queue:work `
        --sleep=$SleepSeconds `
        --tries=$Tries `
        --timeout=$Timeout `
        --max-time=$MaxTime `
        --memory=$Memory *>> $logFile

    $stoppedAt = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    "[$stoppedAt] Queue worker exited. Restarting in 5 seconds..." | Out-File -FilePath $logFile -Append -Encoding utf8
    Start-Sleep -Seconds 5
}

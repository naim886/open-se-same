$ErrorActionPreference = 'Continue'

$taskName = 'OpenSesame Queue Worker'
$startupFolder = [Environment]::GetFolderPath('Startup')
$shortcutPath = Join-Path $startupFolder 'OpenSesame Queue Worker.lnk'

Stop-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
Unregister-ScheduledTask -TaskName $taskName -Confirm:$false -ErrorAction SilentlyContinue

if (Test-Path $shortcutPath) {
    Remove-Item -Path $shortcutPath -Force -ErrorAction SilentlyContinue
    Write-Host "Removed Startup shortcut: $shortcutPath"
}

# Stop worker processes started by this project.
Get-CimInstance Win32_Process -ErrorAction SilentlyContinue |
    Where-Object {
        ($_.Name -eq 'powershell.exe' -and $_.CommandLine -like '*queue-worker.ps1*') -or
        ($_.Name -eq 'php.exe' -and $_.CommandLine -like '*artisan queue:work*')
    } |
    ForEach-Object {
        Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
    }

Write-Host "Removed automatic queue worker configuration."

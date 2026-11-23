$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$workerScript = Join-Path $PSScriptRoot 'queue-worker.ps1'
$taskName = 'OpenSesame Queue Worker'
$startupFolder = [Environment]::GetFolderPath('Startup')
$shortcutPath = Join-Path $startupFolder 'OpenSesame Queue Worker.lnk'

if (-not (Test-Path $workerScript)) {
    throw "Worker script not found: $workerScript"
}

$arguments = "-NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File `"$workerScript`""

$taskCreated = $false

try {
    $action = New-ScheduledTaskAction `
        -Execute 'powershell.exe' `
        -Argument $arguments `
        -WorkingDirectory $projectRoot

    $trigger = New-ScheduledTaskTrigger -AtLogOn

    $settings = New-ScheduledTaskSettingsSet `
        -AllowStartIfOnBatteries `
        -DontStopIfGoingOnBatteries `
        -StartWhenAvailable `
        -MultipleInstances IgnoreNew `
        -RestartCount 999 `
        -RestartInterval (New-TimeSpan -Minutes 1)

    Register-ScheduledTask `
        -TaskName $taskName `
        -Action $action `
        -Trigger $trigger `
        -Settings $settings `
        -Description 'Runs the Laravel queue worker automatically for OpenSesame BD.' `
        -Force | Out-Null

    Start-ScheduledTask -TaskName $taskName
    $taskCreated = $true

    Write-Host "Installed and started scheduled task: $taskName"
} catch {
    Write-Warning "Scheduled task could not be created: $($_.Exception.Message)"
    Write-Host 'Falling back to the Windows Startup folder...'

    $shell = New-Object -ComObject WScript.Shell
    $shortcut = $shell.CreateShortcut($shortcutPath)
    $shortcut.TargetPath = 'powershell.exe'
    $shortcut.Arguments = $arguments
    $shortcut.WorkingDirectory = $projectRoot
    $shortcut.WindowStyle = 7
    $shortcut.Description = 'Runs the Laravel queue worker automatically for OpenSesame BD.'
    $shortcut.Save()

    Start-Process -FilePath $shortcutPath

    Write-Host "Installed Startup shortcut: $shortcutPath"
}

Write-Host "Worker log: $(Join-Path $projectRoot 'storage\logs\queue-worker.log')"

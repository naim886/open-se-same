# Queue Worker Setup

The application uses the `database` queue driver:

```env
QUEUE_CONNECTION=database
```

Jobs are stored in the `jobs` table until a worker processes them. Meta Conversions API events, order notifications and other queued work will not run without a worker.

## Linux / EasyPanel (Docker)

The production image runs Supervisor, and `docker/supervisor.conf` starts two queue workers automatically:

```ini
[program:laravel-queue]
numprocs=2
autostart=true
autorestart=true
startsecs=1
stopasgroup=true
killasgroup=true
stopwaitsecs=3600
redirect_stderr=true
process_name=%(program_name)s_%(process_num)02d
stdout_logfile=/var/log/nginx/worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=3
command=php /var/www/artisan queue:work --sleep=3 --tries=3 --timeout=90 --max-time=3600 --memory=512
```

After changing the worker configuration, rebuild/redeploy the EasyPanel app so the new Supervisor config is copied into the container.

If EasyPanel is not using the Dockerfile's Supervisor command, add the same program to the server's Supervisor config (usually `/etc/supervisor/conf.d/laravel-worker.conf`) and run:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start laravel-queue:*
```

After deploying application code, restart the workers so they load the new code:

```bash
php artisan queue:restart
```

Supervisor will automatically start fresh workers.

## Windows / Laragon

Run this once from the project root:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\install-queue-worker.ps1
```

The installer first tries to create a Windows Scheduled Task named
**OpenSesame Queue Worker**. If Windows denies task creation (common without
elevation), it automatically creates a shortcut in your **Startup** folder
instead:

```text
%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\OpenSesame Queue Worker.lnk
```

Either method:

- starts automatically at user logon
- starts the worker immediately
- restarts the worker every hour (`--max-time=3600`)
- prevents duplicate worker instances
- writes output to `storage\logs\queue-worker.log`

Check the worker processes:

```powershell
Get-CimInstance Win32_Process |
  Where-Object {
    ($_.Name -eq 'powershell.exe' -and $_.CommandLine -like '*queue-worker.ps1*') -or
    ($_.Name -eq 'php.exe' -and $_.CommandLine -like '*artisan queue:work*')
  } |
  Select-Object ProcessId, Name, CommandLine
```

Check the log:

```powershell
Get-Content storage\logs\queue-worker.log -Tail 50
```

Restart manually:

```powershell
Stop-ScheduledTask -TaskName 'OpenSesame Queue Worker' -ErrorAction SilentlyContinue
Start-ScheduledTask -TaskName 'OpenSesame Queue Worker'
```

If the installer used the Startup shortcut, restart by closing the PowerShell
worker process and reopening the shortcut from the Startup folder, or run the
two commands below:

```powershell
Get-CimInstance Win32_Process |
  Where-Object { ($_.Name -eq 'powershell.exe' -and $_.CommandLine -like '*queue-worker.ps1*') -or ($_.Name -eq 'php.exe' -and $_.CommandLine -like '*artisan queue:work*') } |
  ForEach-Object { Stop-Process -Id $_.ProcessId -Force }

Start-Process "$env:APPDATA\Microsoft\Windows\Start Menu\Programs\Startup\OpenSesame Queue Worker.lnk"
```

Remove the automatic worker:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\uninstall-queue-worker.ps1
```

## Manual alternative

If you do not want a background service, run the worker in a terminal:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90 --max-time=3600 --memory=512
```

The worker only runs while the terminal is open.

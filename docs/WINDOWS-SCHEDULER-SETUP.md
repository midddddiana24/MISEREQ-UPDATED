# WVSU MIS Windows Scheduler Setup

This guide installs the Laravel scheduler on the Windows computer that will
host the WVSU MIS Service Request System. The scheduler processes queued PDFs,
creates scheduled backups, removes expired private data, and records the
system-health heartbeat.

## Before installation

Confirm that the client computer has:

- XAMPP, PHP, MySQL, and the MIS application installed.
- The application database configured and migrated.
- `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
- Database-backed queues and cache configured:

```dotenv
CACHE_DRIVER=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

- The application working normally when opened in a browser.

## 1. Update the scheduler script paths

Open:

```text
scripts\run-scheduler.ps1
```

Update `$phpPath` if PHP is installed in a different directory:

```powershell
$phpPath = 'C:\xampp\php\php.exe'
```

The script automatically treats the folder above `scripts` as the Laravel
project folder. The recommended project layout is:

```text
C:\xampp\htdocs\mis-service-request\
├── artisan
├── app\
├── scripts\
│   └── run-scheduler.ps1
└── storage\
```

If the client uses another XAMPP directory, use the actual PHP executable path.
You can locate it with:

```powershell
(Get-Command php.exe).Source
```

## 2. Test the script manually

Open PowerShell and run:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "C:\xampp\htdocs\mis-service-request\scripts\run-scheduler.ps1"
```

Adjust the project path when necessary. A successful execution creates or
updates:

```text
storage\logs\scheduler.log
```

The latest entry should contain:

```text
exit=0
```

## 3. Install using Windows Task Scheduler

Open **Task Scheduler** as an administrator, then:

1. Select **Create Task**.
2. Enter the name `WVSU MIS Laravel Scheduler`.
3. Select **Run whether user is logged on or not**.
4. Select **Run with highest privileges**.
5. Under **Triggers**, create a trigger:
   - Begin the task: **On a schedule**
   - Daily
   - Repeat task every: **1 minute**
   - For a duration of: **Indefinitely**
   - Enabled
6. Under **Actions**, select **Start a program**.
7. Program/script:

```text
powershell.exe
```

8. Add arguments:

```text
-NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File "C:\xampp\htdocs\mis-service-request\scripts\run-scheduler.ps1"
```

9. Under **Conditions**, clear **Start the task only if the computer is on AC
   power** when the host may run on battery.
10. Under **Settings**:
    - Enable **Run task as soon as possible after a scheduled start is missed**.
    - Set **If the task is already running** to **Do not start a new instance**.
    - Set the execution time limit to approximately five minutes.
11. Save the task and provide the Windows account password if requested.

For a dedicated campus server, use a restricted service account or the Windows
system account. Confirm that the selected account can read the project and
write to `storage`.

## 4. Command-line installation

Open PowerShell as an administrator and adjust the project path:

```powershell
schtasks /Create /F /SC MINUTE /MO 1 /RU SYSTEM /RL HIGHEST /TN "WVSU MIS Laravel Scheduler" /TR "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File C:\xampp\htdocs\mis-service-request\scripts\run-scheduler.ps1"
```

If the computer does not permit the system account, create the task through the
Task Scheduler interface and select a dedicated Windows account.

## 5. Verify the installation

Run the task:

```powershell
schtasks /Run /TN "WVSU MIS Laravel Scheduler"
```

Wait approximately ten seconds, then check:

```powershell
schtasks /Query /TN "WVSU MIS Laravel Scheduler" /FO LIST /V
Get-Content "C:\xampp\htdocs\mis-service-request\storage\logs\scheduler.log" -Tail 20
```

Confirm:

- Last Result is `0`.
- The next run time is approximately one minute later.
- `scheduler.log` contains a recent `exit=0`.
- Admin **Settings → System Operations** reports the scheduler as operational.

You can also run:

```powershell
php artisan schedule:list
php artisan queue:failed
```

## Pause, resume, or remove the task

Temporarily disable the scheduler:

```powershell
schtasks /Change /TN "WVSU MIS Laravel Scheduler" /Disable
```

Enable it again:

```powershell
schtasks /Change /TN "WVSU MIS Laravel Scheduler" /Enable
```

Stop only the currently running instance:

```powershell
schtasks /End /TN "WVSU MIS Laravel Scheduler"
```

Permanently remove it:

```powershell
schtasks /Delete /F /TN "WVSU MIS Laravel Scheduler"
```

Disabling is recommended when the application will be moved to another
computer. Deleting is only necessary when the old computer will never host the
system again.

## Troubleshooting

### Last Result is not 0

Run `scripts\run-scheduler.ps1` manually and inspect:

```text
storage\logs\scheduler.log
storage\logs\laravel.log
```

### Task stays queued

Disable the battery-only restriction and confirm that the selected Windows
account is allowed to run scheduled tasks.

### PHP executable not found

Update `$phpPath` in `scripts\run-scheduler.ps1` using the actual client-PC PHP
path.

### Jobs remain pending

Check:

```powershell
php artisan queue:failed
php artisan schedule:run
```

Also confirm that the `jobs`, `failed_jobs`, `cache`, and `cache_locks` database
tables exist.

### Scheduler works only while signed in

Edit the task as an administrator and select **Run whether user is logged on or
not**, using a dedicated service account or the Windows system account.

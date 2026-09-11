# Queue Worker Setup for Windows XAMPP

This document explains how to configure Laravel queue workers as a supervised daemon on Windows using Task Scheduler.

## Problem with Current Setup

The current implementation in `app/Console/Kernel.php` runs `queue:work` every minute via the scheduler:

```php
$schedule->command('queue:work database --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()
    ->onOneServer();
```

**Issues with this approach:**

1. ⚠️ Inefficient: Spawns a new process every minute instead of continuous worker
2. ⚠️ Fragile: If scheduler fails, queue processing stops
3. ⚠️ Slow: Task initialization overhead repeated 1440 times per day
4. ⚠️ Memory: Each process takes time to bootstrap Laravel framework
5. ⚠️ Lost jobs: If `queue:work` crashes, next job waits up to 1 minute

## Recommended Setup (Windows)

### Option A: PowerShell Script with Task Scheduler (Recommended)

#### Step 1: Create Task Scheduler Entry

1. Open **Task Scheduler** (Win+R → `taskschd.msc`)

2. Click **Create Basic Task**:
   - **Name:** `Laravel Queue Worker`
   - **Description:** Supervises Laravel queue workers for MISEREQ
   - **Trigger:** Choose "At startup"
   - **Action:** Choose "Start a program"
     - Program: `powershell.exe`
     - Arguments: `-ExecutionPolicy Bypass -File "c:\xampp_new\htdocs\mis-service-request\mis-service-request\scripts\queue-worker-supervisor.ps1"`
     - Start in: `c:\xampp_new\htdocs\mis-service-request\mis-service-request`
   - **Finish**

3. Edit the task:
   - Go to **Triggers** → Edit the trigger:
     - ✓ Repeat task every: **30 minutes** for **Indefinitely**
     - ✓ Stop if running longer than: **30 minutes** (forces restart)
   - Go to **Settings**:
     - ✓ "Run task as soon as possible after a scheduled start is missed"
     - ✓ "If the task fails, restart every: 5 minutes"
     - ✓ "Run with highest privileges"
   - Go to **General**:
     - ✓ "Run whether user is logged in or not"
     - ✓ User: `SYSTEM`

#### Step 2: Manual Start for Testing

Before relying on Task Scheduler, test manually:

```powershell
# Run as Administrator
cd "c:\xampp_new\htdocs\mis-service-request\mis-service-request"
powershell -ExecutionPolicy Bypass -File scripts\queue-worker-supervisor.ps1
```

Watch for messages like:

```
[2026-09-11 14:30:45] Queue Worker Supervisor starting with 1 worker(s)
[2026-09-11 14:30:45] Configuration: BasePath=c:\xampp_new\htdocs\mis-service-request\mis-service-request, RestartDelay=5s
[2026-09-11 14:30:47] Starting queue worker #1...
```

#### Step 3: Deploy Script

Before deploying new code, run the deploy script to restart workers:

```bash
cd c:\xampp_new\htdocs\mis-service-request\mis-service-request
scripts\deploy.bat
```

This will:

1. ✅ Clear all application caches
2. ✅ Run pending migrations
3. ✅ Regenerate cache (much faster than runtime)
4. ✅ **Restart queue workers** (critical for new code pickup)

**IMPORTANT:** Always run `deploy.bat` before deploying new code, or queue workers will run old code!

---

### Option B: NSSM (Non-Sucking Service Manager)

If you prefer Windows Services instead of Task Scheduler:

#### Step 1: Download NSSM

```powershell
# Download from https://nssm.cc/download
# Extract to c:\nssm\
```

#### Step 2: Create Service

```powershell
# Run as Administrator
cd c:\nssm\win64\
.\nssm.exe install LaravelQueueWorker powershell.exe `
  "-ExecutionPolicy Bypass -File c:\xampp_new\htdocs\mis-service-request\mis-service-request\scripts\queue-worker-supervisor.ps1"

# Start the service
.\nssm.exe start LaravelQueueWorker

# Verify
Get-Service LaravelQueueWorker
```

---

## Monitoring Queue Workers

### Check Queue Status

```bash
# See failed jobs
php artisan queue:failed

# Count pending jobs
php artisan tinker
>>> DB::table('jobs')->count()
=> 1234

# Retry failed jobs
php artisan queue:retry all

# Purge failed jobs
php artisan queue:flush
```

### View Logs

```bash
# Real-time log from supervisor
Get-Content -Path "storage\logs\queue-worker.log" -Wait

# Last 50 lines
Get-Content -Path "storage\logs\queue-worker.log" -Tail 50
```

### Manual Queue Processing (for testing)

```bash
# Process jobs one at a time
php artisan queue:work database --once

# Process with verbose output
php artisan queue:work database --verbose --tries=3 --timeout=90
```

---

## Configuration Reference

### queue-worker-supervisor.ps1 Parameters

```powershell
# Default (1 worker):
powershell -File scripts\queue-worker-supervisor.ps1

# With 2 parallel workers (for high volume):
powershell -File scripts\queue-worker-supervisor.ps1 -WorkerCount 2

# Custom settings:
powershell -File scripts\queue-worker-supervisor.ps1 `
  -WorkerCount 2 `
  -RestartDelaySeconds 10 `
  -BasePath "c:\xampp_new\htdocs\mis-service-request\mis-service-request"
```

### Queue Configuration (config/queue.php)

Current settings for database driver:

```php
'database' => [
    'driver' => 'database',
    'table' => 'jobs',
    'queue' => 'default',
    'retry_after' => 90,  // Retry job if not completed in 90 seconds
    'after_commit' => false,
]
```

**Recommended production settings:**

- `retry_after`: 90 seconds (for quick recovery)
- `tries`: 3 (automatically set in queue:work command)
- `timeout`: 90 seconds (prevents hanging jobs)

---

## Troubleshooting

### Queue Workers Not Starting

**Check logs:**

```powershell
Get-Content "storage\logs\queue-worker.log" -Tail 100
```

**Common issues:**

1. **"PHP executable not found"** → Update `$PhpPath` in supervisor script
2. **"Permission denied"** → Run Task Scheduler as Administrator
3. **"MySQL connection error"** → Check `.env` DATABASE settings
4. **"Queue driver not set"** → Verify `QUEUE_CONNECTION=database` in `.env`

### Jobs Not Processing

**Verify jobs are in queue:**

```bash
php artisan tinker
>>> DB::table('jobs')->get()
```

**Manually trigger worker:**

```bash
php artisan queue:work database --once --verbose
```

**Check Laravel logs:**

```powershell
Get-Content "storage\logs\laravel.log" -Tail 100
```

### Memory Leaks in Long-Running Workers

If supervisor log shows workers restarting frequently:

**Solution 1:** Set `--max-time` limit

```powershell
# Edit supervisor script, change to:
$Process.Arguments = "artisan queue:work database --sleep=3 --tries=3 --timeout=90 --max-time=3600"
```

**Solution 2:** Adjust restart frequency in Task Scheduler

- Restart every 30 minutes instead of indefinite

---

## Production Checklist

Before deploying to production:

- [ ] Run `scripts\deploy.bat` to restart workers
- [ ] Verify queue workers are running: `Get-Service LaravelQueueWorker` (if using NSSM)
- [ ] Check logs for errors: `tail -f storage\logs\queue-worker.log`
- [ ] Test a sample job: `php artisan tinker` → `dispatch(new YourJob())`
- [ ] Monitor for 10 minutes to ensure stable operation
- [ ] Set up Task Scheduler to run `deploy.bat` on server reboot

---

## Related Configuration Files

- [config/queue.php](../../config/queue.php) - Queue driver configuration
- [app/Console/Kernel.php](../../app/Console/Kernel.php) - Scheduler configuration (should NOT run queue:work)
- [scripts/queue-worker-supervisor.ps1](./queue-worker-supervisor.ps1) - Supervisor script
- [scripts/deploy.bat](./deploy.bat) - Deploy script with queue:restart

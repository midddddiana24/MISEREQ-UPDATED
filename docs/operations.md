# MIS Service Request Operations

## Production baseline

Use `APP_ENV=production` and `APP_DEBUG=false`. Serve the application through HTTPS and point the web server only at `public/`.

The repository defaults to database-backed cache, queues, and sessions so multiple application workers can share state. Run migrations before starting workers:

```powershell
php artisan migrate --force
php artisan optimize
php artisan queue:work database --tries=3 --timeout=120
php artisan schedule:work
```

On Windows production, configure the queue worker and `php artisan schedule:run` through Task Scheduler or a service wrapper. Only one scheduler should run per environment. Follow `docs/WINDOWS-SCHEDULER-SETUP.md` when moving the scheduler to the client computer.

## Backup and disaster recovery

Create and verify a backup:

```powershell
php artisan system:backup
php artisan system:restore mis-backup-YYYYMMDD-HHMMSS.zip
```

For a checksum-verified copy on a protected network share:

```powershell
php artisan system:backup --secondary="\\backup-server\wvsu-mis"
```

The second command validates only. Restoring live data is destructive and requires:

```powershell
php artisan down
php artisan system:restore mis-backup-YYYYMMDD-HHMMSS.zip --confirm
php artisan migrate --force
php artisan optimize
php artisan up
```

Backups are stored under `storage/app/backups`, exclude `.env`, and contain the database plus private and secure uploads. Copy completed archives to encrypted off-site storage. Test a restore on a separate machine at least quarterly.

Recommended targets:

- RPO: 24 hours with the default daily backup; reduce to one hour if campus usage requires it.
- RTO: four hours for a tested single-server recovery.
- Local retention: 30 days.
- Off-site retention: twelve monthly archives plus the latest seven daily archives.

Temporary security data is pruned weekly using configured retention periods. Preview the effect without deleting records:

```powershell
php artisan system:privacy-prune --dry-run
```

Administrative activity logs are append-only at the application layer and are not included in automatic privacy pruning.

## Health and monitoring

`GET /health` verifies database, cache, and private storage. Configure the reverse proxy or monitoring service to alert when it returns HTTP 503. Do not expose logs or environment variables through health responses.

Review these regularly:

```powershell
php artisan queue:failed
Get-Content storage\logs\laravel.log -Tail 200
php tests\load\smoke.php http://127.0.0.1:8000 100 10
php tests\responsive\source-audit.php
```

Before production launch, complete `docs/DEVICE-ACCEPTANCE-CHECKLIST.md` on
physical mobile, tablet, and campus laptop devices using the final HTTPS URL.

## Redis upgrade

Install a Redis server and the PHP `redis` extension before enabling Redis. Then set:

```dotenv
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_CLIENT=phpredis
```

Clear configuration and restart all PHP and queue workers afterward.

## High availability

The application supports separate `DB_READ_HOST` and `DB_WRITE_HOST` values with sticky reads. For multi-server deployment:

1. Terminate HTTPS at a reverse proxy or load balancer.
2. Deploy identical application releases and `APP_KEY` values to at least two nodes.
3. Use Redis or database-backed shared sessions, cache, and queues.
4. Move secure uploads to shared S3-compatible storage.
5. Use a managed MySQL primary and read replica with automated snapshots.
6. Run one scheduler and multiple supervised queue workers.
7. Keep backups in a different location and test failover and restore quarterly.

Configuration alone does not create high availability; the redundant servers, database replica, shared storage, monitoring, and operational testing must exist outside this repository.

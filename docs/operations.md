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

## Exception Argument Privacy

PHP must run with `zend.exception_ignore_args=On` so exception stack frames do
not retain function argument values such as passwords, tokens, or recovery
codes. This is a `php.ini` setting, not an application setting tracked by Git.

Set it manually in every environment, including each developer workstation,
staging, and production. For the XAMPP installation used by this project, the
active file is `C:\xampp_new\php\php.ini`:

```ini
zend.exception_ignore_args=On
```

After changing it, restart Apache/PHP and verify the active runtime:

```powershell
C:\xampp_new\php\php.exe --ini
C:\xampp_new\php\php.exe -i | Select-String zend.exception_ignore_args
```

The GitHub Actions CI workflow fails if the active PHP runtime does not report
this setting as `1` (`On`), preventing a silent privacy regression. Repeat the
same check in staging and production startup/health procedures because their
`php.ini` files are managed outside this repository.

## Known Security Debt

The installed Laravel 10 line is affected by `PKSA-mdq4-51ck-6kdq`, a CRLF
injection issue in Laravel's default email validation rule. The issue is fixed
in Laravel 11 and later; no Laravel 10.x patch is available. A full Laravel
framework upgrade is a separate, planned project and is not part of the current
remediation work.

The application currently uses the default `email` validation rule in:

- `RegisterRequest` for new account email addresses.
- `ProfileUpdateRequest` for profile email changes.
- `PasswordResetLinkController` for password-reset requests.
- `AdminSettingsController` for the configured support email.

The compensating-control review found that outgoing email subjects are either
server-generated or stored notification subjects, and user input is not passed
directly into email headers. Recipient and support email values are validated
before use in `to()` or `replyTo()`. Continue treating this as security debt
until the separately planned Laravel 11+ upgrade is completed.

## Sentry

The project uses `sentry/sentry-laravel` 4.27.0. Create a Sentry project at
https://sentry.io, copy its DSN, and set these values in the deployment `.env`
file only:

```dotenv
SENTRY_LARAVEL_DSN=https://examplePublicKey@o0.ingest.sentry.io/0
SENTRY_ENVIRONMENT=production
SENTRY_RELEASE=2026.09.12
SENTRY_SAMPLE_RATE=1.0
SENTRY_TRACES_SAMPLE_RATE=0.0
SENTRY_STATUS_CHECK_ENABLED=false
```

Never commit the real DSN. Sentry is disabled unless `APP_ENV` is `production`
or `staging` and `APP_DEBUG=false`. `send_default_pii` is disabled, and the
`before_send` callback removes request bodies, headers, cookies, authenticated
user data, and extra metadata before an event can be sent. It keeps the request
URL path, method, and non-sensitive query values. Breadcrumbs are retained for
debugging, but metadata keys matching password, token, secret, recovery code,
authorization, cookie, or session are removed recursively.

After deployment, verify configuration without sending an event:

```powershell
php artisan sentry:status
```

The command requires `SENTRY_STATUS_CHECK_ENABLED=true` and runs only in local
or testing environments. It sends one controlled status event, then reports
whether a DSN is configured, environment/release values, and that default PII
is disabled. Use the built-in `php artisan sentry:test` command for a basic SDK
connectivity check; review the resulting event in Sentry before disabling the
temporary verification flag.

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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AdminSystemOperationsController extends Controller
{
    public function __construct(private readonly AdminActivityLogger $activityLogger)
    {
    }

    public function index()
    {
        $checks = [
            'database' => $this->check(fn () => DB::selectOne('select 1 as healthy')?->healthy === 1),
            'cache' => $this->check(function () {
                Cache::put('admin:operations:health', now()->timestamp, 60);
                return (int) Cache::get('admin:operations:health') > 0;
            }),
            'storage' => $this->check(fn () => Storage::disk('secure')->exists('.health')
                || Storage::disk('secure')->put('.health', now()->toIso8601String())),
        ];

        $schedulerSeenAt = Cache::get('system:scheduler_last_seen');
        $schedulerDate = $schedulerSeenAt ? Carbon::parse($schedulerSeenAt) : null;
        $schedulerHealthy = $schedulerDate?->greaterThan(now()->subMinutes(5)) ?? false;

        $backupFiles = collect(File::glob(storage_path('app/backups/mis-backup-*.zip')))
            ->map(fn (string $path) => [
                'name' => basename($path),
                'size' => File::size($path),
                'created_at' => Carbon::createFromTimestamp(File::lastModified($path)),
            ])
            ->sortByDesc('created_at')
            ->values();

        return view('admin.system-operations', [
            'checks' => $checks,
            'schedulerSeenAt' => $schedulerDate,
            'schedulerHealthy' => $schedulerHealthy,
            'pendingJobs' => DB::table('jobs')->count(),
            'failedJobs' => DB::table('failed_jobs')->count(),
            'latestFailedJob' => DB::table('failed_jobs')->latest('id')->first(),
            'backups' => $backupFiles->take(8),
            'latestBackup' => $backupFiles->first(),
            'cacheDriver' => config('cache.default'),
            'queueDriver' => config('queue.default'),
            'sessionDriver' => config('session.driver'),
            'mailDriver' => config('mail.default'),
            'mailConfigured' => filled(config('mail.mailers.smtp.host'))
                && filled(config('mail.mailers.smtp.username'))
                && filled(config('mail.mailers.smtp.password')),
            'backupRetentionDays' => (int) config('backup.retention_days', 30),
            'secondaryBackupConfigured' => filled(config('backup.secondary_path')),
            'privacyRetention' => [
                'Login history' => (int) config('governance.login_audit_retention_days', 365),
                'Revoked sessions' => (int) config('governance.revoked_session_retention_days', 90),
                'Notifications' => (int) config('governance.notification_retention_days', 730),
            ],
        ]);
    }

    public function backup(Request $request)
    {
        $request->validateWithBag('backup', [
            'current_password' => ['required', 'current_password'],
        ]);

        $exitCode = Artisan::call('system:backup');
        if ($exitCode !== 0) {
            return back()->with('error', 'The backup could not be completed. Review the protected server log.');
        }

        $this->activityLogger->logAdministrative(
            'system_backup_created',
            'A verified system backup was created from the admin operations page.'
        );

        return back()->with('success', 'A verified database and secure-file backup was created.');
    }

    public function privacyPrune(Request $request)
    {
        $request->validateWithBag('privacy', [
            'current_password' => ['required', 'current_password'],
        ]);

        $exitCode = Artisan::call('system:privacy-prune');
        if ($exitCode !== 0) {
            return back()->with('error', 'Privacy retention could not be completed. Review the protected server log.');
        }

        $this->activityLogger->logAdministrative(
            'system_privacy_pruned',
            'Configured privacy-retention rules were applied from the admin operations page.'
        );

        return back()->with('success', 'Privacy-retention cleanup completed successfully.');
    }

    private function check(callable $callback): bool
    {
        try {
            return (bool) $callback();
        } catch (Throwable) {
            return false;
        }
    }
}

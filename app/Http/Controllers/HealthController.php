<?php

namespace App\Http\Controllers;

use App\Support\BackupArchiveValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthController extends Controller
{
    public function __construct(private readonly BackupArchiveValidator $archiveValidator)
    {
    }

    public function __invoke(): JsonResponse
    {
        $backupCheck = $this->checkBackupFreshness();

        $checks = [
            'database' => $this->check(fn() => DB::select('select 1')),
            'cache' => $this->check(function () {
                Cache::put('health:last_check', now()->toIso8601String(), 60);

                return Cache::get('health:last_check');
            }),
            'storage' => $this->check(fn() => Storage::disk('secure')->exists('.health') || Storage::disk('secure')->put('.health', now()->toIso8601String())),
            'backup_freshness' => $backupCheck['fresh'],
        ];

        $healthy = ! in_array(false, $checks, true);
        if (! $healthy && ! $backupCheck['fresh']) {
            Log::warning('Backup freshness check failed in health endpoint.', [
                'backup_age_hours' => $backupCheck['age_hours'],
                'backup_path' => $backupCheck['path'],
            ]);
        }

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
            'backup_age_hours' => $backupCheck['age_hours'],
            'backup_path' => $backupCheck['path'],
            'time' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function check(callable $callback): bool
    {
        try {
            $callback();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function checkBackupFreshness(): array
    {
        $backupDir = (string) config('backup.directory', storage_path('app/backups'));
        $latest = $this->archiveValidator->latestValid($backupDir);

        if ($latest === null) {
            return [
                'fresh' => false,
                'age_hours' => null,
                'path' => null,
            ];
        }

        $path = $latest['path'];
        $ageHours = max(0, (time() - $latest['modified_at']) / 3600);

        return [
            'fresh' => $path !== null && $ageHours !== null && $ageHours < 36,
            'age_hours' => $ageHours,
            'path' => $path,
        ];
    }
}

<?php

namespace App\Console\Commands;

use App\Support\BackupArchiveValidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Sentry\Severity;
use Sentry\State\Scope;
use Throwable;

use function Sentry\captureMessage;
use function Sentry\flush;
use function Sentry\withScope;

class VerifyBackupSafetyNet extends Command
{
    protected $signature = 'system:verify-backup
        {--health-url= : Absolute URL of the application health endpoint}
        {--max-age-hours=24 : Maximum acceptable age of the newest valid archive}
        {--backup-directory= : Backup directory override}
        {--report= : Report file path override}';

    protected $description = 'Validate backup freshness, query /health, and actively report failures';

    public function handle(BackupArchiveValidator $archiveValidator): int
    {
        $checkedAt = now();
        $maxAgeHours = max(1, (int) $this->option('max-age-hours'));
        $backupDirectory = (string) ($this->option('backup-directory') ?: config('backup.directory', storage_path('app/backups')));
        $healthUrl = (string) ($this->option('health-url') ?: config('backup.health_url'));
        $reportPath = (string) ($this->option('report') ?: storage_path(
            'logs/backup-automatic-verification-'.$checkedAt->format('Ymd').'.log'
        ));

        $latest = $archiveValidator->latestValid($backupDirectory);
        $latestAgeHours = $latest === null
            ? null
            : max(0, ($checkedAt->timestamp - $latest['modified_at']) / 3600);
        $latestWindow = $checkedAt->copy()->startOfDay()->addHours(2);
        if ($checkedAt->lt($latestWindow)) {
            $latestWindow->subDay();
        }
        $coversLatestWindow = $latest !== null && $latest['modified_at'] >= $latestWindow->timestamp;
        $validBackupFresh = $latestAgeHours !== null
            && $latestAgeHours <= $maxAgeHours
            && $coversLatestWindow;
        $coverageSource = match (true) {
            $latest === null || ! $coversLatestWindow => 'none',
            $latest['modified_at'] <= $latestWindow->copy()->addMinutes(15)->timestamp => 'scheduled_02_00_window',
            default => 'catch_up_or_manual',
        };

        $healthStatus = null;
        $healthBackupFreshness = null;
        $healthError = null;

        try {
            $response = Http::acceptJson()->timeout(20)->get($healthUrl);
            $healthStatus = $response->status();
            $healthBackupFreshness = data_get($response->json(), 'checks.backup_freshness') === true;
        } catch (Throwable $exception) {
            $healthError = $exception->getMessage();
        }

        $issues = [];
        if (! $validBackupFresh) {
            $issues[] = "No valid backup archive within {$maxAgeHours} hours covers the latest 02:00 window.";
        }
        if ($healthError !== null) {
            $issues[] = 'The health endpoint could not be reached.';
        } elseif ($healthBackupFreshness !== true) {
            $issues[] = 'The health endpoint reported backup_freshness=false.';
        }
        if ($healthStatus !== null && $healthStatus >= 500 && $healthBackupFreshness === true) {
            $issues[] = "The health endpoint returned HTTP {$healthStatus} for another degraded check.";
        }

        $report = [
            'checked_at' => $checkedAt->toIso8601String(),
            'status' => $issues === [] ? 'pass' : 'fail',
            'max_age_hours' => $maxAgeHours,
            'latest_required_window' => $latestWindow->toIso8601String(),
            'covers_latest_window' => $coversLatestWindow,
            'coverage_source' => $coverageSource,
            'latest_valid_archive' => $latest['path'] ?? null,
            'latest_valid_archive_time' => $latest === null
                ? null
                : date(DATE_ATOM, $latest['modified_at']),
            'latest_valid_archive_age_hours' => $latestAgeHours,
            'health_url' => $healthUrl,
            'health_http_status' => $healthStatus,
            'health_backup_freshness' => $healthBackupFreshness,
            'health_error' => $healthError,
            'issues' => $issues,
            'sentry_event_id' => null,
        ];

        File::ensureDirectoryExists(dirname($reportPath));

        if ($issues === []) {
            File::put($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
            Log::info('Daily backup verification passed.', $report);
            $this->info("Backup verification passed. Report: {$reportPath}");

            return self::SUCCESS;
        }

        $eventId = withScope(function (Scope $scope) use ($report) {
            $scope->setTag('monitor', 'backup-safety-net');
            $scope->setLevel(Severity::warning());
            $scope->setContext('backup_verification', [
                'checked_at' => $report['checked_at'],
                'max_age_hours' => $report['max_age_hours'],
                'health_http_status' => $report['health_http_status'],
                'health_backup_freshness' => $report['health_backup_freshness'],
                'issues' => $report['issues'],
            ]);
            return captureMessage('MISEREQ daily backup verification failed', Severity::warning());
        });
        flush();
        $report['sentry_event_id'] = $eventId?->__toString();
        File::put($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
        Log::warning('Daily backup verification failed.', $report);

        $this->error(implode(' ', $issues)." Report: {$reportPath}");

        return self::FAILURE;
    }
}

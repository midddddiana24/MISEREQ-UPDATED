<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class VerifyBackupSafetyNetTest extends TestCase
{
    public function test_verification_passes_for_a_recent_valid_archive_and_healthy_endpoint(): void
    {
        [$directory, $report] = $this->temporaryPaths();
        $archive = $directory.'/mis-backup-valid.zip';
        $this->createValidArchive($archive);
        touch($archive, now()->startOfDay()->addHours(3)->timestamp);
        Http::fake([
            'http://health.test/health' => Http::response([
                'checks' => ['backup_freshness' => true],
            ], 200),
        ]);

        try {
            $exitCode = Artisan::call('system:verify-backup', [
                '--health-url' => 'http://health.test/health',
                '--backup-directory' => $directory,
                '--report' => $report,
            ]);

            $result = json_decode((string) File::get($report), true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame(0, $exitCode, Artisan::output());
            $this->assertSame('pass', $result['status']);
            $this->assertTrue($result['covers_latest_window']);
            $this->assertSame('catch_up_or_manual', $result['coverage_source']);
            $this->assertTrue($result['health_backup_freshness']);
            $this->assertSame([], $result['issues']);
        } finally {
            File::deleteDirectory($directory);
            File::delete($report);
        }
    }

    public function test_verification_rejects_a_recent_corrupt_archive_and_reports_health_failure(): void
    {
        [$directory, $report] = $this->temporaryPaths();
        File::ensureDirectoryExists($directory);
        File::put($directory.'/mis-backup-corrupt.zip', 'not a zip archive');
        Http::fake([
            'http://health.test/health' => Http::response([
                'checks' => ['backup_freshness' => false],
            ], 503),
        ]);

        try {
            $exitCode = Artisan::call('system:verify-backup', [
                '--health-url' => 'http://health.test/health',
                '--backup-directory' => $directory,
                '--report' => $report,
            ]);

            $result = json_decode((string) File::get($report), true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame(1, $exitCode);
            $this->assertSame('fail', $result['status']);
            $this->assertFalse($result['covers_latest_window']);
            $this->assertSame('none', $result['coverage_source']);
            $this->assertNull($result['latest_valid_archive']);
            $this->assertFalse($result['health_backup_freshness']);
            $this->assertCount(2, $result['issues']);
        } finally {
            File::deleteDirectory($directory);
            File::delete($report);
        }
    }

    private function temporaryPaths(): array
    {
        $suffix = Str::lower(Str::random(10));

        return [
            storage_path("app/backup-verification-test-{$suffix}"),
            storage_path("logs/backup-verification-test-{$suffix}.json"),
        ];
    }

    private function createValidArchive(string $path): void
    {
        File::ensureDirectoryExists(dirname($path));
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);
        $zip->addFromString('database.sql', str_repeat('valid sql ', 20));
        $zip->addFromString('manifest.json', json_encode(['created_at' => now()->toIso8601String()]));
        $zip->close();
    }
}

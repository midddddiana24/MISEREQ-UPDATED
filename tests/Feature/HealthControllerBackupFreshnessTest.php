<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HealthControllerBackupFreshnessTest extends TestCase
{
    public function test_health_endpoint_reports_stale_backup_as_unhealthy(): void
    {
        $backupDir = (string) config('backup.directory');
        File::ensureDirectoryExists($backupDir);

        $archives = File::glob($backupDir . '/mis-backup-*.zip');
        $timestamps = collect($archives)->mapWithKeys(
            fn(string $path) => [$path => File::lastModified($path)]
        );
        $staleArchive = $backupDir . '/mis-backup-stale-test.zip';
        File::put($staleArchive, 'not-a-real-zip');
        touch($staleArchive, now()->subDays(2)->timestamp);
        foreach ($archives as $archive) {
            touch($archive, now()->subDays(2)->timestamp);
        }

        try {
            $this->get('/health')
                ->assertStatus(503)
                ->assertJsonPath('checks.backup_freshness', false);
        } finally {
            File::delete($staleArchive);
            foreach ($timestamps as $archive => $timestamp) {
                touch($archive, $timestamp);
            }
        }
    }
}

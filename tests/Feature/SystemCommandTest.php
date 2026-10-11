<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class SystemCommandTest extends TestCase
{
    private string $testBackupDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testBackupDirectory = storage_path('app/qa-test-backups-'.Str::lower(Str::random(12)));
        config(['backup.directory' => $this->testBackupDirectory]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->testBackupDirectory);
        parent::tearDown();
    }

    public function test_backup_command_creates_a_verified_database_only_archive(): void
    {
        $backupDirectory = $this->testBackupDirectory;
        File::ensureDirectoryExists($backupDirectory);
        $existingArchives = File::glob($backupDirectory . '/mis-backup-*.zip');
        $fakeDump = storage_path('app/fake-mysqldump.bat');
        File::put($fakeDump, "@echo off\r\necho " . str_repeat('database row ', 20) . "\r\n");
        config(['backup.mysqldump_path' => $fakeDump, 'backup.retention_days' => 1]);

        try {
            $exitCode = Artisan::call('system:backup', ['--database-only' => true, '--retention' => 1]);

            $this->assertSame(0, $exitCode, Artisan::output());
            $archives = File::glob($backupDirectory . '/mis-backup-*.zip');
            $this->assertNotEmpty($archives);
            $zip = new ZipArchive;
            $this->assertTrue($zip->open(end($archives)) === true);
            $this->assertNotFalse($zip->locateName('database.sql'));
            $this->assertNotFalse($zip->locateName('manifest.json'));
            $this->assertStringContainsString('"database_only": true', (string) $zip->getFromName('manifest.json'));
            $zip->close();
        } finally {
            File::delete($fakeDump);
            foreach (array_diff(File::glob($backupDirectory . '/mis-backup-*.zip'), $existingArchives) as $archive) {
                File::delete($archive);
            }
        }
    }

    public function test_backup_if_due_skips_when_latest_daily_window_is_covered(): void
    {
        $backupDirectory = $this->testBackupDirectory;
        File::ensureDirectoryExists($backupDirectory);
        $existingArchives = File::glob($backupDirectory . '/mis-backup-*.zip');
        $archive = $backupDirectory . '/mis-backup-if-due-test.zip';
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);
        $zip->addFromString('database.sql', str_repeat('valid sql ', 20));
        $zip->addFromString('manifest.json', json_encode(['created_at' => now()->toIso8601String()]));
        $zip->close();
        touch($archive, now()->timestamp);
        config(['backup.mysqldump_path' => storage_path('app/does-not-exist-mysqldump.exe')]);

        try {
            $exitCode = Artisan::call('system:backup', ['--if-due' => true]);

            $this->assertSame(0, $exitCode, Artisan::output());
            $expectedArchives = [...$existingArchives, $archive];
            $actualArchives = File::glob($backupDirectory . '/mis-backup-*.zip');
            sort($expectedArchives);
            sort($actualArchives);
            $this->assertSame(
                $expectedArchives,
                $actualArchives,
                'The catch-up command should not create a second archive for the same daily window.'
            );
        } finally {
            File::delete($archive);
        }
    }

    public function test_backup_command_returns_failure_when_mysqldump_is_unavailable(): void
    {
        config(['backup.mysqldump_path' => storage_path('app/does-not-exist-mysqldump.exe')]);

        $exitCode = Artisan::call('system:backup', ['--database-only' => true]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Database backup failed', Artisan::output());
    }

    public function test_restore_command_validates_an_archive_without_running_mysql(): void
    {
        $backupDirectory = $this->testBackupDirectory;
        File::ensureDirectoryExists($backupDirectory);
        $archive = $backupDirectory . '/restore-validation-test.zip';
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);
        $zip->addFromString('database.sql', str_repeat('valid sql ', 20));
        $zip->addFromString('manifest.json', json_encode(['created_at' => now()->toIso8601String(), 'database' => 'test']));
        $zip->close();

        try {
            $exitCode = Artisan::call('system:restore', ['archive' => basename($archive)]);

            $output = Artisan::output();
            $this->assertSame(0, $exitCode, $output);
            $this->assertStringContainsString('Archive verification passed', $output);
        } finally {
            File::delete($archive);
        }
    }

    public function test_restore_command_rejects_an_archive_outside_the_backup_directory(): void
    {
        $exitCode = Artisan::call('system:restore', ['archive' => '../outside.zip']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Backup archive was not found', Artisan::output());
    }

    public function test_scheduler_registers_heartbeat_backup_and_cleanup_tasks(): void
    {
        $exitCode = Artisan::call('schedule:list');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('system-scheduler-heartbeat', $output);
        $this->assertStringContainsString('system:backup', $output);
        $this->assertStringContainsString('system:backup --if-due', $output);
        $this->assertStringContainsString('files:cleanup-orphaned', $output);
    }
}

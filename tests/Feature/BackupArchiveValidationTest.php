<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class BackupArchiveValidationTest extends TestCase
{
    public function test_valid_backup_archive_can_be_verified_without_restoring(): void
    {
        $filename = 'mis-backup-test-' . Str::lower(Str::random(10)) . '.zip';
        $path = storage_path('app/backups/' . $filename);
        File::ensureDirectoryExists(dirname($path));

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('database.sql', str_repeat('-- safe test database content' . PHP_EOL, 10));
        $zip->addFromString('manifest.json', json_encode([
            'application' => 'MIS Service Request',
            'created_at' => now()->toIso8601String(),
            'database' => 'mis_service_request_testing',
            'database_only' => true,
            'format_version' => 1,
        ]));
        $zip->close();

        try {
            $this->artisan('system:restore', ['archive' => $filename])
                ->expectsOutputToContain('Archive verification passed')
                ->assertSuccessful();
        } finally {
            File::delete($path);
        }
    }

    public function test_incomplete_backup_archive_is_rejected(): void
    {
        $filename = 'mis-backup-test-' . Str::lower(Str::random(10)) . '.zip';
        $path = storage_path('app/backups/' . $filename);
        File::ensureDirectoryExists(dirname($path));

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', '{}');
        $zip->close();

        try {
            $this->artisan('system:restore', ['archive' => $filename])
                ->expectsOutputToContain('invalid or incomplete')
                ->assertFailed();
        } finally {
            File::delete($path);
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

class RestoreSystem extends Command
{
    protected $signature = 'system:restore {archive : Backup filename inside storage/app/backups} {--confirm : Perform the destructive restore} {--database-only}';
    protected $description = 'Validate a backup archive and optionally restore its database and secure files';

    public function handle(): int
    {
        $backupDirectory = realpath(storage_path('app/backups'));
        $archive = realpath(storage_path('app/backups/' . basename((string) $this->argument('archive'))));

        if (!$backupDirectory || !$archive || !str_starts_with($archive, $backupDirectory . DIRECTORY_SEPARATOR)) {
            $this->error('Backup archive was not found inside storage/app/backups.');
            return self::FAILURE;
        }

        $zip = new ZipArchive();
        if ($zip->open($archive) !== true || $zip->locateName('database.sql') === false || $zip->locateName('manifest.json') === false) {
            $this->error('The archive is invalid or incomplete.');
            return self::FAILURE;
        }

        $manifest = json_decode((string) $zip->getFromName('manifest.json'), true);
        $this->line('Backup date: ' . ($manifest['created_at'] ?? 'unknown'));
        $this->line('Database: ' . ($manifest['database'] ?? 'unknown'));

        if (!$this->option('confirm')) {
            $zip->close();
            $this->info('Archive verification passed. Re-run with --confirm to restore it.');
            return self::SUCCESS;
        }

        $temporary = storage_path('app/restore-' . Str::uuid());
        File::ensureDirectoryExists($temporary);

        try {
            $zip->extractTo($temporary);
            $zip->close();

            $connection = config('database.connections.mysql');
            $process = new Process([
                $this->mysqlBinary(),
                '--host=' . env('DB_WRITE_HOST', $connection['host']),
                '--port=' . $connection['port'],
                '--user=' . $connection['username'],
                '--default-character-set=utf8mb4',
                $connection['database'],
            ]);
            $process->setEnv(['MYSQL_PWD' => (string) $connection['password']]);
            $process->setInput(File::get($temporary . DIRECTORY_SEPARATOR . 'database.sql'));
            $process->setTimeout(600);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \RuntimeException(trim($process->getErrorOutput()));
            }

            if (!$this->option('database-only')) {
                $this->copyDirectory($temporary . DIRECTORY_SEPARATOR . 'files/secure', storage_path('app/secure'));
                $this->copyDirectory($temporary . DIRECTORY_SEPARATOR . 'files/private', storage_path('app/private'));
            }

            $this->info('Restore completed successfully.');
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            report($exception);
            $this->error('Restore failed: ' . $exception->getMessage());
            return self::FAILURE;
        } finally {
            File::deleteDirectory($temporary);
        }
    }

    private function mysqlBinary(): string
    {
        $configured = config('backup.mysql_path');
        if ($configured) {
            return $configured;
        }

        $candidate = realpath(dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'mysql' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'mysql.exe');
        return $candidate ?: 'mysql';
    }

    private function copyDirectory(string $source, string $destination): void
    {
        if (!File::isDirectory($source)) {
            return;
        }
        File::ensureDirectoryExists($destination);
        File::copyDirectory($source, $destination);
    }
}

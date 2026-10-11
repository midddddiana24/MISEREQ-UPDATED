<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use ZipArchive;

final class BackupArchiveValidator
{
    public function isValid(string $path): bool
    {
        if (! File::isFile($path)) {
            return false;
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            return false;
        }

        $valid = $zip->locateName('manifest.json') !== false
            && $zip->locateName('database.sql') !== false
            && strlen((string) $zip->getFromName('database.sql')) > 100;
        $zip->close();

        return $valid;
    }

    public function latestValid(string $directory): ?array
    {
        $archives = collect(File::glob($directory.DIRECTORY_SEPARATOR.'mis-backup-*.zip'))
            ->filter(fn (string $path) => $this->isValid($path))
            ->sortBy(fn (string $path) => File::lastModified($path), SORT_NUMERIC);

        $path = $archives->last();
        if (! is_string($path)) {
            return null;
        }

        return [
            'path' => $path,
            'modified_at' => File::lastModified($path),
        ];
    }
}

<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Services\StorageService;

class MediaRegisterExisting extends Command
{
    protected $signature = 'media:register-existing
                            {--disk= : Storage disk to scan (defaults to "media", which is where the file manager stores files)}
                            {--limit=0 : Max files to register (0 = no limit)}
                            {--force : Re-register even if a record already exists for the storage_key}';

    protected $description = 'Scan existing media files and register them in the media_files table (for upgrades from before the media library existed).';

    /**
     * Handle the command.
     */
    public function handle(): int
    {
        $driver   = system_setting('media_driver', 'local');
        $diskName = $this->option('disk') ?? 'media';
        $limit    = (int) $this->option('limit');
        $force    = (bool) $this->option('force');

        $this->info("Scanning disk [{$diskName}] under prefix [".StorageService::STORAGE_PREFIX.']...');

        try {
            $disk = Storage::disk($diskName);
        } catch (\Throwable $e) {
            $this->error("Failed to access disk [{$diskName}]: ".$e->getMessage());

            return self::FAILURE;
        }

        $files   = $disk->allFiles();
        $prefix  = rtrim(StorageService::STORAGE_PREFIX, '/');
        $count   = 0;
        $skipped = 0;
        $failed  = 0;

        // Detect whether the disk root already points to the static/media/ directory
        // (e.g. the local "media" disk). In that case raw keys are relative to the prefix.
        $diskRootPath = '';
        try {
            $diskRootPath = $disk->path('');
        } catch (\Throwable $e) {
            // S3 and other cloud disks may not expose a local root path.
        }
        $rootIsMediaPrefix = str_ends_with(rtrim($diskRootPath, '/'), $prefix);

        foreach ($files as $rawKey) {
            if ($limit > 0 && $count >= $limit) {
                break;
            }

            // Skip hidden/system files like .gitkeep.
            if (str_starts_with(basename($rawKey), '.')) {
                continue;
            }

            if ($rootIsMediaPrefix) {
                // Disk root is already static/media/; rawKey is relative to it.
                $storageKey = StorageService::storageKey($rawKey);
            } else {
                // Only register files under the static/media/ prefix.
                if (! str_starts_with($rawKey, $prefix)) {
                    continue;
                }
                $storageKey = StorageService::storageKey($rawKey);
            }

            if (! $force) {
                $existing = MediaFile::findByStorageKey($storageKey);
                if ($existing) {
                    $skipped++;

                    continue;
                }
            }

            try {
                $this->registerFromDisk($disk, $diskName, $rawKey, $storageKey, $driver);
                $count++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('media:register-existing failed', [
                    'storage_key' => $storageKey,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        $this->info("Done. Registered: {$count}, skipped (already existed): {$skipped}, failed: {$failed}.");

        return self::SUCCESS;
    }

    /**
     * Register a single file from the filesystem into media_files.
     */
    protected function registerFromDisk($disk, string $diskName, string $rawKey, string $storageKey, string $driver): void
    {
        $size = $disk->size($rawKey);
        $mime = $disk->mimeType($rawKey);

        // Stream the file to compute SHA-256 without loading it whole.
        $stream  = $disk->readStream($rawKey);
        $hashCtx = hash_init('sha256');
        if (is_resource($stream)) {
            while (! feof($stream)) {
                hash_update($hashCtx, fread($stream, 65536));
            }
            fclose($stream);
        }
        $checksum = hash_final($hashCtx);

        [$width, $height] = $this->readImageDimensions($disk, $diskName, $rawKey, $mime);

        MediaFile::updateOrCreate(
            ['storage_key' => $storageKey],
            [
                'disk'          => $driver,
                'original_name' => basename($rawKey),
                'checksum'      => $checksum,
                'mime'          => $mime,
                'size'          => $size,
                'width'         => $width,
                'height'        => $height,
                'source'        => 'legacy',
            ]
        );
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    protected function readImageDimensions($disk, string $diskName, string $rawKey, ?string $mime): array
    {
        if (! $mime || ! str_starts_with($mime, 'image/')) {
            return [null, null];
        }

        try {
            $tmp    = tempnam(sys_get_temp_dir(), 'media_');
            $stream = $disk->readStream($rawKey);
            if (! is_resource($stream)) {
                return [null, null];
            }
            file_put_contents($tmp, stream_get_contents($stream));
            fclose($stream);

            $info = @getimagesize($tmp);
            @unlink($tmp);

            return $info === false ? [null, null] : [(int) $info[0], (int) $info[1]];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }
}

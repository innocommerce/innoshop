<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Models\MediaFile;

class MediaUrlResolver
{
    /**
     * Prefix used to identify a media reference stored in business tables.
     * Example: "media://123" refers to MediaFile with id=123.
     */
    public const MEDIA_REFERENCE_PREFIX = 'media://';

    public static function getInstance(): static
    {
        return app(static::class);
    }

    /**
     * Check if a stored string is a media reference (e.g. "media://123").
     */
    public static function isMediaReference(?string $value): bool
    {
        return $value && str_starts_with($value, self::MEDIA_REFERENCE_PREFIX);
    }

    /**
     * Extract the media ID from a "media://{id}" reference.
     */
    public static function extractMediaId(string $value): ?int
    {
        if (! self::isMediaReference($value)) {
            return null;
        }

        $id = substr($value, strlen(self::MEDIA_REFERENCE_PREFIX));

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * Build a "media://{id}" reference for storing in business tables.
     */
    public static function buildReference(int $mediaId): string
    {
        return self::MEDIA_REFERENCE_PREFIX.$mediaId;
    }

    /**
     * Register an uploaded file as a media record.
     * If a record with the same checksum already exists, return it (dedup).
     *
     * @param  array  $attrs  Required: disk, storage_key. Optional: original_name, checksum, mime, size, width, height, alt, source.
     */
    public function register(array $attrs): MediaFile
    {
        $storageKey = (string) ($attrs['storage_key'] ?? '');

        // The row for a storage_key describes the object living at that key.
        // Re-uploading the same path overwrites the object, so the existing
        // row is refreshed in place — a second row would be rejected by the
        // unique index on storage_key, and even without it the stale row
        // would leave every media://{id} reference pointing at metadata that
        // no longer matches the served bytes.
        if ($storageKey !== '') {
            $existing = MediaFile::withTrashed()->where('storage_key', $storageKey)->first();
            if ($existing) {
                return $this->refreshRow($existing, $attrs);
            }
        }

        $checksum = $attrs['checksum'] ?? null;

        if ($checksum) {
            $existing = MediaFile::findByChecksum($checksum);
            if ($existing) {
                return $existing;
            }
        }

        return MediaFile::query()->create([
            'disk'          => $attrs['disk'] ?? 'local',
            'storage_key'   => $storageKey,
            'original_name' => $attrs['original_name'] ?? null,
            'checksum'      => $checksum,
            'mime'          => $attrs['mime'] ?? null,
            'size'          => $attrs['size'] ?? 0,
            'width'         => $attrs['width'] ?? null,
            'height'        => $attrs['height'] ?? null,
            'alt'           => $attrs['alt'] ?? null,
            'source'        => $attrs['source'] ?? 'upload',
        ]);
    }

    /**
     * Insert or refresh the row that owns $storageKey — never dedup by
     * checksum.
     *
     * For callers that know exactly which physical object lives at which
     * key (uploads, copies, imports). The unique index on storage_key
     * spans soft-deleted rows, but Eloquent's SoftDeletes scope hides them,
     * so a deleted-then-reuploaded file would hit the constraint. Reusing
     * the row (and restoring it — the object is back on disk) keeps every
     * media://{id} reference alive instead of failing the write.
     *
     * @param  array  $attrs  Optional: disk, original_name, checksum, mime, size, width, height, alt, source.
     */
    public function registerOrRefresh(string $storageKey, array $attrs = []): MediaFile
    {
        $existing = MediaFile::withTrashed()->where('storage_key', $storageKey)->first();

        if ($existing) {
            return $this->refreshRow($existing, $attrs);
        }

        return MediaFile::query()->create(array_merge([
            'disk'          => 'local',
            'original_name' => null,
            'checksum'      => null,
            'mime'          => null,
            'size'          => 0,
            'width'         => null,
            'height'        => null,
            'alt'           => null,
            'source'        => 'upload',
        ], $attrs, ['storage_key' => $storageKey]));
    }

    /**
     * Force-create a media record for a copied file (does NOT dedup by checksum).
     * Each physical copy gets its own row so rename/move/delete can track them independently —
     * unless the destination key is already owned by a row, which then gets
     * refreshed: two rows for one key are rejected by the unique index, and
     * both rows would point at the same object anyway.
     *
     * @param  array  $attrs  Required: storage_key. Optional: original_name, checksum, mime, size, source.
     */
    public function registerCopy(string $storageKey, string $disk, array $attrs = []): MediaFile
    {
        return $this->registerOrRefresh($storageKey, array_merge([
            'disk'   => $disk,
            'source' => 'copy',
        ], $attrs));
    }

    /**
     * Point an existing row at the object that now lives at its key: refresh
     * the object-describing fields and bring the row back if it had been
     * soft-deleted (the file is on disk again, so the row must be too).
     *
     * @param  array  $attrs  Attributes to refresh; null values are ignored so a
     *                        caller that cannot read the metadata keeps the old values.
     */
    private function refreshRow(MediaFile $media, array $attrs): MediaFile
    {
        $values = [];
        foreach (['original_name', 'checksum', 'mime', 'size', 'width', 'height'] as $field) {
            if (array_key_exists($field, $attrs) && $attrs[$field] !== null) {
                $values[$field] = $attrs[$field];
            }
        }

        if ($values !== []) {
            $media->fill($values);
        }

        if ($media->trashed()) {
            $media->restore();
        } elseif ($media->isDirty()) {
            $media->save();
        }

        return $media;
    }

    /**
     * Resolve a media reference (or plain path) to a public URL.
     * Handles three cases:
     *   1. "media://123" — look up MediaFile and resolve its storage_key
     *   2. "static/media/foo.jpg" — legacy direct path, fallback to StorageService
     *   3. other strings (http URLs, local assets) — passthrough to StorageService
     */
    public function resolve(?string $value): string
    {
        if (empty($value)) {
            return StorageService::getInstance()->url('');
        }

        $mediaId = self::extractMediaId($value);
        if ($mediaId !== null) {
            $media = MediaFile::query()->find($mediaId);
            if ($media) {
                return $media->url();
            }

            // Reference points to a missing/deleted media record — return placeholder.
            return StorageService::getInstance()->url('');
        }

        return StorageService::getInstance()->url($value);
    }

    /**
     * Update a media record's storage_key (called after Media rename/move).
     */
    public function relocate(int $mediaId, string $newStorageKey, ?string $newDisk = null): bool
    {
        $media = MediaFile::query()->find($mediaId);
        if (! $media) {
            return false;
        }

        $media->storage_key = $newStorageKey;
        if ($newDisk) {
            $media->disk = $newDisk;
        }
        $media->save();

        return true;
    }

    /**
     * Find a media record by storage_key and relocate it (used by Media hooks).
     */
    public function relocateByKey(string $oldKey, string $newKey, ?string $newDisk = null): bool
    {
        $media = MediaFile::findByStorageKey($oldKey);
        if (! $media) {
            return false;
        }

        return $this->relocate($media->id, $newKey, $newDisk);
    }

    /**
     * Soft delete a media record (called after Media delete).
     */
    public function removeByKey(string $storageKey): bool
    {
        $media = MediaFile::findByStorageKey($storageKey);
        if (! $media) {
            return false;
        }

        $media->delete();

        return true;
    }

    /**
     * Resolve the Laravel Storage disk name for a given driver alias.
     * "local" returns "public", anything else returns "media" (PanelServiceProvider maps it to s3 driver).
     */
    public function diskNameFor(string $driver): string
    {
        return $driver === 'local' ? 'public' : 'media';
    }

    /**
     * Check if "rename to MD5 hash" is enabled in system settings.
     */
    public function shouldRenameToHash(): bool
    {
        return (bool) system_setting('upload_rename_to_md5', false);
    }

    /**
     * Generate a hash-based filename (first 32 chars of SHA-256) when the MD5 rename switch is on.
     * Falls back to the client's original filename when the switch is off.
     */
    public function resolveStoreFileName(UploadedFile $file): string
    {
        if (! $this->shouldRenameToHash()) {
            return $file->getClientOriginalName();
        }

        $realPath  = $file->getRealPath();
        $hash      = is_string($realPath) && file_exists($realPath) ? hash_file('sha256', $realPath) : bin2hex(random_bytes(16));
        $extension = $file->getClientOriginalExtension();

        return $extension ? substr($hash, 0, 32).'.'.$extension : substr($hash, 0, 32);
    }

    /**
     * Register a media record from an UploadedFile (after it has been stored on disk).
     *
     * @param  array  $extra  Override fields (e.g. ['source' => 'upload'])
     */
    public function registerFromUploadedFile(UploadedFile $file, string $storageKey, string $disk, array $extra = []): MediaFile
    {
        $realPath = $file->getRealPath();
        $sha256   = is_string($realPath) && file_exists($realPath) ? hash_file('sha256', $realPath) : null;
        $mime     = $file->getMimeType();

        [$width, $height] = $this->readImageDimensionsForMime($realPath, $mime);

        $mediaFile = $this->register(array_merge([
            'disk'          => $disk,
            'storage_key'   => $storageKey,
            'original_name' => $file->getClientOriginalName(),
            'checksum'      => $sha256,
            'mime'          => $mime,
            'size'          => $file->getSize() ?: 0,
            'width'         => $width,
            'height'        => $height,
            'source'        => 'upload',
        ], $extra));

        // register() dedups by checksum: when identical content already lives
        // under a DIFFERENT storage key, the physical file written for THIS
        // upload would stay untracked — no media row maps its path, so the
        // manager has no media:// reference for it (breaking rename/move and
        // the client-side cover flow) and it lingers as an orphan on delete.
        // Give every physical copy its own row, same contract as registerCopy().
        if ($mediaFile->storage_key !== $storageKey) {
            $mediaFile = $this->registerCopy($storageKey, $disk, array_merge([
                'original_name' => $file->getClientOriginalName(),
                'checksum'      => $sha256,
                'mime'          => $mime,
                'size'          => $file->getSize() ?: 0,
                'width'         => $width,
                'height'        => $height,
                'source'        => 'upload',
            ], $extra));
        }

        return $mediaFile;
    }

    /**
     * Read image dimensions from a local real path (used after UploadedFile is stored).
     *
     * @return array{0: ?int, 1: ?int}
     */
    protected function readImageDimensionsForMime(string|false $realPath, ?string $mime): array
    {
        if (! $realPath || ! file_exists($realPath) || ! $mime || ! str_starts_with($mime, 'image/')) {
            return [null, null];
        }

        $info = @getimagesize($realPath);
        if ($info === false) {
            return [null, null];
        }

        return [(int) $info[0], (int) $info[1]];
    }

    /**
     * Read image dimensions (width/height) from a stored file if it's an image.
     * Returns [width, height] or [null, null] for non-image files.
     *
     * @return array{0: ?int, 1: ?int}
     */
    public function readImageDimensions(string $diskName, string $rawKey): array
    {
        try {
            $disk   = Storage::disk($diskName);
            $stream = $disk->readStream($rawKey);
            if (! $stream) {
                return [null, null];
            }

            $tmpFile = tempnam(sys_get_temp_dir(), 'media_');
            file_put_contents($tmpFile, stream_get_contents($stream));
            fclose($stream);

            $info = @getimagesize($tmpFile);
            @unlink($tmpFile);

            if ($info === false) {
                return [null, null];
            }

            return [(int) $info[0], (int) $info[1]];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }
}

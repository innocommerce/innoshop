<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Services\MediaUrlResolver;
use InnoShop\Common\Services\MediaUsageScanner;
use InnoShop\Common\Services\StorageService;

/**
 * @property int $id
 * @property string $disk
 * @property string $storage_key
 * @property string|null $original_name
 * @property string|null $checksum
 * @property string|null $mime
 * @property int $size
 * @property int|null $width
 * @property int|null $height
 * @property int|null $poster_media_id
 * @property string|null $alt
 * @property string $source
 * @mixin Model
 * @mixin Builder
 */
class MediaFile extends BaseModel
{
    use SoftDeletes;

    protected $table = 'media_files';

    protected $fillable = [
        'disk', 'storage_key', 'original_name', 'checksum',
        'mime', 'size', 'width', 'height', 'poster_media_id', 'alt', 'source',
    ];

    protected $casts = [
        'size'            => 'integer',
        'width'           => 'integer',
        'height'          => 'integer',
        'poster_media_id' => 'integer',
    ];

    /**
     * Auto-extracted poster frame for video files. The poster is itself a
     * MediaFile row (mime=image/jpeg, source=video_poster) so it inherits all
     * resize/URL handling for free.
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(self::class, 'poster_media_id');
    }

    /**
     * Check if this media file still exists on disk.
     */
    public function existsOnDisk(): bool
    {
        $disk = Storage::disk('media');

        try {
            return $disk->exists($this->getRawStorageKey());
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Return the storage key with the static/media/ prefix stripped (raw key passed to Storage::disk()).
     */
    public function getRawStorageKey(): string
    {
        return StorageService::stripPrefix($this->storage_key);
    }

    /**
     * Resolve the public URL for this media file.
     */
    public function url(): string
    {
        return StorageService::getInstance()->url($this->storage_key);
    }

    /**
     * Find a media record by its storage key (any disk).
     */
    public static function findByStorageKey(string $storageKey): ?static
    {
        return static::where('storage_key', $storageKey)->first();
    }

    /**
     * Find a media record by checksum (for dedup).
     */
    public static function findByChecksum(string $checksum): ?static
    {
        return static::where('checksum', $checksum)->first();
    }

    public function scopeForDisk(Builder $query, string $disk): Builder
    {
        return $query->where('disk', $disk);
    }

    /**
     * Tables and image fields that may reference a media file.
     * Used by usageCount() to detect reverse references for safe delete.
     *
     * article_translations is the only per-locale cover table in core schema
     * (category/catalog/brand translations do not carry an image column); it
     * must be scanned so a video poster referenced solely from a translation
     * cover is not pruned by PruneVideoPosters / cleanupReplacedPoster (#291).
     *
     * @var array<string, array<string, 'string'|'json'>>
     */
    protected array $usageTables = [
        'products'             => ['images' => 'json', 'hover_image' => 'string'],
        'product_skus'         => ['images' => 'json'],
        'brands'               => ['logo' => 'string'],
        'categories'           => ['image' => 'string'],
        'catalogs'             => ['image' => 'string'],
        'articles'             => ['image' => 'string'],
        'article_translations' => ['image' => 'string'],
    ];

    /**
     * Whether this media file is referenced by any business row.
     * Short-circuits on the first hit — preferred over usageCount() on hot
     * delete paths (PruneVideoPosters, cleanupReplacedPoster) where only a
     * boolean is needed (#291). The schema caches below keep this fast
     * even when called once per row in a chunked loop.
     *
     * Boundary-safe via MediaUsageScanner: a bare `LIKE '%media://123%'` would
     * also match `media://1234` because the latter contains the former as a
     * substring; the scanner verifies the next character is not another digit
     * before counting the row as a reference.
     */
    public function isInUse(): bool
    {
        $refs = [
            MediaUrlResolver::buildReference($this->id),
            $this->storage_key,
        ];

        foreach ($this->usageTables as $table => $fields) {
            if (! $this->tableExists($table)) {
                continue;
            }

            foreach (array_keys($fields) as $field) {
                if (! $this->columnExists($table, $field)) {
                    continue;
                }
                foreach ($refs as $ref) {
                    if (MediaUsageScanner::rowReferences($table, $field, $ref)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Find all business rows that reference this media file.
     * Returns a map of [table => count] for tables with at least one reference.
     * Useful for prompting the user before delete ("This image is used by 3 products...").
     *
     * For boolean checks, prefer isInUse() — it short-circuits on first hit.
     * Boundary-safe via MediaUsageScanner — see isInUse() for why.
     *
     * @return array<string, int>
     */
    public function usageCount(): array
    {
        $refs = [
            MediaUrlResolver::buildReference($this->id),
            $this->storage_key,
        ];

        $result = [];
        foreach ($this->usageTables as $table => $fields) {
            if (! $this->tableExists($table)) {
                continue;
            }

            $matches = [];
            foreach (array_keys($fields) as $field) {
                if (! $this->columnExists($table, $field)) {
                    continue;
                }
                foreach ($refs as $ref) {
                    foreach (MediaUsageScanner::referencedIds($table, $field, $ref) as $id => $_) {
                        $matches[$id] = true;
                    }
                }
            }

            $count = count($matches);
            if ($count > 0) {
                $result[$table] = $count;
            }
        }

        return $result;
    }

    /**
     * Total reference count across all business tables.
     */
    public function totalUsage(): int
    {
        return array_sum($this->usageCount());
    }

    /**
     * Per-connection cache of Schema::hasTable results. Schema::hasTable
     * runs a SHOW TABLES query per call — wasteful when isInUse() /
     * usageCount() probe the same handful of tables once per row in a
     * chunked loop. Keyed by connection so multiple DBs in one request
     * never poison each other. The schema cannot change mid-request, so
     * a request-lifetime cache is safe; tests that mutate schema should
     * call Schema::getConnection()->reconnect() or rebuild the model.
     *
     * @var array<string, array<string, bool>>
     */
    private static array $tableExistsCache = [];

    /**
     * @var array<string, array<string, array<string, bool>>>
     */
    private static array $columnExistsCache = [];

    /**
     * @param-out bool $_
     */
    protected function tableExists(string $table): bool
    {
        $conn = $this->getConnectionName() ?? '';

        return self::$tableExistsCache[$conn][$table] ??= Schema::hasTable($table);
    }

    /**
     * @param-out bool $_
     */
    protected function columnExists(string $table, string $field): bool
    {
        $conn = $this->getConnectionName() ?? '';

        return self::$columnExistsCache[$conn][$table][$field] ??= Schema::hasColumn($table, $field);
    }
}

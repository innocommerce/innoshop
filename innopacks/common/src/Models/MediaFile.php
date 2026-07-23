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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Services\MediaUrlResolver;
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
        'mime', 'size', 'width', 'height', 'alt', 'source',
    ];

    protected $casts = [
        'size'   => 'integer',
        'width'  => 'integer',
        'height' => 'integer',
    ];

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
     * @var array<string, array<string, 'string'|'json'>>
     */
    protected array $usageTables = [
        'products'     => ['images' => 'json', 'hover_image' => 'string'],
        'product_skus' => ['images' => 'json'],
        'brands'       => ['logo' => 'string'],
        'categories'   => ['image' => 'string'],
        'catalogs'     => ['image' => 'string'],
        'articles'     => ['image' => 'string'],
    ];

    /**
     * Find all business rows that reference this media file.
     * Returns a map of [table => count] for tables with at least one reference.
     * Useful for prompting the user before delete ("This image is used by 3 products...").
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
            if (! Schema::hasTable($table)) {
                continue;
            }

            $count = 0;
            foreach (array_keys($fields) as $field) {
                if (! Schema::hasColumn($table, $field)) {
                    continue;
                }
                foreach ($refs as $ref) {
                    $count += DB::table($table)
                        ->where($field, 'like', '%'.$ref.'%')
                        ->distinct()
                        ->count('id');
                }
            }

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
}

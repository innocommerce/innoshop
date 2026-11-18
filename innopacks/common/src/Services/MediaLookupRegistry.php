<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use InnoShop\Common\Models\MediaFile;

/**
 * Per-request MediaFile lookup for media://{id} references.
 *
 * Earlier lived in Cache::store('array') with a 60s TTL — the array store
 * is process-wide (singleton), so under Octane / Swoole workers the cached
 * MediaFile rows leak across HTTP requests and stay valid for up to a minute
 * after a poster is replaced, hiding the new poster from the very page that
 * triggered it (#291). Bound as a `scoped` singleton so PHP-FPM and Octane
 * both get a fresh registry per request — preload populates within the
 * request, the next request starts empty.
 *
 * Also caches the derived helpers (isVideo, posterUrl) so the same
 * (media id, size) pair never re-resolves within the request — which is
 * what media_poster_url() / media_is_video() need when a list view renders
 * 20 rows.
 */
class MediaLookupRegistry
{
    /** @var array<int, ?MediaFile> */
    private array $mediaCache = [];

    /** @var array<string, string|bool> */
    private array $derivedCache = [];

    public function get(int $mediaId): ?MediaFile
    {
        if (array_key_exists($mediaId, $this->mediaCache)) {
            return $this->mediaCache[$mediaId];
        }

        $media                      = MediaFile::query()->find($mediaId);
        $this->mediaCache[$mediaId] = $media;

        return $media;
    }

    /**
     * Batch-warm with the poster relation eager-loaded so media_is_video
     * and media_poster_url resolve from the cache without a follow-up
     * poster query.
     *
     * @param  array<int>  $mediaIds
     */
    public function preload(array $mediaIds): void
    {
        $missing = array_values(array_filter(
            $mediaIds,
            fn ($id) => ! array_key_exists($id, $this->mediaCache)
        ));
        if ($missing === []) {
            return;
        }

        $mediaFiles = MediaFile::query()->with('poster')->whereIn('id', $missing)->get();
        foreach ($mediaFiles as $mediaFile) {
            $this->mediaCache[$mediaFile->id] = $mediaFile;
        }
    }

    public function isVideo(int $mediaId): bool
    {
        $key = 'is_video:'.$mediaId;
        if (array_key_exists($key, $this->derivedCache)) {
            return (bool) $this->derivedCache[$key];
        }

        $media = $this->get($mediaId);
        if (! $media) {
            return $this->derivedCache[$key] = false;
        }

        if (! empty($media->mime) && str_starts_with($media->mime, 'video/')) {
            return $this->derivedCache[$key] = true;
        }

        return $this->derivedCache[$key] = (bool) preg_match(
            '/\.(mp4|webm|ogg)(\?|$)/i',
            (string) $media->storage_key
        );
    }

    public function posterUrl(int $mediaId, int $width, int $height): string
    {
        $key = "poster_url:{$mediaId}:{$width}x{$height}";
        if (array_key_exists($key, $this->derivedCache)) {
            return (string) $this->derivedCache[$key];
        }

        $media = $this->get($mediaId);
        if (! $media) {
            return $this->derivedCache[$key] = '';
        }

        $poster = $media->poster;

        return $this->derivedCache[$key] = ($poster
            ? image_resize($poster->storage_key, $width, $height)
            : '');
    }
}

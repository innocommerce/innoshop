<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Models\MediaFile;

/**
 * Ensures a video MediaFile carries an auto-extracted poster frame.
 *
 * Shared by the upload flow (MediaService) and the media:generate-posters
 * backfill command. Best-effort by design: every failure returns false and is
 * logged, never thrown — posters are nice-to-have, not blocking.
 */
class VideoPosterService
{
    /**
     * finfo/Symfony report mp4 as `application/mp4` (the IANA type), not
     * `video/mp4` — a bare video/* check silently skips most real mp4
     * uploads. Mirror media_is_video(): positive video mime, extension
     * fallback on the storage key for everything else.
     */
    public static function isVideoMedia(?string $mime, ?string $storageKey): bool
    {
        if ($mime && str_starts_with($mime, 'video/')) {
            return true;
        }

        return (bool) preg_match('/\.(mp4|webm|ogg)$/i', (string) $storageKey);
    }

    public function __construct(private readonly ?VideoPosterExtractor $extractor = null) {}

    /**
     * Extract (once) and link a poster for the given video MediaFile.
     * Idempotent: an existing on-disk poster short-circuits the call unless
     * $force is set to re-extract it (re-extraction dedups through the
     * checksum-based register(); the previous poster row is kept).
     */
    public function ensurePoster(MediaFile $video, bool $force = false): bool
    {
        if (! self::isVideoMedia($video->mime, $video->storage_key)) {
            return false;
        }

        // Judge by the file's own disk, not the current system driver: videos
        // uploaded while the driver was local keep disk='local' and stay
        // extractable after a later switch to a remote driver. Only the local
        // disk is supported; streaming through a temp file can come later.
        if (($video->disk ?? 'local') !== 'local') {
            Log::warning('VideoPosterService: poster extraction skipped for non-local disk.', [
                'storage_key' => $video->storage_key,
            ]);

            return false;
        }

        if (! $force && $video->poster?->existsOnDisk()) {
            return true;
        }

        $previousPoster = $video->poster()->first();

        $disk   = Storage::disk('media');
        $rawKey = $video->getRawStorageKey();

        // Hash-based poster filename so non-ASCII characters in the source
        // video name (e.g. Chinese) do not break the image pipeline —
        // Intervention's file-path decoder refuses non-printable-ASCII paths.
        $posterHash   = substr(hash('sha256', $video->storage_key), 0, 32);
        $posterRawKey = 'posters/'.$posterHash.'.jpg';

        try {
            $videoAbsolute  = $disk->path($rawKey);
            $posterAbsolute = $disk->path($posterRawKey);
        } catch (\Throwable $e) {
            Log::warning('VideoPosterService: cannot resolve storage paths.', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        if (! file_exists($videoAbsolute)) {
            Log::warning('VideoPosterService: video file missing on disk.', [
                'storage_key' => $video->storage_key,
            ]);

            return false;
        }

        $extractor = $this->extractor ?? new VideoPosterExtractor;
        if (! $extractor->extract($videoAbsolute, $posterAbsolute)) {
            return false;
        }

        $size             = filesize($posterAbsolute) ?: 0;
        $checksum         = hash_file('sha256', $posterAbsolute) ?: null;
        [$width, $height] = $this->readImageDimensions($posterAbsolute);

        $posterStorageKey = StorageService::storageKey($posterRawKey);

        // A re-extraction writes to the SAME deterministic path as the
        // previous poster row. Registering a fresh row on that path would
        // leave two rows sharing one file — and the guarded cleanup would
        // then delete the file the surviving row still references. Update
        // the machine-generated row in place instead.
        if ($previousPoster
            && $previousPoster->source === 'video_poster'
            && $previousPoster->storage_key === $posterStorageKey) {
            try {
                $previousPoster->fill([
                    'checksum' => $checksum,
                    'size'     => $size,
                    'width'    => $width,
                    'height'   => $height,
                ])->save();

                $video->poster_media_id = $previousPoster->id;
                $video->save();
            } catch (\Throwable $e) {
                Log::warning('VideoPosterService: failed to update poster row in place.', [
                    'poster_id' => $previousPoster->id,
                    'error'     => $e->getMessage(),
                ]);

                return false;
            }

            return true;
        }

        try {
            $poster = MediaUrlResolver::getInstance()->register([
                'disk' => $video->disk,
                // Disk-relative raw key — storageKey() prepends the static/media/
                // prefix, so passing an absolute path here would corrupt the key.
                'storage_key'   => $posterStorageKey,
                'original_name' => pathinfo($rawKey, PATHINFO_FILENAME).'.poster.jpg',
                'checksum'      => $checksum,
                'mime'          => 'image/jpeg',
                'size'          => $size,
                'width'         => $width,
                'height'        => $height,
                'source'        => 'video_poster',
            ]);
        } catch (\Throwable $e) {
            // The frame was written but no row references it — delete it so no
            // orphan file lingers on disk.
            $this->deleteQuietly($disk, $posterRawKey);
            Log::warning('VideoPosterService: failed to register poster MediaFile.', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        // register() dedups by checksum: a byte-identical frame extracted from
        // another video returns THAT row (different storage_key), which would
        // leave the file we just wrote unreferenced — remove the duplicate.
        if ($poster->storage_key !== $posterStorageKey) {
            $this->deleteQuietly($disk, $posterRawKey);
        }

        try {
            $video->poster_media_id = $poster->id;
            $video->save();
        } catch (\Throwable $e) {
            Log::warning('VideoPosterService: failed to link poster to video.', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        // Force re-extraction also replaces the previous frame — prune it
        // (guarded) instead of letting poster rows accumulate per re-run.
        $this->cleanupReplacedPoster($previousPoster, $poster, $video);

        return true;
    }

    /**
     * Resolve the target video for a cover request. Accepts a media id, a
     * "media://{id}" reference (what the panel form stores), or a raw storage
     * key: files uploaded before the media system (legacy raw-path covers)
     * have no media_files row — when the physical file exists it is adopted
     * into one on the fly, so old covers gain posters AND a media://
     * reference from here on. Non-video rows and missing files return null.
     */
    public function resolveVideoMedia(int|string $video): ?MediaFile
    {
        if (MediaUrlResolver::isMediaReference((string) $video)) {
            $mediaId = MediaUrlResolver::extractMediaId((string) $video);
            $media   = $mediaId ? MediaFile::query()->find($mediaId) : null;

            return ($media && self::isVideoMedia($media->mime, $media->storage_key)) ? $media : null;
        }

        if (ctype_digit((string) $video)) {
            $media = MediaFile::query()->find((int) $video);

            return ($media && self::isVideoMedia($media->mime, $media->storage_key)) ? $media : null;
        }

        $storageKey = trim((string) $video);
        if ($storageKey === '') {
            return null;
        }

        // Full URLs pointing into media storage resolve to their canonical
        // key: take everything after the static/media/ marker wherever it
        // appears (CDN/app base prefixes differ per install). URLs outside
        // media storage are not resolvable to a managed row.
        if (str_starts_with($storageKey, 'http')) {
            $pos = strpos($storageKey, StorageService::STORAGE_PREFIX);
            if ($pos === false) {
                return null;
            }
            $storageKey = substr($storageKey, (int) $pos);
        }

        // Match both the submitted form and the canonical prefixed form: a
        // raw legacy path adopted earlier carries the prefixed key, so a
        // repeated raw submission must reuse that row instead of minting a
        // duplicate for the same physical file.
        $canonicalKey = StorageService::storageKey(StorageService::stripPrefix($storageKey));
        $media        = MediaFile::query()->whereIn('storage_key', array_unique([$storageKey, $canonicalKey]))->first();
        if ($media) {
            return self::isVideoMedia($media->mime, $media->storage_key) ? $media : null;
        }

        // The media disk root sits BELOW the static/media prefix — strip it
        // before touching the disk (StorageService::storageKey() puts it back
        // on when the row is registered).
        $disk   = Storage::disk('media');
        $rawKey = StorageService::stripPrefix($storageKey);
        if (! $disk->exists($rawKey)) {
            return $this->adoptThemeVideo($storageKey);
        }

        $mime = (string) ($disk->mimeType($rawKey) ?: '');

        if (! self::isVideoMedia($mime, $storageKey)) {
            return null;
        }

        // firstOrCreate on the canonical key collapses concurrent adoptions
        // onto one row in the common case. Record the driver the media disk
        // currently maps to (same convention as the upload flow) instead of
        // assuming local. The unique index on storage_key turns a true race
        // (two requests beat each other past the first() check) into a
        // unique-violation QueryException — firstOrCreateMediaFile() catches
        // that and re-queries for the winner row.
        return $this->firstOrCreateMediaFile(
            ['storage_key' => $canonicalKey],
            [
                'disk'          => system_setting('media_driver', 'local'),
                'original_name' => basename($rawKey),
                'mime'          => $mime,
                'size'          => (int) $disk->size($rawKey),
                'source'        => 'upload',
            ]
        );
    }

    /**
     * Adopt a theme-shipped video (static/themes/...) into a media row keyed
     * by its full path: theme files live on the public web root, not under
     * the media disk, so the regular disk probe misses them — but seeded
     * covers reference them and the pick-cover flow needs a row to link a
     * poster to. Server-side extraction can never run for them (the media
     * disk root is the wrong base); the browser-capture flow is the poster
     * source, and the returned media:// reference is what the form stores.
     */
    private function adoptThemeVideo(string $storageKey): ?MediaFile
    {
        if (! str_starts_with($storageKey, 'static/themes/')) {
            return null;
        }

        $extension = strtolower(pathinfo($storageKey, PATHINFO_EXTENSION));
        $mimes     = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg'];
        if (! isset($mimes[$extension]) || ! is_file(public_path($storageKey))) {
            return null;
        }

        return $this->firstOrCreateMediaFile(
            ['storage_key' => $storageKey],
            [
                'disk'          => system_setting('media_driver', 'local'),
                'original_name' => basename($storageKey),
                'mime'          => $mimes[$extension],
                'size'          => (int) filesize(public_path($storageKey)),
                'source'        => 'upload',
            ]
        );
    }

    /**
     * Store a browser-captured poster frame (canvas JPEG/PNG) and link it as
     * the video's poster — the client-side counterpart of ensurePoster(). No
     * ffmpeg involved: the frame is already decoded by the browser, so this
     * also works for videos on remote disks. Re-picking keeps the previous
     * poster row on disk (same semantics as a forced re-extraction); the
     * checksum dedup inside register() collapses byte-identical frames onto
     * the existing row and the just-written duplicate file is removed.
     *
     * @return MediaFile|null The linked poster row, or null when the upload is unusable.
     */
    public function linkPosterFromUpload(MediaFile $video, UploadedFile $poster): ?MediaFile
    {
        if (! $poster->isValid()) {
            return null;
        }

        $mime = (string) ($poster->getMimeType() ?: '');
        if (! str_starts_with($mime, 'image/')) {
            Log::warning('VideoPosterService: uploaded poster frame is not an image.', [
                'mime' => $mime,
            ]);

            return null;
        }

        $disk = Storage::disk('media');
        $name = substr(hash('sha256', $video->storage_key.'|'.uniqid('', true)), 0, 32).'.jpg';

        try {
            $rawKey = (string) $disk->putFileAs('posters', $poster, $name, ['ContentType' => $mime]);
        } catch (\Throwable $e) {
            Log::warning('VideoPosterService: cannot store uploaded poster frame.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($rawKey === '') {
            return null;
        }

        $checksum         = hash_file('sha256', $poster->getRealPath()) ?: null;
        [$width, $height] = $this->readImageDimensions($poster->getRealPath());

        $posterStorageKey = StorageService::storageKey($rawKey);

        try {
            $posterRow = MediaUrlResolver::getInstance()->register([
                // Record the driver the media disk currently maps to (same
                // convention as the upload flow) — the frame was written to
                // disk('media'), wherever that resolves.
                'disk'          => system_setting('media_driver', 'local'),
                'storage_key'   => $posterStorageKey,
                'original_name' => pathinfo($video->storage_key, PATHINFO_FILENAME).'.poster.jpg',
                'checksum'      => $checksum,
                'mime'          => $mime,
                'size'          => filesize($poster->getRealPath()) ?: 0,
                'width'         => $width,
                'height'        => $height,
                'source'        => 'video_poster',
            ]);
        } catch (\Throwable $e) {
            // The frame was written but no row references it — delete it so no
            // orphan file lingers on disk.
            $this->deleteQuietly($disk, $rawKey);
            Log::warning('VideoPosterService: failed to register uploaded poster frame.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        // register() dedups by checksum: a byte-identical frame returns THAT
        // row (different storage key), which would leave the file we just
        // wrote unreferenced — remove the duplicate.
        if ($posterRow->storage_key !== $posterStorageKey) {
            $this->deleteQuietly($disk, $rawKey);
        }

        // Fresh query, not the (cached) relation: repeated links on the same
        // model instance must still see the row this link is replacing.
        $previousPoster = $video->poster()->first();

        try {
            $video->poster_media_id = $posterRow->id;
            $video->save();
        } catch (\Throwable $e) {
            Log::warning('VideoPosterService: failed to link uploaded poster to video.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        // The frame this video used before is no longer referenced by it —
        // prune it (guarded) so repeated re-picking does not accumulate files.
        $this->cleanupReplacedPoster($previousPoster, $posterRow, $video);

        return $posterRow;
    }

    /**
     * Prune the poster a video just replaced — but only when it is safe to:
     * machine-generated rows (source=video_poster) that no other video links.
     * Merchant-uploaded posters and frames shared by sibling videos survive,
     * so repeated cover re-picking no longer accumulates files.
     */
    private function cleanupReplacedPoster(?MediaFile $previous, MediaFile $newPoster, MediaFile $video): void
    {
        if (! $previous || $previous->id === $newPoster->id) {
            return;
        }

        if ($previous->source !== 'video_poster') {
            return;
        }

        $shared = MediaFile::withTrashed()
            ->where('poster_media_id', $previous->id)
            ->where('id', '!=', $video->id)
            ->exists();
        if ($shared) {
            return;
        }

        // The poster may also be picked as a business cover (products/
        // articles/categories storing media://{id}). The media manager
        // refuses to delete in-use files — the automatic path honors the
        // same guard, otherwise business covers 404 without any warning.
        // isInUse() short-circuits on first hit and reuses the per-request
        // schema cache (#291) — way cheaper than usageCount() on the
        // hot delete path.
        if ($previous->isInUse()) {
            return;
        }

        try {
            $rawKey = $previous->getRawStorageKey();
            Storage::disk('media')->delete($rawKey);

            // Cached resize variants (public/cache/...) become dead weight
            // once the source file is gone — sweep them too.
            $variantDir  = public_path('cache/static/media/'.pathinfo($rawKey, PATHINFO_DIRNAME));
            $variantBase = pathinfo($rawKey, PATHINFO_FILENAME);
            foreach (glob($variantDir.'/'.$variantBase.'-*') ?: [] as $variant) {
                @unlink($variant);
            }

            $previous->delete();
        } catch (\Throwable $e) {
            Log::warning('VideoPosterService: failed to prune replaced poster.', [
                'poster_id' => $previous->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Best-effort disk cleanup; failures are swallowed — an orphan left
     * behind beats masking the original error.
     */
    private function deleteQuietly(Filesystem $disk, string $key): void
    {
        try {
            $disk->delete($key);
        } catch (\Throwable) {
        }
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function readImageDimensions(string $absolutePath): array
    {
        $info = @getimagesize($absolutePath);
        if ($info === false) {
            return [null, null];
        }

        return [(int) $info[0], (int) $info[1]];
    }

    /**
     * firstOrCreate() that survives a unique-constraint race.
     *
     * storage_key has a unique index (#291): when two concurrent adoptions
     * both pass the first() probe and both call create(), one insert wins
     * and the other raises a unique-violation QueryException. Re-query for
     * the winning row by the same attributes — if it has appeared in the
     * meantime we return it; if the error is something else, bubble it.
     *
     * @param  array  $attributes  Lookup attributes (must include storage_key).
     * @param  array  $values  Insert values.
     */
    private function firstOrCreateMediaFile(array $attributes, array $values): MediaFile
    {
        try {
            return MediaFile::query()->firstOrCreate($attributes, $values);
        } catch (QueryException $e) {
            // SQLSTATE 23000 = integrity constraint violation (covers unique
            // violations on MySQL, MariaDB, SQLite and PostgreSQL). MySQL
            // also surfaces driver code 1062; SQLite surfaces 19. Treat the
            // SQLSTATE as the canonical signal — it is what every supported
            // driver sets for a unique-key collision.
            $sqlState = $e->errorInfo[0] ?? null;
            if ($sqlState !== '23000') {
                throw $e;
            }

            $existing = MediaFile::query()->where($attributes)->first();
            if (! $existing) {
                // 23000 surfaced for a different constraint (FK, NOT NULL…)
                // — let the caller see the real cause.
                throw $e;
            }

            return $existing;
        }
    }
}

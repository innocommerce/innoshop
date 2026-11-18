<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Services\VideoPosterExtractor;
use InnoShop\Common\Services\VideoPosterService;
use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Extraction itself needs ffmpeg + a real video, so the extractor is replaced
 * with a stub that "writes a frame". What is pinned here is the surrounding
 * contract (#291): registration/linking, checksum-dedup orphan cleanup, and
 * the per-disk skip rule.
 */
class VideoPosterServiceTest extends TestCase
{
    private string $posterBytes = 'fake-poster-frame-bytes';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
    }

    #[Test]
    public function ensure_poster_registers_and_links_a_new_poster(): void
    {
        $video = $this->createVideoWithFile();

        $ok = (new VideoPosterService($this->fakeExtractor()))->ensurePoster($video);

        $this->assertTrue($ok);
        $video->refresh();
        $poster = $video->poster;
        $this->assertNotNull($poster);
        $this->assertSame('video_poster', $poster->source);
        $this->assertTrue(Storage::disk('media')->exists($poster->getRawStorageKey()));
    }

    #[Test]
    public function ensure_poster_removes_fresh_duplicate_when_checksum_dedups_to_existing_row(): void
    {
        // Another video's poster row with the identical frame bytes: register()
        // will dedup to it, so the file written for THIS video is a duplicate.
        $existing = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/posters/other-video.jpg',
            'mime'        => 'image/jpeg',
            'checksum'    => hash('sha256', $this->posterBytes),
            'source'      => 'video_poster',
        ]);
        Storage::disk('media')->put($existing->getRawStorageKey(), $this->posterBytes);

        $video    = $this->createVideoWithFile();
        $freshKey = $this->freshPosterRawKey($video);

        $ok = (new VideoPosterService($this->fakeExtractor()))->ensurePoster($video);

        $this->assertTrue($ok);
        $video->refresh();
        $this->assertSame($existing->id, $video->poster_media_id);
        $this->assertFalse(Storage::disk('media')->exists($freshKey), 'duplicate frame file must be cleaned up');
        $this->assertTrue(Storage::disk('media')->exists($existing->getRawStorageKey()));
    }

    #[Test]
    public function ensure_poster_skips_videos_on_non_local_disk(): void
    {
        $video = MediaFile::query()->create([
            'disk'        => 's3',
            'storage_key' => 'static/media/videos/remote.mp4',
            'mime'        => 'video/mp4',
            'source'      => 'upload',
        ]);

        $ok = (new VideoPosterService($this->fakeExtractor()))->ensurePoster($video);

        $this->assertFalse($ok);
        $this->assertNull($video->poster_media_id);
    }

    #[Test]
    public function ensure_poster_ignores_non_video_media(): void
    {
        $image = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/pictures/pic.jpg',
            'mime'        => 'image/jpeg',
            'source'      => 'upload',
        ]);

        $this->assertFalse((new VideoPosterService($this->fakeExtractor()))->ensurePoster($image));
    }

    #[Test]
    public function replacing_poster_removes_previous_machine_generated_poster(): void
    {
        $video = $this->createVideoWithFile();
        $first = $this->linkPoster($video, 'frame-a');

        $this->linkPoster($video, 'frame-b');

        $video->refresh();
        $this->assertNotSame($first->id, $video->poster_media_id, 'a new frame must produce a new poster row');
        $this->assertNull(MediaFile::query()->find($first->id), 'replaced machine-generated poster row must be pruned');
        Storage::disk('media')->assertMissing($first->getRawStorageKey());
    }

    #[Test]
    public function replacing_poster_keeps_poster_shared_by_another_video(): void
    {
        $video  = $this->createVideoWithFile();
        $other  = $this->createVideoWithFile();
        $poster = $this->linkPoster($video, 'shared-frame');

        // Another video shares the same poster row (identical frame dedup).
        $other->poster_media_id = $poster->id;
        $other->save();

        $this->linkPoster($video, 'other-frame');

        $this->assertNotNull(MediaFile::query()->find($poster->id), 'poster shared by another video must be kept');
        Storage::disk('media')->assertExists($poster->getRawStorageKey());

        $video->refresh();
        $this->assertNotNull($video->poster_media_id);
    }

    #[Test]
    public function replacing_poster_keeps_user_uploaded_poster(): void
    {
        $video = $this->createVideoWithFile();

        // A poster the merchant uploaded by hand (not machine-generated) is
        // business content — replacing the cover must never prune it.
        $manualRawKey = 'posters/manual-'.uniqid().'.jpg';
        $manual       = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/'.$manualRawKey,
            'mime'        => 'image/jpeg',
            'source'      => 'upload',
        ]);
        Storage::disk('media')->put($manualRawKey, 'manual-bytes');
        $video->poster_media_id = $manual->id;
        $video->save();

        $this->linkPoster($video, 'new-frame');

        $video->refresh();
        // The link always moves to the new poster; the manual poster itself
        // must survive untouched (kept row + kept file).
        $this->assertNotSame($manual->id, $video->poster_media_id);
        $this->assertNotNull(MediaFile::query()->find($manual->id), 'user-uploaded poster row must be kept');
        Storage::disk('media')->assertExists($manualRawKey);
    }

    #[Test]
    public function relinking_identical_frame_keeps_current_poster(): void
    {
        $video = $this->createVideoWithFile();
        $first = $this->linkPoster($video, 'same-frame');

        // Same frame bytes → checksum dedup returns the SAME row; it must not
        // be pruned as a "replaced" poster.
        $second = $this->linkPoster($video, 'same-frame');

        $this->assertSame($first->id, $second->id, 'identical frames must dedup onto one row');
        $this->assertNotNull(MediaFile::query()->find($first->id));
        $video->refresh();
        $this->assertSame($first->id, $video->poster_media_id);
    }

    #[Test]
    public function force_reextraction_updates_poster_row_in_place_and_keeps_file(): void
    {
        $service = new VideoPosterService($this->fakeExtractor());
        $video   = $this->createVideoWithFile();
        $this->assertTrue($service->ensurePoster($video));

        $first    = $video->fresh()->poster;
        $firstKey = $first->getRawStorageKey();

        // --force re-extracts to the SAME deterministic path: the row must be
        // updated in place, because a second row on that path would make the
        // guarded cleanup delete the file the surviving row still references.
        $service = new VideoPosterService($this->fakeExtractor('different-frame-bytes'));
        $this->assertTrue($service->ensurePoster($video, true));

        $video->refresh();
        $this->assertSame($first->id, $video->poster_media_id, 'same deterministic path must update the existing row, not mint a duplicate');
        $this->assertNotNull(MediaFile::query()->find($first->id));
        Storage::disk('media')->assertExists($firstKey);
        $this->assertSame('different-frame-bytes', Storage::disk('media')->get($firstKey));
        $this->assertSame(hash('sha256', 'different-frame-bytes'), $video->poster->checksum);
    }

    #[Test]
    public function replacing_poster_keeps_poster_linked_by_soft_deleted_video(): void
    {
        $video  = $this->createVideoWithFile();
        $poster = $this->linkPoster($video, 'shared-frame');

        // A sibling video shares the poster, then goes to the trash — the
        // link is restorable, so the poster must not be judged orphaned.
        $other                  = $this->createVideoWithFile();
        $other->poster_media_id = $poster->id;
        $other->save();
        $other->delete();

        $this->linkPoster($video, 'new-frame');

        $this->assertNotNull(MediaFile::query()->find($poster->id), 'poster linked by a trashed (restorable) video must survive');
        Storage::disk('media')->assertExists($poster->getRawStorageKey());
    }

    #[Test]
    public function replacing_poster_keeps_poster_referenced_by_business_row(): void
    {
        $video  = $this->createVideoWithFile();
        $poster = $this->linkPoster($video, 'cover-frame');

        // The poster itself got picked as an article cover (media://{id}):
        // business references count as in-use, same guard the media manager
        // enforces for manual deletes.
        ArticleRepo::getInstance()->create([
            'slug'         => 'poster-ref-'.uniqid(),
            'image'        => 'media://'.$poster->id,
            'active'       => true,
            'translations' => [['locale' => 'en', 'title' => 'Poster ref', 'image' => '']],
        ]);

        $this->linkPoster($video, 'new-frame');

        $this->assertNotNull(MediaFile::query()->find($poster->id), 'poster referenced by a business cover must survive');
        Storage::disk('media')->assertExists($poster->getRawStorageKey());
    }

    #[Test]
    public function replacing_poster_keeps_poster_referenced_by_translation_cover(): void
    {
        // Per-locale cover (article_translations.image) is the more common
        // case for multi-language stores: the main articles.image is empty or
        // a different file, but a specific locale references the video
        // poster. The poster is in use via that translation row and must
        // survive a re-pick on another video (#291).
        $video  = $this->createVideoWithFile();
        $poster = $this->linkPoster($video, 'cover-frame');

        ArticleRepo::getInstance()->create([
            'slug' => 'poster-trans-ref-'.uniqid(),
            // Main image intentionally left empty — only the translation
            // row holds the reference.
            'image'        => '',
            'active'       => true,
            'translations' => [[
                'locale' => 'en',
                'title'  => 'Per-locale cover ref',
                'image'  => 'media://'.$poster->id,
            ]],
        ]);

        $this->linkPoster($video, 'new-frame');

        $this->assertNotNull(
            MediaFile::query()->find($poster->id),
            'poster referenced only by an article translation cover must survive',
        );
        Storage::disk('media')->assertExists($poster->getRawStorageKey());
    }

    #[Test]
    public function resolve_video_media_reuses_adopted_row_for_repeated_raw_path(): void
    {
        $rawKey = 'videos/legacy-'.uniqid().'.mp4';
        Storage::disk('media')->put($rawKey, 'fake-video-bytes');

        $service = new VideoPosterService($this->fakeExtractor());
        $first   = $service->resolveVideoMedia($rawKey);

        // Same raw value submitted again (another article holds the same
        // legacy cover): must reuse the adopted row, not mint a duplicate.
        $second = $service->resolveVideoMedia($rawKey);
        $this->assertNotNull($first);
        $this->assertSame($first->id, $second?->id, 'repeated adoption of the same physical file must reuse the row');

        // The canonical prefixed form resolves onto the same row too.
        $canonical = $service->resolveVideoMedia('static/media/'.$rawKey);
        $this->assertSame($first->id, $canonical?->id);
    }

    #[Test]
    public function resolve_video_media_maps_full_url_to_media_row(): void
    {
        // Covers stored as absolute URLs (seeders, legacy editors) still
        // resolve when the URL embeds the static/media storage path.
        $video = $this->createVideoWithFile();

        $resolved = (new VideoPosterService($this->fakeExtractor()))
            ->resolveVideoMedia('https://cdn.example.com/base/'.$video->storage_key);

        $this->assertNotNull($resolved, 'a full URL embedding the storage path must resolve to the media row');
        $this->assertSame($video->id, $resolved->id);
    }

    #[Test]
    public function resolve_video_media_adopts_theme_path_video(): void
    {
        // Theme-shipped videos (static/themes/...) live on the public web
        // root, not under the media disk — adopt them keyed by their full
        // path so cover picking works for seeded covers too.
        $themeKey = 'static/themes/test-theme-'.uniqid().'/videos/t.mp4';
        $absolute = public_path($themeKey);
        @mkdir(dirname($absolute), 0777, true);
        file_put_contents($absolute, 'fake-theme-video-bytes');

        try {
            $service = new VideoPosterService($this->fakeExtractor());
            $adopted = $service->resolveVideoMedia($themeKey);

            $this->assertNotNull($adopted, 'theme-shipped video must be adoptable for cover picking');
            $this->assertSame($themeKey, $adopted->storage_key);
            $this->assertSame('video/mp4', $adopted->mime);

            // Repeated adoption reuses the row (firstOrCreate on the path).
            $this->assertSame($adopted->id, $service->resolveVideoMedia($themeKey)?->id);
        } finally {
            @unlink($absolute);
            @rmdir(dirname($absolute));
            @rmdir(dirname($absolute, 2));
            @rmdir(dirname($absolute, 3));
        }
    }

    /**
     * Link a fresh frame onto the video through the public service API
     * (mirrors what the panel endpoint does with a canvas capture). Frames of
     * different names get distinct pixel colors so checksum dedup treats them
     * as genuinely different frames.
     */
    private function linkPoster(MediaFile $video, string $frameContent): MediaFile
    {
        $img   = imagecreatetruecolor(64, 64);
        $color = imagecolorallocate($img, crc32($frameContent) & 0xFF, 90, 160);
        imagefill($img, 0, 0, $color);
        ob_start();
        imagejpeg($img, null, 85);
        $jpeg = ob_get_clean();
        imagedestroy($img);

        $tmp = tempnam(sys_get_temp_dir(), 'frame_').'.jpg';
        file_put_contents($tmp, $jpeg);
        $file = new UploadedFile($tmp, 'poster.jpg', 'image/jpeg', null, true);

        return (new VideoPosterService)->linkPosterFromUpload($video, $file);
    }

    #[Test]
    public function is_video_media_accepts_application_mp4_mime(): void
    {
        // finfo/Symfony guess mp4 as application/mp4 (the IANA type) — the
        // gate must not silently skip real-world uploads.
        $this->assertTrue(VideoPosterService::isVideoMedia('application/mp4', 'static/media/videos/a.mp4'));
        $this->assertTrue(VideoPosterService::isVideoMedia('video/mp4', 'static/media/videos/a.mp4'));
        $this->assertTrue(VideoPosterService::isVideoMedia('video/webm', 'static/media/videos/a.webm'));
        $this->assertTrue(VideoPosterService::isVideoMedia(null, 'static/media/videos/a.webm'));
        $this->assertTrue(VideoPosterService::isVideoMedia('application/ogg', 'static/media/videos/a.OGG'));
        $this->assertFalse(VideoPosterService::isVideoMedia('image/jpeg', 'static/media/pictures/a.jpg'));
        $this->assertFalse(VideoPosterService::isVideoMedia(null, null));
    }

    #[Test]
    public function poster_is_not_protected_by_business_row_pointing_at_sibling_media_id(): void
    {
        // Bare `LIKE '%media://123%'` matches `media://1234` too — siblings
        // share the substring, so a naive probe keeps the wrong poster alive.
        // The boundary check rules that out and lets replacement proceed.
        $video  = $this->createVideoWithFile();
        $poster = $this->linkPoster($video, 'cover-frame');

        ArticleRepo::getInstance()->create([
            'slug' => 'sibling-ref-'.uniqid(),
            // media id adjacent to $poster->id — bare substring would match.
            'image'        => 'media://'.($poster->id + 1),
            'active'       => true,
            'translations' => [['locale' => 'en', 'title' => 'Sibling', 'image' => '']],
        ]);

        $this->linkPoster($video, 'new-frame');

        $this->assertNull(
            MediaFile::query()->find($poster->id),
            'sibling-id reference must not protect an unrelated poster from pruning',
        );
        Storage::disk('media')->assertMissing($poster->getRawStorageKey());
    }

    #[Test]
    public function poster_is_protected_by_business_row_pointing_at_exact_media_id(): void
    {
        // Same boundary check must not over-correct: an exact media://{id}
        // reference still counts as in-use and keeps the poster alive.
        $video  = $this->createVideoWithFile();
        $poster = $this->linkPoster($video, 'cover-frame');

        ArticleRepo::getInstance()->create([
            'slug'         => 'exact-ref-'.uniqid(),
            'image'        => 'media://'.$poster->id,
            'active'       => true,
            'translations' => [['locale' => 'en', 'title' => 'Exact ref', 'image' => '']],
        ]);

        $this->linkPoster($video, 'new-frame');

        $this->assertNotNull(
            MediaFile::query()->find($poster->id),
            'exact media://{id} reference must still protect the poster',
        );
        Storage::disk('media')->assertExists($poster->getRawStorageKey());
    }

    private function createVideoWithFile(): MediaFile
    {
        $video = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/videos/probe-'.uniqid().'.mp4',
            'mime'        => 'video/mp4',
            'source'      => 'upload',
        ]);
        Storage::disk('media')->put($video->getRawStorageKey(), 'fake-video-bytes');

        return $video;
    }

    private function freshPosterRawKey(MediaFile $video): string
    {
        return 'posters/'.substr(hash('sha256', $video->storage_key), 0, 32).'.jpg';
    }

    private function fakeExtractor(?string $bytes = null): VideoPosterExtractor
    {
        $bytes = $bytes ?? $this->posterBytes;

        return new class($bytes) extends VideoPosterExtractor
        {
            public function __construct(private readonly string $posterBytes) {}

            public function extract(string $videoAbsolutePath, string $posterAbsolutePath, int $width = 0, int $height = 0): bool
            {
                @mkdir(dirname($posterAbsolutePath), 0777, true);
                file_put_contents($posterAbsolutePath, $this->posterBytes);

                return true;
            }
        };
    }
}

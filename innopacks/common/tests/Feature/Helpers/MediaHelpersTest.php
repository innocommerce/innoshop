<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Helpers;

use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Services\MediaLookupRegistry;
use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Unit coverage for the video-cover helpers introduced with poster
 * extraction (#291): media:// reference parsing, MIME-first detection with
 * extension fallback, raw-path handling, and the per-request MediaLookupRegistry
 * memoization that bounds list-page queries.
 */
class MediaHelpersTest extends TestCase
{
    #[Test]
    public function media_is_video_detects_video_mime_via_media_reference(): void
    {
        $media = $this->createMedia('static/media/videos/demo.mp4', 'video/mp4');

        $this->assertTrue(media_is_video('media://'.$media->id));
    }

    #[Test]
    public function media_is_video_extension_fallback_also_applies_to_non_video_mime(): void
    {
        // MIME only positively identifies video; whenever it does not, the
        // storage-key extension decides. A mis-mimed .mp4 row still renders
        // as video — safer for the cover display than hiding it.
        $media = $this->createMedia('static/media/fake.mp4', 'image/jpeg');

        $this->assertTrue(media_is_video('media://'.$media->id));

        $image = $this->createMedia('static/media/pictures/real.jpg', 'image/jpeg');

        $this->assertFalse(media_is_video('media://'.$image->id));
    }

    #[Test]
    public function media_is_video_falls_back_to_extension_when_mime_empty(): void
    {
        $media = $this->createMedia('static/media/videos/legacy.webm', '');

        $this->assertTrue(media_is_video('media://'.$media->id));
    }

    #[Test]
    public function media_is_video_returns_false_for_missing_media_reference(): void
    {
        $this->assertFalse(media_is_video('media://999999'));
    }

    #[Test]
    public function media_is_video_checks_bare_paths_by_extension(): void
    {
        $this->assertTrue(media_is_video('uploads/clip.mp4'));
        $this->assertTrue(media_is_video('uploads/clip.WEBM?raw=1'));
        $this->assertFalse(media_is_video('uploads/photo.jpg'));
    }

    #[Test]
    public function media_is_video_returns_false_for_null_and_empty(): void
    {
        $this->assertFalse(media_is_video(null));
        $this->assertFalse(media_is_video(''));
    }

    #[Test]
    public function media_is_video_memoizes_media_lookups_per_request(): void
    {
        $media  = $this->createMedia('static/media/videos/cached.mp4', 'video/mp4');
        $value  = 'media://'.$media->id;
        $warmed = media_is_video($value); // cold call, fills the array cache

        DB::enableQueryLog();
        try {
            $this->assertTrue(media_is_video($value));
            $this->assertTrue(media_is_video($value));

            $mediaQueries = collect(DB::getQueryLog())
                ->filter(fn ($entry) => str_contains($entry['query'], 'media_files'))
                ->count();
            $this->assertSame(0, $mediaQueries, 'warmed helper must not re-query media_files');
        } finally {
            DB::disableQueryLog();
        }

        $this->assertTrue($warmed);
    }

    #[Test]
    public function media_poster_url_returns_resized_url_for_poster(): void
    {
        $video  = $this->createMedia('static/media/videos/postered.mp4', 'video/mp4');
        $poster = $this->createMedia('static/media/posters/postered.jpg', 'image/jpeg');

        $video->poster_media_id = $poster->id;
        $video->save();

        $url = media_poster_url('media://'.$video->id);
        $this->assertNotSame('', $url);
    }

    #[Test]
    public function media_poster_url_returns_empty_without_poster(): void
    {
        $video = $this->createMedia('static/media/videos/bare.mp4', 'video/mp4');

        $this->assertSame('', media_poster_url('media://'.$video->id));
    }

    #[Test]
    public function media_poster_url_returns_empty_for_non_media_values(): void
    {
        // Posters only exist for media-referenced videos: raw paths, null and
        // garbage all yield '' and callers render the play-icon placeholder.
        $this->assertSame('', media_poster_url('uploads/legacy.mp4'));
        $this->assertSame('', media_poster_url(null));
        $this->assertSame('', media_poster_url('not-a-reference'));
    }

    #[Test]
    public function media_poster_url_returns_empty_for_missing_media_reference(): void
    {
        $this->assertSame('', media_poster_url('media://999999'));
    }

    #[Test]
    public function media_cover_preload_seeds_lookup_so_helpers_skip_queries(): void
    {
        $video                  = $this->createMedia('static/media/videos/preloaded.mp4', 'video/mp4');
        $poster                 = $this->createMedia('static/media/posters/preloaded.jpg', 'image/jpeg');
        $video->poster_media_id = $poster->id;
        $video->save();

        media_cover_preload(['media://'.$video->id]);

        DB::enableQueryLog();
        try {
            $this->assertTrue(media_is_video('media://'.$video->id));
            $this->assertNotSame('', media_poster_url('media://'.$video->id));

            $mediaQueries = collect(DB::getQueryLog())
                ->filter(fn ($entry) => str_contains($entry['query'], 'media_files'))
                ->count();
            $this->assertSame(0, $mediaQueries, 'preloaded covers must resolve without queries');
        } finally {
            DB::disableQueryLog();
        }
    }

    #[Test]
    public function media_lookup_model_is_shared_between_helpers(): void
    {
        // Cold path: media_is_video pays the single PK lookup and memoizes the
        // model — media_poster_url must reuse it. This media has no poster
        // (null FK short-circuits the relation query), so zero queries remain.
        $video = $this->createMedia('static/media/videos/shared.mp4', 'video/mp4');
        media_is_video('media://'.$video->id);

        DB::enableQueryLog();
        try {
            media_poster_url('media://'.$video->id);
            $mediaQueries = collect(DB::getQueryLog())
                ->filter(fn ($entry) => str_contains($entry['query'], 'media_files'))
                ->count();
            $this->assertSame(0, $mediaQueries, 'memoized model must be reused, no re-fetch');
        } finally {
            DB::disableQueryLog();
        }
    }

    #[Test]
    public function media_cover_preload_collects_values_from_loaded_relations_only(): void
    {
        $probe = new class
        {
            public function getRawOriginal($key, $default = null)
            {
                return 'media://111';
            }

            public function relationLoaded($relation)
            {
                return $relation === 'translation';
            }

            public function __get($key)
            {
                return (object) ['image' => 'media://222'];
            }
        };

        $values = media_cover_candidate_values($probe);
        $this->assertSame(['media://111', 'media://222'], $values);

        // Plain strings pass through untouched.
        $this->assertSame(['media://333'], media_cover_candidate_values('media://333'));
    }

    #[Test]
    public function article_repo_list_preloads_covers(): void
    {
        $media = $this->createMedia('static/media/videos/listed.mp4', 'video/mp4');
        ArticleRepo::getInstance()->create([
            'slug'         => 'cover-list-'.uniqid(),
            'image'        => 'media://'.$media->id,
            'active'       => true,
            'translations' => [['locale' => 'en', 'title' => 'EN', 'image' => 'media://'.$media->id]],
        ]);

        ArticleRepo::getInstance()->list(['active' => true]);

        $this->assertNotNull(
            app(MediaLookupRegistry::class)->get($media->id),
            'list() must warm the per-request media lookup'
        );
    }

    /**
     * @param  string  $storageKey
     * @param  string  $mime
     * @return MediaFile
     */
    private function createMedia(string $storageKey, string $mime): MediaFile
    {
        return MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => $storageKey,
            'mime'        => $mime,
            'source'      => 'upload',
        ]);
    }
}

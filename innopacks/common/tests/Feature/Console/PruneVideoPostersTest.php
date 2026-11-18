<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Console;

use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * media:prune-posters cleans up the historical accumulation of machine-generated
 * poster rows/files that no video links anymore (before automatic replace-pruning
 * existed, every re-pick left the old poster behind).
 */
class PruneVideoPostersTest extends TestCase
{
    private string $rawKeyA;

    private string $rawKeyB;

    private string $rawKeyC;

    private MediaFile $linked;

    private MediaFile $orphanA;

    private MediaFile $orphanB;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');

        $this->rawKeyA = 'posters/prune-a-'.uniqid().'.jpg';
        $this->rawKeyB = 'posters/prune-b-'.uniqid().'.jpg';
        $this->rawKeyC = 'posters/prune-c-'.uniqid().'.jpg';

        $this->linked  = $this->makePoster($this->rawKeyA);
        $this->orphanA = $this->makePoster($this->rawKeyB);
        $this->orphanB = $this->makePoster($this->rawKeyC);

        // One video links poster A; B and C are orphans.
        $video = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/videos/prune-'.uniqid().'.mp4',
            'mime'        => 'video/mp4',
            'source'      => 'upload',
        ]);
        $video->poster_media_id = $this->linked->id;
        $video->save();
    }

    #[Test]
    public function prune_deletes_unlinked_posters_and_keeps_linked(): void
    {
        $this->artisan('media:prune-posters')->assertSuccessful();

        $this->assertNull(MediaFile::query()->find($this->orphanA->id), 'orphan poster row must be pruned');
        $this->assertNull(MediaFile::query()->find($this->orphanB->id), 'orphan poster row must be pruned');
        $this->assertNotNull(MediaFile::query()->find($this->linked->id), 'linked poster must survive');
        Storage::disk('media')->assertMissing($this->rawKeyB);
        Storage::disk('media')->assertMissing($this->rawKeyC);
        Storage::disk('media')->assertExists($this->rawKeyA);
    }

    #[Test]
    public function prune_dry_run_deletes_nothing(): void
    {
        $this->artisan('media:prune-posters', ['--dry-run' => true])->assertSuccessful();

        $this->assertNotNull(MediaFile::query()->find($this->orphanA->id));
        $this->assertNotNull(MediaFile::query()->find($this->orphanB->id));
        Storage::disk('media')->assertExists($this->rawKeyB);
        Storage::disk('media')->assertExists($this->rawKeyC);
    }

    #[Test]
    public function prune_keeps_poster_linked_only_by_soft_deleted_video(): void
    {
        $poster = $this->makePoster('posters/prune-trashed-'.uniqid().'.jpg');

        $video = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/videos/prune-trashed-'.uniqid().'.mp4',
            'mime'        => 'video/mp4',
            'source'      => 'upload',
        ]);
        $video->poster_media_id = $poster->id;
        $video->save();
        $video->delete();

        $this->artisan('media:prune-posters')->assertSuccessful();

        $this->assertNotNull(MediaFile::query()->find($poster->id), 'poster linked only by a trashed (restorable) video must survive');
        Storage::disk('media')->assertExists($poster->getRawStorageKey());
    }

    #[Test]
    public function prune_keeps_poster_referenced_by_business_row(): void
    {
        $poster = $this->makePoster('posters/prune-biz-'.uniqid().'.jpg');

        // Business references (media://{id} in an article cover) count as
        // in-use — same guard the media manager enforces for manual deletes.
        ArticleRepo::getInstance()->create([
            'slug'         => 'poster-ref-'.uniqid(),
            'image'        => 'media://'.$poster->id,
            'active'       => true,
            'translations' => [['locale' => 'en', 'title' => 'Poster ref', 'image' => '']],
        ]);

        $this->artisan('media:prune-posters')->assertSuccessful();

        $this->assertNotNull(MediaFile::query()->find($poster->id), 'poster referenced by a business cover must survive');
        Storage::disk('media')->assertExists($poster->getRawStorageKey());
    }

    #[Test]
    public function prune_keeps_poster_referenced_only_by_translation_cover(): void
    {
        // Per-locale cover (article_translations.image) — multi-language
        // stores often leave the main articles.image empty and pin the
        // cover to one locale's translation row. The poster must survive
        // even when no main-table row references it (#291).
        $poster = $this->makePoster('posters/prune-trans-'.uniqid().'.jpg');

        ArticleRepo::getInstance()->create([
            'slug'         => 'poster-trans-ref-'.uniqid(),
            'image'        => '',
            'active'       => true,
            'translations' => [[
                'locale' => 'en',
                'title'  => 'Translation cover ref',
                'image'  => 'media://'.$poster->id,
            ]],
        ]);

        $this->artisan('media:prune-posters')->assertSuccessful();

        $this->assertNotNull(
            MediaFile::query()->find($poster->id),
            'poster referenced only by an article translation cover must survive',
        );
        Storage::disk('media')->assertExists($poster->getRawStorageKey());
    }

    private function makePoster(string $rawKey): MediaFile
    {
        Storage::disk('media')->put($rawKey, 'poster-bytes');

        return MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/'.$rawKey,
            'mime'        => 'image/jpeg',
            'source'      => 'video_poster',
        ]);
    }
}

<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Services\MediaUrlResolver;
use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MediaUrlResolverIntegrationTest extends TestCase
{
    #[Test]
    public function resolve_returns_empty_url_for_missing_media_reference(): void
    {
        $url = MediaUrlResolver::getInstance()->resolve('media://999999');
        $this->assertNotSame('media://999999', $url);
    }

    #[Test]
    public function relocate_updates_storage_key_for_existing_media(): void
    {
        $media = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/old/path.jpg',
            'source'      => 'upload',
        ]);

        $ok = MediaUrlResolver::getInstance()->relocate($media->id, 'static/media/new/path.jpg');
        $this->assertTrue($ok);

        $media->refresh();
        $this->assertSame('static/media/new/path.jpg', $media->storage_key);
    }

    #[Test]
    public function relocate_returns_false_for_missing_media_id(): void
    {
        $this->assertFalse(MediaUrlResolver::getInstance()->relocate(999999, 'static/media/whatever.jpg'));
    }

    #[Test]
    public function relocate_by_key_finds_media_via_old_storage_key(): void
    {
        $media = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/before.jpg',
            'source'      => 'upload',
        ]);

        $ok = MediaUrlResolver::getInstance()->relocateByKey('static/media/before.jpg', 'static/media/after.jpg');
        $this->assertTrue($ok);

        $media->refresh();
        $this->assertSame('static/media/after.jpg', $media->storage_key);
    }

    #[Test]
    public function remove_by_key_soft_deletes_media(): void
    {
        $media = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/trash.jpg',
            'source'      => 'upload',
        ]);

        $ok = MediaUrlResolver::getInstance()->removeByKey('static/media/trash.jpg');
        $this->assertTrue($ok);

        // Soft-deleted: not found in default query, but still in DB with deleted_at set.
        $this->assertNull(MediaFile::query()->find($media->id));
        $this->assertNotNull(MediaFile::withTrashed()->find($media->id));
    }

    #[Test]
    public function register_dedups_by_checksum(): void
    {
        $first = MediaUrlResolver::getInstance()->register([
            'disk'        => 'local',
            'storage_key' => 'static/media/dup1.jpg',
            'checksum'    => str_repeat('a', 64),
        ]);

        $second = MediaUrlResolver::getInstance()->register([
            'disk'        => 'local',
            'storage_key' => 'static/media/dup2.jpg',
            'checksum'    => str_repeat('a', 64),
        ]);

        $this->assertSame($first->id, $second->id);
    }

    #[Test]
    public function register_copy_creates_independent_record_even_with_same_checksum(): void
    {
        $first = MediaUrlResolver::getInstance()->registerCopy(
            'static/media/copy1.jpg',
            'local',
            ['checksum' => str_repeat('b', 64)]
        );

        $second = MediaUrlResolver::getInstance()->registerCopy(
            'static/media/copy2.jpg',
            'local',
            ['checksum' => str_repeat('b', 64)]
        );

        $this->assertNotSame($first->id, $second->id);
        $this->assertSame('copy', $second->source);
    }

    #[Test]
    public function register_refreshes_the_row_that_owns_the_storage_key(): void
    {
        // Re-uploading the same path overwrites the stored object, so the
        // row that owns the key must be refreshed in place — a second row
        // would be rejected by the unique index on storage_key, and the
        // stale row would leave media://{id} pointing at old metadata.
        $first = MediaUrlResolver::getInstance()->register([
            'disk'        => 'local',
            'storage_key' => 'static/media/same-path.jpg',
            'checksum'    => str_repeat('c', 64),
            'size'        => 100,
            'width'       => 800,
            'height'      => 800,
        ]);

        $second = MediaUrlResolver::getInstance()->register([
            'disk'        => 'local',
            'storage_key' => 'static/media/same-path.jpg',
            'checksum'    => str_repeat('d', 64),
            'size'        => 200,
            'width'       => 1200,
            'height'      => 1200,
        ]);

        $this->assertSame($first->id, $second->id, 'the row that owns the key must be reused');
        $this->assertSame(1, MediaFile::query()->where('storage_key', 'static/media/same-path.jpg')->count());

        $second->refresh();
        $this->assertSame(str_repeat('d', 64), $second->checksum);
        $this->assertSame(200, $second->size);
        $this->assertSame(1200, $second->width);
    }

    #[Test]
    public function register_reuses_a_soft_deleted_row_for_the_same_storage_key(): void
    {
        // The unique index spans soft-deleted rows while Eloquent's
        // SoftDeletes scope hides them, so a delete-then-reupload would hit
        // the constraint. The row comes back (the object is on disk again)
        // and keeps its id, so media://{id} references stay valid.
        $media = MediaUrlResolver::getInstance()->register([
            'disk'        => 'local',
            'storage_key' => 'static/media/reuploaded.jpg',
            'checksum'    => str_repeat('e', 64),
        ]);
        $media->delete();

        $again = MediaUrlResolver::getInstance()->register([
            'disk'        => 'local',
            'storage_key' => 'static/media/reuploaded.jpg',
            'checksum'    => str_repeat('f', 64),
        ]);

        $this->assertSame($media->id, $again->id);
        $this->assertNull($again->deleted_at, 'the row must be live again');
        $this->assertSame(str_repeat('f', 64), $again->checksum);
    }

    #[Test]
    public function register_or_refresh_creates_then_reuses_the_row_for_a_key(): void
    {
        $created = MediaUrlResolver::getInstance()->registerOrRefresh('static/media/imported.jpg', [
            'disk'          => 'local',
            'checksum'      => str_repeat('1', 64),
            'original_name' => 'imported.jpg',
        ]);

        $refreshed = MediaUrlResolver::getInstance()->registerOrRefresh('static/media/imported.jpg', [
            'disk'     => 'local',
            'checksum' => str_repeat('2', 64),
        ]);

        $this->assertSame($created->id, $refreshed->id);
        $this->assertSame(str_repeat('2', 64), $refreshed->checksum);
        $this->assertSame('imported.jpg', $refreshed->original_name, 'fields the caller left out keep their value');
    }

    #[Test]
    public function usage_count_finds_media_reference_in_business_table(): void
    {
        $media = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/used.jpg',
            'source'      => 'upload',
        ]);

        // Insert a brand row that references this media via media:// link.
        DB::table('brands')->insert([
            'name'       => 'Test Brand',
            'slug'       => 'test-brand',
            'first'      => 'T',
            'logo'       => MediaUrlResolver::buildReference($media->id),
            'active'     => 1,
            'position'   => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usage = $media->usageCount();
        $this->assertArrayHasKey('brands', $usage);
        $this->assertGreaterThanOrEqual(1, $usage['brands']);
        $this->assertGreaterThanOrEqual(1, $media->totalUsage());
    }

    #[Test]
    public function is_in_use_returns_false_when_no_business_references_exist(): void
    {
        // Fresh media row with nothing pointing at it (article cover is empty).
        $media = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/orphan-'.uniqid().'.jpg',
            'source'      => 'upload',
        ]);

        $this->assertFalse($media->isInUse(), 'media with no business references must report as not-in-use');
        $this->assertSame([], $media->usageCount(), 'usageCount() agrees: no breakdown for an orphan');
    }

    #[Test]
    public function is_in_use_detects_media_reference_and_raw_storage_key(): void
    {
        $media = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/covers/legacy-'.uniqid().'.jpg',
            'source'      => 'upload',
        ]);

        // Hit #1: media://{id} reference in articles.image.
        DB::table('articles')->insert([
            'slug'       => 'ref-'.uniqid(),
            'image'      => MediaUrlResolver::buildReference($media->id),
            'active'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue($media->isInUse());
        $this->assertArrayHasKey('articles', $media->usageCount());

        // Hit #2: a separate media row whose storage_key matches a
        // categories.image stored as the raw key (legacy editors did this
        // before MediaUrlResolver::buildReference was introduced).
        $legacyKey = 'static/media/categories/legacy-'.uniqid().'.png';
        $legacy    = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => $legacyKey,
            'source'      => 'upload',
        ]);
        DB::table('categories')->insert([
            'slug'       => 'cat-'.uniqid(),
            'image'      => $legacyKey,
            'active'     => 1,
            'position'   => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue($legacy->isInUse(), 'raw storage-key references in business tables must also count');
        $this->assertArrayHasKey('categories', $legacy->usageCount());
    }
}

<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Regression test for the storage_key unique index migration (#291).
 *
 * RefreshDatabase already ran the migration, so the table starts with the
 * unique index in place. Each test reverts to the pre-migration state
 * (plain index), seeds duplicate rows, runs the migration's up() again and
 * asserts the index builds — reproducing the exact states that blocked
 * `alter table ... add unique` in production.
 */
class MediaFilesStorageKeyUniqueIndexMigrationTest extends TestCase
{
    private const MIGRATION_FILE = '/database/migrations/2026_10_04_120000_add_unique_index_to_media_files_storage_key.php';

    #[Test]
    public function soft_deleted_duplicates_are_purged_when_a_live_row_survives(): void
    {
        // Production failure: two soft-deleted uploads plus one live row on
        // the same storage_key blocked the unique index build because the
        // dedup only scanned live rows.
        $key = 'static/media/视频/场站图片/C0043.MP4_snapshot_00.00.295.jpg';
        $this->seedRow($key, ['deleted_at' => '2026-09-16 06:58:44']);
        $this->seedRow($key, ['deleted_at' => '2026-09-16 07:02:33']);
        $liveId = $this->seedRow($key);

        $this->migration()->up();

        $rows = DB::table('media_files')->where('storage_key', $key)->get();
        $this->assertCount(1, $rows, 'duplicate group must collapse to one row');
        $this->assertSame($liveId, $rows->first()->id, 'the live row must survive');
        $this->assertNull($rows->first()->deleted_at);
        $this->assertUniqueIndexEnforced($key);
    }

    #[Test]
    public function all_deleted_group_keeps_most_recent_soft_deleted_row(): void
    {
        $key   = 'static/media/only/deleted.jpg';
        $oldId = $this->seedRow($key, ['deleted_at' => '2026-09-16 06:58:44']);
        $newId = $this->seedRow($key, ['deleted_at' => '2026-09-16 07:02:33']);

        $this->migration()->up();

        $rows = DB::table('media_files')->where('storage_key', $key)->get();
        $this->assertCount(1, $rows);
        $this->assertSame($newId, $rows->first()->id, 'latest write must survive');
        $this->assertNotSame($oldId, $rows->first()->id);
        $this->assertNotNull($rows->first()->deleted_at, 'survivor stays soft-deleted');
        $this->assertUniqueIndexEnforced($key);
    }

    #[Test]
    public function live_referenced_row_wins_and_poster_links_are_repointed(): void
    {
        // Two live rows on one key; the OLDER one is the poster target of a
        // third row, so it must win and the link stays intact.
        $key          = 'static/media/live/dup.jpg';
        $referencedId = $this->seedRow($key);
        $newerId      = $this->seedRow($key);
        $videoId      = $this->seedRow('static/media/live/video.mp4', ['poster_media_id' => $referencedId]);

        $this->migration()->up();

        $rows = DB::table('media_files')->where('storage_key', $key)->get();
        $this->assertCount(1, $rows);
        $this->assertSame($referencedId, $rows->first()->id, 'poster-referenced row must win');

        $video = DB::table('media_files')->where('id', $videoId)->first();
        $this->assertSame($referencedId, $video->poster_media_id, 'poster link must survive dedup');
        $this->assertNull(DB::table('media_files')->find($newerId), 'unreferenced loser must be deleted');
        $this->assertUniqueIndexEnforced($key);
    }

    #[Test]
    public function up_is_idempotent_after_a_previous_partial_run(): void
    {
        // Reproduce the production state: previous buggy run already
        // dropped the plain index; re-running up() must build the unique
        // index (not crash trying to drop a missing plain index).
        Schema::table('media_files', function (Blueprint $table) {
            $table->dropIndex('media_files_storage_key_index');
        });

        $this->migration()->up();

        $this->assertTrue(
            collect(Schema::getIndexes('media_files'))
                ->contains(fn ($index) => ($index['name'] ?? '') === 'media_files_storage_key_unique' && $index['unique']),
            'unique index must be added even when plain index is already gone'
        );

        // Second run is a no-op (no duplicates, no missing index).
        $this->migration()->up();
    }

    #[Test]
    public function business_references_are_relinked_to_the_surviving_row(): void
    {
        // Both rows carry the same storage_key, so they serve the very same
        // object: re-pointing the reference keeps the cover byte-identical.
        // Leaving it on the deleted row would break it.
        $key      = 'static/media/guarded/dup.jpg';
        $loserId  = $this->seedRow($key);
        $winnerId = $this->seedRow($key);

        DB::table('articles')->insert([
            'image'      => "media://{$loserId}",
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->migration()->up();

        $article = DB::table('articles')->orderBy('id')->first();
        $this->assertSame("media://{$winnerId}", $article->image, 'the reference must point at the surviving row');

        $this->assertNull(DB::table('media_files')->find($loserId), 'the loser must be gone');
        $this->assertNotNull(DB::table('media_files')->find($winnerId));
        $this->assertUniqueIndexEnforced($key);
    }

    #[Test]
    public function business_references_in_json_columns_are_relinked(): void
    {
        $key      = 'static/media/guarded/json-dup.jpg';
        $loserId  = $this->seedRow($key);
        $winnerId = $this->seedRow($key);

        DB::table('products')->insert([
            'brand_id'   => 0,
            'images'     => json_encode(['static/media/other.jpg', "media://{$loserId}"]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->migration()->up();

        $images = json_decode(DB::table('products')->first()->images, true);
        $this->assertSame(['static/media/other.jpg', "media://{$winnerId}"], $images);
        $this->assertUniqueIndexEnforced($key);
    }

    #[Test]
    public function sibling_media_ids_are_left_alone(): void
    {
        // media://59 must not be rewritten when media://5 is the loser.
        $key      = 'static/media/guarded/sibling.jpg';
        $loserId  = $this->seedRow($key);
        $winnerId = $this->seedRow($key);

        $siblingId = $loserId + 4;
        DB::table('articles')->insert([
            'image'      => "media://{$siblingId}",
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->migration()->up();

        $article = DB::table('articles')->orderBy('id')->first();
        $this->assertSame("media://{$siblingId}", $article->image, 'a sibling id must not be rewritten');
        $this->assertNotSame($loserId, $winnerId);
    }

    #[Test]
    public function soft_deleted_losers_keep_their_references(): void
    {
        // A reference to a soft-deleted row already resolves to a
        // placeholder; re-pointing it would resurrect a deleted picture.
        $key      = 'static/media/guarded/deleted-loser.jpg';
        $loserId  = $this->seedRow($key, ['deleted_at' => now()->subDay()]);
        $winnerId = $this->seedRow($key);

        DB::table('articles')->insert([
            'image'      => "media://{$loserId}",
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->migration()->up();

        $article = DB::table('articles')->orderBy('id')->first();
        $this->assertSame("media://{$loserId}", $article->image, 'a deleted row must not be resurrected');
        $this->assertNull(DB::table('media_files')->find($loserId));
    }

    #[Test]
    public function a_loser_on_another_disk_than_the_winner_fails_loudly(): void
    {
        // The same storage_key on two disks is two different objects, so
        // re-pointing the reference would silently swap the picture.
        $key = 'static/media/guarded/mixed-disk.jpg';
        $this->seedRow($key, ['disk' => 'local']);
        $this->seedRow($key, ['disk' => 'cos']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('different disks');

        $this->migration()->up();
    }

    /**
     * @return object migration instance from the migrations directory.
     */
    private function migration(): object
    {
        return require dirname(__DIR__, 3).self::MIGRATION_FILE;
    }

    /**
     * Insert a media_files row through the query builder (bypasses
     * Eloquent so soft-delete semantics never interfere with seeding).
     *
     * @param  array<string, mixed>  $overrides
     */
    private function seedRow(string $storageKey, array $overrides = []): int
    {
        return DB::table('media_files')->insertGetId(array_merge([
            'disk'        => 'local',
            'storage_key' => $storageKey,
            'source'      => 'upload',
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $overrides));
    }

    /**
     * Assert the unique constraint is actually enforced: inserting a second
     * row on the same key must violate it.
     */
    private function assertUniqueIndexEnforced(string $storageKey): void
    {
        $this->assertTrue(
            collect(Schema::getIndexes('media_files'))
                ->contains(fn ($index) => ($index['name'] ?? '') === 'media_files_storage_key_unique' && $index['unique']),
            'media_files_storage_key_unique index must exist after up()'
        );

        $this->expectException(UniqueConstraintViolationException::class);
        $this->seedRow($storageKey);
    }

    /**
     * Runs after RefreshDatabase's migrate:fresh: revert the table to its
     * pre-migration shape (plain index, no unique constraint) so duplicate
     * storage_keys can be seeded.
     */
    protected function afterRefreshingDatabase(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            $table->dropUnique('media_files_storage_key_unique');
            $table->index('storage_key', 'media_files_storage_key_index');
        });
    }
}
